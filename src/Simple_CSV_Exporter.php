<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Class Init
 */
class Simple_CSV_Exporter {

	const POST_TYPE_TO_EXPORT = 'post_type_to_export';
	const SLUG                = 'simple_csv_exporter';

	/**
	 * @var Nonce
	 */
	private $nonce;

	/**
	 * @var Admin_UI
	 */
	private $admin_ui;

	/**
	 * @var Exporter
	 */
	private $exporter;

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->nonce    = new Nonce( self::SLUG );
		$this->admin_ui = new Admin_UI( self::SLUG, self::POST_TYPE_TO_EXPORT, $this->nonce );
		$this->exporter = new Exporter( self::SLUG, self::POST_TYPE_TO_EXPORT, $this->nonce );
	}


}
