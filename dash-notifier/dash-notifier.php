<?php
/**
 * Plugin Name:       Dash Notifier
 * Plugin URI:        https://github.com/litespeedtech/wp-dashboard-notifier
 * Description:       WordPress dashboard notifier
 * Version:           1.1.2
 * Author:            LiteSpeed Technologies
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
// define( 'DASH_NOTIFIER_MSG', json_encode( array( 'msg' => 'This is a message from your hosting provider. We have recently increased the server speed by installing LiteSpeed Web Server with the LSCache module. We recommend installing the LiteSpeed Cache plugin. This plugin includes a nice collection of optimization features, and also works with the server-side cache module to maximize your WordPress performance.', 'plugin' => 'litespeed-cache' ) ) ) ;

if ( defined( 'DASH_NOTIFIER_V' ) ) {
	return ;
}

define( 'DASH_NOTIFIER_V', '1.1.2' ) ;

// Storage hook
add_action( 'setup_theme', 'dash_notifier_save_msg' ) ;
if ( defined( 'SHORTINIT' ) ) {
	dash_notifier_save_msg() ;
}

// Display hook
add_action( 'admin_print_styles', 'dash_notifier_new_msg' ) ;

// Dismiss/install/uninstall hook
add_action( 'admin_init', 'dash_notifier_admin_init' ) ;

/**
 * Admin init actions
 *
 * @since  1.0
 */
function dash_notifier_admin_init()
{
	// Dismiss hook
	if ( empty( $_GET[ 'dash_notifier_action' ] ) || empty( $_GET[ 'nonce' ] ) ) {
		return ;
	}

	if ( ! wp_verify_nonce( $_GET[ 'nonce' ], $_GET[ 'dash_notifier_action' ] ) ) {
		return ;
	}

	if ( ! dash_notifier_can_operate() ) {
		return ;
	}

	switch ( $_GET[ 'dash_notifier_action' ] ) {
		case 'uninstall':
			dash_notifier_uninstall() ;
			break;

		case 'activate':
			dash_notifier_install() ;
			break;

		case 'dismiss':
		default:
			dash_notifier_dismiss() ;
			break;
	}

	wp_redirect( 'index.php' ) ;
	exit ;
}

/**
 * If current user can operate notifier related options
 *
 * @since  1.0
 */
function dash_notifier_can_operate()
{
	if ( ! current_user_can( 'manage_options' ) ) {
		return false ;
	}

	return true ;
}

/**
 * Detect if the plugin is active or not
 *
 * @since  1.0
 */
function dash_notifier_is_plugin_active( $plugin )
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ) ;

	$plugin_path = $plugin . '/' . $plugin . '.php' ;

	return is_plugin_active( $plugin_path ) ;
}

/**
 * Detect if the plugin is installed or not
 *
 * @since  1.0
 */
function dash_notifier_is_plugin_installed( $plugin )
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ) ;

	$plugin_path = $plugin . '/' . $plugin . '.php' ;

	$valid = validate_plugin( $plugin_path ) ;

	return ! is_wp_error( $valid ) ;
}

/**
 * Grab a plugin info from WordPress
 *
 * @since  1.0
 */
function dash_notifier_get_plugin_info( $slug )
{
	include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ) ;
	$result = plugins_api( 'plugin_information', array( 'slug' => $slug ) ) ;

	if ( is_wp_error( $result ) ) {
		return false ;
	}

	return $result ;
}

/**
 * Uninstall dash notifier
 *
 * From developer of dash notifier: We miss you though!
 *
 * @since  1.0
 */
function dash_notifier_uninstall()
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ) ;
	file_put_contents( ABSPATH . '.dash_notifier_bypass', date( 'Y-m-d H:i:s' ) ) ;
	delete_option( 'dash_notifier.msg' ) ;

	dash_notifier_is_plugin_active( 'dash-notifier' ) && deactivate_plugins( 'dash-notifier/dash-notifier.php' ) ;
	delete_plugins( array( 'dash-notifier/dash-notifier.php' ) ) ;
}

/**
 * Install the 3rd party plugin
 *
 * @since  1.0
 */
function dash_notifier_install()
{
	$msg = dash_notifier_get_msg() ;

	if ( empty( $msg[ 'plugin' ] ) ) {
		return ;
	}

	// Check if plugin is installed already
	if ( dash_notifier_is_plugin_active( $msg[ 'plugin' ] ) ) {
		return ;
	}

	/**
	 * @see wp-admin/update.php
	 */
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' ;
	include_once ABSPATH . 'wp-admin/includes/file.php' ;
	include_once ABSPATH . 'wp-admin/includes/misc.php' ;

	$plugin_path = $msg[ 'plugin' ] . '/' . $msg[ 'plugin' ] . '.php' ;

	if ( ! dash_notifier_is_plugin_installed( $msg[ 'plugin' ] ) ) {
		$plugin_info = dash_notifier_get_plugin_info( $msg[ 'plugin' ] ) ;
		if ( ! $plugin_info ) {
			return ;
		}
		// Try to install plugin
		try {
			ob_start() ;
			$skin = new \Automatic_Upgrader_Skin() ;
			$upgrader = new \Plugin_Upgrader( $skin ) ;
			$result = $upgrader->install( $plugin_info->download_link ) ;
			ob_end_clean() ;
		} catch ( \Exception $e ) {
			return ;
		}
	}

	if ( ! is_plugin_active( $plugin_path ) ) {
		activate_plugin( $plugin_path ) ;
	}
}

/**
 * Receive and store dashboard msg
 *
 * @since  1.0
 */
function dash_notifier_save_msg()
{
	if ( ! defined( 'DASH_NOTIFIER_MSG' ) ) {
		return ;
	}

	$msg = json_decode( DASH_NOTIFIER_MSG, true ) ;
	if ( ! $msg || empty( $msg[ 'msg' ] ) ) {
		return ;
	}

	$existing_msg = dash_notifier_get_msg() ;

	$plugin = $plugin_name = '' ;
	if ( ! empty( $msg[ 'plugin' ] ) ) {
		$plugin = $msg[ 'plugin' ] ;

		if ( ! empty( $msg[ 'plugin_name' ] ) ) {
			$plugin_name = $msg[ 'plugin_name' ] ;
		}
		// Query plugin name
		else {
			$data = dash_notifier_get_plugin_info( $plugin ) ;
			if ( ! $data ) {
				return ;
			}

			$plugin_name = $data->name ;
		}
	}


	// Append msg
	$existing_msg[ 'msg' ] = $msg[ 'msg' ] ;
	$existing_msg[ 'msg_md5' ] = md5( $msg[ 'msg' ] ) ;
	$existing_msg[ 'plugin' ] = $plugin ;
	$existing_msg[ 'plugin_name' ] = $plugin_name ;

	update_option( 'dash_notifier.msg', $existing_msg ) ;
}

/**
 * Read current msg
 *
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
 *
 * @since  1.0
 */
function dash_notifier_new_msg()
{
	$screen = get_current_screen() ;
	$screen = $screen ? $screen->id : false ;
	if ( $screen != 'dashboard' ) {
		return ;
	}

	if ( ! dash_notifier_can_operate() ) {
		return ;
	}

	$msg = dash_notifier_get_msg() ;

	if ( ! $msg || empty( $msg[ 'msg' ] ) || $msg[ 'msg_md5' ] == $msg[ 'msg_md5_previous' ] ) {
		return ;
	}

	if ( ! empty( $msg[ 'plugin' ] ) ) {
		// Check if plugin is installed already
		if ( dash_notifier_is_plugin_active( $msg[ 'plugin' ] ) ) {
			return ;
		}
	}

	add_action( 'admin_notices', 'dash_notifier_show_msg' ) ;
}

/**
 * Print dashboard message
 *
 * @since  1.0
 */
function dash_notifier_show_msg()
{
	$msg = dash_notifier_get_msg() ;

	$dismiss_txt = __( 'Dismiss' ) ;

	$install_link = '' ;
	if ( ! empty( $msg[ 'plugin' ] ) && ! empty( $msg[ 'plugin_name' ] ) ) {
		// If plugin installed, no need to show msg
		if ( dash_notifier_is_plugin_active( $msg[ 'plugin' ] ) ) {
			return ;
		}

		// Check if plugin is installed but not activated
		if ( dash_notifier_is_plugin_installed( $msg[ 'plugin' ] ) ) {
			$install_link = '<a href="?dash_notifier_action=activate&nonce=' . wp_create_nonce( 'activate' ) . '" class="install-now button button-primary button-small">' . sprintf( _x( 'Activate %s', 'plugin' ), $msg[ 'plugin_name' ] ) . '</a>' ;
		}
		else {
			$install_link = '<a href="?dash_notifier_action=activate&nonce=' . wp_create_nonce( 'activate' ) . '" class="install-now button button-primary button-small">' . sprintf( __( 'Install %s now' ), $msg[ 'plugin_name' ] ) . '</a>' ;
		}
	}

	$dont_show_link = '<a href="?dash_notifier_action=uninstall&nonce=' . wp_create_nonce( 'uninstall' ) . '" class="button button-small dash-notifier-uninstall">' . __( 'Never Notify Me Again', 'dash-notifier' ) . '</a>' ;

	$nonce_dismiss = wp_create_nonce( 'dismiss' ) ;
	$msg_con = $msg[ 'msg' ] ;
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
	a.button.dash-notifier-uninstall {
		margin-left:auto;
		margin-top:auto;
	}
	</style>
	<div class="updated dash-notifier-msg">
		<a class="dash-notifier-close notice-dismiss" href="?dash_notifier_action=dismiss&nonce=$nonce_dismiss">$dismiss_txt</a>

		<p>$msg_con</p>
		<p style='display:flex;'>
			$install_link
			$dont_show_link
		</p>
	</div>
eot;
}

/**
 * Dismiss current dashboard message
 *
 * @since  1.0
 */
function dash_notifier_dismiss()
{
	$msg = dash_notifier_get_msg() ;
	$msg[ 'msg_md5_previous' ] = $msg[ 'msg_md5' ] ;

	delete_option( 'dash_notifier.msg' ) ;
	update_option( 'dash_notifier.msg', $msg ) ;
}
