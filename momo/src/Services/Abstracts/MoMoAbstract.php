<?php

namespace MG\MoMo\Services\Abstracts;

use Botble\Support\Services\ProduceServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use MG\MoMo\Services\Api\MoMoApi;
use MG\MoMo\Services\Traits\MoMoErrorTrait;

abstract class MoMoAbstract implements ProduceServiceInterface
{
    use MoMoErrorTrait;

    /**
     * @var MoMoApi
     */
    protected $gateway;

    /**
     * MomoPaymentAbstract constructor.
     */
    public function __construct()
    {
        $gateway = new MoMoApi();
        $this->gateway = $gateway;
        $this->gateway->initialize([
            'appId'   => setting('payment_momo_app_id'),
            'appUsername' => setting('payment_momo_app_username'),
            'appPassword'   => setting('payment_momo_app_password'),
            'appWebhookKey'   => setting('payment_momo_webhook_key'),
            'testMode'    => setting('payment_momo_mode') == '0',
        ]);
    }

    /**
     * Execute main service
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function execute(Request $request)
    {
        try {
            return $this->makePayment($request);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception);
            return false;
        }
    }

    /**
     * Make a payment
     *
     * @param Request $request
     *
     * @return mixed
     */
    abstract public function makePayment(Request $request);

    /**
     * Execute main service
     *
     * @param int $orderId
     *
     * @return mixed
     */
    public function hashOrderId($orderId)
    {
        try {
            Crypt::generateKey(MOMOPAY_PAYMENT_SLAT_HASHID);
            return Crypt::encrypt($orderId);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception);
            return false;
        }
    }

    /**
     * Execute main service
     *
     * @param $orderId
     *
     * @return mixed
     */
    public function deHashOrderId($hashOrderId)
    {
        try {
            Crypt::generateKey(MOMOPAY_PAYMENT_SLAT_HASHID);
            return Crypt::decrypt($hashOrderId);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception);
            return false;
        }
    }

    /**
     * Get payment status
     *
     * @param Request $request
     * @return mixed Object payment details or false
     */
    public function getPaymentStatus(Request $request)
    {
        if (empty($request->input('orderId')) || empty($request->input('requestId'))) {
            return false;
        }

        return $this->gateway->getPaymentStatus($request);
    }
}
