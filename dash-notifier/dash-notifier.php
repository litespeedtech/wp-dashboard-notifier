<?php
/**
 * Plugin Name:       Dash Notifier
 * Plugin URI:        https://github.com/litespeedtech/wp-dashboard-notifier
 * Description:       WordPress dashboard notifier
 * Version:           1.0
 * Author:            LiteSpeed Technologies
 * Author URI:        https://github.com/litespeedtech/wp-dashboard-notifier
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.html
 * Text Domain:       dash-notifier
 *
 * Copyright (C) 2015-2017 LiteSpeed Technologies, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */
defined( 'WPINC' ) || exit ;

// Storage hook
if ( defined( 'DASH_NOTIFIER_MSG' ) ) {
	add_action( 'setup_theme', 'dash_notifier_save_msg' ) ;
}

// Display hook
add_action( 'admin_print_styles', 'dash_notifier_admin_init' ) ;

// Dismiss hook
if ( ! empty( $_GET[ 'dash_notifier_dismiss' ] ) ) {
	add_action( 'admin_init', 'dash_notifier_dismiss' ) ;
}

/**
 * Receive and store dashboard msg
 * @since  1.0
 */
function dash_notifier_save_msg()
{
	$msg = json_decode( DASH_NOTIFIER_MSG, true ) ;
	if ( $msg && ! empty( $msg[ 'msg' ] ) ) {
		$existing_msg = dash_notifier_get_msg() ;

		// Append msg
		$existing_msg[ 'msg' ] = $msg[ 'msg' ] ;
		$existing_msg[ 'msg_md5_previous' ] = $existing_msg[ 'msg_md5' ] ;
		$existing_msg[ 'msg_md5' ] = md5( $msg[ 'msg' ] ) ;
		$existing_msg[ 'plugin' ] = ! empty( $msg[ 'plugin' ] ) ? $msg[ 'plugin' ] : '' ;

		update_option( 'dash_notifier.msg', $existing_msg ) ;
	}
}

/**
 * Read current msg
 * @since  1.0
 */
function dash_notifier_get_msg()
{
	$existing_msg = get_option( 'dash_notifier.msg', array() ) ;

	if ( ! is_array( $existing_msg ) ) {
		$existing_msg = array(
			'msg'		=> '',
			'msg_md5'	=> '',
			'msg_md5_previous'	=> '',
		) ;
	}

	return $existing_msg ;
}

/**
 * Check if can print dashboard message or not
 * @since  1.0
 */
function dash_notifier_admin_init()
{
	$screen = get_current_screen() ;
	$screen = $screen ? $screen->id : false ;
	if ( $screen != 'dashboard' ) {
		return ;
	}

	$msg = dash_notifier_get_msg() ;

	if ( ! $msg || empty( $msg[ 'msg' ] ) || $msg[ 'msg_md5' ] == $msg[ 'msg_md5_previous' ] ) {
		return ;
	}

	add_action( 'admin_notices', 'dash_notifier_show_msg' ) ;
}

/**
 * Print dashboard message
 * @since  1.0
 */
function dash_notifier_show_msg()
{
	$msg = dash_notifier_get_msg() ;
	if ( empty( $msg[ 'msg' ] ) ) {
		return ;
	}

	echo <<<eot
	<style>
	div.dash-notifier-msg {
		overflow: hidden;
		position: relative;
		border-left-color: #000099!important;
	}
	a.dash-notifier-close {
		position: static;
		float: right;
		top: 0;
		right: 0;
		padding: 0 15px 10px 28px;
		margin-top: -10px;
		font-size: 13px;
		line-height: 1.23076923;
		text-decoration: none;
	}
	a.dash-notifier-close:before {
		position: relative;
		top: 18px;
		left: -20px;
		-webkit-transition: all .1s ease-in-out;
		transition: all .1s ease-in-out;
	}
	</style>
	<div class="updated dash-notifier-msg">
		<a class="dash-notifier-close notice-dismiss" href="?dash_notifier_dismiss=1">Dismiss</a>

		<p>{$msg[msg]}</p>
	</div>
eot;
}

/**
 * Dismiss current dashboard message
 * @since  1.0
 */
function dash_notifier_dismiss()
{
	$msg = dash_notifier_get_msg() ;
	$msg[ 'msg_md5_previous' ] = $msg[ 'msg_md5' ] ;

	delete_option( 'dash_notifier.msg' ) ;
	update_option( 'dash_notifier.msg', $msg ) ;
}
