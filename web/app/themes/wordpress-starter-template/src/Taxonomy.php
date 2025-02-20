<?php

namespace CommonKnowledge\WordpressStarterTemplate;

class Taxonomy
{
    public static function register()
    {
        /* Example */
        $name = "Example";
        $plural = "Examples";
        $slug = "example";
        $labels = [
            'name'              => $plural,
            'singular_name'     => $name,
            'search_items'      => "Search $plural",
            'all_items'         => "All $plural",
            'edit_item'         => "Edit $name",
            'update_item'       => "Update $name",
            'add_new_item'      => "Add New $name",
            'new_item_name'     => "New $name Name",
            'menu_name'         => $plural,
        ];

        $args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true, // Enables block editor (Gutenberg) and REST API
            'query_var'         => true,
            'rewrite'           => ['slug' => $slug],
        ];

        register_taxonomy($slug, ['post', 'page'], $args);
    }
}
