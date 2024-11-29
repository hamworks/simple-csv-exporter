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
				'var.post_type_to_export' => 'post_type_to_export',
				'var.encoding'            => 'encoding',
				'post_type'               => function ( ContainerInterface $c ) {
					return filter_input( INPUT_POST, $c->get( 'var.post_type_to_export' ), FILTER_SANITIZE_SPECIAL_CHARS ) ?? '';
				},
				Nonce::class              => create()->constructor( $slug ),
				Data_Builder::class       => factory(
					function ( $post_type ) {
						$data_builder = new Data_Builder_For_WP_Posts( $post_type );

						/**
						 * Fires after data generator is created, but before export.
						 *
						 * @param Data_Builder $data
						 */
						do_action( 'simple_csv_exporter_created_data_builder', $data_builder );
						return $data_builder;
					}
				)->parameter( 'post_type', get( 'post_type' ) ),
				CSV_Writer::class         => factory(
					function () {
						$csv_writer = new CSV_Writer();

						/**
						 * Fires after data generator is created, but before export.
						 *
						 * @param Data_Builder $data
						 */
						do_action( 'simple_csv_exporter_created_csv_writer', $csv_writer );
						return $csv_writer;
					}
				),
				Admin_UI::class           => autowire()->constructor( $slug, get( 'var.post_type_to_export' ), get( 'var.encoding' ) ),
				Exporter::class           => autowire(),
			)
		);

		return $builder->build();
	}
}
