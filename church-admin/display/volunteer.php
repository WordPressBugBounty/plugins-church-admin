<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_display_volunteer()
{
  $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
      global $wpdb;
      $out='<div class="church-admin-volunteer">';
      $out.='<h2>'.esc_html( __('Serve in a ministry','church-admin' ) ).'</h2>';
      $volunteerMessage=get_option('church-admin-volunteer-message');
      if(!empty( $volunteerMessage) ) {$out.=wpautop( $volunteerMessage);}
      else{$out.='<p>'.esc_html( __('You can volunteer for various ministries in the church here. The team leaders will be in touch','church-admin' ) ).'</p>';}
      if(!empty( $_POST['nonce'] )&&wp_verify_nonce( $_POST['nonce'],'volunteer') )
      {
        $volunteers = !empty($_POST['volunteer'])?church_admin_sanitize($_POST['volunteer']):array();
          foreach( $volunteers AS $key=>$value)
          {
              $data=explode("/",$value);
              $ministry_id=$data[0];
              $people_id=$data[1];
              $person=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,last_name) AS name,email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
              if(!empty( $ministry_id)&&!empty( $person) )
              {
                church_admin_update_people_meta( $ministry_id,$people_id,'volunteer',NULL);
                $ministry=$wpdb->get_var('SELECT ministry FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int)$ministry_id.'"');
                $token = md5(NONCE_SALT.$people_id);
                $approve=wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=approve-volunteer&token='.$token.'&ministry_id='.(int)$ministry_id.'&people_id='.(int)$people_id,'approve-volunter');
                $decline=wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=decline-volunteer&token='.$token.'&ministry_id='.(int)$ministry_id.'&people_id='.(int)$people_id,'decline-volunteer');
                $contact=__('No contact details','church-admin');
                if(!empty( $person->email) )  {$contact=antispambot($person->email);}
                elseif(!empty( $person->mobile) )  {$contact=$person->mobile;}
                $message='<p>'.esc_html( sprintf(__('%1$s (%2$s) has just volunteered for %3$s, please approve them or get in touch','church-admin' ) ,$person->name,$contact,$ministry) ).'</p>';
                $message.='<p><a href="'.$approve.'">'.esc_html( __('Approve them (24hr link)','church-admin' ) ).'</a></p>';
                $message.='<p><a href="'.$decline.'">'.esc_html( __('Decline them  (24hr link)','church-admin' ) ).'</a></p>';
                $message.='<p>'.__('Please note, the links only last 12 hours! After that please loginto approve/decline','church-admin').'</p>';
                $team_contact=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="team_contact" AND ID="'.(int)$ministry_id.'"');
                $team_contact_email=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$team_contact.'"');
                if ( empty( $team_contact_email) )$team_contact_email=get_option('church_admin_default_from_email');
                $subject=esc_html(sprintf(__('New volunteer request for %1$s','church-admin' ) ,$ministry));

                
                church_admin_email_send($team_contact_email,$subject,$message,null,null,null,null,null,TRUE);
                
              }

              $out.="<p>".esc_html(__('Thank you for volunteering, we will be in touch','church-admin')).'</p>';
          }


      }
      else{


              if(!is_user_logged_in() )
              {
                $out.=esc_html(__('You need to login to volunteer','church-admin'));
                $out.=wp_login_form(array('echo'=>FALSE) );
              }
            else
            {
              $current_user = wp_get_current_user();
              $sql='SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"';
              $household_id=$wpdb->get_var( $sql);
              $sql='SELECT first_name, people_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY people_order ASC';

              $people=$wpdb->get_results( $sql);
              if(!empty( $people) )
              {
                  $ministries=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE volunteer=1 ORDER BY ministry ASC');
                  if(!empty( $ministries) )
                  {
                      $out.='<form action="'.esc_url(get_permalink()).'" method="POST">';
                      $out.='<table class="form-table table-striped table-bordered">';
                      $out.='<thead><tr><th>'.esc_html( __('Ministry team','church-admin' ) ).'</th>';
                      foreach( $people AS $person)
                      {
                          $out.='<th>'.esc_html( $person->first_name).'</th>';
                      }
                      $out.='</tr></thead><tbody>';
                      foreach( $ministries AS $ministry)
                      {
                        $out.='<tr><th scope="row">'.esc_html( $ministry->ministry).'</th>';
                        foreach( $people AS $person)
                        {
                          $already=FALSE;
                          $already=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="ministry" AND ID="'.(int)$ministry->ID.'" AND people_id="'.(int)$person->people_id.'"');
                          if( $already)  {$out.='<td>'.esc_html( __('Already','church-admin' ) ).'</td>';}
                          else{$out.='<td><input type="checkbox" name="volunteer[]" value="'.(int)$ministry->ID.'/'.(int)$person->people_id.'" /></td>';}
                        }
                        $out.='</tr>';
                    }

                    $out.='</tbody></table>';
                    $out.=wp_nonce_field( 'volunteer', 'nonce', TRUE, FALSE );
                    $out.='<p><input type="submit" class="btn btn-success" value="'.esc_attr(__('Send request','church-admin')).'" /></p>';
                    $out.='</form>';
                  }
                  else {
                    $out.='<p>'.esc_html(__('No ministries available for volunteering','church-admin')).'</p>';
                  }
                }else {
                        $out.='<p>'.esc_html(__("Sorry, you need to be in our church directory to volunteer.",'church-admin')).'</p>';
                      }
            }
            }
$out.='</div>';
return $out;


}
