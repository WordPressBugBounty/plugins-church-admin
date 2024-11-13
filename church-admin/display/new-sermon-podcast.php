<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_new_sermons_display($how_many=9,$nowhite=false,$playnoshow=0,$from=null,$rolling=12)
{


 
    church_admin_debug(func_get_args());
   
    global $wpdb;
    //initialise variables
    $out='<script>var nonce="'.wp_create_nonce('sermon-display').'";</script>';
    $out.='<!-- new-sermon-podcast.php-->';
    $stored_series=array();
    $stored_speakers=array();
    $no_of_records_per_page = !empty($how_many)?(int)$how_many:9;
    $exclude=array();
    $start_date=null;
    if(!empty($from) && church_admin_checkdate($from)){$start_date=$from;}
    $upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/sermons/';
		$url=$upload_dir['baseurl'].'/sermons/';
    /*********************************************
     * sanitize user input 
     ***********************************************/
    

    $pageno=!empty($_REQUEST['pageno'])?sanitize_text_field(stripslashes($_REQUEST['pageno'])):1;
    $form_sermon_title = !empty($_REQUEST['sermon'])? sanitize_text_field( stripslashes($_REQUEST['sermon'])) :null;
    $order=!empty($_REQUEST['order'])?sanitize_text_field(stripslashes($_REQUEST['order']) ):'desc';
    $series=!empty($_REQUEST['sermon-series'])?urldecode(sanitize_text_field(stripslashes($_REQUEST['sermon-series']) ) ):null;
    $series_id=!empty($_REQUEST['series_id'])?sanitize_text_field(stripslashes($_REQUEST['series_id']) ) :null;
    $speaker=!empty($_REQUEST['speaker'])?sanitize_text_field(stripslashes($_REQUEST['speaker']) ):null;
    //$book=!empty($_REQUEST['book'])?sanitize_text_field(stripslashes($_REQUEST['book']) ):'book';
    $search = !empty($_REQUEST['sermon-search'])?sanitize_text_field(stripslashes($_REQUEST['sermon-search']) ):null;
    $date = !empty($_REQUEST['sermon-date'])?sanitize_text_field(stripslashes($_REQUEST['sermon-date']) ):null;
    /************************
     * validate user input
    *************************/
    //shortcode variables
    $how_many = (!empty($how_many) && church_admin_int_check($how_many)) ? (int)$how_many : 9;
    $nowhite = !empty( $nowhite ) ? 1 : 0;
    $playnoshow = !empty( $playnoshow ) ? 1 : 0;
    $start_date = (!empty($from) && church_admin_checkdate($from)) ? $from : null;
    if(!empty( $rolling ) && church_admin_int_check($rolling )){
        $start_date = date('Y-m-d', strtotime('-'.(int)$rolling.' months'));

    }
    
    //get series_id from sermon-series
    if(!empty($_REQUEST['sermon-series'])){
        $series_id = $wpdb->get_var('SELECT series_id FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_slug="'.esc_sql($series).'"');
     
    }


    //order
    switch($order)
    {
        case 'asc':
            $order = 'asc';
        break;
        default:
        case 'desc':
            $order = 'desc';
        break;
    }

    // series
    $dateLimiter = !empty( $start_date ) ? ' AND  a.last_sermon > "'.esc_sql( $start_date ).'" ':'';
   
    $seriesSQL= 'SELECT a.* FROM '.$wpdb->prefix.'church_admin_sermon_series a, '.$wpdb->prefix.'church_admin_sermon_files b WHERE a.series_id=b.series_id '.$dateLimiter.' GROUP BY a.series_name ORDER BY a.series_name';
    
    $series_results = $wpdb->get_results($seriesSQL);
    
    if(!empty($series_results))
    {
        foreach($series_results AS $series_row)
        {
            $stored_series[$series_row->series_id]=$series_row->series_name;
        }

    }
       
    
    // speaker 
    $speakers_results=$wpdb->get_results('SELECT speaker FROM '.$wpdb->prefix.'church_admin_sermon_files');
    if(!empty($speakers_results))
    {
        foreach($speakers_results AS $speakers_row){

            //might be comma separated
            $possibles = explode(",",$speakers_row->speaker);
            foreach($possibles AS $key=>$value){
                if(!empty($name)){$name = str_replace('  ',' ',trim( church_admin_title_case( $value )));}
                //church_admin_debug('Possibles: "'.$value.'"');
               if(!empty($name) && !in_array($name,$stored_speakers)) {
                    //church_admin_debug('added to array');
                    $stored_speakers[] = $name;
               }
            }

        }
        array_unique( array_filter( $stored_speakers ) );
        sort($stored_speakers);
        if ( !empty($stored_speakers) && !empty($speaker) && in_array( trim( church_admin_title_case( $speaker )), $stored_speakers  ) ){

            $speaker = trim( church_admin_title_case( $speaker ) );
        }
    }
    church_admin_debug('Speakers...');
    //church_admin_debug($stored_speakers);
    // sermon title
    //not validating but converting to form that is stored in database
    $sanitized_sermon_title = !empty($form_sermon_title) ? urldecode($form_sermon_title) : null;
 
    /************************
     * get sermons
    *************************/
    $dateSQL=$searchSQL='';
    $limitSQL = ' LIMIT '.(int)$how_many;
    $orderSQL = ' ORDER BY a.pub_date '.esc_sql($order);
    $speakerSQL= !empty($speaker) ? ' AND a.speaker LIKE "%'.esc_sql($speaker).'%" ':'';
    $seriesSQL= !empty($series_id) ? ' AND a.series_id ="'.(int)$series_id.'" ':'';
    $fromSQL = !empty($start_date) ? " AND (a.pub_date BETWEEN '".esc_sql($start_date)."' AND NOW() )" : '';
    //sermon id overrides other SQL filters
    if(!empty($sanitized_sermon_title)){
        $sermonTitleSQL = ' AND a.file_slug = "'.esc_sql( $sanitized_sermon_title ).'" ';
        $limitSQL= 'LIMIT 1';
        $orderSQL = $speakerSQL = $seriesSQL = '';
    }else{
        $sermonTitleSQL='';
    }
    /*******************************
     * Search overrides everything!
     *******************************/
    if(!empty($search)){
       
        $s='%'.esc_sql( $search).'%';
        $searchSQL = ' AND (a.file_title LIKE "'.$s.'" OR a.file_description LIKE "'.$s.'" OR a.speaker LIKE "'.$s.'" OR a.bible_texts LIKE "'.$s.'" OR b.series_name LIKE "'.$s.'")  ';
        $orderSQL = $speakerSQL = $seriesSQL = '';
    }
    /*******************************
     * date overrides everything!
     *******************************/
    if(!empty($date)){
       
        $orderSQL = $speakerSQL = $seriesSQL = '';
        $dateSQL = ' AND DATE_FORMAT(a.pub_date, "%Y-%m-%d") = "'.esc_sql($date).'" ';
    }
    /***********************************
     * Build filtered link
     **********************************/
    $link_filters=array();
    if(!empty($order)){
        $link_filters[]='order='.esc_attr($order);
    }
    if(!empty($series)){
        $link_filters[]='series='.urlencode($series);
    }
    if(!empty($speaker)){
        $link_filters[]='speaker='.urlencode($speaker);
    }
    if(!empty($search)){
        $link_filters =array('sermon-search='.urlencode($search));
    }
    $filtered_link=get_permalink().'?'.implode("&amp;",$link_filters);


    //paging
   
    $offset = ($pageno-1) * $no_of_records_per_page;
    $limitSQL = " LIMIT $offset , $no_of_records_per_page ";
    //loggedin
    $loggedinSQL='';
    if(!is_user_logged_in()){
        $loggedinSQL = ' AND a.private = "0" ';
    }

    //run query to get num rows first
    $sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a, '.$wpdb->prefix.'church_admin_sermon_series b WHERE a.series_id=b.series_id '.$dateSQL.$searchSQL.$speakerSQL.$seriesSQL.$sermonTitleSQL.$loggedinSQL.$fromSQL.$orderSQL;
    church_admin_debug($sql);
    $results = $wpdb->get_results( $sql );
    $total_rows = $wpdb->num_rows;
    $total_pages = ceil($total_rows / $no_of_records_per_page);
    //pagination stuff
    $paging = '<ul class="ca-sermon-pagination">';
    //first
    $firstlink = $filtered_link.'&amp;pageno=1';
    church_admin_debug('First link '. $firstlink );
    if($pageno==1){
        $disabled=' class="disabled" ';
    }
    else
    {
        $disabled='';
    }
    $paging .= '<li ><a '.$disabled.' href="'.esc_url($firstlink).'">'.esc_html( __('First','church-admin' ) ).'</a></li>'."\r\n";
    //previous
    if($pageno<=1){
        $disabled= 'class="disabled" ';
        $prevlink='#';
    }
    else
    {   
        $prevlink=($pageno<=1)?$filtered_link:$filtered_link.'&amp;pageno='.($pageno-1);
        $disabled='';
    }
    church_admin_debug('Prev link '. $prevlink );
    $paging .= '<li ><a '.$disabled.' href="'.esc_url($prevlink).'">&laquo;'.esc_html( __('Prev','church-admin' ) ).'</a></li>'."\r\n";
    
    //current
    $currentlink=$filtered_link.'&amp;pageno='.(int)$pageno;
    church_admin_debug('Curr link '. $currentlink );
    $paging.='<li><a class="disabled" href="'.esc_url($currentlink).'">'.(int)$pageno.'</a></li>'."\r\n";
    //next
    $disabled=($pageno >= $total_pages)?1:0;
    if($pageno>=$total_pages)
    {
        $disabled= 'class="disabled" ';
        $nextlink='#';
    }
    else
    {
        $nextlink=($pageno >= $total_pages)?$filtered_link:$filtered_link.'&pageno='.($pageno+1);
        $disabled='';
    }
    church_admin_debug('Next link '. $nextlink );
    $paging .= '<li><a '.$disabled.' href="'.esc_url($nextlink).'">'.esc_html( __('Next','church-admin' ) ).'&raquo;</a></li>'."\r\n";
    //last
    $lastlink = $filtered_link.'&pageno='.(int)$total_pages;
    church_admin_debug('Last link '. $lastlink );
    if($pageno==$total_pages)
    {
        $disabled= 'class="disabled" ';
       
    }
    else
    {
        $disabled='';
    }
    $paging .= '<li><a '.$disabled.' href="'.esc_url($lastlink).'">'.esc_html( __('Last','church-admin' ) ).'</a></li></ul>'."\r\n";
    
    //rerun query with limit


    /************************
     * create output
    *************************/
    $sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a, '.$wpdb->prefix.'church_admin_sermon_series b WHERE a.series_id=b.series_id '.$dateSQL.$searchSQL.$speakerSQL.$seriesSQL.$sermonTitleSQL.$loggedinSQL.$fromSQL.$orderSQL.$limitSQL;
    church_admin_debug($sql);
    $results = $wpdb->get_results( $sql );
    $num_rows = $wpdb->num_rows;
    //podcasts
    $ca_podcast_settings=get_option('ca_podcast_settings');
    

    $out.='<div class="church-admin-podcast-links-container">'."\r\n";
    
	if(!empty( $ca_podcast_settings['itunes_link'] ) ){
        $out.='<div class="church-admin-podcast-link">'."\r\n";
        $out.='<a title="Download on Itunes" href="'.esc_url($ca_podcast_settings['itunes_link']).'">
    <img  alt="Apple Podcasts" src="'.esc_url(plugins_url('/images/apple-podcasts.png',dirname(__FILE__) )).'" width="155" height="40" /></a>';
        $out.='</div>'."\r\n";
    }
    if(!empty( $ca_podcast_settings['spotify_link'] ) ){
        $out.='<div class="church-admin-podcast-link">'."\r\n";
        $out.='<a title="Download on Spotify" href="'.esc_url($ca_podcast_settings['spotify_link']).'">
    <img alt="Spotify"  src="'.esc_url(plugins_url('/images/spotify.png',dirname(__FILE__) )).'" width="110" height="40" /></a>'."\r\n";
        $out.='</div>';
    }
    if(!empty( $ca_podcast_settings['amazon_link'] ) ){
        $out.='<div class="church-admin-podcast-link">'."\r\n";
        $out.='<a title="Download on Amazon" href="'.esc_url($ca_podcast_settings['amazon_link']).'">
    <img alt="Amazon Music"  src="'.esc_url(plugins_url('/images/amazon-podcast.png',dirname(__FILE__) )).'" width="110" height="40" /></a>'."\r\n";
        $out.='</div>';
    }
    $out.='</div>';
    //top filter section
    $out.='<form action= "'.get_permalink().'" method="post">'."\r\n";
    $out.='<input type="hidden" name="pageno" value="'.(int)$pageno.'" />'."\r\n";
    $out.='<div class="church-admin-sermon-filters">'."\r\n";
    
    //order
    $out.='<div class="church-admin-sermon-filter"><select name="order"><option value="desc" '.selected($order,'desc',FALSE).'>'.esc_html(__('Latest first','church-admin')).'</option><option value="asc"  '.selected($order,'asc',FALSE).'>'.esc_html(__('Earliest first','church-admin')).'</option></select></div>'."\r\n";
    if(!empty($stored_speakers))
    {
        //speaker
        $out.='<div class="church-admin-sermon-filter"><select name="speaker"><option value="0">'.esc_html(__('Choose a speaker','church-admin')).'</option>'."\r\n";
        foreach($stored_speakers AS $key=>$spk){
            $out.='<option value="'.esc_attr($spk).'" '.selected($speaker,$spk,FALSE).'/>'.esc_html($spk).'</option>'."\r\n";
        }
        $out.='</select></div>'."\r\n";
    }
    if(!empty($stored_series))
    {
        //series
        $out.='<div class="church-admin-sermon-filter"><select name="series_id"><option value="0">'.esc_html(__('Choose a series','church-admin')).'</option>'."\r\n";
        foreach($stored_series AS $id=>$series_name){
            $out.='<option value="'.esc_attr($id).'" '.selected($series_id,$id,FALSE).'>'.esc_html($series_name).'</option>'."\r\n";
        }
        $out.='</select></div>'."\r\n";
    }
    
    $out.='<div class="church-admin-sermon-filter"><input class="church-admin-sermon-search" placeholder="'.esc_attr(__('Search','church-admin')).'"';
    if(!empty($search)){$out.=' value="'.esc_attr($search).'"';}
    $out.=' type="text" name="sermon-search"></div>';

    $out.='<div class="church-admin-sermon-date">'.church_admin_date_picker( null,'sermon-date',FALSE,NULL,NULL,'church-admin-sermon-date','sermon-date',FALSE,'sermon-date','sermon-date','sermon-date',__('Date','church-admin')).'</div>';
    $out.='<div class="church-admin-sermon-filter-button"><input type="submit" value="'.esc_html(__('Filter','church-admin')).'" /></div>'."\r\n";
    
    $out.='</div>'."\r\n";
    $out.='</form>'."\r\n";
    //sermons
    if(!empty($results))
    {
        
        if($num_rows==1)
        {
            
            //single sermon display
            $row=$results[0];
         
            $video_detail = !empty($row->video_url) ? church_admin_generateVideoEmbedUrl( $row->video_url) : null;
            $video_or_image='';
            //image
            $image = '<img src="'.esc_url(plugins_url('/images/sermon.jpg',dirname(__FILE__) )).'" />';//default image
            if(!empty($row->series_image)){
                $video_or_image = wp_get_attachment_image( $row->series_image,'large','' );
            }
            if(!empty($video_detail['embed'])){
                if(!empty( $nowhite) )
                {
                    $video_or_image='<iframe class="ca-video" src="'.esc_url($video_detail['embed']).'" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                }
                else 
                {
                    $video_or_image='<div style="width:100%"><div style="position:relative;padding-top:56.25%"><iframe class="ca-video" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.esc_url($video_detail['embed']).'" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>'."\r\n";
                }
                $views=church_admin_youtube_views_api( $video_detail['id'] );
            }
            $sermon_title = !empty( $row->file_title ) ? $row->file_title: __('Sermon Title','church-admin');
            $sermon_description_excerpt = !empty( $row->file_description ) ?church_admin_excerpt( $row->file_description,150,'...') : '';

            //audio




            $series = !empty( $row->series_name ) ? $row->series_name: __('Sermons','church-admin');
            $speaker = !empty( $row->speaker ) ? $row->speaker: __('Preacher','church-admin');
            $escaped_series_link = '<a class="church-admin-series-link"  href="'.esc_url(get_permalink().'?series='.urlencode($row->series_name)).'">'.esc_html($row->series_name).'</a>';
            $escaped_speaker_link = '<a class="church-admin-series-link"  href="'.esc_url(get_permalink().'?speaker='.urlencode($speaker)).'">'.esc_html($speaker).'</a>';
            
            $date = !empty( $row->pub_date ) ? mysql2date(get_option('date_format'),$row->pub_date): '';


            $URL= get_permalink().'?sermon='.$row->file_slug;
            $title=sanitize_title(str_replace('"','',$row->file_title) );
            //facebook
            $share='<p><span class="ca-new-sermon-share"><a target="_blank"  style="text-decoration:none" href="'.esc_url('https://www.facebook.com/sharer/sharer.php?u='.esc_url($URL).'?sermon='.$row->file_slug).'"><span class="ca-dashicons dashicons dashicons-bigger  dashicons-facebook"></span></a></span> &nbsp;'."\r\n";
            //twitter
            $share.='<span class="ca-new-sermon-share"><a style="text-decoration:none" target="_blank"  href="'.esc_url('https://twitter.com/intent/tweet?text='.esc_attr($sermon_title).'&url='.$URL).'"><span class="ca-dashicons dashicons dashicons-bigger  dashicons-twitter"></span></a></span>&nbsp;'."\r\n";
            //email
            $share.='<span class="ca-new-sermon-share"><a  style="text-decoration:none" href="'.esc_url('mailto:?subject='.esc_attr($sermon_title).'&amp;body='.$URL).'"><span class="ca-dashicons dashicons dashicons-bigger  dashicons-email"></span></a></span>&nbsp;'."\r\n";
            //link
            $share.='<span class="ca-new-sermon-share"><a class="copy-me" style="text-decoration:none" data-url="'.esc_url($URL).'" ><span class="ca-dashicons dashicons dashicons-bigger  dashicons-admin-links"></span></a></span>'."\r\n";
            
            //sms
            $share.='<span class="ca-new-sermon-share"> <a  href="'.esc_url('sms:?body='.$URL).'"><span class="ca-dashicons dashicons dashicons-bigger dashicons-smartphone" style="text-decoration:none"></span></a></span>'."\r\n";
            
            $plays=church_admin_plays( $row->file_id);  
            

            //create output    
            $out.='<p><a href="'.esc_url(get_permalink()).'">&laquo; '.esc_html( __('Back to all sermons','church-admin' ) ).'</a></p>';
            $out .= "\t".'<div class="church-admin-single-sermon-container church-admin-no-border">'."\r\n";
                    $out .= "\t\t".'<div class="church-admin-sermon-video">'."\r\n";
                        $out .= "\t\t\t".$video_or_image."\r\n";
                    $out .= "\t\t".'</div><!--thumbnail-->'."\r\n";
                    $out .='<div class="church-admin-sermon-content">'."\r\n";
                        $out .= "\t\t\t".'<div class="church-admin-sermon-meta">'."\r\n";
                        $out .= "\t\t\t\t".'<p class="church-admin-pub-date">'.esc_html($row->file_title).'</p>';
                            $out .= "\t\t\t\t".'<p class="church-admin-pub-date">'.esc_html($date).'</p>';
                            $out.="\t\t\t\t".'<p class="church-admin-series_link">'.esc_html(__('Series:','church-admin') ).' '.$escaped_series_link.'</p>'."\r\n";
                            $out.="\t\t\t\t".'<p class="church-admin-speaker_link">'.esc_html(__('Speaker:','church-admin') ).' '.$escaped_speaker_link.'</p>'."\r\n";

                            if(!empty($plays) && empty($playnoshow)){
                                $out.="\t\t\t\t".'<p class="">'.esc_html(__('Audio plays:','church-admin') ).' <span class="plays">'.(int)$plays.'</span></p>'."\r\n";
                            }
                        $out .= "\t\t\t".'</div><!--sermon meta end-->'."\r\n";
                        if(!empty( $row->file_name)&& file_exists( $path.$row->file_name) )
                        {
                            $out.='<p><audio class="sermonmp3" data-id="'.esc_html( $row->file_id).'" src="'.esc_url( $url.$row->file_name).'" preload="auto" controls></audio></p>';
                            $download='<a href="'.esc_url( $url.$row->file_name).'" class="mp3download" data-id="'.(int)$row->file_id.'" title="'.esc_html( $row->file_title).'" download>'.esc_html( $row->file_title).'</a>'."\r\n";
                        }
                        elseif(!empty( $row->external_file) )
                        {
                            //2023-09-17 Google drive broken so fix here..
                            $row->external_file = str_replace('export=download','export=open',$row->external_file );
                            
                            $out.='<p><audio class="sermonmp3" data-id="'.esc_html( $row->file_id).'" src="'.esc_url( $row->external_file).'" preload="auto" controls></audio></p>'."\r\n";

                                $download='<a href="'.esc_url( $row->external_file).'" class="mp3download" data-id="'.(int)$row->file_id.'" title="'.esc_html( $row->file_title).'" download>'.esc_html( $row->file_title).'</a>'."\r\n";
                        }
                        elseif(!empty($row->embed_code))
                        {
                            
                            
                            // WP's default allowed tags
                            global $allowedtags;

                            // allow iframe only in this instance
                            $iframe = array( 'iframe' => array(
                                                'src' => array (),
                                                'allow' => array(),
                                                'width' => array (),
                                                'height' => array (),
                                                'frameborder' => array(),
                                                'allowFullScreen' => array() // add any other attributes you wish to allow
                                                ) );

                            $allowed_html = array_merge( $allowedtags, $iframe );

                            // Sanitize user input.
                            
                            $out.= wp_kses( $row->embed_code, $allowed_html );
                        }

                        $out .="\t\t\t".'<hr/>'."\r\n";
                        $out .= "\t\t\t".'<div class="church-admin-excerpt">'."\r\n";
                            $out .= "\t\t\t\t".wp_kses_post(wpautop($sermon_description_excerpt))."\r\n";
                        $out .= "\t\t\t".'</div><!--excerpt end-->'."\r\n";
                        $out .= "\t\t\t".'<div class="church-admin-sermon-share">'."\r\n";
                            $out .= "\t\t\t\t".wp_kses_post($share)."\r\n";
                    $out .= "\t\t\t".'</div><!--sermon-share end-->'."\r\n";
                    $out.="\t\t".'</div>'."\r\n";
                $out .= "\t".'</div>'."\r\n";
                $out.='<script>var mp3nonce="'.esc_attr(wp_create_nonce("church_admin_mp3_play")).';"</script>';
        }
        else
        {
            $out .= '<div class="church-admin-sermons-container">'."\r\n";
            foreach($results AS $row)
            {
                add_filter('wp_img_tag_add_decoding_attr', '__return_false');
                // prepare output variables
                $video_detail = !empty($row->video_url) ? church_admin_generateVideoEmbedUrl( $row->video_url) : null;
                //image
                $image = '<img src="'.esc_url(plugins_url('/images/sermon.jpg',dirname(__FILE__) )).'" />';//default image
                if(!empty($row->series_image)){
                    $image = wp_get_attachment_image( $row->series_image,'large','',array('class'=>'church-admin-sermon-image') );
                }
                if(!empty($video_detail['image'])){

                    $image = '<img src="'.esc_url($video_detail['image']).'" class="church-admin-sermon-image" />';
                    

                }
                $sermon_title = !empty( $row->file_title ) ? $row->file_title: __('Sermon Title','church-admin');

                if(!empty( $row->file_name)&& file_exists( $path.$row->file_name) )
                {
                    $audio='<p><audio class="sermonmp3" data-id="'.esc_html( $row->file_id).'" src="'.esc_url( $url.$row->file_name).'" controls></audio></p>';
                    $download='<a href="'.esc_url( $url.$row->file_name).'" class="mp3download" data-id="'.(int)$row->file_id.'" title="'.esc_html( $row->file_title).'" download>'.esc_html( $row->file_title).'</a>'."\r\n";
                }
                elseif(!empty( $row->external_file) )
                {
                    //2023-09-17 Google drive broken so fix here..
                    $row->external_file = str_replace('export=download','export=open',$row->external_file );
                    
                    $audio='<p><audio class="sermonmp3" data-id="'.esc_html( $row->file_id).'" src="'.esc_url( $row->external_file).'" controls></audio></p>'."\r\n";

                        $download='<a href="'.esc_url( $row->external_file).'" class="mp3download" data-id="'.(int)$row->file_id.'" title="'.esc_html( $row->file_title).'" download>'.esc_html( $row->file_title).'</a>'."\r\n";
                }
                elseif(!empty($row->embed_code))
                {
                    
                    
                    // WP's default allowed tags
                    global $allowedtags;

                    // allow iframe only in this instance
                    $iframe = array( 'iframe' => array(
                                        'src' => array (),
                                        'allow' => array(),
                                        'width' => array (),
                                        'height' => array (),
                                        'frameborder' => array(),
                                        'allowFullScreen' => array() // add any other attributes you wish to allow
                                        ) );

                    $allowed_html = array_merge( $allowedtags, $iframe );

                    // Sanitize user input.
                    
                    $audio= wp_kses( $row->embed_code, $allowed_html );
                }




                $URL= get_permalink().'?sermon='.$row->file_slug;
                $sermon_description_excerpt = !empty( $row->file_description ) ?church_admin_excerpt( $row->file_description,150,'...') : '';
                $series = !empty( $row->series_name ) ? $row->series_name: __('Sermons','church-admin');
                $speaker = !empty( $row->speaker ) ? $row->speaker: __('Preacher','church-admin');
                $date = !empty( $row->pub_date ) ? mysql2date(get_option('date_format'),$row->pub_date): '';
                $escaped_sermon_link = '<a class="church-admin-sermon-link" href="'.esc_url($URL).'">'.esc_html($sermon_title).'</a>';
                $escaped_series_link = '<a class="church-admin-series-link"  href="'.esc_url(get_permalink().'?series='.urlencode($row->series_name)).'">'.esc_html($row->series_name).'</a>';
                $escaped_speaker_link = '<a class="church-admin-series-link"  href="'.esc_url(get_permalink().'?speaker='.urlencode($speaker)).'">'.esc_html($speaker).'</a>';
                
                
                       
                
                
                //actual output
                
                $out .= "\t".'<div class="church-admin-sermon-container">'."\r\n";
                    $out .= "\t\t".'<div class="church-admin-sermon-thumbnail">'."\r\n";
                        $out .= "\t\t\t".'<a class="church-admin-sermon-link" href="'.esc_url($URL).'">'.wp_kses_post($image).'</a>'."\r\n";
                    $out .= "\t\t".'</div><!--thumbnail-->'."\r\n";
                    $out .='<div class="church-admin-sermon-content">'."\r\n";
                    $out .= "\t\t\t\t".'<h3>'.$escaped_sermon_link.'</h3><p>'."\r\n";
                   
                    $out .= "\t\t\t".'<a class="church-admin-sermon-link" href="'.esc_url($URL).'">'.esc_html(__('Play sermon','church-admin')).'</a></p>'."\r\n";
                        $out .= "\t\t\t".'<div class="church-admin-sermon-meta">'."\r\n";
                            $out .= "\t\t\t\t".'<p class="church-admin-pub-date">'.esc_html($date).'</p><p class="church-admin-series_link">'.esc_html(__('Series:','church-admin') ).' '.$escaped_series_link.'</p>'."\r\n";
                        

                            if(!empty($row->plays) && empty($playnoshow)){
                                $out.="\t\t\t\t".'<p class="">'.esc_html(__('Audio plays:','church-admin') ).' '.(int)$row->plays.'</p>'."\r\n";
                            }
                        $out .= "\t\t\t".'</div><!--sermon meta end-->'."\r\n";
                        $out .= "\t\t\t".'<div class="church-admin-sermon-archive-details">'."\r\n";
                            
                            $out .= "\t\t\t".'<div class="church-admin-speaker">'."\r\n";
                                $out .= "\t\t\t\t".'<p>'.$escaped_speaker_link.'</p>'."\r\n";
                            $out .= "\t\t\t\t".'</div><!--speaker end-->'."\r\n";
                        $out .= "\t\t\t".'</div><!--archive details end-->'."\r\n";
                        $out .="\t\t\t".'<hr/>'."\r\n";
                        $out .= "\t\t\t".'<div class="church-admin-excerpt">'."\r\n";
                            $out .= "\t\t\t\t".wp_kses_post(wpautop($sermon_description_excerpt))."\r\n";
                        $out .= "\t\t\t".'</div><!--excerpt end-->'."\r\n";
                       
                    $out.="\t\t".'</div>'."\r\n";
                $out .= "\t".'</div>'."\r\n";
                
            }
            $out.='</div><!--sermons container end-->';
        }
        // Pagination
        $out.= $paging;
        //Pagination
        $out.='<script>
                        jQuery(document).ready(function($){
                       
                            $(".copy-me").click(function(){
                                console.log("copy me");
                                navigator.clipboard.writeText($(this).data(\'url\'));
                                alert("'.esc_html(__('Link copied to clipboard','church-admin')).'");
                            })
                        });</script>'."\r\n";
    }
    else
    {
        $out.='<p>'.esc_html( __('No sermons found','church-admin' ) ).'</p>';
    }
    
    return $out;
}