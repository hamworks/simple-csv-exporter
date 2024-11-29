# Simple CSV Exporter
Contributors:      Toro_Unit,hamworks  
Donate link:       https://www.paypal.me/torounit  
Tags:              CSV,export  
Requires at least: 5.8  
Tested up to:      6.7  
Requires PHP:      7.4  
Stable tag:        2.2.0
License:           GPLv2 or later  
License URI:       https://www.gnu.org/licenses/gpl-2.0.html  

Simple CSV Exporter.

## Description

Simple CSV Exporter. Exported CSV can be imported with [Really Simple CSV Importer](https://ja.wordpress.org/plugins/really-simple-csv-importer/). 

When you select a post type, the posts will be exported.

Github Repo: [https://github.com/hamworks/simple-csv-exporter](https://github.com/hamworks/simple-csv-exporter)


### Customize the data to be exported

Customize for column.

```php
use HAMWORKS\WP\Simple_CSV_Exporter\Data_Builder;
add_action( 'simple_csv_exporter_created_data_builder', 
	function ( Data_Builder $data ) {
		// Remove column.
		$data->append_drop_column( 'page_template' );
		// Add custom field column.
		$data->append_meta_key( 'my_meta_key' );
	}
);
```

Customize posts for export.

```php
add_action( 'simple_csv_exporter_data_builder_for_wp_posts_pre_get_posts', 
	function ( WP_Query $query ) {
		$query->set( 'order', 'ASC' );
	}
);
```

Data filter for metadata.

```php
add_filter( 'simple_csv_exporter_data_builder_for_wp_posts_get_post_meta_fields',
	function ( array $fields ) {
		foreach (
			array(
				'your_flag',
			) as $key
		) {
			if ( isset( $fields[ $key ] ) ) {
				$fields[ $key ] = ! empty( $fields[ $key ] ) ? 'TRUE' : 'FALSE';
			}
		}
		return $fields;
	}
);
```
Data filter for post.

```php
add_filter(
	'simple_csv_exporter_data_builder_for_wp_posts_row_data',
	function ( $row_data, $post ) {
		$row_data['permalink'] = get_permalink( $post );
		unset( $row_data['comment_status'] );
		return $row_data;
	},
	10,
	2
);
```

## Changelog

### 3.0.0
* Add support UTF-8 with BOM.

### 2.2.0
* Add `simple_csv_exporter_data_builder_for_wp_posts_get_the_terms_field` filter.

### 2.1.7
* Fix taxonomy export.

### 2.1.0
* Rename hooks.
* Add `simple_csv_exporter_data_builder_for_wp_posts_row_data` filter.

### 2.0.1
* Tested on WP 6.0
* Drop support WP 5.7 and PHP 7.3

### 2.0.0
* Use PHP-DI.
* Refactoring.

### 1.1.0
* Refactoring release.

### 1.0.0
* first release.

### 0.0.1
* internal release.

