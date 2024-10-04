<?php
/**
 * Widgets
 */



/*******************************************************
*
* Giving Widget
*
********************************************************/
function church_admin_giving_load_widget() {
    $premium=get_option('church_admin_payment_gateway');
    if(!empty( $premium) )register_widget( 'church_admin_giving_widget' );
}
add_action( 'widgets_init', 'church_admin_giving_load_widget' );


// Creating the widget 
class church_admin_giving_widget extends WP_Widget {
 
// The construct part  
function __construct() {
 parent::__construct(// Base ID of your widget
'church_admin_giving_widget', 
  
// Widget name will appear in UI
esc_html(__('Online giving', 'church-admin' ) ), 
  
// Widget description
array( 'description' => __( 'Paypal giving form for monthly and one-off donations', 'church-admin' ), ) 
);
}
  
// Creating widget front-end
public function widget( $args, $instance ) {
    wp_enqueue_script('church-admin-giving-form',plugins_url( '/', dirname(__FILE__) ) . 'includes/giving.js',array( 'jquery' ),FALSE, TRUE);
    $title = apply_filters( 'widget_title', $instance['title'] );
  
    // before and after widget arguments are defined by themes
    echo $args['before_widget']."\r\n";
    if ( ! empty( $title ) )
    echo $args['before_title'] . $title . $args['after_title']."\r\n";
  
    // This is where you run the code and display the output
    global $current_user,$wpdb;
    $premium=get_option('church_admin_payment_gateway');
    if(CA_PAYPAL=="https://www.sandbox.paypal.com/cgi-bin/webscr")echo'<p>SANDBOX MODE</p>';
    echo'<div class="ca-donate-form-widget">'."\r\n";
    echo'<div class="ca-tabs">'."\r\n";
    echo'<div class="ca-tab ca-active-tab" id="recurring">'.esc_html( __('Give Monthly','church-admin' ) ).'</div>'."\r\n";
    echo'<div class="ca-tab" id="once" >'.esc_html( __('Give Once','church-admin' ) ).'</div>'."\r\n";
    echo'</div><!--.ca-tabs-->'."\r\n";
    echo'<div class="ca-row">'."\r\n";
    echo'<form action="'.CA_PAYPAL.'" method="post"><input type="hidden" name="notify_url" value="'.site_url().'/wp-admin/admin-ajax.php?action=church_admin_paypal_giving_ipn" />';
    
    
    echo'<input type="hidden" name="business" value="'.$premium['paypal_email'].'">'."\r\n";
    echo'<input type="hidden" name="cmd" class="cmd" value="_xclick-subscriptions" /><input type="hidden" class="ca-recurring"  name="p3" value="1" />'."\r\n";
    echo'<input type="hidden" class="ca-recurring" name="t3" value="M" />'."\r\n";
    echo'<input type="hidden" class="ca-recurring" name="src" value="1" />'."\r\n";
    echo'<input type="hidden" name="currency_code" value="'.$premium['paypal_currency'].'" />'."\r\n";
    echo'<input type="hidden" name="charset" value="utf-8" />'."\r\n";
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Donation amount','church-admin' ) ).'*</label><input type="number" required="required" class="church-admin-form-control amount" name="a3" /></div>'."\r\n";
    if( $premium['gift_aid'] )
    {
        echo'<p><strong>Boost your donation by 25p of Gift Aid for every £1 you donate</strong>Gift Aid is reclaimed by the charity from the tax you pay for the current tax year.</p>';
        echo'<div class="church-admin-form-group"><input type="checkbox" name="custom" value="gift-aid" /> I want to Gift Aid my donation and any donations I make in the future or have made in the past 4 years to the church</div>'."\r\n";
    }
    $funds=get_option('church_admin_giving_funds');
    if(!empty( $funds) )
    {
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Fund','church-admin' ) ).'</label><select name="item_name">';
        foreach( $funds AS $key=>$fund)  {
            echo '<option value="'.esc_html( $fund).'">'.esc_html( $fund).'</option>';
        }
        echo'</select></div>'."\r\n";
    }
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Email Address','church-admin' ) ).'*</label><input type="email" required="required" class="church-admin-form-control" name="payer_email" ';
    if(!empty( $person->email) ) echo' value="'.esc_html( $person->email).'" ';
    echo'/></div>'."\r\n";
    echo'<div class="church-admin-form-group"><input type="submit" value="'.esc_html( __('Give monthly by PayPal','church-admin' ) ).'" class="ca-donate-submit" /><img alt="" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" />* '.esc_html( __('Required fields','church-admin' ) ).'</div>';
    echo'</form>'."\r\n";
    echo'</div><!-- .ca-row-->'."\r\n";
   
    echo'</div><!--.ca-donate-form-widget NEW VERSION-->'."\r\n";
   
}
 public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'Online Giving', 'church-admin' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php 
}
      
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
return $instance;
}
          
// Class church_admin_giving_widget ends here
} 




/*******************************************************
*
* Calendar Widget
*
********************************************************/


//function to register the widget
add_action( 'widgets_init', 'church_admin_calendar_register_widget' );
function church_admin_calendar_register_widget() {

 register_widget( 'church_admin_calendar_Widget' );
  
}


class church_admin_calendar_Widget extends WP_Widget {
    function __construct() {

     $widget_options = array (
      'classname' => 'church_admin_calendar_widget',
      'description' => __('Calendar events','church-admin')
     );

     parent::__construct( 'church_admin_calendar_widget', 'Church Admin Calendar', $widget_options );

    }
    function form( $instance ) 
    {
        global $wpdb;
        //defaults
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Calendar', 'church-admin' );
        $description = ! empty( $instance['description'] ) ? $instance['description'] : esc_html__( 'Church events coming soon', 'church-admin' );
        $cat_id = isset( $instance['cat_id'] ) ? $instance['cat_id'] : 0;
        $howmany = isset( $instance['howmany'] ) ? $instance['howmany'] : 5;
        $facilities_id= isset( $instance['facilities_id'] )? $instance['facilities_id']:""; 
        //form
        echo '<p> <label for="'.esc_attr( $this->get_field_id( 'title' ) ).'">'.esc_html( __('Title','church-admin' ) ).'</label><input class="widefat" type="text" id="'.esc_attr( $this->get_field_id( 'title' ) ).'" name="'.esc_attr( $this->get_field_name( 'title' ) ).'" value="'.$title.'" /></p>';
        
        echo '<p> <label for="'.esc_attr( $this->get_field_id( 'description' ) ).'">'.esc_html( __('Description','church-admin' ) ).'</label><textarea class="widefat" id="'.esc_attr( $this->get_field_id( 'desciption' ) ).'"  name="'.esc_attr( $this->get_field_name( 'description' ) ).'">'.esc_textarea( $description ).'</textarea></p>';
        echo '<p> <label for="'.esc_attr( $this->get_field_id( 'howmany' ) ).'">'.esc_html( __('How many','church-admin' ) ).'</label><input class="widefat" type="text" id="'.esc_attr( $this->get_field_id( 'howmany' ) ).'" name="'.esc_attr( $this->get_field_name( 'howmany' ) ).'" value="'.(int)$howmany.'" /></p>';
        echo '<p> <label for="'.esc_attr( $this->get_field_id( 'facilities_id' ) ).'">'.esc_html( __('Facility ID (leave blank if not required)','church-admin' ) ).'</label><input class="widefat" type="text" id="'.esc_attr( $this->get_field_id( 'howmany' ) ).'" name="'.esc_attr( $this->get_field_name( 'facilities_id' ) ).'" value="'.(int)$facilities_id.'" /></p>';
        
        echo'<p><label for="'.esc_attr( $this->get_field_id( 'cat_id' ) ).'">'.esc_html( __('Select a Category','church-admin' ) ).'</label>';
        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_category';
        if ( empty( $instance['cat_id'] ) )$instance['cat_id']=0;
        $results=$wpdb->get_results( $sql );
        echo'<select id="'.esc_attr( $this->get_field_id( 'title' ) ).'" name="'.esc_attr( $this->get_field_name( 'cat_id' ) ).'">';
        echo'<option value="0">'.esc_html( __('All events','church-admin' ) ).'</option>';
        foreach( $results AS $row)
        {
            echo'<option value="'.esc_html( $row->cat_id).'" '.selected( $row->cat_id,$cat_id,false).'>'.esc_html( $row->category).'</option>';
        }
        echo'</select></p>';
        
    }
    
    //function to define the data saved by the widget

  
    function update( $new_instance, $old_instance ) 
    {
        
        $expected=array('title','description','howmany','cat_id');
        
        $instance=array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['description'] = ( ! empty( $new_instance['description'] ) ) ? sanitize_text_field( $new_instance['description'] ) : '';
        $instance['howmany'] = (int)$new_instance['howmany'] ;
        $instance['cat_id'] = (int)$new_instance['cat_id'] ;
        $instance['facilities_id']=(int)$new_instance['facilities_id'];
        return $instance;          

    }         

    
    //function to display the widget in the site

    function widget( $args, $instance ) {
         global $wpdb;
        
       
        $title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : __('Calendar','church-admin');
        $description = ! empty( $instance['description'] ) ? $instance['description'] : esc_html__( 'Church events coming soon', 'church-admin' );
        $cat_id = isset( $instance['cat_id'] ) ? intval( $instance['cat_id'] ) : 0;
        $howmany = isset( $instance['howmany'] ) ? intval( $instance['howmany'] ) : 5;
        $facilities_id= isset( $instance['facilities_id'] )? $instance['facilities_id']:""; 
        //output code
        echo $args['before_widget'];
        if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
        //Calendar output
        if( $cat_id!=0)  {$cat='a.cat_id="'.$cat_id.'" AND ';} else {$cat='';}
        if(!empty( $facilities_id) )  {$fac='a.facilities_id="'.(int)$facilities_id.'" AND';}else{$fac='';}
        $sql='SELECT a.*,b.category FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_calendar_category b WHERE '.$fac.$cat.' a.cat_id=b.cat_id AND a.start_date>="'.date('Y-m-d').'" AND a.general_calendar=1 ORDER by a.start_date,a.start_time LIMIT '.$howmany;
        
        $result=$wpdb->get_results( $sql);
        if(!empty( $result) )
        {

            $old_date='';
            foreach( $result AS $row)
            {

                $date=mysql2date(get_option('date_format'),$row->start_date);

                if( $old_date!=$date)  {echo'<h3>'.$date.'</h3>';}
                echo'<p><strong>'.esc_html(mysql2date(get_option('time_format'),$row->start_time) ).' - '.esc_html( $row->title).'</strong></br/>';
                if(!empty( $row->location) )echo esc_html( $row->location).'<br>';
                if(!empty( $options['description'] )&&!empty( $row->description) )echo esc_html( $row->description).'<br>';
                if(!empty( $row->link) )echo '<div class="ca-day-link"><a href="'.esc_url( $row->link).'">'.esc_html( $row->link_title).'</a></div>';
                echo '</p>';
                $old_date=$date;
            }

            unset( $date,$thisday,$class);

        }//end of non empty result
        else echo __('No calendar events','church-admin');
        echo $args['after_widget'];

    }
}


/*******************************************************
*
* Sermon Podcast Widget
*
********************************************************/


//function to register the widget
add_action( 'widgets_init', 'church_admin_podcast_register_widget' );
function church_admin_podcast_register_widget() {
    $ajax_nonce = wp_create_nonce("church_admin_mp3_play");
	
	wp_enqueue_script('ca_podcast_audio_use');//,plugins_url('church-admin/includes/audio.use.js',dirname(__FILE__) ),'',NULL);
	wp_localize_script( 'ca_podcast_audio_use', 'ChurchAdminAjax', array('security'=>$ajax_nonce, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

 register_widget( 'church_admin_podcast_Widget' );
  
}


class church_admin_podcast_Widget extends WP_Widget {
    function __construct() {

     $widget_options = array (
      'classname' => 'church_admin_podcast_widget',
      'description' => __('Sermon Podcast','church-admin')
     );

     parent::__construct( 'church_admin_podcast_widget', 'Church Admin Podcast', $widget_options );

    }
    function form( $instance ) 
    {
        global $wpdb;
        //defaults
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Sermons', 'church-admin' );
        $description = ! empty( $instance['description'] ) ? $instance['description'] : esc_html__( 'Sermon podcasts', 'church-admin' );
        $howmany = isset( $instance['howmany'] ) ? $instance['howmany'] : 5;
        //form
        echo '<p> <label for="'.esc_attr( $this->get_field_id( 'title' ) ).'">'.esc_html( __('Title','church-admin' ) ).'</label><input class="widefat" type="text" id="'.esc_attr( $this->get_field_id( 'title' ) ).'" name="'.esc_attr( $this->get_field_name( 'title' ) ).'" value="'.$title.'" /></p>';
        
        
        echo '<p> <label for="'.esc_attr( $this->get_field_id( 'howmany' ) ).'">'.esc_html( __('How many','church-admin' ) ).'</label><input class="widefat" type="text" id="'.esc_attr( $this->get_field_id( 'howmany' ) ).'" name="'.esc_attr( $this->get_field_name( 'howmany' ) ).'" value="'.$howmany.'" /></p>';
       
        
    }
    
    //function to define the data saved by the widget

  
    function update( $new_instance, $old_instance ) 
    {
        
        $expected=array('title','description','howmany','cat_id');
        
        $instance=array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['description'] = ( ! empty( $new_instance['description'] ) ) ? sanitize_text_field( $new_instance['description'] ) : '';
        $instance['howmany'] = intval( $new_instance['howmany'] );
       
        return $instance;          

    }         

    
    //function to display the widget in the site

    function widget( $args, $instance ) {
        global $wpdb;
        $upload_dir = wp_upload_dir();
        $path=$upload_dir['basedir'].'/sermons/';
        $url=$upload_dir['baseurl'].'/sermons/';
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
        
       
        $title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : __('Calendar','church-admin');
        $description = ! empty( $instance['description'] ) ? $instance['description'] : esc_html__( 'Church events coming soon', 'church-admin' );
       
        $howmany = isset( $instance['howmany'] ) ? intval( $instance['howmany'] ) : 5;
        //output code
        echo $args['before_widget'];
        if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
        
        $ca_podcast_settings=get_option('ca_podcast_settings');

	   if(!empty( $ca_podcast_settings['itunes_link'] ) ){
            echo'<p><a title="Download on Itunes" href="'.$ca_podcast_settings['itunes_link'].'"><img  alt="badge_itunes-lrg" src="'.plugins_url('/images/badge_itunes-lrg.png',dirname(__FILE__) ).'" width="110" height="40" /></a></p>';
       }
        $sermons=$wpdb->get_results('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a, '.$wpdb->prefix.'church_admin_sermon_series b WHERE a.series_id=b.series_id ORDER BY a.pub_date DESC LIMIT '.$howmany);
        if(!empty( $sermons) )
        {
            foreach( $sermons AS $data)
            {
                $speaker=church_admin_get_people( $data->speaker);
                if(!empty( $data->file_title) )echo'<h3>'.esc_html( $data->file_title).'</h3>';
                    if(!empty( $data->video_url) )
                    {
                        if(strpos( $data->video_url, 'amazonaws.com/') !== false)
                        {
                           echo'<video class="ca-video" width="560" height="315" controls><source src="'.$data->video_url.'" type="video/mp4">Your browser does not support the video tag.
    </video>'; 
                        }else
                        {
                            $video=church_admin_generateVideoEmbedUrl( $data->video_url);
                            $videoUrl=$video['embed'];
                            echo'<iframe class="ca-video" width="560" height="315" src="'.esc_url( $videoUrl).'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                        }
                    }

                    if(!empty( $data->file_name)&& file_exists( $path.$data->file_name) )
                    {
                        echo'<p><audio class="sermonmp3" data-id="'.esc_html( $data->file_id).'" src="'.esc_url( $url.$data->file_name).'" preload="auto" controls></audio></p>';
                        $download='<a href="'.esc_url( $url.$data->file_name).'" class="mp3download" data-id="'.(int)$data->file_id.'" title="'.esc_html( $data->file_title).'" download>'.esc_html( $data->file_title).'</a>';
                    }
                    elseif(!empty( $data->external_file) )
                    {
                            echo'<p><audio class="sermonmp3" data-id="'.esc_html( $data->file_id).'" src="'.esc_url( $data->external_file).'" preload="auto" controls></audio></p>';

                            $download='<a href="'.esc_url( $data->external_file).'" class="mp3download" data-id="'.(int)$data->file_id.'" title="'.esc_html( $data->file_title).'" download>'.esc_html( $data->file_title).'</a>';
                    }
            }
            echo'<script>var mp3nonce="'.wp_create_nonce("church_admin_mp3_play").';"</script>';
        }

        echo $args['after_widget'];

    }
}


/******************************************************************************************************
*
* Use prayer request recent posts in recent posts widget when on prayer request/bible readings Archive
*
*****************************************************************************************************/

add_filter( 'widget_posts_args', 'church_admin_recent_posts_args');
add_filter('widget_comments_args', 'church_admin_recent_posts_args');
/**
 * Add CPTs to recent posts widget
 *
 * @param array $args default widget args.
 * @return array $args filtered args.
 */
function church_admin_recent_posts_args( $args) {
   if(is_post_type_archive('prayer-requests') ) $args['post_type'] = array('prayer-requests');
	 elseif(is_post_type_archive('bile-readings') ) $args['post_type'] = array('bible-readings');
	 else {
	 $args['post_type'] = array('post');
	 }
    return $args;
}

/*******************************************************
*
* Prayer Request Widget
*
********************************************************/
// Register and load the widget
function church_admin_load_prayer_widget() {
    register_widget( 'ca_prayer_widget' );
}
add_action( 'widgets_init', 'church_admin_load_prayer_widget' );

// Creating the widget
class ca_prayer_widget extends WP_Widget {

        function __construct() {
        parent::__construct(

        // Base ID of your widget
        'ca_prayer_widget',

        // Widget name will appear in UI
        esc_html(__('Submit Prayer Request Widget', 'church-admin' ) ),

        // Widget description
        array( 'description' => esc_html(__( 'Prayer Request widget', 'church-admin' )), )
        );
        }

        // Creating widget front-end

        public function widget( $args, $instance ) {
            if ( empty( $ins) )
        $title =__('Submit prayer Request','church-admin');

        // before and after widget arguments are defined by themes
        echo $args['before_widget'].'<div class="widget-white-container">';
        if ( ! empty( $title ) )
        echo $args['before_title'] . $title . $args['after_title'];

        // This is where you run the code and display the output

        if(!empty( $_POST['non_spammer'] ) )
        {
            echo'<p>'.esc_html( __('Prayer request saved for moderation','church-admin' ) ).'</p>';
        }
        else {
            $message=get_option('church_admin_prayer_request_message');
            if(!empty( $message) )echo'<p>'. esc_html( $message).'</p>';
            echo'<form action="" method="POST">';
           
            echo'<div class="church-admin-form-group"><label>'.esc_html( __('Title','church-admin' ) ).'</label><input type="text" class="church-admin-form-control" name="request_title"></div>';
            echo'<div class="church-admin-form-group"><label>'.esc_html( __('Prayer request','church-admin' ) ).'</label><textarea  class="church-admin-form-control"  name="request_content"></textarea></div>';
            echo'<div class="widget-spam-proof">&nbsp;</div>';
            echo'<div><input type="hidden" value="TRUE" name="save_prayer_request" /><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></div>';

            echo'</form>';
            $nonce=wp_create_nonce('prayer-request');
            echo'<script>jQuery(document).ready(function( $) {var content="<div class=\"church-admin-form-group\"><label>'.esc_html( __('Check box if not a spammer','church-admin' ) ).'</label><input type=\"checkbox\" class=\"church-admin-form-control\" name=\"non_spammer\" value=\"'.$nonce.'\" /></td></tr>"; $(".widget-spam-proof").html(content);});</script>';
        }
        echo '</div>'.$args['after_widget'];
        }

} // Class wpb_widget ends here

