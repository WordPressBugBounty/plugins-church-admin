<?php 

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_pledge_totals($year_form = TRUE)
{
    global $wpdb,$church_admin_url;
    
    
    $out = '';
    /***************************************
     * Access checks for this function
     ***************************************/
    if(!church_admin_level_check('Giving')){
        $out.= '<p>'.esc_html( __( 'Only users with "giving" permission have access' , 'church-admin' ) ).'</p>';
        return $out;
    }
     $licence = get_option('church_admin_app_new_licence');;
    if(empty($licence)){
        $out.='<p><a href="'.esc_url(admin_url().$church_admin_url.'&action=app').'">'.esc_html( __('Please upgrade to premium to use this feature','church-admin' ) ).'</a></p>';
        return $out;
    }
    /***************************************
     * Safe to proceed
     ***************************************/
    //sanitize
    $pledge_year = !empty($_REQUEST['pledge_year']) ? church_admin_sanitize($_REQUEST['pledge_year']) : wp_date('Y');
    //validate
    if(!church_admin_int_check($pledge_year)){
        $pledge_year = wp_date('Y');
    }

    /****************************************
     * Other Variables
     ***************************************/
    $pledge_totals = $giving_totals = array();

     //grab currency format
     $premium = get_option( 'church_admin_payment_gateway' );
     $currency_symbol = !empty($premium['currency_symbol']) ? $premium['currency_symbol'] : '$';

    $funds=get_option('church_admin_giving_funds');
    if(empty($funds)){
        $funds = __('General','church-admin');
    }
    /****************************************
     * Get data
     ***************************************/
    foreach($funds AS $key => $fund){
        $pledge_amount = $wpdb->get_var('SELECT SUM(amount) FROM '.$wpdb->prefix.'church_admin_pledge WHERE pledge_year = "'.esc_sql($pledge_year).'" AND fund = "'.esc_sql($fund).'"');
        $pledge_totals[$fund] = !empty( $pledge_amount ) ? $pledge_amount : 0;

        $giving_amount = $wpdb->get_var('SELECT SUM(b.gross_amount) FROM '.$wpdb->prefix.'church_admin_giving a, '.$wpdb->prefix.'church_admin_giving_meta b WHERE YEAR(a.donation_date) = "'.esc_sql($pledge_year).'" AND b.fund = "'.esc_sql($fund).'" AND a.giving_id = b.giving_id');
        $giving_totals [$fund] = !empty( $giving_amount ) ? $giving_amount : 0;

    }
    //Unsassigned
    $pledge_totals['unassigned'] =  $giving_totals['unassigned'] =0;
    $pledge_totals['unassigned'] = $wpdb->get_var('SELECT SUM(amount) FROM '.$wpdb->prefix.'church_admin_pledge WHERE pledge_year = "'.esc_sql($pledge_year).'" AND fund = null');
    $giving_totals['unassigned'] = $wpdb->get_var('SELECT SUM(b.gross_amount) FROM '.$wpdb->prefix.'church_admin_giving a, '.$wpdb->prefix.'church_admin_giving_meta b WHERE YEAR(a.donation_date) = "'.esc_sql($pledge_year).'" AND b.fund = null AND a.giving_id = b.giving_id');
    
    /****************************************
     * Output Data
     ***************************************/
    $out .= '<h3>'.esc_html( sprintf( __('Giving Totals for %1$s','church-admin') , $pledge_year ) ).'</h3>'."\r\n";
    $out .='<div class="pledges-total">';
    if(!empty($year_form)){

        $out.='<div class="tablenav top"><div class="alignleft actions"><form action="" method="POST">';
        //$out .= '<label>'.esc_html( __('Year', 'church-admin' ) ).'</label>';
        $out.='<select  name="pledge_year" >';
        $fiveyearsago = date( 'Y', strtotime( "-5years" ));
        $fiveyearstime = date( 'Y', strtotime( "+5years" ) );
        $current_year = !empty( $data->pledge_year ) ?(int)$data->pledge_year : wp_date('Y');

        for($year = $fiveyearsago ; $year<=$fiveyearstime; $year++ ){
            $out .= '<option value="'.(int)$year.'" '.selected($year,$pledge_year,FALSE).'>'.(int)$year.'</option>';
        }
        $out.='</select>';
        $out .= '<input class="button-primary" type="submit" value="'. esc_attr( __('Show year','church-admin')).'" /></form></div></div>';
    }

    $table_header = '<tr><th>'. esc_html( __( 'Fund','church-admin' ) ).'</th><th>'. esc_html( __( 'Pledged','church-admin' ) ).'</th><th>'. esc_html( __( 'Given','church-admin' ) ).'</th></tr>';

    
    $out .= '<table class="church_admin striped"><thead>'.$table_header.'</thead>'."\r\n";

    foreach($funds AS $key => $fund)
    {
        $out .= '<tr><td>'.esc_html( $fund ).'</td><td>'.esc_html( $currency_symbol.number_format_i18n($pledge_totals[$fund],2 ) ).'</td><td>'.esc_html( $currency_symbol.number_format_i18n($giving_totals[$fund],2 ) ).'</td></tr>';

    }
    //handle unassigned
    if(!empty( $pledge_totals['unassigned']) || !empty( $giving_totals['unassigned'] )  ){
        $out .= '<tr><td>'.esc_html( __('Unassigned','church-admin') ).'</td><td>'.esc_html( $currency_symbol.number_format_i18n($pledge_totals['unassigned'],2 ) ).'</td><td>'.esc_html( $currency_symbol.number_format_i18n($giving_totals['unassigned'],2 ) ).'</td></tr>';
    }


    $out .= '<tr><th scope="row">'. esc_html( __( 'Totals' , 'church-admin' ) ).'</th><td>'. esc_html( $currency_symbol.number_format_i18n(array_sum( $pledge_totals ), 2 ) ).'</td><td>'. esc_html( $currency_symbol.number_format_i18n( array_sum( $giving_totals ), 2) ).'</td></tr>';

    $out .= '</tbody>'."\r\n";
    //$out .= '<tfoot>'.$table_header.'</tfoot>'."\r\n";
    $out .= '</table>'."\r\n";
    $out .='</div>'."\r\n";
    return $out;
}

function church_admin_pledges_list()
{
    global $wpdb;

    
    $out = church_admin_pledge_totals(TRUE);


    $out .= '<h2>'.esc_html('Pledges','church-admin').'</h2>';
    /***************************************
     * Access checks for this function
     ***************************************/
    if(!church_admin_level_check('Giving')){
        $out.= '<p>'.esc_html( __( 'Only users with "giving" permission have access' , 'church-admin' ) ).'</p>';
        return $out;
    }
     $licence = get_option('church_admin_app_new_licence');;
    if(empty($licence)){
        $out.='<p><a href="'.esc_url(admin_url().$church_admin_url.'&action=app').'">'.esc_html( __('Please upgrade to premium to use this feature','church-admin' ) ).'</a></p>';
        return $out;
    }
    /***************************************
     * Safe to proceed
     ***************************************/

    $out .= '<p><a class="button-primary" href="'.esc_url( wp_nonce_url( 'admin.php?page=church_admin/index.php&action=edit-pledge', 'edit-pledge' ) ).'">'.__('Add a pledge','church-admin' ).'</a></p>';

    //grab currency format
    $premium = get_option( 'church_admin_payment_gateway' );
    $currency_symbol = !empty($premium['currency_symbol']) ? $premium['currency_symbol'] : '$';
    

    $sql = 'SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS ordered_name, a.*, b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_pledge b WHERE a.people_id = b.people_id ORDER BY b.pledge_year DESC, ordered_name ASC';
    $results = $wpdb->get_results( $sql );
    if( !empty( $results ) )
    {
        $table_header = '<tr><th>'.esc_html( __('Edit' , 'church-admin' ) ).'</th>
        <th>'.esc_html( __('Delete' , 'church-admin' ) ).'</th>
        <th>'.esc_html( __('Name' , 'church-admin' ) ).'</th>
        <th>'.esc_html( __('Fund' , 'church-admin' ) ).'</th>
        <th>'.esc_html( __('Amount' , 'church-admin' ) ).'</th>
        <th>'.esc_html( __('Year' , 'church-admin' ) ).'</th></tr>';

        $out .=  '<table class="widefat striped">'."\r\n";
        $out .= '<thead>'.$table_header.'</thead>'."\r\n";
        $out .= '<tbody>'."\r\n";

        foreach( $results AS $row ){

            $edit ='<a class="button-primary" href="'.esc_url( wp_nonce_url( 'admin.php?page=church_admin/index.php&action=edit-pledge&pledge_id='.(int)$row->pledge_id, 'edit-pledge' ) ).'">'.__('Edit','church-admin' ).'</a>';
            $delete = '<a class="button-secondary" href="'.esc_url( wp_nonce_url( 'admin.php?page=church_admin/index.php&action=delete-pledge&pledge_id='.(int)$row->pledge_id, 'delete-pledge' ) ).'">'.__('Delete','church-admin' ).'</a>';
            $name = church_admin_formatted_name( $row );
            $pledge = $currency_symbol.$row->amount;

            $out .= '<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($name).'</td><td>'.esc_html($row->fund).'</td><td>'.esc_html($pledge).'</td><td>'.esc_html( $row->pledge_year ).'</td><tr>'."\r\n";

        }


        $out .='</tbody>'."\r\n";
        $out .='<tfoot>'.$table_header.'</tfoot>'."\r\n";
        $out .='</table>'."\r\n";

    }
    else
    {
        $out.= '<p>'.esc_html( __('No pledges found', 'church-admin' ) ).'</p>';
    }
    return $out;
}

function church_admin_edit_pledge($pledge_id)
{
    $out = '<h2>'.esc_html('Add/Edit pledge','church-admin').'</h2>';
    /***************************************
     * Access checks for this function
     ***************************************/
    if(!church_admin_level_check('Giving')){
        $out.= '<p>'.esc_html( __( 'Only users with "giving" permission have access' , 'church-admin' ) ).'</p>';
        return $out;
    }
     $licence = get_option('church_admin_app_new_licence');;
    if(empty($licence)){
        $out.='<p><a href="'.esc_url(admin_url().$church_admin_url.'&action=app').'">'.esc_html( __('Please upgrade to premium to use this feature','church-admin' ) ).'</a></p>';
        return $out;
    }
    /***************************************
     * Safe to proceed
     ***************************************/

    global $wpdb;
    $people = church_admin_people_array();
   
    if(!empty($_POST['save-pledge'])){
        church_admin_debug('Saving pledge');
      
        /***************************************
        * Process form
        ***************************************/
        //sanitize data
        $people_id = !empty($_POST['people_id']) ? church_admin_sanitize( $_POST['people_id'] ) : null;
        $amount = !empty($_POST['amount']) ? church_admin_sanitize( $_POST['amount'] ) : null;
        $fund = !empty($_POST['fund']) ? church_admin_sanitize( $_POST['fund'] ) : __('General','church-admin');
        $year = !empty($_POST['pledge_year']) ? church_admin_sanitize( $_POST['pledge_year'] ) : null;

        //validate
        if(empty( $people_id ) || !church_admin_int_check( $people_id ) || empty($people[$people_id])){
            $out .= '<p>'. esc_html( __('Person not in directory','church-admin')) .'</p>';
            return $out;
        }
        if(empty( $amount ) && !is_numeric( $amount ) )
        {
            $out .= '<p>'. esc_html( __('Amount not recognised','church-admin') ) .'</p>';
            return $out;
        }
        if( empty( $year ) || !church_admin_int_check($year)){
            $out .= '<p>'. esc_html( __('Year not recognised','church-admin')) .'</p>';
            return $out;
        }

        if(empty($pledge_id)){
            $pledge_id = $wpdb->get_var('SELECT pledge_id FROM '.$wpdb->prefix.'church_admin_pledge WHERE people_id = "'.(int)$people_id.'" AND amount = "'.esc_sql($amount).'" AND fund = "'.esc_sql($fund).'" AND pledge_year = "'.(int)$year.'"');
        }
        if(empty($pledge_id))
        {
            //insert
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_pledge (people_id,amount,fund,pledge_year) VALUES("'.(int)$people_id.'","'.esc_sql($amount).'","'.esc_sql($fund).'","'.(int)$year.'")');

        }
        else
        {
            //update
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_pledge SET people_id = "'.(int)$people_id.'" , amount = "'.esc_sql($amount).'" , fund = "'.esc_sql($fund).'" , pledge_year = "'.(int)$year.'" WHERE pledge_id = "'.(int)$pledge_id.'"');
        }
        //church_admin_debug($wpdb->last_query);
        $out .= '<div class="notice notice-success"><h2>'. esc_html( __('Pledge saved', 'church-admin') ).'</h2></div>';
        $out .='<p><a href="'. esc_url( wp_nonce_url( 'admin.php?page=church_admin/index.php&action=pledges', 'pledge-list' ) ) .'">'.esc_html( __( 'Pledges list' , 'church-admin' ) ).'</a></p>';
        return $out;

    }
    else
    {
        /***************************************
        * Show form
        ***************************************/
        if( !empty( $pledge_id ) && church_admin_int_check( $pledge_id ) )
        {
            $data = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_pledge WHERE pledge_id = "'.(int)$pledge_id.'"');
        }
        $current_people_id = !empty($data->people_id) ? (int)$data->people_id : null;
        //get people
       
        if(empty($people))
        {
            $out.= '<p>'.esc_html( __('The directory needs some people in it first','church-admin' ) ).'</p>';
            return $out;
        }

        $out .= '<form action="" method="POST">';

        //name
        $out .= '<div class="church-admin-form-group"><label>'.esc_html( __('Name', 'church-admin' ) ).'</label>';
        //create people dropdown
        $out.='<select class="church-admin-form-control" name="people_id">';
        foreach($people as $id => $name){
            $out .= '<option value="'.(int)$id.'" '.selected( $current_people_id, $id, FALSE ).'>'.esc_html( $name ).'</option>';
        }
        $out .= '<select></div>';

        //amount
        $out .= '<div class="church-admin-form-group"><label>'.esc_html( __('Amount', 'church-admin' ) ).'</label>';
        $out .= '<input class="church-admin-form-control" type="number" name="amount" ';
        if(!empty($data->amount)) {
        $out .= ' value="'.floatval($data->amount).'" ';
        }
        $out .= '/></div>';

        //fund
        $funds=get_option('church_admin_giving_funds');
        if(!empty( $funds) )
        {
            $out.='<div class="church-admin-form-group">
                <label>'.esc_html( __('Fund','church-admin' ) ).'</label><select class="church-admin-form-control" name="fund">';
            foreach( $funds AS $key=>$fund)
            {
                $out.='<option value="'.esc_html( $fund).'">'.esc_html( $fund).'</option>';
            }
            $out.='</select></div>';
        }

        //year
        $out .= '<div class="church-admin-form-group"><label>'.esc_html( __('Year', 'church-admin' ) ).'</label>';
        $out.='<select class="church-admin-form-control" name="pledge_year" >';
        $fiveyearsago = date( 'Y', strtotime( "-5years" ));
        $fiveyearstime = date( 'Y', strtotime( "+5years" ) );
        $current_year = !empty( $data->pledge_year ) ?(int)$data->pledge_year : wp_date('Y');

        for($year = $fiveyearsago ; $year<=$fiveyearstime; $year++ ){
            $out .= '<option value="'.(int)$year.'" '.selected($year,$current_year,FALSE).'>'.(int)$year.'</option>';
        }
        $out.='</select></div>';
       //submit
       $out .='<input type="hidden" name="save-pledge" value="1" />';
       $out .= '<p><input class="button-primary" type="submit" value="'. esc_attr( __('Save', 'church-admin' ) ) .'" /></p>';
       $out .='</form>';

    }

    return $out;
}

function church_admin_delete_pledge($pledge_id)
{
    global $wpdb;

    $out = '<h2>'.esc_html('Delete pledge','church-admin').'</h2>';
    /***************************************
     * Access checks for this function
     ***************************************/
    if(!church_admin_level_check('Giving')){
        $out.= '<p>'.esc_html( __( 'Only users with "giving" permission have access' , 'church-admin' ) ).'</p>';
        return $out;
    }
     $licence = get_option('church_admin_app_new_licence');;
    if(empty($licence)){
        $out.='<p><a href="'.esc_url(admin_url().$church_admin_url.'&action=app').'">'.esc_html( __('Please upgrade to premium to use this feature','church-admin' ) ).'</a></p>';
        return $out;
    }
    if( empty( $pledge_id ) || !church_admin_int_check( $pledge_id ) ){
        $out.= '<p>'.esc_html( __( 'Pledge ID to delete missing.' , 'church-admin' ) ).'</p>';
        return $out;
    }
    /***************************************
     * Safe to proceed
     ***************************************/
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_pledge WHERE pledge_id = "'.(int)$pledge_id.'"');

    $out.='<div class="notice notice-success"><h2>'.esc_html( __( 'Pledge deleted', 'church-admin' ) ).'</h2></div>';
    $out.='<p><a href="'. esc_url( wp_nonce_url( 'admin.php?page=church_admin/index.php&action=pledges', 'pledge-list' ) ) .'">'.esc_html( __( 'Pledges list' , 'church-admin' ) ).'</a></p>';

    return $out;

}