<?php
/**
 * Eko functions and definitions
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
	add_action( 'wp_enqueue_scripts', 'eko_add_frontend_styles_and_scripts' );

	// Enqueue scripts and styles to the editor
	add_action( 'enqueue_block_editor_assets', 'eko_add_editor_styles_and_scripts' );

	// Add class to submit button for contact form 7
	add_filter( 'do_shortcode_tag', 'eko_wpcf7_add_submit_button_class', 10, 4 );

	// Limit excerpt length
	add_filter( 'excerpt_length', function($length) { return 32; } );

} );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @return void
 */
add_action( 'init', function() {

} );

/* ### DEFINE FUNCTIONS ##################################################### */

/**
 * Enqueue styles.
 *
 * @return void
 */
function eko_add_frontend_styles_and_scripts() {

	// Get theme version (for cachebusting)
	$theme_version = wp_get_theme()->get( 'Version' );
	$version_string = is_string( $theme_version ) ? $theme_version : false;

	// Register theme stylesheet.
	wp_register_style(
		'eko-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$version_string
	);

	// Enqueue theme stylesheet
	wp_enqueue_style( 'eko-style' );

	// Register theme script	
	wp_register_script( 
		'eko-script-for-frontend',
        get_template_directory_uri() .'/assets/js/script-for-frontend.js', 
		array(),
		false, 
		$version_string
	);

	// Enqueue theme script
	wp_enqueue_script( 'eko-script-for-frontend' );
}


/**
 * Add scripts and styles (editor)
 */
function eko_add_editor_styles_and_scripts() {

	// Register theme stylesheet.
	$theme_version = wp_get_theme()->get( 'Version' );

	$version_string = is_string( $theme_version ) ? $theme_version : false;
	wp_register_style(
		'eko-style-for-editor',
		get_template_directory_uri() . '/assets/css/style-for-editor.css',
		array(),
		$version_string
	);

	// Enqueue theme stylesheet.
	wp_enqueue_style( 'eko-style-for-editor' );
}

/**
 * Contact form 7 - add class to submit button
 */
function eko_wpcf7_add_submit_button_class($output, $tag, $atts, $m) {

    if ($tag === 'contact-form-7') {
        $output = str_replace(
            'wpcf7-submit',
            'wpcf7-submit wp-block-button__link',
            $output
        );
    }

    return $output;
}

/* ### POLYFILLS ########################################################### */

// Polyfill `str_contains` function for versions < PHP 8 
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}