<?php
function church_admin_display_calendar( $facilities_id=NULL,$cat_id=null)
{
    
    global $current_user,$wpdb;

    if(!empty($cat_id) && church_admin_int_check($cat_id))
    {
        $catSQL = ' AND a.cat_id="'.(int)$cat_id.'" ';
    }
    else{
        $catSQL='';
    }

  


    $allowed_html=array(
        'a' => array(
            'href' => array(),
            'title' => array()
        ),
        'p' => array(),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
        'div'=>array('style'=>array(),),
        'span'=>array('style'=>array(),'class'=>array()),
        'ul'=>array(),
        'li'=>array('class'=>array(),'style'=>array())
    );
	  wp_get_current_user();
	  $out='';

      $params = array('ca_download'=>'monthly-calendar-pdf');
      if(!empty($cat_id))$params['cat_id']=$cat_id;
      if(!empty($fac_ids))$params['facilities_id']=$fac_ids;
  
      $url = add_query_arg( $params , site_url() );
  
      
      $out.='<p><a href="'.$url.'">'.__('This calendar PDF','church-admin').'</a></p>';


    if(isset( $_POST['ca_month'] ) && isset( $_POST['ca_year'] ) )  { 
        $current=mktime(12,0,0,sanitize_text_field(stripslashes($_POST['ca_month']) ),14,sanitize_text_field(stripslashes($_POST['ca_year'] ) ));
    }else{
        $current=current_time('timestamp');
    }
	$thismonth = (int)wp_date("m",$current);
	$thisyear = wp_date( "Y",$current );
	$actualyear=wp_date("Y");
	$next = strtotime("+1 month",$current);
	$previous = strtotime("-1 month",$current);
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


   
$out.='<table class="church_admin_calendar" style="width:100%">
<tr>
        <td colspan="7" class="calendar-date-switcher">
            <form method="post" action="">
'.esc_html( __('Month','church-admin' ) ).'<select name="ca_month">
';
$first=$option='';
for ( $q=0; $q<=12; $q++)
{
    $mon=date('m',( $current+$q*(28*24*60*60) ));
    $MON=date('M',( $current+$q*(28*24*60*60) ));
    if(isset( $_POST['ca_month'] )&& $_POST['ca_month']==$mon) {
        $first='<option value="'.esc_attr($mon).'" selected="selected">'.esc_html($MON).'</option>';
    }else{
        $out.= '<option value="'.esc_attr($mon).'" selected="selected">'.esc_html($MON).'</option>';
    }
}
$out.=$first.$option;
$out.='</select>'.esc_html( __('Year','church-admin' ) ).'<select name="ca_year">';
$first=$option='';
for ( $x=$actualyear; $x<=$actualyear+15; $x++)
{
    if(isset( $_POST['ca_year'] )&&$_POST['ca_year']==$x)
    {
	$first='<option value="'.(int)$x.'" >'.(int)$x.'</option>';
    }
    else
    {
	$option.='<option value="'.(int)$x.'" >'.(int)$x.'</option>';
    }
}
$out.=$first.$option;
$out.='</select><input  type="submit" value="'.esc_html( __('Submit','church-admin' ) ).'" /></form></td></tr> ';
$out.=
'<tr>
               
                    
    <td colspan="3" class="calendar-date-switcher">';
if( $now==date('M Y') )  {
    $out.='&nbsp;';
}else{
    $out.='<form action="'.esc_attr(get_permalink()).'" name="previous" method="post"><input type="hidden" name="ca_month" value="'.esc_attr(date('m',strtotime("$now -1 month") )).'" /><input type="hidden" name="ca_year" value="'.esc_attr(date('Y',strtotime("$now -1 month") )).'" /><input type="submit" value="'.esc_attr(__('Previous','church-admin') ) .'" class="calendar-date-switcher" /></form>';
}
$out.='</td>
                    <td class="calendar-date-switcher">'.$now.'</td>
                    <td class="calendar-date-switcher" colspan="3"><form action="'.get_permalink().'" method="post"><input type="hidden" name="ca_month" value="'.esc_attr(date('m',strtotime( $now.' +1 month') ) ).'" /><input type="hidden" name="ca_year" value="'.esc_attr( date('Y',strtotime( $now.' +1 month') ) ).'" /><input type="submit" class="calendar-date-switcher" value="'.esc_attr(__('Next','church-admin')).'" /></form></td>
                
                
</tr>
		
    <tr><td  ><strong>'.esc_html(__('Sunday','church-admin') ).'</strong></td>
    <td ><strong>'.esc_html(__('Monday','church-admin') ).'</strong></td>
    <td ><strong>'.esc_html(__('Tuesday','church-admin') ).'</strong></td>
    <td ><strong>'.esc_html(__('Wednesday','church-admin') ).'</strong></td>
    <td ><strong>'.esc_html(__('Thursday','church-admin') ).'</strong></td>
    <td ><strong>'.esc_html(__('Friday','church-admin') ).'</strong></td>
    <td ><strong>'.esc_html(__('Saturday','church-admin') ).'</strong></td>
    </tr>
    <tr>';
// put render empty cells
$emptycells = 0;
for( $counter = 0; $counter <  $startday; $counter ++ )
{
    $out.="\t\t<td class=\"church_admin_empty_cell\">&nbsp;</td>\n";
    $emptycells ++;
}
// renders the days
$rowcounter = $emptycells;
$numinrow = 7;
for( $counter = 1; $counter <= $numdaysinmonth; $counter ++ )
{
        $rowcounter ++;
    $out.="\t\t<td align=\"left\"><strong>".(int)$counter."</strong><br>";
    /*
    //put events for day in here
    $sqlnow="$thisyear-$thismonth-".sprintf('%02d', $counter);
    if ( empty( $facilities_id) )  {
        $sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a,'.$wpdb->prefix.'church_admin_calendar_category b WHERE a.general_calendar=1 AND a.cat_id=b.cat_id '.$catSQL.' AND a.start_date="'.$sqlnow.'" ORDER BY a.start_time';
    }
	else{
        $sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a,'.$wpdb->prefix.'church_admin_calendar_category b WHERE a.facilities_id="'.esc_sql( $facilities_id).'" '.$catSQL.' AND a.cat_id=b.cat_id  AND a.start_date="'.$sqlnow.'" ORDER BY a.start_time';
    }
	*/
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
            if(church_admin_int_check( trim( $value)) )  {
                $facsql[]='c.meta_value='.(int)$value;
                $display_facilities[]=$facilities[(int)$value];
            }
        }
        $FACSQL = !empty($facsql) ? ' AND ('.implode(' OR ',$facsql).') ':'';
        $sql='SELECT a.*,b.*,c.* FROM '.$wpdb->prefix.'church_admin_calendar_date a  , '.$wpdb->prefix.'church_admin_calendar_category b , '.$wpdb->prefix.'church_admin_calendar_meta c WHERE a.cat_id=b.cat_id '.$cat_sql.' AND a.event_id=c.event_id AND c.meta_type="facility_id" AND a.start_date="'.$sqlnow.'"  '.$FACSQL.'  ORDER BY a.start_time';
        
        
    }
    else
    {
        //no facilities ID query
        //$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON a.cat_id=b.cat_id  WHERE  a.start_date="'.esc_sql( $date).'"  '.$cat_sql.' ORDER BY a.start_date,a.start_time';
        //church_admin_debug('Month render no fac_ids');
        $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b  WHERE a.cat_id=b.cat_id '.$cat_sql.' AND a.start_date="'.$sqlnow.'" ORDER BY a.start_time';

    }


    //church_admin_debug($sql);

	$result=$wpdb->get_results( $sql);
    if( $wpdb->num_rows=='0')
    {
        $out.='&nbsp;<br>&nbsp;<br>';
    }
    else
    {
        $day=array();
        foreach( $result AS $row)
        {
			$border='#CCC';
			$text='#000';
			$border=church_admin_adjust_brightness( $row->bgcolor, -50);
			$text=church_admin_adjust_brightness( $row->bgcolor, -100);
			$color=substr( $row->bgcolor,1,6);
			
			if(!ctype_xdigit( $color)||$color=='FFF'||$color=='FFFFFF')  {$border='#CCC'; $text='#000'; $row->bgcolor='#FFF';}
			
            $popup='<strong>'.esc_html(strtoupper( $row->title) ).'</strong><br>';
            if(!empty( $row->event_image) )  {
                $popup .= wp_get_attachment_image( $row->event_image, 'medium', false);
            }
            if(!empty( $row->description) )$popup.=esc_html( $row->description).'<br>';
            if(!empty( $row->location) )$popup.=esc_html( $row->location).'<br>';
			if(!empty( $row->facilities_id)&&$row->facilities_id>0)
			{
				$fac=$wpdb->get_var('SELECT facility_name FROM '.$wpdb->prefix.'church_admin_facilities WHERE facilities_id="'.esc_sql( $row->facilities_id).'"');
				$popup.='('.$fac.')';
			}
			if( $row->start_time=='00:00:00' && $row->end_time=='23:59:00')
    		{//all day
    			$popup.=esc_html(__('All Day','church-admin' ) )."<br>".esc_html($row->category)." Event <br>";
    		}
    		else
    		{
				$popup.=esc_html(mysql2date(get_option('time_format'),$row->start_time)." - ".mysql2date(get_option('time_format'),$row->end_time))."<br>".esc_html($row->category)." Event <br>";
			}
			if( $row->recurring=='s')  {$type='single-event-edit'; $nonce='single-event-edit';}else{$type='future-series-edit'; $nonce='future-series-edit';}
			  if(!empty( $row->link) )
            {
                if(!empty( $row->link_title) )  {$title= $row->link_title;}else{$title= __('More information...','church-admin') ;}
                $popup.='<a href="'.esc_url( $row->link).'">'.esc_html($title).'</a>';
            }
            
            $popup.='<p><a  rel="nofollow" class="vcf-link" href="'.esc_url(site_url().'?ca_download=ical&date_id='.(int)$row->date_id).'"><span class="ca-dashicons dashicons dashicons-download"></span></a></p>';   
            if(church_admin_level_check('Calendar') ){
                $popup.='<a title="'.esc_attr(__('Edit Entry','church-admin' ) ).'" href="'.esc_url( wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action='.esc_attr( $type ).'&amp;event_id='.(int)$row->event_id.'&amp;date_id='.(int)$row->date_id,$nonce) ).'"><span class="ca-dashicons dashicons dashicons-edit"></span>'.esc_html( __('Edit','church-admin') ).'</a>';
            }
             		
            $day[$row->event_id]= '<div class="church_admin_cal_item" id="ca'.(int)$row->date_id.'" style="background-color:'.esc_attr($row->bgcolor).';border-left:3px solid '.$border.';padding:5px;color:'.esc_attr($row->text_color).'" >'.esc_html(mysql2date(get_option('time_format'),$row->start_time)).' '.esc_html( $row->title).'... <div id="div'.intval( $row->date_id).'" class="church_admin_tooltip"  style="background-color:'.esc_attr($row->bgcolor).';border-left:3px solid '.esc_html($border).';padding:5px;;color:'.esc_attr($row->text_color).'" >'.wp_kses_post($popup).'</div></div>';
        
        }
        //church_admin_debug($day);
        $out.=implode('',$day);
    }    
    $out.="</td>\n";
        
        if( $rowcounter % $numinrow == 0 )
        {   
            $out.="\t</tr>\n";
            if( $counter < $numdaysinmonth )
            {
                $out.="\t<tr>\n";
            }    
            $rowcounter = 0;
        }
}
// clean up
$numcellsleft = $numinrow - $rowcounter;
if( $numcellsleft != $numinrow )
{
    for( $counter = 0; $counter < $numcellsleft; $counter ++ )
    {
        $out.= "\t\t<td>-</td>\n";
        $emptycells ++;
    }
}

$out.='</tr>
</table>';
$out.="
<script type=\"text/javascript\">

jQuery(document).ready(function( $)  {
    $('.church_admin_cal_item').hover(
        function() {
            var hideNo=this.id.substr(2);
          $('#div'+hideNo).fadeIn(1000);
        }, function() {
            var hideNo=this.id.substr(2);
          $('#div'+hideNo).fadeOut(1000);
        }
      );

    

});</script>
";
return $out;
}