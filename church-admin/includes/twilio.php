<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/************************************
*
*   Twilio listener
*
*************************************/
function church_admin_twilio_listener()
{
    global $wpdb;
    church_admin_debug("******* Twilio listener function fired ********");
    $smsProvider=get_option('church_admin_sms_provider');
    if ( empty( $smsProvider)||$smsProvider!='twilio')  {
        church_admin_debug("SmS provider set to ".$smsProvider); 
        exit('Jesus loves you');
    }
    $sqlsafe=array();
    

    foreach( $_REQUEST AS $key=>$value)$sqlsafe[$key]=esc_sql(church_admin_sanitize( $value) );
    //church_admin_debug(print_r( $sqlsafe,TRUE) );
    $check=$wpdb->get_var('SELECT message_id FROM '.$wpdb->prefix.'church_admin_twilio_messages WHERE twilio_id="'.$sqlsafe['MessageSid'].'"');
    church_admin_debug($wpdb->last_query);
    if ( empty( $check) )
    {
        church_admin_debug('message id: '.$check);
        $people_id=0;
        $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE e164cell="'.$sqlsafe['From'].'"');
        if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
        $optout=array('STOP','STOPALL','QUIT','END','UNSUBSCRIBE','CANCEL');
        if(!empty( $sqlsafe['body'] )&& in_array( $sqlsafe['body'],$optout) )
        {
            //change sms_send=0
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET sms_send=0 WHERE e164cell="'.$sqlsafe['From'].'"');
        }
        if(!empty( $sqlsafe['body'] )&&$sqlsafe['body']=='START')
        {
            //change sms_send=1
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET sms_send=1 WHERE e164cell="'.$sqlsafe['From'].'"');
        }
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_twilio_messages (mobile,direction,message, twilio_id,message_date,people_id)VALUES("'.$sqlsafe['From'].'","0","'.$sqlsafe['Body'].'","'.$sqlsafe['MessageSid'].'","'.wp_date('Y-m-d H:i:s').'","'.(int)$people_id.'")');
        church_admin_debug( $wpdb->last_query);

        //push to admin if they have set to receive them
        $admin_people_ids=get_option('church_admin_twilio_receive_push_to_admin');
        //church_admin_debug('Admin people_ids...');
        //church_admin_debug(print_r( $admin_people_ids,TRUE) );
        if(!empty( $admin_people_ids) )
        {
            $pushTokens=church_admin_get_push_tokens_from_ids( $admin_people_ids);
            //church_admin_debug('Tokens...');
            //church_admin_debug(print_r( $pushTokens,TRUE) );
            if(!empty( $pushTokens) )
            {
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/push.php');
               
                $message=esc_html(__('SMS reply','church-admin' ) .': '.church_admin_sanitize( $_REQUEST['Body'] ));
                $dataMessage=$message;
                
                church_admin_filtered_push( $message,$pushTokens,'SMS reply received',$dataMessage,'message',$sqlsafe['From'] );
                
            }


        }


    }

}
/******************************************
*
*   Twilio recent replies list
*
*******************************************/
function church_admin_twilio_replies_list()
{
    global $wpdb;
    echo'<h2>'.esc_html( __('Twilio replies list','church-admin' ) ).'</h2>';
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sms.php');
    church_admin_sms_credits();
    

    $smsProvider=get_option('church_admin_sms_provider');
    if ( empty( $smsProvider)||$smsProvider!='twilio')  {
        echo '<p><strong>'.esc_html( __('You need to set Twilio as your SMS provider to use this feature','church-admin' ) ).'</strong></p>';
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=sms-settings','sms-settings').'" class="button-primary">'.esc_html( __('SMS Settings','church-admin' ) ).'</a></p>';
    }
    else
    {   
        //set up push notifications for replies
        if(!empty( $_POST['twilio-admin-push-form'] ) )
        {
            if(!empty( $_POST['twilio-admin-push'] ) )
            {
                $twilio_people_id=maybe_unserialize(church_admin_get_people_id( church_admin_sanitize($_POST['twilio-admin-push']) ) );
                update_option('church_admin_twilio_receive_push_to_admin',$twilio_people_id);
            }
            else
            {
                delete_option('church_admin_twilio_receive_push_to_admin');
            }
        }
        echo'<form action="admin.php?page=church_admin%2Findex.php&action=twilio-replies" method="POST"><h3>'.esc_html( __('Set one person who you would like to receive push notificiation of Twilio SMS replies... ','church-admin' ) ).'</h3>';
        $people='';
        wp_nonce_field('twilio-replies');
        $twilio_people_ids=get_option('church_admin_twilio_receive_push_to_admin');
        if(!empty( $twilio_people_ids) )$people=church_admin_get_people( $twilio_people_ids);
        echo '<p>'.church_admin_autocomplete('twilio-admin-push','friends','to',$people,FALSE).'</p>';
        echo'<input type="hidden" name="twilio-admin-push-form" value="yes" /><input type="submit" class="button-secondary" value="'.esc_html( __('Set Twilio reply push','church-admin' ) ).'" /></p></form>';




        $SMSnumber=get_option('church_admin_sms_reply');
        if(!empty( $SMSnumber) )echo '<p>'.esc_html(sprintf(__('SMS number: %1$s','church-admin' ) ,$SMSnumber)).'</p>';
        echo'<p>'.sprintf(esc_html(__('To receive replies make sure you set this webhook %1$s at %2$s. Instruction at %3$s','church-admin' )) ,'<strong>'.admin_url('admin-ajax.php?action=church_admin_twilio_listener').'</strong>','<a target="_blank" href="https://www.twilio.com/console/phone-numbers/incoming">Twilio Numbers page</a>','<a target="_blank"  href="https://www.churchadminplugin.com/tutorials/twilio-incoming-numbers">tutorial page</a>').'</p>';
        echo'<p>'.esc_html( __('This list refreshes automatically every 10secs. Best to delete threads when finished to save data!','church-admin' ) ).'</p>';
        
        
        echo'<table class="widefat wp-list-table striped bordered">';
        $tableHeader='<tr><th class="column-primary">'.esc_html( __('Cell number','church-admin' ) ).'</th><th>'.esc_html( __('Delete thread','church-admin' ) ).'</th><th>'.esc_html( __('Last message received','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Message','church-admin' ) ).'</th><th>'.esc_html( __('View thread','church-admin' ) ).'</th><tr>';
        echo'<thead>'.$tableHeader.'</thead><tfoot>'.$tableHeader.'</tfoot><tbody class="ca-sms-replies">';
        echo church_admin_sms_replies_list();
        echo'</tbody></table>';
        $nonce=wp_create_nonce('refresh-sms-replies');
        echo'<script>
        jQuery(document).ready(function( $)  {
            var timeout = setInterval(reloadChat, 10000);    
            function reloadChat () {
               
                var data={"action":"church_admin","method":"refresh-sms-replies","nonce":"'.$nonce.'"};
		 	  
		 	    $.getJSON({
		 			url: ajaxurl,
		 			type: "POST",
		 			data: data,
		 			success: function(res) 
                    {
                    
                        $(".ca-sms-replies").html(res)
		 			}
		 	    });
                
            }
        
        });
    
    </script>';    
        
    }
}
function church_admin_sms_replies_list()
{
    global $wpdb;
     $results=$wpdb->get_results('SELECT t1.* FROM '.$wpdb->prefix.'church_admin_twilio_messages t1 INNER JOIN ( SELECT `mobile`, MAX(message_date) AS max_message_date FROM '.$wpdb->prefix.'church_admin_twilio_messages WHERE direction=0 GROUP BY `mobile` ) t2 ON t1.`mobile` = t2.`mobile` AND t1.message_date = t2.max_message_date ORDER BY t1.message_date DESC');

        if(!empty( $results) )
        {
            $out='';
            foreach( $results AS $row)
            {
                $name='&nbsp;';
                $nameObj=$wpdb->get_row('SELECT first_name,prefix,last_name FROM '.$wpdb->prefix.'church_admin_people WHERE e164cell="'.esc_sql( $row->mobile).'"');
                if(!empty( $nameObj) )$name=implode(" ",array_filter(array( $nameObj->first_name,$nameObj->prefix,$nameObj->last_name) ));
                $deleteThread='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\')"  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=comms&amp;action=delete-sms-thread&mobile='.urlencode( $row->mobile),'delete-sms-thread').'">'.esc_html( __('Delete SMS thread','church-admin' ) ).'</a>';
                $viewThread='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=view-sms-thread&section=comms&mobile='.urlencode( $row->mobile),'view-sms-thread').'">'.esc_html( __('View SMS thread','church-admin' ) ).'</a>';
                if ( empty( $row->mobile) )$row->mobile=__('Unknown','church-admin');
                
                $out.='<tr>
                    <td data-colname="'.esc_html( __('Cell number','church-admin' ) ).'" class="column-primary">'.esc_html( $row->mobile).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                    <td data-colname="'.esc_html( __('Delete thread','church-admin' ) ).'">'.$deleteThread.'</td>
                    <td data-colname="'.esc_html( __('Message date','church-admin' ) ).'">'.mysql2date(get_option('date_format').' '.get_option('time_format'),$row->message_date).'</td>
                    <td data-colname="'.esc_html( __('Name','church-admin' ) ).'">'.$name.'</td>
                    <td data-colname="'.esc_html( __('Message','church-admin' ) ).'">'.esc_html( $row->message).'</td>
                    <td data-colname="'.esc_html( __('View thread?','church-admin' ) ).'">'.$viewThread.'</td>
                </tr>';
            }
        }else $out='<tr><td colspan=6>'.esc_html( __('No replies received yet','church-admin' ) ).'</td></tr>';
    return $out;
}
/******************************************
*
*   Delete SMS thread
*
*******************************************/
function church_admin_delete_sms_thread( $mobile)
{
    global $wpdb;
    $e164cell='+'.trim( $mobile);
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_twilio_messages WHERE mobile="'.esc_sql( $e164cell).'"');
    echo '<div class="notice notice-success"><h2>'.esc_html(sprintf(__('Message thread for %1$s deleted','church-admin' ) ,$e164cell)).'</h2></div>';
    church_admin_twilio_replies_list();
}

/******************************************
*
*   View thread
*
*******************************************/
function church_admin_view_sms_thread( $mobile)
{
    global $wpdb;
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sms.php');
    $e164cell='+'.trim( $mobile);
    $name='';
    $nameObj=$wpdb->get_row('SELECT first_name,prefix,last_name FROM '.$wpdb->prefix.'church_admin_people WHERE e164cell="'.esc_sql( $e164cell).'"');
    if(!empty( $nameObj) )$name=implode(" ",array_filter(array( $nameObj->first_name,$nameObj->prefix,$nameObj->last_name) ));
    if(!empty( $_POST['e164cell'] ) )
    {
        
        church_admin_sms(church_admin_sanitize( $_POST['e164cell'] ),church_admin_sanitize( $_POST['ca-sms-message'] ),FALSE);
        
    }
    echo'<h2>'.esc_html(sprintf(__('Message thread for %1$s %2$s','church-admin' ) ,$name, $e164cell)).'</h2>';
     echo'<p>'.esc_html( __('This list refreshes automatically every 5secs.','church-admin' ) ).'</p>';
    church_admin_sms_credits();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_twilio_messages WHERE mobile="'.esc_sql( $e164cell).'" ORDER BY message_date ASC');
    if(!empty( $results) )
    {
        echo'<div class="ca-message-container"><div class="ca-sms-messages">';
        foreach( $results AS $row)
        {
            switch( $row->direction)
            {
                case '0': 
                    $class= 'class="ca-message-blue"';
                    $tsClass='class="ca-message-timestamp-left"';
                break;
                case '1': 
                    $class= 'class="ca-message-orange"';
                    $tsClass='class="ca-message-timestamp-right"';    
                break;
            }
            echo'<div '.$class.'>';
            $message=esc_html( $row->message);
            if(function_exists('make_clickable') )$message=make_clickable( $message);
            echo '<div class="ca-message-content">'.esc_html( $message ).'</div>'."\r\n";
            echo'<div '.$tsClass.'>'.esc_html( mysql2date( get_option( 'date_format' ).' '.get_option( 'time_format' ), $row->message_date ) ).'</div></div>'."\r\n";
        }
        $messageID=(int)$row->message_id;
        echo'</div>'."\r\n".'<div class="message_id" data-messageid="'.$messageID.'"></div>'."\r\n";
        echo'</div>'."\r\n";
    }
    echo'<h3>'.esc_html( __('Send reply','church-admin' ) ).'</h3>';
    echo'<form action="" class="ca-send-form" method="post">';
    echo'<textarea class="ca-send-message" name="ca-sms-message"></textarea>';
    echo'<input type="hidden" name="e164cell" value="'.esc_html( $e164cell).'" />';
    echo'<p><input type="submit" class="button-primary" value="'.esc_html( __('Send','church-admin' ) ).'" /></p></form>'; $nonce=wp_create_nonce('refresh-sms');
    echo'<script>
        jQuery(document).ready(function( $)  {
            var timeout = setInterval(reloadChat, 5000);    
            function reloadChat () {
            /* must use .attr as .data doesnt pick up changed DOM! */
                var lastID=$("body .message_id").attr("data-messageid");
                var e164cell="'.esc_html( $e164cell).'";
                var data={"action":"church_admin","method":"refresh-sms","lastID":lastID,"e164cell":e164cell,"nonce":"'.$nonce.'"};
		 	  
		 	    $.getJSON({
		 			url: ajaxurl,
		 			type: "POST",
		 			data: data,
		 			success: function(res) 
                    {
                        if(res.messages)$(".ca-sms-messages").append(res.messages)
                        if(res.id)$(".message_id").attr("data-messageid",res.id);
		 			}
		 	    });
                
            }
        
        });
    
    </script>';
}