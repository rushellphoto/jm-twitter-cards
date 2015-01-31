<?php
/*
Plugin Name: JM Twitter Cards
Plugin URI: http://tweetpressfr.github.io
Description: Meant to help users to implement and customize Twitter Cards easily
Author: Julien Maury
Author URI: http://tweetpressfr.github.io
Version: 5.4.3
License: GPL2++

JM Twitter Cards Plugin
Copyright (C) 2013-2015, Julien Maury - contact@tweetpress.fr

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*    Sources: 
* - https://dev.twitter.com/docs/cards
* - https://dev.twitter.com/docs/cards/getting-started#open-graph
* - https://dev.twitter.com/docs/cards/markup-reference
* - https://dev.twitter.com/docs/cards/types/player-card
* - https://dev.twitter.com/docs/cards/app-installs-and-deep-linking [GREAT]
* - https://dev.twitter.com/discussions/17878
* - https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress [GREAT]
* - https://about.twitter.com/fr/press/brand-assets
* - http://www.jqeasy.com/jquery-character-counter
* - https://trepmal.com/2011/04/03/change-the-virtual-robots-txt-file/ [GREAT]
* - https://github.com/pippinsplugins/Settings-Import-and-Export-Example-Pluginc [GREAT]
* - http://www.wpexplorer.com/wordpress-image-crop-sizes/
*/


//Add some security, no direct load !
defined('ABSPATH')
or die('No direct load !');


//Constantly constant
define( 'JM_TC_VERSION', '5.4.3' );
define( 'JM_TC_DIR', plugin_dir_path(__FILE__) );
define( 'JM_TC_CLASS_DIR', JM_TC_DIR . 'classes/' );
define( 'JM_TC_ADMIN_CLASS_DIR', JM_TC_DIR . 'classes/admin/' );
define( 'JM_TC_ADMIN_PAGES_DIR', JM_TC_DIR . 'views/pages/' );
define( 'JM_TC_METABOX_DIR', JM_TC_DIR . 'classes/meta-box/' );

define( 'JM_TC_LANG_DIR', dirname(plugin_basename(__FILE__)) . '/languages/' );
define( 'JM_TC_TEXTDOMAIN', 'jm-tc' );
define( 'JM_TC_DOC_TEXTDOMAIN', 'jm-tc-doc' );
define( 'JM_TC_SLUG', 'jm_tc' );

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
function jm_tc_load_files( $dir, $files, $suffix = '' ) {
    foreach( $files as $file ) {
        if( is_file( $dir .  $file . $suffix . ".php" ) ) {
            require_once( $dir . $file . $suffix . ".php" );
        }
    }
}

//Call modules
if (is_admin()) {
    jm_tc_load_files(JM_TC_ADMIN_CLASS_DIR, array('author', 'tabs', 'admin-tc', 'meta-box', 'preview', 'import-export'), '.class');
}

jm_tc_load_files(JM_TC_CLASS_DIR, array('init','utilities', 'particular', 'thumbs', 'disable', 'options', 'markup'), '.class');
jm_tc_load_files(JM_TC_DIR.'functions/', array('functions'), '.inc');

/**
* On activation
*/
register_activation_hook(__FILE__, array('JM_TC_Init', 'activate'));


/**
 * Everything that should trigger early
 */
add_action( 'plugins_loaded', 'jm_tc_plugins_loaded' );
function jm_tc_plugins_loaded(){

    if (is_admin()) {

        load_plugin_textdomain(JM_TC_DOC_TEXTDOMAIN, false, JM_TC_LANG_DIR);

        new JM_TC_Admin;
        new JM_TC_Import_Export;
        new JM_TC_Metabox;

    }

    //langs
    load_plugin_textdomain(JM_TC_TEXTDOMAIN, false, JM_TC_LANG_DIR);

    new JM_TC_Init;
    new JM_TC_Disable;
    new JM_TC_Particular;
    new JM_TC_Markup;

}
