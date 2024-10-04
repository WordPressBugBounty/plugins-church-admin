<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_contact_public()
{
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='basic' && $licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
			
		}
    global $wpdb;
    //grab settings
    $out='';
    $settings=get_option('church_admin_contact_form_settings');
    
    /******************************************
     * field6 is javascript field - needs to be not empty
     * field7 is nonce
     * field8 is hidden honeypot
     ******************************************/
    church_admin_debug('**** CONTACT FORM ****');
        church_admin_debug( $_POST);
    if(!empty( $_POST['field6'] ) && $_POST['field6'] == 'haha' && empty( $_POST['field8'] ) && !empty( $_POST['field7'] ) && wp_verify_nonce( $_POST['field7'],'contact-form') )
    {
        
        $name=!empty($_POST['field1']) ? sanitize_text_field(stripslashes( $_POST['field1'] )):null;
        $email=!empty($_POST['field2']) ? sanitize_text_field( stripslashes($_POST['field2'] )):null;
        $phone=!empty( $_POST['field3'] )?sanitize_text_field( stripslashes($_POST['field3'] )):null;
        $subject=!empty( $_POST['field4'] )?sanitize_text_field( stripslashes($_POST['field4'] )):null;
        $message=!empty( $_POST['field5'] )?sanitize_text_field( stripslashes($_POST['field5'] )):null;
        $url = !empty( $_POST['field9'] )?sanitize_text_field( stripslashes($_POST['field9']) ):null;
        $errors=array();
        /**************************************
         * Spam checks
         **************************************/
        $thisURL=get_permalink();
      
        if( $thisURL!=$url)$errors[]=esc_html(__('Where are you coming from?','church-admin'));
        if ( empty( $_POST['field6'] ) )$errors[]=esc_html(__('Hello spammer','church-admin'));
        if ( empty( $name) )$errors[]=esc_html(__('Name field is required','church-admin'));
        if ( empty( $email) )$errors[]=esc_html(__('Email field is required','church-admin'));
        if(!is_email( $email) )$errors[]=esc_html(__('Email not recognised','church-admin'));
        if ( empty( $subject) )$errors[]=esc_html(__('Subject field is required','church-admin'));
        if ( empty( $message) )$errors[]=esc_html(__('Message field is required','church-admin'));
        if(!empty( $errors) ) return church_admin_contact_form( $_POST,$errors);
        // deeper checks
        if(str_word_count( $message,0)<2)$errors[]=esc_html(__('Message is not long enough','church-admin'));
        if(substr_count( $message, "https://") >= $settings['max_urls'] )$errors[]=esc_html(__('Message has too many URLs','church-admin'));
        if(substr_count( $message, "http://") >= $settings['max_urls'] )$errors[]=esc_html(__('Message has too many URLs','church-admin'));
        if(substr_count( $subject, "https://") >0)$errors[]=esc_html(__('URL in the subject is a bit spammy','church-admin'));
        if(substr_count( $subject, "http://") >0)$errors[]=esc_html(__('URL in the subject is a bit spammy','church-admin'));   
        if(church_admin_contact_form_strposa( $message, $settings['spam_words'],0) )$errors[]=esc_html(__('You used spammy words','church-admin'));  
        if(!empty( $errors) ) return church_admin_contact_form( $_POST,$errors);
        church_admin_debug( $_POST);
        church_admin_debug( $_SERVER);
        /**************************************
         *  Not spam
         *************************************/
       
        $date=date('Y-m-d H:i:s');
        $ip=$_SERVER['REMOTE_ADDR'];

        /*************************************
         *  Check if already saved
         *************************************/
        $contact_id=$wpdb->get_var('SELECT contact_id FROM '.$wpdb->prefix.'church_admin_contact_form WHERE name="'.esc_sql( $name).'" AND url="'.esc_sql( $url).'" AND email="'.esc_sql( $email).'" AND subject="'.esc_sql( $subject).'" AND message="'.esc_sql( $message).'" AND ip="'.esc_sql( $ip).'" AND post_date="'.esc_sql( $date).'"');

        if ( empty( $contact_id) )
        {
            /*****************************
             * Save message to database
             * ***************************/
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_contact_form (name,email,phone, subject,message,ip,post_date,url) VALUES("'.esc_sql( $name).'" ,"'.esc_sql( $email).'","'.esc_sql( $phone).'" ,"'.esc_sql( $subject).'", "'.esc_sql( $message).'","'.esc_sql( $ip).'","'.esc_sql( $date).'","'.esc_sql( $url).'")');

            /*****************************
            * email message to recipient
            ******************************/
            $emailSubject=__('Website message','church-admin');
            $emailTo=$settings['recipient'];
            $emailMessage='<table style="margin:20px 0px;border-collapse:collapse;">';
            $emailMessage.='<tr style="border:1px solid"><td style="border:1px solid;width:100px;">'.esc_html( __('Name','church-admin' ) ).'</td><td style="border:1px solid;">'.esc_html( $name).'</td></tr>';
            $emailMessage.='<tr style="border:1px solid"><td style="border:1px solid;width:100px;">'.esc_html( __('Email','church-admin' ) ).'</td><td style="border:1px solid;">'.esc_html( $email).'</td></tr>';
            if(!empty( $phone) )$emailMessage.='<tr style="border:1px solid"><td style="border:1px solid;width:100px;">'.esc_html( __('Phone','church-admin' ) ).'</td><td style="border:1px solid;">'.esc_html( $phone).'</td></tr>';
            $emailMessage.='<tr style="border:1px solid"><td style="border:1px solid;width:100px;">'.esc_html( __('Subject','church-admin' ) ).'</td><td style="border:1px solid;">'.esc_html( $subject).'</td></tr>';
            $emailMessage.='<tr style="border:1px solid"><td style="border:1px solid;width:100px;">'.esc_html( __('Message','church-admin' ) ).'</td><td style="border:1px solid;">'.esc_html( $message).'</td></tr>';
            $emailMessage.='<tr style="border:1px solid"><td style="border:1px solid;width:100px;">'.esc_html( __('Webpage visited','church-admin' ) ).'</td><td style="border:1px solid;">'.esc_url( $url).'</td></tr>';
            $emailMessage.='</table>';

            
            church_admin_email_send($emailTo,$emailSubject,$emailMessage,null,null,null,esc_html($name),esc_html($email),TRUE);
            /*****************************
            * push message 
            ******************************/
            if(!empty( $settings['pushToken'] ) )
            {
                church_admin_debug('Push token');
                $pushToken=$wpdb->get_var('SELECT pushToken FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$settings['pushToken'].'" AND pushToken!=""');
                church_admin_debug( $wpdb->last_query);
                if(!empty( $pushToken) )
                {
                    church_admin_debug('Sending to '.$pushToken);
                    /*****************************
                    * Send push message 
                    ******************************/
                    $pushTokens=array( $pushToken);
                    
                    $pushMessage=$dataMessage=__('New website contact form message',"church-admin");
                    $pushType='contact-form';
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/push.php');
                    church_admin_filtered_push( $pushMessage,$pushTokens,'',$dataMessage,$pushType,NULL);

                }
            
            }
           $out.='<p>'.esc_html( __('Thank you. Your message has been sent. We will get in touch soon','church-admin' ) ).'</p>';
        }

    }
    else $out=church_admin_contact_form(null,null);
    return $out;
}

function church_admin_contact_form( $data,$errors)
{
    $out='';
    if(!empty( $errors) )
    {
        //Display errors
        $out.='<p>'.implode("<br>",$errors).'</p>';
    }
    $out.='<form action="" method="POST">';
    //Name field
    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Name','church-admin' ) ).'</label>';
    $out.='<input class="church-admin-form-control" type="text" required="required" name="field1" ';
    if(!empty( $data['field1'] ) )$out.=' value="'.esc_html( $data['field1'] ).'" ';
    $out.='/></div>';
    //Email field
    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Email','church-admin' ) ).'</label>';
    $out.='<input class="church-admin-form-control" type="email" required="required" name="field2" ';
    if(!empty( $data['field2'] ) )$out.=' value="'.esc_html( $data['field2'] ).'" ';
    $out.='/></div>';
    //Phone field
    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Phone','church-admin' ) ).'</label>';
    $out.='<input class="church-admin-form-control" type="text" name="field3" ';
    if(!empty( $data['field3'] ) )$out.=' value="'.esc_html( $data['field3'] ).'" ';
    $out.='/></div>';
    //subject
    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Subject','church-admin' ) ).'</label>';
    $out.='<input class="church-admin-form-control" type="text" required="required" name="field4" ';
    if(!empty( $data['field4'] ) )$out.=' value="'.esc_html( $data['field4'] ).'" ';
    $out.='/></div>';
    //message
    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Message','church-admin' ) ).'</label>';
    $out.='<textarea class="church-admin-form-control" name="field5" style="height:100px;">';
    if(!empty( $data['field5'] ) )$out.=esc_html( $data['field5'] );
    $out.='</textarea></div>';
    //honeypot
    $out.='<div class="church-admin-form-group contact_form_comment"><label>Extra field</label><input type="text" name="field8" class="church-admin-form-control"></div>';
    $out.='<div class="comment"></div>';
    $out.='<noscript>'.esc_html( __('This contact form only works with Javascript enabled','nlcf')).'</noscript>
    <script>
              var funkybit = document.querySelector(".comment"); 
              var FN = document.createElement("input"); 
                FN.setAttribute("type", "hidden"); 
                FN.setAttribute("name", "field6"); 
                FN.setAttribute("value", "haha");
                funkybit.appendChild(FN);
        </script>';
    $out.=wp_nonce_field('contact-form','field7',FALSE,FALSE);
    $out.='<input type="hidden" name="field9" value="'.esc_url( get_permalink() ).'" />';
    $out.='<p><input type="submit" value="'.esc_html( __('Send message','church-admin')).'" /></p>';
    $out.='</form>';
    return $out;
}

function church_admin_contact_form_strposa( $haystack, $needle, $offset=0) {
    if(!is_array( $needle) ) $needle = array( $needle);
    foreach( $needle as $query) {
        if ( empty( $query) )continue;
        if(strpos(strtoupper( $haystack), strtoupper( $query), $offset) !== false) return true; // stop on first true result
    }
    return false;
}