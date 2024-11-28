<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use Generator;
use WP_Post;
use WP_Query;
use WP_Taxonomy;

/**
 * Class CSV_Generator
 */
class Data_Builder_For_WP_Posts extends Data_Builder {

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	private string $post_type;

	/**
	 * Post field keys to be removed.
	 *
	 * @var string[]
	 */
	protected array $drop_columns = array(
		'post_date_gmt',
		'ping_status',
		'to_ping',
		'pinged',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'guid',
		'post_mime_type',
		'comment_count',
		'filter',
		'tags_input',
		'page_template',
	);

	/**
	 * Meta keys.
	 *
	 * @var string[]
	 */
	private array $meta_keys = array();

	/**
	 * Taxonomies
	 *
	 * @var WP_Taxonomy[]
	 */
	private array $taxonomies;

	/**
	 * Posts query for export.
	 *
	 * @var WP_Query
	 */
	private WP_Query $query;

	/**
	 * CSV_Generator constructor.
	 *
	 * @param string $post_type target post type.
	 */
	public function __construct( string $post_type ) {
		$this->post_type = $post_type;
	}

	/**
	 * @return string
	 */
	public function get_name(): string {
		$post_type = get_post_type_object( $this->post_type );

		return $post_type->label ?? '';
	}

	/**
	 * @return string
	 */
	public function get_post_type(): string {
		return $this->post_type;
	}

	/**
	 * Get the taxonomies related with the post type.
	 *
	 * @return string[]|WP_Taxonomy[]
	 */
	private function fetch_taxonomies(): array {
		$taxonomies = get_taxonomies( array(), 'objects' );
		return array_filter(
			$taxonomies,
			function ( WP_Taxonomy $taxonomy ) {
				return in_array( $this->post_type, $taxonomy->object_type, true );
			}
		);
	}

	/**
	 * Add custom field key for export.
	 *
	 * @param string $key meta key.
	 */
	public function append_meta_key( string $key ) {
		$this->meta_keys = array_merge( $this->meta_keys, array( $key ) );
	}

	/**
	 * Remove custom field key for export.
	 *
	 * @param string $key meta key.
	 */
	public function remove_meta_key( string $key ) {
		$this->meta_keys = array_values( array_diff( $this->meta_keys, array( $key ) ) );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function get_post_meta_fields( WP_Post $post ): array {
		$fields = array_combine(
			$this->meta_keys,
			array_map(
				function ( $key ) use ( $post ) {
					return get_post_meta( $post->ID, $key, true );
				},
				$this->meta_keys
			)
		);

		/**
		 * @param array $fields meta key and value.
		 * @param WP_Post $post post object.
		 *
		 * @deprecated 1.0.0
		 */
		$fields = apply_filters( 'csv_exporter_data_builder_get_post_meta_fields', $fields, $post );

		/**
		 * @param array $fields meta key and value.
		 * @param WP_Post $post post object.
		 *
		 * @deprecated 2.1.0
		 */
		$fields = apply_filters( 'simple_csv_exporter_created_data_builder_for_wp_posts_get_post_meta_fields', $fields, $post );

		/**
		 * @param array $fields meta key and value.
		 * @param WP_Post $post post object.
		 * @since 2.1.0
		 */
		return apply_filters( 'simple_csv_exporter_data_builder_for_wp_posts_get_post_meta_fields', $fields, $post );
	}

	/**
	 * Get term slugs.
	 *
	 * @param WP_Post $post
	 * @param string $taxonomy
	 *
	 * @return string[]
	 */
	private function get_the_term_slugs( WP_Post $post, string $taxonomy ): array {
		$terms = get_the_terms( $post, $taxonomy );
		if ( ! is_array( $terms ) ) {
			return array();
		}

		return array_map( 'urldecode', wp_list_pluck( $terms, 'slug' ) );
	}

	/**
	 * Get terms field.
	 *
	 * @param WP_Post $post
	 * @param string $taxonomy
	 *
	 * @return string
	 */
	private function get_the_terms_field( WP_Post $post, string $taxonomy ): string {
		$field = join( ',', $this->get_the_term_slugs( $post, $taxonomy ) );

		/**
		 * @since 2.2.0
		 * @param string $field
		 * @param WP_Post $post
		 * @param string $taxonomy
		 */
		return apply_filters( 'simple_csv_exporter_data_builder_for_wp_posts_get_the_terms_field', $field, $post, $taxonomy );
	}


	/**
	 * @param WP_Post $post
	 *
	 * @return string[]
	 */
	private function get_taxonomy_fields( WP_Post $post ): array {
		$columns = array(
			'post_category' => null,
			'tags_input'    => null,
		);
		foreach ( $this->taxonomies as $taxonomy ) {
			switch ( $taxonomy->name ) {
				case 'category':
					$columns['post_category'] = $this->get_the_terms_field( $post, 'category' );
					break;
				case 'post_tag':
					$columns['post_tags'] = $this->get_the_terms_field( $post, 'post_tag' );
					break;
				default:
					$columns[ 'tax_' . $taxonomy->name ] = $this->get_the_terms_field( $post, $taxonomy->name );
			}
		}

		return $columns;
	}

	/**
	 * Build export data.
	 */
	private function build() {
		$this->taxonomies = $this->fetch_taxonomies();

		$query = new WP_Query();
		$query->set( 'nopaging', true );
		$query->set( 'post_status', 'any' );
		$query->set( 'post_type', $this->post_type );

		/**
		 * Fires after the query variable object is created, but before the actual query is run.
		 *
		 * @param WP_Query $query
		 *
		 * @deprecated 2.1.0
		 */
		do_action( 'simple_csv_exporter_created_data_builder_for_wp_posts_pre_get_posts', $query );

		/**
		 * Fires after the query variable object is created, but before the actual query is run.
		 *
		 * @param WP_Query $query
		 *
		 * @since 2.1.0
		 */
		do_action( 'simple_csv_exporter_data_builder_for_wp_posts_pre_get_posts', $query );
		$query->get_posts();
		$this->query = $query;
	}

	/**
	 * Row generator.
	 *
	 * @return Generator
	 */
	public function rows(): Generator {
		if ( ! $this->post_type ) {
			return;
		}

		$this->build();

		while ( $this->query->have_posts() ) {
			$this->query->the_post();
			$post     = get_post();
			$row_data = array_merge(
				$post->to_array(),
				array(
					'post_thumbnail' => has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post ) : '',
					'post_author'    => $post->post_author ? get_userdata( $post->post_author )->user_login : null,
				),
				$this->get_field_mask(),
				$this->get_post_meta_fields( $post ),
				$this->get_taxonomy_fields( $post )
			);

			/**
			 * Filter for row data.
			 *
			 * @param array $row_data row data.
			 * @param WP_Post $post post object.
			 *
			 * @since 2.1.0
			 */
			$row_data = apply_filters( 'simple_csv_exporter_data_builder_for_wp_posts_row_data', $row_data, $post );

			// Note: 'foo' => null なものを、まとめて削除.
			yield array_filter(
				$row_data,
				function ( $fields ) {
					return is_string( $fields ) || is_numeric( $fields );
				}
			);
		}
	}
}
