<?php

namespace MG\MoMo\Services\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Log;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Repositories\Eloquent\PaymentRepository;
use Botble\Payment\Services\Traits\PaymentTrait;
use Illuminate\Routing\Controller;
use OrderHelper;
use MG\MoMo\Services\Gateways\MoMoService;
use Str;
use Auth;

class MoMoApi
{
    use PaymentTrait;
    /**
     * @var array
     */
    protected $defaultParameter = [
        'appId',
        'appUsername',
        'appPassword',
        'appWebhookKey',
    ];


    /**
     * Init default parameter
     *
     * @return void
     */
    public function initialize($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get Payment Token
     * @param username password
     * @return token
     */
    public function getPaymentToken($testMode)
    {
        if ($testMode) {
            $token = Http::post(self::getBaseUrlSandbox().'/api/token/', [
                'username' => $this->appUsername,
                'password' => $this->appPassword,
            ]);
            $token->throw();
            return $token['token'];
        } else {
            $token = Http::post(self::getBaseUrlProduction().'/api/token/', [
                'username' => $this->appUsername,
                'password' => $this->appPassword,
            ]);
            $token->throw();
            
            return $token['token'];
        }
    }

    public function getAccountBalance($testMode, $token)
    {
        if ($testMode) {
            $balance = Http::withHeaders(['Authorization' => 'Token '.$token])->get(self::getBaseUrlSandbox().'/api/balance/')->body();
            
            return $balance;
        } else {
            $balance = Http::withHeaders(['Authorization' => 'Token '.$token])->get(self::getBaseUrlProduction().'/api/balance/')->body();
            
            return $balance;
        }
    }

    public function withdrawAccountBalance($validated, $testMode, $token)
    {
        if ($testMode) {
            $withdraw = Http::withHeaders(['Authorization' => 'Token '.$token])->post(self::getBaseUrlSandbox().'/api/withdraw/', [
                'amount' => preg_replace('/\D/', '',$validated['momoAmount']),
                'to' => preg_replace('/\D/', '', $validated['momoTel']),
                'description' => "Withdrawal by admin ".Auth()->User()->id,
                'external_reference' => Str::random(20),
                ]);
                $withdraw->throw();
        } else {
            $withdraw = Http::withHeaders(['Authorization' => 'Token '.$token])->post(self::getBaseUrlProduction().'/api/withdraw/', [
                'amount' =>preg_replace('/\D/', '',$validated['momoAmount']),
                'to' => preg_replace('/\D/', '', $validated['momoTel']),
                'description' => "Withdrawal by admin ".Auth()->User()->id,
                'external_reference' => Str::random(20),
            ]);
            $withdraw->throw();
        }
        do {
            if ($testMode) {
                $response = Http::withHeaders(['Authorization' => 'Token '.$token])->get(self::getBaseUrlSandbox().'/api/transaction/'.$withdraw['reference']);
            } else {
                $response = Http::withHeaders(['Authorization' => 'Token '.$token])->get(self::getBaseUrlProduction().'/api/transaction/'.$withdraw['reference']);
            }
            if($response['status'] == 'SUCCESSFUL')
            {
                return json_encode($response->body());
            }
            if($response['status'] == 'FAILED')
            {
                return json_encode($response->body());
            } 
        } while ($response['status'] == 'PENDING');

    }

    /**
     * Make a purchase
     *
     * @param array $data
     *
     * @return array
     */
    public function purchase($data)
    {
        $data['requestType'] = 'requestMomo';
        $data['orderInfo'] = '';
        $data = $this->getDefaultParameter($data);

        $data['signature'] = $this->getPurchaseSignature($data);
        $response = $this->requestApi($data);
        return $response;
    }

    /**
     * Get API keys
     *
     * @param $data
     *
     * @return mixed
     */
    private function getDefaultParameter($data)
    {
        foreach ($this->defaultParameter as $key) {
            if ($this->$key) {
                $data[$key] = $this->$key;
            }
        }

        return $data;
    }

    /**
     * Get purchase signature
     *
     * @param $data
     *
     * @return string
     */
    private function getPurchaseSignature($data)
    {
        $string = 'appId=' . $this->appId .
            '&appWebhookKey=' . $this->appWebhookKey .
            '&requestId=' . $data['requestId'] .
            '&amount=' . $data['amount'] .
            '&tel=' . $data['tel'] .
            '&orderId=' . $data['orderId'] .
            '&orderInfo=' .
            '&returnUrl=' . $data['returnUrl'] .
            '&notifyUrl=' . $data['notifyUrl'];

        return hash_hmac('sha256', $string, $this->appPassword);
    }

    /**
     * Call api
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    private function requestApi($data)
    {
        $payload = [
            //'amount' => (int)$data['amount'],
            'amount' => "2",
            'from' => $data['tel'],
            'description' => "Afrinook Payment",
            'external_reference' => $data['orderId']
        ];
        try {
            $testMode = $data['testMode'] ?? false;
            unset($data['testMode']);
            
            $api_token = $this->getPaymentToken($testMode);


            if ($testMode) {
                $request = Http::withHeaders(['Authorization' => 'Token '.$api_token])->post(self::getBaseUrlSandbox().'/api/collect/', $payload)['reference'];
            } else {
                $request = Http::withHeaders(['Authorization' => 'Token '.$api_token])->post(self::getBaseUrlProduction().'/api/collect/', $payload)['reference'];
            }

            do {
                if($testMode)
                {
                    $response = Http::withHeaders(['Authorization' => 'Token '.$api_token])->get(self::getBaseUrlSandbox().'/api/transaction/'.$request);
                } else {
                    $response = Http::withHeaders(['Authorization' => 'Token '.$api_token])->get(self::getBaseUrlProduction().'/api/transaction/'.$request);
                }
                
                if($response['status'] == 'SUCCESSFUL')
                {
                    $status = PaymentStatusEnum::COMPLETED;
                    $redirectUrl = $this->updateData($data, $response, $status);
                    return route('public.checkout.success', session('tracked_start_checkout'));
                }
                if($response['status'] == 'FAILED')
                {
                    $status = PaymentStatusEnum::FAILED;
                    $redirectUrl = $this->updateData($data, $response, $status);
                    return false;
                } 
            } while ($response['status'] == 'PENDING');

            return false;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    private function updateData($data, $response, $status)
    {
        $this->paymentRepository = new PaymentRepository(new Payment());
        $orderId = $data['order_id'];
        $transId = $response['external_reference'];
        if (!$transId || $transId === "") {
            Log::error('checkStatusPaymentMomo: transaction not found');
            abort(500, 'Transaction not found');
        }
        $checkTransaction = $this->paymentRepository->getFirstBy(['charge_id' => $transId]);
        if ($checkTransaction) {
            abort(500, 'Transaction already exist in system');
        }
        $this->storeLocalPayment([
            'amount'          => $response['amount'],
            'charge_id'       => $transId,
            'currency'        => $response['currency'],
            'payment_channel' => MoMo_PAYMENT_METHOD_NAME,
            'status'          => $status,
            'customer_id'     => auth('customer')->check() ? auth('customer')->user()
                ->getAuthIdentifier() : null,
            'payment_type'    => $response['operator'],
            'order_id'        => $orderId,
        ]);

        OrderHelper::processOrder($orderId, $transId);
        $baseResponse = new BaseHttpResponse();
        $baseResponse
                    ->setNextUrl(route('public.checkout.success', session('tracked_start_checkout')))
                    ->setMessage(__('Checkout successfully!'));
        return $baseResponse;
    }

    /**
     * Get momo api domain
     *
     * @return string
     */
    private static function getBaseUrlSandbox()
    {
        return config('plugins.momo.general.base_url_sandbox');
    }

    private static function getBaseUrlProduction()
    {
        return config('plugins.momo.general.base_url_production');
    }

    /**
     * Get payment status
     *
     * @param Request $request
     *
     * @return array
     */
    public function getPaymentStatus($request)
    {
        $data = [
            'requestType' => 'transactionStatus',
            'orderId'     => $request->input('orderId'),
            'requestId'   => $request->input('requestId'),
        ];

        $data = $this->getDefaultParameter($data);
        $data['signature'] = $this->getPaymentStatusSignature($data);

        $json = $this->requestApi($data);

        return json_decode($json);
    }

    /**
     * Get payment status signature
     *
     * @param array $data
     *
     * @return string
     */
    private function getPaymentStatusSignature($data)
    {
        $string = 'appId=' . $this->appId .
            '&appWebhookKey=' . $this->appWebhookKey .
            '&requestId=' . $data['requestId'] .
            '&orderId=' . $data['orderId'] .
            '&requestType=transactionStatus';

        return hash_hmac('sha256', $string, $this->secretKey);
    }

    /**
     * Check if call api success
     *
     * @param $response
     *
     * @return boolean
     */
    public function isRedirect($response)
    {
        return $response;
    }

    /**
     * Check data of response
     *
     * @param $data
     *
     * @return boolean
     */
    private function checkSignature($data)
    {
        $string = 'requestId=' . $data->requestId .
            '&orderId=' . $data->orderId .
            '&message=' . $data->message .
            '&localMessage=' . $data->localMessage .
            '&payUrl=' . $data->payUrl .
            '&errorCode=' . $data->errorCode .
            '&requestType=' . $data->requestType;

        return hash_hmac('sha256', $string, $this->secretKey) == $data->signature;
    }

    /**
     * Get redirect Url
     *
     * @param $response
     *
     * @return string
     */
    public function getRedirectUrl($response)
    {
        return route('payments.momo.status');
    }
}
