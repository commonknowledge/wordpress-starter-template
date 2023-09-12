<?php
use Carbon_Fields\Block;
use Carbon_Fields\Field;

function add_query_vars($vars) {
    $vars[] = 'category_name';
    $vars[] = 'content-type';
    $vars[] = 'author_filter';
    return $vars;
}
add_filter('query_vars', 'add_query_vars');

add_action('carbon_fields_register_fields', 'future_natures_filter_posts_block');
function future_natures_filter_posts_block() {
    Block::make( __( 'Filter Posts' ) )
    ->set_description(__('Displays a styled Search & Filter search form'))
    ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
        $category_name = get_query_var('category_name');
        $content_type_query = get_query_var('content-type');
        $author = get_query_var('author_filter');
        
        $queryParameter = [];
        
        $queryParameter['posts_per_page'] = -1;
        $queryParameter['nopaging'] = true;
        
        if  ($category_name && $category_name !== 'all') {
          $queryParameter['category_name'] = $category_name;
        }
        
        if ( $content_type_query &&  $content_type_query !== 'all') {
          $queryParameter['tax_query'] = array(
            array(
                'taxonomy' => 'content-type',
                'field' => 'slug',
                'terms' => $content_type_query
            )
          );
        }
        
        if ($author && $author !== 'all') {
          $queryParameter['author_name'] = $author;
        }
        
        if (count($queryParameter) === 0) {
            $query = new WP_Query(['post_type' => 'post', 'posts_per_page' => -1, 'nopaging' => true]);
        } else {
          $query = new WP_Query($queryParameter);
        }
        
        $posts = $query->get_posts();
        ?>
        <form class="searchandfilter">
        <ul>
          <li>
            <h4>Category</h4>
            <label for="category_name">
              <span class="screen-reader-text">Choose a category</span>
              <select name="category_name" id="category_name">
                <option value="all">All categories</option>
                <?php
                $categories = get_categories(array(
                  'hide_empty' => 0,
                  'exclude' => array(1)
                ));
                
                foreach ($categories as $category) {
                  $selected = '';
                  
                  if ($category_name === $category->slug) {
                    $selected = 'selected';
                  }
                  
                  if ($category->slug === 'the-commons') {
                    continue;
                  }
                  
                  echo '<option value="' . $category->slug . '" '. $selected . '>' . $category->name . '</option>';
                }
                ?>
              </select>
            </label>
          </li>
          <li>
            <h4>Content Type</h4>
            <label for="content-type">
              <span class="screen-reader-text">Choose a content type</span>
              <select name="content-type" id="content-type">
                <option value="all">All content types</option>
                  <?php
                  $content_types = get_terms(array(
                    'taxonomy' => 'content-type',
                    'hide_empty' => 0
                  ));
                  
                  foreach ($content_types as $content_type) {
                      $selected = '';
                    
                    if ($content_type_query === $content_type->slug) {
                      $selected = 'selected';
                    }
                    
                    echo '<option value="' . $content_type->slug . '" '. $selected . '>' . $content_type->name . '</option>';
                  }
                  ?>
              </select>
            </label>
          </li>
          <li>
            <h4>Author</h4>
            <label for="author_filter">
            <span class="screen-reader-text">Choose an author</span>
              <select name="author_filter" id="author_filter">
                <option value="all">All authors</option>
                  <?php
                  $users = get_users(['has_published_posts' => true]);
                  foreach ($users as $user) {
                    $selected = '';
                    
                    if ($author === $user->user_nicename) {
                      $selected = 'selected';
                    }
                    
                    echo '<option value="' . $user->user_nicename . '" '. $selected . '>' . $user->display_name . '</option>';
                  }
                  ?>
              </select>
            </label>
          </li>
          <input type="submit" style="display:none" />
      </ul>
        </form>
        <?php if ( $query->have_posts() ) : ?>
            <?php future_natures_create_grid($posts) ?>
        <?php else : ?>
            <div class="no-results">
              <div>
                <p>Couldn't find any posts that match.</p>
                <p>Try another combination of filters?</p>
              </div>
            </div>
        <?php endif ?>
        <script>
          jQuery('.searchandfilter select').on('change', function(){
            jQuery('form.searchandfilter input[type="submit"]').click();
          });
        </script>
        <?php
    });
}
