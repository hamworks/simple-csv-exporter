<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter\Tests;

use HAMWORKS\WP\Simple_CSV_Exporter\Data_Builder_For_WP_Posts;
use WP_Query;
use WP_UnitTestCase;

/**
 * Unit test for Data_Builder_For_WP_Posts.
 */
class Data_Builder_For_WP_Posts_Test extends WP_UnitTestCase {

	/**
	 * Posts generator.
	 *
	 * @param $post_type
	 * @param int $post_count
	 *
	 * @return int[]
	 */
	public function generate_posts( $post_type, $post_count = 5 ): array {
		$author = $this->factory()->user->create();

		return $this->factory()->post->create_many(
			$post_count,
			array(
				'post_type'   => $post_type,
				'post_author' => $author,
			)
		);
	}

	/**
	 * Date provider for post type.
	 *
	 * @return array
	 */
	public function post_type_provider(): array {
		return array(
			array(
				'post_type' => 'post',
			),
			array(
				'post_type' => 'page',
			),
			array(
				'post_type' => rand_str( 12 ),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider post_type_provider
	 *
	 * @param $post_type
	 */
	public function test_get_rows( $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			register_post_type( $post_type );
		}

		$posts = $this->generate_posts( $post_type );

		foreach ( $posts as $post_id ) {
			$file          = DIR_TESTDATA . '/images/canola.jpg';
			$attachment_id = self::factory()->attachment->create_object(
				$file,
				$post_id,
				array(
					'post_mime_type' => 'image/jpeg',
				)
			);
			set_post_thumbnail( $post_id, $attachment_id );
		}

		$data = new Data_Builder_For_WP_Posts( $post_type );

		$this->assertEquals( $post_type, $data->get_post_type() );

		foreach ( $data->get_rows() as $row ) {
			$this->assertContainsOnly(
				'string',
				array_map(
					function ( $cell ) {
						if ( is_numeric( $cell ) ) {
							return (string) $cell;
						}

						return $cell;
					},
					$row
				)
			);
			$this->assertArrayHasKey( 'ID', $row );
			$this->assertArrayHasKey( 'post_title', $row );
			$this->assertArrayHasKey( 'post_content', $row );
			$this->assertArrayHasKey( 'post_thumbnail', $row );
			$this->assertStringContainsString( 'canola.jpg', $row['post_thumbnail'] );
			$this->assertStringContainsString( 'http', $row['post_thumbnail'] );
			$this->assertArrayNotHasKey( 'tags_input', $row );
			if ( 'post' === $post_type ) {
				$this->assertArrayHasKey( 'post_category', $row );
			}
			if ( 'page' === $post_type ) {
				$this->assertArrayNotHasKey( 'post_category', $row );
			}
		}
	}

	/**
	 * @test
	 * @dataProvider post_type_provider
	 *
	 * @param $post_type
	 */
	public function test_get_rows_with_custom_taxonomy( $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			register_post_type( $post_type );
		}
		$taxonomy = rand_str( 12 );
		register_taxonomy( $taxonomy, array( $post_type ), array( 'public' => true ) );

		$posts = $this->generate_posts( $post_type );

		foreach ( $posts as $post_id ) {
			wp_set_post_terms( $post_id, array( 'term1', 'term2', 'term3' ), $taxonomy, true );
		}
		$data = new Data_Builder_For_WP_Posts( $post_type );

		foreach ( $data->get_rows() as $row ) {
			$this->assertArrayHasKey( 'tax_' . $taxonomy, $row );
			$this->assertEquals( 'term1,term2,term3', $row[ 'tax_' . $taxonomy ] );
		}
	}

	/**
	 * @test
	 * @dataProvider post_type_provider
	 *
	 * @param $post_type
	 */
	public function test_get_rows_with_post_meta( $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			register_post_type( $post_type );
		}

		$posts = $this->generate_posts( $post_type );

		$rand_meta_key   = rand_str( 12 );
		$rand_meta_value = rand_str( 12 );
		foreach ( $posts as $post_id ) {
			update_post_meta( $post_id, $rand_meta_key, $rand_meta_value );
			update_post_meta( $post_id, 'meta_number', 42 );
			update_post_meta( $post_id, 'meta_string', 'string' );
			update_post_meta( $post_id, '_private_string', 'secret' );
		}

		$data = new Data_Builder_For_WP_Posts( $post_type );
		$data->append_meta_key( $rand_meta_key );
		$data->append_meta_key( 'meta_string' );
		$data->append_meta_key( 'meta_number' );
		$data->append_meta_key( '_private_string' );

		foreach ( $data->get_rows() as $row ) {
			$this->assertArrayHasKey( $rand_meta_key, $row );
			$this->assertEquals( $rand_meta_value, $row[ $rand_meta_key ] );
			$this->assertArrayHasKey( 'meta_string', $row );
			$this->assertEquals( 'string', $row['meta_string'] );
			$this->assertArrayHasKey( 'meta_number', $row );
			$this->assertEquals( 42, $row['meta_number'] );
			$this->assertArrayHasKey( '_private_string', $row );
			$this->assertEquals( 'secret', $row['_private_string'] );
		}
	}

	public function test_drop_field() {
		$this->generate_posts( 'post' );
		$data = new Data_Builder_For_WP_Posts( 'post' );
		$data->append_drop_field( 'post_title' );
		$data->remove_drop_field( 'comment_count' );

		foreach ( $data->get_rows() as $row ) {
			$this->assertArrayNotHasKey( 'post_title', $row );
			$this->assertArrayHasKey( 'comment_count', $row );
		}
	}

	/**
	 * @test for `simple_csv_exporter_created_data_builder_pre_get_posts` action.
	 */
	public function test_action_simple_csv_exporter_created_data_builder_for_wp_posts_pre_get_posts() {
		add_action(
			'simple_csv_exporter_created_data_builder_for_wp_posts_pre_get_posts',
			function ( WP_Query $query ) {
				$query->set( 'post_type', 'page' );
			}
		);

		$this->generate_posts( 'post', 1 );
		$this->generate_posts( 'page', 2 );
		$data = new Data_Builder_For_WP_Posts( 'post' );

		foreach ( $data->get_rows() as $row ) {
			$this->assertEquals( 'page', $row['post_type'] );
		}

		$this->assertEquals( 2, count( iterator_to_array( $data->get_rows(), false ) ) );

		remove_all_actions( 'simple_csv_exporter_created_data_builder_for_wp_posts_pre_get_posts' );
	}

	/**
	 * @test for `simple_csv_exporter_created_data_builder_get_post_meta_fields` filter.
	 */
	public function test_filter_simple_csv_exporter_created_data_builder_for_wp_posts_get_post_meta_fields() {
		add_filter(
			'simple_csv_exporter_created_data_builder_for_wp_posts_get_post_meta_fields',
			function ( array $fields ) {
				foreach (
					array(
						'flag_for_test_1',
						'flag_for_test_2',
					) as $key
				) {
					if ( isset( $fields[ $key ] ) ) {
						$fields[ $key ] = ! empty( $fields[ $key ] ) ? 'TRUE' : 'FALSE';
					}
				}
				return $fields;
			}
		);

		$posts = $this->generate_posts( 'post' );

		foreach ( $posts as $post_id ) {
			update_post_meta( $post_id, 'flag_for_test_1', '' );
			update_post_meta( $post_id, 'flag_for_test_2', 42 );
			update_post_meta( $post_id, 'flag_for_test_3', 42 );
		}

		$data = new Data_Builder_For_WP_Posts( 'post' );

		$data->append_meta_key( 'flag_for_test_1' );
		$data->append_meta_key( 'flag_for_test_2' );
		$data->append_meta_key( 'flag_for_test_3' );

		foreach ( $data->get_rows() as $row ) {
			$this->assertEquals( 'FALSE', $row['flag_for_test_1'] );
			$this->assertEquals( 'TRUE', $row['flag_for_test_2'] );
			$this->assertEquals( '42', $row['flag_for_test_3'] );
		}
	}


}
