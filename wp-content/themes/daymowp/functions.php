<?php /*

  This file is part of a child theme called daymowp.
  Functions in this file will be loaded before the parent theme's functions.
  For more information, please read https://codex.wordpress.org/Child_Themes.

*/

// this code loads the parent's stylesheet (leave it in place unless you know what you're doing)

function theme_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array($parent_style));
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

/*  Add your own functions below this line.
    ======================================== */ 

    function _remove_script_version( $src ){
        $parts = explode( '?', $src );
        return $parts[0];
    }
    add_filter( 'script_loader_src', '_remove_script_version', 15, 1 ); 
    add_filter( 'style_loader_src', '_remove_script_version', 15, 1 );
    
    function defer_parsing_of_js ( $url ) {
        if ( FALSE === strpos( $url, '.js' ) ) return $url;
        if ( strpos( $url, 'jquery.js' ) ) return $url;
        if ( strpos( $url, 'jquery-migrate.min.js' ) ) return $url;
        return "$url' defer='defer";
    }
    add_filter( 'clean_url', 'defer_parsing_of_js', 11, 1 );
    
    /**
     * Disable the emoji's
     */
    function disable_emojis() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
        add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
    }
    add_action( 'init', 'disable_emojis' );
       
       /**
        * Filter function used to remove the tinymce emoji plugin.
        * 
        * @param array $plugins 
        * @return array Difference betwen the two arrays
        */
       function disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
        } else {
        return array();
        }
       }
       
       /**
        * Remove emoji CDN hostname from DNS prefetching hints.
        *
        * @param array $urls URLs to print for resource hints.
        * @param string $relation_type The relation type the URLs are printed for.
        * @return array Difference betwen the two arrays.
        */
       function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
        if ( 'dns-prefetch' == $relation_type ) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
       
       $urls = array_diff( $urls, array( $emoji_svg_url ) );
        }
       
       return $urls;
       }

/*Function to defer or asynchronously load scripts*/

function js_async_attr($tag){
    # Do not add defer or async attribute to these scripts
    $scripts_to_exclude = array('jquery.js', 'jquery-migrate.min.js');
    
    foreach($scripts_to_exclude as $exclude_script){
        if(true == strpos($tag, $exclude_script ) )
        return $tag;
}

# Defer or async all remaining scripts not excluded above
return str_replace( ' src', ' defer="defer" src', $tag );
}
add_filter( 'script_loader_tag', 'js_async_attr', 10 );