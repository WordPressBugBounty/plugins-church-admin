<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_block_category( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'church-admin-blocks',
				'title' => __( 'Church Admin', 'church-admin' ),
                'icon'  => 'wordpress',
			),
		)
	);
}
//add_filter( 'block_categories', 'church_admin_block_category', 10, 2);
/****************************************************************
* Selectively enqueue assets on front end for speed
**************************************************************/
add_action('wp_enqueue_scripts','church_admin_block_assets');
function church_admin_block_assets()
{
	if(is_admin() ) return;
	//enqueueing front end
	//church_admin_debug("Function church_admin_block_assets");
	global $post;
	
	if(has_block('church-admin/address-list',$post)
	||has_block('church-admin/basic-register',$post)
	||has_block('church-admin/attendance',$post)

	||has_block('church-admin/calendar',$post)
	||has_block('church-admin/custom-fields',$post)
	||has_block('church-admin/calendar-list',$post)
	
	||has_block('church-admin/register',$post)
	||has_block('church-admin/giving',$post)
	||has_block('church-admin/graph',$post)
	||has_block('church-admin/member-map',$post)

	||has_block( 'church-admin/recent',$post)
	||has_block('church-admin/sermon-podcast',$post)
	||has_block('church-admin/service-booking',$post)
	||has_block('church-admin/sermons',$post)
	||has_block( 'church-admin/sermon-series',$post)
	||has_block( 'church-admin/video-embed',$post)
	)
	{
		
		
		// Register our block editor script.
		
		if(has_block('church-admin/register',$post)
		||has_block('church-admin/basic-register',$post) )
		{

			wp_enqueue_script('ca-draganddrop', plugins_url( '/', dirname(__FILE__ ) ) . 'includes/draganddrop.js', array( 'jquery' ), filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/draganddrop.js'),TRUE);
			wp_enqueue_script( 'jquery-ui-datepicker');
			wp_enqueue_style('church-admin-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css',false,"1.13.2",false);
		}
		
		
		if(	has_block('church-admin/calendar',$post)
		|| has_block('church-admin/calendar-list',$post)
		)
		{
		
			wp_enqueue_script('church-admin-calendar-script',plugins_url('includes/calendar.js',dirname(__FILE__) ),array( 'jquery' ), filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.js'),TRUE );
			wp_enqueue_script('church-admin-calendar',plugins_url('includes/jQueryCalendar.js',dirname(__FILE__) ),array( 'jquery' ),filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/jQueryCalendar.js') ,TRUE);
		}
			//fix issue caused by some "premium" themes, which call google maps w/o key on every admin page. D'uh!
		wp_dequeue_script('avia-google-maps-api');
		if(	has_block('church-admin/register',$post)
			|| has_block('church-admin/basic-register',$post)
			
			|| has_block('church-admin/small-group-members',$post)
			|| has_block('church-admin/small-group-signup',$post)
			|| has_block('church-admin/address-list',$post)
		)
		{	

			//now enqueue google map api with the key & callback function
			$src = 'https://maps.googleapis.com/maps/api/js';
			$key='?key='.get_option('church_admin_google_api_key');
			
			wp_register_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);
			
		}
		if(has_block('church-admin/register',$post)|| has_block('church-admin/basic-register',$post) )
		{

			$api_key=get_option('church_admin_google_api_key');
			if(!empty($api_key))
			{
				wp_enqueue_script('ca-draganddrop', plugins_url( '/', dirname(__FILE__ ) ) . 'includes/draganddrop.js', array( 'jquery' ) ,FALSE, filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/draganddrop.js'));
				$src = 'https://maps.googleapis.com/maps/api/js';
				$key='?key='.$api_key;
				wp_enqueue_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);
			
				wp_enqueue_script('church_admin_map', plugins_url('includes/google_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE);
				wp_enqueue_script('church_admin_sg_map_script', plugins_url('includes/smallgroup_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE);
				wp_enqueue_script('church_admin_map_script', plugins_url('includes/maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE);
			}	
			
		}
		
		if(has_block('church-admin/member-map',$post) )
		{	$api_key=get_option('church_admin_google_api_key');
			if(!empty($api_key))
			{
				$src = 'https://maps.googleapis.com/maps/api/js';
				$key='?key='.$api_key;
				wp_enqueue_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);
				wp_enqueue_script('church_admin_map_script', plugins_url('includes/google_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE);

			}
		}
		if(has_block('church-admin/sermon-podcast',$post) )
		{
			church_admin_debug("Line 108");
			wp_enqueue_script('church_admin_podcast_audio_use',plugins_url('includes/audio.use.js',dirname(__FILE__) ), array( 'jquery' ),CHURCH_ADMIN_VERSION ,FALSE);
		}
		if(has_block('church-admin/sermons',$post) )
		{
			wp_enqueue_script('jquery-ui-datepicker');
			
			wp_enqueue_script('church_admin_podcast_audio_use',plugins_url('includes/audio.use.js',dirname(__FILE__) ), array( 'jquery' ),CHURCH_ADMIN_VERSION ,FALSE);
		}
		
	}
    
}
/*************************************
 * Enqueue all when in block editor
 ************************************/
add_action( 'enqueue_block_editor_assets', 'church_admin_block_editor_assets');
function church_admin_block_editor_assets()
{
	global $wpdb;

	$api_key=get_option('church_admin_google_api_key');
	if(!empty($api_key))
	{
		wp_enqueue_script('ca-draganddrop', plugins_url( '/', dirname(__FILE__ ) ) . 'includes/draganddrop.js', array( 'jquery' ) ,FALSE, filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/draganddrop.js'));
		$src = 'https://maps.googleapis.com/maps/api/js';
		$key='?key='.$api_key;
		wp_enqueue_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);
	
		wp_enqueue_script('church_admin_map', plugins_url('includes/google_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE);
		
		wp_enqueue_script('church_admin_map_script', plugins_url('includes/maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE);
	}	





	if(!is_admin() ) return;
	church_admin_debug("Function church_admin_block_editor_assets");
	
	if(function_exists('wp_get_jed_locale_data') )wp_add_inline_script(
		'church-admin-gutenberg-translation',
		'wp.i18n.setLocaleData( ' . json_encode(  wp_get_jed_locale_data( 'church-admin' ) ) . ', "church-admin" );',
		'before'
	);
	
	if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'church-admin', 'church-admin' );
				
		}
	wp_register_script(
		'church-admin-php-blocks',
		plugins_url( '/', dirname(__FILE__ ) ) . 'gutenberg/php-blocks.js',
		array( 'wp-blocks', 'wp-element','wp-components','wp-block-editor','wp-hooks','wp-server-side-render'),
		filemtime(plugin_dir_path(dirname(__FILE__) ).'gutenberg/php-blocks.js')
	);
	/**************************
	 * Add data for dropdowns
	 **************************/
	
	$peopleArray=array();
	$people_type=get_option('church_admin_people_type');
	foreach( $people_type AS $id=>$type)
	{
		$peopleArray[]=array('value'=>(int)$id,'label'=>esc_html( $type) );
	}
	$eventsArray= array();
	


	$MTArray=array();
	$memberTypesArray=church_admin_member_types_array();
	foreach( $memberTypesArray AS $id=>$type)
	{
		$MTArray[]=array('value'=>(int)$id,'label'=>esc_html( $type));
	}
	
	
	$addJSData="const peopleTypeOptions =".json_encode( $peopleArray)."\r\n";
	wp_add_inline_script( 'church-admin-php-blocks', $addJSData );
	

	
	wp_enqueue_script('church_admin_podcast_audio_use',plugins_url('includes/audio.use.js',dirname(__FILE__) ), array( 'jquery' ),CHURCH_ADMIN_VERSION ,FALSE);
		
	
	wp_enqueue_script('church-admin-calendar-script',plugins_url('includes/calendar.js',dirname(__FILE__) ),array( 'jquery' ),FALSE, filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.js') );
	wp_enqueue_script('church-admin-calendar',plugins_url('includes/jQueryCalendar.js',dirname(__FILE__) ),array( 'jquery' ),FALSE, filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/jQueryCalendar.js') );
}

/**
 * Register our block and shortcode.
 */
add_action( 'init', 'church_admin_block_init' );
function church_admin_block_init() {
	
	
	
	/**************
	*
	* Adddress list
	*
	*****************/
	register_block_type( 'church-admin/address-list', array(
		'title'=>esc_html( __('Address list','church-admin' ) ),
		'description'=>esc_html( __('Displays your address list according to set parameters','church-admin' ) ),
		'attributes'      => array(
			'member_type_id' => array('type' => 'string','default'=>esc_html( __('All','church-admin') )),
			'pdf'=> array('type' => 'boolean','default'=>1),
			'logged_in' => array('type' => 'boolean','default'=>1),
			'map'=> array('type' => 'boolean','default'=>1),
			'photo'=> array('type' => 'boolean','default'=>1),
			'kids'=> array('type' => 'boolean','default'=>1),
			'site_id'=> array('type' => 'string','default'=>''),
			'updateable'=> array('type' => 'boolean','default'=>1),
            'vcf'=> array('type' => 'boolean','default'=>1),
			'address_style'=>array('type'=>'string','default'=>'one'),
			'first_initial'=>array('type' =>'boolean','default'=>1),
			'colorscheme'=> array('type' => 'string','default'=>'white')
			
		),
		'keywords' =>array(
		__( 'Church Admin','church-admin' ),
		__( 'Address List','church-admin' ),
		__( 'Directory','church-admin' )
	),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'church_admin_block_address_list',
	) );
	
	
	/***********************************************************************************************
	*
	* Calendar
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/calendar', array(
		'title'=>esc_html( __('Calendar','church-admin' ) ),
		'description'=>esc_html( __('Displays your calendar','church-admin' ) ),
		'attributes'      => array(
			'style' => array('type' => 'boolean','default'=>0),
			
			'cat_id'=>array('type'=>'string','default'=>''),
			'fac_id'=>array('type'=>'string','default'=>''),
			'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'keywords'=>array(
		__( 'Church Admin','church-admin' ),
		__( 'Calendar','church-admin' )
	),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'church_admin_block_calendar',
		
	) );
	
	/***********************************************************************************************
	*
	* Calendar List
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/calendar-list', array(
		'title'=>esc_html( __('Calendar event list','church-admin' ) ),
		'description'=>esc_html( __('Displays list of calendar events','church-admin' ) ),
		'attributes'      => array(
			'style' => array('type' => 'boolean','default'=>0),
			'days'=>array('type'=>'integer','default'=>28),
			'cat_id'=>array('type'=>'string','default'=>''),
			'fac_id'=>array('type'=>'string','default'=>''),
			'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Calendar','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'church_admin_block_calendar_list',
		
	) );

	/***********************************************************************************************
	*
	* Front end register.
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/register', array(
		'title'=>esc_html( __('Register','church-admin' ) ),
		'description'=>esc_html( __('Displays registration/household edit for logged in users','church-admin' ) ),
		'attributes'      => array(
			'member_type_id' => array('type' => 'string','default'=>esc_html( __('Visitor','church-admin') ) ),
			'admin_email' => array('type' => 'boolean','default'=>TRUE),
			'colorscheme'=>array('type'=>'string','default'=>'white'),
			'allow_registrations' => array('type' => 'boolean','default'=>TRUE),
		),
		'keywords'=>array(
			esc_html(__( 'Church Admin' ,'church-admin' ) ),
			esc_html(__( 'Front End Register','church-admin' )),
			esc_html(__( 'User edit','church-admin' ))
		),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'church_admin_block_register',
	) );
    /***************************
    *
    *   Basic Register
    *
    ****************************/
		register_block_type( 'church-admin/basic-register', array(
			'title'=>esc_html( __('Basic register','church-admin' ) ),
			'description'=>esc_html( __('Displays registration/household edit for logged in users','church-admin' ) ),
			'attributes'      => array(
								'colorscheme'=>array('type'=>'string','default'=>'white'),
								'member_type_id'=>array('type'=>'integer','default'=>1),
								'gender'=>array('type'=>'boolean','default'=>0),
								'custom'=>array('type'=>'boolean','default'=>0),
								'onboarding'=>array('type'=>'boolean','default'=>0),
								'dob'=>array('type'=>'boolean','default'=>0),
								'admin_email'=>array('type'=>'boolean','default'=>1),
								'sites'=>array('type'=>'boolean','default'=>0),
								'groups'=>array('type'=>'boolean','default'=>0),
								'ministries'=>array('type'=>'boolean','default'=>0),
								'allow_registrations' => array('type' => 'boolean','default'=>TRUE),
								'onboarding' => array('type'=>'boolean','default'=>true),
								'full_privacy_show' => array('type'=>'boolean','default'=>true),
							),
			'keywords'=>array(
			__( 'Church Admin' ,'church-admin' ) ,
			__( 'Basic Registration form','church-admin' ),
			__( 'User edit','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'church_admin_block_basic_register',
	 ));
    
	/***********************************************************************************************
	*
	* Member Map
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/member-map', array(
		'title'=>esc_html( __('Member map','church-admin' ) ),
		'attributes'      => array(
			'member_type_id' => array('type' => 'string','default'=>1),
			'zoom'=> array('type' => 'string','default'=>12),
			'width' => array('type' => 'string','default'=>"100%"),
			'height' => array('type' => 'string','default'=>500),
			'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'keywords'=>array(
		__( 'Church Admin' ,'church-admin' ) ,
		__( 'Map','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'church_admin_block_member_map',
	) );

	
	/***********************************************************************************************
	*
	* Sermon Podcast
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/sermon-podcast', array(
			'title'=>esc_html( __('Sermon podcast','church-admin' ) ),
			'description'=>esc_html( __('Displays sermon podcast','church-admin' ) ),
			'attributes'      => array(
				'series_id'=> array('type' => 'string','default'=>''),
				'sermon_title'=> array('type' => 'string','default'=>''),
				'most_popular'=>array('type' => 'boolean','default'=>1),
				'order'=>array('type'=>'string','default'=>'DESC'),
				'exclude'=>array('type'=>'string','default'=>''),
				'howmany'=>array('type'=>'string','default'=>5),
				'colorscheme'=>array('type'=>'string','default'=>''),
				'nowhite'=>array('type'=>'boolean','default'=>0)
			),'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Media','church-admin' ),
				__( 'Sermons','church-admin' ),
				__( 'Podcast','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'church_admin_block_podcast',
	) );
	register_block_type( 'church-admin/sermons', array(
		'title'=>esc_html( __('Sermons (new style)','church-admin' ) ),
		'description'=>esc_html( __('Displays sermons','church-admin' ) ),
		'attributes'      => array(
			'howmany'=>array('type'=>'string','default'=>9),
			'start_date'=>array('type'=>'date','default'=>''),
			'rolling'=>array('type'=>'string','default'=>''),
			'nowhite'=>array('type'=>'boolean','default'=>1),
			'colorscheme'=>array('type'=>'string','default'=>''),
			'playnoshow' =>array('type'=>'boolean','default'=>0),
		),
		'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Media','church-admin' ),
			__( 'Sermons','church-admin' ),
			__( 'Podcast','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'church_admin_block_sermons',
	) );



	
	/***********************************************************************************************
	*
	* Recent
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/recent', array(
			'title'=>esc_html( __('Recent people editing activity','church-admin' ) ),
			'description'=>esc_html( __('Displays recent address list changes','church-admin' ) ),
			'attributes'      => array(
				'weeks' => array('type' => 'string','default'=>1),
				'colorscheme'=>array('type'=>'string','default'=>'')
				
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Recent activity','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'church_admin_block_recent',
	) );

	


	
   
   /***********************************************************************************************
	*
	* Sermon Series
	*
	***********************************************************************************************/

    register_block_type( 'church-admin/sermon-series', array(
		'title'=>esc_html( __('Sermon series','church-admin' ) ),
		'description'=>esc_html( __('Displays sermon series images as links','church-admin' ) ),
		'attributes'      => array(
			'sermon_page' => array('type' => 'string','default'=>''),
            'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'keywords'=>array(
		__( 'Church Admin','church-admin' ),
		__( 'Sermons','church-admin' ),
			
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'church_admin_block_series',
		) 
	);
}











function church_admin_block_video( $attributes ) {
    
    $embed=church_admin_generateVideoEmbedUrl( $attributes['url'] );
    $container=$attributes['container'];
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
		}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	church_admin_debug($attributes);
    $out.='<div class="'.esc_attr($container).'"><div style="position:relative;padding-top:56.25%"><iframe class="ca-video" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.esc_url($embed['embed']).'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>';
    $views=church_admin_youtube_views_api( esc_attr($embed['id']) );
    if(!empty( $views)&& !empty( $attribute['show_views'] ) ){
		$out.='<p>'.esc_html( sprintf(__('%1$s views','church-admin' ) ,$views) ).'</p>';
	}
    $out.='</div>';
	return $out;
}
function church_admin_block_calendar_list( $attributes ) {
	
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
		}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	$out.='<div class="church-admin-calendar alignwide">';
	require_once(plugin_dir_path(dirname(__FILE__) ) .'display/calendar-list.php');
	$out.=church_admin_calendar_list( $attributes['days'],$attributes['cat_id'],$attributes['fac_id'] );
	$out.='</div></div>';
	return $out;
}

function church_admin_block_calendar( $attributes ) {
	
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
		}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	$out.='<div class="church-admin-calendar alignwide">';

	$out.='<table><tr><td>'.esc_html( __('Year Planner PDFs','church-admin' ) ).' </td><td>  <form name="guideform" action="'.esc_attr(sanitize_text_field(stripslashes($_SERVER['PHP_SELF']))).'" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- '.esc_html( __('Choose a pdf','church-admin' ) ).' --</option>';
	for ( $x=0; $x<5; $x++)
	{
		$y=date('Y')+$x;
		$out.='<option value="'.home_url().'/?church_admin_download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.$y.'">'.$y.esc_html( __('Year Planner','church-admin' ) ).'</option>';
	}
	$out.='</select></form></td></tr></table>';
	if( $attributes['style'] )
	{
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/calendar.php');
		$out.=church_admin_display_calendar(NULL);
	}
	else
	{	
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/calendar.new.php');
		$out.=church_admin_display_new_calendar($attributes['cat_id'],$attributes['fac_id']);
	
	}
	$out.='</div></div>';
	return $out;
}



function church_admin_block_custom_fields( $attributes ) {
	
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
		}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	$out.='<div class="church-admin-custom-field alignwide">';
	if(!empty( $attributes['loggedin'] )& !is_user_logged_in() ) 
    {
            return '<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p></div></div>';
    }
	else
	{
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/custom-fields.php');
	
		$custom_id=church_admin_find_custom_id( $attributes['customField'] );
		//church_admin_debug('php-blocks line 765 : ' .$custom_id);
		if(!isset( $custom_id) )
		{
			$out.='<p>'.esc_html( __("Custom field not found yet, please check spelling or use the ID number for the custom field",'church-admin' ) ).'</p>';
		}else $out.=church_admin_display_custom_field( $attributes['days'],$attributes['showYears'],$custom_id);
	}
	$out.='</div></div>';
	return $out;
}


/***********************************************
 * 
 * 	Sermons
 * 
 ***********************************************/

 function church_admin_block_podcast( $attributes ) {
	
    
    require_once(plugin_dir_path(dirname(__FILE__) ) .'display/sermon-podcast.php');
	global $wpdb;
	$wpdb->show_errors;
	$file_id=$series_id=NULL;
    if(!empty( $attributes['howmany'] ) )
    {
        $limit=intval( $attributes['howmany'] );
    }
    else{$limit=5;}
	if(!empty( $attributes['series_id'] ) )
	{
		$series_id=church_admin_sanitize($attributes['series_id']);
		$file_id=NULL;
	}
	if(!empty( $attributes['sermon_title'] ) )
	{
		$file_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_services WHERE file_title LIKE "%'.esc_sql( $attributes['sermon_title'] ).'%"');
		$series_id=NULL;
	}
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
						case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
					}
		}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	$nowhite=empty( $attributes['nowhite'] )?0:1;
	
	$out.= church_admin_podcast_display( $series_id,$file_id,$attributes['exclude'],$attributes['most_popular'],$attributes['order'],$limit,$nowhite);
	$out.='</div>';
	return $out;
}

 function church_admin_block_sermons( $attributes ) {
	
    church_admin_debug('function church_admin_block_sermons');
    require_once(plugin_dir_path(dirname(__FILE__) ) .'display/new-sermon-podcast.php');
	global $wpdb;
	$wpdb->show_errors;
	$file_id=$series_id=NULL;
    if(!empty( $attributes['howmany'] ) )
    {
        $how_many=(int) $attributes['howmany'] ;
    }
    else{$how_many=9;}
	$playnoshow=!empty($attributes['playnoshow'])?1:0;
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
							$out.='ca-background ';
						break;
						case 'bluegrey':
						default: 
							$out.=' ca-dark-mode-blue-grey ';
						break;
						case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
					}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	$nowhite=empty( $attributes['nowhite'] )?0:1;
	$playnoshow=empty($attributes['playnoshow'])?0:1;
	$rolling=(!empty($attributes['rolling']) && church_admin_int_check($attributes['rolling'])) ? (int)$attributes['rolling'] : null;
	$start_date=(!empty($attributes['start_date']) &&church_admin_checkdate($attributes['start_date'])) ? $attributes['start_date'] : null;
	$out.= church_admin_new_sermons_display($how_many,$nowhite,$playnoshow,$start_date,$rolling);
	$out.='</div>';
	return $out;
}





/***********************************************
 * 
 * 	REGISTER
 * 
 ***********************************************/
function church_admin_block_basic_register( $attributes ) {
	require_once(plugin_dir_path(dirname(__FILE__) ) .'includes/front_end_register.php');
	$out='<div class="alignwide church-admin-shortcode-output';
    if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
		}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
    $out.='"><div class="church-admin-register">';
	if ( empty( $attributes['member_type_id'] ) )$attributes['member_type_id']=1;
	$exclude=array();
	if(!empty( $attributes['gender'] ) )$exclude[]='gender';
	if(!empty( $attributes['custom'] ) )$exclude[]='custom';
	if(!empty( $attributes['dob'] ) )$exclude[]='date_of_birth';
	$allow=array();
	if(!empty( $attributes['sites'] ) )$allow[]='sites';
	if(!empty( $attributes['ministries'] ) )$allow[]='ministries';
	if(!empty( $attributes['groups'] ) )$allow[]='groups';
	$allow_registrations = !empty($attributes['allow_registrations'])?1:0;
	$onboarding = !empty($attributes['onboarding']) ? true : false;
	$admin_email = !empty( $attributes['admin_email'] )?1:0;
	$full_privacy_show= !empty( $attributes['full_privacy_show'] )?1:0;
	//church_admin_debug('Basic register block attributes');
	//church_admin_debug( $attributes);
	
	$out .= church_admin_front_end_register( (int)$attributes['member_type_id'], $exclude, $admin_email , $allow, $allow_registrations,$onboarding,$full_privacy_show);
    
	$out.='</div></div>';
	return $out;

}
/***********************************************
 * 
 * 	ADDRESS LIST BLOCK
 * 
 ***********************************************/
function church_admin_block_address_list( $attributes ) {
    global $wpdb;
    church_admin_debug("Address list block");
	church_admin_debug( $attributes);
    
	require_once(plugin_dir_path(dirname(__FILE__) ) .'display/address-list.php');
	if ( empty( $attributes['address_style'] ) )$attributes['address_style']='one';
	/*******************************************************************************
	 * Handle member_type_id which is likely to be a word or comma separated word
	 ****************************************************************************/
	if(!empty( $attributes['member_type_id'] )&&( $attributes['member_type_id']!=__('All','church-admin') ))
	{
		$member_type_ids=implode(",",church_admin_get_member_type_ids( $attributes['member_type_id'] ) );
		church_admin_debug( $member_type_ids);

	}else
	{
		$member_type_ids=NULL;
		
	}
	church_admin_debug( $member_type_ids);
	//set $attributes['member_type_id'] to corrected list
	//church_admin_debug('church_admin_block_address_list member_type_ids comma list');
	//church_admin_debug( $member_type_ids);
    $out='';
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
		}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	
    $out.='">';
	$out.='<div class="church-admin-directory">';
	$api_key=FALSE;
	$api_key=get_option('church_admin_google_api_key');
	//assumed no access allowed
    $access=FALSE;
    if(!empty( $attributes['logged_in'] ) )
    {
       
		if(!is_user_logged_in() ) 
        {
            return '<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p></div></div>';
        }
        if(!empty( $member_type_ids) )
        {
			//note that $attributes['member_type_id'] is likely to be the names of member types not ids
            $mtArray=explode(",",$member_type_ids);
            $current_user=wp_get_current_user();
            $mt_id=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
            if ( empty( $mt_id) )return'<p>'.esc_html( __('Your login does not permit viewing the address list','church-admin' ) ).'</p>';
            if(!church_admin_level_check('Directory')&&!empty( $mt_id)&&!in_array( $mt_id,$mtArray) )return'<p>'.esc_html( __('Your login does not permit viewing the address list','church-admin' ) ).'</p></div>';
            $access=TRUE;
        } 
		if( $attributes['member_type_id']=='All'||$attributes['member_type_id']=='all')$access=true;
		$restrictedList=get_option('church-admin-restricted-access');
		if(!church_admin_level_check('Directory')&&!empty($restrictedList) && is_array( $restrictedList)&&in_array( $people_id,$restrictedList) )return'<p>'.esc_html( __('Your login does not permit viewing the address list','church-admin' ) ).'</p></div>'; 
		if(church_admin_level_check('Directory') )$access=TRUE;  
    }
    else
    {
        //open access
        $access=TRUE;
    }
	if(!empty( $access) )
    {
       
			if(!empty( $attributes['pdf'] ) )
			{
				$out.='<div class="church-admin-address-pdf-links"><p><a  rel="nofollow" target="_blank" href="'.wp_nonce_url(home_url().'/?church_admin_download=addresslist-family-photos&amp;kids='.esc_attr($attributes['kids']).'&amp;member_type_id='.esc_attr($member_type_ids),'address-list' ).'">'.esc_html( __('PDF version','church-admin' ) ).'</a></p></div>';
					
			}
			require_once(plugin_dir_path(dirname(__FILE__) ).'display/address-list.php');
		
       		$out.=church_admin_frontend_directory( $member_type_ids,$attributes['map'],$attributes['photo'],$api_key,$attributes['kids'],$attributes['site_id'],$attributes['updateable'],$attributes['first_initial'],0,$attributes['vcf'],$attributes['address_style'] );
        
        	
    }
    else //login required
    {
		if ( empty( $access) ) $out.='<h2>'.esc_html( __('You have not been granted access to the address list','church-admin' ) ).'</h2>';
		else $out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
    }
	
	$out.='</div></div><!--end shortcode output-->';
    return $out;
}


function church_admin_block_recent( $attributes ) {
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
	$out.=' ca-dark-mode-warm-grey ';
	break;
	case 'coolgrey':
		$out.=' ca-dark-mode-cool-grey ';
	break;
			}
		}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	if ( empty( $loggedin)||is_user_logged_in() )
	{
		require_once(plugin_dir_path(dirname(__FILE__) ).'includes/recent.php');
		
		$out.=church_admin_recent_display( $attributes['weeks'] );
	}
	else //login required
	{
		$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
	}
	$out.='</div>';
	return $out;
}





function church_admin_block_series( $attributes)
{
    if ( empty( $attributes['cols'] ) )$attributes['cols']=3;
    if ( empty( $attributes['sermon_page'] ) )$attributes['sermon_page']=NULL;
	$out='';
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

			switch( $attributes['colorscheme'] )
			{
				case 'white':
					$out.='ca-background ';
				break;
				case 'bluegrey':
				default: 
					$out.=' ca-dark-mode-blue-grey ';
				break;
				case 'warmgrey':
		$out.=' ca-dark-mode-warm-grey ';
	break;
	case 'coolgrey':
		$out.=' ca-dark-mode-cool-grey ';
	break;
			}
		}
		elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
		$out.='">';
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/sermon-series.php');
		$out.=church_admin_all_the_series_display( $attributes['sermon_page'] );
		$out.='</div>';	
		return $out;
}
function church_admin_block_member_map( $attributes)
{
	global $wpdb;
	$member_type_id=1;
	if(!empty( $attributes['member_type_id'] ) )$member_type_id=implode(",",church_admin_get_member_type_ids( $attributes['member_type_id'] ) );
	$out='';
	$out='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $attributes['colorscheme'] ) )  {

		switch( $attributes['colorscheme'] )
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
		}
	}
    elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
	$out.='">';
	if(is_user_logged_in() )
	{
		wp_enqueue_script('church_admin_google_maps_api');
		wp_enqueue_script('church_admin_map');
		
	    $service=$wpdb->get_row('SELECT lat,lng  FROM '.$wpdb->prefix.'church_admin_sites WHERE lat!="" AND lng!="" ORDER BY site_id ASC LIMIT 1');
    	$out.='<div class="church-admin-member-map">';
		
		$out.='<script type="text/javascript">var xml_url="'.site_url().'/?church_admin_download=address-xml&member_type_id='.esc_html( $attributes['member_type_id'] ).'&address-xml='.wp_create_nonce('address-xml').'";'."\r\n";
    	$out.=' var lat='.esc_html( $service->lat).';'."\r\n";
    	$out.=' var lng='.esc_html( $service->lng).';'."\r\n";
		$out.=' var zoom='.esc_html( $attributes['zoom'] ).';'."\r\n";
		$out.=' var translation=["'.esc_html( __('Small Groups','church-admin' ) ).'","'.esc_html( __('Unattached','church-admin' ) ).'","'.esc_html( __('In a group','church-admin' ) ).'","'.esc_html( __('Group','church-admin' ) ).'"];'."\r\n";
		$out.='jQuery(document).ready(function()  {'."\r\n";
		$out.='console.log("Ready to lead");'."\r\n";
    	$out.='load(lat,lng,xml_url,zoom,translation);});</script>';
		$out.='<div id="church-admin-member-map" style="width:'.esc_attr($attributes['width']).';height:'.esc_attr($attributes['height']).'">No map shown on editor screen sorry!</div>';
    	$out.='<div id="groups" ><p><img src="https://maps.google.com/mapfiles/kml/paddle/blu-circle.png" />'.esc_html( __('Small Group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/red-circle.png" />'.esc_html( __('Not in a small group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/grn-circle.png" />'.esc_html( __('In a small Group','church-admin' ) ).'</p></div>';
    	$out.='</div>';
	}
	else {
		$out.='<h3>'.esc_html( __('You need to be logged in to view the map','church-admin' ) ).'</h3>'.wp_login_form(array('echo'=>false) );
	}
	$out.='</div>';
    return $out;
}

