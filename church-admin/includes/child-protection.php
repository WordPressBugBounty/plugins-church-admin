<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly



/***********************************
 *
 * Child Protection Reporting
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 ***********************************/
function church_admin_child_protection_reporting()
{

    // initialise
    global $wpdb;

    echo'<h2>'.esc_html('Child protection incidents','church-admin').'</h2>'."\r\n";
    $ministries = church_admin_ministries_array();
    if(empty($ministries)){
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=ministries','ministries').'">'.esc_html(__('Please set up some ministries including a safeguarding lead/team first','church-admin') ) .'</a></p>';
        return;
    }
    //safeguarding ministry ID needs setting
    if(!empty($_POST['safeguarding_ministry_id']) && church_admin_int_check($_POST['safeguarding_ministry_id'])){
        update_option('church_admin_safeguarding_ministry_id',(int)$_POST['safeguarding_ministry_id']);
    }
    $safeguarding_ministry_id = get_option('church_admin_safeguarding_ministry_id');
    if(empty($safeguarding_ministry_id)){$safeguarding_ministry_id=NULL;}
    echo'<form action="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=child-protection','child-protection').'" method="POST"><p>'.esc_html(__('Safeguarding Team Ministry','church-admin')).'<select name="safeguarding_ministry_id">';
    foreach($ministries AS $min_id=>$ministry){
        echo '<option value="'.(int)$min_id.'" '.selected($min_id,$safeguarding_ministry_id,FALSE).'>'.esc_html($ministry).'</option>';
    }
    echo'</select><input class="button-secondary" type="submit"></p>';
    $dsl_contact = $wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="team_contact" AND ID ="'.(int)$safeguarding_ministry_id.'"' );
    if(empty($dsl_contact)){
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=edit-ministry&id='.(int)$safeguarding_ministry_id,'edit-ministry').'">'.esc_html(__('Please add a team contact (designated safeguarding lead) for your safeguarding lead/team','church-admin') ) .'</a></p>';
        return;

    }

    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-child-protection-incident','edit-child-protection-incident').'">'.esc_html(__('Add child protection incident','church-admin') ).'</a></p>'."\r\n";
    $incidents_count =  church_admin_child_protection_open_incidents_count();
    echo '<p>'.esc_html($incidents_count['readable']).'</p>';
    
    echo'<p><form action="admin.php?page=church_admin/index.php&action=safeguarding&amp;section=child-protection">';
    
    wp_nonce_field('safeguarding');
    echo'<input type="text" name="search-name" placeholder="'.esc_attr(__('Search name','church-admin')).'"><input class="button-primary" type="submit" value="'.esc_attr(__('Search','church-admin')).'"></form></p>'."\r\n";
   
    //get incidents
    if(!empty($_POST['search-name'])){
        $child_search = '%'.church_admin_sanitize($_POST['search-name']).'%';
        $sql = 'SELECT * FROM '.$wpdb->prefix.'church_admin_child_protection_reporting WHERE child LIKE "'.esc_sql($child_search).'" ORDER BY status DESC, updated DESC';
        echo'<p>'.esc_html( sprintf( __( 'Searching for "%1$s"', 'church-admin' ), church_admin_sanitize($_POST['search-name'] ) ) ).'</p>';
    }
    else{
        $sql = 'SELECT * FROM '.$wpdb->prefix.'church_admin_child_protection_reporting ORDER BY status DESC, updated DESC';
    }
    
    $results = $wpdb->get_results($sql);

    if(empty($results )){return;}

    echo '<table class="widefat"><thead><tr>'."\r\n";
    echo '<th>'.__('Incident date','church-admin').'</th>'."\r\n";
    echo '<th>'.__('Incident title','church-admin').'</th>'."\r\n";
    
    echo '<th>'.__('Edit','church-admin').'</th>'."\r\n";
    echo '<th>'.__('View','church-admin').'</th>'."\r\n";
    echo '<th>'.__('Status','church-admin').'</th>'."\r\n";
    echo '<th>'.__('Child / Vulnerable Adult','church-admin').'</th>'."\r\n";
    echo '<th>'.__('Last Updated','church-admin').'</th>'."\r\n";
    echo '<th>'.__('Incident Log','church-admin').'</th>'."\r\n";
    echo'</tr></thead>'."\r\n";
    echo '<tbody>'."\r\n";

    foreach($results AS $row){
        $edit = '<a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-child-protection-incident&ID='.(int)$row->ID,'edit-child-protection-incident').'">'.esc_html(__('Edit','church-admin') ).'</a>';
        $view = '<a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=view-child-protection-incident&ID='.(int)$row->ID,'view-child-protection-incident').'">'.esc_html(__('View','church-admin') ).'</a>';
        $color = !empty($row->status) ? 'style="background:red;color:white" ' : 'style="background:green;color:white" ';
        $status = !empty($row->status) ? __('Open','church-admin') : __('Closed','church-admin') ;
        $child = !empty($row->child) ? esc_html($row->child) : '';
        $log = !empty($row->log) ? wp_kses_post($row->log) :'';
        $incident_date = mysql2date(get_option('date_format'),$row->incident_date);
        $updated = '<p>'.esc_html(church_admin_get_person($row->updated_by)).' - '.mysql2date(get_option('date_format'),$row->updated).'</p>';
        
        echo '<tr><td>'.esc_html($incident_date).'</td><td>'.esc_html($row->title).'</td><td>'.$edit.'</td><td>'.$view.'</td><td '.$color.'>'.esc_html($status).'</td><td>'.$child.'</td><td>'.$updated.'</td><td>'.$log.'</td></tr>'."\r\n";
    }

    echo'</tbody>'."\r\n";
    echo'</table>'."\r\n";

}

function church_admin_view_child_protection_incident($ID){


    global $wpdb;

    if(empty($ID)){
        echo'<div class="notice notice-success"><h2>'.esc_html(__('No incident found.','church-admin')).'</h2></div>';
        return;
    }
    $row = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_child_protection_reporting WHERE ID = "'.(int)$ID.'"' );
    if(empty($row)){
        echo'<div class="notice notice-success"><h2>'.esc_html(__('No incident data found.','church-admin')).'</h2></div>';
        return;
    }

    //output data

    echo '<h3>'.esc_html(sprintf(__('Incident "%1$s" on %2$s','church-admin'),$row->title,mysql2date(get_option('date_format'),$row->incident_date))).'</h3>';
    echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-child-protection-incident&ID='.(int)$row->ID,'edit-child-protection-incident').'">'.esc_html(__('Edit','church-admin') ).'</a></p>';

    //ouput data
    echo'<table class="ca-table striped">';
    
    echo '<tr><th scope="row">'.esc_html('Incident title','church-admin').'</th><td><strong>'.esc_html($row->title).'</strong></td></tr> '; 
    echo '<tr><th scope="row">'.esc_html('Incident date','church-admin').'</th><td>'.esc_html( mysql2date('Y-m-d H:i',$row->incident_date) ).'</td></tr> '; 
    echo '<tr><th scope="row">'.esc_html('Affected children/vulnerable adults','church-admin').'</th><td>'.esc_html($row->child).'</td></tr> '; 
    echo '<tr><th scope="row">'.esc_html('Description','church-admin').'</th><td>'.wp_kses_post(wpautop($row->description)).'</td></tr> '; 
    echo '<tr><th scope="row">'.esc_html('Action taken','church-admin').'</th><td>'.wp_kses_post(wpautop($row->action_taken)).'</td></tr> '; 
    echo '<tr><th scope="row">'.esc_html('Reporting date','church-admin').'</th><td>'.esc_html( mysql2date('Y-m-d H:i',$row->reporting_date) ).'</td></tr> '; 
    $color = !empty($row->status) ? 'style="background:red;color:white" ' : 'style="background:green;color:white" ';
    echo '<tr><th scope="row">'.esc_html('Location','church-admin').'</th><td>'.esc_html($row->location).'</td></tr> '; 
    $status = !empty($row->status) ? __('Open','church-admin') : __('Closed','church-admin');
    echo '<tr><th scope="row">'.esc_html('Status','church-admin').'</th><td '.$color.'>'.esc_html($status).'</td></tr> '; 
    $entered_by = church_admin_get_person($row->entered_by);
    echo '<tr><th scope="row">'.esc_html('Entered by','church-admin').'</th><td>'.esc_html($entered_by).'</td></tr> '; 
    echo '<tr><th scope="row">'.esc_html('Log','church-admin').'</th><td>'.wp_kses_post(wpautop($row->log)).'</td></tr> '; 
    echo'</table>';
}


function church_admin_edit_child_protection_incident($ID){
    church_admin_debug("**** church_admin_edit_child_protection_incident ****");
    //initialise
    global $wpdb;
    $safeguarding_ministry_id = get_option('church_admin_safeguarding_ministry_id');
    if(empty($safeguarding_ministry_id)){
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=child-protection','child-protection').'">'.esc_html(__('Please set up a "ministries" for designated safeguarding lead/team','church-admin') ) .'</a></p>';
        return;
    }
    $dsl_contact = $wpdb->get_row('SELECT a.people_id,b.* FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.people_id=b.people_id  AND a.meta_type="team_contact" AND a.ID ="'.(int)$safeguarding_ministry_id.'"' );
    if(empty($dsl_contact)){
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=edit-ministry&id='.(int)$safeguarding_ministry_id,'edit-ministry').'">'.esc_html(__('Please add a team contact (designated safeguarding lead) for your safeguarding lead/team','church-admin') ) .'</a></p>';
        return;

    }
    church_admin_debug("DSL DETAILS");
    church_admin_debug($dsl_contact);
    $user = wp_get_current_user();
    if(empty($user)){return __('Not logged in','church-admin');}


    if(!empty($ID)) { echo'<h2>'.esc_html(__('Edit child protection incident','church-admin')).'</h2>';}
    else{ echo'<h2>'.esc_html(__('Add child protection incident','church-admin')).'</h2>';}


    $editor = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    $editor_name = church_admin_formatted_name($editor);

    if(!empty($ID)){
        $data = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_child_protection_reporting WHERE ID = "'.(int)$ID.'"' );
    }

    if(!empty($_POST['save'])){

        $form=array();
        foreach($_POST AS $key=>$value){$form[$key] = church_admin_sanitize($value);}
        $form['status'] = !empty($_POST['status']) ? 1:0; 
       
        $form['incident_datetime'] = $form['incident_date'].' '.$form['incident_time'];
        //sort log
        $log = !empty($data->log) ? $data->log : '';
        if(empty($data))
        {
            $log = sprintf(__('Logged by %1$s','church-admin'),$editor_name);
        }
        else{
            //status change 
            $time = wp_date(get_option('date_format').' '.get_option('time_format'));
            if(!empty($data->status) && empty($form['status'])){
                if($data->action_taken!=$form['action_taken']){
                    $log.='<p>'.sprintf(__('Edited by %1$s %2$s','church-admin'),$editor_name,$time).'</p>';
                }
                $log.='<p>'.sprintf(__('Closed by %1$s %2$s','church-admin'),$editor_name,$time).'</p>';
            }
            elseif(empty($data->status) && !empty($form['status'])){
                $log.='<p>'.sprintf(__('Re-opened by %1$s %2$s','church-admin'),$editor_name,$time).'</p>';
            }
            else{
                $log.='<p>'.sprintf(__('Edited by %1$s %2$s','church-admin'),$editor_name,$time).'</p>';
            }
        }

        if(empty($ID)){
            $ID = $wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_child_protection_reporting WHERE title = "'.esc_sql($form['title']).'" AND description = "'.esc_sql($form['description']).'" AND child = "'.esc_sql($form['child']).'" AND incident_date="'.esc_sql($form['incident_datetime']).'" AND reporting_date="'.esc_sql($form['reporting_date']).'" AND location="'.esc_sql($form['location']).'"AND action_taken="'.esc_sql($form['action_taken']).'" AND status="'.esc_sql($form['status']).'" AND entered_by="'.esc_sql($form['entered_by']).'" AND updated_by="'.esc_sql($editor->people_id).'" AND updated="'.esc_sql(wp_date('Y-m-d')).'"');
            
        }

        if(!empty($ID)){
             //update
             $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_child_protection_reporting SET title = "'.esc_sql($form['title']).'",description = "'.esc_sql($form['description']).'",child = "'.esc_sql($form['child']).'",incident_date="'.esc_sql($form['incident_datetime']).'",reporting_date="'.esc_sql($form['reporting_date']).'",location="'.esc_sql($form['location']).'",action_taken="'.esc_sql($form['action_taken']).'",status="'.esc_sql($form['status']).'",entered_by="'.esc_sql($form['entered_by']).'",updated_by="'.esc_sql($editor->people_id).'",updated="'.esc_sql(wp_date('Y-m-d')).'",log="'.esc_sql($log).'" WHERE ID = "'.(int)$ID.'"');
             
             $dsl_message ='<p>'. sprintf(__('Child protection issue "%1$s" updated by %2$s','church-admin'),$form['title'],$editor_name).'</p><p>&nbsp;</p>';
             $dsl_email_message =$dsl_message.'<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;"><tr><td align="center" bgcolor="#19cca3" role="presentation" style="border:none;border-radius:6px;cursor:auto;padding:11px 20px;background:#19cca3;" valign="middle"><a href="'.wp_nonce_url(admin_url().'/admin.php?page=church_admin/index.php&action=view-child-protection-incident&ID='.(int)$ID,'view-child-protection-incident').'" style="background:#19cca3;color:#ffffff;font-family:Helvetica, sans-serif;font-size:18px;font-weight:600;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">'.esc_html(__('View','church-admin') ).'</a></td></tr></table>';


             echo'<div class="notice notice-success"><h2>'.esc_html(__('Incident updated','church-admin')).'</h2></div>';
        }
        else
        {
            //insert
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_child_protection_reporting (title, description, child, incident_date, reporting_date, location, action_taken, status, entered_by,updated_by, updated, log) VALUES ("'.esc_sql($form['title']).'","'.esc_sql($form['description']).'","'.esc_sql($form['child']).'","'.esc_sql($form['incident_datetime']).'","'.esc_sql($form['reporting_date']).'","'.esc_sql($form['location']).'","'.esc_sql($form['action_taken']).'","'.esc_sql($form['status']).'","'.esc_sql($editor->people_id).'","'.esc_sql($editor->people_id).'","'.esc_sql(wp_date('Y-m-d')).'","'.esc_sql($log).'")');
            $ID = $wpdb->insert_id;

            echo'<div class="notice notice-success"><h2>'.esc_html(__('Incident recorded','church-admin')).'</h2></div>';
            
            $dsl_message = '<p>'.sprintf(__('Child protection issue "%1$s" added by %2$s','church-admin'),$form['title'],$editor_name).'</p><p>&nbsp;</p>';
            $dsl_email_message =$dsl_message.'<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;"><tr><td align="center" bgcolor="#19cca3" role="presentation" style="border:none;border-radius:6px;cursor:auto;padding:11px 20px;background:#19cca3;" valign="middle">  <a href="href="'.wp_nonce_url(admin_url().'/admin.php?page=church_admin/index.php&action=view-child-protection-incident&ID='.(int)$ID,'view-child-protection-incident').'" style="background:#19cca3;color:#ffffff;font-family:Helvetica, sans-serif;font-size:18px;font-weight:600;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">'.esc_html(__('View','church-admin') ).'</a></td></tr></table>';
        }
        church_admin_email_send($dsl_contact->email,__('Child Protection Incident Report'),$dsl_email_message,null,null,array(),null,null,FALSE);

        if(!empty($_POST['urgent']) && !empty( $dsl_contact->pushToken ) ){
            church_admin_debug('Send push message to DSL');
            church_admin_send_push('tokens','message',array($dsl_contact->pushToken),__('Urgent Child Protection Issue'),$dsl_message,$editor_name);
        }


        church_admin_child_protection_reporting();
    }
    else
    {
        $url = wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-child-protection-incident','edit-child-protection-incident');
        if(!empty($ID))$url.='&amp;ID='.(int)$ID;
        echo'<form action="'.$url.'" method="POST">';
        //title
        echo '<div class="church-admin-form-group"><label>'.esc_html('Incident title','church-admin').'</label><input class="church-admin-form-control" type="text" name="title" ';
        if(!empty($data->title)){ echo 'value="'.esc_attr($data->title).'" '; }
        echo '></div>';
        //description
        echo '<div class="church-admin-form-group"><label>'.esc_html('Description','church-admin').'</label><textarea class="church-admin-form-control" name="description">';
        if(!empty($data->description)) echo esc_html($data->description);
        echo'</textarea></div>';
        //action taken
        echo '<div class="church-admin-form-group"><label>'.esc_html('Action taken','church-admin').'</label><textarea class="church-admin-form-control" name="action_taken">';
        if(!empty($data->action_taken)) echo esc_html($data->action_taken);
        echo'</textarea></div>';
        //affected children
        $currentChild = !empty($data->child) ? $data->child : '';
        echo '<div class="church-admin-form-group"><label>'.esc_html('Children or vulnerable adults affected','church-admin').'</label>';
        echo church_admin_autocomplete('child','friends','to',$currentChild,FALSE);
        echo'</div>';
        //incident date & time
        $incident_date = !empty($data->incident_date)? mysql2date('Y-m-d',$data->incident_date) : wp_date('Y-m-d');
        echo '<div class="church-admin-form-group"><label>'.esc_html('Incident date','church-admin').'</label>';
        echo church_admin_date_picker( $incident_date,'incident_date',FALSE,NULL,NULL,'incident_date','incident_date',FALSE,'incident_date',NULL,NULL);
        echo'</div>';
        $incident_time = !empty($data->incident_date)? mysql2date('H:i',$data->incident_date) : '';
        echo '<div class="church-admin-form-group"><label>'.esc_html('Incident time','church-admin').'</label><input type="time" name="incident_time" value="'.esc_attr($incident_time).'"></div>';
        //reporting date
        $reporting_date = !empty($data->reporting_date)? mysql2date('Y-m-d',$data->reporting_date) : wp_date('Y-m-d');
        echo '<div class="church-admin-form-group"><label>'.esc_html('Reporting date','church-admin').'</label>';
        echo church_admin_date_picker( $reporting_date,'reporting_date',FALSE,NULL,NULL,'reporting_date','reporting_date',FALSE,'reporting_date',NULL,NULL);
        echo'</div>';
        //location
        echo '<div class="church-admin-form-group"><label>'.esc_html('Location','church-admin').'</label><input class="church-admin-form-control" type="text" name="location" ';
        if(!empty($data->location)){ echo 'value="'.esc_attr($data->location).'" '; }
        echo '></div>';

        //status
        $status = !empty($data->status) ? 1 : 0;
        echo '<div class="church-admin-form-group"><label>'.esc_html('Status','church-admin').'</label><select class="church-admin-form-control"  name="status">';
        echo'<option value="1" '.selected($status,1,FALSE).'>'.esc_html(__('Open','church-admin')).'</option>';
        echo'<option value="0" '.selected($status,0,FALSE).'>'.esc_html(__('Closed','church-admin')).'</option>';
        echo'</select></div>';
        $safeguarding_ministry_id = get_option('church_admin_safeguarding_ministry_id');
        $safeguarding_team = church_admin_get_people_meta_array( 'ministry',$safeguarding_ministry_id);
        echo'<div class="church-admin-form_group"><label>'.esc_html(__('Urgent push message safeguarding lead?','church-admin')).'</label><input type="checkbox" name="urgent"></div>';
        //entered by
        $entered_people_id = !empty($row->entered_by_id) ? (int)$row->entered_by_id : $editor->people_id;
        echo '<div class="church-admin-form-group"><label>'.esc_html('Entered by','church-admin').'</label><select class="church-admin-form-control"  name="entered_by">';
        foreach($safeguarding_team AS $people_id=>$name){
            echo '<option value="'.(int)$people_id.'" '.selected($people_id,$entered_people_id,FALSE).'>'.esc_html($name).'</option>';
        }
        echo '</select>';
        echo'<p><input type="hidden" name="save" value="yes"><input class="button-primary" type="submit" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';




    }

}




function church_admin_child_protection_open_incidents_count(){
    global $wpdb;
    $count = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_child_protection_reporting WHERE status=1');
    if(empty($count)){$count = 0;}

    $output = array('count'=>$count,'readable'=>sprintf(
        _n(
        '%d open incident',
        '%d open incidents',
        $count,'church-admin'
        ),
        $count
        ) );


    return $output;
}