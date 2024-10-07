<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/**********************************
 *
 * Sermon notes
 *
 **********************************/
function church_admin_sermon_notes_pdf( $file_id)
{
    global $wpdb;
    $sermon=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.(int)$file_id.'"');
    if(defined('CA_DEBUG') ) church_admin_debug(print_r( $sermon,TRUE) );
    if(!empty( $sermon) )
    {
        //tidy up transcript
        $transcript=$sermon->transcript;
             
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pdf-html.php');
        $URL=church_admin_find_sermon_page(); 
        $url=$URL.'?sermon='.$sermon->file_slug;
        $pdf = new PDF_HTML();
        $pdf->SetAutoPageBreak(1,15);
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $pdf->AddPage('P',get_option('church_admin_pdf_size') );
        $pdf->SetFont('DejaVu','',10); 
        //Doc Header
        $title=$sermon->file_title;
        if (strlen( $title)>55)
        $title=substr( $title,0,55)."...";
        $pdf->SetTextColor(33,32,95);
        $pdf->SetFontSize(20);
        $pdf->SetFillColor(255,204,120);
        $pdf->Cell(0,20,$title,1,1,"C",1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFontSize(12);
        $pdf->Ln(5);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFontSize(20);
        $pdf->Cell(0,10,$sermon->speaker,0,1,'C');
        $pdf->SetFontSize(12);
        $pdf->Cell(0,10,mysql2date(get_option('date_format'),$sermon->pub_date),0,1,'C');
        $linkTitle=str_replace('http://','',$url);
        $linkTitle=str_replace('https://','',$url);
        if(strlen( $linkTitle) )$linkTitle=substr( $linkTitle,0,55)."...";
        $pdf->Cell(0,10,strip_tags(__('Sermon Audio file','church-admin' ) ),0,1,'C',FALSE,$url);
        
       
        $pdf->SetFont('DejaVu','',10); 
        $pdf->WriteHTML(nl2br( $sermon->transcript) );
        $pdf->Output();
        exit();
    }
}

/**********************************
 *
 * iCal
 *
 **********************************/
function church_admin_export_ical()
{
    global $wpdb;
    $recurringEventID=array();
    $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b WHERE a.cat_id=b.cat_id';

    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {

   
        $ical="BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Church Admin Plugin//Website//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
        foreach( $results AS $row)
        {
            if(in_array( $row->event_id,$recurringEventID) )continue;//ignore repeat of recurring

            
            
            $ical.="BEGIN:VEVENT\r\n";
            $ical.="UID:".sanitize_title( $row->title.(int)$row->date_id)."\r\n";
            $ical.="TRANSP:OPAQUE\r\nX-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC\r\n";
            
            $ical.="DTSTART;VALUE=DATE:".mysql2date('Ymd',$row->start_date)."T".mysql2date("His",$row->start_time)."\r\n";
            $ical.="DTEND;VALUE=DATE:".mysql2date('Ymd',$row->start_date)."T".mysql2date("His",$row->end_time)."\r\n";
            $DTstamp=mysql2date('Ymd',$row->start_date).'T'.mysql2date("His",$row->start_time);
            $ical.="DTSTAMP:".date('Ymd').'T'.date('His')."Z\r\n";
            $startTS=strtotime( $row->start_date.' '.$row->start_time);
            $endTS=strtotime( $row->start_date.' '.$row->end_time);
            $duration=ca_secondsToTime( $endTS-$startTS);
            $dur='P';
            if(!empty( $duration['d'] ) )  {$dur.=$duration['d'].'D';}else{$dur.='0D';}
            if(!empty( $duration['h'] ) )$dur.='T'.$duration['h'].'H';
            if(!empty( $duration['m'] ) )$dur.=$duration['m'].'M';
            //$ical.="DURATION:".$dur."\r\n";
            $ical.="CATEGORIES:".sanitize_text_field( $row->category)."\r\n";
            $ical.="LOCATION:".wordwrap( $row->location, 66, "\r\n")."\r\n";
            $ical.="DESCRIPTION:".wordwrap( $row->description, 63, "\r\n")."\r\n";
            $ical.="SUMMARY:".sanitize_text_field( $row->title)."\r\n";
            if(!empty( $row->link) )$ical.="URL:".esc_url( $row->link)."\r\n"; 
            $d=explode("-",$row->start_date);
            $year=$d[0];
            $month=$d[1];
            $day=$d[2];
            //recurring
            switch( $row->recurring)
            {
                case '1':
                    $ical.='RRULE:FREQ=DAILY;INTERVAL=1;BYDAY=;COUNT='.(int)$row->how_many;
                    $recurringEventID[]=$row->event_id;    
                break;
                case '7':
                    $ical.='RRULE:FREQ=WEEKLY;INTERVAL=1;BYDAY=;COUNT='.(int)$row->how_many;
                    $recurringEventID[]=$row->event_id;    
                break;
                case '14':
                    $ical.='RRULE:FREQ=WEEKLY;INTERVAL=2;BYDAY=;COUNT='.(int)$row->how_many;
                    $recurringEventID[]=$row->event_id;    
                break;
                case 'm':
                    $ical.='RRULE:FREQ=MONTHLY;INTERVAL=1;BYMONTHDAY='.$day.';COUNT='.(int)$row->how_many;
                    $recurringEventID[]=$row->event_id;    
                break;
                case 'a':
                    $ical.='RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH='.$month.';BYMONTHDAY='.$day.';COUNT='.(int)$row->how_many;
                    $recurringEventID[]=$row->event_id;    
                break;
            }
            $ical.="END:VEVENT\r\n";
            
        }
        
        $ical.="END:VCALENDAR";
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename=calendar.ics');
        header('Access-Control-Max-Age: 1728000');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
        header('Access-Control-Allow-Credentials: true');
        echo $ical;
        die();
    }
    else
    {
        echo __("Nothing to export",'church-admin');
    }
    exit();
}
function church_admin_ical( $date_id)
{
    global $wpdb;
    $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b WHERE a.date_id="'.(int)$date_id.'" AND a.cat_id=b.cat_id';
    $row=$wpdb->get_row( $sql);
    if(empty($row)){echo'No date found';exit();}
    $ical="BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Church Admin Plugin//Website//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
    $ical.="BEGIN:VEVENT\r\n";
    $ical.="UID:".sanitize_title( $row->title)."\r\n";
    $ical.="TRANSP:OPAQUE\r\nX-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC\r\n";
    
    $ical.="DTSTART;VALUE=DATE:".mysql2date('Ymd',$row->start_date)."T".mysql2date("His",$row->start_time)."\r\n";
    $ical.="DTEND;VALUE=DATE:".mysql2date('Ymd',$row->start_date)."T".mysql2date("His",$row->end_time)."\r\n";
    $DTstamp=mysql2date('Ymd',$row->start_date).'T'.mysql2date("His",$row->start_time);
    $ical.="DTSTAMP:".date('Ymd').'T'.date('His')."Z\r\n";
    $startTS=strtotime( $row->start_date.' '.$row->start_time);
    $endTS=strtotime( $row->start_date.' '.$row->end_time);
    $duration=ca_secondsToTime( $endTS-$startTS);
    $dur='P';
    if(!empty( $duration['d'] ) )  {$dur.=$duration['d'].'D';}else{$dur.='0D';}
    if(!empty( $duration['h'] ) )$dur.='T'.$duration['h'].'H';
    if(!empty( $duration['m'] ) )$dur.=$duration['m'].'M';
    //$ical.="DURATION:".$dur."\r\n";
    $ical.="CATEGORIES:".sanitize_text_field( $row->category)."\r\n";
    $ical.="LOCATION:".wordwrap( $row->location, 66, "\r\n")."\r\n";
    $ical.="DESCRIPTION:".wordwrap( $row->description, 63, "\r\n")."\r\n";
    $ical.="SUMMARY:".sanitize_text_field( $row->title)."\r\n";
   if(!empty( $row->link) )$ical.="URL:".esc_url( $row->link)."\r\n"; 
    $ical.="END:VEVENT\r\nEND:VCALENDAR";
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=calendar.ics');
    header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
	header('Access-Control-Allow-Credentials: true');
	echo $ical;
	die();
}

function ca_secondsToTime( $inputSeconds) {

    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // extract days
    $days = floor( $inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor( $hourSeconds / $secondsInAnHour);

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor( $minuteSeconds / $secondsInAMinute);

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil( $remainingSeconds);

    // return the final array
    $obj = array(
        'd' => (int) $days,
        'h' => (int) $hours,
        'm' => (int) $minutes,
        's' => (int) $seconds,
    );
    return $obj;
}





/**********************************
 *
 * Produces PDF from filter results
 *
 **********************************/

function church_admin_filter_pdf()
{
    global $wpdb;
    require_once(plugin_dir_path(__FILE__).'/filter.php');
    $sql=church_admin_build_filter_sql( church_admin_sanitize( $_POST['check'] ) ,false);
    $results=$wpdb->get_results( $sql);
    if(defined('CA_DEBUG') )church_admin_debug(print_r( $results,TRUE) );
    if(!empty( $results) )
    {
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	   class PDF extends FPDF
	   {
		  function Header()
		  {
			$this->SetXY(10,10);
			$this->SetFont('DejaVu','','B',18);
			$title=get_option('blogname').' '.strip_tags( __('Filtered Address List','church-admin' ) ).' '.date(get_option('date_format') );
			$this->Cell(0,8,$title,0,1,'C');
			$this->Ln(5);
		  }
	   }
	   $pdf = new PDF();
        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
	   $pdf->SetAutoPageBreak(1,15);
	   $pdf->AddPage('P',get_option('church_admin_pdf_size') );
        foreach( $results AS $row)
        {
            $pdf->SetFont('DejaVu','B',10);
            $name=array_filter(array( $row->first_name,$row->prefix,$row->last_name) );
            $first_line=implode(" ",$name);
            if(!empty( $row->phone) )$first_line.=', '.$row->phone;
            if(!empty( $row->mobile) )$first_line.=', '.$row->mobile;
            if(!empty( $row->email) )$first_line.=', '.$row->email;
            
		    $pdf->Cell(0,5,$first_line,0,1,"L");
            $pdf->SetFont('DejaVu','',10);
            $pdf->Cell(0,5,$row->address,0,1,"L");
            $pdf->Ln(5);
        }
        $pdf->Output();
    }
}

/**
 *
 * Address PDF
 *
 * @author  Andy Moyle
 * @param    $member_type_id
 * @return
 * @version  0.1
 *
 */
function church_admin_address_pdf_v1( $member_type_id=0,$loggedin=1,$showDOB=TRUE,$title=NULL,$address_style='multi',$show_photos=1) 
{
   church_admin_debug("*************\r\n church_admin_address_pdf_v1");
   church_admin_debug('Photos '.$show_photos);
    update_option('church_admin_pdf_title',$title);
	if(!empty( $loggedin)&&!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	if(!empty( $member_type_id)&&is_array( $member_type_id) )$member_type_id=implode(",",$member_type_id);
	//initilaise pdf
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	
	$pdf = new fPDF();
    // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
   
    //$pdf->SetAutoPageBreak(1,20);
	$pdf->AddPage('P',get_option('church_admin_pdf_size') );
    
	$pdf->SetFont('DejaVu','B',18);
	if ( empty( $title) )$title=get_option('blogname').' '.strip_tags( __('Family Listing','church-admin' ) ).' '.date(get_option('date_format') );

  
	$pdf->Cell(0,10,$title,0,1,'C');
	$pdf->Ln(5);
    $pdf->SetFont('DejaVu','','',10);
   

  	global $wpdb;
	//address book cache
	$memb_sql='';
  	if( $member_type_id!=0)
  	{
  		$memb=explode(',',$member_type_id);
      	foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.$value;}
      	if(!empty( $membsql) ) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}
	}
	$sql='SELECT DISTINCT a.household_id,a.*,a.show_me,b.attachment_id AS household_image FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_household b on a.household_id=b.household_id WHERE a.show_me=1 AND a.head_of_household=1 '.$memb_sql.'  ORDER BY a.last_name,a.first_name,a.middle_name ASC';
    if(defined('CA_DEBUG') )church_admin_debug( $sql);
  	$results=$wpdb->get_results( $sql);
    church_admin_debug( $sql);
  	$counter=1;
    $addresses=array();
    $x=0;
    $y=25;
    $pageWidth=$pdf->GetPageWidth();
    $imageTopLeft=$pageWidth-75;//50mm wide and 10mm margin from edge and 5mm from box edge
	foreach( $results AS $ordered_row)
	{
        $currentY=$pdf->GetY();
        if(( $currentY+20)>=( $pdf->GetPageHeight()-20) )
        {
            church_admin_debug('New page');
            $pdf->AddPage('P',get_option('church_admin_pdf_size') );
            $pdf->SetX(10);
            $pdf->SetY(20);
            $x=10;
            $currentY=20;
        }
        
        church_admin_debug('Y = '.$currentY);
		$outputlines = 0;
		$address=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.esc_sql( $ordered_row->household_id).'"');
		$people_results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE show_me=1 AND household_id="'.esc_sql( $ordered_row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
		$adults=$children=$emails=$mobiles=$photos=$date_of_birth=array();
		$last_name='';
		$imageHeight=0;
		$imagePath=NULL;
        $imageY=$pdf->GetY();
		if(!empty( $ordered_row->household_image) && !empty($show_photos) )
		{
			$imagePath=church_admin_scaled_image_path( $ordered_row->household_image,'medium') ;
            church_admin_debug('Image');
            church_admin_debug($imagePath);
            $imageHeight = $imagePath['height'];
            $imageWidth = $imagePath['width'];
           
            
          
            //output image on right hand side
            
            if(!empty( $imagePath) )
            {
                church_admin_debug('add image');
                $height=60*( $imageHeight/$imageWidth);
                $mime_type = wp_get_image_mime($imagePath['path']);
                church_admin_debug($mime_type);
                if($mime_type == 'image/png' || $mime_type=='image/jpeg'){
                    $pdf->Image( $imagePath['path'],$imageTopLeft,$pdf->getY(),60,$height);
                    $imageY+=$height;
                }
            
            }
        }
        $show_address=0;
        $show_landline=0;
  
		foreach( $people_results AS $people)
		{
            //use privacy settings from head of household
            $privacy = maybe_unserialize($people->privacy);
            if(!empty($privacy['show-address']) &&!empty($people->head_of_household)){$show_address=1;}
            if(!empty($privacy['show-landline']) &&!empty($people->head_of_household)){$show_landline=1;}
           
			if( $people->people_type_id=='1')
			{
				if(!empty( $people->prefix) )  {
					$prefix=$people->prefix.' ';
				}else{
					$prefix='';
				}
				$last_name=$prefix.$people->last_name;
				$adults[$last_name][]=$people->first_name;
				if(!empty( $people->email)&&$people->email!=end( $emails) &&!empty($privacy['show-email']) ) $emails[$people->first_name]=$people->email;
				if(!empty( $people->mobile)&&$people->mobile!=end( $mobiles)  &&!empty($privacy['show-cell']) )$mobiles[$people->first_name]=$people->mobile;
                if(!empty( $people->date_of_birth)&&$people->date_of_birth!=end( $date_of_birth) && $people->date_of_birth != '0000-00-00')
                   $date_of_birth[$people->first_name]=mysql2date(get_option('date_format'),$people->date_of_birth);
				if(!empty( $people->attachment_id) )$photos[$people->first_name]=$people->attachment_id;
				$x++;
			}
			else
			{
				$children[]=$people->first_name;
				if(!empty( $people->attachment_id) )$photos[$people->first_name]=$people->attachment_id;
			}

		}
		//create output
		array_filter( $adults); $adultline=array();
		foreach( $adults as $lastname=>$firstnames)  {$adultline[]=implode(" & ",$firstnames).' '.$lastname;}
		//address name of adults in household
		
		$pdf->SetFont('DejaVu','B',10);
		$pdf->Cell(0,5,implode(" & ",$adultline),0,1,'L');
		$pdf->SetFont('DejaVu','',10);
		$outputlines += 1;
		//children
		if(!empty( $children) )  {
			
			$pdf->Cell(0,5,implode(", ",$children),0,1,'L');
			$outputlines += 1;
		}
        
		//address if stored
		if(!empty( $address->address) && !empty($show_address)){
			switch($address_style)
            {
                case 'single':
                default:
                    $pdf->Cell(0,5,$address->address,0,1,'L');
                break;
                case 'multi':
                    $pdf->MultiCell(0,5,str_replace(', ',",\n",$address->address));
                break;
            }
			
			$outputlines += 1;
		}
        if(!empty( $address->mailing_address) && !empty($show_address))  {
			
			$pdf->Cell(0,5,strip_tags(__('Mailing address: ','church-admin' ) ).$address->mailing_address,0,1,'L');
			$outputlines += 1;
		}
		//emails
		if (!empty( $emails) )
		{
			array_unique( $emails);
			if(count( $emails)<2 && $x<=1)
			{
				
				$pdf->Cell(0,5,end( $emails),0,1,'L',FALSE,'mailto:'.end( $emails) );
				$outputlines += 1;
			}
			else
			{//more than one email in household
				$text=array();
				foreach( $emails AS $name=>$email)
				{
					$content=$name.': '.$email;
					if( $email!=end( $emails) )
					$width=$pdf->GetStringWidth( $content);
					
					$pdf->Cell(0,5,$content,0,1,'L',FALSE,'mailto:'.$email);
					$outputlines += 1;
				}


			}
		}
		if (!empty( $address->phone)  && !empty($show_landline)) {
			
			$pdf->Cell(0,5,$address->phone,0,1,'L',FALSE,'tel:'.$address->phone);
			$outputlines += 1;
		}
		if (!empty( $mobiles) ) {
			array_unique( $mobiles);
			
			if(count( $mobiles)<2 && $x<=1) {
				$pdf->Cell(0,5,end( $mobiles),0,0,'L',FALSE,'tel:'.end( $mobiles) );
				$outputlines += 1;
			}
			else {//more than one mobile in household
				$text=array();
				foreach( $mobiles AS $name=>$mobile) {
					$content=$name.': '.$mobile;
					if( $mobile!=end( $mobiles) )$content.=', ';
					$width=$pdf->GetStringWidth( $content);
					$pdf->Cell( $width,5,$content,0,0,'L',FALSE,'tel:'.$mobile);
					//$outputlines += 1;
				}

			}
			$pdf->Ln(5);
			$outputlines += 1;
		}
        //dates of birth
		if (!empty( $date_of_birth)&&!empty( $showDOB) )
		{
			array_unique( $date_of_birth);
			if(count( $date_of_birth)<2 && $x<=1)
			{
				
				$pdf->Cell(0,5,strip_tags(__('Date of birth','church-admin' ) ).' '.end( $date_of_birth),0,1,'L',FALSE,'');
				$outputlines += 1;
			}
			else
			{//more than one email in household
				$text=array();
				foreach( $date_of_birth AS $name=>$dob)
				{
                    //translators: %1$s is name %2$s os a date
					$content=strip_tags(sprintf(__('Date of birth for %1$s: %2$s','church-admin' ) ,$name,$doba0));
					if( $dob!=end( $date_of_birth) )
					$width=$pdf->GetStringWidth( $content);
					
					$pdf->Cell(0,5,$content,0,1,'L',FALSE,'');
					$outputlines += 1;
				}


			}
		}
        $newY=$pdf->GetY()+5;
        if( $imageY>$newY)$newY=$imageY+5;
        $pdf->SetY( $newY);
        
       
    }


	$pdf->Output();

	exit();
}



function church_admin_address_pdf_v2( $member_type_id=1,$loggedin=1) 
{
   if(defined('CA_DEBUG') )church_admin_debug("*************\r\n PDF v2");
	//initilaise pdf
	if(!empty( $loggedin)&&!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	global $wpdb;
	if(!empty( $member_type_id)&&is_array( $member_type_id) )$member_type_id=implode(",",$member_type_id);
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	class PDF extends FPDF
	{
		function Header()
		{
			$this->SetXY(10,10);
			$this->SetFont('DejaVu','','B',18);
			$title=get_option('blogname').' '.strip_tags( __('Directory Listing','church-admin'));
			$this->Cell(0,8,$title,0,1,'C');
			$this->Ln(5);
		}
		function Footer() {
			$footerYLocation = $this->GetPageHeight() -5;
			$this->SetXY(10,$footerYLocation);
			$this->SetFont('DejaVu','','',10);
			$footer=strip_tags(__('Page: ','church-admin' ) ).$this->PageNo();
			$this->Cell(0,5,$footer,0,1,'C');
		}
	}
	$pdf = new PDF();
    // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    $pdf->SetAutoPageBreak(1,10);    
	$pdf->SetAutoPageBreak(1,10);
	$pdf->AddPage('P',get_option('church_admin_pdf_size') );


		global $wpdb;
	//address book cache
	$memb_sql='';
		if( $member_type_id!=0)
		{
			$memb=explode(',',$member_type_id);
				foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.$value;}
				if(!empty( $membsql) ) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}
	}
	$sql='SELECT DISTINCT a.household_id,a.*,a.show_me,b.attachment_id AS household_image FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_household b on a.household_id=b.household_id WHERE a.show_me=1 AND a.head_of_household=1 '.$memb_sql.'  ORDER BY a.last_name,a.first_name,a.middle_name ASC';
    if(defined('CA_DEBUG') )church_admin_debug( $sql);
		$results=$wpdb->get_results( $sql);

		$counter=1;
		$addresses=array();
		$y=25;
		$imagename = "";
	foreach( $results AS $ordered_row) 	{
		$address=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.esc_sql( $ordered_row->household_id).'"');
		$people_results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE show_me=1 AND household_id="'.esc_sql( $ordered_row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
		$adults=$children=$emails=$mobiles=$photos=array();
		$householdmembers = array();
		$last_name='';
		$x=0;
        $show_address=0;
        $show_landline=0;
		foreach( $people_results AS $people) 	{
            //use privacy settings from head of household
            $privacy = maybe_unserialize($people->privacy);
            if(!empty($privacy['show-address']) &&!empty($people->head_of_household)){$show_address=1;}
            if(!empty($privacy['show-landline']) &&!empty($people->head_of_household)){$show_landline=1;}
			if( $people->people_type_id=='1') {
				if(!empty( $people->prefix) )  {
					$prefix=$people->prefix.' ';
				}else{
					$prefix='';
				}
				$last_name=$prefix.$people->last_name;
				$adults[$last_name][]=$people->first_name;
				if(!empty( $people->attachment_id) )$photos[$people->first_name]=$people->attachment_id;
			}
			else {
				$children[]=$people->first_name;
				if(!empty( $people->attachment_id) )$photos[$people->first_name]=$people->attachment_id;
			}
			$householdmembers[$x]['name'] = $people->first_name;
			if( $people->last_name != $last_name) {
				$householdmembers[$x]['name'] = $people->first_name.' '.$people->last_name;
			}
			$householdmembers[$x]['date_of_birth'] = $people->date_of_birth;
			if(!empty($privacy['show-cell'])){$householdmembers[$x]['mobile'] = $people->mobile;}
			if(!empty($privacy['show-email'])){$householdmembers[$x]['email'] = $people->email;}
			$x++;
		}
		//create output
		array_filter( $adults);

		//Check to see if we have room at the bottom of a page for this family
		//Assume the picture lines will take 6 lines of output (30 Y positions)
		//There is one line of individual title (5 Y positions) and 1 line per individual
		//Assume 10 Y positions for the <HR>
		$linesNeeded = (count( $householdmembers) * 5) + 45;
		$currentY = $pdf->getY();
		if( $currentY + $linesNeeded > $pdf->GetPageHeight()-10) {
			$pdf->AddPage('P',get_option('church_admin_pdf_size') );
		}
		$currentY = $pdf->getY();

		$imagePath=plugin_dir_path(dirname(__FILE__) ).'images/nopicture.png';
		if(!empty( $ordered_row->household_image) ) 			{
			$image=church_admin_scaled_image_path( $ordered_row->household_image,'medium') ;
            $imagePath=$image['path'];
            $imageHeight=$image['height'];
            $imageWidth=$image['width'];
			//church_admin_debug(print_r( $imagePath,TRUE) );
		}

		//output image on left hand side
		if(!empty( $imagePath) ){
            $mime_type = wp_get_image_mime($imagePath);
            if($mime_type == 'image/png' || $mime_type=='image/jpeg'){   
                $pdf->Image( $imagePath,10,$currentY,25);//added test for imagePath to stop error 2018-04-09
            }
        }
		//address name of adults in household
		$pdf->SetX(35);
		$pdf->SetFont('DejaVu','B',14);
		$pdf->Cell(0,5,strtoupper( $last_name),0,1,'L');
		$pdf->SetFont('DejaVu','',10);
		//address if stored
		if(!empty( $address->address) &&!empty($show_address)) {
			$address1 = $address->address;
			$address2 = "";
			$comma = strpos ( $address1 , ",");
			if( $comma) {
				$address2 = ltrim(substr( $address1 , $comma + 1) );
				$address1 = substr( $address1, 0, $comma);
			}
			$pdf->SetX(35);
			$pdf->Cell(0,5,$address1,0,1,'L');
			if( $comma) {
				//Second address line
				$pdf->SetX(35);
				$pdf->Cell(0,5,$address2,0,1,'L');
			}
			else {
				$pdf->Ln(5);
			}
			if(!empty( $address->phone) && !empty($show_landline)) {
				$pdf->SetX(35);
				$pdf->Cell(0,5,'Phone: '.$address->phone,0,1,'L');
			}
			else {
				$pdf->Ln(5);
			}
		}

		$pdf->Ln(10);
		$pdf->SetX(10);
		$pdf->SetFont('DejaVu','B',10);
		$pdf->Cell(0,5,'Name');
		$pdf->SetX(65);
		$pdf->Cell(0,5,'Birthdate');
		$pdf->SetX(90);
		$pdf->Cell(0,5,'Cell Phone');
		$pdf->SetX(125);
		$pdf->Cell(0,5,'Email');
		$pdf->SetFont('DejaVu','',10);
		$pdf->Ln(5);
		foreach( $householdmembers as $person) {
			$pdf->SetX(10);
			$pdf->Cell(0,5,$person['name'] );
			$pdf->SetX(65);
			$birthday = "";
			if(!empty( $person['date_of_birth'] ) && $person['date_of_birth'] !="0000-00-00") {
				$birthday = date_format(date_create( $person['date_of_birth'] ),"M d");
			}
			$pdf->Cell(0,5,$birthday);
			$pdf->SetX(90);
			if(!empty( $person->mobile) ) {
				$pdf->Cell(0,5,$person['mobile'] );
			}
			$pdf->SetX(125);
			if(!empty( $person->email) ) {
				$pdf->Cell(0,5,$person['email'] );
			}
			$pdf->Ln(5);

		}
		$currentY = $pdf->getY();
		$pdf->Line(10, $currentY, 180, $currentY);
		$pdf->Ln(5);
		}
    church_admin_debug('Finished pdf directory');
	$pdf->Output();


}


function church_admin_cron_pdf()
{
    //setup pdf
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
    $pdf=new FPDF();
     // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    $pdf->SetAutoPageBreak(1,10);
    $pdf->AddPage('P','A4');
    $pdf->SetFont('DejaVu','B',24);
    $text=__('How to set up Bulk Email Queuing','church-admin');
    $pdf->Cell(0,10,$text,0,2,'L');
    if (PHP_OS=='Linux')
    {
    $phppath='/usr/local/bin/php -f ';

    $cronpath=plugin_dir_path(dirname(__FILE__) ).'includes/cronemail.php';

	update_option('church_admin_cron_path',$cronpath);
	$command=$phppath.$cronpath;
    $command='curl --silent '.site_url().'wp-admin/admin-ajax.php?action=church_admin_cronemail';    

    $pdf->SetFont('DejaVu','',8);
    $text="Instructions for Linux servers and cpanel.\r\nLog into Cpanel which should be ".get_bloginfo('url')."/cpanel using your username and password. \r\nOne of the options will be Cron Jobs which is usually in 'Advanced Tools' at the bottom of the screen. Click on 'Standard' Experience level. that will bring up something like this... ";

    $pdf->MultiCell(0, 10, $text,0,'L' );

    $pdf->Image(plugin_dir_path( dirname(__FILE__) ).'images/cron-job1.jpg','10','65','','','jpg','');
    $pdf->SetXY(10,180);
    $text="In the common settings option - select 'Once an Hour'. \r\nIn 'Command to run' put this:\r\n".$command."\r\n and then click Add Cron Job. Job Done. Don't forget to test it by sending an email to yourself at a few minutes before the hour! ";
    $pdf->MultiCell(0, 10, $text,0,'L' );
    }
    else
    {
         $pdf->SetFont('DejaVu','',10);
        $text=__("Unfortunately setting up queuing for email using cron is only for Linux servers. Please go back to Communication settings and enable the wp-cron option for scheduling sending of queued emails",'church-admin');
        $pdf->MultiCell(0, 10, $text );
    }
    $pdf->Output();


}





function church_admin_label_pdf( $member_type_id=0,$loggedin=1,$addressType='street')
{
    church_admin_debug("address type $addressType");
	global $wpdb;
    if(!empty( $loggedin)&&!is_user_logged_in() )exit(strip_tags(__('You must be logged in to view the PDF','church-admin') ));
        //Build people sql statement from filters
        $group_by=$other='';
        $member_types=$genders=$people_types=$sites=$smallgroups=$ministries=array();
        $genderSQL=$maritalSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
        require_once('filter.php');
        $sql= church_admin_build_filter_sql( church_admin_sanitize($_REQUEST['check']) );

        $results = $wpdb->get_results( $sql);
        if( $results)
        {
            require_once('PDF_Label.php');
            $pdflabel = new PDF_Label(get_option('church_admin_label'), 'mm', 1, 2);
        // Add a Unicode font (uses UTF-8)
            $pdflabel->AddFont('DejaVu','','DejaVuSans.ttf',true);	
            $pdflabel->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
            $pdflabel->SetAutoPageBreak(1,10);
            //$pdflabel->Open();
            $pdflabel->SetFont('DejaVu','B',10);
            $pdflabel->AddPage();
            $counter=1;
            $addresses=array();
            foreach ( $results as $row)
            {

                church_admin_debug( $row);
                $name=church_admin_formatted_name( $row);
                switch( $addressType)
                {
                    default:
                    case 'street':
                    $address=$row->address;
                    break;
                    case 'mailing':
                        if(!empty( $row->mailing_address) )
                        {
                            church_admin_debug('USING MAILING ADDRESS');
                            $address=$row->mailing_address;
                        }
                        else
                        {
                            $address=$row->address;
                        }
                    break;
                }
                $address=str_replace(", ",",",$address);
                $add=explode(",",$address);
                if(!empty($name) && !empty($add))
                {
                    $add=$name."\n".implode(",\n",$add);
                    $pdflabel->Add_Label( $add);
                }

            }

            $pdflabel->Output();

        //end of mailing labels
        }
        exit();
}

function church_admin_household_label_pdf( $member_type_id=0,$loggedin=1,$addressType='street')
{
    church_admin_debug("address type $addressType");
	global $wpdb;
    if(!empty( $loggedin)&&!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	//Build people sql statement from filters
	$group_by=$other='';
	$member_types=$genders=$people_types=$sites=$smallgroups=$ministries=array();
	$genderSQL=$maritalSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=array();
	require_once('filter.php');
    if ( empty( $_REQUEST['check'] ) )exit(__('No filters checked, please go back','church-admin') );
	$sql= church_admin_build_filter_sql( church_admin_sanitize( $_REQUEST['check'] ) );
    $sql=str_replace('GROUP BY a.people_id','GROUP BY a.household_id',$sql);
    
	$results = $wpdb->get_results( $sql);
    if(defined('CA_DEBUG') )church_admin_debug(print_r( $results,TRUE) );
	if( $results)
	{
    	require_once('PDF_Label.php');
  	  	$pdflabel = new PDF_Label(get_option('church_admin_label'), 'mm', 1, 2);
    // Add a Unicode font (uses UTF-8)
        $pdflabel->AddFont('DejaVu','','DejaVuSans.ttf',true);	
        $pdflabel->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $pdflabel->SetAutoPageBreak(1,10);
        //$pdflabel->Open();
	    $pdflabel->SetFont('DejaVu','',10);
  	  	$pdflabel->AddPage();
    	$counter=1;
    	$addresses=array();
    	foreach ( $results as $row)
    	{
            //get adults
            $namesOnLabelArray=array();
            $namesOnLabelOutput=array();
            $namesResult=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_type_id=1 AND household_id="'.intval( $row->household_id).'" ORDER BY people_order');
            if(!empty( $namesResult) )
            {
                foreach( $namesResult AS $nameRow)
                {
                    //make sure last name has prefix if required
                    $last_name=implode(' ',array_filter(array( $nameRow->prefix,$nameRow->last_name) ));
                    $namesOnLabelArray[$last_name][]=$nameRow->first_name;
                }
                foreach( $namesOnLabelArray AS $lastName=>$firstName)
                {
                    $namesOnLabelOutput[]=implode(' & ',$firstName).' '.$lastName;
                    
                }
                
            }
            
            
			$name=implode(" & ",$namesOnLabelOutput);
			switch( $addressType)
            {
                default:
                case 'street':
                   $address=$row->address;
                break;
                case 'mailing':
                    if(!empty( $row->mailing_address) )
                    {
                        church_admin_debug('USING MAILING ADDRESS');
                        $address=$row->mailing_address;
                    }
                    else
                    {
                        $address=$row->address;
                    }
                break;
            }
			$address=str_replace(", ",",",$address);
			$add=explode(",",$address);
			if(!empty($name) && !empty($add))
            {
                $add=$name."\n".implode(",\n",$add);
	    	    $pdflabel->Add_Label( $add);
            }

    	}

		$pdflabel->Output();

	//end of mailing labels
	}
	exit();
}
function ca_person_vcard( $people_id)
{
    church_admin_debug("ca_person_vcard( $people_id)");
    global $wpdb;
    if ( empty( $people_id) )
    {
        church_admin_debug("No people id");
        return __('Nobody specified','church-admin');
    }
    $data=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b where a.household_id=b.household_id AND a.people_id="'.(int)$people_id.'"');
    if ( empty( $data) ){
        
        return __('Nobody specified','church-admin');
    }
    //church_admin_debug($data);
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/vcf.php');
    $v = new vCard();
    if(!empty( $data->phone) )$v->setPhoneNumber( $data->phone, "PREF;HOME;VOICE");
    if(!empty( $data->mobile) )$v->setPhoneNumber( $data->mobile, "CELL;VOICE");
    if(!empty( $data->email) )$v->setEmail( $data->email);
    $lastname=implode(" ",array_filter(array( $data->prefix,$data->last_name) ));
    $v->setName( $lastname, $data->first_name, "", "");
    $v->setAddress('',$data->address,'','','','','','HOME;POSTAL' );
    $output = $v->getVCard();
    $filename=$lastname.'.vcf';
    //church_admin_debug($output);
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/x-vcard");
    header("Content-Transfer-Encoding: binary");
    echo $output;
    exit();
}
function ca_vcard( $id)
{
  global $wpdb;
	//if(!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
    $query='SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.esc_sql( $id).'"';

	$add_row = $wpdb->get_row( $query);
    $address=$add_row->address;
    $phone=$add_row->phone;
    $people_results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.esc_sql( $id).'" ORDER BY people_type_id ASC,sex DESC');
    $adults=$children=$emails=$mobiles=array();
      foreach( $people_results AS $people)
	{
	  if( $people->people_type_id=='1')
	  {
	    $last_name=$people->last_name;
	    $adults[]=$people->first_name;
	    if(!in_array( $people->email,$emails) ) $emails[]=$people->email;
	    if( $people->mobile!=end( $mobiles) )$mobiles[]=$people->mobile;

	  }
	  else
	  {
	    $children[]=$people->first_name;
	  }

	}
  //prepare vcard
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/vcf.php');
    $v = new vCard();
    if(!empty( $add_row->phone) )$v->setPhoneNumber( $add_row->phone, "PREF;HOME;VOICE");
    if(!empty( $mobiles) )$v->setPhoneNumber("{$mobiles['0']}", "CELL;VOICE");
    $v->setName("{$last_name}", implode(" & ",$adults), "", "");

    $v->setAddress('',$add_row->address,'','','','','','HOME;POSTAL' );
    if ( empty( $emails['0'] ) )$v->setEmail("{$emails['0']}");

    if(!empty( $children) )  {$v->setNote("Children: ".implode(", ",$children) );}


    $output = $v->getVCard();
    $filename=$last_name.'.vcf';


    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/x-vcard");
    header("Content-Transfer-Encoding: binary");

   echo $output;
    exit();
}

function church_admin_year_planner_pdf( $initial_year)
{
    if ( empty( $initial_year) )$initial_year==date('Y');
    global $wpdb;
	$days=array(0=>strip_tags( __('Sun','church-admin' ) ),1=>strip_tags( __('Mon','church-admin' ) ),2=>strip_tags( __('Tues','church-admin' ) ),3=>strip_tags( __('Weds','church-admin' ) ),4=>strip_tags( __('Thur','church-admin' ) ),5=>strip_tags( __('Fri','church-admin' ) ),6=>strip_tags( __('Sat','church-admin') ));
    //check cache admin exists
    $upload_dir = wp_upload_dir();
    $dir=$upload_dir['basedir'].'/church-admin-cache/';


    //initialise pdf
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
    $pdf=new FPDF();
        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $pdf->AddPage('L','A4');

    $pageno=0;
    $x=10;
    $y=5;
    //Title
    $pdf->SetXY( $x,$y);
    $pdf->SetFont('DejaVu','B',18);
    $title=get_option('blogname');
    $pdf->Cell(0,8,$title,0,0,'C');
    $pdf->SetFont('DejaVu','B',10);

    //Get initial Values
    $initial_month='01';
    if ( empty( $initial_year) )$initial_year=date('Y');
    $month=0;

    $row=0;
    $current=time();
    $this_month = (int)date("m",$current);
    $this_year = date( "Y",$current );

    for ( $quarter=0; $quarter<=3; $quarter++)
    {
    for ( $column=0; $column<=2; $column++)
    {//print one of the three columns of months
        $x=10+( $column*80);//position column
        $y=15+(44*$quarter);
        $pdf->SetXY( $x,$y);
        $this_month=date('m',strtotime( $initial_year.'-'.$initial_month.'-01 + '.$month.' month') );
        $this_year=date('Y',strtotime( $initial_year.'-'.$initial_month.'-01 + '.$month.' month') );
        // find out the number of days in the month
        $numdaysinmonth = date ('t',strtotime( $initial_year.'-'.$initial_month.'-01 + '.$month.' month') );//cal_days_in_month( CAL_GREGORIAN, $this_month, $this_year );
        // create a calendar object
        $jd = cal_to_jd( CAL_GREGORIAN, $this_month,date( 1 ), $this_year );
        // get the start day as an int (0 = Sunday, 1 = Monday, etc)
        $startday = jddayofweek( $jd , 0 );
        // get the month as a name
        $monthname = jdmonthname( $jd, 1 );
        $month++;//increment month for next iteration
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(70,7,$monthname.' '.$this_year,0,0,'C');
        //position to top left corner of calendar month
        $y+=7;
        $pdf->SetXY( $x,$y);
        $pdf->SetFont('DejaVu','',8);
        //print daylegend
        for ( $day=0; $day<=6; $day++)$pdf->Cell(10,5,$days[$day],1,0,'C');

        $y+=5;
        $pdf->SetXY( $x,$y);
        for ( $monthrow=0; $monthrow<=5; $monthrow++)
        {//print 6 weeks

            for ( $day=0; $day<=6; $day++)
            {
                if( $monthrow==0 && $day==$startday)$counter=1;//month has started
                if( $monthrow==0 && $day<$startday)
                {
                    //empty cells before start of month, so fill with grey colour
                    $pdf->SetFillColor('192','192','192');
                    $pdf->Cell(10,5,'',1,0,'L',TRUE);
                }
                else
                {
                    //during month so category background
                    $sql='SELECT a.bgcolor FROM '.$wpdb->prefix.'church_admin_calendar_category a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE b.year_planner="1" AND a.cat_id=b.cat_id AND b.start_date="'.esc_sql($this_year.'-'.$this_month.'-'.sprintf('%02d',$counter)).'" LIMIT 1';

                    $bgcolor=$wpdb->get_var( $sql);
                    if(!empty( $bgcolor) )
                    {
                        $colour=html2rgb( $bgcolor);
                        $pdf->SetFillColor( $colour[0],$colour[1],$colour[2] );
                    }
                    else
                    {
                        $pdf->SetFillColor(255,255,255);
                    }

                    if( $counter <= $numdaysinmonth)
                    {
                        //duringmonth so print a date
                        $pdf->Cell(10,5,$counter,1,0,'L',TRUE);
                        $counter++;
                    }
                    else
                    {
                    //end of month, so back to grey background
                    $pdf->SetFillColor('192','192','192');
                    $pdf->Cell(10,5,'',1,0,'C',TRUE);
                    }
                }



            }
            $y+=5;

            $pdf->SetXY( $x,$y);
        }

    }//end of column
    }//end row

    //Build key
    $x=250;
    $y=23;
    $pdf->SetFont('DejaVu','',8);
    $result=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
    foreach ( $result AS $row)
    {

        $pdf->SetXY( $x,$y);
        $colour=html2rgb( $row->bgcolor);
        if(!empty($colour) && is_array($colour)){$pdf->SetFillColor( $colour[0],$colour[1],$colour[2] );}
        $pdf->Cell(15,5,' ',0,0,'L',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15,5,$row->category,0,0,'L');
        $pdf->SetXY( $x,$y);
        $pdf->Cell(45,5,'',1);
        $y+=6;
    }
    $pdf->Output();
    exit();
}




/**
* This function produces a xml of people in various categories
*
* @author     	andymoyle
* @param		$member_type_id comma separated,$small_group BOOL
* @return		pdf
*
*/
function church_admin_address_xml( $member_type_id=NULL,$show_small_group=1)
{
    
    church_admin_debug('Show small group '.$show_small_group);
    global $wpdb,$wp_locale;
	if(!is_user_logged_in() )
    {
        $url=$_SERVER['HTTP_HOST'];
       
       if( $url!='www.churchadminplugin.com' ) exit(__('You must be logged in to view the xml file','church-admin') );
    }
	$markers='<markers>';
   
	//grab relevant households
	$memb_sql='';
  	if(!empty( $member_type_id) )
  	{
  		$memb=explode(',',$member_type_id);
      	foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id="'.(int)$value.'"';}
      	if(!empty( $membsql) ) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}
	}
   if( $memb_sql=='#')$membsql='';
	$sql='SELECT DISTINCT a.household_id,a.last_name FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND b.lat IS NOT NULL AND b.lng IS NOT NULL '.$memb_sql.'  ORDER BY last_name ASC ';
	church_admin_debug( $sql);
    $results=$wpdb->get_results( $sql);
    church_admin_debug( $results);
    if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			$address=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$row->household_id.'"');
			$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$row->household_id.'" ORDER BY people_order, people_type_id ASC,sex DESC';
			$people_results=$wpdb->get_results( $sql);
            if(!empty( $people_results) )
            {
                $adults=$children=$emails=$mobiles=$photos=array();
                $last_name='';
                $x=0;
                $markers.= '<marker ';
                foreach( $people_results AS $people)
                {

                    if( $people->people_type_id=='1')
                    {
                        if(!empty( $people->prefix) )  {$prefix=$people->prefix.' ';}else{$prefix='';}
                        $last_name=$prefix.$people->last_name;
                        $adults[$last_name][]=$people->first_name;

                        $smallgroup_id=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup" && people_id="'.(int)$people->people_id.'"');
                        church_admin_debug('Smallgroup id is '.$smallgroup_id);
                        if(!empty( $smallgroup_id) )$smallgroup=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$smallgroup_id.'"');
                        church_admin_debug( $wpdb->last_query);
                                //small group data for marker

                                if(!empty( $smallgroup)&&!empty( $show_small_group) )
                                {
                                    church_admin_debug('FOUND small group');
                                    if ( empty( $smallgroup->group_name) )$smallgroup->group_name=' ';
                                    if ( empty( $smallgroup->address) )$smallgroup->address=' ';
                                    if ( empty( $smallgroup->whenwhere) )$smallgroup->whenwhere=' ';
                                    $sg=array();
                                    
                                    $sg[]= 'smallgroup_id="'.$smallgroup->id.'" ';
                                    $sg[] =  'smallgroup_initials="'.htmlentities(strtoupper(substr( $smallgroup->group_name,0,2) )).'" ';
                                    $sg[]= 'smallgroup_name="'.htmlentities( $smallgroup->group_name).'" ';
                                    $sg[]=  'smallgroup_lat="'.htmlentities( $smallgroup->lat).'" ';
                                    $sg[]=  'smallgroup_lng="'.htmlentities( $smallgroup->lng).'" ';
                                    if(!empty( $smallgroup->group_day) )$sg[]=  'when="'.htmlentities(sprintf('%1$s on %2$s',$smallgroup->frequency,$wp_locale->get_weekday( $smallgroup->group_day) )).'" ';
                                }
                                else
                                {$sg=array();
                                    
                                }
                        $x++;
                    }
                    else
                    {
                        if(!empty( $people->prefix) )  {$prefix=$people->prefix.' ';}else{$prefix='';}
                        $last_name=$prefix.$people->last_name;
                        $children[$last_name][]=$people->first_name;

                    }

                }
                $markers.=implode(" ",$sg);
                //address data for marker
                $markers.= 'lat="' . $address->lat . '" ';
                $markers.= 'lng="' . $address->lng . '" ';
                $markers.= 'address="'. $address->address.'" ';

                //people data
                array_filter( $adults);
                $adultline=array();
                //the join statement makes sure the array is imploded like this ",,,&"
                //http://stackoverflow.com/questions/8586141/implode-array-with-and-add-and-before-last-item
                foreach( $adults as $lastname=>$firstnames)  {$adultline[]=join(' &amp; ', array_filter(array_merge(array(join(', ', array_slice( $firstnames, 0, -1) )), array_slice( $firstnames, -1) )) ).' '.$lastname;}
                $markers.='adults_names="'.implode(" &amp; ",$adultline). '" ';
                array_filter( $children);
                $childrenline=array();
                foreach( $children as $lastname=>$firstnames)  {$childrenline[]=join(' &amp; ', array_filter(array_merge(array(join(', ', array_slice( $firstnames, 0, -1) )), array_slice( $firstnames, -1) )) ).' '.$lastname;}
                $markers.='childrens_names="'.implode(" &amp; ",$childrenline). '" ';
                $markers.= '/>';
            }
		}
		$markers.='</markers>';
		header("Content-type: text/xml;charset=utf-8");
		echo $markers;
	}

    exit();
}





function html2rgb( $color)
{
    if(empty($color))return array();
    if ( $color[0] == '#')
        $color = substr( $color, 1);

    if (strlen( $color) == 6)
        list( $r, $g, $b) = array( $color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5] );
    elseif (strlen( $color) == 3)
        list( $r, $g, $b) = array( $color[0].$color[0], $color[1].$color[1], $color[2].$color[2] );
    else
        return false;

    $r = hexdec( $r); $g = hexdec( $g); $b = hexdec( $b);

    return array( $r, $g, $b);
}


function church_admin_photo_permissions_pdf($people_type_ids)
{
    church_admin_debug('People type ids'.print_r($people_type_ids,true));
    global $wpdb;
    $title = array(__('all age groups','church-admin'));
    if(!empty($people_type_ids))
    {
        //work out people_type_id
        
        $peoplesql =  array();
        foreach( $people_type_ids AS $key=>$value)
        {
            switch(strtolower( $value) )
            {
                case 'all':$peoplesql=array();
                   
                break;
                case 'adults':
                    $peoplesql[]='people_type_id=1';
                    $title[] = __('adults','church-admin');
                break;
                case '1':
                    $peoplesql[]='people_type_id=1';
                    $title[] = __('adults','church-admin');
                break;
                case 'teens':
                    $peoplesql[]='people_type_id=3';
                    $title[] = __('teenagers','church-admin');
                break;
                case '3':
                    $peoplesql[]='people_type_id=3';
                    $title[] = __('teenagers','church-admin');
                break;
                case 'children':
                    $peoplesql[]='people_type_id=2';
                    $title[] = __('children','church-admin');
                break;
                case '2':
                    $peoplesql[]='people_type_id=2';
                    $title[] = __('children','church-admin');
                break;
            }
        }
    }
    $where = ' AND show_me=1 AND gdpr_reason IS NOT NULL AND active=1 ';
    if(!empty( $peoplesql) ) {$people_sql=' AND ('.implode(' || ',$peoplesql).')';}else{$people_sql='';}

    $sql= 'SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE photo_permission=1 '.$where.$people_sql;
    $results = $wpdb->get_results($sql);
    //church_admin_debug($wpdb->last_query);

    /******************
        * CREATE PDF
        *****************/
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
        $pdf=new FPDF();
        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        //$pdf->SetAutoPageBreak(1,20);
        //Title
        $pdf->AddPage('P',get_option('church_admin_pdf_size') );
        $pdf->SetFont('DejaVu','B',16);
        //translators: %1$s is a title for the PDF
        $PDFtitle = strip_tags( sprintf(__('Photo permission for %1$s','church-admin'),implode(', ',$title)));
        $pdf->Cell(0,10,$PDFtitle,0,2,'C');
        $pdf->SetFont('DejaVu','B',12);
        //translators: %1$s is a date
        $PDFdate = strip_tags( sprintf( __('Produced %1$s','church-admin'),wp_date(get_option('date_format'))));
        $pdf->Cell(0,10,$PDFdate,0,2,'C');
    if(!empty($results)){
        $x=0;   
        foreach($results AS $row)
        {
            church_admin_debug($row);
            if($x>=5){
                $pdf->AddPage('P',get_option('church_admin_pdf_size') );
                $pdf->SetFont('DejaVu','B',16);
                //translators: %1$s is the title of the PDF
                $PDFtitle = strip_tags( sprintf(__('Photo permission for %1$s','church-admin'),implode(', ',$title)));
                $pdf->Cell(0,10,$PDFtitle,0,2,'C');
                $pdf->SetFont('DejaVu','B',12);
                $y=$pdf->GetY();
                $x=0;
            }
            if( $row->photo_permission && $row->attachment_id)
            {
                
                $image = church_admin_scaled_image_path($row->attachment_id,'thumbnail');
                
                if(!empty($image)&&!file_exists($image['path'])){
                    $image= plugin_dir_path(dirname(__FILE__) ).'images/default-avatar.jpg';
                }
            }
            else {
                
                $image = plugin_dir_path(dirname(__FILE__) ).'images/default-avatar.jpg';//plugins_url('/images/default-avatar.jpg',dirname(__FILE__) );
            }
            $y = $pdf->GetY();
            church_admin_debug($image);
            $pdf->image($image);
            $name=church_admin_formatted_name($row);
            $pdf->text(100, $y + 20,$name);
            $pdf->ln(5);   
            $x++;
        }
       
    }
    else{
        $pdf->SetFont('DejaVu','B',12);
        $pdf->text(10, 50,__('No one with photo permissions','church-admin'));
    }
    $pdf->Output();
}

function church_admin_weekly_calendar_pdf($facilities_id,$cat_id,$start_date){

    global $wp_locale;

    //days in wp_locale Sun is 0 and Sat is 6

    $events = array(
        '6'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '7'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '8'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '9'=>array(
            0=>array(array('title'=>'Long event','start_hour'=>9,'end_hour'=>14,'start_time'=>'09:00','end_time'=>'14:00')),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '10'=>array(
            0=>array(array('title'=>'Long event','start_hour'=>9,'end_hour'=>14,'start_time'=>'09:00','end_time'=>'14:00')),
            1=>array(),
            2=>array(array('title'=>'iCaf','start_hour'=>10,'end_hour'=>13,'start_time'=>'10:00','end_time'=>'13:00')),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '11'=>array(
            0=>array(array('title'=>'Long event','start_hour'=>9,'end_hour'=>14,'start_time'=>'09:00','end_time'=>'14:00')),
            1=>array(),
            2=>array(array('title'=>'iCaf','start_hour'=>10,'end_hour'=>13,'start_time'=>'10:00','end_time'=>'13:00')),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '12'=>array(
            0=>array(array('title'=>'Long event','start_hour'=>9,'end_hour'=>14,'start_time'=>'09:00','end_time'=>'14:00')),
            1=>array(),
            2=>array(array('title'=>'iCaf','start_hour'=>10,'end_hour'=>13,'start_time'=>'10:00','end_time'=>'13:00')),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '13'=>array(
            0=>array(array('title'=>'Long event','start_hour'=>9,'end_hour'=>14,'start_time'=>'09:00','end_time'=>'14:00')),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '14'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '15'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '16'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '17'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '18'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '19'=>array(
            0=>array(array('title'=>'Service','start_hour'=>19,'end_hour'=>21,'start_time'=>'19:00','end_time'=>'21:00')),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '20'=>array(
            0=>array(array('title'=>'Service','start_hour'=>19,'end_hour'=>21,'start_time'=>'19:00','end_time'=>'21:00')),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '21'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
        '22'=>array(
            0=>array(),
            1=>array(),
            2=>array(),
            3=>array(),
            4=>array(),
            5=>array(),
            6=>array(),
        ),
    );



    $what = 'facility';
    if(empty($start_date)){$start_date=church_admin_get_day(1)->format('Y-m-d');}

    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
        $pdf=new FPDF();
        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $pdf->SetAutoPageBreak(1,10);
        //Title
        $pdf->AddPage('P',get_option('church_admin_pdf_size') );
        $pdf->SetFont('DejaVu','B',12);
        //translators: %1$s is  a date and %2$s is the facility name
        $PDFtitle = strip_tags( sprintf(__('Weekly Calendar w/c %1$s for %2$s','church-admin'),mysql2date(get_option('date_format'),$start_date),$what));
        $pdf->Cell(0,10,$PDFtitle,0,2,'C');
        $pdf->SetFont('DejaVu','B',12);
        $width = $pdf->GetPageWidth() - 20;
        $colwidth = $width/8;
        $height = $pdf->GetPageHeight() - 50;
        $rowheight = $height/17;
        //Day titles row
        //empty cell for time
        $pdf->Cell($colwidth,$rowheight,'',1,0,'C');
        for($days=0;$days<=6;$days++){

            $ln = $days<6 ? 0: 1; //place pointer to right except for last day, when new line
            $pdf->Cell($colwidth,$rowheight,$wp_locale->get_weekday_abbrev($wp_locale->get_weekday($days)),1,$ln,'C');

        }
        $pdf->SetFont('DejaVu','',8);
        //need a grid with 16 rows and 7 columns, but uses $row corresponding to hours!
        for($hour=6;$hour<=22;$hour++)
        {
          
            $pdf->Cell($colwidth,$rowheight,$hour.':00',1,0,'C');
           
            for($days=0; $days<=6 ; $days++){
                
                $pdf->SetFillColor(255,255,255);
                $ln = $days<6 ? 0: 1; //place pointer to right except for last day, when new line
                $border='1';//default is border all round
                if(!empty($events[$hour][$days][0])){
                    church_admin_debug( $hour.' Day '.$wp_locale->get_weekday($days));
                    $pdf->setFillColor(200,200,200);

                    if($hour == $events[$hour][$days][0]['start_hour']){
                        //Event starts in this hour
                        $title= $events[$hour][$days][0]['title'];
                        $time = mysql2date('H:i',$events[$hour][$days][0]['start_time']).'-'.mysql2date('H:i',$events[$hour][$days][0]['end_time']);
                        $border = 'LTR';
                        
                        if($hour == $events[$hour][$days][0]['end_hour'])
                        {
                            //title
                            
                          
                            $current_y = $pdf->GetY();
                            $current_x = $pdf->GetX();    
                            $pdf->Cell($colwidth, $rowheight/2, $title, 'LTR', 2, 'L', true);
                            $pdf->SetXY($current_x, $current_y + ($rowheight/2));
                            
                            $pdf->Cell($colwidth, $rowheight/2, $time, 'LRB',0, 'L', true);
                            $pdf->setXY($current_x+$colwidth,$current_y);
                        }
                        else
                        {
                            
                            $current_y = $pdf->GetY();
                            $current_x = $pdf->GetX();
                            $pdf->Cell($colwidth, $rowheight/2, $title, 'LTR',2, 'L', true);
                            $pdf->SetXY($current_x, $current_y + ($rowheight/2));
                          
                            $pdf->Cell($colwidth, $rowheight/2, $time, 'LR',0,'L', true);
                            $pdf->setXY($current_x+$colwidth,$current_y);
                        }
                    }
                    if($hour > $events[$hour][$days][0]['start_hour'] && $hour < $events[$hour][$days][0]['end_hour'])
                    {
                        //event spans this hour
                       
                        $pdf->Cell($colwidth,$rowheight,'','LR',$ln,'C',1);
                    }
                    /*
                    if($hour > $events[$hour][$days][0]['start_hour'] && $hour == $events[$hour][$days][0]['end_hour'] -1)
                    {
                        //event finishes at the end of this hour
                        $pdf->Cell($colwidth,$rowheight,'oops','LRB',$ln,'C',1);
                    }
                    */
                    //church_admin_debug($events[$hour][$days][0]);
                    church_admin_debug('BORDER '.$border);
                    church_admin_debug('TEXT '.$text);
                    $pdf->SetFillColor(200,200,200);
                }
                else
                {
                    $text='';
                    $pdf->Cell($colwidth,$rowheight,'','LRTB',$ln,'C',1);
                }
                
                
                
            }//end of row

        }//end of columns
        $pdf->Output();
}


function church_admin_monthly_calendar_pdf($start_date, $facilities_id,$cat_id)
{
    
    global $current_user,$wpdb,$wp_locale;
    $permalink = !empty($_REQUEST['url'])?sanitize_url(stripslashes($_REQUEST['url'])):null;
    //initialise PDF
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
    $pdf=new FPDF();
    // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);

    $pdf->AddPage('L',get_option('church_admin_pdf_size') );
    



    $totalPageHeight =  $pdf->getPageHeight()-75;//allow for margins & title
    $totalPageWidth =   $pdf->getPageWidth()-20;//allow for margins


   

	  wp_get_current_user();
	  $out='';
    if(isset( $_POST['ca_month'] ) && isset( $_POST['ca_year'] ) )  { 
        $current=mktime(12,0,0,sanitize_text_field(stripslashes($_POST['ca_month']) ),14,sanitize_text_field(stripslashes($_POST['ca_year'] ) ));
    }else{
        $current=current_time('timestamp');
    }
	$thismonth = (int)wp_date("m",$current);
	$thisyear = wp_date( "Y",$current );
	$actualyear=wp_date("Y");
	
	$now=date("M Y",$current);
	$sqlnow=date("Y-m-d", $current);
    // find out the number of days in the month
    $numdaysinmonth = $numdaysinmonth = date ('t',strtotime( $thisyear.'-'.$thismonth.'-01') );
    //integer for start date (0 Sunday etc)
    $startday = date('w',strtotime( $thisyear.'-'.$thismonth.'-01') );
    
    // get the month as a name
    $monthname =date('F',strtotime( $thisyear.'-'.$thismonth.'-01') );

    




    $display_categories = $display_facilities = array();
    
	$categories=church_admin_calendar_categories_array();
    if(!empty($cat_id))
    {
        $cat_id_array = explode(",",$cat_id);
        foreach($cat_id_array AS $key => $id){
           if(!empty($categories[$id])){
                $display_categories[]=$categories[$id];
           }
        }
    }
    if(!empty($facilities_id))
    {
        $fac_id_array = explode(",",$facilities_id);
        foreach($fac_id_array AS $key => $id){
           if(!empty($facilities[$id])){
                $display_facilities[]=$facilities[$id];
           }
        }
    }
 
    //translators: %1$s is month, %2$s is year
    $title = sprintf(__('Calendar %1$s %2$s','church-admin'), $monthname,$thisyear);


    if(!empty($display_categories) || !empty($display_facilities)){
       $title.=' (';
        if(!empty($display_categories)){
            $title.= __('Categories: ','church-admin').implode(', ',$display_categories);
        }
        if(!empty($display_facilities)){
            $title.= __('Facilities: ','church-admin').implode(', ',$display_facilities);
        }
        $title.=')';
    }
    
    $pdf->SetFont('DejaVu','B',16);
    $pdf->Cell(0,10,$title,0,2,'C');
    $pdf->SetFont('DejaVu','',16);

    $number_of_rows = church_admin_day_count('sunday',$thismonth,$thisyear) +1 ;//add one for days
    church_admin_debug('Number of Sundays +1 : '.$number_of_rows);
    $cellHeight = $totalPageHeight/$number_of_rows;
    $cellWidth = $totalPageWidth/7;

    //make header row
    for($x=0;$x<=6;$x++){
        $ln= ($x!=6) ? 0:1; //where positions goes, to right except last one       
         $pdf->Cell($cellWidth,$cellHeight,$wp_locale->get_weekday_abbrev($wp_locale->get_weekday($x)),1,$ln,'C',0);
    }
    $pdf->SetFont('DejaVu','',6);
    // put render empty cells
    $emptycells = 0;
    for( $counter = 0; $counter <  $startday; $counter ++ )
    {
        $ln= ($counter!=6) ? 0:1; //where positions goes, to right except last one     
        $pdf->SetFillColor(200,200,200);
        $pdf->Cell($cellWidth,$cellHeight,'',1,$ln,'C',1);
        $emptycells ++;
    }
    $pdf->SetFillColor(255,255,255);
    // renders the days
    $colcounter = $emptycells;
    $numinrow = 7;
    for( $counter = 1; $counter <= $numdaysinmonth; $counter ++ )
    {
        church_admin_debug('************ Day '.$counter.' Col counter:'.$colcounter.' ************');
        $colcounter ++;
        
        $xPos = $pdf->getX();//top left corner of box
        $yPos = $pdf->getY();
        if($colcounter >7) {
            $pdf->setX(10);
            $xPos=10;
            $pdf->setY($yPos+$cellHeight);
            $yPos = $yPos+$cellHeight;
            $colcounter=1;
        
        }
        church_admin_debug('x: '.$xPos.' y: '.$yPos);
        $sqlnow="$thisyear-$thismonth-".sprintf('%02d', $counter);
        //process categories
        $catsql=array();
        if ( empty( $cat_id) )  {$cat_sql="";}
        else
        {
            
            $cats=explode(',',$cat_id);
            foreach( $cats AS $key=>$value)  {
            if(church_admin_int_check( $value) )  {
                $catsql[]='a.cat_id='.(int)$value;
                $display_categories[]=$categories[$value];
            }
        }
            if(!empty( $catsql) ) {$cat_sql=' AND ('.implode(' || ',$catsql).')';}

        }
        //process facilities
        $facsql=array();
        $fac_sql='';
        if ( !empty( $facilities_id) ){
            church_admin_debug('Month render with facilities_id '.$facilities_id);
            $facs=explode(",",$facilities_id);
            foreach( $facs AS $key=>$value)  {
                if(church_admin_int_check( trim($value)) )  {
                    $facsql[]='c.meta_value='.(int)$value;
                    $display_facilities[]=$facilities[(int)$value];
                }
            }
            $FACSQL = !empty($facsql) ? ' AND ('.implode(' OR ',$facsql).') ':'';
            $sql='SELECT a.*,b.*,c.* FROM '.$wpdb->prefix.'church_admin_calendar_date a  , '.$wpdb->prefix.'church_admin_calendar_category b , '.$wpdb->prefix.'church_admin_calendar_meta c WHERE a.cat_id=b.cat_id '.$cat_sql.' AND a.event_id=c.event_id AND c.meta_type="facility_id" AND a.start_date="'.$sqlnow.'"  '.$FACSQL.' ORDER BY a.start_time';
            
            
        }
        else
        {
            //no facilities ID query
            //$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON a.cat_id=b.cat_id  WHERE  a.start_date="'.esc_sql( $date).'"  '.$cat_sql.' ORDER BY a.start_date,a.start_time';
           
            $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b  WHERE a.cat_id=b.cat_id '.$cat_sql.' AND a.start_date="'.$sqlnow.'" ORDER BY a.start_time';

        }
        
        //print the day number in the corner
        $pdf->setXY($xPos,$yPos);
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(5,8,$counter,0,0,'L');
        $pdf->SetFont('DejaVu','',8);
        $pdf->setXY($xPos,$yPos);
        //print the box
        $pdf->Cell($cellWidth,$cellHeight,'',1,$ln,'C',0);
        
        $results=$wpdb->get_results($sql);
        
        $no_days_events = $wpdb->num_rows;
        if($no_days_events ==0){continue;}
        $pdf->setXY($xPos,$yPos+6);
        $pdf->SetFont('DejaVu','',8);
        $i=0;//index for $results;
        $y=$yPos+6;
        $x=$pdf->getX();
        $more_events=0;
        church_admin_debug('***** Handling day '.$counter.' *****');
        foreach($results AS $row)
        {
            church_admin_debug('colcounter: '.$colcounter);
            church_admin_debug('cellwidth: '.$cellWidth);
            $theoreticalX = 10 + (($colcounter-1) * $cellWidth);
            church_admin_debug('Theoretical x: '.$theoreticalX);
            church_admin_debug('Actual x'. $pdf->getX());
            $currY=$pdf->getY();
            if(($currY >= $yPos + $cellHeight - 15)){
                if(empty($more_events)){
                    $pdf->setX($x);
                    
                    church_admin_debug('printing more events at '.$pdf->getX().', '.$pdf->getY());
                    $pdf->Cell($cellWidth,5,__('More events...','church-admin').$counter,'LR',0,'L',0,$permalink);
                    $more_events=1;
                }
            }
            else{
                $event = strip_tags(mysql2date(get_option('time_format'),$row->start_time)).' '.strip_tags( $row->title).$counter;
                //church_admin_debug($event.' at '.$pdf->getX().', '.$pdf->getY());
                $pdf->Multicell($cellWidth,5,$event,0,'L',0);
                $y=$pdf->getY();
                $pdf->setX($theoreticalX);
            }
            church_admin_debug('Current x: '.$pdf->getX());
            church_admin_debug('Current y: '.$pdf->getY());
        }
        $pdf->setXY($xPos+$cellWidth,$yPos);
        
    }
    // clean up
    $numcellsleft = $numinrow - $colcounter;
    if( $numcellsleft != $numinrow )
    {
        for( $counter = 0; $counter < $numcellsleft; $counter ++ )
        {
            $pdf->SetFillColor(125,125,125);
            $pdf->Cell($cellWidth,$cellHeight,'',1,$ln,'C',1);
            $emptycells ++;
        }
    }

        $pdf->Output();
}