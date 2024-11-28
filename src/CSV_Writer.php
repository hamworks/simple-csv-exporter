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
	private string $file_name;

	/**
	 * @var iterable
	 */
	private iterable $rows;

	/**
	 * @param string $file_name
	 */
	public function set_file_name( string $file_name ): void {
		$this->file_name = $file_name;
	}

	/**
	 * @param iterable $rows
	 */
	public function set_rows( iterable $rows ): void {
		$this->rows = $rows;
	}

	/**
	 * Render.
	 *
	 * @throws \Exception
	 */
	public function render() {
		if ( ! $this->file_name || ! $this->rows ) {
			throw new \Exception( 'File name or rows are not set.' );
		}
		$this->write( $this->rows );
	}


	/**
	 * Render CSV to Standard IO.
	 *
	 * @param iterable $data
	 */
	public function write( iterable $data ) {
		// phpcs:ignore
		$file_pointer = fopen( $this->file_name, 'w' );

		$header_rendered = false;
		foreach ( $data as $row ) {
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
