<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_send_sms()
{
    echo'<h2>'.esc_html( __('Send SMS','church-admin')).'</h2>';
    /***************************************
    *
    * Make sure SMS provider is uptodate
    *
    **************************************/
    $errors=array();
    $smsProvider= get_option('church_admin_sms_provider');
    if ( empty( $smsProvider) )
    {
        
        $bulksms=get_option('church_admin_bulksms');
        if(!empty( $bulksms) )
        {
            $smsProvider='bulksms';
            update_option('church_admin_sms_provider','bulksms.com');
            delete_option('church_admin_bulksms');
        }
        $cloudservicezm=get_option('church_admin_cloudservicezm');
        if(!empty( $cloudservicezm) )
        {
            $smsProvider='cloudservicezm.com';
            update_option('church_admin_sms_provider','cloudservicezm.com');
            delete_option('church_admin_cloudservicezm');
        }
        if ( empty( $smsProvider) )$errors[]=_('SMS Provider not setup','church-admin');
    }
    /**********************************************
     * Check credentials
     *********************************************/
    
    switch( $smsProvider)
    {
        case 'twilio':
            $sms_SID=get_option('church_admin_twilio_SID');
            $sms_Token=get_option('church_admin_twilio_token');
            if ( empty( $sms_SID) )$errors[]=__("No Twilio SID stored",'church-admin');
            if ( empty( $sms_Token) )$errors[]=__("No Twilio Token stored",'church-admin');

        break;
    }
    if(!empty( $errors) )
    {
        echo'<div class="notice notice=danger"><h2>'.esc_html( __("SMS setup required",'church-admin' ) ).'</h2>';
        echo '<p>'.implode('<br>',$errors).'</p>';
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=sms-settings','sms-settings').'" class="button-primary">'.esc_html( __("SMS setup",'church-admin' ) ).'</a></p>';
        echo'</div>';
        return;
    }
    if(!church_admin_level_check('Bulk SMS') )wp_die(__('You don\'t have permissions to do that','church-admin') );
    global $wpdb;
	
	
		$member_type=church_admin_member_types_array();

    	//check to see if directory is populated!
    	$check=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people');
    	if ( empty( $check)||$check<1)
    	{
            echo'<div class="notice notice-success inline">';
            echo'<p><strong>'.esc_html( __('You need some people in the directory before you can use this Bulk SMS service','church-admin' ) ).'</strong></p>';  // Translating added by JF 8.02.18
            echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">'.esc_html( __('Add a Household','church-admin' ) ).'</a></p>';
            echo'</div>';
        }
    	else
    	{//people stored in directory
            if(isset( $_POST['counttxt'] ) )
            {
               church_admin_debug('About to send'.print_r( $_POST,TRUE) );
                $mobiles=church_admin_get_mobiles();
                
                church_admin_sms( $mobiles,church_admin_sanitize( $_POST['counttxt'] ),TRUE);
            }
            else
            {
                church_admin_sms_credits();
                church_admin_send_sms_form();
            }
	   }
	
}
/**************************************************
*
* This functions returns SMS credit left
*
**************************************************/
function church_admin_sms_credits()
{
    $service=get_option('church_admin_sms_provider');
    switch( $service)
    {
        case'textmagic.com':
            $url='https://rest.textmagic.com/api/v2/user';
            $sms_username=get_option('church_admin_sms_username');
            $api_key=get_option('church_admin_sms_api_key');
            $ch = curl_init( $url);
            $url='https://rest.textmagic.com/api/v2/messages';
            $sms_username=get_option('church_admin_sms_username');
            $api_key=get_option('church_admin_sms_api_key');
            $auth=array('Content-Type: application/json','X-TM-Username:'.$sms_username,'X-TM-Key:'.$api_key);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1); 
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $auth); 
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec( $ch);
            $response=json_decode( $result,TRUE);
            echo'<h3>'.esc_html( __('SMS','church-admin' ) ).'</h2><h2>'.esc_html( __('Provider: textmagic.com','church-admin' ) ).'</h3>';
            echo'<p>'.esc_html( __('Balance',"church-admin").': '.$response['currency']["htmlSymbol"].$response["balance"]).'</p>';
            echo'<p><a href="https://my.textmagic.com/payment">'.esc_html( __('Top up textmagic.com account')).'</a>';
        break;
        case'twilio':
            
            $sms_SID=get_option('church_admin_twilio_SID');
            $sms_Token=get_option('church_admin_twilio_token');
            $url='https://api.twilio.com/2010-04-01/Accounts/'.$sms_SID.'/Balance.json';
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_URL, $url); 
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt( $ch, CURLOPT_USERPWD, "$sms_SID:$sms_Token");
            $response = json_decode(curl_exec( $ch) );
            church_admin_debug($response);
            switch( $response->currency)
            {
                case 'GBP':$symbol='&pound;';break;
                case 'EUR':$symbol='€';break;
                default:$symbol='$';break;
            }
            echo '<p>'.esc_html( __('SMS provider',"church-admin")).': Twilio</p>';
            echo'<p><a href="https://support.twilio.com/hc/en-us/articles/360019772314-Twilio-org-Impact-Access-Pricing-Benefits"><strong>'.__("Twilio not for profit credit does not show all the gift credit in your balance at once, it just tops it up until it is all used.",'church-admin').'</strong></a></p>';
   
            echo '<p>'.esc_html( __('Balance','church-admin' ) ).': '.$symbol.$response->balance.'</p>';
            curl_close( $ch);
        break;
    }
}

/**************************************************
*
* This functions prepares to send the message
*
**************************************************/
function church_admin_sms( $mobile,$message,$echo=TRUE)
{
    if( empty( $mobile ) )return FALSE;
    if( empty( $message ) )return FALSE;
    
    church_admin_debug('******** STARTING: church_admin_sms ********');
    church_admin_debug('Args:');
    church_admin_debug(func_get_args() );

    global $wpdb;
    $messages=array();
	if(!is_array( $mobile) )$mobile=array( $mobile);
	$sender=get_option('church_admin_sms_reply');
	$service=get_option('church_admin_sms_provider');
    church_admin_debug('Sending via '.$service);
    switch( $service)
    {
        case 'twilio':
            
            $SID=get_option('church_admin_twilio_SID');
            $token=get_option('church_admin_twilio_token');
            $url = "https://api.twilio.com/2010-04-01/Accounts/$SID/Messages.json";
            foreach( $mobile AS $key=>$mob)
            {
                church_admin_debug('Log into Twilio table, get people_id first');
                $e164cell=$mob;
                //make sure leading + for DB
                if(substr( $mob,0,1)!='+')$e164cell='+'.$e164cell;
                $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE e164cell="'.esc_sql( $e164cell).'"');
                church_admin_debug( $wpdb->last_query);
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_twilio_messages (mobile,direction,message, twilio_id,message_date,people_id)VALUES("'.esc_sql( $e164cell).'","1","'.esc_sql( $message).'","Unknown","'.esc_sql(wp_date('Y-m-d H:i:s')).'","'.(int)$people_id.'")');
                church_admin_debug( $wpdb->last_query);
                
                if( $echo)echo'<p>'.esc_html( __('Sending to ','church-admin' ) ).esc_html( $mob).'</p>';
                $data = array (
                'From' => $sender,
                'To' => "'" . $mob . "')",
                'Body' => $message,
                );
                $post = http_build_query( $data);
                $x = curl_init( $url );
                curl_setopt( $x, CURLOPT_POST, true);
                curl_setopt( $x, CURLOPT_RETURNTRANSFER, true);
                curl_setopt( $x, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt( $x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt( $x, CURLOPT_USERPWD, "$SID:$token");
                curl_setopt( $x, CURLOPT_POSTFIELDS, $post);
                $y = curl_exec( $x);
               
                $response=json_decode( $y);
                
                if(!empty( $response->message) )
                { 
                   if(!empty( $echo) ) echo '<p>'.esc_html( __('Error','church-admin' ) ).' - '.esc_html( $response->message).'</p>';
                   church_admin_debug('Twilio response : '. $response->message);
                }
                else
                {
                    $status = esc_html(sprintf(__('%1$s with message ID %2$s has status %3$s','church-admin' ) ,  $response->to,$response->sid,$response->status ) );
                    church_admin_debug( $status);
                    if(!empty( $echo) ) echo '<p>'.$status.'</p>';
                }
                
               
                
                curl_close( $x);
                
            }
            
        break;
        case 'bulksms.com':
            $username=get_option('church_admin_sms_username');
            $password=get_option('church_admin_sms_password');
            foreach( $mobile AS $key=>$mob)
            {
                $messages[]=array('to'=>$mob,'body'=>$message);
                echo'<p>'.esc_html( __('Sending to ','church-admin' ) ).esc_html( $mob).'</p>';
            }
            //Use BulkSMS.com JSON api
            $result = church_admin_send_message( json_encode( $messages), 'https://api.bulksms.com/v1/messages?auto-unicode=true', $username, $password );
            if(defined('CA_DEBUG') )church_admin_debug(print_r( $result,TRUE) );
            if ( $result['http_status'] != 201) {

                print "Response " . print_r( $result);
            } else {
                //print "Response " . print_r( $result);
                // Use json_decode( $result['server_response'] ) to work with the response further
                if(json_decode( $result['server_response'] )==1)echo'<p>'.esc_html( __('Success','church-admin' ) ).'</p>';
            }
        
        break;
        case 'cloudservicezm':
            $username=get_option('church_admin_sms_username');
            $password=get_option('church_admin_sms_password');
            $url = 'http://www.cloudservicezm.com/smsservice/jsonapi'; 
            //$sender=substr(get_option('church_admin_sms_reply'),2);
            //Initiate cURL.

            $ch = curl_init( $url); 
            //The JSON data.
            $jsonData=array();
            $jsonData['auth']=array(
                        "username"=>$username,
                        "password"=>$password,
                        "sender_id"=>$sender
                    );

            $sent=esc_html(__('Sent to...','church-admin' ) ).'<ul>';
            foreach( $mobile AS $key=>$mob)
            {
                $jsonData['messages'][]=array('phone'=>$mob,'message'=>$message);
                $sent.='<li>'.$mobile.'</li>';
            }
            $sent.='</ul>';
            $jsonDataEncoded = json_encode( $jsonData);
            curl_setopt( $ch, CURLOPT_POST,1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1); 
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json') ); 
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, 0);
            $result = curl_exec( $ch);
            $response=json_decode( $result);
            echo'<div class="updated fade"><p>'.esc_html( __('Message sent - result...','church-admin' ) ).'<br>'.$response["response_description"].'</p>'.$sent.'</div>';
        break;
        case 'textmagic.com':
            
            $url='https://rest.textmagic.com/api/v2/messages';
            $sms_username=get_option('church_admin_sms_username');
            $api_key=get_option('church_admin_sms_api_key');
            $args=array('text'=>$message,'phones'=>implode(",",$mobile),'from'=>$sender);
            $jsonDataEncoded = json_encode( $args);
            $auth=array('Content-Type: application/json','X-TM-Username:'.$sms_username,'X-TM-Key:'.$api_key);
            
            $ch = curl_init( $url); 
            curl_setopt( $ch, CURLOPT_POST,1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1); 
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $auth); 
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec( $ch);
            $response=json_decode( $result,TRUE);
            if(!empty( $response['id'] ) )
            {
                echo'<p>'.esc_html(sprintf(__('SMS message sent successfully to %1$s recipients','church-admin' ) ,count( $mobile) ) ).'</p>';
            }
            elseif(!empty( $response['message'] ) )
            {
                
                echo'<h3>'.esc_html( $response['message'] ).'</h3>';
                if(!empty( $response['errors']['common'] ) )
                {
                    foreach( $response['errors']['common'] AS $key=>$error)
                    {
                        echo'<p>'.esc_html( $error).'</p>';       
                    }
                    echo '<h3>'.esc_html( __('You tried...','church-admin' ) ).'</h3>';
                    foreach( $args AS $what=>$value)
                    {
                        echo'<p><strong>'.esc_html(ucwords( $what) ).': </strong>'.esc_html( $value).'</p>';
                        
                    }
                }
            }
            else
            {
                echo'<pre>';
                print_r( $response);
                echo'</pre>';
            }
        break;
    }

	
	church_admin_debug('******** END: church_admin_sms ********');
		
}

/*****************************************************************************
*
* The actual message send function with bulksms.com json api
*
******************************************************************************/
function church_admin_send_message ( $post_body, $url, $username, $password) {
  $ch = curl_init( );
  $headers = array(
  'Content-Type:application/json',
  'Authorization:Basic '. base64_encode("$username:$password")
  );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt ( $ch, CURLOPT_URL, $url );
  curl_setopt ( $ch, CURLOPT_POST, 1 );
  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
  // Allow cUrl functions 20 seconds to execute
  curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
  // Wait 10 seconds while trying to connect
  curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
  $output = array();
  $output['server_response'] = curl_exec( $ch );
  $curl_info = curl_getinfo( $ch );
  $output['http_status'] = $curl_info[ 'http_code' ];
  $output['error'] = curl_error( $ch);
  curl_close( $ch );
  return $output;
} 



/*****************************************************************************
*
* SMS Send form
*
******************************************************************************/
function church_admin_send_sms_form()
{
   if(!church_admin_level_check('Bulk SMS') )wp_die(__('You don\'t have permissions to do that','church-admin') );

    echo'<p><a href="https://console.twilio.com/us1/billing/nonprofit-benefits/sign-up">'.__('Twilio now offer a one off not for profit credit of $100, or £80 (other currencies available). Click the link to apply!','church-admin').'</a></p>';
     global $wpdb;
	$member_type=church_admin_member_types_array();
	echo'
<script type="text/javascript">
jQuery(document).ready(function($){
    
        var $remaining = $("#remaining"),
            $messages = $remaining.next();
    
        $("#message").keyup(function(){
            var chars = this.value.length,
                messages = Math.ceil(chars / 160),
                remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
    
            $remaining.text(remaining);
            $messages.text(messages);
        });
  
});
</script>
<h2>'.esc_html( __('Send a text message','church-admin' ) ).'</h2>
<form action="" method="post" name="SMS" id="SMS">
<p><span id="remaining">160</span> characters remaining, <span id="messages">1</span> messages to each person.</p>
<div class="church-admin-form-group"><label>'.esc_html( __('Message','church-admin' ) ).'</label><textarea class="church-admin-form-control" id="message" name="counttxt"  ></textarea></div>'; 


	echo'<h2>'.esc_html( __('Choose recipients...','church-admin' ) ).'</h2>';
	$smsoremail='mobile';
	$member_type=church_admin_member_types_array();
	echo'<p><label>'.esc_html( __('Type in recipient names, separated by a comma (filters will be ignored)','church-admin' ) ).'</label>'.church_admin_autocomplete('recipients','friends','to','').'</p>';
	echo'<p>'.esc_html( __('Or use the filters below. ','church-admin' ) ).'</p>'; 
	require_once(plugin_dir_path(__FILE__).'/filter.php');
    church_admin_directory_filter(FALSE,TRUE);
    //echo'<span id="filtered-response"><h3>'.esc_html( __('Recipients','church-admin' ) ).'</h3><p>'.esc_html( __('Everyone will get this, unless you add some filters','church-admin' ) ).'</p></span>';
    $nonce = wp_create_nonce("church_admin_filter","filter");
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


	echo'<p><br style="clear:left" /><input class="button-primary" type="submit" name="submitted" value="'.esc_html( __('Send Message','church-admin' ) ).'" /></p></form>';  // Translation JF 8.02.18
}


function church_admin_get_mobiles()
{
	global $wpdb;
    
	require_once(plugin_dir_path(__FILE__).'/filter.php');
	if(!empty( $_POST['recipients'] ) )
	{
		$names=array();
		$ids=maybe_unserialize(church_admin_get_people_id(church_admin_sanitize( $_POST['recipients'] ) ));
		foreach( $ids AS $value)  {$names[]='people_id = "'.esc_sql( $value).'"';}
		$sql='SELECT  e164cell FROM '.$wpdb->prefix.'church_admin_people WHERE mobile!="" AND sms_send=1 AND '.implode(' OR ',$names).'  GROUP BY e164cell';
	}
	else
	{
        
		$sql=church_admin_build_filter_sql( church_admin_sanitize($_POST['check']),'sms');
	}

		$results=$wpdb->get_results( $sql);

	    $mobiles=array();
        $provider=get_option('church_admin_sms_provider');
        foreach ( $results AS $row)
        {
            if(!empty( $row->e164cell) )
            {
                
                $sendmobile=$row->e164cell;
                if( $provider!='twilio')$sendmobile=ltrim( $sendmobile,'+');
                if(!empty( $sendmobile) )$mobiles[]=$sendmobile;
            }
		}
		$mobiles=array_unique( $mobiles);
	
	return $mobiles;
}

