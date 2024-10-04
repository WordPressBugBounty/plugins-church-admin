<!doctype html>
<title><?php echo __('Unsubscribe','church-admin');?></title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #333; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>

<article>
	<h2><?php bloginfo('name');?></h2>
	<?php
	if(!empty( $details) )
	{
		echo'<p>'.esc_html(sprintf(__('%1$s, You have been unsubscribed from our email list','church-admin' ) ,$details->first_name) ).'</p>'; 
	
	}else echo'<p>'.esc_html( __('You have been unsubscribed from our email list','church-admin' ) ).'</p>';
	
   	echo'<a href="'.site_url().'?ca_sub='.esc_html( $_GET['ca_unsub'] ).'">'.esc_html( __('Oops, please resubscribe me','church-admin' ) ).'</a></p>';
    ?>
    <div>
        <p><?php echo'<a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin'));?></p>
    </div>
</article>