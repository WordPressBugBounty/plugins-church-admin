<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_inventory_list()
{
    global $wpdb;
    $out='<h2>'.esc_html(__('Inventory','church-admin') ).'</h2>';
    $out.='<p>'.esc_html(__('Used for things like church keys you want to keep track of','church-admin')).'</p>';
    $out.='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-inventory','edit-inventory').'">'.esc_html( __('Add item','church-admin' ) ).'</a></p>';
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_inventory ORDER BY item ASC');
    if(!empty($results))
    {
        $theader='<tr><th class="column-primary">'.esc_html( __('Item','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Description','church-admin' ) ).'</th><th>'.esc_html( __('People','church-admin' ) ).'</th></tr>'."\r\n";
        $out.='<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead>'."\r\n".'<tfoot>'.$theader.'</tfoot>'."\r\n".'<tbody>'."\r\n";
        foreach($results AS $row){

            
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-inventory&amp;id='.(int)$row->inventory_id,'edit-inventory').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-inventory&amp;id='.(int)$row->inventory_id,'delete-inventory').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            $people='';
            $people=church_admin_get_people_meta_list('inventory',$row->inventory_id);
            
            $out.='<tr>
                <td data-colname="'.esc_html( __('Item','church-admin' ) ).'" class="column-primary">'.esc_html( $row->item).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                <td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
                <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
                <td data-colname="'.esc_html( __('Description','church-admin' ) ).'">'.esc_html( $row->description ).'</td>
                <td data-colname="'.esc_html( __('People','church-admin' ) ).'">'.esc_html( $people ).'</td>
            </tr>'."\r\n";
        }
        $out.='</tbody></table>'."\r\n";


    }
    return $out;

}

function church_admin_delete_inventory($ID=null)
{
    global $wpdb;
    $out='';
    if(empty($ID))
    {
        $out.='div class="notice notice-danger">'.esc_html( __('Inventory item not found','church-admin' ) ).'</div>';
        $out.=church_admin_inventory_list();
        return;
    }
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_inventory WHERE inventory_id="'.(int)$ID.'"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="inventory" AND ID="'.(int)$ID.'"');
    $out.='div class="notice notice-danger">'.esc_html( __('Inventory item deleted','church-admin' ) ).'</div>';
    $out.=church_admin_inventory_list();
}

function church_admin_edit_inventory($ID=null)
{
    global $wpdb;
    $out='<h2>'.esc_html( __( 'Edit inventory' , 'church-admin') ).'</h2>';
    $people=array();
    if( !empty( $ID ) ) {
        $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_inventory WHERE inventory_id="'.(int)$ID.'"');
        $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="inventory" AND ID="'.(int)$ID.'" ORDER BY ordered ASC');
        $inventoryPeople=array();
        if(!empty($people))
        {
            foreach($people AS $person)
            {
                $inventoryPeople[$person->ordered]=$person->people_id;
            }

        }
    }
    
    if(!empty($_POST['save']))
    {
        $item = sanitize_text_field( stripslashes( $_POST['item'] ) );
        $description = sanitize_textarea_field( stripslashes( $_POST['description'] ) );
        for($x=1;$x<=20;$x++){
            if( !empty( $_POST['people-'.$x] ) ){
                $people[$x] = sanitize_text_field( stripslashes( $_POST['people-'.$x] ));
            }
        }
        if(empty($ID)){
            $ID=$wpdb->get_var('SELECT inventory_id FROM '.$wpdb->prefix.'church_admin_inventory WHERE item="'.esc_sql( $item ).'" AND "'.esc_sql( $description ).'"');
        }
        if(empty($ID)){
            //insert
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_inventory (item,description) VALUES("'.esc_sql( $item ).'","'.esc_sql( $description ).'")');
            $ID=$wpdb->insert_id;
        }
        else{
            //update
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_inventory SET item="'.esc_sql( $item ).'" , "'.esc_sql( $description ).'" WHERE inventory_id="'.(int)$ID.'"');

        }

        //add people to meta table
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="inventory" AND ID="'.(int)$ID.'"');
        $values=array();
        foreach($people AS $ordered=>$value){
            $values[]='("inventory","'.esc_sql($value).'","'.(int)$ordered.'","'.(int)$ID.'","'.esc_sql(wp_date('Y-m-d')).'")';
        }
        if(!empty($values)){
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta(meta_type,people_id,ordered,ID,meta_date) VALUES '.implode(",",$values));
        }
       
        $out.='<div class="notice notice-success"><h2>'.esc_html( __('Item saved','church-admin' ) ).'</h2></div>';
        $out.=church_admin_inventory_list();
    }
    else
    {
        $out.='<form action="" method="POST">';
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Inventory item name','church-admin' ) ).'</label>';
        $out.='<input type="text" name="item" class="church-admin-form-control" ';
        if(!empty($data->item)){
            $out.=' value="'.esc_html( $data->item ).'" ';
        }
        $out.='/></div>';
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Description','church-admin' ) ).'</label>';
        $out.= '<textarea name="description" class="church-admin-form-control" >';
        if(!empty($data->description)){
            $out.=wpkes_post($data->description);
        }
        $out.='</textarea></div>';
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('People','church-admin' ) ).'</label></div>';
        for($x=1;$x<=20;$x++)
        {
            $out.='<div class="church-admin-form-group"><label>'.esc_html(sprintf(__('ID #%1$d','church-admin' ) ,$x)).'</label>';
            $out.='<input type="text" class="church-admin-form-control" name="people-'.(int)$x.'" ';
            if(!empty($inventoryPeople[$x]))
            {
                $out.='value="'.esc_html( church_admin_get_person( $inventoryPeople[$x] ) ).'"';
            }
            $out.='</div>';
        }
        $out.='<p><input type="hidden" name="save" value="yes" /> <input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p>';
        $out.='</form>';

    }
    return $out;
}