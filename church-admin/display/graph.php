<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/**************************************
* 
* Updated 2023-04-17
* Beefed up sanitization
* Meeting choice is validated to check 
* that it is expected input 
* G,S, or C / Integer
*
****************************************/



function church_admin_graph( $type='weekly',$meet=NULL,$start=NULL,$end=NULL,$width=NULL,$height=NULL,$admin=FALSE)
{
	church_admin_debug('***** GRAPH *****');
	$out='';
    $licence =get_option('church_admin_app_new_licence');
	if($licence!='standard' && $licence!='premium'){
		return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
		
	}

	//sanitize and validate
	$start = !empty($_REQUEST['start'])?sanitize_text_field(stripslashes($_REQUEST['start'] ) ):wp_date('Y-m-d',strtotime('- 1 year') );
	$end = !empty($_REQUEST['end'])?sanitize_text_field(stripslashes($_REQUEST['end'] ) ):wp_date('Y-m-d' );
   	$type=!empty( $_REQUEST['type'] )?sanitize_text_field(stripslashes($_REQUEST['type'] ) ):NULL;
	// changed by form input
	if(!empty($_REQUEST['change-graph'])){
		$meet=!empty($_REQUEST['meeting'])?sanitize_text_field( stripslashes( $_REQUEST['meeting'] ) ):null;
	}

	if ( empty( $meet) )
	{
		$service_id=$wpdb->get_var('SELECT service_id FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_id ASC LIMIT 1');
		if(empty($service_id)){return;}
		$meet='S/'.(int)$service_id;
	}
	

	
	//validate for expected
	$regex='/[GCS]\/[0-9]*/';
	$match=preg_match($regex,$meet);


	if(!empty($match))
	{

		$mtgDetails=explode("/",$meet);
		switch( $mtgDetails['0'] )
		{
			default:
			case'S':
				$mtg_type='service';
			break;
			case 'G':
				$mtg_type='group';
			break;
			case 'C':
				$mtg_type='class';
			break;
		}
		$mtg_id=(int)$mtgDetails['1'];
	}
	//recreate meeting variable for later
	if(empty($mtg_type)){$mtg_type='service';}
	if(empty($mtg_id)){$mtg_id=1;}
	$what=esc_html($mtg_type.'/'.$mtg_id);





	global $wpdb,$post;
	
		$out.='<div class="church-admin-graph">';
    //$out.=print_r( $_REQUEST,TRUE);
	//check services, classes or groups setup
  	$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
  	$groups=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
	$classes=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_classes');
	if ( empty( $services) && empty( $classes) && empty( $groups) )
	{
		$out.= '<p>'.esc_html( __('Please set up a service, group or class first','church-admin'));
	}
	else
	{//safe to proceed
		
		
		if ( !empty( $_GET['action'] )&& is_admin() )
		{
			
			$out.='<form action="admin.php" method="GET">';
			$out.='<input type="hidden" name="page" value="church_admin/index.php">';
			$out.='<input type="hidden" name="action" value="graph">';
			$out.=wp_nonce_field('graph','_wpnonce',false,false);

		}	 
	 	else $out.='<form action="" method="POST">';
		if(!empty( $admin) )$out.='<input type="hidden" name="attendance_graph" value="1" />';
	 	$out.='<p><label>'.esc_html(__('Meeting','church-admin')).'</label><select name="meeting">';
		$first='';
        $option='';
			//services first
		
			if(!empty( $services) )
			{
				
				foreach( $services AS $serv)
				{
					$serviceDetail=esc_html(__('Service','church-admin')).' - '.esc_html( $serv->service_name).' '.esc_html( $serv->service_time);
     				if(!empty( $mtg_type) && $mtg_type=='service'&& $mtg_id==$serv->service_id)
     				{
	  					$first='<option value="S/'.esc_html( $serv->service_id).'" selected="selected">'.$serviceDetail.'</option>';
     				}
     				else
     				{
	  					$option.='<option value="S/'.esc_html( $serv->service_id).'" >'.$serviceDetail.'</option>';
     				}
				}
			}
			//groups
			if(!empty( $groups) )
			{
				foreach( $groups AS $group)
				{
					if(!empty( $mtg_type) && $mtg_type=='group'&& $mtg_id==$group->id)
					{
						$first='<option value="G/'.esc_html( $group->id).'" selected="selected">'.esc_html( __('Group','church-admin' ) ).' - '.$group->group_name.'</option>';
					}
					else
     				{
	  					$option.='<option value="G/'.esc_html( $group->id).'" >'.esc_html( __('Group','church-admin' ) ).' - '.esc_html( $group->group_name).'</option>';
     				}

				}
			}
			//classes
			if(!empty( $classes) )
			{
				foreach( $classes AS $class)
				{
					if(!empty( $mtg_type) && $mtg_type=='class'&& $mtg_id==$class->class_id)
					{
						$first='<option value="C/'.esc_html( $class->class_id).'" selected="selected">'.esc_html( __('Class','church-admin' ) ).' - '.$class->name.'</option>';
					}
					else
     				{
	  					$option.='<option value="C/'.esc_html( $class->class_id).'" >'.esc_html( __('Class','church-admin' ) ).' - '.esc_html( $class->name).'</option>';
     				}

				}
			}

    $out.= $first.$option.'</select></p>';
	 $out.='<p><input type="radio" name="type" value="weekly" ';
	 if( $type=='weekly'||empty($type)) $out.=' checked="checked"';
	 $out.='/> '.esc_html(__('Weekly Attendance Graph','church-admin')).'</p>';
	  $out.='<p><input type="radio" name="type" value="rolling" ';
	 if( $type=='rolling') $out.=' checked="checked"';
	 $out.='/> '.esc_html(__('Rolling Average Attendance Graph','church-admin')).'</p>';
	 $out.='<p>'.esc_html(__('Start Date','church-admin')).': '.church_admin_date_picker( $start,'start',NULL,date('Y')-30,date('Y'),'start','start').'</p>';
	 $out.='<p>'.esc_html(__('End Date','church-admin')).': '.church_admin_date_picker( $end,'end',NULL,date('Y')-30,date('Y'),'end','end').'</p>';
	 $out.='<p><input type="hidden" name="change-graph" value=1><input class="button-primary" type="submit" value="'.esc_attr(__('Show','church-admin')).'" /></p></form>';


	 //build graph
	if(empty($type)){$type='weekly';}
	 //grab attendanc data
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_attendance WHERE service_id="'.(int) $mtg_id.'" AND mtg_type="'.esc_sql( $mtg_type).'" AND `date` BETWEEN "'.esc_sql( $start).'" AND "'.esc_sql( $end).'" ORDER BY `date` ASC';
	church_admin_debug($sql);
	$results=$wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		$data=array();
	 	foreach( $results AS $row)
	 	{
	 		$total=(int)$row->adults+$row->children;
	 		$rolling_total= $row->rolling_adults + $row->rolling_children;
	 		if( $type=='weekly')$data[]='["'.mysql2date('d M Y',$row->date).'",'.$total.','.(int)$row->adults.','.(int)$row->children.']';
	 		if( $type=='rolling')$data[]='["'.mysql2date('d M Y',$row->date).'",'.$rolling_total.','.(int)$row->rolling_adults.','.(int)$row->rolling_children.']';
	 	}
	 	$out.='<script>// Load the Visualization API.
    	google.load("visualization", "1", {"packages":["line"]});

    	// Set a callback to run when the Google Visualization API is loaded.
    	google.setOnLoadCallback(drawChart);
     	function drawChart() {

      		var data = new google.visualization.DataTable();
      		data.addColumn("string", "'.esc_html( __('Date','church-admin' ) ).'");
      		data.addColumn("number", "'.esc_html( __('Total','church-admin' ) ).'");
      		data.addColumn("number", "'.esc_html( __('Adults','church-admin' ) ).'");
      		data.addColumn("number",  "'.esc_html( __('Children','church-admin' ) ).'");

      		data.addRows(['.implode(',',$data).'] );

      		var options = {
        	chart: {
          		title: "'.esc_html( __('Attendance Graph','church-admin' ) ).'"
        	},
        	
      	};

      	var chart = new google.charts.Line(document.getElementById("attendance-chart") );

      	chart.draw(data, options);

    	}</script>';
			$out.='<div id="attendance-chart" style="width:'.esc_html( $width).';height:'.esc_html( $height).'"></div>';


	}else{$out.='<p>'.esc_html( __('No attendance data','church-admin' ) ).'</p>';}
	}//safe to proceed
	$out.='</div>';
	return $out;
}
