<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Class Init
 */
class Simple_CSV_Exporter {

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$builder = new ContainerBuilder();
		$builder->addDefinitions(
			array(
				'var.name'      => 'post_type_to_export',
				'slug'          => 'simple_csv_exporter',
				Nonce::class    => function ( ContainerInterface $c ) {
					return new Nonce( $c->get( 'slug' ) );
				},
				Admin_UI::class => function ( ContainerInterface $c ) {
					return new Admin_UI( $c->get( 'slug' ), $c->get( 'var.name' ), $c->get( Nonce::class ) );
				},
				Exporter::class => function ( ContainerInterface $c ) {
					return new Exporter( $c->get( 'slug' ), $c->get( 'var.name' ), $c->get( Nonce::class ) );
				},
			)
		);

		$container = $builder->build();
		$container->get( Admin_UI::class );
		$container->get( Exporter::class );
	}


}
