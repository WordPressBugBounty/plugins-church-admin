<?php

function church_admin_all_the_series_display( $sermon_page=NULL)
{
   

    global $wpdb;
    $cols=3;
    if ( empty( $sermon_page) )return __('Your sermon_page needs defining in the shortcode or block','church-admin');
    $output='<div class="church-admin-series">';
    $template='<div class="ca-img-container"><a title="{series_name}" href="{series_url}"><img src="{image_url}"></a><div class="ca-overlay"><a title="{series_name}" href="{series_url}">
    <span>{series_name}</span>
  </a></div></div>';
    
 
    $series_result=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_series ORDER BY last_sermon DESC');
    if(!empty( $series_result) )
    {
        $x=1;
        foreach( $series_result AS $series)
        {
            if( $x==1)$output.='<div class="ca-series-row">';
            
            $out=$template;
           
            $out=str_replace('{series_name}',esc_html( $series->series_name),$out);
            $out=str_replace('{series_url}',esc_url($sermon_page.'/?sermon-series='.sanitize_title( $series->series_name)),$out);
            if(!empty( $series->series_image) )
            {
                $image=wp_get_attachment_image_src( $series->series_image,'full');
                if(!empty( $image[0] ) )$out=str_replace('{image_url}',$image[0],$out);
                else $out=str_replace('{image_url}',esc_url( plugins_url( 'images/sermon.png', dirname(__FILE__) ) ) ,$out);
            }
            else 
            {
                $out=str_replace('{image_url}',esc_url( plugins_url( 'images/sermon.png', dirname(__FILE__) ) ) ,$out);
            }
            $output.=$out;
            $x++;
            if( $x>$cols)
            {
                $x=1;
                $output.='</div>';
            }
            
            
        }
        if( $x<=$cols)
        {
            $output.='<!-- Filler blocks -->';
            for ( $y=$x; $y<=$cols; $y++)
            {
                $output.='<div class="ca-img-container">&nbsp;</div>';
            }
            $output.='</div>';
        }
        $output.='</div>';
    }
    $output.='</div>';
    $output.='<script>var nonce="'.wp_create_nonce('sermon-display').'";</script>';
    return $output;
}