<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use DI\ContainerBuilder;
use function DI\autowire;
use function DI\create;
use function DI\get;

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
				'var.name'          => 'post_type_to_export',
				'slug'              => 'simple_csv_exporter',
				Nonce::class        => create()->constructor( get( 'slug' ) ),
				Data_Builder::class => create( Data_Builder_For_WP_Posts::class )
					->constructor( filter_input( INPUT_POST, get( 'var.name' ), FILTER_SANITIZE_STRING ) ),
				Admin_UI::class     => autowire()->constructor( get( 'slug' ), get( 'var.name' ) ),
				Exporter::class     => autowire()->constructor( get( 'slug' ), get( 'var.name' ) ),
			)
		);

		$container = $builder->build();

		$container->get( Admin_UI::class );
		$container->get( Exporter::class );
	}


}
