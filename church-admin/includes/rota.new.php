<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
/********************************************
*
*
*	Reconfigured for $wpdb->prefix.'church_admin_new_rota':
* 	January 2017
*
*********************************************/


/**
 *
 * displays rota for $service_id
 *
 * @author  Andy Moyle
 * @param    $service_id
 * @return   html string
 * @version  0.1
 *
 *
 */

function church_admin_rota_list( $service_id=NULL)
{
	if(!church_admin_level_check('Rota') )wp_die(__('You don\'t have permission to do that','church-admin') );
	//initialise
	global $wpdb,$wp_locale;
	$mtg_type='service';

	$expected_frequency = array('ah'=>__('Ad hoc','church-admin'),
	'1'=>__('Daily','church-admin'),
	'14'=>__('Fortnightly','church-admin'),
	'm'=>__('Monthly','church-admin'),
	'a'=>__('Annually','church-admin'),
	'70'=>__('Weekly on Sunday','church-admin'),
	'71'=>__('Weekly on Monday','church-admin'),
	'72'=>__('Weekly on Tuesday','church-admin'),
	'73'=>__('Weekly on Wednesday','church-admin'),
	'74'=>__('Weekly on Thursday','church-admin'),
	'75'=>__('Weekly on Friday','church-admin'),
	'76'=>__('Weekly on Saturday','church-admin'),
	'n10'=>__('First Sunday','church-admin'),
	'n11'=>__('First Monday','church-admin'),
	'n12'=>__('First Tuesday','church-admin'),
	'n13'=>__('First Wednesday','church-admin'),
	'n14'=>__('First Thursday','church-admin'),
	'n15'=>__('First Friday','church-admin'),
	'n16'=>__('First Saturday','church-admin'),
	'n20'=>__('Second Sunday','church-admin'),
	'n21'=>__('Second Monday','church-admin'),
	'n22'=>__('Second Tuesday','church-admin'),
	'n23'=>__('Second Wednesday','church-admin'),
	'n24'=>__('Second Thursday','church-admin'),
	'n25'=>__('Second Friday','church-admin'),
	'n26'=>__('Second Saturday','church-admin'),
	'n30'=>__('Third Sunday','church-admin'),
	'n31'=>__('Third Monday','church-admin'),
	'n32'=>__('Third Tuesday','church-admin'),
	'n33'=>__('Third Wednesday','church-admin'),
	'n34'=>__('Third Thursday','church-admin'),
	'n35'=>__('Third Friday','church-admin'),
	'n36'=>__('Third Saturday','church-admin'),
	'n40'=>__('Fourth Sunday','church-admin'),
	'n41'=>__('Fourth Monday','church-admin'),
	'n42'=>__('Fourth Tuesday','church-admin'),
	'n43'=>__('Fourth Wednesday','church-admin'),
	'n44'=>__('Fourth Thursday','church-admin'),
	'n45'=>__('Fourth Friday','church-admin'),
	'n46'=>__('Fourth Saturday','church-admin'),
);


	echo'<h2>'.esc_html(__('Service Schedule','church-admin')).'</h2>';
	echo'<p><a class="button-secondary" href="https://www.churchadminplugin.com/tutorials/rota-schedule/"><span class="dashicons dashicons-bigger dashicons-welcome-learn-more"></span> Tutorial</a></p>';
	if ( empty( $service_id) )
	{
		//look for first service
		$service_id=$wpdb->get_var('SELECT service_id FROM '.$wpdb->prefix.'church_admin_services WHERE active=1  ORDER BY service_id ASC LIMIT 1');

		if ( empty( $service_id) )
		{
			echo  '<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&section=rota&amp;action=edit-service",'edit-service').'">'.esc_html( __('Please set up a service first','church-admin' ) ).'</a></p>';
			return; 
		}

	}
	/*******************************
	 * CHOOSE service and month
	 *******************************/
	$sql='SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.active=1';
    $services=$wpdb->get_results( $sql);
	if(empty($services)){
		return '<div class="notice notice-inline notice-warning"><h2 style="color:red">'.esc_html( __('Please create a service first','church-admin' ) ).'</h2></div>';
		}
	echo'<form action="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&amp;action=rota','rota').'" method="POST">';
	wp_nonce_field('rota');
	echo'<div class="church-admin-form-group"><label>'.esc_html('Choose a service','church-admin').'</label><select class="church-admin-form-control" name="service_id">';
	foreach( $services AS $service){

			if( $service->service_day!=8)
			{
				echo'<option value="'.(int)$service->service_id.'" '.selected($service_id,$service->service_id,FALSE).'>'.esc_html(sprintf( __( '%1$s at %2$s on %3$s %4$s', 'church-admin' ), $service->service_name, $service->venue,$wp_locale->get_weekday( $service->service_day),$service->service_time)).'</option>';
			}
			else
			{
				echo'<option value="'.(int)$service->service_id.'" '.selected($service_id,$service->service_id,FALSE).'>'.esc_html(sprintf( __( '%1$s at %2$s', 'church-admin' ), $service->service_name, $service->venue )).'</option>';
			}

	}
	echo'</select></div>';

	$rota_start_date = !empty($_POST['start_rota_date']) ? church_admin_sanitize($_POST['start_rota_date']) : wp_date('Y-m-01');
	//sanitize
	if(!church_admin_checkdate($rota_start_date)){$rota_start_date = wp_date('Y-m-01');}


	echo'<div class="church-admin-form-group"><label>'.esc_html(__('Choose month','church-admin')).'</label><select class="church-admin-form-control" name="start_rota_date">';
	for($x=-6;$x<=12;$x++){
		$now=time();
		$interval = $x * 28 * 24* 60 * 60;
		$date = date('Y-m-01',$now+$interval);
		echo'<option value="'.esc_attr($date).'" '.selected($date,$rota_start_date,FALSE).'>'.mysql2date('M Y',$date).'</option>';
	}
	echo'</select></div>';
	
	echo'<p><input class="button-primary" type="submit" value="'.esc_attr(__('Change month and service','church-admin')).'"/></p></form>';

    $csv='';
	






	//get details of service for title

	$service=$wpdb->get_row('SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.service_id="'.(int)$service_id.'"');
   church_admin_debug($service);
	//FIX THIS BIT
	if(empty($service->service_time)){$service->service_time='';}
	echo '<h2>'.sprintf( esc_html__( '%1$s Schedule for %2$s at %3$s, %4$s  %5$s', 'church-admin' ),mysql2date('M Y',$rota_start_date), $service->service_name, $service->venue,$expected_frequency[$service->service_frequency],$service->service_time ).'</h2>';
	
	
	

	//check rota jobs are set up
	$allRotaJobs=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings  ORDER by rota_order');
	if ( empty( $allRotaJobs) ){//no rota jobs
		echo'<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&section=rota&amp;action=rota-settings",'rota-settings').'">'.esc_html( __('Please set up some schedule jobs first','church-admin' ) ).'</a></p>';
		return;
	}
	else
	{//rota jobs exist, so safe to proceed

		
		$rotaJobs=church_admin_required_rota_jobs($service_id);
		if(empty($rotaJobs)){
			echo'<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&section=rota&amp;action=rota-settings",'rota-settings').'">'.esc_html( __('Please set up some schedule jobs first for this service','church-admin' ) ).'</a></p>';
			return;

		}
		echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=edit-rota&amp;service_id='.(int)$service_id.'&amp;mtg_type=service','edit-rota').'">'.__('Add a date','church-admin').'</a></p>';
		//we now have an array $rotaJobs that contains id as key and name of job as value

		//get calendar data
		$calendar_data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id = "'.(int)$service->event_id.'" ORDER BY start_date DESC LIMIT 1');
		
		
		//get rota dates
		$chosenYear=mysql2date('Y',$rota_start_date);
		$chosenMonth = mysql2date('m',$rota_start_date);
		$sql='SELECT rota_date,service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND mtg_type="service" AND YEAR(rota_date)="'.esc_sql($chosenYear).'" AND MONTH(rota_date)="'.esc_sql($chosenMonth).'" GROUP BY rota_date ORDER BY rota_date ASC';
		church_admin_debug($wpdb->last_query);
		$rotaDatesResults=$wpdb->get_results( $sql);
		
		if ( empty( $rotaDatesResults) )
		{
			//array $dates will be the array of dates to create
			$dates = array();
			
				/**********************
				 * Create dates array
				 **********************/
				switch($service->service_frequency){
					case '1': 
						//every day in month
						$date = DateTime::createFromFormat("Y-n", "$chosenYear-$chosenMonth");
						for($i=1; $i<=$date->format("t"); $i++){
							$dates[] = DateTime::createFromFormat("Y-n-d", "$chosenYear-$chosenMonth-$i")->format('Y-m-d');
						}
					break;
					
					case '70':
						//weekly on Sunday
						$dates = church_admin_get_days($chosenYear,$chosenMonth,0);
					break;
					case '71':
						//weekly on Monday
						$dates = church_admin_get_days($chosenYear,$chosenMonth,1);
					break;
					case '72':
						//wwekly on a Tuesday
						$dates = church_admin_get_days($chosenYear,$chosenMonth,2);
					break;
					case '73':
						//wwekly on a Tuesday
						$dates = church_admin_get_days($chosenYear,$chosenMonth,3);
					break;
					case '74':
						//wwekly on a Tuesday
						$dates = church_admin_get_days($chosenYear,$chosenMonth,4);
					break;
					case '75':
						//wwekly on a Tuesday
						$dates = church_admin_get_days($chosenYear,$chosenMonth,5);
					break;
					case '76':
						//wwekly on a Tuesday
						$dates = church_admin_get_days($chosenYear,$chosenMonth,6);
					break;
					case 'n10':
						$dates[] = church_admin_nth_day(1, 0, "$chosenYear-$chosenMonth-01");
					break;
					case 'n11':
						$dates[] = church_admin_nth_day(1, 1, "$chosenYear-$chosenMonth-01");
					break;
					case 'n12':
						$dates[] = church_admin_nth_day(1, 2, "$chosenYear-$chosenMonth-01");
					break;
					case 'n13':
						$dates[] = church_admin_nth_day(1, 3, "$chosenYear-$chosenMonth-01");
					break;
					case 'n14':
						$dates[] = church_admin_nth_day(1, 4, "$chosenYear-$chosenMonth-01");
					break;
					case 'n15':
						$dates[] = church_admin_nth_day(1, 5, "$chosenYear-$chosenMonth-01");
					break;
					case 'n16':
						$dates[] = church_admin_nth_day(1, 6, "$chosenYear-$chosenMonth-01");
					break;
					case 'n20':
						$dates[] = church_admin_nth_day(2, 0, "$chosenYear-$chosenMonth-01");
					break;
					case 'n21':
						$dates[] = church_admin_nth_day(2, 1, "$chosenYear-$chosenMonth-01");
					break;
					case 'n22':
						$dates[] = church_admin_nth_day(2, 2, "$chosenYear-$chosenMonth-01");
					break;
					case 'n23':
						$dates[] = church_admin_nth_day(2, 3, "$chosenYear-$chosenMonth-01");
					break;
					case 'n24':
						$dates[] = church_admin_nth_day(2, 4, "$chosenYear-$chosenMonth-01");
					break;
					case 'n25':
						$dates[] = church_admin_nth_day(2, 5, "$chosenYear-$chosenMonth-01");
					break;
					case 'n26':
						$dates[] = church_admin_nth_day(2, 6, "$chosenYear-$chosenMonth-01");
					break;	
					case 'n30':
						$dates[] = church_admin_nth_day(3, 0, "$chosenYear-$chosenMonth-01");
					break;
					case 'n31':
						$dates[] = church_admin_nth_day(3, 1, "$chosenYear-$chosenMonth-01");
					break;
					case 'n32':
						$dates[] = church_admin_nth_day(2, 2, "$chosenYear-$chosenMonth-01");
					break;
					case 'n33':
						$dates[] = church_admin_nth_day(2, 3, "$chosenYear-$chosenMonth-01");
					break;
					case 'n34':
						$dates[] = church_admin_nth_day(2, 4, "$chosenYear-$chosenMonth-01");
					break;
					case 'n35':
						$dates[] = church_admin_nth_day(2, 5, "$chosenYear-$chosenMonth-01");
					break;
					case 'n36':
						$dates[] = church_admin_nth_day(2, 6, "$chosenYear-$chosenMonth-01");
					break;
					case 'n40':
						$dates[] = church_admin_nth_day(4, 0, "$chosenYear-$chosenMonth-01");
					break;
					case 'n41':
						$dates[] = church_admin_nth_day(4, 1, "$chosenYear-$chosenMonth-01");
					break;
					case 'n42':
						$dates[] = church_admin_nth_day(4, 2, "$chosenYear-$chosenMonth-01");
					break;
					case 'n43':
						$dates[] = church_admin_nth_day(4, 3, "$chosenYear-$chosenMonth-01");
					break;
					case 'n44':
						$dates[] = church_admin_nth_day(4, 4, "$chosenYear-$chosenMonth-01");
					break;
					case 'n45':
						$dates[] = church_admin_nth_day(4, 5, "$chosenYear-$chosenMonth-01");
					break;
					case 'n46':
						$dates[] = church_admin_nth_day(4, 6, "$chosenYear-$chosenMonth-01");
					break;
				}
				if(!empty($dates)){
					$values = array();
					foreach($dates AS $key=>$date){
						foreach($rotaJobs AS $ID=>$value){
							$values[]='("'.(int)$ID.'","'.esc_sql( $date ).'",NULL,"'.(int)$service_id.'","'.esc_sql( $mtg_type).'")';
							$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_new_rota (rota_task_id,rota_date,people_id,service_id,mtg_type) VALUES '.implode(",",$values) );
							church_admin_debug($wpdb->last_query);
							$sql='SELECT rota_date,service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND mtg_type="service" AND YEAR(rota_date)="'.esc_sql($chosenYear).'" AND MONTH(rota_date) = "'.esc_sql($chosenMonth).'" GROUP BY rota_date ORDER BY rota_date ASC';
							$rotaDatesResults=$wpdb->get_results( $sql);
						
						}
						
				}
					
				}
				
				
			
			
		}
		if(empty($rotaDatesResults)){
			echo'<p>'.esc_html(__('No rota dates set automatically, please use button below','church-admin')).'</p>';
			echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=edit-rota&amp;service_id='.(int)$service_id.'&amp;mtg_type=service','edit-rota').'">'.__('Add a date','church-admin').'</a></p>';
			return;
		}

		//rota jobs exist already

		//feed in message if rota date has been copied and then redirected back here
		if(!empty( $_GET['message'] )&&$_GET['message']=='copied'){
			echo'<div class="notice notice-success inline"><h2>'.esc_html( __('Schedule date copied','church-admin' ) ).'</h2></div>';
		}



		$thead='<tr><th class="column-primary">'.esc_html( __('Service date & time','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th>';
		foreach( $rotaJobs AS $id=>$value)
		{
			$thead.='<th>'.esc_html( $value).'</th>';
			$csvheader[]='"'.esc_html( $value).'"';   
		}
		$thead.='<th>'.esc_html( __("Copy",'church-admin' ) ).'</th>';
		$thead.='<th>'.esc_html( __("Calendar Event?",'church-admin' ) ).'</th>';
		echo'<p>'.__('Dates with nobody serving are not shown in front-end or app schedules','church-admin').'</p>';
		echo '<table class="widefat wp-list-table striped"><thead>'.$thead.'</thead>'."\r\n";
		echo'<tbody>'."\r\n";
		$id=1;
		
		
		//build row for each date
		$date_options=$rotaDatesResults;
		foreach( $rotaDatesResults AS $row)
		{
			
			$edit_url=wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=edit-rota&rota_date='.esc_html( $row->rota_date).'&amp;service_id='.(int)$service_id.'&amp;mtg_type=service','edit-rota');
		$delete_url=wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=delete_rota&rota_date='.esc_html( $row->rota_date).'&amp;service_id='.(int)$service_id.'&amp;mtg_type=service','delete_rota');
			

			echo'<tr><td data-colname="Name" class="column-primary">'.mysql2date(get_option('date_format'),$row->rota_date).' '.mysql2date(get_option('time_format'),$row->service_time).' <button type="button" class="toggle-row">
				<span class="screen-reader-text">show details</span>
			</button></td><td data-colname="Edit"><a href="'.$edit_url.'">'.esc_html( __('Edit','church-admin' ) ).'</a></td>
			<td data-colname="Delete"><a href="'.$delete_url.'" onclick="return confirm(\'Are you sure?\')">'.esc_html( __('Delete','church-admin' ) ).'</a></td>'."\r\n";
			
			foreach( $rotaJobs AS $rota_task_id=>$jobName)
			{
				//note that rota_id for ALL rota jobsrefers to the rota task id!
				$people=church_admin_rota_people_array( $row->rota_date,$rota_task_id,$service_id,'service');
				
				$currentData=!empty( $people)?esc_html(implode(", ",$people) ):'<em>'.esc_html( __('Click to enter data','church-admin' ) ).'</em>';
				$dataCurrentData=!empty( $people)?esc_html(implode(", ",$people) ):'';
				$uniqueID=md5('rota-item-'.$row->rota_date.'-'.$service_id.'-'.$id);
				$data='data-id="'.$uniqueID.'" data-time="'.esc_html( $row->service_time).'" data-service_id="'.(int)$service_id.'" data-rota_date="'.esc_html( $row->rota_date).'" data-rota_task_id="'.(int)$rota_task_id.'" data-id="'.(int)$uniqueID.'" data-current_data="'.esc_attr($dataCurrentData).'" ';
				echo'<td data-colname="'.esc_html( $jobName).'" '.$data.' id="'.$uniqueID.'"> '."\r\n";
				echo'<span class="rota_edit" '.$data.' >'."\r\n";
				echo $currentData."\r\n";
				echo '</span></td>'."\r\n";
				$id++;
				
			}
			
			//copy section
			echo'<td  data-colname="Copy"><form action="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=copy-rota-data','copy-rota-data').'" method="POST">';
			
			
			
			echo'<input type="hidden" name="service_id" value="'.(int)$service_id.'" /><input type="hidden" name="mtg_type" value="service" />';
			echo'<input type="hidden" name="rotaDate1" value="'.esc_html( $row->rota_date).'" />';
			echo esc_html(__('Copy to new schedule date...','church-admin' ) ).church_admin_date_picker( $row->rota_date,'rotaDate2',FALSE,NULL,NULL,'new_date'.$row->rota_date,'new_date'.$row->rota_date);
			echo'<input type="submit" value="'.esc_html( __('Copy schedule','church-admin' ) ).'" /></form></td>';
			//calendar section
			$calendar_event_exists = FALSE;
			if(!empty($service->event_id)){
				$check = $wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id = "'.esc_sql($service->event_id).'" AND start_date="'.esc_sql($row->rota_date).'"');
				if(!empty($check)){$calendar_event_exists = TRUE;}
			}

			echo'<td data-colname="'.esc_attr(__('Yes','church-admin')).'" id="'.esc_attr($row->rota_date).'">';
			if($calendar_event_exists){
				echo '<span class="dashicons dashicons-yes"></span>'."\r\n";
			}
			else{
				$event_id=!empty($service->event_id) ? (int)$service->event_id:'';

				echo'<span class="create-calendar-event-from-rota"  data-event_id="'.esc_attr($event_id).'" data-service_id="'.(int)$service_id.'" data-date="'.esc_attr($row->rota_date).'">'.esc_html(__('Create calendar event','church-admin')).'</span>';
			}
			echo'</td>';
				
			




			echo'</tr>'."\r\n";
			}
		
		echo'</tbody><tfoot>'.$thead.'</tfoot></table>';
		$nonce = wp_create_nonce("edit_rota");
		echo '<script>jQuery(document).ready(function( $) {

			$("body").on("click",".create-calendar-event-from-rota",function(){
			
					var event_id = $(this).data("event_id");
					var service_id = $(this).data("service_id");
					var start_date = $(this).data("date");
					var args={"action":"church_admin","method":"create-calendar-event-from-rota","event_id":event_id,"start_date":start_date,"service_id":service_id,"nonce":"'.$nonce.'"};
					console.log(args);
					jQuery.post(ajaxurl, args, function(response)  {
						console.log("Response data");
						console.log(response)
						$("#"+response.id).html(response.html);
					},"json");
			});


			$("body").on("click",".rota_edit",function()  {
			console.log("Rota Item clicked");
				var rota_date=$(this).attr("data-rota_date");
				var rota_task_id=$(this).attr("data-rota_task_id");
				var service_id=$(this).attr("data-service_id");
				var time=$(this).attr("data-time");
				var id=$(this).attr("data-id");
				var current_data=$(this).attr("data-current_data");
				console.log("current data" + current_data);
				console.log("cycle through current active edit fields");
				//undo all current input fields
				$("body .editing").each(function()  {
					var curr_data=$(this).attr("data-current_data");
					var curr_rota_date=$(this).attr("data-rota_date");
					var curr_rota_task_id=$(this).attr("data-rota_task_id");
					var curr_service_id=$(this).attr("data-service_id");
					var curr_time=$(this).attr("data-time");
					var curr_id=$(this).attr("data-id");
					
					console.log("handle field with id " +curr_id);
					var html="<span class=\"rota_edit\" data-id=\""+curr_id+"\" data-rota_date=\""+curr_rota_date+"\" data-time=\""+curr_time+"\" data-rota_task_id=\""+curr_rota_task_id+"\" data-service_id=\""+curr_service_id+"\" id=\""+curr_id+"\">"+curr_data+"</span>";
					console.log("Current data for field is " +html);
					$("#"+curr_id).html(html);
					$("#"+curr_id).removeClass("editing");
				})
				
				
				$("#"+id).addClass("editing");
				var imageloader="<img src=\"'.admin_url().'/images/loading.gif\" />";
				$("#"+id).html(imageloader)
				var args={"action":"church_admin",
					"method":"rota_get_edit",
					"time":time,
					"id":id,
					"rota_task_id":rota_task_id,
					"service_id":service_id,
					"idtochange":id,
					"rota_date":rota_date,
					"current":current_data,
					"nonce":"'.$nonce.'",
					dataType: "json"};	
				console.log("Args to send");
				console.log(args)
				jQuery.post(ajaxurl, args, function(response)  {
					
				
					console.log("Response data");
					console.log(response)
					$("#"+response.id).html(response.html);
				},"json");
			});
			$("body").on("change",".rota-dropdown",function()
			{
				var name=$("option:selected",this).attr("value");
				var rota_date=$(this).attr("data-rota_date");
				var id=$(this).attr("data-id");
				var time=$(this).attr("data-time");
				var rota_task_id=$(this).attr("data-rota_task_id");
				var service_id=$(this).attr("data-service_id");
				var data = {
					"action": "church_admin",
					"method": "edit_rota",
					"nonce": "'.$nonce.'",
					"rota_task_id":rota_task_id,
					"rota_date":rota_date,
					"content":name,
					"idtochange":id,
					"time":time,
					"service_id":service_id
				};
				console.log(data);
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					console.log("dropdown change response");
					console.log(response);
					console.log("#"+response.idtochange);
								
					
					$("#"+response.idtochange).html(response.content);
					$("#"+response.idtochange).attr("data-current_data",response.persondata);
				},"json");
			});
			
			$("body").on("change", ".editable_rota", function()
			{
				console.log("Changed");
				var data=$(this).val();
				var rota_task_id=$(this).attr("data-rota_task_id");
				var rota_date=$(this).attr("data-rota_date");
				var id=$(this).attr("data-id");
				var service_id=$(this).attr("data-service_id");
				var time=$(this).attr("data-time");
				var data = {
					"action": "church_admin",
					"method": "edit_rota",
					"nonce": "'.$nonce.'",
					"rota_task_id":rota_task_id,
					"rota_date":rota_date,
					"content":data,
					"idtochange":id,
					"time":time,
					"service_id":service_id
				};
				console.log(data);
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					console.log("Response");
					console.log(response);
					$("#"+response.idtochange).html(response.content);
					$("#"+response.idtochange).attr("data-current_data",response.persondata);
				},"json");
			})
		
		});</script>';
	

        
	}
        
	   




}//end function

function church_admin_rota_csv( $start_date,$end_date,$service_id,$initials=0)
{
    if(!is_user_logged_in() )exit(__('Login required','church-admin') );
    if ( empty( $service_id) )exit(__('Service needs specifying','church-admin') );
    global $wpdb;
    //csv header
    $rotaJobs=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
		$requiredRotaJobs=$rotaDates=array();
		foreach( $rotaJobs AS $rota_task)
		{
			$allServiceID=maybe_unserialize( $rota_task->service_id);
			if(is_array( $allServiceID)&&in_array( $service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=array('job'=>$rota_task->rota_task,'initials'=>$rota_task->initials);
		}
        
    $csv='';
    $csvheader=array(__('"Date"','church-admin') );
    foreach( $requiredRotaJobs AS $id=>$rotaJobsArray)
    {
        $csvheader[]='"'.esc_html( $rotaJobsArray['job'] ).'"';   
    }
    //get dates
    $sql='SELECT rota_date,service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND mtg_type="service" AND rota_date>="'.esc_sql( $start_date).'" AND rota_date<="'.esc_sql( $end_date).'" GROUP BY rota_date ORDER BY rota_date ASC';
    
    $rotaDatesResults=$wpdb->get_results( $sql);
    if(!empty( $rotaDatesResults) )
    {
        
        foreach( $rotaDatesResults AS $rotaDateRow)
        {
            $csvrow=array();
            foreach( $requiredRotaJobs AS $rota_task_id=>$jobArray)
			{
				            
                if ( empty( $initials)&&empty( $jobArray['initials'] ) )
                    {
					   $people=esc_html(church_admin_rota_people( $rotaDateRow->rota_date,$rota_task_id,$service_id,'service') );
                    }
                    else
                    {//initials
                        
                        $people=esc_html(church_admin_rota_people_initials( $rotaDateRow->rota_date,$rota_task_id,$service_id,'service') );
                    }
                $csvrow[]='"'.html_entity_decode( $people).'"';
            }
            $csv.='"'.mysql2date(get_option('date_format'),$rotaDateRow->rota_date).'",'.implode(",",$csvrow)."\r\n";
        }
     if(defined('CA_DEBUG') )church_admin_debug( $csv);   
    
    }else{$csv='Nothing here yet';}
    header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="schedule.csv"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header("Content-Disposition: attachment; filename=\"schedule.csv\"");
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo implode(",",$csvheader)."\r\n".$csv;
}



  /**
 *
 * Delete rota entry
 *
 * @author  Andy Moyle
 * @param    $date,$mtg_type,$service_id
 * @return   BOOL
 * @version  0.1
 *
 */
 function church_admin_delete_rota( $rota_date,$mtg_type,$service_id)
 {
 	if(!church_admin_level_check('Rota') )wp_die(__('You don\'t have permission to do that','church-admin') );
 	global $wpdb;
 	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_date="'.esc_sql( $rota_date).'" AND mtg_type="'.esc_sql( $mtg_type).'" AND service_id="'.(int)$service_id.'"');
 	echo '<div class="notice notice-success inline">'.esc_html( __('Schedule Date Deleted','church-admin' ) ).'</div>';
	//MICK WALL
	 //Redirect back to the list for current selected service.
	 $url=wp_nonce_url(admin_url().'admin.php?page=church_admin%2Findex.php&action=rota&message=Schedule Date Deleted&section=rota&service_id='.$service_id,'rota');
	 wp_redirect( $url );
 	exit();
 }

/**
 *
 * copies data from rota_date to another rota_date
 *	Call early and then redirect to protect url, in case it is done again.
 *
 * @author  Andy Moyle
 * @param    $rotaDate1,$rotaDate2, $service_id,$mtg_type
 * @return   NULL
 * @version  0.1
 *
 */
function church_admin_copy_rota( $rotaDate1,$rotaDate2, $service_id,$mtg_type)
{
	church_admin_debug('***** church_admin_copy_rota function ****');
	church_admin_debug(func_get_args());
	if(!church_admin_level_check('Rota') )wp_die(__('You don\'t have permission to do that','church-admin') );
	$message=__("copied",'church-admin');
	//$rotaDate1 is destination
	//$rotaDate2 is copy
	global $wpdb;
	//Mick Wall
	// only copy if the dates are different
	if ( $rotaDate1 != $rotaDate2)
	{
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND mtg_type="service" AND rota_date="'.esc_sql($rotaDate2).'"');
		//church_admin_debug($wpdb->last_query);
		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_date="'.esc_sql( $rotaDate1).'"  AND mtg_type="'.esc_sql( $mtg_type).'" AND service_id="'.(int)$service_id.'"');
		//church_admin_debug($wpdb->last_query);
		if(!empty( $results) )
		{

			foreach( $results AS $row)
			{
				church_admin_update_rota_entry( $row->rota_task_id,$rotaDate2,$row->people_id,$mtg_type,$service_id,$row->service_time);
			}
		}
	}
	else
		$message="ERROR:+You+cannot+copy+to+the+same+date.";
	return $message;
	church_admin_debug('***** End church_admin_copy_rota function ****');
}

//edit one ministry item on rota
function church_admin_edit_ministry_rota($mtg_type,$service_id)
{
	$out='<h2>'.esc_html( __("Ministry specific schedule",'church-admin' ) ).'</h2>';
	global $wpdb,$wp_locale;
	//$wpdb->show_errors;
	$premium=get_option('church_admin_payment_gateway');
	if(!is_user_logged_in()){
		$out.= __('Not logged in','church-admin');
		$out.= wp_login_form();
		return $out;
	}
	$user=wp_get_current_user();
	$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
	if(empty($person)){
		$out.= __('Your login is not connected to a directory entry','church-admin');
		return $out;
	}
	$team_contact_ids=$wpdb->get_results('SELECT a.*,b.ID FROM '.$wpdb->prefix.'church_admin_rota_settings a, '.$wpdb->prefix.'church_admin_people_meta b WHERE b.ID=a.ministries AND b.people_id="'.(int)$person->people_id.'" AND b.meta_type="team_contact" GROUP BY a.rota_task ORDER by a.rota_task');

	if(church_admin_level_check('Rota')){

		//Rota level people can do any ministry
		$team_contact_ids=$wpdb->get_results('SELECT a.*,b.ID FROM '.$wpdb->prefix.'church_admin_rota_settings a, '.$wpdb->prefix.'church_admin_people_meta b WHERE b.ID=a.ministries GROUP BY a.rota_task ORDER by a.rota_task');
	}
	if(empty($team_contact_ids)){
		$out.='<p>'.esc_html( __('You are not the team contact for any ministries','church-admin' ) ).'</p>';
		return $out;
	}
	$rota_task_id=null;
	if(!empty($_POST['rota_task_id']))$rota_task_id=(int)$_POST['rota_task_id'];
	
	if($wpdb->num_rows==1)
	{
		$rota_task_id=$team_contact_ids[0]->rota_id;
	}

	if(empty($rota_task_id))
	{
		$out.='<form action="" method="POST">';
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Which ministry?','church-admin' ) ).'</label><select name="rota_task_id">';
		foreach($team_contact_ids AS $teams){
			$out.='<option value="'.(int)$teams->rota_id.'" '.selected($teams->rota_id,$rota_task_id,FALSE).'>'.esc_html($teams->rota_task).'</option>';
		}
		$out.='</select></div>';
		$out.='<p><input type="submit" class="button-primary" value="'.esc_html( __('Choose team','church-admin' ) ).'" /></p></form>';
	}

	if( !empty( $rota_task_id ) ) {

		//which services
		$rota_task_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings WHERE rota_id="'.(int)$rota_task_id.'"');
		if(empty($rota_task_details)){
			return __('Cannot find schedule task details','church-admin');
		}
		$service_id=!empty($_POST['service_id'])?(int)$_POST['service_id']:NULL;
		$poss_service_id=maybe_unserialize($rota_task_details->service_id);
		if(empty($poss_service_id)||!is_array($poss_service_id)){
			return __('No service setup for this schedule task','church-admin');
		}
		if(count($poss_service_id)==1){$service_id=(int)$poss_service_id[0];}
	
		if(!in_array($service_id,$poss_service_id)){
			//show form
			$out.='<form action="" method="POST">';
			$out.='<p>'.esc_html( __('Which service','church-admin' ) ).'</p>';
			$out.='<input type="hidden" name="rota_task_id" value="'.(int)$rota_task_id.'" />';
			foreach($poss_service_id AS $key=>$service_id)
			{
				$service=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
				$out.='<div class="church-admin-form-group"><input type="radio" name="service_id" value="'.(int)$service_id.'" /> '.esc_html(sprintf(__('%1$s at %2$s %3$s','church-admin' ) ,$service->service_name,$wp_locale->get_weekday($service->service_day),$service->service_time)).'</div>';
			}
			$out.='<p><input type="submit" class="button-primary" value="'.esc_html( __('Choose service','church-admin' ) ).'" /></p></form>';
		}

		if(!empty($rota_task_id) &&!empty($service_id)){

			
			//get dates
			$dates=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND rota_task_id="'.(int)$rota_task_id.'" AND rota_date>=NOW() ORDER BY rota_date LIMIT 12');
			if(empty($dates)){
				//create 3 months
				$requiredRotaJobs=church_admin_required_rota_jobs( $service_id);
				$service=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
				$last_date=$wpdb->get_var('SELECT MAX(rota_date) FROM '.$wpdb->prefix.'church_admin_new_rota WHERE mtg_type="service" AND service_id="'.(int)$service_id.'"');
				if ( empty( $last_date) )$last_date=date('Y-m-d');
				$date=new DateTime( $last_date);
				$nextDate=$date->modify('next '.$wp_locale->get_weekday( $service->service_day) );

				$values=array();
				for ( $int=0; $int<12; $int++)
				{
					foreach( $requiredRotaJobs AS $ID=>$job)
					{
						$values[]='("'.(int)$ID.'","'.esc_sql( $date->format('Y-m-d') ).'",NULL,"'.(int)$service_id.'","'.esc_sql( $mtg_type).'")';
					}
					$date=$date->modify('+7 days');
				}
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_new_rota (rota_task_id,rota_date,people_id,service_id,mtg_type) VALUES '.implode(",",$values) );
					
				$out.='<p>'.esc_html( __('3 months of service dates automatically created','church-admin' ) ).'</p>';
				$dates=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND rota_task_id="'.(int)$rota_task_id.'" AND rota_date>=NOW() ORDER BY rota_date LIMIT 12');
			}
			if(!empty($_POST['save-rota'])){
			
				$errors=array();
				foreach($dates AS $date){
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_date="'.esc_sql($date->rota_date).'" AND service_id="'.(int)$service_id.'" AND rota_task_id="'.(int)$rota_task_id.'"');
					
					$people=array();
					$jdates = !empty($_POST['j'.$date->rota_date])?church_admin_sanitize($_POST['j'.$date->rota_date]):array();
					if(!empty($jdates )){
						foreach($jdates  AS $key=>$people_id){
							$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_new_rota (service_id,rota_task_id,people_id,mtg_type,rota_date)VALUES("'.(int)$service_id.'" ,"'.(int)$rota_task_id.'","'.(int)$people_id.'","service","'.esc_sql($date->rota_date).'")');
							
						}
					}
					if(!empty($_POST[$date->rota_date])){
						$people=unserialize(church_admin_get_people_id(sanitize_text_field( stripslashes($_POST[$date->rota_date] ) )));
						foreach( $people AS $key=>$people_id)
						{
							$check=FALSE;
							if(!empty( $premium) )
							{
								$check=$wpdb->get_var('SELECT not_id FROM '.$wpdb->prefix.'church_admin_not_available WHERE unavailable="'.esc_sql( $date->rota_date).'" AND people_id="'.(int)$people_id.'"');
							}
							if ( empty( $check) )
							{
								$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_new_rota (service_id,rota_task_id,people_id,mtg_type,rota_date)VALUES("'.(int)$service_id.'" ,"'.(int)$rota_task_id.'","'.esc_sql($people_id).'","service","'.esc_sql($date->rota_date).'")');
								
							}
							else
							{
								$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
								$errors[]=esc_html(sprintf(__('%1$s not added because they are unavailable on %2$s','church-admin' ) ,church_admin_formatted_name( $person),mysql2date(get_option('date_format'),$date->rota_date )) );
							}
						}

					}
				}
				$out.='<div class="notice notice-success"><h2>'.esc_html(__('Schedule updated','church-admin')).'</h2>';
				if(!empty($errors))$out.=implode('<br/>',$errors);
				$out.='</div>';
			}
			$out.='<form action="" method="POST">';
			$out.='<input type="hidden" name="rota_task_id" value="'.(int)$rota_task_id.'" />';
			$out.='<input type="hidden" name="service_id" value="'.(int)$service_id.'" />';
			$rota_task=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings WHERE rota_id="'.(int)$rota_task_id.'"');
			
			$out.='<h3>'.esc_html( sprintf(__('Schedule for %1$s','church-admin' ) ,$rota_task->rota_task)).'</h3>';;
			foreach($dates AS $row){
				$out.='<div class="church-admin-form-group"><label>'.mysql2date(get_option('date_format'),$row->rota_date).'</label></div>';
				$currentPeople=church_admin_rota_people_array( $row->rota_date,$rota_task_id,$service_id,'service');
				
				$allMinistryPeople=array();
				
					$allMinistryPeople=$allMinistryPeople+church_admin_ministry_people_array( $rota_task->ministries );
					

					asort( $allMinistryPeople);
					foreach( $allMinistryPeople AS $people_id=>$name)
					{
						$check=FALSE;
						if(!empty( $premium) )
						{
							$check=$wpdb->get_var('SELECT not_id FROM '.$wpdb->prefix.'church_admin_not_available WHERE unavailable="'.esc_sql( $row->rota_date).'" AND people_id="'.(int)$people_id.'"');
						}
						if(!$check)
						{
							$out.='<input type="checkbox" name="j'.esc_attr($row->rota_date).'[]" value="'.(int)$people_id.'"';
							if(!empty( $currentPeople[$people_id] ) ) {$out.= ' checked="checked "';unset( $currentPeople[$people_id] );}
							$out.='/> ';
						}
						$out.=' <span class="ca-names">'.esc_html( $name).' </span>';
						if( $check)$out.= '<strong>('.esc_html( __('Not available','church-admin' ) ).')</strong>';
						$out.='<br>';
					}
				
				//autocomplete text field populated with rest of names!
					if(!empty( $currentPeople) )  {$current=implode(", ",$currentPeople);}
					elseif(!empty( $_POST[$rota_task_id] ) )$current=(int)$_POST[$rota_task_id];
					else{$current='';}

					$out.= '<p>'.church_admin_autocomplete($row->rota_date,'friends'.(int)$rota_task_id,'to'.(int)$rota_task_id,$current,FALSE).'</p>';
			}
			$out.='<p><input type="hidden" name="save-rota" value="1" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
		
		}

	}

	if(is_admin()){
		$out.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=ministry-rota','ministry-rota').'" class="button-secondary">'.esc_html( __('Start again','church-admin' ) ).'</a></p>';
	}else {
		$out.='<p><a href="" class="button-secondary">'.esc_html( __('Start again','church-admin' ) ).'</a></p>';

	}
	return $out;
}



/**
 *
 * Edit Rota Date
 *
 * @author  Andy Moyle
 * @param    $rota_date,$mtg_type,$service_id
 * @return
 * @version  0.1
 *
 */
function church_admin_edit_rota( $rota_date=NULL,$mtg_type='service',$service_id=1)
{
	if(!church_admin_level_check('Rota') )
	{
		church_admin_edit_ministry_rota($mtg_type,$service_id);
	}
	else
	{
		//rota permissions
		global $wpdb;
		$premium=get_option('church_admin_payment_gateway');
		if ( empty( $rota_date) )
		{
			if(!empty( $_POST['rota_date'] ) )
			{
				$rota_date=church_admin_sanitize($_POST['rota_date']);
				if(!church_admin_checkdate($rota_date)){$rota_date = null;}
			}
			else
			{
				$rota_date=NULL;
			}
		}
		



		if(!empty( $_POST['save_rota'] ) )
		{
			if ( empty( $rota_date) )
			{
				$error['rota_date']=__('Please specify a date','church-admin');
				church_admin_rota_form( $service_id,NULL,$error);
			}
			else
			{	
				/*********************************
				 * No errors
				 *********************************/
				//clear out current entries for that date,service_id and mtg_type;
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND mtg_type="service" AND rota_date="'.esc_sql($rota_date).'"');
				$requiredRotaJobs=church_admin_required_rota_jobs( $service_id);
				//grab service details
				$service=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
				if(!empty( $rota_date) )$service_time=$wpdb->get_var('SELECT service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND rota_date="'.esc_sql( $rota_date).'" LIMIT 1');
				//grab rota jobs for thsi service id
				$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings  ORDER BY rota_order');
				$requiredRotaJobs=$requiredMinistries=array();
				foreach( $rota_tasks AS $rota_task)
				{
					$allServiceID=maybe_unserialize( $rota_task->service_id);
					if(is_array( $allServiceID)&&in_array( $service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
					$requiredMinistries[$rota_task->rota_id]=$rota_task->ministries;
				}
				$errors=array();
				foreach( $requiredRotaJobs AS $job_id=>$job_name)
				{
					//deal with checkbox generated entries
					$jjob_id = !empty( $_POST['j'.$job_id] )?church_admin_sanitize($_POST['j'.$job_id]):array();
					if(!empty( $jjob_id ) )
					{

						foreach( $jjob_id  AS $key=>$people_id)
						{
							$check=FALSE;
							if(!empty( $premium) )
							{
								$check=$wpdb->get_var('SELECT not_id FROM '.$wpdb->prefix.'church_admin_not_available WHERE unavailable="'.esc_sql( $rota_date).'" AND people_id="'.(int)$people_id.'"');
							}
							if ( empty( $check) )
							{
								church_admin_update_rota_entry( $job_id,$rota_date,$people_id,'service',$service_id,$_POST['service_time'] );
							}
							else
							{
								$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
								$errors[]=esc_html(sprintf(__('%1$s not added to "%2$s", because they are unavailable','church-admin' ) ,church_admin_formatted_name( $person),$requiredRotaJobs[$job_id] ));
							}
						}
					}
					//deal with autocomplete
					if(!empty( $_POST[$job_id] ) )
					{
						$people=unserialize(church_admin_get_people_id(sanitize_text_field(stripslashes( $_POST[$job_id] ) )));
						foreach( $people AS $key=>$people_id)
						{
							$check=FALSE;
							if(!empty( $premium) )
							{
								$check=$wpdb->get_var('SELECT not_id FROM '.$wpdb->prefix.'church_admin_not_available WHERE unavailable="'.esc_sql( $rota_date).'" AND people_id="'.(int)$people_id.'"');
							}
							if ( empty( $check) )
							{
								church_admin_update_rota_entry( $job_id,$rota_date,$people_id,'service',$service_id,church_admin_sanitize($_POST['service_time'] ));
							}
							else
							{
								$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
								$errors[]=esc_html(sprintf(__('%1$s not added to "%2$s", because they are unavailable','church-admin' ) ,church_admin_formatted_name( $person),$requiredRotaJobs[$job_id]) );
							}
						}
					}
				}

				echo '<div class="notice notice-success inline"><h2>'.esc_html( __('Schedule Updated','church-admin' ) ).'</h2>';
				if(!empty( $errors) )echo'<p>'.implode("<br>",$errors).'</p>';
				echo'</div>';
				church_admin_rota_list( $service_id);
			}
			if(!empty( $premium) )update_option('church_admin_modified_app_content',time() );
		}
		else
		{//form
			church_admin_rota_form( $service_id,$rota_date,NULL);

		}//form
	}

}


function church_admin_rota_form( $service_id,$rota_date,$error)
{
	global $wpdb;
	$premium=get_option('church_admin_payment_gateway');
	$requiredRotaJobs=church_admin_required_rota_jobs( $service_id);
	//grab service details
	$service=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
	if(!empty( $rota_date) )$service_time=$wpdb->get_var('SELECT service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND rota_date="'.esc_sql( $rota_date).'" LIMIT 1');
	//grab rota jobs for thsi service id
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings  ORDER BY rota_order');
	$requiredRotaJobs=$requiredMinistries=array();
	foreach( $rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize( $rota_task->service_id);
		if(is_array( $allServiceID)&&in_array( $service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
		$requiredMinistries[$rota_task->rota_id]=!empty( $rota_task->ministries)?$rota_task->ministries:NULL;
	}

	echo'<h2>'.esc_html( __('Edit Schedule for','church-admin' ) ).' ';
	if(!empty( $rota_date) )echo mysql2date(get_option('date_format'),$rota_date).' ';
	echo esc_html( $service->service_name).'</h2>';
	if(!empty( $error) )
	{
		echo'<div class="notice notice-danger"><h2>'.esc_html( __('There are some errors','church-admin' ) ).'</h2><p>';
		echo implode("<br>",$error);
		echo'</p></div>';
	}
	echo'<form action="" method="POST">';

	if ( empty( $rota_date) )
	{
		echo '<div class="church-admin-form-group"><label>'.esc_html( __('Date','church-admin' ) ).'</label>'.church_admin_date_picker(NULL,'rota_date',FALSE,NULL,NULL,'rota_date','rota_date').'</div>';

	}
	
	echo '<div class="church-admin-form-group"><label>'.esc_html( __('Service time','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="service_time" ';
	if(!empty( $service_time) )  {echo' value="'.esc_html( $service_time).'" ';}else{echo' value="'.esc_html( $service->service_time).'" ';}
		echo'/></div>';
	
	foreach( $requiredRotaJobs AS $job_id=>$job_name)
	{
		echo'<div class="church-admin-form-group"><label>'.esc_html( $job_name).'</label><br/>';
	
		//checkbox first
		$currentPeople=church_admin_rota_people_array( $rota_date,$job_id,$service_id,'service');

		$allMinistryPeople=array();
		if(!empty( $requiredMinistries[$job_id] ) )
		{
			$allMinistryPeople=$allMinistryPeople+church_admin_ministry_people_array( $requiredMinistries[$job_id] );
			

			asort( $allMinistryPeople);
			foreach( $allMinistryPeople AS $people_id=>$name)
			{
				$check=FALSE;
				if(!empty( $premium) )
				{
					$check=$wpdb->get_var('SELECT not_id FROM '.$wpdb->prefix.'church_admin_not_available WHERE unavailable="'.esc_sql( $rota_date).'" AND people_id="'.(int)$people_id.'"');
				}
				if(!$check)
				{
					echo'<input type="checkbox" name="j'.intval( $job_id).'[]" value="'.(int)$people_id.'"';
					if(!empty( $currentPeople[$people_id] ) ) {echo ' checked="checked "';unset( $currentPeople[$people_id] );}
					echo'/> ';
				}
				echo' <span class="ca-names">'.esc_html( $name).'</span>';
				if( $check)echo '<strong>('.esc_html( __('Not available','church-admin' ) ).')</strong>';
				echo'<br>';
			}
		}
		//autocomplete text field populated with rest of names!
		if(!empty( $currentPeople) )  {$current=implode(", ",$currentPeople);}
		elseif(!empty( $_POST[$job_id] ) )$current=(int)$_POST[$job_id];
		else{$current='';}

		echo church_admin_autocomplete(intval( $job_id),'friends'.intval( $job_id),'to'.intval( $job_id),$current,FALSE);
		echo'</div>';
	}
	echo'<tp><input type="hidden" name="save_rota" value="yes" />'.wp_nonce_field('edit-rota').'<input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p>';
	echo'</form>';
	
}


 /**
 *
 * Emails out the rota
 *
 * @author  Andy Moyle
 * @param    $service_id,$date
 * @return   html string
 * @version  0.2
 *
 * Fix for translated installs, don't translate date
 */

function church_admin_email_rota( $service_id,$date)
{
	church_admin_debug('**** church_admin_email_rota ****');
	if(!church_admin_level_check('Rota') )wp_die(__('You don\'t have permission to do that','church-admin') );
 	$debug=TRUE;
	

	global $church_admin_version,$wpdb,$wp_locale;
	//don't translate days as strtotime doesn't work
	$wpdb->show_errors;
	//grab service details
	$service=$wpdb->get_row('SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.service_id="'.(int)$service_id.'"');
    $rota_data= new stdClass();
    if(defined('CA_DEBUG') )church_admin_debug(print_r( $service,TRUE) );
    if ( empty( $date) )  {
		$rota_data=$wpdb->get_row('SELECT rota_date,service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE mtg_type="service" AND service_id="'.(int)$service_id.'" AND rota_date>=CURDATE() ORDER BY service_id,rota_date ASC LIMIT 1');
	}
    else
    {
        $rota_data=$wpdb->get_row('SELECT rota_date,service_time FROM '.$wpdb->prefix.'church_admin_new_rota WHERE mtg_type="service" AND service_id="'.(int)$service_id.'" AND rota_date="'.esc_sql( $date).'" ORDER BY service_id,rota_date ASC LIMIT 1'); 
    }
	if(empty($rota_data)){
		
		return __('No schedule data found for that day.','church-admin');
	}
	//handle if no $rota_date->service_time, which is an overrie time not always set.
	if(empty($service->service_time)){
		$rota_data->service_time = $service->service_time;
	}
	if(!empty( $_POST['rota_email'] ))
	{//process form and send email


		$rotaJobs=church_admin_required_rota_jobs( $service_id);

		//$rotaJobs is an array rota_task_id=>rota_task

		echo '<h2>'.wp_kses_post(sprintf(__('Schedule for %1$s at %2$s on %3$s at %4$s', 'church-admin' ) , $service->service_name, $service->venue,$wp_locale->get_weekday( $service->service_day).' '.mysql2date(get_option('date_format'),$rota_data->rota_date),$rota_data->service_time )).'</h2>';
		//build email

			//build rota with jobs
			$user_message= wp_kses_post(nl2br(stripslashes( $_POST['message'] ) ) );
			//fix floated images for email
			$user_message=str_replace('class="alignleft ','style="float:left;margin-right:20px;" class="',$user_message);
			$user_message=str_replace('class="alignright ','style="float:right;margin-left:20px;" class="',$user_message);
			//$textversion=strip_tags( $user_message).'\r\n for '.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'\r\n';
			if( $service->service_day!=8)  {
				$message=$user_message.'<h4>'.wp_kses_post(sprintf(__('Schedule for %1$s at %2$s on %3$s at %4$s', 'church-admin' ) , $service->service_name, $service->venue,$wp_locale->get_weekday( $service->service_day).' '.mysql2date(get_option('date_format'),$rota_data->rota_date),$rota_data->service_time )) .'</h4>';
			}
			else{$message=$user_message.'<h4>'. wp_kses_post(sprintf(__( 'Schedule for %1$s at %2$s on %3$s at %4$s', 'church-admin' ), $service->service_name, $service->venue,mysql2date(get_option('date_format'),$rota_data->rota_date),$rota_data->service_time) ).'</h4>';}

	
			$message.='<table><thead><tr><th>'.esc_html( __('Ministry','church-admin' ) ).'</th><th>'.esc_html( __('Who','church-admin' ) ).'</th></tr></thead><tbody>';
			$not_receiving = $recipients = $pushTokens=array();

			$app_id=get_option('church_admin_app_id');
			foreach( $rotaJobs AS $rota_task_id=>$jobName)
				{
					$people='';
					
					$people=church_admin_rota_people_array( $rota_data->rota_date,$rota_task_id,$service_id,'service');
					church_admin_debug('People array');
					church_admin_debug( $people);
					if(!empty( $people) )
					{
						foreach( $people AS $people_id=>$name)
						{
							$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
							//church_admin_debug($wpdb->last_query);
							if(empty($data)){
								continue;
							}
							//handle people we cannot send to...
							if(empty($data->gdpr_reason)){
								$not_receiving[$data->people_id] = '<p>'.esc_html(sprintf(__('%1$s has no data protection reason set, so email not sent','church-admin'),$name) ).'</p>';
								continue;
							}
							elseif(empty($data->email_send)){
								$not_receiving[$data->people_id] =  '<p>'.esc_html(sprintf(__('%1$s has opted not to receive any emails','church-admin'),$name) ).'</p>'; 
								continue;
							}
							elseif(empty($data->rota_email)){
								$not_receiving[$data->people_id] =  '<p>'.esc_html(sprintf(__('%1$s has opted not to receive any schedule reminder emails','church-admin'),$name) ).'</p>'; 
								continue;
							}
							$name=church_admin_formatted_name($data);
                            if( !empty($data->people_type_id) && $data->people_type_id!=1)
                            {
								//Not an adult - head of household gets and email as well as child
                                $parentEmail=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$data->household_id.'" AND email!="" AND rota_email=1 AND gdpr_reason!="" AND head_of_household=1 ORDER BY people_order LIMIT 1');
                                $parentName=sprintf(__('Parent of %1$s',"church-admin"),$name);
                                if(!empty( $parentEmail) && !empty($data->rota_email) &&!in_array( $parentEmail,$recipients)){
									$recipients[$parentName]=$parentEmail;
								}
                            }
							if(!empty( $data->email)&&!in_array( $data->email,$recipients) && !empty($data->rota_email)&&!empty($data->email_send)){
								//adult
								$recipients[$name]=$data->email;
							}
							
							
							
							if(!empty( $data->pushToken) && !in_array( $data->pushToken,$pushTokens) ) $pushTokens[$name]=$data->pushToken;
							
							
						}
						$message.='<tr><td>'.esc_html( $jobName).'</td><td>'.esc_html(implode(", ",$people) ).'</td></tr>';
					}
				}
				$message.='</table>';
			
				//church_admin_debug($recipients);
			//start emailing the message
			$message.='';
			if(!empty( $recipients) )
			{
				church_admin_debug('Recipients array');
				church_admin_debug( $recipients);
				foreach( $recipients AS $name=>$email)
				{
					 	$email_content='<p>'.esc_html( __('Dear','church-admin' ) ).' '.$name.',</p>'.$message;
						$send_email_content = str_replace('[NAME]',$name,$email_content);
						 echo church_admin_email_send( $email,wp_kses_post(__("This weeks service schedule for ",'church-admin' ) ).mysql2date(get_option('date_format'),$rota_data->rota_date),$send_email_content,null,null,null,null,null,FALSE);
				}
			}
			else{
				echo'<p>'.esc_html( __('No recipients','church-admin' ) ).'</p>';
			}
			if(!empty($not_receiving)){
				echo wp_kses_post(implode("/r/n",$not_receiving));
			}
			//push notification...
			if(!empty( $pushTokenDetails) )
			{
				//church_admin_send_push('tokens',$message_type,$pushTokenDetails,$subject,$message,$sender);
				
			}
	}//end send out email
	else
	{
		if( $service->service_day!=8)  {$day=$wp_locale->get_weekday( $service->service_day);}else{$day='';}
		echo'<h2>'.esc_html (sprintf( __( 'Email service schedule for %1$s at %2$s on %3$s at %4$s', 'church-admin' ),$service->service_name, $service->venue,$day.' '.mysql2date(get_option('date_format'),$rota_data->rota_date),$rota_data->service_time ) ).'</h2>';
		echo'<form action="" method="POST">';
		echo'<div class="church-admin-form-group><label>'.esc_html( __('From name','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" required="required" id="from_name" name="from_name"  ';
		$from_name=get_option('church_admin_default_from_name');
		if(!empty( $from_name) ) echo ' value="'.esc_html( $from_name).'"';
		echo'/></div>';
		echo'<div class="church-admin-form-group><label>'.esc_html( __('From email','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" id="from_email" required="required" name="from_email"  ';
		$from_email=get_option('church_admin_default_from_email');
		if(!empty( $from_email) ) echo ' value="'.esc_html( $from_email).'"';
		echo'/></div>';
		echo'<p>'.esc_html( __('The email will contain a salutation and the service schedule. Please add your own message','church-admin' ) ).'</p>';
		wp_editor('','message',"", true);
		echo'<input type="hidden" name="service_id" value="'.(int)$service_id.'"><input type="hidden" name="rota_date" value="'.esc_attr($date).'">';
		echo'<p><input type="hidden" name="rota_email" value="yes" />'.wp_nonce_field('email-rota').'<input type="submit" class="button-primary" value="'.esc_html( __('Send to rota participants','church-admin' ) ).'" /></p>';
		echo'</form>';
	}

}

 /**
 *
 * SMS next rota out for $service_id
 *
 * @author  Andy Moyle
 * @param    $service_id
 * @return   html
 * @version  0.1
 *
 *
 */

function church_admin_sms_rota( $service_id=NULL)
{
  echo'<h2>'.esc_html( __('SMS schedule','church-admin' ) ).'</h2>';
	if(!church_admin_level_check('Rota') )wp_die(__('You don\'t have permission to do that','church-admin') );
   	$debug=TRUE;
	$provider=get_option('church_admin_sms_provider');
    global $wpdb,$wp_locale;
	if ( empty( $_POST['rota_id'] ) )
	{
		$sql='SELECT a.rota_date, a.rota_id,b.service_name,a.service_time,c.venue FROM '.$wpdb->prefix.'church_admin_new_rota a LEFT JOIN '.$wpdb->prefix.'church_admin_services b ON a.service_id=b.service_id  LEFT JOIN '.$wpdb->prefix.'church_admin_sites c ON b.site_id=c.site_id WHERE a.rota_date >= CURDATE( ) AND b.active=1 GROUP BY a.service_id, a.rota_date ORDER BY rota_date ASC LIMIT 36';
	
		$results=$wpdb->get_results( $sql);
		if(!empty( $results) )
		{
			
			echo'<form action="" method="post"><p><select name="rota_id">';
			foreach( $results AS $row)
			{

				$rotaInstance=mysql2date("j M",$row->rota_date).' '.mysql2date(get_option('time_format'),$row->service_time).' '.esc_html( $row->service_name);
				echo'<option value="'.(int)$row->rota_id.'" '.selected( $rota_id,$row->rota_id,FALSE).'>'.$rotaInstance.'</option>';
			}
			echo'</select><input class="button-primary" type="submit" value="'.esc_html( __('Pick service','church-admin' ) ).'" /></p></form>';
		}
		
	}
	else
	{

		$rota_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE mtg_type="service" AND rota_id="'.(int)$_POST['rota_id'].'" AND rota_date>CURDATE() ORDER BY rota_date ASC LIMIT 1');
		
		
		if(!empty( $rota_details) )
		{
			$service=$wpdb->get_row('SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.service_id="'.esc_sql( $rota_details->service_id).'"');

			if( $service->service_day!=8)  {echo '<h2>'.sprintf( esc_html__( 'Schedule for %1$s at %2$s on %3$s at %4$s', 'church-admin' ), $service->service_name, $service->venue,mysql2date(get_option('date_format'),$rota_details->rota_date),$service->service_time ).'</h2>';}
				else{echo '<h2>'.sprintf( esc_html__( 'Schedule for %1$s at %2$%', 'church-admin' ), $service->service_name, $service->venue).'</h2>';}


			if ( empty( $provider) )
			{
				$out='<h2>Please setup your Bulksms account settings first</h2>';
				echo $out;
				if(!empty( $debug) )church_admin_debug("**********\r\n rota.new.php line632\r\n FORM ".$out."\r\n");
			}
			else
			{
				//initialise sms sending
				require_once(plugin_dir_path(__FILE__).'/sms.php');

				if(!empty( $debug) )church_admin_debug('SMS Schedule Send: '.date('Y-m-d h:i:s') );
				//get jobs
				$jobs=church_admin_required_rota_jobs( $rota_details->service_id);
				//get people and mobile for each job
				$recipients=array();
				foreach( $jobs AS $job_id=>$jobName)
				{
					//array of people
					$people=church_admin_rota_people_array( $rota_details->rota_date,$job_id,$rota_details->service_id,'service');
					$message=esc_html(sprintf(__('A quick reminder from %1$s, on %2$s at %3$s, you are scheduled for %4$s.','church-admin' ) ,get_option('blogname'),mysql2date(get_option('date_format'),$rota_details->rota_date),mysql2date(get_option('time_format'),$service->service_time),$jobs[$job_id]) );
					$extramessage = get_option('church_admin_sms_rota_reply_mesage');
                    if(!empty($extramessage)){$message.=' '.$extramessage;}

					foreach( $people AS $people_id=>$name)
					{
						$mobile='';
						
						$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'" and sms_send=1');
						
						if(!empty( $person->e164cell) )
						{
							$mobile=$person->e164cell;
							
						}
						elseif ( empty( $person->mobile) )
						{
							echo'<p>'.esc_html(sprintf(__('%1$s does not have a mobile number stored','church-admin' ) ,church_admin_formatted_name( $person) )).'</p>';
						}
						else
						{
							$mobile=church_admin_e164( $person->mobile);
							//might as well update while we are here
							$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET e164cell="'.esc_sql( $mobile).'" WHERE people_id="'.(int)$person->people_id.'"');
						}
						if( $provider!='twilio')$mobile=ltrim( $mobile,'+');
						if(!empty( $mobile) )
						{
							$result=church_admin_sms( $mobile,$message);
							if(!empty( $result) )print_r( $result);
							//$result=array('success'=>TRUE);//debug
							if(!empty( $result['success'] ) )
							{
								echo'<p>'.esc_html( __('SMS sent to','church-admin' ) ).' '.esc_html( $name).'</p>';
							}
						}
					}
				}

			}
		}//rota_date found
	}
}


 /**
 *
 * Required rota jobs for service_id
 *
 * @author  Andy Moyle
 * @param    $service_id
 * @return   array rota_task_id=>$rota_task
 * @version  0.1
 *
 *
 */
function church_admin_required_rota_jobs( $service_id)
{
	global $wpdb;
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings  ORDER BY rota_order');
	$requiredRotaJobs=array();
	foreach( $rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize( $rota_task->service_id);
		if(is_array( $allServiceID)&&in_array( $service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
	}
	return $requiredRotaJobs;

}

/**
*
* Check rota wp-cron jobs
*
* @author  Andy Moyle
* @param
* @return
* @version  0.1
*
*
*/
function church_admin_cron_check()
{
 global $wpdb;
 $expected_frequency = array('ah'=>__('Ad hoc','church-admin'),
 '1'=>__('Daily','church-admin'),
 '14'=>__('Fortnightly','church-admin'),
 'm'=>__('Monthly','church-admin'),
 'a'=>__('Annually','church-admin'),
 '70'=>__('Weekly on Sunday','church-admin'),
 '71'=>__('Weekly on Monday','church-admin'),
 '72'=>__('Weekly on Tuesday','church-admin'),
 '73'=>__('Weekly on Wednesday','church-admin'),
 '74'=>__('Weekly on Thursday','church-admin'),
 '75'=>__('Weekly on Friday','church-admin'),
 '76'=>__('Weekly on Saturday','church-admin'),
 'n10'=>__('First Sunday','church-admin'),
 'n11'=>__('First Monday','church-admin'),
 'n12'=>__('First Tuesday','church-admin'),
 'n13'=>__('First Wednesday','church-admin'),
 'n14'=>__('First Thursday','church-admin'),
 'n15'=>__('First Friday','church-admin'),
 'n16'=>__('First Saturday','church-admin'),
 'n20'=>__('Second Sunday','church-admin'),
 'n21'=>__('Second Monday','church-admin'),
 'n22'=>__('Second Tuesday','church-admin'),
 'n23'=>__('Second Wednesday','church-admin'),
 'n24'=>__('Second Thursday','church-admin'),
 'n25'=>__('Second Friday','church-admin'),
 'n26'=>__('Second Saturday','church-admin'),
 'n30'=>__('Third Sunday','church-admin'),
 'n31'=>__('Third Monday','church-admin'),
 'n32'=>__('Third Tuesday','church-admin'),
 'n33'=>__('Third Wednesday','church-admin'),
 'n34'=>__('Third Thursday','church-admin'),
 'n35'=>__('Third Friday','church-admin'),
 'n36'=>__('Third Saturday','church-admin'),
 'n40'=>__('Fourth Sunday','church-admin'),
 'n41'=>__('Fourth Monday','church-admin'),
 'n42'=>__('Fourth Tuesday','church-admin'),
 'n43'=>__('Fourth Wednesday','church-admin'),
 'n44'=>__('Fourth Thursday','church-admin'),
 'n45'=>__('Fourth Friday','church-admin'),
 'n46'=>__('Fourth Saturday','church-admin'),
);
    $cron=get_option('cron');
	/*
		echo'<pre>';
		print_r($cron);
		echo'</pre>';
    */
	echo'<h2>'.esc_html( __('Current auto schedule email/sms wp-cron jobs','church-admin' ) ).'</h2>';
    $tableData='';
    if(!empty( $cron) )
    {
        foreach( $cron AS $ts=>$details)
        {
             if(!empty( $details['church_admin_cron_email_rota'] ) )
             {
                    $churchAdminCronJobs=$details['church_admin_cron_email_rota'];
					church_admin_debug( $details);


                    foreach( $churchAdminCronJobs AS $key=>$churchAdminCronJob)
                    {
                        
                        $service_id=$churchAdminCronJob['args']['service_id'];
                        $serviceRow=$wpdb->get_row('SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.service_id="'.esc_sql( $service_id).'"');
                        if(!empty( $serviceRow) )
                        {
                            
                            $service=sprintf( esc_html__( '%1$s at %2$s (%3$s %4$s)', 'church-admin' ), $serviceRow->service_name, $serviceRow->venue,$expected_frequency[$serviceRow->service_frequency],$serviceRow->service_time);
                            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-cron&amp;section=rota&which=email&ts='.esc_html( $ts).'&key='.esc_html( $key),'delete-cron').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';

                            $tableData.='<tr><td>'.$delete.'</td><td>'.esc_html( __('Email','church-admin' ) ).'</td><td>'.mysql2date(get_option('date_format').' '.get_option('time_format'),date('Y-m-d h:i:s',$ts) ).'</td><td>'.esc_html( $service).'</td></tr>';
                        }
                    }

                }
				if(!empty( $details['church_admin_cron_sms_rota'] ) )
             {
                    $churchAdminCronJobs=$details['church_admin_cron_sms_rota'];
					church_admin_debug( $details);


                    foreach( $churchAdminCronJobs AS $key=>$churchAdminCronJob)
                    {
                        
                        $service_id=$churchAdminCronJob['args']['service_id'];
                        $serviceRow=$wpdb->get_row('SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.service_id="'.(int)$service_id.'"');
                        if(!empty( $serviceRow) )
                        {
                            
                            $service=sprintf( esc_html__( '%1$s at %2$s (%3$s at %4$s)', 'church-admin' ), $serviceRow->service_name, $serviceRow->venue,$expected_frequency[$serviceRow->service_frequency],$serviceRow->service_time);
                            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-cron&amp;section=rota&which=sms&ts='.esc_html( $ts).'&key='.esc_html( $key),'delete-cron').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';

                            $tableData.='<tr><td>'.$delete.'</td><td>'.esc_html( __('SMS','church-admin' ) ).'</td><td>'.mysql2date(get_option('date_format').' '.get_option('time_format'),date('Y-m-d h:i:s',$ts) ).'</td><td>'.esc_html( $service).'</td></tr>';
                        }
                    }

                }
        }
		  if(!empty( $tableData) )
			{
				
				echo '<table class="widefat"><thead><tr><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Email/SMS','church-admin' ) ).'</th><th>'.esc_html( __('Next send','church-admin' ) ).'</th><th>'.esc_html( __('Which Service','church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Email/SMS','church-admin' ) ).'</th><th>'.esc_html( __('Next send','church-admin' ) ).'</th><th>'.esc_html( __('Which Service','church-admin' ) ).'</th></tr></tfoot><tbody>';
				echo $tableData;
				echo'</tbody></table>';
			}
            else
            {//there is cron but no church admin ones
              echo'<p>'.esc_html( __('No cron jobs set to email/sms the schedule','church-admin' ) ).'</p>';
                echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=auto-email-rota','auto-email-rota').'">'.esc_html( __('Setup auto email of schedule','church-admin' ) ).'</a></p>';
				echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=auto-sms-rota','auto-sms-rota').'">'.esc_html( __('Setup auto SMS of schedule','church-admin' ) ).'</a></p>';
          }
    }
    else
    {//no cron jobs set up at all (unlikely to happen!!!!)
        echo'<p>'.esc_html( __('No cron jobs set to email/sms the schedule','church-admin' ) ).'</p>';
        echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=auto-email-rota','auto-email-rota').'">'.esc_html( __('Setup auto email of schedule','church-admin' ) ).'</a></p>';
		echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=rota&action=auto-sms-rota','auto-sms-rota').'">'.esc_html( __('Setup auto SMS of schedule','church-admin' ) ).'</a></p>';
    }
}

function church_admin_delete_cron( $ts,$key,$what)
{
	church_admin_debug("delete cron function");
	church_admin_debug("TS $ts");
	church_admin_debug("key $key");
	church_admin_debug("what $what");
	if(!church_admin_level_check('Rota') )wp_die(__('You don\'t have permission to do that','church-admin') );
	$cron=get_option('cron');
	church_admin_debug("before deletion");
	//church_admin_debug($cron);
	switch($what)
	{
		case'sms':
			$hook='church_admin_cron_sms_rota';
			
		break;
		case'email':
			$hook='church_admin_cron_email_rota';
		break;
	}
	unset($cron[$ts][$hook]);
	church_admin_debug("after deletion");
	//church_admin_debug($cron);
    update_option('cron',$cron);
	
}

function church_admin_email_rota_form()
{
    global $wpdb,$wp_locale;
    echo'<h2>'.esc_html( __('Email out service schedule','church-admin' ) ).'</h2>';
    echo'<form action="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=email-rota','email-rota').'" method="POST">';
	
    echo'<table ><tr><td><select id="services" name="service_id">';
    echo'<option value="">'.esc_html( __('Choose a service','church-admin' ) ).'...</option>';
    $services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
    foreach( $services AS $service)
    {
    echo'<option value="'.$service->service_id.'">'.esc_html(sprintf( __('%1$s on %2$s at %3$s ', 'church-admin' ) , $service->service_name,$wp_locale->get_weekday( $service->service_day),$service->service_time) ).'</option>';
    }
        echo'</select></td><td><span id="dates">'.esc_html( __('Choose services, then choice of dates will appear','church-admin' ) ).'</span></td><td><input  	class="button-primary"  type="submit" name="submit" value="'.esc_html( __('Send service schedule','church-admin' ) ).'"></td></tr></table>';
    echo'</form>';
    $nonce = wp_create_nonce("rota-dates");
   echo'<script>jQuery(document).ready(function( $) {
			$("body").on("change","#services",function() {
            
			var service_id=$(this).val();
			var data = {
                "action": "church_admin",
                "method":"rota-dates",
                "service_id":service_id,
                "nonce": "'.$nonce.'"
		      };
        console.log(data);
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			$("#dates").html(response);
		});

			});
			});</script>';
}

function church_admin_rota_auto_email()
{
    global $wpdb,$wp_locale;
    echo'<h2>'.esc_html( __('Communicate the schedule ','church-admin' ) ).'</h2>';
	if(!empty( $_POST['email_rota_day'] ) )
    {   
        echo'<div class="notice notice-success"><h2>'.esc_html( __('Schedule cron job saved','church-admin' ) ).'</h2></div>';
        church_admin_cron_check();
    }
    else
    {    $email_day=get_option('church_admin_email_rota_day');
        if(!empty( $email_day)&&!empty( $rota_days[$email_day] ) ) echo'<p><strong>'.esc_html(sprintf(__('This week\'s schedules are automatically emailed on %1$s, when your website is first accessed that day.','church-admin'  ),$rota_days[$email_day]) ).'</strong></p>';
        echo'<form action="" method="POST">';
        echo'<h3>'.esc_html( __('Set up auto email of schedule','church-admin' ) ).'</h3>';
        echo'<table ><tr><th scope="row">'.esc_html( __('Which Service?','church-admin' ) ).'</th><td><select name="service_id">';
        $services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');    
        foreach( $services AS $service) echo'<option value="'.(int)$service->service_id.'">'.esc_html( $service->service_name).'</option>';
        echo'</select>';
        echo'</td><td>'. esc_html(__("Automatically email current week's schedule",'church-admin' ) ).'</td><td>';
        echo'<select name="email_rota_day">';
        echo'<option value="8"'.selected( $email_day, NULL, FALSE  ).'>'.esc_html( __('No Auto Send','church-admin' ) ).'</option>';
        echo'<option value="1"'.selected( $email_day, 1, FALSE  ).'>'.esc_html( __('Monday','church-admin' ) ).'</option>';
        echo'<option value="2"'.selected( $email_day, 2 , FALSE ).'>'.esc_html( __('Tuesday','church-admin' ) ).'</option>';
        echo'<option value="3"'.selected( $email_day, 3, FALSE  ).'>'.esc_html( __('Wednesday','church-admin' ) ).'</option>';
        echo'<option value="4"'.selected( $email_day, 4, FALSE  ).'>'.esc_html( __('Thursday','church-admin' ) ).'</option>';
        echo'<option value="5"'.selected( $email_day, 5 , FALSE ).'>'.esc_html( __('Friday','church-admin' ) ).'</option>';
        echo'<option value="6"'.selected( $email_day, 6, FALSE  ).'>'.esc_html( __('Saturday','church-admin' ) ).'</option>';
        echo'<option value="7"'.selected( $email_day, 7 , FALSE ).'>'.esc_html( __('Sunday','church-admin' ) ).'</option>';
        echo'</select><td></tr>';
        $message='';
        $message=get_option('church_admin_auto_rota_email_message');
        echo '<tr><th scope="row">'.esc_html( __('Email message','church-admin' ) ).'</th><td colspan=2><textarea name="auto-rota-message" class="large-text">'.esc_textarea( $message ).'</textarea></td></tr>';
        echo'<tr><td cellpsacing=2><input   class="button-primary" type="submit" value="Save" /></td></tr></table></form>';
    }
}

function church_admin_rota_auto_sms()
{
    global $wpdb,$wp_locale;
    echo'<h2>'.esc_html( __('Automatically SMS the schedule ','church-admin' ) ).'</h2>'."\r\n";
	if(!empty( $_POST['sms_rota_day'] ) )
    {   
        echo'<div class="notice notice-success"><h2>'.esc_html( __('Schedule cron job saved','church-admin' ) ).'</h2></div>';
        church_admin_cron_check();
    }
    else
    {    $sms_day=get_option('church_admin_sms_rota_day');
		$sms_time=get_option('church_admin_sms_rota_time');
		if(empty($sms_time)){$sms_time='10:00';}
        if(!empty( $sms_day)&&!empty( $rota_days[$sms_day] ) ) echo'<p><strong>'.esc_html(sprintf(__('This week\'s schedules are automatically SMS\'d on %1$s, when your website is first accessed that day after %2$s.','church-admin' ) ,$rota_days[$sms_day],$sms_time) ) .'</strong></p>';
        echo'<form action="admin.php?page=church_admin/index.php&action=auto-sms-rota" method="POST">'."\r\n";
		wp_nonce_field('auto-sms-rota');
        echo'<h3>'.esc_html( __('Set up auto email of schedule','church-admin' ) ).'</h3>'."\r\n";
        echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Which Service?' , 'church-admin' ) ).'</label><select class="church-admin-form-control" name="service_id">';
        $services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');    
        foreach( $services AS $service) echo'<option value="'.(int)$service->service_id.'" >'.esc_html( $service->service_name).'</option>';
        echo'</select>';
        echo'</div>'."\r\n";
		echo '<div class="church-admin-form-group"><label>'.esc_html( __("Automatically SMS current week's schedule",'church-admin' ) ).'</label>';
        echo'<select class="church-admin-form-control" name="sms_rota_day">';
        echo'<option value="8" '.selected( $sms_day, NULL, FALSE ).'>'.esc_html( __('No Auto Send','church-admin' ) ).'</option>';
        echo'<option value="1" '.selected( $sms_day, 1, FALSE  ).'>'.esc_html( __('Monday','church-admin' ) ).'</option>';
        echo'<option value="2" '.selected( $sms_day, 2, FALSE  ).'>'.esc_html( __('Tuesday','church-admin' ) ).'</option>';
        echo'<option value="3" '.selected( $sms_day, 3, FALSE  ).'>'.esc_html( __('Wednesday','church-admin' ) ).'</option>';
        echo'<option value="4" '.selected( $sms_day, 4, FALSE  ).'>'.esc_html( __('Thursday','church-admin' ) ).'</option>';
        echo'<option value="5" '.selected( $sms_day, 5, FALSE  ).'>'.esc_html( __('Friday','church-admin' ) ).'</option>';
        echo'<option value="6" '.selected( $sms_day, 6, FALSE  ).'>'.esc_html( __('Saturday','church-admin' ) ).'</option>';
        echo'<option value="7" '.selected( $sms_day, 7, FALSE  ).'>'.esc_html( __('Sunday','church-admin' ) ).'</option>';
        echo'</select></div>'."\r\n";
       echo'<div class="church-admin-form-group"><label>'.esc_html( __('Approx time to send','church-admin' ) ).'</label><input class="church-admin-form-control" type="time" name="sms_time" value="'.esc_html($sms_time).'" /></div>'."\r\n";
        
        echo'<p><input   class="button-primary" type="submit" value="Save" /></p></form>'."\r\n";
    }
}

function church_admin_rota_pdf_menu()
{
    global $wpdb,$wp_locale;
    	echo'<h2 >'.esc_html( __('Schedule PDF','church-admin' ) ).' </h2>';
	echo'<form action="'.home_url().'" method="GET"><table class="form-table">';
		//dates
		echo'<tr><th scope="row">'.esc_html( __('Select month','church-admin' ) ).'</th><td><select name="date">';	
		for ( $x=0; $x<=12; $x++)
		{
			$date=date('Y-m-01',strtotime("+ $x month") );
			echo '<option value="'.$date.'">'.mysql2date('M Y', $date).'</option>';
		}
		echo'</select></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Select Service','church-admin' ) ).'<input type="hidden" name="ca_download" value="horizontal_rota_pdf" /></th><td><select name="service_id">';

		$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
		foreach( $services AS $service)
		{
					if( $service->service_day!=8)  {echo'<option value="'.$service->service_id.'">'.esc_html(sprintf( __('%1$s on %2$s at %3$s', 'church-admin' ) ,$service->service_name,$wp_locale->get_weekday( $service->service_day),$service->service_time)).'</option>';}
					else{echo'<option value="'.$service->service_id.'">'.$service->service_name.'</option>';}
		}
		echo'</select></td></tr>';
        /*
        $rota_jobs=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
        foreach( $rota_jobs AS $rota_job)
        {
			
            echo'<tr><th scope="row">'.$rota_job->rota_task.'</th><td><input type="checkbox" name="rota_id[]" value="'.$rota_job->rota_id.'" /> '.esc_html( __('Initials?','church-admin' ) ).'<input type="checkbox" name="initials[]" value="'.$rota_job->rota_id.'" /></td></tr>';

		
        }*/

        echo'<tr><td colspan="2"><input   class="button-primary" type="submit" value="'.esc_html( __('Create PDF','church-admin' ) ).'" /></td></tr></table></form>';
   		
}


function church_admin_rota_csv_menu()
{
    global $wpdb,$wp_locale;
    /*********************************************************
        *
        *   Rota CSV
        *
        ***********************************************************/
        echo'<h2>'.esc_html( __('Download schedule CSV','church-admin' ) ).'</h2>';
        echo'<form action="" method="GET"><table>';
        echo'<tr><th scope="row">'.esc_html( __('Select Service','church-admin' ) ).'<input type="hidden" name="ca_download" value="rota-csv" /></th><td><select name="service_id">';
        $services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
		foreach( $services AS $service)
		{
			if( $service->service_day!=8)  {
				echo'<option value="'.$service->service_id.'">'.esc_html(sprintf( __('%1$s on %2$s at %3$s', 'church-admin' ) ,$service->service_name,$wp_locale->get_weekday( $service->service_day),$service->service_time)).'</option>';
			}
			else{echo'<option value="'.$service->service_id.'">'.$service->service_name.'</option>';}
        }
		echo'</select></td></tr>';
        echo'<tr><th scope="row">'.esc_html( __('Earliest date','church-admin' ) ).'</th><td>'.church_admin_date_picker(NULL,'start_date',FALSE,date("Y-m-d",strtotime("-10years") ),NULL,'start_date','start_date').'</td></tr>';
        echo'<tr><th scope="row">'.esc_html( __('Latest date','church-admin' ) ).'</th><td>'.church_admin_date_picker(NULL,'end_date',FALSE,date("Y-m-d",strtotime("-10years") ),NULL,'end_date','end_date').'</td></tr>';
        echo'<tr><td colspan="2"><input   class="button-primary" type="submit" value="'.esc_html( __('Download CSV','church-admin' ) ).'" /></td></tr></table></form>';
    
}

function church_admin_three_months_rota( $service_id,$mtg_type='service')
{
	global $wpdb,$wp_locale;
	echo'<h3>'.esc_html( __('Add three months of dates to schedule','church-admin' ) ).'</h3>';
	if ( empty( $service_id) )
	{
		//check for more than one service and show form if there is
		$sql='SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.active=1';
		
		$services=$wpdb->get_results( $sql);
		
		$noOfServices=$wpdb->num_rows;
		
		//always show choose service form if more than one
		if( $noOfServices==0)  {echo'<div class="notice notice-inline notice-warning"><h2>'.esc_html( __('No active services set up','church-admin' ) ).'</h2><p><a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-service&section=services','edit-service').'">'.esc_html( __('Add a service','church-admin' ) ).'</a></p></div>';}
		if( $noOfServices>1)
		{
			echo'<form action="admin.php" method="GET">';
			wp_nonce_field('add-three-months');
			echo'<input type="hidden" name="page" value="church_admin/index.php" /><input type="hidden" name="section" value="rota" /><input type="hidden" name="action" value="add-three-months" />';
			echo'<table class="form-table"><tbody><tr><th scope=row>'.esc_html( __('Which Service?','church-admin' ) ).'</th><td><select name="service_id">';
			echo'<option>'.esc_html( __('Which Service','church-admin' ) ).'</option>';
			foreach( $services AS $service)
			{

				if( $service->service_day!=8)
				{
					echo'<option value="'.(int)$service->service_id.'">'.sprintf( esc_html__( '%1$s at %2$s on %3$s %4$s', 'church-admin' ), $service->service_name, $service->venue,$wp_locale->get_weekday( $service->service_day),$service->service_time).'</option>';
				}
				else
				{
					echo'<option value="'.(int)$service->service_id.'">'.sprintf( esc_html__( '%1$s at %2$s', 'church-admin' ), $service->service_name, $service->venue ).'</option>';
				}

			}
			echo'</select> <input type="submit" class="button-primary" name="choose_service" value="'.esc_html( __('Choose service','church-admin' ) ).' &raquo;" /></td></tr></tbody></table></form>';
		}else{
			$service_id=$services[0]->service_id;
		}

	}
	
	$service=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
	if ( empty( $service) )return __('Service does not exist','church-admin');

	$requiredRotaJobs=church_admin_required_rota_jobs( $service_id);

	$last_date=$wpdb->get_var('SELECT MAX(rota_date) FROM '.$wpdb->prefix.'church_admin_new_rota WHERE mtg_type="'.esc_sql( $mtg_type).'" AND service_id="'.(int)$service_id.'"');
	if ( empty( $last_date) )$last_date=wp_date('Y-m-d');
	$date=new DateTime( $last_date);
	$nextDate=$date->modify('next '.$wp_locale->get_weekday( $service->service_day) );

	$values=array();
	for ( $int=0; $int<12; $int++)
	{
		foreach( $requiredRotaJobs AS $ID=>$job)
		{
			$values[]='("'.(int)$ID.'","'.esc_sql( $date->format('Y-m-d') ).'",NULL,"'.(int)$service_id.'","'.esc_sql( $mtg_type).'")';
		}
		$date=$date->modify('+7 days');
	}
	$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_new_rota (rota_task_id,rota_date,people_id,service_id,mtg_type) VALUES '.implode(",",$values) );

	echo'<div class="notice notice-success"><h2>'.esc_html( __('Three months added','church-admin' ) ).'</h2></div>';
	church_admin_rota_list( $service_id);

}

function church_admin_copy_rota_data(){

	global $wpdb;

	/******************************
    * copy rota and then redirect
    *******************************/
	
        $services =  church_admin_services_array();
        church_admin_debug('***** Copying rota *****');
		church_admin_debug($_REQUEST);
		//update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize//sanitize
        //church_admin_debug('Sanitizing');
        $rota_date1 = !empty($_REQUEST['rotaDate1'])?sanitize_text_field(stripslashes($_REQUEST['rotaDate1'] ) ):null;
        //church_admin_debug('Rota date 1: '.$rota_date1);
        $rota_date2 = !empty($_REQUEST['rotaDate2'])?sanitize_text_field(stripslashes($_REQUEST['rotaDate2'] ) ):null;
        //church_admin_debug('Rota date 2: '.$rota_date2);
        $service_id = !empty($_REQUEST['service_id'])?sanitize_text_field(stripslashes($_REQUEST['service_id'] ) ):null;
        //church_admin_debug('Service id: '.$service_id);
        $mtg_type   = !empty($_REQUEST['mtg_type'])?sanitize_text_field(stripslashes($_REQUEST['mtg_type'] ) ):'service';
        //church_admin_debug('Mtg Type: '.$mtg_type);
        //validate
        //church_admin_debug('Validating');
        $validated = TRUE;

        if( empty( $rota_date1 ) || !church_admin_checkdate($rota_date1)) { 
            $validated = FALSE; 
            church_admin_debug('Rota date 1 fail');
        }
        if( empty( $rota_date2 ) || !church_admin_checkdate($rota_date2)) { 
            $validated = FALSE; 
            church_admin_debug('Rota date 2 fail');
        }
        if(  empty( $services) || empty( $service_id ) || empty($services[$service_id])){ 
            $validated = FALSE; 
            church_admin_debug('Service id  fail');
        }
        if(!empty($mtg_type) && $mtg_type!='service'){ 
            $validated = FALSE; 
            church_admin_debug('Mtg type fail');
        }
		$_POST['start_rota_date'] = $rota_date2; //to show correct rota month
        if(!empty($validated))
        {
            church_admin_debug('Passed validation');
            
            $message=church_admin_copy_rota( $rota_date1,$rota_date2, $service_id,$mtg_type );
            echo'<div class="notice notice-success"><h2>'.esc_html(__('Schedule data copied','church-admin')).'</h2></div>';
			church_admin_rota_list( $service_id);
        } 
	
}