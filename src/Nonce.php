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
		$this->key = $key;
	}

	/**
	 * Render nonce field
	 */
	public function render() {
		wp_nonce_field( $this->key );
	}

	/**
	 * @return bool
	 */
	public function verify(): bool {
		return ! empty( $_POST ) && check_admin_referer( $this->key );
	}
}
