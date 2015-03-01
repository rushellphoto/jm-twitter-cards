<?php
namespace jm_twitter_cards;

//Add some security, no direct load !
defined('ABSPATH')
or die('No direct load !');


//Constantly constant
define( 'JM_TC_NS', '\\' .  __NAMESPACE__ . '\\' );
define( 'JM_TC_VERSION', '5.5' );
define( 'JM_TC_DIR', plugin_dir_path(__FILE__) );
define( 'JM_TC_CLASS_DIR', JM_TC_DIR . 'classes/' );
define( 'JM_TC_ADMIN_CLASS_DIR', JM_TC_DIR . 'classes/admin/' );
define( 'JM_TC_ADMIN_PAGES_DIR', JM_TC_DIR . 'views/pages/' );
define( 'JM_TC_METABOX_DIR', JM_TC_DIR . 'classes/meta-box/' );

define( 'JM_TC_LANG_DIR', dirname(plugin_basename(__FILE__)) . '/languages/' );
define( 'JM_TC_TEXTDOMAIN', 'jm-tc' );
define( 'JM_TC_DOC_TEXTDOMAIN', 'jm-tc-doc' );

define( 'JM_TC_URL', plugin_dir_url(__FILE__) );
define( 'JM_TC_METABOX_URL', JM_TC_URL . 'classes/meta-box/' );
define( 'JM_TC_IMG_URL', JM_TC_URL . 'assets/img/' );
define( 'JM_TC_CSS_URL', JM_TC_URL . 'assets/css/' );
define( 'JM_TC_JS_URL', JM_TC_URL . 'assets/js/' );

/**
 * Load stuffs
 * @param $dir
 * @param $files
 * @param string $suffix
 */
function load_files( $dir, $files, $suffix = '' ) {
    foreach( $files as $file ) {
        if( is_file( $dir .  $file . $suffix . ".php" ) ) {
            require_once( $dir . $file . $suffix . ".php" );
        }
    }
}


//Call modules only admin
if (is_admin()) {
    load_files(JM_TC_ADMIN_CLASS_DIR, array('author', 'tabs', 'admin-tc', 'meta-box', 'preview', 'import-export'), '.class');
}

//Call functions
load_files(JM_TC_DIR.'functions/', array('functions'), '.inc');

//Call modules
load_files(JM_TC_CLASS_DIR, array('init', 'utilities', 'particular', 'thumbs', 'disable', 'options', 'markup'), '.class');


/**
 * On activation
 */
\register_activation_hook(__FILE__, array( JM_TC_NS .  'Init', 'activate'));


/**
 * Everything that should trigger early
 */
add_action( 'plugins_loaded', JM_TC_NS . 'plugins_loaded' );
function plugins_loaded(){

    if (is_admin()) {

        load_plugin_textdomain(JM_TC_DOC_TEXTDOMAIN, false, JM_TC_LANG_DIR);

        $GLOBALS['tc-admin'] = new Admin;
        $GLOBALS['tc-import-export'] = new Import_Export;
        $GLOBALS['tc-metabox'] =new Metabox;

    }

    //langs
    load_plugin_textdomain(JM_TC_TEXTDOMAIN, false, JM_TC_LANG_DIR);

    $GLOBALS['tc-init'] = new Init;
    $GLOBALS['tc-disable'] = new Disable;
    $GLOBALS['tc-particular'] = new Particular;
    $GLOBALS['tc-markup'] = new Markup;

}

/**
 * Add a "Settings" link in the plugins list
 * @param $links
 * @return mixed
 */

add_filter('plugin_action_links', JM_TC_NS . 'settings_action_links', 10, 2);
function settings_action_links($links){
    $settings_link = '<a href="' . admin_url('admin.php?page='.'jm_tc') . '">' . __("Settings") . '</a>';
    array_unshift($links, $settings_link);

    return $links;
}