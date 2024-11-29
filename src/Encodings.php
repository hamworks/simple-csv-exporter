<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Encodings
 */
class Encodings {

	const ITEMS = array(
		array(
			'name'  => 'UTF-8',
			'value' => 'UTF-8',
		),
		array(
			'name'  => 'UTF8_WITH_BOM',
			'value' => 'UTF-8 with BOM',
		),
	);

	public static function get_items(): array {
		return self::ITEMS;
	}
}
