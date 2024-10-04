<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly





/****************************
 * APP pre v24
 ****************************/
/**
 *
 * Checks token
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_check_token()
{
		global $wpdb;
		$output=array('error'=>'login required');
		if ( empty( $_REQUEST['token'] ) )
		{
			$output=array('error'=>'login required');
		}
		else
		{
			$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field(stripslashes( $_REQUEST['token'] ) ) ).'"';

			$user_id=$wpdb->get_var( $sql);
			if(!empty( $result) )
			{
				$output=array(TRUE);
				//people-id
				$people_id=NULL;
				$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user_id.'"');
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_app SET last_login=NOW(),people_id="'.esc_sql( $people_id).'" WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes( $_REQUEST['token'] )) ).'"');
            }
			else
			{
				$output=array('error'=>'login required');
			}
		}
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode( $output);
		exit();
}
add_action("wp_ajax_ca_check_token", "ca_check_token");
add_action("wp_ajax_nopriv_ca_check_token", "ca_check_token");
/**
 *
 * Returns media
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */

function ca_sermons()
{
		global $wpdb;
		church_admin_app_log_visit( $loginStatus, __('Sermons','church-admin') );
		if(!empty( $_GET['token'] ) ){
			church_admin_app_log_visit( $loginStatus, __('Sermons','church-admin' ) ,$loginStatus );
		}
		$page=!empty($_REQUEST['page'])?sanitize_text_field(stripslashes($_GET['page'])):1;
		if(!church_admin_int_check($page)) {$page=1;}
		if($page>1)  {$offset=10*(int)$page-10;}else{$offset=0;}
		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/sermons/';
		$url=$upload_dir['baseurl'].'/sermons/';
		$output=array();
		$max=$wpdb->get_var('SELECT COUNT(file_id) FROM '.$wpdb->prefix.'church_admin_sermon_files');
		$pages=ceil( $max/10);
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files ORDER BY pub_date DESC LIMIT '.esc_sql($offset).',10';
		//church_admin_debug( $sql);
		$results=$wpdb->get_results( $sql);

		if(!empty( $results) )
		{
			foreach( $results AS $row)
			{


				$output[]=array('maxPages'=>$pages,'title'=>esc_html( $row->file_title),'id'=>(int)$row->file_id,'description'=>esc_html( $row->file_description),'speaker'=>esc_html( $row->speaker),'pub_date'=>mysql2date(get_option('date_format'),$row->pub_date),'file_url'=>esc_url( $url.$row->file_name) );
			}
		}


		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode( $output);
		exit();
}
add_action("wp_ajax_ca_sermons", "ca_sermons");
add_action("wp_ajax_nopriv_ca_sermons", "ca_sermons");
/**
 *
 * Returns one sermon media
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_sermon()
{
		global $wpdb;
		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/sermons/';
		$url=$upload_dir['baseurl'].'/sermons/';
		$output=array();

		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.(int)$_REQUEST['ID'].'"';
        //church_admin_debug( $sql);
		$row=$wpdb->get_row( $sql);
        //church_admin_debug(print_r( $row,TRUE) );
		if(!empty( $row) )
		{
                $file=NULL;
                if(!empty( $row->video_url) )  {$video=church_admin_generateVideoEmbedUrl( $row->video_url);}else $video=array('embed'=>0);
                if(!empty( $row->file_name) )$file=$url.$row->file_name;
                if(!empty( $row->external_file) )$file=$row->external_file;
				$output=array('title'=>esc_html( $row->file_title),'id'=>(int)$row->file_id,'description'=>esc_html( $row->file_description),'speaker'=>esc_html( $row->speaker),'pub_date'=>mysql2date(get_option('date_format'),$row->pub_date),'file_url'=>esc_url( $file),'video'=>$video['embed'] );
				if(!empty( $row->transcript) )$output['pdf'] = site_url().'?ca_download=sermon-notes&amp;file_id='.(int)$row->file_id; 
                $output['nonce']=wp_create_nonce("church_admin_mp3_play");
                $sermonlink=church_admin_find_sermon_page();
                $title=str_replace('"','',$data->file_title);
                $title=esc_html( $title);
				
                if(!empty( $sermonlink) )$output['share']='<p class="social-share"><a target="_blank" class="ca-share"  href="https://www.facebook.com/sharer/sharer.php?u='.$sermonlink.'?sermon='.esc_attr($row->file_slug).'"><svg class="ca-share-icon" style="width:50px;height:50px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20" /><g><path class="social-share-path" d="M2.89 2h14.23c.49 0 .88.39.88.88v14.24c0 .48-.39.88-.88.88h-4.08v-6.2h2.08l.31-2.41h-2.39V7.85c0-.7.2-1.18 1.2-1.18h1.28V4.51c-.22-.03-.98-.09-1.86-.09-1.85 0-3.11 1.12-3.11 3.19v1.78H8.46v2.41h2.09V18H2.89c-.49 0-.89-.4-.89-.88V2.88c0-.49.4-.88.89-.88z" /></g></svg></a> &nbsp; <a  class="ca-share"  target="_blank"  href="https://twitter.com/intent/tweet?text='.esc_attr($row->file_title.' '.$sermonlink).'?sermon='.esc_attr($row->file_slug).'"><svg class="ca-share-icon" style="width:50px;height:50px;"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20" /><g><path class="social-share-path" d="M18.94 4.46c-.49.73-1.11 1.38-1.83 1.9.01.15.01.31.01.47 0 4.85-3.69 10.44-10.43 10.44-2.07 0-4-.61-5.63-1.65.29.03.58.05.88.05 1.72 0 3.3-.59 4.55-1.57-1.6-.03-2.95-1.09-3.42-2.55.22.04.45.07.69.07.33 0 .66-.05.96-.13-1.67-.34-2.94-1.82-2.94-3.6v-.04c.5.27 1.06.44 1.66.46-.98-.66-1.63-1.78-1.63-3.06 0-.67.18-1.3.5-1.84 1.81 2.22 4.51 3.68 7.56 3.83-.06-.27-.1-.55-.1-.84 0-2.02 1.65-3.66 3.67-3.66 1.06 0 2.01.44 2.68 1.16.83-.17 1.62-.47 2.33-.89-.28.85-.86 1.57-1.62 2.02.75-.08 1.45-.28 2.11-.57z" /></g></svg></a>&nbsp;<a style="text-decoration:none" href="mailto:?subject='.esc_attr($row->file_title).'&amp;body='.esc_attr($sermonlink).'?sermon='.esc_attr($row->file_slug).'"><svg class="ca-share-icon"  style="width:50px;height:50px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20" /><g><path class="social-share-path" d="M3.87 4h13.25C18.37 4 19 4.59 19 5.79v8.42c0 1.19-.63 1.79-1.88 1.79H3.87c-1.25 0-1.88-.6-1.88-1.79V5.79c0-1.2.63-1.79 1.88-1.79zm6.62 8.6l6.74-5.53c.24-.2.43-.66.13-1.07-.29-.41-.82-.42-1.17-.17l-5.7 3.86L4.8 5.83c-.35-.25-.88-.24-1.17.17-.3.41-.11.87.13 1.07z" /></g></svg></a>&nbsp;<a href="sms:&body='.esc_attr($sermonlink).'?sermon='.esc_attr($row->file_slug).'"><svg  class="ca-share-icon"  style="width:50px;height:50px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20" /><g><path  class="social-share-path" d="M6 2h8c.55 0 1 .45 1 1v14c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1V3c0-.55.45-1 1-1zm7 12V4H7v10h6zM8 5h4l-4 5V5z" /></g></svg></a></p>';


			if ( empty( $row->file_name)&&!empty( $row->external_file) )$output['file_url']=esc_url( $row->external_file);

			church_admin_app_log_visit( $loginStatus,  esc_html( __('Sermon','church-admin' ) ).' - '.$row->file_title);
            //church_admin_debug(print_r( $output,TRUE) );
		}
		else
		{
			$output=array('error'=>'No sermon found');
		}


		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode( $output);
		exit();
}
add_action("wp_ajax_ca_sermon", "ca_sermon");
add_action("wp_ajax_nopriv_ca_sermon", "ca_sermon");
/**
 *
 * Returns posts
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_prayer_requests()
{
	global $wpdb;
	$postsPerPage=2;
	$page=!empty($_REQUEST['page'])?sanitize_text_field(stripslashes($_GET['page'])):1;
	if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus,  esc_html(__('Prayer Request','church-admin' ) ),$loginStatus );
	church_admin_app_log_visit( $loginStatus, __('Prayer Request','church-admin') );
	$private=get_option('church-admin-private-prayer-requests');
	if( $private)
	{

		if ( empty( $_GET['token'] ) )
		{//private but no token
			$output=array('error'=>'login required');

		}
		else
		{//private and check token
			$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field(stripslashes(  $_GET['token'] ) ) ).'"';
			$result=$wpdb->get_var( $sql);
			if ( empty( $result) )
			{//private and no login

				$output=array('error'=>'login required');
			}
			else
			{//private and logged in
				$output=ca_prayer_reqs( $paged);
			}
		}
	}
	else
	{
			//not private
			$output=ca_prayer_reqs( $paged);
	}

	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);

	die();
}

function ca_prayer_reqs( $paged)
{

	$posts_array = array();
	$postsPerPage=10;
	$args = array("post_type" => "prayer-requests", "orderby" => "date", "order" => "DESC", "post_status" => "publish","posts_per_page" => (int)$postsPerPage,'paged'=>$paged);

	$posts = new WP_Query( $args);

	if( $posts->have_posts() ):
		while( $posts->have_posts() ):
			$posts->the_post();
            $content = wpautop(get_the_content() );
			$content = '<div>'.$content.'</div>';
			$content= do_shortcode( $content);
            $post_array = array('title'=>get_the_title(),'content'=>$content,'date'=> get_the_date(),'ID'=>get_the_ID() );
            array_push( $posts_array, $post_array);

		endwhile;
		else:
        	 return array(array('title'=>esc_html( __('No prayer requests yet') ),'content'=>'',"date"=>wp_date('Y-m-d'),'ID'=>NULL) );

	endif;
	return( $posts_array);


}



add_action("wp_ajax_ca_prayer", "ca_prayer_requests");
add_action("wp_ajax_nopriv_ca_prayer", "ca_prayer_requests");
/**
 *
 * Returns posts
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_posts()
{
	
    header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	$postsPerPage=10;
	$paged=!empty($_REQUEST['page'])?sanitize_text_field(stripslashes($_GET['page'])):1;
	$paged=!empty($_REQUEST['paged'])?sanitize_text_field(stripslashes($_GET['paged'])):1;
   
	if(!empty( $_GET['cat_name'] ) )
	{
	    $cat_name=sanitize_text_field(stripslashes($_GET['cat_name'] ) );
		$cat_name=str_replace("-category","",$cat_name);//sorts main menu!
		
        $idObj = get_category_by_slug( $cat_name);
	    $cat_id = $idObj->term_id;
       
		$cat_count = get_category( $idObj);
      
        $maxPosts=$cat_count->count;
		if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus, esc_html( $cat_name),$loginStatus );
		church_admin_app_log_visit( $loginStatus, esc_html( $cat_name),'church-admin');
	}else
	{
		if(!empty( $_GET['token'] ) )  {
			church_admin_app_log_visit( $loginStatus, esc_html(__('News','church-admin' ) ),$loginStatus );
		}
		$postCount=wp_count_posts('post');
		$maxPosts=$postCount->publish;

		church_admin_app_log_visit( $loginStatus, __('News','church-admin') );
	}


	
	$maxNoOfPages=ceil( $maxPosts/$postsPerPage);

	$posts_array = array();

	$args = array("post_type" => "post", "orderby" => "date", "order" => "DESC", "post_status" => "publish", "posts_per_page" => $postsPerPage,'paged'=>$paged);
	
	$args['paged']=!empty($_REQUEST['page'])?sanitize_text_field(stripslashes($_GET['page'])):1;
	if(!empty( $cat_id) )  {$args['cat']=$cat_id;}
	if(defined('CA_DEBUG') )church_admin_debug(print_r( $args,TRUE) );
	$posts = new WP_Query( $args);

	if( $posts->have_posts() ):
		while( $posts->have_posts() ):
			$posts->the_post();
            church_admin_debug('Title '.get_the_title() );
            $post_array = array('title'=>get_the_title(),'link'=> get_the_permalink(),'date'=> get_the_date(),'thumbnail'=> wp_get_attachment_url(get_post_thumbnail_id() ),'ID'=>get_the_ID(),'max_pages'=>$maxNoOfPages);
            array_push( $posts_array, $post_array);

		endwhile;
		else:
        	echo "{'posts' = []}";
        	die();
	endif;
    //church_admin_debug(print_r( $posts_array,TRUE) );
	echo json_encode( $posts_array);

	die();
}



add_action("wp_ajax_ca_posts", "ca_posts");
add_action("wp_ajax_nopriv_ca_posts", "ca_posts");



add_action("wp_ajax_ca_posts", "ca_posts");
add_action("wp_ajax_nopriv_ca_posts", "ca_posts");
/**
 *
 * Returns one post
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_post()
{

    global $post;
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');


	$thisPost=get_post( (int)sanitize_text_field(stripslashes($_REQUEST['ID'] ) ) );
	$content=ca_filter_giving( $thisPost->post_content);
	$user = get_userdata( $thisPost->post_author);
	if(!empty( $_GET['token'] ) )
	{
			church_admin_app_log_visit( $loginStatus,  $thisPost->post_title,$loginStatus );
	}
    $previous_post=get_previous_post( $thisPost->ID);
    $prevID=$previous_post->ID;
    //church_admin_debug(print_r( $previous_post,TRUE) );
    $next_post=get_next_post( $thisPost->ID);
    $nextID=$next_post->ID;
    $links='<p>';
    if(!empty( $prevID) )$links.='<button class="newsItem button" id="'.(int)$prevID.'" data-tab="'.(int)$prevID.'" data-target=".newsitem">Previous</button> &nbsp;';
    if(!empty( $nextID) )$links.='<button class="newsItem button" id="'.(int)$nextID.'" data-tab="'.(int)$nextID.'" data-target=".newsitem">Next</button>';
    $links.='</p>';
	//handle blocks
	$content=do_blocks( $content);
    $content=do_shortcode( $content).$links;
	$author=get_the_author_meta('display_name',$thisPost->post_author);
	$data=array('title'=>$thisPost->post_title,'content'=>$content,'author'=>$author,'date'=>mysql2date(get_option('date_format'),$thisPost->post_date) );
	church_admin_app_log_visit( $loginStatus, esc_html(__('Blog post','church-admin' ) ).' - '.$thisPost->post_title);
	echo json_encode( $data);

	die();
}
add_action("wp_ajax_ca_post", "ca_post");
add_action("wp_ajax_nopriv_ca_post", "ca_post");

/**
 *
 * Returns rota
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_json_rota()
{
	global $wpdb;
	church_admin_app_log_visit( $loginStatus, __('Schedule','church-admin') );
	$version = !empty($_GET['version'] )?sanitize_text_field(stripslashes($_GET['version'] )):null;
	if(!empty( $version  ) && version_compare( $version ,2.6,'>=')>=0)
	{


		$private=get_option('church-admin-private-schedule');
		if( $private)
		{

			if ( empty( $_GET['token'] ) )
			{//private but no token
				$output=array('error'=>'login required');

			}
			else
			{//private and check token
				$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"';
				$result=$wpdb->get_var( $sql);
				if ( empty( $result) )
				{//private and no login

					$output=array('error'=>'login required');
				}
				else
				{//private and logged in
					$output=ca_json_rota_output();
				}
			}
		} //not private and app >2.6
		else $output=ca_json_rota_output();
	}
	else
	{//app is less than 2.6
		$output=ca_json_rota_output();
	}

	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}

function ca_json_rota_output()
{
	global $wpdb;
	if(!empty( $_GET['token'] ) )  {
		church_admin_app_log_visit( $loginStatus, esc_html(__('Rota','church-admin' ) ),$loginStatus );
		$userID=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field(stripslashes( $_GET['token'] ) )).'"');
	}
	$output=$rota=array();
	$check=$wpdb->get_var('SELECT count(*) FROM '.$wpdb->prefix.'church_admin_new_rota');
	if ( empty( $check) )
	{
		return array('error'=>"No one is doing anything yet");
	}
	//put chosen rota_id as first in json for dropdown
	$rota_id=!empty($_REQUEST['rota_id'])? sanitize_text_field(stripslashes($_GET['rota_id']) ):null;
	 
	if(!empty( $rota_id ) && church_admin_int_check( $rota_id ))
	{
		$sql='SELECT a.rota_date, a.rota_id,b.service_name,a.service_time,c.venue FROM '.$wpdb->prefix.'church_admin_new_rota a LEFT JOIN '.$wpdb->prefix.'church_admin_services b ON a.service_id=b.service_id  LEFT JOIN '.$wpdb->prefix.'church_admin_sites c ON b.site_id=c.site_id WHERE a.rota_id="'.(int)$rota_id.'" AND b.active=1';
		$row=$wpdb->get_row( $sql);
		
		if(!empty( $row) )$rota['services'][]=array('rota_id'=>(int)$row->rota_id,'detail'=>mysql2date("j M",$row->rota_date).' '.mysql2date(get_option('time_format'),$row->service_time).' '.esc_html( $row->service_name) );
	}
	//grab next 12 meetings

	$sql='SELECT a.rota_date, a.rota_id,b.service_name,a.service_time,c.venue FROM '.$wpdb->prefix.'church_admin_new_rota a LEFT JOIN '.$wpdb->prefix.'church_admin_services b ON a.service_id=b.service_id  LEFT JOIN '.$wpdb->prefix.'church_admin_sites c ON b.site_id=c.site_id WHERE a.rota_date >= CURDATE( ) AND b.active=1 GROUP BY a.service_id, a.rota_date ORDER BY rota_date ASC LIMIT 36';
	$results=$wpdb->get_results( $sql);
	foreach( $results AS $row)
	{
		$rota['services'][]=array('rota_id'=>(int)$row->rota_id,'detail'=>mysql2date("j M",$row->rota_date).' '.mysql2date(get_option('time_format'),$row->service_time).' '.esc_html( $row->service_name) );
	}

	//rota details for requested service
	if(!empty( $rota_id ) )
	{
		
		$sql='SELECT a.*,b.service_name,a.rota_date FROM '.$wpdb->prefix.'church_admin_new_rota  a,'.$wpdb->prefix.'church_admin_services b WHERE a.rota_id="'.(int)$rota_id.'" AND a.service_id =b.service_id';
	}
	else
	{

		$sql='SELECT a.*,b.service_name,a.rota_date FROM '.$wpdb->prefix.'church_admin_new_rota  a  LEFT JOIN '.$wpdb->prefix.'church_admin_services b ON a.service_id =b.service_id WHERE a.rota_date>=CURDATE() AND b.active=1 ORDER BY rota_date ASC LIMIT 1';
	}

	$selectedService=$wpdb->get_row( $sql);
	$rotaID=(int)$selectedService->rota_id;
	//workout which rota jobs are required
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
	$requiredRotaJobs=$rotaDates=array();
	foreach( $rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize( $rota_task->service_id);
		if(is_array( $allServiceID)&&in_array( $selectedService->service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
	}
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$selectedService->service_id.'" AND mtg_type="service" AND rota_date>='.$selectedService->rota_date;
	$rotaDatesResults=$wpdb->get_results( $sql);

	foreach( $requiredRotaJobs AS $rota_task_id=>$value)
	{
		$people=esc_html(church_admin_rota_people( $selectedService->rota_date,$rota_task_id,$selectedService->service_id,'service') );
		if(!empty( $people) )$rota['tasks'][]=array('job'=>esc_html( $value),'people'=>$people);

	}

	if(!empty( $userID) )
	{
		$rota['admin']=TRUE;
		$rota['rota_date']=$selectedService->rota_date;
		$rota['service_id']=(int)$selectedService->service_id;
	}
	return $rota;

}



add_action("wp_ajax_ca_rota", "ca_json_rota");
add_action("wp_ajax_nopriv_ca_rota", "ca_json_rota");
/*****************************************************
 * 
 * Schedule Edit
 *****************************************************/
add_action("wp_ajax_ca_edit_rota", "ca_edit_rota");
add_action("wp_ajax_nopriv_ca_edit_rota", "ca_edit_rota");
function ca_edit_rota()
{
	global $wpdb;
	church_admin_debug('**** ca_edit_rota function ****');
	church_admin_debug( $_REQUEST);
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	$out=array();
	//Check for token
	if ( empty( $_REQUEST['token'] ) )
	{
		church_admin_debug('No token');
		$out['error'] = 'login required';
		echo json_encode( $out);
		exit();
	}
	//check valid user
	$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field(stripslashes($_GET['token'] ) ) ).'"';
	$userID=$wpdb->get_var( $sql);
	if ( empty( $userID) )
	{
		church_admin_debug('No valid user');
		echo json_encode(array('error'=>'login required') );
		exit();
	}
	//check user is allowed to edit rota
	if(!church_admin_level_check('Rota',$userID) )
	{
		church_admin_debug('No rota edit permission');
		echo json_encode(array('content'=>__("You don't have permissions to edit the schedule",'church-admin') ));
		exit();
	}
	//safe to proceed
	church_admin_debug('Proceeding');
	$service_id=!empty( $_REQUEST['service_id'] ) ? sanitize_text_field(stripslashes($_REQUEST['service_id'])):null;
	$rota_date=!empty( $_REQUEST['rota_date'] ) ? sanitize_text_field(stripslashes( $_REQUEST['rota_date'] )):null;
	if ( empty( $service_id)||empty( $rota_date) ||!church_admin_checkdate($rota_date)||!church_admin_int_check($service_id))
	{
		church_admin_debug('No service or date details');
		echo json_encode(array('content'=>__("Missing service details",'church-admin') ));
		exit();
	}

	$service=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
	if(!empty( $rota_date) ){
		$service_time=$wpdb->get_var('SELECT service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND rota_date="'.esc_sql( $rota_date).'" LIMIT 1');
	}
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings  ORDER BY rota_order');
	$requiredRotaJobs=array();
	foreach( $rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize( $rota_task->service_id);
		if(is_array( $allServiceID)&&in_array( $service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
		
	}
	church_admin_debug("Required Rota Jobs");
	church_admin_debug( $requiredRotaJobs);


	$content ='<h2>'.esc_html(sprintf(__('Edit Schedule for %1$s','church-admin' ) , mysql2date(get_option('date_format'),$rota_date).' '. $service->service_name) ).'</h2>';
	church_admin_debug( $content);
	$results=$wpdb->get_results('SELECT a.*, a.rota_id AS edit_rota_id,b.* FROM '.$wpdb->prefix.'church_admin_new_rota a, '.$wpdb->prefix.'church_admin_rota_settings b WHERE a.service_id="'.(int)$service_id.'" AND a.rota_date="'.esc_sql($rota_date).'" AND a.rota_task_id=b.rota_id ORDER BY b.rota_order ASC');
	church_admin_debug( $wpdb->last_query);
	$rotaJobs=array();
	//create array of currently filled jobs from DB
	foreach( $results AS $row)
	{
		
		$rotaJobs[$row->rota_task_id]=church_admin_rota_people( $rota_date,$row->rota_task_id,$service_id,'service');
	}

	//now go through all jobs for that service and create form field, populating value as done.
	foreach( $requiredRotaJobs AS $rota_task_id=>$jobName)
	{

		$content.='<p class="form-group"><label>'.esc_html( $jobName).'</label><input class="rota-data form-control" type="text"  data-rota_task_id="'.(int)$rota_task_id.'" class="rota-job" value="'.$rotaJobs[$rota_task_id].'" /></p>';
	}

	$content.='<p><button class="button" id="save-rota" data-rota_date="'.esc_html( $rota_date).'" data-service_id="'.(int)$service_id.'">'.esc_html( __('Save','church-admin' ) ).'</button>';
	echo json_encode(array('content'=>$content) );
	exit();
}

/*****************************************************
 * 
 * Schedule SAVE
 *****************************************************/
add_action("wp_ajax_ca_save_rota", "ca_save_rota");
add_action("wp_ajax_nopriv_ca_save_rota", "ca_save_rota");
function ca_save_rota()
{
	global $wpdb;
	church_admin_debug('**** ca_save_rota function ****');
	
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	$out=array();
	//Check for token
	if ( empty( $_REQUEST['token'] ) )
	{
		church_admin_debug('No token');
		$out['error'] = 'login required';
		echo json_encode( $out);
		exit();
	}
	//check valid user
	$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] )) ).'"';
	$userID=$wpdb->get_var( $sql);
	if ( empty( $userID) )
	{
		church_admin_debug('No valid user');
		echo json_encode(array('error'=>'login required') );
		exit();
	}
	//check user is allowed to edit rota
	if(!church_admin_level_check('Rota',$userID) )
	{
		church_admin_debug('No rota edit permission');
		echo json_encode(array('content'=>__("You don't have permissions to edit the schedule",'church-admin') ));
		exit();
	}
	//safe to proceed
	church_admin_debug('Proceeding with saving rota ');

	$service_id=!empty( $_REQUEST['service_id'] ) ? sanitize_text_field(stripslashes($_REQUEST['service_id'])):null;
	if(empty($service_id) || !church_admin_int_check($service_id)){exit();}
	$rota_date=!empty( $_REQUEST['rota_date'] ) ? sanitize_text_field(stripslashes( $_REQUEST['rota_date'] )):null;
	if(empty($rota_date) || !church_admin_checkdate($rota_date)){exit();}
	$rota_data = !empty($_REQUEST['rota_data'])? church_admin_sanitize($_REQUEST['rota_data']):null;
	if(empty($rota_date)){exit();}

	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_date="'.esc_sql($rota_date).'" AND service_id="'.(int)$service_id.'" AND mtg_type="service"');
	foreach( $rota_data AS $task_id=>$people)
	{
		if(!empty( $people) )$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_new_rota (mtg_type,service_id,rota_date,people_id,rota_task_id) VALUES("service","'.$service_id.'","'.esc_sql($rota_date).'","'.esc_sql(sanitize_text_field( $people) ).'","'.(int)$task_id.'")');
	}
	echo json_encode(array('content'=>'<h2>'.esc_html( __('Saved','church-admin' ) ).'</h2><p><button class="tab-button button" data-tab="#rota">'.esc_html( __('Back to schedule','church-admin' ) ).'</button></p>') );
	exit();
}







add_action("wp_ajax_ca_new_cal", "ca_app_calendar");
add_action("wp_ajax_nopriv_ca_new_cal", "ca_app_calendar");

function ca_app_calendar()
{
	global $wpdb;
	church_admin_app_log_visit( $loginStatus, __('Calendar','church-admin') );
	if(!empty( $_GET['token'] ) ){
		church_admin_app_log_visit( $loginStatus, esc_html(__('Calendar','church-admin' ) ) ,$loginStatus );
	}

	$date = !empty( $_REQUEST['date'])?sanitize_text_field(stripslashes($_REQUEST['date'])):date('Y-m-d',strtotime("yesterday") );
	if(empty($date) || !church_admin_checkdate($date)){exit();}

	
	$sql='SELECT link,link_title,event_id, title,description,start_date,start_time,end_time,location,date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE general_calendar=1 AND start_date BETWEEN DATE_ADD("'.esc_sql($date).'" , INTERVAL 1 DAY) AND DATE_ADD("'.esc_sql($date).'" , INTERVAL 8 DAY) ORDER By start_date,start_time ASC';
	church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	$output=array();
	$dates='';
	
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			$thisTS=strtotime( $row->start_date);
			$lastWeek=date("Y-m-d",$thisTS-604800);
			$dates.= '<li  class="ui-li-static ui-body-inherit calItem"  data-date="'.esc_html( $row->start_date).'" data-prev-week="'.esc_attr($lastWeek).'">';
		
			$dates.='<h3 class="ui-li-heading">'.esc_html( $row->title).'</h3>';
			$dates.='<p class="ui-li-desc"><strong>'.mysql2date(get_option('date_format'),$row->start_date).' '.mysql2date(get_option('time_format'),$row->start_time).'-'.mysql2date(get_option('time_format'),$row->end_time).'</strong><br>';
			if( $row->description) $dates.=sanitize_text_field( $row->description).'<br>';
			if( $row->location) $dates.=sanitize_text_field( $row->location).'<br>';
			$dates.='</p>';
			if( $row->link) $dates.='<p><a href="'.esc_url( $row->link).'" class="button" >'.esc_html( $row->link_title).'</a></p>'; 
			$dates.='<p><a  rel="nofollow" href="'.esc_url(site_url().'/?ca_download=ical&amp;date_id='.(int)$row->date_id).'"  class="button" ><i class="fas fa-calendar-plus"></i> '.esc_html( __("Download",'church-admin' ) ).'</a></p>';
			$dates.='</li>';

		}
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo $dates;
	die();
}


/**
 *
 * Returns calendar
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_json_cal()
{
	global $wpdb;

	church_admin_app_log_visit( $loginStatus, __('Calendar','church-admin') );
	if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus, esc_html( __('Calendar','church-admin' ) ),$loginStatus );
	$output=$op=array();
	//dates
	$date = !empty( $_REQUEST['date'])?sanitize_text_field(stripslashes($_REQUEST['date'])):wp_date('Y-m-d' );

	if(!church_admin_checkdate( $date) )  {$date=NULL;}
	$output['dates']=ca_createweeklist( $date);


	//information for dates
	$now='CURDATE()';
	if(church_admin_checkdate( $date) )$now='"'.$date.'"';
	$sql='SELECT link,link_title,event_id, title,description,start_date,start_time,end_time,location,date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE general_calendar=1 AND start_date BETWEEN '.esc_sql($now).' AND DATE_ADD('.esc_sql($now).', INTERVAL 7 DAY) ORDER By start_date,start_time ASC';
	church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
            if( $row->start_time=='00:00:00' &&$row->end_time=='23:59:00')
            {
                $start_time= __('All day event','church-admin');
                $end_time='';
                $iso_start_time='';
                $iso_end_time='';
            }
            else
            {
                $iso_start_time=esc_html( $row->start_time);
                $iso_end_time=esc_html( $row->end_time);
                $start_time=mysql2date(get_option('time_format'),$row->start_time);
                $end_time=mysql2date(get_option('time_format'),$row->end_time);
            }
            if ( empty( $row->link_title) )$row->link_title=__('More information','church-admin');
			$output['cal'][]=array(
							'title'=>$row->title,
							'description'=>$row->description,
							'location'=>esc_html( $row->location),
							'link'=>$row->link,
                            'link_title'=>$row->link_title,
                            'start_date'=>mysql2date(get_option('date_format'),$row->start_date),
							'iso_date'=>esc_html( $row->start_date),
							'iso_start_time'=>$iso_start_time,
							'iso_end_time'=>$iso_end_time,
							'start_time'=>$start_time,
							'end_time'=>$end_time,
							'event_id'=>(int)$row->event_id,
							'ical'=>site_url().'/?ca_download=ical&amp;date_id='.(int)$row->date_id
							);

		}

	}else{
        $output['cal'][]=array(
							'title'=>esc_html( __('No events in the calendar this week','church-admin' ) ),
							'description'=>esc_html( __('Your church admin needs to add some events','church-admin' ) ),
							'location'=>' ',
							'link'=>'',
                            'link_title'=>'',
                            'start_date'=>mysql2date(get_option('date_format'),$date),
							'iso_date'=>esc_html( $date),
							'iso_start_time'=>'09:00:00',
							'iso_end_time'=>'09:01:00',
							'start_time'=>mysql2date(get_option('time_format'),'09:00:00'),
							'end_time'=>mysql2date(get_option('time_format'),'09:01:00'),
							'event_id'=>0
							);


    }
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}



add_action("wp_ajax_ca_cal", "ca_json_cal");
add_action("wp_ajax_nopriv_ca_cal", "ca_json_cal");

/**
 *
 * Returns week of list
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_createweeklist( $date) {
	$dates=array();
	if(!empty( $date)&&church_admin_checkdate( $date) )$dates[]=array('mysql'=>$date,'friendly'=>date('D jS M', strtotime( $date) ));
	// assuming your week starts  sunday

	// set start date
	// function will return the monday of the week this date is in
	// eg the monday of the week containing 1/1/2005
	// was 31/12/2004

	$startdate = ca_sundayofweek(date("j"), date("n"), date("Y") );

	// set end date
	// the values below use the current date

	$enddate = ca_sundayofweek(date('j',strtotime('+12 weeks') ),date('n',strtotime('+12 weeks') ),date('Y',strtotime('+12 weeks') ));

	// $currentdate loops through each inclusive monday in the date range

	$currentdate = $startdate;

	do {

		$dates[]=array('mysql'=>date("Y-m-d", $currentdate),'friendly'=>date('D jS M', $currentdate) );

		$currentdate = strtotime("12pm next Sunday", $currentdate);

	} while ( $currentdate <= $enddate);
	return $dates;

}

function ca_sundayofweek( $day, $month, $year) {

	// setting the time to noon avoids any daylight savings time issues

	$returndate = mktime(12, 0, 0, $month, $day, $year);

	// if the date isnt a sunday adjust it to the previous sunday

	if (date("w", $returndate) != 0) {

		$returndate = strtotime("12pm last sunday", $returndate);

	}

	return $returndate;

}
/**
 *
 * Login
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_login()
{
	global $wpdb;
	

	if(!empty( $_GET['username'] ) )
	{
		//backwards compatible for older versions of app
		$username = urldecode(sanitize_text_field( stripslashes( $_GET["username"] )) );
		$password = sanitize_text_field(stripslashes($_GET["password"]) );
	}
	else
	{
		//username1 & password1 to get round Google Captcha plugin blocking logins
		$username = urldecode(sanitize_text_field( $_GET["username1"] ));
		$password  =sanitize_text_field( $_GET["password1"]);
	}
	$pushToken=!empty( $_POST['pushToken'] )?sanitize_text_field( stripslashes($_POST['pushToken'] ) ):NULL;
	$user=wp_authenticate( $username,$password);
	if(is_wp_error( $user) )
	{
		$op=array('error'=>'login required');
	}
	else
	{
		$userID=$user->ID;
	}
	if (empty( $userID) )
	{

		$op=array('error'=>'login required');

	}else
	{
		if(defined('CA_DEBUG') )church_admin_debug('Look for people_id');
		$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$userID.'"');
		if(defined('CA_DEBUG') )church_admin_debug("If in directorys people_id = ".(int)$people_id);
		if(!$people_id)
		{
			
			if(defined('CA_DEBUG') )church_admin_debug('People id not found by user_id');
			//check if email address is in directory
			$user=get_user_by('ID',$userID);

			$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql( $user->user_email).'"');
			if(!empty( $people_id) )
			{
				church_admin_debug("People id  found by email address so updating user_id");
				//update directory entry with userID
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$user->ID.'" WHERE people_id="'.(int)$people_id.'"');
			}
			else
			{
				church_admin_debug('Creating an entry');
				//create an entry
				if(!empty( $user->first_name) )  {$first_name=$user->first_name;}else{$first_name='Admin';}
				if(!empty( $user->last_name) )  {$last_name=$user->last_name;}else{$last_name='User';}
				$email=$user->user_email;
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household(address,first_registered)VALUES("","'.esc_sql(wp_date('Y-m-d')).'")');
				$household_id=$wpdb->insert_id;
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,household_id,show_me,user_id,head_of_household,sex,people_type_id,gdpr_reason,pushToken,first_registered)VALUES("'.esc_sql($first_name).'","'.esc_sql($last_name).'","'.esc_sql($email).'","'.(int)$household_id.'","0","'.(int)$user->ID.'",1,1,1,"'.esc_sql(__('Created from current user account','church-admin')).'","'.esc_sql($pushToken).'","'.esc_sql(wp_date('Y-m-d')).'")');
				$people_id=$wpdb->insert_id;
			}

		}
		//update push Token
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET pushToken="'.esc_sql( $pushToken).'" WHERE people_id="'.(int)$people_id.'"');
		
		$sql='SELECT app_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['UUID'] )) ).'"';
		$check=$wpdb->get_var( $sql);
		if(defined('CA_DEBUG') )church_admin_debug('Entry for UUID?'. $sql);
		
		if(!empty( $check) )
		{
			//update
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_app SET user_id="'.(int)$userID.'",people_id="'.esc_sql( $people_id).'",last_login="'.esc_sql(wp_date('Y-m-d h:i:s')).'" WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['UUID'] )) ).'"');
			if(defined('CA_DEBUG') )church_admin_debug('UPDATE app table with hash of username/password andlast login timestamp.');
			if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
		}
		else
		{
			//store hashed UUID to use as token along with people_id, user_id
			$sql='INSERT INTO '.$wpdb->prefix.'church_admin_app (UUID,user_id,last_login,people_id)VALUES("'.esc_sql(sanitize_text_field( stripslashes($_GET['UUID'] )) ).'","'.esc_sql($userID).'","'.esc_sql(wp_date('Y-m-d h:i:s')).'","'.esc_sql( $people_id).'")';

			$wpdb->query( $sql);
			if(defined('CA_DEBUG') )church_admin_debug('Insert hash and timestamp, people_id into app table');
			if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
		}
        $menu=ca_build_menu( $people_id);
		$op=array('login'=>true,'menu'=>implode("\r\n",$menu) );
	}
    //church_admin_debug(print_r( $op,TRUE) );
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $op);
	die();
}

add_action("wp_ajax_ca_login", "ca_login");
add_action("wp_ajax_nopriv_ca_login", "ca_login");


function ca_search()
{
	church_admin_app_log_visit( $loginStatus, __('Address List','church-admin') );
	global $wpdb;

	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus, esc_html(__('Address List','church-admin' ) ),$loginStatus );
	$output=array();
	//check token first
	if ( empty( $_GET['token'] ) )
	{
		echo json_encode(array('error'=>'login required') );
		exit();
	}
	$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field(stripslashes( $_GET['token'] )) ).'"';
	$userID=$wpdb->get_var( $sql);
	if ( empty( $userID) )
	{

		echo json_encode(array('error'=>'login required') );
		exit();
	}


	$person=$wpdb->get_row('SELECT * FROM '. $wpdb->prefix.'church_admin_people'.' WHERE user_id="'.(int)$userID.'"');
	church_admin_debug(print_r( $person,TRUE) );
	//Get ordered results
	$mt=get_option('church_admin_app_member_types');
	church_admin_debug("Member types");
	church_admin_debug(print_r( $mt,TRUE) );
	//reject if wrong member type
	if(!in_array( $person->member_type_id,$mt) )
	{
		church_admin_debug("App address list - wrong member type");
		echo json_encode( array('formatted'=>'<p>'.esc_html( __("Unfortunately you can't access the directory list (member type)",'church-admin' ) ).'</p>') );
		exit();
	}
	//reject if restricted access
	$restrictedList=get_option('church-admin-restricted-access');
	if(is_array( $restrictedList)&&in_array( $person->people_id,$restrictedList) )
	{ 
		church_admin_debug("App address list - luser on restricted list");
		echo json_encode( array('formatted'=>'<p>'.esc_html( __("Unfortunately you can't access the directory list (restricted)",'church-admin' ) ).'</p>') );
		exit();
	}
			
			
	$s=!empty($_GET['search'])?sanitize_text_field( stripslashes($_GET['search'] ) ) :null;
	
	if ( empty( $mt) )$mt=array(1);
	foreach( $mt AS $key=>$type)  {$mtsql[]='a.member_type_id='.(int)$type;}
	//adjust member_type_id section
	$sql='SELECT a.*,b.address,b.lat,b.lng,b.phone,a.e164cell,a.mobile,a.email FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_household b ON b.household_id=a.household_id WHERE a.show_me=1 AND a.household_id=b.household_id AND ('.implode('||',$mtsql).')AND  (CONCAT_WS(" ",a.first_name,a.last_name) LIKE("%'.esc_sql($s).'%")||CONCAT_WS(" ",a.first_name,a.middle_name,a.last_name) LIKE("%'.esc_sql($s).'%")||a.nickname LIKE("%'.esc_sql($s).'%")||a.first_name LIKE("%'.esc_sql($s).'%")||a.middle_name LIKE("%'.esc_sql($s).'%")||a.last_name LIKE("%'.esc_sql($s).'%")||a.email LIKE("%'.esc_sql($s).'%")||a.mobile LIKE("%'.esc_sql($s).'%")||b.address LIKE("%'.esc_sql($s).'%")||b.phone LIKE("%'.esc_sql($s).'%")  ||b.address LIKE("%'.$sql_safe_search.'%")  || b.phone LIKE ("%'.$sql_safe_search.'%") ) AND (b.privacy=0 OR b.privacy IS NULL) ORDER BY a.last_name,a.people_order,a.first_name';
	church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);

	if(!empty( $results) )
	{
		$formatted='';
		foreach( $results AS $row)
		{

			$formatted.='<p><strong>'.esc_html(implode(" ",array_filter(array( $row->first_name,$row->prefix,$row->last_name) ))).'</strong></p>';
			if(!empty( $row->address) )$formatted.='<p>'.esc_html( $row->address).'</p>';
			if(!empty( $row->mobile)&&!empty( $row->e164cell) )$formatted.='<p><a href="tel:'.esc_html(str_replace(' ','',$row->e164cell) ).'">'.esc_html( $row->mobile).'</p>';
			if(!empty( $row->email) )$formatted.='<p><a href="mailto:'.esc_html(str_replace(' ','',$row->email) ).'">'.esc_html( $row->email).'</p>';

			$formatted.='<p><a href="'.esc_url('https://www.google.com/maps/search/?api=1&query='.$row->lat.','.$row->lng.'&amp;t=m&amp;z=16').'" class="button button-map">'.esc_html( __('Map','church-admin' ) ).'</a></p>'."\r\n\t";
			$formatted.='<p><a href="https://www.google.com/maps/dir/?api=1&destination='.urlencode( $row->address).'" class="button button-map">'.esc_html( __('Directions','church-admin' ) ).'</a></p>'."\r\n\t";
			$formatted.='<p><a href="'.home_url().'/?ca_download=vcf&id='.(int)$row->household_id.'&vcf='.md5('AllAboutJesus'.$row->household_id).'&_wpnonce='.wp_create_nonce( $row->household_id).'">'.esc_html( __('Save to contacts','church-admin' ) ).'</a></p><hr/>';
			if ( empty( $row->phone) )$row->phone='';
			if ( empty( $row->mobile) )$row->mobile='';
			if(!empty( $row->address) )  {$address=explode(", ", $row->address);}else{$address=array(0=>NULL,1=>NULL,2=>NULL,3=>NULL);}

				$output[]=array('id'=>(int)$row->people_id,
							'first_name'=>esc_html( $row->first_name),
							'last_name'=>esc_html( $row->last_name),
							'name'=>esc_html( $row->first_name).' '.esc_html( $row->last_name),
							'email'=>esc_html( $row->email),
							'mobile'=>esc_html( $row->mobile),
							'phone'=>esc_html( $row->phone),
							'address'=>esc_html( $row->address),
							'streetAddress'=>$address[0],
							'locality'=>$address[1],
							'region'=>$address[2],
							'postalCode'=>$address[3],
							'vcf'=>home_url().'/?ca_download=vcf&id='.(int)$row->household_id.'&vcf='.md5('AllAboutJesus'.$row->household_id).'&_wpnonce='.wp_create_nonce( $row->household_id),

						);

			}
		//handle new version fo app from v12
		if(!empty( sanitize_text_field(stripslashes($_GET['search-version'] ))) )echo json_encode(array('formatted'=>$formatted) );
	}
	else
	{
		church_admin_debug('No search results');
		echo json_encode(array('formatted'=>'<p>'.esc_html( __('No results','church-admin' ) ).'</p>') );
		exit();
	}
	die();
}
add_action("wp_ajax_ca_search", "ca_search");
add_action("wp_ajax_nopriv_ca_search", "ca_search");


function ca_groups()
{
	church_admin_app_log_visit( $loginStatus, __('Groups','church-admin') );
	global $wpdb,$wp_locale;

	$allowed_html = [
		'a'      => [
			'href'  => [],
			'title' => [],
		],
		'br'     => [],
		'em'     => [],
		'strong' => [],
	];
	if(!empty( $_GET['token'] ) ){
		church_admin_app_log_visit( $loginStatus, esc_html(__('Groups','church-admin' ) ),$loginStatus);
	}
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id!=1';
	$results = $wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		foreach ( $results as $row)
		{
			$leaders=NULL;
			$ldrsResults=$wpdb->get_results('SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE b.meta_type="smallgroupleader" AND b.ID="'.(int)$row->id.'" AND a.people_id=b.people_id');
			
			if(!empty( $ldrsResults) )
			{
				$curr_leaders=array();
				foreach( $ldrsResults AS $ldrsRow)
				{
					$curr_leaders[]=$ldrsRow->name;
				}
				$leaders=esc_html(implode(", ",$curr_leaders) );
			}
			$image=null;
			if(!empty( $row->attachment_id) ) $image = wp_get_attachment_image( $row->attachment_id,'medium',FALSE,array('class'=>"group-image") );
			$description=!empty( $row->description)?wp_kses( $row->description,$allowed_html):null;
			$contact=NULL;
			if(!empty( $row->contact_number) )
			{
				if(is_email( $row->contact_number) )
				{
					$contact='<a href="'.esc_url('mailto:'.$row->contact_number).'">'.esc_html( $row->contact_number).'</a>';
				}
				else
				{
					$contact='<a href="'.esc_url('tel:'.$row->contact_number).'">'.esc_html( $row->contact_number).'</a>';
				}

			}
		
			$output[]=array(
				'name'=>esc_html( $row->group_name),
				'whenwhere'=>esc_html( $wp_locale->get_weekday( $row->group_day).' '.mysql2date(get_option('time_format'),$row->group_time) ),
				'address'=>esc_html( $row->address),
				'lat'=>$row->lat,
				'lng'=>$row->lng,
				'leaders'=>$leaders,
				'description'=>$description,
				'contact'=>$contact,
				'image'=>$image,
				//'staticmap'=>$staticMapImage
			);
		}

	}else
	{
		$output=array('error'=>esc_html( __('No small groups yet','church-admin') ));

	}
	
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_groups", "ca_groups");
add_action("wp_ajax_nopriv_ca_groups", "ca_groups");

function ca_forgotten_password()
{
		$login = trim( sanitize_text_field(stripslashes($_GET['user_login'] )));
		church_admin_debug("Forgotten password login ".$login);
		$user_data = get_user_by('login', $login);
		if ( empty( $user_data) )$user_data = get_user_by('email', $login);
		
		if ( empty( $user_data) )  {$output=array('error'=>'<p>User details not found, please try again</p>');}
		else
		{
			//church_admin_debug("User date".print_r( $user_data,true) );
			// Redefining user_login ensures we return the right case in the email.
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$key = get_password_reset_key( $user_data );
			$message = '<p>Someone has requested a password reset for the following account at '. "\r\n\r\n";
			$message .= network_home_url( '/' ) . "</p>\r\n\r\n";
			$message .= '<p>'.sprintf(__('Username: %s'), $user_login) . "</p>\r\n\r\n";
			$message .= '<p>If this was a mistake, just ignore this email and nothing will happen.</p>' . "\r\n\r\n";
			$message .= '<p>To reset your password, visit the following address:</p>' . "\r\n\r\n";
			$message .= '<p><a href="' . site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login), 'login') . '">'.esc_html( __("Reset",'church-admin' ) ).'</a></p>'."\r\n";
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			$title = sprintf( __('[%s] Password Reset'), $blogname );
			//$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );
			church_admin_debug( $message);
			add_filter( 'wp_mail_from_name','church_admin_from_name' );
			add_filter( 'wp_mail_from', 'church_admin_from_email');
			add_filter('wp_mail_content_type','church_admin_email_type');
			if ( $message && wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) )  {	$output=array('message'=>'<p>Password email has been sent to your registered email address</p>');}
			else
			{
				$error=esc_html(sprintf(__('Password reset email failed to send to %1$s','church-admin' ) ,$user_email));
			
				$output=array('error'=>'<p>Password reset email failed to send. Please try again.</p>');
			
			}
			remove_filter( 'wp_mail_from_name','church_admin_from_name' );
			remove_filter( 'wp_mail_from', 'church_admin_from_email');
			remove_filter('wp_mail_content_type','church_admin_email_type');
		}
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode( $output);
		die();
}
add_action("wp_ajax_ca_forgotten_password", "ca_forgotten_password");
add_action("wp_ajax_nopriv_ca_forgotten_password", "ca_forgotten_password");


function ca_my_group()
{

	global $wpdb;
	church_admin_app_log_visit( $loginStatus, __('My group','church-admin') );
	$output=array();
	//check token first
	if ( empty( $_GET['token'] ) )
	{
		church_admin_app_log_visit( $loginStatus, esc_html(__('My group','church-admin' ) ),$loginStatus);
		$output=array('error'=>'login required');
		if(defined('CA_DEBUG') )church_admin_debug('No token');

	}
	else
	{

		$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] )) ).'"';

		$userID=$wpdb->get_var( $sql);
		if ( empty( $userID) )
		{

			$output=array('error'=>'login required');
		}
		else
		{
			if ( empty( $_GET['version'] ) )
			{//app version <=2.5

				//get group ID
				$groupID=$wpdb->get_var('SELECT a.ID FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.meta_type="smallgroup" AND b.user_ID="'.(int)$userID.'" and a.people_id=b.people_id');

				if(!empty( $groupID)&&groupID!=1)
				{
						$output=ca_get_group( $groupID);
				}
				else{$output=array('error'=>'No results');}
			}//end of old version
			else
			{
				/********************************************
				*
				*	From app v2.6, look for multiple groups
				*
				*********************************************/
				$groupIDs=$wpdb->get_results('SELECT a.ID FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.meta_type="smallgroup" AND b.user_ID="'.(int)$userID.'" and a.people_id=b.people_id AND a.ID!=1');
				if(defined('CA_DEBUG') )church_admin_debug('Group IDS'.print_r( $groupIDs,TRUE) );
				if(!empty( $groupIDs) )
				{
					$output=array();
					 foreach( $groupIDs AS $groupID)
					 $output[]=ca_get_group( $groupID->ID);
				}
				else
				{//no groups found for user
					$output=array('error'=>'No results');
				}
				if(defined('CA_DEBUG') )church_admin_debug('Output'.print_r( $output,TRUE) );
			}//endapp version>=2.6

		}
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
		die();
}
add_action("wp_ajax_ca_my_group", "ca_my_group");
add_action("wp_ajax_nopriv_ca_my_group", "ca_my_group");
function ca_get_group( $group_id)
{
	global $wpdb;
	//person is in a group
	//get group name
	$groupDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$group_id.'"');
	$output=array();
	$output['group_name']=esc_html( $groupDetails->group_name);
	$output['when_where']=esc_html( $groupDetails->whenwhere.' '.$groupDetails->address);
	$output['group_id']=$groupID->ID;
	//get group members
	$mt=get_option('church_admin_app_member_types');
	if ( empty( $mt) )$mt=array(1);
	foreach( $mt AS $key=>$type)  {$mtsql[]='a.member_type_id="'.(int)$type.'"';}
	$sql='SELECT a.*,b.address,b.phone FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b, '.$wpdb->prefix.'church_admin_people_meta c WHERE ('.implode('||',$mtsql).') AND a.household_id=b.household_id AND a.people_id=c.people_id AND c.meta_type="smallgroup" AND c.ID="'.(int)$group_id.'"  ORDER BY a.last_name,a.people_order,a.first_name';

	$results=$wpdb->get_results( $sql);

	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			if ( empty( $row->phone) )$row->phone='';
			if ( empty( $row->mobile) )$row->mobile='';
			$address=implode(', ',$row->address);
			$output['people'][]=array('id'=>(int)$row->people_id,'first_name'=>esc_html( $row->first_name),'last_name'=>esc_html( $row->last_name),'name'=>esc_html(implode(" ",array_filter(array( $row->first_name,$row->prefix,$row->last_name) )) ),'email'=>esc_html( $row->email),'mobile'=>esc_html( $row->mobile),'phone'=>esc_html( $row->phone),'address'=>esc_html( $row->address),'streetAddress'=>$address[0],'locality'=>$address[1],'region'=>$address[2],'postalCode'=>$address[3] );
		}
	}
	return $output;
}

function ca_which_group()
{
	global $wpdb;
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{

		$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field(stripslashes( $_GET['token'] ) )).'"';

		$userID=$wpdb->get_var( $sql);
		if ( empty( $userID) )
		{

			$output=array('error'=>'login required');
		}
		else
		{
			$peopleID=$wpdb->get_var('SELECT a.people_id FROM '.$wpdb->prefix.'church_admin_people a,'.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token']) ) ).'"');
			$groupID=$wpdb->get_var('SELECT a.ID FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.meta_type="smallgroup" AND b.people_ID="'.(int)$peopleID.'" and a.people_id=b.people_id');
			$groupName=$wpdb->get_var('SELECT group_name FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$groupID.'"');
			$output=array('groupID'=>(int)$groupID,'peopleID'=>(int)$peopleID,'groupName'=>esc_html( $groupName) );
		}
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
		die();
}
add_action("wp_ajax_ca_which_group", "ca_which_group");
add_action("wp_ajax_nopriv_ca_which_group", "ca_which_group");


function ca_bible_readings()
{
	church_admin_debug("ca_bible_readings");
	
	church_admin_app_log_visit( $loginStatus, __('Bible Reading','church-admin') );
	global $wpdb;

	if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus, esc_html(__('Bible Reading','church-admin' ) ),$loginStatus );
	//bible readings ID starts at 1 date('z') returns 0 for Jan 1


	$version=get_option('church_admin_bible_version');
	if(!empty( $_GET['version'] ) )$version=sanitize_text_field(stripslashes($_GET['version']));
	
	$ID=date('z',strtotime('Today') )+1;
	if(!empty( $_GET['date'] ) )
	{
		$ID=date('z',strtotime( sanitize_text_field(stripslashes($_GET['date'] ))) );

	}
	/***************************************************************************
	 * Older versions of app use  date variable, new version uses post ID
	 ***************************************************************************/
	$headphonesSVG='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6 23v-11c-4.036 0-6 2.715-6 5.5 0 2.807 1.995 5.5 6 5.5zm18-5.5c0-2.785-1.964-5.5-6-5.5v11c4.005 0 6-2.693 6-5.5zm-12-13.522c-3.879-.008-6.861 2.349-7.743 6.195-.751.145-1.479.385-2.161.716.629-5.501 4.319-9.889 9.904-9.889 5.589 0 9.29 4.389 9.916 9.896-.684-.334-1.415-.575-2.169-.721-.881-3.85-3.867-6.205-7.747-6.197z" /></svg>';
	//check to see if there is a post in bible-readings for the date

	//grab dates for array for date picker
	church_admin_debug('GET Variables');
	church_admin_debug(print_r( $_GET,TRUE) );
	$datesArray=array();
	$results=$wpdb->get_results('SELECT ID,post_date,post_title FROM `'.$wpdb->posts.'` WHERE post_status="publish" AND post_type="bible-readings" AND post_date>=DATE_SUB(NOW(), INTERVAL 28 DAY) AND post_date<=DATE_ADD(NOW(),INTERVAL 1 DAY) ORDER BY post_date DESC ');
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{

			if(!empty( $ID ) && $row->ID==(int)$ID )
			{
				$selected=1;
			}
			else
			{
				$selected=0;
			}
			$datesArray[]=array('title'=>esc_html( $row->post_title),'date'=>mysql2date('d M',$row->post_date),'ID'=>(int)$row->ID,'selected'=>$selected);
		}

	}
	
	
	//v1.1.0 of the app sends $_GET['date'] to get date, still need to add 1 though!
	//if(!empty( $_GET['date'] ) ) $ID=date('z' , strtotime( $_GET['date'] ) )+1;
	//android sends the date in a way strtotime cannot formatting
	if(!empty( $_GET['date'] ) )
	{
		church_admin_debug("GET date from variable");
		//$d=\DateTime::createFromFormat('Y-m-d',$_GET['date'] );
		//android is a pain! need to only use first15 charas of GET['date']
		$date=substr( sanitize_text_field(stripslashes($_GET['date'])),0,15);
		church_admin_debug("stripped date $date");
		$d = new dateTime( $date);
		church_admin_debug("DateTime object");
		church_admin_debug(print_r( $d,TRUE) );
		$date=$d->format('Y-m-d');
		church_admin_debug("SQL formatted $date");
		$ID=$d->format('z')+1;
	}
	else
	{
		$date=wp_date('Y-m-d');
	}
	if( $date=='1970-01-01')  {$date=wp_date('Y-m-d');}//handle Android notification handler bug
	if(defined('CA_DEBUG') )church_admin_debug('Date looking for : '.$date);
	
	
	//use date if now ID passed
	if(!empty( $ID ) )
	{
		$sql='SELECT * FROM '.$wpdb->posts.' WHERE post_type="bible-readings" AND ID="'.(int)$ID.'"';
	}
	else
	{
		$sql = 'SELECT * FROM '.$wpdb->posts.' WHERE post_type="bible-readings" AND DATE_FORMAT(post_date, "%Y-%m-%d")="'.esc_sql($date).'" AND (post_status="publish" OR post_status="future")';
	}

	church_admin_debug( $sql);
	$out=array();

    


	

	$bible_readings=$wpdb->get_results( $sql);

	if(!empty( $bible_readings) )
	{//use the Bible Reading post type
		foreach( $bible_readings AS $bible_reading)
		{
			$output='<h2 class="ca-bible-reading-title">'.esc_html( $bible_reading->post_title).'</h2>';
			$passage=get_post_meta( $bible_reading->ID ,'bible-passage',TRUE);
			if(!empty( $passage) )
            {
                $output.='<p class="ca-bible-reading"><a href="'.esc_url('https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.urlencode( $version).'&interface=print').'"  >'.esc_html( $passage).'</a></p>';

                $bibleCV=church_admin_bible_audio_link( $passage,$version);
                if(!empty( $bibleCV['url'] ) )$output.='<p class="ca-bible-audio-link"><a href="'.esc_url($bibleCV['url']).'">'.$headphonesSVG.' '.esc_html($bibleCV['linkText']).'</a></p>';
            }
			$output.='<div class="ca-bible-commentary">';
            $blocks = parse_blocks( $bible_reading->post_content);
                foreach ( $blocks as $block) {
					church_admin_debug('*** Handle Block ***');
					church_admin_debug( $block['blockName'] );
                    if ( $block['blockName'] == 'core/embed') {

                        $output.='<p><audio controls><source src="'.esc_url($block['attrs']['url']).'" type="audio/mpeg">'.esc_url($block['attrs']['url']).'</audio></p>';
                    }
                    elseif( $block['blockName']=='core/shortcode')
					{
						church_admin_debug('Shortcode block');
						church_admin_debug(do_shortcode( $block['innerHTML'] ) );
						$output.= do_shortcode( $block['innerHTML'] );
					}
					else $output.= render_block( $block);
                }

            $output.='</div>';
			$output.='<p class="ca-bible-author-meta">'.esc_html(get_the_author_meta( 'display_name',$bible_reading->post_author)).'</p>';
			$out[]=$output;
				
		}
		$reading=$out;
		
	}
	else
	{//use the old style bible reading plan
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_brplan WHERE ID="'.(int)$ID.'"';
		$data=$wpdb->get_row( $sql);
		if(!empty( $_GET['version'] ) )$version=sanitize_text_field(stripslashes($_GET['version']) );
		if ( empty( $version) )$version=get_option('church_admin_bible_version');
		if ( empty( $version) )$version="ESV";
		$readings=maybe_unserialize( $data->readings);
		$date = (!empty( $_GET['date'] )&&church_admin_checkdate( $_GET['date'] ) )?sanitize_text_field(stripslashes($_GET['date'])) :date('Y-m-d');
		$out=array('<h2>'.esc_html(mysql2date(get_option('date_format'),$date)).'</h2>');
		if(!empty( $readings) )
		{
			
			
			foreach( $readings AS $key=>$passage)
			{

                $bibleCV=church_admin_bible_audio_link( $passage,$version);
                $output='<p class="ca-bible-reading"><a href="'.esc_url('https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.urlencode( $version).'&interface=print').'" >'.esc_html( $passage).'</a></p>';


                if(!empty( $bibleCV['url'] ) )$output.='<p><a href="'.esc_url($bibleCV['url']).'">'.$headphonesSVG.' '.esc_html($bibleCV['linkText']).'</a></p>';

                $out[]=$output;
			}
			$reading=$out;
		}else $reading=__('No passages','church-admin');

	}
	if(!empty( $_GET['readingversion'] ) )
	{
		$outputArray=array('reading'=>$reading);
		if(!empty( $datesAttay) )$outputArray['dates']=$datesArray;
	}
	else
	{
		$outputArray=$reading;
	}
	church_admin_debug(json_encode( $outputArray) );
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $outputArray);
	die();

}
add_action("wp_ajax_ca_bible_readings", "ca_bible_readings");
add_action("wp_ajax_nopriv_ca_bible_readings", "ca_bible_readings");


function ca_app_my_rota()
{


	global $wpdb;
	church_admin_app_log_visit( $loginStatus, __('My rota','church-admin') );

	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{
		$people=$wpdb->get_row('SELECT a.first_name,a.prefix,a.last_name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token']) ) ).'"');

		if ( empty( $people->people_id) )
		{
			$output=array('error'=>"Your user identity is not connected to a church user profile.");

		}
		else
		{

			$sql='SELECT a.service_name,c.service_time, b.rota_task,c.rota_date,a.service_id FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_rota_settings b, '.$wpdb->prefix.'church_admin_new_rota c WHERE a.service_id=c.service_id AND c.mtg_type="service" AND c.rota_task_id=b.rota_id AND a.active=1 AND c.people_id="'.(int)$people->people_id.'" AND c.rota_date>=CURDATE() ORDER BY c.rota_date,c.service_time ASC';

			$results=$wpdb->get_results( $sql);
			if(!empty( $results) )
			{
				$task=$output=array();
				foreach( $results AS  $row)
				{

					$service=esc_html( $row->service_name.' '.$row->service_time);
					$date=mysql2date(get_option('date_format'),$row->rota_date);
					$task[$row->rota_date][]=array('date'=>$date,'job'=>esc_html( $row->rota_task).' - '.esc_html( $row->service_name.' '.$row->service_time) );
				}
				foreach( $task AS $date=>$values)$output[]=$values;
			}
			else $output=array('error'=>'no-rota-jobs');

		}
	}



	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();

}
add_action("wp_ajax_ca_my_rota", "ca_app_my_rota");
add_action("wp_ajax_nopriv_ca_my_rota", "ca_app_my_rota");


/*********************************************************
*
*	App Content
*
**********************************************************/

function ca_app_content()
{
		global $wpdb;
		if(!empty( $_GET['page_name'] ) )
		{
			$page_name=sanitize_text_field( stripslashes( $_GET['page_name'] ) ) ;
			$row=$wpdb->get_row('SELECT post_title, post_content FROM '.$wpdb->posts.' WHERE post_status="publish" AND post_type="app-content" AND post_name="'.esc_sql($page_name).'" LIMIT 1');
			if(!empty( $row) )
			{
				church_admin_app_log_visit( $loginStatus,  $row->post_title);
				if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus,  $row->page_title,$loginStatus );
				$content=do_blocks( $row->post_content);
				$content=do_shortcode( $content);
				$output=array('content'=>wp_kses_post($content));
			}
			else{$output=array('content'=>esc_html( __('Nothing here yet','church-admin') ));}
		}
		else{$output=array('content'=>esc_html( __('Nothing here yet','church-admin') ));}
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode( $output);
		die();
}
add_action("wp_ajax_ca_app_content", "ca_app_content");
add_action("wp_ajax_nopriv_ca_app_content", "ca_app_content");

/*********************************************************
*
*	Home

*
**********************************************************/
function ca_home()
{
	global $wpdb;

    if(!church_admin_app_licence_check() )
    {
        $menu='<li id="home-tab-button" class="tab-button" data-tab="#home"> <span class="languagespecificHTML" data-text="home">Home</span></li><li id="logout-tab-button" class="tab-button" data-tab="#logout"  data-tap-toggle="false"></i> <span class="languagespecificHTML" data-text="logout">Logout</span></li>';
        $home='<p>Unfortunately your church is not currently subscribed to the church app, we\'d love to have you back, so please do resubscribe the church at <a href="https://www.churchadminplugin.com/app">https://www.churchadminplugin.com/app</a>, or email <a href="mailto:support@churchadminplugin.com">support@churchadminplugin.com</a> for help</p>';
		$home.='<p>Developer debug information</p>';
		$home.=church_admin_url_check();
		$output=array(
						'menu_title'=>esc_html(__("No app sub yet",'church-admin' ) ),
						'giving'=>esc_html(__("No app sub yet",'church-admin' ) ),
						'groups'=>esc_html(__("No app sub yet",'church-admin' ) ),
						'church_id'=>NULL,
						'menu'=>$menu,
						'style'=>'',
						'home'=>$home,
						'nonce'=>NULL
					);

    }
    else
    {
        $people_id=NULL;
        //church_admin_debug(print_r( $_GET,TRUE) );
        church_admin_app_log_visit( $loginStatus, __('Home','church-admin') );
        if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus, esc_html(__('Home','church-admin' ) ),$loginStatus );
        if(!empty( $_GET['token'] ) )
        {
            $people_id=$wpdb->get_var('SELECT a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token']) ) ).'"');
            if(!empty( $_GET['pushToken'] ) )
            {
              $sql='UPDATE '.$wpdb->prefix.'church_admin_people SET pushToken="'.esc_sql( sanitize_text_field(stripslashes($_GET['pushToken'] )) ).'" WHERE people_id="'.(int)$people_id.'"';
              //church_admin_debug( $sql);
              if(!empty( $people_id) )$wpdb->query( $sql);
            }
        }
        $menu_title=$menu_title=get_option('church_admin_app_menu_title');
        /**********************************************************************
        *
        *	From v2.2520 app content is stored in post type "app-content"
        *
        ***********************************************************************/
        $defaultContentIDs=get_option('church_admin_app_defaults');
        $home=do_blocks(do_shortcode(ca_filter_giving( $wpdb->get_var('SELECT post_content FROM '.$wpdb->posts.' WHERE ID="'.(int)$defaultContentIDs['home'].'"') )) );
        $giving=do_blocks(do_shortcode(ca_filter_giving( $wpdb->get_var('SELECT post_content FROM '.$wpdb->posts.' WHERE ID="'.(int)$defaultContentIDs['giving'].'"') )) );
        require_once(plugin_dir_path(dirname(__FILE__) ).'display/giving.php');
        $groups=do_blocks(do_shortcode(ca_filter_giving( $wpdb->get_var('SELECT post_content FROM '.$wpdb->posts.' WHERE ID="'.(int)$defaultContentIDs['smallgroup'].'"') )) );
        if ( empty( $home) )$home=__('Please login into your Church Admin>App to start setting up the app','church-admin');
        if ( empty( $giving) )$giving=__('Please login into your Church Admin>App to start setting up the app','church-admin');
        if ( empty( $groups) )$groups=__('Please login into your Church Admin>App to start setting up the app','church-admin');
        $addressUpdated=get_option('addressUpdated');
        if ( empty( $addressUpdated) )$addressUpdated=time();
        $style=get_option('church_admin_app_style');
        $church_id=get_option('church_admin_app_id');
        $paypal=get_option('church_admin_payment_gateway');
        $paypalEmail=$paypal['paypal_email'];
        $funds=get_option('church_admin_giving_funds');
        //church_admin_debug(print_r( $funds,TRUE) );
        if( $paypal['gift_aid'] )  {$giftAid=1;}else{$giftAid=0;}
        /**********************
        *
        *   Add paypal giving form
        *
        ***********************/
        if(!empty( $paypal)&&!empty( $paypal['show_in_app'] ) )
        {
            $giving=ca_app_giving();
        }
        //church_admin_debug("people id $people_id");
        $menuOutput=ca_build_menu( $people_id);
        if ( empty( $menuOutput) )
        {
            $menuOutput=array('<li id="home-tab-button" class="tab-button" data-tab="#home"> <span class="languagespecificHTML" data-text="home">Home</span></li>','<li id="logout-tab-button" class="tab-button" data-tab="#logout"  data-tap-toggle="false"></i> <span class="languagespecificHTML" data-text="logout">Logout</span></li>');
        }
        $nonce=wp_create_nonce(site_url().'app');
       $output=array('menu_title'=>esc_html($menu_title),'giving'=>$giving,'groups'=>$groups,'church_id'=>(int)$church_id,'menu'=>implode("\r\n",$menuOutput),'style'=>$style,'home'=>$home,'nonce'=>$nonce,'businessEmail'=>$paypalEmail,'funds'=>$funds,'giftAid'=>$giftAid);
    }
    //church_admin_debug(print_r( $output,TRUE) );
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();


}
add_action("wp_ajax_ca_home", "ca_home");
add_action("wp_ajax_nopriv_ca_home", "ca_home");


function ca_filter_giving( $content)
{
	/*******************************************
	* handle giving shortcode/block in a post
	* as it doesn't look great or work!
	*******************************************/
	$giving=ca_app_giving();
	$content=str_replace('[church_admin type="giving"]',$giving,$content);
	$content=str_replace('<!-- wp:church-admin/giving /-->',$giving,$content);
	$content=str_replace("[church_admin type='giving']",$giving,$content);
	return $content;
}
function ca_app_giving()
{
	$paypal=get_option('church_admin_payment_gateway');
    $paypalEmail=$paypal['paypal_email'];
    $funds=get_option('church_admin_giving_funds');
    //church_admin_debug(print_r( $funds,TRUE) );
    if(!empty( $paypal['gift_aid'] ) )  {$giftAid=1;}else{$giftAid=0;}
	if(!empty( $paypal['currency_code'] ) )  {$currency_code=$paypal['currency_code'];}else{$currency_code="USD";}
	$giving='<h2>'.esc_html( __('Give now with PayPal','church-admin' ) ).'</h2><p>'.esc_html( __('Email','church-admin' ) ).'<br><input id="email" type="email"  autocorrect="off" autocapitalize="none" /></p><p>'.esc_html( __('Amount','church-admin' ) ).'<br><input id="amount" type="text"  autocorrect="off" autocapitalize="none" /></p><p>'.esc_html( __("One off?",'church-admin' ) ).'<input type="radio" class="donation_type"  name="donation_type" value="one-off" checked="checked" /></p><p>'.esc_html( __("Monthly?",'church-admin' ) ).'<input type="radio" class="donation_type" name="donation_type" value="monthly" /></p>';
    if(!empty( $paypal['gift_aid'] ) ) $giving.='<p>Boost your donation by 25p of Gift Aid for every £1 you donate Gift Aid is reclaimed by the charity from the tax you pay for the current tax year.</p><p>I want to gift aid this and all future donations.<input type="checkbox" value="yes" id="gift_aid" /></p>';
    $giving.='<p>'.esc_html( __('Funds','church-admin' ) ).'<select id="fund">';
    foreach( $funds AS $key=>$fund)
    {
        $giving.='<option value="'.esc_attr(urlencode( $fund)).'">'.esc_html( $fund).'</option>';
    }
	$giving.='</select></p>';

	$giving.='<p><button id="donate" class="button red">Donate</button></p><script>
                $("#page #rendered").on("click","#donate",function()  {
                    var churchURL ="'.site_url().'";
                    var gift_aid=$("#gift_aid:checked").val();
                    var amount=$("#amount").val();
                    var email=$("#email").val();
                    var item_name=$("#fund option:selected").val();
                    if(!item_name)var item_name=$("#funded").val();
                    console.log("Item name: "+item_name);
                    var currencyCode="GBP";
                    var donationType=$(".donation_type:checked").val();
                    var url="https://www.paypal.com/cgi-bin/webscr";
                    url = url + "?business='.esc_html($paypalEmail).'";
                    switch(donationType)
                    {
                        case "one-off":
                            url= url + "&cmd=_donations&amount="+amount;
                        break;
                        case "monthly":
                            url=url+ "&cmd=_xclick-subscriptions&p3=1&t3=M&src=1&a3="+amount;
                        break;
                    }
                    if(gift_aid)url=url+"&custom=giftaid";
                    url=url+"&item_name="+encodeURI(item_name);
                    url=url+"&purpose="+encodeURI(item_name);
                    url=url+"&currency_code='.esc_attr($currency_code).'";

                    url=url+"&payer_email="+email;
                    url=url+ "&notify_url='.site_url().'/wp-admin/admin-ajax.php?action=church_admin_paypal_giving_ipn";
                    console.log(url);
                    cordova.InAppBrowser.open(url, "_system", "hidden=yes,location=yes");
                })</script>';

	return $giving;
}

function ca_account()
{
	global $wpdb;
	if(!empty( $_GET['token'] ) ){
		church_admin_app_log_visit( $loginStatus, esc_html(__('Account','church-admin' ) ),$loginStatus);
	}
	church_admin_app_log_visit( $loginStatus, __('Account','church-admin') );
	$output=array();
	//check token first
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{
		
		$people=$wpdb->get_row('SELECT a.first_name,a.prefix,a.last_name, a.people_id,a.household_id,a.attachment_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] )) ).'"');

		if ( empty( $people->people_id) )
		{
			$output=array('error'=>"Your user identity is not connected to a church user profile.");

		}
		else
		{
			//update push Token
			if(!empty( $_REQUEST['pushToken'] ) )
			{
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET pushToken="'.esc_sql(sanitize_text_field( stripslashes( $_REQUEST['pushToken'] ) ) ).'" WHERE people_id="'.(int)$people->people_id.'"');
			}
			$peeps=array();
			$image=null;
			if(!empty( $people->attachment_id) )$image = wp_get_attachment_image( $people->attachment_id,'thumbnail','',array('class'=>'person-image') );
			$peeps[]=array('name'=>esc_html(implode(" ",array_filter(array( $people->first_name,$people->prefix,$people->last_name) )) ),'people_id'=>(int)$people->people_id,'image'=>$image);
			$others=get_results('SELECT first_name,prefix,last_name,people_id,attachment_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$people->household_id.'" AND people_id!="'.(int)$people->people_id.'" ORDER BY people_order ASC');
			if(!empty( $others) )
			{
				foreach( $others AS $other)
				{
					$image=null;
					if(!empty( $other->attachment_id) )$image = wp_get_attachment_image( $other->attachment_id,'thumbnail','',array('class'=>'person-image') );
					$peeps[]=array('name'=>esc_html(implode(" ",array_filter(array( $other->first_name,$other->prefix,$other->last_name) )) ),'people_id'=>(int)$other->people_id,'image'=>$image);

				}
			}

			$address=$wpdb->get_row('SELECT phone, address,lat,lng FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$people->household_id.'"');
			if(!empty( $address) )
			{

                if ( empty( $address->address) )$address->address=__('Please edit to add your address','church-admin');
                if ( empty( $address->phone) )$address->phone=__('No home phone stored','church-admin');
				$add=array('address'=>esc_html( $address->address),'lat'=>esc_html( $address->lat),'lng'=>esc_html( $address->lng),'phone'=>esc_html( $address->phone),'household_id'=>(int)$people->household_id);
			}else{$add=array();}

			$output=array('people'=>$peeps,'address'=>$add);
		}
	}

	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}

add_action("wp_ajax_ca_account", "ca_account");
add_action("wp_ajax_nopriv_ca_account", "ca_account");



function ca_people_edit()
{
	church_admin_app_log_visit( $loginStatus, __('People edit','church-admin') );
   // church_admin_debug(print_r( $_REQUEST,TRUE) );
	global $wpdb;
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{
		if ( empty( $_REQUEST['people_id'] )||$_REQUEST['people_id']==0)  {$output=array('first_name'=>'','last_name'=>'','mobile'=>'','email'=>'');}
		else
        {
			$people_id=sanitize_text_field(stripslashes($_GET['people_id']));
            $output=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"', ARRAY_A);
            //fix permissions not sent with integer variable type for JSON!
            $output['photo_permission']=(int)$output['photo_permission'];
            $output['email_send']=(int)$output['email_send'];
			$output['news_send']=(int)$output['news_send'];
            $output['mail_send']=(int)$output['mail_send'];
            $output['sms_send']=(int)$output['sms_send'];
            $output['phone_calls']=(int)$output['phone_calls'];
            $output['people_id']=(int)$output['people_id'];
			$output['show_me']=(int)$output['show_me'];
            $bible_reading=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="bible-readings"');
            if(!empty( $bible_reading) )  {$output['bible_readings']=1;}else{$output['bible_readings']=0;}
            $prayer_requests=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="prayer-requests"');
            if(!empty( $prayer_requests) )  {$output['prayer_requests']=1;}else{$output['prayer_requests']=0;}

        }
	}
    //church_admin_debug("People edit form data sent to app".print_r( $output,TRUE) );
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_people_edit", "ca_people_edit");
add_action("wp_ajax_nopriv_ca_people_edit", "ca_people_edit");

function ca_address_edit()
{

	global $wpdb;
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{
		$household_id=$wpdb->get_var('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"');
		if( $household_id==(int)$_GET['household_id'] )
		{
			$output=$wpdb->get_row('SELECT address,phone FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"', ARRAY_A);

		}
		else
		{
			$output=array('error'=>'login required');
		}
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_address_edit", "ca_address_edit");
add_action("wp_ajax_nopriv_ca_address_edit", "ca_address_edit");

function ca_save_address_edit()
{
	global $wpdb;
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{
		$household_id=$wpdb->get_var('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"');

		if( $household_id==(int)$_GET['household_id'] )
		{
			$phone=!empty($_GET['phone'])? sanitize_text_field(stripslashes( $_GET['phone']) ):null;
			$address=!empty($_GET['address'])? sanitize_text_field(stripslashes( $_GET['address']) ):null;

			$data=array('address'=>$address,'phone'=>$phone,'geocoded'=>0);
			$wpdb->update($wpdb->prefix.'church_admin_household',$data,array('household_id'=>(int)$household_id) );
			$output=array('error'=>'success');

		}
		else{
			$output=array('error'=>'login required');

		}


	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_save_address_edit", "ca_save_address_edit");
add_action("wp_ajax_nopriv_ca_save_address_edit", "ca_save_address_edit");

function ca_save_people_edit()
{
	if(defined('CA_DEBUG') )church_admin_debug("***************************\r\nca-save_people_edit");
    global $wpdb;
	if ( empty( $_REQUEST['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{
        if(defined('CA_DEBUG') )church_admin_debug(print_r( $_REQUEST,TRUE) );
        $household_id=$wpdb->get_var('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_REQUEST['token'] ) ) ).'"');
		$people_id=!empty($_REQUEST['people_id'])? sanitize_text_field(stripslashes( $_REQUEST['people_id']) ):null;
		if(!empty( $people_id ) && church_admin_int_check($people_id) )
		{
			$old_email=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		}
		else
		{
			$old_email=NULL;
		}
        if(defined('CA_DEBUG') )church_admin_debug("household_id $household_id");
		$form_dob = !empty( $_REQUEST['date_of_birth'] )?sanitize_text_field(stripslashes($_GET['date_of_birth'] )):null;
		if(!$form_dob )
        {
            $dob= new DateTime($form_dob);
            $date_of_birth=$dob->format('Y-m-d');
        }
        if(!empty( $_REQUEST['show_me'] ) )  {$show_me=1;}else{$show_me=0;}
        if(!empty( $_REQUEST['email_send'] ) )  {$email_send=1;}else{$email_send=0;}
       if(!empty( $_REQUEST['photo_permission'] ) )  {$photo_permission=1;}else{$photo_permission=0;}
       if(!empty( $_REQUEST['prayer_requests'] ) )  {$prayer_requests=1;}else{$prayer_requests=0;}
        if(!empty( $_REQUEST['bible_readings'] ) )  {$bible_readings=1;}else{$bible_readings=0;}
       if(!empty( $_REQUEST['blog_posts'] ) )  {$news_send=1;}else{$news_send=0;}
       if(!empty( $_REQUEST['mail_send'] ) )  {$mail_send=1;}else{$mail_send=0;}
       if(!empty( $_REQUEST['sms_send'] ) )  {$sms_send=1;}else{$sms_send=0;}
        if(!empty( $_REQUEST['phone_calls'] ) )  {$phone_calls=1;}else{$phone_calls=0;}
        if(!empty( $_REQUEST['mobile'] ) )$e164=church_admin_e164( sanitize_text_field(stripslashes($_REQUEST['mobile'] )));
        $data=array('first_name'=>sanitize_text_field( stripslashes( $_REQUEST['first_name']) ),'last_name'=>sanitize_text_field( stripslashes($_REQUEST['last_name']) ),'mobile'=>sanitize_text_field( $_REQUEST['mobile'] ),'email'=>sanitize_text_field( $_REQUEST['email'] ),'email_send'=>$email_send,'news_send'=>$news_send,'photo_permission'=>$photo_permission,'phone_calls'=>$phone_calls,'sms_send'=>$sms_send,'mail_send'=>$mail_send,'show_me'=>$show_me);
        if(!empty( $e164) )$data['e164']=$e164;
		if(!empty( $date_of_birth) )$data['date_of_birth']=$date_of_birth;
        if(defined('CA_DEBUG') )church_admin_debug("data".print_r( $data,TRUE) );
		if( $household_id && $people_id==0)
		{//new person
			$data['household_id']=(int)$household_id;
			$wpdb->insert($wpdb->prefix.'church_admin_people',$data);
            $people_id=$wpdb->insert_id;
            church_admin_debug('INSERT '.$people_id);
		}
		elseif( $household_id)
		{
			$check=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'" AND household_id="'.(int)$household_id.'"');
			if( $check)
			{

				$updated=$wpdb->update($wpdb->prefix.'church_admin_people',$data,array('people_id'=>(int)$people_id ) );
                $people_id=$check;
                church_admin_debug('Rows updated '.$updated);
			}
		}
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="bible-readings" AND people_id="'.(int)$people_id.'"');
        if( $bible_readings)
        {
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,people_id,meta_date,ID) VALUES("bible-readings","'.(int)$people_id.'","'.esc_sql(wp_date('Y-m-d')).'","1")');
        }

        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="prayer-requests" AND people_id="'.(int)$people_id.'"');
        if(!empty( $prayer_requests) )
        {
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,people_id,meta_date,ID) VALUES("prayer-requests","'.(int)$people_id.'","'.esc_sql(wp_date('Y-m-d')).'","1")');
        }
        if ( empty( $privacy) )
        {
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET privacy=0 WHERE household_id="'.(int)$household_id.'"');
        }else{$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET privacy=1 WHERE household_id="'.(int)$household_id.'"');}
		$output=array('error'=>'success');
		
		$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		$email_method=get_option('church_admin_email_method');
		
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_save_people_edit", "ca_save_people_edit");
add_action("wp_ajax_nopriv_ca_save_people_edit", "ca_save_people_edit");

function ca_delete_people()
{
	global $wpdb;
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');

	}
	else
	{
		$household_id=$wpdb->get_var('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"');

		$check=$wpdb->get_row('SELECT people_id, user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$_GET['people_id'].'" AND household_id="'.(int)$household_id.'"');
		if( $check)
		{
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$_GET['people_id'].'"');
			
			//delete token, so login expires
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_app WHERE people_id="'.(int)$_GET['people_id'].'" AND UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"');
			$output=array('error'=>'success');
		}else{$output=array('error'=>'no one found to delete');}
	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_delete_people", "ca_delete_people");
add_action("wp_ajax_nopriv_ca_delete_people", "ca_delete_people");

function ca_send_prayer_request()
{
		global $wpdb;
        if(defined("CA_DEBUG") )church_admin_debug("**********************r\nAPP prayer request");
		if(!empty( $_GET['token'] ) )
		{
			$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"';
			$user_id=$wpdb->get_var( $sql);
		}
        if(defined("CA_DEBUG") )church_admin_debug(print_r( $_REQUEST,TRUE) );


		$title=!empty($_GET['prayer_title'])?sanitize_text_field(stripslashes( $_GET['prayer_title'] )):null;
		$content=!empty($_GET['content'])?sanitize_textarea_field( stripslashes($_GET['content'] )):null;
		if(empty($title) || empty($content)){exit();}
		$args=array('post_content'=> $content,'post_title'=>wp_strip_all_tags( $title),'post_status'=>'draft','post_type'=>'prayer-requests');
		//if(user_can( $user_id, 'manage_options' ) )$args['post_status']='publish';
        if(!empty( $user_id)&&church_admin_level_check('Prayer',$user_id) )
        {
            if(defined("CA_DEBUG") )church_admin_debug("User doesnt need moderation $user_id");
            $args['post_status']='publish';
        }
		if(!empty( $user_id) )$args['post-author']=$user_id;
		$post_id = wp_insert_post( $args);

		if(!is_wp_error( $post_id) )  {
  			//the post is valid

  			if(!user_can( $user_id, 'manage_options' ) )
            {
                $prm= get_option('prayer-request-moderation');
                if(!empty( $prm) )$prm=get_option('church_admin_default_from_email');
                //wp_mail( $prm,esc_html(__('Prayer Request Draft','church-admin' ) ),esc_html(__('A draft prayer request has been posted. Please moderate','church-admin') ));
				church_admin_email_send($prm,__('Prayer Request Draft','church-admin' ),__('A draft prayer request has been posted. Please moderate','church-admin'),null,null,null,null,null,TRUE);
            }
		}else{
  			//there was an error in the post insertion,

		}
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode(array('done') );
		die();
}
add_action("wp_ajax_ca_send_prayer_request", "ca_send_prayer_request");
add_action("wp_ajax_nopriv_ca_send_prayer_request", "ca_send_prayer_request");

function ca_classes()
{
	global $wpdb;
	church_admin_app_log_visit( $loginStatus, __('Classes','church-admin') );
	$output=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE end_date >= CURDATE() ';
	//$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE next_start_date >= CURDATE() ORDER BY next_start_date,start_time';

	$classes=$wpdb->get_results( $sql);

	if ( empty( $classes) )  {$output['error']='No classes yet';}
	else
	{



        $students=array();
		foreach( $classes AS $class)
		{
			$household=array();
            if(!empty( $_REQUEST['token'] ) )
            {
                $household_id=$wpdb->get_var('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"');
                if(!empty( $household_id) )$people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'"');
                if(!empty( $people) )
                {
                    foreach( $people AS $person)
                    {
                        $checked=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$person->people_id.'" AND meta_type="class" AND ID="'.(int)$class->class_id.'"');
                        if(!empty( $checked) )  {$check=1;}else{$check=0;}
                        $household[]=array('checked'=>$check,'id'=>$person->people_id,'name'=>implode(" ",array_filter(array( $person->first_name,$person->prefix,$person->last_name) )) );
                    }
                }
            }
            //get date
			$allDates=array();

			$datesResults=$wpdb->get_results('SELECT start_date FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$class->event_id.'" ORDER BY start_date ASC');
			if(!empty( $datesResults) )
			{
				foreach( $datesResults As $datesRow)
				{
					$allDates[]=mysql2date(get_option('date_format'),$datesRow->start_date);
				}
			}

			//add checkin for leaders
			if(!empty( $_GET['token'] ) )
			{
				//logged in
				$sql='SELECT a.people_id,a.user_id,a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] ) )).'"';
				if(defined('CA_DEBUG') )church_admin_debug( $sql);
				$people=$wpdb->get_row( $sql);
				$user_id=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] )) ).'"');

				if(!empty( $people)||!empty( $user_id) )
				{

					//user in directory

					if(in_array( $people->people_id,maybe_unserialize( $class->leadership) )|| user_can( $people->user_id, 'manage_options' )||user_can( $user_id,'manage_options') )
					{

						//user is leader so give array of students
							$students=array();
							$people_result=church_admin_people_meta( $class->class_id,NULL,'class');
							if(!empty( $people_result) )
							{//people are booked in for class, so can check them in
								foreach( $people_result AS $data)
								{
									$name=implode(" ",array_filter(array( $data->first_name,$data->prefix,$data->last_name) ));
									$students[]=array('people_id'=>(int)$data->people_id,'name'=>esc_html( $name) );

								}
							}
							$bookin=FALSE;
							$family=FALSE;

					}
					else {
						$sql='SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)
						$people->people_id.'" AND meta_type="class" AND ID="'.(int)$class->class_id.'"';

						$check=$wpdb->get_var( $sql);
						// opportunity to book in
						if(!$check)$bookin=TRUE;

						$family=array();
						$sql='SELECT first_name,prefix,last_name,people_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$people->household_id.'"  ORDER BY people_order ASC';
						if(defined('CA_DEBUG') )church_admin_debug( $sql);
						$people=$wpdb->get_results( $sql);
						if(!empty( $people) )
						{
							foreach( $people AS $person)
                            {

                                $family[]=array('name'=>esc_html(implode(" ",array_filter(array( $person->first_name,$person->prefix,$person->last_name) )) ),'people_id'=>(int)$person->people_id);
                            }
						}
					}
				}
			}
            switch( $class->recurring)
            {
                case'1':
					$dates=esc_html(sprintf(__('From %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$class->start_time),mysql2date(get_option('time_format'),$class->end_time)) );
					break;
                case'7':
					$dates=esc_html(sprintf(__('Weekly from %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$class->start_time),mysql2date(get_option('time_format'),$class->end_time) ));
				break;
                case'14':
					$dates=esc_html(sprintf(__('Fortnightly from %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$class->start_time),mysql2date(get_option('time_format'),$class->end_time) )); 
				break;
                case 'm':
					$dates=esc_html(sprintf(__('Monthly from %1$s to %2$s','church-admin' ),mysql2date(get_option('time_format'),$class->start_time),mysql2date(get_option('time_format'),$class->end_time) )); 
				break;
                case 'a':$dates=esc_html(sprintf(__('Annually from %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$class->start_time),mysql2date(get_option('time_format'),$class->end_time) )); break;

            }
			$output[]=array(	'class_id'       =>	(int)$class->class_id,
												'date'			=>  mysql2date(get_option('date_format'),$class->next_start_date),
												'sqldate'		=>  esc_html( $class->next_start_date),
												'name'			=>	esc_html( $class->name),
												'description'	=>	wp_kses_post( $class->description),
												'dates'			=>	esc_html($dates),
												'times'			=>	'',//mysql2date(get_option('time_format'),$class->start_time).' - '.mysql2date(get_option('time_format'),$class->end_time),
												'students'		=> 	esc_html($students),
												'bookin' =>$bookin,
												'people'=>$family,
												'all_dates'		=> $allDates,
                                                'household'=>$household
											);

		}

	}

	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_classes", "ca_classes");
add_action("wp_ajax_nopriv_ca_classes", "ca_classes");


function ca_class_checkin()
{
	global $wpdb;
	$class_id = !empty($_GET['class_id'])?sanitize_text_field(stripslashes($_GET['id'])):null;
	$formdate = !empty($_GET['date'])?sanitize_text_field(stripslashes($_GET['date'])):null;
	if(empty($class_id)){exit();}
	if(empty($formdate)){exit();}

	$date=new DateTime( $formdate);


	if(empty($class_id) ||!church_admin_int_check($class_id)){exit();}
	$class=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"');
	
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');
	}
	else
	{


		$people=$wpdb->get_row('SELECT a.people_id,a.user_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"');
		$user_id=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token']) ) ).'"');
		if(!empty( $people)||!empty( $user_id) )
		{
			//user in directory or an admin
			if(in_array( $people->people_id,maybe_unserialize( $class->leadership) )|| user_can( $people->user_id, 'manage_options' )||user_can( $user_id,'manage_options') )
			{

				$class_id=(int)$_GET['class_id'];
				$adults=$child=0;
				
				if(defined('CA_DEBUG') )church_admin_debug(print_r( $date,TRUE) );
				$people_ids = !empty($_GET['people_id'])?church_admin_sanitize($_GET['people_id']):array();

				foreach( $people_ids AS $key=>$people_id)
				{
					if(!church_admin_int_check($people_id)){continue;}
					$check=$wpdb->get_var('SELECT attendance_id FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE date="'.esc_sql( $date->format('Y-m-d') ).'" AND people_id="'.(int)$people_id.'" AND meeting_type="class" AND meeting_id="'.(int)$class_id.'"');
					if ( empty( $check) )
					{
						$sql=	'INSERT INTO '.$wpdb->prefix.'church_admin_individual_attendance (`date`,people_id,meeting_type,meeting_id) VALUES ("'.esc_sql( $date->format('Y-m-d') ).'","'.(int)$people_id.'","class","'.(int)$class_id.'")';
						$wpdb->query( $sql);
						if(defined('CA_DEBUG') )church_admin_debug( $sql);
						//check people type
						$sql='SELECT people_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"';
						$person_type=$wpdb->get_var( $sql);
						switch( $person_type)
						{
							case 1:$adult++;break;
							case 2:$child++;break;
							case 3:$child++;break;
						}
					}
				}
				if(!empty( $adult)||!empty( $child) )
				{
					$entered_date = !empty($_GET['date']) ? sanitize_text_field( stripslashes($_GET['date']) ):null;
					if(!empty($entered_date) && church_admin_checkdate($entered_date)){

						$sql='INSERT INTO '.$wpdb->prefix.'church_admin_attendance (`date`,adults,children,service_id,mtg_type) VALUES ("'.esc_sql( $entered_date ).'","'.(int)$adult.'","'.(int)	$child.'","'.(int)$class_id.'","class")';
						$wpdb->query( $sql);
						church_admin_refresh_rolling_average();
					}
				}
				if(defined('CA_DEBUG') )church_admin_debug(print_r( $date,TRUE) );
				$name=$wpdb->get_var('SELECT name FROM '.$wpdb->prefix.'church_admin_classes' .' WHERE class_id="'.(int)$class_id.'"');
				$output=array('success'=>"true",'class_name'=>esc_html( $name),'date'=>mysql2date(get_option('date_format'),$date->format('Y-m-d') ));
			}
		}
		else{$output=array('error'=>'login required');}

	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}

add_action("wp_ajax_ca_class_checkin", "ca_class_checkin");
add_action("wp_ajax_nopriv_ca_class_checkin", "ca_class_checkin");

function ca_class_book()
{
    //church_admin_debug("*******************\r\n Class Booking");
    //church_admin_debug(print_r( $_REQUEST,TRUE) );
    global $wpdb;
    if ( empty( $_REQUEST['token'] ) )
	{
		$output=array('error'=>'login required');
	}
	else
	{
		$appUser=$wpdb->get_row('SELECT a.people_id,a.user_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_REQUEST['token'] ) ) ).'"');
        //church_admin_debug('User');
        //church_admin_debug(print_r( $appUser,TRUE) );
        if(!empty( $appUser)||!empty( $user_id) )
        {
            $class_id= !empty($_REQUEST['class_id'])?sanitize_text_field(stripslashes($_REQUEST['class_id'])):null;
            if ( empty( $class_id) ||!church_admin_int_check($class_id) )
            {
                $output=array('error'=>esc_html( __('No class selected','church-admin') ));
            }
            else
            {
				$people_ids = !empty($_GET['people_id'])?church_admin_sanitize($_GET['people_id']):array();
                foreach( $people_ids AS $key=>$people_id)
                {
					if(!church_admin_int_check($people_id)){continue;}
                    church_admin_update_people_meta( $class_id,(int)$people_id,'class',NULL);
                }
                $output=array('success'=>esc_html( __('All booked in','church-admin') ));
            }

        }
    }
    //church_admin_debug( $output);
    header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();
}
add_action("wp_ajax_ca_class_book", "ca_class_book");
add_action("wp_ajax_nopriv_ca_class_book", "ca_class_book");

/**
 *
 * Returns array of events to checkin to today
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_now()
{
	global $wpdb;
	if(defined('CA_DEBUG') )church_admin_debug('ca_now function');
	if ( empty( $_GET['token'] ) )
	{
		$output=array('error'=>'login required');
	}
	else
	{
		$sql='SELECT a.people_id,a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"';
		if(defined('CA_DEBUG') )church_admin_debug( $sql);
		$people=$wpdb->get_row( $sql);
		if(defined('CA_DEBUG') )church_admin_debug("People \r\n".print_r( $people,TRUE) );
		$events=array();
		$day=idate('w');
		$household=array();
		$family=$wpdb->get_results('SELECT people_id,first_name,prefix,last_name  FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$people->household_id.'"');
		if(!empty( $family) )
		{
			foreach( $family as $person)
			{
				$household[]=array('people_id'=>(int)$person->people_id,'name'=>esc_html(implode(" ",array_filter(array( $person->first_name,$person->prefix,$person->last_name) )) ));
			}
		}
		//look for service now
		$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_day="'.esc_sql( $day).'"');
		if(!empty( $services) )
		{
			foreach( $services AS $service)
			{
				$events[]=array('type'=>'service','id'=>esc_html( $service->service_id),'name'=>esc_html( $service->service_name) );
			}
		}
		//look for right small group
		$sql='SELECT a.leadership,a.group_name,a.ID FROM '.$wpdb->prefix.'church_admin_smallgroup a, '.$wpdb->prefix.'church_admin_people_meta' .' b WHERE a.id=b.ID AND b.people_id="'.(int)$people->people_id.'" AND b.meta_type="smallgroup" AND a.group_day="'.(int)$day.'"';
		//if(defined('CA_DEBUG') )church_admin_debug("small group:\r\n".$sql);
		$group=$wpdb->get_row( $sql);
		$leaders=maybe_unserialize( $group->leadership);
		//if(defined('CA_DEBUG') )church_admin_debug("Leaders:\r\n".print_r( $leaders,TRUE) );
		if(is_array( $leaders) )
		{
			$getGroup=FALSE;
			//Go through hierarchy to see if people_id is in leadership of group or oversight
			foreach( $leaders AS $key=>$ldrs)
			{
				if(is_array( $ldrs)&& in_array( $people->people_id,$ldrs) )$getGroup=TRUE;
			}
			if( $getGroup)
			{
				$sql='SELECT a.first_name,a.prefix,a.last_name,a.people_id FROM '.$wpdb->prefix.'church_admin_people a ,'.$wpdb->prefix.'church_admin_people_meta b where a.people_id=b.people_id AND  b.meta_type="smallgroup" AND b.ID="'.esc_sql( $group->ID).'" AND a.people_id!="'.(int)$people->people_id.'" ORDER BY a.last_name ';
				if(defined('CA_DEBUG') )church_admin_debug("Get Group:\r\n".$sql);
				$result=$wpdb->get_results( $sql);
				if(!empty( $result) )
				{
					$people=array();
					foreach( $result AS $row)$currentPeople[]=array('people_id'=>(int)$row->people_id,'name'=>esc_html(implode(" ",array_filter(array( $row->first_name,$row->prefix,$row->last_name) )) ));
				}
				if(defined('CA_DEBUG') )church_admin_debug("Household before:\r\n".print_r( $household,TRUE) );
				if(!empty( $currentPeople) )$household=array_merge( $household,$currentPeople);
				if(defined('CA_DEBUG') )church_admin_debug("Household after:\r\n".print_r( $household,TRUE) );
			}
		}
		if(!empty( $group) )$events[]=array('type'=>'smallgroup','id'=>esc_html( $group->ID),'name'=>esc_html( $group->group_name) );
		//look for Classes
		$sql='SELECT a.name,a.class_id FROM '.$wpdb->prefix.'church_admin_classes a, '.$wpdb->prefix.'church_admin_people_meta' .' b WHERE a.class_id=b.ID AND b.people_id="'.(int)$people->people_id.'" AND b.meta_type="class" AND a.next_start_date="'.esc_sql( wp_date('Y-m-d') ) .'"';
		if(defined('CA_DEBUG') )church_admin_debug("Classes:\r\n".$sql);
		$classes=$wpdb->get_results( $sql);
		if(!empty( $classes) )
		{
			foreach( $classes AS $class)
			{
				$events[]=array('type'=>'class','id'=>esc_html( $class->class_id),'name'=>esc_html( $class->name) );
			}
		}

		if ( empty( $events) )$output=array('error'=>esc_html( __('There is nothing to check in to today','church-admin') ));
		else $output=array('events'=>$events,'people'=>$household,'date'=>date('Y-m-d') );

	}
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);
	die();

}
add_action("wp_ajax_ca_now", "ca_now");
add_action("wp_ajax_nopriv_ca_now", "ca_now");


function ca_checkin_sent()
{
		if(defined('CA_DEBUG') )church_admin_debug('ca_checkin_sent function');
		if(defined('CA_DEBUG') )church_admin_debug('$_GET array'."\r\n".print_r( $_GET,TRUE) );
		global $wpdb;
		if ( empty( $_GET['token'] ) )
		{

			$output=array('error'=>'login required');

		}
		else
		{
				$id=!empty($_GET['class_id'])?sanitize_text_field(stripslashes($_GET['class_id'])):null;
				$date=!empty($_GET['class_id'])?sanitize_text_field(stripslashes($_GET['date'])):null;

				$what='';
				switch( $_GET['what'] )
				{
						case 'Service' 	: 	$what='service';		break;
						case 'Group' 		: 	$what='smallgroup';	break;
						case 'Class'		: 	$what='class';			break;
				}
		}
		if(defined('CA_DEBUG') )church_admin_debug("What: $what");
		$people_id=array();
		$people_ids=!empty($_GET['people_id'])?church_admin_sanitize($_GET['people_id']):null;
		$loggedin_people_id=$wpdb->get_var('SELECT a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id and b.UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"');
		if(defined('CA_DEBUG') )church_admin_debug("Logged in people id: $loggedin_people_id");
		if(!empty( $id) && !empty( $what) &&!empty( $people_ids) &&!empty( $loggedin_people_id)&&in_array( $loggedin_people_id,$people_ids) )
		{
			if(defined('CA_DEBUG') )church_admin_debug('Checks passed');
			foreach( $people_ids AS $key=>$peep)
			{
				//individual attendance
				$people_type_id=$wpdb->get_var('SELECT people_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$peep.'"');
				if(!empty( $people_type_id) )
				{

					switch( $people_type_id)
					{
						case 1:$which='adults=adults+1'; $v='"1","0"';break;
						case 2:$which='children=children+1'; $v='"0","1"';break;
						default:$which='adults=adults+1'; $v='"1","0"';break;
					}

					$check=$wpdb->get_var('SELECT attendance_id FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE people_id="'.(int)$peep.'" AND meeting_type="'.esc_sql( $what).'" AND meeting_id="'.(int)$id.'" AND `date`="'.esc_sql( $date).'"');
					if ( empty( $check) )
					{
							$sql='INSERT '.$wpdb->prefix.'church_admin_individual_attendance (people_id,meeting_type,meeting_id,`date`) VALUES("'.(int)$peep.'","'.esc_sql( $what).'","'.(int)$id.'","'.esc_sql( $date).'")';
							if(defined('CA_DEBUG') )church_admin_debug( $sql);
							$wpdb->query( $sql);
							//main attendance
							$sql='SELECT attendance_id FROM '.$wpdb->prefix.'church_admin_attendance WHERE mtg_type="'.esc_sql( $what).'" AND service_id="'.(int)$id.'" AND `date`="'.esc_sql( $date).'"';
							if(defined('CA_DEBUG') )church_admin_debug( $sql);
							$check=$wpdb->get_var( $sql);
							if(!empty( $check) )
							{
								$sql='UPDATE '.$wpdb->prefix.'church_admin_attendance SET '.$which.' WHERE mtg_type="'.esc_sql( $what).'" AND service_id="'.(int)$id.'" AND `date`="'.esc_sql( $date).'"';
								if(defined('CA_DEBUG') )church_admin_debug( $sql);
								$wpdb->query( $sql);
							}
							else {
								$sql='INSERT INTO '.$wpdb->prefix.'church_admin_attendance (adults,children,mtg_type,service_id,`date`) VALUES ('.$v.',"'.esc_sql( $what).'","'.(int)$id.'","'.esc_sql( $date).'")';
								if(defined('CA_DEBUG') )church_admin_debug( $sql);
								$wpdb->query( $sql);
							}

					}
				}
			}
			$output=array('success'=>'Success');
		}
		else {
			$output=array('error'=>"Empty");
		}
		if(defined('CA_DEBUG') )church_admin_debug(print_r( $output,TRUE) );
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode( $output);
		die();

}
add_action("wp_ajax_ca_checkin_send", "ca_checkin_sent");
add_action("wp_ajax_nopriv_ca_checkin_send", "ca_checkin_sent");


/**************************************************************
*
* Acts of courage
*
***************************************************************/
/**
 *
 * Returns acts of courage
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function ca_acts_of_courage()
{
	global $wpdb;
	if(!empty( $_GET['token'] ) )church_admin_app_log_visit( $loginStatus, esc_html(__('Acts of Courage','church-admin' ) ),$loginStatus);
	church_admin_app_log_visit( $loginStatus, __('Acts of Courage','church-admin') );
	$private=get_option('church-admin-private-acts-of-courage');
	if( $private)
	{

		if ( empty( $_GET['token'] ) )
		{//private but no token
			$output=array('error'=>'login required');

		}
		else
		{//private and check token
			$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] ) )).'"';
			$result=$wpdb->get_var( $sql);
			if ( empty( $result) )
			{//private and no login

				$output=array('error'=>'login required');
			}
			else
			{//private and logged in
				$output=ca_acts();
			}
		}
	}
	else
	{
			//not private
			$output=ca_acts();
	}

	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode( $output);

	die();
}

function ca_acts()
{
	global $wpdb;
	$posts_array = array();

	$args = array("post_type" => "acts-of-courage", "orderby" => "date", "order" => "DESC", "post_status" => "publish");

	$posts = new WP_Query( $args);

	if( $posts->have_posts() ):
		while( $posts->have_posts() ):
			$posts->the_post();
      		$content = get_the_content();

			$content = '<div>'.$content.'</div>';
			$content= do_shortcode( $content);
		
		$author=get_the_author_meta('display_name');
      	$post_array = array('title'=>get_the_title(),'content'=>wp_kses_post($content),'date'=> get_the_date(),'ID'=>get_the_ID(),'author'=>esc_html($author));
      array_push( $posts_array, $post_array);

		endwhile;
		else:
        	 return array('error'=>'no-acts-of-courage');

	endif;
	return( $posts_array);


}
/******************
*
* Address List
*
*******************/
add_action("wp_ajax_ca_address_list", "ca_address_list");
add_action("wp_ajax_nopriv_ca_address_list", "ca_address_list");
function ca_address_list()
{
    global $wpdb;
	header('Access-Control-Max-Age: 1728000');
    header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
    if ( empty( $_GET['token'] ) )
    {// no token
		echo json_encode(array('error'=>'login required') );
		exit();
	}
	else
	{//check token
		$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] )) ).'"';
		$userID=$wpdb->get_var( $sql);
		church_admin_debug("User id: $userID");
		if ( empty( $userID) )
        {// no login
				church_admin_debug("App address list - login required");
				echo json_encode(array('error'=>'login required') );
				exit();
		}
        else
        {
			$person=$wpdb->get_row('SELECT * FROM '. $wpdb->prefix.'church_admin_people'.' WHERE user_id="'.(int)$userID.'"');
			church_admin_debug(print_r( $person,TRUE) );
			//Get ordered results
		    $mt=get_option('church_admin_app_member_types');
			church_admin_debug("Member types");
			church_admin_debug(print_r( $mt,TRUE) );
			//reject if wrong member type
			if(!user_can( $userID,'manage_options') && !in_array( $person->member_type_id,$mt) )
			{
				church_admin_debug("App address list - wrong member type");
				echo json_encode( array('address_list'=>'<p>'.esc_html( __("Unfortunately you can't access the directory list",'church-admin' ) ).'</p>') );
				exit();
			}
			//reject if restricted access
			$restrictedList=get_option('church-admin-restricted-access');
      		if(!user_can( $userID,'manage_options') && is_array( $restrictedList)&&in_array( $person->people_id,$restrictedList) )
			{ 
				church_admin_debug("App address list - user on restricted list");
				echo json_encode( array('address_list'=>'<p>'.esc_html( __("Unfortunately you can't access the directory list",'church-admin' ) ).'</p>') );
				exit();
			}
			//output var
			$add='';
			$admin=FALSE;
			if(church_admin_level_check('Directory',$userID) )
			{
				$admin=TRUE;
				$add.='<li class="addItem" style="padding:.7em 1em;"><button id="add-directory" class="button green ">'.esc_html( __('Add directory entry','church-admin' ) ).'</button></li>';
			}
			if ( empty( $mt) )$mt=array(1);
			foreach( $mt AS $key=>$type)  {$mtsql[]='member_type_id='.(int)$type;}
            if(!empty( $mtsql) )  {$membSQL=' AND ('.implode(' OR ',$mtsql).' ) ';}else{$membSQL='';}
            $orderSQL=' ORDER BY last_name ASC ';
            $sql='SELECT DISTINCT household_id, last_name FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND show_me=1 AND active=1 '.esc_sql($membSQL.$orderSQL);
            church_admin_debug( $sql);
            $results=$wpdb->get_results( $sql);
            //church_admin_debug(print_r( $results,TRUE) );
            if(!empty( $results) )
            {
                $address=array();
                foreach( $results AS $ordered_row)
                {
					$add='';
                    //church_admin_debug('Processing household_id: '.$ordered_row->household_id);
                     $addressLine=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE  household_id="'.(int)$ordered_row->household_id.'"');

                        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$ordered_row->household_id .'"  AND show_me=1 AND active="1" ORDER BY people_order ASC, people_type_id ASC,sex DESC';
                          //church_admin_debug(print_r( $addressLine) );
                        $people_results=$wpdb->get_results( $sql);

                        $first_names=$adults=$children=$emails=$mobiles=$photos=array();
                        $last_name='';
                        $x=0;
                          $photo=FALSE;
                        foreach( $people_results AS $person)
                        {
                            //church_admin_debug('Processing people_id: '.$person->people_id. ' '.$person->first_name.' '.$person_last_name);
                            //build first part of name
                            if( $person->photo_permission)$photo=TRUE;
                            $name=$person->first_name.' ';
                            $middle_name=get_option('church_admin_use_middle_name');
                            if(!empty( $middle_name)&&!empty( $person->middle_name) )$name.=$person->middle_name.' ';
                            $nickname=get_option('church_admin_use_nickname');
                            if(!empty( $nickname)&&!empty( $person->nickname) )$name.='('.$person->nickname.') ';
                            //last name
                            $prefix=get_option('church_admin_use_prefix');
                            if(!empty( $prefix) &&!empty( $person->prefix) )  {	$Prefix=$person->prefix.' ';}else{$Prefix='';}
                            $last_name=esc_html( $Prefix.$person->last_name);

                            if( $person->people_type_id=='1')
                            {
                                $adults[$last_name][]=esc_html( $name);

                                $first_names[]=$name;
                                if(!empty( $person->email)&&$person->email!=end( $emails) ) $emails[$name]=$person->email;
                                if(!empty( $person->mobile)&&$person->mobile!=end( $mobiles) )$mobiles[$name]=esc_html( $person->mobile);
                                
                                if(!empty( $person->attachment_id) )$photos[$name]=$person->attachment_id;
                                $x++;
                            }
                            else
                            {
                                $children[]=esc_html(trim( $name) );
                                if(!empty( $person->attachment_id) )$photos[$name]=$person->attachment_id;
                            }

                        }

                        //create output
                        array_filter( $adults);
                        //church_admin_debug(print_r( $adults,TRUE) );
                        $adultline=array();
                        //Adults names
                        $add='<li class="addItem" style="padding: .7em 1em;"><h3>';
                        foreach( $adults as $lastname=>$firstnames)  {$adultline[]=implode(" &amp; ",$firstnames).' '.$lastname;}
                        $add .="\r\n".esc_html(implode(" &amp; ",$adultline) ).'</h3>';

                        if(!empty( $children) )$add.='<p>'.esc_html(implode(", ",$children) ).'</p>';
                        if(!empty( $addressLine->attachment_id)&&$photo)
                        {
                            $image=wp_get_attachment_image( $addressLine->attachment_id,'ca-address-thumb');
                            if(defined('CA_DEBUG') )church_admin_debug( $image);
                            $add.='<p>'.$image.'</p>';
                        }
                        if(!empty( $addressLine->address) )
                        {
                            $add.='<p>'.str_replace(',',',<br>',$addressLine->address).'</p>';
                        }
                        $add.='<p><a href="'.esc_url('https://www.google.com/maps/search/?api=1&query='.$addressLine->lat.','.$addressLine->lng.'&amp;t=m&amp;z=16').'" class="button button-map"><i class="far fa-map"></i> '.esc_html( __('Map','church-admin' ) ).'</a></p>'."\r\n\t";
                        $add.='<p><a href="https://www.google.com/maps/dir/?api=1&destination='.urlencode( $addressLine->address).'" class="button button-map"><i class="fas fa-route"></i> '.esc_html( __('Directions','church-admin' ) ).'</a></p>'."\r\n\t";
                        if ( $addressLine->phone)$add.='<p><a class="email ca-email" href="'.esc_html('tel:'.str_replace(' ','',$addressLine->phone) ).'">'.esc_html( $addressLine->phone)."</a></p>\n\r\t\t";
                        if(!empty( $emails) )
                        {
                            foreach( $emails AS $name=>$email)
                            {
                                $add.='<p>'.esc_html( $name).':<a href="mailto:'.esc_html( $email).'">'.esc_html( $email).'</a></p>';
                            }
                        }
                        if(!empty( $mobiles) )
                        {
                            foreach( $mobiles AS $name=>$mobile)
                            {
                                $add.='<p>'.esc_html( $name).': <a href="tel:'.esc_html(str_replace(' ','',$mobile) ).'">'.esc_html( $mobile).'</a></p>';
                            }
                        }
                        $add.='<a href="'.home_url().'/?ca_download=vcf&id='.(int)$ordered_row->household_id.'&vcf='.md5('AllAboutJesus'.$ordered_row->household_id).'&token='.esc_attr(sanitize_text_field(stripslashes($_GET['token'])) ).'&_wpnonce='.wp_create_nonce( $ordered_row->household_id).'"><div class="ui-btn ui-btn-icon-right ui-icon-arrow-d"></div></a>';
						if(!empty( $admin) )$add.='<p><button id="edit-directory" data-household_id="'.(int)$ordered_row->household_id.'" class="button green">'.esc_html( __('Edit directory entry','church-admin' ) ).'</button></p>';
        				$add.= '</li>';


                    $address['add'.$ordered_row->household_id]=$add;
            }


            }else{$address=array('<li>'.esc_html( __('No address list yet','church-admin' ) ).'</li>');}

            $output=array('address_list'=>'<ul class="address  ui-listview">'.wp_kses_post(implode("\r\n ",array_unique( $address) )).'</ul>');
        }
    }
    
    //church_admin_debug(print_r( $output,TRUE) );
	echo json_encode( $output);
    die();
}

add_action("wp_ajax_ca_acts_of_courage", "ca_acts_of_courage");
add_action("wp_ajax_nopriv_ca_acts_of_courage", "ca_acts_of_courage");
/******************
*
* `courage`
*
*******************/

function ca_send_courage_request()
{
		global $wpdb;
		if(!empty( $_GET['token'] ) )
		{
			$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token'] ) )).'"';
			$user_id=$wpdb->get_var( $sql);
		}


		$title=!empty( $_GET['courage_title'])?sanitize_text_field(  stripslashes($_GET['courage_title']) ):null;
		$content=!empty( $_GET['content'])?sanitize_textarea_field(  stripslashes($_GET['content'] )):null;
		if(empty($title) || empty($content)){exit();}
		$args=array('post_content'=>wp_kses_post( $content),'post_title'=>wp_strip_all_tags( $title),'post_status'=>'draft','post_type'=>'acts-of-courage');
		if(!empty( $user_id) )$args['post_author']=$user_id;
		if(user_can( $user_id, 'manage_options' ) )$args['post_status']='publish';
		if(!empty( $user_id) )$args['post-author']=$user_id;
		$post_id = wp_insert_post( $args);

		if(!is_wp_error( $post_id) )  {
  			//the post is valid

  			if(!user_can( $user_id, 'manage_options' ) ){
				church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(__('Act of courage Draft','church-admin' ) ),esc_html(__('A draft act of courage has been posted. Please moderate','church-admin') ),null,null,null,null,null,TRUE);
				//wp_mail(get_option('church_admin_default_from_email'),esc_html(__('Act of courage Draft','church-admin' ) ),esc_html(__('A draft act of courage has been posted. Please moderate','church-admin') ));
				
			}
		}else{
  			//there was an error in the post insertion,

		}
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		header('Access-Control-Allow-Credentials: true');
		echo json_encode(array('done') );
		die();
}
add_action("wp_ajax_ca_send_courage_request", "ca_send_courage_request");
add_action("wp_ajax_nopriv_ca_send_courage_request", "ca_send_courage_request");








/******************
*
* Service book
*
*******************/
function ca_bookservice()
{
    global $wpdb;
    $output='';
    //church_admin_debug(print_r( $_POST,TRUE) );
    if(wp_verify_nonce( $_POST['nonce'],'service-prebooking')&&!empty( $_POST['email'] )&&!empty( $_POST['phone'] ) && !empty( $_POST['date_id'] )&&!empty( $_POST['names'] ) )
    {
		//sanitize
        $email=!empty($_REQUEST['email'])?sanitize_text_field(stripslashes( $_POST['email'] ) ):null;

        $phone=!empty($_REQUEST['email'])?sanitize_text_field( stripslashes($_POST['phone'] ) ):null;
        $date_id=!empty($_REQUEST['date_id'])?sanitize_text_field(stripslashes($_POST['date_id'])):null;
        $service_id=!empty($_REQUEST['service_id'])?sanitize_text_field(stripslashes($_POST['service_id'])):null;
		$names = !empty($_REQUEST['names'])?church_admin_sanitize($_POST['names']):array();
		//validate
		if(empty($email) ||!is_email($email)){exit();}
		if(empty($phone)){exit();}
		if(empty($date_id)||!ctypr_digit($date_id)){exit();}
		if(empty($service_id)||!ctypr_digit($service_id)){exit();}


        $bubbleID=$wpdb->get_var('SELECT MAX(bubble_id) FROM '.$wpdb->prefix.'church_admin_covid_attendance');
        if ( empty( $bubbleID) )$bubbleID=0;
        $bubble_id=$bubbleID+1;
        $serviceDetail=$wpdb->get_row('SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE a.event_id=b.event_id AND b.date_id="'.esc_sql($date_id).'"');
        $service=esc_html(sprintf(__('%1$s on %2$s at %3$s','church-admin' ) ,$serviceDetail->service_name,mysql2date(get_option('date_format'),$serviceDetail->start_date),mysql2date(get_option('time_format'),$serviceDetail->start_time)) );
        $output.='<p>'.$service.'</p>';
        $people=array();
        foreach( $names AS $key=>$name)
        {
            
            if(!empty( $name) )
            {
                $check=$wpdb->get_var('SELECT covid_id FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE date_id="'.(int)$date_id.'" AND people_id="'.esc_sql($name).'" AND email="'.esc_sql($email).'" AND phone="'.esc_sql($phone).'"');
                //church_admin_debug( $wpdb->last_query);
                if( $check)
                {
                    $output.=esc_html(sprintf(__('%1$s is already booked in','church-admin'  ),$name)).'<br>';
                }
                else
                {
                    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_covid_attendance (service_id,bubble_id,date_id,people_id,phone,email)VALUES("'.(int)$serviceDetail->service_id.'","'.(int)$bubble_id.'","'.(int)$date_id.'","'.esc_sql($name).'","'.esc_sql($phone).'","'.esc_sql($email).'")');
                    //church_admin_debug( $wpdb->last_query);
                    $output.=esc_html(sprintf(__('%1$s is booked in','church-admin' ) ,$name)).'<br>';
                    $people[]='<p>'.esc_html( $name).'</p>';
                }
            }
        }
        $output.='<p><button class="tab-button" data-tab="#service-prebooking">'.esc_html( __("Make another booking",'church-admin' ) ).'</button></p>';
        $message=esc_html( sprintf(__('Service pre-booking for %1$s at %2$s','church-admin' ),mysql2date(get_option('date_format'),$serviceDetail->start_date),$serviceDetail->start_time));
        $message.='<p><strong>'.esc_html( __('Booking names','church-admin' ) ).'</strong></p>'.implode("",$people);
        $message.='<p>'.esc_html(sprintf(__('Booking ID is %1$s','church-admin' ) ,$bubble_id ) ).'</p>';
        $message.='<p>'.esc_html( __('Please reply ASAP if you need to cancel, so others can book in','church-admin' ) ).'</p>';
       
		church_admin_email_send($email,esc_html(__('Service prebooking','church-admin' ) ),wp_kses_post($message),null,null,null,null,null,TRUE);
    }

    header('Access-Control-Max-Age: 1728000');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
    header('Access-Control-Allow-Credentials: true');
    echo $output;
    //church_admin_debug( $output);
	die();
}



add_action("wp_ajax_ca_bookservice","ca_bookservice");
add_action("wp_ajax_nopriv_ca_bookservice","ca_bookservice");
function ca_service_prebooking()
{
    global $wpdb;
    $nonce=wp_create_nonce('service-prebooking');
    //grab services for next seven days
    $sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE a.event_id=b.event_id AND b.start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) ORDER BY b.start_date,b.start_time';
    //church_admin_debug( $sql);
    $services=$wpdb->get_results( $sql);
    if(!empty( $services) )
    {
        $totalAvailability=0;
        foreach ( $services AS $service)
        {
            if(!empty( $service->bubbles) )
            {
                $bookedBubbles=$wpdb->get_var('SELECT COUNT(DISTINCT(bubble_id) ) FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE date_id="'.(int)$service->date_id.'"' );
                $availability= $service->bubbles - $bookedBubbles;
                $totalAvailability+=$availability;
                $type="bubbles";
                $bubble_size=$service->bubble_size;
            }
            else
            {
                $bookedPeople=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE date_id="'.(int)$service->date_id.'"');
                $availability=$service->max_attendance-$bookedPeople;
                $totalAvailability+=$availability;
                $type="individuals";
                $bubble_size=0;
            }
            $serviceArray[]=array('date_id'=>$service->date_id,
                'service'=>esc_html(sprintf(__('%1$s on %2$s at %3$s','church-admin' ) ,$service->service_name,mysql2date(get_option('date_format'),$service->start_date),mysql2date(get_option('time_format'),$service->service_time) )),
                'availability'=>(int)$availability,
                'type'=>esc_html($type),'bubble_size'=>(int)$bubble_size
            );
        }




    }
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo json_encode(array('nonce'=>$nonce,'availability'=>$totalAvailability,'services'=>$serviceArray) );
	die();
}

add_action("wp_ajax_ca_service_prebooking", "ca_service_prebooking");
add_action("wp_ajax_nopriv_ca_service_prebooking", "ca_service_prebooking");



/*****************************************************
*
* Register
*
******************************************************/
function ca_register()
{
    global $wpdb;
    $output='<h2>'.esc_html( __('Register','church-admin' ) ).'</h2>';

    $email=sanitize_text_field( $_POST['email'] );
    $first_name=sanitize_text_field( $_POST['first_name'] );
    $last_name=sanitize_text_field( $_POST['last_name'] );
    $mobile=sanitize_text_field( $_POST['mobile'] );
	$address=sanitize_text_field( $_POST['address'] );
	$gender=!empty( $_POST['gender'] )?1:0;
	$send_email=!empty( $_POST['send_email'] )?1:0;
	$blog_posts=!empty( $_POST['blog_posts'] )?1:0;
	$prayer_requests=!empty( $_POST['prayer_requests'] )?1:0;	
	$bible_readings=!empty( $_POST['bible_readings'] )?1:0;
	$photo_permission=!empty( $_POST['photo_permission'] )?1:0;
    $sms_receive=!empty( $_POST['sms_receive'] )?1:0;
	$pushToken=!empty( $_POST['pushToken'] )?$_POST['pushToken']:NULL;
    $errors=array();
    if(church_admin_spam_check( $email,'email') )$errors[]='<p>That was not a recognisable email</p>';
    if(church_admin_spam_check( $first_name,'text') )$errors[]='<p>Spam!</p>';
    if(church_admin_spam_check( $last_name,'text') )$errors[]='<p>Spam!</p>';
    if(!empty( $email) )$check=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql( $email).'"');
    if( $check)  {
		$errors[]='<p>'.esc_html( __('Your email address is already registered.','church-admin' ) ).'</p><p><button class="button" data-tab="#forgotten" id="forgotten"  >'.esc_html( __('Forgotten Password','church-admin' ) ).'</button></p>';
	}
    $output='';
    if(!empty( $errors) )
    {
        $output.=implode('<br>',$errors);
    }
    else
    {
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,last_updated,first_registered) VALUES("'.esc_sql( $address).'","'.esc_sql( wp_date("Y-m-d H:i:s") ) .'","'.esc_sql(wp_date('Y-m-d')).'")');
        $household_id=$wpdb->insert_id;
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,mobile,household_id,people_type_id,member_type_id,email_send,sms_send,photo_permission,active,pushToken,first_registered)VALUES("'.esc_sql( $first_name).'","'.esc_sql( $last_name).'","'.esc_sql( $email).'","'.esc_sql( $mobile).'","'.(int)$household_id.'","1","1","'.$send_email.'","'.$sms_receive.'","'.$photo_permission.'",1,"'.esc_sql( $pushToken).'","'.esc_sql(wp_date('Y-m-d')).'")');
        $people_id=$wpdb->insert_id;
		church_admin_update_people_meta( $prayer_requests,$people_id,'prayer-requests',date('Y-m-d') );
		church_admin_update_people_meta( $bible_readings,$people_id,'bible-readings',date('Y-m-d') );



        $output.='<p>'.esc_html( __('Thank you for registering, you will get a confirmation email and then an admin will give you a user login','church-admin' ) ).'</p>';
        //send admin email
        
        $adminmessage=get_option('church_admin_new_entry_admin_email');
		$admin_message = str_replace('[HOUSEHOLD_ID]','[HOUSEHOLD_ID]&token=[NONCE]',$admin_message);
		$admin_message=str_replace('[HOUSEHOLD_ID',(int)$household_id,$admin_message);
		$adminmessage.='<p>'.esc_html(implode(" ",array_filter(array( $form['first_name'],$form['prefix'],$form['last_name'] ) )) ).'</p>';
		if(!empty( $form['email'] ) )$adminmessage.='<p><a href="'.esc_url('mailto:'.$form['email'] ).'">'.esc_html( $form['email'] ).'</a></p>';
		if(!empty( $form['mobile'] ) )$adminmessage.='<p><a href="call:'.esc_url( $form['mobile'] ).'">'.esc_html( $form['mobile'] ).'</a></p>';
		if(!empty( $form['address'] ) )$adminmessage.='<p>'.esc_html( $form['address'] ).'</p>';   
		$admin_email=get_option('church_admin_default_from_email');
        if(!empty( $admin_email) )
        {
            add_filter( 'wp_mail_from_name','church_admin_from_name' );
			add_filter( 'wp_mail_from', 'church_admin_from_email');
            add_filter('wp_mail_content_type','church_admin_email_type');
            
            $headers=array('Reply To:'.esc_html(implode(" ",array_filter(array( $form['first_name'],$form['prefix'],$form['last_name'] ) )) ).'<'.esc_html( $form['email'] ).'>');
            remove_filter('wp_mail_content_type','church_admin_email_type');
            remove_filter( 'wp_mail_from_name','church_admin_from_name' );
		    remove_filter( 'wp_mail_from', 'church_admin_from_email');
        }
        church_admin_email_confirm( $people_id);
    }
    header('Access-Control-Max-Age: 1728000');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
    header('Access-Control-Allow-Credentials: true');
    echo $output;
    die();
}
add_action("wp_ajax_ca_register","ca_register");
add_action("wp_ajax_nopriv_ca_register","ca_register");



/*****************************************************
*
* Delete app content
*
******************************************************/
function church_admin_delete_current_app_content()
{
    global $wpdb;
    $wpdb->query('DELETE FROM '.$wpdb->posts.' WHERE post_type="app-content"');
    delete_option('church_admin_app_defaults');
    church_admin_fix_app_default_content();
    echo'<h2>'.esc_html( __('App content deleted and reset','church-admin' ) ).'</h2>';
}








/*******************************
 * CONTACT MESSAGES
 ******************************/
add_action("wp_ajax_ca_contact_messages","ca_contact_messages");
add_action("wp_ajax_nopriv_ca_contact_messages","ca_contact_messages");

function ca_contact_messages()
{
	church_admin_debug('Accessing ca_contact_messages');
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	$output=array();


	global $wpdb;
	if(!empty( $_GET['token'] ) )
	{
		$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes( $_GET['token'] ) ) ).'"';
		$user_id=$wpdb->get_var( $sql);
	}
	if(!empty( $user_id) )
	{
		$person=$wpdb->get_row('SELECT * FROM '. $wpdb->prefix.'church_admin_people'.' WHERE user_id="'.(int)$user_id.'"');
	}
	if ( empty( $person) )
	{
		$output['data']=__("You don't have permission for this content",'church-admin');
		echo json_encode( $output);
		exit();
	}
	$settings=get_option('church_admin_contact_form_settings');
	if ( empty( $settings) || $settings['pushToken']!=$person->people_id)
	{
		//no user access
		$output['content']=__("You don't have permission for this content",'church-admin');
		echo json_encode( $output);
		exit();
	}
	/********************************
	 * Safe to proceed
	 *******************************/
	$ID = !empty($_REQUEST['id'] )?sanitize_text_field(stripslashes($_REQUEST['id'] )):null;
	if(!empty( $ID ) && church_admin_int_check($ID) )  {$where=' WHERE contact_id="'.(int)$ID.'"';}else{$where='';}
	$messages=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_contact_form '.$where.' ORDER BY post_date DESC');
	if ( empty( $messages) )
	{
		//no messages
		$output['content']=__("No contact form messages",'church-admin');
		echo json_encode( $output);
		exit();
	}
	/**********************************
	 *  Build messages output
	 *********************************/
	$out='<h2>'.esc_html( __('Contact form messages','church-admin' ) ).'</h2>';
	$out.='<ul class="contact ui-listview">';
	foreach( $messages AS $message)
	{
		church_admin_debug( $message);
		$out.='<li class="contact" style="padding: .7em 1em;" id="message'.(int)$message->contact_id.'"><p><strong>'.esc_html( $message->subject).'</strong> '.mysql2date(get_option('date_format'),$message->post_date).'</p>';
		$out.='<p>'.esc_html( $message->name).' <a href="'.esc_url('mailto:'.$message->email.'&subject='.esc_html( __('Re:','church-admin' ) ).esc_html( $message->subject) ).'">'.esc_html( $message->email).'</a></p>';
		if(!empty( $message->phone) )$out.='<p><a href="'.esc_url('tel:'.$message->phone).'">'.esc_html( $message->phone).'</a></p>';
		$out.='<p>'.esc_html( $message->message).'</p>';
		$out.='<p><button class="delete-message button" data-contact_id="'.(int)$message->contact_id.'">'.esc_html( __('Delete message','church-admin' ) ).'</button>';
		$out.='</li>';

	}
	church_admin_debug( $out);
	$output['content']=$out;
	echo json_encode( $output);
	exit();


}

add_action("wp_ajax_ca_delete_contact_message","ca_delete_contact_message");
add_action("wp_ajax_nopriv_ca_delete_contact_message","ca_delete_contact_message");

function ca_delete_contact_message()
{
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	church_admin_debug("**** DELETE CONTACT MESSAGE ***");
	church_admin_debug( $_POST);
	global $wpdb;
	if(!empty( $_REQUEST['token'] ) )
	{
		$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_REQUEST['token']) ) ).'"';
		church_admin_debug( $sql);
		$user_id=$wpdb->get_var( $sql);

	}
	if(!empty( $user_id) )
	{
		$person=$wpdb->get_row('SELECT * FROM '. $wpdb->prefix.'church_admin_people'.' WHERE user_id="'.(int)$user_id.'"');
	}
	if ( empty( $person) )
	{
		echo 'No person found';
		exit();
	}
	$settings=get_option('church_admin_contact_form_settings');
	if ( empty( $settings) || $settings['pushToken']!=$person->people_id)
	{
		echo'No push token';
		exit();
	}
	/********************************
	 * Safe to proceed
	 *******************************/
	$contact_id = !empty($_REQUEST['contact_id'])? sanitize_text_field(stripslashes($_REQUEST['contact_id'])):null;
	if(!empty( $contact_id) && church_admin_int_check($contact_id))  {
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_contact_form WHERE contact_id="'.(int)$contact_id.'"');
	}
	echo(int)$contact_id;
	exit();

}