<?php 
/**
 * Move/Copy Product for Woocommerce
 *
 * @link       aguyiknow.com.au
 * @since      1.0.1
 *
 * @package    Btsbiot
 * @subpackage Btsbiot/includes
*/


add_filter( 'bulk_actions-edit-product', 'bulk_action_move_product' );

function bulk_action_move_product( $bulk_array ) {
 	if( $sites = get_sites( array(
		// 'site__in' => array( 1,2,3 )
		'site__not_in' => get_current_blog_id(), // excluding current blog
		'number' => 10,))) {
		foreach( $sites as $site ) {
			$bulk_array['move_to_'.$site->blog_id] = 'Moved/Copy Product to &quot;' .$site->blogname . '&quot;';
		}
	}
	return $bulk_array;
}

add_filter( 'handle_bulk_actions-edit-product', 'mcfaith_bulk_action_multisite_handler', 10, 3 );

function mcfaith_bulk_action_multisite_handler( $redirect, $doaction, $object_ids ) {

	// we need query args to display correct admin notices
	$redirect = remove_query_arg( array( 'mcfaith_posts_moved', 'mcfaith_blogid' ), $redirect );

 	// our actions begin with "move_to_", so let's check if it is a target action
	if( strpos( $doaction, "move_to_" ) === 0 ) {
		$blog_id = str_replace( "move_to_", "", $doaction );

		foreach ( $object_ids as $product_id ) {

			// get the original post object as an array
			$post = get_post( $product_id, ARRAY_A );
			// if you need to apply terms (more info below the code)
			$product_terms = wp_get_object_terms($product_id, 'category', array('fields' => 'slugs'));
			// get all the post meta
			$data = get_post_custom($product_id);
			// empty ID field, to tell WordPress to create a new post, not update an existing one
			$post['ID'] = '';


			switch_to_blog( $blog_id );
			
			// insert the post
			$inserted_product_id = wp_insert_post($post); // insert the post
			// update post terms
			wp_set_object_terms($inserted_product_id, $product_terms, 'category', false);
			$taxonomies = get_object_taxonomies( $post['post_type'] );
			foreach ( $taxonomies as $taxonomy ) {
				$product_terms = wp_get_object_terms( $product_id, $taxonomy, array('fields' => 'slugs') );
			}
			foreach ( $taxonomies as $taxonomy ) {
				wp_set_object_terms( $inserted_product_id, $product_terms, $taxonomy, false );
			}
			// add post meta
			foreach ( $data as $key => $values) {
				// if you do not want weird redirects
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
			'mcfaith_product_moved' => count( $object_ids ),
			'mcfaith_blogid' => $blog_id
		), $redirect );

	}
	return $redirect;
}

// Notice
add_action( 'admin_notices', 'product_move_notice' );

function product_move_notice() {

	if( ! empty( $_REQUEST['mcfaith_posts_moved'] ) ) {

		// because I want to add blog names to notices
		$blog = get_blog_details( $_REQUEST['mcfaith_blogid'] );

		// depending on ho much posts were changed, make the message different
		printf( '<div id="message" class="updated notice is-dismissible"><p>' .
			_n( '%d Product has been Moved/Copy to &quot;%s&quot;.', '%d Product have been Moved/Copy to &quot;%s&quot;.', intval( $_REQUEST['mcfaith_posts_moved'] )
		) . '</p></div>', intval( $_REQUEST['mcfaith_posts_moved'] ), $blog->blogname );
	}
}
// End