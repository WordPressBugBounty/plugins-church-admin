<?php	
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly    
    function church_admin_bible_reading_shortcode()
    {
		$licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
			
		}
       /* global $wpdb;
        $out=array();
        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_brplan WHERE ID="'.date('z').'"';
        
		$data=$wpdb->get_row( $sql);
		if ( empty( $version) )$version=get_option('church_admin_bible_version');
		if ( empty( $version) )$version="ESV";
		if(!empty( $data->readings) )$readings=maybe_unserialize( $data->readings);
		if(!empty( $readings) )
		{
			foreach( $readings AS $key=>$value)
			{
				$out[]='<p class="ca-bible-reading"><a href="https://www.biblegateway.com/passage/?search='.urlencode( $value).'&version='.urlencode( $version).'&interface=print" >'.esc_html( $value).'</a></p>';
			}
		}else $out=array('error'=>'No passages');
    
        $output=implode("\r\n",$out);
    return $output;   
    
    */
    global $wpdb;
     $version=get_option('church_admin_bible_version');   
    $date=wp_date('Y-m-d');    
    $ID=wp_date('z')+1; 
    $headphonesSVG='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6 23v-11c-4.036 0-6 2.715-6 5.5 0 2.807 1.995 5.5 6 5.5zm18-5.5c0-2.785-1.964-5.5-6-5.5v11c4.005 0 6-2.693 6-5.5zm-12-13.522c-3.879-.008-6.861 2.349-7.743 6.195-.751.145-1.479.385-2.161.716.629-5.501 4.319-9.889 9.904-9.889 5.589 0 9.29 4.389 9.916 9.896-.684-.334-1.415-.575-2.169-.721-.881-3.85-3.867-6.205-7.747-6.197z" /></svg>';
	//check to see if there is a post in bible-readings for the date

	$sql='SELECT * FROM '.$wpdb->posts.' WHERE post_type="bible-readings" AND DATE_FORMAT(post_date, "%Y-%m-%d")="'.esc_sql($date).'" AND (post_status="publish" OR post_status="future")';

	$bible_readings=$wpdb->get_results( $sql);

	if(!empty( $bible_readings) )
	{//use the Bible Reading post type
		foreach( $bible_readings AS $bible_reading)
		{
			$output='<h2>'.esc_html( $bible_reading->post_title).'</h2>';
			$passage=get_post_meta( $bible_reading->ID ,'bible-passage',TRUE);
			$output.='<p class="ca-bible-reading"><a href="'.esc_url('https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.urlencode( $version).'&interface=print').'"  >'.esc_html( $passage).'</a></p>';
            
            $bibleCV=church_admin_bible_audio_link( $passage,$version);
            if(!empty( $bibleCV['url'] ) )$output.='<p><a href="'.esc_url($bibleCV['url']).'">'.$headphonesSVG.' '.$bibleCV['linkText'].'</a></p>';
            
			$output.='<p>'.wp_kses_post(nl2br(do_shortcode( $bible_reading->post_content),TRUE)).'</p>';
			$output.='<p>'.wp_kses_post(get_the_author_meta( $bible_reading->post_author)).'</p>';
			$out[]=$output;
			
		}

	}
	else
	{//use the old style bible reading plan
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_brplan WHERE ID="'.(int)$ID.'"';
		$data=$wpdb->get_row( $sql);
		$version=$_GET['version'];
		if ( empty( $version) )$version=get_option('church_admin_bible_version');
		if ( empty( $version) )$version="ESV";
		$readings=maybe_unserialize( $data->readings);
		if(!empty( $readings) )
		{
			foreach( $readings AS $key=>$passage)
			{
				$bibleCV=church_admin_bible_audio_link( $passage,$version);
                $output='<p class="ca-bible-reading"><a href="https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.urlencode( $version).'&interface=print" >'.esc_html( $passage).'</a></p>';
                
               
                if(!empty( $bibleCV['url'] ) )$output.='<p><a href="'.esc_url($bibleCV['url']).'">'.$headphonesSVG.' '.esc_html($bibleCV['linkText']).'</a></p>';
            
                $out[]=$output;
			}
		}else $out=array('error'=>'No passages');
	}
        return implode("<hr/>",$out);
    }