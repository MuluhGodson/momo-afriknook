<?php

use Botble\Ecommerce\Models\Currency;

if (!defined('MoMo_PAYMENT_METHOD_NAME')) {
    define('MoMo_PAYMENT_METHOD_NAME', 'momo');
}

if (!defined('MOMOPAY_PAYMENT_SLAT_HASHID')) {
    define('MOMOPAY_PAYMENT_SLAT_HASHID', '12345678987654321qwertyuioplkjhgfdcvgbtrds');
}

if (!defined('MOMOPAY_PAYMENT_ALPHABET_HASHID')) {
    define('MOMOPAY_PAYMENT_ALPHABET_HASHID', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
}


if (!function_exists('convert_amount_to_XAF')) {
    /**
     * @return int|null
     */
    function convert_amount_to_XAF($amount)
    {
        return currency($amount, $from = get_application_currency()->title, $to = "XAF", $format=false);
    }
}
