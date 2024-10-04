<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/*********************************
 * 
 * COVID booking CSV
 * 
 *********************************/
function church_admin_service_booking_csv( $date_id,$service_id,$alphabetical=FALSE)
{
  
    global $wpdb;
    $nextservice=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$date_id.'"');
    if(defined('CA_DEBUG') )church_admin_debug(print_r( $nextservice,true) );
    $serviceDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE service_id="'.(int)$service_id.'" AND date_id="'.(int)$date_id.'" ORDER BY people_id ASC';
    $results=$wpdb->get_results( $sql);
    
    if(!empty( $results) )
    {
        
       
        
        
        $csv='"'.esc_html( __('No','church-admin' ) ).'","'.esc_html( __('Bubble','church-admin' ) ).'","'.esc_html( __('Name','church-admin' ) ).'","'.esc_html( __('Email','church-admin' ) ).'","'.esc_html( __('Phone','church-admin' ) ).'"'."\r\n";
        $x=1;
        
        if(!empty( $alphabetical) )
        {
            $newArray=array();
            foreach( $results AS $row)
            {
                $splitname=explode(" ",$row->people_id);
                if(!empty( $splitname[1] ) )
                {
                    $key=$splitname[1].', '.$splitname[0];
                }else{$key=$splitname[0];}
                $newArray[$key]=$row;
                $newArray[$key]->people_id=$key;
            }
            ksort( $newArray);
            $results=$newArray;
        }
        foreach( $results AS $row)
        {
            $csv.='"'.$x.'","'.(int)$row->bubble_id.'","'.$row->people_id.'","'.$row->email.'","'.$row->phone.'"'."\r\n";
            
        }
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="service-prebooking.csv"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header("Content-Disposition: attachment; filename=\"service-prebooking.csv\"");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $csv;
		exit();
    }
}
/**********************************
 *
 * Giving CSV
 *
 **********************************/
function church_admin_giving_csv( $start_date,$end_date,$people_id)
{
    church_admin_debug('**** function church_admin_giving_csv ****');
    if(empty($start_date)||!church_admin_checkdate($start_date)){exit(__('No start date given'));}
    if(empty($end_date)||!church_admin_checkdate($end_date)){exit(__('No end date given'));}
    if(!empty($people_id) && church_admin_int_check($people_id)){ 
        $peopleSQL = ' AND a.people_id = "'.(int)$people_id.'" ';
    }else{$peopleSQL='';}

    global $wpdb;
    
    //create header row for csv
    $csv='"'.esc_html( __('Donation date','church-admin' ) ).'","'.esc_html( __('Donation ID','church-admin' ) ).'","'.esc_html( __('Envelope ID','church-admin' ) ).'","'.esc_html( __('Donor','church-admin' ) ).'","'.esc_html( __('Email','church-admin' ) ).'","'.esc_html( __('Service','church-admin' ) ).'","'.esc_html( __('Gross Amount','church-admin' ) ).'","'.esc_html( __('Net Amount','church-admin' ) ).'","'.esc_html( __('Transaction Type','church-admin' ) ).'","'.esc_html( __('Transaction Frequency','church-admin' ) ).'","'.esc_html( __('Fund','church-admin' ) ).'"';
    //add custom fields to header row
    $custom_fields=church_admin_get_custom_fields();
    if(!empty( $custom_fields) )
    {
        foreach( $custom_fields AS $id=>$field)
        {
            if( $field['section']!='giving')continue;
            $csv.=',"'.esc_html( $field['name'] ).'"';
        }
    }
    $csv.="\r\n";//header finished
   

    $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_giving a, '.$wpdb->prefix.'church_admin_giving_meta b WHERE a.giving_id=b.giving_id AND a.donation_date>="'.esc_sql( $start_date).'" AND a.donation_date<="'.esc_sql( $end_date).'" '.$peopleSQL.' ORDER BY donation_date DESC';
    church_admin_debug( $sql);
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        foreach( $results AS $row)
        {
            $name=$row->name;
            $serviceDetail='';
            if(!empty( $row->service_id) )
            {
                $service=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$row->service_id.'"');
                if(!empty( $service) )
                {
                    $serviceDetail=$service->service_name.' '.$service->service_time;
                }
            }
            if(!empty( $row->people_id) )
            {
                $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$row->people_id.'"');
                if(!empty( $person) )$name=church_admin_formatted_name( $person);
            }
            if ( empty( $name) )$name=__('Anonymous','church-admin');
			$netAmount=$row->gross_amount-$row->paypal_fee;
            $envelopeID = !empty($row->envelope_id)?$row->envelope_id:'';
            $csv.='"'.esc_html( $row->donation_date).'","'.(int)$row->giving_id.'","'.esc_html($envelopeID).'","'.$name.'","'.esc_html( $row->email).'","'.esc_html( $serviceDetail).'","'.(float)$row->gross_amount.'","'.(float)$netAmount.'","'.esc_html( $row->txn_type).'","'.esc_html( $row->txn_frequency).'","'.esc_html( $row->fund).'"';
            if(!empty( $custom_fields) )
                {
                    foreach( $custom_fields AS $id=>$field)
                    {
                        
                        if( $field['section']!='giving')continue;
                        $thisData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$id.'" AND gift_id="'.(int)$row->giving_id.'"');
                        
                        switch( $field['type'] )
                        {
                            case 'boolean':
                                if(!empty( $thisData->data) )  {$customOut==__('Yes','church-admin');}else{$customOut=__('No','church-admin');}
                            break;
                            case 'date':
                                if(!empty( $thisData->data) )  {$customOut=mysql2date(get_option('date_format'),$thisData->data);}else{$customOut="";}
                            break;
                            default:
                                if(!empty( $thisData->data) )  {$customOut=esc_html( $thisData->data);}else{$customOut="";}
                            break;
                        }
                        if(!empty( $customOut) )  {$csv.= ',"'.$customOut.'"';}else{$csv.=',""';}
                        
                    }
                }
            $csv.="\r\n";
        }
        
        
    }
    header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="giving.csv"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header("Content-Disposition: attachment; filename=\"giving.csv\"");
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $csv;
    exit();
}

/**********************************
 *
 * GA rport CSV
 *
 **********************************/
function church_admin_gift_aid_csv( $start_date,$end_date,$fund='All')
{
    if(empty($start_date) || !church_admin_checkdate( $end_date) )return FALSE;
    if(empty($end_date) || !church_admin_checkdate( $end_date) )return FALSE;
    update_option('church-admin-last-gift-aid',$end_date);
    global $wpdb;
    
    $csv='"'.esc_html( __('Donation date','church-admin' ) ).'","'.esc_html( __('Donor','church-admin' ) ).'","'.esc_html( __('Email','church-admin' ) ).'","'.esc_html( __('Service','church-admin' ) ).'","'.esc_html( __('Amount','church-admin' ) ).'","'.esc_html( __('Transaction Type','church-admin' ) ).'","'.esc_html( __('Transaction Frequency','church-admin' ) ).'"'."\r\n";
    $csv='"Title","First name","Last name","House number","Postcode","Aggregated Donations","Sponsored event","Date","Amount"'."\r\n";
    if( $fund=='All')
    {
        $fundSQL='';
    }else{$fundSQL=' AND b.fund="'.esc_sql( $fund).'"';}
    
    $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_giving a, '.$wpdb->prefix.'church_admin_giving_meta b WHERE a.gift_aid=1 AND a.giving_id=b.giving_id AND a.donation_date>="'.esc_sql( $start_date).'" AND a.donation_date<="'.esc_sql( $end_date).'" '.$fundSQL.'  ORDER BY a.donation_date DESC';
   
    if(defined('CA_DEBUG') )church_admin_debug( $sql);
    $results=$wpdb->get_results( $sql);
    $csvRows=array();
    if(!empty( $results) )
    {
        foreach( $results AS $row)
        {
            /*
                echo'<pre>';
                print_r( $row);
                echo'</pre>';
            */
            //church_admin_debug('****** Processing a record *******');
            //church_admin_debug(print_r( $row,TRUE) );
            if(!empty( $row->name) )
            {
                //church_admin_debug('Got a name');
                $paypalName=explode(" ",$row->name);
                $row->first_name=$paypalName[0];
                $row->last_name=$paypalName[1];
            }
            elseif(!empty( $row->people_id) )
            {
                //church_admin_debug('Got a people_id');
                $name=$wpdb->get_row('SELECT a.*,b.address FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.people_id="'.(int)$row->people_id.'" AND a.household_id=b.household_id');
                if(!empty( $name) )
                {
                    $row->first_name=$name->first_name;
                    $row->last_name=$name->last_name;
                    $row->address=$name->address;
                }
            }
           
                if(!empty( $row->sex)&&$row->sex==1)  {$title="Mr";}else{$title="Ms";}
                
                $houseNo=substr( $row->address, 0, strspn( $row->address, "0123456789") );
                //grab postcode
                $postcodeRegex = "/((GIR 0AA)|((([A-PR-UWYZ][0-9][0-9]?)|(([A-PR-UWYZ][A-HK-Y][0-9][0-9]?)|(([A-PR-UWYZ][0-9][A-HJKSTUW] )|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY] ) )) ) [0-9][ABD-HJLNP-UW-Z]{2}) )/i";
               
                $postcodeRegex = '/([A-Za-z]{1,2}[0-9]{1,2}[A-Za-z]?[ ]?)([0-9]{1}[A-Za-z]{2})/i';

                if (preg_match( $postcodeRegex, $row->address , $matches) )
                {
                    $postcode = $matches[0];
                }
                else
                {
                    $postcode='';
                }
                //if(defined('CA_DEBUG') )church_admin_debug( $row->address);
                //if(defined('CA_DEBUG') )church_admin_debug(print_r( $matches,TRUE) );
                if(!empty( $row->first_name)&&!empty( $row->last_name)&&!empty( $postcode) )
                {
                    //church_admin_debug('Got enough details for GA');
                    if ( empty( $csvRows[$row->giving_id] ) )
                    {
                        //$csv.='"'.esc_html( $title).'","'.esc_html( $row->first_name).'","'.esc_html( $row->last_name).'","'.(int)$houseNo.'","'.esc_html( $postcode).'","","","'.mysql2date("d/m/Y",$row->donation_date).'","'.$row->gross_amount.'"'."\r\n";
                        $csvRows[$row->giving_id]=array(    'title'=>'"'.esc_html( $title).'"',
                                                            'first_name'=>'"'.esc_html( $row->first_name).'"',
                                                            'last_name'=>'"'.esc_html( $row->last_name).'"',
                                                            'house_no'=>'"'.(int)$houseNo.'"',
                                                            'postcode'=>'"'.esc_html( $postcode).'"',
                                                            'aggregate'=>'""',
                                                            'sponsored'=>'""',
                                                            'donation_date'=>'"'.mysql2date("d/m/Y",$row->donation_date).'"',
                                                            'amount'=>$row->gross_amount
                    );
                    }else
                    {
                        $csvRows[$row->giving_id]['amount']+=$row->gross_amount;
                    }
                }
                //church_admin_debug('Person processed!');
        }
        
        
    }
    foreach( $csvRows AS $ID=>$data)  {$csv.=implode(",",$data)."\r\n";}
    $filename=str_replace(" ","-",$fund.'-gift-aid-report.csv');
    header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $csv;
    exit();
}



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



function church_admin_attendance_csv()
{
    global $wpdb;
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/attendance.php');

    echo'<h2>'.esc_html( __('Attendance CSV','church-admin' ) ).'</h2>';
	echo'<form action="'.admin_url().'admin.php" method="POST"><table>';
    wp_nonce_field('attendance-csv');
	echo'<input type="hidden" name="page" value="church_admin/index.php" /><input type="hidden" name="ca_download" value="attendance-csv" /><input type="hidden" name="service" value="attendance" />';
    echo'<tr><th scope="row">'.esc_html( __('Meeting','church-admin' ) ).'</th><td>'.church_admin_att_mtg_chooser(NULL,NULL);
	echo'</td><td><input class="button-primary" type="submit" value="'.esc_html( __('Download CSV','church-admin' ) ).'" /></td></tr></table></form>';
    
}
function church_admin_attendance_csv_output()
{
    
    global $wpdb;
    $csv='"'.esc_html( __('Date','church-admin' ) ).'","'.esc_html( __('Adults','church-admin' ) ).'","'.esc_html( __('Children','church-admin' ) ).'","'.esc_html( __('Rolling Average Adults','church-admin' ) ).'","'.esc_html( __('Rolling Average Children','church-admin' ) ).'"'."\r\n";

    if(!empty( $_REQUEST['service_id'] ) )
  		{
  			$meeting=explode('/',church_admin_sanitize($_REQUEST['service_id'] ));
  			if(!empty( $meeting) )
  			{//meeting populated
  				switch( $meeting['0'] )
  				{
  					default:
  					case'S':
  						$mtg_type='service';
  					break;
  					case 'G':
  						$mtg_type='group';
  					break;
  					case 'C':
  						$mtg_type='class';
  					break;
  				}
		  		$service_id=intval( $meeting['1'] );
  		    }
        }
  		else
  		{
  			$service_id=1;
  			$mtg_type='service';
  		}
  		$query = 'SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE mtg_type="'.$mtg_type.'" AND service_id="'.esc_sql( $service_id).'" ORDER BY `date` DESC';
        $results=$wpdb->get_results( $query);
        if(!empty( $results) )
        {
            foreach( $results AS $row)
            {
                $csv.='"'.$row->date.'","'.intval( $row->adults).'","'.intval( $row->children).'","'.intval( $row->rolling_adults).'","'.intval( $row->rolling_children).'"'."\r\n";
            }
            
        }
    
    $filename="attendance.csv";
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=$filename");
	header("Content-Type: text/csv");
	header("Content-Transfer-Encoding: binary");
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $csv; 
    exit();
}