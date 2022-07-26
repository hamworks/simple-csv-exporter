<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use Generator;
use IteratorAggregate;
use Traversable;

/**
 * Data_Builder
 *
 * @abstract
 */
abstract class Data_Builder implements IteratorAggregate {

	/**
	 * Post field keys to be removed.
	 *
	 * @var string[]
	 */
	protected array $drop_columns = array();


	/**
	 * Add column to be removed from the CSV.
	 *
	 * @param string $column_name column name.
	 */
	public function append_drop_column( string $column_name ) {
		$this->drop_columns = array_merge( $this->drop_columns, array( $column_name ) );
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
	 * @return Traversable
	 */
	abstract protected function rows(): Traversable;

	/**
	 * Iterator
	 *
	 * @return Generator
	 */
	final public function getIterator(): Generator {
		foreach ( $this->rows() as $row ) {
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
	 * Export file name.
	 *
	 * @return string
	 */
	abstract public function get_name(): string;
}
