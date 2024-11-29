<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Class Request
 */
class Request {

	public const POST_TYPE_TO_EXPORT = 'post_type_to_export';
	public const ENCODING            = 'encoding';

	/**
	 * @return string
	 */
	public function get_post_type_to_export(): string {
		return filter_input( INPUT_POST, self::POST_TYPE_TO_EXPORT, FILTER_SANITIZE_SPECIAL_CHARS ) ?? '';
	}

	/**
	 * @return string
	 */
	public function get_encoding(): string {
		return filter_input( INPUT_POST, self::ENCODING, FILTER_SANITIZE_SPECIAL_CHARS ) ?? '';
	}
}
