<?php
/**
 * Shortcodes init
 *
 * Init main shortcodes, and add a few others such as recent products.
 *
 * @author 		WooThemes
 * @category 	Shortcodes
 * @package 	WooCommerce/Shortcodes
 * @version     1.7.0
 */

/** Cart shortcode */
include_once('shortcode-cart.php');

/** Checkout shortcode */
include_once('shortcode-checkout.php');

/** My Account shortcode */
include_once('shortcode-my_account.php');

/** Order tracking shortcode */
include_once('shortcode-order_tracking.php');

/** Lost password shortcode */
include_once( 'shortcode-lost_password.php' );

/** Pay shortcode */
include_once('shortcode-pay.php');

/** Thanks shortcode */
include_once('shortcode-thankyou.php');


/**
 * List products in a category shortcode
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_product_category( $atts ){
	global $woocommerce_loop;

  	if ( empty( $atts ) ) return;

	extract( shortcode_atts( array(
		'per_page' 		=> '12',
		'columns' 		=> '4',
	  	'orderby'   	=> 'title',
	  	'order'     	=> 'asc',
	  	'category'		=> ''
		), $atts ) );

	if ( ! $category ) return;

  	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'orderby' => $orderby,
		'order' => $order,
		'posts_per_page' => $per_page,
		'meta_query' => array(
			array(
				'key' => '_visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN'
			)
		),
		'tax_query' => array(
	    	array(
		    	'taxonomy' => 'product_cat',
				'terms' => array( esc_attr($category) ),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);

  	ob_start();

	$products = new WP_Query( $args );

	$woocommerce_loop['columns'] = $columns;

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}


/**
 * List all (or limited) product categories
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_product_categories( $atts ) {
	global $woocommerce_loop;

	extract( shortcode_atts( array (
		'number'     => null,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'columns' 	 => '4',
		'hide_empty' => 1
		), $atts ) );

	if ( isset( $atts[ 'ids' ] ) ) {
		$ids = explode( ',', $atts[ 'ids' ] );
	  	$ids = array_map( 'trim', $ids );
	} else {
		$ids = array();
	}

	$hide_empty = ( $hide_empty == true || $hide_empty == 1 ) ? 1 : 0;

  	$args = array(
  		'number'     => $number,
  		'orderby'    => $orderby,
  		'order'      => $order,
  		'hide_empty' => $hide_empty,
		'include'    => $ids
	);

  	$product_categories = get_terms( 'product_cat', $args );

  	$woocommerce_loop['columns'] = $columns;

  	ob_start();

  	// Reset loop/columns globals when starting a new loop
	$woocommerce_loop['loop'] = $woocommerce_loop['column'] = '';

  	if ( $product_categories ) {

  		woocommerce_product_loop_start();

		foreach ( $product_categories as $category ) {

			woocommerce_get_template( 'content-product_cat.php', array(
				'category' => $category
			) );

		}

		woocommerce_product_loop_end();

	}

	woocommerce_reset_loop();

	return ob_get_clean();
}


/**
 * Recent Products shortcode
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_recent_products( $atts ) {

	global $woocommerce_loop;

	extract(shortcode_atts(array(
		'per_page' 	=> '12',
		'columns' 	=> '4',
		'orderby' => 'date',
		'order' => 'desc'
	), $atts));

	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order,
		'meta_query' => array(
			array(
				'key' => '_visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN'
			)
		)
	);

	ob_start();

	$products = new WP_Query( $args );

	$woocommerce_loop['columns'] = $columns;

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}


/**
 * List multiple products shortcode
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_products( $atts ) {
	global $woocommerce_loop;

  	if (empty($atts)) return;

	extract(shortcode_atts(array(
		'columns' 	=> '4',
	  	'orderby'   => 'title',
	  	'order'     => 'asc'
		), $atts));

  	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'orderby' => $orderby,
		'order' => $order,
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' 		=> '_visibility',
				'value' 	=> array('catalog', 'visible'),
				'compare' 	=> 'IN'
			)
		)
	);

	if(isset($atts['skus'])){
		$skus = explode(',', $atts['skus']);
	  	$skus = array_map('trim', $skus);
    	$args['meta_query'][] = array(
      		'key' 		=> '_sku',
      		'value' 	=> $skus,
      		'compare' 	=> 'IN'
    	);
  	}

	if(isset($atts['ids'])){
		$ids = explode(',', $atts['ids']);
	  	$ids = array_map('trim', $ids);
    	$args['post__in'] = $ids;
	}

  	ob_start();

	$products = new WP_Query( $args );

	$woocommerce_loop['columns'] = $columns;

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}


/**
 * Display a single prodcut
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_product( $atts ) {
  	if (empty($atts)) return;

  	$args = array(
    	'post_type' => 'product',
    	'posts_per_page' => 1,
    	'no_found_rows' => 1,
    	'post_status' => 'publish',
    	'meta_query' => array(
			array(
				'key' => '_visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN'
			)
		)
  	);

  	if(isset($atts['sku'])){
    	$args['meta_query'][] = array(
      		'key' => '_sku',
      		'value' => $atts['sku'],
      		'compare' => '='
    	);
  	}

  	if(isset($atts['id'])){
    	$args['p'] = $atts['id'];
  	}

  	ob_start();

	$products = new WP_Query( $args );

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}


/**
 * Display a single prodcut price + cart button
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_product_add_to_cart( $atts ) {
  	if (empty($atts)) return;

  	global $wpdb, $woocommerce;

  	if (!isset($atts['style'])) $atts['style'] = 'border:4px solid #ccc; padding: 12px;';

  	if ($atts['id']) :
  		$product_data = get_post( $atts['id'] );
	elseif ($atts['sku']) :
		$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $atts['sku']));
		$product_data = get_post( $product_id );
	else :
		return;
	endif;

	if ($product_data->post_type=='product') {

		$product = $woocommerce->setup_product_data( $product_data );

		ob_start();
		?>
		<p class="product" style="<?php echo $atts['style']; ?>">

			<?php echo $product->get_price_html(); ?>

			<?php woocommerce_template_loop_add_to_cart(); ?>

		</p><?php

		return ob_get_clean();

	} elseif ($product_data->post_type=='product_variation') {

		$product = new WC_Product( $product_data->post_parent );

		$GLOBALS['product'] = $product;

		$variation = new WC_Product_Variation( $product_data->ID );

		ob_start();
		?>
		<p class="product product-variation" style="<?php echo $atts['style']; ?>">

			<?php echo $product->get_price_html(); ?>

			<?php

			$link 	= $product->add_to_cart_url();

			$label 	= apply_filters('add_to_cart_text', __('Add to cart', 'woocommerce'));

			$link = add_query_arg( 'variation_id', $variation->variation_id, $link );

			foreach ($variation->variation_data as $key => $data) {
				if ($data) $link = add_query_arg( $key, $data, $link );
			}

			printf('<a href="%s" rel="nofollow" data-product_id="%s" class="button add_to_cart_button product_type_%s">%s</a>', esc_url( $link ), $product->id, $product->product_type, $label);

			?>

		</p><?php

		return ob_get_clean();

	}
}


/**
 * Get the add to cart URL for a product
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_product_add_to_cart_url( $atts ){
  	if (empty($atts)) return;

  	global $wpdb;

  	if ($atts['id']) :
  		$product_data = get_post( $atts['id'] );
	elseif ($atts['sku']) :
		$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $atts['sku']));
		$product_data = get_post( $product_id );
	else :
		return;
	endif;

	if ($product_data->post_type!=='product') return;

	$_product = new WC_Product( $product_data->ID );

	return esc_url( $_product->add_to_cart_url() );
}

/**
 * List all products on sale
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_sale_products( $atts ){
    global $woocommerce_loop, $woocommerce;

    extract( shortcode_atts( array(
        'per_page'      => '12',
        'columns'       => '4',
        'orderby'       => 'title',
        'order'         => 'asc'
        ), $atts ) );

	// Get products on sale
	if ( false === ( $product_ids_on_sale = get_transient( 'wc_products_onsale' ) ) ) {

		$meta_query = array();

	    $meta_query[] = array(
	    	'key' => '_sale_price',
	        'value' 	=> 0,
			'compare' 	=> '>',
			'type'		=> 'NUMERIC'
	    );

		$on_sale = get_posts(array(
			'post_type' 		=> array('product', 'product_variation'),
			'posts_per_page' 	=> -1,
			'post_status' 		=> 'publish',
			'meta_query' 		=> $meta_query,
			'fields' 			=> 'id=>parent'
		));

		$product_ids 	= array_keys( $on_sale );
		$parent_ids		= array_values( $on_sale );

		// Check for scheduled sales which have not started
		foreach ( $product_ids as $key => $id )
			if ( get_post_meta( $id, '_sale_price_dates_from', true ) > current_time('timestamp') )
				unset( $product_ids[ $key ] );

		$product_ids_on_sale = array_unique( array_merge( $product_ids, $parent_ids ) );

		set_transient( 'wc_products_onsale', $product_ids_on_sale );
	}
		
	$product_ids_on_sale[] = 0;

	$meta_query = array();
	$meta_query[] = $woocommerce->query->visibility_meta_query();
    $meta_query[] = $woocommerce->query->stock_status_meta_query();

	$args = array(
		'posts_per_page'=> $per_page,
		'orderby' 		=> $orderby,
        'order' 		=> $order,
		'no_found_rows' => 1,
		'post_status' 	=> 'publish',
		'post_type' 	=> 'product',
		'orderby' 		=> 'date',
		'order' 		=> 'ASC',
		'meta_query' 	=> $meta_query,
		'post__in'		=> $product_ids_on_sale
	);

  	ob_start();

	$products = new WP_Query( $args );
	
	$woocommerce_loop['columns'] = $columns;

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * List best selling products on sale
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_best_selling_products( $atts ){
    global $woocommerce_loop;

    extract( shortcode_atts( array(
        'per_page'      => '12',
        'columns'       => '4'
        ), $atts ) );

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'ignore_sticky_posts'   => 1,
        'posts_per_page' => $per_page,
        'meta_key' 		 => 'total_sales',
    	'orderby' 		 => 'meta_value',
        'meta_query' => array(
            array(
                'key' => '_visibility',
                'value' => array( 'catalog', 'visible' ),
                'compare' => 'IN'
            )
        )
    );

  	ob_start();
  	
	$products = new WP_Query( $args );
	
	$woocommerce_loop['columns'] = $columns;

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * List top rated products on sale
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_top_rated_products( $atts ){
    global $woocommerce_loop;

    extract( shortcode_atts( array(
        'per_page'      => '12',
        'columns'       => '4',
        'orderby'       => 'title',
        'order'         => 'asc'
        ), $atts ) );

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'ignore_sticky_posts'   => 1,
        'orderby' => $orderby,
        'order' => $order,
        'posts_per_page' => $per_page,
        'meta_query' => array(
            array(
                'key' => '_visibility',
                'value' => array('catalog', 'visible'),
                'compare' => 'IN'
            )
        )
    );

  	ob_start();
  	
  	add_filter( 'posts_clauses', 'woocommerce_order_by_rating_post_clauses' );

	$products = new WP_Query( $args );
	
	remove_filter( 'posts_clauses', 'woocommerce_order_by_rating_post_clauses' );
	
	$woocommerce_loop['columns'] = $columns;

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * Output featured products
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_featured_products( $atts ) {

	global $woocommerce_loop;

	extract(shortcode_atts(array(
		'per_page' 	=> '12',
		'columns' 	=> '4',
		'orderby' => 'date',
		'order' => 'desc'
	), $atts));

	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order,
		'meta_query' => array(
			array(
				'key' => '_visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN'
			),
			array(
				'key' => '_featured',
				'value' => 'yes'
			)
		)
	);

	ob_start();

	$products = new WP_Query( $args );

	$woocommerce_loop['columns'] = $columns;

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}


/**
 * Show a single product page
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_product_page_shortcode( $atts ) {
  	if (empty($atts)) return;

	if (!$atts['id'] && !$atts['sku']) return;

  	$args = array(
    	'posts_per_page' 	=> 1,
    	'post_type'	=> 'product',
    	'post_status' => 'publish',
    	'ignore_sticky_posts'	=> 1,
    	'no_found_rows' => 1
  	);

  	if(isset($atts['sku'])){
    	$args['meta_query'][] = array(
      		'key' => '_sku',
      		'value' => $atts['sku'],
      		'compare' => '='
    	);
  	}

  	if(isset($atts['id'])){
    	$args['p'] = $atts['id'];
  	}

  	$single_product = new WP_Query( $args );

  	ob_start();

	while ( $single_product->have_posts() ) : $single_product->the_post(); wp_enqueue_script( 'wc-single-product' ); ?>

		<div class="single-product">

			<?php woocommerce_get_template_part( 'content', 'single-product' ); ?>

		</div>

	<?php endwhile; // end of the loop.

	wp_reset_postdata();

	return ob_get_clean();
}


/**
 * Show messages
 *
 * @access public
 * @param array $atts
 * @return string
 */
function woocommerce_messages_shortcode() {
	ob_start();

	woocommerce_show_messages();

	return ob_get_clean();
}

/**
 * woocommerce_order_by_rating_post_clauses function.
 * 
 * @access public
 * @param mixed $args
 * @return void
 */
function woocommerce_order_by_rating_post_clauses( $args ) {

	global $wpdb;

	$args['where'] .= " AND $wpdb->commentmeta.meta_key = 'rating' ";

	$args['join'] .= "
		LEFT JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
		LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
	";

	$args['orderby'] = "$wpdb->commentmeta.meta_value DESC";

	$args['groupby'] = "$wpdb->posts.ID";

	return $args;
}

/**
 * Shortcode creation
 **/
add_shortcode('product', 'woocommerce_product');
add_shortcode('product_page', 'woocommerce_product_page_shortcode');
add_shortcode('product_category', 'woocommerce_product_category');
add_shortcode('product_categories', 'woocommerce_product_categories');
add_shortcode('add_to_cart', 'woocommerce_product_add_to_cart');
add_shortcode('add_to_cart_url', 'woocommerce_product_add_to_cart_url');
add_shortcode('products', 'woocommerce_products');
add_shortcode('recent_products', 'woocommerce_recent_products');
add_shortcode('sale_products', 'woocommerce_sale_products');
add_shortcode('best_selling_products', 'woocommerce_best_selling_products');
add_shortcode('top_rated_products', 'woocommerce_top_rated_products');
add_shortcode('featured_products', 'woocommerce_featured_products');
add_shortcode('woocommerce_cart', 'get_woocommerce_cart');
add_shortcode('woocommerce_checkout', 'get_woocommerce_checkout');
add_shortcode('woocommerce_order_tracking', 'get_woocommerce_order_tracking');
add_shortcode('woocommerce_my_account', 'get_woocommerce_my_account');
add_shortcode('woocommerce_edit_address', 'get_woocommerce_edit_address');
add_shortcode('woocommerce_change_password', 'get_woocommerce_change_password');
add_shortcode('woocommerce_lost_password', 'get_woocommerce_lost_password');
add_shortcode('woocommerce_view_order', 'get_woocommerce_view_order');
add_shortcode('woocommerce_pay', 'get_woocommerce_pay');
add_shortcode('woocommerce_thankyou', 'get_woocommerce_thankyou');
add_shortcode('woocommerce_messages', 'woocommerce_messages_shortcode');