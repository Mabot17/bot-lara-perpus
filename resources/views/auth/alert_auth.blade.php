@if(Session::has('success_register'))
<div class="fixed-bottom p-3">
    <div id="success-register-alert" class="alert text-white bg-success alert-dismissible fade show" role="alert">
        <div class="iq-alert-icon">
           <i class="ri-check-line"></i>
        </div>
        <div class="iq-alert-text">{{ Session::get('success_register') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="ri-close-line"></i>
        </button>
    </div>
</div>
<script>
    setTimeout(function(){
        $('#success-register-alert').alert('close');
    }, 3000);
</script>
@endif
@if(Session::has('failed_login'))
<div class="fixed-bottom p-3">
    <div class="alert alert-danger" role="alert" id="failed-login-alert">
        <div class="iq-alert-icon">
           <i class="ri-information-line"></i>
        </div>
        <div class="iq-alert-text">{{ Session::get('failed_login') }}</div>
    </div>
</div>
<script>
    setTimeout(function(){
        $('#failed-login-alert').alert('close');
    }, 3000);
</script>
@endif
