<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_recent_display( $weeks=4,$member_type_id=NULL)
{
    global $wpdb;

    $weeks = !empty($weeks) ? (int)$weeks : 4;


    $out='<div class="church-admin-recent"><h2>'.esc_html( __('Recent address list activity','church-admin' ) ).'</h2>';
    $shownMember_types=array();
    $AllMember_types=church_admin_member_types_array();
	if ( empty( $member_type_id) )$shownMember_types=$AllMember_types;
    else
    {
        $givenMember_types=explode(',',$member_type_id);
        foreach( $givenMember_types AS $key=>$Mtype)
        {
            $shownMember_types[$Mtype]=$AllMember_types[$Mtype];
        }
    }
 
    foreach( $shownMember_types AS $type_id=>$type)
    {
        $sql='SELECT a.last_updated AS lastUpdate,a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.last_updated>DATE_SUB(NOW(), INTERVAL '.(int) $weeks.' WEEK) AND a.member_type_id ="'.(int) $type_id.'"';
        
        $results=$wpdb->get_results( $sql);
       church_admin_debug(print_r( $results,TRUE) );
		$out.='<h3>'.esc_html(sprintf(__('%1$s address list activity in the last %2$s weeks','church-admin' ) ,$type,(int)$weeks)).'</h3>';
		if( $results)
        {
            
            $out.='<table  class="church_admin"> <thead><tr><th>'.esc_html( __('Last Updated','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Address','church-admin' ) ).'</th><th>'.esc_html( __('Mobile','church-admin' ) ).'</th><th>'.esc_html( __('Phone','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('LAst Updated','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Address','church-admin' ) ).'</th><th>'.esc_html( __('Mobile','church-admin' ) ).'</th><th>'.esc_html( __('Phone','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th></tr></tfoot><tbody>';
            foreach( $results AS $row)
            {
                
                
               $out.='<tr><td>'.mysql2date(get_option('date_format'),$row->lastUpdate).'</td><td>'.esc_html( $row->first_name.' '.$row->last_name).'</td><td>'.esc_html( $row->address).'</td><td>'.esc_html( $row->mobile).'</td><td>'.esc_html( $row->phone).'</td><td>'.esc_html( $row->email).'</td></tr>';
            }
            $out.='</tbody></table>';
        }else{$out.='<p>'.esc_html( __('No new address list activity','church-admin' ) ).'</p>';}
    }
    $out.='</div>';
   return $out; 
    
}

