<?php
namespace WPUM;

class Admin {

	public function __construct() {
        add_action( 'admin_menu', [$this, 'admin_menu'] );
		add_action( 'init', [ $this, 'add_post_type' ] );
        add_filter( 'enter_title_here', [ $this, 'change_title_text' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ]);
        add_action( 'save_post', [ $this, 'save_post' ], 10, 2  );

        add_filter( 'manage_books_posts_columns', [ $this, 'books_custom_columns' ] );
        add_action( 'manage_books_posts_custom_column',[ $this, 'custom_books_column_content' ], 10, 2 );
        add_filter( 'manage_edit-books_sortable_columns', [ $this, 'books_sortable_columns' ] );

        add_filter('template_include', [ $this, 'books_custom_template' ] );
        add_action( 'pre_get_posts', [ $this, 'books_pre_get_posts' ] );
	}

    public function admin_menu() {
        $capability = 'manage_options';
        add_menu_page( __( 'WP Publication Manager', 'wp-publication-manager' ), __( 'WP Publication Manager', '' ), $capability, 'wp-publication-manager', '' );
        add_submenu_page( 'wp-publication-manager', __( 'Books', 'wp-publication-manager' ), __( 'Books', 'wp-publication-manager' ), $capability, 'edit.php?post_type=books' );
        add_submenu_page( 'wp-publication-manager', __( 'Authors', 'wp-publication-manager' ), __( 'Authors', 'wp-publication-manager' ), $capability, 'edit-tags.php?taxonomy=books_author&post_type=books' );
        add_submenu_page( 'wp-publication-manager', __( 'Publishers', 'wp-publication-manager' ), __( 'Publishers', 'wp-publication-manager' ), $capability, 'edit-tags.php?taxonomy=books_publisher&post_type=books' );
        remove_submenu_page('wp-publication-manager','wp-publication-manager');
    }

    public function books_pre_get_posts( $query ) {
        if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == 'books'  ) {


            if( !empty( $_GET['book_name'] ) ) {
                $book_name = sanitize_text_field( wp_unslash( $_GET['book_name'] ) );
                $query->set( 's', $book_name );
            }

            $tax_queries = [];

            if ( !empty( $_GET['book_author' ] ) ) {
                    $book_author = sanitize_text_field( wp_unslash( $_GET['book_author'] ) );

                    $tax_queries[] = [
                        'taxonomy' => 'books_author',
                        'field' => 'slug',
                        'terms' => $book_author,
                        'include_children' => true
                    ];
            }

            if ( !empty( $_GET['books_publisher' ] ) ) {
                $books_publisher = sanitize_text_field( wp_unslash( $_GET['books_publisher'] ) );

                $tax_queries[] = [
                    'taxonomy'         => 'books_publisher',
                    'field'            => 'term_id',
                    'terms'            => $books_publisher,
                    'include_children' => true
                ];
            }

            $count_tax_queries = count( $tax_queries );

            if ( $count_tax_queries ) {
                $tax_query = ( $count_tax_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $tax_queries ) : $tax_queries;
                $query->set( 'tax_query', $tax_query );
            }


            $meta_queries = [];

            if ( !empty( $_GET['books_rating' ] ) ) {
                $books_rating = sanitize_text_field( wp_unslash( $_GET['books_rating'] ) );

                $meta_queries[] = array(
                    'key'       => 'book_rating',
                    'value'     => $books_rating,
                    'compare'   => '='
                );
            }

            if ( isset( $_GET['price'] ) && !empty( $_GET['price'] ) ) {

                $p = wpum_clean( wp_unslash( $_GET['price'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

                $price = array_filter( $p );


                if ( $n = count( $price ) ) {
                    if ( 2 == $n ) {
                        $meta_queries[] = array(
                            'key' => 'book_price',
                            'value' => array_map('intval', $price),
                            'type' => 'NUMERIC',
                            'compare' => 'BETWEEN'
                        );
                    } else {
                        if (empty($price[0])) {
                            $meta_queries[] = array(
                                'key' => 'book_price',
                                'value' => (int)$price[1],
                                'type' => 'NUMERIC',
                                'compare' => '<='
                            );
                        } else {
                            $meta_queries[] = array(
                                'key' => 'book_price',
                                'value' => (int)$price[0],
                                'type' => 'NUMERIC',
                                'compare' => '>='
                            );
                        }
                    }
                }
            }// end price

            $count_meta_queries = count( $meta_queries );

            if( $count_meta_queries ) {
                $meta_query = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries;
                $query->set( 'meta_query', $meta_query );
            }
        }

        return $query;
    }


    public function books_custom_template( $template ) {
        global $wp_query;

        $post_type = get_query_var('post_type');

        if( $post_type == 'books' ){
            if ( file_exists( WPUM_PATH . '/templates/books.php' ) ) {
                return WPUM_PATH . '/templates/books.php';
            }
        }

        return $template;
    }


	public function add_post_type() {
        $capability = 'manage_options';

        register_post_type( 'books', [
            'label'           => __( 'Book', 'wp-publication-manager' ),
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => false, //false,
            'capability_type' => 'post',
            'hierarchical'    => false,
            'query_var'       => false,
            'supports'        => ['title', 'editor'],
            'capabilities'    => [
                'publish_posts'       => $capability,
                'edit_posts'          => $capability,
                'edit_others_posts'   => $capability,
                'delete_posts'        => $capability,
                'delete_others_posts' => $capability,
                'read_private_posts'  => $capability,
                'edit_post'           => $capability,
                'delete_post'         => $capability,
                'read_post'           => $capability,
            ],
            'labels' => [
                'name'               => __( 'Books', 'wp-publication-manager' ),
                'singular_name'      => __( 'Book', 'wp-publication-manager' ),
                'menu_name'          => __( 'Books', 'wp-publication-manager' ),
                'add_new'            => __( 'Add Book', 'wp-publication-manager' ),
                'add_new_item'       => __( 'Add New Book', 'wp-publication-manager' ),
                'edit'               => __( 'Edit', 'wp-publication-manager' ),
                'edit_item'          => __( 'Edit Book', 'wp-publication-manager' ),
                'new_item'           => __( 'New Book', 'wp-publication-manager' ),
                'view'               => __( 'View Book', 'wp-publication-manager' ),
                'view_item'          => __( 'View Book', 'wp-publication-manager' ),
                'search_items'       => __( 'Search Book', 'wp-publication-manager' ),
                'not_found'          => __( 'No Book Found', 'wp-publication-manager' ),
                'not_found_in_trash' => __( 'No Book Found in Trash', 'wp-publication-manager' ),
                'parent'             => __( 'Parent Book', 'wp-publication-manager' ),
            ],
        ] );

        $authorArgs = array(
            'labels'            => array(
                'name'                       => __('Authors', 'wp-publication-manager'),
                'singular_name'              => __('Category', 'wp-publication-manager'),
                'menu_name'                  => __('Authors', 'wp-publication-manager'),
                'edit_item'                  => __('Edit Category', 'wp-publication-manager'),
                'update_item'                => __('Update Category', 'wp-publication-manager'),
                'add_new_item'               => __('Add New Category', 'wp-publication-manager'),
                'new_item_name'              => __('New Category Name', 'wp-publication-manager'),
                'parent_item'                => __('Parent Category', 'wp-publication-manager'),
                'parent_item_colon'          => __('Parent Category:', 'wp-publication-manager'),
                'all_items'                  => __('All Authors', 'wp-publication-manager'),
                'search_items'               => __('Search Authors', 'wp-publication-manager'),
                'popular_items'              => __('Popular Authors', 'wp-publication-manager'),
                'separate_items_with_commas' => __('Separate authors with commas','wp-publication-manager'),
                'add_or_remove_items'        => __('Add or remove authors', 'wp-publication-manager'),
                'choose_from_most_used'      => __('Choose from the most used  authors','wp-publication-manager'),
                'not_found'                  => __('No authors found.', 'wp-publication-manager'),
            ),
            'public'            => true,
            'show_in_nav_menus' => false,
            'show_ui'           => true,
            'show_tagcloud'     => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'query_var'         => true,
        );

        register_taxonomy( 'books_author', 'books', $authorArgs );

        $publisherArgs = array(
            'labels'            => array(
                'name'                       => __('Publishers', 'wp-publication-manager'),
                'singular_name'              => __('Publisher', 'wp-publication-manager'),
                'menu_name'                  => __('Publishers', 'wp-publication-manager'),
                'edit_item'                  => __('Edit Publisher', 'wp-publication-manager'),
                'update_item'                => __('Update Publisher', 'wp-publication-manager'),
                'add_new_item'               => __('Add New Publisher', 'wp-publication-manager'),
                'new_item_name'              => __('New Publisher Name', 'wp-publication-manager'),
                'parent_item'                => __('Parent Publisher', 'wp-publication-manager'),
                'parent_item_colon'          => __('Parent Publisher', 'wp-publication-manager'),
                'all_items'                  => __('All Publishers', 'wp-publication-manager'),
                'search_items'               => __('Search Publishers', 'wp-publication-manager'),
                'popular_items'              => __('Popular Publishers', 'wp-publication-manager'),
                'separate_items_with_commas' => __('Separate Publishers with commas','wp-publication-manager'),
                'add_or_remove_items'        => __('Add or remove Publishers', 'wp-publication-manager'),
                'choose_from_most_used'      => __('Choose from the most used Publishers','wp-publication-manager'),
                'not_found'                  => __('No Publishers found.', 'wp-publication-manager'),
            ),
            'public'            => true,
            'show_in_nav_menus' => false,
            'show_ui'           => true,
            'show_tagcloud'     => true,
            'hierarchical'      => false,
            'show_admin_column' => true,
            'query_var'         => true,
        );

        register_taxonomy( 'books_publisher', 'books', $publisherArgs );
    }

    public function change_title_text( $title ){
        $screen = get_current_screen();

        if  ( 'books' == $screen->post_type ) {
            $title = 'Enter Book Title';
        }

        return $title;
    }

    public function books_custom_columns( $columns ) {
        $columns['book_price'] = __( 'Price', 'wp-publication-manager' );
        $columns['book_rating'] = __( 'Rating', 'wp-publication-manager' );

        return $columns;
    }

    public function custom_books_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'book_price':
                echo esc_html_e( get_post_meta( $post_id, 'book_price', true ), 'wp-publication-manager' );
                break;
            case 'book_rating':
                echo esc_html_e( get_post_meta( $post_id, 'book_rating', true ), 'wp-publication-manager' );
                break;
        }
    }


    public function books_sortable_columns( $columns ) {
        $columns['book_price'] = 'book_price';
        $columns['book_rating'] = 'book_rating';

        return $columns;
    }

    public function add_meta_boxes() {
        add_meta_box('book_meta_information', __('Book\'s Information', 'wp-publication-manager'), [ $this, 'books_meta_information' ], 'books', 'normal', 'high');
    }

    public function books_meta_information() {
        global $post;
        $price = get_post_meta( $post->ID, 'book_price', true );
        $rating = get_post_meta( $post->ID, 'book_rating', true );
        wp_nonce_field( basename(__FILE__), "wpum_meta_nonce" );
        $price  =  !empty( $price ) ? $price : '';
        $rating =  !empty( $rating ) ? $rating : '';
     ?>
    <table>
        <tbody>
            <tr>
                <td>
                    <label>  
                        <?php esc_html_e('Price:','wp-publication-manager'); ?> 
                        <input type="text" name="book_price" value="<?php esc_attr_e( $price, 'wp-publication-manager' ); ?>" class="publication-meta"/> 
                    </label>
                </td>
            </tr>

            <tr>
                <td>
                    <label>  <?php esc_html_e('Rating:','wp-publication-manager' ); ?>
                        <input type="text" name="book_rating" value="<?php esc_attr_e( $price, 'wp-publication-manager' ); ?>"  class="publication-meta"/> 
                    </label>
                </td>
            </tr>
    </table>
    <?php }

    public function save_post( $post_id, $post ) {
        if ( !isset( $_POST["wpum_meta_nonce"] ) || !wp_verify_nonce( sanitize_key( $_POST["wpum_meta_nonce"] ) , basename(__FILE__) ) )
            return $post_id;

        if( !current_user_can( "edit_post", $post_id ) )
            return $post_id;

        if( defined("DOING_AUTOSAVE") && DOING_AUTOSAVE )
            return $post_id;

        if( 'books' == $post->post_type ) {
            if( isset( $_POST['book_price'] ) ) {
                $book_price = sanitize_text_field( wp_unslash( $_POST['book_price'] )  );
                update_post_meta( $post_id, 'book_price', $book_price );
            }

            if( isset( $_POST['book_rating'] ) ) {
                $book_rating = sanitize_text_field( wp_unslash( $_POST['book_rating'] ) );
                update_post_meta( $post_id, 'book_rating', $book_rating );
            }
        }
    }
}