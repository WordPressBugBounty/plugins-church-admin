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
/*****************************
 * ONBOARDING EMAIL SETUP
 ****************************/
function church_admin_registration_follow_up_email()
{

    church_admin_debug('***** church_admin_registration_follow_up_email ******');
    global $wpdb,$church_admin_url;
    $user=wp_get_current_user();
    church_admin_debug($user);
    $person = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    $household_id = $person->household_id;
    church_admin_debug('Household id = '.$household_id);
    $household_details = !empty($household_id) ? church_admin_household_details_table($household_id):NULL;
    church_admin_debug($household_details);

    echo'<h2>'.esc_html(__('Registration flow','church-admin') ).'</h2>'."\r\n";
    echo'<p>'.esc_html(__("An easy way to get people onboarding to your church directory is to use the [church_admin type=register] shortcode or Register block on a page. People start wih entering their email and the plugin detects whether they need to register or login. Once they have registered, they will receive an email asking them to confirm their email address. You can set a follow up email to be sent if they don't. Then the admin receives an email about the new entry. You can opt whether the new registrant automatically gets a subscriber level user account, or an admin issues it. Once an account is created the new user receives details about their account. Below are the options and email  templates.",'church-admin') ).'</p>'."\r\n";
    
    
    $followup_template = get_option('church_admin_followup_email_template');
    if(!empty($_POST['save']))
    {
        //username_style
        switch($_POST['username_style']){
            default:
            case 'firstnamelastname':
                update_option('church_admin_username_style','firstnamelastname');
            break;
            case 'initiallastname':update_option('church_admin_username_style','initiallastname');break;
            case 'firstname.lastname':update_option('church_admin_username_style','firstname.lastname');break;
            case 'lastnamefirstname':update_option('church_admin_username_style','lastnamefirstname');break;
        }
        if(empty($_POST['username_style'])){update_option('church_admin_username_style','firstnamelastname');}
        //confirmation
        if(!empty($_POST['confirmation_subject']) && !empty($_POST['confirmation_message'])){
            $from_name = church_admin_sanitize($_POST['confirmation_from_name']);
            $from_email = church_admin_sanitize($_POST['confirmation_from_email']);
            $subject = church_admin_sanitize($_POST['confirmation_subject']);
            $message = wp_kses_post(wpautop(stripslashes($_POST['confirmation_message'])));
            update_option('church_admin_confirm_email_template',array('from_name'=>$from_name,'from_email'=>$from_email,'subject'=>$subject,'message'=>$message) );
        }
        //admin approval
        if ( empty( $_POST['admin-approval'] ) )
        {
            delete_option('church_admin_admin_approval_required');
        }else{
            update_option('church_admin_admin_approval_required',TRUE);
        }
        //admin new entry template
        if(!empty($_POST['admin_new_entry_template'])){
            update_option('church_admin_new_entry_admin_email',wp_kses_post(wpautop(stripslashes( $_POST['admin_new_entry_template'] ) ))) ;

        }
        //new user email template
        if(!empty($_POST['user_template'])){
            update_option('church_admin_user_created_email',wp_kses_post(wpautop(stripslashes( $_POST['user_template'] ) ))) ;
        }



        //followup email
       
        if(!empty($_POST['cancel_followup'])){
            if(!empty($followup_template['days'])){ $args = array('days'=>$followup_template['days']);}
            //undo set flag
            $followup_template = get_option('church_admin_followup_email_template');
            unset($followup_template['days']);
            update_option('church_admin_followup_email_template',$followup_template);
            wp_clear_scheduled_hook( 'church_admin_followup_email');
        }
        elseif(!empty($_POST['followup_subject']) && !empty($_POST['followup_message'])){
           
            $from_name = church_admin_sanitize($_POST['followup_from_name']);
            $from_email = church_admin_sanitize($_POST['followup_from_email']);
            $subject = church_admin_sanitize($_POST['followup_subject']);
            $message = wp_kses_post(wpautop(stripslashes($_POST['followup_message'])));
            $days = !empty($_POST['followup_days'])?(int)$_POST['followup_days']:2;
            $args = array('set'=>1,'days'=>$days,'from_name'=>$from_name,'from_email'=>$from_email,'subject'=>$subject,'message'=>$message);
          
          
            update_option('church_admin_followup_email_template',$args );

            //setup cron job
            $first_run = strtotime("0600 tomorrow");
            
			if (! wp_next_scheduled ( 'church_admin_followup_email')) {
				wp_schedule_event( $first_run, 'daily','church_admin_followup_email');
				
			}
        }
        echo'<div class="notice notice-sucess"><h2>'.esc_html(__('Follow up email automation settings saved','church-admin')).'</h2></div>'."\r\n";
    }

    echo'<script>
            var htmlbefore = "<html><head><style>*,::before,::after{box-sizing:border-box}html{font-family:system-ui,\"Segoe UI\",Roboto,Helvetica,Arial,sans-serif,\"Apple Color Emoji\",\"Segoe UI Emoji\";line-height:1.15;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4}body{margin:0}hr{height:0;color:inherit}abbr[title]{text-decoration:underline dotted}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:ui-monospace,SFMono-Regular,Consolas,\"Liberation Mono\",Menlo,monospace;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit}button,input,optgroup,select,textarea,p,th,td{font-family:inherit;font-size:100%;line-height:1.15;margin:0}button,select{text-transform:none}button,[type=\"button\"],[type=\"reset\"],[type=\"submit\"]{-webkit-appearance:button}::-moz-focus-inner{border-style:none;padding:0}p,table{margin-bottom:10px}:-moz-focusring{outline:1px dotted ButtonText}:-moz-ui-invalid{box-shadow:none}legend{padding:0}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=\"search\"]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}.play-button{box-sizing:border-box;position:relative;top:150px;left:200px;width: 22px;height: 22px}.gg-play-button-o {    box-sizing: border-box;position: relative;display: block;width: 60px;height: 60px;border: 2px solid;border-radius: 68px;color: red;}.gg-play-button-o::before {content: \"\";display: block;box-sizing: border-box;position: absolute;width: 0;height: 25px;border-top: 25px solid transparent;border-bottom: 25px solid transparent;border-left: 25px solid;top: 4px;left: 20px;}</style></head><body><div id=\"container\" style=\"width:90%;height:auto;margin:0 auto;padding:10px;background:#FFF\"><!--content-->";
            var htmlafter="<!--end-content--><p style=\"font-family:Arial;font-size:1em;\"><a href=\"'.site_url().'/?action=user-email-settings\">'.esc_html( __('Update which emails you receive', 'church-admin' ) ).'</a></p></div></body></html>";
            var household_details = "'. addslashes($household_details) .'";
            
    </script>'."\r\n";
    /***********************************
     * Confirmation email
     ***********************************/
    echo'<h2>'.__('Step 1 - new registration receives email so they can confirm their email address exists.','church-admin').'</h2>';
    echo'<form action="admin.php?page=church_admin%2Findex.php&action=registration-followup-email-setup" method="POST">';
    wp_nonce_field('registration-followup-email-setup');
    
    $registration_confirmation_template = get_option('church_admin_confirm_email_template');
    echo'<div class="church-admin-email-template-wrapper">';
        echo'<div class="church-admin-email-template-column">';
            echo'<h3>'.esc_html(__('Confirmation email template','church-admin')).'</h3>';
            echo '<div class="church-admin-form-group"><label>'.esc_html(__('From name','church-admin')).'</label>';
            echo'<input type="text" class="church-admin-form-control" name="confirmation_from_name" ';
            if(!empty($registration_confirmation_template['from_name']))
            {
                echo' value="'.esc_html($registration_confirmation_template['from_name']).'" ';
            }else{
                echo' value="'.esc_attr(get_option('blogname')).'" ';
            }
            echo'/></div>';
            echo '<div class="church-admin-form-group"><label>'.esc_html(__('From email address','church-admin')).'</label>';
            echo'<input type="text" class="church-admin-form-control" name="confirmation_from_email" ';
            if(!empty($registration_confirmation_template['from_email']))
            {
                echo' value="'.esc_html($registration_confirmation_template['from_email']).'" ';
            }else{
                echo' value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
            }
            echo'/></div>';
            echo '<div class="church-admin-form-group"><label>'.esc_html(__('"Confirm your email" subject','church-admin')).'</label>';
            echo'<input type="text" class="church-admin-form-control" name="confirmation_subject" ';
            if(!empty($registration_confirmation_template['subject'])) echo' value="'.esc_html($registration_confirmation_template['subject']).'" ';
            echo'/></div>';
            echo'<p>'.esc_html(__('Use html and these shortcodes [CONFIRM_LINK],[SITE_URL],[CHURCH_NAME],[CONFIRM_URL]','church-admin') ).'</p>';
        
            $content   = !empty($registration_confirmation_template['message']) ? $registration_confirmation_template['message'] :'';
            $editor_id = 'confirmation_message';
            echo'<p><strong>'.esc_html(__('"Confirm your email" message','church-admin')).'</strong></p>';
            wp_editor( $content, $editor_id,array(
                'tinymce' => array(
                    'init_instance_callback' => 'function(editor) {
                                editor.on("keyup", function(){
                                  
                                    console.log("Editor contents was modified. Contents: " + editor.getContent());
                                    var newContent = editor.getContent();
                                    newContent = newContent.replace("[HOUSEHOLD_DETAILS]",household_details);
                                    newContent = newContent.replace("[CONFIRM_LINK]", "'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'");
                                    newContent = newContent.replace("[SITE_URL]", "'.home_url().'");
                                    newContent = newContent.replace("[CONFIRM_URL]","<a target=\"_blank\" href=\"'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'\">'.esc_html( __( 'Click to confirm','church-admin') ).'</a>");
                                    document.getElementById("confirmation-email-preview").srcdoc = htmlbefore + newContent + htmlafter;
                                });
                        }'
                    ) )
                );
                
        echo'</div><!-- end of confirmation template form-->';
        echo'<div class="church-admin-email-template-column">';
            echo'<h3>'.__('Email Preview','church-admin').'</h3>';
            $html_content = !empty($content) ? church_admin_prep_html_email($content,'Confirm your email') :'';
            $html_content = str_replace('[HOUSEHOLD_DETAILS]',$household_details,$html_content);
            $html_content =str_replace('[CONFIRM_LINK]', home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id),$html_content);
            $html_content =str_replace('[SITE_URL]',home_url(),$html_content);
            $html_content =str_replace('[CHURCH_NAME]',get_bloginfo('name'),$html_content);
            $html_content =str_replace('[CONFIRM_URL]',' <a target="_blank" href="'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'">'.esc_html( __( 'Click to confirm','church-admin') ).'</a>',$html_content);
            echo'<iframe id="confirmation-email-preview" class="church-admin-email-template-container" srcdoc=\''.$html_content.'\'></iframe>';
            
        echo'</div>';
    echo'</div><!-- end of display result confirmation template -->';
    echo'<hr/>';
    /************************************
     * Admin Approval
     *************************************/
    echo'<h2>'.__('Step 2 - administrators can receive an email informing them of the new site registration.','church-admin').'</h2>';
    
    echo'<p>'.esc_html('It is best practice for anyone in your congregation who has an email to have a username/password combination so they can view restricted content and edit their entry (if you use the register/login block and shortcode. Here you can choose whether that is automated or not. If unchecked, admins will need to edit the person and click "Create User" to give the individual an account.','church-admin').'</p>';
    $adminApproval=get_option('church_admin_admin_approval_required');
    echo'<div class="church-admin-checkbox">';
    echo'<input type="checkbox" name="admin-approval" value="1" ';
    if(!empty( $adminApproval) ) echo' checked="checked" ';
    echo'/>';
    echo'<label>'.esc_html( __('Do you want admin approval before account creation?','church-admin' ) ).'</label>';
    
    echo'</div>';
    $username_style = get_option('church_admin_username_style');
   
    echo'<h3>'.__('Choose username format using "John Smith" as an example','church-admin').'</h3>';
    echo'<div class="church-admin-checkbox"><input type="radio" name="username_style" value="firstnamelastname" '.checked('firstnamelastname',$username_style,FALSE).' ><label>'.esc_html(__('firstnamelastname e.g. johnsmith','church-admin')).'</label></div>';
    echo'<div class="church-admin-checkbox"><input type="radio" name="username_style" value="initiallastname" '.checked('initiallastname',$username_style,FALSE).' ><label>'.esc_html(__('initiallastname e.g. jsmith','church-admin')).'</label></div>';
    echo'<div class="church-admin-checkbox"><input type="radio" name="username_style" value="firstname.lastname" '.checked('firstname.lastname',$username_style,FALSE).' ><label>'.esc_html(__('firstname.lastname e.g. john.smith','church-admin')).'</label></div>';
    echo'<div class="church-admin-checkbox"><input type="radio" name="username_style" value="lastnamefirstname" '.checked('lastnamefirstname',$username_style,FALSE).' ><label>'.esc_html(__('lastnamefirstname e.g. smithjohn','church-admin')).'</label></div>';

    echo'<div class="church-admin-email-template-wrapper">';
    echo'<div class="church-admin-email-template-column">';       
    
        echo'<h2>'.esc_html(__('Email template for admins when new entry has confirmed their email','church-admin')).'</h2>';
        echo'<p>'.__('[HOUSEHOLD_DETAILS] will display a table of the household details','church-admin').'</p>';
        $admin_email_message=get_option('church_admin_new_entry_admin_email');

        $content   = !empty( $admin_email_message) ?  $admin_email_message :'';
        $editor_id = 'admin_new_entry_template';
        wp_editor( $content, $editor_id ,array(
            'tinymce' => array(
                'init_instance_callback' => 'function(editor) {
                            editor.on("keyup", function(){
                                console.log(household_details);
                                console.log("Editor contents was modified. Contents: " + editor.getContent());
                                var newContent = editor.getContent();
                                newContent = newContent.replace("[HOUSEHOLD_DETAILS]",household_details);
                                newContent = newContent.replace("[CHURCH_NAME]","'.get_bloginfo('name').'");
                                newContent = newContent.replace("[CONFIRM_LINK]", "'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'");
                                newContent = newContent.replace("[SITE_URL]", "'.home_url().'");
                                newContent = newContent.replace("[CONFIRM_URL]","<a target=\"_blank\" href=\"'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'\">'.esc_html( __( 'Click to confirm','church-admin') ).'</a>");
                                document.getElementById("admin-email-preview-container").srcdoc = htmlbefore + newContent + htmlafter;
                            });
                    }'
                ) )
            );
    echo'</div>';
   
    echo'<div class="church-admin-email-template-column">';
            echo'<p>'.esc_html(__('For demo purposes, your household details are displayed','church-admin') ).'</p>';
            
            if(!empty($household_details))$content = str_replace('[HOUSEHOLD_DETAILS]',$household_details,$content);
            echo'<h3>'.__('Email Preview','church-admin').'</h3>';
            $html_content = !empty($content) ? church_admin_prep_html_email($content,'Confirm your email') :'';
           
            echo'<iframe id="admin-email-preview-container" class="church-admin-email-template-container" srcdoc=\''.$html_content.'\'></iframe>';
            
        echo'</div>';
    echo'</div><!-- end of amin email new registration template -->';
    echo'<hr/>';


     /***********************************
     * Follow up Email
     ***********************************/
    
    echo'<h2>'.esc_html(__('Step 3 Follow up email','church-admin')).'</h2>';
    echo'<p>'.esc_html(__("If the new registrant, does not click on the confirmation email link, you can setup thisreminder email to get them to confirm their email address",'church-admin')).'</p>';
    echo'<div class="church-admin-email-template-wrapper">';
    echo'<div class="church-admin-email-template-column">'; 
        $followup_template = get_option('church_admin_followup_email_template');
        echo '<div class="church-admin-form-group"><label>'.esc_html(__('From name','church-admin')).'</label>';
        echo'<input type="text" class="church-admin-form-control" name="followup_from_name" ';
        if(!empty($followup_template['from_name']))
        {
            echo' value="'.esc_html($followup_template['from_name']).'" ';
        }else{
            echo' value="'.esc_attr(get_option('blogname')).'" ';
        }
        echo'/></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html(__('From email address','church-admin')).'</label>';
        echo'<input type="text" class="church-admin-form-control" name="followup_from_email" ';
        if(!empty($followup_template['from_email']))
        {
            echo' value="'.esc_html($followup_template['from_email']).'" ';
        }else{
            echo' value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
        }
        echo'/></div>';

        if(!empty($followup_template['set'])){
            echo'<div class="church-admin-form-group"><label>'.__('Cancel automatic followup email','church-admin').'</label><input type="checkbox" name="cancel_followup"></div>';
        }
        else {
            echo'<p>'.esc_html('No automation set currently','church-admin').'</p>';
        }
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('How many days after first registering','church-admin')).'<label>';
        echo'<input type="number" class="church-admin-form-control" name="followup_days" ';
        if(!empty($followup_template['days'])){ echo ' value="'.(int)$followup_template['days'].'" ';}
        echo'></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html(__('Follow up email subject','church-admin')).'</label>';
        echo'<input type="text" class="church-admin-form-control" name="followup_subject" ';
        if(!empty($followup_template['subject'])) echo' value="'.esc_html($followup_template['subject']).'" ';
        echo'/></div>';
        echo'<p>'.esc_html(__('Use HTML and these shortcodes [CONFIRM_URL] (which nudges a confirmation email click),[NAME],[CHURCH_URL]','church-admin') ).'</p>';
      
        $content   = !empty($followup_template['message']) ? $followup_template['message']:'';
        $editor_id = 'followup_message';
        
        wp_editor( $content, $editor_id ,array(
            'tinymce' => array(
                'init_instance_callback' => 'function(editor) {
                            editor.on("keyup", function(){
                                console.log(household_details);
                                console.log("Editor contents was modified. Contents: " + editor.getContent());
                                var newContent = editor.getContent();
                                newContent = newContent.replace("[HOUSEHOLD_DETAILS]",household_details);
                                newContent = newContent.replace("[CHURCH_NAME]","'.get_bloginfo('name').'");
                                newContent = newContent.replace("[CONFIRM_LINK]", "'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'");
                                newContent = newContent.replace("[SITE_URL]", "'.home_url().'");
                                newContent = newContent.replace("[CONFIRM_URL]","<a target=\"_blank\" href=\"'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'\">'.esc_html( __( 'Click to confirm','church-admin') ).'</a>",newContent);
                                document.getElementById("follow-up-email-template-preview").srcdoc = htmlbefore + newContent + htmlafter;
                            });
                    }'
                ) ));        
    echo'</div>';
    echo'<div class="church-admin-email-template-column">';
        echo'<h3>'.__('Email Preview','church-admin').'</h3>';
        $html_content = !empty($content) ? church_admin_prep_html_email($content,'Confirm your email') :'';
       
        echo'<iframe id="follow-up-email-template-preview" class="church-admin-email-template-container" srcdoc=\''.$html_content.'\'></iframe>';
    
    echo'</div>';
    echo'</div><!-- end of follow up email template -->';
    echo'<hr/>';


    echo'<h2>'.esc_html(__('Last step! New user email template','church-admin')).'</h2>';
	echo'<p>'.wp_kses_post(__('This is the tempate for the email message that is sent when a new user is created from Church Admin plugin. You can use [SITE_URL], [USERNAME], [PASSWORD] as shortcodes to be replaced by the relevant data. Premium plugin users can also use [ANDROID] and [IOS] for app store links - although https://ourchurchapp.online takes users to the correct app store on their device.','church-admin')).'</p>';
    echo'<div class="church-admin-email-template-wrapper">';
    echo'<div class="church-admin-email-template-column">'; 
        $user_email_message=get_option('church_admin_user_created_email');
        $content   = !empty($user_email_message) ? $user_email_message :'';
        $editor_id = 'user_template';
        
        wp_editor( $content, $editor_id,array(
            'tinymce' => array(
                'init_instance_callback' => 'function(editor) {
                            editor.on("keyup", function(){
                              
                                console.log("Editor contents was modified. Contents: " + editor.getContent());
                                var newContent = editor.getContent();
                                newContent = newContent.replace("[HOUSEHOLD_DETAILS]",household_details);
                                newContent = newContent.replace("[CHURCH_NAME]","'.get_bloginfo('name').'");
                                newContent = newContent.replace("[CONFIRM_LINK]", "'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'");
                                newContent = newContent.replace("[SITE_URL]", "'.home_url().'");
                                newContent = newContent.replace("[CONFIRM_URL]","<a target=\"_blank\" href=\"'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'\">'.esc_html( __( 'Click to confirm','church-admin') ).'</a>",newContent);
                                document.getElementById("new-user-email-preview").srcdoc = htmlbefore + newContent + htmlafter;
                            });
                    }'
                ) ) );
    echo'</div>';
    echo'<div class="church-admin-email-template-column">';
        echo'<h3>'.__('Email Preview','church-admin').'</h3>';
        $html_content = !empty($content) ? church_admin_prep_html_email($content,'Confirm your email') :'';
       
        echo'<iframe id="new-user-email-preview" class="church-admin-email-template-container" srcdoc=\''.$html_content.'\'></iframe>';

    echo'</div>';
    echo'</div><!-- end of follow up email template -->';
  
   

    echo'<p><input type="hidden" name="save" value="1"><input type="submit" class="button-primary" value="'.esc_html(__('Save','church-admin') ).'"></p>';
    echo'</form>';
    


   






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