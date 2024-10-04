<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_smallgroup_PDF_form()
{
    global $member_types;
    echo'<h2>'.esc_html( __('Small Group PDF','church-admin' ) ).'</h2><form name="smallgroup_form" action="'.home_url().'" method="get"><table class="form-table"><input type="hidden" name="ca_download" value="smallgroup" />';
    echo'<tr><th Scope="row">'.esc_html( __('PDF Title','church-admin' ) ).'</th><td><input type="text" name="title" /></td></tr>';
	echo'<tr><th scope="row">'.esc_html( __('Age Range','church-admin' ) ).'</th><td>';
    $people_type=get_option('church_admin_people_type');
    foreach( $people_type AS $key=>$value)
    {
        echo'<input type="checkbox" name="people_type_id[]" value="'.(int)$key.'" />'.esc_html( $value).'<br>';
     }
     echo'</td></tr>'."\r\n";
     echo'<tr><th scope="row">'.esc_html( __('Member types to include','church-admin' ) ).'</th><td>';
     foreach( $member_types AS $key=>$value)
     {
         echo'<input type="checkbox" value="'.esc_html( $key).'" name="member_type_id[]" />'.esc_html( $value).'<br>';
     }
     echo'</td></tr>';
    echo '<tr><td colspacing=2>'.wp_nonce_field('smallgroup').'<input type="submit" class="button-primary" value="'.esc_html( __('Download','church-admin' ) ).'" /></td></tr></table></form>';
}
/**
 *
 * Outputs small group structure
 *
 * @author  Andy Moyle
 * @param
 * @return  html
 * @version  0.1
 *
 *
 *
 */
function church_admin_small_group_structure()
{
	global $wpdb;
	$out='<h2>'.esc_html( __('Small group oversight structure','church-admin' ) ).'</h2>';
	
	
	//get all parents
	$structure=church_admin_get_ministry_hierarchy(1);
	
	//add new ministry, oversight level
	if(!empty( $_POST['oversight_level'] ) )
	{
		$oversight_level = church_admin_sanitize($_POST['oversight_level'] );
		$check=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql( ).'"');
		if(!$check)
		{
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry) VALUES("'.esc_sql($oversight_level ).'")');
			$ID=$wpdb->insert_id;
			
			//not everyone has PHP 7.0 so get last key of $structure the old way!
			end( $structure );
            $last = key( $structure );
			
			if ( empty( $last) )$last=1;
			$sql='UPDATE '.$wpdb->prefix.'church_admin_ministries SET parentID="'.(int)$id.'" WHERE ID="'.intval( $last).'"';
			if(defined('CA_DEBUG') )church_admin_debug( $sql);
			$wpdb->query( $sql);
		}
		$structure=church_admin_get_ministry_hierarchy(1);
		
	}

	$out.='<form action="" method="POST"><p><input type="text" placeholder="'.esc_html( __('Add an oversight level','church-admin' ) ).'" class="large-text" name="oversight_level" /> <input type="submit" class="button-secondary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
	$out.='<p>'.esc_html( __('Drag and drop to set oversight order, each row is the parent of the one after.','church-admin' ) ).' ';
	if(!empty( $structure) )
	{
		$structure=array_reverse( $structure,TRUE);
		$out.='<table id="ministry-sortable" class="widefat striped"><tbody class="content ui-sortable">';
		foreach( $structure AS $ID=>$ministry)
		{
			$out.='<tr id="min'.(int)$ID.'"';
			$out.='><td >'.esc_html( $ministry).'</td></tr>';
		}
		$out.='</table>';
		$out.='<script>
			

 jQuery(document).ready(function( $) {

    var fixHelper = function(e,ui)  {
            ui.children().each(function() {
                $(this).width( $(this).width() );
            });
            return ui;
        };
    var minSortable = $("#ministry-sortable tbody.content").sortable({
    helper: fixHelper,
    stop: function(event, ui) {
        //create an array with the new order


				var order = $(this).sortable(\'toArray\').toString();
				console.log(order);
				var nonce="'.wp_create_nonce("ministry-parents").'";
		 		var data={"action":"church_admin","method":"ministry-parents","order":order,"nonce":nonce};


        $.ajax({
            url: ajaxurl,
            type: "post",
            data:  data,
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
		return $out;
}

function church_admin_oversight_list()
{
	global $wpdb;
	
	if(!empty( $_POST['oversight'] )&&!empty( $_POST['ministry_id'] ) )
	{
		
		$name=esc_sql(church_admin_sanitize( $_POST['oversight'] ) );
		$min_id=intval( $_POST['ministry_id'] );
		$parent_id=intval( $_POST['parentID'] );
		if ( empty( $parent_id) )$parent_id=NULL;
		$ID=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_cell_structure WHERE name="'.$name.'" AND ministry_id="'.$min_id.'"');
		if ( empty( $ID) )
		{
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_cell_structure(name,ministry_id,parent_id) VALUES("'.$name.'","'.$min_id.'","'.$parent_id.'")');
			$ID=$wpdb->insert_id;
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$id.'" AND meta_type="oversight"');
			if(!empty( $_POST['people'] ) )
			{
				$autocompleted=explode(',',church_admin_sanitize($_POST['people']) );//string with entered names

				foreach( $autocompleted AS $x=>$name)
				{
					$p_id=church_admin_get_one_id(trim( $name) );//get the people_id

					if(!empty( $p_id) )
					{
						church_admin_update_people_meta( $ID,$p_id,'oversight');//update person as leader at that level
					}
				}
			}
		}
	}
	$out='';
	$structure=church_admin_get_ministry_hierarchy(1);
	if(!empty( $structure) )$structure=array_reverse( $structure,TRUE);//parents first
	$parent_id=NULL;
	if(count( $structure)>1)
	{
		foreach( $structure AS $ID=>$ministry)
		{
			if( $ID!=1)//don't do this for small group leader
			{
				$out.='<h2>'.esc_html(sprintf(__('Details of %1$s','church-admin' ) ,$ministry) ).'</h2>';
				$out.='<form action="" method="POST"><p><input type="text" placeholder="'.esc_html( __('Add a','church-admin' ) ).' '.esc_html( $ministry).'" class="large-text" name="oversight" /><input type="hidden" name="ministry_id" value="'.(int)$ID.'" /></p>';
				if( $parent_id)
				{
					$parentMinistry=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.intval( $parent_id).'"');
					$out.='<p>Which parent "'.$parentMinistry->ministry.'"? <select name="parentID">';
					$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_cell_structure WHERE ministry_id="'.intval( $parentMinistry->ID).'"');
					if(!empty( $results) )
					{
						foreach( $results AS $row)  {$out.='<option value="'.(int)$row->ID.'">'.esc_html( $row->name).'</option>';}
					}
					$out.='</select></p>';
				}
				$out.='<p>'.church_admin_autocomplete('people','friends','to',array(),FALSE).'</p><input type="submit" class="button-secondary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
				$cells=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_cell_structure WHERE ministry_id="'.(int)$ID.'"');
			
				if(!empty( $cells) )
				{
					if ( empty( $parentMinistry) )	{$parentMinistry=new stdClass(); $parentMinistry->ministry='';}
					$out.='<table class="widefat striped"><thead><tr><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( $parentMinistry->ministry).'</th><th>'.esc_html( __('People','church-admin' ) ).'</th></tr></thead><tbody>';
					foreach( $cells AS $cell)
					{
						$delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_cell&amp;ID='.intval( $cell->ID),'delete_cell').'">'.esc_html( __('Delete ','church-admin' ) ).'</a>';
						$out.='<tr ><td>'.$delete.'</td><td><input class="cell" data-id="'.intval( $cell->ID).'" value="'.esc_html( $cell->name).'" /></td>';
						//parent
						$out.='<td>';
						if(!empty( $parent_id) )
						{
							
							$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_cell_structure WHERE ministry_id="'.intval( $parent_id).'"');
							
							if(!empty( $results) )
							{
								$out.='<select name="parentID">';
								$first=$output='';
								foreach( $results AS $row)
								{
									if( $row->ID==$parent_id)$first.='<option value="'.(int)$row->ID.'" selected="selected">'.esc_html( $row->name).'</option>';
									$output.='<option value="'.(int)$row->ID.'">'.esc_html( $row->name).'</option>';
								}
								$out.=$first.$output;
								$out.='</select></p>';
							}
						}
						else $out.='&nbsp;';
						$out.='</td>';
						
						$list=church_admin_get_people_meta_list('oversight',$cell->ID);
						$out.='<td><input class="people large-text" data-id="'.intval( $cell->ID).'" value="'.$list.'" /></td>';
						$out.='</tr>';
					}
					$out.='</tbody></table><hr/>';
				}
				$out.='<script>
			
				jQuery(document).ready(function( $) {
			
				$(".cell").on("change",function()  {
					console.log("cell changed")
					var id=$(this).data("id");
					var name=$(this).val();
					var nonce="'.wp_create_nonce("update-oversight").'";
		 			var data={"action":"church_admin","method":"update-oversight","cell_id":id,"name":name,"nonce":nonce};
		 			console.log(data);
					$.ajax({
            				url: ajaxurl,
            				type: "post",
            				data:  data,
            				error: function() {console.log("theres an error with AJAX");},
            				success: function() {console.log("Success");}
        			});
				});
				$(".people").on("change",function()  {
					console.log("people changed")
					var id=$(this).data("id");
					var people=$(this).val();
					var nonce="'.wp_create_nonce("update-oversight").'";
		 			var data={"action":"church_admin","method":"update-oversight","cell_id":id,"people":people,"nonce":nonce};
		 			console.log(data);
					$.ajax({
            				url: ajaxurl,
            				type: "post",
            				data:  data,
            				error: function() {console.log("theres an error with AJAX");},
            				success: function() {console.log("Success");}
        			});
				});
			});
			
			
			</script>';
			}
			$parent_id=$ID;
		}
	
	
	
	}
	return $out;


}


function church_admin_delete_cell( $ID)
{
	global $wpdb;
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_cell_structure WHERE ID="'.(int)$id.'"');
	church_admin_smallgroups_main();
}






/**
 *
 * Outputs small group list
 *
 * @author  Andy Moyle
 * @param
 * @return  html
 * @version  0.1
 *
 * 2016-11-07 restrict showing small groups to admins and people assigned to leadership hierarchy
 *
 */
//deprecated...
function church_admin_small_groups()
{
	//function to output small group list
	global $wpdb,$current_user,$people_type,$wp_locale,$member_types,$ministries;
	
    //clean up groups
    church_admin_groups_cleanup();
    
    
    $current_user=wp_get_current_user();
	
	
	
	$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
	$args=array();
	foreach( $_GET AS $key=>$value)  {$args[$key]=church_admin_sanitize($value);}



	$out='<h2>'.esc_html( __('Small groups','church-admin' ) ).'</h2>';
	//Add a small group
	$out.='<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&section=groups&amp;action=edit-small-group",'edit-small-group').'">'.esc_html( __('Add a small group','church-admin' ) ).'</a></p>';
    
	//unassigned people count
	$out.=church_admin_unassigned_count();
	
	if(!empty( $_GET['message'] ) )$out.='<div class="updated"><p>'.esc_html(urldecode( church_admin_sanitize($_GET['message']) ) ).'</p></div>';
	
	/*********************************************************************
	*
	*  map of small groups
	*
	**********************************************************************/
	$key=get_option('church_admin_google_api_key');
	if(!empty( $key) )
	{
		$row=$wpdb->get_row('SELECT lat,lng  FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE lat!="" AND lng!="" ORDER BY id DESC LIMIT 1' );
		if(!empty( $row) )
		{
			$lat=esc_html( $row->lat);
			$lng=esc_html( $row->lng);
		}	
		else {$lat=38.8977; $lng=77.0365;}
		$out.='<script type="text/javascript">var xml_url="'.site_url().'/?ca_download=small-group-xml&small-group-xml='.wp_create_nonce('small-group-xml').'";';
			$out.=' var lat='.$lat.';';
			$out.=' var lng='.$lng.';';
			$out.=' var zoom=13;';
			$out.='jQuery(document).ready(function()  {sgload(lat,lng,xml_url,zoom);});</script><div id="map" class="ca-small-group-map"></div><div id="groups" ></div><div class="clear"></div>';
		
	}
	else $out.='<p><a href="a'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=settings&section=settings','settings').'">'.esc_html( __('Add a Google Maps API key (under General settings) to show small group map','church-admin' ) ).'</a></p>';
		$out.="\r\n";
	//list


	//table of groups
		$out.=__('Drag and Drop to change row display order','church-admin');
		$out.='<table  id="sortable" class="widefat striped"><thead><tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Group Name','church-admin' ) ).'</th><th>'.esc_html( __('Leaders','church-admin' ) ).'</th><th>'.esc_html( __('When','church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Group Name','church-admin' ) ).'</th><th>'.esc_html( __('Leaders','church-admin' ) ).'</th><th>'.esc_html( __('When','church-admin' ) ).'</th></tr></tfoot><tbody class="content">';
		//grab small group information
		$sg_sql = 'SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup ORDER BY smallgroup_order';
		$sg_results = $wpdb->get_results( $sg_sql);
		foreach ( $sg_results as $sg_row)
		{
			
			/******************************************************************************************
			*
			* Only leaders and oversight of a group and site admins can see the row for each small group
			*
			*******************************************************************************************/
			$leader_ids=array();//array of people_id for various levels of oversight
			$leaders_people=array();
			$show=FALSE;//default to not showing row
			if(current_user_can('manage_options') )$show=true;//admins can see
			if(!empty( $sg_row->oversight) )$oversight=maybe_unserialize( $sg_row->oversight);
			
			if(!empty( $oversight)&&is_array( $oversight) )
			{
				foreach( $oversight AS $key=>$oversight_id)
				{
					$overseersResults=$wpdb->get_results('SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.people_id,c.ministry_id, c.name AS oversight_name FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b, '.$wpdb->prefix.'church_admin_cell_structure c WHERE  b.ID=c.ID AND b.meta_type="oversight" AND c.ID="'.intval( $oversight_id).'" AND a.people_id=b.people_id');
					if(!empty( $overseersResults) )
					{
						foreach( $overseersResults AS $overseersRow)
						{
							$leaders_ids[]=$overseersRow->people_id;
							$leaders_people[$overseersRow->ministry_id]['oversight_team']=$overseersRow->oversight_name;
							$leaders_people[$overseersRow->ministry_id]['names'][]=$overseersRow->name;
						}
					}
				}
			}
			$ldrsResults=$wpdb->get_results('SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE b.meta_type="smallgroupleader" AND b.ID="'.intval( $sg_row->id).'" AND a.people_id=b.people_id');
			if(!empty( $ldrsResults) )
				{
					foreach( $ldrsResults AS $ldrsRow)
					{
						$leaders_ids[]=$ldrsRow->people_id;
						$leaders_people[1]['oversight_team']=NULL;
						$leaders_people[1]['names'][]=$ldrsRow->name;
					}
				}
		
			
			if(!empty( $leaders_ids)&&is_array( $leaders_ids)&&in_array( $people_id,$leaders_ids) )$show=TRUE;
			
			
			if( $show)
			{//only build row if user allowed to see it
				//build leaders
				$ldrs='';
				foreach( $leaders_people AS $key=>$leaders)
				{
					$ldrs.='<p><strong>'.$ministries[$key].'</strong><br>';
					if(!empty( $leaders['oversight_team'] ) )$ldrs.='<strong>'.$leaders['oversight_team'].':</strong> ';
					if(!empty( $leaders['names'] ) )$ldrs.=implode(", ",$leaders['names'] ).'</p>';
				}
    			

				$edit_url=wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-group&section=groups&amp;id='.(int)$sg_row->id,'edit-small-group');
				$delete_url=wp_nonce_url('admin.php?page=church_admin/index.php&action=delete-group&section=groups&amp;id='.(int)$sg_row->id,'delete-group');

        		if( $sg_row->id!=1)
				{
					if ( empty( $sg_row->group_day) )$sg_row->group_day=1;
				
					$out.='<tr class="sortable-row" id="'.$sg_row->id.'"><td><a href="'.$edit_url.'">'.esc_html( __('Edit','church-admin' ) ).'</a></td><td><a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.$delete_url.'">'.esc_html( __('Delete','church-admin' ) ).'</a></td><td class="ca-names"><a title="'.esc_html( __('Who is in this group?','church-admin' ) ).'" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=whosin&amp;id='.intval( $sg_row->id),'whosin').'">'.esc_html(sanitize_text_field( $sg_row->group_name) ).'</a></td><td class="ca-names">'.$ldrs.'</td><td>'.$wp_locale->get_weekday( $sg_row->group_day).' '.$sg_row->group_time.'</td></tr>';
				}
				else
				{
					if ( empty( $sg_row->group_day) )$sg_row->group_day=1;
					$out.='<tr class="sortable-row" id="'.intval( $sg_row->id).'"><td>&nbsp;</td><td>&nbsp;</td><td >'.esc_html(sanitize_text_field( $sg_row->group_name) ).'</td><td>&nbsp;</td><td>'.$wp_locale->get_weekday( $sg_row->group_day).' '.$sg_row->group_time.'</td></tr>';
       			}
       		}//only build row if user is allowed to see it
		}
		$out.="</tbody></table>";
	$out.= '
    <script type="text/javascript">

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
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=small_groups",
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



		</script>
	';

	return $out;
}
//end of small group information function







function church_admin_remove_from_smallgroup( $people_id,$ID)
{
	global $wpdb;
	$name=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,prefix,last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.esc_sql( $people_id).'"');
	if(!empty( $name) )
	{
		church_admin_delete_people_meta( $ID,$people_id,'smallgroup');
		church_admin_update_people_meta(1, $people_id,'smallgroup');
		echo'<div class="notice notice-success inline">'.$name.' '.esc_html( __('has been removed from group and put in unattached group','church-admin' ) ).'</div>';
	}
	church_admin_whosin( $ID);
}

function church_admin_whosin( $id)
{
	//2016-11-07 added ability to restrict to leaders over that group and admins

	global $wpdb,$current_user;
	wp_get_current_user();
	$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
	$attendance=array('1'=>'Regular','2'=>'Irregular','3'=>'Connected');

	$out='';
	$group=$wpdb->get_row('SELECT * FROM  '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.esc_sql((int)$id).'"');
	if(!empty( $group) )
	{
		$show=FALSE;//default no show
		$leaders=maybe_unserialize( $group->leadership);
		foreach( $leaders AS $leaderlevel) if(in_array( $people_id,$leaderlevel) )$show=TRUE;//allowed!
		if(current_user_can('manage_options') )$show=true;

		if( $show)
		{

			//group details
			$out.=sprintf( '<h2>%1$s %2$s %3$s</h2>', __( 'Who is in', 'church-admin' ),esc_html( $group->group_name),__('group','church-admin') );
			$out.='';
			$out.='<table class="form-table"><tbody>';
			$out.='<tr><th scope="row">'.esc_html( __('Leader(s)','church-admin' ) ).':</th><td>';
			$ldr='';
			$hierarchy=church_admin_get_hierarchy(1);
    		krsort( $hierarchy);//sort top level down
    		//who is currently leading
    		$curr_leaders=maybe_unserialize( $group->leadership);
    		//need titles of leaders levels
    		$ministries=church_admin_ministries(NULL);
    		foreach( $hierarchy AS $key=>$min_id)
    		{
    			$ldr.='<h3>'.$ministries[$min_id].'</h3><p>';//leader level name
    		if(!empty( $curr_leaders[$min_id] ) )  {
				foreach( $curr_leaders[$min_id] AS $k=>$people_id){
					$ldr.=esc_html(church_admin_get_person( $people_id) ).'<br>';
				}
			}else{
				$ldr.=esc_html(__('No leaders assigned yet','church-admin' ) ).'<br>';
				}
    			$ldr.='</p>';
			}
			$out.=$ldr;
			$out.='</td><td rowspan=3><img class="alignleft" src="http://maps.google.com/maps/api/staticmap?center='.esc_html( $group->lat).','.esc_html( $group->lng).'&zoom=13&markers='.esc_html( $group->lat).','.esc_html( $group->lng).'&size=200x200" /></td></tr>';
			$out.='<tr><th scope="row">'.esc_html( __('Meeting','church-admin' ) ).':</th><td>'.esc_html( $group->whenwhere).'</td></tr>';
			$out.='<tr><th scope="row">'.esc_html( __('Venue','church-admin' ) ).':</th><td>'.esc_html( $group->address).'</td></tr>';
			$out.='</tbody></table>';
			//grab group ids of people in group
			$sql='SELECT a.people_id,b.first_name,b.prefix,b.last_name,b.prefix,b.nickname,b.smallgroup_attendance,b.email,b.mobile FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.ID="'.esc_sql( $id).'" AND a. meta_type="smallgroup" AND a.people_id=b.people_id';
			$peopleresults = $wpdb->get_results( $sql);
			if(!empty( $peopleresults) )
			{


				$out.='<table class="widefat striped">';
				$out.='<thead><tr><th>'.esc_html( __('Remove from Group','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Attendance','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th><th>'.esc_html( __('Mobile','church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('Remove from Group','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Attendance','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th><th>'.esc_html( __('Mobile','church-admin' ) ).'</th></tr></tfoot><tbody>';
				foreach( $peopleresults AS $row)
				{

					//build name
					$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty( $middle_name)&&!empty( $row->middle_name) )$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty( $nickname)&&!empty( $row->nickname) )$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if( $prefix)	$name.=$row->prefix.' ';
					$name.=$row->last_name;
					$remove='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=remove_from_smallgroup&section=groups&amp;smallgroup_id='.$id.'&amp;people_id='.$row->people_id,'remove_from_smallgroup').'">'.esc_html( __('Remove','church-admin' ) ).'</a>';
						$out.='<tr><td>'.$remove.'</td><td class="ca-names">'.esc_html( $name).'</td><td>'.$attendance[$row->smallgroup_attendance].'</td><td class="ca-email"><a href="mailto:'.esc_html( $row->email).'">'.esc_html( $row->email).'</a></td><td class="ca-mobile"><a href="call:'.$row->mobile.'">'.esc_html( $row->mobile).'</td></tr>';

				}
				$out.='</tbody></table>';
			}
		}
		else{$out.=__('You are not allowed to see this group','church-admin');}
	echo $out;
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/comments.php');
	if( $show)church_admin_show_comments('smallgroup',$id);
	}

}

/**
 *
 * Delete all small groups
 *
 * @author  Andy Moyle
 * @param
 * @return  html
 * @version  0.1
 *
 *
 *
 */
function church_admin_delete_all_small_groups()
{
	global $wpdb;
	$wpdb->show_errors;
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id!="1"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="ministry" AND ID="1"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroupleader" ');
	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID="1", meta_date="'.date('Y-m-d').'" WHERE meta_type="smallgroup"');
	echo'<div class="wrap church_admin"><div id="message" class="notice notice-success inline"><p><strong>'.esc_html( __('Small Groups Deleted and people reset to unattached','church-admin' ) ).'</strong></p></div>';
}
/**
 *
 * Delete small group
 *
 * @author  Andy Moyle
 * @param    $id
 * @return
 * @version  0.1
 *
 *
 *
 */
function church_admin_delete_small_group( $id)
{
    global $wpdb;

	$sql='DELETE FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.esc_sql( (int)$_GET['id'] ).'"';
	$wpdb->query( $sql);
	$out='<div class="notice notice-success inline"><p><strong>'.esc_html( __('Small Group Deleted','church-admin' ) ).'</strong></p></div>';
	require_once(plugin_dir_path(dirname(__FILE__) ).'display/small-group-list.php');
	$out.='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-group&section=groups','edit-small-group').'">'.esc_html(__('Add a group','church-admin')).'</a></p>';
	$out.= church_admin_small_group_list(1,13,1,TRUE);
    return $out;
}


/**
 *
 * Edit small group
 *
 * @author  Andy Moyle
 * @param    $id
 * @return
 * @version  0.1
 *
 * v1.071 - changed leadership to the more efficient autocomplete
 *
 */

function church_admin_edit_small_group( $id=null)
{
    global $wpdb,$wp_locale;
    $out='';
	//current poeple in group
	if(!empty( $id) )  {$displayCurrentPeople=church_admin_get_people_meta_list('smallgroup',$id);}
	else{$displayCurrentPeople='';}




    $hierarchy=church_admin_get_hierarchy(1);//leadership hierarchy for small groups.
    $hierarchy=array_reverse( $hierarchy,TRUE);//sort top level down
    $ministries=church_admin_ministries(NULL);
    if(isset( $_POST['edit-small-group'] ) )
    {
		
		$form=array();
		foreach( $_POST AS $key=>$value)$form[$key]=church_admin_sanitize( $value);
		if ( empty( $form['lat'] ) )$form['lat']=0;
		if ( empty( $form['lng'] ) )$form['lng']=0;
		if ( empty( $form['attachment_id'] ) )$form['attachment_id']='';
        
        
		
		//handle oversight
		$structure=church_admin_get_ministry_hierarchy(1);
		$oversight=array();
    	foreach( $structure AS $ID=>$ministry)
    	{
    		if(!empty( $_POST['oversight'.(int)$id] ) )
    		{
    			$oversight[$ID]=intval( $_POST['oversight'.(int)$id] );
    		}
		}


		//check to see if processed
		if(!$id)$id=$wpdb->get_var('SELECT id FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE  group_name="'.esc_sql( $form['group_name'] ).'" AND lat="'.esc_sql( $form['lat'] ).'" AND lng="'.esc_sql( $form['lng'] ).'" AND address="'.esc_sql( $form['address'] ).'"');
		if( $id)
		{//update
			$sql='UPDATE '.$wpdb->prefix.'church_admin_smallgroup SET lat="'.esc_sql( $form['lat'] ).'",lng="'.esc_sql( $form['lng'] ).'",address="'.esc_sql( $form['address'] ).'",group_name="'.esc_sql( $form['group_name'] ).'",group_day="'.intval( $form['group_day'] ).'",group_time="'.esc_sql( $form['group_time'] ).'",oversight="'.esc_sql(serialize( $oversight) ).'",attachment_id="'.esc_sql( $form['attachment_id'] ).'", max_attendees="'.esc_sql( $form['max_attendees'] ).'" , frequency="'.esc_sql( $form['frequency'] ).'" , description="'.esc_sql( $form['group_description'] ).'" , contact_number="'.esc_sql( $form['contact_number'] ).'" WHERE id="'.esc_sql((int)$id).'"';
           
			$wpdb->query( $sql);

		}//end update
		else
		{//insert
			$sql='INSERT INTO  '.$wpdb->prefix.'church_admin_smallgroup (group_name,group_day,group_time,address,lat,lng,oversight,attachment_id,max_attendees,frequency,description,contact_number) VALUES("'.esc_sql( $form['group_name'] ).'","'.esc_sql( $form['group_day'] ).'","'.esc_sql( $form['group_time'] ).'","'.esc_sql( $form['address'] ).'","'.esc_sql( $form['lat'] ).'","'.esc_sql( $form['lng'] ).'","'.esc_sql(serialize( $oversight) ).'","'.esc_sql( $form['attachment_id'] ).'","'.esc_sql( $form['max_attendees'] ).'","'.esc_sql( $form['frequency'] ).'","'.esc_sql( $form['group_description'] ).'","'.esc_sql( $form['contact_number'] ).'")';

			$wpdb->query( $sql);
			$id=$wpdb->insert_id;
		}//insert
		if(defined('CA_DEBUG') )church_admin_debug("Group ID is $id");
		//add people to group
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$id.'" AND meta_type="smallgroup"');
		if(!empty( $_POST['people'] ) )
		{

			//find ids of people entered

			$people_ids=maybe_unserialize(church_admin_get_people_id(trim(church_admin_sanitize( $_POST['people'] ) )) );

			if(defined('CA_DEBUG') )church_admin_debug("People in group\r\n".print_r( $people_ids,TRUE) );
			if(!empty( $people_ids) )
			{

				foreach( $people_ids AS $key=>$person_id)
				{
					if(church_admin_int_check( $person_id) )
					{
						church_admin_update_people_meta( $id,$person_id,'smallgroup');
					}
				}
			}
			//anyone who was in group and has now been deleted gets put in unattached group
			if(!empty( $currentPeople) )
			{
				foreach( $currentPeople AS $key=>$people_id)
				{
					$check=church_admin_get_people_meta( $people_id,'smallgroup');//look to see if in any group
					if ( empty( $check) )church_admin_update_people_meta(1,$people_id,'smallgroup');//put them in group 1 if not
				}
			}
		}
		//handle leadership
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$id.'" AND meta_type="smallgroupleader"');
		if(!empty( $_POST['leaders'] ) )
		{
			$autocompleted=maybe_unserialize(church_admin_get_people_id(trim(church_admin_sanitize( $_POST['leaders'] ) )) );
			if(defined('CA_DEBUG') )church_Admin_debug("Leaders\r\n".print_r( $autocompleted,TRUE) );
			foreach( $autocompleted AS $x=>$name)
			{
				$p_id=church_admin_get_one_id(trim( $name) );//get the people_id
					if(defined('CA_DEBUG') )church_Admin_debug("Name: $name, People_id: $p_id, group id $id");
				if(!empty( $p_id) )
				{
					church_admin_update_people_meta( $id,$p_id,'smallgroupleader');//update person as leader at that level
				}
			}
		}
		
		
		$out.='<div class="wrap church_admin"><div id="message" class="notice notice-success inline"><p><strong>'.esc_html( __('Small Group Edited','church-admin' ) ).'</strong></p></div>';
			require_once(plugin_dir_path(dirname(__FILE__) ).'display/small-group-list.php');
		$out.= church_admin_small_group_list(1,13,TRUE,TRUE);
    }
    else
    {

		$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.esc_sql( $id).'"');
		if ( empty( $data) )$data=new stdClass();

	    $out.='<h2>'.esc_html( __('Add/Edit Small Group','church-admin' ) ).'</h2><form action="" method="post">';
	    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Small group name','church-admin' ) ).'</label><input type="text" class="church-admin-form-control" name="group_name"';
	    if(!empty( $data->group_name) ) $out.= ' value="'.esc_html( $data->group_name).'" ';
	    $out.=' /></div>';
		/*************************************
		*
		*	Image
		*
		*************************************/
		
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Photo','church-admin' ) ).'</label><div class="church-admin-smallgroup-image ca-upload-area" data-nonce="'.wp_create_nonce("smallgroup-image-upload").'" data-which="smallgroup" data-id="'.(int)$id.'" id="uploadfile">';
		if(!empty( $data->attachment_id) )
		{
			
			$image_attributes=wp_get_attachment_image_src( $data->attachment_id );
			if ( $image_attributes )
			{
				$out.='<img id="smallgroup-image'.(int)$data->id.'" src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'" class="rounded" alt="'.esc_html( __('Smallgroup image','church-admin' ) ).'" />';
			}
		}
		else
		{
			$out.='<img id="smallgroup-image'.(int)$id.'"  src="'.plugins_url('/', dirname(__FILE__) ) . 'images/household.svg'.'" width="300" height="200" class="rounded" alt="'.esc_html( __('Smallgroup image','church-admin' ) ).'" />';
		}
		$out.= '<br>'.esc_html( __('Drag and drop new image for small group','church-admin'));
		$out.= '<span id="smallgroup-upload-message"></span>';
		$out.='<input type="hidden" name="attachment_id" id="attachment_id" ';
		if(!empty( $data->attachment_id) )$out.=' value="'.(int)$data->attachment_id.'" ';
		$out.='/>';
		$out.='</div></div>';	
		//End image
	    if(!empty( $data->whenwhere) )$out.='<div class="church-admin-form-group"><label><strong>'.esc_html( __('Old Value','church-admin' ) ).'</label> '.esc_html( $data->whenwhere).'</div>';
	   	
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Meeting frequency','church-admin' ) ).'</label><select name="frequency" class="church-admin-form-control"> ';
		if(!empty( $data->frequency) )$out.='<option value="'.esc_html( $data->frequency).'" selected="selected">'.esc_html( $data->frequency).'</option>';
		$out.='<option value="'.esc_html( __('Weekly','church-admin' ) ).'" >'.esc_html( __('Weekly','church-admin' ) ).'</option>';
		$out.='<option value="'.esc_html( __('Fortnightly','church-admin' ) ).'" >'.esc_html( __('Fortnightly','church-admin' ) ).'</option>';
		$out.='<option value="'.esc_html( __('Occasionally','church-admin' ) ).'" >'.esc_html( __('Occasionally','church-admin' ) ).'</option>';
		$out.='<option value="'.esc_html( __('Monthly','church-admin' ) ).'" >'.esc_html( __('Monthly','church-admin' ) ).'</option>';
		$out.='</select></div>';
		
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Meeting Day','church-admin' ) ).'</label><select name="group_day" class="church-admin-form-control"> ';
       
       	$myweek = array();

		for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
			
			$myweek[] = $wp_locale->get_weekday(  $wdcount  );
		}
		$first=$option='';
		foreach ( $myweek as $key=>$wd ) {
		
			$day_name =  $wp_locale->get_weekday_abbrev( $wd );
			$wd = esc_attr( $wd );

			if(!empty( $data->group_day)&&$key==$data->group_day)
			{
				$first='<option value="'.(int)$key.'" selected="selected">'.$day_name.'</option>';

			}
			else
			{
				$option.='<option value="'.(int)$key.'">'.$day_name.'</option>';
			}

		}
		$out.=$first.$option;
       $out.='</select></div>';
	    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Start Time e.g. 19:00','church-admin' ) ).'</label><input type="time" id="group_time" class="church-admin-form-control" name="group_time"';
	    if(!empty( $data->group_time) ) $out.= ' value="'.esc_html( $data->group_time ).'" ';
	    $out.='/></div>';
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Max attendees ','church-admin' ) ).'</label><input type="text" class="church-admin-form-control" name="max_attendees" ';
		if(!empty( $data->max_attendees) ) $out.= ' value="'.esc_html( $data->max_attendees ).'" ';
	    $out.='/></div>';
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Description ','church-admin' ) ).'</label><textarea name="group_description"  class="church-admin-form-control" rows="10"> ';
		if(!empty( $data->description) ) $out.= esc_textarea( $data->description );
	    $out.='</textarea></div>';
		/*************************************************
		*
		* Map and Address
		*
		*************************************************/
		$key=get_option('church_admin_google_api_key');
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Address','church-admin' ) ).'</label><input type="text" id="address" class="church-admin-form-control" name="address" ';
	    if(!empty( $data->address) ) $out.= ' value="'.esc_html( $data->address).'" ';
	    $out.='/></div>';
		$api_key=get_option('church_admin_google_api_key');
		if(!empty( $api_key) )
		{
			$out.= '<div class="church-admin-form-group"><label><button id="geocode_address" class="button-primary btn btn-info">'.esc_html( __('Update map','church-admin' ) ).'</button></label><span id="finalise" ></span>';
			if(!empty( $data->id) )  {$id=(int)$data->id;}else{$id=0;}
            if(!empty( $data->lat) && !empty( $data->lat) )
            {//initial data for position already available
                $out.='<script >
					var zoom=17;
					var ca_method="smallgroup-map-geocode";
					var ID="'.(int)$id.'";
					var nonce="'.wp_create_nonce("smallgroup-map-geocode").'"; 
					var beginLat ='.esc_html( $data->lat).';';
					$out.= 'var beginLng ='.esc_html( $data->lng);
                $out.=';</script>';
            }else
            {
                $out.='<script >var zoom=0;
					var ID="'.(int)$id.'";
					var ca_method="smallgroup-map-geocode";
					var nonce="'.wp_create_nonce("smallgroup-map-geocode").'"; 
					var beginLat =0;
					var beginLng =0;
				</script>';
            }
			$out.='<div id="map" style="width:500px;height:300px;margin-bottom:20px"></div></div>';
			$out.='<input type="hidden" name="lat" id="lat" ';
			if(!empty( $data->lat) ) $out.=' value="'.esc_html( $data->lat).'"';
			$out.='/>';
			$out.='<input type="hidden" name="lng" id="lng" ';
			if(!empty( $data->lng) ) $out.=' value="'.esc_html( $data->lng).'"';
			$out.='/>';
			$out.='<div class="church-admin-response-errors"></div>';

		}
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Contact','church-admin' ) ).'</label><input type="text" class="church-admin-form-control" name="contact_number"';
	    if(!empty( $data->contact_number) ) $out.= ' value="'.esc_html( $data->contact_number).'" ';
	    $out.=' /></div>';
	



  		/*************************************************
		*
		* leadership section
		*
		**************************************************/
		$current_leaders='';
    	if(!empty( $data->id) )
    	{
    		$current_leaders=church_admin_get_people_meta_list('smallgroupleader',$data->id);
    	}
			$out.='<div class="form-group autocomplete"><label>'.esc_html( __('Group leaders','church-admin' ) ).'</label>'.church_admin_autocomplete('leaders','a','b',$current_leaders).'</div>';
    	//oversight
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Oversight','church-admin' ) ).'</label>';
    	if(!empty( $data->oversight) )
    	{
    		
    		$oversight=maybe_unserialize( $data->oversight);
    		
    	}
    	$structure=church_admin_get_ministry_hierarchy(1);
    	if(!empty( $structure) )
		{
    		foreach( $structure AS $ID=>$ministry)
    		{
    		
    			$levelResults=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_cell_structure WHERE ministry_id="'.(int)$id.'"');
    	
    			if(!empty( $levelResults) )
    			{
    				$out.='<div class="church-admin-form-group"><label>'.esc_html( $ministry).'</label><select class="church-admin-form-control" name="oversight'.(int)$id.'">';
    				foreach( $levelResults AS $levelRow)
    				{
    					$out.='<option value="'.intval( $levelRow->ID).'" '.selected( $levelRow->ID,$ID,FALSE).'>'.esc_html( $levelRow->name).'</option>';
    				}
    				$out.='</select></div>';
    			}
			}
    		
    	}

		$out.='</div>';
		$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Add some people to the group','church-admin' ) ).'</label>'.church_admin_autocomplete('people','friends','to',$displayCurrentPeople).'</div>';
		$out.='<div class="church-admin-form-group"><label>&nbsp;</label><input class="button-primary" type="submit" name="edit-small-group" value="'.esc_html( __('Save Small Group','church-admin' ) ).' &raquo;" /></div></form>';

    }
    return $out;
}

 /**
 *
 * Count of unassigned people
 *
 * @author  Andy Moyle
 * @param
 * @return  $out
 * @version  0.1
 *
 *
 *
 */
function church_admin_unassigned_count()
{
	global $wpdb;
	church_admin_groups_cleanup();
	$out='<h2 class="unassigned-toggle">'.esc_html( __('People in small groups cleanup (Click to Toggle)','church-admin' ) ).'</h2>';
	$out.='<div class="unassigned" style="display:none">';
		//work out how many people not assigned.
		$unassignedCount=$doubleAssigned=0;
		$peopleUnassigned=$peopleDoubleAssigned='';
		$people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people');
		if(!empty( $people) )
		{
			foreach( $people AS $person)
			{
				$inGroup=church_admin_get_people_meta( $person->people_id,'smallgroup');
				if ( empty( $inGroup) )
				{//not in a group
					$unassignedCount+=1;
					church_admin_update_people_meta(1,$person->people_id,'smallgroup');
				}
				if( $wpdb->num_rows>1)
				{
					$doubleAssigned+=1;
					$peopleDoubleAssigned.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.(int)$person->people_id,'edit_people').'">'.esc_html( $person->first_name .' '.$person->last_name).' '.esc_html( __('is in more than one small group','church-admin' ) ).'</a></p>';
				}

			}
		}

		if( $unassignedCount>0)$out.=sprintf( '<p>'.esc_html(__( 'There were %d unassigned people in your entire address list (They are now in "unattached")', 'church-admin' ) ).'</p>', $unassignedCount );
		if( $doubleAssigned>0)$out.=sprintf( '<p>'.esc_html(__( 'There are %d people who are in more than one small group at once in your entire address list.', 'church-admin' ) ).'</p>', $doubleAssigned );
		if(!empty( $peopleUnassigned) )$out.=$peopleUnassigned;
		if(!empty( $peopleDoubleAssigned) )$out.=$peopleDoubleAssigned;
	if ( empty( $peopleUnassigned)&&empty( $peopleDoubleAssigned) )  {$out.='<p>'.esc_html( __('All clean!','church-admin' ) ).'</p>';}
	$out.='</div>';
	$out.='<script type="text/javascript">jQuery(function()  {  jQuery(".unassigned-toggle").click(function()  {jQuery(".unassigned").toggle();  });});</script>';


	return $out;
}

 /**
 *
 * Cleanup of groups in people_meta table
 *
 * @author  Andy Moyle
 * @param
 * @return  $out
 * @version  0.1
 *
 *
 *
 */
function church_admin_smallgroups_cleanup()
{
	global $wpdb;
	$smg=array();//all the small groups
    ///get groups inti an array $smg
	$groups=$wpdb->get_results('SELECT id FROM '.$wpdb->prefix.'church_admin_smallgroup');
	if(!empty( $groups) )
	{
		foreach( $groups AS $group) $smg[]=intval( $group->id);
	}

    //find groups in meta table
	$groupsInMetaTable=$wpdb->get_results('SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta GROUP BY ID');
	
    if(!empty( $groupsInMetaTable) )
	{
		$where=array();
		foreach( $groupsInMetaTable AS $groupInMetaTable)
		{
			if(!in_array( $groupInMetaTable->ID,$smg) )
			{
				  $where[]=' (ID="'.intval( $groupInMetaTable->ID).'" AND meta_type="smallgroup") ';

			}
		}
        //delete from meta table if group no longer exists
		if(!empty( $where) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE '.implode(" OR ",$where) );
	}
    
    //make sure all adults are in a group or unattached
    $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_type_id=1');
    if(!empty( $people) )
    {
        foreach( $people AS $person)
        {
            //look to see if in any group
            $check=church_admin_get_people_meta( $person->people_id,'smallgroup');
            //put them in group 1 if not
            if ( empty( $check) )church_admin_update_people_meta(1,$person->people_id,'smallgroup');
        }
    }
	return '<div class="notice notice-success"><h2>'.esc_html( __('Small groups cleaned','church-admin' ) ).'</h2></div>';

}

function church_admin_smallgroup_metrics()
{
	
	global $wpdb,$member_types;

	/******************************************
	 * Handle member type 
	 *******************************************/
	$sql_safe_memb_sql='';
	$membsql=$sitesql=array();
	$member_type_ids=(!empty($_REQUEST['member_types']))?church_admin_sanitize($_REQUEST['member_types']):array('#');
	if ( empty( $member_type_ids)||$member_type_ids==__('All','church-admin')||$member_type_ids=="#")
	{
		//dont set the $memb_sql par of the queries if no member type given or set to all
		$membsql=array(); 
		$sql_safe_memb_sql="";
	}
	else
	{
		
		foreach( $member_type_ids AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='b.member_type_id='.(int)$value;}
		if(!empty( $membsql) ) {
			$sql_safe_memb_sql=' AND ('.implode(' || ',$membsql).')';
		}
	}



	$unattached=$wpdb->get_var('SELECT COUNT(a.people_id) FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.meta_type="smallgroup" AND a.ID=1 AND a.people_id=b.people_id AND b.people_type_id=1 '.$sql_safe_memb_sql);
	$inSmallGroup=$wpdb->get_var('SELECT COUNT(a.people_id) FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.meta_type="smallgroup" AND a.ID!=1 AND a.people_id=b.people_id AND b.people_type_id=1 '.$sql_safe_memb_sql);
	echo'<h2 class="smallgroupmetrics-toggle">'.esc_html( __('Small group metrics','church-admin' ) ).'</h2>';
	
	echo'<form action="admin.php?page=church_admin/index.php&action=groups" method="POST">';
	wp_nonce_field('groups');
	echo'<h3>'.esc_html(__('Member types','church-admin') ).'</h3>';
       
 		foreach( $member_types AS $key=>$value)
 		{
 			echo'<p><input type=checkbox value="'.(int)$key.'" name="member_types[]" ';
 			if(!empty( $member_type_ids) && in_array( $key, $member_type_ids) )echo' checked="checked" ';
 			echo'/>'.esc_html( $value).'</p>';

 		}
	echo'<p><input type="submit" class="button-primary" value="'.esc_html(__('Update pie chart','church-admin')).'"></p></form>';
	//echo"<p>$unattached not in a small group, $inSmallGroup are in a small group<p>";
	echo'
    <script type="text/javascript">
      google.charts.load("current", {"packages":["corechart"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
			["group status", "adults"],
         	["'.esc_html( __('In a small group','church-admin' ) ).'",'.intval( $inSmallGroup).'],["'.esc_html( __('Unattached','church-admin' ) ).'",'.intval( $unattached).']
        ] );

        var options = {
          "chartArea.backgroundColor":"#f1f1f1",backgroundColor:"#f1f1f1",is3D: true
        };

        var chart = new google.visualization.PieChart(document.getElementById("small-group-chart") );

        chart.draw(data, options);
      }
    </script><div id="small-group-chart" style="width:100%;height:200px"></div>';
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE  mtg_type="group" AND `date` BETWEEN "'.date('Y-m-d',strtotime("-1 year") ).'" AND "'.date('Y-m-d').'" ORDER BY `date` ASC';

		$results=$wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		$data=array();
	 	foreach( $results AS $row)
	 	{
	 		$total=(int)$row->adults+$row->children;
	 		
	 		if(!empty( $type)&& $type=='weekly')$data[]='["'.mysql2date('d M Y',$row->date).'",'.$total.']';
	 		
	 	}
	 	echo'<script>// Load the Visualization API.
    	google.load("visualization", "1", {"packages":["line"]});

    	// Set a callback to run when the Google Visualization API is loaded.
    	google.setOnLoadCallback(drawChart);
     	function drawChart() {

      		var data = new google.visualization.DataTable();
      		data.addColumn("string", "'.esc_html( __('Date','church-admin' ) ).'");
      		data.addColumn("number", "'.esc_html( __('Total','church-admin' ) ).'");
      		

      		data.addRows(['.implode(',',$data).'] );

      		var options = {
        	chart: {
          		title: "'.esc_html( __('Attendance Graph','church-admin' ) ).'"
        	},
        	width: '.$width.',
        	height: '.$height.'
      	};

      	var chart = new google.charts.Line(document.getElementById("smallgroup_attendance") );

      	chart.draw(data, options);

    	}</script>';
			echo'<div id="smallgroup_attendance" style="width:100%;height:200px"></div>';


	}
}

function church_admin_unattached_list()
{
	
	global $wpdb,$member_types;

	/**************************************
	 * Initialize variables
	 *************************************/
	$people_types=get_option('church_admin_people_type');
	$groups=church_admin_groups_array();
	
	$validated_input = $sanitized_input =  $peopleSQLarray = $memberSQLarray = array();
	$peopleSQL ='';
	$memberSQL = '';
	

	
	/**************************************
	 * Sanitize and validate user input 
	 *************************************/
	//handle people types
	if(!empty($_REQUEST['people_types']))
	{
		$entered_people_types = church_admin_sanitize($_REQUEST['people_types']);
		foreach ($entered_people_types AS $key=>$value){
			//sanitize input
			$this_sanitized = sanitize_text_field(stripslashes($value) );
			$sanitized_input['people_types'][] = $this_sanitized;
			//validate input
			if(!empty($people_types[$this_sanitized])){
				$validated_input['people_types'][]=(int)$this_sanitized;
			}
			unset( $this_sanitized );
		}
	}
	else
	{
		//just adults then
		$validated_input['people_types'][] = 1;
	}

	//handle member types

	if(!empty($_REQUEST['member_types']))
	{
		$entered_member_types = church_admin_sanitize($_REQUEST['member_types']);
		foreach ($entered_member_types AS $key=>$value){
			$this_sanitized = sanitize_text_field(stripslashes($value) );
			$sanitized_input['member_types'][] = $this_sanitized;
			//validate input
			if(!empty($member_types[$this_sanitized])){
				$validated_input['member_types'][]=(int)$this_sanitized;
			}
		}
	}
	else
	{
		//all member types
		$validated_input['member_types'] = array_keys($member_types);
	}
	
	/****************************
	 * Build SQL
	 ***************************/
	
	foreach($validated_input['people_types'] AS $key => $people_type_id){
		$peopleSQLarray[] = '(a.people_type_id = "'.(int)$people_type_id.'") ';
	}
	if(!empty($peopleSQLarray)){
		$peopleSQL= ' AND ('.implode(' OR ',$peopleSQLarray).' )';
	}
	foreach($validated_input['member_types'] AS $key => $member_type_id){
		$memberSQLarray[] = '(a.member_type_id = "'.(int)$member_type_id.'") ';
	}
	if(!empty($memberSQLarray)){
		$memberSQL= ' AND ('.implode(' OR ',$memberSQLarray).' )';
	}
	
	$sql=' SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id = b.people_id AND b.meta_type="smallgroup" AND b.ID = 1 '.$peopleSQL.$memberSQL.' ORDER BY a.last_name,a.first_name';
	
	$results = $wpdb->get_results($sql);
	//church_admin_debug($wpdb->last_query);


	//Build Output

	echo'<h2>'.esc_html( __('Unattached to group list','church-admin' ) ).'</h2>';

	/***********************
	 * Form
	 *********************/
	echo'<form action="admin.php?page=church_admin/index.php&action=unattached" method="post">';
	wp_nonce_field('unattached');
	echo'<p><strong>'.esc_html( __('Which People Types to Show','church-admin' ) ).'</strong></p>';
	foreach($people_types AS $people_type_id=>$people_type){
		echo'<p><input type="checkbox" name="people_types[]" value="'.(int)$people_type_id.'" ';
		if(!empty($validated_input['people_types']) && in_array($people_type_id,$validated_input['people_types'])){echo ' checked="checked" ';}
		echo'/>'.esc_html($people_type).'</p>';
	}
	
	echo'<p><strong>'.esc_html( __('Which Member Types to Show','church-admin' ) ).'</strong></p>';
	foreach($member_types AS $member_type_id=>$member_type){
		echo'<p><input type="checkbox" name="member_types[]" value="'.(int)$member_type_id.'" ';
		if(!empty($validated_input['member_types']) && in_array($member_type_id,$validated_input['member_types'])){echo ' checked="checked" ';}
		echo'/>'.esc_html($member_type).'</p>';
	}

	echo'<p><input type="submit" value="'.esc_html( __('Choose','church-admin' ) ).'" class="button-primary" /></p>';
	echo'</form>';

	/***********************
	 * Output results
	 *********************/
	if(empty($results)){
		echo '<p>'.esc_html( __('No people unattached to a group.','church-admin' ) ).'</p>';
		return;
	}
	echo'<h3>'.esc_html( __('People','church-admin' ) ).'</h3>';
	$groupOptions='<option>'.esc_html( __('Choose a group','church-admin' ) ).'</option>';
	foreach($groups AS $group_id=>$group_name){
		$groupOptions .='<option value="'.(int)$group_id.'">'.esc_html($group_name).'</option>';
	}

	foreach($results AS $row)
	{

		$name=church_admin_formatted_name($row);
		$dropdown='<select class="church-admin-form-control group-selector" data-people-id="'.(int)$row->people_id.'">'.$groupOptions.'</select>';
		echo'<div class="church-admin-form-group" id="people-'.(int)$row->people_id.'"><label><a target="_blank" href="admin.php?page=church_admin/index.php&action=edit_people&people_id='.(int)$row->people_id.'">'.esc_html($name).'</a></label>'.$dropdown.'</div>';

	}

	/***********************
	 * jQuery AJAX magic
	 *********************/

	echo'<script>';
	?>
		jQuery(document).ready(function($){
			$(document).on('change', '.group-selector', function() {
					console.log('Selector changed');
				
					var people_id=$(this).data("people-id");
					var group_id = $(this).find('option:selected').val();
					var nonce="<?php echo wp_create_nonce("add-to-group");?>";
        			var args = {"action": "church_admin","method": "add-to-group","people_id": people_id,"group_id":group_id,"nonce":nonce};
					console.log(args);
					$.getJSON({
                        url: ajaxurl,
                        type: "post",
                        data:  args,
                        success: function(response) {
                            console.log(response);
                            if(response.people_id)  {$("#people-"+people_id).hide("slow")}
                            
                        }
                    });
					
			});

		})
	<?php
	echo'</script>';
}