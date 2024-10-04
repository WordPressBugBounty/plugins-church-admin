<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function  church_admin_classes()
{
    global $wpdb;

	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-class&section=people','edit-class').'">'.esc_html( __('Add a class','church-admin' ) ).'</a></p>';

	$classes=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_classes ORDER BY end_date DESC');
	if(!empty( $classes) )
	{
		$theader='<tr>
		<th class="column-primary">'.esc_html( __('Class Name','church-admin' ) ).'</th>
		<th>'.esc_html( __('Edit','church-admin' ) ).'</th>
		<th>'.esc_html( __('Delete','church-admin' ) ).'</th>
		
		<th>'.esc_html( __('Next Start Date','church-admin' ) ).'</th>
		<th>'.esc_html( __('Repeat','church-admin' ) ).'</th>
		<th>'.esc_html( __('End Date','church-admin' ) ).'</th>
		<th>'.esc_html( __('Enrolled','church-admin' ) ).'</th>
		<th>'.esc_html( __('Shortcode','church-admin' ) ).'</th>
		</tr>';
		echo'<table class="widefat striped wp-list-table">
		<thead>'.$theader.'</thead><tbody>';
		$rowNumber=1;
		$current=FALSE;
		foreach( $classes AS $row)
		{
			if( $rowNumber==1 && $row->end_date>date('Y-m-d') )echo'<tr><th scope="row" colspan=8><strong>'.esc_html( __('Current and future classes','church-admin' ) ).'</strong></th></tr>';
            if ( empty( $current) && $row->end_date<=date('Y-m-d') )
			{
				echo'<tr><th scope="row" colspan=8><strong>'.esc_html( __('Past classes','church-admin' ) ).'</strong></th></tr>';
				$current=TRUE;
			}
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=classes&amp;action=edit-class&amp;id='.intval( $row->class_id),'edit-class').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=classes&amp;action=delete-class&amp;id='.intval( $row->class_id),'delete-class').'" onclick="return confirm(\''.esc_html( __('Deleting the class, deletes bookings and attendance data too','church-admin' ) ).'\');">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            echo'<tr>';
			echo'<td class="column-primary" data-colname="'.esc_html( __('Class Name','church-admin' ) ).'"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=classes&amp;action=view-class&amp;id='.(int)$row->class_id,'view-class').'">'.esc_html( $row->name).'</a><button type="button" class="toggle-row">
			<span class="screen-reader-text">show details</span>
		</button></td><td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td><td>'.$delete.'</td>';
			echo'<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->next_start_date).'</td>';
			switch( $row->recurring)
			{
				case's':$recurring=__('Once','church-admin');break;
				case'1':$recurring=__('Daily','church-admin');break;
				case'7':$recurring=__('Weekly','church-admin');break;
                case'14':$recurring=__('Fortnightly','church-admin');break;
				case'n':$recurring=__('Nth Day','church-admin');break;
				case'm':$recurring=__('Monthly','church-admin');break;
				case'a':$recurring=__('Annually','church-admin');break;

			}
			echo'<td data-colname="'.esc_html( __('Recurring','church-admin' ) ).'">'.esc_html( $recurring).'</td>';
            echo'<td>'.mysql2date(get_option('date_format'),$row->end_date).'</td>';
            $enrolled=0;
		      $enrolled=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="class" AND ID="'.(int)$row->class_id.'"');
            echo'<td data-colname="'.esc_html( __('Enrolled','church-admin' ) ).'">'.(int)$enrolled.'</td>';
            
            echo'<td data-colname="'.esc_html( __('Shortcode','church-admin' ) ).'">[church_admin type="class" class_id="'.(int)$row->class_id.'"]</td>';
          
			echo'</tr>';
			$rowNumber++;
        }

		echo'</tbody><tfoot>'.$theader.'</tfoot></table>';
	}
}
function church_admin_delete_class( $class_id=NULL)
{
	global $wpdb;
	$wpdb->show_errors();
	//check event_id
	$event_id=$wpdb->get_var('SELECT event_id FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"');
	if(!empty( $event_id) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $event_id).'"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"');
	//delete people booked in
	if(!empty( $class_id) )
	{
		church_admin_delete_people_meta( $class_id,NULL,'class');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE meeting_id="'.(int)$class_id.'" AND meeting_type="class"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_attendance WHERE  mtg_type="class" AND service_id="'.(int)$class_id.'"');
	}
	echo'<div class="notice notice-success inline"><p>'.esc_html( __('Class, bookings and attendance deleted','church-admin' ) ).'</p></div>';
	church_admin_classes();
}

function church_admin_edit_class( $class_id=NULL)
{
	global $wpdb;

	if(defined('CA_DEBUG') )$wpdb->show_errors();
	echo'<h2>'.esc_html( __('Edit Class','church-admin' ) ).'</h2>';
	if(!empty( $class_id) )
	{
		$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"');

	}
	if(!empty( $_POST['save'] ) )
	{
		if(defined('CA_DEBUG') )church_admin_debug(print_r( $_POST,TRUE) );
		$sql=array();
		foreach( $_POST AS $key=>$value) $sql[$key]=esc_sql(church_Admin_sanitize( $value) );
		//handle leaders
		$leaders=array();
		if(!empty( $_POST['leadership'] ) )
		{

			$leaders=church_admin_get_people_id(sanitize_text_field( stripslashes( $_POST['leadership'] ) ) );
		}else{$leaders=serialize(array() );}

		if ( empty( $class_id) )$class_id=$wpdb->get_var('SELECT class_id FROM '.$wpdb->prefix.'church_admin_classes WHERE name="'.$sql['name'].'" AND description="'.$sql['description'].'" AND next_start_date="'.$sql['next_start_date'].'" AND recurring="'.$sql['recurring'].'" AND how_many="'.$sql['how_many'].'" AND start_time="'.$sql['start_time'].'" AND end_time="'.$sql['end_time'].'"');
		if ( empty( $class_id) )
		{
			$query='INSERT INTO '.$wpdb->prefix.'church_admin_classes (name,description,next_start_date,recurring,how_many,start_time,end_time, leadership,cat_id)VALUES("'.$sql['name'].'","'.$sql['description'].'","'.$sql['next_start_date'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['start_time'].'","'.$sql['end_time'].'","'.esc_sql( $leaders).'","'.$sql['cat_id'].'")';
			if(defined('CA_DEBUG') )church_admin_debug("INSERT\r\n".$query);
			$wpdb->query( $query);
			$class_id=$wpdb->insert_id;
			if(defined('CA_DEBUG') )church_admin_debug('class_id: '.$class_id);
		}
		else{
			$query='UPDATE '.$wpdb->prefix.'church_admin_classes SET name="'.$sql['name'].'" , description="'.$sql['description'].'" , next_start_date="'.$sql['next_start_date'].'" , recurring="'.$sql['recurring'].'" , how_many="'.$sql['how_many'].'" , start_time="'.$sql['start_time'].'", end_time="'.$sql['end_time'].'"  ,leadership ="'.esc_sql( $leaders).'" , cat_id="'.$sql['cat_id'].'" WHERE class_id="'.(int)$class_id.'"';
			if(defined('CA_DEBUG') )church_admin_debug("UPDATE\r\n".$query);
			$wpdb->query( $query);
		}
			$query='';


			if ( empty( $sql['how_many'] ) )  {$how_many=1;}else{$how_many=(int)$sql['how_many'];}

			if(!empty( $data->event_id) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $data->event_id).'"');
			$event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date')+1;

			switch( $_POST['recurring'] )
			{
				case's':
					$query='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,recurring,cat_id,event_id,how_many,start_date,start_time,end_time)VALUES("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['cat_id'].'","'.$event_id.'","'.$sql['how_many'].'","'.$sql['next_start_date'].'","'.$sql['start_time'].'","'.$sql['end_time'].'")';
					$date=$sql['next_start_date'];
				break;
				case'n':
					//handle nth
					require_once(plugin_dir_path(__FILE__).'/calendar.php');
					$values=array();
					for ( $x=0; $x<$how_many; $x++)
					{
						$date=church_admin_nth_day( $sql['nth'],$sql['day'],date('Y-m-d',strtotime( $sql['next_start_date']." +$x month") ));
               			$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$date.'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}

					$query='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id) VALUES '.implode(",",$values);
				break;
				case '14':
					$values=array();
					for ( $x=0; $x<$how_many; $x++)
					{
						$date=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x fortnight") );
						$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$date.'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$query='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case '1':
					$values=array();

					for ( $x=0; $x<$how_many; $x++)
					{
						$date=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x day") );
							$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$date.'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}

					$query='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case '7':
					$values=array();

					for ( $x=0; $x<$how_many; $x++)
					{
						$date=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x week") );
							$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$date.'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}

					$query='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case 'm':
					$values=array();
					for ( $x=0; $x<how_many; $x++)
					{
						$date=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x month") );
						$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$date.'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$query='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case 'a':
					$values=array();
					for ( $x=1; $x<$how_many; $x++)
					{
						$date=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x year") );
						$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$date.'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$query='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;

			}

			$wpdb->query( $query);
			if(defined('CA_DEBUG') )church_admin_debug("CALENDAR query\r\n".$query);
			$cal_id=$wpdb->insert_id;
			if(!empty( $date) )
      {
          $end_date=$date;//value of last saved date
		      $query='UPDATE '.$wpdb->prefix.'church_admin_classes SET event_id="'.esc_sql( $event_id).'",end_date="'.$end_date.'" WHERE class_id="'.(int)$class_id.'"';
			      $wpdb->query( $query);
      }
      if(defined('CA_DEBUG') )church_admin_debug("UPDATE the class with event id and last date\r\n".$query);

			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$class_id.'" AND meta_type="class"');
			if(!empty( $_POST['delegate'] ) )
			{
				$people=maybe_unserialize(church_admin_get_people_id(sanitize_text_field( stripslashes($_POST['delegate'] ) )) );
				if(!empty( $people) )foreach( $people AS $key=>$people_id)
				{
					church_admin_update_people_meta((int)$class_id,$people_id,'class');
				}
			}
		echo'<div class="notice notice-success inline"><p>'.esc_html( __('Class updated','church-admin' ) ).'</p></div>';
		church_admin_classes();
	}
	else
	{
		echo'<form action="" method="POST"><table class="form-table">';
		echo'<tr><th scope="row">'.esc_html( __('Class Name','church-admin' ) ).'</th><td><input type="text" required="required" name="name" ';
		if(!empty( $data->name) ) echo 'value="'.esc_html( $data->name).'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Class Description','church-admin' ) ).'</th><td><textarea name="description">';
		if(!empty( $data->name) ) echo esc_html( $data->description);
		echo'</textarea></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Led by','church-admin' ) ).'</th><td>';
		$current=array();
		if(!empty( $data->leadership) )$current=maybe_unserialize( $data->leadership);
		echo church_admin_autocomplete('leadership','friends','to',$current,FALSE);
		echo'</td><tr>';

		if(!empty( $data->next_start_date) )  {$current=$data->next_start_date;}else{$current=NULL;}
		echo'<tr><th scope="row">'.esc_html( __('Start Date','church-admin' ) ).' (yyyy-mm-dd)</th><td>'.church_admin_date_picker( $current,'next_start_date',FALSE,date('Y'),date('Y')+10,'start_date','start_date').'</td></tr>';

		echo '<tr><th scope="row">'.esc_html( __('Recurring','church-admin' ) ).'</th><td><select name="recurring" ';
		echo ' id="recurring" onchange="OnChange(\'recurring\')">';
		if(!empty( $data->recurring) )
		{
			$option=array('s'=>esc_html( __('Once','church-admin' ) ),
					'1'=>esc_html( __('Daily','church-admin' ) ),
					'7'=>esc_html( __('Weekly','church-admin' ) ),
					'n'=>esc_html( __('nth day eg.1st Friday','church-admin' ) )
					,'m'=>esc_html( __('Monthly','church-admin' ) ),
					'a'=>esc_html( __('Annually','church-admin') )
				);
			echo'<option value="'.$data->recurring.'" selected="selected">'.$option[$data->recurring].'</option>';
		}
		echo'<option value="s">'.esc_html( __('Once','church-admin' ) ).'</option><option value="1">'.esc_html( __('Daily','church-admin' ) ).'</option><option value="7">'.esc_html( __('Weekly','church-admin' ) ).'</option><option value="14">'.esc_html( __('Fortnightly','church-admin' ) ).'</option><option value="n">'.esc_html( __('nth day (eg 1st Friday)','church-admin' ) ).'</option><option value="m">'.esc_html( __('Monthly on same date','church-admin' ) ).'</option><option value="a">'.esc_html( __('Annually','church-admin' ) ).'</option></select></td></tr>';
		echo'<tr id="nth" ';
		if ( empty( $data->recurring) || $data->recurring !='n')echo 'style="display:none"';
		echo'><th scope="row">'.esc_html( __('Recurring on','church-admin' ) ).'</th><td><select name="nth">';
		if(!empty( $data->recurring) ) echo'<option value="'.esc_html( $data->recurring).'">'.esc_html( $data->recurring).'</option>';
			echo'<option value="1">'.esc_html( __('1st','church-admin' ) ).'</option><option value="2">'.esc_html( __('2nd','church-admin' ) ).'</option><option value="3">'.esc_html( __('3rd','church-admin' ) ).'</option><option value="4">'.esc_html( __('4th','church-admin' ) ).'</option></select>&nbsp;<select name="day"><option value="0">'.esc_html( __('Sunday','church-admin' ) ).'</option><option value="1">'.esc_html( __('Monday','church-admin' ) ).'</option><option value="2">'.esc_html( __('Tuesday','church-admin' ) ).'</option><option value="3">'.esc_html( __('Wednesday','church-admin' ) ).'</option><option value="4">'.esc_html( __('Thursday','church-admin' ) ).'</option><option value="5">'.esc_html( __('Friday','church-admin' ) ).'</option><option value="6">'.esc_html( __('Saturday','church-admin' ) ).'</option></select></td></tr><script type="text/javascript">

function OnChange()  {
if(document.getElementById(\'recurring\').value==\'s\')  {
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'none\';
		}
if(document.getElementById(\'recurring\').value==\'1\')  {
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
if(document.getElementById(\'recurring\').value==\'7\')  {
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';;
		}
if(document.getElementById(\'recurring\').value==\'14\')  {
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';;
		}
if(document.getElementById(\'recurring\').value==\'n\')  {
		document.getElementById(\'nth\').style.display = \'table-row\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
if(document.getElementById(\'recurring\').value==\'m\')  {
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
if(document.getElementById(\'recurring\').value==\'a\')  {
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
}
</script>';
		echo'<tr id="howmany" ';

		echo '><th scope="row">'.esc_html( __('How many times in all?','church-admin' ) ).'</th><td><input type="text"  required="required" name="how_many" ';
		if(isset( $data->how_many) )  {echo' value="'.intval( $data->how_many).'"';}else {echo' value="1" ';}
		echo'/></td></tr>';

		echo'<tr><th scope="row"> '.esc_html( __('Category','church-admin' ) ).'</th><td><select name="cat_id" ';
		echo' >';
		$select='';
		$first='<option value="">'.esc_html( __('Please select','church-admin' ) ).'...</option>';
		$sql="SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category";
		$result3=$wpdb->get_results( $sql);
		foreach( $result3 AS $row)
		{
			if(!empty( $data->cat_id)&&$data->cat_id==$row->cat_id)
			{
				$first='<option value="'.$row->cat_id.'" style="background:'.esc_attr($row->bgcolor).';color:'.esc_attr($row->text_color).'" selected="selected">'.$row->category.'</option>';
			}
			else
			{
			$select.='<option value="'.$row->cat_id.'" style="background:'.esc_attr($row->bgcolor).';color:'.esc_attr($row->text_color).'">'.$row->category.'</option>';
			}
		}
		echo $first.$select;//have original value first!
		echo'</select></td></tr>';
		if(!empty( $data->start_time) )$data->start_time=substr( $data->start_time,0,5);//remove seconds
		if(!empty( $data->end_time) )$data->end_time=substr( $data->end_time,0,5);//remove seconds
		echo '<tr><th scope="row">'.esc_html( __('Start Time of form HH:MM','church-admin' ) ).'</th><td><input type="text"  required="required" name="start_time" ';
		if(!empty( $data->start_time) )echo' value="'.$data->start_time.'"';
		echo'/></td></tr>';
		echo '<tr><th scope="row">'.esc_html( __('End Time of form HH:MM','church-admin' ) ).'</th><td><input type="text"  required="required" name="end_time" ';
		if(!empty( $data->end_time) )echo' value="'.$data->end_time.'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Add some people','church-admin' ) ).'</th><td>';
		$current=array();
		if(!empty( $data->class_id) )
		{
			$people_result=church_admin_people_meta( $data->class_id,NULL,'class');
			foreach( $people_result AS $data)
			{
				$name=array_filter(array( $data->first_name,$data->prefix,$data->last_name) );
				$current[]=implode(' ',$name);
			}
		}
		echo church_admin_autocomplete('delegate','friends1','to1',$current,FALSE);
		echo'</td><tr>';
        /*
        echo'<tr><th scope="row">'.esc_html( __('Booking message','church-admin' ) ).'</th><td><textarea name="message" style="width:100%;height:250px">';
        if(!empty( $data->message) )echo $data->message;
        echo'</textarea></td></tr>';
		*/
        echo'<tr><td colspan="2"><input type="hidden" name="save" value="yes" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></form>';
		echo'</table>';

	}

}

function church_admin_view_class( $id)
{
	global $wpdb;
	//$wpdb->show_errors();
	if(!empty( $_POST['update-attendance'] )&&!empty( $_POST['attendance'] ) )
	{
		$attendance = church_admin_sanitize( $_POST['attendance']);
		foreach( $attendance AS $key=>$value)
		{
			$adult=$child=0;
			//update individual attendance table
			
			//validate data
			$haystack=sanitize_text_field( stripslashes( $value ) );
			//regexfor iso date/people_id
			$regex='/^\d{4}-([0]\d|1[0-2])-([0-2]\d|3[01])\/[0-9]*$/';
			$match=preg_match($regex,$haystack);
			if(!empty($match[0])){
				//yes
				$attData=explode($match[0]);
				
			}
			else {
				//no
				continue;
			}
			


			$updateData=array('meeting_id'=>(int)$_POST['class_id'] ,'meeting_type'=>"class",'date'=>$attData[0],'people_id'=>$attData[1] );
			$wpdb->replace( $wpdb->prefix.'church_admin_individual_attendance', $updateData);
			//check people type
			$sql='SELECT people_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int) $attData[1] .'"';
			$person_type=$wpdb->get_var( $sql);
			switch( $person_type)
			{
				case 1:$adult=1;break;
				case 2:$child=1;break;
				case 3:$child=1;break;
			}
			$currentData= new stdClass();
			$currentData->children=$currentData->adults=$currentData->attendance_id=0;
			//update attendance table
			$current=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE `date`="'.esc_sql( $attData[0] ).'" AND mtg_type="class"');
			if(!empty( $current) )$currentData=$current;
			$childData=$currentData->children + $child;
			$adultData=$currentData->adults +$adult;
			if(!empty( $currentData) )
			{

				$query='UPDATE '.$wpdb->prefix.'church_admin_attendance SET adults = "'.intval( $adultData).'", children="'.intval( $childData).'" WHERE attendance_id="'.intval( $currentData->attendance_id).'"';
				$wpdb->query( $query);
			}
			else
			{
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_attendance (adults,children,`date`,mtg_type) VALUES ("'.intval( $adult).'","'.intval( $child).'","'.esc_sql( $attData[0] ).'","class")');
			}
		}
	}
	$classData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$id.'"');

	//class details
	echo'<h2>'.esc_html( $classData->name).'</h2>';
	if(!empty( $data->leadership) )
	{

		$leaders=church_admin_get_people( $data->leadership);
		echo'<p>'.esc_html( __('Led by','church-admin' ) ).$leaders.'</p>';
	}
	//show attendance
	$people_result=church_admin_people_meta( $id,NULL,'class');

	if(!empty( $people_result) )
	{
		$table='<form action="" method="POST"><input type="hidden" name="class_id" value="'.(int)$id.'" /><table class="widefat striped">';
		//get dates of class

		$dates=$wpdb->get_results('SELECT start_date FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.intval( $classData->event_id).'" ORDER BY start_date ASC');

		//table header
		$table.='<thead><tr><th>'.esc_html( __('Name','church-admin' ) ).'</th>';
		foreach( $dates AS $date)
		{
			$table.='<th class="att-date">'.mysql2date(get_option('date_format'),$date->start_date).'</th>';
		}
		$table.='</thead><tbody>';
		foreach( $people_result AS $person)
		{
            if(defined('CA_DEBUG') )church_admin_debug(print_r( $person,TRUE) );
			$name=array_filter(array( $person->first_name,$person->prefix,$person->last_name) );
			if ( empty( $person->active) )  {$active='class="ca-deactivated" ';}else{$active='';}
            $table.='<tr '.$active.'><td>'.esc_html(implode(" ",$name) ).'</td>';
			//get attendance
			foreach( $dates AS $date)
			{
				$tick='<input type="checkbox" name="attendance[]" value="'.esc_html( $date->start_date.'/'.$person->people_id).'" />';
				$sql='SELECT attendance_id FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE people_id="'.$person->people_id.'" AND meeting_type="class" AND meeting_id="'.(int)$id.'" AND `date`="'.esc_sql( $date->start_date).'"';
				;
				$attended=$wpdb->get_var( $sql);
				if( $attended)$tick=__('Yes','church-admin');
				$table.='<td>'.$tick.'</td>';
			}
			$table.'</tr>';
		}
		$table.='</table>';
		$table.='<p><input type="hidden" name="update-attendance" value="true" /><input type="submit" class="button-primary" value="'.esc_html( __('Update attendance','church-admin' ) ).'" /></form></p>';
	}
	if(!empty( $table) )echo $table;
	echo'<h3>'.esc_html( __('Attendance Graphs','church-admin' ) ).'</h3>';

      if(!empty( $_POST['type'] ) )
		{
			switch( $_POST['type'] )
			{
				case'weekly':$graphtype='weekly';break;
				case'rolling':$graphtype='rolling';break;
				default:$graphtype='weekly';break;
			}
		}else{$graphtype='weekly';}
		if(!empty( $_POST['start'] ) )  {
			$start=sanitize_text_field(stripslashes($_POST['start']));
		}else{
			$start=date('Y-m-d',strtotime('-1 year') );
		}
		if(!church_admin_checkdate($start)){
			$start=date('Y-m-d',strtotime('-1 year') );
		}
		if(!empty( $_POST['end'] ) )  {
			$end= sanitize_text_field(stripslashes($_POST['end']));
		}else{
			$end=date('Y-m-d');
		}
		if(!church_admin_checkdate( $end )){
			$end=date('Y-m-d');
		}
		$service_id = !empty($_POST['service_id']) ? sanitize_text_field(stripslashes($_POST['service_id'])) :'C/1';
		
		require_once(CA_PATH.'display/graph.php');
		echo church_admin_graph( $graphtype,$service_id,$start,$end,900,500);

}
?>
