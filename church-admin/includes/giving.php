<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



function church_admin_fix_anon_giving()
{
    global $wpdb;
    $results=$wpdb->get_results('SELECT *  FROM '.$wpdb->prefix.'church_admin_giving WHERE name is NULL OR name="" AND people_id IS NOT NULL AND people_id!=0');
   
    if(!empty( $results) )
    {
        foreach( $results AS $row)
        {
            $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$row->people_id.'"');
            if(!empty( $person) )
            {
                $name=church_admin_formatted_name( $person);
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_giving SET name="'.esc_sql( $name).'" WHERE giving_id="'.(int)$row->giving_id.'"');
                echo '<p>'. esc_html(_sprintf(__('Gift id %1$s name fixed with "%2$s"','church-admin' ) ,(int)$row->giving_id, $name) ).'</p>';
            }
        }
    }
    else
    {echo'<div class="notice notice-warning"><h2>'.esc_html( __('No anonymous gifts with a people ID saved','church-admin' ) ).'</h2></div>';}

}
/*************************************
*
*		GIVING List
*   Updated 2022-01-03 for giving meta table
**************************************/
function church_admin_giving_list()
{
    global $wpdb;
    if(!empty( $_POST['attach_people_id'] )&& !empty( $_POST['giving_id'] ) )
    {
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_giving SET people_id="'.(int)sanitize_text_field(stripslashes($_POST['attach_people_id'])).'" WHERE giving_id="'.(int)sanitize_text_field(stripslashes($_POST['giving_id'])).'"');
    }
    echo'<h2>Giving List</h2>';
    
    echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/giving/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
   
    $licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
        echo'<p>'.esc_html( __('To use the PayPal giving shortcode and block please set up PayPal above. Manual giving logging is below','church-admin' ) ).'</p>';
        $premium=array('currency_symbol'=>"$");
    }
   
    
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-gift&amp;section=giving','edit-gift').'">'.esc_html( __('Add donation','church-admin' ) ).'</a></p>';
    echo'<form action="admin.php" method="GET"><p><input type="hidden" name="page" value="church_admin/index.php" /><input type="hidden" name="action" value="giving" /><p><input type="text" placeholder="'.esc_html( __('Search by donor details','church-admin' ) ).'" name="search" /><input class="button-primary" type="submit" value="'.esc_html( __('Search giving records','church-admin' ) ).'" /></p></form>';
    wp_nonce_field('giving');
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=fix-anon-gifts&amp;section=giving','fix-anon-gifts').'">  <strong>'.esc_html( __('Attempt to fix anon gifts','church-admin' ) ).'</a></p>';
    
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.class.php');
    if(!empty( $_GET['search'] ) )
    {
        if( $_GET['search']== __('Anonymous','church-admin') )  {$s=' WHERE a.name IS NULL or a.name="" ';}
        else
        {
            $s=' WHERE a.name LIKE "%'.esc_sql(sanitize_text_field(stripslashes( $_GET['search'] ))).'%" OR a.email LIKE "%'.esc_sql(sanitize_text_field(stripslashes( $_GET['search'] ))).'%" or a.address LIKE "%'.esc_sql(sanitize_text_field(stripslashes( $_GET['search'] ))).'%" ';
        }
        $sql='SELECT a.*,b.*,c.*,a.gift_aid AS thisGA,a.email AS donor_email,a.address AS donor_address FROM '.$wpdb->prefix.'church_admin_giving a LEFT JOIN '.$wpdb->prefix.'church_admin_people b ON a.people_id=b.people_id  LEFT JOIN '.$wpdb->prefix.'church_admin_services c ON a.service_id=c.service_id '.$s.' ORDER BY donation_date DESC,giving_id DESC'; 
    }else
    {
    
        $sql='SELECT a.*,b.*,c.*,a.gift_aid AS thisGA,a.email AS donor_email,a.address AS donor_address FROM '.$wpdb->prefix.'church_admin_giving a LEFT JOIN '.$wpdb->prefix.'church_admin_people b ON a.people_id=b.people_id  LEFT JOIN '.$wpdb->prefix.'church_admin_services c ON a.service_id=c.service_id ORDER BY donation_date DESC,giving_id DESC'; 
    }
    $results=$wpdb->get_results( $sql);
    $items=$wpdb->num_rows;
    if( $items > 0)
    {

	   $p = new caPagination;
	   $p->items( $items);
       
	   $p->limit(25); // Limit entries per page
        $p->target(wp_nonce_url('admin.php?page=church_admin/index.php&section=giving&action=giving','giving'));
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
        $limit = esc_sql(" LIMIT " . ( $p->page - 1) * $p->limit  . ", " . $p->limit);
        $results=$wpdb->get_results( $sql.$limit);
        church_admin_debug( $sql.$limit);
        if(!empty( $_GET['search'] ) )echo '<h3>'.esc_html(sprintf(__('Results for search "%1$s"','church-admin' ) ,sanitize_text_field(stripslashes($_GET['search']) ) )).'</h3>';
        // Pagination
    	echo '<div class="tablenav"><div class="tablenav-pages">';
    	echo $p->show();
    	echo '</div></div>';
        $action='giving';
        church_admin_giving_table( $results);
        // Pagination
    	echo '<div class="tablenav"><div class="tablenav-pages">';
    	echo $p->show();
    	echo '</div></div>';
    
    }
    else 
    {
        if ( empty( $_GET['search'] ) )  {echo'<p>'.esc_html( __('No gifts processed yet','church-admin' ) ).'</p>';}
        else{echo '<h3>'. esc_html(sprintf(__('No results for search "%1$s"','church-admin' ) ,esc_html(sanitize_text_field( stripslashes($_GET['search'] )  ) ))).'</h3>';}
    }
}

function church_admin_giving_table( $results)
{
    global $wpdb;
    $premium=get_option('church_admin_payment_gateway');

        $tableHeader='<tr><th class="column-primary"></span>'.esc_html( __('Donation date','church-admin' ) ).' </th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Donor','church-admin' ) ).'</th><th>'.esc_html( __('Attach','church-admin' ) ).'</th><th>'.esc_html( __('Envelope ID','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th><th>'.esc_html( __('Address','church-admin' ) ).'</th><th>'.esc_html( __('Service','church-admin' ) ).'</th><th>'.esc_html( __('Transaction type','church-admin' ) ).'</th><th>'.esc_html( __('Frequency','church-admin' ) ).'</th><th>'.esc_html( __('Amounts (Net of fees)','church-admin' ) ).'</th>';
        if(!empty( $premium['gift_aid'] ) )  {$tableHeader.='<th>'.esc_html( __('Gift Aided?','church-admin' ) ).'</th><th>'.__('Refund','church-admin').'</th><th>'.esc_html(__('Send Receipt','church-admin')).'</th>';}
        $tableHeader.='</tr>';
        //add custom fields to header
       
        $custom_fields=church_admin_get_custom_fields();
        if(!empty( $custom_fields) )
        {
            foreach( $custom_fields AS $id=>$field)
            {
                if( $field['section']!='giving')continue;
               
                $tableHeader.='<th>'.esc_html( $field['name'] ).'</th>';
            }
        }
        $tableHeader.='</tr>';
       
        echo'<table class="widefat wp-list-table striped"><thead>'.$tableHeader.'<thead><tfoot>'.$tableHeader.'</tfoot><tbody>';
        foreach( $results AS $row)
        {
            
            $edit='<a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-gift&amp;section=giving&amp;giving_id='.(int)$row->giving_id,'edit-gift').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure you want to delete this item?','church-admin' ) ).'\')" class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-gift&amp;section=giving&amp;gift_id='.(int)$row->giving_id,'delete-gift').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            $date=mysql2date(get_option('date_format').' '.get_option('time_format'),$row->donation_date);
            if(!empty( $row->last_name) )
            {
                $donor='<a title="'.esc_html( __('View donor giving','church-admin' ) ).'" href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=donor-gift&amp;section=giving&amp;people_id='.(int)$row->people_id,'donor-gift')).'">'.church_admin_formatted_name( $row).'</a>';
            }
            elseif(!empty( $row->name) )  {$donor=esc_html( $row->name);}
            else{ $donor=esc_html(__('Anonymous','church-admin') );}
            //Ability to attach a people record to donor
            $attach='&nbsp;';
            if ( empty( $row->people_id) )
            {
                //look for people to attach
                $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE CONCAT_WS(" ",first_name, last_name) LIKE "'.esc_sql( $donor).'" ';
                if(!empty( $row->donor_email) )$sql.='OR email="'.esc_sql( $row->donor_email).'"';//only use email if there is one, or it returns all empty email records!
                $people=$wpdb->get_results( $sql );
                church_admin_debug( $wpdb->last_query);
                if(!empty( $people) )
                {
                    $attach='<form action="" method="POST"><select name="attach_people_id">';
                    foreach( $people AS $person)  {$attach.='<option value="'.(int)$person->people_id.'">'.esc_html(church_admin_formatted_name( $person) ).'</option>';}
                    $attach.='</select><input type="hidden" name="giving_id" value="'.(int)$row->giving_id.'" /><input type="submit" value="'.esc_html( __('Attach','church-admin' ) ).'" /></form>'; 
                }

            }
            if(!empty( $row->envelope_id) )  {$envelope=esc_html( $row->envelope_id);}else{$envelope='&nbsp;';}
            if(!empty( $row->donor_address) )  {$address=esc_html( $row->donor_address);}else{$address='&nbsp;';}
            if(!empty( $row->donor_email) )  {$email=esc_html( $row->donor_email);}else{$email='&nbsp;';}
            if ( empty( $row->name)&& empty( $row->first_name) && !empty( $row->last_name) )
            {
                $donor=church_admin_formatted_name( $row);
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_giving SET name="'.esc_sql( $donor).'" WHERE giving_id="'.(int)$row->giving_id.'"');
            }
            /******************
             * Grab donations
             ******************/
            $amountCol='&nbsp;';
           $thisDonation=0;
            $amounts=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE giving_id="'.(int)$row->giving_id.'"');
            if(!empty( $amounts) )
            {
                
                foreach ( $amounts AS $amount)
                {
                    if(!empty( $amount->paypal_fee) )  {$fee=$amount->paypal_fee;}else{$fee=0;}
                    $net_amount=$amount->gross_amount-$fee;
                    $thisDonation+=$net_amount;
                    $currSymbol=!empty( $premium['currency_symbol'] )?$premium['currency_symbol']:"";
                    $txndate = !empty($amount->txn_date)?mysql2date(get_option('date_format'),$amount->txn_date):'';
                    $amountCol.=esc_html( $txndate.' '. $amount->fund).': '.$currSymbol.$net_amount.'<br>';
                    
                }
                if( $thisDonation!=$net_amount)$amountCol.='<strong>'.esc_html( __('Total','church-admin' ) ).': '.$currSymbol.floatval( $thisDonation).'</strong>';
               
            }
            
            
            
            if ( empty( $row->service) )$row->service='';
            if ( empty( $row->venue) )$row->venue='';
            if ( empty( $row->service_time) )$row->service_time='';
            $venue=implode(" ",array_filter(array( $row->service_name,$row->venue,$row->service_time) ));
            $refund=__('Stripe Only','church-admin');
            if($premium['gateway']=='stripe'){
                if($thisDonation>0){
                     $refund = '<a onclick="return confirm(\''.esc_html( __('Are you sure you want to delete this item?','church-admin' ) ).'\')" class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=refund-gift&amp;section=giving&amp;gift_id='.(int)$row->giving_id,'refund-gift').'">'.esc_html( __('Refund','church-admin' ) ).'</a>';
                }else{$refund='&nbsp;';}
            }
            echo'<tr><td class="column-primary" data-colname="'.esc_html( __('Donation date','church-admin' ) ).'">'.esc_html($date).' <button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td><td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td><td  data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
            <td>'.$donor.'</td><td  data-colname="'.esc_html( __('Attach','church-admin' ) ).'">'.$attach.'</td><td>'.esc_html($envelope).'</td><td  data-colname="'.esc_html( __('Email','church-admin' ) ).'">'.$email.'</td><td  data-colname="'.esc_html( __('Address','church-admin' ) ).'">'.$address.'</td><td  data-colname="'.esc_html( __('Venue','church-admin' ) ).'">'.esc_html( $venue).'</td><td data-colname="'.esc_html( __('Transaction Type','church-admin' ) ).'">'.esc_html( $row->txn_type).'</td><td  data-colname="'.esc_html( __('Frequency','church-admin' ) ).'">'.esc_html( $row->txn_frequency).'</td><td data-colname="'.esc_html( __('Amounts','church-admin' ) ).'">'.$amountCol.'</td>';
            if(!empty( $row->thisGA) )
            {
                $GA=__('Yes','church-admin');
            }
            else
            {
                $GA=__('No','church-admin');
            }
            if(!empty( $premium['gift_aid'] ) )echo'<td  data-colname="'.esc_html( __('Gift Aid','church-admin' ) ).'">'.$GA.'</td>';
            echo'<td data-colname="'.esc_html( __('Refund','church-admin' ) ).'">'.wp_kses_post($refund).'</td>';
            //receipt
            $link = '<a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=email-donor-receipt&id='.(int)$row->giving_id,'email-donor-receipt').'">'.esc_html(__('Email receipt','church-admin')).'</a>';
            echo'<td>'.$link.'</td>';    
            //add custom field data
            if(!empty( $custom_fields) )
                {
                    foreach( $custom_fields AS $id=>$field)
                    {
                       
                        if( $field['section']!='giving')continue;
                        echo'<td>';
                        $thisData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$id.'" AND gift_id="'.(int)$row->giving_id.'"');
                        
                        switch( $field['type'] )
                        {
                            case 'boolean':
                                if(!empty( $thisData->data) )  {$customOut==__('Yes','church-admin');}else{$customOut=__('No','church-admin');}
                            break;
                            case 'date':
                                if(!empty( $thisData->data) )  {$customOut=mysql2date(get_option('date_format'),$thisData->data);}else{$customOut="";}
                            break;
                            default:
                                if(!empty( $thisData->data) )  {$customOut=esc_html( $thisData->data);}else{$customOut="";}
                            break;
                        }
                        if(!empty( $customOut) )  {echo $customOut;}else{echo'&nbsp;';}
                        echo'</td>';
                    }
                }
            

            echo'</tr>';
        }
        echo'</tbody></table>';
        
}
/*************************************
*
*		Edit gift
*
**************************************/
function church_admin_giving_edit( $giving_id=NULL)
{
    
    /****************************************************************************
     * 2022-01-03 Update gifts can be split; 
     * giving meta table now holds donations for a particular gift
     ****************************************************************************/
    global $wpdb;
    
    //set up data variables
    $envelope=null;
    $funds=get_option('church_admin_giving_funds');
    $custom_fields=church_admin_get_custom_fields();
    $premium=get_option('church_admin_payment_gateway');
    $currency_symbol=(!empty( $premium['currency_symbol'] ) )?$premium['currency_symbol']:"";
    $name=$person=$email=NULL;
    $donation_date=date('Y-m-d');
    if(!empty( $giving_id) )
    {
        $donation=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_giving WHERE giving_id="'.(int)$giving_id.'"');
        if(!empty( $donation) )
        {
           
            $donation_date=$donation->donation_date;
            if(!empty( $donation->people_id) )
            {
                $person=$wpdb->get_row('SELECT a.* FROM '. $wpdb->prefix.'church_admin_people'.' a, '.$wpdb->prefix.'church_admin_household b WHERE a.people_id="'.(int)$donation->people_id.'" AND a.household_id=b.household_id');
                if(!empty( $person) )$name=esc_html(church_admin_formatted_name( $person) );
                if(!empty( $person) )$email=esc_html( $person->email);
            }
            else
            {
                $name=esc_html( $donation->name);
                $email=esc_html( $donation->email);
            }
            $envelope = !empty($donation->envelope_id)?$donation->envelope_id:null;
            $amounts=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE giving_id="'.(int)$giving_id.'"');    
        }
    }
   

    if(!empty( $_POST['save-gift'] ) )
    {
        
        
        /**************************
         * Delete current values
         **************************/
        if(!empty( $giving_id) )
        {
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_giving WHERE giving_id="'.(int)$giving_id.'"');
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE giving_id="'.(int)$giving_id.'"');
        }
        $totalAmount=0;
        foreach( $_POST['amount'] AS $key=>$amt)  {$totalAmount+=(int)$amt;}
        
        if ( empty( $totalAmount) )
        {
            echo'<div class="notice notice-danger"><h2>'.esc_html( __("Empty donation",'church-admin' ) ).'</h2>';
            echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=edit-gift','edit-gift').'">'.esc_html( __('Enter a new donation','church-admin' ) ).'</a></p>';
            echo'</div>';
        }
        else
        {
           //envelope
           $envelope = !empty($_POST['envelope_id'])?church_admin_sanitize($_POST['envelope_id']):null;
            //donation date
            $donation_date=date('Y-m-d');
            if(!empty( $_POST['donation_date'] ) && church_admin_checkdate( $_POST['donation_date'] ) )
            {
                $donation_date=esc_sql( sanitize_text_field(stripslashes($_POST['donation_date'] )));
            }
            //find people_id
            if(!empty( $_POST['donor'] ) )
            {
                $name=rtrim(sanitize_text_field( $_POST['donor'] ),",");
                //look for people_id
                $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND (a.first_name LIKE "'.esc_sql( $name).'" OR a.last_name LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",a.first_name,a.last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",a.first_name,a.middle_name,a.prefix,a.last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",a.first_name,a.middle_name,a.last_name) LIKE "'.esc_sql( $name).'" OR CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) LIKE "'.esc_sql( $name).'" OR  a.nickname LIKE "'.esc_sql( $name).'") ';
                //adding email makes it more robust to one person.
                if(!empty( $_POST['email'] ) )$sql.= ' AND a.email="'.esc_sql(sanitize_text_field( stripslashes($_POST['email']) ) ).'"';
                $sql.= ' LIMIT 1'; 
                $personDetails=$wpdb->get_row( $sql);
                
                if(!empty( $personDetails) )
                {
                    $name=church_admin_formatted_name( $personDetails);
                    $address=$personDetails->address;
                    $people_id=$personDetails->people_id;
                    $email=sanitize_text_field(stripslashes( $_POST['email']) );
                }else
                {
                    $name=sanitize_text_field(stripslashes( $_POST['donor'] ));
                    $email=sanitize_text_field( stripslashes($_POST['email']) );
                    $people_id=NULL;
                    $address=NULL;
                }
            }
            //INSERT into giving table
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_giving (name,email,address,txn_type,txn_frequency,donation_date,people_id,envelope_id) VALUES("'.esc_sql( $name).'","'.esc_sql( $email).'","'.esc_sql( $address).'","'.esc_sql(sanitize_text_field(stripslashes( $_POST['txn_type']) ) ).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['txn_frequency'] ) )).'","'.esc_sql( $donation_date).'","'.(int)$people_id.'","'.esc_sql($envelope).'")');
            
            $giving_id=$wpdb->insert_id;
            //insert into giving meta
            $values=array();
            foreach( $_POST['fund'] AS $key=>$value)
            {
                $values[]='("'.esc_sql(floatval( sanitize_text_field(stripslashes($_POST['amount'][$key] ))) ).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['fund'][$key] )) ).'","'.(int)$giving_id.'")';
            }
            if(!empty( $values) )
            {
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_giving_meta (gross_amount,fund,giving_id) VALUES '.implode(",",$values) );
            }
           
            echo'<div class="notice notice-success"><h2>'.esc_html( __('Donation saved','church-admin' ) ).'</h2></div>';
            switch( $_POST['submission'] )
            {
                case __('Save and list gifts','church-admin'):
                    church_admin_giving_list();
                   
                break;
                default:
                    unset( $_POST);
                    church_admin_giving_edit(NULL);
                break;

            }  
        }         
    }
    else
    {
        /*********************
         * FORM
         ********************/
        $totalAmount=0;
        echo'<form action="" method="POST">';
        //date
        echo'<div class="church-admin-form-group" id="donation-date"><label>'.esc_html( __('Donation date','church-admin' ) ).'</label>'.church_admin_date_picker( $donation_date,'donation_date',FALSE,date('Y-m-d',strtotime("-10 years") ),NULL,'donation_date','donation_date',FALSE).'</div>';
        //name
        echo'<div class="church-admin-form-group" id="donor"><label>'.esc_html( __('Donor','church-admin' ) ).'</label>'.church_admin_autocomplete('donor','friends','to',$name).'</div>';
        //envelope
        echo'<div class="church-admin-form-group" id="envelope"><label>'.esc_html( __('Envelope ID','church-admin' ) ).'</label><input type="text" name="envelope_id" value="'.$envelope.'" class="church-admin-form-control" /></div>';
        //email
        echo '<div class="church-admin-form-group"  id="donation-email"><label>'.esc_html( __('Email','church-admin' ) ).'</label><input type="email" name="email" value="'.$email.'" class="church-admin-form-control" /></div>';
        //donation type
        echo'<div class="church-admin-form-group"  id="donation-type"><label>'.esc_html( __('Donation Type','church-admin' ) ).'</label><select class="church-admin-form-control" name="txn_type">';
        $types=array( esc_html(__('Cash','church-admin' ) ), esc_html(__('Check','church-admin' ) ), esc_html(__('BACs','church-admin' ) ), esc_html(__('Standing order','church-admin'),'PayPal','Stripe' ));
        foreach( $types AS $key=>$type)
        {
            echo '<option value="'.$type.'"';
            if(!empty( $gift->txn_type) && $gift->txn_type ==$type) echo 'selected="selected" ' ;
            echo'>'.$type.'</option>';
        }
        echo'</select></div>';
        //donation frequency
        $freq=array(esc_html(__('One off','church-admin' ) ),esc_html(__('Weekly','church-admin' ) ),esc_html(__('Monthly','church-admin') ));
        echo'<div class="church-admin-form-group"  id="donation-frequency"><label>'.esc_html( __('Donation Frequency','church-admin' ) ).'</label><select class="church-admin-form-control" name="txn_frequency">';
        foreach( $freq AS $key=>$fr)
        {
            echo '<option value="'.$fr.'"';
            if(!empty( $gift->txn_type) && $gift->txn_type ==$fr) echo 'selected="selected" ' ;
            echo'>'.$fr.'</option>';
        }
        echo'</select></div>';
        //donations
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Donation amount(s)','church-admin' ) ).'</label></div>';
        if(!empty( $amounts) )
        {
            
            foreach ( $amounts as $amount)
            {
                echo'<div class="church-admin-form-row"><div class="church-admin-form-col"><label>'.esc_html( __('Fund','church-admin' ) ).'</label><select class="church-admin-form-control" name="fund[]">';
                foreach( $funds AS $key=>$fund)
                {
                    if(!empty( $amount->fund) && $amount->fund==$fund)  {$sel=' selected="selected" ';}else{$sel='';}
                    echo'<option value="'.esc_html( $fund).'" '.$sel.'>'.esc_html( $fund).'</option>';
                }
                echo'</select></div><div class="church-admin-form-col"><label>'.esc_html( __('Amount','church-admin' ) ).'</label><input class="church-admin-form-control amount" type="number" step=".01" name="amount[]"';
                if(!empty( $amount->gross_amount) ) 
                {
                    echo ' value="'.floatval( $amount->gross_amount).'" ';
                    $totalAmount += $amount->gross_amount;
                }
                echo '/></div></div>';
                }
        }
        echo'<div class="church-admin-form-row" id="donation-row"><div class="church-admin-form-col"><label>'.esc_html( __('Fund','church-admin' ) ).'</label><select class="church-admin-form-control" name="fund[]">';
        foreach( $funds AS $key=>$fund)
        {
            
            echo'<option value="'.esc_html( $fund).'">'.esc_html( $fund).'</option>';
        }
        echo'</select></div><div class="church-admin-form-col"><label>'.esc_html( __('Amount','church-admin' ) ).'</label><input class="church-admin-form-control amount" type="number"  step=".01" name="amount[]" placeholder="'.esc_html( __('Amount','church-admin' ) ).'" /></div></div>';
        //javascript
        echo'<div class="more-funds"></div><p><button class="another-fund button-secondary">'.esc_html( __('Add another fund split amount','church-admin' ) ).'</button>';
        echo'<script>
        jQuery(document).ready(function( $)  {
            $(".another-fund").click(function(e)  {
                e.preventDefault();
                console.log("Clicked");
                $("#donation-row" ).clone().appendTo( ".more-funds" ).find("input").val("");
            });
            $(document).on("change paste keyup",".amount", function() {
                    console.log("Amount changed");
                    var sum = 0;
                    $(".amount").each(function()  {
                        console.log( $(this).val() );
                        sum += + $(this).val();
                    });
                    $(".totalAmount").html(sum);
            });

        });
        
        </script>';

        echo'<p><strong>'.esc_html( __('Total donation:','church-admin' ) ).' '.$currency_symbol.'<span class="totalAmount">'.$totalAmount.'</span></strong></p>';
        //custom fields
        if(!empty( $custom_fields) )
        {
            foreach( $custom_fields AS $id=>$field)
            {
                if( $field['section']!='giving')continue;

                if(!empty( $gift) )
                {
                    $thisGiftCustomData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE gift_id="'.(int)$gift->gift_id.'" AND custom_id="'.(int)$id.'"');
                }
                else
                {
                    $gift = new stdClass();
                    $gift->gift_id=0;
                }
                echo'<div class="church-admin-address church-admin-form-group" ><label>'.esc_html( $field['name'] ).'</label>';
                switch( $field['type'] )
                {
                    case 'boolean':
                        echo'<input type="radio" name="custom-'.(int)$id.'" data-what="household-custom" data-custom-id="'.(int)$gift->gift_id.'" data-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable"  value="1" ';
                        if (isset( $thisGiftCustomData->data)&&$thisGiftCustomData->data==1)echo 'checked="checked" ';
                        echo '>'.esc_html( __('Yes','church-admin' ) ).'<br> <input name="custom-'.(int)$id.'" type="radio" data-custom-id="'.(int)$gift->gift_id.'"  data-what="household-custom" data-ID="'.(int)$gift->gift_id.'" data-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable" value="0" name="custom-'.(int)$id.'" ';
                        if (isset( $thisGiftCustomData->data)&& $$thisGiftCustomData->data==0) echo  'checked="checked" ';
                        echo '>'.esc_html( __('No','church-admin'));
                        break;
                    case'text':
                        echo '<input type="text" data-what="household-custom" data-custom-id="'.(int)$gift->gift_id.'"  data-id="'.(int)$id.'" class="church-admin-form-control church-admin-editable"  name="custom-'.(int)$id.'" ';
                        if(!empty( $thisGiftCustomData->data)||isset( $field['default'] ) )echo ' value="'.esc_html( $thisGiftCustomData->data).'"';
                        echo '/>';
                    break;
                    case'date':
                        if(!empty( $thisGiftCustomData->data) )  {$currentData=$thisGiftCustomData->data;}else{$currentData='';}
                        echo church_admin_date_picker( $currentData,'custom-'.(int)$id,FALSE,1910,date('Y'),'custom-'.(int)$id,'custom-'.(int)$id,FALSE,'household-custom',(int)$gift->gift_id,(int)$id);
                    
                    break;
                }
                echo '</div>';
                
            }

        }
        //submit
        echo'<div class="church-admin-form-group"><input type="hidden" name="save-gift" value="TRUE" /><input type="submit" name="submission" value="'.esc_html( __('Save and list gifts','church-admin' ) ).'"  class="button-primary" />&nbsp;<input name="submission" type="submit" value="'.esc_html( __('Save and enter another','church-admin' ) ).'"  class="button-primary" /></div>';
        echo'</form>';
    }

}



/*************************************
*
*		refund gift
*
**************************************/
function church_admin_giving_delete( $giving_id=NULL)
{
    global $wpdb;
    echo'<h2>'.esc_html( __('Delete gift','church-admin' ) ).'</h2>';
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_giving WHERE giving_id="'.(int)$giving_id.'"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_giving_meta WHERE giving_id="'.(int)$giving_id.'"');
    echo'<div class="notice notice-success">'.esc_html( __('Gift deleted','church-admin' ) ).'</div>';
    church_admin_giving_list();

}
/*************************************
*
*		View donor
*
**************************************/
function church_admin_donor_giving( $people_id=NULL)
{
    
    global $wpdb;
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    if ( empty( $person) )return '<div class="notice notice-success"><h2>'.esc_html( __('Person not found','church-admin' ) ).'</h2></div>';

    echo'<h2>'.esc_html(sprintf(__('View donor giving for %1$s','church-admin' ) ,church_admin_formatted_name( $person) )).'</h2>';
    $sql='SELECT a.*,b.*,c.*,a.gift_aid AS thisGA,a.email AS donor_email,a.address AS donor_address FROM '.$wpdb->prefix.'church_admin_giving a LEFT JOIN '.$wpdb->prefix.'church_admin_people b ON a.people_id=b.people_id  LEFT JOIN '.$wpdb->prefix.'church_admin_services c ON a.service_id=c.service_id WHERE b.people_id="'.(int)$people_id.'" ORDER BY donation_date DESC,giving_id DESC'; 
    $results=$wpdb->get_results( $sql);
    if ( empty( $results) ) return '<div class="notice notice-success"><h2>'.esc_html( __('No gifts found','church-admin' ) ).'</h2></div>';
    church_admin_giving_table( $results);
}
/*************************************
*
*		GIVING funds
*
**************************************/
function church_admin_giving_funds()
{
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-fund&amp;section=giving','edit-fund').'">'.esc_html( __('Add fund','church-admin' ) ).'</a></p>';
    $funds=get_option('church_admin_giving_funds');
    if(!empty( $funds) )
    {
        $tableHeader='<tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Fund name','church-admin' ) ).'</th></tr>';
        echo'<p>'.esc_html( __("Deleting a fund won't affect previous donations to that fund",'church-admin' ) ).'</p>';
        echo'<table class="widefat striped"><thead>'.$tableHeader.'</thead><tfoot>'.$tableHeader.'<tfoot><tbody>';
        foreach( $funds AS $key=>$fund)
        {
            $edit='<a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-fund&amp;section=giving&amp;fund_id='.(int)$key,'edit-fund').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure you want to delete this item?','church-admin' ) ).'\')" class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-fund&amp;section=giving&amp;fund_id='.(int)$key,'delete-fund').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html( $fund).'</td></tr>';
        }
        echo'</tbody></table>';
    }
    
}
/*************************************
*
*		Delete Fund
*
**************************************/

function church_admin_delete_fund( $id)
{
    $funds=get_option('church_admin_giving_funds');
    unset( $funds[$id] );
    update_option('church_admin_giving_funds',$funds);
    echo'<div class="notice notice-success">'.esc_html( __('Fund deleted','church-admin' ) ).'</div>';
        church_admin_giving_funds();
}
/*************************************
*
*		Edit Fund
*
**************************************/
function church_admin_edit_fund( $id)
{
    $funds=get_option('church_admin_giving_funds');
    
    if(!empty( $_POST['save-fund'] ) )
    {
        if(isset( $id) )
        {
            $funds[$id]=sanitize_text_field( stripslashes($_POST['fund'] ));
        }else $funds[]=sanitize_text_field( stripslashes($_POST['fund'] ));
        update_option('church_admin_giving_funds',$funds);
        echo'<div class="notice notice-success">'.esc_html( __('Fund edited','church-admin' ) ).'</div>';
        church_admin_giving_funds();
    }
    else
    {
        echo'<h2>'.esc_html( __('Edit Fund','church-admin' ) ).'</h2>';
        echo'<form action="" method="POST">';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Fund name','church-admin' ) ).'</label><input type="text" name="fund" ';
        if(!empty( $id)&&!empty( $funds[$id] ) ) echo ' value="'.esc_html( $funds[$id] ).'"';
        echo'/></div>';
        echo'<div class="church-admin-form-group"><input type="hidden" name="save-fund" value="TRUE" /><input type="submit" value="Save"  class="button-primary">';
        
    }
}
/*************************************
*
*		CSV  Form
*
**************************************/
function church_admin_giving_csv_form()
{
    global $wpdb; 
    echo'<h2>'.esc_html( __('Download CSV','church-admin' ) ).'</h2>';
    echo'<form action="" method="GET"><input type="hidden" name="ca_download" value="giving-csv" />';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Start date','church-admin' ) ).'</label>'.church_admin_date_picker('start_date','start_date',FALSE,date('Y-m-d',strtotime("-10 years") ),NULL,'start_date','start_date',FALSE).'</div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('End date','church-admin' ) ).'</label>'.church_admin_date_picker('end_date','end_date',FALSE,date('Y-m-d',strtotime("-10 years") ),NULL,'end_date','end_date',FALSE).'</div>';
    //people
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('People','church-admin' ) ).'</label><select name="people_id" class="church-admin-form-control"><option value="0">'.esc_html( __('Everyone','church-admin' ) ).'</option>';
      
    $sql='SELECT a.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_giving b WHERE a.people_id=b.people_id GROUP BY a.people_id ORDER By a.last_name';
   
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
         foreach( $results AS $row)
        {
            $name=implode(" ",array_filter(array( $row->first_name,$row->prefix,$row->last_name) ));
            echo'<option value="'.(int)$row->people_id.'">'.esc_html( $name).'</option>';
        }
       
    }
    echo '</select></div>';
    echo'<div class="church-admin-form-group"><input type="hidden" name="save-fund" value="TRUE" /><input type="submit" value="Save"  class="button-primary"></form>';
}
function church_admin_gift_aid_csv()
{
    global $wpdb; 
    echo'<h2>'.esc_html( __('Download Gift Aid Report CSV','church-admin' ) ).'</h2>';
    $premium=get_option('church_admin_payment_gateway');
    if ( empty( $premium['gift_aid'] ) )
    {
        echo '<p>'.esc_html( __('You need to be an app subscriber and have setup the Paypal details to include Gift Aid',"church-admin")).'</p>';
        return;
    }
    
    echo'<form action="" method="GET"><input type="hidden" name="ca_download" value="gift-aid-csv" />';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Start date','church-admin' ) ).'</label>'.church_admin_date_picker('start_date','start_date',FALSE,date('Y-m-d',strtotime("-10 years") ),NULL,'start_date','start_date',FALSE).'</div>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('End date','church-admin' ) ).'</label>'.church_admin_date_picker('end_date','end_date',FALSE,date('Y-m-d',strtotime("-10 years") ),NULL,'end_date','end_date',FALSE).'</div>';
    $funds=get_option('church_admin_giving_funds');
    echo '<div class="church-admin-form-group"><label>'.esc_html( __('Fund','church-admin' ) ).'</label><select name="fund"><option value="All">'.esc_html( __('All','church-admin' ) ).'</option>';
    foreach( $funds AS $key=>$fund)
    {
        echo'<option value="'.urlencode( $fund).'">'.esc_html( $fund).'</option>';
    }
    echo'</select></div>';
    echo'<div class="church-admin-form-group"><input type="hidden" name="save-fund" value="TRUE" /><input type="submit" value="Save"  class="button-primary"></form>';
}

function church_admin_giving_receipt_template(){
    $template = get_option('church_admin_giving_receipt_template');

    if(!empty($_POST['save-giving-template'])){
       
        $template = !empty($_POST['template']) ? wp_kses_post( stripslashes( $_POST['template'] ) ):'';
        update_option('church_admin_giving_receipt_template',$template);
        echo'<div class="notice notice-sucess"><h2>'.esc_html(__('Template saved','church-admin')).'</h2><p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=giving','giving').'">'.esc_html('Giving List','church-admin').'</a><h3>'.esc_html(__('Template content','church-admin')).'</h3></p>'.wp_kses_post( wpautop( $template ) ).'</div>';
    }
    else{

        echo'<form action="" method="POST">';
        echo'<p>'.esc_html(__('use HTML and shortcodes [name] for donor name and [donations] which produces a table of donations for a particular gift','church-admin'));
        echo'<div class="church-admin-form-group"><label>'.__('Donation receipt message template','church-admin').'</label>';
		
		echo'<textarea name="template" class="church-admin-form-control">';
		if(!empty($template)){ echo wp_kses_post($template);}
		echo '</textarea></div>'."\r\n";
        echo'<p><input type="hidden" name="save-giving-template" value="1"><input type="submit" class="button-primary" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';
    }

}