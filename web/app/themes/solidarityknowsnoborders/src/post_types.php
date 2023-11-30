<?php

register_post_type(
    'resource',
    array(
        'labels'      => array(
            'name'          => 'Resources',
            'singular_name' => 'Resource',
        ),
        'public'      => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-book',
        'rewrite' => array('slug' => 'resource'),
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
        'taxonomies' => array("category", "resource_type")
    )
);

register_taxonomy('resource_format', ['resource'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'resource-type'],
    'labels'            => [
        'name'              => _x('Resource format', 'taxonomy general name'),
        'singular_name'     => _x('Resource format', 'taxonomy singular name'),
    ]
]);

register_taxonomy('resource_language', ['resource'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'resource-type'],
    'labels'            => [
        'name'              => _x('Resource language', 'taxonomy general name'),
        'singular_name'     => _x('Resource language', 'taxonomy singular name'),
    ]
]);
