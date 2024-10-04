<?PHP

 /**
 *
 * Registers google map api with low priority, so it happens last on enqueuing!
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */



function church_admin_media_uploader_enqueue() {
    if(is_admin() ) wp_enqueue_media();//enqueue media uploader if on admin page
 
}
 
function church_admin_google_map_api()
{

    //fix issue caused by some "premium" themes, which call google maps w/o key on every admin page. D'uh!
    wp_dequeue_script('avia-google-maps-api');

    //now enqueue google map api with the key
    $src = 'https://maps.googleapis.com/maps/api/js';
	$key='?key='.get_option('church_admin_google_api_key');
   
  
	wp_enqueue_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);


}
function church_admin_calendar_script()
{
    wp_enqueue_script(
           'church-admin-calendar-script',
           plugins_url( '/',dirname(__FILE__) ). 'includes/calendar.js',
           array( 'jquery' ),
           FALSE, TRUE
       );
}

function church_admin_frontend_graph_script()
{

	wp_enqueue_script('google-graph-api','https://www.google.com/jsapi', array( 'jquery' ) ,FALSE, FALSE);

}
function church_admin_podcast_script()
{
	$ajax_nonce = wp_create_nonce("church_admin_mp3_play");
	wp_enqueue_script('jquery');
	wp_localize_script( 'ca_podcast_audio', 'ChurchAdminAjax1', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script('ca_podcast_audio_use');
	wp_localize_script( 'ca_podcast_audio_use', 'ChurchAdminAjax', array('security'=>$ajax_nonce, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
function church_admin_sortable_script()
{
	wp_enqueue_script( 'jquery-ui-sortable' ,'','',NULL);
	wp_enqueue_script('touch-punch',plugins_url('/',dirname(__FILE__) ). 'includes/jQuery.touchpunch.js', array( 'jquery' ) ,FALSE, TRUE);
}
function church_admin_form_script()
{
	wp_enqueue_script('form-clone',plugins_url('/',dirname(__FILE__) ). 'includes/jquery-formfields.js', array( 'jquery' ) ,filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/jquery-formfields.js'), TRUE);
}
//deprecated
function church_admin_sg_map_script()
{

	church_admin_google_map_api();
	//wp_enqueue_script('ca_smallgroups_map_script', plugins_url('/',dirname(__FILE__) ). 'includes/smallgroup_maps.js', array( 'jquery' ) ,FALSE, TRUE);
    wp_enqueue_script('church_admin_sg_map_script', plugins_url('includes/smallgroup_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/smallgroup_maps.js'));
}
function church_admin_map_script()
{
   
    wp_enqueue_script('church_admin_main_map_script', plugins_url('/',dirname(__FILE__) ). 'includes/maps.js', array( 'jquery' ) ,filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/maps.js'), TRUE);
    church_admin_google_map_api();
}
function church_frontend_map_script()
{
	church_admin_google_map_api();
	wp_enqueue_script('js_map', plugins_url('/',dirname(__FILE__) ). 'includes/google_maps.js', array( 'jquery' ) ,filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/google_maps.js'), TRUE);
}
function church_admin_autocomplete_script()
{
	wp_enqueue_script('jquery-ui-autocomplete', array( 'jquery' ),'',NULL);
}
function church_admin_date_picker_script()
{
	global $church_admin_version;
    wp_enqueue_script( 'jquery-ui-datepicker','','',NULL );
    wp_enqueue_style('church-admin-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css',false,"1.13.2",false);

}
function church_admin_farbtastic_script()
{
	wp_enqueue_script( 'farbtastic' ,'','',NULL);
    wp_enqueue_style('farbtastic','','',NULL);
}
function church_admin_email_script()
{
	wp_enqueue_script('jquery','','',NULL);
    wp_register_script('ca_email',  plugins_url('/',dirname(__FILE__) ). 'includes/email.js', array( 'jquery' ) ,FALSE, TRUE);
	wp_enqueue_script('ca_email','','',NULL);
}
function church_admin_editable_script()
{
    wp_register_script('ca_editable',  plugins_url('/',dirname(__FILE__) ). 'includes/jquery.jeditable.mini.js', array('jquery'), NULL,TRUE);
    wp_enqueue_script('ca_editable');
}