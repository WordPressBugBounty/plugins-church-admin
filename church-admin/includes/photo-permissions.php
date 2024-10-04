<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/****************************
*   Photo permissions page
*****************************/

function church_admin_photo_list( $member_type_id=NULL)
{
    global $wpdb;
    
    $out='<h2>'.esc_html( __('Photo Permissions','church-admin' ) ).'</h2>';
    
    
    $memb_sql='';
    $membsql=array();
    if ( empty( $member_type_id)||$member_type_id==__('All','church-admin')||$member_type_id=="#")  {$memb_sql="";}
    elseif( $member_type_id!="")
    {
  		$memb=explode(',',$member_type_id);
        foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.$value;}
        if(!empty( $membsql) ) {$memb_sql=' ('.implode(' || ',$membsql).')';}
    }
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people '.$memb_sql.' ORDER BY photo_permission DESC,last_name ASC,first_name ASC');
   
    if ( empty( $results) )
    {
        $out.=__('No one in the directory with your parameters','church-admin');
        return $out;
    }
    $yesTitle=1;
    $noTitle=1;  
    $out.='<table class="table table-bordered widefat"><tbody>';
    foreach( $results AS $row)
    {
        /*************************************
        *   Output subtitle for 
        **************************************/
        if( $yesTitle && $row->photo_permission)
        {
            $out.='<tr><th colspan=2><h3>'.esc_html( __("People who allow photos",'church-admin' ) ).'</h3></th></tr>';
            $yesTitle=0;
            
        }
        if( $noTitle && !$row->photo_permission)
        {
            $out.='<tr><th colspan=2><h3>'.esc_html( __("No photos permission",'church-admin' ) ).'</h3></th></tr>';
            $noTitle=0;
        }
        /**************************************
        *   Image 
        ***************************************/
        if( $row->photo_permission && $row->attachment_id)
        {
            $image=wp_get_attachment_image( $row->attachment_id,'thumbnail');
        }
        else $image='<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="150" height="150"  alt="'.esc_html( __('No photo yet','church-admin' ) ).'"  />';
        $name=esc_html(implode(" ",array_filter(array( $row->first_name,$row->prefix,$row->last_name) )) );
        $out.='<tr><td>'.$image.'</td><td>'.$name.'</td></tr>';
        
    }
    $out.='</tbody></table>';
    return $out;
}

function church_admin_photo_permission_pdf_form()
{

   $out = '<h2>'.esc_html(__('Photo Permissions PDF form','church-admin'));
   $out .= '<form action="'.site_url().'" method="GET"><input type="hidden" name="ca_download" value="photo-permissions-pdf" />';
   $out.=wp_nonce_field('photo-permissions-pdf');
   $out .= '<p><input type="checkbox" name="people_type_id[]" value="1" /> '.esc_html(__('Adults','church-admin')).'</p>';
   $out .= '<p><input type="checkbox" name="people_type_id[]" value="2" /> '.esc_html(__('Children','church-admin')).'</p>';
   $out .= '<p><input type="checkbox" name="people_type_id[]" value="3" /> '.esc_html(__('Teenagers','church-admin')).'</p>';
   $out .= '<p><input type="submit" class="button-primary" value="'.esc_attr(__('Create PDF','church-admin')).'" /></p>';
   $out .= '</form>';
   return $out;



}