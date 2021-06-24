<?php 

add_filter( 'bulk_actions-edit-page', 'mcfaith_bulk_page_multisite_actions' );

function mcfaith_bulk_page_multisite_actions( $bulk_array ) {
 	if( $sites = get_sites( array(
		'site__not_in' => get_current_blog_id(),
		'number' => 10,))) {
		foreach( $sites as $site ) {
			$bulk_array['move_to_'.$site->blog_id] = 'Move/Copy Page to &quot;' .$site->blogname . '&quot;';
		}
	}
	return $bulk_array;
}

