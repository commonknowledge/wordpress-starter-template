<?php

/**
 * Theme Functions
 */

use CommonKnowledge\WordpressStarterTemplate\ThemeOptions;

// Classes in src/ are loaded by the project's Composer autoloader.
ThemeOptions::register();

add_action('init', function () {
    // Register every custom block in blocks/, each defined by its block.json
    foreach (glob(get_template_directory() . '/blocks/*/block.json') as $blockJson) {
        register_block_type(dirname($blockJson));
    }
});

add_action('wp_enqueue_scripts', function () {
    $VERSION = (WP_ENV !== "production") ? time() : "1.0.0";

    $fileRoot = WP_ENV === "development" ? "http://localhost:9000" : (get_template_directory_uri() . "/build");

    // The build lists the WordPress scripts main.js imports, such as
    // wp-dom-ready. The dev server also writes this file to build/.
    $assetFile = get_template_directory() . '/build/main.asset.php';
    $dependencies = file_exists($assetFile) ? (require $assetFile)['dependencies'] : [];

    wp_enqueue_style('theme-custom-style', $fileRoot . '/main.css', [], $VERSION, 'all');
    wp_enqueue_script('theme-custom-script', $fileRoot . '/main.js', $dependencies, $VERSION, true);
});

add_action('wp_footer', function () {
    /**
     * Output required config and session information to the footer for use by front-end JavaScript.
     */
    $data = [];
    echo '<script type="application/json" id="wordpress-config">' . json_encode($data, JSON_PRETTY_PRINT) . '</script>';
});
