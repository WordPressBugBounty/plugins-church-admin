<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
2011-02-04 added calendar single and series delete; fixed slashes problem
2011-03-14 fixed errors not sowing as red since 0.32.4
2012-07-20 Update Internationalisation
2014-09-22 Simplify db and add image
2014-10-06 Added facilities bookings

*/


 /**
 *
 * Calendar Display
 *
 * @author  Andy Moyle
 * @param    $current,$facilities_id
 * @return
 * @version  0.1
 *
 *
 *
 */
 function church_admin_new_calendar( $start_date=NULL,$facilities_id=NULL,$cat_id=null) {
	if(!church_admin_level_check('Calendar') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin'  ),__('Calendar','church-admin') )).'</h2></div>';
		return;
	}

	church_admin_debug('********* church_admin_new_calendar *********');
	church_admin_debug(func_get_args());
	global $wpdb, $m, $monthnum, $year, $wp_locale;
	if(defined('CA_DEBUG') )$wpdb->show_errors();
	$initial=true;

	$display_categories = $display_facilities = array();
    $facilities=church_admin_calendar_facilities_array();
	$categories=church_admin_calendar_categories_array();
    if(!empty($cat_id))
    {
        $cat_id_array = explode(",",$cat_id);
        foreach($cat_id_array AS $key => $id){
           if(!empty($categories[$id])){
                $display_categories[]=$categories[$id];
           }
        }
    }
    if(!empty($facilities_id))
    {
        $fac_id_array = explode(",",$facilities_id);
        foreach($fac_id_array AS $key => $id){
           if(!empty($facilities[$id])){
                $display_facilities[]=$facilities[$id];
           }
        }
    }
 
    


    if(!empty($display_categories) || !empty($display_facilities)){
        echo '<p>';
        if(!empty($display_categories)){
            echo __('Categories: ','church-admin').implode(', ',$display_categories).'<br>';
        }
        if(!empty($display_facilities)){
            echo __('Facilities: ','church-admin').implode(', ',$display_facilities).'<br>';
        }
        echo'</p>';
    }


	echo '<p>'.esc_html(__("Italicised events don't appear on public facing calendars",'church-admin')).'</p>';


	//use 1st of this month if something wrong with given dates
	if(empty($start_date)){
		$start_date = !empty($_POST['start_date']) ? church_admin_sanitize($_POST['start_date']) : wp_date('Y-m-01');

	}
	if(!church_admin_datecheck($start_date)){$start_date =wp_date('Y-m-01');}
	$date_parts = explode("-",$start_date);
	$start_date = $date_parts[0].'-'.$date_parts[1].'-01';
	$prev=explode('-',date('Y-m-d',strtotime( $start_date.' -1 month') ));
	$next=explode('-',date('Y-m-d',strtotime( $start_date.' +1 month') ));



	// week_begins = 0 stands for Sunday
	$week_begins = (int) get_option( 'start_of_week' );
	$ts = strtotime( $start_date );
	$thisyear = gmdate( 'Y', $ts );
	$thismonth = gmdate( 'm', $ts );
	$thisday = gmdate( 'd', $ts );
	$unixmonth = mktime( 0, 0 , 0, $thismonth, 1, $thisyear );
	$last_day = date( 't', $unixmonth );


    if(!empty( $facilities_id) )  {$type='facility-bookings';}else{$type='calendar';}
	/* translators: Calendar caption: 1: month name, 2: 4-digit year */
	$calendar_caption = _x('%1$s %2$s', 'calendar caption');
	$calendar_output = '<table  class="church_admin_calendar"><thead><tr>';
	$calendar_output .= "\n\t\t".'<th colspan="2" id="prev">'.ca_date_link( $prev[0],$prev[1],'01',__('< Previous','church-admin' ) ,NULL,$type,$facilities_id).'</th>';
	$calendar_output .= "\n\t\t".'<th colspan="3" >' . sprintf(
		$calendar_caption,
		$wp_locale->get_month( $thismonth ),
		date( 'Y', $unixmonth )
	) . '</th>';
	$calendar_output .= "\n\t\t".'<th colspan="2" id="next">'.ca_date_link( $next[0],$next[1],'01',__('Next >','church-admin' ) ,NULL,$type,$facilities_id).'</th>';
	$calendar_output .= '</tr></thead><tbody>';

	$myweek = array();

	for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
		$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
	}

	foreach ( $myweek as $wd ) {
		//$day_name = $initial ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
		$wd = esc_attr( $wd );
		$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\" class=\"dayheader\">$wd</th>";
	}

	$calendar_output .= '
	</tr>
	</thead>
	<tbody>
	<tr class="cal">';

	$daywithevent = array();

	// Get days with events
	$dayswithevents = $wpdb->get_results('SELECT DISTINCT DAYOFMONTH(start_date) FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE start_date >= "'.esc_sql($thisyear.'-'.$thismonth.'-01 00:00:00').'" AND start_date <= "'.esc_sql($thisyear.'-'.$thismonth.'-'.$last_day.' 23:59:59').'"', ARRAY_N);
	if ( $dayswithevents ) {
		foreach ( (array) $dayswithevents as $daywith ) {
			$daywithevent[] = $daywith[0];
		}
	}

	// See how much we should pad in the beginning
	$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
	if ( 0 != $pad ) {
		$calendar_output .= "\n\t\t".'<td colspan="'. esc_attr( $pad ) .'" class="pad">&nbsp;</td>';
	}

	$newrow = false;
	$daysinmonth = (int) date( 't', $unixmonth );

	for ( $day = 1; $day <= $daysinmonth; ++$day ) {
		if ( isset( $newrow) && $newrow ) {
			$calendar_output .= "\n\t</tr>\n\t<tr class=\"cal\">\n\t\t";
		}
		$newrow = false;
		$class=array('ca-day');
		if ( $day == gmdate( 'j', $ts ) &&
			$thismonth == gmdate( 'm', $ts ) &&
			$thisyear == gmdate( 'Y', $ts ) ) {
			$class[]= "ca-chosen";

		} elseif(
		 	$day == gmdate( 'j', time() ) &&
			$thismonth == gmdate( 'm',  time() ) &&
			$thisyear == gmdate( 'Y',  time() ) )  {
			$class[]="ca-today";

		}
		$day_output='';
		if ( in_array( $day, $daywithevent ) )
		{

			$class[]="ca-event";
			$this_day=esc_sql( $thisyear.'-'.$thismonth.'-'.$day);
			//process categories
			$catsql=array();
			if ( empty( $cat_id) )  {$cat_sql="";}
			else
			{
				
				$cats=explode(',',$cat_id);
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
			 if ( !empty( $facilities_id) ){
				 church_admin_debug('Month render with facilities_id '.$facilities_id);
				 $facs=explode(",",$facilities_id);
				 foreach( $facs AS $key=>$value)  {
					 if(church_admin_int_check( trim( $value)) )  {
						 $facsql[]='c.meta_value='.(int)$value;
						 $display_facilities[]=$facilities[(int)$value];
					 }
				 }
				 $FACSQL = !empty($facsql) ? ' AND ('.implode(' OR ',$facsql).') ':'';
				 $sql='SELECT a.*,b.*,c.* FROM '.$wpdb->prefix.'church_admin_calendar_date a  , '.$wpdb->prefix.'church_admin_calendar_category b , '.$wpdb->prefix.'church_admin_calendar_meta c WHERE a.cat_id=b.cat_id '.$cat_sql.' AND a.event_id=c.event_id AND c.meta_type="facility_id" AND a.start_date="'.$this_day.'"  '.$FACSQL.'  ORDER BY a.start_time';
				 
				 
			 }
			 else
			 {
				 //no facilities ID query
				 //$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON a.cat_id=b.cat_id  WHERE  a.start_date="'.esc_sql( $date).'"  '.$cat_sql.' ORDER BY a.start_date,a.start_time';
				 //church_admin_debug('Month render no fac_ids');
				 $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b  WHERE a.cat_id=b.cat_id '.$cat_sql.' AND a.start_date="'.$this_day.'" ORDER BY a.start_time';
		 
			 }
		
			$events=$wpdb->get_results( $sql);
			foreach( $events AS $event)
        	{
				$border=church_admin_adjust_brightness( $event->bgcolor, -50);
				$text=church_admin_adjust_brightness( $event->bgcolor, -100);
				if( $event->start_time=='00:00:00' && $event->end_time=='23:59:00')
    			{//all day
    				$day_output .=   '<div class="cal-item-detail"  id="item'.(int)$event->date_id.'"style="background-color:'.$event->bgcolor.';border-left:3px solid '.$border.';padding:5px;color:'.$text.'" >';
					if(empty($event->general_calendar)){$day_output .='<em>';}
					$day_output .= esc_html( __('All Day','church-admin' ) ).' '.esc_html( $event->title);
					if(empty($event->general_calendar)){$day_output .='</em>';}
					$day_output .= '<br/>';
					
    			}
    			else
    			{
					$day_output .=  '<div class="cal-item-detail" id="item'.(int)$event->date_id.'"style="background-color:'.$event->bgcolor.';border-left:3px solid '.$border.';padding:5px;color:'.$text.'" ><p>';
					if(empty($event->general_calendar)){$day_output .= '<em>';}
					$day_output .=mysql2date(get_option('time_format'),$event->start_time).' '.esc_html( $event->title).'... ';
					if(empty($event->general_calendar)){$day_output .= '<em>';}
					$day_output .= '</p>';

				}
				$day_output.='<select class="church-admin-calendar-dropdown"><option>'.esc_html(__('Actions','church-admin')).'</option>';
				$day_output.='<option data-what="'.esc_attr(__('Edit this single event','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=single-event-edit&section=calendar&amp;event_id='.(int)$event->event_id.'&amp;date_id='.(int)$event->date_id,'single-event-edit').'">'.esc_html('Single event edit','church-admin').'</option>';
				
				if( $event->recurring!='s'){
					
					$day_output.='<option data-what="'.esc_attr(__('Edit whole series','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=whole-series-edit&section=calendar&amp;event_id='.$event->event_id.'&amp;date_id='.(int)$event->date_id,'whole-series-edit').'">'.esc_html('Edit whole series','church-admin').'</option>';
					$day_output.='<option data-what="'.esc_attr(__('Edit rest of series','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=future-series-edit&section=calendar&amp;event_id='.$event->event_id.'&amp;date_id='.(int)$event->date_id,'future-series-edit').'">'.esc_html('Edit rest of series','church-admin').'</option>';
				
				}
				$day_output.='<option data-what="'.esc_attr(__('Delete single event','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=single-event-delete&section=calendar&amp;event_id='.(int)$event->event_id.'&amp;date_id='.(int)$event->date_id,'single-event-delete').'">'.esc_html('Single event delete','church-admin').'</option>';
				if( $event->recurring!='s'){
					$day_output.='<option data-what="'.esc_attr(__('Delete this and future events in series','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=future-event-delete&amp;event_id='.(int)$event->event_id.'&amp;date_id='.(int)$event->date_id,'future-event-delete').'">'.esc_html('Delete this and future series','church-admin').'</option>';
					$day_output.='<option data-what="'.esc_attr(__('Delete whole series','church-admin')).'"  value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=series-event-delete&amp;event_id='.(int)$event->event_id.'&amp;date_id='.(int)$event->date_id,'series-event-delete').'">'.esc_html('Delete whole series','church-admin').'</option>';

				}
				$day_output.='</select>';
				
				$day_output .='</div>';
            }
		}else{$class[]="ca-no-event";}
		$calendar_output .= '<td';
		if(!empty( $class) ) $calendar_output.=' class="'.implode(" ",$class).'"';
		$calendar_output.=' id="'.$thisyear.'-'.$thismonth.'-'.$day.'"';
		$calendar_output.='>'.$day.'<br>';
		$calendar_output.= $day_output.'</td>';

		if ( 6 == calendar_week_mod( date( 'w', mktime(0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
			$newrow = true;
		}
	}

	$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins );
	if ( $pad != 0 && $pad != 7 ) {
		$calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr( $pad ) .'">&nbsp;</td>';
	}
	$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";
	
	
	$calendar_output .= '<script type="text/javascript">	
		jQuery(document).ready(function( $) {$(".church-admin-calendar-dropdown").on("change", function(event) {
			var action =$(this).find(":selected").val();
			var what = $(this).find(":selected").data("what");
			console.log(action);
			if(confirm(what + " '.__('Are you sure?','church-admin').' ")){
					window.open(action);
				}
			';
	$calendar_output.= '});});</script>';
	

	echo  $calendar_output ;
}




function church_admin_category_list()
{
    global $wpdb;
    //build category tableheader
        $thead='<tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th width="100">'.esc_html( __('Category','church-admin' ) ).'</th><th>'.esc_html( __('Shortcode','church-admin' ) ).'</th></tr>';
    $table= '<table class="widefat striped" ><thead>'.$thead.'</thead><tbody>';
        //grab categories
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_category');
    foreach( $results AS $row)
    {
        $edit_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-category&section=calendar&amp;id='.$row->cat_id,'edit-category').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
        $delete_url='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-category&section=calendar&amp;id='.$row->cat_id,'delete-category').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
        $shortcode='[church_admin type=calendar-list category='.$row->cat_id.' weeks=4]';
        $table.='<tr><td>'.$edit_url.'</td><td>'.$delete_url.'</td><td style="background:'.esc_attr($row->bgcolor).';color:'.esc_attr($row->text_color).'">'.esc_html( $row->category).'</td><td>'.$shortcode.'</td></tr>';
    }
    $table.='</tbody><tfoot>'.$thead.'</tfoot></table>';
    echo '<h2>'.esc_html( __('Calendar Categories','church-admin' ) ).'</h2><p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-category&section=calendar','edit-category').'">'.esc_html( __('Add a category','church-admin' ) ).'</a></p>'.$table;
}



function church_admin_delete_category( $id)
{
    global $wpdb;
	if(empty($id)||!church_admin_int_check($id)){
		echo '<div class="notice notice danger"><h2>'.esc_html( __('Invalid category id, not deleted','church-admin' ) ).'</h2></div>';
		return;
	}
    //count how many events have that category
    $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE cat_id="'.(int)$id.'"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_category WHERE cat_id="'.(int)$id.'"');
    //adjust events with deleted cat_id to 0
    $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_date SET cat_id="1" WHERE cat_id="'.(int)$id.'"');
    echo '<div id="message" class="notice notice-success inline">';
        echo '<p><strong>'.esc_html( __('Category Deleted','church-admin' ) ).'.<br>';
        if( $count==1) {
			echo esc_html( sprintf(__('Please note that %1$s event used that category and will need editing','church-admin' ) ,$count)).'.';
		}
        if( $count>1) {
			echo esc_html( sprintf(__('Please note that %1$s events used that category and will need editing','church-admin' ) ,$count)).'.';
		}
        echo'</strong></p>';
        echo '</div>';
        church_admin_category_list();


}

function church_admin_edit_category( $id)
{
    global $wpdb;
    if(!empty( $_POST) )
    {
		$color = "#FFF";
		$textColor= "#000";

		//sanitize
		$color = church_admin_sanitize($_POST['color']);
		
		$text_color = church_admin_light_or_dark($color);
		$category_name = church_admin_sanitize($_POST['category']);
		if(!empty( $id) )
        {
        	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_category SET category="'.esc_sql($category_name  ).'",bgcolor="'.esc_sql($color).'",textcolor="'.esc_sql($textColor).'" WHERE cat_id="'.(int)$id.'"');
        }
        else
        {
        	$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_category (category,bgcolor,textcolor) VALUES("'.esc_sql($color ).'","'.esc_sql($textColor).'")');        
		}
        echo '<div id="message" class="notice notice-success inline">';
        if( $id)  {
			echo '<p><strong>'.esc_html( __('Category Edited','church-admin' ) ).'</strong></p>';
		}else{
			echo '<p><strong>'.esc_html( __('Category Added','church-admin' ) ).'</strong></p>';
		}
        echo '</div>';
        church_admin_category_list();
    }
    else
    {
		if ( empty( $id) )  {
			$which=__("Add category",'church-admin');
		}
		else{
			$which=__('Edit','church-admin');
		}
		//grab current data
		$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_category WHERE cat_id="'.(int)$id.'"');
		if ( empty( $data) )$data=new stdClass();
	
		if ( empty( $data->bgcolor) )$data->bgcolor='#e4afb1';
		echo '<script type="text/javascript" >
		jQuery(document).ready(function( $) {
					
				$("#colorpicker").farbtastic("#color");
				
		});
		</script>';
    	echo'<h2>'.$which.'</h2><form action="" method="post"><div class="church-admin-form-group">';
   
 		echo'<label>'.esc_html( __('Category Name','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="category" ';
 		if(!empty( $data->category) ) echo 'value="'.esc_attr( $data->category).'"';
			echo'/></div>';
 		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Background Colour','church-admin' ) ).'</label><input  class="church-admin-form-control" type="text" ';
  		if(!empty( $data->bgcolor) ) echo' style="background:'.esc_attr( $data->bgcolor).';color:'.esc_attr($row->text_color).'" ';
  		echo' id="color" name="color" ';
  		if(!empty( $data->bgcolor) )echo' value="'.esc_attr( $data->bgcolor).'" ';
  		echo'/><div>';
		echo'<div id="colorpicker"></div>';
    	echo'<p><input type="submit" class="button-primary" name="edit_category" value="'.$which.'" /></p></form>';

    
    }
}


function church_admin_calendar()
{
    global $wpdb;
    echo'<h2>'.esc_html( __('Calendar List','church-admin' ) ).'</h2><p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=category-list','category-list').'">'.esc_html( __('Category List','church-admin' ) ).'</a></p>';
    church_admin_calendar_list();
   
}


 /**
 *
 * Edit an event
 *
 * @author  Andy Moyle
 * @param    $date_id,$event_id,$edit_type,$date,$facilities_id
 * @return
 * @version  0.1
 *
 *
 *
 */
function church_admin_event_edit( $date_id,$event_id,$edit_type,$date,$facilities_id)
{
	church_admin_debug('******* church_admin_event_edit ********');
	church_admin_debug(func_get_args());
	global $wpdb;
    $wpdb->show_errors();
	$edit=''.esc_html( __('Add','church-admin' ) ).'';
	if(!empty( $date_id) && church_admin_int_check($date_id) )
	{
		$data=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON a.cat_id=b.cat_id WHERE a.date_id="'.esc_sql( $date_id).'"');
		
		$edit=''.esc_html( __('Edit','church-admin' ) ).'';
	}
	$recurring = !empty($data->recurring) ? $data->recurring : '1';
	/********************************************
	 * Correct Title
	 *******************************************/
	$title=__('Add calendar event','church-admin');
	if(!empty($date_id))
	{
		switch($edit_type)
		{
			case 'single':
				$title = __('Edit single event','church-admin' );
			break;
			case 'whole-series':
				$title = __('Edit whole series','church-admin' );
			break;
			case 'future-series':
				$title = sprintf(__('Edit rest of series from %1$s'),mysql2date(get_option('date_format'),$data->start_date));
			break;
		}
	}
	if(!empty($facilities_id))
	{
		switch($edit_type)
		{
			case 'single':
				$title = __('Edit single facility booking','church-admin' );
			break;
			case 'series':
				$title = __('Edit whole series facility booking','church-admin' );
			break;
		}
	}
	echo '<h2>'.esc_html($title).'</h2>';

	/********************************************
	 * Process save
	 *******************************************/
	$start_date = !empty($_POST['start_date'])?sanitize_text_field(stripslashes($_POST['start_date'])):null;
	if(!empty( $start_date ) && church_admin_checkdate($start_date))
	{//process
        //if(defined('CA_DEBUG') )church_admin_debug("***************\r\nfunction church_admin_event_edit ".date('Y-m-d H:i:s') );
		//if(defined('CA_DEBUG') )church_admin_debug(print_r( $_POST,TRUE) );
		switch( $edit_type)
		{
			case'single':
			
				if(!empty( $date_id) )  {
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int) $date_id.'"');
					
					$recurring='s';
				}
				if(!empty($event_id)){
					/***********************************************************************************
					 * Make sure other events in this event_id note the exceptions for future editing 
					 ***********************************************************************************/
					$currExceptions = $wpdb->get_var('SELECT exceptions FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$event_id.'"');
					if(!empty($currExceptions)){
						$currExceptions=maybe_unserialize($currExceptions);
					}
					else{
						$currExceptions = array();
					}
					$currExceptions[]=$start_date;
					$exceptions = serialize($currExceptions);
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_date SET exceptions = "'.esc_sql($exceptions).'" WHERE event_id = "'.(int)$event_id.'"');
				}
			break;
			case'whole-series':
				if(!empty( $event_id) )  {

					$currData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $event_id).'" AND start_date>="'.esc_sql( $start_date ).'" LIMIT 1');
					$recurring = $currData->recurring;
					$exceptions = maybe_unserialize($currData->exceptions);
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $event_id).'"');
				
				}
			break;
			case 'future-series':
				$currData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $event_id).'" AND start_date>="'.esc_sql( $start_date ).'" LIMIT 1');
				$recurring = $currData->recurring;
				$exceptions = maybe_unserialize($currData->exceptions);
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $event_id).'" AND start_date>="'.esc_sql( $start_date ).'"');
			break;

		}
		
		//get next highest event_id
		$event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date')+1;
		$form=array();
		foreach( $_POST AS $key=>$value)$form[$key]=church_admin_sanitize( $value) ;
		//adjust data
		$form['start_time'].=':00';
		$form['end_time'].=':00';
		$event_image = (!empty($form['attachment_id']) && church_admin_int_check($form['attachment_id']) ) ? (int)$form['attachment_id'] : null;
		if(!empty( $form['all_day'] ) )  {$form['start_time']='00:00:00'; $form['end_time']='23:59:00';}
		if ( empty( $form['cat_id'] ) )  {$form['cat_id']=1;}
		if ( empty( $form['year_planner'] ) )  {$form['year_planner']=0;}else{$form['year_planner']=1;}
		if ( empty( $form['general_calendar'] ) )  {$form['general_calendar']=0;}else{$form['general_calendar']=1;}
		if ( empty( $form['end_date'] ) )  {$form['end_date']=$form['start_date'];}
        if(!empty( $form['how_many'] )&& $form['how_many']>365)$form['how_many']=365;
		//text link overrides dropdown menu
		$form['link']='';
		if(!empty( $form['link2'] ) )  {$form['link']=$form['link2'];}
		if(!empty( $form['link1'] ) )  {$form['link']=$form['link1'];}

		$facilities_id = !empty($form['facilities_id']) ? $form['facilities_id']:null;
		//only allow one submit!
		$checksql='SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql( $form['title'] ).'" AND description="'.esc_sql( $form['description'] ).'" AND location="'.esc_sql( $form['location'] ).'"  AND cat_id="'.esc_sql( $form['cat_id'] ).'" AND start_date="'.esc_sql( $form['start_date'] ).'" AND start_time="'.esc_sql( $form['start_time'] ).'" AND end_time="'.esc_sql( $form['end_time'] ).'" LIMIT 1';
		$eventType = 'calendar';
		$check=$wpdb->get_var( $checksql);
		if ( empty( $check)||!empty( $date_id) )
		{
			
			//if ( empty( $recurring) )$recurring=$_POST['recurring'];
			 
			
			switch( $form['recurring'] )
			{
				
				case 's':
					$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.esc_sql( $form['start_date'] ).'","'.esc_sql( $form['start_time'] ).'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","s","1","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';
				break;
				case '1':
					//daily
					for ( $x=0; $x<$form['how_many']; $x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x day") );
						$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.esc_sql( $start_date).'","'.esc_sql( $form['start_time'] ).'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","1","'.(int)$form['how_many'].'","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';
					}
				break;
				case '7':
					//weekly
					for ( $x=0; $x<$form['how_many']; $x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x week") );
						$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['start_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","7","'.(int)$form['how_many'].'","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';	
					}
				break;
				case '14':
					//fortnightly
					for ( $x=0; $x<$form['how_many']; $x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']} + $x fortnight") );
						$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['start_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","14","'.(int)$form['how_many'].'","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';	
					}
				break;
				case'm':
					//monthly
					for ( $x=0; $x<$form['how_many']; $x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x month") );
						$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['start_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","m","'.(int)$form['how_many'].'","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';	
					}

				break;
				case 'q':
					for ( $x=0; $x<$form['how_many']; $x++)
					{
						$y=$x*84;
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$y day") );
						$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['start_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","m","'.(int)$form['how_many'].'","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';	
					}

				break;
				case'a':
					//annually
					for ( $x=0; $x<$form['how_many']; $x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x year") );
						$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['start_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","a","'.(int)$form['how_many'].'","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';	
					}

				break;
				
				default:
					//nth day
					$type=substr( $form['recurring'],0,1);//whether l or r
					$nth=substr( $form['recurring'],1,1);
					
					$day=substr( $form['recurring'],2,1);
					//church_admin_debug($form['recurring']);
					//church_admin_debug("Nth day type = $type n = $nth and day = $day");
					$formdate=mysql2date('Y-m-01',$form['start_date']);//needs to be 1st of month to safely add one month each iteration
					for ( $x=0; $x<$form['how_many']; $x++)
					{
						
						$date=new DateTime( $formdate );
						$date->modify("+ $x month");
						if($type=='l'){
							$days=array(0=>'Sunday',1=>"Monday",2=>"Tuesday",3=>"Wednesday",4=>"Thursday",5=>"Friday",6=>"Saturday");
							$start_date=date('Y-m-d',strtotime("last $days[$day]",$date->format('U')));
						}
						else{
							$start_date=church_admin_nth_day( $nth,$day,$date->format('Y-m-d') );
						}
						//church_admin_debug("Start date $start_date");
						if(!empty($start_date)){
							$values[]='("'.esc_sql( $form['title'] ).'","'.esc_sql( $form['description'] ).'","'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['start_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","'.esc_sql( $form['recurring'] ).'","'.(int)$form['how_many'].'","'.(int)$form['general_calendar'].'","'.esc_sql( $eventType).'","'.esc_sql($form['link']).'","'.esc_sql($form['link_title']).'","'.$event_image.'")';
						}
					}
				break;
			}
			if( !empty( $values ) ){
				$sql='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,cat_id,event_id,recurring,how_many,general_calendar,event_type,link,link_title,event_image) VALUES '.implode(",",$values);
				//church_admin_debug( $sql);
				if( $wpdb->query( $sql) )
				{
					echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Date(s) saved','church-admin' ) ).'</strong></p></div>';
				}
				else
				{
					echo  '<div class="notice notice-success inline"><h2>ERROR</h2>'.esc_html($wpdb->last_query).'</div>';
				}
				
				//handle facilities_ids
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_meta WHERE meta_type="facility_id" AND event_id="'.(int)$event_id.'"');
				if(!empty($form['facilities_id'])){
					foreach($form['facilities_id'] AS $key=>$facility_id){
						$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_meta (event_id,meta_type,meta_value) VALUES("'.(int)$event_id.'","facility_id","'.(int)$facility_id.'")');

					}
				}
			}
			else
			{
				echo  '<div class="notice notice-success inline"><h2>ERROR</h2>'.esc_html( __('An issue occurred with your chosen dates, please try again','church-admin' ) ).'</div>';
			}
			
			
			
		}
		else{echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Date(s) already saved','church-admin' ) ).'</strong></p></div>';}
		$facids=null;
		if(!empty($facilities_id) && is_array($facilities_id)){$facids = implode(',',$facilities_id);}
		if(!empty($facilities_id) && is_string($facilities_id)){$facids = $facilities_id;}
		
		church_admin_new_calendar( $form['start_date'] ,$facids);
	}//end process
	else
	{
		if ( empty( $error) )$error =new stdClass();
		if ( empty( $data) ) $data = new stdClass();
		/********************************************
		 * Form
		 *******************************************/
		if(!empty($edit_type)){
			switch($edit_type){
				case 'single': 
					$data->recurring='s';
				break;
			}
		}

		echo'<form action=""  id="calendar" method="post">';
	
		
		
		echo church_admin_calendar_form( $data,$error,$recurring,$date,$edit_type);
		echo '<p><input type="submit" class="button-primary" name="edit_event" value="'.esc_html(__('Save Event','church-admin')).'" /></p></form>';



		}

}

function church_admin_calendar_form( $data,$error,$recurring,$date,$edit_type)
{
	church_admin_debug('***** church_admin_calendar_form ******');
	church_admin_debug(func_get_args());
	global $wpdb;
	if ( empty( $data) ) $data=new stdClass();
    $out='';
	
	/*****************************************
	*
	* Event Title	
	*
	******************************************/
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Event Title','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="title" ';
		if(!empty( $data->title) )$out.=' value="'.esc_html( $data->title).'" ';
		if(!empty( $error->title) )$out.=esc_html($error->title);
	$out.=' /></div>';
	/*****************************************
	*
	* Event Photo	
	*
	******************************************/
	$out.='<h3>'.esc_html( __( 'Photo','church-admin') ).'</h3><div class="church-admin-form-group"><label>';
		if(!empty( $data->event_image) )
		{
			$out.=wp_get_attachment_image( $data->event_image,'medium','', array('class'=>"current-photo",'id'=>"calendar-frontend-image") );

		}
		else
		{
			$out.= '<img src="'.esc_url(plugins_url('/images/default-avatar.jpg',dirname(__FILE__) )).'" width="300" height="200" id="calendar-frontend-image"  alt="'.esc_html( __( 'Photo of Person','church-admin') ).'"  />';
		}
        $out.='</label>';
		if(is_admin() )
		{//on admin page so use media library
			$out.='<button id="household-image"  class=" button-secondary calendar-upload-button button" >'.esc_html( __( 'Upload Image','church-admin') ).'</button>';

		}else
		{//on front end so use boring update
			$out.='<input type="file" id="file-chooser" class="file-chooser" name="logo" style="display:none;" /><input type="button" id="calendar_image" class="calendar-frontend-button" value="'.esc_html( __( 'Upload Photo','church-admin') ).'" />';
    	}
	    $out.='<input type="hidden" name="attachment_id" class="attachment_id" id="calendar_attachment_id" ';
    	if(!empty( $data->event_image) )$out.=' value="'.(int)$data->event_image.'" ';
    	$out.='/><span id="calendar-upload-message"></span>';
    	$out.='</div>';
	$out.='<script>var mediaUploader;
		jQuery(document).ready(function($){
	$(".calendar-upload-button").click(function(e) {
	  e.preventDefault();
	  var id="#calendar_attachment_id";
	  console.log(id);
	  // If the uploader object has already been created, reopen the dialog
		if (mediaUploader) {
		mediaUploader.open();
		return;
	  }
	  // Extend the wp.media object
	  mediaUploader = wp.media.frames.file_frame = wp.media({
		title: "Choose Image",
		button: {
		text: "Choose Image"
	  }, multiple: false });
  
	  // When a file is selected, grab the URL and set it as the text fields value
	  mediaUploader.on("select", function() {
		var attachment = mediaUploader.state().get("selection").first().toJSON();
		console.log(attachment);
		$(id).val(attachment.id);
		if(attachment.sizes.medium.url)
		{
		  $("#calendar-frontend-image").attr("src",attachment.sizes.medium.url);
		}
		else{$("#calendar-frontend-image").attr("src",attachment.sizes.thumbnail.url);}
		$("#calendar-frontend-image").attr("srcset",null);
	  });
	  // Open the uploader dialog
	  mediaUploader.open();
	});
	});
  </script>';
	/*
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Photo','church-admin' ) ).'</label><input type="file" id="photo" name="uploadfiles" size="35" class="uploadfiles" /></div>';
	
	if(!empty( $data->event_image) )
		{//photo available
			$out.= '<div class="church-admin-form-group"><label>'.esc_html( __('Current Photo','church-admin' ) ).'</label>';
			$out.= wp_get_attachment_image( $data->event_image,'ca-people-thumb' );
			
		}//photo available
		else
		{
			$out.= '<div class="church-admin-form-group"><label>'.esc_html( __('Default','church-admin' ) ).'</label>';
			$out.= '<img src="'.plugins_url('images/default-avatar.jpg',dirname(__FILE__) ) .'" width="75" height="75" />';
			
		}
	$out.='</div>';
		*/
	/*****************************************
	*
	* Event Description		
	*
	******************************************/
	
	$out.='<div class="church-admin-form-group"><label>'.esc_html(__('Event Description','church-admin')).'</label><textarea class="church-admin-form-control" rows="5" cols="50" name="description" ';
	if(!empty( $error->description) )$out.=esc_html($error->description);
	$out.='>';
	if(!empty( $data->description) )$out.=wp_kses_post( $data->description);
	$out.='</textarea></label>';
	/*****************************************
	*
	* Event Location		
	*
	******************************************/
	$out.='<div class="church-admin-form-group"><label>'.esc_html(__('Event Location','church-admin')).'</label><textarea class="church-admin-form-control" rows="5" cols="50" name="location" ';
	if(!empty( $error->location) )$out.=$error->location;
	$out.='>';
	if(!empty( $data->location) )$out.=sanitize_text_field( $data->location);
	$out.='</textarea></div>';
	/*****************************************
	*
	* Facilities		
	*
	******************************************/
	$facilities = church_admin_facilities_array();

	if(!empty( $facilities) )
	{
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Facility/Room','church-admin' ) ).'</label></div>';
		foreach( $facilities AS $fac_id=>$facility)  {
			if(!empty($data->event_id)){
				$check = $wpdb->get_var('SELECT meta_value FROM '.$wpdb->prefix.'church_admin_calendar_meta WHERE event_id="'.$data->event_id.'" AND meta_type="facility_id" AND meta_value="'.(int)$fac_id.'"');
			}
			$out.='<div class="church-admin-checkbox"><input type="checkbox" value="'.(int)$fac_id.'" ';
			if(!empty($check)){$out.=' checked="checked" ';}
			$out.=' name="facilities_id[]"><label>'.esc_html($facility).'</label></div>';
		}

	}
	
	/*****************************************
	*
	* Category		
	*
	******************************************/	
	$out.='<div class="church-admin-form-group"><label> '.esc_html(__('Category','church-admin')).'</label><select required="required" class="church-admin-form-control" name="cat_id" ';
	if(!empty( $error->category) ) $out.=$error->category;
	$out.=' >';
	$select='';
	$first='<option value="">'.esc_html(__('Please select','church-admin')).'...</option>';
	$sql="SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category";
	$result3=$wpdb->get_results( $sql);
	foreach( $result3 AS $row)
	{
    	if(!empty( $data->cat_id)&&$data->cat_id==$row->cat_id)
    	{
			$first='<option value="'.(int)$data->cat_id.'" style="background-color:'.esc_html( $row->bgcolor).';color:'.esc_attr($row->text_color).'" selected="selected">'.esc_html( $data->category).'</option>';
    	}
    	else
    	{
        	$select.='<option value="'.(int)$row->cat_id.'" style="background-color:'.esc_html( $row->bgcolor).';color:'.esc_attr($row->text_color).'">'.esc_html( $row->category).'</option>';
    	}
	}
	$out.=$first.$select;//have original value first!
	$out.='</select></div>';
	/*****************************************
	*
	* Start Date		
	*
	******************************************/		
		/* if editing the whole series, get start date of whole series */
	if(!empty($edit_type)){
		switch($edit_type){
			case 'whole-series':
				$start_date = $wpdb->get_var('SELECT start_date FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.intval( $data->event_id).'" ORDER BY start_date ASC LIMIT 1');
				$recurring = $data->recurring;
			break;
			case 'future-series':
				$recurring = $data->recurring;
			break;
			case 'single':
				$recurring = 's';
			break;
		}
	}
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Start Date','church-admin' ) ).'</label>';
	if(!empty( $data->start_date) )  {$start_date=$data->start_date;}
	elseif(!empty( $date) )  {$start_date=$date;}
	else{$start_date=NULL;}
	$endDate=date('Y-m-d',strtotime("+20 years") );
	$out.=church_admin_date_picker( $start_date,'start_date',FALSE,$start_date,$endDate,'start_date','start_date');
	$out.='</div>';
	/*****************************************
	*
	* Recurring	
	*
	******************************************/	
	if( $recurring=='single')$recurring='s';
	$out .= '<div class="church-admin-form-group"><label>'.esc_html( __('Recurring','church-admin' ) ).'</label>';
	$out .= '<select id="recurring" name="recurring" class="church-admin-form-control">';
	$out .= '<option value="s" '.selected( $recurring,"s",FALSE).'>'.esc_html( __('Single','church-admin' ) ).'</option>';
	$out .= '<option value="1" '.selected( $recurring,1,FALSE).'>'.esc_html( __('Daily','church-admin' ) ).'</option>';
	$out .= '<option value="7" '.selected( $recurring,7,FALSE).'>'.esc_html( __('Weekly','church-admin' ) ).'</option>';
	$out .= '<option value="14" '.selected( $recurring,14,FALSE).'>'.esc_html( __('Fortnightly','church-admin' ) ).'</option>';
	$out .= '<option value="m" '.selected( $recurring,"m",FALSE).'>'.esc_html( __('Monthly on same date','church-admin' ) ).'</option>';
	$out .= '<option value="q" '.selected( $recurring,"q",FALSE).'>'.esc_html( __('Quarterly (every84 days)','church-admin' ) ).'</option>';
	$out .= '<option value="a" '.selected( $recurring,"a",FALSE).'>'.esc_html( __('Annually on same date','church-admin' ) ).'</option>';
	$out .= '<option value="n10" '.selected( $recurring,"n10",FALSE).'>'.esc_html( __('1st Sunday','church-admin' ) ).'</option>';
	$out .= '<option value="n20" '.selected( $recurring,"n20",FALSE).'>'.esc_html( __('2nd Sunday','church-admin' ) ).'</option>';
	$out .= '<option value="n30" '.selected( $recurring,"n30",FALSE).'>'.esc_html( __('3rd Sunday','church-admin' ) ).'</option>';
	$out .= '<option value="n40" '.selected( $recurring,"n40",FALSE).'>'.esc_html( __('4th Sunday','church-admin' ) ).'</option>';
	$out .= '<option value="n50" '.selected( $recurring,"n50",FALSE).'>'.esc_html( __('5th Sunday','church-admin' ) ).'</option>';
	$out .= '<option value="l00" '.selected( $recurring,"l0",FALSE).'>'.esc_html( __('Last Sunday','church-admin' ) ).'</option>';

	$out .= '<option value="n11" '.selected( $recurring,"n11",FALSE).'>'.esc_html( __('1st Monday','church-admin' ) ).'</option>';
	$out .= '<option value="n21" '.selected( $recurring,"n21",FALSE).'>'.esc_html( __('2nd Monday','church-admin' ) ).'</option>';
	$out .= '<option value="n31" '.selected( $recurring,"n31",FALSE).'>'.esc_html( __('3rd Monday','church-admin' ) ).'</option>';
	$out .= '<option value="n41" '.selected( $recurring,"n41",FALSE).'>'.esc_html( __('4th Monday','church-admin' ) ).'</option>';
	$out .= '<option value="n51" '.selected( $recurring,"n51",FALSE).'>'.esc_html( __('5th Monday','church-admin' ) ).'</option>';
	$out .= '<option value="l11" '.selected( $recurring,"l1",FALSE).'>'.esc_html( __('Last Monday','church-admin' ) ).'</option>';

	$out .= '<option value="n12" '.selected( $recurring,"n12",FALSE).'>'.esc_html( __('1st Tuesday','church-admin' ) ).'</option>';
	$out .= '<option value="n22" '.selected( $recurring,"n22",FALSE).'>'.esc_html( __('2nd Tuesday','church-admin' ) ).'</option>';
	$out .= '<option value="n32" '.selected( $recurring,"n32",FALSE).'>'.esc_html( __('3rd Tuesday','church-admin' ) ).'</option>';
	$out .= '<option value="n42" '.selected( $recurring,"n42",FALSE).'>'.esc_html( __('4th Tuesday','church-admin' ) ).'</option>';
	$out .= '<option value="n52" '.selected( $recurring,"n52",FALSE).'>'.esc_html( __('5th Tuesday','church-admin' ) ).'</option>';
	$out .= '<option value="l22" '.selected( $recurring,"l2",FALSE).'>'.esc_html( __('Last Tuesday','church-admin' ) ).'</option>';

	$out .= '<option value="n13" '.selected( $recurring,"n13",FALSE).'>'.esc_html( __('1st Wednesday','church-admin' ) ).'</option>';
	$out .= '<option value="n23" '.selected( $recurring,"n23",FALSE).'>'.esc_html( __('2nd Wednesday','church-admin' ) ).'</option>';
	$out .= '<option value="n33" '.selected( $recurring,"n33",FALSE).'>'.esc_html( __('3rd Wednesday','church-admin' ) ).'</option>';
	$out .= '<option value="n43" '.selected( $recurring,"n43",FALSE).'>'.esc_html( __('4th Wednesday','church-admin' ) ).'</option>';
	$out .= '<option value="n53" '.selected( $recurring,"n53",FALSE).'>'.esc_html( __('5th Wednesday','church-admin' ) ).'</option>';
	$out .= '<option value="l33" '.selected( $recurring,"l3",FALSE).'>'.esc_html( __('Last Wednesday','church-admin' ) ).'</option>';

	$out .= '<option value="n14" '.selected( $recurring,"n14",FALSE).'>'.esc_html( __('1st Thursday','church-admin' ) ).'</option>';
	$out .= '<option value="n24" '.selected( $recurring,"n24",FALSE).'>'.esc_html( __('2nd Thursday','church-admin' ) ).'</option>';
	$out .= '<option value="n34" '.selected( $recurring,"n34",FALSE).'>'.esc_html( __('3rd Thursday','church-admin' ) ).'</option>';
	$out .= '<option value="n44" '.selected( $recurring,"n44",FALSE).'>'.esc_html( __('4th Thursday','church-admin' ) ).'</option>';
	$out .= '<option value="n54" '.selected( $recurring,"n54",FALSE).'>'.esc_html( __('5th Thursday','church-admin' ) ).'</option>';
	$out .= '<option value="l44" '.selected( $recurring,"l5",FALSE).'>'.esc_html( __('Last Thursday','church-admin' ) ).'</option>';

	$out .= '<option value="n15" '.selected( $recurring,"n14",FALSE).'>'.esc_html( __('1st Friday','church-admin' ) ).'</option>';
	$out .= '<option value="n25" '.selected( $recurring,"n24",FALSE).'>'.esc_html( __('2nd Friday','church-admin' ) ).'</option>';
	$out .= '<option value="n35" '.selected( $recurring,"n34",FALSE).'>'.esc_html( __('3rd Friday','church-admin' ) ).'</option>';
	$out .= '<option value="n45" '.selected( $recurring,"n44",FALSE).'>'.esc_html( __('4th Friday','church-admin' ) ).'</option>';
	$out .= '<option value="n55" '.selected( $recurring,"n44",FALSE).'>'.esc_html( __('5th Friday','church-admin' ) ).'</option>';
	$out .= '<option value="l55" '.selected( $recurring,"l5",FALSE).'>'.esc_html( __('Last Friday','church-admin' ) ).'</option>';


	$out .= '<option value="n16" '.selected( $recurring,"n16",FALSE).'>'.esc_html( __('1st Saturday','church-admin' ) ).'</option>';
	$out .= '<option value="n26" '.selected( $recurring,"n26",FALSE).'>'.esc_html( __('2nd Saturday','church-admin' ) ).'</option>';
	$out .= '<option value="n36" '.selected( $recurring,"n36",FALSE).'>'.esc_html( __('3rd Saturday','church-admin' ) ).'</option>';
	$out .= '<option value="n46" '.selected( $recurring,"n46",FALSE).'>'.esc_html( __('4th Saturday','church-admin' ) ).'</option>';
	$out .= '<option value="n56" '.selected( $recurring,"n56",FALSE).'>'.esc_html( __('5th Friday','church-admin' ) ).'</option>';
	$out .= '<option value="l66" '.selected( $recurring,"l6",FALSE).'>'.esc_html( __('Last Friday','church-admin' ) ).'</option>';

	$out .= '</select></div>';	
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('How many times in all? (Max 365)','church-admin' ) ).'</label><input type="number" min=1 max=365 required="required" class="church-admin-form-control" name="how_many" ';
	if(!empty( $error->how_many) ) $out.=esc_html($error->how_many);
	if(!empty( $data->how_many) && $edit_type!='single')  {$out.=' value="'.esc_attr($data->how_many).'"';}
	else{
		$out.='   value="1" ';
	}
	$out.='/></div>';

    
	/*****************************************
	*
	* Start Time	
	*
	******************************************/
	if(!empty( $data->start_time) )$data->start_time=substr( $data->start_time,0,5);//remove seconds
	if(!empty( $data->end_time) )$data->end_time=substr( $data->end_time,0,5);//remove seconds
	$out.='<div class="church-admin-form-group"><label>'.esc_html(__('Start Time of form HH:MM','church-admin')).'</label><input class="church-admin-form-control" type="time" name="start_time" ';
	if(!empty( $error->start_time) )$out.=$error->start_time;
	if(!empty( $data->start_time) )$out.=' value="'.esc_attr($data->start_time).'"';
	$out.='/></div>';
	/*****************************************
	*
	* End Time	
	*
	******************************************/
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('End Time of form HH:MM','church-admin' ) ).'</label><input class="church-admin-form-control" type="time" name="end_time" ';
	if(!empty( $error->end_time) ) $out.=$error->end_time;
	if(!empty( $data->end_time) )$out.=' value="'.esc_attr($data->end_time).'" ';
	$out.='/></div>';
	/*****************************************
	*
	* All day	
	*
	******************************************/
	$out.='<div class="church-admin-form-check"><input type="checkbox" name="all_day" ';
	if(!empty( $data->start_time)&&$data->start_time='00:00' &&!empty( $data->end_time)&&$data->end_time=='23:59')$out.=' checked="checked" ';
	$out.='/><label>'.esc_html( __('All day','church-admin' ) ).'</label></div>';
	/********************************
	*
	* Add a link
	*
	*********************************/
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Add a link to event page/post','church-admin' ) ).'</label><input type="text" name="link1" id="cal-link" class="church-admin-form-control" ';
	if(!empty( $data->link) )$out.=' value="'.esc_html( $data->link).'" ';
	$out.='placeholder="'.esc_html( __('Add a link','church-admin' ) ).'" /></div>';
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Or select one','church-admin' ) ) .'</label><select name="link2" class="church-admin-form-control">';
	//add in a dropdown of pages and post_status
	$out.='<option value="">'.esc_html( __('Select a post or page','church-admin' ) ).'</option>';
	$out.=' <optgroup label="'.esc_html( __('Posts','church-admin' ) ).'">';
	$args = array( 'numberposts' => 10);
	$postlinks = get_posts( $args);
	foreach( $postlinks as $postlink ) { setup_postdata( $postlink); $out.='<option value="'.get_permalink( $postlink->ID).'">'.$postlink->post_title.'</option>';}
	$out.='</optgroup>';
	$out.=' <optgroup label="'.esc_html( __('Pages','church-admin' ) ).'">';
	$args = array( 'post_type'=>'page','numberposts' =>-1,'orderby'=>'title','order'=>'ASC');
	$postlinks = get_posts( $args);
	foreach( $postlinks as $postlink ) { setup_postdata( $postlink); $out.='<option value="'.get_permalink( $postlink->ID).'">'.$postlink->post_title.'</option>';}
	$out.='</optgroup>';
	$out.='</select>';
	$out.='</div>';
	/*****************************************
	*
	* Link title		
	*
	******************************************/
	$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Link title','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="link_title" ';
	if(!empty( $data->link_title) )  {$out.=' value="'.esc_html( $data->link_title).'" ';}else{$out.=' value="'.esc_html( __('More information','church-admin' ) ).'" ';}
	$out.='/></div>';
	/*****************************************
	*
	* Year planner	
	*
	******************************************/
	$out.='<div class="church-admin-form-check"><input type="checkbox" name="year_planner" value="1"';
	if(!empty( $data->year_planner) ) $out.=' checked="checked"';
	$out.='/><label>'.esc_html( __('Appear on Year Planner?','church-admin' ) ).'</label></div>';


	$out.='<div class="church-admin-form-check"><input type="checkbox" name="general_calendar" value="1"  checked="checked" />';
	$out.='<input type="hidden" name="save_date" value="yes" /><label>'.esc_html( __('Appear on General Calendar?','church-admin' ) ).'</label></div>';
	
	return $out;
}


function church_admin_single_event_delete( $date_id)
{
    global $wpdb;
	if(empty($date_id) || !church_admin_int_check($date_id)){
		echo '<div class="notice notice-success inline"><h2>'.esc_html('Invalid date ID.','church-admin') .'<h2></div>';
		return;
	}
    $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.esc_sql( $date_id).'"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.esc_sql( $date_id).'"');
	if(!empty($data)){$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_meta WHERE event_id="'.esc_sql( $data->event_id).'"');}
    echo '<div id="message" class="notice notice-success inline">';
    echo '<p><strong>'.esc_html( __('Calendar Event deleted','church-admin' ) ).'.</strong></p>';
    echo '</div>';
	church_admin_new_calendar($data->start_date);
}

function church_admin_series_event_delete( $event_id,$date_id)
{
	
	if(empty($event_id) || !church_admin_int_check($event_id)){
		echo '<div class="notice notice-success inline"><h2>'.esc_html('Invalid event ID.','church-admin') .'<h2></div>';
		return;
	}
	if(empty($date_id) || !church_admin_int_check($date_id)){
		echo '<div class="notice notice-success inline"><h2>'.esc_html('Invalid event ID.','church-admin') .'<h2></div>';
		return;
	}
    global $wpdb;
    $date=$wpdb->get_var('SELECT start_date FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$date_id.'"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $event_id).'"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_meta WHERE event_id="'.esc_sql( $event_id).'"');
    echo '<div id="message" class="notice notice-success inline">';
    echo '<p><strong>'.esc_html( __('Calendar Events deleted','church-admin' ) ).'.</strong></p>';
    echo '</div>';
    church_admin_new_calendar(mysql2date('Y-m-01',$date) );
}
function church_admin_future_event_delete( $event_id,$date_id)
{
	if(empty($event_id) || !church_admin_int_check($event_id)){
		echo '<div class="notice notice-success inline"><h2>'.esc_html('Invalid event ID.','church-admin') .'<h2></div>';
		return;
	}
	if(empty($date_id) || !church_admin_int_check($date_id)){
		echo '<div class="notice notice-success inline"><h2>'.esc_html('Invalid event ID.','church-admin') .'<h2></div>';
		return;
	}
	global $wpdb;
	$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql( $event_id).'" AND date_id="'.esc_sql( $date_id).'"');
	if(empty($data)){
		echo '<div class="notice notice-success inline"><h2>'.esc_html('Event date not found.','church-admin') .'<h2></div>';
		return;
	}
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$event_id.'" AND start_date>="'.esc_sql($data->start_date).'" ');
	echo'<div class="notice notice-success inline"><h2>'.esc_html(sprintf(__('This and future occurrences of "%1$s" event deleted from %2$s','church-admin'),$data->title,mysql2date(get_option('date_format'),$data->start_date))).'</h2></div>';
	church_admin_new_calendar($data->start_date );
}

function church_admin_calendar_error_check( $data)
{
    global $error,$sqlsafe;
     //check startdate
      $start_date=church_admin_dateCheck( $data['start_date'] );

      $end_date=church_admin_dateCheck( $data['end_date'], $yearepsilon=50);

      if( $start_date)  {$sqlsafe['start_date']=esc_sql( $start_date);}else{$error->start_date==1;}

      //check start time
   if (preg_match ("/([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1})/", $data['start_time'] ) )  {$sqlsafe['start_time']=$data['start_time'];}else{$error['start_time']='1';}
        //check end time
  if (preg_match("/([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1})/", $data['end_time'] ) )  {$sqlsafe['end_time']=$data['end_time'];}else{$error->end_time='1';}

      //check recurring
      if( $data['recurring']=='s'||$data['recurring']=='1'||$data['recurring']=='7'||$data['recurring']=='14'||$data['recurring']=='n'||$data['recurring']=='m'||$data['recurring']=='a')  {$sqlsafe['recurring']=$data['recurring'];}else{$error['recurring']=1;}
      //check how many
      if( $data['recurring']!='s')
      {
        if(church_admin_int_check( $data['how_many'] ) )
        {
            $sqlsafe['how_many']=$data['how_many'];
        }
        else
        {
            $error->how_many=1;
        }
      }
      //check nth if necessary
      if( $data['recurring']=='n')
        {
            if(!empty( $data['nth'] ) && $data['nth']<='4')
            {
                $sqlsafe['nth']=$data['nth']; $sqlsafe['day']=$data['day'];
            }
            else
            {
                $error->nth=$error['day']=1;
            }
        }
       if(!empty( $data['title'] ) )  { $sqlsafe['title']= esc_sql( $data['title'] );}else{$error->title=1;}
       if(!empty( $data['description'] ) )  { $sqlsafe['description']= esc_sql(nl2br( $data['description'] ) );}else{$error->description=1;}
       $sqlsafe['description']=strip_tags( $sqlsafe['description'] );
      $sqlsafe['location']=esc_sql( $data['location'] );
      if(!empty( $_POST['category'] )&&church_admin_int_check( $data['category'] ) )  {$sqlsafe['category']=$data['category'];}else{$error['category']=1;}
      if( $data['year_planner']=='1')  {$sqlsafe['year_planner']=1;}else{$sqlsafe['year_planner']=0;}

    return $error;
}


function church_admin_calendar_list()
{
/******************************************
 * Security audited and updated 2023-06-02
 * Andy Moyle
 *******************************************/

		global $wpdb;
		//sanitize
		$current=!empty($_REQUEST['date'] )?sanitize_text_field(stripslashes($_REQUEST['date'])):wp_date('Y-m-d');
		//validate
		if(!church_admin_checkdate($current)){
			$current = wp_date('Y-m-d');
		}
		
		

		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=add-calendar&amp;date='.$current,'edit-calendar').'">'.esc_html( __('Add calendar Event','church-admin' ) ).'</a></p>';
		$events=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_calendar_date');
		if(!empty( $events) )
		{
			//which month to view
			
			$next = strtotime("+1 month",strtotime($current));
			$nextISO = date('Y-m-d',$next);
			$previous = strtotime("-1 month",strtotime($current));
			$previousISO = date('Y-m-d',$previous);
			$now=date("M Y",strtotime($current));
			$sqlnow=date("Y-m%", strtotime($current));
			$sqlnext=date("Y-m-d",$next);

			
			echo'<form action="admin.php?page=church_admin/index.php&amp;action=calendar-list" method="post">';
			wp_nonce_field('calendar-list');
			echo'<p><select name="date">';
			if(!empty($entereddate)){echo '<option value="'.esc_attr($entereddate).'">'.esc_html(date('M Y',$entereddate)).'</option>';}
		//generate a form to access calendar
		for ( $x=0; $x<12; $x++)
		{
			$date=strtotime("+ $x month",time() );
			echo '<option value="'.esc_attr($date).'">'.esc_html(date('M Y',$date)).'</option>';
		}
		echo '</select><input type="submit" value="'.esc_html( __('Go to date','church-admin' ) ).'" /></form></p>';
		//initialise table
		$theader='<tr><th>'.esc_html(__('Edit & delete options','church-admin')).'</th><th>'.esc_html(__('Start date','church-admin')).'</th><th>'.esc_html(__('Start Time','church-admin')).'</th><th>'.esc_html(__('End Time','church-admin')).'</th><th>'.esc_html(__('Event Name','church-admin')).'</th><th>'.esc_html(__('Category','church-admin')).'</th><th>'.esc_html(__('Year Planner','church-admin')).'?</th></tr>';
		
		echo '<h3><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=calendar&amp;action=calendar-list&amp;date='.$previousISO,'calendar-list').'">'.esc_html( __('Prev','church-admin' ) ).'</a> &nbsp;'.esc_html($now).'&nbsp;<a class="button-secondary"  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=calendar&amp;action=calendar-list&amp;date='.esc_html($nextISO),'calendar-list').'">'.esc_html( __('Next','church-admin' ) ).'</a></h3>';
		$table='<table class="widefat striped"><thead>'.$theader.'</thead><tbody>';


		$sql='SELECT a.*,b.category FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b WHERE a.cat_id=b.cat_id AND a.start_date LIKE "'.esc_sql($sqlnow).'" ORDER BY a.start_date';

	$result=$wpdb->get_results( $sql);
		foreach( $result AS $row)
		{
			
		$edit_option='<select class="church-admin-calendar-dropdown"><option>'.esc_html(__('Actions','church-admin')).'</option>';
		$edit_option.='<option data-what="'.esc_attr(__('Edit this single event','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=single-event-edit&section=calendar&amp;event_id='.(int)$row->event_id.'&amp;date_id='.(int)$row->date_id,'single-event-edit').'">'.esc_html('Single event edit','church-admin').'</option>';
				
		if( $row->recurring!='s'){
			$edit_option.='<option data-what="'.esc_attr(__('Edit whole series','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=whole-series-edit&section=calendar&amp;event_id='.(int)$row->event_id.'&amp;date_id='.(int)$row->date_id,'series_event_edit').'">'.esc_html('Edit whole series','church-admin').'</option>';
			$edit_option.='<option data-what="'.esc_attr(__('Edit rest of series','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=future-series-edit&section=calendar&amp;event_id='.(int)$row->event_id.'&amp;date_id='.(int)$row->date_id,'future-series-edit').'">'.esc_html('Edit rest of series','church-admin').'</option>';
		
		}
		$edit_option.='<option data-what="'.esc_attr(__('Delete single event','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=single-event-delete&section=calendar&amp;event_id='.(int)$row->event_id.'&amp;date_id='.(int)$row->date_id,'single_event_edit').'">'.esc_html('Single event delete','church-admin').'</option>';
		if( $row->recurring!='s'){
				$edit_option.='<option data-what="'.esc_attr(__('Delete this and future events in series','church-admin')).'" value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=future-event-delete&amp;event_id='.(int)$row->event_id.'&amp;date_id='.(int)$row->date_id,'future-event-delete').'">'.esc_html('Delete this and future series','church-admin').'</option>';
				$edit_option.='<option data-what="'.esc_attr(__('Delete whole series','church-admin')).'"  value="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=series-event-delete&amp;event_id='.(int)$row->event_id.'&amp;date_id='.(int)$row->date_id,'future_series_delete').'">'.esc_html('Delete whole series','church-admin').'</option>';

		}
		$edit_option.='</select>';




		//sort out category
		if ( empty( $row->bgcolor) )$row->bgcolor='#FFF';
		$table.='<tr><td>'.$edit_option.'</td><td>'.mysql2date(get_option('date_format'),$row->start_date).'</td><td>'.esc_html( $row->start_time).'</td><td>'.esc_html( $row->end_time).'</td><td>'.esc_html( $row->title).'</td><td style="border-left:3px SOLID '.esc_attr($row->bgcolor).'">'.esc_html( $row->category).'</td><td>';
		if( $row->year_planner)  {$table.=esc_html(__('Yes','church-admin'));}else{$table.='&nbsp;';}
		$table.='</td></tr>';
		}
		$table.='</tbody><tfoot>'.$theader.'</tfoot></table>';
		echo $table;

		echo '<script type="text/javascript">	
		jQuery(document).ready(function( $) {$(".church-admin-calendar-dropdown").on("change", function(event) {
			var action =$(this).find(":selected").val();
			var what = $(this).find(":selected").data("what");
			console.log(action);
			if(confirm(what + " '.__('Are you sure?','church-admin').' ")){
					window.open(action);
				}
			';
		echo '});});</script>';
	}//end of non empty calendar table

}





function church_admin_import_ical()
{
	

	require_once(plugin_dir_path(dirname(__FILE__) ).'/includes/ical-reader.php');
	global $wpdb;
	echo'<h2>'.esc_html( __('Import ics file or url into calendar','church-admin' ) ).'</h2>';
	echo'<p>'.esc_html('If importing from a Google calendar, click on your calendar\'s three dots in Google, then "Settings and Share" and then "Export Calendar"','church-admin').'</p>';
	echo'<p><a class="button-secondary" href="https://www.churchadminplugin.com/tutorials/import-ical">'.esc_html( __('How to tutorial','church-admin' ) ).'</a></p>';
	if(!empty( $_POST['import-ical'] ) )
	{
		$event_ids=array();
		//get category id
		$cat_id=1;
		if(!empty( $_POST['cat_id'] ) )
		{
			$cat_id=(int)$_POST['cat_id'];
		}
		if(!empty( $_POST['new-category'] ) )
		{
			$category=sanitize_text_field( stripslashes($_POST['new-category'] ));



		}
		//clear just chosen category
		if(!empty( $_POST['clear-category'] ) )
		{
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE cat_id="'.(int)$cat_id.'"');
		}
		//clear calendar completely
		if(!empty( $_POST['clear-calendar'] ) )
		{
			$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_calendar_date');
		}
		
		
		try{
			$cat_id = !empty($_POST['cat_id']) ? church_admin_sanitize($_POST['cat_id']) :1;
			// add in new category


			$start=!empty($_POST['start_date'])?sanitize_text_field(stripslashes($_POST['start_date'])):null;
			if ( empty($start) || !church_admin_checkdate($start)){$start=wp_date('Y-m-d');}
			$end=!empty($_POST['end_date'])?sanitize_text_field(stripslashes($_POST['end_date'])):null;
			if ( empty( $end) || !church_admin_checkdate($end)){$end=date('Y-m-d',strtotime('+1 year') );}

			
			if  ( $_FILES['fileToUpload']['size']>0)
			{
				$upload_dir = wp_upload_dir();
	
				$destination = $upload_dir['basedir'].'/church-admin-cache/calendar.ics';
				$path = $_FILES['fileToUpload']['tmp_name'];
				$zip = new ZipArchive;
				if ($zip->open($path) === true) {
					church_admin_debug('A zip file');
					for($i = 0; $i < $zip->numFiles; $i++) {
						$filename = $zip->getNameIndex($i);
						$fileinfo = pathinfo($filename);
						copy("zip://".$path."#".$filename, $destination);
					}                   
					$zip->close();                   
				}
				else{
					//not a zip file.
					church_admin_debug('Not a zip file');
					move_uploaded_file( $_FILES['fileToUpload']['tmp_name'], $destination);
					
				}
				if(!file_exists($destination)){
					echo'<div class="notice-warning notice"><h2>'.__("File not uploaded",'church-admin');
				}
				
			}
			else{
				echo'<div class="notice-warning notice"><h2>'.__("File not uploaded",'church-admin');
				return;
			}


			$startTC = strtotime($start);
			$endTC = strtotime($end);

			$ical = new ical($destination);
			
			
			echo '<p>'.esc_html(sprintf(__('Events between %1$s and %2$s','church-admin'),mysql2date(get_option('date_format'),$start),mysql2date(get_option('date_format'),$end))).'</p>';
			echo '<p><strong>'.esc_html(__('Initially all dates will be inserted to manage recurring events and then those outside of your chosen range will be deleted.','church-admin' ) ).'</strong></p>';
			$events = $ical->events();
			foreach($events AS $key=>$event){
				
				$start_datetime = $ical->iCalDateToDateTime( $event['DTSTART']);
			
				$start_date = $start_datetime->format('Y-m-d');
				$startTC = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
				

				if(!empty($event['SUMMARY'])){
					$title = $event['SUMMARY'];
				}
				else{
					//skip this one as no title
					continue;
				}
				echo'<h3>'.esc_html($title).'</h3>';
				if(!empty($event['UID'])){
					echo'<p>'.esc_html(sprintf(__('UID: %1$s','church-admin'),$event['UID'])).'</p>';
				}
				echo'<p>'.esc_html(sprintf(__('Start date: %1$s','church-admin'),$start_date)).'</p>';
				$start_time = $start_datetime->format('H:i:s');
				echo'<p>'.esc_html(sprintf(__('Start time: %1$s','church-admin'),$start_time)).'</p>';
				
				
				if(!empty($event['DTEND'])){
					$end_datetime = $ical->iCalDateToDateTime( $event['DTEND']);
				}
				else
				{
					$end_datetime = $start_datetime->modify('+1 hour');
					echo'<p stykle="color:red">'.__('Missing end time in iCal, so assumed 1hr later','church-admin');
				}
				$end_date = $end_datetime->format('Y-m-d');
				echo'<p>'.esc_html(sprintf(__('End date: %1$s','church-admin'),$end_date)).'</p>';
				$end_time = $end_datetime->format('H:i:s');
				echo'<p>'.esc_html(sprintf(__('End time: %1$s','church-admin'),$end_time)).'</p>';
				$title =$event['SUMMARY'];
				//handle recurrence
				$recurring = 's';
				$frequency =1;
				if(!empty($event['RRULE'])){

					$rules =array();
					//RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU

					$rule = explode(';', $event['RRULE']);
					foreach ($rule as $r) {
						list($key, $value) = explode('=', $r);
						$rules[$key] = $value;
					}

					switch($rules['FREQ']){
						case 'YEARLY': $recurring = 'a';break;
						case 'MONTHLY': $recurring = 'm';break;
						case 'WEEKLY': $recurring = '7'; break;
					}
					if(!empty($rules['COUNT']) && $rules['COUNT']==1 ){
						//stupid situation where recurring is set, but how many is just once
						$rules['UNTIL'] = $event['DTSTART'];
					}
					if(!empty($rules['UNTIL'])){
						$last_occurrence = $ical->iCalDateToDateTime($rules['UNTIL']);
						$untilTC = $ical->iCalDateToUnixTimestamp($rules['UNTIL']);
					}
					else
					{
						$last_occurrence = $start_datetime->modify("+ 2 year");
						$untilTC = $last_occurrence->getTimestamp();
						echo '<p style="color:red">'.esc_html(sprintf(__('No end date for recurrence given, so 12 years assumed %1$s','church-admin'),mysql2date(get_option('date_format'),date('Y-m-d',$untilTC)))).'</p>';
					}
				}
				$description = !empty($event['DESCRIPTION']) ? church_admin_sanitize($event['DESCRIPTION']):null; 
				$location = !empty($event['LOCATION']) ? church_admin_sanitize($event['LOCATION']):null; 
				$uid = !empty($event['UID']) ? church_admin_sanitize($event['UID']):null; 
				//check not already saved
				

				
				//sort an event_id if needed
				//get event_id
				// starts at 1, use max + 1; or if recurring event use already used one
				$event_id=1;

				if( $recurring!='s')
				{
					$event_id=$wpdb->get_var('SELECT event_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE external_uid="'.esc_sql( $uid).'"');
					
					if ( empty( $event_id) )$event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date')+1;
				}
				else
				{
					$event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date')+1;
				}
				$event_ids[$event_id] =!empty($event_ids[$event_id]) ?$event_ids[$event_id]++: 1;

				
				switch($recurring){

					case 's':
						$date_id=$wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql( $title).'" AND description="'.esc_sql( $description).'" AND location="'.esc_sql( $location).'" AND start_date="'.esc_sql( $start_date).'" AND start_time="'.esc_sql( $start_time).'" AND end_time="'.esc_sql( $end_time).'" AND recurring="'.esc_sql( $recurring).'" AND external_uid="'.esc_sql( $uid).'"');
						if(empty($date_id))
						{
							$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,recurring,event_id,cat_id,general_calendar,external_uid) VALUES("'.esc_sql( $title).'","'.esc_sql( $description).'","'.esc_sql( $location).'","'.esc_sql( $start_date).'","'.esc_sql( $start_time).'","'.esc_sql( $end_time).'","'.esc_sql( $recurring).'","'.(int)$event_id.'","'.(int)$cat_id.'","1","'.esc_sql( $uid).'")');
							echo'<p>'.__('Event saved','church-admin').'</p>';
						}
					break;
					case '1':
						$interval =  24 * 60 * 60;
						for($st_date = $startTC+$interval ; $st_date <= $untilTC; $st_date+=$interval ){
							if(!empty($event['EXDATE'])){
								$exclude = $ical->iCalDateToDateTime($event['EXDATE']);
								$st_datetime =new DateTime();
								$st_datetime->setTimestamp($st_date);
								if($st_datetime == $exlclude){
									echo'<p>'.esc_html(sprintf('%1$s occurence set to be excluded','church-admin'),mysql2date(get_option('date_format'),$exclude->format('Y-m-d'))).'</p>';
									continue;
								}
							}
							$check=null;
							$sql_st_date=date('Y-m-d',$st_date);
							$check = $wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql( $title).'" AND description="'.esc_sql( $description).'" AND location="'.esc_sql( $location).'" AND start_date="'.esc_sql($sql_st_date).'" AND start_time="'.esc_sql( $start_time).'" AND end_time="'.esc_sql( $end_time).'" AND recurring="'.esc_sql( $recurring).'" AND event_id="'.(int)$event_id.'" AND external_uid="'.esc_sql( $uid).'"');
							if(empty($check)){
								$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,recurring,event_id,cat_id,general_calendar,external_uid) VALUES("'.esc_sql( $title).'","'.esc_sql( $description).'","'.esc_sql( $location).'","'.esc_sql( $sql_st_date).'","'.esc_sql( $start_time).'","'.esc_sql( $end_time).'","'.esc_sql( $recurring).'","'.(int)$event_id.'","'.(int)$cat_id.'","1","'.esc_sql( $uid).'")');
								echo $wpdb->last_query.'<br>';
							}
						}
					break;
					case '7':
						$interval = 7 * 24 * 60 * 60;
						for($st_date = $startTC+$interval ; $st_date <= $untilTC; $st_date+=$interval ){
							if(!empty($event['EXDATE'])){
								$exclude = $ical->iCalDateToDateTime($event['EXDATE']);
								$st_datetime =new DateTime();
								$st_datetime->setTimestamp($st_date);
								if($st_datetime == $exlclude){
									echo'<p>'.esc_html(sprintf('%1$s occurence set to be excluded','church-admin'),mysql2date(get_option('date_format'),$exclude->format('Y-m-d'))).'</p>';
									continue;
								}
							}
							$check=null;
							$sql_st_date=date('Y-m-d',$st_date);
							$check = $wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql( $title).'" AND description="'.esc_sql( $description).'" AND location="'.esc_sql( $location).'" AND start_date="'.esc_sql($sql_st_date).'" AND start_time="'.esc_sql( $start_time).'" AND end_time="'.esc_sql( $end_time).'" AND recurring="'.esc_sql( $recurring).'" AND event_id="'.(int)$event_id.'" AND external_uid="'.esc_sql( $uid).'"');
							if(empty($check)){
								$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,recurring,event_id,cat_id,general_calendar,external_uid) VALUES("'.esc_sql( $title).'","'.esc_sql( $description).'","'.esc_sql( $location).'","'.esc_sql( $sql_st_date).'","'.esc_sql( $start_time).'","'.esc_sql( $end_time).'","'.esc_sql( $recurring).'","'.(int)$event_id.'","'.(int)$cat_id.'","1","'.esc_sql( $uid).'")');
								echo '<p>'.esc_html(sprintf(__('Added weekly occurrence for %1$s','church-admin'),mysql2date(get_option('date_format'),$sql_st_date))).'</p>';
							}
						}
					break;
					case '14':
						$interval = 14 * 24 * 60 * 60;
						for($st_date = $startTX+$interval ; $st_date <= $untilTC; $st_date+=$interval ){
							if(!empty($event['EXDATE'])){
								$exclude = $ical->iCalDateToDateTime($event['EXDATE']);
								$st_datetime =new DateTime();
								$st_datetime->setTimestamp($st_date);
								if($st_datetime == $exlclude){
									echo'<p>'.esc_html(sprintf('%1$s occurence set to be excluded','church-admin'),mysql2date(get_option('date_format'),$exclude->format('Y-m-d'))).'</p>';
									continue;
								}
							}
							$check=null;
							$sql_st_date=date('Y-m-d',$st_date);
							$check = $wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql( $title).'" AND description="'.esc_sql( $description).'" AND location="'.esc_sql( $location).'" AND start_date="'.esc_sql( $sql_st_date).'" AND start_time="'.esc_sql( $start_time).'" AND end_time="'.esc_sql( $end_time).'" AND recurring="'.esc_sql( $recurring).'" AND event_id="'.(int)$event_id.'" AND external_uid="'.esc_sql( $uid).'"');
							if(empty($check)){
								$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,recurring,event_id,cat_id,general_calendar,external_uid) VALUES("'.esc_sql( $title).'","'.esc_sql( $description).'","'.esc_sql( $location).'","'.esc_sql( $st_date).'","'.esc_sql( $start_time).'","'.esc_sql( $end_time).'","'.esc_sql( $recurring).'","'.(int)$event_id.'","'.(int)$cat_id.'","1","'.esc_sql( $uid).'")');
								echo '<p>'.esc_html(sprintf(__('Added fortnightly occurrence for %1$s','church-admin'),mysql2date(get_option('date_format'),$sql_st_date))).'</p>';
							}
							
						}
					break;
					case 'm':
						$x=1;
						for($st_date = $start_datetime; $st_date < $last_occurrence; $st_date = $st_date->modify('+1 month')){
							if(!empty($event['EXDATE'])){
								$exclude = $ical->iCalDateToDateTime($event['EXDATE']);
								
								if($st_date == $exlclude){
									echo'<p>'.esc_html(sprintf('%1$s occurence set to be excluded','church-admin'),mysql2date(get_option('date_format'),$exclude->format('Y-m-d'))).'</p>';
									continue;
								}
							}
							$check=null;
							$sql_st_date=$st_date->format('Y-m-d');
							$check = $wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql( $title).'" AND description="'.esc_sql( $description).'" AND location="'.esc_sql( $location).'" AND start_date="'.esc_sql( $sql_st_date).'" AND start_time="'.esc_sql( $start_time).'" AND end_time="'.esc_sql( $end_time).'" AND recurring="'.esc_sql( $recurring).'" AND event_id="'.(int)$event_id.'" AND external_uid="'.esc_sql( $uid).'"');
							if(empty($check)){
								$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,recurring,event_id,cat_id,general_calendar,external_uid) VALUES("'.esc_sql( $title).'","'.esc_sql( $description).'","'.esc_sql( $location).'","'.esc_sql( $sql_st_date).'","'.esc_sql( $start_time).'","'.esc_sql( $end_time).'","'.esc_sql( $recurring).'","'.(int)$event_id.'","'.(int)$cat_id.'","1","'.esc_sql( $uid).'")');
								echo '<p>'.esc_html(sprintf(__('Added monthly occurrence for %1$s','church-admin'),mysql2date(get_option('date_format'),$sql_st_date))).'</p>';
							}
							$x++;
						}
					break;
					case 'a':
						$x=1;
						for($st_date = $start_datetime; $st_date < $last_occurrence; $st_date = $st_date->modify('+1 year')){
							if(!empty($event['EXDATE'])){
								$exclude = $ical->iCalDateToDateTime($event['EXDATE']);
								
								if($st_date == $exlclude){
									echo'<p>'.esc_html(sprintf('%1$s occurence set to be excluded','church-admin'),mysql2date(get_option('date_format'),$exclude->format('Y-m-d'))).'</p>';
									continue;
								}
							}
							$check=null;
							$sql_st_date=$st_date->format('Y-m-d');
							$check = $wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql( $title).'" AND description="'.esc_sql( $description).'" AND location="'.esc_sql( $location).'" AND start_date="'.esc_sql( $sql_st_date).'" AND start_time="'.esc_sql( $start_time).'" AND end_time="'.esc_sql( $end_time).'" AND recurring="'.esc_sql( $recurring).'" AND event_id="'.(int)$event_id.'" AND external_uid="'.esc_sql( $uid).'"');
							if(empty($check)){
								$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,recurring,event_id,cat_id,general_calendar,external_uid) VALUES("'.esc_sql( $title).'","'.esc_sql( $description).'","'.esc_sql( $location).'","'.esc_sql( $sql_st_date).'","'.esc_sql( $start_time).'","'.esc_sql( $end_time).'","'.esc_sql( $recurring).'","'.(int)$event_id.'","'.(int)$cat_id.'","1","'.esc_sql( $uid).'")');
								echo '<p>'.esc_html(sprintf(__('Added annual occurrence for %1$s','church-admin'),mysql2date(get_option('date_format'),$sql_st_date))).'</p>';
							}
							$x++;
						}
					break;	



				}

					
					
				
				
				
				//church_admin_debug($wpdb->last_query);

			}

				//remove calendar dates o/s date range
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE start_date<"'.esc_sql($start).'"');
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE start_date>"'.esc_sql($end).'"');
			echo'<p>'.__('Finished','church-admin').'</p>';
		} catch (\Exception $e) {
			echo'<div class="notice notice-warning"><h2>'.esc_html( __('It did not work','church-admin' ) ).'</h2><p>';
			echo $e->getMessage();
			echo'</p></div>';
		}

	


	}
	else
	{

		echo'<form action="" method="post" enctype="multipart/form-data">';
		echo'<table class="form-table">';
		echo'<tr><th colspan="2"><h3>'.esc_html( __('ICS source','church-admin' ) ).'</h3></th></tr>';
		
		echo'<tr><th scope="row">'.esc_html( __('Upload ICS file','church-admin' ) ).'</th><td><input required="required" type="file" name="fileToUpload" id="fileToUpload"></td></tr>';
		echo'<tr><th colspan="2"><h3>'.esc_html( __('Date range to import','church-admin' ) ).'</h3></th></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Start date','church-admin' ) ).'</th><td>'.church_admin_date_picker(wp_date('Y-m-d'),'start_date',NULL,date('Y-m-d',strtotime("-10 years")),NULL,'start_date','start_date',NULL,FALSE,NULL,NULL).'</td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('End date','church-admin' ) ).'</th><td>'.church_admin_date_picker(wp_date('Y-m-d',strtotime('+1 year') ),'end_date',NULL,NULL,NULL,'end_date','end_date',NULL,FALSE,NULL,NULL).'</td></tr>';
		echo'<tr><th colspan="2"><h3>'.esc_html( __('What to do with current calendar','church-admin' ) ).'</h3></th></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Clear all current calendar events','church-admin' ) ).'</th><td><input type="checkbox" name="clear-calendar" value="1" /></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Or clear current calendar events in chosen category below','church-admin' ) ).'</th><td><input type="checkbox" name="clear-category" value="1" /></td></tr>';
		//get calendar categories
		$cats=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_category');
		if(!empty( $cats) )
		{
			echo '<tr><th scope="row">'.esc_html( __('Choose category to put events into','church-admin' ) ).'</th><td><select name="cat_id">';
			foreach( $cats AS $cat)
			{
				echo'<option value="'.(int)$cat->cat_id.'" style="background-color:'.esc_html( $cat->bgcolor).'">'.esc_html( $cat->category).'</option>';
			}
			echo '</select></td></tr>';
		}
		echo'<tr><th scope="row">'.esc_html( __('Or create new calendar category','church-admin' ) ).'</th><td><input type="text" name="new-category" /></td></tr>';
		
		echo'<tr><td colspan=2><input type="hidden" name="import-ical" value="1" /><input class="button-primary" type="submit" value="'.esc_html( __('Import','church-admin' ) ).'" /></td></tr></table></form>';
	}

}


function church_admin_delete_calendar(){

	global $wpdb;

	if(!empty($_POST['i-am-sure'])){

		$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_calendar_date');
		echo'<div class="notice notice-success"><h2>'.esc_html(__('Calendar deleted','church-admin')).'</h2></div>';
		
		
		echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=add-calendar&section=calendar','edit-calendar').'">'.esc_html(__('Add calendar event','church-admin')).'</a></p>';
		church_admin_new_calendar();
	}
	else{
		echo'<h2>'.esc_html(__('Delete calendar','church-admin')).'</h2><form action="" method="POST"><p><input type="hidden" name="i-am-sure" value="yes"><input class="button-primary" type="submit" value="'.__('Yes, delete all events','church-admin').'"></p></form>';

	}
	

}