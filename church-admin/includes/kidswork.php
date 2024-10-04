<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
function church_admin_kidswork()
{
    global $wpdb;
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}

	$genders=get_option('church_admin_gender');
	echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=ministries&action=edit_kidswork','edit_kidswork').'">'.esc_html( __('Add a kidswork age group','church-admin' ) ).'</a></p>';
	echo '<p>'.esc_html( __('The dates will go up a year on January 1st automatically.','church-admin'));


	//autocorrect
	if(date('z')==0)  {$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_kidswork SET youngest = youngest + INTERVAL 1 YEAR, oldest = oldest + INTERVAL 1 YEAR');}
	//get groups
	$results=$wpdb->get_results('SELECT a.*,b.ministry FROM '.$wpdb->prefix.'church_admin_kidswork a  LEFT JOIN '.$wpdb->prefix.'church_admin_ministries b ON a.department_id=b.ID ORDER BY youngest DESC');
	if(!empty( $results) )
	{

		echo '<table class="widefat striped wp-list-table"><thead><tr><th class="column-primary">'.esc_html( __('Group name','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html(__('Gender specific','church-admin')).'</th><th>'.esc_html( __('Led by','church-admin' ) ).'</th><th>'.esc_html( __('Youngest','church-admin' ) ).'</th><th>'.esc_html( __('Oldest','church-admin' ) ).'</th></tr></thead><tbody>';
		foreach( $results AS $row)
		{
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=ministries&action=edit_kidswork&id='.(int)$row->id,'edit_kidswork').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
			$delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=ministries&action=delete_kidswork&id='.(int)$row->id,'delete_kidswork').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
			if(empty($row->gender)||$row->gender=='x'){
				$gender = __('Mixed','church-admin');
			}
			else
			{
				$gender = $genders[$row->gender];
			}
			
			echo '<tr><td class="column-primary" data-colname="'.esc_html( __('Group name','church-admin' ) ).'">'.esc_html( $row->group_name).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td><td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td><td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td><td data-colname="'.esc_html(__('Gender specific','church-admin')).'">'.esc_html($gender).'</td><td data-colname="'.esc_html( __('Ministry','church-admin' ) ).'">'.esc_html( $row->ministry).'</td><td  data-colname="'.esc_html( __('Youngest','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->youngest).'</td><td  data-colname="'.esc_html( __('Oldest','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->oldest).'</td></tr>';
		}
		echo '</table>';
	}
	
		

}




function church_admin_delete_kidswork( $id)
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
	global $wpdb;
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_kidswork WHERE id="'.esc_sql( $id).'"');
	echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Kidswork group deleted','church-admin' ) ).'</strong></p></div>';
		church_admin_kidswork();
}



function church_admin_edit_kidswork( $id=NULL)
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
	global $wpdb;
	$genders=get_option('church_admin_gender');
	if(!empty( $_POST['save'] ) )
	{
	
		$sqlsafe=array();
		foreach( $_POST AS $key=>$value){
			$sqlsafe[$key]=esc_sql(sanitize_text_field(stripslashes( $value) ));
		}
		
		if ( empty( $id) )$id=$wpdb->get_var('SELECT id FROM '.$wpdb->prefix.'church_admin_kidswork WHERE group_name="'.$sqlsafe['group_name'].'" AND youngest="'.$sqlsafe['youngest'].'" AND oldest="'.$sqlsafe['oldest'].'" AND department_id="'.$sqlsafe['department_id'].'"');
		if(!empty( $id) )
		{//update
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_kidswork SET group_name="'.$sqlsafe['group_name'].'" , youngest="'.$sqlsafe['youngest'].'" , oldest="'.$sqlsafe['oldest'].'" , department_id="'.$sqlsafe['department_id'].'", gender="'.$sqlsafe['gender'].'" WHERE id="'.esc_sql( $id).'"');
		}
		else
		{//insert
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_kidswork (group_name,gender,youngest,oldest,department_id)VALUES("'.$sqlsafe['group_name'].'","'.$sqlsafe['gender'].'","'.$sqlsafe['youngest'].'","'.$sqlsafe['oldest'].'","'.$sqlsafe['department_id'].'" )');
		}
		
		echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Kidswork updated','church-admin' ) ).'</strong></p></div>';
		church_admin_kidswork();

	}
	else
	{
		if(!empty( $id) )$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork WHERE id="'.esc_sql( $id).'"');
		echo'<h2>'.esc_html( __('Add a kids work group','church-admin' ) ).'<form action="" method="POST">';
		echo'<table class="form-table"><tbody><tr><th scope="row">'.esc_html( __('Group Name','church-admin' ) ).'</th><td><input type="text" name="group_name" id="group_name" ';
		if(!empty( $data->group_name) ) echo'value="'.esc_html( $data->group_name).'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Gender','church-admin' ) ).'</th>';
		echo'<td><select name="gender"><option value="x" ';
		if(!isset($data->gender)){ echo' selected="selected" ';}
		echo'>'.esc_html('Mixed','church-admin').'</option>';

		foreach( $genders AS $key=>$value)  {echo '<option value="'.esc_html( $key).'" >'.esc_html( $value).'</option>';}
		echo '</select></td></tr>'."\r\n";

		if(!empty( $data->youngest) )  {$youngest=$data->youngest;}else{$youngest=NULL;}
		echo'<tr><th scope="row">'.esc_html( __('Youngest','church-admin' ) ).'</th><td>'.church_admin_date_picker( $youngest,'youngest',FALSE,1910,date('Y'),'youngest','youngest').'</td></tr>';
		if(!empty( $data->oldest) )  {$oldest=$data->oldest;}else{$oldest=NULL;}
		echo'<tr><th scope="row">'.esc_html( __('Oldest','church-admin' ) ).'</th><td>'.church_admin_date_picker( $oldest,'oldest',FALSE,1910,date('Y'),'oldest','oldest').'</td></tr>';

		echo'<tr><th scope="row">'.esc_html( __('Led by people from ','church-admin' ) ).'</th><td>';
   		$ministries=church_admin_ministries();

		if(!empty( $ministries) )
		{
			echo'<select name="department_id">';
			$first=$option='';
			foreach( $ministries AS $ID=>$name)
			{
				if(!empty( $data->department_id) && $data->department_id==$ID) $first='<option selected="selected" value="'.(int)$id.'">'.esc_html( $name).'</option>';
				else $option.='<option value="'.(int)$ID.'">'.esc_html( $name).'</option>';
			}
			echo $first.$option;
			echo'</select>';
		}
		echo'</td></tr>';
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save" value="yes" /><input type="submit" value="Save" class="button-primary" /></td></tr></tbody></table></form>';

	}
}






/**
 * 		Safeguarding main function
 *
 *		Looks to see if safeguarding nation and which ministries require safeguardingare set up
 *
 *
 *
 */
function church_admin_safeguarding_legacy_main()
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
    echo '<h2>'.esc_html( __("Legacy Safeguarding",'church-admin' ) ).'</h2>';
	
	echo church_admin_safeguarding_old_style_nation();
	$nation=get_option('church_admin_safeguarding_nation');
	if(!empty( $nation) )echo church_admin_safeguarding_ministries();
	if(!empty( $nation) )echo church_admin_safeguarding_old_style_list();



	

}





/*********************************
 * LEGACY SAFEGUARDING FUNCTIONS
 ********************************/



/**
 * Choose which nation's safeguarding to follow
 *
 *
 */
function church_admin_safeguarding_old_style_nation()
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin'))).'</h2></div>';
		return;
	}
	if(!empty( $_POST['save-nation'] ) )
	{
		switch( $_POST['save-nation'] )
		{
			case 'Australia':$nation='Australia';break;
			case 'United Kingdom':$nation='United Kingdom';break;
		}
		if(!empty( $nation) )update_option('church_admin_safeguarding_nation',$nation);
		echo'<div class="notice notice-success">'.esc_html( __('Nation saved','church-admin' ) ).'</div>';
	}
	$nation=get_option('church_admin_safeguarding_nation');
	if ( empty( $nation) )  {$safe_red='style="color:red" ';}else{$safe_red='';}
	$out='<h2 class="safe-nation-toggle" '.$safe_red.' >'.esc_html( __("Choose which nation's safeguarding standards to use (Click to toggle)",'church-admin' ) ).'</h2>';
	echo '<div class="safe-nation" style="display:none">';
	echo '<p><a href="https://www.churchadminplugin.com/contact-us">'.esc_html( __('Please let me know if your nation has child protection or safeguarding requirements for people working with children and vulnerable adults.','church-admin' ) ).'</a></p>';

	$nation=get_option('church_admin_safeguarding_nation');
	echo '<form action="" method="POST">';
	echo '<table class="form-table">';
	//Australia
	echo '<tr><th scope="row">Australia</th><td><input type="radio" name="save-nation" value="Australia" ';
	if(!empty( $nation) && $nation=='Australia')echo ' checked="checked" ';
	echo '/></td></tr>';
	//Australia
	echo '<tr><th scope="row">United Kingdom</th><td><input type="radio" name="save-nation" value="United Kingdom" ';
	if(!empty( $nation) && $nation=='United Kingdom')echo ' checked="checked" ';
	echo '/></td></tr>';
	echo '<tr><td colspan="2"><input type="submit" name="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr>';
	echo '</table></form>';
	echo '</div>';
	echo '<script type="text/javascript">jQuery(function()  {  jQuery(".safe-nation-toggle").click(function()  {jQuery(".safe-nation").toggle();  });});</script>';

	return $out;
}
/**
 * Adds people in safeguarding required ministries to the safeeguarding table
 *
 *
 */
function church_admin_populate_old_style_safeguarding()
{
	global $wpdb;
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
	$ministries=church_admin_safeguarded_ministries();

	if ( empty( $ministries) )
	{
		$out='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-ministry&section=ministries','edit-ministry').'">'.esc_html( __('Please set up some ministries first','church-admin' ) ).'</a></p>';
	}
	else
	{//safe to proceed ministries available to choose
		$results=array();
		foreach( $ministries AS $key=>$ministry_id)
		{
			$results=church_admin_people_meta( $ministry_id,NULL,'ministry');


			//$results is $wpdb object with data array(people_id,ID)
			foreach( $results AS $row)
			{
				$department_id=array();
				//check to see if people_id is in CP table
				$peopleData=$wpdb->get_row('SELECT people_id,department_id FROM '.$wpdb->prefix.'church_admin_safeguarding WHERE people_id="'.(int)$row->people_id.'"');
				if(!empty( $peopleData) )$department_id=maybe_unserialize( $peopleData->department_id);

				//put the ministry id ID into array
				if(!in_array( $row->ID,$department_id) )$department_id[]=$row->ID;
				$serializedDepartmentArray=serialize( $department_id);
				if ( empty( $peopleData->people_id) )
				{
					//create new record
					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_safeguarding (people_id,department_id)VALUES("'.(int)$row->people_id.'","'.esc_sql( $serializedDepartmentArray).'")');
				}
				else
				{
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_safeguarding SET department_id="'.esc_sql( $serializedDepartmentArray).'" WHERE people_id="'.(int)$row->people_id.'"');
				}
			}
		}
	}//safe to proceeed, ministries available
}
/**
 * Safeguarding required ministries people list table
 *
 * @return $out
 *
 */
function church_admin_safeguarding_old_style_list()
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
	global $wpdb;
	//make sure list is populated
	church_admin_populate_old_style_safeguarding();
	$nation=get_option('church_admin_safeguarding_nation');
	$out='<h2>'.esc_html( __('People in Safeguarding required ministries','church-admin' ) ).' ('.esc_html( $nation).')</h2>';

	$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name,a.active,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_safeguarding b WHERE b.people_id=a.people_id ORDER BY a.last_name,a.first_name';
	$results=$wpdb->get_results( $sql);

	if(!empty( $results) )
	{//not empty

		switch( $nation)
		{
			case'Australia': echo church_admin_australia_safeguarding_table( $results);break;
			case'United Kingdom': echo church_admin_uk_safeguarding_table( $results);break;
			default:echo church_admin_australia_safeguarding_table( $results);break;
		}

	}//end not empty
	else
	{//no results
		echo '<p>'.esc_html( __('No people in safeguarding required ministries yet.','church-admin' ) ).'</p>';
	}//end no results

	return $out;
}
/**
 * Returns people table for United Kingdom
 *
 * @return $out array
 *
 */
function church_admin_uk_safeguarding_table( $results)
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
		$CAministries=church_admin_ministries();
		$out='<table class="widefat striped wp-list-table">
		<thead>
			<tr>
				
				<th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th>
				<th>Edit</th>
				<th>'.esc_html( __('Position','church-admin' ) ).'</th>
				<th>'.esc_html( __('Volunteer/Paid','church-admin' ) ).'</th>
				<th>'.esc_html( __('Start Date','church-admin' ) ).'</th>
				<th>'.esc_html( __('Application form','church-admin' ) ).'</th>
				<th>'.esc_html( __('Reference 1','church-admin' ) ).'</th>
				<th>'.esc_html( __('Reference 2','church-admin' ) ).'</th>
				<th>'.esc_html( __('Interview','church-admin' ) ).'</th>
				<th>'.esc_html( __('DBS Status and Action','church-admin' ) ).'</th>
				<th>'.'DBS Number'.'</th>
				<th>'.'DBS Date?'.'</th>
				<th>'.esc_html( __('Review Date','church-admin' ) ).'</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				
				<th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th>
				<th>Edit</th>
				<th>'.esc_html( __('Position','church-admin' ) ).'</th>
				<th>'.esc_html( __('Volunteer/Paid','church-admin' ) ).'</th>
				<th>'.esc_html( __('Start Date','church-admin' ) ).'</th>
				<th>'.esc_html( __('Application form','church-admin' ) ).'</th>
				<th>'.esc_html( __('Reference 1','church-admin' ) ).'</th>
				<th>'.esc_html( __('Reference 2','church-admin' ) ).'</th>
				<th>'.esc_html( __('Interview','church-admin' ) ).'</th>
				<th>'.esc_html( __('DBS Status and Action','church-admin' ) ).'</th>
				<th>'.'DBS Number'.'</th>
				<th>'.'DBS Date?'.'</th>
				<th>'.esc_html( __('Review Date','church-admin' ) ).'</th>
			</tr>
		</foot>
		<tbody>';
		foreach( $results AS $row)
		{//build table
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_safeguarding&amp;people_id='.(int)$row->people_id,'edit_safeguarding').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
			$ministries=unserialize( $row->department_id);
			$mins=array();
			foreach( $ministries AS $key=>$ministry_id) if(!empty( $CAministries[$ministry_id] ) )$mins[]=$CAministries[$ministry_id];
			$class='';
			if(!$row->active)$class=' style="color:#CCC!important;" ';
			$out.= '
			<tr '.$class.'>
			<td '.$class.' class="ca-names column-primary" data-colname="'.esc_html( __('Name','church-admin' ) ).'">'.esc_html( $row->name).'<button type="button" class="toggle-row">
			<span class="screen-reader-text">show details</span>
		</button></td><td '.$class.' data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
				
				<td '.$class.' data-colname="'.esc_html( __('Position','church-admin' ) ).'">'.esc_html(implode(", ",$mins) ).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Employment status','church-admin' ) ).'">'.esc_html( $row->employment_status).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Start date','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->start_date).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Application form','church-admin' ) ).'">'.esc_html( $row->application_form).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Reference 1','church-admin' ) ).'">'.esc_html( $row->reference1).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Reference 2','church-admin' ) ).'">'.esc_html( $row->reference2).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Interview','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->interview).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Status','church-admin' ) ).'">'.esc_html( $row->status).'</td>
				<td '.$class.' data-colname="'.esc_html( __('DBS','church-admin' ) ).'">'.esc_html( $row->DBS).'</td>
				<td '.$class.' data-colname="'.esc_html( __('DBS date','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->DBS_date).'</td>
				<td '.$class.' data-colname="'.esc_html( __('Review date','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->review_date).'</td>
			</tr>';
		}//build table
		$out.= '</tbody></table>';

		return $out;
}

/**
 * Returns people table for Australia
 *
 * @return $out array
 *
 */
function church_admin_australia_safeguarding_table( $results)
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin')) ).'</h2></div>';
		return;
	}
		$CAministries=church_admin_ministries();
		$out='<table class="widefat striped">
		<thead>
			<tr>
				<th>Edit</th>
				<th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th>
				<th>'.esc_html( __('Position','church-admin' ) ).'</th>
				<th>'.esc_html( __('Volunteer/Paid','church-admin' ) ).'</th>
				<th>'.esc_html( __('Start Date','church-admin' ) ).'</th>
				<th>'.'CRW category'.'</th>
				<th>'.esc_html( __('Exemption Applied - Why?','church-admin' ) ).'</th>
				<th>'.esc_html( __('Status and Action','church-admin' ) ).'</th>
				<th>'.esc_html( __('Receipt number (if appl.)','church-admin' ) ).'</th>
				<th>'.'WWC Card Number'.'</th>
				<th>'.esc_html( __('Expiry Date','church-admin' ) ).'</th>
				<th>'.esc_html( __('Review Date','church-admin' ) ).'</th>
				<th>'.esc_html( __('Validation Dates','church-admin' ) ).'</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Edit</th>
				<th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th>
				<th>'.esc_html( __('Position','church-admin' ) ).'</th>
				<th>'.esc_html( __('Volunteer/Paid','church-admin' ) ).'</th>
				<th>'.esc_html( __('Start Date','church-admin' ) ).'</th>
				<th>'.'CRW category'.'</th>
				<th>'.esc_html( __('Exemption Applied - Why?','church-admin' ) ).'</th>
				<th>'.esc_html( __('Status and Action','church-admin' ) ).'</th>
				<th>'.esc_html( __('Receipt number (if appl.)','church-admin' ) ).'</th>
				<th>'.'WWC Card Number'.'</th>
				<th>'.esc_html( __('Expiry Date','church-admin' ) ).'</th>
				<th>'.esc_html( __('Review Date','church-admin' ) ).'</th>
				<th>'.esc_html( __('Validation Dates','church-admin' ) ).'</th>
			</tr>
		</foot>
		<tbody>';
		foreach( $results AS $row)
		{//build table
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_safeguarding&amp;people_id='.(int)$row->people_id,'edit_safeguarding').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
			$ministries=unserialize( $row->department_id);
			$mins=array();
			foreach( $ministries AS $key=>$ministry_id) $mins[]=$CAministries[$ministry_id];
			$class='';
			if(!$row->active)$class=' style="color:#CCC;" ';
			echo '
			<tr '.$class.'>
				<td  class="column-primary" data-colname="Name">'.esc_html( $row->name).'<button type="button" class="toggle-row">
                <span class="screen-reader-text">show details</span>
            </button></td>
				<td data-colname="Edit">'.$edit.'</td>
				<td data-colname="position">'.esc_html(implode(", ",$mins) ).'</td>
				<td data-colname="Employment status">'.esc_html( $row->employment_status).'</td>
				<td data-colname="Start date">'.mysql2date(get_option('date_format'),$row->start_date).'</td>
				<td  data-colname="CRW category">'.esc_html( $row->CRW_cat).'</td>
				<td data-colname="Exemptions">'.esc_html( $row->exemptions).'</td>
				<td data-colname="Status">'.esc_html( $row->status).'</td>
				<td data-colname="Receipt No.">'.esc_html( $row->receipt).'</td>
				<td> data-colname="WWC Card No."'.esc_html( $row->WWC_card).'</td>
				<td data-colname="WWC Expiry">'.mysql2date(get_option('date_format'),$row->WWC_expiry).'</td>
				<td data-colname="Review date">'.mysql2date(get_option('date_format'),$row->review_date).'</td>
				<td data-colname="Validation dates">'.mysql2date(get_option('date_format'),$row->validation_date).'</td>
			</tr>';
		}//build table
		echo '</tbody></table>';

		return $out;
}






function church_admin_edit_safeguarding( $people_id)
{
	
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}global $wpdb;
	//$wpdb->show_errors;
	$nation=get_option('church_admin_safeguarding_nation');

	switch( $nation)
	{
		case 'Australia':
			$status=array('Holds current WWC Card','Application lodged','Interim negative notice','Negative notice','Application Withdrawn');
		break;
		case 'United Kingdom':
			$status=array('DBS Applied for','DBS Clear','DBS not clear');
		break;
	}
	$ministries=church_admin_ministries();
	$safeguardedMinistries=church_admin_safeguarded_ministries();

	$out='';
	switch( $nation)
	{
	 	case 'Australia':

	 		$fields=array(

	 			'department_id'=>esc_html( __('Ministry','church_admin')),
	 			'employment_status'=>esc_html( __('Employment Status','church-admin' ) ),
	 			'start_date'=>esc_html( __('Start date','church-admin' ) ),
	 			'CRW_cat'=>'CRW Cat',
	 			'exemptions'=>esc_html( __('Exemptions','church-admin' ) ),
	 			'status'=>esc_html( __('Status and Action','church-admin' ) ),
	 			'receipt'=>esc_html( __('Receipt','church-admin' ) ),
	 			'WWC_card'=>'WWC Card',
	 			'WWC_expiry'=>'WWC Expiry',
	 			'review_date'=>esc_html( __('Review Date','church-admin' ) ),
	 			'validation_date'=>esc_html( __('Validation Date','church-admin' ) ),
	 		);
	 	break;
	 	case 'United Kingdom':

	 		$fields=array(

	 			'department_id'=>esc_html( __('Ministry','church_admin')),
	 			'employment_status'=>esc_html( __('Employment Status','church-admin' ) ),
	 			'start_date'=>esc_html( __('Start date','church-admin' ) ),

	 			'application_form'=>esc_html( __('Application Form','church-admin' ) ),
				'reference1'=>esc_html( __('Reference 1','church-admin' ) ),
	 			'reference2'=>esc_html( __('Reference 2','church-admin' ) ),
				'interview'=>esc_html( __('Interview','church-admin' ) ),
				'status'=>esc_html( __('DBS Status & Action','church-admin' ) ),
	 			'DBS'=>'DBS Number',
	 			'DBS_date'=>'DBS date',
	 			'review_date'=>esc_html( __('Review Date','church-admin'))
			);
		break;
	}

	if(!empty( $_POST['save-person'] ) )
	{//process

		$where=array('people_id'=>$people_id);
		$data=array();
		//echo '<pre>'.print_r( $_POST,TRUE).'</pre>';
		foreach( $fields AS $col=>$title)
		{
			if(!empty( $_POST[$col] ) )
			{
				if( $col=='department_id')
				{
					//delete all safeguarded ministries for that person
					foreach( $safeguardedMinistries AS $key=>$ID)church_admin_delete_people_meta( $ID,$people_id,'ministry');
					$dep=array();
					$dep_id = !empty($_POST['department_id'])?church_admin_sanitize($_POST['department_id']):array();
					foreach( $dep_id  AS $key=>$value)
					{
						$dep[]=$value;

						//re-add the saved ones!
						if(!empty( $value) )  {church_admin_update_people_meta( $value,$people_id,'ministry');}
					}
					$data['department_id']=serialize( $dep);//serialised array value for department_id field
				}

				else $data[$col]=sanitize_text_field(stripslashes( $_POST[$col] ) );
			}
		}

		$wpdb->update( $wpdb->prefix.'church_admin_safeguarding', $data, $where, '%s', NULL );
		echo '<div class="notice notice-success inline">'.esc_html( __('Record Updated','church-admin' ) ).'</div>';
		echo church_admin_safeguarding_old_style_list();
	}//end process
	else
	{
		$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_safeguarding b WHERE b.people_id=a.people_id AND a.people_id="'.(int)$people_id.'"';
		$row=$wpdb->get_row( $sql);
		echo '<h2>'.esc_html( __('Edit "Working With Childen" data for ','church-admin' ) ).esc_html( $row->name).'</h2>';
		echo '<form action="" method="POST"><table class="form-table">';
		foreach( $fields AS $col=>$title)
		{
			if( $col!='people_id')
			{
				echo '<tr><th scope="row">'.$title.'</th><td>';
				//handle different types of fields
				switch( $col)
				{
					case 'start_date':
					case 'WWC_expiry':
					case 'review_date':
					case 'validation_date':
					case 'DBS_date':
					case 'interview':
						echo church_admin_date_picker( $row->$col,$col,FALSE,date('Y',strtotime('-20 years') ),date('Y',strtotime('+20 years') ),sanitize_title( $col),sanitize_title( $col) );
					break;
					case 'employment_status':
						echo '<p><input type="radio" name="employment_status" value="Volunteer" ';
						if(!empty( $row->employment_status)&&$row->employment_status==__('Volunteer','church_admin') )echo ' checked="checked" ';
						echo '/> <label>'.esc_html( __('Volunteer','church-admin' ) ).'</label></p>';
						echo '<p><input type="radio" name="employment_status" value="Paid" ';
						if(!empty( $row->employment_status)&&$row->employment_status==__('Paid','church_admin') )echo ' checked="checked" ';
						echo '/> <label>'.esc_html( __('Paid','church-admin' ) ).'</label></p>';
					break;
					case 'status':
						foreach( $status AS $key=>$value)
						{
							echo '<p><input type="radio" name="status" value="'.esc_html( $value).'"';
							if(!empty( $row->status)&&$row->status==$value)echo ' checked="checked" ';
							echo '/> <label>'.esc_html( $value).'</label></p>';
						}
					break;
					case 'department_id':
						$department_id=maybe_unserialize( $row->department_id);

						$safe_ministries=church_admin_safeguarded_ministries();

						foreach( $safe_ministries AS $key=>$id)
						{
							echo '<p><input type="checkbox" name="department_id[]" value="'.(int)$id.'"';
							if(in_array( $id,$department_id) )echo ' checked="checked" ';
							echo '/> <label>'.$ministries[$id].'</label></p>';
						}
					break;
					default:
						echo '<input type="text" name="'.$col.'" ';
						if(!empty( $row->$col) )echo ' value="'.esc_html( $row->$col).'" ';
						echo '/>';
					break;

				}
				echo '</td></tr>';
			}
		}
		echo '<tr><td colspacing=2><input type="hidden" name="save-person" value=yes/><input type="submit" class="button-primary" name="submit" value="'.esc_html( __('Save','church-admin' ) ).'" />';
		echo '</table></form>';
	}
	echo $out;
}

function church_admin_kidswork_PDF()
{
    
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
	
	echo'<h2>'.esc_html( __('Kids Work PDF','church-admin' ) ).'</h2>';

	echo '<form name="kidswork_form" action="'.home_url().'" method="get"><input type="hidden" name="ca_download" value="kidswork_pdf" />';
	echo'<table class="form-table">';
	$member_type=church_admin_member_types_array();
	foreach( $member_type AS $key=>$value)
	{
		echo'<tr><th scope="row">'.esc_html( $value).'</th><td><input type="checkbox" value="'.esc_html( $key).'" name="member_type_id[]" /></td></tr>';
	}

	echo '<tr><td colspacing=2>'.wp_nonce_field('kidswork').'<input type="submit" class="button-primary" value="'.esc_html( __('Download','church-admin' ) ).'" /></td></tr></table></form>';
	
}


function church_admin_kidswork_checkin_PDF()
{
    	global $wpdb;
		if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
	/********************************************************
	* 
	* Kidswork Check in PDF form
	*
	*********************************************************/
	echo '<h2>'.esc_html( __('Download a kids work checkin PDF ','church-admin' ) ).'</h2>';
	
	echo  '<form name="kidswork_form" action="'.home_url().'" method="get"><input type="hidden" name="ca_download" value="kidswork-checkin" />';
	echo '<table class="form-table">';
	$kidsworkGroups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork ORDER BY youngest ASC');
	foreach( $kidsworkGroups AS $row)
	{
		echo '<tr><th scope="row">'.esc_html( $row->group_name).'</th><td><input type="checkbox" value="'.esc_attr( $row->id).'" name="id[]" /></td></tr>';
	}
	$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
	if(!empty( $services) )
	{
		echo '<tr><th scope="row">'.esc_html( __('Service',"church-admin")).'</th><td><select name="service_id">';
		foreach( $services AS $service)
		{
			echo '<option value="'.(int)$service->service_id.'">'.esc_html( $service->service_name).'</option>';
		}
		echo '</select></td></tr>';
	}
	echo '<tr><th scope="row">'.esc_html( __('Meeting Date','church-admin' ) ).'</th><td>'.church_admin_date_picker(date('Y-m-d'),'date',FALSE,date('Y-m-d'),NULL,NULL,NULL).'</td></tr>';
	echo  '<tr><td colspacing=2>'.wp_nonce_field('kidswork-checkin').'<input type="submit" class="button-primary" value="'.esc_html( __('Download','church-admin' ) ).'" /></td></tr></table></form>';
}