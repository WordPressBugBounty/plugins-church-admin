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
 * Service Booking
 *
 **********************************/
function church_admin_service_bubble_pdf( $date_id,$service_id)
{
   global $wpdb;
    $nextservice=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$date_id.'"');
    if(defined('CA_DEBUG') )church_admin_debug(print_r( $nextservice,true) );
    $serviceDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE service_id="'.(int)$service_id.'" AND date_id="'.(int)$date_id.'" ORDER BY bubble_id ASC'; 
    $results=$wpdb->get_results( $sql);
    
    if(!empty( $results) )
    {
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
        $pdf = new fpdf();
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);
        $pdf->SetAutoPageBreak(1,15);
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $pdf->AddPage('P',get_option('church_admin_pdf_size') );
        $pageWidth=$pdf->GetPageWidth()-50;
        
        //Title
        $title=strip_tags(sprintf(__('Bookings for %1$s on %2$s at %3$s','church-admin' ) ,$serviceDetails->service_name,mysql2date(get_option('date_format'),$nextservice->start_date),$nextservice->start_time) );
        $pdf->SetFont('DejaVu','B',18);
        $pdf->SetFillColor(255,255,255);
        $pdf->MultiCell(0,8,$title,0,0,'C',FALSE);
        $pdf->SetFont('DejaVu','',8);
        $pdf->Ln(5);
        $pdf->Cell(50,8,strip_tags(__('Household/Bubble size','church-admin' ) ),1,0,'C');
        $pdf->Cell(0,8,strip_tags(__('Names','church-admin' ) ),1,1,'C');
        $bubbles=array();
        foreach( $results AS $row)
        {
            $bubbles[$row->bubble_id][]=strip_tags( $row->people_id);
        }
        foreach( $bubbles AS $id=>$people)
        {
            $count=count( $people);
            church_admin_debug( $count);
            $pdf->SetFont('DejaVu','',8);
            $pdf->Cell(50,8,$count,1,0,'C');
            $pdf->SetFont('DejaVu','',8);
            $pdf->Cell(0,8,implode(", ",$people),1,1,'L');
        }
        $pdf->Output();
        exit();
    }
}
function church_admin_service_booking_pdf( $date_id,$service_id,$alphabetical=FALSE)
{
  
    global $wpdb;
    $nextService=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$date_id.'"');
    if ( empty( $nextService) ) {
        echo $wpdb->last_query.'<br>'.strip_tags( __('No service date found','church-admin'));
        exit();
    }
    if(defined('CA_DEBUG') )church_admin_debug(print_r( $nextService,true) );
    $serviceDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
    if ( empty( $serviceDetails) )  {echo __('No service details found','church-admin');exit();}
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE service_id="'.(int)$service_id.'" AND date_id="'.(int)$date_id.'" ORDER BY people_id ASC';
    $results=$wpdb->get_results( $sql);
    
    if(!empty( $results) )
    {
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
        $pdf = new fpdf();
        $pdf->SetAutoPageBreak(1,15);
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);
        $pdf->AddPage('P',get_option('church_admin_pdf_size') );
        $pageWidth=$pdf->GetPageWidth()-50  ;
         // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        //Title
        $title=strip_tags(sprintf(__('Bookings for %1$s on %2$s at %3$s','church-admin' ) ,$serviceDetails->service_name,mysql2date(get_option('date_format'),$nextService->start_date),$nextService->start_time) );
        $pdf->SetFont('DejaVu','B',16);
        
        $pdf->SetFillColor(255,255,255);
        $pdf->MultiCell(0,8,$title,0,0,'C',FALSE);
        $pdf->Ln(5);
        
        /**************************************************************************************
        *
        *   Calculate bubble sizes
        *
        ***************************************************************************************/
            $bubbles=$wpdb->get_results('SELECT COUNT(bubble_id) AS counted FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE service_id="'.intval( $serviceDetails->service_id).'" AND date_id="'.(int)$nextService->date_id.'" GROUP BY bubble_id');
            
            if(!empty( $bubbles) )
            {
                $bubbleCounts=array();
                foreach( $bubbles AS $bubble)
                {
                    if ( empty( $bubbleCounts[$bubble->counted] ) )
                    {
                        $bubbleCounts[$bubble->counted]=1;
                    }
                    else $bubbleCounts[$bubble->counted]++;
                }
                ksort( $bubbleCounts);
                $pdf->SetFont('DejaVu','',8);
                $pdf->Cell(0,8,strip_tags(__('Household/Bubble counts','church-admin' ) ),0,1,'C');
                $pdf->Cell(75,8,strip_tags(__('Household/Bubble size','church-admin' )),1,0,'C');
                $pdf->Cell(75,8,strip_tags(__('Number of bookings that size','church-admin' )),1,1,'C');
                foreach( $bubbleCounts AS $size=>$count)
                {
                    $pdf->Cell(75,8,intval( $size),1,0,'C');
                $pdf->Cell(75,8,intval( $count),1,1,'C');
                }
                
            }
        
         $pdf->Ln(5);
        
        
        $pdf->SetFont('DejaVu','',8);
        $x=1;
        $pdf->SetX(10);
        
        $pdf->Cell(15,8,strip_tags(__('No','church-admin' ) ),1,0,'L');
        $pdf->Cell(15,8,strip_tags(__('Bubble','church-admin' ) ),1,0,'L');
        $pdf->Cell( $pageWidth/4,8,strip_tags(__('Name','church-admin' ) ),1,0,'L');
        $pdf->Cell( $pageWidth/2,8,strip_tags(__('Email','church-admin' ) ),1,0,'L');
        $pdf->Cell( $pageWidth/4,8,strip_tags(__('Phone','church-admin' ) ),1,1,'L');
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
            $pdf->Cell(15,8,$x,1,0,'L');
            $pdf->Cell(15,8,$row->bubble_id,1,0,'L');
            $pdf->Cell( $pageWidth/4,8,$row->people_id,1,0,'L');
            $pdf->Cell( $pageWidth/2,8,$row->email,1,0,'L');
            $pdf->Cell( $pageWidth/4,8,$row->phone,1,1,'L');
            $x++;
        }
         $pdf->Output();
        exit();
    }else {echo __('No bookings yet','church_admin');exit();}
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
 * Produces PDF of tickets
 *
 **********************************/

function church_admin_tickets_pdf( $booking_ref)
{
    
    global $wpdb;
    
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	$premium=get_option('church_admin_payment_gateway');
        
        $pdf=new FPDF();
        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $pdf->SetAutoPageBreak(1,10);
        $pdf->AddPage('P',get_option('church_admin_pdf_size') );
        $pdf->SetFont('DejaVu','B',16);
        
        $col=( $pdf->GetPageWidth()-20)/4;
        
        $pdf->SetX(10);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('DejaVu','B',18);
        
        
        //get booking
        $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_bookings WHERE booking_ref="'.esc_sql( $booking_ref).'"');
        church_admin_debug($results);
        if(!empty( $results) )
        {
            //Get Event Detail
            $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id ="'.intval( $results[0]->event_id).'"');
            church_admin_debug($event);
            //Title
            $title=strip_tags(sprintf(__('Booking for %1$s','church-admin' ) ,$event->title) );
            church_admin_debug($title);
            $pdf->SetFont('DejaVu','B',24);
            $pdf->Cell(0,24,$title,0,1,'C');
            
            //Location
            $eventDateTime=mysql2date(get_option('date_format').' '.get_option('time_format'),$event->event_date);
            $pdf->SetFont('DejaVu','',18);
            $pdf->Cell(0,8,$event->location,0,1,'C');
            $pdf->Cell(0,8,$eventDateTime,0,1,'C');
            $pdf->Ln(10);
            //tickets
            $total=0;
            foreach( $results AS $row)
            {
                $name=$row->first_name.' '.$row->last_name;
                
                //ticket_type
                $ticket=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_tickets WHERE ticket_id="'.(int)$row->ticket_type.'"');
                
                $y=$pdf->getY();
                $pdf->SetFont('DejaVu','B',12);
                $pdf->Cell( $col,35,$name,0,0,'L');
                $pdf->SetFont('DejaVu','',12);
                $pdf->Cell( $col,35,strip_tags(sprintf(__('%1$s ticket','church-admin' ) ,$ticket->name)),0,0,'L');
                if(!empty( $premium)&&!empty( $ticket->ticket_price)&&$ticket->ticket_price>0)
                {
                    $pdf->Cell( $col,35,$premium['currency_symbol'].$ticket->ticket_price,0,0,'L');
                    $total+=$ticket->ticket_price;
                }
                else{$pdf->Cell( $col,35,'',0,0,'L');}
                
                   $x=$pdf->GetX();                      
                //QR code
                $filename=md5( $booking_ref.'/'.$name).'.png';
                $upload_dir = wp_upload_dir();
                $filepath=$upload_dir['basedir'].'/church-admin-cache/';  
                if(extension_loaded('gd')){
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/qrcode/qrcode.class.php');
                    $qrcode = new QRcode($booking_ref.'/'.$name);
                    $qrcode->displayPNG($w=100, array(255,255,255), array(0,0,0), $filepath.$filename, 5);
                

                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/qrcode/qrcode.class.php');
                    $qrcode = new QRcode($booking_ref.'/'.$name, 'M'); // error level : L, M, Q, H
                    $qrcode->displayPNG(200, array(255,255,255), array(0,0,0), $filepath.$filename , 5);
                    $pdf->Image( $filepath.$filename ,$x,$y,35,35);
                    unlink($filepath.$filename);
                    $pdf->SetY( $y+40);
                }
            }
            if(!empty( $total) )
            {
                $thirdcol=$col*3;
                $pdf->Cell( $thirdcol,35,strip_tags(sprintf(__('Total %1$s ','church-admin' ) ,$premium['currency_symbol'].$total)),0,0,'L');
            }
        }
        else
        {
            $pdf->SetFont('DejaVu','','B',18);
            $pdf->Cell(0,8,strip_tags(__('No booking found','church-admin' ) ),0,1,'C');
        }
        $pdf->Output();
    
}
/**********************************
 *
 * Produces PDF of event bookings
 *
 **********************************/
function church_admin_event_tickets_pdf($event_id)
{
    global $wpdb;



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



/**
 *
 * Small GroupsPDF
 *
 * @author  Andy Moyle
 * @param    $member_type_id,$people_type_id
 * @return
 * @version  0.1
 *
 */
function church_admin_smallgroups_pdf( $loggedin,$title='Small groups')
{
	if(!empty( $loggedin)&&!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	global $wpdb,$wp_locale;
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id!="1" ORDER BY group_day,group_time';
	$results = $wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		//Build Header
		$pdf=new FPDF();
        // Add a Unicode font (uses UTF-8)
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
        $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $pdf->SetAutoPageBreak(1,10);
        $pdf->SetFillColor(245);
		
		$pdfSize=get_option('church_admin_pdf_size');
		$maxY=$pdf->GetPageHeight()-10;
		$pdf->AddPage('P',$pdfSize);
		//$pdf->Image(plugin_dir_path(dirname(__FILE__) ).'images/a4-map.png',0,0,$pdf->getPageWidth(),$pdf->getPageHeight() );
		$pdf->SetFont('DejaVu','B',24);
		$pdf->Cell(0,20,$title,0,2,'C');
        
		foreach( $results AS $row)
		{
			$y=$pdf->getY();
			if( $y+35>$maxY)
			{
				$pdf->AddPage('P',$pdfSize);
				//$pdf->Image(plugin_dir_path(dirname(__FILE__) ).'images/a4-map.png',0,0,$pdf->getPageWidth(),$pdf->getPageHeight() );
				$pdf->SetFont('DejaVu','B',24);
				$pdf->Cell(0,20,strip_tags(__('Small Groups','church-admin' ) ),0,2,'C');
                
			}
			//image
			if(!empty( $row->attachment_id) )
			{
				$imagePath=church_admin_scaled_image_path( $row->attachment_id,'medium') ;
                //church_admin_debug($image);
               
			}
			else
			{
				$imagePath=plugin_dir_path(dirname(__FILE__) ).'images/nopicture.png';
			}
            //church_admin_debug($imagePath);
            $mime_type = wp_get_image_mime($imagePath);
            //church_admin_debug($mime_type);
            if($mime_type != 'image/png' && $mime_type !='image/jpeg'){
                $imagePath=plugin_dir_path(dirname(__FILE__) ).'images/nopicture.png';
            }
			$currentY = $pdf->getY();
			if(!empty( $imagePath) ){
                
                $pdf->Image( $imagePath,10,$currentY,55);//added test for imagePath to stop error 2018-04-09
                
            }
			
			$pdf->SetX(70);
			$pdf->SetFont('DejaVu','B',12);
			$pdf->Cell(0,10,$row->group_name,0,1,'L',0);
			$pdf->SetX(70);
			$pdf->SetFont('DejaVu','',10);
    
            $pdf->Cell(0,8,sprintf('%1$s on %2$s',$row->frequency,$wp_locale->get_weekday( $row->group_day) ).' '.mysql2date(get_option('time_format'),$row->group_time),0,1,'L',0);
			$pdf->SetX(70);
            $address=$row->address;
           	$pdf->Cell(0,8,$address,0,1,'L',0);
            $pdf->SetX(70);
            if(!empty( $row->contact_number) ){$pdf->Cell(0,8,$row->contact_number,0,1,'L',0);}
			$pdf->SetX(10);
			
			$pdf->setY( $currentY+40);
		}
	}
		$pdf->Output();
}
/**
 *
 * Small Group members PDF
 *
 * @author  Andy Moyle
 * @param    $member_type_id,$people_type_id
 * @return
 * @version  0.1
 *
 */
function church_admin_smallgroup_pdf( $member_type_id,$people_type_id,$logged=1,$title='Small groups')
{
    /********************************************************************
    *
    *   Refactored 2020-12-27
    *   Cleaner code, better variable assignment
    *   Handles larger groups by giving them more than one column
    *
    *********************************************************************/
    global $wpdb;
    if(!empty( $loggedin)&&!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	global $wpdb,$people_type;
	$member_type=church_admin_member_types_array();
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
    /********************************************************************
    *
    *   Set up MySQL limiters for people type and member type
    *
    *********************************************************************/
    $ptype_sql='';
    if(!empty( $people_type_id) )
    {
		if(!is_array( $people_type_id) )  {$ptype=explode(',',$people_type_id);}else{$ptype=$people_type_id;}
		foreach( $ptype AS $key=>$value)  {if(church_admin_int_check( $value) )  $ptypesql[]='a.people_type_id='.$value;}
		if(!empty( $ptypesql) ) {$ptype_sql=' AND ('.implode(' OR ',$ptypesql).')';}else{$ptype_sql=' ';}
	}
	//handle member_type_id
	$memb_sql='';
	if( $member_type_id!=0)
	{
		if(!is_array( $member_type_id) )  {$memb=explode(',',$member_type_id);}else{$memb=$member_type_id;}
		foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.$value;}
		if(!empty( $membsql) ) {$memb_sql=' AND ('.implode(' OR ',$membsql).')';}
	}
    
    
    /********************************************************************
    *
    *   6 columns per page with 30 rows including header row
    *
    *********************************************************************/
    //Get groups
    $groups=array();
    $results=$wpdb->get_results('SELECT group_name,id FROM '.$wpdb->prefix.'church_admin_smallgroup');
	if(!empty( $results) )
	{
		foreach( $results AS $row)
        {
            $groups[$row->id]=$row->group_name;
        }
    }
    $columns=array();
    $col=1;
    /**************************************************************************************
    *
    *   Fill $columns array with at least one column per group, max 29 people per column
    *
    **************************************************************************************/
    foreach( $groups AS $id=>$group_name)
    {
        $people=$wpdb->get_results('SELECT a.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="smallgroup" and b.ID="'.(int)$id.'" '.$memb_sql.$ptype_sql.' ORDER BY a.last_name,a.first_name');
        if(!empty( $people) )
        {
            $row=1;
            foreach( $people AS $person)
            {
                if( $row==29)
                {//reset column
                    $row=1;
                    $col++;
                }
                $row++;
               $columns[$col][$group_name][]=church_admin_formatted_name($person); 
            
            }
        }
        $col++;
    }
    
    /********************************************************************
    *
    *   Launch PDF
    *
    *********************************************************************/
    $x=10;
    $pdf=new FPDF();
    // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    $pdf->SetAutoPageBreak(1,10);
    $pdf->AddPage('L',get_option('church_admin_pdf_size') );
    $pdf->SetFont('DejaVu','B',16);
    $pdf->Cell(0,10,$title,0,2,'C');     
    $colWidth=( $pdf->GetPageWidth()-20)/4;
    church_admin_debug("Col width $colWidth");
    $pdf->SetY(20);
    
    /********************************************************************
    *
    *   Build Output
    *
    *********************************************************************/
    $pageColNumber=1;
    $newX=10;
    $rowOffset=0;
    foreach( $columns AS $key=>$groupOutput)
    {
        
       
        $pdf->SetXY( $newX,20);//make sure Y is at top of page
        if( $pageColNumber % 5 == 0)
        {
            
            //new page every 5 columns
            $pdf->AddPage('L',get_option('church_admin_pdf_size') );
            $pdf->SetFont('DejaVu','B',16);
            $pdf->Cell(0,10,strip_tags($title ),0,2,'C');  
            $pageColNumber=1;
            $newX=10;
            $pdf->SetX( $newX);
            $pdf->SetY(20);
        }
        
        //output box with group name
        $groupName=key( $groupOutput);
        $count = count($groupOutput[$groupName]);
        if(!empty( $lastGroupName)&&$lastGroupName==$groupName)
        {
            //same group name so carry on incrementing row no.
            $rowOffset=$rowNo;
        }else $rowOffset=0;
        $pdf->SetFont('DejaVu','B',10);
		$pdf->Cell( $colWidth,8,$groupName.' ('.(int)$count.')',1,2,'C');
        $pdf->SetX( $newX);
        //output column   
		$pdf->SetFont('DejaVu','',10);
        $colText='';
        foreach( $groupOutput[$groupName] AS $peopleKey=>$personName)
        {
            $rowNo=$peopleKey+1+$rowOffset;
            $colText.=$rowNo.') '.trim( $personName)."\n";
        }
        
        $pdf->MultiCell( $colWidth,5,$colText,1);
        
        //reset x position to next column
        $newX=10+( $pageColNumber*$colWidth);
        $pageColNumber++;
        
       
        $lastGroupName=$groupName;  
    }
    $pdf->Output();
    exit();
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


/**
* This function produces a pdf of people in each ministry
*
* @author     	andymoyle
* @param		none
* @return		pdf
*
*/
function church_admin_ministry_pdf( $loggedin=1)
{
	if(!empty( $loggedin)&&!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	global $wpdb;
	$ministries=$ministry_names=array();
	$results=$wpdb->get_results('SELECT ministry,ID FROM '.$wpdb->prefix.'church_admin_ministries ORDER BY ministry ASC');
	foreach( $results AS $row)$ministry_names[(int)$row->ID]=$row->ministry;

	foreach( $ministry_names AS $key=>$ministry_name)
	{
			$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE b.meta_type="ministry" AND a.people_id=b.people_id AND b.ID="'.esc_sql( $key).'" ORDER BY a.last_name';
			$ministries[$ministry_name]=array();
			$people=$wpdb->get_results( $sql);
			if(!empty( $people) )
			{
				foreach( $people AS $person) {$ministries[$ministry_name][]=$person->name;}
			}

	}

	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	$pdf=new FPDF();
        // Add a Unicode font (uses UTF-8)
     // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    
	$pdf->AddPage('L',get_option('church_admin_pdf_size') );

	$pdf->SetFont('DejaVu','B',12);
	$pdf->Cell(0,10,strip_tags(__('Ministries','church-admin' ) ),0,0,'C');
	$pdf->SetFont('DejaVu','',10);
	$i=1;
	$x=15;
	$y=25;
	ksort( $ministries);
	foreach( $ministries AS $min_name=>$people)
	{
		if ( empty( $people) )$people=array(0=>strip_tags( __('No-one yet','church-admin')) );
		if( $i>6)
		{
			$pdf->AddPage('L',get_option('church_admin_pdf_size') ); $x=15; $x=25; $i=1;

			$pdf->SetFont('DejaVu','B',12);
			$pdf->Cell(0,6,strip_tags(__('Ministries','church-admin' ) ),0,0,'C');

		}
		$pdf->SetXY( $x,25);
		//ministry name
		$pdf->SetFont('DejaVu','B',10);
		$pdf->Cell(40,6,$min_name,1,0,'C');
		$pdf->SetXY( $x,31);
		//ministry people
		$pdf->SetFont('DejaVu','',10);
		$pdf->MultiCell(40,6,implode("\n",$people),1,'L');

		$i++;
		$x+=40;
		$y=30;
		$pdf->SetXY( $x,$y);
	}
	$pdf->Output();
	exit();
}




/**
 *
 * Horizontal PDF using new rota table and sized to fit
 *
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 *
 */

function church_admin_new_rota_pdf( $service_id,$date=NULL,$initials=0)
{
    if(!is_user_logged_in() )exit(__('Login required','church-admin') );
    if ( empty( $service_id) )exit(__('Service needs specifying','church-admin') );
    if ( empty( $date) )$date=date('Y-m-01');
    $title=strip_tags(sprintf(__('Schedule for %1$s','church-admin' ) ,date('M Y',strtotime( $date) )));
    church_admin_debug("Title: $title");
    global $wpdb;

	
	if(!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
    if ( empty( $service_id) ) exit(__('No service specified','church-admin') );  
	
    //work out number of columns
    $sql='SELECT rota_date FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$service_id.'" AND mtg_type="service" AND rota_date>="'.esc_sql( $date).'" GROUP BY rota_date ORDER BY rota_date LIMIT 5';
	$rotaDatesResults=$wpdb->get_results( $sql);
	$noOfColumns=$wpdb->num_rows+1;
    
	$dates=$displayDates=array();
    foreach( $rotaDatesResults AS $row)
    {
        $dates[]=$row->rota_date;
        $displayDates[]=mysql2date(get_option('date_format'),$row->rota_date);
    }
    array_unshift( $displayDates,__("Jobs",'church-admin') );
    //work out jobs
    $requiredRotaJobs=array();
    $rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
	
    $requiredRotaJobs=$rotaDates=array();
		foreach( $rota_tasks AS $rota_task)
		{
			$allServiceID=maybe_unserialize( $rota_task->service_id);
			if(is_array( $allServiceID)&&in_array( $service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=array('job'=>$rota_task->rota_task,'initials'=>$rota_task->initials);
        }
    
    $noOfRows=count( $requiredRotaJobs)+1;
   
    //create 2D array to create rows
    $tableRows=array();
    //first row is dates
    $tableRows[]=$displayDates;
    
    //now work through each job, with first item as job name
    foreach( $requiredRotaJobs AS $jobID=>$jobArray)
    {
        $thisRow=array( $jobArray['job'] );
        foreach( $dates AS $key=>$rotaDate)
        {
             
            if ( empty( $initials)&&empty( $jobArray['initials'] ) )
            {
				$thisRow[]=strip_tags(church_admin_rota_people( $rotaDate,$jobID,$service_id,'service') );
            }
            else
            {//initials
                        
                $thisRow[]=strip_tags(church_admin_rota_people_initials( $rotaDate,$jobID,$service_id,'service') );
            }
        }
        
        $tableRows[]=$thisRow;
    }
    
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pdf-table.php');

    $pdf=new PDF_MC_Table();
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    $pdfSize=get_option('church_admin_pdf_size');
    switch( $pdfSize)
    {
        case "A4":$width=247;break;
        case "Letter":$width=229;break;    
        case "Legal":$width=306;break;
    }
    $colWidth=ceil( $width/$noOfColumns);
    $pdf->AddPage('L',$pdfSize);
    
    $pdf->SetWidths(array( $width) );
    //Title Row
    $pdf->SetFont('DejaVu','',24);
    $pdf->SetAligns('C');
    $pdf->Row(array( $title),FALSE);
    $pdf->SetAligns('L');
    $pdf->Ln(5);
    //Create array with column widths & set
    $pdf->SetFont('DejaVu','',8);
    $widthsArray=array();
    for ( $x=1; $x<=$noOfColumns; $x++)  {$widthsArray[]=$colWidth;}
    church_admin_debug("******************");
    church_admin_debug(print_r( $widthsArray,TRUE) );
    $pdf->SetWidths( $widthsArray);
    //do each row
    for ( $i=0; $i<$noOfRows; $i++)
    {
        if( $i==0)  {$bold=TRUE;}else{$bold=FALSE;}
        $pdf->Row( $tableRows[$i],TRUE,$bold);
    }   
    $pdf->Output();
    
    
}

/************************************************************
*
*       Unit pdf
*
**************************************************************/
function church_admin_unit_pdf( $unit_id)
{
    
    global $wpdb;
    if(!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
    if ( empty( $unit_id) ) exit(__('No service specified','church-admin') ); 
    //work out number of columns
    $unitDetail=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_units WHERE unit_id="'.(int)$unit_id.'"');
    $units=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE unit_id="'.(int)$unit_id.'" ORDER BY name');
    if(!empty( $units) )
    {
        $titleRow=$peopleRow=array();
        $countUnits=$wpdb->num_rows;
        church_admin_debug("Count units {$countUnits}");
        foreach( $units AS $unit)
        {
            $titleRow[]=iconv('UTF-8', 'windows-1252',$unit->name);
            $peopleRow[]=iconv('UTF-8', 'windows-1252',str_replace(",","\r\n",church_admin_get_people_meta_list('unit',$unit->subunit_id) ));
        }
        
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pdf-table.php');
        $pdf=new PDF_MC_Table();
        $pdfSize=get_option('church_admin_pdf_size');
        switch( $pdfSize)
        {
            case "A4":$width=247;break;
            case "Letter":$width=229;break;    
            case "Legal":$width=306;break;
        }
        $colWidth=ceil( $width/5);
        $columnWidthArray=array( $colWidth,$colWidth,$colWidth,$colWidth,$colWidth);
       
        
        $pageCount=ceil( $countUnits/5);
        church_admin_debug("Page count: $pageCount");
        $pdf->AddPage('L',$pdfSize);
        $pdf->SetFont('Arial','',16);
        $pdf->SetWidths(array( $width) );
        $pdf->SetAligns('C');
        $pdf->Row(array(iconv('UTF-8', 'windows-1252',$unitDetail->name) ),FALSE);
        $pdf->SetWidths( $columnWidthArray);
        $pdf->SetAligns('L');
        $pdf->SetFont('Arial','',12);
        $i=0;
        for ( $pages=0; $pages<$pageCount; $pages++)
        {
           $headerRow=$peopleOutput=array();
            //Row of names of sub units
            for ( $x=$i; $x<$i+5; $x++)
            {
               if(!empty( $titleRow[$x] ) )  {$headerRow[]=$titleRow[$x];} 
                if(!empty( $peopleRow[$x] ) )  {$peopleOutput[]=$peopleRow[$x];}
            }
            
            $pdf->SetFont('Arial','B',12);
            $pdf->Row( $headerRow,TRUE);
            //Row of people in subunit;
            
            $pdf->SetFont('Arial','',12);
            $pdf->Row( $peopleOutput,TRUE);
            $i+=5;
        }
        $pdf->Output();
    }
}
/**
 *
 * Kids work pdf
 *
 * @author  Andy Moyle
 * @param   Array $member_type_id
 * @return  pdf
 * @version  0.2
 *
 * 2017-01-10 - corrected sql to make override work properly
 */
function church_admin_kidswork_pdf( $member_type_id,$loggedin=1)
{
	global $wpdb;
	if(!empty( $loggedin)&&!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	$kidsworkGroups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork ORDER BY youngest DESC');
	$memb_sql='';
  	if( $member_type_id!=0)
  	{
  		if(!is_array( $member_type_id) )  {$memb=explode(',',$member_type_id);}else{$memb=$member_type_id;}
      	foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='member_type_id="'.(int)$value.'"';}
      	if(!empty( $membsql) ) {$memb_sql=' ('.implode(' OR ',$membsql).')';}
	}

	$member_type=church_admin_member_types_array();
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	//cache small group pdf

	$kidsworkgroups=$groupnames=array();
	$count=0;
	$leader=array();

	$count=$noofgroups=0;
	//get groups

	if(!empty( $kidsworkGroups) )
	{
		foreach( $kidsworkGroups AS $row)
		{
			$noofgroups++;
			$groupname[$row->id]=$row->group_name;//title first
			//corrected sql 2017-01-10 to make sure override works properly!
			$sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name,kidswork_override FROM '.$wpdb->prefix.'church_admin_people WHERE '.$memb_sql.' AND (kidswork_override="'.esc_sql( $row->id).'" OR ((date_of_birth<"'.esc_sql($row->youngest).'" AND date_of_birth>"'.esc_sql($row->oldest).'") AND kidswork_override=0 ) ) ORDER BY last_name ';

			$peopleresults = $wpdb->get_results( $sql);
			if(!empty( $peopleresults) )
			{
				$colCount=1;
				foreach( $peopleresults AS $people)
				{
					$kidsworkgroups[$row->id][]=$colCount.') '.$people->name;
					$colCount++;//column count
					$count++;//total count for title area
				}
			}
		}
	}



	$counter=$noofgroups;

	$pdf=new FPDF();
     // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
	$pageno=0;
	$x=10;
	$y=20;
	$w=1;
	$width=55;
	$pdf->AddPage('L',get_option('church_admin_pdf_size') );
	$pdf->SetFont('DejaVu','B',16);

	$whichtype=array();

	$text=implode(", ",$whichtype).' '.strip_tags( __('Kidswork Group List','church-admin' ) ).' '.wp_date(get_option('date_format') ).'  '.$count.' '.strip_tags( __('people','church-admin'));
	$pdf->Cell(0,10,$text,0,2,'C');
	$pageno+=1;



	foreach( $groupname AS $id=>$groupname)
	{
		$text='';
		if( $w==6)
		{
			$pdf->SetFont('DejaVu','B',16);
			$pdf->AddPage('L',get_option('church_admin_pdf_size') );

			$whichtype=array();
			foreach( $memb AS $key=>$value)$whichtype[]=$member_type[$value];
			$text=implode(", ",$whichtype).' '.strip_tags( __('Kidswork Group List','church-admin' ) ).' '.wp_date(get_option('date_format') ).'  '.$count.' '.strip_tags( __('people','church-admin'));
			$pdf->Cell(0,10,$text,0,2,'C');
			$x=10;
			$y=20;
			$w=1;
		}
		$newx=$x+(( $w-1)*$width);
		if( $pageno>1) {$newx=$x+(( $z-( $pageno*5) )*$width);}
		$pdf->SetXY( $newx,$y);
		$pdf->SetFont('DejaVu','B',10);
		$pdf->Cell( $width,8,$groupname,1,1,'C');
		$pdf->SetFont('DejaVu','',10);
		$pdf->SetXY( $newx,$y+8);


			$pdf->SetFont('DejaVu','',10);
			$text='';
			if(!empty( $kidsworkgroups[$id] ) )$text=implode("\n",$kidsworkgroups[$id] );
			$pdf->MultiCell( $width,5,$text."\n",'LRB');

			$pdf->SetX( $newx);


		$pdf->Cell( $width,0,"",'LB',2,'L');
		$w++;
	}
	$pdf->Output();
}

function church_admin_kidswork_checkin_pdf( $groupIDs,$service_id,$inputDate)
{
	
	global $wpdb;
	if(!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	//begin to build PDF
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	$pdf=new FPDF();
        // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
	//format date
	$date=new DateTime( $inputDate);
	$outputDate=$date->format(get_option('date_format') );
	if(!empty( $groupIDs) )
    {
        foreach( $groupIDs AS $key=>$id)
        {

        //get kidswork details
        $kidsworkGroup=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork WHERE id="'.(int)$id.'"');
        if ( empty( $kidsworkGroup) )exit(__("Can't find that children's work group",'church-admin') );
        //get service
        $service=$wpdb->get_var('SELECT service_name FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');

        //get children in that group
        $sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name,kidswork_override FROM '.$wpdb->prefix.'church_admin_people WHERE  (kidswork_override="'.esc_sql( $id).'" OR ((date_of_birth<"'.esc_sql($kidsworkGroup->youngest).'" AND date_of_birth>"'.esc_sql($kidsworkGroup->oldest).'") AND kidswork_override=0 ) ) ORDER BY last_name ';
        $peopleresults = $wpdb->get_results( $sql);
        if(!empty( $peopleresults) )
        {




            //First page is for leaders to note check in and check out 
            $pdf->AddPage('P',get_option('church_admin_pdf_size') );
            $pdf->SetFont('DejaVu','B',16);
            $text=wp_kses_post((sprintf(__('%1$s at %2$s on %3$s','church-admin' ) ,$kidsworkGroup->group_name,$service,$outputDate)));
            $pdf->Cell(0,8,$text,0,2,'C');
            $pdf->Ln(5);
            $pdf->SetFont('DejaVu','',12);
            $text=__('Leaders should tick or write check in time for each child and hand over precut label to parent. When the parent returns with the slip and child is checked out, add the time to this sheet.',"church-admin");
            $pdf->MultiCell(0,5,$text,0,"L",FALSE);
            $pdf->SetX(10);
            $pdf->Ln(25);
            //output children
            $pdf->SetFont('DejaVu','B',12);
            $pdf->Cell(75,8,strip_tags(__("Name of child",'church-admin' ) ),1,0,'C');
            $pdf->Cell(30,8,strip_tags(__("Checkin",'church-admin' ) ),1,0,'C');	
            $pdf->Cell(30,8,strip_tags(__("Checkout",'church-admin' ) ),1,0,'C');	
            $pdf->Cell(50,8,strip_tags(__("Leaders Initials",'church-admin' ) ),1,1,'C');		
            $pdf->SetFont('DejaVu','',12);
            foreach( $peopleresults AS $people)
            {

                $pdf->Cell(75,8,$people->name,1,0,'L');
                $pdf->Cell(30,8,"",1,0,'L');	
                $pdf->Cell(30,8,"",1,0,'L');	
                $pdf->Cell(50,8,"",1,1);
            }
            //output slips for parents
            $pdf->AddPage('P',get_option('church_admin_pdf_size') );

            foreach( $peopleresults AS $people)
            {
                $currentY = $pdf->getY();
                if( $currentY + 50 > $pdf->GetPageHeight()-10)
                {
                    $pdf->AddPage('P',get_option('church_admin_pdf_size') );
                    $currentY = $pdf->getY();
                }
                $pdf->SetFont('DejaVu','B',16);
                $pdf->Cell(0,25,"",1,1);
                $pdf->setY( $currentY+5);
                $pdf->Cell(100,8,$people->name.' ('.$kidsworkGroup->group_name.')',0,1,'L');
                $pdf->SetFont('DejaVu','',12);
                $text=__("Please bring this slip to collect your child","church-admin");		   	
                $pdf->Cell(100,8,$text,0,1,'L');	
                $pdf->Ln(10);
            }


        }


    }
    }else
    {
        $pdf->Cell(0,8,strip_tags(__('No-one to book in yet','church-admin' ) ),0,2,'C');
    }
    $pdf->Output();
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





function church_admin_small_group_xml()
{
	if(!is_user_logged_in() )exit(__('You must be logged in to view the PDF','church-admin') );
	global $wpdb, $wp_locale;
	$days=array(0=>strip_tags( __('Sunday','church-admin' ) ),1=>strip_tags( __('Monday','church-admin' ) ),2=>strip_tags( __('Tuesday','church-admin' ) ),3=>strip_tags( __('Wednesday','church-admin' ) ),4=>strip_tags( __('Thursday','church-admin' ) ),5=>strip_tags( __('Friday','church-admin' ) ),6=>strip_tags( __('Saturday','church-admin') ));
	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE lat!="" AND lng!="" ORDER BY group_day, group_time');
	if(!empty( $results) )
	{
		$color_def = array
	('1'=>"FF0000",'2'=>"00FF00",'3'=>"0000FF",'4'=>"FFF000",'5'=>"00FFFF",'6'=>"FF00FF",'7'=>"CCCCCC",	8  => "FF7F00",	9  => "7F7F7F",	10 => "BFBFBF",	11 => "007F00",
		12 => "7FFF00",	13 => "00007F",	14 => "7F0000",	15 => "7F4000",
		16 => "FF9933",	17 => "007F7F",	18 => "7F007F",	19 => "007F7F",
		20 => "7F00FF",	21 => "3399CC",	22 => "CCFFCC",	23 => "006633",
		24 => "FF0033",	25 => "B21919",	26 => "993300",	27 => "CC9933",
		28 => "999933",	29 => "FFFFBF",	30 => "FFFF7F",31  => "000000"
	);

		header("Content-type: text/xml;charset=utf-8");
		echo '<markers>';
		$x=1;
		foreach( $results AS $row)
		{
			
			
			// Iterate through the rows, printing XML nodes for each

			// ADD TO XML DOCUMENT NODE
				echo '<marker ';
				echo 'pinColor="'.$color_def[$x].'" ';
				echo 'lat="' . $row->lat . '" ';
				echo 'lng="' . $row->lng . '" ';
				echo 'smallgroup_name="'.htmlspecialchars( $row->group_name,ENT_QUOTES).'" ';
				echo 'when="'.htmlspecialchars(sprintf('%1$s on %2$s at %3$s',$row->frequency,$wp_locale->get_weekday( $row->group_day),mysql2date(get_option('time_format'),$row->group_time) ),ENT_QUOTES).'" ';
				echo 'smallgroup_id="'.$row->id.'" ';
				echo 'address="'.htmlspecialchars( $row->address,ENT_QUOTES).'" ';
				
				
				echo '/>';
				$x++;
		}
		// End XML file
		echo '</markers>';

	}
	exit();
}


function church_admin_smallgroup_signup_pdf( $title)
{
    global $wpdb,$wp_locale;
    $groupDetail=array();
    $groups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
    if(!empty( $groups) )
    {
        foreach( $groups AS $group)
        {
            $groupDay=$readable = '';
            if(!empty($group->groupDay)&& church_admin_int_check($group->groupDay) && $group->groupDay<=6){
                $readable = strip_tags(sprintf(__('%1$s meeting at %2$s on %3$s at %4$s','church-admin' ) ,$group->group_name,$group->address, $wp_locale->get_weekday( $group->group_day) ,mysql2date(get_option('time_format'),$group->group_time) ));
                $groupDay = strip_tags( $wp_locale->get_weekday( $group->group_day) );
            }
            $currentAttendees=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup" AND ID="'.(int)$group->id.'"');
            $spaces=$group->max_attendees-$currentAttendees;
            $groupDetail[]=array(
                'groupName'=>$group->group_name,
                'groupDay'=>$groupDay,
                'groupTime'=>mysql2date(get_option('time_format'),$group->group_time),
                'space'=>(int)$spaces,
                'readable'=>$readable
                
            );
		}
    }
    church_admin_debug( $groupDetail);
    if ( empty( $title) )$title=__('Small group signup','church-admin');
    /******************
     * CREATE PDF
     *****************/
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	$pdf=new FPDF();
    // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);	
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    //Title
    $pdf->AddPage('P',get_option('church_admin_pdf_size') );
	$pdf->SetFont('DejaVu','B',16);
    $pdf->Cell(0,10,$title,0,2,'C');
    $pdf->SetFont('DejaVu','',16);
    $xTwo=$pdf->GetPageWidth()-10;//righthand x value for lines
    $pdf->SetAutoPageBreak(1,10);
    $pdf->SetLineWidth(0.2);
    $pdf->SetDrawColor(22,22,22);
    //work through groups
    foreach( $groupDetail AS $key=>$detail)
    {
        if(!empty( $detail['space'] ) && $detail['space']>0)
        {
            $pdf->SetFont('DejaVu','B',8);
            $pdf->Cell(0,10,$detail['readable'],0,1,'C');
            
            $pdf->SetFont('Arial','',16);
            for ( $x=1; $x<=$detail['space']; $x++)
            {
                $pdf->Cell(0,15,(int)$x,'B',1,'L');
            }
            $pdf->Ln(15);
        }


    }
    $pdf->Output();
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
        $PDFtitle = strip_tags( sprintf(__('Photo permission for %1$s','church-admin'),implode(', ',$title)));
        $pdf->Cell(0,10,$PDFtitle,0,2,'C');
        $pdf->SetFont('DejaVu','B',12);
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
    $facilities=church_admin_calendar_facilities_array();
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