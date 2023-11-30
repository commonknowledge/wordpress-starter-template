<?php

namespace CommonKnowledge\WordPress\SolidarityKnowsNoBorders;

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('solidarity-knows-no-borders', get_template_directory_uri() . '/style.css');
    wp_enqueue_script('solidarity-knows-no-borders', get_template_directory_uri() . '/scripts.js', null, true);
    wp_enqueue_script('mapbox', 'https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js');
    wp_enqueue_style('mapbox', 'https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css');
});





add_action('init', function () {
    register_block_pattern_category(
        'solidarity-knows-no-borders',
        array(
            'label' => __('Solidarity Knows No Borders', 'solidarity-knows-no-borders'),
        )
    );
});
