<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/**
 *
 * Custom fields
 *
 * @param
 *
 *
 * @author andy_moyle
 *
 */
function church_admin_list_custom_fields()
{

	global $wpdb;
	//$wpdb->show_errors;
    $out='<h2>'.esc_html( __('Custom fields','church-admin' ) ).'</h2>';
	
	$out.='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-custom-field&amp;section=people','edit-custom-field').'">'.esc_html( __('Add a custom field','church-admin' ) ).'</a></p>';
	$custom_fields=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE section="household" OR section="people" ORDER BY section,custom_order,name');
	if(!empty( $custom_fields) )
	{
		$out.=__('Drag and Drop to change row display order','church-admin');
		$thead='<tr><th class="column-primary">'.esc_html( __('Custom field name','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Section','church-admin' ) ).'</th><th>'.esc_html( __('Custom field type','church-admin' ) ).'</th><th>'.esc_html( __('Default','church-admin' ) ).'</th><th>'.esc_html( __('First registration only','church-admin') ).'</th></tr>';
		
		$out.='<table  id="sortable" class="widefat striped"><thead>'.$thead.'</thead><tbody class="content">';
		
		foreach( $custom_fields AS $custom_field)
		{
			$onboarding = !empty($custom_field->onboarding)?__('Yes','church-admin'):__('No','church-admin');
			if(!empty( $custom_field->default_value) )  {$default=$custom_field->default_value;}else{$default="";}
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-custom-field&amp;section=people&amp;id='.(int)$custom_field->ID,'edit-custom-field').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
			$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-custom-field&amp;section=people&amp;id='.(int)$custom_field->ID,'delete-custom-field').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
			$out.='<tr  class="sortable" id="'.(int)$custom_field->ID.'">';
			$out.='<td data-colname="'.esc_html( __('Custom field','church-admin' ) ).'" class="column-primary">'.esc_html( $custom_field->name).'</td>';
			$out.='<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>';
			$out.='<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>';
			
			$out.='<td data-colname="'.esc_html( __('Custom field section','church-admin' ) ).'">'.esc_html( $custom_field->section).'</td>';
			$out.='<td data-colname="'.esc_html( __('Custom field type','church-admin' ) ).'">'.esc_html( $custom_field->type).'</td>';
			$out.='<td data-colname="'.esc_html( __('Default value','church-admin' ) ).'">'.esc_html( $default).'</td>';
			$out.='<td data-colname="'.esc_html( __('Onboarding only','church-admin' ) ).'">'.esc_html($onboarding).'</td></tr>';
		}
		$out.='</tbody><tfoot>'.$thead.'</tfoot></table>';
	}
	echo' <script type="text/javascript">

		jQuery(document).ready(function( $) {
	
		var fixHelper = function(e,ui)  {
				ui.children().each(function() {
					$(this).width( $(this).width() );
				});
				return ui;
			};
		var sortable = $("#sortable tbody.content").sortable({
		helper: fixHelper,
		stop: function(event, ui) {
			//create an array with the new order
	
	
					var Order = "order="+$(this).sortable(\'toArray\').toString();
	
	
	
			$.ajax({
				url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=custom_fields",
				type: "post",
				data:  Order,
				error: function() {
					console.log("theres an error with AJAX");
				},
				success: function() {
	
				}
			});}
		});
		$("#sortable tbody.content").disableSelection();
		});
	
	
	
			</script>';
	return $out;
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
 function church_admin_edit_custom_field( $ID)
 {
	 global $wpdb;
	 $wpdb->show_errors;
	$data= new stdClass();
	if(!empty( $ID) )$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE ID="'.(int)$ID.'"');
 	$out='';

 	if(!empty( $_POST['save_custom_field'] ) )
 	{
		
	
		$sqlsafe=array();
		foreach( $_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(church_admin_sanitize( $value ) );
		$onboarding = !empty($_POST['onboarding'])?1:0;
		$options = !empty($_POST['options']) ? array_filter(church_admin_sanitize($_POST['options'])) : array();
		if ( empty( $sqlsafe['custom-field-default'] ) )$sqlsafe['custom-field-default']="";
		if(!empty( $_POST['show-me'] ) )  {$show_me=1;}else{$show_me=0;}
 		if ( empty( $ID) )$ID=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE name="'.$sqlsafe['custom-field-name'].'" AND type="'.$sqlsafe['custom-field-type'].'" AND section="'.$sqlsafe['custom-field-section'].'" AND default_value="'.$sqlsafe['custom-field-default'].'" AND show_me="'.(int)$show_me.'"');
		if ( empty( $ID) )
		{
			//insert
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields (name,type,section,default_value,show_me,options,onboarding) VALUES("'.$sqlsafe['custom-field-name'].'" ,"'.$sqlsafe['custom-field-type'].'","'.$sqlsafe['custom-field-section'].'","'.$sqlsafe['custom-field-default'].'","'.(int)$show_me.'","'.esc_sql(serialize($options)).'","'.esc_sql($onboarding).'")');
		}
		else
		{
			//update
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_custom_fields SET name="'.$sqlsafe['custom-field-name'].'" , type="'.$sqlsafe['custom-field-type'].'" , section="'.$sqlsafe['custom-field-section'].'" , default_value="'.$sqlsafe['custom-field-default'].'", show_me="'.(int)$show_me.'", options = "'.esc_sql(serialize($options)).'", onboarding = "'.esc_sql($onboarding).'"  WHERE ID = "'.(int)$ID.'"');
		}
		
		if(!empty( $_POST['custom-all'] ) )
		{
			if ( empty( $_POST['custom-field-default'] ) )  {$default="";}else{$default=esc_sql(sanitize_text_field( stripslashes($_POST['custom-field-default'] ) ));}
			$people=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people');
			if(!empty( $people) )
			{
					foreach( $people AS $peep)
					{
							$check=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE people_id="'.(int)$peep->people_id.'" AND custom_id="'.(int)$ID.'" ');
							$sql='INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (people_id,custom_id,data,section) VALUES("'.(int)$peep->people_id.'","'.(int)$ID.'","'.$default.'","people")';

							if ( empty( $check) )$wpdb->query( $sql);
					}
			}
		}
 		$out.='<div class="notice notice-success"><h2>'.esc_html( __('Custom field saved','church-admin' ) ).'</h2></div>';
 		$out.=church_admin_list_custom_fields();
  	}
 	else
 	{
 		$out='<h2>'.esc_html( __('Edit custom field','church-admin' ) ).'</h2>';
 		$out.='<form action="" method="POST">';
 		$out.='<table class="form-table">';
 		$out.='<tr><th scope="row">'.esc_html( __('Custom field name','church-admin' ) ).'</th><td><input type="text" name="custom-field-name" ';
 		if(!empty( $data->name) )$out.=' value="'.esc_html( $data->name).'" ';
 		$out.='/>';

		//TYPE
		if ( empty( $data->section) )$data->section='';
		if ( empty( $data->default_value) )$data->default_value='';
 		if ( empty( $data->type) )$data->type='';
 		$out.='<tr><th scope="row">'.esc_html( __('Custom field type','church-admin' ) ).'</th><td><select name="custom-field-type" class="custom-type"><option value="boolean" '.selected('boolean',$data->type,FALSE).'>'.esc_html( __('Yes/No','church-admin' ) ).'</option><option value="date" '.selected('date',$data->type,FALSE).'>'.esc_html( __('Date','church-admin' ) ).'</option><option value="text" '.selected('text',$data->type,FALSE).'>'.esc_html( __('Text field','church-admin' ) ).'</option><option value="radio"'.selected('radio',$data->type,FALSE).'>'.esc_html( __('Radio buttons','church-admin' ) ).'</option>
		 <option value="checkbox"'.selected('checkbox',$data->type,FALSE).'>'.esc_html( __('Checkboxes','church-admin' ) ).'</option>
		 <option value="select"'.selected('select',$data->type,FALSE).'>'.esc_html( __('Select dropdown','church-admin' ) ).'</option></select></td></tr>';
		//SECTION
		if(!empty( $data) )$out.='<tr><th scope="row" colspan=2>'.esc_html( __('Changing from Household to People will require editing people entries for which person in a household the data applies to.','church-admin' ) ).'</th></tr>';
		$out.='<tr><th scope="row">'.esc_html( __('Custom field section','church-admin' ) ).'</th><td><select name="custom-field-section" class="custom-section"><option value="people" '.selected('people',$data->section,FALSE).'>'.esc_html( __('People','church-admin' ) ).'</option><option value="household" '.selected('household',$data->section,FALSE).'>'.esc_html( __('Household','church-admin' ) ).'</option>';
		/* $out.='<option value="event" '.selected('event',$data->section,FALSE).'>'.esc_html( __('Event ticket','church-admin' ) ).'</option>';
		*/
		$out.='<option value="giving" '.selected('giving',$data->section,FALSE).'>'.esc_html( __('Giving','church-admin' ) ).'</option></select></td></tr>';
		if(!empty( $data->type) )
		{
			$option= !empty($data->options) ? maybe_unserialize($data->options):null;
			switch( $data->type)
			{
				case 'radio':
				case 'checkbox':
				case 'select':
					$text='style="display:none"';
					$boolean='style="display:none"';
					$booleanField=' disabled="disabled"';
					$textField=' disabled="disabled"';
					$options='';
				break;
				case 'text':
					$text='style="display:table-row"';
					$boolean='style="display:none"';
					$booleanField=' disabled="disabled"';
					$textField='';
					$options ='style="display:none"';
				break;
				case 'boolean':
					$text='style="display:none"';
					$textField=' disabled="disabled"';
					$boolean='style="display:table-row"';
					$booleanField=' disabled="disabled"';
					$options ='style="display:none"';
				break;
				
				default:
					$text='style="display:none"';
				
					$boolean='style="display:none"';
					$textField=$booleanField=' disabled="disabled"';
					$options ='style="display:none"';
				break;
			}
			
			$out.='<tr class="boolean" '.$boolean.'><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th>';
			$out.='<td><select name="custom-field-default" '.$booleanField.' class="boolean-default"><option value="1" ';
			if(!empty( $data->default_value) )$out.=' selected="selected" ';
			$out.='>'.esc_html( __('Yes','church-admin' ) ).'</option><option value="0" ';
			if(isset( $data->default_value)&& $data->default_value=="0")$out.=' selected="selected" ';
			$out.='>'.esc_html( __('No','church-admin' ) ).'</option></select></td></tr>';

			$out.='<tr class="text" '.$text.'><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th>';
			$out.='<td ><input type="text" class="text-default" '.$textField.' name="custom-field-default" ';
			if(!empty( $data->default_value) )$out.=' value="'.esc_html( $data->default_value).'" ';
			$out.='/></td></tr>';
			//options
			$out.='<tr class="options" '.$options.'><th scope="row">'.esc_html( __('Add upto 20 choices','church-admin' ) ).'</th><td>';
			$options_chosen = !empty($data->options)? unserialize($data->options) : array();
			for($x=0;$x<=20;$x++){
				$out.='<input type="text" name="options[]" ';
				if(!empty($option[$x]))$out.=' value="'.esc_html( $option[$x]).'" ';
				$out.='><br/>';
			}
			$out.='</td></tr>';
			



			
		}
		else {
			$out.='<tr class="boolean" style="display:table-row"><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th><td><select  class="boolean-default" name="custom-field-default"><option value="1" ';
			if(!empty( $data->default_value) )$out.=' selected="selected" ';
			$out.='>'.esc_html( __('Yes','church-admin' ) ).'</option><option value="0" ';
			if(isset( $data->default_value)&& $data->default_value=="0")$out.=' selected="selected" ';
			$out.='>'.esc_html( __('No','church-admin' ) ).'</option></select></td></tr>';

			$out.='<tr class="text"  style="display:none"><th scope="row">'.esc_html( __('Default','church-admin' ) ).'</th><td><input disabled="disabled" type="text" class="text-default" name="custom-field-default" ';
			if(!empty( $data->default_value) )$out.=' value="'.esc_html( $data->default_value).'" ';
			$out.='/></td></tr>';
			//options
			$out.='<tr class="options" style="display:none"><th scope="row">'.esc_html( __('Add up to 20 choices','church-admin' ) ).'</th><td>';
			$options_chosen = !empty($data->options)? unserialize($data->options) : array();
			for($x=0;$x<20;$x++){
				$y=$x+1;
				$out.='<p>'.$y.' <input type="text" name="options[]" ';
				if(!empty($option[$x]))$out.=' value="'.esc_html( $option[$x]).'" ';
				$out.='></p>';
			}
			$out.='</td></tr>';
		}
		$out.='<tr class="all"><th scope="row">'.esc_html( __('Registration/Add New household forms only','church-admin' ) ).'</th>';
		$out.='<td><input type="checkbox" name="onboarding" ';
		if(!empty( $data->onboarding) ) $out.=' checked="checked" ';
		$out.='/></td></tr>';
		$out.='<tr class="all"><th scope="row">'.esc_html( __('Apply to everyone','church-admin' ) ).'</th>';
			$out.='<td><input type="checkbox" name="custom-all" ';
			if(!empty( $data->all) ) $out.=' checked="checked" ';
			$out.='/></td></tr>';
		$out.='<tr class="show_me"><th scope="row">'.esc_html( __('Show on address list etc','church-admin' ) ).'</th>';
		$out.='<td><input type="checkbox" name="show-me" ';
		if(!empty( $data->show_me) ) $out.=' checked="checked" ';
		$out.='/></td></tr>';
		$out.='<script>
				jQuery(document).ready(function( $)  {
					$(".custom-type").change(function()  {
							var val=$(this).val();
							console.log("type changed to " +val);
							switch(val)
							{
								case "radio":
								case "checkbox":
								case "select":
									$(".boolean").hide();
									$(".text").hide();
									$(".text-boolean").prop("disabled", true);
									$(".text-boolean").prop("disabled", false);
									$(".options").show();
								break;
								case "boolean":
									$(".boolean").show();
									$(".text").hide();
									$(".text-boolean").prop("disabled", true);
									$(".text-boolean").prop("disabled", false);
									$(".options").hide();
								break;
								case "text":
									$(".boolean").hide();
									$(".text").show();
									$(".text-default").prop("disabled", false);
									$("..boolean-default").prop("disabled", true);
									$(".options").hide();
								break;
								case "date":
									$(".boolean").hide();
									$(".text").hide();
									$(".all").hide();
									$(".text-default").prop("disabled", true);
									$(".boolean-default").prop("disabled", true);
									$(".options").hide();
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
 		$out.='<tr><td>&nbsp;</td><td><input type="hidden" name="save_custom_field" value="yes" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr></table></form>';
 	}
 	return $out;


}
/**
 * Delete Custom Field
 *
 * @param
 * @param
 *
 * @author andy_moyle
 *
 */
 function church_admin_delete_custom_field( $ID)
 {
	 global $wpdb;
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE custom_id="'.(int)$ID.'"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_custom_fields WHERE ID="'.(int)$ID.'"');
	$out='<div class="notice notice-success"><h2>'.esc_html( __('Custom field deleted','church-admin' ) ).'</h2></div>';


 	$out.=church_admin_list_custom_fields();
 	return $out;
 }
