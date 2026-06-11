<?php
/**
 * Server-side render template for the Digital Clock block.
 *
 * The current time is rendered in PHP so the block is meaningful before
 * JavaScript runs; the Alpine.js component registered in view.ts then takes
 * over and ticks every second using the visitor's local time.
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?> x-data="digitalClock">
    <time x-text="time"><?php echo esc_html(wp_date('H:i:s')); ?></time>
</div>
