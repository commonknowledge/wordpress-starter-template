<?php
use Carbon_Fields\Block;
use Carbon_Fields\Field;


add_action('carbon_fields_register_fields', 'future_natures_related_posts_block');
function future_natures_related_posts_block() {
    Block::make( __( 'Related Posts' ) )
    ->set_description(__('Displays related posts to the current post'))
    ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
        global $post;

        $query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 3,
            'cat' => $post->post_category,
            'post__not_in' => [$post->ID],
            'orderby' => 'rand'
        ]);

        $posts = $query->get_posts();
        ?>
        <?php if ( $query->have_posts() ) : ?>
            <section class="flex w-full md:max-w-4xl md:mx-auto p-4 justify-between items-center related-posts-header">
                <h4 class="text-xl">More from The Commons</h4>
                <a href="/the-commons" class="uppercase text-xs">View all</a>
            </section>

            <?php future_natures_create_grid($posts) ?>
        <?php endif;
    });
}?>