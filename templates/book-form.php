<?php

	$publishers = get_terms( 'books_publisher', [
		'hide_empty' => true
	] );

	$authors = get_terms( 'books_author', [
		'hide_empty' => true
	] );
?>

<form class="book-search-form" method="get" role="search">
	<div class="book-form-row">
		<div class="book-form-group">
			<label> <?php esc_html_e( 'Book Name', 'wp-publication-manager' ); ?> </label> <input type="text" name="book_name" value="<?php if( isset( $_GET['book_name' ] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_GET['book_name' ] ) ) ); } ?>" />
		</div>
		<div class="book-form-group">
			<label> <?php esc_html_e( 'Author', 'wp-publication-manager' ); ?> </label>
			<input type="text" id="book_author" name="book_author" value="<?php if( isset( $_GET['book_author' ] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_GET['book_author' ] ) ) ); } ?>" />
		</div>
	</div>

	<div class="book-form-row">
		<div class="book-form-group">
			<label> <?php esc_html_e( 'Publisher', 'wp-publication-manager' ); ?> </label>
			<select name="books_publisher">
				<option value=""></option>
				<?php
				foreach ( $publishers as $publisher ) {
						if( !empty( $getdata['books_publisher']  ) ) {
    						$books_publisher = sanitize_text_field( $getdata['books_publisher'] );
    					}

					printf('<option value="%s" %s > %s</option>',
							$publisher->term_id,
							( isset( $books_publisher ) &&  $publisher->term_id == $books_publisher ) ? 'selected' : '',
						 esc_html( $publisher->name )
						);
				} ?>
			</select>
		</div>

		<div class="book-form-group">
			<label> <?php esc_html_e( 'Rating', 'wp-publication-manager' ); ?> </label>
			<select name="books_rating">
				<option value=""></option>
				<option value="1"> 1 </option>
				<option value="2"> 2 </option>
				<option value="3"> 3 </option>
				<option value="4"> 4 </option>
				<option value="5"> 5 </option>
			</select>
		</div>
	</div>

<?php
$data       = publication_filters_prices();
$slider_val = array( $data['min'], $data['max'] );
$input_val  = $data['min'] . ':' . $data['max'];
?>
	<div class="book-form-row">
		<div class="book-form-group">
			<label> <?php esc_html_e( 'Price', 'wp-publication-manager' ); ?> </label>

			<input type="number" id="min_price" name="price[]" class="price-range-field" value="<?php echo esc_attr( $input_val ); ?>"/>
			<div id="slider-range"
			data-defaults="<?php echo esc_attr( htmlspecialchars( json_encode( $slider_val ) ) ); ?>"
			data-min="<?php echo esc_attr( $data['min'] ); ?>"
			data-max="<?php echo esc_attr( $data['max'] ); ?>"
			data-step="1" class="price-filter-range" name="rangeInput"></div>
	 		<input type="number" id="max_price" name="price[]" class="price-range-field" value="<?php echo esc_attr( $input_val ); ?>"/>
        </div>
    </div>

	<div class="book-form-row">
		<div class="book-form-group">
			<input type="hidden" name="post_type" value="books">
			<button type="submit" class="book-submit-btn"> <?php esc_html_e( 'Search', 'wp-publication-manager' ); ?> </button>
		</div>
	</div>
</form>