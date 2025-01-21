<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ca_podcast_list_series()
{
    /**
     *
     * Lists podcast series
     *
     * @author  Andy Moyle
     * @param    null
     * @return   html string
     * @version  0.1
     *
     */
    global $wpdb;


    echo'<h2>'.esc_html( __('Sermon Series','church-admin' ) ).'</h2>';
    echo'<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-sermon-series','edit-sermon-series').'">Add a Sermon Series</a></p>';

    //grab files from table
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY last_sermon DESC');
    if( $results)
    {//results
        
        $theader='<tr><th class="column-primary">'.esc_html( __('Series','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Image','church-admin' ) ).'</th><th>'.esc_html( __('Files','church-admin' ) ).'</th><th>'.esc_html( __('Shortcode','church-admin' ) ).'</th></tr>';
        $table='<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead>'."\r\n".'<tfoot>'.$theader.'</tfoot>'."\r\n".'<tbody>';
        foreach( $results AS $row)
        {
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-sermon-series&section=media&amp;id='.(int)$row->series_id,'edit-sermon-series').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=media&amp;action=delete-sermon-series&amp;id='.(int)$row->series_id,'delete-sermon-series').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            $files=$wpdb->get_var('SELECT count(*) FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE series_id="'.esc_sql( $row->series_id).'"');
            if(!$files)$files="0";
            $image=wp_get_attachment_image( $row->series_image,'medium');
            if ( empty( $image) )$image='';
        
            $table.='<tr>
                <td data-colname="'.esc_html( __('Series','church-admin' ) ).'" class="column-primary">'.esc_html( $row->series_name).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                <td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
                <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
                <td data-colname="'.esc_html( __('Image','church-admin' ) ).'">'.$image.'</td>
                <td data-colname="'.esc_html( __('Files','church-admin' ) ).'">'.(int)$files.'</td>
                <td data-colname="'.esc_html( __('Shortcode','church-admin' ) ).'">[church_admin type="podcast" series_id="'.(int)$row->series_id.'"]</td>
            </tr>';
        }

        $table.='</tbody></table>';
        echo $table;
    }//end results
    else
    {
        echo'<p>'.esc_html( __('No Sermon Series stored yet','church-admin' ) ).'</p>';
    }


}
function ca_podcast_delete_series( $id=NULL)
{
    /**
 *
 * Delete podcast events
 *
 * @author  Andy Moyle
 * @param    $id=null
 * @return   html string
 * @version  0.1
 *
 */

	global $wpdb;
	$wpdb->query('DELETE  FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_id="'.esc_sql((int)$id).'"');
	 echo'<div class="notice notice-success inline"><p>'.esc_html( __('Series Deleted','church-admin' ) ).'</p></div>';
        ca_podcast_list_series();

 }
/**************************************************
 *
 * Edit podcast events
 *
 * @author  Andy Moyle
 * @param    $id=null
 * @return   html string
 * @version  0.1
 *
 ***************************************************/
function ca_podcast_edit_series( $id=NULL)
{


    global $wpdb;
    if(!empty( $id) )
    {
        $current_data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_id="'.(int)$id.'"');
        $title='Edit';
    }
    else
    {
        $id=0;
        $title='Add';
    }
    echo'<h2>'.esc_html( $title).' Sermon Series</h2>';
    if(!empty( $_POST['save_series'] ) )
    {//process form

        $series_name=sanitize_text_field(stripslashes( $_POST['series_name'] ) );
        $series_description=sanitize_textarea_field(stripslashes( $_POST['series_description'] ) ) ;
        if ( empty( $id) )$id=$wpdb->get_var('SELECT series_id FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_name="'.esc_sql( $series_name).'" AND series_description="'.esc_sql($series_description).'"');
        if(!empty( $id) )
        {//update
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_series SET series_name="'.esc_sql( $series_name).'",series_description="'.esc_sql($series_description).'" ,series_image="'.(int)$_POST['attachment_id'].'",series_slug="'.esc_sql(sanitize_title( $series_name) ).'" WHERE series_id="'.(int)$id.'"');
        }//end update
        else
        {//insert
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name,series_description,series_image,series_slug)VALUES("'.esc_sql( $series_name).'","'.esc_sql($series_description).'","'.(int)$_POST['attachment_id'].'","'.esc_sql(sanitize_title( $series_name) ).'")');
        }//end insert
        echo'<div class="notice notice-success inline"><p>'.esc_html( __('Series Saved','church-admin' ) ).'</p></div>';
        ca_podcast_list_series();
    }//end process form
    else
    {//form
        echo '<form action="" method="POST"><table class="form-table">';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Series Name','church-admin' ) ).'</th><td><input class="church-admin-form-control" type="text" name="series_name" id="series_name" class="large-text"';
        if(!empty( $current_data->series_name) ) echo 'value="'.esc_html( $current_data->series_name).'"';
        echo'/></td></tr>';

        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Photo (600x400px is ideal)','church-admin' ) ).'</th><td>';
	
    echo'<div class="church-admin-series-image ca-upload-area" data-nonce="'.wp_create_nonce("series-image-upload").'" data-which="series" data-id="'.(int)$id.'" id="uploadfile"><h3>'.esc_html( __('Series image','church-admin' ) ).'</h3>';
    if(!empty( $current_data->series_image) )
    {
        $series_image_attributes=wp_get_attachment_image_src( $current_data->series_image,'medium','' );
        if ( $series_image_attributes )
        {
            echo'<img id="series-image" src="'.$series_image_attributes[0].'" width="'.$series_image_attributes[1].'" height="'.$series_image_attributes[2].'" class="rounded" alt="'.esc_html( __('Household image','church-admin' ) ).'" />';
        }else {
            echo'<img id="series-image"  src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="300" height="200" class="rounded" alt="'.esc_html( __('Series image','church-admin' ) ).'" />';
        }
    }
    else
    {
        echo'<img id="series-image"  src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="300" height="200" class="rounded" alt="'.esc_html( __('Series image','church-admin' ) ).'" />';
    }
    echo '<br>'.esc_html( __('Drag and drop new image for series','church-admin'));
    echo '<span id="series-upload-message"></span>';

    echo'</div>';

   
    echo'<input type="hidden" name="attachment_id" class="attachment_id" id="attachment_id" ';
    if(!empty( $current_data->series_image) ) echo ' value="'.(int)$current_data->series_image.'" ';
        echo'/><br style="clear:left" />';
    echo'</td></tr>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Series Description','church-admin' ) ).'</th><td>';
        echo'<textarea name="series_description" id="series_description" class="large-text">';
		if(!empty( $current_data->series_description ) )echo esc_textarea( $current_data->series_description );
		echo'</textarea></td></tr>';
        echo '<tr><td colspacing=2><input type="hidden" name="save_series" value="save_series" /><input type="submit" class="button-primary" value="'.esc_html( __('Save Sermon Series','church-admin' ) ).'" /></td></tr></table></form>';
    
    }//form


}




function ca_podcast_list_files()
{
    /**
     *
     * Lists podcast files
     *
     * @author  Andy Moyle
     * @param    null
     * @return   html string
     * @version  0.1
     *
     */ 



    global $wpdb,$church_admin_url;
    $upload_dir = wp_upload_dir();
            $path=$upload_dir['basedir'].'/sermons/';
            $url=$upload_dir['baseurl'].'/sermons/';

    if(!empty($_POST)){
        //church_admin_debug($_POST);
    }

    /***************************
     * Handle bulk actions
     ***************************/
    if(!empty($_POST['method']))
    {
        $message=__('Selected sermons removed from database','church-admin');

        if(!empty($_POST['file_id']))
        {
            foreach($_POST['file_id'] AS $key=>$id){
                $file_id=(!empty($id) && church_admin_int_check($id))?(int)$id:null;
                if(!empty($file_id)){

                    $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.(int)$file_id.'"');
                    //church_admin_debug($wpdb->last_query);
                    if(!empty($data))
                    {
                        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.(int)$file_id.'"');
                        //church_admin_debug($wpdb->last_query);
                        if($_POST['method']=='delete-files'){
                            $fullfilepath=$path.sanitize_file_name($data->file_name);
                            if(!empty($data->file_name) && file_exists($fullfilepath)){
                                unlink($fullfilepath);
                            }
                            $message=__('Selected sermons removed from database and deleted from server','church-admin');
                        }
                    }

                }
           
           
            }
            echo'<div class="notice notice-success"><h2>'.esc_html($message).'</h2></div>';
           
        }
    }
 
    /***************************
     * End Handle bulk actions
     ***************************/
 
    ca_podcast_xml();
    //church_admin_debug('List files');
	
    echo'<p><a href="'.$url.'podcast.xml">Podcast RSS file</a></p>';
    if(!empty( $_GET['action'] ) )
    {
        echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=migrate_advanced_sermons&amp;section=media','migrate_advanced_sermons').'">'.esc_html( __('Migrate from Advanced Sermons (and Pro) plugin','church-admin' ) ).'</a><p>';
        echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=migrate_sermon_manager&amp;section=media','migrate_sermon_manager').'">'.esc_html( __('Migrate from Sermon Manager plugin','church-admin' ) ).'</a><p>';
        echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=migrate_sermon_browser&amp;section=media','migrate_sermon_browser').'">'.esc_html( __('Migrate from Sermon Browser plugin','church-admin' ) ).'</a><p>';
    
        if(!file_exists( $path.'podcast.xml') )
        {
            ca_podcast_xml();

        }
        if(file_exists( $path.'podcast.xml') )echo'<p><a href="'.$url.'podcast.xml">Podcast RSS File</a></p>';
        echo'<p class="search-box"><form action="admin.php?page=church_admin%2Findex.php&action=media&section=podcast" method="POST">';
        wp_nonce_field('podcast');
        echo'<input class="church-admin-form-control" type="text" name="sermon-search" placeholder="'.esc_html( __('Search','church-admin' ) ).'" /><input class="button-secondary" type="submit" value="'.esc_html( __('Search sermons','church-admin' ) ).'" /></form></p>';
    
    }
    
    if(!empty( $_POST['sermon-search'] ) )
    {
        $s=esc_sql(sanitize_text_field( stripslashes($_POST['sermon-search'] ) ) );
        
        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_title LIKE "%'.$s.'%" OR file_description LIKE "%'.$s.'%" OR speaker LIKE "%'.$s.'%" OR bible_texts LIKE "%'.$s.'%" ORDER BY pub_date DESC';
       
        $results=$wpdb->get_results( $sql);
        $searchCount=$wpdb->num_rows;
        if ( empty( $results) )
        {
            echo'<p>'.esc_html( __('No results','church-admin' ) ).'</p>';
            echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=media&section=podcast','media').'">'.esc_html( __('View all sermons','church-admin' ) ).'</a></p>';
        }
        else
        {//translators: %1$s is a search string, %2$s is a number 
            echo'<h2>'.sprintf(__('Your search for "%1$s" yielded %2$s results','church-admin' ) ,esc_html(sanitize_text_field(stripslashes( $_POST['sermon-search'] )) ),$searchCount).'</h2>';
            echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=media&section=podcast','media').'">'.esc_html( __('View all sermons','church-admin' ) ).'</a></p>';
            $theader='<tr><th class="column-primary">'.esc_html( __('Title','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Publ. Date','church-admin' ) ).'</th><th>'.esc_html( __('Speakers','church-admin' ) ).'</th><th>'.esc_html( __('Mp3 File','church-admin' ) ).'</th></th><th>'.esc_html( __('Mp3 File Okay?','church-admin' ) ).'</th><th>'.esc_html( __('Length','church-admin' ) ).'</th><th>'.esc_html( __('Media','church-admin' ) ).'</th><th>'.esc_html( __('Embed','church-admin' ) ).'</th><th>'.esc_html( __('Series','church-admin' ) ).'</th><th>'.esc_html( __('Plays','church-admin' ) ).'</th><th>'.esc_html( __('Shortcode','church-admin' ) ).'</th></tr>';
            
            $table='<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead>'."\r\n".'<tfoot>'.$theader.'</tfoot>'."\r\n".'<tbody>';
            foreach( $results AS $row)
			{
			    
				if(file_exists(plugin_dir_path( $path.$row->file_name) ))  {
                    $okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="32" height="32" />';
                }else{
                    $okay='<img src="'.plugins_url('images/red.png',dirname(__FILE__) ) .'" width="32" height="32" />';
                }
                
                $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=upload-mp3&amp;id='.$row->file_id,'upload-mp3').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
                $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-media-file&amp;id='.$row->file_id,'delete-media-file').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
                $series_name=$wpdb->get_var('SELECT series_name FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_id="'.esc_sql( $row->series_id).'"');
                if(!empty( $row->file_name)&&file_exists( $path.$row->file_name) )  {
                    $file='<a href="'.esc_url( $url.$row->file_name).'">'.esc_html( $row->file_name).'</a>'; $okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" />';
                }
                elseif(!empty( $row->external_file) )  {
                    $file='<a class="external_file" href="'.esc_url( $row->external_file).'">'.esc_html( $row->external_file).'</a>'; 
                    $okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" />';
                    
                    
                
                }
                else
                {
                    $file='&nbsp;'; $okay='<img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" />';
                }
                $videoPlays='';
                
                if(!empty( $row->video_url) )
                {
                    
                    $embed=church_admin_generateVideoEmbedUrl( $row->video_url);
                    
                    $views=church_admin_youtube_views_api( $embed['id'] );
                    if(!empty( $views) )  {
                        //translators: %1$s is a number
                        $videoPlays='<br>'.esc_html(sprintf(__('%1$s views','church-admin' ) ,$views));
                    }
                }
                $embed_code=!empty($row->embed_code)?__('Yes','church-admin'):__('No','church-admin');
                //output table row
                //translators: %1$s is a number
                $table.='<tr>
                    <td data-colname="'.esc_html( __('Sermon title','church-admin' ) ).'" class="column-primary">'.esc_html( $row->file_title).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                    <td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
                    <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
                    <td data-colname="'.esc_html( __('Published','church-admin' ) ).'">'.date(get_option('date_format').' '.get_option('time_format'),strtotime( $row->pub_date) ).'</td>
                    <td data-colname="'.esc_html( __('Speakers','church-admin' ) ).'" class="ca-names">'.esc_html(church_admin_get_people( $row->speaker) ).'</td>
                    <td data-colname="'.esc_html( __('Sermon filename','church-admin' ) ).'">'.$file.'</td>
                    <td data-colname="'.esc_html( __('File exists','church-admin' ) ).'">'.$okay.'</td>
                    <td data-colname="'.esc_html( __('Sermon length','church-admin' ) ).'"><span id="length'.(int)$row->file_id.'">'.esc_html( $row->length).'</span></td>
                    <td data-colname="'.esc_html( __('Video URL','church-admin' ) ).'">'.$row->video_url.'</td>
                    <td data-colname="'.esc_html( __('Embed','church-admin' ) ).'">'.$embed_code.'</td>
                    <td data-colname="'.esc_html( __('Series name','church-admin' ) ).'">'.esc_html( $series_name).'</td>
                    <td data-colname="'.esc_html( __('Video plays','church-admin' ) ).'">'.esc_html(sprintf(__('%1$s mp3 plays','church-admin' ) ,intval( $row->plays) )).$videoPlays.'</td>
                    <td data-colname="'.esc_html( __('Shortcode','church-admin' ) ).'">[church_admin type="single-sermon" file_id="'.intval( $row->file_id).'"]</td>
                </tr>';

			}
       
            $table.='</tbody></table>';
            echo $table;
        }
    }
    else
    {
    
    
    
    
            //grab files from table
            $count=$wpdb->get_var('SELECT SUM(plays) FROM '.$wpdb->prefix.'church_admin_sermon_files');
            $results=$wpdb->get_results('SELECT a.* FROM '.$wpdb->prefix.'church_admin_sermon_files a  ORDER BY pub_date DESC');

            if(!empty( $count) ){
             //translators: %1$s is a number
                echo'<p>'.sprintf(__('%1$s total sermon plays','church_admin'),$count).'</p>';
            }
            $total_records= $wpdb->num_rows;
            echo '<p>total records: '.$total_records.'</p>';
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.class.php');
            
            if( $total_records > 0)
            {
              $p = new caPagination;
              $p->items( $total_records);

              $page_limit=5;//get_option('church_admin_pagination_limit');
              if ( empty( $page_limit) )  {$page_limit=20;update_option('church_admin_pagination_limit',20);}
              $p->limit( $page_limit); // Limit entries per page
                if(!empty( $_GET['order'] ) )
                {
                    switch( $_GET['order'] )
                    {
                        case 'title':
                            $order='a.file_title ASC';
                            $p->target(wp_nonce_url("admin.php?page=church_admin%2Findex.php&action=media&section=media&order=title",'media'));
                        break;
                        case 'speakers':
                            $order='a.speaker ASC';
                            $p->target(wp_nonce_url("admin.php?page=church_admin%2Findex.php&action=media&section=media&order=speaker",'media'));
                        break;    
                        case 'series':
                            $order='b.series_name ASC';
                            $p->target(wp_nonce_url("admin.php?page=church_admin%2Findex.php&action=media&section=media&order=series",'media'));
                        break;
                        case 'plays':
                            $order='a.plays DESC';
                            $p->target(wp_nonce_url("admin.php?page=church_admin%2Findex.php&action=media&section=media&order=plays",'media'));
                        break;
                        default:
                            $order='pub_date DESC';
                            $p->target("admin.php?page=church_admin%2Findex.php&action=media&section=media");
                        break;
                    }
                }
                 
                else
                {   $order='pub_date DESC';
                  $p->target(wp_nonce_url("admin.php?page=church_admin%2Findex.php&action=media&section=podcast",'media'));
                }
             $current_page = !empty($_GET['page']) ? (int)$_GET['page']:1;
              
              $p->currentPage( $current_page); // Gets and validates the current page
              $p->calculate(); // Calculates what to show
              $p->parameterName('paging');
              $p->adjacents(1); //No. of page away from the current page
              if(!isset( $_GET['paging'] ) )
              {
                  $p->page = 1;
              }
              else
              {
                  $p->page = $_GET['paging'];
              }
              //Query for limit paging
              $limit = " LIMIT " . ( $p->page - 1) * $p->limit  . ", " . $p->limit;


              // Pagination
                echo'<div class="tablenav"><div class="tablenav-pages">';
                echo $p->getOutput();
                echo '</div></div>';
                
              //Pagination
            }else{$limit='';}
           
            /*
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/pagination.php');
            $paginator = new Paginator();
            $paginator->total = $total_records;
            $paginator->paginate();
            $current_page = !empty($paginator->currentPage) ? $paginator->currentPage : 1;
            $limit1 = !empty($paginator->currentPage) ? ($paginator->currentPage-1)*$paginator->itemsPerPage : 1;
            $limit = "LIMIT $limit1,  $paginator->itemsPerPage";
            */
            $order='pub_date DESC';
            $results=$wpdb->get_results('SELECT a.*,b.series_name FROM '.$wpdb->prefix.'church_admin_sermon_files a LEFT JOIN '.$wpdb->prefix.'church_admin_sermon_series b  ON a.series_id=b.series_id  ORDER BY '.$order.' '.$limit);
            church_admin_debug($wpdb->last_query);
            if(!empty( $results ))
            {//results
                church_admin_debug($results);
                $table='';
                $header='<tr><th class="manage-column check-column"><input type="checkbox" id="select-all-files"></th><th class="column-primary"><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=media&section=media&order=title&paging='.(int)$current_page,'media').'">'.esc_html( __('Title','church-admin' ) ).'</a></th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Publ. Date','church-admin' ) ).'</th><th><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=media&section=media&order=speaker&paging='.(int)$current_page,'media').'">'.esc_html( __('Speakers','church-admin' ) ).'</a></th><th>'.esc_html( __('Mp3 File','church-admin' ) ).'</th><th>'.esc_html( __('File Okay?','church-admin' ) ).'</th><th>'.esc_html( __('Length','church-admin' ) ).'</th><th>'.esc_html( __('Media','church-admin' ) ).'</th><th>'.esc_html( __('Embed','church-admin' ) ).'</th><th><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=media&section=media&order=series&paging='.(int)$current_page,'media').'">'.esc_html( __('Series','church-admin' ) ).'</a></th><th><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=media&section=media&order=plays&paging='.(int)$current_page,'media').'">'.esc_html( __('Plays','church-admin' ) ).'</a></th><th>'.esc_html( __('Shortcode','church-admin' ) ).'</th></tr>';
                
                //bulk actions
                $table.='<form action="admin.php?page=church_admin/index.php&action=media" method="post">';
                $table.=wp_nonce_field('media');
                $table.='<p><select class="church-admin-form-control" name="method"><option>'.esc_html( __('Bulk actions','church-admin' ) ).'</option>';
                $table.='<option value="remove-database">'.esc_html( __('Remove from database','church-admin' ) ).'</option>';
                $table.='<option value="delete-files">'.esc_html( __('Delete files and remove','church-admin' ) ).'</option>';
                $table.='</select><input type="submit" class="button-secondary" value="'.esc_html( __('Apply','church-admin' ) ).'" /></p>';


                $table.='<table class="widefat striped wp-list-table"><thead>'.$header.'</thead>'."\r\n".'<tbody>';
                foreach( $results AS $row)
                {
                    if(file_exists(plugin_dir_path( $path.$row->file_name) ))  {$okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" width="32" height="32" />';}else{$okay='<img alt="'.esc_html( __('No file','church-admin' ) ).'" src="'.plugins_url('images/red.png',dirname(__FILE__) ) .'" width="32" height="32" />';}
                    $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=upload-mp3&amp;id='.$row->file_id,'upload-mp3').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
                    $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-media-file&amp;id='.$row->file_id,'delete-media-file').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
                    
                    if(!empty( $row->file_name)&&file_exists( $path.$row->file_name) )  {
                        $file='<a href="'.esc_url( $url.$row->file_name).'">'.esc_html( $row->file_name).'</a>'; 
                        $okay='<img alt="'.esc_html( __('File OK','church-admin' ) ).'" src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" />';
                    }
                    elseif(!empty( $row->external_file) )  {
                        $file='<a href="'.esc_url( $row->external_file).'">'.esc_html( $row->external_file).'</a>'; 
                        $okay='<img src="'.plugins_url('images/green.png',dirname(__FILE__) ) .'" />';
                    
                        if(empty($row->length))
                        {
                            //do some sneaky js and ajax magic to work this out and update the database
                            $file.='<script>
                            jQuery(document).ready(function($){
                    
                            var url="'.esc_url($row->external_file).'";
                            var au'.(int)$row->file_id.' = document.createElement("audio");

                            // Define the URL of the MP3 audio file
                            au'.(int)$row->file_id.'.src = url;
                            $("#audio_player'.(int)$row->file_id.'").html(au'.(int)$row->file_id.');
                            // Once the metadata has been loaded, display the duration in the console
                            au'.(int)$row->file_id.'.addEventListener("loadedmetadata", function(){
                                    
                                    var duration = parseInt(au'.(int)$row->file_id.'.duration);
                                    //convert to ISO format
                                    var date'.(int)$row->file_id.' = new Date(null);
                                    date'.(int)$row->file_id.'.setSeconds(duration); 
                                    var hhmmssFormat'.(int)$row->file_id.' = date'.(int)$row->file_id.'.toISOString().substr(11, 8);
                                    console.log("External duration is "+ hhmmssFormat'.(int)$row->file_id.');
                                    $("#length'.(int)$row->file_id.'").html(hhmmssFormat'.(int)$row->file_id.');
                                    var nonce="'.wp_create_nonce('sermon_length').'";
                                    var args'.(int)$row->file_id.'={"action":"church_admin","method":"sermon_length","nonce":nonce,"id":'.(int)$row->file_id.',"length":hhmmssFormat'.(int)$row->file_id.'};
                                    console.log(args'.(int)$row->file_id.');
                                    $.ajax({
                                        url: ajaxurl,
                                        type: "post",
                                        data:  args'.(int)$row->file_id.',
                                        success: function(response) {
                                          
                                            $("#length'.(int)$row->file_id.'").html(response);
                                            }
                                      });

                                },false);
                            

                    });
                    
                    </script>';

                        }
                    
                    }
                    else{$file='&nbsp;'; $okay='<img src="'.plugins_url('images/red.png',dirname(__FILE__) ).'" />';}
                    $videoPlays='';
                
                    if(!empty( $row->video_url) )
                    {

                        $embed=church_admin_generateVideoEmbedUrl( $row->video_url);
                        
                        $views=church_admin_youtube_views_api( $embed['id'] );
                        if(!empty( $views) )  {
                            //translators: %1$s is a number of video views
                            $videoPlays='<br>'.esc_html(sprintf(__('%1$s views','church-admin' ) ,$views));
                        }
                    }
                    $embed_code=!empty($row->embed_code)?__('Yes','church-admin'):__('No','church-admin');
                    //translators: %1$s is a number
                    $table.='<tr>
                        <th class="check-column"><input type="checkbox" name="file_id[]" value="'.(int)$row->file_id.'" /></th>
                        <td data-colname="'.esc_html( __('Sermon title','church-admin' ) ).'" class="column-primary">'.esc_html( $row->file_title).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                        <td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
                        <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
                        <td data-colname="'.esc_html( __('Published','church-admin' ) ).'">'.date(get_option('date_format').' '.get_option('time_format'),strtotime( $row->pub_date) ).'</td>
                        <td data-colname="'.esc_html( __('Speakers','church-admin' ) ).'" class="ca-names">'.esc_html(church_admin_get_people( $row->speaker) ).'</td>
                        <td data-colname="'.esc_html( __('Sermon filename','church-admin' ) ).'">'.$file.'</td>
                        <td data-colname="'.esc_html( __('File exists','church-admin' ) ).'">'.$okay.'</td>
                        <td data-colname="'.esc_html( __('Sermon length','church-admin' ) ).'"><span id="length'.(int)$row->file_id.'">'.esc_html( $row->length).'</span></td>
                        <td data-colname="'.esc_html( __('Video URL','church-admin' ) ).'">'.$row->video_url.'</td>
                        <td data-colname="'.esc_html( __('Embed code','church-admin' ) ).'">'.$embed_code.'</td>
                        <td data-colname="'.esc_html( __('Series name','church-admin' ) ).'">'.esc_html( $row->series_name).'</td>
                        
                        <td data-colname="'.esc_html( __('Video plays','church-admin' ) ).'">'.esc_html(sprintf(__('%1$s mp3 plays','church-admin' ) ,intval( $row->plays) )).$videoPlays.'</td>
                        <td data-colname="'.esc_html( __('Shortcode','church-admin' ) ).'">[church_admin type="single-sermon" file_id="'.intval( $row->file_id).'"]</td>
                    </tr>';
                }

                $table.='</tbody></table>';
                $table.='</form>';
                echo $table;
            }//end results
            else
            {
                echo'<p>'.esc_html( __('No files stored yet','church-admin' ) ).'</p>';
                echo'<p><a class="button-secondary" href="'.wp_nonce_url($church_admin_url.'&action=itunes-importer&amp;section=media','itunes-importer').'">'.esc_html( __('Import Podcast Feed','church-admin' ) ).'</a></p>';
            
                echo'<p><a class="button-primary" href="'.wp_nonce_url($church_admin_url.'&action=upload-mp3&amp;section=media','upload-mp3').'">'.esc_html( __('Upload Sermon','church-admin' ) ).'</a></p>';
            }
    }
}


function ca_podcast_delete_media_file( $id=NULL)
{
    /**
     *
     * Delete File
     *
     * @author  Andy Moyle
     * @param    $id=null
     * @return   html string
     * @version  0.1
     *
     */
    global $wpdb,$rm_podcast_settings;
    if(!empty( $id) )
    {//non empty $id
        $data=$wpdb->get_row('SELECT a.*,b.series_name AS series_name FROM '.$wpdb->prefix.'church_admin_sermon_files a , '.$wpdb->prefix.'church_admin_sermon_series b WHERE a.file_id="'.esc_sql( $id).'" AND a.series_id=b.series_id');
        if(!empty( $_POST['sure'] ) )
        {//end sure so delete
			$upload_dir = wp_upload_dir();
            if(!empty( $data->file_name)&&file_exists( $upload_dir['basedir'].'/sermons/'.$data->file_name) )unlink( $upload_dir['basedir'].'/sermons/'.$data->file_name);
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.esc_sql( $id).'"');
            ca_podcast_xml();//update podcast feed
            echo'<div class="notice notice-success inline">'.esc_html( $data->file_title).' '.esc_html( __('from','church-admin' ) ).' '.esc_html( $data->series_name).' '.esc_html( __('deleted','church-admin' ) ).'</p></div>';
            ca_podcast_list_files();
        }//end sure so delete
        else
        {
            //translators: %1$s is a title, %2$s is the series name
            echo'<p>'.esc_html(sprintf(__('Are you sure you want to delete %1$s sermon form %2s?','church-admin' ) , $data->file_title, $data->series_name) );
            echo'<form action="" method="post"><input type="hidden" name="sure" value="YES" /><input type="submit" value="'.esc_html( __('Yes','church-admin' ) ).'" class="button-primary" /></form></p>';
        }

    }//end non empty $id
    else{echo'<p>'.esc_html( __('No file specified','church-admin' ) ).' '.(int)$id.'</p>';}
}

function ca_podcast_check_files()
{
        /**
     *
     * Checks Files in media directory, table of non db stored files
     *
     * @author  Andy Moyle
     * @param    $id=null
     * @return   html string
     * @version  0.2
     * 0.2 fixed empty $form array 2016-03-20
     *
     */
    global $wpdb,$rm_podcast_settings;
    $table='';
	$upload_dir = wp_upload_dir();
            $path=$upload_dir['basedir'].'/sermons/';
            $url=$upload_dir['baseurl'].'/sermons/';
	$files=scandir( $path);
    $exclude_list = array(".", "..", "index.php","podcast.xml",".htaccess");
    $files = array_diff( $files, $exclude_list);


        echo '<h2>'.esc_html( __('Unattached Media Files','church-admin' ) ).'</h2>';
        $file_count=0;
      
        $table.='<table class="widefat striped"><thead><tr><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Filename','church-admin' ) ).'</th><th>'.esc_html( __('Play','church-admin' ) ).'</th><th>'.esc_html( __('Add to podcast','church-admin' ) ).'</th></tr></thead><tfoot><tr><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Filename','church-admin' ) ).'</th><th>'.esc_html( __('Play','church-admin' ) ).'</th><th>'.esc_html( __('Add to podcast','church-admin' ) ).'</th></tr></tfoot><tbody>';

        foreach( $files as $entry)
        {
            $check=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_name="'.esc_sql(basename( $entry) ).'"');

            if(is_file( $path.$entry)&&!$check)
            {
                $file_count++;
                $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-media-file&file='.esc_html( $entry),'delete-media-file').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
                $add='<a class="button-primary" target="_blank" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=add-media-file&file='.esc_html( $entry),'add-media-file').'">'.esc_html( __('Add to podcast','church-admin' ) ).'</a>';
                $table.='<tr ><td style="vertical-align:middle;height75px;">'.$delete.'</td><td  style="vertical-align:middle;height75px;">'.$entry.'</td><td  style="vertical-align:middle;height75px;"><audio controls><source src="'.esc_url($url.$entry).'"></audio></td><td  style="vertical-align:middle;height75px;">'.$add.'</td></tr>';
            }
        }
        $table.='</tbody></table>';
        
        if(empty($file_count)){
            echo '<p>'.esc_html(__('No unconnected media files found','church-admin')).'</p>';
        }
        else{
            echo $table;
        }
       

}

function ca_podcast_file_add( $file_name=NULL)
{
    /**
     *
     * Edit podcast file from directory to podcasts
     *
     * @author  Andy Moyle
     * @param    $id=null
     * @return   html string
     * @version  0.2
     * 0.2 fixed no blog post title and featured image
     *
     */
 	$settings=get_option('ca_podcast_settings');
    if(!$file_name)wp_die("No file specified");
	$upload_dir = wp_upload_dir();
            $path=$upload_dir['basedir'].'/sermons/';
            $url=$upload_dir['baseurl'].'/sermons/';
	$current_data=new stdClass();
	$settings=get_option('ca_podcast_settings');

    global $wpdb;
    //$wpdb->show_errors();
    $file_name=basename( $file_name);
    $sanitizedFilename=sanitize_file_name( $file_name);
    $file_url=$url.$sanitizedFilename;
    //translators: %1$s is a file name
    echo'<h2>'.esc_html(sprintf(__('Add File - %1$s','church-admin' ) ,$file_name)).'</h2>';
    if(!empty( $_POST['save_file'] )  )
    {//process form
        
        if( $sanitizedFilename!=$file_name)
        {
        if(!rename( $path.$file_name,$path.$sanitizedFilename) ) exit(__('Filename needs to be sanitized no special characters or whitespaces','church-admin') );
        }
        $speaker=esc_sql( sanitize_text_field(stripslashes($_POST['speaker'] ) ) );
        $length="00:00";
        if ( empty( $sanitizedFilename) &&!empty( $current_data->file_name) )
        {
            //check and fix filename
            $file_name=sanitize_file_name( $current_data->file_name);
            
        }
        if(!empty( $sanitizedFilename)&&file_exists( $path.$sanitizedFilename) )
		{
		
            //from 3.6.20 use WordPress native function
            $audiometadata=wp_read_audio_metadata( $path.$sanitizedFilename );
            $length=!empty( $audiometadata['length_formatted'] )?$audiometadata['length_formatted']:null;


        }
        $form=$sqlsafe=array();
        foreach( $_POST AS $key=>$value)  {$form[$key]=sanitize_text_field(stripslashes( $value) );}
        foreach( $_POST AS $key=>$value)  {$sqlsafe[$key]=esc_sql(sanitize_text_field(stripslashes( $value) ));}
        $allowed=array(
    		'a' => array(
        		'href' => array(),
        		'title' => array()
    		),
    		'br' => array(),
    		'em' => array(),
    		'p' =>array(),
    		'img'=>array(),
    		'strong' => array(),
		);
        $transcript=esc_sql(wp_kses_post( $_POST['transcript'] ,$allowed) );
        $passages=esc_sql(church_admin_podcast_readings( $form['passages'] ) );
        $pub_date = !empty( $_POST['pub_date'] ) ? sanitize_text_field(stripslashes($_POST['pub_date'])) : wp_date('Y-m-d');
        if(!church_admin_datecheck($pub_date)){$pub_date = wp_date('Y-m-d');}
        $sqlsafe['pub_date'] = esc_sql($pub_date);
        if(!empty( $sqlsafe['pub_time'] ) )
        {
            $sqlsafe['pub_date'].=' '.$sqlsafe['pub_time'].':00';
        }
        else
        {
            $sqlsafe['pub_date'].=' 12:00:00';    
        }
        if(!empty( $_POST['private'] ) )  {$private="1";}else{$private="0";}

        if ( empty( $id) )$id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_name="'.esc_sql($sanitizedFilename).'"' );
        if(!empty( $id) )
        {//update
            $sql='UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET video_url="'.$sqlsafe['video_url'].'",pub_date="'.$sqlsafe['pub_date'].'", length="'.$length.'", last_modified="'.date("Y-m-d H:i:s" ).'",private="'.$private.'",file_name="'.esc_sql( $sanitizedFilename).'" ,file_subtitle= "'.$sqlsafe['file_subtitle'].'",file_title="'.$sqlsafe['file_title'].'" , file_description="'.$sqlsafe['file_description'].'" , series_id="'.$sqlsafe['series_id'].'" ,service_id="'.$sqlsafe['service_id'].'" , speaker="'.$speaker.'",transcript="'.$transcript.'",bible_passages="'.$passages.'",bible_texts="'.$sqlsafe['passages'].'",file_slug="'.esc_sql(sanitize_title( $form['file_title'] ) ).'" WHERE file_id="'.esc_sql( $id).'"';

            $wpdb->query( $sql);
        }//end update
        else
        {//insert
            $sql='INSERT INTO '.$wpdb->prefix.'church_admin_sermon_files (file_name,file_subtitle,file_title,file_description,private,length,series_id,service_id,speaker,pub_date,last_modified,video_url,transcript,bible_passages,bible_texts,file_slug)VALUES("'.esc_sql( $sanitizedFilename).'","'.$sqlsafe['file_subtitle'].'","'.$sqlsafe['file_title'].'","'.$sqlsafe['file_description'].'" ,"'.$private.'","'.$length.'","'.$sqlsafe['series_id'].'","'.$sqlsafe['service_id'].'","'.$speaker.'" ,"'.$sqlsafe['pub_date'].'","'.date("Y-m-d H:i:s" ).'","'.$sqlsafe['video_url'].'","'.$transcript.'","'.$passages.'","'.$sqlsafe['passages'].'","'.esc_sql(sanitize_title( $form['file_title'] ) ).'")';

            $wpdb->query( $sql);
            $id=$wpdb->insert_id;
        }//end insert
      
        //email section
        if(!empty( $_POST['email_send'] ) )
        {
          $check=$wpdb->get_var('SELECT email_sent FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.esc_sql( $id).'"');
          if( $check=="0000-00-00")church_admin_send_sermon( $id);
        }
		//post if set
		if(!empty( $_POST['blog'] ) )
		{

			$settings=church_admin_handle_podcast_image( $settings);
			$title=$form['file_title'];
			$content='[church_admin type="single-sermon" file_id="'.(int)$id.'"]';
			$cat_id=wp_create_category( __('Sermon Mp3s','church-admin') );
			$postID=$wpdb->get_var('SELECT postID FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.esc_sql( $id).'"');

			$args=array('post_title'=>$title,'post_content'=>$content,'post_type'=>'post','post_status'=>'publish');

			if ( empty( $postID) )$args['ID']=$postID;
			$postID=wp_insert_post( $args);
			$message ='<p><a href="'.esc_url( get_permalink( $postID) ).'">'.esc_html( __('Sermon posted.','church-admin' ) ).' </a></p>';

			wp_set_post_categories( $postID,array( $cat_id) );
			if(!empty( $settings['thumbnail_id'] ) )  {set_post_thumbnail( $postID, $settings['thumbnail_id'] );echo'<p>'.esc_html( __('Thumbnail set.','church-admin' ) ).'</p>';}
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET postID="'.(int)$postID.'"');
		}
		//ping where sermons are shown
		$id=church_admin_get_id_by_shortcode('podcast');
		if(!empty( $id) )
		{
			generic_ping( $id);
			$datetime = date("Y-m-d H:i:s");
			$wpdb->query( "UPDATE `$wpdb->posts` SET `post_modified` = '".$datetime."' WHERE `ID` = '".(int)$id."'" );
		}
        ca_podcast_xml();//update podcast feed
        church_admin_update_last_sermon_series();
        echo'<div class="notice notice-success inline"><p>'.esc_html( __('File Saved','church-admin' ) ).'</p></div>';
        echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=upload-mp3&amp;section=podcast','upload-mp3').'">'.esc_html( __('Upload or add external mp3 File','church-admin' ) ).'</a></p>';
          echo '<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=check-media-files&amp;sermon=podcast','check-media-files').'">'.esc_html( __('Add Already Uploaded Files','church-admin' ) ).'</a></p>';
        echo'<p><a class="button-secondary"  href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=list_sermon_series&amp;section=podcast",'list_sermon_series').'">'.esc_html( __('List Sermon Series','church-admin' ) ).'</a></p>';
        ca_podcast_list_files();
    }//end process form
    else
    {//form

    	
        echo '<form action="" method="POST" id="churchAdminForm" enctype="multipart/form-data">';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('File title (required)','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" required="required" name="file_title" id="file_name" ';
        if(!empty( $current_data->file_title) ) echo 'value="'.esc_html( $current_data->file_title).'"';
        echo'/></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('File SubTitle (a few words - required)','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" required="required"  name="file_subtitle" id="file_subtitle" ';
        if(!empty( $current_data->file_subtitle) ) echo 'value="'.esc_html( $current_data->file_subtitle ).'"';
        echo'/></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('File Description','church-admin' ) ).'</label>';
        echo '<textarea name="file_description">';
        if(!empty( $current_data->file_description) )echo esc_textarea( $current_data->file_description );
        echo'</textarea></div>';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Scripture passage','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" class="large-text" name="passages" placeholder="'.esc_html( __('Bible passages','church-admin' ) ).'" ';
        if(!empty( $current_data->bible_text) ) echo 'value="'.esc_html( $current_data->bible_text ).'" ';
        echo '</div>';
        echo'<div class="church-admin-checkbox"><label>'.esc_html( __('Logged in only?','church-admin' ) ).'</label> <input type="checkbox" name="private" value="yes" /></p>';
        $series_id= !empty($current_data->series_id) ? (int)$current_data->series_id : null;
        $ev_res=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY series_id DESC');
        if( $ev_res)
        {
            echo'<div class="church-admin-form-group"><label>'.esc_html( __('Service','church-admin' ) ).'</label><select class="church-admin-form-control" name="series_id">';
            
            foreach( $ev_res AS $series_row)
            {
                echo '<option value="'.(int)$series_row->series_id.'" '.selected($series_id,$series_row->series_id,FALSE).'>'.esc_html( $series_row->series_name).'</option>';
                

            }
            echo '</select></div>';
        }
        //service
        $service_id= !empty($current_data->service_id) ? (int)$current_data->service_id : null;
        $service_res=$wpdb->get_results('SELECT CONCAT_WS(" ",service_name,service_time) AS service_name,service_id FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_id DESC');
        if( $service_res)
        {
            echo'<div class="church-admin-form-group"><label>'.esc_html( __('Service','church-admin' ) ).'</label><select class="church-admin-form-control" name="service_id">';
            
            foreach( $service_res AS $service_row)
            {
                echo '<option value="'.(int)$service_row->service_id.'" '.selected($service_id,$service_row->service_id, FALSE).'>'.esc_html( $service_row->service_name).'</option>';

            }
            echo '</select></div>';
        }
        echo'<div class="church-admin-form-group"><label>Speaker</label>';
        echo church_admin_autocomplete('speaker','friends','to', NULL);
        echo'</div>';
        if ( empty( $current_data->pub_date) )
        {
            $current_data->pub_date=date('Y-m-d ');
            $current_time='12:00:00';
        }
        else
        {
            //make sure only date component passed to date picker!
            $current_time=mysql2date('H:i:s',$current_data->pub_date);
            $current_data->pub_date=date('Y-m-d',strtotime( $current_data->pub_date) );
        }
        //javascript to bring up date picker
       
	
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Publication Date','church-admin' ) ).'</label>'.church_admin_date_picker(NULL,'pub_date',FALSE,date('Y-m-d',strtotime("-15 years") ),NULL,'pub_date','pub_date',FALSE).'</div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Publication Time','church-admin' ) ).'</label><input type="time" step=1 name="pub_time"';
         echo ' value="'.mysql2date('H:i:s',$current_time).'" ';
        echo'/></div>';
        
        
        echo'<input type="hidden" name="external_duration" id="external_duration" />';
            //javascript to detect length of external audio
                echo'<script>
                jQuery(document).ready(function($){
                  
                        var url="'.esc_url($file_url).'";
                        var au = document.createElement("audio");

                        // Define the URL of the MP3 audio file
                        au.src = url;
                        $("#audio_player").html(au);
                        // Once the metadata has been loaded, display the duration in the console
                        au.addEventListener("loadedmetadata", function(){
                                
                                var duration = parseInt(au.duration);
                                //convert to ISO format
                                var date = new Date(null);
                                date.setSeconds(duration); 
                                var hhmmssFormat = date.toISOString().substr(11, 8);
                                console.log("External duration is "+ hhmmssFormat);
                                $("#external_duration").val(hhmmssFormat);
                            },false);
                        

                });
                
                </script>';
         echo'<div class="church-admin-form-group"><label>'.esc_html( __('Video URL','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="video_url" id="video_url"';
		if(!empty( $current_data->video_url) )echo' value="'.esc_url( $current_data->video_url).'" ';
		echo'/>'.esc_html( __('Add [VIDEO_URL] to your sermon files template to display','church-admin' ) ).'</div';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Notes/Transcript','church-admin' ) ).'</label>';
        if ( empty( $current_data->transcript) )  {$transcript='';}else{$transcript=$current_data->transcript;}
        wp_editor( $transcript, 'transcript' );
        echo'</div>';
      
		echo'<div class="church-admin-checkbox"> <input type="checkbox" checked name="blog" value="1" /><label>'.esc_html( __('Blog the sermon','church-admin' ) ).'</label></div>';
        echo '<div class="church-admin-form-group"><label>&nbsp;</label><input type="hidden" name="save_file" value="save_file" />'.wp_nonce_field('upload-mp3').'<input type="submit" class="button-primary" value="'.esc_html( __('Save File','church-admin' ) ).'" /></div></form>';
    }//form


}
function ca_podcast_file_delete( $file_name=NULL)
{
	$upload_dir = wp_upload_dir();
            $path=$upload_dir['basedir'].'/sermons/';
            $url=$upload_dir['baseurl'].'/sermons/';
    if( $file_name &&is_file( $path.basename( $file_name) ))
    {
        unlink( $path.basename( $file_name) );
        echo'<div class="notice notice-success inline"><p>'.esc_html(basename( $file_name) ).' '.esc_html( __('deleted','church-admin' ) ).'</p></div>';
        ca_podcast_check_files();
    }
}




function church_admin_latest_sermons_widget_output( $limit,$title)
{
	global $wpdb;
	$upload_dir = wp_upload_dir();
            $path=$upload_dir['basedir'].'/sermons/';
            $url=$upload_dir['baseurl'].'/sermons/';
	
	$out='<div class="church-admin-sermons-widget">';
	$ca_podcast_settings=get_option('ca_podcast_settings');

	if(!empty( $ca_podcast_settings['link'] ) )$out.='<p><a title="Download on Itunes" href="'.$ca_podcast_settings['itunes_link'].'">
    <img  alt="badge_itunes-lrg" src="'.plugins_url('/images/badge_itunes-lrg.png',dirname(__FILE__) ).'" width="110" height="40" /></a></p>';
	$options=get_option('church_admin_latest_sermons_widget');

	$limit=$options['sermons'];
	if ( empty( $limit) )$limit=5;
	$sermons=$wpdb->get_results('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a, '.$wpdb->prefix.'church_admin_sermon_series b WHERE a.series_id=b.series_id ORDER BY a.pub_date DESC LIMIT '.$limit);
	if(!empty( $sermons) )
	{
		foreach( $sermons AS $data)
		{
			$speaker=church_admin_get_people( $data->speaker);
			/*if(!empty( $sermon->file_name) )  {$out.='<p><a href="'.esc_url( $url.$sermon->file_name).'"  title="'.esc_html( $sermon->file_title).'">'.esc_html( $sermon->file_title).'</a>';}else{$out.='<p><a href="'.esc_url( $sermon->external_file).'"  title="'.esc_html( $sermon->file_title).'">'.esc_html( $sermon->file_title).'</a>';}
			$out.='<br>By '.esc_html( $speaker).' on '.mysql2date(get_option('date_format'),$sermon->pub_date).'<br>';

			$out.='<audio class="sermonmp3" id="'.$sermon->file_id.'" src="'.esc_url( $url.$sermon->file_name).'" preload="none"></audio><br>';
                */
            if(!empty( $data->file_title) )$out.='<h2>'.esc_html( $data->file_title).'</h2>';
				if(!empty( $data->video_url) )
                {
                    if(strpos( $data->video_url, 'amazonaws.com/') !== false)
                    {
                       $out.='<video class="ca-video" width="560" height="315" controls><source src="'.$data->video_url.'" type="video/mp4">Your browser does not support the video tag.
    </video>'; 
                    }else
                    {
                        $video=church_admin_generateVideoEmbedUrl( $data->video_url);
                        $videoUrl=$video['embed'];
                        $out.='<iframe class="ca-video" width="560" height="315" src="'.esc_url( $videoUrl).'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                    }
                }

				if(!empty( $data->file_name)&& file_exists( $path.$data->file_name) )
                {
                    $out.='<p><audio class="sermonmp3" data-id="'.esc_html( $data->file_id).'" src="'.esc_url( $url.$data->file_name).'" preload="auto" controls></audio></p>';
				    $download='<a href="'.esc_url( $url.$data->file_name).'" class="mp3download" data-id="'.(int)$data->file_id.'" title="'.esc_html( $data->file_title).'" download>'.esc_html( $data->file_title).'</a>';
				}
				elseif(!empty( $data->external_file) )
				{
						$out.='<p><audio class="sermonmp3" data-id="'.esc_html( $data->file_id).'" src="'.esc_url( $data->external_file).'" preload="auto" controls></audio></p>';

						$download='<a href="'.esc_url( $data->external_file).'" class="mp3download" data-id="'.(int)$data->file_id.'" title="'.esc_html( $data->file_title).'" download>'.esc_html( $data->file_title).'</a>';
				}
		}
        $out.='<script>var mp3nonce="'.wp_create_nonce("church_admin_mp3_play").';"</script>';
	}



    $out.='</div>';
    return $out;

}


function church_admin_podcast_readings( $passages)
{
	if(!empty( $passages) )
	{
	$version=get_option('church_admin_bible_version');
	$readings=explode(",",$passages);
 $out='';
	$passages=array();
	foreach( $readings AS $key=>$value)
		{

  			$passage = urlencode( $value);

  			switch( $version)
  			{

  				case'KJV':
  					$out='';
  					$url='https://bible-api.com/'.$passage.'?translation=kjv';
  					$ch = curl_init( $url);
  					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
  					$response = json_decode(curl_exec( $ch),true);
  					if( $response)
  					{
  						curl_close( $ch);
  						$oldChapter='';
  						$out='<p>';
  						foreach( $response['verses']AS $verses)
  						{
  							$chapter=$verses['chapter'];
  							//only outpt chapter number on new chapter
  			 				if( $chapter!=$oldChapter)  {$out.='<span style="font-size:larger">'.$verses['chapter'].':'.$verses['verse'].'</span> ';}
  			 				else{$out.='<span style="font-size:smaller">'.$verses['verse'].'</span> ';}
  			 				//output scripture text
  			 				$out.=$verses['text'].'<br>';
  			 				$oldChapter=$chapter;
  						}
  						$out.='</p>';
  					}
  					$passages[$key]='<h2>'.$value.'</h2><div class="bible-text" id="passage'.$key.'">'.$out.'</div>';
  				break;
  				//old style vesrion using api
  				case'KJV':
				case "ostervald":
				case "schlachter":
				case "statenvertaling":
				case "swedish":
				case "bibelselskap":
				case "sse":
				case "lithuanian":
  					$url='https://api.preachingcentral.com/bible.php?passage='.$passage.'&version='.$version;

  					$ch = curl_init( $url);
  					curl_setopt( $ch,CURLOPT_FAILONERROR,true);
  					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
  					$out='<p>';
  					$response = simplexml_load_string(curl_exec( $ch) );

  					$oldChapter='';
  					foreach( $response->range->item AS $id=>$passage)
  					{
  						//church_admin_debug( $passage->chapter.' '.$oldChapter."\r\n");
  						//only output chapter number on new chapter
  			 			if(intval( $passage->chapter) != intval( $oldChapter) )  {$out.='<span style="font-size:larger">'.$passage->chapter.':'.$passage->verse.'</span> ';}
  			 			else{$out.='<span style="font-size:smaller">'.$passage->verse.'</span> ';}
  			 			//output scripture text
  			 			$out.=$passage->text.' ';
  			 			$oldChapter=$passage->chapter;
  					}
  					$out.='</p>';
  					$passages[$key]='<h2>'.$value.'</h2><div class="bible-text" id="passage'.$key.'">'.$out.'</div>';

  					if(curl_errno( $ch) )  {
    					//church_admin_debug( 'Request Error:' . curl_error( $ch) );
					}
  					curl_close( $ch);


  				break;
  				default:
  					$out.='<a href="https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.$version.'&interface=print" target="_blank">'.esc_html( $passage).'</a>';

  				break;
  			}

		}//end of readings grabbed
		return(implode('<br>',$passages) );
	}
}



function church_admin_send_sermon( $id)
{
    global $wpdb;
    //church_admin_debug('Church Admin Send Sermon begin');
    $upload_dir = wp_upload_dir();
            $path=$upload_dir['basedir'].'/sermons/';
            $url=$upload_dir['baseurl'].'/sermons/';


    $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a, '.$wpdb->prefix.'church_admin_sermon_series b WHERE a.series_id=b.series_id AND a.file_id="'.(int)$id.'"';
    $data=$wpdb->get_row( $sql);

  
    if(!empty( $data) )
    {
      $subject=__('Latest sermon available','church-admin');
      //translators: %1$s is a speaker name, %2$s is title, %3$s is series name
      $message='<p>'.esc_html(sprintf(__('The latest sermon "%1$s" by %2$s in the %3$s series is online','church-admin' ) , $data->speaker, $data->file_title,  $data->series_name) ).'<p>';
      if(!empty( $data->video_url) )$message.='<p><a href="'.esc_url( $data->video_url).'">'.esc_html( __('Watch now','church-admin' ) ).'</p>';
      if(!empty( $data->file_name) && file_exists( $path.$data->file_name) )
      {
          $message.='<a href="'.esc_url( $url.$data->file_name).'" title="'.esc_html( $data->file_title).'">'.esc_html( $data->file_title).'</a>';
      }
      elseif(!empty( $data->external_file) )
      {
        $message.='<p><a href="'.esc_url( $data->external_file).'" title="'.esc_html( $data->file_title).'">'.esc_html( $data->file_title).'</a></p>';
      }
      if(!empty( $data->bible_texts) )
      {
        $pass=array();
        $version=get_option('church_admin_bible_version');
        $passages=explode(",",$data->bible_texts);
        if(!empty( $passages)&&is_array( $passages) )
        {
          foreach( $passages AS $passage)$pass[]='<a href="https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.$version.'&interface=print" target="_blank">'.esc_html( $passage).'</a>';

        $message.='<p>'.esc_html( __('Scriptures','church-admin' ) ).':&nbsp;</td><td>'.implode(", ",$pass).'</p>';
        }
      }
      if(!empty( $data->transcript) )  {$message.=$data->transcript;}else{$message.='<p>'.esc_html( __('No Notes saved for this sermon','church-admin' ) ).'</p>';}
      
    }

}



function church_admin_edit_sermon( $file_id)
{
    if(!church_admin_level_check('Sermons') )wp_die(__('You don\'t have permission to do that','church-admin') );
    church_admin_debug("********************************\r\n church_admin_edit_sermon\r\n".date('Y-m-d H:i:s') );

    global $wpdb;
    $wpdb->show_errors;
    $data=NULL;
   
    if(!empty( $file_id) ){
        $data=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_sermon_files a LEFT JOIN '.$wpdb->prefix.'church_admin_sermon_series b ON a.series_id=b.series_id WHERE a.file_id="'.(int)$file_id.'" ');
    }

	$settings=get_option('ca_podcast_settings');
    
    $upload_dir = wp_upload_dir();
    $path=$upload_dir['basedir'].'/sermons/';
    $url=$upload_dir['baseurl'].'/sermons/';
    church_admin_debug('Upload Directory details');
    church_admin_debug($upload_dir);
    church_admin_debug('$path ='.$path);
    church_admin_debug('$url ='.$url);
    echo'<h2>'.esc_html( __('Sermon upload/Add media','church-admin' ) ).'</h2>';
   
    $errors=array();
    church_admin_debug("POSTED DATA");
    church_admin_debug( $_POST);
    if(!empty( $_POST['save_file'] ) )
    {
        
        //church_admin_debug("Includeed Save_file");
        
        
        $form=$sqlsafe=array();
        foreach( $_POST AS $key=>$value)$form[$key]=sanitize_text_field(stripslashes( $value) );
        foreach( $form AS $key=>$value)$sqlsafe[$key]=esc_sql( $value);
        /********************************
        *   Check for sermon title
        *********************************/
        if ( empty( $form['file_title'] ) )$errors['file_title']=__('A title for the sermon is required','church-admin');
        /********************************
        *   Check for mp3/video content
        *********************************/
        if ( empty( $data->file_name) && empty( $_FILES['file']['name'] )&&empty( $form['audio_url'] )&&empty( $form['video_url'] ) && empty( $form['embed_code']) )
        {
            $errors['content']=__('You need to upload an audio file or enter an audio or video url','church-admin');
        }
        /****************************************
         * Handle if Google share url in audio_url
         ***************************************/
        if(!empty( $form['audio_url'] ) )
        {
            $sqlsafe['audio_url']=$form['audio_url'];
            $audioURL=$form['audio_url'];
            church_admin_debug('URL: '.$sqlsafe['audio_url']);
           
            $mimeType=church_admin_getRemoteMimeType( $sqlsafe['audio_url'] );
            
            $dot_and_ext = substr($sqlsafe['audio_url'] ,-4);
            church_admin_debug($dot_and_ext);

            if( $mimeType!='audio/mpeg' && $dot_and_ext!='.mp3' ) {$errors['audio_url']=__('External file is not an mp3','church-admin');}
            
            $length=!empty($form['external_duration'])?$form['external_duration']: NULL;

            $file_name=NULL;
         
            
        }
        /********************
        *   Handle Upload
        *********************/
        if(!empty( $data->file_name) )$file_name=$data->file_name;
       
        if(!empty( $_FILES['file']['name'] ) )
        {//mp3s
            $arr_file_type = wp_check_filetype(basename( $_FILES['file']['name'] ) );
            $uploaded_file_type = $arr_file_type['type'];
            // Set an array containing a list of acceptable formats
            $allowed_file_types = array('audio/mp4', 'audio/mpeg','audio/mpeg3','audio/x-mpeg-3');
            // If the uploaded file is the right format
            if(in_array( $uploaded_file_type, $allowed_file_types) )
            {//valid file
                    $tmp_name = $_FILES["file"]["tmp_name"];
                    $name = $_FILES["file"]["name"];
                    $x=1;
                    $file_name=sanitize_file_name($_FILES["file"]["name"] );
                    $file_name=wp_unique_filename( $path,$_FILES["file"]["name"] );
                    church_admin_debug('File name after wp_unique_filename is '.$file_name);              
                    if(!move_uploaded_file( $tmp_name, $path.$file_name) )
                    {
                        church_admin_debug("Upload error");
                        echo'<p>'.esc_html( __('File Upload issue','church-admin' ) ).'</p>';
                    }else{
                        //translators: %1$s is a URL
                        echo'<p>'.esc_html(sprintf(__('File saved to %1$s','church-admin'),$url.$file_name)).'</p>';
                        church_admin_debug('Files saved: '.$path.$file_name);
                    }
            }else{
                church_admin_debug('Invalid file type uploaded - has to be audio mp3 or m4a');
                $errors['file']=__('Invalid file type uploaded - has to be audio mp3 or m4a');
            }
        }
       
        if(!empty( $file_name)&&file_exists( $path.$file_name) )
		{
			church_admin_debug('Getting meta data');
            //from 3.6.20 use WordPress native function
            $file_name=sanitize_file_name( $file_name);
            $audiometadata=wp_read_audio_metadata( $path.$file_name );
            $length=!empty( $audiometadata['length_formatted'] )?$audiometadata['length_formatted']:null;
		}
        if(empty($file_name))$ile_name=null;
        /******************************
        *   Abort if there are errors
        ******************************/
        if(!empty( $errors) )  {
            church_admin_debug("There were errors \r\n".print_r( $errors,TRUE) );
            echo wp_kses_post('<p>'.implode('<br>',$errors).'</p>');
            church_admin_sermon_form( $data,$errors);
        }
        else
        {//save sermon
            //church_admin_debug("OK to save sermon");
           
            /********************************
            *   Sanitize input data
            *********************************/
            if(!empty( $sqlsafe['speaker'] ) )  {$speaker=$sqlsafe['speaker'];}else{$speaker='';}
            $passages=esc_sql(church_admin_podcast_readings( $form['passages'] ) );
            /********************************
            *   Transcript
            *********************************/
            if ( empty( $_POST['transcript'] ) )$_POST['transcript']='';
            $allowed_html = ['a'=>['href'=>[],'title'=>[],],'br'=>[],'em'=>[],'strong' =>[],'ul' =>[],'ol' =>[],'li' =>[],'h1' =>[],'h2' =>[],'h3' =>[],'h4' =>[],'img' =>['src'=>[],'width'=>[],'height'=>[]]];
            $transcript=esc_sql(wp_kses( stripslashes($_POST['transcript']) , $allowed_html ));
            if(!empty( $_POST['private'] ) )  {$private="1";}else{$private="0";}
            /***********************************
            *   Fix publication date and time
            ************************************/
            $pub_date = !empty( $_POST['pub_date'] ) ? sanitize_text_field(stripslashes($_POST['pub_date'])):wp_date('Y-m-d');
            if(!church_admin_datecheck($pub_date)){$pub_date = wp_date('Y-m-d');}
            $sqlsafe['pub_date'] = esc_sql($pub_date);
            if(!empty( $sqlsafe['pub_time'] ) )
            {
                $sqlsafe['pub_date'].=' '.$sqlsafe['pub_time'].':00';
            }
            else
            {
                $sqlsafe['pub_date'].=' 12:00:00';    
            }
            /********************
            *   Sort series
            *********************/
            if(!empty( $sqlsafe['sermon_series'] ) )
            {
                //check if already exists
                $check=$wpdb->get_var('SELECT series_id FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_name="'.$sqlsafe['sermon_series'].'"');
                if(!$check)
                {
                    $series_slug = sanitize_title(church_admin_sanitize($_POST['sermon_series']));
                    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name,series_slug)VALUES("'.$sqlsafe['sermon_series'].'","'.esc_sql($series_slug).'")');
                    $sqlsafe['series_id']=$wpdb->insert_id;
                }
                else
                {
                    $sqlsafe['series_id']=$check;
                }
            }
            /*************************
             *  SAnitize embed code
             ************************/
            $embed_code='';
            if(!empty($_POST['embed_code']))
            {
                // WP's default allowed tags
                global $allowedtags;

                // allow iframe only in this instance
                $iframe = array( 'iframe' => array(
                                    'src' => array (),
                                    'allow' => array(),
                                    'width' => array (),
                                    'height' => array (),
                                    'frameborder' => array(),
                                    'allowFullScreen' => array() // add any other attributes you wish to allow
                                    ) );

                $allowed_html = array_merge( $allowedtags, $iframe );

                // Sanitize user input.
                $embed_code = wp_kses(stripslashes($_POST['embed_code']), $allowed_html );
            }
            /********************
            *   Check if already saved
            *********************/           
            if(empty($length)){$length=null;}
            if(empty($file_name)){$file_name=null;}

            //church_admin_debug("SQLSAFE data\r\n".print_r( $sqlsafe,TRUE) );
            if ( empty( $file_id) )$file_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE external_file="'.$sqlsafe['audio_url'].'" AND length="'.$length.'" AND private="'.$private.'" AND file_name="'.$file_name.'" AND file_title="'.$sqlsafe['file_title'].'" AND file_description="'.$sqlsafe['file_description'].'" AND service_id="'.$sqlsafe['service_id'].'" AND series_id="'.$sqlsafe['series_id'].'" AND speaker="'.$speaker.'"');
            //church_admin_debug( $wpdb->last_query);
            if(!empty( $file_id) )
            {//update
                
                $sql='UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET embed_code="'.esc_sql( $embed_code ).'",external_file="'.$sqlsafe['audio_url'].'", video_url="'.$sqlsafe['video_url'].'",transcript="'.$transcript.'",file_subtitle="'.$sqlsafe['file_subtitle'].'",pub_date="'.$sqlsafe['pub_date'].'",length="'.$length.'", private="'.$private.'",last_modified="'.date("Y-m-d H:i:s" ).'",file_name="'.esc_sql($file_name).'" , file_title="'.$sqlsafe['file_title'].'" , file_description="'.$sqlsafe['file_description'].'" , service_id="'.$sqlsafe['service_id'].'",series_id="'.$sqlsafe['series_id'].'" , speaker="'.$speaker.'", bible_passages="'.$passages.'",bible_texts="'.$sqlsafe['passages'].'",file_slug="'.esc_sql(sanitize_title( $form['file_title'] ) ).'" WHERE file_id="'.esc_sql( $file_id).'"';

                $wpdb->query( $sql);
                //church_admin_debug("DB Update \r\n".$wpdb->last_query);
            }//end update
            else
            {//insert
                $sql='INSERT INTO '.$wpdb->prefix.'church_admin_sermon_files (file_name,file_title,file_subtitle,file_description,private,length,service_id,series_id,speaker,pub_date,last_modified,transcript,video_url,external_file,bible_passages,bible_texts,file_slug,embed_code)VALUES("'.esc_sql($file_name).'","'.$sqlsafe['file_title'].'","'.$sqlsafe['file_subtitle'].'","'.$sqlsafe['file_description'].'" ,"'.$private.'","'.$length.'","'.$sqlsafe['service_id'].'","'.$sqlsafe['series_id'].'","'.$speaker.'" ,"'.$sqlsafe['pub_date'].'","'.date("Y-m-d H:i:s" ).'","'.$transcript.'","'.$sqlsafe['video_url'].'","'.$sqlsafe['audio_url'].'","'.$passages.'","'.$sqlsafe['passages'].'","'.esc_sql(sanitize_title( $form['file_title'] ) ).'","'.esc_sql( $embed_code ).'")';
                $wpdb->query( $sql);
                //church_admin_debug("DB Insert \r\n".$wpdb->last_query);
                $file_id=$wpdb->insert_id;
            }//end insert
            if(!empty( $_POST['blog'] ) )
            {
                //church_admin_debug('Going to post sermon blog');
                $title=$form['file_title'];
                $content='[church_admin type="single-sermon" file_id="'.(int)$file_id.'"]';
                $cat_id=wp_create_category( __('Sermon Mp3s','church-admin') );
                $postID=$wpdb->get_var('SELECT postID FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.esc_sql( $file_id).'"');

                $args=array('post_title'=>$title,'post_content'=>$content,'post_type'=>'post','post_status'=>'publish');
                if ( empty( $postID) )$args['ID']=$postID;
                $postID=wp_insert_post( $args);
                $message ='<p><a href="'.esc_url( get_permalink( $postID) ).'">Sermon posted </a></p>';

                wp_set_post_categories( $postID,array( $cat_id) );
                if(!empty( $settings['thumbnail_id'] ) )  {set_post_thumbnail( $postID, $settings['thumbnail_id'] );echo'<p>Thumbnail set</p>';}

                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET postID="'.$postID.'"');
                //church_admin_debug( $wpdb->last_query);
            }
            church_admin_debug('UPDATE xml feed');
            ca_podcast_xml();//update podcast feed
            church_admin_debug('XML feed update finished');
            church_admin_debug('Update last sermon series preached');
            church_admin_update_last_sermon_series();
            if ( empty( $message) )$message="";
            echo'<div class="notice notice-success inline"><p>'.esc_html( __('File','church-admin' ) ).' '.esc_html( $file_name).' '.esc_html( __('Saved','church-admin' ) ).'</p>'.$message.'</div>';
            ca_podcast_list_files();
        }
    }
    else
    {
        if ( empty( $data) )$data=NULL;
        church_admin_sermon_form( $data,NULL);
    }
}
    
function church_admin_sermon_form( $current_data,$errors)    
{
    global $wpdb;
    if ( empty( $current_data) &&!empty( $_POST) )$current_data=$_POST;
    if(!empty( $errors) )
    {
        echo'<h3 style="color:red">'.esc_html( __('There were some errors','church-admin' ) ).'</h2><p style="color:red">';
        foreach( $errors AS $item=>$error)echo $error.'<br>';
        echo'</p>';
    }
    
    //Show max file upload size
    $max_upload = (int)(ini_get('upload_max_filesize') );
    $max_post = (int)(ini_get('post_max_size') );
    $memory_limit = (int)(ini_get('memory_limit') );
    $upload_mb = min( $max_upload, $max_post, $memory_limit);
    //translators: %1$s is a number
    echo'<p>'.esc_html(sprintf(__('You can upload a file up to %1$sMB','church-admin' ) ,$upload_mb)).'</p>';
    echo'<form action="" method="POST"  enctype="multipart/form-data" id="churchAdminForm">';
    echo'<table class="form-table"><tbody>';
    //Sermon Title
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Sermon Title','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" required="required" name="file_title" id="file_title" ';
    if(!empty( $errors['file_title'] ) ) echo 'style="border:1px red solid"';
    if(!empty( $current_data->file_title) ) echo 'value="'.esc_html( $current_data->file_title).'"';
    echo'/></div>';
    //Sub Title
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Sub Title (a few words)','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="file_subtitle" id="file_subtitle" ';
    if(!empty( $current_data->file_subtitle) ) echo 'value="'.esc_html( $current_data->file_subtitle).'"';
    echo'/></div>';
    //Description
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('File Description','church-admin' ) ).'</label>';
    echo '<textarea class="church-admin-form-control" name="file_description">';
    if(!empty( $current_data->file_description) ) echo esc_textarea( $current_data->file_description );
    echo'</textarea></div>';
    //Bible passages
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Scripture passage','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="passages" class="large-text" placeholder="'.esc_html( __('Bible passages','church-admin' ) ).'" ';
    if(!empty( $current_data->bible_texts) ) echo 'value="'.esc_html( $current_data->bible_texts).'" ';
    echo '></div>';
   
    //sermon series
    $series_id = !empty($current_data->series_id) ? (int)$current_data->series_id : null;
    $series_res=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY series_id DESC');
    if( $series_res)
    {
    
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Sermon Series','church-admin' ) ).'</label><select class="church-admin-form-control" name="series_id"><option value="">'.esc_html( __('Choose a sermon series...','church-admin' ) ).'</option>';
   
        foreach( $series_res AS $series_row)
        {
            
             echo '<option value="'.(int)$series_row->series_id.'" '.selected($series_id,$series_row->series_id,FALSE).'>'.esc_html( $series_row->series_name).'</option>';
            
        }
        echo '</select></div>';
    }
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Create a new sermon series','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="sermon_series" /></div>';
    //service
    $service_id = !empty($current_data->service_id) ? (int)$current_data->service_id : null;
    $service_res=$wpdb->get_results('SELECT CONCAT_WS(" ",service_name,service_time) AS service_name,service_id FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_id DESC');
    if( $service_res)
    {
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Service','church-admin' ) ).'</label><select class="church-admin-form-control" name="service_id">';
        
        foreach( $service_res AS $service_row)
        {
                echo '<option value="'.(int) $service_row->service_id.'" '.selected($service_row->service_id,$service_id,false).'>'.esc_html( $service_row->service_name).'</option>';

        }
       echo '</select></div>';
    }
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Speaker','church-admin' ) ).'</label>';
    $s=array();
    $speaker=NULL;
    if(!empty( $current_data->speaker) )  {$speaker=$current_data->speaker;}
    echo church_admin_autocomplete('speaker','friends','to',$speaker);
    echo'</div>';
    $pub_date=date('Y-m-d');
    $current_time=date('H:i:s');
    
    if(!empty( $current_data->pub_date) )
    {
        //make sure only date component passed to date picker!
        $current_time=mysql2date('H:i:s',$current_data->pub_date);
        $pub_date=date('Y-m-d',strtotime( $current_data->pub_date) );
    }
    //javascript to bring up date picker
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Publication Date','church-admin' ) ).'</label>'.church_admin_date_picker( $pub_date,'pub_date',FALSE,date('Y-m-d',strtotime("-15 years") ),NULL,'pub_date','pub_date',FALSE).'</div>';
    echo '<div class="church-admin-form-group"><label>'.esc_html( __('Publication Time','church-admin' ) ).'</label> <input class="church-admin-form-control" type="time" step=1 name="pub_time"';
    echo ' value="'.mysql2date('H:i:s',$current_time).'" ';
    echo'/></div>';
    //file name

    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Audio Mp3/M4a File','church-admin' ) ).'</label>';
    if(!empty( $current_data->file_name) )  {
        echo esc_html( sprintf( __( 'Keep file (%1$s) or change.', 'church-admin' ),$current_data->file_name) ).'<br/>';
        
        echo'<input type="hidden" name="file_name" value="'. esc_attr( $current_data->file_name ) .'" />';
    }
    echo'<div id="file-error"></div>';
    echo'<input type="file" name="file" id="file" /></div>';
    
    echo'<script>
    jQuery(document).ready(function($){
        $("#file").on("change", function() { 
            console.log("file change");
            if (this.files[0].size > '.church_admin_return_bytes(ini_get('post_max_size') ).') { 
                $("#file-error").text("'.esc_attr('File size too large, form submission disabled','church-admin').'");
                $("#submit").attr("disabled",true);
            } else { 
                $("#file-error").text("'.esc_attr('File size okay','church-admin').'"); 
                $("#submit").attr("disabled",false);
            } 
        }); 
    });
  </script>';

    //external file
    echo'<p style="color:red">'.__('WARNING - Google drive hosted files will no longer play since 10th Jan 2024. Google have changed their settings','church-admin').'</p>';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('External Audio mp3 URL','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="audio_url" id="audio_url"';
    if(!empty( $errors['audio_url'] ) ) echo 'style="border:1px red solid"';
    if(!empty( $current_data->external_file) )echo' value="'.esc_url( $current_data->external_file).'" ';
    echo'/>';
    echo'<input Type="hidden" name="external_duration" id="external_duration" ';
    if( !empty( $current_data->length ) )echo' value="'.esc_attr($current_data->length).'"';
    echo '/></div>';
    //javascript to detect length of external audio
    echo'<script>
    jQuery(document).ready(function($){
        $("#audio_url").change(function(){

            var url=$(this).val();
            console.log(url);
            var au = document.createElement("audio");

            // Define the URL of the MP3 audio file
            au.src = url;

            // Once the metadata has been loaded, display the duration in the console
            au.addEventListener("loadedmetadata", function(){
                    
                    var duration = parseInt(au.duration);
                    //convert to ISO format
                    var date = new Date(null);
                    date.setSeconds(duration); 
                    var hhmmssFormat = date.toISOString().substr(11, 8);
                    console.log("External duration is "+ hhmmssFormat);
                    $("#external_duration").val(hhmmssFormat);
                },false);
            })

    });
    
    </script>';

    //embed code
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Embed code','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="embed_code" id="embed_code"';
    if(!empty( $errors['embed_code'] ) ) echo 'style="border:1px red solid"';
    if(!empty( $current_data->embed_code) )echo' value="'.esc_url( $current_data->embed_code).'" ';
    echo'/></div>';

    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Video "Share" URL (YouTube, Vimeo or Facebook)','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="video_url" id="video_url"';
    if(!empty( $errors['video_url'] ) ) echo 'style="border:1px red solid"';
    if(!empty( $current_data->video_url) )echo' value="'.esc_url( $current_data->video_url).'" ';
    echo'/></div>';
   
    echo'<div class="church-admin-form-group"><label>'.esc_html( __('Notes/Transcript','church-admin' ) ).'</label>';
    if ( empty( $current_data->transcript) )  {$transcript='';}else{$transcript=$current_data->transcript;}
    wp_editor( $transcript, 'transcript' );
    echo'</div>';
    if ( empty( $current_data->postID) )  {
        echo'<div  class="church-admin-checkbox"><input type="checkbox"  name="blog" value="1" /><label>'.esc_html( __('Blog the sermon','church-admin' ) ).'</label></div>';}
        else 
        {
            echo'<div class="church-admin-form-group"><label>'.esc_html( __('Sermon already posted','church-admin' ) ).'</label>'.esc_html(get_the_title( $current_data->postID) ).'</div>';
        }
        
         //Logged in only
    echo'<div class="church-admin-checkbox"><input type="checkbox" name="private" ';
    if(!empty( $current_data->private) )echo ' checked="checked" ';
    echo'value="yes" /> <label>'.esc_html( __('Logged in only','church-admin' ) ).'?</label></div>';
    echo '<p><input type="hidden" name="save_file" value="save_file" />'.wp_nonce_field('upload-mp3').'<input type="submit"  class="button-primary" id="submit" class="button-primary" value="Save File" /></p></form>';
}




/**********************************************
*
* migrate advanced sermons
*
***********************************************/
function church_admin_migrate_advanced_sermons()
{
    global $wpdb;
    echo'<h2>'.esc_html( __('Migrate from "Advanced Sermons" plugin','church-admin' ) ).'</h2>';
    $sermons=$wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type="sermons" AND post_status="publish" ORDER BY post_date DESC');
    if (empty( $sermons) ){
        echo'<p>'.esc_html( __('No advanced sermons plugins sermons to import','church-admin') ).'</p>';
        return;
    }
    $total=$wpdb->num_rows;
    //translators: %1$s is a number
    echo'<div class="notice notice-info inline"><h2>'.esc_html(sprintf(__('%1$s possible sermon record(s) to import found','church-admin' ) ,$total)).'</h2></div>';
    $count=0;
    foreach( $sermons AS $sermon)
    {
        $title  = $sermon->post_title;
        $file_slug=sanitize_title( $sermon->post_title);
        $file_description=$sermon->post_content;
        $date   = $sermon->post_date;
        $series=wp_get_post_terms( $sermon->ID, 'sermon_series', array("fields" => "all") );
        
        if ( empty( $series->errors) )
        {
            $series_name=$series[0]->name;
            $series_slug=$series[0]->slug;
            
        }
        $speakerData =  wp_get_post_terms( $sermon->ID, 'sermon_speaker', array("fields" => "all") ); 
        if ( empty( $speakerData->errors) )
        {
            $speaker=$speakerData[0]->name;
        }
        $vimeo = get_post_meta( $sermon->ID,'asp_sermon_vimeo',TRUE);
        $youtube =  get_post_meta( $sermon->ID,'asp_sermon_youtube',TRUE);
        $fileURL = get_post_meta( $sermon->ID,'asp_sermon_mp4',TRUE);
        $passage =  get_post_meta( $sermon->ID,'asp_sermon_bible_passage',TRUE);
        if(!empty( $title)&& !empty( $speaker) && !empty( $fileURL) )
        {
            //check and update sermon series
            $series_id=$wpdb->get_var('SELECT series_id FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_name="'.esc_sql( $series_name).'"');
            if ( empty( $series_id) )
            {
                $series_slug = sanitize_title($series_name);
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name,series_slug)VALUES("'.esc_sql( $series_name).'","'.esc_sql( $series_slug).'")');
                
                $series_id=$wpdb->insert_id;
            }

            
            //check if there is a youtube/vimeo and update $video
            $sermonVideo='';
            if(!empty( $vimeo) )$sermonVideo=$vimeo;
            if(!empty( $youtube) )$sermonVideo=$youtube;
            //check if already in sermons table
            $sermon_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_title="'.esc_sql( $title).'" AND file_description="'.esc_sql( $file_description).'" AND file_slug="'.esc_sql( $file_slug).'" AND bible_texts="'.esc_sql( $passage).'" AND series_id="'.(int)$series_id.'" AND pub_date="'.esc_sql( $date).'" AND speaker="'.esc_sql( $speaker).'" AND video_url="'.esc_sql( $sermonVideo).'" AND external_file="'.esc_sql( $fileURL).'"');
            if ( empty( $sermon_id) )
            {
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_files (file_title,file_description,file_slug,bible_texts,series_id,pub_date,last_modified,speaker,video_url,external_file)VALUES("'.esc_sql( $title).'","'.esc_sql( $file_description).'","'.esc_sql( $file_slug).'","'.esc_sql( $passage).'","'.(int)$series_id.'","'.esc_sql( $date).'","'.esc_sql( $date).'","'.esc_sql( $speaker).'","'.esc_sql( $sermonVideo).'","'.esc_sql( $fileURL).'")');
            $count++;
                $file_id=$wpdb->insert_id;
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_series SET last_sermon="'.esc_sql( $date).'" WHERE series_id="'.(int)$series_id.'"');
            }
            //show message
            echo '<p> Sermon "'.esc_html( $title).'" preached by '.esc_html( $speaker).' on  '.mysql2date(get_option('date_format'),$date).' imported</p>';
        }else
        {
            //translators: %1$s is a number
            echo'<p>'.esc_html(sprintf(__('Not enough data found to import for this record ID: %1$s','church-admin' ) ,$sermon->ID)).'</p>';
            
        }
    }
    //show completion message
    //translators: %1$s  and %2$s numbers
    echo'<div class="notice notice-success"><h2>'.esc_html(sprintf(__('%1$s sermons out of %2$s successfully imported','church-admin' ) ,(int)$count,(int)$total)).'</h2></div>';
    
}
/**********************************************
*
* migrate sermon manager
*
***********************************************/
function church_admin_migrate_sermon_manager()
{
	global $wpdb;
    echo'<h3>'.esc_html(__('Sermon Manager plugin migration','church-admin')).'</h3>';
	if(!in_array('sermon-manager-for-wordpress/sermons.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
        echo '<p>'.__('Sermon Manager plugin needs to be active for migration to work successfully','church-admin');
        return;
    }

	
	
		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/sermons/';
		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type="wpfc_sermon" ');
       
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name,series_slug) VALUES("'.esc_sql( __('Non series sermon','church-admin' ) ).'","'.esc_sql( sanitize_title(__('Non series sermon','church-admin' ) ) ).'")');
		$defaultSeriesID=$wpdb->insert_id;
        if(!empty( $results) )
		{
            $count = $wpdb->num_rows;
            //translators: %1$s is a number
            echo'<h3>'.esc_html(sprintf(__('Migrating %1$s sermons','church-admin'),(int)$count)).'</h3>';


           
			foreach( $results AS $row)
			{
                
                $preachers = array();
                $series_id = $defaultSeriesID;
                $slug = $title = $description = $preacher_name = $passage = $video_url = $embed_code = $passages = $audio_file = $audio_length = $pub_date = null;


                //handle data in taxonomy terms
                    $custom_taxonomies = array(
                        'wpfc_preacher',
                        'wpfc_sermon_series',
                        'wpfc_sermon_topics',
                        'wpfc_bible_book',
                        'wpfc_service_type',
                    );
                    $terms = wp_get_object_terms( $row->ID, $custom_taxonomies );
                    
                    foreach($terms AS $term){
                        switch($term->taxonomy)
                        {
                            case 'wpfc_preacher':
                                $preachers[]=church_admin_sanitize($term->name);
                            break;
                            case 'wpfc_sermon_series':
                                $series_name = church_admin_sanitize($term->name);
                                $series_id = $wpdb->get_var('SELECT series_id FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_name="'.esc_sql($series_name).'"');
                                if(empty($series_id)){
                                    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name) VALUES("'.esc_sql($series_name).'")');
                                    $series_id = $wpdb->insert_id;
                                }
                            break;
                        }
                    }
                    //handle preachers
                    if(!empty($preachers)){
                        $preacher_name = implode(",",$preachers);
                    }
                    //handle post_meta data
                    $post_meta=get_post_meta( $row->ID);
                   
                    $audio_file = !empty($post_meta['sermon_audio'])?church_admin_sanitize($post_meta['sermon_audio'][0]):null;
                    $audio_length = !empty($post_meta['_wpfc_sermon_duration'])?church_admin_sanitize($post_meta['_wpfc_sermon_duration'][0]):null;
                    $video_url = !empty($post_meta['sermon_video_link'])?church_admin_sanitize($post_meta['sermon_video_link'][0]):null;
                    $embed_code = !empty($post_meta['sermon_video'])?church_admin_sanitize($post_meta['sermon_video'][0]):null;
                    $pub_date = !empty($post_meta['sermon_date'])?date('Y-m-d H:i:s',church_admin_sanitize($post_meta['sermon_date'][0])):null;
                    $title = $row->post_title;
                    $description = !empty($post_meta['sermon_description'])?church_admin_sanitize($post_meta['sermon_description'][0]):null;
                    $slug = $row->post_name;
                    $file_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE embed_code="'.esc_sql($embed_code).'" AND video_url="'.esc_sql($video_url).'" AND file_title="'.esc_sql( $title).'" AND external_file="'.esc_sql( $audio_file).'" AND file_slug = "'.esc_sql( $slug).'" AND file_description = "'.esc_sql($description).'"');
                    if(empty($file_id)){

                        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_files (file_title,file_description,external_file,speaker,service_id,series_id,length,pub_date,embed_code,video_url,file_slug) VALUES ("'.esc_sql( $title).'","'.esc_sql($description).'","'.esc_sql( $audio_file).'","'.esc_sql( $preacher_name).'","1","'.(int)$series_id.'","'.$audio_length.'","'.esc_sql( $pub_date).'","'.esc_sql($embed_code).'","'.esc_sql($video_url).'","'.esc_sql($slug).'")');
                        echo $wpdb->last_query;
                        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_series SET last_sermon="'.esc_sql( $pub_date).'" WHERE series_id="'.(int)$series_id.'"');
                        $display_date= !empty($pub_date)?mysql2date(get_option('date_format'),$pub_date):__('Unknown date','church-admin');
                        //translators: %1$s is a sermon title, %2$s is a date
                        echo'<p>'.esc_html(sprintf(__('Sermon "%1$s" preached on %2$s migrated','church-admin'),$title,$display_date)).'</p>';
                    }else{
                        //translators: %1$s is a sermon title
                        echo'<p>'.esc_html(sprintf(__('Sermon "%1$s"  already migrated','church-admin'),$title)).'</p>';
                    }

            }
               
		}
        echo'<p>'.__('Sermon Manager permalinks override Church Admin sermons, so afterwards please deactivate Sermon Manager and reset permalinks, by going to Dashboard>Settings>Permalinks','church-admin').'</p>';
        $plugin_file = 'sermon-manager-for-wordpress/sermons.php';
        echo'<p>'. sprintf(
            '<a class="button-primary" href="%s" >%s</a>',
            wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode( $plugin_file  ), 'deactivate-plugin_' . $plugin_file ),
             __( 'Deactivate Sermon Manager','church-admin' )
        );
        
}
/**********************************************
*
* migrate sermon browser
*
***********************************************/
function church_admin_migrate_sermon_browser()
{
    global $wpdb;
    echo'<h2>'.esc_html(__('Sermon Browser Import','church-admin')).'</h2>';
    //check for a sermon browser table
    if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'sb_sermons"')!=$wpdb->prefix.'sb_sermons')
    {
        echo'<p>'.esc_html(__('No sermon browser database tables found, so nothing to import','church-admin')).'</p>';
        return;
    }
    $upload_dir = wp_upload_dir();
    $path=$upload_dir['basedir'].'/sermons/';
    $url=$upload_dir['baseurl'].'/sermons/';
   
    
    $sql='SELECT a.*,b.name AS preacher_name,c.name AS series_name,d.name AS file_name,d.duration AS duration, d.count AS count  FROM '.$wpdb->prefix.'sb_sermons a, '.$wpdb->prefix.'sb_preachers b,'.$wpdb->prefix.'sb_series c,'.$wpdb->prefix.'sb_stuff d WHERE a.preacher_id=b.id AND a.series_id=c.id AND a.id=d.sermon_id ORDER BY a.`datetime` DESC';
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        echo'<h2>'.esc_html( __('Sermon Browser file adding to Church Admin started','church-admin' ) ).'</h2>';
        foreach( $results AS $row)
        {
            
            if(file_exists( $sermon_path.$row->file_name) )
            {
               
                //get passage
                $start=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'sb_books_sermons WHERE sermon_id="'.(int)$row->id.'" AND type="start" LIMIT 1');
                $end=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'sb_books_sermons WHERE sermon_id="'.(int)$row->id.'" AND type="end" LIMIT 1');
                if(!empty( $start)&&!empty( $end) )
                {
                    if( $start->book_name==$end->book_name&& $start->chapter=$end->chapter)$passage=$start->book_name.' '.$start->chapter.':'.$start->verse.'-'.$end->verse;
                    elseif( $start->book_name==$end->book_name&& $start->chapter=$end->chapter)$passage=$start->book_name.' '.$start->chapter.':'.$start->verse.'-'.$end->chapter.':'.$end->verse;
                    else{$passage=$start->book_name.' '.$start->chapter.':'.$start->verse.'-'.$end->book_name.' '.$end->chapter.':'.$end->verse;}
                }else{$passage='';}
                //check series id
                $new_series_id=$wpdb->get_var('SELECT series_id FROM '.$wpdb->prefix.'church_admin_sermon_series WHERE series_name="'.esc_sql( $row->series_name).'"');
                if ( empty( $new_series_id) )
                {
                    $series_slug=sanitize_title( $row->series_name);
                    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_series (series_name,series_slug,last_sermon)VALUES("'.esc_sql( $row->series_name).'","'.esc_sql( $series_slug).'","'.esc_sql( $row->datetime).'")');
                    $new_series_id=$wpdb->last_insert_id;
                }
                //check not already added
                $sermon_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_name="'.esc_sql( $row->file_name).'"');
                
                if ( empty( $sermon_id) )
                {
                    $file_slug=sanitize_title( $row->title);
                    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_sermon_files (file_name,file_title,pub_date,series_id,speaker, plays,length,file_slug,bible_texts)VALUES("'.esc_sql( $row->file_name).'","'.esc_sql( $row->title).'","'.esc_sql( $row->datetime).'","'.(int)$new_series_id.'","'.esc_sql( $row->preacher_name).'","'.(int)$row->count.'","'.esc_sql( $row->duration).'","'.esc_sql( $file_slug).'","'.esc_sql( $passage).'")');
                   //translators: %1$s is a sermon title, %2$s is a name. %3$s is a date 
                    echo '<p>'.esc_html(sprintf(__('%1$s preached by %2$s on %3$s added','church-admin' ) ,$row->title,$row->preacher_name,mysql2date(get_option('date_format').' '.get_option('time_format'),$row->datetime) )).'</p>';
                } else{
                        //translators: %1$s is a sermon title, %2$s is a name. %3$s is a date 
                    echo '<p>'.esc_html(sprintf(__('%1$s preached by %2$s on %3$s already added','church-admin' ) ,$row->title,$row->preacher_name,mysql2date(get_option('date_format').' '.get_option('time_format'),$row->datetime) )).'</p>'; 
                }
            }

        }
        echo'<div class="notice"><h2>'.esc_html( __('Sermon Browser files migrated','church-admin' ) ).'</h2></div>';
    }
    else
    {
        echo'<div class="notice"><h2>'.esc_html( __('No Sermon Browser plugin entries detected','church-admin' ) ).'</h2></div>';
    }

}

/**********************************************
*
*   Podcast Settings
*
***********************************************/
function ca_podcast_xml()
{

   church_admin_debug('****** Updating XML feed ******');
    global $wpdb,$ca_podcast_settings;
    $settings=get_option('ca_podcast_settings');
	$upload_dir = wp_upload_dir();
    church_admin_debug('Upload Directory details');
    church_admin_debug($upload_dir);

	$path=$upload_dir['basedir'].'/sermons/';
	$sermonFilesDirURL=$upload_dir['baseurl'].'/sermons/';
    $results=$wpdb->get_results('SELECT *, DATE_FORMAT(pub_date,"%a, %d %b %Y %T") AS publ_date FROM '. $wpdb->prefix.'church_admin_sermon_files'.'  WHERE private="0"  ORDER BY pub_date DESC');
    church_admin_debug( $wpdb->last_query);
    if(!empty( $results) && !empty( $settings['title'] ) )
    {

        //CONSTRUCT RSS FEED HEADERS
        $output = '<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">';
        $output .= '<channel>';
        if(!empty($settings['title']))$output .= '<title>'.htmlspecialchars( $settings['title'], ENT_XML1, 'UTF-8').'</title>';
        $output .= '<link>'.htmlspecialchars(site_url(), ENT_XML1, 'UTF-8').'</link>';
        if(!empty($settings['language']))$output .= '<language>'.htmlspecialchars( $settings['language'], ENT_XML1, 'UTF-8').'</language>';
        if(!empty($settings['copyright']))$output .= '<copyright>&#x2117; &amp; &#xA9; '.date('Y').' '.htmlspecialchars( $settings['copyright'], ENT_XML1, 'UTF-8').'</copyright>';
        $output .= '<itunes:type>episodic</itunes:type>';
        if(!empty($settings['subtitle']))$output .= '<itunes:subtitle>'.htmlspecialchars( $settings['subtitle'] ).'</itunes:subtitle>';
        if(!empty($settings['author']))$output .= '<itunes:author>'.htmlspecialchars( $settings['author'], ENT_XML1, 'UTF-8').'</itunes:author>';
        if(!empty($settings['summary']))$output .= '<itunes:summary>'.htmlspecialchars( $settings['summary'], ENT_XML1, 'UTF-8').'</itunes:summary>';
        if(!empty($settings['description']))$output .= '<description>'.htmlspecialchars( $settings['description'], ENT_XML1, 'UTF-8').'</description>';
        $output .= '<itunes:owner>';
        if(!empty( $settings['owner_name'] ) )$output .= '<itunes:name>'.htmlspecialchars( $settings['owner_name'], ENT_XML1, 'UTF-8').'</itunes:name>';
        if(!empty( $settings['owner_email'] )&&is_email( $settings['owner_email'] ) )$output .= '<itunes:email>'.$settings['owner_email'].'</itunes:email>';
        $output .= '</itunes:owner>';
        if(!empty($settings['explicit']))$output .= '<itunes:explicit>'.htmlspecialchars( $settings['explicit'], ENT_XML1, 'UTF-8').'</itunes:explicit>';

        $output .='<itunes:image href="'.htmlspecialchars( $settings['image'], ENT_XML1, 'UTF-8').'" />';
        if(!empty( $settings['category'] ) )
        {
            $cat=explode("-",$settings['category'] );
            
            if(count( $cat)==2)  {$output .='<itunes:category text="'.trim(htmlspecialchars( $cat[0], ENT_XML1, 'UTF-8') ).'"><itunes:category text="'.htmlspecialchars( $cat[1], ENT_XML1, 'UTF-8').'" /></itunes:category>';}
            elseif(count( $cat)==1)  {$output .='<itunes:category text="'.trim(htmlspecialchars( $cat[0], ENT_XML1, 'UTF-8') ).'" />';}

        }
        $output=str_replace('&amp;amp;','&amp;',$output);//fix double escape
        

        $sermonPageURL=rtrim(church_admin_find_sermon_page(),"/");
        
        //BODY OF RSS FEED
        foreach( $results AS $row)
        {
            church_admin_debug('Processing row ***');
            church_admin_debug($row); 
            $sermonDetailURL=esc_url( $sermonPageURL.'?sermon='.$row->file_slug);
            if(!empty( $row->file_name)||!empty( $row->external_file) )
            {
                //get speakers

                $names=!empty( $row->speaker)?church_admin_get_people( $row->speaker):__('Preacher','church-admin');
                $subtitle=!empty( $row->file_subtitle)?$row->file_subtitle:$row->file_subtitle;
                //end get speakers
                $service=$wpdb->get_var('SELECT CONCAT_WS(" ",service_name,service_time) FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.esc_sql( $row->service_id).'"');
                $output .= '<item>';
                $output .= '<title>'.htmlspecialchars( $row->file_title, ENT_XML1, 'UTF-8').'</title>';
                $output .= '<itunes:title>'.htmlspecialchars( $row->file_title, ENT_XML1, 'UTF-8').'</itunes:title>';
                $output .= '<description>'.htmlspecialchars( $row->file_description, ENT_XML1, 'UTF-8').'</description>';
                $output .= '<itunes:author>'.htmlspecialchars( $names, ENT_XML1, 'UTF-8').'</itunes:author>';
                $output .= '<itunes:subtitle>'.htmlspecialchars( $subtitle, ENT_XML1, 'UTF-8').'</itunes:subtitle>';
                $output .= '<itunes:summary>'.htmlspecialchars( $row->file_description, ENT_XML1, 'UTF-8').'</itunes:summary>';
                ;
                if(!empty( $row->file_name) && file_exists( $path.$row->file_name) )
                {
                    church_admin_debug('Adding '.$sermonFilesDirURL.$row->file_name);
                    $output .= '<link>'.htmlspecialchars( $sermonFilesDirURL.$row->file_name, ENT_XML1, 'UTF-8').'</link>';
                    $output .= '<enclosure url="'.htmlspecialchars( $sermonFilesDirURL.$row->file_name, ENT_XML1, 'UTF-8').'" length="'.filesize( $path.$row->file_name).'" type="audio/mpeg" />';
                    $output .= '<guid>'.htmlspecialchars( $sermonFilesDirURL.$row->file_name, ENT_XML1, 'UTF-8').'</guid>';
                }
                else
                {
                    church_admin_debug('Adding '.$row->external_file);
                    $output .= '<link>'.htmlspecialchars( $row->external_file, ENT_XML1, 'UTF-8').'</link>';
                    $output .= '<enclosure url="'.htmlspecialchars( $row->external_file, ENT_XML1, 'UTF-8').'" length="" type="audio/mpeg" />';
                    $output .= '<guid>'.htmlspecialchars( $row->external_file, ENT_XML1, 'UTF-8').'</guid>';
                }

                $output .= '<pubDate>'.htmlspecialchars( $row->publ_date.' '.date('O'), ENT_XML1, 'UTF-8').'</pubDate>';
                $output .= '<itunes:duration>'.htmlspecialchars( $row->length, ENT_XML1, 'UTF-8').'</itunes:duration>';
                //$output .= '<itunes:keywords></itunes:keywords>';
                $output .= '</item>'."\r\n";
            }
        }
        //CLOSE RSS FEED
        $output .= '</channel>';
        $output .= '</rss>';
        church_admin_debug($output);
        //SEND COMPLETE RSS FEED TO podcast xml file
        church_admin_debug($path.'podcast.xml');
        $fp = @fopen($path.'podcast.xml', 'w');
        if (!$fp) {
            $err = error_get_last();
            church_admin_debug($err['message']);
        }
        else
        {
            church_admin_debug($fp);
            fwrite( $fp, $output);
            fclose( $fp);
            return TRUE;
        }
       
        
        
    }//end results
    else
    {
        if(file_exists( $path.'podcast.xml') )unlink( $path.'podcast.xml');
    }
}

function church_admin_handle_podcast_image( $settings)
{
	if ( empty( $settings['thumbnail_id'] )&&!empty( $settings['image'] ) )
			{
				$path = parse_url( $settings['image'] );
				$image=$_SERVER['DOCUMENT_ROOT'] . $path['path'];

				$filetype = wp_check_filetype( basename( $image), null );
				$filetitle = preg_replace('/\.[^.]+$/', '', basename( $image) );
				$filename = $filetitle . '.' . $filetype['ext'];
				$upload_dir = wp_upload_dir();
				/**
				* Check if the filename already exist in the directory and rename the
				* file if necessary
				*/
				$i = 0;
				while ( file_exists( $upload_dir['path'] .'/' . $filename ) )
				{
					$filename = $filetitle . '_' . $i . '.' . $filetype['ext'];
					$i++;
				}
				$filedest = $upload_dir['path'] . '/' . $filename;

				if(file_exists( $image) )
				{
					copy( $image, $filedest);
					$attachment = array('post_mime_type' => $filetype['type'],'post_title' => $filetitle,'post_content' => '','post_status' => 'inherit');
					$attachment_id = wp_insert_attachment( $attachment, $filedest );
					$settings['thumbnail_id']=$attachment_id;
					update_option('ca_podcast_settings',$settings);
					require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attachment_id, $filedest );
					wp_update_attachment_metadata( $attachment_id,  $attach_data );
				}
			}
			return $settings;
}

function church_admin_update_last_sermon_series()
{
    global $wpdb;
    
    $sql='SELECT MAX(pub_date) AS pub_date,series_id FROM '.$wpdb->prefix.'church_admin_sermon_files GROUP BY series_id';
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        foreach( $results AS $row)$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_series SET last_sermon="'.esc_sql( $row->pub_date).'" WHERE series_id="'.(int)$row->series_id.'"');

    }
    
}

function church_admin_set_sermon_page()
{
    
    if(!empty( $_POST['page_id'] ) )
    {
        update_option('church-admin-sermon-page',(int)$_POST['page_id'] );
    }    
    
    $sermonPageID=get_option('church-admin-sermon-page');
    $args=array();
    if(!empty( $sermonPageID) ) $args['selected']= $sermonPageID;
    echo'<h2>'.esc_html( __('Set which page has your main sermons shortcode/block','church-admin' ) ).'</h2>';
    echo'<form action="" method="POST">';
    wp_dropdown_pages( $args);
    echo'<p><input type="hidden" name="save-sermon-page" value="1" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin')).'" />';

}