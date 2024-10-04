<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly



function church_admin_frontend_small_groups( $member_type_id=1,$restricted=FALSE)
{
	$licence =get_option('church_admin_app_new_licence');
	if($licence!='standard' && $licence!='premium'){
		return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
		
	}

	//$restricted means people only see the groups they are involved with

	global $wpdb,$current_user;
	$show=TRUE;
	if( $restricted)
	{
		wp_get_current_user();
		$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
		$show=FALSE;//default no show

	}

	$out='<div class="church-admin-smallgroups">';
	$out.='<h2>'.esc_html( __('Small groups members list','church-admin' ) ).'</h2>';
	if(!empty( $member_type_id)&&!is_array( $member_type_id) )  {$memb=explode(',',$member_type_id);}else{$memb=array();}
    foreach( $memb AS $key=>$value)  {if(church_admin_int_check( $value) )  $membsql[]='a.member_type_id='.$value;}
    if(!empty( $membsql) ) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}else{$memb_sql='';}
	//show small groups
	$leader=array();

	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup' .' WHERE id!=1 ORDER BY smallgroup_order';
	$small_group=$sg=array();
	$results = $wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			if( $restricted)
			{
				//check if a leader
				$leaders=maybe_unserialize( $row->leadership);
				if(!empty( $leaders) )foreach( $leaders AS $leaderlevel) if(in_array( $people_id,$leaderlevel) )$show=TRUE;//allowed!
				//check if a site admin
				if(current_user_can('manage_options') )$show=true;
				//check  if in group
				$check=$wpdb->get_var('SELECT people_ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int)$row->id.'" AND people_id="'.(int)$people_id.'" AND meta_type="smallgroup"');
				if( $check)$show=TRUE;
			}
			if( $show)
			{
				$small_group[$row->id]='';
				$out.='<h3>'.$row->group_name.'</h3>';

				//leaders
				//build leaders
				
				$hierarchy=church_admin_get_hierarchy(1);
    			$structure=array_reverse( $hierarchy,TRUE);//sort top level down
    			//who is currently leading
    			
				$ldrsResults=$wpdb->get_results('SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE b.meta_type="smallgroupleader" AND b.ID="'.(int)$row->id.'" AND a.people_id=b.people_id');
    			if(!empty( $ldrsResults) )
				{
					$curr_leaders=array();
					foreach( $ldrsResults AS $ldrsRow)
					{
						$curr_leaders[]=$ldrsRow->name;
					}
					$out.='<p>'.esc_html( __('Led by','church-admin' ) ).': '.esc_html(implode(", ",$curr_leaders) ).'</p>';
				}
				
    				
    		
    			

				//grab people
				$sql='SELECT CONCAT_WS(" ", a.first_name,a.prefix,a.last_name) AS name,b.ID FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b  WHERE b.ID="'.esc_sql((int) $row->id).'" AND a.people_id=b.people_id AND b.meta_type="smallgroup" '.$memb_sql.' ORDER BY a.last_name,a.first_name';

				$peopleresults=$wpdb->get_results( $sql);
				if(!empty( $peopleresults) )
				{
					$out.='<p><strong>'.esc_html( __('Group Members','church-admin' ) ).'</strong><br>';
					foreach( $peopleresults AS $people)  {$out.='<span  class="ca-names">'.esc_html( $people->name).'</span><br>';}
					$out.='</p>';
				}
			}//end show
		}




	}
$out.='</div>';
	return $out;
}
