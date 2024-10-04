<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_small_group_list( $map=1,$zoom=1,$photo=FALSE,$loggedin=1,$title=NULL,$pdf=TRUE,$no_address=FALSE)
{
	$licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
	
	global $wpdb,$wp_locale;
    $out='<div class="church-admin-smallgroups">'."\r\n";

	if ( empty( $title) )$title=__('Small Groups PDF','church-admin');
	//show small groups
	if ( empty( $loggedin)||( $loggedin && is_user_logged_in() ))
	{
		
		if(!empty( $pdf)&&empty( $no_address) )$out.='<p><a  rel="nofollow" href="'.esc_url(home_url().'/?ca_download=smallgroups&title='. $title).'">'.esc_html( __('PDF of small groups','church-admin' ) ).'</a></p>'."\r\n";
		$key=get_option('church_admin_google_api_key');
		if(!empty( $key) )
		{
			//Get centre of groups
            $centre=church_admin_center_coordinates($wpdb->prefix.'church_admin_smallgroup');
			if(!empty( $centre)&& !empty( $map) )
			{
				
				$out.='<script>var xml_url="'.site_url().'/?ca_download=small-group-xml&small-group-xml='.esc_attr(wp_create_nonce('small-group-xml')).'";'."\r\n";
				if(!empty( $centre->lat) )  {$out.=' var lat='.esc_html( $centre->lat).';'."\r\n"; $zoom=13;}else{$out.=' var lat=0;'."\r\n";}
				if(!empty( $centre->lng) )  {$out.=' var lng='.esc_html( $centre->lng).';'."\r\n";}else{$out.=' var lng=0;'."\r\n";}
				if(!empty( $zoom) )  {$out.=' var zoom='.(int)$zoom.';';}else{$out.=' var zoom=0;'."\r\n";}
				$out.='jQuery(document).ready(function( $)  {
						console.log("Fire sgload");
						try{
							sgload(lat,lng,xml_url,zoom);
						}
						catch(error)
						{
							$("#group-map").html("'.esc_html( __('Map not loading currently','church-admin' ) ).'");
							$("#group-map").height(75);
						}
					});
					</script>'."\r\n";
				
				$out.='<div id="group-map" class="ca-small-group-map" >';
					
				$out.='</div>'."\r\n";
			}
		}
		$out.="\r\n";
		$leader=array();
			$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id!="1" ORDER BY group_day,group_time';
			$results = $wpdb->get_results( $sql);
			if(!empty( $results) )
			{
				
				if(!$photo)
				{
					$out.='<ul>';
					foreach ( $results as $row) 
					{
							if(!empty( $row->group_day) )
                            {
                                $day=$wp_locale->get_weekday( $row->group_day);
                            }
                            else $day='';
                            $out.='<li><strong>'.esc_html( $row->group_name).'</strong> <br>';
							if(!empty( $row->contact_number) )$out.='<a href="'.esc_url('tel:'.$row->contact_number).'">'.esc_html( $row->contact_number).'</a><br>';
							$out.=$day.' '.esc_html(mysql2date(get_option('time_format'),$row->group_time)).'<br>';
                            if ( empty( $no_address) )$out.=' '.esc_html( $row->address).'<br>';
                            if(!empty( $row->description) )$out.=esc_html( $row->description);
                            $out.='</li>';
							$out.="\r\n";
					}
					$out.='</ul>';
					$out.="\r\n";
				}
				else
				{
					/***************************
					*
					* Photo Style
					*
					****************************/
					$out.='<div class="ca-small-groups">';
					foreach ( $results as $row) 
					{
						church_admin_debug(print_r( $row,TRUE) );
						$out.='<div class="ca-small-group" id="group-'.(int)$row->id.'"><div class="ca-small-group-content">';
						$out.="\r\n";
						if(!empty( $row->attachment_id) )
						{
							$out.=wp_get_attachment_image( $row->attachment_id,'medium');
							$out.="\r\n";
						}
						else
						{
							$out.='<img id="smallgroup-image'.(int)$row->id.'"  src="'.esc_url(plugins_url('/', dirname(__FILE__) ) . 'images/household.svg').'" width="300" height="200" class="rounded frontend-image current-photo " alt="'.esc_html( __('Smallgroup image','church-admin' ) ).'" />';
							//$out.= '<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" class="frontend-image current-photo " alt="'.esc_html( __('Photo of Person','church-admin' ) ).'" id="frontend-image" />';
						}
						$out.="\r\n";
						$out.='<p><strong>'.esc_html( $row->group_name).'</strong></p>';
						$out.="\r\n";
						$frequency='';
						if(!empty( $row->frequency) )$frequency=$row->frequency;
						$out.='<p><i class="far fa-calendar-alt"></i>&nbsp; '.esc_html(sprintf(__('%1$s on %2$s','church-admin' ) ,$frequency,$wp_locale->get_weekday( $row->group_day) ) ).'</p>';
						$out.="\r\n";
						$out.='<p><i class="far fa-clock"></i> &nbsp;'.esc_html(mysql2date(get_option('time_format'),$row->group_time)) .'</p>';
						$out.="\r\n";
						if ( empty( $no_address) )$out.='<p> <i class="fas fa-map-marker-alt"></i>&nbsp;'.esc_html( $row->address).'</p>';
						$out.="\r\n";
						if(!empty( $row->contact_number) )$out.=esc_html( $row->contact_number);
						if(!empty( $row->description) )$out.='<p>'.wp_kses_post($row->description).'</p>';
						$out.="\r\n";
						if(!empty( $row->max_attendees) )
						{
							$noOfPeople=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup" AND ID="'.(int)$row->id.'"');
							$optimum=intval( $noOfPeople*0.75);
							$out.='<p><meter min="0" max="'.esc_attr($row->max_attendees).'" value="'.intval( $noOfPeople).'" optimum="'.esc_attr($optimum).'"></meter></p>';
							if(is_admin() ){
								$out.='<p>'.esc_html(sprintf(__('%1$s attendees out of a maximum %2$s','church-admin' ) ,$noOfPeople,$row->max_attendees) ).'</p>';
							}
						}
						$out.="\r\n";
						if(is_admin() )  {$out.='<p><a class="button-primary" href="'.esc_attr(wp_nonce_url("admin.php?page=church_admin/index.php&section=small_groups&amp;action=edit-small-group&id=".(int)$row->id,'edit-small-group')).'"><i class="far fa-edit"></i> '.esc_html( __('Edit small group','church-admin' ) ).'</a></p><p><a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=delete-group&section=small_groups&amp;id='.(int)$row->id,'delete-group').'">'.esc_html( __('Delete small group','church-admin' ) ).'</a></p>';}
						$out.="</div></div><!--small-group-->\r\n";
					}
					$out.="</div><div style='clear:left'></div><!--small-groups-->\r\n";
				}
			}
	}
	else{
        
        $out.='<p>'.esc_html(__('You need to be logged in to view our small groups','church-admin')).'</p>';
        $out.=wp_login_form(array('echo'=>FALSE) );
    }
	$out.'</div>';
	return $out;
}

