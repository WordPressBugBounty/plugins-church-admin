<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_front_admin()
{
    //variables for menu
    global $church_admin_url,$church_admin_menu, $church_admin_version,$wpdb, $current_user;
    $modules=get_option('church_admin_modules');
    
	$user_id = $current_user->ID;
    $api_key=get_option('church_admin_google_api_key');
    
    ?>
    <div id="church-admin-mobile-menu"><?php echo church_admin_mobile_menu();?></div>
    <div id="church-admin-menu">
        <ul id="churchadminmenu">
            <li class="church-admin">
               
                    <img src="<?php echo esc_url( plugins_url('/images/church-admin-logo.png',dirname(__FILE__) ) );?>" width="128" height="128" alt="Church Admin plugin" /><br>
                    <span id="church-admin-title"><strong>CHURCH</strong>ADMIN</span>
            </li>
            <div class="church-admin-favourites">
            <?php church_admin_favourites_menu(); ?>
          </div>
            
            <?php


$parent=''; 
$premium=get_option('church_admin_new_app_licence');
if(!$premium||$premium!='subscribed')
{
    unset( $church_admin_menu['app-content'] );
}
$firstMainMenuItem=1;
foreach( $church_admin_menu AS $menuID=>$menuItem)    
{

    $modules['Settings']=TRUE;
    $modules['App']=TRUE;
   
    if(!empty( $modules[$menuItem['module']] )&& church_admin_level_check( $menuItem['level'] ) )
    {
        if( $parent!=$menuItem['parent'] )
        {
            $parent=$menuItem['parent'];
            //main menu item
            if( $firstMainMenuItem>1) echo'</ul></li>';
            $firstMainMenuItem++;
            $firstSubMenuItem=1;
            echo'<li class="church-admin-top-menu ';
            if(!empty( $_GET['section'] )&&$_GET['section']==$parent)  { echo ' active ';}else{echo'inactive';}
            //if(!empty( $_GET['action'] ) && $_GET['action']==$menuID)  {echo 'active';}else{echo'inactive';}
            echo'" data-menu-id="'.esc_attr($menuID).'">';
            echo '<div class="church-admin-top-menu-item '.esc_attr($menuID).'" data-menu-id="'.esc_attr($menuID).'" >'."\r\n";
            echo  '<a href="'.esc_url(wp_nonce_url( $menuItem['link'],$menuID)).'"><span class="ca-dashicons dashicons '.esc_html($menuItem['dashicon']).'"></span> '.esc_html($menuItem['title']).'</a>'."\r\n";
            echo'</div>'."\r\n";
            echo'<ul id="'.$menuID.'" class="church-admin-submenu';
            if(!empty( $_GET['section'] )&&$_GET['section']==$parent)  {echo ' active ';}else{echo' inactive';}
            echo'">'."\r\n";;
        }
        else
        {
            //submenu item
            echo '<li class="church-admin-submenu-item '.$menuID.' ';
            if( $firstSubMenuItem==1)echo' first-item';
            $firstSubMenuItem=0;
            echo'" data-menuItem="'.esc_attr($menuID).'">'."\r\n";
             echo  '<a href="'.esc_url(wp_nonce_url( $menuItem['link'],$menuID)).'">'.esc_html($menuItem['title']).'</a>'."\r\n";
            echo"</li>\r\n";

        }
    }
}
    
?>    
            
</ul>        
        <li class="church-admin-top-menu"><a href="https://www.youtube.com/channel/UCrgMuYHBxoMElkvbMXNRb3w" target="_blank"><span class="ca-dashicons dashicons dashicons-youtube" style="text-decoration:none"></span><?php echo __("YouTube tutorials",'church-admin');?></a></li>
     </ul>
    </div>
   
<script>
jQuery(document).ready(function( $)  {
    
   
    //submenu items can be added to favourites...
    $('body').on('click',"#add-to-favourites",function(e)  {
        e.preventDefault();
        var  add="<?php if(!empty( $_GET['action'] ) ) echo esc_attr( sanitize_text_field(stripslashes($_GET['action'] ) ) );?>";
        var user_id="<?php echo (int)$user_id;?>";
        
        //ajax send it to Favourites menu item
        var nonce="<?php echo wp_create_nonce("add-to-favourites");?>";
        var args = {"action": "church_admin","method": "add-to-favourites","what": add,"user_id":user_id,"nonce":nonce};
        console.log(args);
        $.ajax({
                  url: ajaxurl,
                  type: "post",
                  data:  args,
                  success: function(response) {
                    
                      $(".church-admin-favourites").html(response);
                      }
                });
        
    });
    $('body').on('click',".ca-favourites-remove",function(e)  {
        console.log('Remove clicked')
        e.preventDefault();
        var  remove=$(this).data('remove');
        var user_id="<?php echo (int)$user_id;?>";
        var nonce="<?php echo wp_create_nonce("remove-from-favourites");?>";
        var args = {"action": "church_admin","method": "remove-from-favourites","what": remove,"user_id":user_id,"nonce":nonce};
        console.log(args);
        $.ajax({
                  url: ajaxurl,
                  type: "post",
                  data:  args,
                  success: function(response) {
                    
                      $(".church-admin-favourites").html(response);
                      }
                });
    });
})

</script>
<?php 
}