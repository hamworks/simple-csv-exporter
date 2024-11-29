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
	protected string $file_name;

	/**
	 * @var iterable
	 */
	protected iterable $rows;

	/**
	 * CSV_Builder constructor.
	 *
	 * @param iterable|null $rows
	 * @param string|null $file_name
	 */
	public function __construct( iterable $rows = null, string $file_name = null ) {
		if ( $rows ) {
			$this->rows = $rows;
		}
		if ( $file_name ) {
			$this->file_name = $file_name;
		}
	}

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
		$this->write();
	}

	/**
	 * Put Contents
	 *
	 * @param resource $file_pointer
	 *
	 * @return void
	 */
	protected function put( $file_pointer ) {
		$header_rendered = false;
		foreach ( $this->rows as $row ) {
			if ( ! $header_rendered ) {
				fputcsv( $file_pointer, array_keys( $row ) );
				$header_rendered = true;
			}
			fputcsv( $file_pointer, $row );
		}
	}

	/**
	 * Write CSV
	 *
	 * @throws \Exception
	 */
	public function write() {
		// phpcs:ignore
		$file_pointer = fopen( $this->file_name, 'w' );
		if ( ! $file_pointer ) {
			throw new \Exception( 'Could not open file.' );
		}
		$this->put( $file_pointer );
		// phpcs:ignore
		fclose( $file_pointer );
	}
}
