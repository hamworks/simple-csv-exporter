<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

use WP_Post_Type;

/**
 * Admin UI.
 */
class Admin_UI {

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var string
	 */
	private $post_type_var_name;

	/**
	 * Admin_UI constructor.
	 *
	 * @param string $slug               Slug for admin page.
	 * @param string $post_type_var_name `name` attribute for post type select control.
	 * @param Nonce $nonce
	 */
	public function __construct( string $slug, string $post_type_var_name, Nonce $nonce ) {
		add_action(
			'admin_menu',
			function () {
				$this->register();
			}
		);
		$this->slug               = $slug;
		$this->nonce              = $nonce;
		$this->post_type_var_name = $post_type_var_name;
	}

	private function register() {
		add_management_page(
			esc_html__( 'CSV Export', 'simple-csv-exporter' ),
			esc_html__( 'CSV Export', 'simple-csv-exporter' ),
			'export',
			$this->slug,
			array(
				$this,
				'render',
			)
		);
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
					<?php $this->nonce->render(); ?>
					<table>
						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( $this->post_type_var_name ); ?>">
									<?php esc_html_e( 'Export', 'simple-csv-exporter' ); ?>
								</label>
							</th>
							<td>
								<select
									id="<?php echo esc_attr( $this->post_type_var_name ); ?>"
									name="<?php echo esc_attr( $this->post_type_var_name ); ?>"
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
