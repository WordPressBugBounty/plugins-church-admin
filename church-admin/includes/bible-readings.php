<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


/**
 *
 * Bible Reading Plan
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function church_admin_bible_reading_bulk_post()
{
    delete_option('church-admin-no-bible-readings');
	global $wpdb;
	$current_user = wp_get_current_user();
	if(!church_admin_level_check('Directory') )wp_die(__('You don\'t have permissions to do that','church-admin') );
    $version = get_option( 'church_admin_bible_version');
    if( empty( $version ) ) { 
        $version='ESV';
    }
    $headphonesSVG='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6 23v-11c-4.036 0-6 2.715-6 5.5 0 2.807 1.995 5.5 6 5.5zm18-5.5c0-2.785-1.964-5.5-6-5.5v11c4.005 0 6-2.693 6-5.5zm-12-13.522c-3.879-.008-6.861 2.349-7.743 6.195-.751.145-1.479.385-2.161.716.629-5.501 4.319-9.889 9.904-9.889 5.589 0 9.29 4.389 9.916 9.896-.684-.334-1.415-.575-2.169-.721-.881-3.85-3.867-6.205-7.747-6.197z" /></svg>';
	

 	echo'<h2>'.esc_html( __('Bulk publish a Bible reading plan? ','church-admin' ) ).'</h2>';

	
	if(!empty( $_POST['save_csv'] ) )
	{
        $allowed_extensions = array('csv');
		$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
       
        
        if(!empty( $_FILES) && $_FILES['featured-image']['error'] == 0)
        {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            // Let WordPress handle the upload.
            $attachment_id = media_handle_upload( 'featured-image', 0 );
        }
        $tmpName  = $_FILES['bible-plan']['tmp_name'];
        $this_mime = mime_content_type($tmpName);
        $file_parts = explode('.',$tmpName);
        $extension = end($file_parts);//protects against backdoor.png.php 
        // check correct mime type, file has at least one . and after the last . is an allowed extension
        if(!in_array($this_mime,$mimes)||!in_array($extension,$allowed_extensions)){
            echo'<p>'.__("Bible reading plan files doesn't seem to be a CSV",'church-admin');
            return;
        }
		if(!empty( $_FILES) && $_FILES['bible-plan']['error'] == 0 && in_array( $_FILES['bible-plan']['type'],$mimes) )
		{
			
			$filename = $_FILES['bible-plan']['name'];
			$upload_dir = wp_upload_dir();
			$filedest = $upload_dir['path'] . '/' . $filename;
			if(move_uploaded_file( $_FILES['bible-plan']['tmp_name'], $filedest) )echo '<h3>'.esc_html( __('File Uploaded and saved','church-admin' ) ).'</h3>';

			//ini_set('auto_detect_line_endings',TRUE);
			$file_handle = fopen( $filedest, "r");
			if($file_handle){
                $postNum=0;
                $startDate=strtotime(date('Y-m-d 06:00:00') );
                if(!empty( $_POST['pub_date'] ) && church_admin_checkdate( $_POST['pub_date'] ) )
                {
                    $startDate = strtotime( sanitize_text_field(stripslashes($_POST['pub_date'].' '.$_POST['pub_time'] ) ));
                }
            
                while (( $data = fgetcsv( $file_handle, 1000, ",") ) !== FALSE)
                {   
                    $thisDate=$startDate+( $postNum*24*60*60);
                    if(time()>$thisDate)  {$status='future';}else{$status='publish';}
                    $pub_time=date('Y-m-d H:i:s',$thisDate);
                    $readableDate = date(get_option('date_format'),$thisDate);
                    echo'<h3>'.esc_html(sprintf(__('Preparing bible reading post for %1$s','church-admin' ) ,$readableDate)).'</h3>';
                    
                    $post_content='';
                    foreach( $data AS $key=>$reading)
                    {
                        echo'<p>Adding '.esc_html($reading).'</p>';
                        //fix reading to format Bible Gateway understands



                        $bibleCV=church_admin_bible_audio_link( $reading,$version);
                        
                        $BibleGatewayReading= $bibleCV['book'].' '.$bibleCV['chapter'];
                        if( !empty( $bibleCV[ 'verses' ] ) ) {
                            $BibleGatewayReading .= ':'.$bibleCV['verses'];
                        }
                        $post_content.='
                        <!-- wp:heading -->
                        <h2>'.esc_html( $reading).'</h2>
                        <!-- /wp:heading -->
                        <!-- wp:paragraph -->
                        <p><a target="_blank" href="'.esc_url('https://www.biblegateway.com/passage/?search='.urlencode( $BibleGatewayReading).'&version='.urlencode( $version).'&interface=print').'" >'.esc_html( sprintf( __('Read from the %1$s version on Biblegateway.com','church-admin' ), esc_html( $version ) ) ).'.</a></p><!-- /wp:paragraph -->';
                        if(!empty( $bibleCV['url'] ) )$post_content.=' <!-- wp:paragraph --><p><a target="_blank" href="'.esc_url($bibleCV['url']).'">'.$headphonesSVG.' '.esc_html($bibleCV['linkText']).'</a></p><!-- /wp:paragraph -->';
                    }
                    
                    $post_title=esc_html( sprintf(__('Bible reading for %1$s','church-admin' ) ,$readableDate));
                    $args=array(    'post_type'=>'bible-readings',
                                    'post_author'=>$current_user->ID,
                                    'post_title'=>$post_title,
                                    'post_content'=>$post_content,
                                    'post_status'=>$status,
                                    'post_date'=>$pub_time
                                );
                
                    $ID= wp_insert_post( $args);
                    
                    if( $ID)
                    {
                        if(!empty( $attachment_id) )set_post_thumbnail( $ID,$attachment_id);
                        echo'<p>'.esc_html(sprintf(__('%1$s saved for %2$s','church-admin' ) ,$post_title,$readableDate)).'</p>';
                    }
                    $postNum++;
                }
            }
		}
	}
	else
	{
		$plan=get_option('church_admin_brp');
        
		echo'<p>'.esc_html( __('Publish Bible readings from a CSV - day per row,comma separated passages for each day','church-admin' ) ).'</p>';
		echo'<form action="" method="POST" enctype="multipart/form-data">';
		wp_nonce_field('bible_upload');
		echo'<p><label>'.esc_html( __('Featured image','church-admin' ) ).'</label><input type="file" name="featured-image" /><input type="hidden" name="save_csv" value="yes" /></p>';
		echo'<p><label>'.esc_html( __('CSV File','church-admin' ) ).'</label><input type="file" name="bible-plan" accept=".csv" /><input type="hidden" name="save_csv" value="yes" /></p>';
		echo '<p><label>'.esc_html( __("Start date",'church-admin' ) ).'</label>'.church_admin_date_picker(NULL,'pub_date',FALSE,date('Y-m-d',strtotime("-1 years") ),NULL,'pub_date','pub_date',FALSE).'</p>';
        echo'<p><label>'.esc_html( __('Daily publish time','church-admin')).'</label><input type="time" name="pub_time" value="06:00" /></p>';
        echo'<p><input  class="button-primary" type="submit" Value="'.esc_html( __('Upload','church-admin' ) ).'" /></p></form>';
	}

	
}