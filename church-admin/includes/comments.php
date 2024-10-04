<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/**
 *
 * Comment form
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
 function church_admin_comment( $comment_type='people',$comment_id=NULL,$parent_id=NULL,$ID=NULL)
 {
 
 	global $wpdb,$current_user;

 	wp_get_current_user();
 	

 		if(!empty( $comment_id) )$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_comments WHERE comment_id="'.(int) $comment_id.'"');
 	
 		echo'<form action="" method="POST">';
 		$placeholder='placeholder="'.esc_html( __('Leave a note','church-admin' ) ).'"';
 		if(!empty( $parent_id) )  {$placeholder='placeholder="'.esc_html( __('Leave a reply','church-admin' ) ).'"';}
 		echo'<p><textarea class="ca-comment" name="comment" '.$placeholder.'>';
 		if(!empty( $data->comment) ) echo esc_html( $data->comment);
 		echo '</textarea></p>';
 		if(!empty( $ID) )echo'<input type="hidden" name="ID" value="'.(int)$ID.'" />';
 		if(!empty( $comment_type) )echo'<input type="hidden" name="comment_type" value="'.esc_html( $comment_type).'" />';
 		if(!empty( $parent_id) )echo'<input type="hidden" name="parent_id" value="'.intval( $parent_id).'" />';
 		if(!empty( $comment_id) )echo'<input type="hidden" name="comment_id" value="'.(int) $comment_id.'" />';
 		echo'<p><input type="hidden" name="save-ca-comment" value="yes" /><input class="button-secondarary" type="submit" value="'.esc_html( __('Save Note','church-admin' ) ).'&raquo;" /></p>';
 		echo'</form><hr/>';

 	
 }
 
 /**
 *
 * Show Comments
 * 
 * @author  Andy Moyle
 * @param   $comment_type,$ID
 * @return   
 * @version  0.1
 *
 * 2016-11-08 Changed query to LEFT JOIN to make work 
 *
 */ 
 
 function church_admin_show_comments( $comment_type,$ID)
 {
 	global $wpdb;
	church_admin_debug('*** church_admin_show_comments ***');
	//church_admin_debug($comment_type);
	//church_admin_debug($ID);
 	echo'<div class="ca-comments">';
 	echo'<h3>'.esc_html( __('Notes','church-admin' ) ).'</h3>';
 	//$sql='SELECT a.*, CONCAT_WS(" ",b.first_name,b.prefix,b.last_name) AS name FROM '.$wpdb->prefix.'church_admin_comments a, '.$wpdb->prefix.'church_admin_people b WHERE a.ID="'.esc_sql( $ID).'" AND a.comment_type="'.esc_sql( $comment_type).'" AND a.parent_id=0 AND a.author_id=b.user_id ORDER BY timestamp ASC';
 	//need to left join for some reason
 	$sql='SELECT a.*, CONCAT_WS(" ",b.first_name,b.prefix,b.last_name) AS name FROM '.$wpdb->prefix.'church_admin_comments a LEFT JOIN '.$wpdb->prefix.'church_admin_people b ON a.author_id=b.user_id WHERE a.ID="'.esc_sql( $ID).'" AND a.comment_type="'.esc_sql( $comment_type).'" ORDER BY timestamp ASC';
 	
 	$comments=$wpdb->get_results( $sql);
 	
 	if(!empty( $comments) )
 	{
 		
 		foreach( $comments AS $comment)
 		{
 		
 			church_admin_show_comment( $comment);
 		}	
 		
 	}
 	church_admin_comment( $comment_type,NULL,NULL,$ID);
 	echo'</div><!--ca-comments-->';
 }
 	
 	
 function church_admin_show_comment( $comment)
 {
 	global $wpdb;

 	echo'<div class="ca-comment ';
 	if(!empty( $comment->parent_id) )  { echo 'ca-reply';}
 	echo'" id="comment-'.$comment->comment_id.'">';
 	if ( empty( $comment->name) )$comment->name=$wpdb->get_var('SELECT user_login FROM '.$wpdb->users.' WHERE ID="'.(int)$comment->author_id.'"');
 	echo'<p class="ca-comment-meta">'.get_avatar( $comment->author_id,'50').esc_html( __('Posted by','church-admin' ) ).' '.esc_html( $comment->name).' '.esc_html( __('on','church-admin' ) ).' '.mysql2date(get_option('date_format'),$comment->timestamp).' <span class="note-delete" data-comment-id="'.$comment->comment_id.'" ><span style="color:red" class="ca-dashicons dashicons dashicons-no"></span>Delete</span> </p>';
 	echo'<p class="ca_comment_content">'.esc_html( $comment->comment).'</p>';
 	$replies=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_comments WHERE parent_id="'.(int)$comment->comment_id.'" ORDER BY timestamp DESC');
 	if(!empty( $replies) )
 	{
 		foreach( $replies AS $reply)
 		{
 			church_admin_show_comment( $reply);
 		}
 	}
 	
 	echo '<p class="ca-comment-reply" id="comment'.$comment->comment_id.'">'.esc_html( __('Reply (Click to toggle)','church-admin' ) ).'</p>';
 	echo'<div id="reply'.$comment->comment_id.'" style="display:none">';
 	church_admin_comment( $comment->comment_type,NULL,$comment->comment_id,$comment->ID);
 	echo'</div>';
 	$nonce = wp_create_nonce("note_delete");
 	echo'<script>jQuery(document).ready(function( $) {
 	$("#comment'.$comment->comment_id.'").click(function()  {$("#reply'.$comment->comment_id.'").toggle();});
 	$(".note-delete").on("click",function()
 	{
 		var note_id=$(this).attr("data-comment-id");
 		
 		var data = {
			"action": "church_admin",
			"method":"note_delete",
			"note_id": note_id,
			"nonce":"'.$nonce.'"
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			if(response)
			{
				var id="#comment-"+note_id;
				console.log(id);
				$(id).hide();
			}
		});
 	});
 	});</script>';
 	echo'</div>';
 }				