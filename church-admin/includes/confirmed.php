<!doctype html>
<html>
<head><title><?php echo __('General Data Protection Regulations Confirmation','church-admin');?></title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #333; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>
</head>
<body>
<article>
	<h2><?php bloginfo('name');?></h2>
	<p><?php
        if ( empty( $CAUSER) )
        {
            echo __('Thanks for confiming that you are happy to receive email, sms or mail from us and be on our address list. An admin will check your entry and issue you a user account','church-admin');
        }
        else
        {
            echo __('Thanks for confiming your email. Your user details have been emailed to you','church-admin');
        }?>
    </p>
    <div>
        
        <p><?php echo'<a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin'));?></a></p>
    </div>
</article>
</body>
</html>