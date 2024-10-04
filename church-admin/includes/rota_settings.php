<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




 /**
 *
 * Delete rota settings
 *
 * @author  Andy Moyle
 * @param    $id
 * @return   html
 * @version  0.1
 *
 */
function church_admin_delete_rota_settings( $id)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".esc_sql((int)$id)."'");
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota  WHERE rota_task_id="'.(int)$id.'"');
    church_admin_rota_settings_list();
}


 /**
 *
 * church_admin_edit_rota_settings
 *
 * @author  Andy Moyle
 * @param    $id
 * @return   html
 * @version  0.1
 *
 */
function church_admin_edit_rota_settings( $id=NULL)
{
	church_admin_debug('***** church_admin_edit_rota_settings *****');
	global $wpdb,$departments;
	$ministries=church_admin_ministries();
	$wpdb->show_errors();
	//set a rota order
	$rota_order=$wpdb->get_var('SELECT MAX(rota_order) FROM '.$wpdb->prefix.'church_admin_rota_settings')+1;
	if ( empty( $rota_order) )$rota_order=1;
	if(!empty( $_POST['rota_order'] ) )$rota_order=(int)$_POST['rota_order'];


	if(isset( $_POST['rota_task'] ))
	{

    	$rota_task=sanitize_text_field( stripslashes( $_POST['rota_task']  ) );
		$services=array();

		if(!empty( $_POST['service_id'] ) )  {
			$service_ids = church_admin_sanitize($_POST['service_id']);
			foreach( $service_ids AS $key=>$value)  {
				$services[]=(int)$value;
			}
		}else
			{
				$services=array('0'=>'1');
			}

		if(!empty( $_POST['initials'] ) )  {$initials=1;}else{$initials=0;}
		$ministry=array();
		$ministry_id=!empty( $_POST['ministry_id'] )?(int)$_POST['ministry_id']:NULL;
		if(!empty( $_POST['rota_order'] ) )$rota_order=(int)$_POST['rota_order'];
		//check for new ministry
		if(!empty( $_POST['ministry'] ) )
		{
			$ministry=church_admin_sanitize($_POST['ministry'] ) ;
			$ministries=church_admin_ministries();
			if(!in_array( $ministry,$ministries) )
			{
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry) VALUES("'.esc_sql($ministry).'")');
				$ministry_id=$wpdb->insert_id;
			}
			else
			{
				$ministry_id=array_search( $ministry,$ministries);
			}
		}
		//sort out ministry names
		if(!empty( $_POST['people'] ) )
		{
			church_admin_debug('handle people');
			church_admin_debug( print_r($_POST['people'],TRUE) );
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="ministry" AND ID="'.(int)$ministry_id.'"');
			church_admin_debug( $wpdb->last_query);
			$peoples_id=church_admin_get_people_ids(church_admin_sanitize( $_POST['people'] )) ;
			
			if(!empty( $peoples_id)  ) 
			{
				foreach( $peoples_id AS $key=>$people_id)
				{
					$sql='INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,ID,meta_type,meta_date)VALUES("'.(int)$people_id.'","'.(int) $ministry_id.'","ministry","'.esc_sql(wp_date('Y-m-d')).'")';
					$wpdb->query( $sql);
					church_admin_debug( $sql);
				}
			}
		}
		$calendar = !empty($_POST['calendar'])?1:0;
		
    	if(!$id)
    	{//insert
        	$id=$wpdb->get_var('SELECT rota_id FROM '.$wpdb->prefix.'church_admin_rota_settings WHERE rota_task="'.esc_sql($rota_task).'"' );
        	if(!$id)
        	{
				

            	$sql='INSERT INTO '.$wpdb->prefix.'church_admin_rota_settings (rota_task,initials,rota_order,service_id,ministries,calendar) VALUES("'.$rota_task.'","'.$initials.'","'.(int)$rota_order.'","'.esc_sql(serialize( $services) ).'","'.esc_sql( $ministry_id).'","'.$calendar.'")';

            	$wpdb->query( $sql);
            	$job_id=$wpdb->insert_id;


            	if(!empty( $job_id) )  {echo'<div id="message" class="notice notice-success inline"><h2>'.esc_html( __('Schedule Job Added','church-admin' ) ).'</h2></div>';}else{{echo'<div id="message" class="notice notice-success inline"><h2>'.esc_html( __('Schedule Job failed to save','church-admin' ) ).'</h2></div>';}}
            	church_admin_rota_settings_list();
        	}else
        	{
            	$sql='UPDATE '.$wpdb->prefix.'church_admin_rota_settings SET rota_order="'.(int)$rota_order.'",rota_task="'.esc_sql(sanitize_text_field( stripslashes($_POST['rota_task'] ) ) ).'",service_id="'.esc_sql(serialize( $services) ).'",calendar = "'.$calendar.'",initials="'.esc_sql($initials).'", ministries="'.(int)$ministry_id.'" WHERE rota_id="'.(int)$id.'"';

           	 $wpdb->query( $sql);
            	echo'<div id="message" class="notice notice-success inline"><p><strong>'.esc_html( __('Schedule Job Updated','church-admin' ) ).'</strong></p></div>';

            	church_admin_rota_settings_list();
        	}
    	}//insert
    	else
    	{//update
        	$sql='UPDATE '.$wpdb->prefix.'church_admin_rota_settings SET rota_order="'.(int)$rota_order.'",rota_task="'.esc_sql(sanitize_text_field( $_POST['rota_task'] ) ).'",service_id="'.esc_sql(serialize( $services) ).'",initials="'.$initials.'",ministries="'.(int)$ministry_id.'" WHERE rota_id="'.(int)$id.'"';

        	$wpdb->query( $sql);
        	echo'<div id="message" class="notice notice-success inline"><p><strong>'.esc_html( __('Schedule Job Updated','church-admin' ) ).'</strong></p></div>';

        	church_admin_rota_settings_list();
   	 	}//update
	}
	else
	{
		echo'<h2>'.esc_html( __('Set up Schedules','church-admin' ) ).'</h2><h2>'.esc_html( __('Edit a Schedule Job','church-admin' ) ).'</h2><form action="" method="post">';
		 wp_nonce_field('edit-rota-job');
		$rota_task=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".(int)$id."'");
		echo'<table class="form-table"><tbody><tr><th scope="row">'.esc_html( __('Schedule Job','church-admin' ) ).':</th><td><input type="text" name="rota_task" ';
		if(!empty( $rota_task->rota_task) ) echo'value="'.esc_html( $rota_task->rota_task).'"';
		echo'/></td></tr>';

		echo'<tr><th scope="row">'.esc_html( __('Enable use initials','church-admin' ) ).'</th><td><input type="checkbox" name="initials" value="1"';
		if(!empty( $rota_task->initials)&&$rota_task->initials>0) echo' checked="checked" ';
		echo'/></td></tr>';
		$rota_order=(!empty( $rota_task->rota_order) )?(int)$rota_task->rota_order:$rota_order;//value from above if not set
		echo'<tr><th scope="row">'.esc_html( __('Schedule order','church-admin' ) ).'</th><td><input type="number" name="rota_order" value="'.(int)$rota_order.'" /></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Which Services need this task?','church-admin' ) ).'</th><td>';
		if(!empty( $rota_task->service_id) )$current_services=unserialize( $rota_task->service_id);
		$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
		if(!empty( $services) )
		{
			$ser=array();
			foreach( $services AS $service)
			{
				echo'<input type="checkbox" name="service_id[]" value="'.(int)$service->service_id.'" ';
				if(count( $services)==1 || !empty( $current_services)&&!empty( $service->service_id)&&is_array( $current_services) && (in_array( $service->service_id,$current_services) )) echo' checked="checked" ';
				echo'/>'.esc_html( $service->service_name).'<br>';
			}
		}
		echo'</td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Show on calendar service item','church-admin' ) ).'?</td><td><input type="checkbox" name="calendar" value=1';
        if(!empty( $rota_task->calendar) ) echo' checked="checked" ';
        echo'/></td></tr>';
		//which ministries make up this rota job
		echo'<tr><th colspan=2>'.esc_html( __('For speedy scheduling, a ministry can be assigned to a schedule task. The people in that ministry can then be quickly added to a schedule by checking their checkbox or selecting them.','church-admin' ) ).'</th></tr>';
		
		if(!empty( $ministries) )
		{
			echo '<tr><th scope="row">'.esc_html( __('Choose which ministries do this job','church-admin' ) ).'</th><td><select id="ministry_id" name="ministry_id"><option id="no-ministry">'.esc_html( __('Pick a ministry','church-admin' ) ).'</option>';
		
		
			/******************************************************
			 * Only one ministry id now allowed for each rota task
			 ******************************************************/
			$people=NULL;
			if(!empty( $rota_task->ministries) )
			{
				$currentMinistryId=(int)$rota_task->ministries;
				
				$people=church_admin_get_people_meta_array('ministry',$currentMinistryId);
			}

			if(!empty( $ministries) )
			{
				foreach( $ministries AS $id=>$ministry)
				{
					echo'<option  value="'.(int)$id.'"';
					if(!empty( $currentMinistryId) ) selected( $id,$currentMinistryId);
					echo'>';
					echo esc_html( $ministry).'</option>';
				}
				echo '</select></td></tr>';
			}
			echo '<tr><th scope="row">'.esc_html( __('Or create a new ministry to do this rota task','church-admin' ) ).'</th><td><input type="text" name="ministry" id="add-ministry" /></td></tr>';
		
		}
		else
		{
			//no current ministries
			echo '<tr><th scope="row">'.esc_html( __('Create a new ministry to do this rota task','church-admin' ) ).'</th><td><input type="text" name="ministry" id="add-ministry" /></td></tr>';
		
		}
		/*******************************
		 * Show people in that ministry
		 *******************************/
		echo'<tr><th scope="row">'.esc_html( __('People in ministry')).'</th><td>';
		if ( empty( $people) )$people=array();	
		echo  church_admin_autocomplete('people','friends','to',$people,FALSE);
		echo'</td></tr>';
		echo'<tr><th scope="row"><input type="submit" name="edit_rota_setting" value="'.esc_html( __('Save Schedule Job','church-admin' ) ).' &raquo;" class="button-primary" /></td></tr></table></form>';

		echo'<script>jQuery(document).ready(function( $)  {
			$("#add-ministry").focusout(function()  {
				$("#ministry_id").val("");
				$("#no-ministry").attr("selected","selected");
			});
		});</script>';
	}
	church_admin_debug('***** END church_admin_edit_rota_settings *****');
}
 /**
 *
 * church_admin-_rota_settings_list
 *
 * @author  Andy Moyle
 * @param
 * @return   html
 * @version  0.1
 *
 */
function church_admin_rota_settings_list()
{
    //outputs the list of rota jobs
	global $wpdb;

	/****************
	* Handle change job order
	*/
	if(!empty( $_POST['change_rota_job_order'] ) )
	{
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_rota_settings SET rota_order = "'.(int)$_POST['rota_order'].'" WHERE rota_id="'.(int)$_POST['change_rota_job_order'].'"');
	}

	$allMinistries=church_admin_ministries();
	echo '<h2>'.esc_html( __('Schedule Jobs','church-admin' ) ).'</h2>';
	echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=edit-rota-job&section=rota",'edit-rota-job').'" class="button-primary">'.esc_html( __('Add a schedule job','church-admin' ) ).'</a></p>';

	$rota_results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order ASC');
	if(!empty( $rota_results) )
	{
		$numberRows=$wpdb->num_rows;
		$theader='<tr><th class="column-primary">'.esc_html( __('Rota Task','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Which Services?','church-admin' ) ).'</th><th>'.esc_html( __('Initials?','church-admin' ) ).'</th><th>'.esc_html( __('Ministries','church-admin' ) ).'</th><th>'.esc_html( __('Show in calendar','church-admin' ) ).'</th></tr>';
		echo '<table id="sortable" class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tfoot>'.$theader.'</tfoot><tbody  class="content">';
		foreach( $rota_results AS $rota_row)
		{
			$rota_edit_url=wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-rota-job&id='.$rota_row->rota_id,'edit-rota-job');
			$rota_delete_url=wp_nonce_url('admin.php?page=church_admin/index.php&action=delete-rota-job&id='.$rota_row->rota_id,'delete-rota-job');

			if(!empty( $rota_row->initials) )  {$initials=__('Yes','church-admin');}else{$initials=__('No','church-admin');}
			//services
			$ser=array();
			$services=maybe_unserialize( $rota_row->service_id);
			foreach( $services AS $key=>$value)  {$ser[]=$wpdb->get_var('SELECT service_name FROM '.$wpdb->prefix.'church_admin_services' .' WHERE service_id="'.esc_sql( $value).'"');}
			//ministries
			$ministry=!empty( $rota_row->ministries)?$allMinistries[$rota_row->ministries]:'';
			
			
			
			$calendar=!empty($rota_row->calendar)?__('Yes','church-admin'):__('No','church-admin');

			echo '<tr class="sortable" id="'.(int)$rota_row->rota_id.'">
				<td data-colname="'.esc_html( __('Rota Task','church-admin' ) ).'" class="column-primary">'.esc_html(church_admin_sanitize( $rota_row->rota_task) ).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>	
				<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'" ><a href="'.wp_nonce_url( $rota_edit_url, 'edit-rota-job').'">Edit</a></td>
				<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'" ><a href="'.wp_nonce_url(        $rota_delete_url, 'delete-rota-job').'">Delete</a></td>
				
				<td data-colname="'.esc_html( __('Which services?','church-admin' ) ).'" >'.implode('<br>',$ser).'</td>
				<td data-colname="'.esc_html( __('Enable use initials','church-admin' ) ).'" >'.esc_html( $initials).'</td>
				<td data-colname="'.esc_html( __('Ministries','church-admin' ) ).'" >'.esc_html( $ministry).'</td>
				<td data-colname="'.esc_html( __('Show in calendar','church-admin' ) ).'" >'.esc_html( $calendar).'</td>
			</tr>';
		}
		echo'</tbody></table>';

		echo' <script type="text/javascript">

		jQuery(document).ready(function( $) {
	
		var fixHelper = function(e,ui)  {
				ui.children().each(function() {
					$(this).width( $(this).width() );
				});
				return ui;
			};
		var sortable = $("#sortable tbody.content").sortable({
		helper: fixHelper,
		stop: function(event, ui) {
			//create an array with the new order
	
	
					var Order = "order="+$(this).sortable(\'toArray\').toString();
	
	
	
			$.ajax({
				url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=rota_settings",
				type: "post",
				data:  Order,
				error: function() {
					console.log("theres an error with AJAX");
				},
				success: function() {
	
				}
			});}
		});
		$("#sortable tbody.content").disableSelection();
		});
	
	
	
			</script>';
		
	}
}
