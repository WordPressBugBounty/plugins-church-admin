<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly




function church_admin_push()
{
    global $wpdb;
    require_once(plugin_dir_path(__FILE__).'/filter.php');
    $licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
		church_admin_buy_app();

	}
    else
    {
        if(!empty( $_POST['push'] ) )
        {
         
            $user=wp_get_current_user();
            $sender = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
            
            $pushTokens=array();
            $subject = !empty($_POST['subject']) ? church_admin_sanitize($_POST['subject']) : null;
            $message= !empty($_POST['message']) ? strip_tags(church_admin_sanitize( $_POST['message'] ) ) : null;
            if(empty($subject)){echo '<div class="notice notice-danger">'.esc_html(__('No subject','church-admin') ); return;}
            if(empty($message)){echo '<div class="notice notice-danger">'.esc_html(__('No message','church-admin') ); return;}

            $sql=church_admin_build_filter_sql( church_admin_sanitize( $_POST['check'] ) ,'push');
            $results=$wpdb->get_results( $sql);
            if(!empty( $results) )
            {
                echo'<p>'.esc_html( __('Preparing to send to...','church-admin' ) ).'</p>';
                echo esc_html(__('You','church-admin' ) ).'<br>';
                foreach( $results as $row)
                {
                    if(!empty( $row->pushToken) )
                    {
                        $name=implode(' ',array_filter(array( $row->first_name,$row->prefix,$row->last_name) ));
                        echo esc_html( $name ).'<br>';
                        $pushTokens[]=$row->pushToken;
                    }
                }
                if(!in_array( $sender->pushToken,$pushTokens) )$pushTokens[]=$sender->pushToken;
                if(!empty( $pushTokens) )  {
                   
                    echo church_admin_send_push('tokens','message',$pushTokens,$subject,$message,church_admin_formatted_name($sender));
                }
                else{
                    echo'<p>'.esc_html( __('No logged in app users to send to','church-admin' ) ).'</p>';
                }

            }else{
                echo'<p>'.esc_html( __('Nobody found from those filter results','church-admin' ) ).'</p>';
            }
        }
        else
        {
            echo '<h2>'.esc_html( __('Send a push message','church-admin' ) ).'</h2>';
            echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=push-to-all&amp;section=comms','push-to-all').'" >'.esc_html( __('Push to all app users, whether logged in our not','church-admin' ) ).'</a></p>';
            
            echo '<form action="" method="post" name="PUSH" id="push">';
            echo '<div class="church-admin-form-group"><label>'.esc_html(__('Subject','church-admin') ).'</label><input class="church-admin-form-control" type="text" required="required" name="subject"></div>';
            echo '<div class="church-admin-form-group"><label>'.esc_html( __('Message','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" required="required" name="message"></div>'; 
            echo '<p><strong>'.esc_html( __('You will also get the message just so you can see it!','church-admin' ) ).'<strong></p>';
            
            
            church_admin_directory_filter(FALSE,TRUE);

            $nonce = wp_create_nonce("church_admin_filter",'filter');
            echo'<script type="text/javascript">
                jQuery(document).ready(function( $) {

            //handle send button disabled while no selections
             //$(\':input[type="submit"]\').prop(\'disabled\', true);
             $(\'input[type="text"]\').keyup(function() {
                if( $(this).val() != "") {
                   $(\':input[type="submit"]\').prop(\'disabled\', false);
                   $("#filtered-response").html("");
                }
             });



                    $(".all").on("change", function()  {
                        var id = this.id;

                        $("input."+id).prop("checked", !$("."+id).prop("checked") )
                    });
                   $("#filters1").on("change", function()  {

                        var category_list = [];
                        $("#filters1 :input:checked").each(function()  {

                            $(\':input[type="submit"]\').prop(\'disabled\', false);

                            var category = $(this).val();
                            category_list.push(category);

                        });


                        var data = {
                        "action": "church_admin",
                        "method":"category_list",
                        "data": category_list,
                        "nonce": "'.$nonce.'"
                        };
                        console.log(data);
            $("#filtered-response").html(\'<p style="text-align:center"><img src="'.admin_url().'/images/wpspin_light-2x.gif" /></p>\');
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                jQuery.post(ajaxurl, data, function(response) {
                    $("#filtered-response").html("<h3>"+response+"</h3>");
                    $(\':input[type="submit"]\').prop(\'disabled\', false);
                });
                    });
                });
            </script>
            ';

            //end of choose recipients


            echo'<p><br style="clear:left" /><input type="hidden" name="push" value="yes" /><input class="button-primary" type="submit" name="submitted" value="'.esc_html( __('Push Message','church-admin' ) ).'" /></p></form>';  // Translation JF 8.02.18
        }
        }
}





function church_admin_old_push()
{
    global $wpdb;
    require_once(plugin_dir_path(__FILE__).'/filter.php');
    $licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
		church_admin_buy_app();

	}
    else
    {
        if(!empty( $_POST['push'] ) )
        {
            $user=wp_get_current_user();
            $myPushToken=$wpdb->get_var('SELECT pushToken FROm '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'" AND pushToken!=""');
            $pushTokens=array();
            $message=esc_html(sanitize_text_field(stripslashes( $_POST['message'] ) ) );
            $sql=church_admin_build_filter_sql( church_admin_sanitize( $_POST['check'] ) ,'push');
            $results=$wpdb->get_results( $sql);
            if(!empty( $results) )
            {
                echo'<p>'.esc_html( __('Preparing to send to...','church-admin' ) ).'</p>';
                echo esc_html(__('You','church-admin' ) ).'<br>';
                foreach( $results as $row)
                {
                    if(!empty( $row->pushToken) )
                    {
                        $name=implode(' ',array_filter(array( $row->first_name,$row->prefix,$row->last_name) ));
                        echo esc_html( $name).'<br>';
                        $pushTokens[]=$row->pushToken;
                    }
                }
                if(!in_array( $myPushToken,$pushTokens) )$pushTokens[]=$myPushToken;
                if(!empty( $pushTokens) )  {church_admin_filtered_push( $message,$pushTokens,'Our Church App',$message);}
                else{echo'<p>'.esc_html( __('No logged in app users to send to','church-admin' ) ).'</p>';}

            }else{echo'<p>'.esc_html( __('Nobody found from those filter results','church-admin' ) ).'</p>';}
        }
        else
        {
            echo'<h2>'.esc_html( __('Send a push message','church-admin' ) ).'</h2>';
            echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=push-to-all&amp;section=comms','push-to-all').'" class="button-primary">'.esc_html( __('Push to all app users, whether logged in our not','church-admin' ) ).'</a></p>';
            echo'<p><strong>'.esc_html( __('Or use these filters to send to logged in app users','church-admin' ) ).'</strong></p>';
            echo'<form action="" method="post" name="PUSH" id="push">
        <div class="church-admin-form-group"><label>'.esc_html( __('Message','church-admin' ) ).'</label><textarea  class="church-admin-form-control" rows="4" cols="50" name="message">  </textarea></div>'; 
            echo'<p><strong>'.esc_html( __('You will also get the message just so you can see it!','church-admin' ) ).'<strong></p>';
            
            
            church_admin_directory_filter(FALSE,TRUE);

            $nonce = wp_create_nonce("church_admin_filter",'filter');
            echo'<script type="text/javascript">
                jQuery(document).ready(function( $) {

            //handle send button disabled while no selections
             //$(\':input[type="submit"]\').prop(\'disabled\', true);
             $(\'input[type="text"]\').keyup(function() {
                if( $(this).val() != "") {
                   $(\':input[type="submit"]\').prop(\'disabled\', false);
                   $("#filtered-response").html("");
                }
             });



                    $(".all").on("change", function()  {
                        var id = this.id;

                        $("input."+id).prop("checked", !$("."+id).prop("checked") )
                    });
                   $("#filters1").on("change", function()  {

                        var category_list = [];
                        $("#filters1 :input:checked").each(function()  {

                            $(\':input[type="submit"]\').prop(\'disabled\', false);

                            var category = $(this).val();
                            category_list.push(category);

                        });


                        var data = {
                        "action": "church_admin",
                        "method":"category_list",
                        "data": category_list,
                        "nonce": "'.$nonce.'"
                        };
                        console.log(data);
            $("#filtered-response").html(\'<p style="text-align:center"><img src="'.admin_url().'/images/wpspin_light-2x.gif" /></p>\');
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                jQuery.post(ajaxurl, data, function(response) {
                    $("#filtered-response").html("<h3>"+response+"</h3>");
                    $(\':input[type="submit"]\').prop(\'disabled\', false);
                });
                    });
                });
            </script>
            ';

            //end of choose recipients


            echo'<p><br style="clear:left" /><input type="hidden" name="push" value="yes" /><input class="button-primary" type="submit" name="submitted" value="'.esc_html( __('Push Message','church-admin' ) ).'" /></p></form>';  // Translation JF 8.02.18
        }
        }
}
/*********************************************************
*
*   Sends $message Push Message out to array of $pushTokens
*
**********************************************************/
function church_admin_filtered_push( $message,$pushTokens,$title,$dataMessage,$type='message',$mobile=NULL)
{

    if ( empty( $type) )$type='message';
    if ( empty( $title) )  {$title="Our Church App";}
    if ( empty( $message) )
    {
        church_admin_debug('No message for church_admin_filtered_push');
        return FALSE;
    }
    if ( empty( $pushTokens)||!is_array( $pushTokens) )
    {
        church_admin_debug('No pushTokens arrayfor church_admin_filtered_push');
        return FALSE;
    }
    global $wpdb;
    $user=wp_get_current_user();
	if(!empty( $user->ID) )$username=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    if ( empty( $username) )$username=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql(get_option('church_admin_default_from_email') ).'"');
    if ( empty( $username) )$username=__('Automatic push notification');
    //app
	$api_key="AAAA50JK2is:APA91bE-SZWcUncaSxdbevuGOdochq7zS2fgJabNBAmbqBnmR8Lq4BoaQwG_p-JM2Ftx5rAKInlnG5RmxhWW_LcOPW9A9cQqpg7tUA1GFi1-NvX2q5YbFqnM9ZmV5xuE0PfeRWFUL1d4Te4zwzpu5qglwzZpg_JWzg";
	
	$url = 'https://fcm.googleapis.com/fcm/send';
	$headers = array('Authorization: key=' . $api_key,'Content-Type: application/json');
					
                    
                    //updated for iOS13 which requires APNS headers
                    
                    
                    $data=array("notification"=>array("title"=>$title,
													  "body"=>$message,
													  "sound"=>"default",
													  //"click_action"=>"FCM_PLUGIN_ACTIVITY",
													  "icon"=>"fcm_push_icon",
													  "content_available"=> 1,
                                                      'apnsPushType'=>'alert'
													 ),
                                  "apns"=> array(
                                            'headers'=> array( 
                                                        'apns-push-type'=> 'alert',
                                                        "apns-priority"=>5,
                                                        "apns-topic"=>"com.churchadminplugin.wpchurch"
                                            ),
                                            "payload"=>array("alert"=>array("title"=>$title,"body"=>$message),
                                                             "aps"=>array( "content_available"=>1),
                                                             "sound"=>"default",
                                                             "content_available"=>1
                                                            ),
                        
                                ),
								"data"=>array(  "notification_foreground"=>TRUE,
                                                "notification_body" => $message,
                                                "notification_title"=> "Church App",
                                              "notification_android_priority"=>1,
                                              "notification_ios_sound"=>"default",
                                              "sound"=>"default",
                                                "title"=>$title,
											  "body"=>$message,
											  "type"=>$type,
											  "senderName"=>$username,
												"timestamp"=>date(get_option('time_format').' '.get_option('date_format') )
										),
								"registration_ids"=>$pushTokens,
								"priority"=>"high"
								);
                    if( $type=='sms-thread' &&!empty( $mobile) )
                    {
                        $data['data']['mobile']=esc_html( $mobile);
                    }
                    if(defined('CA_DEBUG') )church_admin_debug(print_r( $data,TRUE) );            
			        
					$ch = curl_init ();
    				curl_setopt ( $ch, CURLOPT_URL, $url );
    				curl_setopt ( $ch, CURLOPT_POST, true );
    				curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
    				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	    			curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode( $data) );

    				$result = curl_exec ( $ch );
                    $resultObj=json_decode( $result);
                    if(defined('CA_DEBUG') )church_admin_debug(print_r( $resultObj,TRUE) );
                    if(is_admin() )
                    {
                        //only echo message status on admin page
    				    if( $resultObj->success)  {echo'<p>'.esc_html( __('Push message(s) sent successfully','church-admin' ) ).'</p>';}
                        else
                        {
                            echo'<p>'.esc_html( __('Push message(s) failed','church-admin' ) ).'</p>';
                            print_r( $resultObj);
                        }
                    }
                    curl_close ( $ch );
}
