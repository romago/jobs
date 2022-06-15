<?php

/**
 *
 * @link              https://www.roma.la
 * @since             1.0.0
 * @package           Jobs
 *
 * @wordpress-plugin
 * Plugin Name:       Import jobs
 * Plugin URI:        https://www.roma.la
 * Description:       Sync Jobs Board from GreenHouse website.
 * Version:           1.7.0
 * Author:            Roman Golubev
 * Author URI:        https://www.roma.la
 * Text Domain:       import jobs
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'JOBS_VERSION', '1.7.0' );

require plugin_dir_path( __FILE__ ) . 'includes/class-jobs.php';

function run_jobs() {

	$plugin = new Jobs();
	$plugin->run();

}

run_jobs();
