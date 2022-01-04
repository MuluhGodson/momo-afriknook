<?php

namespace MG\MoMo\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Repositories\Eloquent\PaymentRepository;
use Botble\Payment\Services\Traits\PaymentTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller;
use Log;
use OrderHelper;
use MG\MoMo\Services\Gateways\MoMoService;


class MomoPaymentController extends Controller
{
    use PaymentTrait;

    /**
     * @var MomoService
     */
    protected $momoService;
    protected $appId;
    protected $appUsername;
    protected $appPassword;
    protected $appWebhookKey;
    protected $testMode;
    protected $api_token;

    /**
     * PaymentController constructor.
     * @param MomoService $momoService
     */
    public function __construct(MoMoService $momoService)
    {
        $this->momoService = $momoService;
        $this->appId = setting('payment_momo_app_id');
        $this->appUsername = setting('payment_momo_app_username');
        $this->appPassword = setting('payment_momo_app_password');
        $this->appWebhookKey = setting('payment_momo_webhook_key');
        $this->testMode = setting('payment_momo_mode') == '0';
        $this->api_token = $this->momoService->getToken($this->testMode);
    }


    public function momoBalance()
    {
        $accountBalance = $this->momoService->getBalance($this->testMode);
        return $accountBalance;
    }

    public function withdrawBalance(Request $request)
    {
        $validated = $request->validate([
            'momoTel' => 'required|min:9',
            'momoAmount' => 'required|min:1',
        ]);
        $withdrawBalance = $this->momoService->withdrawBalance($validated, $this->testMode);
        return $withdrawBalance;
    }


    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function checkStatusPaymentMomo(Request $request, BaseHttpResponse $response)
    {
        try {
            $this->paymentRepository = new PaymentRepository(new Payment());
            $responsePay = $this->momoService->getPaymentStatus($request);
            $orderId = json_decode($responsePay->extraData)->order_id;
            $transId = $responsePay->transId;
            if (!$transId || $transId === "") {
                Log::error('checkStatusPaymentMomo: transaction not found');
                abort(500, 'Transaction not found');
            }
            $checkTransaction = $this->paymentRepository->getFirstBy(['charge_id' => $transId]);

            if ($checkTransaction) {
                abort(500, 'Transaction exist in system');
            }
            if ($responsePay->errorCode == 0) {
                $this->storeLocalPayment([
                    'amount'          => $responsePay->amount,
                    'charge_id'       => $transId,
                    'currency'        => 'XAF',
                    'payment_channel' => MoMo_PAYMENT_METHOD_NAME,
                    'status'          => PaymentStatusEnum::COMPLETED,
                    'customer_id'     => auth('customer')->check() ? auth('customer')->user()
                        ->getAuthIdentifier() : null,
                    'payment_type'    => $responsePay->payType,
                    'order_id'        => $orderId,
                ]);

                OrderHelper::processOrder($orderId, $transId);

                $response
                    ->setNextUrl(route('public.checkout.success', session('tracked_start_checkout')))
                    ->setMessage(__('Checkout successfully!'));
            } else {
                $this->storeLocalPayment([
                    'amount'          => $responsePay->amount,
                    'charge_id'       => $transId,
                    'currency'        => 'XAF',
                    'payment_channel' => MoMo_PAYMENT_METHOD_NAME,
                    'status'          => PaymentStatusEnum::FAILED,
                    'customer_id'     => auth('customer')->check() ? auth('customer')->user()
                        ->getAuthIdentifier() : null,
                    'payment_type'    => 'direct',
                    'order_id'        => $orderId,
                ]);

                OrderHelper::processOrder($orderId, $transId);

                return $response
                    ->setError()
                    ->setNextUrl(route('public.checkout.success', session('tracked_start_checkout')))
                    ->setMessage($responsePay->localMessage);
            }
        } catch (Exception $e) {
            Log::error('checkStatusPaymentMomo:' . $e->getMessage());
            abort(500);
        }
    }
}
