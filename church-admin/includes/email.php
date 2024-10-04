<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//define('EMAIL_TEST',TRUE);


//2014-02-24 fixed encoding error


function church_admin_test_email( $email=NULL)
{
    echo'<h1>'.esc_html( __('Send a test email','church-admin' ) ).'</h1>';
    if ( empty( $email) )
    {
        echo'<p>'.esc_html( __("Please note - sometimes hosting companies set website email sending up so you can't send to an email address on the same domain as the site domain name. Contact your host after checking a different email address as well one on this domain.",'church-admin' ) ).'</p>';
        echo '<form action="" method="POST">';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Email address to send to','church-admin' ) ).'</label><input class="church-admin-form-control" required="required" type="email" name="test-email" /></div>';
		echo'<p><input type="submit" class="button-primary" value="'.esc_attr(__('Send','church-admin')).'"></p></form>';
		
	}
    else
    {
        church_admin_debug("***********************\r\n Email test send");
		
		$testemail = !empty($_REQUEST['test-email'])?sanitize_text_field(stripslashes($_REQUEST['test-email'])):null;
		
		if(!empty( $testemail  ) && is_email( $testemail  ) )$to=$testemail ;

		$attachment = null;
		
		$headers = null;


		//wp_mail
		echo'<h2>'.__('Sending using WordPress email function','church-admin').'</h2>';
		$subject = __('Test email using WordPress email function','church-admin');
		$message='<p>Test email from the church admin plugin using WordPress email function</p>';
		$from_email=get_option('church_admin_default_from_email');
		$from_name=get_option('church_admin_default_from_name');
		add_filter( 'wp_mail_from', function( $email)use ($from_email){ return trim($from_email);} );
        add_filter( 'wp_mail_from_name', function( $name)use ($from_name){return trim($from_name);} );
        add_filter( 'wp_mail_content_type', 'set_html_content_type' );
        
        if(wp_mail( $to, $subject,$message,$headers) )
        {
            echo '<p>'.  esc_html(sprintf(__('Email sent to %1$s  successfully','church-admin'),$to)).'</p>';
        }
        else
        {//log errors
            global $phpmailer;
            if (isset( $phpmailer) ) {
                church_admin_debug("**********\r\n Send error\r\n ".print_r( $phpmailer->ErrorInfo,TRUE)."\r\n");
                //church_admin_debug($phpmailer);
                echo '<p>'. sprintf(__('Failed to send to %1$s','church-admin'),$to).' '.$phpmailer->ErrorInfo.'</p>';
            }
        }
        remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        remove_filter( 'wp_mail_from_name',function( $name ) {return $from_name;} );
        remove_filter( 'wp_mail_from', function( $email) {return $from_email;} );
		echo'<hr>';


		$mailersend_api = get_option('church_admin_mailersend_api_key');
		//church admin email function
		echo'<h2>'.__('Sending using church admin specific bulk email function','church-admin').'</h2>';
		if(!empty($mailersend_api)){echo'<p>'.__('Will be using Mailersend API key','church-admin');}
		$subject = __('Test email using Church Admin single email function','church-admin');
		$message='<p>Test email from the church admin plugin using Church Admin single email function</p>';
		$response = church_admin_email_send($to,$subject,$message,$from_name,$from_email,$attachment,$from_name,$from_email,TRUE);
		
		echo'<h4>'.esc_html(__('Test email result','church-admin')).'</h4>';
		echo wp_kses_post($response);
		echo'<hr>';

		
		
    	if(!empty($mailersend_api)){
			//mailersend bulk test
			echo'<h2>'.__('Sending using church admin specific single email function','church-admin').'</h2>';
			$recipients = array(array('email'=>$to,'first_name'=>__('Forename','church-admin'),'name'=>__('Name','church-admin')));
			$subject = __('Test email using Church Admin bulk email function','church-admin');
			$message='<p>Test email from the church admin plugin using Church Admin bulk email function</p>';
			church_admin_mailersend_bulk($recipients,$subject,$message,$from_email,$from_name,$from_email,$from_name,$attachment,TRUE);
			echo'<hr>';
		}
    }
}


function church_admin_delete_email( $email_id)
{
	global $wpdb;
	if(empty($email_id) || !church_admin_int_check($email_id)){
		echo '<div class="notice notice-danger"><h2>'.esc_html( __('Email not found','church-admin' ) ).'</h2></div>';
		return;
	}
	$row=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_email_build WHERE email_id="'.(int)$email_id.'"');
	if(!empty( $data->filename) )$paths=maybe_unserialize( $data->filename);
	if(!empty( $paths) )  {foreach( $paths AS $key=>$value) unlink( $value);}
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_email_build WHERE email_id="'.(int)$email_id.'"');
	echo'<div class="notice notice-success inline">'.esc_html( __('Email deleted','church-admin' ) ).'</div>';
	church_admin_email_list();
}
/**
 * Church Email list
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */
function church_admin_email_list()
{
	echo'<h2>'.esc_html( __('Email List','church-admin' ) ).'</h2>';
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=send-email','send-email').'">'.esc_html( __('Send email','church-admin' ) ).'</a></p>';
	global $wpdb;
	$items=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_email_build WHERE recipients!=""' );
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.class.php');
    if( $items > 0)
    {

	$p = new caPagination;
	$p->items( $items);
	$p->limit(get_option('church_admin_page_limit') ); // Limit entries per page
	$p->target(wp_nonce_url('admin.php?page=church_admin/index.php&section=communications&action=email-list','email-list'));
	$current_page = !empty($_GET['page']) ? (int)$_GET['page']:1;
              
	  $p->currentPage( $current_page); // Gets and validates the current page
	$p->calculate(); // Calculates what to show
	$p->parameterName('paging');
	$p->adjacents(1); //No. of page away from the current page
	if(!isset( $_GET['paging'] ) )
	{
	    $p->page = 1;
	}
	else
	{
	    $p->page = intval( $_GET['paging'] );
	}
        //Query for limit paging
	$limit = esc_sql("LIMIT " . ( $p->page - 1) * $p->limit  . ", " . $p->limit);
    $result=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_email_build WHERE recipients!="" ORDER BY send_date DESC '.$limit );
	if(!empty( $result) )
	{
		echo'<h2>'.esc_html( __('Sent Emails','church-admin' ) ).'</h2>';
		// Pagination
		echo'<div class="tablenav"><div class="tablenav-pages">';
        echo $p->getOutput();
        echo '</div></div>';
     	 //Pagination
		$theader='<tr><th class="column-primary">'.esc_html( __('Subject','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Date','church-admin' ) ).'</th><th>'.esc_html( __('Number of recipients','church-admin' ) ).'</th><th>'.esc_html( __('Excerpt','church-admin' ) ).'</th><th>'.esc_html( __('Resend','church-admin' ) ).'?</th><th>'.esc_html( __('Resend to new recipients','church-admin' ) ).'</th><th>'.esc_html( __('Edit and resend','church-admin' ) ).'</th></tr>';
		echo' <table class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tfoot>'.$theader.'</tfoot><tbody>';
		foreach( $result AS $row)
		{
			/*
			$startsAt = strpos( $row->message, "<!--salutation-->") + strlen("{FINDME}");
			$endsAt = strpos( $row->message, "<!--News,events-->", $startsAt);
			$message = strip_tags(substr( $row->message, $startsAt+17, $endsAt - $startsAt) );
			$message=substr( $message,0,500);
			*/
			$delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=communications&action=delete-email&email_id='.intval( $row->email_id),'delete-email').'">Delete</a>';
			$resend='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=communications&action=resend-email&email_id='.intval( $row->email_id),'resend-email').'">Resend to previous recipients</a>';
			$new='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=communications&action=resend-new&email_id='.intval( $row->email_id),'resend-new').'">'.esc_html( __('Resend to new recipients','church-admin' ) ).'</a>';
			$reedit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=communications&action=edit-resend&email_id='.intval( $row->email_id),'resend-new').'">'.esc_html( __('Edit and send','church-admin' ) ).'</a>';
			$recipients = maybe_unserialize($row->recipients);
			$recipients_count = count($recipients);
			$recipients_output='';
			foreach($recipients AS $key=>$recipient){
				if(is_object($recipient)){
					$recipients_output .=$recipient->email.',';
				}
				elseif(is_array($recipient)){
					$recipients_output .=implode(", ",$recipient).',';
				}
				else{
					$recipients_output .=$recipient.',';
				}
			}
			
			echo'<tr>
				<td data-colname="'.esc_html( __('Subject','church-admin' ) ).'" class="column-primary">'.$row->subject.'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>	
				<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
				<td data-colname="'.esc_html( __('Send date','church-admin' ) ).'">'.mysql2date(get_option('date_format').' '.get_option('time_format'),$row->send_date).'</td>
				<td data-colname="'.esc_html( __('Recipients','church-admin' ) ).'" style="width:200px;">'.esc_html($recipients_output).'</td>
				
				<td data-colname="'.esc_html( __('Message','church-admin' ) ).'">'.church_admin_excerpt( strip_tags($row->message),250,'...').'</td>
				<td  data-colname="'.esc_html( __('Resend','church-admin' ) ).'">'.$resend.'</td>
				<td  data-colname="'.esc_html( __('Resend to new recipients','church-admin' ) ).'">'.$new.'</td>
				<td  data-colname="'.esc_html( __('Edit and send again','church-admin' ) ).'">'.$reedit.'</td>
			</tr>';
		}
		echo'</tbody></table>';
	}
	}
	else{
		echo'<p>'.esc_html( __('No emails have been sent yet','church-admin' ) ).'</p>';
	}
}
/**
 * Send email function
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */
function church_admin_send_email( $email_id=NULL)
{
	global $wpdb;
	echo'<div class="wrap"><h2>'.esc_html( __('Bulk Email','church-admin' ) ).'</h2>';
	$member_type=church_admin_member_types_array();
	$gender=get_option('church_admin_gender');
    
	if(!empty( $email_id) )$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_email_build WHERE email_id="'.esc_sql( $email_id).'"');
	if(!empty( $_POST['send-email'] ) )
	{
		echo'<pre>';
		//print_r($_POST);
		echo'</pre>';
		echo'<p>'.esc_html('Processing','church-admin').'</p>';
		
		$subject = !empty($_POST['subject'] ) ? church_admin_sanitize($_POST['subject'] ) : null;
		$from_email = $reply_email = !empty($_POST['from_email']) ? church_admin_sanitize( $_POST['from_email']) : get_option('church_admin_default_from_name');
		$from_name =  $reply_name = !empty($_POST['from_name']) ? church_admin_sanitize( $_POST['from_name']) : get_option('church_admin_default_from_email');
		$message= !empty($_POST['message']) ? wpautop(wp_kses_post(stripslashes($_POST['message'] )))  : null;
		
		$recipients = !empty($_POST['recipients']) ? church_admin_sanitize($_POST['recipients']) : null;
		$filters = !empty($_POST['check']) ? church_admin_sanitize($_POST['check']) : null;

		if(empty($recipients) && empty($filters)){
			echo'<div class="notice notice-warning"><h2>'.esc_html('Missing recipients','church-admin').'</h2><p>'.esc_html(__('Email not sent','church-admin')).'</p></div>';
			return;
		}
		if(empty($subject)){
			echo'<div class="notice notice-warning"><h2>'.esc_html('Missing subject','church-admin').'</h2><p>'.esc_html(__('Email not sent','church-admin')).'</p></div>';
			return;
		}
		if(empty($message)){
			echo'<div class="notice notice-warning"><h2>'.esc_html('Missing message','church-admin').'</h2><p>'.esc_html(__('Email not sent','church-admin')).'</p></div>';
			return;
		}
		
		
		/*
		//handle template & message
		if(function_exists("mb_convert_encoding") )  {
            $message = mb_convert_encoding($message , 'HTML-ENTITIES', 'UTF-8' );
        }
		*/
		//sort image floating
		$message=str_replace('class="alignleft','style="float:left;margin:5px;" class="alignleft',$message);
		$message=str_replace('class="alignright','style="float:right;margin:5px;" class="alignright',$message);
		$message=str_replace('class="aligncenter','style="  display: block;  margin-left: auto;  margin-right: auto;" class="aligncenter',$message);
		$message=str_replace('<ol>','<ol style="margin-left:5px;">',$message);
		$message=str_replace('<ul>','<ul style="margin-left:5px;">',$message);
		$message=str_replace('[subject]',sanitize_text_field( stripslashes($_POST['subject'])),$message);//add subject
		if(get_option('church_admin_feedburner') )
		{
			$RSS='&nbsp;<a href="http://feedburner.google.com/fb/a/mailverify?uri='.get_option('church_admin_feedburner').'&amp;loc=en_US">Subscribe to '.get_option('blogname').' blog by Email</a>';
		}else{$RSS='';}
		$message=str_replace('[RSS]',$RSS,$message);
		//twitter url
		if(get_option('church_admin_twitter') )  {$twitter='<a href="http://twitter.com/#!/'.get_option('church_admin_twitter').'" style="text_decoration:none" title="Follow us on Twitter">Twitter</a>&nbsp; ';}else{$twitter='';}
		$message=str_replace('[TWITTER]',$twitter,$message);
		//facebook url
		if(get_option('church_admin_facebook') )  {$facebook='<a href="'.get_option('church_admin_facebook').'" style="text_decoration:none" title="Follow us on Facebook">Facebook</a> &nbsp;';}else{$facebook='';}
		$message=str_replace('[FACEBOOK]',$facebook,$message);
		$message=str_replace('[BLOGINFO]','<a href="'.get_bloginfo('url').'">'.get_bloginfo('url').'</a>',$message);
		$message=str_replace('[HEADER_IMAGE]','<img class="header_image" src="'.get_option('church_admin_email_image').'" alt="" >',$message);
		//copyright year
		$message=str_replace('[year]',date('Y'),$message);

	
		$sqlsafe['message']=esc_sql( $message);//make message sqlsafe!
		//save build message
		$email_id=$wpdb->get_var('SELECT email_id FROM '.$wpdb->prefix.'church_admin_email_build WHERE subject="'.esc_sql(sanitize_text_field( stripslashes($_POST['subject'] ) ) ).'" AND message="'.esc_sql( $message).'" AND from_email="'.esc_sql(sanitize_text_field( stripslashes($_POST['from_email'] ) ) ).'" AND from_name="'.esc_sql(sanitize_text_field( stripslashes($_POST['from_name'] ) ) ).'"');
		if( $email_id)
		{//update
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_email_build SET subject="'.esc_sql(sanitize_text_field(stripslashes( $_POST['subject'] ) ) ).'",message="'.esc_sql( $message).'", from_email="'.esc_sql(sanitize_text_field( stripslashes($_POST['from_email'] ) )).'" ,from_name="'.esc_sql(sanitize_text_field(stripslashes( $_POST['from_name'] )) ).'", send_date = "'.esc_sql(wp_date('Y-m-d H:i:s')).'" WHERE email_id="'.esc_sql( $email_id).'"');
		}//end update
		else
		{//insert
			$sql='INSERT INTO '.$wpdb->prefix.'church_admin_email_build (subject,message,from_email,from_name,send_date,content) VALUES("'.esc_sql(sanitize_text_field( stripslashes($_POST['subject'] ) )).'","'.esc_sql( $message).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['from_email'] )) ).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['from_name'] ) )).'","'.esc_sql(wp_date('Y-m-d H:i:s')).'","'.esc_sql( $message).'")';
			$wpdb->query( $sql);
			$email_id=$wpdb->insert_id;
		}//insert
		//when to send!
		$schedule=null; //wp_date('Y-m-d');
		if(!empty( $_POST['send_date'] ) && !empty($_POST['send_time']))
		{
			$inputted_datetime = church_admin_sanitize($_POST['send_date'].' '.$_POST['send_time']);
			$check=church_admin_check_date($inputted_datetime,'Y-m-d h:i');
			if( $check)  {
				$schedule= $inputted_datetime;
			}
		}
		if(!empty( $schedule) )
		{
			//set up wp_cron
			if( !wp_next_scheduled('church_admin_bulk_email') )wp_schedule_event(time(), 'hourly', 'church_admin_bulk_email');
		}
		//find recipients

		if(!empty( $_POST['recipients'] ) )
		{
					$names=array();
					$ids=maybe_unserialize(church_admin_get_people_id($recipients ));
					foreach( $ids AS $value)  {$names[]='people_id = "'.esc_sql( $value).'"';}
					$sql='SELECT  email,first_name, people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email!="" AND email_send=1 AND '.implode(' OR ',$names).' AND email_send=1 GROUP BY email';
		}

		else
		{
			require_once(plugin_dir_path(__FILE__).'/filter.php');
			$sql=church_admin_build_filter_sql($filters,'email');
		}


	//church_admin_debug( $sql);
	//end build recipients sql

	

	
		$results=$wpdb->get_results( $sql);
		$recipients=array();
		if( $results)
		{
			echo '<p>'.esc_html(sprintf(__('Preparing sending to %1$s recipients','church-admin'),$wpdb->num_rows)).'</p>';
			foreach( $results AS $row)
			{
				if(!empty($row->email)){
					if(empty($row->first_name)){$row->first_name = '';}
					$recipients[]=array('email'=>$row->email,'name'=>church_admin_formatted_name($row),'first_name'=>$row->first_name,'people_id'=>$row->people_id);
				}
				
				
			}
			church_admin_debug($recipients);
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_email_build SET recipients="'.esc_sql(maybe_serialize( $recipients) ).'" WHERE email_id="'.esc_sql( $email_id).'"');
			if(empty($schedule)){
				$mailersend_api = get_option('church_admin_mailersend_api_key');
				if(!empty($mailersend_api)){
					echo'<p>'.esc_html(__('Sending by Mailersend','church-admin')).'</p>';
					echo church_admin_mailersend_bulk($recipients,$subject,$message,$from_email,$from_name,$reply_email,$reply_name,null,TRUE);
				}
				else{
					foreach($results AS $row){
						if(empty($row->first_name)){$row->first_name = '';}
						$send_message = str_replace('[NAME]',$row->first_name,$message);
						echo church_admin_email_send($row->email,$subject,$send_message,$from_name,$from_email,null,$reply_name,$reply_name,FALSE);
					}
				}
			}else{
				$values=array();
				foreach($recipients AS $ke=>$details){
					$send_message = str_replace('[NAME]',$details['first_name'],$message);
					$values[] = '("'.esc_sql($schedule).'","'.esc_sql($details['email']).'","'.esc_sql($from_email).'","'.esc_sql($from_name).'","'.esc_sql($reply_email).'","'.esc_sql($reply_name).'","'.esc_sql($subject).'","'.esc_sql($send_message).'")';
					echo'<p>'.esc_html(sprintf(__('Scheduled to %1$s','church-admin'),$details['email'])).'</p>';

				}
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_email (schedule,recipient,from_email,from_name,reply_to,reply_name,subject,message) VALUES '.implode(',',$values));
				echo'<p>'.esc_html(__('Scheduled','church-admin')).'</p>';
			}
			
		}else{echo '<div class="error fade">'.esc_html( __('No email addresses found','church-admin' ) ).'</p>';}
		//send or queue



	}
	else
	{


		
		
		echo'<form action="" method="post" >';
		church_admin_recipients();
		//subject
		
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Subject','church-admin' ) ).'</label><input type="text" class="church-admin-form-control" name="subject" ';
		if(!empty( $data->subject) ) echo ' value="'.esc_html( $data->subject).'"';
		echo'/></div>';

		echo '<p><span id="me" style="text-decoration:underline">'.esc_html( __('Use me as from name and email values','church-admin' ) ).'</span></p>';
		$current_user = wp_get_current_user();
		$user=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,middle_name,prefix,last_name) AS name, email FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
		if(!empty( $user->email) )
        {
            echo'<script type="text/javascript">
                jQuery(document).ready(function( $)  {
                    $("#me").click(function() {
                        $("#from_name").val("'.esc_html( $user->name).'");
                        $("#from_email").val("'.esc_html( $user->email).'");
                    });
                });
                </script>';
        }
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('From name','church-admin' ) ).'</label><input type="text" class="church-admin-form-control"  id="from_name" name="from_name"  ';
		$from_name=get_option('church_admin_default_from_name');
		if(!empty( $from_name) ) echo ' value="'.esc_html( $from_name).'"';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('From email','church-admin' ) ).'</label><input type="text" class="church-admin-form-control"  id="from_email" name="from_email"  ';
		$from_email=get_option('church_admin_default_from_email');
		if(!empty( $from_email) ) echo ' value="'.esc_html( $from_email).'"';
		echo'/></div>';
		echo'<p><em>'.esc_html(__('Attachments have been removed for security and performance reasons. Upload to Media Library and paste in the link is best practice.','church-admin')).'</em></p>';
		$content = '';
		$editor_id = 'message';
		echo'<p>'.esc_html(__('The shortcode [NAME] will be replaced by the first name of the recipient','church-admin')).'</p>';
		echo'<div id="poststuff">';
		$content='';
		if(!empty( $data->content) )$content=$data->content;
		wp_editor( $content,'message');
		echo'</div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Send now','church-admin' ) ).' </label><input type=checkbox id="now" name=schedule value="now" checked="checked" /></div>';
		echo'<p>'.esc_html('Please note schedule below uses "wp-cron" which sends the emails behind the scenes when the website is viewed after the schedule time.','church-admin').'</p>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Or schedule?','church-admin' ) ).'</label>';
		echo church_admin_date_picker(null,'send_date',FALSE,wp_date('Y-m-d'),NULL,'send-date','send_date',FALSE,'send-date',NULL,NULL);
		//( $db_date=null,$name=null,$array=FALSE,$start=NULL,$end=NULL,$class=NULL,$id=NULL,$disabled=FALSE,$datawhat=NULL,$dataid=NULL,$dataCustomID=NULL)
		echo '</div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Send time','church-admin' ) ).' </label><input type=time id="send_time" name="send_time" value="09:00"  /></div>';
		
		echo'<script>jQuery(document).ready(function( $)  {  
		 $("#send_datex").change(function()  {$("#now").prop( "checked", false );});
			});</script>';
        echo'<input type="hidden" name="send-email"  value="TRUE" />';
		echo'<div class="church-admin-form-group"><input class="button-primary" type="submit" value="'.esc_html( __('Send','church-admin' ) ).'" disabled="disabled" /></div>';

		echo'</form>';
	}
echo'</div>';
}

 /**
 *
 * Recipients form element
 *
 * @author  Andy Moyle
 * @param
 * @return
 * @version  0.945
 *
 *
 *
 */
function church_admin_recipients( $type='email')
{
	global $wpdb;
	if(!empty( $type) && $type=='sms')  {$smsoremail='mobile';}else{$smsoremail='email';}
    $which='email';
    if(!empty( $type) )
    {
        switch( $type)
        {
            case'email': $which='email';break;
            case'sms':$which='sms';break;
            case'push':$which='push';break;
        }
    }
	$member_type=church_admin_member_types_array();
	echo'<p><label>'.esc_html( __('Type in recipient names, separated by a comma (filters will be ignored)','church-admin' ) ).'</label>'.church_admin_autocomplete('recipients','friends','to','').'</p>';
	echo'<p>'.esc_html( __('Or use the filters below','church-admin' ) ).'</p>';
	require_once(plugin_dir_path(__FILE__).'/filter.php');
    church_admin_directory_filter(false,true);

	$response='<h3>'.esc_html( __('Recipients','church-admin' ) ).'</h3><p>'.esc_html( __('Everyone will get this, unless you add some filters','church-admin' ) ).'</p>';
	$filter_email_nonce = wp_create_nonce('filter');
    echo'<script>
		jQuery(document).ready(function( $) {
        $("#filtered-response").html("'.$response.'");
	//handle send button disabled while no selections
     $(\'input[type="submit"]\').prop(\'disabled\', true);
     $(\'input[type="text"]\').keyup(function() {
        if( $(this).val() != "") {
           $(\':input[type="submit"]\').prop(\'disabled\', false);
          
        }
     });



			$(".all").on("change", function()  {
				var id = this.id;

				$("input."+id).prop("checked", !$("."+id).prop("checked") )
			});
		   
		   
		   function doFilter()  {

      			var category_list = [];
				var exclude= $("#exclude").val();
      			$("#filters1 :input:checked").each(function()  {


        			var category = $(this).val();
        			category_list.push(category);

        		});


      			var data = {
				"action": "church_admin",
				"method":"filter_email",
				"type":"'.$which.'",
				"exclude":exclude,
				"data": category_list,
				"nonce": "'.$filter_email_nonce .'"
				};
				console.log(data);
				$("#filtered-response").html(\'<p style="text-align:center"><img src="'.admin_url().'/images/wpspin_light-2x.gif" /></p>\');
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
				console.log(response);
					$("#filtered-response").html("<h3>"+response+"</h3>");
					$(\':input[type="submit"]\').prop(\'disabled\', false);
				});
			};
			$(".category").on("change",doFilter);
			$("#exclude").on("change",doFilter);

		});
	</script>
	';
}

function getTweetUrl( $url, $text)
{

	$maxTitleLength = 120 ;

	if (strlen( $text) > $maxTitleLength) {

		$text = substr( $text, 0, ( $maxTitleLength-3) ).'...';

	}

	$text=str_replace('"','',$text);

	$outputurl='https://twitter.com/share?wrap_links=true&amp;url='.urlencode( $url).'&amp;text='.urlencode( $text);

	$output='<a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$outputurl.'" data-text="'.$text.'" data-count="horizontal">Tweet</a>';

	return $output;

}

function church_admin_resend( $email_id)
{
	global $wpdb;
	$email=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_email_build WHERE email_id="'.esc_sql( $email_id).'"');

	if(!empty( $email) )
	{
		$addresses=maybe_unserialize( $email->recipients);
		foreach( $addresses AS $key=>$emailadd)
			{
				church_admin_email_send($emailadd,$email->subject,$email->message,$email->from_name,$email->from_email,$email->filename,$email->from_name,$email->from_email,FALSE);
				
			}


	}
}

function church_admin_resend_new( $email_id)
{
 	global $wpdb;
	echo'<h2>'.esc_html( __('Resending email to new recipients','church-admin' ) ).'</h2>';
	//get the original email
	$email=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_email_build WHERE email_id="'.esc_sql( $email_id).'"');
	//process sending
	if(!empty( $_POST['resend_new'] ) )
	{

		//find recipients
		switch( $_POST['type'] )
		{
			case 'gender':
				$sql='SELECT email, first_name FROM '.$wpdb->prefix.'church_admin_people WHERE email!="" AND email_send=1  AND sex="'.esc_sql( sanitize_text_field(stripslashes($_POST['sex'] ))).'"';
			break;
			case 'site':
				$sql='SELECT email, first_name FROM '.$wpdb->prefix.'church_admin_people WHERE email!="" AND email_send=1 AND site_id="'.(int)sanitize_text_field(stripslashes( $_POST['site_id']) ).'"';
			break;
			case 'autocomplete':
				$names=array();
				$ids=maybe_unserialize(church_admin_get_people_id(sanitize_text_field( stripslashes($_POST['recipients'] ) )));

				foreach( $ids AS $value)  {$names[]='people_id = "'.esc_sql( $value).'"';}
				$sql='SELECT  email,first_name FROM '.$wpdb->prefix.'church_admin_people WHERE email!="" AND email_send=1 AND '.implode(' OR ',$names);

			break;
			case 'smallgroup':
				$sql='SELECT DISTINCT a.email,a.first_name FROM '.$wpdb->prefix.'church_admin_people a,'.$wpdb->prefix.'church_admin_people_meta b WHERE a.email!="" AND email_send=1 AND b.meta_type="smallgroup"  AND b.ID="'.esc_sql((int) sanitize_text_field( stripslashes($_POST['group_id'] ))).'" AND a.people_id=b.people_id';
			break;
			case 'member_types':
				 $w=array();
				$where='(';
				foreach( $_POST['member_type'] AS $key=>$value)if(array_key_exists( $value,$member_type) )$w[]=' member_type_id='.$value.' ';
				$where.=implode("||",$w).')';
				$sql='SELECT email, first_name FROM '.$wpdb->prefix.'church_admin_people WHERE email!="" AND email_send=1 AND "'.$where;
			break;
			case 'individuals':
				$names=array();
				foreach ( $_POST['person'] AS $value)  {$names[]='people_id = "'.esc_sql( $value).'"';}
				$sql='SELECT  email,first_name FROM '.$wpdb->prefix.'church_admin_people WHERE email!="" AND email_send=1 AND '.implode(' OR ',$names);
			break;
			case 'ministries':
				foreach( $_POST['role_id'] AS $key=>$value)$r[]='b.ID='.$value;
				$sql='SELECT  a.email,a.first_name FROM '.$wpdb->prefix.'church_admin_people a,'.$wpdb->prefix.'church_admin_people_meta b WHERE b.meta_type="ministry" AND b.people_id=a.people_id AND a.email!="" AND email_send=1 AND ('.implode( " || ",$r).')' ;
			break;
		
		}

		$results=$wpdb->get_results( $sql);
		$emails=array();
		if( $results)
		{
			foreach( $results AS $row)
			{

				church_admin_email_send($row->email,$email->subject,$email->message,$email->from_name,$email->from_email,$email->filename,$email->from_name,$email->from_email,FALSE);
				
			}

		}else{echo '<div class="notice notice-danger">'.esc_html( __('No email addresses found','church-admin' ) ).'</p>';}
	}
	else
	{//form to choose and send
		echo'<h2>'.esc_html( __('Choose recipients to resend to','church-admin' ) ).'</h2>';
		echo'<form action="" method="POST">';
		echo church_admin_recipients();
		wp_nonce_field('resend-new');
		echo'<table class="form-table"><tr><th scope="row">&nbsp;</th><td><input type="hidden" name="resend-new" value="TRUE" /><input class="button-primary" type="submit" value="'.esc_html( __('Send','church-admin' ) ).'" /></td></tr>';
		echo'</tbody></table></form>';
	}
}



function church_admin_clear_email_queue(){

	global $wpdb;
	$count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_email');
	$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_email');
    echo'<div class="notice notice-success"><h2>'.esc_html(sprintf(__('%1$s emails cleared from queue','church-admin'),$count)).'</h2></div>';
}