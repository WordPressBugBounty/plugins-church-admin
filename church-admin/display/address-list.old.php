<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_frontend_directory( $member_type_id=NULL,$map=NULL,$photo=NULL,$api_key=NULL,$kids=TRUE,$site_id=0,$updateable=1)
{

	//sanitize and validate
	$map = !empty($map) ? 1:0;
	$photo = !empty($photo) ? 1:0;
	$kids = !empty($kids) ? 1:0;
	$site_id = !empty($site_id) ? (int)$site_id : 1;
	$updateable = !empty($updateable)? 1:0;
	//member_type_id sorted below
	///$api_key no longer used

	//updte 2014-04-16 to validate and contain microdata
	//update 2014-03-19 to allow for multiple surnames
	$api_key=get_option('church_admin_google_api_key');
  global $wpdb;
  //$wpdb->show_errors();
  $out='';

  $out.='<form name="ca_search" action="" method="POST"><p><label>'.esc_html( __('Search','church-admin' ) ).'</label><input name="ca_search" type="text" /><input type="submit" value="'.esc_html( __('Go','church-admin' ) ).'" />';
  $out.='<input type="hidden" name="ca_search_nonce" value="'.wp_create_nonce('ca_search_nonce').'" />';
  $out.='</p></form>';
  	$memb_sql='';
  	$membsql=$sitesql=array();
  	if( $member_type_id=="#"||empty( $member_type_id) )  {$memb_sql="";}
  	elseif( $member_type_id!="")
  	{
  		$memb=explode(',',$member_type_id);
      	foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='member_type_id='.(int)$value;}
      	if(!empty( $membsql) ) {$memb_sql=' ('.implode(' || ',$membsql).')';}
	}
	$site_sql='';
	if( $site_id!=0)
  	{
  		$sites=explode(',',$site_id);
      	foreach( $sites AS $key=>$value)  {if(church_admin_int_check( $value) )  $sitesql[]='site_id='.(int)$value;}
      	if(!empty( $sitesql) ) {$site_sql=' ('.implode(' || ',$sitesql).')';}
	}

	if ( empty( $_POST['ca_search'] ) )
    {
		$limit='';

      //build query adding relevant member_types and sites
      $sql='SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1  ';
	  if(!empty( $memb_sql)||!empty( $site_sql) ) $sql.=' AND ';
	  $sql.=$memb_sql;
	  if(!empty( $memb_sql)&&!empty( $site_sql) )$sql.=' AND ';
	  $sql.=$site_sql;
	  $sql.='   ORDER BY last_name ASC ';

	  //execute query...
      $results=$wpdb->get_results( $sql);
      $items=$wpdb->num_rows;
      // number of total rows in the database
      require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.class.php');
      if( $items > 0)
      {
	  $p = new caPagination;
	  $p->items( $items);

	  $page_limit=get_option('church_admin_pagination_limit');
	  if ( empty( $page_limit) )  {$page_limit=20;update_option('church_admin_pagination_limit',20);}
	  $p->limit( $page_limit); // Limit entries per page

	  $p->target(get_permalink() );
	  $current_page = !empty($_GET['page']) ? (int)$_GET['page']:1;
              
	  $p->currentPage( $current_page); // Gets and validates the current page
	  $p->calculate(); // Calculates what to show
	  $p->parameterName('paging');
	  $p->adjacents(1); //No. of page away from the current page
	  if(!isset( $_GET['paging'] ) )
	  {
	      $p->page = 1;
	  }
	  else
	  {
	      $p->page = $_GET['paging'];
	  }
	  //Query for limit paging
	  $limit = " LIMIT " . ( $p->page - 1) * $p->limit  . ", " . $p->limit;


	  // Pagination
		$out.= '<div class="tablenav"><div class="tablenav-pages">';
        $out.= $p->getOutput();
        $out.= '</div></div>';
      //Pagination
      }
     //build query adding relevant member_types and sites
      $sql='SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1  ';
	  if(!empty( $memb_sql)||!empty( $site_sql) ) $sql.=' AND ';
	  $sql.=$memb_sql;
	  if(!empty( $memb_sql)&&!empty( $site_sql) )$sql.=' AND ';
	  $sql.=$site_sql;
	  $sql.='   ORDER BY last_name ASC ';
	  $sql.=$limit;
	  $results=$wpdb->get_results( $sql);
    }
    else
    {//search form
      $s=esc_sql(sanitize_text_field( $_POST['ca_search'] ) );
      $sql='SELECT DISTINCT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE (first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||email LIKE("%'.$s.'%")||mobile LIKE("%'.$s.'%") )';
	  if(!empty( $memb_sql) ) $sql.=' AND '.$memb_sql;
     	if(!empty( $limit) )$sql.=$limit;
      $results=$wpdb->get_results( $sql);
      if(!$results)
      {
        $sql='SELECT DISTINCT household_id FROM '.$wpdb->prefix.'church_admin_household WHERE address LIKE("%'.$s.'%")||phone LIKE("%'.$s.'%") AND privacy=0 ';
		if(!empty( $memb_sql) ) $sql.=' AND '.$memb_sql;
        $sql.=$limit;
		$results=$wpdb->get_results( $sql);
      }
    }

  foreach( $results AS $ordered_row)
  {
      $address=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.esc_sql( $ordered_row->household_id).'"');
	  if ( empty( $address->privacy) )
      {
      	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.esc_sql( $ordered_row->household_id).'"  AND active="1" ORDER BY people_order ASC, people_type_id ASC,sex DESC';
		$people_results=$wpdb->get_results( $sql);
		$first_names=$adults=$children=$emails=$mobiles=$photos=array();
		$last_name='';
		$x=0;
		foreach( $people_results AS $people)
		{

			//build first part of name
			$name=$people->first_name.' ';
			$middle_name=get_option('church_admin_use_middle_name');
			if(!empty( $middle_name)&&!empty( $people->middle_name) )$name.=$people->middle_name.' ';
			$nickname=get_option('church_admin_use_nickname');
			if(!empty( $nickname)&&!empty( $people->nickname) )$name.='('.$people->nickname.') ';
			//last name
			$prefix=get_option('church_admin_use_prefix');
			if(!empty( $prefix) &&!empty( $people->prefix) )  {	$Prefix=$people->prefix.' ';}else{$Prefix='';}
			$last_name=esc_html( $Prefix.$people->last_name);

			if( $people->people_type_id=='1')
			{
				$adults[$last_name][]=esc_html( $name);

				$first_names[]=$name;
				if(!empty( $people->email)&&$people->email!=end( $emails) ) $emails[$name]=$people->email;
				if(!empty( $people->mobile)&&$people->mobile!=end( $mobiles) )$mobiles[$name]=esc_html( $people->mobile);
				
				if(!empty( $people->attachment_id) )$photos[$name]=$people->attachment_id;
				$x++;
			}
			else
			{
				$children[]=esc_html(trim( $name) );
				if(!empty( $people->attachment_id) )$photos[$name]=$people->attachment_id;
			}

		}

		//create output
		array_filter( $adults); $adultline=array();

		foreach( $adults as $lastname=>$firstnames)  {$adultline[]=implode(" &amp; ",$firstnames).' '.$lastname;}
		$out .="\r\n". '<div class="church_admin_address" itemscope itemtype="http://schema.org/Person">'."\r\n\t".'<div class="church_admin_name_address ca-names" >'."\r\n\t\t".'<span itemprop="name"><strong>'.esc_html(implode(" &amp; ",$adultline) ).'</strong></span>';

		if( !empty( $kids)&&!empty( $children) )$out.='<br>'.esc_html(implode(", ",$children) );

		if(!empty( $address->address) )
		{
			$out.='<p><span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress" class="ca-addresses">'.str_replace(',',',<br>',$address->address).'</span></p>';
		}
		if ( $address->phone)$out.=' <a class="email ca-email" href="'.esc_url('tel:'.str_replace(' ','',$address->phone) ).'">'.esc_html( $address->phone)."</a><br>\n\r\t\t";


		$out.='<div class="church_admin_vcard" >'."\r\n\t\t";
		//only display edit link to logged in user of that household or people with permissions
		if(is_user_logged_in() )
		{
			$user = wp_get_current_user();
			if( $updateable&&( $user->ID==$ordered_row->household_id||church_admin_level_check('Directory') ))
			{
				$page_id=church_admin_register_page_id();
				if(!empty( $page_id) )
				{
				$out.='<p>&nbsp;<a title="'.esc_html( __('Edit Entry','church-admin' ) ).'" href="'.esc_url( add_query_arg( 'household_id',$ordered_row->household_id ,get_permalink( $page_id) ) ).'"><span class="ca-dashicons dashicons dashicons-edit"></span></a>';
				}else
				{
					$out.='<p>&nbsp;<a title="'.esc_html( __('Edit Entry','church-admin' ) ).'" href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=display-household&amp;household_id='.$ordered_row->household_id,'display-household').'"><span class="ca-dashicons dashicons dashicons-edit"></span></a>';
				}
			}
		}
		$out.='<span><a  rel="nofollow" title="'.esc_html( __('Download Vcard','church-admin' ) ).'" href="'.home_url().'/?ca_download=vcf&amp;vcf='.wp_create_nonce( $ordered_row->household_id).'&amp;id='.$ordered_row->household_id.'"><span class="ca-dashicons dashicons dashicons-id"></span></a></span>  </p>'."\r\n\t".'</div><!--church_admin_vcard-->'."\r\n";

		$out.='</div><!--church_admin_name_address-->'."\r\n\t";
		$out.=	'<div class="church_admin_phone_email">'."\r\n\t\t";


		foreach( $first_names AS $first_name)
		{
			$out.='<p>';
			if(!empty( $photos[$first_name] ) )$out.=wp_get_attachment_image( $photos[$first_name], array(90,90),0,array('class'=>'alignleft') );
			if(count( $mobiles)>1||count( $emails)>1||!empty( $photos['first_name'] ) )$out.='<span class="ca-names">'.esc_html( $first_name).'</span><br>';
			if(!empty( $mobiles[$first_name] )||!empty( $emails[$first_name] ) )
			{


				if(!empty( $mobiles[$first_name] ) )$out.='<a class="email ca-email"  href="tel:'.str_replace(' ','',$mobiles[$first_name] ).'"><span itemprop="telephone">'.esc_html( $mobiles[$first_name] )."</span></a><br>";
				if(!empty( $emails[$first_name] ) )$out.='<a class="email ca-names"  href="'.esc_url('mailto:'.$emails[$first_name] ).'"><span itemprop="email">'.esc_html( $emails[$first_name] )."</span></a><br>";
				if(!empty( $photos[$first_name] ) )$out.='<br style="clear:left" />';

			}
			$out.='</p>';
		}


		$out.='</p>'."\r\n\t".'</div><!--church_admin_phone_email-->';

		if(!empty( $map)&&!empty( $address->lng)&!empty( $address->address) )
		{
			$url='https://maps.google.com/maps/api/staticmap?center='.$address->lat.','.$address->lng.'&amp;zoom=15&amp;markers='.$address->lat.','.$address->lng.'&amp;size=250x250';
			$api_key=get_option('church_admin_google_api_key');
			if(!empty( $api_key) )$url.='&amp;key='.$api_key;
			$map_url=esc_url( $url);


			$out.="\r\n\t".'<div class="church_admin_address_map">'."\r\n\t\t".'<a href="'.esc_url('https://maps.google.com/maps?q='.$address->lat.','.$address->lng.'&amp;t=m&amp;z=16').'"><img src="'.$map_url.'" height="250" width="250" alt="Map" /></a>'."\r\n\t";
		$out.='</div><!--church_admin_address_map-->'."\r\n\t";
		}

		$out.='</div><!--church_admin_address-->'."\r\n";
	}
	}
	// Pagination
		if(!empty( $p) )
		{
			$out.= '<div class="tablenav"><div class="tablenav-pages">';
			$out.= $p->getOutput();
			$out.= '</div></div>';
		}
      //Pagination
	return $out;
}
?>
