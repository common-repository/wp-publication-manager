<?php

function publication_filters_prices() {
	global $wpdb;
	global $wp_query;

	$args = $wp_query->query_vars;
	$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
	$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

	if ( ! is_post_type_archive( 'books' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
		$tax_query[] = array(
			'taxonomy' => $args['taxonomy'],
			'terms'    => array( $args['term'] ),
			'field'    => 'slug',
		);
	}

	foreach ( $meta_query + $tax_query as $key => $query ) {
		if ( ! empty( $query['book_price'] ) ) {
			unset( $meta_query[ $key ] );
		}
	}

	$meta_query = new WP_Meta_Query( $meta_query );
	$tax_query  = new WP_Tax_Query( $tax_query );

	$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
	$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

	$sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min, max( CEILING( price_meta.meta_value ) ) as max FROM {$wpdb->posts} ";
	$sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];

	$sql .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', array( 'books' ) ) ) . "')
		AND {$wpdb->posts}.post_status = 'publish'
		AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', array( 'book_price' )  ) ) . "')
		AND price_meta.meta_value > '' ";

	$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

	$price = $wpdb->get_row( $sql, ARRAY_A );

	return $price; // WPCS: unprepared SQL ok.
}

function wpum_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wpum_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}