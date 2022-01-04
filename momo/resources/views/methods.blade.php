@if (get_payment_setting('status', MoMo_PAYMENT_METHOD_NAME) == 1)
    <li class="list-group-item">
        <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_momo"
               value="{{ MoMo_PAYMENT_METHOD_NAME }}" data-bs-toggle="collapse" data-bs-target=".payment_momo_wrap"
               data-parent=".list_payment_method"
               @if (setting('default_payment_method') == MoMo_PAYMENT_METHOD_NAME) checked @endif
        >
        <label for="payment_momo">{{ get_payment_setting('name', MoMo_PAYMENT_METHOD_NAME) }}</label>

        <div class="payment_momo_wrap payment_collapse_wrap collapse @if (setting('default_payment_method') == MoMo_PAYMENT_METHOD_NAME) show @endif">
            <div class="mt-3 form-group">
                <label for="amount">Amount</label>
                <p>{{ $amount }} {{ $currency }}</p>
                <input type="text" value="{{ currency($amount, $currency, 'XAF') }}" class="form-control disabled input-lg" disabled>
            </div>
            <div class="mt-3 form-group">
                <label for="momo_number">Enter Mobile Money Number (MTN or Orange)</label>
                <input type="text" class="form-control cam-tel input-lg" name="momo-tel" id="momo-tel" placeholder="+2376xxxxxxxx">
            </div>
        </div>
    </li>

   

@endif

 <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js" integrity="sha512-KaIyHb30iXTXfGyI9cyKFUIRSSuekJt6/vqXtyQKhQP6ozZEGY8nOtRS6fExqE4+RbYHus2yGyYg1BrqxzV6YA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/addons/cleave-phone.cm.js" integrity="sha512-9neLk6DqtD7MORwgrO6zcrBi2BzsAqas6lM6gRMNk7kYMvIy2F3M1wDEpeQ7DuYMn8gixAtoyLuIZhB/6njBwg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    var cleave = new Cleave('.cam-tel', {
        phone: true,
        phoneRegionCode: 'CM',
        prefix: '+237',
        noImmediatePrefix: true,
    });
</script>