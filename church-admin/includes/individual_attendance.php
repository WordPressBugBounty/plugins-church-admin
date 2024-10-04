<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_individual_attendance_list()
{
	global $wpdb;
	if(!empty( $_POST['action'] )&&$_POST['action']=='delete')
	{
		$date = !empty($_POST['date']) ? sanitize_text_field( stripslashes( $_POST['date'] ) ):null;
		$meeting_id = !empty($_POST['meeting_id']) ? sanitize_text_field( stripslashes( $_POST['meeting_id'] ) ):null;
		$meeting_type = !empty($_POST['meeting_type']) ? sanitize_text_field( stripslashes( $_POST['meeting_type'] ) ):null;
		if(!empty($date) && church_admin_checkdate($date) && !empty($meeting_id) && church_admin_int_check($meeting_id) &&!empty($meeting_type))
		{
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE `date`="'.esc_sql($date ).'" AND meeting_id="'.(int)$meeting_id.'" AND meeting_type="'.esc_sql( $meeting_type ).'"');
			
			$wpdb->query=('DELETE FROM '.$wpdb->prefix.'church_admin_attendance WHERE `date`="'.esc_sql($date ).'" AND meeting_id="'.(int)$meeting_id.'" AND meeting_type="'.esc_sql( $meeting_type ).'"');
			echo'<div class="notice notice-success"><h2>'.esc_html( __('Attendance deleted','church-admin' ) ).'</h2></div>';
		}
		
	}
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_individual_attendance GROUP BY `date`,`meeting_type` ORDER BY date DESC';
	$results=$wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		$theader='<tr><th>'.esc_html( __('Delete','church-admin')).'</th><th>'.esc_html( __('Edit','church-admin')).'</th><th>'.esc_html( __('Date','church-admin')).'</th><th>'.esc_html( __('Meeting type','church-admin' ) ).'</th><th>'.esc_html( __('Meeting','church-admin' ) ).'</th><th>'.esc_html( __('People count','church-admin' ) ).'</th></tr>';
		echo'<table class="widefat striped"><thead>'.$theader.'</thead><tbody>';
		foreach( $results AS $row)
		{
			switch( $row->meeting_type)
				{
					case 'smallgroup':
						$meeting = 'smallgroup-'.(int)$row->meeting_id;
						$which=$wpdb->get_var('SELECT group_name FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$row->meeting_id.'"');
					break;
					case 'class':
						$meeting = 'class-'.(int)$row->meeting_id;
						$which=$wpdb->get_var('SELECT name FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$row->meeting_id.'"');
					break;
					default:
					case 'service':
						$meeting = 'service-'.(int)$row->meeting_id;
						$which=$wpdb->get_var('SELECT service_name FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$row->meeting_id.'"');
						
					break;
				}
			$count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE meeting_type="'.esc_sql( $row->meeting_type).'" AND `date`="'.esc_sql( $row->date).'"');
			$delete='<form action="" method="POST"><input type="hidden" name="meeting_id" value="'.esc_attr( $row->meeting_id).'" /><input type="hidden" name="meeting_type" value="'.esc_html( $row->meeting_type).'" /><input type="hidden" name="date" value="'.esc_attr( $row->date).'" /><input type="hidden" name="action" value="delete" /><input type="submit" class="button-primary" onClick="return confirmSubmit()" value="'.esc_attr( __('Delete attendance','church-admin')).'" /></form>';
			$edit = '<a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-individual-attendance&meeting='.esc_attr( $meeting).'&date='.esc_attr($row->date),'individual-attendance').'">'.esc_html(__('Edit','church-admin')).'</a>';
			echo'<tr><td>'.$delete.'</td><td>'.$edit.'</td><td>'.mysql2date(get_option('date_format'),$row->date).'</td><td>'.esc_html(ucwords( $row->meeting_type) ).'</td><td>'.esc_html( $which).'</td><td>'.(int)$count.'</td></tr>';
		}
		echo'</tbody></table>';
		echo '<script>function confirmSubmit()
		{
		var agree=confirm("'.esc_html( __('Are you sure','church-admin' ) ).'");
		if (agree)
		 return true ;
		else
		 return false ;
		}</script>';
	}
}


/**
 *
 * Individual attendance tracking form
 *
 * @author  	Andy Moyle
 * @param    	null
 * @return   	html
 * @version  	1.2450
 * @date 		2017-01-03
 */
function church_admin_individual_attendance()
{
	church_admin_debug('**** INDIVIDUAL ATTENDANCE *****');
		global $wpdb,$wp_locale;
		$out='<div class="church-admin-attendance"><h2>'.esc_html( __('Individual Attendance','church-admin' ) ).'</h2>';
		$out.='<h3 class="toggle" id="attendance-download">'.esc_html( __('CSV download (Click to show/hide)','church-admin' ) ).'</h3>';
		$out.='<div class="attendance-download" ';
		if ( empty( $_POST['ind_att_csv'] ) )$out.='style="display:none" ';
		$out.='>';
		$out.=church_admin_individual_attendance_csv();
		$out.='</div><script type="text/javascript">jQuery(function( $)  {  $(".toggle").click(function()  {var id=$(this).attr("id");jQuery("."+id).toggle();  });});</script>';
		$out.='<h3 class="toggle" id="attendance">'.esc_html( __('Add Individual Attendance ','church-admin' ) ).'</h3>';
		
		/***************************************************************
		*
		*	Option to choose which type of attendance
		*
		***************************************************************/
		if ( empty( $_GET['meeting'] ) )
		{

			$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
			if(!empty( $services) )
			{
				$option='';
				foreach( $services AS $service)
				{
					$option.='<option value="service-'.$service->service_id.'">'.$service->service_name.' on '.$wp_locale->get_weekday( $service->service_day).' at '.$service->service_time.'</option>';
				}
			}
			$smallgroups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
			if(!empty( $smallgroups) )
			{
				foreach( $smallgroups AS $smallgroup)$option.='<option class="smallgroup" value="smallgroup-'.intval( $smallgroup->id).'">Small Group - '.esc_html( $smallgroup->group_name).'</option>';
			}

			$out.='<form action="" method="GET">';
			$out.=wp_nonce_field('individual-attendance','_wpnonce',FALSE);
			if(is_admin() )$out.='<input type="hidden" name="page" value="church_admin/index.php" /><input type="hidden" name="action" value="individual_attendance" /><input type="hidden" name="tab" value="services" />';
			$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Which Meeting','church-admin' ) ).'</label><select class="church-admin-form-control" name="meeting">'.$option.'</select></div>';
			$member_type=church_admin_member_types_array();
			$first=$option='';
			$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Member type','church-admin' ) ).'</label></div>';
			foreach( $member_type AS $id=>$type)
			{

				$out.='<div class="checkbox"><label><input type="checkbox" name="member_type_id[]" value="'.(int)$id.'" />'.esc_html($type).'</label></div>';
			}
			
			$out.='<p><input type="submit"  class="button-primary" value="'.esc_html( __('Choose','church-admin' ) ).'" /></p>';
			$out.='</form>';
		}
		else
		{

			$meeting=explode("-",sanitize_text_field(stripslashes($_GET['meeting'] ) ) );
			church_admin_debug('Meeting: '.print_r($meeting,TRUE));
			if(!empty( $_POST['save_ind_att'] ) )
			{

				/***************************************************************
				*
				*	Process
				*
				***************************************************************/
				$adult=$child=0;
				$date=!empty($_POST['date'] ) ? sanitize_text_field( stripslashes($_POST['date'] ) ):null;
				if(!empty($date) && church_admin_checkdate($date))
				{
					//populate individual attendance table

					//first delete old save if present
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE `date`="'.esc_sql($date).'" AND meeting_type="'.esc_sql( $meeting[0] ).'" AND meeting_id="'.esc_sql( $meeting[1] ).'"');
					church_admin_debug($wpdb->last_query);
					$values=array();
					$people_ids = !empty($_POST['people_id'])? church_admin_sanitize($_POST['people_id']):array();
					foreach( $people_ids AS $key=>$people_id)
					{

						$values[]='("'.esc_sql($date).'","'.(int)$people_id.'","'.esc_sql( $meeting[0] ).'","'.esc_sql( $meeting[1] ).'")';
						//find people type so that main attendance can be populated...
						$sql='SELECT people_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"';
						$person_type=$wpdb->get_var( $sql);

						switch( $person_type)
						{
							case 1:$adult++;break;
							case 2:$child++;break;
							case 3:$child++;break;
						}
					}
					$adult+=intval( $_POST['visitor-adults'] );
					$child+=intval( $_POST['visitor-children'] );
					$sql='INSERT INTO '.$wpdb->prefix.'church_admin_individual_attendance (`date`,people_id,meeting_type,meeting_id) VALUES '.implode(",",$values);
					$wpdb->query( $sql);
					church_admin_debug($sql);
					//process main attendance table
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_attendance WHERE `date`="'.$date.'" and service_id="'.(int)$meeting[1].'" AND mtg_type="'.esc_sql( $meeting[0] ).'"');
					if ( empty( $check) )
					{
 						$sql='INSERT INTO '.$wpdb->prefix.'church_admin_attendance (`date`,adults,children,service_id,mtg_type)VALUES("'.esc_sql($date).'","'.(int)$adult.'","'.(int)$child.'","'.(int)$meeting[1].'","'.esc_sql( $meeting[0] ).'")';
						church_admin_debug($sql);
						$wpdb->query( $sql);
						
					}
					church_admin_refresh_rolling_average();
					$out.='<div class="notice notice-inline notice-success"><h2>'.esc_html( __('Attendance saved','church-admin' ) ).'</h2></div>';
					require_once(CA_PATH.'display/graph.php');
					$meet="S/1";
					
					switch( $meeting[0] )
					{
						case'service':
							$meet='S/'.(int)$meeting[1];
						break;
						case'smallgroup':
							$meet='G/'.(int)$meeting[1];
						break;
						case'class':
							$meet='C/'.(int)$meeting[1];
						break;
						default:
							$meet="S/1";
						break;
					}
					church_admin_debug('Meet: '.$meet);
					$out.= church_admin_graph('weekly',$meet,date('Y-m-d',strtotime('-1 year') ),date('Y-m-d'),900,500,TRUE);
				}
			}
			else
			{
				/***************************************************************
				*
				*	Form
				*
				***************************************************************/
				if(!is_array($meeting)){return;}
				//People Query

				switch( $meeting[0] )
				{
					case 'smallgroup':
						$sql='SELECT a.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="smallgroup" AND b.ID="'.(int)$meeting[1].'"';
						$meeting_type=__('Small Group','church-admin');
						$which=$wpdb->get_var('SELECT group_name FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$meeting[1].'"');
					break;
					default:
					case 'service':
						$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_services b WHERE a.site_id=b.site_id AND b.service_id="'.(int)$meeting[1].'"';
						$meeting_type=__('Service','church-admin');
						$which=$wpdb->get_var('SELECT service_name FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$meeting[1].'"');
					break;
				}
				//add in member types
				$membSQL='';
				$membsql=Array();
				$member_types= !empty( $_GET['member_type_id'] ) ?church_admin_sanitize( $_GET['member_type_id'] ):array();
				if(!empty($member_types) ){
					foreach( $member_types AS $key=>$value)  {
						if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id="'.(int)$value.'"';
					}
				}
				if(!empty( $membsql) ) {$membSQL=' AND ('.implode(' || ',$membsql).')';}
				//get relevant people
				$query=$sql.$membSQL.' ORDER BY last_name, first_name';

				$people=$wpdb->get_results( $query);
				church_admin_debug($wpdb->last_query);
				$already=array();
				$already_results=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE `date`="'.esc_sql(wp_date('Y-m-d')).'" AND meeting_type="'.esc_sql( $meeting[0] ).'" AND meeting_id="'.esc_sql( $meeting[1] ).'"');
				church_admin_debug($wpdb->last_query);
				if(!empty( $already_results) ){
					foreach( $already_results AS $already_row)$already[]=$already_row->people_id;
				}

				if(!empty( $people) )
				{
					$out.= '<h3>'.$meeting_type.' - '.esc_html( $which).'</h3>';
					$out.='<form action="" method="POST">';
					$date=!empty($_REQUEST['date']) && church_admin_checkdate($_REQUEST['date']) ? church_admin_sanitize($_REQUEST['date']) :date('Y-m-d',current_time('timestamp') );
					$out.='<p><label><strong>'.esc_html( __('Date','church-admin' ) ).':</strong></label>'.church_admin_date_picker( $date,'date',FALSE,'2011',date('Y',time()+60*60*24*365*10),'date','date').' '.esc_html( __('Select date to edit individual attendance','church-admin' ) ).'</p>';
					$out.='<table class="wp-list-table striped"><thead><tr><th><input type="checkbox" class="all-people" /> '.esc_html( __('Attended?','church-admin' ) ).'</th><th>'.esc_html( __('Photo','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Address','church-admin' ) ).'</th></tr></thead>';

					foreach( $people AS $person)
					{
						$out.='<tr><td><input type="checkbox" name="people_id[]" class="people_id" id="person-'.(int)$person->people_id.'" value="'.(int)$person->people_id.'"';
						if(is_array( $already) && in_array( $person->people_id,$already) )$out.=' checked="checked" ';
						$out.='/></td>';
						if(!empty( $person->attachment_id) )
						{//photo available
							$out.='<td>'. wp_get_attachment_image( $person->attachment_id,'ca-people-thumb',NULL,array('class'=>'alignleft') ).'</td>';
						}
						else $out.='<td><img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="75" height="75" class="frontend-image current-photo alignleft" alt="'.esc_html( __('Photo of Person','church-admin' ) ).'"  /></td>';
						$name=array_filter(array( $person->first_name,$person->middle_name,$person->prefix,$person->last_name) );
						$address='';
						$address=$wpdb->get_var('SELECT address FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$person->household_id.'"');
						$out.='<td><strong>'.esc_html(implode(" ",$name) ).'</td><td>'.esc_html( $address).'</td></tr>';
					}
					$out.= '<tr><th scope="row">'.esc_html( __('How many visiting adults?','church-admin' ) ).'</th><td><input type="text" name="visitor-adults" placeholder="0" /></td></tr>';
					$out.= '<tr><th scope="row">'.esc_html( __('How many visiting children?','church-admin' ) ).'</th><td><input type="text" name="visitor-children" placeholder="0" /></td></tr>';
					$out.='</table><p><input type="hidden" name="save_ind_att" value="yes" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
					$nonce = wp_create_nonce("individual-attendance");
					$out.='<script type="text/javascript">jQuery(function( $)  {
						$(".all-people:checkbox").change(function()  {console.log("All people clicked"); $(".people_id").not(this).prop("checked", this.checked); });
						$(".datex").on("change",function()
							{
								var date =$(".date").val();

								var args = {
									"action": "church_admin",
									"method": "individual_attendance",
									"nonce": "'.$nonce.'",
									"date": date,
									"meeting_type":"'.$meeting[0].'",
									"meeting_id":"'.$meeting[1].'"

								};
								console.log(args);
								// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
								$.getJSON(ajaxurl,args, function(data) {
									console.log(data);
									for(var count = 0; count < data.length; count++)
        					{
        						var item=data[count];
										console.log(item);
										$("#"+item).prop("checked","checked");
									}
								});
							});
						});</script>';
				}
				else
				{	
					$out.='<p>'.esc_html( __('No people are registered for that choice','church-admin' ) ).'</p>';
				}
			}
			
		}


		$out.='</div>';

		return $out;
}


/**
 *
 * Individual attendance csv download
 *
 * @author  	Andy Moyle
 * @param    	null
 * @return   	html
 * @version  	1.2450
 * @date 		2017-01-03
 */

function church_admin_individual_attendance_csv()
{
	global $wpdb,$wp_locale;
	$out='<h3>'.esc_html( __('Individual Attendance CSV download','church-admin' ) ).'</h3>';

		$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
		if(!empty( $services) )
		{
			$option='';
			foreach( $services AS $service)
			{
				$value = ($service->service_day<=6) ? sprintf(__('%1$s on %2$s at %3$s','church-admin'),$service->service_name,$wp_locale->get_weekday( $service->service_day),$service->service_time) : $service->service_name;
				$option.='<option value="service-'.(int)$service->service_id.'">'.esc_html($value).'</option>';
			}
		}
		$smallgroups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
		if(!empty( $smallgroups) )
		{
			foreach( $smallgroups AS $smallgroup)$option.='<option class="smallgroup" value="smallgroup-'.intval( $smallgroup->id).'">Small Group - '.esc_html( $smallgroup->group_name).'</option>';
		}

		$out.='<form action="" method="POST">';
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Which Meeting','church-admin' ) ).'</label><select class="church-admin-form-control" name="meeting">'.$option.'</select></div>';
		$member_type=church_admin_member_types_array();
		$first=$option='';
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Member type','church-admin' ) ).'</label></div>';
		foreach( $member_type AS $id=>$type)
		{
			$out.='<div class="church-admin-checkbox"><input type="checkbox"  name="member_type_id[]" value="'.$id.'" /> <label>'.$type.'</label></div>';
		}
	
		$date=date('Y-m-d');
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Start Date','church-admin' ) ).':</label>'.church_admin_date_picker( $date,'start_date',FALSE,'1970',date('Y',time()+60*60*24*365*10),'start_date','start_date').'</div>';
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('End Date','church-admin' ) ).':</label>'.church_admin_date_picker( $date,'end_date',FALSE,'1970',date('Y',time()+60*60*24*365*10),'end_date','end_date').'</div>';
		$out.='<div class="church-admin-form-group"><input type="hidden" name="ind_att_csv" value="yes" /><input type="submit" class="button-primary" value="'.esc_html( __('Choose','church-admin' ) ).'" /></div>';
		$out.='</form>';


	return $out;
}

function church_admin_output_ind_att_csv()
{
	global $wpdb;
	$debug=TRUE;
	church_admin_debug('Processing csv');
	church_admin_debug($_POST);
		$meeting=explode("-",$_POST['meeting'] );
		$out='';

		$start_date = !empty($_POST['start_date'])?sanitize_text_field(stripslashes($_POST['start_date'])):null;
		if(empty($start_date) || !church_admin_checkdate($start_date)){exit();}
		$end_date = !empty($_POST['end_date'])?sanitize_text_field(stripslashes($_POST['end_date'])):null;
		if(empty($end_date) || !church_admin_checkdate($end_date)){exit();}


		$sql='SELECT `date` FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE `date`>="'.esc_sql( $start_date  ).'" AND meeting_type="'.esc_sql( $meeting[0] ).'" ORDER BY `date` ASC LIMIT 1';
		church_admin_debug('Start date query'.$sql);
		$startdate=$wpdb->get_var( $sql);
		church_admin_debug('Start date '.$startdate);
		$sql='SELECT `date` FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE `date`<="'.esc_sql( $end_date).'" AND meeting_type="'.esc_sql( $meeting[0] ).'" ORDER BY `date` DESC LIMIT 1';
		$enddate=$wpdb->get_var( $sql);
		church_admin_debug('End date sql '.$sql);
		$sql='SELECT `date` FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE meeting_type="'.esc_sql( $meeting[0] ).'" AND `date`>="'.esc_sql( $startdate).'" AND `date`<="'.esc_sql( $enddate).'" GROUP BY `date`';
		$dates=$wpdb->get_results( $sql);
		church_admin_debug('Dates sql '.$sql);
		church_admin_debug(print_r( $dates,TRUE) );

		if ( empty( $startdate)||empty( $enddate)||empty( $dates) )  {
			church_admin_debug('No dates');
			$out.='<p>No dates found</p>';
		}
		else
		{

			switch( $meeting[0] )
			{
				case 'smallgroup':
					$sql='SELECT a.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="smallgroup" AND b.ID="'.(int)$meeting[1].'"';
					$meeting_type=__('Small Group','church-admin');
					$which=$wpdb->get_var('SELECT group_name FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$meeting[1].'"');
				break;
				default:
				case 'service':
					$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_services b WHERE a.site_id=b.site_id AND b.service_id="'.(int)$meeting[1].'"';
					$meeting_type=__('Service','church-admin');
					$which=$wpdb->get_var('SELECT service_name FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$meeting[1].'"');
				break;
			}
			//add in member types
			$membSQL='';
			$membsql=array();
			$member_types= !empty( $_GET['member_type_id'] ) ?church_admin_sanitize( $_GET['member_type_id'] ):array();
				if(!empty($member_types) ){
					foreach( $member_types AS $key=>$value)  {
						if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id="'.(int)$value.'"';
					}
				}
			if(!empty( $membsql) ) {$membSQL=' AND ('.implode(' || ',$membsql).')';}
			//get relevant people
			$query=$sql.$membSQL.' ORDER BY last_name, first_name';
			$people=$wpdb->get_results( $query);
			church_admin_debug($wpdb->last_query);
			if(!empty( $people) )
			{
				$csvheader=array('"Name"','"Address"','"Cell"','"Phone"');
				foreach( $dates AS $date)$csvheader[]='"'.mysql2date(get_option('date_format'),$date->date).'"';
				$csv=implode(',',$csvheader)."\r\n";;
				foreach( $people AS $person)
				{
					$name=array_filter(array( $person->first_name,$person->middle_name,$person->prefix,$person->last_name) );
					$household=$wpdb->get_row('SELECT address,phone FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$person->household_id.'"');
					$csvline=array('"'.implode(" ",$name).'"');
					if(!empty( $household->address) )  {$csvline[]='"'.esc_html( $household->address).'"';}else{$csvline[]='" "';}
					if(!empty( $person->mobile) )  {$csvline[]='"'.esc_html( $person->mobile).'"';}else{$csvline[]='" "';}
					if(!empty( $household->phone) )  {$csvline[]='"'.esc_html( $household->phone).'"';}else{$csvline[]='" "';}
					foreach( $dates AS $date)
					{
						$attendance=$wpdb->get_var('SELECT attendance_id FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE meeting_type="'.esc_sql( $meeting[0] ).'" AND meeting_id="'.esc_sql( $meeting[1] ).'" AND `date`="'.esc_sql( $date->date).'" AND people_id="'.(int)$person->people_id.'"');
						if(!empty( $attendance) )  {$csvline[]='"x"';}else{$csvline[]='" "';}
					}
					$csv.=implode(',',$csvline)."\r\n";

				}
				church_admin_debug( $csv);
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="attendance-'.$meeting[0].'.csv"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Disposition: attachment; filename="attendance-'.$meeting[0].'.csv"');
				echo $csv;
				return __('Done','church-admin');
			}else{$out=__('Nothing found','church-admin');}
}
return $out;
}
?>
