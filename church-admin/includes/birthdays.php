<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function church_admin_frontend_birthdays( $member_type_id=0,$people_type_id=0, $deltadays=31,$showAge=FALSE,$email=FALSE,$phone=FALSE)
{
	$out='';

	global $wpdb;
	//$wpdb->show_errors;
	if(is_admin()){
		$out.='<h2>'.esc_html(__('Birthdays','church-admin')).'</h2>';	
		
	}
	$out.='<div class="church-admin-birthdays">';
    $membsql=array();
	if( $member_type_id!=0)
	{
		$memb=explode(',',$member_type_id);

      foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='member_type_id='.esc_sql($value);}

      if(!empty( $membsql) ) {$memb_sql=' ('.implode(' || ',$membsql).')';}
	}
	if( $people_type_id!=0)
    {
	   $peoplesql=array();
	   $people=explode(',',$people_type_id);

      foreach( $people AS $key=>$value)  {if(church_admin_int_check( $value) )  $peoplesql[]='people_type_id='.esc_sql($value);}

      if(!empty( $peoplesql) ) {$people_sql=' ('.implode(' OR ',$peoplesql).')';}
    }
	
	
	
	$today =wp_date('z');
	$max = $today + $deltadays;

	//check for no of days in year
	$year = date('Y');
	if((0 == $year % 4) & (0 != $year % 100) | (0 == $year % 400))
	{
		$days=366;
	}
	else
	{
		$days = 365;
	}

	if($max<$days)
	{
		$dayNoSQL = 'DAYOFYEAR(date_of_birth) BETWEEN '.(int)$today.' AND '.(int)$max;
	}
	else
	{
		$max=$max-$days;
		$dayNoSQL= '(DAYOFYEAR(date_of_birth) BETWEEN '.(int)$today.' AND '.$days.') OR (DAYOFYEAR(date_of_birth) BETWEEN 0 AND '.$max.')';
	}



	 $sql='SELECT * FROM  '.$wpdb->prefix.'church_admin_people WHERE 1=1 ';
	 if(!is_admin()) $sql.=' AND show_me=1 AND  gdpr_reason!="" ';
	 $sql.=' AND ('.$dayNoSQL.')';
    
	if(!empty( $memb_sql) ) $sql.=' AND '.$memb_sql;
	if(!empty( $people_sql) ) $sql.=' AND '.$people_sql;
	//$sql.=' ORDER BY  DAYOFYEAR(date_of_birth) ASC, MONTH(date_of_birth) ';

	$sql.=' ORDER BY CONCAT(SUBSTR(`date_of_birth`,6) < SUBSTR(CURDATE(),6), SUBSTR(`date_of_birth`,6))';
 
	
    $people_results=$wpdb->get_results( $sql);

	if(!empty( $people_results) )
	{

		$out .= '<p><strong>'.esc_html(sprintf( __('Birthdays within the next %1$s  days','church-admin' ) , $deltadays) ).':</strong></p>';

		$out .= '<table class="table table-bordered table-striped widefat">';

		$out.='<thead><tr><th><strong>'.esc_html( __('Name','church-admin' ) ).'</strong></th><th>'.esc_html( __('Birthday','church-admin' ) ).'</th>';
		if(!empty($email))$out.='<th>'.esc_html( __('Email','church-admin' ) ).'</th>';
		if(!empty($phone))$out.='<th>'.esc_html( __('Cell','church-admin' ) ).'</th>';
		$out.='</tr></thead><tbody>';

		foreach( $people_results AS $people)

		{

			if(!empty( $people->prefix) )  { $prefix=$people->prefix.' '; } else {	$prefix='';	}

			$name=$people->first_name.' '.$prefix.$people->last_name;
            if(( $showAge) )  {$format ='jS M Y';}else{$format ='jS M ';}
			$birthday = mysql2date( $format,$people->date_of_birth);
			$birthyear = mysql2date("Y",$people->date_of_birth);
			$currentyear = date("Y");
			$age = $currentyear - $birthyear;
			$out.='<tr><td class="ca-names">'.esc_html( $name).'</td><td>'.esc_html( $birthday);
			if(!empty( $showAge) )$out.=', '.esc_html($age).' '.esc_html( __("years","church-admin"));
			$out.='</td>';
			if(!empty($email))
			{
				$out.='<td>';
				if(!empty($people->email)){
					$out.=antispambot($people->email);
				}
				else{
					$out.='&nbsp;';
				}
				$out.='</td>';
			}
			if(!empty($phone))
			{
				$out.='<td>';
				if(!empty($people->e164cell)){
					$out.='<a href="'.esc_url('tel:'.$people->e164cell).'">'.esc_html($people->mobile).'</a>';
				}
				else{
					$out.='&nbsp;';
				}
				$out.='</td>';
			}
			$out.='</tr>';

		}

		$out.='</tbody></table>';
		$out.="\r\n";
	}
	else{
		$out.='<p>'.esc_html( __('No upcoming birthdays','church-admin'));
	}

	$out.='</div>';
	return $out;

}

function church_admin_frontend_anniversaries( $member_type_id=0,$people_type_id=0, $deltadays=31,$showAge=FALSE,$email=FALSE,$phone=FALSE)
{
	$out='';
	
	global $wpdb;
	//$wpdb->show_errors;
	if(is_admin()){
		$out.='<h2>'.esc_html(__('Anniversaries','church-admin')).'</h2>';	
	}
	$out.='<div class="church-admin-anniversaries">';
    $membsql=array();
	if( $member_type_id!=0)
	{
		$memb=explode(',',$member_type_id);

      foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.esc_sql($value);}

      if(!empty( $membsql) ) {$memb_sql=' ('.implode(' || ',$membsql).')';}
	}
	if( $people_type_id!=0)
    {
	   $peoplesql=array();
	   $people=explode(',',$people_type_id);

      foreach( $people AS $key=>$value)  {if(church_admin_int_check( $value) )  $peoplesql[]='a.people_type_id='.esc_sql($value);}

      if(!empty( $peoplesql) ) {$people_sql=' ('.implode(' OR ',$peoplesql).')';}
    }
	
	
	
	$today =wp_date('z');
	$max = $today + $deltadays;

	//check for no of days in year
	$year = date('Y');
	if((0 == $year % 4) & (0 != $year % 100) | (0 == $year % 400))
	{
		$days=366;
	}
	else
	{
		$days = 365;
	}

	if($max<$days)
	{
		$dayNoSQL = 'DAYOFYEAR(b.wedding_anniversary) BETWEEN '.(int)$today.' AND '.(int)$max;
	}
	else
	{
		$max=$max-$days;
		$dayNoSQL= '(DAYOFYEAR(b.wedding_anniversary) BETWEEN '.(int)$today.' AND '.$days.') OR (DAYOFYEAR(b.wedding_anniversary) BETWEEN 0 AND '.$max.')';
	}



	 $sql='SELECT a.*,b.*,DAYOFYEAR(b.wedding_anniversary) AS dayofyear FROM  '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id = b.household_id AND a.head_of_household=1';
	 if(!is_admin()) $sql.=' AND show_me=1 AND  gdpr_reason!="" ';
	 $sql.=' AND ('.$dayNoSQL.')';
    
	if(!empty( $memb_sql) ) $sql.=' AND '.$memb_sql;
	if(!empty( $people_sql) ) $sql.=' AND '.$people_sql;
	

	$sql.=' ORDER BY DAYOFYEAR(b.wedding_anniversary),a.last_name ASC ';

	
    $household_results=$wpdb->get_results( $sql);

	if(!empty( $household_results) )
	{

		$out .= '<p><strong>'.esc_html(sprintf( __('Wedding Anniversaries within the next %1$s  days','church-admin' ) , $deltadays) ).':</strong></p>';

		$out .= '<table class="table table-bordered table-striped widefat">';

		$out.='<thead><tr><th>'.esc_html( __('Name','church-admin' ) ).'</strong></th><th>'.esc_html( __('Wedding date','church-admin' ) ).'</th>';
		if(!empty($email))$out.='<th>'.esc_html( __('Email','church-admin' ) ).'</th>';
		if(!empty($phone))$out.='<th>'.esc_html( __('Cell','church-admin' ) ).'</th>';
		$out.='</tr></thead><tbody>';
		$outputrows=array();//we need to arrange wedding anniveraries in order of day
		foreach( $household_results AS $household)
		{
			$people_type_id = $household->people_type_id;
			$adults = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id = "'.(int)$household->household_id.'" AND people_type_id="'.(int)$people_type_id.'"');
			if(empty($adults)){continue;}
			$names = $first_names = $last_names=array();

			foreach($adults AS $adult)
			{
				$first_names[] = $adult->first_name;
				if(!in_array($adult->last_name,$last_names)){$last_names[] = $adult->last_name;}
				$names[]=church_admin_formatted_name($adult);
			}
			//handle the fact that some couples have different last names (cultural not ethical BTW)
			if(count($last_names)==1){
				$name = implode (' & ',$first_names).' '.$last_names[0];
			}
			else
			{
				$name = implode (' & ',$names);
			}

            if(( $showAge) )  {$format ='jS M Y';}else{$format ='jS M ';}
			$birthday = mysql2date( $format,$household->wedding_anniversary);
			$weddingyear = mysql2date("Y",$household->wedding_anniversary);
			$currentyear = date("Y");
			$age = $currentyear - $weddingyear;

			

			$outputrows[$household->dayofyear][$household->household_id]='<tr><td class="ca-names">'.esc_html( $name).'</td><td>'.esc_html( $birthday);
			if(!empty( $showAge) ){
				$outputrows[$household->dayofyear][$household->household_id].=', '.esc_html($age).' '.esc_html( __("years","church-admin"));
			}
			$outputrows[$household->dayofyear][$household->household_id].='</td>';
			if(!empty($email))
			{
				$outputrows[$household->dayofyear][$household->household_id].='<td>';
				if(!empty($household->email)){
					$outputrows[$household->dayofyear][$household->household_id].=antispambot($household->email);
				}
				else{
					$outputrows[$household->dayofyear][$household->household_id].='&nbsp;';
				}
				$out.='</td>';
			}
			if(!empty($phone))
			{
				$outputrows[$household->dayofyear][$household->household_id].='<td>';
				if(!empty($household->e164cell)){
					$outputrows[$household->dayofyear][$household->household_id].='<a href="'.esc_url('tel:'.$household->e164cell).'">'.esc_html($household->mobile).'</a>';
				}
				else{
					$outputrows[$household->dayofyear][$household->household_id].='&nbsp;';
				}
				$outputrows[$household->dayofyear][$household->household_id].='</td>';
			}
			$outputrows[$household->dayofyear][$household->household_id].='</tr>';

		}
		
		//Now we have to output the $outputrows starting from today's day number to end of year, then start of year to yesterday
		for($x = $today; $x <= 366; $x++){
			if(!empty($outputrows[$x])){
				$out.= implode( '', $outputrows[$x] );
			}
		}
		$yesterday = $today - 1;
		if($yesterday>0)
		{
			for($x=1; $x<=$yesterday; $x++){
				if(!empty($outputrows[$x])){
					$out.= implode( '', $outputrows[$x] );
				}
			}
		}


		$out.='</tbody></table>';
		$out.="\r\n";
	}
	else{
		$out.='<p>'.esc_html( __('No upcoming wedding anniversaries','church-admin'));
	}

	$out.='</div>';
	return $out;

}



 /**
     *
     * Set up Individual Birthday Email 
     *
     * @author  Andy Moyle
     * @param    null
     * @return
     * @version  0.1
     *
     */
function church_admin_happy_birthday_email_setup(){
	echo'<h2>'.esc_html(__('Daily emails to people celebrating their birthdays that day','church-admin')).'</h2>';
	echo'<p>'.esc_html(__('Respects communications preferences','church-admin')).'</p>';


	


	$member_types = church_admin_member_types_array();
	$happy_birthday=get_option('church_admin_happy_birthday_template');
	
	$key_dates_email=get_option('church_admin_key_dates_emails');
	if(!empty($_POST['save-happy-birthday'])){
		$from_name = !empty($_POST['happy_birthday_from_name']) ? church_admin_sanitize($_POST['happy_birthday_from_name']): get_option('church_admin_default_from_name');
		$from_email = !empty($_POST['happy_birthday_from_email']) ? church_admin_sanitize($_POST['happy_birthday_from_email']): get_option('church_admin_default_from_email');
		$reply_name = !empty($_POST['happy_birthday_reply_name']) ? church_admin_sanitize($_POST['happy_birthday_reply_name']): get_option('church_admin_default_from_name');
		$reply_email = !empty($_POST['happy_birthday_reply_email']) ? church_admin_sanitize($_POST['happy_birthday_reply_email']): get_option('church_admin_default_from_email');
		$happy_birthday_subject = !empty($_POST['happy_birthday_subject'])?wp_kses_post(stripslashes($_POST['happy_birthday_subject'])):null;
		$happy_birthday_message = !empty($_POST['happy_birthday_message'])?wp_kses_post(wpautop(stripslashes($_POST['happy_birthday_message'])) ):null;
		$parent_message = !empty($_POST['parent_message'])?wp_kses_post(wpautop(stripslashes($_POST['parent_message']))):null;
		if(!empty($happy_birthday_message)){
			update_option('church_admin_happy_birthday_template',array('from_name'=>$from_name,'from_email'=>$from_email,'reply_name'=>$reply_name,'reply_email'=>$reply_email,'parent_message'=>$parent_message,'message'=>$happy_birthday_message,'subject'=>$happy_birthday_subject));
		}
		$first_run = strtotime("6am Tomorrow UTC");
		echo 'Tomorrow is '.date('Y-m-d H:i:s',$first_run);
		$key_dates_email = array();
		if(!empty($_POST['happy_birthday'])){
			$args=array(church_admin_sanitize($_POST['happy_birthday_mt']));
			update_option('church_admin_happy_birthday_arguments',$args,false);
			
			$key_dates_email['happy_birthday']['member_types']=$args;
			update_option('church_admin_key_dates_emails',$key_dates_email);
			if (! wp_next_scheduled ( 'church_admin_happy_birthday_email', $args )) {
				wp_schedule_event( $first_run, 'daily','church_admin_happy_birthday_email',$args);
				
			}
			$message = __('Happy birthday email automation setup','church-admin');

		}else{
			$args = get_option('church_admin_happy_birthday_arguments');
			//delete_option('church_admin_happy_birthday_arguments');
			unset($key_dates_email['happy_birthday']);
			update_option('church_admin_key_dates_emails',$key_dates_email);
			wp_clear_scheduled_hook( 'church_admin_happy_birthday_email',$args);
			$message = __('Happy birthday email automation deleted','church-admin');
		}
		echo'<div class="notice notice-success"><h2>'.esc_html($message).'</h2></div>';
	}
	else
	{
		$key_dates_email=get_option('church_admin_key_dates_emails');
		echo'<form action="" method="POST">';
		echo'<h3>'.esc_html(__('Email template for email that goes to everyone detailing birthdays that day','church-admin')).'</h3>';
		echo'<div class="church-admin-form-group"><label>'.__("Email from name",'church-admin').'</label>';
		echo'<input type="text" name="happy_birthday_from_name" class="church-admin-form-control" ';
		if(!empty($happy_birthday['from_name'])) {
			echo 'value="'.esc_attr($happy_birthday['from_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("From email address",'church-admin').'</label>';
		echo'<input type="text" name="happy_birthday_from_email" class="church-admin-form-control" ';
		if(!empty($happy_birthday['from_email'])) {
			echo 'value="'.esc_attr($happy_birthday['from_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';
		
		//reply
		echo'<div class="church-admin-form-group"><label>'.__("Reply from name",'church-admin').'</label>';
		echo'<input type="text" name="happy_birthday_reply_name" class="church-admin-form-control" ';
		if(!empty($happy_birthday['reply_name'])) {
			echo 'value="'.esc_attr($happy_birthday['reply_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__('Reply email address','church-admin').'</label>';
		echo'<input type="text" name="happy_birthday_reply_email" class="church-admin-form-control" ';
		if(!empty($happy_birthday['reply_email'])) {
			echo 'value="'.esc_attr($happy_birthday['reply_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';




		echo'<p>'.esc_html(__('You can use the shortcodes [first_name],[last_name],[email],[cell],[age],[date] and HTML','church-admin')).'</p>';
		$happy_birthday=get_option('church_admin_happy_birthday_template');

		echo'<div class="church-admin-form-group"><label>'.__("Subject for email to people celebrating their birthday",'church-admin').'</label>';
		echo'<input type="text" name="happy_birthday_subject" class="church-admin-form-control" ';
		if(!empty($happy_birthday['subject'])) {
			echo 'value="'.esc_attr($happy_birthday['subject']).'" ';
		}
		echo'/></div>';

		$content   = !empty($happy_birthday['message']) ? $happy_birthday['message'] :'';
		$editor_id = 'happy_birthday_message';
		echo'<p><strong>'.__('Message template','church-admin').'</strong></p>';
		wp_editor( $content, $editor_id );

		
		echo'<p>'.__('If the birthday is being celebrated by a child, the email will go to the head of household with this template and the additional shortcode [child_name],[parent_name]','church-admin').'</p>';
		/*
		echo'<div class="church-admin-form-group"><label>'.__('Parent Message template','church-admin').'</label>';
		echo'<textarea name="parent_message" class="church-admin-form-control">';
		if(!empty($happy_birthday['parent_message'])){ 
			echo wp_kses_post($happy_birthday['parent_message']);
		}
		echo '</textarea></div>'."\r\n";
		*/
		$content   = !empty($happy_birthday['parent_message']) ? $happy_birthday['parent_message'] :'';
		$editor_id = 'parent_message';
		echo'<p><strong>'.__('Message template','church-admin').'</strong></p>';
		wp_editor( $content, $editor_id );



		echo'<h3><input type="checkbox" name="happy_birthday" id="happy_birthday" ';
		if(!empty($key_dates_email['happy_birthday'])) {echo ' checked="checked" ';}
		echo'>&nbsp;'.esc_html(__('Happy birthday to individual','church-admin')).'</h3>';
		foreach($member_types AS $id=>$type){
			echo '<p class="ca-tabbed"><input type="checkbox" class="happy_birthday" name="happy_birthday_mt[]" value="'.(int)$id.'" ';
			if(!empty($key_dates_email['happy_birthday']['member_types'][0]) && in_array($id,$key_dates_email['happy_birthday']['member_types'][0])){
				echo ' checked="checked" ';
			}
			echo'> &nbsp '.esc_html($type).'</p>';
		}
		echo'<p><input type="hidden" name="save-happy-birthday" value="1"><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';
		echo'<script>
		jQuery(document).ready(function($){
			$("body").on("change","#happy_birthday",function(){
				if(!$("#happy_birthday").is(":checked")){
					$(".happy_birthday").prop("checked",false);
				}
			});
		});</script>';
	}	
}


 /**
     *
     * Set up Global Birthday Email 
     *
     * @author  Andy Moyle
     * @param    null
     * @return
     * @version  0.1
     *
     */
function church_admin_global_birthday_email_setup(){
	$member_types = church_admin_member_types_array();
	$global_birthday=get_option('church_admin_global_birthday_template');
	

	echo'<h2>'.esc_html(__('Daily emails to everyone about birthdays that day','church-admin')).'</h2>';
	echo'<p>'.esc_html(__('Respects communications preferences','church-admin')).'</p>';


	$key_dates_email=get_option('church_admin_key_dates_emails');
	if(!empty($_POST['save-global-birthday'])){
		$from_name = !empty($_POST['global_birthday_from_name']) ? church_admin_sanitize($_POST['global_birthday_from_name']): get_option('church_admin_default_from_name');
		$from_email = !empty($_POST['global_birthday_from_email']) ? church_admin_sanitize($_POST['global_birthday_from_email']): get_option('church_admin_default_from_email');
		$reply_name = !empty($_POST['global_birthday_reply_name']) ? church_admin_sanitize($_POST['global_birthday_reply_name']): get_option('church_admin_default_from_name');
		$reply_email = !empty($_POST['global_birthday_reply_email']) ? church_admin_sanitize($_POST['global_birthday_reply_email']): get_option('church_admin_default_from_email');
		$global_birthday_subject = !empty($_POST['global_birthday_subject'])?wp_kses_post(stripslashes($_POST['global_birthday_subject'])):null;
		$global_birthday_message = !empty($_POST['global_birthday_message'])?wp_kses_post(wpautop(stripslashes($_POST['global_birthday_message']))):null;
		if(!empty($global_birthday_message)){update_option('church_admin_global_birthday_template',array('reply_name'=>$reply_name,'reply_email'=>$reply_email,'from_name'=>$from_name,'from_email'=>$from_email,'message'=>$global_birthday_message,'subject'=>$global_birthday_subject));}

		$first_run = strtotime("6am Tomorrow UTC");
		
		if(!empty($_POST['global_birthday'])){
			$args=array(church_admin_sanitize($_POST['global_birthday_mt']));
			update_option('church_admin_global_birthday_arguments',$args,false);
			
			$key_dates_email['global_birthday']['member_types']=$args;
			update_option('church_admin_key_dates_emails',$key_dates_email);
			if (! wp_next_scheduled ( 'church_admin_global_birthday_email', $args )) {
				wp_schedule_event( $first_run, 'daily','church_admin_global_birthday_email',$args);
				
			}
			$message = __('Global birthday email automation setup','church-admin');

		}else{
			$args = get_option('church_admin_global_birthday_arguments');
			//delete_option('church_admin_happy_birthday_arguments');
			unset($key_dates_email['global_birthday']);
			update_option('church_admin_key_dates_emails',$key_dates_email);
			wp_clear_scheduled_hook( 'church_admin_global_birthday_email',$args);
			$message = __('Global birthday email automation deleted','church-admin');
		}
		echo'<div class="notice notice-success"><h2>'.esc_html($message).'</h2></div>';
	}
	else
	{
		$key_dates_email=get_option('church_admin_key_dates_emails');
		
		echo'<form action="" method="POST">';
	
		$global_birthday=get_option('church_admin_global_birthday_template');
		echo'<div class="church-admin-form-group"><label>'.__("Email from name",'church-admin').'</label>';
		echo'<input type="text" name="global_birthday_from_name" class="church-admin-form-control" ';
		if(!empty($global_birthday['from_name'])) {
			echo 'value="'.esc_attr($global_birthday['from_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("From email address",'church-admin').'</label>';
		echo'<input type="text" name="global_birthday_from_email" class="church-admin-form-control" ';
		if(!empty($global_birthday['from_email'])) {
			echo 'value="'.esc_attr($global_birthday['from_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';


	//reply
	echo'<div class="church-admin-form-group"><label>'.__("Reply from name",'church-admin').'</label>';
	echo'<input type="text" name="global_birthday_reply_name" class="church-admin-form-control" ';
	if(!empty($global_birthday['reply_name'])) {
		echo 'value="'.esc_attr($global_birthday['reply_name']).'" ';
	}else{
		echo 'value="'.esc_attr(get_option('blog_name')).'" ';
	}
	echo'/></div>';
	echo'<div class="church-admin-form-group"><label>'.__('Reply email address','church-admin').'</label>';
	echo'<input type="text" name="global_birthday_reply_email" class="church-admin-form-control" ';
	if(!empty($global_birthday['reply_email'])) {
		echo 'value="'.esc_attr($global_birthday['reply_email']).'" ';
	}else{
		echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
	}
	echo'/></div>';



	echo'<p>'.esc_html(__('Use the shortcode [first_name], [last_name] and text for subject','church-admin')).'</p>';
	echo'<div class="church-admin-form-group"><label>'.__('Subject','church-admin').'</label>';
	echo'<input type="text" name="global_birthday_subject" class="church-admin-form-control" ';
	if(!empty($global_birthday['subject'])) echo 'value="'.esc_attr($global_birthday['subject']).'" ';
	echo'/></div>';
	echo'<p>'.esc_html(__("Use the shortcode [birthdays] which adds a html table of that day's birthdays and HTML",'church-admin')).'</p>';
	/*
	echo'<div class="church-admin-form-group"><label>'.__('Message template','church-admin').'</label>';
	echo'<textarea name="global_birthday_message" class="church-admin-form-control">';
	if(!empty($global_birthday['message'])){
		echo wp_kses_post($global_birthday['message']);
	}
	echo '</textarea></div>'."\r\n";
	*/
	$content   = !empty($global_birthday['message']) ? $global_birthday['message'] :'';
	$editor_id = 'global_birthday_message';
	echo'<p><strong>'.__('Message template','church-admin').'</strong></p>';
	wp_editor( $content, $editor_id );



		echo'<h3><input type="checkbox" value=1 name="global_birthday" id="global_birthday" ';
		if(!empty($key_dates_email['global_birthday'])) {echo ' checked="checked" ';}
		echo'>&nbsp;'.esc_html(__('Send email to people about birthdays that day','church-admin')).'</h3>';
		foreach($member_types AS $id=>$type){
			echo '<p class="ca-tabbed"><input type="checkbox" class="global_birthday" name="global_birthday_mt[]" value="'.(int)$id.'" ';
			if(!empty($key_dates_email['global_birthday']['member_types'][0]) && in_array($id,$key_dates_email['global_birthday']['member_types'][0])){
				echo ' checked="checked" ';
			}
			echo'> &nbsp '.esc_html($type).'</p>';
		}
		echo'<p><input type="hidden" name="save-global-birthday" value="1"><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';
		echo'<script>
		jQuery(document).ready(function($){
			$("body").on("change","#global_birthday",function(){
				if(!$("#global_birthday").is(":checked")){
					$(".global_birthday").prop("checked",false);
				}
			});
		});</script>';
	}	
}

 /**
     *
     * Set up Couple Anniversary Email 
     *
     * @author  Andy Moyle
     * @param    null
     * @return
     * @version  0.1
     *
     */
function church_admin_happy_anniversary_email_setup(){
	echo'<h2>'.esc_html(__('Set up daily email for couples who are celebrating their wedding anniversary','church-admin')).'</h2>';
	$member_types = church_admin_member_types_array();
	$happy_anniversary=get_option('church_admin_happy_anniversary_template');
	
	$key_dates_email=get_option('church_admin_key_dates_emails');
	if(!empty($_POST['save-happy-anniversary'])){
		$from_name = !empty($_POST['happy_anniversary_from_name']) ? church_admin_sanitize($_POST['happy_anniversary_from_name']): get_option('church_admin_default_from_name');
		$from_email = !empty($_POST['happy_anniversary_from_email']) ? church_admin_sanitize($_POST['happy_anniversary_from_email']): get_option('church_admin_default_from_email');
		$reply_name = !empty($_POST['happy_anniversary_reply_name']) ? church_admin_sanitize($_POST['happy_anniversary_reply_name']): get_option('church_admin_default_from_name');
		$reply_email = !empty($_POST['happy_anniversary_reply_email']) ? church_admin_sanitize($_POST['happy_anniversary_reply_email']): get_option('church_admin_default_from_email');
		
		$happy_anniversary_subject = !empty($_POST['happy_anniversary_subject'])?wp_kses_post(stripslashes($_POST['happy_anniversary_subject'])):null;
		$happy_anniversary_message = !empty($_POST['happy_anniversary_message'])?wp_kses_post(wpautop(stripslashes($_POST['happy_anniversary_message']))):null;
			
		
		
	


		$first_run = strtotime("6am Tomorrow UTC");
	
		if(!empty($_POST['happy_anniversary'])){
			update_option('church_admin_happy_anniversary_template',array('reply_name'=>$reply_name,'reply_email'=>$reply_email,'from_name'=>$from_name,'from_email'=>$from_email,'message'=>$happy_anniversary_message,'subject'=>$happy_anniversary_subject));
			$args=array(church_admin_sanitize($_POST['happy_anniversary_mt']));
			update_option('church_admin_happy_anniversary_arguments',$args,false);
			
			$key_dates_email['happy_anniversary']['member_types']=$args;
			update_option('church_admin_key_dates_emails',$key_dates_email);
			if (! wp_next_scheduled ( 'church_admin_happy_anniversary_email', $args )) {
				wp_schedule_event( $first_run, 'daily','church_admin_happy_anniversary_email',$args);
				
			}
			$message = __('Happy_anniversary email automation setup','church-admin');

		}else{
			$args = get_option('church_admin_happy_anniversary_arguments');
			unset($key_dates_email['happy_anniversary']);
			update_option('church_admin_key_dates_emails',$key_dates_email);
			
			wp_clear_scheduled_hook( 'church_admin_happy_anniversary_email',$args);
			$message = __('Happy anniversary email automation deleted','church-admin');
		}
		echo'<div class="notice notice-success"><h2>'.esc_html($message).'</h2></div>';
	}
	else
	{
		$key_dates_email=get_option('church_admin_key_dates_emails');
		
		echo'<form action="" method="POST">';

		$happy_anniversary=get_option('church_admin_happy_anniversary_template');
	
	
	
		echo'<div class="church-admin-form-group"><label>'.__("Email from name",'church-admin').'</label>';
		echo'<input type="text" name="happy_anniversary_from_name" class="church-admin-form-control" ';
		if(!empty($happy_anniversary['from_name'])) {
			echo 'value="'.esc_attr($happy_anniversary['from_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("From email address",'church-admin').'</label>';
		echo'<input type="text" name="happy_anniversary_from_email" class="church-admin-form-control" ';
		if(!empty($happy_anniversary['from_email'])) {
			echo 'value="'.esc_attr($happy_anniversary['from_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';

		/***************
		 * REPLY
		 **************/
		echo'<div class="church-admin-form-group"><label>'.__("Reply from name",'church-admin').'</label>';
		echo'<input type="text" name="happy_anniversary_reply_name" class="church-admin-form-control" ';
		if(!empty($happy_anniversary['reply_name'])) {
			echo 'value="'.esc_attr($happy_anniversary['reply_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("Reply email address",'church-admin').'</label>';
		echo'<input type="text" name="happy_anniversary_reply_email" class="church-admin-form-control" ';
		if(!empty($happy_anniversary['reply_email'])) {
			echo 'value="'.esc_attr($happy_anniversary['reply_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';


		echo'<div class="church-admin-form-group"><label>'.__('Individual happy anniversary email subject','church-admin').'</label>';
		echo'<input type="text" name="happy_anniversary_subject" class="church-admin-form-control" ';
		if(!empty($happy_anniversary['subject'])) {
			echo 'value="'.esc_attr($happy_anniversary['subject']).'" ';
		}
		echo'/></div>';
		echo'<p>'.esc_html(__('Use the shortcodes [couple_names],[email],[cell],[years] and HTML','church-admin')).'</p>'."\r\n";
		/*
		echo'<div class="church-admin-form-group"><label>'.__('Message template','church-admin').'</label>';
		echo'<textarea name="happy_anniversary_message" class="church-admin-form-control">';
		if(!empty($happy_anniversary['message'])) { echo wp_kses_post($happy_anniversary['message']);}
		echo '</textarea></div>'."\r\n";
		*/
		$content   = !empty($happy_anniversary['message']) ? $happy_anniversary['message'] :'';
		$editor_id = 'happy_anniversary_message';
		echo'<p><strong>'.__('Message template','church-admin').'</strong></p>';
		wp_editor( $content, $editor_id );


		echo'<h3><input type="checkbox" name="happy_anniversary" id="happy_anniversary" ';
		if(!empty($key_dates_email['happy_anniversary'])) {echo ' checked="checked" ';}
		echo'>&nbsp;'.esc_html(__('Send email to couples celebrating their anniversary that day','church-admin')).'</h3>';
		foreach($member_types AS $id=>$type){
			echo '<p class="ca-tabbed"><input type="checkbox" class="happy_anniversary" name="happy_anniversary_mt[]" value="'.(int)$id.'" ';
			if(!empty($key_dates_email['happy_anniversary']['member_types'][0]) && in_array($id,$key_dates_email['happy_anniversary']['member_types'][0])){
				echo ' checked="checked" ';
			}
			echo'> &nbsp '.esc_html($type).'</p>';
		}
		echo'<p><input type="hidden" name="save-happy-anniversary" value="1"><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';
		echo'<script>
		jQuery(document).ready(function($){
			$("body").on("change","#happy_anniversary",function(){
				if(!$("#happy_anniversary").is(":checked")){
					$(".happy_anniversary").prop("checked",false);
				}
			});
		});</script>';
	}	
}

 /**
     *
     * Set up Global Anniversaries Email 
     *
     * @author  Andy Moyle
     * @param    null
     * @return
     * @version  0.1
     *
     */
function church_admin_global_anniversary_email_setup(){
	$member_types = church_admin_member_types_array();
	$global_anniversary=get_option('church_admin_global_anniversary_template');
	
	$key_dates_email=get_option('church_admin_key_dates_emails');
	if(!empty($_POST['save-global-anniversary'])){
		$from_name = !empty($_POST['global_anniversary_from_name']) ? church_admin_sanitize($_POST['global_anniversary_from_name']): get_option('church_admin_default_from_name');
		$from_email = !empty($_POST['global_anniversary_from_email']) ? church_admin_sanitize($_POST['global_anniversary_from_email']): get_option('church_admin_default_from_email');

		$reply_name = !empty($_POST['global_anniversary_reply_name']) ? church_admin_sanitize($_POST['global_anniversary_reply_name']): get_option('church_admin_default_from_name');
		$reply_email = !empty($_POST['global_anniversary_reply_email']) ? church_admin_sanitize($_POST['global_anniversary_reply_email']): get_option('church_admin_default_from_email');


		$global_anniversary_subject = !empty($_POST['global_anniversary_subject'])?wp_kses_post(stripslashes($_POST['global_anniversary_subject'])):null;
		$global_anniversary_message = !empty($_POST['global_anniversary_message'])?wp_kses_post(wpautop(stripslashes($_POST['global_anniversary_message']))):null;

		if(!empty($global_anniversary_message)){update_option('church_admin_global_anniversary_template',array('reply_name'=>$reply_name,'reply_email'=>$reply_email,'from_name'=>$from_name,'from_email'=>$from_email,'message'=>$global_anniversary_message,'subject'=>$global_anniversary_subject));}


		$first_run = strtotime("6am Tomorrow UTC");
		
		if(!empty($_POST['global_anniversary'])){
			$args=array(church_admin_sanitize($_POST['global_anniversary_mt']));
			update_option('church_admin_global_anniversary_arguments',$args,false);
			
			$key_dates_email['global_anniversary']['member_types']=$args;
			update_option('church_admin_key_dates_emails',$key_dates_email);
			if (! wp_next_scheduled ( 'church_admin_global_anniversary_email', $args )) {
				wp_schedule_event( $first_run, 'daily','church_admin_global_anniversary_email',$args);
				
			}
			$message = __('Global anniversary email automation setup','church-admin');

		}else{
			$args = get_option('church_admin_global_anniversary_arguments');
			
			unset($key_dates_email['global_anniversary']);
			update_option('church_admin_key_dates_emails',$key_dates_email);
			wp_clear_scheduled_hook( 'church_admin_global_anniversary_email',$args);
			$message = __('Global anniversary email automation deleted','church-admin');
		}
		echo'<div class="notice notice-success"><h2>'.esc_html($message).'</h2></div>';
	}
	else
	{
		$key_dates_email=get_option('church_admin_key_dates_emails');
		echo'<form action="" method="POST">';

		echo'<h3>'.esc_html(__('Email template for emails that goes to everyone with details of each couple celebrating on that day','church-admin')).'</h3>';
		$global_anniversary=get_option('church_admin_global_anniversary_template');
		
		echo'<div class="church-admin-form-group"><label>'.__("Email from name",'church-admin').'</label>';
		echo'<input type="text" name="global_anniversary_from_name" class="church-admin-form-control" ';
		if(!empty($global_anniversary['from_name'])) {
			echo 'value="'.esc_attr($global_anniversary['from_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("From email address",'church-admin').'</label>';
		echo'<input type="text" name="global_birthday_from_email" class="church-admin-form-control" ';
		if(!empty($global_anniversary['from_email'])) {
			echo 'value="'.esc_attr($global_anniversary['from_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';

		/****************
		 * REPLY
		 ***************/
		echo'<div class="church-admin-form-group"><label>'.__("Reply from name",'church-admin').'</label>';
		echo'<input type="text" name="global_anniversary_reply_name" class="church-admin-form-control" ';
		if(!empty($global_anniversary['reply_name'])) {
			echo 'value="'.esc_attr($global_anniversary['reply_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("Reply email address",'church-admin').'</label>';
		echo'<input type="text" name="global_birthday_reply_email" class="church-admin-form-control" ';
		if(!empty($global_anniversary['reply_email'])) {
			echo 'value="'.esc_attr($global_anniversary['reply_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';





		echo'<div class="church-admin-form-group"><label>'.__("Subject",'church-admin').'</label>';
		echo'<input type="text" name="global_anniversary_subject" class="church-admin-form-control" ';
		if(!empty($global_anniversary['subject'])) echo 'value="'.esc_attr($global_anniversary['subject']).'" ';
		echo'/></div>'."\r\n";
		echo'<p>'.esc_html(__("[anniversaries] is replaced with a table of anniversary details for that day",'church-admin') ).'</p>';
		/*
		echo'<div class="church-admin-form-group"><label>'.__('Message template','church-admin').'</label>';
		
		echo'<textarea name="global_anniversary_message" class="church-admin-form-control">';
		if(!empty($global_anniversary['message'])){ echo wp_kses_post($global_anniversary['message']);}
		echo '</textarea></div>'."\r\n";
		*/
		$content   = !empty($global_anniversary['message']) ? $global_anniversary['message'] :'';
		$editor_id = 'global_anniversary_message';
		echo'<p><strong>'.__('Message template','church-admin').'</strong></p>';
		wp_editor( $content, $editor_id );
		
		echo'<h3><input type="checkbox" name="global_anniversary" id="global_anniversary" ';
		if(!empty($key_dates_email['global_anniversary'])) {echo ' checked="checked" ';}
		echo'>&nbsp;'.esc_html(__('Send email to people about anniversaries that day','church-admin')).'</h3>';
		foreach($member_types AS $id=>$type){
			echo '<p class="ca-tabbed"><input type="checkbox" class="global_anniversary" name="global_anniversary_mt[]" value="'.(int)$id.'" ';
			if(!empty($key_dates_email['global_anniversary']['member_types'][0]) && in_array($id,$key_dates_email['global_anniversary']['member_types'][0])){
				echo ' checked="checked" ';
			}
			echo'> &nbsp '.esc_html($type).'</p>';
		}
		echo'<p><input type="hidden" name="save-global-anniversary" value="1"><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';
		echo'<script>
		jQuery(document).ready(function($){
			$("body").on("change","#global_anniversary",function(){
				if(!$("#global_anniversary").is(":checked")){
					$(".global_anniversary").prop("checked",false);
				}
			});
		});</script>';
	}	
}


function church_admin_global_both_email_setup(){
	$member_types = church_admin_member_types_array();
	$global_both=get_option('church_admin_global_both_template');
	
	$key_dates_email=get_option('church_admin_key_dates_emails');
	if(!empty($_POST['save-global-both'])){
		$from_name = !empty($_POST['global_both_from_name']) ? church_admin_sanitize($_POST['global_both_from_name']): get_option('church_admin_default_from_name');
		$from_email = !empty($_POST['global_both_from_email']) ? church_admin_sanitize($_POST['global_both_from_email']): get_option('church_admin_default_from_email');
		$reply_name = !empty($_POST['global_both_reply_name']) ? church_admin_sanitize($_POST['global_both_reply_name']): get_option('church_admin_default_from_name');
		$reply_email = !empty($_POST['global_both_reply_email']) ? church_admin_sanitize($_POST['global_both_reply_email']): get_option('church_admin_default_from_email');
		$global_both_subject = !empty($_POST['global_both_subject'])?wp_kses_post(stripslashes($_POST['global_both_subject'])):null;
		$global_both_message = !empty($_POST['global_both_message'])?wp_kses_post(wpautop(stripslashes($_POST['global_both_message']))):null;
		if(!empty($global_both_message)){update_option('church_admin_global_both_template',array('reply_name'=>$reply_name,'reply_email'=>$reply_email,'from_name'=>$from_name,'from_email'=>$from_email,'message'=>$global_both_message,'subject'=>$global_both_subject));}


		$first_run = strtotime("6am Tomorrow UTC");
		
		if(!empty($_POST['global_both'])){
			$args=array(church_admin_sanitize($_POST['global_both_mt']));
			update_option('church_admin_global_both_arguments',$args,false);
			
			$key_dates_email['global_both']['member_types']=$args;
			update_option('church_admin_key_dates_emails',$key_dates_email);
			if (! wp_next_scheduled ( 'church_admin_global_both_email', $args )) {
				wp_schedule_event( $first_run, 'daily','church_admin_global_birthday_and_anniversary_email',$args);
				
			}
			$message = __('Global both email automation setup','church-admin');

		}else{
			$args = get_option('church_admin_global_both_arguments');
			unset($key_dates_email['global_both']);
			update_option('church_admin_key_dates_emails',$key_dates_email);
			
			wp_clear_scheduled_hook( 'church_admin_global_both_email',$args);
			$message = __('Global both email automation deleted','church-admin');
		}
		echo'<div class="notice notice-success"><h2>'.esc_html($message).'</h2></div>';
	}
	else
	{
		$key_dates_email=get_option('church_admin_key_dates_emails');
		echo'<form action="" method="POST">';

		echo'<h3>'.esc_html(__('Email template for emails that go to everyone with details of each birthday and couples celebrating wedding anniversaries on that day','church-admin')).'</h3>';
		$global_both=get_option('church_admin_global_both_template');
		if(empty($global_both)){
		   $global_both = array('message'=>'[birthday_text]Here are the birthdays being celebrated today. Why not drop them a message with a blessing! [/birthday_text]
		   [birthdays]
		   [anniversary_text]Here are the anniversaries being celebrated today. Why not drop them a message with a blessing! [/anniversary_text]
		   [anniversaries]',
		   'subject'=>"Today's birthdays and anniversaries"
		);
   
		}
		echo'<div class="church-admin-form-group"><label>'.__("Email from name",'church-admin').'</label>';
		echo'<input type="text" name="global_both_from_name" class="church-admin-form-control" ';
		if(!empty($global_both['from_name'])) {
			echo 'value="'.esc_attr($global_both['from_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("From email address",'church-admin').'</label>';
		echo'<input type="text" name="global_both_from_email" class="church-admin-form-control" ';
		if(!empty($global_both['from_email'])) {
			echo 'value="'.esc_attr($global_both['from_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';

		/****************
		 * REPLY
		 ***************/
		echo'<div class="church-admin-form-group"><label>'.__("Reply from name",'church-admin').'</label>';
		echo'<input type="text" name="global_both_reply_name" class="church-admin-form-control" ';
		if(!empty($global_both['reply_name'])) {
			echo 'value="'.esc_attr($global_both['reply_name']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('blog_name')).'" ';
		}
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.__("Reply email address",'church-admin').'</label>';
		echo'<input type="text" name="global_both_reply_email" class="church-admin-form-control" ';
		if(!empty($global_both['reply_email'])) {
			echo 'value="'.esc_attr($global_both['reply_email']).'" ';
		}else{
			echo 'value="'.esc_attr(get_option('church_admin_default_from_email')).'" ';
		}
		echo'/></div>';



	   echo'<h3>'.esc_html(__("Send daily email with details of that day's birthdays and anniversaries",'church-admin') ).'</h3>';
	   echo'<div class="church-admin-form-group"><label>'.__("Subject",'church-admin').'</label>';
		echo'<input type="text" name="global_both_subject" class="church-admin-form-control" ';
		if(!empty($global_both['subject'])) echo 'value="'.esc_attr($global_both['subject']).'" ';
		echo'/></div>';
	   echo'<p>'.esc_html(__("Enclose text about birthdays between [birthday_text] and [/birthday_text]. That text will show if in the email if there are any birthdays that day.",'church-admin')).'</p>'."\r\n";
	   echo'<p>'.esc_html(__("[birthdays] is replaced with a table of birthday details for that day",'church-admin' ) ).'</p>';
	   echo'<p>'.esc_html(__("Enclose text about anniversaries between [anniversary_text] and [/anniversary_text]. That text will show if in the email if there are any anniversaries that day.",'church-admin')).'</p>'."\r\n";
	   echo'<p>'.esc_html(__("[anniversaries] is replaced with a table of anniversary details for that day",'church-admin') ).'</p>';
	   $global_both=get_option('church_admin_global_both_template');
	   /*
	   echo'<div class="church-admin-form-group"><label>'.__('Message template','church-admin').'</label>';
	   echo'<textarea name="global_both_message" class="church-admin-form-control">';
	   if(!empty($global_both['message'])){ echo wp_kses_post($global_both['message']);}
		echo '</textarea></div>'."\r\n";
		*/
		$content   = !empty($global_both['message']) ? $global_both['message'] :'';
		$editor_id = 'global_both_message';
		echo'<p><strong>'.__('Message template','church-admin').'</strong></p>';
		wp_editor( $content, $editor_id );



		echo'<h3><input type="checkbox" name="global_both" id="happy_both" ';
		if(!empty($key_dates_email['global_both'])) {echo ' checked="checked" ';}
		echo'>&nbsp;'.esc_html(__('Send email to people about all the birthdays and  anniversaries that day','church-admin')).'</h3>';
		foreach($member_types AS $id=>$type){
			echo '<p class="ca-tabbed"><input type="checkbox" class="global_both" name="global_both_mt[]" value="'.(int)$id.'" ';
			if(!empty($key_dates_email['global_both']['member_types'][0]) && in_array($id,$key_dates_email['global_both']['member_types'][0])){
				echo ' checked="checked" ';
			}
			echo'> &nbsp '.esc_html($type).'</p>';
		}
		echo'<p><input type="hidden" name="save-global-both" value="1"><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';
		echo'<script>
		jQuery(document).ready(function($){
			$("body").on("change","#global_both",function(){
				if(!$("#global_both").is(":checked")){
					$(".global_both").prop("checked",false);
				}
			});
		});</script>';
	}	
}



function church_admin_key_dates_email_setup()
{
	$member_types = church_admin_member_types_array();
	echo '<h3>'.esc_html(__('Set up auto email for keys dates','church-admin')).'</h3>';
	echo'<p>'.esc_html(__('Please note email sending permissions are respected.','church-admin')).'</p>';
	echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=happy-birthday-email-setup&amp;section=key-dates','happy-birthday-email-setup').'">'.esc_html(__('Individual Birthday email setup','church-admin')).'</a></p>';
	echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=global-birthday-email-setup&amp;section=key-dates','global-birthday-email-setup').'">'.esc_html(__('Daily info about birthdays email setup','church-admin')).'</a></p>';
	echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=happy-anniversary-email-setup&amp;section=key-dates','happy-anniversary-email-setup').'">'.esc_html(__("Couple's anniversary email setup",'church-admin')).'</a></p>';
	echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=global-anniversary-email-setup&amp;section=key-dates','global-anniversary-email-setup').'">'.esc_html(__('Daily info about anniversaries email setup','church-admin')).'</a></p>';
	echo'<p><a href="'.wp_nonce_url($church_admin_url.'&action=global-both-email-setup&amp;section=key-dates','global-both-email-setup').'">'.esc_html(__('Daily info about birthdays and anniversaries email setup','church-admin')).'</a></p>';

}

function church_admin_birthday_widget_control()
{

    //get saved options

    $options=get_option('church_admin_birthday_widget');

    //handle user input

    if(!empty( $_POST['widget_submit'] ) )

    {

		$options['title']=strip_tags(sanitize_text_field( $_POST['title'] ) );

        if(church_admin_int_check( $_POST['days'] ) )  {
			$options['days']=(int)sanitize_text_field(stripslashes($_POST['days']));
		}else{
			$options['days']='14';
		}
        $memb=array();
		$memb_ids = !empty($_POST['member_type_id'])? church_admin_sanitize($_POST['member_type_id']):array();
		foreach( $memb_ids AS $key=>$value){
			$memb[]=$value;
		}
		$options['member_type_id']=implode(',',$memb);
		if(!empty( $_POST['showAge'] ) )  {$options['showAge']=TRUE;}else{$options['showAge']=FALSE;}
        update_option('church_admin_birthday_widget',$options);

    }

    church_admin_birthday_widget_control_form();

}



function church_admin_birthday_widget_control_form()
{

    global $wpdb;
	$member_type=church_admin_member_types_array();





    $option=get_option('church_admin_birthday_widget');
	if ( empty( $option['showAge'] ) )$option['showAge']=FALSE;
    echo '<p><label for="title"><strong>'.esc_html( __('Title','church-admin' ) ).':</strong></label><input type="text" name="title" value="'.esc_attr($option['title']).'" /></p>';
	echo '<p><label for="showAge"><strong>'.esc_html( __('Show Age?','church-admin' ) ).':</strong></label><input type="checkbox" name="showAge" '.checked(TRUE,$option['showAge'],FALSE).'/></p>';
    echo '<p><label for="member_type_id"><strong>'.esc_html( __('Which Member Types?','church-admin' ) ).':</strong></label></p>';

	if(!empty( $option['member_type_id'] ) )$stored=explode(',',$option['member_type_id'] );

	foreach( $member_type AS $key=>$value )

	{

		echo'<p>'.esc_html( $value).' <input type="checkbox" name="member_type_id[]" value="'.esc_html( $key).'" ';

		if(!empty( $stored)&& in_array( $key,$stored) ) echo' checked="checked" ';

		echo'/></p>';

	}

    echo'</p>';

    echo '<p><label for="days"><strong>'.esc_html( __('How many days to show','church-admin' ) ).'?</strong></label><select name="days">';

    if(isset( $option['days'] ) ) echo '<option value="'.esc_html( $option['days'] ).'">'.esc_html( $option['days'] ).'</option>';

    for ( $x=1; $x<=365; $x++)  {echo '<option value="'.(int)$x.'">'.(int)$x.'</option>';}

    echo'</select><input type="hidden" name="widget_submit" value="1" />';

}