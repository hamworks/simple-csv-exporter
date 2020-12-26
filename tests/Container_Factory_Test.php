<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter\Tests;

use HAMWORKS\WP\Simple_CSV_Exporter\Data_Builder;
use HAMWORKS\WP\Simple_CSV_Exporter\Data_Builder_For_WP_Posts;
use HAMWORKS\WP\Simple_CSV_Exporter\Container_Factory;
use WP_UnitTestCase;

/**
 * Class DI_Container_Test
 */
class Container_Factory_Test extends WP_UnitTestCase {

	public function test_create() {
		$container = Container_Factory::create();
		$container->set( 'post_type', 'post' );
		$data = $container->get( Data_Builder::class );
		$this->assertInstanceOf( Data_Builder_For_WP_Posts::class, $data );
		$this->assertEquals( 'Posts', $data->get_name() );
	}

	public function test_create_page() {
		$container = Container_Factory::create();
		$container->set( 'post_type', 'page' );
		$data = $container->get( Data_Builder::class );
		$this->assertInstanceOf( Data_Builder_For_WP_Posts::class, $data );
		$this->assertEquals( 'Pages', $data->get_name() );
	}

	public function test_action_simple_csv_exporter_created_data_builder() {
		add_action(
			'simple_csv_exporter_created_data_builder',
			function ( Data_Builder $data ) {
				// Remove column.
				$data->append_drop_column( 'page_template' );
				// Add custom field column.
				$data->append_meta_key( 'my_meta_key' );
			}
		);
		$this->factory()->post->create_many( 2 );

		$container = Container_Factory::create();
		$container->set( 'post_type', 'post' );
		$data = $container->get( Data_Builder::class );
		foreach ( $data as $row ) {
			$this->assertArrayNotHasKey( 'page_template', $row );
			$this->assertArrayHasKey( 'my_meta_key', $row );
		}
	}
}
