<?php
use Carbon_Fields\Block;
use Carbon_Fields\Field;

function future_natures_create_grid($posts) {
  $numberOfPosts = count($posts);

  $maximumNumberOfPosts = 100;
  
  $gridClasses = 'md:max-w-4xl';
  
  if ($numberOfPosts === 1) {
    $gridClasses = 'md:w-4xl';
  }
  ?>
  <section class="grid gap-3 md:grid-cols-12 <?= $gridClasses ?> md:mx-auto p-2">
  <?php
      $variations = ['simple_row', 'three_thin', 'one_item', 'one_big_two_small'];
      $chosen = null;
      $i = 0;
      
      while (true) {
          $variationsInPlay = array_filter($variations, function($k) use ($chosen) {
              return $k !== $chosen; 
          });
          
          $pick = array_rand($variationsInPlay);
      
          $chosen = $variationsInPlay[$pick];
          
          $i++;
          
          if ($i === $maximumNumberOfPosts) {
            break;
          }
          
          $numberOfPostsRemaining = count($posts);
          
          if ($numberOfPostsRemaining === 0) {
            break;
          }
          
          /*
          If we do have more posts remaining, but have a number that can fit on a complete row
          then we create that row and stop processing the grid.
          */
          if ($numberOfPostsRemaining === 1) {
            one_item($posts);
            break;
          }
          
          if ($numberOfPostsRemaining === 2) {
            simple_row($posts);
            break;
          }
          
          if ($numberOfPostsRemaining === 3) {
            one_big_two_small($posts);
            break;
          }
          
          $chosen($posts);
      }
  ?>
  </section>
  <?php
   wp_reset_postdata();
}

add_action('carbon_fields_register_fields', 'future_natures_posts_grid_block');
function future_natures_posts_grid_block() {
  $categories = get_categories();
  
  $categoriesSelect = [ 0 => 'All'];
  
  foreach ($categories as $category) {
    $categoriesSelect += [$category->term_id => $category->name];
  }
  
  $postTypes = get_post_types(['public' => true], 'objects');
  
  $postTypesSelect = [];

  foreach ($postTypes as $postType) {
    $postTypesSelect += [$postType->name => $postType->label];
  }

  Block::make( __( 'Posts Grid' ) )
  ->set_description(__('Grid of posts with advanced functionality'))
  ->add_fields( array(
    Field::make('text', 'grid-title', 'Grid Title'),
    Field::make('text', 'grid_number_of_posts', 'Number of posts'),
    Field::make('select', 'grid_category', 'Category')
      ->set_options($categoriesSelect),
    Field::make('select', 'grid_post_type', 'Post type')
      ->set_options($postTypesSelect),
    Field::make( 'checkbox', 'grid_show_title', 'Show title')
  ) )
  ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
        $posts = get_posts([
          'numberposts' => $fields['grid_number_of_posts'] ? $fields['grid_number_of_posts'] : -1,
          'category' => $fields['grid_category'] ? $fields['grid_category'] : 0,
          'post_type' => $fields['grid_post_type']
        ]);
      
        $viewMoreLink = '/the-commons';
        
        if ($fields['grid_category']) {
          $viewMoreLink = get_category_link($fields['grid_category']);
        }

        if ($fields['grid_post_type'] == 'magazine') {
          $parentMagazineId = get_the_ID();

          $associatedPostsQuery = new WP_Query( array(
            'post_type' => 'post',
            'meta_query' => array(
              array(
                'key' => 'crb_association', 
                'value' => "post:magazine:$parentMagazineId",
              ),
            ),
          ) );

          $posts = $associatedPostsQuery->get_posts();
        }
        ?>
        <?php if ($fields['grid_show_title'] && $fields['grid_post_type'] != 'magazine') : ?>
          <h3 class="md:grid-cols-12 md:max-w-4xl md:mx-auto grid-header flex justify-between items-center w-full">
            <?php echo esc_html($fields['grid-title']) ?>
            <a class="grid-header__link" href="<?php echo $viewMoreLink ?>">
              View all
            </a>
          </h3>
        <?php endif; ?>
        <?php future_natures_create_grid($posts); ?>
        <?php
  } );
}

function includes_long_word($post_title, $max_length) {
  $split_post_title = explode(" ", $post_title);
  $includes_long_word = false;

  foreach($split_post_title as $word) {
    if (strlen($word) > $max_length) {
      $includes_long_word = true;
      break;
    }
  }

  return $includes_long_word;
};

function simple_row(&$posts) {
    $postsToUse = array_splice($posts, 0, 2);

    $firstPost = $postsToUse[0];
    $secondPost = $postsToUse[1];
  ?>
    <article
      class="relative bg-green border-comic-strip-variant-a bg-cover bg-center z-0 md:col-span-5 p-2 min-h-[400px] md:min-h-0"
      style="background-image: url(<?= get_the_post_thumbnail_url($firstPost) ?>);"
    >
        <a href="<?= get_the_permalink($firstPost) ?>" class="stretched-link"></a>
        <div class="relative h-full">
          <header class="bg-dark-green p-4 z-10 w-full">
            <div class="wp-block-group article-header">
                  <?php $contentTypes = get_the_terms($firstPost, 'content-type') ?>
              <div class="wp-block-group article-metadata">
                <span class="bg-green p-1 inline-block leading-tight">
                  <span class="text-pink uppercase text-xs">
                          <?= $contentTypes[0]->name ?>
                      </a>
                </span>
              </div>
              <h3 class="text-pink text-display my-1"><?= $firstPost->post_title ?></h3>
              <p class="text-pink opacity-50 text-xs">
                    <?php $display_name = get_the_author_meta( 'display_name', $firstPost->post_author ); ?>
                    <?= $display_name ?>
                  </p>
            </div>
          </header>
        </div>
    </article>

    <article
      class="relative bg-green border-comic-strip-variant-b bg-cover bg-center md:col-span-7 p-2 z-0 min-h-[400px] md:min-h-0"
      style="background-image: url(<?= get_the_post_thumbnail_url($secondPost) ?>);"
    >
          <a href="<?= get_the_permalink($secondPost) ?>" class="stretched-link"></a>
        <div class="relative h-full">
          <header class="bg-dark-green p-4 z-10 w-full max-w-sm mb-2">
            <div class="wp-block-group article-header">
                    <?php $contentTypes = get_the_terms($secondPost, 'content-type') ?>
              <div class="wp-block-group article-metadata">
                <span class="bg-green p-1 inline-block leading-tight">
                  <span class="text-pink uppercase text-xs"><?= $contentTypes[0]->name ?></a>
                </span>
              </div>
              <h3 class="text-pink text-display my-1"><?= $secondPost->post_title ?></h3>
              <p class="text-pink opacity-50 text-xs">
                    <?php $display_name = get_the_author_meta( 'display_name', $secondPost->post_author ); ?>
                    <?= $display_name ?>
                  </p>
            </div>
          </header>

          <h4 class="my-0 z-10 bg-dark-green text-pink text-xs font-body p-4 w-full max-w-xs">
            <?= get_the_excerpt($secondPost) ?>
          </h4>
        </div>
    </article>
  <?php
}

function three_thin(&$posts) {
    $postsToUse = array_splice($posts, 0, 3);

    $firstPost = $postsToUse[0];
    $secondPost = $postsToUse[1];
    $thirdPost = $postsToUse[2];
    ?>
    <article
        class="relative bg-green border-comic-strip-variant-a bg-cover bg-center min-h-[780px] z-0 md:col-span-4 p-2"
        style="background-image: url(<?= get_the_post_thumbnail_url($firstPost) ?>);"
    >
        <a href="<?= get_the_permalink($firstPost) ?>" class="stretched-link"></a>
        <div class="relative h-full">
          <header class="bg-dark-green p-4 z-10 absolute top-0 left-0 w-full">
            <div class="wp-block-group article-header">
              <?php $contentTypes = get_the_terms($firstPost, 'content-type') ?>
              <div class="wp-block-group article-metadata">
                <span class="bg-green p-1 inline-block leading-tight">
                  <span class="text-pink uppercase text-xs">
                    <?= $contentTypes[0]->name ?>
                  </a>
                </span>
              </div>
              <h3 class="text-pink text-display my-1 <?php if (includes_long_word($firstPost->post_title, 12)) echo 'text-3xl' ?>"><?= $firstPost->post_title ?></h3>
              <p class="text-pink opacity-50 text-xs">
                <?php $display_name = get_the_author_meta( 'display_name', $firstPost->post_author ); ?>
                <?= $display_name ?>
              </p>
            </div>
          </header>

          <h4 class="my-0 z-10 bg-dark-green text-pink text-xs font-body p-4 absolute bottom-0 left-0 w-full">
            <?= get_the_excerpt($firstPost) ?>
          </h4>
        </div>
    </article>


    <article
        class="relative bg-green border-comic-strip-variant-b bg-cover bg-center min-h-[780px] z-0 md:col-span-4 p-2"
        style="background-image: url(<?= get_the_post_thumbnail_url($secondPost) ?>);"
    >
        <a href="<?= get_the_permalink($secondPost) ?>" class="stretched-link"></a>
        <div class="relative h-full">
          <header class="bg-dark-green p-4 z-10 absolute top-0 left-0 w-full">
            <div class="wp-block-group article-header">
            <?php $contentTypes = get_the_terms($secondPost, 'content-type') ?>
              <div class="wp-block-group article-metadata">
                <span class="bg-green p-1 inline-block leading-tight">
                  <span class="text-pink uppercase text-xs"><?= $contentTypes[0]->name ?></a>
                </span>
              </div>
              <h3 class="text-pink text-display my-1 <?php if (includes_long_word($secondPost->post_title, 12)) echo 'text-3xl' ?>"><?= $secondPost->post_title ?></h3>
              <p class="text-pink opacity-50 text-xs">
                <?php $display_name = get_the_author_meta( 'display_name', $secondPost->post_author ); ?>
                <?= $display_name ?>
              </p>
            </div>
          </header>

          <h4 class="my-0 z-10 bg-dark-green text-pink text-xs font-body p-4 absolute bottom-0 left-0 w-full">
            <?= get_the_excerpt($secondPost) ?>
          </h4>
        </div>
    </article>


    <article
        class="relative bg-green border-comic-strip-variant-a bg-cover bg-center min-h-[780px] z-0 md:col-span-4 p-2"
        style="background-image: url(<?= get_the_post_thumbnail_url($thirdPost) ?>);"
    >
    <a href="<?= get_the_permalink($thirdPost) ?>" class="stretched-link"></a>
    <div class="relative h-full">
      <header class="bg-dark-green p-4 z-10 absolute top-0 left-0 w-full">
        <div class="wp-block-group article-header">
          <?php $contentTypes = get_the_terms($thirdPost, 'content-type') ?>
          <div class="wp-block-group article-metadata">
            <span class="bg-green p-1 inline-block leading-tight">
              <span class="text-pink uppercase text-xs"><?= $contentTypes[0]->name ?></a>
            </span>
          </div>
          <h3 class="text-pink text-display my-1 <?php if (includes_long_word($thirdPost->post_title, 12)) echo 'text-3xl' ?>"><?= $thirdPost->post_title ?></h3>
          <p class="text-pink opacity-50 text-xs">
            <?php $display_name = get_the_author_meta( 'display_name', $thirdPost->post_author ); ?>
            <?= $display_name ?>
          </p>
        </div>
      </header>

      <h4 class="my-0 z-10 bg-dark-green text-pink text-xs font-body p-4 absolute bottom-0 left-0 w-full">
        <?= get_the_excerpt($thirdPost) ?>
      </h4>
    </div>
    </article>
    <?php
}

function one_item(&$posts) {
    $postsToUse = array_splice($posts, 0, 1);

    $firstPost = $postsToUse[0];
    ?>
    <article
        class="relative bg-green border-comic-strip-variant-b bg-cover bg-center min-h-[550px] z-0 md:col-span-12 p-2"
        style="background-image: url(<?= get_the_post_thumbnail_url($firstPost) ?>);"
    >
        <a href="<?= get_the_permalink($firstPost) ?>" class="stretched-link"></a>
        <div class="relative h-full">
        <header class="bg-dark-green p-4 z-10 absolute top-0 left-0 w-full <?php echo includes_long_word($firstPost->post_title, 10) ? 'max-w-lg' : 'max-w-sm'; ?>">
          <div class="wp-block-group article-header">
            <?php $contentTypes = get_the_terms($firstPost, 'content-type') ?>
            <div class="wp-block-group article-metadata">
              <span class="bg-green p-1 inline-block leading-tight">
                <span class="text-pink uppercase text-xs"><?= $contentTypes[0]->name ?></a>
              </span>
            </div>
            <h3 class="text-pink text-display my-1 text-6xl"><?= $firstPost->post_title ?></h3>
            <p class="text-pink opacity-50 text-xs">
              <?php $display_name = get_the_author_meta( 'display_name', $firstPost->post_author ); ?>
              <?= $display_name ?>
            </p>
          </div>
        </header>

        <h4 class="my-0 z-10 bg-dark-green text-pink text-xs font-body p-4 absolute bottom-0 right-0 w-full max-w-xs">
            <?= get_the_excerpt($firstPost) ?>
        </h4>
        </div>
    </article>
    <?php
}

function one_big_two_small(&$posts) {
    $postsToUse = array_splice($posts, 0, 3);

    $firstPost = $postsToUse[0];
    $secondPost = $postsToUse[1];
    $thirdPost = $postsToUse[2];
    ?>
    <article
        class="relative bg-green border-comic-strip-variant-a bg-cover bg-center min-h-[550px] z-0 md:col-span-7 p-2"
        style="background-image: url(<?= get_the_post_thumbnail_url($firstPost) ?>);"
    >
        <a href="<?= get_the_permalink($firstPost) ?>" class="stretched-link"></a>
        <div class="relative h-full">
          <header class="bg-dark-green p-4 z-10 absolute top-0 left-0 w-full <?php if (!includes_long_word($firstPost->post_title, 13)) echo 'max-w-sm' ?>">
            <div class="wp-block-group article-header">  
              <?php $contentTypes = get_the_terms($firstPost, 'content-type') ?>
              <div class="wp-block-group article-metadata">
                <span class="bg-green p-1 inline-block leading-tight">
                  <span class="text-pink uppercase text-xs"><?= $contentTypes[0]->name ?></a>
                </span>
              </div>
              <h3 class="text-pink text-display my-1 text-6xl"><?= $firstPost->post_title ?></h3>
              
              <p class="text-pink opacity-50 text-xs">
              <?php $display_name = get_the_author_meta( 'display_name', $firstPost->post_author ); ?>
                  <?= $display_name ?>
              </p>
            </div>
          </header>

          <h4 class="my-0 z-10 bg-dark-green text-pink text-xs font-body p-4 absolute bottom-0 right-0 w-full max-w-xs">
            <?= get_the_excerpt($firstPost) ?>
          </h4>
        </div>
    </article>
  <section class="md:col-span-5 grid gap-3 md:grid-rows-2">
    <article
      class="relative bg-green border-comic-strip-variant-b bg-cover bg-center z-0 md:col-span-5 p-2 min-h-[400px] md:min-h-0"
      style="background-image: url(<?= get_the_post_thumbnail_url($secondPost) ?>);"
    >
      <a href="<?= get_the_permalink($secondPost) ?>" class="stretched-link"></a>
      <div class="relative h-full">
        <header class="bg-dark-green p-4 z-10 w-full">
          <div class="wp-block-group article-header">
            <?php $contentTypes = get_the_terms($secondPost, 'content-type') ?>
            <div class="wp-block-group article-metadata">
              <span class="bg-green p-1 inline-block leading-tight">
                <span class="text-pink uppercase text-xs"><?= $contentTypes[0]->name ?></a>
              </span>
            </div>
            <h3 class="text-pink text-display my-1"><?= $secondPost->post_title ?></h3>

            <p class="text-pink opacity-50 text-xs">
                <?php $display_name = get_the_author_meta( 'display_name', $secondPost->post_author ); ?>
                <?= $display_name ?>
            </p>
          </div>
        </header>
      </div>
    </article>

    <article
      class="relative bg-green border-comic-strip-variant-a bg-cover bg-center z-0 md:col-span-5 p-2 min-h-[400px] md:min-h-0"
      style="background-image: url(<?= get_the_post_thumbnail_url($thirdPost) ?>);"
    >
      <a href="<?= get_the_permalink($thirdPost) ?>" class="stretched-link"></a>
      <div class="relative h-full">
        <header class="bg-dark-green p-4 z-10 w-full">
          <div class="wp-block-group article-header">
          <?php $contentTypes = get_the_terms($thirdPost, 'content-type') ?>
            <div class="wp-block-group article-metadata">
              <span class="bg-green p-1 inline-block leading-tight">
                <span class="text-pink uppercase text-xs"><?= $contentTypes[0]->name ?></a>
              </span>
            </div>
            <h3 class="text-pink text-display my-1"><?= $thirdPost->post_title ?></h3>

            <p class="text-pink opacity-50 text-xs">
                <?php $display_name = get_the_author_meta( 'display_name', $thirdPost->post_author ); ?>
                <?= $display_name ?>
            </p>
          </div>
        </header>
      </div>
    </article>
  </section>
    <?php
}