<?php

namespace MG\MoMo\Providers;

use Botble\Ecommerce\Repositories\Interfaces\OrderAddressInterface;
use Botble\Payment\Enums\PaymentMethodEnum;
use Html;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Throwable;
use MG\MoMo\Services\Gateways\MoMoService;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerMoMoMethod'], 126, 2);
        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithMoMo'], 126, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 126);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['MoMo'] = MoMo_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 126, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MoMo_PAYMENT_METHOD_NAME) {
                $value = 'MoMo';
            }

            return $value;
        }, 126, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MoMo_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 126, 2);
    }

    /**
     * @param string $settings
     * @return string
     * @throws Throwable
     */
    public function addPaymentSettings($settings)
    {
        return $settings . view('plugins/momo::settings')->render();
    }

    /**
     * @param string $html
     * @param array $data
     * @return string
     */
    public function registerMoMoMethod($html, array $data)
    {
        return $html . view('plugins/momo::methods', $data)->render();
    }

    /**
     * @param Request $request
     * @param array $data
     */
    public function checkoutWithMoMo(array $data, Request $request)
    {
        if ($request->input('payment_method') == MoMo_PAYMENT_METHOD_NAME) {
            $data = $this->app->make(MoMoService::class)->execute($request);
            if ($data === false) {
                $message = $this->app->make(MoMoService::class)->getErrorMessage();
                abort(500, $message);
            }

            if ($data) {
                header('Location: ' . $data);
                exit;
            } else {
                abort(500);
            }
        }

        return $data;
    }
}
