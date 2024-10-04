<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**************************************
* 
* Updated 2023-04-17
* Beefed up sanitization
* Meeting choice is validated to check 
* that it is expected input 
* G,S, or C / Integer
*
****************************************/



/*
 Adds attendance figures
church_admin_show_rolling_average()

church_admin_add_attendance()

*/
function church_admin_attendance_list($meeting=null)
{
    global $wpdb,$wp_locale;

	//initialise
	if ( empty( $meeting) )
	{
		$service_id=$wpdb->get_var('SELECT service_id FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_id ASC LIMIT 1');
		$meet='S/'.(int)$service_id;
	}

	echo'<h3>'.esc_html( __('Attendance List','church-admin' ) ).'</h3>';
	echo'<form action="'.esc_url(admin_url().'admin.php').'" method="GET">';
	echo'<input type="hidden" name="page" value="church_admin/index.php" /><input type="hidden" name="action" value="attendance" /><input type="hidden" name="service" value="attendance" />';
	
    $mtg_type=$service_id=NULL;
    if(empty($meeting) && !empty($_REQUEST['meeting']) )
    {
		//sanitize
		$meeting=sanitize_text_field( stripslashes( $_REQUEST['meeting'] ) );
	}
	if(!empty($meeting))
	{
		//validate for expected
		$regex='/[GCS]\/[0-9]*/';
		$match=preg_match($regex,$meeting);
        
		$mtg_type = $service_id = NULL;
		if(!empty($match))
		{

			$mtgDetails=explode("/",$meeting );
			switch( $mtgDetails['0'] )
			{
				default:
				case'S':
					$mtg_type='service';
				break;
				case 'G':
					$mtg_type='group';
				break;
				case 'C':
					$mtg_type='class';
				break;
			}
			$service_id=(int)$mtgDetails['1'];
		}
		else
		{
			echo'<p>'.esc_html( __('Meeting not recognised, defaulting to first service, or please choose one...','church-admin')).'</p>';
		}
		//recreate meeting variable for later
        $what=esc_html($mtg_type.'/'.$service_id);
    }
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('Meeting','church-admin' ) ).'</label>'.church_admin_att_mtg_chooser( $mtg_type,$service_id);
	echo'</div><p><input class="button-primary" type="submit" value="'.esc_html( __('Choose','church-admin' ) ).'" /></p></form>';
    if(!empty( $mtg_type)&& !empty( $service_id) )
    {
        //Button for already chosen meeting
        echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-attendance&amp;section=Attendance&amp;meeting='.esc_html( $mtg_type.'/'.$service_id),'edit-attendance').'">'.esc_html( __('Save attendance','church-admin' ) ).'</a></p>';
    }

	if(empty($service_id) && empty($mtg_type))
	{

		//empty so lets initialise with a service 
		$service_id=$wpdb->get_var('SELECT service_id FROM '.$wpdb->prefix.'church_admin_attendance WHERE mtg_type="service" ORDER BY `date` DESC LIMIT 1');
		
		church_admin_debug( $wpdb->last_query);
		church_admin_debug('SERVICE '.$service_id);
		$mtg_type='service';
		$what="s/".$service_id;
	}

	$query = 'SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE mtg_type="'.esc_sql($mtg_type) .'" AND service_id="'.(int) $service_id.'" ORDER BY `date` DESC ';
	
	$results=$wpdb->get_results( $query);
	$items=$wpdb->num_rows;

	
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.class.php');
	if( $items > 0)
	{

		$p = new caPagination;
		$p->items( $items);
		$page_limit=get_option('church_admin_pagination_limit');
		if ( empty( $page_limit) )  {$page_limit=20;update_option('church_admin_pagination_limit',20);}
		$p->limit( (int)$page_limit); // Limit entries per page

		$p->target(wp_nonce_url("admin.php?page=church_admin/index.php&action=attendance&section=attendance&amp;meeting=".esc_attr($what),'attendance'));
		$current_page = !empty($_GET['page']) ? (int)$_GET['page']:1;
              
	  $p->currentPage( $current_page); // Gets and validates the current page
		$p->calculate(); // Calculates what to show
		$p->parameterName('paging');
		$p->adjacents(1); //No. of page away from the current page
		if(!isset( $_GET['paging'] ) )
		{
			$p->page = 1;
		}
		else
		{
			$p->page = intval( $_GET['paging'] );
		}
			//Query for limit paging
		$limit = esc_sql("LIMIT " . ( $p->page - 1) * $p->limit  . ", " . $p->limit);

		$sql=$query.$limit;
		$results=$wpdb->get_results( $sql);
		if(!empty( $results) )
		{
			
			// Pagination
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo $p->show();
			echo '</div></div>';
			//Pagination
			$theader='<tr>
				<th class="column-primary">'.esc_html( __('Date', 'church-admin' ) ).'</th>
				<th>'.esc_html( __('Edit','church-admin' ) ).'</th>
				<th>'.esc_html( __('Delete','church-admin' ) ).'</th>
				<th>'.esc_html( __('Adults','church-admin' ) ).'</th>
				<th>'.esc_html( __('Children','church-admin' ) ).'</th>
				<th>'.esc_html( __('Rolling adults','church-admin' ) ).'</th>
				<th>'.esc_html( __('Rolling children','church-admin' ) ).'</th>
			</tr>';
			$table='<table class="wp-list-table striped widefat"><thead>'.$theader.'</thead><tfoot>'.$theader.'</tfoot><tbody>';
			foreach( $results AS $row)
			{
				$edit = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-attendance&amp;section=Attendance&amp;attendance_id='.(int)$row->attendance_id,'edit-attendance').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
				$delete = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-attendance&amp;section=Attendance&amp;attendance_id='.(int)$row->attendance_id,'delete-attendance').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
				$date=mysql2date(get_option('date_format'),$row->date);

				$table.='<tr>
				<td data-colname="'.esc_html( __('Date','church-admin' ) ).'" class="column-primary">'.$date.'<button type="button" class="toggle-row">
                <span class="screen-reader-text">show details</span>
            </button></td> 
				<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'" >'.$edit.'</td>
				<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'" >'.$delete.'</td>
				<td data-colname="'.esc_html( __('Adults','church-admin' ) ).'" >'.(int)$row->adults.'</td>
				<td data-colname="'.esc_html( __('Children','church-admin' ) ).'" >'.(int)$row->children.'</td>
				<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'" >'.(int)$row->rolling_adults.'</td>
				<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'" >'.(int)$row->rolling_children.'</td>
				</tr>';

			}
			$table.='</tbody></table>';
			echo $table;
		}
	}
	
}



/**
 *
 * Save attendance
 *
 * @author  Andy Moyle
 * @param    $attendance_id
 * @return   html string
 * @version  0.1
 *
 * refactored 11th April 2016 to remove multi-service bug
 * refactored 9th January 2017 to allow attanednce for services, classes and groups to be recorded
 *
 */
function church_admin_edit_attendance( $attendance_id)
{
	global $wpdb,$wp_locale;
	if( !church_admin_level_check( 'Attendance') ){ return;}

	//validate attendance_id if present
	if(!empty($attendance_id) && !church_admin_int_check($attendance_id)) {
		echo '<div class="notice notice-danger"><h2>'.	esc_html( __('Invalid attendance id','church-admin' ) ).'</h2></div>';
		return;
	}
	
	//initial values

	$mtg_type='service';
	$mtg_id=$wpdb->get_var('SELECT MIN(service_id) FROM '.$wpdb->prefix.'church_admin_services LIMIT 1');
	
	//sanitize meeting
	if(!empty($_REQUEST['meeting']))
	{
			$meeting=sanitize_text_field( stripslashes( $_REQUEST['meeting'] ) );
			
			//validate for expected
			$regex='/[GCS]\/[0-9]*/';
			$match=preg_match($regex,$meeting);
			
			
			if(!empty($match))
			{

				$mtgDetails=explode("/",$meeting );
				switch( $mtgDetails['0'] )
				{
					default:
					case'S':
						$mtg_type='service';
					break;
					case 'G':
						$mtg_type='group';
					break;
					case 'C':
						$mtg_type='class';
					break;
				}
				$mtg_id=(int)$mtgDetails['1'];
			}
			//recreate meeting variable for later
			$what=esc_html($mtg_type.'/'.$mtg_id);
	}


  	
 
  	//check services, classes or groups setup
  	$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
  	$groups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
	$classes=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_classes');
	if ( empty( $services) && empty( $classes) && empty( $groups) )
	{
		echo '<p>'.esc_html( __('Please set up a service, group or class first','church-admin'));
	}
	else
	{//safe to proceed

 		if(!empty( $attendance_id) )$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE attendance_id="'.(int)$attendance_id.'"');

		if(isset( $_POST['edit_att'] ) &&!empty($_POST['add_date']))
		{
  			
  				$form=array();


     			$date=date('Y-m-d',strtotime( sanitize_text_field( $_POST['add_date'] ) ));
     			//print_r( $sql);
     			if ( empty( $attendance_id) )$attendance_id=NULL;
     			$data=array(
     				'attendance_id'=>$attendance_id,
     				'mtg_type'=>$mtg_type,
     				'service_id'=>(int)$mtg_id,
     				'adults'=>(int)$_POST['adults'],
     				'children'=>(int)$_POST['children'],
     				'date'=>$date
     			);
     			$wpdb->replace($wpdb->prefix.'church_admin_attendance',$data,'%s');
     			if ( empty( $attendance_id) )$attendance_id=$wpdb->insert_id;
     			church_admin_refresh_rolling_average();
     			//work out rolling average from values!
				/*
     			$avesql='SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM '.$wpdb->prefix.'church_admin_attendance WHERE `mtg_type`="'.esc_sql( $mtg_type).'" AND `service_id`="'.esc_sql( $service_id).'" AND `date` >= DATE_SUB("'.esc_sql(date('Y-m-d',strtotime( $_POST['add_date'] ) )).'",INTERVAL 52 WEEK) AND `date`<= "'.esc_sql(date('Y-m-d',strtotime( $_POST['add_date'] ) )).'"';
    			$averow=$wpdb->get_row( $avesql);

     			//update table with rolling average
         		$up='UPDATE '.$wpdb->prefix.'church_admin_attendance SET rolling_adults="'.$averow->rolling_adults.'", rolling_children="'.$averow->rolling_children.'" WHERE attendance_id="'.esc_sql( $attendance_id).'"';
	 			$wpdb->query( $up);
				*/

     			echo '<div id="message" class="notice notice-success inline">';
     			echo '<p><strong>'.esc_html( __('Attendance added','church-admin' ) ).'.</strong></p>';
     			echo '</div>';
	 		
	 		
    		church_admin_attendance_list($what);

		}//end process
		else
		{//form
			
			
			switch( $mtg_type )
			{
				case'service':$meeting=$wpdb->get_var('SELECT service_name FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$mtg_id.'"');break;
				case 'group':$meeting=$wpdb->get_var('SELECT group_name FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$mtg_id.'"');break;
				case 'class':$meeting=$wpdb->get_var('SELECT class_name FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$mtg_id.'"');break;
				default: 
					$meeting=null;
				break;
			}
			echo'<h2>'.esc_html( sprintf(__('Attendance for %1$s','church-admin' ) ,$meeting) ).'</h2>';
		
			echo '<form action="" method="post" name="add_attendance" id="add_attendance">';

			echo'<table class="form-table">';
			if(!empty( $_REQUEST['service_id'] ) )
            {
                echo'<input type="hidden" name="service_id" value="'.esc_attr(urldecode( (int)$_REQUEST['service_id'] )).'" />';
            }
            else{echo'<tr><th scope="row">'.esc_html( __('Meeting','church-admin' ) ).'</th><td>'.church_admin_att_mtg_chooser( $mtg_type, $mtg_id).'</td></tr>';}
			//datepicker js
			if(!empty( $data->date) )  {$date=$data->date;}else{$date=NULL;}
			echo '<tr><th scope="row">'.esc_html( __('Date','church-admin' ) ).' :</th><td>'.church_admin_date_picker( $date,'add_date',FALSE,'2011',date('Y',time()+60*60*24*365*10),'add_date','add_date').'</td></tr>';

			echo '<tr><th scope="row">'.esc_html( __('Adults','church-admin' ) ).'</th><td><input type="text" name="adults"  ';
			if(!empty( $data->adults) ) echo' value="'.esc_html( $data->adults).'" ';
			echo '/></td></tr>';
			echo '<tr><th scope="row"><label >'.esc_html( __('Children','church-admin' ) ).'</th><td><input type="text" name="children" ';
			if(!empty( $data->children) ) echo' value="'.esc_html( $data->children).'" ';
			echo'/><input type="hidden" name="edit_att" value="y" /></td></tr>';
			echo '<tr><td cellspacing=2><input class="button-primary" type="submit" value="'.esc_html( __('Save attendance for that date','church-admin' ) ).' &raquo;" /></td></tr></table></form>';;

		}//end of attendance form
	}//end safe to proceed
}//end function


function church_admin_att_mtg_chooser( $mtg_type=NULL,$meeting_id=NULL)
{
	global $wpdb;
	$selected = FALSE;
	if(!empty($mtg_type) && !empty($meeting_id)){
		switch($mtg_type)
		{
			case'service':
				$selected = 'S/'.(int)$meeting_id;
			break;
			case'group':
				$selected = 'G/'.(int)$meeting_id;
			break;
			case'class':
				$selected = 'G/'.(int)$meeting_id;
			break;
		}
	}


	//which meeting
			//service

			//services first
			$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE active = 1 ORDER BY service_name ASC');
			if(!empty( $services) )
			{
				$option='<optgroup label="'.esc_attr(__('Active Services','church-admin')).'">';
				foreach( $services AS $service)
				{
					$serviceDetail = $service->service_name.' '. $service->service_time;
     				$value = 'S/'.(int) $service->service_id;
					$option.='<option value="'.esc_attr($value).'" '.selected($selected,$value,FALSE).'>'.esc_html($serviceDetail).'</option>';
     				
				}
				$option.='</optgroup>';
			}
			//inactive services
			$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE active = 0 ORDER BY service_name ASC');
			if(!empty( $services) )
			{
				$option.='<optgroup label="'.esc_attr(__('Inactive Services','church-admin')).'">';
				foreach( $services AS $service)
				{
					$serviceDetail = $service->service_name.' '. $service->service_time;
     				$value = 'S/'.(int) $service->service_id;
					$option.='<option value="'.esc_attr($value).'" '.selected($selected,$value,FALSE).'>'.esc_html($serviceDetail).'</option>';
     				
				}
				$option.='</optgroup>';
			}
			//groups
			$groups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
			if(!empty( $groups) )
			{
				$option.='<optgroup label="'.esc_attr(__('Smallgroups','church-admin')).'">';
				foreach( $groups AS $group)
				{
                    $value = 'G/'.(int) $group->id;
	  				$option.='<option value="'.esc_attr($value).'" '.selected($selected,$value,FALSE).'>'.esc_html( $group->group_name).'</option>';
     				

				}
				$option.='</optgroup>';
			}
			//classes
			$classes=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_classes');
			if(!empty( $classes) )
			{
				$option.='<optgroup label="'.esc_attr(__('Classes','church-admin')).'">';
				foreach( $classes AS $class)
				{
					
					$value = 'C/'.(int) $class->class_id;
	  				$option.='<option value="'.esc_attr( $value).'" '.selected($selected,$value,FALSE).'>'.esc_html( $class->name).'</option>';
     			
				}
				$option.='</optgroup>';
			}

    		return '<select name="meeting">'.$option.'</select>';
}

function church_admin_save_attendance( $attendance_id,$date,$mtg_type,$mtg_id,$adults,$children)
{
	global $wpdb;
	//validate
	if ( empty( $date) || church_admin_checkdate($date)){
		$date=wp_date('y-m-d');
	}
	if(empty($adults)||!church_admin_int_check($adults)){$adults = 0;}
	if(empty($children)||!church_admin_int_check($children)){$children = 0;}
	if(empty($mtg_id)||!church_admin_int_check($mtg_id)){return FALSE;}
	if(empty($mtg_type)){$mtg_type = 'service';}
	//attendance_id is optional so only reject if present and not a digit
	if(!empty($attendance_id)&&!church_admin_int_check($attendance_id)){return FALSE;}

	$data=array(
     				'attendance_id'=>(int)$attendance_id,
     				'date'=>$date,
     				'mtg_type'=>$mtg_type,
     				'service_id'=>(int)$mtg_id,
     				'adults'=>(int)$adults,
     				'children'=>(int)$children
     			);
     			$wpdb->replace($wpdb->prefix.'church_admin_attendance',$data,'%s');
}
function church_admin_delete_attendance( $attendance_id)
{
     global $wpdb;
	//validate attendance_id if present
	if(!empty($attendance_id) && !church_admin_int_check($attendance_id)) {
		echo '<div class="notice notice-danger"><h2>'.	esc_html( __('Invalid attendance id','church-admin' ) ).'</h2></div>';
		return;
	}
     //so attendance table displays right list
     $mtg=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE attendance_id="'.(int) $attendance_id.'"');
     switch( $mtg->mtg_type)
     {
     	case'service':$mtg_type='S';break;
     	case'group':$mtg_type='G';break;
     	case'class':$mtg_type='C';break;

     }
     $what=esc_html($mtg_type.'/'.$mtg->service_id);//so attendance table displays right list

	 $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_attendance WHERE attendance_id="'.esc_sql( $attendance_id).'"');
     echo'<div class="notice notice-success inline"><p>'.esc_html( __('Attendance record deleted','church-admin' ) ).'</p></div>';


     church_admin_attendance_list($what);
}

function church_admin_attendance_metrics( $service_id=1)
{
     global $wpdb,$wp_locale;
	 $people_types=get_option('church_admin_people_type');
     $thead='';
     if ( empty( $service_id) )$service_id=1;
     $service=$wpdb->get_var('SELECT CONCAT_WS(" ",service_name,service_time) AS service FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
     $first_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.$wpdb->prefix.'church_admin_attendance WHERE service_id="'.(int)$service_id.'" ORDER BY `date` ASC LIMIT 1');
     $last_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.$wpdb->prefix.'church_admin_attendance WHERE service_id="'.(int)$service_id.'" ORDER BY `date` DESC LIMIT 1');

     for ( $year=$first_year; $year<=$last_year; $year++)  {$thead.='<th>'.esc_html($year).'</th>';}

     $aggtable=$totaltable=$adulttable=$childtable='<table class="widefat striped"><thead><tr><th>'.esc_html( __('Month','church-admin' ) ).'</th>'.$thead.'</tr></thead><tfoot><tr><th>Month</th>'.$thead.'<tr></tfoot><tbody>';

	  $results=$wpdb->get_results('SELECT ROUND( AVG( adults ) ) AS adults, ROUND( AVG( children ) ) AS children, YEAR( `date` ) AS year, MONTH( `date` ) AS month FROM '.$wpdb->prefix.'church_admin_attendance WHERE service_id="'.(int)$service_id.'" GROUP BY YEAR( `date` ) , MONTH( `date` )');

	if( $results)
	{	  foreach( $results AS $row)
		{

			$adults[$row->month][$row->year]=$row->adults;
			$children[$row->month][$row->year]=$row->children;
		}

		for ( $month=1; $month<=12; $month++)
		{
		$aggtable.='<tr><td>'.esc_html( $month).'</td>';
		$totaltable.='<tr><td>'.esc_html( $month).'</td>';
		$adulttable.='<tr><td>'.esc_html( $month).'</td>';
		$childtable.='<tr><td>'.esc_html( $month).'</td>';
		for ( $year=$first_year; $year<=$last_year; $year++)
		{
			if ( empty( $adults[$month][$year] ) )  {$adulttable.='<td>&nbsp;</td>';}else{$adulttable.='<td>'.esc_html( $adults[$month][$year] ).'</td>';}
			if ( empty( $children[$month][$year] ) )  {$childtable.='<td>&nbsp;</td>';}else{$childtable.='<td>'.esc_html( $children[$month][$year] ).'</td>';}
			if(!empty( $adults[$month][$year] ) )$total=$adults[$month][$year]+$children[$month][$year];
			if(!empty( $adults[$month][$year] ) )if( $adults[$month][$year]+$children[$month][$year]>0)  {$totaltable.='<td>'.esc_html( $total).'</td>';}else{$totaltable.='<td>&nbsp;</td>';}
			if(!empty( $adults[$month][$year] )&&$adults[$month][$year]+$children[$month][$year]>0)
			{
				$aggtable.='<td><span class="adults">'.esc_html( $adults[$month][$year] ).'</span>, <span class="children">'.esc_html( $children[$month][$year] ).'</span> (<span class="total">'.esc_html( $total).')</span></td>';
			}
			else
			{
				$aggtable.='<td>&nbsp;</td>';
			}

		}
		$aggtable.='</tr>';
		$totaltable.='</tr>';
		$adulttable.='</tr>';
		$childtable.='</tr>';
		}
		$aggtable.='</tbody></table>';
		$totaltable.='</tbody></table>';
		$adulttable.='</tbody></table>';
		$childtable.='</tbody></table>';
	}
	else
	{
		$totaltable=$aggtable=$childtable=$adulttable='<p>'.esc_html( __('No attendance recorded yet','church-admin' ) ).'</p>';
	}

    echo'<h2>'.esc_html( __('Attendance Figures','church-admin' ) ).'</h2>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit-attendance','edit-attendance').'">'.esc_html(__('Add attendance','church-admin') ).'</a>';
    $services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');

    echo'<table>';
    foreach( $services AS $service)
    {
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE service_id="'.esc_sql( $service->service_id).'"';

		$check=$wpdb->get_row( $sql);
		if( $service->service_id==$service_id){
			$service_details=esc_html( sprintf( __('%1$s on %2$s at %3$s', 'church-admin' ) , $service->service_name,$wp_locale->get_weekday( $service->service_day),esc_html( $service->service_time) ) );
		}
		if( $check) echo'<tr><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=attendance-metrics&amp;service_id='.$service->service_id,'attendance-metrics').'">'.esc_html( sprintf(__('View attendance table for %1$s  %2$s','church-admin' ) , $service->service_name, $service->service_time ) ).'</a></td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=attendance-list&amp;service_id='.$service->service_id,'attendance-list').'">'.esc_html( sprintf(__('Edit week by week attendance for %1$s %2$s','church-admin' ) ,$service->service_name,$service->service_time) ).'</a></td></tr>';
    }
    echo'</table>';
    echo '<h2>'.esc_html( __('Attendance Adults,Children (Total)','church-admin' ) ).' '.esc_html($service_details).'</h2>'.esc_html( $aggtable);
    echo '<h2>'.esc_html( __('Total Attendance for','church-admin' ) ).' '.esc_html($service_details).'</h2>'.esc_html( $totaltable);
    echo '<h2>'.esc_html( __('Adults Attendance for','church-admin' ) ).' '.esc_html($service_details).'</h2>'.$adulttable;
    echo '<h2>'.esc_html( __('Children Attendance for','church-admin' ) ).' '.esc_html($service_details).'</h2>'.$childtable;

}

function church_admin_edit_this_weeks_attendance()
{
	global $wpdb,$wp_locale;
	$people_types=get_option('church_admin_people_type');
	$formData=array();
	if ( empty( $_POST['date'] ) )
	{
		$now=date('Y-m-d');
	}
	else
	{
		$now=date('Y-m-d',strtotime( sanitize_text_field(stripslashes($_POST['date'] ) )) );
	}


	/********************
	 * grab services
	 ******************/
	$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
	if(!empty( $services) )
	{
		foreach( $services AS $service)
		{
			$serviceName=esc_html( __('Service','church-admin' ) ).': '.$service->service_name.' '.$wp_locale->get_weekday( $service->service_day).' '.mysql2date(get_option('time_format'),$service->service_time);
			//grab scheduled occurrences from rota
			$scheduledServices=$wpdb->get_results('SELECT rota_date FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service->service_id.'" AND rota_date BETWEEN  DATE("'.$now.'" - INTERVAL 6 DAY) AND "'.$now.'" GROUP BY rota_date');
			
			if(!empty( $scheduledServices) )
			{
				foreach( $scheduledServices AS $scheduledService)
				{
					$formData[]=array('type'=>'service','id'=>$service->service_id,'label'=>$serviceName,'datetime'=>$scheduledService->rota_date);
				}
			}
		}
	}
	/********************
	 * grab classes
	 ******************/
	$classes=$wpdb->get_results( 'SELECT a.* FROM '.$wpdb->prefix.'church_admin_classes a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE a.event_id=b.event_id AND b.start_date BETWEEN  DATE('.$now.' - INTERVAL 7 DAY) AND "'.$now.'" ');
	if(!empty( $classes) )
	{
		foreach( $classes AS $class)
		{
			$formData[]=array('type'=>'class','id'=>$class->class_id,'label'=>esc_html( __('Class','church-admin' ) ).': '.$class->name,'datetime'=>$class->start_date);
		}
	}
	/********************
	 * grab groups
	 ******************/
	$groups= $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
	if(!empty( $groups) )
	{
		foreach( $groups AS $group)
		{
			//$date is the Monday of the week chosen which is a 1 in PHP date terms
			$sunday=strtotime( $now);
			$groupDay=date('Y-m-d',$sunday+( $group->group_day*24*60*60) );

			$formData[]=array('type'=>'group','id'=>$group->id,'label'=>esc_html( __('Group','church-admin' ) ).': '.$group->group_name,'datetime'=>$groupDay);
		}
	}

	/**********************
	 * Build Form
	 *********************/
	
	$out='<h2>'.esc_html( __('Add attendance','church-admin' ) ).'</h2>';
	$out.='<p><strong>'.esc_html( __('This form is built from service rotas, groups and classes','church-admin' ) ).'<strong></p>';
	$out.='<h2>'.esc_html( __('Set to week commencing Sunday...','church-admin') ).'</h2>';
	$out.='<form action="" method="POST"><p><select name="date">';
	$posted_date = !empty($_POST['date'])? sanitize_text_field(stripslashes($_POST['date'])):null;
	if(!empty( $posted_date ) && church_admin_checkdate($posted_date) ){
		$out.='<option value="'.esc_attr($posted_date ).'">'.mysql2date(get_option('date_format'),$posted_date ).'</option>';
	}
	for ( $x=0; $x<5; $x++)
	{
		
		$date=date('Y-m-d',strtotime('Sunday '.$x.' weeks ago') );
		$readableDate=mysql2date(get_option('date_format'),$date);
		$out.='<option value="'.esc_attr($date).'">'.esc_html($readableDate).'</option>';
	}
	$out.='</select><input type="submit" class="button-primary" value="'.esc_html( __('Change date','church-admin' ) ).'" /></p></form>';
	if(!empty( $formData) )
	{
		$out.='<div class="church-admin-attendance">';
		foreach( $formData AS $form)
		{
			$title=esc_html( $form['label'].' '.mysql2date(get_option('date_format'),$form['datetime'] ) );
			$out.='<h3>'.$title.'</h3><table class="form-table">';
			$readable=esc_html( sprintf(__('%1$s adults for %2$s','church-admin' ) ,'%1$s',$title) );
			$out.='<tr><th scope="row">'.esc_html( __("Adults",'church-admin' ) ).'</th><td><input class="formdata" type="number" data-id="'.(int)$form['id'].'" data-date="'.esc_html( $form['datetime'] ).'" data-type="'.esc_html( $form['type'] ).'" data-people-type="1" data-readable="'.esc_attr($readable).'" /></td></tr>';
			$readable=esc_html( sprintf(__('%1$s children for %2$s','church-admin' ) ,'%1$s',$title) );
			$out.='<tr><th scope="row">'.esc_html( __("Children",'church-admin' ) ).'</th><td><input class="formdata" type="number" data-id="'.(int)$form['id'].'" data-date="'.esc_html( $form['datetime'] ).'" data-type="'.esc_html( $form['type'] ).'" data-people-type="2" data-readable="'.esc_attr($readable).'" /></td></tr>';
			$out.='</table>';
		}
		$out.='<p><button class="save-attendance button-primary">'.esc_html( __('Save attendance','church-admin' ) ).'</button></p></div>';
	}
	$out.='<script>
	jQuery(document).ready(function( $)  {
		var attendanceData=[];
		$(".save-attendance").click(function(e)  {
				e.preventDefault();
				$(".formdata").each(function()  {
					var id=$(this).data("id");
					var type=$(this).data("type");
					var people_type=$(this).data("people-type");
					var att=$(this).val();
					var date=$(this).data("date");
					var readable=$(this).data("readable");
					var item=[id,type,people_type,att,date,readable];
					attendanceData.push(item);
				})
				var nonce="'.wp_create_nonce("week-attendance").'";
        		var args = {"action": "church_admin","method": "week-attendance","formdata": attendanceData,"nonce":nonce};
				console.log(args);
				$.ajax({
					url: ajaxurl,
					type: "post",
					data:  args,
					success: function(response) {
					  
						$(".church-admin-attendance").html(response);
						}
				  });


		});

	});
	
	
	</script>';
	echo $out;
}
