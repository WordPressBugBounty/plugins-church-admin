<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//Address Directory Functions
//2016-09-26 Added Nickname
//2020-08-07 added phone call privacy permission


/**
 * People Admin
 *
 * @param
 * @param
 *
 * @author andy_moyle
 *
 */
function church_admin_people_main()
{
    global $wpdb,$people_type;

	$allowed_html = array(
		'a' => array(
			'href' => array(),
			'title' => array(),
			'class' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
	);


	$member_type=church_admin_member_types_array();
 	echo'<h2>'.esc_html( __(  'People', 'church-admin' ) ).'</h2>';
     echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/address-list-filtering/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';

	 echo'<div class="notice notice-error">';
	 church_admin_gdpr_check();
	 echo'</div>';
	/*************************************************************
		 * 
		 * Check for app user address updates that need geolocating
		 * 
		 *************************************************************/
		$api_key=get_option('church_admin_google_api_key');
		if(!empty( $api_key) )
		{
			//only perform check if there is a Google api key
			$geocodeRequiredCount=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_household WHERE address!=", , , ," AND address!=", , ," AND address!="" AND (geocoded=0 OR lat="" OR lng="")');
			if(!empty( $geocodeRequiredCount) )
			{
							
				echo'<div class="notice inline notice-warning"><h3>'.esc_html( sprintf( _n('%s household needs its address geocoding','%s households need their addresses geocoding',$geocodeRequiredCount,'church-admin' ) , $geocodeRequiredCount ) ).'</h3><p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=bulk-geocode&section=people','bulk-geocode').'">'.esc_html( __( 'Update mapping now', 'church-admin' ) ).'</a></p></div>';
			}
		}
	require_once(plugin_dir_path(__FILE__).'/filter.php');
    church_admin_directory_filter(TRUE,FALSE);
	
	
}

function church_admin_export_csv()
{
    //CSV
	echo'<h2>'.esc_html( __( 'Download a CSV of people/ Mailing labels','church-admin') ).'</h2>';
	
	echo'<form action="" method="POST">';
	echo wp_nonce_field('people-csv','people-csv');
	require_once(plugin_dir_path(dirname(__FILE__) ).'/includes/filter.php');
	church_admin_directory_filter(FALSE,TRUE);
    echo'<br style="clear:left" />';
	echo'<p><input type="radio" name="ca_download" checked="checked" value="people-csv" />'.esc_html( __( 'CSV file','church-admin' ) ).'</p>';
	echo'<p><input type="radio" name="ca_download" value="mailinglabel" />'.esc_html( __( 'Individual mailing labels','church-admin' ) ).'</p>';
    echo'<p><input type="radio" name="ca_download" value="householdlabel" />'.esc_html( __( 'Household mailing labels','church-admin' ) ).'</p>';
	echo'<p><select name="addressType"><option value="street">'.esc_html( __( 'Use street address','church-admin' ) ).'</option><option value="mailing">'.esc_html( __( 'Prefer mailing address if it exists', 'church-admin' ) ).'</option></select></p>';
	echo'<p><input class="button-primary" type="submit" value="'.esc_html( __( 'Download','church-admin') ).'" /></p>';
	echo'</form>';
}


function church_admin_pdf_menu()
{
    echo'<h2>'.esc_html( __( 'PDF of address list','church-admin' ) ).'</h2>';
    echo'<form action="'.site_url().'" method="get">';
	wp_nonce_field('address-list');
	echo'<input type="hidden" name="ca_download" value="address-list">';
	echo'<div class="church-admin-form-group"><label>'.esc_html(__('PDF Title','church-admin')).'</label><input class="church-admin-form-control" name="title"></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html(__('PDF Style','church-admin')).'</label><select class="church-admin-form-control" name="pdf_version" >';
	echo'<option value="1">'.esc_html(__('With household photos v1','church-admin')).'</option>';
	echo'<option value="2">'.esc_html(__('With household photos v2','church-admin')).'</option>';
	echo'<option value="3">'.esc_html(__('No photos','church-admin')).'</option>';
	echo'</select></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Address Style', 'church-admin' ) ).'</label>';
	echo'<select class="church-admin-form-control" name="address_style">';
	echo'<option value="single">'.esc_html(__('Single line','church-admin')).'</option>';	
	echo'<option value="multi">'.esc_html(__('Multiple line','church-admin')).'</option>';
	echo'</select></div>';

$member_type=church_admin_member_types_array();
if(!empty( $member_type) )
{
	echo'<div class="church-admin-form-group"><label>'.esc_html(__('Member types','church-admin')).'</label></div>';
	foreach( $member_type AS $id=>$membertype)
	{
		echo '<div class="checkbox"><input type="checkbox" name="member_type_id[]" checked="checked" value="'.(int)$id.'" /><label>'.esc_html( $membertype ).'</label></div>';
	}
}
echo'<p><input type="submit" class="button-primary" value="'.esc_html( __( 'Download','church-admin') ).'" /></p></form>';
}


function church_admin_view_person( $people_id=NULL)
{

	global $wpdb;

	$data=$wpdb->get_row('SELECT *,first_name,middle_name,prefix,last_name FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	if(!empty( $data) )
	{
		if(!empty( $data->attachment_id) )
		{//photo available

			echo wp_get_attachment_image( $data->attachment_id,'ca-people-thumb',NULL,array('class'=>'alignleft') );

		}//photo available
		$name=church_admin_formatted_name( $data );
		echo'<h2><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.(int)$people_id,'edit_people').'">'.esc_html( $name).'</a></h2><br style="clear:left" />';
		//active/inactive section
		
		if(!empty( $data->active) )  {$activate=__('Active','church-admin');}else{$activate=__('Inactive','church-admin');}
		echo'<p  data-colname="'.esc_html( __('Active','church-admin' ) ).'"><span class="activate ca-active" id="active-'.(int)$data->people_id.'">'.$activate.'</span> </p>';
			
		echo'<script>jQuery(document).ready(function($){$("body").on("click",".activate",function()  {
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
				console.log(data.status)
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
        });});</script>';
		
		
		echo'<h3>'.esc_html( __( 'Contact Details','church-admin') ).'</h3>';
		echo'<table class="form-table">';
		if(!empty( $data->mobile) )echo'<tr><th scope="row">'.esc_html( __( 'Mobile','church-admin') ).'</th><td><a href="call:'.esc_html( $data->mobile ).'">'.esc_html( $data->mobile).'</a></td></tr>';
		if(!empty( $data->email) )echo'<tr><th scope="row">'.esc_html( __( 'Email','church-admin') ).'</th><td><a href="call:'.esc_html( $data->email ).'">'.esc_html( $data->email).'</a></td></tr>';
		echo'</table>';
		
		$others=$wpdb->get_results('SELECT *,CONCAT_WS(" ",first_name,prefix,last_name) AS name FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.intval( $data->household_id).'" AND people_id!="'.(int)$people_id.'" ORDER BY people_order ASC');
		if(!empty( $others) )
		{
			echo'<h3>'.esc_html( __( 'Others in household','church-admin') ).'</h3>';
			foreach( $others AS $other)
			{
				echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.intval( $other->people_id),'edit_people').'">'.esc_html( $other->name).'</a></p>';
			}
		}
		//notes
		require_once(plugin_dir_path(dirname(__FILE__) ).'includes/comments.php');
		if(!empty( $people_id) )church_admin_show_comments('people',(int)$people_id);

	}

}

function church_admin_view_directory()
{
	global $church_admin_url;
    if(isset( $_POST['member_type_id'] ) )
    {
        church_admin_address_list( sanitize_text_field(stripslashes( $_POST['member_type_id'] ) ) ) ;
    }
    else
    {
        echo'<table class="form-table"><tbody><tr><th scope="row">'.esc_html( __( 'Select a directory to view','church-admin' ) ).'</th><td><form name="address" action="'.$church_admin_url.'&amp;action=view_directory" method="POST"><select name="member_type_id" >';
        echo '<option value="0">'.esc_html( __( 'All Member Types', 'church-admin' ) ).'</option>';
		$member_type=church_admin_member_types_array();
		foreach( $member_type AS $key=>$value)
		{
			$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.esc_sql( $key).'"');
			echo '<option value="'.esc_html( $key).'" >'.esc_html( $value).' ('.$count.' people)</option>';
		}
		echo'</select><input  class="button-primary"  type="submit" value="'.esc_html( __( 'Go','church-admin') ).'" /></form></td></tr></tbody></table>';
}
}

function church_admin_address_list( $member_type_id=0)
{
    if(!church_admin_level_check('Directory') )wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') ) );
    global $wpdb;
	if(empty($member_type_id)){$member_type_id=0;}
    $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=0 WHERE head_of_household=NULL');
	$member_type=church_admin_member_types_array();
	$member_type[0]=esc_html( __( 'Complete','church-admin') );
	$people_types=get_option('church_admin_people_type');


    //grab address list in order
	$sql='SELECT DISTINCT household_id FROM '.$wpdb->prefix.'church_admin_people';
    if(!empty( $member_type_id) )
	{
		$sql.=' WHERE member_type_id="'.esc_sql( $member_type_id).'"';
	}

    $result = $wpdb->get_var( $sql);
    $items=$wpdb->num_rows;

    echo'<hr/><table class="form-table">'."\r\n";
	echo'<tbody><tr><th scope="row">'.esc_html( __( 'Select different address list to view','church-admin') ).'</th><td><form name="address" action="admin.php?page=church_admin/index.php&amp;action=view-address-list&section=people" method="POST">';
	wp_nonce_field('view-address-list');
	echo'<select name="member_type_id" >';
	echo '<option value="0">'.esc_html( __( 'All Member Type...','church-admin') ).'</option>';
	foreach( $member_type AS $id=>$value)
	{
		$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.(int)$id.'"');
		echo '<option value="'.esc_html( $id).'" >'.esc_html( $value).' ('.$count.' people)</option>';
	}
	echo'</select><input class="button-primary" type="submit" value="'.esc_html( __( 'Go','church-admin') ).'" /></form></td></tr></tbody></table>'."\r\n";
    // number of total rows in the database
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.class.php');
    if( $items > 0)
    {

	$p = new caPagination;
	$p->items( $items);
	$page_limit=get_option('church_admin_pagination_limit');
	if ( empty( $page_limit) )  {$page_limit=20;update_option('church_admin_pagination_limit',20);}
	$p->limit( $page_limit); // Limit entries per page

	$p->target(wp_nonce_url("admin.php?page=church_admin/index.php&section=people&action=view-address-list&section=people&amp;member_type_id=".(int)$member_type_id,'view-address-list'));
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
	    $p->page = intval( $_GET['paging'] );
	}
        //Query for limit paging
	$limit = esc_sql("LIMIT " . ( $p->page - 1) * $p->limit  . ", " . $p->limit);


    //prepare WHERE clause using given Member_type_id
	$sort='last_name,first_name ASC';
	if(!empty( $_GET['sort'] ) )
	{
		switch( $_GET['sort'] )
		{
			case'date' :
				$sort='last_updated DESC';
			break;
			case'last_name':
			default:
				$sort='last_name,first_name ASC';
			break;
		}
	}
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people' ;
    if(!empty( $member_type_id) )$sql.=' WHERE member_type_id="'.esc_sql( $member_type_id).'"';
    $sql.=' GROUP BY household_id ORDER BY '.$sort.' '.$limit;

    $results=$wpdb->get_results( $sql);

    if(!empty( $results) )
    {
		if ( empty( $member_type[$member_type_id] ) )$member_type[$member_type_id]=esc_html( __( 'Whole','church-admin') );
		echo '<h2>'.esc_html(sprintf( __( '%1$s address list','church-admin' ) ,$member_type[$member_type_id] ) ).'</h2>';
	 	echo'<p><span class="ca-private">'.esc_html( __( 'Households not shown publicly have an orange background','church-admin') ).' </span></p>';
		// Pagination
    	echo '<div class="tablenav">'."\r\n";
		echo '<div class="tablenav-pages">'."\r\n";
    	echo $p->show();
    	echo '</div><!--tablenav-pages-->'."\r\n";
		echo'</div><!--tablenav-->'."\r\n";
    	//Pagination
    	//grab address details and associate people and put in table
		
		$theader='<tr><th>'.esc_html( __( 'Delete','church-admin') ).'</th>'."\r\n";
		$theader.= '<th>'.esc_html( __( 'Display household','church-admin') ).'</th>'."\r\n";
		$theader.= '<th><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=address-list&section=people&member_type_id='.(int)$member_type_id.'&sort=last_name','address-list').'">'.esc_html( __( 'Last name','church-admin') ).'</a></th>'."\r\n";
		$theader.= '<th>'.esc_html( __( 'First Name(s)','church-admin') ).'</th>';
		$theader.='<th>'.esc_html( __( 'People Type','church-admin') ).'</th>'."\r\n";
		$theader.='<th>'.esc_html( __( 'Address','church-admin') ).'</th>'."\r\n";
		$theader.= '<th><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=address-list&section=people&member_type_id='.(int)$member_type_id.'&sort=date','address-list').'">'.esc_html( __( 'Household last update','church-admin') ).'</a></th>'."\r\n";
		$theader.= '</tr>'."\r\n";


		echo '<table class="widefat striped">'."\r\n";
		echo'<thead>'."\r\n";
		echo $theader;
		echo '</thead>'."\r\n";
		echo '<tbody>'."\r\n";
		foreach( $results AS $row)
		{
	    	$first=1;//in case head of household not set
			$firstPeopleID=0;//in case head of household not set
			$firstLastName='';//in case head of household not set
	    	//grab address
	    	$add_row=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$row->household_id.'"');
            //church_admin_debug(print_r( $add_row,TRUE) );
	     	//grab people
	    	$people_results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$row->household_id.'" ORDER BY people_order,people_type_id ASC,sex DESC');
	    	$adults=$children=array();
	    	$prefix='';
			$private='';
			$head=0;

			$class=array();
			if(!empty( $add_row->privacy) )$class[]='ca-private';

	    	foreach( $people_results AS $people)
	    	{
				//setting head of household recover variables if needed later...
				if( $first==1)  {$firstPeopleID=$people->people_id; $firstLastName=$people->last_name;}
				$first++;
				if ( empty( $people->active) )$class[]='ca-deactivated';
				if ( $people->head_of_household==1)
				{
					$head=1;
					$last_name='';
					if(!empty( $people->prefix) )$last_name.=$people->prefix.' ';
					$last_name.=$people->last_name;
				}
				if ( empty( $last_name) )$last_name=esc_html( __( 'Add Surname','church-admin') );
				if ( empty( $people->first_name) )$people->first_name=esc_html( __( 'Add Firstname','church-admin') );
				if( $people->people_type_id=='1')  {$adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=people&action=edit_people&amp;household_id='.(int)$row->household_id.'&amp;people_id='.(int)$people->people_id,'edit_people').'">'.esc_html( $people->first_name).'</a>';}else{$children[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&section=people&household_id='.(int)$row->household_id.'&amp;people_id='.(int)$people->people_id,'edit_people').'">'.esc_html( $people->first_name).'</a>' ;}
				if(!empty( $people->prefix) )  {$prefix=$people->prefix.' ';}else{$prefix='';}
	    	}
	    	//check if there were anyone as head of household
	    	if( $head==0)
	    	{
				church_admin_debug('**** includes/directory.php church_admin_address_list NO HEAD OF HOUSEHOLD ******');
				church_admin_debug($people_results);
	
	    		//no head of household set so make first named person in household the head
	    		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE people_id="'.intval( $firstPeopleID).'"');
				church_admin_debug($wpdb->last_query);
	    		$last_name=esc_html( $firstLastName);
	    	}
	    	if(!empty( $adults) )  {$adult=implode(" & ",$adults);}else{ $adult=esc_html( __( "Add Name",'church-admin') );}
	    	if(!empty( $children) )  {$kids=' ('.implode(", ",$children).')';}else{$kids='';}

	    	$delete='<a onclick="return confirm(\''.esc_html( __( 'Are you sure?','church-admin') ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=people&action=delete_household&household_id='.$row->household_id.'&amp;member_type_id='.(int)$member_type_id,'delete_household').'">'.esc_html( __( 'Delete','church-admin') ).'</a>';
	    	//if ( empty( $add_row->address) )$add_row->address=esc_html( __( 'Add Address','church-admin') );
	    	if(!empty( $class) )  {$classes=' class="'.implode(" ",$class).'"';}else$classes='';

	   		echo '<tr '.$classes.' id="'.(int)$row->household_id.'">'."\r\n";
			echo '<td>'.$delete.'</td><td><a  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display-household&section=people&household_id='.$row->household_id,'display-household').'">'.esc_html( __( 'Display household','church-admin') ).'</a></td>'."\r\n";
			echo'<td><a  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display-household&section=people&household_id='.$row->household_id,'display-household').'">'.esc_html( $prefix.$last_name).'</a></td>'."\r\n";
			echo'<td>'.$adult.' '.$kids.'</td>'."\r\n";
			echo'<td>'.esc_html($people_types[$people->people_type_id]).'</td>';
			echo '<td>';
			//changed to direct edit link 2018-04-09
	   		echo '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_household&amp;household_id='.$row->household_id,'edit_household').'">';
	   		if(!empty( $add_row->address) )  {echo esc_html( $add_row->address);}else{echo esc_html( __( 'Add Address','church-admin') );}
            $updated_by='';
            if(!empty( $add_row->updated_by) )
            {
                $updated_by=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name, last_name) FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.intval( $add_row->updated_by).'"');
            }
            
			echo'</a></td>'."\r\n";
			echo '<td>'.mysql2date('d/M/Y',$add_row->last_updated).'<br>'.$updated_by.'</td>'."\r\n";
			echo '</tr>';
		}


		echo '</tbody><tfoot>'.$theader.'</tfoot></table>';
    
    	// Pagination
    	echo '<div class="tablenav"><div class="tablenav-pages">';
    	echo $p->show();
    	echo '</div></div>';
		
    	//Pagination

    }//end of items>0
		
    }	else{
		echo'<p>'.esc_html( __( 'There are no people in that member type category','church-admin') ).'</p>';
	}




}
 /**
 *
 * Edit Household
 *
 * @author  Andy Moyle
 * @param    $household_id
 * @return   html
 * @version  0.1
 *
 */
function church_admin_edit_household( $household_id=NULL)
{

    global $wpdb;
	$member_type=church_admin_member_types_array();
    if(is_user_logged_in() )$user=wp_get_current_user();
    


    $member_type_id=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'"  ORDER BY people_type_id ASC LIMIT 1');
    if(!empty( $household_id) )  {
		$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"');
	}else{
		$data=new stdClass();
	}
    if(!empty( $_POST['edit_household'] ) )
    {//process form
		$private=NULL;
		if(!empty( $_POST['show_me'] ) )  {$private=0;}else{$private=1;}
		$form=array();
		foreach ( $_POST AS $key=>$value)$sql[$key]=esc_sql(church_admin_sanitize( $value));
		$sql['lat']=!empty($sql['lat'])?$sql['lat']:null;
		$sql['lng']=!empty($sql['lng'])?$sql['lng']:null;
		$sql['wedding_anniversary'] = (!empty($sql['wedding_anniversary'] )&&church_admin_checkdate($sql['wedding_anniversary'] ))?$sql['wedding_anniversary'] :null;

		if(!$household_id)$household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_household WHERE address="'.$sql['address'].'" AND mailing_address="'.$sql['mailing_address'].'" AND lat="'.$sql['lat'].'" AND lng="'.$sql['lng'].'" AND phone="'.$sql['phone'].'"');
		if(!$household_id)
		{//insert
	    	$success=$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,lat,lng,mailing_address,phone,privacy,attachment_id,first_registered) VALUES("'.$sql['address'].'", "'.$sql['lat'].'","'.$sql['lng'].'","'.$sql['mailing_address'].'","'.$sql['phone'].'","'.esc_sql($private).'","'.$sql['household_attachment_id'].'" ,"'.esc_sql(wp_date('Y-m-d')).'")');
	    $household_id=$wpdb->insert_id;
		}//end insert
		else
		{//update
	   	 	$sql='UPDATE '.$wpdb->prefix.'church_admin_household SET wedding_anniversary="'.$sql['wedding_anniversary'].'", address="'.$sql['address'].'" , lat="'.$sql['lat'].'" , lng="'.$sql['lng'].'",mailing_address="'.$sql['mailing_address'].'" , phone="'.$sql['phone'].'", privacy="'.$private.'" , attachment_id="'.$sql['household_attachment_id'].'" WHERE household_id="'.(int)$household_id.'"';
	   	 //echo $sql;
	   		$success=$wpdb->query( $sql);
		}//update
        if(!empty( $user->ID) )$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET updated_by="'.(int)$user->ID.'" WHERE household_id="'.(int)$household_id.'"');
		if( $success)
		{
	    	echo '<div class="notice notice-success inline"><p><strong>'.esc_html( __( 'Address saved','church-admin') ).' <br><a href="./admin.php?page=church_admin/index.php&section=people&action=church_admin_address_list&member_type_id='.$member_type_id.'">'.esc_html( __( 'Back to Directory','church-admin') ).'</a></strong></td></tr></div>';
		}
	    echo'<div id="post-body" class="metabox-holder columns-2"><!-- meta box containers here -->';

		echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __( 'Household Edited','church-admin') ).' <br>';
		if(church_admin_level_check('Directory') ) echo'<a href="./admin.php?page=church_admin/index.php&section=people&action=church_admin_address_list&member_type_id='.$member_type_id.'">'.esc_html( __( 'Back to Directory','church-admin') ).'</a>';
		echo'</strong></td></tr></div>';
        update_option('addressUpdated',time() );
		church_admin_head_of_household_tidy( $household_id);
		church_admin_new_household_display( $household_id);


    }//end process form
    else
    {//household form
		if(!empty( $household_id) )  {$text='Edit ';}else{$text='Add ';}
		echo '<form action="" method="post">';
		//clean out old style address data
		if(!empty( $data->address)&&is_array(maybe_unserialize( $data->address) ))
		{
			$data->address=implode(", ",array_filter(maybe_unserialize( $data->address) ));
		}
		echo church_admin_address_form( $data,$error=NULL);
		//Phone
    	echo '<table class="form-table"><tr><th scope="row">'.esc_html( __( 'Phone','church-admin') ).'</th><td><input type="text" name="phone" ';
		if(!empty( $data->phone) ) echo ' value="'.esc_html( $data->phone).'"';
    	if(!empty( $errors['phone'] ) )echo' class="red" ';
    	echo '/></td></tr>';
        //Default to private
        $privacy=1;
        if(isset( $data->privacy)&&$data->privacy==0)$privacy=0;
    	
		echo'<tr><th scope="row">'.esc_html( __( 'Show household on the password protected address list','church-admin') ).'</th><td><input type="checkbox" name="show_me" value="1" '.checked(0,$privacy,FALSE).'/></td></tr>';

		echo'<tr><td colspan="2"><input type="hidden" name="edit_household" value="yes" /><input class="button-primary" type="submit" value="'.esc_html( __( 'Save Address','church-admin') ).'&raquo;" /></th></tr></table></form>';
    }//end household form


}



 /**
 *
 * Edit a person
 *
 * @author  Andy Moyle
 * @param    $people_id,$household_id
 * @return
 * @version  0.2
 *
 * 0.11 added photo upload 2012-02-24
 * 0.2 added site_id, marital status 2016-05-12
 *
 */


function church_admin_edit_people( $people_id=NULL,$household_id=NULL)
{
	church_admin_debug('*** church admin edit people ****');
	church_admin_debug(func_get_args());
	$onboarding = !empty($people_id) ? 0 : 1; //set onboarding if no people id
    global $wpdb,$people_type,$current_user;
	$member_type=church_admin_member_types_array();
	
	$church_admin_marital_status=get_option('church_admin_marital_status');
	if ( empty( $church_admin_marital_status) )
	{
		$church_admin_marital_status=array(0=>esc_html( __( 'N/A','church-admin') ),
		1=>esc_html( __( 'Single','church-admin') ),
		2=>esc_html( __( 'Co-habiting','church-admin') ),
		3=>esc_html( __( 'Married','church-admin') ),
		4=>esc_html( __( 'Divorced','church-admin') ),
		5=>esc_html( __( 'Widowed','church-admin') )
		)	;
		update_option('church_admin_marital_status',$church_admin_marital_status);
	}
  	


    
	

    if( !empty($people_id)){
		$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	}
		if ( empty( $data) ) $data = new stdClass();

    if(!empty( $data->household_id) )$household_id=$data->household_id;
    if(!empty( $_POST['edit_people'] ) )
    {//process


    	if ( empty( $household_id) )
		{
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,first_registered) VALUES("","'.esc_sql(wp_date('Y-m-d')).'")');
			$household_id=$wpdb->insert_id;
		}


		church_admin_debug('Line 555 People_id:' . $people_id);
    	$output=church_admin_save_person(1,$people_id,$household_id,$onboarding);
        update_option('addressUpdated',time() );



		church_admin_head_of_household_tidy( $household_id);
		echo'<div class="notice notice-success inline"><p><strong>'.wp_kses_post( __( 'Person Edited','church-admin') ).' <br>'.$output['output'];
		if(church_admin_level_check('Directory') &&!empty( $sql['member_type_id'] ) ) echo'<a href="./admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&section=people&amp;member_type_id='.$sql['member_type_id'].'">'.esc_html( __( 'Back to Directory','church-admin') ).'</a>';
		echo'</strong></td></tr></div>';
		
		church_admin_new_household_display( $household_id);

        update_option('addressUpdated',time() );//for the app

    }//end process
    else
    {//form
		echo'<h2>'.esc_html( __( 'Edit Person','church-admin') ).'</h2>';
		if( $people_id){
			$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		}

		echo'<form action="" method="POST" enctype="multipart/form-data">';

		echo church_admin_edit_people_form(1,$data,NULL,$onboarding);
		if(!empty( $data->user_id ) )
		{
			echo'<table class="form-table"><tr><th scope="row">'.esc_html( __( 'Wordpress User','church-admin') ).'</th><td><input type="hidden" name="ID" value="'.esc_html( $data->user_id).'" />';
			$user_info=get_userdata( $data->user_id);
			if(!empty( $user_info) )
			{
				echo '<span class="username">'.esc_html( __( 'Username','church-admin') ).': '.$user_info->user_login.'<span class="unattach_user"><span class="ca-dashicons dashicons dashicons-no"></span></span><br>'.esc_html( __( 'User level','church-admin') ).': '.$user_info->roles['0'].'</span>';

			$nonce = wp_create_nonce("church_admin_unattach_user");
			echo'<script >jQuery(document).ready(function( $) {
			$(".unattach_user").click(function() {
			var data = {
			"action": "church_admin",
			"method": "unattach_user",
			"people_id": '.(int)$data->people_id.',
			"user_id": '.intval( $data->user_id).',
			"nonce": "'.$nonce.'"
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {console.log(response);
			$(".username").html("'.esc_html( __( 'User disconnected - refresh page to reconnect to a user account','church-admin') ).'");
		});

			});
			});</script>';

			}
			echo'</td></tr></table>';
		}
		else
		{
            echo'<table class="form-table">';
			echo church_admin_username_form();
            echo'</table>';
		}



		echo'<table class=form-table"><tr><th scope="row"><input type="hidden" name="edit_people" value="yes" /><input class="button-primary" type="submit" value="'.esc_html( __( 'Save Details','church-admin') ).'&raquo;" /></td></tr></tbody></table></form>';
		
	}//form
    

}
 
 
 /**
 *
 * Table row for username entry
 *
 * @author  Andy Moyle
 * @param
 * @return   html
 * @version  0.1
 *
 */
function church_admin_username_form()
{
	if(!church_admin_level_check('Directory') )wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') ) );
	global $wpdb;
			$sql='SELECT user_login,ID FROM '.$wpdb->prefix.'users WHERE `ID` NOT IN (SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id!=0) ORDER BY user_login';
			$users=$wpdb->get_results( $sql);
			$out='';
			if(!empty( $users) )
			{
					$out.='<tr><th scope="row">'.esc_html( __( 'Choose a Wordpress account to associate','church-admin') ).'</th><td><select name="ID"><option value="">'.esc_html( __( 'Select a user...','church-admin') ).'</option>';
					foreach( $users AS $user) $out.='<option value="'.esc_html( $user->ID).'">'.esc_html( $user->user_login).'</option>';
					$out.='</select></td></tr>';
			}
			$out.='<tr><th scope="row">'.esc_html( __( 'Or create a new Wordpress User','church-admin') ).'</th><td><input id="username" type="text" placeholder="'.esc_html( __( 'Username','church-admin') ).'" name="username" value="" /><span id="user-result"></span></td></tr>'."\r\n";
			$nonce = wp_create_nonce("church_admin_username_check");
			$out.='<script >jQuery(document).ready(function( $) {
			$("#username").change(function() {
			var username=$("#username").val();
			var data = {
			"action": "church_admin",
			"method":"username_check",
			"user_name": username,
			"nonce": "'.$nonce.'"
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {console.log(response);
			$("#user-result").html(response);
		});

			});
			});</script>';
	return $out;
}
 
 
 


 /**
 *
 * Address form
 *
 * @author  Andy Moyle
 * @param    $data, $error
 * @return   html
 * @version  0.1
 *
 */
function church_admin_address_form( $data,$error)
{
    global $wpdb;
	$method='update-directory';
	$nonce=wp_create_nonce("update-directory");
	$ID=!empty($data->household_id)?(int)$data->household_id:null;
	if(empty($ID))
	{
		//probably small group, so check that one
		$ID=!empty($data->id)?(int)$data->id:null;
		$method='update-sg';
		$nonce=wp_create_nonce("update-sg");
	}
	if(empty($ID))
	{
		//probably site id
		$ID=!empty($data->site_id)?(int)$data->site_id:null;
		$method='update-site';
		$nonce=wp_create_nonce("update-site");
	}
    //echos form contents where $data is object of address data and $error is array of errors if applicable
	$api=get_option('church_admin_google_api_key');
    if ( empty( $data) )$data=(object)'';
    if(!empty( $_GET['action'] )&& $_GET['action']=="edit_site")  {$out='<h3>'.esc_html( __( 'Edit Site Details','church-admin') ).'</h3>';}else{$out='<h3>'.esc_html( __( 'Edit Household Details','church-admin') ).'</h3>';}
    if(!empty( $errors) )$out.='<p>'.esc_html( __( 'There were some errors marked in red','church-admin') ).'</p>';
	if(!empty( $api) )
	{
		$out.='<script >var ca_method="update-directory";'."\r\n";
            if(!empty( $data->lat) && !empty( $data->lat) )
            {//initial data for position already available
                
				$out.='var zoom=17;'."\r\n";
				$out.='var ID='.(int)$ID."\r\n";
				$out.='var nonce="'.$nonce.'"'."\r\n";
				$out.='var beginLat='.esc_attr( $data->lat).';'."\r\n";
                $out.= 'var beginLng='.esc_attr( $data->lng)."\r\n";
				$out.= 'var ca_method="'.esc_attr( $method).'"'."\r\n";
				$out.='var what="geocode"'."\r\n";
               
            }else
            {
                $out.='var zoom=17;'."\r\n";
				$out.='var ID='.(int)$ID."\r\n";
				$out.='var nonce="'.esc_attr($nonce).'"'."\r\n";
				$out.='var beginLat=0;'."\r\n";
                $out.= 'var beginLng=0;'."\r\n";
				$out.= 'var ca_method="'.esc_attr( $method).'"'."\r\n";
				$out.='var what="geocode"'."\r\n";
            }
			$out.="\r\n".'</script>';
	}
   	/*************************************
	*
	*	Image
	*
	*************************************/

		$out.='<h3>'.esc_html( __( 'Photo','church-admin') ).'</h3><div class="church-admin-form-group"><label>';
		if(!empty( $data->attachment_id) )
		{
			$out.=wp_get_attachment_image( $data->attachment_id,'medium','', array('class'=>"current-photo",'id'=>"household-frontend-image") );

		}
		else
		{
			$out.= '<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="300" height="200" id="household-frontend-image"  alt="'.esc_html( __( 'Photo of Person','church-admin') ).'"  />';
		}
        $out.='</label>';
		if(is_admin() )
		{//on admin page so use media library
			$out.='<button id="household-image"  class=" button-secondary household-upload-button button" >'.esc_html( __( 'Upload Image','church-admin') ).'</button>';

		}else
		{//on front end so use boring update
			$out.='<input type="file" id="file-chooser" class="file-chooser" name="logo" style="display:none;" /><input type="button" id="household_image" class="household-frontend-button" value="'.esc_html( __( 'Upload Photo','church-admin') ).'" />';
    	}
	    $out.='<input type="hidden" name="household_attachment_id" class="attachment_id" id="household_attachment_id" ';
    	if(!empty( $data->attachment_id) )$out.=' value="'.intval( $data->attachment_id).'" ';
    	$out.='/><span id="household-upload-message"></span>';
    	$out.='</div>';


    $out.= '<div class="church-admin-form-group"><label>'.esc_html( __( 'Street Address','church-admin') ).'</label><input  type="text" id="address" name="address" ';
    if(!empty( $data->address) ) $out.=' value="'.esc_html( $data->address).'" ';
	$out.=' class="church-admin-form-control ';
    if(!empty( $error['address'] ) ) $out.= 'red';
    $out.= '" /></div>';
	$api_key=get_option('church_admin_google_api_key');
    if(!empty( $api_key) )
	{
		if(!isset( $data->lng) )$data->lng='51.50351129583287';
    	if(!isset( $data->lat) )$data->lat='-0.148193359375';
    	$out.= '<div class="church-admin-form-group"><label><button id="geocode_address" class="button-primary btn btn-info">'.esc_html( __( 'Update map','church-admin') ).'</button></label><span id="finalise" ></span><input type="hidden" name="what-three-words" id="what-three-words" name="what-three-words" /><input type="hidden" name="lat" id="lat" value="'.$data->lat.'" /><input type="hidden" name="lng" id="lng" value="'.$data->lng.'" /><div id="map" style="width:500px;height:300px;margin-bottom:20px"></div></div>';
	}
	
	//check for what three words
	$w3w=get_option('church_admin_what_three_words');
	$w3wLanguage=get_option('church_admin_what_three_words_language');
	if(empty($w3wLanguage)){
		$w3wLanguage='en';
	}
	if(isset( $w3w) )
	{
		$out.='<script>'."\r\n";
		$out.='const useW3W=true;'."\r\n";
		$out.='const w3wLanguage="'.esc_html( $w3wLanguage).'";'."\r\n";
		
		$out.='</script>'."\r\n";
	}
	
   		if(is_admin() )
   		{
   			$out.='<script >jQuery(document).ready(function( $)  {


		 	//remove image
		 	$(".remove-image").click(function()
		 	{
		 			var type= $(this).data("type");
		 			var attachment_id=$(this).data("attachment_id");
		 			var id=$(this).data("id");

		 			var nonce="'.wp_create_nonce("remove-image").'";
		 			var data={"action":"church_admin","method":"remove-image","type":type,"attachment_id":attachment_id,"id":id,"nonce":nonce};
		 			console.log(data);
		 			$.ajax({
		 								url: ajaxurl,
		 								type: "POST",
		 								data: data,
		 								success: function(res) {
		 									console.log(res);
		 									$("#upload-message").html("'.esc_html( __( "Image Deleted","church-admin") ).'<br>");
		 									$("#household-frontend-image").attr("src","'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'");
		 									$("#household-frontend-image").attr("srcset","");
		 									$("#attachment_id").val("");
		 								},
		 								error: function(res) {
		 							$("#upload-message").html("Error deleting<br>");
		 								}
		 						 });
		 	});

  var mediaUploader;

  $(".household-upload-button").click(function(e) {
    e.preventDefault();
    var id="#household_attachment_id";
    console.log(id);
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: "Choose Image",
      button: {
      text: "Choose Image"
    }, multiple: false });

    // When a file is selected, grab the URL and set it as the text fields value
    mediaUploader.on("select", function() {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      console.log(attachment);
      $(id).val(attachment.id);
      if(attachment.sizes.medium.url)
      {
        $("#household-frontend-image").attr("src",attachment.sizes.medium.url);
      }
      else{$("#household-frontend-image").attr("src",attachment.sizes.thumbnail.url);}
      $("#household-frontend-image").attr("srcset",null);
    });
    // Open the uploader dialog
    mediaUploader.open();
  });

	});</script>';

    }else
    {
   $out.='<script>
	jQuery(document).ready(function( $) {
	$(".household-frontend-button").click(function()  { $("#file-chooser").trigger("click"); });
	$( "body" ).on("change","#file-chooser", function( event ) {


	$("#household-frontend-image").attr("src","'.admin_url().'/images/wpspin_light-2x.gif");
	$("#household-frontend-image").attr("srcset","");
	var data = new FormData();
	jQuery.each(jQuery("#file-chooser")[0].files, function(i, file) {
    data.append("file-"+i, file);
	});
	$.ajax({
        		url: "'.admin_url().'admin-ajax.php?action=church_admin_image_upload",
        		type: "POST",
        		data: data,
        		processData: false,
        		contentType: false,
        		success: function(res) {
        		var image=JSON.parse(res);
        		console.log(image);

        			$("#household-upload-message").html("'.esc_html( __( 'Success uploading', 'church-admin' ) ).'<br>");
        			$("#household-frontend-image").attr("src",image.src);
        			$("#household-frontend-image").attr("srcset","");
        			$("#household_attachment_id").val(image.attachment_id);
        		},
        		error: function(res) {
					$("#upload-message").html("'.esc_html( __( "Error uploading, please try again", 'church-admin' ) ).'<br>");
         		}
         });
    });

	});
	</script>';

	}
	$out.= '<div class="church-admin-form-group"><label>'.esc_html( __( 'Mailing Address','church-admin') ).'</label><input  type="text" id="mailing_address" name="mailing_address" ';
    if(!empty( $data->mailing_address) ) $out.=' value="'.esc_html( $data->mailing_address).'" ';
	$out.=' class="church-admin-form-control ';
    if(!empty( $error['mailing_address'] ) ) $out.= 'red';
    $out.= '" /></div>';

	//wedding_anniversary
	$wa=get_option('church_admin_show_wedding_anniversary');
	if(!empty($wa)){
		$wedding_anniversary = !empty($data->wedding_anniversary) ? $data->wedding_anniversary:null;
		$out.= '<div class="church-admin-form-group"><label>'.esc_html( __( 'Wedding Anniversary','church-admin') ).'</label>';
		$out.= church_admin_date_picker( $wedding_anniversary,'wedding_anniversary',FALSE,'1910',NULL,'wedding_anniversary','wedding_anniversary',FALSE,NULL,NULL,NULL);
		$out.='</div>';
	}

	$custom_fields=church_admin_get_custom_fields();
	if(!empty( $custom_fields) )
	{
		
		foreach( $custom_fields AS $id=>$field)
		{
			if( $field['section']!='household')continue;
			$dataField='';
			$household_id=null;
			if(!empty($data->household_id)){
				$household_id= $data->household_id;
				$dataField=$wpdb->get_var('SELECT `data` FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE section="household" AND household_id="'.(int)$data->household_id.'" AND custom_id="'.(int)$id.'"');
				//church_admin_debug($wpdb->last_query);
				church_admin_debug('Custom: '.$field['name']);
				//church_admin_debug($dataField);
			}
			
			echo'<div class="church-admin-address church-admin-form-group" ><label>'.esc_html( $field['name'] ).'</label>';
			switch( $field['type'] )
			{
				case 'boolean':
					echo'<input type="radio" data-what="household-custom" data-id="'.(int)$household_id.'" data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable" name="custom-'.(int)$id.'" value="1" ';
					if (!empty( $dataField))echo ' checked="checked" ';
					echo '>'.esc_html( __( 'Yes','church-admin') ).'<br> <input type="radio"  data-id="'.(int)$household_id.'"  data-what="household-custom" data-ID="'.(int)$household_id.'" data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable" value="0" name="custom-'.(int)$id.'" ';
					if (empty( $dataField)) echo  'checked="checked" ';
					echo '>'.esc_html( __( 'No','church-admin') );
					break;
				case'text':
					echo '<input type="text"  data-what="household-custom" data-id="'.(int)$household_id.'"  data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable"  name="custom-'.(int)$id.'" ';
					if(!empty( $thisHouseholdCustomData->data) || isset( $field['default'] ) )echo ' value="'.esc_html( $dataField).'"';
					echo '/>';
				break;
				case'date':
					
					echo church_admin_date_picker( $dataField,'custom-'.(int)$id,FALSE,1910,date('Y'),'custom-'.(int)$id,'custom-'.(int)$id,FALSE,'household-custom',(int)$household_id,(int)$id);
				
				break;
				case 'checkbox':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						echo '<div class="checkbox"><label><input type="checkbox" data-what="household-custom" data-id="'.(int)$household_id.'"  data-custom-id="'.(int)$id.'"  data-type="checkbox" class="church-admin-editable custom-'.(int)$id.'" name="custom-'.(int)$id.'[]" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && is_array($dataField) && in_array($options[$y],$dataField) ){echo ' checked="checked" ';}
						
						echo '> '.esc_html($options[$y]).'</label></div>';
					}
				break;
				case 'radio':
					$options = maybe_unserialize($field['options']);
				
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						echo '<div class="checkbox"><label><input type="radio" data-id="'.(int)$household_id.'" data-what="household-custom"  data-type="radio" data-custom-id="'.(int)$id.'" class="church-admin-editable"  name="custom-'.(int)$id.'" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && $options[$y] == $dataField) {echo ' checked="checked" ';}
						echo '> '.esc_html($options[$y]).'</label></div>';
					}
				break;	
				case 'select':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					echo '<select name="custom-'.(int)$id.'" class="church-admin-form-control church-admin-editable" data-id="'.(int)$household_id.'" data-what="household-custom"  data-type="radio" data-custom-id="'.(int)$id.'"><option>'.esc_html( __( 'Choose' , 'church-admin' ) ) .'</option>';
					for ($y=0;$y<count($options);$y++){
						echo '<option value="'.esc_attr($options[$y]).'" '.selected($options[$y],$dataField,FALSE).' data-what="household-custom" data-type="select" data-custom-id="'.(int)$id.'"> '.esc_html($options[$y]).'</option>';
					}
					echo '</select>';
				break;
			}
			echo '</div>';
			
		}

	}

	
    
    return $out;

}
 /**
 *
 * Display household
 *
 * @author  Andy Moyle
 * @param    $household_id
 * @return   html
 * @version  0.1
 *
 */
function church_admin_new_display_person( $people_id)
{
    global $wpdb;
    $person_template='<div class="ca-person">
    <div class="ca-name">
        <div class="first_name"><!--first_name--></div><div class="prefix"><!--prefix--></div><div class="last_name"><!--last_name--></div>
    </div>
	<p  data-colname="'.esc_html( __('Active','church-admin' ) ).'"><span class="activate ca-active" id="active-PEOPLEID">ACTIVESTATUS</span> </p>		
    <div class="ca=picture"><!--picture--></div>
    <div class="ca-email"><span class="ca-dashicons dashicons dashicons-email-alt"></span><!--email--></div>
    <div class="ca-cell"><span class="ca-dashicons dashicons dashicons-smartphone"></span><!--cell--></div>
    <div class="ca-dob"><span class="ca-dashicons dashicons dashicons-buddicons-community"></span><!--dob--></div>
    <div class="ca-pt"><span class="ca-dashicons dashicons dashicons-admin-users"></span><!--member_type-->
    <div class="ca-mt"><span class="ca-dashicons dashicons dashicons-businessperson"></span><!--member_type--></div>
    <div class="ca-group"><!--groups--></div>
    <div class="ca-classes><span class="ca-dashicons dashicons dashicons-welcome-learn-more"></span><!--classes--></div>
    <div class="ca-att"><span class="ca-dashicons dashicons dashicons-chart-line"></span><!--attendance--></div>
    <div class="ca-giving"><span class="ca-dashicons dashicons dashicons-money-alt"></span><!--giving--></div>
    </div>';
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    $output=$person_template;
	$output=str_replace("PEOPLEID",(int) $person->people_id,$output);
	if(!empty( $person->active) )  {
		
		$output=str_replace("ACTIVESTATUS",esc_html( __('Active','church-admin')),$output);
	}else{
		$output=str_replace("ACTIVESTATUS",esc_html( __('Inactive','church-admin')),$output);
	}
    $output=str_replace("<!--first_name-->",esc_html( $person->first_name),$output);
    if(!empty( $person->prefix) )$output=str_replace("<!--prefix-->",esc_html( $person->prefix),$output);
    $output=str_replace("<!--last_name-->",esc_html( $person->last_name),$output);
    $output=str_replace("<!--email-->",esc_html( $person->last_name),$output);
	$output.='<script>$("body").on("click",".activate",function()  {
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
			console.log(data.status)
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
	});</script>';
	return $output;
}

function church_admin_migrate_users()
{
    global $wpdb;
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->users);
    if( $results)
    {
	foreach( $results AS $row)
	{
	    $check=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$row->ID.'"');
	    if(!$check)
	    {
			$user_info=get_userdata( $row->ID);
			$address='';
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household(member_type_id,address)VALUES("1","'.$address.'")');
			$household_id=$wpdb->insert_id;
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,household_id,user_id,member_type_id,people_type_id,smallgroup_id,sex,first_registered) VALUES("'.esc_sql($user_info->first_name).'","'.esc_sql($user_info->last_name).'","'.esc_sql($user_info->user_email).'","'.(int)$household_id.'","'.(int)$row->ID.'","1","1","0","1","'.esc_sql(mysql2date('Y-m-d',$row->user_registered)).'")');
	    }
	}

	echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __( 'Wordpress Users migrated','church-admin') ).'</strong></td></tr></div>';
    }

    church_admin_address_list();
}
 /**
 *
 * Move person
 *
 * @author  Andy Moyle
 * @param    $people_id
 * @return   html
 * @version  0.1
 *
 */
function church_admin_move_person( $people_id)
{
    global $wpdb;
        $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    $message='';
    if(!empty( $data) )
    {

		if(!empty( $_POST['move_person'] ) )
		{
			//handle if person being moved is head of household
			if(!empty( $data->head_of_household) )
			{//need to reassign head of household
				$message.= esc_html(sprintf(  __(  '%1$s was head of household','church-admin' ) ,$data->first_name.' '.$data->last_name) ).'<br>';
				//look for another adult
				$next_person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$data->household_id .'" AND people_type_id=1 AND people_id!="'.(int)$people_id.'" LIMIT 1');
				if(!empty( $next_person) ){
					$message.=esc_html( sprintf(  __(  'Head of household reassigned to %1$s','church-admin' ) ,$next_person->first_name.' '.$next_person->last_name) ) .'<br>';
				}
				//no adult, find someone!
				if ( empty( $next_person->people_id) ){
					$next_person=$wpdb->get_row('SELECT * from '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$data->household_id.'"  AND people_id!="'.(int)$people_id.'" AND people_type_id=1 LIMIT 1');
				}
				if(!empty( $next_person) ){
					$message.=esc_html( sprintf( __(  'Head of household reassigned to %1$s','church-admin' ) ,$next_person->first_name.' '.$next_person->last_name) ) .'<br>';
				}
				else{$message='';}
				//set new head of hosuehold
				if(!empty( $next_person->people_id) )
				{
					church_admin_debug(' ***** church_admin_move_person SET head of household ******');
					$sql='UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE people_id="'.intval( $next_person->people_id).'"';
					church_admin_debug($sql);
					$wpdb->query( $sql);
				}
				//stop them being head of household!
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=0 WHERE people_id="'.(int)$people_id.'"');
			}

	    	if(!empty( $_POST['create'] ) )
			{
				$sql='INSERT INTO '.$wpdb->prefix.'church_admin_household ( address,lat,lng,phone ) SELECT address,lat,lng,phone FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int) $data->household_id.'";';
				church_admin_debug(' ***** church_admin_move_person create household and set head of household ******');
				$wpdb->query( $sql);
				$household_id=$wpdb->insert_id;
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET household_id="'.(int)$household_id.'",head_of_household=1 WHERE people_id="'.(int)$people_id.'"');
				church_admin_debug($swpdb->last_query);
				$message.=esc_html(sprintf(  __(  '%1$s has been moved to a new household with the same address','church-admin' ) ,$data->first_name.' '.$data->last_name) );
				echo'<div class="notice notice-success inline"><p><strong>'.$message.'</strong></td></tr></div>';

			}
			else
			{
				//remove household entry if only one person was in it.
				$no=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int) $data->household_id.'"');
				if( $no==1)$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int) $data->household_id.'"');
				//move the person to the new household
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET household_id="'.esc_sql( (int)$_POST['household_id'] ).'" WHERE people_id="'.esc_sql( (int)$people_id).'"');
				$message.=esc_html( sprintf( __(  '%1$s has been moved','church-admin' ) ,$data->first_name.' '.$data->last_name) );
				echo'<div class="notice notice-success inline"><p><strong>'.$message.'</strong></td></tr></div>';
				$household_id=(int)$_POST['household_id'];
			}
	    	church_admin_new_household_display( $household_id);

		}
		else
		{
	   		echo'<h2>Move '.esc_html( $data->first_name).' '.esc_html( $data->last_name).'</h2>';

	    	$results=$wpdb->get_results('SELECT a.last_name,a.first_name, a.household_id,b.member_type FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_member_types b WHERE b.member_type_id=a.member_type_id GROUP BY a.household_id,a.last_name ORDER BY a.last_name');
	    	if(!empty( $results) )
	    	{
				echo'<form action="" method="post">';
				echo'<tr><th scope="row">'.esc_html( __( 'Create a new household with same address','church-admin') ).'</th><td><input type="checkbox" name="create" value="yes" /></td></tr>';
				echo'<tr><th scope="row">'.esc_html( __( 'Move to household','church-admin') ).'</th><td><select name="household_id"><option value="">'.esc_html( __( 'Select a new household...','church-admin') ).'</option>';
				foreach( $results AS $row)
				{
		    		echo'<option value="'.esc_html( $row->household_id).'">'.esc_html( $row->last_name).', '.esc_html( $row->first_name).' '.'('.$row->member_type.')</option>';
				}
				echo'</select></td></tr>';
				echo'<p><input type="hidden" name="move_person" value="yes" /><input type="submit" class="button-primary" value="'.esc_html( __( 'Move person','church-admin') ).'" /></td></tr>';
				echo'</form>';
	    	}
		}
    }else{
		echo'<div class="notice notice-warning inline"><h2>'.esc_html( __( "Oh No! Couldn't find the person you want to move",'church-admin') ).'</h2></div>';
	}
}
 /**
 *
 * Create user for all people with email address
 *
 * @author  Andy Moyle
 * @param    $people_id
 * @return   html
 * @version  0.1
 *
 */
 function church_admin_users()
 {
 		global $wpdb;
 		echo'<h2>'.esc_html( __( 'Create user accounts for every one with an email address','church-admin') ).'</h2>';
 		if(!empty( $_POST['create_users_member_type_form'] ) )
 		{
			$mts = !empty( $_POST['member_type_id'] ) ? church_admin_sanitize( $_POST['member_type_id'] ):array();
 			foreach( $mts  AS $key=>$member_type_id)
 			{
 				$sql='SELECT CONCAT(first_name,last_name) AS username,people_id,household_id FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.(int)$member_type_id.'"  AND email!=""';
				$results=$wpdb->get_results( $sql);
				if(!empty( $results) )
				{
					foreach( $results AS $row)
                    {
                        echo church_admin_create_user( $row->people_id,$row->household_id,$row->username,null);
                    }
				}

			}
			echo'<div class="notice notice-sucess inline"><h2>'.esc_html( __( 'Users created','church-admin') ).'</h2</div>';
 		}
 		else
 		{
 			echo'<form action="" method="POST">';

 			$member_type=church_admin_member_types_array();
 			foreach( $member_type AS $key=>$value)
			{
				echo'<p><input type="checkbox" name="member_type_id[]" value="'.esc_html( $key).'" />'.esc_html( $value).'</p>';

			}
			echo'<p><input type="hidden" name="create_users_member_type_form" value="yes" /><input type="submit" class="button-primary" value="'.esc_html( __( 'Create users','church-admin') ).'" /></p></form>';
 		}

 }


function church_admin_confirmed_users()
{
    echo'<h2>'.esc_html( __( 'Create user accounts for GDPR confirmed directory entries','church-admin') ).'</h2>';
    echo'<p>'.esc_html( __( 'User accounts will be created for users with a unique email who have confirmed GDPR','church-admin') ).'</p>';
	global $wpdb;
	$sql='SELECT CONCAT(first_name,last_name) AS username,people_id,household_id FROM '.$wpdb->prefix.'church_admin_people WHERE (gdpr_reason IS NOT NULL OR gdpr_reason!="") AND email!=""';
	$results=$wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		foreach( $results AS $row){
			echo church_admin_create_user( $row->people_id,$row->household_id,$row->username,null);
		}
		echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __( 'Users created and updated','church-admin') ).'</strong></p></div>';
	}
	else{echo'<div class="notice notice-warning inline"><p><strong>'.esc_html( __( 'No GDPR confirmed people with an email to create a user account','church-admin') ).'</strong></p>';}

}


 




function church_admin_get_capabilities( $id)
{
    if ( empty( $id) )return FALSE;
    $user_info=get_userdata( $id);
    if ( empty( $user_info) )return FALSE;
    $cap=$user_info->roles;

	if (in_array('subscriber',$cap) )return 'Subscriber';
	if (in_array('author',$cap) )return 'Author';
	if (in_array('editor',$cap) )return  'Editor';
	if (in_array('administrator',$cap) ) return 'Administrator';
	return FALSE;
}


 /**
 *
 * Search
 *
 * @author  Andy Moyle
 * @param
 * @return   html
 * @version  0.1
 *
 */
function church_admin_search( $search)
{
    global $wpdb;
	$people_types=get_option('church_admin_people_type');
    //$wpdb->show_errors();
    echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=people&action=add-household','add-household').'">'.esc_html( __( 'Add a Household','church-admin') ).'</a> </p>';
	
    $s=sanitize_text_field(stripslashes( $search ) );
  
    if(!empty( $_REQUEST['all-records'] ) )
    {
        $active='';
    }
    else{$active=' a.active=1 AND ';}
   
    $sql='SELECT a.*, b.last_updated AS householdUpdated,b.address AS address,b.phone FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND '.$active.' (CONCAT_WS(" ",a.first_name,a.last_name) LIKE("%'.esc_sql($s).'%")||CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) LIKE("%'.esc_sql($s).'%")||a.first_name LIKE("%'.esc_sql($s).'%")||a.last_name LIKE("%'.esc_sql($s).'%")||a.nickname LIKE("%'.esc_sql($s).'%")||a.email LIKE("%'.esc_sql($s).'%")||a.mobile LIKE("%'.esc_sql($s).'%")||a.e164cell LIKE("%'.esc_sql($s).'%")||b.phone LIKE("%'.esc_sql($s).'%")||b.address LIKE("%'.esc_sql($s).'%") ) GROUP BY a.people_id';
  
    $results=$wpdb->get_results( $sql);
    

    if( $results)
    {

		$theader='<tr><th>'.esc_html( __( 'Delete','church-admin') ).'</th><th>'.esc_html( __( 'Display household','church-admin') ).'</th><th>'.esc_html( __( 'Last Name','church-admin') ).'</th><th>'.esc_html( __( 'First Name','church-admin') ).'</th><th>'.esc_html( __( 'People Type','church-admin') ).'</th><th>'.esc_html( __( 'Address','church-admin') ).'</th><th>'.esc_html( __( 'Last Update','church-admin') ).'</th><th>'.esc_html( __( 'Move','church-admin') ).'</th><th>'.esc_html( __( 'Household ID','church-admin') ).'</th><th>'.esc_html( __( 'User','church-admin') ).'</th><th>V-card</th></tr>';
	    echo '<h2>'.esc_html( __( 'Address List Results','church-admin') ).' for "'.esc_html( $search).'"</h2><table class="widefat striped"><thead>'.$theader.'</thead><tbody>';
		foreach( $results AS $row)
		{
            
	    
            if(!empty( $row->address) )  {
				$address='<a href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_household&amp;household_id='.(int)$row->household_id,'edit_household')).'">'.esc_html( $row->address).'</a>';
			}else{
				$address='<a href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_household&amp;household_id='.(int)$row->household_id,'edit_household')).'">'.esc_html(__('Add Address','church-admin')).'</a>';
			}
			$user=church_admin_user_check( $row,FALSE);
            $delete='<a onclick="return confirm(\''.esc_html( __( 'Are you sure?','church-admin') ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_people&amp;people_id='.(int)$row->people_id.'&amp;household_id='.(int)$row->household_id,'delete_people').'">'.esc_html( __( 'Delete person','church-admin') ).'</a>';
            $updated=esc_html( sprintf(__( 'Person updated %1$s, Household updated %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$row->last_updated),mysql2date(get_option('date_format'),$row->householdUpdated) ) );
            $move='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=move-person&amp;people_id='.$row->people_id,'move-person').'">'.esc_html( __( 'Move','church-admin') ).'</a>';
            $vcf='<a  rel="nofollow" style="text-decoration:none" title="'.esc_html( __( 'Download personal Vcard','church-admin') ).'" href="'.wp_nonce_url(home_url().'/?ca_download=vcf-person&amp;id='.(int)$row->people_id,(int)$row->people_id).'"><span class="ca-dashicons dashicons dashicons-index-card"></span> VCF</a>';
       
			echo '<tr><td>'.$delete.'</td><td><a  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=display-household&section=people&household_id='.$row->household_id,'display-household').'">'.esc_html( __( 'Display household','church-admin') ).'</a></td><td class="ca-names"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=display-household&amp;household_id='.(int)$row->household_id,'display-household').'">'.esc_html( $row->last_name).'</a></td><td class="ca-names"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.(int)$row->people_id,'edit_people').'">'.esc_html( $row->first_name).'</td><td>'.esc_html($people_types[$row->people_type_id]).'</td><td class="ca-addresses">'.$address.'</td><td>'.$updated.'</td><td>'.$move.'</td><td>'.(int)$row->household_id.'</td><td>'.$user.'</td><td>'.$vcf.'</td></tr>';


		}
		echo '</tbody><tfoot>'.$theader.'</tfoot></table>';
		echo'<script>
		jQuery(document).ready(function( $)  {
			$(".ca_connect_user").click(function() {
				var people_id=$(this).attr("data-peopleid");
				var data = {
				"action": "church_admin",
				"method": "connect_user",
				"people_id": people_id,
				"user_id": $(this).attr("data-userid"),
				"nonce": "'.wp_create_nonce("connect_user").'",
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
			$(".ca_create_user").click(function() {
				var people_id=$(this).attr("data-peopleid");
				var data = {
				"action": "church_admin",
				"method": "create_user",
				"people_id": $(this).attr("data-peopleid"),
				"nonce": "'.wp_create_nonce("create_user").'",
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
		});
		</script>';


    }
	else{
		echo'<p>"'.esc_html( $search).'" '.esc_html( __( 'not found in directories','church-admin') ).'.</p>';
		church_admin_search_form();
	}


	$people_id=church_admin_get_one_id( $search);
	$serial='s:'.strlen( $people_id).':"'.$people_id.'";';
	
	
	//search podcast

	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_title LIKE "%'.esc_sql($s).'%" OR file_description LIKE "%'.esc_sql($s).'%" OR speaker LIKE "%'.esc_sql( $serial).'%" OR speaker LIKE "%'.esc_sql($s).'%" ORDER BY pub_date DESC');
	if(!empty( $results) )
	{

		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/sermons/';
		$url=$upload_dir['baseurl'].'/sermons/';
		echo '<h2>'.esc_html( __( 'Sermon Podcast Results for ','church-admin') ).'"'.esc_html( $search).'"</h2>';
		$table='<table class="widefat striped"><thead><tr><th>'.esc_html( __( 'Edit','church-admin') ).'</th><th>'.esc_html( __( 'Delete','church-admin') ).'</th><th>'.esc_html( __( 'Publ. Date','church-admin') ).'</th><th>'.esc_html( __( 'Title','church-admin') ).'</th><th>'.esc_html( __( 'Speakers','church-admin') ).'</th><th>'.esc_html( __( 'Mp3 File','church-admin') ).'</th></th><th>'.esc_html( __( 'File Okay?','church-admin') ).'</th><th>'.esc_html( __( 'Length','church-admin') ).'</th><th>'.esc_html( __( 'Media','church-admin') ).'</th><th>'.esc_html( __( 'Transcript','church-admin') ).'</th><th>'.esc_html( __( 'Event','church-admin') ).'</th><th>'.esc_html( __( 'Shortcode','church-admin') ).'</th></tr></thead>'."\r\n".'<tfoot><tr><th>'.esc_html( __( 'Edit','church-admin') ).'</th><th>'.esc_html( __( 'Delete','church-admin') ).'</th><th>'.esc_html( __( 'Publ. Date','church-admin') ).'</th><th>'.esc_html( __( 'Title','church-admin') ).'</th><th>'.esc_html( __( 'Speakers','church-admin') ).'</th><th>'.esc_html( __( 'Mp3 File','church-admin') ).'</th></th><th>'.esc_html( __( 'File Okay?','church-admin') ).'</th><th>'.esc_html( __( 'Length','church-admin') ).'</th><th>'.esc_html( __( 'Media','church-admin') ).'</th><th>'.esc_html( __( 'Transcript','church-admin') ).'</th><th>'.esc_html( __( 'Event','church-admin') ).'</th><th>'.esc_html( __( 'Shortcode','church-admin') ).'</th></tr></tfoot>'."\r\n".'<tbody>';
        foreach( $results AS $row)
        {
            if(file_exists(plugin_dir_path( $path.$row->file_name) ))  {$okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="32" height="32" />';}else{$okay='<img src="'.plugins_url('images/red.png',dirname(__FILE__) ) .'" width="32" height="32" />';}
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=upload-mp3&amp;id='.$row->file_id,'upload-mp3').'">Edit</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __( 'Are you sure?','church-admin') ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-media-file&amp;id='.$row->file_id,'delete-media-file').'">'.esc_html( __( 'Delete','church-admin') ).'</a>';
            $series_name=$wpdb->get_var('SELECT series_name FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_id="'.(int)$row->series_id.'"');

            if(!empty( $row->file_name)&&file_exists( $path.$row->file_name) )  {
				$file='<a href="'.$url.esc_url( $row->file_name).'">'.esc_html( $row->file_name).'</a>'; 
				$okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" />';
			}
			elseif(!empty( $row->external_file) )  {
				$file='<a href="'.esc_url( $row->external_file).'">'.esc_html( $row->external_file).'</a>'; 
				$okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" />';
			}
			else{
				$file='&nbsp;'; 
				$okay='<img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" />';
			}

            $table.='<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html(date(get_option('date_format'),strtotime( $row->pub_date) )).'</td><td>'.esc_html( $row->file_title).'</td><td class="ca-names">'.esc_html(church_admin_get_people( $row->speaker) ).'</td><td>'.$file.'</td><td>'.$okay.'</td><td>'.esc_html( $row->length).'</td><td>'.esc_html($row->video_url).'</td>';
            if(file_exists( $path.$row->transcript) )  {$table.='<td><a href="'.esc_url( $url.$row->transcript).'">'.esc_html( $row->transcript).'</a></td>';}else{$table.='<td>&nbsp;</td>';}
            $table.='<td>'.esc_html( $series_name).'</td><td>[church_admin type="podcast" file_id="'.(int)$row->file_id.'"]</td></tr>';
        }

        $table.='</tbody></table>';
        echo $table;
	}
	//search calendar

}
 /**
 *
 * Replicate ministries in to roles.
 * The roles must already have been created in wordpress
 *
 * @author  Andy Moyle
 * @param    None
 * @return   N/A
 * @version  0.2
 *
 */

function church_admin_replicate_roles()
{
	if(!church_admin_level_check('Directory') )wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') ) );
	global $wpdb;
	//$wpdb->show_errors;

    if ( empty( $_POST['replicate-roles'] ) )
    {
        echo'The replicate roles function replicates Church Admin Ministries to WordPress roles.</br>To use this you first need to create the matching role in WordPress, there any many plugins that do this including User Role Editor and Advanced Access Manager (AAM).  Once you have created a matching word press role for the Ministries you want to replicate just use the replicate roles function to add people in your ministries to the equivalent WordPress role.<br>Why would you do this?  It allows you then to use other plugins to restrict access based on the roles, for example if you have a “service leader” role and you want a page only service leaders see when they logon you can so this by.<br>   Use church admin to create a service leader ministry and add you service leaders.<br>Manually create a WordPress role called “Service Leader”, this must match exactly the name of the ministry. Use the Replicate Roles function to populate your new Role with those people you assigned to the ministry.    Now use an access plugin to apply the controls you want. AAM does this very easily.<br>There are a couple of restrictions<br>This function old “adds” people, so if you remove anyone from a ministry you have to manually delete them from the role – or deleted everyone and then use the replicate roles function again.<br> As mentioned, you have to create the roles manually with another plugin.<br>Once pressed you see this kind of output<br><ul><li>Andy Moyle already has role PCC (pcc).</li><li>Unable to add Role (WebTeam) to user Andy Moyle. The role was not found in wordpress - please add this manually if required.</li><li>Andy Moyle already has role Church Wardens (church_wardens).</li><li>Adding role Tea Room (tea_room) to Andy Moyle</li></ul>';
        echo'<form action="" method="post">';
		wp_nonce_field('replicate-roles');
		echo'<p><input type="hidden" name="replicate-roles" value="TRUE" /><input type="submit" class="button-primary" value="'.esc_html( __( 'Replicate roles','church-admin') ).'"></p></form>';
    }
    else{
        //Get an array of all the defined roles.
        $wp_roles = new WP_Roles();
        $names =$wp_roles->get_names();
        echo '<h2>'.esc_html( __( 'Replicate roles','church-admin') ).'</h2>';
        echo'<p>'.esc_html( __( "This replicates people's ministries to WordPress user roles, if you have already created roles for those ministry names",'church-admin') ).'</p>';
        echo'<p>'.esc_html( __( 'Starting to replicate roles','church-admin') ).'</p>';

        //Find all users in church admin that have a wordpress user ID.
        $sql='SELECT first_name,last_name,people_id,user_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id>=1';

        if(defined('CA_DEBUG') )church_admin_debug('running sql '.$sql);

        $result=$wpdb->get_results( $sql);
        if(defined('CA_DEBUG') )church_admin_debug('Results '.print_r( $result,TRUE) );
        if(!empty( $result) )
        {
            //We found some users with wordpress user ID's - iterate through them.
            foreach( $result AS $users)
            {
                if(defined('CA_DEBUG') )church_admin_debug('Person Found with wordpress ID "'.(int)$users->user_id.'"');

                //Now find the ministry ID's this user has.
                $sql='SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="ministry" AND people_id="'.(int)$users->people_id.'"';
                if(defined('CA_DEBUG') )church_admin_debug('running sql '.$sql);
                $metaresult=$wpdb->get_results( $sql);
                if(!empty( $metaresult) )
                {

                    //User is in some ministries - iterate through them to get the names.
                    foreach( $metaresult AS $role)
                    {
                        if(defined('CA_DEBUG') )church_admin_debug('Role ID Found '.(int)$role->ID);
                        //For each ID we find get the actual name.
                        $sql='SELECT ministry FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID='.(int)$role->ID;
                        if(defined('CA_DEBUG') )church_admin_debug('running sql '.$sql);
                        $rolename=$wpdb->get_var( $sql);
                        if(defined('CA_DEBUG') )church_admin_debug('Role name is '.$rolename.' Adding role');

                        //Iterate through the available roles to get the internal role name
                        //as this is whats needed for the add role.
                        $internalrolename='';
                        $user=get_userdata( $users->user_id);
                        foreach( $names as $key=>$ID)
                        {
                            if(defined('CA_DEBUG') )church_admin_debug('Role name is '.$key.' ID is '.(int)$ID);

                            if (strtolower( $ID)==strtolower( $rolename) )
                            {
                                $internalrolename=$key;
                                //Check if the user already has the role, user->roles is an array of all the roles the user has
                                if (!in_array( $internalrolename, $user->roles )	)
                                {
                                    //User does not have the role, so add it.
                                    echo'<br> Adding role '.$ID.' ('.$internalrolename.') to '.$users->first_name.' '.$users->last_name;
                                    $user->add_role( $internalrolename);
                                }
                                else
                                    echo'<br>'.$users->first_name.' '.$users->last_name.' already has role '.$ID.' ('.$internalrolename.').';
                                break;
                            }
                        }
                        //We have iterated through all wordpress's known roles and have not found anything that matches.
                        if ( empty( $internalrolename) )
                        {
                            echo'<br>Unable to add Role <b>('.$rolename.')</b> to user '.$users->first_name.' '.$users->last_name.'. The role was not found in wordpress - please add this manually if required.' ;
                        }
                    }
                }
            }
        }
    }
}
 /**
 *
 * Import CSV
 *
 * @author  Andy Moyle
 * @param
 * @return   html
 * @version  0.1
 *
 */
function church_admin_import_csv()
{
	/*************************************************************************
	* 2023-07-24 SSRF vulnerability removed by not putting the file 
	* destination of uploaded CSV in the form. 
	* Now using fixed file location v3.8.0
	* Added checking mime type of uploaded file and rejecting if not CSV
	* Added data sanitization as each line is read from CSV file
	************************************************************************/
		if(!church_admin_level_check('Directory') )wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') ) );
		echo'<h2>'.esc_html( __( 'Import CSV','church-admin') ).'</h2>';
		echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/import-address-list-csv"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
   
		$upload_dir = wp_upload_dir();
		$temp_path=$upload_dir['basedir'].'/church-admin-cache/';
		$filename = 'import.csv';
		$filedest = $temp_path.'/import.csv';


		global $wpdb;
		//$wpdb->show_errors;
		$people_types=get_option('church_admin_people_type');
		$gender=get_option('church_admin_gender');
		$debug=TRUE;
		$church_admin_marital_status=get_option('church_admin_marital_status');
		if ( empty( $church_admin_marital_status) ){
				$church_admin_marital_status=array(
				0=>esc_html( __( 'N/A','church-admin') ),
				1=>esc_html( __( 'Single','church-admin') ),
				2=>esc_html( __( 'Co-habiting','church-admin') ),
				3=>esc_html( __( 'Married','church-admin') ),
				4=>esc_html( __( 'Divorced','church-admin') ),
				5=>esc_html( __( 'Widowed','church-admin') )
			);
		}
	if(!empty( $_POST['process'] ) )
	{
		echo'<p>'.esc_html( __( 'Processing','church-admin') ).'</p>';
		if(!empty( $_POST['overwrite'] ) )
		{
			$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_people');
			$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_household');
			$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_people_meta');
			$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_custom_fields_meta');
			$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_custom_fields');
			update_option('church_admin_gender',array(1=>esc_html( __( 'Male','church-admin') ),0=>esc_html( __( 'Female','church-admin') )));
			echo'<p>'.esc_html( __( 'Tables truncated','church-admin') ).'</p>';
		}

		foreach( $_POST AS $key=>$value)
		{
			if(substr( $key,0,6)=='column')
			{
				$column=substr( $key,6);
				switch( $value)
				{
					case 'title':$title = $column;break;
					case'first_name':$first_name=$column;break;
					case'middle_name':$middle_name=$column;break;
					case'nickname':$nickname=$column;break;
					case'prefix':$prefix=$column;break;
					case'last_name':$last_name=$column;break;
					case'sex':$sex=$column;break;
					case'marital_status':$marital_status=$column;break;
					case'date_of_birth':$date_of_birth=$column;break;
					case'email':$email=$column;break;
					case'mobile':$mobile=$column;break;
					case'phone':$phone=$column;break;
					case'address':$address=$column;break;
					case'street_address':$street_address=$column;break;
					case'city':$city=$column;break;
					case'state':$state=$column;break;
					case'zip_code':$zipcode=$column;break;
					
					case'member_type':$member_type=$column;break;
					case'people_type':$people_type=$column;break;
					case'people_order':$people_order=$column;break;
					
					case 'custom1':$custom1=$column;break;
					case 'custom2':$custom2=$column;break;
					case 'custom3':$custom3=$column;break;
					case 'custom4':$custom4=$column;break;
					case 'custom5':$custom5=$column;break;
					case 'custom6':$custom6=$column;break;
					case 'custom7':$custom7=$column;break;
					case 'custom8':$custom8=$column;break;
					case 'custom9':$custom9=$column;break;
					case 'custom10':$custom10=$column;break;
					case 'show_me':$show_me=$column;break;
                    case 'mail_send':$mail_send=$column;break;
                    case 'email_send':$email_send=$column;break;
					case 'news_send':$news_send=$column;break;
                    case 'sms_send':$sms_send=$column;break;
                    case 'photo_permission':$photo_permission=$column;break;  
					case 'head_of_household':$headofhousehold=$column;break;  
					case 'household_id':$householdid=$column;break;
					case 'gdpr_reason':$gdpr_reason=$column;break;
					case 'wedding_anniversary': $wedding_anniversary=$column;break;
					
				}

			}

		}
		//ini_set('auto_detect_line_endings',TRUE);
		if(!file_exists($filedest)){
			echo __('File failure, please try again','church-admin');
			exit();
		}
		
	
		if (( $handle = fopen( $filedest, "r") ) !== FALSE)
		{
			echo'<p>'.esc_html( __( 'Begin file Processing','church-admin') ).'</p>';
			$header=fgetcsv( $handle, 0, ",");
			//handle custom headers
			$customFields=array();
			if(!empty( $custom1) )  {$custom1Header=$header[$custom1]; $customFields[1]=array('name'=>$custom1Header,'type'=>"text");}
			if(!empty( $custom2) )  {$custom2Header=$header[$custom2]; $customFields[2]=array('name'=>$custom2Header,'type'=>"text");}
			if(!empty( $custom3) )  {$custom3Header=$header[$custom3]; $customFields[3]=array('name'=>$custom3Header,'type'=>"text");}
			if(!empty( $custom4) )  {$custom4Header=$header[$custom4]; $customFields[4]=array('name'=>$custom4Header,'type'=>"text");}
			if(!empty( $custom5) )  {$custom5Header=$header[$custom5]; $customFields[5]=array('name'=>$custom5Header,'type'=>"text");}
			if(!empty( $custom6) )  {$custom6Header=$header[$custom6]; $customFields[6]=array('name'=>$custom6Header,'type'=>"text");}
			if(!empty( $custom7) )  {$custom7Header=$header[$custom7]; $customFields[7]=array('name'=>$custom7Header,'type'=>"text");}
			if(!empty( $custom8) )  {$custom8Header=$header[$custom8]; $customFields[8]=array('name'=>$custom8Header,'type'=>"text");}
			if(!empty( $custom9) )  {$custom9Header=$header[$custom9]; $customFields[9]=array('name'=>$custom9Header,'type'=>"text");}
			if(!empty( $custom10) )  {$custom10Header=$header[$custom10]; $customFields[10]=array('name'=>$custom10Header,'type'=>"text");}
			if(!empty( $customFields) )
			{
				foreach( $customFields AS $key=>$field)
				{
					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields (name,section,type,ID)VALUES("'.esc_sql( $field['name'] ).'","people","text","'.(int)$key.'")');
				}
			}			
			echo'<p>'.esc_html( __( 'Got CSV header','church-admin') ).'</p>';
			$users=array();
			while (( $data = fgetcsv( $handle, 0, ",") ) !== FALSE)
			{
				$check=array_filter( $data);
				if(!empty( $check) )
				{
                    //ensure data is utf-8 and sanitized
                    foreach( $data AS $key=>$value)
                    {
                        $data[$key]=mb_convert_encoding(church_admin_sanitize( $value ) ,'UTF-8');
                    }
              
				if(isset($headofhousehold) && isset( $data[$headofhousehold] ) )
				{
					//variable set, if not empty it should be 1 else it should be 0
					$head_of_household = !empty($data[$headofhousehold])?1:0;
				}
				else{
					 $head_of_household=1;//reset to 1 each time. Set to 0 if address already stored, which implies head already stored.
				}
				//household
				$household_id=NULL;
				$add='';
				if(!empty( $address)&&!empty( $data[$address] ) )
				{
					$ad=array( $data[$address] );
					if(!empty( $city)&&!empty( $data[$city] ) )$ad[]=$data[$city];
					if(!empty( $state)&&!empty( $data[$state] ) )$ad[]=$data[$state];
					if(!empty( $zipcode)&&!empty( $data[$zipcode] ) )$ad[]=$data[$zipcode];
					if(!empty( $wedding_anniversary)&&!empty( $data[$wedding_anniversary] ) )$ad[]=$data[$wedding_anniversary];
					$add=implode(', ',$ad);

				}
                     
				if(!empty( $phone)&&!empty( $data[$phone] ) )  {$ph=$data[$phone];}else{$ph=NULL;}
				//if the address is empty then don;t try to match with existing household
				if(!empty( $householdid)&&!empty( $data[$householdid] ) )
				{
					$household_id=$data[$householdid];
				}
				if(!empty( $address)&&!empty( $data[$address] ) && empty( $household_id) )
				{
					$sql='SELECT household_id FROM '.$wpdb->prefix.'church_admin_household WHERE address="'.esc_sql( $add).'"';
					$household_id=$wpdb->get_var( $sql);
				}
				if ( empty( $household_id) )$household_id=$wpdb->get_var('SELECT MAX(household_id) FROM '.$wpdb->prefix.'church_admin_household') + 1;
				$householdData=array('household_id'=>$household_id,'address'=>$add,'phone'=>$ph);
				$format=array('%d','%s','%s','%s');
				$wpdb->replace($wpdb->prefix.'church_admin_household',$householdData,$format);
				//make sure titles are stored well for future use!
				if(!empty($title) && !empty($data[$title])){
					$titles = get_option('church_admin_titles');
					if(!is_array($titles)){$titles=array($data[$title]);}
					if(!in_array($data[$title],$titles)){$titles[]=$data[$title];}
					update_option('church_admin_titles',$titles);
				}
				//member type
				if(!empty( $member_type) )
				{
					$mt=$data[$member_type];
					$member_type_id=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_member_types WHERE member_type="'.esc_sql( $mt).'"');
					if ( empty( $member_type_id) )
					{
						$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_member_types (member_type)VALUES("'.esc_sql( $mt).'")');
						$member_type_id=$wpdb->insert_id;
					}
				}else
				{
					$member_type_id=1;
					$check=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_member_types WHERE member_type_id=1' );
					if(!$check)
					{
						$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_member_types (member_type)VALUES("'.esc_sql( __( 'Member','church-admin') ).'")');
						$member_type_id=$wpdb->insert_id;
					}
				}
				/*************************
				 * PEOPLE
				 *************************/
				
				/*************************
				 * Gender
				 *************************/
				if(isset( $sex)&&!empty( $data[$sex] ) )
				{
					$malefemale=array_search( $data[$sex],$gender);
					if(!isset( $malefemale) )
					{
						$gender[]=$data[$sex];
						update_option('church_admin_gender',$gender);
					}
					$malefemale=(int)array_search( $data[$sex],$gender);
				}else $malefemale=1;
				/*************************
				 * Date of Birth
				 *************************/
				if(isset( $date_of_birth) && !empty( $data[$date_of_birth] ) )
				{

					if(church_admin_checkdate( $data[$date_of_birth] ) )  {$dob=$data[$date_of_birth];}
					else{$dob=date('Y-m-d',strtotime( $data[$date_of_birth] ) );}

					if ( empty( $dob) ) $dob=NULL;
				}else{$dob=NULL;}

				

				if (empty( $marital_status) )  {$data['marital_status']=0;}
				elseif(!in_array( $data[$marital_status],$church_admin_marital_status) )  {$data['marital_status']=0;}else{$data['marital_status']=$data[$marital_status];}
				if ( empty( $data['marital_status'] ) )  {$data['marital_status']=0;}
				if(!isset( $title)||empty( $data[$title] ) )  {$data['title']=NULL;}else{$data['title']=$data[$title];}
				if(!isset( $first_name)||empty( $data[$first_name] ) )  {$data['first_name']=NULL;}else{$data['first_name']=$data[$first_name];}
				if(!isset( $middle_name)||empty( $data[$middle_name] ) )  {$data['middle_name']=NULL;}else{$data['middle_name']=$data[$middle_name];}
				if(!isset( $nickname)||empty( $data[$nickname] ) )  {$data['nickname']=NULL;}else{$data['nickname']=$data[$nickname];}
				if(!isset( $prefix)||empty( $data[$prefix] ) )  {$data['prefix']=NULL;}else{$data['prefix']=$data[$prefix];}
				if(!isset( $last_name)||empty( $data[$last_name] ) )  {$data['last_name']=NULL;}else{$data['last_name']=$data[$last_name];}
				
                if(!isset( $mobile)||empty( $data[$mobile] ) )
                {
                    $data['e164cell']=$data['mobile']=NULL;
                }else
                {
                    $data['mobile']=$data[$mobile];
                    $data['e164cell']=church_admin_e164( $data[$mobile] );
                }
				if(!isset( $email)||empty( $data[$email] ) )  {$data['email']=NULL;}else{$data['email']=$data[$email];}
				if(!isset( $custom1)||empty( $data[$custom1] ) )  {$data['custom1']=NULL;}else{$data['custom1']=$data[$custom1];}
				if(!isset( $custom2)||empty( $data[$custom2] ) )  {$data['custom2']=NULL;}else{$data['custom2']=$data[$custom2];}
				if(!isset( $custom3)||empty( $data[$custom3] ) )  {$data['custom3']=NULL;}else{$data['custom3']=$data[$custom3];}
				if(!isset( $custom4)||empty( $data[$custom4] ) )  {$data['custom4']=NULL;}else{$data['custom4']=$data[$custom4];}
				if(!isset( $custom5)||empty( $data[$custom5] ) )  {$data['custom5']=NULL;}else{$data['custom5']=$data[$custom5];}
				if(!isset( $custom6)||empty( $data[$custom6] ) )  {$data['custom6']=NULL;}else{$data['custom6']=$data[$custom6];}
				if(!isset( $custom7)||empty( $data[$custom7] ) )  {$data['custom7']=NULL;}else{$data['custom7']=$data[$custom7];}
				if(!isset( $custom8)||empty( $data[$custom8] ) )  {$data['custom8']=NULL;}else{$data['custom8']=$data[$custom8];}
				if(!isset( $custom9)||empty( $data[$custom9] ) )  {$data['custom9']=NULL;}else{$data['custom9']=$data[$custom9];}
				if(!isset( $custom10)||empty( $data[$custom10] ) )  {$data['custom10']=NULL;}else{$data['custom10']=$data[$custom10];}
                
                if(!isset( $mail_send)||empty( $data[$mail_send] ) )  {$data['mail_send']=0;}else{$data['mail_send']=1;} 
                if(!isset( $email_send)||empty( $data[$email_send] ) )  {$data['email_send']=0;}else{$data['email_send']=1;} 
				if(!isset( $news_send)||empty( $data[$news_send] ) )  {$data['news_send']=0;}else{$data['news_send']=1;} 
                if(!isset( $sms_send)||empty( $data[$sms_send] ) )  {$data['sms_send']=0;}else{$data['sms_send']=1;}
                if(!isset( $photo_permission)||empty( $data[$photo_permission] ) )  {$data['photo_permission']=0;}else{$data['photo_permission']=1;}     
				if(!isset( $show_me)||empty( $data[$show_me] ) )  {$data['show_me']=0;}else{$data['show_me']=1;} 
				if(!isset( $gdpr_reason)||empty( $data[$gdpr_reason] ) )  {$data['gdpr_reason']="";}else{$data['gdpr_reason']=$data[$gdpr_reason];}
				if(!isset( $people_order)||empty( $data['people_order'] ) )$data['people_order']=0;
				$data['people_type_id']=1;
                if(!isset( $people_type)||empty( $data[$people_type] ) )  {$data['people_type_id']=1;}
				else
				{
					foreach( $people_types AS $id=>$type) if(strtolower( $type)==strtolower( $data[$people_type] ) )  {$data['people_type_id']=(int)$id;}
				}
				if(isset( $gdpr_reason) )  {$gdpr=$data[$gdpr_reason];}else{$gdpr='';}
				$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.esc_sql(sanitize_text_field( stripslashes($data['first_name'] ) ) ).'" AND last_name="'.esc_sql(sanitize_text_field( stripslashes($data['last_name'] )) ).'" AND household_id="'.(int)$household_id.'"');
				if ( empty( $people_id) )
				{
					$sql='INSERT INTO '.$wpdb->prefix.'church_admin_people (title,first_name,middle_name,nickname,prefix,last_name,email,mobile,sex,date_of_birth,member_type_id,household_id,people_type_id,head_of_household,marital_status,people_order,mail_send,email_send,sms_send,photo_permission,e164cell,gdpr_reason,show_me,first_registered)VALUES("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['first_name'] ).'","'.esc_sql( $data['middle_name'] ).'","'.esc_sql( $data['nickname'] ).'","'.esc_sql( $data['prefix'] ).'","'.esc_sql( $data['last_name'] ).'","'.esc_sql( $data['email'] ).'","'.esc_sql( $data['mobile'] ).'","'.$malefemale.'","'.$dob.'","'.esc_sql( $member_type_id).'","'.(int)$household_id.'","'.intval( $data['people_type_id'] ).'","'.$head_of_household.'","'.esc_sql( $data['marital_status'] ).'","'.intval( $data['people_order'] ).'","'.(int)$data['mail_send'].'","'.(int)$data['email_send'].'","'.(int)$data['sms_send'].'","'.(int)$data['photo_permission'].'","'.esc_sql($data['e164cell']).'","'.esc_sql($data['gdpr_reason']).'","'.(int)$data['show_me'].'","'.esc_sql(wp_date('Y-m-d')).'")';

					if(defined('CA_DEBUG') )church_admin_debug( $sql);
					$wpdb->query( $sql);
					
					$people_id=$wpdb->insert_id;
					$what=esc_html( __( 'Added','church-admin') );
				}
				else {
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET title="'.esc_sql($data['title']).'", first_name="'.esc_sql( $data['first_name'] ).'",middle_name="'.esc_sql( $data['middle_name'] ).'",nickname="'.esc_sql( $data['nickname'] ).'",prefix="'.esc_sql( $data['prefix'] ).'",last_name="'.esc_sql( $data['last_name'] ).'",email="'.esc_sql( $data['email'] ).'",mobile="'.esc_sql( $data['mobile'] ).'",sex="'.$malefemale.'",date_of_birth="'.$dob.'",member_type_id="'.esc_sql( $member_type_id).'",household_id="'.(int)$household_id.'",people_type_id="'.intval( $data['people_type_id'] ).'",head_of_household="'.$head_of_household.'",marital_status="'.esc_sql( $data['marital_status'] ).'",people_order="'.intval( $data['people_order'] ).'",mail_send="'.$data['mail_send'].'",email_send="'.(int)$data['email_send'].'",news_send="'.(int)$data['news_send'].'",sms_send="'.(int)$data['sms_send'].'",photo_permission="'.(int)$data['photo_permission'].'", e164cell="'.esc_sql($data['e164cell']).'",gdpr_reason="'.esc_sql($data['gdpr_reason']).'" WHERE people_id="'.(int)$people_id.'"');
					$what=esc_html( __( 'Updated','church-admin') );
                    
				}
				$user_id=email_exists($data['email']);
				//connect user account first time only!
				if(!empty($user_id) && !in_array($data['email'],$users)){
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$user_id.'" WHERE people_id="'.(int)$people_id.'"');
					$users[]=$data['email'];
				}
				//news send
				church_admin_update_people_meta(NULL,$people_id,'posts',date('Y-m-d'));
                
				
				if(isset( $data['custom1'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","1","'.esc_sql( $data['custom1'] ).'","people")');
				}
				if(isset( $data['custom2'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","2","'.esc_sql( $data['custom2'] ).'","people")');
				}
				if(isset( $data['custom3'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","3","'.esc_sql( $data['custom3'] ).'","people")');
				}
				if(isset( $data['custom4'] ) )
				{


					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","4","'.esc_sql( $data['custom4'] ).'","people")');
				}
				if(isset( $data['custom5'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","5","'.esc_sql( $data['custom5'] ).'","people")');
				}
				if(isset( $data['custom6'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","6","'.esc_sql( $data['custom6'] ).'","people")');
				}
				if(isset( $data['custom7'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","5","'.esc_sql( $data['custom7'] ).'","people")');
				}
				if(isset( $data['custom8'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","8","'.esc_sql( $data['custom8'] ).'","people")');
				}
				if(isset( $data['custom9'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data) VALUES("'.(int)$people_id.'","9","'.esc_sql( $data['custom9'] ).'","people")');
				}
				if(isset( $data['custom10'] ) )
				{

					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","10","'.esc_sql( $data['custom10'] ).'","people")');
				}
				//look for custom_ in the array, these are user defined custom fields.
				if(defined('CA_DEBUG') )church_admin_debug('Data array:'.print_r( $data,TRUE) );
				//echo '<br> about to loop '.count( $_POST);

				foreach( $_POST as $field => $value)
				{
					//echo '<br> field= '.$field;
					$key=sanitize_text_field(stripslashes($value));
					$pos=strpos( $key,'custom_');
					//echo '<br> pos= '.$pos;
					//echo '<br> key= '.$key;
					if ( $pos!==false)
					{
					    //echo '<br> Found custom_ at '.$pos;

						$column=substr( $field,6);
						//found a custom defined field, extract the custom id
						$cust_id=substr( $key,strlen('custom_')+$pos);
						//echo '<br> cust id is'.$cust_id;
						//echo '<br> Data for row '.$column. 'is '.$data[$column];
						$sql='INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$people_id.'","'.intval( $cust_id) .'","'.esc_sql( $data[$column] ).'","people")';
  					    $wpdb->query( $sql);
					}
				}

				echo '<p>'.$what.' '.$data[$first_name].' '.$data[$last_name].'</p>';

			}//non empty data
			

			}
			//fix head of households
			$householdsNeedingFixing=array();
				$households=$wpdb->get_results('SELECT household_id FROM '.$wpdb->prefix.'church_admin_household');
				if(!empty( $households) )
				{
					/**************************************************
					 *  Check and Fix head of household missing issue
					 **************************************************/
					echo'<h2>'.esc_html( __( 'Checking for issues with head of household not set ','church-admin') ).'</h2>'."\r\n";
					foreach( $households AS $household)
					{
						$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$household->household_id.'"');
						
						if ( empty( $people_id) )$householdsNeedingFixing[]=$household->household_id;
					}
					if ( empty( $householdsNeedingFixing) )
					{
						echo'<p>'.esc_html( __( 'All households have a head of household set','church-admin') ).'<span class="ca-dashicons dashicons dashicons-saved" style="color:green"></span></p>'."\r\n";
					}
					else
					{
						
						$countHouseholds=count( $householdsNeedingFixing);
						echo'<p>'.esc_html(sprintf(__( '%1$s households do not have a head of household set. Fixing now', 'church-admin' ) ,$countHouseholds)) .'</p>'."\r\n";
						foreach( $householdsNeedingFixing AS $key=>$household_id)
						{
							$peopleInHousehold=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY people_order ASC, people_type_id ASC, sex DESC');
							
						if(defined('CA_DEBUG') )church_admin_debug(print_r( $peopleInHousehold,TRUE) );
							if(!empty( $peopleInHousehold) )
							{
								if(defined('CA_DEBUG') )church_admin_debug( $peopleInHousehold);
								if( $wpdb->num_rows==1)
								{
									//one person household
									$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE household_id="'.(int)$household_id.'" ');
								
									echo'<p>'.esc_html( __( 'One person household head of household fixed','church-admin') ).'<span class="ca-dashicons dashicons dashicons-saved"  style="color:green"></span></p>'."\r\n";
								}
								else
								{
									$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE people_id="'.(int)$peopleInHousehold[0]->people_id.'" ');
									echo'<p>'.esc_html( __( 'Multiple person household head of household fixed','church-admin') ).'<span class="ca-dashicons dashicons dashicons-saved" style="color:green"></span></p>'."\r\n";
								}
							}
						}
					}
        		}


			echo'<p>'.esc_html( __( 'Finished file Processing','church-admin') ).'</p>';
            update_option('addressUpdated',time() );
		}
		fclose( $handle);
		unlink($filedest);//delete file

	}
	elseif(!empty( $_POST['save_csv'] ) )
	{
		if(!empty( $_FILES) && $_FILES['file']['error'] == 0)
		{
			$custom_fields=church_admin_get_custom_fields();
			$filename = $_FILES['file']['name'];
			$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
    		$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
			if(!in_array($mime,$csvMimes)){
				echo __('Not detected to be a CSV file','church-admin');
				exit();
			}
			if(move_uploaded_file( $_FILES['file']['tmp_name'], $filedest) )echo '<p>'.esc_html( __( 'File Uploaded and saved','church-admin') ).'</p>';

			//ini_set('auto_detect_line_endings',TRUE);
			$file_handle = fopen( $filedest, "r");
			
			$header=fgetcsv( $file_handle, 0, ",");

			$example1=fgetcsv( $file_handle, 0, ",");
			$example2=fgetcsv( $file_handle, 0, ",");
			$example3=fgetcsv( $file_handle, 0, ",");


			echo'<form  action="" method="post"><table class="widefat striped bordered">';
			echo'<input type="hidden" name="process" value="yes" />';
			if(!empty( $_POST['overwrite'] ) ){
				echo'<input type="hidden" name="overwrite" value="yes" />';
			}
			echo'<thead><tr><th>'.esc_html( __( 'Your Header','church-admin') ).'</th><th>'.esc_html( __( 'Maps to','church-admin') ).'</th><th>'.esc_html( __( 'Example 1','church-admin') ).'</th><th>'.esc_html( __( 'Example 2','church-admin') ).'</th><th>'.esc_html( __( 'Example 3','church-admin') ).'</th></tr></thead><tbody>';
			foreach( $header AS $key=>$value)
			{
				echo'<tr><th scope="row">'.esc_html( $value).'</th><td>';
				echo'<select name="column'.$key.'">';
				echo'<option value="unused">'.esc_html( __( 'Unused','church-admin') ).'</option>';
				echo'<option value="title">'.esc_html( __( 'Title','church-admin') ).'</option>';
				echo'<option value="first_name">'.esc_html( __( 'First Name','church-admin') ).'</option>';
				echo'<option value="middle_name">'.esc_html( __( 'Middle Name','church-admin') ).'</option>';
				echo'<option value="nickname">'.esc_html( __( 'Nickname','church-admin') ).'</option>';
				echo'<option value="prefix">'.esc_html( __( 'Prefix','church-admin') ).'</option>';
				echo'<option value="last_name">'.esc_html( __( 'Last Name','church-admin') ).'</option>';
				echo'<option value="sex">'.esc_html( __( 'Gender','church-admin') ).'</option>';
				echo'<option value="marital_status">'.esc_html( __( 'Marital Status','church-admin') ).'</option>';
				echo'<option value="date_of_birth">'.esc_html( __( 'Date of Birth','church-admin') ).'</option>';
				echo'<option value="wedding_anniversary">'.esc_html( __('Wedding Anniversary','church-admin') ).'</option>';
				echo'<option value="email">'.esc_html( __( 'Email Address','church-admin') ).'</option>';
				echo'<option value="mobile">'.esc_html( __( 'Mobile','church-admin') ).'</option>';
				echo'<option value="phone">'.esc_html( __( 'Home phone','church-admin') ).'</option>';
				echo'<option value="address">'.esc_html( __( 'Address','church-admin') ).'</option>';
				echo'<option value="city">'.esc_html( __( 'City','church-admin') ).'</option>';
				echo'<option value="state">'.esc_html( __( 'State','church-admin') ).'</option>';
				echo'<option value="zip_code">'.esc_html( __( 'Zip Code','church-admin') ).'</option>';
				
				echo'<option value="member_type">'.esc_html( __( 'Member Type','church-admin') ).'</option>';
				
				echo'<option value="people_type">'.esc_html( __( 'People Type','church-admin') ).'</option>';
				echo'<option value="show_me">'.esc_html( __( 'Show me (1 or 0)','church-admin') ).'</option>';
				echo'<option value="custom1">'.esc_html( __( 'Custom field 1','church-admin') ).'</option>';
				echo'<option value="custom2">'.esc_html( __( 'Custom field 2','church-admin') ).'</option>';
				echo'<option value="custom3">'.esc_html( __( 'Custom field 3','church-admin') ).'</option>';
				echo'<option value="custom4">'.esc_html( __( 'Custom field 4','church-admin') ).'</option>';
				echo'<option value="custom5">'.esc_html( __( 'Custom field 5','church-admin') ).'</option>';
				echo'<option value="custom6">'.esc_html( __( 'Custom field 6','church-admin') ).'</option>';
				echo'<option value="custom7">'.esc_html( __( 'Custom field 7','church-admin') ).'</option>';
				echo'<option value="custom8">'.esc_html( __( 'Custom field 8','church-admin') ).'</option>';
				echo'<option value="custom9">'.esc_html( __( 'Custom field 9','church-admin') ).'</option>';
				echo'<option value="custom10">'.esc_html( __( 'Custom field 10','church-admin') ).'</option>';
                echo'<option value="mail_send">'.esc_html( __( 'Mail send (1 or 0)','church-admin') ).'</option>';
                echo'<option value="email_send">'.esc_html( __( 'Email send (1 or 0)','church-admin') ).'</option>';
				echo'<option value="news_send">'.esc_html( __( 'Blog post emails send (1 or 0)','church-admin') ).'</option>';
                echo'<option value="sms_send">'.esc_html( __( 'SMS send (1 or 0)','church-admin') ).'</option>';
                echo'<option value="photo_permission">'.esc_html( __( 'Photo permission (1 or 0)','church-admin') ).'</option>';
				echo'<option value="head_of_household">'.esc_html( __( 'Head of Household (1 or 0)','church-admin') ).'</option>';
				echo'<option value="household_id">'.esc_html( __( 'Household ID (integer)','church-admin') ).'</option>';
				echo'<option value="gdpr_reason">'.esc_html( __( 'GDPR reason','church-admin') ).'</option>';
				foreach( $custom_fields AS $ID=>$field)
				{
					if( $field['section']=='people') echo'<option value=custom_'.$ID.'>'.esc_html( __(  $field['name'].' (custom field)','church-admin') ).'</option>';

				}
				echo'</select>';
				echo'</td>';
				//output examples
				echo'<td>';
				if(!empty( $example1[$key] ) ) 
				{
					echo esc_html( $example1[$key] );
				}
				else {echo '&nbsp;';}
				echo'</td>';
				echo'<td>';
				if(!empty( $example2[$key] ) ) 
				{
					echo esc_html( $example2[$key] );
				}
				else {echo '&nbsp;';}
				echo'</td>';
				echo'<td>';
				if(!empty( $example3[$key] ) ) 
				{
					echo esc_html( $example3[$key] );
				}
				else {echo '&nbsp;';}
				echo'</td>';
				echo'</tr>';
			}
			wp_nonce_field('csv_upload','nonce');
			echo'<tr><td colspan="2"><input type="submit" class="button" value="'.esc_html( __( 'Save','church-admin') ).'" /></td></tr></tbody></table></form>';
		}
	}
	else
	{
		

		echo'<form action="" method="POST" enctype="multipart/form-data">';
		echo'<tr><th scope="row">'.esc_html( __( 'CSV File with 1st row as headers','church-admin') ).'</th><td><input type="file" name="file" /><input type="hidden" name="save_csv" value="yes" /></p>';
		echo'<tr><th scope="row">'.esc_html( __( 'Delete current address details?','church-admin') ).'</th><td><input type="checkbox" name="overwrite" value="yes" /></p>';
		echo'<tr><th colspan=2>'.esc_html( __('Any entries whose email already has a user account will be connected to that user account (first occurrence only!)','church-admin' ) ).'</th></tr>';
		echo'<p><input  class="button-primary" type="submit" Value="'.esc_html( __( 'Upload','church-admin') ).'" /></p></form>';

	}
}
/**
 * add new household.
 *
 * @param
 * @param html display new household
 *
 * @author andy_moyle
 *
 */
function church_admin_new_household()
{
	church_admin_debug('******* new household **********');
	//2016-04-14 Allow duplicate entries
	//v1.05 add middle name

	global $wpdb,$people_type;
	$church_admin_marital_status=get_option('church_admin_marital_status');
	if ( empty( $church_admin_marital_status) )
	{
		$church_admin_marital_status=array(0=>esc_html( __( 'N/A','church-admin')),1=>esc_html( __( 'Single','church-admin') ),
		2=>esc_html( __( 'Co-habiting','church-admin') ),
		3=>esc_html( __( 'Married','church-admin') ),
		4=>esc_html( __( 'Divorced','church-admin') ),
		5=>esc_html( __( 'Widowed','church-admin') )
	);
	update_option('church_admin_marital_status',$church_admin_marital_status);
	}
	$member_type=church_admin_member_types_array();
	$people_type=get_option('church_admin_people_type');
	if(!empty( $_POST['new_household'] )  )
	{//process
		church_admin_debug('Processing');
			church_admin_debug("POST \r\n".print_r( $_POST,TRUE) );
			$return=church_admin_save_household(1,NULL,NULL,1);//$return is array('household_id','output')
        	if(!empty( $return['output'] ) )
        	{
        		echo $return['output'];
        		echo '<div class="notice notice-success"><p>'.esc_html( __( 'Household Added','church-admin') ).'</p></div>';
        	}
			church_admin_head_of_household_tidy( $return['household_id'] );

		/*****************
		 * Admin email 
		 ****************/
		$adminmessage=get_option('church_admin_new_entry_admin_email');
		$admin_message = str_replace('[HOUSEHOLD_ID]','[HOUSEHOLD_ID]&token=[NONCE]',$adminmessage);
        $adminmessage=str_replace('[HOUSEHOLD_ID]',(int)$return['household_id'],$adminmessage);
		$token = md5(NONCE_KEY.$return['household_id']);
        $adminmessage=str_replace('[NONCE]',$token,$adminmessage);
        $adminmessage.='<p>&nbsp;</p>';

        $adminmessage.= church_admin_household_details_table($return['household_id'] );
        church_admin_debug($adminmessage);
		church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(sprintf(__('New household registration on %1$s','church-admin' ) ,site_url()) ),wp_kses_post($adminmessage),null,null,null,null,null,FALSE);
           

		echo'<div class="notice notice-success">'.esc_html(__('Household saved','church-admin')).'</div>';
		if(!empty( $return['household_id'] ) )church_admin_new_household_display( $return['household_id'] );
				
    }//end process
	else
	{
		echo '<div class="church_admin">';
		  echo '<h2>'.esc_html( __( 'Add new household','church-admin') ).'</h2>';
		  echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/addingediting-a-household/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
		  echo'<form action="" method="post"><input type="hidden" name="save" value="yes" />';
       	    echo church_admin_edit_people_form(1,NULL,NULL,1);
            echo '<input type="hidden" id="fields" name="fields"  value=1/>';
            echo '<p id="jquerybuttons"><input class="button-primary" type="button" id="btnAdd" value="'.esc_html( __( 'Add another person','church-admin') ).'" /> <input type="button"  disabled=disabled class="button-secondary"  id="btnDel" value="'.esc_html( __( 'Remove person','church-admin') ).'" /></p>';

             echo'<script >
         		jQuery("body").on("focus",".date_of_birth", function()  {
    			jQuery(this).datepicker({dateFormat : "yy-mm-dd",altField:"#"+this(id), changeYear: true ,yearRange: "1910:'.date('Y').'"});
			});</script>';


            echo '<div class="church-admin-form-group"><label>'.esc_html( __( 'Phone','church-admin') ).'</label><input name="phone" type="text" /></div>';
            echo church_admin_address_form(NULL,NULL);

		      echo'<div class="checkbox"><label>'.esc_html( __( 'Private (not shown publicly)','church-admin') ).'<input type="checkbox" name="private" value="1" /></label></div>';
                wp_nonce_field('new-household','nonce');
            echo'<input type="hidden" name="new_entry" value="1" />';
            echo  '<p><input type="hidden" name="new_household" value="TRUE" /><input  class="button-primary" type="submit" value="'.esc_html( __( 'Save','church-admin') ).'" /></form>';
        echo'</div><!-- .church_admin-->';
    }//form


}


function church_admin_save_household( $member_type_id,$exclude,$household_id,$onboarding)
{
	global $wpdb;
	church_admin_debug('*** save household ***');
	church_admin_debug('Function args...');
	church_admin_debug(print_r(func_get_args(),TRUE));
    if ( empty( $member_type_id) )$member_type_id=1;
    if(is_user_logged_in() )$user=wp_get_current_user();
	$out='';

	delete_option('church-admin-directory-output');//get rid of cached directory, so it is updated
	$debug=FALSE;

			$form=$sql=array();
			foreach ( $_POST AS $key=>$value){$form[$key]=church_admin_sanitize( $value);}

			if(empty($form['wedding_anniversary'])||(!empty($form['wedding_anniversary']) && !church_admin_checkdate($form['wedding_anniversary']))){
				$form['wedding_anniversary']=null;
			}
			if(defined('CA_DEBUG') )church_admin_debug("*************".date('Y-m-d h:i:s')."\r\n"."Save Household\r\n".print_r( $form,TRUE) );
			if ( empty( $household_id) && empty( $form['address'] ) )$household_id=NULL;
		
			if ( empty( $form['phone'] ) )$form['phone']=NULL;
			if ( empty( $form['lat'] ) )$form['lat']=0;
			if ( empty( $form['lng'] ) )$form['lng']=0;
			if ( empty( $household_id) ){
				$household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_household WHERE address="'.esc_sql( $form['address'] ).'" AND lat="'.esc_sql( $form['lat'] ).'" AND lng="'.esc_sql( $form['lng'] ).'" AND phone="'.esc_sql( $form['phone'] ).'"');
			}

			$last_updated = wp_date('Y-m-d H:i:s');
			$updated_by = !empty($user->ID) ? (int)$user->ID: 1;
			if ( empty( $household_id)||!empty( $_POST['new_entry'] ) )
			{//insert
				$sql='INSERT INTO '.$wpdb->prefix.'church_admin_household (wedding_anniversary,address,lat,lng,phone,attachment_id,last_updated,updated_by,first_registered) VALUES("'.esc_sql($form['wedding_anniversary']).'","'.esc_sql( $form['address'] ).'", "'.esc_sql( $form['lat'] ).'","'.esc_sql( $form['lng'] ).'","'.esc_sql( $form['phone'] ).'","'.intval( $form['household_attachment_id'] ).'","'.esc_sql($last_updated).'","'.(int)$updated_by.'","'.esc_sql(wp_date('Y-m-d')).'" )';
				if(defined('CA_DEBUG') )church_admin_debug("Inserted Household : $sql\r\n");

	    		$success=$wpdb->query( $sql);
	    		$household_id=$wpdb->insert_id;
	    		if(defined('CA_DEBUG') )church_admin_debug("Inserted Household_id : $household_id \r\n");
			}//end insert
			else
			{//update
				$sql='UPDATE '.$wpdb->prefix.'church_admin_household SET wedding_anniversary = "'.esc_sql($form['wedding_anniversary']).'", address="'.esc_sql( $form['address'] ).'" , lat="'.esc_sql( $form['lat'] ).'" , lng="'.esc_sql( $form['lng'] ).'" , phone="'.esc_sql( $form['phone'] ).'", attachment_id="'.intval( $form['household_attachment_id'] ).'",updated_by = "'.(int)$updated_by.'" ,last_updated="'.esc_sql($last_updated).'", WHERE household_id="'.(int)$household_id.'"';
				//if(defined('CA_DEBUG') )church_admin_debug("Updated Household : $sql\r\n");
	   			$success=$wpdb->query( $sql);


			}//update
            //add updated by
            if(!empty( $user->ID) )  {$user_id=$user->ID;}else{$user_id=0;}
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET updated_by="'.intval( $user_id).'" WHERE household_id="'.(int)$household_id.'"');
			$sql=array();
			//if(defined('CA_DEBUG') )church_admin_debug("household_id is :".$household_id);
			if(!empty( $_POST['fields'] ) )  {
				$fields=(int)$_POST['fields'] ;
			}else{
				$fields=1;
			}
			for ( $x=1; $x<=$fields; $x++)
      		{
				//Mick - Added to help debug.
				//if(defined('CA_DEBUG') )church_admin_debug("saving person :".$x);
				church_admin_save_person( $x,NULL,$household_id,$exclude,$onboarding);

    		}//add or update people

		/*****************************************************
        * Save Household Custom Fields
        *****************************************************/
		church_admin_update_meta_fields('household',NULL,$household_id,FALSE,NULL);


		$head_people_id=church_admin_head_of_household_tidy( $household_id);

        //if(defined('CA_DEBUG') )church_admin_debug("Output :\r\n $out\r\n");
	//reset app address list cache
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
    return array('household_id'=>$household_id,
	'head_people_id'=>$head_people_id,
	'output'=> $out,
	'last_name'=>esc_html(sanitize_text_field(stripslashes($_POST['last_name1'] ) )),
	'email'=>esc_html(sanitize_text_field(stripslashes($_POST['email1'] ))));
}


/**
 * Edit people form
 *
 * @param $x,$data
 *
 *
 * @author andy_moyle
 *
 */
function church_admin_edit_people_form( $x=1,$data=null,$exclude=array(),$onboarding=0 )
{
	//initialise variables and arrays
	global $wpdb,$people_type,$current_user;
	church_admin_debug('**** church_admin_edit_people_form ****');
	if ( empty( $exclude) ){
		$exclude=array();
	}
	if ( empty( $x) ){
		$x=1;
	}
	

	$required=array();

	$church_admin_marital_status= get_option('church_admin_marital_status');

	$member_type=church_admin_member_types_array();
	
	$people_type=get_option('church_admin_people_type');
	$name= !empty($data) ? church_admin_formatted_name( $data): '';
	
	
	//start $out
	//Mick  - line move to fron_end_register (setting field value to 1.
	//start cloned area
	$out='<div class="edit-people-form"><div class="clonedInput" id="input'.$x.'">';
	

    $out.='<div class="ca-people-form"><h2 class="hndle" data-ID="'.$x.'">'.esc_html( __( 'Person','church-admin') ).' #<span class="person">'.$x.'</span> ';
	if(!empty( $data->first_name)&&!empty( $data->last_name) ){
		$out.= $name;
	}
	if(!is_admin()){$out.=' ('.esc_html( __( 'Click to toggle','church-admin') ).')';}
	$out.='</h2>';
    $out.='<div class="inside" id="person'.$x.'" ';
	if( $x>1){
		$out.=' style="display:none" ';
	}
	$out.='>';
	if(is_admin() && !empty($data->head_of_household)){
		$out.= '<p>'.esc_html( __('Set as head of household','church-admin') ).'</p>';
	}
    $out.='<p>'.esc_html( __( '* required','church-admin') ).'</p>';
    $out.='<input type=hidden value="0" name="people_id'.(int)$x.'" id="people_id'.(int)$x.'" />';
     /************************************************
	 * Output Image
	 ***********************************************/
	if(!in_array('image',$exclude) )
	{
		if(!empty( $data->people_id) )  {$people_id=(int)$data->people_id;}else{$people_id=0;}
		$out.='<div class="church-admin-people-image ca-upload-area" data-which="people" data-id="'.$people_id.'" data-nonce="'.wp_create_nonce("people-image-upload").'" id="uploadfile">';
		if(!empty( $data->attachment_id) )
		{
			$person_image_attributes=wp_get_attachment_image_src( $data->attachment_id,'medium','' );
			if ( $person_image_attributes )
			{
				$out.='<img id="people-image'.(int)$data->people_id.'" src="'.$person_image_attributes[0].'" width="'.$person_image_attributes[1].'" height="'.$person_image_attributes[2].'" class="rounded" alt="'.esc_html( $name).'" />';
			}else
			{
				//image not available although attachment id is saved.
				if(isset( $data->sex) &&$data->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
				$out.='<img id="people-image'.(int)$data->people_id.'"  src="'.plugins_url('/', dirname(__FILE__) ) . 'images/'.$image.'" width="300" height="200" class="rounded current-image" alt="'.esc_html( $name).'" />';
			}
			$out.='<input id="attachment_id'.(int)$data->people_id.'" type="hidden" name="attachment_id'.(int)$x.'" value="'.(int)$data->attachment_id.'" />';
		}
		else
		{
			if(isset( $data->sex) &&$data->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
			
			$out.='<img id="people-image'.$people_id.'"  src="'.plugins_url('/', dirname(__FILE__) ) . 'images/'.$image.'" width="300" height="200" class="rounded current-image" alt="'.esc_html( $name).'" />';
			$out.='<input id="attachment_id'.(int)$people_id.'"  type="hidden" name="attachment_id'.(int)$x.'" />';
		}
		
		$out.= '<br><span id="upload-message">'.esc_html(sprintf( __( 'Drag and drop new image for %1$s','church-admin' ) ,$name) ).'</span>';
		$out.='</div>';
		$out.='<p><span id="people-image" data-people-id="'.(int)$people_id.'" class=" button-secondary upload-button button" >'.esc_html( __( 'WordPress Image Uploader','church-admin') ).'</span></p>';
		if(!empty( $data->attachment_id) )  {$attachment_id=(int)$data->attachment_id;}else{$attachment_id=NULL;}
		$out.='<span id="'.$x.'" class="remove-image button-secondary" data-attachment_id="'.(int)$attachment_id.'" data-type="people" data-id="'.(int)$people_id.'">'.esc_html( __( 'Remove image','church-admin') ).'</span>';
	}	
	$use_titles = get_option('church_admin_use_titles');
	if(!empty($use_titles)){
		//church_admin_debug('Use titles '.$use_titles);
		$titles = get_option('church_admin_titles');
		//church_admin_debug('Titles '.print_r($titles,true));
		if(!empty($titles)){
			$out.='<div class="church-admin-form-group"><label for="title">'.esc_html( __('Title','church-admin' ) ).' </label><select id="title" data-name="title" name="title'.(int)$x.'" class="church-admin-form-control"><option value="">'.esc_html(__('Choose title','church-admin')).'</option>';
			foreach($titles AS $key=>$title){
				$out.='<option value="'.esc_attr($title).'" ';
				if(!empty($data->title) && $data->title==$title){$out.=' selected="selected" ';}
				$out.='>'.esc_html($title).'</option>';
			}
			$out.='</select></div>';
		}
	}
     //first name
    $out.='<div class="church-admin-form-group"><label for="first_name'.(int)$x.'">'.esc_html( __( 'First Name','church-admin') );
   	$out.=' *';
    $out.='</label><input placeholder="'.esc_html( __( 'First Name','church-admin') ).'" type="text" ';
    $out.='required="required" ';
	//mick - added the $x to first_name 
    $out.='data-name="first_name" class="church-admin-form-control" name="first_name'.(int)$x.'"';
    if(!empty( $data->first_name) ) $out.=' value="'.esc_html( $data->first_name).'" ';
    $out.='/></div>';

    //middle name
    $middle_name=get_option('church_admin_use_middle_name');
	
	if( !empty($middle_name) || (!empty($exclude) && !in_array('middle-name',$exclude) ))
	{
		$out.='<div class="church-admin-form-group"><label for="middle_name'.(int)$x.'">'.esc_html( __( 'Middle Name','church-admin') );
		 if(in_array('middle_name',$required) )$out.=' *';
		 $out.='</label><input type="text" placeholder="'.esc_html( __( 'Middle Name','church-admin') ).'"';
		  if(in_array('middle_name',$required) )$out.='required="required" ';
		 $out.='  data-name="middle_name" class="church-admin-form-control" name="middle_name'.(int)$x.'" ';
		if(!empty( $data->middle_name) ) $out.=' value="'.esc_html( $data->middle_name).'" ';
    	$out.='/></div>';
    }

    //nickname

	$nickname=get_option('church_admin_use_nickname');
	if(in_array('nickname',$exclude) )$nickname=FALSE;
	if(!empty( $nickname) )
	{
		$out.='<div class="church-admin-form-group"><label for="nickname'.(int)$x.'">'.esc_html( __( 'Nickame','church-admin') );
		 if(in_array('middle_name',$required) )$out.=' *';
		$out.='</label><input type="text" placeholder="'.esc_html( __( 'Nickame','church-admin') ).'" data-name="nickname" class="church-admin-form-control" ';
		 if(in_array('nickname',$required) )$out.='required="required" ';
		$out.='name="nickname'.(int)$x.'" ';
	 	if(!empty( $data->nickname) ) $out.=' value="'.esc_html( $data->nickname).'" ';
    	$out.='/></div>';
    }

    //prefix

	$prefix=get_option('church_admin_use_prefix');
    if(empty($prefix)){$exclude[]='prefix';}
	if(is_array( $exclude) && !in_array('prefix',$exclude) )
	{
		$out.='<div class="church-admin-form-group"><label for="prefix'.(int)$x.'">'.esc_html( __( 'Prefix e.g. "van der"','church-admin') );
		 if(in_array('prefix',$required) )$out.=' *';
		$out.='</label><input placeholder="'.esc_html( __( 'Prefix e.g. van der','church-admin') ).'" type="text" data-name="prefix" class="church-admin-form-control" ';
		if(in_array('prefix',$required) )$out.='required="required" ';
		$out.=' name="prefix'.(int)$x.'" ';
		if(!empty( $data->prefix) ) $out.=' value="'.esc_html( $data->prefix).'" ';
    	$out.='/></div>';
	}

	//last name
	$out.='<div class="church-admin-form-group"><label for="last_name'.(int)$x.'">'.esc_html( __( 'Last Name','church-admin') );
	$out.=' *';
	$out.='</label><input placeholder="'.esc_html( __( 'Last Name','church-admin') ).'" type="text" required="required" data-name="last_name" class="church-admin-form-control" ';
	if(in_array('last_name',$required) )$out.=' required="required" ';
	$out.='  name="last_name'.(int)$x.'" ';
	if(!empty( $data->last_name) ) $out.=' value="'.esc_html( $data->last_name).'" ';
    $out.='/></div>';
    
	//mobile
    if(!in_array('mobile',$exclude) )
	{
    	$out.='<div class="church-admin-form-group"><label >'.esc_html( __( 'Mobile','church-admin') );
    	if(in_array('mobile',$required) )$out.=' *';
    	$out.='</label><input type="text" data-name="mobile" class="church-admin-form-control" placeholder="'.esc_html( __( 'Mobile','church-admin') ).'"';
    	if(in_array('mobile',$required) )$out.=' required="required"';
    	$out.='  name="mobile'.(int)$x.'" ';
    	if(!empty( $data->mobile) )$out.=' value="'.esc_html( $data->mobile).'" ';
    	$out.='/></div>';
        
        if(church_admin_level_check('Directory') )
        {
            //e164 field
            $out.='<div class="church-admin-form-group"><label ><a href="https://www.churchadminplugin.com/tutorials/e-164-phone-format/" target="_blank">'.esc_html( __( 'Mobile in e.164 format (+Country code and no leading zero, spaces, hyphens or brackets)','church-admin') );
    	   
    	   $out.='</a></label><input type="text" data-name="e164cell" class="church-admin-form-control" placeholder="'.esc_html( __( 'Mobile in e.164 format','church-admin') ).'"';
    	   
    	   $out.='  name="e164cell'.(int)$x.'" ';
    	   if(!empty( $data->e164cell) )$out.=' value="'.esc_html( $data->e164cell).'" ';
    	   $out.='/></div>';
        }
    }
	//email
	$out.='<div class="church-admin-form-group"><label >'.esc_html( __( 'Email','church-admin') );
	$out.='</label><input type="text" data-name="email" class="church-admin-form-control ca-email" placeholder="'.esc_html( __( 'Email','church-admin') ).'"';
	$out.='  name="email'.(int)$x.'"';
    if(!empty( $data->email) )$out.=' value="'.esc_html( $data->email).'" ';
    $out.='/></div>';

	//date of birth
	if(!in_array('date-of-birth',$exclude) )
	{
		if(!empty( $data->date_of_birth) )  {$dob=$data->date_of_birth;}else{$dob=NULL;}
		$out.= '<div class="church-admin-form-group"><label for="date_of_birth'.(int)$x.'x">'.esc_html( __( 'Date of birth','church-admin') );
		$out.='</label>'. church_admin_date_picker( $dob,'date_of_birth'.(int)$x,FALSE,1910,date('Y'),'date_of_birth','date_of_birth'.(int)$x);
		$out.='</div>';
	}
	if(!in_array('gender',$exclude) )
	{
    	$gender=get_option('church_admin_gender');
		$out.='<div class="church-admin-form-group"><label >'.esc_html( __( 'Gender','church-admin') ).'</label><select data-name="sex" name="sex'.(int)$x.'" class="sex church-admin-form-control" >';
		$first=$option='';

		foreach( $gender AS $key=>$value)
		{
			if(isset( $data->sex)&&$data->sex == $key)
				{
					$first= '<option value="'.esc_html( $key).'" selected="selected">'.esc_html( $value).'</option>';
				}
				else
				{
					$option.= '<option value="'.esc_html( $key).'" >'.esc_html( $value).'</option>';
				}

		}
		$out.=$first.$option.'</select></div>'."\r\n";
	}
	//marital status
	if(!in_array('marital-status',$exclude) )
	{
		$church_admin_marital_status=get_option('church_admin_marital_status');
		$out.='<div class="church-admin-form-group"><label for="marital_status'.(int)$x.'">'.esc_html( __( 'Marital Status','church-admin') ).'</label><select data-name="marital_status" name="marital_status'.(int)$x.'" id="marital_status'.(int)$x.'" class="marital_status church-admin-form-control">';
    	$current_marital_status = !empty($data->marital_status) ? $data->marital_status : 0;
    	foreach( $church_admin_marital_status AS $id=>$type)
    	{
			$out.='<option value="'.$id.'" '.selected($current_marital_status,$id,FALSE).'>'.$type.'</option>'."\r\n";
    	}
    	$out.='</select></div>'."\r\n";
	}

	//person type
	$out.='<div class="church-admin-form-group"><label for="people_type_id'.(int)$x.'">'.esc_html( __( 'Person type','church-admin') ).'</label><select id="people_type_id'.(int)$x.'" name="people_type_id'.(int)$x.'" data-name="people_type_id" class="people_type_id church-admin-form-control">';
    $first=$option='';
    foreach( $people_type AS $id=>$type)
    {

    	if(!empty( $data->people_type_id)&& $id==$data->people_type_id)
    	{
    		$first='<option value="'.$id.'" selected="selected">'.$type.'</option>'."\r\n";
    	}else $option.='<option value="'.$id.'">'.$type.'</option>'."\r\n";


    }
    $out.=$first.$option.'</select></div>'."\r\n";
	
	/*****************************************
    * User bio
    ****************************************/
	if(!empty( $data->user_id) && user_can( $data->user_id, 'edit_posts' ) )
	{
		$bio=get_user_meta( $data->user_id,'description',TRUE);
		$allowed_html = wp_kses_allowed_html( 'data' );
		$out.='<div class="church-admin-form-group"><label >'.esc_html( __( 'Bio for user','church-admin') ).'</label><textarea data-name="bio" id="bio'.(int)$x.'" name="bio'.(int)$x.'" style="height:150px" class="church-admin-form-control">';
		if(!empty( $bio) )$out.=wp_kses( $bio,$allowed_html);
		$out.='</textarea></div>';
	}
	/*************************************
	*
	*	Member levels for authorised users
	*
	*************************************/
	$directory_permission=church_admin_level_check('Directory');

	if( $directory_permission)
	{
		$first=$option='';
		$out.='<div class="church-admin-form-group"><label for="member_type'.(int)$x.'">'.esc_html( __( 'Member type','church-admin') ).'</label><select name="member_type_id'.(int)$x.'"  data-name="member_type_id" class="church-admin-form-control">';
        foreach( $member_type AS $id=>$type)
        {
        	if(!empty( $data->member_type_id) && $data->member_type_id==$id)
        	{	$first.= '<option value="'.$id.'" selected="selected" >'.$type.'</option>';
        	}
        	else
        	{
        		$option.='<option value="'.$id.'">'.$type.'</option>';
        	}
        }
        $out.=$first.$option.'</select></div>';
		//member_type_id

		//if(!empty( $data->member_data) )$prev_member_types=maybe_unserialize( $data->member_data);
		$prev_member_types=array();
		if(!empty( $data->people_id) )
		{
			$prev_member_types_res=$wpdb->get_results('SELECT ID,meta_date FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="member_date" AND people_id="'.(int)$data->people_id.'"');
			if(!empty( $prev_member_types_res) )
			{

				foreach( $prev_member_types_res AS $prevMTrow)
				{
					$prev_member_types[$prevMTrow->ID]=$prevMTrow->meta_date;
				}
			}
		}
		if(!in_array('member-dates',$exclude) )
		{
	    	$out.='<h3 >'.esc_html( __( 'Dates of Member Levels','church-admin') ).'</h3>';
	    	foreach( $member_type AS $key=>$value)
	    	{


	    		if ( empty( $prev_member_types[$key] ) )$prev_member_types[$key]=NULL;
	    		if ( empty( $value) )$value='';

				$out.='<div class="church-admin-form-group"><label>'.$value.'</label>'. 			church_admin_date_picker( $prev_member_types[$key],'mt-'.(int)$key.'-'.$x,FALSE,1910,date('Y'),'mt-'.(int)$key,'mt-'.(int)$key.'-'.$x).'</div>';

			}
			$out.="\r\n";
		}

	}
	else
	{
		/***************************************************************************************
		*
		* user cannot adjust member_type, but we must set to mailing list or keep current level
		*
		***************************************************************************************/
		if(!empty( $data->member_type_id) )  {$member_type_id=intval( $data->member_type_id);}else{$member_type_id=1;}
			$out.='<input type="hidden" data-name="member_type_id"  name="member_type_id'.(int)$x.'" value="'.$member_type_id.'" />';
		
	}
 


	

	

    

	/*****************************************************
	*
	* Custom Fields
	*
	*****************************************************/
	$custom_fields=church_admin_get_custom_fields();
	church_admin_debug('Custom field section');
	church_admin_debug($custom_fields);
	church_admin_debug('Onboarding: '.$onboarding);
	if(!empty( $custom_fields)&&!in_array('custom',$exclude) )
	{
		
		foreach( $custom_fields AS $id=>$field)
		{
			if( $field['section']!="people")continue;
			$dataField='';
			if(!empty( $data->people_id) ){
				$dataField=$wpdb->get_var('SELECT `data` FROM '.$wpdb->prefix.'church_admin_custom_fields_meta' .' WHERE section="people" AND people_id="'.(int)$data->people_id.'" AND custom_id="'.(int)$id.'"');
				church_admin_debug($wpdb->last_query);
				church_admin_debug('$dataField: '.$dataField);
			}
			if((empty($onboarding) && empty($field['onboarding'])) ||!empty($onboarding)&&!empty($field['onboarding'])){
				church_admin_debug($field);
				//show fields 
				$out.='<div class="church-admin-form-group"><label >'.esc_html( $field['name'] ).'</label>';
				switch( $field['type'] )
				{
					case 'boolean':
						church_admin_debug('Boolean');
						$out.='</div><div class="checkbox"><label><input type="radio" data-name="custom-'.(int)$id.'"   value="1" name="custom-'.(int)$id.'-'.(int)$x.'" ';
						if (isset( $dataField)&&$dataField==1)
							$out.= 'checked="checked" ';
						$out.='>'.esc_html( __( 'Yes','church-admin') ).'</label></div><div class="checkbox"><label> <input type="radio" data-name="custom-'.(int)$id.'-'.(int)$x.'"  value="0" name="custom-'.(int)$id.'-'.(int)$x.'" ';
						if (isset( $dataField)&& $dataField==0)
							$out.= 'checked="checked" ';
						$out.='>'.esc_html( __( 'No','church-admin') ).'</label></div>';
					break;
					case'text':
						church_admin_debug('Text');
						$out.='<input type="text" data-name="custom-'.(int)$id.'" class="church-admin-form-control"  name="custom-'.(int)$id.'-'.(int)$x.'" ';
						if(!empty( $dataField)||isset( $field['default'] ) )$out.=' value="'.esc_html( $dataField).'"';
						$out.='/>';
						$out.='</div>';
					break;
					case'date':
						$out.= church_admin_date_picker( $dataField,'custom-'.(int)$id.'-'.$x,FALSE,1910,date('Y'),'custom-'.(int)$id,'custom-'.(int)$id.'-'.$x);
						$out.='</div>';
					break;
					case 'checkbox':
						church_admin_debug('Checkbox');
						$out.='</div>';
						$options = maybe_unserialize($field['options']);
						if(!empty($dataField))$dataField = maybe_unserialize($dataField);
						if(empty($options)){break;}
						
						for ($y=0;$y<count($options);$y++){
							$out.='<div class="checkbox"><label><input type="checkbox"  name="custom-'.(int)$id.'-'.(int)$x.'[]" value="'.esc_attr($options[$y]).'" ';
							if(!empty($dataField) && in_array($options[$y],$dataField) ){$out.=' checked="checked" ';}
							
							$out.='> '.esc_html($options[$y]).'</label></div>';
						}
					break;
					case 'radio':
						church_admin_debug('Radio');
						$out.='</div>';
						$options = maybe_unserialize($field['options']);
					
						if(empty($options)){break;}
						
						for ($y=0;$y<count($options);$y++){
							$out.='<div class="checkbox"><label><input type="radio"  name="custom-'.(int)$id.'-'.(int)$x.'" value="'.esc_attr($options[$y]).'" ';
							if(!empty($dataField) && $options[$y] == $dataField) {$out.=' checked="checked" ';}
							$out.='> '.esc_html($options[$y]).'</label></div>';
						}
					break;	
					case 'select':
						
						$options = maybe_unserialize($field['options']);
						if(!empty($dataField))$dataField = maybe_unserialize($dataField);
						if(empty($options)){break;}
						$out.='<select data-name="custom-'.(int)$id.'" name="custom-'.(int)$id.'-'.(int)$x.'" class="church-admin-form-control">';
						$out.='<option>'.esc_html(__('Choose...','church-admin')).'</option>';
						for ($y=0;$y<count($options);$y++){
							$out.='<option value="'.esc_attr($options[$y]).'" '.selected($options[$y],$dataField,FALSE).'> '.esc_html($options[$y]).'</option>';
						}
						$out.='</select>';
						$out.='</div>';
					break;
				}
				
			}
			else{
				//not onboarding, so just show the values
				if(isset($dataField)){
					$out.= '<p><strong>'.esc_html(sprintf(__('Onboarding custom field %1$s','church-admin'), $field['name'] )).': </strong>';
					switch($field['type'] )
					{
						case 'boolean':
							$op = !empty($dataField) ? __('Yes','church-admin') : __('No','church-admin');
						break;
						case 'date':
							$op = mysql2date(get_option('date_format'),$dataField);
						break;
						default:
							if(!empty($dataField)){$op = $dataField;} else{$op = __('No value given when onboarding','church-admin');}
						break;
					}
					$out.= esc_html($op).'</p>';
				}
			}
		}

	}

	/*****************************************************
	*
	* Privacy and comms permissions
	*
	*****************************************************/
	$out.='<h3>'.esc_html( __( 'Privacy','church-admin') ).'</h3>';
    $out.='<h2>'.esc_html( __( 'I give permission...','church-admin') ).'</h2>';
	$out.='<div class="checkbox"><label ><input type="checkbox" name="email_send'.$x.'" value="TRUE" id="email_send" class="email-permissions" data-name="email_send"  ';
	if(!empty( $data->email_send)||empty( $data) ) $out.=' checked="checked" ';
	$out.=' /> '.esc_html( __( 'To receive email','church-admin') ).'</label></div>';
	$out.='<p><strong>'.esc_html( __( 'Refine type of email you can receive','church-admin') ).'</strong></p>';
	$out.='<div class="checkbox"><label ><input type="checkbox" name="news_send'.$x.'" value="TRUE" id="news_send" class="email-permissions" data-name="news_send"  ';
	if(!empty( $data->news_send) ){
		$out.=' checked="checked" ';
	}
	$out.=' /> '.esc_html( __( 'To receive blog post email','church-admin') ).'</label></div>';
	$noPrayer=get_option('church-admin-no-prayer');
	if ( empty( $noPrayer) ){
		$out.='<div class="checkbox"><label ><input type="checkbox" value="1"  data-name="prayer_chain" id="prayer_requests" class="email-permissions" name="prayer_chain'.$x.'" ';
		if(!empty( $data->people_id) ){
			$prayer=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$data->people_id.'" AND meta_type="prayer-requests"');
		}
		if(!empty( $prayer) ) {
			$out.=' checked="checked" ';
		}
		$out.=' /> '.esc_html( __( 'To receive Prayer requests by email','church-admin') ).'</label></div>';
	}
	$noBibleReadings=get_option('church-admin-no-bible-readings');
	if ( empty( $noBibleReadings) ){
		$out.='<div class="checkbox"><label ><input type="checkbox" value="1" data-name="bible_readings" id="bible_readings" class="email-permissions" name="bible_readings'.$x.'" ';
		if(!empty( $data->people_id) ){
			$bible=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$data->people_id.'" AND meta_type="bible-readings"');
		}
		if(!empty( $bible) ) $out.=' checked="checked" ';
		$out.=' /> '.esc_html( __( 'To receive new Bible Reading notes by email','church-admin') ).'</label></div>';
		
	}

	
		   
	$out.='<p><strong>'.esc_html( __( 'Other privacy permissions','church-admin') ).'</strong></p>';
	$out.='<div class="checkbox"><label ><input type="checkbox" name="sms_send'.$x.'" value="TRUE" data-name="sms_send"  ';
	if(!empty( $data->sms_send)||empty( $data) ) {
		$out.=' checked="checked" ';
	}
	$out.=' /> '.esc_html( __( 'To receive SMS','church-admin') ).'</label></div>';
	
	
	
    
    $out.='<div class="checkbox"><label ><input type="checkbox" name="phone_calls'.$x.'" value="TRUE" data-name="phone_calls"  ';
	if(!empty( $data->phone_calls)||empty( $data) ){
		 $out.=' checked="checked" ';
	}
	$out.=' /> '.esc_html( __( 'To receive phone calls','church-admin') ).'</label></div>';
    
    $out.='<div class="checkbox"><label ><input type="checkbox" name="photo_permission'.$x.'" value="TRUE" data-name="photo_permission"  ';
	if(!empty( $data->photo_permission)||empty( $data) ){
		 $out.=' checked="checked" ';
	}
	$out.=' /> '.esc_html( __( 'To show photos of me on the website','church-admin') ).'</label></div>';

	$out.='<div class="checkbox"><label>';
    $out.='<input type="checkbox" name="show_me'.$x.'" value="TRUE" data-name="show_me"  ';
	if(!empty( $data->show_me) ){
		$out.='checked="checked" ';
	}
	$out.='/>';
	$out.=esc_html( __( 'To show me on the password protected address list','church-admin') );
	$out.='</label></div>';

	$out.='<p><strong>'.esc_html( __('Refine address list privacy','church-admin' ) ).'</strong></p>';
	$fine_privacy=!empty($data->privacy)?maybe_unserialize($data->privacy):array();
	//show email
	$out.='<div class="checkbox"><input type="checkbox" name="show-email'.$x.'" id="show-email" ';
	if(!empty( $fine_privacy['show-email']) )  {$out.=' checked ="checked" ';}
	$out.='/> '.esc_html( __("Show email address",'church-admin' ) ).' </div>';
	//show cell
	$out.='<div class="checkbox"><input type="checkbox" name="show-cell'.$x.'" id="show-cell" ';
	if(!empty( $fine_privacy['show-cell']) )  {$out.=' checked ="checked" ';}
	$out.='/> '.esc_html( __("Show cell number",'church-admin' ) ).' </div>';
	//show landline
	$out.='<div class="checkbox"><input type="checkbox" name="show-landline'.$x.'" id="show-landline"  ';
	if(!empty( $fine_privacy['show-landline']) )  {$out.=' checked ="checked" ';}
	$out.='/> '.esc_html( __("Show landline",'church-admin' ) ).' </div>';
	//show address
	$out.='<div class="checkbox"><input type="checkbox" name="show-address'.$x.'" id="show-address"  ';
	if(!empty( $fine_privacy['show-address']) )  {$out.=' checked ="checked" ';}
	$out.='/> '.esc_html( __("Show address",'church-admin' ) ).' </div><p>&nbsp;</p>';





    $gdpr=get_option('church_admin_gdpr');

	if( $directory_permission){
		$first=$option='';
		$out.='<div class="church-admin-form-group"><label >'.esc_html( __( 'How data permission is given','church-admin') ).'</label><input name="gdpr'.$x.'" type="text" data-name="gdpr" class="church-admin-form-control" ';
		if(!empty( $data->gdpr_reason) )$out.=' value="'.esc_html( $data->gdpr_reason).'" ';
		$out.='/></div>';
		
	}
	else{
		
		$out.='<input type="hidden"  name="gdpr'.$x.'" ';
		if(!empty( $data->gdpr_reason) )  {
			$out.='value="'.esc_html( $data->gdpr_reason).'"';
		}else{
			$out.'value="'.esc_html(esc_html( __( 'User registered on the website','church-admin') ) ).'"';
		}
		$out.='/>';
	}
    
		/*****************************************************
		*
		* user_id,head_of_household
		*
		*****************************************************/
		//MICK WALL
		//Make user_id post value have the index of the user being edited  - so user_id1, user_id2 etc.
		if(!empty( $data->user_id) )$out.='<input type="hidden" data-name="user_id" name="user_id'.$x.'" value="'.intval( $data->user_id).'" />';
        if(!empty( $data->head_of_household) )$out.='<input type="hidden" data-name="head_of_household" name="head_of_household'.(int)$x.'" value="'.intval( $data->head_of_household).'" />';
        if(!empty( $data->people_order) )
        {
            $out.='<input type="hidden" data-name="people_order" name="people_order'.(int)$x.'" value="'.intval( $data->people_order).'" />';
        }
    $out.='<script>jQuery(document).ready(function( $)  {
            
		if( $("#email_send").prop("checked")== false)
		{
			console.log("Unchecking");
			$("#news_send").prop( "checked", false );
			$("#prayer_requests").prop( "checked", false );
			$("#bible_readings").prop( "checked", false );
			
		}
		
		$(".email-permissions").change(function()
		{
			var id=$(this).attr("id");
			switch(id)
			{
				case "email_send":
					console.log("email send changed");
					if( $(this).prop("checked")==false)
					{
						$("#news_send").prop( "checked", false );
						$("#prayer_requests").prop( "checked", false );
						$("#bible_readings").prop( "checked", false );
					
					}
				break;
				case "news_send":
				case "prayer_requests":
				case "bible_readings":
					console.log("other checkbox changed");
					if( $(this).prop("checked") ) 
					{
						console.log("Other checked");
						$("#email_send").prop("checked", true);
					}
				break;
			}
		   
		});
		
		});
	</script>';
    $out.='</div><!-- .ca-people-form-->';
    $out.='</div><!-- .cloned-input --></div></div>';
	if( $x==1)
	{
		if(is_admin() )
		{
			$out.='<script >jQuery(document).ready(function( $)  {
				$( "body" ).on("click",".hndle",function()  {
						console.log("person clicked")
						console.log("all data: "+ $(this).data() );
						var id=$(this).data("people-id");
						console.log("Person\'s id "+id);
						$("#person"+id).toggle();
					});
					//remove image
					$(".remove-image").click(function()
					{
						console.log("REMOVE IMAGE");
						var type= $(this).data("type");
						var attachment_id=$(this).data("attachment_id");
						var peopleID=$(this).data("id");
						var imageid=$(this).attr("id");
						var nonce="'.wp_create_nonce("remove-image").'";
						var data={"action":"church_admin","method":"remove-image","type":type,"attachment_id":attachment_id,"id":peopleID,"nonce":nonce};
						console.log("Data to send");
						console.log(data);
						$.ajax({
											url: ajaxurl,
											type: "POST",
											data: data,
											success: function(res) {
												console.log("Response " + res);
												$("#upload-message").html("'.esc_html( __( 'Image Deleted. Drag and drop new image.','church-admin' ) ).'<br>");
												$("#people-image"+res).attr("src","'.plugins_url('/', dirname(__FILE__) ) . 'images/man.svg");
												$("#people-image"+res).attr("srcset","");
												$("#attachment_id"+res).val("");
											},
											error: function(res) {
										$("#upload-message").html("Error deleting<br>");
											}
										});
					});


					var mediaUploader;

					$(".upload-button").click(function(e) {
						console.log("Upload button clicked");
						e.preventDefault();
						var id="#attachment_id"+$(this).data("people-id");
						var peopleID=$(this).data("people-id");
						console.log("Attachment id: "+id);
						// If the uploader object has already been created, reopen the dialog
						if (mediaUploader) {
						mediaUploader.open();
						return;
						}
						// Extend the wp.media object
						mediaUploader = wp.media.frames.file_frame = wp.media({
						title: "Choose Image",
						button: {
						text: "Choose Image"
						}, multiple: false });

						// When a file is selected, grab the URL and set it as the text fields value
						mediaUploader.on("select", function() {
						var attachment = mediaUploader.state().get("selection").first().toJSON();
						console.log("Attachment details");
						console.log(attachment);
						$(id).val(attachment.id);
						
						if(typeof attachment.sizes.medium !=="undefined")
						{
							console.log("Image id: " +"#people-image"+peopleID);
							console.log("Medium "+ attachment.sizes.medium.url);
							$("#people-image"+peopleID).attr("src",attachment.sizes.medium.url);
							$("#upload-message").html("'.esc_html( __( 'Image uploaded','church-admin') ).'");
						}
						else
						{
							console.log("Medium "+ attachment.sizes.full.url);
							$("#people-image"+peopleID).attr("src",attachment.sizes.full.url);
						}
						$("#people-image"+peopleID).attr("srcset",null);
					});
						// Open the uploader dialog
						mediaUploader.open();
					});
				

				});</script>';

    

		}
	}
    return $out;
}


/**
 * save a person using POST with $x
 *
 * @param $_POST,$x,$people_id,$household_id
 *
 *
 * @author andy_moyle
 *
 */
function church_admin_save_person( $x=1,$people_id=NULL,$household_id=NULL,$exclude=array(),$onboarding=0 )
{
	church_admin_debug('*** Save person function ***');
	church_admin_debug('People_id: '.$people_id);
	church_admin_debug('POSTED DATA');
	//church_admin_debug($_POST);


    global $wpdb;
    if(is_user_logged_in() )
    {
        $user=wp_get_current_user();
        
    }
	/************************************
	 * GET $old_email for MailChimp sync
	 ************************************/
    if(!empty( $people_id) )
	{
		$old_email=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	}
	else
	{
		$old_email=NULL;
	}
	
	
	
	$member_type=church_admin_member_types_array();
	$people_type=get_option('church_admin_people_type');
	$church_admin_marital_status= get_option('church_admin_marital_status');
	$out='';
	//sanitise form input

    	$form=array();
    	foreach( $_POST AS $key=>$value) {
			$form[$key]=church_admin_sanitize( $value );
		}
		church_admin_debug('sanitized data');
		//church_admin_debug($form);
		$dob = !empty($_POST['data_of_birth'.$x])? sanitize_text_field(stripslashes($_POST['data_of_birth'.$x])):null;
		$dobx = !empty($_POST['data_of_birth'.$x.'x'])? sanitize_text_field(stripslashes($_POST['data_of_birth'.$x.'x'])):null;
    	

    	if(!empty( $_POST['attachment_id'.$x] ) )  {
			$form['attachment_id'.$x]=intval( $_POST['attachment_id'.$x] );
		}else{
			if(!empty( $data->attachment_id) )$form['attachment_id'.$x]=intval( $data->attachment_id);
		}
		$data['attachment_id']=!empty( $form['attachment_id'.$x] )?intval( $form['attachment_id'.$x] ):0;
		church_admin_debug('After fiddling with dob');
		//church_admin_debug($form);
    	//build data array for query
    	$data=array();
    	$data['user_id']			=	!empty( $form['user_id'.$x] )?intval( $form['user_id'.$x] ):NULL;
    	//solve issue of new ID getting formed.
		if(!empty( $form['ID'] ) )$data['user_id']=intval( $form['ID'] );
		if ( empty( $exclude) )$exclude=array();

		
    	$data['people_type_id']		=	!empty( $form['people_type_id'.$x] )?intval( $form['people_type_id'.$x] ):1;
    	$data['household_id']		=	!empty( $household_id)?(int)$household_id:NULL;
    	$data['member_type_id']		=	isset( $form['member_type_id'.$x] )?intval( $form['member_type_id'.$x] ):1;
		$data['title']			=	!empty( $form['title'.$x] )?trim( $form['title'.$x] ):"";
    	$data['first_name']			=	!empty( $form['first_name'.$x] )?trim( $form['first_name'.$x] ):"";
    	if(!in_array('nickname',$exclude) )$data['nickname']			=	!empty( $form['nickname'.$x] )?trim( $form['nickname'.$x] ):"";
    	if(!in_array('middlename',$exclude) )$data['middle_name']		=	!empty( $form['middle_name'.$x] )?trim( $form['middle_name'.$x] ):"";
    	if(!in_array('prefix',$exclude) )$data['prefix']				=	!empty( $form['prefix'.$x] )?trim( $form['prefix'.$x] ):"";
    	$data['last_name']			=	!empty( $form['last_name'.$x] )?trim( $form['last_name'.$x] ):"";
    	$data['email']				=	!empty( $form['email'.$x] )?$form['email'.$x]:"";
    	$data['mobile']				=	!empty( $form['mobile'.$x] )?$form['mobile'.$x]:"";
        if(!empty( $form['mobile'.$x] )&& empty( $form['e164cell'.$x] ) )  {
			$data['e164cell']=church_admin_e164( $form['mobile'.$x] );
		}else{
			$data['e164cell']= !empty( $form['e164cell'.$x] ) ? esc_sql( $form['e164cell'.$x] ) : NULL;
		}
		
		if(!in_array('date-of-birth',$exclude) ){
			$data['date_of_birth']		=	(!empty( $form['date_of_birth'.$x]) && $form['date_of_birth'.$x]!='0000-00-00' )?$form['date_of_birth'.$x]:NULL;
			if(empty($data['date_of_birth'])) {
				//use manually added if existent
				$data['date_of_birth']	=	!empty( $form['date_of_birth'.$x.'x'] )?date('Y-m-d',strtotime($form['date_of_birth'.$x])):NULL;
			}
		}
		if(!empty($data['date_of_birth']) && $data['date_of_birth'] =="0000-00-00"){$data['date_of_birth'] =null;}
    	if(!in_array('marital-status',$exclude) )$data['marital_status']= isset( $form['marital_status'.$x] )?$form['marital_status'.$x]:0;
    	if(!in_array('image',$exclude) )$data['attachment_id']		=	!empty( $form['attachment_id'.$x] )?$form['attachment_id'.$x]:0;
    	//must use isset as female is  0 and !empty(0) returns false!
    	$data['sex']				=	isset( $form['sex'.$x] )?intval( $form['sex'.$x] ):1;
    	//$data['prayer_chain']		=	isset( $form['prayer_chain'.$x] )?1:0;//deprecated 1.2608, now part of ministries
    	$data['site_id']			=	isset( $form['site_id'.$x] )?intval( $form['site_id'.$x] ):1;
    	$data['email_send']			=	!empty( $form['email_send'.$x] )?1:0;
		$data['news_send']			=	!empty( $form['news_send'.$x] )?1:0;
        $data['phone_calls']			=	!empty( $form['phone_calls'.$x] )?1:0;
    	$data['sms_send']			=	!empty( $form['sms_send'.$x] )?1:0;
    	$data['mail_send']			=	!empty( $form['mail_send'.$x] )?1:0;
        $data['photo_permission']	=	!empty( $form['photo_permission'.$x] )?1:0;
		$data['show_me']			= 	!empty( $form['show_me'.$x] )?1:0;
    	$data['gdpr_reason']		=	!empty( $form['gdpr'.$x] )?$form['gdpr'.$x]:"";
     	$data['kidswork_override']	=	!empty( $form['kidswork_override'.$x] )?$form['kidswork_override'.$x]:"0";
		
		if ( empty( $form['people_order'.$x] ) )
        {
            $data['people_order']=(int)$x;
        }
        else{$data['people_order']=intval( $form['people_order'.$x] );}
		$data['head_of_household']	=	isset( $form['head_of_household'.$x] )?intval( $form['head_of_household'.$x] ):0;
		 //privacy
		 $privacy=array();
		 if(!empty($_POST['show-email'.$x])){$privacy['show-email']=1;}else{$privacy['show-email']=0;}
		 if(!empty($_POST['show-cell'.$x])){$privacy['show-cell']=1;}else{$privacy['show-cell']=0;}
		 if(!empty($_POST['show-landline'.$x])){$privacy['show-landline']=1;}else{$privacy['show-landline']=0;}
		 if(!empty($_POST['show-address'.$x])){$privacy['show-address']=1;}else{$privacy['show-address']=0;}
		
		 $data['privacy']=serialize($privacy);
		 church_admin_debug('Validated data');
		//church_admin_debug($data);
		church_admin_debug('People_id: '.$people_id);
		//front_end_register doesn't pass people_id so let's check they are not in first...
		if ( empty( $people_id) )
    	{
			$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" AND first_name="'.esc_sql( $data['first_name'] ).'" AND last_name="'.esc_sql( $data['last_name'] ).'" AND people_type_id = "'.esc_sql( $data['people_type_id'] ).'"');
			//church_admin_debug($wpdb->last_query);
			church_admin_debug('People_id: '.$people_id);
		}

		//expected data
		$data['active'] = 1;
		$data['household_id'] = (int)$household_id;
		$data['last_updated'] = wp_date('Y-m-d H:i:s');
		$data['updated_by'] = !empty($user->ID) ? (int)$user->ID:1;
		$people_table_columns = array('household_id','title','first_name','middle_name','nickname','prefix','last_name','email','mobile','e164cell','date_of_birth','marital_status','attachment_id','site_id','email_send','sms_send','news_send','phone_calls','mail_send','photo_permission','show_me','gdpr_reason','kidswork_override','people_order','people_type_id','member_type_id','sex','privacy','last_updated','updated_by');
		

		if(!empty( $people_id) )
		{
			$wpdb->update($wpdb->prefix.'church_admin_people',$data,array('people_id'=>$people_id));
			/*
			$SET=array();
			foreach( $people_table_columns AS $key=>$col_name) {
				$SET[]=' '.esc_sql($col_name).'="'.esc_sql( $data[$col_name] ).'"';
			}
			$sql='UPDATE '.$wpdb->prefix.'church_admin_people SET '.implode(",",$SET).' WHERE people_id="'.(int)$people_id.'"' ;
			$wpdb->query( $sql);
			*/
			//church_admin_debug($wpdb->last_query);
			church_admin_debug('People_id: '.$people_id);
   		}
		else
		{
			
			$field_names = $field_data = array();
			
			foreach( $people_table_columns AS $key=>$col_name)  {
				$field_names []=$col_name; $field_data[]=esc_sql( $data[$col_name]);
			}
           
			$sql='INSERT INTO '.$wpdb->prefix.'church_admin_people ('.implode(", ",$field_names).',first_registered) VALUES ("'.implode('", "',$field_data).'","'.esc_sql(wp_date('Y-m-d')).'")';
			$wpdb->query( $sql);
			$people_id=$wpdb->insert_id;
			//church_admin_debug($wpdb->last_query);
			church_admin_debug('People_id: '.$people_id);	
		}
		//if(defined('CA_DEBUG') )church_admin_debug("Person update/insert \r\n".$sql);
		
		//church_admin_debug($wpdb->last_query);
		church_admin_debug('People_id: '.$people_id);	

		
		//clear people meta table
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'"');


		
	   	/**************************************
		* add updated by tp people table
		**************************************/
        if(!empty( $user->ID) )  {
			$user_id=$user->ID;}else{$user_id=0;
			}
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET updated_by="'.(int)$user_id.'" WHERE people_id="'.(int)$people_id.'"');
		//church_admin_debug($wpdb->last_query);
		church_admin_debug('People_id: '.$people_id);	
		
		if(!empty( $user->ID) )  {$user_id=$user->ID;}else{$user_id=0;}
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET updated_by="'.(int)$user_id.'" WHERE household_id="'.(int)$data['household_id'].'"');
    	//church_admin_debug($wpdb->last_query);
    	
		//create user if necessary
    	if(!empty( $_POST['username'] )&& church_admin_level_check('Directory') ){
			church_admin_debug('Before function call'.$people_id);
			church_admin_create_user( $people_id,$household_id,church_admin_sanitize($_POST['username'] ),null );
		}
		/***************
		 * USER bio
		 ****************/
		//user bio
        $user_id=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
        if(!empty( $user_id)&&!empty( $_POST['bio'.$x] ) )
        {
            update_user_meta( $user_id,'description',sanitize_text_field( stripslashes($_POST['bio'.$x] ) ));
        }
		/*************************************************************
		*
		*    Member level dates
		*
		***************************************************************/
			
		
			if(is_array( $exclude)&&!in_array('member-dates',$exclude) )
			{
				//handle member type dates


				foreach( $member_type AS $id=>$type)
				{

					if(!empty( $_POST['mt-'.$id.'-'.$x] ) && church_admin_checkdate( sanitize_text_field(stripslashes($_POST['mt-'.$id.'-'.$x])) ) )
					{
						church_admin_update_people_meta( $id,$people_id,'member_date',sanitize_text_field(stripslashes($_POST['mt-'.$id.'-'.$x] )));

					}
				}
			}
			/*************************************************************
			*
			*    Small Group
			*
			***************************************************************/
		
		//handle new smallgroup
		if(!empty( $_POST['smallgroup'.$x] ) )
		{

				$check=$wpdb->get_var('SELECT id FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE group_name="'.esc_sql(sanitize_text_field( stripslashes($_POST['smallgroup'.$x] ) )).'"');
				if(!empty( $check) )  {church_admin_update_people_meta( $check,$people_id,'smallgroup');}
				else
				{
					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_smallgroup (group_name) VALUES("'.esc_sql(sanitize_text_field(  stripslashes( $_POST['smallgroup'.$x] ) ) ).'")');
					$id=$wpdb->insert_id;
					church_admin_update_people_meta( $id,$people_id,'smallgroup');
				}

		}
		
		if(!empty( $_POST['smallgroup_id'.$x] ) )
		{
			foreach( $_POST['smallgroup_id'.$x] AS $key=>$id)
			{

					church_admin_update_people_meta( sanitize_text_field(stripslashes($id)),$people_id,'smallgroup');
			}
		}
        else
        {
            if(defined('CA_DEBUG') )church_admin_debug('Need to put '.print_r($people_id,TRUE).' in unattached');
            church_admin_update_people_meta(1,$people_id,'smallgroup');
        }
		//classes
		
		$class_ids = !empty( $_POST['class_id'.$x] )? church_admin_sanitize($_POST['class_id'.$x]):array();
		if(!empty( $class_ids  ) )
		{

				foreach( $class_ids  AS $key=>$class_id)church_admin_update_people_meta( $class_id,$people_id,'classes');

		}


		//ministries

		

		if(!empty( $_POST['prayer_chain'.$x] ) )  {church_admin_update_people_meta(1,$people_id,"prayer-requests",date('Y-m-d'));}
		if(!empty( $_POST['bible_readings'.$x] ) )  {church_admin_update_people_meta(1,$people_id,"bible-readings",date('Y-m-d'));}
		
		

		
		/********************************************************
		*
		*   USER id
		*
		*********************************************************/


		//user account
		//check is user has user_id
		$user_id=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		//MICK WALL
		//add the .$x. to get the user id for the current index.
		
		if(!empty( $_POST['user_id'.$x] ) && empty( $user_id) )
		{
			$out.= church_admin_create_user( $people_id,$household_id,(int)$_POST['user_id'.$x],null,null );
		}
		if(!empty( $_POST['ID'] ) )
		{
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$_POST['ID'].'" WHERE people_id="'.(int)$people_id.'"');
		}
	/*****************************************************
	*
	* Custom Fields
	*
	*****************************************************/
	church_admin_debug('Handle custom fields');
	
	church_admin_update_meta_fields('people',$people_id,$household_id,$onboarding,$x);



	//church_admin_debug( $out);
    update_option('addressUpdated',time() );

	//update user_meta
	$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	if(!empty( $data->user_id) )church_admin_update_user_meta( $people_id,$data->user_id);
	
	//reset app address list cache
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
	return array('output'=>$out,'people_id'=>$people_id,'household_id'=>$household_id);
}



function church_admin_gdpr_email()
{
	if(!church_admin_level_check('Directory') ){
		wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') ) );
	}
	global $wpdb;

	//grab ID of church_admin_register shortcode
	$registerID=$wpdb->get_var('SELECT ID FROM '.$wpdb->posts.' WHERE post_content LIKE "%[church_admin_register]%" AND post_status="publish"');
	if(!empty( $registerID) )update_option('church_admin_register',$registerID);

	echo'<h2>'.esc_html( __( 'General Data Protection Requirement Email Sending','church-admin') ).'</h2>';
	$result=$wpdb->get_results(' SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.last_name,a.people_id, a.email ,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND  email!=""  AND (gdpr_reason IS NULL OR gdpr_reason="")  GROUP BY email ');
	if(!empty( $result) )
	{

		foreach( $result AS $row)
		{

			church_admin_gdpr_email_send( $row);
		}
	}
	else
	{

		echo'<p>'.esc_html( __( 'Everyone has responded! So you are GDPR compliant where communications permissions are concerned','church-admin') ).'</p>';
	}
}
function church_admin_gdpr_email_test()
{
	if(!church_admin_level_check('Directory') ){
		wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') ) );
	}
	global $wpdb;
	$user = wp_get_current_user();
	$row=$wpdb->get_row(' SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.last_name,a.people_id, a.email ,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND  email!=""  AND  user_id="'.(int)$user->ID.'"');
	if ( empty( $row) )  {
		echo'<div class="notice notice-warning notice-inline">'.esc_html( __( 'Your login is not attached to anyone in the directory','church-admin') ).'</div>';
	}
	else
	{
		echo'<h2>'.esc_html( __( 'GDPR test email send','church-admin') ).'</h2>';
		church_admin_gdpr_email_send( $row,TRUE,TRUE);
	}
}

function church_admin_gdpr_email_send( $row,$echo=TRUE,$immediate=FALSE)
{
	//if(!church_admin_level_check('Directory') )wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') );
	global $wpdb;
	if ( empty( $row->name) )$row->name=implode(" ",array_filter(array( $row->first_name,$row->prefix,$row->last_name) ));
	$message=$row->name.'<br>';

	
	$template = get_option('church_admin_confirm_email_template');
	$message = $template['message'];
	$message=str_replace('[CONFIRM_LINK]', home_url().'?confirm_email='.md5( $row->email).'&amp;people_id='.md5( $row->people_id),$message);
	$message=str_replace('[SITE_URL]',home_url(),$message);
	$message=str_replace('[CHURCH_NAME]',get_bloginfo('name'),$message);
	$message=str_replace('[CONFIRM_URL]',' <a target="_blank" href="'.home_url().'?confirm_email='.md5( $row->email).'&amp;people_id='.md5( $row->people_id).'">'.esc_html( __( 'Click to confirm','church-admin') ).'</a>',$message);
	
	$message=str_replace('[EDIT_URL]','',$message);


	$household_details = church_admin_household_details_table($row->household_id);
	$message=str_replace('[HOUSEHOLD_DETAILS]',$household_details,$message);


	$message.='<p style="margin:20px 0px"><a  target="_blank" href="'.site_url().'?confirm_email='.md5( $row->email).'&amp;people_id='.md5( $row->people_id).'" style="display: inline-block;padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: 400;line-height: 1.42857143;text-align: center;white-space: nowrap;vertical-align: middle;-ms-touch-action: manipulation;touch-action: manipulation;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none; user-select: none;background-image: none;border: 1px solid transparent;border-radius: 4px;color: #fff;background-color: #5bc0de;border-color: #46b8da;">'.esc_html( __( 'Click here to confirm','church-admin') ).'</a></p>';
	
	church_admin_email_send($row->email,$template['subject'],$message,$template['from_name'],$template['from_email'],null,null,null,$immediate);
	if(!empty($echo)){
		echo'<p>'.wp_kses_post( sprintf( __( 'GDPR confirmation email sent to %1$s at %2$s', 'church-admin' ) , church_admin_formatted_name( $row ), make_clickable( $row->email ) ) ) .'</p>';
	}
	
}

function church_admin_gdpr_pdf()
{
	if(!church_admin_level_check('Directory') ){
		wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin')  ) );
	}
	global $wpdb;
	require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
	//grab ID of church_admin_register shortcode
	$registerID=$wpdb->get_var('SELECT ID FROM '.$wpdb->posts.' WHERE post_content LIKE "%[church_admin_register]%" AND post_status="publish"');
	if(!empty( $registerID) ){
		update_option('church_admin_register',$registerID);
	}

	$sql=' SELECT a.last_name,a.people_id, a.email ,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b  WHERE a.household_id=b.household_id AND  email!=""  AND (gdpr_reason IS NULL OR gdpr_reason="")  GROUP BY email ORDER BY a.last_name';

	$result=$wpdb->get_results( $sql);
	if(!empty( $result) )
	{

		$pdf = new FPDF();

		foreach( $result AS $row)
		{
			$pdf->AddPage('P',get_option('church_admin_pdf_size') );
			$pdf->SetFont('Arial','B',16);
			$pdf->Cell(0,10,urldecode(church_admin_encode(get_bloginfo('name').' '.esc_html( __( 'Data Protection Permission','church-admin') ).' - '.$row->last_name) ),0,2,'L');
			$pdf->Ln(10);
			$pdf->SetFont('Arial','',12);
			$text= esc_html( sprintf( __( 'The GDPR regulations protect how your personal data is used. We store your name, address and phone details so we can keep the church organised and would like to be able to continue to communicate by email, sms and mail with you. Your contact details are available on the website (%1$s) within a password protected area. Please check with other members of your household who are over 16, sign this form and return if you are happy for us to continue to hold your personal data and use it to communicate with you. If you are not happy or would like to discuss further then do get in touch with the church office.','church-admin' ) , site_url() ) );
			//$height=$pdf->GetMultiCellHeight(0,7,$text,'LTR','L');
			$pdf->MultiCell(0, 7, urldecode(church_admin_encode( $text) ),0,'L' );
			//$pdf->Ln( $height+10);
			//confirm online
			$text = esc_html( __( 'Click this link to confirm online','church-admin') );
			//$link=site_url().'?confirm='.urldecode(church_admin_encode( $row->last_name) ).'/'.(int)$row->people_id;
			//use new style link from 2021-04-30
			$link=site_url().'/?confirm_email='.md5( $row->email).'&people_id='.md5( $row->people_id);
			$pdf->SetFont('Arial','U',12);
			$pdf->Cell(0,7,$text,0,1,'L',NULL,$link);
			$pdf->Ln(5);

			//person's entry
			$people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$row->household_id.'" ORDER BY people_order ASC');
			if(!empty( $people) )
			{
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(0,7,esc_html( __( 'People in your household', 'church-admin' ) ),0,1,'L');
				$pdf->Cell(50,7,esc_html( __( "Name",'church-admin') ),1,0,'L');
				$pdf->Cell(30,7,esc_html( __( "Mobile",'church-admin') ),1,0,'L');
				$pdf->Cell(75,7,esc_html( __( "Email",'church-admin') ),1,0,'L');
				$pdf->Cell(30,7,esc_html( __( "Date of Birth",'church-admin') ),1,1,'L');
				$pdf->SetFont('Arial','',12);
				$text='';
				foreach( $people AS $person)
				{
					$name=array_filter(array( $person->first_name,$person->middle_name,$person->last_name) );
					$mobile=!empty( $person->mobile)?esc_html( $person->mobile):"";
					$email=!empty( $person->email)?esc_html( $person->email):"";

					if(!empty( $person->date_of_birth)&&$person->date_of_birth!=NULL)
					{$dob=mysql2date(get_option('date_format'),$person->date_of_birth);}else{$dob="";}


					$pdf->Cell(50,7,urldecode(church_admin_encode(implode(' ',$name) )),1,0,'L');
					$pdf->Cell(30,7,$mobile,1,0,'L');
					$pdf->Cell(75,7,$email,1,0,'L');
					$pdf->Cell(30,7,$dob,1,1,'L');
				}

			}
			$pdf->Ln(5);
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(0,7,urldecode(church_admin_encode(esc_html( __( 'Address details','church-admin') ) )),0,1,'L');
			$pdf->SetFont('Arial','',12);
			if(!empty( $row->address) )$pdf->Cell(0,7,urldecode(church_admin_encode( $row->address) ),0,1,'L');
			if(!empty( $row->phone) )$pdf->Cell(0,7,esc_html( $row->phone),0,1,'L');

			//form confirmation...
			$pdf->Ln(10);
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(0,7,urldecode(church_admin_encode(esc_html( __( 'Confirmation of personal data use','church-admin') ) )),0,1,'L');
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(7,7,'',1);
			$text=urldecode(church_admin_encode(esc_html( __( 'Please send email','church-admin')  )));
			$pdf->Cell(0,7,$text,0,1,'L');
			$pdf->Ln(2);
			$pdf->Cell(7,7,'',1);
			$text=urldecode(church_admin_encode(esc_html( __( 'Please send SMS','church-admin') )));
			$pdf->Cell(0,7,$text,0,1,'L');
			$pdf->Ln(2);
			$pdf->Cell(7,7,'',1);
			$text=urldecode(church_admin_encode(esc_html( __( 'Please send mail','church-admin') )) ) ;
			$pdf->Cell(0,7,$text,0,1,'L');
			$pdf->Ln(2);
			$pdf->Cell(7,7,'',1);
			$text=urldecode(church_admin_encode(esc_html( __( "Please don't publish my address details on the password protected website",'church-admin') ) ));
			$pdf->Cell(0,7,$text,0,1,'L');
			$pdf->Ln(5);
			$pdf->Cell(75,7,'','B');
			$pdf->Cell(0,7,urldecode(church_admin_encode(esc_html( __( 'Signature','church-admin') ))),0,1,'L');
			$pdf->Ln(10);
			$pdf->Cell(75,7,'','B');
			$pdf->Cell(0,7,urldecode(church_admin_encode(esc_html( __( 'Date','church-admin') )) ),0,1,'L');


		}
			$pdf->Output();
	}else{echo'no people';}
}

function gdpr_confirm_everyone()
{
	if(!church_admin_level_check('Directory') ){
		wp_die(esc_html( __( 'You don\'t have permissions to do that','church-admin') ) );
	}
	global $wpdb;
	//$wpdb->show_errors();
	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET mail_send=1,email_send=1,news_send=1,sms_send=1,gdpr_reason="'.esc_sql( __( 'GDPR Confirmed by admin','church-admin') ).'" WHERE gdpr_reason IS NULL OR gdpr_reason=""');
	//add in people meta for news_send

	echo'<h2>GDPR</h2>';
	echo'<p>'.esc_html( __( 'That was very naughty. You have confirmed that your entire directory are happy to have personal data stored and be communicated with','church-admin') ).'</p>';
	//reset app address list cache
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
	update_option('church_admin_modified_app_content',time() );
}



function church_admin_bulk_geocode()
{
		global $wpdb;
	//$wpdb->show_errors;
    echo'<h2>'.esc_html( __( 'Batch geocoding','church-admin') ).'</h2>';
    if(!empty($_POST['google_api_key'])){
		$googleApi = church_admin_sanitize($_POST['google_api_key']);
		update_option('church_admin_google_api_key', $googleApi);

	}
	
	$googleApi=get_option('church_admin_google_api_key');
	if ( empty( $googleApi) )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html( __( "Please get a Google Maps API key first",'church-admin') ).'</h2>';
		echo'<p><a traget="_blank" href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __( 'Tutorial on getting an API key','church-admin') ).'</a></p>';
		echo'<form action="admin.php?page=church_admin%2Findex.php&action=bulk-geocode" method="POST">';
		wp_nonce_field('bulk-geocode');
		echo'<div class="church-admin-form-group"><label>'.esc_html('Google API Key','church-admin').'</label>';
		echo'<input class="church-admin-form-control" type="text" name="google_api_key"></div><p><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';
		echo'</div>';

		return;
	}
		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE address!=", , , ," AND address!=", , ," AND address!="" AND (geocoded=0 OR lat="" OR lng="") LIMIT 10');
		if(!empty( $_POST['batch_geocode'] ) )
		{
		
			if(!empty( $results) )
			{
				foreach( $results AS $row)
				{
						
					
						if(isset( $_POST['lat'.(int)$row->household_id] )&&isset( $_POST['lng'.(int)$row->household_id] ) )
						{
							$lat = !empty( $_POST['lat'.(int)$row->household_id] ) ? sanitize_text_field(stripslashes($_POST['lat'.(int)$row->household_id])) : null;
							$lng = !empty( $_POST['lng'.(int)$row->household_id] ) ? sanitize_text_field(stripslashes($_POST['lng'.(int)$row->household_id])) : null;
							$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET lat="'.esc_sql( $lat ).'", lng="'.esc_sql( $lng ).'" , geocoded=1 WHERE household_id="'.(int)$row->household_id.'"');
							
						}
				}
				echo'<div class="notice notice-success inline"><h2>'.esc_html( __( 'Address geocodes updated','church-admin') ).'</h2></div>';
				
			}
			//redo query to get next 10
			$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE address!=", , , ," AND address!=", , ," AND address!="" AND (geocoded=0 OR lat="" OR lng="") LIMIT 10');
		}
		

            


			if(!empty( $results) )
			{
                echo'<h2>'.esc_html( __( 'We can only batch geocode 10 addresses at a time, so this will keep going through the cycle until finished!','church-admin') ).'</h2>';
			echo'<form action="" method="post">';
			echo'<p><button class="button-primary btn btn-info" id="geocode_address">'.esc_html( __( 'Step 1 Click to batch geocode household addresses','church-admin') ).'</button></p>';
			echo '<p><input type="hidden" name="batch_geocode" value="TRUE" /><input type="submit" id="submit_batch_geocode" disabled="disabled" value="'.esc_html( __( 'Step 2 - Save batched geocode','church-admin') ).'" /></p>';
			echo'<div id="map" style="width:500px;height:500px"></div>';
            
				echo'<script>var beginLat = 51.50351129583287;var beginLng = -0.148193359375;</script>';
			
				foreach( $results AS $row)
				{
					echo '<p >'.esc_html( $row->address).'<input type="hidden" id="'.(int)$row->household_id.'" class="address" value="'.esc_html( $row->address).'" /></p>';
					echo esc_html( __( 'Latitude','church-admin') ).'<input type="text" id="lat'.(int)$row->household_id.'"   value="'.esc_html( $row->lat).'" name="lat'.(int)$row->household_id.'" /> '. esc_html( __( 'Longitude','church-admin') ).'<input type="text" value="'.esc_html( $row->lng).'" name="lng'.(int)$row->household_id.'" id="lng'.(int)$row->household_id.'" />';
				}
			}else{echo'<p>'.esc_html( __( 'No  households need geocoding','church-admin') ).'</p>';}
			echo'</form>';
    
}


function church_admin_bulk_not_private()
{
    global $wpdb;

	echo'<h2>'.esc_html( __( 'Make Everyone visible on the address list','church-admin') ).'</h2>';
	if(!current_user_can('manage_options') )return '<p>'.esc_html( __( 'Only site admins can do this','church-admin') ).'</p>';
	if(!empty( $_POST['sure'] ) )
	{
    	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET privacy=0');
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET show_me=1, privacy="'.esc_sql('a:4:{s:10:"show-email";i:1;s:9:"show-cell";i:1;s:13:"show-landline";i:1;s:12:"show-address";i:1;}').'"');
    	echo'<div class="notice notice-success notice-inline"><h2>'.esc_html( __( 'Everyone now set to show on the directory','church-admin') ).'</h2></div>';
	}
	else
	{
		echo'<form action="" method="post"><p><strong>'.esc_html( __( "Are you sure you want to override people's privacy choice?",'church-admin') ).'</strong><br><input type="hidden" name="sure" value="yes" /><input type="submit"  class="button-secondary" value="'.esc_html( __( 'Yes, make everyone visible','church-admin') ).'" /></p></form>';
	}
}


function church_admin_potential_duplicates()
{
	echo'<h2>'.esc_html( __( 'Check for duplicate entries','church-admin') ).'</h2>';
    global $wpdb;
    $nameResults=$wpdb->get_results('SELECT CONCAT_WS(" ",first_name,last_name) AS name, COUNT(CONCAT_WS(" ",first_name,last_name) ) AS count FROM '.$wpdb->prefix.'church_admin_people GROUP BY CONCAT_WS(" ",first_name,last_name) HAVING COUNT(name)>1');
    if(!empty( $nameResults) )
    {
        echo'<h3>'.esc_html( __( 'Here are some potential duplicate entries','church-admin') ).'</h3>';
        echo '<p><strong>'.esc_html( sprintf( __( '%1$s people duplicated', 'church-admin' ), $wpdb->num_rows) ).'</strong></p>';
        echo'<p>'.esc_html( __( 'Carefully check the results to see which one to keep!','church-admin') ).'</p>';
        foreach( $nameResults AS $nameRow)
        {
            echo'<h2>'.esc_html( $nameRow->name).' x'.intval( $nameRow->count).'</h2>';
            $householdResults=$wpdb->get_results('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND CONCAT_WS(" ",a.first_name,a.last_name)="'.esc_sql( $nameRow->name).'"');
            if(!empty( $householdResults) )
            {
				echo'<table class="widefat"><thead><tr><th>'.esc_html(__('Delete','church-admin')).'</th><th>'.esc_html(__('Name','church-admin')).'</th><th>'.esc_html(__('Cell','church-admin')).'</th><th>'.esc_html(__('Address','church-admin')).'</th><th>'.esc_html(__('Others in household','church-admin')).'</th></tr></thead><tbody>';
				foreach( $householdResults AS $householdRow)
            	{
					$others=array();
					$othersRes=$wpdb->get_results('SELECT *,CONCAT_WS(" ",first_name,prefix,last_name) AS name FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$householdRow->household_id.'" AND people_id!="'.(int)$householdRow->people_id.'" ORDER BY people_order ASC');
					if(!empty($othersRes)){
						foreach($othersRes AS $othersRow){
							$others[]=$othersRow->name;
						}
						
					}
					$othersOutput = !empty($others) ? implode(',',$others) : '';
               		$delete = '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=delete_people&people_id='.(int)$householdRow->people_id,'delete_people').'">'.esc_html(__('Delete','church-admin')).'</a>';
					$address = !empty($householdRow->address) ? $householdRow->address :'';
					$name = church_admin_formatted_name($householdRow);
					$cell = !empty($householdRow->mobile) ? $householdRow->mobile :'';
					echo'<tr><td>'.$delete.'</td><td>'.esc_html($name).'</td><td>'.esc_html($mobile).'</td><td>'.esc_html($address).'</td><td>'.esc_html($othersOutput).'</td></tr>';
       			}	
				echo'</tbody></table>';
			}
            
        }
        
    }
	else{
		echo'<p>'.__('There are no duplicates','church-admin').'</p>';
	}
    
}




function church_admin_new_household_display( $household_id)
{

	/******************************************************
	* 2022-10-29 
	* This function also used by subscriber level household 
	* members for their household, so added in some level 
	* checking where needed
    ********************************************************/

	global $wpdb,$people_type;
	$member_type=church_admin_member_types_array();
	$sms = get_option('church_admin_sms_provider');
	/******************************************
     * handle move household to member type
     *****************************************/
	if(!empty( $_POST['move_member_type'] ) )
	{
        //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        $move_household_id = !empty($_POST['move_household_id'])?sanitize_text_field(stripslashes($_POST['move_household_id'])):null;
        $new_member_type_id = !empty($_POST['new_member_type_id'])?sanitize_text_field(stripslashes($_POST['new_member_type_id'])):null;

        //validate
        $validated = TRUE;
        if(empty($move_household_id)){$validated = FALSE;}
        if(empty($new_member_type_id) || empty($member_type[$new_member_type_id])){$validated = FALSE;}
        if(!church_admin_int_check($new_member_type_id)){$validated = FALSE;}

		if(!empty($validated) )
		{
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET member_type_id="'.(int)$new_member_type_id.'" WHERE household_id="'.(int)$move_household_id.'"');
			
			echo '<div class="notice notice-success"><h2>'.esc_html( sprintf( __( 'Household member type updated to %1$s','church-admin' ),$member_type[$new_member_type_id]  )).'</h2></div>';
		}
		
	}
	/*************************
	 * Handle head of household
	 ***************************/
	if(!empty( $_POST['change_head_of_household'] ) && church_admin_int_check( $_POST['change_head_of_household'] ) )
	{
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=0 WHERE household_id="'.(int)$household_id.'"');
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET head_of_household=1 WHERE people_id="'.(int)$_POST['change_head_of_household'].'" AND household_id="'.(int)$household_id.'"');
		$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$_POST['change_head_of_household'].'"');
		echo '<div class="notice notice-success"><h2>'.esc_html( sprintf( __( 'Head of household updated to %1$s','church-admin' ) ,church_admin_formatted_name( $person) ) ).'</h2></div>';
	}
	/*****************************************
	 * 
	 * Handle add person from this screen
	 * 
	 * ***************************************/
	if(!empty( $_POST['add-person'] ) )
	{
		$sqlsafe=array();
		foreach( $_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(sanitize_text_field(stripslashes( $value ) ) );
		$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.$sqlsafe['first_name'].'" AND last_name="'.$sqlsafe['last_name'].'" AND email="'.$sqlsafe['email_address'].'" AND household_id="'.(int)$household_id.'" AND sex="'.$sqlsafe['sex'].'" AND people_type_id="'.$sqlsafe['people_type_id'].'"');
		if ( empty( $people_id) )
		{
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,mobile,email,household_id,people_type_id,sex,member_type_id,show_me,gdpr_reason,first_registered)VALUES("'.$sqlsafe['first_name'].'","'.$sqlsafe['last_name'].'","'.$sqlsafe['mobile'].'","'.$sqlsafe['email_address'].'","'.(int)$household_id.'","'.$sqlsafe['people_type_id'].'","'.$sqlsafe['sex'].'","'.$sqlsafe['member_type_id'].'",0,"'.esc_sql( __( 'Admin added','church-admin') ).'","'.esc_sql(wp_date('Y-m-d')).'")');
			$people_id=$wpdb->insert_id;
		}
		$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		//reset app address list cache
		delete_option('church_admin_app_address_cache');
		delete_option('church_admin_app_admin_address_cache');

		
		echo'<div class="notice notice-success"><h2>'.esc_html( sprintf( __(  '%1$s added', 'church-admin' ),  church_admin_formatted_name( $person )  ) ).'</h2></div>';
	}
	/*****************************************
	 * 
	 * Handle send SMS from this screen
	 * 
	 * ***************************************/
	if(!empty($sms) &&!empty($_POST['e164cell']) && !empty($_POST['sms-message'])){
		$message = church_admin_sanitize($_POST['sms-message']);
		$mobile = church_admin_sanitize($_POST['e164cell']);
		require_once(plugin_dir_path(__FILE__).'/sms.php');
		echo '<div class="notice notice-success"><h2>'.esc_html(__('Send SMS','church-admin')).'</h2>';
		church_admin_sms( $mobile,$message,TRUE);
		echo'</div>';
	}
    /**************************************************
     * Grab household data and abort if none found
     *************************************************/
    if ( empty( $household_id) ){
		echo esc_html( __( 'No household to display','church-admin') );
		return;
	}
    $household=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"');
    if ( empty( $household) ){
		echo esc_html( __( 'No household found from household_id','church-admin') );
		return;
	}
    $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY head_of_household DESC');
    if ( empty( $people) )
    {
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"');
        echo esc_html( __( 'household deleted as no-one in it!','church-admin') );
		return;
    }
	
	
    echo'<div class="church-admin-household">';
    /************************************************
     * Output Household Address details
     ***********************************************/
    echo'<div class="church-admin-household-title"><h2>'.esc_html( church_admin_directory_title_name( $people ) ).'</h2></div>';

	
	if(church_admin_level_check('Directory') )
	{
		/***********************************
		 * move people to same member type
		 * ********************************/
		$current_member_type = $wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id=".'.(int)$household_id.'." and head_of_household=1');
		
		
		echo'<div class="church-admin-household-move"><form action="admin.php?page=church_admin/index.php&amp;action=display-household&household_id='.(int)$household_id.'&section=people" method="POST" />';
		wp_nonce_field('display-household');
		echo'<input type="hidden" name="move_member_type" value="1" />';
		echo'<input type="hidden" name="move_household_id" value="'.(int)$household_id.'" />';
		echo'<p>'.esc_html( __( 'Change member type of whole household','church-admin') ).'<select name="new_member_type_id">';
		foreach( $member_type AS $key=>$value)
		{

			echo '<option value="'.esc_html( $key).'" '.selected($key,$current_member_type,FALSE ).'>'.esc_html( $value ).'</option>';
		}
		echo'</select>';
		echo'<input type="submit" class="button-secondary" value="'.esc_html( __( 'Move','church-admin') ).'"></p></form></div>';
		
		
	
		/*************************************
		 * Head of household
		 *************************************/
		echo'<div class="church-admin-household-move"><form action="admin.php?page=church_admin/index.php&amp;action=display-household&household_id='.(int)$household_id.'&section=people" method="POST" />';
		wp_nonce_field('display-household');
		echo'<p>'.esc_html( __( 'Update head of household','church-admin') ).'</label><select name="change_head_of_household">';
		foreach( $people AS $person)
		{
			echo'<option value="'.(int)$person->people_id.'" '.selected( $person->head_of_household,1,FALSE).'>' .esc_html( church_admin_formatted_name( $person ) ) .'</option>';
		}
		echo'</select>';
		echo'<input type="submit" class="button-secondary" value="'.esc_html( __( 'Save','church-admin') ).'"></p></form></div>';
	}
	/************************************************
     * Output Image
     ***********************************************/
    echo'<div class="church-admin-household-image ca-upload-area" data-nonce="'.wp_create_nonce("household-image-upload").'" data-which="household" data-id="'.(int)$household->household_id.'" id="uploadfile"><h3>'.esc_html( __( 'Household image','church-admin') ).'</h3>';
    if(!empty( $household->attachment_id) )
    {
        $household_image_attributes=wp_get_attachment_image_src( $household->attachment_id,'medium','' );
        if ( $household_image_attributes )
        {
            echo'<img id="household-image" src="'.esc_url( $household_image_attributes[0] ).'" width="'.(int)$household_image_attributes[1].'" height="'.(int)$household_image_attributes[2].'" class="rounded" alt="'.esc_html( __( 'Household image','church-admin') ).'" />';
        }
    }
    else
    {
        echo'<img id="household-image"  src="'.esc_url( plugins_url( '/', dirname(__FILE__) ) . 'images/household.svg' ) .'" width="300" height="200" class="rounded" alt="'.esc_html( __( 'Household image','church-admin') ).'" />';
    }
    echo '<br>'.esc_html( __( 'Drag and drop new image for household','church-admin') );
    echo '<span id="household-upload-message"></span>';

    echo'</div>';
    /************************************************
     * Output Household Address details
     ***********************************************/
    echo'<div class="church-admin-address-details">';
    echo '<h3>'.esc_html( __( 'Household address','church-admin') ).'</h3>';
    echo'<div class="church-admin-address church-admin-form-group" ><label>'.esc_html( __( 'Phone','church-admin') ).'</label><input type="phone" class="church-admin-form-control church-admin-editable" data-what="phone" data-id="'.(int)$household_id.'" id="phone" value="'.esc_html( $household->phone).'" /></div>';  
    echo'<div class="church-admin-address church-admin-form-group"><label>'.esc_html( __( 'Street Address','church-admin') ).'</label><input data-ID="'.(int)$household_id.'"  data-what="address" data-id="'.(int)$household_id.'"  id="address" class="church-admin-form-control church-admin-editable" type="text" value="'.esc_html( $household->address).'" data-ID="0" data-what="address" data-household-id="'.(int)$household_id.'"></div>';   
	echo'<div class="church-admin-address church-admin-form-group"><label>'.esc_html( __( 'Mailing Address','church-admin') ).'</label><input data-ID="'.(int)$household_id.'"  data-what="mailing-address" data-id="'.(int)$household_id.'"  id="mailing-address" class="church-admin-form-control church-admin-editable" type="text" value="'.esc_html( $household->mailing_address).'" data-ID="0" data-what="mailing_address" data-household-id="'.(int)$household_id.'"></div>'; 
	echo'<p><a style="text:decoration:none" title="'.esc_html( __( 'Download Vcard','church-admin') ).'" href="'.wp_nonce_url(home_url().'/?ca_download=vcf&amp;id='.(int)$person->household_id,$person->household_id).'"><span class="ca-dashicons dashicons dashicons-index-card"></span>'.esc_html( __( 'Household VCF','church-admin') ).'</a></p>';
    $key=get_option('church_admin_google_api_key');
    
    $wa=get_option('church_admin_show_wedding_anniversary');
	if(!empty($wa)){
		$wa_date = !empty($household->wedding_anniversary)?$household->wedding_anniversary:null;
		echo'<div class="church-admin-address church-admin-form-group"><label>'.esc_html( __( 'Wedding Anniversary','church-admin') ).'</label>';
		echo church_admin_date_picker( $wa_date,'wedding_anniversary',FALSE,'1910',NULL,'wedding_anniversary','wedding_anniversary',FALSE,'wedding_anniversary',$household->household_id,$household->household_id);
		echo'</div>'; 
	}
    
    if(!empty( $key) )
    {
        echo'<div id="map" style="width:500px;height:300px;margin-bottom:20px"></div>';
		echo'<p><button id="geocode_address" class="button-primary btn btn-info">'.esc_html( __( 'Update map','church-admin') ).'</button></p>';
		echo'<script >var ca_method="update-directory";var ID='.(int)$household_id.'; var nonce="'.wp_create_nonce('update-directory').'";var beginLat=';
		if(!empty( $household->lat) ) {echo esc_html($household->lat).';';}else {echo '0;';}
		if(!empty( $household->lng) ) 
		{
			echo 'var beginLng='.esc_html( $household->lng ).';var zoom=17;';
		}else 
		{
			echo'var beginLng=0;var zoom=0;';
		}
		echo 'console.log("ID "+ ID)';
		echo';</script>';
    }
    else
    {
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=settings#directory-settings','settings').'">'.esc_html( __( 'To use mapping features, please set up a Google Maps API key','church-admin') ).'</a></p>';
    }
    //custom fields in address section
	$custom_fields=church_admin_get_custom_fields();
	if(!empty( $custom_fields) )
	{
		
		foreach( $custom_fields AS $id=>$field)
		{
			if( $field['section']!='household')continue;
			$dataField='';
			$dataField=$wpdb->get_var('SELECT `data` FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE section="household" AND household_id="'.(int)$household->household_id.'" AND custom_id="'.(int)$id.'"');
			//church_admin_debug($wpdb->last_query);
			church_admin_debug('Custom: '.$field['name']);
			//church_admin_debug($dataField);
			echo'<div class="church-admin-address church-admin-form-group" ><label>'.esc_html( $field['name'] ).'</label>';
			switch( $field['type'] )
			{
				case 'boolean':
					echo'<input type="radio" data-what="household-custom" data-id="'.(int)$household->household_id.'" data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable" name="custom-'.(int)$id.'" value="1" ';
					if (!empty( $dataField))echo ' checked="checked" ';
					echo '>'.esc_html( __( 'Yes','church-admin') ).'<br> <input type="radio"  data-id="'.(int)$household->household_id.'"  data-what="household-custom" data-ID="'.(int)$household->household_id.'" data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable" value="0" name="custom-'.(int)$id.'" ';
					if (empty( $dataField)) echo  'checked="checked" ';
					echo '>'.esc_html( __( 'No','church-admin') );
					break;
				case'text':
					echo '<input type="text"  data-what="household-custom" data-id="'.(int)$household->household_id.'"  data-custom-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable"  name="custom-'.(int)$id.'" ';
					if(!empty( $thisHouseholdCustomData->data) || isset( $field['default'] ) )echo ' value="'.esc_html( $dataField).'"';
					echo '/>';
				break;
				case'date':
					
					echo church_admin_date_picker( $dataField,'custom-'.(int)$id,FALSE,1910,date('Y'),'custom-'.(int)$id,'custom-'.(int)$id,FALSE,'household-custom',(int)$household->household_id,(int)$id);
				
				break;
				case 'checkbox':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						echo '<div class="checkbox"><label><input type="checkbox" data-what="household-custom" data-id="'.(int)$household->household_id.'"  data-custom-id="'.(int)$id.'"  data-type="checkbox" class="church-admin-editable custom-'.(int)$id.'" name="custom-'.(int)$id.'[]" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && is_array($dataField) && in_array($options[$y],$dataField) ){echo ' checked="checked" ';}
						
						echo '> '.esc_html($options[$y]).'</label></div>';
					}
				break;
				case 'radio':
					$options = maybe_unserialize($field['options']);
				
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						echo '<div class="checkbox"><label><input type="radio" data-id="'.(int)$household->household_id.'" data-what="household-custom"  data-type="radio" data-custom-id="'.(int)$id.'" class="church-admin-editable"  name="custom-'.(int)$id.'" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && $options[$y] == $dataField) {echo ' checked="checked" ';}
						echo '> '.esc_html($options[$y]).'</label></div>';
					}
				break;	
				case 'select':
					$options = maybe_unserialize($field['options']);
					if(!empty($dataField))$dataField = maybe_unserialize($dataField);
					if(empty($options)){break;}
					echo '<select name="custom-'.(int)$id.'" class="church-admin-form-control church-admin-editable" data-id="'.(int)$household->household_id.'" data-what="household-custom"  data-type="radio" data-custom-id="'.(int)$id.'"><option>'.esc_html( __( 'Choose' , 'church-admin' ) ) .'</option>';
					for ($y=0;$y<count($options);$y++){
						echo '<option value="'.esc_attr($options[$y]).'" '.selected($options[$y],$dataField,FALSE).' data-what="household-custom" data-type="select" data-custom-id="'.(int)$id.'"> '.esc_html($options[$y]).'</option>';
					}
					echo '</select>';
				break;
			}
			echo '</div>';
			
		}

	}
    
    echo'</div>';
    /*************************************
     * People
     ************************************/
	$member_type=church_admin_member_types_array();
	
    foreach ( $people as $person) 
	{
        echo'<div class="church-admin-person-details">';
      
        $name=church_admin_formatted_name( $person);
		$head='';
		if(!empty($person->head_of_household)){
			$head = ' ('. __('Head of household','church-admin') .')';
		}
        echo '<h3>'.esc_html( $name.$head ).'</h3>';
		
        /************************************************
         * Output Image
         ***********************************************/
        echo'<div class="church-admin-people-image ca-upload-area" data-which="people" data-id="'.(int)$person->people_id.'" data-nonce="'.wp_create_nonce("people-image-upload").'" id="uploadfile">';
        if(!empty( $person->attachment_id) )
        {
            $person_image_attributes=wp_get_attachment_image_src( $person->attachment_id,'medium','' );
            if ( $person_image_attributes )
            {
                echo'<img id="people-image'.(int)$person->people_id.'" src="'.esc_url( $person_image_attributes[0] ).'" width="'.(int)$person_image_attributes[1].'" height="'.(int)$person_image_attributes[2].'" class="rounded" alt="'.esc_html( $name).'" />';
            }else
            {
                //image not available although attachment id is saved.
                if(isset( $person->sex) &&$person->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
                echo'<img id="people-image'.(int)$person->people_id.'"  src="'.esc_url( plugins_url('/', dirname(__FILE__) ) . 'images/'.$image ).'" width="300" height="200" class="rounded" alt="'.esc_html( $name).'" />';
            }
			echo'<input class="attachment_id" type="hidden" name="attachment_id" value="'.(int)$person->attachment_id.'" />';
        }
        else
        {
            if(isset( $person->sex) &&$person->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
            
            echo'<img id="people-image'.(int)$person->people_id.'"  src="'.esc_url( plugins_url('/', dirname(__FILE__) ) . 'images/'.$image ).'" width="300" height="200" class="rounded" alt="'.esc_html( $name).'" />';
			echo'<input class="attachment_id" type="hidden" name="attachment_id" />';
        }
      
        echo '<span id="drag-message'.(int)$person->people_id.'">'.esc_html( sprintf( __( 'Drag and drop new image for %1$s','church-admin' ) ,$name) ).'</span>';
        echo'</div>';

		/**********************
		 * Active/Inactive
		 *********************/
		if(!empty( $person->active) )  {$activate=__('Active','church-admin');}else{$activate=__('Inactive','church-admin');}
		echo'<p "><span class="button-secondary activate ca-active" id="active-'.(int)$person->people_id.'">'.$activate.'</span> </p>';
			
		echo'<script>jQuery(document).ready(function($){
			$("body").on("click",".activate",function()  {
				console.log("Toggle active");
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
					console.log(data.status)
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
		});</script>';



       	//other details
        echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Email', 'church-admin' ) ).'</label><input type="email" name="email" id="email'.(int)$person->people_id.'" class="church-admin-form-control church-admin-editable" data-what="email" data-ID="'.(int)$person->people_id.'" data-id="'.(int)$person->people_id.'" value="'.esc_html( $person->email).'" /></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Cell', 'church-admin' ) ).'</label><input type="phone" id="cell'.(int)$person->people_id.'" name="cell" class="church-admin-form-control church-admin-editable" id="cell" data-what="cell" data-ID="'.(int)$person->people_id.'" data-id="'.(int)$person->people_id.'" value="'.esc_html( $person->mobile).'" /></div>';
		//people type
		echo'<div class="church-admin-form-group"><label for="people_type_id">'.esc_html( __( 'Person type','church-admin') ).'</label><select class="people_type_id church-admin-form-control church-admin-editable" data-id="'.(int)$person->people_id.'" data-what="people_type_id" name="people_type_id" id="mt'.(int)$person->people_id.'">';
		foreach( $people_type AS $id => $type ){
			echo '<option value="'.(int)$id.'" '.selected( $person->people_type_id,$id,FALSE).'>'.esc_html( $type ).'</option>'."\r\n";
		}
		echo'</select></div>';
		//member type
		if(church_admin_level_check('Directory') )
		{
			if(!empty( $member_type) )
			{
				$current=!empty( $person->member_type_id)?(int)$person->member_type_id:NULL;
				echo '<div class="church-admin-form-group"><label for="member_type_id">'.esc_html( __( 'Member Type','church-admin') ).'</label><select class="church-admin-form-control church-admin-editable" data-id="'.(int)$person->people_id.'" data-what="member_type_id" name="member_type_id" id="mt'.(int)$person->people_id.'">';
				foreach( $member_type AS $id=>$membertype)
				{
					echo '<option value="'.(int)$id.'" '.selected( $id,$current,FALSE).'>'.esc_html( $membertype ).'</option>';
				}
				echo'</select></div>';
			}


			echo '<p>'.esc_html( __( 'User account','church-admin') ).': '.church_admin_user_check( $person,FALSE).'</p>';
			echo'<p><a style="text-decoration:none" title="'.esc_html( __( 'Download Vcard','church-admin') ).'" href="'.wp_nonce_url(home_url().'/?ca_download=vcf-person&amp;id='.(int)$person->people_id,(int)$person->people_id).'"><span class="ca-dashicons dashicons dashicons-index-card"></span>'.esc_html( __( 'Personal VCF','church-admin') ).'</a></p>';
			//edit button
			echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_people&amp;people_id='.(int)$person->people_id.'&amp;household_id='.(int)$household_id,'edit_people').'">'.esc_html( __( 'View and edit other attributes','church-admin') ).'</a></p>';
			//move person
			echo'<p><a class="button-secondary" onclick="return confirm(\''.esc_html(sprintf( __( 'Are you sure you want to move %1$s','church-admin' ) ,church_admin_formatted_name( $person) ) ) .'\')" href="'.esc_url( wp_nonce_url( 'admin.php?page=church_admin/index.php&amp;action=move-person&amp;people_id='.(int)$person->people_id,'move-person' ) ).'">'.esc_html( __( 'Move to different household','church-admin') ).'</a></p>';
		}
		//delete person
		echo'<p><a  class="button-secondary"   onclick="return confirm(\''.esc_html(sprintf(__( 'Are you sure you want to delete %1$s','church-admin' ) ,church_admin_formatted_name( $person) )).'\')"  href="'.esc_url( wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_people&amp;household_id='.(int)$household_id.'&amp;people_id='.(int)$person->people_id.'&amp;household_id='.(int)$household_id,'delete_people' ) ).'">'.esc_html( __( 'Delete','church-admin') ).'</a></p>';
        /*****************************************
		 * 
		 * Send SMS form
		 * 
		 * ***************************************/
		if(!empty($person->sms_send) &&!empty($person->e164cell)&&!empty($sms))
		{
			//send sms

			//put view household action in to form to make sure it goes back to correct page!
			echo '<form action="admin.php?page=church_admin%2Findex.php&action=display-household&household_id='.(int)$person->household_id.'" method="POST">';
			wp_nonce_field('display-household');
			echo'<div class="church-admin-form-group"><label>'.esc_html(sprintf(__('Send SMS to %1$s','church-admin'),church_admin_formatted_name($person) ) ) .'</label>';
			echo'<textarea name="sms-message" class="church-admin-form-control" id="SMS'.(int)$person->people_id.'"></textarea><span id="chars'.(int)$person->people_id.'"></span></div>';
			echo'<p><input type="hidden" name="e164cell" value="'.esc_attr($person->e164cell).'" /><input type="submit" class="button-secondary" value="'.__('Send SMS','church-admin').'"></p></form>';
			echo '<script>jQuery(document).ready(function($) {
				var $txtArea = $("#SMS'.(int)$person->people_id.'");
				var $chars   = $("#chars'.(int)$person->people_id.'");
				var textMax = 160;
			  
				$chars.html(textMax + "/160");
			
				$txtArea.on("keyup", countChar);
				
				function countChar() {
					var textLength = $txtArea.val().length;
					var textRemaining = textMax - textLength;
					$chars.html(textRemaining + "/160");
				};
			});</script>';
		
		}
		echo'</div><!--Person-->';
    }
	//new person
	echo'<div class="church-admin-person-details">';
       echo'<form action="admin.php?page=church_admin/index.php&amp;action=display-household&amp;household_id='.(int)$household_id.'" method="post">';
	   wp_nonce_field('display-household');
        echo'<h3>'.esc_html( __( 'Add person','church-admin') ).'</h3>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'First name','church-admin') ).'</label><input type="text" name="first_name"  class="church-admin-form-control" /></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Last name','church-admin') ).'</label><input type="text" name="last_name"  class="church-admin-form-control" /></div>';
       
        echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Email', 'church-admin') ).'</label><input type="email" name="email_address"  class="church-admin-form-control" /></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Cell','church-admin') ).'</label><input type="phone"  name="mobile" class="church-admin-form-control" id="cell" /></div>';
        
		//people type
		echo'<div class="church-admin-form-group"><label for="people_type_id">'.esc_html( __( 'Person type','church-admin') ).'</label><select name="people_type_id"  class="people_type_id church-admin-form-control">';
		foreach( $people_type AS $id => $type ) {echo '<option value="'.(int)$id.'">'.esc_html( $type ).'</option>'."\r\n";}
		echo '</select></div>'."\r\n";
		//member type
		if(church_admin_level_check('Directory') )
		{
			$member_type=church_admin_member_types_array();
			if(!empty( $member_type) )
			{
				echo '<div class="church-admin-form-group"><label for="member_type_id">'.esc_html( __( 'Member Type','church-admin') ).'</label><select class="church-admin-form-control" name="member_type_id">';
				foreach( $member_type AS $id=>$membertype)
				{
					echo '<option value="'.(int)$id.'">'.esc_html( $membertype ).'</option>';
				}
				echo'</select></div>';
			}
		}else{echo '<input type="hidden" name="member_type_id" value=1/>';}
		//sex
		$gender=get_option('church_admin_gender');
		echo'<div class="church-admin-form-group"><label >'.esc_html( __( 'Gender','church-admin') ).'</label><select name="sex" class="sex church-admin-form-control" >';
		foreach( $gender AS $key => $value )  { echo '<option value="'.esc_html( $key).'" >'.esc_html( $value).'</option>';}
		echo'</select></div>'."\r\n";
		//edit button
		echo'<p><input type=hidden name="add-person" value="y" /><input class="button-primary" type="submit" value="'.esc_html( __( 'Add person','church-admin') ).'" /></p></form>';
		echo'</div><!--Person-->';


    //end of household details...
    echo '</div>';
    echo '<script>
    
        jQuery(document).ready(function( $)  {
            $("body").on("change",".church-admin-editable",function()  {
				console.log("editable fired");
                var what=$(this).data("what");
				var type = $(this).data("type")
				var customID=$(this).data("custom-id");
                var id=$(this).data("id");
                var div = $(this).attr("id");
                var value=$(this).val();
				if(what==="member_type_id") value=$("option:selected", this).val();
				if(type==="select")value=$("option:selected", this).val();
				if(type==="radio"){
					var selected = $(".custom-"+customID+" input[type=radio]:checked");
								if (selected.length > 0) {
									value = selected.val();
								}
				}
				if(type==="checkbox") {
					console.log("Analysing household custom checkbox"+customID)
					let a = [];
						$(".custom-"+customID+":checked").each(function() {
							console.log("checked value = "+$(this).val());
							a.push($(this).val());
						});
					value=a;
				}
				
                console.log("Value");
				console.log(value);
               var data= {"action":"church_admin","method":"update-directory","what":what,"id":id,"custom-id":customID,"nonce":"'.wp_create_nonce('update-directory').'","value":value,"div":div};
                console.log(data);
                $.getJSON({
                    url: ajaxurl,
                    type: "post",
                    data:  data,
                    success: function(response) {
                        console.log(response);
							if(response.div)
							{
								$("#"+response.div).val();
								$("#"+response.div).append("'.esc_html( __( 'Updated','church-admin') ).'");
							}
							if(response.mtout)
							{	
								$("#mt"+response.div).html(response.mtout);
							}
                        }
                });
                
            });
			$(".ca_connect_user").click(function() {
				var people_id=$(this).attr("data-peopleid");
				var data = {
				"action": "church_admin",
				"method": "connect_user",
				"people_id": people_id,
				"user_id": $(this).attr("data-userid"),
				"nonce": "'.wp_create_nonce("connect_user").'",
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
			$(".ca_create_user").click(function() {
				var people_id=$(this).attr("data-peopleid");
				var data = {
				"action": "church_admin",
				"method": "create_user",
				"people_id": $(this).attr("data-peopleid"),
				"nonce": "'.wp_create_nonce("create_user").'",
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
        });
    </script>';
}

function church_admin_merge_people( $person1_id,$person2_id)
{

	church_admin_debug('****** church_admin_merge_people ******');
	church_admin_debug(func_get_args());
	echo'<h2>'.esc_html( __( "Merge two people's records",'church-admin') ).'</h2>'."\r\n";
	echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/merge-duplicate-entries/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
	

	global $wpdb;
	$data1=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person1_id.'"');
	if ( empty( $data1) )return esc_html( __( 'No data for first person','church-admin') );
	$data2=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person2_id.'"');
	if ( empty( $data2) )return esc_html( __( 'No data for second person','church-admin') );
	$user=wp_get_current_user();
	$member_type=church_admin_member_types_array();
	$people_type=get_option('church_admin_people_type');
	$marital_status=get_option('church_admin_marital_status');
	$personFields=array(
						'attachment_id'=>array('type'=>'attachment_id','display'=>esc_html( __( "Photo",'church-admin') ) ),	
						'first_name'=>array('type'=>'text','display'=>esc_html( __( "First name",'church-admin') ) ),
						'middle_name'=>array('type'=>'text','display'=>esc_html( __( "Middle name",'church-admin') )),
						'last_name'=>array('type'=>'text','display'=>esc_html( __( "Last name",'church-admin') )),
						'head_of_household'=>array('type'=>'boolean','display'=>esc_html( __( "Head of household ",'church-admin') )),
						'date_of_birth'=>array('type'=>'date','display'=>esc_html( __( "Date of birth",'church-admin') ) ),
						'member_type_id'=>array('type'=>'member_type','display'=>esc_html( __( 'Member type') ) ),
						'people_type_id'=>array('type'=>'people_type','display'=>esc_html( __( 'Person type') ) ),
						'sex'=>array('type'=>'sex','display'=>esc_html( __( 'Gender','church-admin') ) ),
						'mobile'=>array('type'=>'text','display'=>esc_html( __( "Cellphone",'church-admin') ) ),
						'email'=>array('type'=>'text','display'=>esc_html( __( 'Email','church-admin') ) ),
						'marital_status'=>array('type'=>'marital_status','display'=>esc_html( __( 'Marital status','church-admin') ) ),
						'active'=>array('type'=>'boolean','display'=>esc_html( __( 'Active','church-admin') ) ),
						'site_id'=>array('type'=>'site','display'=>esc_html( __( 'Site','church-admin') ) ),
						'email_send'=>array('type'=>'boolean','display'=>esc_html( __( 'Email send?','church-admin') ) ),
						'news_send'=>array('type'=>'boolean','display'=>esc_html( __( 'Blog posts emails send?','church-admin') ) ),
						'sms_send'=>array('type'=>'boolean','display'=>esc_html( __( 'SMS send','church-admin') ) ),
						'mail_send'=>array('type'=>'boolean','display'=>esc_html( __( 'Mail send','church-admin') ) ),
						'gdpr_reason'=>array('type'=>'boolean','display'=>esc_html( __( 'GDPR reason','church-admin') ) ),
						
						'photo_permission'=>array('type'=>'boolean','display'=>esc_html( __( 'Photo permission?','church-admin') ) ),
						'phone_calls'=>array('type'=>'boolean','display'=>esc_html( __( 'Phone calls?','church-admin') ) ),
						'gift_aid'=>array('type'=>'boolean','display'=>esc_html( __( 'UK gift Aid','church-admin') ) ),
						'show_me'=>array('type'=>'boolean','display'=>esc_html( __( 'Show in directory','church-admin') ) )
						
					);
	
	if ( empty( $_POST['merge-people-submitted'] ) )
	{
		church_admin_debug('Initial merge form');
		$data1=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person1_id.'"');
		if ( empty( $data1) )return esc_html( __( 'No data for first person','church-admin') );
		$data2=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person2_id.'"');
		if ( empty( $data2) )return esc_html( __( 'No data for second person','church-admin') );

		//form

		
		echo'<p>'.esc_html( __( 'The second person will be deleted, so for each row choose which column to keep, or click cancel','church-admin') ).'</p>'."\r\n";
		echo'<form action="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=merge-people','merge-people').'" method="POST">'."\r\n";
		echo'<input type="hidden" name="people_id1" value="'.(int)$person1_id.'">';
		echo'<input type="hidden" name="people_id2" value="'.(int)$person2_id.'">';
		
		$theader='<tr><th>'.esc_html( __( 'Field','church-admin') ).'</th><th>'.esc_html( __( 'First person','church-admin') ).' - '. esc_html( church_admin_formatted_name( $data1 ) ).'</th><th>'.esc_html( __( 'Second person','church-admin') ).' - '. esc_html( church_admin_formatted_name( $data2 ) ).'</th></tr>'."\r\n";
		
		echo'<table class="form-table bordered striped">'."\r\n";
		echo'<thead>'.$theader.'</thead>'."\r\n";
		echo'<tbody>'."\r\n";
		foreach( $personFields AS $fieldName=>$fieldData)
		{
			
			switch( $fieldData['type'] )
			{
				case 'attachment_id':
					if(!empty( $data1->attachment_id) )
					{
						$first= esc_url( wp_get_attachment_image( $data1->attachment_id,'medium',NULL ) );
					}else
					{
						if(isset( $data1->sex) &&$data1->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
                		$first='<img  src="'.esc_url( plugins_url( '/', dirname(__FILE__) ) . 'images/'.$image ) .'" width="300" height="200" class="rounded" />';
					}
					if(!empty( $data2->attachment_id) )
					{
						$second= esc_url( wp_get_attachment_image( $data2->attachment_id,'medium',NULL ) );
					}else
					{
						if(isset( $data2->sex) &&$data2->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
                		$second='<img   src="'.plugins_url('/', dirname(__FILE__) ) . 'images/'.$image.'" width="300" height="200" class="rounded" />';
					}
				break;
				case 'text':
					$first=esc_html( $data1->$fieldName);
					$second=esc_html( $data2->$fieldName);
				break;
				case 'boolean':
					if(!empty( $data1->$fieldName) )  {$first=esc_html( __( "Yes",'church-admin') );}else{$first=esc_html( __( "No",'church-admin') );}
					if(!empty( $data2->$fieldName) )  {$second=esc_html( __( "Yes",'church-admin') );}else{$second=esc_html( __( "No",'church-admin') );}
				break;
				case 'sex':
					if(!empty( $data1->$fieldName) )  {$first=esc_html( __( "Male",'church-admin') );}else{$first=esc_html( __( "Female",'church-admin') );}
					if(!empty( $data2->$fieldName) )  {$second=esc_html( __( "Male",'church-admin') );}else{$second=esc_html( __( "Female",'church-admin') );}
				break;
				case 'marital_status':
					if(!empty( $data1->$fieldName)&&$data1->$fieldName=='N/A')$data1->$fieldName=0;
					if(!empty( $data2->$fieldName)&&$data2->$fieldName=='N/A')$data2->$fieldName=0;
					$first=esc_html( $marital_status[$data1->$fieldName] );
					$second=esc_html( $marital_status[$data2->$fieldName] );
				break;
				
				case 'member_type':
					$first=esc_html( $member_type[$data1->$fieldName] );
					$second=esc_html( $member_type[$data2->$fieldName] );
				break;
				case 'people_type':
					$first=esc_html( $people_type[$data1->$fieldName] );
					$second=esc_html( $people_type[$data2->$fieldName] );
				break;
				case 'date':
					if(!empty( $data1->$fieldName)&& $data1->$fieldName!="0000-00-00")$first=mysql2date(get_option('date_format'),$data1->$fieldName);
					if(!empty( $data2->$fieldName)&& $data2->$fieldName!="0000-00-00")$second=mysql2date(get_option('date_format'),$data2->$fieldName);
				break;
			}
			echo'<tr><th scope="row">'.esc_html( $fieldData['display'] ).'</th>';
			echo'<td><input type="radio" name="'.esc_html( $fieldName).'" checked="checked" value="'.esc_html( $data1->$fieldName).'" />'.$first.'</td>';
			echo'<td><input type="radio" name="'.esc_html( $fieldName).'" value="'.esc_html( $data2->$fieldName).'" />'.$second.'</td>';
			echo'</tr>'."\r\n";
		}
			//household - other people
			echo'<tr><th scope="row">'._('Other people in household').'</th>';
			$first=$second=array();
			$firstHousehold=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$data1->household_id.'" AND people_id!="'.(int)$data1->people_id.'"');
			if(!empty( $firstHousehold) )
			{
				foreach( $firstHousehold AS $fh)$first[]=church_admin_formatted_name( $fh);
			}
			$secondHousehold=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$data2->household_id.'" AND people_id!="'.(int)$data2->people_id.'"');
			if(!empty( $secondHousehold) )
			{
				foreach( $secondHousehold AS $sh)$second[]=church_admin_formatted_name( $sh);
			}
			echo'<td><input type="radio" name="household_id" value="'.(int)$data1->household_id.'" />'.implode(", ",$first).'</td>';
			echo'<td><input type="radio" name="household_id" value="'.(int)$data2->household_id.'" />'.implode(", ",$second).'</td>';
			echo'</tr>'."\r\n";
			/**************************
			 * Other household data
			 * ************************/

			$household1=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$data1->household_id.'"');
			$household2=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$data2->household_id.'"');
			//Houehold Photo
			//Address
			echo'<tr><th scope="row">'.esc_html( __( 'Address','church-admin') ).'</th>';
			echo'<td><input type="radio" name="address" checked="checked" value="'.esc_html( $household1->address).'" />'.esc_html( $household1->address).'</td>';
			echo'<td><input type="radio" name="address" value="'.esc_html( $household2->address).'" />'.esc_html( $household2->address).'</td>';
			echo'</tr>'."\r\n";
			//Phone
			echo'<tr><th scope="row">'.esc_html( __( 'Phone','church-admin') ).'</th>';
			echo'<td><input type="radio" name="phone" checked="checked" value="'.esc_html( $household1->phone).'" />'.esc_html( $household1->phone).'</td>';
			echo'<td><input type="radio" name="phone" value="'.esc_html( $household2->phone).'" />'.esc_html( $household2->phone).'</td>';
			echo'</tr>'."\r\n";

		echo'<tfoot>'.$theader.'</tfoot>'."\r\n";
		echo'</tbody></table>';
		echo'<p><input type="hidden" name="merge-people-submitted" value="'.wp_create_nonce("merge-people-submitted").'" /><input type="submit" value="'.esc_html( __( 'Merge','church-admin') ) .'" class="button-primary" />&nbsp;<a href="admin.php?page=church_admin/index.php" class="button-secondary">'.esc_html( __( 'Cancel','church-admin') ).'</a></p></form>'."\r\n";


	}
	else
	{
		church_admin_debug('Merge attempt');
		church_admin_debug(print_r( $_POST,TRUE) );
		//delete USER for person2_id
		$user_id2=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person2_id.'"');
		if(!empty( $user_id) && $user_id!=$user->ID && !user_can('manage_options',$user_id) && !count_user_posts($user_id) )
		{
			require_once(ABSPATH.'wp-admin/includes/user.php' );
			wp_delete_user( $user_id );
		}
		//delete $wpdb->prefix.'church_admin_household' record for person2_id if only one person in household
		$numberOfPeople=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$data2->household_id.'"');
		if( $numberOfPeople==1)$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$data2->household_id.'"');
		//delete $wpdb->prefix.'church_admin_people' record for person2_id
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$person2_id.'"');
		//delete $wpdb->prefix.'church_admin_people_meta' records for person2_id
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$person2_id.'"');
		$fieldQuery=array();
		
		
		foreach( $personFields AS $key=>$data)
		{
				if(isset( $_POST[$key] ) )
				{
					$fieldQuery[$key]=' '.esc_sql( $key).'="'.esc_sql( sanitize_text_field(stripslashes($_POST[$key])) ).'"';
				}
				else $fieldQuery[$key]=' '.esc_sql( $key).'=""';
				
		}
		if(defined('CA_DEBUG') )church_admin_debug(print_r( $fieldQuery,TRUE) );
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET '.implode(",",$fieldQuery).' WHERE people_id="'.(int)$person1_id.'"');
		if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET address="'.esc_sql(sanitize_text_field( stripslashes($_POST['address'] ) )).'",phone="'.esc_sql(sanitize_text_field( stripslashes($_POST['address'] ) ) ).'" WHERE household_id="'.(int)$data1->household_id.'"');

		echo '<div class="notice notice-success"><h2>'.esc_html( __( 'People records merged','church-admin') ).'</h2></div>';

	}

}

function church_admin_bulk_edit_wedding_anniversary(){

	echo '<h2>'.esc_html('Update Wedding Anniversaries','church-admin').'</h2>';

	global $wpdb;
	
	$old_data = $wpdb->get_results('SELECT a.*, b.wedding_anniversary FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.head_of_household=1 AND a.marital_status="'.esc_sql(__( 'Married' , 'church-admin' ) ).'" AND a.household_id=b.household_id ORDER BY a.last_name');

 	if(empty($old_data)){
		echo'<p>'.__('No one showing as "Married"','church-admin').'</p>';
		return;
	}



	if(!empty($_POST['save'])){
		
		$transients=array();
		$transients = get_option('church_admin_transient_wedding_anniversary_update');
		foreach($old_data AS $family){
			$old_value = !empty($family->wedding_anniversary) ? $family->wedding_anniversary: null;
			$new_value = !empty($_POST[$family->household_id]) ? $_POST[$family->household_id]: null;
			if(!empty($new_value) && !church_admin_checkdate($new_value)){
				$new_value = null;

			}	
			//update household record
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET wedding_anniversary= "'.esc_sql($new_value).'" WHERE household_id="'.(int)$family->household_id.'"');
			

			//update transient array
			$new_transient_value = array('household_id'=>$family->household_id,'old_value'=>$old_value,'new_value'=>$new_value);
			if(empty($transients)){$transients=array();}
			$transients[]=$new_transient_value;
		}
		update_option('church_admin_transient_wedding_anniversary_update',$transients);
		echo '<div class="notice notice-success"><h2>'.esc_html(__('Wedding Anniversaries updated','church-admin')).'</h2></div>';
	}

	$data = $wpdb->get_results('SELECT a.*, b.wedding_anniversary FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.head_of_household=1 AND a.marital_status="'.esc_sql(__( 'Married' , 'church-admin' ) ).'" AND a.household_id=b.household_id ORDER BY a.last_name');
	
	echo'<form action="" method="POST">';
	foreach($data AS $family){
		echo'<div class="church-admin-form-group"><label>'.esc_html(church_admin_formatted_name($family)).'</label>';
		$db_date=!empty($family->wedding_anniversary)?$family->wedding_anniversary:null;
		echo church_admin_date_picker( $db_date,$family->household_id,FALSE,"1910",wp_date('Y'),'family-'.$family->household_id,'family-'.$family->household_id,FALSE,NULL,NULL,NULL);
		echo'</div>';
	}
	echo'<p><input type="hidden" name="save" value="yes"><input class="button-primary" type="submit" value="'.__('Update wedding anniversaries').'"></p></form>';
}



function church_admin_bulk_edit_date_of_birth(){

	echo '<h2>'.esc_html('Update Dates of Birth','church-admin').'</h2>';

	global $wpdb;
	
	$old_data = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people ORDER BY last_name,first_name');

 	if(empty($old_data)){
		echo'<p>'.__('No one in database','church-admin').'</p>';
		return;
	}



	if(!empty($_POST['save'])){
		
		$transients=array();
		$transients = get_option('church_admin_transient_date_of_birth_update');
		foreach($old_data AS $person){
			$old_value = !empty($person->date_of_birth) ? $person->date_of_birth: null;
			$new_value = !empty($_POST[$person->people_id]) ? $_POST[$person->people_id]: null;
			if(!empty($new_value) && !church_admin_checkdate($new_value)){
				$new_value = null;

			}	
			//update household record
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET date_of_birth= "'.esc_sql($new_value).'" WHERE people_id="'.(int)$person->people_id.'"');
			

			//update transient array
			$new_transient_value = array('people_id'=>$person->people_id,'old_value'=>$old_value,'new_value'=>$new_value);
			if(empty($transients)){$transients=array();}
			$transients[]=$new_transient_value;
		}
		update_option('church_admin_transient_date_of_birth_update',$transients);
		echo '<div class="notice notice-success"><h2>'.esc_html(__('Dates of Birth updated','church-admin')).'</h2></div>';
	}

	$data = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people ORDER BY last_name,first_name');
	
	echo'<form action="" method="POST">';
	foreach($data AS $person){
		echo'<div class="church-admin-form-group"><label>'.esc_html(church_admin_formatted_name($person)).'</label>';
		$db_date=!empty($person->date_of_birth) ? $person->date_of_birth : null;
		echo church_admin_date_picker( $db_date,$person->people_id,FALSE,"1910",wp_date('Y'),'person-'.$person->people_id,'person-'.$person->people_id,FALSE,NULL,NULL,NULL);
		echo'</div>';
	}
	echo'<p><input type="hidden" name="save" value="yes"><input class="button-primary" type="submit" value="'.__('Update dates of birth').'"></p></form>';
}


function church_admin_bulk_edit_custom_field(){
	global $wpdb;


	echo '<h2>'.esc_html('Bulk edit a custom field','church-admin').'</h2>'."\r\n";

	
	
	$custom_fields = church_admin_custom_fields_array();
	if(empty($custom_fields)){
		echo'<p>'.__('No custom fields set up yet','church-admin');
		return;
	}



	$custom_id = !empty($_POST['custom_id']) ? (int)church_admin_sanitize($_POST['custom_id']) : null;


	if(empty($custom_id)){
		echo'<form action="admin.php?page=church_admin%2Findex.php&action=bulk-edit-custom" method="POST">'."\r\n";
		wp_nonce_field('bulk-edit-custom');
		echo'<div class="church-admin-form-group"><label>'.esc_html('Choose a custom field to bulk edit','church-admin').'</label>';
		echo '<select class="church-admin-form-control" name="custom_id"><option value=0>'.__('Please select...','church-admin').'</option>';
		foreach($custom_fields AS $id=>$data){
			if($data['section']=='people' || $data['section']=='household')
			switch($data['section'])
			{
				case 'people' 	: $which = __('Individual','church-admin'); break;
				case 'household' : $which = __('Household','church-admin'); break;
			}
			echo '<option value="'.(int)$id.'">'.esc_html($data['name']).' ('.esc_html($which).')</option>';
		}
		echo'</select></div>'."\r\n";
		echo'<p><input class="button-primary"type="submit" value="'.__('Go','church-admin').'" ></p></form>';
		return;
	}
	$custom_field_data = $custom_fields[$custom_id];
	if(!empty($_POST['save'])){

		
		$section=$custom_field_data['section'];

		//delete current values
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE section="'.esc_sql($section).'" AND custom_id="'.(int)$custom_id.'"');

		switch($custom_field_data['section']){

			case 'people':
				$sql='SELECT a.*, b.data FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_custom_fields_meta b ON a.people_id = b.people_id AND b.section="people" AND b.custom_id="'.(int)$custom_id.'"';

			break;
			case 'household':
				$sql='SELECT a.*,b.data FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_custom_fields_meta b ON a.household_id=b.household_id AND b.section="household" AND b.custom_id="6" WHERE a.head_of_household=1;';
			break;

		}
		$results = $wpdb->get_results($sql);
		$records=0;
		foreach($results AS $row)
		{
			//check for input for each row
			if($custom_field_data['section'] =='people'){
				$data = !empty($_POST['id-'.$row->people_id]) ? church_admin_sanitize($_POST['id-'.$row->people_id]) : null;
				if(!empty($data)){
					if(is_array($data)){
						$data = serialize($data);
					}
					//insert with people_id section = people
					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (section, people_id, data, custom_id) VALUES ("people","'.(int)$row->people_id.'","'.esc_sql($data).'","'.(int)$custom_id.'")');
				}
			}
			else{
				$data = !empty($_POST['id-'.$row->household_id]) ? church_admin_sanitize($_POST['id-'.$row->household_id]) : null;
				if(!empty($data)){
					if(is_array($data)){
						$data = serialize($data);
					}
					//insert with household_id section = household
					$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (section, household_id, data, custom_id) VALUES ("household","'.(int)$row->household_id.'","'.esc_sql($data).'","'.(int)$custom_id.'")');
				}
			}
		
			$records++;
		}
		//finished saving message
		echo '<div class="notice notice-success"><h2>'.esc_html(sprintf(__('"%1$s" custom field with %2$s records updated','church-admin'),$custom_field_data['name'],$records)).'</h2></div>';

	}
	else
	{
		
		if(empty($custom_field_data)){
			echo'<p>'.__('Custom field not found','church-admin').'</p>';
			return;
		}
		
		echo'<h3>'.esc_html(sprintf(__('Bulk editing "%1$s" custom field','church-admin'),$custom_field_data['name'])).'</h3>'."\r\n";
		echo'<form action="admin.php?page=church_admin%2Findex.php&action=bulk-edit-custom" method="POST">';
		wp_nonce_field('bulk-edit-custom');
		echo'<input type="hidden" name="custom_id" value="'.(int)$custom_id.'">';

		switch($custom_field_data['section']){

			case 'people':
				$sql='SELECT a.*, b.data FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_custom_fields_meta b ON a.people_id = b.people_id AND b.section="people" AND b.custom_id="'.(int)$custom_id.'" ORDER BY a.last_name,a.first_name';

			break;
			case 'household':
				$sql='SELECT a.*,b.data FROM '.$wpdb->prefix.'church_admin_people a LEFT JOIN '.$wpdb->prefix.'church_admin_custom_fields_meta b ON a.household_id=b.household_id AND b.section="household" AND b.custom_id="6" WHERE a.head_of_household=1 ORDER BY a.last_name,a.first_name;';
			
			break;

		}
		
		$results = $wpdb->get_results($sql);
		foreach($results AS $row)
		{
			switch($custom_field_data['section']){
				case 'people':
					$id=$row->people_id;
					$label = church_admin_formatted_name($row);
				break;
				case 'household':
					$id=$row->household_id;
					$label = sprintf(__('Household of %1$s','church-admin'),church_admin_formatted_name($row));
				break;
			}

			echo'<div class="church-admin-form-group"><label>'.esc_html($label).'</label>';

			switch($custom_field_data['type']){

				case 'boolean':
					echo'<input type="radio"  class="church-admin-form-control" name="id-'.(int)$id.'" value="1" ';
					if (!empty( $row->data))echo ' checked="checked" ';
					echo '>'.esc_html( __( 'Yes','church-admin') ).'<br> <input type="radio"   class="church-admin-form-control" value="0" name="id-'.(int)$id.'" ';
					if (empty( $row->data)) echo  'checked="checked" ';
					echo '>'.esc_html( __( 'No','church-admin') );
					break;
				case'text':
					echo '<input type="text"  class="church-admin-form-control "  name="id-'.(int)$id.'" ';
					if(!empty( $row->data) || isset( $field['default'] ) )echo ' value="'.esc_html( $row->data).'"';
					echo '/>';
				break;
				case'date':
					
					echo church_admin_date_picker( $row->data,'id-'.(int)$id,FALSE,1910,date('Y'),'id-'.(int)$id,'id-'.(int)$id,FALSE,'custom',(int)$id,(int)$id);
				
				break;
				case 'checkbox':
					$options = maybe_unserialize($custom_field_data['options']);
					if(!empty($row->data))$dataField = maybe_unserialize($row->data);
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						echo '<div class="checkbox"><label><input type="checkbox" class="church-admin-editable id-'.(int)$id.'" name="id-'.(int)$id.'[]" value="'.esc_attr($options[$y]).'" ';
						if(!empty($dataField) && is_array($dataField) && in_array($options[$y],$dataField) ){echo ' checked="checked" ';}
						
						echo '> '.esc_html($options[$y]).'</label></div>';
					}
				break;
				case 'radio':
					$options = maybe_unserialize($custom_field_data['options']);
				
					if(empty($options)){break;}
					
					for ($y=0;$y<count($options);$y++){
						echo '<div class="checkbox"><label><input type="radio" class="church-admin-editable"  name="id-'.(int)$id.'" value="'.esc_attr($options[$y]).'" ';
						if(!empty($row->data) && $options[$y] == $row->data) {echo ' checked="checked" ';}
						echo '> '.esc_html($options[$y]).'</label></div>';
					}
				break;	
				case 'select':
					$options = maybe_unserialize($custom_field_data['options']);
					if(!empty($row->data))$dataField = maybe_unserialize($row->data);
					if(empty($options)){break;}
					echo '<select name="id-'.(int)$id.'" class="church-admin-form-control "><option>'.esc_html( __( 'Choose' , 'church-admin' ) ) .'</option>';
					for ($y=0;$y<count($options);$y++){
						echo '<option value="'.esc_attr($options[$y]).'" '.selected($options[$y],$dataField,FALSE).' data-what="household-custom" data-type="select" data-custom-id="'.(int)$id.'"> '.esc_html($options[$y]).'</option>';
					}
					echo '</select>';
				break;


			}
			echo'</div>';

		}
		echo'<p><input type="hidden" name="save" value="yes"><input class="button-primary"type="submit" value="'.__('Save','church-admin').'" ></p></form>';
	}




}


function church_admin_bulk_edit_comms_settings(){
	global $wpdb;
	echo '<h2>'.esc_html(__('Email communication permissions','church-admin')).'</h2>';
	if(!empty($_POST['save'])){
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="prayer-requests"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="bible-readings"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="posts"');
		
		$people=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people');
		if(!empty($people)){
			$values=array();
			foreach($people AS $person){

				if(!empty($_POST['news_send']))$values[]='("'.(int)$person->people_id.'","news_send",1,"'.esc_sql(wp_date('Y-m-d')).'")';
				if(!empty($_POST['prayer_requests']))$values[]='("'.(int)$person->people_id.'","prayer_requests",1,"'.esc_sql(wp_date('Y-m-d')).'")';
				if(!empty($_POST['bible_readings']))$values[]='("'.(int)$person->people_id.'","bible_readings",1,"'.esc_sql(wp_date('Y-m-d')).'")';

			}
			if(!empty($values)){
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,meta_type,ID,meta_date) VALUES '.implode(',',$values));
				echo $wpdb->last_query;
			}
		}
		echo'<div class="notice notice-succes"><h2>'.esc_html(__('Email communication permissions updated','church-admin')).'</h2></div>';
	}
	else
	{
		echo'<p>'.__('Uncheck and save to undo permissions for all','church-admin').'</p>';
		echo'<form action="admin.php?page=church_admin/index.php&action=bulk-edit-comms-permissions" method="POST">';
		wp_nonce_field('bulk-edit-comms-permissions');
		echo'<div class="church-admin-form-group"><label>'.__('Set everyone to receive...','church-admin').'</div>';
		echo'<p><input type="checkbox" name="news_send">&nbsp;'.esc_html('Blog posts by email','church-admin').'</p>';
		echo'<p><input type="checkbox" name="prayer_requests">&nbsp;'.esc_html('New Prayer requests by email','church-admin').'</p>';
		echo'<p><input type="checkbox" name="bible_readings">&nbsp;'.esc_html('New Bible readings by email','church-admin').'</p>';
		
		echo'<p><input type="hidden" name="save" value="yes"><input type="submit" class="button-primary" value="'.__('Save','church-admin').'"></p></form>';
	}
}

function church_admin_quick_household(){
	global $wpdb;
	$current_user = wp_get_current_user();

	echo'<h2>'.__('Quick household with user creation','church-admin').'</h2>';
	if(!empty($_POST['save'])){
		$first_name = !empty($_POST['first_name']) ? church_admin_sanitize($_POST['first_name']) : null;
		$last_name = !empty($_POST['last_name']) ? church_admin_sanitize($_POST['last_name']) : null;
		$email  = !empty($_POST['email_address']) ? church_admin_sanitize($_POST['email_address']) : null;

		if(empty($first_name) || empty($last_name) || empty($email)){
			echo'<div class="notice notice-warning"><h2>'.esc_html(__("Missing form fields",'church-admin')).'</h2></div>';
			return;
		}

		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (first_registered,updated_by, last_updated) VALUES("'.esc_sql(wp_date('Y-m-d')).'","'.(int)$current_user->ID.'","'.esc_sql(wp_date('Y-m-d')).'")');
		church_admin_debug($wpdb->last_query);
		$household_id=$wpdb->insert_id;
		
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name, email,people_type_id, household_id,email_send, active,first_registered, last_updated, updated_by,gdpr_reason,head_of_household,member_type_id,sex)VALUES("'.esc_sql($first_name).'","'.esc_sql($last_name).'","'.esc_sql($email).'",1,"'.(int)$household_id.'",1,1,"'.esc_sql(wp_date('Y-m-d')).'","'.esc_sql(wp_date('Y-m-d')).'","'.(int)$current_user->ID.'","'.esc_sql(__('Admin added','church-admin')).'",1,1,1)');
		$people_id=$wpdb->insert_id;
		church_admin_debug($wpdb->last_query);
		church_admin_debug('P-ID: '.$people_id);
		church_admin_create_user($people_id,$household_id,null,null);
		/*****************
		 * Admin email 
		 ****************/
		$adminmessage=get_option('church_admin_new_entry_admin_email');
		$admin_message = str_replace('[HOUSEHOLD_ID]','[HOUSEHOLD_ID]&token=[NONCE]',$adminmessage);
        $adminmessage=str_replace('[HOUSEHOLD_ID]',(int)$household_id,$adminmessage);
		$token = md5(NONCE_KEY.$household_id);
        $adminmessage=str_replace('[NONCE]',$token,$adminmessage);
        $adminmessage.='<p>&nbsp;</p>';
		$adminmessage=str_replace('[HOUSEHOLD_DETAILS]','',$adminmessage);
        $adminmessage.= church_admin_household_details_table($household_id);
        church_admin_debug($adminmessage);
		church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(sprintf(__('New household registration on %1$s','church-admin' ) ,site_url()) ),wp_kses_post($adminmessage),null,null,null,null,null,FALSE);
           
		echo'<div class="notice notice-warning"><h2>'.esc_html(__("Person saved and user created",'church-admin')).'</h2></div>';



	}
	
		echo'<form action="" method="POST">';
		echo'<div class="church-admin-form-group"><label>'.esc_html(__('First name','church-admin')).'</label>';
		echo'<input class="church-admin-form-control" type="text" name="first_name" required="required">';
		echo'</div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html(__('Last name','church-admin')).'</label>';
		echo'<input class="church-admin-form-control" type="text" name="last_name" required="required">';
		echo'</div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html(__('Email','church-admin')).'</label>';
		echo'<input class="church-admin-form-control" type="email" name="email_address" required="required">';
		echo'</div>';
		echo'<p><input type="hidden" name="save" value="save"><input class="button-primary" type="submit" value="'.esc_attr(__('Save','church-admin')).'" ></p></form>';



}