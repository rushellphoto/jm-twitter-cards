<?php
namespace jm_twitter_cards;

if (!defined('JM_TC_VERSION')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class Utilities{

    /**
     * @param $at
     * @return mixed
     */
    public static function remove_at($at){
        $noat = str_replace('@', '', $at);
        return $noat;
    }

    /**
     * @param $key
     * @param $post_id
     * @return bool|string
     */
    public static function strip_meta($key, $post_id){

        $meta =  get_post_meta( $post_id, $key, true );
        if('' !== $meta && is_string($meta)) {
            return htmlspecialchars(stripcslashes($meta));
        }

        return false;
    }

    /**
     * Remove line breaks
     * @param $lb
     * @return string
     */
    public static function remove_lb($lb){
        $output = str_replace(array(
            "\r\n",
            "\r"
        ), "\n", $lb);
        $lines = explode("\n", $output);
        $nolb = array();
        foreach ($lines as $key => $line) {
            if (!empty($line)) $nolb[] = trim($line);
        }

        return implode($nolb);
    }

    /**
     * Get excerpt by post ID and filter shortcodes, tags and special chars
     * @param $post_id
     * @return string|void
     */
    public static function get_excerpt_by_id($post_id){
        $the_post = get_post($post_id);
        $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt

        //kill shortcode
        $shortcode_pattern = get_shortcode_regex();
        $the_excerpt = preg_replace('/' . $shortcode_pattern . '/', '', $the_excerpt);

        // kill tags
        $the_excerpt = strip_tags($the_excerpt);

        return esc_attr(substr($the_excerpt, 0, 200)); // to prevent meta from being broken by e.g ""
    }

    /**
     * Get tutorials
     * @param $data
     * @param string $provider
     * @return string
     */
    public static function display_footage($data, $provider = 'YouTube'){

        $output = '';
        $args = array( 'width' => 400, 'height' => 300);

        if( 'YouTube' === $provider ) {
            if (is_array($data)) {
                foreach ($data as $label => $id) {
                    $url = add_query_arg( array('v' => $id  ) , 'https://www.youtube.com/watch' );
                    $output .= '<div class="inbl"><h3 id="' . $id . '">' . $label . '</h3>' . '<p>' . wp_oembed_get($url, $args) . '</p></div>';
                }
            }

        }

        return $output;
    }

}
