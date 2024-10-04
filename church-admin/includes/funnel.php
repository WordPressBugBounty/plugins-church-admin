<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * List of funnels
 *
 * @param
 *
 *
 * @author andy_moyle
 *
 */
function church_admin_funnel_list()
{
    global $wpdb,$people_type,$member_types,$ministries;
   
   

	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_funnel','edit_funnel').'">'.esc_html( __('Add a follow up funnel','church-admin' ) ).'</a></p>';


    $result=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_funnels' .'  ORDER BY funnel_order');
    if( $result)
    {

        
        $theader='<tr>
        <th class="column-primary">'.esc_html( __('Funnel','church-admin' ) ).'</th>
        <th>'.esc_html( __('Edit','church-admin' ) ).'</th>
        <th>'.esc_html( __('Delete','church-admin' ) ).'</th>
        <th>'.esc_html( __('Applies to','church-admin' ) ).'...</th>
        <th>'.esc_html( __('Ministry responsible','church-admin' ) ).'</th>
        <th>'.esc_html( __('Active','church-admin' ) ).'</th>
        <th>'.esc_html( __('Not yet emailed','church-admin' ) ).'</th>
        <th>'.esc_html( __('Completed','church-admin' ) ).'</th>
        </tr>';
        
        echo'<table  class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tbody>';
        $totalNotEmailed=0;
        foreach( $result AS $row)
        {
          $active=$completed=$notEmailed=0;
          $active=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_follow_up WHERE funnel_id="'.(int)$row->funnel_id.'" AND completion_date=NULL');
          $notEmailed=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_follow_up WHERE funnel_id="'.(int)$row->funnel_id.'" AND email=NULL');
          $totalNotEmailed+=$notEmailed;
          $complete=$wpdb->get_var('SELECT COUNT(*)  FROM '.$wpdb->prefix.'church_admin_follow_up WHERE funnel_id="'.(int)$row->funnel_id.'" AND completion_date!=NULL');
           $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=people&amp;action=edit_funnel&section=attendance&amp;funnel_id='.(int)$row->funnel_id,'edit_funnel').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
				   $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=people&amp;action=delete_funnel&section=attendance&amp;funnel_id='.(int)$row->funnel_id,'delete_funnel').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';

            echo'<tr  id="'.(int)$row->funnel_id.'"><td data-colname="'.esc_html( __('Funnel','church-admin' ) ).'" class="column-primary">'.esc_html( $row->action).'<button type="button" class="toggle-row">
            <span class="screen-reader-text">show details</span></button></td>';
            echo '<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>';
            echo '<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>';
			if(!empty( $member_types[$row->member_type_id] ) )  {echo '<td data-colname="'.esc_html( __('Applies to','church-admin' ) ).'">'.esc_html( $member_types[$row->member_type_id] ).'</td>';}else{echo'<td>&nbsp;</td>';}
			if(!empty( $ministries[$row->department_id] ) )  {echo '<td data-colname="'.esc_html( __('Ministry responsible','church-admin' ) ).'">'.$ministries[$row->department_id].'</td>';}else{echo'<td>&nbsp;</td>';}
            echo'<td data-colname="'.esc_html( __('Active','church-admin' ) ).'">'.(int)$active.'</td>';
            echo'<td data-colname="'.esc_html( __('Not yet emailed','church-admin' ) ).'">'.(int)$notEmailed.'</td>';
            echo'<td data-colname="'.esc_html( __('Completed','church-admin' ) ).'">'.(int)$completed.'</td>';
            echo'</tr>';

        }
		    echo'</tbody><tfoot>'.$theader.'</tfoot></table>';
          if( $totalNotEmailed)echo'<p><a class="button-secondary"   href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=attendance&amp;action=email_follow_up_activity','email_funnels').'">'.esc_html( __('Email newly assigned follow-up activity','church-admin' ) ).'</a></p>';
 
  church_admin_my_follow_ups();
    }
}
/**
 * Delete a funnel
 *
 * @param $funnel_id
 *
 *
 * @author andy_moyle
 *
 */
function church_admin_delete_funnel( $funnel_id=NULL)
{
	global $wpdb;
	if(!empty( $funnel_id)&&church_admin_int_check( $funnel_id) )
	{
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_funnels WHERE funnel_id="'.(int)$funnel_id.'"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_follow_up WHERE funnel_id="'.(int)$funnel_id.'"');
		echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Follow Up Funnel Deleted','church-admin' ) ).'</strong></p></div>';
		church_admin_funnel_list();
	}
}


/**
 * Edit a funnel
 *
 * @param
 *
 *
 * @author andy_moyle
 *
 */
function church_admin_edit_funnel( $funnel_id=NULL,$people_type_id=1)
{
    global $wpdb,$people_type,$member_types,$ministries;
 
    


    echo'<h2>';
        if( $funnel_id)  {echo __('Edit','church-admin'); $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_funnels WHERE funnel_id="'.(int)$funnel_id.'"');}else{echo __('Add','church-admin');}
        echo' '.esc_html( __('Follow Up Funnel','church-admin' ) ).'</h2>';

        if(isset( $_POST['edit_funnel'] ) )
        {//process form
            //deal with new department
            if(!empty( $_POST['new_ministry'] )&&$_POST['new_ministry']!=__('Or add a new ministry','church-admin') )
            {
                if(!in_array(sanitize_text_field(stripslashes( $_POST['new_new_ministry'] )),$ministries) )
                {
                    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry) VALUES ("'.esc_sql(sanitize_text_field( stripslashes($_POST['new_ministry'] ) ) ).'")');
                    $ministries[]=sanitize_text_field(stripslashes( $_POST['new_new_ministry']) );
                }
            }
            if(!$funnel_id)$funnel_id=$wpdb->get_var('SELECT funnel_id FROM '.$wpdb->prefix.'church_admin_funnels WHERE action="'.esc_sql(sanitize_text_field( $_POST['action'] ) ).'" AND member_type_id="'.esc_sql((int)sanitize_text_field(stripslashes( $_POST['member_type_id'] ) )).'"');
            if( $funnel_id)
            {//update
                $success=$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_funnels SET people_type_id="'.esc_sql( $people_type_id).'", action="'.esc_sql(sanitize_text_field( $_POST['action'] ) ).'",member_type_id="'.esc_sql((int)sanitize_text_field(stripslashes($_POST['member_type_id'] ) )).'",department_id="'.esc_sql((int)sanitize_text_field(stripslashes( $_POST['ministry_id'] )) ).'",funnel_order="'.(int)sanitize_text_field(stripslashes($_POST['funnel_order'])).'" WHERE funnel_id="'.(int)$funnel_id.'"');
            }//end update
            else
            {//insert
                $success=$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_funnels (action,member_type_id,department_id,people_type_id,funnel_order)VALUES("'.esc_sql(sanitize_text_field( $_POST['action'] ) ).'" ,"'.esc_sql((int)sanitize_text_field(stripslashes( $_POST['member_type_id'] )) ).'","'.esc_sql((int)sanitize_text_field(stripslashes( $_POST['ministry_id'] ) )).'","'.esc_sql( $people_type_id).'","'.(int)sanitize_text_field(stripslashes($_POST['funnel_order'])).'")');
            }//insert
            echo '<div class="notice notice-success inline"><p>'.esc_html( __('Funnel Updated','church-admin' ) ).'</p></div>';
            church_admin_funnel_list( $people_type_id);
        }//end process form
        else
        {//form
           echo'<form action="" method="POST">';

           //funnel action
           echo'<table class="form-table"><tbody><tr><th scope="row">'.esc_html( __('Funnel Action','church-admin' ) ).'</th><td><input type="text" name="action" ';
           if(!empty( $data->action) )echo ' value="'.esc_html( $data->action).'" ';
           echo'/></td></tr>';
           //funnel order
           echo'<tr><th scope="row">'.esc_html( __('Funnel order','church-admin' ) ).'</th><td><input type="number" name="funnel_order" ';
           if(!empty( $row->funnel_order) )  {echo ' value="'.(int)$row->funnel_order.'" ';}else{echo ' value=1 ';}
           echo'/></td></tr>';
           //member type
           echo'<tr><th scope="row">'.esc_html( __('Link to Member Type','church-admin' ) ).'</th><td><select name="member_type_id">';
           $first='<option value="">'.esc_html( __('Please select member type','church-admin' ) ).'</option>';
           $option='';
           foreach( $member_types AS $id=>$type)
           {
             if( $id==$data->member_type_id)  {$first='<option value="'.(int)$id.'" selected="selected">'.esc_html( $type).'</option>'; }else{$option.='<option value="'.(int)$id.'" >'.esc_html( $type).'</option>';}
           }
           echo $first.$option.'</option></select></td></tr>';
           //responsible department
           echo'<tr><th scope="row">'.esc_html( __('Ministry responsible for action','church-admin' ) ).'</th><td><select name="ministry_id">';
           $first=$option='';
           foreach( $ministries AS $id=>$type)
           {
             if( $id==$data->member_type_id)  {$first='<option value="'.(int)$id.'" selected="selected">'.esc_html( $type).'</option>'; }else{$option.='<option value="'.(int)$id.'" >'.esc_html( $type).'</option>';}
           }
           echo $first.$option.'</option></select></td></tr>';
           echo '<tr><th scope="row">'.esc_html( __('Or create a new ministry','church-admin' ) ).'</th><td><input type="text" name="new_ministry" onfocus="javascript:this.value=\'\';" value="'.esc_html( __('Or add a new ministry','church-admin' ) ).'" /></td></tr>';

           echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="edit_funnel" value="yes" /><input class="button-primary" type="submit" value="'.esc_html( __('Save Follow Up Funnel','church-admin' ) ).' &raquo;" /></td></tr></tbody></table></form>';
        }//form

}


function church_admin_follow_up_completed( $id)
{
    global $wpdb;
    $followUpID=$wpdb->get_var('SELECT id FROM  '.$wpdb->prefix.'church_admin_follow_up WHERE md5(CONCAT("follow_up",`id`) )="'.esc_sql( $id).'"');
    if( $followUpID)
    {
      $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_follow_up SET completion_date="'.esc_sql(wp_date('Y-m-d')).'" WHERE id="'.esc_sql( $followUpID).'"');
      echo '<div class="notice notice-inline notice-success"><h2>Funnel Completed</h2></div>';
      church_admin_my_follow_ups();
    }else
    {
        wp_exit(__('Follow up task not found','church-admin') );
    }

}

function church_admin_my_follow_ups()
{
    global $wpdb,$current_user;
    $user_id=get_current_user_id();
    $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user_id.'"');
    if( $people_id)
    {
      echo'<h2>'.esc_html( __('My follow up tasks','church-admin' ) ).'</h2>';
      $tasks=$wpdb->get_results( $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_follow_up  LEFT JOIN '.$wpdb->prefix.'church_admin_funnels ON '.$wpdb->prefix.'church_admin_follow_up.funnel_id = '.$wpdb->prefix.'church_admin_funnels.funnel_id LEFT JOIN '.$wpdb->prefix.'church_admin_people ON '.$wpdb->prefix.'church_admin_follow_up.people_id = '.$wpdb->prefix.'church_admin_people.people_id LEFT JOIN '.$wpdb->prefix.'church_admin_household ON '.$wpdb->prefix.'church_admin_people.household_id = '.$wpdb->prefix.'church_admin_household.household_id WHERE '.$wpdb->prefix.'church_admin_follow_up.assign_id="'.(int)$people_id.'" AND '.$wpdb->prefix.'church_admin_follow_up.completion_date="0000-00-00"');

      if( $tasks)
      {
          echo'<table class="widefat"><thead><tr><th>'.esc_html( __('Follup Up Task','church-admin' ) ).'</th><th>'.esc_html( __('Who','church-admin' ) ).'</th><th>'.esc_html( __('Completed','church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('Follup Up Task','church-admin' ) ).'</th><th>'.esc_html( __('Who','church-admin' ) ).'</th><th>'.esc_html( __('Completed','church-admin' ) ).'</th></tr></tfoot><tbody>';
          foreach( $tasks AS $task)
          {
            echo '<tr><td>'.esc_html( $task->action).'</td><td>'.esc_html( $task->first_name.' '.$task->last_name).'</td><td><a href="'.admin_url().'?page=church_admin/index.php&section=attendance&amp;action=follow_up_completed&id='.md5('follow_up'.$task->id).'">'.esc_html( __("Completed",'church-admin' ) ).'</a></td></tr>';
          }
          echo'</tbody></table>';
      }
      else{echo '<p>'.esc_html( __('No follow up taks for you currently','church-admin' ) ).'</p>';}
    }
}
