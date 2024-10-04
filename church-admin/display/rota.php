<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/*********************
 * Display new rota
 ***********************/
function church_admin_front_end_rota( $service_id=null,$limit=null,$pdfFontResize=null,$date=null,$title=null,$initials=FALSE,$links=TRUE,$nameStyle=null)
{
	$licence =get_option('church_admin_app_new_licence');
	if($licence!='standard' && $licence!='premium'){
		return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
		
	}
	//for backwards compatibility merge $initials into $nameStyle
	if(!empty( $initials)&& empty( $nameStyle) )$nameStyle='Initials';
	church_admin_debug("Name style is $nameStyle");

	global $wpdb,$wp_locale;
	$wpdb->show_errors();
	$out='<div class="church-admin-rota">';
	$sql='SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id AND a.active=1 ORDER BY service_id';
   	$services=$wpdb->get_results( $sql);
	
	$noOfServices=$wpdb->num_rows;
	if ( empty( $service_id) )
	{
		//No service_id via shortcode
		$sqlServiceID=$services[0]->service_id;
	}
	else{$sqlServiceID=$service_id;}
	if(!empty( $_REQUEST['service_id'] ) )  {$sqlServiceID=(int)$_REQUEST['service_id'] ;}
	
	//graceful abort if not ready for public viewing
	if ( empty( $services)||empty( $sqlServiceID) )
	{
		$out.='<h3>'.esc_html( __('No service set up yet','church-admin' ) ).'</h3>';
		if(church_admin_level_check('Rota') )$out.='<p><a class="button-primary" href="'.esc_url(wp_nonce_url("admin.php?page=church_admin/index.php&section=rota&amp;action=edit-service",'edit-service')).'">'.esc_html(__('Please set up a service first','church-admin')).'</a></p>';
		$out.='</div>';
		return $out;
	}



    if(!empty( $sqlServiceID) )
    {

		//get required rota tasks

		$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
		//graceful abort
		if ( empty( $rota_tasks) )
		{
			$out.='<h3>'.esc_html( __('No schedule tasks set up yet','church-admin' ) ).'</h3>';
			if(church_admin_level_check('Rota') )$out.='<p><a class="button-primary" href="'.esc_url(wp_nonce_url("admin.php?page=church_admin/index.php&section=rota&amp;action=rota-settings",'rota-settings')).'">'.esc_html(__('Please set up some schedule jobs first','church-admin')).'</a></p>';
			$out.='</div>';
			return $out;


		}

		$requiredRotaJobs=$rotaDates=$noInitials=array();
		foreach( $rota_tasks AS $rota_task)
		{
			$allServiceID=maybe_unserialize( $rota_task->service_id);
			if(is_array( $allServiceID)&&in_array( $sqlServiceID,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=array('job'=>$rota_task->rota_task,'initials'=>$rota_task->initials);
			$noInitials[$rota_task->rota_id]=$rota_task->initials;
		}
        
		//get next four weeks of rota_jobs for each rota task
		//first grab next month of services
		$rota_date = !empty($_POST['rota_date'])? sanitize_text_field(stripslashes($_POST['rota_date'])):null;
		if(empty( $rota_date  ) || !church_admin_checkdate( $rota_date ) )
        {
			$rota_date=wp_date('Y-m-d');
		}
	
		
		
		//adjust how many dependent on how many sundays in month
		$start = new DateTime( $rota_date);
		$end =  new DateTime( $rota_date);
		$end->modify('last day of');
		$days = $start->diff( $end, true)->days;
		//$limit = intval( $days / 7) + ( $start->format('N') + $days % 7 >= 7);
		
		/*****************************************
		*
		* Build rota array of dates and jobs
		*
		******************************************/
		
		$rota=$rotaDates=array();
		
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.(int)$sqlServiceID.'" AND mtg_type="service" AND rota_date>="'.esc_sql($rota_date).'"  GROUP BY rota_date ORDER BY rota_date ASC LIMIT '.$limit;
		
		
		$rotaDatesResults=$wpdb->get_results( $sql);
		//$out.='<pre>'.print_r( $rotaDatesResults, TRUE).'</pre>';
		if( $rotaDatesResults)
		{
			foreach( $rotaDatesResults AS $rotaDatesRow)$rotaDates[$rotaDatesRow->rota_date]=$rotaDatesRow->service_time;
			//grab people for each job and each date and populate $rota array
			

			foreach( $rotaDatesResults AS $rotaDateRow)
			{
				
				foreach( $requiredRotaJobs AS $rota_task_id=>$value)
				{
					church_admin_debug("forloop $nameStyle");
                    if( $noInitials[$rota_task_id] )
					{
						switch( $nameStyle)
						{
							case 'FirstNameFirstLetterLastName':
								church_admin_debug("using firstNameFirstLetterLastName");
								$rota[$rota_task_id][$rotaDateRow->rota_date]=church_admin_rota_people_firstname_initial_last_name( $rotaDateRow->rota_date,$rota_task_id,$sqlServiceID,'service' );
							break;
							case 'Initials':
								$rota[$rota_task_id][$rotaDateRow->rota_date]=church_admin_rota_people_initials( $rotaDateRow->rota_date,$rota_task_id,$sqlServiceID,'service') ;
							break;
							default:
							case 'Full':
								$rota[$rota_task_id][$rotaDateRow->rota_date]=church_admin_rota_people( $rotaDateRow->rota_date,$rota_task_id,$sqlServiceID,'service') ;
							break;
						}
					}
					else $rota[$rota_task_id][$rotaDateRow->rota_date]=church_admin_rota_people( $rotaDateRow->rota_date,$rota_task_id,$sqlServiceID,'service') ;
					
				}
			}
			
		}
		else
		{
			//graceful abort
			$out.='<h3>'.esc_html( __('No schedule set up yet','church-admin' ) ).'</h3>';
			if(church_admin_level_check('Rota') )$out.='<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&section=rota&amp;action=rota",'rota').'">'.esc_html(__('Please set up a schedule','church-admin')).'</a></p>';
			$out.='</div>';
			return $out;
		}
		
		//Title
		$service=$wpdb->get_row('SELECT a.*,b.venue FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_sites b WHERE a.service_id="'.intval( $sqlServiceID).'" AND a.site_id=b.site_id');
		if(!empty( $service) )
		{
			if( $service->service_day>=0||$service->service_day<=7)  {$day=$wp_locale->get_weekday( $service->service_day);}else{$day='';}
			if(!empty( $title) )  {$out.='<h3>'.esc_html($title).'</h3>';}
            else
            {
                $out.='<h3>'.esc_html( __('Schedule for','church-admin' ) ).' '.esc_html( $service->service_name).' '.esc_html( __('on','church-admin' ) ).' '.esc_html( $day).' '.esc_html( __('at','church-admin' ) ).' '.mysql2date(get_option('time_format'),$service->service_time).' '.esc_html( $service->venue).'</h3>';
            }
			$out.='<form action="'.get_permalink().'" method="POST"><div class="church-admin-form-group"><label>'.esc_html(__('Choose Month','church-admin')).'</label><select class="church-admin-form-control" name="rota_date" class="rota_date">';
			$option='';
			//this month
			$first='';
			//first dropw down month equal to previously selected month if applicable
			if(!empty( $rota_date ) )$first='<option selected="selected"  value="'.date('Y-m-d',strtotime( $rota_date ) ).'">'.date('M Y',strtotime( $rota_date ) ).'</option>';
			//make sure this month appears in dropdown
			$option.='<option  value="'.date('Y-m-d',strtotime('first day of this month') ).'">'.date('M Y',strtotime('first day of this month') ).'</option>';
			for ( $x=1; $x<=12; $x++)
			{
				$option.='<option value="'.date('Y-m-d',strtotime('first day of +'.$x.' month') ).'">'.date('M Y',strtotime('first day of +'.$x.' month') ).'</option>';
			}
			$out.=$first.$option;
			
			$out.='</select></div>';
			
			
			
    		//Only alllow choosing of service if service_id unspecified in shortcode
    		if ( empty( $service_id) )
			{
				if( $noOfServices==1)
    			{//only one service
				$out.='<input type="hidden" name="service_id" value="'.intval( $services[0]->service_id).'" />';
    			}//only one service
    			else
    			{//choose service

					$out.='<div class="church-admin-form-group"><label>'.esc_html( __('Which Service?','church-admin' ) ).'</label><select class="church-admin-form-control" name="service_id">';
					foreach( $services AS $service)
					{
						if( $service->service_day>=0||$service->service_day<=7)  {$day=$wp_locale->get_weekday( $service->service_day);}else{$day='';}
						$out.='<option value="'.(int)$service->service_id.'">'.esc_html( $service->service_name).' '.esc_html( __('on','church-admin' ) ).' '.$day.' '.esc_html( __('at','church-admin' ) ).' '.mysql2date(get_option('time_format'),$service->service_time).' '.esc_html( $service->venue).'</option>';
					}
					$out.='</select></div>';
    			}//choose service
			
			}
			
			$out.='<div class="church-admin-form-group"><input class="button-primary" type="submit" value="'.esc_html( __('Choose','church-admin' ) ).'" /></div></form>';
			
			
			if(!empty( $rota) )
			{
				if(!empty( $links) )
				{
					//only show links if flag is true
					if(!empty( $sqlServiceID) &&is_user_logged_in() )$out.='<p><a  rel="nofollow" href="'.wp_nonce_url(site_url().'?ca_download=rotacsv&amp;service_id='.intval( $sqlServiceID).'&initials='.intval( $initials),'rotacsv').'">'.esc_html( __('Download Schedule CSV','church-admin' ) ).'</a></p>';
					
					if(!empty( $rota_date )&& church_admin_checkdate( $rota_date ) )  {
						$urlDate=sanitize_text_field( stripslashes($rota_date ) );
					}else{
						$urlDate=date('Y-m-d');
					}
					if( !empty( $sqlServiceID)&&is_user_logged_in() )
					{
						$out.='<p><a  rel="nofollow" class="pdf-link" href="'.esc_url(site_url().'?ca_download=rota&amp;initials='.intval( $initials).'&amp;service_id='.(int)$service_id.'&date='.$urlDate).'">'.esc_html(__('Download Service Schedule PDF','church-admin')).'</a></p>';
						$out.='<script>
						jQuery(document).ready(function( $)  {
						$(".rota_date").on("change",function()  {
							var link="'.esc_url(get_permalink().'?ca_download=rota&amp;service_id='.(int)$service_id).'";
							console.log(link);
							var rota_date=$(this).val();
							console.log(rota_date);
							$(".pdf-link").attr("href",link+"&date="+rota_date);
						});})</script>';
					}
				}
                //table header
				$out.='<table class="church_admin">';
				$thead='<tr><th>'.esc_html( __('Ministries','church-admin' ) ).'</th>';

				foreach( $rotaDates AS $table_rota_date=>$time)
				{

					$thead.='<th>'.esc_html(mysql2date(get_option('date_format'),$table_rota_date).' '.mysql2date(get_option('time_format'),$time)).'</th>';
				}
				$out.='<thead>'.$thead.'</thead><tbody>';
				//table data

				foreach( $rota AS $rota_task_id=>$data)
				{
					//1st column is job
					$out.='<tr><th scope="row">'.esc_html( $requiredRotaJobs[$rota_task_id]['job'] ).'</th>';
					//rest of columns for that row
					foreach( $data AS$date=>$value)
					{
						$out.='<td class="ca-names">'.esc_html( $value).'</td>';
					}
					$out.='</tr>';
				}
				$out.='</tbody><tfoot>'.$thead.'</tfoot></table>';
			}
			else{$out.='<p>'.esc_html(sprintf(__('No scheduled ministries for %1$s','church-admin' ) ,mysql2date('M Y',$date) ) ).'</p>';}
		}
	}

	$out.='</div>';
	return $out;
}

function church_admin_my_rota()
{
	$licence =get_option('church_admin_app_new_licence');
	if($licence!='standard' && $licence!='premium'){
		return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="'.admin_url().'admin.php?page=church_admin/index.php#support">Upgrade</a></p></div>';
		
	}
	global $wpdb; $current_user;
	$current_user = wp_get_current_user();

	$out='<div class="church-admin-my-rota"><h2>'.esc_html( __('My Schedule','church-admin' ) ).'</h2>';

	if ( empty( $current_user->ID)	)
	{
		$out.='<p>'.esc_html( __('You must be logged in','church-admin' ) ).'</p>';
		$out.=wp_login_form(array('echo' => false) );

	}
	else
	{
		$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people  WHERE user_id="'.(int)$current_user->ID.'"');
		if ( empty( $people_id) )
		{
			$out='<p>'.esc_html( __('Your login needs to be connected to someone in the Church Directory','church-admin' ) ).'</p>';
		}
		else
		{

			$sql='SELECT a.service_name,a.service_time, b.rota_task,c.rota_date,c.service_time AS updated_time FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_rota_settings b, '.$wpdb->prefix.'church_admin_new_rota c WHERE a.service_id=c.service_id AND c.mtg_type="service" AND c.rota_task_id=b.rota_id  AND c.people_id="'.(int)$people_id.'" AND c.rota_date>=CURDATE() ORDER BY c.rota_date ASC';

			$results=$wpdb->get_results( $sql);
			if(!empty( $results) )
			{
				$out.='<table class="table table-bordered table-striped">';
				foreach( $results AS  $row)
				{
					if(!empty( $row->updated_time) )  {$time=$row->updated_time;}else{$time=$row->service_time;}
					$out.='<tr><th scope="row">'.esc_html(mysql2date(get_option('date_format'),$row->rota_date).' '.esc_html( $row->service_name.' '.mysql2date(get_option('time_format'),$time) )).'</th><td>'.esc_html( $row->rota_task).'</td></tr>';
				}
				$out.='</table>';
			}

		}
	}
	$out.='</div>';
	return $out;
}


