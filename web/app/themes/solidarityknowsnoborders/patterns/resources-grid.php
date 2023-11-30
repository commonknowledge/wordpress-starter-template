<?php

/**
 * Title: Resources Grid
 * Slug: solidarity-knows-no-borders/resources-grid
 * Description:
 * Categories: solidarity-knows-no-borders
 * Keywords:
 * Viewport Width: 1320
 * Block Types:
 * Post Types:
 * Inserter: true
 */
?>
<!-- wp:query {"queryId":26,"query":{"perPage":"5","pages":0,"offset":0,"postType":"resource","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"parents":[]},"layout":{"type":"constrained","justifyContent":"left","contentSize":""}} -->
<div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default","columnCount":3}} -->
    <!-- wp:post-title {"level":3,"isLink":true,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|small"}},"elements":{"link":{"color":{"text":"var:preset|color|black"}}}},"className":"stretched-link"} /-->

    <!-- wp:carbon-fields/resource-metadata /-->

    <!-- wp:spacer {"height":"var:preset|spacing|small"} -->
    <div style="height:var(--wp--preset--spacing--small)" aria-hidden="true" class="wp-block-spacer"></div>
    <!-- /wp:spacer -->

    <!-- wp:post-featured-image {"aspectRatio":"1","width":"360px","height":""} /-->
    <!-- /wp:post-template -->
</div>
<!-- /wp:query -->