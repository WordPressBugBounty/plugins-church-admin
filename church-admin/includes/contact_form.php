<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_contact_form_settings()
{
    global $wpdb;
    $settings=get_option('church_admin_contact_form_settings');

    if(!empty( $_POST['contact_settings'] ) )
    {
        $maxUrls=(int)$_POST['max_urls'];
        $recipient=sanitize_text_field( stripslashes( $_POST['recipient'] ) );
        $pushToken=!empty( $_POST['pushToken'] )?sanitize_text_field( stripslashes($_POST['pushToken'] )):null;
        $spamWords=explode(",",str_replace(", ",",",sanitize_text_field( stripslashes($_POST['spam_words'] )) ));
        $newSettings=array(
            'max_urls'=>$maxUrls,
            'recipient'=>$recipient,
            'pushToken'=>$pushToken,
            'spam_words'=>$spamWords
        );
        update_option('church_admin_contact_form_settings',$newSettings);
        echo'<div class="notice notice-success"><h2>'.esc_html( __('Contact form settings updated','church-admin' ) ).'</h2></div>';
    }
    $settings=get_option('church_admin_contact_form_settings');
    echo'<form action="" method="POST">';
    echo'<h2>'.esc_html( __('Contact form settings','church-admin' ) ).'</h2>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Max URLS allowed in message','church-admin' ) ).'</label><input class="church-admin-form-control" name="max_urls" type="number" value="'.(int)$settings['max_urls'].'" /></div>'."\r\n";
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Email to send message to','church-admin' ) ).'</label><input class="church-admin-form-control" type="email" name="recipient" value="'.esc_html( $settings['recipient'] ).'" /></div>'."\r\n";
    $pushTokenPeople=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE pushToken IS NOT NULL ORDER BY last_name,first_name');
    if(!empty( $pushTokenPeople) )
    {
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Send push notification to','church-admin' ) ).'</label><select name="pushToken">';
        foreach( $pushTokenPeople AS $person)
        {
            echo'<option value="'.esc_html( $person->people_id).'" '.selected( $person->people_id,$settings['pushToken'],false).'>'.church_admin_formatted_name( $person).'</option>';
        }
        echo'</select></div>';
    }
 
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Banned words','church-admin' ) ).'</label><textarea class="church-admin-form-control" name="spam_words" style="height:100px">';
    echo esc_textarea(implode(", ",$settings['spam_words'] ) ).'</textarea></div>';
    echo'<p><input type="hidden" name="contact_settings" value="true" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
}

function church_admin_delete_contact_message( $id)
{
    global $wpdb;
    if(empty($id)){
        echo'<div class="notice notice-success"><h2>'.esc_html( __('No message selected','church-admin' ) ).'</h2></div>';
    }
    if($id=='all'){
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_contact_form');
        echo'<div class="notice notice-success"><h2>'.esc_html( __('All Messages deleted','church-admin' ) ).'</h2></div>';
    }
    else
    {
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_contact_form WHERE contact_id="'.(int)$id.'"');
        echo'<div class="notice notice-success"><h2>'.esc_html( __('Message deleted','church-admin' ) ).'</h2></div>';
    }
    church_admin_contact_form_list();
}



function church_admin_contact_form_list()
{
    global $wpdb;
    echo'<h2>'.esc_html( __('Contact form messages','church-admin' ) ).'</h2>';
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_contact_form ORDER BY post_date DESC');
    if ( empty( $results) )
    {
        echo '<p>'.esc_html( __('No messages yet','church-admin' ) ).'</p>';
        return;
    } 
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-contact-message&amp;section=contact-form&amp;id=all','delete_contact_message').'">'.esc_html( __('Delete all messages','church-admin' ) ).'</a></p>';
    $theader='<tr><th class="column-primary">'.esc_html( __('Messages','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Date','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Contact email','church-admin' ) ).'</th><th>'.esc_html( __('Telephone','church-admin' ) ).'</th><th>'.esc_html( __('Message','church-admin' ) ).'</th><th>URL</th></tr>';
        echo'<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tbody>';
    foreach( $results AS $row)
    {
        $phone=!empty( $row->phone)?'<a href="'.esc_url('tel:'.$row->phone).'">'.esc_html( $row->phone).'</a>':'&nbsp;';
        $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-contact-message&amp;section=contact-form&amp;id='.$row->contact_id,'delete_contact_message').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
        echo '<tr>
        <td data-colname="'.esc_html( __('Messages','church-admin' ) ).'" class="column-primary">'.esc_html( $row->subject).'<button type="button" class="toggle-row"><span class="screen-reader-text">'.esc_html( __('Show details','church-admin' ) ).'</span></button></td>
        <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'" >'.$delete.'</td>
        <td data-colname="'.esc_html( __('Date','church-admin' ) ).'" >'.mysql2date(get_option('date_format'),$row->post_date).'</td>
        <td data-colname="'.esc_html( __('Name','church-admin' ) ).'" >'.esc_html( $row->name).'</td>
        
        <td data-colname="'.esc_html( __('Contact email','church-admin' ) ).'" ><a href="'.esc_url('mailto:'.$row->email).'">'.esc_html( $row->email).'</a></td>
        <td data-colname="'.esc_html( __('Telephone','church-admin' ) ).'" >'.$phone.'</td>
        <td data-colname="'.esc_html( __('Message','church-admin' ) ).'" >'.sanitize_text_field( $row->message).'</td>
        <td data-colname="URL" >'.esc_url( $row->url).'</td>
       
        </tr>';
    }
    echo'</tbody><tfoot>'.$theader.'</tfoot></table>';
}