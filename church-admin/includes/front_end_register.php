<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_front_end_register( $member_type_id=1, $exclude=array(), $admin_email=TRUE, $allow=NULL,  $allow_registrations=true, $onboarding = FALSE,$full_privacy_show=TRUE)
{
    /******************************************
    *
    *   Setup
    *
    ******************************************/
    global $wpdb;
    $out='';
    church_admin_date_picker_script();
    $saved_member_type_id=get_option('church_admin_member_type_id_for_registrations');
    if(!empty($saved_member_type_id)){$member_type_id = $saved_member_type_id;}
    if(empty($member_type_id)){$member_type_id=1;}
    /******************************************
    *
    *   Check for $_POST['ca-cmd']
    *
    ******************************************/
    if(!empty( $_POST['ca-cmd'] ) )
    {
        church_admin_debug('****** FRONT END REGISTER POSTED *******');
        //church_admin_debug($_POST);
        if ( empty( $exclude) ) $exclude=array();
        if ( empty( $allow) ) $allow=array();
        if ( empty( $_POST['nonce'] ) ) return '<p>'.esc_html( __('No security code','church-admin' ) ).'</p>';
        if(!wp_verify_nonce( $_POST['nonce'],$_POST['ca-cmd'] ) )  {
            church_admin_debug('Nonce fail');
            return '<p>'.esc_html( __('Invalid security code','church-admin' ) ).'</p>';
        }    
       
        $id=!empty( $_POST['id'] )?(int)sanitize_text_field(stripslashes($_POST['id'] ) ):NULL;
        if(!empty( $_POST['ca-email'] ) )  {$enteredEmail=sanitize_email( stripslashes( $_POST['ca-email'] ) );}else{$enteredEmail='';}
        switch( $_POST['ca-cmd'] )
        {
            case 'email-check':
                if(!is_email($enteredEmail))
                {
                    church_admin_debug('email-check scenario 1');
                    $out.='<p>'.esc_html( __("That wasn't a valid email address", 'church-admin' ) ).'</p>';
                }
                elseif( email_exists($enteredEmail) && !church_admin_front_end_email_check())
                {
                    church_admin_debug('email-check scenario 2');
                    //weird scenario where they are a user but not in the directory
                    $out.='<p>'.esc_html( __('Looks like you have a user account for this site, but no directory entry','church-admin')).'</p>';
                    $out.='</p>'.wp_login_form(array('echo'=>FALSE) ).'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" alt="'.esc_html( __( 'Lost Password', 'church-admin' )).'">'.esc_html( __( 'Lost Password', 'church-admin' )).'</a></p>';
                }
                elseif(!church_admin_front_end_email_check() )
                {
                    church_admin_debug('email-check scenario 3');
                    if(!empty($allow_registrations)){
                        church_admin_debug('ALLOW registrations '.$allow_registrations);
                        $out.=church_admin_front_end_basic_register( $member_type_id,$exclude,$admin_email,$enteredEmail,1,$full_privacy_show);
                    }else{
                        church_admin_debug('no registrations');
                        $out.='<p>'.esc_html( __('If you would like to register on this site, please fill out our contact form below.','church-admin' ) ).'</p>';
                        require_once(plugin_dir_path(dirname(__FILE__) ).'display/contact.php');
                        $out.=church_admin_contact_public();
                    }  
                }
                else {

                    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql($enteredEmail).'"');
                    if(empty($person))
                    {
                        //this case should not happen
                        $out.='<p>'.esc_html( __('An error has occurred, sorry come back soon.','church-admin' ) ).'</p>';
                    }
                    elseif(empty($person->gdpr_reason))
                    {
                        //user hasn't responded to confirmation email.
                        $out.='<h2>'.esc_html( __('Welcome back','church-admin' ) ).'</h2>';
                        $out.='<p>'.esc_html( __('It looks like you have registerd but not yet clicked on the link in the confirmation email. If you have not received it yet, please check your spam folder','church-admin' ) ).'</p>';

                    }
                    elseif(empty($person->user_id))
                    {
                        //user has responded to confirmation email but awaiting admin approval.
                        $out.='<h2>'.esc_html( __('Welcome back','church-admin' ) ).'</h2>';
                        $out.='<p>'.esc_html( __('It looks like you have confirmed your email. An admin will check your directory entry and issue a user account soon','church-admin' ) ).'</p>';
                    }
                    else
                    {
                        church_admin_debug('Login screen presented');
                       
                        $out.='<p>'.esc_html( __('Looks like you are on our system, please login','church-admin' ) ).'</p>'.wp_login_form(array('echo'=>FALSE) ).'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" alt="'.esc_html( __( 'Lost Password', 'church-admin' )).'">'.esc_html( __( 'Lost Password', 'church-admin' )).'</a></p>';

                    }
                    
                    
                }
            break; 
            case 'basic-register':
                if(church_admin_front_end_email_check() )
                {
                    $out.='<p>'.esc_html( __('Looks like you are on our system, please login','church-admin' ) ).'</p>'.wp_login_form(array('echo'=>FALSE) ).'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" alt="'.esc_html( __( 'Lost Password', 'church-admin' ) ).'">'.esc_html( __( 'Lost Password', 'church-admin'  )).'</a></p>';
                }
                else {
                    if(!empty($allow_registrations)){
                        church_admin_debug('ALLOW registrations '.$allow_registrations);
                        $out.=church_admin_front_end_basic_register( $member_type_id,$exclude,$admin_email,$enteredEmail,$onboarding,$full_privacy_show);
                    }else{
                        $out.='<p>'.esc_html( __('If you would like to register on this site, please fill out our contact form below.','church-admin' ) ).'</p>';
                        require_once(plugin_dir_path(dirname(__FILE__) ).'display/contact.php');
                        $out.=church_admin_contact_public();
                    }
                }
            break;    
            case 'display-household':
                $out.=church_admin_admin_frontend_display_household();
            break;
            case 'edit-address':
                $household_id=church_admin_frontend_user_can('edit-address',$id);
                if(!$household_id)  {return '<p>'.esc_html( __('Sorry you cannot do that','church-admin' ) ).'</p>';}
                $address=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"');
                if (empty( $address) )return"<p>".esc_html( __('No household address to edit','church-admin' ) )."</p>";
                $out.=church_admin_frontend_edit_address( $address);
            break;
            case 'add-household':
                $out.=church_admin_admin_frontend_add_household();
            break;
            case 'add-person':
                $people_id=NULL;
                $household_id=church_admin_frontend_user_can('edit-person',$id);
                if(!$household_id)  {return '<p>'.esc_html( __('Sorry you cannot do that','church-admin' ) ).'</p>';}
                else $out.=church_admin_frontend_edit_people(NULL,$household_id,$exclude,'add',NULL,$full_privacy_show,$member_type_id);   
            break;    
            case 'edit-person':
                $household_id=church_admin_frontend_user_can('edit-person',$id);
                if(!$household_id)  {return '<p>'.esc_html( __('Sorry you cannot do that','church-admin' ) ).'</p>';}
                else $out.=church_admin_frontend_edit_people($id,$household_id,$exclude,'edit',$allow,$full_privacy_show,$member_type_id);   
            break;
            case 'delete-person':
                if(!church_admin_frontend_user_can('delete-person',$id) )  {return '<p>'.esc_html( __('Sorry you cannot do that','church-admin' ) ).'</p>';}
                else $out=church_admin_frontend_delete_people( $id);   
            break; 
            case 'delete-household':
                if(!church_admin_frontend_user_can('delete-household',$id) )  {return '<p>'.esc_html( __('Sorry you cannot do that','church-admin' ) ).'</p>';}
                $return=church_admin_delete_household($id);
                if( $return)  {$out='<p>'.esc_html( __('Your household has been completely deleted','church-admin' ) ).'</p>';}
                else{$out='<p>'.esc_html( __('Household deletion failed, please get in touch','church-admin' ) ).'</p>';}
            break;  
            
            case 'add-household-from-user':
                church_admin_debug('***** add-household-from-user *****');
                if(!is_user_logged_in()){
                    church_admin_debug('not logged in');
                    $out.=__('Not logged in','church-admin');
                    return $out;
                    exit();
                }
                $user=wp_get_current_user();
                if(empty($user))
                {
                    church_admin_debug('no user found');
                    $out.=__('user not found','church-admin');
                    return $out;
                    exit();
                }
                $check=$wpdb->get_row('SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.user_id="'.(int)$user->ID.'"');
                if(!empty($check))
                {
                    church_admin_debug('already a household');
                    $out.=church_admin_admin_frontend_display_household();
                    return $out;
                    exit();
                }
                //create quick household
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,first_registered) VALUES( "'.esc_sql(__('Add an address','church-admin')).'","'.esc_sql(wp_date('Y-m-d')).'")');
                $household_id=$wpdb->insert_id;
                //church_admin_debug($wpdb->last_query);

                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,head_of_household,household_id,email,user_id,first_registered) VALUES("'.esc_sql($user->user_login).'",1,"'.(int)$household_id.'","'.esc_sql($user->user_email).'","'.(int)$user->ID.'","'.esc_sql(wp_date('Y-m-d')).'")');

                $out.='<div class="notice notice-success"><p>'.esc_html( __('Quick household created from your login details, please edit','church-admin' ) ).'</p></div>';
                //church_admin_debug($wpdb->last_query);
                unset($_POST);
                $out.=church_admin_admin_frontend_display_household();
                return $out;
                exit();

            break;
        }
    }
    else
    {
        if(!is_user_logged_in() )
        {
            /***************************************
            *
            *   First step email to check
            *
            ****************************************/      
            $out.='<div id="ca-first-step">';
            $out.='<h2>'.esc_html( __('Register/Login','church-admin' ) ).'</h2>';
            $out.='<form action="" method="POST"><div class="church-admin-form-group"><label>'.esc_html( __('Please start with your email address','church-admin' ) ).'</label>';
            $out.='<input type="email" id="ca-email-address" name="ca-email" class="church-admin-form-control"></div>';
            $out.=wp_nonce_field('email-check','nonce',FALSE,FALSE);
            $out.='<p><input type="hidden" name="ca-cmd" value="email-check" /><input type="submit" class="btn btn-success" value='.esc_html( __("Next &raquo;",'church-admin' ) ).'" /></p></form></div>';
        }
        else
        {
            church_admin_debug('Login Screen');
            $out.=church_admin_admin_frontend_display_household();
        }
        
        
    }
    //$out.='<script> var beginLat=0; var beginLng=0</script>';

    return $out;
}
                   
/**********************************************************
*
*   Front End user check
*
***********************************************************/
function church_admin_frontend_user_can( $what,$id)
{
    global $wpdb;
    if(!is_user_logged_in() )return FALSE;
    
    /***************************************
    *
    *   Get household_id for logged in user
    *
    ****************************************/
    $user=wp_get_current_user();
    $household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    
    
    //user not connected to a household
    if ( empty( $household_id) )return FALSE;

    //delete-household case
    if($what=='delete-household'){
        if(empty($id)){return FALSE;}
        if($id != $household_id){
            return FALSE;
        }   
        else{ 
            return TRUE;
        }
    }



    //other cases
    if(!empty( $id) )
    {
        $check=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" AND people_id="'.(int)$id.'"');
        if(!$check)  { return FALSE;}
    }
    return $household_id;
}




/**********************************************************
*   Basic Register
***********************************************************/ 
function church_admin_front_end_basic_register( $member_type_id,$exclude,$admin_email,$enteredEmail,$onboarding,$full_privacy_show)
{
    if(defined('CA_DEBUG') )church_admin_debug("***************************\r\nFRONT END REGISTER ".date('Y-m-d H:i:s') );
    global $wpdb;
    $out='';
    if(defined('CA_DEBUG')&&!empty( $_POST) )church_admin_debug(print_r( $_POST,TRUE) );
    if(!empty( $_POST['save-registration'] )&&!(empty( $_POST['first_name'] ) )&&!(empty( $_POST['last_name'] ) )&&empty( $_POST['funky-bit'] ) && wp_verify_nonce( $_POST['nonce'],'basic-register') )
    { 
       if(defined('CA_DEBUG') )church_admin_debug('Passed quick check');
        
       //sanitize
        $form=$form=array();
        if ( empty( $_POST['lat'] ) )$_POST['lat']=NULL;
        if ( empty( $_POST['lng'] ) )$_POST['lng']=NULL;
        foreach( $_POST AS $key=>$value)
        {
            if ( empty( $value) )$value=NULL;
            $form[$key]=church_admin_sanitize( $value);
        }
        //escaped array
        foreach( $form AS $key=>$value)$form[$key]=esc_sql( $value);
        
        if(church_admin_spam_check( $form['email'],'email') )
        {
            if(defined('CA_DEBUG') )church_admin_debug('Email field is spam');
            $out.=__('You are acting like a spammer','church-admin');
            return $out;
        }
        foreach( $form AS $key=>$value)
        {
            if(!empty( $form[$key] ) &&!is_array($form[$key]) )
            {
                if(church_admin_spam_check( $form[$key],'text') )
                {
                    if(defined('CA_DEBUG') )church_admin_debug('Failed '.$key.' field with '.$form[$key] );
                    return '<p>'.esc_html(sprintf(__('Unfortunately your input  "%1$s" has failed the spam checks','church-admin' ) ,$form[$key]) ).'</p>';
                }
            }
        }
        
       
         
        /***********************************
        *   Save household
        ***********************************/
        //First check if person has been saved
        $household=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.esc_sql( $form['first_name'] ).'" AND last_name="'.esc_sql( $form['last_name'] ).'" AND email="'.esc_sql( $form['email'] ).'"');
        if( $household)
        {
            $out='<p>'.esc_html( __('You need to login to edit your entry')).'</p>'.wp_login_form(array('echo'=>FALSE) ).'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" alt="'.esc_html( __( 'Lost Password', 'church-admin' )).'">'.esc_html( __( 'Lost Password', 'church-admin' ) ).'</a></p>';
            return $out;
        }
        else
        {
            //insert address
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,lat,lng,phone,first_registered) VALUES("'.esc_sql( $form['address'] ).'","'.esc_sql( $form['lat'] ).'","'.esc_sql( $form['lng'] ).'","'.esc_sql( $form['phone'] ).'","'.esc_sql(wp_date('Y-m-d')).'")');
            $household_id=$wpdb->insert_id;
            church_admin_update_meta_fields('household',NULL,$household_id,TRUE,NULL);//update onboarding household custom fields
            if(defined('CA_DEBUG') )church_admin_debug("*****************\r\nHousehold id is $household_id ");
        }
        $people_id=church_admin_frontend_save_person( $household_id,NULL,1,$member_type_id,NULL,1);
        if(defined('CA_DEBUG') )church_admin_debug("*****************\r\nPeople id is $people_id ");
        /*********************
        *   Send admin email
        *********************/
        if( $admin_email)
        {
            $adminmessage=get_option('church_admin_new_entry_admin_email');

            $adminmessage = str_replace('[HOUSEHOLD_ID]','[HOUSEHOLD_ID]&token=[NONCE]',$adminmessage);
            $adminmessage=str_replace('[HOUSEHOLD_ID]',(int)$household_id,$adminmessage);
            $token = md5(NONCE_KEY.$household_id);
            $adminmessage=str_replace('[NONCE]',$token,$adminmessage);
            $adminmessage=str_replace('',$household_details,$adminmessage);//not needed!
            $adminmessage.='<p>&nbsp;</p>';
            $adminmessage.= church_admin_household_details_table($household_id);
         
            church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(sprintf(__('New household registration on %1$s','church-admin' ) ,site_url()) ),wp_kses_post($adminmessage),null,null,null,null,null,FALSE);
           
        }
        /*********************
        *   Send user email
        *********************/
        church_admin_email_confirm( $people_id);
        $out.='<p>'.esc_html( __('Thank you for registering on the site. You will receive a confirmation email shortly. Please click on the link to confirm your email','church-admin' ) ).'</p>';
    	//reset app address list cache
        delete_option('church_admin_app_address_cache');
        delete_option('church_admin_app_admin_address_cache');
    }
    else
    {
        $out.='<h3>'.esc_html( __('Please fill out form to register','church-admin' ) ).'</h3>';
        $out.='<form action="" method="POST">';
        $out.='<input type="hidden" name="ca-cmd" value="basic-register" />';
        $out.=wp_nonce_field('basic-register','nonce',FALSE,FALSE);
        $allow=array('sites');
        $out.=church_admin_frontend_people_form(NULL,NULL,$exclude,$allow,$enteredEmail,$onboarding,$full_privacy_show);
        
       
        
        $out.='<div class="church-admin-form-group"><input type="hidden" name="funky-bit" class="funkybit" /><input type="hidden" name="save-registration" value="1" /><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></div></form>';
    }
    
    return $out;
    
    
}
/***********************************************************
*
*   Front End Display Household
*
***********************************************************/                   
function church_admin_admin_frontend_display_household()
{
    church_admin_debug('********** church_admin_admin_frontend_display_household() **********');
    global $wpdb;
    $out="";
    if(!is_user_logged_in() ){
        church_admin_debug('not logged in');
        return FALSE;
    }
    /***************************************
    *
    *   Get household_id for logged in user
    *
    ****************************************/
    $user=wp_get_current_user();
    //church_admin_debug($user);
    $household=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    //church_admin_debug($household);
    church_admin_debug( $wpdb->last_query);
    if ( empty( $household) )
    {
        church_admin_debug('No connected directory entry');
        //first let's just check if the user has an unconnected entry. grab first one, pref head of household
        $household=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql($user->user_email).'"  LIMIT 1');
        //church_admin_debug($wpdb->last_query);
        if(!empty($household))
        {
            church_admin_debug('Users email is in directory, we will connect them to account');

            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$user->ID.'" WHERE people_id="'.(int)$household->people_id.'"');
        }
        $household=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    }


    if(empty($household))
    {
        church_admin_debug("No people table record for login");
       // $out.='<p>'.$wpdb->last_query.'</p>';
        $message = wp_kses_post(sprintf( __('User "%1$s" has logged in to the site at %2$s, but does not have a directory entry. Please check what has gone wrong!' , 'church-admin' ) ,esc_html( $user->user_login ), make_clickable(get_permalink()) ));
        church_admin_debug( $message );

        church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(__('User logged in with no directory entry','church-admin') ),$message,null,null,null,null,null,FALSE);

    
        $out.='<p>'.esc_html( __('No household found for your login. An email has been sent to the site administrator.','church-admin' ) ).'</p>';
        $out.='<p>'.church_admin_frontend_button_form('add-household-from-user',$user->user_id,esc_html(__('Quick household creation','church-admin' ) ),'btn-success').'</p><p>'.esc_html( __('You can edit at the next step','church-admin' ) ).'</p>';
        $out.='<p><a href="'.wp_logout_url( get_permalink() ).'">'.esc_html( __('Logout','church-admin' ) ).'</a></p>';
  
        return $out;
    }
    //sage to procees as user has directory entry
    $address=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household->household_id.'"');
    church_admin_debug( $wpdb->last_query);
    
    
    /*********************************************
    *   Household Title
    *********************************************/
    $out.='<div class="church-admin-register-title"><h2>'.esc_html(church_admin_household_title( $household->household_id) ).'</h2></div>';
    /*********************************************
    *   Image
    *********************************************/
    if(!empty( $address->attachment_id) )
    {
        $household_image_attributes=wp_get_attachment_image_src( $address->attachment_id,'medium','' );
		if ( $household_image_attributes )
        {
            $out.='<p><img src="'.$household_image_attributes[0].'" width="'.$household_image_attributes[1].'" height="'.$household_image_attributes[2].'" class="rounded" style="margin-bottom:10px" /></p>';
        }
       
    }
    /*********************************************
    *   Address
    *********************************************/
    if(!empty( $address->address) )$out.='<p>'.esc_html( __('Address','church-admin' ) ).': '.esc_html( $address->address).'</p>';
    if(!empty( $address->phone) )$out.='<p>'.esc_html( __('Phone','church-admin' ) ).': '.esc_html( $address->phone).'</p>';
    $custom_fields=church_admin_get_custom_fields();
    if(!empty( $custom_fields) )
    {
        foreach( $custom_fields AS $ID=>$field)
        {
            if( $field['section']!='household') continue;
            if( $field['show_me']!=1) continue;

            //note people_id on the $wpdb->prefix.'church_admin_custom_fields_meta' can have the value of household_id!
            $thisData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$ID.'" AND people_id="'.(int)$address->household_id.'"');
            switch( $field['type'] )
            {
                case 'boolean':
                    if(!empty( $thisData->data) )  {$customOut=__('Yes','church-admin');}else{$customOut=__('No','church-admin');}
                break;
                case 'date':
                    if(!empty( $thisData->data) )  {$customOut=mysql2date(get_option('date_format'),$thisData->data);}else{$customOut="";}
                break;
                default:
                    if(!empty( $thisData->data) )  {$customOut=esc_html( $thisData->data);}else{$customOut="";}
                break;
            }
            if(!empty( $customOut) )$out.=esc_html( $field['name'] ).': '.$customOut.'<br>';
        }
    }
    /*********************************************
    *   Map
    **********************************************/
    $api_key=get_option('church_admin_google_api_key');
	if(!empty( $api_key) )
	{
		$api='';
        if(!empty( $api_key) )$api='key='.$api_key;
        if(!empty( $household_image_attributes) )
        {
            $size=$household_image_attributes[1].'x'.$household_image_attributes[2];
            $mapSize=' width="'.$household_image_attributes[1].'" height="'.$household_image_attributes[2].'"';
        }
        else
        {
            $size='300x225';   
            $mapSize=' width="300" height="225"';
        }
        $url='https://maps.google.com/maps/api/staticmap?'.$api.'&center='.$address->lat.','.$address->lng.'&zoom=15&markers=color:blue%7C'.$address->lat.','.$address->lng.'&size='.$size;
		$map_url=esc_url( $url);
       
		$out.='<p><img src="'.$map_url.'" '.$mapSize.' alt="Map" /></p>'."\r\n\t";

	}
    
    /*********************************************
    *   Edit Address button
    *********************************************/
    $out.='<p>'.church_admin_frontend_button_form('edit-address',$household->people_id,esc_html(__('Edit address','church-admin' ) ),'btn-success').'</p><p>&nbsp;</p>';
    /*********************************************
    *   People
    *********************************************/
    $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household->household_id.'"');
    $out.='<table class="table table-bordered ca-table">';
    foreach( $people AS $person)
    {
        $out.='<tr>';
        $out.='<td data-colname="'.esc_html(__('Name','church-admin')).'">'.esc_html( church_admin_formatted_name( $person ) ).'</td>';
        $out.='<td data-colname="'.esc_html(__('Edit','church-admin')).'">'.church_admin_frontend_button_form('edit-person',$person->people_id,esc_html(__('Edit person','church-admin' ) ),'btn-success',FALSE).'</td>';
        $out.='<td data-colname="'.esc_html(__('Delete','church-admin')).'">'.church_admin_frontend_button_form('delete-person',$person->people_id,esc_html(__('Delete person','church-admin' ) ),'btn-danger',TRUE).'</td>';
        if(!empty( $person->attachment_id) )
        {
            $out.='<td data-colname="'.esc_html( __('Image', 'church-admin' )).'" >'.wp_get_attachment_image( $person->attachment_id,'medium').'</td>';
        }
        else
        {
            
        if(isset( $person->sex) &&$person->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
 
        $out.='<td data-colname="'.esc_html( __('Image', 'church-admin' )).'"  "><img src="'.plugins_url('/', dirname(__FILE__) ) . 'images/'.$image.'" width="300" height="200"  alt="'.esc_html( __('No photo yet','church-admin' ) ).'"  /></td>';}
        
        $out.='</tr>';
    }
    $out.='<tr><td colspan=4>'.church_admin_frontend_button_form('add-person',NULL,esc_html(__('Add a person','church-admin' ) ),'btn-success',FALSE).'</td>';
    $out.='</table>';
    $out.='<p>'.church_admin_frontend_button_form('delete-household',$person->household_id,esc_html(__('Delete Household','church-admin' ) ),'btn-danger',TRUE).'</p>';
    return $out;
}
/***********************************************************
*
*   Front End Button form
*
***********************************************************/ 
function church_admin_frontend_button_form( $what,$id,$text,$cssType,$confirm=FALSE)
{
    if ( empty( $what) ) return NULL;
    $out='<form action="" method="post" ';
    if( $confirm)$out.=' onsubmit="return confirm(\''.esc_html( __('Are you sure? This deletes you and your data completely.','church-admin' ) ).$text.'\')"';
    $out.='><input type="hidden" name="ca-cmd" value="'.$what.'" />';
    if( $id)$out.='<input type="hidden" name="id" value="'.(int)$id.'" />';
    $out.=wp_nonce_field( $what,'nonce',FALSE,FALSE);
    $out.='<input type="submit" class="btn '.$cssType.' church-admin-register-button" value="'.$text.'" ';
    
    $out.='/></form>';
    return $out;
}


/***********************************************************
*
*   Front End Edit address
*
***********************************************************/
function church_admin_frontend_edit_address( $address)
{
    global $wpdb;
    $out='';
    if(!empty( $_POST['save-address'] )&& wp_verify_nonce( $_POST['save-address'],'save-address') )
    {
        if(defined('CA_DEBUG') )church_admin_debug(print_r( $_POST,TRUE) );
        $form=array();
        if(empty($_POST['lat']))$_POST['lat']=null;
        if(empty($_POST['lng']))$_POST['lng']=null;
        foreach( $_POST AS $key=>$value)$form[$key]=church_admin_sanitize($value)  ;
        
        if(!empty( $_POST['household_attachment_id'] ) )  {
            $attachment_id=(int)$_POST['household_attachment_id'];
        }else{
            $attachment_id=NULL;
        }

        $form['wedding_anniversary'] = (!empty($form['wedding_anniversary'] )&&church_admin_checkdate($form['wedding_anniversary'] ))?$form['wedding_anniversary'] :null;

        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET wedding_anniversary="'.esc_sql( $form['wedding_anniversary'] ).'", attachment_id="'.$attachment_id.'" , address="'.esc_sql( $form['address'] ).'",lat="'.esc_sql( $form['lat'] ).'", lng="'.esc_sql( $form['lng'] ).'",phone="'.esc_sql( $form['phone'] ).'" WHERE household_id="'.(int)$address->household_id.'"');
        /*****************************************************
        * Save Household Custom Fields
        *****************************************************/
        church_admin_update_meta_fields('household',NULL,$address->household_id,FALSE,NULL);
        $out.='<div class="notice notice-success">'.esc_html( __('Address saved','church-admin' ) ).'</div>';
        $out.=church_admin_admin_frontend_display_household();
    }
    else
    {
        $out.='<h2>'.esc_html( __('Edit Address','church-admin' ) ).'</h3>';
        $out.='<form action="" method="post">';
        $out.='<input type="hidden" name="ca-cmd" value="edit-address" />';
        $out.=wp_nonce_field('edit-address','nonce',FALSE,FALSE);
        $out.=wp_nonce_field('save-address','save-address',FALSE,FALSE);
        $out.=church_admin_frontend_address_form( $address,TRUE,FALSE);
        $out.='<div class="church-admin-form-group"><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></div></form>';
    }
    return $out;
}

/***********************************************************
*
*   Front End Edit person
*
***********************************************************/

function church_admin_frontend_edit_people( $people_id,$household_id,$exclude,$which,$allow,$full_privacy_show,$member_type_id)
{
    church_admin_debug('****church_admin_frontend_edit_people***** ');
    global $wpdb;
    
    $out='';
    if ( empty( $allow) )$allow=array();
    if ( empty( $which) )$which='edit';
    if ( empty( $exclude) )$exclude=array();
    if ( empty( $household_id) )return '<p>'.esc_html( __('You cannot edit that person','church-admin' ) ).'</p>';

    //sort of out whether head_of_household needs setting
    $head_of_household=0;
    if(!empty( $people_id) ){
        $head_of_household=$wpdb->get_var('SELECT head_of_household FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    }
    church_admin_debug($wpdb->last_query);
    church_admin_debug('Head of household: '. $head_of_household);
    $data=$wpdb->get_row('SELECT a.*,b.wedding_anniversary FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND a.people_id="'.(int)$people_id.'"');
    $member_type_id = !empty($data->member_type_id)?(int)$data->member_type_id:$member_type_id;
    if(!empty( $_POST['save-person'] )&& wp_verify_nonce( $_POST['save-person'],'save-person')&&!(empty( $_POST['first_name'] ) )&&!(empty( $_POST['last_name'] ) ))
    {
        church_admin_frontend_save_person( $household_id,$people_id,$head_of_household,$member_type_id,$allow,0);
        /*****************************************************
        * Message
        *****************************************************/
        $out.='<div class="notice notice-success">'.esc_html( __('Saved person','church-admin' ) ).'</div>';
        $out.=church_admin_admin_frontend_display_household();
    }
    else
    {
        switch( $which)
        {
            case 'edit':
                $title=__('Edit Person','church-admin');
                $data=$wpdb->get_row('SELECT a.*,b.wedding_anniversary FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND a.people_id="'.(int)$people_id.'"');
            break;
            case 'add':
                $title=__('Add Person','church-admin');
                $people_id=NULL;
            break;    
        }
       
        $out.='<div class="church-admin-register-title"><h2>'.$title.'</h2></div>';
        $out.='<form action="" method="POST">';
        /*****************************************************
        * Image
        *****************************************************/
        $out.='<div class="ca-upload-area"  data-which="people" data-id="'.(int)$people_id.'" data-nonce="'.wp_create_nonce("people-image-upload").'" id="uploadfile"><h3>'.esc_html( __('Photo ','church-admin' ) ).'</h3><div class="church-admin-form-group"><label>';
        if(!empty( $data->attachment_id) )
        {
            $out.=wp_get_attachment_image( $data->attachment_id,'medium','', array('class'=>"current-photo",'id'=>'people-image'.(int)$people_id) );
        }
        else
        {
            $out.= '<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="300" height="200" id="people-image'.(int)$people_id.'"  alt="'.esc_html( __('Photo of Person','church-admin' ) ).'"  />';
        }
        $out.='<p class="drag-message">'.esc_html( __('Drag and drop image into this box to replace','church-admin' ) ).'</p> </div>';
        $out.='<input type="hidden" name="people_attachment_id" class="attachment_id" id="attachment_id" ';
        if(!empty( $data->attachment_id) )$out.=' value="'.intval( $data->attachment_id).'" ';
        $out.='/><span id="upload-message"></span>';
        $out.='</div>';
        if(!empty( $people_id) )  {$out.='<input type="hidden" name="people_id" value="'.(int)$people_id.'" />';}
        
        switch( $which)
        {
            case 'edit':
                $out.=wp_nonce_field('edit-person','nonce',FALSE,FALSE);
                $out.='<input type="hidden" name="ca-cmd" value="edit-person" />';
            break;
            case 'add':
                $out.=wp_nonce_field('add-person','nonce',FALSE,FALSE);
                $out.='<input type="hidden" name="ca-cmd" value="add-person" />';
            break;
        }
        $out.=wp_nonce_field('save-person','save-person',FALSE,FALSE);
        //if ( empty( $data) )$data=NULL;
       
        $out.=church_admin_frontend_people_form( $data,$people_id,$exclude,$allow,NULL,FALSE,$full_privacy_show);
        $out.='<div class="church-admin-form-group"><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></div></form>';
       
    }

    return $out;                   
}
/******************************************
*   Front End Delete person
******************************************/
function church_admin_frontend_delete_people( $people_id)
{
    church_admin_debug('**** church_admin_frontend_delete_people *****');
    global $wpdb;
    $user=wp_get_current_user();
    $out='';
    $person=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.people_id="'.(int)$people_id.'" AND a.household_id=b.household_id');
    if( $person)
    {
        $message = esc_html(sprintf( __(' "%1$s" has been deleted by %2$s, logged in at %3$s.' , 'church-admin' ) , church_admin_formatted_name($person) , $user->user_login , make_clickable(get_permalink() ) ) );
        if(!empty($person->user_id) && $person->user_id!=$user->ID && !user_can('manage_options', $person->user_id ) )
        {
            
           
            $message .=  '<a href="'.get_edit_user_link($person->user_id).'">'.sprintf(__('User account edit/delete %1$s','church-admin'),church_admin_formatted_name($person)).'</a></p>';

        }
        $number=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$person->household_id.'"');
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
        if( $number==1)
        {
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$person->household_id.'"');
            $message.='<p>'.esc_html( __('As it was a single person household, the address details have been deleted.'));
        }
        church_admin_debug(' calling church_admin_head_of_household_tidy');
        church_admin_head_of_household_tidy( $person->household_id);
    }
     //let admin know
    
     church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(__('Person deleted from directory','church-admin') ),$message,null,null,null,null,null,FALSE);
        $out.='<div class="notice notice-success">'.esc_html(sprintf(__('%1$s deleted','church-admin' ) ,church_admin_formatted_name($person) ) ).'</div>';
    $out.=church_admin_admin_frontend_display_household();
    church_admin_debug('**** END church_admin_frontend_delete_people *****');
    return $out;                  
} 
/*******************************************
*   Front End People Form
*******************************************/ 
function church_admin_frontend_people_form( $data,$people_id,$exclude,$allow,$enteredEmail,$onboarding,$full_privacy_show)
{
    $out='';
    global $wpdb,$ministries;
    
        if ( empty( $allow) )$allow=array();
        
        if ( empty( $exclude) )$exclude=array();
       
       
        if(!empty( $_POST['id'] ) )$out.='<input type="hidden" name="id" value="'.(int)sanitize_text_field(stripslashes($_POST['id'] ) ).'" />';
        $use_titles = get_option('church_admin_use_titles');
        if(!empty($use_titles)){

            $titles = get_option('church_admin_titles');
            if(!empty($titles)){
                $out.='<div class="church-admin-form-group"><label for="title">'.esc_html( __('Title','church-admin' ) ).' </label><select id="title" name="title" class="church-admin-form-control"><option>'.esc_html(__('Choose title','church-admin')).'</option>';
                foreach($titles AS $key=>$title){
                    $out.='<option value="'.esc_attr($title).'" ';
                    if(!empty($data->title) && $data->title==$title) {$out.=' selected="selected" ';}
                    $out.='>'.esc_html($title).'</option>';
                }
                $out.='</select></div>';
            }
        }
        /**************************************
        *   First name
        ***************************************/      
        $out.='<div class="church-admin-form-group"><label for="first_name">'.esc_html( __('First Name','church-admin' ) ).' *</label><input id="first_name" placeholder="'.esc_html( __('First Name','church-admin' ) ).'" type="text" required="required" class="church-admin-form-control" name="first_name"';
        if(!empty( $data->first_name) )  {$out.=' value="'.esc_html( $data->first_name).'"';}
        $out.='/></div>';
        /**************************************
        *   Prefix
        ***************************************/
        $prefix=get_option('church_admin_use_prefix');
        if(empty($prefix)){$exclude[]='prefix';}
        if(is_array( $exclude)&&!in_array('prefix',$exclude) )
        {
            $out.='<div class="church-admin-form-group"><label for="prefix">'.esc_html( __('Prefix','church-admin' ) ).'</label><input id="prefix"  placeholder="'.esc_html( __('Prefix','church-admin' ) ).'" type="text"  class="church-admin-form-control" name="prefix" ';
            if(!empty( $data->prefix) )  {$out.=' value="'.esc_html( $data->prefix).'"';}
            $out.='/></div>';
        }
        /**************************************
        *   Last name
        ***************************************/ 
        $out.='<div class="church-admin-form-group"><label for="last_name">'.esc_html( __('Last Name','church-admin' ) ).' *</label><input id="last_name" placeholder="'.esc_html( __('Last Name','church-admin' ) ).'" type="text" required="required" class="church-admin-form-control" name="last_name" ';
        if(!empty( $data->last_name) )  {$out.=' value="'.esc_html( $data->last_name).'"';}
        $out.='/></div>';
        
       
        
        /**************************************
        *   Email
        ***************************************/ 
        $out.='<div class="church-admin-form-group"><label for="email">'.esc_html( __('Email address','church-admin' ) ).' </label><input id="email"  placeholder="'.esc_html( __('Email','church-admin' ) ).'" type="email"  class="church-admin-form-control ca-email" name="email"';
        if(!empty( $data->email) )  {$out.=' value="'.esc_html( $data->email).'"';}elseif(!empty( $enteredEmail) )  {$out.=' value="'.esc_html( $enteredEmail).'"';}
        $out.='/></div>';

    
        
        /**************************************
        *   Cell
        ***************************************/ 
        $out.='<div class="church-admin-form-group"><label for="cell">'.esc_html( __('Cellphone number','church-admin' ) ).'</label><input id="cell"  placeholder="'.esc_html( __('Cellphone number','church-admin' ) ).'" type="text" class="church-admin-form-control" name="mobile"';
        if(!empty( $data->mobile) )  {$out.=' value="'.esc_html( $data->mobile).'"';}
        $out.='/></div>';
        
        if(!empty($onboarding)){
            $out.=church_admin_frontend_address_form( NULL,0,$onboarding);
        }


        /**************************************
        *   Date of Birth
        ***************************************/
        if(is_array( $exclude)&&!in_array('date_of_birth',$exclude) )
        {
            if(!empty( $data->date_of_birth) )  {$dob=$data->date_of_birth;}else{$dob=NULL;}
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Date of birth','church-admin' ) ).'</label>';
            $out.=church_admin_date_picker( $dob,'date_of_birth',FALSE,1910,date('Y'),'date_of_birth','date_of_birth');
            $out.='</div>';
            
        }
        /**************************************
        *   Gender
        ***************************************/        
        if(is_array( $exclude)&&!in_array('gender',$exclude) )
        {
            $gender=get_option('church_admin_gender');
            $out.='<div class="church-admin-form-group"><label >'.esc_html( __('Gender','church-admin' ) ).'</label><select name="sex" class="sex church-admin-form-control" >';
            $first=$option='';

            foreach( $gender AS $key=>$value)
            {
                if(isset( $data->sex)&&$data->sex == $key)
                    {
                        $first= '<option value="'.esc_html( $key).'" selected="selected">'.esc_html( $value).'</option>';
                    }
                    else
                    {
                        $option.= '<option value="'.esc_html( $key).'" >'.esc_html( $value).'</option>';
                    }

            }
            $out.=$first.$option.'</select></div>'."\r\n";
        }
        /**************************************
        *   People Type
        ***************************************/
        if(is_array( $exclude)&&!in_array('people_type_id',$exclude) )
        {
            $people_type=get_option('church_admin_people_type');
            $out.='<div class="church-admin-form-group"><label for="people_type_id">'.esc_html( __('Person type','church-admin' ) ).'</label><select id="people_type_id"  name="people_type_id"  class="people_type_id church-admin-form-control">';
            $first=$option='';
            foreach( $people_type AS $id=>$type)
            {

                if(!empty( $data->people_type_id)&& $id==$data->people_type_id)
                {
                    $first='<option value="'.$id.'" selected="selected">'.$type.'</option>'."\r\n";
                }else $option.='<option value="'.$id.'">'.$type.'</option>'."\r\n";


            }
            $out.=$first.$option.'</select></div>'."\r\n";
        }else
        {
            $out.='<option value=1 type="hidden" name="people_type_id">';
        }
        
        /**************************************
        *   Marital Status
        ***************************************/
        if(is_array( $allow)&&!in_array('marital_status',$allow) )
        {
            $church_admin_marital_status=get_option('church_admin_marital_status');
            $out.='<div class="church-admin-form-group"><label for="marital_status">'.esc_html( __( 'Marital Status','church-admin') ).'</label><select id="marital_status"  data-name="marital_status" name="marital_status" id="marital_status" class="marital_status church-admin-form-control">';
            $first=$option='';
            foreach( $church_admin_marital_status AS $id=>$type)
            {

                if(!empty( $data->marital_status)&& $data->marital_status==$type)
                {
                    $first='<option value="'.$id.'" selected="selected">'.$type.'</option>'."\r\n";
                }else $option.='<option value="'.$id.'">'.$type.'</option>'."\r\n";
            }
            $out.=$first.$option.'</select></div>'."\r\n";
            
            //wedding_anniversary
            $wa=get_option('church_admin_show_wedding_anniversary');
            if(!empty($wa)){
                $wedding_anniversary=!empty($data->wedding_anniversary) ? $data->wedding_anniversary : null;
               
                $out.= '<div class="church-admin-form-group" id="wedding-anniversary" style="display:none"><label>'.esc_html( __( 'Wedding Anniversary','church-admin') ).'</label>';
                $out.= church_admin_date_picker( $wedding_anniversary,'wedding_anniversary',FALSE,'1910',NULL,'wedding_anniversary','wedding_anniversary',FALSE,NULL,NULL,NULL);
                $out.='</div>';
            }

            $out.='<script>
            jQuery(document).ready(function($){
        
                $("#marital_status").on("change", function (e) {
                    
                    var selected = $("option:selected", this).val();
                    console.log(selected);
                    if(selected==="3"){ 
                        console.log("show wa field");
                        $("#wedding-anniversary").show();
                    }
        
                });
            });
            </script>';

        }else
        {
            $out.='<option value="'.__('N/A','church-admin').'" type="hidden" name="marital_status">';
        }
        /*****************************************
         * User bio
         ****************************************/
        if(!empty( $data->user_id) && user_can( $data->user_id, 'edit_posts' ) )
        {
            $bio=get_user_meta( $data->user_id,'description',TRUE);
            $allowed_html = wp_kses_allowed_html( 'data' );
            $out.='<div class="church-admin-form-group"><label >'.esc_html( __('Bio for user','church-admin' ) ).'</label><textarea name="bio" style="height:150px" class="church-admin-form-control">';
            if(!empty( $bio) )$out.=wp_kses( $bio,$allowed_html);
            $out.='</textarea></div>';
        }
    /*****************************************************
	* Custom Fields
	*****************************************************/
	/*****************************************************
	*
	* Custom Fields
	*
	*****************************************************/
	$custom_fields=church_admin_get_custom_fields();
	church_admin_debug('Custom field section PEOPLE');
    church_admin_debug('Onboarding: '.$onboarding);
    
	if(!empty( $custom_fields) && (!in_array('custom',$exclude) || !empty($onboarding) ) )
	{
		foreach( $custom_fields AS $id=>$field)
		{
            
			if( $field['section']!="people"){
                church_admin_debug('not people');
                continue;
            }
            //church_admin_debug($field);
            if(!empty($onboarding) && empty($field['onboarding'])){
                //looking for onboarding, but not onboarding
                church_admin_debug('looking for onboarding, but not onboarding');
                continue;
            }
            if(empty($onboarding) && !empty($field['onboarding'])){
                //not looking for onboarding but it is onboarding
                church_admin_debug('not looking for onboarding but it is onboarding');
                continue;
            }
			$dataField='';
			if(!empty( $data->people_id) ){
				$dataField=$wpdb->get_var('SELECT `data` FROM '.$wpdb->prefix.'church_admin_custom_fields_meta' .' WHERE section="people" AND people_id="'.(int)$data->people_id.'" AND custom_id="'.(int)$id.'"');
				//church_admin_debug($wpdb->last_query);
				church_admin_debug('$dataField: '.$dataField);
			}
			$out.='<div class="church-admin-form-group"><label >'.esc_html( $field['name'] ).'</label>';
			switch( $field['type'] )
			{
				case 'boolean':
					$out.='</div><div class="checkbox"><label><input type="radio" data-name="custom-'.(int)$id.'"   value="1" name="custom-'.(int)$id.'" ';
					if (isset( $dataField)&&$dataField==1)
						$out.= 'checked="checked" ';
					$out.='>'.esc_html( __( 'Yes','church-admin') ).'</label></div><div class="checkbox"><label> <input type="radio" data-name="custom-'.(int)$id.'"  value="0" name="custom-'.(int)$id.'" ';
					if (isset( $dataField)&& $dataField==0)
						$out.= 'checked="checked" ';
					$out.='>'.esc_html( __( 'No','church-admin') ).'</label></div>';
				break;
				case'text':
					$out.='<input type="text" data-name="custom-'.(int)$id.'" class="church-admin-form-control"  name="custom-'.(int)$id.'" ';
					if(!empty( $dataField)||isset( $field['default'] ) )$out.=' value="'.esc_html( $dataField).'"';
					$out.='/>';
                    $out.='</div>';
				break;
				case'date':
					$out.= church_admin_date_picker( $dataField,'custom-'.(int)$id,FALSE,1910,date('Y'),'custom-'.(int)$id,'custom-'.(int)$id);
                    $out.='</div>';
				break;
				case 'checkbox':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					$out.='</div>';
					for ($y=0;$y<count($options);$y++){
						$out.='<div class="checkbox"><label><input type="checkbox"  name="custom-'.(int)$id.'[]" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && in_array($options[$y],$dataField) ){$out.=' checked="checked" ';}
						
						$out.='> '.esc_html($options[$y]).'</label></div>';
					}
				break;
				case 'radio':
                    $out.='</div>';
					$options = maybe_unserialize($field['options']);
				
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						$out.='<div class="checkbox"><label><input type="radio"  name="custom-'.(int)$id.'" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && $options[$y] == $dataField) {$out.=' checked="checked" ';}
						$out.='> '.esc_html($options[$y]).'</label></div>';
					}
				break;	
				case 'select':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					$out.='<select name="custom-'.(int)$id.'" class="church-admin-form-control"><option>'.__('Choose','church-admin').'</option>';
					for ($y=0;$y<count($options);$y++){
						$out.='<option value="'.esc_attr($options[$y]).'" '.selected($options[$y],$dataField,FALSE).'> '.esc_html($options[$y]).'</option>';
					}
					$out.='</select>';
                    $out.='</div>';
				break;
			}
			

		}

	}

    /*********************************************
     * Ministries
     * Needs to be allowed to keep form simple
     *********************************************/
    if(!empty( $allow) &&is_array( $allow) && in_array('ministries',$allow) )
    {
        if(!empty( $data->people_id) )$personsMinistries=church_admin_get_people_meta( $data->people_id,'ministry');
       
        $out.='<h3 >'.esc_html( __('Ministries','church-admin' ) ).'</h3>';
        foreach( $ministries AS $ministry_id=>$ministry)
        {
            $out.='<div class="church-admin-form-group"><div class="checkbox"><label><input type="checkbox" data-name="ministry_id" name="ministry_id[]" value="'.(int)$ministry_id.'" ';
            if(!empty( $personsMinistries) && in_array( $ministry_id,$personsMinistries) ) $out.=' checked="checked" ';
            $out.='/>&nbsp;'.esc_html( $ministry).'</label></div></div>';
    
        }

    }
    /*********************************************
     * Groups
     * Needs to be allowed to keep form simple
     *********************************************/
    if(!empty( $allow) &&is_array( $allow) && in_array('groups',$allow) )
    {    
        $out.='<h3>'.esc_html( __('Small Group','church-admin' ) ).'</h3>';
		$smallgroups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
		if(!empty( $smallgroups) )
		{
			if(!empty( $data->people_id) )$dataSmallGroups=church_admin_get_people_meta( $data->people_id,'smallgroup');
			foreach( $smallgroups AS $smallgroup)
			{
				$out.='<div class="checkbox"><label><input type="checkbox" data-name="smallgroup_id"  name="smallgroup_id[]" value="'.(int)$smallgroup->id.'" ';
				if(!empty( $dataSmallGroups) && in_array( $smallgroup->id,$dataSmallGroups) ) $out.=' checked="checked" ';
				$out.='/> '.esc_html( $smallgroup->group_name).'</label></div>'."\r\n";
			}
			
		}

    }
    /*********************************************
     * Sites
     * Needs to be allowed to keep form simple
     *********************************************/
    if(!empty( $allow) &&is_array( $allow) && in_array('sites',$allow) )
    { 
        $sites=$wpdb->get_results('SELECT venue,site_id FROM '.$wpdb->prefix.'church_admin_sites ORDER BY venue ASC');
        if( $wpdb->num_rows==1)
        {
            $out.='<input type="hidden" name="site_id" '; 
            if(!empty( $data->site_id) )
            {
                $out.=' value="'.(int)$data->site_id.'" ';
            }
            else
            {
                $out.=' value="'.(int)$sites[0]->site_id.'" ';
            } 
            $out.='/>';
        }
        else
        {
            $out.='<div class="church-admin-form-group"><label >'.esc_html( __('Which site do you attend?','church-admin' ) ).'</label> <select name="site_id" data-name="site_id" class="church-admin-form-control">';
            $curr_site_id = !empty($data->site_id)?(int)$data->site_id: null;
            foreach( $sites AS $site)
            {
                
                    $out.='<option value="'.intval( $site->site_id).'" '.selected($curr_site_id,$site->site_id,FALSE).'>'.esc_html( $site->venue).'<option>';
               
            }
            $out.='</select></div>';
        }

    }
        /**************************************
        *   Privacy Permissions
        ***************************************/       
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('I give permission...','church-admin' ) ).'</label></div>';
        $out.='<div class="checkbox"><label ><input type="checkbox" id="email_send" name="email_send" value="TRUE" data-name="email_send"  class="email-permissions"';
        if(!empty( $data->email_send) )$out.='checked="checked" ';
        $out.='/> '.esc_html( __('To receive email','church-admin' ) ).'</label></div>';
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Refine type of email you can receive','church-admin' ) ).'</label></div>';

         //Schedule emails
         $out.='<div class="checkbox"><label ><input type="checkbox" value="1" id="rota_email" data-name="rota-email"  class="email-permissions"  name="rota_email" ';
         if(!empty( $data->rota_email) ) $out.=' checked="checked" ';
         $out.=' /> '.esc_html( __('To receive email schedule reminders','church-admin' ) ).'</label></div>';

        $out.='<div class="checkbox"><label ><input type="checkbox" name="news_send" id="news_send" value="TRUE"  class="email-permissions" data-name="news_send"  ';
        if(!empty($data->news_send)){
            $out.=' checked="checked" ';
        }
        $out.='/> '.esc_html( __('To receive blog posts by email','church-admin' ) ).'</label></div>';
        //PRAYER REQUESTS
        $noPrayer=get_option('church-admin-no-prayer');
	    if ( empty( $noPrayer) ){   
            $out.='<div class="checkbox"><label ><input type="checkbox" value="1" id="prayer_requests" data-name="prayer_chain"  class="email-permissions"  name="prayer_requests" ';
            if(!empty( $data->people_id) )$prayer=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$data->people_id.'" AND meta_type="prayer-requests"');
            if(!empty( $prayer) ) $out.=' checked="checked" ';
            $out.=' /> '.esc_html( __('To receive Prayer requests by email','church-admin' ) ).'</label></div>';
        }
        //BIBLE READINGS
        $noBibleReadings=get_option('church-admin-no-bible-readings');
	    if ( empty( $noBibleReadings) ){
           $out.='<div class="checkbox"><label ><input type="checkbox" value="1" id="bible_readings" data-name="bible_readings"  class="email-permissions"  name="bible_readings" ';
            if(!empty( $data->people_id) )$bible=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$data->people_id.'" AND meta_type="bible-readings"');
            if(!empty( $bible) ) $out.=' checked="checked" ';
            $out.=' /> '.esc_html( __('To receive new Bible Reading notes by email','church-admin' ) ).'</label></div>';
        }
       
       if(!empty($full_privacy_show))
       {
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Other privacy permissions','church-admin' ) ).'</label></div>';
        $out.='<div class="checkbox"><label ><input type="checkbox" name="photo_permission" value="TRUE" data-name="photo_permission"  ';
        if(!empty( $data->photo_permission) )$out.='checked="checked" ';
        $out.='/> '.esc_html( __('To use my photo in the directory and on the website','church-admin' ) ).'</label></div>';
        $out.='<div class="checkbox"><label ><input type="checkbox" name="sms_send" value="TRUE" data-name="sms_send"  ';
        if(!empty( $data->sms_send) )$out.='checked="checked" ';
        $out.='/> '.esc_html( __('To receive SMS','church-admin' ) ).'</label></div>';
        
        $out.='<div class="checkbox"><label ><input type="checkbox" name="mail_send" value="TRUE" data-name="mail_send" ';
        if(!empty( $data->mail_send) )$out.='checked="checked" ';
        $out.='/> '.esc_html( __('To receive mail','church-admin' ) ).'</label></div>';

        $out.='<div class="checkbox"><label ><input type="checkbox" name="show_me" id="show-me" value="TRUE" data-name="show_me" ';
        if(!empty( $data->show_me) )$out.='checked="checked" ';
        $out.='/> '.esc_html( __('To show me on the password protected address list','church-admin' ) ).'</label></div>';
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Refine address list privacy','church-admin' ) ).'</label></div>';
        $fine_privacy=!empty($data->privacy)?maybe_unserialize($data->privacy):array();
        //show email
        $out.='<div class="church-admin-form-group"><label><input type="checkbox" name="show-email" id="show-email" ';
        if(!empty( $fine_privacy['show-email']) )  {$out.=' checked ="checked" ';}
        $out.='/> '.esc_html( __("Show email address",'church-admin' ) ).'</label> </div>';
        //show cell
        $out.='<div class="church-admin-form-group"><label><input type="checkbox" name="show-cell" id="show-cell" ';
        if(!empty( $fine_privacy['show-cell']) )  {$out.=' checked ="checked" ';}
        $out.='/> '.esc_html( __("Show cell number",'church-admin' ) ).' </label></div>';
        //show landline
        $out.='<div class="church-admin-form-group"><label><input type="checkbox" name="show-landline" id="show-landline"  ';
        if(!empty( $fine_privacy['show-landline']) )  {$out.=' checked ="checked" ';}
        $out.='/> '.esc_html( __("Show landline",'church-admin' ) ).' </label></div>';
        //show address
        $out.='<div class="church-admin-form-group"><label><input type="checkbox" name="show-address" id="show-address"  ';
        if(!empty( $fine_privacy['show-address']) )  {$out.=' checked ="checked" ';}
        $out.='/> '.esc_html( __("Show address",'church-admin' ) ).'</label> </div>';

        $out.='<script>jQuery(document).ready(function( $)  {
            
            $("#show-me").change(function()
            {
                if( $("#show-me").prop("checked")== false)
                {
                    console.log("Unchecking detailed privacy");
                    $("#show-email").prop( "checked", false );
                    $("#show-cell").prop( "checked", false );
                    $("#show-landline").prop( "checked", false );
                    $("#show-address").prop( "checked", false );
                }
            });
            if( $("#email_send").prop("checked")== false)
            {
                console.log("Unchecking");
                $("#news_send").prop( "checked", false );
                $("#prayer_requests").prop( "checked", false );
                $("#bible_readings").prop( "checked", false );
                $("#rota_email").prop( "checked", false );
            }
            $(".email-permissions").change(function()
            {
                var id=$(this).attr("id");
                switch(id)
                {
                    case "email_send":
                        console.log("email send changed");
                        if( $(this).prop("checked")==false)
                        {
                            $("#news_send").prop( "checked", false );
                            $("#prayer_requests").prop( "checked", false );
                            $("#bible_readings").prop( "checked", false );
                            $("#rota_email").prop( "checked", false );
                        }
                    break;
                    case "news_send":
                    case "prayer_requests":
                    case "bible_readings":
                        console.log("other checkbox changed");
                        if( $(this).prop("checked") ) 
                        {
                            console.log("Other checked");
                            $("#email_send").prop("checked", true);
                        }
                    break;
                }
               
            });
            
            });
        </script>';
       }
    return $out;
}
/*******************************************
*   Front End Address Form
*******************************************/ 
function church_admin_frontend_address_form( $address,$image,$onboarding)
{
    global $wpdb;
    $out='';
    if(!empty( $image) )
    {
        $out.='<script>var nonce="'.wp_create_nonce("household-image-upload").'";
        var whichImage="household";';
        if(!empty( $address->household_id) )  {$out.='var id="'.(int)$address->household_id.'";';}else{$out.= 'var id;';}
        $out.='</script>';
        $out.='<div class="ca-upload-area"  data-which="household" data-id="'.(int)$address->household_id.'" data-nonce="'.wp_create_nonce("household-image-upload").'" id="uploadfile"><h3>'.esc_html( __('Photo ','church-admin' ) ).'</h3><div class="church-admin-form-group"><label>';
        if(!empty( $address->attachment_id) )
        {
            $out.=wp_get_attachment_image( $address->attachment_id,'medium','', array('class'=>"current-photo",'id'=>"household-image") );
        }
        else
        {
            $out.= '<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="300" height="200" id="household-image"  alt="'.esc_html( __('Photo of Person','church-admin' ) ).'"  />';
        }
        $out.='<p class="drag-message">'.esc_html( __('Drag and drop image into this box to replace','church-admin' ) ).'</p> </div>';
        $out.='<input type="hidden" name="household_attachment_id" class="attachment_id" id="attachment_id" ';
        if(!empty( $address->attachment_id) )$out.=' value="'.(int)$address->attachment_id.'" ';
        $out.='/><span id="household-upload-message"></span>';
        $out.='</div>';
    }
    $out.= '<!--Address--><div class="church-admin-form-group"><label>'.esc_html( __('Address ','church-admin' ) ).'</label><input  class="church-admin-form-control" type="text" id="address" name="address" ';
    if(!empty( $address->address) ) $out.=' value="'.esc_html( $address->address).'" ';
	$out.='/></div>';
    $coords=church_admin_center_coordinates($wpdb->prefix.'church_admin_household');
    if(!empty( $address->lat) )  {$lat=$address->lat;}else{$lat=$coords->lat;}
    if(!empty( $address->lng) )  {$lng=$address->lng;}else{$lng=$coords->lng;}
    if(!empty( $address->household_id) )  {$household_id=(int)$address->household_id;}else{$household_id=NULL;}
    $out.=church_admin_frontend_google_map( $lat,$lng,$household_id);
    $out.= '<div class="church-admin-form-group"><label>'.esc_html( __('Home phone','church-admin' ) ).'</label><input  class="church-admin-form-control" type="text" id="phone" name="phone" ';
    if(!empty( $address->phone) ) $out.=' value="'.esc_html( $address->phone).'" ';
	$out.='/></div>';
    


    $custom_fields=church_admin_get_custom_fields();
	if(!empty( $custom_fields) )
	{
		
		foreach( $custom_fields AS $id=>$field)
		{
			if( $field['section']!='household')continue;
            if(!empty($onboarding) && empty($field['onboarding'])){
                //looking for onboarding, but not onboarding
                church_admin_debug('looking for onboarding, but not onboarding');
                continue;
            }
            if(empty($onboarding) && !empty($field['onboarding'])){
                //not looking for onboarding but it is onboarding
                church_admin_debug('not looking for onboarding but it is onboarding');
                continue;
            }
			$dataField='';
            $household_id=null;
			if(!empty($address->household_id)){
                $household_id=$address->household_id;
                $dataField=$wpdb->get_var('SELECT data FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE section="household" AND household_id="'.(int)$household_id.'" AND custom_id="'.(int)$id.'"');
            }
			//church_admin_debug($wpdb->last_query);
			church_admin_debug('Custom: '.$field['name']);
			//church_admin_debug($dataField);
			$out.='<div class="church-admin-address church-admin-form-group" ><label>'.esc_html( $field['name'] ).'</label>';
			switch( $field['type'] )
			{
				case 'boolean':
                    $out.='</div><div class="church-admin-form-check">';
					$out.='<label><input type="radio" data-what="household-custom" data-id="'.(int)$household_id.'" data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable" name="custom-'.(int)$id.'" value="1" ';
					if (!empty( $dataField))$out.=' checked="checked" ';
					$out.= '>'.esc_html( __( 'Yes','church-admin') ).'</label></div><div class="church-admin-form-check"><label> <input type="radio"  data-id="'.(int)$household_id.'"  data-what="household-custom" data-ID="'.(int)$household->household_id.'" data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable" value="0" name="custom-'.(int)$id.'" ';
					if (empty( $dataField)) $out.= 'checked="checked" ';
					$out.='>'.esc_html( __( 'No','church-admin') ).'</label></div>';
					break;
				case'text':
					$out.= '<input type="text"  data-what="household-custom" data-id="'.(int)$household_id.'"  data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable"  name="custom-'.(int)$id.'" ';
					if(!empty( $thisHouseholdCustomData->data) || isset( $field['default'] ) )$out.= ' value="'.esc_html( $dataField).'"';
					$out.='/>';
				break;
				case'date':
					
					$out.= church_admin_date_picker( $dataField,'custom-'.(int)$id,FALSE,1910,date('Y'),'custom-'.(int)$id,'custom-'.(int)$id,FALSE,'household-custom',(int)$household->household_id,(int)$id);
				
				break;
				case 'checkbox':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						$out.= '<div class="checkbox"><label><input type="checkbox" data-what="household-custom" data-id="'.(int)$household_id.'"  data-custom-id="'.(int)$id.'"  data-type="checkbox" class="church-admin-editable custom-'.(int)$id.'" name="custom-'.(int)$id.'[]" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && is_array($dataField) && in_array($options[$y],$dataField) ){$out.= ' checked="checked" ';}
						
						$out.= '> '.esc_html($options[$y]).'</label></div>';
					}
				break;
				case 'radio':
					$options = maybe_unserialize($field['options']);
				
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						$out.= '<div class="checkbox"><label><input type="radio" data-id="'.(int)$household_id.'" data-what="household-custom"  data-type="radio" data-custom-id="'.(int)$id.'" class="church-admin-editable"  name="custom-'.(int)$id.'" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && $options[$y] == $dataField) {$out.= ' checked="checked" ';}
						$out.= '> '.esc_html($options[$y]).'</label></div>';
					}
				break;	
				case 'select':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					$out.= '<select name="custom-'.(int)$id.'" class="church-admin-form-control church-admin-editable" data-id="'.(int)$household_id.'" data-what="household-custom"  data-type="radio" data-custom-id="'.(int)$id.'"><option>'.esc_html( __( 'Choose' , 'church-admin' ) ) .'</option>';
					for ($y=0;$y<count($options);$y++){
						$out.= '<option value="'.esc_attr($options[$y]).'" '.selected($options[$y],$dataField,FALSE).' data-what="household-custom" data-type="select" data-custom-id="'.(int)$id.'"> '.esc_html($options[$y]).'</option>';
					}
                    $out.='</select>';
				break;
			}
			$out.= '</div>';
			
		}

	}
    return $out;
}
/*******************************************
*   Front End Google Maps
*******************************************/ 
function church_admin_frontend_google_map( $lat=NULL,$lng=NULL,$household_id=NULL)
{
    global $wpdb;
    $out='';    
    $googleMapAPI=get_option('church_admin_google_api_key');
    if(!$googleMapAPI)return NULL;
    /*if ( empty( $lat)||empty( $lng) )
    {   
        $site=$wpdb->get_row('SELECT lat,lng FROM '.$wpdb->prefix.'church_admin_sites ORDER BY site_id LIMIT 1');
        if(!empty( $site) )
        {
            $out.='<script > var beginLat ='.esc_html( $site->lat).';';
            $out.= 'var beginLng ='.esc_html( $site->lng);
            $out.=';</script>';
            $lat=$site->lat;
            $lng=$site->lng;
        }else
        {
            $out.='<script >';
            $out.='var beginLat = 51.50351129583287;var beginLng = -0.148193359375;if (navigator.geolocation) {var location_timeout = setTimeout("geolocFail()", 10000);navigator.geolocation.getCurrentPosition(function(position) {clearTimeout(location_timeout);beginLat = position.coords.latitude;beginLng = position.coords.longitude;}, function(error) {clearTimeout(location_timeout);});}</script>';
            $lat=51.50351129583287;
            $lng=-0.148193359375;
        }
    }else
    {
            $out.='<script > var beginLat ='.esc_html( $lat).';';
            $out.= 'var beginLng ='.esc_html( $lng);
            $out.=';</script>';
    }
    */
    $out.='<script >var ca_method="update-directory";var ID="'.(int)$household_id.'"; var nonce="'.wp_create_nonce('update-directory').'";var beginLat=';
    if(!empty( $lat) ) {$out.= $lat.';';}else {$out.= '0;';}
    if(!empty( $lng) ) {
        $out.= 'var beginLng='.$lng.';var zoom=17';
    }else {
        $out.='var beginLng=0;var zoom=0';
    }
   
    $out.=';</script>';
      
    if(!empty( $googleMapAPI) )
    {
        $out.= '<div class="church-admin-form-group"><label><button id="geocode_address" class="btn btn-info">'.esc_html( __('Update map','church-admin' ) ).'</button></label><span id="finalise" ></span><input type="hidden" name="lat" id="lat" value="'.$lat.'" /><input type="hidden" name="lng" id="lng" value="'.$lng.'" /><div id="map" style="width:500px;height:300px;margin-bottom:20px"></div></div>';
    }



    
    return $out;
}

/*******************************************
*   Front End save person
*******************************************/ 
function church_admin_frontend_save_person( $household_id,$people_id,$head_of_household=0,$member_type_id=1,$allow=NULL,$onboarding=0)
{
    church_admin_debug('**** church_admin_frontend_save_person ***** ');
    church_admin_debug(func_get_args());
    global $wpdb;

    /************************************
	 * GET $old_email for MailChimp sync
	 ************************************/
    if(!empty( $people_id) )
	{
		$old_email=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	}
	else
	{
		$old_email=NULL;
	}
    /**********************
    *  Sanitize input
    ***********************/
        $form= array();
        foreach( $_POST AS $key=>$value){
            $form[$key]=church_admin_sanitize($value) ;
        }
        
        //privacy
        $privacy=array();
      
        if(!empty($_REQUEST['show-email'])){$privacy['show-email']=1;}else{$privacy['show-email']=0;}
		if(!empty($_REQUEST['show-cell'])){$privacy['show-cell']=1;}else{$privacy['show-cell']=0;}
		if(!empty($_REQUEST['show-landline'])){$privacy['show-landline']=1;}else{$privacy['show-landline']=0;}
		if(!empty($_REQUEST['show-address'])){$privacy['show-address']=1;}else{$privacy['show-address']=0;}
        //church_admin_debug($privacy);
        $form['privacy']=serialize($privacy);

        if ( empty( $form['title'] ) )$form['title']=NULL;
        if ( empty( $form['prefix'] ) )$form['prefix']=NULL;
        if(!empty( $_POST['people_id'] ) )$people_id=(int)$_POST['people_id'];
        $form['show_me'] = !empty($form['show_me']) ? 1 : 0;
        $form['email_send'] = !empty($form['email_send']) ? 1 : 0;
        $form['rota_email'] = !empty($form['rota_email']) ? 1 : 0;
        $form['mail_send'] = !empty($form['mail_send']) ? 1 : 0;
        $form['news_send'] = !empty($form['news_send']) ? 1 : 0;
        $form['sms_send'] = !empty($form['sms_send']) ? 1 : 0;
        $form['photo_permission'] = !empty($form['photo_permission']) ? 1 : 0;
        $form['rota_email'] = !empty($form['rota_email']) ? 1 : 0;
       
        if(!empty( $_POST['date_of_birth'] ) && church_admin_checkdate( $_POST['date_of_birth'] ) )
        {
            $form['date_of_birth']=esc_sql( sanitize_text_field(stripslashes($_POST['date_of_birth'] )));
           
        }
        else
        {
            $form['date_of_birth']=NULL;
        }
        if(!empty( $_POST['people_attachment_id'] ) )  {
            $form['attachment_id'] = (int)$_POST['people_attachment_id'];
        }else{
            $form['attachment_id'] = NULL;
        }
        $e164cell='';
        if(!empty( $_POST['mobile'] ) )$e164cell=esc_sql(church_admin_e164( sanitize_text_field(stripslashes($_POST['mobile'])) ) );

        /*****************************
         * Array of expected data
         ****************************/
        $expected_data = array('title'=>null,'first_name'=>null,'prefix'=>'','last_name'=>null,'email'=>null,'mobile'=>null,'sex'=>1,'mail_send'=>0,'people_type_id'=>1,'email_send'=>0,'sms_send'=>0,'news_send'=>0,'photo_permission'=>0,'attachment_id'=>null,'show_me'=>0,'rota_email'=>0,'date_of_birth'=>null,'household_id'=>$household_id,'head_of_household'=>0,'last_updated'=>wp_date('Y-m-d'),'attachment_id'=>null,'privacy'=>serialize($privacy),'member_type_id'=>$member_type_id);
        foreach($expected_data AS $key =>$value){
            $expected_data[$key]= isset($form[$key])? $form[$key] : $value; //uses form data or default value
        }

        church_admin_debug('******* expected data to be saved ******');
        church_admin_debug($expected_data);
        church_admin_debug('******* END expected data to be saved ******');
        /**********************
        *  Check if saved
        ***********************/
        if ( empty( $people_id) )
        {
            $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE title="'.esc_sql( $form['title'] ).'" AND first_name="'.esc_sql( $form['first_name'] ).'" AND prefix="'.esc_sql( $form['prefix'] ).'" AND last_name="'.esc_sql( $form['last_name'] ).'" AND email="'.esc_sql( $form['email'] ).'" AND mobile="'.esc_sql( $form['mobile'] ).'" AND sex="'.esc_sql( $form['sex'] ).'" AND mail_send="'.(int)$expected_data['mail_send'].'" AND people_type_id="'.esc_sql( $form['people_type_id'] ).'" AND sex="'.esc_sql( $form['sex'] ).'" AND email_send="'.(int)$expected_data['email_send'].'" AND sms_send="'.(int)$expected_data['sms_send'].'" AND photo_permission="'.(int)$expected_data['photo_permission'].'" AND attachment_id="'.(int)$expected_data['attachment_id'].'" AND show_me="'.(int)$expected_data['show_me'].'" AND rota_email="'.(int)$expected_data['rota_email'].'" AND date_of_birth="'.esc_sql( $expected_data['date_of_birth'] ).'" AND household_id="'.(int)$household_id.'"');
        }
        /**********************
        *  Save 
        ***********************/
        if ( empty( $people_id) )
        {
            $expected_data['first_registered']=wp_date('Y-m-d H:i:s');

            //include $head_of_household in INSERT
            $wpdb->insert($wpdb->prefix.'church_admin_people',$expected_data);
            church_admin_debug($wpdb->last_query);
            $people_id=$wpdb->insert_id;
            church_admin_update_meta_fields('people',$people_id,$household_id,TRUE,NULL);//update onboarding custom fields
        }
        else
        {
            //don't update head of household, as it should be set already
            unset($expected_data['head_of_household']);
            $wpdb->update($wpdb->prefix.'church_admin_people',$expected_data,array('people_id'=>$people_id));
            church_admin_debug($wpdb->last_query);   
            church_admin_update_meta_fields('people',$people_id,$household_id,FALSE,NULL);//update NON onboarding custom fields
        }
        //user bio
        $user_id=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
        if(!empty( $user_id) && !empty($_POST['bio']))
        {
            update_user_meta( $user_id,'description',sanitize_text_field( stripslashes( $_POST['bio'] ) ) );
        }
        //save prayer requests and bible readings
        if(!empty( $_POST['prayer_requests'] ) )  {church_admin_update_people_meta(1,$people_id,"prayer-requests");}
		if(!empty( $_POST['bible_readings'] ) )  {church_admin_update_people_meta(1,$people_id,"bible-readings");}
      
        /****************************************************
        * Save groups 
        *****************************************************/
        if(!empty( $allow)&&in_array('groups',$allow) )
        {
            church_admin_delete_people_meta(NULL,$people_id,'smallgroup');
            if(!empty( $_POST['smallgroup_id'] ) )
            {
                foreach( $_POST['smallgroup_id'] AS $key=>$id)
                {
                    church_admin_update_people_meta( (int)$id,(int)$people_id,'smallgroup');
                }
            }
            else
            {
                if(defined('CA_DEBUG') )church_admin_debug('Need to put '.(int)$people_id.' in unattached');
                church_admin_update_people_meta(1,$people_id,'smallgroup');
            }
        }
        /*****************************************************
         *  Ministry
         ****************************************************/
        if(!empty( $allow)&&in_array('ministries',$allow) )
        {
            church_admin_delete_people_meta(NULL,$people_id,'ministry');
            if(!empty( $_POST['ministry_id'] ) )
            {    foreach( $_POST['ministry_id'] AS $key=>$id)
                {
                        church_admin_update_people_meta( (int)$id,(int)$people_id,"ministry");
                }
            }
        }
        	/*****************************************************
            *
            * Custom Fields
            *
            *****************************************************/
            church_admin_debug('Handle custom fields');
            church_admin_update_meta_fields('people',$people_id,$household_id,$onboarding,null);
        
        //update user_meta
        $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
        if(!empty( $data->user_id) )church_admin_update_user_meta( $people_id,$data->user_id);
      

    return $people_id;
}

