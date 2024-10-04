<?php

function church_admin_app_stripe_public($currency,$amount,$what,$ID)
{ 
    
    if(empty($currency)){$currency='gbp';}
    $premium = get_option('church_admin_payment_gateway');
    if(empty($premium)){
        return esc_html('Please set up your payment gateway first');
       
    }
    


    $out='<div class="alignwide"><div id="stripe-wrapper">';
   
    require_once "vendor/autoload.php";
    
 
    $stripe = new \Stripe\StripeClient($premium['stripe_secret_key']);
 
    // creating setup intent
    $payment_intent = $stripe->paymentIntents->create([
        'payment_method_types' => ['card'],
 
        // convert double to integer for stripe payment intent, multiply by 100 is required for stripe
        'amount' => round($amount) * 100,
        'currency' => strtolower($currency),
    ]);
    $funds=get_option('church_admin_giving_funds');
    if(!empty( $funds) )
    {
        $out.='<div class="church-admin-form-group">
            <label>'.esc_html( __('Fund','church-admin' ) ).'</label><select class="church-admin-form-control" id="fund">';
        foreach( $funds AS $key=>$fund)
        {
            $out.='<option value="'.esc_attr( $fund).'">'.esc_html( $fund).'</option>';
        }
        $out.='</select></div>'."\r\n";
    }
    $out.='<p><label>Email</label><input type="email" id="user-email" value="" required/></p>';
    $out.='<p><label>Name</label><input type="text" id="user-name" value="" required/>';
    if(!empty($premium['gift_aid'])){
        $out.='<p><input type="checkbox" name="gift_aid" id="gift_aid" value=1> Gift aid this donation?</p>';
    }
    $out.='<input type="hidden" id="stripe-public-key" value="'.esc_attr($premium['stripe_public_key']).'" />';
    $out.='<input type="hidden" id="stripe-payment-intent" value="'.esc_attr($payment_intent->client_secret).'" />';
    $out.='<!-- credit card UI will be rendered here -->';
    $out.='<div id="stripe-card-element" style="margin-top: 20px; margin-bottom: 20px;width:300px"></div>';
    $out.='<p><button class="btn btn-success" type="button" id="stripe-button" >Pay via Stripe</button></p>';
    $out.='</div>';
    $out.='<div id="stripe-message"></div>';
    $out.='<!-- include Stripe library -->';
    $out.='<script src="https://js.stripe.com/v3/"></script>';
    $out.='<script>
    jQuery(document).ready(function($){
        // global variables
        var stripe = null;
        var cardElement = null;
    
        const stripePublicKey = $("#stripe-public-key").val();
    
        stripe = Stripe(stripePublicKey);
        var elements = stripe.elements();
        cardElement = elements.create("card");
        cardElement.mount("#stripe-card-element");
       $("body").on("click","#stripe-button",payViaStripe);

        function payViaStripe() {   
            // get stripe payment intent
            const stripePaymentIntent = document.getElementById("stripe-payment-intent").value;
        
            // execute the payment
            stripe
                .confirmCardPayment(stripePaymentIntent, {
                    payment_method: {
                            card: cardElement,
                            billing_details: {
                                "email": document.getElementById("user-email").value,
                                "name": document.getElementById("user-name").value,
                               
                            },
                        },
                    })
                    .then(function(result) {
        
                        // Handle result.error or result.paymentIntent
                        if (result.error) {
                            console.log(result);
                            $("#stripe-message").html(result.error.message);
                            
                        } else {
                            console.log("The card has been verified successfully...", result.paymentIntent.id);
        
                            // [call AJAX function here]
                            confirmPayment(result.paymentIntent.id);
                        }
                    });
            }
            function confirmPayment(paymentId) {
                $("#stripe-button").attr("disabled", "true");
                var fund = $("#fund").find(":selected").val()
                
                var what = "'.esc_html($what).'";
                var id ="'.(int)$ID.'";
                var gift_aid = 0;
                gift_aid = $("#gift_aid:checked").val();
                var args = {"payment_id":paymentId,"action":"church_admin_stripe_payment_process","what":what,"ID":id,"gift_aid":gift_aid,"fund":fund};
                console.log(args);
                $.ajax({
                    method:"POST",
                    url: ajaxurl,
                    data: args,
                    dataType: "json",
                    success:function(data) {
                        // This outputs the result of the ajax request
                        console.log(data);
                        $("#stripe-message").html(data.message);
                        $("#stripe-wrapper").html("");
                    },
                    error: function(errorThrown){
                        console.log(errorThrown);
                        $("#stripe-message").html(errorThrown);
                    }
                }); 
                
            }
            });    </script></div>';
    return $out;
}


function church_admin_stripe_delete( $gift_id){
    echo '<h3>'.esc_html(__('Stripe refund','church-admin')).'</h3>';
    echo  church_admin_which_stripe_mode();
    if(empty($gift_id) ||!church_admin_int_check($gift_id)){
        echo'<p>'. __('No donation specified','church-admin').'</p>';
        return;
    }
    $premium = get_option('church_admin_payment_gateway');
    if(empty($premium)){
        echo'<p>'. esc_html('Please set up your payment gateway first').'</p>';
       return;
    }
    global $wpdb;

    $donation = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE giving_id = "'.esc_sql($gift_id).'"');
    if(empty($donation)){
        echo'<p>'. __('Donation ID not found','church-admin').'</p>';
        return;
    }


    require_once "vendor/autoload.php";
    $stripe = new \Stripe\StripeClient($premium['stripe_secret_key']);

    try{  
        $response = $stripe->refunds->create([
        'charge' => esc_attr($donation->txn_id),
        ]);
       
        $refund_id = church_admin_sanitize($response->id);
        $amount = 0 - church_admin_sanitize($response->amount/100);
        

        $check = $wpdb->get_var('SELECT giving_id FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE txn_id="'.esc_sql($refund_id).'"');
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_giving_meta (txn_id,gross_amount,giving_id,txn_date,fund) VALUES("'.esc_sql($refund_id).'","'.esc_sql($amount).'","'.esc_sql($gift_id).'","'.esc_sql(wp_date('Y-m-d H:i:s')).'","'.esc_sql($donation->fund).'")');
        echo '<div class="notice notice-success">'.__('Donation refunded','church-admin').'</div>';
    }
    catch(\Stripe\Exception\CardException $e) {
        // Since it's a decline, \Stripe\Exception\CardException will be caught
        echo 'Status is:' . $e->getHttpStatus() . '\n';
        echo 'Type is:' . $e->getError()->type . '\n';
        echo 'Code is:' . $e->getError()->code . '\n';
        // param is '' in this case
        echo 'Param is:' . $e->getError()->param . '\n';
        echo 'Message is:' . $e->getError()->message . '\n';
      } catch (\Stripe\Exception\RateLimitException $e) {
        echo '<p>Too many requests made to the API too quickly</p>';
      } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo '<p>Invalid parameters were supplied to Stripe\'s API</p>';
      } catch (\Stripe\Exception\AuthenticationException $e) {
        echo '<p>Authentication with Stripe\'s API failed - (maybe you changed API keys recently)</p>';
      } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo '<p> Network communication with Stripe failed</p>';
      } catch (\Stripe\Exception\ApiErrorException $e) {
        echo '<p>somethimh went wrong with the API</p>';
        
      } catch (Exception $e) {
        echo '<p> Something else happened, completely unrelated to Stripe</p>';
      }
}

