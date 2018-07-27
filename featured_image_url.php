<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://theluckycoder.net
 * @since             1.0.0
 * @package           featured_image_url
 *
 * @wordpress-plugin
 * Plugin Name:       Featured Image URL
 * Plugin URI:        https://github.com/TheLuckyCoder/REST-API-Featured-Image-URL
 * Description:       Adds a featured_image_url field to the REST API posts response
 * Version:           1.0.0
 * Author:            The Lucky Coder
 * Author URI:        http://theluckycoder.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       featured_image_url
 */

add_action('init', 'rest_api_featured_images_urls_init');

/**
 * Register the featured_image_url field to all public post types
 * that support post thumbnails.
 *
 * @since  1.0.0
 */
function rest_api_featured_images_urls_init() {

	$post_types = get_post_types(array('public' => true), 'objects');

	foreach ($post_types as $post_type) {

		$post_type_name = $post_type->name;
		$show_in_rest = (isset($post_type->show_in_rest) && $post_type->show_in_rest) ? true : false;
		$supports_thumbnail = post_type_supports($post_type_name, 'thumbnail');

		// Only proceed if the post type is set to be accessible over the REST API
		// and supports featured images.
		if ($show_in_rest && $supports_thumbnail) {
			register_rest_field( $post_type_name,
				'featured_image_url',
				array(
					'get_callback' => 'rest_api_featured_images_urls_get_field',
					'schema'       => null,
				)
			);
		}
	}
}

/**
 * Return the featured_image_url field.
 *
 * @since   1.0.0
 *
 * @param   object  $object      The response object.
 * @param   string  $field_name  The name of the field to add.
 * @param   object  $request     The WP_REST_Request object.
 *
 * @return  object|null
 */
function rest_api_featured_images_urls_get_field($object, $field_name, $request) {

	// Only proceed if the post has a featured image.
	if (empty($object['featured_media'])) {
		return null;
	}

	$image_id = (int) $object['featured_media'];

	$image = get_post($image_id);

	if (!$image) {
		return null;
	}

	return apply_filters('rest_api_featured_image_url', wp_get_attachment_url($image_id), $image_id);
}
