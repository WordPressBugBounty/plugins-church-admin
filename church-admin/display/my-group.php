<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
function church_admin_my_group()
{
	$licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
    global $wpdb;
	$out='<h2>'.esc_html( __('My group','church-admin' ) ).'</h2>';
	//check token first
	if(!is_user_logged_in() )
	{
		$out.=wp_login_form(array('echo' => false) );
	}
	else
	{

		
		$user=wp_get_current_user();
        if(defined('CA_DEBUG') )church_admin_debug('USer'.print_r( $user,TRUE) );
        if( $user)$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
        if( $people_id)
        {
				/********************************************
				*
				*	From app v2.6, look for multiple groups
				*
				*********************************************/
				$groupIDs=$wpdb->get_results('SELECT a.ID FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b WHERE a.meta_type="smallgroup" AND b.user_ID="'.(int)$user->ID.'" and a.people_id=b.people_id AND a.ID!=1');
				if(defined('CA_DEBUG') )church_admin_debug('Group IDS'.print_r( $groupIDs,TRUE) );
				if(!empty( $groupIDs) )
				{
					           
					foreach( $groupIDs AS $groupID)
                    {
                        $groupName=$wpdb->get_var('SELECT group_name FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$groupID->ID.'"');
                        if(defined('CA_DEBUG') )church_admin_debug('Group name: '.print_r( $groupName,TRUE) );
                        $groupDate=array();
                        
                        $people=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$groupID->ID.'" AND meta_type="smallgroup"');
                        if(!empty( $people) )
                        {
                            foreach( $people AS $person)
                            {
                                $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person->people_id.'"');
								$privacy=unserialize($data->privacy);
								if(!empty( $data->mobile)  &&!empty($privacy['show-cell']) )  {$mobile='<a href="'.esc_url('call:'. $data->mobile).'">'.esc_html( $data->mobile).'</a>';}else{$mobile='&nbsp;';}
                                if(!empty( $data->email)  &&!empty($privacy['show-email']))  {$email='<a href="'.esc_url('mailto:'.antispambot($data->email)).'">'.esc_html( antispambot($data->email )).'</a>';}else{$email='&nbsp;';}
                                $groupData[$data->last_name.$data->first_name]='<tr><td>'.esc_html(implode(" ",array_filter(array( $data->first_name,$data->prefix,$data->last_name) ))).'</td><td>'.$mobile.'</td><td>'.$email.'</td></tr>';
                                ksort( $groupData);
                            }
                            $out.='<table class="table table-bordered table-striped"><thead><tr><th colspan=3>'.esc_html( $groupName).'</tr></thead><tbody>'.wpkses_post(implode("\r\n",$groupData)).'</tbody></table>';
                        }
                        
                    }
				}
				else
				{//no groups found for user
					$out.='<p>'.esc_html(__("You don't seem to be in any small groups",'church-admin')).'</p>';
				}
				
			}else{$out.='<p>'.esc_html(__("You don't seem to have a directory entry",'church-admin')).'</p>';}

		}
	return $out;
    }