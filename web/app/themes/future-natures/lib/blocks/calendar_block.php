<?php
use Carbon_Fields\Block;
use Carbon_Fields\Field;

function get_post_thumbnail_alt()  {
    $post_thumbnail_id = get_post_thumbnail_id();
    $post_thumbnail_alt = get_post_meta( $post_thumbnail_id, '_wp_attachment_image_alt', true );
    return $post_thumbnail_alt;
}

function calendar_block() {
	Block::make( __( 'calendar_block' ) )
	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
        $query  = new WP_Query ( array(
            'post_type'      => 'event',
            'meta_query' => array(
                array(
                    'key'     => 'event_start',
                    'compare' => '>',
                    'value' => date('Y-m-d H:i:s')
                )),
            'orderby' => 'meta_value',
            'meta_key' => '_event_start',
            'order' => 'ASC'
        ));

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
               $query->the_post();
               $event_url = carbon_get_post_meta(get_the_ID(), 'url');
               $event_start = carbon_get_post_meta(get_the_ID(), 'event_start');
               $event_end = carbon_get_post_meta(get_the_ID(), 'event_end');
               $event_venue = carbon_get_post_meta(get_the_ID(), 'venue');
               $subtitle = carbon_get_post_meta(get_the_ID(), 'subtitle');
               $event_online = carbon_get_post_meta(get_the_ID(), 'online');
               $event_city = carbon_get_post_meta(get_the_ID(), 'city');
               $event_country = carbon_get_post_meta(get_the_ID(), 'country');
               $posttags = get_the_tags();

 
    
              ?>  
                    <div class="wp-block-query">
                        <ul class="wp-block-post-template">
                            <li
                            class="wp-block-post event type-event status-publish has-post-thumbnail"
                            >
                            <a
                                class="event"
                                href="<?= $event_url ?>"
                                target="”_blank”"
                                rel="”noopener"
                                noreferrer”=""
                            >
                            </a>
                            <div class="wp-block-columns">
                                <a
                                class="event"
                                href="<?= $event_url ?>"
                                target="”_blank”"
                                rel="”noopener"
                                noreferrer”=""
                                >
                                </a>
                                <div class="wp-block-column">
                                <a
                                    class="event"
                                    href="<?= $event_url ?>"
                                    target="”_blank”"
                                    rel="”noopener"
                                    noreferrer”=""
                                >
                                    <div class="wp-block-columns">
                                    <div
                                        class="wp-block-column event-featured-image"
                                        style="flex-basis:33.33%"
                                    >
                                        <figure class="wp-block-post-featured-image">
                                        <img
                                            width="1745"
                                            height="1190"
                                            src="<?=the_post_thumbnail_url()?>"
                                            class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                            alt="<?=get_post_thumbnail_alt()?>"
                                            loading="lazy"
                                            srcset="
                                            <?=the_post_thumbnail_url()?>               1745w,
                                            <?=the_post_thumbnail_url()?>-300x205.png    300w,
                                            <?=the_post_thumbnail_url()?>-1024x698.png  1024w,
                                            <?=the_post_thumbnail_url()?>-768x524.png    768w,
                                            <?=the_post_thumbnail_url()?>-1536x1047.png 1536w
                                            "
                                            sizes="(max-width: 1745px) 100vw, 1745px"
                                        />
                                        </figure>
                                        <div>
                                        <div class="wp-block-column" style="flex-basis:66.66%">
                                            <div
                                            class="is-vertical is-content-justification-space-between is-nowrap wp-block-group event-title-metadata-column"
                                            style="margin-top:0"
                                            >
                                            <div
                                                class="is-vertical wp-block-group event-item-title"
                                                style="flex-wrap:wrap"
                                            >
                                                <h2 class="wp-block-post-title">
                                                <?=get_the_title()?>
                                                </h2>
                                                <svg
                                                class="event-link"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 20 20"
                                                fill="none"
                                                >
                                                <path
                                                    d="M2.19981 19.7L0.799805 18.3L16.5998 2.5H7.33314V0.5H19.9998V13.1667H17.9998V3.9L2.19981 19.7Z"
                                                    fill="#1C1B1F"
                                                ></path>
                                                </svg>
                                            </div>

                                            <div class="subtitle">
                                                <?= $subtitle ?>
                                            </div>

                                            <div
                                                class="is-vertical is-content-justification-left wp-block-group event-item-metadata"
                                                style="flex-direction:column"
                                            >
                                                <?php
                                        if ($event_online) {
                                    ?>
                                                <p>Online</p>
                                                <?php	
                                }
                            ?>
                                                <span><?= $event_venue ?></span>
                                                <span><?= $event_city ?></span>
                                                <span><?= $event_country ?></span>
                                                <?php
                                        
                                if (!$event_end) {
                                    ?>
                                                <?php
                                            $date_start = date_create($event_start);
                                            echo date_format($date_start,"d M Y");
                                            ?>
                                                <?php	
                                }

                                else {
                                    ?>
                                                <?php
                                            $date_start = date_create($event_start);
                                            echo date_format($date_start,"d  M");
                                            ?>
                                                —
                                                <?php
                                            $date_end = date_create($event_end);
                                            echo date_format($date_end,"d M Y");
                                            ?>
                                                <?php	
                            }
                            ?>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="wp-block-column event-item-description">
                                        <div class="is-vertical wp-block-group">
                                        <div class="wp-block-post-excerpt">
                                            <p class="wp-block-post-excerpt__excerpt">
                                            <?= get_the_excerpt()?>
                                            </p>
                                        </div>

                                        <div class="tags-container">
                                            <?php
                                    $tags = array();
                                    if ($posttags) {
                                        foreach($posttags as $tag) {
                                        $tags[] =  $tag->name; } } if($tags){ foreach($tags as $tag){ echo '
                                            <div class="tagstyles">' . $tag . '</div>
                                            '; }; } ?>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                </a>
                                </div>
                            </div>
                            </li>
                        </ul>
                    </div>

              <?php

             }
         }


       
    });
}

add_action('carbon_fields_register_fields', 'calendar_block');






