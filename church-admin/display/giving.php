<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*************************************
*
*   Fund so far
*
**************************************/
function church_admin_fund_so_far( $fund=NULL,$start_date=NULL,$target=0)
{
    $licence =get_option('church_admin_app_new_licence');
    if($licence!='standard' && $licence!='premium'){
        return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
        
    }
    global $wpdb,$church_admin_url;
    $out='';
    $licence=get_option('church_admin_new_app_licence');
    if(empty($licence)){
        $out.='<p><a href="'.wp_nonce_url($church_admin_url.'&action=upgrade','upgrade').'">'.esc_html(__('Please upgrade to premium to use this feature','church-admin')).'</a></p>';
        return $out;
    }
    $premium=get_option('church_admin_payment_gateway');
    if ( empty( $premium) ){

        $out.='<p><a href="'.wp_nonce_url($church_admin_url.'&action=payment-gateway-setup','payment-gateway-setup').'">'.esc_html(__('Please setup PayPal giving','church-admin')).'</a></p>';
        return $out;
    }
    if(empty($premium['gateway'])){

        $out.='<p><a href="'.wp_nonce_url($church_admin_url.'&action=payment-gateway-setup','payment-gateway-setup').'">'.esc_html(__('Please setup PayPal giving','church-admin')).'</a></p>';
        return $out;

    }
    $gateway_errors=array();
    switch($premium['gateway'])
    {

        case 'stripe':
            if(empty($premium['stripe_public_key'])){
                $gateway_errors[]=__('Stripe public key required','church-admin');

            }
            if(empty($premium['stripe_secret_key'])){
                $gateway_errors[]=__('Stripe secret key required','church-admin');
                
            }
            if(empty($premium['currency_symbol'])){
                $gateway_errors[]=__('Currency symbol required','church-admin');
                
            }

        break;
        case 'paypal':
            if(empty($premium['paypal_email'])){
                $gateway_errors[]=__('Currency symbol required','church-admin');
                
            }
        break;
    }
    if(empty($premium['paypal_currency'])){
        $gateway_errors[]=__('Currency required','church-admin');
        
    }

    if(!empty($gateway_errors)){

        $out.='<div class="notice notice-danger"><h2>'.esc_html('Some payment gateway setup is required','church-admin').'</h2>';
        $out.=implode('<br>',$gateway_errors);
        $out.='</div>';
        return $out;
    }
    if(!empty( $start_date) )$sqldate=date('Y-m-d',strtotime( $start_date) );
    $sql='SELECT SUM(gross_amount) FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE 1=1 ';
    if(!empty( $fund) ) $sql.=' AND fund="'.esc_sql( $fund).'" ';
    if(!empty( $sqldate) )$sql.=' AND donation_date>"'.esc_sql( $sqldate).'"';
    $amount=$wpdb->get_var( $sql);
    if(!empty( $amount) )
    {
        if(!empty( $fund)&&!empty( $target)&&!empty( $start_date) )
        {
            $meterTarget=(int)str_replace( [',','.'], '', $target )*1.0;
            $meterAmount=(int)str_replace( [',','.'], '', $amount )*1.0;
            $since=date(get_option('date_format'),strtotime( $start_date) );
            $out='<p><meter min=0 max="'.esc_attr($meterTarget).'" value="'.esc_attr($meterAmount).'"></meter></p>';
            $out.='<p>'.esc_html(sprintf(__('%1$s out of %2$s for "%3$s" raised so far since %4$s','church-admin' ) ,$premium['currency_symbol'].$amount,$premium['currency_symbol'].$target,esc_html( $fund),$since)).'</p>';
        }
        elseif(!empty( $fund)&&!empty( $start_date) )
        {
            $meterTarget=(int)str_replace( [',','.'], '', $target )*1.0;
            $meterAmount=(int)str_replace( [',','.'], '', $amount )*1.0;
            $since=date(get_option('date_format'),strtotime( $start_date) );
            
            $out='<p>'.esc_html(sprintf(__('%1$s for "%2$s" raised so far since %3$s','church-admin' ) ,$premium['currency_symbol'].$amount,esc_html( $fund),$since)).'</p>';
        }
        elseif(!empty( $fund)&&!empty( $target) )
        {
            $meterTarget=(int)str_replace( [',','.'], '', $target )*1.0;
            $meterAmount=(int)str_replace( [',','.'], '', $amount )*1.0;
            $out='<p><meter min=0 max="'.esc_attr($meterTarget).'" value="'.esc_attr($meterAmount).'"></meter></p>';
            $out.='<p>'.esc_html(sprintf(__('%1$s out of %2$s for "%3$s" raised so far','church-admin' ) ,$premium['currency_symbol'].$amount,$premium['currency_symbol'].$target, $fund)).'</p>';
        }
        elseif(!empty( $fund)&&empty( $target) )
        {
            $out='<p>'.esc_html(sprintf(__('%1$s for "%2$s" raised so far','church-admin' ) ,$premium['currency_symbol'].$amount,esc_html( $fund) ) ).'</p>';
        }
        else
        {
            $out='<p>'.esc_html(sprintf(__('%1$s raised so far','church-admin' ) ,$premium['currency_symbol'].$amount) ).'</p>';
        }
    }
    return $out;
}
/*************************************
*
*		Paypal Giving Form
*
**************************************/
function church_admin_giving_form( $fund=NULL,$monthly=TRUE)
{
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
    global $current_user,$wpdb,$church_admin_url;
    wp_enqueue_script('church-admin-giving-form');
   
    $premium=get_option('church_admin_payment_gateway');
    if ( empty( $premium) ){
        if(is_user_logged_in() &&church_admin_level_check('Giving')){
            $out='<p><a href="'.wp_nonce_url(admin_url().$church_admin_url.'&action=payment-gateway-setup','payment-gateway-setup').'">'.esc_html( __('Please setup PayPal giving','church-admin' ) ).'</a></p>';
        }else{
            $out='<p>'.esc_html(__('PayPal giving has not been set up yet','church-admin')).'</p>';
        }
        return $out;
    }
    $premium=get_option('church_admin_payment_gateway');
    if ( empty( $premium) ){

        $out='<p><a href="'.wp_nonce_url($church_admin_url.'&action=payment-gateway-setup','payment-gateway-setup').'">'.esc_html(__('Please setup payment gateway','church-admin')).'</a></p>';
        return $out;
    }
    if(empty($premium['gateway'])){

        $out='<p><a href="'.wp_nonce_url($church_admin_url.'&action=payment-gateway-setup','payment-gateway-setup').'">'.esc_html(__('Please setup payment gateway','church-admin')).'</a></p>';
        return $out;

    }
    $gateway_errors=array();
    switch($premium['gateway'])
    {

        case 'stripe':
            if(empty($premium['stripe_public_key'])){
                $gateway_errors[]=__('Stripe public key required','church-admin');

            }
            if(empty($premium['stripe_secret_key'])){
                $gateway_errors[]=__('Stripe secret key required','church-admin');
                
            }
            if(empty($premium['currency_symbol'])){
                $gateway_errors[]=__('Currency symbol required','church-admin');
                
            }

        break;
        case 'paypal':
            if(empty($premium['paypal_email'])){
                $gateway_errors[]=__('Currency symbol required','church-admin');
                
            }
        break;
    }
    if(empty($premium['paypal_currency'])){
        $gateway_errors[]=__('Currency required','church-admin');
        
    }

    if(!empty($gateway_errors)){

        $out='<div class="notice notice-danger"><h2>'.esc_html('Some payment gateway setup is required','church-admin').'</h2>';
        $out.=implode('<br>',$gateway_errors);
        $out.='<p><strong><a href="'.esc_url(admin_url().$church_admin_url.'&action=payment-gateway-setup').'">'.esc_html(__('Please setup payment gateway','church-admin')).'</a></strong</p>';
        
        $out.='</div>';
        return $out;
    }

    //safe to proceed


    switch($premium['gateway']){
        case 'paypal':
        default:
            $out= church_admin_paypal_giving($fund,$monthly);
        break;
        case 'stripe':
            $out= church_admin_stripe_giving();
        break;

    }
    return $out;
}


function church_admin_stripe_giving()
{
    $licence =get_option('church_admin_app_new_licence');
    if($licence!='standard' && $licence!='premium'){
        return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
        
    }
    global $wpdb;
    $premium=get_option('church_admin_payment_gateway');
    //church_admin_debug($premium);
    $out='';
    $out.=church_admin_which_stripe_mode();
   
    $ID=null;
    if(is_user_logged_in()){
        $user=wp_get_current_user();
        $ID = $wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    }
    if(!empty($_POST['amount']) && is_numeric($_POST['amount']))
    {
        $amount = church_admin_sanitize($_POST['amount']);
        require_once(plugin_dir_path(dirname(__FILE__) ).'stripe/index.php');
        $out .= church_admin_app_stripe_public(strtolower($premium['paypal_currency']),$amount,'giving',$ID);
    }
    else
    {
        $out.='<form action="'.get_permalink().'" method="POST">';
        
        $out.='<div class="church-admin-form-group"><label>'.esc_html(__('Donation amount','church-admin')).'*</label><input type="number" required="required" class="church-admin-form-control amount" name="amount" /></div>'."\r\n";
        $out.='<p><input type="submit" value="'.esc_html(__('Give')).'" class="button"></p>';
        $out.='</form>';
   
    }
    return $out;
}

function church_admin_paypal_giving($fund,$monthly)
{
    $premium=get_option('church_admin_payment_gateway');
    $out='';
    if(CA_PAYPAL=="https://www.sandbox.paypal.com/cgi-bin/webscr")$out.='<p>SANDBOX MODE</p>';
    $out.='<div class="ca-donate-form">'."\r\n";
    $out.='<div class="ca-tabs">'."\r\n";
	if( $monthly)
    {
        $out.='<div class="ca-tab ca-active-tab" id="recurring">'.esc_html(__('Give Monthly','church-admin')).'</div><div class="ca-tab" id="once" >'.esc_html(__('Give Once','church-admin')).'</div>'."\r\n";
    }
    $out.='</div>'."\r\n";
    $out.='<div class="ca-giving-form">';
	$out.='<form action="'.CA_PAYPAL.'" method="post">'."\r\n";
    $out.='<input type="hidden" name="notify_url" value="'.esc_url(site_url().'/wp-admin/admin-ajax.php?action=church_admin_paypal_giving_ipn').'" />';
    $out.='<input type="hidden" name="currency_code" value="'.esc_attr($premium['paypal_currency']).'" />'."\r\n";
    $out.='<input type="hidden" name="charset" value="utf-8" />'."\r\n";
    if(!empty( $fund) )
    {
         $out.='<h2>'.esc_html(__(sprintf(__('Donate to %1$s','church-admin' ) ,$fund) )).'<input type="hidden" name="item_name" value="'.esc_html( $fund).'" /></h2>'."\r\n";
    }
    
    $out.='<input type="hidden" name="no_shipping" value="2"><input type="hidden" name="business" value="'.esc_html( $premium['paypal_email'] ).'">'."\r\n";
    if( $monthly)
    {
        /****************
        *   Monthly
        *****************/
        $out.='<input type="hidden" name="cmd" class="cmd" value="_xclick-subscriptions" /><input type="hidden" class="ca-recurring"  name="p3" value="1" /><input type="hidden" class="ca-recurring" name="t3" value="M" /><input type="hidden" class="ca-recurring" name="src" value="1" />';
         $out.='<div class="church-admin-form-group"><label>'.esc_html(__('Donation amount','church-admin')).'*</label><input type="number" required="required" class="church-admin-form-control amount" name="a3" /></div>'."\r\n";
    }
    else
    {
        /****************
        *   One Off Only
        *****************/
        $out.='<input type="hidden" name="cmd" class="cmd" value="_donations" />'."\r\n";
        $out.='<div class="church-admin-form-group"><label>'.esc_html(__('Donation amount','church-admin')).'*</label><input type="number" required="required" class="church-admin-form-control amount" name="amount" /></div>'."\r\n";
    }
    
   
    if( $premium['gift_aid'] )
    {
        $out.='<div class="church-admin-form-group"><label>Boost your donation by 25p of Gift Aid for every £1 you donate</label></div>'."\r\n";
        $out.='<div class="church-admin-form-group"><label>Gift Aid is reclaimed by the charity from the tax you pay for the current tax year.</label></div>'."\r\n";
        $out.='<div class="church-admin-form-group"><input type="checkbox"  name="custom" value="gift-aid" /> I want to Gift Aid my donation and any donations I make in the future or have made in the past 4 years to the church.</div><div class="church-admin-form-group"><label>Make sure you share your address on the Paypal payment page.</label></div>'."\r\n";
    }
    
    
    if ( empty( $fund) )
    {
        $funds=get_option('church_admin_giving_funds');
        if(!empty( $funds) )
        {
            $out.='<div class="church-admin-form-group">
                <label>'.esc_html( __('Fund','church-admin' ) ).'</label><select class="church-admin-form-control" name="item_name">';
            foreach( $funds AS $key=>$fund)
            {
                $out.='<option value="'.esc_attr( $fund).'">'.esc_html( $fund).'</option>';
            }
            $out.='</select></div>'."\r\n";
        }
    }
    $out.='<div class="church-admin-form-group">
            <label>'.esc_html( __('Email Address','church-admin' ) ).'*</label>
			<input type="email" required="required" class="church-admin-form-control" name="payer_email" ';
    if(!empty( $person->email) ) $out.=' value="'.esc_attr( $person->email).'" ';
    $out.='/></div>'."\r\n";
    if( $monthly)
    {
        $out.='<div class="church-admin-form-group"><label>'.esc_html(__("You don't need a PayPal account to donate by card",'church-admin')).'</label><input type="submit" value="'.esc_html(__('Give monthly','church-admin')).'"  class="ca-donate-submit" /><img alt="" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" /></div>'."\r\n";
    }
    else
    {
        $out.='<div class="church-admin-form-group"><label>'.esc_html(__("You don't need a PayPal account to donate by card",'church-admin')).'</label><input type="submit" value="'.esc_html( __('Donate','church-admin' ) ).'"  class="ca-donate-submit"><img alt="" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" /></div>'."\r\n";
    }
    $out.='<div class="church-admin-form-group"><label>* '.esc_html(__('Required fields','church-admin')).'</label></div>'."\r\n";
	$out.='</form>'."\r\n";
    $out.='</div><!--.ca-giving-form-->'."\r\n";
    $out.='</div><!--.ca-donate-form-->'."\r\n";
    return $out;
}