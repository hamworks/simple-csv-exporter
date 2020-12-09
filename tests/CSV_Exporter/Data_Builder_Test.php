<?php

namespace HAMWORKS\WP\Tests\Simple_CSV_Exporter;

use HAMWORKS\WP\Simple_CSV_Exporter\Data_Builder;
use WP_UnitTestCase;

/**
 * Class Data_Builder_Test
 */
class Data_Builder_Test extends WP_UnitTestCase {

	public function generate_posts( $post_type ) {
		$author = $this->factory()->user->create();

		return $this->factory()->post->create_many(
			5,
			array(
				'post_type'   => $post_type,
				'post_author' => $author,
			)
		);
	}

	/**
	 * @return array
	 */
	public function postProvider() {
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
	 * @dataProvider postProvider
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

		$data = new Data_Builder( $post_type );

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
	 * @dataProvider postProvider
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
		$data = new Data_Builder( $post_type );

		foreach ( $data->get_rows() as $row ) {
			$this->assertArrayHasKey( 'tax_' . $taxonomy, $row );
			$this->assertEquals( 'term1,term2,term3', $row[ 'tax_' . $taxonomy ] );
		}
	}

	/**
	 * @test
	 * @dataProvider postProvider
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

		$data = new Data_Builder( $post_type );
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
		$data = new Data_Builder( 'post' );
		$data->append_drop_field( 'post_title' );
		$data->remove_drop_field( 'comment_count' );

		foreach ( $data->get_rows() as $row ) {
			$this->assertArrayNotHasKey( 'post_title', $row );
			$this->assertArrayHasKey( 'comment_count', $row );
		}
	}


}
