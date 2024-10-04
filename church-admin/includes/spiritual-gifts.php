<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_spiritual_gifts_list()
{
    global $wpdb,$church_admin_spiritual_gifts;

    $countPeople = 0;
    $countPeople=$wpdb->get_var('SELECT COUNT(DISTINCT(people_id) ) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="spiritual-gifts" GROUP BY people_id');
    
    $totalPeople=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people');
    echo'<h3>'.esc_html( __('Spiritual gifts','church-admin' ) ).'</h3>';
    echo'<p>'.esc_html(sprintf(__('%1$s out of %2$s people have filled out the spiritual gifts questionnaire','church-admin'  ),$countPeople,$totalPeople )).'</p>';
    foreach( $church_admin_spiritual_gifts AS $giftID =>$gift)
    {
        $giftCount=$wpdb->get_var('SELECT COUNT(DISTINCT(people_id) ) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="spiritual-gifts" AND ID="'.(int)$giftID.'" GROUP BY people_id');
        echo'<p>'.esc_html(sprintf(__('%1$s  identified "%2$s"','church-admin' ) ,(int)$giftCount,$gift) ).'</p>';
    }

}