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
	//check not doubling up with premium
	$registry = WP_Block_Type_Registry::get_instance();
	if ( $registry->get_registered( 'church-admin/address-list' ) ) {
		return;
	}
	//enqueueing front end
	//church_admin_debug("Function church_admin_block_assets");
	global $post;

	if(has_block('church-admin/address-list',$post)
	||has_block('church-admin/basic-register',$post)
	||has_block('church-admin/attendance',$post)
	||has_block('church-admin/birthdays',$post)
	||has_block('church-admin/calendar',$post)
	||has_block('church-admin/custom-fields',$post)
	||has_block('church-admin/calendar-list',$post)
	||has_block('church-admin/event_booking',$post)
	||has_block('church-admin/register',$post)
	||has_block('church-admin/giving',$post)
	||has_block('church-admin/graph',$post)
	||has_block('church-admin/member-map',$post)
	||has_block('church-admin/ministries',$post)
	||has_block('church-admin/my-rota',$post)
	||has_block('church-admin/rota',$post)
	||has_block('church-admin/pledges',$post)
	||has_block( 'church-admin/recent',$post)
	||has_block('church-admin/sermon-podcast',$post)
	||has_block('church-admin/service-booking',$post)
	||has_block('church-admin/sermons',$post)
	||has_block( 'church-admin/sermon-series',$post)
	||has_block('church-admin/serving',$post)
	||has_block('church-admin/sessions',$post)
	||has_block('church-admin/small-groups-list',$post)
	||has_block('church-admin/small-group-members',$post)
	||has_block('church-admin/small-group-signup',$post)
	||has_block( 'church-admin/volunteer',$post)
	||has_block( 'church-admin/video-embed',$post)
	)
	{
		
		
		// Register our block editor script.
		
		if(has_block('church-admin/register',$post)
		||has_block('church-admin/basic-register',$post) )
		{

			wp_enqueue_script('ca-draganddrop', plugins_url( '/', dirname(__FILE__ ) ) . 'includes/draganddrop.js', array( 'jquery' ), filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/draganddrop.js'),TRUE);
			wp_enqueue_script( 'jquery-ui-datepicker');//,plugins_url('church-admin/includes/jquery-ui.min.js',dirname(__FILE__) ),array('jquery'), filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/jquery-ui.min.js'),TRUE );
			wp_enqueue_style('church-admin-ui',plugins_url('/',dirname(__FILE__) ). 'css/jquery-ui-1.13.2.css',false,"1.13.2",false);
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
		if(has_block('church-admin/small-groups-list',$post) )
		{

			wp_enqueue_script('church_admin_sg_map_script', plugins_url('includes/smallgroup_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/smallgroup_maps.js'));
			//now enqueue google map api with the key & callback function
			$src = 'https://maps.googleapis.com/maps/api/js';
			$key='?key='.get_option('church_admin_google_api_key');
			
			wp_register_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);
		}	
		//wp_enqueue_script('church_admin_map_script', plugins_url('includes/google_maps.js',dirname(__FILE__) ), array( 'jquery' ) ,FALSE);
		if(has_block('church-admin/sermon-podcast',$post) )
		{
			church_admin_debug("Line 108");
			wp_enqueue_script('ca_podcast_audio_use',plugins_url('includes/audio.use.js',dirname(__FILE__) ), array( 'jquery' ),filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/audio.use.js'),FALSE);
		}
		if(has_block('church-admin/sermons',$post) )
		{
			wp_enqueue_script('jquery-ui-datepicker');
			
			wp_enqueue_script('ca_podcast_audio_use',plugins_url('includes/audio.use.js',dirname(__FILE__) ), array( 'jquery' ),filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/audio.use.js'),FALSE);
		}
		if(has_block('church-admin/giving',$post) )
		{
			
			wp_enqueue_script('church-admin-giving-form',plugins_url( '/', dirname(__FILE__ ) ) . 'includes/giving.js',array( 'jquery' ),filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/giving.js'), TRUE);
		}
		if(has_block('church-admin/event_booking',$post) )
		{
			church_admin_debug("Line 119");
			wp_register_script('church-admin-event-booking',plugins_url( '/', dirname(__FILE__) ) . 'includes/event-booking.js',array( 'jquery' ),FALSE, TRUE);
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
	if(!is_admin() ) return;
	
	//check not doubling up with premium
	$registry = WP_Block_Type_Registry::get_instance();
	if ( $registry->get_registered( 'church-admin/address-list' ) ) {
		return;
	}


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
	$seriesArray=array(array('value'=>null,'label'=>esc_html( __('All series')) ));
	$series = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY series_name ASC');
	
	if(!empty($series)){
		foreach($series AS $item){
			$seriesArray[]=array('value'=>(int)$item->series_id,'label'=>esc_html( $item->series_name) );
		}
	}
	
	
	$peopleArray=array();
	$people_type=get_option('church_admin_people_type');
	foreach( $people_type AS $id=>$type)
	{
		$peopleArray[]=array('value'=>(int)$id,'label'=>esc_html( $type) );
	}
	
	$MTArray=array();
	$memberTypesArray=church_admin_member_types_array();
	foreach( $memberTypesArray AS $id=>$type)
	{
		$MTArray[]=array('value'=>(int)$id,'label'=>esc_html( $type));
	}
	$sitesArray = array();
	$sitesArray = church_admin_sites_array();
	church_admin_debug('SITES'.print_r($sitesArray,TRUE));
	foreach($sitesArray AS $id=>$site){
		$SitesArray[]=array('value'=>(int)$id,'label'=>$site);
	}
	$addJSData="const seriesOptions =".json_encode( $seriesArray)."\r\n";
	$addJSData.="const membTypeOptions =".json_encode( $MTArray)."\r\n";
	$addJSData.="const siteOptions =".json_encode( $SitesArray)."\r\n";
	$addJSData.="const serviceOptions =".json_encode( $serviceArray)."\r\n";
	$addJSData.="const peopleTypeOptions =".json_encode( $peopleArray)."\r\n";
	wp_add_inline_script( 'church-admin-php-blocks', $addJSData );
	wp_enqueue_script('church-admin-event-booking',plugins_url( '/',dirname(__FILE__ ) ) . 'includes/event-booking.js',array( 'jquery' ),FALSE, TRUE);
	
	wp_enqueue_script( 'jquery-ui-datepicker');//,plugins_url('church-admin/includes/jquery-ui.min.js',dirname(__FILE__) ),array('jquery'),NULL );
	wp_enqueue_script('church-admin-giving-form',plugins_url( '/', dirname(__FILE__ ) ) . 'includes/giving.js',array( 'jquery' ),FALSE, TRUE);
	wp_enqueue_script('ca_podcast_audio_use',plugins_url('includes/audio.use.js',dirname(__FILE__) ), array( 'jquery' ),filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/audio.use.js'),FALSE);
		
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
	wp_enqueue_script('church-admin-calendar-script',plugins_url('includes/calendar.js',dirname(__FILE__) ),array( 'jquery' ),FALSE, filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.js') );
	wp_enqueue_script('church-admin-calendar',plugins_url('includes/jQueryCalendar.js',dirname(__FILE__) ),array( 'jquery' ),FALSE, filemtime(plugin_dir_path(dirname(__FILE__) ).'includes/jQueryCalendar.js') );
}

/**
 * Register our block and shortcode.
 */
add_action( 'init', 'ca_block_init' );
function ca_block_init() {
	
	//check not doubling up with premium
	$registry = WP_Block_Type_Registry::get_instance();
	if ( $registry->get_registered( 'church-admin/address-list' ) ) {
		return;
	}
	
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
		'render_callback' => 'ca_block_address_list',
	) );
	/**************
    *
    * Giving
    *
    **************/
    	register_block_type( 'church-admin/giving', array(
			'title'=>esc_html( __('Giving','church-admin' ) ),
			'description'=>esc_html( __('Online giving form using PayPal','church-admin' ) ),
		'attributes'      => array('fund'=>array('type'=>'string','default'=>""),'colorscheme'=>array('type'=>'string','default'=>'') ),
            'keywords'=>array(
		__( 'Church Admin','church-admin' ),
		__( 'Giving','church-admin' ),
		__( 'PayPal','church-admin' )
	),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_giving',
	) );

       	register_block_type( 'church-admin/pledges', array(
			'title'=>esc_html( __('Pledges','church-admin' ) ),
		'attributes'      => array('colorscheme'=>array('type'=>'string','default'=>'') ),
            'keywords'=>array(
		__( 'Church Admin','church-admin' ),
		__( 'Giving','church-admin' ),
		__( 'Pledges','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_pledge',
	) );
    /***********************************************************************************************
	*
	* Attendance
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/attendance', array(
		'title'=>esc_html( __('Attendance','church-admin' ) ),
		'description'=>esc_html( __('Show attendance graphs','church-admin' ) ),
		'attributes'      => array('colorscheme'=>array('type'=>'string','default'=>'') ),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_attendance',
	) );
	/***********************************************************************************************
	*
	* Birthdays
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/birthdays', array(
		'title'=>esc_html( __('Birthdays','church-admin' ) ),
		'description'=>esc_html( __('Displays birthdays according to set parameters','church-admin' ) ),
		'attributes'      => array(
			'member_type_id' => array('type' => 'string','default'=>esc_html( __('Member, Mailing List','church-admin') )),
			'people_type_id' => array('type' => 'string','default'=>esc_html( __('Adult,Child,Teenager','church-admin') )),
			'days' => array('type' => 'string','default'=>31),
            'show_age'=>array('type'=>'boolean','default'=>0),
			'show_email'=>array('type'=>'boolean','default'=>0),
			'show_phone'=>array('type'=>'boolean','default'=>0),
			'colorscheme'=>array('type'=>'string','default'=>''),
			'loggedin'=>array('type'=>'boolean','default'=>1),
		),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_birthdays',
	) );
	register_block_type( 'church-admin/anniversaries', array(
		'title'=>esc_html( __('Anniversaries','church-admin' ) ),
		'description'=>esc_html( __('Displays wedding anniversaries according to set parameters','church-admin' ) ),
		'attributes'      => array(
			'member_type_id' => array('type' => 'string','default'=>esc_html( __('Member, Mailing List','church-admin') )),
			'people_type_id' => array('type' => 'string','default'=>esc_html( __('Adult,Child,Teenager','church-admin') )),
			'days' => array('type' => 'string','default'=>31),
            'show_age'=>array('type'=>'boolean','default'=>0),
			'show_email'=>array('type'=>'boolean','default'=>0),
			'show_phone'=>array('type'=>'boolean','default'=>0),
			'colorscheme'=>array('type'=>'string','default'=>''),
			'loggedin'=>array('type'=>'boolean','default'=>1),
		),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_anniversaries',
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
		'render_callback' => 'ca_block_calendar',
		
	) );
	/***********************************************************************************************
	*
	* Custom fields
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/custom-fields', array(
		'title'=>esc_html( __('Custom fields','church-admin' ) ),
		'attributes'      => array(
			'style' => array('type' => 'boolean','default'=>0),
			'colorscheme'=>array('type'=>'string','default'=>''),
			'days'=>array('type'=>'string','default'=>28),
			'customField'=>array('type'=>'string','default'=>''),
			'showYears'=>array('type' => 'boolean','default'=>0),
			'loggedin'=>array('type' => 'boolean','default'=>1)
		),
		'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Calendar','church-admin' )
		),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_custom_fields',
		
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
			'render_callback' => 'ca_block_calendar_list',
		
	) );
	/***********************************************************************************************
	*
	* Event Booking
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/event-booking', array(
		'title'=>esc_html( __('Event Booking','church-admin' ) ),
		'description'=>esc_html( __('Displays event booking form','church-admin' ) ),
		'attributes'      => array(
			'event_id'=>array('type'=>'string','default'=>''),
			
			'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Event booking','church-admin' ),
			__( 'Event tickets','church-admin' )
		),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_event_booking',
		
	) );
	/***********************************************************************************************
	*
	* Not Available
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/not-available', array(
		'title'=>esc_html( __('Not Available to serve','church-admin' ) ),
		'description'=>esc_html( __('Displays form for user to select unavailable dates','church-admin' ) ),
		'attributes'      => array(
			'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Not Available','church-admin' ),
			__( 'Schedule','church-admin' )
		),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_not_available',
		
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
		'render_callback' => 'ca_block_register',
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
			'render_callback' => 'ca_block_basic_register',
	 ));
    /*************************
	*
	*Graph
	*
	***************************/
	register_block_type( 'church-admin/graph', array(
			'title'=>esc_html( __('Graph','church-admin' ) ),
			'description'=>esc_html( __('Displays graph','church-admin' ) ),
			'attributes'      => array(
				'width' => array('type' => 'string','default'=>900),
				'height' => array('type' => 'string','default'=>500),
				'colorscheme'=>array('type'=>'string','default'=>'')
			),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_graph',
	) );
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
			'render_callback' => 'ca_block_member_map',
	) );
	/***********************************************************************************************
	*
	* Ministries
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/ministries', array(
		'title'=>esc_html( __('Ministries','church-admin' ) ),
		'description'=>esc_html( __('Displays ministries','church-admin' ) ),
		'attributes'      => array(
			'member_type_id' => array('type' => 'string','default'=>esc_html( __('Member','church-admin') )),
			'ministry_id' => array('type' => 'string','default'=>'#'),
			'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_ministries',
	) );
	/***********************************************************************************************
	*
	* My rota
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/my-rota', array(
		'title'=>esc_html( __('My schedule','church-admin' ) ),
		'description'=>esc_html( __('Displays a users schedule entries','church-admin' ) ),
		'attributes'      => array('colorscheme'=>array('type'=>'string','default'=>'') ),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_my_rota',
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
			'render_callback' => 'ca_block_podcast',
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
			'render_callback' => 'ca_block_sermons',
	) );



	/***********************************************************************************************
	*
	* Rota
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/rota', array(
			'title'=>esc_html( __('Schedule','church-admin' ) ),
			'description'=>esc_html( __('Displays schedule for church services','church-admin' ) ),
			'attributes'      => array(
				'weeks'=> array('type' => 'string','default'=>5),
				'service_id' => array('type' => 'string','default'=>1),
				'logged_in' => array('type' => 'boolean','default'=>1),
				'title'=> array('type' => 'string','default'=>__("Schedule",'church-admin') ),
				'initials'=>array('type'=>'boolean','default'=>0),
				'links'=>array('type'=>'boolean','default'=>1),
				'name_style'=>array('type'=>'string','default'=>'Full'),
				'colorscheme'=>array('type'=>'string','default'=>''),
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Rota','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_rota',
	) );
    /***********************************************************************************************
	*
	* Service Prebooking
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/service-booking', array(
			'title'=>esc_html( __('Service booking','church-admin' ) ),
			'description'=>esc_html( __('Displays service prebooking form','church-admin' ) ),
			'attributes'      => array(
				'days'=> array('type' => 'string','default'=>7),
				'booking_mode'=>array('type'=>'string','default'=>'individuals'),
				'service_id' => array('type' => 'string','default'=>1),
				'max_fields' =>array('type' => 'string','default'=>5),
				'admin_email_address' =>array('type' => 'string','default'=>get_option('church_admin_default_from_email') ),
				'email_text'=>array('type'=>'string','default'=>''),
				'colorscheme'=>array('type'=>'string','default'=>''),
				'loggedin'=>array('type'=>'boolean','default'=>0)
			),
			'keywords'=>array(
				__( 'Church Admin','church-admin' ),
				__( 'Rota','church-admin' )
			),
				'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
				'render_callback' => 'ca_block_service_booking',
		) );
	/***********************************************************************************************
	*
	* Small Groups List
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/small-groups-list', array(
			'title'=>esc_html( __('Small group list','church-admin' ) ),
			'description'=>esc_html( __('Displays list of small groups','church-admin' ) ),
			'attributes'      => array(
				'map'=> array('type' => 'boolean','default'=>1),
				'zoom' => array('type' => 'string','default'=>12),
				'title' => array('type' => 'string','default'=>'Small Groups'),
				'photo'=> array('type' => 'boolean','default'=>1),
				'loggedin'=>array('type' => 'boolean','default'=>1),
				'pdf'=>array('type' => 'boolean','default'=>1),
				'colorscheme'=>array('type'=>'string','default'=>'')
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Small groups','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_smallgroups',
	) );
	/***********************************************************************************************
	*
	* Small Groups Members
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/small-group-members', array(
			'title'=>esc_html( __('Small group members','church-admin' ) ),
			'description'=>esc_html( __('Displays members of each small group','church-admin' ) ),
			'attributes'      => array(
				'member_type_id' => array('type' => 'string','default'=>esc_html( __('Member','church-admin') )),
				'colorscheme'=>array('type'=>'string','default'=>'')
				
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Small groups','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_smallgroup_members',
	) );
    /***********************************************************************************************
	*
	* Small Groups Signup
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/small-groups-signup', array(
			'title'=>esc_html( __('Small groups signup','church-admin' ) ),
			'description'=>esc_html( __('Displays signup form for small groups','church-admin' ) ),
			'attributes'      => array(
				'people_types' => array('type' => 'string','default'=>esc_html( __('Adults','church-admin') )),
				'title' => array('type' => 'string','default'=>esc_html( __('Small Groups Signup','church-admin') )),
				'colorscheme'=>array('type'=>'string','default'=>'')
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Small groups','church-admin' ),
				__( 'Small groups signup','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_smallgroups_signup',
	) );
	/***********************************************************************************************
	*
	* Serving
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/serving', array(
			'title'=>esc_html( __('Serving form','church-admin' ) ),
			'description'=>esc_html( __('Displays serving form','church-admin' ) ),
			'attributes'      => array(
				'logged_in' => array('type' => 'boolean','default'=>1),
				'colorscheme'=>array('type'=>'string','default'=>'')
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Serving','church-admin' ),
			__( 'Volunteer','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_serving',
	) );
	/***********************************************************************************************
	*
	* Sessions
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/sessions', array(
			'title'=>esc_html( __('Sessions','church-admin' ) ),
			'description'=>esc_html( __('Displays sessions module','church-admin' ) ),
			'attributes'      => array(
				
				'colorscheme'=>array('type'=>'string','default'=>'')
				
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Sessions','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_sessions',
	) );
	/***********************************************************************************************
	*
	* Spiritual gifts
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/spiritual-gifts', array(
			'title'=>esc_html( __('Spiritual gifts','church-admin' ) ),
			'description'=>esc_html( __('Displays spiritual gifts questionnaire','church-admin' ) ),
			'attributes'      => array(
				'admin_email_address' =>array('type' => 'string','default'=>get_option('church_admin_default_from_email') ),
				
				'colorscheme'=>array('type'=>'string','default'=>'')
				
			),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Spiritual gifts','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_spiritual_gifts',
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
			'render_callback' => 'ca_block_recent',
	) );

	/***********************************************************************************************
	*
	* Volunteer
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/volunteer', array(
			'title'=>esc_html( __('Volunteer','church-admin' ) ),
			'description'=>esc_html( __('Displays volunteering form','church-admin' ) ),
			'keywords'=>array(
			__( 'Church Admin','church-admin' ),
			__( 'Volunteer','church-admin' ),
				__( 'Serve','church-admin' )
		),
			'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
			'render_callback' => 'ca_block_volunteer',
	) );
    

/***********************************************************************************************
	*
	* Contact form
	*
	***********************************************************************************************/
	register_block_type( 'church-admin/contact-form', array(
		'title'=>esc_html( __('Contact form','church-admin' ) ),
		'description'=>esc_html( __('Displays the contact form','church-admin' ) ),
		'keywords'=>array(
		__( 'Church Admin','church-admin' ),
		__( 'Contact','church-admin' ),
		
	),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_contact',
) );






    /***********************************************************************************************
	*
	* Video Embed
	*
	***********************************************************************************************/
    
    register_block_type( 'church-admin/video-embed', array(
		'title'=>esc_html( __('Video embed','church-admin' ) ),
		'description'=>esc_html( __('Displays a video embed with responsive sizing','church-admin' ) ),
		'attributes'      => array(
			'url' => array('type' => 'string','default'=>''),
            'show_views'=>array('type'=>'boolean','default'=>1),
            'container'=>array('type'=>'string','default'=>'alignfull'),
			'colorscheme'=>array('type'=>'string','default'=>'')
		),
		'keywords'=>array(
		__( 'Church Admin','church-admin' ),
		__( 'Video','church-admin' ),
		__( 'YouTube','church-admin' )
	),
		'editor_script'   => 'church-admin-php-blocks', // The script name we gave in the wp_register_script() call.
		'render_callback' => 'ca_block_video',
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
			'render_callback' => 'ca_block_series',
		) 
	);
}




function ca_block_event_booking( $attributes)
{
	$out='';
	wp_enqueue_script('church-admin-form-case-enforcer');
	wp_enqueue_script('church-admin-event-booking');
	require_once(plugin_dir_path(dirname(__FILE__) ).'display/events.php');
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
	$out.=church_admin_event_bookings_output( $attributes['event_id'] );
	$out.='</div>';
	return $out;
}






function ca_block_service_booking( $attributes)
{
   church_admin_debug('*** ca_block_service_booking ***');
   church_admin_debug($attributes);
    wp_enqueue_script('church-admin-form-case-enforcer');
    require_once(plugin_dir_path(dirname(__FILE__) ).'display/covid-prebooking.php');
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
	if ( empty( $attributes['loggedin'] )||is_user_logged_in() )
	{
    	$out.= church_admin_covid_attendance((int)$attributes['service_id'],esc_html( $attributes['booking_mode'] ),(int)$attributes['max_fields'],(int)$attributes['days'],$attributes['admin_email_address'],$attributes['email_text'] );
	}
	else
	{
		$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
					 
	}
    $out.='</div>';
	return $out;
}
function ca_block_video( $attributes ) {
    
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
		//translators: %1$s is a number of video views
		$out.='<p>'.esc_html( sprintf(__('%1$s views','church-admin' ) ,$views) ).'</p>';
	}
    $out.='</div>';
	return $out;
}
function ca_block_calendar_list( $attributes ) {
	
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

function ca_block_calendar( $attributes ) {
	
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
		$out.='<option value="'.home_url().'/?ca_download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.$y.'">'.$y.esc_html( __('Year Planner','church-admin' ) ).'</option>';
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



function ca_block_custom_fields( $attributes ) {
	
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

 function ca_block_podcast( $attributes ) {
	
    
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

 function ca_block_sermons( $attributes ) {
	
    church_admin_debug('function ca_block_sermons');
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
 * Ministries
 * 
 ***********************************************/


function ca_block_ministries( $attributes ) {
	require_once(plugin_dir_path(dirname(__FILE__) ) .'display/ministries.php');
	$member_type_id=implode(",",church_admin_get_member_type_ids( $attributes['member_type_id'] ) );
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
	$out.=church_admin_frontend_ministries( $attributes['ministry_id'],$member_type_id);
	$out.='</div>';
	return $out;
}

/***********************************************
 * 
 * Serving
 * 
 ***********************************************/
function ca_block_serving( $attributes ) {
	require_once(plugin_dir_path(dirname(__FILE__) ) .'display/volunteer.php');
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
	$out.=church_admin_display_volunteer();
	$out.='</div>';
	return $out;
}
/***********************************************
 * 
 * 	Sessions
 * 
 ***********************************************/

function ca_block_sessions( $attributes ) {
	require_once(plugin_dir_path(dirname(__FILE__) ) .'includes/sessions.php');
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
		$out.=church_admin_sessions(NULL,NULL);
		$out.='</div>';
		return $out;
	}


	function ca_block_register( $attributes ) {
		require_once(plugin_dir_path(dirname(__FILE__) ) .'includes/front_end_register.php');
		church_admin_debug('**** REGISTER BLOCK ****');
		//church_admin_debug($attributes);
	
		
		$member_type_id=(int) $attributes['member_type_id'];
		
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
		if ( empty( $attributes['admin_email_address'] ) )$attributes['admin_email_address']=get_option('church_admin_default_from_email');
		$allow_registrations = !empty($attributes['allow_registrations']) ? true : false;
		$onboarding = !empty($attributes['onboarding']) ? true : false;
		$full_privacy_show = !empty($attributes['full_privacy_show']) ? true : false;
		$out.='"><div class="church-admin-register">';
		//$out.= church_admin_front_end_register( $member_type_id,NULL,$attributes['admin_email_address'] );
		$out .= church_admin_front_end_register( $member_type_id, NULL, $attributes['admin_email_address'] ,NULL, $allow_registrations,$onboarding,$full_privacy_show);
		$out .= '</div></div>';
		return $out;
}

/***********************************************
 * 
 * 	REGISTER
 * 
 ***********************************************/
function ca_block_basic_register( $attributes ) {
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
function ca_block_address_list( $attributes ) {
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
	//church_admin_debug('ca_block_address_list member_type_ids comma list');
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
				$out.='<div class="church-admin-address-pdf-links"><p><a  rel="nofollow" target="_blank" href="'.wp_nonce_url(home_url().'/?ca_download=addresslist-family-photos&amp;kids='.esc_attr($attributes['kids']).'&amp;member_type_id='.esc_attr($member_type_ids),'address-list' ).'">'.esc_html( __('PDF version','church-admin' ) ).'</a></p></div>';
					
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
function ca_block_rota( $attributes ) {
	require_once(plugin_dir_path(dirname(__FILE__) ) .'display/rota.php');

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
    if(defined('CA_DEBUG') )church_admin_debug( $attributes);
	if(is_user_logged_in()||empty( $attributes['logged_in'] ) )
	{
       
        if(!empty( $_REQUEST['rota_date'] ) )  {
			$date=sanitize_text_field(stripslashes($_REQUEST['rota_date']));
		}else{
			$date=wp_date('Y-m-d');
		}
		if(!church_admin_checkdate($date)){$date = wp_date('Y-m-d');}
        $out.=church_admin_front_end_rota( $attributes['service_id'],$attributes['weeks'],TRUE,$date,$attributes['title'],$attributes['initials'],$attributes['links'],$attributes['name_style'] );
	}
	else //login required
	{
		$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
	}
	$out.='</div>';
	return $out;
}
function ca_block_my_rota( $attributes ) {
	require_once(plugin_dir_path(dirname(__FILE__) ) .'display/rota.php');
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
        $out.=church_admin_my_rota();
	}
	else //login required
	{
		$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
	}
	$out.='</div>';
	return $out;
}
function ca_block_smallgroups( $attributes ) {
	wp_enqueue_script('church_admin_google_maps_api');
	wp_enqueue_script('church_admin_sg_map_script');
	require_once(plugin_dir_path(dirname(__FILE__) ).'/display/small-group-list.php');
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
    $out.=church_admin_small_group_list( $attributes['map'],$attributes['zoom'],$attributes['photo'],$attributes['loggedin'],$attributes['title'],$attributes['pdf'] );
    $out.='</div>';
	return $out;
	
}
function ca_block_smallgroups_signup( $attributes ) {
	
	require_once(plugin_dir_path(dirname(__FILE__) ).'/display/small-group-signup.php');
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
    $out.=church_admin_smallgroup_signup( $attributes['title'],$attributes['people_types'] );
	$out.='</div>';
    return $out;
	
}
function ca_block_smallgroup_members( $attributes ) {
	wp_enqueue_script('church_admin_google_maps_api');
	wp_enqueue_script('church_admin_sg_map_script');
	require_once(plugin_dir_path(dirname(__FILE__) ).'/display/small-groups.php');
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
    $out.=church_admin_frontend_small_groups( $attributes['member_type_id'],FALSE);
	$out.='</div>';
	return $out;
}
function ca_block_recent( $attributes ) {
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
function ca_block_not_available( $attributes)  {
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
	require_once(plugin_dir_path(dirname(__FILE__) ).'display/not-available.php');
	$out.=church_admin_not_available();
	$out.='</div>';
	return $out;
}
function ca_block_spiritual_gifts( $attributes ) {
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
	if(is_user_logged_in() )
	{
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/spiritual-gifts.php');
		if ( empty( $attributes['admin_email'] ) )$attributes['admin_email']='';
		$out.=church_admin_spiritual_gifts( $attributes['admin_email'] );
	}
	else //login required
	{
		$out.='<div class="login"><h2>'.esc_html( __('Please login to fill out our spiritual gifts questionnaire','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
	}
	$out.='</div>';
	return $out;
}
function ca_block_graph( $attributes)
{
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('church_admin_google_graph_api');
		if(!empty( $_POST['type'] ) )
		{
			switch( $_POST['type'] )
			{
				case'weekly':$graphtype='weekly';break;
				case'rolling':$graphtype='rolling';break;
				default:$graphtype='weekly';break;
			}
		}else{$graphtype='weekly';}
		$start = !empty( $_POST['start'] ) ? sanitize_text_field(stripslashes($_POST['start'])):null;
		if(empty($start) || !church_admin_checkdate($start)){$start = wp_date('Y-m-d',strtotime('-1 year') );}
		$end = !empty( $_POST['end'] ) ? sanitize_text_field(stripslashes($_POST['end'])):null;
		if(empty($end) || !church_admin_checkdate($end)){$start = wp_date('Y-m-d') ;}


		if(!empty( $_POST['service_id'] ) )  {
			$service_id=sanitize_text_field(stripslashes($_POST['service_id']));
		}else{
			$service_id='S/1';
		}
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/graph.php');
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
			}
		}
		elseif(!empty( $attributes['background'] ) )$out.=' ca-background ';
		$out.='">';
		$out.=church_admin_graph( $graphtype,$service_id,$start,$end,$attributes['width'],$attributes['height'],FALSE);
		$out.='</div>';
		return $out;
}
function ca_block_attendance( $attributes)
{
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
	if(is_user_logged_in()&&church_admin_level_check('Directory') )
	{
		require_once(plugin_dir_path(dirname(__FILE__) ).'includes/individual_attendance.php');
		$out.=church_admin_individual_attendance();
	}
	else
	{
		$out.='<h3>'.esc_html( __('Only logged in users with permission can use this feature','church-admin' ) ).'</h3>';
		$out.=wp_login_form(array('echo' => false) );
	}
	$out.='</div>';
	return $out;
}
function ca_block_volunteer( $attributes)
{
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
		if(is_user_logged_in() )
		{
			require_once(plugin_dir_path(dirname(__FILE__) ).'display/volunteer.php');
			$out.=church_admin_display_volunteer();
		}
		else
		{
			$out.='<h3>'.esc_html( __('Only logged in users can use this feature','church-admin' ) ).'</h3>';
			$out.=wp_login_form(array('echo' => false) );
		}
		$out.='</div>';
		return $out;
}
function ca_block_contact( $attributes)
{
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
		
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/contact.php');
		$out.=church_admin_contact_public();
		$out.='</div>';
		return $out;
}
function ca_block_birthdays( $attributes)
{
	$member_type_id=1;

    $member_type_id=implode(",",church_admin_get_member_type_ids( $attributes['member_type_id'] ) );
	$people_type_id=implode(",",church_admin_get_people_type_ids( $attributes['people_type_id'] ) );
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
	if ( empty( $attributes['loggedin'] )||is_user_logged_in() )
    {
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
        $out.=church_admin_frontend_birthdays( (int)$member_type_id,(int)$people_type_id, (int)$attributes['days'],(int)$attributes['show_age'],(int)$attributes['show_email'],(int)$attributes['show_phone'] );
    }
	else //login required
	{
		$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
	}
	$out.='</div>';
	return $out;
}
function ca_block_anniversaries( $attributes)
{
	$member_type_id=1;

    $member_type_id=implode(",",church_admin_get_member_type_ids( $attributes['member_type_id'] ) );
	$people_type_id=implode(",",church_admin_get_people_type_ids( $attributes['people_type_id'] ) );
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
	if ( empty( $attributes['loggedin'] )||is_user_logged_in() )
    {
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
        $out.=church_admin_frontend_anniversaries( (int)$member_type_id,(int)$people_type_id, (int)$attributes['days'],(int)$attributes['show_age'],(int)$attributes['show_email'],(int)$attributes['show_phone'] );
    }
	else //login required
	{
		$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
	}
	$out.='</div>';
	return $out;
}


function ca_block_series( $attributes)
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
function ca_block_member_map( $attributes)
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
    	$out.='<div class="church-admin-member-map"><script type="text/javascript">var xml_url="'.site_url().'/?ca_download=address-xml&member_type_id='.esc_html( $attributes['member_type_id'] ).'&address-xml='.wp_create_nonce('address-xml').'";';
    	$out.=' var lat='.esc_html( $service->lat).';';
    	$out.=' var lng='.esc_html( $service->lng).';';
		$out.=' var zoom='.esc_html( $attributes['zoom'] ).';';
		$out.=' var translation=["'.esc_html( __('Small Groups','church-admin' ) ).'","'.esc_html( __('Unattached','church-admin' ) ).'","'.esc_html( __('In a group','church-admin' ) ).'","'.esc_html( __('Group','church-admin' ) ).'"];';
    	$out.='jQuery(document).ready(function()  {console.log("Ready to lead");
    load(lat,lng,xml_url,zoom,translation);});</script><div id="church-admin-member-map" style="width:'.$attributes['width'].';height:'.$attributes['height'].'">Gutenberg is still a bit naff, so map will show in front end ;-)</div>';
    	$out.='<div id="groups" ><p><img src="https://maps.google.com/mapfiles/kml/paddle/blu-circle.png" />'.esc_html( __('Small Group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/red-circle.png" />'.esc_html( __('Not in a small group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/grn-circle.png" />'.esc_html( __('In a small Group','church-admin' ) ).'</p></div>';
    	$out.='</div>';
	}
	else {
		$out.='<h3>'.esc_html( __('You need to be logged in to view the map','church-admin' ) ).'</h3>'.wp_login_form(array('echo'=>false) );
	}
	$out.='</div>';
    return $out;
}

function ca_block_giving( $attributes)
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'display/giving.php');
    $premium=get_option('church_admin_payment_gateway');
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
	$out.='<div class="church-admin-giving">';
    if(!empty( $premium) )
    {
        if ( empty( $attributes) )$attributes=array('fund'=>"");
        $out.=church_admin_giving_form( $attributes['fund'] );
    }
    else
    {
        $out.='<h2>Oh dear!</h2>'.esc_html( __('The giving form is only active for app subscribers and when PayPal settings on the giving page have been filled in','church-admin' ) ).'</p>';
    }
	$out.='</div></div>';
    return $out;
}
function ca_block_pledge( $attributes)
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'display/pledge.php');
    
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
    $out.=church_admin_pledge_form();
	$out.='</div>';
	return $out;
}