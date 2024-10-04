<?php    
function church_admin_profile()
{
    global $wpdb;
    echo'<!doctype html><head><title>'.esc_html( __('User Email Settings','church-admin' ) ).'</title><style>body{text-align: center; padding: 150px;font:20px Helvetica, sans-serif; color: #333;}h1{font-size:50px;}article{display: block; text-align:left; width:650px; margin:0 auto; }a{color:#dc8100; text-decoration:none; }a:hover{color:#333;text-decoration:none; }</style>';
    echo'<script src="'.site_url().'/wp-includes/js/jquery/jquery.min.js?ver=3.6.1" id="jquery-core-js"></script>';
    echo'</head><body><article><h2>'.get_bloginfo('name').'</h2>';
    
    global $wpdb;

    if(!empty( $_REQUEST['email-address'] )&&empty( $_REQUEST['token'] ) )
    {
        if ( empty( $_REQUEST['email-address'] )||!is_email(church_admin_sanitize( $_REQUEST['email-address'] ) ))  {
            echo'<p>'.esc_html( __('Email not recognised','church-admin' ) ).'</p><p><a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin' ) ).'</p></article></body></html>';
            exit();
        }
        //send token to email address
        $people=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql(church_admin_sanitize( $_REQUEST['email-address'] ) ).'"');
       
        if(!empty( $people) )
        {
            //create token and save
            $bytes = random_bytes(20);
            $token=bin2hex( $bytes);
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET token="'.esc_sql( $token).'",token_date="'.date('Y-m-d').'" WHERE people_id="'.(int)$people->people_id.'"' );
            //send email
            
            $message='<p>'.esc_html( __('Thank you for clicking the link in an email to adjust your preferences. To make sure it is you, please click the link in this email to login and adjust which emails you will receieve (or not!) in future.','church-admin' ) ).'<p><a href="'.esc_url(site_url().'/?action=user-email-settings&email-address='.$people->email.'&token='.$token).'">'.esc_html( __('Login link.','church-admin' ) ).'</a></p>';
            $subject='Login link to adjust email preferences';
            $to=church_admin_formatted_name( $people).'<'.$people->email.'>';

            church_admin_email_send($people->email,esc_html(__('Login link to update email preferences','church-admin' ) ),$message,null,null,null,null,null,TRUE);

            echo'<p>'.esc_html( __('A login link has been sent to your email address','church-admin' ) ).'</p>';;
           
        }
        else
        {
            echo'<p>'.esc_html( __('Your email was not found in the directory','church-admin' ) ).'</p>';
            $register_page = get_option('church_admin_register_page');
            if(!empty($register_page) ) {
                // do stuff
                echo'<p><a href="'.esc_url($register_page).'">'.__('Register to receive emails','church-admin').'</a></p>';
            }
            echo'<p><a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin' ) ).'</p></article></body></html>';
            exit();
        }
    }
    elseif(!empty( $_REQUEST['email-address'] ) && !empty( $_REQUEST['token'] ) )
    {
        if ( empty( $_REQUEST['email-address'] )||!is_email(church_admin_sanitize($_REQUEST['email-address'] ) ))  {
            echo'<p>'.esc_html( __('Email not recognised','church-admin' ) ).'</p><p><a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin' ) ).'</p></article></body></html>';
            exit();
        }
        $people=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql(church_admin_sanitize( $_REQUEST['email-address'] ) ).'"');
        if ( empty( $people) )exit();
        if( $people->token!=church_admin_sanitize( $_REQUEST['token'] ) )
        {
            echo'<p>'.esc_html( __('Login link not recognised','church-admin' ) ).'</p>';
            echo'<p>'.esc_html( __('Try logging in','church-admin' ) ).'</p>';
            wp_login_form();
            echo '<p><a href="'.wp_lostpassword_url().'>'.esc_html( __('Reset password')).'</a></p>';
            echo'</article></body></html>';
            exit();
        }
        
        if(!empty( $_REQUEST['save-profile'] )&&  wp_verify_nonce( $_REQUEST['save-profile'],'save-profile') )
        {
           
            //update users profile
            $show_me=!empty( $_POST['show_me'] )?1:0;
            $mail_send=!empty( $_POST['mail_send'] )?1:0;
            $email_send=!empty( $_POST['email_send'] )?1:0;
            $news_send=!empty( $_POST['news_send'] )?1:0;
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET show_me="'.$show_me.'", mail_send="'.$mail_send.'", email_send="'.$email_send.'",news_send="'.$news_send.'" WHERE people_id="'.$people->people_id.'"');
            if(!empty( $_POST['prayer_requests'] ) )
            {   
                church_admin_update_people_meta(1,$people->people_id,"prayer-requests");
            }
            else
            {
                $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta  WHERE people_id="'.(int)$people->people_id.'" AND meta_type="prayer-requests"');
            }
            if(!empty( $_POST['bible_readings'] ) )
            {   
                church_admin_update_people_meta(1,$people->people_id,"bible_readings");
            }
            else
            {
                $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta  WHERE people_id="'.(int)$people->people_id.'" AND meta_type="bible_readings"');
            }
         
            echo'<h3>'.esc_html( __('Privacy settings updated. Thanks.','church-admin' ) ).'</h3><p><a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin' ) ).'</p></article></body></html>';
            exit();
        }
        else
        {
            //output profile form
            
           
              echo'<h2>'.esc_html(sprintf(__('Welcome back %1$s','church-admin' ) ,church_admin_formatted_name( $people)) ).'</h2>';
                echo'<p>'.esc_html( __('Adjust your privacy settings','church-admin' ) ).'</p>';
                echo'<form action="" method="post">';
                echo'<div class="church-admin-form-group"><label>'.esc_html( __('I give permission...','church-admin' ) ).'</label></div>';
                echo'<div class="checkbox"><label ><input type="checkbox" id="email_send" name="email_send" value="TRUE" data-name="email_send"  class="email-permissions"';
                if(!empty( $people->email_send) )echo'checked="checked" ';
                echo'/> '.esc_html( __('To receive email','church-admin' ) ).'</label></div>';
                echo'<p>'.esc_html( __('Refine type of email you can receive','church-admin' ) ).'</p>';
                echo'<div class="checkbox"><label ><input type="checkbox" name="news_send" id="news_send" value="TRUE"  class="email-permissions" data-name="news_send"  ';
                if(!empty( $people->news_send) )echo'checked="checked" ';
                echo'/> '.esc_html( __('To receive blog posts by email','church-admin' ) ).'</label></div>';
                //PRAYER REQUESTS
                if(post_type_exists('prayer-requests') )
                {
                    echo'<div class="checkbox"><label ><input type="checkbox" value="1" id="prayer_requests" data-name="prayer_chain"  class="email-permissions"  name="prayer_requests" ';
                    if(!empty( $people->people_id) )$prayer=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta  WHERE people_id="'.(int)$people->people_id.'" AND meta_type="prayer-requests"');
                    if(!empty( $prayer) ) echo' checked="checked" ';
                    echo' /> '.esc_html( __('To receive Prayer requests by email','church-admin' ) ).'</label></div>';
                }
                //BIBLE READINGS
                if(post_type_exists('bible-readings') )
                {
                   echo'<div class="checkbox"><label ><input type="checkbox" value="1" id="bible_readings" data-name="bible_readings"  class="email-permissions"  name="bible_readings" ';
                    if(!empty( $people->people_id) )$bible=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta  WHERE people_id="'.(int)$people->people_id.'" AND meta_type="bible-readings"');
                    if(!empty( $bible) ) echo' checked="checked" ';
                    echo' /> '.esc_html( __('To receive new Bible Reading notes by email','church-admin' ) ).'</label></div>';
                }
                
                echo'<p>'.esc_html( __('Other privacy permissions','church-admin' ) ).'</p>';
                echo'<div class="checkbox"><label ><input type="checkbox" name="photo_permission" value="TRUE" data-name="photo_permission"  ';
                if(!empty( $people->photo_permission) )echo'checked="checked" ';
                echo'/> '.esc_html( __('To use my photo in the directory and on the website','church-admin' ) ).'</label></div>';
                echo'<div class="checkbox"><label ><input type="checkbox" name="sms_send" value="TRUE" data-name="sms_send"  ';
                if(!empty( $people->sms_send) )echo'checked="checked" ';
                echo'/> '.esc_html( __('To receive SMS','church-admin' ) ).'</label></div>';
                
                echo'<div class="checkbox"><label ><input type="checkbox" name="mail_send" value="TRUE" data-name="mail_send" ';
                if(!empty( $people->mail_send) )echo'checked="checked" ';
                echo'/> '.esc_html( __('To receive mail','church-admin' ) ).'</label></div>';
        
                echo'<div class="checkbox"><label ><input type="checkbox" name="show_me" value="TRUE" data-name="show_me" ';
                if(!empty( $people->show_me) )echo'checked="checked" ';
                echo'/> '.esc_html( __('To show me on the password protected address list','church-admin' ) ).'</label></div>';
                $nonce = wp_create_nonce('save-profile');
                echo'<p><input type="hidden" name="save-profile" value="'.$nonce.'" /><input type="submit" name="save" value="'.esc_html( __('Save Settings','church-admin' ) ).'" /></p></form>';
                echo'<script>jQuery(document).ready(function( $)  {
                    
                    if( $("#email_send").prop("checked")== false)
                    {
                        console.log("Unchecking");
                        $("#news_send").prop( "checked", false );
                        $("#prayer_requests").prop( "checked", false );
                        $("#bible_readings").prop( "checked", false );
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
                echo'</article></body></html>';
                exit();
        }
           
    }
    else
    {
        //form to send email token
        echo '<h2>'.esc_html( __('Welcome','church-admin' ) ).'</h2>';
        echo'<p>'.esc_html( __("Please enter your email address to be emailed a temporary login token",'church-admin' ) ).'</p>';
        echo'<form action="" method="GET">';
        echo'<p><input type="email" placeholder="'.esc_html( __('Email address','church-admin' ) ).'" name="email-address" /><input type="hidden"  name="action" value="user-email-settings" /></p><p><input type="submit" value="'.esc_html( __("Go",'church-admin' ) ).'" /></p></form>';
        echo'</article></body></html>';
        exit();
    }

    echo'<p><a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin' ) ).'</p></article></body></html>';
}