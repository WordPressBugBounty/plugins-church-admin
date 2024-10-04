<?php
/**
 * Single post
 */
get_header(); 


$content='<div style="margin:0 auto;"><h2>'.esc_html(__('Prayer Requests','church-admin') ).'</h2>';
$content.='<p>'.esc_html( __('This page is for logged in users only','church-admin' ) ).'</p>';
$content.= wp_login_form(array('echo'=>FALSE) );
$content.='<a href="'.esc_url( wp_lostpassword_url( get_permalink() ) ).'" alt="'.esc_attr(__( 'Lost Password', 'church-admin' ) ).'">'.esc_attr( __( "I've forgotten my password", 'church-admin' ) ).'</a></p></div>';

$registerPage=get_option('church_admin_register_page');
if(!empty($registerPage))
{
    $content.='<a href="'.esc_url($registerPage).'">'.esc_html( __("Register",'church-admin') ).'</a></p>';
}

$content.='</div>';
echo $content;

get_footer();

