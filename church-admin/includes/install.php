<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_install()
{
	
/**
 *
 * Installs WP tables and options
 *
 * @author  Andy MoyleF
 * @param    null
 * @return
 * @version  0.2
 *
 *
 *
 */
    global $wpdb;



	church_admin_debug("******** Install.php firing for ".CHURCH_ADMIN_VERSION."  ".date('Y-m-d H:i:s') );
	church_admin_debug('Called by: '. debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function']);	
 //$wpdb->show_errors();

 
 if(!defined('OLD_CHURCH_ADMIN_VERSION')){define('OLD_CHURCH_ADMIN_VERSION',0);}

$people_types=get_option('church_admin_people_type');
if(empty($people_types)){

	$people_types=array('1'=>__('Adult','church-admin'),2=>__('Child','church-admin'),3=>__('Teenager','church-admin'));
	update_option('church_admin_people_type',$people_types);
}

$use_prefix=get_option('church_admin_use_prefix');
if(!isset( $use_prefix) )update_option('church_admin_use_prefix',FALSE);
$use_middle=get_option('church_admin_use_middle_name');
if(!isset( $use_middle) )update_option('church_admin_use_middle_name',FALSE);

	//check for pagination limit
	$page=get_option('church_admin_page_limit');
	if ( empty( $page) )update_option('church_admin_page_limit',50);


 
	
/*********************************************************
*
* Sermon Files table
*
*********************************************************/
if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_sermon_files"') != $wpdb->prefix.'church_admin_sermon_files')
{
	$sql='CREATE TABLE   IF NOT EXISTS '.$wpdb->prefix.'church_admin_sermon_files (`file_name` TEXT NOT NULL ,`file_title` TEXT NOT NULL ,`file_description` TEXT NOT NULL ,`service_id` INT(11),`bible_passages` TEXT NOT NULL,`private` INT(1) NOT NULL DEFAULT "0",`length` TEXT NOT NULL, `pub_date` DATETIME, last_modified DATETIME, `series_id` INT( 11 ) NOT NULL ,`transcript` TEXT,`video_url` TEXT, `speaker` TEXT NOT NULL,`file_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY) CHARACTER SET utf8 COLLATE utf8_general_ci;';
	$wpdb->query( $sql);
}
$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_sermon_files');
$current=array();
foreach($results AS $row){$current[]=$row->Field;}
if(!in_array('embed_code',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD embed_code TEXT NULl DEFAULT NULL');}
if(!in_array('file_subtitle',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD file_subtitle TEXT NULl DEFAULT NULL');}
if(!in_array('external_file',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD external_file TEXT NULl DEFAULT NULL');}
if(in_array('extrenal_file',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_sermon_files DROP COLUMN extrenal_file');}
if(!in_array('transcript',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD transcript TEXT NULl DEFAULT NULL');}
if(!in_array('postID',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD postID INT(11) NULl DEFAULT NULL');}
if(!in_array('plays',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD plays INT(11) NULl DEFAULT NULL');}
if(!in_array('email_sent',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD `email_sent` DATE NOT NULL AFTER `external_file`;');}
if(!in_array('file_slug',$current)){
	$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD file_slug TEXT NULl DEFAULT NULL');
	$sermons=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files');
	if(!empty( $sermons) )
	{
		foreach( $sermons AS $sermon)
		{
			$sql='UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET file_slug="'.esc_sql(sanitize_title( $sermon->file_title) ).'" WHERE file_id="'.(int)$sermon->file_id.'"';
			//church_admin_debug( $sql);
			$wpdb->query( $sql);
		}
	}

}	
$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET plays=0 WHERE plays=null');
//End update for 2.6876, adding in titles for sermon podcast display    
if(!in_array('video_url',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD video_url TEXT NULL DEFAULT NULL AFTER `transcript`');}
if(!in_array('bible_texts',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_files ADD bible_texts TEXT NULL DEFAULT NULL AFTER `bible_passages`');}


	
	
	
	
//sermon podcast table install

    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_sermon_series"') != $wpdb->prefix.'church_admin_sermon_series')
    {
        $sql='CREATE TABLE   IF NOT EXISTS '.$wpdb->prefix.'church_admin_sermon_series (`series_name` TEXT NOT NULL ,`series_image` TEXT NOT NULL,`series_description` TEXT NOT NULL ,last_sermon DATETIME,`series_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY) CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query( $sql);
    }
	$current = array();
	$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_sermon_series');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('last_sermon',$current)){ 
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_series ADD last_sermon DATETIME');
		$sql='SELECT max(pub_date) AS pub_date, series_id FROM '.$wpdb->prefix.'church_admin_sermon_files GROUP BY series_id ORDER BY pub_date DESC';
        $results=$wpdb->get_results( $sql);
        if(!empty( $results) )
        {
            foreach( $results AS $row)
            {
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_series SET last_sermon="'.$row->pub_date.'" WHERE series_id="'.(int)$row->series_id.'"');
            }
        }
	}
    if(!in_array('series_slug',$current)){$wpdb->query( 'ALTER TABLE  '.$wpdb->prefix.'church_admin_sermon_series ADD series_slug TEXT NULL DEFAULT NULL');}
   	//fix missing series_slug
	$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_slug IS NULL OR series_slug=""');
	if(!empty($results)){
		foreach($results AS $row)
		{
			$series_slug = sanitize_title($row->series_name);
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_series SET series_slug="'.esc_sql($series_slug).'" WHERE series_id="'.(int)$row->series_id.'"');
		
		}
	}

	/*********************************************************
	*
	* Check to see if a default series has been created
	* causes display problems if user forgets
	*
	* added 2017-01-10
	*
	**********************************************************/

    $check=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series');
    if ( empty( $check) )
    {
    	$name=get_option('blogname');
    	if ( empty( $name) )$name=__('Default Sermon Series','church-admin');
    	$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name)VALUES("'.esc_sql( $name).'")');
    }

	$member_types=array('0'=>esc_html( __('Mailing List','church-admin' ) ),
	'1'=>esc_html( __('Visitor','church-admin' ) ),
	'2'=>esc_html( __('Member','church-admin')) 
);


//install member type table
if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_member_types"') != $wpdb->prefix.'church_admin_member_types')
{
	$sql='CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_member_types (`member_type_order` INT( 11 ) NOT NULL ,`member_type` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,`member_type_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY)  CHARACTER SET utf8 COLLATE utf8_general_ci;';
	$wpdb->query( $sql);
	$order=1;
	foreach( $member_types AS $id=>$type)
	{
	$check=$wpdb->get_var('SELECT member_type_id FROM '. $wpdb->prefix.'church_admin_member_types'. ' WHERE member_type_id="'.esc_sql( $id).'"');
	if(!$check)$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_member_types' .' (member_type_order,member_type,member_type_id) VALUES("'.esc_sql($order).'","'.esc_sql( $type).'","'.esc_sql( $id).'")');
	$order++;
	}
}

/*****************************
* household table
*******************************/
if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_household"') != $wpdb->prefix.'church_admin_household')
{
	$sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_household ( privacy INT(1) DEFAULT 0,address TEXT NULL DEFAULT NULL, lat VARCHAR(50) NULL DEFAULT NULL,lng VARCHAR (50) NULL DEFAULT NULL,mailing_address TEXT NULL DEFAULT NULL, phone TEXT NULL DEFAULT NULL,member_type_id INT(11) NULL DEFAULT NULL,ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,household_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (household_id) );';
	$wpdb->query( $sql);
}
$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_household');
$current=array();
foreach($results AS $row){$current[]=$row->Field;}

if(!in_array('geocoded',$current)){$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'church_admin_household` ADD `geocoded` INT(1) NOT NULL DEFAULT "0" AFTER `lng`;');}
if(!in_array('first_registered',$current)){$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'church_admin_household` ADD `first_registered` DATE  NULL  AFTER `geocoded`;');}   
if(!in_array('mailing_address',$current)){$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'church_admin_household` ADD `mailing_address` TEXT AFTER `lng`;');} 
if(!in_array('wedding_anniversary',$current)){$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'church_admin_household` ADD `wedding_anniversary` DATE NULL DEFAULT NULL AFTER `mailing_address`;');}    
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_household CHANGE `lat` `lat` VARCHAR(50) NULL DEFAULT NULL;');
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_household CHANGE `lng` `lng` VARCHAR(50) NULL DEFAULT NULL;');
$phoneCheck=$wpdb->get_var('SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS  WHERE table_name = "'.$wpdb->prefix.'church_admin_household" AND COLUMN_NAME = "phone"');
if(strtoupper( $phoneCheck)=='VARCHAR')
{
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_household CHANGE `phone` `phone` TEXT ');
}

if(!in_array('what_three_words',$current)){ $wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_household ADD `what_three_words` TEXT NULL DEFAULT NULL');}
if(in_array('private',$current)){ $wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_household DROP `private`');}
if(!in_array('attachment_id',$current)){ $wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_household ADD `attachment_id` INT (11) NULL DEFAULT NULL');}
   

	/*****************************
	* $wpdb->prefix.'church_admin_people_meta'
	*****************************/
    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_people_meta"') != $wpdb->prefix.'church_admin_people_meta')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_people_meta ( meta_type VARCHAR(255) DEFAULT "ministry", people_id TEXT,ID INT(11), meta_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (meta_id) );';
        $wpdb->query( $sql);
    }
	$current =array();
	$meta_results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_people_meta');
	foreach($meta_results AS $row){$current[]=$row->Field;}
	if(in_array('role_id',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_people_meta CHANGE role_id ID INT(11)');}
	if(in_array('department_id',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_people_meta CHANGE department_id ID INT(11)');}
	if(!in_array('ordered',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_people_meta ADD ordered INT(11) NULL AFTER people_id');}
	if(!in_array('meta_date',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people_meta ADD `meta_date` DATE NOT NULL');}
	if(!in_array('meta_type',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people_meta ADD `meta_type` VARCHAR(255) NOT NULL DEFAULT "ministry" FIRST;');}
	//oopsie on news sending
	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET meta_type="posts" WHERE meta_type="news-send"');
	
    if( $wpdb->get_var('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'.$wpdb->prefix.'church_admin_people_meta" AND  EXTRA like "%auto_increment%"')!=$wpdb->prefix.'church_admin_people_meta')
    {
      $wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people_meta CHANGE `meta_id` `meta_id` INT(11) NOT NULL AUTO_INCREMENT');
    }
    
	
   /*****************************
	* $wpdb->prefix.'church_admin_people'
	*****************************/
    
    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_people"') != $wpdb->prefix.'church_admin_people')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_people (first_name VARCHAR(100) NULL DEFAULT NULL,last_name VARCHAR(100) NULL DEFAULT NULL, date_of_birth DATE NULL DEFAULT NULL, member_type_id INT(11) NULL DEFAULT NULL,attachment_id INT(11) NULL DEFAULT NULL, roles TEXT NULL DEFAULT NULL, sex INT(1) NOT NULL DEFAULT 0 ,mobile TEXT  NULL DEFAULT NULL, email TEXT NULL DEFAULT NULL,people_type_id INT(11) NOT NULL DEFAULT 1,smallgroup_id INT(11) NULL DEFAULT NULL,household_id INT(11) NULL DEFAULT NULL,member_data TEXT NULL DEFAULT NULL,gift_aid INT(1) NOT NULL DEFAULT "0", user_id INT(11) NULL DEFAULT NULL,people_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (people_id) );';
        $wpdb->query( $sql);
    }
	if( $wpdb->get_var('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'.$wpdb->prefix.'church_admin_people" AND  EXTRA like "%auto_increment%"')!=$wpdb->prefix.'church_admin_people')
    {
      $wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `people_id` `people_id` INT(11) NOT NULL AUTO_INCREMENT');
    }
	$current = array();
	$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_people');
	foreach($results AS $row){
		$current[]=$row->Field;
	}
	church_admin_debug('CURRENT columns in people table');
	church_admin_debug($current);
	$sql=array();
	if(!in_array('title',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `title` TEXT NULL DEFAULT NULL FIRST');}
	if(!in_array('first_name',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `first_name` TEXT NULL DEFAULT NULL');}
	if(!in_array('middle_name',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `middle_name` TEXT NULL DEFAULT NULL ');}
	if(!in_array('prefix',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `prefix` TEXT NULL DEFAULT NULL ');}
	if(!in_array('last_name',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `first_name` TEXT NULL DEFAULT NULL ');}
	if(!in_array('email',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `email` TEXT NULL DEFAULT NULL ');}
	if(!in_array('nickname',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `nickname` TEXT NULL DEFAULT NULL ');}
	if(!in_array('mobile',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `mobile` TEXT NULL DEFAULT NULL ');}
	if(!in_array('e164cell',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `e164cell` TEXT NULL DEFAULT NULL ');}
	if(!in_array('sex',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `sex` TEXT NULL DEFAULT NULL ');}
	if(!in_array('household_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `household_id` INT(11) NULL DEFAULT NULL ');}
	if(!in_array('head_of_household',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `head_of_household` INT(1) NOT NULL DEFAULT "0" ');}
	if(!in_array('people_type_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `people_type_id` INT(1) NOT NULL DEFAULT "1" ');}
	if(!in_array('member_type_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `member_type_id` INT(11)  NOT NULL DEFAULT "1" ');}
	if(!in_array('marital_status',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `marital_status` TEXT  NULL DEFAULT NULL ');}
	if(!in_array('pushToken',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `pushToken` TEXT  NULL DEFAULT NULL ');}
	
	if(!in_array('site_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `site_id` INT(11)  NOT NULL DEFAULT "1" ');}
	if(!in_array('user_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `member_type_id` INT(11)  NOT NULL DEFAULT "1" ');}
	if(!in_array('ignore_last_name_check',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `ignore_last_name_check` INT(11)  NOT NULL DEFAULT "0" ');}
	if(!in_array('kidswork_override',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `kidswork_override` INT(11)  NOT NULL DEFAULT "0" ');}

	if(!in_array('mail_send',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `mail_send` INT(11)  NOT NULL DEFAULT "1" ');}
	if(!in_array('email_send',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `email_send` INT(11)  NOT NULL DEFAULT "0" ');}
	if(!in_array('news_send',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `news_send` INT(11)  NOT NULL DEFAULT "0" ');}
	if(!in_array('sms_send',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `sms_send` INT(11)  NOT NULL DEFAULT "0" ');}
	if(!in_array('gdpr_reason',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `gdpr_reason` TEXT NULL DEFAULT NULL');}
	if(!in_array('phone_calls',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `phone_calls` INT(11)  NOT NULL DEFAULT "0" ');}
	if(!in_array('photo_permission',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `photo_permission` INT(11)  NOT NULL DEFAULT "0" ');}
	if(!in_array('active',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `active` INT(1) NOT NULL DEFAULT "1" ');}
	if(!in_array('gift_aid',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `gift_aid` INT(1) NOT NULL DEFAULT "0" ');}
	if(!in_array('funnels',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `funnels` TEXT NULL DEFAULT NULL ');}
	if(!in_array('people_order',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `people_order` INT(11)  NOT NULL DEFAULT "0" ');}
	if(!in_array('token',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `token` TEXT NULL DEFAULT NULL');}
	if(!in_array('token_date',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `token_date` DATE NULL DEFAULT NULL ');}
	if(!in_array('first_registered',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `first_registered` DATE NULL DEFAULT NULL ');}
	if(!in_array('last_updated',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `last_updated` DATE NULL DEFAULT NULL ');}
	if(!in_array('updated_by',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `updated_by` TEXT  NULL DEFAULT NULL ');}
	if(!in_array('show_me',$current)){
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `show_me` INT(11)  NOT NULL DEFAULT "0" ');
		
		$households=$wpdb->get_results('SELECT household_id, privacy FROM '.$wpdb->prefix.'church_admin_household');
		if(!empty( $households) )
		{
			foreach( $households AS $hou)
			{
				if(!empty( $hou->privacy) )  {$show_me=0;}else{$show_me=1;}
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET show_me="'.$show_me.'" WHERE household_id="'.(int)$hou->household_id.'"');
			}
		}
	}
	if(!in_array('privacy',$current)){
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `privacy` TEXT   NULL DEFAULT NULL ');
		$privacy =serialize(array('show-email'=>1,'show-cell'=>1,'show-landline'=>1,'show-address'=>1));
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET privacy="'.esc_sql($privacy).'"');
	}
	
	if(in_array('member_data',$current)){
		//church_admin_debug('Sorting member data');;
		$sql='SELECT people_id, member_data FROM '.$wpdb->prefix.'church_admin_people';
		//church_admin_debug( $sql);;
		$people=$wpdb->get_results( $sql);
		if(!empty( $people) )
		{
			$data=array();
			foreach( $people AS $peep)
			{
				$member_data=maybe_unserialize( $peep->member_data);
				//church_admin_debug("Member_data");
				//church_admin_debug(print_r( $member_data,TRUE) );
	
				if(!empty( $member_data) )
				{
				foreach( $member_data AS $id=>$date)
				{
					$data[]='("'.intval( $peep->people_id).'","member_date","'.(int)$id.'","'.esc_sql( $date).'")';
				}
				}
			}
		
			//church_admin_debug(print_r( $data,TRUE) );
			if(!empty( $data) ){
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,meta_type,ID,meta_date) VALUES '.implode(",",$data) );
			}
			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people DROP member_data;');
		}
	}
	if(in_array('smallgroup_id',$current)){
	//sort out old style smallgroup data
		$check=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup"');
		if ( empty( $check) )
		{
			$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE smallgroup_id!=""');
			if(!empty( $results) )
			{
				foreach( $results AS $row)
				{
					$sgids=maybe_unserialize( $row->smallgroup_id);
					if(is_array( $sgids) )
					{//handle if array form
						foreach( $sgids as $key=>$value)church_admin_update_people_meta( $value,$row->people_id,$meta_type='smallgroup');
	
					}
					else{church_admin_update_people_meta( $row->smallgroup_id,$row->people_id,$meta_type='smallgroup');}
				}
			}
		}
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people DROP COLUMN smallgroup_id');
		//$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people DROP COLUMN smallgroup_attendance');
		//$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people DROP COLUMN departments');
	


	}
	
	if(version_compare(OLD_CHURCH_ADMIN_VERSION,'4.1.32')<=0){
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `first_name` `first_name` TEXT  NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `middle_name` `middle_name` TEXT  NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `last_name` `last_name` TEXT  NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `member_type_id` `member_type_id` INT(11)  NULL DEFAULT NULL;'); 
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `people_type_id` `people_type_id` INT(11)  NULL DEFAULT NULL;'); 
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `user_id` `user_id` INT(11)  NULL DEFAULT NULL;'); 
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `pushToken` `pushToken` TEXT  NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `gdpr_reason` `gdpr_reason` TEXT  NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `marital_status` `marital_status` TEXT  NULL DEFAULT NULL;');
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET marital_status=0 WHERE marital_status="N/A"');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `mobile` `mobile` TEXT  NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `prefix` `prefix` TEXT  NULL DEFAULT NULL;'); 
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `sex` `sex` INT(1)  NULL DEFAULT NULL;'); 
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people CHANGE `attachment_id` `attachment_id` INT(11)  NULL DEFAULT NULL;'); 

 	}
	$current =array();
	$household_results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_household');
	foreach($household_results AS $row){$current[]=$row->Field;}
	if(in_array('ts',$current)){ $wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_household DROP `ts`;');}	
	if(!in_array('last_updated',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_household ADD last_updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP');}
	if(!in_array('updated_by',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_household ADD updated_by INT(11) DEFAULT NULL ');}
	
	
	

	
    
 

$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET meta_type="class" WHERE meta_type="classes"');
    //change people_id column so that it accepts name if not in directory
    $peopleIDCheck=$wpdb->get_var('SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE table_name = "'.$wpdb->prefix.'church_admin_people_meta" AND COLUMN_NAME = "people_id"');
    if(strtoupper( $peopleIDCheck)=='INT')
    {
        $wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people_meta CHANGE `people_id` `people_id` TEXT ');
    }






   

/******************************************************
*
*
* fix blank households from CSV import
*
*
******************************************************/
$empty_households=$wpdb->get_results('SELECT household_id FROM '.$wpdb->prefix.'church_admin_household WHERE address="" AND lat="" AND lng=""');
if(!empty( $empty_households) )
{
    foreach( $empty_households AS $empty)
    {
      //check if no people
      $people=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$empty->household_id.'"');
      if ( empty( $people) )  {$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$empty->household_id.'"');}
      else {
        //check if empty people records
        $empty_people=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$empty->household_id.'" AND first_name="" AND last_name=""');
        if(!empty( $empty_people) )
        {
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$empty->household_id.'"');
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$empty->household_id.'"');
        }
      }
    }
}



//directory housekeeping
//fix bug in 1.3600
$peopleCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people');
$head=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1');
if( $peopleCount==$head)$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=0');



    




//comments


    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_comments"') != $wpdb->prefix.'church_admin_comments')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_comments ( comment TEXT, comment_type TEXT,  timestamp DATETIME, ID int(11), author_id INT(11), parent_id INT (11)  NOT NULL DEFAULT "0",comment_id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (comment_id) );';
        $wpdb->query( $sql);
	}

	/**************************
	 * W3w Language
	 *************************/
	$googleAPI=get_option('church_admin_google_api_key');
	if(!empty( $googleAPI) )
	{
		$whatThreeWords=get_option('church_admin_what_three_words');
		if ( empty( $whatThreeWords) )
		{
			//church_admin_debug('SET W3W');
			update_option('church_admin_what_three_words','off');
		}
		$w3wLanguage=get_option('church_admin_what_three_words_language');
		if ( empty( $w3wLanguage) )update_option('church_admin_what_three_words_language','en');
	}


 
    /************************
	 * install email tables
	*************************/
    if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_email"') != $wpdb->prefix.'church_admin_email')
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $wpdb->prefix.'church_admin_email' ." (recipient varchar(500) NOT NULL,  from_name text NOT NULL,  from_email text NOT NULL,  copy text NOT NULL, subject varchar(500) NOT NULL, message text NOT NULL,attachment text NOT NULL,sent datetime NOT NULL,email_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (email_id) );";
        $wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_email');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('schedule',$current)){
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_email ADD `schedule` DATETIME NULL DEFAULT NULL');
	}else{
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'church_admin_email` CHANGE `schedule` `schedule` DATETIME NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'church_admin_email` CHANGE `sent` `sent` DATETIME NULL DEFAULT NULL;');
	}
	
	if(version_compare(OLD_CHURCH_ADMIN_VERSION,'3.8.65')<=0){
		//old table broken
		$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_email');
	}
	if(!in_array('reply_name',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_email ADD `reply_name` TEXT NULL DEFAULT NULL AFTER from_email');}
	if(!in_array('reply_to',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_email ADD `reply_to` TEXT NULL DEFAULT NULL AFTER from_email');}

	
	if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_email_build"') != $wpdb->prefix.'church_admin_email_build')
	{
		$sql='CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'church_admin_email_build` ( `schedule` DATETIME DEFAULT NULL, `recipients` mediumtext NOT NULL, `subject` mediumtext NOT NULL,`message` mediumtext NOT NULL, `send_date` DATETIME NOT NULL, `filename` mediumtext NOT NULL, `from_name` varchar(500) NOT NULL, `from_email` varchar(500) NOT NULL, `email_id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`email_id`) )';
			$wpdb->query( $sql);
	}
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_email_build');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('content',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_email_build ADD `content` TEXT NULL DEFAULT NULL AFTER message');}
	if(!in_array('status',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_email_build ADD `status` TEXT NULL DEFAULT NULL AFTER message');}
	if(!in_array('reply_name',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_email_build ADD `reply_name` TEXT NULL DEFAULT NULL AFTER from_email');}
	if(!in_array('reply_to',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_email_build ADD `reply_to` TEXT NULL DEFAULT NULL AFTER from_email');}
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_email_build CHANGE `send_date` `send_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');
    
	


/*******************************************************************
*
* Custom fields
*
*******************************************************************/


	if(( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_custom_fields_meta"') != $wpdb->prefix.'church_admin_custom_fields_meta') && ( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_custom_fields"') == $wpdb->prefix.'church_admin_custom_fields') )
	{
		/********************************************************************************************
		 * UPDATING Custom fields for existing sites
		 * OCT 2021 V3.4.95
		 * New meta table doesn't exist
		 * 1) rename the old custom fields table to be the meta table
		 * 2) create a custom fields table
		 * 3) Bring over the custom fields from the options table - beware an array so add 1 to key
		 * 
		 *******************************************************************************************/
		//if $wpdb->prefix.'church_admin_custom_fields' exists in old form, rename it 
		if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_custom_fields"')== $wpdb->prefix.'church_admin_custom_fields')$wpdb->query('RENAME TABLE '.$wpdb->prefix.'church_admin_custom_fields TO '.$wpdb->prefix.'church_admin_custom_fields_meta');
		//now create new version of $wpdb->prefix.'church_admin_custom_fields'
		$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_custom_fields (`name` TEXT NULL, `section` TEXT NULL, `type` TEXT NULL,`default_value` TEXT NULL,show_me INT(1) NULL, `ID` INT(11) AUTO_INCREMENT, PRIMARY KEY (ID) );';
		$wpdb->query( $sql);
		$current = array();
		$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_custom_fields_meta');
		foreach($results AS $row){$current[]=$row->Field;}
		if(!in_array('household_id',$current)){	
			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta ADD `household_id` INT(11) NULL DEFAULT NULL');
		}
		$custom_fields=get_option('church_admin_custom_fields');
		if(!empty( $custom_fields) )
		{
			foreach( $custom_fields AS $key=>$field)
			{
				$ID=$key++; //array starts with 0 key
				if(!empty( $field['default'] ) )  {$default=$field['default'];}else{$default="";}
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields (name,section,type,default_value,ID) VALUES("'.esc_sql( $field['name'] ).'","people","'.esc_sql( $field['type'] ).'","'.esc_sql( $default).'","'.esc_sql( $key).'")');
			} 
			//fix meta table +1 
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_custom_fields_meta SET custom_id = custom_id+1');
		}
		/**************************************************************************************
		 * Add household_id to any $wpdb->prefix.'church_admin_custom_fields_meta' entries
		 * This is in case a custom field is changed from people to household to protect data 
		 ***************************************************************************************/
		$metaResults=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_custom_fields_meta GROUP BY people_id');
		if(!empty( $metaResults) )
		{
			foreach( $metaResults AS $metaRow)
			{
				$household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$metaRow->people_id.'"');
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_custom_fields_meta SET household_id="'.(int)$household_id.'" WHERE people_id="'.(int)$metaRow->people_id.'"');
			}
		}
	}
//new install wouldn't have had the custom meta table so add both custom field tables
if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_custom_fields"') != $wpdb->prefix.'church_admin_custom_fields')
{
	$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_custom_fields (`name` TEXT NULL, `section` TEXT NULL, `type` TEXT NULL,`default_value` TEXT NULL,show_me INT(1) NULL, `ID` INT(11) AUTO_INCREMENT, PRIMARY KEY (ID) );';
	$wpdb->query( $sql);
}

	if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_custom_fields_meta"') != $wpdb->prefix.'church_admin_custom_fields_meta')
	{

		$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_custom_fields_meta (`people_id` INT(11) NULL,`household_id` INT(11) NULL,gift_id INT(11) NULL, `data` TEXT, `custom_id` INT(11),`ID` INT(11) AUTO_INCREMENT, PRIMARY KEY (ID) );';
		$wpdb->query( $sql);
		

	}    
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_custom_fields');
	foreach($results AS $row){
		$current[]=$row->Field;
	}
	if(!in_array('options',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields ADD options TEXT NULL AFTER default_value');}
	if(!in_array('onboarding',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields ADD onboarding INT(1) NULL AFTER options');}
	if(!in_array('custom_order',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields ADD custom_order INT(11) NULL AFTER show_me');}
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_custom_fields_meta');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('household_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta ADD household_id INT(11) NULL ');}
	if(!in_array('gift_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta ADD gift_id INT(11) NULL');}
	if(!in_array('section',$current)){
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta ADD section TEXT NULL');
		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields');
		if(!empty($results)){
			foreach($results AS $row){
				$section=$row->section;
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_custom_fields_meta SET section="'.esc_sql($section).'" WHERE custom_id="'.(int)$row->ID.'"');
				
			}
		}
	}
	




	/*****************************
	 * install calendar table1
	 ****************************/
    $table_name = $wpdb->prefix.'church_admin_calendar_date';
    if( $wpdb->get_var("show tables like '$table_name'") != $table_name)
    { 
		$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_calendar_date (`title` text NULL DEFAULT NULL,`description` text NULL DEFAULT NULL,`location` text NULL DEFAULT NULL,`year_planner` int(1) NULL DEFAULT NULL,`event_image` int(11) NULL DEFAULT NULL,`end_date` date NULL DEFAULT NULL ,`start_date` date NULL DEFAULT NULL ,`start_time` time NULL DEFAULT NULL,`end_time` time NULL DEFAULT NULL, `event_id` int(11) NULL DEFAULT NULL,`facilities_id` int(11) NULL DEFAULT NULL, `general_calendar` int(1) NOT NULL DEFAULT "1",`how_many` int(11) NULL DEFAULT NULL,`date_id` int(11) NOT NULL AUTO_INCREMENT, `cat_id` int(11) NULL DEFAULT NULL,`recurring` text NULL DEFAULT NULL,PRIMARY KEY (`date_id`) )   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
        $wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_calendar_date');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('exceptions',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD exceptions TEXT NULL DEFAULT NULL AFTER recurring');}
    if(!in_array('facilities_id',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD facilities_id INT(11) NULL DEFAULT NULL AFTER event_id');}
	if(!in_array('external_uid',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD external_uid TEXT NULL DEFAULT NULL AFTER event_id');}
	if(!in_array('event_type',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ADD `event_type` VARCHAR(50) NOT NULL DEFAULT "calendar" AFTER `recurring`;');}
	if(!in_array('link',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD link TEXT NULL DEFAULT NULL AFTER facilities_id');}
	if(!in_array('link_title',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD link_title TEXT NULL DEFAULT NULL AFTER facilities_id');}
	if(!in_array('general_calendar',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD general_calendar INT(1) NOT NULL DEFAULT "1" AFTER `facilities_id`');}
	if(!in_array('description',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `description` TEXT NOT NULL DEFAULT NULL  AFTER `facilities_id`');}
	if(!in_array('location',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `location` TEXT NOT NULL DEFAULT NULL AFTER `description`');}
	if(!in_array('year_planner',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `year_planner` INT(1) NOT NULL AFTER `location`');}
	if(!in_array('how_many',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `how_many` INT(11) NULL DEFAULT NULL AFTER `event_id`');}
	if(!in_array('event_image',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `event_image` INT (11) NULL DEFAULT NULL AFTER `year_planner`');}
	if(!in_array('startTime',$current)){
		$sql='ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `startTime` DATETIME AFTER `start_time`';
		$wpdb->query( $sql);
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_date SET startTime=CONCAT_WS(" ",start_date,start_time)');
		$sql='ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `endTime` DATETIME AFTER `end_time`';
		$wpdb->query( $sql);
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_date SET endTime=CONCAT_WS(" ",start_date,end_time)');
	}
	if(in_array('end_date',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date DROP `end_date`;');}

	if(!in_array('recurring',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_date ADD `recurring` TEXT NOT NULL AFTER `year_planner`');}
	if(!in_array('title',$current))
	{
		$sql='ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ADD `title` TEXT  NULL FIRST;';
		$wpdb->query( $sql);
		$events=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_events');
		if(!empty( $events) )
		{
			foreach( $events AS $event)
			{
			$sql='UPDATE '.$wpdb->prefix.'church_admin_calendar_date SET cat_id="'.esc_sql( $event->cat_id).'",event_id="'.esc_sql( $event->event_id).'",recurring="'.esc_sql( $event->recurring).'",title="'.esc_sql( $event->title).'", description="'.esc_sql($event->description).'", location="'.esc_sql( $event->location).'", year_planner="'.esc_sql( $event->year_planner).'" WHERE event_id="'.esc_sql( $event->event_id).'"';

			$wpdb->query( $sql);
			}
		}

	}
	if( !in_array('provisional',$current))
	{
		$sql='ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ADD `provisional` INT(0) NOT NULL DEFAULT 0;';
		$wpdb->query( $sql);
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_date SET provisional=0');
	}
	/***********************
	 * Calendar meta table
	 ************************/
	if(( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_calendar_meta"') != $wpdb->prefix.'church_admin_calendar_meta'))
	{
		$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_calendar_meta (`event_id` INT(11) DEFAULT NULL,`meta_type` TEXT NULL, `meta_value` TEXT NULL, `meta_id` INT (11) AUTO_INCREMENT, PRIMARY KEY (meta_id))';
		$wpdb->query($sql);
		//populate meta table with facility_id
		$events=$wpdb->get_results('SELECT DISTINCT event_id,facilities_id FROM '.$wpdb->prefix.'church_admin_calendar_date');
		if(!empty($events))
		{
			//church_admin_debug($events);
			$values=array();
			foreach($events AS $event){
				//church_admin_debug($event);
				if(!empty($event->facilities_id)){$values[]= '("'.(int)$event->event_id.'","facility_id","'.(int)$event->facilities_id.'")';}
			}
			if(!empty($values)){
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_calendar_meta (event_id,meta_type,meta_value) VALUES '.implode(",",$values));
				//church_admin_debug($wpdb->last_query);
			}
		}

	}
	


    
	
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `title` `title` TEXT NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `description` `description` TEXT NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `location` `location` TEXT NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `recurring` `recurring` TEXT NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `event_image` `event_image` INT(11) NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `cat_id` `cat_id` INT(11) NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `start_date` `start_date` date NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `start_time` `start_time` time NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `startTime` `startTime` datetime NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `end_time` `end_time` time NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `event_id` `event_id` INT(11) NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `facilities_id` `facilities_id` INT(11) NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `link` `link` TEXT NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `link_title` `link_title` TEXT NULL DEFAULT NULL;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `general_calendar` `general_calendar` INT(1)  DEFAULT 1;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `year_planner` `year_planner` INT(1)  DEFAULT 1;');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CHANGE `how_many` `how_many` INT(11) NULL DEFAULT NULL;');



	
    //install calendar table2
    $table_name = $wpdb->prefix.'church_admin_calendar_category';
    if( $wpdb->get_var("show tables like '$table_name'") != $table_name)
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $table_name ."  (category varchar(255)  NOT NULL DEFAULT '',  fgcolor varchar(7)  NOT NULL DEFAULT '', bgcolor varchar(7)  NOT NULL DEFAULT '',textcolor varchar(7)  NOT NULL DEFAULT '', cat_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`cat_id`) )" ;
        $wpdb->query( $sql);
        $wpdb->query("INSERT INTO $table_name (category,bgcolor,cat_id) VALUES('Unused','#FFFFFF','0')");
    }
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_email_build');
	
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_calendar_category');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('textcolor',$current)){
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_category ADD textcolor TEXT NULL DEFAULT NULL');
		$results = $wpdb->get_results('SELECT * FROM  '.$wpdb->prefix.'church_admin_calendar_category');
		if(!empty($results)){
			foreach($results AS $row){

				
				$text_color = church_admin_light_or_dark($row->bgcolor);
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_category SET text_color="'.esc_sql($text_color).'" WHERE cat_id="'.(int)$row->cat_id.'"');
			}
		}
	}



/********************************
 * FACILITIES TABLE
 *******************************/
if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_facilities"')!=$wpdb->prefix.'church_admin_facilities')
{
	$sql="CREATE TABLE IF NOT EXISTS ". $wpdb->prefix.'church_admin_facilities' ."  (facility_name TEXT,facilities_order INT(11),  facilities_id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`facilities_id`) )" ;
        $wpdb->query( $sql);
}
$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_facilities');
$current=array();
foreach($results AS $row){$current[]=$row->Field;}
if(!in_array('hourly_rate',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_facilities ADD `hourly_rate` FLOAT(5,2) NOT NULL DEFAULT "0.00" AFTER `facilities_order`, ADD `terms_doc` TEXT NULL DEFAULT NULL AFTER `hourly_rate`, ADD `admin_email` TEXT NULL DEFAULT NULL AFTER `terms_doc`;');}





$levels=get_option('church_admin_levels');
if(empty($levels)){$levels=array();}
if(empty($levels['ChildProtection'])){$levels['ChildProtection']='administrator';}
if(empty($levels['Prayer'])){$levels['Prayer']='administrator';}
if(empty($levels['Automations'])){$levels['Automations']='administrator';}
if(empty($levels['Pastoral'])){$levels['Pastoral']='administrator';}
if(empty($levels['Directory'])){$levels['Directory']='administrator';}

if(empty($levels['Children'])){$levels['Children']='administrator';}
if(empty($levels['Contact form'])){$levels['Contact form']='administrator';}
if(empty($levels['Comms'])){$levels['Comms']='administrator';}
if(empty($levels['Groups'])){$levels['Groups']='administrator';}
if(empty($levels['Calendar'])){$levels['Calendar']='administrator';}
if(empty($levels['Media'])){$levels['Media']='administrator';}
if(empty($levels['Facilities'])){$levels['Facilities']='administrator';}
if(empty($levels['Ministries'])){$levels['Ministries']='administrator';}
if(empty($levels['Service'])){$levels['Service']='administrator';}
if(empty($levels['Sessions'])){$levels['Sessions']='administrator';}
if(empty($levels['Member Type'])){$levels['Member Type']='administrator';}
if(empty($levels['Sermons'])){$levels['Sermons']='administrator';}
if(empty($levels['Pastoral'])){$levels['Pastoral']='administrator';}
if(empty($levels['Attendance'])){$levels['Attendance']='administrator';}
if(empty($levels['Bulk SMS'])){$levels['Bulk SMS']='administrator';}
if(empty($levels['App'])){$levels['App']='administrator';}
if(empty($levels['Events'])){$levels['Events']='administrator';}
if(empty($levels['Bulk Email'])){$levels['Bulk Email']='administrator';}
if(empty($levels['Visitor'])){$levels['Visitor']='administrator';}
if(empty($levels['Funnel'])){$levels['Funnel']='administrator';}
if(empty($levels['Inventory'])){$levels['Inventory']='administrator';}
if(empty($levels['Bible'])){$levels['Bible']='administrator';}
if(empty($levels['Gifts'])){$levels['Gifts']='administrator';}
update_option('church_Admin_levels',$levels);

//update pdf cache
if(!get_option('church_admin_calendar_width') )update_option('church_admin_calendar_width','630');
if(!get_option('church_admin_pdf_size') )update_option('church_admin_pdf_size','A4');
if(!get_option('church_admin_label') )update_option('church_admin_label','L7163');
if(!get_option('church_admin_page_limit') )update_option('church_admin_page_limit',30);




delete_option('ca_podcast_file_template');
delete_option('ca_podcast_series_template');
delete_option('ca_podcast_speaker_template');
$ca_podcast_settings=get_option('ca_podcast_settings');
$upload_dir = wp_upload_dir();
$path=$upload_dir['basedir'].'/sermons/';
$url=$upload_dir['baseurl'].'/sermons/';
if ( empty( $ca_podcast_settings) )
{
        $ca_podcast_settings=array(

            'title'=>'',
            'copyright'=>'',
            'link'=>$url.'podcast.xml',
            'subtitle'=>'',
            'author'=>'',
            'summary'=>'',
            'description'=>'',
            'owner_name'=>'',
            'owner_email'=>'',
            'image'=>'',
            'category'=>'',
            'explicit'=>''
        );

    }
//Update for 2.6876, adding in titles for sermon podcast display
if ( empty( $ca_podcast_settings['sermons'] ) )$ca_podcast_settings['sermons']=__('Sermons','church-admin');
if ( empty( $ca_podcast_settings['series'] ) )$ca_podcast_settings['series']=__('Series','church-admin');  
if ( empty( $ca_podcast_settings['sermons'] ) )$ca_podcast_settings['sermons']=__('Sermons','church-admin');
if ( empty( $ca_podcast_settings['most-popular'] ) )$ca_podcast_settings['most-popular']=__('Most popular','church-admin');   
if ( empty( $ca_podcast_settings['now-playing'] ) )$ca_podcast_settings['now-playing']=__('Now playing','church-admin'); 
if ( empty( $ca_podcast_settings['sermon-notes'] ) )$ca_podcast_settings['sermon-notes']=__('Sermon notes','church-admin');
update_option('ca_podcast_settings',$ca_podcast_settings);


$socials=get_option('church-admin-socials');
if(!isset( $socials) )update_option('church-admin-socials','TRUE');
//sermonpodcast
//update version
update_option('church_admin_version',CHURCH_ADMIN_VERSION);
update_option('church_admin_prayer_login',FALSE);
//change sex part!

$gender=get_option('church_admin_gender');
if( $gender==array(1=>'Male',0=>'Female') )
{
	//make sure translation is set up
	update_option('church_admin_gender',array(1=>esc_html( __('Male','church-admin' ) ),0=>esc_html( __('Female','church-admin') )));
}
if ( empty( $gender) )update_option('church_admin_gender',array(1=>esc_html( __('Male','church-admin' ) ),0=>esc_html( __('Female','church-admin') )));



  //db indexes

$check=$wpdb->get_results('SHOW INDEX FROM '.$wpdb->prefix.'church_admin_people WHERE KEY_NAME = "member_type_id"');
if ( empty( $check) )
{
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD INDEX `member_type_id` (`member_type_id`)');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD INDEX `household_id` (`household_id`)');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD INDEX `user_id` (`user_id`)');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_household ADD INDEX `household_id` (`household_id`)');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_comments ADD INDEX `author_id` (`author_id`)');
}

 $gdpr=array(
 				esc_html(__('Subscribed via Mailchimp','church-admin' ) ),
 				esc_html(__('User registered on the website','church-admin' ) ),
 				esc_html(__('User confirmed using GDPR form','church-admin' ) ),
 				esc_html(__('Verbal confirmation','church-admin'))
 			);
$gdpr_setting=get_option('church_admin_gdpr');
if ( empty( $gdpr_setting) )update_option('church_admin_gdpr',$gdpr);


//marital status
$old_marital_status=get_option('church-admin-marital_status');
$marital_status=get_option('church_admin_marital_status');
//pre 1.3900 marital status stored wrongly, so update if needed.
if(!empty( $old_marital_status) )
{
  delete_option('church-admin-marital-status');
  if ( empty( $marital_status) )
  {
    $marital_status=$old_marital_status;
    update_option('church_admin_marital_status',$marital_status);
  }
}
if ( empty( $marital_status) )
update_option('church_admin_marital_status',array(
		0=>esc_html( __('N/A','church-admin' ) ),
		1=>esc_html( __('Single','church-admin' ) ),
		2=>esc_html( __('Co-habiting','church-admin' ) ),
		3=>esc_html( __('Married','church-admin' ) ),
		4=>esc_html( __('Divorced','church-admin' ) ),
		5=>esc_html( __('Widowed','church-admin')
	) ));

$admin_message = get_option('church_admin_new_entry_admin_email');
if(empty($admin_message)){
	$admin_message='<p>A new household has confirmed their email. Please <a href="'.admin_url().'/admin.php?page=church_admin/index.php&action=display-household&household_id=[HOUSEHOLD_ID]&section=people&token=[NONCE]">check them out</a></p>';
	update_option('church_admin_new_entry_admin_email',$admin_message);

}else
{
	//fix dodgy link
	$admin_message = str_replace('action=display_household','action=display-household',$admin_message);
	//update from 4.3.6 to add a nonce
	$admin_message = str_replace('&token=[NONCE]','',$admin_message);//oops on previous updates.
	$admin_message = str_replace('[HOUSEHOLD_ID]','[HOUSEHOLD_ID]&token=[NONCE]',$admin_message);
	update_option('church_admin_new_entry_admin_email',$admin_message);
}



$username_style = get_option('church_admin_username_style');
if(empty($username_style)){
	update_option('church_admin_username_style','firstnamelastname');
}
$confirm_email_template = get_option('church_admin_confirm_email_template');
if(empty($confirm_email_template)){

	$subject = __('Please confirm your email address','church-admin');
	if(!empty($gdpreMessage)){
		$message = $gdprMessage;
	}
	else
	{
		$message = '<p>'.__('Thanks for registering on our website. We, [CHURCH_NAME], store your name, address and phone details so we can keep the church organised and would like to be able to continue to communicate by email, sms and mail with you. Your contact details are available on the website [SITE_URL] within a password protected area. Please check with other members of your household who are over 16 and click this [CONFIRM_URL] if you are happy. If you are not happy or would like to discuss further then do get in touch with the church office.</p><p>[EDIT_URL]</p>[HOUSEHOLD_DETAILS]','church-admin');
	}
	update_option('church_admin_confirm_email_template',array('subject'=>$subject,'message'=>$message));
	delete_option('church_admin_gdpr_email');
}



/**********************************************************
*
* From 2.72120 admin approval of new registrations default
*
************************************************************/    
if(OLD_CHURCH_ADMIN_VERSION<=2.72110)
{
    update_option('church_admin_admin_approval_required',TRUE);
}
    
    
/***************************************
*
* Tidy of head of household
*
****************************************/
$households=$wpdb->get_results('SELECT household_id FROM '.$wpdb->prefix.'church_admin_household');
if(!empty( $households) )
{
	foreach( $households As $household)  {church_admin_head_of_household_tidy( $household->household_id);}
}
/***************************************
*
* Refresh Address list output after change
*
****************************************/
if(OLD_CHURCH_ADMIN_VERSION<=2.4290)  {delete_option('church-admin-directory-output');}

 /**************************************
 *
 * Fix MySQL dates can't be 0000-00-00
 *
 ***************************************/
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ALTER start_date DROP DEFAULT');
//$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ALTER end_date DROP DEFAULT');    


   
$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET date_of_birth = "1000-01-01" WHERE date_of_birth<"1000-01-01"');
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people MODIFY date_of_birth DATE NULL');
$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET date_of_birth = NULL WHERE date_of_birth<="1000-01-01"');


  


	//fix bug in 4.0.14
	if(defined('OLD_CHURCH_ADMIN_VERSION') && version_compare(OLD_CHURCH_ADMIN_VERSION,'4.0.14')<=0)
	{
		if(!empty($smtp)){
			update_option('church_admin_transactional_email_method','smtpserver');
			update_option('church_admin_bulk_email_method','smtpserver');
		}

	}

	$old_email_method=get_option('church_admin_email_method');
	if(empty($old_email_method)){
		delete_option('church_admin_email_method');
		$smtp=get_option('church_admin_smtp_settings');
		if(!empty($smtp)){
			update_option('church_admin_transactional_email_method','smtpserver');
			update_option('church_admin_bulk_email_method','smtpserver');
		}
		else{
			update_option('church_admin_transactional_email_method','native');
			update_option('church_admin_bulk_email_method','native');
		}
		
		
	}
	else
	{
	
		delete_option('church_admin_email_method');
		switch($old_email_method){
			case 'native':
			case'website':
				update_option('church_admin_transactional_email_method','native');
				update_option('church_admin_bulk_email_method','native');
			break;
			case 'smtpserver':
				update_option('church_admin_transactional_email_method','smtpserver');
				update_option('church_admin_bulk_email_method','server');
			break;
			case 'mailchimp':
				delete_option('church_admin_mailchimp');
				update_option('church_admin_bulk_email_method','native');
				update_option('church_admin_transactional_email_method','native');
			break;
			}
	}
	$mailersend_api = get_option('church_admin_mailersend_api_key');
	if(!empty($mailersend_api)){
			update_option('church_admin_transactional_email_method','mailersend');
			update_option('church_admin_bulk_email_method','mailersend');
	}

		


	


	$oldroles=get_option('church_admin_roles');
	if(!empty( $oldroles) )
	{
		update_option('church_admin_departments',$oldroles);
		delete_option('church_admin_roles');
	}





	//fix podcast links
	if(defined('OLD_CHURCH_ADMIN_VERSION') && version_compare(OLD_CHURCH_ADMIN_VERSION,'3.7.38')<=0)
	{
		church_admin_debug('Update podcast links');
		$ca_podcast_settings=get_option('ca_podcast_settings');
		if(!empty($ca_podcast_settings))
		{
			if(!empty($ca_podcast_settings['itunes_link'])){
				$ca_podcast_settings['itunes_link'] = html_entity_decode($ca_podcast_settings['itunes_link']);
			}
			if(!empty($ca_podcast_settings['spotify_link'])){
				$ca_podcast_settings['spotify_link'] = html_entity_decode($ca_podcast_settings['spotify_link']);
			}
			if(!empty($ca_podcast_settings['amazon_link'])){
				$ca_podcast_settings['amazon_link'] = html_entity_decode($ca_podcast_settings['amazon_link']);
			}
			update_option('ca_podcast_settings',$ca_podcast_settings);
		}
	}

	
	//force app cache reset
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');


	$default_from_name = get_option('church_admin_default_from_name');
	if(empty($default_from_name)){update_option('church_admin_default_from_name',get_option('blogname'));}
	$default_from_email = get_option('church_admin_default_from_email');
	if(empty($default_from_email)){update_option('church_admin_default_from_email',get_option('admin_email'));}


	//update permissions v4.5.0
	$user_permissions=get_option('church_admin_user_permissions');
	if(!empty($user_permissions['Bulk Email'])){
		$user_permissions['Bulk_Email']  = $user_permissions['Bulk Email'];
		$user_permissions['Bulk_SMS'] = $user_permissions['Bulk Email'];
		$user_permission['Push'] = $user_permissions['Bulk Email'];
		unset($user_permissions['Bulk Email']);
		update_option('church_admin_user_permissions',$user_permissions);
	}
	if(!empty($user_permissions['Contact Form'])){
		$user_permissions['Contact_Form'] = $user_permissions['Contact Form'];
		unset($user_permissions['Contact Form']);
		update_option('church_admin_user_permissions',$user_permissions);
	}
	

	/*********************************
	 * Turn services into an option
	 **********************************/
	if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_services"') == $wpdb->prefix.'church_admin_services')
	{
		church_admin_debug('found services table');
		$services = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_id');
		church_admin_debug($wpdb->last_query);
		church_admin_debug($services);
		if(!empty($services)){
			$servicesArray=array();
			foreach($services AS $service){
				$details = $service->service_name;
				if(!empty($service->service_time)){$details.=' '.$service->service_time;}
				$servicesArray[$service->service_id]=$details;
			}
			update_option('church_admin_services',$servicesArray);
			$wpdb->query('DROP TABLE '.$wpdb->prefix.'church_admin_services');
		}
	}

	church_admin_debug("Install function finished ".date("Y-m-d H:i:s") );
}//end of install function
