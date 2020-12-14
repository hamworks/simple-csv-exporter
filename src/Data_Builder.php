<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use Generator;
use Iterator;

/**
 * Data_Builder
 *
 * @abstract
 */
abstract class Data_Builder implements \IteratorAggregate {

	/**
	 * Post field keys to be removed.
	 *
	 * @var string[]
	 */
	protected $drop_columns = array();

	/**
	 * Alias for append_drop_column.
	 *
	 * @param string $column_name column name.
	 *
	 * @deprecated 1.0.0
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
	 * @param string $column_name column name.
	 *
	 * @deprecated 1.0.0
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
		return array_map(
			function () {
				return null;
			},
			array_flip( $this->drop_columns )
		);
	}

	/**
	 * @return Iterator
	 */
	abstract protected function generate_rows();

	/**
	 * @return Generator
	 */
	final public function getIterator(): Generator {
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

	/**
	 * Alias for getIterator.
	 *
	 * @deprecated 1.1.0
	 *
	 * @return Generator
	 */
	final public function get_rows(): Generator {
		return $this->getIterator();
	}
}
