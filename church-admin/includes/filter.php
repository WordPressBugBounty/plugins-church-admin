<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


//use $email=TRUE to stop javascript activating as well eg for SMS and CSV download
function church_admin_directory_filter( $JSUse=TRUE,$email=FALSE)
{

	//Make $email TRUE when used for email and SMS
	global $wpdb,$church_admin_spiritual_gifts,$church_admin_url;
	$church_admin_marital_status=get_option('church_admin_marital_status');
	if( $JSUse)echo'<h2>'.esc_html( __('Filtered Address List','church-admin' ) ).'</h2>';
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=choose-filters','choose-filters').'" class="button-primary">'.esc_html(__('Changed which filter boxes are available','church-admin')).'</a></p>';
	if( $JSUse)echo'<p><strong>'.esc_html( __('Use the checkboxes to filter the address list you will see, results appear under the filter boxes','church-admin' ) ).'</strong></p>';
	if( $JSUse)echo'<p><strong>'.esc_html( __('People totals in brackets are for that particular item only','church-admin' ) ).'</strong></p>';
    
	//exclude section
	$disabled=$message='';
	$premium=get_option('church_admin_payment_gateway');
	if(empty($premium)){
		$disabled='disabled = "disabled" ';
		$message= ' <a href="'.wp_nonce_url($church_admin_url.'&action=app','app').'">'. esc_html( __('This is a premium feature, please upgrade','church-admin' ) ).'</a>';
	}
	echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Select everyone in the address list (ignores all other choices)' , 'church-admin' ) ). $message .'</label> &nbsp;';
	echo '<input type="checkbox" ';
	if(!empty($premium)){echo 'name="send-to-all" value="yes"   id="send-to-all" ';}else echo $disabled;
	echo'/>';
	echo'</div>';


	echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Exclude people from results (comma separated)' , 'church-admin' ) ). $message .'</label>';
	echo church_admin_autocomplete('exclude','exclude','to',null);
	//echo '<input type="text" name="exclude" class="church-admin-form-control" '.$disabled.' id="exclude" />';
	echo'</div>';

	
	
	

	if ( empty( $email) )  {echo'<form action="admin.php?page=church_admin%2Findex.php" method="POST"><div id="filters" class="ca-box">';}else{echo'<div id="filters1" class="ca-box">';}
	$class='category';
    
	$whichFilters=get_option('church-admin-which-filters');
	//church_admin_debug($whichFilters);
	//gender
	if(!empty( $whichFilters['genders'] ) )
    {
        $genders=get_option('church_admin_gender');
        echo'<div class="filterblock"><label>'.esc_html( __('Gender','church-admin' ) ).'</label>';
        foreach( $genders AS $key=>$gender)
        {
            $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  sex="'.esc_sql( $key).'" ');
            echo'<p><input type="checkbox" name="check[]" class="'.$class.' gender" value="ge/'.sanitize_title( $gender).'" />'.esc_html( $gender).' ('.(int)$count.')</p>';
        }
        echo'</div>';
    }
    //address
	if(!empty( $whichFilters['address'] ) )
    {
        
        echo'<div class="filterblock"><label>'.esc_html( __('Address','church-admin' ) ).'</label>';
		$noAddressCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_household'. ' WHERE  address="" ');
		$addressCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_household'. ' WHERE  address!="" ');
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' address" value="ad/no" />'.esc_html( __("No address",'church-admin' ) ).' ('.(int)$noAddressCount.')</p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' email" value="ad/yes" />'.esc_html( __("Has address",'church-admin' ) ).' ('.(int)$addressCount.')</p>';

	   echo'</div>';
    }
	//email
	if(!empty( $whichFilters['email-addresses'] ) )
    {
		echo'<div class="filterblock"><label>'.esc_html( __('Email Address','church-admin' ) ).'</label>';
		$noEmailCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  email="" ');
		$emailCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  email!="" ');
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' email" value="em/no" />'.esc_html( __("No email address",'church-admin' ) ).' ('.(int)$noEmailCount.')</p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' email" value="em/yes" />'.esc_html( __("Has email address",'church-admin' ) ).' ('.(int)$emailCount.')</p>';

	   echo'</div>';
    }
	if(!empty( $whichFilters['email-send'] ) )
    {
		echo'<div class="filterblock"><label>'.esc_html( __('Email send permission','church-admin' ) ).'</label>';
		$noPermission=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  email_send=0 ');
		$emailCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  email_send=1 and email!=""');
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' email_send" value="se/0" />'.esc_html( __("No email send permission",'church-admin' ) ).' ('.(int)$noPermission.')</p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' email_send" value="se/1" />'.esc_html( __("Email send permission and an email address",'church-admin' ) ).' ('.(int)$emailCount.')</p>';

	   echo'</div>';
    }
    
	//cell
	if(!empty( $whichFilters['cell'] ) )
    {
        echo'<div class="filterblock"><label>'.esc_html( __('Cell phone','church-admin' ) ).'</label>';
		$noMobileCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  mobile="" ');
		$mobileCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  mobile!="" ');
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' mobile" value="ce/no" />'.esc_html( __("No cell phone",'church-admin' ) ).' ('.(int)$noMobileCount.')</p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' mobile" value="ce/yes" />'.esc_html( __("Has cell phone",'church-admin' ) ).' ('.(int)$mobileCount.')</p>';

	   echo'</div>';
    }
	//photo-permission
	if(!empty( $whichFilters['photo-permission'] ) )
    {
        echo'<div class="filterblock"><label>'.esc_html( __('Photo permission','church-admin' ) ).'</label>';
		$noPhotoPermissionCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  photo_permission=0 ');
		$PhotoPermissionCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  photo_permission=1');
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' photo-permission" value="pp/0" />'.esc_html( __("No photo permission",'church-admin' ) ).' ('.(int)$noPhotoPermissionCount.')</p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' photo-permission" value="pp/1" />'.esc_html( __("Photo permission",'church-admin' ) ).' ('.(int)$PhotoPermissionCount.')</p>';

	   echo'</div>';
    }
	//show-me
	if(!empty( $whichFilters['show-me'] ) )
    {
        echo'<div class="filterblock"><label>'.esc_html( __('Visible in directory','church-admin' ) ).'</label>';
		$noShowCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  show_me=0 ');
		church_admin_debug($wpdb->last_query);
		$showCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  show_me=1');
		church_admin_debug($wpdb->last_query);
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' show-me" value="sm/0" />'.esc_html( __("Not shown in directory",'church-admin' ) ).' ('.(int)$noShowCount.')</p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' show-me" value="sm/1" />'.esc_html( __("Shown in directory",'church-admin' ) ).' ('.(int)$showCount.')</p>';

	   echo'</div>';
    }
	//user account
	if(!empty( $whichFilters['user-accounts'] ) )
    {	echo'<div class="filterblock"><label>'.esc_html( __('Connected User account','church-admin' ) ).'</label>';
		$noUserCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  user_id="" ');
		$userCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  user_id!="" ');
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' user" value="us/no" />'.esc_html( __("No connected user account",'church-admin' ) ).' ('.(int)$noUserCount.')</p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.' user" value="us/yes" />'.esc_html( __("Connect user account",'church-admin' ) ).' ('.(int)$userCount.')</p>';

	   echo'</div>';
    }
	//data protection
	if(!empty( $whichFilters['gdpr'] ) )
    {
        echo'<div class="filterblock"><label>'.esc_html( __('Personal Data','church-admin' ) ).'</label>';
        $notCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  gdpr_reason IS NULL OR gdpr_reason="" ');
        $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  gdpr_reason IS NOT NULL OR gdpr_reason!="" ');
        echo'<p><input type="checkbox" name="check[]" class="'.$class.' gdpr" value="da/1" />'.esc_html( __('Confirmed','church-admin' ) ).' ('.(int)$count.')</p>';
        echo'<p><input type="checkbox" name="check[]" class="'.$class.' gdpr" value="da/0" />'.esc_html( __('Not Confirmed','church-admin' ) ).' ('.(int)$notCount.')</p>';
        echo'</div>';
    }
	//people types
    if(!empty( $whichFilters['people_types'] ) )
    {
	   $people_types=get_option('church_admin_people_type');
        if(!empty( $people_types) )
        {
            echo'<div class="filterblock"><label>'.esc_html( __('People Types','church-admin' ) ).'</label>';
            $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people');
            echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="people" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).' ('.(int)$count.')</strong></p>';
            foreach( $people_types AS $key=>$people_type)
            {
                $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  people_type_id="'.esc_sql( $key).'" ');
                echo'<p><input type="checkbox" name="check[]" class="'.$class.' spiritual-gifts" value="pe/'.sanitize_title( $people_type).'" />'.esc_html( $people_type).' ('.(int)$count.')</p>';
            }
            echo'</div>';
        }
    }
	if(!empty( $whichFilters['spiritual-gifts'] ) )
	{
		echo'<div class="filterblock"><label>'.esc_html( __('Spiritual gifts','church-admin' ) ).'</label>';
		$count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people');
        echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="people" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).' ('.(int)$count.')</strong></p>';
        foreach( $church_admin_spiritual_gifts AS $giftID=>$gift)
		{
			$count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="spiritual_gifts" AND ID="'.(int)$giftID.'"');
			echo'<p><input type="checkbox" name="check[]" class="'.$class.' people" value="sp/'.(int)$giftID.'" />'.esc_html( $gift).' ('.(int)$count.')</p>';
            }
            echo'</div>'; 
	}
    //phone calls
    if(!empty( $whichFilters['phone-calls'] ) )
    {
	   $phoneCallsCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  phone_calls="1" ');
        $noPhoneCallsCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  phone_calls="0" ');
        
            echo'<div class="filterblock"><label>'.esc_html( __('Phone calls','church-admin' ) ).'</label>';
           
            echo'<p><input  type="checkbox" name="check[]" class="'.$class.' phone-calls" value="pc/1" />'.esc_html( __('Can receive phonecalls','church-admin' ) ).' ('.(int)$phoneCallsCount.')</p>';
        
        echo'<p><input  type="checkbox" name="check[]" class="'.$class.' phone-calls" value="pc/0" />'.esc_html( __('No phone calls permission','church-admin' ) ).' ('.(int)$noPhoneCallsCount.')</p>';
            echo'</div>';
       
    }
	//active
	 if(!empty( $whichFilters['active'] ) )
    {
        echo'<div class="filterblock"><label>'.esc_html( __('Active/Deactivated','church-admin' ) ).'</label>';
        $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  active="1" ');
        echo'<p><input  type="checkbox" name="check[]" class="'.$class.' marital" value="ac/1" />'.esc_html( __('Active','church-admin' ) ).' ('.(int)$count.')</p>';
        $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  active="0" ');
        echo'<p><input  type="checkbox" name="check[]" class="'.$class.' marital" value="ac/0" />'.esc_html( __('Inactive','church-admin' ) ).' ('.(int)$count.')</p>';
        echo'</div>';
    }
	//marital status
	 if(!empty( $whichFilters['marital'] ) )
    {
        echo'<div class="filterblock"><label>'.esc_html( __('Marital Status','church-admin' ) ).'</label>';
        foreach( $church_admin_marital_status AS $key=>$status)
        {
            $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  marital_status="'.esc_sql( $status).'" AND people_type_id=1');
            echo'<p><input  type="checkbox" name="check[]" class="'.$class.' marital" value="ma/'.sanitize_title( $status).'" />'.esc_html( $status).' ('.(int)$count.')</p>';
        }
        echo'</div>';
    
    }
	//Sites
     if(!empty( $whichFilters['sites'] ) )
    {
            $results=$wpdb->get_results('SELECT venue,site_id FROM '.$wpdb->prefix.'church_admin_sites ORDER BY venue ASC');
        if(!empty( $results) )
        {
            echo'<div class="filterblock"><label>'.esc_html( __('Sites','church-admin' ) ).'</label>';
            echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="sites" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).'</strong></p>';

            foreach( $results AS $row)
            {
                    $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people'. ' WHERE  site_id="'.esc_sql( $row->site_id).'" ');
                    echo'<p><input type="checkbox" name="check[]" class="'.$class.' sites" value="si/'.sanitize_title( $row->venue).'" />'.esc_html( $row->venue).' ('.(int)$count.')</p>';

            }
                    echo'</div>';
        }
    }
	//Member Types
	 if(!empty( $whichFilters['member_types'] ) )
    {
        $results=$wpdb->get_results('SELECT member_type_id,member_type FROM '.$wpdb->prefix.'church_admin_member_types ORDER BY member_type_order ASC');
        if(!empty( $results) )
        {
            echo'<div class="filterblock"><label>'.esc_html( __('Member Types','church-admin' ) ).'</label>';
            echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="member" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).'</strong></p>';
            foreach( $results AS $mt)
            {
                $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.(int) $mt->member_type_id.'"');
                echo'<p><input  type="checkbox" name="check[]" class="'.$class.' member" value="mt/'.sanitize_title( $mt->member_type).'" />'.esc_html( $mt->member_type).' ('.(int)$count.')</p>';
            }
            echo'</div>'."\r\n";
        }
    }
	//Small Groups
    if(!empty( $whichFilters['small-groups'] ) )
    {
    $results=$wpdb->get_results('SELECT id, group_name FROM '.$wpdb->prefix.'church_admin_smallgroup ORDER BY group_name ASC');
	if(!empty( $results) )
	{

		echo'<div class="filterblock"><label>'.esc_html( __('Small Groups','church-admin' ) ).'</label>';
		echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="groups" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).'</strong></p>';
		echo'<p><input type="checkbox" name="check[]" class="'.$class.'" value="gp/no-group" /><strong>'.esc_html( __('Not in a group (overrides other small group selections)','church-admin' ) ).'</strong></p>';
		echo'<p>';

			foreach( $results AS $row)
			{
				$count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$row->id.'" AND meta_type="smallgroup"');

				echo'<span ><input type="checkbox" name="check[]" class="'.$class.' groups" value="gp/'.sanitize_title( $row->group_name).'" />'.esc_html( $row->group_name).' ('.(int)$count.')</span><br>';

			}
		}

				echo'</p></div>'."\r\n";
    }
	//Classes
    if(!empty( $whichFilters['classes'] ) )
    {
		$results=$wpdb->get_results('SELECT class_id, name FROM '.$wpdb->prefix.'church_admin_classes ORDER BY name ASC');
		if(!empty( $results) )
		{

			echo'<div class="filterblock"><label>'.esc_html( __('Classes','church-admin' ) ).'</label>';
			echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="classes" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).'</strong></p>';
			
			echo'<p>';

			foreach( $results AS $row)
			{
				$count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$row->class_id.'" AND meta_type="class"');

				echo'<span ><input type="checkbox" name="check[]" class="'.$class.' classes" value="cl/'.sanitize_title( $row->name).'" />'.esc_html( $row->name).' ('.(int)$count.')</span><br>';

			}
			echo'</p></div>'."\r\n";
		}

		
    }
	//Ministries
     if(!empty( $whichFilters['ministries'] ) )
    {
        $results=$wpdb->get_results('SELECT ministry,ID FROM '.$wpdb->prefix.'church_admin_ministries ORDER BY ministry ASC');
        if(!empty( $results) )
        {
            echo'<div class="filterblock"><label>'.esc_html( __('Ministries','church-admin' ) ).'</label>';
            echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="ministries" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).'</strong></p>';

             echo '<p>';
            foreach( $results AS $row)
            {
                $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$row->ID.'" AND meta_type="ministry"');

                echo'<span ><input type="checkbox" name="check[]" class="'.$class.' ministries" value="mi/'.sanitize_title( $row->ministry).'" />'.esc_html( $row->ministry).' ('.(int)$count.')</span><br>';

            }
                    echo'</p></div>'."\r\n";
        }
    }
		//Bible readings
		if(!empty( $whichFilters['bible-readings'] ) )
		{
			
			
			echo'<div class="filterblock"><label>'.esc_html( __('Bible Readings','church-admin' ) ).'</label>';
			echo '<p>';
		
			$count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="bible-readings"');
			
			echo'<span ><input type="checkbox" name="check[]" class="'.$class.' bible-readings" value="br/1" />'.esc_html( __('Receive Bible readings','church-admin')).' ('.(int)$count.')</span><br>';
					
			echo'</p></div>'."\r\n";
			
		}
	//year of birth
	 if(!empty( $whichFilters['birth-year'] ) )
    {

        $years=$wpdb->get_results('SELECT YEAR(date_of_birth) AS year FROM '.$wpdb->prefix.'church_admin_people WHERE date_of_birth!="0000-00-00" GROUP BY YEAR(date_of_birth) ORDER BY YEAR(date_of_birth) ASC');
        if(!empty( $years) )
        {
                echo'<div class="filterblock"><label>'.esc_html( __('Year of Birth','church-admin' ) ).'</label><ul style="columns:2">';
                
                foreach( $years AS $year)
                {
                    $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE YEAR(date_of_birth)="'.esc_sql( $year->year).'"');

                    echo'<li><input type="checkbox" name="check[]" class="'.$class.' years" value="ye/'.sanitize_title( $year->year).'">'.esc_html( $year->year).' ('.(int)$count.')</li>';
                }
                echo'</ul></div>'."\r\n";
        }
    }
     if(!empty( $whichFilters['birth-month'] ) )
    {
        $months=$wpdb->get_results('SELECT MONTH(date_of_birth) AS month FROM '.$wpdb->prefix.'church_admin_people WHERE date_of_birth!="0000-00-00" GROUP BY MONTH(date_of_birth) ORDER BY MONTH(date_of_birth) ASC');
        if(!empty( $months) )
        {
                echo'<div class="filterblock"><label>'.esc_html( __('Month of Birth','church-admin' ) ).'</label>';
                echo '<p>';
                foreach( $months AS $month)
                {
                    $count=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE MONTH(date_of_birth)="'.esc_sql( $month->month).'"');
                    echo'<span ><input type="checkbox" name="check[]" class="'.$class.' parents" value="mo/'.sanitize_title( $month->month).'" />'.mysql2date('F','2018-'.sprintf('%02d',$month->month).'-01').' ('.intval( $count).')</span>';
                    echo'<br>';
                }
                echo'</p></div>'."\r\n";
        }
    }
	//parents
     if(!empty( $whichFilters['parents'] ) )
    {
        $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork ORDER BY youngest');
        if(!empty( $results) )
        {
            echo'<div class="filterblock"><label>'.esc_html( __('Parents with children in...','church-admin' ) ).'</label>';
            echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="parents" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).'</strong></p>';
            echo '<p>';
            foreach( $results AS $row)
            {

                echo'<span ><input type="checkbox" name="check[]" class="'.$class.' parents" value="pa/'.sanitize_title( $row->id).'" />'.esc_html( $row->group_name).'</span>';
                echo'<br>';
            }
            echo'<p></div>'."\r\n";
        }
    }
	//
	//age-related-ministry
	if(!empty( $whichFilters['age-related'] ) )
    {
        $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork ORDER BY youngest');
        if(!empty( $results) )
        {
            echo'<div class="filterblock"><label>'.esc_html( __('People in age related group','church-admin' ) ).'</label>';
            echo'<p><input type="checkbox" name="check[]" class="all '.$class.'" data-id="age-related" value="all" /><strong>'.esc_html( __('All','church-admin' ) ).'</strong></p>';
            echo '<p>';
            foreach( $results AS $row)
            {

                echo'<span ><input type="checkbox" name="check[]" class="'.$class.' age-related" value="ar/'.sanitize_title( $row->id).'" />'.esc_html( $row->group_name).'</span>';
                echo'<br>';
            }
            echo'<p></div>'."\r\n";
        }
    }
	/********************************************************
	*
	* Custom Fields
	*
	*********************************************************/
	$customFields=church_admin_get_custom_fields();

	if(!empty( $customFields) )
	{

		foreach ( $customFields AS $ID=>$field)
		{
            //if( $field['section']!='people')continue;
			if(!empty( $whichFilters[$field['sanitized-name']] ) )
            {
				//church_admin_debug('Print '.$field['name']);
				if(!empty($field['onboarding'])){
					$field['name'] = sprintf(__('Onboarding custom field - %1$s','church-admin'),$field['name']);
				}
				else{
					$field['name'] = sprintf(__('Custom field - %1$s','church-admin'),$field['name']);
				}
                $type=$field['type'];
				$section= $field['section'];

				//format cu/ID~type~section~value
                switch( $type)
                {
                    case'boolean';
                        $counttrue=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_custom_fields_meta'. ' WHERE  custom_id='.(int)$ID.' AND data=1 ');
                        $countfalse=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_custom_fields_meta'. ' WHERE  custom_id='.(int)$ID.' AND data=0 ');

                        echo'<div class="filterblock"><label>'. esc_html( $field['name'] ).'</label>';


                        echo'<p><span ><input type="checkbox" name="check[]" class="'.$class.' custom" value="'.esc_attr('cu/'.(int)$ID.'~bo~'.$section.'~1').'" />'.esc_html( __('Yes','church-admin' ) ).' ('.$counttrue.') </span><span ><input type="checkbox" name="check[]" class="'.$class.' custom" value="'.esc_attr('cu/'.(int)$ID.'~bo~'.$section.'~0').'" />'.esc_html( __('No','church-admin' ) ).' ('.$countfalse.')</span>';

                        echo'</div>'."\r\n";
                    break;
                    case 'date':
                        $dates=$wpdb->get_results('SELECT  `data` AS customDate FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE data!="0000-00-00" AND custom_id="'.(int)$ID.'" GROUP BY `data` ORDER BY `data` ASC');

                        if(!empty( $dates) )
                        {
                            echo'<div class="filterblock"><label>'.esc_html( $field['name'] ).'</label><p>';

                            if( $wpdb->num_rows>20)
                            {
                                echo'<select name="check[]" class="'.$class.' custom"><option>'.esc_html( __('Please choose a date','church-admin' ) ).'</option>';
                                foreach( $dates as $date)
                                {
                                    if(!empty( $date->customDate) )
                                    {
                                        echo'<option value="'.esc_attr('cu/'.(int)$ID.'~da~'.$section.'~'.sanitize_title( $date->customDate)).'" />'.mysql2date(get_option('date_format'),$date->customDate).'</option>';

                                    }
                                }
                                echo'</select>';
                            }
                            else{
                                foreach( $dates as $date)
                                {
                                    if(!empty( $date->customDate) )
                                    {
                                        echo'<span ><input type="checkbox" name="check[]" class="'.$class.' custom" value="'.esc_attr('cu/'.(int)$ID.'~da~'.$section.'~'.$date->customDate).'" />'.mysql2date(get_option('date_format'),$date->customDate).'</span>';
                                        echo'<br>';
                                    }
                                }
                            }
                            echo'</p></div>'."\r\n";
                        }
                    break;
                    case'text':
                        $sql='SELECT DISTINCT `data` AS textString FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE `data`!="" AND custom_id="'.(int)$ID.'" ORDER BY `data` ASC';
                        $texts=$wpdb->get_results( $sql);

                        if(!empty( $texts) )
                        {
                            echo'<div class="filterblock"><label>'.esc_html( $field['name'] ).'</label><p>';

                            if( $wpdb->num_rows>20)
                            {
                                echo'<select name="check[]" class="'.$class.' custom"><option>'.esc_html( __('Please choose a text string','church-admin' ) ).'</option>';
                                foreach( $texts AS $text)
                                {
                                    if(!empty( $text->textString) )
                                    {
                                        $string=substr( $text->textString,0,50);
										echo'<option value="'.esc_attr('cu/'.(int)$ID.'~da~'.$section.'~'.$date->customDate).'">'.esc_html( $string).'</option>';
                                       

                                    }
                                }
                                echo'</select>';
                            }
                            else
                            {
                                foreach( $texts AS $text)
                                {
                                    if(!empty( $text->textString) )
                                    {
                                        $string=substr( $text->textString,0,50);
                                        echo'<span ><input type="checkbox" name="check[]" class="'.$class.' custom" value="'.esc_attr('cu/'.(int)$ID.'~tx~'.$section.'~'.$string).'" />'.esc_html( $string).'</span>';
                                        echo'<br>';
                                    }
                                }	
                            }
                            echo'</p></div>'."\r\n";
                        }
                    break;
					case 'select':
					case 'radio':
					case 'checkbox':
						echo'<div class="filterblock"><label>'. esc_html( $field['name'] ).'</label>';
						$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE ID="'.(int)$ID.'"');
						if(!empty($data->options)){$options = maybe_unserialize($data->options);}
						foreach($options as $option_id=>$option){
							if($data->section=='people'){
								$count = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE data LIKE "%'.esc_sql($option).'%"');
							}
							else
							{
								$count = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_custom_fields_meta b WHERE a.household_id=b.household_id AND b.data LIKE "%'.esc_sql($option).'%"');
								$count = sprintf(__('%1$s people','church-admin'),$count);
							}
							
							echo'<p><span ><input type="checkbox" name="check[]" class="'.$class.' custom" 
							value="'.esc_attr('cu/'.(int)$ID.'~opt~'.$section.'~'.$option).'">'.esc_html( $option).' ('.esc_html($count).')</span></p>';
						}
						echo'</div>'."\r\n";
					break;
                }
            }
		}

	}
	echo'</div>'."\r\n";
	if ( empty( $email) )echo'<p><input type="checkbox" name="ca_download" value="pdf-filter">'.esc_html( __('PDF of filter results','church-admin' ) ).'</p><p><input type="checkbox" name="ca_download" value="csv-filter">'.esc_html( __('CSV of filter results','church-admin' ) ).'</p><input type="submit" value="'.esc_html( __('Download results','church-admin' ) ).'" class="button-primary" /></form>';
    echo'<div id="filtered-response"></div>'."\r\n";
	$nonce = wp_create_nonce('filter');
	if( $JSUse)echo'

	<script >
		jQuery(document).ready(function( $) {
			$("#filters .all").on("change", function()  {
				var id = $(this).attr("data-id");

				$("input."+id).prop("checked", !$("."+id).prop("checked") )
			});
		   
		   function doFilter()  {
				var sendtoall = false;
				if($("#send-to-all").is(":checked")) {sendtoall=1;}
				console.log("send to all" + sendtoall);
				var exclude = $("#exclude").val();
      			var category_list = [];
      			$("#filters :input:checked").each(function()  {
							var category = $(this).val();
        			category_list.push(category);

        		});
				$("#filters :selected").each(function()  {

        			category = $(this).val();
							console.log(category);
        			category_list.push(category);

        		});
      			var data = {
				"action": "church_admin",
				method:"filter",
				"data": category_list,
				"exclude":exclude,
				"send-to-all":sendtoall,
				"nonce": "'.$nonce.'"
				};
				console.log(data);
				$("#filtered-response").html(\'<p style="text-align:center"><img src="'.admin_url().'/images/wpspin_light-2x.gif" /></p>\');
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					$("#filtered-response").html(response);
				});
			};

			$("#filters .category").on("change",doFilter);
			$("#exclude").on("change",doFilter);
			
			$("#send-to-all").change(function(){
				console.log("send to all changed");
				doFilter();
			});
		});
	</script>
	
	'."\r\n";
}



function church_admin_filter_process()
{
	//if changes made here also update email.php

	global $wpdb;
    //$wpdb->show_errors();
	$ptypes=get_option('church_admin_people_type');
	$church_admin_marital_status=get_option('church_admin_marital_status');
	$out='';
	$group_by='';
	$bible_readings = $email_send=$spiritual_gifts=$addresses=$classes=$gdpr=$userAccount=$custom=$months=$years=$member_types=$parents=$genders=$people_types=$sites=$smallgroups=$ministries=$photo_permission=array();
	$bible_readingsSQL = $email_sendSQL=$spiritual_giftsSQL=$addressesSQL=$classesSQL=$customSQL=$userSQL=$monthSQL=$yearSQL=$marritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=$photo_permissionSQL=array();
	$gdprSQL='';
	$sql= church_admin_build_filter_sql( church_admin_sanitize($_POST['data']),NULL);
	
	if(defined('CA_DEBUG') )church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	$count=$wpdb->num_rows;
	echo'<h2>'.esc_html(sprintf( __('Filter results (%1$s people): ','church-admin' ) , $count) ).'</h2>';
	if(!empty( $results) )
	{

		echo'<table class="widefat striped wp-list-table">';
		$header='<tr><th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th>
		<th>'.esc_html( __('Delete','church-admin' ) ).'</th>
		<th>'.esc_html( __('Display whole household','church-admin' ) ).'</th>
		<th>'.esc_html( __('Active','church-admin' ) ).'</th>
		
		<th>'.esc_html( __('People type','church-admin' ) ).'</th>
		<th>'.esc_html( __('Home Phone','church-admin' ) ).'</th>
		<th>'.esc_html( __('Cell phone','church-admin' ) ).'</th>
		<th>'.esc_html( __('Email','church-admin' ) ).'</th>
		<th>'.esc_html( __('Address','church-admin' ) ).'</th>
		<th>'.esc_html( __('User Account','church-admin' ) ).'</th>
		<th>'.esc_html( __('Last updated','church-admin' ) ).'</th>
		<th>'.esc_html( __('First registered','church-admin' ) ).'</th>
		<th>'.esc_html( __('Household ID','church-admin' ) ).'</th>
		<th>'.esc_html( __('Move to different household','church-admin' ) ).'</th>
		</tr>';
		echo'<thead>'.$header.'</thead><tfoot>'.$header.'</tfoot><tbody>';
		foreach( $results AS $row)
		{
			//church_admin_debug(print_r( $row,TRUE) );
			$class=array('is-expanded');
			if ( empty( $row->show_me) )$class[]='ca-private';
			if ( empty( $row->active) )$class[]='ca-deactivated';
			if(!empty( $class) )  {$classes=' class="'.implode(" ",$class).'"';}else$classes='';
			echo '<tr '.$classes.' id="row'.(int)$row->people_id.'">';
			$name=array_filter(array( $row->first_name,$row->middle_name,$row->prefix,$row->last_name) );
			echo'<td class="ca-names column-primary" data-colname="'.esc_html( __('Name','church-admin' ) ).'">'.esc_html(implode(' ',$name) ).'</td>';
			echo'<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.$row->people_id.'&amp;household_id='.$row->household_id,'edit_people').'">'.esc_html( __('Edit','church-admin' ) ).'</a></td>';
			echo'<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_people&amp;people_id='.$row->people_id.'&amp;household_id='.(int)$row->household_id,'delete_people').'">'.esc_html( __('Delete','church-admin' ) ).'</a></td>';
			echo'<td data-colname="'.esc_html( __('Display household','church-admin' ) ).'"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display-household&amp;household_id='.$row->household_id,'display-household').'">'.esc_html( __('Display','church-admin' ) ).'</a></td>';
            if(!empty( $row->active) )  {
				$activate=__('Active','church-admin');
			}else{
				$activate=__('Inactive','church-admin');
			}
			echo'<td  data-colname="'.esc_html( __('Active','church-admin' ) ).'"><span class="activate ca-active" id="active-'.(int)$row->people_id.'">'.$activate.'</span> </td>';
			
			if(!empty( $row->people_type_id) )  {echo'<td data-colname="'.esc_html( __('People type','church-admin' ) ).'">'.esc_html( $ptypes[$row->people_type_id] ).'</td>';}else{echo'<td  data-colname="'.esc_html( __('People type','church-admin' ) ).'">&nbsp;</td>';}
			echo'<td class="ca-phone" data-colname="'.esc_html( __('Phone','church-admin' ) ).'">'.esc_html( $row->phone).'</td>';
			echo'<td class="ca-mobile"  data-colname="'.esc_html( __('Cell ','church-admin' ) ).'">'.esc_html( $row->mobile).'</td>';
			if(!empty( $row->email) )  {echo'<td class="ca-email"  data-colname="'.esc_html( __('Email','church-admin' ) ).'"><a href="mailto:'.$row->email.'">'.esc_html( $row->email).'</a></td>';}else{echo'<td>&nbsp;</td>';}
			echo'<td class="ca-addresses"  data-colname="'.esc_html( __('Address','church-admin' ) ).'"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_household&amp;household_id='.$row->household_id,'edit_household').'">';
			if(!empty( $row->address) )  {echo esc_html( $row->address);}
			else {echo __('Add Address','church-admin');}
			echo '</a></td>';
            $user='&nbsp;';
			if(!empty( $row->email) )
			{//user account only relevant for people with email
				if(!empty( $row->user_id) )
				{
					$user_info=get_userdata( $row->user_id);
					if(!empty( $user_info) )$user=$user_info->user_login;
				}
				else
				{
					//check if a user exists for this email
					$user_id=email_exists( $row->email);
					$unassigned_user=get_userdata( $user_id);
					if(!empty( $user_id) )
					{
						$user='<span class="ca_connect_user" data-peopleid="'.(int)$row->people_id.'" data-userid="'.(int)$user_id.'">'.esc_html( __('Connect','church-admin' ) ).' '.$unassigned_user->user_login.'</span>';

					}
					else
					{
						if(!empty( $row->gdpr_reason) )
						{
							$user='<span class="ca_create_user" data-peopleid="'.(int)$row->people_id.'" >'.esc_html( __('Create user account','church-admin' ) ).'</span>';
						}
						else
						{
							$user='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=single-gdpr-email&people_id='.(int)$row->people_id,'single-gdpr-email').'" >'.esc_html( __('Send GDPR confirmation email','church-admin' ) ).'</a>';
						}
						
					}
				}
			}
			echo'<td><div class="ca-names userinfo'.(int)$row->people_id.'">'.$user.'</div></td>';
            $people_updated_by = '?';
            if(!empty( $row->updated_by) )
            {
                $people_updated_by=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int) $row->updated_by.'"');
            }
            if(!empty($row->householdUpdated)){
				$updated= esc_html( sprintf(__('Person updated %1$s, Household updated %2$s by %3$s','church-admin' ) ,mysql2date(get_option('date_format'),$row->last_updated), mysql2date(get_option('date_format'),$row->householdUpdated),$people_updated_by ));
			}
			else{
				$updated='';
			}
            echo'<td  data-colname="'.esc_html( __('Last update','church-admin' ) ).'">'.$updated.'</td>';
			$first_registered=!empty($row->first_registered)?mysql2date(get_option('date_format'),$row->first_registered):'&nbsp;';
			echo'<td  data-colname="'.esc_html( __('First registered','church-admin' ) ).'">'.$first_registered.'</td>';
            echo'<td  data-colname="'.esc_html( __('Household ID','church-admin' ) ).'">'.(int)$row->household_id.'</td>';
			echo'<td  data-colname="'.esc_html( __('Move household','church-admin' ) ).'"><a class="button-secondary" onclick="return confirm(\''.esc_html(sprintf(__('Are you sure you want to move %1$s','church-admin' ) ,church_admin_formatted_name( $row) )).'\')" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=move-person&amp;people_id='.$row->people_id,'move-person').'">'.esc_html( __('Move','church-admin' ) ).'</a></td>';
			echo'</tr>'."\r\n";

		}
		echo'</tbody></table>';

	}else{echo'<p>'.esc_html( __('Your filters produced no results. Please try again.','church-admin' ) ).'</p>';}
	$connect_nonce = wp_create_nonce("connect_user");
	$create_nonce = wp_create_nonce("create_user");
	echo'<script >jQuery(document).ready(function( $) {

			$(".ca_connect_user").click(function() {
			var people_id=$(this).attr("data-peopleid");
			var data = {
			"action": "church_admin",
			"method": "connect_user",
			"people_id": people_id,
			"user_id": $(this).attr("data-userid"),
			"nonce": "'.$connect_nonce.'",
			dataType: "json"
			};console.log(data);
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response)
			{
				var data=JSON.parse(response);
				console.log("body .userinfo"+data.people_id + " "+data.login)
				$(".userinfo"+data.people_id).replaceWith(data.login);
			});

		});
        $("body").on("click",".activate",function(event)  {
			event.stopPropagation();
   			 event.stopImmediatePropagation();
			console.log("Active Triggered");
            var people_id=$(this).attr("id");
            var nonce="'.wp_create_nonce('activate').'";
            var data = {
			"action": "church_admin",
			"method": "people_activate",
			"people_id": people_id,
			"nonce": nonce,
			dataType:"json"
			};
            console.log(data);
            jQuery.post(ajaxurl, data, function(response)
			{
				var data=JSON.parse(response);
				console.log(data);
				
				$("active-"+data.id).html(data.status);
                if(data.status=="Active")
                {
                    $("body #row"+data.id).addClass("ca-activated");
                    $("body #row"+data.id).removeClass("ca-deactivated")
                    $("body #active-"+data.id).html("'.esc_html( __("Active",'church-admin' ) ).'");
                }
                else
                {
                    $("body #row"+data.id).removeClass("ca-activated");
                    $("body #row"+data.id).addClass("ca-deactivated");
                     $("body #active-"+data.id).html("'.esc_html( __("Inactive",'church-admin' ) ).'");
                    
                }
			});
			
        });
		$(".ca_create_user").click(function() {
			var people_id=$(this).attr("data-peopleid");
			var data = {
			"action": "church_admin",
			"method": "create_user",
			"people_id": $(this).attr("data-peopleid"),
			"nonce": "'.$create_nonce.'",
			dataType:"json"
			};
			console.log(data);
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response)
			{
				var data=JSON.parse(response);
				console.log("body .userinfo"+data.people_id + " "+data.login)
				$(".userinfo"+data.people_id).replaceWith(data.login);
			});
		});
			});</script>';

}


function church_admin_filter_count( $type)
{
	//if changes made here also update email.php

	global $wpdb;
	$church_admin_marital_status=get_option('church_admin_marital_status');
	$out='';
	$group_by='';
	$show_me=$bible_readings=$age_related=$email_send=$spiritual_gifts=$addresses=$classes=$gdpr=$userAccount=$custom=$months=$years=$member_types=$parents=$genders=$people_types=$sites=$smallgroups=$ministries=$photo_permission=array();
	$show_meSQL=$bible_readingsSQL=$age_relatedSQL=$email_sendSQL=$spiritual_giftsSQL=$addressesSQL=$classesSQL=$customSQL=$userSQL=$monthSQL=$yearSQL=$marritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=$photo_permissionSQL=array();
	
	$sql= church_admin_build_filter_sql( church_admin_sanitize($_POST['data']),$type);
	church_admin_debug( $sql);
	$result=$wpdb->get_results( $sql);
	$count=$wpdb->num_rows;
	if ( empty( $count) )$count='0';
	return '<strong>'.esc_html(sprintf(__('%1$s people','church-admin' ) ,$count)).'</strong>';
}
function church_admin_filter_email()
{
	church_admin_debug('*** church_admin_filter_email *** ');
	church_admin_debug($_POST);
	//if changes made here also update email.php

	global $wpdb;
	$church_admin_marital_status=get_option('church_admin_marital_status');
	$out='';
	$group_by='';
	$show_me=$bible_readings=$age_related=$email_send=$spiritual_gifts=$addresses=$classes=$member_types=$genders=$people_types=$sites=$smallgroups=$ministries=$photo_permission=array();
	$show_meSQL=$bible_readingsSQL=$age_relatedSQL=$email_sendSQL=$spiritual_giftsSQL=$addressesSQL=$classesSQL=$maritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$photo_permissionSQL=$filteredby=array();
	if(empty($_POST['data'])){return;}
	$sql= church_admin_build_filter_sql( church_admin_sanitize($_POST['data']),'email');
	church_admin_debug( $sql);
	$result=$wpdb->get_results( $sql);
	if(!empty( $result) )
    {
        $out='<h2>'.esc_html( __('Recipients (check before sending)','church-admin' ) ).'</h2><p>';
        foreach( $result AS $row)
        {
            $out.=esc_html( $row->first_name.' '. $row->last_name).' '.$row->email.'<br>';
        }
		$out.='</p>';
    }else $out='<h2>'.esc_html( __('No-one with an email selected','church-admin' ) ).'</h2>';
	return $out;
}




function church_admin_build_filter_sql( $input,$type=NULL)
	{
		//church_admin_debug($_POST);

		if(!empty($_POST['send-to-all'])&& $_POST['send-to-all']=='yes'){
			church_admin_debug('Send to all');
			$sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a,'.$wpdb->prefix.'church_admin_household b WHERE a.household_id = b.household_id';
			return $sql; 
		}


		global $wpdb,$church_admin_spiritual_gifts;
        $show_me=$bible_readings=$age_related=$email_send=$spiritual_gifts=$phoneCalls=$addresses=$classes=$active=$custom=$months=$years=$marital=$genders=$people_types=$email=$sites=$smallgroups=$ministries=$parents=$mobile=$photo_permission=array();
        $show_meSQL=$bible_readingsSQL=$age_relatedSQL=$email_sendSQL=$spiritual_giftsSQL=$phoneCallsSQL=$addressesSQL=$emailSQL=$mobileSQL=$classesSQL=$maritalSQL=$genderSQL=$memberSQL=$peopleSQL=$smallgroupsSQL=$ministriesSQL=$filteredby=$photo_permissionSQL=array();
		$church_admin_marital_status=get_option('church_admin_marital_status');
		//church_admin_debug(print_r( $input,TRUE) );
		if(!empty($input)){
			foreach( $input AS $key=>$data)
			{
				//extract posted data
				$temp=explode('/',$data);
				switch( $temp[0] )
				{
					case 'br':	if ( !empty( $temp[1] ) )  {$bible_readingsSQL=' a.people_id=(SELECT c.people_id FROM '.$wpdb->prefix.'church_admin_people_meta c WHERE  c.meta_type="bible-readings" AND c.people_id=a.people_id)';}break;
					case 'se':$email_send[]=(int)$temp[1];break;
					case 'da':	if ( empty( $temp[1] ) )  {$gdprSQL=' (a.gdpr_reason IS NULL OR a.gdpr_reason="" )';}else{$gdprSQL=' (a.gdpr_reason!="") ';}break;
					case 'ad':$addresses[]=sanitize_text_field( $temp[1] );break;
					case 'ac':	$active[]=(int)$temp[1];break;
					case 'pc':	$phoneCalls[]=(int)$temp[1];break;
					case 'cl':  $classes[]=sanitize_text_field( $temp[1] );break;
					case 'cu':	$custom[]=sanitize_text_field( $temp[1] );break;
					case 'us':	$user[]=sanitize_text_field( $temp[1] );break;
					case 'ce':	$mobile[]=sanitize_text_field( $temp[1] );break;
					case 'mo':	$months[]=(int)$temp[1];break;
					case 'ye':	$years[]=(int)$temp[1];break;
					case 'ma': $marital[]=sanitize_text_field( $temp[1] );			break;
					case 'ge': 	$genders[]=sanitize_text_field( $temp[1] );			break;
					case 'mt': 	$member_types[]=sanitize_text_field( $temp[1] );		break;
					case 'pe':	$people_types[]=sanitize_text_field( $temp[1] );		break;
					case 'em':$email[]=sanitize_text_field( $temp[1] );break;
					case 'si':	$sites[]=sanitize_text_field( $temp[1] );			break;
					case 'gp':	$smallgroups[]=sanitize_text_field( $temp[1] );		break;
					case 'mi':	$ministries[]=sanitize_text_field( $temp[1] );		break;
					case 'pa':	$parents[]=sanitize_text_field( $temp[1] );		break;
					case 'pp':	$photo_permission[]=(int)$temp[1];break;
					case 'sm':	$show_me[]=(int)$temp[1];break;
					case 'sp':$spiritual_gifts[]=(int)$temp[1];break;
					case 'ar': $age_related[] = sanitize_text_field( $temp[1] );		break;
				}
			}
		}
		church_admin_debug('parents' .print_r($parents,true));
		church_admin_debug('age related' .print_r($age_related,true));

		//create clauses for different
		if(!empty( $active)&&is_array( $active) )
		{
			foreach( $active AS $key=>$act)$activeSQL[]='a.active="'.(int)$act.'" ';
		}
        if(!empty( $phoneCalls)&&is_array( $phoneCalls) )
		{
			foreach( $phoneCalls AS $key=>$pc)$phoneCallsSQL[]='a.phone_calls="'.(int)$pc.'" ';
		}
		if(!empty( $email_send)&&is_array( $email_send) )
		{
			foreach( $email_send AS $key=>$se)$email_sendSQL[]='a.email_send="'.(int)$se.'" ';
		}
		if(!empty( $photo_permission)&&is_array( $photo_permission) )
		{
			foreach( $photo_permission AS $key=>$pp)$photo_permissionSQL[]='a.photo_permission="'.(int)$pp.'" ';
		}
		if(!empty( $show_me)&&is_array( $show_me) )
		{
			foreach( $show_me AS $key=>$sm)$show_meSQL[]='a.show_me="'.(int)$pp.'" ';
		}
		if(!empty( $custom)&&is_array( $custom) )
		{
			//format cu/ID~type~section~value
			foreach( $custom AS $key=>$cust)
			{
				$customData=explode('~',church_admin_sanitize($cust));
				
				$ID = $customData[0];
				$type = $customData[1];
				$section = $customData[2];
				$value = $customData[3];
				church_admin_debug('Custom Data');
				church_admin_debug($customData);
				switch($section){
					case 'people': $people_part = 'h.people_id=a.people_id';break;
					case 'household': $people_part = 'h.household_id=a.household_id';break;
				}
				switch( $type )
				{
					case'bo':$customSQL[]='h.`custom_id`="'.(int)$ID.'" AND h.`data`="'.esc_sql($value).'" AND '.$people_part;break;
					case'da':$customSQL[]='h.`data`="'.esc_sql( $value ).'"AND '.$people_part;break;
					case'tx': 
						$customSQL[]=' h.`data` LIKE "%'.esc_sql( $value  ).'%" AND '.$people_part;
					break;
					case 'opt':
						$customSQL[]=' h.`data` LIKE "%'.esc_sql($value ).'%" AND '.$people_part;
					break;
					
				}

			}
			
		}
		if(!empty( $email) )
		{
			foreach ( $email AS $key=>$emailAnswer)
			{
				if( $emailAnswer=='no' && !in_array(' a.email="" ',$emailSQL) )$emailSQL[]= ' a.email="" ';
				elseif(!in_array(' a.email!="" ',$emailSQL) )  {$emailSQL[]=' a.email!="" ';}	
			}
		}

        if(!empty( $addresses) )
		{
			foreach ( $addresses AS $key=>$addressAnswer)
			{
				if( $addressAnswer=='no' && !in_array(' b.address="" ',$addressesSQL) )$addressesSQL[]= ' b.address="" ';
								    elseif(!in_array(' b.address!="" ',$addressesSQL) )  {$addressesSQL[]=' b.address!="" ';}	
								}
		}
		if(!empty( $user) )
		{
			foreach ( $user AS $key=>$userAnswer)
			{
				if( $userAnswer=='no' && !in_array(' a.user_id="" ',$userSQL) )$userSQL[]= ' a.user_id="" ';
				elseif(!in_array(' a.user_id!="" ',$userSQL) )  {$userSQL[]=' a.user_id!="" ';}	
			}
		}
		if(!empty( $mobile) )
		{	
						
			foreach ( $mobile AS $key=>$mobileAnswer)
			{
				if( $mobileAnswer=='no' && !in_array('a.mobile=""',$mobileSQL) )  {$mobileSQL[]= 'a.mobile=""';}
				elseif(!in_array('a.mobile!=""',$mobileSQL) )  {$mobileSQL[]='a.mobile!=""';}	
			}
		}
				
		if(!empty( $months)&&is_array( $months) )
		{
				foreach( $months AS $key=>$month)
				{
					$monthSQL[]=' MONTH(a.date_of_birth)="'.(int)$month.'"';
				}

		}
		if(!empty( $years)&&is_array( $years) )
		{
			church_admin_debug('Years');
			//church_admin_debug($years);
			foreach( $years AS $key=>$year)
				{
					$yearSQL[]='YEAR(a.date_of_birth)="'.(int)$year.'"';
				}

		}

		if(!empty( $marital)&&is_array( $marital) )
		{
			foreach( $church_admin_marital_status AS $key=>$status)
			{
				if(in_array(sanitize_title( $status),$marital) )$maritalSQL[]='a.marital_status="'.esc_sql( $status).'"';
			}
		}
					
		if(!empty( $genders) )
		{

			$sex=get_option('church_admin_gender');
			foreach( $sex AS $key=>$gender)
			{

				if(in_array(sanitize_title( $gender),$genders) )
				{
					$genderSQL[]='(a.sex="'.(int)$key.'")';
					$filteredby[]=$gender;
				}
			}

		}

		//end gender section
		//member types
		if(!empty( $member_types)&&is_array( $member_types) )
		{

			$allmembers=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_member_types');

			if(!empty( $allmembers) )
			{
				foreach( $allmembers AS $onetype)
				{

					if(in_array(sanitize_title( $onetype->member_type),$member_types) )
					{
						$memberSQL[]='(a.member_type_id="'.(int)$onetype->member_type_id.'" AND a.member_type_id=f.member_type_id)';
						$filteredby[]=$onetype->member_type;
					}
				}
			}
		}//end member_types

		//people types
		$ptypes=get_option('church_admin_people_type');
		if(!empty( $people_types) )
		{

			if(!in_array('all',$people_types) )//only do if all not selected
			{
				$ptypes=get_option('church_admin_people_type');

				foreach( $ptypes AS $key=>$ptype)
				{
					if(in_array(sanitize_title( $ptype),$people_types) )
					{
						$peopleSQL[]='(a.people_type_id="'.(int)$key.'")';
						$filteredby[]=$ptype;
					}
				}
			}
		}//end people type section

		//sites

		if(!empty( $sites)&&is_array( $sites) )
		{
			if(!in_array('all',$sites) )//only do if all not selected
			{
				$campuses=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sites');

				if(!empty( $campuses) )
				{
					foreach( $campuses AS $campus)
					{
						if(in_array(sanitize_title( $campus->venue),$sites) )
						{
							$sitesSQL[]='(a.site_id="'.(int)$campus->site_id.'")';
							$filteredby[]=$campus->venue;
						}
					}
				}
			}
		}//end sites
		//Parents
		//$parents is array of kidswork group_id we are looking for parents of!

		$kidsworkData=church_admin_whosin_kidswork_array();
		
		$kidsworkSQL=array();
		if(!empty( $parents) && is_array( $parents) )
		{
			church_admin_debug('Kidswork Data Array');
			church_admin_debug($kidsworkData);
			church_admin_debug('Parents of these groups...');
			church_admin_debug($parents);


			foreach( $parents AS $id=>$kidswork_group_id)
			{
				church_admin_debug('Adding for kidswork group '.$kidswork_group_id);
				if(!empty( $kidsworkData[$kidswork_group_id]['children'] ) )
				{
					
						foreach( $kidsworkData[$kidswork_group_id]['children'] AS $key=>$moreData)
						{
							if(!empty($moreData['parents'])){
								foreach($moreData['parents'] AS $key=>$parent_id){
									$kidsworkSQL[]='(a.people_id="'.(int)$parent_id.'")';
								}
							}
						}
				}
			}
			church_admin_debug('KIDSWORK SQL');
			church_admin_debug($kidsworkSQL);
		}
		//age related ministry is old kidswork table with gender column added null is mixed, 0,1 etc are genders.
		$age_relatedSQL=array();
		if(!empty( $age_related) && is_array( $age_related) )
		{
				foreach( $age_related AS $key=>$id)
				{
					$row= $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_kidswork WHERE id="'.(int)$id.'"');
					if(empty($row)){continue;}
					$genSQL='';
					if(isset($row->gender)){
						switch($row->gender){
							case '0':$genSQL =' AND a.sex=0';break;
							case '1':$genSQL =' AND a.sex=1';break; 
						}
					}
					$age_relatedSQL[] = 'a.date_of_birth IS NOT NULL AND a.kidswork_override="'.(int)$id.'" OR (date_of_birth<"'.$row->youngest.'" AND date_of_birth>"'.$row->oldest.'") '.$genSQL;
					

				}
			//church_admin_debug($age_relatedSQL);
		}
		//spiritual gifts

		if(!empty( $spiritual_gifts)&&is_array( $spiritual_gifts) )
		{

			if(!in_array('all',$spiritual_gifts) )//only do if all not selected
			{
				
				if(!empty( $church_admin_spiritual_gifts) )
				{
					foreach( $church_admin_spiritual_gifts AS $gift_id=>$gift)
					{

						if(in_array(sanitize_title( $gift_id),$spiritual_gifts) )
						{
							$spiritual_giftsSQL[]='a.people_id=(SELECT c.people_id FROM '.$wpdb->prefix.'church_admin_people_meta c WHERE  c.ID="'.(int)$gift_id.'" AND c.meta_type="spiritual-gifts" AND c.people_id=a.people_id)';
							$filteredby[]=$gift;
						}
					}
				}
			}
			if(in_array('no-group',$smallgroups) )
			{
				 $spiritual_giftsSQL=array('a.people_id NOT IN (SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="spiritual-gifts")');
					}
		}//end spiritual gifts
		//small groups

		if(!empty( $smallgroups)&&is_array( $smallgroups) )
		{

			if(!in_array('all',$smallgroups) )//only do if all not selected
			{
				$sgs=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
				if(!empty( $sgs) )
				{
					foreach( $sgs AS $sg)
					{

						if(in_array(sanitize_title( $sg->group_name),$smallgroups) )
						{
							$smallgroupsSQL[]='a.people_id=(SELECT c.people_id FROM '.$wpdb->prefix.'church_admin_people_meta c WHERE  c.ID="'.(int)$sg->id.'" AND c.meta_type="smallgroup" AND c.people_id=a.people_id)';
							$filteredby[]=$sg->group_name;
						}
					}
				}
			}
			if(in_array('no-group',$smallgroups) )
			{
				 $smallgroupsSQL=array('a.people_id NOT IN (SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup")');
					}
		}//end smallgroups
        /**************************************************
        *
        *   Classes
        *
        ***************************************************/
        if(!empty( $classes)&&is_array( $classes) )
		{
            if(defined('CA_DEBUG') )church_admin_debug('Classes section');
            if(defined('CA_DEBUG') )church_admin_debug(print_r( $classes,TRUE) );
			if(!in_array('all',$classes) )//only do if all not selected
			{
				if(defined('CA_DEBUG') )church_admin_debug("Not all classes");
                $cls=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_classes');
                if(defined('CA_DEBUG') )church_admin_debug(print_r( $cls,TRUE) );
				if(!empty( $cls) )
				{
					foreach( $cls AS $cl)
					{

						if(in_array(sanitize_title( $cl->name),$classes) )
						{
							$classesSQL[]='a.people_id=(SELECT c.people_id FROM '.$wpdb->prefix.'church_admin_people_meta c WHERE  c.ID="'.(int)$cl->class_id.'" AND c.meta_type="class" AND c.people_id=a.people_id)';
							$filteredby[]=$cl->name;
						}
					}
				}
			}

		}//end classes

		//ministries
		if(!empty( $ministries)&&is_array( $ministries) )
		{
			if(!in_array('all',$ministries) )//only do if all not selected
			{
				$mins=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries');

				if(!empty( $mins) )
				{
					foreach( $mins AS $min)
					{
						if(in_array(sanitize_title( $min->ministry),$ministries) )
						{
							$ministriesSQL[]=' a.people_id=(SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta c, '.$wpdb->prefix.'church_admin_ministries g WHERE  c.ID="'.(int)$min->ID.'" AND c.ID=g.ID AND c.meta_type="ministry" AND c.people_id=a.people_id)';
							$filteredby[]=$min->ministry;
						}
					}
				}
			}
		}//end smallgroups
		$other=$tbls='';
		 $group_by=' GROUP BY a.people_id ';
		$columns=array('a.first_registered','a.pushToken','a.people_id','a.user_id','a.household_id','a.head_of_household','a.first_name','a.middle_name','a.prefix','a.last_name','a.people_type_id','a.member_type_id','a.email','a.mobile','a.e164cell','a.sex','b.phone','b.address','b.mailing_address','b.wedding_anniversary','a.date_of_birth','a.show_me','a.active','a.marital_status','a.date_of_birth','a.last_updated','b.last_updated AS householdUpdated','a.updated_by','a.gdpr_reason');
		$tables=array($wpdb->prefix.'church_admin_people'.' a',$wpdb->prefix.'church_admin_household'.' b');
		$table_header=array(esc_html(__('Edit','church-admin' ) ),
				esc_html(__('Delete','church-admin' ) ),
				esc_html(__('Activate','church-admin' ) ),
				esc_html(__('Name','church-admin' ) ),
				esc_html(__('People Type','church-admin' ) ),
				esc_html(__('Phone','church-admin' ) ),
				esc_html(__('Mobile','church-admin' ) ),
				esc_html(__('Email','church-admin' ) ),
				esc_html(__('Address','church-admin' ) ),
				esc_html(__('Site User','church-admin') ) 
			);
		if(!empty($bible_readingsSQL)) {
			
			$other.=' AND ('.$bible_readingsSQL.')';
			$tables['c']=$wpdb->prefix.'church_admin_people_meta'.' c';
		}
		if(!empty($age_relatedSQL)) {$other.=' AND ('. implode(" OR ",$age_relatedSQL).')';}
		if(!empty( $email_sendSQL) )		$other.=' AND ('. implode(" OR ",$email_sendSQL).')';
		if(!empty( $show_meSQL) )		$other.=' AND ('. implode(" OR ",$show_meSQL).')';
		if(!empty( $photo_permissionSQL) )		$other.=' AND ('. implode(" OR ",$photo_permissionSQL).')';
		if(!empty( $activeSQL) )		$other.=' AND ('. implode(" OR ",$activeSQL).')';
        if(!empty( $phoneCallsSQL) )  $other.=' AND ('. implode(" OR ",$phoneCallsSQL).')';
        if(!empty( $addressesSQL) )		$other.=' AND ('. implode(" OR ",$addressesSQL).')';
		if(!empty( $emailSQL) )		$other.=' AND ('. implode(" OR ",$emailSQL).')';
		if(!empty( $mobileSQL) )		$other.=' AND ('. implode(" OR ",$mobileSQL).')';
		if(!empty( $userSQL) )		$other.=' AND ('. implode(" OR ",$userSQL).')';
		if(!empty( $gdprSQL) )			$other.=' AND '.$gdprSQL;
		if(!empty( $maritalSQL) )		$other.=' AND ('. implode(" OR ",$maritalSQL).')';
		if(!empty( $genderSQL) ) 		$other.=' AND ('. implode(" OR ",$genderSQL).')';
		if(!empty( $peopleSQL) ) 		$other.=' AND ('. implode(" OR ",$peopleSQL).')';
		if(!empty( $maritalSQL) ) 		$other.=' AND ('. implode(" OR ",$maritalSQL).')';
		if(!empty( $kidsworkSQL) )	$other.=' AND ('. implode(" OR ",$kidsworkSQL).')';
		if(!empty( $yearSQL) )		$other.=' AND ('. implode(" OR ",$yearSQL).')';
		if(!empty( $monthSQL) )		$other.=' AND ('. implode(" OR ",$monthSQL).')';
		if(!empty( $sitesSQL) ) 		{
										$other.=' AND ('. implode(" OR ",$sitesSQL).') AND a.site_id=d.site_id';
										$tables['d']=$wpdb->prefix.'church_admin_sites'.' d';
										$columns[]='d.venue';
									}
		if(!empty( $smallgroupsSQL) ) 	{
										$other.=' AND ('. implode(" OR ",$smallgroupsSQL).') AND c.ID=e.id';
										$columns[]='e.group_name';
										$tables['c']=$wpdb->prefix.'church_admin_people_meta'.' c';
										$tables['e']=$wpdb->prefix.'church_admin_smallgroup'.' e';
									}
		if(!empty( $spiritual_giftsSQL) ) 	{
			$other.=' AND ('. implode(" OR ",$spiritual_giftsSQL).')';
			//$columns[]='e.group_name';
			$tables['c']=$wpdb->prefix.'church_admin_people_meta'.' c';
			//$tables['e']=$wpdb->prefix.'church_admin_smallgroup'.' e';
		}
        if(!empty( $classesSQL) ) 	{
										$other.=' AND ('. implode(" OR ",$classesSQL).') AND c.ID=i.class_id';
										$columns[]='i.name';
										$tables['c']=$wpdb->prefix.'church_admin_people_meta'.' c';
										$tables['i']=$wpdb->prefix.'church_admin_classes'.' i';
									}
		if(!empty( $memberSQL) ) 		{
										$other.=' AND ('. implode(" OR ",$memberSQL).')';
										$columns[]='f.member_type';
										$tables['f']=$wpdb->prefix.'church_admin_member_types'.' f';
									}
		if(!empty( $ministriesSQL) ) 	{
										$other.=' AND ('. implode(" OR ",$ministriesSQL).')';
										$columns[]='g.ministry ';
										$tables['g']=$wpdb->prefix.'church_admin_ministries'.' g';
										$tables['c']=$wpdb->prefix.'church_admin_people_meta'.' c';
									}
		if(!empty( $customSQL) )
		{

										$other.=' AND ('. implode(" OR ", $customSQL).')';
										$columns[]='h.data ';
										$tables['h']=$wpdb->prefix.'church_admin_custom_fields_meta'.' h';
		}
        if( $type=='push')$other.=' AND a.pushToken!=""  AND a.active=1';
        if( $type=='email')$other.=' AND a.email!="" AND a.email_send=1 AND a.active=1';
        if( $type=='sms')$other.=' AND a.mobile!="" AND a.sms_send=1  AND a.active=1';
		foreach( $tables AS $letter=>$table)$tbls.=', '.$table.' '.$letter;

		
		
		//handle exclude
		$premium=get_option('church_admin_payment_gateway');
		if(!empty($premium)){
			$excludeSQL='';
			$exclude = !empty($_POST['exclude'])?sanitize_text_field( stripslashes( $_POST['exclude'] ) ) : null;
			if(!empty($exclude)){
				church_admin_debug('Exclude: '.$exclude);
				$excludeArray=array();
				
				$people = church_admin_get_people_ids(stripslashes( $exclude ) );
				//church_admin_debug($people);
				foreach( $people AS $key=>$people_id)
				{
					$excludeArray[]=' (a.people_id!="'.(int)$people_id.'") ';

				}
				if(!empty($excludeArray))
				{
					//church_admin_debug($excludeArray);
					$excludeSQL.=' AND '.implode(' AND ',$excludeArray);
				}
				if(empty($excludeSQL)){
					church_admin_debug( 'ExcludeSQL is: '.$excludeSQL);
				}
			}
		}
		$sql='SELECT '.implode(", ",$columns).' FROM '.implode(", ",array_filter( $tables) ).' WHERE a.household_id=b.household_id '.$other.' '.$excludeSQL.' '.$group_by.' ORDER BY a.last_name,a.people_order';
		
		church_admin_debug('Filter sql function output....');
		church_admin_debug( $sql);
		
		return $sql;
	}
