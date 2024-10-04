<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Lukaswhite\PodcastFeedParser\Parser;
function church_admin_import_itunes(){

    global $wpdb;


    echo'<h2>'.esc_html(__('Podcast feed importer','church-admin')).'</h2>';
    if(empty($_POST['feed_url'])){

        echo'<form action="'.esc_url(wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=itunes-importer','itunes-importer')).'" method="POST">';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Feed URL to import','church-admin') ).'</label>';
        echo '<input class="church-admin-form-control" type="url" name="feed_url"></div>';
        echo'<p><input class="button-primary" type="submit" name="'.esc_attr(__('Import','church-admin') ).'"></p></form>';
        return;

    }
    //initialise podcast class
    require_once 'vendor/autoload.php';
 
 
    //feed url
     
    $url=church_admin_sanitize($_POST['feed_url']);
    //check it is a url
    if (!wp_http_validate_url( $url ) ) {
        echo'<div class="notice notice-error"><p>'.esc_html(__('Feed URL is not a valid URL','church-admin')).'</p></div>';
        return;
    }

    //get content
    try{
        $content = wp_remote_get($url);
        if(!empty($content)){
            $feed_content= $content['body'];
        }
    }
    catch(Exception $e) {
        echo'<div class="notice notice-error"><p>'.esc_html(__('Could not retrieve feed from URL','church-admin')).'</p></div>';
        return;

    }
   

    //try to parse content
    try{
        $parser = new Parser();
        $parser->setContent($feed_content);
        $podcast = $parser->run();
    }
    catch (Exception $e) {
        echo'<div class="notice notice-error"><p>'.esc_html(__('URL does not appear to be a valid/readable podcast feed','church-admin')).'</p></div>';
        return;
    }

    $data = $podcast->getEpisodes(); //Return: array<Episodes>
    

    if(empty($data)){
        echo'<div class="notice notice-error"><p>'.esc_html(__('No episodes found','church-admin')).'</p></div>';
        return;
    }

    /******************************
    * build podcast xml settings
    *******************************/


    $new_settings=array(
        
        'title' =>  $podcast->getTitle(),
        'description' => $podcast->getDescription(),
        'language'=>'',
        'copyright'=>$podcast->getCopyright(),
        'subtitle'=>$podcast->getSubtitle(),
        'author'=>$podcast->getAuthor(),
        'summary'=>'',
        'owner_name'=>$podcast->getManagingEditor(),
        'owner_email'=>'',
        'image_id'=>'',
        'image'=>$podcast->getArtwork()->getUri(),
        'category'=>'',
        'language'=>$podcast->getLanguage(),
        'explicit'=>'clean',
            
    );
   
    update_option('ca_podcast_settings',$new_settings);

    /************************************
    * Get episodes and save to database
    ************************************/
    //get a series id
    $series_id=$wpdb->get_var('SELECT series_id FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY series_id ASC LIMIT 1');
    if(empty($series_is)){
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name) VALUES("'.esc_sql(__('Non series sermon','church-admin')).'")');
        $series_id = $wpdb->insert_id;
    }
    //get a service id
    $service_id=$wpdb->get_var('SELECT service_id FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_id ASC LIMIT 1');
    if(empty($series_is)){
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_services (service_name) VALUES("'.esc_sql(__('Service','church-admin')).'")');
        $service_id = $wpdb->insert_id;
    }
    /************************************
    * Process episodes
    ************************************/
    $count=0;
    foreach($data as $episode) {
        $title = church_admin_sanitize($episode->getTitle());
        if(empty($title)){continue;}
        $media_url = sanitize_url(stripslashes($episode->getMedia()->getUri()));
        //some podcast do weird stuff with links eg link within link
        if(empty($media_url)){continue;}
        $urls = explode('https',$media_url);
              if(is_array($urls)){
            $media_url=urldecode('https'.end($urls)); //grab last url and prepend https back!
        }
        
        $file_slug = sanitize_title($title);
        $author = church_admin_sanitize($episode->getAuthor());
        $length = church_admin_sanitize($episode->getDuration());
        $date = church_admin_sanitize($episode->getPublishedDate());
        $description = church_admin_sanitize($episode->getDescription());
        echo'<h2>'.esc_html($title).'</h2>';
        echo'<p>'.esc_html($description).'</p>';
        echo'<p>'.esc_html(sprintf(__('Preached by %1$s on %2$s','church-admin'),$author,$date->format(get_option('date_format') ) )).'</p>';
        echo'<p><audio controls><source src="'.esc_url($media_url).'" type="audio/mpeg"></audio></p>';
       
        $check = $wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE external_file="'.esc_sql($media_url).'" AND file_title="'.esc_sql($title).'"');
        if(empty($check)){

            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_files (file_title,file_description,length,pub_date,service_id,series_id,speaker,external_file,file_slug) VALUES("'.esc_sql($title).'","'.esc_sql($description).'","'.esc_sql($length).'","'.esc_sql($date->format('Y-m-d H:i:s')).'","'.(int)$service_id.'","'.(int)$series_id.'","'.esc_sql($author).'","'.esc_sql($media_url).'","'.esc_sql($file_slug).'")');
            echo'<p>'.esc_html(sprintf(__('Database file ID %1$s','church-admin'),$wpdb->insert_id ) ) .'</p>';

            $count++;
        }

    }
    require_once(plugin_dir_path(dirname(__FILE__) ) .'sermon-podcast.php');
    ca_podcast_xml();
    if(!empty($count)){
        echo'<div class="notice notice-success"><h2>'.esc_html(__('Import completed','church-admin')).'</h2>';
        echo'<p>'.esc_html(sprintf(__('%1$s sermons added to the database, and the podcast file has been updated, with some of the settings required.','church-admin'),$count)).'</p>';
        echo'<p><a href="'.esc_url(wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=podcast-settings','podcast-settings')).'">'.esc_html(__('Update podcast settings','church-admin')).'</a></p>';
        echo'</div>';
    }



}