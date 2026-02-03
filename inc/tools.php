<?php

/**
 * Indique si le post est un custom post type
 */
if ( ! function_exists( 'is_custom_post_type' ) ) {
	function is_custom_post_type( $post = NULL ) {
		$all_custom_post_types = get_post_types( array ( '_builtin' => false ) );

		if ( empty ( $all_custom_post_types ) )
			return false;

		if ( is_archive() && !$post ) {
			$current_post_type = get_query_var( 'post_type' );
		} else {
			$current_post_type = get_post_type( $post );
		}

		if ( !$current_post_type )
			return false;

		$custom_types = array_keys( $all_custom_post_types );

		return in_array( $current_post_type, $custom_types );
	}
}


/**
 * Retourne de type de taxonomy
 */
if ( ! function_exists( 'get_taxonomy_type' ) ) {
	function get_taxonomy_type() {
		if ( is_category() ) {
			return 'category';
		}
		if ( is_tag() ) {
			return 'post_tag';
		}
		if ( is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->taxonomy ) ) {
				return $term->taxonomy;
			}
		}
		return false;
	}
}
