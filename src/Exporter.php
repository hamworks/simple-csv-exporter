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
	 * @var string
	 */
	private $post_type_var_name;
	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * Exporter
	 *
	 * @param string $slug               Slug for admin page.
	 * @param string $post_type_var_name `name` attribute for post type select control.
	 * @param Nonce $nonce
	 */
	public function __construct( string $slug, string $post_type_var_name, Nonce $nonce ) {
		add_action(
			'current_screen',
			function () {
				$this->do_export();
			},
			9999
		);
		$this->slug               = $slug;
		$this->post_type_var_name = $post_type_var_name;
		$this->nonce              = $nonce;
	}


	private function do_export() {
		$screen = get_current_screen();

		if ( 'tools_page_' . $this->slug !== $screen->id ) {
			return;
		}

		if ( $this->nonce->verify() ) {
			if ( ! current_user_can( 'export' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to export the content of this site.', 'default' ) );
			}

			$post_type_to_export = filter_input( INPUT_POST, $this->post_type_var_name, FILTER_SANITIZE_STRING );

			$this->send_headers( $post_type_to_export . '.csv' );

			$factory      = new Data_Builder_Factory();
			$data_builder = $factory->create( 'WordPress', array( 'post_type' => $post_type_to_export ) );

			if ( $data_builder ) {
				$csv = new CSV_Writer( $data_builder, 'php://output' );
				$csv->render();
			} else {
				wp_die( 'No data!' );
			}

			exit();
		}
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
