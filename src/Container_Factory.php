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
	 * @return Container
	 * @throws Exception
	 */
	public static function create(): Container {
		$builder = new ContainerBuilder();
		$builder->addDefinitions(
			array(
				'var.name'          => 'post_type_to_export',
				'slug'              => 'simple_csv_exporter',
				'post_type'         => function ( ContainerInterface $c ) {
					return filter_input( INPUT_POST, $c->get( 'var.name' ), FILTER_SANITIZE_STRING ) ?? '';
				},
				Nonce::class        => create()->constructor( get( 'slug' ) ),
				Data_Builder::class => factory(
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
				Admin_UI::class     => autowire()->constructor( get( 'slug' ), get( 'var.name' ) ),
				Exporter::class     => autowire()->constructor( get( 'slug' ) ),
			)
		);

		return $builder->build();
	}
}
