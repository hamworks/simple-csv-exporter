<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Class Exporter
 *
 * @package HAMWORKS\WP\Simple_CSV_Exporter
 */
class Exporter {
	/**
	 * @var Nonce
	 */
	private Nonce $nonce;

	/**
	 * @var Data_Builder
	 */
	private Data_Builder $data_builder;

	/**
	 * @var CSV_Writer
	 */
	private CSV_Writer $csv_writer;

	/**
	 * Exporter
	 *
	 * @param Nonce $nonce
	 * @param Data_Builder $data_builder
	 * @param CSV_Writer $csv_writer
	 */
	public function __construct( Nonce $nonce, Data_Builder $data_builder, CSV_Writer $csv_writer ) {
		$this->nonce        = $nonce;
		$this->data_builder = $data_builder;
		$this->csv_writer   = $csv_writer;

		$this->process_request();
	}

	private function process_request() {
		if ( ! $this->nonce->verify() ) {
			return;
		}

		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to export the content of this site.', 'default' ) );
		}

		if ( $this->data_builder ) {
			$this->do_export();
		} else {
			wp_die( 'No data!' );
		}
	}

	private function do_export() {
		$this->send_headers( $this->data_builder->get_name() . '.csv' );
		$this->csv_writer->set_rows( $this->data_builder );
		$this->csv_writer->set_file_name( 'php://output' );

		try {
			$this->csv_writer->render();
		} catch ( \Exception $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		exit();
	}

	/**
	 * Response headers.
	 *
	 * @param string $file_name
	 */
	private function send_headers( string $file_name ) {
		header( 'Content-Type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename={$file_name}" );
		header( 'Content-Transfer-Encoding: binary' );
	}
}
