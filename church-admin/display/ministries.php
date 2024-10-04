<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_frontend_ministries( $ministry_id,$member_type_id)
{
	$licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
			
		}

	global $wpdb;
	 $ministries=church_admin_ministries('None');

    $out='<div class="church-admin-ministries">';
	if ( empty( $ministries) )
	{
		$out.='<p>'.esc_html(__('No ministries yet','church-admin')).'</div>';
		return $out;
	}
    //ministry ids
	if( $ministry_id=="#")  {$ministry_id="";}
    if(!empty( $ministry_id) )  {
    	$min=explode(',',$ministry_id);
   	}else{
   		$min=array_keys( $ministries);
   	}
   	//member type ids
   	$memb_sql='';
  	$membsql=$sitesql=array();
  	if( $member_type_id=="#")  {$memb_sql="";}
  	elseif( $member_type_id!="")
  	{
  		$memb=explode(',',$member_type_id);
      	foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.(int)$value;}
      	if(!empty( $membsql) ) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}
	}
	if ( empty( $min) )
	{
		$out.='<p>'.esc_html(__('No ministries yet','church-admin')).'</div>';
		return $out;
	}


    foreach( $min AS $key=>$min_id)  {
		$out.='<h2>'.esc_html( $ministries[$min_id] ).'</h2><p>';
		$sql='SELECT a.first_name,a.last_name,a.middle_name,a.prefix, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.ID="'.esc_sql( $min_id).'" AND b.meta_type="ministry" '.$memb_sql.' ORDER BY a.last_name ASC';

		$results=$wpdb->get_results( $sql);
		if(!empty( $results) )  {
			
			foreach( $results as $row)  {
					//build name
					$name=$row->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty( $middle_name)&&!empty( $row->middle_name) )$name.=$row->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty( $nickname)&&!empty( $row->nickname) )$name.='('.$row->nickname.') ';
					$prefix=get_option('church_admin_use_prefix');
					if(!empty( $prefix)&&!empty( $row->prefix) )		$name.=$row->prefix.' ';
					$name.=$row->last_name;


				$out.='<span  class="ca-names">'.esc_html( $name).'</span><br>';
			}
			$out.='</p>';

		}
		else{$out.='<p>'.esc_html(__('No-one doing this ministry yet','church-admin'));}
	}
	$out.='</div>';
	return $out;
}

function church_admin_frontend_ministry_list()  {

	global $wpdb;
	$ministries=get_option('church_admin_ministries');

    $out='<h2>'.esc_html(__('List of Ministries','church-admin')).'</h2><p>';

    sort( $ministries);
    foreach( $ministries AS $key=>$value)
    {
    	$out.=esc_html( $value).'<br>';
    }
	$out.='</p>';


	return $out;
}
