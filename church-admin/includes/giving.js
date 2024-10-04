jQuery(document).ready(function( $) { 
$('.ca-tab').click(function() {
        $('.ca-tab').removeClass('ca-active-tab');
        $(this).addClass('ca-active-tab');
        var which=$(this).attr('id');
        console.log(which);
        switch(which)
        {
            case'recurring':
                $('.cmd').val('_xclick-subscriptions');
                $('.amount').prop('name','a3');
                $('.ca-donate-submit').val('Give monthly by PayPal');
                $('.ca-recurring').prop('disabled',false);
            break;
            case 'once':
                $('.cmd').val('_donations');
                $('.ca-donate-submit').val('Donate');
                $('.amount').prop('name','amount');
                $('.ca-recurring').prop('disabled',true);
            break;
        }
    });
  });