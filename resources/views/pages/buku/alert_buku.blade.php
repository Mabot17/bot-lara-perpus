@if(Session::has('success_insert_mhs'))
<div class="fixed-bottom p-3">
    <div id="success-insert-alert" class="alert text-white bg-success alert-dismissible fade show" role="alert">
        <div class="iq-alert-icon">
           <i class="ri-check-line"></i>
        </div>
        <div class="iq-alert-text">{{ Session::get('success_insert_mhs') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="ri-close-line"></i>
        </button>
    </div>
</div>
<script>
    setTimeout(function(){
        $('#success-insert-alert').alert('close');
    }, 3000);
</script>
@endif

@if(Session::has('success_update_mhs'))
<div class="fixed-bottom p-3">
    <div id="success-update-alert" class="alert text-white bg-success alert-dismissible fade show" role="alert">
        <div class="iq-alert-icon">
           <i class="ri-check-line"></i>
        </div>
        <div class="iq-alert-text">{{ Session::get('success_update_mhs') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="ri-close-line"></i>
        </button>
    </div>
</div>
<script>
    setTimeout(function(){
        $('#success-update-alert').alert('close');
    }, 3000);
</script>
@endif

@if(Session::has('delete_mhs'))
<div class="fixed-bottom p-3">
    <div id="delete-alert" class="alert text-white bg-success alert-dismissible fade show" role="alert">
        <div class="iq-alert-icon">
           <i class="ri-check-line"></i>
        </div>
        <div class="iq-alert-text">{{ Session::get('delete_mhs') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="ri-close-line"></i>
        </button>
    </div>
</div>
<script>
    setTimeout(function(){
        $('#delete-alert').alert('close');
    }, 3000);
</script>
@endif
