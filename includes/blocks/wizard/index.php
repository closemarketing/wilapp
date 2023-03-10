<?php
/**
 * Block Subservicios
 *
 * @package    WordPress
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2022 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_image_size( 'subservicio', 220, 250, true );

add_action( 'rest_api_init', 'custom_api_get_all_posts' );
/**
 * API function child posts
 *
 * @return void
 */
function custom_api_get_all_posts() {
	register_rest_route(
		'close/v1',
		'/subservicios/(?P<id>\d+)',
		array(
			'methods'  => 'GET',
			'callback' => 'custom_api_get_all_posts_callback'
		)
	);
}

/**
 * Render API posts
 *
 * @param array $data
 * @return object
 */
function custom_api_get_all_posts_callback( $data ) {
	$posts_data     = array();
	$post_parent    = get_post_parent( $data['id'] );
	$post_parent_id = null === $post_parent ? $data['id'] : $post_parent->ID;
	$posts = get_posts( array(                    
		'numberposts' => -1,   
		'post_type'   => 'servicios',
		'post_parent' => $post_parent_id,
	));

	// Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
	foreach( $posts as $post ) {
		$posts_data[] = (object) array( 
			'id'    => $post->ID, 
			'link'  => get_permalink( $post->ID ),
			'title' => $post->post_title,
			'image' => get_the_post_thumbnail_url( $post->ID, 'subservicio' ),
		);
	}                
	return $posts_data;                   
}


add_action( 'init', 'close_news_block_init' );
/**
 * Register Blocks Subservicios
 *
 * @return void
 */
function close_news_block_init() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'close_news_render_callback',
		)
	);
}

/**
 * Render block in public
 *
 * @param array $block_attributes
 * @param array $block_content
 * @return html
 */
function close_news_render_callback( $block_attributes, $block_content ) {
	$block_classes = isset( $block_attributes['className'] )
		? $block_attributes['className'] . 'wp-block-close-subservicios'
		: 'wp-block-close-subservicios';

	$posts_subser = custom_api_get_all_posts_callback( array( 'id' => get_the_ID() ) );
	$render = '';
	if ( 0 < count( $posts_subser ) ) {
		$render     .= '<div class="' . esc_attr( $block_classes ) . '">';
		foreach ( $posts_subser as $post_subser ) {
			$render .= '<div class="item-subservicios"';
			if ( $post_subser->image ) {
				$render .= ' style="background-image:url(' . $post_subser->image . ');"';
			}
			$render .= '><a class="link" href="' . esc_url( $post_subser->link ) . '"></a>';
			$render .= '<h2 class="title-subservicios gb-headline">' . $post_subser->title . '</h2>';
			$render .= '</div>';
		}
		$render     .= '</div>';
	}

	return $render;
}