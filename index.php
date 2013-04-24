<?php
/*
    Plugin Name: TVST Countdown
    Plugin URI: http://tozelabs.com
    Description: Powerfull widget to use show times countdown in your site.
    Version: 1.0.0
    Author: toze
    Author URI: https://github.com/toze
    License: GPL2
    
	Copyright 2011  tozelabs.com  (email : admin@tozelabs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Launch the plugin
add_action( 'plugins_loaded', 'tvst_countdown_plugin_loaded' );

// Initializes the plugin and it's features
function tvst_countdown_plugin_loaded() {

	// Set constant
	define( 'TVST_COUNTDOWN_VERSION', '1.0.0' );
	define( 'TVST_COUNTDOWN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'TVST_COUNTDOWN_URL', plugin_dir_url( __FILE__ ) );
	
	// Load require file
	require_once( TVST_COUNTDOWN_DIR . 'tvst-countdown.php' );
	
	// Loads and registers the widgets
	add_action( 'widgets_init', 'TVST_Countdown_load_widgets' );
}

// Load widget, require additional file and register the widget
function TVST_Countdown_load_widgets( $atts ) {
	// Load widget and register TVST Countdown widget
	require_once( TVST_COUNTDOWN_DIR . 'tvst-countdown-widget.php' );
	register_widget( 'TVST_Countdown_Pro_Widget' );
}

?>