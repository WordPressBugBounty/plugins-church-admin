<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_podcast_settings()
{
/**
 *
 * Podcast Settings
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */
$settings=get_option('church_admin_podcast_settings');
$upload_dir = wp_upload_dir();
$path=$upload_dir['basedir'].'/sermons/';
$url=$upload_dir['baseurl'].'/sermons/';
    
    global $wpdb,$church_admin_podcast_settings;
    echo'<h2>'.esc_html( __('Podcast Settings for RSS file','church-admin' ) ).'</h2>';
    if(!empty( $settings )){echo'<p><a href="'.$url.'podcast.xml">Podcast RSS file</a></p>';}
   
    $language_codes = array(
		'en-GB' => 'English UK' ,
        'en_US' => 'English US' ,
		'aa' => 'Afar' , 
		'ab' => 'Abkhazian' , 
		'af' => 'Afrikaans' , 
		'am' => 'Amharic' , 
		'ar' => 'Arabic' , 
		'as' => 'Assamese' , 
		'ay' => 'Aymara' , 
		'az' => 'Azerbaijani' , 
		'ba' => 'Bashkir' , 
		'be' => 'Byelorussian' , 
		'bg' => 'Bulgarian' , 
		'bh' => 'Bihari' , 
		'bi' => 'Bislama' , 
		'bn' => 'Bengali/Bangla' , 
		'bo' => 'Tibetan' , 
		'br' => 'Breton' , 
		'ca' => 'Catalan' , 
		'co' => 'Corsican' , 
		'cs' => 'Czech' , 
		'cy' => 'Welsh' , 
		'da' => 'Danish' , 
		'de' => 'German' , 
		'dz' => 'Bhutani' , 
		'el' => 'Greek' , 
		'eo' => 'Esperanto' , 
		'es' => 'Spanish' , 
		'et' => 'Estonian' , 
		'eu' => 'Basque' , 
		'fa' => 'Persian' , 
		'fi' => 'Finnish' , 
		'fj' => 'Fiji' , 
		'fo' => 'Faeroese' , 
		'fr' => 'French' , 
		'fy' => 'Frisian' , 
		'ga' => 'Irish' , 
		'gd' => 'Scots/Gaelic' , 
		'gl' => 'Galician' , 
		'gn' => 'Guarani' , 
		'gu' => 'Gujarati' , 
		'ha' => 'Hausa' , 
		'hi' => 'Hindi' , 
		'hr' => 'Croatian' , 
		'hu' => 'Hungarian' , 
		'hy' => 'Armenian' , 
		'ia' => 'Interlingua' , 
		'ie' => 'Interlingue' , 
		'ik' => 'Inupiak' , 
		'in' => 'Indonesian' , 
		'is' => 'Icelandic' , 
		'it' => 'Italian' , 
		'iw' => 'Hebrew' , 
		'ja' => 'Japanese' , 
		'ji' => 'Yiddish' , 
		'jw' => 'Javanese' , 
		'ka' => 'Georgian' , 
		'kk' => 'Kazakh' , 
		'kl' => 'Greenlandic' , 
		'km' => 'Cambodian' , 
		'kn' => 'Kannada' , 
		'ko' => 'Korean' , 
		'ks' => 'Kashmiri' , 
		'ku' => 'Kurdish' , 
		'ky' => 'Kirghiz' , 
		'la' => 'Latin' , 
		'ln' => 'Lingala' , 
		'lo' => 'Laothian' , 
		'lt' => 'Lithuanian' , 
		'lv' => 'Latvian/Lettish' , 
		'mg' => 'Malagasy' , 
		'mi' => 'Maori' , 
		'mk' => 'Macedonian' , 
		'ml' => 'Malayalam' , 
		'mn' => 'Mongolian' , 
		'mo' => 'Moldavian' , 
		'mr' => 'Marathi' , 
		'ms' => 'Malay' , 
		'mt' => 'Maltese' , 
		'my' => 'Burmese' , 
		'na' => 'Nauru' , 
		'ne' => 'Nepali' , 
		'nl' => 'Dutch' , 
		'no' => 'Norwegian' , 
		'oc' => 'Occitan' , 
		'om' => '(Afan)/Oromoor/Oriya' , 
		'pa' => 'Punjabi' , 
		'pl' => 'Polish' , 
		'ps' => 'Pashto/Pushto' , 
		'pt' => 'Portuguese' , 
		'qu' => 'Quechua' , 
		'rm' => 'Rhaeto-Romance' , 
		'rn' => 'Kirundi' , 
		'ro' => 'Romanian' , 
		'ru' => 'Russian' , 
		'rw' => 'Kinyarwanda' , 
		'sa' => 'Sanskrit' , 
		'sd' => 'Sindhi' , 
		'sg' => 'Sangro' , 
		'sh' => 'Serbo-Croatian' , 
		'si' => 'Singhalese' , 
		'sk' => 'Slovak' , 
		'sl' => 'Slovenian' , 
		'sm' => 'Samoan' , 
		'sn' => 'Shona' , 
		'so' => 'Somali' , 
		'sq' => 'Albanian' , 
		'sr' => 'Serbian' , 
		'ss' => 'Siswati' , 
		'st' => 'Sesotho' , 
		'su' => 'Sundanese' , 
		'sv' => 'Swedish' , 
		'sw' => 'Swahili' , 
		'ta' => 'Tamil' , 
		'te' => 'Tegulu' , 
		'tg' => 'Tajik' , 
		'th' => 'Thai' , 
		'ti' => 'Tigrinya' , 
		'tk' => 'Turkmen' , 
		'tl' => 'Tagalog' , 
		'tn' => 'Setswana' , 
		'to' => 'Tonga' , 
		'tr' => 'Turkish' , 
		'ts' => 'Tsonga' , 
		'tt' => 'Tatar' , 
		'tw' => 'Twi' , 
		'uk' => 'Ukrainian' , 
		'ur' => 'Urdu' , 
		'uz' => 'Uzbek' , 
		'vi' => 'Vietnamese' , 
		'vo' => 'Volapuk' , 
		'wo' => 'Wolof' , 
		'xh' => 'Xhosa' , 
		'yo' => 'Yoruba' , 
		'zh' => 'Chinese' , 
		'zu' => 'Zulu' , 
		);
        asort( $language_codes);
        $cats = array( 'Religion & Spirituality - Christianity',
                  'Arts - Design',
            'Arts - Fashion &amp; Beauty',
            'Arts - Food',
            'Arts - Literature',
            'Arts - Performing Arts',
            'Arts - Visual Arts',  
            'Business - Business News',
            'Business - Careers',
            'Business - Investing',
            'Business - Management &amp; Marketing',
            'Business - Shopping',
            'Comedy',
            'Education - Education Technology',
            'Education - Higher Education',
            'Education - K-12',
            'Education - Language Courses',
            'Education - Training',
            'Games &amp; Hobbies - Automotive',
            'Games &amp; Hobbies - Aviation',
            'Games &amp; Hobbies - Hobbies',
            'Games &amp; Hobbies - Other Games',
            'Games &amp; Hobbies - Video Games',
            'Government &amp; Organizations - Local',
            'Government &amp; Organizations - National',
            'Government &amp; Organizations - Non-Profit',
            'Government &amp; Organizations - Regional',
            'Health - Alternative Health',
            'Health - Fitness &amp; Nutrition',
            'Health - Self-Help',
            'Health - Sexuality',
            'Kids &amp; Family',
            'Music',
            'News &amp; Politics',
            'Religion &amp; Spirituality -Buddhism',
            'Religion &amp; Spirituality -Christianity',
            'Religion &amp; Spirituality -Hinduism',
	    'Religion &amp; Spirituality -Islam',
            'Religion &amp; Spirituality -Judaism',
            'Religion &amp; Spirituality -Other',
            'Religion &amp; Spirituality -Spirituality',
            'Science &amp; Medicine - Medicine',
            'Science &amp; Medicine -Natural Sciences',
            'Science &amp; Medicine -Social Sciences',
            'Society &amp; Culture - History',
            'Society &amp; Culture - Personal Journals',
            'Society &amp; Culture - Philosophy',
            'Society &amp; Culture - Places &amp; Travel',
            'Sports &amp; Recreation - Amateur',
            'Sports &amp; Recreation - College &amp; High School',
            'Sports &amp; Recreation - Outdoor',
            'Sports &amp; Recreation - Professional',
            'Technology - Gadgets',
            'Technology - Tech News',
            'Technology - Podcasting',
            'Technology - Software How-To',
            'TV &amp; Film');
            

    if(current_user_can('manage_options') )
    {//current user can
        if(!empty( $_POST['save_settings'] ) )
        {//process
            
            $upload_dir = wp_upload_dir();
	        $path=$upload_dir['basedir'].'/sermons/';
	       
            $xml=array();
            foreach( $_POST AS $key=>$value) $xml[$key]=xmlentities(sanitize_text_field(stripslashes( $value)  ) );
          
            switch( $xml['explicit'] )
            {
                case 'clean':$xml['explicit']='clean';break;
                case 'no':$xml['explicit']='no';break;
                case 'yes':$xml['explicit']='yes';break;
                default:$xml['explicit']='no';
            }
            $image=wp_get_attachment_image_src( church_admin_sanitize($_POST['image_id']),'full' );
            //church_admin_debug(print_r( $image,TRUE) );
            if(!empty( $image) )  {$image_path=$image[0];}else{$image_path="";}
            //only allow valid category
            if(in_array( $_POST['category'],$cats) )  {$xml['category']=sanitize_text_field( stripslashes($_POST['category'] ) );}else{$xml['category']='Religion &amp; Spirituality -Christianity';}
            if(!array_key_exists( $xml['language'],$language_codes) )$xml['language']='en';
            $new_settings=array(
                'itunes_link'=>sanitize_url(stripslashes($_POST['itunes_link'])),
                'spotify_link'=>sanitize_url(stripslashes($_POST['spotify_link'])),
                'amazon_link'=>sanitize_url(stripslashes($_POST['amazon_link'])),
                'title'=>$xml['title'],  
                'copyright'=>$xml['copyright'],
                'link'=>$path.'podcast.xml',
                'subtitle'=>$xml['subtitle'],
                'author'=>$xml['author'],
                'summary'=>$xml['summary'],
                'description'=>$xml['description'],
                'owner_name'=>$xml['owner_name'],
                'owner_email'=>church_admin_sanitize( $_POST['owner_email'] ),
                'image_id'=>$xml['image_id'],
                'image'=>$image_path,
                'category'=>$xml['category'],
                'language'=>$xml['language'],
                'explicit'=>$xml['explicit'],
                'sermons'=>$xml['sermons'],                   
                'series'=>$xml['series'],
                'most-popular'=>$xml['most-popular'],
                'search'=>$xml['search'],
                'now-playing'=>$xml['now-playing'],
                'sermon-notes'=>$xml['sermon-notes']
            
            );
            
            update_option('church_admin_podcast_settings',$new_settings);
            
            echo'<div class="notice notice-success inline"><p><strong>Podcast Settings Updated<br><a href="'.$url.'podcast.xml">Check Podcast RSS file</a></p></div>';
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
            church_admin_podcast_xml();
            
        }//end process
        else
        {//form
				
 
            echo '<form action="" enctype="multipart/form-data" method="post"><table class="form-table">';
			echo '<tr><th scope="row">Amazon Music Link</th><td><input id="spotifyLink" type="text" class="regular-text" name="amazon_link" ';
            if(!empty( $settings['amazon_link'] ) )echo 'value="'.esc_attr( $settings['amazon_link'] ).'"';
            echo '/></td></tr>';
			echo '<tr><th scope="row">Itunes Link</th><td><input id="iTunesLink" type="text" class="regular-text" name="itunes_link" ';
            if(!empty( $settings['itunes_link'] ) ) echo ' value="'.esc_attr( $settings['itunes_link'] ).'"';
            echo '/></td></tr>';
            echo'<tr><th scope="row">Spotify Link</th><td><input id="spotifyLink" type="text" class="regular-text" name="spotify_link"  ';
            if(!empty( $settings['spotify_link'] ) ) echo ' value="'.esc_attr( $settings['spotify_link'] ).'"';
            echo '/></td></tr>';
            echo'<tr><th scope="row">Podcast title (255 charas)</th><td><input id="title" type="text" class="regular-text" name="title"  ';
            if(!empty( $settings['title'] ) ) echo ' value="'.esc_attr( $settings['title'] ).'"';
            echo '/></td></tr>';
            echo'<tr><th scope="row">Copyright Message: &copy;</th><td><input id="copyright"  class="regular-text" type="text" name="copyright"  ';
            if(!empty( $settings['copyright'] ) ) echo ' value="'.esc_html( $settings['copyright'] ).'"';
            echo '/></td></tr>';
            echo'<tr><th scope="row">Subtitle</th><td><textarea id="subtitle" cols=45 rows=4  name="subtitle" >';
            if(!empty( $settings['subtitle'] ) ) echo esc_html( $settings['subtitle'] );
            echo '</textarea></td></tr>';
            echo'<tr><th scope="row">Author</th><td><input id="author" class="regular-text" type="text" name="author"  ';
            if(!empty( $settings['author'] ) ) echo ' value="'.esc_html( $settings['author'] ).'"';
            echo '/></td></tr>';
            echo'<tr><th scope="row">Summary</th><td><textarea id="summary" cols=45 rows=4   name="summary">'.esc_textarea( $settings['summary'] ).'</textarea></td></tr>';
            echo'<tr><th scope="row">Description</th><td><textarea cols=45 rows=4 id="description"  name="description">'.esc_textarea( $settings['title'] ).'</textarea></td></tr>';
            echo'<tr><th scope="row">Explicit content</th><td><select name="explicit">';
            if(!empty( $settings['explicit'] ) )echo'<option value="'.esc_html( $settings['explicit'] ).'" selected="selected">'.esc_html( $settings['explicit'] ).'</option>';
            echo'<option value="clean">clean</option><option value="no">no</option><option value="yes">yes</option></select></td></tr>';
            
            echo'<tr><th scope="row">Owner Name</th><td><input  class="regular-text" id="owner_name" type="text" name="owner_name"  ';
            if(!empty( $settings['owner_name'] ) ) echo ' value="'.esc_html( $settings['owner_name'] ).'"';
            echo '/></td></tr>';
            echo'<tr><th scope="row">Owner Email</th><td><input class="regular-text" type="text" name="owner_email"  ';
            if(!empty( $settings['owner_email'] ) ) echo ' value="'.esc_html( $settings['owner_email'] ).'"';
            echo '/></td></tr>';
            echo'<tr><th scope="row">Language</th><td><select id="language" name="language">';
            $first=$option='';
            foreach( $language_codes AS $key=>$value)
            {
                if( $key==$settings['language'] )  {$first='<option value="'.esc_html( $key).'" selected="selected" >'.esc_html( $value).'</option>';}else{ $option.='<option value="'.esc_html( $key).'">'.esc_html( $value).'</option>';}
            }
            echo $first.$option.'</select></td></tr>';
            echo'<tr><th scope="row">Itunes Category</th><td><select id="category" name="category">';
            $first=$option='';
            foreach( $cats AS $key=>$value)
            {
                if( $value==$settings['category'] )  {$first='<option value="'.(int)$value.'" selected="selected" >'.esc_html( $value).'</option>';}else{ $option.='<option value="'.(int)$value.'">'.esc_html( $value).'</option>';}
            }
            echo $first.$option.'</select></td></tr>';
            echo'<tr><th scope="row">Image (1400px square)</th><td>';
            echo'<input type="hidden" name="image_id" id="podcast_image_id" ';
            if(!empty( $settings['image_id'] ) )echo' value="'.(int)$settings['image_id'].'" ';
            echo'/>';
            if(!empty( $settings['image_id'] ) )
            {
                $imagePath= wp_get_attachment_image_src( $settings['image_id'],'thumbnail');
                echo'<img src="'.$imagePath[0].'" id="podcast-image"><button id="remove-podcast-image"  class="button-secondary " >'.esc_html( __('Remove Image','church-admin' ) ).'</button>';
            }
            elseif(!empty( $settings['image_path'] ) )  {echo'<img src="'.esc_url( $settings['image_path'] ).'" id="podcast-image"><button id="remove-podcast-image"  class="button-secondary " >'.esc_html( __('Remove Image','church-admin' ) ).'</button>';}
            else{echo'<img src="https://dummyimage.com/300x300/000/fff&text=Podcast+Image" width="300" height="300" id="podcast-image" />';}
            
            echo'<button id="podcast-image-upload"  class="button-secondary " >'.esc_html( __('Upload Image','church-admin' ) ).'</button>';
            
            echo'</td></tr>';
            echo'<tr><th scope="row">'.esc_html( __('Titles for front end podcast','church-admin' ) ).'</th><td>&nbsp;</td></tr>';
            echo'<tr><th scope="row">'.esc_html( __('Sermons','church-admin' ) ).'</td><td><input type="text" name="sermons" value="';
            if(!empty( $settings['sermons'] ) )  {echo esc_html( $settings['sermons'] );}else{echo __('Sermons','church-admin');}
            echo'"></td></tr>';
            echo'<tr><th scope="row">'.esc_html( __('Series','church-admin' ) ).'</td><td><input type="text" name="series" value="';
            if(!empty( $settings['series'] ) )  {echo esc_html( $settings['series'] );}else{echo __('Series','church-admin');}
            echo'"></td></tr>';
            echo'<tr><th scope="row">'.esc_html( __('Most popular','church-admin' ) ).'</td><td><input type="text" name="most-popular" value="';
            if(!empty( $settings['most-popular'] ) )  {echo esc_html( $settings['most-popular'] );}else{echo __('Most popular','church-admin');}
            echo'"></td></tr>';
            echo'<tr><th scope="row">'.esc_html( __('Search','church-admin' ) ).'</td><td><input type="text" name="search" value="';
            if(!empty( $settings['search'] ) )  {echo esc_html( $settings['search'] );}else{echo __('Search','church-admin');}
            echo'"></td></tr>';
            echo'<tr><th scope="row">'.esc_html( __('Now playing','church-admin' ) ).'</td><td><input type="text" name="now-playing" value="';
            if(!empty( $settings['now-playing'] ) )  {echo esc_html( $settings['now-playing'] );}else{echo __('Now playing','church-admin');}
            echo'"></td></tr>';
            echo'<tr><th scope="row">'.esc_html( __('Sermon notes','church-admin' ) ).'</td><td><input type="text" name="sermon-notes" value="';
            if(!empty( $settings['sermon-notes'] ) )  {echo esc_html( $settings['sermon-notes'] );}else{echo __('Sermon notes','church-admin');}
            echo'"></td></tr>';
            echo '<tr><th scope="row"><input type="hidden" name="save_settings" value="yes" /><input type="submit" class="button-primary" value="Save Podcast XML settings" /></td></tr></table></form>';
            echo'<script >jQuery(document).ready(function( $)  {
             var mediaUploader;
                $("#remove-podcast-image").click(function(e)  {
                    e.preventDefault();
                    console.log("remove")
                    $("#podcast-image").attr("src",null);
                    $("#podcast-image").attr("srcset",null);
                    $("podcast_image_id").val("");
                    $("#remove-podcast-image").hide();
                })
                $("#podcast-image-upload").click(function(e) {
                    e.preventDefault();
                    var id="#podcast_image_id";
                    
                    // If the uploader object has already been created, reopen the dialog
                      if (mediaUploader) {
                      mediaUploader.open();
                      return;
                    }
                    // Extend the wp.media object
                    mediaUploader = wp.media.frames.file_frame = wp.media({
                      title: "Choose Image",
                      button: {
                      text: "Choose Image"
                    }, multiple: false });

                    // When a file is selected, grab the URL and set it as the text fields value
                    mediaUploader.on("select", function() {
                      var attachment = mediaUploader.state().get("selection").first().toJSON();
                      console.log(attachment);
                      $(id).val(attachment.id);
                      console.log(attachment.sizes);
                      $("#podcast-image").attr("src",attachment.sizes.medium.url);
                      $("#podcast-image").attr("srcset",null);
                    });
                    // Open the uploader dialog
                    mediaUploader.open();
                  });
            });</script>';
            
            
            
        }//form        
        
        
        
    }//end current user can
    
    
}

  function xmlentities( $string ) {
        $not_in_list = "A-Z0-9a-z\s_-";
        return preg_replace_callback( "/[^{$not_in_list}]/" , 'get_xml_entity_at_index_0' , $string );
    }
    function get_xml_entity_at_index_0( $CHAR ) {
        if( !is_string( $CHAR[0] ) || ( strlen( $CHAR[0] ) > 1 ) ) {
            die( "function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type." );
        }
        switch( $CHAR[0] ) {
            case "'":    case '"':    case '&':    case '<':    case '>':
                return htmlspecialchars( $CHAR[0], ENT_QUOTES );    break;
            default:
                return numeric_entity_4_char( $CHAR[0] );                break;
        }       
    }
    function numeric_entity_4_char( $char ) {
        return "&#".str_pad(ord( $char), 3, '0', STR_PAD_LEFT).";";
    }
    
?>