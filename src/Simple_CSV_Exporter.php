<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use WP_Post_Type;

/**
 * Class Init
 */
class Simple_CSV_Exporter {

	const POST_TYPE_TO_EXPORT = 'post_type_to_export';
	const SLUG                = 'simple_csv_exporter';

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action(
			'admin_menu',
			function () {
				$this->register();
			}
		);
		add_action(
			'current_screen',
			function () {
				$this->do_export();
			},
			9999
		);
	}

	/**
	 * Register export page.
	 */
	private function register() {
		add_management_page(
			esc_html__( 'CSV Export', 'simple-csv-exporter' ),
			esc_html__( 'CSV Export', 'simple-csv-exporter' ),
			'export',
			self::SLUG,
			array(
				$this,
				'render',
			)
		);
	}

	private function do_export() {
		$screen = get_current_screen();

		if ( 'tools_page_' . self::SLUG !== $screen->id ) {
			return;
		}

		if ( ! empty( $_POST ) && check_admin_referer( self::SLUG ) ) {
			if ( ! current_user_can( 'export' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to export the content of this site.', 'default' ) );
			}

			$post_type_to_export = filter_input( INPUT_POST, self::POST_TYPE_TO_EXPORT, FILTER_SANITIZE_STRING );

			$this->send_headers( $post_type_to_export . '.csv' );

			$factory      = new Data_Builder_Factory();
			$data_builder = $factory->create( 'WordPress', array( 'post_type' => $post_type_to_export ) );

			$csv = new CSV_Writer( $data_builder->get_rows(), 'php://output' );
			$csv->render();

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

	/**
	 * Admin UI.
	 */
	public function render() {
		?>
		<div class="wrap">
			<h1>CSV Export</h1>
			<div id="csv_export" class="wrap">
				<form method="post">
					<?php wp_nonce_field( self::SLUG ); ?>
					<table>
						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( self::POST_TYPE_TO_EXPORT ); ?>">
									<?php esc_html_e( 'Export', 'simple-csv-exporter' ); ?>
								</label>
							</th>
							<td>
								<select
									id="<?php echo esc_attr( self::POST_TYPE_TO_EXPORT ); ?>"
									name="<?php echo esc_attr( self::POST_TYPE_TO_EXPORT ); ?>"
								>
									<?php
									/** @var WP_Post_Type $post_type */
									foreach ( get_post_types( array( 'can_export' => true ), 'objects' ) as $post_type ) :
										?>
										<option value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->label ); ?></option>
										<?php
									endforeach;
									?>
								</select>
							</td>
						</tr>
					</table>
					<?php submit_button( esc_html__( 'Export', 'simple-csv-exporter' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}
