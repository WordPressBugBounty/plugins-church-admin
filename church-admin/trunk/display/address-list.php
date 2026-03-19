<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_frontend_directory( $member_type_ids=NULL,$map=0,$photo=0,$api_key=NULL,$kids=TRUE,$site_id=null,$updateable=1,$first_initial=0,$cache=1,$vcf=0,$address_style='one')
{
    global $wpdb;

		//sanitize and validate
		$map = !empty($map) ? 1:0;
		$photo = !empty($photo) ? 1:0;
		$kids = !empty($kids) ? 1:0;
		$site_id = !empty($site_id) ? (int)$site_id : null;
		$updateable = !empty($updateable)? 1:0;
		$first_initial = !empty($first_initial)? 1:0;
		$cache = !empty($cache)? 1:0;
		$vcf = !empty($vcf)? 1:0;
		
		//member_type_id sorted below
		///$api_key no longer used
	



    //only allow people with the same member_types to view
    //church_admin_debug('***** church_admin_frontend_directory ******');
    //church_admin_debug('Member types ids '.$member_type_ids);
	$params = array('member_type_ids'=>$member_type_ids,'map'=>$map,'photo'=>$photo,'api_key'=>$api_key,'kids'=>$kids,'site_id'=>$site_id,'first_initial'=>$first_initial,'cache'=>$cache,'vcf'=>$vcf,'address_style'=>$address_style);
    church_admin_debug($params);
	
	/******************************************
	 * Handle member type 
	 *******************************************/
	$sql_safe_memb_sql='';
	$memb=$membsql=$sitesql=array();
	
	if ( empty( $member_type_ids)||$member_type_ids==__('All','church-admin')||$member_type_ids=="#")
	{
		//dont set the $memb_sql par of the queries if no member type given or set to all
		$membsql=array(); $memb_sql="";
	}
	elseif( $member_type_ids!="")
	{
		$memb=explode(',',$member_type_ids);
		foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.(int)$value;}
		if(!empty( $membsql) ) {
			$sql_safe_memb_sql=' ('.implode(' || ',$membsql).')';
		}
	}


	$out='';
	//if(is_admin() )$out.=church_admin_fix_directory_issues( $memb);
	$output='<div class="church-admin-address-search"><form name="ca_search" action="'.esc_url(get_permalink() ).'" method="POST"><p><input name="ca_search" class="ca-search-field" type="text" placeholder="'.esc_html( __('Search','church-admin') ).'" /><input class="ca-search-submit" type="submit" value="'.esc_html( __('Search','church-admin') ).'" />';
  	$output.='<input type="hidden" name="ca_search_nonce" value="'.esc_attr(wp_create_nonce('ca_search_nonce')).'" />';
  	$output.='</p></form></div>';
	if(!empty( $_POST['ca_search'] ) )
    {
	  	/**********************************************************************
	  	*
	  	* If a search has happened, replace the output with the search
	  	*
	  	***********************************************************************/
			$sql_safe_search=esc_sql(sanitize_text_field( stripslashes($_POST['ca_search'] ) ) );
      		//$sql='SELECT DISTINCT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE (first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||email LIKE("%'.$s.'%")||mobile LIKE("%'.$s.'%") )';
            $sql='SELECT DISTINCT(a.household_id), UPPER(LEFT(last_name,1) ) AS letter FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.active=1 AND a.show_me=1 AND ( CONCAT_WS(" ",a.first_name,a.last_name) LIKE("%'.$sql_safe_search.'%")||CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) LIKE("%'.$sql_safe_search.'%")||a.first_name LIKE("%'.$sql_safe_search.'%")||a.last_name LIKE("%'.$sql_safe_search.'%")||a.nickname LIKE("%'.$sql_safe_search.'%")||a.email LIKE("%'.$sql_safe_search.'%")||a.mobile LIKE("%'.$sql_safe_search.'%") ||b.address LIKE("%'.$sql_safe_search.'%")  || b.phone LIKE ("%'.$sql_safe_search.'%") ) AND a.gdpr_reason IS NOT NULL AND a.show_me=1 AND a.active=1 ';
	  		
			if(!empty( $sql_safe_memb_sql) ) $sql.=' AND '.$sql_safe_memb_sql;
     		
      		$results=$wpdb->get_results( $sql);
      		if(!empty( $results) )
      		{
      			$count=$wpdb->num_rows;
				$out='<h2>'.esc_html(sprintf(__('Your search for "%1$s" yielded %2$s result(s) ','church-admin' ) ,esc_html( sanitize_text_field(stripslashes($_POST['ca_search']) )),(int)$count)).'</h2>';	
				foreach( $results AS $row)
				{
					$data=church_admin_people_data( (int)$row->household_id);
					//get privacy for head of household
					$privacy=$wpdb->get_var('SELECT privacy FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$row->household_id.'"');
					$data['address-privacy']=$privacy;
					$out.='<div class="ca_search_result">'.church_admin_formatted_household( $data,$map,$updateable,$photo).'</div>';
 	 			}
 	 		}
			else{
				$out = '<p>'.esc_html(sprintf(__('"%1$s" not found','church-admin'),stripslashes($sql_safe_search)) ).'</p>';
			}
 	 		$output.=$out;	
 	}
	else
	{
	

  		//set up variables
  		$api_key=get_option('church_admin_google_api_key');
  		
  		$lettersOutput='';
  		$addresEntry='';
 	 	/**************************************************************************
		*
 	 	*	Grab people head of household ordered into a multi-dimensional array
  		*	1st key is First Letter
  		*	2nd key is order key
  		*	3rd array is household data
  		**************************************************************************/
  		$directory=array();
  		/**************************************************************************
		*
		* Build Query to get household_id of relevant people in alphabetic order
		*
		**************************************************************************/
  		
		$site_sql='';
		if( $site_id!=0)
  		{
  			$sites=explode(',',$site_id);
    	  	foreach( $sites AS $key=>$value)  {if(church_admin_int_check( $value) )  $sitesql[]='a.site_id='.$value;}
    	  	if(!empty( $sitesql) ) {$site_sql=' ('.implode(' || ',$sitesql).')';}
		}

		$sql='SELECT UPPER(LEFT(a.last_name,1) ) AS letter,a.*, a.household_id,b.* FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_household b ON a.household_id=b.household_id WHERE a.active=1 AND a.head_of_household=1 AND a.show_me=1 AND a.gdpr_reason IS NOT NULL AND a.active=1 ';
		//$sql='SELECT UPPER(LEFT(last_name,1) ) AS letter,last_name,household_id FROM '.$wpdb->prefix.'church_admin_people a WHERE a.head_of_household=1 AND a.show_me=1 AND a.active=1 ';
		if(!empty( $sql_safe_memb_sql)) $sql.=' AND ';
		$sql.=$sql_safe_memb_sql;
		if(!empty( $site_sql) )$sql.=' AND ';
		$sql.=esc_sql($site_sql);
		$sql.='  ORDER BY letter, a.last_name,a.first_name ASC';
		
        church_admin_debug( $sql);
  		$results=$wpdb->get_results( $sql);
        if(!empty( $results) )
        {   foreach( $results AS $row)
            {
                if(ctype_alpha( $row->letter) )
                {
                    $directory[$row->letter][]=church_admin_people_data( $row->household_id);
                }
            }
        }
		else
		{

			$output = '<p>'.__('No one in directory viewable yet','church-admin').'</p>';
			return $output;
		}



  		/**************************************************************************
		*
		* 	Build Output
  		*
  		**************************************************************************/

  		if(!empty( $directory) )
  		{
  			$i=0;
            $householdIndex=0;
  			foreach( $directory AS $letter=>$data)
  			{
  				
  				if( $i==0)  {$highlighted="church-admin-highlighted"; $firstData=$data;}else{$highlighted='';}
  				$lettersOutput.='<div class="church-admin-letter  '.$highlighted.'" id="'.esc_html( $letter).'" data-firstid="'.(int)$data[0]['household_id'] .'"><span class="church-admin-item">'.esc_html( $letter).'</span></div><div class="letterNames letter-'.esc_html($letter).'" >';

  				foreach( $data AS $household)
  				{
					
					//church_admin_debug($household);
  					if( $i==0)  {$style='style="display:block"';}else{$style='style="display:none"';}
  					if( $householdIndex==0)  {$highlightedName="church-admin-highlighted-name";}else{$highlightedName='';}
  					$firstInitial = "";
  					if( !empty($household['first_name']) && $first_initial == 1) {
  						$firstInitial = ", ";
  						if(strlen( $household['first_name'] )>0) {
  							$firstInitial = $firstInitial.substr( $household['first_name'],0,1);
  						}
  						else {
  							$firstInitial = $firstInitial.$household['first_name'];
  						}
  					}
                    
  					$lettersOutput.='<div '.$style.' class="church-admin-directory-name ca-names letter-'.$letter.' '.$highlightedName.'" id="index'.$householdIndex.'" data-id="'.intval( $household['household_id'] ).'" ><span class="church-admin-name-item">'.$household['last_name'].$firstInitial.'</span></div>';
  					$householdIndex++;
  				}
  				$lettersOutput.='</div>';
  				if( $i==0)$directoryEntry=church_admin_formatted_household( $data[0],$map,$updateable,$photo,$vcf,$address_style);
  				$i++;

 	 		}


	  	}
	  	else
        {
		
            $directoryEntry=__('No directory results with the parameters given','church-admin');
			if(church_admin_level_check($directory)){
			
				return '<h2>'.esc_html($directoryEntry).'</h2>'.print_r($params,TRUE).church_admin_address_list_issues_fixer( $memb);
			}
			else{
				return $directoryEntry;
			}
        }
 	 	
 	 	/**********************************************************************
	  	*
	  	* Create Normal Output
	  	*
	  	***********************************************************************/
 	 		
 	 		$out.="<div class=\"church-admin-new-directory\">\r\n";
 	 		$out.="<div class=\"church-admin-letters\">\r\n";
 	 		$out.=$lettersOutput;
 	 		$out.="</div><!-- .church-admin-letters -->\r\n";
	  		$out.="<div class=\"church-admin-address-entry\">\r\n";
 	 		if(!empty( $directoryEntry) )$out.=$directoryEntry;
 	 		$out.="</div><!-- .church-admin-address-entry -->\r\n";
  			$out.="</div><!-- .church-admin-new-directory -->\r\n";
			

	
	$output.=$out;
	//jQuery AJAX magic  needs to be fresh every time for nonce
	
	$nonce=wp_create_nonce( "show-person" );
  	$output.='<script>jQuery(document).ready(function( $) {$(".church-admin-letter").click(function()  {$(".letterNames").hide();var id=$(this).attr("id"); $(".letter-"+id).show();var first_id=$(this).data("firstid");var data = {"action":  "church_admin","method": "show-person","vcf":"'.esc_attr($vcf).'",security: "'.esc_attr($nonce).'","map":"'.esc_attr($map).'","photo":"'.esc_attr($photo).'","updateable":"'.esc_attr($updateable).'","address_style":"'.esc_attr($address_style).'","id":first_id};console.log(data); $.ajax({url: ajaxurl,type: "post",data:data,success: function( response )  {console.log(response); $(".church-admin-address-entry").html(response);},});}); $(".church-admin-directory-name").click(function()  {$(".church-admin-directory-name").removeClass("church-admin-highlighted-name");var household_id=$(this).data("id"); $(this).addClass("church-admin-highlighted-name");var data = {"action":  "church_admin","method": "show-person",security: "'.esc_attr($nonce).'","map":"'.esc_attr($map).'","updateable":"'.esc_attr($updateable).'","photo":"'.esc_attr($photo).'","address_style":"'.esc_attr($address_style).'","id":household_id}; $.ajax({url: ajaxurl,type: "post",data:data,success: function( response ) {$(".church-admin-address-entry").html(response);},});});});</script>';
	
	}
    //if(defined('CA_DEBUG') )//church_admin_debug( $output);
	return $output;
}


function church_admin_people_data( $household_id)
{
	global $wpdb;
	$peopleResult=$first_names=$last_names=$directory_names=$adults=$children=array();
	$directory=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"','ARRAY_A');
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" AND active=1 and show_me=1 AND gdpr_reason IS NOT NULL ORDER BY head_of_household DESC,people_order';
  	$peopleResult=$wpdb->get_results( $sql, ARRAY_A);

  	if(!empty( $peopleResult) )
  	{
		$use_title = get_option('church_admin_use_titles');
		//church_admin_debug('Use titles '.$use_title);
  		foreach( $peopleResult AS $row)
  		{
			if(!empty($use_title) && !empty($row['title']) && $row['title']!=__('Choose title','church-admin')){$title = $row['title'];}else{$title = '';}
  			if( $row['head_of_household']==1){
				$directory['last_name']=$row['last_name'];
				$directory['address-privacy']=$row['privacy'];
				$title_people_type_id = $row['people_type_id'];
			}
  			if( $row['people_type_id']== $title_people_type_id)
  			{
  				$first_names[]=$title.' '.$row['first_name'];
				$last_names[]=implode(" ",array_filter(array( $row['prefix'],$row['last_name'] ) ));
				if(!empty( $row['nickname'] ) )  {$nickname='('.$row['nickname'].')';}else{$nickname="";}
  				$row['name']=implode(" ",array_filter(array( $title,$row['first_name'],$row['middle_name'],$nickname,$row['prefix'],$row['last_name'] ) ));
  				$adults[]=$row;
  			}
  			else
  			{
				if(!empty( $row['nickname'] ) )  {$nickname='('.$row['nickname'].')';}else{$nickname="";}
  				$row['name']=implode(" ",array_filter(array( $title,$row['first_name'],$row['middle_name'],$nickname,$row['prefix'],$row['last_name'] ) ));
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
  			for ( $x=0; $x<count( $last_names); $x++)$directory_names[]=$first_names[$x].' '.$last_names[$x];

  			$directory['directory_name']=implode(" &amp; ",$directory_names);

  		}
  		if ( empty( $directory['last_name'] ) )$directory['last_name']=$peopleResult[0]['last_name'];//no head of household set
  		$directory['first_name'] = $peopleResult[0]['first_name'];
  		$directory['adults']=$adults;
  		$directory['children']=$children;
		$directory['household_id']=(int)$household_id;
		
  	}

  	return $directory;
}

function church_admin_formatted_household( $data=NULL,$map=0,$updateable=TRUE,$photo=1,$vcf=0,$address_style='one')
{
	global $wpdb;
    //church_admin_debug('******* church_admin_formatted_household $data array *********');
	//church_admin_debug(print_r( $data,TRUE) );
	//church_admin_debug(array_keys($data));
	$custom_fields=church_admin_get_custom_fields();
	$out='';
    
	$out.='<div class="church-admin-household-title ca-names">'.esc_html( $data['directory_name'] ).'</div>';
    $out.='<div class="church-admin-address-entry-content">';
	/**************************************************************************
	*
	* 	Image and Map
  	*
  	**************************************************************************/

	//$out.='<div class="church-admin-display-household-image">';
	if(!empty( $data['attachment_id'] )&& $photo )
	{
		$household_image_attributes=wp_get_attachment_image_src( (int)$data['attachment_id'],'medium','' );
		if ( $household_image_attributes )
        {
            $out.='<img alt="'.esc_attr( $data['directory_name'] ).'" src="'.esc_url($household_image_attributes[0]).'" width="'.esc_attr($household_image_attributes[1]).'" height="'.esc_attr($household_image_attributes[2]).'" class="rounded church-admin-display-45" />';
        }

	}
	$privacy=maybe_unserialize($data['address-privacy']);
	$api_key=get_option('church_admin_google_api_key');
	if(!empty( $api_key)&&!empty( $map)&&!empty( $data['lng'] )&!empty( $data['address'] ) && !empty($privacy['show-address']))
	{
		$api='';

			if(!empty( $api_key) )$api='key='.$api_key;
			if(!empty( $household_image_attributes[1] ) )
            {
                $size=$household_image_attributes[1].'x'.$household_image_attributes[2];
                $mapSize=' width="'.esc_attr($household_image_attributes[1]).'" height="'.esc_attr($household_image_attributes[2]).'"';
            }
            else 
            {
                $size='300x225';   
                $mapSize=' width="300" height="225"';
            }
            $url='https://maps.google.com/maps/api/staticmap?'.esc_attr($api).'&center='.esc_attr($data['lat']).','.esc_attr($data['lng']).'&zoom=15&markers=color:blue%7C'.esc_attr($data['lat']).','.esc_attr($data['lng']).'&size='.esc_attr($size);

			$map_url=esc_url( $url);


			$out.='<a href="'.esc_url('https://maps.google.com/maps?q='.esc_attr($data['lat']).','.esc_attr($data['lng']).'&amp;t=m&amp;z=16').'"><img src="'.esc_url($map_url).'" '.esc_attr($mapSize).' class="rounded church-admin-display-45" alt="Map" /></a>'."\r\n\t";

	}
	//$out.='</div><!--church-admin-display-household-image-->'."\r\n\t";
	/**************************************************************************
	*
	* 	Name & Address
  	*
  	**************************************************************************/
	
	$out.='<div class="church-admin-display-address-details">';
	if(!empty( $data['address'] ) && !empty($privacy['show-address']))
	{
		if( $address_style=='multi')$data['address']=str_replace(', ',',<br>',esc_html( $data['address'] ) );
		$out.='<div id="street-address">'.esc_html( __('Street Address','church-admin') ).'<br><span class="ca-addresses"> '.wp_kses_post(($data['address'])).'</span></div>';
	}
    if(!empty( $data['mailing_address'] ) && !empty($privacy['show-address']))
	{
		if( $address_style=='multi')$data['mailing_address']=str_replace(', ',',<br>',esc_html( $data['mailing_address'] ) );
		$out.='<div>'.esc_html( __('Mailing Address','church-admin') ).'<br><span class="ca-addresses"> '.wp_kses_post($data['mailing_address']).'</span></div>';
	}
	
	if(!empty($privacy['show-address'])){$out.=church_admin_what_three_words( $data,$wpdb->prefix.'church_admin_household');}
	
	
	if(!empty( $data['phone'] ) && !empty($privacy['show-landline']) ){
		$out.='<div><label>'.esc_html( __('Phone','church-admin') ).':</label><span class="ca-mobile">  '.esc_html( $data['phone'] ).'</span></div>';
	}
	//household custom fields
	foreach( $custom_fields AS $ID=>$field)
	{
		if( $field['section']!='household') continue;
		if( $field['show_me']!=1) continue;

		//note people_id on the $wpdb->prefix.'church_admin_custom_fields_meta' can have the value of household_id!
		$thisData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$ID.'" AND household_id="'.(int)$data['household_id'].'"');
		switch( $field['type'] )
		{
			case 'boolean':
				if(!empty( $thisData->data) )  {$customOut=__('Yes','church-admin');}else{$customOut=__('No','church-admin');}
			break;
			case 'date':
				if(!empty( $thisData->data) )  {$customOut=mysql2date(get_option('date_format'),$thisData->data);}else{$customOut="";}
			break;
			case 'checkbox':
				if(!empty($thisData->data)){
					$checkboxData = maybe_unserialize($thisData->data);
					$customOut = implode(", ",$checkboxData);

				}
				else{
					$customOut="";
				}
			break;
			default:
				if(!empty( $thisData->data) )  {$customOut=esc_html( $thisData->data);}else{$customOut="";}
			break;
		}
		if(!empty( $customOut) )$out.='<strong>'.esc_html( $field['name'] ).':</strong> '.$customOut.'<br>';
	}
	
	$out.='</div>';

		
	/**************************************************************************
	*
	* 	Adults
  	*
  	**************************************************************************/
	$use_title = get_option('church_admin_use_titles');
	//church_admin_debug("***** ADULTS ******");
	//church_admin_debug($data);
	foreach( $data['adults'] AS $adult)
	{

		$adultprivacy=maybe_unserialize($adult['privacy']);
		$buildAdult=$person_image='';
        $buildAdult='<div class="church-admin-address-list-name church-admin-clear-right">';
		if(!empty( $adult['attachment_id'] ) && $photo && (!empty( $adult['photo_permission'] ) ))
		{
			$check = church_admin_check_image_exists( $adult['attachment_id']);
			if($check)
			{
				$image=wp_get_attachment_image_src( $adult['attachment_id'],'thumbnail');
				if(!empty( $image[0] ) )$person_image='<img src="'.esc_url($image[0]).'" width="'.esc_attr($image[1]).'" height="'.esc_attr($image[2]).'" alt="'.esc_attr( $adult['name'] ).'" class="church-admin-person-image rounded" /><br/>';

			}
			
			
			
		}
		$buildAdult.='<p>';
		if(!empty($data['title'])&&!empty($use_title)){$title = $data['title'].' ';}else{$title='';}
		
		$buildAdult.=$person_image.'<strong>'.esc_html( $title.$adult['name'] ).'</strong><br>';
			
		if(!empty( $adult['email'] ) && !empty($adultprivacy['show-email']) )$buildAdult.='<a class="ca-email" href="'.esc_url('mailto:'.antispambot($adult['email'] )).'">'.esc_html( antispambot($adult['email'] )).'</a><br>';
		if(!empty( $adult['mobile'] ) && !empty($adultprivacy['show-cell']))$buildAdult.='<a class="ca-mobile" href="tel:'.esc_html(str_replace(" ","",$adult['mobile'] ) ).'">'.esc_html( $adult['mobile'] ).'</a><br>';
		
		//custom fields
		
		foreach( $custom_fields AS $ID=>$field)
		{
			if( $field['section']!='people') continue;
			if( $field['show_me']!=1) continue;
			$thisData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$ID.'" AND people_id="'.(int)$adult['people_id'].'"');
			switch( $field['type'] )
			{
				case 'boolean':
					if(!empty( $thisData->data) )  {$customOut=__('Yes','church-admin');}else{$customOut=__('No','church-admin');}
				break;
				case 'date':
					if(!empty( $thisData->data) )  {$customOut=mysql2date(get_option('date_format'),$thisData->data);}else{$customOut="";}
				break;
				case 'checkbox':
					if(!empty($thisData->data)){
						$checkboxData = maybe_unserialize($thisData->data);
						$customOut = implode(", ",$checkboxData);
	
					}
					else{
						$customOut="";
					}
				break;
				default:
					if(!empty( $thisData->data) )  {$customOut=esc_html( $thisData->data);}else{$customOut="";}
				break;
			}
			if(!empty( $customOut) )$buildAdult.=esc_html( $field['name'] ).': '.$customOut.'<br>';
		}
		$buildAdult.='</p>';
        $buildAdult.='</div>';
		//church_admin_debug($buildAdult);
		if(!empty( $buildAdult) )$out.=wp_kses_post($buildAdult);//.'<br style="clear:left" />';
	}
	/**************************************************************************
	*
	* 	Children
  	*
  	**************************************************************************/
	  //church_admin_debug("***** CHILDREN ******");
	  //church_admin_debug($data['children']);
	if(!empty( $data['children'] ) )
	{
		//church_admin_debug('THERE ARE CHILDREN');
		foreach( $data['children'] AS $key => $child)
		{
			$out.='<div class="church-admin-address-list-name church-admin-clear-right">';
			$childprivacy=maybe_unserialize($child['privacy']);
			if(!empty( $child['attachment_id'] ) && (!empty( $child['photo_permission'] ) ))
			{
				$check = church_admin_check_image_exists( $adult['attachment_id']);
				if($check)
				{
					$image=wp_get_attachment_image_src( $child['attachment_id'],'thumbnail');
					
					$out.='<image src="'.esc_url($image[0]).'" width="'.esc_attr($image[1]).'" height="'.esc_attr($image[2]).'" alt="'.esc_html( $child['name'] ).'" class="church-admin-person-image rounded" />'; 
					
				}
			}
			$out.='<p>';
			if(is_array( $data['children'] )&&count( $data['children'] )>=1)$out.='<p class="ca-names"><strong>'.esc_html( $child['name'] ).'</strong><br>';
			if(!empty( $child['email'] )  && !empty($childprivacy['show-email']))$out.='<a  class="ca-email" href="'.esc_url('mailto:'.antispambot($child['email']) ).'">'.esc_html( antispambot($child['email'])).'</a><br>';
			if(!empty( $child['mobile'] ) && !empty($childprivacy['show-cell']))$out.='<a  class="ca-mobile" href="tel:'.esc_html(str_replace(" ","",$child['mobile'] ) ).'">'.esc_html( $child['mobile'] ).'</a><br>';
			
			foreach( $custom_fields AS $ID=>$field)
			{
				if( $field['section']!='people') continue;
				if( $field['show_me']!=1) continue;
				$thisData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$ID.'" AND people_id="'.(int)$adult['people_id'].'"');
				switch( $field['type'] )
				{
					case 'boolean':
						if(!empty( $thisData->data) )  {$customOut=__('Yes','church-admin');}else{$customOut=__('No','church-admin');}
					break;
					case 'date':
						if(!empty( $thisData->data) )  {$customOut=mysql2date(get_option('date_format'),$thisData->data);}else{$customOut="";}
					break;
					case 'checkbox':
						if(!empty($thisData->data)){
							$checkboxData = maybe_unserialize($thisData->data);
							$customOut = implode(", ",$checkboxData);
		
						}
						else{
							$customOut="";
						}
					break;
					default:
						if(!empty( $thisData->data) )  {$customOut=esc_html( $thisData->data);}else{$customOut="";}
					break;
				}
				if(!empty( $customOut) )$out.=esc_html( $field['name'] ).': '.esc_html($customOut).'<br>';
			}
			$out.='</p></div>';
			//$out.='<br style="clear:left;" />';//added clear float 2018-04-09
		}
	}
	/**************************************************************************
	*
	* 	Edit stuff
  	*
  	**************************************************************************/

	if( $updateable && is_user_logged_in() )
	{
		$user=wp_get_current_user();
		$household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
		//church_admin_debug("Household ID $household_id");
		//church_admin_debug( $data);
		if(church_admin_level_check('Directory')||$household_id==$data['household_id'] )
		{
			//church_admin_debug('Ok for edit link');
			$page_id=church_admin_register_page_id();
			if(!empty( $page_id) )
			{
				$out.='<p>&nbsp;<a style="text-decoration:none" title="'.esc_html( __('Edit Entry','church-admin') ).'" href="'.esc_url( add_query_arg( 'household_id',$data['household_id'] ,get_permalink( $page_id) ) ).'"><span class="ca-dashicons dashicons dashicons-edit"></span>'.esc_html( __('Edit Entry','church-admin') ).'</a></p>';
			}else
			{
				$out.='<p>&nbsp;<a style="text-decoration:none" title="'.esc_html( __('Edit Entry','church-admin') ).'" href="'.wp_nonce_url(esc_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=display-household&amp;household_id='.(int)$data['household_id'],'display-household')).'"><span class="ca-dashicons dashicons dashicons-edit"></span> '.esc_html( __('Edit Entry','church-admin') ).'</a></p>';
			}
		}
	}

		if( $vcf)
        {
            //if(defined('CA_DEBUG') )//church_admin_debug('Output VCF link for '.$data['household_id'] );
            $out.='<p><a  rel="nofollow" style="text-decoration:none" title="'.esc_html( __('Download Vcard','church-admin') ).'" href="'.wp_nonce_url(home_url().'/?ca_download=vcf&amp;id='.(int)$data['household_id'],(int)$data['household_id'] ).'"><span class="ca-dashicons dashicons dashicons-index-card"></span> VCF</a> Last Updated: '.esc_html(mysql2date(get_option('date_format'),$data['last_updated'] ) ).'</p>'."\r\n\t".'<!--church_admin_vcard-->'."\r\n";
        }
    $out.='</div>';
	return $out;

}

