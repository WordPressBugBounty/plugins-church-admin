<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_latest_sermon(){


    global $wpdb;
    $sermon=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files ORDER BY pub_date DESC LIMIT 1');
    if(empty($sermon)){return;}
    $video_detail = !empty($sermon->video_url) ? church_admin_generateVideoEmbedUrl( $sermon->video_url) : null;
    if(empty($video_detail)){return;}

    $upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/sermons/';
	$url=$upload_dir['baseurl'].'/sermons/';


    $out ='<div class="aligncenter:"><div class="church-admin-recent-message" >

                <div  class="church-admin-play-button" data-sermon-id="'.(int)$sermon->file_id.'">
                    <i>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 16"><path d="M13.1 6.7L2.2.2C1.4-.3 0 .2 0 1.5v13c0 1.2 1.3 1.9 2.2 1.3l11-6.5c.9-.6.9-2 0-2.6z" fill="currentColor"></path></svg>
                    </i>
                </div>
                <div class="info">
                    <h6>'.esc_html(__('Watch the Latest Sermon','church-admin')).'</h6>
                    <h3>'.esc_html($sermon->file_title).'</h3>
                    <h4>'.esc_html($sermon->speaker).'</h4>
                </div>
            </div>';
    $out.='<div id="church-admin-recent-message" style="display:none">
                <div class="church-admin-recent-message-content"><span class="church-admin-recent-message-close">X</span>
                        <h6>'.esc_html($sermon->file_title).'</h6>
        
                       <div style="width:100%;height:auto">
                            <div style="position:relative;padding-top:56.25%">
                                    <iframe class="ca-video" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.esc_url($video_detail['embed']).'" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>
                </div>
        </div></div>';
    $out.='<script>jQuery(document).ready(function( $)  {
                $("body .church-admin-play-button").click(function()  {
                    $("#church-admin-recent-message").show();
                    $("body").addClass("church-admin-grey-background");
                    $([document.documentElement, document.body]).animate({
                            scrollTop: $("#church-admin-recent-message").offset().top
                        }, 200);
                });
                $("body .church-admin-recent-message-close").click(function()  {
                    $("#church-admin-recent-message").hide();
                    $("body").removeClass("church-admin-grey-background");
                });
                
            });
            
            </script>';

    return $out;


}