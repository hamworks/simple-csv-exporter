<?php
/**
 * Plugin Name:     simple-csv-exporter
 * Plugin URI:      https://github.com/team-hamworks/simple-csv-exporter
 * Description:     CSV Exporter
 * Author:          HAMWORKS
 * Author URI:      https://ham.works
 * License:         GPLv2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wp-csv-exporter
 * Domain Path:     /languages
 * Version: 0.0.3
 */

use HAMWORKS\WP\Simple_CSV_Exporter\Simple_CSV_Exporter;

require_once __DIR__ . '/vendor/autoload.php';

add_action(
	'init',
	function () {
		new Simple_CSV_Exporter();
	}
);
