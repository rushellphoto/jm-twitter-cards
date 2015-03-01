<?php
namespace jm_twitter_cards;

if (!defined('JM_TC_VERSION')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class Author{

    /**
     * Create suggested plugins list
     * @since  5.3.0
     * @return string
     * @param array $slugs
     */
    public static function get_plugins_list($slugs = array()){

        $list = '<ul class="plugins-list">';

        foreach ($slugs as $slug => $name) {

            $list .= '<li><a class="button" target="_blank" href="http://wordpress.org/plugins/' . $slug . '">' . sprintf(__('%s', JM_TC_TEXTDOMAIN), $name) . '</a></li>';
        }

        $list .= '</ul>';
        return $list;

    }

    /**
     * Displays author infos
     * @since  5.3.0
     * @return string
     * @param $name
     * @param $desc
     * @param $gravatar_email
     * @param $url
     * @param $donation
     * @param $twitter
     * @param $googleplus
     * @param array $slugs
     */
    public static function get_author_infos($name, $desc, $gravatar_email, $url, $donation, $twitter, $googleplus, $slugs = array()){

        $output  = '<div class="metabox-holder"><div class="postbox">';
        $output .= '<h3 class="hndle">' . __('The developer', JM_TC_TEXTDOMAIN) . '</h3>';
        $output .= '<div class="inside">';
        $output .= '<figure>';
        $output .= '<img src="http://www.gravatar.com/avatar/' . md5($gravatar_email) . '" alt=""/>';
        $output .= '<figcaption>';
        $output .= $name;
        $output .= '<p>' . $desc . '</p>';
        $output .= '<figcaption>';
        $output .= '</figure>';
        $output .= '<ul class="social-links">';
        $output .= '<li class="inbl"><a class="social button button-secondary dashicons-before dashicons-admin-site" href="' . $url . '" target="_blank" title="' . esc_attr__('My website', JM_TC_TEXTDOMAIN) . '"><span class="visually-hidden">' . __('My website', JM_TC_TEXTDOMAIN) . '</span></a></li>';
        $output .= '<li class="inbl"><a class="social button button-secondary link-like dashicons-before dashicons-twitter" href="http://twitter.com/intent/user?screen_name=' . $twitter . '" title="' . esc_attr__('Follow me', JM_TC_TEXTDOMAIN) . '"> <span class="visually-hidden">' . __('Follow me', JM_TC_TEXTDOMAIN) . '</span></a></li>';
        $output .= '<li class="inbl"><a class="social button button-secondary dashicons-before dashicons-googleplus" href="' . $googleplus . '" target="_blank" title="' . esc_attr__('Add me to your circles', JM_TC_TEXTDOMAIN) . '"> <span class="visually-hidden">' . __('Add me to your circles', JM_TC_TEXTDOMAIN) . '</span></a></li>';
        $output .= '</ul>';
        $output .= '<h3 class="hndle"><span>' . __('Keep the plugin free', JM_TC_TEXTDOMAIN) . '</span></h3>';
        $output .= '<p>' . __('Please help if you want to keep this plugin free.', JM_TC_TEXTDOMAIN) . '</p>';
        $output .= '
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="' . $donation . '">
                    <input type="image" src="https://www.paypalobjects.com/en_US/FR/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
                    </form>
                    ';
        $output .= '<h3><span>' . __('Plugin', JM_TC_TEXTDOMAIN) . '</span></h3>';
        $output .= '<p>';
        $output .= __('Maybe you will like this plugin too: ', JM_TC_TEXTDOMAIN) . self::get_plugins_list($slugs);
        $output .= '</p>';
        $output .= '<div>';
        $output .= '</div>';
        $output .= '</div>';

        echo $output;
    }
}
