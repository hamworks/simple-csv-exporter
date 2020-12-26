<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Nonce management.
 */
class Nonce {

	/**
	 * @var string
	 */
	private $key;

	public function __construct( string $key ) {
		$this->key = '_' . $key . '_nonce';
	}

	/**
	 * Render nonce field
	 *
	 * @param string $action
	 */
	public function render( $action = 'export' ) {
		wp_nonce_field( $action, $this->key );
	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 */
	public function verify( $action = 'export' ): bool {
		return ! empty( $_POST ) && check_admin_referer( $action, $this->key );
	}
}
