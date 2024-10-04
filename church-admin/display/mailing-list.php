<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_mailing_list($member_type_id=1){

    global $wpdb;
    $out='';
    if(!empty($_POST['save']) 
        && wp_verify_nonce( $_POST['field7'],'contact-form')
        && empty($_POST['field4'])
        && !empty($_POST['field5'])    
    ){
        $first_name = !empty($_POST['field1']) ? church_admin_sanitize($_POST['field1']):null;
        $last_name = !empty($_POST['field2']) ? church_admin_sanitize($_POST['field2']):null;
        $email = !empty($_POST['field3']) ? church_admin_sanitize($_POST['field3']):null;
        if(empty($first_name)||empty($last_name)||empty($email)||!is_email($email))
        {
            $out.='<p>'.__('Some of the form fields were not correctly filled out, please press backand try again.','church-admin').'</p>';
            return $out;
        }


        $check = $wpdb->get_var('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql($email).'"');
        if(!empty($check)){
            $out.='<p>'.__('You are already on the system, please try logging in to adjust your email preferences.','church-admin').'</p>';
            
            $out.='<p>'.esc_html( __("Please enter your email address to be emailed a temporary login token",'church-admin' ) ).'</p>';
            $out.='<form action="" method="GET">';
            $out.=wp_nonce_field('email-settings');
            $out.='<p><input type="email" value="'.esc_attr($email).'" name="email-address" /><input type="hidden"  name="action" value="user-email-settings" /></p><p><input type="submit" value="'.esc_html( __("Go",'church-admin' ) ).'" /></p></form>';
            
            return $out;

        }
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,lat,lng,privacy)VALUES(null, null, null,1)');
        $household_id=$wpdb->insert_id;
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,member_type_id,people_type_id,head_of_household,email_send,show_me,household_id)VALUES("'.esc_sql($first_name).'","'.esc_sql($last_name).'","'.esc_sql($email).'","'.(int)$member_type_id.'","1","1","1","0","'.(int)$household_id.'")');
        $people_id=$wpdb->insert_id;
        if(!empty($people_id)){
            church_admin_email_confirm($people_id);
            $out.='<p>'.esc_html(__('Thank you. A confirmation email has been sent to your email address, to confirm your email. Please click on the button in the email.','church-admin')).'</p>';
        }else{
            $out.='<p>'.__('Something else went wrong.','church-admin').'</p>';
            return $out;
        }

    }
    else{

        $out .='<form action="'.get_permalink().'" method="POST">';
        $out.= '<div class="church-admin-form-group"><label>'.esc_html(__('First name','church-admin')).'</label><input class="church-admin-form-control" required="required" type="text" name="field1"></div>';
        $out.= '<div class="church-admin-form-group"><label>'.esc_html(__('Last name','church-admin')).'</label><input class="church-admin-form-control" required="required" type="text" name="field2"></div>';
        $out.= '<div class="church-admin-form-group"><label>'.esc_html(__('Email','church-admin')).'</label><input class="church-admin-form-control" required="required" type="text" name="field3"></div>';
        //honeypot
        $out.='<div class="church-admin-form-group contact_form_comment"><label>Extra field</label><input type="text" name="field4" class="church-admin-form-control"></div>';
        $out.='<div class="comment"></div>';
        $out.='<noscript>'.esc_html( __('This contact form only works with Javascript enabled','church-admin')).'</noscript>
        <script>
                var funkybit = document.querySelector(".comment"); 
                var FN = document.createElement("input"); 
                    FN.setAttribute("type", "hidden"); 
                    FN.setAttribute("name", "field5"); 
                    FN.setAttribute("value", "haha");
                    funkybit.appendChild(FN);
            </script>';
        $out.=wp_nonce_field('contact-form','field7',FALSE,FALSE);
        $out.='<p><input type="hidden" name="save" value="yes"><input class="button red" type="submit" value="'.esc_attr('Join email list','church-admin').'"></p></form>';
    }
return $out;

}