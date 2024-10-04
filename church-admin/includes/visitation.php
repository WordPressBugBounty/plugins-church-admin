<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_pastoral_main()
{
    $settings = get_option('church_admin_pastoral_settings');
    echo '<h2>'.esc_html(__('Pastoral Visitation Module','church-admin') ).'<h2>';
    echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/using-the-pastoral-visit-module/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
    echo'<a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=pastoral-settings','pastoral-settings').'">'.esc_html('Settings','church-admin').'</a></p>';
    
    if(!empty($settings['days'])){
        echo'<h3>'.esc_html(sprintf(__('Latest church visitors from last %1$s days','church-admin'),(int)$settings['days'] ) ).'</h3>';
    }else{
        echo '<h2>'.esc_html(__('Latest church visitors to see','church-admin') ).'<h2>';
    }
    
    church_admin_pastoral_latest_new_people();
    echo '<h2>'.esc_html(__('Pastoral Visitation List','church-admin') ).'<h2>';
    church_admin_pastoral_visits_list(FALSE);
    echo '<h2>'.esc_html(__('Pastoral Visitation Calendar','church-admin') ).'<h2>';
    
    if(!empty($settings['cat_id'])){

        require_once( plugin_dir_path( __FILE__ ).'/calendar.php');
        church_admin_new_calendar( wp_date('Y-m-01'),null,$settings['cat_id']);
    }
}


function church_admin_pastoral_settings()
{
    global $wpdb,$ministries;
    /***************************************************************
     * option : church_admin_pastoral_settings array
     * $days new people list 
     * $frequency - how often to visit people in months
     * $ministry_id - which ministry does visiting
     * $email template - from name, from email, subject, message
     * $calendar_cat_id - calendar category id
     * $not_shown = show/not show on general calendar
     **************************************************************/
    
   



    echo'<h3>'.esc_html(__('Pastoral visitation module settings','church-admin')).'</h3>';
    echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/using-the-pastoral-visit-module/"><span class="dashicons dashicons-welcome-learn-more#settings"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
    $settings = get_option('church_admin_pastoral_settings');
    
    //process form
    if(!empty($_POST['save-settings'])){
        
        //sanitize and validate
        $days = (!empty($_POST['days']) && church_admin_int_check($_POST['days']) ) ? (int) $_POST['days']:7;
        $frequency = (!empty($_POST['frequency']) && church_admin_int_check($_POST['frequency']) ) ? (int) $_POST['frequency']:56;
        $ministry_id = (!empty($_POST['ministry_id']) && church_admin_int_check($_POST['ministry_id']) ) ? (int) $_POST['ministry_id']:null;
       
        if(empty($ministries[$ministry_id])){
            $ministry_id = null;
        }
        $calendar_cat_id = (!empty($_POST['calendar_cat_id']) && church_admin_int_check($_POST['calendar_cat_id']) ) ? (int) $_POST['calendar_cat_id']:null;
        $calendar_category = !empty($_POST['calendar_category']) ? church_admin_sanitize($_POST['calendar_category']):null;

        $from_name = !empty($_POST['from_name']) ? church_admin_sanitize($_POST['from_name']) :get_option('church_admin_default_from_name');
        $from_email = !empty($_POST['from_email']) ? church_admin_sanitize($_POST['from_email']) :get_option('church_admin_default_from_email');
        $visitor_subject = !empty($_POST['visitor_subject']) ? church_admin_sanitize($_POST['visitor_subject']) :__('Pastoral visit details','church-admin');
        $visitor_message = !empty($_POST['visitor_message']) ? wp_kses_post(stripslashes($_POST['visitor_message'])) :'<p>Pastoral visit details</p>,<p>Please visit [name], all their details are attached for you.</p>';
        $visited_subject = !empty($_POST['visited_subject']) ? church_admin_sanitize($_POST['visited_subject']) :__('Pastoral visit details','church-admin');
        $visited_message = !empty($_POST['visited_message']) ? wp_kses_post(stripslashes($_POST['visited_message'])) :'<p>Pastoral visit details</p>,<p>Please visit [name], all their details are attached for you.</p>';
        $calendar_cat_id = (!empty($_POST['cat_id']) && church_admin_int_check($_POST['cat_id']) ) ? (int) $_POST['cat_id']:null;
        $calendar_category = !empty($_POST['calendar_category']) ? church_admin_sanitize($_POST['calendar_category']):null;
        //handle new calendarcategory
        if(!empty($calendar_category)){
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_category (category)VALUES("'.esc_sql($calendar_category).'")');
            $calendar_cat_id = $wpdb->insert_id;
        }


        //handle entered new ministry
        $ministry =  !empty($_POST['ministry']) ? church_admin_sanitize($_POST['ministry']):null;
        $people =  !empty($_POST['people'])  ? church_admin_sanitize($_POST['people']):null;
        if(!empty($ministry)){
            $ministries=church_admin_ministries();
            if(!in_array( $ministry,$ministries) )
            {//add new one if unique
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry) VALUES("'.esc_sql( $ministry).'")');
                $ministry_=$wpdb->insert_id;
            }else{
                $ministry_id=array_search($ministry,$ministries);
            }
            if(!empty($people)){
                $people_ids=maybe_unserialize(church_admin_get_people_id($people));
                if(!empty( $people_ids) )
                {
                    foreach( $people_ids AS $key=>$people_id)
                    {
                        if(!empty( $people_id) )church_admin_update_people_meta( $ministry_id,$people_id,'ministry');
                    }
                }
            }
        }
        //handle entered calendar category
        

        //build settings array and save
        $settings = array(
            'days'=>$days,
            'frequency'=>$frequency,
            'ministry_id'=>$ministry_id,
            'cat_id'=>$calendar_cat_id,
            'visitor_email_template'=>array('from_name'=>$from_name,
                                    'from_email'=>$from_email,
                                    'subject'=>$visitor_subject,
                                    'message'=>$visitor_message
            ),
            'visited_email_template'=>array('from_name'=>$from_name,
                                        'from_email'=>$from_email,
                                        'subject'=>$visited_subject,
                                        'message'=>$visited_message
                )
        );
       
        update_option('church_admin_pastoral_settings',$settings);
        //display saved message
        echo'<div class="notice notice-success"><h2>'.esc_html(__('Pastoral visitation settings saved','church-admin')).'</h2></div>';
    }

    //if empty data, use default
    if(empty($settings)){
        $settings = array(
            'days'=>7,
            'frequency'=>56,
            'ministry_id'=>null,
            'cat_id'=>null,
            'visitor_email_template'=>array('from_name'=>get_option('blogname'),
                                    'from_email'=>get_option('church_admin_default_from_email'),
                                    'subject'=>__('Pastoral visit details'),
                                    'message'=>'<p>Pastoral visit details</p>,<p>Please visit [name], all their details are attached for you.</p>'
            ),

            'visitor_email_template'=>array('from_name'=>get_option('blogname'),
                                        'from_email'=>get_option('church_admin_default_from_email'),
                                        'subject'=>__('Pastoral visit details'),
                                        'message'=>'<p>Pastoral visit details</p>,<p>a visit has been arranged for you by [visitor] on [date] and [time].</p>'
                )
            );
    }

    //show form with $settings data
    echo'<form action="admin.php?page=church_admin/index.php&action=pastoral-settings" method="POST">';
    wp_nonce_field('pastoral-settings');
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Days of new registrations to show','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="number" name="days" value="'.(int)$settings['days'].'"></div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Frequency of pastoral visits (in days)','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="number" name="frequence" value="'.(int)$settings['frequency'].'"></div>';

    $categories=church_admin_calendar_categories_array();
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Pastoral visitation calendar category','church-admin')).'</label>';
    echo'<select id="cal_cat" name="cat_id"  class="church-admin-form-control"><option>'.esc_html(__('Choose...','church-admin')).'</option>';
    foreach( $categories AS $id=>$category){
        echo'<option value="'.(int)$id.'" '.selected($id,$settings['cat_id'],FALSE).'>'.esc_html($category).'</option>';
    }
    echo'<option value="0">'.__('Create calendar category','church-admin').'</option>';
    echo'</select></div>';
    $catshow='style="display:none"';
    echo'<div '.$catshow.' id="calendar_category"><div class="church-admin-form-group"><label>'.esc_html(__('Create calendar category','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" name="calendar_category"></div></div>';


    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Pastoral visitation ministry','church-admin')).'</label>';
    if(!empty($ministries))
    {
        echo '<select id="ministry_id" name="ministry_id" class="church-admin-form-control"><option>'.esc_html(__('Choose a ministry','church-admin')).'</option>';
        foreach($ministries AS $id=>$ministry){
            echo '<option value="'.(int)$id.'" '.selected($id,$settings['ministry_id']).'>'.esc_html($ministry).'</option>';
        }
        echo'<option value="0">'.__('Create visitation ministry','church-admin').'</option>';
        echo'</select>';
        $show='style="display:none"';
        echo'</div>';
    }
    else{$show = '';}
    echo'<div '.$show.' id="new_ministry"><div class="church-admin-form-group"><label>'.esc_html(__('Create visitation ministry','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" name="ministry"></div>';
    echo '<div class="church-admin-form-group"><label>'.esc_html('Add some people','church-admin').'</label>';
   echo church_admin_autocomplete('people','friends','to',NULL);
   echo'</div></div>';
    echo'<script>
        jQuery(document).ready(function($){
            $("#ministry_id").change(function(){
                var selected = $("option:selected", this).val();
                if (selected ==="0") {$("#new_ministry").show();}else{$("#new_ministry").hide();}
            });
            $("#cal_cat").change(function(){
                var selected = $("option:selected", this).val();
                if (selected ==="0") {$("#calendar_category").show();}else{$("#calendar_category").hide();}
            });
        });
    </script>';
    
    
    

    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Email from name','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="text" name="from_name" value="'.esc_html($settings['visitor_email_template']['from_name']).'"></div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Email from address','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="email" name="from_email" value="'.esc_html($settings['visitor_email_template']['from_email']).'"></div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Email subject','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="text" name="subject" value="'.esc_html($settings['visitor_email_template']['subject']).'"></div>';
    echo'<p>[date],[time],[person],[address],[phone],[mobile],[iCal]</p>';
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Pastoral Visitor Email message','church-admin')).'</label>';
    
    $visitor_template = !empty($settings['visitor_email_template']['message']) ? $settings['visitor_email_template']['message'] : __('<p>Pastoral visit details</p>,<p>A visit has been arranged for you to meet with [person] on [date] and [time].</p>
    <p>[iCal]</p>','church-admin');
    wp_editor($visitor_template,'visitor_message');
    echo'</div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Pastoral Visited Email subject','church-admin')).'</label>';
    $visited_subject = !empty($settings['visited_email_template']['subject']) ? $settings['visited_email_template']['subject'] : __('Pastoral visit appointment','church-admin');
    echo'<input class="church-admin-form-control" type="text" name="visited_subject" value="'.esc_html($visited_subject).'"></div>';
    echo'<p>[date],[time],[visitor]</p>';
    echo'<div class="church-admin-form-group"><label>'.esc_html(__('Pastoral Visited Email message','church-admin')).'</label>';
   
    $message = !empty($settings['visited_email_template']['message']) ? $settings['visited_email_template']['message'] : __('A pastoral visit has been arranged for you by [visitor] on [date] at [time].','church-admin');
    wp_editor($message,'visited_message');
    echo'</div>';
    echo'<p><input type="hidden" name="save-settings" value="yes"><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin' ) ).'"></form>';
}

function church_admin_pastoral_latest_new_people()
{
    $settings=get_option('church_admin_pastoral_settings');
    if(empty($settings)){
        echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=pastoral-settings','pastoral-settings').'">'.esc_html('Please adjust pastoral visitation settings first','church-admin').'</a></p>';
        return;
    }
    
    $days = !empty($settings['days']) ? $settings['days'] :7;

    global $wpdb;
    
    $results = $wpdb->get_results('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.head_of_household=1 AND a.household_id=b.household_id AND a.first_registered >= DATE(NOW()) - INTERVAL '.(int)$days.' DAY');
    if(empty($results)){
        echo'<p>'.esc_html(__('No new people','church-admin') ).'</p>';
        echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=add-household','add-household').'">'.esc_html( __('Add a household','church-admin') ).'</a></p>';
        return;
    }
    $count=$wpdb->num_rows;
    echo'<p>'.esc_html(sprintf(_n('%1$d person has registered within the last %2$d days','%1$d people have been registered within the last %2$d days',(int)$count,'church-admin'),(int)$count,(int)$days)).'</p>';

    $table_header='<tr><th>'.esc_html(__('Date','church-admin')).'</th><th>'.esc_html(__('Name(s)','church-admin')).'</th><th>'.esc_html(__('Address','church-admin')).'</th><th>'.esc_html(__(' Visitation PDF','church-admin')).'</th><th>'.esc_html('Add note','church-admin').'</th><th>'.esc_html('View notes','church-admin').'</th><th>'.esc_html(__('Schedule visit','church-admin')).'</th></tr>';


    echo'<table class="widefat striped bordered"><thead>'.$table_header.'</thead><tbody>';
    foreach($results AS $row)
    {
        $date = mysql2date(get_option('date_format'),$row->first_registered);
        $names = array();
        $names[] = church_admin_formatted_name($row);
        $extras = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$row->household_id.'" AND people_id!="'.(int)$row->people_id.'" ORDER BY people_order');
        if(!empty($extras))
        {
            foreach($extras AS $extra){$names[]=church_admin_formatted_name($extra);}

        }
        $address = !empty($row->address)?$row->address: '';
        $PDFlink='<a class="button-primary" target="_blank" href="'.esc_url(site_url().'?ca_download=visitation-pdf&people_id='.(int)$row->people_id).'">PDF</a>';
        $scheduleLink='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=schedule-pastoral-visit&people_id='.(int)$row->people_id,'schedule-pastoral-visit').'">'.__('Schedule visit','church-admin').'</a>';
        $addNote = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-pastoral-visit-note&people_id='.(int)$row->people_id,'edit-pastoral-visit').'">'.esc_html(__('Add visit notes','church-admin')).'</a>';
        $viewNotesCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited= "'.(int)$row->people_id.'"');
        if(empty($viewNotesCount)){
            $viewNotes=__('No notes yet','church-admin');
        }
        else
        {
            $viewNotes='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=view-pastoral-visits&people_id='.(int)$row->people_id,'view-pastoral-visits').'">'.esc_html(sprintf(_n('View %1$d note','View %1$d note',$viewNotesCount,'church-admin'),$viewNotesCount)).'</a>';
        }
        echo'<tr><td>'.esc_html( $date ).'</td><td>'.wp_kses_post( implode("<br>",$names) ).'</td><td>'.esc_html($address).'</td><td>'.$PDFlink.'</td><td>'.$addNote.'</td><td>'.$viewNotes.'</td><td>'.$scheduleLink.'</td></tr>';
    }
    echo'</tbody></table>';

}

function church_admin_edit_pastoral_note($people_id,$visit_id){
    
    
    global $wpdb;
    $settings = get_option('church_admin_pastoral_settings');

    //sanitize and validate initial function variable
    if(empty($people_id) || !church_admin_int_check($people_id))
    {
        echo'<div class="notice notice-warning"><h2>'.esc_html( __('Nobody specified','church-admin') ).'</h2></div>';
        return;
    }
    $person= $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    if(empty($person) )
    {
        echo'<div class="notice notice-warning"><h2>'.esc_html( __('Person not found','church-admin') ).'</h2></div>';
        return;
    }
    if(!empty($visit_id))
    {
        $visit = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visit_id="'.(int)$visit_id.'"');
        //church_admin_debug($visit);
        if(empty($visit) )
        {
            return __('Visit not found','church-admin');
        }
    }

    if(!empty($_POST['save']))
    {


        //sanitize
        $notes = !empty($_POST['notes']) ? church_admin_sanitize($_POST['notes']):null;
        $visit_date = !empty($_POST['visit_date']) ? church_admin_sanitize($_POST['visit_date']) : null;
        $visit_time =!empty($_POST['visit_time']) ? church_admin_sanitize($_POST['visit_time']) : null;
        $visitor = !empty($_POST['visitor']) ? church_admin_sanitize($_POST['visitor']):null;

        //validate
        $errors = array();
        if(empty($notes)){
            $errors[] = __('No notes', 'church-admin' ); 
        }
        if(empty($visitor)){
            $errors[] = __('No visitor specified', 'church-admin' ); 
        }
        if(empty($visit_date)){
            $errors[] = __('No visit date specified', 'church-admin' ); 
        }
        if(!church_admin_checkdate($visit_date)){
            $errors[] = __('Invalid visit date format', 'church-admin' ); 
        }
        if(empty($visit_time)){
            $errors[] = __('No visit time specified', 'church-admin' ); 
        }
        if(!empty($visit_time)){
            $hour_mins = explode(":",$visit_time);
          
            if(empty($hour_mins) ||!is_array($hour_mins)){
                $errors[] = __('Missing visit time', 'church-admin' );
            }
            if( !church_admin_int_check($hour_mins[0]) || !church_admin_int_check($hour_mins[1]) ){
                $errors[] = __('Non number time entered', 'church-admin' );
            }
            if($hour_mins[0]>23 ||$hour_mins[1]>59){
                $errors[] = __('Visit hours should be  between 0 and 23, and minutes 0 to 59 format', 'church-admin' );
            }
        }

        //show errors
        if(!empty($errors)){
            echo'<div class="notice notice-danger"><h2>'.esc_html(__('There were some errors','church-admin' ) ).'</h2><p style="color:red">'.wp_kses_post(implode('<br>',$errors)).'</p>';
        }
        else
        {
            $visit_datetime = $visit_date.' '.$visit_time;
            //safe to save data
            if(empty($visit_id)){
                $visit_id=$wpdb->get_var('SELECT visit_id FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited="'.(int)$people_id.'" AND visitor="'.(int)$visitor.'" AND visit_date="'.esc_sql($visit_datetime).'" AND notes="'.esc_sql($notes).'"');
            }
            if(empty($visit_id))
            {
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_pastoral_visits (visited,visitor,visit_date,notes) VALUES ("'.(int)$people_id.'","'.(int)$visitor.'","'.esc_sql($visit_datetime).'","'.esc_sql($notes).'")');
            }
            else{
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_pastoral_visits SET visited="'.(int)$people_id.'", visitor="'.(int)$visitor.'", visit_date="'.esc_sql($visit_datetime).'", notes = "'.esc_sql($notes).'" WHERE visit_id = "'.(int)$visit_id.'"');
            }
            echo '<div class="notice notice-success"><h2>'.esc_html( __('Notes saved' , 'church-admin' ) ).'</h2></div>';
            church_admin_view_pastoral_notes($people_id);
        }
        //end of process form data
    } else {

        //output form
        echo '<h2>'.esc_html( sprintf( __('Visit record to  %1$s','church-admin') , church_admin_formatted_name( $person ) ) ).'</h2>';
        echo'<form action="" method="post">';
        wp_nonce_field('edit-pastoral-visit-note');
        //who
        echo'<div class="church-admin-form-group"><label>'.__('Visitor name','church-admin').'</label>';
        
        $people = church_admin_get_people_meta_array('ministry',$settings['ministry_id']);
        //church_admin_debug($people);
        if(!empty($people)){
            echo'<select name="visitor" class="church-admin-form-control" ><option>'.__('Choose visitor','church-admin').'</option>';
            foreach($people AS $visitor_id=>$name){
                echo'<option  value="'.(int)$visitor_id.'" ';
                if(!empty($visit->visitor)) {
                    
                    selected($visitor_id,$visit->visitor, TRUE);
                }
                echo '>';
                echo esc_attr($name);
                echo'</option>';
            }
            echo'</select></div>';
        }

        //date & time
        echo'<div class="church-admin-form_group"><label>'.esc_html(__('Visit date and time','church-admin' ) ).'</label>';
        $date = !empty($visit->visit_date) ? mysql2date('Y-m-d',$visit->visit_date) : null;
        echo church_admin_date_picker( $date,'visit_date',FALSE,'2023-01-01',wp_date('Y-m-d'),'visit-date','visit-date',FALSE,NULL,NULL,NULL);
        $time = !empty($visit->visit_date) ? mysql2date('H:i',$visit->visit_date) :'';
        echo'<input type="time" name="visit_time" value="'.esc_attr($time).'"></div>';
        //note
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('Notes','church-admin')).'</label>';
        echo '<textarea name="notes" class="church-admin-form-control">';
        if(!empty($visit->notes)){echo wp_kses_post($visit->notes);}
        echo '</textarea></div>';
        //submit
        echo'<p><input type="hidden" name="save" value="yes"><input class="button-primary" type="submit" value="'.esc_html(__('Save','church-admin')).'"></p>';
        echo'</form>';
    }
    
}

function church_admin_delete_pastoral_note($people_id,$visit_id){
    echo'Coming soon';
}


function church_admin_view_pastoral_notes($people_id){
    global $wpdb;

    //check $people_id
    if(empty($people_id) || !church_admin_int_check($people_id))
    {
        return __('Nobody specified','church-admin');
    }
    $person= $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    if(empty($person) )
    {
        return __('Person not found','church-admin');
    }
    $notes = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited="'.(int)$people_id.'" ORDER BY visit_date DESC');
    $count = $wpdb->num_rows;

    
    //safe to proceed
    echo'<h2>'.esc_html(sprintf(_n('Pastoral visit note for %1$s','Pastoral visit notes for %1$s',$count,'church-admin'),church_admin_formatted_name($person))).'</h2>';
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-pastoral-visit-note&people_id='.(int)$people_id,'edit-pastoral-visit-note').'">'.esc_html(__('Add visit notes','church-admin')).'</a></p>';

    if(empty($notes)){
        return __('No notes yet','church-admin');
    }
    

    //create table
    $tableheader='<tr><th class="column-primary">'.esc_html(__('Date','church-admin')).'</th><th>'.esc_html(__('Edit','church-admin')).'</th><th>'.esc_html(__('Delete','church-admin')).'</th><th>'.esc_html(__('Visited by','church-admin')).'</th><th>'.esc_html(__('Notes','church-admin')).'</th></tr>';

    echo'<table class="widefat wp-list-table striped border"><thead>'.$tableheader.'</thead><tbody>'."\r\n";
    foreach($notes AS $note){
        $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-pastoral-visit-note&people_id='.$note->visited.'&amp;note_id='.(int)$note->visit_id,'edit-pastoral-visit-note').'">'.esc_html(__('Edit','church-admin')).'</a>';
        $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=delete-pastoral-visit-note&people_id='.$note->visited.'&amp;note_id='.(int)$note->visit_id,'delete-pastoral-visit-note').'">'.esc_html(__('Delete','church-admin')).'</a>';
        $date = mysql2date(get_option('date_format').' '.get_option('time_format'),$note->visit_date);
        $visitor_row=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$note->visitor.'"');
        $visitor = !empty($visitor_row) ? church_admin_formatted_name($visitor_row): '';
        $notes = $note->notes;

        echo '<tr><td data-colname="'.esc_html( __('Date','church-admin' ) ).'" class="column-primary">'.esc_html($date).'<button type="button" class="toggle-row"><span class="screen-reader-text">'.esc_html( __('Show details','church-admin' ) ).'</span></button></td>';
        echo '<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>';
        echo '<td data-colname="'.esc_html( __('Deleted','church-admin' ) ).'">'.$delete.'</td>';
        echo '<td data-colname="'.esc_html( __('Visitor','church-admin' ) ).'">'.esc_html($visitor).'</td>';
        echo '<td data-colname="'.esc_html( __('Notes','church-admin' ) ).'">'.wp_kses_post($notes).'</td>';
        echo '</tr>'."\r\n";
    }
    echo'</tbody></table>'."\r\n";

}

function church_admin_visitation_pdf($people_id,$save)
{
    
    //initialise variables
    global $wpdb;

    //check people_id
    if(empty($people_id) || !church_admin_int_check($people_id))
    {
        return __('Nobody specified','church-admin');
    }
    $person = $wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id = b.household_id AND a.people_id = "'.(int)$people_id.'"');

    if(empty($person))
    {
        return __('Person not found','church-admin');
    }

    //safe to proceed
    
    //get details of others in household
    $others=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$person->household_id.'" AND people_id!="'.(int)$person->people_id.'" ORDER BY people_order');
	
    $people_types=get_option('church_admin_people_type');
    $people_by_type = array();
    
    foreach($others AS $other){
        $people_by_type[$other->people_type_id][]=church_admin_formatted_name($other);

    }
    //church_admin_debug($people_by_type);

    //google map magic
    $api_key=get_option('church_admin_google_api_key');
    if(!empty( $api_key)&&!empty( $person->lng ) && !empty( $person->lat ))
	{
        $api='key='.$api_key;
        $url='https://maps.google.com/maps/api/staticmap?'.esc_attr($api).'&center='.esc_attr($person->lat).','.esc_attr($person->lng).'&zoom=15&markers=color:red%7C'.esc_attr($person->lat).','.esc_attr($person->lng).'&size=600x400';
        $response = wp_remote_get($url);
        $map_image = !empty($response) ? $response['body'] : null;

        $upload_dir = wp_upload_dir();
		$image_path =$upload_dir['basedir'].'/church-admin-cache/map.png';
        $fp = fopen($image_path, 'w');
        fwrite( $fp, $map_image);
        fclose($fp);
        
    }
    //initialise PDF
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
    $pdf=new FPDF();
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    
    
    $pdf->AddPage('P',get_option('church_admin_pdf_size') );
    $pdf->SetFont('DejaVu','B',18);
    $pdf->SetTextColor(0,0,0);
 
    //Title
    $title = __('Visitation details','church-admin');
    $pdf->SetFont('DejaVu','B',18);
    $pdf->Cell(0,15,$title,0,1,'C');

    //person name
    $name = church_admin_formatted_name($person);
    
    
    $pdf->SetFont('DejaVu','B',16);
    $pdf->Cell(0,8,$name,0,1,'C');

    //household
    if(!empty($people_by_type)){
        foreach($people_by_type AS $p_id=>$people){

            $pdf->SetFont('DejaVu','B',10);
            $pdf->Cell(40,8,sprintf(__('Other %1$s','church-admin'),$people_types[$p_id]).':',0,0,'L');
            $pdf->SetFont('DejaVu','',10); 
            $pdf->Cell(0,8,implode(', ',$people),0,1,'L');
        }
    }

   


    //more information
    $dob =!empty($person->date_of_birth) ? mysql2date( get_option( 'date_format' ),$person->date_of_birth) : 'null';
    $phone = !empty($person->phone) ? $person->phone: null;
    $mobile = !empty($person->mobile) ? $person->mobile: null;
    $email = !empty($person->email) ? $person->email: null;

    $pdf->SetFont('DejaVu','',10);
  
    if( !empty( $dob ) ) {
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(40,8,__('Date of birth','church-admin').':',0,0,'L');
        $pdf->SetFont('DejaVu','',10); 
        $pdf->Cell(0,8,$dob,0,1,'L'); 
    }

    if( !empty( $phone ) ) {
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(40,8,__('Phone','church-admin').':',0,0,'L');
        $pdf->SetFont('DejaVu','',10);
        $pdf->Cell(0,8,$phone,0,1,'L'); 
    }
    
    if( !empty( $cell ) ) {
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(40,8,__('Cell','church-admin').':',0,0,'L');
        $pdf->SetFont('DejaVu','',10);
        $pdf->Cell(0,8,$cell,0,1,'L');   
    }

    if( !empty( $email ) ) {
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(40,8,__('Email','church-admin').' :',0,0,'L');
        $pdf->SetFont('DejaVu','',10);
        $pdf->Cell(0,8,$email,0,1,'L'); 
    }
    
    //added to system details
    $added =!empty($person->first_registered) ? mysql2date( get_option( 'date_format' ),$person->first_registered) : null;
    $pdf->SetFont('DejaVu','',10);
    if( !empty( $added ) ) {
    
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(40,8,__('Added to system ','church-admin').' :',0,0,'L');
        $pdf->SetFont('DejaVu','',10);
        $pdf->Cell(0,8,$added,0,1,'L'); 
    }

    //significant others

    //address details
    $address = !empty($person->address) ?$person->address: null;
    if( !empty( $address ) ) {
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(40,8,__('Address ','church-admin').':',0,0,'L');
        $pdf->SetFont('DejaVu','',10);
        $pdf->Cell(0,8,$address,0,1,'L'); 
    
    }
    //map
    if(!empty($image_path)){
        $pdf->Image($image_path,null,null,null);
    }
    //previous visit record
     //previous visits
     $visits = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited = "'.(int)$people_id.'" ORDER BY visit_date DESC');
     if(!empty($visits)){
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(0,8,__('Previous visits','church-admin'),0,1,'L');
         foreach($visits AS $visit){
            $datetime = mysql2date(get_option('date_format').' '.get_option('time_format'),$visit->visit_date);
            $visitor_details = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id = "'.(int)$visit->visitor.'"');
            $visitor = !empty($visitor_details) ? church_admin_formatted_name($visitor_details):'';

            $pdf->SetFont('DejaVu','',10);
            $pdf->Cell(0,8,$datetime.' '.$visitor,0,1,'L');          
         }
     }

    if(empty($save)) {
        $pdf->Output();
    }else{

        $filename = md5(church_admin_formatted_name($person)).'.pdf';
        $upload_dir = wp_upload_dir();
		$file_path=$upload_dir['basedir'].'/church-admin-cache/'.$filename;
        $pdf->Output('F', $file_path);
        return $file_path;
    }
    exit();


}

function church_admin_pastoral_visits_list($edit=FALSE)
{
    global $wpdb;
    $settings = get_option('church_admin_pastoral_settings');
    
    if(!empty($edit)){
        church_admin_pastoral_edit();
    }
    echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=pastoral-visit-list','pastoral-visit-list').'">'.esc_html(__('Edit visitation list','church-admin')).'</a></p>';
    $on_visit_list=church_admin_on_visit_list_array();
    if(!empty($on_visit_list))
    {
        
        echo'<table class="widefat wp-list-table striped">';
        $theader='<tr><th>'.esc_html(__('Name','church-admin') ).'</th><th>'.esc_html(__('Remove','church-admin') ).'</th><th>'.esc_html(__('Add note','church-admin')).'</th><th>'.esc_html(__('Schedule','church-admin') ).'</th><th>'.esc_html(__('Last visit','church-admin') ).'</th><th>'.esc_html(__('View visit notes','church-admin') ).'</th></tr>';
        echo'<thead>'.$theader.'</thead><tbody>';
        foreach($on_visit_list AS $name => $details)
        {
            
            $remove_person='<a class="remove-person" data-people_id="'.(int)$details['people_id'].'">'.__('Remove from list','church-admin').'</a>';
            $last_visit_date = !empty($details['last_visit']) ? mysql2date(get_option(date_format),$details['last_visit']) : '';
            
            $check_scheduled_date=$wpdb->get_var('SELECT a.start_date FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_meta b WHERE a.event_id=b.event_id AND b.meta_type="pastoral-visit-scheduled" AND b.meta_value="'.(int)$details['people_id'].'" AND a.start_date>=NOW()');

            if( $check_scheduled_date){
                $button = sprintf(__('Scheduled %1$s','church-admin'), mysql2date(get_option('date_format'),$check_scheduled_date));
            }
            else{
                $button = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=schedule-pastoral-visit&people_id='.(int)$details['people_id'],'schedule-pastoral-visit').'">'.__('Schedule visit','church-admin').'</a>';
            }


            $last_visit_date = $wpdb->get_var('SELECT visit_date FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited = "'.(int)$details['people_id'].'"');
            if(empty($last_visit_date) && empty( $check_scheduled_date)){
                $last_visit ='<strong>'. __('Never visited','church-admin');
            }
            elseif(!empty($last_visit_date) && strtotime($last_visit_date) < strtotime($settings['frequency'] .' days ago')){
                $last_visit = '<strong>'.sprintf(__('Overdue, last visit %1$s','church-admin'),mysql2date(get_option('date_format'),$last_visit_date)).'</strong>';
            }
            else{
                $last_visit = mysql2date(get_option('date_format'),$last_visit_date);
            }

            $addNote = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-pastoral-visit-note&people_id='.(int)$details['people_id'],'edit-pastoral-visit-note').'">'.esc_html(__('Add visit notes','church-admin')).'</a>';

            $noOfnotes = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited="'.(int)$details['people_id'].'"');
            if(!empty($noOfnotes)){
                $visit_notes ='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=view-pastoral-visits&people_id='.(int)$details['people_id'],'view-pastoral-visits').'">'.esc_html(__('View visit notes','church-admin')).'</a>';
            }
            else
            {
                $visit_notes = '&nbsp;';
            }


            
            echo '<tr id="person'.(int)$details['people_id'].'"><td>'. esc_html($name). '</td><td>'.$remove_person.'</td><td>'.$addNote.'</td><td>' . $button .'</td><td>'. wp_kses_post($last_visit).'</td><td>'.wp_kses_post($visit_notes).'</td></tr>';

        }
        echo'</tbody></table>';
        echo'<script>
        jQuery(document).ready(function($){
            $(".remove-person").click(function(e){
                e.preventDefault();
                console.log("Remove person clicked");
                var people_id=$(this).data("people_id");
                var args={"action":"church_admin","method":"pastoral-list-remove","people_id":people_id,"nonce":"'.wp_create_nonce('pastoral-list-remove').'"};
                console.log(args);
                $.getJSON({
                    url: ajaxurl,
                    type: "post",
                    data:  args,
                    success: function(response) {
                        console.log(response);
                        $("#person"+response).hide();
                    }
                });

            });

        })</script>';

    }else{
        echo'<p>'.esc_html('No one on the list yet','church-admin').'</p>';
    }

}




function church_admin_pastoral_edit(){
    global $wpdb;
    $on_visit_list=church_admin_on_visit_list_array();
    if(!empty($_POST['save'])&&wp_verify_nonce('add-people','add-people')){
  
        $people_ids=array();
        if(!empty($_POST['manual-add'])){
            $people_ids=maybe_unserialize(church_admin_get_people_id(church_admin_sanitize($_POST['manual-add'] )));
        }
        if(!empty($_POST['people_type_ids'])){
            foreach ($_POST['people_type_ids'] AS $key=>$people_type_id)
            {
                $people = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_type_id="'.(int)$people_type_id.'"');
                if(!empty($people)){
                    foreach($people AS $person){
                        if(!in_array($person->people_id,$people_ids)){
                            $people_ids[]=(int)$person->people_id;
                        }
    
                    }
                }
            }
        }
        if(!empty($_POST['member_type_ids'])){
            foreach ($_POST['member_type_ids'] AS $key=>$member_type_id)
            {
                $people = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.(int)$member_type_id.'"');
                if(!empty($people)){
                    foreach($people AS $person){
                        if(!in_array($person->people_id,$people_ids)){
                            $people_ids[]=(int)$person->people_id;
                        }
    
                    }
                }
            }
        }
 
        if(!empty($people_ids)){

            foreach($people_ids AS $key=>$people_id){
                if(empty($on_visit_list[$people_id])){
                    
                    $last_visit = null;
                    $last_visit = $wpdb->get_var('SELECT visit_date FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited="'.(int)$people_id.'" ORDER BY visit_date DESC LIMIT 1');
                    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
                    
                    if(!empty($person)){
                        //person exists
                        $name=church_admin_formatted_name($person);
                        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,meta_type,ID) VALUES("'.(int)$people_id.'","pastoral-visit-required",1)');
                        $on_visit_list[$name] = array('people_id'=>$people_id,'last_visit'=>$last_visit);
                       
                    }
                }
            }

        }
        asort($on_visit_list);
       
    }
    echo'<h2>'.esc_html(__('Add people to visitation list','church-admin')).'</h2>';
    $member_types = church_admin_member_types_array();
    $people_types = get_option('church_admin_people_type');
    echo'<form action="" method="post">';
    wp_nonce_field('add-people','add-people');
    echo '<div class="church-admin-form-group"><label>'.esc_html(__('Manually add names','church-admin')).'</label>';
    echo church_admin_autocomplete('manual-add','people','people-to-add',NULL);
    echo'</div>';
    //
    echo '<div class="church-admin-form-group"><label>'.esc_html(__('Add all people with specific people type','church-admin')).'</label>';
  
    foreach( $people_types AS $id => $type ) {echo '<div class="church-admin_checkbox"><input type="checkbox" name="people_type_ids[]"  value="'.(int)$id.'">'.esc_html( $type ).'</div>'."\r\n";}
    echo '</select></div>'."\r\n";
    //
    echo '<div class="church-admin-form-group"><label>'.esc_html(__('Add all people with specific member type','church-admin')).'</label></div>';
   
    foreach( $member_types AS $id => $type ) {echo '<div class="church-admin_checkbox"><input type="checkbox" name="member_type_ids[]"  value="'.(int)$id.'">'.esc_html( $type ).'</div>'."\r\n";}
   
    echo '<p><input type="hidden" name="save" value="yes"><input class="button-primary" type="submit" value="'.esc_html(__('Add','church-admin') ).'"></p>';
    echo'</form>';
}




function church_admin_schedule_pastoral_visit($people_id){


    global $wpdb;

    $settings = get_option('church_admin_pastoral_settings');
    if(empty($settings)){
        echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=pastoral-settings','pastoral-settings').'">'.esc_html('Please adjust pastoral visitation settings first','church-admin').'</a></p>';
        return;
    }
    //team
    $results=$wpdb->get_results('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '. $wpdb->prefix.'church_admin_people_meta'.' b WHERE a.people_id = b.people_id AND b.meta_type="ministry" AND b.ID="'.(int)$settings['ministry_id'].'"');
    if(empty($results))
    {
        echo '<div class="notice notice-warning"><h2>'.esc_html(__('Please add some visitors to your visitation ministry.','church-admin') ).'</h2>';
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-ministry&amp;section=ministries&amp;id='.$settings['ministry_id'],'edit-ministry').'">'.esc_html( __('Edit visitation ministry','church-admin' ) ).'</a></p></div>';
        return;
    }
    else
    {
        $team = array();
        foreach($results AS $row){
            $team[$row->people_id] = church_admin_formatted_name($row);
        }
     
    }



    if(!empty($people_id) && church_admin_int_check($people_id)){
        $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
        echo'<h2>'.esc_html(sprintf(__('Schedule pastoral visit for %1$s','church-admin'),church_admin_formatted_name($person))).'</h2>';
    }
    
    
    if(empty($person)){
        echo'<h2>'.esc_html(__('Schedule pastoral visit','church-admin')).'</h2>';
        $on_visit_list=church_admin_on_visit_list_array();

        echo'<form action="admin.php" method="GET">';
        wp_nonce_field('schedule-pastoral-visit');
        echo '<input type="hidden" name="page" value="church_admin/index.php">';
        echo '<input type="hidden" name="action" value="schedule-pastoral-visit">';
        echo'<div class="church-admin-form-group"><select class="church-admin-form_control" name="people_id"><option>'.esc_html(__('Choose someone','church-admin')).'</option>';
        foreach($on_visit_list AS $name=>$details){
            echo '<option value="'.(int)$details['people_id'].'">'.esc_html($name).'</option>';
        }
        echo'</select></div>';
        echo'<p><input type="submit" value="'.esc_html(__('Choose','church-admin')).'"></p></form>';
        return;
    }
    
    //echo church_admin_this_week($cat_id);

    if(!empty($_POST['save'])){
       church_admin_debug(print_r($_POST,true));
        //sanitize
        $start_date = !empty($_POST['visit_date'])?church_admin_sanitize($_POST['visit_date']):null;
        $start_time = !empty($_POST['visit_time'])?church_admin_sanitize($_POST['visit_time']):null;
        $end_time = !empty($_POST['end_time'])?church_admin_sanitize($_POST['end_time']):null;
        $team_member = !empty($_POST['visitor'])?church_admin_sanitize($_POST['visitor']):null;
        $email_visited = !empty($_POST['email-visited'])?1:0;
        $email_visitor = !empty($_POST['email-visitor'])?1:0;

        //validate
        $errors=array();
        if(empty($start_date) ||!church_admin_checkdate($start_date)){
            $errors[]=__('Invalid visit date','church-admin');
        }
        if(!empty($start_time)){
            $hour_mins = explode(":",$start_time);
          
            if(empty($hour_mins) ||!is_array($hour_mins)){
                $errors[] = __('Missing visit start time', 'church-admin' );
            }
            if( !church_admin_int_check($hour_mins[0]) || !church_admin_int_check($hour_mins[1]) ){
                $errors[] = __('Non number start time entered', 'church-admin' );
            }
            if($hour_mins[0]>23 ||$hour_mins[1]>59){
                $errors[] = __('Visit start time should be  between 0 and 23, and minutes 0 to 59 format', 'church-admin' );
            }
        }
        if(!empty($end_time)){
            $hour_mins = explode(":",$end_time);
          
            if(empty($hour_mins) ||!is_array($hour_mins)){
                $errors[] = __('Missing visit end time', 'church-admin' );
            }
            if( !church_admin_int_check($hour_mins[0]) || !church_admin_int_check($hour_mins[1]) ){
                $errors[] = __('Non number end time entered', 'church-admin' );
            }
            if($hour_mins[0]>23 ||$hour_mins[1]>59){
                $errors[] = __('Visit end time should be  between 0 and 23, and minutes 0 to 59 format', 'church-admin' );
            }
        }
        $startTS = strtotime($start_date.' '.$start_time);
        $endTS = strtotime($start_date.' '.$end_time);
        if($startTS>$endTS){
            $errors[]=__('The end time is before the start time','church-admin');
        }
        if(empty($team_member) || empty($team[$team_member])){
            $errors[] = __('No recognisable visitation team member','church-admin');
        }

        if(!empty($errors)){
            echo'<div class="notice notice-danger"><h2>'.esc_html('There were some errors','church-admin').'</h2>';
            echo '<p>'.implode('<br>',$errors).'</p>';
            echo'<p><strong>'.__('Please press back and try again','church-admin').'</p>';
            echo'</div>';
        }

       
        $title = sprintf(__('Pastoral visit - %1$s','church-admin'),church_admin_formatted_name($person));
        $check = $wpdb->get_var('SELECT date_id FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE title="'.esc_sql($title).'" AND cat_id="'.(int)$settings['cat_id'].'" AND start_date="'.esc_sql($start_date).'" AND start_time="'.esc_sql($start_time).'" AND end_time="'.esc_sql($end_time).'" AND recurring="s"');
        if(empty($check)){
            //insert
            $curr_event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date');
            $event_id =!empty($curr_event_id) ? (int)$curr_event_id+1 : 1;
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,start_date,start_time,end_time,recurring,cat_id,event_id,general_calendar) VALUES("'.esc_sql($title).'","'.esc_sql($start_date).'","'.esc_sql($start_time).'","'.esc_sql($end_time).'","s","'.(int)$settings['cat_id'].'","'.(int)$event_id.'",0)');
            $date_id=$wpdb->insert_id;

            //add calendar meta
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_meta (event_id,meta_type,meta_value) VALUES ("'.(int)$event_id.'","pastoral-visit-scheduled","'.(int)$people_id.'"),("'.(int)$event_id.'","pastoral-team-member","'.(int)$team_member.'")');

        }
        $full_person_details=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.people_id="'.(int)$people_id.'"');
        $address = !empty($full_person_details->address) ? $full_person_details->address :'';
        $phone = !empty($full_person_details->phone) ? $full_person_details->phone :'';
        $mobile = !empty($full_person_details->mobile) ? $full_person_details->mobile :'';
        $name = church_admin_formatted_name($full_person_details);
        $ical = '<a href="'.esc_url(site_url().'/ca_download=ical&date_id='.(int)$date_id).'">'.esc_html(__('Download iCal appointment to your diary app','church-admin')).'</a>';
        $team_member_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$team_member.'"');
        if(!empty($_POST['email-visitor'])){
            church_admin_debug('Email team member');
            $message = $settings['visitor_email_template']['message'];
            $message = str_replace('[date]',mysql2date(get_option('date_format'),$start_date),$message);
            $message = str_replace('[time]',$start_time,$message);
            $message = str_replace('[name]',$name,$message);
            $message = str_replace('[address]',$address,$message);
            $message = str_replace('[mobile]',$mobile,$message);
            $message = str_replace('[phone]',$phone,$message);
            $message = str_replace('[iCal]',$ical,$message);
            //church_admin_debug($message);
            church_admin_visitation_pdf($people_id,1);
            $filename = md5($name).'.pdf';
            $upload_dir = wp_upload_dir();
            $attachment=$upload_dir['basedir'].'/church-admin-cache/'.$filename;
            //church_admin_debug($attachment);
           
            if(!empty($team_member_details->email)){
                church_admin_email_send($team_member_details->email,$settings['visitor_email_template']['subject'],$message,$settings['visitor_email_template']['from_name'],$settings['visitor_email_template']['from_email'],$attachment);
            }
            else{
                echo '<p>'.esc_html(__("Team member doesn't have an email address",'church-admin') ).'</p>';
            }
        }   
        if(!empty($_POST['email-visited'])){
            church_admin_debug('Email visited person');
            if(!empty($full_person_details->email)){
                $message = $settings['visited_email_template']['message'];
                $message = str_replace('[date]',mysql2date(get_option('date_format'),$start_date),$message);
                $message = str_replace('[time]',$start_time,$message);
                $message = str_replace('[visitor]',church_admin_formatted_name($team_member_details),$message);

                church_admin_email_send($team_member_details->email,$settings['visited_email_template']['subject'],$message,$settings['visited_email_template']['from_name'],$settings['visited_email_template']['from_email']);
            }else{
                echo '<p>'.esc_html(__("Person being visited doesn't have an email address",'church-admin') ).'</p>';
            }


        } 
        echo'<div class="notice notice-success"><h2>'.esc_html('Appointment booked','church-admin').'</h2></div>';
    }
    else
    {
        echo'<form action="admin.php?page=church_admin%2Findex.php&action=schedule-pastoral-visit" method="POST">';
        wp_nonce_field('schedule-pastoral-visit');
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('Date','church-admin')).'</label>';
        echo church_admin_date_picker( null,'visit_date',FALSE,date('Y-m-d'),date('Y-m-d',strtotime("+2 years") ),'visit_date','visit_date');
        echo'</div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('Time','church-admin')).'</label>';
        echo'<input class="church-admin-form-control" type="time" name="visit_time"></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html(__('Visit end time','church-admin')).'</label>';
        echo '<input class="church-admin-form-control" type="time" name="end_time"></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html(__('Team member','church-admin')).'</label>';
        echo'<select class="church-admin-form-control" name="visitor"><option>'.esc_html(__('Choose someone','church-admin')).'</option>';
        foreach($team AS $p_id=>$name){
            echo'<option value="'.(int)$p_id.'">'.esc_html($name).'</option>';
        }
        echo'</select></div>';
        echo'<div class="church-admin-checkbox"><input type="checkbox" name="email-visited"><label>'.sprintf(__('Email %1$s appointment details','church-admin'),church_admin_formatted_name($person)).'</label></div>';
        echo'<div class="church-admin-checkbox"><input type="checkbox" name="email-visitor"><label>'.sprintf(__('Email team member the appointment details','church-admin')).'</label></div>';
        echo'<p><input type="hidden" name="people_id" value="'.(int)$people_id.'"><input type="hidden" name="save" value="1"><input class="button-primary" type="submit" value="'.__('Save appointment','church-admin').'"></p>';
        echo'</form>';
   
       
    }
     /***********************
     * Monthly PDF link
     ***********************/
    $params = array('ca_download'=>'monthly-calendar-pdf');
    if(!empty($settings['cat_id'])){$params['cat_id']=$settings['cat_id'];}
    $params['url']=get_permalink();
    $url = add_query_arg( $params , site_url() );
    echo '<p><a href="'.$url.'">'.__('This calendar PDF','church-admin').'</a></p>';
    /***********************
     * End Monthly PDF link
     ***********************/

    require_once( plugin_dir_path( __FILE__ ).'/calendar.php');
    church_admin_new_calendar( wp_date('Y-m-01'),null,$settings['cat_id']);



}




