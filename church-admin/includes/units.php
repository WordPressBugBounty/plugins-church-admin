<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




/******************************************************************
*
*   Unit List
*
*
*******************************************************************/
function church_admin_units_list()
{
    global $wpdb;
    
    $out='<h2>'.esc_html( __('Units','church-admin' ) ).'</h2>';
    //$out.='<p>'.esc_html( __("Use units for groups that don't fit as normal small groups or classes, like prayer triplets, bible studies.",'church-admin' ).'</p>';
    $out.='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-unit&section=units','edit-unit').'">'.esc_html( __('Add a unit','church-admin' ) ).'</a></p>';
    
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_units ORDER BY name');
    if(!empty( $results) )
    {
        $theader='<tr><th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Description','church-admin' ) ).'</th><th>'.esc_html( __('Count','church-admin' ) ).'</th>';
        $out.='<table class="widefat wp-list-table"><thead>'.$theader.'</thead><tfoot>'.$theader.'</tfoot><tbody>';
        foreach( $results AS $row)
        {
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-unit&section=units&amp;unit_id='.$row->unit_id,'edit-unit').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-unit&section=units&amp;unit_id='.$row->unit_id,'delete-unit').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE unit_id="'.intval( $row->unit_id).'"');
            $out.='<tr><td class="column-primary" data-colname="'.esc_html( __('Name','church-admin' ) ).'">'.esc_html( $row->name).'<button type="button" class="toggle-row">
            <span class="screen-reader-text">show details</span></button></td>';
            $out.='<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>';
            $out.='<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>';
            $out.='<td data-colname="'.esc_html( __('Description','church-admin' ) ).'">'.esc_html( $row->description).'</td>';
            $out.='<td data-colname="'.esc_html( __('Count','church-admin' ) ).'">'.$count.'</td></tr>';
        }
        $out.='</tbody></table>';
    }
    else{$out.='<p>'.esc_html( __('No units created yet','church-admin' ) ).'</p>';}
    echo $out;
}

/******************************************************************
*
*   Edit Unit List
*
*
*******************************************************************/
function church_admin_edit_unit( $unit_id=NULL)
{
    global $wpdb;
    if(!empty( $unit_id) )$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_units WHERE unit_id="'.(int)$unit_id.'"');
    echo'<h2>'.esc_html( __('Add/Edit Unit','church-admin' ) ).'</h2>';
    echo'<p>'.esc_html( __("Here you can create a unit type like Prayer Triplets, which will appear in the main menu",'church-admin' ) ).'</p>';
    if(!empty( $_POST['save-unit'] ) )
    {
        
        echo'<div class="notice notice-success inline"><h2>'.esc_html( __('Unit saved','church-admin' ) ).'</h2></div>';
        church_admin_units_list();
        
        
    }
    else
    {
        echo'<form action="" method="POST">';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Name','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="unit_name" required="required" placeholder="'.esc_html( __('Name of unit e.g. Prayer Triplets','church-admin' ) ).'" ';
        if(!empty( $data->name) )echo' value="'.esc_html( $data->name).'" ';
        echo'/></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Description','church-admin' ) ).'</label><textarea class="church-admin-form-control" name="unit_description">';
        if(!empty( $data->description) )echo esc_textarea( $data->description);
        echo'</textarea></div>';
        echo'<p><input type="hidden" name="save-unit" value=TRUE/><input class="button-primary" type="submit" value="'.esc_html( __('Save','church-admin' ) ).'&raquo;" /></p></form>';
    }
    
}

/******************************************************************
*
*   List of sub units, eg list of prayer triplets
*
*
*******************************************************************/

function church_admin_show_subunits( $unit_id)
{
    global $wpdb;
    $unitDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_units WHERE unit_id="'.(int)$unit_id.'"');
    $tab=sanitize_title( $unitDetails->name);    
    echo'<h2>'.esc_html( $unitDetails->name).'</h2>';
    echo'<p><a class="button-primary" href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-subunit&section='.$tab.'&amp;unit_id='.(int)$unit_id,'edit-subunit')).'">'.esc_html(sprintf(__('Add %1$s','church-admin' ) ,esc_html( $unitDetails->name) )).'</a></p>';
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE unit_id="'.(int)$unit_id.'" ORDER BY name ASC');
    if(!empty( $results) )
    {
        
        $theader='<tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Description','church-admin' ) ).'</th><th>'.esc_html( __('Count','church-admin' ) ).'</th>';
        echo'<table class="widefat"><thead>'.$theader.'</thead><tfoot>'.$theader.'</tfoot><tbody>';
        foreach( $results AS $row)
        {
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-subunit&section='.$tab.'&amp;unit_id='.intval( $row->unit_id).'&subunit_id='.intval( $row->subunit_id),'edit-unit').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-subunit&section='.$tab.'&amp;unit_id='.intval( $row->unit_id).'&subunit_id='.intval( $row->subunit_id),'delete-unit').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="unit" AND ID="'.intval( $row->subunit_id).'"');
            echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html( $row->name).'</td><td>'.esc_html( $row->description).'</td><td>'.$count.'</td></tr>';
        }
        echo'</tbody></table>';
    }else{echo '<p>'.esc_html( __('No sub units yet','church-admin' ) ).'</p>';}
}
/******************************************************************
*
*   Delete a subunit 
*
*
*******************************************************************/
function church_admin_delete_unit( $unit_id)
{
    global $wpdb;
    $subunits=$wpdb->get_results('SELECT subunit_id FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE unit_id="'.(int)$unit_id.'"');
    if(!empty( $subunits) )
    {
        foreach( $subunits AS $subunit)
        {
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE subunit_id="'.(int)$subunit->subunit_id.'"');
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$subunit->subunit_id.'" AND meta_type="unit"');
        }
    }
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_units WHERE unit_id="'.(int)$unit_id.'"');
    echo'<div class="notice notice-success inline"><h2>'.esc_html( __('Unit deleted','church-admin' ) ).'</h2></div>';
    church_admin_units_list();
    
}
/******************************************************************
*
*   Delete a  unit eg a particular prayer triplet
*
*
*******************************************************************/
function church_admin_delete_subunit( $unit_id,$subunit_id)
{
    global $wpdb;
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE subunit_id="'.(int)$subunit_id.'"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$subunit_id.'" AND meta_type="unit"');
    echo'<div class="notice notice-success inline"><h2>'.esc_html( __('Subunit deleted','church-admin' ) ).'</h2>';
    church_admin_show_subunits( $unit_id);
    
}
/******************************************************************
*
*   Add or edit a sub unit eg a particular prayer triplet
*
*
*******************************************************************/
function church_admin_edit_subunit( $unit_id,$subunit_id=NULL)
{
    global $wpdb;
    $unitDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_units WHERE unit_id="'.(int)$unit_id.'"');
    echo'<h2>'.esc_html(sprintf(__('Add/Edit %1$s','church-admin' ),$unitDetails->name)).'</h2>';
    if(!empty( $subunit_id) )  {$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE subunit_id="'.(int)$subunit_id.'"');}
    if(!empty( $_POST['save-subunit'] ) )
    {
        
       
        echo'<div class="notice notice-success inline"><h2>'.esc_html( __('Unit saved','church-admin' ) ).'</h2></div>';
        church_admin_show_subunits( $unit_id);
    }
    else
    {
        echo'<form action="" method="POST">';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Name','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="unit_name" required="required" placeholder="'.esc_html( __('Name of unit e.g. Prayer Triplets','church-admin' ) ).'" ';
        if(!empty( $data->name) )echo' value="'.esc_html( $data->name).'" ';
        echo'/></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Description','church-admin' ) ).'</label><textarea class="church-admin-form-control" name="unit_description">';
        if(!empty( $data->description) )echo esc_textarea( $data->description);
        echo'</textarea></div>';
        $currentPeople=NULL; if(!empty( $subunit_id) )$currentPeople=church_admin_get_people_meta_list('unit',$subunit_id);
        echo'<div class="form-group"><label>'.esc_html( __('Add some people','church-admin' ) ).'</label>'.church_admin_autocomplete('people','friends','to',$currentPeople).'</div>';
        echo'<p><input type="hidden" name="save-subunit" value=TRUE/><input class="button-primary" type="submit" value="'.esc_html( __('Save','church-admin' ) ).'&raquo;" /></p></form>';
    }
}