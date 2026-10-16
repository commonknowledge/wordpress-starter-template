<?php

/**
 * Server-side render for the example block.
 *
 * @var array $attributes Block attributes from block.json.
 */

$heading = $attributes['heading'] ?? '';
?>
<div <?= get_block_wrapper_attributes() ?>>
    <?php if ($heading) : ?>
        <h2><?= esc_html($heading) ?></h2>
    <?php endif; ?>
</div>
