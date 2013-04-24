<?php
/*
    TVST Countdown 1.0.0
    http://tozelabs.com
    Copyright 2013  tozelabs.com  (email : antonio@tozelabs.com)

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


/**
 * TVST countdown main function
 * Set up the default form values
 * date-time: mm jj aa hh mn
 * @return
 * @since 1.0
**/
function tvst_countdown( $args ) {

	$defaults = array(
		'id' 				=> '',
		'title' 			=> esc_attr__( 'Countdown', 'tvst-countdown' ),
		'show' 				=> 'Game of Thrones',
		'show_id'			=> 121361,
		'theme'				=> 0
	);

	/* Merge the user-selected arguments with the defaults. */
	$instance = wp_parse_args( (array) $args, $defaults );
	
	extract($instance, EXTR_SKIP);
	//print_r($defaults);

	return "<div id='countdown-$id'></div>";
}


/**
 * Get the custom styles/script for each meta for further use 
 * Using wp_head hook to push this function to the head
 * @return
 * @since 1.0
**/
add_action( 'wp_enqueue_scripts', 'tvst_countdown_wp_head' );

function tvst_countdown_wp_head() {
	$id   = get_the_ID();
	$meta = get_post_meta($id, 'tvst_countdown', true);
}




?>