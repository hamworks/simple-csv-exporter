<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use Exception;

/**
 * Class Init
 */
class Simple_CSV_Exporter {

	const SLUG = 'simple_csv_exporter';

	/**
	 * Admin constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {

		$container = Container_Factory::create( self::SLUG );
		$container->get( Admin_UI::class );

		add_action(
			'load-tools_page_' . self::SLUG,
			function () use ( $container ) {
				$container->get( Exporter::class );
			}
		);
	}
}
