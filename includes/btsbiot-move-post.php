<?php 

/**
 * Move/Copy Product Post for Woocommerce
 *
 * @link       aguyiknow.com.au
 * @since      1.0.0
 *
 * @package    Btsbiot
 * @subpackage Btsbiot/includes
 */

add_filter( 'bulk_actions-edit-product', 'mcfaith_product_bulk_action_multisite' );

function mcfaith_product_bulk_action_multisite( $bulk_array ) {
 	if( $sites = get_sites( array(
		'site__not_in' => get_current_blog_id(),
		'number' => 10,))) {
		foreach( $sites as $site ) {
			$bulk_array['move_to_'.$site->blog_id] = 'Copy Product to &quot;' .$site->blogname . '&quot;';
		}
	}
	return $bulk_array;
}

add_filter( 'handle_bulk_actions-edit-product', 'bulk_product_action_multisite_handler', 10, 3 );

function bulk_product_action_multisite_handler( $redirect, $doaction, $object_ids ) {
	$redirect = remove_query_arg( array( 'product_post_moved', 'mcfaith_blogid' ), $redirect );

	if( strpos( $doaction, "move_to_" ) === 0 ) {
		$blog_id = str_replace( "move_to_", "", $doaction );

		foreach ( $object_ids as $product_id ) {
			$post = get_post( $product_id, ARRAY_A );
			$taxonomies = get_object_taxonomies( $post['post_type'] );
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $product_id, $taxonomy, array('fields' => 'slugs') );
			}
			foreach ( $taxonomies as $taxonomy ) {
				wp_set_object_terms( $inserted_product_id, $post_terms, $taxonomy, false );
			}
			$data = get_post_custom($product_id);
			$post['ID'] = '';


			switch_to_blog( $blog_id );
			$inserted_product_id = wp_insert_post($post);
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $product_id, $taxonomy, array('fields' => 'slugs') );
			}
			foreach ( $taxonomies as $taxonomy ) {
				wp_set_object_terms( $inserted_product_id, $post_terms, $taxonomy, false );
			}
			foreach ( $data as $key => $values) {
				if( $key == '_wp_old_slug' ) {
					continue;
				}
				foreach ($values as $value) {
					add_post_meta( $inserted_product_id, $key, $value );
				}
			}
			restore_current_blog();
		}


		$redirect = add_query_arg( array(
			'product_post_moved' => count( $object_ids ),
			'mcfaith_blogid' => $blog_id
		), $redirect );

	}
	return $redirect;
}
add_action( 'admin_notices', 'bulk_move_product_multisite_notices' );

function bulk_move_product_multisite_notices() {

	if( ! empty( $_REQUEST['product_post_moved'] ) ) {
		$blog = get_blog_details( $_REQUEST['mcfaith_blogid'] );
		printf( '<div id="message" class="updated notice is-dismissible"><p>' .
			_n( '%d Product has been Moved/Copy to &quot;%s&quot;.', '%d Product have been Moved/Copy to &quot;%s&quot;.', intval( $_REQUEST['product_post_moved'] )
		) . '</p></div>', intval( $_REQUEST['product_post_moved'] ), $blog->blogname );
	}
}