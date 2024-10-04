<?PHP

function church_admin_find_custom_id( $customField)
{
    global $wpdb;
    church_admin_debug('Looking for '.$customField);
    $ID=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE name LIKE "%'.esc_sql( $customField).'%"');
    
    return $ID;
}


function church_admin_display_custom_field( $deltadays=365,$showYears=1,$custom_id)
{
    $licence =get_option('church_admin_app_new_licence');
    if($licence!='standard' && $licence!='premium'){
        return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
        
    }
    global $wpdb;
    $custom_fields=church_admin_get_custom_fields();
    if ( empty( $custom_fields[$custom_id] ) ) return __("Custom field doesn't exist",'church-admin');
    if( $custom_fields[$custom_id]['type']!='date')return __("Only date custom fields can be displayed",'church-admin');
    $out='';
    
    $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_custom_fields_meta b WHERE a.people_id=b.people_id AND b.custom_id="'.(int)$custom_id.'" AND DATE_ADD(b.data, INTERVAL YEAR(CURDATE() )-YEAR(b.data)+ IF(DAYOFYEAR(CURDATE() ) > DAYOFYEAR(b.data),1,0)YEAR) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL '.(int)$deltadays.' DAY) ORDER BY MONTH(b.data) ASC,DAYOFYEAR(b.data) ASC';
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        $out .= '<p><strong>'.esc_html(sprintf( __('%1$s within the next %2$s  days','church-admin' ) , $custom_fields[$custom_id]['name'] , $deltadays) ).':</strong></p>';

		$out .= '<table class="table table-bordered table-striped widefat">';

		$out.='<thead><tr><th>'.esc_html( __('Name','church-admin' ) ).'</strong></th><th>'.esc_html( $custom_fields[$custom_id]['name'] ).'</th></tr></thead><tbody>';

		foreach( $results AS $people)

		{

	
			$name=church_admin_formatted_name( $people);
            if(( $showYears) )  {$format ='jS M Y';}else{$format ='jS M ';}
			$customFieldDay = mysql2date( $format,$people->data);
			$year = mysql2date("Y",$people->data);
			$currentyear = date("Y");
			$yearsPassed = $currentyear - $year;
			$out.='<tr><td class="ca-names">'.esc_html( $name).'</td><td>'.esc_html( $customFieldDay);
			if(!empty( $showYears) )$out.=', '.esc_html($yearsPassed).' '.esc_html( __("years","church-admin"));
			$out.='</td></tr>';

		}

		$out.='</tbody></table>';
		$out.="\r\n";

    }else{$out.='<p><strong>'.esc_html(sprintf( __('There are no "%1$s" within the next %2$s  days','church-admin'  ), $custom_fields[$custom_id]['name'] , $deltadays) ).':</strong></p>';}
    return $out;

}