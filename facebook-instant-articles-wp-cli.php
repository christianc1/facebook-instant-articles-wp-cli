<?php
/**
 * Plugin Name: Facebook Instant Articles WP CLI Commands
 * Description: Adds some WP CLI commands to assist in managing Facebook Instant Articles
 * Author: LiftUX <christian@liftux.com>
 * Author URI: https://liftux.com/
 * Version: 0.1.0
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package  Facebook_Instant_Articles_WP_CLI
 */

namespace Lift\WP_CLI\Facebook_Instant_Articles;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once plugin_dir_path( __FILE__ ) . '/classes/facebook-instant-articles-wp-cli-command.php';

	add_action( 'plugins_loaded', function() {
		if ( ! class_exists( 'WPCOM_VIP_CLI_Command' ) || ! class_exists( 'Instant_Articles_Publisher') ) {
			return;
		}

		\WP_CLI::add_command( 'instant-articles', __NAMESPACE__ . '\\Command' );
	} );
}