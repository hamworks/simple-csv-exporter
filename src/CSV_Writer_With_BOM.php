<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * CSV Writer with BOM.
 */
class CSV_Writer_With_BOM extends CSV_Writer {
	/**
	 * Render CSV to Standard IO.
	 *
	 * @param iterable $data
	 */
	public function write() {
		// phpcs:ignore
		$file_pointer = fopen( $this->file_name, 'w' );
		// phpcs:ignore
		fwrite( $file_pointer, pack( 'C*', 0xEF, 0xBB, 0xBF ) );
		$header_rendered = false;
		foreach ( $this->rows as $row ) {
			if ( ! $header_rendered ) {
				fputcsv( $file_pointer, array_keys( $row ) );
				$header_rendered = true;
			}
			fputcsv( $file_pointer, $row );
		}
		// phpcs:ignore
		fclose( $file_pointer );
	}
}
