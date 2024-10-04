<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_recent_people_activity()
{
    global $wp,$wpdb,$people_type;
	$member_type=church_admin_member_types_array();
    $out='';

    // number of total rows in the database
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.class.php');
    $items=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people');
    if( $items > 0)
    {
        echo '<hr/><h2><a id="recent_people">'.esc_html( __('Recent People Activity','church-admin' ) ).'</a></h2>';
        echo'<p><a href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=church_admin_email_follow_up_activity','email_funnels').'">'.esc_html( __('Email newly assigned follow-up activity','church-admin' ) ).'</a></p>';
        $p = new caPagination;
        $p->items( $items);
        $p->limit(get_option('posts_per_page') ); // Limit entries per page
        if(is_admin() )  {$p->target(wp_nonce_url("admin.php?page=church_admin/index.php&section=people&action=people-activity",'people-activity'));}else{$p->target=home_url( $wp->request );}
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
        $limit = "LIMIT " . ( $p->page - 1) * $p->limit  . ", " . $p->limit;
            $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people ORDER BY last_updated DESC '.$limit);
            if( $results)
            {
                if(defined('CA_DEBUG') )church_admin_debug(print_r( $results,TRUE) );
                // Pagination

                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo $p->getOutput();
                echo '</div></div>';
                //prepare table
                $theader='<tr><th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Member Level','church-admin' ) ).'</th><th>'.esc_html( __('Follow Up Action','church-admin' ) ).'</th><th>'.esc_html( __('Mobile','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th><th>'.esc_html( __('Last Updated','church-admin' ) ).'</th></tr>';
               echo '<table class="widefat striped table table-bordered table-striped wp-list-table"><thead>'.$theader.'</thead><tfoot>'.$theader.'</tfoot><tbody>';
                foreach( $results AS $row)
                {
                    $fun_display='';
                    $sql='SELECT a.*,b.action,CONCAT_WS(" ",c.first_name,c.last_name) AS name FROM '.$wpdb->prefix.'church_admin_follow_up a, '.$wpdb->prefix.'church_admin_funnels b,'.$wpdb->prefix.'church_admin_people c WHERE a.people_id="'.esc_sql( $row->people_id).'" AND a.member_type_id="'.esc_sql( $row->member_type_id).'" AND b.funnel_id=a.funnel_id AND c.people_id=a.assign_id';

                    $funnel=$wpdb->get_row( $sql);
                    if( $funnel)
                    {//funnel has been assigned already
                        $fun_display=esc_html(sprintf(__('%1$s assigned to %2$s on %3$s','church-admin' ) , $funnel->action,$funnel->name,mysql2date(get_option('date_format'),$funnel->assigned_date) ));
                        if( $funnel->completion_date!='0000-00-00')$fun_display.= esc_html(sprintf(__('completed on %1$s','church-admin' ) ,mysql2date(get_option('date_format'),$funnel->completion_date)));
                    }
                    else
                    {
                        $funnel_id=$wpdb->get_var('SELECT funnel_id FROM '.$wpdb->prefix.'church_admin_funnels WHERE member_type_id="'.esc_sql( $row->member_type_id).'" LIMIT 1');
                        if( $funnel_id)  {$fun_display.=church_admin_funnel_assign( $row->people_id,$funnel_id,$row->member_type_id);}else{$fun_display.='';}

                    }
                    $edit='<a href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.$row->people_id,'edit_people').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
                    $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=delete_people&amp;people_id='.$row->people_id,'delete_people').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
                    $mt='&nbsp;';
                    if(!empty( $member_type[$row->member_type_id] ) )$mt=$member_type[$row->member_type_id];
                    
                    echo '<tr><td class="column-primary" data-colname="'.esc_html( __('Name','church-admin' ) ).'">'.esc_html( $row->first_name).' <strong>'. esc_html( $row->last_name).'</strong><button type="button" class="toggle-row">
                    <span class="screen-reader-text">show details</span>
                </button></td><td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td><td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td><td data-colname="'.esc_html( __('Member type','church-admin' ) ).'">'.$mt.'</td><td class="funnel-display" data-colname="'.esc_html( __('Funnel','church-admin' ) ).'" id="person'.(int)$row->people_id.'">'.$fun_display.'</td><td data-colname="'.esc_html( __('Cell','church-admin' ) ).'">'.esc_html( $row->mobile).'</td><td data-colname="'.esc_html( __('Email','church-admin' ) ).'">';
                   echo '';
                    //only provide email link if actually an email
                    if(is_email( $row->email) ) {echo'<a href="mailto:'.$row->email.'">'.esc_html( $row->email).'</a>';}else{echo esc_html( $row->email);}
                    echo '</td><td data-colname="'.esc_html( __('Last update','church-admin' ) ).'">'.mysql2date(get_option('date_format'),$row->last_updated).'</td></tr>';

                }
                echo '</tbody></table>';
                // Pagination

                echo  '<div class="tablenav"><div class="tablenav-pages">';
                echo $p->getOutput();
                $nonce = wp_create_nonce("assign_funnel");
                echo  '</div></div>';
                echo'<script>';
                echo'jQuery(document).ready(function( $)  {
                    $(".funnel").on("change",function()  {
                        var people_id=$(this).data("people_id");
                        var funnel_id=$(this).data("funnel_id");
                        var member_type_id=$(this).data("member_type_id");
                        var assign_id=$(this).val();

                        var data = {
                            "action": "church_admin",
                            "method": "assign_funnel",
                            "people_id": people_id,
                            "funnel_id": funnel_id,
                            "assign_id": assign_id,
                            "member_type_id": member_type_id,
                            "nonce": "'.$nonce.'"
                        };
                        console.log(data);
                        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                        jQuery.getJSON(ajaxurl, data, function(response) {console.log(response);
                            console.log("person"+response.people_id);
                            $("#person"+response.people_id).html(response.message);
                        });
                    });
                })</script>';
        }
    }

return $out;

}
function church_admin_funnel_assign( $people_id,$funnel_id,$member_type_id)
{
       //returns form to assign someone to action a particular funnel for a particular person
    global $wpdb;
	$fun_display='';
    $funnel_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_funnels WHERE funnel_id="'.esc_sql( $funnel_id).'"');
    if( $funnel_details)
    {

        $people=$wpdb->get_results('SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id AS people_id FROM '.$wpdb->prefix.'church_admin_people a,'.$wpdb->prefix.'church_admin_people_meta b WHERE b.meta_type="ministry" AND b.ID="'.esc_sql( $funnel_details->department_id).'" AND b.people_id=a.people_id ORDER BY a.last_name');
        if( $people)
        {//people available to assign to
            $fun_display.='<p>'.esc_html(sprintf(__('Assign %1$s to ','church-admin' ) , $funnel_details->action) ).': <select name="assign_id" class="funnel" data-people_id="'.(int)$people_id.'" data-funnel_id="'.intval( $funnel_id).'" data-member_type_id="'.intval( $member_type_id).'">';
            $fun_display.='<option value="">'.esc_html( __('Select someone...','church-admin' ) ).'</option>';
            foreach ( $people AS $person)
            {
                $fun_display.='<option value="'.(int)$person->people_id.'">'.esc_html( $person->name).'</option>';
            }
            $fun_display.='</select>';
        }
    }
    return $fun_display;
}

function church_admin_assign_funnel()
{
    //uses form data to adjust persons funnel data
    global $wpdb;

    ;

    if ( empty( $_POST['people_id'] ) || empty( $_POST['funnel_id'] ) || empty( $_POST['assign_id'] ) || !church_admin_int_check( $_POST['people_id'] )||!church_admin_int_check( $_POST['funnel_id'] )||!church_admin_int_check( $_POST['assign_id'] ) )
    {
        echo'<div class="notice notice-success inline"><p>'.esc_html( __("Couldn't process data",'church-admin' ) ).'</p></strong></div>'.church_admin_recent_people_activity();
    }
    else
    {
        $funnel_id=church_admin_sanitize($_POST['funnel_id']);
        $people_id=church_admin_sanitize($_POST['people_id']);
        $assign_id=church_admin_sanitize($_POST['assign_id']);
        $member_type_id=church_admin_sanitize($_POST['member_type_id']);
        $assign_date=wp_date('Y-m-d');

        $sql='INSERT INTO '.$wpdb->prefix.'church_admin_follow_up' .'(funnel_id,people_id,member_type_id,assign_id,assigned_date,completion_date)VALUES("'.(int)$funnel_id.'","'.(int)$people_id.'","'.(int)$member_type_id.'","'.(int)$assign_id.'","'.(int)$assign_date.'","0000-00-00")';

        $wpdb->query( $sql);
        echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Follow up funnel assigned','church-admin' ) ).'</strong></p></div>';
        church_admin_recent_people_activity();
    }
}

function church_admin_email_follow_up_activity()
{
    global $wpdb;
    add_filter('wp_mail_content_type','church_admin_email_type');
    //grab ids of people with assigned follow-up actions
    $sql='SELECT DISTINCT assign_id FROM '.$wpdb->prefix.'church_admin_follow_up WHERE email="0000-00-00" ';

    $results=$wpdb->get_results( $sql);
if( $results)
{

    echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Follow Up activities emailed to...','church-admin' ) ).'<br>';
    foreach( $results AS $row)
    {

        $assign=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.esc_sql( $row->assign_id).'"');
        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_follow_up  LEFT JOIN '.$wpdb->prefix.'church_admin_funnels ON '.$wpdb->prefix.'church_admin_follow_up.funnel_id = '.$wpdb->prefix.'church_admin_funnels.funnel_id LEFT JOIN '.$wpdb->prefix.'church_admin_people ON '.$wpdb->prefix.'church_admin_follow_up.people_id = '.$wpdb->prefix.'church_admin_people.people_id LEFT JOIN '.$wpdb->prefix.'church_admin_household ON '.$wpdb->prefix.'church_admin_people.household_id = '.$wpdb->prefix.'church_admin_household.household_id WHERE '.$wpdb->prefix.'church_admin_follow_up.assign_id="'.(int)$row->assign_id.'" AND '.$wpdb->prefix.'church_admin_follow_up.email="0000-00-00"';

        $re=$wpdb->get_results( $sql);
        $message='<p>Hi '.$assign->first_name.' '.$assign->last_name.',</p><p>'.esc_html( __("You've been assigned some follow up actions",'church-admin' ) ).'</p>';
        foreach( $re AS $f_row)
        {
            $message.='<h2>'.$f_row->action.' '.esc_html( __('assigned on','church-admin' ) ).' '.mysql2date(get_option('date_format'),$f_row->assigned_date).'</h2>';
            $message.='<table><tr><td>Name</td><td>'.esc_html( $f_row->first_name.' '.$f_row->last_name).'</td></tr>';
            if(!empty( $f_row->address) )$message.='<tr><td>'.esc_html( __('Address','church-admin' ) ).'</td><td>'.esc_html( $f_row->address).'</td></tr>';
            if(!empty( $f_row->email)&&is_email( $f_row->email) )$message.='<tr><td>'.esc_html( __('Email','church-admin' ) ).'</td><td><a href="mailto:'.$f_row->email.'">'.$f_row->email.'</a></td></tr>';
            if(!empty( $f_row->mobile) )$message.='<tr><td>'.esc_html( __('Mobile','church-admin' ) ).'</td><td>'.esc_html( $f_row->mobile).'</td></tr>';
            if(!empty( $f_row->phone) )$message.='<tr><td>'.esc_html( __('Phone','church-admin' ) ).'</td><td>'.esc_html( $f_row->phone).'</td></tr>';
            $message.='</table>';
            $message.='<a href="'.admin_url().'?page=church_admin/index.php&amp;action=follow_up_completed&id='.md5('follow_up'.$f_row->id).'">'.esc_html( __("Please click here to log completion",'church-admin' ) ).'</a>';
        }
        echo esc_html( $assign->first_name.' '.$assign->last_name).'<br>';

        church_admin_email_send( $assign->email,esc_html(__("You've been assigned some follow up tasks",'church-admin' ) ),$message,null,null,null,null,null,FALSE);
       
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_follow_up SET email="'.esc_sql(wp_date('Y-m-d')).'" WHERE assign_id="'.esc_sql( $assign->people_id).'" AND email="0000-00-00"');
    }
    echo'</strong></p></div>';
}
    else{echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Follow Up activities did not need to be emailed','church-admin' ) ).'</strong></p></div>';}
    church_admin_recent_people_activity();
}
