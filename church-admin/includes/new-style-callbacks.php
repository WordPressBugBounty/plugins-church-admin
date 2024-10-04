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


function church_admin_pastoral_callback()
{
    echo'<h2>Pastoral Visitation</h2>';
    $premiumLicence = get_option('church_admin_app_new_licence');
    if ( empty( $premiumLicence)||$premiumLicence!='premium')
    {
        echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
        church_admin_app_purchase();
    return;
    }
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=pastoral-settings','pastoral-settings').'">'.esc_html('Pastoral visit settings','church-admin').'</a></p>';
    //echo church_admin_pastoral_overdue();
    church_admin_pastoral_latest_new_people();
    
}     
function church_admin_automations_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
    church_admin_automations_list();
}
function church_admin_keydates_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
    echo'<p>'.esc_html( __('Admin area shows all birthdays including people who have selected not to be shown publicly in the address list', 'church-admin' )).'</p>';
    echo church_admin_frontend_birthdays( null,null, 31,TRUE,TRUE,TRUE);
    echo church_admin_frontend_anniversaries( null,null, 31,TRUE,TRUE,TRUE);
}


function church_admin_contact_callback()
{
    
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/contact_form.php');
    church_admin_contact_form_list();
}

function church_admin_attendance_callback()
{
    global $wpdb;
    $service_id=$wpdb->get_var('SELECT service_id FROM '.$wpdb->prefix.'church_admin_attendance WHERE mtg_type="service" ORDER by `date` DESC LIMIT 1');
    $meet='s/'.(int)$service_id;
    require_once(plugin_dir_path(dirname(__FILE__) ).'display/graph.php');
    echo church_admin_graph('weekly',$meet,date("y-m-d",strtotime("-12 weeks") ),date("Y-m-d"),'100%','200px',TRUE);
}
function church_admin_child_protection_callback()
{
    echo'<h2>'.esc_html(__('Child Protection','church-admin') ) .'</h2>';
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-child-protection-incident','edit-child-protection-incident').'">'.esc_html(__('Add child protection incident','church-admin') ).'</a></p>'."\r\n";
}
function church_admin_app_callback()
{
    //show upgrade message if needed

    $premiumLicence = get_option('church_admin_app_new_licence');
    if ($premiumLicence!='premium')
    {
        church_admin_buy_app();
	}
	else
    {
        echo'<p><a href="'.admin_url().'edit.php?post_type=app-content" class="button-primary">'.__('App content','church-admin').'</a></p>';
        require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
        church_admin_app_logs();
        /*********
        * PUSH
        *********/
      
        ca_push_message();
    }
}

function church_admin_children_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
    church_admin_kidswork();

}


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

function church_admin_comms_callback()
{
    global $wpdb;
    /*********
    * Email
    *********/
    echo church_admin_mailersend_get_domains();
    if(!function_exists('mail')){
        $settings=get_option('church_admin_smtp_settings');
        if(empty($settings)){
            echo '<div class="notice notice-warning"><h2>'.esc_html('Your hosting company has disabled email on the server','church-admin').'</h2>';
            echo'<p>'.esc_html(__('Your hosting company has not enabled sending email from our website. Please set up SMTP settings.','church-admin')).'</p>';
            echo'<p><a target="_blank" href="https://www.churchadminplugin.com/tutorials/help-im-not-getting-emails-from-the-plugin/">'.esc_html(__('Tutorial','church-admin')).'</a></p>';
            echo'</div>';
        }
    }
    echo'<h3>'.esc_html( __('Transactional Email','church-admin') ).'</h3>';
    $email_method=get_option('church_admin_transactional_email_method');
    if(empty($email_method)){
        update_option('church_admin_transactional_email_method','native');
        $email_method = 'native';

    }
    switch( $email_method)
    {
        case 'mailersend':
            $em =__('Using Mailersend','church-admin');
        break;
        default:
        case 'native':
            $em=__('Native WordPress email functions','church-admin');
        break;
        case 'smtpserver':
            $em=__('Using SMTP settings','church-admin');
            //check for settings
            $settings=get_option('church_admin_smtp_settings');
            if(empty($settings) || empty($settings['host'])  || empty($settings['username'] || empty($settings['password'])) ){
                $em.='<br/><strong><span style="color:red">'.__('Some or all required SMTP settings are missing.','church-admin').'</span></strong>';
            }
        break;
        
    }
    echo '<p>'.wp_kses_post($em).'</p>';

    echo'<h3>'.esc_html( __('Bulk Email','church-admin') ).'</h3>';
    $bulk_email_method=get_option('church_admin_bulk_email_method');
    switch( $bulk_email_method)
    {
        case 'mailersend':             $em =__('Using Mailersend','church-admin'); break;
        default:
        case 'native':$em=__('Native WordPress email functions','church-admin');break;
        case 'smtp-server':$em=__('Using your SMTP settings','church-admin');break;
        
    }
     $smtp=get_option('church_admin_smtp_settings');
   
    echo '<p>'.esc_html($em).'</p>';
   
    if(!empty($smtp)){echo'<p>'.esc_html(__('SMTP settings entered (if you are using Mailsersend in the plugin, WordPress will use them for site emails) ','church-admin'));}
  

    
    if($email_method =='native' || $email_method =='smtpserver'){
        echo'<h3>'.esc_html( __('Email send speed','church-admin') ).'</h3>';
        $queue_method = get_option('church_admin_cron');
        switch($queue_method){
            case 'wp-cron':
                $quantity = get_option('church_admin_bulk_email');
                echo '<p>'.esc_html(sprintf(__('You are set to use WordPress cron, sending %1$s emails per hour','church-admin'),$quantity)).'</p>';
                $queue_quantity = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_email');
                echo '<p>'.esc_html(sprintf(__('You are have %1$s emails in the queue','church-admin'),$queue_quantity)).'</p>';
            break;
            case 'cron':
                $quantity = get_option('church_admin_bulk_email');
                echo '<p>'.esc_html(sprintf(__('You are set to use Server cron, sending %1$s emails per hour','church-admin'),$quantity)).'</p>';
                $queue_quantity = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_email');
                echo '<p>'.esc_html(sprintf(__('You are have %1$s emails in the queue','church-admin'),$queue_quantity)).'</p>';
            break;    
            default:
                echo'<p>'.esc_html(__('Email set to send immediately','church-admin')).'</p>';
            break;
        }
        

    }
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=email-settings','email-settings').'">'.esc_html( __('Setup Email','church-admin' ) ).'</a></p>';
   

    echo '<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=new-user-email-template','new-user-email-template').'">'.esc_html(__('New user email template','church-admin')).'</a></p>';
    /*********
    * SMS
    *********/
    echo'<h3>SMS</h3>';
    echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=sms-settings','sms-settings').'">'.esc_html( __('Setup SMS','church-admin') ).'</a></p>';
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sms.php');
    $sms_type=get_option('church_admin_sms_provider');
    if ( empty( $sms_type) )
    {
        echo'<p>'.esc_html( __("Bulk SMS is a great way of communicating with your whole church or targeting specific groupings. Most people check the ping on their phone quickly! ",'church-admin') ).'</p>';
        echo'<p><a href="https://console.twilio.com/us1/billing/nonprofit-benefits/sign-up">'.__('Twilio now offer a one off not for profit credit of $100, or £80 (other currencies available). Click to apply!','church-admin').'</a></p>';
        echo'<p><a target="_blank" href="https://www.twilio.com/referral/YjV7bl"><img src="'.esc_url( plugins_url('/images/twilio-logo.png',dirname(__FILE__) ) ).'" alt="Twilio" /></a></p>';

    }
     church_admin_sms_credits();
   
}

function church_admin_classes_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/classes.php');
    church_admin_classes();
}

function church_admin_facilities_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
    church_admin_facilities_list(NULL,1);
 

}
function church_admin_followup_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/funnel.php');
    church_admin_funnel_list();
}
function church_admin_gifts_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/spiritual-gifts.php');
    church_admin_spiritual_gifts_list();
}
function church_admin_giving_callback()
{
    global $wpdb;
    $premiumLicence = get_option('church_admin_app_new_licence');
    if ( empty( $premiumLicence)||$premiumLicence!='premium')
    {
        $buttonText=__('Upgrade to premium for PayPal/Stripe giving','church-admin');
    }
    else
    {
        $buttonText=__('Payment gateway settings','church-admin');
    }
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=payment-gateway-setup','payment-gateway-setup').'" class="button-primary">'.esc_html( $buttonText ).'</a></p>';

    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pledges.php');
    echo church_admin_pledge_totals(FALSE);
   
    $premium=get_option('church_admin_payment_gateway');
    if(!empty( $premium['gift_aid'] ) )
    {
        $lastGiftAid=get_option('church-admin-last-gift-aid');
        if(!empty( $lastGiftAid) )
        {
            echo'<p>'.esc_html( sprintf(__('Your last Gift Aid report download was to date %1$s','church-admin' ) ,mysql2date(get_option('date_format'),$lastGiftAid) ) ).'</p>';
        }
    }
    /***********************************************
     * 
     * Create giving graph for last 12 weeks
     * 
     * ********************************************/
    $results=$wpdb->get_results('SELECT b.fund, WEEK(a.donation_date) AS date, SUM(b.gross_amount) AS giving FROM '.$wpdb->prefix.'church_admin_giving a, '.$wpdb->prefix.'church_admin_giving_meta b WHERE a.giving_id=b.giving_id AND a.donation_date > DATE(NOW() - INTERVAL 84 DAY) GROUP BY b.fund, WEEK(a.donation_date)
    ORDER BY b.fund,WEEK(a.donation_date)');
   
    if(!empty( $results) )
    {
        $graphData=array();
        $funds=array();
        $columns=$rows='';
        foreach( $results as $row)
        {
            if(!in_array( $row->fund,$funds) )$funds[]=$row->fund;
            
            $graphData[$row->date][$row->fund]=$row->giving;
        }
      
        foreach( $funds AS $key=>$givingFund)
        {
            $columns.='data.addColumn("number", "'.esc_html( $givingFund).'");'."\r\n";
        }
        foreach( $graphData AS $date=>$givingData)
        {
            //put zero in empty funds for that week
            foreach( $funds AS $key=>$fundName)  {if ( empty( $givingData[$fundName] ) )$givingData[$fundName]=0.0;}
            $rows.='['.$date.','.implode(",",$givingData).'],';
        }
        echo '
        <script type="text/javascript">
          google.charts.load("current", {"packages":["line"]});
          google.charts.setOnLoadCallback(drawChart);
    
          function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn("number", "'.esc_html( __('Week','church-admin' ) ).'");'."\r\n";
          echo $columns;
          echo' data.addRows(['.esc_html( $rows ).'] )'."\r\n";
          echo 'var options = {
              title: "'.esc_html( __('Weekly Giving','church-admin' ) ).'",
              curveType: "function",
              legend: { position: "bottom"}
            };

            var chart = new google.charts.Line(document.getElementById("giving_chart") );

            chart.draw(data, google.charts.Line.convertOptions(options) );
          }
        </script><div id="giving_chart"></div>';
    }
    //end giving graph
}

function church_admin_people_callback()
{
    global $wpdb,$member_types;
    church_admin_search_form();
    echo '<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=check-directory-issues','church-admin').'">'.esc_html( __('Check for issues with the directory','church-admin' ) ).'</a></p>';
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
function church_admin_groups_callback()
{
    echo'<p><a href="'.site_url().'/?ca_download=smallgroup-signup" class="button-primary">'.esc_html( __('Smallgroup signup PDF sheet','church-admin' ) ).'</a></p>';
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
    church_admin_smallgroup_metrics();
}
function church_admin_media_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
    $sermonPageID=get_option('church-admin-sermon-page');
    if ( empty( $sermonPageID) )
    {
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=set-sermon-page','set-sermon-page').'">'.esc_html( __('Please set where your sermon page is, to activate share links.','church-admin' ) ).'</a></p>';
    }



    ca_podcast_list_files();
}
function church_admin_ministries_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/departments.php');
    church_admin_ministries_list();
}

function church_admin_rota_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
    church_admin_rota_list(NULL,'service');
}
function church_admin_units_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/units.php');
    church_admin_units_list();
}
function church_admin_inventory_callback()
{
   
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/inventory.php');
    echo church_admin_inventory_list();
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

function church_admin_services_callback()
{
    global $church_admin_sites;
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sites.php');
    church_admin_site_list();
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/services.php');
    echo church_admin_service_list();
}

function church_admin_kiosk_app_callback()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kiosk-app.php');
    echo wp_kses_post('<h2>'.__('Coming soon','church-admin').'</h2><p>'.__('A new app for Android tablets to allow quick registration by visitors at your services and event ticket check in.','church-admin').'</p>');
    church_admin_kiosk_qr_code();
}