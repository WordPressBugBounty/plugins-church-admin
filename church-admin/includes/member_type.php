<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function  church_admin_member_type()
{
    global $wpdb;
    if(!church_admin_level_check('Member Type') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin'  ),__('Member type','church-admin') )).'</h2></div>';
		return;
	}
    echo '<h2>'.esc_html( __('Member Types','church-admin' ) ).'</h2>';
    echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/member-types/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
   

    if(isset( $_POST['current'] ) )
    {
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET member_type_id="'.esc_sql((int)$_POST['reassign'] ).'" WHERE member_type_id="'.esc_sql((int)$_POST['current'] ).'" OR member_type_id IS NULL');
        
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET member_type_id="'.esc_sql((int)$_POST['reassign'] ).'" WHERE member_type_id="'.esc_sql((int)$_POST['current'] ).'" OR member_type_id IS NULL');
      
        echo'<div class="notice notice-success inline"><p>'.esc_html( __('People reassigned','church-admin' ) ).'</p></div>';
    }

 
   
    $member_type=church_admin_member_types_array();
    
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-member-type','edit-member-type').'" class="button-primary">'.esc_html( __('Add member type','church-admin' ) ).'</a></p>';
    
    $theader='<tr><th class="column-primary">'.esc_html( __('Type','church-admin' ) ).'</th><th>ID</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Reassign','church-admin' ) ).'</th></tr>';
    echo'<table  class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tfoot>'.$theader.'</tfoot><tbody class="content">';
    
    //first row
    //people with no member type
    $noMemberType=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id =0 or member_type_id IS NULL');
    if( $noMemberType)
    {
            $reassign='<form action="admin.php?page=church_admin/index.php&action=member-types" method="post">';
            $reassign.=wp_nonce_field('member-types');
            $reassign.=esc_html( __('Reassign people to','church-admin' ) ).' <select name="reassign">';
			foreach( $member_type AS $mtid=>$value)$reassign.='<option value="'.intval( $mtid).'">'.esc_html( $value).'</option>';
            $reassign.='</select><input type="hidden" name="current" value="0" /><input type="submit" class="button-secondary" value="Reassign" /></form>';
         echo'<tr ><td style="color:red!important" class="column-primary" data-colname="'.esc_html( __('Member Type','church-admin' ) ).'">'.esc_html( __("No member type specified",'church-admin' ) ).' ('.esc_html(sprintf(__('%1$s people','church-admin' ) ,$noMemberType)).')</td><td data-colname="ID">0</td><td>&nbsp;</td><td>&nbsp;</td><td data-colname="'.esc_html( __('Reassign','church-admin' ) ).'">'.$reassign.'</td></tr>';
    }
    foreach( $member_type AS $id=>$membertype)
    {
        $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-member-type&amp;section=people&amp;member_type_id='.$id,'edit-member-type').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
        $check=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.esc_sql( $id).'"');
        if(!$check)
        {
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-member-type&section=people&member_type_id='.$id,'delete-member-type').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            $reassign='&nbsp;';
        }
        else
        {
            $delete=$check .' '.esc_html( __('people who are','church-admin' ) ).' '.esc_html( $membertype);
            $reassign='<form action="admin.php?page=church_admin/index.php&action=member-types" method="post">'.esc_html( __('Reassign people to','church-admin' ) ).' ';
            
            $reassign.=wp_nonce_field('member-types');
            $reassign.='<select name="reassign">';
			foreach( $member_type AS $mtid=>$value)if( $mtid!=$id) $reassign.='<option value="'.intval( $mtid).'">'.esc_html( $value).'</option>';
            $reassign.='</select><input type="hidden" name="current" value="'.(int)$id.'" /><input type="submit" class="button-secondary" value="Reassign" /></form>';
        }


        echo'<tr id="'.(int)$id.'"><td class="column-primary" data-colname="'.esc_html( __('Member Type','church-admin' ) ).'">'.esc_html( $membertype).' ('.esc_html(sprintf(__('%1$s people','church-admin' ) ,$check)).') <button type="button" class="toggle-row">
        <span class="screen-reader-text">show details</span>
    </button></td><td data-colname="ID">'.(int)$id.'</td><td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td><td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td><td data-colname="'.esc_html( __('Reassign','church-admin' ) ).'">'.$reassign.'</td></tr>';

    }
    echo'</tbody></table>';
    
}

function church_admin_edit_member_type( $member_type_id=NULL)
{
    global $wpdb;
    if(!church_admin_level_check('Member Type') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Member type','church-admin')) ).'</h2></div>';
		return;
	}
    if(isset( $_POST['edit_member_type'] ) )
    {
        if( $member_type_id)
        {
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_member_types SET member_type="'.esc_sql(sanitize_text_field( stripslashes($_POST['member_type']) ) ).'" WHERE member_type_id="'.esc_sql( (int)$member_type_id).'"');
        }
        else
        {
            $nextorder=1+$wpdb->get_var('SELECT member_type_order FROM '.$wpdb->prefix.'church_admin_member_types ORDER BY member_type_order LIMIT 1');
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_member_types(member_type_order,member_type)VALUES("'.esc_sql( $nextorder).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['member_type'] ) ) ).'")');
        }
       
        echo'<div class="notice notice-success inline"><p>'.esc_html( __('Member Type Updated','church-admin' ) ).'</p></div>';
		echo '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type&amp;section=people','edit_member_type').'" class="button-primary">'.esc_html( __('Add a member type','church-admin' ) ).'</a>';
        church_admin_member_type();
    }
    else
    {


        echo'<div class="wrap church_admin"><h2>';
        if( $member_type_id)  {echo' '.esc_html( __('Edit','church-admin' ) ).' ';}else{echo esc_html(__('Add','church-admin' ) ).' ';}
        echo esc_html(__('Member Type','church-admin' ) ).'</h2><form action="" method="POST">';
        echo'<p><label>'.esc_html( __('Member Type','church-admin' ) ).'</label><input type="text" name="member_type" ';
        if(!empty( $member_type_id) )
	{
	    $type=$wpdb->get_var('SELECT member_type FROM '.$wpdb->prefix.'church_admin_member_types WHERE member_type_id="'.esc_sql( $member_type_id).'"');
	    echo'value="'.esc_html( $type).'" ';
	}
        echo'/></p>';
        echo'<p class="submit"><input type="hidden" name="edit_member_type" value="yes" /><input type="submit" value="'.esc_html( __('Save Member Type','church-admin' ) ).' &raquo;" class="button-primary" /></p></form></div>';

    }
}
function church_admin_delete_member_type( $member_type_id=NULL)
{
    global $wpdb;
    if(!church_admin_level_check('Member Type') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Member type','church-admin') )).'</h2></div>';
		return;
	}
    if( $member_type_id)
    {
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_member_types WHERE member_type_id="'.esc_sql( $member_type_id).'"');
        echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Member Type Deleted','church-admin' ) ).'</strong></p></div>';
    }
    church_admin_member_type();
}
?>
