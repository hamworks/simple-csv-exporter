<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * CSV Writer with BOM.
 */
class CSV_Writer_With_BOM extends CSV_Writer {

	/**
	 * @param resource $file_pointer
	 *
	 * @return void
	 */
	protected function put( $file_pointer ) {
		// phpcs:ignore
		fwrite( $file_pointer, pack( 'C*', 0xEF, 0xBB, 0xBF ) );
		parent::put( $file_pointer );
	}
}
