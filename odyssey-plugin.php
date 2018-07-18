<?php

/**
 *
 * @link              https://xavierroy.com
 * @since             1.0.0
 * @package           Odyssey_Plugin.php
 *
 * @wordpress-plugin
 * Plugin Name:       Odyssey - Site Enhancements
 * Plugin URI:        https://github.com/xavierroy/odyssey-plugin/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Xavier Roy
 * Author URI:        https://xavierroy.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       odyssey-plugin.php
 * Domain Path:       /languages
 * GitHub Plugin URI:	xavierroy/odyssey-plugin
 * GitHub Plugin URI:	https://github.com/xavierroy/odyssey-plugin

 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/*
 * Table of Contents
 * 1. Shortcode for Search Form
*/

/*
1. Shortcode for Search Form
The [wpsearch] shortcode will add a search form anywhere in a post or page.
*/
add_shortcode('wpsearch', 'get_search_form');
