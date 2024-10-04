 <?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

 
 /**
 *
 * Site list
 *
 * @author  Andy Moyle
 * @param
 * @return
 * @version  1.088
 *
 * Using wordpress native table class now
 *
 */
function church_admin_site_list()
{

	global $wpdb;


	echo'<p><a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_site','edit_site').'">'.esc_html( __('Add a site','church-admin' ) ).'</a></p>';
	$api_key=get_option('church_admin_google_api_key');
	$results=$wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'church_admin_sites');
	
	if(!empty( $results) )
	{
        $theader='<tr><th class="column-primary">'.esc_html( __('Venue','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Image','church-admin' ) ).'</th><th>'.esc_html( __('Address','church-admin' ) ).'</th><th>'.esc_html( __('Map','church-admin' ) ).'</th></tr>';
		echo'<table class="widefat wp-list-table"><thead>'.$theader.'</thead><tbody>';
		foreach( $results AS $row)
		{
			if( $api_key)  {
                $mapImage='<img alt="map" src="https://maps.google.com/maps/api/staticmap?key='.$api_key.'&center='.$row->lat.','.$row->lng.'&zoom=15&markers=color:blue%7C'.$row->lat.','.$row->lng.'&size=150x100" />';}else{$mapImage='<a href="https://www.churchadminplugin.com/tutorials/google-api-key/" target="_blank">'.esc_html( __('Map will show when you have a Google Api Key set','church-admin')).'</a>';}
			$edit='<a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_site&amp;section=Services&amp;site_id='.(int)$row->site_id,'edit_site').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
			$delete='<a  class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_site&amp;section=Services&amp;site_id='.(int)$row->site_id,'delete_site').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
			if(!empty( $row->attachment_id) ){
                $image=wp_get_attachment_image( $row->attachment_id,'thumbnail','',array('class'=>'site-image','loading'=>'lazy') );
            }
            else
            {
                $image='<img src="'.plugins_url('/', dirname(__FILE__) ) . 'images/household.svg'.'" alt="'.esc_attr(__('Site image','church-admin')).'" />';
            }    
            echo'<tr>
            <td class="column-primary" data-colname="'.esc_html( __('Venue','church-admin' ) ).'">'.esc_html( $row->venue).'<button type="button" class="toggle-row">
            <span class="screen-reader-text">show details</span>
        </button></td>
            <td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
            <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
            <td data-colname="'.esc_html( __('Image','church-admin' ) ).'">'.$image.'</td>
            <td data-colname="'.esc_html( __('Address','church-admin' ) ).'">'.esc_html( $row->address).'</td>
            <td data-colname="'.esc_html( __('Map','church-admin' ) ).'">'.$mapImage.'</td>
            </tr>';
		}
		echo'</tbody><tfoot>'.$theader.'</tfoot></table>';
	}
}




 /**
 *
 * Delete site
 *
 * @author  Andy Moyle
 * @param    site_id
 * @return
 * @version  0.945
 *
 *
 *
 */
 function church_admin_delete_site( $site_id)
 {

 	global $wpdb;
 	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_sites WHERE site_id="'.esc_sql( $site_id).'"');
 	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_services WHERE site_id="'.esc_sql( $site_id).'"');
 	$new_site_id=$wpdb->get_var('SELECT site_id FROM '.$wpdb->prefix.'church_admin_sites ORDER BY site_id ASC');
 	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET site_id="'.$new_site_id.'" WHERE site_id="'.esc_sql( $site_id).'"');
 	echo'<div class="notice notice-success inline">Site Deleted</div>';
     require_once(plugin_dir_path(dirname(__FILE__) ).'includes/services.php');
 	echo church_admin_service_list();
 }


 /**
 *
 * Add/Edit site
 *
 * @author  Andy Moyle
 * @param    site_id
 * @return
 * @version  0.945
 *
 *
 *
 */
 function church_admin_edit_site( $site_id=NULL)
 {

 	global $wpdb;

 	 echo'<h2>'.esc_html( __('Add/Edit Site','church-admin' ) ).'</h2>';

    if( $site_id)$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sites WHERE site_id="'.esc_sql(intval( $site_id) ).'"');
    if(isset( $_POST['edit_site'] ) )
    {

        $form=array();
        foreach( $_POST AS $key=>$value)$form[$key]=sanitize_text_field(stripslashes( $value) );
        $attachment_id=!(empty($form['household_attachment_id']))?(int)$form['household_attachment_id']:null;
        if ( empty( $form['lat'] ) )
        {
            $form['lat']=0.0;
            $form['lng']=0.0;
        }

        if(!$site_id)$site_id=$wpdb->get_var('SELECT site_id FROM '.$wpdb->prefix.'church_admin_sites WHERE venue="'.esc_sql( $form['venue'] ).'" AND address="'.esc_sql( $form['address'] ).'" AND lat="'.esc_sql( $form['lat'] ).'" AND lng="'.esc_sql( $form['lng'] ).'" AND attachment_id="'.(int)$attachment_id.'"');
        if( $site_id)
        {//update
            $sql='UPDATE '.$wpdb->prefix.'church_admin_sites SET  attachment_id="'.(int)$attachment_id.'", venue="'.esc_sql( $form['venue'] ).'" , address="'.esc_sql( $form['address'] ).'" , lat="'.esc_sql( $form['lat'] ).'" , lng="'.esc_sql( $form['lng'] ).'", what_three_words="'.esc_sql( $form['what-three-words'] ).'" WHERE site_id="'.(int)$site_id.'"';

            $wpdb->query( $sql);
        }//update
        else
        {//insert
           $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sites (venue,address,lat,lng,what_three_words,attachment_id) VALUES ("'.esc_sql( $form['venue'] ).'","'.esc_sql( $form['address'] ).'","'.esc_sql( $form['lat'] ).'","'.esc_sql( $form['lng'] ).'","'.esc_sql( $form['what-three-words'] ).'","'.(int)$attachment_id.'")');
        }//insert
        echo'<div class="notice notice-success inline"><p>'.esc_html( __('Site saved','church-admin' ) ).'</p></div>';
        church_admin_site_list();

    }
    else
    {

       echo'<form action="'.esc_url(sanitize_url($_SERVER['REQUEST_URI'])).'" method="post">';

       echo'<table class="form-table"><tr><th scope="row">'.esc_html( __('Service Venue','church-admin' ) ).'</th><td><input type="text" name="venue" ';
       if(!empty( $data->venue) )echo' value="'.esc_html( $data->venue).'" ';
       echo'/></td></tr></table>';
       require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
       if ( empty( $data) )$data=new stdClass();

	   echo church_admin_address_form( $data,NULL);
       echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="edit_site" value="yes" /><input class="button-primary"  type="submit" value="'.esc_html( __('Save Site','church-admin' ) ).'&raquo;" /></td></tr></table></form>';
    }
 }
