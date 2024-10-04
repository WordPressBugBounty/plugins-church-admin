<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_search_form()
{
    echo '<form name="ca_search" action="admin.php?page=church_admin/index.php&amp;action=people&amp;section=people" method="POST">';
    wp_nonce_field('church_admin_search');
    echo '<p>'.esc_html( __('Search','church-admin' ) ).'<input name="church_admin_search" style="width:200px;" type="text" /><input type="checkbox" name="all-records" value="TRUE" />'.esc_html( __('Include inactive entries','church-admin' ) ).'&nbsp;<input class="button-primary" type="submit" value="'.esc_html( __('Go','church-admin' ) ).'" /></p></form>';
}

/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 * @author Tristan Jahier
 */
function dateformat_PHP_to_jQueryUI( $php_format)
{
    $SYMBOLS_MATCHING = array(
        // Day
        'd' => 'dd',
        'D' => 'D',
        'j' => 'd',
        'l' => 'DD',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => 'o',
        // Week
        'W' => '',
        // Month
        'F' => 'MM',
        'm' => 'mm',
        'M' => 'M',
        'n' => 'm',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => 'yy',
        'y' => 'y',
        // Time
        'a' => '',
        'A' => '',
        'B' => '',
        'g' => '',
        'G' => '',
        'h' => '',
        'H' => '',
        'i' => '',
        's' => '',
        'u' => ''
    );
    $jqueryui_format = "";
    $escaping = false;
    for ( $i = 0; $i < strlen( $php_format); $i++)
    {
        $char = $php_format[$i];
        if( $char === '\\') // PHP date format escaping character
        {
            $i++;
            if( $escaping) $jqueryui_format .= $php_format[$i];
            else $jqueryui_format .= '\'' . $php_format[$i];
            $escaping = true;
        }
        else
        {
            if( $escaping) { $jqueryui_format .= "'"; $escaping = false; }
            if(isset( $SYMBOLS_MATCHING[$char] ) )
                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
            else
                $jqueryui_format .= $char;
        }
    }
    return $jqueryui_format;
}
function church_admin_date_picker( $db_date=null,$name=null,$array=FALSE,$start=NULL,$end=NULL,$class=NULL,$id=NULL,$disabled=FALSE,$datawhat=NULL,$dataid=NULL,$dataCustomID=NULL,$placeholder = null)
{
    //church_admin_debug(func_get_args() );
    //church_admin_debug("Date picker class ".$class);
	if ( empty( $start) )$start=wp_date('Y');
	if ( empty( $end) )$end=wp_date('Y')+10;
	$out='';
	$date_format=get_option('date_format');
	if ( empty( $date_format) )$date_format='jS M, Y';
    $jsdate_format=dateformat_PHP_to_jQueryUI( $date_format);
    if ( empty( $jsdate_format) )$jsdate_format='d M,yy';
	$out.='<span class="ca-dashicons dashicons dashicons-calendar-alt"></span>';
	//text field that can be seen
	$out.='<input autocomplete="off" type="text" ';
    
    if(!empty($name)){$out.='data-name="'.esc_html( $name).'" ';}
    $out.=' name="'.esc_html( $name).'x';
	if( $array)$out.='[]';
	$out.='" class="';
    if(!empty($class)){$out.=sanitize_title( $class).'x';}
    $out.=' church-admin-form-control clonableDatePicker"' ;
    if(!empty($placeholder)) $out.=' placeholder="'.esc_attr($placeholder).'" ';
    if( $disabled)$out.=' disabled="disabled" ';
	if(!empty( $db_date)&&$db_date!='0000-00-00') $out.= ' value="'.mysql2date(get_option('date_format'),$db_date).'" ';
	$out.=' id="'.esc_html( $id).'x" />'."\r\n";
	
	//data that will be processed when form submitted
	$out.='<input  id="'.esc_html( $id).'" type="hidden" name="'.esc_html( $name);
	if( $array)$out.='[]';
	$out.='" class="clonableHiddenDatePicker church-admin-editable '.esc_html( $class).'" data-name="'.esc_html( $name).'" ';
	if(!empty( $db_date) )$out.='value="'. esc_html( $db_date).'" ';
    if(!empty( $datawhat) ) $out.='data-what="'.esc_html( $datawhat).'" ';
    if(!empty( $dataid) ) $out.='data-id="'.esc_html( $dataid).'" ';
    if(!empty( $dataCustomID) )$out.='data-custom-id="'.esc_html( $dataCustomID).'" ';
	$out.='/>';
	if(!$disabled)$out.='<script>
		jQuery(document).ready(function( $)  {


         	$("body").on("focus",".'.esc_html( $class).'x", function()  {
         		var hidden = "#"+this.id.slice(0, -1);//need to be able to detect the hidden id field when cloned
                 console.log("hidden id is "+hidden);
                 $(this).datepicker({altFormat: "yy-mm-dd",
                    altField:hidden, 
                    dateFormat : "'.$jsdate_format.'", 
                    changeYear: true ,
                    yearRange: "'.intval( $start).':'.intval( $end).'",
                    onClose: function() {console.log(hidden +" change fired"); $(hidden).trigger("change");}
                }).keyup(function(e)  {
                        if ( e.keyCode == 8 || e.keyCode == 46) {$.datepicker._clearDate(this);}
                    });
			});
		});
		</script>';

	return $out;

}




/**
 * Array of ministries
 *
 * @param
 * deprecated
 *
 * @author andy_moyle
 *
 */
function church_admin_ministries( $childID=NULL)  {
	global $wpdb;
	$ministries=array();
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_ministries';
	$where='';
	//if(!empty( $childID) ) {$where=' WHERE childID ="'.intval( $childID).'"';}
	//if( $childID=='None')$where=' WHERE childID =0';
	$order=' ORDER BY ministry';
	$results=$wpdb->get_results( $sql.$where.$order);
	if(!empty( $results) )
	{

		foreach( $results as $row)  {$ministries[$row->ID]=$row->ministry;}

	}

	return $ministries;

}
/**
 * sets wp_mail to html type!
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */
 function church_admin_email_type( $content_type)  {
return 'text/html';
}


/**
 * This function initialises wp_mail with stored smtp settings
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */
add_action( 'wp_mail_failed', function ( $error ) {
    church_admin_debug( $error->get_error_message() );
} );


add_action( 'phpmailer_init', 'church_admin_smtp_email');
function church_admin_smtp_email( $phpmailer ) {
    
    church_admin_debug("church_admin_smtp_email fired");
	$smtp=get_option('church_admin_smtp_settings');
    church_admin_debug($smtp);
	if(!empty( $smtp['username'] )&&!empty( $smtp['host'] )&&!empty( $smtp['port'] )&&!empty( $smtp['password'] ) )
	{
        church_admin_debug('Using SMTP for PHPMAILER');
		// Define that we are sending with SMTP
		
        $phpmailer->IsSMTP();
        //$phpmailer->SMTPDebug= 1;
		// The hostname of the mail server
		$phpmailer->Host = $smtp['host'];//"smtp.example.com";

		// Use SMTP authentication (true|false)
		$phpmailer->SMTPAuth = true;

		// SMTP port number - likely to be 25, 465 or 587
		$phpmailer->Port = $smtp['port'];//"587";

		// Username to use for SMTP authentication
		$phpmailer->Username = $smtp['username'];//yourusername";

		// Password to use for SMTP authentication
		$phpmailer->Password =$smtp['password']; //"yourpassword";

		// Encryption system to use - ssl or tls
		if(!empty($smtp['secure'])){
            $phpmailer->SMTPSecure =$smtp['secure']; 
        }
        if(!empty($smtp['reply_email'])&&$smtp['reply_name']){
            $phpmailer->addReplyTo($smtp['reply_email'], $smtp['reply_name']);
        }    
       
       
        church_admin_debug('Added SMTP credentials');
	}
}
//end smtp settings for wp_mail




function church_admin_max_file_upload_in_bytes() {
    //select maximum upload size
    $max_upload = substr(ini_get('upload_max_filesize'),0,-1);
   
    //select post limit
    $max_post = substr(ini_get('post_max_size'),0,-1);
   
    //select memory limit
    $memory_limit = substr(ini_get('memory_limit'),0,-1);
   
    // return the smallest of them, this defines the real limit
    return size_format(min( $max_upload, $max_post, $memory_limit),0);
}

function church_admin_get_id_by_shortcode( $shortcode) {
	global $wpdb;

	$id = NULL;

	$sql = 'SELECT ID
		FROM ' . $wpdb->posts . '
		WHERE
			post_type = "page"
			AND post_status="publish"
			AND post_content LIKE "%' . esc_sql($shortcode) . '%"';

	$id = $wpdb->get_var( $sql);
	return $id;
}
function church_admin_initials( $people)
{
	$people=maybe_unserialize( $people);
	if(!empty( $people) )
	{

		foreach( $people as $id=>$peep)
		{
			if(church_admin_int_check( $peep) )  {$person=church_admin_get_person( $peep);}else{$person=$peep;}
			$strlen=strlen( $person);
			$initials[$id]='';
			for ( $i=0; $i<=$strlen; $i++)
			{
				$char=substr( $person,$i,1);
				if (ctype_upper( $char) )  {$initials[$id].=$char;}
			}
		}

		return implode(', ',$initials);

	}else return '';
}

function church_admin_checkdate( $date)
{
        if(strlen($date)!=10){return FALSE;}
		$d=explode('-',$date);
        if(empty($d)){return FALSE;}
        if(!is_array($d)){return FALSE;}
        if(empty($d[0])){return FALSE;}
        if(empty($d[1])){return FALSE;}
        if(empty($d[2])){return FALSE;}
        
		if(checkdate( $d[1],$d[2],$d[0] ) ){
            return TRUE;
        }else{
            return FALSE;
        }
}

function church_admin_level_check( $what,$user_id=NULL)
{
  
    global $wpdb;
    //church_admin_debug('Level check for '.$what);
    //church_admin_debug('Given user ID: '.$user_id);
    $current_user=wp_get_current_user();
    if(current_user_can('manage_options') ){
        
        return true;
    }
    if ( empty( $user_id) )$user_id=$current_user->ID;
    if ( empty( $user_id) ){
       
        return false;
    }
/*
    //ministry team contact
    if($what=="ministries")
    {
        church_admin_debug('Checking for ministries what');
        $people_id = $wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user_id.'"');
        //church_admin_debug($wpdb->last_query);
        if(!empty($people_id))
        {
            $is_team_contact=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="team_contact"');
            //church_admin_debug($wpdb->last_query);
            if(!empty($is_team_contact)){
                return TRUE;
            }
        }
    }
*/


    $user_permissions=maybe_unserialize(get_option('church_admin_user_permissions') );
    //church_admin_debug('User permissions:');
    //church_admin_debug( $user_permissions);
    $level=get_option('church_admin_levels');
    //church_admin_debug('Levels method:');
    //church_admin_debug( $level);
    if(!empty( $user_permissions[$what] ) )
    {//user permissions have been set for $what
      
        //church_admin_debug('checking $user_permissions');
        //church_admin_debug(print_r(maybe_unserialize( $user_permissions[$what] ),TRUE) );
        if( in_array( $user_id,maybe_unserialize( $user_permissions[$what] ) ))
        {
            //church_admin_debug('TRUE');
            return TRUE;
        }
        else
        {
            //church_admin_debug('FALSE');
            return FALSE;
        }
    }
		//end user permissions have been set
    elseif(!empty( $level[$what] ) && $level[$what]=="administrator")  {return user_can( $user_id,'manage_options');}
    elseif(!empty( $level[$what] ) && $level[$what]=="editor")  {return user_can( $user_id,'delete_others_pages');}
    elseif(!empty( $level[$what] ) &&$level[$what]=="author")  {return user_can( $user_id,'publish_posts');}
    elseif(!empty( $level[$what] ) &&$level[$what]=="contributor")  {return user_can( $user_id,'edit_posts');}
    elseif(!empty( $level[$what] ) &&$level[$what]=="subscriber")  {return user_can( $user_id,'read');}
    else{ return false;}
    church_admin_debug('*** END church_admin_level_check END ***');
}

function church_admin_user( $ID)
{
		global $wpdb;
		$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$ID.'"');
		if(!empty( $people_id) ) {
            return $people_id;
        }else{
            return FALSE;
        }
}

function church_admin_collapseBoxForUser( $userId, $boxId) {
    $optionName = "closedpostboxes_church-admin";
    $close = get_user_option( $optionName, $userId);
    $closeIds = explode(',', $close);
    $closeIds[] = $boxId;
    $closeIds = array_unique( $clodeIds); // remove duplicate Ids
    $close = implode(',', $closeIds);
    update_user_option( $userId, $optionName, $close);
}



function church_admin_autocomplete( $name='people',$first_id='friends',$second_id='to',$current_data=array(),$user_id=FALSE,$filter=FALSE)
{
            /**
 *
 * Creates autocomplete field
 *
 * @author  Andy Moyle
 * @param    $name,$first_id,$second_id
 * @return   html string
 * @version  0.1
 *
 *
 */
    $current='';
    
    if(!empty( $current_data) )
    {
        $curr_data=maybe_unserialize( $current_data);
        
        if(!empty( $curr_data)&&is_array( $curr_data) )
		{
			foreach( $curr_data AS $key=>$value)
			{

				if(church_admin_int_check( $value) )
				{
						if(!$user_id)
						{//people_id
							$peoplename=church_admin_get_person( $value);
						}
						else
						{//user_id
							$peoplename=church_admin_get_name_from_user( $value);
						}
				}else $peoplename=$value;
				$current.=$peoplename.', ';
			}
		}else$current=$current_data;
    }
    $out= "\r\n".'<input autocomplete="off" id="'.sanitize_title_with_dashes( $first_id).'" class="'.sanitize_title_with_dashes( $second_id).' church-admin-form-control" placeholder="'.esc_html( __('Enter names, separated by commas','church-admin' ) ).'" type="text" name="'.esc_html( $name).'" value="'.esc_html( $current).'" /> ';
    $out.="\r\n";
    $ajax_nonce = wp_create_nonce( "church-admin-autocomplete" );
    $out.='<script type="text/javascript">

	jQuery(document).ready(function ( $)  {

	$("#'.sanitize_title_with_dashes( $first_id).'").autocomplete({
		source: function(req, add)  {
			req.action="church_admin";
			req.method="autocomplete";
			req.security="'.$ajax_nonce.'";
			console.log(req);
			$.getJSON("'.site_url().'/wp-admin/admin-ajax.php", req,  function(data) {

                    console.log("Response " + data);
                    //create array for response objects
                    var suggestions = [];

                    //process response
                    $.each(data, function(i, val)  {suggestions.push(val.name);});

                //pass array to callback
                add(suggestions);
                $(".'.sanitize_title_with_dashes( $second_id).'").removeClass(".ui-autocomplete-loading");
            });

		},
		focus: function() {
          // prevent value inserted on focus
          return false;
        },
		select: function (event, ui) {
                var terms = $("#'.sanitize_title_with_dashes( $first_id).'").val().split(", ");
		// remove the current input
                terms.pop();
                console.log("current:"+terms);
		// add the selected item
                terms.push(ui.item.value);
				
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                console.log("new:" + terms)
				$("#'.sanitize_title_with_dashes( $first_id).'").val(this.value);
                return false;
            },
		minLength: 3,

	});


});


</script>';
    return $out;
}

             /**
 *
 * Returns person's names from $people_id
 *
 * @author  Andy Moyle
 * @param    $id
 * @return   string
 * @version  0.1
 *
 *
*/
function church_admin_get_person( $id)
{

 global $wpdb;
 if(!church_admin_int_check( $id) )return $id;
    $row=$wpdb->get_row('SELECT first_name,middle_name,nickname,prefix,last_name FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$id.'"');
    if( $row)
    {
        $name=church_admin_formatted_name( $row);
    
        return $name;
    }else{return FALSE;}
}
function church_admin_get_name_from_user( $id)
{
             /**
 *
 * Returns person's names from user_id
 *
 * @author  Andy Moyle
 * @param    $id
 * @return   string
 * @version  0.1
 *
 *
*/
 global $wpdb;
 ;
    $row=$wpdb->get_row('SELECT first_name,middle_name,nickname,prefix,last_name FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.esc_sql( $id).'"');

    if( $row)
    {
    	//build name
		$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty( $middle_name)&&!empty( $row->middle_name) )$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty( $nickname)&&!empty( $row->nickname) )$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty( $prefix)&&!empty( $row->prefix) )		$name.=$row->prefix.' ';
					$name.=$row->last_name;
    	return esc_html( $name);
    }else{return FALSE;}
}


function church_admin_get_people_type_ids( $people_type)
{
	global $wpdb;
	//church_admin_debug("********************\r\n function church_admin_get_people_type_ids( $people_type)");
	$return=array();
	$people_types=get_option('church_admin_people_type');
	$ptype_array=explode(",",$people_type);
	foreach( $ptype_array AS $id=>$type)
	{
		$key=array_search(trim( $type),$people_types);
		if( $key)$return[]=$key;
	}
	return( $return);
}


 /****************************************************************************************************
 *
 * Returns array of valid member_type_ids from string of comma separated list of member types or ids
 *
 * @author  Andy Moyle
 * @param    $member_type
 * @return   array
 * @version  0.2
 *
 ******************************************************************************************************/


function church_admin_get_member_type_ids( $member_type)
{
    /*************************************************************
     * Returns array of member_type_id from comma separated list
     *************************************************************/
	global $wpdb;
	
	$member_type_id=array();
	$member_array=explode(",",$member_type);
	
	foreach ( $member_array AS $key=>$memb)
	{
        if(church_admin_int_check( $memb) )
        {
            $memberTypeID=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_member_types WHERE member_type_id="'.(int)$memb.'"');
            if( $memberTypeID)$member_type_id[]=(int)$memberTypeID;

        }
        elseif(!empty( $memb) )
        {
		    $memberTypeID=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_member_types WHERE member_type LIKE "%'.esc_sql(trim( $memb) ).'%"');
            if( $memberTypeID)$member_type_id[]=(int)$memberTypeID;
        }
	}
	
	if(!empty( $member_type_id) )  { return $member_type_id;} else{return array();}
}
 /**
 *
 * Returns peoples names from serialized array
 *
 * @author  Andy Moyle
 * @param    $idArray
 * @return   string
 * @version  0.1
 *
 */
function church_admin_get_people( $idArray)
{

    global $wpdb;
    $ids=maybe_unserialize( $idArray);
    if(!is_array( $ids) )return $ids;
    if(!empty( $ids) )
    {
        $names=array();
        foreach( $ids AS $key=>$id)
        {
            if(church_admin_int_check( $id) )
            {//is int
                $row=$wpdb->get_row('SELECT first_name,middle_name,nickname,prefix,last_name FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$id.'"');
                if(!empty( $row) )
                {
                	$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty( $middle_name)&&!empty( $row->middle_name) )$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty( $nickname)&&!empty( $row->nickname) )$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty( $prefix)&&!empty( $row->prefix) )		$name.=$row->prefix.' ';
					$name.=$row->last_name;
                	$names[]=$name;
                }
            }//end is int
            else
            {//is text
                $names[]=$id;
            }//end is text
        }
        return implode(", ", array_filter( $names) );
    }
    else
    return " ";
}

function church_admin_get_people_id( $name)
{
        /**
 *
 * Returns serialized array of people_id if $name is in DB
 *
 * @author  Andy Moyle
 * @param    $name
 * @return   serialized array
 * @version  0.1
 *
 */
    global $wpdb;
    $names=explode(',',$name);

    $people_ids=array();
    if(!empty( $names) )
    {
        foreach( $names AS $key=>$value)
        {
			$value=trim( $value );
            if(!empty( $value) )
            {//only look if a name stored!
                $sql='SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE  CONCAT_WS(" ",first_name,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR  nickname LIKE "'.esc_sql( $value).'" LIMIT 1';

                $result=$wpdb->get_var( $sql);
                if( $result)  {$people_ids[]=$result;}else{$people_ids[]=$value;}
            }
        }
    }
    return maybe_serialize(array_filter( $people_ids) );
}

function church_admin_get_people_ids( $names)
{
        /**
 *
 * Returns array of people_ids if $names is in DB
 *
 * @author  Andy Moyle
 * @param    $name
 * @return   array
 * @version  0.1
 *
 */
    global $wpdb;
    church_admin_debug('church_admin_get_people_ids: '.$names);
    $namesArray=explode(',',$names);

    $people_ids=array();
    if(!empty( $namesArray) )
    {
        foreach( $namesArray AS $key=>$value)
        {
			$value=trim( $value );
            if(!empty( $value) )
            {//only look if a name stored!
                $sql='SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.esc_sql($value).'" OR last_name="'.esc_sql($value).'" OR CONCAT_WS(" ",first_name,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR  nickname LIKE "'.esc_sql( $value).'"';
                //church_admin_debug($sql);
                $results=$wpdb->get_results( $sql);
                //church_admin_debug($wpdb->last_query);
                if( !empty($results))
                {
                    foreach($results AS $row){
                        $people_ids[]=$row->people_id;
                    }
                }else{$people_ids[]=$value;}
            }
        }
    }
    //church_admin_debug($people_ids);
    church_admin_debug('END church_admin_get_people_ids');
    return array_filter( $people_ids) ;
}




function church_admin_get_push_tokens_from_ids( $ids)
{
    church_admin_debug("**** FUNCTION church_admin_get_push_tokens_from_ids ******");
    church_admin_debug("For array of people_ids");
    //church_admin_debug($ids);
        /**
         *
         * Returns  array of pushTokens from array of people_ids
         *
         * @author  Andy Moyle
         * @param    $name
         * @return   serialized array
         * @version  0.1
         *
         */
    global $wpdb;
    
    $pushTokens=array();
    if(!empty( $ids) )
    {
        foreach( $ids AS $key=>$id)
        {
			 $sql='SELECT pushToken FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$id.'" AND pushToken!="" LIMIT 1';
            church_admin_debug( $sql);
            $result=$wpdb->get_var( $sql);
            //church_admin_debug($result);
            if( !empty( $result ) )  {$pushTokens[]=$result;}
         
        }
    }
    church_admin_debug('Array of pushTokens');
    //church_admin_debug($pushTokens);
    return array_filter( $pushTokens);
}
function church_admin_get_user_id( $name)
{
        /**
 *
 * Returns serialized array of user_id if $name is in DB
 *
 * @author  Andy Moyle
 * @param    $name
 * @return   serialized array
 * @version  0.1
 *
 */
    global $wpdb;
    $names=explode(',',$name);

    $user_ids=array();
    if(!empty( $names) )
    {
        foreach( $names AS $key=>$value)
        {
			      $value=trim( $value);

            if(!empty( $value) )
            {//only look if a name stored!
                //$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $value).'"OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "'.esc_sql( $value).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $value).'" OR  nickname LIKE "'.esc_sql( $value).'" LIMIT 1';
                $sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE CONCAT_WS(" ",first_name,last_name) LIKE("%'.esc_sql($value).'%")||CONCAT_WS(" ",first_name,prefix,last_name) LIKE("%'.esc_sql($value).'%")||first_name LIKE("%'.esc_sql($value).'%")||last_name LIKE("%'.esc_sql($value).'%")||nickname LIKE("%'.esc_sql($value).'%")||email LIKE("%'.esc_sql($value).'%")||mobile LIKE("%'.esc_sql($value).'%") LIMIT 1';

                $result=$wpdb->get_var( $sql);

                if( $result)  {$user_ids[]=$result;}else
				        {
					          echo '<p>'.esc_html( $value).' is not stored by Church Admin as Wordpress User. ';
					          $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE CONCAT_WS(" ",first_name,last_name) REGEXP "^'.esc_sql( $value).'" LIMIT 1');
					          if(!empty( $people_id) )echo'Please <a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.$people_id,'edit_people').'">edit</a> entry to connect/create site user account.';
					          echo'</p>';
				        }
            }
        }
    }
    if(!empty( $user_ids) )  { return maybe_serialize(array_filter( $user_ids) );}else{return NULL;}
}
function church_admin_get_one_id( $name)
{
	global $wpdb;
	$sql='SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name LIKE "'.esc_sql( $name).'" OR last_name LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",first_name,last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "'.esc_sql( $name).'" OR  nickname LIKE "'.esc_sql( $name).'" LIMIT 1';
    $result=$wpdb->get_var( $sql);
	if(!empty( $result) )  {return $result;}else{return $name;}
}


function church_admin_update_order( $which='member_type')
{
    global $wpdb;
    if(isset( $_POST['order'] ) )
    {
        switch( $which)
        {
            case 'custom_fields':$tb=$wpdb->prefix.'church_admin_custom_fields';$field='custom_order';$id='ID';break;
			case'facilities':$tb=$wpdb->prefix.'church_admin_facilities'; $field='facilities_order'; $id='facility_id';break;
            case'member_type':$tb=$wpdb->prefix.'church_admin_member_types'; $field='member_type_order'; $id='member_type_id';break;
            case'rota_settings':$tb=$wpdb->prefix.'church_admin_rota_settings'; $field='rota_order'; $id='rota_id';break;
            case'small_groups':$tb=$wpdb->prefix.'church_admin_smallgroup'; $field='smallgroup_order'; $id='id';break;
			case'people':$tb=$wpdb->prefix.'church_admin_people'; $field='people_order'; $id='people_id';break;
            case'funnel':$tb=$wpdb->prefix.'church_admin_funnels'; $field='funnel_order'; $id='funnel_id';break;
        }
        $order=explode(",",church_admin_sanitize($_POST['order'])) ;
        foreach( $order AS $order=>$row_id)
        {
            $member_type_order++;
            $head='';
            if( $which=='people')
            {
            	if( $order==0)  {$head=', head_of_household=1';}else{$head=', head_of_household=0';}
            }
            $sql='UPDATE '.$tb.' SET '.$field.'="'.esc_sql( $order).'" '.$head.' WHERE '.$id.'="'.esc_sql( $row_id).'"';
            //church_admin_debug( $sql);
            $wpdb->query( $sql);
        }
    }
}

   
 
function church_admin_sermon_series_array(){
    global $wpdb;
    $out=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY series_name';
    $results=$wpdb->get_results( $sql );
    if( $results )
    {

        foreach( $results AS $row ){
        
            $out[$row->series_id]= $row->series_name;
        }
    }
    
    return $out;

}
function church_admin_classes_array(){
    global $wpdb;
    $out=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE next_start_date>=NOW() ORDER BY name';
    $results=$wpdb->get_results( $sql );
    if( $results )
    {

        foreach( $results AS $row ){
        
            $out[$row->class_id]= $row->name;
        }
    }
    
    return $out;

}
function church_admin_event_array(){
    global $wpdb;
    $out=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_date>=NOW() ORDER BY title';
    $results=$wpdb->get_results( $sql );
    if( $results )
    {

        foreach( $results AS $row ){
        
            $out[$row->event_id]= $row->title;
        }
    }
    
    return $out;

}
function church_admin_facilities_array(){
    global $wpdb;
    $out=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_facilities ORDER BY facility_name';
    $results=$wpdb->get_results( $sql );
    if( $results )
    {

        foreach( $results AS $row ){
        
            $out[$row->facilities_id]= $row->facility_name;
        }
    }
    
    return $out;
}


function church_admin_events_array(){
    global $wpdb;
    $out=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_date>=NOW() ORDER BY event_date';
    $results=$wpdb->get_results( $sql );
    if( $results )
    {

        foreach( $results AS $row ){
        
            $out[(int)$row->event_id]= esc_html($row->title);
        }
    }
    
    return $out;


}

function church_admin_sermon_sermons_array(){
    global $wpdb;
    $out=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files ORDER BY pub_date DESC LIMIT 100';
    $results=$wpdb->get_results( $sql );
    if( $results )
    {

        foreach( $results AS $row ){
        
            $out[$row->file_id]= $row->file_title.' - ('.mysql2date(get_option('date_format'),$row->pub_date).')';
        }
    }
    
    return $out;

}
function church_admin_services_array(){
    global $wpdb,$wp_locale;
    $out=array();
    $sql='SELECT a.*,b.venue AS site FROM '.$wpdb->prefix.'church_admin_services a ,'.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id';
    $results=$wpdb->get_results( $sql);
    if( $results)
    {

        foreach($results AS $row){
            if(church_Admin_int_check($row->service_day) && $row->service_day <=6 && $row->service_day>=0 )
            {
                //church_admin_debug($row->service_day.' - '.$wp_locale->get_weekday( $row->service_day));
                $serviceDay = $wp_locale->get_weekday( $row->service_day);
            }
            else
            { 
                //church_admin_debug($row->service_day);
                $serviceDay = __('As Arranged','church-admin');
            }
           
            $out[$row->service_id]=esc_html(sprintf(__('%1$s on %2$s at %3$s','church-admin' ) ,$row->service_name,$serviceDay,$row->service_time));
        }
    }
    
    return $out;
}
function church_admin_sites_array(){
    global $wpdb;
    $out=array();
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sites';
    $results=$wpdb->get_results( $sql);
    if( $results)
    {

        foreach($results AS $row){
            
            
            $out[$row->site_id]= esc_html( $row->venue );
        }
    }
    
    return $out;
}
function church_admin_groups_array()
{
    //church_admin_debug('****** CHURCH ADMIN GROUPS ARRAY ******');
    global $wpdb,$wp_locale;
    $groups=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup ORDER BY group_day ASC, group_name ASC');
    if(!empty( $results) )
    {
        foreach( $results AS $row)
        {
            $groups[(int)$row->id]=$row->group_name;
            if(isset( $row->group_day) )$groups[(int)$row->id].=' ('.$wp_locale->get_weekday( $row->group_day).')';
        }
    }
    //church_admin_debug( $groups);
    //church_admin_debug('**************************************');
    return $groups;
}
function church_admin_units_array()
{
    global $wpdb;
    $units=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_units ORDER BY name');
    if(!empty($results))
    {
        foreach($results AS $row){
            $units[(int)$row->unit_id]=$row->name;
        }
    }
    return $units;
}

function church_admin_on_visit_list_array()
{
    global $wpdb;

    $output = array();

    $results = $wpdb->get_results('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_people b  WHERE a.people_id=b.people_id AND a.meta_type="pastoral-visit-required"');
    if(empty($results)){return $output;}

    foreach($results AS $row){
        $last_visit = null;$last_visit = $wpdb->get_var('SELECT visit_date FROM '.$wpdb->prefix.'church_admin_pastoral_visits WHERE visited="'.(int)$row->people_id.'" ORDER BY visit_date DESC LIMIT 1');
        $lastvisit = null;
        $output[church_admin_formatted_name($row)] = array('people_id'=>$row->people_id,'last_visited'=>$last_visit);
    }
    asort($output);
    return $output;
}

function church_admin_calendar_facilities_array()
{
    global $wpdb;
    $categories=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities ORDER BY facility_name ASC');
    foreach( $results AS $row)
    {
        $categories[$row->facilities_id]=$row->facility_name;
    }
    return( $categories);
}

function church_admin_calendar_categories_array()
{
    global $wpdb;
    $categories=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_category ORDER BY category ASC');
    foreach( $results AS $row)
    {
        $categories[$row->cat_id]=$row->category;
    }
    return( $categories);
}
function church_admin_people_array()
{
    global $wpdb;
    $people=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people ORDER BY last_name,first_name ASC');
    foreach( $results AS $row)
    {
        $people[$row->people_id] = church_admin_formatted_name($row);
    }
    return( $people );
}
function church_admin_member_types_array()
{
    global $wpdb;
    $member_type=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_member_types ORDER BY member_type_order ASC');
    foreach( $results AS $row)
    {
        $member_type[$row->member_type_id]=$row->member_type;
    }
    return( $member_type);
}
function church_admin_member_type_ids()
{
    global $wpdb;
    $member_type=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_member_types ORDER BY member_type_order ASC');
    foreach( $results AS $row)
    {
        $member_type[]=$row->member_type_id;
    }
    return( $member_type);

}
function church_admin_custom_fields_array()
{
    global $wpdb;
    $custom_fields=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE section="household" OR section="people" ORDER BY section,name');
    if(empty($custom_fields)){return array();}
    $output=array();
    foreach($custom_fields AS $cf){
        $output[$cf->ID]=array('name'=>$cf->name,
                                'section'=>$cf->section,
                                'type'=>$cf->type,
                                'default_value'=>$cf->default_value,
                                'show_me'=>$cf->show_me,
                            'options'=>$cf->options);

    }
    return $output;

}
function church_admin_custom_automations_array()
{
   church_admin_debug('**** church_admin_custom_automations_array ****');
    $output=array();
    $automations = get_option('church_admin_custom_fields_automations');
    if(empty($automations)){return FALSE;}
    foreach($automations AS $auto)
    {
        $output[$auto['custom_id']]=$auto;
    }
    return $output;
}
function church_admin_get_old_custom_values($household_id)
{
    church_admin_debug('**** church_admin_get_old_custom_values ****');
    global $wpdb;
    $old_values=array();
    $custom_old_values=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE household_id="'.(int)$household_id.'"');
    //church_admin_debug($wpdb->last_query);
    //church_admin_debug($custom_old_values);
    if(!empty($custom_old_values)){
        $old_values=array();
        foreach($custom_old_values AS $row){
            $old_values[$row->custom_id]=$row->data;
        }
    }
    //church_admin_debug($old_values);
    return $old_values;
}
function church_admin_custom_transient($custom_id,$people_id,$household_id,$old_value,$new_value){

    church_admin_debug('**** church_admin_custom_transient ****');
    
    if($old_value == $new_value){
        church_admin_debug('No change');
        //no change;
        return;
    }
    if(empty($custom_id)){
        church_admin_debug('empty custom_id');
        return;
    }
    $auto = church_admin_custom_automations_array();
    if(empty($auto[$custom_id])){
        church_admin_debug('no automation for that custom_id');
        return;
    }//no automation for that custom_id
    $transient=get_option('church_admin_transient_custom_id'.$custom_id);
    if(empty($transient))$transient=array();
    $transient[]=array('people_id'=>$people_id,'household'=>$household_id,'old_value'=>$old_value,'new_value'=>$new_value);
    //church_admin_debug($transient);
    update_option('church_admin_transient_custom_id'.$custom_id,$transient);
}
function church_admin_kidswork_array()
{
    global $wpdb;
    $kidswork=array();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork ORDER BY youngest ASC');
    foreach( $results AS $row)
    {
        $kidswork[$row->id]=$row->group_name;
    }
    return( $kidswork);
}




function church_admin_get_ministry_hierarchy( $ID)
{

	$structure=array();
	while( $ID)
	{
		$thisOne=church_admin_get_ministry( $ID);
		if(!empty( $thisOne->ministry) )$structure[$ID]=$thisOne->ministry;
		if(!empty( $thisOne->parentID) )  {$ID=$thisOne->parentID;}
		else $ID=FALSE;
	}
	return $structure;
	
}
function church_admin_get_ministry( $ID)
{
	global $wpdb;
    if(empty($ID) || !church_admin_int_check($ID)){
        return FALSE;
    }
	$result=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE id="'.(int)$ID.'"');
	if ( empty( $result) )  {
        return FALSE;
    }else{
        return $result;
    }

}

function church_admin_get_hierarchy( $ID)
{
		$rand=rand();
  		church_admin_leadership_hierarchy( $ID,$rand);
    	$hierarchy=get_option('church_admin_leadership_hierarchy'.$rand);
    	delete_option('church_admin_leadership_hierarchy'.$rand);
    	return $hierarchy;
}

function church_admin_leadership_hierarchy( $ID,$rand)
{
	global $wpdb;
	$hierarchy=get_option('church_admin_leadership_hierarchy'.$rand);
	if ( empty( $hierarchy)||(is_array( $hierarchy)&&!in_array( $ID,$hierarchy) ))  {$hierarchy=array(1=>$ID);update_option('church_admin_leadership_hierarchy'.$rand,$hierarchy);}
	$sql='SELECT parentID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.esc_sql( $ID).'"';

	$nextlevel=$wpdb->get_var( $sql);
	if ( empty( $nextlevel) )
	{

	 	return $hierarchy;
	}
	else
	{
		$hierarchy[]=(int)$nextlevel;

		update_option('church_admin_leadership_hierarchy'.$rand,$hierarchy);
		church_admin_leadership_hierarchy( $nextlevel,$rand);
	}
}
/**
* This function updates a people meta
*
* @author     	andymoyle
* @param		$people_id,$meta_type,$ID
* @return		array
*
*/
function church_admin_update_people_meta( $ID,$people_id,$meta_type='ministry',$meta_date=NULL)
{
    church_admin_debug('***** church_admin_update_people_meta ****');
    church_admin_debug(func_num_args());
  global $wpdb;
  if(empty($ID)){
    return FALSE;
  }
  if(empty($people_id) || !church_admin_int_check($people_id)){
    return FALSE;
  }
  if ( empty( $meta_date) ){
    $meta_date=wp_date('Y-m-d');
  }
 
  	$meta_id=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="'.esc_sql( $meta_type).'" AND ID="'.esc_sql( $ID).'" AND meta_date="'.esc_sql( $meta_date).'"');
  	//church_admin_debug($wpdb->last_query);
    if ( empty( $meta_id) )
  	{
  		$sql='INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,ID,meta_type,meta_date) VALUES ("'.(int)$people_id.'", "'.esc_sql( $ID).'", "'.esc_sql( $meta_type).'", "'.esc_sql( $meta_date).'" );';
        //church_admin_debug($wpdb->last_query);
  		$wpdb->query( $sql);

  	}
    else
    {
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID="'.esc_sql( $ID).'" WHERE meta_id="'.(int)$meta_id.'"');
        //church_admin_debug($wpdb->last_query);
    }
    church_admin_debug('***** END church_admin_update_people_meta ****');
   return TRUE;
}

function church_admin_person_meta_array( $people_id)
{
    global $wpdb;
    $outputArray=array();
    if ( empty( $people_id) ) return $outputArray;
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'"');
    if ( empty( $results) ) return $outputArray;
    foreach( $results AS $row)
    {
        $outputArray[esc_html( $row->meta_type)]=(int)$row->ID;
    }
    return $outputArray;
}
function church_admin_get_people_meta_array( $meta_type,$ID)
{
    //church_admin_debug('******** church_admin_get_people_meta_array ************');
	global $wpdb;
    $people=array();
    $displayCurrentPeople='';
    $sql='SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="'.esc_sql( $meta_type).'" AND ID="'.(int)$ID.'"';
    //church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
    //church_admin_debug(print_r( $results,true) );
    if( $results)
    {
        foreach( $results AS $row)
        {
            if(church_admin_int_check( $row->people_id) )
            { 
                $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$row->people_id.'"';
                //church_admin_debug( $sql);
                $person=$wpdb->get_row( $sql);
                //church_admin_debug( $person);
                if(!empty( $person) ) $people[$person->people_id]= church_admin_formatted_name( $person);
            }
            else $people[$row->people_id]=$row->people_id;
        }
    }
    asort($people);
	return $people;
}

function church_admin_get_people_meta_list( $meta_type,$ID)
{
    $out=NULL;
    $people =  array_filter(church_admin_get_people_meta_array( $meta_type,$ID));
    if(!empty( $people) ) 
    {
        $out= implode(', ',$people);
    }
    return $out;
    
}
/**
* This function produces an array of meta_id for people_id
*
* @author     	andymoyle
* @param		$people_id,$meta_type
* @return		FALSE or array
*
*/
function church_admin_get_people_meta( $people_id,$meta_type='smallgroup')  {
  global $wpdb;
  $out=array();

  $results=$wpdb->get_results('SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="'.esc_sql( $meta_type).'"');
  if ( empty( $results) )  {return FALSE;}
  else
  {
  	foreach( $results AS $row)$out[]=$row->ID;
  	return $out;
  }
}

function church_admin_people_meta( $ID=NULL,$people_id=NULL,$meta_type=NULL)
{
	global $wpdb;
	$sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people_meta a ,'.$wpdb->prefix.'church_admin_people b WHERE a.people_id=b.people_id AND ';
	$where=array();
	if(!empty( $ID) ) $where[]= 'a.ID="'.(int)$ID.'" ';
	if(!empty( $people_id) )$where[]=' a.people_id="'.(int)$people_id.'"';
	if(!empty( $meta_type) )$where[]=' a.meta_type="'.esc_sql( $meta_type).'"';
	$query=$sql.implode(' AND ',$where).' ORDER BY b.last_name,b.first_name ASC';

	$results=$wpdb->get_results( $query);
	return $results;
}

/**
* This function deletes a meta data for a given people_id or meta ID
*
* @author     	andymoyle
* @param		$people_id,$meta_type
* @return
*
*/
function church_admin_delete_people_meta( $ID=NULL,$people_id=NULL,$meta_type=NULL)
{
    //church_admin_debug('*** church_admin_delete_people_meta ***');
    //church_admin_debug('ID '.$ID);
    //church_admin_debug('people id '.$people_id);
    //church_admin_debug('meta type '.$meta_type);
	global $wpdb;
	if ( empty( $people_id) )return FALSE;
    if( $ID)  {
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="'.esc_sql( $meta_type).'" AND ID="'.esc_sql( $ID).'"');
	}else{
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="'.esc_sql( $meta_type).'"');
	}
    //church_admin_debug( $wpdb->last_query);
}




function strip_only( $str, $tags)
{
    //this functions strips some tages, but not all
    if(!is_array( $tags) ) {
        $tags = (strpos( $str, '>') !== false ? explode('>', str_replace('<', '', $tags) ) : array( $tags) );
        if ( end( $tags) == '') array_pop( $tags);
    }
    foreach( $tags as $tag) $str = preg_replace('#</?'.$tag.'[^>]*>#is', '', $str);
    return $str;
}

function checkDateFormat( $date)
{
  //match the format of the date
  if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts) )
  {
    //check weather the date is valid of not
        if(checkdate( $parts[2],$parts[3],$parts[1] ) )
          return true;
        else
         return false;
  }
  else
    return false;
}


function church_admin_queue_email( $to,$subject,$message,$copy=NULL,$from_name=NULL,$from_email=NULL,$attachment=NULL,$schedule=NULL,$reply_name=NULL,$reply_to=NULL)
{
    global $wpdb;
    if(empty($schedule)){
        $schedule=wp_date('Y-m-d');
    }
    $sqlsafe=array();
    $sqlsafe['to']=esc_sql( $to);
    $sqlsafe['from_name']=esc_sql( $from_name);
    $sqlsafe['from_email']=esc_sql( $from_email);
    $sqlsafe['reply_name']=!empty($reply_name) ? esc_sql($reply_name) : esc_sql( $from_name);
    $sqlsafe['reply_to']=!empty($reply_to) ? esc_sql($reply_to) : esc_sql( $from_email);
    $sqlsafe['from_email']=esc_sql( $from_email);
    $sqlsafe['subject']=esc_sql( $subject );
    $sqlsafe['message']=esc_sql( $message );
    $sqlsafe['attachment']=!empty( $attachment) ? esc_sql(maybe_serialize( $attachment) )  :null;
	$sqlsafe['schedule']=!empty( $schedule)?esc_sql( $schedule)  :null;
    $sqlsafe['copy']=esc_sql(maybe_unserialize( $copy) );
    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_email (recipient,from_name,from_email,copy,subject,message,attachment,schedule,reply_to,reply_name)VALUES("'.$sqlsafe['to'].'","'.$sqlsafe['from_name'].'","'.$sqlsafe['from_email'].'","'.$sqlsafe['copy'].'","'.$sqlsafe['subject'].'","'.$sqlsafe['message'].'","'.$sqlsafe['attachment'].'","'.$sqlsafe['schedule'].'","'.$sqlsafe['reply_to'].'","'.$sqlsafe['reply_name'].'")';

	$result=$wpdb->query( $sql);

    if( $result) {
        return $wpdb->insert_id;
    }else{
        return FALSE;
    }
}

if(!function_exists('set_html_content_type') )  {function set_html_content_type() {return 'text/html';}}

function church_admin_plays( $file_id)
{
	global $wpdb;
	$plays=$wpdb->get_var('SELECT plays FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.esc_sql( $file_id).'"');
	return $plays;
}

function church_admin_dateCheck( $date, $yearepsilon=5000)
{ // inputs format is "yyyy-mm-dd" ONLY !
if (count( $datebits=explode('-',$date) )!=3) return false;
$year = intval( $datebits[0] );
$month = intval( $datebits[1] );
$day = intval( $datebits[2] );
if ((abs( $year-date('Y') )>$yearepsilon) || // year outside given range
( $month<1) || ( $month>12) || ( $day<1) ||
(( $month==2) && ( $day>28+(!( $year%4) )-(!( $year%100) )+(!( $year%400) )) ) ||
( $day>30+(( $month>7)^( $month&1) )) ) return false; // date out of range
if( checkdate( $month,$day,$year) ) {return ( $year.'-'.esc_html(sprintf("%02d", $month).'-'.sprintf("%02d", $day) ));}else{return FALSE;}
}

/**************************************************************************************************************************************************
*
*
*  Check if logged in user can do what is wanted
* param ID - ID of person about to be edited/deleted or ID of ministry
* admins can do anything
*
*
*
*
***************************************************************************************************************************************************/
function church_admin_user_can( $ID,$meta_type='smallgroup')
{
	$can=FALSE;
	global $current_user;
	wp_get_current_user();
	$user_people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');

	//administrator
	if(current_user_can('manage_options') ) return TRUE;

	//if current user is the passed ID
	if( $user_people_id==$ID)return TRUE;

	if( $meta_type=='smallgroup')
	{
		//check if $ID is in a group led or overseen
		$sgID=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$id.'" AND meta_key="smallgroup"');
		if(!empty( $sgID) )
		{
			$leaders=maybe_unserialize( $wpdb->get_var('SELECT leadership FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$sgID.'"') );
			if(!empty($leaders) && is_array( $leaders) )
			{
				foreach( $leaders AS $leaderlevel)
				{
					if(in_array( $user_people_id,$leaderlevel) ) {
                        return TRUE;
                    }
				}
			}
		}
	}
	else
	{//ministry
	//see if ministry has a parent
		$parentID=$wpdb->get_var('SELECT parentID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int)$id.'"');
		if ( empty( $parentID) ) return FALSE;
		if(parent( $ID) )  {return TRUE;}
		function parent( $ID)
		{
			$check=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$id.'" AND people_id="'.(int) $user_people_id.'" AND meta_type="ministry"');
			if(!empty( $check) ) return TRUE;
			$next_level=$wpdb->get_var('SELECT parentID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int)$parentID.'"');
			if(!empty( $next_level) )
			{
				if(parent( $next_level) )  { 
                    return TRUE;
                }else {
                    return FALSE;
                }
			}
			else {
                return FALSE;
            }
		}
		//see if user is in that parent ministry

	}
	return FALSE;
}


function church_admin_adjust_brightness( $hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps) );

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen( $hex) == 3) {
        $hex = str_repeat(substr( $hex,0,1), 2).str_repeat(substr( $hex,1,1), 2).str_repeat(substr( $hex,2,1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split( $hex, 2);
    $return = '#';

    foreach ( $color_parts as $color) {
        $color   = hexdec( $color); // Convert to decimal
        $color   = max(0,min(255,$color + $steps) ); // Adjust color
        $return .= str_pad(dechex( $color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}
 /**
 *
 * Replace rota entry
 *
 * @author  Andy Moyle
 * @param    $people_id,$date,$mtg_type,$service_id,$rota_task_id
 * @return   BOOL
 * @version  0.1
 *
 */
 function church_admin_update_rota_entry( $rota_task_id,$rota_date,$people_id,$mtg_type,$service_id,$service_time)
 {
 	global $wpdb;
    //church_admin_debug('******** church_admin_update_rota_entry **********');
 	$table=$wpdb->prefix.'church_admin_new_rota';
	
 	$data=array(
 			'rota_task_id'=>$rota_task_id,
 			'people_id'=>$people_id,
 			'mtg_type'=>$mtg_type,
 			'service_id'=>$service_id,
 			'rota_date'=>$rota_date,
			'service_time'=>$service_time
 	);

 	$format=array(
 			'%d',
 			'%s',
 			'%s',
 			'%d',
 			'%s',
		'%s'
 	);
	 $rota_id=$wpdb->get_var('SELECT rota_id FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_task_id="'.esc_sql( $rota_task_id).'" AND people_id="'.(int)$people_id.'" AND mtg_type="'.esc_sql( $mtg_type).'" AND service_id="'.(int)$service_id.'" AND rota_date="'.esc_sql( $rota_date).'"');

 	if ( empty( $rota_id) )
 	{
 		$wpdb->insert( $table,$data,$format);
 	}
 	else
 	{
 		$where=array('rota_id'=>$rota_id);
 		$wpdb->update( $table, $data, $where, $format  );

	}
    church_admin_debug( $wpdb->last_query);
 }
  /**
 *
 * Grab array of people_ids for particular ministry_id
 *
 * @author  Andy Moyle
 * @param    $ministry_id
 * @return   array( $people_id=>$name)
 * @version  0.1
 *
 */
 function church_admin_ministry_people_array( $ministry_id)
 {
 	global $wpdb;
 	$out=array();
 	$results=$wpdb->get_results('SELECT a.*, b.people_id AS people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="ministry" AND b.ID="'.(int)$ministry_id.'" ');
 	//church_admin_debug( $results);
    if(!empty( $results) )
 	{
 		foreach( $results AS $row)$out[$row->people_id]=church_admin_formatted_name( $row);
 	}

 	return $out;
 }
  /**
 *
 * Grab array of people_ids for particular rota_task_id and event
 *
 * @author  Andy Moyle
 * @param    $rota_date,$rota_taks_id,$service_id,$mtg_type
 * @return   array( $people_id=>$name)
 * @version  0.1
 *
 */
 function church_admin_rota_people_array( $rota_date,$rota_task_id,$service_id,$mtg_type)
 {
 	global $wpdb;
 	$out=array();
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_task_id="'.(int)$rota_task_id.'" AND mtg_type="'.esc_sql( $mtg_type).'" AND service_id="'.(int)$service_id.'" AND rota_date="'.esc_sql( $rota_date).'"';
	
    //church_admin_debug( $sql);
 	
	if(!empty( $rota_date) )$results=$wpdb->get_results( $sql);
 	if(!empty( $results) )
 	{
 		foreach( $results AS $row)  {
            if(!empty( $row->people_id) ){
                $out[$row->people_id]=church_admin_get_person( $row->people_id);
            }
        }
 	}
 	return $out;
 }
   /**
 *
 * Grab comma separated list of people for particular rota_taks_id and event
 *
 * @author  Andy Moyle
 * @param    $rota_date,$rota_taks_id,$service_id,$mtg_type
 * @return   string
 * @version  0.1
 *
 */
 function church_admin_rota_people( $rota_date,$rota_task_id,$service_id,$mtg_type)
 {
 	return implode(", ",church_admin_rota_people_array( $rota_date,$rota_task_id,$service_id,$mtg_type) );
 }


 /*
 * Grab array of schedule for date, service_id and mtg_type
 *
 * @author  Andy Moyle
 * @param    $service_id,$date,$mtg_type
 * @return   array
 * @version  0.1
 *
 */
 function church_admin_retrieve_rota_data_array($service_id,$date,$mtg_type='service'){
	church_admin_debug(' church_admin_retrieve_rota_data_array');
    global $wpdb;
	if(empty($service_id)){
        church_admin_debug('No service id');
        return NULL;
    }
	if(empty($date)){
        church_admin_debug('No date');
        return NULL;
    }


	//get tasks
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
	$requiredRotaJobs=array();
	foreach( $rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize( $rota_task->service_id);
		if(is_array( $allServiceID)&& in_array( $service_id,$allServiceID) && !empty($rota_task->calendar))$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
		
	}
	if(empty($requiredRotaJobs)){
        church_admin_debug('No jobs');
        return NULL;
    }
	$data=array();
    //church_admin_debug($requiredRotaJobs);
	foreach($requiredRotaJobs AS $rota_task_id => $job){
        $people = FALSE;
        //church_admin_debug($date.' '.$rota_task_id.' '.$service_id.' '.$mtg_type);
		$people = church_admin_rota_people( $date,$rota_task_id,$service_id,$mtg_type);
        if(!empty($people))$data[$job]= $people;
	}
    //church_admin_debug($data);
    church_admin_debug('END church_admin_retrieve_rota_data_array');
	return $data;
}

    /**
 *
 * Grab comma separated list of people for particular rota_taks_id and event
 *
 * @author  Andy Moyle
 * @param    $rota_date,$rota_taks_id,$service_id,$mtg_type
 * @return   string
 * @version  0.1
 *
 */
 function church_admin_rota_people_initials( $rota_date,$rota_task_id,$service_id,$mtg_type)
 {
 	$people=church_admin_rota_people_array( $rota_date,$rota_task_id,$service_id,$mtg_type);
 	$initials=array();
 	foreach( $people AS $key=>$person)
 	{
 		$words = explode(" ", $person);
        
		$acronym = "";
		if(!empty( $words) )
        {
            foreach ( $words as $w) {if(!empty( $w[0] ) )$acronym .= strtoupper( $w[0] );}
        }
		$initials[]=$acronym;
 	}
 	return implode(", ",$initials);
 }

function church_admin_rota_people_firstname_initial_last_name( $rota_date,$rota_task_id,$service_id,$mtg_type)
{
    //church_admin_debug('First name initial Last name initial processing');
    global $wpdb;
    $out=array();
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_task_id="'.(int)$rota_task_id.'" AND mtg_type="'.esc_sql( $mtg_type).'" AND service_id="'.(int)$service_id.'" AND rota_date="'.esc_sql( $rota_date).'"';
	if(!empty( $rota_date) )$results=$wpdb->get_results( $sql);
 	if(!empty( $results) )
 	{
 		foreach( $results AS $row)
        {
            //church_admin_debug( $row);
            //church_admin_debug( $row->people_id);
            if(church_admin_int_check( $row->people_id) )
            {
                //church_admin_debug("DIGIT");
                //stored as a people id
                $person = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$row->people_id.'"');
                //church_admin_debug( $person);
                if(!empty( $person) )
                {
                    //church_admin_debug( $person);
                    $firstName=!empty( $person->first_name)?$person->first_name:NULL;
                    $prefix=!empty( $person->prefix)?$person->prefix:NULL;
                    $initialOfLastName=!empty( $person->last_name)?substr( $person->last_name,0,1)  :NULL;   
                    $out[]=implode(" ",array_filter(array( $firstName,$prefix,$initialOfLastName) ));
                }
            }
            else
            {
                
                //church_admin_debug("WORDS");
                //stored as a name
                $words=explode(" ",$row->people_id);
                //church_admin_debug( $words);
                //assume first word is first name and last word is last name, everything between a prefix
                $count=count( $words);
                //church_admin_debug('ARRAY COUNT:'. $count);
                $firstName=$words[0];
                $initialOfLastName=strtoupper(substr( $words[$count-1],0,1) );
                $prefix=array();
                if( $count>2)
                {
                    
                    //there's a prefix eg "van der"
                    for ( $x=1; $x<$count-1; $x++)$prefix[]=$words[$x];
                }
                $prefix=implode(' ',$prefix);
                $out[]=implode(" ",array_filter(array( $firstName,$prefix,$initialOfLastName) ));
                
            }
        }
 	}
 	return implode(", ",$out);
}

/**
 *
 * Works out font size and orientation for data
 *
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 *
 */
function church_admin_pdf_settings( $lengths,$fontSize=10)
{
	//M is max width letter and at 1pt Arial will take up 0.35mm approx, will allow 3mm either side
	$colWidth=array();
	foreach( $lengths AS $key=>$length)$colWidth[$key]=( $length*$fontSize*0.2)+6;
	$pdfSettings=array('font_size'=>$fontSize,'widths'=>$colWidth);
	//find total width and check it is less than width of page
	$tableWidth=array_sum( $colWidth);
	//church_admin_debug("Table Width: $tableWidth");
	$pdfSize=get_option('church_admin_pdf_size');

	switch( $pdfSize)
	{
		case 'A4':
					if(( $tableWidth)<190)$pdfSettings['orientation']='P';
					elseif( $tableWidth<277)$pdfSettings['orientation']='L';
					else{return FALSE;}
		break;
		case 'Letter':
					if(( $tableWidth)<195)$pdfSettings['orientation']='P';
					elseif( $tableWidth<259)$pdfSettings['orientation']='L';
					else{return FALSE;}
		break;
		case 'Legal':
					if(( $tableWidth)<200)$pdfSettings['orientation']='P';
					elseif( $tableWidth<346)$pdfSettings['orientation']='L';
					else{return FALSE;}
		break;
	}

	return $pdfSettings;

}

     function church_admin_api_checker( $url) {
        $curl = curl_init( $url);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt( $curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec( $curl);

        $ret = false;

        //if request did not fail
        if ( $result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE);
       		$ret=$statusCode;
        }
        curl_close( $curl);

       return $statusCode;
    }
/**
 *
 * Page id of church_admin_register shortcode containing page
 *
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 *
 */
function church_admin_register_page_id()
{
	global $wpdb;
	$page_id=$wpdb->get_var('SELECT ID FROM '.$wpdb->posts.' WHERE (post_content LIKE "%[church_admin_register]%" OR post_content LIKE "%wp:church-admin/register%" OR post_content LIKE "%wp:church-admin/basic-register%")AND post_status="publish" LIMIT 1');
    //church_admin_debug("PAGE id is $page_id");
	if(!empty( $page_id) )  {return (int)$page_id;}else{return FALSE;}
}
 /**
 *
 * Page id of church_admin_unsubscribe shortcode containing page
 *
 * @author  Andy Moyle
 * @param    $lengths, $fontSize
 * @return   array(orientation,font_size,widths)
 * @version  0.1
 *
 */
function church_admin_unsubscribe_page_id()
{
	global $wpdb;
	$page_id=$wpdb->get_var('SELECT ID FROM '.$wpdb->posts.' WHERE post_content LIKE "%[church_admin_unsubscribe]%" AND post_status="publish" LIMIT 1');
	if(!empty( $page_id) )  {return intval( $page_id);}else{return FALSE;}
}

 /**
 *
 * Check whether person with peple_id is active or not
 *
 * @author  Andy Moyle
 * @param    $people_id
 * @return   BOOL
 * @version  0.1
 *
 */
function church_admin_deactivated_check( $people_id)
{
	global $wpdb;
	$check=$wpdb->get_var('SELECT active FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	if( $check)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}
 /**
 *
 * Output JQuery to handle clicking on Activate/Deactivate
 *
 * @author  Andy Moyle
 * @param
 * @return   $out
 * @version  0.1
 *
 */
function church_admin_activate_script()
{	//jQuery for processing activate/deactivate peopl
		$nonce = wp_create_nonce("church_admin_people_activate");
		$out='

	<script type="text/javascript">
		jQuery(document).ready(function( $) {
			$("body").on("click",".activate", function()  {
				var id = this.id;
        console.log("people_id "+id);
      			var data = {
				"action": "church_admin",
				"method":"people_activate",
				"people_id": id,
				"nonce": "'.$nonce.'"
				};
      console.log( data);
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		  $.getJSON(ajaxurl, data, function(response) {
      console.log( response)
			if(response.status==1)  {
					$("#"+response.id).removeClass("ca-deactivated");
					$("#active-"+response.id).html("Active ");
				}else{
					$("#"+response.id).addClass("ca-deactivated");
					$("#active-"+response.id).html("Deactive ");
				}
		});
			});
		});
	</script>

	';
	return $out;
}
/*
function church_admin_helper()
{


$out='
	<script>
		jQuery(document).ready(function( $) {
			$("body").on("click"," .help", function()  {
			var id=this.id;
			var message;
				switch(id)
				{
					case "active-message":
						alert("'.esc_html( __('Click to change active status of person in directory','church-admin' ) ).'");
					break;


				}

			});
			});
	</script>';
return $out;


}
*/

class ChurchAdminDateTime extends DateTime {

    public function returnAdd(DateInterval $interval)
    {
        $dt = clone $this;
        $dt->add( $interval);
        return $dt;
    }

    public function returnSub(DateInterval $interval)
    {
        $dt = clone $this;
        $dt->sub( $interval);
        return $dt;
    }

}



/*********************
*
*
*	AJAX operations
*
***********************/
function church_admin_date()
{
	require_once(plugin_dir_path( __FILE__) .'../display/calendar.new.php');
    $date = !empty($_REQUEST['date'])?church_admin_sanitize($_POST['date']):null;
    if(!empty($date ) && church_admin_checkdate($date)){	echo church_admin_display_day(  );}
	exit();
}


function church_admin_note_delete_callback() {

	//check_admin_referer('church_admin_delete_note','nonce');
	global $wpdb;
	$sql='DELETE FROM '.$wpdb->prefix.'church_admin_comments  WHERE comment_id="'.(int)church_admin_sanitize($_POST['note_id'] ).'"';
	$wpdb->query( $sql);
	$sql='DELETE FROM '.$wpdb->prefix.'church_admin_comments  WHERE parent_id="'.(int) church_admin_sanitize($_POST['note_id'] ).'"';
	$wpdb->query( $sql);
	echo TRUE;
	exit();
}



function church_admin_dismissable_notices() {
    church_admin_debug('*** church_admin_dismissable_notices ****');
    church_admin_debug($_GET);
    
    
    /********************************
    * ROLES and Permissions v4.5.0
    ********************************/
    $roles_dismissed=get_option('dismissed-church-admin-roles-permissions');
    
    if ( empty( $roles_dismissed) && !empty( $_GET['page'] )&& ( $_GET['page']=='church_admin/index.php') ) { 
        church_admin_debug('NOTICE');
        // Added the class "notice-my-class" so jQuery pick it up and pass via AJAX,
        // and added "data-notice" attribute in order to track multiple / different notices
        // multiple dismissible notice states 
        echo '<div class="notice notice-info is-dismissible ca-notice-dismiss" data-notice="prefix_deprecated">
            <h2 style="color:red">'.esc_html( __( 'Church Admin - Roles and Permissions have changed from v4.5.0', 'church-admin' )) .'</h2>
            <p>'.esc_html( __('Please check roles/permissions for non WordPress Administrator accounts using Church Admin','church-admin' ) ).'</p>
            <p><a class="button-primary" target="_blank" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=roles','roles').'">'.esc_html( __('Roles','church-admin' ) ).'</a></p>
            <p><a  class="button-primary"  target="_blank" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=permissions','permissions').'">'.esc_html( __('Permissions','church-admin' ) ).'</a></p>
            </div>';
        echo'<script>jQuery(document).ready(function( $)  {
            $("body").on("click",".ca-notice-dismiss",function()  {
                
                var data ={
                    "action": "church_admin",
                    "method":"dismissed-notice-handler",
                    "nonce":"'.wp_create_nonce('dismissed-notice-handler').'",
                    "type": "church-admin-roles-permissions"
                  };
                  
                $.ajax( ajaxurl,
                {
                  "type": "POST",
                  "data": data ,
                  success:function()  {console.log("Ajax dismiss done");}
                } );

            });

        });</script>';
    }
}

add_action( 'admin_notices', 'church_admin_dismissable_notices' );

function church_admin_unattach_user()
{

	global $wpdb;
	//church_admin_debug((int)$_POST['people_id'] );
    $people_id = !empty($_POST['people_id'])?church_admin_sanitize($_POST['people_id']):null;
	if(!empty($people_id) && church_admin_int_check($people_id)){
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id=NULL WHERE people_id="'.(int)$people_id.'"');
    }
}
/**
 *
 * Ajax - returns json array with people's names
 * Used by fautoe
 * @author  Andy Moyle
 * @param    null
 * @return   json array
 * @version  0.1
 *
 */
function church_admin_ajax_people( $active=FALSE)
{

    global $wpdb;
    $names=explode(", ", church_admin_sanitize($_GET['term'] ));//put passed var into array
    $name=esc_sql(trim(end( $names) ) );//grabs final value for search
    if(!empty( $active) )  { $activeSQL=" active=1 AND ";}else{$activeSQL="";}

   $sql='SELECT CONCAT_WS(" ",first_name,prefix, last_name) AS name FROM '.$wpdb->prefix.'church_admin_people WHERE '.$activeSQL.' (CONCAT_WS(" ",first_name,last_name) LIKE "%'.esc_sql( $name).'%" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "%'.esc_sql( $name).'%" OR CONCAT_WS(" ",first_name,middle_name,prefix,last_name) LIKE "%'.esc_sql( $name).'%" OR CONCAT_WS(" ",first_name,middle_name,last_name) LIKE "%'.esc_sql( $name).'%" OR CONCAT_WS(" ",first_name,prefix,last_name) LIKE "%'.esc_sql( $name).'%" OR  nickname LIKE "%'.esc_sql( $name).'%") ';
    //church_admin_debug( $sql);
    $result=$wpdb->get_results( $sql);

    if( $result)
    {
        $people=array();
        foreach( $result AS $row)
        {
            $people[]=array('name'=>$row->name);
        }

        //echo JSON to page

    $response =json_encode( $people);

    echo $response;
    }
    exit();
}


function church_admin_mp3_plays() {
	
    church_admin_debug('*** church_admin_mp3_plays ***');
    church_admin_debug($_POST);
	global $wpdb;
	$file_id = (int)church_admin_sanitize($_POST['file_id'] ) ;
   if(!empty($file_id) && church_admin_int_check($file_id)) 
   {
        $currPlays=$wpdb->get_var('SELECT plays FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE  file_id = "'.(int)$file_id.'"');
        if ( empty( $currPlays) )$currPlays=0;
        $newPlays=$currPlays+1;
        $sql='UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET plays = '.(int)$newPlays.' WHERE file_id = "'.(int)$file_id.'"';
        $wpdb->query( $sql);
        church_admin_debug($wpdb->last_query);
        $plays=$wpdb->get_var('SELECT plays FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id = "'.(int)$file_id.'"');

        echo $plays;
        die();
   }
}

function church_admin_username_check()
{
	//check_admin_referer('church_admin_username_check','nonce');

	if(username_exists(church_admin_sanitize( $_POST['user_name'] ) ))   {
        echo'<span class="ca-dashicons dashicons dashicons-no" style="color:red"></span>';
    }else{
        echo'<span style="color:green" class="ca-dashicons dashicons dashicons-yes"></span>';
    }
	exit();
}

function church_admin_filter_callback() {

	check_ajax_referer('filter','nonce');

	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/filter.php');
	//church_admin_debug("callback");

	church_admin_filter_process();

	exit();
}

function church_admin_filter_email_callback() {

	check_ajax_referer('filter','nonce');
	require_once(plugin_dir_path(dirname(__FILE__) ).'/includes/filter.php');
	echo church_admin_filter_email( church_admin_sanitize($_POST['data'] ));
	exit();
}




function church_admin_people_activate_callback() {

	//check_admin_referer('activate','nonce');
	global $wpdb;
  $people_id=substr( church_admin_sanitize($_REQUEST['people_id']),7);
  if(!church_admin_int_check($people_id)){exit();}
    //church_admin_debug( $people_id);
	$sql='UPDATE '.$wpdb->prefix.'church_admin_people SET active = !active WHERE people_id="'.(int)$people_id.'"';
    // //church_admin_debug( $sql);
	$wpdb->query( $sql);
	$status=$wpdb->get_var('SELECT active FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    if( $status==0)  {
        $active=__('Inactive','church-admin');
    }else{
        $active=__('Active','church-admin');
    }
	$output=array('status'=>$active,'id'=>(int)$people_id);
  header('Access-Control-Max-Age: 1728000');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: *');
  header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
  header('Access-Control-Allow-Credentials: true');
  echo json_encode( $output);
  die();
  exit();
}


function church_admin_ministries_array()
{
	global $wpdb;
	$ministries=array();
	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries ORDER BY ministry');
	if(!empty( $results) )
	{
		foreach( $results AS $row){
            $ministries[(int)$row->ID]=esc_html( $row->ministry);
        }
	}
	return $ministries;
}


function church_admin_small_groups_array()
{
	global $wpdb;
	$small_groups=array();
	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
	if(!empty( $results) )
	{
		foreach( $results AS $row)$small_groups[(int)$row->id]=esc_html( $row->group_name);
	}
	return $small_groups;
}

function church_admin_encode( $word) {

    $word = str_replace("@","%40",$word);
    $word = str_replace("`","%60",$word);
    $word = str_replace("¢","%A2",$word);
    $word = str_replace("£","%A3",$word);
    $word = str_replace("¥","%A5",$word);
    $word = str_replace("|","%A6",$word);
    $word = str_replace("«","%AB",$word);
    $word = str_replace("¬","%AC",$word);
    $word = str_replace("¯","%AD",$word);
    $word = str_replace("º","%B0",$word);
    $word = str_replace("±","%B1",$word);
    $word = str_replace("ª","%B2",$word);
    $word = str_replace("µ","%B5",$word);
    $word = str_replace("»","%BB",$word);
    $word = str_replace("¼","%BC",$word);
    $word = str_replace("½","%BD",$word);
    $word = str_replace("¿","%BF",$word);
    $word = str_replace("À","%C0",$word);
    $word = str_replace("Á","%C1",$word);
    $word = str_replace("Â","%C2",$word);
    $word = str_replace("Ã","%C3",$word);
    $word = str_replace("Ä","%C4",$word);
    $word = str_replace("Å","%C5",$word);
    $word = str_replace("Æ","%C6",$word);
    $word = str_replace("Ç","%C7",$word);
    $word = str_replace("È","%C8",$word);
    $word = str_replace("É","%C9",$word);
    $word = str_replace("Ê","%CA",$word);
    $word = str_replace("Ë","%CB",$word);
    $word = str_replace("Ì","%CC",$word);
    $word = str_replace("Í","%CD",$word);
    $word = str_replace("Î","%CE",$word);
    $word = str_replace("Ï","%CF",$word);
    $word = str_replace("Ð","%D0",$word);
    $word = str_replace("Ñ","%D1",$word);
    $word = str_replace("Ò","%D2",$word);
    $word = str_replace("Ó","%D3",$word);
    $word = str_replace("Ô","%D4",$word);
    $word = str_replace("Õ","%D5",$word);
    $word = str_replace("Ö","%D6",$word);
    $word = str_replace("Ø","%D8",$word);
    $word = str_replace("Ù","%D9",$word);
    $word = str_replace("Ú","%DA",$word);
    $word = str_replace("Û","%DB",$word);
    $word = str_replace("Ü","%DC",$word);
    $word = str_replace("Ý","%DD",$word);
    $word = str_replace("Þ","%DE",$word);
    $word = str_replace("ß","%DF",$word);
    $word = str_replace("à","%E0",$word);
    $word = str_replace("á","%E1",$word);
    $word = str_replace("â","%E2",$word);
    $word = str_replace("ã","%E3",$word);
    $word = str_replace("ä","%E4",$word);
    $word = str_replace("å","%E5",$word);
    $word = str_replace("æ","%E6",$word);
    $word = str_replace("ç","%E7",$word);
    $word = str_replace("è","%E8",$word);
    $word = str_replace("é","%E9",$word);
    $word = str_replace("ê","%EA",$word);
    $word = str_replace("ë","%EB",$word);
    $word = str_replace("ì","%EC",$word);
    $word = str_replace("í","%ED",$word);
    $word = str_replace("î","%EE",$word);
    $word = str_replace("ï","%EF",$word);
    $word = str_replace("ð","%F0",$word);
    $word = str_replace("ñ","%F1",$word);
    $word = str_replace("ò","%F2",$word);
    $word = str_replace("ó","%F3",$word);
    $word = str_replace("ô","%F4",$word);
    $word = str_replace("õ","%F5",$word);
    $word = str_replace("ö","%F6",$word);
    $word = str_replace("÷","%F7",$word);
    $word = str_replace("ø","%F8",$word);
    $word = str_replace("ù","%F9",$word);
    $word = str_replace("ú","%FA",$word);
    $word = str_replace("û","%FB",$word);
    $word = str_replace("ü","%FC",$word);
    $word = str_replace("ý","%FD",$word);
    $word = str_replace("þ","%FE",$word);
    $word = str_replace("ÿ","%FF",$word);
    return $word;
}


function church_admin_in_array_r( $needle, $haystack, $strict = false) {
   if(!empty( $haystack)&&is_array( $haystack) )
   {
   	foreach ( $haystack as $item)
   		{
    	    if (( $strict ? $item === $needle : $item == $needle) || (is_array( $item) && church_admin_in_array_r( $needle, $item, $strict) ))
    	    {
        	    return true;
        	}
    	}
	}
    return false;
}

function church_admin_user_id_exists( $user)  {

    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user) );

    if( $count == 1)  { return true; }else{ return false; }

}


function ca_date_link( $year,$month,$day,$legend=FALSE,$class=NULL,$type=calendar,$facilities_id=NULL)
{
	if ( empty( $legend) )$legend=$day;
    if( $type=='facility-bookings')  {$section='facilities';}else{$section='calendar';}
	if(!empty( $class) )  {$class=' class="'.$class.'"';}else{$class='';}
	if(is_admin() )
    {
        $link=wp_nonce_url('admin.php?page=church_admin/index.php&amp;action='.$type.'&amp;section='.$section,'calendar');
        if(!empty( $facilities_id) )$link.='&amp;facilities_id='.intval( $facilities_id);
    }else{$link='';}
	$out='<form action="'.$link.'" method="POST">
	<input type="hidden" name="start_date" value="'.esc_html( $year.'-'.$month.'-'.sprintf('%02d', $day) ).'" /><button '.$class.'>'.esc_html( $legend).'</button></form>';
	return $out;

}



function church_admin_scaled_image_path( $attachment_id, $size = 'thumbnail') {
    $file = get_attached_file( $attachment_id, true);
    
    if (empty( $size) || $size === 'full') {
        // for the original size get_attached_file is fine
        return realpath( $file);
    }
    if (! wp_attachment_is_image( $attachment_id) ) {
        return false; // the id is not referring to a media
    }
    $info = image_get_intermediate_size( $attachment_id, $size);
   
    if (!is_array( $info) || ! isset( $info['file'] ) ) {
        return false; // probably a bad size argument
    }
    $path=realpath(str_replace(wp_basename( $file), $info['file'], $file) );
  
    $return=array('path'=>$path,'height'=>$info['height'],'width'=>$info['width'] );
    return $return;
}

function church_admin_detect_runtime_issues()
{
    global $wp_version;
    $error=array();
      if  (!in_array  ('curl', get_loaded_extensions() ))
      {
        $error['curl']=__('cURL is not enabled on your server, please contact your hosting company to get it enabled','church-admin');
      }
      if  (!in_array  ('gd', get_loaded_extensions() ))
      {
        $error['GD']=__('GD is not enabled on your server, which means QR codes cannot be generated. Please contact your hosting company to get it enabled','church-admin');
      }
      if (version_compare(phpversion(), '5.3.10', '<') )
      {
        $error['php']=__('Your PHP version is low and therefore is not safe and lacks some features needed by WordPress and Church Admin','church-admin');
      }
      if ( version_compare( $wp_version, '4.0', '<=' ) )
      {
        $error['wordpress']=__('Your WordPress version is very out of date. Please update now','church-admin');
      }
      //SPIT OUT ERRORS IF NEEDED
      if(!empty( $error) )
      {
        echo'<div class="notice notice-warning"><h2>'.esc_html( __('Issues detected by Church Admin plugin','church-admin' ) ).'</h2><p><strong>'.implode("<br>",$error).'</strong></div>';

      }


}
function church_admin_refresh_rolling_average()
{
	global $wpdb;
	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_attendance');
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			$avesql='SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM '.$wpdb->prefix.'church_admin_attendance WHERE `mtg_type`="'.esc_sql( $row->mtg_type).'" AND `service_id`="'.esc_sql( $row->service_id).'" AND `date` >= DATE_SUB("'.esc_sql( $row->date).'",INTERVAL 52 WEEK) AND `date`<= "'.esc_sql( $row->date).'"';
    		$averow=$wpdb->get_row( $avesql);
    		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_attendance SET rolling_adults= "'.(int)$averow->rolling_adults.'",rolling_children= "'.(int) $averow->rolling_children.'" WHERE attendance_id="'.(int) $row->attendance_id.'"');
		}
	}

}

function church_admin_whosin_kidswork_array()
{
		global $wpdb;
		$kidswork=array();
		//select GROUPS

			$results=$wpdb->get_results('SELECT a.*,a.id AS kidswork_id, b.ministry FROM '.$wpdb->prefix.'church_admin_kidswork a  LEFT JOIN '.$wpdb->prefix.'church_admin_ministries b ON a.department_id=b.ID ORDER BY youngest DESC');
			if(!empty( $results) )
			{
					foreach( $results AS $row)
					{
							$kidswork[$row->kidswork_id]=array('name'=>$row->group_name,'youngest'=>$row->youngest,'oldest'=>$row->oldest);
							//get kids in that group
							$sql='SELECT people_id,household_id,kidswork_override FROM '.$wpdb->prefix.'church_admin_people WHERE  (kidswork_override="'.esc_sql( $row->id).'" OR ((date_of_birth<"'.$row->youngest.'" AND date_of_birth>"'.$row->oldest.'") AND kidswork_override=0 ) )';

							$kids=$wpdb->get_results( $sql);
							if(!empty( $kids) )
							{
								foreach( $kids AS $kid)
								{
										//get parents of that kid
										$parents=array();
										$parents_result=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int) $kid->household_id.'" AND people_type_id=1');
										if(!empty( $parents_result) )
										{
											foreach( $parents_result AS $parent)
											{
													$parents[]=$parent->people_id;
											}
										}
										$kidswork[$row->kidswork_id]['children'][]=array('people_id'=>(int)$kid->people_id,'household_id'=>(int) $kid->household_id,'parents'=>$parents);

								}
							}
					}
				}

				return $kidswork;

}
function church_admin_get_kids_groups( $people_id)
{
  //this function finds the kids work groups a parent has children in.
    $groups=array();//array of groups people_id is a parent of a child in that group
    $kidsGroups=church_admin_whosin_kidswork_array();
    if(!empty( $kidsGroups) )
    {
      foreach( $kidsGroups AS $group_id=>$kidsGroup)
      {
        if(!empty( $kidsGroup['children'] ) )
	    {
		  	foreach( $kidsGroup['children'] AS $kids)
          	{
            	if(in_array( $people_id,$kids['parents'] ) )$groups[]=$group_id;
          	}
		}
      }
    }
    //church_admin_debug("people_id: $people_id is a parent of kids in these groups \r\n".print_r( $groups,TRUE) );
    return $groups;
}
/**
 *
 * Returns member level of current user
 *
 * @author  Andy Moyle
 * @param    $member_type_id Comma separated ids
 * @return
 * @version  0.1
 *
 */
 function church_admin_user_member_level( $member_type_id)
 {
   global $wpdb,$current_user;
   wp_get_current_user();
   $permission=FALSE;
   $member_type_ids=explode(',',$member_type_id);
   if(is_user_logged_in() )
   {
     $person_member_type_id=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int) $current_user->ID.'"');
     if ( empty( $member_type_id) )$permission=TRUE;
     elseif(!empty( $member_type_id) && ((is_array( $member_type_ids)&& in_array( $person_member_type_id,$member_type_ids) )||$member_type_id=='#') )  {$permission=TRUE;}
        
   }
   return $permission;
 }


function church_admin_head_of_household_tidy( $household_id)
{
    //church_admin_debug('****** church_admin_head_of_household_tidy ********');
    //church_admin_debug('Called by: '. debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function']);
    //church_admin_debug('Household_id '.$household_id);
	global $wpdb;
	/******************************************************************
		*
		* Make sure there is a head of household and tidy up if messy
		*
		*******************************************************************/
		$check=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$household_id.'"');
        //church_admin_debug($wpdb->last_query);
        if(!empty( $check)){
            //church_admin_debug($check);
        }
		if(!empty( $check) && $wpdb->num_rows>1)
		{
            //church_admin_debug('more than one head of household so reset');
			//more than one head of household so reset
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=0 WHERE household_id="'.(int)$household_id.'"');
            //church_admin_debug($wpdb->last_query);
			unset( $check);
		}
		if ( empty( $check) )
		{
			$people_order=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_order=1 AND household_id="'.(int)$household_id.'" ORDER BY people_id ASC');
            //church_admin_debug($wpdb->last_query);
			if( $people_order)
			{//people_order set and more than 1 is 1
				if( $wpdb->num_rows>1)
				{
                    //church_admin_debug('more than one entry has people order 1 so tidy that first');
					//more than one entry has people order 1 so tidy that first
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET people_order=0 WHERE household_id="'.(int)$household_id.'"');
                    //church_admin_debug($wpdb->last_query);
					$x=1;
					foreach( $people_order AS $people)
					{
						$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET people_order="'.(int)$x.'" WHERE people_id="'.esc_sql( $people->people_id).'"');
						//church_admin_debug($wpdb->last_query);
                        $x++;
					}
				}
				//set household_id for frst person
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE household_id="'.(int)$household_id.'" AND people_order=1');
                //church_admin_debug($wpdb->last_query);
			}
			else
			{
                //church_admin_debug('no people order set or head of household_id');
				//no people order set or head of household_id
				$people=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE  household_id="'.(int)$household_id.'" ORDER BY people_id ASC');
                //church_admin_debug($wpdb->last_query);
				if(!empty( $people) )
				{
                    
					$x=1;
					foreach( $people AS $person)
					{
						if( $x==1)  {
                            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1, people_order=1 WHERE people_id="'.(int) $person->people_id.'"');
                            //church_admin_debug($wpdb->last_query);
                        }
						else{
                            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET  people_order='.$x.' WHERE people_id="'.(int)$person->people_id.'"');
                            //church_admin_debug($wpdb->last_query);
                        }
						$x++;
					}
				
				}
			}
		}
    $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$household_id.'"');
    //church_admin_debug($wpdb->last_query);
    return $people_id;
    //church_admin_debug('******END OF  church_admin_head_of_household_tidy ********');
}



function church_admin_shortcodes_list()
{
	global $wpdb,$wp_locale;
	//shortcodes
	echo'<h2>'.esc_html( __('Shortcodes','church-admin' ) ).'</h2>';
	echo '<h3>'.esc_html( __('Communications','church-admin' ) ).'</h3>';
	//classes
	echo'<h3>'.esc_html( __('Classes','church-admin' ) ).'</h3>';
	echo'<p>[church_admin type="classes" today=TRUE] '.esc_html( __('displays a list of classes, today (remove today=TRUE for current classes). Logged in users can book in. Logged in Class leaders can check in students','church-admin' ) ).'</p>';
    //calendar
    echo'<h3>'.esc_html( __('Calendar','church-admin' ) ).'</h3>';
    //calendar
    echo'<p>[church_admin type="calendar-list" days="28" category="1,2,3"] '.esc_html( __('displays a list of calendar events for the next 28 days from (optional) 1,2,3 categories','church-admin' ) ).'</p>';
    echo'<p>[church_admin type="calendar" style="old"] '.esc_html( __('displays a monthly calendar table','church-admin' ) ).'</p>';
    echo'<p>[church_admin type="calendar" ] '.esc_html( __('displays a day to view calendar','church-admin' ) ).'</p>';
    echo '<p>'.esc_html( __('[church_admin type="calendar-list" category="1,2,3" weeks="4"] shows calendar events from categories 1,2 and 3 for the next 4 weeks','church-admin' ) ).'</p>';
    $results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
    if( $results)
    {
    	echo'<table><thead><tr><th>'.esc_html( __('Shortcode','church-admin' ) ).'</th><th>Category</th></tr></thead><tbody>';
        foreach( $results AS $row)
        {
             $shortcode='<strong>[church_admin type="calendar-list" category="'.esc_html( $row->cat_id).'" weeks="4"]</strong>';
            echo'<tr><th scope="row">'.$shortcode.'</th><td>'.esc_html(sprintf(__('Calendar List by Category %1$s','church-admin' ) ,esc_html( $row->category) )).'</td></tr>';
        }
        echo'</tbody></table>';
    }
	//directory

    echo'<h3>'.esc_html( __('Directory','church-admin' ) ).'</h3>';
    echo'<p>'.esc_html( __('The directory shortcode is [church_admin type="address-list" member_type_id="#" photo="1" map="1" site_id="0" pdf="1"]','church-admin' ) ).'</strong></p>';
		  echo'<p>'.esc_html( __('pdf=0 will not display PDF links, pdf=1 displays standard PDF, pdf=2 displays alternate version ','church-admin' ) ).'</p>';
		echo'<p>'.esc_html( __('photo=1 will display a thumbnail if one has been uploaded','church-admin' ) ).'</p>';
    echo'<p>'.esc_html( __('site=0 will display people from all sites, or if comma separated numbers used individual sites.','church-admin' ) ).'</p>';
	echo'<p>'.esc_html( __('map=1 shows a map for households where you have updated location on the google map when editing.','church-admin' ) ).' </p>';
    echo'<p>'.esc_html( __('Member type can include more than one member type separated with commas e.g.:','church-admin' ) ).'<strong>[church_admin type=address-list member_type_id=1,2 map=1 photo=1]</strong></p>';
    echo'<p>'.esc_html( __('kids=0 will stop children being shown','church-admin' ) ).'.</p>';
    echo'<p>'.esc_html( __('loggedin=TRUE makes the page available to logged in users only','church-admin' ) ).'</p>';
    echo'<p>'.esc_html( __("updateable=FALSE disables the edit link on each entry for admins and logged in user's entry",'church-admin' ) ).'</p>';

    $member_types=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_member_types ORDER BY member_type_id');
    if( $member_types)
    {
        echo '<p>'.esc_html( __('These are your current member types','church-admin' ) ).'</p>';
        foreach( $member_types AS $row)
        {
            echo'<p><label>'.esc_html( $row->member_type).': </label>member_type_id='.intval( $row->member_type_id).'</p>';
        }
    }
	echo '<h3>'.esc_html( __('Follow Up funnels','church-admin' ) ).'</h3>';
	echo '<p>'.esc_html( __('[church_admin type="follow-up"] display recent people activity and follow up actions menu','church-admin' ) ).'</p>';
    echo'<h3>'.esc_html( __('Names','church-admin' ) ).'</h3>';
    echo'<p>'.esc_html( __('[church_admin type=names member_type_id=# people_types=#] displays just names','church-admin' ) ).'</p>';
     echo'<p>'.esc_html( __('people_types can be "all","adults","teens","children" or a combination separated by a comma','church-admin' ) ).'</p>';

    //media
    echo'<h3>'.esc_html( __('Media','church-admin' ) ).'</h3>';
    echo'<p><strong>[church_admin type=podcast] </strong>'.esc_html( __('Lists all sermons','church-admin' ) ).'</p>';
	echo'<p><strong>[church_admin type=podcast most_popular=FALSE] </strong>'.esc_html( __('Lists all sermons and turns off Most Popular tab','church-admin' ) ).'</p>';
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series');
    if( $results)
    {//results
    	echo'<table class="widefat striped">';
    	echo'<thead><tr><th>'.esc_html( __('Series Name','church-admin' ) ).'</th><th>'.esc_html( __('Number of sermons','church-admin' ) ).'</th><th>'.esc_html( __('Shortcode'.'church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('Series Name','church-admin' ) ).'</th><th>'.esc_html( __('Number of sermons','church-admin' ) ).'</th><th>'.esc_html( __('Shortcode'.'church-admin' ) ).'</th></tr></tfoot><tbody>';
    	foreach ( $results AS $row)
    	{
    		$files=$wpdb->get_var('SELECT count(*) FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE series_id="'.(int)$row->series_id.'"');
            if(!$files)$files="0";
    		echo'<tr><td>'.esc_html( $row->series_name).'</td><td>'. (int)$files .'</td><td>[church_admin type="podcast" series_id="'.(int)$row->series_id.'"]</td></tr>';
    	}
    	echo'</tbody></table>';
    }
    //member map
    echo'<h3>'.esc_html( __('Member Map','church-admin' ) ).'</h3>';
    echo'<p>'.esc_html( __('[church_admin_map member_type_id="#" zoom="13" small_group="1"]- zoom is Google map zoom level, small_group=1 for different colours for small groups, 0 for all in red','church-admin' ) ).'</p>';
    //ministries
    echo'<h3>'.esc_html( __('Ministries','church-admin' ) ).'</h3>';
    echo'<p><strong>[church_admin type="ministries" ministry_id=# member_type_id="#"] </strong>'.esc_html( __('Lists people doing various ministries - just specify ministry_ids','church-admin' ) ).'</p>';
    $min=get_option('church_admin_ministries');
    if(!empty( $min) )  {
    	foreach( $min AS $id=>$ministry) echo'<p>'.esc_html(sprintf(__( '"%1$s" has id %2$s.', 'church-admin' ), $ministry, (int)$id )).'</p>';

    }
    echo'<p>'.esc_html( __('[church_admin type="kidswork"] shows the childrens groups','church-admin' ) ).'.</p>';
	//volunteers
    echo'<h3>'.esc_html( __('Online serving/volunteer application','church-admin' ) ).'</h3>';
	echo'<p>'.esc_html( __('[church_admin type="volunteer"] allows logged in users to volunteer for ministries where the online volunteer checkbox is checked','church-admin' ) ).'.</p>';
    //recent
	echo'<h3>'.esc_html( __('Recent Visitors','church-admin' ) ).'</h3>';
	echo'<p><strong>[church_admin type="recent" member_type_id="#"] </strong>'.esc_html( __('Lists your recent visitors - just specify member_types_ids','church-admin' ) ).'</p>';
    //small groups
    echo'<h3>'.esc_html( __('Small groups','church-admin' ) ).'</h3>';
    echo'<p><strong>[church_admin type="small-groups-list" map="1"]</strong>'.esc_html( __(' lists all your small group\'s details in map form (map=1)or as a list (map=0)','church-admin' ) ).'</p>';
    echo'<p><strong>[church_admin type="small-groups" member_type_id="#" ]</strong>'.esc_html( __('lists all your small groups and their members for a specific member type','church-admin' ) ).'</p>';
    echo'<p>'.esc_html( __('For the small-groups shortcode you can add loggedin=TRUE and restricted=TRUE to only show groups the user is in or leading','church-admin' ) ).'</p>';

    //rotas
    echo'<h3>'.esc_html( __('Schedules','church-admin' ) ).'</h3>';
    echo'<p><strong>[church_admin type="my_rota"]</strong>'.esc_html( __(' shows a logged in user their schedule involvement.','church-admin' ) ).'</p>';
    echo'<p><strong>[church_admin type="rota" service_id="1"]</strong>'.esc_html( __(' lists the upcoming schedule for a particular service','church-admin' ) ).'</p>';

    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_id');
    if( $results)
    {
        echo '<p>'.esc_html( __('These are your current services','church-admin' ) ).'</p>';
        foreach( $results AS $row)
        {
            if(!(church_admin_int_check( $row->service_day)&&$row->service_day>=0 &&$row->service_day<=7) )$row->service_day=0;

            	echo'<p><label>'.esc_html( $row->service_name).' on '.$wp_locale->get_weekday( $row->service_day).' at '.esc_html( $row->service_time).' </label>service_id='.intval( $row->service_id).'</p>';

        }
    }

    //user registration
    echo'<h3>'.esc_html( __('User Registration','church-admin' ) ).'</h3>';
    echo'<p><strong>[church_admin_register create_user=FALSE admin_email=TRUE] </strong></p>';
    echo'<p>'.esc_html( __('This shortcode allows new people to register and logged in users to update their own entry.','church-admin' ) ).'</p>';
     echo'<p>'.esc_html( __('create_user=TRUE will create a subscriber user for each valid email address','church-admin' ) ).'</p>';
      echo'<p>'.esc_html( __('admin_email=TRUE lets the admin email know that a new address entry has been created','church-admin' ) ).'</p>';
    echo '<p> exclude="middle-name,nickname,prefix,date-of-birth,marital-status,image,small-groups,classes,socials,ministries,mobile,gender,custom" '.esc_html( __('allows you to exclude and or all of those fields from the form','church-admin' ) ).'</p>';
    //recent activity
    echo'<h3>'.esc_html( __('Recent Directory Activity','church-admin' ) ).'</h3>';
    echo'<p><strong>[church_admin_recent]</strong></p>';



	//Attendance
	 echo'<h3>'.esc_html( __('Attendance','church-admin' ) ).'</h3>';
    echo'<p><strong>[church_admin type="graph" width="900" height="500"]</strong> - '.esc_html( __('displays graph image 900x500px;','church-admin' ) ).'</p>';
    //Birthdays
	echo'<h3>'.esc_html( __('Birthdays','church-admin' ) ).'</h3>';
	echo'<p><strong>[church_admin type="birthdays" member_type_id="#" days="#" show_age=FALSE people_type_id="#"]</strong> - '.esc_html( __('displays upcoming birthdays for the next # days for member_types_ids # and people_type_ids=#. show_age=FALSE stops the year and age displaying!','church-admin' ) ).'</p>';
	//Restricted content
	echo'<h3>'.esc_html( __('Restricted Content','church-admin' ) ).'</h3>';
	echo'<p><strong>[church_admin type="restricted" member_type_id="#"]'.esc_html( __('Some Content','church-admin' ) ).'[/church_admin]</strong> - '.esc_html( __('restrictes the content to certain member_types_ids #, which can be comma separated e.g. 1,2,3','church-admin' ) ).'</p>';
	if( $member_types)
	{
			echo '<p>'.esc_html( __('These are your current member types','church-admin' ) ).'</p>';
			foreach( $member_types AS $row)
			{
					echo'<p><label>'.esc_html( $row->member_type).': </label>member_type_id='.intval( $row->member_type_id).'</p>';
			}
	}
}

/*********************************************************
*
* Correctly add $months months to datetime object $date
*
*********************************************************/
function church_admin_addMonths( $date, $months)
{
    $years = floor(abs( $months / 12) );
    $leap = 29 <= $date->format('d');
    $m = 12 * (0 <= $months?1:-1);
    for ( $a = 1; $a < $years;++$a) {
        $date = addMonths( $date, $m);
    }
    $months -= ( $a - 1) * $m;
   
    $init = clone $date;
    if (0 != $months) {
        $modifier = $months . ' months';
       
        $date->modify( $modifier);
        if ( $date->format('m') % 12 != (12 + $months + $init->format('m') ) % 12) {
            $day = $date->format('d');
            $init->modify("-{$day} days");
        }
        $init->modify( $modifier);
    }
   
    $y = $init->format('Y');
    if ( $leap && ( $y % 4) == 0 && ( $y % 100) != 0 && 28 == $init->format('d') ) {
        $init->modify('+1 day');
    }
    return $init;
}


function church_admin_is_post_type( $type)  {
    global $wp_query;
    if( $type == get_post_type() ) 
        return true;
    return false;
}



function church_admin_generateVideoEmbedUrl( $url)  {
    
    //This is a general function for generating an embed link of an FB/Vimeo/Youtube Video.
    $finalUrl = $image = $videoId = NULL;


    if(strpos( $url, 'facebook.com/') !== false) {
        //it is FB video
        $finalUrl.='https://www.facebook.com/plugins/video.php?href='.rawurlencode( $url).'&show_text=1&width=200';
    }else if(strpos( $url, 'vimeo.com/') !== false) {
        //it is Vimeo video
        $videoId = explode("vimeo.com/",$url)[1];
        if(strpos( $videoId, '&') !== false)  {
            $videoId = explode("&",$videoId)[0];
        }
        $finalUrl.='https://player.vimeo.com/video/'.$videoId;
        $image = ca_get_vimeo_data_from_id( $videoId, 'thumbnail_url' );
    }
    if(empty($finalUrl))
    {
        //probably YouTube
        /***********************************************************************************************************
        * I've adjusted regex from https://www.geeksforgeeks.org/how-to-get-youtube-video-id-with-php-regex/ to 
        * include the new style share link https://www.youtube.com/live/l18MjuLZcCo?feature=share
        ***********************************************************************************************************/
        preg_match_all("#(?<=v=|v\/|vi=|vi\/|youtu.be\/|youtube.com\/live\/)[a-zA-Z0-9_-]{11}#", $url, $match);
        if( !empty( $match['0']['0'] ) ){
           $videoId = $match['0']['0'];
           if ( empty( $videoId) )$videoId=NULL;
            $image=null;
            if( $videoId){
                $image='https://img.youtube.com/vi/'.$videoId.'/0.jpg'; 
            }
            $finalUrl = 'https://www.youtube.com/embed/'.$videoId;
        }
    }
    if(empty($finalUrl)){
        //just pass it through as not YT, FB or Vimeo
        $finalUrl = $url;
        $videoId= null;
        $image = null;
    }
    $output=array('embed'=>$finalUrl,'id'=>$videoId,'image'=>$image);
    return $output;
}

function ca_get_vimeo_data_from_id( $video_id, $data ) {
    if(empty($video_id))return null;
	$request = wp_remote_get( 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/' . $video_id );
	
	$response = wp_remote_retrieve_body( $request );
	if(empty($video_id))return null;
	$video_array = json_decode( $response, true );
	if(empty($video_array['data'])){return null;}
	return $video_array[$data];
}

function church_admin_youtube_views_api( $embed_id)
{
   //church_admin_debug('church_admin_youtube_views_api: ' . $embed_id);
    $views='';
    $google_api=get_option('church_admin_google_api_key');
    if(!empty( $google_api)&&!empty( $embed_id) )
     {
            $args = array('headers' => array( 'Referer' => site_url() ) );
            $url='https://www.googleapis.com/youtube/v3/videos?part=statistics&id='.esc_html( $embed_id).'&key='.$google_api;
            //church_admin_debug( $url);
           $response=wp_remote_get( $url,$args);
           $api_response =  json_decode(wp_remote_retrieve_body( $response ),TRUE);
           //church_admin_debug( $api_response);
           if(!empty( $api_response['items'] ) )$views=$api_response['items'][0]['statistics']['viewCount'];
     }
    return $views;
}



function church_admin_bible_audio_link( $holy_str,$version)
{
    /*********************************************************************
     * 
     * Returns array $book with keys passage,book,chapter, verses, url (audio)
     *
     * *******************************************************************/
    if ( empty( $version) )$version='ESV';
    if ( empty( $holy_str) )return FALSE;
    //church_admin_debug("**************\r\nFUNCTION church_admin_bible_audio_link $holy_str, $version");
    // split verses from book chapters
    $parts = preg_split('/\s*:\s*/', trim( $holy_str, " ;") );

    // init book
    $book = array('passage'=>'','book' => "", 'chapter' => "", 'verses' => array() );

    // $part[0] = book + chapter, if isset $part[1] is verses
    if(isset( $parts[0] ) )
    {
      // 1.) get chapter
      if(preg_match('/\d+\s*$/', $parts[0], $out) ) {
        $book['chapter'] = rtrim( $out[0] );
      }

      // 2.) book name

        $searchBook = trim(preg_replace('/\d+\s*$/', "", $parts[0] ) );
      //church_admin_debug('Search book: '.$searchBook);
        $book['book'] =church_admin_bible_gateway_books( $searchBook);    
        
    }

    // 3.) verses
    if(isset($parts[1])) {
        $book['verses'] = preg_split('~\s*,\s*~', $parts[1]);
      }
      
    $book['passage'] = $book['book'].' '.$book['chapter'];
    if(!empty($book['verses'])){
        $book['passage'] .= ':'.implode(",",$book['verses']);
    }
    switch( $version)
    {
            
        case 'ESV':case'ESVUK':
            $book['url']='https://www.biblegateway.com/audio/mclean/esv/'.$book['book'].':'.$book['chapter'];
                $book['linkText']='ESV audio';
        break;
        case 'NIV':case'NIVUK' :                    $book['url']='https://www.biblegateway.com/audio/mclean/niv/'.$book['book'].':'.$book['chapter'];
            $book['linkText']='NIV audio';
        break;
        case 'NASB':    $book['url']='https://www.biblegateway.com/audio/mcconachie/nasb/'.$book['book'].':'.$book['chapter'];
            $book['linkText']='NASB audio';
        break;
        case 'NVI':
            $book['url']='https://www.biblegateway.com/audio/single/nvi/'.$book['book'].':'.$book['chapter'];
            $book['linkText']='Nueva Versión Internacional audio';
        break;
        case 'NTLR':
            $book['url']='https://www.biblegateway.com/audio/biblica/ntlr/'.$book['book'].':'.$book['chapter'];
            $book['linkText']='Nouă Traducere În Limba Română';
        break;
        case 'NVIPT':
            $book['url']='https://www.biblegateway.com/audio/biblica/nvi-pt/'.$book['book'].':'.$book['chapter'];
            $book['linkText']='Nova Versão Internacional';
        break;
    }
    //church_admin_debug(print_r( $book,TRUE) );
    return $book;
}
/*************************************************
*
*   This function returns the abbrev biblegateway.com is looking for
*
**************************************************/
function church_admin_bible_gateway_books( $book)
{
    //church_admin_debug("**************\r\nFUNCTION church_admin_bible_gateway_books");
    $books=array("Gen"=>array('Genesis','Gen','Ge','Gn'),
       "Exod"=>array('Exodus','Exo','Ex','Exod'),
       "Lev"=>array('Leviticus','Lev','Le','Lv'),
       "Num"=>array('Numbers','Num','Nu','Nm','Nb'),
       "Deut"=>array('Deuteronomy','Deut','Dt'),
       "Josh"=>array('Joshua','Josh','Jos','Jsh'),
       "Judg"=>array('Judges','Judg','Jdg','Jg','Jdgs'),
        "Ruth"=>array('Ruth','Rth','Ru'),
       "1Sam"=>array('1 Samuel','1 Sam','1 Sa','1Samuel','1S','I Sa','1 Sm','1Sa','I Sam','1Sam','I Samuel','1st Samuel','First Samuel'), 
       "2Sam"=>array('2 Samuel','2 Sam','2 Sa','2S','II Sa','2 Sm','2Sa','II Sam','2Sam','II Samuel','2Samuel','2nd Samuel','Second Samuel'), 
       "1Kgs"=>array('1 Kings','1 Kgs','1 Ki','1K','I Kgs','1Kgs','I Ki','1Ki','I Kings','1Kings','1st Kgs','1st Kings','First Kings','First Kgs','1Kin'), 
       "2Kgs"=>array('2 Kings','2 Kgs','2 Ki','2K','II Kgs','2Kgs','II Ki','2Ki','II Kings','2Kings','2nd Kgs','2nd Kings','Second Kings','Second Kgs','2Kin'), 
       "1Chr"=>array('1 Chronicles','1 Chron','1 Ch','I Ch','1Ch','1 Chr','I Chr','1Chr','I Chron','1Chron','I Chronicles','1Chronicles','1st Chronicles','First Chronicles'), 
       "2Chr"=>array('2 Chronicles','2 Chron','2 Ch','II Ch','2Ch','II Chr','2Chr','II Chron','2Chron','II Chronicles','2Chronicles','2nd Chronicles','Second Chronicles'), "Ezra"=>array('Ezra','Ezra','Ezr'), 
       "Neh"=>array('Nehemiah','Neh','Ne'), 
       "Est"=>array('Esther','Esth','Es'), 
        "Job"=>array('Job','Job','Job','Jb'), 
       "Ps"=>array('Psalm','Pslm','Ps','Psalms','Psa','Psm','Pss'), "Prov"=>array('Proverbs','Prov','Pr','Prv'),
        "Eccl"=>array('Ecclesiastes','Eccles','Ec','Ecc','Qoh','Qoheleth'), 
       "Song"=>array('Song of Solomon','Song','So','Canticle of Canticles','Canticles','Song of Songs','SOS'), 
       "Isa"=>array('Isaiah','Isa','Is'), 
       "Jer"=>array('Jeremiah','Jer','Je','Jr'), 
       "Lam"=>array('Lamentations','Lam','La'), 
       "Eze"=>array('Ezekiel','Ezek','Eze','Ezk'), 
       "Dan"=>array('Daniel','Dan','Da','Dn'), 
       "Hos"=>array('Hosea','Hos','Ho'), 
       "Joel"=>array('Joel','Joel','Joe','Jl'), 
       "Amos"=>array('Amos','Amos','Am'), 
       "Obad"=>array('Obadiah','Obad','Ob'), 
    "Jonah"=>array('Jonah','Jnh','Jon'), 
       "Mic"=>array('Micah','Micah','Mic'), 
       "Nah"=>array('Nahum','Nah','Na'), 
       "Hab"=>array('Habakkuk','Hab','Hab'), 
       "Zeph"=>array('Zephaniah','Zeph','Zep','Zp'), 
       "Hag"=>array('Haggai','Haggai','Hag','Hg'), 
       "Zech"=>array('Zechariah','Zech','Zec','Zc'), 
       "Mal"=>array('Malachi','Mal','Mal','Ml'), 
       "Matt"=>array('Matthew','Matt','Mt'), 
       "Mark"=>array('Mark','Mrk','Mk','Mr'), 
       "Luke"=>array('Luke','Luk','Lk'), 
       "John"=>array('John','John','Jn','Jhn'), 
       "Acts"=>array('Acts','Acts','Ac'), 
    "Rom"=>array('Romans','Rom','Ro','Rm'), 
       "1Cor"=>array('1 Corinthians','1 Cor','1 Co','I Co','1Co','I Cor','1Cor','I Corinthians','1Corinthians','1st Corinthians','First Corinthians'), 
       "2Cor"=>array('2 Corinthians','2 Cor','2 Co','II Co','2Co','II Cor','2Cor','II Corinthians','2Corinthians','2nd Corinthians','Second Corinthians'), 
       "Gal"=>array('Galatians','Gal','Ga'), 
       "Eph"=>array('Ephesians','Ephes','Eph'), 
       "Phil"=>array('Philippians','Phil','Php'), 
       "Col"=>array('Colossians','Col','Col'), 
       "1Thess"=>array('1 Thessalonians','1 Thess','1 Th','I Th','1Th','I Thes','1Thes','I Thess','1Thess','I Thessalonians','1Thessalonians','1st Thessalonians','First Thessalonians'), 
       "2Thess"=>array('2 Thessalonians','2 Thess','2 Th','II Th','2Th','II Thes','2Thes','II Thess','2Thess','II Thessalonians','2Thessalonians','2nd Thessalonians','Second Thessalonians'), 
       "1Tim"=>array('1 Timothy','1 Tim','1 Ti','I Ti','1Ti','I Tim','1Tim','I Timothy','1Timothy','1st Timothy','First Timothy'), 
       "2Tim"=>array('2 Timothy','2 Tim','2 Ti','II Ti','2Ti','II Tim','2Tim','II Timothy','2Timothy','2nd Timothy','Second Timothy'), 
       "Titus"=>array('Titus','Titus','Tit'), 
       "Phlm"=>array('Philemon','Philem','Phm'), 
       "Heb"=>array('Hebrews','Hebrews','Heb'), 
       "Jas"=>array('James','James','Jas','Jm'), 
       "1Pet"=>array('1 Peter','1 Pet','1 Pe','I Pe','1Pe','I Pet','1Pet','I Pt','1 Pt','1Pt','I Peter','1Peter','1st Peter','First Peter'), 
       "2Pet"=>array('2 Peter','2 Pet','2 Pe','II Pe','2Pe','II Pet','2Pet','II Pt','2 Pt','2Pt','II Peter','2Peter','2nd Peter','Second Peter'), 
       "1John"=>array('1 John','1 John','1 Jn','I Jn','1Jn','I Jo','1Jo','I Joh','1Joh','I Jhn','1 Jhn','1Jhn','I John','1John','1st John','First John'), 
       "2John"=>array('2 John','2 John','2 Jn','II Jn','2Jn','II Jo','2Jo','II Joh','2Joh','II Jhn','2 Jhn','2Jhn','II John','2John','2nd John','Second John'), 
       "3John"=>array('3 John','3 John','3 Jn','III Jn','3Jn','III Jo','3Jo','III Joh','3Joh','III Jhn','3 Jhn','3Jhn','III John','3John','3rd John','Third John'), 
       "Jude"=>array('Jude','Jude','Jud'), 
       "Rev"=>array('Revelation','Rev','Re','The Revelation') ); 
    
    foreach( $books AS $key=>$bookArray)
    {
        
        if(in_array( $book,$bookArray) )
        {
            //church_admin_debug("Found $book and using $key");
            return $key;
        }
    }
    return $book;
}






//deprecated
/*
function church_admin_nth_day( $nth,$day,$date)
{
   //Updated 2020-01-01 because of translated plugin issues...
    //don't use wp_locale as doesn't play nicely with strtotime()
	$days=array(0=>'Sunday',1=>"Monday",2=>"Tuesday",3=>"Wednesday",4=>"Thursday",5=>"Friday",6=>"Saturday");
    $month=date('M',strtotime( $date) );
    $year=date('Y',strtotime( $date) );
    return date('Y-m-d',strtotime("$nth {$days[$day]} $month $year") );
}
*/

/**
*   nth_day_of_month(int $nbr, str $day, int $mon, int $year)
*   $nbr = nth weekday to find
*   $day = integer for day no
*   $rough_date = ISO date for caldulating from eg 2023-03-03
*   returns iso date or FALSE if issues or no date exists
*/
function church_admin_nth_day($nbr, $day, $rough_date){ 

    //check valid date
    if(empty($rough_date)||!church_admin_checkdate( $rough_date)){
        //invalid date
       
        return FALSE;
    }

    $date_spilt=explode("-",$rough_date);
    $year=$date_spilt[0];
    $month=$date_spilt[1];
 
    $date = mktime(0, 0, 0, $month, 0, $year);

    if($date == 0){ 
        //invalid date
      
       return(FALSE); 
    } 
    $days=array(0=>'Sunday',1=>"Monday",2=>"Tuesday",3=>"Wednesday",4=>"Thursday",5=>"Friday",6=>"Saturday");
     
    if(empty($days[$day])){ 
        //day number invalid
        
       return(FALSE); 
    }
 
    for($week = 1; $week <= $nbr; $week++){ 
       $date = strtotime("next $days[$day] ", $date); 
    }
    
    //check still in correct month
    $calculated_month=date('m',$date);
    if($calculated_month!=$month){
        // Not in month
        return FALSE;
    }
    if(empty($date))return FALSE;
    return date('Y-m-d',$date);

}




function church_admin_favourites_menu()
{
    return;
  
}

/***************************************************
*
*   Premium check and signup
*
****************************************************/

function church_admin_paypal_setup()    
{
    echo '<h2>'.esc_html( __('Payment Gateway Setup','church-admin' ) ).'</h2>';

    $licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
        return  church_admin_buy_app();
    }
    $currencies=array(
                        'USD'=>'US Dollar',
                        'GBP'   =>'Pounds Sterling',
                        'AUD'=>'Australian Dollar',
                        'CAD'=>'Canadian Dollar',
                        'CNY'=>'Chinese Renmenbi',
                        'CZK' =>'Check Krone',
                        'DKK' =>'Danish Krone',
                        'EUR'   => 'Euro',
                        'HKD'   =>  'Hong Kong Dollar',
                        'HUF'   =>  'Hungarian Forint',
                        'ILS'   =>'Israeli New Shequel',
                        'JPY'   =>'Japanese Yen',
                        'MYR'   =>'Malaysian Ringgit',
                        'MXN'   =>'Mexican Peso',
                        'NOK'   =>'Norwegian Krone',
                        'NZD'   =>'New Zealand Dollar',
                        'PHP'   =>'Philippine Peso',
                        'PLN'   =>'Polish Zloty',
                        'SGD'   =>'Singapore Dollar',
                        'SEK'   =>'Swedish Krona',
                        'CHF'   =>'Swiss Franc',
                        'TWD'   =>'Taiwanese Dollar',
                        'THB'   =>'Thao Baht'

    );
     $licence = get_option('church_admin_app_new_licence');;
    if(empty($licence))
    {
        //app purchase required
        echo'<h2>Buy the premium version</h2>';
        return;
    }
    if(!empty($_POST['save-gateway']) && church_admin_level_check('Giving')){
        //sanitize
        $sanitized=array();
        foreach($_POST AS $key=>$value){
            $sanitized[$key] = !empty($value)?sanitize_text_field( stripslashes( $value ) ):null;
        }
        
        //validate
        $errors=array();
        switch($sanitized['payment_gateway'])
        {
            case 'stripe':
                $payment_gateway = 'stripe';
            break;
            default:
            case 'paypal':
                $payment_gateway = 'paypal';
            break;  
        }

        //check currency
        if(empty($sanitized['paypal_currency']) || empty($currencies[ $sanitized['paypal_currency'] ] ) ){
            $errors[]=__('Invalid Currency','church-admin');
        }
        if($sanitized['payment_gateway']=='paypal' && (empty($sanitized['paypal_email']) || !is_email($sanitized['paypal_email']) )){$errors[]=__('Email not recognisable','church-admin');}
        $gift_aid =!empty( $sanitized['gift_aid'] )? 1 : 0;
        $show_in_app = !empty( $sanitized['gift_aid'] ) ? 1 : 0;
        if(empty($errors))
        {
            $church_admin_payment_gateway = array(
                'gateway' =>$payment_gateway,
                'stripe_public_key' =>$sanitized['stripe_public_key'],
                'stripe_secret_key' =>$sanitized['stripe_secret_key'], 
                'paypal_email'=>$sanitized['paypal_email'],
                'paypal_currency' =>$sanitized['paypal_currency'],
                'currency_symbol' => $sanitized['currency_symbol'],
                'show_in_app' => $show_in_app,
                'gift_aid' => $gift_aid
            );
            
            update_option('church_admin_payment_gateway',$church_admin_payment_gateway);
            echo '<div class="notice notice-success"><h2>'.esc_html( __('Payment gateway settings saved','church-admin' ) ).'</h2></div>';
        }
    }
   
        
    //do form
    $premium=get_option('church_admin_payment_gateway');
    
    if(!empty($errors)){
        echo'<p>'.esc_html( __('There were some errors','church-admin' ) ).'</p>';
        foreach($errors AS $key=>$error){
            echo'<p>'.esc_html($error).'</p>';
        }
    }
    if(empty($premium['gateway'])){$premium['gateway'] = 'paypal';}
    echo'<form action=""method="post">';
    echo'<h3>'.esc_html( __('Payment Gateway','church-admin') ).'</h3>';
    echo'<p><input type="radio" name="payment_gateway" class="payment_gateway" '.checked('stripe',$premium['gateway'],false).' value="stripe">&nbsp; Stripe</p>';
    echo'<p><input type="radio" name="payment_gateway" class="payment_gateway" '.checked('paypal',$premium['gateway'],false).'  value="paypal">&nbsp; PayPal</p>';
    echo'<div id="stripe" ';
    if($premium['gateway']=='paypal'){echo' style="display:none" '; }
    echo '>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Stripe public key','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="text" name="stripe_public_key" ';
    if(!empty($premium['stripe_public_key'])) echo ' value="'.esc_attr($premium['stripe_public_key']).'" ';
    echo'/></div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Stripe secret key','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="text" name="stripe_secret_key" ';
    if(!empty($premium['stripe_secret_key'])) echo ' value="'.esc_attr($premium['stripe_secret_key']).'" ';
    echo'/></div>';
    echo'</div><!--stripe settings-->';
    echo'<div id="paypal" ';
    if($premium['gateway']=='stripe'){echo' style="display:none" '; }
    echo '>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('PayPal Email address','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="email" name="paypal_email" ';
    if(!empty($premium['paypal_email'])) echo ' value="'.esc_attr($premium['paypal_email']).'" ';
    echo'/></div>';
   
    
    echo'</div><!--PayPal settings -->';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Currency','church-admin')).'</label>';
    echo'<select name="paypal_currency" class="church-admin-form-control">';
    $my_currency=!empty($premium['paypal_currency'])?$premium['paypal_currency']:'USD';
    
    foreach($currencies AS $code=>$name)
    {
        echo'<option value="'.esc_attr($code).'" '.selected($my_currency,$code,FALSE).'>'.esc_html($name).'</option>';
    }
    echo'</select></div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Currency symbol','church-admin')).'</label>';
    echo'<input class="church-admin-form-control" type="text" name="currency_symbol" ';
    if(!empty($premium['currency_symbol'])) echo ' value="'.esc_attr($premium['currency_symbol']).'" ';
    echo'/></div>';
    $show_in_app=!empty($premium['show_in_app'])?1:0;
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Show giving form in app (PayPal only currently)','church-admin')).'</label>';
    echo'<input type="checkbox" value=1 name="show_in_app" '.checked($show_in_app,1,false).'/></div>';
    $uk_gift_aid=!empty($premium['gift_aid'])?1:0;
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('UK Gift Aid form?','church-admin')).'</label>';
    echo'<input type="checkbox" value=1 name="gift_aid" '.checked($uk_gift_aid,1,false).'/></div>';
    echo'<p><input type="hidden" name="save-gateway" value=1/><input type="submit" class="button-primary" /></p></form>';
    
    echo'<script>
    jQuery(document).ready(function($){
        $("input[type=radio][name=payment_gateway]").change(function() {
            var gateway =$(".payment_gateway:checked").val();
            switch(gateway){
                case "paypal":
                    $("#paypal").show();
                    $("#stripe").hide();
                break;
                case "stripe":
                    $("#paypal").hide();
                    $("#stripe").show();
                break;
            }
        });
    });
    
    </script>';

}




function church_admin_app_licence_check()
{
    //church_admin_debug('**** church_admin_app_licence_check() *****');
    //church_admin_debug('Called by: '. debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function']);
    $one_month = 28*24*50*60;
    global $church_admin_version;

    //check trial 
    $trial = get_option('church_admin_trial');
    //church_admin_debug('Trial: '.$trial);
    if(!empty($trial) && time() > ($trial + $one_month)){   
         
            delete_option('church_admin_licence_checked');
            delete_option('church_admin_trial');
        
    }


    $licence = get_option('church_admin_app_new_licence');
    //church_admin_debug('Licence saved: '.$licence);
    $licence_checked = 0;
    $licence_checked = get_option('church_admin_licence_checked');
    
    if(!empty($licence) && $licence == 'no-sub'){
       
        $licence_checked=1;
    }

    if(empty($licence) && empty($licence_checked)){
       
        //give 28 day trial
        update_option('church_admin_app_new_licence','standard');
		update_option('church_admin_trial',time());
		update_option('church_admin_licence_checked',time());
        return;
    }



    
    if(time() > $licence_checked + $one_month ){
        
        //time to check licence again
        $url = 'https://www.churchadminplugin.com/?licence_check='.md5(site_url() ).'&url='.site_url().'&v='.$church_admin_version;
        //church_admin_debug($url);
        $response = wp_remote_get( esc_url_raw( $url ) );
        if ( is_array(  $response ) && ! is_wp_error(  $response ) )
        {
            $answer =(array)json_decode($response['body']);
            
            if(empty($answer)||$answer['licence'] =='no-sub'){
                church_admin_debug('No sub, revertingto free');
                update_option('church_admin_app_new_licence','free');
                update_option('church_admin_licence_checked',time());
        
                return 'free';
            }
            elseif($answer['licence'] == 'basic'){
               
                update_option('church_admin_app_new_licence','basic');
                update_option('church_admin_licence_checked',time());
                delete_option('church_admin_trial');
        
                return 'basic';
            }
            elseif($answer['licence']=='standard'){
              
                update_option('church_admin_app_new_licence','standard');
                update_option('church_admin_licence_checked',time());
                delete_option('church_admin_trial');
            
                return 'standard';
            }
            elseif($answer['licence']=='premium'){
               
                update_option('church_admin_app_new_licence','premium');
                $premium=array();
                $premium=get_option('church_admin_payment_gateway');
                if(empty($premium)){$premium=array();}
                $premium['licence']='subscribed';
                update_option('church_admin_payment_gateway',$premium);
                update_option('church_admin_app_id',(int)$answer['ID']);
                //update_option('church_admin_push_token',$answer['token']);
                update_option('church_admin_licence_checked',time());
            
                return 'premium';
            }
        }
        else{
            //couldn't connect, return what was stored
            church_admin_debug('Could not connect, so returning licence');
            return $licence;
        }
        
        

        
    }
    else
    {
        return $licence;
    }
   
    church_admin_debug('**** END church_admin_app_licence_check() *****');
  
}


function church_admin_current_donations( $people_id,$currYear,$currPledge)
{
    global $wpdb;
    
    $out='';
    $premium=get_option('church_admin_payment_gateway');
    $donations=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_giving WHERE people_id="'.(int)$people_id.'" AND YEAR(donation_date)="'.(int) $currYear.'" ORDER BY donation_date DESC');
        if(!empty( $donations) )
        {
            $total=0;
            $out.='<h3>'.esc_html( __('Your generosity so far this year','church-admin' ) ).'</h3><table><thead><tr><th>'.esc_html( __('Date','church-admin' ) ).'</th><th>'.esc_html( __('Amount','church-admin' ) ).'</th><th>'.esc_html( __('How given','church-admin' ) ).'</th><th>'.esc_html( __('Fund','church-admin' ) ).'</th></tr></thead><tbody>';
            foreach( $donations AS $donation)
            {
                $total+=$donation->gross_amount;
                $out.='<tr><td>'.mysql2date(get_option('date_format'),$donation->donation_date).'</td><td>';
                if(!empty( $premium['currency_symbol'] ) )$out.=$premium['currency_symbol'];
                $out.=number_format_i18n( $donation->gross_amount,2).'</td><td>'.esc_html( $donation->txn_type).'</td><td>'.esc_html( $donation->fund).'</td></tr>';
            }
            $out.='<tr><td><strong>'.esc_html( __('Total','church-admin' ) ).'</td><td>';
            if(!empty( $premium['currency_symbol'] ) )$out.=$premium['currency_symbol'];
            $out.=number_format( $total,2).'</td><td colspan=2>&nbsp;</td></tr>';
            if(!empty( $currPledge) )
            {
                $out.='<tr><td><strong>'.esc_html( __('Current Pledge','church-admin' ) ).'</td><td>';
                if(!empty( $premium['currency_symbol'] ) )$out.=$premium['currency_symbol'];
                $out.=number_format_i18n( $currPledge,2).'</td><td colspan=2>&nbsp;</td></tr>';
                $out.='<tr><td><strong>'.esc_html( __('Amount Outstanding','church-admin' ) ).'</td><td>';
                if(!empty( $premium['currency_symbol'] ) )$out.=$premium['currency_symbol'];
                $out.=number_format_i18n( $currPledge-$total,2).'</td><td colspan=2>&nbsp;</td></tr>';
            }
            $out.='</tbody></table>';
        }
    return $out;
}
/********************************************************
*
*   attempt to convert phone number to e.164 format
*
*******************************************************/
function church_admin_e164( $mobile)
{
    $mobile=str_replace(' ','',$mobile);
    $mobile=str_replace('-','',$mobile);
    $mobile=str_replace('(','',$mobile);
    $mobile=str_replace(')','',$mobile);
    $mobile=ltrim( $mobile,'0');//Europe
    $country=get_option('church_admin_sms_iso');
    //check if country is already at the start
    if(substr($mobile,0,1)!='+')
    {
        $e164cell='+'.$country.$mobile;
    }
    else
    {
        $e164cell=$mobile;
    }
    
    return $e164cell;
}
/********************************************************
*
*   spam check
*   returns TRUE for spam
*   $type = email,text
*******************************************************/
function church_admin_spam_check( $text,$type)
{
    if ( empty( $text) ) return TRUE;
    
    //church_admin_debug("*****************Spam Check \r\n Checking - $text and type $type");
    //check email type is an email
    if( $type=='email' &&!is_email( $text) )
    {
        //church_admin_debug('Should be an email');
        return TRUE;
    }    
    //look for links
    if(substr_count( $text, "http") > 0)
    {
        //church_admin_debug('Contains a link');
        return TRUE;
    }
        //check for spam words
    $needle=array('click here','Page 1 rankings','bitcoin','shemail','lesbian','gay','Make $1000','casino','teen photos','passive income','porn','bitcoin','viagra','fuck','penis','sex','visit your website','www.yandex.ru','сайт','products on this site','business directory','<script','onClick','boobs','tits','horny','all-night');
    foreach( $needle as $query) {
        if(strpos(strtoupper( $text), strtoupper( $query), 0) !== false)
        {
            //church_admin_debug('Spam words');
            return true; // stop on first true result
        }
    }
    return FALSE;
    
}


function church_admin_household_details_table($household_id)
{
	if(empty($household_id)){return null;}
	global $wpdb;
	$people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY people_order ASC');
	if(!empty( $people) )
	{
		$details='<h3 style="margin:20px 0px">'.esc_html( __( 'Household details','church-admin') ).'</h3>';
		$details.='<table style="margin:20px 0px;border-collapse:collapse;"><thead><tr style="border:1px solid"><th  style="border:1px solid;padding:3px;">'.esc_html( __( 'Name','church-admin') ).'</th><th style="border:1px solid;padding:3px;">'.esc_html( __( 'Cell phone','church-admin') ).'</th><th style="border:1px solid;padding:3px;">'.esc_html( __( 'Email','church-admin') ).'</th></tr></thead><tbody>';
		foreach( $people AS $person)
		{
			$name=array_filter(array( $person->first_name,$person->middle_name,$person->last_name) );
			$mobile=!empty( $person->mobile)?esc_html( $person->mobile):"";
			$email=!empty( $person->email)?esc_html( $person->email):"";


			$details.='<tr><td style="border:1px solid;padding:3px;">'.esc_html(implode(" ",$name) ).'</td><td style="border:1px solid;padding:3px;">'.$mobile.'</td><td style="border:1px solid;padding:3px;">'.$email.'</td></tr>';
		}
		$details.='</tbody></table>';
	}else{return null;}
	return $details;
}

/*************************************************************
*
*   Email confirmation send if no GDPR reason and $people_id
*
*************************************************************/
function church_admin_email_confirm( $people_id)
{
    //only send email confirm if no GDPR reason
    global $wpdb;
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    if(!empty( $person) &&!empty( $person->email)&& empty( $person->gdpr_reason) )
    {
        $template = get_option('church_admin_confirm_email_template');
        if(empty($template)){
            $template=array('message'=>'<p>Thank you for signing up at [SITE_URL]</p><pplease confirm your email address by clicking on [CONFIRM_LINK]</p><p>Thank you</p>',
            'from_name'=>get_bloginfo('name'),
            'from_email'=>get_option('church_admin_default_from_email')
            );
            update_option('church_admin_confirm_email_template',$template);


        }
        $message = $template['message'];
        $message=str_replace('[CONFIRM_LINK]', home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id),$message);
        $message=str_replace('[SITE_URL]',home_url(),$message);
        $message=str_replace('[CHURCH_NAME]',get_bloginfo('name'),$message);
        $message=str_replace('[CONFIRM_URL]',' <a href="'.home_url().'?confirm_email='.md5( $person->email).'&amp;people_id='.md5( $person->people_id).'">'.esc_html( __( 'Click to confirm','church-admin') ).'</a>',$message);
        $household_details = church_admin_household_details_table($person->household_id);
	    $message=str_replace('[HOUSEHOLD_DETAILS]','<p>&nbsp;</p>'.$household_details,$message);
        if(empty($template['from_name']))$template['from_name']=get_option('church_admin_default_from_name');
        if(empty($template['from_email']))$template['from_email']=get_option('church_admin_default_from_email');
        $message=str_replace('[EDIT_URL]','',$message);
        church_admin_email_send($person->email,$template['subject'],$message,$template['from_name'],$template['from_email'],null,null,null,TRUE);
        
    }
}


function church_admin_excerpt( $string,$length,$end='...')
{
    if(empty($string)){return ;}
    if(empty($length)){$length=250;}
    $string = strip_tags( $string);

    if (strlen( $string) > $length) {

        // truncate string
        $stringCut = substr( $string, 0, $length);

        // make sure it ends in a word so assassinate doesn't become ass...
        $string = substr( $stringCut, 0, strrpos( $stringCut, ' ') ).$end;
    }
    return $string;
}


function church_admin_gz( $source, $level = 9)  { 
    $dest = $source . '.gz'; 
    $mode = 'wb' . $level; 
    $error = false; 
    if ( $fp_out = gzopen( $dest, $mode) ) { 
        if ( $fp_in = fopen( $source,'rb') ) { 
            while (!feof( $fp_in) ) 
                gzwrite( $fp_out, fread( $fp_in, 1024 * 512) ); 
            fclose( $fp_in); 
        } else {
            $error = true; 
        }
        gzclose( $fp_out); 
    } else {
        $error = true; 
    }
    if ( $error)
        return false; 
    else
        return $dest; 
} 

if(!function_exists('array_to_object') ) {
  function array_to_object( $array = array() ) {
    if (!empty( $array) ) {
        $data = false;
        foreach ( $array as $akey => $aval) {
            $data -> {$akey} = $aval;
        }
        return $data;
    }
    return false;
}

}

/*****************************************************
*
* Returns 1st two adults in household
*
*****************************************************/
function church_admin_household_title( $household_id)
{
    global $wpdb;
    if ( empty( $household_id) )return NULL;
    $adults=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_type_id=1 AND household_id="'.(int)$household_id.'" ORDER BY head_of_household DESC LIMIT 2');
    if ( empty( $adults) )return NULL;
    $names=$title=array();
    foreach( $adults AS $row)
    {
        $fullLastName=implode(" ",array_filter(array( $row->prefix,$row->last_name) ));
        $names[$fullLastName][]=$row->first_name;
    }
    if ( empty( $names) )return NULL;
    foreach( $names AS $lastName=>$firstNames)
    {
        $title[]=implode(" & ",$names[$lastName] ).' '.$lastName;
    }
    return implode(" & ",$title);
}

/*****************************************************
*
* Warnings if homeurl and siteurl will cause app issues
*
*****************************************************/
function church_admin_url_check( $echo=TRUE)
{
    global $wpdb;
    // check if changed on wp-config with WP_HOME and WP_SITEURL constants but this would be triggered everytime while the constant is defined on wp-config
    $message='';
    if ( defined('WP_HOME') && '' != WP_HOME ) {
        
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'home' ) );
        $home = $row->option_value;
        if(WP_HOME!=$home)
        {
            
            $message='<h2  style="color:red">'.esc_html( __('Homepage URL issue','church-admin' ) ).'</h2><p>'.esc_html( __('You have a hard-coded value of the site homepage that is different from the setting in Dashboard>Settings>General','church-admin' ) ).'</p>';
            $message.='<p>'.esc_html( sprintf(__('WP_HOME is %1$s','church-admin' ) ,esc_url(WP_HOME) )).'</p>';
            $message.='<p>'.esc_html( sprintf(__('Settings value is %1$s','church-admin' ) ,esc_url( $home) )).'</p>';
        }
        
    }

    if ( defined('WP_SITEURL') && '' != WP_SITEURL ) {
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'siteurl' ) );
        $siteurl = $row->option_value;
        if(WP_SITEURL!=$siteurl)
        {
            $message.='<h2 style="color:red">'.esc_html( __('Site URL issue','church-admin' ) ).'</h2><p>'.esc_html( __('You have a hard-coded value of the site url that is different from the setting in Dashboard>Settings>General','church-admin' ) ).'</p>';
            $message.='<p>'.esc_html( sprintf(__('WP_SITEURL is %1$s','church-admin' ) ,esc_url(WP_SITEURL)) ).'</p>';
            $message.='<p>'.esc_html( sprintf(__('Settings value is %1$s','church-admin' ) ,esc_url( $siteurl)) ).'</p>';
        }
        
       
    }
    if(!church_admin_maybe_is_ssl() )$message.='<h2 style="color:red">'.esc_html( __('SSL issue','church-admin' ) ).'</h2><p>'.esc_html( __('The value in Dashboard>Settings>General Site Address must be https for the app to work on iOS and Android devices','church-admin' ) ).'</p>';
    $message.='<p>'.esc_html( sprintf(__('Site URL is %1$s','church-admin' ) ,esc_url(site_url() ))).'</p>';
    
   
    if ( empty( $echo) )  {
        echo $message;
    }else{
        return $message;
    }
}

function church_admin_maybe_is_ssl() {
    // cloudflare
    if ( ! empty( $_SERVER['HTTP_CF_VISITOR'] ) ) {
        $cfo = json_decode( $_SERVER['HTTP_CF_VISITOR'] );
        if ( isset( $cfo->scheme ) && 'https' === $cfo->scheme ) {
            return true;
        }
    }
 
    // other proxy
    if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
        return true;
    }
 
    return function_exists( 'is_ssl' ) ? is_ssl() : false;
}

function church_admin_first_step( $id=0)
{

    $out='<div id="ca-first-step'.(int)$id.'">';
               
    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Please start with your email address','church-admin' ) ).'</label>';
    $out.='<input type="email" id="ca-email-address'.(int)$id.'" class="church-admin-form-control"></div><p><button id="ca-next-step'.(int)$id.'" class="btn btn-success">'.esc_html( __("Next &raquo;",'church-admin' ) ).'</button></p></div>';
    
    $out.='<div id="ca-login'.(int)$id.'" style="display:none">';
    $out.='<p>'.esc_html( __('It looks like you are already registed, please login to continue','church-admin' ) ).'</p>';
    $out.=wp_login_form(array('echo'=>FALSE) );
    $out.='<a href="'.esc_url( wp_lostpassword_url( get_permalink() ) ).'" alt="'.esc_html( __( 'Lost Password', 'church-admin' )).'">'.esc_html( __( "I've forgotten my password", 'church-admin' )).'</a></p></div>';
    $out.='<script type="text/javascript">jQuery(function( $)  {  
       
        $("#ca-next-step'.(int)$id.'").click(function()  {
        console.log("clicked '.(int)$id.'");
            var email=$("#ca-email-address'.(int)$id.'").val();
            $("#ca-email-address'.(int)$id.'").val(email);
            var id="'.(int)$id.'";
            var nonce="'.wp_create_nonce('email-checker').'";
            console.log(email);
            var args = {"action": "church_admin","method": "email-checker","email": email,"nonce":nonce,"id":id};
            console.log(args);
            $.getJSON({
                url: ajaxurl,
                type: "post",
                data:  args,
                success: function(response) {
                    console.log(response);
                    if(response.found)
                    {
                        console.log("login");
                        $("#form'.(int)$id.'").hide();
                        $("#ca-login'.(int)$id.'").show();
                        $("#ca-first-step'.(int)$id.'").hide();
                    }
                    else
                    {
                        $("#form'.(int)$id.'").show();
                        $(".funky-bit").val(response.nonce);
                        $(".ca-email").val(response.email);
                        $("#ca-login'.(int)$id.'").hide();
                        $("#ca-first-step'.(int)$id.'").hide();
                        
                    }
                    
                }
            });
        });
        });</script>'."\r\n";

    return $out;

}
function church_admin_formatted_name_from_user( $userID)
{
    global $wpdb;
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$userID.'"');
    if( $person) return church_admin_formatted_name( $person);
    else return NULL;
}

function church_admin_formatted_name( $person)
{
    if ( empty( $person) )return;
    $use_title = get_option('church_admin_use_title');
    if($use_title && !empty($row->title)){$title = $person->title;}else{$title = '';}
    $array = array();
    if(!empty($title)){$array[]=$title;}
    if(!empty($person->first_name)){$array[]=$person->first_name;}
    if(!empty($person->prefix)){$array[]=$person->prefix;}
    if(!empty($person->last_name)){$array[]=$person->last_name;}
    return implode(" ",$array);
}


function church_admin_mobile_menu()
{
    global $church_admin_url,$church_admin_menu;
    $modules=get_option('church_admin_modules');
    //church_admin_debug(print_r( $church_admin_menu,TRUE) );
    $parent='people';
    $out='<form action="admin.php" method="GET" id="church-admin-mobile-menu"><input type="hidden" name="page" value="church_admin/index.php" />';
    $out.=wp_nonce_field('church-mobile-menu-action','_wpnonce',TRUE);
    $out.='<select name="action" class="church-mobile-menu-action">';
    if ( empty( $_GET['action'] ) )  {$out.='<option>'.esc_html( __('Church Admin Menu','church-admin' ) ).'</option>';}
    $out.='<optgroup label="'.esc_html( __('People','church-admin' ) ).'">';
    foreach( $church_admin_menu AS $menuID=>$menuItem)    
    {

        $modules['Settings']=TRUE;
        $modules['App']=TRUE;
        if( $menuItem['parent']!=$parent)
        {
            //is a parent so add an optgroup
            $out.='</optgroup><optgroup label="'.esc_html( $menuItem['title'] ).'">';
        }
        if(!empty( $modules[$menuItem['module']] )&& church_admin_level_check( $menuItem['level'] ) )
        {
        if(!empty( $_GET['action'] ) )  {
            $action=sanitize_text_field( stripslashes( $_GET['action'] ) );
        }else{
            $action='';
        }
           $out.='<option value="'.esc_html( $menuID).'" '.selected( $menuID,$action,FALSE).'>'.esc_html( $menuItem['title'] ).'</option>';           
            
        }
        $parent=$menuItem['parent'];
    }
    $out.='<optgroup></select>';
    $out.='</form><script> jQuery(document).ready(function( $)  {

            $(".church-mobile-menu-action").change(function()  {
                console.log("Submit");
                var form=$("form#church-admin-mobile-menu").serialize();
                console.log(form)
                var url="admin.php?"+ form;
                console.log(url);
                window.location.href = url
            })
        });</script>';
    return $out;
}


function church_admin_check_user_in_directory()
{
    
    global $wpdb,$church_admin_version;
    $user=wp_get_current_user();
    //perform check user is in directory
    $warning='';
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql( $user->user_email).'" OR user_id="'.(int)$user->ID.'"');
    if(!empty( $person) )
    {
        if ( empty( $person->user_id) )
        {
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$user->ID.'" WHERE people_id="'.(int)$person->people_id.'"');
            $warning=esc_html( sprintf(__('Your user login email %1$s was not connected to a directory entry, so the plugin connected you to %2$s','church-admin' ) ,$user->user_email,'<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.(int)$person->people_id,'edit_people').'">'.implode(" ",array_filter(array( $person->first_name,$person->prefix,$person->last_name) )).'</a>'));
        }
    }else
    {
        if(!empty( $user->first_name) )  {$first_name= $user->first_name;}else{$first_name='Admin';}
        if(!empty( $user->last_name) )  {$last_name=$user->last_name;}else{$last_name='User';}
        $email=$user->user_email;
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household(address,first_registered)VALUES("","'.esc_sql(wp_date('Y-m-d')).'")');
        $household_id=$wpdb->insert_id;
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,household_id,show_me,user_id,head_of_household,sex,people_type_id,gdpr_reason,first_registered)VALUES("'.esc_sql( $first_name).'","'.esc_sql( $last_name).'","'.esc_sql( $email ).'","'.(int)$household_id.'","0","'.(int)$user->ID.'",1,1,1,"'.esc_sql( __('Created from current user account')).'",,"'.esc_sql(wp_date('Y-m-d')).'")');
        $people_id=$wpdb->insert_id;
        $warning=esc_html( sprintf(__('Your user login email %1$s was not in the directory, so the plugin created an entry for you - %2$s','church-admin' ) ,   $user->user_email,
        '<a class="button-primary" href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.(int)$people_id,'edit_people')).'">'.esc_html( __('Edit your entry','church-admin')).'</a>'));
     
        
    
    }
}
function church_admin_trial_period(){
    $trial_period_countdown = FALSE;
    $one_month = 28*24*60*60;
    $trial_period = get_option('church_admin_trial');
    if(!empty($trial_period)){
        if(time() < ($trial_period + $one_month)){
            
            $sec_left = ($trial_period + $one_month) - time();
            $days = (int)($sec_left/86400);
            $trial_period_countdown = '<span style="color:red">'.sprintf(_n('Trial period - %1$s day left','Trial period - %1$s days left',$days,'church-admin'),$days).'</span>';
        }

    }
    return $trial_period_countdown;
}
function church_admin_title()
{
  
   
    global $wpdb,$church_admin_version;
    $licence = get_option('church_admin_app_new_licence');
    //church_admin_debug('licence: '.$licence);
    switch($licence){
        case 'basic':
            $title =__('Church Admin Plugin - Basic version','church-admin'); 
        break;
        case 'free': 
        default:
            $title =__('Church Admin Plugin - Free version','church-admin'); 
        break;
        case 'standard': 
            $title =__('Church Admin Plugin - Standard version','church-admin'); 
        break;
        case 'premium': 
            $title =__('Church Admin Plugin - Premium version','church-admin'); 
        break;
    }
 
    $title .= ' '.church_admin_trial_period().' v.'. $church_admin_version;
    echo'<h1 class="church-admin-title"><a title="'.esc_html( __('Back to menu','church-admin')).'" href="'.esc_url(admin_url().'admin.php?page=church_admin/index.php').'"><span class="ca-dashicons dashicons dashicons-menu-alt3" style="text-decoration:none;"></span></a> '.$title.'</h1>';

}
function church_admin_classic_header()
{
    global $wpdb,$church_admin_version;
    
    
    //header signup
    echo'<div class="church-admin-content">';
    echo'<div id="church-admin-header">';
    church_admin_title();
    church_admin_change_look();
    church_admin_search_form();
    if(!empty( $warning) )
    {
       echo'<div class="notice notice-error inline"><h2>'.esc_html( __('Your account','church-admin' ) ).'</h2><p>'.$warning.'</p></div>';
    }
    if(isset( $_GET['action'] ) )  {
        $action= sanitize_text_field( stripslashes( $_GET['action'] ) ) ;
    }else {
        $action='default';
    }
    if(!empty( $_GET['section'] ) && $_GET['section']!='favourites'){
        echo'<p><button class="button-secondary" id="add-to-favourites">'.esc_html( __('Add this page to favourites menu','church-admin' ) ).'</button></p>'; 
    }
    echo'<div class="church_admin_help"><div class="ca-show-help"><p><button class="button-primary ca-helper" data-action="'.esc_attr($action).'">'.esc_html( __('Show help for this page','church-admin' ) ).'</button></p></div></div><!-- END helper-->
    <script>
    jQuery(document).ready(function( $)  {
        $("body").on("click",".ca-hide-help",function(e)
        {
            console.log("hide help");
            e.preventDefault();
            
            $(".ca-show-help").hide();
        });

        $("body").on("click",".ca-helper",function(e)
        {
            e.preventDefault();
            var which=$(this).data("action");
            console.log("which: "+ which);
            var data = {"action":"church_admin","method": "show-help","which": which};
            console.log(data);
            var request=$.ajax({
                url: ajaxurl,
                type: "post",
                data:  data
            })
            request.error(function()  {console.log("theres an error with AJAX");} );
            request.success(function(response) { $(".ca-show-help").html(response);})
                
        });
    });
    </script>';
    
    
    echo'</div><!-- END .church-admin-header -->';
    
   
    
}

function church_admin_module_dropdown( $module)
{
    global $church_admin_menu;
    //church_admin_debug('*** church_admin_module_dropdown ***');
    //create array for dropdowns for each parent
    $parentArray=array();
    foreach( $church_admin_menu AS $name=>$item)
    {
        $item['action']=$name;
        $parentArray[$item['parent']][]=$item;
        

    }
  
     //dropdown menu
     echo'<p><select class="church-admin-menu-action" name="action" onchange="churchAdminGoTo(this.value)" >'."\r\n";
     echo'<option>'.esc_html( __('Section menu','church-admin' ) ).'</option>'."\r\n";
     foreach( $parentArray[$module] AS $chItem)
     {
         echo'<option value="'.esc_html( $chItem['link'] ).'">'.esc_html( $chItem['title'] ).'</option>'."\r\n";
     }
     echo'</select></p>'."\r\n";
     echo'<script>
     function churchAdminGoTo(val)
     {
        window.location.href=val;
            
     }  
 </script>'."\r\n";

   
   

}

/**********************************************************
*   Output boxes to main front admin page
***********************************************************/  
function church_admin_boxes_look()
{
    global $church_admin_menu;
    $modules=get_option('church_admin_modules');
  
    $modules['Settings']=1;//Setting always visible to users with permission
    $modules['App']=1;

    church_admin_title();
    //church_admin_change_look();
    church_admin_modules_dropdown();
  
    if( !empty( $modules['Support'] ) ){ 
        church_admin_manual_advert();
    }
    church_admin_new_look_gdpr();
    $licence = get_option('church_admin_app_new_licence');

    //create array for dropdowns for each parent
    $parentArray=array();
    foreach( $church_admin_menu AS $name=>$item)
    {
        $item['action']=$name;
        $parentArray[$item['parent']][]=$item;
        
    }
   
    $permissions = $x =0;//no of modules permission for
    foreach( $church_admin_menu AS $name=>$item)
    {
       
        
        if( $item['parent']==$name && !empty( $modules[$item['module']] && in_array($licence,$item['licence_level'] ) ))
        {
            $x++;
            if(church_admin_level_check( $item['level'] ) )
            {
                $permissions++;
                echo '<div class="ca-boxes" id="'.esc_html( $name).'">';
                echo    '<div class="ca-boxes-header '.$item['background'].'">'."\r\n";
                echo '<p>'.$item['font-awesome'].'</p>'."\r\n";
                echo'<h3>'.$item['title'].'</h3>'."\r\n";
                echo'</div>'."\r\n";
                echo '<div class="ca-boxes-content">';
                
                    if(count( $parentArray[$name] )>1)
                    {
                        church_admin_module_dropdown( $name);
                    }else
                    {
                        echo'<p><a class="button-primary" href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action='.esc_html( $name),$name).'">'.esc_html( $item['title'] ).'</a></p>';
                    }
                    if(is_callable( $item['callback'] ) )call_user_func( $item['callback'] );
                    echo'</div></div>';
            }
           
           
            
        }
    }
    if($x>$permissions)
    {
        echo'<div class="notice notice-danger"><h2>Church Admin</h2><p>'.sprintf(__('You have permissions for %1$d out of %2$d modules','church-admin'),$permissions,$x).'</p>';
        
        echo'</div>';
    }
    echo "\r\n";
    echo'<script>
            jQuery(document).ready(function( $)  {
                $(".church-admin-menu-action").change(function()  {
                    var action=$(this).val();
                    console.log(action);
                    window.location.href="'.admin_url().'admin.php?page=church_admin/index.php&action="+action;
                });
                $(".ca-toggle").click(function()
                {
                    var toggle=$(this).attr("id");
                    $("."+toggle).toggle()
                });
            });
    </script>';
    
}
/**********************************************************
*   Switcher for look
***********************************************************/  
function church_admin_change_look()
{
    if(!church_admin_level_check('Directory') ) return;
    global $church_admin_url,$church_admin_menu;
    $user=wp_get_current_user();
    /*
    $frontpage=get_option('church-admin-frontpage-look'.$user->ID);
    echo'<div class="church-admin-change-look"><form action="" method="post"><p>';
    switch( $frontpage)
    {
        case'classic':default: echo'<input type="hidden" name="change-look" value="boxes" /><input class="button-primary" type="submit" value="'.esc_html( __('Switch to new style','church-admin' ) ).'" />';break;
      
    }
    echo'</p></form>';
    */
    echo'<div id="church-admin-signup"><form action="//thegatewaychurch.us2.list-manage.com/subscribe/post?u=de873ad10bb6b43b54744b951&amp;id=848214cef0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate><div id="mc_embed_signup_scroll"><strong>'.esc_html( __('Sign up for news and free PDF manual','church-admin' ) ).'</strong>';
    if(!empty( $user->user_firstname) )echo'<input type="hidden" name="FNAME" value="'.esc_html( $user->user_firstname).'" />';
    if(!empty( $user->user_lastname) )echo'<input type="hidden" name="LNAME" value="'.esc_html( $user->user_lastname).'" />';
    echo'<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="'.esc_html( __('Email address','church-admin' ) ).'" required><!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups--><div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_de873ad10bb6b43b54744b951_848214cef0" tabindex="-1" value=""></div><input type="submit" value="'.esc_html( __('News sign up','church-admin' ) ).'" name="subscribe" id="mc-embedded-subscribe" class="button-primary"></div></form></div></div>';
 
    
}

/**********************************************************
*   Front End email check
***********************************************************/                   
function church_admin_front_end_email_check()
{
    global $wpdb;
    if(!empty( $_POST['ca-email'] ) )$email=sanitize_text_field( stripslashes($_POST['ca-email'] ));
    if(!empty( $_POST['email'] ) )$email=sanitize_text_field( stripslashes( $_POST['email'] ));
    if ( empty( $email) ){
        return FALSE;
    }
    if(!is_email( $email) ){ 
        return FALSE;
    }
    $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql( $email).'" LIMIT 1');
    church_admin_debug($wpdb->last_query);
    
    if( $people_id)  {
        church_admin_debug('people id'.$people_id);
        return TRUE;
    }else{
        church_admin_debug('people id'.$people_id);
        return FALSE;
    }
}




function church_admin_app_graph()
{
    return;
    //deprecated
    global $wpdb;
    $pages=$wpdb->get_results('SELECT DISTINCT app_page FROM '.$wpdb-prefix.'church_admin_app_visits WHERE app_page!="" ORDER BY app_page');
    if(!empty( $pages) )
    {
        echo'<h3>'.esc_html( __("App usage graph",'church-admin' ) ).'</h3>';
        echo'<form action="'.admin_url().'admin.php?page=church_admin/index.php#app" method="POST"><p><select name="app_page">';
        if(!empty( $_POST['app_page'] ) )
        {
            echo'<option value="'.esc_attr( church_admin_sanitize($_POST['app_page'])) .'">'.esc_html(church_admin_sanitize($_POST['app_page'])).'</option>';
        }
        foreach( $pages AS $page){
            echo'<option value="'.esc_attr( $page->app_page).'">'.esc_html( $page->app_page).'</option>';
        }
        echo'</select><select name="year">';
        if(!empty( $_POST['year'] ) )
        {
            echo'<option value="'.intval( $_POST['year'] ).'">'.intval( $_POST['year'] ).'</option>';
        }
        for ( $year=date('Y'); $year>=date('Y')-5; $year--)
        {
            echo'<option value="'.intval( $year).'">'.intval( $year).'</option>';
        }
        echo'</select>';
        echo'<input type="submit" class="button-primary"   value="'.esc_html( __('Show','church-admin' ) ).'" /></p></form>';
        $data=$columns=array();

        $sql='SELECT visit_date FROM '.$wpdb-prefix.'church_admin_app_visits WHERE YEAR(visit_date)="'.esc_sql(wp_date('Y')).'" GROUP BY visit_date ORDER BY visit_date';
        $dates=$wpdb->get_results( $sql);
        if ( empty( $_POST['app_page'] ) )
        {
            $_POST['app_page']='Home';
            $_POST['year']=date('Y');
        }
        foreach( $dates AS $date)
        {
            $row=array();

                $sql='SELECT visits  FROM '.$wpdb-prefix.'church_admin_app_visits WHERE visit_date="'.esc_sql( $date->visit_date).'" AND app_page="'.esc_sql( church_admin_sanitize($_POST['app_page'])).'"';
                //church_admin_debug( $sql);
                $appPageCount=$wpdb->get_var( $sql);
                //church_admin_debug( $date->visit_date.' '.$appPageCount);
                if(!empty( $appPageCount) )  {$row[]=intval( $appPageCount);}else{$row[]=0;}


           array_unshift( $row,mysql2date(get_option('date_format'),$date->visit_date) );
            $data[]=$row;
        }

            $addRows=json_encode(array('Date',esc_html( church_admin_sanitize($_POST['app_page'] ))) ).",\r\n";
            foreach ( $data AS $key=>$value)
            {
                $addRows.=json_encode( $value).",\r\n";
            }



            //var_dump( $data);
            $out='
        <script type="text/javascript">
          google.charts.load("current", {"packages":["corechart"]});
          google.charts.setOnLoadCallback(drawChart);

          function drawChart() {
            var data = google.visualization.arrayToDataTable([
                '.$addRows.'
                 ] );

            var options = {
              title: "'.esc_html( __('App visits','church-admin' ) ).' - '.esc_html( church_admin_sanitize($_POST['app_page']))  .'",

              legend: { position: "bottom" }
            };

            var chart = new google.visualization.LineChart(document.getElementById("app_graph") );

            chart.draw(data, options);
          }</script>';
                $out.='<div id="app_graph" style="width:100%;height:200px;"></div>';
            echo $out;
    }
}
/***********************
 * 
 * ACTIONS 
 * 
 ***********************/

function church_admin_actions()
{
    global $wpdb, $church_admin_member_types_array;

    


    $licence = get_option('church_admin_app_new_licence');
    if(empty($licence)){
        echo'<div class="notice notice-warning"><p>'.__('Licence missing - free, standard or premium','church-admin');
        return;
    }
    //allow people to edit their own entry
    //if(!is_user_logged_in() )exit( __('You must be logged in','church-admin') );
    //if(!is_admin() )exit( __('You must be logged in','church-admin') );
	$self_edit=FALSE;
	$user_id=get_current_user_id();
    $household_id = !empty($_REQUEST['household_id'])?(int)church_admin_sanitize($_REQUEST['household_id']):null;

	if(!empty( $household_id ) && !empty($user_id) )$check=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int) $user_id.'" AND household_id="'.(int)$household_id.'"');

	if(!empty( $check) && $check==$user_id)$self_edit=TRUE;
	$user_id=!empty( $_GET['user_id'] )?church_admin_sanitize($_GET['user_id'])  :NULL;
	$id=isset( $_GET['id'] )?church_admin_sanitize($_GET['id'])  :0;
    $custom_id=!empty( $_REQUEST['custom_id'] )?church_admin_sanitize($_REQUEST['custom_id'])  :NULL;
    $fund_id=isset( $_GET['fund_id'] )?church_admin_sanitize($_GET['fund_id']) :NULL;
    $gift_id=isset( $_GET['gift_id'] )?church_admin_sanitize($_GET['gift_id'])  :NULL;
    $giving_id=isset( $_GET['giving_id'] )?church_admin_sanitize($_GET['giving_id'])  :NULL;
	$mtg_type=!empty( $_GET['mtg_type'] )?church_admin_sanitize($_GET['mtg_type'])  :'service';
	$rota_date=!empty( $_GET['rota_date'] )?church_admin_sanitize($_GET['rota_date'])  :NULL;
    $date=!empty( $_GET['date'] )?church_admin_sanitize($_GET['date'])  :NULL;
	$rota_id=!empty( $_GET['rota_id'] )?church_admin_sanitize($_GET['rota_id'])  :NULL;
	$copy_id=!empty( $_GET['copy_id'] )?church_admin_sanitize($_GET['copy_id'])  :NULL;
    $date_id=!empty( $_GET['date_id'] )?church_admin_sanitize($_GET['date_id'])  :NULL;
    $event_id=!empty( $_GET['event_id'] )?church_admin_sanitize($_GET['event_id'])  :NULL;
    $pledge_id=!empty( $_GET['pledge_id'] )?church_admin_sanitize($_GET['pledge_id'])  :NULL;
	$email_id=!empty( $_GET['email_id'] )?church_admin_sanitize($_GET['email_id'])  :NULL;
    $people_id=!empty( $_REQUEST['people_id'] )?church_admin_sanitize($_REQUEST['people_id'])  :NULL;
    
    $household_id=!empty( $_GET['household_id'] )?church_admin_sanitize($_GET['household_id'])  :NULL;
    $service_id=!empty( $_REQUEST['service_id'] )?church_admin_sanitize($_REQUEST['service_id'] ) :NULL;
    $mtg_type=!empty( $_REQUEST['mtg_type'] )?church_admin_sanitize($_REQUEST['mtg_type'])  :'service';
    $site_id=!empty( $_REQUEST['site_id'] )?church_admin_sanitize($_REQUEST['site_id'])  :NULL;
    $attendance_id=!empty( $_GET['attendance_id'] )?church_admin_sanitize($_GET['attendance_id'])  :NULL;
	$ministry_id=!empty( $_GET['ministry_id'] )?church_admin_sanitize($_GET['ministry_id'])  :NULL;
    $ID=!empty( $_GET['ID'] )?church_admin_sanitize($_GET['ID'])  :NULL;
    $meeting=!empty($_GET['meeting'])?church_admin_sanitize($_GET['meeting'])  :NULL;
    $unit_id=!empty( $_GET['unit_id'] )?church_admin_sanitize($_GET['unit_id'])  :NULL;
    $subunit_id=!empty( $_GET['subunit_id'] )?church_admin_sanitize($_GET['subunit_id']) :NULL;
    $funnel_id=!empty( $_GET['funnel_id'] )?church_admin_sanitize($_GET['funnel_id'])  :NULL;
    $ticket_id=!empty( $_GET['ticket_id'] )?church_admin_sanitize($_GET['ticket_id'])  :NULL;
    $booking_ref=!empty( $_GET['booking_ref'] )?church_admin_sanitize($_GET['booking_ref'])  :NULL;
    $people_type_id=isset( $_GET['people_type_id'] )?church_admin_sanitize($_GET['people_type_id']):NULL;
    $member_type_id=isset( $_REQUEST['member_type_id'] )?church_admin_sanitize($_REQUEST['member_type_id'])  :NULL;
    $note_id=isset( $_REQUEST['note_id'] )?church_admin_sanitize($_REQUEST['note_id']):NULL;
	$facilities_id=isset( $_REQUEST['facilities_id'] )?church_admin_sanitize($_REQUEST['facilities_id'])  :null;
    $edit_type=!empty( $_REQUEST['edit_type'] )?church_admin_sanitize($_REQUEST['edit_type'])  :'single';
    $file=!empty( $_GET['file'] )?church_admin_sanitize($_GET['file'])  :NULL;
    $app_date=!empty( $_GET['app_date'] )?church_admin_sanitize($_GET['app_date'])  :date('Y-m-d');
	$smallgroup_id=!empty( $_GET['smallgroup_id'] )?church_admin_sanitize($_GET['smallgroup_id'])  :NULL;
    $message=!empty( $_GET['message'] )?church_admin_sanitize($_GET['message'])  :NULL;
    if(!empty( $_REQUEST['church_admin_search'] ) )  {
        if(church_admin_level_check('Directory') )  {
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
        church_admin_search( $_REQUEST['church_admin_search'] );
        }
    }
	elseif(isset( $_GET['action'] ) )
    {
        
        switch( $_GET['action'] )
        {
              /*************************************
            *
            *       AUTOMATIONS
            *
            **************************************/
            case 'automations':
                check_admin_referer('automations');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('automations');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
                echo church_admin_automations_list();
            break;
            case'registration-followup-email-setup':
                check_admin_referer('registration-followup-email-setup');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('automations');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
                church_admin_registration_follow_up_email();
            break;
            case 'custom-field-automations':
                check_admin_referer('custom-field-automations');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('automations');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
                church_admin_custom_fields_automations_list();
            break;
            case 'conditional-custom-field-automations':
                check_admin_referer('conditional-custom-field-automations');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('automations');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
                church_admin_edit_conditional_automation($custom_id);
            break;
            case 'edit-custom-field-automation':
                check_admin_referer('edit-custom-field-automation');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('automations');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
                church_admin_edit_custom_field_automation($id);
            break;
            case 'delete-custom-field-automation':
                check_admin_referer('delete-custom-field-automation');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                     return;
                }
                church_admin_module_dropdown('automations');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
                church_admin_delete_custom_field_automation($id);
            break;
              /*************************************
            *
            *       INVENTORY
            *
            **************************************/
            case 'inventory':
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Inventory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Inventory','church-admin') )).'</h2></div>';
                     return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/inventory.php');
                echo church_admin_inventory_list();
            break;
            case 'edit-inventory':
                check_admin_referer('edit-inventory');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Inventory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Inventory','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/inventory.php');
                echo church_admin_edit_inventory($id);
            break;
            case 'delete-inventory':
                check_admin_referer('delete-inventory');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Inventory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Inventory','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/inventory.php');
                echo church_admin_delete_inventory($id);
            break;
            /****************************
             * BIBLE READINGS
             ****************************/
            case 'bulk-bible-readings':
                check_admin_referer('bulk-bible-readings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bible') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bible readings','church-admin') )).'</h2></div>';
                     return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/bible-readings.php');
                church_admin_bible_reading_bulk_post();
            break;
            case'resend-bible-reading':
                check_admin_referer('resend-bible-reading');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bible') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bible readings','church-admin') )).'</h2></div>';
                    return;
                }
                $ID=(int)$_GET['ID'];
                $post=get_post( $ID);
               
                echo'<p>'.esc_html( __('Bible reading resent','church-admin' ) ).'</p>';
            break;
            case 'reset_readings':
                check_admin_referer('reset_readings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }if(!church_admin_level_check('Bible') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bible','church-admin') )).'</h2></div>';
                   
                    return;
                }
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_brplan SET passages=""');
                echo esc_html(__('Done','church-admin'));
            break;
            /****************************
             * DIRECTORY
             ****************************/
            case 'merge-people':
                check_admin_referer('merge-people');
                if(church_admin_level_check('Directory') )
                {
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    //sanitize
                    $p1 = !empty($_REQUEST['people_id1'])? church_admin_sanitize($_REQUEST['people_id1']):null;
                    $p2 = !empty($_REQUEST['people_id2'])? church_admin_sanitize($_REQUEST['people_id2']):null;   
                    if(!empty($p1) && church_admin_int_check($p1) && !empty($p2) && church_admin_int_check($p2)){
                        echo church_admin_merge_people( (int)$p1,(int)$p2 );
                    }else{echo'<div class="error"><p>'.esc_html( __("Missing people ids",'church-admin' ) ).'</p></div>';}
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    
                }
            break;  
            
            /****************************
             * FACILITIES
             ****************************/
            case 'approve-facility-booking':
                check_admin_referer('approve-facility-booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(church_admin_level_check('Calendar') )
                {
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
                    echo church_admin_approve_facility_booking( $booking_id);
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') )).'</h2></div>';
                    
                }
            break;
            case 'decline-facility-booking':
                check_admin_referer('decline-facility-booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(church_admin_level_check('Calendar') )
                {
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
                    echo church_admin_decline_facility_booking( $booking_id);
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') )).'</h2></div>';
                   
                }
            break;
            
            /*************************
             * SHORTCODES
             ************************/
            case'shortcodes':
                check_admin_referer('shortcodes');
                church_admin_shortcodes_list();
            break;
            
                       
            
          
            //csv import
            case'import-csv':
                check_admin_referer('import-csv');
                if(!church_admin_level_check('Directory') ){
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                else{
                    church_admin_module_dropdown('people');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_import_csv();
                }
            break;
            
            case'replicate-roles':
            case'replicate_roles':
                check_admin_referer('replicate-roles');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                else{
                    church_admin_module_dropdown('Directory');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_replicate_roles();
                }
            break;

            case 'edit_marital_status': 
                check_admin_referer('edit_marital_status');
                if(!church_admin_level_check('Directory') ){
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') )).'</h2></div>';
                }
                else
                {require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');church_admin_edit_marital_status( $ID);}
            break;
            case 'delete_marital_status': 
                check_admin_referer('delete_marital_status');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') )).'</h2></div>';
                    return;
                }
                else{
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                    church_admin_delete_marital_status( $ID);
                }
            break;
            /****************************
             * Spiritual Gifts
             ****************************/
            case 'spiritual-gifts':
                check_admin_referer('spiritual-gifts');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Gifts') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Spiritual Gifts','church-admin') )).'</h2></div>';
                    return;
                }
                echo'<p>'.esc_html( __('Use the shortcode [church_admin type="spiritual-gifts"] for people to fill in the questionnaire','church-admin' ) ).'</p>';
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/spiritual-gifts.php');
                church_admin_spiritual_gifts_list();
                
            break;
            /*************************************
            *
            *		APP
            *
            **************************************/
            case 'app-logout':
                check_admin_referer('app-logout');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');    
                church_admin_logout_app_everyone();
            break;
            case 'logout_app':
                check_admin_referer('logout_app');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');church_admin_logout_app( $user_id);
            break;
            //case 'app_page':require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');church_admin_app_post();break;
            case 'app': 
                check_admin_referer('app');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
                church_admin_app();
            break;
            case 'app-visits':
                check_admin_referer('app-visits');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                } 
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
                church_admin_app_logs();
            break;
            case 'reset-app-menu':
                check_admin_referer('reset-app-menu');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                delete_option('church_admin_app_new_menu');
                church_admin_app();
            break;
            //case 'delete_app_content':if(current_user_can('manage_options') )  {require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');church_admin_delete_current_app_content();}break;
            case 'app-settings':
                check_admin_referer('app-settings');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
                church_admin_app_settings();
                church_admin_app_member_types();
            break;
            case 'app-menu':
                check_admin_referer('app-menu');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
                church_admin_sortable_script();
                church_admin_app_menu();
            break;
            case 'bible-reading-plan':
                check_admin_referer('bible-reading-plan');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bible') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bible','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
                church_admin_bible_reading_plan();
            break; 
            case 'app-cache-clear':
                check_admin_referer('app-cache-clear');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                delete_option('church_admin_modified_app_content');
                echo'<div class="notice notice-success"><p>'.esc_html(__('App cache cleared','church-admin')).'</p></div>';
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
                church_admin_app_menu();
            break;
            case 'churchwide-my-prayer':
                check_admin_referer('churchwide-my-prayer');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-setup.php');
                church_admin_churchwide_my_prayer();
            break;
            case 'app-member-types':
                check_admin_referer('app-member-types');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('App','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');    
                church_admin_app_member_types();
            break;
            
            case 'app-logins':
            case 'app-users':
                check_admin_referer('app-users');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is premium only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('App') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('app');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');    
                church_admin_app_logins();
            break;
           
            /*************************************
            *
            *		ATTENDANCE
            *
            **************************************/
            case 'graph':
                check_admin_referer('graph');
                echo'<h2>'.esc_html( __('Attendance graph','church-admin' ) ).'</h2>';
                require_once(plugin_dir_path(dirname(__FILE__) ).'display/graph.php');
                echo church_admin_graph( $type='weekly','S/1',date("Y-01-01"),date("Y-12-31"),900,500,TRUE);
                
            break;
            case'show-attendance':
            case 'attendance':  
                check_admin_referer('attendance');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') )).'</h2></div>';
                    return;
                }
                echo'<h2>'.esc_html( __('Attendance','church-admin' ) ).'</h2>';
                church_admin_module_dropdown('attendance');

                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/attendance.php');
                church_admin_attendance_list($meeting);

                echo'<h2>'.esc_html( __('Attendance graph','church-admin' ) ).'</h2>';
                require_once(plugin_dir_path(dirname(__FILE__) ).'display/graph.php');
                echo church_admin_graph( $type='weekly','S/1',date("Y-01-01"),date("Y-12-31"),900,500,TRUE);
                
                
            break; 
            case 'individual-attendance-list':
                check_admin_referer('individual-attendance-list');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin'  ),__('Attendance','church-admin') )).'</h2></div>';
                    return;
                }    
                church_admin_module_dropdown('attendance'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/individual_attendance.php');
                church_admin_individual_attendance_list();
                echo'<h2>'.esc_html( __('Attendance graph','church-admin' ) ).'</h2>';
                require_once(plugin_dir_path(dirname(__FILE__) ).'display/graph.php');
                echo church_admin_graph( $type='weekly','S/1',date("Y-01-01"),date("Y-12-31"),900,500,TRUE);
            break;
            case 'individual-attendance-csv':
                check_admin_referer('individual-attendance-csv');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ),__('Attendance','church-admin') ) ).'</h2></div>';
                    return;
                }   
                church_admin_module_dropdown('attendance'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/individual_attendance.php');
                echo church_admin_individual_attendance_csv();
            
            break;
            case 'attendance-csv':
                check_admin_referer('attendance-csv');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') )).'</h2></div>';
                    return;
                }   
                church_admin_module_dropdown('attendance');  
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/csv.php');
                echo church_admin_attendance_csv();
                
            break;
            case 'edit-individual-attendance':
            case 'individual-attendance':
            case 'individual_attendance':
                check_admin_referer('individual-attendance');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('attendance');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/individual_attendance.php'); 
                echo church_admin_individual_attendance();
            break;
            case 'attendance-metrics':
                check_admin_referer('attendance-metrics');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin'  ),__('Attendance','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('attendance');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/attendance.php');
                church_admin_attendance_metrics( $service_id);break;
            case 'attendance-list':
                check_admin_referer('attendance-list');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/attendance.php');
                church_admin_attendance_list( $service_id);
            break;
            case 'add-attendance':
            case 'edit-attendance':
                check_admin_referer('edit-attendance');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('attendance');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/attendance.php');
                church_admin_edit_attendance($attendance_id);
                
            break;
            case 'weeks-attendance':
                check_admin_referer('weeks-attendance');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('attendance');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/attendance.php');
                church_admin_edit_this_weeks_attendance();
                
            break;
            case 'delete-attendance':
                check_admin_referer('delete-attendance');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Attendance') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('attendance');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/attendance.php');
                church_admin_delete_attendance( $attendance_id);
            break;


            /*************************************
            *
            *		CALENDAR
            *
            **************************************/
            case 'import-ics':
                check_admin_referer('import-ics');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_import_ical();
            break;
            case 'calendar':
            case 'church_admin_new_calendar':
            case 'new-calendar':
                check_admin_referer('calendar');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                $start_date = !empty($_REQUEST['start_date']) ? church_admin_sanitize($_REQUEST['start_date']) : wp_date('Y-m-01');
                church_admin_new_calendar($start_date,$facilities_id);
            break;
            case 'add-calendar':
            case 'church_admin_new_edit_calendar':
            case 'edit-calendar':
                check_admin_referer('edit-calendar');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');

                if(substr( $id,0,4)=='item')  {church_admin_event_edit(substr( $id,4),NULL,$edit_type,NULL,$facilities_id);}
                else
                {
                    church_admin_event_edit(NULL,NULL,NULL,$id,$facilities_id);
                }
            
            break;
            case 'delete-calendar':
                check_admin_referer('delete-calendar');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_delete_calendar();

            break;
            case 'church_admin_calendar_list':
            case 'calendar-list':
                check_admin_referer('calendar-list');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_calendar();
            break;

            case 'edit-category':
            case 'church_admin_edit_category':
                check_admin_referer('edit-category');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') )) .'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_edit_category( $id,NULL);
            break;

            case 'church_admin_delete_category':
            case 'delete-category':
                check_admin_referer('delete-category');
                    if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                        echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                        return;
                    }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_delete_category( $id);
            break;

            case 'church_admin_single_event_delete':
            case 'single-event-delete':
                check_admin_referer('single-event-delete');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                    
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_single_event_delete( $date_id,$event_id); 
            break;

            case 'church_admin_series_event_delete':
            case 'series-event-delete':
                check_admin_referer('series-event-delete');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('series_event_delete');
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_series_event_delete( $event_id,$date_id);
            break;
            case 'future-event-delete':
                check_admin_referer('future-event-delete');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('series_event_delete');
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_future_event_delete( $event_id,$date_id);
            break;
            case'categories':
            case 'church_admin_category_list':    
            case 'category-list':
            case 'view-categories':
                check_admin_referer('view-categories');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_category_list();
            break;
            case 'church_admin_single_event_edit':
            case 'single-event-edit':
                check_admin_referer('single-event-edit');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('single_event_edit');
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_event_edit( $date_id,$event_id,'single',NULL,NULL);
            break;
            case 'whole-series-edit':
                check_admin_referer('whole-series-edit');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('single_event_edit');
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                     return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_event_edit( $date_id,$event_id,'whole-series',NULL,NULL);
            break;   
            case 'future-series-edit':
                check_admin_referer('future-series-edit');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('single_event_edit');
                if(!church_admin_level_check('Calendar') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Calendar','church-admin') ) ).'</h2></div>';
                     return;
                }
                church_admin_module_dropdown('calendar'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
                church_admin_event_edit( $date_id,$event_id,'future-series',NULL,NULL);
            break;  
            
                
            /*************************************
            *
            *		CHECK-IN
            *
            **************************************/
            case 'QRCode':
                check_admin_referer('QRCode');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Check-in') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Check-in','church-admin') ) ).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/checkin.php');
                church_admin_create_QR( $people_id);
                break;
            case 'checkin-labels':
                check_admin_referer('checkin-labels');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Check-in') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Check-in','church-admin') ) ).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/checkin.php');
                church_admin_checkin_labels();
            break;
            /******************************************
            *
            *   classes
            *
            *******************************************/
            case 'classes':
            case 'class':
                check_admin_referer('classes');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Classes') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Classes','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('classes'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/classes.php');
                echo'<h2>'.esc_html( __("Classes",'church-admin' ) ).'</h2>';
                church_admin_classes();
                
            break;
            case 'edit-class':
                check_admin_referer('edit-class');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Classes') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Classes','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('classes'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/classes.php');
                church_admin_edit_class( $id);
                
            break;
            case 'delete-class':
                check_admin_referer('delete-class');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Classes') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Classes','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('classes'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/classes.php');
                church_admin_delete_class( $id);
                
            break;
            case 'view_class':
            case 'view-class':
                check_admin_referer('view-class');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Classes') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Classes','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('classes'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/classes.php');
                    church_admin_view_class( $id);
                
            break;

            /*************************************
            *
            *		COMMUNICATIONS
            *
            **************************************/
            case 'test_email':
                case 'test-email':
                case 'testemail':
                    check_admin_referer('test-email');
                    if($licence!='standard' && $licence!='premium'){
                        echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                        return;
                    }
                    if(!church_admin_level_check('Bulk_Email') )
                    {
                        echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin'))  ).'</h2></div>';
                        return;
                    }
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                    $email = !empty($_POST['test-email'] )? sanitize_text_field( stripslashes($_POST['test-email'] ) ): null;
                    church_admin_test_email( $email);
                        
                    
                    
                    
                break;


            /*************************
             * PUSH
             ************************/
            case 'push-to-all':
                check_admin_referer('push-to-all');
                if( $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Push') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Push','church-admin'))  ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                
                 $licence = get_option('church_admin_app_new_licence');;
                if( $licence=='premium')ca_push_message();
                
            break;
            
            case 'send-sms':
                check_admin_referer('send-sms');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_SMS') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk SMS','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sms.php');
                church_admin_send_sms();
                
            break;
            case 'twilio-replies':
                check_admin_referer('twilio-replies');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_SMS') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk SMS','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/twilio.php');
                church_admin_twilio_replies_list();
                
            break;   
            case 'view-sms-thread':
                check_admin_referer('view-sms-thread');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_SMS') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk SMS','church-admin') )).'</h2></div>';
                    return;
                }
                    church_admin_module_dropdown('comms'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/twilio.php');
                    church_admin_view_sms_thread(urldecode( church_admin_sanitize($_GET['mobile'] ) ));
                
            break; 
            case 'delete-sms-thread':
                check_admin_referer('delete-sms-thread');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_SMS') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk SMS','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/twilio.php');
                church_admin_delete_sms_thread(urldecode(church_admin_sanitize( $_GET['mobile'] )));
                
            break; 
            case 'email-settings':
                check_admin_referer('email-settings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');            
                church_admin_email_settings();
                
            break;
            case 'sms-settings':
                check_admin_referer('sms-settings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_SMS') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');            
                church_admin_sms_settings();
                
            break;
            case 'smtp-settings':
                check_admin_referer('smtp-settings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');     
                       
                church_admin_email_settings();
                
            break;          
            
            case 'push':
            case 'push-message':  
                check_admin_referer('push');
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }  
                if(!church_admin_level_check('Push') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk SMS','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/push.php');            
                church_admin_push();
                
            break;
            case 'single-gdpr-email':
                check_admin_referer('single-gdpr-email');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                $people_id = !empty($_POST['people_id'])?church_admin_sanitize($_POST['people_id']):null;
                if(!empty($people_id) && church_admin_int_check($people_id)){
                
                    $row=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
                    if(!empty( $row) ){
                        church_admin_gdpr_email_send( $row,TRUE);
                    }
                }
                    
                
            break;
            
            case 'gdpr-email': 
                check_admin_referer('gdpr-email');
                if(!church_admin_level_check('Directory') )  {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_gdpr_email();
                
            break;
            case 'gdpr-email-test': 
                check_admin_referer('gdpr-email-test');
                if(church_admin_level_check('Directory') )  {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_gdpr_email_test();
                
            break;
            case 'send-email':
                check_admin_referer('send-email');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                church_admin_send_email(NULL);
                
            break;
            case 'mailersend-status':
                check_admin_referer('mailersend-status');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                $bulk_email_id = !empty($_GET['bulk_email_id']) ? church_admin_sanitize($_GET['bulk_email_id']): null;
                if(empty($bulk_email_id)){
                    echo '<div class="notice notice-warning"><h2>'.esc_html(__('No email id','church-admin')).'</h2></div>';
                    return;
                }
                church_admin_mailersend_status($bulk_email_id);
            break;
            case 'clear-email-queue':
                check_admin_referer('clear-email-queue');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                church_admin_clear_email_queue();
                
            break;
            case 'comms':
                check_admin_referer('comms');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') && !church_admin_level_check('Bulk_SMS'))
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Attendance','church-admin') ) ).'</h2></div>';
                    return;
                }
                echo'<h2>'.esc_html( __('Communications','church-admin' ) ).'</h2>';
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/new-style-callbacks.php');
                church_admin_comms_callback();
            break;
            case 'email_list':
            case 'email-list':
                check_admin_referer('email-list');

                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                church_admin_email_list();
                
            break;
            case 'send-to-support':
                check_admin_referer('send-to-support');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                church_admin_send_debug_to_support();
            break;
            case 'delete-email':
                check_admin_referer('delete-email');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                church_admin_delete_email( $email_id);
            break;
            case 'resend-email':
                check_admin_referer('resend-email');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                church_admin_resend( $email_id);
            break;
            case 'resend-new':
                check_admin_referer('resend-new');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                church_admin_resend_new( $email_id);
            break;

            case 'edit-resend':
                check_admin_referer('edit-resend');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('comms'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/email.php');
                church_admin_send_email( $email_id);
            break;

            
            /*************************************
            *
            *		CUSTOM FIELDS
            *
            **************************************/
            case 'custom-fields':
                check_admin_referer('custom-fields');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('People'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/custom_fields.php');
                echo church_admin_list_custom_fields();
                
            break;
            case 'edit_custom_field':
            case 'edit-custom-field':  

                check_admin_referer('edit-custom-field'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people'); 
                echo church_admin_edit_custom_field( $id);
            break;
            case 'delete_custom_field':
            case 'delete-custom-field':    
                check_admin_referer('delete-custom-field'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                echo church_admin_delete_custom_field( $id);
            break;
            /*************************************
            *
            *		DIRECTORY
            *
            **************************************/
            case 'photo-permissions':
                check_admin_referer('photo-permissions');
                if(church_admin_level_check('Directory') )
                {
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/photo-permissions.php');
                    echo church_admin_photo_list( $member_type_id);
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                   
                }
            break;
            case 'photo-permissions-pdf':
                check_admin_referer('photo-permissions-pdf');
                if(church_admin_level_check('Directory') )
                {
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/photo-permissions.php');
                    echo church_admin_photo_permission_pdf_form();
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                   
                }
            break;
            case 'import-from-users':
                check_admin_referer('import-from-users'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');  
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/import.php'); 
                church_admin_import_from_users();
            break;
            case 'check-directory-issues':
                check_admin_referer('check-directory-issues'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');  
                    church_admin_directory_issues_fixer();
                
            break;
            case 'people-activity':
                check_admin_referer('people-activity'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');    
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/people_activity.php'); 
                echo church_admin_recent_people_activity();
            break;



            /***********
             * GROUPS
             ***********/
            case 'smallgroups-map':
                check_admin_referer('smallgroups-map'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') ) ).'</h2></div>';
                    return;
                }
                $centre=church_admin_center_coordinates($wpdb->prefix.'church_admin_smallgroup');
                echo'<h2>'.esc_html( __('Smallgroups map','church-admin' ) ).'</h2>';
                church_admin_module_dropdown('groups');   
                if ( empty( $centre->lat)||empty( $centre->lng) ){
                    $centre=$wpdb->get_row('SELECT lat, lng FROM '.$wpdb->prefix.'church_admin_sites ORDER BY site_id ASC LIMIT 1');
                }
                if(!empty( $centre) )
                {
                    echo'<script type="text/javascript">var xml_url="'.site_url().'/?ca_download=small-group-xml&small-group-xml='.wp_create_nonce('small-group-xml').'";';
                    if(!empty( $centre->lat) )  {echo' var lat='.esc_html( $centre->lat).';';}
                    if(!empty( $centre->lng) )  {echo' var lng='.esc_html( $centre->lng).';';}
                    echo' var zoom=13;';
                    echo'jQuery(document).ready(function()  {sgload(lat,lng,xml_url,zoom);})</script><div id="map" style="width:100%;height:750px;" ></div>';
                    
                }
                
            
                
            break; 
            case 'people-map':
                check_admin_referer('people-map'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                
                church_admin_module_dropdown('people');   
                $api_key=get_option('church_admin_google_api_key');
                if(empty($api_key)){
                    echo '<div class="notice notice-warning"><h2>'.esc_html(__('People Map','church-admin' ) ).'</h2><p>'.esc_html( __('A Google API key is required for this feature','church-admin') ).'</p>';
                    echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/google-api-key/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
                    echo'</div>';
                    return;
                }
                $args=array('width'=>'100%','height'=>'750px','zoom'=>13);
                echo church_admin_map(13,NULL,1,1,1,"100%","750px");
            break;
            case 'add-household':
                check_admin_referer('add-household'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_new_household();
                
            break;
            case 'quick-household':
                check_admin_referer('quick-household'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_quick_household();
            break;
            
            case 'download-csv':
                check_admin_referer('download-csv'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_export_csv();
                
            break;
         
            case 'recent-activity':
                check_admin_referer('recent-activity'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/people_activity.php');     
                church_admin_recent_people_activity();    
                
            break;
            case 'view-directory':
                check_admin_referer('view-directory'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_view_directory();
                
            break;
            case 'export-pdf':
                check_admin_referer('address-list');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_pdf_menu();
                
            break;
            case'bulk-geocode':
                check_admin_referer('bulk-geocode');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_bulk_geocode();
        break;
     
        case 'key-dates':
            check_admin_referer('key-dates');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                return;
            }
                church_admin_module_dropdown('key-dates');  
                echo'<h2>'.esc_html(__('Key dates','church-admin')).'</h2>';
                church_admin_keydates_callback();
        break;
       
        case 'happy-birthday-email-setup':
            check_admin_referer('happy-birthday-email-setup');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('key-dates');   
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
            church_admin_happy_birthday_email_setup();
        break;
        case 'global-birthday-email-setup':
            check_admin_referer('global-birthday-email-setup');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('key-dates');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
                church_admin_global_birthday_email_setup();
        break;
        case 'happy-anniversary-email-setup':
            check_admin_referer('happy-anniversary-email-setup');
            if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('key-dates');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
                church_admin_happy_anniversary_email_setup();
        break;
        case 'global-anniversary-email-setup':
            check_admin_referer('global-anniversary-email-setup');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('key-dates');   
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
            church_admin_global_anniversary_email_setup();
        break;
        case 'global-both-email-setup':
            check_admin_referer('global-both-email-setup');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('key-dates');     
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
            church_admin_global_both_email_setup();
        break;

        case 'send-test-automation-emails':
            check_admin_referer('send-test-automation-emails');
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('key-dates');   
            church_admin_send_test_automation_email();
        break;
        case 'birthdays':
            check_admin_referer('birthdays');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('key-dates');   
                echo'<h2>'.esc_html( __("All birthdays",'church-admin' ) ).'</h2>';
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
                echo church_admin_frontend_birthdays(0,0,365,TRUE,1,1);
                
        break;
        case 'anniversaries':
        
            check_admin_referer('anniversaries');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('key-dates');   
            echo'<h2>'.esc_html( __("Wedding Anniversaries",'church-admin' ) ).'</h2>';
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
            echo church_admin_frontend_anniversaries(0,0,365,TRUE,1,1);
            
    break;
        
    
            case 'gdpr-bulk-confirm':
                check_admin_referer('gdpr-bulk-confirm');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                gdpr_confirm_everyone();
            break;
            case 'view_person':
                check_admin_referer('view_person');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php'); 
                church_admin_view_person( $people_id);
            break;
            case 'move-person':
                check_admin_referer('move-person');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_move_person( $people_id);
            break;
            case 'view-address-list':
            case 'church_admin_address_list': 
                check_admin_referer('view-address-list');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_address_list( $member_type_id);
            break;

            case 'create_users':
            case 'create-users':
                check_admin_referer('create-users');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_users();
                
            break;
                
            case 'church_admin_create_user':
                check_admin_referer('church_admin_create_user');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_create_user( $people_id,$household_id);
            break;
            case 'church_admin_migrate_users':
                check_admin_referer('migrate_users');
            
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_migrate_users();
            break;
            case 'display_household':
            case 'display-household': 
                
                /******************************************************
                 * nonce generated by register functions on front end 
                 * and sentby email to admin won't work normally
                 * because a nonce uses user_id etc
                 * we will check nonce for use within admin area, 
                 * but also allow access if a logged in user with 
                 * directory permissions
                 * *************************************************/
                $access=FALSE;
                $nonce = !empty($_REQUEST['_wpnonce'])?church_admin_sanitize($_REQUEST['_wpnonce']):NULL;
                if(!empty($nonce) && wp_verify_nonce($nonce,'display-household')){$access=TRUE;}
                
                $token = !empty($_REQUEST['token'])?church_admin_sanitize($_REQUEST['token']):NULL;
                if(!empty($token)){
                    $expected_token = md5(NONCE_KEY.$household_id);
                    if($token == $expected_token){
                        $access=TRUE;
                    }
                }
                    
                if(!empty($access) && church_admin_level_check('Directory'))
                {
                    if(!$self_edit)church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_new_household_display( $household_id);
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                }
            break;
            case 'everyone-visible':
                check_admin_referer('everyone-visible'); 
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');  
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_bulk_not_private();
            
            break;
            case 'edit_household':
                check_admin_referer('edit_household'); 
                if(church_admin_level_check('Directory')||$self_edit)
                {
                    if(!$self_edit)church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_edit_household( $household_id);
                }else{echo'<p>'.esc_html( __('You do not have permission to do that','church-admin' ) ).'</p>';}
                break;
            case 'delete_household':
            case 'delete-household':
                check_admin_referer('delete_household');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_delete_household( $household_id);
                
            break;
            case 'check-duplicates':
                check_admin_referer('check-duplicates');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people');   
                church_admin_module_dropdown('people');   
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_potential_duplicates();
                
            break;
            case 'edit_people':
                check_admin_referer('edit_people');
                if(church_admin_level_check('Directory')||$self_edit)
                {
                    if(!$self_edit)church_admin_module_dropdown('people');   
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');

                    church_admin_debug('functions.php line 5529 people_id:'.$people_id);
                    church_admin_edit_people( $people_id,$household_id);
                
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>'; 
                    return;
                }
            break;
            case 'delete-all':
                check_admin_referer('delete-all');
                if(!current_user_can('manage_options') )return '<p>'.esc_html( __('Only site admins can do this','church-admin' ) ).'</p>';
                if(!$self_edit)church_admin_module_dropdown('people'); 
                if(!empty( $_POST['sure'] ) )
                {
                    $wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_people');
                    $wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_household');
                    $wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_people_meta');
                    echo'<h2>'.esc_html( __('All households deleted','church-admin' ) ).'</h2>';
                }
                else
                {
                    echo'<form action="" method="post"><h2><strong>'.esc_html( __("Are you sure you want to delete everyone?",'church-admin' ) ).'</strong></h2><p><input type="hidden" name="sure" value="yes" /><input type="submit"  class="button-secondary" value="'.esc_html( __('Yes, delete everyone','church-admin' ) ).'" /></p></form>';
                }
                
            break;
            case 'delete_people':
                check_admin_referer('delete_people');
                if(church_admin_level_check('Directory')||$self_edit)
                {
                    if(!$self_edit)church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_delete_people( $people_id,$household_id,TRUE,TRUE);
                }
                else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
            break;
            case 'church_admin_search':
                check_admin_referer('church_admin_search');
                if(wp_verify_nonce('ca_search_nonce','ca_search_nonce') )
                {
                    if(!$self_edit)church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_search( church_admin_sanitize($_POST['ca_search'] ));
                }
            break;
         
            /*************************************
            *
            *		contact form
            *
            **************************************/
            case 'contact-form':
                check_admin_referer('contact-form');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Contact_Form') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Contact Form','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('contact-form'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/contact_form.php'); 
                church_admin_contact_form_list();
            break;
            case 'contact-form-settings':
                check_admin_referer('contact-form-settings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Contact_Form') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Contact Form','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('contact-form'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/contact_form.php'); 
                church_admin_contact_form_settings();
            break;
            case 'delete-contact-message':
                check_admin_referer("delete_contact_message");
                if(!church_admin_level_check('Contact_Form') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Contact Form','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('contact-form'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/contact_form.php'); 
                church_admin_delete_contact_message((int) $_GET['id'] );
            break;
            /*************************************
            *
            *		ERRORS
            *
            **************************************/
            case 'activation-log-clear':
                check_admin_referer('activation-log-clear');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') ) ).'</h2></div>';
                    return;
                }
                church_admin_activation_log_clear();
            break;

            case 'installation-errors':
                check_admin_referer('installation-errors');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') ) ).'</h2></div>';
                    return;
                }
                echo'<h2>'.esc_html( __('Installation errors','church-admin' ) ).'</h2>';	
                $error=get_option('church_admin_plugin_error');
                if(!empty( $error) )
                {
                    
                    echo'<p>'.esc_html( __('This is what was saved as an error during activation ','church-admin' ) ).'"'.$error.'"</p>';
                    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=activation-log-clear','activation-log-clear').'">'.esc_html( __('Clear activation errors log','church-admin' ) ).'</a></p><hr/>';
                }
                else{
                    echo'<p>'.esc_html( __('No installation errors recoreded','church-admin' ) ).'</p>';
                }
            break;

            /*************************************
            *
            *		Events
            *
            **************************************/
           
            case 'edit_event':
                check_admin_referer('edit_event');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                    
                church_admin_edit_event( $event_id);

            break;
            case 'edit_ticket_type':
                check_admin_referer('edit_ticket_type');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_edit_ticket_type( $event_id,$ticket_id);
                
            break;
            case 'delete_ticket_type':
                check_admin_referer('delete_ticket_type');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_delete_ticket_type( $event_id,$ticket_id);
            break;
            case 'view-event':
        
                check_admin_referer('view-event');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_view_event( $event_id);
                
            break;
            case   'delete_event':
                check_admin_referer('delete_event');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
             
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_delete_event( $event_id);
            break;
            case 'edit-booking':
                check_admin_referer('edit-booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_edit_booking( $ticket_id,$event_id,$booking_ref);
            break;
            case 'delete_booking':
                check_admin_referer('delete_booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_delete_booking( $ticket_id);
            break;
            case 'event-checkin':
                if(!church_admin_level_check('Events') )
                    {
                        echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                        return;
                    }
                    church_admin_module_dropdown('events'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                echo church_admin_ticket_checkin($event_id);
            break;
            case 'view_bookings':
                check_admin_referer('view_bookings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_view_bookings( $event_id);
            break;
            case 'show-events':
            case 'events':
                check_admin_referer('events');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Events') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Events','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('events'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php'); 
                church_admin_events();
                
            break;

                
            /*************************************
            *
            *		FACILITIES
            *
            **************************************/
            case 'facilities':
            case 'church_admin_facilities':
                check_admin_referer('facilities');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Facilities') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Facilities','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('facilities'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
                church_admin_facilities($facilities_id);
                
            break;
            case 'edit_facility':
            
                check_admin_referer('edit_facility');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Facilities') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Facilities','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('facilities'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
                    church_admin_edit_facility( $facilities_id);
                
            break;
            case 'delete_facility':
                check_admin_referer('delete_facility');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Facilities') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Facilities','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('facilities'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
                church_admin_delete_facility( $facilities_id);
                
            break;
            case 'facility-bookings':
                check_admin_referer('facility-bookings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Facilities') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Facilities','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('facilities'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
                    church_admin_facility_bookings( $facilities_id);
    
            break;
            case 'facility-hires':
                check_admin_referer('facility-hires');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Facilities') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Facilities','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('facilities'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/facilities.php');
                church_admin_facility_hire( $facilities_id);
    
            break;  
            /*************************************
            *
            *		FOLLOW UP
            *
            **************************************/
            case'funnel':
            case 'follow-up':
            case 'church_admin_funnel_list':
                check_admin_referer('funnel');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                church_admin_module_dropdown('follow-up'); 
                if(!church_admin_level_check('Funnel') ){
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Follow Up Funnel','church-admin') )).'</h2></div>';
                    return;
                }else{ 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/funnel.php');
                    church_admin_funnel_list();
                }
            break;
            case 'add-funnel':
            case 'edit_funnel':
                check_admin_referer('edit_funnel');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Funnel') )
                { 
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Follow Up Funnel','church-admin') )).'</h2></div>';
                    return;
                }
            break;
            case 'delete_funnel':
                check_admin_referer('delete_funnel');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Funnel') )
                {  
                    
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Follow Up Funnel','church-admin') )).'</h2></div>';
                    return;

                }
             
                church_admin_module_dropdown('follow-up'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/funnel.php');
                church_admin_delete_funnel( $funnel_id);
                break;
            case 'assign_funnel':
                check_admin_referer('assign_funnel');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Funnel') )
                {  
                    
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Follow Up Funnel','church-admin') )).'</h2></div>';
                    return;

                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/people_activity.php');
                church_admin_assign_funnel();
            break;
            case 'email_follow_up_activity':
                check_admin_referer('email_follow_up_activity');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Funnel') ){
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Follow Up Funnel','church-admin') )).'</h2></div>';
                    return;
                }
                else
                { 
                    church_admin_module_dropdown('follow-up'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/funnel.php');
                    church_admin_edit_funnel( $funnel_id,$people_type_id);
                }
                church_admin_module_dropdown('follow-up'); 
                
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/people_activity.php');
                church_admin_email_follow_up_activity();
            break;
            case 'follow_up_completed':
                //no nonce as coming from an email
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Funnel') )
                { 
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Follow Up Funnel','church-admin') )).'</h2></div>';
                    
                    return;
                }
                church_admin_module_dropdown('follow-up'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/funnel.php');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/people_activity.php');
                church_admin_follow_up_completed( $id);
            break;
                
                
           
            /*************************************
            *
            *		GIVING
            *
            **************************************/
            case 'pledges':
                check_admin_referer('pledges');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pledges.php');
                echo church_admin_pledges_list(); 
            break;
            case 'edit-pledge':
                check_admin_referer('edit-pledge');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pledges.php');
                echo church_admin_edit_pledge($pledge_id); 
            break;
            case 'delete-pledge':
                check_admin_referer('delete-pledge');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pledges.php');
                echo church_admin_delete_pledge($pledge_id); 
            break;
            case 'payment-gateway-setup':
            case 'paypal-setup':
            case 'giving-paypal-setup':
                check_admin_referer('payment-gateway-setup'); 
                if($licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }   
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                church_admin_paypal_setup();
            break;
            case 'upgrade':
                check_admin_referer('upgrade'); 
                church_admin_app_purchase();
            break;
            case 'giving':
                check_admin_referer('giving'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }

                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_giving_list(); 
                
            break;
            case 'giving-csv':
                check_admin_referer('giving-csv'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_giving_csv_form(); 
                
            break;
            case 'gift-aid-csv':
                check_admin_referer('gift-aid-csv'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                church_admin_date_picker_script();
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');           
                church_admin_gift_aid_csv();
                
            break;
            case 'donation-receipt-template':
                check_admin_referer('donation-receipt-template'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');  
                church_admin_giving_receipt_template();
            break;
            case 'email-donor-receipt':
                check_admin_referer('email-donor-receipt'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');    
                church_admin_donation_receipt_email($id,TRUE) ;        
                //church_admin_email_donor_receipt( church_admin_sanitize($_GET['receipt_id'] )));
            break;  
            case 'funds':
                check_admin_referer('funds'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                ////check_admin_referer('funds');
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_giving_funds(); 
                
            break;
                case 'edit-fund':
                    check_admin_referer('edit-fund'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                ////check_admin_referer('edit-fund');
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_edit_fund( $fund_id); 
                
            break;
            case 'delete-fund':
                check_admin_referer('delete-fund'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                ////check_admin_referer('delete-fund');
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_delete_fund( $fund_id); 
                
            break;
            case 'edit-gift':
                check_admin_referer('edit-gift'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_giving_edit( $giving_id); 

            break;
            case 'fix-anon-gifts':
                check_admin_referer('fix-anon-gifts'); 
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_fix_anon_giving();
                church_admin_giving_list();
            break;
            case 'delete-gift':
                check_admin_referer('delete-gift');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('delete-gift');
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_giving_delete( $gift_id); 
                
            break;
            case 'refund-gift':
                check_admin_referer('refund-gift');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('delete-gift');
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'stripe/index.php');
                church_admin_stripe_delete( $gift_id); 
                
            break;
            case 'donor-giving':
            case 'donor-gift':
                check_admin_referer('donor-gift');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                
                if(!church_admin_level_check('Giving') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Giving','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('giving'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/giving.php');
                church_admin_donor_giving( $people_id); 

            break; 
            /*************************************
            *
            *		Kiosk App
            *
            **************************************/
            case 'kiosk-app':
                check_admin_referer('kiosk-app');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kiosk-app.php');
                church_admin_kiosk_app();

            break;

            /*************************************
            *
            *		KIDS WORK
            *
            **************************************/
            case 'kidswork-pdf':
            
                check_admin_referer('kidswork-pdf');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
                    church_admin_kidswork_PDF();
                
            break;
            case 'kidswork-checkin-pdf':
                check_admin_referer('kidswork-checkin-pdf');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
                church_admin_kidswork_checkin_PDF();
                
            break;
            case 'edit_kidswork':
                check_admin_referer('edit_kidswork');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
                church_admin_edit_kidswork( $id);
                
            break;
            case 'delete_kidswork':
                check_admin_referer('delete_kidswork');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
                church_admin_delete_kidswork( $id);
                
            break;
            case 'children':

            case 'kidswork': 
            case 'childrens-work':
               
                check_admin_referer('childrens-work');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
                    echo'<h2>'.esc_html( __("Age related groups",'church-admin' ) ).'</h2>';
                    echo church_admin_kidswork();
            
            break;
            case 'edit_safeguarding':
                check_admin_referer('edit_safeguarding');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //legacy
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
                    church_admin_edit_safeguarding( $people_id);
                
            break;
            case 'child-protection':
                check_admin_referer('child-protection');
                if( $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('ChildProtection') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Child Protection Ministry','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('child-protection'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/child-protection.php');
                church_admin_child_protection_reporting();


            break;
            case 'edit-child-protection-incident':
                check_admin_referer('edit-child-protection-incident');
                if( $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('ChildProtection') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Child Protection Ministry','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('child-protection'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/child-protection.php');
                church_admin_edit_child_protection_incident($ID)
                ;

            break;
            case 'view-child-protection-incident':
                check_admin_referer('view-child-protection-incident');
                if( $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('ChildProtection') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Child Protection Ministry','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('child-protection'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/child-protection.php');
                church_admin_view_child_protection_incident($ID)
                ;

            break;
            case 'safeguarding':
                check_admin_referer('safeguarding');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/safeguarding.php');
                church_admin_safeguarding_main();
                
            break;
            case 'edit-person-safeguarding':
                check_admin_referer('edit-person-safeguarding');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_date_picker_script();
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/safeguarding.php');
                church_admin_edit_person_safeguarding( $people_id);
                
            break;
            case 'edit-safeguarding-field':
                check_admin_referer('edit-safeguarding-field');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/safeguarding.php');
                church_admin_edit_safeguarding_field( $ID);
                
            break;
            case 'legacy-safeguarding':
                check_admin_referer('legacy-safeguarding');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Kidswork') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('childrens-work'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/kidswork.php');
                church_admin_safeguarding_legacy_main();
                
            break;


                
            /*************************************
            *
            *		MEDIA
            *
            **************************************/
            case 'itunes-importer':
                check_admin_referer('itunes-importer');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media');  
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/itunes-importer/importer.php');
                church_admin_import_itunes();
            break;
            case 'podcast':
            case 'media':
                check_admin_referer('media');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                church_admin_module_dropdown('media');  
                echo'<h2>'.esc_html( __('Sermon podcast files','church-admin' ) ).'</h2>';
                ca_podcast_list_files();
            break;
          
            case 'migrate_sermon_manager':
                check_admin_referer('migrate_sermon_manager');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                church_admin_migrate_sermon_manager();
            
            break;  
            case 'migrate_advanced_sermons':
                check_admin_referer('migrate_advanced_sermons');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                    church_admin_migrate_advanced_sermons();
                
            break; 
            case 'set-sermon-page':
                check_admin_referer('set-sermon-page');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                church_admin_set_sermon_page();
            break;
            case 'migrate_sermon_browser':
                check_admin_referer('migrate_sermon_browser');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                    church_admin_migrate_sermon_browser();
                
            break;          
            case'list_speakers':
                check_admin_referer('list_speakers');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(church_admin_level_check('Sermons') )
                {
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                    church_admin_module_dropdown('media'); 
                    ca_podcast_list_speakers();
                }
            break;
            case'edit_speaker':
                check_admin_referer('edit_speaker');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_edit_speaker( $id);
                    
            break;
            case'delete_speaker':
                check_admin_referer('delete_speaker');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_delete_speaker( $id);
            break;
            case 'sermon-series':
            case'list_sermon_series':
                check_admin_referer('sermon-series');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                        ca_podcast_list_series();
                
            break;
            case 'edit-sermon-series':
           
                check_admin_referer('edit-sermon-series');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_edit_series( $id);
                
            break;
            case'delete-sermon-series':
                check_admin_referer('delete-sermon-series');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_delete_series( $id);
            break;
            case'list_files':
                check_admin_referer('list_files');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(church_admin_level_check('Sermons') )
                { 
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_list_files();
            break;
            case 'upload-mp3':
           
                check_admin_referer('upload-mp3');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                church_admin_edit_sermon( $id);
                
            break;
            case'delete-media-file':
                check_admin_referer('delete-media-file');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('delete_podcast_file');
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_delete_media_file( $id);
            break;
            case 'refresh-podcast':
                check_admin_referer('refresh-podcast');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_xml();
                echo'<div class="notice notice-success"><h2>'.esc_html( __('Podcast XML file updated','church-admin' ) ).'</h2></div>';
                ca_podcast_list_files();
            break;
           
            case'add-media-file':
                //check_admin_referer('add-media-file');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                ca_podcast_file_add( $file);
            break;
            case'check-media-files':
                check_admin_referer('check-media-files');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sermon-podcast.php');
                    ca_podcast_check_files();
                
            break;
           
            case'podcast-settings':
                check_admin_referer('podcast-settings');
                if($licence!='basic' && $licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Sermons') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sermons','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('media'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/podcast-settings.php');
                ca_podcast_settings();
                
            break;

            /*************************************
            *
            *		MEMBER TYPE
            *
            **************************************/
        case 'member-types':
            check_admin_referer('member-types');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/member_type.php');
                    church_admin_member_type();
                
            break;
            case 'add-member-type':
            case 'edit-member-type':
            case 'church_admin_edit_member_type':
                check_admin_referer('edit-member-type');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/member_type.php');
                    church_admin_edit_member_type( $member_type_id);
                
            break;
            case 'church_admin_delete_member_type':
            case 'delete-member-type':
                check_admin_referer('delete-member-type');
                if(!church_admin_level_check('Directory') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Directory','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('people'); 
                //check_admin_referer('delete-member-type');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/member_type.php');
                church_admin_delete_member_type( $member_type_id);
            break;

            /*************************************
            *
            *		MINISTRIES
            *
            **************************************/
            case 'edit-ministry':
           
                check_admin_referer('edit-ministry');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Ministries',NULL, $id) )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Ministries','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('ministries');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/departments.php');
                church_admin_edit_ministry( $id);
                
            break;
            case 'delete-ministry': 
                check_admin_referer('delete-ministry');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }           
                if(!church_admin_level_check('Ministries') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Ministries','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('ministries');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/departments.php');
                church_admin_delete_ministry( $id);
                
            break;
            case 'ministries':
           
            case 'ministry_list':
                check_admin_referer('ministries');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Ministries') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Ministries','church-admin') )).'</h2></div>';
                    return;
                }
                echo'<h2>'.esc_html( __('Ministries','church-admin' ) ).'</h2>';
                church_admin_module_dropdown('ministries');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/departments.php');
                church_admin_ministries_list();
                
            break;
            case 'view_ministry':    
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }        
                if(!church_admin_level_check('Ministries') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Ministries','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('ministries');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/departments.php');
                    church_admin_view_ministry( $id);
                
            break;
            case 'volunteers':
                check_admin_referer('volunteers');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Ministries') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Ministries','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('ministries');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/volunteer.php');
                echo church_admin_volunteer_display();
                
            break;

            /*************************************
            *
            *		ROTA
            *
            **************************************/

            case 'show-cron':
                check_admin_referer('show-cron');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                church_admin_cron_check();
                
            break;
            case 'copy-rota-data':
                check_admin_referer('copy-rota-data');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');

                church_admin_copy_rota_data();
            break;
            case 'rota':
                check_admin_referer('rota');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');

                church_admin_rota_list( $service_id,$mtg_type);
                
            break;
            case 'add-three-months':
                check_admin_referer('add-three-months');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                church_admin_three_months_rota( $service_id,$mtg_type);
            break;
            case 'ministry-rota':
                check_admin_referer('ministry-rota');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                echo church_admin_edit_ministry_rota($mtg_type,$service_id);
            break;
            case 'edit_rota':
            case'edit-rota': 
                check_admin_referer('edit-rota');	
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                church_admin_edit_rota( $rota_date,$mtg_type,$service_id);
                
            break;
            case 'delete_rota':
                check_admin_referer('delete_rota');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                church_admin_delete_rota( $rota_date,$mtg_type,(int)$_GET['service_id'] );
                
            break;
           
            
            /*************************************
            *
            *		ROTA SETTINGS
            *
            **************************************/
            case 'not-available':
                check_admin_referer('not-available');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'display/not-available.php');
                echo church_admin_not_available();
            break;
            case 'rota-settings':
            case'church_admin_rota_settings_list':
                    check_admin_referer('rota-settings');
                    if($licence!='standard' && $licence!='premium'){
                        echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                        return;
                    }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota_settings.php');
                church_admin_rota_settings_list();
                
            break;
            case 'edit-rota-job':
          
                check_admin_referer('edit-rota-job');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota_settings.php');
                church_admin_edit_rota_settings( $id);
                
            break;
            case 'delete-rota-job':
                check_admin_referer('delete-rota-job');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('delete_rota_settings');
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota_settings.php');
                church_admin_delete_rota_settings( $id);
            break;
           
            
            case 'auto-email-rota':
                check_admin_referer('auto-email-rota');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                church_admin_rota_auto_email();
                
            break;
            
            case 'auto-sms-rota':
                check_admin_referer('auto-sms-rota');
                    if($licence!='standard' && $licence!='premium'){
                        echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                        return;
                    }
                    if(!church_admin_level_check('Rota') )
                    {
                        echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                        return;
                    }
                    church_admin_module_dropdown('rota');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                    church_admin_rota_auto_sms();
                    
                break;
            case 'email-rota':
                check_admin_referer('email-rota');
                
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                $service_id = !empty($_REQUEST['service_id'])?church_admin_sanitize($_REQUEST['service_id']): null;
                $date = !empty($_REQUEST['rota_date'])?church_admin_sanitize($_REQUEST['rota_date']): null;
                if (!empty($service_id) && church_admin_int_check($service_id) && !empty($date) && church_admin_checkdate($date)) {
                        church_admin_email_rota( $service_id,$date);
                }
                else {
                    church_admin_email_rota_form();
                }
                
            break; 
            case'pdf-rota':
                check_admin_referer('pdf-rota');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                    church_admin_rota_pdf_menu();
                
            
            break;
            case'csv-rota':
                check_admin_referer('csv-rota');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');
                    church_admin_rota_csv_menu();
                
            
            break;
            case 'sms-rota':
                check_admin_referer('sms-rota');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Rota') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Schedule','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('rota');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/rota.new.php');           
                church_admin_sms_rota();
               
            break;
            /*************************************
            *
            *		SERVICES
            *
            **************************************/
            
           
          
            case 'services-list':
                check_admin_referer('services-list');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                echo'<h2>'.esc_html( __('Services','church-admin' ) ).'</h2>';
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/services.php');
                echo church_admin_service_list( $message);
            
                
            break; 
            
            case'site-list':
                check_admin_referer('site-list');
                    if($licence!='standard' && $licence!='premium'){
                        echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                        return;
                    }
                echo'<h2>'.esc_html( __('Sites','church-admin' ) ).'</h2>';
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sites.php');
                    
                    church_admin_site_list();
                
                
            break; 
        
            case 'edit-service':
           
                check_admin_referer('edit-service');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/services.php');  
                church_admin_edit_service( $id);
                
            break;
            case 'delete-service':
           
                check_admin_referer('delete-service');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/services.php');  
                church_admin_delete_service( $id);
                
            break;
            case  'service-prebooking':
                check_admin_referer('service-prebooking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/covid-prebooking.php');  
                echo church_admin_covid_attendance_list();
                
            break;
            case 'delete_service_booking'  :
                check_admin_referer('delete_service_booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/covid-prebooking.php');
                    echo church_admin_delete_service_booking( $id);
            break;    
            case 'delete_bubble_booking'  :
                check_admin_referer('delete_bubble_booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                        church_admin_module_dropdown('services');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/covid-prebooking.php');
                    echo church_admin_delete_bubble_booking( $id);
            break;  
            case 'edit_bubble_booking'  :
                check_admin_referer('edit_bubble_booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/covid-prebooking.php');
                    echo church_admin_edit_bubble_booking( $id);
            break;
            case 'add_bubble_booking_to_service'  :
                check_admin_referer('add_bubble_booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/covid-prebooking.php');
                echo church_admin_add_bubble_booking_to_service( $id);
            break;
            case 'add_bubble_booking'  :
                check_admin_referer('add_bubble_booking');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }    
                require_once(plugin_dir_path(dirname(__FILE__) ).'display/covid-prebooking.php');
                echo'<h2>'.esc_html( __('Add service pre-booking','church-admin' ) ).'</h2>';

                echo church_admin_covid_attendance( $service_id,'bubbles',10,365,get_option('church_admin_default_from_email') );
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/covid-prebooking.php'); 
                if(!empty( $_POST) ) echo church_admin_covid_attendance_list();
            break;
            case  'delete_service':
                check_admin_referer('delete_service');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                } 
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/services.php');  
                church_admin_delete_service( $id);
            
            break;
            case 'delete_site':
                check_admin_referer('delete_site');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sites.php'); 
                church_admin_delete_site( $site_id);
            break;
            case 'edit-site':
            case 'edit_site':
                check_admin_referer('edit_site');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Service') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Service','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('services');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sites.php'); 
                    church_admin_edit_site( $site_id);
                
            break;
            /*************************************
            *
            *       SESSIONS
            *
            *************************************/
            case 'sessions': 
                check_admin_referer('sessions');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }               
                if(!church_admin_level_check('Sessions') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Sessions','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sessions.php');
                echo'<h2>'.esc_html( __('Sessions','church-admin' ) ).'</h2>';
                echo church_admin_sessions();
            break;
                
            /*************************************
            *
            *		SMALL GROUPS
            *
            **************************************/
            case'small_groups':
                check_admin_referer('small_groups');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                echo church_admin_smallgroups_main();
                
            break;
            case 'delete_cell':
                check_admin_referer('delete_cell');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html( sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                   
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');church_admin_delete_cell( $ID);
            break;
            case 'cleanup-groups':
                check_admin_referer('cleanup-groups');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                wp_enqueue_script('church_admin_google_maps_api');
                wp_enqueue_script('church_admin_sg_map_script');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                
                echo church_admin_smallgroups_cleanup();
                
                church_admin_module_dropdown('groups');
               
                require_once(plugin_dir_path(dirname(__FILE__) ).'display/small-group-list.php');
                echo church_admin_small_group_list(1,13,TRUE); 
                
            break;
            case 'show-groups':
                check_admin_referer('show-groups');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups');
                require_once(plugin_dir_path(dirname(__FILE__) ).'display/small-groups.php');
                echo church_admin_frontend_small_groups(NULL,FALSE);
                
            break;
            case 'unattached':
                check_admin_referer('unattached');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                church_admin_unattached_list();
            break;
            case 'groups':
                check_admin_referer('groups');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                    wp_enqueue_script('church_admin_sg_map_script');
                    wp_enqueue_script('church_admin_google_maps_api');
                    
                    echo'<h2>'.esc_html( __('Small groups','church-admin' ) ).'</h2>';
                    church_admin_module_dropdown('groups');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'display/small-group-list.php');
                    church_admin_smallgroup_metrics();
                    echo church_admin_small_group_list(1,1,TRUE);  
            break;
            case 'smallgroup-show-pdf-form':
                check_admin_referer('smallgroup-show-pdf-form');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups');    
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                church_admin_smallgroup_PDF_form();
                
            break;
            case 'small-group-metrics':
                check_admin_referer('small-group-metrics');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                    church_admin_smallgroup_metrics();
                
            break;
            
            case 'delete-all-groups':
                check_admin_referer('delete-all-groups');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups');   
                if(!empty( $_POST['sure'] ) )
                {
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                    church_admin_delete_all_small_groups();
                    echo '<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&section=small_groups&amp;action=edit-small-group",'edit-small-group').'">'.esc_html( __('Add a small group','church-admin' ) ).'</a></p>';
                
                }
                else
                {
                    echo'<form action="" method="post"><h2><strong>'.esc_html( __("Are you sure you want to delete all the groups?",'church-admin' ) ).'</strong></h2><p><input type="hidden" name="sure" value="yes" /><input type="submit"  class="button-secondary" value="'.esc_html( __('Yes, delete all the','church-admin' ) ).'" /></p></form>';
                }
                
                
            
                
            break;	
            case 'smallgroup-structure':
            case 'small-group-structure':
                check_admin_referer('smallgroup-structure');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                echo church_admin_small_group_structure();
            break;
            case 'oversight-list': 
                check_admin_referer('oversight-list');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }   
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');            
                    echo church_admin_oversight_list();
            
            break;
            case'remove_from_smallgroup':
                check_admin_referer('remove_from_smallgroup');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('remove');
                church_admin_module_dropdown('groups');
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php');
                church_admin_remove_from_smallgroup( $people_id,$smallgroup_id);
            break;
            case'whosin':
                check_admin_referer('whosin');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                ////check_admin_referer('whosin');
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php'); echo church_admin_whosin( $id);
            break;
            case  'edit-small-group':
            case 'edit-group':
            case 'add-group':
                check_admin_referer('edit-small-group');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                ////check_admin_referer('edit-group');
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                church_admin_module_dropdown('groups'); 
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php'); 
                echo church_admin_edit_small_group( $id);
            break;
            case  'delete-group':
                check_admin_referer('delete-group');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                //check_admin_referer('delete_small_group');
                
            
                if(!church_admin_level_check('Groups') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                    return;
                }
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/small_groups.php'); 
                echo church_admin_delete_small_group( $id);
            break;
      
            /*************************************
            *
            *		SETTINGS
            *
            **************************************/
            case 'choose-filters':
                check_admin_referer('choose-filters');
                if(current_user_can('manage_options') )
                {
                    church_admin_module_dropdown('settings');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');                 
                    church_admin_choose_filters();
                }
            break;
            
            case 'delete-smtp-settings':
                check_admin_referer('delete-smtp-settings');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(!church_admin_level_check('Bulk_Email') )
                {
                    echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Bulk Email','church-admin') )).'</h2></div>';
                    return;
                }
                delete_option('church_admin_smtp_settings');
                update_option('church_admin_transactional_email_method','native');
                $bulk_method = get_option('church_admin_transactional_email_method');
                if(!empty($bulk_method) && $bulk_method='smtpserver'){
                    update_option('church_admin_bulk_email_method','native');
                }
                echo '<div class="notice notice-success"><h2>'.esc_html( __('SMTP settings deleted','church-admin' ) ).'</h2><p>'.esc_html( __('Email method set to natice WordPress','church-admin' ) ).'</p></div>';
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php'); 
                church_admin_email_settings();

            break;
            case 'toggle-debug-mode':
                check_admin_referer('toggle-debug-mode');
                if(current_user_can('manage_options') )
                {
                    church_admin_module_dropdown('settings');
                   
                   
                   
                    church_admin_settings_callback();
                }
                
            break;
            case 'clear-debug':
                check_admin_referer('clear-debug');
                if(!current_user_can('manage_options') )
                {
                    echo'<div class="error"><p>'.esc_html( __("Only admins can clear the debug log",'church-admin' ) ).'</p></div>';
                    return;
                }
                church_admin_module_dropdown('settings');
                $upload_dir = wp_upload_dir();
	            $debug_path=$upload_dir['basedir'].'/church-admin-cache/debug_log.php';
                if(file_exists( $debug_path) )unlink( $debug_path);
                echo'<div class="notice notice-success"><h2>'.esc_html( __("Debug log file deleted",'church-admin' ) ).'</h2></div>';
                church_admin_settings_callback();
            break;
            case 'debug-log':
                check_admin_referer('debug-log');
                if(current_user_can('manage_options') )
                {
                    church_admin_module_dropdown('settings');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                    church_admin_debug_log();
                }
        break;
            case 'restrict-access':
                check_admin_referer('restrict-access');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(current_user_can('manage_options') )
                {
                    church_admin_module_dropdown('settings');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                    echo church_admin_restrict_access();
                }
        break;
        case 'people-types':
            check_admin_referer('people-types');
                if(current_user_can('manage_options') )
                {
                    church_admin_module_dropdown('settings');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                    church_admin_people_types_list();
                }
        break;
        case 'choose-modules':
        
            check_admin_referer('choose-modules');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
                if(current_user_can('manage_options') )
                {
                    
                    church_admin_module_dropdown('settings');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');           
                    church_admin_modules();
                }
        break;
        case 'marital-status':
            check_admin_referer('marital-status');
                if(current_user_can('manage_options') )
                {
                    church_admin_module_dropdown('settings');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');           
                    church_admin_marital_status();
                }
            break;       
        case 'permissions':
                check_admin_referer('permissions');
                if(current_user_can('manage_options') )
                {
                    church_admin_module_dropdown('settings');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/permissions.php');
                    church_admin_permissions();
                }
        break;
       case 'roles':
            check_admin_referer('roles');
            if(current_user_can('manage_options') )
            {            
                church_admin_module_dropdown('settings');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                church_admin_roles();
            }
        break;
        
        case 'smtp-settings':
            check_admin_referer('smtp-settings');
            if(current_user_can('manage_options') )
            {            
                church_admin_module_dropdown('settings');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');       
                church_admin_smtp_settings();    
            }
        break;
        case 'bible-version':
            check_admin_referer('bible-version');
            if(!church_admin_level_check('Bible') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('settings');
                require_once(plugin_dir_path(dirname(__FILE__) ).'app/app-admin.php');
                church_admin_bible_version();
            
        break;
        case 'general-settings':
        case 'church_admin_settings':
        case 'settings':
            check_admin_referer('settings');
            if(current_user_can('manage_options') )
            {
                church_admin_module_dropdown('settings');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                church_admin_general_settings();
            }
        break;
        case 'grcode-generator':
            check_admin_referer('grcode-generator');
                church_admin_module_dropdown('settings');
                church_admin_qrcode_generator();

        break;
        case 'new-user-email-template':
            check_admin_referer('new-user-email-template');
            if(current_user_can('manage_options') )
            {
            
                church_admin_module_dropdown('settings');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
                church_admin_new_user_template();
            }
        break;
        case 'global-communications-settings':
            check_admin_referer('global-communications-settings');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(current_user_can('manage_options') )
            {
                church_admin_module_dropdown('settings');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');    
                church_admin_global_communications_settings();
            }
        break;
        case 'registration-email-settings':
            check_admin_referer('registration-email-settings');
            if(current_user_can('manage_options') )
            {
                church_admin_module_dropdown('settings');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/automations.php');
                church_admin_registration_follow_up_email();
            }
        break;
        case 'shortcode-generator':
            check_admin_referer('shortcode-generator');
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('settings');
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/shortcode-generator.php');
            echo church_admin_shortcode_generator();
        break;
        case'edit_people_type':
            check_admin_referer('edit_people_type');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                return;
            }
            church_admin_module_dropdown('settings');
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
            echo church_admin_edit_people_type( $ID);
        
        break;
        case'delete_people_type':
            check_admin_referer('delete_people_type');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Directory') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Groups','church-admin') )).'</h2></div>';
                return;
            }
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/settings.php');
            echo church_admin_delete_people_type( $ID);
            echo church_admin_people_types_list();
            break;
        /*************************************
        *
        *       UNITS
        *
        **************************************/
        case 'units':
            check_admin_referer('units');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Units') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Units','church-admin') )).'</h2></div>';
                return;
            }
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/units.php');
            church_admin_units_list();
        break;
        case 'edit-unit':
        case 'add-unit':
            check_admin_referer('edit-unit');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Units') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Units','church-admin') )).'</h2></div>';
                return;
            }
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/units.php');
            church_admin_edit_unit( $unit_id);
        break;


        case 'show-subunits':
            check_admin_referer('show-subunits');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Units') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Units','church-admin') )).'</h2></div>';
                return;
            }
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/units.php');
            church_admin_show_subunits( $unit_id);
        break;
        case 'edit-subunit':
            check_admin_referer('edit-subunit');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Units') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Units','church-admin') )).'</h2></div>';
                return;
            }
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/units.php');
            church_admin_edit_subunit( $unit_id,$subunit_id);
        break;
        case 'delete-unit':
            check_admin_referer('delete-unit');
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Units') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Units','church-admin') )).'</h2></div>';
                return;
            }
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/units.php');
            church_admin_delete_unit( $unit_id);
        break;    
        case 'delete-subunit':
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            if(!church_admin_level_check('Units') )
            {
                echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Units','church-admin') )).'</h2></div>';
                return;
            }
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/units.php');
            church_admin_delete_subunit( $unit_id,$subunit_id);
        break;
        /*************************************
        *
        *       VOLUNTEERS
        *
        **************************************/
        case 'approve-volunteer':
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            /******************************************************
             * nonce generated by register functions on front end 
             * and sentby email to admin won't work normally
             * because a nonce uses user_id etc
             * we will check nonce for use within admin area, 
             * but also allow access if a logged in user with 
             * directory permissions
             * *************************************************/
            $access=FALSE;
            $nonce = church_admin_sanitize($$_REQUEST['_wpnonce']);
            if(wp_verify_nonce($nonce,'approve-volunteer')){$access=TRUE;}
            $expected_token = md5(NONCE_SALT.$people_id);
            $token = church_admin_sanitize($_REQUEST['token']);
            if($token = $expected_token){$access=TRUE;}
           

            if(!empty($access) && church_admin_level_check('Ministries')){
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/volunteer.php');
                church_admin_volunteer_approval( $people_id,$ministry_id);

            }

            
           
            
            
        break;
        case 'decline-volunteer':
          
            if($licence!='standard' && $licence!='premium'){
                echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                return;
            }
            /******************************************************
             * nonce generated by register functions on front end 
             * and sentby email to admin won't work normally
             * because a nonce uses user_id etc
             * we will check nonce for use within admin area, 
             * but also allow access if a logged in user with 
             * directory permissions
             * *************************************************/
            $access=FALSE;
            $nonce = church_admin_sanitize($$_REQUEST['_wpnonce']);
            if(wp_verify_nonce($nonce,'decline-volunteer')){$access=TRUE;}
            $expected_token = md5(NONCE_SALT.$people_id);
            $token = church_admin_sanitize($_REQUEST['token']);
            if($token = $expected_token){$access=TRUE;}
           

            if(!empty($access) && church_admin_level_check('Ministries')){
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/volunteer.php');
                church_admin_volunteer_decline( $people_id,$ministry_id);
            }
        break;   
            /*************************************
        *
        *     PASTORAL MODULE
        *
        **************************************/
        case 'pastoral':
            check_admin_referer('pastoral');
            $premiumLicence = get_option('church_admin_app_new_licence');
            if ( empty( $premiumLicence)||$premiumLicence!='premium')
            {
                echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
                church_admin_app_purchase();
                return;
            }
            $settings = get_option('church_admin_pastoral_settings');
            if(church_admin_level_check('Pastoral') || church_admin_ministry_team_check($settings['ministry_id']))
            {
                church_admin_module_dropdown('pastoral');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');
                church_admin_pastoral_main();
            }
            else{
                echo '<div class="notice notice-danger"><h2>'.esc_html(__('You need "Pastoral" permissions to access this page, or be part of the pastoral ministry team','church-admin' ) ).'</h2></div>';
            }
        break;
        case 'pastoral-visit-list':
            check_admin_referer('pastoral-visit-list');
            $premiumLicence = get_option('church_admin_app_new_licence');
            if ( empty( $premiumLicence)||$premiumLicence!='premium')
            {
                echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
                church_admin_app_purchase();
                return;
            }
            
            if(church_admin_level_check('Pastoral') || church_admin_ministry_team_check($settings['ministry_id']))
            {
                church_admin_module_dropdown('pastoral');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');
                church_admin_pastoral_visits_list(true);
            }else{
                echo '<div class="notice notice-danger"><h2>'.esc_html(__('You need "Pastoral" permissions to access this page, or be part of the pastoral ministry team','church-admin' ) ).'</h2></div>';
            }
        break;
        case 'pastoral-settings':
            check_admin_referer('pastoral-settings');
            $premiumLicence = get_option('church_admin_app_new_licence');
            if ( empty( $premiumLicence)||$premiumLicence!='premium')
            {
                echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
                church_admin_app_purchase();
                return;
            }
            if(church_admin_level_check('Pastoral') || church_admin_ministry_team_check($settings['ministry_id']))
            {
                
                church_admin_module_dropdown('pastoral');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');
                church_admin_pastoral_settings();
            }else{
                echo '<div class="notice notice-danger"><h2>'.esc_html(__('You need "Pastoral" permissions to access this page, or be part of the pastoral ministry team','church-admin' ) ).'</h2></div>';
            }
        break;
        case 'schedule-pastoral-visit':
            check_admin_referer('schedule-pastoral-visit');
            $premiumLicence = get_option('church_admin_app_new_licence');
            if ( empty( $premiumLicence)||$premiumLicence!='premium')
            {
                echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
                church_admin_app_purchase();
                return;
            }
            if(church_admin_level_check('Pastoral') || church_admin_ministry_team_check($settings['ministry_id']))
            {
                church_admin_module_dropdown('pastoral');
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');
                church_admin_schedule_pastoral_visit($people_id);
            }else{
                echo '<div class="notice notice-danger"><h2>'.esc_html(__('You need "Pastoral" permissions to access this page, or be part of the pastoral ministry team','church-admin' ) ).'</h2></div>';
            }
        break;


            case 'edit-pastoral-visit-note':
                check_admin_referer('edit-pastoral-visit-note');
                $premiumLicence = get_option('church_admin_app_new_licence');
                if ( empty( $premiumLicence)||$premiumLicence!='premium')
                {
                    echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
                    church_admin_app_purchase();
                    return;
                }
                if(church_admin_level_check('Pastoral') || church_admin_ministry_team_check($settings['ministry_id']))
                {
                    church_admin_module_dropdown('pastoral');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');

                    church_admin_edit_pastoral_note($people_id,$note_id);
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html(__('You need "Pastoral" permissions to access this page, or be part of the pastoral ministry team','church-admin' ) ).'</h2></div>';
                }
            break;
            case 'delete-pastoral-visit-note':
                check_admin_referer('delete-pastoral-visit-note');
                $premiumLicence = get_option('church_admin_app_new_licence');
                if ( empty( $premiumLicence)||$premiumLicence!='premium')
                {
                    echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
                    church_admin_app_purchase();
                    return;
                }
                if(church_admin_level_check('Pastoral') || church_admin_ministry_team_check($settings['ministry_id']))
                {
                    church_admin_module_dropdown('pastoral');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');
                    church_admin_delete_pastoral_note($people_id,$id);
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html(__('You need "Pastoral" permissions to access this page, or be part of the pastoral ministry team','church-admin' ) ).'</h2></div>';
                }
            break;
            case 'view-pastoral-visits':
                check_admin_referer('view-pastoral-visits');
                $premiumLicence = get_option('church_admin_app_new_licence');
                if ( empty( $premiumLicence)||$premiumLicence!='premium')
                {
                    echo '<p>'.esc_html( __('This feature is premium only, please upgrade to unlock') ).'</p>';
                    church_admin_app_purchase();
                    return;
                }
                if(church_admin_level_check('Pastoral') || church_admin_ministry_team_check($settings['ministry_id']))
                {
                    church_admin_module_dropdown('pastoral');
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/visitation.php');
        
                    church_admin_view_pastoral_notes($people_id);
                }else{
                    echo '<div class="notice notice-danger"><h2>'.esc_html(__('You need "Pastoral" permissions to access this page, or be part of the pastoral ministry team','church-admin' ) ).'</h2></div>';
                }
            break;
            case 'bulk-edit-anniversary':
                check_admin_referer('bulk-edit-anniversary');
                if(church_admin_level_check('Directory') )
                {
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_bulk_edit_wedding_anniversary();
                }
                else{echo'<p>'.esc_html( __("You don't have permissions for this page",'church-admin' ) ).'</p>';}
            break;
            case 'bulk-edit-dob':
                check_admin_referer('bulk-edit-dob');
                if(church_admin_level_check('Directory') )
                {
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_bulk_edit_date_of_birth();
                }
                else{echo'<p>'.esc_html( __("You don't have permissions for this page",'church-admin' ) ).'</p>';}
            break;
            case 'bulk-edit-custom':
                check_admin_referer('bulk-edit-custom');
                if(church_admin_level_check('Directory') )
                {
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_bulk_edit_custom_field();
                }
                else{echo'<p>'.esc_html( __("You don't have permissions for this page",'church-admin' ) ).'</p>';}
            break;

            case 'bulk-edit-comms-permissions':
                check_admin_referer('bulk-edit-comms-permissions');
                if($licence!='standard' && $licence!='premium'){
                    echo'<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
                    return;
                }
                if(church_admin_level_check('Directory') )
                {
                    church_admin_module_dropdown('people'); 
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                    church_admin_bulk_edit_comms_settings();
                }
                else{echo'<p>'.esc_html( __("You don't have permissions for this page",'church-admin' ) ).'</p>';}
            break;
            case 'test':
                if(current_user_can('manage_options')){
                    church_admin_test_function();
                }
                
            break;
            /*************************************
            *
            *		DEFAULT
            *
            **************************************/
            case 'people':
            default:
            if(church_admin_level_check('Directory') )
            {
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/directory.php');
                church_admin_module_dropdown('people'); 
                church_admin_search_form();
                church_admin_people_main();
            }else{echo'<p>'.esc_html( __("You don't have permissions for this page",'church-admin' ) ).'</p>';}break;

        }

    }
 
    
}
function church_admin_directory_issues_fixer()
{
        global $wpdb;
        
        $upload_dir = wp_upload_dir();
        $path=$upload_dir['basedir'].'/church-admin-cache/';
        $url=$upload_dir['baseurl'].'/church-admin-cache/';
       
        /******************************************************************
         * Remove Empty households
         ******************************************************************/
        echo'<h2>'.esc_html( __('Check for empty households','church-admin' ) ).'</h2>'."\r\n";
        $households=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_household');
        if(!empty( $households) )
        {
            $x=0;
            foreach( $households AS $household)
            {
                $people=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household->household_id.'"');
                if ( empty( $people) )
                {
                    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household->household_id.'"');
                    $x++;
                }
            }
            if(!empty( $x) )
            {
                echo '<p>'.esc_html(sprintf(__('%1$s empty households deleted','church-admin' ) ,(int)$x) ) .'</p>';
            }
            else echo '<p>'.esc_html( __('No empty households found','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
        }
        else
        {
            echo '<p>'.esc_html( __('No households found','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-no"  style="color:red"></span></p>';
        }

        /******************************************************************
         * FIX people records that have no houshold or 0 household_id
         ******************************************************************/
        echo'<h2>'.esc_html( __('Check for people who have become detached from a household','church-admin' ) ).'</h2>'."\r\n";
        $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id=0');
        if(!empty( $people) )
        {
            $zeroCount=$wpdb->num_rows;
            echo'<p>'.esc_html(sprintf(__('%1$s people detached from a  household','church-admin' ) ,(int)$zeroCount)).'</p>'."\r\n";
            foreach( $people AS $person)
            {
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,first_registered) VALUES ("","'.esc_sql(wp_date('Y-m-d')).'")');
                $household_id=$wpdb->insert_id;
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET household_id="'.(int)$household_id.'" WHERE people_id="'.(int)$person->people_id.'"');
                echo'<p>'.esc_html(sprintf(__('Fixed %1$s','church-admin' ) ,church_admin_formatted_name( $person) ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>'."\r\n";
            }

        }else{echo'<p>'.esc_html( __('No people detached from a household detected','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>'."\r\n";}
        /******************************************************************
         * MEMBER TYPE ISSUES
         ******************************************************************/
        echo'<h2>'.esc_html( __('Check for Member type issues','church-admin' ) ).'</h2>'."\r\n";
        $member_types_details_results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_member_types');
        if(!empty( $member_types_details_results) )
        {
            //form posted so fix invalid member types
            if(!empty( $_POST['fix-member-types'] ) )
            {
                if(!empty( $_POST['invalidMT'] ) )
                {
                    $invalidmts = church_admin_sanitize($_POST['invalidMT']);
                    $new_mt_id = !empty($_POST['new_mt_id'] )? church_admin_sanitize($_POST['new_mt_id']) : null;
                    foreach( $invalidmts AS $key=>$old_mt_id)
                    {
                        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET member_type_id="'.(int)$new_mt_id.'" WHERE member_type_id="'.(int)$old_mt_id.'"');
                    }
                }
            }
            $select=$mtSQL=array();
            foreach( $member_types_details_results AS $mtdRow)
            {
                $mtSQL[]=' member_type_id!="'.(int)$mtdRow->member_type_id.'" ';
                $select[]='<option value="'.(int)$mtdRow->member_type_id.'">'.esc_html( $mtdRow->member_type).'</option>';
                $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.(int)$mtdRow->member_type_id.'"');
                echo'<p>'.esc_html(sprintf(__('%1$s people with member type %2$s','church-admin' ) ,(int)$count, $mtdRow->member_type )).'</p>'."\r\n";
            }
            //check for people with no member type
            $count=0;
            $invalidMTs=$wpdb->get_results('SELECT member_type_id,COUNT(*) AS count FROM '.$wpdb->prefix.'church_admin_people WHERE ('.implode(' AND ',$mtSQL).') GROUP BY member_type_id');
            if(!empty( $invalidMTs) )
            {
                echo '<h3>'.esc_html( __('You have people in the directory of unknown member type, that needs fixing','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-no" style="color:red"></span></h3>'."\r\n";
                echo'<form action="" method="POST">';
                foreach( $invalidMTs AS $invalid)
                {
                    echo'<input type="hidden" name="invalidMT[]" value="'.(int)$invalid->member_type_id.'" />'."\r\n";
                    $count+=(int)$invalid->count;
                }
                echo'<table class="form-table><tr><th scope="row">'.esc_html(sprintf(__('Move %1$s people to a valid member type','church-admin' ) ,$count)).'</th><select name="new_mt_id">';
                echo implode("\r\n",$select);
                echo'</select></td></tr>'."\r\n";
                echo'<tr><td colspan=2><input type="hidden" name="fix-member-types" value="YES" /><input type="submit" class="button-primary" /></td></tr></table>'."\r\n";
            }else echo'<p>'.esc_html( __('There are no people with unknown member types stored','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>'."\r\n";
        }
        /******************************************************************
         * HEAD OF HOUSEHOLD ISSUES
         ******************************************************************/
        $householdsNeedingFixing=array();
        $households=$wpdb->get_results('SELECT household_id FROM '.$wpdb->prefix.'church_admin_household');
        if(!empty( $households) )
        {
            /**************************************************
             *  Check and Fix head of household missing issue
             **************************************************/
            echo'<h2>'.esc_html( __('Checking for issues with head of household not set ','church-admin' ) ).'</h2>'."\r\n";
            foreach( $households AS $household)
            {
                $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$household->household_id.'"');
                
                if ( empty( $people_id) )$householdsNeedingFixing[]=$household->household_id;
            }
            if ( empty( $householdsNeedingFixing) )
            {
                echo'<p>'.esc_html( __('All households have a head of household set','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>'."\r\n";
            }
            else
            {
                
                $countHouseholds=count( $householdsNeedingFixing);
                echo'<p>'.esc_html(sprintf(__('%1$s households do not have a head of household set. Fixing now','church-admin' ) ,$countHouseholds)).'</p>'."\r\n";
                foreach( $householdsNeedingFixing AS $key=>$household_id)
                {
                    $peopleInHousehold=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY people_order ASC, people_type_id ASC, sex DESC');
                    
                //church_admin_debug(print_r( $peopleInHousehold,TRUE) );
                    if(!empty( $peopleInHousehold) )
                    {
                        //church_admin_debug( $peopleInHousehold);
                        if( $wpdb->num_rows==1)
                        {
                            //one person household
                            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE household_id="'.(int)$household_id.'" ');
                        
                            echo'<p>'.esc_html( __('One person household head of household fixed','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>'."\r\n";
                        }
                        else
                        {
                            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE people_id="'.(int)$peopleInHousehold[0]->people_id.'" ');
                            echo'<p>'.esc_html( __('Multiple person household head of household fixed','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>'."\r\n";
                        }
                    }
                }
            }
        }
        /******************************************************************
         * Geocoder issues
         ******************************************************************/
        echo'<h2>'.esc_html( __('Google Maps API and address geocoding ','church-admin' ) ).'</h2>';
        $api=get_option('church_admin_google_api_key');
        if( $api)
        {
            $geocodeRequiredCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_household WHERE address!=", , , ," AND address!=", , ," AND address!="" AND (geocoded=0 OR lat="" OR lng="")');
            if(!empty( $geocodeRequiredCount) )
            {
                $updateButton='<a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=bulk-geocode&section=people','bulk-geocode').'">'.esc_html( __('Update mapping now','church-admin' ) ).'</a>'."\r\n";
				echo'<h2>'.esc_html(sprintf(_n('%s household needs its address geocoding','%s households need their addresses geocoding',$geocodeRequiredCount,'church-admin' ) ,$geocodeRequiredCount)).'</h2><p>'.$updateButton.'</p>'."\r\n";
            }else{
                echo'<p>'.esc_html( __('All addresses already geocoded','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
            }
        }else{
            echo'<p>'.esc_html( __('No Google maps API key set yet','church-admin' ) ).'</p>';
            echo'<p><a href="https://www.churchadminplugin.com/tutorials/google-api-key/">Tutorial</a></p>';
        }
         /******************************************************************
         * e164 mobile
         ******************************************************************/
        echo'<h2>'.esc_html( __('Checking all mobiles also have e164 version saved ','church-admin' ) ).'</h2>';
        $noe164=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE (mobile IS NOT NULL AND mobile!="") AND (e164cell = "" OR e164cell IS NULL)');
        $ISO=get_option('church_admin_sms_iso');
        if(!empty($noe164) && !empty( $ISO ) )
        {
            $x=0;
            foreach($noe164 AS $row)
            {
                $e164 = church_admin_e164($row->mobile);
                $wpdb->query( 'UPDATE '.$wpdb->prefix.'church_admin_people SET e164cell= "'.esc_sql($e164).'" WHERE people_id="'.(int)$row->people_id.'"');
                $x++;
            }
            echo '<p>'.esc_html( sprintf(__('%1$s mobiles also saved in e164 format', 'church-admin' ), (int)$x ) ).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
        }else{
            echo '<p>'.esc_html(__('All mobile phone numbers already also saved in e164 format for SMS','church-admin')).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
        }
        /******************************************************************
         * Fix bad e164 mobile
         ******************************************************************/
        echo'<h2>'.esc_html( __('Fixing for badly formed e164','church-admin' ) ).'</h2>';
        $country=get_option('church_admin_sms_iso');
        $bad = '+'.$country.'+'.$country;
        $good = '+'.$country;
        $count =$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET e164cell = REPLACE (e164cell, "'.esc_sql($bad).'", "'.esc_sql($good).'")');
        
        echo'<p>'.esc_html(sprintf(__('%1$s badly formed e164 fixed', 'church-admin' ), (int)$count ) ).'</p>';

         /******************************************************************
         * Check if missing from any group including unattached
         ******************************************************************/
        echo'<h2>'.esc_html( __('Checking all people are in small group or unattached.','church-admin' ) ).'</h2>';
         $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id NOT IN (SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup");');
        if($people){
            $count=$wpdb->num_rows;
            $values=array();
            foreach($people AS $row){
                $values[]='("1","'.(int)$row->people_id.'","smallgroup","'.esc_sql(wp_date('Y-m-d')).'")';
            }
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (ID,people_id,meta_type,meta_date) VALUES '.implode(",",$values));
           
            echo'<p>'.esc_html(sprintf(__('%1$s people who were in no small group, have been marked as unattached.','church-admin'  ),$count)).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
        }
        else
        {
            echo '<p>'.esc_html( __('Everyone is in a small group or unattached','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
        }
        /******************************************************************
         * Remove meta table duplicates
         ******************************************************************/
        echo'<h2>'.esc_html( __('Remove people duplicates in smallgroups, ministries and classes','church-admin' ) ).'</h2>';
        $sql='DELETE  t1 FROM '.$wpdb->prefix.'church_admin_people_meta t1 INNER JOIN '.$wpdb->prefix.'church_admin_people_meta t2 WHERE t1.meta_id < t2.meta_id AND t1.people_id = t2.people_id AND t1.ID = t2.ID AND t1.meta_type=t2.meta_type;';
        $wpdb->query($sql);
        echo '<p>'.esc_html( sprintf( __('%1$s duplicates removed','church-admin' ) ,$wpdb->rows_affected)).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
        echo'<h2>'.esc_html( __('Remove orphaned entries  in smallgroups, ministries and classes','church-admin' ) ).'</h2>';
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id REGEXP "^-?[0-9]+$" AND people_id NOT IN (SELECT people_id FROM '.$wpdb->prefix.'church_admin_people);');
        echo '<p>'.esc_html( sprintf( __('%1$s orphan entries removed','church-admin' ) ,$wpdb->rows_affected)).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';

        /******************************************************************
         * GDPR ISSUES
         ******************************************************************/
        echo'<h2>'.esc_html( __('Checking some people set to show on address list and have set a data protection reason ','church-admin' ) ).'</h2>';
        $countShowMe=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people WHERE show_me=1 AND gdpr_reason!=""');
        if ( empty( $countShowMe) )
        {
            echo'<p>'.esc_html( __('Nobody has set "show me" on the address list','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-no" style="color:red"></span></p>';
        }
        else echo'<p>'.esc_html(sprintf(__('%1$s people have set themselves to show me on the address list','church-admin' ) ,$countShowMe)).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>';
        /******************************************************************
         * SHOW POSSIBLE DUPLICATES
         ******************************************************************/ 
        echo'<h2>'.esc_html( __('Checking for duplicate entries','church-admin' ) ).'</h2>';
        $sql='SELECT first_name, COUNT(first_name) AS first_name_count,last_name, COUNT(last_name) AS last_name_count FROM '.$wpdb->prefix.'church_admin_people GROUP BY first_name,last_name HAVING COUNT(first_name)>1 AND COUNT(last_name)>1';
        $results=$wpdb->get_results( $sql);
       
        if(!empty( $results) )
        {
            foreach( $results AS $row)
            {
                echo '<h3>'.esc_html(sprintf(__('Possible duplication of %1$s, which occurs %2$s times','church-admin' ) ,esc_html( $row->first_name.' '.$row->last_name),(int)$row->last_name_count)).'</h3>';
                //grab those details
                $duplicateResult=$wpdb->get_results('SELECT a.*,b.address,b.last_updated AS householdUpdated FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.first_name="'.esc_sql( $row->first_name).'" AND last_name="'.esc_sql( $row->last_name).'" ORDER BY people_id ASC');
                $dupeNo=1;
                if(!empty( $duplicateResult) )
                {
                    
                    $theader='<tr><th>'.esc_html( __('Merge','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Cell','church-admin' ) ).'</th><th>'.esc_html( __('Address','church-admin' ) ).'</th><th>'.esc_html( __('Display household','church-admin' ) ).'</th><th>'.esc_html( __('Last Updated','church-admin' ) ).'</th></tr>';
                    echo'<table class="widefat striped bordered"><thead>'.$theader.'</thead><tbody>';
                    foreach( $duplicateResult AS $dupe)
                    {
                        if( $dupeNo==1)
                        { 
                            $merge=__('Original','church-admin');
                            $firstPeopleID=$dupe->people_id;
                        }
                        else
                        {
                            $merge='<a target="_blank" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=merge-people&amp;people_id1='.(int)$firstPeopleID.'&people_id2='.$dupe->people_id,'merge-people').'">'.esc_html( __('Merge','church-admin' ) ).'</a>';
                        }
                        $delete='<a target="_blank" onclick="return confirm(\'Are you sure you want to delete '.esc_html( $dupe->first_name).' '.esc_html( $dupe->last_name).'?\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_people&amp;people_id='.$dupe->people_id,'delete_people').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
                        $name=church_admin_formatted_name( $dupe);
                        if(!empty( $dupe->cell) )  {$cell=esc_html( $dupe->cell);}else{$cell='&nbsp;';}
                        if(!empty( $dupe->address) )  {$address=esc_html( $dupe->address);}else{$address='&nbsp;';}
                        if(!empty( $row->updated_by) )
                        {
                            $updatedBy=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$dupe->updated_by.'"');
                        }
                        $updated=sprintf(__('Person updated %1$s, Household updated %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$dupe->last_updated),mysql2date(get_option('date_format'),$dupe->householdUpdated) );
                        $householdCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$dupe->household_id.'"');
                        $display='<a target="_blank" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display-household&section=people&household_id='.(int)$dupe->household_id,'display-household').'">'.esc_html(sprintf(__('Display household (%1$s people)','church-admin' ),$householdCount)).'</a>';
                        echo'<tr><td>'.$merge.'</td><td>'.$delete.'</td><td>'.$name.'</td><td>'.$cell.'</td><td>'.$address.'</td><td>'.$display.'</td><td>'.$updated.'</td></tr>';
                        $dupeNo++;
                    }
                    echo'</tbody><tfoot>'.$theader.'</tfoot></table>';
                }
            }
        }
        else
        {
            echo'<p>'.esc_html( __("No duplicates found",'church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>'."\r\n";
        }
        /******************************************************************
         * SHOW HOUSEHOLDS WHERE LAST NAME IS DIFFERENT
         ******************************************************************/ 
        echo'<h2>'.esc_html( __('Checking for households with different last names','church-admin' ) ).'</h2>'."\r\n";
        echo'<p>'.esc_html( __('This may show households that have got joined by mistake','church-admin' ) ).'</p>'."\r\n";
        $households=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_household');
        if(!empty( $households) )
        {
            $count=0;
            $diffLastNameDisplay='';
            foreach( $households AS $household)
            {
                $check=$wpdb->get_results('SELECT DISTINCT last_name FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household->household_id.'" AND ignore_last_name_check=0');
                
                if(!empty( $check)&$wpdb->num_rows>1)
                {
                    $lName=array();
                    foreach( $check AS $diffS)
                    {
                        $lName[]=$diffS->last_name;
                    }
                    $count++;
                    $diffLastNameDisplay.='<p id="h'.(int)$household->household_id.'"> <a target="_blank" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display-household&section=people&household_id='.(int)$household->household_id,'display-household').'">'.$count.') '.esc_html(sprintf(__('Please check this household with last names - %1$s','church-admin' ) ,implode(", ",$lName) )).'</a><button class="ignore-last-name button-secondary" data-household-id="'.(int)$household->household_id.'">'.esc_html( __('Ignore different last names in future','church-admin' ) ).'</button></p>'."\r\n";
                    
                }
            }
            if(!empty( $diffLastNameDisplay) )
            {
                echo '<p><strong>'.esc_html(sprintf(__('%1$s households need checking','church-admin'  ),$count)).'</strong></p>';
                echo $diffLastNameDisplay;
            }else{echo'<p>'.esc_html( __('No households need checking','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>';}
        }
        $nonce = wp_create_nonce("ignore-last-name");
        echo '<script>
        
        jQuery(function( $)  {
            $(".ignore-last-name").click(function(e)
            {
                console.log("Ignore clicked");
                e.preventDefault();
                var household_id=$(this).data("household-id");
                var data = {
                    "action":  "church_admin",
                    "method": "ignore-last-name",
                    "household_id":household_id,
                    "nonce":"'.$nonce.'"
                };
                console.log(data);
                $.ajax({
                    url: ajaxurl,
                    type: "post",
                    data:data,
                    success: function( response ) {
                        console.log(response);
                        $("#h"+response).hide();
                    },
                });
            });
        });</script>';
}
function church_admin_get_custom_fields()
{
    global $wpdb;
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields ORDER BY custom_order');
    if ( empty( $results) ) return array();
    $out=array();
    foreach( $results AS $row)
    {
        $out[$row->ID]=(array)$row;
        $out[$row->ID]['sanitized-name']=sanitize_title($row->name);
    }    
    return $out;
}
function church_admin_address_list_issues_fixer( $member_type_ids)
{
    global $wpdb;
    $output='<div class="notice notice-danger"><h2>'.esc_html( __('Address list issues detect and fix')).'</h2>';

    //first check GDPR and show me!
    $output.='<h3>'.esc_html( __('Checking some people set to show on address list and have set a data protection reason ','church-admin' ) ).'</h3>';
    $countShowMe=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people WHERE show_me=1 AND gdpr_reason!=""');
    if ( empty( $countShowMe) )
    {
        $output.='<p>'.esc_html( __('Nobody has set "show me" on the address list','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-no" style="color:red"></span></p>';
    }
    else $output.='<p>'.esc_html(sprintf(__('%1$s people have set themselves to show_me','church-admin' ) ,$countShowMe)).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>';
    //second check and fix head of households
    $householdsNeedingFixing=array();
    $households=$wpdb->get_results('SELECT household_id FROM '.$wpdb->prefix.'church_admin_household');
    if(!empty( $households) )
    {
        /**************************************************
         *  Check and Fix head of household missing issue
         **************************************************/
        $output.='<h3>'.esc_html( __('Checking for issues with head of household not set ','church-admin' ) ).'</h3>';
        foreach( $households AS $household)
        {
            $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$household->household_id.'"');
            
            if ( empty( $people_id) )$householdsNeedingFixing[]=$household->household_id;
        }
        if ( empty( $householdsNeedingFixing) )
        {
            $output.='<p>'.esc_html( __('All households have a head of household set','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>';
        }
        else
        {
            
            $countHouseholds=count( $householdsNeedingFixing);
            $output.='<p>'.esc_html(sprintf(__('%1$s households do not have a head of household set. Fixing now','church-admin' ) ,$countHouseholds)).'</p>';
            foreach( $householdsNeedingFixing AS $key=>$household_id)
            {
                $peopleInHousehold=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY people_order ASC, people_type_id ASC, sex DESC');
                $output.=$wpdb->last_query;
                //church_admin_debug(print_r( $peopleInHousehold,TRUE) );
                if(!empty( $peopleInHousehold) )
                {
                    //church_admin_debug( $peopleInHousehold);
                    if( $wpdb->num_rows==1)
                    {
                        //one person household
                        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE household_id="'.(int)$household_id.'" ');
                       
                        $output.='<p>'.esc_html( __('One person household head of household fixed','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes"  style="color:green"></span></p>';
                    }
                    else
                    {
                        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE people_id="'.(int)$peopleInHousehold[0]->people_id.'" ');
                        $output.='<p>'.esc_html( __('Multiple person household head of household fixed','church-admin' ) ).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>';
                    }
                }
            }
        }
        /**************************************************
         *  Check member types
         **************************************************/
        $output.='<h3>'.esc_html( __('Check member type','church-admin' ) ).'</h3>';
       
        if ( empty( $member_type_ids) )
        {
            $output.='<p>'.esc_html( __('Directory set to show any member type','church-admin' ) ).'</p>';
        }
        else
        {
            $output.='<p>'.esc_html( __('Directory set to show certain member types, checking...','church-admin' ) ).'</p>';
            foreach( $member_type_ids AS $key=>$member_type_id)
            {
                $member_type_detail=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_member_types WHERE member_type_id="'.(int)$member_type_id.'"');
                if ( empty( $member_type_detail) )  {$output.='<p>'.esc_html(sprintf(__('Member Type  "%1$s" does not exist, so will not be found','church-admin' ) ),(int)$member_type_id).'</p>';}
                else
                {
                    $peopleCount=$wpdb->get_var('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.(int)$member_type_id.'"');
                    if(!empty( $peopleCount) )
                    {
                        $output.='<p>'.esc_html(sprintf(__('People found for member type "%1$s" ID  %2$s','church-admin' ) ,$member_type_detail->member_type,(int)$member_type_id)).'<span class="ca-dashicons dashicons dashicons-yes" style="color:green"></span></p>';
                    }
                    else
                    {
                        $output.='<p>'.esc_html(sprintf(__('No People found for member type "%1$s" ID  %2$s','church-admin' ) , $member_type_detail->member_type,(int)$member_type_id)).'<span class="ca-dashicons dashicons dashicons-no" style="color:red"></span></p>';
                    }
                }

            }
        }
    }
    return $output;
}


/*
function church_admin_plugin_notice() {
	
	global $current_user;
	
	$user_id = $current_user->ID;
	
	if (!get_user_meta( $user_id, 'church_admin_plugin_notice_ignore') ) {
		
		echo '<div class="updated notice notice-warning"><h2>'. __('Please note that service prebooking is now defaulted to login only','church-admin') .'</h2><p>'.esc_html( __('Add loggedin=FALSE to the shortcode or uncheck "Require login?" in the block options.','church-admin' ) ).'</p><p> <a href="'.add_query_arg( 'dismiss-ca-service-notice', 'true').'">Dismiss</a></p></div>';
		
	}
	
}
add_action('admin_notices', 'church_admin_plugin_notice');
	
function church_admin_plugin_notice_ignore() {
	
	global $current_user;
	
	$user_id = $current_user->ID;
	
	if (isset( $_GET['dismiss-ca-service-notice'] ) ) {
		
		add_user_meta( $user_id, 'church_admin_plugin_notice_ignore', 'true', true);
		
	}
	
}
add_action('admin_init', 'church_admin_plugin_notice_ignore');
*/
function church_admin_directory_title_name( $people)
{
    $directory=$first_names=$last_names=array();
    //$people should be the result of a people result for a household_id
    foreach( $people AS $row)
  		{
  			if( $row->head_of_household==1)$directory['last_name']=$row->last_name;
  			if( $row->people_type_id==1)
  			{
  				$first_names[]=$row->first_name;
				$last_names[]=implode(" ",array_filter(array( $row->prefix,$row->last_name) ));
				if(!empty( $row->nickname) )  {$nickname='('.$row->nickname.')';}else{$nickname="";}
  				$row->name=implode(" ",array_filter(array( $row->first_name,$row->middle_name,$nickname,$row->prefix,$row->last_name) ));
  				$adults[]=$row;
  			}
  			else
  			{
				if(!empty( $row->nickname) )  {$nickname='('.$row->nickname.')';}else{$nickname="";}
  				$row->name=implode(" ",array_filter(array( $row->first_name,$row->middle_name,$nickname,$row->prefix,$row->last_name) ));
  				$children[]=$row;
  			}
  		}

  		if(count( $last_names)==1)
  		{
  			$directory['directory_name']=$first_names[0].' '.$last_names[0];
  		}
  		elseif(count( $last_names) != count(array_unique( $last_names) ))
  		{//same last names
  			$directory['directory_name']=implode(" &amp; ",$first_names).' '.end( $last_names);
  		}
  		else
  		{//different last names
            $directory_names=array();
  			for ( $x=0; $x < count( $last_names ); $x++ ){
                if(!empty($last_names[$x])) {
                    $directory_names[] = $first_names[$x].' '.$last_names[$x];
                }
            }    
  			$directory['directory_name']=implode(" &amp; ",$directory_names);

  		}
        return $directory['directory_name'];
}
/************************
 * Manual Advert
 ************************/
function church_admin_manual_advert()
{
    global $church_admin_version;
    $licence = get_option('church_admin_app_new_licence');;
    if(!empty($licence) && $licence == 'premium'){return;}
    /**********************
     * Check Trial Period
     *********************/
    $trial_period = get_option('church_admin_trial');
    $trial_period_countdown = church_admin_trial_period();
    
    
    
    echo '<div class="ca-boxes" id="upgrade">';
    echo    '<div class="ca-boxes-header ca-blue">'."\r\n";
    echo '<p><span class="ca-dashicons dashicons dashicons-heart ca-dashicons"></span></p>'."\r\n";
    echo'<h3>'.esc_html( __('Support Church Admin Plugin','church-admin' ) ).'</h3>'."\r\n";
    echo'</div>'."\r\n";
    echo '<div class="ca-boxes-content">';
    echo'<h3>'.esc_html(__('Weekly Email List','church-admin')).'</h3>';
    echo'<p><a href="http://churchadminplugin.com/#email-list">Join are our weekly email list to get the free PDF manual</a></p>';
    
    $output='';
    $output.='<div class="notice notice-success"><h2>'.esc_html(__('If your licence seems wrong.','church-admin')).'</h2>';
    $output.='<p>'.__('Sometimes the licence check is delayed, please click the button below to check again.','church-admin').'</p>';
    $output.='<p><a href="'.site_url().'/?licence-change=reset">'.__('Check again','church-admin').'</a></p>';
    $output.='</div>';

        
    if(!empty($trial_period) && $licence!='premium'){
        //28 day trial period of standard version
        $output .= '<h2>'.esc_html(__('Trial Period of Standard Version','church-admin')).'</h2>';
        $output .='<p style="color:red"><strong>'.$trial_period_countdown.'</strong></p>';
    }
    if($licence =='free' || !empty($trial_period)){
        $output .= '<h3>'.esc_html(__('Free Version','church-admin')).'</h3>';
        $output .= '<p>'.esc_html(__('Only includes the People module and front end address list blocks and shortcodes','church-admin')).'</p>';
    }
    if($licence == 'basic'){
        $output .= '<h3>'.esc_html(__('Basic Version','church-admin')).'</h3>';
        $output .= '<p>'.esc_html(__('Only includes the People, Calendar and Sermons modules','church-admin')).'</p>';
    }
    if(!empty($trial_period)||$licence=='free'){

        $output.='<h3>'.esc_html(__('Basic Version','church-admin')).'</h3>';
        $output .= '<p>'.esc_html(__('Only includes the People, Calendar and Sermons modules','church-admin')).'</p>';
        $output.='<form action="'.CA_PAYPAL.'" method="post"><input name="cmd" type="hidden" value="_xclick-subscriptions"> 
            <input name="item_name" type="hidden" value="Church Admin Basic Version Subscription from v'.esc_attr($church_admin_version).'"> 
            
            <input type="hidden" name="rm" value=2/><input name="notify_url" type="hidden" value="https://www.churchadminplugin.com/wp-admin/admin-ajax.php?action=church_admin_basic_ipn"><div class="church-admin-form-group"><label>Your website URL</label><input  type="hidden" name="custom" value="'.site_url().'" > </div><p><input name="business" type="hidden" value="support@churchadminplugin.com"><input type="hidden" name="a3" class="basic-price" value="12.50"><input type="hidden" class="ca-recurring"  name="p3" value="1" /><input type="hidden" class="ca-recurring" name="t3" value="Y" /><input type="hidden" class="ca-recurring" name="src" value="1" /><input type="hidden" name="no_note" value=1><div class="form-group"><select class="basic-currency_code" name="currency_code"><option value="USD">US Dollar $12.50 annually</option><option value="GBP">GB Pound Sterling £10 annually</option><option value="EUR">Euro €12.50 annually</option><option value="AUD">Australian Dollar $19 annually</option><option value="BRL">Brazilian Real 62 annually</option><option value="CAD">Canadian Dollar $17 annually</option><option value="MXN">Mexican Peso 215 annually</option> <option value="CHF">Swiss Franc 11 annually</option></select></div><button class="button-secondary" type="submit">Instant upgrade to Basic</button></form></p><script>
                   jQuery( document ).ready(function($) {
                       console.log( "ready!" );
                   
                       $(".standard-currency_code").change(sortPrice);
                       $(".standard-frequency").change(sortPrice);
                       
                       function sortPrice(){
                           var currency_code=$(".basic-currency_code").val();
                           var frequency=$(".basic-frequency").val();
                           console.log("Currency "+ currency_code+ "Frequency "+frequency);
                           var price=30;
                           
                           var sign="&pound;";
                           console.log(currency_code)
                           switch(currency_code)
                           {
                               default:case "GBP":price=10;sign="GBP &pound;10";break;	
                               case "AUD":price=19;sign="AUD &dollar;19";break;
                               case "MXN":price=215;sign="MXN Peso 2120";break;
                               case "BRL":price=62;sign="BRL Real 62";break;
                               case "CAD":price=17;sign="CAD &dollar;17";break;
                               case "USD":price=12.50;sign="USD &dollar;12.50";break;
                               case"EUR":price=12.50;sign="EU &euro;12.50";break;
                               case "CHF":price=11;sign="CHF11";break;
                               
                           }
                           
                           $(".basic-sign").html(sign);
                           var formattedPrice =parseFloat(Math.round(price * 100) / 100).toFixed(2);
                           $(".basic-cost").html(formattedPrice);
                           $(".basic-price").val(formattedPrice);
                           $(".basic-freq").html(freq);
                           
                       };
                       
                   });</script>
            <p><a href="https://buy.stripe.com/8wM29Tg2P7ABcjm14I">Or Pay by card (upgrade is not instant, will be handled manually)</a></p>';
    }
    if(empty($licence)||$licence=='free'||$licence=='basic'){
        $output .= '<h3>'.esc_html(__('Standard Version','church-admin')).'</h3>';
        $output .= '<p>'.esc_html(__('Includes the following modules: people, age related groups, attendance, calendar, contact form, classes, free events,  follow up funnels,key dates, sermon media and schedule ','church-admin')).'</p>';
   

        $output.='<p><form action="'.CA_PAYPAL.'" method="post"><input name="cmd" type="hidden" value="_xclick-subscriptions"> 
        <input name="item_name" type="hidden" value="Church Admin Standard Version Subscription from v'.esc_attr($church_admin_version).'">
        <input type="hidden" name="return" value="'.site_url().'/?licence-change=reset"/>
        <input type="hidden" name="rm" value=2/><input name="notify_url" type="hidden" value="https://www.churchadminplugin.com/wp-admin/admin-ajax.php?action=church_admin_standard_ipn">
            <input type="hidden" name="custom" value="'.site_url().'"> 
            <input name="business" type="hidden" value="support@churchadminplugin.com"> 
            <input type="hidden" name="a3" class="standard-price" value="40">
       <input type="hidden" class="ca-recurring"  name="p3" value="1" />
       <input type="hidden" class="ca-recurring" name="t3" value="Y" />
       <input type="hidden" class="ca-recurring" name="src" value="1" />
       <input type="hidden" name="no_note" value=1>
       <div class="form-group"><select class="standard-currency_code" name="currency_code"><option value="USD">US Dollar $40 annually</option><option value="GBP">GB Pound Sterling £30 annually</option><option value="EUR">Euro €35 annually</option><option value="AUD">Australian Dollar $60 annually</option><option value="BRL">Brazilian Real 185 annually</option><option value="CAD">Canadian Dollar $170 annually</option><option value="MXN">Mexican Peso 2120 annually</option> <option value="CHF">Swiss Franc 33 annually</option></select></div><input class="button-primary" type="submit" value="Instant upgrade to Standard"></form></p><script>
               jQuery( document ).ready(function($) {
                   console.log( "ready!" );
               
                   $(".standard-currency_code").change(sortPrice);
                   $(".standard-frequency").change(sortPrice);
                   
                   function sortPrice(){
                       var currency_code=$(".standard-currency_code").val();
                       var frequency=$(".standard-frequency").val();
                       console.log("Currency "+ currency_code+ "Frequency "+frequency);
                       var price=30;
                       
                       var sign="&pound;";
                       console.log(currency_code)
                       switch(currency_code)
                       {
                           default:case "GBP":price=30;sign="GBP &pound;30";break;	
                           case "AUD":price=60;sign="AUD &dollar;60";break;
                           case "MXN":price=2120;sign="MXN Peso 2120";break;
                           case "BRL":price=185;sign="BRL Real 185";break;
                           case "CAD":price=170;sign="CAD &dollar;170";break;
                           case "USD":price=40;sign="USD &dollar;40";break;
                           case"EUR":price=35;sign="EU &euro;35";break;
                           case "CHF":price=33;sign="CHF33";break;
                           
                       }
                       
                       $(".standard-sign").html(sign);
                       var formattedPrice =parseFloat(Math.round(price * 100) / 100).toFixed(2);
                       $(".standard-cost").html(formattedPrice);
                       $(".standard-price").val(formattedPrice);
                       $(".standard-freq").html(freq);
                       
                   };
                   
               });</script>';
               $output.='<p><a href="https://buy.stripe.com/dR615P7wjbQR5UYaEV">Or pay by card  (upgrade is not instant, will be handled manually)</a></p>';
    }
    

    $output .= '<h3>'.esc_html(__('Premium Version','church-admin')).'</h3>';
    $output .= '<p>'.esc_html(__('Includes standard version features and automations, paid tickets for events, giving forms for PayPal and Stripe, the pastoral visitation module,  non availability for serving on schedules and most importantly the app','church-admin')).'</p>';

    $output.='<p><form action="'.CA_PAYPAL.'" method="post">
    <input name="cmd" type="hidden" value="_xclick-subscriptions"> 
    <input name="item_name" type="hidden" value="Church Admin Premium Version from v'.esc_attr($church_admin_version).'"
    <input type="hidden" name="return" value="'.site_url().'/?licence-change=reset"/>
    <input type="hidden" name="rm" value=2/>
    <input name="notify_url" type="hidden" value="https://www.churchadminplugin.com/wp-admin/admin-ajax.php?action=church_admin_premium_ipn"> 
        <input type="hidden" name="custom" value="'.site_url().'">
        <input name="business" type="hidden" value="support@churchadminplugin.com"> 
        <input type="hidden" name="a3" class="premium-price" value="99">
       <input type="hidden" class="ca-recurring"  name="p3" value="1" /><input type="hidden" class="ca-recurring" name="t3" value="Y" /><input type="hidden" class="ca-recurring" name="src" value="1" /><input type="hidden" name="no_note" value=1>
       <div class="form-group"><select class="premium-currency_code" name="currency_code"><option value="USD">US Dollar $129 annually</option><option value="GBP">GB Pound Sterling £99 annually</option><option value="EUR">Euro €110 annually</option><option value="AUD">Australian Dollar $190 annually</option><option value="BRL">Brazilian Real 500 annually</option><option value="CAD">Canadian Dollar $170 annually</option><option value="MXN">Mexican Peso 2120 annually</option> <option value="CHF">Swiss Franc 110 annually</option></select></div><input class="button-primary" type="submit" value="Instant Upgrade to Premium"></form></p><script>
               jQuery( document ).ready(function($) {
                   console.log( "ready!" );
              
                   $(".premium-currency_code").change(sortPrice);
                   $(".premium-frequency").change(sortPrice);
                   
                   function sortPrice(){
                       var currency_code=$(".premium-currency_code").val();
                       var frequency=$(".premium-frequency").val();
                       console.log("Currency "+ currency_code+ "Frequency "+frequency);
                       var price=99;
                       
                       var sign="&pound;";
                       console.log(currency_code)
                       switch(currency_code)
                       {
                           default:case "GBP":price=99;sign="GBP &pound;99";break;	
                           case "AUD":price=190;sign="AUD &dollar;190";break;
                           case "MXN":price=2120;sign="MXN Peso 2120";break;
                           case "BRL":price=600;sign="BRL Real 600";break;
                           case "CAD":price=170;sign="CAD &dollar;170";break;
                           case "USD":price=129;sign="USD &dollar;129";break;
                           case"EUR":price=110;sign="EU &euro;110";break;
                           case "CHF":price=110;sign="CHF110";break;
                           
                       }
                       
                       $(".premium-sign").html(sign);
                       var formattedPrice =parseFloat(Math.round(price * 100) / 100).toFixed(2);
                       $(".premium-cost").html(formattedPrice);
                       $(".premium-price").val(formattedPrice);
                       $(".premiumfreq").html(freq);
                       
                   };
                   
               });</script>';
    
    $output.='<p><a href="https://buy.stripe.com/6oE7ud03R1cd3MQ00i" >Or pay by card  (upgrade is not instant, will be handled manually)</a></p>';
    echo $output;
               
    echo'</div></div>';
}
/************************
 * GDPR Check
 ************************/
function church_admin_new_look_gdpr()
{
    if(!church_admin_level_check('Directory')){return;}
    echo '<div class="ca-boxes" id="new-look-gdpr">';
    echo    '<div class="ca-boxes-header ca-red">'."\r\n";
    echo '<p><span class="ca-dashicons dashicons dashicons-shield ca-dashicons"></span></p>'."\r\n";
    echo'<h3>'.esc_html( __('Data Protection','church-admin' ) ).'</h3>'."\r\n";
    echo'</div>'."\r\n";
    echo '<div class="ca-boxes-content">';
    church_admin_detect_runtime_issues();
    church_admin_gdpr_check();
    church_admin_sort_wedding_anniversary();
    echo'</div></div>';
}



function church_admin_gdpr_check()
{
    global $wpdb;
     /*************************************
    *
    *   data protection section
   	*
    *************************************/
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=check-directory-issues','check-directory-issues').'">'.esc_html( __('Check for directory issues','church-admin' ) ).'</a></p>';
    $sql=' SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND  email!=""  AND (gdpr_reason IS NULL OR gdpr_reason="") ';
    $noncompliant=$wpdb->get_var( $sql);
    if( $noncompliant>0)
    {
            
   			echo '<h2 class="gdpr-minimise">'.esc_html(sprintf(__('Data Protection %1$s entry not confirmed','church-admin' ) ,$noncompliant)).'</h2>';
   			echo '<div id="data-protection" ><p>'.esc_html( __("UK & EU churches must comply with the General Data Protection regulations from 25th May 2018. They include making sure people are aware of what personal data you store for them and that you have obtained their permission to email, sms or mail them. Common sense, stuff, so I'm making the requirement to confirm permission mandatory from that date to send email and sms. You can obtain verbal permission and edit entries or send an email to everyone with a confirmation link.",'church-admin'));
   			echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=gdpr-email-test&amp;section=people','gdpr-email-test').'">'.esc_html( __("Send GDPR test email to yourself",'church-admin' ) ).'</a>&nbsp;';
   			echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=gdpr-email&amp;section=people','gdpr-email').'" onclick="return confirm(\'Are you sure?\')">'.esc_html( __("Send GDPR email to everyone who isn't confirmed already",'church-admin' ) ).'</a></p>';
   			echo'<p><a class="button-primary" href="'.site_url('?ca_download=gdpr-pdf').'">'.esc_html( __("Print GDPR forms for everyone who isn't confirmed already",'church-admin' ) ).'</a></p>';

   			echo '<p>'.esc_html(sprintf(__(' %1$s people (with email addresses) have not confirmed','church-admin' ) ,(int) $noncompliant) ).'</p>';
			church_admin_not_confirmed_gdpr();
   			echo'<p><strong>'.esc_html( __('This notice will contine to display until everyone has confimed','church-admin' ) ).'</strong></p>';
   			echo'<p>'.esc_html( __('This is bad practice and illegal in the EU from 25th May 2018....','church-admin' ) ).'<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=gdpr-bulk-confirm&amp;section=people','gdpr-bulk-confirm').'">'.esc_html( __("Confirm everyone",'church-admin' ) ).'</a></p>';
   			echo'</div>';
	}else{echo'<p>'.esc_html( __('All directory entries have confirmed for data protection','church-admin' ) ).'</p>';}
    //end of data protection section


}
function church_admin_not_confirmed_gdpr()
{
	global $wpdb;
	$result=$wpdb->get_results(' SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.last_name,a.people_id, a.email ,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND  email!=""  AND (gdpr_reason IS NULL OR gdpr_reason="")  GROUP BY email ');
	if(!empty( $result) )
	{
			echo'<h3>'.esc_html( __('These people have not responded to GDPR confirmation','church-admin' ) ).'</h3>';
			foreach( $result AS $row)
			{
				echo'<p>'.esc_html( $row->name).'</p>';
			}
	}
}

function church_admin_user_check( $person,$app=FALSE)
{
    global $wpdb;
    if( $app)  {$appClass=' button action ';}else{$appClass='';}
    //church_admin_debug(print_r( $person,TRUE) );
    if(!empty( $person->email) )
    {//user account only relevant for people with email
        if(!empty( $person->user_id) )
        {
            $user_info=get_userdata( $person->user_id);
            if(!empty( $user_info) )
            {
                $user=$user_info->user_login;
                //church_admin_debug("User id");
            }
            else
            {
                $user=esc_html(__('Invalid user ID stored','church-admin' ) ).'<br><span data-tab="create-user" class="ca_create_user userinfo'.(int)$person->people_id.' '.$appClass.'" data-peopleid="'.(int)$person->people_id.'" >'.esc_html( __('Create user account','church-admin' ) ).'</span>';
            }
                
        }
        else
        {
            //check if a user exists for this email
            $user_id=email_exists( $person->email);
            $unassigned_user=get_userdata( $user_id);
            if(!empty( $user_id) )
            {
                $user='<span class="ca_connect_user userinfo'.(int)$person->people_id.' '.$appClass.'" data-tab="connect-user-account" data-peopleid="'.(int)$person->people_id.'" data-userid="'.(int)$user_id.'">'.esc_html( __('Connect','church-admin' ) ).' '.$unassigned_user->user_login.'</span>';
                //church_admin_debug("Unassigned user");

            }
            else 
            {
                if(!empty( $person->gdpr_reason) )
                {
                    $user='<span  data-tab="create-user" class="ca_create_user userinfo'.(int)$person->people_id.' '.$appClass.'" data-peopleid="'.(int)$person->people_id.'" >'.esc_html( __('Create user account','church-admin' ) ).'</span>';
                    //church_admin_debug("GDPR reason");
                }
                else
                {    
                    $user=__("No GDPR reason,<br> so can't create user",'church-admin');
                    //church_admin_debug("No GDPR reason");
                }
            }
        }
    }
    else
    {
        $user=__("No email address,<br> so can't create user",'church-admin');
        //church_admin_debug("No email");
    }
    return $user;
}

function church_admin_disable_srcset( $sources ) {
    return false;
    }

function church_admin_update_user_meta( $people_id,$user_id)
{
    global $wpdb;
    if ( empty( $people_id) ) return;
    if ( empty( $user_id) ) return;
    $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'" AND user_id="'.(int)$user_id.'"');
    if ( empty( $data) )return;
    update_user_meta( $user_id,'first_name',$data->first_name);
    update_user_meta( $user_id,'last_name',$data->last_name);
    update_user_meta( $user_id,'display_name',$data->first_name.' '.$data->first_name);
    if(!empty( $data->bio) )update_user_meta( $user_id,'description',$data->bio);
}    

function church_admin_center_coordinates( $table)
{
    global $wpdb;
   
    $coords=new StdClass();
    
    /*
    $lng=$wpdb->get_var('SELECT AVG(t.lng) FROM '.$table.' AS t CROSS JOIN ( SELECT AVG(lng) avgr, STD(lng) stdr FROM '.$table.' ) AS stats WHERE t.lng BETWEEN (stats.avgr-stats.stdr) AND (stats.avgr+stats.stdr)');
    //church_admin_debug( $wpdb->last_query);
    $lat=$wpdb->get_var('SELECT AVG(t.lat) FROM '.$table.' AS t CROSS JOIN ( SELECT AVG(lat) avgr, STD(lat) stdr FROM '.$table.' ) AS stats WHERE t.lat BETWEEN (stats.avgr-stats.stdr) AND (stats.avgr+stats.stdr)');
    //church_admin_debug( $wpdb->last_query);
    */
    $lat=$wpdb->get_var('SELECT AVG(lat) FROM '.esc_sql($table).' WHERE lat!=0');
    $lng=$wpdb->get_var('SELECT AVG(lng) FROM '.esc_sql($table).' WHERE lng!=0');
    if(!empty( $lat)&&!empty( $lng) )
    {
        $coords->lat=floatval( $lat);
        $coords->lng=floatval( $lng);
    }
    else
    {
        $lat=$wpdb->get_var('SELECT AVG(lat) FROM '.$wpdb->prefix.'church_admin_sites WHERE lat!=0');
        $lng=$wpdb->get_var('SELECT AVG(lng) FROM '.$wpdb->prefix.'church_admin_sites WHERE lng!=0');
        if( !empty( $lat ) && !empty( $lng ) )
        {
            $coords->lat=floatval( $lat );
            $coords->lng=floatval( $lng );
        }
        else
        {
            $coords->lat=0.0;
            $coords->lng=0.0;
        }

    }
    //church_admin_debug("church_admin_coordinates function \r\n".print_r( $coords,TRUE) );
    return $coords;
 
}



function church_admin_prepare_post_for_email( $content,$type,$ID)
{
    global $church_admin_for_email;
    $church_admin_for_email=TRUE;
    //church_admin_debug(print_r(func_get_args(),TRUE) );
        $blocks=parse_blocks( $content);
        //church_admin_debug(print_r( $blocks,TRUE) );
        $content='';
        if(!empty( $blocks) )
        {
            foreach( $blocks AS $block)
            {

                //handle class shortcode
                

                //church_admin_debug("HANDLING A BLOCK");
                switch( $block['blockName'] )
                {
                    case 'core/buttons':
                        $button= $block['innerBlocks'][0]['innerHTML'];
                        $button = str_replace('<div class="wp-block-button">','<p>',$button);
                        $button = str_replace('</div>','</p>',$button);
                        $style = 'style="display: inline-block;padding: 10px 20px;background-color: #007bff;color: #fff;text-decoration: none;border-radius: 5px;font-weight: bold;transition: background-color 0.3s ease;"';
                        $button = str_replace('<a class=','<a '.$style.' class=',$button);
                        $content.=$button;
                    break;
                    case 'core/embed':
                        church_admin_debug("Handling core/embed");
                        church_admin_debug($block);
                        $link=$block['attrs']['url'];
                        //church_admin_debug("Link $link ");
                        switch( $block['attrs']['type'] )
                        {
                            case 'audio':
                            case 'rich':
                                $label=__('Play audio','church-admin');
                                $content.='<table border="0" style="margin-top:10px"  cellspacing="0" cellpadding="0"><tr><td align="center" style="border-radius: 3px;" bgcolor="#e9703e"><a href="'.esc_url( $link).'" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; text-decoration: none;border-radius: 3px; padding: 12px 18px; border: 1px solid #e9703e; display: inline-block;">'.$label.'</a></td></tr></table>';
                            break;
                            case 'video':
                                $label=__('Play video','church-admin');
                                $video=church_admin_generateVideoEmbedUrl( $block['attrs']['url']);
                                $content.='<div style="position:relative;width:480px;height:360px;background:#CCC url('.$video['image'].');"><a target="_blank" class="play-button" title="play video" href="'.esc_url($link).'" ><i class="gg-play-button-o"></i></a></div>';
                                church_admin_debug($video);



                               // $content.='<p><a target="_blank" title="play video" href="'.esc_url($link).'"><img src="'.esc_url($video['image']).'" alt="'.$label.'"></a></p>';
                            break;
                        }
                        
                    break;
                    case 'core/shortcode':
                    case 'wp:shortcode':
                        //church_admin_debug("Handling shortcode");
                        //church_admin_debug( $block);

                        $content.=do_shortcode( $block['innerHTML'] );
                    break;
                    default:
                        //church_admin_debug("Handling default block");
                        $content.=wpautop(implode("\r\n",$block['innerContent'] ) );
                    break;
                }
            }
        }else{ $content .= do_shortcode( $content);}
        if( $type=='bible-readings'&&!empty( $ID) )
        {
            $custom_content ='';
            $version=get_option('church_admin_bible_version');
            $passage=get_post_meta( $ID ,'bible-passage',TRUE);
            
            if(!empty( $debug) )//church_admin_debug('Passage:'.$passage);
            $content ='<div class="ca-bible-date" style="display: block;border: 1px solid #CF2022;text-align: center;color: #CF2022;text-decoration: none; font-size: 1.4em;padding: 10px;margin: 30px auto 40px;">'.get_the_date(get_option('date_format'),$ID).'</div>'.$content;
            if(!empty( $passage) )
            {
                $link='https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.urlencode( $version).'&interface=print';
                $custom_content .= '<table border="0" style="margin-top:10px"  cellspacing="0" cellpadding="0"><tr><td align="center" style="border-radius: 3px;" bgcolor="#e9703e"><a href="'.esc_url( $link).'" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; text-decoration: none;border-radius: 3px; padding: 12px 18px; border: 1px solid #e9703e; display: inline-block;">'.esc_html(sprintf(__('Read %1$s','church-admin' ) , $passage) ).'</a></td></tr></table>';
               
                $bibleCV=church_admin_bible_audio_link( $passage,$version);

                if(!empty( $bibleCV['url'] ) )
                {
                    $custom_content .= '<table border="0" style="margin-top:10px" cellspacing="0" cellpadding="0"><tr><td align="center" style="border-radius: 3px;" bgcolor="#e9703e"><a href="'.esc_url( $bibleCV['url'] ).'" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; text-decoration: none;border-radius: 3px; padding: 12px 18px; border: 1px solid #e9703e; display: inline-block;">'.esc_html(sprintf(__('Listen to  %1$s','church-admin' ) ,$bibleCV['linkText'] ) ).'</a></td></tr></table>';
                }
            }
            $content.=$custom_content;
            
        }
    return $content;
}

function church_admin_delete_household( $household_id)
{
    global $wpdb;
    //church_admin_debug('Attempting to delete household id '.$household_id);
    if ( empty( $household_id) )return FALSE;
    //delete from household table
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"');
    //church_admin_debug( $wpdb->last_query);
    //get people
    $persons=$names = $user_ids = array();
    $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'"');
    //church_admin_debug( $wpdb->last_query);
    if(!empty( $people) )
    {
        foreach( $people AS $person)
        {
            $name=church_admin_formatted_name($person);
            $names[]=$name;
            if(!empty($person->user_id)){ $user_ids[$name]= $person->user_id; }
            $persons[]=church_admin_formatted_name($person);
           
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$person->people_id.'"');
            //church_admin_debug( $wpdb->last_query);
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person->people_id.'"');
            //church_admin_debug( $wpdb->last_query);
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_app WHERE people_id="'.(int)$person->people_id.'"');
            //church_admin_debug( $wpdb->last_query);
        }

    }
    $admin_message= '<p>'.__('A household has been deleted','church-admin').'</p>';
    $admin_message.= '<p>'.implode(",",$names).'</p>';

    if(!empty($user_ids)){
        //associated user accounts, so add to admin message
        foreach($user_ids AS $name=>$ID){
            $admin_message.='<a href="'.get_edit_user_link($ID).'">'.sprintf(__('User account edit/delete %1$s','church-admin'),$name).'</a></p>';
        }
    }
    church_admin_email_send(get_option('church_admin_default_from_email'),__('Household deleted','church-admin'),$admin_message);
    echo'<div class="notice notice-success"><h2>'.esc_html( __('Household deleted','church-admin' ) ).'</h2>';
    echo'<p>'.implode('<br/>',$persons).'</p></div>';
    $member_type_id=!empty($_GET['member_type_id'])?(int)$_GET['member_type_id']:0;
    church_admin_address_list($member_type_id);

}

/**
 *
 * Create user
 *
 * @author  Andy Moyle
 * @param    $people_id,$household_id,$username
 * @return   html
 * @version  0.1
 *
 */
function church_admin_create_user( $people_id,$household_id,$username=NULL,$password=null)
{
    global $wpdb;
 
    church_admin_debug('**** church_admin_create_user *****');
    church_admin_debug(func_get_args());
    church_admin_debug('People ID: '.$people_id);
    church_admin_debug('Household ID: '.$household_id);
    if(empty($username)){
        $username = $wpdb->get_var('SELECT CONCAT(first_name,last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'" AND household_id="'.(int)$household_id.'"');
        if(empty($username)) return FALSE;
    }
    $username=strtolower($username);
    church_admin_debug('Username: '.$username);
	//$wpdb->show_errors;
    $out='';
    if(empty($people_id) || !church_admin_int_check($people_id))
    {
			$out='<p>'.esc_html( __('Nobody was specified to create a wordpress account','church-admin' ) ).'</p>';
            //church_admin_debug($out);
            return $out;
    }
   
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    church_admin_debug( $wpdb->last_query);

	if ( empty( $person) )
	{
        
	    $out='<p>'.esc_html(__("That people record doesn't exist",'church-admin')).'</p>';
        //church_admin_debug($out);
        return $out;
	}
        //person exits in plugin db
    church_admin_debug('Person email: '.$person->email);
    $user_id = email_exists($person->email);
    if(!empty($user_id)){
        church_admin_debug('User ID exists: '.$user_id);
    }
    if(!empty( $user_id) && $person->user_id==$user_id)
    {//wp user exists and is in plugin db
        $userDetails=get_user_by('id',$user_id);
        church_admin_update_user_meta( $people_id,$user_id);
        $out='<p>'.$userDetails->user_login.'  - '.esc_html( __('user already created','church-admin' ) ).'</p>';
        church_admin_debug('User exists already and is in directory');
        return $out;
    }
	
    if(!empty($user_id))
	{//wp user exists, update plugin
        church_admin_debug('Updating plugin section = User ID exists: '.$user_id);
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$user_id.'" WHERE people_id="'.(int)$people_id.'"');
        church_admin_debug('User exists, update plugin');
        church_admin_debug( $wpdb->last_query);
        $userDetails=get_user_by('id',$user_id);
        $out='<p>'.$userDetails->user_login.' '.esc_html( __('user updated','church-admin' ) ).'</p>';
        //church_admin_debug($out);
        return $out;
	}
	//no user account   
        //create unique username
        if ( empty( $username) ){
            $username_style = get_option('church_admin_username_style');
            switch($username_style){
                default:
                case 'firstnamelastname':
                    $username=strtolower(str_replace(' ','',$person->first_name).str_replace(' ','',$person->last_name) );
                break;
                case 'firstname.lastname':
                    $username=strtolower(str_replace(' ','',$person->first_name).'.'.str_replace(' ','',$person->last_name) );
                break;
                case'initiallastname':
                    $username = strtolower(substr(trim($person->first_name),0,1).str_replace(' ','',$person->last_name));
                break;
                case 'lastnamefirstname':
                    $username=strtolower(str_replace(' ','',$person->last_name).str_replace(' ','',$person->first_name) );
                break;
            }
        }
        $x='';
        while(username_exists( $username.$x ) )
        {
            $x+=1;
        }
        //church_admin_debug('Creating user with username: '.$username.$x);
        $random_password = !empty($password)? $password :  wp_generate_password( $length=12, $include_standard_special_chars=false );
        $new_user_id = wp_create_user( $username.$x, $random_password, $person->email );
        church_admin_debug('new user id');
        //church_admin_debug($new_user_id);
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$new_user_id.'" WHERE people_id="'.(int)$people_id.'"');
        church_admin_debug( $wpdb->last_query);
        church_admin_update_user_meta( $people_id,$user_id);
        $message=wpautop(get_option('church_admin_user_created_email'));
        

        if ( empty( $message) )
        {
            $message='<p>'.esc_html( __('The web team at','church-admin' ) ). ' <a href="[SITE_URL]">[SITE_URL]</a> '.esc_html( __('have just created a user login for you.','church-admin' ) ).'</p><p>'.esc_html( __('Your username is','church-admin' ) ).' <strong>[USERNAME]</strong></p><p>'.esc_html( __('Your password is','church-admin' ) ).' <strong>[PASSWORD]</strong></p><p>'.esc_html( __('We also have an app you can download for [ANDROID] and [IOS]','church-admin' ) ).' </p>';
            update_option('church_admin_user_created_email',$message);
        }
        $message=str_replace('[SITE_URL]',site_url(),$message);
        $message=str_replace('[USERNAME]',esc_html( $username.$x),$message);
        $message=str_replace('[PASSWORD]',$random_password,$message);
        //$page_id=church_admin_register_page_id();
         
        //if(!empty( $page_id) )$message=str_replace('[EDIT_PAGE]',get_page_link( $page_id),$message);
        $message=str_replace('[ANDROID]','<a href="http://www.tinyurl.com/androidChurchApp">Android</a>',$message);
        $message=str_replace('[IOS]','<a href="http://www.tinyurl.com/iOSChurchApp">iOS</a>',$message);
        $out.='<p>'.esc_html( __('User created with username','church-admin' ) ).' <strong>'.esc_html( $username.$x).'</strong>, '.esc_html( __('password','church-admin' ) ).': <strong>'.$random_password.'</strong> '.esc_html( __('and this message was queued to them','church-admin' ) ).'<br>'.esc_html( $message);
            
            add_filter('wp_mail_content_type','church_admin_email_type');
            $subject=get_option('church_admin_user_created_email_subject');

            if ( empty( $subject) )$subject='Login for '.site_url();
        
            $from_email = get_option('church_admin_default_from_email');
            $from_name = get_option('church_admin_default_from_name');
            if(church_admin_email_send($person->email,$subject,$message,$from_name,$from_email,null,null,null,TRUE)){
                $out.='<strong>'.esc_html( __('User creation email sent/queued successfully','church-admin' ) ).'</strong></p>';
            }
            else{
                $out.='<strong>'.esc_html( __('User creation email NOT sent/queued successfully','church-admin' ) ).'</strong></p>';
            }

	  


        //church_admin_debug($out);
	


    
    church_admin_debug('**** END church_admin_create_user *****');
    return $out;
}//function church_admin_create_user


/**
 *
 * Delete People
 *
 * @author  Andy Moyle
 * @param    $people_id,$household_id
 * @return   html
 * @version  0.1
 *
 */
function church_admin_delete_people( $people_id,$household_id,$echo=TRUE,$confirm_required=TRUE)
{
    
    //if(!church_admin_level_check('Directory') )wp_die(__('You don\'t have permissions to do that','church-admin') );
    global $wpdb;
    
    $user_id=get_current_user_id();
    $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'" ');
    if ( empty( $data) )  {
        echo '<h2>'.esc_html(sprintf(__('No person with ID %1$s exists on the system','church-admin' ) ,(int)$people_id)).'</h2>';
        return;
    }
    $name= church_admin_formatted_name($data);
    $admin_message= '<p>'.sprintf(__('A person "%1$s" has been deleted','church-admin'),$name).'</p>';
    if(!empty( $confirm_required) && empty( $_POST['delete_confirm'] ) )
    {
        echo '<h2>'.esc_html(sprintf(__('Confirm deletion of %1$s','church-admin' ) ,church_admin_formatted_name($data) ) ).'</h2>';   
        echo'<form action="" method="post">';
        echo'<table class="form-table">';
        echo'<tr><td colspan=2><input type="hidden" name="delete_confirm" value="'.(int)$people_id.'" /><input class="button-primary" type="submit" value="'.esc_html( __('Confim deletion','church-admin' ) ).'" /></td></tr></table>';
        return;
    }
   
    delete_option('church-admin-directory-output');//get rid of cached directory, so it is updated
    //deletes person with specified people_id
    
    $message='';
    
    if(!empty($data->user_id))
    {
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_app WHERE user_id="'.(int)$data->user_id.'"');

       
        $admin_message.='<a href="'.get_edit_user_link($data->user_id).'">'.sprintf(__('User account edit/delete %1$s','church-admin'),$name).'</a></p>';
       
    }
    
        
        
    
    if(!empty( $data->head_of_household) )
    {//need to reassign head of household
        $message.=  esc_html(sprintf(__( '%1$s was head of household','church-admin' ) ,$data->first_name.' '.$data->last_name) ).'<br>';
        //look for another adult
        $next_person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" AND people_type_id=1 AND people_id!="'.(int)$people_id.'" LIMIT 1');
        if(!empty( $next_person) )$message.=sprintf( esc_html__( 'Head of household reassigned to %1$s','church-admin' ) ,$next_person->first_name.' '.$next_person->last_name).'<br>';
        //no adult, find someone!
        if ( empty( $next_person->people_id) ){
            $next_person=$wpdb->get_row('SELECT * from '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'"  AND people_id!="'.(int)$people_id.'" AND people_type_id=1 LIMIT 1');
        }
        if(!empty( $next_person) ){
            $message.=esc_html(sprintf(__( 'Head of household reassigned to %1$s','church-admin' ) ,$next_person->first_name.' '.$next_person->last_name) ).'<br>';}
        else{
            $message='';
        }
        //set new head of hosuehold
        if(!empty( $next_person->people_id) )
        {
            $sql='UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE people_id="'.(int)$next_person->people_id.'"';
            $wpdb->query( $sql);
        }
    }
    //Delete from people table
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'" ');
    //Delete from custom fields table.
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE people_id="'.(int)$people_id.'" ');

    $message.= esc_html(sprintf(__( '%1$s has been deleted','church-admin' ),$data->first_name.' '.$data->last_name)).'<br>';
    $count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id ="'.(int)$household_id.'" ');
    if ( empty( $count) )
    {
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ');
        $message= esc_html(__('Household Deleted','church-admin' ) ).'<br>';
    }
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'"');
  
    if(!empty( $echo) )
    {
        echo'<div class="notice notice-success inline"><p><strong>'.$message.'</strong></td></tr></div>';

        if(!empty( $count) )  {church_admin_new_household_display( $household_id);}else{church_admin_people_main();}
    }

    church_admin_email_send(get_option('church_admin_default_from_email'),__('Person deleted','church-admin'),$admin_message);

}
function church_admin_what_three_words( $data,$table)
{
    
    global $wpdb;
    $out='';
    if ( empty( $data) ) return;
    if(!is_array( $data) )$data=(array)$data;
    $w3w=get_option('church_admin_what_three_words');
    if ( empty( $w3w)|| $w3w=='off') return;
	if(!empty( $data['what-three-words'] ) )
	{
		//output what three words
		$out='<p><a title="'.esc_html( __('What Three Words precise location','church-admin' ) ).'" target="_blank" href="'.esc_url('https://w3w.co/'.$data['what-three-words'] ).'"><span style="color:red">///</span>'.esc_html( $data['what-three-words'] ).'</a></p>';
	}
	else
	{
       

		//lookup
        if ( empty( $data['lat'] )||empty( $data['lng'] ) )return;

        $url="https://api.what3words.com/v3/convert-to-3wa?key=7F5FVM60&coordinates=".$data['lat'].",".$data['lng']."&language=en&format=json";
		
        
        $response = wp_remote_get( esc_url_raw( $url ) );
		$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
       
        if(!empty( $api_response['words'] ) )
        {
            switch( $table)
            {
                case $wpdb->prefix.'church_admin_household':
                    $wpdb->query('UPDATE '.$table.' SET what_three_words="'.esc_sql( $api_response['words'] ).'" WHERE household_id="'.(int)$data['household_id'].'"');
                break;
                case $wpdb->prefix.'church_admin_sites':
                    $wpdb->query('UPDATE '.$table.' SET what_three_words="'.esc_sql( $api_response['words'] ).'" WHERE site_id="'.(int)$data['household_id'].'"');
                break;
            }
           
            $out='<p><a title="'.esc_html( __('What Three Words precise location','church-admin' ) ).'" target="_blank"  href="'.esc_url('https://w3w.co/'.$api_response['words'] ).'"><span style="color:red">///</span>'.esc_html( $api_response['words'] ).'</a></p>';
	
        }

	}

	return $out;



}

function church_admin_member_type_option( $currentID)
{
    $mt=church_admin_member_types_array();

    $out = '';
    foreach( $mt AS $id=>$type)
    {
        $out.='<option value="'.(int)$id.'" '.selected( $id,$currentID,FALSE).'>'.esc_html( $type).'</option>';
    }
    return $out;
}


function church_admin_getRemoteMimeType( $url) {
    //church_admin_debug('Mime type check for '.$url);
    $ch = curl_init( $url);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec( $ch);

    # get the content type
    $mimeType=curl_getinfo( $ch, CURLINFO_CONTENT_TYPE);
    //church_admin_debug('Mime type '.$mimeType);
    return $mimeType;
}


/**
 * Recursive sanitation for text or array
 * 
 * @param $array_or_string (array|string)
 * @since  0.1
 * @return mixed
 */
function church_admin_sanitize($array_or_string) {
    if( is_string($array_or_string) ){
        $array_or_string = sanitize_text_field(stripslashes( $array_or_string ) );
        return $array_or_string;
    }elseif( is_array($array_or_string) ){
        foreach ( $array_or_string as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = church_admin_sanitize($value);
                
            }
            else {
                $value = sanitize_text_field( stripslashes( $value ) );
            }
        }
    }
  
    return $array_or_string;
  }


  function church_admin_app_default_menu()
  {
    church_admin_debug('***** church_admin_app_default_menu() ********');
    global $wpdb;
    $defaultMenu = array(
        'home'=>array('edit'=>false,'item'=>esc_html( __('Home','church-admin' ) ),'order'=>1,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'account'=>array('edit'=>false,'item'=>esc_html( __('Account','church-admin' ) ),'order'=>2,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        
        'address'=>array('edit'=>true,'item'=>esc_html( __('Address','church-admin' ) ),'order'=>4,'show'=>TRUE,'type'=>'app','loggedinOnly'=>1),
        
        'calendar'=>array('edit'=>true,'item'=>esc_html( __('Calendar','church-admin' ) ),'order'=>7,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'checkin'=>array('edit'=>true,'item'=>esc_html( __('Checkin','church-admin' ) ),'order'=>8,'show'=>TRUE,'type'=>'app','loggedinOnly'=>1),
        'classes'=>array('edit'=>true,'item'=>esc_html( __('Classes','church-admin' ) ),'order'=>9,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'giving'=>array('edit'=>true,'item'=>esc_html( __('Giving','church-admin' ) ),'order'=>10,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'smallgroup'=>array('edit'=>true,'item'=>esc_html( __('Groups','church-admin' ) ),'order'=>11,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'media'=>array('edit'=>true,'item'=>esc_html( __('Media','church-admin' ) ),'order'=>12,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'messages'=>array('edit'=>TRUE,'item'=>esc_html( __('Messages','church-admin' ) ),'order'=>13,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'myprayer'=>array('edit'=>TRUE,'item'=>esc_html( __('My prayer','church-admin' ) ),'order'=>15,'show'=>TRUE,'type'=>'app','loggedinOnly'=>1),
        'news'=>array('edit'=>true,'item'=>esc_html( __('News','church-admin' ) ),'order'=>16,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        
        'rota'=>array('edit'=>true,'item'=>esc_html( __('Schedule','church-admin' ) ),'order'=>17,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'notifications'=>array('edit'=>false,'item'=>esc_html( __('Notification settings','church-admin' ) ),'order'=>18,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'service-prebooking'=>array('edit'=>TRUE,'item'=>esc_html( __('Service Prebooking','church-admin' ) ),'order'=>19,'show'=>TRUE,'type'=>'app','loggedinOnly'=>1),
        'notifications'=>array('edit'=>false,'item'=>esc_html( __('Notification Settings','church-admin' ) ),'order'=>20,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        '3circles'=>array('edit'=>false,'item'=>esc_html( __('3 circles','church-admin' ) ),'order'=>21,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'not-available'=>array('edit'=>false,'item'=>esc_html( __('My availability','church-admin' ) ),'order'=>22,'show'=>TRUE,'type'=>'app','loggedinOnly'=>1),			
        'register'=>array('edit'=>false,'item'=>esc_html( __('Register','church-admin' ) ),'order'=>23,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0),
        'serving'=>array('edit'=>false,'item'=>esc_html( __('Serving','church-admin' ) ),'order'=>24,'show'=>TRUE,'type'=>'app','loggedinOnly'=>1),
        'logout'=>array('edit'=>false,'item'=>esc_html( __('Reset church','church-admin' ) ),'order'=>25,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0)
        );

        //add custom post types if existant
        $ACexists=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->posts.' WHERE post_type="acts-of-courage"');
        if( !empty( $ACexists ) )
        {
            church_admin_debug('Found acts of courage, so adding');
            $defaultMenu['courage'] = array('edit'=>true,'item'=>esc_html( __('Acts of courage','church-admin' ) ),'order'=>13,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0);
                    
        }
        $BRexists=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->posts.' WHERE post_type="bible-readings"');
        if( !empty( $BRexists ) ){
            church_admin_debug('Found bible reading posts, so adding');
            $defaultMenu['bible'] = array('edit'=>true,'item'=>esc_html( __('Bible Readings','church-admin' ) ),'order'=>5,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0);
            $defaultMenu['bible-readings-archive'] = array('edit'=>true,'item'=>esc_html( __('All Bible Readings','church-admin' ) ),'order'=>6,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0);       
        }
        $PRexists= $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->posts.' WHERE post_type="prayer-requests"');
        
        if(!empty( $PRexists)){
            church_admin_debug('Found prayer requests, so adding');
           $defaultMenu['prayer'] = array('edit'=>true,'item'=>esc_html( __('Prayer requests','church-admin' ) ),'order'=>14,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0);
        }
       
        //church_admin_debug($defaultMenu);
        church_admin_debug('***** END church_admin_app_default_menu() ********');
        return $defaultMenu;

  }

function church_admin_posts_and_pages_dropdown($current=null)
{

    $out='<option value="">'.esc_html( __('Select a post or page','church-admin' ) ).'</option>';
		
    $out .=' <optgroup label="'.esc_html( __('Pages','church-admin' ) ).'">';
    $args = array( 'post_type'=>'page','numberposts' =>-1,'orderby'=>'title','order'=>'ASC');
    $postlinks = get_posts( $args);
    foreach( $postlinks as $postlink ) { setup_postdata( $postlink); $out .= '<option value="'.esc_url( get_permalink( $postlink->ID ) ).'" '.selected(get_permalink( $postlink->ID),$current,FALSE).'>'.esc_html( $postlink->post_title ).'</option>';}
    $out .='</optgroup';
    $out .= ' <optgroup label="'.esc_html( __('Posts','church-admin' ) ).'">';
    $args = array( 'numberposts' => 10);
    $postlinks = get_posts( $args);
    foreach( $postlinks as $postlink ) { setup_postdata( $postlink); $out .= '<option value="'.esc_url( get_permalink( $postlink->ID ) ).'" '.selected(get_permalink( $postlink->ID),$current,FALSE).'>'.esc_html( $postlink->post_title ).'</option>';}
    $out .='</optgroup>';

    return $out;


}

function church_admin_title_case($string) 
{
    if(empty($string)){return;}

    // https://www.media-division.com/correct-name-capitalization-in-php/

	$word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
	$lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
	$uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');

	$string = strtolower($string);
	foreach ($word_splitters as $delimiter)
	{ 
		$words = explode($delimiter, $string); 
		$newwords = array(); 
		foreach ($words as $word)
		{ 
			if (in_array(strtoupper($word), $uppercase_exceptions))
				$word = strtoupper($word);
			else
			if (!in_array($word, $lowercase_exceptions))
				$word = ucfirst($word); 

			$newwords[] = $word;
		}

		if (in_array(strtolower($delimiter), $lowercase_exceptions))
			$delimiter = strtolower($delimiter);

		$string = join($delimiter, $newwords); 
	} 
	return $string; 
}


/**
 * Returns array of safeguarding required ministries.
 *
 * @return $out array
 *
 */
function church_admin_safeguarded_ministries()
{
	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}
	global $wpdb;
	$results=$wpdb->get_results('SELECT ID FROM '.$wpdb->prefix.'church_admin_ministries WHERE safeguarding=1');
	if(!empty( $results) )
	{
		$out=array();
		foreach( $results AS $row)$out[]=$row->ID;
		return $out;
	}
	else return FALSE;
}



function church_admin_safeguarding_ministries()
{
	global $wpdb;

	if(!church_admin_level_check('Kidswork') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html(sprintf(__('You need "%1$s" permissions to access this page','church-admin' ) ,__('Kidswork','church-admin') )).'</h2></div>';
		return;
	}


	$ministries=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries ORDER BY ministry');
	if(!empty( $_POST['safemin'] ) )
	{

		foreach( $ministries AS $ministry)
		{
			$postedTitle=sanitize_title( $ministry->ministry);
			if(!empty( $_POST[$postedTitle] ) )
			{
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET safeguarding=1 WHERE ID="'.(int)$ministry->ID.'"');
			}
			else
			{
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET safeguarding=0 WHERE ID="'.(int)$ministry->ID.'"');
			}
		}
	}
	echo'<h2 class="safeguardingministries-toggle">'.esc_html( __('Which ministries require safeguarding? (click to toggle)','church-admin' ) ).'</h2>';
	echo '<script type="text/javascript">jQuery(function()  {  jQuery(".safeguardingministries-toggle").click(function()  {jQuery(".safeguardingministries").toggle();  });});</script>';
	echo '<div class="safeguardingministries" ';
	if ( empty( $_POST['safemin'] ) )echo ' style="display:none" ';
	echo '>';

	$ministries=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries ORDER BY ministry');
	if(!empty( $ministries) )
	{
		echo '<form action="" method="POST">';
		echo '<table class="form-table">';
		foreach( $ministries AS $ministry)
		{
			echo '<tr><th scope="row">'.esc_html( $ministry->ministry).'</th><td><input type="checkbox" name="'.esc_html(sanitize_title( $ministry->ministry) ).'" value=1';
			if(!empty( $ministry->safeguarding) )echo ' checked="checked" ';
			echo '/></td></tr>';
		}
		echo '<tr><td colspan="2"><input type="hidden" name="safemin" value=1/><input type="submit" name="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr>';
		echo '</table></form>';
	}
	else{echo '<p>'.esc_html( __('Please set up some ministries first','church-admin' ) ).'</p>';}
	echo '</div>';
	return;
}

/**
 * Validates a given latitude $lat
 *
 * @param float|int|string $lat Latitude
 * @return bool `true` if $lat is valid, `false` if not
 */
function church_admin_validate_latitude(float $latitude): bool
{
    return $latitude <= 90 && $latitude >= -90;
}
  
  /**
   * Validates a given longitude $long
   *
   * @param float|int|string $long Longitude
   * @return bool `true` if $long is valid, `false` if not
   */
  function church_admin_validate_longitude(float $longitude): bool
  {
      return $longitude <= 180 && $longitude > -180;
  }


  function church_admin_modules_dropdown()
  {

    //favourites
    $favourites = get_option('church_admin_favourites');
    if(!empty($favourites)){
        echo'<p class="church-admin-module-select">'.esc_html(__('Go to','church-admin')).'<select id="ca_favourite_select">'."\r\n";
        echo'<option>'.esc_html(__('Go to favourite...','church-admin')).'</option>';
        foreach($favourites AS $key=>$data){
            echo '<option value="'.esc_url(wp_nonce_url($data['url'],$data['nonce'])).'">'.esc_html($data['title']).'</option>';
        }
        echo'</section>';
    }



    //church_admin_debug('**** church_admin_modules_dropdown ******');
    $modules=get_option('church_admin_modules');
    $licence = get_option('church_admin_app_new_licence');
   
    if(!empty($_GET['action'])){return;}//only on main menu page
    echo'<p class="church-admin-module-select">'.esc_html(__('Go to','church-admin')).'<select id="ca_module_select">'."\r\n";
    echo'<option>'.esc_html(__('Module...','church-admin')).'</option>';
    if($licence=='premium'){echo'<option value="app">'.esc_html(__('App','church-admin')).'</option>';}
    if(!empty($modules['Attendance']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="attendance">'.esc_html(__('Attendance','church-admin')).'</option>';}
    if(!empty($modules['Calendar']) AND ($licence=='basic'||$licence=='standard'||$licence=='premium')){echo'<option value="calendar">'.esc_html(__('Calendar','church-admin')).'</option>';}
    if(!empty($modules['ChildProtection']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="child-protection">'.esc_html(__('Child protection','church-admin')).'</option>';}
    
    if(!empty($modules['Children'])AND ($licence=='standard'||$licence=='premium')){echo'<option value="childrens-work">'.esc_html(__("Age related groups",'church-admin')).'</option>';}
    if(!empty($modules['Contact'])AND ($licence=='standard'||$licence=='premium')){echo'<option value="contact-form">'.esc_html(__('Contact form','church-admin')).'</option>';}
    if(!empty($modules['Classes'])AND ($licence=='standard'||$licence=='premium')){echo'<option value="classes">'.esc_html(__('Classes','church-admin')).'</option>';}
    if(!empty($modules['Comms'])AND ($licence=='standard'||$licence=='premium')){echo'<option value="comms">'.esc_html(__('Communications','church-admin')).'</option>';}
    echo'<option value="data-protection">'.esc_html(__('Data protection','church-admin')).'</option>';
    if(!empty($modules['Events'])AND ($licence=='standard'||$licence=='premium')){echo'<option value="events">'.esc_html(__('Events','church-admin')).'</option>';}
    if(!empty($modules['Facilities']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="facilities">'.esc_html(__('Facilities','church-admin')).'</option>';}
    if(!empty($modules['Followup']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="follow-up">'.esc_html(__('Follow up','church-admin')).'</option>';}
    if(!empty($modules['Giving']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="giving">'.esc_html(__('Giving','church-admin')).'</option>';}
    if(!empty($modules['Groups']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="groups">'.esc_html(__('Groups','church-admin')).'</option>';}
    if(!empty($modules['Inventory']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="inventory">'.esc_html(__('Inventory','church-admin')).'</option>';}
    if(!empty($modules['Kiosk-App']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="kiosk-app">'.esc_html(__('Kiosk App','church-admin')).'</option>';}
    
    if(!empty($modules['Media'])AND ($licence=='basic'||$licence=='standard'||$licence=='premium')){echo'<option value="media">'.esc_html(__('Media','church-admin')).'</option>';}
    if(!empty($modules['Ministries'])AND ($licence=='standard'||$licence=='premium')){echo'<option value="ministries">'.esc_html(__('Ministries','church-admin')).'</option>';}
    if(!empty($modules['Pastoral'])AND $licence=='premium'){echo'<option value="pastoral">'.esc_html(__('Pastoral','church-admin')).'</option>';}
    if(!empty($modules['People'])){echo'<option value="people">'.esc_html(__('People','church-admin')).'</option>';}
    if(!empty($modules['Rota']) AND ($licence=='standard'||$licence=='premium')){echo'<option value="rota">'.esc_html(__('Schedule','church-admin')).'</option>';}
    
    if(!empty($modules['Children']) AND ($licence=='standard'||$licence=='premium') ){echo'<option value="services">'.esc_html(__('Services and sites','church-admin')).'</option>';}
    if(!empty($modules['Sessions'])AND ($licence=='standard'||$licence=='premium')){ echo'<option value="sessions">'.esc_html(__('Sessions','church-admin')).'</option>';}
    echo'<option value="settings">'.esc_html(__('Settings','church-admin')).'</option>';
    if(!empty($modules['Gifts'])AND ($licence=='standard'||$licence=='premium')){ echo'<option value="spiritual-gifts">'.esc_html(__('Spiritual gifts','church-admin')).'</option>';}
    if(!empty($modules['Units'])AND ($licence=='standard'||$licence=='premium')){echo'<option value="units">'.esc_html(__('Units','church-admin')).'</option>';}
    echo'</select>';
    echo'<script>
   jQuery(document).ready(function($){ 
    $(function(){
      // bind change event to select
      $("#ca_module_select").on("change", function () {
          var url = "#" + $(this).val(); // get selected value
          console.log(url);
          if (url) { // require a URL
              window.location = url; // redirect
          }
          return false;
      });
      $("#ca_favourite_select").on("change", function () {
        var url =  $(this).val(); // get selected value
        console.log(url);
        if (url) { // require a URL
            window.location = url; // redirect
        }
        return false;
    });
    });
});
</script>';
}

  function church_admin_int_check($var){
    if ((is_int($var) || is_string($var)) && preg_match('/^[0-9]\d*\z/', $var)) { 
        return TRUE;
    }else{
        return FALSE;
    }
  }

  function church_admin_save_base64_image( $base64_img, $title ) {
    church_admin_debug('FUNCTION church_admin_save_base64_image');
	// Upload dir.
	$uploads = wp_upload_dir( wp_date('Y/m') );
    $upload_path = $uploads['path'];
    church_admin_debug('Upload PATH :'.$upload_path);
	$img             = str_replace( 'data:image/jpeg;base64,', '', $base64_img );
	$img             = str_replace( ' ', '+', $img );
	$decoded         = base64_decode( $img, TRUE );
    if(empty($decoded)){return FALSE;}

	$file_type       = 'image/jpeg';
	$hashed_filename = md5( $title . microtime() ) . '.jpg';

	// Save the image in the uploads directory.
	$upload_file = file_put_contents( $upload_path . '/' . $hashed_filename, $decoded );

	$attachment = array(
		'post_mime_type' => $file_type,
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
		'guid'           => $upload_dir['url'] . '/' . basename( $hashed_filename )
	);

	$attach_id = wp_insert_attachment( $attachment,$upload_path . '/' . $hashed_filename );
    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    
    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_path . '/' . $hashed_filename );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    return $attach_id;
}

function church_admin_which_stripe_mode()
{
    $premium = get_option('church_admin_payment_gateway');
    if(empty($premium)){
        return '<p>'.__('No payment gateway setup','church-admin').'</p>';
    }
    if(empty($premium['gateway'])||$premium['gateway']!='stripe'){
        return '<p>'.__('Payment Gateway not set to Stripe').'</p>';
    }

    if(empty($premium['stripe_secret_key'])){
        return '<p>'.__('No Stripe secret key set','church-admin').'</p>';
    }
    if(empty($premium['stripe_public_key'])){
        return '<p>'.__('No Stripe public key set','church-admin').'</p>';
    }

    $pos = stripos($premium['stripe_public_key'],'test');
    if ($pos === false) {
        return ;
    } else {
        return '<p>'.wp_kses_post(sprintf(__('Stripe is in TEST mode use test cards from %1$s','church-admin'),'<a target="_blank" href="https://stripe.com/docs/testing#cards">https://stripe.com/docs/testing#cards</a>') ).'</p>';
    }

}

function church_admin_sort_wedding_anniversary()
{
    global $wpdb;
    if(!empty($_POST['dontshow'])){
        update_option('church_admin_custom_wedding_anniversary',TRUE);
    }
    $wa_custom = get_option('church_admin_custom_wedding_anniversary');
    if(!empty($wa_custom)){
        return;
    }
    $custom_fields=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE section="household"  ORDER BY name');
    if(empty($custom_fields)){
        
        return;
    }


    
    //This function allows user to move custom field wedding anniversary to the household database field
    if(!empty($_POST['save'])){
        $ID = (!empty($_POST['custom_id']) && church_admin_int_check($_POST['custom_id']))?(int)$_POST['custom_id']:null;
        if(!empty($ID))
        {
            $household_data=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id = "'.(int)$ID.'" AND data!=""');
            //church_admin_debug($wpdb->last_query);
            church_admin_debug(print_r($household_data,true));
            foreach($household_data AS $row){
                if(church_admin_datecheck($row->data))
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET wedding_anniversary="'.esc_sql($row->data).'" WHERE household_id="'.(int)$row->household_id.'"');
                //church_admin_debug($wpdb->last_query);
            }
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE ID = "'.(int)$ID.'"');
            //church_admin_debug($wpdb->last_query);
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id = "'.(int)$ID.'"');
            //church_admin_debug($wpdb->last_query);
            echo'<div class="notice notice-success"><h2>'.esc_html(__('Wedding Anniversary custom field data migrated','church-admin')).'</h2></div>';
            update_option('church_admin_custom_wedding_anniversary',TRUE);
            update_option('church_admin_show_wedding_anniversary',TRUE);
        }else{
            echo'<div class="notice notice-success">'.esc_html(__('No custom id specified','church-admin')).'</div>';
        }

    }
    else
    {
        echo'<div class="wa_box"><h3>'.esc_html(__('Move "wedding anniversary" custom field to main database','church-admin')).'</h3>';
        echo'<p><form action="" method="POST"><input type=hidden value="1" name="dontshow"><input class="button-secondary" type="submit" value="'.__("Don't show again",'church-admin').'"></form></p>';
        echo'<p>'.esc_html(__('From v3.8.2, "wedding anniversary" has been made a baked in form field for households, which you can enable/disable in settings. Some churches have created a custom field already for wedding anniversaries. If you have, please choose the custom field to migrate the data over.','church-admin')).'</p>';
        
        echo'<form action="" method="POST">';
        echo'<div class="church-admin-form-group"><label>'.esc_html('Pick the custom field that is for wedding anniversaries','church-admin').'</label>';
        echo'<select class="church-admin-form-control" name="custom_id">';
        foreach($custom_fields AS $CF){
            echo'<option value="'.(int)$CF->ID.'">'.esc_html($CF->name).'</option>';
        }
        echo'</select></div>';
        echo'<p><input type="hidden" name="save" value="TRUE"><input type="submit" class="button-primary" value="'.esc_html(__('Move','church-admin')).'"></p></form>';
        echo'</div>';
    }


}







function church_admin_donation_receipt_email($giving_id,$echo)
{
    global $wpdb;

    church_admin_debug('*****  church_admin_donation_receipt_email  ****** ');
    church_admin_debug('Giving ID "'.(int)$giving_id.'"');
    if(empty($giving_id)||!church_admin_int_check($giving_id)){
        church_admin_debug('No/invalid giving id');
        return;
    }

    $giver=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_giving WHERE giving_id="'.esc_sql($giving_id).'"');
    if(empty($giver)){
        church_admin_debug('No giver details');
        return;
    }
    $gifts = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE giving_id="'.esc_sql($giving_id).'"');
    if(empty($gifts)){
        church_admin_debug('No gift(s) details');
        return;
    }

    $premium=get_option('church_admin_payment_gateway');
    //church_admin_debug($premium);
    $currSymbol=!empty( $premium['currency_symbol'] )?$premium['currency_symbol']:"";
    //gift details table
    $total=0;
    $cols = 3;//for total row filling
    $tableStyle='style="font-family: Arial;font-size:1em; border-collapse: collapse;margin-bottom:10px"';
    $thStyle = 'style="border: 1px solid #ddd;padding: 12px; text-align: left; background-color: #CCC;color: white;" ';
    $tdStyle = 'style="border: 1px solid #ddd;padding: 8px;"';
    $givingTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html('Date','church-admin').'</th><th '.$thStyle.'>'.esc_html('Amount','church-admin').'</th><th '.$thStyle.'>'.esc_html('Fund','church-admin').'</th><th '.$thStyle.'>'.esc_html('Method','church-admin').'</th>';
    if ( !empty( $premium['gift_aid'] ) )
    {
        $givingTable.='<th '.$thStyle.'>Gift Aided</th>';
    }
    $givingTable.='</tr></thead>';
    foreach($gifts AS $gift)
    {
        $date   = mysql2date(get_option('date_format'),$giver->donation_date);
        $amount = $currSymbol.number_format($gift->gross_amount,2);
        $total += $gift->gross_amount;
        $fund = $gift->fund;
        $method = ucwords($giver->txn_type);

        $giftaid = !empty($giver->gift_aid)? __('Yes','church-admin'):__('No','church-admin'); 

        $givingTable .='<tr><td '.$tdStyle.'>'.$date.'</td><td '.$tdStyle.'>'.$amount.'</td><td '.$tdStyle.'>'.$fund.'</td><td '.$tdStyle.'>'.$method.'</td>';
        if ( !empty( $premium['gift_aid'] ) )
        {
            $givingTable.='<td '.$tdStyle.'>'.$giftaid.'</td>';
            $cols=3;
        }
        $givingTable.='</tr>';
       
    }
    $givingTable.='<tr><td '.$tdStyle.'>'.__('Total donation','church-admin').'</td><td '.$tdStyle.'>'.$currSymbol.number_format($total,2).'</td><td '.$tdStyle.' colspan='.$cols.'>&nbsp;</td></tr></tbody>';

    //donor name

    $name = $giver->name;
    if(empty( $name ) && !empty( $giver->people_id ) ){
        $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$giver->people_id.'"');
        if(!empty($person)){
            $name = church_admin_formatted_name($person);
        }
    }
    if(empty($name)){
        $name=__('Anonymous','church-admin');
    }
    //donor address
    $address= $giver->address;
    if(empty($address) &&!empty( $giver->people_id ) ){
        $address=$wpdb->get_var('SELECT b.address FROM '.$wpdb->prefix.'church_admin_household b, '.$wpdb->prefix.'church_admin_people a WHERE a.household_id=b.household_id AND a.people_id="'.(int)$giver->people_id.'"');
    }

    $email = !empty( $giver->email ) ? $giver->email : null;
    //prepare message

    $template = get_option('church_admin_giving_receipt_template');
    if(empty($template)){$template='[donations]';}
    $message = str_replace('[name]',$name,wpautop($template));
    $message = str_replace('[donations]',$givingTable,$message);
    $subject = __('Donation receipt','church-admin');

    if(!empty($email)){
        church_admin_email_send($email,$subject,wp_kses_post($message),null,null,null);
        if(!empty($echo)){
            echo'<div class="notice notice-sucess"><h2>'.esc_html(sprintf(__('Receipt sent to %1$s','church-admin'),$name)).'</h2><p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=giving','giving').'">'.esc_html('Back to Giving List','church-admin').'</a><h3>'.esc_html(__('Message content','church-admin')).'</h3></p>'.wp_kses_post($message).'</div>';
        }
    }

}

function church_admin_update_meta_fields($section,$people_id,$household_id,$onboarding,$x)
{
    church_admin_debug('**** church_admin_update_meta_fields **** ');

    global $wpdb;
    $wpdb->show_errors;
    if(empty($section)){$section = 'people';}
    $index = !empty($x) ? '-'.(int)$x :null;
    /*****************************************************
    * Save Custom Fields
    *****************************************************/
    $custom_fields=church_admin_get_custom_fields();

    if(!empty( $custom_fields) )
    {
        $old_values=church_admin_get_old_custom_values($household_id);

        foreach( $custom_fields AS $custom_id=>$field)
        {
            if(empty($onboarding) && !empty($field['onboarding'])){continue;}
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE section="'.esc_sql($section).'" AND people_id="'.(int)$people_id.'" AND custom_id="'.(int)$custom_id.'"');
            church_admin_debug($wpdb->last_query);
            if( $field['section']!=$section)continue;
            if(isset( $_POST['custom-'.$custom_id.$index] ) )
            {
                $new_value = !empty($_POST['custom-'.$custom_id.$index]) ? church_admin_sanitize($_POST['custom-'.$custom_id.$index] ) :NULL ;
                if(is_array($new_value)){$new_value=serialize($new_value);}
                $old_value = !empty($old_values[$custom_id])?$old_values[$custom_id]:null;
                church_admin_custom_transient($custom_id,$people_id,$household_id,$old_value,$new_value);
                $sql='INSERT INTO  '.$wpdb->prefix.'church_admin_custom_fields_meta (`data`,`household_id`,`people_id`,`custom_id`,`section`) VALUES ("'.esc_sql( $new_value ) .'","'.(int)$household_id.'","'.(int)$people_id.'","'.(int)$custom_id.'","'.esc_sql($section).'")';
                $affectd_rows= $wpdb->query($sql);
                church_admin_debug('Affected rows: '.$affectd_rows);
                church_admin_debug($wpdb->last_query);
                $id =$wpdb->insert_id;
                church_admin_debug('Insert ID: '.$id);
            }
        }

    }
}

function church_admin_rota_popup($service_id,$start_date)
{
    $out='Schedule popup';
    return $out;
}




 /**
   * Gives date of a particular day in the current week
   *
   * @param int $day where 1=Monday, 2=Tuesday etc
   * @return ISO date
   */


function church_admin_get_day($day)
{
    $days = array('Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7);

    $today = new \DateTime();
    $today->setISODate((int)$today->format('o'), (int)$today->format('W'), $days[ucfirst($day)]);
    return $today;
}


function church_admin_day_count($day,$month,$year){
    $totalDay=cal_days_in_month(CAL_GREGORIAN,$month,$year);
    
    $count=0;
    
    for($i=1;$totalDay>=$i;$i++){
    
      if( date('l', strtotime($year.'-'.$month.'-'.$i))==ucwords($day)){
        $count++;
        }
    
    }
    
    return $count;
    
    
}
    
function church_admin_ministry_team_check($ministry_id){
    global $wpdb;
    $user=wp_get_current_user();

    if(empty($user)){return FALSE;}

    $check = $wpdb->get_var('SELECT COUNT(a.people_id) FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.user_id="'.(int)$user->ID.'" AND a.people_id=b.people_id AND b.meta_type="ministry" AND b.ID="'.(int)$ministry_id.'"');

    if(!empty($check)){
        return TRUE;
    }
    return FALSE;
}

function church_admin_parents($people_id){
    global $wpdb;
    //church_admin_debug('***** FUNCTION church_admin_parents *******');
    if(empty($people_id)){
        //church_admin_debug('No people_id');
        return null;
    }

    global $wpdb;
    $household_id = $wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    if(empty( $household_id)){
        
        //church_admin_debug('Household not found')
        return null;
    }
    $head_of_household_people_type_id = $wpdb->get_var('SELECT people_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$household_id.'"');
    if(empty(  $head_of_household_people_type_id)){
        return null;
    }
    
    
    $results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" AND people_type_id="'.(int)$head_of_household_people_type_id.'"');
    if(empty($results)){
        return NULL;
    }
    $names=array();
    foreach($results AS $row){
        $names[implode(" ", array_filter(array($row->prefix,$row->last_name)))][]=$row->first_name;
    }
    $output = '';
    foreach($names AS $last_name =>$first_names){
        $output = implode(" & ",$first_names).' '. $last_name;
    }
    return $output;
}

function church_admin_check_image_exists($ID){
    $upload_dir = wp_upload_dir();
    $upload_basedir = $upload_dir['basedir'];
    $meta = get_post_meta( $ID, '_wp_attached_file', true );
    $file = $upload_basedir . '/' . $meta;
    if(file_exists($file)){
        return true;
    }
    else
    {
        return FALSE;
    }
}

/*******************************
 * CHURCH ADMIN EMAIL SEND
 ******************************/
function church_admin_email_send($to,$subject,$message,$from_name=null,$from_email=null,$attachment=array(),$reply_name=null,$reply_to=null,$force_now=FALSE)
{
    church_admin_debug('**** church_admin_email_send ****');
    church_admin_debug(func_get_args());
   


    global $wpdb;
   
    $wpdb->show_errors;
    if(!is_email($to)){
        return __('Missing destination email','church-admin');
    }
    
    $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql($to).'" LIMIT 1');
    church_admin_debug('***** function church_admin_email_send ******');
    
    if(empty($from_name)){$from_name = get_option('church_admin_default_from_name');}
    if(empty($from_email)){$from_email = get_option('church_admin_default_from_email');}
  
    if(empty($reply_name)){$reply_name = $from_name;}
    if(empty($reply_to)){$reply_to = $from_email;}

    $send_message = church_admin_prep_html_email($message,$subject);



    $headers=array('content-type: text/html; charset=UTF-8','from: '.$from_name.' <'.$from_email.'>', 'reply-To: '.$reply_name.' <'.$reply_to.'>');
    
    /**************************
     * Try mailersend first
     *************************/
    $mailersend_api = get_option('church_admin_mailersend_api_key');
    if(!empty($mailersend_api)){
        church_admin_debug('Using mailersend API');
        $url = 'https://api.mailersend.com/v1/email';

        $data = new stdClass();
        $data->from = (object)array('email'=>$from_email,'name'=>$from_name);
            $email_to = new stdClass();
            $email_to->email  = $to;
            //$to->name   = 'Andy Moyle';
        $data->to=array($email_to);
            $reply = new stdClass();
            $reply->email = $reply_name;
            $reply->name = $reply->email;
        $data->reply_to=array($reply);
        $data->subject = $subject;
        $data->html = $send_message;
       
      
        $requestArgs = [
            'httpversion' => '1.1',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $mailersend_api
            ],
            'body' => json_encode($data)
        ];
        
       
        $response = wp_remote_post($url,$requestArgs);
        church_admin_debug($response);
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            church_admin_debug( "Something went wrong: $error_message");
            church_admin_debug($response);
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_email_build (recipients,subject,message,send_date,from_name,from_email)VALUES("'.esc_sql(serialize(array($to))).'","'.esc_sql($subject).'","'.esc_sql($message).'","'.esc_sql(wp_date('Y-m-d H:i:s')).'","'.esc_sql($from_name).'","'.esc_sql($from_email).'")');
            $response_message = '<p>'.esc_html(sprintf(__('Email NOT sent to %1$s with Mailersend','church-admin'),$to)).'</p>'.$response->error_message;
           
        } else {

            $response_code = $response['response']['code'];
            
            church_admin_debug($response_code);
            if($response_code>=400){
                $body=json_decode($response['body']);
                $message=$body->message;
                $response_message ='<p>'.esc_html(__('Email failed to send','church-admin')).'<br>';
                $response_message .= esc_html($message).'<br/>';
                if($message=='The from.email domain must be verified in your account to send emails. #MS42207'){
                    $response_message .= esc_html(sprintf(__('From email used was %1$s','church-admin'),$from_email)).'<br>';
                    $response_message .= church_admin_mailersend_get_domains();
                }
                $response_message.='</p>';
            }
            else
            {
                $response_message = '<p>'.esc_html(sprintf(__('Email sent to %1$s with Mailersend.','church-admin'),$to)).'</p>';
                church_admin_debug($response);
            }
           return $response_message;
        }


        
    }



    $whenToSend=get_option('church_admin_cron');
    if( $whenToSend=='immediate'||empty( $whenToSend) ||!empty($force_now))
    {
        
       
        add_filter( 'wp_mail_from', function( $email)use ($from_email){ return trim($from_email);} );
        add_filter( 'wp_mail_from_name', function( $name)use ($from_name){return trim($from_name);} );
        add_filter( 'wp_mail_content_type', 'set_html_content_type' );
        
        if(wp_mail( $to, $subject,$send_message,$headers) )
        {
            church_admin_debug('Sent to '.$to);
            $recipients = maybe_serialize(array($to));
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_email_build (recipients,subject,message,send_date,from_name,from_email)VALUES("'.esc_sql($recipients).'","'.esc_sql($subject).'","'.esc_sql($message).'","'.esc_sql(wp_date('Y-m-d H:i:s')).'","'.esc_sql($from_name).'","'.esc_sql($from_email).'")');//add message not prepared $send_message!
            //church_admin_debug($wpdb->last_query);
            return esc_html(sprintf(__('Email sent to %1$s  successfully','church-admin'),$to));
        }
        else
        {//log errors
            global $phpmailer;
            if (isset( $phpmailer) ) {
                church_admin_debug("**********\r\n Send error\r\n ".print_r( $phpmailer->ErrorInfo,TRUE)."\r\n");
                //church_admin_debug($phpmailer);
                return sprintf(__('Failed to send to %1$s','church-admin'),$to).' '.$phpmailer->ErrorInfo;
            }
        }
        remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        remove_filter( 'wp_mail_from_name',function( $name ) {return $from_name;} );
        remove_filter( 'wp_mail_from', function( $email) {return $from_email;} );
    }
    else
    {
       
        church_admin_queue_email( $to,$subject,$message,NULL,$from_name,$from_email,$attachment,NULL,$reply_name,$reply_to);//add message not prepared $send_messsage
        church_admin_debug('Queued to '.$to);
        return  esc_html(sprintf(__('Email queued to %1$s successfully','church-admin'),$to));
        
    }
    //save in email build table
    
}


/********************************************
 * CHURCH ADMIN MAILERSEND BULK EMAIL SEND
 ********************************************/

 function church_admin_mailersend_bulk($recipients,$subject,$message,$from_email,$from_name,$reply_email,$reply_name,$attachment,$echo=FALSE){
    
    church_admin_debug('**** church_admin_mailersend_bulk *****');
    church_admin_debug('Recipients array');
    church_admin_debug($recipients);
    
    global $wpdb;
    /*******************************************************************************************
     * 
     * Recipients is an arraywith each item an array
     * $recipients = array(array('email'=>'email','name'=>'name','people_id'=>'people_id'));
     *
     *******************************************************************************************/
   
    $mailersend_api = get_option('church_admin_mailersend_api_key');
    if(empty($mailersend_api)){
        return __('Mailersend API token missing','church-admin');
    }
    if(empty($recipients)){
        return __('No recipients','church-admin');
    }
    if(empty($subject)){
        return __('No subject','church-admin');
    }
    if(empty($message)){
        return __('No email content','church-admin');
    }
    if(empty($from_name)){$from_name = get_option('church_admin_default_from_name');}
    if(empty($from_email)){$from_email = get_option('church_admin_default_from_email');}
    if(empty($reply_name)){$reply_name = $from_name;}
    if(empty($reply_to)){$reply_to = $from_email;}
    		
    $url = 'https://api.mailersend.com/v1/bulk-email';
    $email_recipients=array();//for database record
    $count = count($recipients);
    //Mailersend allows max 500 recipients per POST so make $send_data multidimensional max 500 per array
    $i=1;
    $send_data = array();
    foreach($recipients AS $x=>$recipient){

        $data = new stdClass();
        $data->from = (object)array('email'=>$from_email,'name'=>$from_name);
        $to = new stdClass();
        $to->email  = $recipient['email'];
        $to->name   = $recipient['name'];
        $email_recipients[] = $recipient['email'];
        $data->to=array($to);
        $data->subject = $subject;
        $send_message = str_replace('[NAME]',$recipient['first_name'],$message);
        $data->html = church_admin_prep_html_email($send_message,$subject);
        $send_data[$i][]=$data;
        /*************************************************
        * Mailersend allows 500 email recipients per post.
        * So split into batched of 500
        * but $x starts a 0 so 0%500 is 0
        * so to avoid that add 1 and do ($x+1)%499
        **************************************************/
        if(($x+1) % 499 == 0){
            $i++;
        }

    }
    church_admin_debug('SEND DATA');
    church_admin_debug($send_data);
    
    foreach($send_data AS $iteration=>$data_send){
        $requestArgs = [
            'httpversion' => '1.1',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $mailersend_api
            ],
            'body' => json_encode($data_send)
        ];
        $response = wp_remote_post($url,$requestArgs);
        church_admin_debug($response);
        if ( ! is_wp_error( $response ) ) {
            $readable = json_decode( wp_remote_retrieve_body( $response ), true );
            if(!empty($echo)){
                echo'<p>'.esc_html($readable['message']).'</p>';
                echo'<p>Bulk Email ID: '.esc_html($readable['bulk_email_id']).'</p>';
            }
            
        } else {
            $error_message = $response->get_error_message();
            church_admin_debug($error_message);
            if(!empty($echo)){
                echo '<div class="notice notice-danger"><h2>'.esc_html(__('Something has gone wrong','church-admin')).'</h2><p>'.esc_html($error_message).'</p></div>';
            }
            return;
        }
        
       
        
    }
    if($readable['message'] == 'The bulk email is being processed.' )
            {
                
                //save to database
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_email_build (recipients,subject,message,send_date,from_name,from_email)VALUES("'.esc_sql(serialize(array($email_recipients))).'","'.esc_sql($subject).'","'.esc_sql($message).'","'.esc_sql(wp_date('Y-m-d H:i:s')).'","'.esc_sql($from_name).'","'.esc_sql($from_email).'")');
                //now check status of the message in more detail
                if(!empty($echo)){church_admin_mailersend_status($readable['bulk_email_id']);}
                
            }
    
 

 }

 function church_admin_mailersend_status($bulk_email_id){
    echo'<h3>'.esc_html(__('Mailersend Bulk Email Status','church-admin') ) .'</h3>';
    $mailersend_api = get_option('church_admin_mailersend_api_key');
    if(empty($mailersend_api)){
        echo __('Mailersend API token missing','church-admin');
        return;
    }
        $requestArgs = [
            'httpversion' => '1.1',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $mailersend_api
            ]
        
        ];
    $url = 'https://api.mailersend.com/v1/bulk-email/'.$bulk_email_id;
    $response = wp_remote_get($url,$requestArgs);
    echo'<p>Check for bulk send status</p>';
    
    $readable_status = json_decode($response['body']);
    if(empty($readable_status)){return;}

    echo'<table class="widefat">';
    echo'<tr><th scope="row">'.esc_html('Status').'</th><td>'.esc_html(ucwords($readable_status->data->state)).'</td></tr>';
    echo'<tr><th scope="row">'.esc_html('Recipient count').'</th><td>'.esc_html($readable_status->data->total_recipients_count).'</td></tr>';
    echo'<tr><th scope="row">'.esc_html('Suppressed recipient count').'</th><td>'.esc_html($readable_status->data->suppressed_recipients_count).'</td></tr>';
    if(!empty($readable_status->data->validation_errors)){
        echo'<tr><th scope="row">'.esc_html('Validation errors').'</th><td>'.esc_html(print_r($readable_status->data->validation_errors,TRUE)).'</td></tr>';
    }
    echo'</table>';
    
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=mailersend-status&bulk_email_id='.esc_attr($bulk_email_id),'mailersend-status').'">'.__('Recheck','church-admin').'</a></p>';

 }
 function church_admin_mailersend_get_domains(){
    
    $mailersend_api = get_option('church_admin_mailersend_api_key');
    if(empty($mailersend_api)){
        return;
    }
    $out = '<h3>'.esc_html('Mailersend Verified domain status','church-admin').'</h3>';
    
    $requestArgs = [
            'httpversion' => '1.1',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $mailersend_api
            ]
        
        ];
    $url = 'https://api.mailersend.com/v1/domains';
    $response = wp_remote_get($url,$requestArgs);

    if ( ! is_wp_error( $response ) ) {
        $readable = json_decode( wp_remote_retrieve_body( $response ), true );
        $domains = $readable['data'];
        $verified_domains=array();
        foreach($domains AS $key => $domain){
            $verified = !empty($domain['is_verified']) ? __('Yes','church-admin'):__('No','church-admin');
            $dkim = !empty($domain['dkim']) ? __('Yes','church-admin'):__('No','church-admin');
            $spf = !empty($domain['spf']) ? __('Yes','church-admin'):__('No','church-admin');
            if( !empty($domain['is_verified'])){$verified_domains[]=$domain['name'];}
            $out.='<p><strong>'.__('Domain','church-admin').': '.esc_html($domain['name']).'</strong></br>';
            $out.= esc_html(__('Verified','church-admin')).': '.esc_html($verified).'<br>';
            $out.= esc_html(__('DKIM','church-admin')).': '.esc_html($dkim).'<br>';
            $out.= esc_html(__('SPF','church-admin')).': '.esc_html($spf).'</p>';
        }
        //check church admin default from email
        $email = get_option('church_admin_default_from_email');
        $domainpart = substr($email, strpos($email, '@') + 1);
        if(!in_array($domainpart,$verified_domains)){
            $out.='<p style="color:red"><strong>'.esc_html(__('Your default from email is not the same domain as any Mailersend verified domains. Email sending will fail.','church-admin') ).'</strong></p>';
        }
        
    } else {
        $error_message = $response->get_error_message();
        church_admin_debug($error_message);
        $out.= '<div class="notice notice-danger"><h2>'.esc_html(__('Something has gone wrong','church-admin')).'</h2><p>'.esc_html($error_message).'</p></div>';
        
    }
   
    return $out;


 }
/********************************************
 * HTML email preparation
 ********************************************/
 function church_admin_prep_html_email($content,$subject){
    $content=str_replace('<p>','<p style="font-family:Arial;font-size:1em;">',$content);

    $html='<html><head><title>'.esc_html($subject).'</title><style>*,::before,::after{box-sizing:border-box}html{font-family:system-ui,"Segoe UI",Roboto,Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji";line-height:1.15;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4}body{margin:0}hr{height:0;color:inherit}abbr[title]{text-decoration:underline dotted}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:ui-monospace,SFMono-Regular,Consolas,"Liberation Mono",Menlo,monospace;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit}button,input,optgroup,select,textarea,p,th,td{font-family:inherit;font-size:100%;line-height:1.15;margin:0}button,select{text-transform:none}button,[type="button"],[type="reset"],[type="submit"]{-webkit-appearance:button}::-moz-focus-inner{border-style:none;padding:0}p,table{margin-bottom:10px}:-moz-focusring{outline:1px dotted ButtonText}:-moz-ui-invalid{box-shadow:none}legend{padding:0}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type="search"]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}.play-button{box-sizing:border-box;position:relative;top:150px;left:200px;width: 22px;height: 22px}.gg-play-button-o {    box-sizing: border-box;position: relative;display: block;width: 60px;height: 60px;border: 2px solid;border-radius: 68px;color: red;}.gg-play-button-o::before {content: "";display: block;box-sizing: border-box;position: absolute;width: 0;height: 25px;border-top: 25px solid transparent;border-bottom: 25px solid transparent;border-left: 25px solid;top: 4px;left: 20px;}</style></head><body><div id="container" style="width:90%;height:auto;margin:0 auto;padding:10px;background:#FFF"><!--content-->'.$content.'<!--end-content--><p style="font-family:Arial;font-size:1em;"><a href="'.site_url().'/?action=user-email-settings">'.esc_html( __('Update which emails you receive', 'church-admin' ) ).'</a></p></div></body></html>';
   
    return $html;

 }

 function church_admin_send_push($push_type,$message_type,$tokens,$subject,$message,$sender){
    church_admin_debug('***** church_admin_send_push *******');
    $licence = get_option('church_admin_app_new_licence');
    if($licence!='premium') {return __('Not premium','church-admin');}
    $appID = get_option('church_admin_app_id');
    if(empty($appID)) {return __('Missing App ID','church-admin');}
    if(empty($push_type)){return __('Push type token/topic missing','church-admin');}
    if(empty($message_type)){return __('Message type missing','church-admin');}
    if($push_type=='tokens' AND empty($tokens)){return __('No recipients specified','church-admin');}
    if(empty($subject)){return __('No message subject specified','church-admin');}
    if(empty($message)){return __('No message specified','church-admin');}
    if(empty($sender)){$sender= get_option('blogname');}
    //array('key'=>$key,'app_id'=>$app_id,'push_type'='tokens/topic','tokens'=>$tokens_array,'subject'=>$subject,'message'=>$message)
    $key = md5($appID.site_url());
    $timestamp = mysql2date(get_option('date_format').' '.get_option('time_format'),wp_date('Y-m-d H:i:s'));
    $send_data = array('type'=>$message_type,'site_url'=>site_url(), 'key'=>$key,'app_id'=>$appID,'push_type'=>$push_type,'subject'=>$subject,'message'=>$message,'sender'=>$sender,'timestamp'=>$timestamp);
    
    if($push_type=='tokens' && !empty($tokens) && is_array($tokens)){
        $send_data['tokens']=$tokens;
    }
    else
    {
        $send_data['topic']='church'.(int)$appID;
    }
    $ch = curl_init( 'https://www.churchadminplugin.com/?cap-push='.$key );
   
    # Setup request to send json via POST.
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($send_data) );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    # Return response instead of printing.
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    # Send request.
    $result = curl_exec($ch);
    curl_close($ch);
 
    return $result;
 }


function ca_push_message()
{
	global $wpdb;

	$user=wp_get_current_user();
	$username=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
	$appID=get_option('church_admin_app_id');
	if(empty( $appID) ){return;}
	echo'<h3>'.esc_html( __('Send a push message to app users','church-admin' ) ).'</h3>';;
	if(!empty( $_POST['push-message'] ) &&!empty($_POST['push-subject']))
	{
        echo'<p>Processing</p>';
     
            $message= church_admin_sanitize($_POST['push-message']);
            $subject= church_admin_sanitize($_POST['push-subject']);
            echo church_admin_send_push('topic','message',null,$subject,$message,$username);
		
	}
	else{	
		echo'<form action="admin.php?page=church_admin%2Findex.php&action=push-to-all" method="post" >';
        wp_nonce_field('push-to-all');
        echo '<div class="church-admin-form-group"><label>'.esc_html(__('Subject','church-admin') ).'</label><input class="church-admin-form-control" type="text" required="required" name="push-subject"></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Message','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" required="required" name="push-message"></div>'; 
        echo '<p><input class="button-primary" type="submit" value="'.__('Push to all','church-admin').'"></p></form>';
    }
	
    
}

function church_admin_return_bytes($val) {
    if (empty($val)) {
        $val = 0;
    }
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = floatval($val);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= (1024 * 1024 * 1024); //1073741824
            break;
        case 'm':
            $val *= (1024 * 1024); //1048576
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}


function church_admin_qrcode_generator()
{

    echo'<h2>'.esc_html(__('QR code generator','church-admin')).'</p>';
    if(!empty($_POST['qr-data'])){

    }
    echo'<form action="admin.php?page=church_admin/index.php" method="POST"><input type="hidden" name="action" value="qrcode-generator">';
    wp_nonce_field('qrcode-generator');
    echo'<div class="church-admin-form-group"><label>'.esc_html('Data to put in QR code').'</label>';


}

function church_admin_HTMLToRGB($htmlCode)
  {
    if($htmlCode[0] == '#')
      $htmlCode = substr($htmlCode, 1);

    if (strlen($htmlCode) == 3)
    {
      $htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
    }

    $r = hexdec($htmlCode[0] . $htmlCode[1]);
    $g = hexdec($htmlCode[2] . $htmlCode[3]);
    $b = hexdec($htmlCode[4] . $htmlCode[5]);

    return $b + ($g << 0x8) + ($r << 0x10);
  }

function church_admin_RGBToHSL($RGB) {
    $r = 0xFF & ($RGB >> 0x10);
    $g = 0xFF & ($RGB >> 0x8);
    $b = 0xFF & $RGB;

    $r = ((float)$r) / 255.0;
    $g = ((float)$g) / 255.0;
    $b = ((float)$b) / 255.0;

    $maxC = max($r, $g, $b);
    $minC = min($r, $g, $b);

    $l = ($maxC + $minC) / 2.0;

    if($maxC == $minC)
    {
      $s = 0;
      $h = 0;
    }
    else
    {
      if($l < .5)
      {
        $s = ($maxC - $minC) / ($maxC + $minC);
      }
      else
      {
        $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
      }
      if($r == $maxC)
        $h = ($g - $b) / ($maxC - $minC);
      if($g == $maxC)
        $h = 2.0 + ($b - $r) / ($maxC - $minC);
      if($b == $maxC)
        $h = 4.0 + ($r - $g) / ($maxC - $minC);

      $h = $h / 6.0; 
    }

    $h = (int)round(255.0 * $h);
    $s = (int)round(255.0 * $s);
    $l = (int)round(255.0 * $l);

    return (object) Array('hue' => $h, 'saturation' => $s, 'lightness' => $l);
  }

  function church_admin_light_or_dark($color){
    church_admin_debug('***************** church admin light or dark _******************');
    church_admin_debug('Original Color inHTML format:'.$color);

    $textColor = "#000000";
    $rgb = church_admin_HTMLToRGB($color);
    church_admin_debug('RGB format:'.print_r($rgb,TRUE));
	$hsl = church_admin_RGBToHSL($rgb);
    church_admin_debug('HSL:'.print_r($hsl,TRUE));
	if($hsl->lightness > 200) {
	    // this is light colour, so dark text
        church_admin_debug('Light');
        $textColor = "#000000";
    }
    else{
        church_admin_debug('Dark');
        $textColor = "#FFFFFF";
    }
    return $textColor;
  }

/**********************************
 * Gtes array of dates in a year $y, month $m with given day number where 0 = Sunday
 */
  function church_admin_get_days($y,$m,$day){
   
 
        $dates = array();
        $dayObj  = new DateTime($y.'-'.$m.'-01 09:00:00'); 
  
        for ($i = 0; $i < 7; $i++)
        {
          
            if ($dayObj->format('w') == $day)
            { 
                // first $day found, increment with 7
                while ($i <= 31)
                {
                    $dates[] =  $dayObj->format("Y-m-d");
                  
                    $i += 7;
                    $dayObj->modify("+7 day");
                }
                break;
            }

            $dayObj->modify("+1 day");
        }
        return $dates;
  }

function church_admin_check_date($date, $format = 'Y-m-d H:i:s')
{
      $d = DateTime::createFromFormat($format, $date);
      return $d && $d->format($format) == $date;
}