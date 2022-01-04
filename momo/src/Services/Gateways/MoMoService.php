<?php

namespace MG\MoMo\Services\Gateways;

use Exception;
use Illuminate\Http\Request;
use Log;
use MG\MoMo\Services\Abstracts\MoMoAbstract;
use MG\MoMo\Services\Traits\MoMoErrorTrait;

class MoMoService extends MomoAbstract
{
    use MoMoErrorTrait;

    private $transaction;

    /**
     * Make a payment
     *
     * @param Request $request
     *
     * @return mixed
     * @throws Exception
     */
    public function supportedCurrencyCodes(): array
    {
        return [
            'XAF'
        ];
    }
    public function makePayment(Request $request)
    {
        $this->transaction = [
            'amount'    => convert_amount_to_XAF($request->get('amount')),
            'tel' => '+237'.$request->get('momo-tel'),
            'returnUrl' => route('payments.momo.status'),
            'notifyUrl' => route('payments.momo.status'),
            'orderId'   => uniqid(),
            'requestId' => $request->get('token'),
            'order_id' => $request->get('order_id'),
        ];
        try {
            $response = $this->gateway->purchase($this->transaction);
            return $response;

           /* if ($this->gateway->isRedirect($response)) {
                return $this->gateway->getRedirectUrl($response, $transaction);
            }

            $this->setErrorMessage($response->localMessage);

            return false;*/
        } catch (Exception $e) {
            Log::error('MomoService - makePayment: ' . $e->getMessage());
            $this->setErrorMessage('Error with Payment');

            return false;
        }
    }

    public function getToken($testMode)
    {
        return $this->gateway->getPaymentToken($testMode);
    }
    
    public function getBalance($testMode)
    {
        $token = $this->getToken($testMode);
        return $this->gateway->getAccountBalance($testMode, $token);
    }

    public function withdrawBalance($validated,$testMode)
    {
        $token = $this->getToken($testMode);
        return $this->gateway->withdrawAccountBalance($validated, $testMode, $token);
    }

}
