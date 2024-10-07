<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_automations_list()
{
    global $wpdb,$church_admin_url;
    echo'<h2>'.esc_html(__('Automations','church-admin')).'</h2>';
    echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/automations/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
    $this_month = wp_date('m');
	$this_day = wp_date('d');    
    $happy_birthdays = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE  email_send=1 AND MONTH(date_of_birth)="'.(int)$this_month.'" AND DAY(date_of_birth)="'.(int)$this_day.'" ');
    $global_birthdays = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE show_me=1 AND email_send=1 AND MONTH(date_of_birth)="'.(int)$this_month.'" AND DAY(date_of_birth)="'.(int)$this_day.'" ');
    $anni_result = $wpdb->get_results('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.email_send=1 AND MONTH(b.wedding_anniversary)="'.(int)$this_month.'" AND DAY(b.wedding_anniversary)="'.(int)$this_day.'"  GROUP BY a.household_id');
    $ind_anniversaries = !empty($anni_result) ? $wpdb->num_rows : 0;
    

   $global_anni_result = $wpdb->get_results('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.show_me=1 AND a.email_send=1 AND MONTH(b.wedding_anniversary)="'.(int)$this_month.'" AND DAY(b.wedding_anniversary)="'.(int)$this_day.'"  GROUP BY a.household_id');
   $global_anniversaries = !empty($global_anni_result) ? $wpdb->num_rows : 0;
    
   
    echo'<div class="notice notice-success"><h2>'.__('Todays emails...').'</h2><p>'.__('each email only sent when there are people to include.','church-admin').'</p>';
    echo '<p>'.esc_html(sprintf(__('%1$d people should receive an individual happy birthday email today','church-admin'),$happy_birthdays)).'</p>'; 
    echo '<p>'.esc_html(sprintf(__('%1$d couples should receive an individual happy anniversary email today','church-admin'),$ind_anniversaries)).'</p>';  
    echo'<p><em>'.esc_html(__('The global anniversaries and birthdays email is sent to people other than the individuals involved. So the "show me in the address list" privacy setting is respected')).'</em></p>';
    echo'<p>'.esc_html(sprintf(__('%1$d birthdays and %2$s anniversaries are included in the global anniversaries and birthdays email email today','church-admin'),$global_birthdays,$global_anniversaries)).'</p>';  
    echo'</div>';
    
    
    echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=happy-birthday-email-setup&amp;section=key-dates','happy-birthday-email-setup').'">'.esc_html(__('Daily Email to people celebrating their birthday','church-admin')).'</a>';
    $args=get_option('church_admin_happy_birthday_arguments');
    if(wp_next_scheduled ( 'church_admin_happy_birthday_email', $args )){
        echo ' <img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="25" height="25" alt="'.esc_attr(__('Automation set','church-admin')).'" />';
    }else{
        echo ' <img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" width="25" height="25"  alt="'.esc_attr(__('Automation not set','church-admin')).'" />';
    }
    echo'</p>';
    
    echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=global-birthday-email-setup&amp;section=key-dates','global-birthday-email-setup').'">'.esc_html(__("Daily Email to everyone listing that day's birthdays",'church-admin')).'</a>';
    $args=get_option('church_admin_global_birthday_arguments');
    if(wp_next_scheduled ( 'church_admin_global_birthday_email', $args )){
        echo ' <img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="25" height="25" alt="'.esc_attr(__('Automation set','church-admin')).'" />';
    }else{
        echo' <img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" width="25" height="25" alt="'.esc_attr(__('Automation not set','church-admin')).'" />';
    }
    echo'</p>';
    echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=happy-anniversary-email-setup&amp;section=key-dates','happy-anniversary-email-setup').'">'.esc_html(__('Daily Email to couples celebrating their anniversary','church-admin')).'</a>';
    $args=get_option('church_admin_happy_anniversary_arguments');
    if(wp_next_scheduled ( 'church_admin_happy_anniversary_email', $args )){
        echo ' <img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="25" height="25" alt="'.esc_attr(__('Automation set','church-admin')).'" />';
    }
        else{
            echo ' <img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" width="25" height="25" alt="'.esc_attr(__('Automation not set','church-admin')).'" />';
    }
    echo'</p>';
    echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=global-anniversary-email-setup&amp;section=key-dates','global-anniversary-email-setup').'">'.esc_html(__("Daily Email to everyone listing that day's anniversaries",'church-admin')).'</a>';
    $args=get_option('church_admin_global_anniversary_arguments');
    if(wp_next_scheduled ( 'church_admin_global_anniversary_email', $args )){
        echo '<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="25" height="25" alt="'.esc_attr(__('Automation set','church-admin')).'" />';
    }else{
        echo' <img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" width="25" height="25" alt="'.esc_attr(__('Automation not set','church-admin')).'" />';
    }
    echo'</p>';
    echo'<p>'.__('Only people who have been set to show in address list and receive emails will be included in the email on their birthday/anniversary','church-admin').'</p>';
    echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=global-both-email-setup&amp;section=key-dates','global-both-email-setup').'">'.esc_html(__("Daily Email to everyone listing that day's birthdays and anniversaries",'church-admin')).'</a>';
   
    $args=get_option('church_admin_global_both_arguments');
    if(wp_next_scheduled ( 'church_admin_global_birthday_and_anniversary_email', $args )){
        echo ' <img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="25" height="25" alt="'.esc_attr(__('Automation set','church-admin')).'" />';
    }else{
        echo' <img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" width="25" height="25" alt="'.esc_attr(__('Automation not set','church-admin')).'" />';
    }
    echo'</p>';
    church_admin_send_test_automation_email();



    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=registration-followup-email-setup','registration-followup-email-setup').'">'.esc_html(__("Registration, confirmation and new user email templates",'church-admin')).'</a>';
   
    if(wp_next_scheduled ( 'church_admin_followup_email')){
        echo ' <img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="25" height="25" alt="'.esc_attr(__('Automation set','church-admin')).'" />';
    }else{
        echo' <img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" width="25" height="25" alt="'.esc_attr(__('Automation not set','church-admin')).'" />';
    }
    echo'</p>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=custom-field-automations','custom-field-automations').'">'.esc_html(__('Custom field email automations','church-admin')).'</a>';
    if(wp_next_scheduled ( 'church_admin_custom_fields_automations' )){
        echo ' <img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="25" height="25" alt="'.esc_attr(__('Automation set','church-admin')).'" />';
    }else{
        echo' <img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" width="25" height="25" alt="'.esc_attr(__('Automation not set','church-admin')).'" />';
    }
}

function church_admin_custom_fields_automations_list()
{

    global $church_admin_url;
    echo '<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=new-user-email-template','new-user-email-template').'">'.esc_html(__('New user email template','church-admin')).'</a></p>';
    
    echo'<h2>'.esc_html( __( 'Custom field email automations', 'church-admin' ) ).'</h2>';

    //variables
    $automations = get_option('church_admin_custom_fields_automations');
    $custom_fields = church_admin_custom_fields_array();
   
  

    
    echo'<p>'.esc_html( __( 'These automations email a named contact when a custom field is edited', 'church-admin' ) ).'</p>';
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-custom-field-automation','edit-custom-field-automation').'">'.esc_html( __('Add automation','church-admin') ).'</a></p>';
    if(!empty($automations))
    {
        $tableHeader = '<tr>
                            <th>'.esc_html( __('Automation Name','church-admin' ) ).'</th>
                            <th>'.esc_html( __('Edit','church-admin' ) ).'</th>
                            <th>'.esc_html( __('Delete','church-admin' ) ).'</th>
                            <th>'.esc_html( __('Custom field','church-admin' ) ).'</th>
                            <th>'.esc_html( __('Contact(s)','church-admin' ) ).'</th>
                            <th>'.esc_html( __('Email Type','church-admin' ) ).'</th>
                        </tr>';    
        echo'<table class="widefat bordered striped"><thead>'.$tableHeader.'</thead><tbody>';
        foreach($automations AS $id=>$auto)
        {
            $edit = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-custom-field-automation&id='.(int)$id,'edit-custom-field-automation').'">'.esc_html(__('Edit','church-admin')).'</a>';
            $delete = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=delete-custom-field-automation&id='.(int)$id,'delete-custom-field-automation').'">'.esc_html(__('Delete','church-admin')).'</a>';
            $custom_field_name = !empty($custom_fields[$id])?$custom_fields[$id]['name'] : __('Error: No custom field','church-admin');
            if(!empty($auto['contacts']))
            {
                $contacts = church_admin_get_people( $auto['contacts']);
            }
            else
            {
                $contacts = __('Error: No contacts saved','church-admin');
            }


            echo'<tr>   <td>'.esc_html($auto['name']).'</td>
                        <td>'.$edit.'</td>
                        <td>'.$delete.'</td>
                        <td>'.esc_html($custom_field_name).'</td>
                        <td>'.esc_html($contacts).'</td>
                        <td>'.esc_html($auto['email_type']).'</td>
                </tr>';

        }   
        echo'</tbody><tfoot></table>';

    }

}

function church_admin_edit_custom_field_automation($id)
{
    global $wpdb,$church_admin_url;
    church_admin_debug('*** church_admin_edit_custom_field_animation ***');
    echo'<h2>'.esc_html(__('Edit a custom field automation','church-admin')).'</h2>';

    //variables
    $automations = get_option('church_admin_custom_fields_automations');
    if(!empty($automations[$id]))
    {
        $auto = $automations[$id];
    }
    $custom_fields = church_admin_custom_fields_array();
    if(empty($custom_fields)){
        echo'<p>'.__('Please set up a custom field first','church-admin').'</p>';
        echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-custom-field','edit-custom-field').'">'.__('Add a custom field','church-admin').'</a></p>';
        return;
    }
    if(empty($id)){
       if(!empty($automations)) 
       {
            $id = max(array_keys($automations))+1;
       }else{
            $id=1;
       }
    }
        
    
    
    church_admin_debug('Key '.$id);
    if(!empty($_POST['save']))
    {

        //sanitize
        $from_name = !empty($_POST['from_name']) ? church_admin_sanitize($_POST['from_name']): get_option('church_admin_default_from_name');
		$from_email = !empty($_POST['from_email']) ? church_admin_sanitize($_POST['from_email']): get_option('church_admin_default_from_email');
        $name = !empty( $_POST['name'] ) ? church_admin_sanitize($_POST['name']):null;
        $custom_id = !empty( $_POST['custom_id'] ) ? church_admin_sanitize($_POST['custom_id']):null;
        $contact = !empty( $_POST['contact'] ) ? church_admin_sanitize($_POST['contact']) : null;
        $people_ids = church_admin_get_people_id( $contact );
        $email_type = !empty( $_POST['email_type'] ) ? church_admin_sanitize($_POST['email_type']):null;
        //validate
        if(empty($name)) {return __('No automation name','church-admin');}
        if(empty($custom_id) || !church_admin_int_check($custom_id) || empty($custom_fields[$custom_id])){
            return __('Invalid Custom field','church-admin');
        }
        if(empty($email_type)){return __('No email type selected','church-admin');}
        if($email_type!='digest' && $email_type!='individual'){return __('Invalid email type selected','church-admin');}

        $args=array('from_name'=>$from_name,
        'from_email'=>$from_email,
                    'name'=>$name,
                    'custom_id'=>$custom_id,
                    'contacts' =>$people_ids,
                    'email_type' =>$email_type
        );
        $automations[$id] = $args;
        update_option('church_admin_custom_fields_automations',$automations);
        $first_run = strtotime("0600 tomorrow");
        if (! wp_next_scheduled ( 'church_admin_custom_fields_automations' )) {
            wp_schedule_event( $first_run, 'daily','church_admin_custom_fields_automations');
            
        }
        echo'<div class="notice notice-success"><h2>'.__('Automation saved','church-admin').'</h2><p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=custom-field-automations','custom-field-automations').'">'.esc_html(__('Back to custom field automations' ) ).'</a></p></div>';
    }
    else
    {
        echo'<form action="" method="POST">';

        echo'<div class="church-admin-form-group"><label>'.__("Email from name",'church-admin').'</label>';
		echo'<input type="text" name="from_name" class="church-admin-form-control" ';
		if(!empty($auto['from_name'])) {
			echo 'value="'.esc_attr($happy_birthday['from_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("From email address",'church-admin').'</label>';
		echo'<input type="text" name="from_email" class="church-admin-form-control" ';
		if(!empty($auto['from_email'])) {
			echo 'value="'.esc_attr($auto['from_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';
        //name
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('Automation name (email subject)','church-admin')).'</label><input type="text" name="name" required="required" ';
        if(!empty($auto['name'])){
            echo ' value="'.esc_attr($auto['name']).'" ';
        }
        echo '></div>';
        //custom field
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('Automation name','church-admin')).'</label><select name="custom_id">';
        foreach($custom_fields AS $id=>$cf){
            echo'<option value="'.(int)$id.'" ';
            if(!empty($auto['custom_id'])&&$auto['custom_id']==$id){
                echo' selected="selected" ';
            }
            echo '>'.esc_html($cf['name']).'</option>';
        }
        echo'</select></div>';
        //contact
        $names = !empty($auto['contacts'])?church_admin_get_people( $auto['contacts']):array();
        echo'<div class="church-admin-form-group" ><label>'.esc_html( __('Contact(s)','church-admin' ) ).'</label>'.church_admin_autocomplete('contact','friends','to',$names).'</div>';
        //email type
        echo'<div class="church-admin-form-group" ><label>'.esc_html( __('Email type','church-admin' ) ).'</label>';
        $email_type = !empty($auto['email_type'])?$auto['email_type']:null;
        /*
        echo'<select name="email_type" class="church-admin-form-control">';
        echo'<option value="digest" '.selected($email_type,'digest',false).'>'.esc_html(__('Daily digest - one email with all changes','church-admin')).'</option>';
        echo'<option value="individual" '.selected($email_type,'individual',false).'>'.esc_html(__('Individual - an email for each changes','church-admin')).'</option>';
        echo'</select></div>';
        */
        echo'<input type="hidden" name="email_type" value="digest">';
        echo'<p><input type="hidden" name="save" value=1><input class="button-primary" type="submit" value="'.esc_attr(__('Save','church-admin') ).'"></p></form>';
       
    }
    
}
function church_admin_delete_custom_field_automation($id)
{
    global $wpdb,$church_admin_url;
    if(empty($id)){return;}
    $automations = get_option('church_admin_custom_fields_automations');
    unset($automations[$id]);
    update_option('church_admin_custom_fields_automations',$automations);
    echo'<div class="notice notice-success"><h2>'.__('Automation deleted','church-admin').'</h2><p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=custom-field-automations','custom-field-automations').'">'.esc_html(__('Back to custom field automations' ) ).'</a></p></div>';
}

function church_admin_edit_conditional_automation($custom_id,$automation_id=null){
    global $wpdb,$church_admin_url;

    echo '<h2>'.esc_html(__('Conditional Custom field automation','church-admin')).'</h2>';
    $custom_fields = church_admin_custom_fields_array();
    if(empty($custom_fields)){
        echo '<div class="notice notice-danger"><h2>'.esc_html(__('No custom fields setup','church-admin')).'</h2></div>'."\r\n";
        return;
    }
    if(empty($custom_id)){

        echo'<form action="" method="POST">'."\r\n";
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('Choose custom field','church-admin')).'</label>'."\r\n";
        echo'<select class="church-admin-form-control" name="custom_id">'."\r\n";
        echo '<option>'.esc_html(__('Choose...','church-admin')).'</option>'."\r\n";
        foreach($custom_fields AS $id=>$details){
            echo'<option value="'.$id.'">'.esc_html($details['name']).'</option>'."\r\n";
        }
        echo'</select></div>'."\r\n";
        echo'<p><input class="button-primary" type="submit" value="'.esc_html(__('Choose custom field','church-admin')).'" ></p>'."\r\n";
        echo'</form>'."\r\n";
        return;
    }

    if(!empty($_POST['save'])){
        //sanitize
        $title = !empty($_POST['title']) ? church_admin_sanitize( $_POST['title']) : null;
        $trigger = !empty($_POST['trigger']) ? church_admin_sanitize( $_POST['trigger']) : null;
        $value = !empty($_POST['value'])  ? church_admin_sanitize( $_POST['value']) : null;
        



        //validate


        //save to db

        
        //set cron job


        //display success


        //link to all conditional automations list

    }



    /*********************
     *  FORM
     ********************/

    if(!empty($automation_id)){
        $data = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_automations WHERE automation_id="'.(int)$automation_id.'"');
    }

    $custom_field_data = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE ID="'.(int)$custom_id.'"');
    if(empty($custom_field_data)){
        echo '<div class="notice notice-danger"><h2>'.esc_html(__('Error with that custom field','church-admin')).'</h2></div>'."\r\n";
        return;

    }
    echo'<h3>'.esc_html(sprintf(__('Conditional custom field automation for %1$s','church-admin'),$custom_fields[$custom_id]['name'])).'</h3>';
    echo'<form action="" method="POST">';
    echo'<input type="hidden" name="custom_id" value="'.(int)$custom_id.'" >';
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Name for this conditional automation ','church-admin')).'</label>'."\r\n";
    echo'<input class="church-admin-form-control" type="text" name="title" ';
    if(!empty($data->title)){echo ' value="'.esc_attr($data->title).'" ';}
    echo ' required="required"/></div>';
    /******************
     * TRIGGER
     *****************/
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Trigger','church-admin')).'</label>'."\r\n";
    echo'<select class="church-admin-form-control" id="trigger" name="trigger">'."\r\n";
    echo '<option>'.esc_html(__('Choose...','church-admin')).'</option>'."\r\n";
    echo '<option value="days-after-registration">'.esc_html(__('Days after registration','church-admin')).'</option>';
    echo '<option value="change-of-value">'.esc_html(__('Value change','church-admin')).'</option>';
    echo'</select></div>';


    echo'<div class="church-admin-form-group" id="days-after-registration" style="display:none" ><label>'.esc_html(__('Days after registration','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="number" value="days-after-registration" ';
    if(!empty($data->action_data['days'])){ echo ' value="'.(int)$data->action_data['days'].'" ';}
    echo'/></div>';




    /******************
     * VALUE
     *****************/
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('What value for this automation?','church-admin')).'</label>'."\r\n";
    switch($custom_fields[$custom_id]['type']){

        case 'text':
            $value = !empty($data->value)?$data->value:null;
            echo'<input class="church-admin-form-control" type="text" name="value" value="'.esc_attr($value).'">';
        break;
        case 'date':
            $value = !empty($data->value)?$data->value:null;
            echo church_admin_date_picker( $value,'value',FALSE,NULL,NULL,NULL,NULL,FALSE,NULL,NULL,NULL);
        break;
        case 'boolean':
            $value = !empty($data->value)?1:0;
            echo'<br>';
            echo'<input type="radio" name="value" value="1" '.checked(1,$value,FALSE).'>'.esc_html(__('True','church-admin')).'<br>';
            echo'<input type="radio" name="value" value="0" '.checked(0,$value,FALSE).'>'.esc_html(__('False','church-admin')).'<br>';
        break;
        case 'radio':
        case 'select':
        case 'checkbox':
            echo'<br>';
            $options = maybe_unserialize($custom_field_data->options);
            if(!empty($options)){
                foreach($options AS $key=>$option)
                {
                    echo'<input type="radio" name="value" value="'.esc_attr($option).'"> '.esc_html($option).'<br>';
                }
            }
        break;

    }

    echo '</div>';
    /******************
     * ACTION
     *****************/
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Action?','church-admin')).'</label>'."\r\n";
    echo'<select id="action" class="church-admin-form-control">'."\r\n";
    echo '<option value=0>'.esc_html(__('Choose...','church-admin')).'</option>'."\r\n";
    echo '<option value="email">'.esc_html(__('Email')).'</option>';
    echo '<option value="member-type">'.esc_html(__('Change member type','church-admin')).'</option>';
    echo'</select>'."\r\n";
    echo'</div>'."\r\n";
    /******************
     * EMAIL SECTION
     *****************/
    echo'<div id="email" class="action-option" style="display:none">'."\r\n";
        echo'<h3>'.esc_html( __('Email','church-admin') ).'</h3>';
        //email from name
        echo'<div class="church-admin-form-group"><label>'.__("Email from name",'church-admin').'</label>';
        echo'<input type="text" name="happy_birthday_from_name" class="church-admin-form-control" ';
        if(!empty($data->from_name)) {
            echo 'value="'.esc_attr($data->from_name).'" ';
        }else{
            echo 'value="'.esc_attr(get_option('blog_name')).'" ';
        }
        echo'/></div>';
        //email from
        echo'<div class="church-admin-form-group"><label>'.__("From email address",'church-admin').'</label>'."\r\n";
        echo'<input type="text" name="happy_birthday_from_email" class="church-admin-form-control" ';
        if(!empty($data->from_email)) {
            echo 'value="'.esc_attr($data->from_email).'" ';
        }else{
            echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
        }
        $content   = !empty($data->action_data['message']) ? $data->action_data['message'] :'';
        $editor_id = 'message';
        echo'<p><strong>'.__('Message template','church-admin').'</strong></p>';
        wp_editor( $content, $editor_id );



    echo'</div>';

    echo'</div>'."\r\n";
    /******************
     * MEMBER TYPE SECTION
     *****************/ 
    $member_types= church_admin_member_types_array();
    
    echo'<div id="member-type"  class="action-option" style="display:none">'."\r\n";
        echo'<div class="church-admin-form-group"><label>'.__("Member type to change to...",'church-admin').'</label>'."\r\n";
        echo'<select class="church-admin-form-control"  name="member_type_id">'."\r\n";
        echo '<option value=0>'.esc_html(__('Choose...','church-admin')).'</option>'."\r\n";
        foreach($member_types AS $id => $member_type){
            echo '<option value="'.(int)$id.'">'.esc_html($member_type).'</option>';
        }
        echo'</select></div>';

    echo'</div>'."\r\n";
    echo'<p><input class="button-primary"  type="submit" value="'.__('Setup automation','church-admin').'"></p>';
    echo'</form>';
    /******************
    * jQuery magic
    *****************/ 
    echo'<script>
    jQuery(document).ready(function($){

        $("#trigger").on("change", function (e) {
            $("#days-after-registration").hide();
            var selected = $("option:selected", this).val();
            console.log(selected);
            if(selected==="days-after-registration"){ $("#days-after-registration").show();}

        });



        $("#action").on("change", function (e) {
            $(".action-option").hide();
            var selected = $("option:selected", this).val();
            console.log(selected);
            if(selected != 0){$("#"+selected).show();}
        });

    });
    </script>';
    
}