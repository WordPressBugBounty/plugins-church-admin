<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_podcast_display( $series_id=NULL,$file_id=NULL,$exclude=NULL,$most_popular=TRUE,$order='DESC',$limit=5,$nowhite=FALSE)
{
    
    church_admin_debug('***** church_admin_podcast_display *****');
    church_admin_debug(func_get_args());
    $licence =get_option('church_admin_app_new_licence');
    if($licence!='basic' && $licence!='standard' && $licence!='premium'){
        return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
        
    }
     global $wpdb;
    if(!is_user_logged_in() )  {$private=' AND private="0" ';}else{$private='';}
    if(!empty( $exclude)&&!is_array( $exclude) )$exclude=explode(",",$exclude);
    $out='<!-- sermon-podcast.php -->'."\r\n";
    $out.='<script>var nonce="'.wp_create_nonce('sermon-display').'";</script>'."\r\n";
    $out.='<div class="church-admin-sermons">'."\r\n";
    
    if(!empty( $_REQUEST['order'] ) && $_REQUEST['order']=='ASC')  {
        $order='ASC';
    }
    else
    {
        $order='DESC';
    }
    $latest_sermon=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE 1=1 '.$private.' ORDER BY pub_date '.$order.' LIMIT 1');
    $speaker = !empty($_REQUEST['speaker'])? church_admin_sanitize($_REQUEST['speaker']):null;
    $search = !empty($_REQUEST['search'])? church_admin_sanitize($_REQUEST['search']):null;
    $series_id =!empty($_REQUEST['series_id'])? church_admin_sanitize($_REQUEST['series_id']):$series_id;
    $file_id=null;
    $series_sql='';
    $series_sql_array=array();
    if(!empty($series_id)){$series_array = explode(',',$series_id);}
    if(!empty($series_array)){

        $series_sql='(';

        foreach($series_array AS $key=>$ser_id){
            $series_sql_array[]= ' series_id="'.(int)$ser_id.'" ';
        }
        $series_sql .= implode(' OR ',$series_sql_array);
        $series_sql .=')';

        $latest_sermon=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE '.$series_sql.' '.$private.' ORDER BY pub_date '.$order.' LIMIT 1');
        if(!empty($latest_sermon)){$file_id=$latest_sermon->file_id;}
    }

    
    $sermon_series = !empty( $_REQUEST['sermon-series'] )? sanitize_text_field(stripslashes( $_REQUEST['sermon-series'] )):null;
    if(!empty( $sermon_series ) )
    {
        //coming from sermon series link
        $series=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_slug="'.esc_sql($sermon_series).'"');
        if(!empty( $series) )
        {
            $series_id=(int)$series->series_id;
            $file_id=$wpdb->get_row('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE series_id="'.(int)$series_id.'" '.$private.' ORDER BY pub_date DESC LIMIT 1');
        }
    }
    $sermon_slug = !empty($_GET['sermon'])? sanitize_text_field(stripslashes($_GET['sermon'])):null;
    if(!empty( $sermon_slug ) )
    {
        $sermon=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_slug="'.esc_sql($sermon_slug ).'" '.$private.' LIMIT 1');
        if(!empty( $sermon) )
        {
              $file_id=$sermon->file_id;
            $series_id=$sermon->series_id;
        }
    }
    $page=!empty( $_GET['page'] )?sanitize_text_field((int)$_GET['page']):1;
    if(defined('CA_DEBUG') )church_admin_debug('latest file query...');
    if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
   
    //if ( empty( $series_id) )$series_id=$latest_sermon->series_id;
    /********************************************************
    *
    *   Output
    *
    *********************************************************/
    $out.='<div class="ca-podcast clearfix" id="ca-sermons">';
    $out.='<div class="ca-podcast-left-column">
                <div class="ca-series-current">'.church_admin_podcast_series_detail( $series_id,$exclude,$limit).'</div>
                <div class="ca-podcast-current">'.church_admin_podcast_file_detail( $file_id,$exclude,$nowhite).'</div>
               
                </div><!--.ca-podcast-list-->
            <div class="ca-podcast-list">
                <div class="ca-menu-area">'.church_admin_podcast_menu( $page,$limit,$series_id).'</div>
                <div class="ca-media-file-list">'.church_admin_podcast_files_list( $series_id,$page,$limit,$speaker,$search,$order,$series_sql).'</div>
            </div><!--.ca-podcast-list-->'; 
    
    $out.='</div><!--#ca-sermons-->';
    $out.='<script>var mp3nonce="'.esc_attr(wp_create_nonce("church_admin_mp3_play")).'"</script>';
    $out.='</div>';
    return $out;
}



function church_admin_podcast_files_list( $series_id=null,$page=1,$limit=5,$speaker=NULL,$search=NULL,$order='DESC',$series_sql=null)
{
    if(defined('CA_DEBUG') )
    {
        church_admin_debug('FUNCTION church_admin_podcast_files_list');
        church_admin_debug(print_r(get_defined_vars(),TRUE) );
    }
    global $wpdb;
    if(!empty( $order) )
    {
        switch( $order)
        {
            case'ASC':$order='ASC';break;    
            default:case'DESC':$order='DESC';break;   
        }
    }else $order='DESC';
    $out='';
    if(!is_user_logged_in() )  {$private=' AND private="0" ';}else{$private='';}
    $template='<div class="ca-media-list-item" data-id="{file_id}" data-date="{pub_date}" title="'.esc_html( __('Click to play sermon','church-admin' ) ).'"><span class="ca-dashicons dashicons dashicons-controls-play ca-play"></span><h3>{title}</h3><span class="ca-name">{speakers}</span><br>{human_date}</div>';
    $searchTerm='';
    if(!empty( $search) )
    {
        $searchTerm=sanitize_text_field( $search);
        $s='%'.esc_sql( $searchTerm).'%';
        
        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE (file_title LIKE "'.$s.'" OR file_description LIKE "'.$s.'" OR speaker LIKE "'.$s.'" OR bible_texts LIKE "'.$s.'") '.$private.' ORDER BY pub_date '.$order;
    }
    else
    {
        $SQL=array();
        if(!empty( $speaker) )
        {
            $speaker=sanitize_text_field( $speaker);
            if(!empty( $speaker) )$SQL[]=' AND speaker LIKE "%'.esc_sql( $speaker).'%"';
        }
        if(!empty( $series_sql) )
        {
            //Get Series Name
            $seriesNames=$wpdb->get_results('SELECT series_name FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE '.$series_sql);
            /*
            if(!empty( $seriesNames) ){
                $out.='<h2>'.esc_html(sprintf(__('%1$s series','church-admin' ) ,$seriesName)).'</h2>';
            }
            */
            if(!empty( $series_id) )$SQL[]= 'AND '.$series_sql;//series_id="'.(int)$series_id.'"';
        }
        if(!empty($series_id)&& church_admin_int_check($series_id) ) {$SQL[]='AND series_id="'.(int)$series_id.'"';}
        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE 1=1 '.implode(' ',$SQL).' '.$private.' ORDER BY pub_date '.$order; 
    }
    if(defined('CA_DEBUG') )church_admin_debug("List files SQL");
    if(defined('CA_DEBUG') )church_admin_debug( $sql);
    $countResult=$wpdb->get_results( $sql);
    $count=$wpdb->num_rows;
    if(defined('CA_DEBUG') )church_admin_debug("File Count: $count");
    if(defined('CA_DEBUG') )church_admin_debug("Page: $page");
    if ( empty( $limit) )$limit=5;
    if( $page==0)
    {
        $paged=0;
    }else
    {
        $paged=( $page-1)*$limit;
    }
    $newSQL=$sql.' LIMIT '.( $paged).','.$limit;
    if(defined('CA_DEBUG') )church_admin_debug("QUERY WITH  LIMIT ADDED\r\n".$newSQL);
    $results=$wpdb->get_results( $newSQL);
   
    if ( empty( $results) )return __('No sermons found');
    else
    {
        foreach( $results AS $row )
        {
            $thisFile=$template;
            $thisFile=str_replace('{file_id}',$row->file_id,$thisFile);
            $thisFile=str_replace('{pub_date}',$row->pub_date,$thisFile);
            $thisFile=str_replace('{title}',$row->file_title,$thisFile);
            $thisFile=str_replace('{speakers}',$row->speaker,$thisFile);
            $thisFile=str_replace('{human_date}',mysql2date(get_option('date_format').' '.get_option('time_format'),$row->pub_date),$thisFile);
            $out.=$thisFile."\r\n";
        }
    }

        $next=$page+1;
        $prev=$page-1;
        if( $prev>0)$out.='<span class="more-sermons prev-sermons btn btn-danger" data-page="'.(int)$prev.'" data-search="'.esc_attr( $search).'" data-series="'.esc_attr($series_id).'" data-limit="'.$limit.'" data-speaker="'.esc_html( $speaker).'">'.esc_html( __('Previous','church-admin' ) ).'</span>';
        if( $count>=( $limit*$page)&&$next>0){
            $out.='<span class="more-sermons next-sermons btn btn-danger" data-page="'.(int)$next.'" data-search="'.esc_attr( $search).'" data-series="'.esc_attr($series_id).'" data-limit="'.(int)$limit.'" data-speaker="'.esc_html( $speaker).'">'.esc_html( __('Next','church-admin' ) ).'</span>';
        }
    if(defined('CA_DEBUG') )
    {
        church_admin_debug("OUTPUT\r\n $out \r\n*****************");
        church_admin_debug('END OF church_admin_podcast_files_list');
    }
    return $out; 
}
function church_admin_podcast_menu( $page,$limit,$series_id)
{
    global $wpdb;
    $out='';
    $ca_podcast_settings=get_option('ca_podcast_settings');
    if(!empty( $ca_podcast_settings['itunes_link'] )||!empty( $ca_podcast_settings['spotify_link'] ) )$out.='<div class="ca-podcast-links">';
	if(!empty( $ca_podcast_settings['itunes_link'] ) )$out.='<a title="Download on Itunes" href="'.esc_url($ca_podcast_settings['itunes_link']).'">
<img  alt="Apple Podcasts" src="'.esc_url(plugins_url('/images/apple-podcasts.png',dirname(__FILE__) )).'" width="155" height="40" /></a>';
    if(!empty( $ca_podcast_settings['spotify_link'] ) )$out.='&nbsp; <a title="Download on Spotify" href="'.esc_url($ca_podcast_settings['spotify_link']).'">
<img alt="Spotify" class="ca-podcast-logo" src="'.esc_url(plugins_url('/images/spotify.png',dirname(__FILE__) )).'" width="110" height="40" /></a>';
if(!empty( $ca_podcast_settings['amazon_link'] ) )$out.='&nbsp; <a title="Download on Amazon" href="'.esc_url($ca_podcast_settings['amazon_link']).'">
<img alt="Amazon Music" class="ca-podcast-logo" src="'.esc_url(plugins_url('/images/amazon-podcast.png',dirname(__FILE__) )).'" width="110" height="40" /></a>';
    if(!empty( $ca_podcast_settings['itunes_link'] )||!empty( $ca_podcast_settings['spotify_link'] ) )$out.='</div>';
    //search
    $out.='<div class="ca-podcast-search church-admin-form-group">
    <input type="text" class="sermon-search" name="sermon-search" placeholder="'.esc_html( __('Search sermons','church-admin' ) ).'"><button class="ca-sermon-search btn btn-secondary" data-page="'.(int)$page.'" data-limit="'.(int)$limit.'"  type="button">?</button></div>';
    //series
    $series=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY last_sermon DESC');
    if(!empty( $series) )
    {
        $out.='<div class="church-admin-form-group">';
        $out.='<select data-page="'.(int)$page.'" data-limit="'.(int)$limit.'" class="ca-series-dropdown" name="series_id">'."\r\n";
        $out.='<option value="">'.esc_html( __('Or choose Series','church-admin' ) ).'</option>'."\r\n";
        foreach( $series AS $seriesRow)
        {
            $out.="\t".'<option value="'.(int)$seriesRow->series_id.'">'.esc_html( $seriesRow->series_name).'</option>'."\r\n";
        }
        $out.='</select></div>'."\r\n";
    }
    //speaker
    $speakers=$wpdb->get_results('SELECT DISTINCT(speaker) FROM '.$wpdb->prefix.'church_admin_sermon_files');
    if(!empty( $speakers) )
    {
        $speakerArray=array();
        foreach( $speakers AS $speaker)
        {
            if(strpos( $speaker->speaker,",") )
            {
                
                $speakers=array_filter(explode(",",$speaker->speaker) );
                foreach( $speakers AS $key=>$name)
                {
                    $name=str_replace("  "," ",trim( $name) );
                    if(!empty( $name) && !in_array( $name,$speakerArray) )$speakerArray[]=$name;
                }
               
                
            }else $speakerArray[]=trim( $speaker->speaker);
        }
        
        $speakerArray=array_unique(array_filter( $speakerArray),SORT_STRING );
        asort( $speakerArray);
        $out.='<div class="church-admin-form-group">'."\r\n";
        $out.='<select data-page="'.(int)$page.'" data-limit="'.(int)$limit.'" class="ca-speaker-dropdown" name="speaker">'."\r\n";
        $out.='<option value="">'.esc_html( __('Or choose Speaker','church-admin' ) ).'</option>'."\r\n";
        foreach( $speakerArray AS $key=>$name)
        {
            $out.="\t".'<option value="'.esc_html(trim( $name) ).'">'.esc_html(trim( $name) ).'</option>'."\r\n";
        }
        $out.='</select></div>'."\r\n";
        $out.='<div class="church-admin-form-group"><select data-page="'.(int)$page.'" data-limit="'.(int)$limit.'" class="ca-order" name="order"><option value="DESC">'.esc_html( __('Newest first','church-admin' ) ).'</option><option value="ASC">'.esc_html( __('Oldest First','church-admin' ) ).'</option></select></div>'."\r\n";
    }
    return $out;
}

 function church_admin_podcast_series_detail( $series_id=NULL,$exclude=array() )
 {
 		global $wpdb,$podcastSettings;
        if ( empty( $exclude) )$exclude=array();
 		$out='';

 		if(!empty( $series_id) )
 		{
 			$detail=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_id="'.(int)$series_id.'" ');
 			if(!empty( $detail) )
 			{

 				if(!empty( $detail->series_name)&&!in_array('seriesName',$exclude) )  {
                    $out.='<h2>'.esc_html(sprintf(__('%1$s series','church-admin' ) ,esc_html( $detail->series_name) ) ).'</h2>';
                }
 				if(!empty( $detail->series_image)&&!in_array('seriesImage',$exclude) )  {
                    $out.='<p>'.wp_get_attachment_image( $detail->series_image,'large','',array('class'=>'img-responsive') ).'</p>';
                }

 				if(!empty( $detail->series_description)&&!in_array('seriesDescription',$exclude) )  {
                    $out.='<p>'.esc_html($detail->series_description).'</p>';
                }
 			}


 		}

 	return $out;

 }
  
function church_admin_podcast_file_detail( $fileID,$exclude=NULL,$nowhite=FALSE)
{
    
	global $wpdb,$wp_embed,$post;
    $podcastSettings=get_option('ca_podcast_settings');
	if ( empty( $exclude) )$exclude=array();
	$out='';
	$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/sermons/';
		$url=$upload_dir['baseurl'].'/sermons/';
	if(!is_user_logged_in() )  {$private=' AND a.private="0" ';}else{$private='';}
	if ( !empty( $fileID) )  {
        $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a LEFT JOIN  '.$wpdb->prefix.'church_admin_sermon_series b ON a.series_id=b.series_id WHERE a.file_id="'.(int) $fileID.'" '.$private;
    }
    else{
        $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a LEFT JOIN '.$wpdb->prefix.'church_admin_sermon_series b ON a.series_id=b.series_id ORDER BY a.pub_date DESC LIMIT 1';

    }
		
    	$data=$wpdb->get_row( $sql);
       
        if ( empty( $data) )  {return("<p>There is no file to display</p>");}
		else
		{
			
			//now playing tab
				if(!empty( $data->file_title) )$out.='<h2 class="ca-sermon-title">'.esc_html( $data->file_title).'</h2>';
                if(!empty( $data->file_subtitle)&&!in_array('subtitle',$exclude) )$out.='<p class="ca-sermon-subtitle">'.$data->file_subtitle.'</p>';
				if(!empty( $data->video_url) )
                {
                    if(strpos( $data->video_url, 'amazonaws.com/') !== false)
                    {
                       $out.='<video class="ca-video" width="560" height="315" controls><source src="'.esc_url($data->video_url).'" type="video/mp4">Your browser does not support the video tag./video>'."\r\n"; 
                    }else
                    {
                        $video=church_admin_generateVideoEmbedUrl( $data->video_url);
                        $videoUrl=$video['embed'];
                        if(!empty( $nowhite) )
                        {
                            $out.='<iframe class="ca-video" src="'.esc_url($video['embed']).'" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                        }
                        else 
                        {
                            $out.='<div style="width:100%"><div style="position:relative;padding-top:56.25%"><iframe class="ca-video" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.esc_url($video['embed']).'" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>'."\r\n";
                        }
                        $views=church_admin_youtube_views_api( $video['id'] );
                    }
                }

				if(!empty( $data->file_name)&& file_exists( $path.$data->file_name) )
                {
                    $out.='<p><!--file_name method--><audio class="sermonmp3" data-id="'.esc_html( $data->file_id).'" src="'.esc_url( $url.$data->file_name).'" preload="auto" controls></audio></p>';
				    if(!in_array('download',$exclude) )$download='<a href="'.esc_url( $url.$data->file_name).'" class="mp3download" data-id="'.(int)$data->file_id.'" title="'.esc_html( $data->file_title).'" download>'.esc_html( $data->file_title).'</a>'."\r\n";
				}
				elseif(!empty( $data->external_file) )
				{
                    $data->external_file = str_replace('export=download','export=open',$data->external_file );
					$out.='<p><!-- external --><audio class="sermonmp3" data-id="'.esc_html( $data->file_id).'" src="'.esc_url( $data->external_file).'" preload="auto" controls></audio></p>'."\r\n";
                    $download='<a href="'.esc_url( $data->external_file).'" class="mp3download" data-id="'.(int)$data->file_id.'" title="'.esc_html( $data->file_title).'" download>'.esc_html( $data->file_title).'</a>'."\r\n";
				}
                elseif(!empty($data->embed_code))
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
                    
                    $out.= wp_kses( $data->embed_code, $allowed_html );
                }



				if(!in_array('nowPlaying',$exclude) )
				{
					
							
					
						
						$plays=church_admin_plays( $data->file_id);
						$out.='<div class="ca-podcast-file-content ca-tab-content">';
							if(!empty( $data->file_description)&&!in_array('fileDescription',$exclude) )$out.='<div class="sermon-file-description">'.wp_kses_post($data->file_description).'</div>';
							$out.='<table>';
								if(!empty( $data->speaker)&&!in_array('speaker',$exclude) )$out.='<tr><td>'.esc_html( __('Speaker','church-admin' ) ).':&nbsp;</td><td class="ca-names">'.esc_html( $data->speaker).'</td></tr>';
								if(!empty( $data->series_name)&&!in_array('seriesName',$exclude) )$out.='<tr><td>'.esc_html( __('Series','church-admin' ) ).':&nbsp;</td><td>'.esc_html( $data->series_name).'</td></tr>';
								if(!empty( $data->pub_date)&&!in_array('date',$exclude) )$out.='<tr><td>'.esc_html( __('Date','church-admin' ) ).':&nbsp;</td><td>'.esc_html(mysql2date(get_option('date_format'),$data->pub_date)).'</td></tr>';
								if(!empty( $download)&&!in_array('download',$exclude) )$out.='<tr><td>'.esc_html( __('Download','church-admin' ) ).':&nbsp;</td><td>'.$download.'</td></tr>';
								$showPlays=FALSE;
                                if(!empty( $data->file_name) )$showPlays=TRUE;
                                if(!empty( $data->external_file) )$showPlays=TRUE;
                                if(isset( $plays)&&!in_array('plays',$exclude)&&$showPlays)
                                {
                                    $out.='<tr><td>'.esc_html( __('Plays','church-admin' ) ).':&nbsp;</td><td class="plays">'.$plays.'</td></tr>';
                                }
                                if(!empty( $views)&&!in_array('views',$exclude)&&$showPlays)
                                {
                                    $out.='<tr><td>'.esc_html( __('Views','church-admin' ) ).':&nbsp;</td><td class="views">'.$views.'</td></tr>';
                                }
								if(!empty( $data->bible_texts) )
								{
									$pass=array();
									$version=get_option('church_admin_bible_version');
									$passages=explode(",",$data->bible_texts);
									if(!empty( $passages)&&is_array( $passages) )
									{
										foreach( $passages AS $passage)$pass[]='<a href="https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.$version.'&interface=print" target="_blank">'.esc_html( $passage).'</a>'."\r\n";

										if(!in_array('bible',$exclude) )$out.='<tr><td>'.esc_html( __('Scriptures','church-admin' ) ).':&nbsp;</td><td>'.implode(", ",$pass).'</td></tr>';
									}
								}
                                if(!empty( $data->transcript) )
                                {
                                    $out.='<tr><td>'.esc_html( __('Sermon Notes','church-admin' ) ).':&nbsp;</td><td><a  rel="nofollow" href="'.site_url().'?ca_download=sermon-notes&amp;file_id='.(int)$data->file_id.'">PDF</a></td></tr>';
                                }
                                if(!in_array('sharing',$exclude) )
                                {
                                    $URL=church_admin_find_sermon_page(); 
                                    $url=esc_url( $URL.'?sermon='.$data->file_slug);
                                    $title=urlencode(str_replace('"','',$data->file_title) );
                                    //facebook
                                    $share='<a target="_blank" class="ca-share" style="text-decoration:none" href="'.esc_url('https://www.facebook.com/sharer/sharer.php?u='.$URL.'?sermon='.$data->file_slug).'"><span class="ca-dashicons dashicons dashicons-facebook"></span></a> &nbsp;'."\r\n";
                                    //twitter
                                    $share.='<a  class="ca-share" style="text-decoration:none" target="_blank"  href="'.esc_url('https://twitter.com/intent/tweet?text='.$title.'&url='.$url).'"><span class="ca-dashicons dashicons dashicons-twitter"></span></a>&nbsp;'."\r\n";
                                    //email
                                    $share.='<a style="text-decoration:none" href="'.esc_url('mailto:?subject='.$title.'&amp;body='.$url).'"><span class="ca-dashicons dashicons dashicons-email"></span></a>&nbsp;'."\r\n";
                                    //link
                                    $share.='<a class="copy-me" style="text-decoration:none" data-url="'.esc_url($URL.'?sermon='.$data->file_slug).'"><span class="ca-dashicons dashicons dashicons-admin-links"></span></a>'."\r\n";
                                    $share.='<script>
                                        jQuery(document).ready(function($){
                                            $(".copy-me").click(function(){
                                                console.log("copy me");
                                                navigator.clipboard.writeText($(this).data(\'url\'));
                                                alert("'.esc_html(__('Link copied to clipboard','church-admin')).'");
                                            })
                                        });</script>';
                                    //sms
                                    $share.='<a href="'.esc_url('sms:?body='.$url).'"><span class="ca-dashicons dashicons dashicons-smartphone" style="text-decoration:none"></span></a>'."\r\n";

                                    $out.='<tr style="vertical-align:middle"><td>'.esc_html( __('Share this sermon','church-admin' ) ).':&nbsp;</td><td>'.$share.'</td></tr>'."\r\n";
                                    
                                }
							$out.='</table>'."\r\n";
							$out.='</div><!--ca-podcast-file-content-->'."\r\n";
				}
						

					
					
				
		}
	
    $out.='<script>var mp3nonce="'.wp_create_nonce("church_admin_mp3_play").'";</script>'."\r\n";
	$out=do_shortcode( $out);
	return $out;
}


function church_admin_player( $file_id)
{
    global $wpdb,$podcastSettings;
    if(!is_user_logged_in() )  {$private=' AND a.private="0" ';}else{$private='';}
    $sermon=$wpdb->get_var('SELECT file_name FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.(int)$file_id.'" '.$private);
    if(!empty( $sermon) )
    {
        $upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/sermons/';
		$url=$upload_dir['baseurl'].'/sermons/';
        if(!empty( $sermon) )
        {
            $out='<p><audio class="sermonmp3" data-id="'.esc_html( $file_id).'" src="'.esc_url( $url.$sermon).'" preload="auto" controls></audio></p>';
            $out.='<script>var mp3nonce="'.esc_attr(wp_create_nonce("church_admin_mp3_play")).'";</script>';
        }
    }else{$out=__('No sermon file found','church-admin');}
    return $out;

}
    
