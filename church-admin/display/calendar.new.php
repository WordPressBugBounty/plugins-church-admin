<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/**
 *
 * Displays calendar via shortcode
 *
 * @author  Andy Moyle
 * @param    null
 * @return   $out
 * @version  0.1
 *
 */
 
function church_admin_display_new_calendar($cat_id,$fac_ids)
{
   
    $out='';
    $out.='<script>var cat_id="'.esc_attr($cat_id).'";var facilities_ids="'.esc_attr($fac_ids).'";</script>';
    
    
    /***********************
     * Monthly PDF link
     ***********************/
    $params = array('ca_download'=>'monthly-calendar-pdf');
    if(!empty($cat_id))$params['cat_id']=$cat_id;
    if(!empty($fac_ids))$params['facilities_id']=$fac_ids;
    $params['url']=get_permalink();
    $url = add_query_arg( $params , site_url() );
    $out.='<p><a href="'.$url.'">'.__('This calendar PDF','church-admin').'</a></p>';
    /***********************
     * End Monthly PDF link
     ***********************/
    
    
     $out.= church_admin_calendar_container($cat_id,$fac_ids);
    return $out;
}


function church_admin_calendar_container($cat_id,$fac_ids)
{
    global $wp_locale,$wpdb;

    $out='';




    $date=wp_date('Y-m-d');
    $events=church_admin_day_events_array( $date,$cat_id,$fac_ids);
    $todayEvents='<li>'.wp_kses_post(implode('<br>',array_filter( $events) )).'</li>';
    
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
    if(!empty($fac_ids))
    {
        $fac_id_array = explode(",",$fac_ids);
        foreach($fac_id_array AS $key => $id){
           if(!empty($facilities[$id])){
                $display_facilities[]=$facilities[$id];
           }
        }
    }
 
    


    if(!empty($display_categories) || !empty($display_facilities)){
        $out.='<p>';
        if(!empty($display_categories)){
            $out.= __('Categories: ','church-admin').implode(', ',$display_categories).'<br>';
        }
        if(!empty($display_facilities)){
            $out.= __('Facilities: ','church-admin').implode(', ',$display_facilities).'<br>';
        }
        $out.='</p>';
    }



  
    $out.='<div class="ca-calendar">';
    $out.='<div class="ca-calendar-sidebar">';
    $out.=' <div class="ca-sidebar-title-area"><h3 class="ca-sidebar-heading" id="ca-date"></h3><div class="ca-today-button"><button class="btn btn-warning ca-calendar-nav" data-date="'.date('Y-m-d').'" data-cat_id="'.esc_attr($cat_id).'" data-facilities_id="'.esc_attr($fac_ids).'">'.esc_html( __('Today','church-admin' ) ).'</button></div></div><ul class="ca-sidebar-list">'.$todayEvents.'</ul>';
    $out.='</div>';
    $out.=' <div class="ca-calendar-render"><div class="ca-calendar-top-bar"><span class="ca-top-bar-days"><svg viewBox="0 0 100 100">
  <text x="25%" y="50%">'.esc_html( $wp_locale->get_weekday_abbrev( esc_html( $wp_locale->get_weekday(0) )) ).'</text>
    </svg></span><span class="ca-top-bar-days"><svg viewBox="0 0 100 100">
    <text x="25%" y="50%">'.esc_html( $wp_locale->get_weekday_abbrev(  esc_html( $wp_locale->get_weekday(1) )) ).'</text>
    </svg></span><span class="ca-top-bar-days"><svg viewBox="0 0 100 100">
    <text x="25%" y="50%">'.esc_html( $wp_locale->get_weekday_abbrev( esc_html( $wp_locale->get_weekday(2 ) ) ) ).'</text>
    </svg></span><span class="ca-top-bar-days"><svg viewBox="0 0 100 100">
    <text x="25%" y="50%">'.esc_html( $wp_locale->get_weekday_abbrev( esc_html( $wp_locale->get_weekday(3 ) ) ) ).'</text>
    </svg></span><span class="ca-top-bar-days"><svg viewBox="0 0 100 100">
    <text x="25%" y="50%">'.esc_html( $wp_locale->get_weekday_abbrev( esc_html( $wp_locale->get_weekday(4) ) ) ).'</text>
    </svg></span><span class="ca-top-bar-days"><svg viewBox="0 0 100 100">
    <text x="25%" y="50%">'.esc_html( $wp_locale->get_weekday_abbrev( esc_html( $wp_locale->get_weekday( 5) ) ) ).'</text>
    </svg></span><span class="ca-top-bar-days"><svg viewBox="0 0 100 100">
    <text x="25%" y="50%">'.esc_html( $wp_locale->get_weekday_abbrev( esc_html( $wp_locale->get_weekday( 6) ) ) ).'</text>
    </svg></span></div>'."\r\n";
    $day=$events=1;
    for ( $week=1; $week<=5; $week++)
    {
        $out.='<div class="ca-week">';
        for ( $d=1; $d<=7; $d++)
        {
            $out.='<div class="ca-day " id="day'.esc_attr($day).'" data-date="" data-cat_id="'.esc_attr($cat_id).'" data-facilities_id="'.esc_attr($fac_ids).'"> </div>'."\r\n";
            $day++;
            $events++;
        }
        $out.='</div>'."\r\n";
    }
     $out.='</div></div><!-- End of Church Admin Calendar -->'."\r\n";
    $out.='<script>var caCalendar=1;</script>'."\r\n";
    $out.='<script>
    var nonce = "'.wp_create_nonce("calendar").'";
	jQuery(document).ready(function($){
        
		$("body").on("click",".rota-expander",function(){
			console.log("click");
			$(".rota-details").hide();
			var id	=$(this).attr("data-id");
			console.log(id);
			var what = $(this).attr("data-what");
			if(what==="show"){
				$(this).attr("data-what","hide");
				$(".date"+id).show();
				$("#toggle"+id).removeClass("dashicons-arrow-up-alt2");
				$("#toggle"+id).addClass("dashicons-arrow-down-alt2");
			}else
			{
				$(this).attr("data-what","show");
				$("#toggle"+id).addClass("dashicons-arrow-up-alt2");
				$("#toggle"+id).removeClass("dashicons-arrow-down-alt2");
			}
		});
	})</script>'."\r\n";
     return $out;
 }

function church_admin_render_month( $month,$year,$day,$cat_id,$fac_ids)
{
    church_admin_debug('**** church_admin_render_month *****');


    global $wpdb;
    if ( empty( $month) )  {$month=date('m');}
    if ( empty( $year) )  {$year=date('Y');}
    if ( empty( $day) )  {$day='01';}
    $date=$year.'-'.$month.'-'.$day;
    $d=new DateTime( $date);
    $prev=church_admin_addMonths( $d,-1);
    $next=church_admin_addMonths( $d,2);
    
    // find out the number of days in the month
    $numDaysInMonth =  date ('t',strtotime( $year.'-'.$month.'-01') );
    //integer for start date (0 Sunday etc)
    $startday = date('w',strtotime( $year.'-'.$month.'-01') );
    church_admin_debug('Year' . $year);
    church_admin_debug('Month' . $month);
    church_admin_debug('num days' . $numDaysInMonth);
    church_admin_debug('start days' . $startday );
    // get the month as a name
    $monthname =date('F',strtotime( $year.'-'.$month.'-01') );
    
    
    //$sql='SELECT COUNT(*) AS eventsCount,DAY(start_date) AS day FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE MONTH(start_date)="'. esc_sql($month).'" AND YEAR(start_Date)="'.esc_sql( $year).'" GROUP BY start_date';
    $display_categories = $display_facilities = array();
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
	if ( !empty( $fac_ids) ){
        church_admin_debug('Month render with facilities_id '.$fac_ids);
		$facs=explode(",",$fac_ids);
		foreach( $facs AS $key=>$value)  {
			if(church_admin_int_check( trim($value ) ) )  {
				$facsql[]='c.meta_value='.(int)$value;
				$display_facilities[]=$facilities[(int)$value];
			}
		}
        $FACSQL = !empty($facsql) ? ' AND ('.implode(' OR ',$facsql).') ':'';
        $sql='SELECT COUNT(*) AS eventsCount, DAY(a.start_date) AS day,a.event_id FROM '.$wpdb->prefix.'church_admin_calendar_date a  , '.$wpdb->prefix.'church_admin_calendar_category b , '.$wpdb->prefix.'church_admin_calendar_meta c WHERE a.cat_id=b.cat_id '.$cat_sql.' AND a.event_id=c.event_id AND c.meta_type="facility_id" AND MONTH(a.start_date)="'. esc_sql($month).'" AND YEAR(a.start_date)="'.esc_sql( $year).'"  '.$FACSQL.'  GROUP BY start_date,a.event_id';
		
		
	}
	else
	{
		//no facilities ID query
		//$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON a.cat_id=b.cat_id  WHERE  a.start_date="'.esc_sql( $date).'"  '.$cat_sql.' ORDER BY a.start_date,a.start_time';
		//church_admin_debug('Month render no fac_ids');
        $sql='SELECT COUNT(*) AS eventsCount,DAY(a.start_date) AS day FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b  WHERE a.cat_id=b.cat_id '.$cat_sql.' AND MONTH(a.start_date)="'. esc_sql($month).'" AND YEAR(a.start_Date)="'.esc_sql( $year).'"  '.$cat_sql.' GROUP BY start_date';

	}

    //church_admin_debug($sql);

    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        $events=array();
        foreach( $results AS $row)
        {
            $events[$row->day]=$row->eventsCount;
        }
    }
    //output array of dates, note that on the calendar the first day box is day0
    $dates=array();
    $dayNo=0;
    for ( $calendarDays=0; $calendarDays<=35; $calendarDays++)
    {
        if( $calendarDays<$startday)  {$dates[$calendarDays]=NULL;}
        elseif( $dayNo<=$numDaysInMonth)
        {
            if(date('j')==$dayNo && $date==date('Y-m-d') )
            {
                $dateNumbers[$calendarDays]=str_pad( $dayNo, 2, 0, STR_PAD_LEFT);
                $dates[$calendarDays]='<span class="ca-today">'.$dayNo.'</span>';
                if(!empty( $events[$dayNo] ) )$dates[$calendarDays].='<br><span class="ca-events">'.esc_html( sprintf(__('%1$s events','church-admin' ) ,$events[$dayNo] ) ).'</span>';
                $dayNo++;
            }
            else
            {
                $dateNumbers[$calendarDays]=str_pad( $dayNo, 2, 0, STR_PAD_LEFT);
                $dates[$calendarDays]=$dayNo;
                 if(!empty( $events[$dayNo] ) )$dates[$calendarDays].='<br><span class="ca-events">'.esc_html( sprintf(__('%1$s events','church-admin' ) ,$events[$dayNo] )).'</span>';
                $dayNo++;
            }
        }
        else{$dates[$calendarDays]=NULL;}
        
    }
    $title='<p style="text-align:center"><button class="ca-calendar-nav btn btn-info" data-date="'.esc_attr($prev->format('Y-m-d')).'">'.esc_html( __('Prev','church-admin' ) ).'</button> '.mysql2date('M Y',$date).' <button class="ca-calendar-nav  btn btn-info" data-date="'.esc_attr($next->format('Y-m-d') ).'">'.esc_html( __('Next','church-admin' ) ).'</button></p>';
   
    $output=array('dates'=>$dates,'dateNumber'=>$dateNumbers,'title'=> $title ,'eventList'=>church_admin_day_events_array( $date,$cat_id,$fac_ids),'partDate'=>esc_html($year.'-'.$month.'-'));
    header('Access-Control-Max-Age: 1728000');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
    header('Access-Control-Allow-Credentials: true');
    echo json_encode( $output);
    exit();
    
}

function church_admin_render_day( $date,$cat_id,$fac_ids)
{
    
    $output=church_admin_day_events_array( $date,$cat_id,$fac_ids);
    header('Access-Control-Max-Age: 1728000');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
    header('Access-Control-Allow-Credentials: true');
    echo json_encode( $output);
    exit();
    
}
function church_admin_day_events_array( $date,$cat_id,$fac_ids)
{
    global $wpdb;


    
	$categories=church_admin_calendar_categories_array();


    //$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a,'.$wpdb->prefix.'church_admin_calendar_category b WHERE a.general_calendar=1 AND a.cat_id=b.cat_id  AND a.start_date="'.esc_sql( $date).'" ORDER BY a.start_time';
    //$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON b.cat_id = a.cat_id WHERE a.start_date="'.esc_sql( $date).'"  ORDER BY a.start_time';
    //process categories
    $cat_sql='';
	$catsql=array();
    if ( empty( $cat_id) )  {$cat_sql="";}
    else
    {
        
        $cats=explode(',',$cat_id);
        foreach( $cats AS $key=>$value)  {
          if(church_admin_int_check( $value) )  {
              $catsql[]='a.cat_id='.(int)$value;
              if(!empty($categories[$value])){$display_categories[]=$categories[$value];}
          }
      }
        if(!empty( $catsql) ) {$cat_sql=' AND ('.implode(' || ',$catsql).')';}
      
  }
    //process facilities
	$facsql=array();
	$fac_sql='';
	if ( !empty( $fac_ids) ){
		$facs=explode(",",$fac_ids);
		foreach( $facs AS $key=>$value)  {
			if(church_admin_int_check( trim( $value)) )  {
				$facsql[]='c.meta_value='.(int)$value;
				if(!empty($facilities[$value])){$display_facilities[]=$facilities[(int)$value];}
			}
		}
        $FACSQL = !empty($facsql) ? ' AND ('.implode(' OR ',$facsql).')':'';

		$sql='SELECT a.*, b.*,c.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b , '.$wpdb->prefix.'church_admin_calendar_meta c  WHERE a.cat_id=b.cat_id AND a.event_id=c.event_id AND c.meta_type="facility_id" AND a.start_date="'.esc_sql( $date).'"  '.$cat_sql.' '.$FACSQL.' ORDER BY a.start_date,a.start_time';
		
	}
	else
	{
		//no facilities ID query
		$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON a.cat_id=b.cat_id  WHERE  a.general_calendar=1 AND a.start_date="'.esc_sql( $date).'"  '.$cat_sql.' ORDER BY a.start_date,a.start_time';
		


	}

    //church_admin_debug($sql);

    
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        $output=array('<li style="font-size:larger;margin-bottom:20px">'.esc_html(mysql2date(get_option('date_format'),$date)).'</li>');
        foreach( $results AS $row)
        {
            if(!empty($row->service_id)){
                $row->rota = church_admin_retrieve_rota_data_array($row->service_id,$row->start_date,'service');
            }
            $day[$row->event_id]='<p style="border-left:5px solid '.esc_attr($row->bgcolor).';padding-left:3px;"><strong>'.esc_html( $row->title).'</strong><br>';
            if(!empty($row->event_image))
            {
                $day[$row->event_id].= wp_get_attachment_image( $row->event_image, 'medium', false).'<br>';
            }

            if( $row->start_time=='00:00:00' && $row->end_time=='23:59:00')
            {
                $day[$row->event_id].=__('All day event','church-admin').'<br>';
            }else {
                $day[$row->event_id].=esc_html( mysql2date(get_option('time_format'),$row->start_time).' - '.mysql2date(get_option('time_format'),$row->end_time) ).'<br>';
            }
            if(!empty($row->description)){
                $day[$row->event_id].=wp_kses_post(church_admin_excerpt( $row->description,50,'...') ).'<br>';
            }
            if(!empty($row->location)){
                $day[$row->event_id].=esc_html( $row->location).'<br>';
            }
            if(!empty( $row->link) )
            {
                if(!empty( $row->link_title) )  {$title=esc_html( $row->link_title);}else{$title=esc_html(__('More information...','church-admin'));}
                $day[$row->event_id].='<a href="'.esc_url( $row->link).'">'.$title.'</a>'.'<br>';
            }
            if(!empty($row->rota)){
				$day[$row->event_id].='<table><tr ><th scope="row" data-id="'.(int)$row->event_id.'">'.esc_html('Who is doing what','church-admin').'</th><td><span class="rota-expander dashicons dashicons-arrow-up-alt2" id="toggle'.(int)$row->event_id.'" data-what="show" data-id="'.(int)$row->event_id.'" ></span></td></tr>';
				foreach($row->rota AS $job=>$who){
					$day[$row->event_id].='<tr class="rota-details date'.(int)$row->event_id.'" style="display:none"><th scope="row">'.esc_html($job).'</th><td>'.esc_html($who).'</td></tr>';
				}
				$day[$row->event_id].='</table>';
			}
            if(!empty($row->service_id)){
                //add link to rota for this service
               if(church_admin_level_check('Rota')){
                    $day[$row->event_id].='<p><a href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=edit-rota&service_id='.(int)$row->service_id.'&rota_date='.esc_attr($row->start_date),'edit-rota').'">'.esc_html(__('Edit schedule','church-admin')).'</a></p>';
               }
               
               $day[$row->event_id].=church_admin_rota_popup($row->service_id,$row->start_date);
               
            }

            $day[$row->event_id].='<a  rel="nofollow" class="vcf-link" href="'.esc_url(site_url().'?ca_download=ical&date_id='.(int)$row->date_id).'"><span class="ca-dashicons dashicons dashicons-download"></span>'.esc_html(__('iCal download','church-admin')).'</a></p>';


            
        }
        $output[]='<li >'.implode('<p>&nbsp;</p>',array_filter( $day) ).'</li>';
            
    }else 
    {
        $output=array('<li style="font-size:larger;margin-bottom:20px">'.esc_html(mysql2date(get_option('date_format'),$date)).'</li><li>'.esc_html( __('No events today','church-admin') ).'</li>');
    }
    return $output;
}

