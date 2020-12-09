<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use WP_Post_Type;

/**
 * Class Init
 */
class Simple_CSV_Exporter {

	const POST_TYPE_TO_EXPORT = 'post_type_to_export';

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'current_screen', array( $this, 'export' ), 9999 );
	}

	/**
	 * Register export page.
	 */
	public function register() {
		add_management_page(
			esc_html__( 'CSV Export', 'simple-csv-exporter' ),
			esc_html__( 'CSV Export', 'simple-csv-exporter' ),
			'export',
			'csv_export',
			array(
				$this,
				'render',
			)
		);
	}

	public function export() {
		$screen = get_current_screen();

		if ( 'tools_page_csv_export' !== $screen->id ) {
			return;
		}

		if ( ! empty( $_POST ) && check_admin_referer( 'csv_export' ) ) {
			if ( ! current_user_can( 'export' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to export the content of this site.', 'default' ) );
			}

			$post_type_to_export = filter_input( INPUT_POST, self::POST_TYPE_TO_EXPORT, FILTER_SANITIZE_STRING );
			$file_name           = $post_type_to_export . '.csv';
			header( 'Content-Type: application/octet-stream' );
			header( "Content-Disposition: attachment; filename={$file_name}" );
			header( 'Content-Transfer-Encoding: binary' );

			$data = new Data_Builder( $post_type_to_export );

			/**
			 * Fires after data generator is created, but before export.
			 *
			 * @param Data_Builder $data
			 */
			do_action( 'simple_csv_exporter_pre_export', $data );

			$csv = new CSV_Writer( $data->get_rows(), 'php://output' );
			$csv->render();

			exit();
		}
	}

	public function render() {
		?>
		<div class="wrap">
			<h1>CSV Export</h1>
			<div id="csv_export" class="wrap">
				<form method="post">
					<?php wp_nonce_field( 'csv_export' ); ?>

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
					<label>


					</label>

					<?php submit_button( esc_html__( 'Export', 'simple-csv-exporter' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}
