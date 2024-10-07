<?PHP
/**
     *
     * Automations callback
     *
     * @author  Andy Moyle
     * @param    null
     * @return
     * @version  0.1
     *
     */


   






function church_admin_calendar_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'display/calendar-list.php');
    echo'<h3>'.esc_html( __( 'Next 7 days', 'church-admin' ) ).'</h3>';
    echo church_admin_calendar_list(7,NULL);
}
function church_admin_events_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php');
    church_admin_events();
}





function church_admin_people_callback()
{
    global $wpdb,$member_types;
    church_admin_search_form();
    echo '<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=check-directory-issues','check-directory-issues').'">'.esc_html( __('Check for issues with the directory','church-admin' ) ).'</a></p>';
    if(!empty($_POST['country_iso'])){
        $countryISO=sanitize_text_field( stripslashes( $_POST['country_iso'] ) );
        update_option('church_admin_sms_iso',(int)$countryISO);
    }
    $countryISO = get_option('church_admin_sms_iso');
    if(empty($countryISO))
    {
        echo'<form action="" method="POST">';
        echo '<div class="church-admin-form-group"><label>'. esc_html('Please set your Country STD (telephone dialling code e.g. 1 for USA, 44 for UK)','church-admin' ) .'</label>';
        echo '<input type="number" name="country_iso" /><input type="submit" value="'.esc_html( __( 'Save', 'church-admin' ) ).'" /></div></form>';
    }
    $householdsCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_household');
    $recentPeople=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE last_updated > DATE(NOW() ) + INTERVAL -1 DAY ');
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=recent-activity&amp;section=people','recent-activity').'">'.esc_html( sprintf(__('%1$s people records edited in the last 7 days','church_admin'), $recentPeople ) ).'</a></p>';
    echo'<p>'.esc_html( sprintf(__('%1$s households stored in total','church-admin' ) ,$householdsCount) ).'</p>';
  
    $memberTypeCount=$wpdb->get_results('SELECT COUNT(member_type_id) AS count,member_type_id FROM '.$wpdb->prefix.'church_admin_people GROUP BY member_type_id');
    
    foreach( $memberTypeCount AS $mtCount)
    {
        if(!empty( $member_types[$mtCount->member_type_id] ) )echo '<p>'.esc_html(sprintf(__('%1$s people of "%2$s" member type','church-admin' ) ,(int)$mtCount->count,$member_types[$mtCount->member_type_id]) ).'</p>';
    }




}




function church_admin_settings_callback()
{
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=shortcode-generator','shortcode-generator').'">'.esc_html( __('Shortcode generator','church-admin' ) ).'</a></p>';

   echo'<h2>'.esc_html( __('Debugging','church-admin' ) ).'</h2>';
   $debug=get_option('church_admin_debug_mode');
   if(defined('CA_DEBUG') )  {
        if(!empty($debug))
        {
            echo '<p>'.esc_html( __( 'Church Admin debug mode is ON','church-admin' ) ).'</p>';
            echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=toggle-debug-mode','toggle-debug-mode').'">'.esc_html( __('Toggle debug mode','church-admin') ).'</a></p>';
  
        }
            
        elseif(!empty( $_COOKIE['ca_debug_mode'] ) ){
            echo '<p>'.esc_html( __( 'Church Admin debug mode is ON,set by cookie method','church-admin' ) ).'</p>';
            echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=toggle-debug-mode','toggle-debug-mode').'">'.esc_html( __('Toggle debug mode','church-admin') ).'</a></p>';
  
        }else{ 
            echo '<p>'.esc_html( __('Church Admin debug mode is ON, hard-coded probably in wp-config.php','church-admin' ) ) .'</p>';
        }
    }
    else{
        echo'<p>'.esc_html( __('Church Admin debug mode is OFF','church-admin') ).'</p>';
        echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=toggle-debug-mode','toggle-debug-mode').'">'.esc_html( __('Toggle debug mode','church-admin') ).'</a></p>';
  
    }
    $upload_dir = wp_upload_dir();
    $debug_path=$upload_dir['basedir'].'/church-admin-cache/debug_log.php';
    if(file_exists( $debug_path) )
    {
        $filesize=filesize( $debug_path);
        $size=size_format( $filesize, $decimals = 2 );
        echo'<p>'.esc_html( sprintf(__('Debug file is currently %1$s','church-admin' ) ,$size) ).'</p>';
        
	    
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=debug-log','debug-log').'" id="download-ca-debug" class="button-secondary">'.esc_html( __('Display debug file','church-admin') ).'</a></p>';
        echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=clear-debug','clear-debug').'">'.esc_html( __('Delete debug file','church-admin') ).'</a></p>';
    }
    echo'<p><a href="https://patchstack.com/database/vdp/church-admin"><img width="300" src="https://patchstack.com/wp-content/uploads/2022/12/patchstack_badge_program_372x72.svg" alt="Patchstack logo" /></a></p>';
   echo'<p>'.__('Cleantalk is the only spam plugin we have found that works and highly recommend it with this affiliate link').'<br/><a href="https://cleantalk.org/wordpress?pid=933495"><img width="150" height="53" alt="" src="https://cleantalk.org/images/icons/150px/Normal.png"></a></p>';
}



