<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly



function church_admin_display_unit( $unit_id)
{
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
    global $wpdb;
    $out='';
    $unitDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_units WHERE unit_id="'.(int)$unit_id.'"');
    $out.='<h2>'.esc_html( $unitDetails->name).'</h2>';
    $out.='<p>'.esc_html($unitDetails->description).'</p>';
    
    
    $subunits=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE unit_id="'.(int)$unit_id.'"');
    if(!empty( $subunits) )
    {
        foreach( $subunits As $subunit)
        {
            $out.='<h2>'.esc_html( $subunit->name).'</h2>';
            if(!empty( $subunit->description) )$out.='<p class="ca-subunit-description">'.esc_html($subunit->description).'</p>';
            $out.='<p>'.wp_kses_post(church_admin_get_people_meta_list('unit',$subunit->subunit_id)).'</p><hr/>';
        }
    }

    return $out;
}