<?php
/**
 * Ejo.Eco functions and definitions
 */

/* ### HOOK IT UP ########################################################## */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @return void
 */
add_action( 'after_setup_theme', function() {

	// Enqueue editor styles.
	add_editor_style( 'style.css' );

	// Enqueue scripts and styles to frontend
	add_action( 'wp_enqueue_scripts', 'gks_add_frontend_styles_and_scripts' );

	// Enqueue scripts and styles to the editor
	add_action( 'enqueue_block_editor_assets', 'gks_add_editor_styles_and_scripts' );

	// Add class to submit button for contact form 7
	add_filter( 'do_shortcode_tag', 'gks_wpcf7_add_submit_button_class', 10, 4 );

	// Limit excerpt length
	add_filter( 'excerpt_length', function($length) { return 32; } );

	// Use default featured image on special pages
	// add_filter( 'post_thumbnail_html', 'gks_show_default_image_on_special_pages', 21, 5 );
	
} );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @return void
 */
add_action( 'init', function() {

	// Add shortcode for rendering upcoming agenda items
	add_shortcode( 'agenda_upcoming', 'gks_shortcode_upcoming_agenda_items' );

} );

/* ### DEFINE FUNCTIONS ##################################################### */

/**
 * Enqueue styles.
 *
 * @return void
 */
function gks_add_frontend_styles_and_scripts() {

	// Get theme version (for cachebusting)
	$theme_version = wp_get_theme()->get( 'Version' );
	$version_string = is_string( $theme_version ) ? $theme_version : false;

	// Register theme stylesheet.
	wp_register_style(
		'gks-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$version_string
	);

	// Enqueue theme stylesheet
	wp_enqueue_style( 'gks-style' );

	// Register theme script	
	wp_register_script( 
		'gks-script-for-frontend',
        get_template_directory_uri() .'/assets/js/script-for-frontend.js', 
		array(),
		false, 
		$version_string
	);

	// Enqueue theme script
	wp_enqueue_script( 'gks-script-for-frontend' );
}


/**
 * Add scripts and styles (editor)
 */
function gks_add_editor_styles_and_scripts() {

	// Register theme stylesheet.
	$theme_version = wp_get_theme()->get( 'Version' );

	$version_string = is_string( $theme_version ) ? $theme_version : false;
	wp_register_style(
		'gks-style-for-editor',
		get_template_directory_uri() . '/assets/css/style-for-editor.css',
		array(),
		$version_string
	);

	// Enqueue theme stylesheet.
	wp_enqueue_style( 'gks-style-for-editor' );
}

/**
 * Contact form 7 - add class to submit button
 */
function gks_wpcf7_add_submit_button_class($output, $tag, $atts, $m) {

    if ($tag === 'contact-form-7') {
        $output = str_replace(
            'wpcf7-submit',
            'wpcf7-submit wp-block-button__link',
            $output
        );
    }

    return $output;
}


/**
 * Show default featured image on special pages like post-archive and 404
 */
function gks_show_default_image_on_special_pages( $html, $post_id, $post_thumbnail_id, $size, $attr ) {

	error_log('testttt');

	if ( is_404() ) {
		$default_thumbnail_id = get_option( 'dfi_image_id' ); // select the default thumb.
	}

	// Attributes can be a query string, parse that.
	if ( is_string( $attr ) ) {
		wp_parse_str( $attr, $attr );
	}

	if ( isset( $attr['class'] ) ) {
		// There already are classes, we trust those.
		$attr['class'] .= ' default-featured-img';
	} else {
		// No classes in the attributes, try to get them form the HTML.
		$img = new \WP_HTML_Tag_Processor( $html );
		if ( $img->next_tag() ) {
			$attr['class'] = trim( $img->get_attribute( 'class' ) . ' default-featured-img' );
		}
	}

	$html = wp_get_attachment_image( $default_thumbnail_id, $size, false, $attr );
	return apply_filters( 'dfi_thumbnail_html', $html, $post_id, $default_thumbnail_id, $size, $attr );
}

/**
 * Add 
 */
function gks_shortcode_upcoming_agenda_items( $atts = [], $content = null, $tag = '' ) {

	// normalize attribute keys, lowercase
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );

	// Start with empty output
	$output = '';

	// Get agenda post (page)
	$agenda_post = get_page_by_path('agenda');

	// Check if we have found the agenda page
	if ($agenda_post) {

		// // Get the blocks
	    $blocks = parse_blocks( $agenda_post->post_content );

		// Show only the header group and the first two events
		foreach ( $blocks as $block ) {

			// Agenda block name
			if ( str_contains($block['attrs']['className'],'agenda-overview') ) {

				/**
				 * Render only the first three inner blocks (agenda items including header)
				 */
				for ($i = 0; $i < 3; ++$i) {

					if ( isset($block['innerBlocks'][$i]) ) {
						$output .= render_block($block['innerBlocks'][$i]);
					}
				}

				/**
				 * Wrap output in agenda block wrapper
				 */
				if ($output) {
					$output = $block['innerContent'][0] . $output . end($block['innerContent']); 
				}
				
				// Exit loop, because we only want to check the first occurence of agenda-overview
				break;
			}
		}
	}	
	else {
		$output = '<p>Geen activiteiten gevonden</p>';
	}

	// return output
	return $output;

}


/* ### POLYFILLS ########################################################### */

// Polyfill `str_contains` function for versions < PHP 8 
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}