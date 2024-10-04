<?PHP
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_safeguarding_main(){
    echo '<h2>'.esc_html( __('Safeguarding','church-admin' ) ).'</h2>';
    church_admin_list_safeguarding_fields();
    church_admin_safeguarding_ministries();
    church_admin_safeguarding_status();
}




function church_admin_list_safeguarding_fields()
{

	global $wpdb;
	//$wpdb->show_errors;
   echo '<h2>'.esc_html( __('Safeguarding fields','church-admin' ) ).'</h2>';
	
	echo '<p><a href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-safeguarding-field','edit-safeguarding-field')).'">'.esc_html( __('Add a safeguarding field','church-admin' ) ).'</a></p>';
	$safeguarding_fields=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE section="safeguarding" ORDER BY custom_order,name');
	if(!empty( $safeguarding_fields) )
	{

		$thead='<tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Safeguarding field name','church-admin' ) ).'</th><th>'.esc_html( __('Custom field type','church-admin' ) ).'</th><th>'.esc_html( __('Default','church-admin' ) ).'</th></tr>';
		echo '<table class="widefat striped"><thead>'.$thead.'</thead><tbody>';
		foreach( $safeguarding_fields AS $safeguarding_field)
		{
			if(!empty( $safeguarding_field->default_value) )  {$default=$safeguarding_field->default_value;}else{$default="";}
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-safeguarding-field&amp;ID='.(int)$safeguarding_field->ID,'edit-safeguarding-field').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
			$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-safeguarding-field&amp;ID='.(int)$safeguarding_field->ID,'delete-safeguarding-field').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
			echo '<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html( $safeguarding_field->name).'</td><td>'.esc_html( $safeguarding_field->type).'</td><td>'.esc_html( $default).'</td></tr>';
		}
		echo '</tbody><tfoot>'.$thead.'</tfoot></table>';
	}
	return;
}



/**
 * Edit Custom Field
 *
 * @param
 * @param
 *
 * @author andy_moyle
 *
 */
function church_admin_edit_safeguarding_field( $ID)
{
    global $wpdb;
    $wpdb->show_errors;
   $data= new stdClass();

    if(!empty( $ID) )$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE ID="'.(int)$ID.'"');
   

    if(!empty( $_POST['save_custom_field'] ) )
    {
       
   
       $sqlsafe=array();
       foreach( $_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(sanitize_text_field( stripslashes($value) ) );
       $order=!(empty($_POST['order']))?(int)$_POST['order']:1;
       if ( empty( $sqlsafe['custom-field-default'] ) )$sqlsafe['custom-field-default']="";
       if(!empty( $_POST['show-me'] ) )  {$show_me=1;}else{$show_me=0;}
        if ( empty( $ID) )$ID=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE name="'.$sqlsafe['custom-field-name'].'" AND type="'.$sqlsafe['custom-field-type'].'"  AND default_value="'.$sqlsafe['custom-field-default'].'" AND show_me="'.$show_me.'"');
       if ( empty( $ID) )
       {
           //insert
           $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields (name,type,section,default_value,show_me,custom_order) VALUES("'.$sqlsafe['custom-field-name'].'" ,"'.$sqlsafe['custom-field-type'].'","safeguarding","'.$sqlsafe['custom-field-default'].'","'.(int)$show_me.'","'.(int)$order.'")');
       }
       else
       {
           //update
           $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_custom_fields SET name="'.$sqlsafe['custom-field-name'].'" , type="'.$sqlsafe['custom-field-type'].'" , section="safeguarding" , default_value="'.$sqlsafe['custom-field-default'].'", show_me="'.(int)$show_me.'",custom_order="'.(int)$order.'" WHERE ID = "'.(int)$ID.'"');
       }
       
       if(!empty( $_POST['custom-all'] ) )
       {
           if ( empty( $_POST['custom-field-default'] ) )  {$default="";}else{$default=esc_sql(church_admin_sanitize( $_POST['custom-field-default'] ) );}
           $people=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people');
           if(!empty( $people) )
           {
                   foreach( $people AS $peep)
                   {
                           $check=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE people_id="'.(int)$peep->people_id.'" AND custom_id="'.(int)$ID.'" ');
                           $sql='INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$peep->people_id.'","'.(int)$ID.'","'.$default.'","safeguarding")';

                           if ( empty( $check) )$wpdb->query( $sql);
                   }
           }
       }
        echo '<div class="notice notice-success"><h2>'.esc_html( __('Safeguarding field saved','church-admin' ) ).'</h2></div>';
        church_admin_list_safeguarding_fields();
     }
    else
    {
        echo '<h2>'.esc_html( __('Edit safeguarding field','church-admin' ) ).'</h2>';
        echo '<form action="" method="POST">';
        echo '<table class="form-table">';
        echo '<tr><th scope="row">'.esc_html( __('Safeguarding item','church-admin' ) ).'</th><td><input type="text" name="custom-field-name" ';
        if(!empty( $data->name) )echo ' value="'.esc_html( $data->name).'" ';
        echo '/>';

      
        if ( empty( $data->type) )$data->type='';
        echo '<tr><th scope="row">'.esc_html( __('Safeguarding field type','church-admin' ) ).'</th><td><select name="custom-field-type" class="custom-type"><option value="boolean" '.selected('boolean',$data->type,FALSE).'>'.esc_html( __('Yes/No','church-admin' ) ).'</option><option value="date" '.selected('date',$data->type,FALSE).'>'.esc_html( __('Date','church-admin' ) ).'</option><option value="text" '.selected('text',$data->type,FALSE).'>'.esc_html( __('Text field','church-admin' ) ).'</option></select></td></tr>';
		
       if(!empty( $data->type) )
       {
           switch( $data->type)
           {
               case 'text':
                   $text='style="display:table-row"';
                   $boolean='style="display:none"';
                   $booleanField=' disabled="disabled"';
                   $textField='';
               break;
               case 'boolean':
                   $text='style="display:none"';
                   $textField=' disabled="disabled"';
                   $boolean='style="display:table-row"';
                   $booleanField=' disabled="disabled"';
               break;
               
               default:
                   $text='style="display:none"';
               
                   $boolean='style="display:none"';
                   $textField=$booleanField=' disabled="disabled"';
               break;
           }
           
           echo '<tr class="boolean" '.$boolean.'><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th>';
           echo '<td><select name="custom-field-default" '.$booleanField.' class="boolean-default"><option value="1" ';
           if(!empty( $data->default_value) )echo ' selected="selected" ';
           echo '>'.esc_html( __('Yes','church-admin' ) ).'</option><option value="0" ';
           if(isset( $data->default_value)&& $data->default_value=="0")echo ' selected="selected" ';
           echo '>'.esc_html( __('No','church-admin' ) ).'</option></select></td></tr>';

           echo '<tr class="text"'.$text.'><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th>';
           echo '<td ><input type="text" class="text-default" '.$textField.' name="custom-field-default" ';
           if(!empty( $data->default_value) )echo ' value="'.esc_html( $data->default_value).'" ';
           echo '/></td></tr>';
           
       }
       else {
           echo '<tr class="boolean" style="display:table-row"><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th><td><select  class="boolean-default" name="custom-field-default"><option value="1" ';
           if(!empty( $data->default_value) )echo ' selected="selected" ';
           echo '>'.esc_html( __('Yes','church-admin' ) ).'</option><option value="0" ';
           if(isset( $data->default_value)&& $data->default_value=="0")echo ' selected="selected" ';
           echo '>'.esc_html( __('No','church-admin' ) ).'</option></select></td></tr>';

           echo '<tr class="text"  style="display:none"><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th><td><input disabled="disabled" type="text" class="text-default" name="custom-field-default" ';
           if(!empty( $data->default_value) )echo ' value="'.esc_html( $data->default_value).'" ';
           echo '/></td></tr>';
           
       }
     
    
       echo '<script>
               jQuery(document).ready(function( $)  {
                   $(".custom-type").change(function()  {
                           var val=$(this).val();
                           console.log("type changed to " +val);
                           switch(val)
                           {
                               case "boolean":
                                   $(".boolean").show();
                                   $(".text").hide();
                                   $(".text-boolean").prop("disabled", true);
                                   $(".text-boolean").prop("disabled", false);
                               break;
                               case "text":
                                   $(".boolean").hide();
                                   $(".text").show();
                                   $(".text-default").prop("disabled", false);
                                   $("..boolean-default").prop("disabled", true);
                                   break;
                               case "date":
                                   $(".boolean").hide();
                                   $(".text").hide();
                                   $(".all").hide();
                                   $(".text-default").prop("disabled", true);
                                   $(".boolean-default").prop("disabled", true);
                               break;
                           }
                       });
                   $(".custom-section").change(function()  {
                           console.log("Change of section");
                           var val=$(".custom-section option:selected").val();
                           switch(val)
                           {
                               case "giving":
                                   $(".all").hide();
                                   $(".show_me").hide();
                               break;
                               default:
                                   $(".all").show();
                                   $(".show_me").show();
                               break;
                           }
                   });
               
               
           });
       </script>';
        echo'<tr><td>&nbsp;</td><td><input type="hidden" name="save_custom_field" value="yes" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr></table></form>';
    }
    return ;


}
/**
* Delete Safeguarding Field
*
* @param
* @param
*
* @author andy_moyle
*
*/
function church_admin_delete_safeguarding_field( $ID)
{
    global $wpdb;
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$ID.'"');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE type="safeguarding" AND ID="'.(int)$ID.'"');
    echo'<div class="notice notice-success"><h2>'.esc_html( __('Safeguarding field deleted','church-admin' ) ).'</h2></div>';
    echo church_admin_list_safeguarding_fields();
    return;
}

/**
* Show Safeguarding Status
*
* @param
* @param
*
* @author andy_moyle
*
*/
function church_admin_safeguarding_status()
{
    global $wpdb;
    echo'<h2>'.esc_html( __('Safeguarding status','church-admin' ) ).'</h2>';

    //get safeguarding ministries
    $ministries=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE safeguarding=1 ORDER BY ministry');
    if(empty($ministries)){
        echo'<p>'.esc_html( __('No ministries require safeguarding','church-admin' ) ).'</p>';
        return;

    }
    //get safeguarding fields
    $safeguarding=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE section="safeguarding" ORDER BY custom_order ASC');
    if(empty($safeguarding)){
        echo'<p>'.esc_html( __('No safeguarding tasks setup yet','church-admin' ) ).'</p>';
        return;

    }
    //table header
    $colspan=2;
    $table_header='<tr><th scope="row">'.esc_html( __('Edit','church-admin' ) ).'</th><th scope="row">'.esc_html( __( 'Name' , 'church-admin' ) ).'</th>';
    foreach($safeguarding AS $sg){
        $table_header.='<th scope="row">'. esc_html($sg->name).'</th>';
        $colspan++;
    }
    $table_header.='</tr>';

    echo'<table class="widefat striped bordered"><thead>'.$table_header.'</thead><tbody>';

    foreach($ministries AS $ministry){

        //output ministry name
        echo'<tr><th scope="row" colspan="'.(int)$colspan.'"><strong>'. esc_html( $ministry->ministry ).'</strong></th></tr>';
        //get people in ministry
        $people = $wpdb->get_results('SELECT a.people_id, b.* FROM '. $wpdb->prefix.'church_admin_people_meta'.' a, '.$wpdb->prefix.'church_admin_people b WHERE a.people_id=b.people_id AND  a.meta_type="ministry" AND a.ID="'.(int)$ministry->ID.'"');
        if(empty($people))
        {
            echo'<tr><td colspan="'.(int)$colspan.'">'.esc_html( __("No one doing that ministry yet",'church-admin' ) ).'</td></tr>';
            continue;
        }
        foreach($people AS $person){
            
            $name=church_admin_formatted_name($person);
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-person-safeguarding&amp;people_id='.(int)$person->people_id,'edit-person-safeguarding').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';;
            echo'<tr><td>'.$edit.'</td><td>'.esc_html($name).'</td>';
            foreach($safeguarding AS $sg)
            {
                $data=$wpdb->get_var('SELECT `data` FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE people_id="'.(int)$person->people_id.'" AND custom_id="'.(int)$sg->ID.'"');
                switch($sg->type)
                {
                    case 'date':
                        $data = mysql2date(get_option('date_format'),$data);
                    break;
                    case 'boolean':
                        if(!empty($data)){
                            $data=__('Yes','church-admin');
                        }
                        else{
                            $data = __('No','church-admin');
                        }

                }   
                echo'<td>'.esc_html($data).'</td>';
            }
            echo'</tr>';
        }
    }   
    echo'</tbody></table>';

}

function church_admin_edit_person_safeguarding( $people_id )
{
    //initialise 
    global $wpdb;
   
    //check for people_id
    if( empty( $people_id ) || !church_admin_int_check( $people_id ) ) {
        echo'<div class="notice notice-warning"><h2>'.esc_html( __( 'No-one specified to edit' , 'church-admin' ) ).'</h2></div>';
        return;
    }

    //grab person's details
    $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');

    if( empty( $person ) ) {
        echo'<div class="notice notice-warning"><h2>'.esc_html( __( 'No-one specified to edit' , 'church-admin' ) ).'</h2></div>';
        return;
    }

    

    //get safeguarding fields
    $safeguarding=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE section="safeguarding" ORDER BY custom_order ASC');
    if(empty($safeguarding)){
        echo'<p>'.esc_html( __('No safeguarding tasks setup yet','church-admin' ) ).'</p>';
        return;

    }
   
    

    if(!empty($_POST['save-safeguarding']))
    {
       
        //delete current data
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE section="safeguarding" AND people_id="'.(int)$people_id.'"');

        $sql='INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES ';
        foreach($safeguarding AS $sg){
            switch($sg->type){
                case 'boolean':
                    if(!empty($_POST['custom-'.(int)$sg->ID])){
                        $values[]='("'.(int)$people_id.'","'.(int)$sg->ID.'",1,"safeguarding")';
                    }
                    else
                    {
                        $values[]='("'.(int)$people_id.'","'.(int)$sg->ID.'",0,"safeguarding")';
                    }
                break;
                case 'text':
                    $text=!empty($_POST['custom-'.(int)$sg->ID])?sanitize_text_field(stripslashes($_POST['custom-'.(int)$sg->ID] ) ):null;
                    $values[]='("'.(int)$people_id.'","'.(int)$sg->ID.'","'.esc_sql($text).'","safeguarding")';
                break;
                case 'date':
                    $date=!empty($_POST['custom-'.(int)$sg->ID])?sanitize_text_field(stripslashes($_POST['custom-'.(int)$sg->ID] ) ):null;
                    $check=church_admin_checkdate( $date);
                    if(!empty($check)){
                        $values[]='("'.(int)$people_id.'","'.(int)$sg->ID.'","'.esc_sql($date).'","safeguarding")';
                    }
                break;
            }
        
        }
        $wpdb->query($sql.implode(",",$values));
        echo'<div class="notice notice-success"><h2>'.esc_html( __('Safeguarding update saved','church-admin' ) ).'</h2></div>';
        echo'<p><a href="admin.php?page=church_admin/index.php&action=safeguarding">'.esc_html( __('Back to safeguarding list','church-admin' ) ).'</a></p>';
    }
    //title
    $name = church_admin_formatted_name( $person );
    echo'<h2>'.esc_html( sprintf( __( 'Edit Safeguarding Information for %1$s' , 'church-admin' ), $name ) ).'</h2>';
    echo'<form action="admin.php?page=church_admin/index.php&action=edit-person-safeguarding&people_id='.(int)$people_id.'" method="POST">';
    wp_nonce_field('edit-person-safeguarding');
    
			
    foreach($safeguarding AS $field){

        $dataField='';
	    if(!empty( $people_id) )$dataField=$wpdb->get_var('SELECT data FROM '.$wpdb->prefix.'church_admin_custom_fields_meta' .' WHERE people_id="'.(int)$people_id.'" AND custom_id="'.(int)$field->ID.'"');

        echo '<div class="church-admin-form-group"><label >'.esc_html( $field->name ).'</label>';
        switch( $field->type )
        {
            case 'boolean':
                echo '<input type="radio" data-name="custom-'.(int)$field->ID.'" class="church-admin-form-control"  value="1" name="custom-'.(int)$field->ID.'" ';
                if (isset( $dataField)&&$dataField==1){
                    echo 'checked="checked" ';
                }
                echo '>'.esc_html( __( 'Yes','church-admin') ).'<br> <input type="radio" data-name="custom-'.(int)$field->ID.'" class="church-admin-form-control" value="0" name="custom-'.(int)$field->ID.'" ';
                if (isset( $dataField)&& $dataField==0){
                    echo  'checked="checked" ';
                }
                echo '>'.esc_html( __( 'No','church-admin') );
                break;
            case'text':
                echo '<input type="text" data-name="custom-'.(int)$field->ID.'" class="church-admin-form-control"  name="custom-'.(int)$field->ID.'" ';
                if(!empty( $dataField)||isset( $field->default ) ){
                    echo ' value="'.esc_html( $dataField).'"';
                }
                echo '/>';
            break;
            case'date':
                echo church_admin_date_picker( $dataField,'custom-'.(int)$field->ID,FALSE,1910,date('Y'),'custom-'.(int)$field->ID,'custom-'.(int)$field->ID);

            break;
        }
        echo '</div>';
    }
    echo '<p><input type="hidden" name="save-safeguarding" value="yes" /><input class="button-primary" type="submit" value="'.esc_html(__('Save','church-admin')).'" /></p></form>';

}