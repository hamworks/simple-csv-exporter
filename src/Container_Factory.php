<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;

/**
 * DI Container Factory
 */
class Container_Factory {

	/**
	 * @param string $slug
	 *
	 * @return Container
	 * @throws Exception
	 */
	public static function create( string $slug ): Container {
		$builder = new ContainerBuilder();
		$builder->addDefinitions(
			array(
				Nonce::class        => create()->constructor( $slug ),
				Data_Builder::class => factory(
					function ( Request $request ) {
						$data_builder = new Data_Builder_For_WP_Posts( $request->get_post_type_to_export() );

						/**
						 * Fires after data generator is created, but before export.
						 *
						 * @param Data_Builder $data
						 */
						do_action( 'simple_csv_exporter_created_data_builder', $data_builder );

						return $data_builder;
					}
				),
				CSV_Writer::class   => factory(
					function ( Request $request ) {
						if ( $request->get_encoding() === Encodings::UTF8_WITH_BOM['name'] ) {
							$csv_writer = new CSV_Writer_With_BOM();
						} else {
							$csv_writer = new CSV_Writer();
						}

						/**
						 * Fires after data generator is created, but before export.
						 *
						 * @param CSV_Writer $csv_writer
						 */
						do_action( 'simple_csv_exporter_created_csv_writer', $csv_writer );

						return $csv_writer;
					}
				),
				Admin_UI::class     => autowire()->constructor( $slug ),
				Exporter::class     => autowire(),
			)
		);

		return $builder->build();
	}
}
