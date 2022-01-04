@php $momopayStatus = get_payment_setting('status', MoMo_PAYMENT_METHOD_NAME); @endphp
<table class="table payment-method-item">
    <tbody>
    <tr class="border-pay-row">
        <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
        <td style="width: 20%;">
            <img src="{{ url('vendor/core/plugins/momo/images/mobilemoney.jpg') }}" alt="Mobile Money Payment Plugin by Muluh MG Godson" height="40">
        </td>
        <td class="border-right">
            <ul>
                <li>
                    <a href="https://www.linkedin.com/in/muluh-mg-godson/" target="_blank">{{ __('Mobile Money') }}</a>
                    <p>{{ __('Customer can buy product and pay via Mobile Money') }}</p>
                </li>
            </ul>
        </td>
    </tr>
    </tbody>
    <tbody class="border-none-t">
    <tr class="bg-white">
        <td colspan="3">
            <div class="float-start" style="margin-top: 5px;">
                <div
                    class="payment-name-label-group @if (get_payment_setting('status', MoMo_PAYMENT_METHOD_NAME) == 0) hidden @endif">
                    <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span> <label
                        class="ws-nm inline-display method-name-label">{{ get_payment_setting('name', MoMo_PAYMENT_METHOD_NAME) }}</label>
                </div>
            </div>
            <div class="float-end">
                <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($momopayStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($momopayStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
            </div>
        </td>
    </tr>
    <tr class="paypal-online-payment payment-content-item hidden">
        <td class="border-left" colspan="3">
            {!! Form::open() !!}
            {!! Form::hidden('type', MoMo_PAYMENT_METHOD_NAME, ['class' => 'payment_type']) !!}
            <div class="row">
                <div class="col-sm-6">
                    <ul>
                        <li>Create an Application on the CamPay Dashboard   <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => 'Momo']) }}</label>
                        </li>
                        <li class="payment-note">
                            <p>{{ trans('plugins/payment::payment.configuration_requirement', ['name' => 'Momo']) }}
                                :</p>
                            <ul class="m-md-l" style="list-style-type:decimal">
                                <li style="list-style-type:decimal">
                                    <p>Create an Account at any MoMo Vendor (For now, this plugin is configured to work with CamPay).</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>Create an Application on the CamPay Dashboard</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>Copy the App ID, App Username, App Password and the App Webhook Key and paste in their respective fields.</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>To use the Withdrawals feature, activate withdrawals from your application settings on CamPay.</p>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <div class="well bg-white">
                        <div class="form-group">
                            <label class="text-title-field"
                                   for="momopay_name">{{ trans('plugins/payment::payment.method_name') }}</label>
                            <input type="text" class="next-input" name="payment_{{ MoMo_PAYMENT_METHOD_NAME }}_name"
                                   id="momopay_name" data-counter="400"
                                   value="{{ get_payment_setting('name', MoMo_PAYMENT_METHOD_NAME, __('Online payment via Mobile Money')) }}">
                        </div>
                        <p class="payment-note">
                            {{ trans('plugins/payment::payment.please_provide_information') }} - Mobile Money:
                        </p>
                        <div class="form-group">
                            <label class="text-title-field" for="momo_app_id">{{ __('App ID') }}</label>
                            <input type="text" class="next-input"
                                   name="payment_momo_app_id" id="momo_app_id"
                                   value="{{ get_payment_setting('app_id', MoMo_PAYMENT_METHOD_NAME) }}">
                        </div>
                        <div class="form-group">
                            <label class="text-title-field" for="momo_app_username">{{ __('App Username') }}</label>
                            <input type="text" class="next-input" id="momo_app_username"
                                   name="payment_momo_app_username"
                                   value="{{ get_payment_setting('app_username', MoMo_PAYMENT_METHOD_NAME) }}">
                        </div>
                        <div class="form-group">
                            <label class="text-title-field" for="momo_app_password">{{ __('App Password') }}</label>
                            <input type="password" class="next-input" placeholder="••••••••" id="momo_app_password"
                                   name="payment_momo_app_password"
                                   value="{{ get_payment_setting('app_password', MoMo_PAYMENT_METHOD_NAME) }}">
                        </div>
                        <div class="form-group">
                            <label class="text-title-field" for="momo_webhook_key">{{ __('App Webhook Key') }}</label>
                            <input type="password" class="next-input" placeholder="••••••••" id="momo_webhook_key"
                                   name="payment_momo_webhook_key"
                                   value="{{ get_payment_setting('webhook_key', MoMo_PAYMENT_METHOD_NAME) }}">
                        </div>
                        {!! Form::hidden('payment_'.MoMo_PAYMENT_METHOD_NAME.'_mode', 1) !!}
                        <div class="form-group">
                            <label class="next-label">
                                <input type="checkbox" class="hrv-checkbox" value="0" name="payment_momo_mode"
                                 @if (get_payment_setting('mode', MoMo_PAYMENT_METHOD_NAME) == 0) checked @endif>
                                {{ trans('plugins/payment::payment.sandbox_mode') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 bg-white d-flex justify-content-end">
                 <div>
                    <button class="btn btn-success @if ($momopayStatus == 0) hidden @endif btn-trigger-withdraw"
                            type="button">Withdraw</button>
                </div>
                <div>
                    <button class="btn btn-warning disable-payment-item @if ($momopayStatus == 0) hidden @endif"
                            type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                </div>
                <div>
                    <button
                        class="btn btn-info save-payment-item btn-text-trigger-save @if ($momopayStatus == 1) hidden @endif"
                        type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                </div>
                <div>
                    <button
                        class="btn btn-info save-payment-item btn-text-trigger-update @if ($momopayStatus == 0) hidden @endif"
                        type="button">{{ trans('plugins/payment::payment.update') }}</button>
                </div>
            </div>
            {!! Form::close() !!}
        </td>
    </tr>
    </tbody>
</table>

{!! Form::modalAction('confirm-withdraw-modal-mg', 'Momo Withdrawal Form', 'info', view('plugins/momo::withdraw.index')->render(),'confirm-momo-withdraw-button', 'Withdraw') !!}

<script>
    $(document).on('click', '.btn-trigger-withdraw', event => {
        event.preventDefault();
        let _self = $(event.currentTarget);
        _self.addClass('button-loading');
        $('#confirm-withdraw-modal-mg').modal('show');
        $('#withdraw-form').hide();
        $('#api-msg').html('Loading data...').show();
        $('#confirm-momo-withdraw-button').addClass('disabled');
        $.ajax({
            type: 'GET',
            cache: false,
            url: "{{ route('payments.momo.balance') }}",
            success: res => {
                
                if (!res.error) {
                    $('#main-order-content').load(window.location.href + ' #main-order-content > *');
                    Botble.showSuccess(res.message);
                    const apiResponse = JSON.parse(res);
                    const totalBalance = apiResponse.total_balance;
                    const mtnBalance = apiResponse.mtn_balance;
                    const orangeBalance = apiResponse.orange_balance;
                    const currency = apiResponse.currency;
                    $('#total-balance').html(totalBalance + ' ' + currency);
                    $('#mtn-balance').html(mtnBalance + ' ' + currency);
                    $('#orange-balance').html(orangeBalance + ' ' + currency);
                    $('#withdraw-form').show();
                    $('#api-msg').hide();
                    $('#confirm-momo-withdraw-button').removeClass('disabled');
                    //_self.closest('.modal').modal('hide');
                } else {
                     $('#api-msg').html(res.message).show();
                     $('#withdraw-form').hide();
                     $('#confirm-momo-withdraw-button').addClass('disabled');
                    Botble.showError(res.message);
                }
                _self.removeClass('button-loading');
            },
            error: res => {
                $('#withdraw-form').hide();
                $('#confirm-momo-withdraw-button').addClass('disabled');
                 $('#api-msg').html(res.message).show();
                Botble.handleError(res);
                _self.removeClass('button-loading');
            }
        });
    });
    $(document).on('click', '#confirm-momo-withdraw-button', event => {
            event.preventDefault();
            let _self = $(event.currentTarget);
            _self.addClass('button-loading');
            var formData = {
                momoTel: $('#momo-tel').val(),
                momoAmount: $('#momo-amount').val(),
                _token: "{{ csrf_token() }}",
            }
            $.ajax({
                type: 'POST',
                cache: false,
                url:  "{{ route('payments.momo.withdraw') }}",
                data: formData,
                success: res => {
                    if (!res.error) {
                        $('#main-order-content').load(window.location.href + ' #main-order-content > *');
                        Botble.showSuccess(res.message);
                        _self.closest('.modal').modal('hide');
                    } else {
                        console.log(res.message)
                        Botble.showError(res.message);
                    }
                    _self.removeClass('button-loading');
                },
                error: res => {
                    Botble.handleError(res);
                    _self.removeClass('button-loading');
                }
            });
        });

        
</script>
