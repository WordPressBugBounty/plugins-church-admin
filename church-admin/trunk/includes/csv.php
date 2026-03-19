<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly



/**
 *
 * outputs address list csv according to filters
 *
 * @author  Andy Moyle
 * @param
 * @return   application/octet-stream
 * @version  1.03
 *
 * rewritten 7th July 2016 to use filters from filter.php
 * refactored 11th April 2016 to remove multi-service bug
 *
 */
function church_admin_people_csv()
{
	global $wpdb;
	$group_by='';
	$gdpr=$custom=$months=$years=$member_types=$parents=$genders=$people_types=$sites=$smallgroups=$ministries=array();
	$customSQL=$monthSQL=$yearSQL=$marritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
	$gdprSQL='';
	$people_type=get_option('church_admin_people_type');
    $member_types=church_admin_member_types_array();
	require_once('filter.php');
    $checked_boxes = !empty($_REQUEST['check'])?church_admin_sanitize($_REQUEST['check'] ):null;
	$sql= church_admin_build_filter_sql( $checked_boxes );

	$gender=get_option('church_admin_gender');
    $custom_fields=church_admin_get_custom_fields();
   
	$results=$wpdb->get_results( $sql);

	if(!empty( $results) )
	{

		$table_header=array(esc_html( __('Household ID','church-admin' ) ),
        esc_html( __('People ID','church-admin' ) ),
        esc_html( __('First name','church-admin' ) ),
        esc_html( __('Last name','church-admin' ) ),esc_html( __('Date of birth','church-admin' ) ),esc_html( __('People type','church-admin' ) ),esc_html( __('Member Type','church-admin' ) ),esc_html( __('Marital status','church-admin' ) ),esc_html( __('Phone','church-admin' ) ),esc_html( __('Cellphone','church-admin' ) ),esc_html( __('Email','church-admin' ) ),esc_html( __('Address','church-admin' ) ),esc_html( __('Venue','church-admin' ) ),esc_html( __('Gender','church-admin' ) ),esc_html( __('Is head of household','church-admin' ) ),esc_html(__('Date of birth','church-admin')),esc_html(__('Wedding Anniversary','church-admin')),esc_html( __('Household last name','church-admin' ) ),esc_html( __('Head of household full name','church-admin') ));
        foreach($custom_fields AS $id=>$name)
        {
            $table_header[]=$name['name'];
        }
        //church_admin_debug($table_header);
		$csv='"'.iconv('UTF-8', 'ISO-8859-1',implode('","',$table_header) ).'"'."\r\n";
		foreach( $results AS $row)
		{
            $head=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$row->household_id.'" AND head_of_household=1');
            $csv.='"'.(int)$row->household_id.'",';
            $csv.='"'.(int)$row->people_id.'",';
			if(!empty( $row->first_name) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->first_name).'",';}else $csv.='"",';
			if(!empty( $row->last_name) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->last_name).'",';}else $csv.='"",';
			if(!empty( $row->date_of_birth)&&$row->date_of_birth!="0000-00-00")  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->date_of_birth).'",';}else $csv.='"",';
			if(!empty( $people_type[$row->people_type_id] ) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$people_type[$row->people_type_id] ).'",';}else $csv.='"",';
            if(!empty( $member_types[$row->member_type_id] ) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$member_types[$row->member_type_id] ).'",';}else $csv.='"",';
			if(!empty( $row->marital_status) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->marital_status).'",';}else $csv.='"'.esc_html( __('N/A','church-admin' ) ).'",';
			if(!empty( $row->phone) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->phone).'",';}else $csv.='"",';
			if(!empty( $row->mobile) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->mobile).'",';}else $csv.='"",';
			if(!empty( $row->email) )  {$csv.='"'.$row->email.'",';}else $csv.='"",';
			if(!empty( $row->address) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->address).'",';}else $csv.='"",';
			if(!empty( $row->venue) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->venue).'",';}else $csv.='"",';
			//if(!empty( $row->group_name) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->group_name).'",';}else $csv.='"",';
			if(isset( $row->sex) )  {$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$gender[$row->sex] ).'",';}else $csv.='"",';
            if(!empty( $row->head_of_household) )  {$csv.='"1",';}else{$csv.='"0",';}
            if(!empty($row->date_of_birth)){$csv.='"'.$row->date_of_birth.'",';}else{$csv.='"",';}
            if(!empty($row->wedding_anniversary)){$csv.='"'.$row->wedding_anniversary.'",';}else{$csv.='"",';}
            $csv.='"'.iconv('UTF-8', 'ISO-8859-1',$head->last_name).'",';
            $csv.='"'.iconv('UTF-8', 'ISO-8859-1',church_admin_formatted_name( $head) ).'",';

            foreach( $custom_fields AS $ID=>$field)
            {
                church_admin_debug('CUSTM ID '.$ID);
                //church_admin_debug($field);
                if( $field['section']!='people') continue;
                if( $field['show_me']!=1) continue;

                //note people_id on the $wpdb->prefix.'church_admin_custom_fields_meta' can have the value of household_id!
                
                $thisData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$ID.'" AND people_id="'.(int)$row->people_id.'"');
                //church_admin_debug($wpdb->last_query);
                //church_admin_debug($thisData);
                switch( $field['type'] )
                {
                    case 'boolean':
                        if(!empty( $thisData->data) )  {$csv.='"'.esc_html( __('Yes','church-admin' ) ).'",';}else{$csv.='"'.esc_html( __('No','church-admin' ) ).'",';}
                    break;
                    case 'date':
                        if(!empty( $thisData->data) )  {$csv.='"'.mysql2date(get_option('date_format'),$thisData->data).'",';}else{$csv.='"",';}
                    break;
                    default:
                        if(!empty( $thisData->data) )  {$csv.='"'.esc_html( $thisData->data).'",';}else{$csv.='"",';}
                    break;
                }
               
            }



			$csv.="\r\n";
		}

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="filtered-address-list.csv"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header("Content-Disposition: attachment; filename=\"filtered-address-list.csv\"");
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $csv;
	}
	exit();



}


