<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Displays ministries.
 *
 * @param
 * @param
 *
 * @author andy_moyle
 *
 */
function church_admin_ministries_list()
{
    global $wpdb;
    $ministries=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE parentID IS NULL OR parentID=0 ORDER BY ministry ASC');

    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-ministry&section=ministries','edit-ministry').'">'.esc_html( __('Add a ministry','church-admin' ) ).'</a> <a  rel="nofollow" class="button-secondary" href="'.wp_nonce_url(site_url().'/?ca_download=ministries_pdf','ministries_pdf').'">'.esc_html( __('Ministries PDF','church-admin' ) ).'</a></p>';
    if(!empty( $ministries) )
    {
        $theader='<tr><th class="column-primary">'.esc_html( __('Ministry','church-admin' ) ).'</th><th>Ministry id</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Team Contact','church-admin' ) ).'</th><th>'.esc_html( __('Allow online volunteering','church-admin' ) ).'</th><th>'.esc_html( __('Safeguarding Needed','church-admin' ) ).'</th><th>'.esc_html( __('Shortcode','church-admin' ) ).'</th></tr>';
        echo'<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tbody>';
        foreach( $ministries AS $ministry)
        {
            church_admin_ministries_table_row($ministry,TRUE);
            $children = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE parentID ="'.(int)$ministry->ID.'" ORDER BY ministry ASC');
            if(!empty($children)){
                foreach($children AS $child){church_admin_ministries_table_row($child,FALSE);}
            }


        }
        echo'</tbody><tfoot>'.$theader.'</tfoot></table>';
    }

}

function church_admin_ministries_table_row($ministry,$bold_title){
    global $wpdb;
    $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-ministry&amp;section=ministries&amp;id='.$ministry->ID,'edit-ministry').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
    if( $ministry->ID!=1)  {$delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-ministry&amp;id='.$ministry->ID,'delete-ministry').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';}else{$delete=__("Can't be deleted",'church-admin');}
    $min=esc_html( $ministry->ministry);
    if(!empty( $ministry->parentID) )
    {
        $parent=$wpdb->get_var('SELECT ministry FROM '.$wpdb->prefix.'church_admin_ministries'. ' WHERE ID="'.(int)$ministry->parentID.'"');
        if(!empty( $parent) )$min.=' ('.esc_html( __('Overseen by','church-admin' ) ).' '.esc_html( $parent).')';
    }
    if(!empty( $ministry->safeguarding) )  {$safe='<span class="ca-dashicons dashicons dashicons-yes"></span>';}else{$safe='';}
    if(!empty( $ministry->volunteer) )  {$volunteer='<span class="ca-dashicons dashicons dashicons-yes"></span>';}else{$volunteer='';}
    $team_contact_ids=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$ministry->ID.'" AND meta_type="team_contact"');
    $team_contact=__('Not assigned yet','church-admin');
    $teamContactData=array();
    if(!empty( $team_contact_ids) )
    {
        foreach( $team_contact_ids AS $team_contact_id)
        {
            $team_contact_row=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,last_name) AS name,email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.intval( $team_contact_id->people_id).'"');
            if(!empty( $team_contact_row->email) )
            {
                $teamContactData[]='<a href="mailto:'.$team_contact_row->email.'">'.esc_html( $team_contact_row->name).'</a>';
            }
            elseif(!empty( $team_contact_row_name) )
            {
            $teamContactData[]=esc_html( $team_contact_row->name);
            }
        }
        $team_contact=implode(", ",$teamContactData);
    }

    echo'<tr>
    <td data-colname="'.esc_html( __('Ministry','church-admin' ) ).'" class="column-primary">';
    if(!empty($bold_title)){echo'<strong>';}
    echo esc_html( $min).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button>';
    if(!empty($bold_title)){echo'</strong>';}
    echo '</td>';
    echo '<td data-colname="'.esc_html( __('Ministry ID','church-admin' ) ).'" >'.(int)$ministry->ID.'</td>';
    echo '<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'" >'.$edit.'</td>';
    echo '<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'" >'.$delete.'</td>';
    echo '<td data-colname="'.esc_html( __('Team Contact','church-admin' ) ).'" >'.$team_contact.'</td>';
    echo '<td data-colname="'.esc_html( __('Online Volunteer?','church-admin' ) ).'" >'.$volunteer.'</td>';
        echo '<td data-colname="'.esc_html( __('Safeguarding needed?','church-admin' ) ).'" >'.$safe.'</td>';
    echo '<td data-colname="'.esc_html( __('Shortcode','church-admin' ) ).'" >[church_admin type="ministries" ministry_id='.(int)$ministry->ID.']</td>';
    echo '</tr>';
}
function church_admin_view_ministry( $id)
{
    if(!empty($id) || !church_admin_int_check($id))
    {
        echo '<div class="notice notice-danger"><h2>'.esc_html( __('No ministry selected','church-admin') ).'</h2></div>';
        return;
    }
		echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=ministries",'ministries').'">'.esc_html( __('Ministry List','church-admin' ) ).'</a></p>';
		global $wpdb;
		$ministries=church_admin_ministries();
		$sql='SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.ID="'.(int)$id.'" AND b.meta_type="ministry" ORDER BY a.last_name ASC';

		$results=$wpdb->get_results( $sql);
		if(!empty( $_POST) )
		{
			//delete people from that ministry
			if(!empty( $_POST['remove'] ) )  {
                $remove= church_admin_sanitize($_POST['remove']);
				foreach( $remove AS $key=>$value) $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="ministry" AND ID="'.(int)$id.'" AND people_id="'.esc_sql( $value).'"');
			}
			//add people to ministry
			$peoples_id=maybe_unserialize(church_admin_get_people_id(sanitize_text_field( stripslashes($_POST['people'] )) ));
			if(!empty( $peoples_id) ) {
					foreach( $peoples_id AS $key=>$people_id)  {

						$sql='INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,ID,meta_type)VALUES("'.(int)$people_id.'","'.(int)$id.'","ministry")';
						$wpdb->query( $sql);
					}
				}

		}
	$results=$wpdb->get_results('SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.ID="'.(int)$id.'" AND b.meta_type="ministry" ORDER BY a.last_name,a.first_name ASC');

			echo '<h2>'.esc_html(sprintf(__('Viewing who is in "%1s" ministry','church-admin' ) , $ministries[$id] ) ).'</h2><form action="" method="POST">';
			if(!empty( $results) )
			{//ministry contains people
				echo'<table class="widefat striped" ><thead><tr><th>'.esc_html( __('Remove','church-admin' ) ).'</th><th>'.esc_html( __('Person','church-admin' ) ).'</th></tr></thead><tbody>';
				foreach( $results AS $row)
				{
					$delete='<input type="checkbox" value="'.esc_html( $row->people_id).'" name="remove[]" />';
					echo'<tr><td>'.$delete.'</td><td>'.esc_html( $row->name).'</td></tr>';
				}
				echo'</table>';
			}//ministry contains people
			echo'<p>'.church_admin_autocomplete('people','friends','to',NULL).'</p>';
			echo'<p><label>'.esc_html( __('Add people','church-admin' ) ).'</label><input type="hidden" name="view_ministries" value="yes" /><input type="submit" value="'.esc_html( __('Update','church-admin' ) ).'" /></p></form>';


		require_once(plugin_dir_path(dirname(__FILE__) ).'includes/comments.php');
		if(!empty( $id) )church_admin_show_comments('ministry',	$id);
}
function church_admin_delete_ministry( $id)
{
    global $wpdb;
	$wpdb->query(' DELETE FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int)$id.'"');
    //delete ministry from people
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$id.'" AND meta_type="ministry"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$id.'" AND meta_type="team_contact"');
    echo'<div class="notice notice-success inline"><p>'.esc_html( __('Ministries Deleted','church-admin' ) ).'</p></div>';
    church_admin_ministries_list();
}



function church_admin_edit_ministry( $ID)
{
global $wpdb,$ministries;


   if ( empty( $ministries) ) $ministries=array();
    if(isset( $_POST['edit_ministry'] ) )
    {//process
        $dep_name=sanitize_text_field(stripslashes( $_POST['ministry_name'] ) );
        $overseer=sanitize_text_field(stripslashes( $_POST['overseer'] ) );
        $calendar = !empty($_POST['calendar'])?1:0;
        if( $ID)
        {//update current ministry name
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET ministry="'.esc_sql( $dep_name).'"  WHERE ID="'.esc_sql( $ID).'"');
            echo '<div class="notice notice-success inline"><p>'.esc_html( __('Ministries Updated','church-admin' ) ).'</p></div>';
        }
        elseif(!in_array( $dep_name,$ministries) )
        {//add new one if unique
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry) VALUES("'.esc_sql( $dep_name).'")');
            echo '<div class="notice notice-success inline"><p>'.esc_html( __('Ministries Updated','church-admin' ) ).'</p></div>';
            $ID=$wpdb->insert_id;
        }
        else
        {//not unique or update, so ignore!
           echo '<div class="notice notice-success inline"><p>'.esc_html( __('Ministries Unchanged','church-admin' ) ).'</p></div>';
        }
        if(!empty( $_POST['safeguarding'] ) )
        {
        	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET   safeguarding="1" WHERE  ID="'.(int)$ID.'"');
        }
        else
        {
        	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET   safeguarding="0" WHERE  ID="'.(int)$ID.'"');
        }
		
		if(!empty( $_POST['volunteer'] ) )
        {
        	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET volunteer="1" WHERE  ID="'.(int)$ID.'"');
        }
        else
        {
        	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET volunteer="0" WHERE  ID="'.(int)$ID.'"');
        }
        if(!empty( $_POST['parent_id'] ) )
        {
        	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET   parentID="'.(int) $_POST['parent_id'] .'" WHERE  ID="'.(int)$ID.'"');
        }
        if(!empty( $_POST['overseer'] ) )
        {
        	$check=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql( $overseer).'"');
        	if( $check)
        	{//update
        		$sql='UPDATE '.$wpdb->prefix.'church_admin_ministries SET parent_ID="'.(int)$check.'" WHERE ID="'.(int)$id.'"';
        		echo $sql;
        		$wpdb->query( $sql);
        		echo '<div class="notice notice-success inline"><p>'.$overseer.' '.esc_html( __('updated','church-admin' ) ).'</p></div>';
        	}
        	else
        	{
        		$sql='INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry) VALUES("'.esc_sql( $overseer).'",)';

        		$wpdb->query( $sql);
        		$parentID=$wpdb->insert_id;
        		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET parentID="'.intval( $parentID).'" WHERE ID"'.(int)$ID.'"');
        		echo '<div class="notice notice-success inline"><p>'.esc_html( __('Overseer added','church-admin' ) ).'</p></div>';
        	}

        }
        //v1.06 add in extra people
       	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="ministry" AND ID ="'.(int)$ID.'"');
        if(!empty( $_POST['people'] ) )$people_ids=maybe_unserialize(church_admin_get_people_id(sanitize_text_field( stripslashes($_POST['people'] ) )));
        if(!empty( $people_ids) )
        {
        	foreach( $people_ids AS $key=>$people_id)
        	{
				if(!empty( $people_id) )church_admin_update_people_meta( $ID,$people_id,'ministry');
        	}
        }
        //v1.500 edit team contact
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="team_contact" AND ID ="'.(int)$ID.'"');
        //v1.5200 make sure people ids isn't repeated from above!
        if(!empty( $_POST['team_contact'] ) ){
            $vol_people_ids=maybe_unserialize(church_admin_get_people_id(sanitize_text_field( stripslashes( $_POST['team_contact'] ))));
        }
        if(!empty( $vol_people_ids) )
        {
        	foreach( $vol_people_ids AS $key=>$people_id)
        	{
				        

                if(!empty( $people_id) ){
                church_admin_update_people_meta( $ID,$people_id,'team_contact');
                }
        	}
        }
        church_admin_ministries_list();

    }//end process
    else
    {//form

        echo'<h2>';
        if( $ID)
        {
        	$which= esc_html( __('Update','church-admin' ) ).' ';
        	$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int)$ID.'"');
        	if( $data->parentID)$parent=$wpdb->get_var('SELECT ministry FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.intval( $data->parentID).'"');
        }else {$which= esc_html(  __('Add','church-admin' ) ).' ';}
        echo $which .esc_html( __('Ministry','church-admin' ) ).'</h2>';
        echo'<form action="" method="post">';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Ministry Name','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="ministry_name" ';
        if( $ID) echo ' value="'.esc_html( $ministries[$ID] ).'" ';
        echo'/></div>';
       
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Online volunteer?','church-admin' ) ).'?</label><input type="checkbox" name="volunteer" value=1';
        if(!empty( $data->volunteer) ) echo' checked="checked" ';
        echo'/></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Safeguarding needed','church-admin' ) ).'?</label><input type="checkbox" name="safeguarding" value=1';
        if(!empty( $data->safeguarding) ) echo' checked="checked" ';
        echo'/></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Parent Ministry','church-admin')) .'</label><input class="church-admin-form-control"  type="text" name="overseer"  ';
        //if(!empty( $parent) )  {echo 'value="'.esc_html( $parent).'" ';}
        echo'/></div>';

        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Or choose a parent ministry','church-admin') ).'</label>';
        echo'<select class="church-admin-form-control"  name="parent_id">';

        if(!empty( $parent)&&!empty( $data->parentID) )echo'<option value="'.intval( $data->parentID).'" selected="selected">'.esc_html( $parent).'</option>';
        echo'<option value="">'.esc_html( __('None','church-admin' ) ).'</option>';
        foreach( $ministries AS $id=>$min)
        	{
        		if((!empty( $ID)&&$ID!=$id) ||empty( $ID) ) echo'<option value="'.(int)$id.'">'.esc_html( $min).'</option>';
        	}
        echo'</select></div>';

       	if(!empty( $ID) )$results=$wpdb->get_results('SELECT people_id FROM '. $wpdb->prefix.'church_admin_people_meta'.' WHERE meta_type="ministry" AND ID="'.(int)$ID.'"');
       	if(!empty( $results) )
       	{
       		foreach( $results AS $row)$current_ldrs[]=$row->people_id;
       	}
       	if(!empty( $current_ldrs) )  {$current=church_admin_get_people( $current_ldrs);}else{$current='';}
    		echo'<div class="church-admin-form-group"><label>'.esc_html( __('People in ministry','church-admin' ) ).'</label>'.church_admin_autocomplete("people","friends","to",$current).'</div>';
        // Team contact
        $current_contacts=array();
        if(!empty( $ID) )$results=$wpdb->get_results('SELECT people_id FROM '. $wpdb->prefix.'church_admin_people_meta'.' WHERE meta_type="team_contact" AND ID="'.(int)$ID.'"');
       	if(!empty( $results) )
       	{
       		foreach( $results AS $row)$current_contacts[]=$row->people_id;
       	}
       	if(!empty( $current_contacts) )  {$currentContacts=church_admin_get_people( $current_contacts);}else{$currentContacts='';}
    		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Team contacts','church-admin' ) ).'</label>'.church_admin_autocomplete("team_contact","team_contact","to",$currentContacts).'</td></tr>';
   

        echo'<p class="submit"><input type="hidden" name="edit_ministry" value="yes" /><input class="button-primary" type="submit" value="'.esc_html( __('Save Ministry','church-admin' ) ).'&raquo;" /></p></form>';



    }//end form

        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/comments.php');
		if(!empty( $id) )church_admin_show_comments('ministry',	$ID);
}



