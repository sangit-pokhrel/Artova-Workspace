<?php

if ( ! function_exists( 'pillar_blog_get_post_categories' ) ) {
	/**
	* Get post categories by by term id
	* 
	* @return array
	*/
	function pillar_blog_get_post_categories(){

		$terms = get_terms( array(
		    'taxonomy' => 'category',
		    'hide_empty' => true,
		) );

		if( empty($terms) || !is_array( $terms ) ){
			return array();
		}

		$data = array();
		foreach ( $terms as $key => $value) {
			$term_id = absint( $value->term_id );
			$data[$term_id] =  esc_html( $value->name );
		}
		return $data;

	}
}