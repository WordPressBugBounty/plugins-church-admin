<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
function church_admin_frontend_people( $member_type_id=0,$map=NULL,$photo=NULL,$api_key=NULL,$kids=TRUE,$site_id=0)
{
	global $wpdb;
	$api_key=get_option('church_admin_google_api_key');

  	$out='';

  	$memb_sql='';
  	$membsql=$sitesql=array();
  	if( $member_type_id!=0)
  	{
  		$memb=explode(',',$member_type_id);
      	foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.$value;}
      	if(!empty( $membsql) ) {$memb_sql=' ('.implode(' || ',$membsql).')';}
	}
	$site_sql='';
	if( $site_id!=0)
  	{
  		$sites=explode(',',$site_id);
      	foreach( $sites AS $key=>$value)  {if(church_admin_int_check( $value) )  $sitesql[]='site_id='.$value;}
      	if(!empty( $sitesql) ) {$site_sql=' ('.implode(' || ',$sitesql).')';}
	}
	//build query adding relevant member_types and sites
      $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.show_me=1 AND a.household_id=b.household_id AND a.gdpr_reason IS NOT NULL  AND active=1';
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
	  	$page_limit=20;
	  	$page_limit=get_option('church_admin_pagination_limit');
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
	  	$limit = "LIMIT " . ((int) $p->page - 1) * (int)$p->limit  . ", " . (int)$p->limit;


	  	// Pagination
		$out.= '<div class="tablenav"><div class="tablenav-pages">';
        $out.= $p->getOutput();
        $out.= '</div></div>';
     	 //Pagination
      }
      $thead=array(
					esc_html(__('Name','church-admin' ) ),
					esc_html(__('Email','church-admin' ) ),
					esc_html(__('Mobile','church-admin' ) ),
					esc_html(__('Phone','church-admin' ) ),
					esc_html(__('Address','church-admin'))
				);
      $out.='<table class="striped"><thead><tr><th>'.implode("</th><th>",$thead).'</th></tr></thead><tfoot><tr><th>'.implode("</th><th>",$thead).'</th></tr></tfoot><tbody>';
      $results=$wpdb->get_results( $sql.$limit);
      foreach( $results AS $row)
      {
			$privacy=unserialize($row->privacy);
      		if( $row->active)
      		{
      			$name=array_filter(array( $row->first_name,$row->middle_name,$row->prefix,$row->last_name) );
      			$out.='<tr><td class="ca-names">'.esc_html(implode(" ",$name) ).'</td><td class="ca-email">';
				if(!empty($privacy['show-email'])){ 
					$out.='<a href="'.esc_url('mailto:'.antispambot($row->email)).'">'.esc_html( antispambot($row->email )).'</a>';
					
				}
				else
				{
					$out.='&nbsp;';
				}
				$out.='</td><td class="ca-mobile">';
				if(!empty($privacy['show-cell'])){ 
					$out.='<a href="'.esc_url('call:'.$row->mobile).'">'.esc_html( antispambot($row->mobile)).'</a>';
				}
				else
				{
					$out.='&nbsp;';
				}
				$out.='</td><td>';
				if(!empty($privacy['show-landline'])){ 
					$out.='<a  class="ca-phone" href="call:'.$row->phone.'">'.esc_html( $row->phone).'</a>';
				}
				else
				{
					$out.='&nbsp;';
				}	
				$out.='</td><td  class="ca-addresses">';
				if(!empty($privacy['show-address'])){ 
					$out.=esc_html( $row->address);
				}
				else
				{
					$out.='&nbsp;';
				}
				
				$out.='</td></tr>';
      		}
      }
      $out.='</tbody></table>';
      // Pagination
		$out.= '<div class="tablenav"><div class="tablenav-pages">';
        $out.= $p->getOutput();
        $out.= '</div></div>';
     	 //Pagination
      return $out;

}
