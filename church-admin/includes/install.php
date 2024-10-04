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
    global $wpdb,$church_admin_version;



	church_admin_debug("******** Install.php firing for $church_admin_version  ".date('Y-m-d H:i:s') );
	church_admin_debug('Called by: '. debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function']);	
 //$wpdb->show_errors();

 
 if(!defined('OLD_CHURCH_ADMIN_VERSION')){define('OLD_CHURCH_ADMIN_VERSION',0);}
//working with children
    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_safeguarding"') != $wpdb->prefix.'church_admin_safeguarding')
    {
    	$sql='CREATE TABLE  IF NOT EXISTS `'.$wpdb->prefix.'church_admin_safeguarding` (`people_id` INT(11),`department_id` TEXT,`employment_status` TEXT,`start_date` DATE NULL,`CRW_cat` TEXT,`exemptions` TEXT, `status` TEXT,`receipt` TEXT, `WWC_card` TEXT, `WWC_expiry` DATE NULL, `review_date` DATE NULL, `validation_date` DATE NULL, `DBS` TEXT, `DBS_date` DATE NULL, `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query( $sql);
  	}
    

/*********************************************************
*
* App tables
*
*********************************************************/
if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_app_log_visit"') != $wpdb->prefix.'church_admin_app_log_visit')
{
        $sql='CREATE TABLE   IF NOT EXISTS '.$wpdb->prefix.'church_admin_app_log_visit (`page` TEXT  NULL ,`visit_date` DATE,`visits` INT(11) NULL, ID INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query( $sql);
}


//app logins added 2016-08-05
    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_app"') != $wpdb->prefix.'church_admin_app')
    {
        $sql='CREATE TABLE   IF NOT EXISTS '.$wpdb->prefix.'church_admin_app (`UUID` TEXT NOT NULL ,`user_id` INT NOT NULL,`people_id` INT NOT NULL ,`last_login` DATETIME,`last_page` TEXT, app_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query( $sql);
    }
	$current=array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_app');
	foreach($results AS $row){$current[]=$row->Field;}
	if(in_array('last-login',$current)){$wpdb->query( 'ALTER TABLE  `'.$wpdb->prefix.'church_admin_app` CHANGE  `last-login`  `last_login` DATETIME NULL DEFAULT NULL ;');}
	if(!in_array('last_page',$current)){$wpdb->query( 'ALTER TABLE  `'.$wpdb->prefix.'church_admin_app` ADD last_page TEXT NULL DEFAULT NULL;');}
    //delete any lingering logins with no user_id so they are forced to login again
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_app WHERE user_id=0');
    //Bible REading Plan

      if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_brplan"') != $wpdb->prefix.'church_admin_brplan')
    {
        $sql='CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'church_admin_brplan` (  `readings` TEXT NOT NULL,`passages` TEXT NOT NULL,  `ID` int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`ID`) ) ';
        $wpdb->query( $sql);
        $sql='INSERT INTO '.$wpdb->prefix.'church_admin_brplan (`readings`, `passages`, `ID`) VALUES
(\'a:4:{i:0;s:5:"Gen 1";i:1;s:6:"Matt 1";i:2;s:6:"Ezra 1";i:3;s:6:"Acts 1";}\',"",1),
(\'a:4:{i:0;s:5:"Gen 2";i:1;s:6:"Matt 2";i:2;s:6:"Ezra 2";i:3;s:6:"Acts 2";}\',"", 2),
(\'a:4:{i:0;s:5:"Gen 3";i:1;s:6:"Matt 3";i:2;s:6:"Ezra 3";i:3;s:6:"Acts 3";}\',"", 3),
(\'a:4:{i:0;s:5:"Gen 4";i:1;s:6:"Matt 4";i:2;s:6:"Ezra 4";i:3;s:6:"Acts 4";}\',"", 4),
(\'a:4:{i:0;s:5:"Gen 5";i:1;s:6:"Matt 5";i:2;s:6:"Ezra 5";i:3;s:6:"Acts 5";}\',"", 5),
(\'a:4:{i:0;s:5:"Gen 6";i:1;s:6:"Matt 6";i:2;s:6:"Ezra 6";i:3;s:6:"Acts 6";}\',"", 6),
(\'a:4:{i:0;s:5:"Gen 7";i:1;s:6:"Matt 7";i:2;s:6:"Ezra 7";i:3;s:6:"Acts 7";}\',"", 7),
(\'a:4:{i:0;s:5:"Gen 8";i:1;s:6:"Matt 8";i:2;s:6:"Ezra 8";i:3;s:6:"Acts 8";}\',"", 8),
(\'a:4:{i:0;s:8:"Gen 9-10";i:1;s:6:"Matt 9";i:2;s:6:"Ezra 9";i:3;s:6:"Acts 9";}\',"", 9),
(\'a:4:{i:0;s:6:"Gen 11";i:1;s:7:"Matt 10";i:2;s:7:"Ezra 10";i:3;s:7:"Acts 10";}\',"", 10),
(\'a:4:{i:0;s:6:"Gen 12";i:1;s:7:"Matt 11";i:2;s:5:"Neh 1";i:3;s:7:"Acts 11";}\',"", 11),
(\'a:4:{i:0;s:6:"Gen 13";i:1;s:7:"Matt 12";i:2;s:5:"Neh 2";i:3;s:7:"Acts 12";}\',"", 12),
(\'a:4:{i:0;s:6:"Gen 14";i:1;s:7:"Matt 13";i:2;s:5:"Neh 3";i:3;s:7:"Acts 13";}\',"",13),
(\'a:4:{i:0;s:6:"Gen 15";i:1;s:7:"Matt 14";i:2;s:5:"Neh 4";i:3;s:7:"Acts 14";}\', "",14),
(\'a:4:{i:0;s:6:"Gen 16";i:1;s:7:"Matt 15";i:2;s:5:"Neh 5";i:3;s:7:"Acts 15";}\',"",15),
(\'a:4:{i:0;s:6:"Gen 17";i:1;s:7:"Matt 16";i:2;s:5:"Neh 6";i:3;s:7:"Acts 16";}\',"",16),
(\'a:4:{i:0;s:6:"Gen 18";i:1;s:7:"Matt 17";i:2;s:5:"Neh 7";i:3;s:7:"Acts 17";}\',"",17),
(\'a:4:{i:0;s:6:"Gen 19";i:1;s:7:"Matt 18";i:2;s:5:"Neh 8";i:3;s:7:"Acts 18";}\',"",18),
(\'a:4:{i:0;s:6:"Gen 20";i:1;s:7:"Matt 19";i:2;s:5:"Neh 9";i:3;s:7:"Acts 19";}\',"",19),
(\'a:4:{i:0;s:6:"Gen 21";i:1;s:7:"Matt 20";i:2;s:6:"Neh 10";i:3;s:7:"Acts 20";}\',"",20),
(\'a:4:{i:0;s:6:"Gen 22";i:1;s:7:"Matt 21";i:2;s:6:"Neh 11";i:3;s:7:"Acts 21";}\',"", 21),
(\'a:4:{i:0;s:6:"Gen 23";i:1;s:7:"Matt 22";i:2;s:6:"Neh 12";i:3;s:7:"Acts 22";}\',"",22),
(\'a:4:{i:0;s:6:"Gen 24";i:1;s:7:"Matt 23";i:2;s:6:"Neh 13";i:3;s:7:"Acts 23";}\',"", 23),
(\'a:4:{i:0;s:6:"Gen 25";i:1;s:7:"Matt 24";i:2;s:5:"Est 1";i:3;s:7:"Acts 24";}\',"", 24),
(\'a:4:{i:0;s:6:"Gen 26";i:1;s:7:"Matt 25";i:2;s:5:"Est 2";i:3;s:7:"Acts 25";}\',"", 25),
(\'a:4:{i:0;s:6:"Gen 27";i:1;s:7:"Matt 26";i:2;s:5:"Est 3";i:3;s:7:"Acts 26";}\',"", 26),
(\'a:4:{i:0;s:6:"Gen 28";i:1;s:7:"Matt 27";i:2;s:5:"Est 4";i:3;s:7:"Acts 27";}\',"", 27),
(\'a:4:{i:0;s:6:"Gen 29";i:1;s:7:"Matt 28";i:2;s:5:"Est 5";i:3;s:7:"Acts 28";}\',"", 28),
(\'a:4:{i:0;s:6:"Gen 30";i:1;s:6:"Mark 1";i:2;s:5:"Est 6";i:3;s:5:"Rom 1";}\',"", 29),
(\'a:4:{i:0;s:6:"Gen 31";i:1;s:6:"Mark 2";i:2;s:5:"Est 7";i:3;s:5:"Rom 2";}\',"", 30),
(\'a:4:{i:0;s:6:"Gen 32";i:1;s:6:"Mark 3";i:2;s:5:"Est 8";i:3;s:5:"Rom 3";}\',"", 31),
(\'a:4:{i:0;s:6:"Gen 33";i:1;s:6:"Mark 4";i:2;s:8:"Est 9-10";i:3;s:5:"Rom 4";}\',"", 32),
(\'a:4:{i:0;s:6:"Gen 34";i:1;s:6:"Mark 5";i:2;s:5:"Job 1";i:3;s:5:"Rom 5";}\',"", 33),
(\'a:4:{i:0;s:9:"Gen 35-36";i:1;s:6:"Mark 6";i:2;s:5:"Job 2";i:3;s:5:"Rom 6";}\',"", 34),
(\'a:4:{i:0;s:6:"Gen 37";i:1;s:6:"Mark 7";i:2;s:5:"Job 3";i:3;s:5:"Rom 7";}\',"", 35),
(\'a:4:{i:0;s:6:"Gen 38";i:1;s:6:"Mark 8";i:2;s:5:"Job 4";i:3;s:5:"Rom 8";}\',"", 36),
(\'a:4:{i:0;s:6:"Gen 39";i:1;s:6:"Mark 9";i:2;s:5:"Job 5";i:3;s:5:"Rom 9";}\',"", 37),
(\'a:4:{i:0;s:6:"Gen 40";i:1;s:7:"Mark 10";i:2;s:5:"Job 6";i:3;s:6:"Rom 10";}\',"", 38),
(\'a:4:{i:0;s:6:"Gen 41";i:1;s:7:"Mark 11";i:2;s:5:"Job 7";i:3;s:6:"Rom 11";}\',"", 39),
(\'a:4:{i:0;s:6:"Gen 42";i:1;s:7:"Mark 12";i:2;s:5:"Job 8";i:3;s:6:"Rom 12";}\',"", 40),
(\'a:4:{i:0;s:6:"Gen 43";i:1;s:7:"Mark 13";i:2;s:5:"Job 9";i:3;s:6:"Rom 13";}\',"", 41),
(\'a:4:{i:0;s:6:"Gen 44";i:1;s:7:"Mark 14";i:2;s:6:"Job 10";i:3;s:6:"Rom 14";}\',"", 42),
(\'a:4:{i:0;s:6:"Gen 45";i:1;s:7:"Mark 15";i:2;s:6:"Job 11";i:3;s:6:"Rom 15";}\',"", 43),
(\'a:4:{i:0;s:6:"Gen 46";i:1;s:7:"Mark 16";i:2;s:6:"Job 12";i:3;s:6:"Rom 16";}\',"", 44),
(\'a:4:{i:0;s:6:"Gen 47";i:1;s:11:"Luke 1:1-38";i:2;s:6:"Job 13";i:3;s:7:"1 Cor 1";}\',"", 45),
(\'a:4:{i:0;s:6:"Gen 48";i:1;s:12:"Luke 1:39-80";i:2;s:6:"Job 14";i:3;s:7:"1 Cor 2";}\',"", 46),
(\'a:4:{i:0;s:6:"Gen 49";i:1;s:6:"Luke 2";i:2;s:6:"Job 15";i:3;s:7:"1 Cor 3";}\',"", 47),
(\'a:4:{i:0;s:6:"Gen 50";i:1;s:6:"Luke 3";i:2;s:9:"Job 16-17";i:3;s:7:"1 Cor 4";}\',"", 48),
(\'a:4:{i:0;s:4:"Ex 1";i:1;s:6:"Luke 4";i:2;s:6:"Job 18";i:3;s:7:"1 Cor 5";}\',"", 49),
(\'a:4:{i:0;s:4:"Ex 2";i:1;s:6:"Luke 5";i:2;s:6:"Job 19";i:3;s:7:"1 Cor 6";}\',"", 50),
(\'a:4:{i:0;s:4:"Ex 3";i:1;s:6:"Luke 6";i:2;s:6:"Job 20";i:3;s:7:"1 Cor 7";}\',"", 51),
(\'a:4:{i:0;s:4:"Ex 4";i:1;s:6:"Luke 7";i:2;s:6:"Job 21";i:3;s:7:"1 Cor 8";}\',"", 52),
(\'a:4:{i:0;s:4:"Ex 5";i:1;s:6:"Luke 8";i:2;s:6:"Job 22";i:3;s:7:"1 Cor 9";}\',"", 53),
(\'a:4:{i:0;s:4:"Ex 6";i:1;s:6:"Luke 9";i:2;s:6:"Job 23";i:3;s:8:"1 Cor 10";}\',"", 54),
(\'a:4:{i:0;s:4:"Ex 7";i:1;s:7:"Luke 10";i:2;s:6:"Job 24";i:3;s:8:"1 Cor 11";}\',"", 55),
(\'a:4:{i:0;s:4:"Ex 8";i:1;s:7:"Luke 11";i:2;s:9:"Job 25-26";i:3;s:8:"1 Cor 12";}\',"", 56),
(\'a:4:{i:0;s:4:"Ex 9";i:1;s:7:"Luke 12";i:2;s:6:"Job 27";i:3;s:8:"1 Cor 13";}\',"", 57),
(\'a:4:{i:0;s:5:"Ex 10";i:1;s:7:"Luke 13";i:2;s:6:"Job 28";i:3;s:8:"1 Cor 14";}\',"", 58),
(\'a:4:{i:0;s:5:"Ex 11";i:1;s:7:"Luke 14";i:2;s:6:"Job 29";i:3;s:8:"1 Cor 15";}\',"", 59),
(\'a:4:{i:0;s:5:"Ex 12";i:1;s:7:"Luke 15";i:2;s:6:"Job 30";i:3;s:8:"1 Cor 16";}\',"", 60),
(\'a:4:{i:0;s:5:"Ex 13";i:1;s:7:"Luke 16";i:2;s:6:"Job 31";i:3;s:7:"2 Cor 1";}\',"", 61),
(\'a:4:{i:0;s:5:"Ex 14";i:1;s:7:"Luke 17";i:2;s:6:"Job 32";i:3;s:7:"2 Cor 2";}\',"", 62),
(\'a:4:{i:0;s:5:"Ex 15";i:1;s:7:"Luke 18";i:2;s:6:"Job 33";i:3;s:7:"2 Cor 3";}\',"", 63),
(\'a:4:{i:0;s:5:"Ex 16";i:1;s:7:"Luke 19";i:2;s:6:"Job 34";i:3;s:7:"2 Cor 4";}\',"", 64),
(\'a:4:{i:0;s:5:"Ex 17";i:1;s:7:"Luke 20";i:2;s:6:"Job 35";i:3;s:7:"2 Cor 5";}\',"", 65),
(\'a:4:{i:0;s:5:"Ex 18";i:1;s:7:"Luke 21";i:2;s:6:"Job 36";i:3;s:7:"2 Cor 6";}\',"", 66),
(\'a:4:{i:0;s:5:"Ex 19";i:1;s:7:"Luke 22";i:2;s:6:"Job 37";i:3;s:7:"2 Cor 7";}\',"", 67),
(\'a:4:{i:0;s:5:"Ex 20";i:1;s:7:"Luke 23";i:2;s:6:"Job 38";i:3;s:7:"2 Cor 8";}\',"", 68),
(\'a:4:{i:0;s:5:"Ex 21";i:1;s:7:"Luke 24";i:2;s:6:"Job 39";i:3;s:7:"2 Cor 9";}\',"", 69),
(\'a:4:{i:0;s:5:"Ex 22";i:1;s:6:"John 1";i:2;s:6:"Job 40";i:3;s:8:"2 Cor 10";}\',"", 70),
(\'a:4:{i:0;s:5:"Ex 23";i:1;s:6:"John 2";i:2;s:6:"Job 41";i:3;s:8:"2 Cor 11";}\',"", 71),
(\'a:4:{i:0;s:5:"Ex 24";i:1;s:6:"John 3";i:2;s:6:"Job 42";i:3;s:8:"2 Cor 12";}\',"", 72),
(\'a:4:{i:0;s:5:"Ex 25";i:1;s:6:"John 4";i:2;s:6:"Prov 1";i:3;s:8:"2 Cor 13";}\',"", 73),
(\'a:4:{i:0;s:5:"Ex 26";i:1;s:6:"John 5";i:2;s:6:"Prov 2";i:3;s:5:"Gal 1";}\',"", 74),
(\'a:4:{i:0;s:5:"Ex 27";i:1;s:6:"John 6";i:2;s:6:"Prov 3";i:3;s:5:"Gal 2";}\',"", 75),
(\'a:4:{i:0;s:5:"Ex 28";i:1;s:6:"John 7";i:2;s:6:"Prov 4";i:3;s:5:"Gal 3";}\',"", 76),
(\'a:4:{i:0;s:5:"Ex 29";i:1;s:6:"John 8";i:2;s:6:"Prov 5";i:3;s:5:"Gal 4";}\',"", 77),
(\'a:4:{i:0;s:5:"Ex 30";i:1;s:6:"John 9";i:2;s:6:"Prov 6";i:3;s:5:"Gal 5";}\',"", 78),
(\'a:4:{i:0;s:5:"Ex 31";i:1;s:7:"John 10";i:2;s:6:"Prov 7";i:3;s:5:"Gal 6";}\',"", 79),
(\'a:4:{i:0;s:5:"Ex 32";i:1;s:7:"John 11";i:2;s:6:"Prov 8";i:3;s:5:"Eph 1";}\',"", 80),
(\'a:4:{i:0;s:5:"Ex 33";i:1;s:7:"John 12";i:2;s:6:"Prov 9";i:3;s:5:"Eph 2";}\',"", 81),
(\'a:4:{i:0;s:5:"Ex 34";i:1;s:7:"John 13";i:2;s:7:"Prov 10";i:3;s:5:"Eph 3";}\',"", 82),
(\'a:4:{i:0;s:5:"Ex 35";i:1;s:7:"John 14";i:2;s:7:"Prov 11";i:3;s:5:"Eph 4";}\',"", 83),
(\'a:4:{i:0;s:5:"Ex 36";i:1;s:7:"John 15";i:2;s:7:"Prov 12";i:3;s:5:"Eph 5";}\',"", 84),
(\'a:4:{i:0;s:5:"Ex 37";i:1;s:7:"John 16";i:2;s:7:"Prov 13";i:3;s:5:"Eph 6";}\',"", 85),
(\'a:4:{i:0;s:5:"Ex 38";i:1;s:7:"John 17";i:2;s:7:"Prov 14";i:3;s:6:"Phil 1";}\',"", 86),
(\'a:4:{i:0;s:5:"Ex 39";i:1;s:7:"John 18";i:2;s:7:"Prov 15";i:3;s:6:"Phil 2";}\',"", 87),
(\'a:4:{i:0;s:5:"Ex 40";i:1;s:7:"John 19";i:2;s:7:"Prov 16";i:3;s:6:"Phil 3";}\',"", 88),
(\'a:4:{i:0;s:5:"Lev 1";i:1;s:7:"John 20";i:2;s:7:"Prov 17";i:3;s:6:"Phil 4";}\',"", 89),
(\'a:4:{i:0;s:7:"Lev 2-3";i:1;s:7:"John 21";i:2;s:7:"Prov 18";i:3;s:5:"Col 1";}\',"", 90),
(\'a:4:{i:0;s:5:"Lev 4";i:1;s:6:"Ps 1-2";i:2;s:7:"Prov 19";i:3;s:5:"Col 2";}\',"", 91),
(\'a:4:{i:0;s:5:"Lev 5";i:1;s:6:"Ps 3-4";i:2;s:7:"Prov 20";i:3;s:5:"Col 3";}\',"", 92),
(\'a:4:{i:0;s:5:"Lev 6";i:1;s:6:"Ps 5-6";i:2;s:7:"Prov 21";i:3;s:5:"Col 4";}\',"", 93),
(\'a:4:{i:0;s:5:"Lev 7";i:1;s:6:"Ps 7-8";i:2;s:7:"Prov 22";i:3;s:8:"1 Thes 1";}\',"", 94),
(\'a:4:{i:0;s:5:"Lev 8";i:1;s:4:"Ps 9";i:2;s:7:"Prov 23";i:3;s:8:"1 Thes 2";}\',"", 95),
(\'a:4:{i:0;s:5:"Lev 9";i:1;s:5:"Ps 10";i:2;s:7:"Prov 24";i:3;s:8:"1 Thes 3";}\',"", 96),
(\'a:4:{i:0;s:6:"Lev 10";i:1;s:8:"Ps 11-12";i:2;s:7:"Prov 25";i:3;s:8:"1 Thes 4";}\',"", 97),
(\'a:4:{i:0;s:9:"Lev 11-12";i:1;s:8:"Ps 13-14";i:2;s:7:"Prov 26";i:3;s:8:"1 Thes 5";}\',"", 98),
(\'a:4:{i:0;s:6:"Lev 13";i:1;s:8:"Ps 15-16";i:2;s:7:"Prov 27";i:3;s:8:"2 Thes 1";}\',"", 99),
(\'a:4:{i:0;s:6:"Lev 14";i:1;s:5:"Ps 17";i:2;s:7:"Prov 28";i:3;s:8:"2 Thes 2";}\',"", 100),
(\'a:4:{i:0;s:6:"Lev 15";i:1;s:5:"Ps 18";i:2;s:7:"Prov 29";i:3;s:8:"2 Thes 3";}\',"", 101),
(\'a:4:{i:0;s:6:"Lev 16";i:1;s:5:"Ps 19";i:2;s:7:"Prov 30";i:3;s:7:"1 Tim 1";}\',"", 102),
(\'a:4:{i:0;s:6:"Lev 17";i:1;s:8:"Ps 20-21";i:2;s:7:"Prov 31";i:3;s:7:"1 Tim 2";}\',"", 103),
(\'a:4:{i:0;s:6:"Lev 18";i:1;s:5:"Ps 22";i:2;s:6:"Eccl 1";i:3;s:7:"1 Tim 3";}\',"", 104),
(\'a:4:{i:0;s:6:"Lev 19";i:1;s:8:"Ps 23-24";i:2;s:6:"Eccl 2";i:3;s:7:"1 Tim 4";}\',"", 105),
(\'a:4:{i:0;s:6:"Lev 20";i:1;s:5:"Ps 25";i:2;s:6:"Eccl 3";i:3;s:7:"1 Tim 5";}\',"", 106),
(\'a:4:{i:0;s:6:"Lev 21";i:1;s:8:"Ps 26-27";i:2;s:6:"Eccl 4";i:3;s:7:"1 Tim 6";}\',"", 107),
(\'a:4:{i:0;s:6:"Lev 22";i:1;s:8:"Ps 28-29";i:2;s:6:"Eccl 5";i:3;s:7:"2 Tim 1";}\',"", 108),
(\'a:4:{i:0;s:6:"Lev 23";i:1;s:5:"Ps 30";i:2;s:6:"Eccl 6";i:3;s:7:"2 Tim 2";}\',"", 109),
(\'a:4:{i:0;s:6:"Lev 24";i:1;s:5:"Ps 31";i:2;s:6:"Eccl 7";i:3;s:7:"2 Tim 3";}\',"", 110),
(\'a:4:{i:0;s:6:"Lev 25";i:1;s:5:"Ps 32";i:2;s:6:"Eccl 8";i:3;s:7:"2 Tim 4";}\',"", 111),
(\'a:4:{i:0;s:6:"Lev 26";i:1;s:5:"Ps 33";i:2;s:6:"Eccl 9";i:3;s:7:"Titus 1";}\',"", 112),
(\'a:4:{i:0;s:6:"Lev 27";i:1;s:5:"Ps 34";i:2;s:7:"Eccl 10";i:3;s:7:"Titus 2";}\',"", 113),
(\'a:4:{i:0;s:5:"Num 1";i:1;s:5:"Ps 35";i:2;s:7:"Eccl 11";i:3;s:7:"Titus 3";}\',"", 114),
(\'a:4:{i:0;s:5:"Num 2";i:1;s:5:"Ps 36";i:2;s:7:"Eccl 12";i:3;s:5:"Phm 1";}\',"", 115),
(\'a:4:{i:0;s:5:"Num 3";i:1;s:5:"Ps 37";i:2;s:5:"Sng 1";i:3;s:5:"Heb 1";}\',"", 116),
(\'a:4:{i:0;s:5:"Num 4";i:1;s:5:"Ps 38";i:2;s:5:"Sng 2";i:3;s:5:"Heb 2";}\',"", 117),
(\'a:4:{i:0;s:5:"Num 5";i:1;s:5:"Ps 39";i:2;s:5:"Sng 3";i:3;s:5:"Heb 3";}\',"", 118),
(\'a:4:{i:0;s:5:"Num 6";i:1;s:8:"Ps 40-41";i:2;s:5:"Sng 4";i:3;s:5:"Heb 4";}\',"", 119),
(\'a:4:{i:0;s:5:"Num 7";i:1;s:8:"Ps 42-43";i:2;s:5:"Sng 5";i:3;s:5:"Heb 5";}\',"", 120),
(\'a:4:{i:0;s:5:"Num 8";i:1;s:5:"Ps 44";i:2;s:5:"Sng 6";i:3;s:5:"Heb 6";}\',"", 121),
(\'a:4:{i:0;s:5:"Num 9";i:1;s:5:"Ps 45";i:2;s:5:"Sng 7";i:3;s:5:"Heb 7";}\',"", 122),
(\'a:4:{i:0;s:6:"Num 10";i:1;s:8:"Ps 46-47";i:2;s:5:"Sng 8";i:3;s:5:"Heb 8";}\',"", 123),
(\'a:4:{i:0;s:6:"Num 11";i:1;s:5:"Ps 48";i:2;s:5:"Isa 1";i:3;s:5:"Heb 9";}\',"", 124),
(\'a:4:{i:0;s:9:"Num 12-13";i:1;s:5:"Ps 49";i:2;s:5:"Isa 2";i:3;s:6:"Heb 10";}\',"", 125),
(\'a:4:{i:0;s:6:"Num 14";i:1;s:5:"Ps 50";i:2;s:7:"Isa 3-4";i:3;s:6:"Heb 11";}\',"", 126),
(\'a:4:{i:0;s:6:"Num 15";i:1;s:5:"Ps 51";i:2;s:5:"Isa 5";i:3;s:6:"Heb 12";}\',"", 127),
(\'a:4:{i:0;s:6:"Num 16";i:1;s:8:"Ps 52-54";i:2;s:5:"Isa 6";i:3;s:6:"Heb 13";}\',"", 128),
(\'a:4:{i:0;s:9:"Num 17-18";i:1;s:5:"Ps 55";i:2;s:5:"Isa 7";i:3;s:5:"Jas 1";}\',"", 129),
(\'a:4:{i:0;s:6:"Num 19";i:1;s:8:"Ps 56-57";i:2;s:5:"Isa 8";i:3;s:5:"Jas 2";}\',"", 130),
(\'a:4:{i:0;s:6:"Num 20";i:1;s:8:"Ps 58-59";i:2;s:5:"Isa 9";i:3;s:5:"Jas 3";}\',"", 131),
(\'a:4:{i:0;s:6:"Num 21";i:1;s:8:"Ps 60-61";i:2;s:6:"Isa 10";i:3;s:5:"Jas 4";}\',"", 132),
(\'a:4:{i:0;s:6:"Num 22";i:1;s:8:"Ps 62-63";i:2;s:9:"Isa 11-12";i:3;s:5:"Jas 5";}\',"", 133),
(\'a:4:{i:0;s:6:"Num 23";i:1;s:8:"Ps 64-65";i:2;s:6:"Isa 13";i:3;s:7:"1 Pet 1";}\',"", 134),
(\'a:4:{i:0;s:6:"Num 24";i:1;s:8:"Ps 66-67";i:2;s:6:"Isa 14";i:3;s:7:"1 Pet 2";}\',"", 135),
(\'a:4:{i:0;s:6:"Num 25";i:1;s:5:"Ps 68";i:2;s:6:"Isa 15";i:3;s:7:"1 Pet 3";}\',"", 136),
(\'a:4:{i:0;s:6:"Num 26";i:1;s:5:"Ps 69";i:2;s:6:"Isa 16";i:3;s:7:"1 Pet 4";}\',"", 137),
(\'a:4:{i:0;s:6:"Num 27";i:1;s:8:"Ps 70-71";i:2;s:9:"Isa 17-18";i:3;s:7:"1 Pet 5";}\',"", 138),
(\'a:4:{i:0;s:6:"Num 28";i:1;s:5:"Ps 72";i:2;s:9:"Isa 19-20";i:3;s:7:"2 Pet 1";}\',"", 139),
(\'a:4:{i:0;s:6:"Num 29";i:1;s:5:"Ps 73";i:2;s:6:"Isa 21";i:3;s:7:"2 Pet 2";}\',"", 140),
(\'a:4:{i:0;s:6:"Num 30";i:1;s:5:"Ps 74";i:2;s:6:"Isa 22";i:3;s:7:"2 Pet 3";}\',"", 141),
(\'a:4:{i:0;s:6:"Num 31";i:1;s:8:"Ps 75-76";i:2;s:6:"Isa 23";i:3;s:6:"1 Jn 1";}\',"", 142),
(\'a:4:{i:0;s:6:"Num 32";i:1;s:5:"Ps 77";i:2;s:6:"Isa 24";i:3;s:6:"1 Jn 2";}\',"", 143),
(\'a:4:{i:0;s:6:"Num 33";i:1;s:10:"Ps 78:1-39";i:2;s:6:"Isa 25";i:3;s:6:"1 Jn 3";}\',"", 144),
(\'a:4:{i:0;s:6:"Num 34";i:1;s:11:"Ps 78:40-72";i:2;s:6:"Isa 26";i:3;s:6:"1 Jn 4";}\',"", 145),
(\'a:4:{i:0;s:6:"Num 35";i:1;s:5:"Ps 79";i:2;s:6:"Isa 27";i:3;s:6:"1 Jn 5";}\',"", 146),
(\'a:4:{i:0;s:6:"Num 36";i:1;s:5:"Ps 80";i:2;s:6:"Isa 28";i:3;s:6:"2 Jn 1";}\',"", 147),
(\'a:4:{i:0;s:6:"Deut 1";i:1;s:8:"Ps 81-82";i:2;s:6:"Isa 29";i:3;s:6:"3 Jn 1";}\',"", 148),
(\'a:4:{i:0;s:6:"Deut 2";i:1;s:8:"Ps 83-84";i:2;s:6:"Isa 30";i:3;s:6:"Jude 1";}\',"", 149),
(\'a:4:{i:0;s:6:"Deut 3";i:1;s:5:"Ps 85";i:2;s:6:"Isa 31";i:3;s:5:"Rev 1";}\',"", 150),
(\'a:4:{i:0;s:6:"Deut 4";i:1;s:8:"Ps 86-87";i:2;s:6:"Isa 32";i:3;s:5:"Rev 2";}\',"", 151),
(\'a:4:{i:0;s:6:"Deut 5";i:1;s:5:"Ps 88";i:2;s:6:"Isa 33";i:3;s:5:"Rev 3";}\',"", 152),
(\'a:4:{i:0;s:6:"Deut 6";i:1;s:5:"Ps 89";i:2;s:6:"Isa 34";i:3;s:5:"Rev 4";}\',"", 153),
(\'a:4:{i:0;s:6:"Deut 7";i:1;s:5:"Ps 90";i:2;s:6:"Isa 35";i:3;s:5:"Rev 5";}\',"", 154),
(\'a:4:{i:0;s:6:"Deut 8";i:1;s:5:"Ps 91";i:2;s:6:"Isa 36";i:3;s:5:"Rev 6";}\',"", 155),
(\'a:4:{i:0;s:6:"Deut 9";i:1;s:8:"Ps 92-93";i:2;s:6:"Isa 37";i:3;s:5:"Rev 7";}\',"", 156),
(\'a:4:{i:0;s:7:"Deut 10";i:1;s:5:"Ps 94";i:2;s:6:"Isa 38";i:3;s:5:"Rev 8";}\',"", 157),
(\'a:4:{i:0;s:7:"Deut 11";i:1;s:8:"Ps 95-96";i:2;s:6:"Isa 39";i:3;s:5:"Rev 9";}\',"", 158),
(\'a:4:{i:0;s:7:"Deut 12";i:1;s:8:"Ps 97-98";i:2;s:6:"Isa 40";i:3;s:6:"Rev 10";}\',"", 159),
(\'a:4:{i:0;s:10:"Deut 13-14";i:1;s:9:"Ps 99-101";i:2;s:6:"Isa 41";i:3;s:6:"Rev 11";}\',"", 160),
(\'a:4:{i:0;s:7:"Deut 15";i:1;s:6:"Ps 102";i:2;s:6:"Isa 42";i:3;s:6:"Rev 12";}\',"", 161),
(\'a:4:{i:0;s:7:"Deut 16";i:1;s:6:"Ps 103";i:2;s:6:"Isa 43";i:3;s:6:"Rev 13";}\',"", 162),
(\'a:4:{i:0;s:7:"Deut 17";i:1;s:6:"Ps 104";i:2;s:6:"Isa 44";i:3;s:6:"Rev 14";}\',"", 163),
(\'a:4:{i:0;s:7:"Deut 18";i:1;s:6:"Ps 105";i:2;s:6:"Isa 45";i:3;s:6:"Rev 15";}\',"", 164),
(\'a:4:{i:0;s:7:"Deut 19";i:1;s:6:"Ps 106";i:2;s:6:"Isa 46";i:3;s:6:"Rev 16";}\',"", 165),
(\'a:4:{i:0;s:7:"Deut 20";i:1;s:6:"Ps 107";i:2;s:6:"Isa 47";i:3;s:6:"Rev 17";}\',"", 166),
(\'a:4:{i:0;s:7:"Deut 21";i:1;s:10:"Ps 108-109";i:2;s:6:"Isa 48";i:3;s:6:"Rev 18";}\',"", 167),
(\'a:4:{i:0;s:7:"Deut 22";i:1;s:10:"Ps 110-111";i:2;s:6:"Isa 49";i:3;s:6:"Rev 19";}\',"", 168),
(\'a:4:{i:0;s:7:"Deut 23";i:1;s:10:"Ps 112-113";i:2;s:6:"Isa 50";i:3;s:6:"Rev 20";}\',"", 169),
(\'a:4:{i:0;s:7:"Deut 24";i:1;s:10:"Ps 114-115";i:2;s:6:"Isa 51";i:3;s:6:"Rev 21";}\',"", 170),
(\'a:4:{i:0;s:7:"Deut 25";i:1;s:6:"Ps 116";i:2;s:6:"Isa 52";i:3;s:6:"Rev 22";}\',"", 171),
(\'a:4:{i:0;s:7:"Deut 26";i:1;s:10:"Ps 117-118";i:2;s:6:"Isa 53";i:3;s:6:"Matt 1";}\',"", 172),
(\'a:4:{i:0;s:7:"Deut 27";i:1;s:11:"Ps 119:1-24";i:2;s:6:"Isa 54";i:3;s:6:"Matt 2";}\',"", 173),
(\'a:4:{i:0;s:7:"Deut 28";i:1;s:12:"Ps 119:25-48";i:2;s:6:"Isa 55";i:3;s:6:"Matt 3";}\',"", 174),
(\'a:4:{i:0;s:7:"Deut 29";i:1;s:12:"Ps 119:49-72";i:2;s:6:"Isa 56";i:3;s:6:"Matt 4";}\',"", 175),
(\'a:4:{i:0;s:7:"Deut 30";i:1;s:12:"Ps 119:73-96";i:2;s:6:"Isa 57";i:3;s:6:"Matt 5";}\',"", 176),
(\'a:4:{i:0;s:7:"Deut 31";i:1;s:13:"Ps 119:97-120";i:2;s:6:"Isa 58";i:3;s:6:"Matt 6";}\',"", 177),
(\'a:4:{i:0;s:7:"Deut 32";i:1;s:14:"Ps 119:121-144";i:2;s:6:"Isa 59";i:3;s:6:"Matt 7";}\',"", 178),
(\'a:4:{i:0;s:10:"Deut 33-34";i:1;s:14:"Ps 119:145-176";i:2;s:6:"Isa 60";i:3;s:6:"Matt 8";}\',"", 179),
(\'a:4:{i:0;s:6:"Josh 1";i:1;s:10:"Ps 120-122";i:2;s:6:"Isa 61";i:3;s:6:"Matt 9";}\',"", 180),
(\'a:4:{i:0;s:6:"Josh 2";i:1;s:10:"Ps 123-125";i:2;s:6:"Isa 62";i:3;s:7:"Matt 10";}\',"", 181),
(\'a:4:{i:0;s:6:"Josh 3";i:1;s:10:"Ps 126-128";i:2;s:6:"Isa 63";i:3;s:7:"Matt 11";}\',"", 182),
(\'a:4:{i:0;s:6:"Josh 4";i:1;s:10:"Ps 129-131";i:2;s:6:"Isa 64";i:3;s:7:"Matt 12";}\',"", 183),
(\'a:4:{i:0;s:6:"Josh 5";i:1;s:10:"Ps 132-134";i:2;s:6:"Isa 65";i:3;s:7:"Matt 13";}\',"", 184),
(\'a:4:{i:0;s:6:"Josh 6";i:1;s:10:"Ps 135-136";i:2;s:6:"Isa 66";i:3;s:7:"Matt 14";}\',"", 185),
(\'a:4:{i:0;s:6:"Josh 7";i:1;s:10:"Ps 137-138";i:2;s:5:"Jer 1";i:3;s:7:"Matt 15";}\',"", 186),
(\'a:4:{i:0;s:6:"Josh 8";i:1;s:6:"Ps 139";i:2;s:5:"Jer 2";i:3;s:7:"Matt 16";}\',"", 187),
(\'a:4:{i:0;s:6:"Josh 9";i:1;s:10:"Ps 140-141";i:2;s:5:"Jer 3";i:3;s:7:"Matt 17";}\',"", 188),
(\'a:4:{i:0;s:7:"Josh 10";i:1;s:10:"Ps 142-143";i:2;s:5:"Jer 4";i:3;s:7:"Matt 18";}\',"", 189),
(\'a:4:{i:0;s:7:"Josh 11";i:1;s:6:"Ps 144";i:2;s:5:"Jer 5";i:3;s:7:"Matt 19";}\',"", 190),
(\'a:4:{i:0;s:10:"Josh 12-13";i:1;s:6:"Ps 145";i:2;s:5:"Jer 6";i:3;s:7:"Matt 20";}\',"", 191),
(\'a:4:{i:0;s:10:"Josh 14-15";i:1;s:10:"Ps 146-147";i:2;s:5:"Jer 7";i:3;s:7:"Matt 21";}\',"", 192),
(\'a:4:{i:0;s:10:"Josh 16-17";i:1;s:6:"Ps 148";i:2;s:5:"Jer 8";i:3;s:7:"Matt 22";}\',"", 193),
(\'a:4:{i:0;s:10:"Josh 18-19";i:1;s:10:"Ps 149-150";i:2;s:5:"Jer 9";i:3;s:7:"Matt 23";}\',"", 194),
(\'a:4:{i:0;s:10:"Josh 20-21";i:1;s:6:"Acts 1";i:2;s:6:"Jer 10";i:3;s:7:"Matt 24";}\',"", 195),
(\'a:4:{i:0;s:7:"Josh 22";i:1;s:6:"Acts 2";i:2;s:6:"Jer 11";i:3;s:7:"Matt 25";}\',"", 196),
(\'a:4:{i:0;s:7:"Josh 23";i:1;s:6:"Acts 3";i:2;s:6:"Jer 12";i:3;s:7:"Matt 26";}\',"", 197),
(\'a:4:{i:0;s:7:"Josh 24";i:1;s:6:"Acts 4";i:2;s:6:"Jer 13";i:3;s:7:"Matt 27";}\',"", 198),
(\'a:4:{i:0;s:6:"Judg 1";i:1;s:6:"Acts 5";i:2;s:6:"Jer 14";i:3;s:7:"Matt 28";}\',"", 199),
(\'a:4:{i:0;s:6:"Judg 2";i:1;s:6:"Acts 6";i:2;s:6:"Jer 15";i:3;s:6:"Mark 1";}\',"", 200),
(\'a:4:{i:0;s:6:"Judg 3";i:1;s:6:"Acts 7";i:2;s:6:"Jer 16";i:3;s:6:"Mark 2";}\',"", 201),
(\'a:4:{i:0;s:6:"Judg 4";i:1;s:6:"Acts 8";i:2;s:6:"Jer 17";i:3;s:6:"Mark 3";}\',"", 202),
(\'a:4:{i:0;s:6:"Judg 5";i:1;s:6:"Acts 9";i:2;s:6:"Jer 18";i:3;s:6:"Mark 4";}\',"", 203),
(\'a:4:{i:0;s:6:"Judg 6";i:1;s:7:"Acts 10";i:2;s:6:"Jer 19";i:3;s:6:"Mark 5";}\',"", 204),
(\'a:4:{i:0;s:6:"Judg 7";i:1;s:7:"Acts 11";i:2;s:6:"Jer 20";i:3;s:6:"Mark 6";}\',"", 205),
(\'a:4:{i:0;s:6:"Judg 8";i:1;s:7:"Acts 12";i:2;s:6:"Jer 21";i:3;s:6:"Mark 7";}\',"", 206),
(\'a:4:{i:0;s:6:"Judg 9";i:1;s:7:"Acts 13";i:2;s:6:"Jer 22";i:3;s:6:"Mark 8";}\',"", 207),
(\'a:4:{i:0;s:7:"Judg 10";i:1;s:7:"Acts 14";i:2;s:6:"Jer 23";i:3;s:6:"Mark 9";}\',"", 208),
(\'a:4:{i:0;s:7:"Judg 11";i:1;s:7:"Acts 15";i:2;s:6:"Jer 24";i:3;s:7:"Mark 10";}\',"", 209),
(\'a:4:{i:0;s:7:"Judg 12";i:1;s:7:"Acts 16";i:2;s:6:"Jer 25";i:3;s:7:"Mark 11";}\',"", 210),
(\'a:4:{i:0;s:7:"Judg 13";i:1;s:7:"Acts 17";i:2;s:6:"Jer 26";i:3;s:7:"Mark 12";}\',"", 211),
(\'a:4:{i:0;s:7:"Judg 14";i:1;s:7:"Acts 18";i:2;s:6:"Jer 27";i:3;s:7:"Mark 13";}\',"", 212),
(\'a:4:{i:0;s:7:"Judg 15";i:1;s:7:"Acts 19";i:2;s:6:"Jer 28";i:3;s:7:"Mark 14";}\',"", 213),
(\'a:4:{i:0;s:7:"Judg 16";i:1;s:7:"Acts 20";i:2;s:6:"Jer 29";i:3;s:7:"Mark 15";}\',"", 214),
(\'a:4:{i:0;s:7:"Judg 17";i:1;s:7:"Acts 21";i:2;s:9:"Jer 30-31";i:3;s:7:"Mark 16";}\',"", 215),
(\'a:4:{i:0;s:7:"Judg 18";i:1;s:7:"Acts 22";i:2;s:6:"Jer 32";i:3;s:6:"Luke 1";}\',"", 216),
(\'a:4:{i:0;s:7:"Judg 19";i:1;s:7:"Acts 23";i:2;s:6:"Jer 33";i:3;s:6:"Luke 2";}\',"", 217),
(\'a:4:{i:0;s:7:"Judg 20";i:1;s:7:"Acts 24";i:2;s:6:"Jer 34";i:3;s:6:"Luke 3";}\',"", 218),
(\'a:4:{i:0;s:7:"Judg 21";i:1;s:7:"Acts 25";i:2;s:6:"Jer 35";i:3;s:6:"Luke 4";}\',"", 219),
(\'a:4:{i:0;s:6:"Ruth 1";i:1;s:7:"Acts 26";i:2;s:6:"Jer 36";i:3;s:6:"Luke 5";}\',"", 220),
(\'a:4:{i:0;s:6:"Ruth 2";i:1;s:7:"Acts 27";i:2;s:6:"Jer 37";i:3;s:6:"Luke 6";}\',"", 221),
(\'a:4:{i:0;s:8:"Ruth 3-4";i:1;s:7:"Acts 28";i:2;s:6:"Jer 38";i:3;s:6:"Luke 7";}\',"", 222),
(\'a:4:{i:0;s:7:"1 Sam 1";i:1;s:5:"Rom 1";i:2;s:6:"Jer 39";i:3;s:6:"Luke 8";}\',"", 223),
(\'a:4:{i:0;s:7:"1 Sam 2";i:1;s:5:"Rom 2";i:2;s:6:"Jer 40";i:3;s:6:"Luke 9";}\',"", 224),
(\'a:4:{i:0;s:7:"1 Sam 3";i:1;s:5:"Rom 3";i:2;s:6:"Jer 41";i:3;s:7:"Luke 10";}\',"", 225),
(\'a:4:{i:0;s:7:"1 Sam 4";i:1;s:5:"Rom 4";i:2;s:6:"Jer 42";i:3;s:7:"Luke 11";}\',"", 226),
(\'a:4:{i:0;s:9:"1 Sam 5-6";i:1;s:5:"Rom 5";i:2;s:6:"Jer 43";i:3;s:7:"Luke 12";}\',"", 227),
(\'a:4:{i:0;s:9:"1 Sam 7-8";i:1;s:5:"Rom 6";i:2;s:9:"Jer 44-45";i:3;s:7:"Luke 13";}\',"", 228),
(\'a:4:{i:0;s:7:"1 Sam 9";i:1;s:5:"Rom 7";i:2;s:6:"Jer 46";i:3;s:7:"Luke 14";}\',"", 229),
(\'a:4:{i:0;s:8:"1 Sam 10";i:1;s:5:"Rom 8";i:2;s:6:"Jer 47";i:3;s:7:"Luke 15";}\',"", 230),
(\'a:4:{i:0;s:8:"1 Sam 11";i:1;s:5:"Rom 9";i:2;s:6:"Jer 48";i:3;s:7:"Luke 16";}\',"", 231),
(\'a:4:{i:0;s:8:"1 Sam 12";i:1;s:6:"Rom 10";i:2;s:6:"Jer 49";i:3;s:7:"Luke 17";}\',"", 232),
(\'a:4:{i:0;s:8:"1 Sam 13";i:1;s:6:"Rom 11";i:2;s:6:"Jer 50";i:3;s:7:"Luke 18";}\',"", 233),
(\'a:4:{i:0;s:8:"1 Sam 14";i:1;s:6:"Rom 12";i:2;s:6:"Jer 51";i:3;s:7:"Luke 19";}\',"", 234),
(\'a:4:{i:0;s:8:"1 Sam 15";i:1;s:6:"Rom 13";i:2;s:6:"Jer 52";i:3;s:7:"Luke 20";}\',"", 235),
(\'a:4:{i:0;s:8:"1 Sam 16";i:1;s:6:"Rom 14";i:2;s:5:"Lam 1";i:3;s:7:"Luke 21";}\',"", 236),
(\'a:4:{i:0;s:8:"1 Sam 17";i:1;s:6:"Rom 15";i:2;s:5:"Lam 2";i:3;s:7:"Luke 22";}\',"", 237),
(\'a:4:{i:0;s:8:"1 Sam 18";i:1;s:6:"Rom 16";i:2;s:5:"Lam 3";i:3;s:7:"Luke 23";}\',"", 238),
(\'a:4:{i:0;s:8:"1 Sam 19";i:1;s:7:"1 Cor 1";i:2;s:5:"Lam 4";i:3;s:7:"Luke 24";}\',"", 239),
(\'a:4:{i:0;s:8:"1 Sam 20";i:1;s:7:"1 Cor 2";i:2;s:5:"Lam 5";i:3;s:6:"John 1";}\',"", 240),
(\'a:4:{i:0;s:11:"1 Sam 21-22";i:1;s:7:"1 Cor 3";i:2;s:6:"Ezek 1";i:3;s:6:"John 2";}\',"", 241),
(\'a:4:{i:0;s:8:"1 Sam 23";i:1;s:7:"1 Cor 4";i:2;s:6:"Ezek 2";i:3;s:6:"John 3";}\',"", 242),
(\'a:4:{i:0;s:8:"1 Sam 24";i:1;s:7:"1 Cor 5";i:2;s:6:"Ezek 3";i:3;s:6:"John 4";}\',"", 243),
(\'a:4:{i:0;s:8:"1 Sam 25";i:1;s:7:"1 Cor 6";i:2;s:6:"Ezek 4";i:3;s:6:"John 5";}\',"", 244),
(\'a:4:{i:0;s:8:"1 Sam 26";i:1;s:7:"1 Cor 7";i:2;s:6:"Ezek 5";i:3;s:6:"John 6";}\',"", 245),
(\'a:4:{i:0;s:8:"1 Sam 27";i:1;s:7:"1 Cor 8";i:2;s:6:"Ezek 6";i:3;s:6:"John 7";}\',"", 246),
(\'a:4:{i:0;s:8:"1 Sam 28";i:1;s:7:"1 Cor 9";i:2;s:6:"Ezek 7";i:3;s:6:"John 8";}\',"", 247),
(\'a:4:{i:0;s:11:"1 Sam 29-30";i:1;s:8:"1 Cor 10";i:2;s:6:"Ezek 8";i:3;s:6:"John 9";}\',"", 248),
(\'a:4:{i:0;s:8:"1 Sam 31";i:1;s:8:"1 Cor 11";i:2;s:6:"Ezek 9";i:3;s:7:"John 10";}\',"", 249),
(\'a:4:{i:0;s:7:"2 Sam 1";i:1;s:8:"1 Cor 12";i:2;s:7:"Ezek 10";i:3;s:7:"John 11";}\',"", 250),
(\'a:4:{i:0;s:7:"2 Sam 2";i:1;s:8:"1 Cor 13";i:2;s:7:"Ezek 11";i:3;s:7:"John 12";}\',"", 251),
(\'a:4:{i:0;s:7:"2 Sam 3";i:1;s:8:"1 Cor 14";i:2;s:7:"Ezek 12";i:3;s:7:"John 13";}\',"", 252),
(\'a:4:{i:0;s:9:"2 Sam 4-5";i:1;s:8:"1 Cor 15";i:2;s:7:"Ezek 13";i:3;s:7:"John 14";}\',"", 253),
(\'a:4:{i:0;s:7:"2 Sam 6";i:1;s:8:"1 Cor 16";i:2;s:7:"Ezek 14";i:3;s:7:"John 15";}\',"", 254),
(\'a:4:{i:0;s:7:"2 Sam 7";i:1;s:7:"2 Cor 1";i:2;s:7:"Ezek 15";i:3;s:7:"John 16";}\',"", 255),
(\'a:4:{i:0;s:9:"2 Sam 8-9";i:1;s:7:"2 Cor 2";i:2;s:7:"Ezek 16";i:3;s:7:"John 17";}\',"", 256),
(\'a:4:{i:0;s:8:"2 Sam 10";i:1;s:7:"2 Cor 3";i:2;s:7:"Ezek 17";i:3;s:7:"John 18";}\',"", 257),
(\'a:4:{i:0;s:8:"2 Sam 11";i:1;s:7:"2 Cor 4";i:2;s:7:"Ezek 18";i:3;s:7:"John 19";}\',"", 258),
(\'a:4:{i:0;s:8:"2 Sam 12";i:1;s:7:"2 Cor 5";i:2;s:7:"Ezek 19";i:3;s:7:"John 20";}\',"", 259),
(\'a:4:{i:0;s:8:"2 Sam 13";i:1;s:7:"2 Cor 6";i:2;s:7:"Ezek 20";i:3;s:7:"John 21";}\',"", 260),
(\'a:4:{i:0;s:8:"2 Sam 14";i:1;s:7:"2 Cor 7";i:2;s:7:"Ezek 21";i:3;s:6:"Ps 1-2";}\',"", 261),
(\'a:4:{i:0;s:8:"2 Sam 15";i:1;s:7:"2 Cor 8";i:2;s:7:"Ezek 22";i:3;s:6:"Ps 3-4";}\',"", 262),
(\'a:4:{i:0;s:8:"2 Sam 16";i:1;s:7:"2 Cor 9";i:2;s:7:"Ezek 23";i:3;s:6:"Ps 5-6";}\',"", 263),
(\'a:4:{i:0;s:8:"2 Sam 17";i:1;s:8:"2 Cor 10";i:2;s:7:"Ezek 24";i:3;s:6:"Ps 7-8";}\',"", 264),
(\'a:4:{i:0;s:8:"2 Sam 18";i:1;s:8:"2 Cor 11";i:2;s:7:"Ezek 25";i:3;s:4:"Ps 9";}\',"", 265),
(\'a:4:{i:0;s:8:"2 Sam 19";i:1;s:8:"2 Cor 12";i:2;s:7:"Ezek 26";i:3;s:5:"Ps 10";}\',"", 266),
(\'a:4:{i:0;s:8:"2 Sam 20";i:1;s:8:"2 Cor 13";i:2;s:7:"Ezek 27";i:3;s:8:"Ps 11-12";}\',"", 267),
(\'a:4:{i:0;s:8:"2 Sam 21";i:1;s:5:"Gal 1";i:2;s:7:"Ezek 28";i:3;s:8:"Ps 13-14";}\',"", 268),
(\'a:4:{i:0;s:8:"2 Sam 22";i:1;s:5:"Gal 2";i:2;s:7:"Ezek 29";i:3;s:8:"Ps 15-16";}\',"", 269),
(\'a:4:{i:0;s:8:"2 Sam 23";i:1;s:5:"Gal 3";i:2;s:7:"Ezek 30";i:3;s:5:"Ps 17";}\',"", 270),
(\'a:4:{i:0;s:8:"2 Sam 24";i:1;s:5:"Gal 4";i:2;s:7:"Ezek 31";i:3;s:5:"Ps 18";}\',"", 271),
(\'a:4:{i:0;s:7:"1 Kgs 1";i:1;s:5:"Gal 5";i:2;s:7:"Ezek 32";i:3;s:5:"Ps 19";}\',"", 272),
(\'a:4:{i:0;s:7:"1 Kgs 2";i:1;s:5:"Gal 6";i:2;s:7:"Ezek 33";i:3;s:8:"Ps 20-21";}\',"", 273),
(\'a:4:{i:0;s:7:"1 Kgs 3";i:1;s:5:"Eph 1";i:2;s:7:"Ezek 34";i:3;s:5:"Ps 22";}\',"", 274),
(\'a:4:{i:0;s:9:"1 Kgs 4-5";i:1;s:5:"Eph 2";i:2;s:7:"Ezek 35";i:3;s:8:"Ps 23-24";}\',"", 275),
(\'a:4:{i:0;s:7:"1 Kgs 6";i:1;s:5:"Eph 3";i:2;s:7:"Ezek 36";i:3;s:5:"Ps 25";}\',"", 276),
(\'a:4:{i:0;s:7:"1 Kgs 7";i:1;s:5:"Eph 4";i:2;s:7:"Ezek 37";i:3;s:8:"Ps 26-27";}\',"", 277),
(\'a:4:{i:0;s:7:"1 Kgs 8";i:1;s:5:"Eph 5";i:2;s:7:"Ezek 38";i:3;s:8:"Ps 28-29";}\',"", 278),
(\'a:4:{i:0;s:7:"1 Kgs 9";i:1;s:5:"Eph 6";i:2;s:7:"Ezek 39";i:3;s:5:"Ps 30";}\',"", 279),
(\'a:4:{i:0;s:8:"1 Kgs 10";i:1;s:6:"Phil 1";i:2;s:7:"Ezek 40";i:3;s:5:"Ps 31";}\',"", 280),
(\'a:4:{i:0;s:8:"1 Kgs 11";i:1;s:6:"Phil 2";i:2;s:7:"Ezek 41";i:3;s:5:"Ps 32";}\',"", 281),
(\'a:4:{i:0;s:8:"1 Kgs 12";i:1;s:6:"Phil 3";i:2;s:7:"Ezek 42";i:3;s:5:"Ps 33";}\',"", 282),
(\'a:4:{i:0;s:8:"1 Kgs 13";i:1;s:6:"Phil 4";i:2;s:7:"Ezek 43";i:3;s:5:"Ps 34";}\',"", 283),
(\'a:4:{i:0;s:8:"1 Kgs 14";i:1;s:5:"Col 1";i:2;s:7:"Ezek 44";i:3;s:5:"Ps 35";}\',"", 284),
(\'a:4:{i:0;s:8:"1 Kgs 15";i:1;s:5:"Col 2";i:2;s:7:"Ezek 45";i:3;s:5:"Ps 36";}\',"", 285),
(\'a:4:{i:0;s:8:"1 Kgs 16";i:1;s:5:"Col 3";i:2;s:7:"Ezek 46";i:3;s:5:"Ps 37";}\',"", 286),
(\'a:4:{i:0;s:8:"1 Kgs 17";i:1;s:5:"Col 4";i:2;s:7:"Ezek 47";i:3;s:5:"Ps 38";}\',"", 287),
(\'a:4:{i:0;s:8:"1 Kgs 18";i:1;s:8:"1 Thes 1";i:2;s:7:"Ezek 48";i:3;s:5:"Ps 39";}\',"", 288),
(\'a:4:{i:0;s:8:"1 Kgs 19";i:1;s:8:"1 Thes 2";i:2;s:5:"Dan 1";i:3;s:8:"Ps 40-41";}\',"", 289),
(\'a:4:{i:0;s:8:"1 Kgs 20";i:1;s:8:"1 Thes 3";i:2;s:5:"Dan 2";i:3;s:8:"Ps 42-43";}\',"", 290),
(\'a:4:{i:0;s:8:"1 Kgs 21";i:1;s:8:"1 Thes 4";i:2;s:5:"Dan 3";i:3;s:5:"Ps 44";}\',"", 291),
(\'a:4:{i:0;s:8:"1 Kgs 22";i:1;s:8:"1 Thes 5";i:2;s:5:"Dan 4";i:3;s:5:"Ps 45";}\',"", 292),
(\'a:4:{i:0;s:7:"2 Kgs 1";i:1;s:8:"2 Thes 1";i:2;s:5:"Dan 5";i:3;s:8:"Ps 46-47";}\',"", 293),
(\'a:4:{i:0;s:7:"2 Kgs 2";i:1;s:8:"2 Thes 2";i:2;s:5:"Dan 6";i:3;s:5:"Ps 48";}\',"", 294),
(\'a:4:{i:0;s:7:"2 Kgs 3";i:1;s:8:"2 Thes 3";i:2;s:5:"Dan 7";i:3;s:5:"Ps 49";}\',"", 295),
(\'a:4:{i:0;s:7:"2 Kgs 4";i:1;s:7:"1 Tim 1";i:2;s:5:"Dan 8";i:3;s:5:"Ps 50";}\',"", 296),
(\'a:4:{i:0;s:7:"2 Kgs 5";i:1;s:7:"1 Tim 2";i:2;s:5:"Dan 9";i:3;s:5:"Ps 51";}\',"", 297),
(\'a:4:{i:0;s:7:"2 Kgs 6";i:1;s:7:"1 Tim 3";i:2;s:6:"Dan 10";i:3;s:8:"Ps 52-54";}\',"", 298),
(\'a:4:{i:0;s:7:"2 Kgs 7";i:1;s:7:"1 Tim 4";i:2;s:6:"Dan 11";i:3;s:5:"Ps 55";}\',"", 299),
(\'a:4:{i:0;s:7:"2 Kgs 8";i:1;s:7:"1 Tim 5";i:2;s:6:"Dan 12";i:3;s:8:"Ps 56-57";}\',"", 300),
(\'a:4:{i:0;s:7:"2 Kgs 9";i:1;s:7:"1 Tim 6";i:2;s:5:"Hos 1";i:3;s:8:"Ps 58-59";}\',"", 301),
(\'a:4:{i:0;s:11:"2 Kgs 10-11";i:1;s:7:"2 Tim 1";i:2;s:5:"Hos 2";i:3;s:8:"Ps 60-61";}\',"", 302),
(\'a:4:{i:0;s:8:"2 Kgs 12";i:1;s:7:"2 Tim 2";i:2;s:7:"Hos 3-4";i:3;s:8:"Ps 62-63";}\',"", 303),
(\'a:4:{i:0;s:8:"2 Kgs 13";i:1;s:7:"2 Tim 3";i:2;s:7:"Hos 5-6";i:3;s:8:"Ps 64-65";}\',"", 304),
(\'a:4:{i:0;s:8:"2 Kgs 14";i:1;s:7:"2 Tim 4";i:2;s:5:"Hos 7";i:3;s:8:"Ps 66-67";}\',"", 305),
(\'a:4:{i:0;s:8:"2 Kgs 15";i:1;s:7:"Titus 1";i:2;s:5:"Hos 8";i:3;s:5:"Ps 68";}\',"", 306),
(\'a:4:{i:0;s:8:"2 Kgs 16";i:1;s:7:"Titus 2";i:2;s:5:"Hos 9";i:3;s:5:"Ps 69";}\',"", 307),
(\'a:4:{i:0;s:8:"2 Kgs 17";i:1;s:7:"Titus 3";i:2;s:6:"Hos 10";i:3;s:8:"Ps 70-71";}\',"", 308),
(\'a:4:{i:0;s:8:"2 Kgs 18";i:1;s:5:"Phm 1";i:2;s:6:"Hos 11";i:3;s:5:"Ps 72";}\',"", 309),
(\'a:4:{i:0;s:8:"2 Kgs 19";i:1;s:5:"Heb 1";i:2;s:6:"Hos 12";i:3;s:5:"Ps 73";}\',"", 310),
(\'a:4:{i:0;s:8:"2 Kgs 20";i:1;s:5:"Heb 2";i:2;s:6:"Hos 13";i:3;s:5:"Ps 74";}\',"", 311),
(\'a:4:{i:0;s:8:"2 Kgs 21";i:1;s:5:"Heb 3";i:2;s:6:"Hos 14";i:3;s:8:"Ps 75-76";}\',"", 312),
(\'a:4:{i:0;s:8:"2 Kgs 22";i:1;s:5:"Heb 4";i:2;s:6:"Joel 1";i:3;s:5:"Ps 77";}\',"", 313),
(\'a:4:{i:0;s:8:"2 Kgs 23";i:1;s:5:"Heb 5";i:2;s:6:"Joel 2";i:3;s:5:"Ps 78";}\',"", 314),
(\'a:4:{i:0;s:8:"2 Kgs 24";i:1;s:5:"Heb 6";i:2;s:6:"Joel 3";i:3;s:5:"Ps 79";}\',"", 315),
(\'a:4:{i:0;s:8:"2 Kgs 25";i:1;s:5:"Heb 7";i:2;s:6:"Amos 1";i:3;s:5:"Ps 80";}\',"", 316),
(\'a:4:{i:0;s:9:"1 Chr 1-2";i:1;s:5:"Heb 8";i:2;s:6:"Amos 2";i:3;s:8:"Ps 81-82";}\',"", 317),
(\'a:4:{i:0;s:9:"1 Chr 3-4";i:1;s:5:"Heb 9";i:2;s:6:"Amos 3";i:3;s:8:"Ps 83-84";}\',"", 318),
(\'a:4:{i:0;s:9:"1 Chr 5-6";i:1;s:6:"Heb 10";i:2;s:6:"Amos 4";i:3;s:5:"Ps 85";}\',"", 319),
(\'a:4:{i:0;s:9:"1 Chr 7-8";i:1;s:6:"Heb 11";i:2;s:6:"Amos 5";i:3;s:5:"Ps 86";}\',"", 320),
(\'a:4:{i:0;s:10:"1 Chr 9-10";i:1;s:6:"Heb 12";i:2;s:6:"Amos 6";i:3;s:8:"Ps 87-88";}\',"", 321),
(\'a:4:{i:0;s:11:"1 Chr 11-12";i:1;s:6:"Heb 13";i:2;s:6:"Amos 7";i:3;s:5:"Ps 89";}\',"", 322),
(\'a:4:{i:0;s:11:"1 Chr 13-14";i:1;s:5:"Jas 1";i:2;s:6:"Amos 8";i:3;s:5:"Ps 90";}\',"", 323),
(\'a:4:{i:0;s:8:"1 Chr 15";i:1;s:5:"Jas 2";i:2;s:6:"Amos 9";i:3;s:5:"Ps 91";}\',"", 324),
(\'a:4:{i:0;s:8:"1 Chr 16";i:1;s:5:"Jas 3";i:2;s:6:"Obad 1";i:3;s:8:"Ps 92-93";}\',"", 325),
(\'a:4:{i:0;s:8:"1 Chr 17";i:1;s:5:"Jas 4";i:2;s:7:"Jonah 1";i:3;s:5:"Ps 94";}\',"", 326),
(\'a:4:{i:0;s:8:"1 Chr 18";i:1;s:5:"Jas 5";i:2;s:7:"Jonah 2";i:3;s:8:"Ps 95-96";}\',"", 327),
(\'a:4:{i:0;s:11:"1 Chr 19-20";i:1;s:7:"1 Pet 1";i:2;s:7:"Jonah 3";i:3;s:8:"Ps 97-98";}\',"", 328),
(\'a:4:{i:0;s:8:"1 Chr 21";i:1;s:7:"1 Pet 2";i:2;s:7:"Jonah 4";i:3;s:9:"Ps 99-101";}\',"", 329),
(\'a:4:{i:0;s:8:"1 Chr 22";i:1;s:7:"1 Pet 3";i:2;s:5:"Mic 1";i:3;s:6:"Ps 102";}\',"", 330),
(\'a:4:{i:0;s:8:"1 Chr 23";i:1;s:7:"1 Pet 4";i:2;s:5:"Mic 2";i:3;s:6:"Ps 103";}\',"", 331),
(\'a:4:{i:0;s:11:"1 Chr 24-25";i:1;s:7:"1 Pet 5";i:2;s:5:"Mic 3";i:3;s:6:"Ps 104";}\',"", 332),
(\'a:4:{i:0;s:11:"1 Chr 26-27";i:1;s:7:"2 Pet 1";i:2;s:5:"Mic 4";i:3;s:6:"Ps 105";}\',"", 333),
(\'a:4:{i:0;s:8:"1 Chr 28";i:1;s:7:"2 Pet 2";i:2;s:5:"Mic 5";i:3;s:6:"Ps 106";}\',"", 334),
(\'a:4:{i:0;s:8:"1 Chr 29";i:1;s:7:"2 Pet 3";i:2;s:5:"Mic 6";i:3;s:6:"Ps 107";}\',"", 335),
(\'a:4:{i:0;s:7:"2 Chr 1";i:1;s:6:"1 Jn 1";i:2;s:5:"Mic 7";i:3;s:10:"Ps 108-109";}\',"", 336),
(\'a:4:{i:0;s:7:"2 Chr 2";i:1;s:6:"1 Jn 2";i:2;s:7:"Nahum 1";i:3;s:10:"Ps 110-111";}\',"", 337),
(\'a:4:{i:0;s:9:"2 Chr 3-4";i:1;s:6:"1 Jn 3";i:2;s:7:"Nahum 2";i:3;s:10:"Ps 112-113";}\',"", 338),
(\'a:4:{i:0;s:7:"2 Chr 5";i:1;s:6:"1 Jn 4";i:2;s:7:"Nahum 3";i:3;s:10:"Ps 114-115";}\',"", 339),
(\'a:4:{i:0;s:7:"2 Chr 6";i:1;s:6:"1 Jn 5";i:2;s:5:"Hab 1";i:3;s:6:"Ps 116";}\',"", 340),
(\'a:4:{i:0;s:7:"2 Chr 7";i:1;s:6:"2 Jn 1";i:2;s:5:"Hab 2";i:3;s:10:"Ps 117-118";}\',"", 341),
(\'a:4:{i:0;s:7:"2 Chr 8";i:1;s:6:"3 Jn 1";i:2;s:5:"Hab 3";i:3;s:11:"Ps 119:1-24";}\',"", 342),
(\'a:4:{i:0;s:7:"2 Chr 9";i:1;s:6:"Jude 1";i:2;s:6:"Zeph 1";i:3;s:12:"Ps 119:25-48";}\',"", 343),
(\'a:4:{i:0;s:8:"2 Chr 10";i:1;s:5:"Rev 1";i:2;s:6:"Zeph 2";i:3;s:12:"Ps 119:49-72";}\',"", 344),
(\'a:4:{i:0;s:11:"2 Chr 11-12";i:1;s:5:"Rev 2";i:2;s:6:"Zeph 3";i:3;s:12:"Ps 119:73-96";}\',"", 345),
(\'a:4:{i:0;s:8:"2 Chr 13";i:1;s:5:"Rev 3";i:2;s:5:"Hag 1";i:3;s:13:"Ps 119:97-120";}\',"", 346),
(\'a:4:{i:0;s:11:"2 Chr 14-15";i:1;s:5:"Rev 4";i:2;s:5:"Hag 2";i:3;s:14:"Ps 119:121-144";}\',"", 347),
(\'a:4:{i:0;s:8:"2 Chr 16";i:1;s:5:"Rev 5";i:2;s:6:"Zech 1";i:3;s:14:"Ps 119:145-176";}\',"", 348),
(\'a:4:{i:0;s:8:"2 Chr 17";i:1;s:5:"Rev 6";i:2;s:6:"Zech 2";i:3;s:10:"Ps 120-122";}\',"", 349),
(\'a:4:{i:0;s:8:"2 Chr 18";i:1;s:5:"Rev 7";i:2;s:6:"Zech 3";i:3;s:10:"Ps 123-125";}\',"", 350),
(\'a:4:{i:0;s:11:"2 Chr 19-20";i:1;s:5:"Rev 8";i:2;s:6:"Zech 4";i:3;s:10:"Ps 126-128";}\',"", 351),
(\'a:4:{i:0;s:8:"2 Chr 21";i:1;s:5:"Rev 9";i:2;s:6:"Zech 5";i:3;s:10:"Ps 129-131";}\',"", 352),
(\'a:4:{i:0;s:11:"2 Chr 22-23";i:1;s:6:"Rev 10";i:2;s:6:"Zech 6";i:3;s:10:"Ps 132-134";}\',"", 353),
(\'a:4:{i:0;s:8:"2 Chr 24";i:1;s:6:"Rev 11";i:2;s:6:"Zech 7";i:3;s:10:"Ps 135-136";}\',"", 354),
(\'a:4:{i:0;s:8:"2 Chr 25";i:1;s:6:"Rev 12";i:2;s:6:"Zech 8";i:3;s:10:"Ps 137-138";}\',"", 355),
(\'a:4:{i:0;s:8:"2 Chr 26";i:1;s:6:"Rev 13";i:2;s:6:"Zech 9";i:3;s:6:"Ps 139";}\',"", 356),
(\'a:4:{i:0;s:11:"2 Chr 27-28";i:1;s:6:"Rev 14";i:2;s:7:"Zech 10";i:3;s:10:"Ps 140-141";}\',"", 357),
(\'a:4:{i:0;s:8:"2 Chr 29";i:1;s:6:"Rev 15";i:2;s:7:"Zech 11";i:3;s:6:"Ps 142";}\',"", 358),
(\'a:4:{i:0;s:8:"2 Chr 30";i:1;s:6:"Rev 16";i:2;s:7:"Zech 12";i:3;s:6:"Ps 143";}\',"", 359),
(\'a:4:{i:0;s:8:"2 Chr 31";i:1;s:6:"Rev 17";i:2;s:7:"Zech 13";i:3;s:6:"Ps 144";}\',"", 360),
(\'a:4:{i:0;s:8:"2 Chr 32";i:1;s:6:"Rev 18";i:2;s:7:"Zech 14";i:3;s:6:"Ps 145";}\',"", 361),
(\'a:4:{i:0;s:8:"2 Chr 33";i:1;s:6:"Rev 19";i:2;s:5:"Mal 1";i:3;s:10:"Ps 146-147";}\',"", 362),
(\'a:4:{i:0;s:8:"2 Chr 34";i:1;s:6:"Rev 20";i:2;s:5:"Mal 2";i:3;s:6:"Ps 148";}\',"", 363),
(\'a:4:{i:0;s:8:"2 Chr 35";i:1;s:6:"Rev 21";i:2;s:5:"Mal 3";i:3;s:6:"Ps 149";}\',"", 364),
(\'a:4:{i:0;s:8:"2 Chr 36";i:1;s:6:"Rev 22";i:2;s:5:"Mal 4";i:3;s:6:"Ps 150";}\',"", 365);';
        $wpdb->query( $sql);
     update_option('church_admin_brp',"Murray M'Cheyne Reading Plan");
    }


church_admin_delete_backup();//get rid of backups!

$use_prefix=get_option('church_admin_use_prefix');
if(!isset( $use_prefix) )update_option('church_admin_use_prefix',TRUE);
$use_middle=get_option('church_admin_use_middle_name');
if(!isset( $use_middle) )update_option('church_admin_use_middle_name',TRUE);
//fix for v0.943
if( $church_admin_version>=0.943)  {
	//get current saved option for auto rota email
	$rota_day=get_option('church_admin_email_rota_day');
	if ( empty( $rota_day)||!church_admin_int_check( $rota_day) )
	{
		$check=wp_get_schedule( 'church_admin_cron_email_rota');
		if(!empty( $check) )
		{
			wp_clear_scheduled_hook('church_admin_cron_email_rota');
			//echo'<div class="notice notice-success inline"> Rota auto email bug cleared</div>';
		}
	}
}

	//update old smtp settings to new if necessary
	$smtp=get_option('church_admin_smtp');
	if(!empty( $smtp) )//old smtp settings
	{

		$check=get_option('church_admin_smtp_settings');
		if ( empty( $check) )//not done already
		{
			update_option('church_admin_smtp_settings',$smtp);
			delete_option('church_admin_smtp');
		}
	}

	//check for modules
	$modules=get_option('church_admin_modules');
	if ( empty( $modules) )
	{
		//church_admin_debug('INSTALL - modules is empty');
		$modules=array('Gifts'=>TRUE,'Pastoral'=>TRUE,'Support'=>TRUE,'Contact form'=>TRUE,'App'=>TRUE,'Giving'=>TRUE,'People'=>TRUE,'Sessions'=>TRUE,'Services'=>TRUE,'Attendance'=>TRUE,'Podcast'=>TRUE,'Rota'=>TRUE,'Children'=>TRUE,'Classes'=>TRUE,'Calendar'=>TRUE,'Comms'=>TRUE,'Groups'=>TRUE,'Media'=>TRUE,'Facilities'=>TRUE,'Ministries'=>TRUE,'Units'=>TRUE,'Inventory'=>TRUE);
		update_option('church_admin_modules',$modules);
		//church_admin_debug( $modules);
	}
	if(!isset( $modules['Kiosk-App']))
	{
		
		$modules['Kiosk-App']=TRUE;
		
	}
	if(!isset( $modules['ChildProtection']))
	{
		unset($modules['ChildProtection']);
		$modules['ChildProtection']=TRUE;
		
	}
	if(!isset( $modules['Support']))
	{
		unset($modules['Kiosk App']);
		$modules['Support']=TRUE;
		
	}
	if(!isset( $modules['Inventory']))
	{
		
		$modules['Inventory']=TRUE;
		
	}
	if(!isset( $modules['Pastoral']))
	{
		
		$modules['Pastoral']=TRUE;
		
	}
	if(!isset( $modules['Keydates']))
	{
		
		$modules['Keydates']=TRUE;
		
	}
	if(!isset( $modules['Automations']))
	{
		
		$modules['Automations']=TRUE;
		
	}
	//fix contact form
	if(isset( $modules['Contact form'] ) )
	{
		$contact=$modules['Contact form'];
		unset( $modules['Contact form'] );
		$modules['Contact']=$contact;
	}
	if(!isset( $modules['Contact'] ) )
	{
		$modules['Contact']=TRUE;
		
	}
	if(isset( $modules['Spiritual gifts'] ) )
	{
		
		unset( $modules['Spiritual gifts'] );
		$modules['Gifts']=TRUE;
	}
	if(!isset($modules['Gifts'])){ $modules['Gifts']=TRUE; }
    if(!isset( $modules['Units'] ) )  {$modules['Units']=FALSE;}
    if(!isset( $modules['Events'] ) )  {$modules['Events']=FALSE;}
  	if(!isset( $modules['Attendance'] ) )  {$modules['Attendance']=FALSE;}
	if(!isset( $modules['App'] ) )  {$modules['App']=TRUE;}
	if(!isset( $modules['Services'] ) )  {$modules['Services']=FALSE;}
	if(!isset( $modules['Sessions'] ) )  {$modules['Sessions']=FALSE;}
    if(!isset( $modules['Giving'] ) )  { $modules['Giving']=TRUE; }
    //now update all at once!
	update_option('church_admin_modules',$modules);
	
	//check for pagination limit
	$page=get_option('church_admin_page_limit');
	if ( empty( $page) )update_option('church_admin_page_limit',50);


	//bulksms update
	$eapi=get_option('church_admin_bulksms');
	if( $eapi=='https://community.bulksms.co.uk')update_option('church_admin_bulksms','https://community.bulksms.co.uk/eapi');
	if( $eapi=='https://bulksms.co.uk')update_option('church_admin_bulksms','https://bulksms.co.uk/eapi');
    
	
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


//change way speakers are stored for v0.5963
$sermons=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files');
if(!empty( $sermons) && OLD_CHURCH_ADMIN_VERSION <0.5963)
{

foreach ( $sermons AS $sermon)
{
	$speaker=church_admin_get_people( $sermon->speaker);
	$sql='UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET speaker="'.esc_sql( $speaker).'" WHERE file_id="'.esc_sql( $sermon->file_id).'"';

	$wpdb->query( $sql);
}

}
	
	
	
	
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




if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_facilities_bookings"') != $wpdb->prefix.'church_admin_facilities_bookings')
{

	 $sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_facilities_bookings (`event` TEXT NULL, `name` TEXT NULL,`organisation` TEXT NULL,`address` TEXT NULL, `email_address` TEXT NULL, `people_id` INT(11) NULL, `phone` TEXT NULL, `start_date` DATE NULL, `start_time` TIME NULL, `end_time` TIME NULL, `facilities_id` INT(11) NOT NULL,`recurring` TEXT NULL, `occurrences` INT(11) NULL,`duration` FLOAT (4,2), `cost` FLOAT(7,2), `admin_approved` INT(1) DEFAULT 0, ID INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY(`ID`)  )';
	 //church_admin_debug( $sql);
	 $wpdb->query( $sql);
}	 
$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_facilities_bookings');
$current=array();
foreach($results AS $row){$current[]=$row->Field;}
if(!in_array('duration',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_facilities_bookings ADD `duration` INT(11) NULL ');}
if(!in_array('cost',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_facilities_bookings ADD `cost` FLOAT(7,2) NOT NULL DEFAULT "0.00" ');}
if(!in_array('calendar_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_facilities_bookings ADD `calendar_id` INT(11) NOT NULL DEFAULT 0;');	}
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
	if(!in_array('rota_email',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people ADD `rota_email` INT(11)  NOT NULL DEFAULT "1" ');}
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




/**************************************************
*
* ministries table
*
***************************************************/
if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_ministries"') != $wpdb->prefix.'church_admin_ministries')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_ministries ( ministry TEXT,safeguarding INT(1)  NULL DEFAULT "0", parentID INT(11) NULL DEFAULT NULL,ID INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID) );';
        $wpdb->query( $sql);
        $ministries=get_option('church_admin_ministries');
        if(!empty( $ministries) )
        {
        	$values=array();
        	foreach( $ministries AS $ID=>$ministry)  {$values[]='("'.esc_sql( $ministry).'","'.esc_sql( $ID).'")';}
        	$sql='INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry,ID) VALUES '.implode(",",$values);
			$wpdb->query( $sql);
			delete_option('church_admin_ministries');
        }
        else
        {
        	$sql='INSERT INTO '.$wpdb->prefix.'church_admin_ministries (ministry,ID) VALUES ("'.esc_sql(__('Small Group Leader','church-admin')).'",1),("'.esc_sql(__('Elder','church-admin')).'",2),("'.esc_sql(__('Prayer requests send','church-admin')).'",3),("'.esc_sql(__('Bible readings send','church-admin')).'",4)';
			$wpdb->query( $sql);
        }

    }
	$current=array();
	$results =$wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_ministries');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('volunteer',$current)){$wpdb->query( 'ALTER TABLE  '.$wpdb->prefix.'church_admin_ministries ADD `volunteer` INT(1) NULL DEFAULT "0"');}
    if(!in_array('parentID',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_ministries ADD parentID INT(11) NULL DEFAULT "0"');}
	if(!in_array('childID',$current)){
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_ministries ADD childID INT(11) NULL DEFAULT "0"');
		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE childID!=0');
    	if(!empty( $results) )
    	{
    		foreach( $results AS $row)
    		{
    			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET parentID="'.(int)$row->ID.'" WHERE ID="'.(int)$row->childID.'"');
    			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_ministries SET childID="0" WHERE ID="'.(int)$row->ID.'"');
    		}
    	}
    	
	
	}
	if(!in_array('safeguarding',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_ministries ADD safeguarding INT(1) NULL DEFAULT "0"');}
/********************************************************************************
*
*   Tody uup prayer requests, bible readings 
*
*********************************************************************************/
	//If prayer requests and bible readings are ministries sort out
	$prayer_id=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql(__('Prayer requests send','church-admin')).'"');
    if(!empty( $prayer_id) )
	{
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET meta_type="prayer-requests",ID=0 WHERE ID="'.intval( $prayer_id).'" AND meta_type="ministry"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql(__('Prayer requests send','church-admin')).'"');
		
	}
	$bible_id=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql(__('Bible readings send','church-admin')).'"');
    if(!empty( $bible_id) )
	{
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET meta_type="bible-readings",ID=0 WHERE ID="'.intval( $bible_id).'" AND meta_type="ministry"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql(__('Bible readings send','church-admin')).'"');
		
	}
	$news_id=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql(__('News send','church-admin')).'"');
    if(!empty( $news_id) )
	{
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET meta_type="news-send",ID=0 WHERE ID="'.intval( $news_id).'" AND meta_type="ministry"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_ministries WHERE ministry="'.esc_sql(__('News send','church-admin')).'"');
		
	}
	
   
/**************************************************
*
* Sessions table
*
***************************************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_session"') != $wpdb->prefix.'church_admin_session')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_session ( `what` TEXT NOT NULL ,what_id INT(11) NOT NULL, `event_type` TEXT NOT NULL,`start_time` DATETIME NOT NULL , `end_time` DATETIME NOT NULL , `notes` TEXT NOT NULL , `user_id` TEXT NOT NULL , `session_id` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`session_id`) )';
        $wpdb->query( $sql);

	}
	//sessions meta table
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_session_meta"') != $wpdb->prefix.'church_admin_session_meta')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_session_meta ( `people_id` INT(11) NOT NULL, `meta_value` TEXT NULL, `session_id` INT(11) NOT NULL , `ID` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`ID`) )';
        $wpdb->query( $sql);

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
   
    $people_type=get_option('church_admin_people_type');
    if ( $people_type==array(1=>'Adult',2=>'Child',3=>'Teenager') )
    {
    	//make sure translation is set up by re-writing it!
    	$people_type=array('1'=>esc_html( __('Adult','church-admin' ) ),'2'=>esc_html( __('Child','church-admin' ) ),3=>esc_html( __('Teenager','church-admin')) );
    }
    if ( empty( $people_type) )$people_type=array('1'=>esc_html( __('Adult','church-admin' ) ),'2'=>esc_html( __('Child','church-admin')) );
	if ( empty( $people_type[3] ) )$people_type[3]=__('Teenager','church-admin');
    update_option('church_admin_people_type',$people_type);



    delete_option('church_admin_people_settings');



    //make sure addresses are stored not as an array from v0.554
    $result=$wpdb->get_results('SELECT * FROM '. $wpdb->prefix.'church_admin_household');
    if(!empty( $result) )
    {
		foreach( $result AS $row)
		{
			if(!empty( $row->address) )$address=maybe_unserialize( $row->address);
			if(!empty( $address) && is_array( $address) ){
				
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET address="'.esc_sql(implode(", ",$address) ).'" WHERE household_id="'.esc_sql( $row->household_id).'"');
			}
		}
    }
//end migrate old tables
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
//services

    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_services"') != $wpdb->prefix.'church_admin_services')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_services ( service_name TEXT, service_day INT(1),service_time TIME, venue VARCHAR(100),address TEXT,lat VARCHAR(50),lng VARCHAR(50),first_date DATE,service_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (service_id) );';
        $wpdb->query( $sql);
	$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_services (service_name,service_day,service_time,venue,address,lat,lng,first_date) VALUES ("'.esc_sql( __('Sunday Service','church-admin')).'","1","10:00","'.esc_sql( __('Main Venue','church-admin')).'","","52.0","0.0","'.esc_sql(wp_date('Y-m-d')).'")');
    }

	//sort service addresses for ver 0.5911 onwards
	$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
	if(!empty( $services) )
	foreach( $services AS $service)
	{
		if(!empty( $service->address) )
		{
			$address=maybe_unserialize( $service->address);
			if(is_array( $address) )
			{
				$address=implode(', ',array_filter( $address) );
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_services SET address="'.esc_sql( $address).'" WHERE service_id="'.esc_sql( $service->service_id).'"');
			}
		}
	}
	/*************************
	 * $wpdb->prefix.'church_admin_services'
	 *************************/
	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_services SET service_day=0 WHERE service_day<0');
	church_admin_debug('****** church_admin_services *******');
	$current=array();
	$results=$wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_services');
	
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('service_frequency',$current)){	
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_services ADD service_frequency TEXT NULL DEFAULT NULL'); 
		//adjust service day to service_frequency assume 70 ie weekly on Sunday
		$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
		if(!empty($services)){
			foreach($services AS $service){
				switch($service->service_day){
					case '8': $service_frequency ='ah';break;
					default:
					case '0': 
						$service_frequency ='70';
					break;
					case '1': $service_frequency ='71';break;
					case '2': $service_frequency ='72';break;
					case '3': $service_frequency ='73';break;
					case '4': $service_frequency ='74';break;
					case '5': $service_frequency ='75';break;
					case '6': $service_frequency ='76';break;
				}
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_services SET service_frequency="'.esc_sql($service_frequency).'" WHERE service_id = "'.(int)$service->service_id.'"');
			}
		}
	
	}
	if(!in_array('site_id',$current)){	$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_services ADD site_id INT(11) NULL DEFAULT NULL'); }
	if(!in_array('event_id',$current)){	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services ADD `event_id` INT(11) NOT NULL DEFAULT "0";');}
	if(!in_array('recurring',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services ADD `recurring` TEXT NULL DEFAULT NULL ;');	}
	if(!in_array('end_time',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services ADD `end_time` TEXT NULL DEFAULT NULL ;');	}
	if(in_array('first_meeting',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services CHANGE `first_meeting` `first_date` DATE NULL DEFAULT NULL;');	}
	if(!in_array('how_many',$current)){
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services ADD `how_many` INT(1) NOT NULL DEFAULT "1";');	
		church_admin_debug($wpdb->last_query);
	}
	if(!in_array('active',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services ADD `active` INT(1) NOT NULL DEFAULT "1";');}
	if(!in_array('max_attendance',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_services` ADD max_attendance INT(11) NULL DEFAULT NULL');}
	if(!in_array('bubbles',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_services` ADD bubbles INT(11) NULL DEFAULT NULL' );}
	if(!in_array('bubble_size',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_services` ADD bubble_size INT(11) NULL DEFAULT NULL');}

	
	
	/*************************
	 * $wpdb->prefix.'church_admin_sites'
	 *************************/
    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_sites"') != $wpdb->prefix.'church_admin_sites')
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_sites ( venue VARCHAR(100),address TEXT,lat VARCHAR(50) NULL DEFAULT NULL,lng VARCHAR(50) NULL DEFAULT NULL,site_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (site_id) );';
        $wpdb->query( $sql);
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_sites CHANGE `lat` `lat` VARCHAR(50) NULL DEFAULT NULL;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_sites CHANGE `lng` `lng` VARCHAR(50) NULL DEFAULT NULL;');
		//upgrade service table if lready established version
		//add site id to service table
		

		if(!empty( $services) )
		{
			foreach( $services AS $service)
			{
				$siteID=$wpdb->get_var('SELECT site_id FROM '.$wpdb->prefix.'church_admin_sites WHERE venue="'.esc_sql( $service->venue).'" AND address= "'.esc_sql( $service->address).'" ');
				if(!$siteID)
				{//only make unique new sites
						if(!empty( $service->service_address) )$add=maybe_unserialize( $service->service_address);
						if(!empty( $add)&&is_array( $add) )  {$add=implode(', ',$add);}else{$add='';}
						$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sites (venue,address,lat,lng,site_id)VALUES("'.esc_sql( $service->venue).'","'.esc_sql( $add).'","'.esc_sql( $service->lat).'","'.esc_sql( $service->lng).'","'.esc_sql( $service->service_id).'")');
						$siteID=$wpdb->insert_id;
				}
				//update service table row with site id
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_services SET site_id="'.esc_sql( $siteID).'" WHERE service_id="'.esc_sql( $service->service_id).'"');
			}
		}
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services DROP venue;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services DROP address;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services DROP lat;');
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_services DROP lng');

	}
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_sites');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('what_three_words',$current)){$wpdb->query( 'ALTER TABLE  '.$wpdb->prefix.'church_admin_sites ADD `what_three_words` TEXT NULL ');}
	if(!in_array('attachment_id',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_sites ADD `attachment_id` INT(11) NULL ');}
	if(!in_array('active',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_sites ADD `active` INT(1) NOT NULL DEFAULT "1";');}
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


   
/*********************************************************
*
* Rota Settings Table
*
*********************************************************/

    //install rota settings table
     if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_rota_settings"') != $wpdb->prefix.'church_admin_rota_settings')
    {
		$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_rota_settings  (rota_task TEXT NOT NULL ,rota_order INT(11),autocomplete INT(1) NULL DEFAULT "1",ministries TEXT NOT NULL, rota_id INT( 11 ) NOT NULL AUTO_INCREMENT ,PRIMARY KEY (  rota_id ) );';

		$wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_rota_settings');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('ministries',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_rota_settings ADD ministries TEXT NULL DEFAULT NULL');}
	if(!in_array('initials',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_rota_settings ADD initials INT(1) NULL DEFAULT "0"');}
	if(!in_array('calendar',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_rota_settings ADD `calendar` INT(1) NULL DEFAULT "1" ');}
	if(!in_array('mtg_type',$current)){
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'church_admin_rota_settings` ADD `mtg_type` TEXT NULL DEFAULT NULL AFTER `initials`;');
	
	}
	if(!in_array('service_id',$current)){
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_rota_settings ADD service_id TEXT AFTER `rota_order`');
		//add default services
		$services=$wpdb->get_results('SELECT service_id FROM '.$wpdb->prefix.'church_admin_services');
		if(!empty( $services) )
		{
			$ser=array();
			foreach( $services AS $service)$ser[]=$service->service_id;
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_rota_settings SET service_id="'.esc_sql(serialize( $ser) ).'"');
		}
	}
	
	//v3.6.8 make ministry_id one ID not serialized array
	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings');
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			$minIDS=maybe_unserialize( $row->ministries);
			if(is_array( $minIDS) )
			{
				$ministry_id=reset( $minIDS);
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_rota_settings SET ministries="'.(int)$ministry_id.'" WHERE rota_id="'.(int)$row->rota_id.'"' );
			}
		}
	}


/**********************************************************************
*
*
*   New rota table
*
*
***********************************************************************/
	if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_new_rota"') != $wpdb->prefix.'church_admin_new_rota')
    {
		$sql='CREATE TABLE IF NOT EXISTS  '.$wpdb->prefix.'church_admin_new_rota  (rota_date DATE,rota_task_id TEXT NOT NULL ,people_id TEXT NULL,service_id INT(11),mtg_type TEXT, rota_id INT( 11 ) NOT NULL AUTO_INCREMENT ,PRIMARY KEY (  rota_id ) );';

		$wpdb->query( $sql);
	}

	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_new_rota MODIFY people_id TEXT NULL');

	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_new_rota');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('service_time',$current)){
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_new_rota ADD service_time TEXT NULL DEFAULT NULL AFTER `mtg_type`');
		//update rota table with times of service
		$timesResults=$wpdb->get_results('SELECT service_time, service_id FROM '. $wpdb->prefix.'church_admin_services');
		if(!empty( $timesResults) )
		{
			foreach( $timesResults AS $timesRow)
			{
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_new_rota SET service_time="'.esc_sql( $timesRow->service_time).'" WHERE service_id="'.(int) $timesRow->service_id.'"AND mtg_type="service"');
			}
		}
	}
	
	//populate with current data
	$oldRotaResults=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota');
	if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_rotas"') == $wpdb->prefix.'church_admin_rotas' && empty( $oldRotaResults) )
    {
    		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rotas WHERE rota_date>=CURDATE()');

			if(!empty( $results) )
			{

				foreach( $results AS $row)
				{
					$rota_jobs=maybe_unserialize( $row->rota_jobs);
					if(!empty( $rota_jobs) )
					{
						foreach( $rota_jobs AS $rota_task_id=>$people)
						{
								$peeps=maybe_unserialize( $people);
								foreach( $peeps AS $key=>$people)
								{
									$people_id='';
									if ( empty( $people) )  {$people_id='';}
									elseif(church_admin_int_check( $people) )  {$people_id=$people;}
									else{$people_id=church_admin_get_one_id( $people);}	church_admin_update_rota_entry( $rota_task_id,$row->rota_date,$people_id,'service',$row->service_id);
								}

						}
					}

				}
			}
	}


 	//bug for non directory people sorted v1.0741
 	if( $wpdb->get_var('select data_type from information_schema.columns where table_name = "'.$wpdb->prefix.'church_admin_new_rota" and column_name = "people_id"')=='int')
	{
		$sql='ALTER TABLE `'.$wpdb->prefix.'church_admin_new_rota` CHANGE `people_id` `people_id` TEXT NULL DEFAULT NULL;';

		$wpdb->query( $sql);
	}
    //install attendance table
    $table_name = $wpdb->prefix.'church_admin_attendance';
    if( $wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE   IF NOT EXISTS  ". $table_name ."  (date DATE NOT NULL ,adults INT(11) NOT NULL,children INT(11)NOT NULL,rolling_adults INT(11) NOT NULL,rolling_children INT(11)NOT NULL,service_id INT(11), attendance_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
	$wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_attendance');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('mtg_type',$current)){
		$sql='ALTER TABLE  '.$wpdb->prefix.'church_admin_attendance ADD `mtg_type` TEXT NOT NULL AFTER `service_id`';
		$wpdb->query( $sql);
		$sql='UPDATE  '.$wpdb->prefix.'church_admin_attendance SET mtg_type="service"';
		$wpdb->query( $sql);
	}
	
	if(!in_array('service_id',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_attendance ADD service_id INT(11) DEFAULT "1"');}


	 //install attendance table
    $table_name = $wpdb->prefix.'church_admin_individual_attendance';
    if( $wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE   IF NOT EXISTS  ". $table_name ."  (date DATE NOT NULL ,people_id INT(11) NOT NULL,meeting_type TEXT, meeting_id INT(11), attendance_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
	$wpdb->query( $sql);
    }
 	/*****************************
	* install classes table
	******************************/
    $table_name = $wpdb->prefix.'church_admin_classes';
    if( $wpdb->get_var("show tables like '$table_name'") != $table_name)
    {
		$sql='CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'church_admin_classes` (  `name` text,  `description` text,  `next_start_date` date DEFAULT NULL,  `how_many` int(11) DEFAULT NULL,  `calendar` int(1) DEFAULT "1",  `class_order` int(11) DEFAULT NULL,  `class_id` int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`class_id`) ); ';
		$wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_classes');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('recurring',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD `recurring` TEXT NULL DEFAULT NULL AFTER `next_start_date`');}
	if(!in_array('location',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD `location` TEXT NULL DEFAULT NULL AFTER `recurring`');}
	if(!in_array('event_id',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD `event_id` INT(11) NULL DEFAULT NULL AFTER `recurring`');}
	if(!in_array('start_time',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD `start_time` time NOT NULL DEFAULT "00:00:00" AFTER `recurring`');}
	if(!in_array('end_time',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD `end_time` time NOT NULL DEFAULT "00:00:00" AFTER `recurring`');}
	if(!in_array('start_time',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD	`end_date` DATE NULL DEFAULT NULL AFTER `end_time`');}
	if(!in_array('leadership',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD	`leadership` TEXT NULL DEFAULT NULL AFTER `end_time`');}	
	if(!in_array('cat_id',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD	`cat_id` INT(11) NULL DEFAULT NULL AFTER `leadership`');}
	if(!in_array('message',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_classes ADD	`message` TEXT NULL DEFAULT NULL AFTER `cat_id`');}
	

	/**************************************************
	*
	* Fix leadership double serialized bug in 1.2410
	*
	**************************************************/
	$classes=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_classes');
	if(!empty( $classes) )
	{
		foreach( $classes AS $class)
		{
			$ldrs=maybe_unserialize( $class->leadership);
			if(!is_array( $ldrs) )
			{
				$ldrs=maybe_unserialize( $ldrs); //fix bug in 1.2410
				if(is_array( $ldrs) )$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_classes SET leadership="'.esc_sql(maybe_serialize( $ldrs) ).'" WHERE class_id="'.(int)$class->class_id.'"');
			}

		}

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
    
	
	/*****************************
	 * install kids work table
	******************************/
    if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_kidswork"') != $wpdb->prefix.'church_admin_kidswork')
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $wpdb->prefix.'church_admin_kidswork'." (group_name TEXT NOT NULL,  youngest DATE NOT NULL,  oldest DATE NOT NULL,department_id INT(11), id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id) );";
        $wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_kidswork');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('gender',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_kidswork ADD `gender` TEXT NULL DEFAULT NULL AFTER group_name');}
	
	
	/*********************************
	 * events, tickets and bookings
	 **********************************/
	if(OLD_CHURCH_ADMIN_VERSION && OLD_CHURCH_ADMIN_VERSION<=2.4300)
	{
		if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_events"') == $wpdb->prefix.'church_admin_events') $wpdb->query('DROP TABLE '.$wpdb->prefix.'church_admin_events');
		if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_bookings"') == $wpdb->prefix.'church_admin_bookings') $wpdb->query('DROP TABLE '.$wpdb->prefix.'church_admin_bookings');
		if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_tickets"') == $wpdb->prefix.'church_admin_tickets') $wpdb->query('DROP TABLE '.$wpdb->prefix.'church_admin_tickets');
	}

    if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_events"') != $wpdb->prefix.'church_admin_events')
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $wpdb->prefix.'church_admin_events'." (title TEXT NOT NULL,  location TEXT NOT NULL, `custom` TEXT NULL, event_date DATETIME NOT NULL, event_id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (event_id) );";
        $wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_events');
	foreach($results AS $row){$current[]=$row->Field;}

	if(!in_array('medical',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_events ADD `medical` INT(1)  NOT NULL DEFAULT "0"AFTER location');}
	if(!in_array('dietary',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_events ADD `dietary` INT(1)  NOT NULL DEFAULT "0"AFTER location');}
	if(!in_array('photo_permission',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_events ADD `photo_permission` INT(1)  NOT NULL DEFAULT "0"AFTER location');}

 	  
    if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_tickets"') != $wpdb->prefix.'church_admin_tickets')
    {
        $sql='CREATE TABLE IF NOT EXISTS '. $wpdb->prefix.'church_admin_tickets'.' (name TEXT NOT NULL,  description TEXT NOT NULL,  available_from DATE NOT NULL,available_until DATE NOT NULL, quantity INT(11), people_type_id TEXT , event_id INT(11),ticket_price FLOAT(6,2) NOT NULL, ticket_id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (ticket_id) );';
        $wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_tickets');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('custom',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_tickets ADD `custom` TEXT NULL DEFAULT NULL');}
   
    if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_bookings"') != $wpdb->prefix.'church_admin_bookings')
    {
        $sql='CREATE TABLE IF NOT EXISTS '. $wpdb->prefix.'church_admin_bookings'.' (booking_ref TEXT,event_id INT(11) NOT NULL,  household_id INT (11), people_id INT (11), first_name TEXT,last_name TEXT, ticket_type INT(11),email TEXT NULL DEFAULT NULL, booking_date DATE NULL DEFAULT NULL , ticket_id INT(11) AUTO_INCREMENT, PRIMARY KEY (ticket_id) );';
        $wpdb->query( $sql);
    }
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_bookings');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('custom',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_bookings ADD `custom` TEXT NULL AFTER booking_ref');}
	if(!in_array('medical',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_bookings ADD `medical` INT(1)  NOT NULL DEFAULT "0"');}
	if(!in_array('dietary',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_bookings ADD `dietary` INT(1)  NOT NULL DEFAULT "0"');}
	if(!in_array('phone',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_bookings ADD `phone` TEXT NULL DEFAULT NULL ');}
	if(!in_array('photo_permission',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_bookings ADD `photo_permission` INT(1)  NOT NULL DEFAULT "0"');}
	if(in_array('booking_date',$current)){
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_bookings SET booking_date = current_date() WHERE booking_date<"1000-01-01"');
		$sql='ALTER TABLE '.$wpdb->prefix.'church_admin_bookings CHANGE `booking_date` `booking_date` DATE NULL DEFAULT NULL ;';
		$wpdb->query( $sql);

	}

	if(!in_array('check_in',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_bookings ADD `check_in` DATETIME NULL DEFAULT NULL AFTER booking_date');}
    if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_event_payments"') != $wpdb->prefix.'church_admin_event_payments')
    {
        $sql='CREATE TABLE IF NOT EXISTS '. $wpdb->prefix.'church_admin_event_payments'.' (amount TEXT,txn_id TEXT,payer_email TEXT, booking_ref TEXT, payment_date DATETIME,event_id INT(11),payment_id INT(11) AUTO_INCREMENT, PRIMARY KEY (payment_id) );';
        $wpdb->query( $sql);
    }
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
		if(!in_array('household_id',$current)){	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta ADD `household_id` INT(11) NULL DEFAULT NULL AFTER `people_id`');}
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
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('options',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields ADD options TEXT NULL AFTER default_value');}
	if(!in_array('onboarding',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields ADD onboarding INT(1) NULL AFTER options');}
	if(!in_array('custom_order',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields ADD custom_order INT(11) NULL AFTER show_me');}
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_custom_fields_meta');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('gift_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta ADD gift_id INT(11) NULL AFTER household_id');}
	if(!in_array('section',$current)){
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta ADD section TEXT NULL AFTER household_id');
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
	if(!in_array('service_id',$current)){
		//v3.8.12 - integrating calendar and rota bit better
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ADD service_id INT(11) NULL AFTER cat_id');
		$services =$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
		if(!empty($services)){
			foreach($services AS $service){
				if(!empty($service->event_id)){
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_date SET service_id = "'.(int)$service->service_id.'" WHERE event_id="'.(int)$service->event_id.'"');
				}
			}
		}
	}
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
        $sql="CREATE TABLE IF NOT EXISTS ". $table_name ."  (category varchar(255)  NOT NULL DEFAULT '',  fgcolor varchar(7)  NOT NULL DEFAULT '', bgcolor varchar(7)  NOT NULL DEFAULT '', cat_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`cat_id`) )" ;
        $wpdb->query( $sql);
        $wpdb->query("INSERT INTO $table_name (category,bgcolor,cat_id) VALUES('Unused','#FFFFFF','0')");
    }
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_calendar_category');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('text_color',$current)){
		$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_calendar_category ADD text_color TEXT NULL DEFAULT NULL');
		$results = $wpdb->get_results('SELECT * FROM  '.$wpdb->prefix.'church_admin_calendar_category');
		if(!empty($results)){
			foreach($results AS $row){

				
				$text_color = church_admin_light_or_dark($row->bgcolor);
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_calendar_category SET text_color="'.esc_sql($text_color).'" WHERE cat_id="'.(int)$row->cat_id.'"');
			}
		}
	}



    //follow up funnels
    if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_funnels"')!=$wpdb->prefix.'church_admin_funnels')
    {

		if(!defined( 'DB_CHARSET') )define( 'DB_COLLATE','utf8');
		$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_funnels (action TEXT CHARACTER SET '.DB_CHARSET.' ,
	member_type_id INT( 11 )  ,department_id INT( 11 )  , funnel_order INT(11), people_type_id INT(11), funnel_id INT( 11 ) AUTO_INCREMENT PRIMARY KEY
	) CHARACTER SET '.DB_CHARSET.';';
		$wpdb->query( $sql);
    }
        //follow up people's funnels
    if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_follow_up"')!=$wpdb->prefix.'church_admin_follow_up')
    {
		$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_follow_up (funnel_id INT(11) ,member_type_id INT(11),people_id INT( 11 )  ,assign_id INT( 11 )  , assigned_date DATE,email DATE NOT NULL, completion_date DATE, id INT( 11 ) AUTO_INCREMENT PRIMARY KEY) ;';
		$wpdb->query( $sql);
    }
	/*********************
	 * $wpdb->prefix.'church_admin_smallgroup'
	 ********************/
	//install small group table
	$table_name = $wpdb->prefix.'church_admin_smallgroup';
	if( $wpdb->get_var("show tables like '$table_name'") != $table_name)
	{
		$sql="CREATE TABLE   IF NOT EXISTS ". $table_name ." (leadership TEXT NOT NULL,group_name varchar(255) NOT NULL,whenwhere TEXT NOT NULL,address TEXT, lat VARCHAR(30),lng VARCHAR(30), id int(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (id) );";
		$wpdb->query( $sql);
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_smallgroup (group_name,id)VALUES ( "'.esc_html( __('Unattached','church-admin' ) ).'", "1");');
	}
	$current = array();
	$results= $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_smallgroup');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('smallgroup_order',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD smallgroup_order INT(11) NULL DEFAULT NULL');}
	if(!in_array('contact_number',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD contact_number TEXT NULL DEFAULT NULL');}
	if(!in_array('max_attendees',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD max_attendees INT (11) NULL DEFAULT NULL');}
	if(!in_array('frequency',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD frequency TEXT NULL DEFAULT NULL');}
	if(!in_array('description',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD description TEXT NULL DEFAULT NULL');}
	if(!in_array('lat',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD lat VARCHAR(30) NULL DEFAULT NULL');}
	if(!in_array('lng',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD lng VARCHAR(30) NULL DEFAULT NULL');}
	if(!in_array('address',$current)){$wpdb->query('ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD address TEXT NULL DEFAULT NULL');}
	if(!in_array('group_day',$current)){
		$sql='ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD group_day INT(1)';
		$wpdb->query( $sql);
		$sql='ALTER TABLE  '.$wpdb->prefix.'church_admin_smallgroup ADD group_time TIME';
		$wpdb->query( $sql);
	}
	if(!in_array('oversight',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_smallgroup ADD `oversight` TEXT NULL DEFAULT NULL AFTER `group_time`');}

	if(!in_array('attachment_id',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_smallgroup ADD `attachment_id` INT(11) NULL DEFAULT NULL AFTER `group_time`');}
	if(in_array('leader',$current)){
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_smallgroup ADD leadership TEXT NULL DEFAULT NULL AFTER `group_time`');
		$results=$wpdb->get_results('SELECT leader, id FROM '.$wpdb->prefix.'church_admin_smallgroup');

		if(!empty( $results) )
		{
			foreach( $results AS $row)
			{
				$leader=maybe_unserialize( $row->leader);
	
				if(is_array( $leader) )
				{
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_smallgroup SET leadership="'.esc_sql(serialize(array(1=>$leader) )).'" WHERE id="'.(int) $row->id.'"');
				}
			}
		}
		$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_smallgroup DROP leader');
	
	}

//v 2.3000 add cell structure table
	
    if( $wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_cell_structure"') != $wpdb->prefix.'church_admin_cell_structure')
   	{
   		//church_admin_debug("***************************\r\n Small Group oversight handle");
		$wpdb->query('CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_cell_structure(  `name` text NOT NULL, `ministry_id` int(11),`parent_id` int(11),oversight TEXT, `ID` int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`ID`) )   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ');
		
		//now sort out current oversight structure
		$groups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
		if(!empty( $groups) )
		{
			$x=1;
			foreach( $groups AS $group)
			{
				//church_admin_debug("*****************");
				//church_admin_debug("Handle group - $group->group_name");
				$oversight=array();
				$leadership=maybe_unserialize( $group->leadership);
				//church_admin_debug("Leadership:\r\n".print_r( $leadership,TRUE) );
				if(!empty( $leadership) )
				{
					
					$prev_level_id=1;
					$oversight=array();
					foreach( $leadership AS $min_id=>$leaders)
					{
						//church_admin_debug("Ministry_id $min_id\r\n".print_r( $leaders,TRUE) );
						if( $min_id==1)
						{
							//smallgroupleader
							foreach( $leaders AS $key=>$people_id)church_admin_update_people_meta( $group->id,$people_id,'smallgroupleader');
						}
						else
						{
							//create row in cell_structure table if needed
							//church_admin_debug("Create row in cell structure table if needed");
							$where=array();
							foreach( $leaders AS $key=>$people_id)$where[]=' (a.people_id="'.(int)$people_id.'") ';
							//church_admin_debug(print_r( $where,TRUE) );
							if(!empty( $where) )
							{
								$sql='SELECT a.ID FROM '.$wpdb->prefix.'church_admin_people_meta a, '.$wpdb->prefix.'church_admin_cell_structure b WHERE a.meta_type="oversight" AND a.ID=b.ID AND '.implode(" OR ",$where).' GROUP BY ID LIMIT 1';	
								//church_admin_debug( $sql);
								$ID=$wpdb->get_var( $sql);
								//church_admin_debug("Retrieved $ID");
							}
											
							if ( empty( $ID) )
							{
								//church_admin_debug("Create a new entry in cell structure table");
								$sql='INSERT INTO '.$wpdb->prefix.'church_admin_cell_structure (name,ministry_id) VALUES("'.$x.'","'.intval( $min_id).'")';
								//church_admin_debug( $sql);
								$wpdb->query( $sql);
								$ID=$wpdb->insert_id;
								$x++;
							}
							foreach( $leaders AS $key=>$people_id)church_admin_update_people_meta( $ID,$people_id,'oversight');
							//update prev level with this ID as parent or smg table
										
							$oversight[]=$ID;
							//church_admin_debug("Oversight array : \r\n".print_r( $oversight,TRUE) );
							$prev_level_id=$ID;
						}
					}
					$sql='UPDATE '.$wpdb->prefix.'church_admin_smallgroup SET oversight="'.esc_sql(serialize( $oversight) ).'" WHERE id="'.(int)$group->id.'"';
					//church_admin_debug( $sql);
					$wpdb->query( $sql);
				}
				else{
					//church_admin_debug("No Leadership");
				}
				//church_admin_debug("*****************");
			}
			
		
		}
	}
	



$levels=get_option('church_admin_levels');
if(empty($levels)){$levels=array();}
if(empty($levels['ChildProtection'])){$levels['ChildProtection']='administrator';}
if(empty($levels['Prayer'])){$levels['Prayer']='administrator';}
if(empty($levels['Automations'])){$levels['Automations']='administrator';}
if(empty($levels['Pastoral'])){$levels['Pastoral']='administrator';}
if(empty($levels['Directory'])){$levels['Directory']='administrator';}
if(empty($levels['Rota'])){$levels['Rota']='administrator';};
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
update_option('church_admin_version',$church_admin_version);
update_option('church_admin_prayer_login',FALSE);
//change sex part!

$gender=get_option('church_admin_gender');
if( $gender==array(1=>'Male',0=>'Female') )
{
	//make sure translation is set up
	update_option('church_admin_gender',array(1=>esc_html( __('Male','church-admin' ) ),0=>esc_html( __('Female','church-admin') )));
}
if ( empty( $gender) )update_option('church_admin_gender',array(1=>esc_html( __('Male','church-admin' ) ),0=>esc_html( __('Female','church-admin') )));


 //update ministries from departments
 $ministries=get_option('church_admin_ministries');
if ( empty( $ministries) ) {$ministries=get_option('church_admin_departments');update_option('church_admin_ministries',$ministries);delete_option('church_admin_departments');}

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



   
/*****************************************************************************
*
*   Units
*
******************************************************************************/
    if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_units"') != $wpdb->prefix.'church_admin_units')
    {
		$sql='CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_units (`name` TEXT,`description` TEXT, `active` INT(1) NOT NULL DEFAULT "0" ,`unit_id` int(11) NOT NULL AUTO_INCREMENT ,PRIMARY KEY ( unit_id ) )';
		//church_admin_debug( $sql);
		$wpdb->query( $sql);
	} 
        if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_unit_meta"') != $wpdb->prefix.'church_admin_unit_meta')
    {
		$sql='CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_unit_meta (`name` TEXT,`description` TEXT, `active` INT(1) NOT NULL DEFAULT "0" ,`unit_id` INT(11),`subunit_id` int(11) NOT NULL AUTO_INCREMENT ,PRIMARY KEY ( subunit_id ) )';
		//church_admin_debug( $sql);
		$wpdb->query( $sql);
	} 
/***************************************
*
* App Menu
*
****************************************/


$appMenu=get_option('church-admin-app-menu');
if(OLD_CHURCH_ADMIN_VERSION<=1.4591 && (!empty( $appMenu) ))
{

    $appMenu['Address']=TRUE;
    asort( $appMenu);
    update_option('church-admin-app-menu',$appMenu);

}
	$appNewMenu=get_option('church_admin_app_new_menu');
if(OLD_CHURCH_ADMIN_VERSION<=2.3800 && (!empty( $appNewMenu) ))
{	
	if(!in_array('messages',$appNewMenu) )
	{
		$appNewMenu['messages']=array('edit'=>false,'item'=>esc_html( __('Messages','church-admin' ) ),'order'=>99,'show'=>TRUE,'type'=>'app');
		asort( $appNewMenu);
    	update_option('church_admin_app_new_menu',$appNewMenu);
	}
}

$appNewMenu=get_option('church_admin_app_new_menu');
//church_admin_debug("App menu array\r\n". print_r( $appNewMenu,TRUE) );
if(!empty( $appNewMenu['register'] ) )
{
		unset( $appNewMenu['register'] );
		asort( $appNewMenu);
        
    	update_option('church_admin_app_new_menu',$appNewMenu);
}


/***************************************
*
* App Initial Content
*
****************************************/
	$appHome=get_option('church_admin_app_home');
	if ( empty( $appHome) )update_option('church_admin_app_home','<p>'.esc_html( __('Welcome to the app for ','church-admin' ) ).home_url().'</p>');
	$appTitle=get_option('church_admin_app_menu_title');
	if ( empty( $appTitle) )update_option('church_admin_app_menu_title',home_url() );
	$appGiving=get_option('church_admin_app_giving');
	if ( empty( $appGiving) )update_option('church_admin_app_giving','<p>'.esc_html( __('This will be the giving page, when set up.','church-admin' ) ).'</p>');
	$appGroups=get_option('church_admin_app_groups');
	if ( empty( $appGroups) )update_option('church_admin_app_groups','<p>'.esc_html( __('This will be the groups page, when set up.','church-admin' ) ).'</p>');

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
/***************************************
*
* Rota Title
*
****************************************/	
	
	$rotaTitle=get_option('church-admin-rota');
	if ( empty( $rotaTitle) )update_option('church-admin-rota',__('Schedule','church-admin') );
/*********************************************************************
 *
 * Check email sending is set up and default to immediate if not
 *
 *********************************************************************/	
    
    $emailSendOption=get_option('church_admin_cron');
    if ( empty( $emailSendOption) ) update_option('church_admin_cron','immediate');
 /**************************************
 *
 * Fix MySQL dates can't be 0000-00-00
 *
 ***************************************/
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ALTER start_date DROP DEFAULT');
//$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date ALTER end_date DROP DEFAULT');    
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_attendance ALTER `date` DROP DEFAULT');
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_follow_up ALTER `assigned_date` DROP DEFAULT');
   
$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET date_of_birth = "1000-01-01" WHERE date_of_birth<"1000-01-01"');
$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_people MODIFY date_of_birth DATE NULL');
$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET date_of_birth = NULL WHERE date_of_birth<="1000-01-01"');


  
/**********************************
*
* Which filter blocks to show
*
***********************************/
$which_filters=get_option('church-admin-which-filters');
if ( empty( $which_filters) )
{
    $which_filters=array("spiritual-gifts"=>esc_html( __('Spiritual gifts','church-admin' ) ),"genders"=>esc_html( __('Genders','church-admin' ) ),'email-addresses'=>esc_html( __('Email address','church-admin' ) ),'cell'=>esc_html( __('Cell phone','church-admin' ) ),'gdpr'=>esc_html( __('Data protection confirmed','church-admin' ) ),'people_types'=>esc_html( __('People types','church-admin' ) ),'active'=>esc_html( __('Active','church-admin' ) ),'marital'=>esc_html( __('Marital Status','church-admin' ) ),'sites'=>esc_html( __('Sites','church-admin' ) ),'member_types'=>esc_html( __('Member Types','church-admin' ) ),'small-groups'=>esc_html( __('Small groups','church-admin' ) ),'ministries'=>esc_html( __('Ministries','church-admin' ) ),'birth-year'=>esc_html( __('Birth year','church-admin' ) ),'birth-month'=>esc_html( __('Birth month','church-admin' ) ),'parents'=>esc_html( __('Parents','church-admin' ) ),'addresss'=>esc_html( __('Address','church-admin') ));

    update_option('church-admin-which-filters',$which_filters);
}
if(OLD_CHURCH_ADMIN_VERSION<=2.7752)
{
	$which_filters=get_option('church-admin-which-filters');
	$which_filters['photo-permission']=__('Photo Permission','church-admin');
	update_option('church-admin-which-filters',$which_filters);
}
if(version_compare(OLD_CHURCH_ADMIN_VERSION,'3.2.1')<=0)
{
	$which_filters=get_option('church-admin-which-filters');
	$which_filters['spiritual-gifts']=__('Spiritual gifts','church-admin');
	church_admin_debug( $which_filters);
	update_option('church-admin-which-filters',$which_filters);
}
if(version_compare(OLD_CHURCH_ADMIN_VERSION,'3.8.2')<=0)
{
	$which_filters=get_option('church-admin-which-filters');
	$which_filters['age-related']=__('Age Related Groups','church-admin');
	church_admin_debug( $which_filters);
	update_option('church-admin-which-filters',$which_filters);
}
/***************************************
*
* Covid Attendance
*
****************************************/
 	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_covid_attendance"') != $wpdb->prefix.'church_admin_covid_attendance')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS '.$wpdb->prefix.'church_admin_covid_attendance ( service_id INT(11) NOT NULL, `date_id` INT(11) NOT NULL, people_id TEXT,email TEXT,phone TEXT, `covid_id` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`covid_id`) )';
        $wpdb->query( $sql);

	} 
	$current = array();
	$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_covid_attendance');
	foreach($results AS $row){$current[]=$row->Field;}

	if(!in_array('booking_date',$current)){$wpdb->query('ALTER TABLE '.$wpdb->prefix.'church_admin_covid_attendance ADD `booking_date` DATETIME NULL DEFAULT NULL');}
	if(!in_array('bubble_id',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_covid_attendance` ADD bubble_id INT(11) NULL DEFAULT NULL' );}
	if(!in_array('token',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_covid_attendance` ADD token TEXT NULL DEFAULT NULL');}
	if(!in_array('waiting_list',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_covid_attendance` ADD waiting_list INT(1) DEFAULT 0');}
	
	
	

/***************************************
*
* GIVING
*
****************************************/
 	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_giving"') != $wpdb->prefix.'church_admin_giving')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS `'.$wpdb->prefix.'church_admin_giving` (`gross_amount` float(12,2) NOT NULL,`paypal_fee` float(12,2) NOT NULL,
  `donation_date` datetime NOT NULL,`gift_aid` int(1) NOT NULL DEFAULT 0,`txn_id` text NOT NULL,`txn_type` text NOT NULL,`txn_frequency` TEXT NOT NULL,`email` text NOT NULL,`people_id` int(11) NULL,`fund` TEXT NOT NULL,`service_id` int(11) NOT NULL,`giving_id` int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`giving_id`) )';
        $wpdb->query( $sql);

	} 
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_giving_meta"') != $wpdb->prefix.'church_admin_giving_meta')
    {
		$wpdb->query('CREATE TABLE  IF NOT EXISTS `'.$wpdb->prefix.'church_admin_giving_meta` (`gross_amount` float(12,2)NULL,`paypal_fee` float(12,2)  NULL,`txn_id` text NOT NULL,fund TEXT NULL,giving_id INT(11) NULL, `amount_id` int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`amount_id`) )');
		church_ADMIN_DEBUG( $wpdb->last_query);
		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_giving');
		if(!empty( $results) )
		{
			foreach( $results AS $row)$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_giving_meta (gross_amount,paypal_fee,txn_id,fund,giving_id) VALUES("'.floatval( $row->gross_amount).'","'.floatval( $row->paypal_fee).'","'.esc_sql( $row->txn_id).'","'.esc_sql( $row->fund).'","'.(int)$row->giving_id.'")');;
		}
		//update giving to drop deprecated columns
		$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` DROP COLUMN  `gross_amount`');
		$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` DROP COLUMN  `paypal_fee`');
		$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` DROP COLUMN  `fund`');
	}

	$current = array();
	$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_giving_meta');
	foreach($results AS $row){$current[]=$row->Field;}

	if(!in_array('txn_date',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving_meta` ADD `txn_date`  DATETIME NULL DEFAULT NULL AFTER `txn_id`' );}


	$current = array();
	$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_giving');
	foreach($results AS $row){$current[]=$row->Field;}
	if(in_array('amount',$current)){
		$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` CHANGE  `amount`  `gross_amount` float(12,2) NOT NULL;');
		$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` ADD `paypal_fee` float(12,2) NOT NULL AFTER gross_amount' );
        $wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` ADD `gift_aid` INT(1) NOT NULL DEFAULT "0" AFTER paypal_fee' );
	}
	
	if(!in_array('gift_aid',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` ADD `gift_aid` int(1) NOT NULL DEFAULT 0 AFTER donation_date');}
	if(!in_array('envelope_id',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` ADD `envelope_id` TEXT NULL AFTER donation_date');}
	if(!in_array('name',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` ADD `name` TEXT AFTER people_id' );}
	if(!in_array('address',$current)){ $wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` ADD `address` TEXT AFTER name' );}
	if(!in_array('receipt_id',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_giving` ADD `receipt_id` INT(11) NULL AFTER people_id' );}
	
   
	/**************************
	 * $wpdb->prefix.'church_admin_donor_receipts'
	 *************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_donor_receipts"') != $wpdb->prefix.'church_admin_donor_receipts')
    {
        $sql = 'CREATE TABLE  IF NOT EXISTS `'.$wpdb->prefix.'church_admin_donor_receipts` (`receipt_id` INT(11) NULL,`person` TEXT NULL,`email` TEXT NULL, `fund` TEXT NOT NULL,`amount` float(12,2),`date_range` TEXT NULL ,`ID` int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`ID`) )';
        $wpdb->query( $sql);

	} 
	$funds=get_option('church_admin_giving_funds');
    if ( empty( $funds) )
    {
        $funds=array(esc_html(__('General','church-admin' ) ),esc_html(__('Building fund','church-admin') ));
        update_option('church_admin_giving_funds',$funds);
    }
	/***************************************
	*
	* PLEDGE
	*
	****************************************/
 	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_pledge"') != $wpdb->prefix.'church_admin_pledge')
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'church_admin_pledge` (`amount` float(12,2) NOT NULL,
  `pledge_year` int(4) NOT NULL,`people_id` int(11) NOT NULL,`fund` TEXT NOT NULL,`pledge_id` int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`pledge_id`) )';
        $wpdb->query( $sql);

	} 
    
	/***************************************
	*
	* CONTACT FORM
	*
	****************************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_contact_form"') != $wpdb->prefix.'church_admin_contact_form')
	{
		$wpdb->query('CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_contact_form (`name` text DEFAULT NULL,`message` text DEFAULT NULL,`subject` text DEFAULT NULL, `email` text DEFAULT NULL,`phone` text DEFAULT NULL,`post_date` datetime DEFAULT NULL,`ip` TEXT DEFAULT NULL,`date_read` datetime NULL DEFAULT NULL ,`contact_id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`contact_id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		$settings=array(
			'max_urls'=>2,
			'recipient'=>get_option('church_admin_default_from_email'),
			'pushToken'=>null,
			'spam_words'=>array('SEO','Page 1 rankings','bitcoin','shemail','lesbian','gay','Make $1000','casino','teen photos','passive income','porn','bitcoin','viagra','fuck','penis','sex','visit your website','www.yandex.ru','сайт','products on this site','business directory','<script','onClick','boobs','tits','horny','all-night','intimate photos')
		);
		update_option('church_admin_contact_form_settings',$settings); 

	}
	$current = array();
	$results = $wpdb->get_results('SHOW COLUMNS FROM '.$wpdb->prefix.'church_admin_contact_form');
	foreach($results AS $row){$current[]=$row->Field;}
	if(!in_array('url',$current)){$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'church_admin_contact_form` ADD `url` TEXT AFTER phone' );}

	/***************************************
	*
	* Twilio messaging
	*
	****************************************/
 	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_twilio_messages"') != $wpdb->prefix.'church_admin_twilio_messages')
    {    
        $sql=' CREATE TABLE  IF NOT EXISTS `'.$wpdb->prefix.'church_admin_twilio_messages` (`mobile` text NOT NULL,`direction` int(1) NOT NULL,`message` text NOT NULL,`twilio_id` text NOT NULL,`message_date` datetime NOT NULL,`people_id` int(11) NOT NULL, `message_id` int(11) AUTO_INCREMENT , PRIMARY KEY (`message_id`) )ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $wpdb->query( $sql);
    }
  
	/***************************************
	*
	* My Prayer
	*
	****************************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_my_prayer"') != $wpdb->prefix.'church_admin_my_prayer')
    {    
       $sql='CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'church_admin_my_prayer` (`title` text,`description` text,`day0` int(1) DEFAULT NULL,`day1` int(1) DEFAULT NULL,`day2` int(1) DEFAULT NULL,`day3` int(1) DEFAULT NULL,`day4` int(1) DEFAULT NULL,`day5` int(1) DEFAULT NULL,	`day6` int(1) DEFAULT NULL,`date_started` date DEFAULT NULL,`date_answered` date DEFAULT NULL,	`people_id` int(11) NOT NULL,	`prayer_id` int(11)  AUTO_INCREMENT , PRIMARY KEY (`prayer_id`) )ENGINE=InnoDB DEFAULT CHARSET=utf8;';
	   $wpdb->query( $sql);
	}
	/***************************************
	*
	* Sort stupidly large calendar and rotas
	*
	****************************************/

	$date=date('Y-m-d',strtotime("+5 years") );
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_date>"'.$date.'"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE start_date>"'.$date.'"');


	/****************************************************
	* 
	* fix cron of rota v3.5.97
	* parameter should be user_message not message
	**************************************************/
	$cron=get_option('cron');
	if(!empty( $cron) )
	{
        foreach( $cron AS $ts=>$details)
        {
            if(!empty( $details['church_admin_cron_email_rota'] ) )
            {
				foreach( $details['church_admin_cron_email_rota'] AS $key=>$cronJob)
				{
					if(!empty( $cronJob['args']['message'] ) )$user_message=$cronJob['args']['message'];
					if(!empty( $user_message) )
					{
						$cron[$ts]['church_admin_cron_email_rota'][$key]['args']['user_message']=$user_message;
						unset( $cron[$ts]['church_admin_cron_email_rota'][$key]['args']['message'] );
					}
				}
			}
		}
		update_option('cron',$cron);
	}
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');


	//Not available table
	//church_admin_debug('line 2897');

	$licence= church_admin_app_licence_check();
	if(!empty( $licence) && $licence == 'premium')
	{
		if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_not_available"') != $wpdb->prefix.'church_admin_not_available')
    	{
			$sql=' CREATE TABLE  IF NOT EXISTS `'.$wpdb->prefix.'church_admin_not_available` (`people_id` INT(11) NOT NULL,`unavailable` DATE NULL, `not_id` int(11) AUTO_INCREMENT , PRIMARY KEY (`not_id`) )ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        	$wpdb->query( $sql);
		}

	}
	
	if(version_compare(OLD_CHURCH_ADMIN_VERSION,'3.6.8','<=') )
	{
		//update meta table with notifications on for app users
		if(!empty( $licence) && $licence == 'premium')
		{
			$people=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id IS NOT NULL');
			if(!empty( $people) )
			{
				$values=array();
				foreach( $people AS $person)	$values[]='("bible-readings-notifications","'.(int)$person->people_id.'",1,"'.date('Y-m-d').'"),("prayer-requests-notifications","'.(int)$person->people_id.'",1,"'.esc_sql(wp_date('Y-m-d')).'"),("news-notifications","'.(int)$person->people_id.'",1,"'.esc_sql(wp_date('Y-m-d')).'")';
				if(!empty( $values) )$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,people_id,ID,meta_date) VALUES '.implode(",",$values) );
			}
		}
	}

	//App menu update for notifications ettings
	$chosenMenu=get_option('church_admin_app_new_menu');
	if(empty($chosenMenu)){$chosenMenu=array();}
	if ( empty( $chosenMenu['notifications'] ) )
	{
		
		$chosenMenu['notifications']=array('edit'=>false,'item'=>esc_html( __('Notification settings','church-admin' ) ),'order'=>19,'show'=>TRUE,'type'=>'app','loggedinOnly'=>0);
		update_option('church_admin_app_new_menu',$chosenMenu);			
	}
	$chosenMenu=get_option('church_admin_app_new_menu');
	if ( empty( $chosenMenu['not-available'] ) )
	{
		$chosenMenu=get_option('church_admin_app_new_menu');
		$chosenMenu['not-available']=array('edit'=>false,'item'=>esc_html( __('My availability','church-admin' ) ),'order'=>19,'show'=>TRUE,'type'=>'app','loggedinOnly'=>1);
		update_option('church_admin_app_new_menu',$chosenMenu);			
	}
	
	$appPeopleTypes=get_option('church_admin_app_people_types');
	if(!empty( $licence) && $licence == 'premium' && empty( $appPeopleTypes) )
	{
		//force app address list refresh
		delete_option('church_admin_app_address_cache');
		delete_option('church_admin_app_admin_address_cache');
		//add adult and teens by default
		$appPeopleTypes=array(1,3);
		update_option('church_admin_app_people_types',$appPeopleTypes);
	}
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

	/*********************************************************************
	 * Change church_admin_premium option to church_admin_payment_gateway
	 **********************************************************************/
	$premium=get_option('church_admin_premium');
	if(!empty($premium)){
		update_option('church_admin_payment_gateway',$premium);
		delete_option('church_admin_premium');
	}


	
	/*****************************************
	 * 	INVENTORY
	 * 
	 ****************************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_inventory"') != $wpdb->prefix.'church_admin_inventory')
    { 	
		$sql='CREATE TABLE `'.$wpdb->prefix.'church_admin_inventory` (`item` text,`description` TEXT DEFAULT NULL,`inventory_id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (inventory_id) )ENGINE=InnoDB';
		$wpdb->query($sql);
	}



	/*****************************************
	 * 	VISITATION
	 * 
	 ****************************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_pastoral_visits"') != $wpdb->prefix.'church_admin_pastoral_visits')
    { 	
		$sql='CREATE TABLE '.$wpdb->prefix.'church_admin_pastoral_visits ( `visited` INT(11) NULL DEFAULT NULL , `visitor` INT(11) NULL DEFAULT NULL , `visit_date` DATETIME NULL DEFAULT NULL , `notes` TEXT NULL , `visit_id` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`visit_id`)) ENGINE = InnoDB;
		';
		$wpdb->query($sql);
	}

	/*****************************************
	 * 	AUTOMATIONS
	 * 
	 ****************************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_automations"') != $wpdb->prefix.'church_admin_automations')
    { 	
		$sql='CREATE TABLE '.$wpdb->prefix.'church_admin_automations (`title` TEXT NULL,`custom_id` INT(11) NULL, `trigger` TEXT NULL, `value` TEXT NULL, `action` TEXT NULL, `action_data` TEXT NULL,`automation_id` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`automation_id`))';
		$wpdb->query($sql);
	}
	/*****************************************
	 * 	Child protection logging
	 * 
	 ****************************************/
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_child_protection_reporting"') != $wpdb->prefix.'church_admin_child_protection_reporting'){
		
		$sql='CREATE TABLE '.$wpdb->prefix.'church_admin_child_protection_reporting (`title` text DEFAULT NULL,`description` text DEFAULT NULL,`child` TEXT DEFAULT NULL,`incident_date` datetime DEFAULT NULL, `reporting_date` datetime DEFAULT NULL, `location` text DEFAULT NULL,`action_taken` text DEFAULT NULL,`status` int(1) NOT NULL DEFAULT "1",`log` TEXT DEFAULT NULL, `entered_by` int(11) DEFAULT NULL,`updated_by` int(11) DEFAULT NULL, `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, ID INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`ID`))';
		$wpdb->query($sql);

	}

	/*****************************************
	 * 	Service planner table
	 * 2023-09-29 started
	 ****************************************/
	/*
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_service_planner"') != $wpdb->prefix.'church_admin_service_planner')
    { 	
		$sql='CREATE TABLE '.$wpdb->prefix.'church_admin_service_planner ( `service_id` INT(11) NULL ,`element_id` INT(11) NULL , `service_date` DATE NULL , `service_order` INT(11) NULL , `element_data` TEXT NULL ,`element_duration` INT(11) NULL, `plannerID` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`songID`));
		$wpdb->query($sql);
	}

	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_songs"') != $wpdb->prefix.'church_admin_songs')
    { 	
		$sql='CREATE TABLE '.$wpdb->prefix.'church_admin_pastoral_visits ( `title` TEXT NULL , `authors` TEXT NULL , `original_key` TEXT NULL , `CCLI_song_number` TEXT NULL ,`copyright` TEXT NULL , `songID` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`songID`)) ';
		$wpdb->query($sql);
	}
	if ( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_bible_books"') != $wpdb->prefix.'church_admin_bible_books')
    { 
		$wpdb->query('CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_bible_books (`bible_id`	INT(4) NOT NULL AUTO_INCREMENT ,`title_short`	text NULL,`title_full`	TEXT NULL,`abbreviation` TEXT NULL,`category` TEXT NULL,`otnt` text NOT NULL,`chapters` INT(4) NULL, PRIMARY KEY (`bible_id`))');
		$wpdb->query('INSERT INTO book_info VALUES(1,"Genesis","The First Book of Moses Called Genesis","Gen.","Law","OT",50), (2,"Exodus","The Second Book of Moses Called Exodus","Ex.","Law","OT",40), (3,"Leviticus","The Third Book of Moses Called Leviticus","Lev.","Law","OT",27), (4,"Numbers","The Fourth Book of Moses Called Numbers","Num.","Law","OT",36), (5,"Deuteronomy","The Fifth Book of Moses Called Deuteronomy","Deut.","Law","OT",34), (6,"Joshua","The Book of Joshua","Josh.","Old Testament Narrative","OT",24), (7,"Judges","The Book of Judges","Judg.","Old Testament Narrative","OT",21), (8,"Ruth","The Book of Ruth","Ruth","Old Testament Narrative","OT",4), (9,"1 Samuel","The First Book of Samuel","1 Sam.","Old Testament Narrative","OT",31), (10,"2 Samuel","The Second Book of Samuel","2 Sam.","Old Testament Narrative","OT",24), (11,"1 Kings","The First Book of Kings","1 Kings","Old Testament Narrative","OT",22), (12,"2 Kings","The Second Book of Kings","2 Kings","Old Testament Narrative","OT",25), (13,"1 Chronicles","The First Book of Chronicles","1 Chron.","Old Testament Narrative","OT",29), (14,"2 Chronicles","The Second Book of Chronicles","2 Chron.","Old Testament Narrative","OT",36), (15,"Ezra","The Book of Ezra","Ezra","Old Testament Narrative","OT",10), (16,"Nehemiah","The Book of Nehemiah","Neh.","Old Testament Narrative","OT",13), (17,"Esther","The Book of Esther","Est.","Old Testament Narrative","OT",10), (18,"Job","The Book of Job","Job","Wisdom Literature","OT",42), (19,"Psalms","The Book of Psalms","Ps.","Wisdom Literature","OT",150), (20,"Proverbs","The Book of Proverbs","Prov.","Wisdom Literature","OT",31), (21,"Ecclesiastes","The Book of Ecclesiastes","Eccles.","Wisdom Literature","OT",12), (22,"Song of Solomon","Song of Solomon","Song","Wisdom Literature","OT",8), (23,"Isaiah","The Book of Isaiah","Isa.","Major Prophets","OT",66), (24,"Jeremiah","The Book of Jeremiah","Jer.","Major Prophets","OT",52), (25,"Lamentations","The Book of Lamentations","Lam.","Major Prophets","OT",5), (26,"Ezekiel","The Book of Ezekiel","Ezek.","Major Prophets","OT",48), (27,"Daniel","The Book of Daniel","Dan.","Major Prophets","OT",12), (28,"Hosea","The Book of Hosea","Hos.","Minor Prophets","OT",14), (29,"Joel","The Book of Joel","Joel","Minor Prophets","OT",3), (30,"Amos","The Book of Amos","Amos","Minor Prophets","OT",9), (31,"Obadiah","The Book of Obadiah","Obad.","Minor Prophets","OT",1), (32,"Jonah","The Book of Jonah","Jonah","Minor Prophets","OT",4), (33,"Micah","The Book of Micah","Mic.","Minor Prophets","OT",7), (34,"Nahum","The Book of Nahum","Nah.","Minor Prophets","OT",3), (35,"Habakkuk","The Book of Habakkuk","Hab.","Minor Prophets","OT",3), (36,"Zephaniah","The Book of Zephaniah","Zeph.","Minor Prophets","OT",3), (37,"Haggai","The Book of Haggai","Hag.","Minor Prophets","OT",2), (38,"Zechariah","The Book of Zechariah","Zech.","Minor Prophets","OT",14), (39,"Malachi","The Book of Malachi","Mal.","Minor Prophets","OT",4), (40,"Matthew","The Gospel According to Matthew","Matt.","New Testament Narrative","NT",28), (41,"Mark","The Gospel According to Mark","Mark","New Testament Narrative","NT",16), (42,"Luke","The Gospel According to Luke","Luke","New Testament Narrative","NT",24), (43,"John","The Gospel According to John","John","New Testament Narrative","NT",21), (44,"Acts","The Acts of the Apostles","Acts","New Testament Narrative","NT",28), (45,"Romans","The Epistle of Paul to the Romans","Rom.","Pauline Epistles","NT",16), (46,"1 Corinthians","The First Epistle of Paul to the Corinthians","1 Cor.","Pauline Epistles","NT",16), (47,"2 Corinthians","The Second Epistle of Paul to the Corinthians","2 Cor.","Pauline Epistles","NT",13), (48,"Galatians","The Epistle of Paul to the Galatians","Gal.","Pauline Epistles","NT",6), (49,"Ephesians","The Epistle of Paul to the Ephesians","Eph.","Pauline Epistles","NT",6), (50,"Philippians","The Epistle of Paul to the Philippians","Phil.","Pauline Epistles","NT",4), (51,"Colossians","The Epistle of Paul to the Colossians","Col.","Pauline Epistles","NT",4), (52,"1 Thessalonians","The First Epistle of Paul to the Thessalonians","1 Thess.","Pauline Epistles","NT",5), (53,"2 Thessalonians","The Second Epistle of Paul to the Thessalonians","2 Thess.","Pauline Epistles","NT",3), (54,"1 Timothy","The First Epistle of Paul to Timothy","1 Tim.","Pauline Epistles","NT",6), (55,"2 Timothy","The Second Epistle of Paul to Timothy","2 Tim.","Pauline Epistles","NT",4), (56,"Titus","The Epistle of Paul to the Titus","Titus","Pauline Epistles","NT",3), (57,"Philemon","The Epistle of Paul to the Philemon","Philem.","Pauline Epistles","NT",1), (58,"Hebrews","The Epistle to the Hebrews","Heb.","General Epistles","NT",13), (59,"James","The General Epistle of James","James","General Epistles","NT",5), (60,"1 Peter","The First Epistle of Peter","1 Pet.","General Epistles","NT",5), (61,"2 Peter","The Second Epistle of Peter","2 Pet.","General Epistles","NT",3), (62,"1 John","The First Epistle of John","1 John","General Epistles","NT",5), (63,"2 John","The Second Epistle of John","2 John","General Epistles","NT",1), (64,"3 John","The Third Epistle of John","3 John","General Epistles","NT",1), (65,"Jude","The Epistle of Jude","Jude","General Epistles","NT",1), (66,"Revelation","The Book of Revelation","Rev.","Apocalyptic Epistle","NT",22)')
	}
	*/
	/*****************************************
	 * 	END Service planner tables
	 * 
	 ****************************************/

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

	//handle uninstall.php
	$uninstall = get_option('church_admin_delete_data_on_uninstall');
	if(empty($uninstall) && file_exists(plugin_dir_path(dirname(__FILE__) ).'/uninstall.php')){
		rename(plugin_dir_path(dirname(__FILE__) ).'/uninstall.php',plugin_dir_path(dirname(__FILE__) ).'/dont_uninstall.php');
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
	



	church_admin_debug("Install function finished ".date("Y-m-d H:i:s") );
}//end of install function
