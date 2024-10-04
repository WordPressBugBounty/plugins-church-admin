<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//use chillerlan\QRCode\{QRCode, QROptions};
function church_admin_kiosk_app()
{
    echo'<h2>'.__('Kiosk App, a new app for registering visitors on a tablet','church-admin').'</h2>';
    

    church_admin_kiosk_qr_code();
    echo'<form action="admin.php?page=church_admin%2Findex.php&action=kiosk-app" method="POST"><p>';
    wp_nonce_field( 'reset-kiosk-token', 'reset-kiosk-token');
    echo'<input type="submit" class="button-primary" value="'.esc_attr(__('Logout all kiosk apps','church-admin')).'"></p></form>'."\r\n";
    //reset form fieldsto default
    if(!empty($_POST['reset-kiosk-form']) && wp_verify_nonce(church_admin_sanitize($_POST['reset-kiosk-form']), 'reset-kiosk-form')){
        delete_option('church_admin_kiosk_app_form');
        echo'<div class="notice notice-success"><h2>'.esc_html(__('Form reset to default','church-admin')).'</h2></div>';
    }
    
   
    //process kiosk app form fields
    if(!empty($_POST['kiosk-app-form'])){
        $form_title = !empty($_POST['kiosk_form_title']) ? church_admin_sanitize($_POST['kiosk_form_title']): null;
        $form_preamble = !empty($_POST['kiosk_form_preamble']) ? wp_kses_post(stripslashes($_POST['kiosk_form_preamble'])): null;
        update_option('church_admin_kiosk_form_title',$form_title);
        update_option('church_admin_kiosk_form_preamble',$form_preamble);
    }
    $form_title = '';
    $form_title = get_option('church_admin_kiosk_form_title');
    $form_premable ='';
    $form_preamble = get_option('church_admin_kiosk_form_preamble');
    echo'<h2>'.__('Build form for kiosk app','church-admin').'</h2>';
    echo'<form action="admin.php?page=church_admin/index.php&action=kiosk-app" method="POST">';
    wp_nonce_field('kiosk-app');
    echo'<div class="church-admin-form-group"><label>'.__('Registration form title','church-admin').'</label><input class="church-admin-form-control" name="kiosk_form_title" value="'.esc_attr($form_title).'"></div>';
    echo'<div class="church-admin-form-group"><label>'.__('Registration form explainer','church-admin').'</label></div>';
    wp_editor($form_preamble,'kiosk_form_preamble');
    echo'<p><input type="hidden" name="kiosk-app-form" value=1><input class="button-primary" type="submit" value="'.esc_attr(__('Save','church-admin')).'"></p></form>'."\r\n";


    $fields= array( 'title'=>array('order'=>1, 'name'=>'title','title'=>__('Title','church-admin'),'type'=>'text'),
                    'first_name'=>array('order'=>2, 'name'=>'first_name','title'=>__('First name','church-admin'),'type'=>'text'),
                  'prefix'=>array('order'=>3, 'name'=>  'prefix','title'=>__('Prefix','church-admin'),'type'=>'text'),
                  'last_name'=>array('order'=>4, 'name'=>  'last_name','title'=>__('Last name','church-admin'),'type'=>'text'),
                  'gender'=>array('order'=>5,'name'=>'gender','title'=>__('Gender'),'type'=>'select','options'=>array('1'=>__('Male','church-admin'),'0'=>__('Female','church-admin'))),
                  'email'=>array('order'=>6, 'name'=>  'email','title'=>__('Email','church-admin'),'type'=>'email'),
                  'mobile'=>array('order'=>7, 'name'=>  'mobile','title'=>__('Cell phone','church-admin'),'type'=>'text'),
                  'spouse'=>array('order'=>8, 'name'=>  'spouse','title'=>__('Spouse (e.g. Jane Smith)','church-admin'),'type'=>'text'),
                  'children'=>array('order'=>9, 'name'=>  'children','title'=>__('Children (e.g. Joe Smith, Fred, Bob)','church-admin'),'type'=>'text'),
                  'address'=>array('order'=>10, 'name'=>  'address','title'=>__('Address','church-admin'),'type'=>'text'),
                  'email_send'=>array('order'=>11, 'name'=>  'email_send','title'=>__('Email send?','church-admin'),'type'=>'checkbox'),
                  'sms_send'=>array('order'=>12, 'name'=>  'sms_send','title'=>__('SMS send?','church-admin'),'type'=>'checkbox'),
                  'user_account'=>array('order'=>13, 'name'=>  'user_account','title'=>__('Create user account?','church-admin'),'type'=>'checkbox'),
    );
    $order=14;
    $custom_fields = church_admin_custom_fields_array();
    if(!empty($custom_fields)){
        foreach($custom_fields AS $key=>$cf){

            $fields[]=array('order'=>$order,'title'=>$cf['name'],'name'=>'custom-'.(int)$key,'type'=>$cf['type'],'default_value'=>$cf['default_value'],'options'=>$cf['options']);
            $order++;
        }

    }
    

    $saved_fields = get_option('church_admin_kiosk_app_form');
    if(empty($saved_fields)){
        $saved_fields=$fields;
        update_option('church_admin_kiosk_app_form',$fields);
    }
    $displayable_fields=array();
    foreach($saved_fields AS $key=>$value){
        $displayable_fields[$value['order']]=$value;
    }
    ksort($displayable_fields);
    echo'<form action="admin.php?page=church_admin%2Findex.php&action=kiosk-app" method="POST"><p>';
    wp_nonce_field( 'reset-kiosk-form', 'reset-kiosk-form' );
    echo'<input type="submit" class="button-primary" value="'.esc_attr(__('Reset form to default','church-admin')).'"></p></form>'."\r\n";
    echo'<h2>'.esc_html(__('Choose which form fields to show and order by drag and drop','church-admin')).'</h2>';
    echo'<table id="sortable" class="kiosk-app widefat bordered"><tbody class="content ui-sortable">';
	$x=1;		
    foreach($displayable_fields AS $key=>$field)
    {
        //work out look of form field
        switch($field['type']){
            case 'text':
                $this_field='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label><input class="church-admin-form-control" data-name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'" type="text"></div>';
            break;
            case 'email':
                $this_field='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label><input class="church-admin-form-control" name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'" type="email"></div>';
            break;
            case 'checkbox':
                $this_field='<div class="checkbox"><label ><input type="checkbox" value="1"   data-name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'" > '.esc_html($field['title']).'</label></div>';
            break;
            case 'select':
                $this_field='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label><select class="church-admin-form-control" data-name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'"><option>'.__('Choose...','church-admin').'</option>';
                foreach($field['options'] AS $key=>$name){
                    $this_field.='<option value="'.esc_attr($key).'">'.esc_attr($name).'</option>';
                }
                $this_field.='</select>';
            break;
            case 'boolean':
                $this_field='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label></div><div class="checkbox"><label><input type="radio" data-name="custom-'.(int)$key.'"   value="1" name="'.esc_attr($field['name']).'" >'.esc_html( __( 'Yes','church-admin') ).'</label></div><div class="checkbox"><label> <input type="radio" data-name="'.esc_attr($field['name']).'"  value="0" name="'.esc_attr($field['name']).'">'.esc_html( __( 'No','church-admin') ).'</label></div>';
            break;
        }

        echo'<tr class="sortable-row ui-sortable-handle" id="'.esc_attr( $field['name']).'">';
       // echo'<td class="kiosk-app-order"><span class="kiosk-app-order-content">'.(int)$x.'</span></td>';
        echo'<td class="kiosk-app-delete" data-id="'.esc_attr($field['name']).'"><span class="kiosk-app-order-content">'.esc_html( __('Delete','church-admin') ).'</span></td>';
        echo'<td class="kiosk-app-form-title">'.$this_field.'</td>';
        echo'</tr>'."\r\n";
        $x++;

    }
    echo'</tbody></table>';
 
    echo'<script>
    jQuery(document).ready(function( $) {

        var nonce="'.wp_create_nonce("kiosk-app-ajax").'";

        $(".kiosk-app").on("click",".kiosk-app-delete",function(event){
            event.preventDefault();
            var id= $(this).data("id");
            console.log("Deleting " + id);
            var args={"action":"church_admin","method":"kiosk-app-delete","id":id,"nonce":nonce} 
            console.log(args);
            $.ajax({
                url: ajaxurl,
                type: "post",
                data:  args,
                error: function() {
                    console.log("theres an error with AJAX");
                },
                success: function(result) {
                    $("#"+id).hide();
                }
            });
        });

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


            var Order = $(this).sortable(\'toArray\').toString();
            var data = {
            "action": "church_admin",
            "method": "kiosk_form_order",
            "order": Order,
            "nonce": nonce
        };
        console.log(data);
        console.log(ajaxurl);
    $.ajax({
        url: ajaxurl,
        type: "post",
        data:  data,
        error: function() {
            console.log("theres an error with AJAX");
        },
        success: function() {

        }
    });}}); $("#sortable tbody.content").disableSelection();
});</script>';

}


function church_admin_kiosk_app_form(){


    $form_title = '';
    $form_title = get_option('church_admin_kiosk_form_title');
    $form_premable ='';
    $form_preamble = get_option('church_admin_kiosk_form_preamble');
    $form='<h2>'.esc_html($form_title).'</h2>';
    $form.=wp_kses_post( wpautop( $form_preamble) );
    //get form fields
    $saved_fields = get_option('church_admin_kiosk_app_form');
    if(empty($saved_fields)){
        $saved_fields=$fields;
        update_option('church_admin_kiosk_app_form',$fields);
    }
    $displayable_fields=array();
    foreach($saved_fields AS $key=>$value){
        $displayable_fields[$value['order']]=$value;
    }

    foreach($displayable_fields AS $key=>$field){

        switch($field['type']){
                case 'text':
                    $form.='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label><input class="church-admin-form-control" data-name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'" type="text"></div>';
                break;
                case 'email':
                    $form.='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label><input class="church-admin-form-control" name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'" type="email"></div>';
                break;
                case 'checkbox':
                    $form.='<div class="checkbox"><label ><input type="checkbox" value="1"   data-name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'" > '.esc_html($field['title']).'</label></div>';
                break;
                case 'select':
                    $form.='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label><select class="church-admin-form-control" data-name="'.esc_attr($field['name']).'" name="'.esc_attr($field['name']).'"><option>'.__('Choose...','church-admin').'</option>';
                    foreach($field['options'] AS $key=>$name){
                        $form.='<option value="'.esc_attr($key).'">'.esc_attr($name).'</option>';
                    }
                    $form.='</select></div>';
                break;
                case 'boolean':
                    $form.='<div class="church-admin-form-group"><label>'.esc_html($field['title']).'</label></div><div class="checkbox"><label><input type="radio" data-name="custom-'.(int)$key.'"   value="1" name="'.esc_attr($field['name']).'" >'.esc_html( __( 'Yes','church-admin') ).'</label></div><div class="checkbox"><label> <input type="radio" data-name="'.esc_attr($field['name']).'"  value="0" name="'.esc_attr($field['name']).'">'.esc_html( __( 'No','church-admin') ).'</label></div>';
				break;
            }
        }

    

    return $form;
}

function church_admin_kiosk_qr_code(){
    if(!empty($_POST['reset-kiosk-token']) && wp_verify_nonce(church_admin_sanitize($_POST['reset-kiosk-token']), 'reset-kiosk-token')){
        delete_option('church_admin_kiosk_token');
        delete_option('church_admin_kiosk_pin');
        echo'<p><strong>Token and PIN reset, all devices logged out</strong></p>';
    }
    $token = get_option('church_admin_kiosk_token');
    if(empty($token))
    {
        $token=bin2hex(random_bytes(9));
        update_option('church_admin_kiosk_token',$token);
    }
    echo '<p>Token: '.$token.'</p>';
    $pin_code = get_option('church_admin_kiosk_pin');
    if(empty($pin_code)){
        $pin_code = mt_rand(1000,9898);
        update_option('church_admin_kiosk_pin',$pin_code);
    }
    echo'<p>Lock your Church Admin Kiosk app to this website with this token</p>';
    echo'<p>Admin PIN is '.$pin_code.'</p>';
    $pin_code = get_option('church_admin_kiosk_pin');
    $token = get_option('church_admin_kiosk_token');
    $upload_dir = wp_upload_dir();
	$debug_path=$upload_dir['basedir'].'/church-admin-cache/';
    //use wp_normalize_path for windows localhosts to correct slashes.
    $filename=wp_normalize_path($debug_path.$token.'.png');
    $data = json_encode(array('url'=>admin_url().'admin-ajax.php?action=church_admin_kiosk_app&token='.esc_attr($token),'pin'=>$pin_code));
   
    /*
    require_once(plugin_dir_path(__FILE__ ).'/phpqrcode/qrlib.php');
    
    QRcode::png(json_encode($data),$filename);
    $image = $upload_dir['baseurl'].'/church-admin-cache/'.$token.'.png';
    echo'<p><img src="'.esc_url($image).'" alt="QR code for kiosk app locking"></p>';
    */
    
    //require_once(plugin_dir_path(__FILE__ ).'/php-qrcode/autoload.php');
    if(extension_loaded('gd')){
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/qrcode/qrcode.class.php');
        $qrcode = new QRcode($data, 'M'); // error level : L, M, Q, H
        $qrcode->displayPNG(200, array(255,255,255), array(0,0,0), $filename , 5);
        echo '<p><img src="'.esc_url($upload_dir['baseurl'].'/church-admin-cache/'.$token.'.png').'" width="200" height="200" alt="QR Code" /></p>';
    }
    else{
        echo'<p>'.esc_html(__('Please ask your host to enable the GD image library','church-admin')).'</p>';
    }
}

function church_admin_kiosk_process_register(){
    global $wpdb;
    church_admin_debug('**** Process Register ****');
    $data=array();
    
    $expected_fields = get_option('church_admin_kiosk_app_form');
    foreach($_POST['data'] AS $key=>$field){
        if(empty($field['name'])){continue;}
         $name = church_admin_sanitize($field['name']);
        if(!empty($field['value'])) $value = church_admin_sanitize($field['value']);
        if(!empty($name) && !empty($value)) { $data[$name] = $value; }
    }
    church_admin_debug('FORM DATA processed into an array');
    church_admin_debug($data);
    if(empty($data['email'])) return FALSE;//absolute minimum is an email field
    if(!is_email($data['email'])) return FALSE;
    
    //now only use the expected data...
    $expected_data = array();
    foreach($expected_fields AS $key=>$field){
        if(!empty($data[$field['name']])){
            $expected_data[$field['name']] = $data[$field['name']];
        }
    }
    $sms_send = !empty($expected_data['sms_send'])?1:0;
    $email_send = !empty($expected_data['email_send'])?1:0;
    $create_user = !empty($expected_data['create_user'])?1:0;
    //check for people id
    $people_id = $wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.esc_sql($expected_data['first_name']).'" AND last_name="'.esc_sql($expected_data['last_name']).'" AND email = "'.esc_sql($expected_data['email']).'"');
    church_admin_debug($wpdb->last_query);
    if(!empty($people_id)){
        church_admin_debug('Already in directory');
        return 2;
    }

    if(!empty($expected_data['address'])){
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address) VALUES ("'.esc_sql($expected_data['address']).'")');       
    }
    else{
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address) VALUES ("")'); 
    }
    church_admin_debug($wpdb->last_query);
    $household_id = $wpdb->insert_id;
   


    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,sex,email,mobile,household_id,head_of_household,email_send,sms_send,people_type_id,member_type_id,first_registered,show_me) VALUES ("'.esc_sql($expected_data['first_name']).'","'.esc_sql($expected_data['last_name']).'","'.esc_sql($expected_data['gender']).'","'.esc_sql($expected_data['email']).'","'.esc_sql($expected_data['mobile']).'","'.(int)$household_id.'",1,"'.(int)$email_send.'","'.(int)$sms_send.'",1,1,NOW(),0)');
    church_admin_debug($wpdb->last_query);
    $people_id = $wpdb->insert_id;
    church_admin_email_confirm($people_id);
 
    //handle spouse
    if(!empty($expected_data['spouse'])){
        church_admin_debug('Handle Spouse');
        $spouse = explode(" ",$expected_data['spouse']);
        $gender = !empty($expected_data['gender'])?0:1;//give opposite!
        //handle last name not given
        if(empty($spouse[1])){$spouse[1]=$expected_data['last_name'];}
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,sex,email,mobile,household_id,head_of_household,email_send,sms_send,people_type_id,member_type_id,first_registered,show_me) VALUES ("'.esc_sql(trim($spouse[0])).'","'.esc_sql(trim($spouse[1])).'","'.esc_sql($gender).'","","","'.(int)$household_id.'",0,"'.(int)$email_send.'","'.(int)$sms_send.'",1,1,NOW(),0)');
        church_admin_debug($wpdb->last_query);
    }
    //handle children
    if(!empty($expected_data['children'])){
        church_admin_debug('Handle Children');
        $children = explode(",",$expected_data['children']);
        church_admin_debug($children);
        foreach($children AS $key=>$child){
            $name = explode(" ",trim($child));
            if(empty($name[1])){$name[1]= $expected_data['last_name'];}
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,sex,email,mobile,household_id,head_of_household,email_send,sms_send,people_type_id,member_type_id,first_registered,show_me) VALUES ("'.esc_sql(trim($name[0])).'","'.esc_sql(trim($name[1])).'","1","","","'.(int)$household_id.'","0","'.(int)$email_send.'","'.(int)$sms_send.'",2,1,NOW(),0 )');
            church_admin_debug($wpdb->last_query);
        }
    }
 
    return 1;
}