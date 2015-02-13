<?php
if (!defined('JM_TC_VERSION')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/**
 * Globalize options
 * provides filter for options
 * @return $jm_tc_options
 */

if (!function_exists('jm_tc_get_options')) {
    function jm_tc_get_options(){
        global $jm_tc_options;
        $jm_tc_options = apply_filters( 'jm_tc_get_options', get_option('jm_tc') );
        return $jm_tc_options;
    }
}

/**
 * Remove @ from string
 * @param $string
 * @return mixed
 */
function jm_tc_remove_at($string){
    return JM_TC_Utilities::remove_at($string);
}

/**
 * Remove line breaks from string
 * @param $string
 * @return string
 */
function jm_remove_lb($string){
    return JM_TC_Utilities::remove_lb($string);
}

/**
 * Get excerpt by post ID outside the loop
 * @param $post_id
 * @return string|void
 */
function jm_tc_get_excerpt_by_id($post_id){
    return JM_TC_Utilities::get_excerpt_by_id($post_id);
}
