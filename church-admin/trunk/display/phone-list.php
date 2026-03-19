<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_frontend_phone_list( $people_type_id=NULL,$member_type_id=NULL)
{

    global $wpdb;
    $out='';
    
    $memb_sql='';
    $membsql=$sitesql=array();
    if( $member_type_id=="#"||empty( $member_type_id) )  {$memb_sql="";}
    elseif( $member_type_id!="")
    {
  		$memb=explode(',',$member_type_id);
      	foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.esc_sql($value);}
      	if(!empty( $membsql) ) {$memb_sql=' ('.implode(' || ',$membsql).')';}
	}
    if( $people_type_id=="#"||empty( $people_type_id) )  {$ptype_sql="";}
    elseif( $people_type_id!="")
    {
  		$peop=explode(',',$peop_type_id);
      	foreach( $peop AS $key=>$value)  {if(church_admin_int_check( $value) )  $ptypesql[]='a.people_type_id='.esc_sql($value);}
      	if(!empty( $ptypesql) ) {$peop_sql=' ('.implode(' || ',$ptypesql).')';}
	}
    
    $sql='SELECT a.*,b.phone FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_household b ON a.household_id=b.household_id WHERE a.active=1 AND b.privacy=0 ';
    if(!empty( $memb_sql) ) $sql.=' AND '.$memb_sql;
    if(!empty( $peop_sql) ) $sql.=' AND '.$peop_sql;
    $sql.=' ORDER BY a.last_name, a.first_name ASC';
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        
        $out.='<table class="table table-bordered table-striped"><thead><tr><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Landline','church-admin' ) ).'</th><th>'.esc_html( __('Cell','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Landline','church-admin' ) ).'</th><th>'.esc_html( __('Cell','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th></tr></tfoot><tbody>';
        foreach ( $results AS $row)
        {
            $privacy=unserialize($row->privacy);
            $name=implode(" ",array_filter(array( $row->first_name,$row->middle_name,$row->prefix,$row->last_name) ));
            if(!empty( $row->phone) &&!empty($privacy['show-landline']))
            {
                $landline='<a href="'.esc_url('call:'. $row->phone).'">'.esc_html( $row->phone).'</a>';
            }
            else
            {
                $landline='&nbsp;';
            }
            if(!empty( $row->mobile)  &&!empty($privacy['show-cell']))
            {
                $cell='<a href="'.esc_url('call:'.$row->mobile).'">'.esc_html( $row->mobile).'</a>';
            }
            else
            {
                $cell='&nbsp;';
                                                                                                     
            }
            if(!empty( $row->email)  &&!empty($privacy['show-email']))
            {
                $email='<a href="'.esc_url('mailto:'.antispambot( $row->email) ).'">'.esc_html( antispambot($row->email)).'</a>';
            }
            else
            {
                $email='&nbsp;';
            }
            $out.='<tr><td>'.esc_html( $name).'</td><td>'.$landline.'</td><td>'.$cell.'</td><td>'.$email.'</td></tr>';
        }
        $out.='</tbody></table>';
        
    }else
    {
        $out.='<p>'.esc_html( __('No entries found','church-admin' ) ).'</p>';    
    }
    
    return $out; 
}