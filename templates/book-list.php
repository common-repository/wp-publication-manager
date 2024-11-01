<?php
	$paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

	$args = array(
		'post_type'      => 'books',
		'post_status'    => 'publish',
		'posts_per_page' => !empty( get_option('posts_per_page') ) ? get_option('posts_per_page') : 10,
		'paged'	=> $paged
	);

	$book_query = new WP_Query( $args );
?>


<?php if( $book_query->have_posts() ) :?>
<table>
	<thead>
		<td> <?php esc_html_e( 'No', 'wp-publication-manager' ); ?> </td>
		<td> <?php esc_html_e( 'Book Name', 'wp-publication-manager' ); ?> </td>
		<td> <?php esc_html_e( 'Price', 'wp-publication-manager' ); ?> </td>
		<td> <?php esc_html_e( 'Author', 'wp-publication-manager' ); ?> </td>
		<td> <?php esc_html_e( 'Publisher', 'wp-publication-manager' ); ?> </td>
		<td> <?php esc_html_e( 'Rating', 'wp-publication-manager' ); ?> </td>
		<td></td>
	</thead>
	<tbody>

			<?php	while ( $book_query->have_posts() ) : $book_query->the_post();
					global $post;
					$price      = get_post_meta( $post->ID, 'book_price', true );
					$rating     = get_post_meta( $post->ID, 'book_rating', true );
					$authors    = wp_list_pluck( wp_get_object_terms( $post->ID, 'books_author'), 'name' );
					$publishers = wp_list_pluck( wp_get_object_terms( $post->ID, 'books_publisher'), 'name' );
		?>
					<tr>
					  	<td></td>
						<td itemprop ="title"> <?php the_title(); ?> </td>
						<td itemprop ="price"> <?php esc_html_e( $price, 'wp-publication-manager' ); ?> </td>
						<td itemprop ="author"> <?php esc_html_e( implode( ',', $authors ), 'wp-publication-manager' ); ?> </td>
						<td itemprop ="publisher"> <?php esc_html_e( implode( ',', $publishers ), 'wp-publication-manager' ); ?> </td>
						<td itemprop ="description">
		<div class="br-wrapper br-theme-css-stars">
            <div class="br-widget">
            	<?php for( $i=1; $i<= $rating; $i++ ) {
            		printf('<a href="#"> </a>');
            	} ?>
            </div>
        </div>
        <?php // esc_html_e( $rating, 'wp-publication-manager' ); ?> </td>
					</tr>
		<?php  endwhile;
            wp_reset_postdata(); ?>
	</tbody>
</table>


<div class="book-form-row">
	<div class="book-pagination">
		<?php
			$big = 999999999;
			echo paginate_links( array(  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			    'format' => '?paged=%#%',
			    'current' => max( 1, get_query_var('paged') ),
			    'total' => $book_query->max_num_pages
			) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
</div>

<?php else :
	esc_html_e( 'There is no Book found', 'wp-publication-manager');
endif;  wp_reset_postdata(); ?>
