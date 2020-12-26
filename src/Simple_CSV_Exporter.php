<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

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
		$container = $this->build_di_container();
		$container->get( Admin_UI::class );

		$slug = $container->get( 'slug' );
		add_action(
			"load-tools_page_{$slug}",
			function () use ( $container ) {
				$container->get( Exporter::class );
			}
		);
	}

	/**
	 * Build DI container.
	 *
	 * @return Container
	 * @throws Exception
	 */
	private function build_di_container(): Container {
		$builder = new ContainerBuilder();
		$builder->addDefinitions(
			array(
				'var.name'          => 'post_type_to_export',
				'slug'              => 'simple_csv_exporter',
				Nonce::class        => create()
					->constructor( get( 'slug' ) ),
				Data_Builder::class => function ( ContainerInterface $c ) {
					$post_type = filter_input( INPUT_POST, $c->get( 'var.name' ), FILTER_SANITIZE_STRING ) ?? '';
					return new Data_Builder_For_WP_Posts( $post_type );
				},
				Admin_UI::class     => autowire()
					->constructor( get( 'slug' ), get( 'var.name' ) ),
				Exporter::class     => autowire()
					->constructor( get( 'slug' ) ),
			)
		);

		return $builder->build();
	}


}
