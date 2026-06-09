<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use CommonKnowledge\WordpressStarterTemplate\API;
use CommonKnowledge\WordpressStarterTemplate\Blocks;
use CommonKnowledge\WordpressStarterTemplate\Taxonomy;

/**
 * Theme Functions
 */

add_action('carbon_fields_register_fields', function () {
    Container::make('theme_options', 'Theme Options')
        ->add_fields(array(
            Field::make('text', 'crb_text', 'Text Field'),
        ));
    Blocks::register();
});

add_action('after_setup_theme', function () {
    \Carbon_Fields\Carbon_Fields::boot();
});

add_action('init', function () {
    Taxonomy::register();
});

add_action('rest_api_init', function () {
    API::register();
});


add_action('wp_enqueue_scripts', function () {
    $VERSION = (WP_ENV !== "production") ? time() : "1.0.0";

    $fileRoot = WP_ENV === "development" ? "http://localhost:9000" : (get_template_directory_uri() . "/build");

    wp_enqueue_style('theme-custom-style', $fileRoot . '/main.css', [], $VERSION, 'all');
    wp_enqueue_script('theme-custom-script', $fileRoot . '/main.js', ['jquery'], $VERSION, true);
});

add_action('wp_footer', function () {
    /**
     * Output required config and session information to the footer for use by front-end JavaScript.
     */
    $data = [];
    echo '<script type="application/json" id="wordpress-config">' . json_encode($data, JSON_PRETTY_PRINT) . '</script>';
});
