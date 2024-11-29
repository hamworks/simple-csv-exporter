<?php

namespace HAMWORKS\WP\Simple_CSV_Exporter\Tests;

use HAMWORKS\WP\Simple_CSV_Exporter\CSV_Writer;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CSV_Writer.
 */
class CSV_Writer_Test extends TestCase {

	/**
	 * @test
	 */
	public function test_write_stream() {
		$data = array(
			array(
				'a' => 1,
				'b' => 2,
			),
			array(
				'a' => 'A',
				'b' => 'B',
			),
			array(
				'a' => 'あいうえお',
				'b' => 'あ い う え お',
			),
		);

		$expect = <<<CSV
a,b
1,2
A,B
あいうえお,"あ い う え お"

CSV;

		$this->expectOutputString( $expect );
		$csv = new CSV_Writer();
		$csv->set_file_name( 'php://output' );
		$csv->set_rows( $data );
		$csv->write( $data );
	}

	/**
	 * @test
	 */
	public function test_write_file() {
		$data = array(
			array(
				'a' => 1,
				'b' => 2,
			),
			array(
				'a' => 'A',
				'b' => 'B',
			),
			array(
				'a' => 'あいうえお',
				'b' => 'あ い う え お',
			),
		);

		$expect = <<<CSV
a,b
1,2
A,B
あいうえお,"あ い う え お"

CSV;

		$csv = new CSV_Writer();
		$csv->set_rows( $data );
		$csv->set_file_name( '/tmp/test.csv' );
		$csv->write( $data );
		$this->assertFileExists( '/tmp/test.csv' );
		$this->assertIsReadable( '/tmp/test.csv' );
		$this->assertStringEqualsFile( '/tmp/test.csv', $expect );
	}
}
