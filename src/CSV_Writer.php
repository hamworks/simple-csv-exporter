<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Class CSV_Generator
 */
class CSV_Writer {

	/**
	 * Default file name.
	 *
	 * @var string
	 */
	private $file_name;

	/**
	 * @var iterable
	 */
	private $rows;

	/**
	 * CSV_Builder constructor.
	 *
	 * @param iterable $rows
	 * @param string $file_name
	 */
	public function __construct( iterable $rows, string $file_name ) {
		$this->rows      = $rows;
		$this->file_name = $file_name;
	}

	/**
	 * Render.
	 */
	public function render() {
		$this->write( $this->rows );
	}


	/**
	 * Render CSV to Standard IO.
	 *
	 * @param iterable $data
	 */
	public function write( iterable $data ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$file_pointer = fopen( $this->file_name, 'w' );

		$header_rendered = false;
		foreach ( $data as $row ) {
			if ( ! $header_rendered ) {
				fputcsv( $file_pointer, array_keys( $row ) );
				$header_rendered = true;
			}
			fputcsv( $file_pointer, $row );
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $file_pointer );
	}

}
