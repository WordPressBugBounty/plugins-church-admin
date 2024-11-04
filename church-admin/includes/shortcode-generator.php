<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_shortcode_generator()
{
    global $wpdb,$wp_locale;
    $out='';
    $out.='<h2>'.esc_html( __('Church Admin Shortcode Generator','church-admin' ) ).'</h2>'."\r\n";
    $classes=church_admin_classes_array();
    $facilities=church_admin_facilities_array();
    $series=church_admin_sermon_series_array();
    $sermons=church_admin_sermon_sermons_array();
   
	$member_types=church_admin_member_types_array();
    $people_types=get_option('church_admin_people_type');
    $map_api_key=get_option('church_admin_google_api_key');
    $categories=church_admin_calendar_categories_array();
   
    $exclude=array('seriesName'=>esc_html( __('Series name','church-admin' ) ),
                    'seriesDescription'=>esc_html( __('Series description','church-admin' ) ),
                    'seriesImage'=>esc_html( __('Series image','church-admin' ) ),
                    'subtitle'=>esc_html( __('Subtitle','church-admin' ) ),
                    'download'=>esc_html( __('Download link','church-admin' ) ),
                    'fileDescription'=>esc_html( __('File description','church-admin' ) ),
                    'speaker'=>esc_html( __('Speaker','church-admin' ) ),
                    'date'=>esc_html( __('Date','church-admin' ) ),
                    'views'=>esc_html( __('Views','church-admin' ) ),
                    'bible'=>esc_html( __('Bible','church-admin' ) ),
                    'sharing'=>esc_html( __('Sharing','church-admin'))
                );  
    $shortcodes=array(
        'address-list'=>array('title'=>esc_html( __('Address list','church-admin' ) ),
                               'description'=>esc_html( __('Displays the address list, with various options','church-admin' ) ),
                               'options'=>array('loggedin','member_type_id','people_type_id','photos','pdf_link','kids','updateable','vcf','map' ),
                               'type'=>'no-content'
                        ),
        'anniversaries'=>array('title'=>esc_html( __(' Anniversaries','church-admin' ) ),
                'description'=>esc_html( __('Show anniversaries for different categories, with option to show age and how many days ahead to show.','church-admin' ) ),
                'options'=>array('loggedin','member_type_id','people_type_id','days','show_age','show_phone','show_email'),
                'type'=>'no-content'
        ),
        'attendance' =>array('title'=>esc_html( __('Attendance','church-admin' ) ),
                'description'=>esc_html( __('Form to log attendance','church-admin' ) ),
                'options'=>NULL,
                'type'=>'no-content'
        ),
        
        'birthdays'=>array('title'=>esc_html( __(' Birthdays','church-admin' ) ),
                'description'=>esc_html( __('Show birthdays for different categories, with option to show age and how many days ahead to show.','church-admin' ) ),
                'options'=>array('loggedin','member_type_id','people_type_id','days','show_age','show_phone','show_email'),
                'type'=>'no-content'
        ),
        'calendar' =>array('title'=>esc_html( __('Calendar','church-admin' ) ),
                    'description'=>esc_html( __('Displays the calendar','church-admin' ) ),
                    'options'=>array('calendar-pdf','facilities_id','cat_id','style'),
                    'type'=>'no-content'
        ),
        'calendar-table' =>array('title'=>esc_html( __('Calendar - table style','church-admin' ) ),
                    'description'=>esc_html( __('Displays the calendar in a table','church-admin' ) ),
                    'options'=>array('calendar-pdf','facilities_id','cat_id'),
                    'type'=>'no-content'
        ),
        'calendar-list' =>array('title'=>esc_html( __('Calendar - list style','church-admin' ) ),
                'description'=>esc_html( __('Displays list style calendar','church-admin' ) ),
                'options'=>array('days','cat_id'),
                'type'=>'no-content'
        ),
        
        'contact-form' =>array('title'=>esc_html( __('Contact form','church-admin' ) ),
                'description'=>esc_html( __('Displays the contact form','church-admin' ) ),
                'options'=>array(),
                'type'=>'no-content'
        ),
        
        
        'hello' =>array('title'=>esc_html( __('Hello user','church-admin' ) ),
                'description'=>esc_html( __('Displays a welcome to a logged in user','church-admin' ) ),
                'options'=>null,
                'type'=>'no-content'
        ),
        'latest-sermon' =>array('title'=>esc_html( __('Latest Sermon','church-admin' ) ),
                'description'=>esc_html( __('Displays the newest sermon','church-admin' ) ),
                'options'=>null,
                'type'=>'no-content'
        ),
        'logged-in' =>array('title'=>esc_html( __('Logged in','church-admin' ) ),
                'description'=>esc_html( __('Displays content only to logged in website visitors','church-admin' ) ),
                'options'=>NULL,
                'type'=>'content'
        ),
        'mailing-list'=>array('title'=>esc_html( __('Mailing list','church-admin' ) ),
                'description'=>esc_html( __('Simple mailing list form  to allow people to add themselves to the church directory for email list.','church-admin' ) ),
                'options'=>array('save_as_member_type_id'),
                'type'=>'no-content'
        ),              
       
     
       
        'names' =>array('title'=>esc_html( __('Names','church-admin' ) ),
                'description'=>esc_html( __('Displays a list of names','church-admin' ) ),
                'options'=>array('member_type_id','people_type_id'),
                'type'=>'no-content'
        ),

        'not-logged-in' =>array('title'=>esc_html( __('Not logged in','church-admin' ) ),
                'description'=>esc_html( __('Displays content to not logged in website visitors','church-admin' ) ),
                'options'=>array('login_form'),
                'type'=>'content'
        ),
        'player' =>array('title'=>esc_html( __('Sermon audio player','church-admin' ) ),
                'description'=>esc_html( __('Display single sermon audio player','church-admin' ) ),
                'options'=>array('file_id'),
                'type'=>'no-content'
        ),
        
        'phone-list' =>array('title'=>esc_html( __('Phone list','church-admin' ) ),
                'description'=>esc_html( __('Phone list','church-admin' ) ),
                'options'=>array('people_type_id','member_type_id'),
                'type'=>'no-content'
        ),
        'pledges' =>array('title'=>esc_html( __('Pledge form','church-admin' ) ),
            'description'=>esc_html( __('Shows pledge form (Premium only)','church-admin' ) ),
            'options'=>array(),
            'type'=>'no-content'
        ),
        
        'recent' =>array('title'=>esc_html( __('Recent people activity','church-admin' ) ),
                'description'=>esc_html( __('People edits','church-admin' ) ),
                'options'=>array('weeks','member_type_id'),
                'type'=>'no-content'
        ),
        
        'register' => array('title'=>esc_html( __('Register','church-admin' ) ),
                'description'=>esc_html( __('Registration form','church-admin' ) ),
                'options'=>array('allow_registrations','save_as_member_type_id','allow','people_exclude','onboarding','full_privacy_show','admin_email'),
                'type'=>'no-content'
        ),
        'restricted'=>array('title'=>esc_html( __('Restricts content to certain member types','church-admin' ) ),
                'description'=>esc_html( __('Only displays content to certain member types','church-admin' ) ),
                'options'=>array('member_type_id'),
                'type'=>'content'
        ),
        
        'sermons' =>array('title'=>esc_html( __('Sermons (new style)','church-admin' ) ),
                'description'=>esc_html( __('New style sermons display','church-admin' ) ),
                'options'=>array('how_many','nowhite','start_date','rolling'),
                'type'=>'no-content'
        ),
       
        'podcast' =>array('title'=>esc_html( __('Sermons (old style)','church-admin' ) ),
                'description'=>esc_html( __('Displays all the sermons','church-admin' ) ),
                'options'=>array('exclude'),
                'type'=>'no-content'
        ),
        'sessions' =>array('title'=>esc_html( __('Sessions','church-admin' ) ),
                'description'=>esc_html( __('Tracking small group activity','church-admin' ) ),
                'options'=>NULL,
                'type'=>'no-content'
        ),
        'single-sermon' =>array('title'=>esc_html( __('Single Sermon','church-admin' ) ),
                'description'=>esc_html( __('Display single sermon','church-admin' ) ),
                'options'=>array('file_id'),
                'type'=>'no-content'
        ),
        
        
       
        
        'video' =>array('title'=>esc_html( __('Video embed','church-admin' ) ),
                        'description'=>esc_html( __('Video embed with aspect ratio','church-admin' ) ),
                        'options'=>array('video_url'),
                        'type'=>'no-content'
                ),
        'volunteer' =>array('title'=>esc_html( __('Volunteer','church-admin' ) ),
                        'description'=>esc_html( __('Form to apply to serve in a ministry','church-admin' ) ),
                        'options'=>NULL,
                        'type'=>'no-content'
                ),
        
        
        
        

        
        

    );
    if(!empty($_POST['create-shortcode']))
    {
      
        $shortcode=sanitize_text_field(stripslashes($_POST['shortcode']));
        //validate
        if(empty($shortcodes[$shortcode])){
            $out.='<div class="church-admin-form-groupnotice notice-danger"><h2>'.esc_html(__('Invalid shortcode choice','church-admin' ) ).'</h2>';
            $out.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=shortcode-generator','shortcode-generator').'">'.esc_html( __('Try again','church-admin' ) ).'<p>';
            $out.='</div>';
        }
        $expected_options=$shortcodes[$shortcode]['options'];
        if(empty($expected_options)){
            //simple shortcode
            $shortcode_output='[church_admin type="'.esc_attr($shortcode).'"]';
        }else{
            //build options
            $options=array();
            foreach($expected_options AS $key=>$option){
                
                if(isset($_POST[$option]))
                {
                    $postedoption = church_admin_sanitize($_POST[$option]);
                    switch( $option ){
                        
                        case 'exclude':
                            $exc=array();
                            
                            foreach( $postedoption  AS $key=>$value){
                                
                                    $exc[]=$value;
                                
                            }
                            sort($exc);
                            $options[]='exclude="'.esc_attr(implode(',',$exc)).'"';

                        break;
                        case 'people_exclude':
                            $exc=array();
                            
                            foreach( $postedoption  AS $key=>$value){
                                
                                    $exc[]=$value;
                                
                            }
                            sort($exc);
                            $options[]='exclude="'.esc_attr(implode(',',$exc)).'"';

                        break;
                        case 'allow':
                            $exc=array();
                            
                            foreach( $postedoption  AS $key=>$value){
                                
                                    $exc[]=$value;
                                
                            }
                            sort($exc);
                            $options[]='allow="'.esc_attr(implode(',',$exc)).'"';

                        break;
                        case 'member_type_id':
                            
                            if(is_array($postedoption ) && in_array("#",$postedoption )){
                                $options[]='member_type_id="#"';
                            }
                            else
                            {
                                $mts=array();
                                foreach($postedoption AS $key=>$id){
                                    if(!empty($member_types) & !empty($member_types[$id])){
                                        $out.='adding '.$member_types[church_admin_sanitize($id)].'<br/>';
                                        $mts[]=(int)$id;
                                    }
                                }
                                sort($mts);
                                $options[]='member_type_id="'.esc_attr(implode(',',$mts)).'"';
                            }
                        break;
                        case 'ministry_id':
                            $mins = array();
                            $value=!empty($postedoption )? $postedoption:'';
                            $options[]='ministry_id="'.(int)$value.'"';
                        break;
                        case 'save_as_member_type_id':
                            $value=!empty($postedoption )? $postedoption:1;
                            $options[]='member_type_id="'.(int)$value.'"';
                            
                        break; 

                        case 'cat_id':
                            
                            if(is_array($postedoption ) && in_array("#",$postedoption )){
                                $options[]=' category="" ';
                            }
                            else
                            {
                                $cats=array();
                                
                                foreach($postedoption  AS $key=>$id){
                                    if(!empty($categories) & !empty($categories[$id])){
                                        $out.='adding '.$categories[$id].'<br/>';
                                        $cats[]=(int)$id;
                                    }
                                }
                                sort($cats);
                                $options[]='category="'.esc_attr(implode(',',$cats)).'"';
                            }
                        break;    
                        case 'people_type_id':
                            if(is_array($postedoption ) && in_array("#",$postedoption )){
                                $options[]='people_type_id="#"';
                            }
                            else
                            {
                                $pts=array();
                                foreach($postedoption  AS $key=>$id){
                                    if(!empty($people_types) & !empty($people_types[$id])){
                                        $pts[]=(int)$id;
                                    }
                                }
                                sort($pts);
                                $options[]='people_type_id="'.esc_attr( implode( ',', $pts ) ).'"';
                            }
                        break;
                        case'how_many':
                            $value=!empty($postedoption )? $postedoption:9;
                            $options[]='how_many="'.(int)$value.'"';
                        break;
                        case'rolling':
                            $value=!empty($postedoption )? $postedoption:9;
                            $options[]='rolling="'.(int)$value.'"';
                        break;
                        case'nowhite':
                            $value=!empty($postedoption )?1:0;
                            $options[]='nowhite="'.$value.'"';
                        break;
                        case'login_form':
                            $value=!empty($postedoption )?1:0;
                            $options[]='login_form="'.$value.'"';
                        break;
                        case'onboarding':
                            $value=!empty($postedoption )?1:0;
                            $options[]='onboarding="'.$value.'"';
                        break;
                        case'full_privacy_show':
                            $value=!empty($postedoption )?1:0;
                            $options[]='full_privacy_show="'.$value.'"';
                        break;
                        case'allow_registrations':
                            $value=!empty($postedoption )?1:0;
                            $options[]='allow_registrations="'.$value.'"';
                        break;
                        case'admin_email':
                            $value=!empty($postedoption )?1:0;
                            $options[]='admin_email="'.$value.'"';
                        break;
                        case'show_email':
                            $value=!empty($postedoption )?1:0;
                            $options[]='show_email="'.$value.'"';
                        break;
                        case'show_phone':
                            $value=!empty($postedoption )?1:0;
                            $options[]='show_phone="'.$value.'"';
                        break;
                        case 'loggedin':
                            $value=!empty($postedoption )?1:0;
                            $options[]='loggedin="'.$value.'"';
                        break;
                        case 'start_date':
                            $value=!empty($postedoption ) && church_admin_checkdate($postedoption )?$postedoption :'';
                            $options[]='start_date="'.$value.'"';
                        case 'pdf':
                            switch($postedoption ){
                                case '1':
                                default:
                                    $options[]='pdf=1';
                                break;
                                case '2':
                                    $options[]='pdf=2';
                                break;
                                case 'multi':
                                    $options[]='pdf="multi"';
                                break;
                            }
                        break;
                        case 'cal-pdf':
                            if(!empty($postedoption )){
                                $options[]='pdf=1';
                            }
                        break;
                        case 'days':
                            $options[]='days="'.(int)$postedoption .'"';
                        break;
                        case 'weeks':
                            $options[]='days="'.(int)$postedoption .'"';
                        break;
                        case 'title':
                            $options[]='title="'.esc_attr( $postedoption  ).'"';
                        break;
                        case 'video_url':
                            $options[]='url="'.esc_url( $postedoption  ).'"';
                        break;
                        case'photos':
                            $value=!empty($postedoption )?1:0;
                            $options[]='photo="'.$value.'"';
                        break;
                        case'updateable':
                            $value=!empty($postedoption )?1:0;
                            $options[]='updateable="'.$value.'"';
                        break;
                        case 'map':
                            $value=!empty($postedoption )?1:0;
                            $options[]='map="'.$value.'"';
                        break;
                        case 'initials':
                            $value=!empty($postedoption )?1:0;
                            $options[]='initials="'.$value.'"';
                        break;
                        case 'vcf':
                            $value=!empty($postedoption )?1:0;
                            $options[]='vcf="'.$value.'"';
                        break;
                        case 'groups':
                            $gps=array();
                            foreach($postedoption  AS $key=>$id){
                                if(!empty($groups) & !empty($groups[$id])){
                                    $gps[]=(int)$id;
                                }
                            }
                            sort($gps);
                            $options[]='groups="'.esc_attr( implode( ',', $gps ) ).'"';
                        break;
                        case 'zoom':
                            if(!empty($postedoption )){
                                $options[]='zoom="'.(int)$postedoption .'"';
                            }
                        break;
                        case 'event':
                            if(!empty($postedoption )){
                                $options[]='event_id="'.(int)$postedoption .'"';
                            }
                        break;
                        case 'sites':
                            $sts=array();
                            foreach($postedoption  AS $key=>$id){
                                if(!empty($sites) & !empty($sites[$id])){
                                    $sts[]=(int)$id;
                                }
                            }
                            sort($sts);
                            $options[]='site_id="'.esc_attr( implode( ',', $sts ) ).'"';
                        break;
                        case 'services':
                            if(!empty($postedoption )&&!empty($services[$postedoption ])){
                                $options='service_id="'.(int)$postedoption .'"';
                            }
                        break;
                        case 'series':
                            if(!empty($postedoption )&&!empty($series[$postedoption ])){
                                $options='series_id="'.(int)$postedoption .'"';
                            }
                        break;
                        case 'file_id':
                            if(!empty($postedoption )){
                                $options[]='file_id="'.(int)$postedoption .'"';
                            }
                        break;
                        case 'facilities_id':
                            
                                $fac_id=array();
                                foreach($postedoption AS $key=>$fac_id){
                                   if(!empty($fac_id)){
                                    $fac_ids[]=(int)$fac_id;
                                   } 
                                }
                                $options[]='facilities_id="'.implode(',',$fac_ids) .'"';
                            
                        break;

                    }
                }
                

            }
           
            $shortcode_output='[church_admin type="'.esc_attr($shortcode).'" '.implode(' ',$options).']';
        }
        if( $shortcodes[$shortcode]['type'] == 'content' ){
            $shortcode_output.=' '.esc_html( __('Your Content','church-admin' ) ).' [/church_admin]';
        }
        $out.='<p>'.esc_html( __('Your shortcode with options...','church-admin' ) ).'</p>';
        $out.='<p id="shortcodeoutput">'.esc_html( $shortcode_output ).'</p>';
        $out.='<p><button onclick="copyToClipboard()">Copy to clipboard</button></p>';
        $out.='<script>
        function copyToClipboard(){
           
                navigator.clipboard.writeText(document.getElementById("shortcodeoutput").textContent).then(() => {
                    console.log("Copied to clipboard!!!");
                });
            
        }</script>';
        $out.='<p><a class="church-admin-form-groupbutton-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=shortcode-generator','shortcode-generator').'">'.esc_html( __( 'Create another shortcode','church-admin')).'</a></p>';
    }
    else{
        $js=$description='';
        //translators: %1$s is a number
        $out.='<p><strong>'.esc_html( sprintf( __( 'There are %1$s shortcodes to choose from. Some will display a list of various optional options.','church-admin' ) ,count($shortcodes))).'</strong></p>'."\r\n";
        $out.='<form action="" method="POST">'."\r\n";
        $out.='<div ><label>'.esc_html( __('Choose shortcode','church-admin' ) ).'</label>'."\r\n";
        $out.='<select name="shortcode" id="shortcode">';
        foreach( $shortcodes AS $shortcode=>$details ){
            $out.='<option value="'.esc_attr( $shortcode ).'">'.esc_html( $details['title'] ).'</option>';
            $js.='case "'.$shortcode.'":'."\r\n";
            $js.='$("#'.$shortcode.'-description").show();';
            $js.='console.log("Show options for '.$shortcode.'")'."\r\n";
            //church_admin_debug($shortcode);
            //church_admin_debug($details['options']);
            if(!empty($details['options'])){
                foreach($details['options']AS $key=>$option){
                    $js.='$("#'.esc_html($option).'").show();'."\r\n";
                }
            }
            $js.='break;'."\r\n";

            $description.='<div id="'.$shortcode.'-description" class="shortcode-description">'.esc_html($details['description']).'</div>';
        }

        $out.='</select></div>'."\r\n";
        $out.='<h3>'.esc_html( __('This shortcode...','church-admin' ) ).'</h3>';
        $out.= $description;
       
        
        $out.='<script>        
        jQuery(document).ready(function($){
            $(".shortcode-option").hide();
            $(".shortcode-description").hide();
            //form fields for address list - shown initially
            $("#address-list-description").show();
            $("#loggedin").show();
            $("#member_type_id").show();
            $("#people_type_id").show();
            $("#photos").show();
            $("#pdf_link").show();
            $("#kids").show();
            $("#updateable").show();
            $("#vcf").show();
            $("#shortcode").change(function(){
                $(".shortcode-option").hide();
                $(".shortcode-description").hide();
                var which = $("#shortcode option:selected").val();
                
                console.log ("Shortcode selected "+which);
                switch(which)
                {
                    '.$js.'
                }
            });

        });
        
        </script>'."\r\n";
        //exclude
        $out.='<div class="church-admin-form-group shortcode-option" id="start_date" ><label>'.esc_html( __('Start date','church-admin' ) ).'</label>';
        
            $out.='<input type="date"  name="start_date" class="church-admin-form-control">'."\r\n";
       
        $out.='</div>'."\r\n";
        //exclude
        $out.='<div class="church-admin-form-group shortcode-option" id="exclude" ><h3>'.esc_html( __('Items to exclude','church-admin' ) ).'</h3>';
        foreach( $exclude AS $value=>$name ){
            $out.='<p><input type="checkbox"  name="exclude[]" value="'.esc_html($value).'" ><label> '.esc_html( $name ).'</label></p>'."\r\n";
        }
        $out.='</div>'."\r\n";
        //allow
        $out.='<div class="church-admin-form-group shortcode-option" id="allow" ><h3>'.esc_html( __("Show",'church-admin' ) ).'</h3>';
        
        $out.='<p><input type="checkbox"  name="allow[]" value="ministries" ><label> '.esc_html( __('Ministries','church-admin')).'</label></p>'."\r\n";
        $out.='<p><input type="checkbox"  name="allow[]" value="groups" ><label> '.esc_html( __('Small groups','church-admin')).'</label></p>'."\r\n";
        $out.='<p><input type="checkbox"  name="allow[]" value="sites" ><label> '.esc_html( __('Sites','church-admin')).'</label></p>'."\r\n";
        $out.='</div>'."\r\n";
    

        //peopleexclude
        $out.='<div class="church-admin-form-group shortcode-option" id="people_exclude" ><h3>'.esc_html( __("Don't show",'church-admin' ) ).'</h3>';
        
        $out.='<p><input type="checkbox"  name="people_exclude[]" value="prefix" ><label> '.esc_html( __('Prefix field for "van", "van der" etc','church-admin')).'</label></p>'."\r\n";
        $out.='<p><input type="checkbox"  name="people_exclude[]" value="date_of_birth" ><label> '.esc_html( __('Date of birth form field','church-admin')).'</label></p>'."\r\n";
        $out.='<p><input type="checkbox"  name="people_exclude[]" value="gender" ><label> '.esc_html( __('Gender','church-admin')).'</label></p>'."\r\n";
     
        $out.='<p><input type="checkbox"  name="people_exclude[]" value="custom_fields" ><label> '.esc_html( __('Custom form fields','church-admin')).'</label></p>'."\r\n";

        $out.='</div>'."\r\n";
        //login
        $out.='<div class="church-admin-form-group shortcode-option" id="loggedin" ><h3>'.esc_html( __('Login required','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="loggedin"  checked="checked" value="1" > '.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="loggedin"  value="0" /><strong> '.esc_html( __('No - NOT recommended for privacy reasons!','church-admin' ) ).'</strong></p>'."\r\n";
        $out.='</div>'."\r\n";
        //nowhite
        $out.='<div class="church-admin-form-group shortcode-option" id="nowhite" ><h3>'.esc_html( __('No white space above video (for themes that auto adjust video aspect ratio)','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="nowhite"  value="1" /> '.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="nowhite" checked="checked"  value="0" /><strong> '.esc_html( __('No ','church-admin' ) ).'</strong></p>';
        
        $out.='</div>'."\r\n";
        //allow registrations
        $out.='<div class="church-admin-form-group shortcode-option" id="allow-new" ><h3>'.esc_html( __('Allow new registrations','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="nowhite"  value="1" /> '.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="nowhite" checked="checked"  value="0" /><strong> '.esc_html( __('No ','church-admin' ) ).'</strong></p>';
        $out.='</div>'."\r\n";
        //onboarding
        $out.='<div class="church-admin-form-group shortcode-option" id="onboarding" ><h3>'.esc_html( __('Show only onboarding custom fields on first registration','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="onboarding"  value="1" /> '.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="onboarding" checked="checked"  value="0" /><strong> '.esc_html( __('No ','church-admin' ) ).'</strong></p>';
        
        $out.='</div>'."\r\n";
        //full privacy show
        $out.='<div class="church-admin-form-group shortcode-option" id="full_privacy_show" ><h3>'.esc_html( __('Show all privacy form fields','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="full_privacy_show"  value="1" /> '.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="full_privacy_show" checked="checked"  value="0" /><strong> '.esc_html( __('No ','church-admin' ) ).'</strong></p>';
        
        $out.='</div>'."\r\n";
        //howmany
        
        $out.='<div class="church-admin-form-group shortcode-option church-admin-form-group" id="how_many" ><h3>'.esc_html( __('How many sermons per page','church-admin' ) ).'</h3>';
        $out.='<p><input type="number" name="how_many" value=9 /></p></div>'."\r\n";
        //login_form
        $out.='<div class="church-admin-form-group shortcode-option" id="login_form" ><h3>'.esc_html( __("Show login form",'church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="login_form"  checked="checked" value="1" />'.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="login_form" value="0" />'.esc_html( __('No','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //show photos
        $out.='<div class="church-admin-form-group shortcode-option" id="photos" ><h3>'.esc_html( __("Show photos (obeys people's permission settings)",'church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="photos"  checked="checked" value="1" />'.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="photos" value="0" />'.esc_html( __('No','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //pdf
        $out.='<div class="church-admin-form-group shortcode-option" id="pdf" ><h3>'.esc_html( __('PDF Link','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="pdf"  value="2" /> '.esc_html( __('Yes (family photos, single line address)','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="pdf"  checked="checked" value="multi" /> '.esc_html( __('Yes (family photos, multi line address)','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="pdf" value="1" /> '.esc_html( __('Yes (no photos)','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="pdf"  value="0" /> '.esc_html( __('No PDF','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //calendar pdf
        $out.='<div class="church-admin-form-group shortcode-option" id="calendar-pdf" ><h3>'.esc_html( __('PDF Link','church-admin' ) ).'</h3>';
        $out.='<p><input type="checkbox" name="cal-pdf" checked="checked" value="1" /> '.esc_html( __('PDF link','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //show map
        $out.='<div class="church-admin-form-group shortcode-option" id="map" ><h3>'.esc_html( __('Show map','church-admin' ) ).'</h3>';
        if(empty($map_api_key)){
            $out.='<p>'.esc_html( __('You need to set up a Google API key','church-admin' ) ).'</p>';
        }
        $out.='<p><input type="radio" name="map" checked="checked" value="1" />'.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="map" value="0" />'.esc_html( __('No','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //initials

        $out.='<div class="church-admin-form-group shortcode-option" id="initials" ><h3>'.esc_html( __('Use initials or full names','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="initials" value="1" />'.esc_html( __('Initials','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="initials" checked="checked" value="0" />'.esc_html( __('Full names','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //zoom 
        $out.='<div class="church-admin-form-group shortcode-option" id="zoom" ><h3>'.esc_html( __('Map zoom level 0-20','church-admin' ) ).'</h3>';
         $out.='<p><input type="number" name="zoom" value=13  max=20 /></p>';
         $out.='</div>'."\r\n";
        //calendar categories
        $out.='<div class="church-admin-form-group shortcode-option" id="cat_id" ><h3>'.esc_html( __('Calendar Categories','church-admin' ) ).'</h3>';
        $out.='<p><input type="checkbox"  name="cat_id[]" value="#" checked="checked" /><label> '.esc_html( __('All','church-admin')).'</label></p>';
        foreach( $categories AS $id=>$category ){
            $out.='<p><input type="checkbox"  name="cat_id[]" value="'.(int)$id.'" /><label> '.esc_html( $category ).'</label></p>';
        }
        $out.='</div>'."\r\n";
       //member types
        $out.='<div class="church-admin-form-group shortcode-option" id="member_type_id" ><h3>'.esc_html( __('Member Types','church-admin' ) ).'</h3>';
        $out.='<p><input type="checkbox"  name="member_type_id[]" value="#" checked="checked" /><label> '.esc_html( __('All','church-admin')).'</label></p>';
        foreach( $member_types AS $id=>$type ){
            $out.='<p><input type="checkbox"  name="member_type_id[]" value="'.(int)$id.'" /><label> '.esc_html( $type ).'</label></p>';
        }
        $out.='</div>'."\r\n";
        //save as member types
        $out.='<div class="church-admin-form-group shortcode-option" id="save_as_member_type_id" ><h3>'.esc_html( __('Save as Member Type','church-admin' ) ).'</h3>';
        
        foreach( $member_types AS $id=>$type ){
            $out.='<p><input type="radio"  name="save_as_member_type_id" value="'.(int)$id.'" /><label> '.esc_html( $type ).'</label></p>';
        }
        $out.='</div>'."\r\n";

    
        //people types
        $out.='<div class="church-admin-form-group shortcode-option" id="people_type_id" ><h3>'.esc_html( __('People Types','church-admin' ) ).'</h3>';
        foreach( $people_types AS $id => $type ){
            $out.='<p><input type="checkbox"  name="people_type_id[]" checked="checked" value="'.(int)$id.'" /><label> '.esc_html( $type ).'</label></p>';
        }
        $out.='</div>'."\r\n";
       
      
         //sermon series
         
            $out.='<div class="church-admin-form-group shortcode-option" id="series" ><h3>'.esc_html( __('Sermon Series','church-admin' ) ).'</h3>';
            if( !empty( $series) ){
                foreach( $series AS $id => $title ){
                    $out.='<p><input type="radio"  name="series_id" value="'.(int)$id.'" /><label> '.esc_html( $title ).'</label></p>';
                }
                
            }
            else{
                $out.='<p><a href="admin.php?page=church_admin/index.php&action=edit_series" target="_blank">'.esc_html( __('Please set up a sermon series','church-admin' ) ).'</a></p>';
            }
            $out.='</div>'."\r\n";

        //file_id

     
            $out.='<div class="church-admin-form-group shortcode-option" id="file_id" ><h3>'.esc_html( __('Sermons','church-admin' ) ).'</h3>';
           
            if(!empty($sermons))
            {
                $out.='<select name="file_id" class="church-admin-form-groupchurch-admin-form-control">';
                foreach($sermons AS $id=>$sermon){
                    $out.='<option value="'.(int)$id.'">'.esc_html( $sermon).'</option>';
                }
                $out.='</select>';
            }else{
                $out.='<p><a href="admin.php?page=church_admin/index.php&action=upload-mp3" target="_blank">'.esc_html( __('Please upload a sermon MP3','church-admin' ) ).'</a></p>';
            }
                $out.='</div>'."\r\n";

            
        //facilities
        if(!empty($facilities)){
            $out.='<div class="church-admin-form-group shortcode-option" id="facilities_id" ><h3>'.esc_html( __('Facilities','church-admin' ) ).'</h3>';
            foreach( $facilities AS $id => $title ){
                $out.='<p><input type="checkbox"  name="facilities_id[]" value="'.(int)$id.'" /><label> '.esc_html( $title ).'</label></p>';
            }
            $out.='</div>'."\r\n";
        }
        //style
        $out.='<div class="church-admin-form-group shortcode-option" id="style" ><h3>'.esc_html( __('Calendar Style','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio"  name="style" value="table" /><label> '.esc_html(__('Table Style') ).'</label></p>';
        $out.='<p><input type="radio"  name="style" checked="checked" value="new" /><label> '.esc_html(__('New Style') ).'</label></p>';
        $out.='</div>'."\r\n";
        //show age
        $out.='<div class="church-admin-form-group shortcode-option" id="show_age" ><h3>'.esc_html( __('Show age','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="show_age"  checked="checked" value="1" />'.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="show_age" value="0" />'.esc_html( __('No','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
         //show_email
         $out.='<div class="church-admin-form-group shortcode-option" id="show_email" ><h3>'.esc_html( __('Show email','church-admin' ) ).'</h3>';
         $out.='<p><input type="radio" name="show_email"  value="1" /> '.esc_html( __('Yes','church-admin' ) ).'</p>';
         $out.='<p><input type="radio" name="show_email" checked="checked"  value="0" /><strong> '.esc_html( __('No ','church-admin' ) ).'</strong></p>';
         
         $out.='</div>'."\r\n";
         //show_phone
         $out.='<div class="church-admin-form-group shortcode-option" id="show_phone" ><h3>'.esc_html( __('Show phone','church-admin' ) ).'</h3>';
         $out.='<p><input type="radio" name="show_phone"  value="1" /> '.esc_html( __('Yes','church-admin' ) ).'</p>';
         $out.='<p><input type="radio" name="show_phone" checked="checked"  value="0" /><strong> '.esc_html( __('No ','church-admin' ) ).'</strong></p>';
         
         $out.='</div>'."\r\n";
         //show_phone
         $out.='<div class="church-admin-form-group shortcode-option" id="admin_email" ><h3>'.esc_html( __('Email admin','church-admin' ) ).'</h3>';
         $out.='<p><input type="radio" name="admin_email"  value="1" /> '.esc_html( __('Yes','church-admin' ) ).'</p>';
         $out.='<p><input type="radio" name="admin_email" checked="checked"  value="0" /><strong> '.esc_html( __('No ','church-admin' ) ).'</strong></p>';
         
         $out.='</div>'."\r\n";
        //allow registrations
        $out.='<div class="church-admin-form-group shortcode-option" id="registrations" ><h3>'.esc_html( __('Allow registrations','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="allow_registrations"  checked="checked" value="1" />'.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="allow_registrations" value="0" />'.esc_html( __('No','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //allow registrations
        $out.='<div class="church-admin-form-group shortcode-option" id="updateable" ><h3>'.esc_html( __('Allow users to update their entry','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="updateable"  checked="checked" value="1" />'.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="updateable" value="0" />'.esc_html( __('No','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";
        //class_id
        
        $out.='<div class="church-admin-form-group shortcode-option" id="class_id" ><h3>'.esc_html( __('Classes','church-admin' ) ).'</h3>';
        if(!empty($classes)){
            foreach( $classes AS $id => $title ){
                $out.='<p><input type="radio"  name="class_id" value="'.(int)$id.'" /><label> '.esc_html( $title ).'</label></p>';
            }
        }else{
            $out.='<p><a href="admin.php?page=church_admin/index.php&action=edit-class" target="_blank">'.esc_html( __('Please add a class','church-admin' ) ).'</a></p>';
        }
        $out.='</div>'."\r\n";
        
         //days
         $out.='<div class="church-admin-form-group shortcode-option" id="days" ><h3>'.esc_html( __('No. of days to show','church-admin' ) ).'</h3>';
         $out.='<input type="number" name="days"  class="church-admin-form-groupchurch-admin-form-control" />';
         $out.='</div>'."\r\n";

         //weeks
         $out.='<div class="church-admin-form-group shortcode-option" id="weeks" ><h3>'.esc_html( __('No. of weeks to show','church-admin' ) ).'</h3>';
         $out.='<input type="number" name="weeks"  class="church-admin-form-groupchurch-admin-form-control" />';
         $out.='</div>'."\r\n";
         //rolling
         $out.='<div class="church-admin-form-group shortcode-option" id="rolling" ><h3>'.esc_html( __('No. of previous months to show (overrides start date)','church-admin' ) ).'</h3>';
         $out.='<input type="number" name="rolling"  class="church-admin-form-groupchurch-admin-form-control" />';
         $out.='</div>'."\r\n";
        //video url
        $out.='<div class="church-admin-form-group shortcode-option" id="video_url" ><h3>'.esc_html( __('Video embed','church-admin' ) ).'</h3>';
        $out.='<input type="url" name="url" placeholder="embed url" class="church-admin-form-groupchurch-admin-form-control" />';
        $out.='</div>'."\r\n";
        //title
        $out.='<div class="church-admin-form-group shortcode-option" id="title" ><h3>'.esc_html( __('Title','church-admin' ) ).'</h3>';
        $out.='<input type="text" name="title" placeholder="'.esc_html( __('PDF title','church-admin' ) ).'" class="church-admin-form-groupchurch-admin-form-control" />';
        $out.='</div>'."\r\n";
        //vcf

        $out.='<div class="church-admin-form-group shortcode-option" id="vcf" ><h3>'.esc_html( __('V-card link','church-admin' ) ).'</h3>';
        $out.='<p><input type="radio" name="initials" checked="checked" value="1" />'.esc_html( __('Yes','church-admin' ) ).'</p>';
        $out.='<p><input type="radio" name="initials"  value="0" />'.esc_html( __('No','church-admin' ) ).'</p>';
        $out.='</div>'."\r\n";

        //submit
        $out.='<p><input class="button-primary" type="hidden" name="create-shortcode" value="yes" /><input type="submit" class="button-primary" /></p></form>'."\r\n";
    }
 return $out;
}