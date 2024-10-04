<?php
$path = preg_replace('/wp-content.*$/','',__DIR__);
require_once( $path."/wp-load.php");
require_once(  "../index.php");
church_admin_bulk_email();
exit();
?>
