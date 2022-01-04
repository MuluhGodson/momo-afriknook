<div>
    <div class="row">
        <div class="card col">
            <img style="width: 90px" class="card-img-top img-thumbnail rounded-full mx-auto d-block" src="{{ url('vendor/core/plugins/momo/images/mobilemoney.jpg') }}" alt="MTN and Orange">
            <div class="card-body">
                <h5 class="card-title text-center">Total Balance</h5>
                 <p id="total-balance" class="text-center"></p>
            </div>
        </div>
        <div class="card col">
            <img style="width: 60px" class="card-img-top img-thumbnail rounded-full mx-auto d-block" src="{{ url('vendor/core/plugins/momo/images/mtn.png') }}" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title text-center">MTN Balance</h5>
                 <p id="mtn-balance" class="text-center"></p>
            </div>
        </div>
        <div class="card col">
            <img style="width: 60px" class="card-img-top img-thumbnail rounded-full mx-auto d-block" src="{{ url('vendor/core/plugins/momo/images/orange.jpg') }}" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title text-center">Orange Balance</h5>
                <p id="orange-balance" class="text-center"></p>
            </div>
        </div>
    </div>
    <div id="withdraw-form" class="my-2">
        <form action="{{ route('payments.momo.withdraw') }}" method="post">
            <div class="form-group">
                <label for="tel">Mobile Money Number</label>
                <input type="text" class="form-control cam-tel" id="momo-tel" name="momo_tel" placeholder="Enter tel" required>
                <small class="form-text text-muted">Enter the number you wish to withdraw to.</small>
            </div>
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="text" class="form-control cam-amount" id="momo-amount" name="momo_amount" required>
                <small class="form-text text-muted">Enter the amount you wish to withdraw. It should be less than the available balance.</small>
            </div>
        </form>
    </div>
    <div>
        <p id="api-msg"></p>
    </div>
</div>

 <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js" integrity="sha512-KaIyHb30iXTXfGyI9cyKFUIRSSuekJt6/vqXtyQKhQP6ozZEGY8nOtRS6fExqE4+RbYHus2yGyYg1BrqxzV6YA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/addons/cleave-phone.cm.js" integrity="sha512-9neLk6DqtD7MORwgrO6zcrBi2BzsAqas6lM6gRMNk7kYMvIy2F3M1wDEpeQ7DuYMn8gixAtoyLuIZhB/6njBwg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    var amountsCollection = document.getElementsByClassName("cam-amount");
    var amounts = Array.from(amountsCollection);
    amounts.forEach(function (el) {
        var cleave = new Cleave(el, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralPositiveOnly: true,
            prefix: 'XAF ',
            rawValueTrimPrefix: true,
        });
    });
    var cleave = new Cleave('.cam-tel', {
        phone: true,
        phoneRegionCode: 'CM',
        prefix: '+237',
        noImmediatePrefix: true,
    });
</script>