<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_smallgroup_signup( $title,$people_types)
{
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
    global $wpdb,$current_user,$wp_locale;
    $wpdb->show_errors;
    $out='<div class="church-admin-smallgroups">';
    if(!is_user_logged_in() )
    {
        $out.= wp_login_form(array('echo' =>FALSE) );
    }
    else
    {
        // grab household
            $current_uder=wp_get_current_user();
            $household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID .'"');
            if ( empty( $household_id) )
            {
                $out.='<p>'.esc_html( __("Sorry, can't find you in a directory",'church-admin' ) ).'</p>';
            }
            else
            {
                //Build SQL using given people types and household_id
                $peopleTypeSQL=$peopleTypeSQLArray=array();
                $people_type_ids=church_admin_get_people_type_ids( $people_types);
               
                foreach( $people_type_ids AS $key=>$PID)  {$peopleTypeSQLArray[]='`people_type_id`="'.intval( $PID).'"';}
                if(!empty( $peopleTypeSQLArray) )  {$peopleTypeSQL='AND ('.implode("OR",$peopleTypeSQLArray).')';}else{$peopleTypeSQL='';}
                $sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name,people_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" '.$peopleTypeSQL ;
               
                $household=$wpdb->get_results( $sql);
                if(!empty( $household) )
                {
                    if(!empty( $_POST['save_groups'] ) )
                    {
                        foreach( $household AS $people)
                        {
                            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people->people_id.'" AND meta_type="smallgroup"');
                            if(!empty( $_POST['person/'.$people->people_id] ) )
                            {
                                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,meta_type,ID) VALUES("'.(int)$people->people_id.'","smallgroup","'.(int)sanitize_text_field( stripslashes( $_POST['person/'.$people->people_id] ) ).'")');
                               
                            }
                        }
                        $out.='<div class="bs-callout bs-callout-info ">Small groups updated</div>';
                    }
                    
            
                        //grab groups
                        $smallGroups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup ORDER BY group_name');
                        if ( empty( $smallGroups) )
                        {
                            $out.='<p>'.esc_html( __("There are no small groups yet",'church-admin' ) ).'</p>';
                        }
                        else
                        {
                            $form='<form action="" method="POST"><input type="hidden" name="save_groups" value="1" />';
                            $form.='<table class="table table-striped table-bordered"><thead><tr><th>'.esc_html( __('Group name','church-admin' ) ).'</th>';
                            foreach( $household AS $people)
                            {
                                $form.='<th>'.esc_html( $people->name).'</th>';
                            }
                            $form.='</tr></thead><tbody>';
                            foreach( $smallGroups AS $row)
                            {
                                $noOfPeople=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup" AND ID="'.(int)$row->id.'"');
                                if( $noOfPeople<$row->max_attendees)
                                {
                                    $form.='<tr><td><h3>'.esc_html( $row->group_name).'</h3>';
                                    if(!empty( $row->contact_number) )$form.='<a href="'.esc_url('tel:'.$row->contact_number).'">'.esc_html( $row->contact_number).'</a><br>';
                                    
                                    if(!empty( $row->group_day) )
                                    {
                                        $day=$wp_locale->get_weekday( $row->group_day);
                                    }
                                    else $day='';
                                    $form.=$day.' '.esc_html(mysql2date(get_option('time_format'),$row->group_time)).'<br>';
                                    if ( empty( $no_address) )$form.=' '.esc_html( $row->address).'<br>';
                                    if(!empty( $row->description) )$form.=esc_html( $row->description);
                                    $form.='</td>';
                                    foreach( $household AS $people)
                                    {
                                        $form.='<td><input type="radio" name="person/'.(int)$people->people_id.'" value="'.(int)$row->id.'"';
                                        $groupID=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup" AND people_id="'.(int)$people->people_id.'"');
                                        if(!empty( $groupID)&& $groupID==$row->id)  {$form.='  checked="checked" ';}
                                        $form.='></td>';
                                    }
                                    $form.='</tr>';
                                }
                            }
                            $form.='</tbody></table><p><input type="submit" class="btn btn-danger" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
                        }
                        //produce output
                        $out.='<h2>'.esc_html( $title).'</h2>'.$form;
                    
                
        
            }
    
        }
    }
    $out.='</div>';
    return $out;

}