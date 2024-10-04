<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
function church_admin_calendar_list( $days=28,$category=NULL,$fac_ids=NULL)
{
	$licence =get_option('church_admin_app_new_licence');
		if($licence!='basic' && $licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
	//church_admin_debug("DISPLAY: church_admin_calendar_list");
	global $wpdb,$wp_locale;
	$out='';
	$params = array('ca_download'=>'monthly-calendar-pdf');
    if(!empty($cat_id))$params['cat_id']=$cat_id;
    if(!empty($fac_ids))$params['facilities_id']=$fac_ids;

    $url = add_query_arg( $params , site_url() );
	$out.='<p><a href="'.$url.'">'.__('This calendar PDF','church-admin').'</a></p>';


	//work out dates
	if(!empty( $_POST['date'] )&& church_admin_checkdate( sanitize_text_field(stripslashes($_POST['date'])) ) )  {
		$date=new ChurchAdminDateTime( $_POST['date'] );
	}else{
		$date=new ChurchAdminDateTime();
	}
	
	$sqlstart=$date->format('Y-m-d');
	$nowDisplayDate=$date->format(get_option('date_format') );
	$end=$date->returnAdd(new DateInterval('P'.$days.'D') );
	$nextDisplay=$sqlend=$end->format('Y-m-d');
	$next=$sqlend;
	$prevDate=$date->returnSub(new DateInterval('P'.$days.'D') );
	$prevDisplay=$prevDate->format('Y-m-d');
	
	
	$display_categories = $display_facilities = array();
	
	$facilities=church_admin_calendar_facilities_array();
	$categories=church_admin_calendar_categories_array();
	//process categories
	$cat_sql='';
	$catsql=array();
  	if ( !empty( $category) ){
  		
  		$cats=explode(',',$category);
      	foreach( $cats AS $key=>$value)  {
			if(church_admin_int_check( $value) )  {
				$catsql[]='a.cat_id='.(int)$value;
				$display_categories[]=$categories[$value];
			}
		}
      	if(!empty( $catsql) ) {$cat_sql=' AND ('.implode(' || ',$catsql).')';}
		
	}
	//process facilities
	$facsql=array();
	$fac_sql='';
	if ( !empty( $fac_ids) ){
		$facs=explode(",",$fac_ids);
		foreach( $facs AS $key=>$value)  {
			if(church_admin_int_check( trim($value) ) )  {
				$facsql[]='c.meta_value='.(int)$value;
				$display_facilities[]=$facilities[$value];
			}
		}
		$FACSQL = !empty($facsql) ? ' AND ('.implode(' OR ',$facsql).')':'';
		$sql='SELECT a.*, b.*,c.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b , '.$wpdb->prefix.'church_admin_calendar_meta c  WHERE a.cat_id=b.cat_id AND a.event_id=c.event_id AND c.meta_type="facility_id" AND a.start_date BETWEEN CAST("'.esc_sql($sqlstart).'" AS DATE) AND CAST("'.esc_sql($sqlend).'" AS DATE) '.$cat_sql.' '.$FACSQL.' ORDER BY a.start_date,a.start_time';
		
	}
	else
	{
		//no facilities ID query
		$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON a.cat_id=b.cat_id  WHERE a.general_calendar=1 AND  a.start_date BETWEEN CAST("'.esc_sql($sqlstart).'" AS DATE) AND CAST("'.esc_sql($sqlend).'" AS DATE) '.$cat_sql.' ORDER BY a.start_date,a.start_time';
		


	}

	//church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	
	$data=array();
	if(!empty( $results) )
	{
		//build $data for outputing
		foreach( $results AS $row)
		{
			$rota = null;
			if(!empty($row->service_id)){
				/********************************************
				 * Is a service, so retrieve schedule data
				 *******************************************/
				$rota = church_admin_retrieve_rota_data_array($row->service_id,$row->start_date,'service');
			}
			if($row->bgcolor=='#FFFFFF'){$row->bgcolor="#CCCCCC";}
			if( $row->start_time=='00:00:00' && $row->end_time=='23:59:00')
    		{//all day
				$data[]=array(
								'id'=>(int)$row->date_id,
								'date'=>esc_html(mysql2date(get_option('date_format'),$row->start_date)),
								'time'=>esc_html( __('All Day','church-admin' ) ),
								'title'=>sanitize_text_field( $row->title ),
								'description'=>sanitize_text_field( $row->description) ,
                                'link'=>$row->link,
                                'link_title'=>$row->link_title,
								'location'=>$row->location,
								'bgcolor'=>$row->bgcolor,
								'textcolor'=>$row->text_color,
								'rota'=>$rota,
								'service_id'=>$row->service_id
							);
			}
			{//timed
				$data[]=array(
								'id'=>(int)$row->date_id,
								'date'=>mysql2date(get_option('date_format'),$row->start_date),
								'time'=>mysql2date(get_option('time_format'),$row->start_time)." - ".mysql2date(get_option('time_format'),$row->end_time),
								'title'=>esc_html(sanitize_text_field( $row->title) ),
								'description'=>esc_html(sanitize_text_field( $row->description) ),
                                'link'=>$row->link,
                                'link_title'=>$row->link_title,
								'location'=>$row->location,
								'bgcolor'=>$row->bgcolor,
								'textcolor'=>$row->text_color,
								'rota'=>$rota,
								'service_id'=>$row->service_id
							);
			}
		}
	}//got results
	if(!is_admin() )
	{//Chooser
		$out.='<table class="ca-calendar-list-chooser widefat striped bordered"><tr><td class="ca-calendar-list-prev">';
		$out.='<form action="'.get_permalink().'"  method="post"><input type="hidden" name="date" value="'.$prevDisplay.'" /><input class="calendar-date-switcher" type="submit" value="'.esc_html( __('Previous','church-admin' ) ).'" /></form></td>';
			

		$title= sprintf(__( 'Calendar for the next %1$s days','church-admin' ) ,(int)$days);
		$detail='';
		if(!empty($display_categories) || !empty($display_facilities)){
			$detail.='<br> (';
			if(!empty($display_categories)){
				$detail.= __('Categories: ','church-admin').implode(', ',$display_categories);
			}
			if(!empty($display_categories)){
				$detail.= __('Facilities: ','church-admin').implode(', ',$display_facilities);
			}
			$detail.=')';
		}

		$out.='<td class="ca-calendar-list-chooser">'.wp_kses_post( $title.$detail ).'</td>';
		$out.='<td class="ca-calendar-list-next"><form action="'.esc_url( get_permalink() ).'"  method="post"><input type="hidden" name="date" value="'.esc_attr($nextDisplay).'" /><input class="calendar-date-switcher" type="submit" value="'.esc_html( __('Next','church-admin' ) ).'" /></form></td>';
		$out.='</tr></table>';
	}
	//build output
	if ( empty( $data) )
	{	
		$out.='<p>'.esc_html( sprintf(__('No events to display in the next %1$d days','church-admin' ),(int)$days) ).'</p>';
	}
	else
	{
		$out.='<table class="ca-calendar-list  widefat striped bordered"><thead><tr><th class="ca-list-date">'.esc_html( __('Date','church-admin' ) ).'</th><th class="ca-list-time">'.esc_html( __('Time','church-admin' ) ).'</th><th class="ca-list-event">'.esc_html( __('Event','church-admin' ) ).'</th></tr></thead><tbody>';
		foreach( $data AS $key=>$event)
		{
			church_admin_debug($event);
			$out.=	'<tr>
						<td class="ca-list-date">'.esc_html($event['date']).'</td>
						<td class="ca-list-time">'.esc_html($event['time']).'</td>
						<td class="ca-list-event" style="border-left:8px SOLID '.esc_attr($event['bgcolor']).'"><strong>'.esc_html($event['title']).'</strong>';
			if(!empty($row->description)){
				 $out.='<br>'.$event['description'];
			}
			if(!empty( $event['location'] ) ){
				$out.='<br>'.esc_html(sprintf(__('Location: %1$s','church-admin' ) ,esc_html( $event['location'] ) ) );
			}
            if(!empty( $event['link'] ) )
            {
                if(!empty( $event['link_title'] ) )  {$title=esc_html( $event['link_title'] );}else{$title=__('More information...','church-admin');}
                $out.='<br/><a href="'.esc_url( $event['link'] ).'">'.$title.'</a>';
            }
			if(!empty($event['rota'])){
				$out.='<table><tr ><th scope="row" data-id="'.(int)$event['id'].'">'.esc_html('Who is doing what','church-admin').'</th><td><span class="rota-expander dashicons dashicons-arrow-up-alt2" id="toggle'.(int)$event['id'].'" data-what="show" data-id="'.(int)$event['id'].'" ></span></td></tr>';
				foreach($event['rota'] AS $job=>$who){
					$out.='<tr class="rota-details date'.(int)$event['id'].'" style="display:none"><th scope="row">'.esc_html($job).'</th><td>'.esc_html($who).'</td></tr>';
				}
				$out.='</table>';
			}
			if(!empty($row->service_id)){

				
                //add link to rota for this service
               if(church_admin_level_check('Rota')){
                    $out.='<p><a href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=edit-rota&service_id='.(int)$event['service_id'].'&rota_date='.esc_attr($event['date']),'edit-rota').'">'.esc_html(__('Edit schedule','church-admin')).'</a></p>';
               }
               
               $out.=church_admin_rota_popup($row->service_id,$row->start_date);
			}
			$out.= '<br/><a  rel="nofollow" class="vcf-link" href="'.site_url().'?ca_download=ical&date_id='.(int)$event['id'].'"><span class="ca-dashicons dashicons dashicons-download"></span>'.esc_html(__('iCal download','church-admin')).'</a>';
            $out.='</td></tr>';
		}
		$out.='</tbody></table>';
	}
	$out.='<script>
	jQuery(document).ready(function($){
		$(".rota-expander").click(function(){
			console.log("click");
			$(".rota-details").hide();
			var id	=$(this).attr("data-id");
			console.log(id);
			var what = $(this).attr("data-what");
			if(what==="show"){
				$(this).attr("data-what","hide");
				$(".date"+id).show();
				$("#toggle"+id).removeClass("dashicons-arrow-up-alt2");
				$("#toggle"+id).addClass("dashicons-arrow-down-alt2");
			}else
			{
				$(this).attr("data-what","show");
				$("#toggle"+id).addClass("dashicons-arrow-up-alt2");
				$("#toggle"+id).removeClass("dashicons-arrow-down-alt2");
			}
		});
	})</script>';
	
	
	return $out;
}