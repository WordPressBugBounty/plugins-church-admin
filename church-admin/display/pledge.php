<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*************************************
*
*		Paypal Pledge Form
*
**************************************/
function church_admin_pledge_form()
{
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
    global $current_user,$wpdb;
    $current_user=wp_get_current_user();
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID .'"');
    $currYear=date('Y');
    $out='';
    if(!is_user_logged_in() )
    {
        $out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.esc_url(wp_lostpassword_url(get_permalink() )).'" title="Lost Password">'.esc_html(__('Help! I don\'t know my password','church-admin')).'</a></p>';
        return $out;
    }
        
    if(!empty( $_POST['save-pledge'] )&& wp_verify_nonce( $_POST['save-pledge'],'save-pledge') )
    {
        $amount=floatval( sanitize_text_field(stripslashes($_POST['amount'] ) )  );
        $fund=sanitize_text_field( stripslashes($_POST['fund'] ) ) ;
        
        $pledge_id=$wpdb->get_var('SELECT pledge_id FROM '.$wpdb->prefix.'church_admin_pledge WHERE people_id="'.(int)$person->people_id.'" AND fund="'.esc_sql($fund).'" AND pledge_year="'.(int) $currYear.'"');
        
        if(!empty( $pledge_id) )
        {//update
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_pledge SET amount="'.esc_sql($amount).'",people_id="'.(int)$person->people_id.'" , fund="'.esc_sql($fund).'" , pledge_year="'.(int)$currYear.'" WHERE pledge_id="'.(int) $pledge_id.'"');
        }
        else
        {//insert
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_pledge (amount,people_id,fund,pledge_year)VALUES("'.$amount.'","'.(int)$person->people_id.'","'.esc_sql($fund).'" ,"'.(int)$currYear.'")');
        }
        $out.='<div class="ca-donate-form"><div class="ca-tabs ca-active-tab">'.esc_html(sprintf(__('Pledge Form for %1$s','church-admin' ) ,$currYear)).'</div><div class="ca-row ">'.esc_html(__('Thank you for your pledge','church-admin')).'</div></div>';                 
    }
    else
    {
        
        
        $currentPledge=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_pledge WHERE people_id="'.(int)$person->people_id.'" AND pledge_year="'.esc_sql($currYear).'"');
        
        $out.='<h2>'.esc_html(sprintf(__('Pledge Form for %1$s','church-admin' ) ,$currYear) ).'</h2><form action="" method="POST">';
        if( $currentPledge)  {$out.='<p>'.esc_html( __('Your current pledge is shown below, which you can change','church-admin' ) ).'</p>';}
        
        
        //Amount
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Pledge Amount','church-admin' ) ).'        </label><input type="number" name="amount"  required="required" class="church-admin-form-control" ';
        if(!empty( $currentPledge->amount) )$out.='value="'.$currentPledge->amount.'"';
        $out.='/></div>';
        //Hidden people id
        
        if(!empty( $person->people_id) )
        {
            $out.='<input type="hidden" name="custom" value="'.(int)$person->people_id.'" />';
        }
        //funds
        $funds=get_option('church_admin_giving_funds');
        if(!empty( $funds) )
        {
            $out.='<div class="church-admin-form-group">
                <label>'.esc_html( __('Fund','church-admin' ) ).'</label><select class="church-admin-form-control" name="fund">';
            foreach( $funds AS $key=>$fund)
            {
                $out.='<option value="'.esc_html( $fund).'">'.esc_html( $fund).'</option>';
            }
            $out.='</select></div>';
        }
        $out.='<div class="church-admin-form-group"><input type="hidden" name="save-pledge" value="'.wp_create_nonce('save-pledge').'" /><input type="submit" value="'.esc_html( __('Save pledge','church-admin' ) ).'"  class="ca-donate-submit" /></div>';
        $out.='</form>';
        //current donations
        $amount=0;
        if(!empty( $currentPledge->amount) )  {$amount=$currentPledge->amount;}
        $out.=church_admin_current_donations( $person->people_id,$currYear,$amount);
        
        $out.='</div>';
    }
    return $out;
}