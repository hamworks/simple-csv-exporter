<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use Exception;

/**
 * Class Init
 */
class Simple_CSV_Exporter {

	/**
	 * Admin constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		$container = Container_Factory::create();
		$container->get( Admin_UI::class );

		$slug = $container->get( 'slug' );
		add_action(
			"load-tools_page_{$slug}",
			function () use ( $container ) {
				$container->get( Exporter::class );
			}
		);
	}
}
