<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Class Exporter
 *
 * @package HAMWORKS\WP\Simple_CSV_Exporter
 */
class Exporter {
	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var Data_Builder
	 */
	private $data_builder;

	/**
	 * Exporter
	 *
	 * @param string $slug Slug for admin page.
	 * @param Nonce $nonce
	 * @param Data_Builder $data_builder
	 */
	public function __construct( string $slug, Nonce $nonce, Data_Builder $data_builder ) {
		$this->slug         = $slug;
		$this->nonce        = $nonce;
		$this->data_builder = $data_builder;

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
		$csv = new CSV_Writer( $this->data_builder, 'php://output' );
		$csv->render();
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
