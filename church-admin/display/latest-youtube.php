<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_latest_youtube( $playlist_id,$cache=3600)
{
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
			
		}
    if(is_user_logged_in() &&(church_admin_level_check('Media')||user_can('manage_options') ))delete_option('church_admin_youtube_latest-'.$playlist_id);
    //cache - each lookup costs a quota unit, so result is cached.
    $lastYTLookup=get_option('church_admin_youtube_latest-'.$playlist_id);
    if(!empty( $lastYTLookup) )
    {
       
        if( $lastYTLookup['timestamp']+$cache<time() )
        {
            
            return $lastYTLookup['content'];
        }
    }
    //Need to get result and cache
    $google_api=get_option('church_admin_google_api_key');
    if ( empty( $google_api) )
    {
     
        $output='<h2>'.esc_html(__('Google API key required','church-admin')).'</h2>';
        if(church_admin_level_check('Directory') )
        {
            $output.='<p>'.esc_html(__('If you alreday have a Google API key, paste it into the Google Api key form field on the settings page.','church-admin')).'</p>';
            $output.='<p><a href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=settings','settings').'">'.esc_html( __('Settings','church-admin' ) ).'</a></p>';
        }
        return $output;

    }
    if ( empty( $playlist_id) )
    {
        $output='<h2>'.esc_html( __('Playlist ID required','church-admin' ) ).'</h2>';
        $output.='<p><a href="https://www.churchadminplugin.com/tutorials/latest-youtube-video/">'.esc_html(__('How to get the playlist ID ','church-admin')).'</a></p>';
        return $output;

    }
    $args = array('headers' => array( 'Referer' => site_url() ) );
    $response=wp_remote_get(esc_url('https://www.googleapis.com/youtube/v3/playlistItems/?part=snippet,status,contentDetails&playlistId='.esc_html( $playlist_id).'&key='.$google_api),$args);
    $api_response =  json_decode(wp_remote_retrieve_body( $response ),TRUE);
    //church_admin_debug($api_response);
    if ( empty( $api_response['items'][0] ) )
    {
        return '<p>'.esc_html(__('No playlist videos retrieved from Youtube','church-admin')).'</p>';
    }
    $item=$api_response['items'][0];

    $output='<div style="position:relative;padding-top:56.25%"><iframe class="ca-video" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.esc_url('https://www.youtube.com/embed/'.$item['snippet']['resourceId']['videoId'] ).'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
        
    $cache=array('timestamp'=>time(),'content'=>$output);
    update_option('church_admin_youtube_latest-'.$playlist_id,$cache);

    
    return $output;

}