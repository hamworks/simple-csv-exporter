<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter;

/**
 * Encodings
 */
class Encodings {

	public const UTF8 = array(
		'name'  => 'UTF-8',
		'value' => 'UTF-8',
	);

	public const UTF8_WITH_BOM = array(
		'name'  => 'UTF8_WITH_BOM',
		'value' => 'UTF-8 with BOM',
	);

	const ITEMS = array(
		self::UTF8,
		self::UTF8_WITH_BOM,
	);

	public static function get_items(): array {
		return self::ITEMS;
	}
}
