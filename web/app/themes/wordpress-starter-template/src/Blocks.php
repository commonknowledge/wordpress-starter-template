<?php

namespace CommonKnowledge\WordpressStarterTemplate;

use Carbon_Fields\Container\Block_Container;
use Carbon_Fields\Block;
use Carbon_Fields\Field;

class Blocks
{
    public static function register()
    {
        self::registerExampleBlock();
    }

    public static function registerExampleBlock()
    {
        /** @var Block_Container $block */
        $block = Block::make('Example Custom Block');
        $block->add_fields([
            Field::make('separator', 'crb_separator', "Example Custom Block"),
        ]);
        $block->set_category("widgets")
            ->set_icon('heart')
            ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
                ?>
            <div class="<?= $attributes['className'] ?? '' ?>">Example Custom Block</div>
                <?php
            });
    }
}
