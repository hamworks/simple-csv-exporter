<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use Generator;
use Iterator;

/**
 * Data_Builder
 *
 * @abstract
 *
 * @package HAMWORKS\WP\Simple_CSV_Exporter
 */
abstract class Data_Builder {

	/**
	 * Post field keys to be removed.
	 *
	 * @var string[]
	 */
	private $drop_columns = array(
		'post_date_gmt',
		'comment_status',
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
	);


	/**
	 * Alias for append_drop_column.
	 *
	 * @deprecated 1.0.0
	 *
	 * @param string $column_name column name.
	 */
	public function append_drop_field( string $column_name ) {
		$this->append_drop_column( $column_name );
	}

	/**
	 * Add column to be removed from the CSV.
	 *
	 * @param string $column_name column name.
	 */
	public function append_drop_column( string $column_name ) {
		$this->drop_columns = array_merge( $this->drop_columns, array( $column_name ) );
	}

	/**
	 * Alias for append_drop_column.
	 *
	 * @deprecated 1.0.0
	 *
	 * @param string $column_name column name.
	 */
	public function remove_drop_field( string $column_name ) {
		$this->remove_drop_column( $column_name );
	}

	/**
	 * Remove column to be removed from the CSV.
	 *
	 * @param string $column_name column name.
	 */
	public function remove_drop_column( string $column_name ) {
		$this->drop_columns = array_values( array_diff( $this->drop_columns, array( $column_name ) ) );
	}

	/**
	 * Get null fields.
	 *
	 * @return null[]
	 */
	protected function get_field_mask(): array {
		return array_map( '__return_null', array_flip( $this->drop_columns ) );
	}

	/**
	 * @return Iterator
	 */
	abstract protected function generate_rows();

	/**
	 * @return Generator
	 */
	final public function get_rows(): Generator {
		foreach ( $this->generate_rows() as $row ) {
			$masked_row = array_merge( $row, $this->get_field_mask() );
			// Note: 'foo' => null なものを、まとめて削除.
			yield array_filter(
				$masked_row,
				function ( $fields ) {
					return is_string( $fields ) || is_numeric( $fields );
				}
			);
		}
	}

}
