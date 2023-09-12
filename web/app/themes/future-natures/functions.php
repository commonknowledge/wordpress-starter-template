<?php
use Carbon_Fields\Block;
use Carbon_Fields\Field;
use Carbon_Fields\Container;

require_once('lib/blocks/grid_block.php');
require_once('lib/blocks/filter_posts_block.php');
require_once('lib/blocks/calendar_block.php');
require_once('lib/blocks/related_posts_block.php');

function future_natures_register_content_type_taxonomy() {
	$labels = [
		'name'                       => _x( 'Content Types', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Content Type', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Content Types', 'text_domain' ),
		'all_items'                  => __( 'All Items', 'text_domain' ),
		'parent_item'                => __( 'Parent Item', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
		'new_item_name'              => __( 'New Item Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Item', 'text_domain' ),
		'edit_item'                  => __( 'Edit Item', 'text_domain' ),
		'update_item'                => __( 'Update Item', 'text_domain' ),
		'view_item'                  => __( 'View Item', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Items', 'text_domain' ),
		'search_items'               => __( 'Search Items', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No items', 'text_domain' ),
		'items_list'                 => __( 'Items list', 'text_domain' ),
		'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
	];

	$args = [
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		// Required to make taxonomy visible in Gutenberg editor
		'show_in_rest' => true,
	];
  
  
	register_taxonomy( 'content-type', [ 'post' ], $args );
    
}
add_action( 'init', 'future_natures_register_content_type_taxonomy', 0 );

add_action( 'init', 'future_natures_change_posts_to_the_commons' );

function future_natures_change_posts_to_the_commons() {
    $get_post_type = get_post_type_object('post');
    $labels = $get_post_type->labels;

    $labels->name = 'The Commons';
    $labels->singular_name = 'Post';
    $labels->add_new = 'Add Post';
    $labels->add_new_item = 'Add Post';
    $labels->edit_item = 'Edit Post';
    $labels->new_item = 'Post';
    $labels->view_item = 'View Post';
    $labels->search_items = 'Search The Commons';
    $labels->not_found = 'No Posts found';
    $labels->not_found_in_trash = 'No Posts found in Trash';
    $labels->all_items = 'All The Commons';
    $labels->menu_name = 'The Commons';
    $labels->name_admin_bar = 'The Commons';
}

add_action('carbon_fields_register_fields', 'future_natures_register_subtitle_post_meta');
function future_natures_register_subtitle_post_meta() {
    Container::make('post_meta', __('Subtitle'))
        ->set_context('side')
        ->or_where('post_type', '=', 'post')
		->or_where('post_type', '=', 'event')
		->or_where('post_type', '=', 'magazine')
        ->add_fields(array(
            Field::make('text', 'subtitle', 'Subtitle')
        ));
}

add_action('carbon_fields_register_fields', 'future_natures_subtitle_block');
function future_natures_subtitle_block() {
	Block::make( __( 'Post Subtitle' ) )
	->set_icon('heading')
	->set_description(__('The subtitle of the post'))
	->add_fields( array(
		Field::make( 'html', 'subtitle_information_text' )
    	->set_html( '<h3>You subtitle will go here</h3>' )
	) )
	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
		$subtitle = carbon_get_post_meta(get_the_ID(), 'subtitle');
		?>
		<div class="subtitle"><?= $subtitle ?></div>
		<?php
	} );
};

add_action('carbon_fields_register_fields', 'future_natures_register_originally_published_on_post_meta');
function future_natures_register_originally_published_on_post_meta() {
    Container::make('post_meta', __('Originally Published On'))
        ->set_context('side')
        ->or_where('post_type', '=', 'post')
        ->add_fields(array(
            Field::make('text', 'publication_name', 'Publication Name'),
			Field::make('text', 'publication_original_url', 'Original URL')
        ));
}

add_action('carbon_fields_register_fields', 'future_natures_originally_published_block');
function future_natures_originally_published_block() {
	Block::make( __( 'Post Original Publication' ) )
	->set_icon('link')
	->set_description(__('The original publication of the post'))
	->add_fields( array(
		Field::make( 'html', 'subtitle_information_text' )
    	->set_html( 'Link to original publication of post' )
	) )
	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
		$publicationName = carbon_get_post_meta(get_the_ID(), 'publication_name');
		$publicationUrl = carbon_get_post_meta(get_the_ID(), 'publication_original_url');
		
		if (!$publicationName) {
			return null;
		}
		?>
			<div class="wp-block-group article-credit">
				<p class="small">Originally published in</p>
				<div class="wp-block-button credit">
					<a href="<?= $publicationUrl ?>" target=”_blank” rel=”noopener noreferrer”><?= $publicationName ?></a>
				</div>
			</div>
		<?php
	} );
}

function future_natures_add_events_post_type() {
	$labels = [
		'name'                  => _x( 'Events', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Event', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Events', 'text_domain' ),
		'name_admin_bar'        => __( 'Events', 'text_domain' ),
		'archives'              => __( 'Event Archives', 'text_domain' ),
		'attributes'            => __( 'Event Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Events', 'text_domain' ),
		'add_new_item'          => __( 'Add New Event', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Event', 'text_domain' ),
		'edit_item'             => __( 'Edit Event', 'text_domain' ),
		'update_item'           => __( 'Update Event', 'text_domain' ),
		'view_item'             => __( 'View Event', 'text_domain' ),
		'view_items'            => __( 'View Events', 'text_domain' ),
		'search_items'          => __( 'Search Event', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into event', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this event', 'text_domain' ),
		'items_list'            => __( 'Event list', 'text_domain' ),
		'items_list_navigation' => __( 'Event list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter events list', 'text_domain' ),
	];

	$args = [
		'label'                 => __( 'Event', 'text_domain' ),
		'description'           => __( 'Calendar events', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => [ 'title', 'editor', 'thumbnail', 'revisions' ],
		'taxonomies'            => ['category', 'post_tag'],
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-calendar-alt',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		'show_in_rest'          => true,
		'rewrite' => array('slug' => 'calendar'),
	];

	register_post_type( 'event', $args );
}
add_action( 'init', 'future_natures_add_events_post_type', 0 );

add_action('carbon_fields_register_fields', 'future_natures_register_event_post_meta');
function future_natures_register_event_post_meta() {
    Container::make('post_meta', __('Event Details'))
        ->set_context('side')
        ->or_where('post_type', '=', 'event')
        ->add_fields(array(
			Field::make( 'date_time', 'event_start', 'Event Start' )
				->set_required( true )
				->set_help_text('The date and time of the event start'),
			Field::make( 'date_time', 'event_end', 'Event End' )
				->set_help_text('Option date and time of the event end'),
			Field::make( 'checkbox', 'online', __( 'Is this event online or in person?' ) )
				->set_help_text('Ticking this box means we show a Online label next to the event when it is displayed'),
			Field::make( 'text', 'venue', __( 'Venue' ) )
				->set_help_text('Venue for the event (if applicable)'),
			Field::make( 'text', 'city', __( 'City' ) )
				->set_help_text('The City where the event is taking place (if applicable)'),
			Field::make( 'text', 'country', __( 'Country' ) )
				->set_help_text('The Country where the event is taking place (if applicable)'),
			Field::make('text', 'url', 'URL of event - for sign up or tickets')
				->set_required( true )
				->set_help_text('This should be filled in on all events, as booking and tickets are handled off site'),
	));
}


add_action('carbon_fields_register_fields', 'future_natures_event_date_block');

function future_natures_event_date_block() {
Block::make( __( 'Event date' ) )
	->set_description(__('Event date'))
	->add_fields( array(
		Field::make( 'html', 'event_dates' )
    	->set_html( '<p>Event start and end dates will go here</p>' )
	) )
	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
		$event_start = carbon_get_post_meta(get_the_ID(), 'event_start');
		$event_end = carbon_get_post_meta(get_the_ID(), 'event_end');

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
					?>— <?php
					$date_end = date_create($event_end);
					echo date_format($date_end,"d M Y");
					?>	
			<?php	
		}
	} );
}


add_action('carbon_fields_register_fields', 'future_natures_event_venue_block');

function future_natures_event_venue_block() {
Block::make( __( 'Event venue' ) )
	->set_description(__('Event venue'))
	->add_fields( array(
		Field::make( 'html', 'event_venue' )
    	->set_html( '<p>Venue for the event</p>' )
	) )
	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
		$event_venue = carbon_get_post_meta(get_the_ID(), 'venue');
			?>
				<p><?= $event_venue ?></p>	
			<?php	
	} );
}

add_action('carbon_fields_register_fields', 'future_natures_event_online_block');

function future_natures_event_online_block() {
Block::make( __( 'Event online' ) )
	->set_description(__('Event online'))
	->add_fields( array(
		Field::make( 'html', 'event_venue' )
    	->set_html( '<p>Displays whether the event is available online</p>' )
	) )
	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
		$event_online = carbon_get_post_meta(get_the_ID(), 'online');
		if ($event_online) {
			?>
				<p>Online</p>	
			<?php	
		}
	} );
}

add_action('carbon_fields_register_fields', 'future_natures_event_city_country_block');

function future_natures_event_city_country_block() {
	Block::make( __( 'Event city and country' ) )
		->set_description(__('Event city and country'))
		->add_fields( array(
			Field::make( 'html', 'event_city_country' )
			->set_html( '<p>Displays the city and country the event will take place</p>' )
		) )
		->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
			$event_city = carbon_get_post_meta(get_the_ID(), 'city');
			$event_country = carbon_get_post_meta(get_the_ID(), 'country');
				?>
					<span><?= $event_city ?></span>	<span><?= $event_country ?></span>	
				<?php	
		} );
} 
	

function event_clickable_wrapper() {
	Block::make( __( 'event_clickable_wrapper' ) )
	->set_inner_blocks( true )
	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
		$event_url = carbon_get_post_meta(get_the_ID(), 'url');
	
		?>
      		<a class="event" href="<?= $event_url ?>" target=”_blank” rel=”noopener noreferrer”>
	   		 	<?= $inner_blocks ?>
			</a>
		<?php
	} );
}

add_action('carbon_fields_register_fields', 'event_clickable_wrapper');


function header_current_page ()
{
  wp_enqueue_script('header_js', get_stylesheet_directory_uri() . '/block-template-parts/header.js', array('jquery'));
}
add_action('wp_enqueue_scripts', 'header_current_page');


function add_metatags() {
    ?>
      <!-- Primary Meta Tags -->
    <title>Future Natures</title>
    <meta name="title" content="Future Natures">
    <meta name="description" content="Better futures are not only possible—they already exist in the making.">

<!-- Open Graph / Facebook -->
    <!-- <meta property="og:type" content="website">
    <meta property="og:url" content="https://futurenatures.org/">
    <meta property="og:title" content="Future Natures">
    <meta property="og:description" content="Better futures are not only possible—they already exist in the making.">
    <meta property="og:image" content="https://futurenatures.org/wp-content/uploads/2022/07/share-card-1.png"> -->

	<!-- Twitter --> 
	<!-- <meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@future_natures">
	<meta name="twitter:title" content="Future Natures">
	<meta name="twitter:description" content="Better futures are not only possible—they already exist in the making.">
	<meta name="twitter:image" content="https://futurenatures.org/wp-content/uploads/2022/07/share-card-1.png">  -->
		<?php
	}
	add_action('wp_head', 'add_metatags');

function future_natures_display_tags() {
	Block::make( __( 'Display tags' ) )
		->set_description(__('Display tags'))
		->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
			$posttags = get_the_tags();
		

			$tags = array();
			if ($posttags) {
				foreach($posttags as $tag) {
				$tags[] =  $tag->name;
				}
			}

			if($tags){
				foreach($tags as $tag){
				echo '<div class="tagstyles">' . $tag . '</div>';
			};
		};
			
		} );
	}
	
	add_action('carbon_fields_register_fields', 'future_natures_display_tags');

function add_favicon() { 
	?>
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="manifest" href="/site.webmanifest">
	<?php
}

add_action('wp_head', 'add_favicon');

/* 
Magazine 
*/

// create Magazine editorial post type, which is conceptually a Magazine edition

function future_natures_add_magazine_post_type() {
	$labels = [
		'name'                  => _x( 'Magazine', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Magazine edition', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Magazine', 'text_domain' ),
		'name_admin_bar'        => __( 'Magazine editions', 'text_domain' ),
		'archives'              => __( 'Magazine archives', 'text_domain' ),
		'attributes'            => __( 'Magazine attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All editions', 'text_domain' ),
		'add_new_item'          => __( 'Add new edition', 'text_domain' ),
		'add_new'               => __( 'Add new', 'text_domain' ),
		'new_item'              => __( 'New edition', 'text_domain' ),
		'edit_item'             => __( 'Edit edition', 'text_domain' ),
		'update_item'           => __( 'Update edition', 'text_domain' ),
		'view_item'             => __( 'View edition', 'text_domain' ),
		'view_items'            => __( 'View editions', 'text_domain' ),
		'search_items'          => __( 'Search editions', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into edition', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this edition', 'text_domain' ),
		'items_list'            => __( 'Edition list', 'text_domain' ),
		'items_list_navigation' => __( 'Edition list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter edition list', 'text_domain' ),
	];

	$args = [
		'label'                 => __( 'Edition', 'text_domain' ),
		'description'           => __( 'Magazine editions', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => [ 'title', 'editor', 'thumbnail', 'revisions' ],
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 6,
		'menu_icon'             => 'dashicons-admin-page',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'show_in_rest'          => true,
		'rewrite'	=> array('slug' => 'magazine'),
	];

	register_post_type( 'magazine', $args );
}
add_action( 'init', 'future_natures_add_magazine_post_type', 0 );

function future_natures_register_magazine_post_meta() {
	Container::make('post_meta', __('Magazine Details'))
	->set_context('side')
	->where('post_type', '=', 'magazine')
	->add_fields(array(
		Field::make( 'date', 'date-of-publication', __( 'Publication date' ) )
		->set_required( true )
		->set_storage_format( 'd-m-Y' ),
		Field::make( 'text', 'issue_number_text', __( 'Issue number in text form' ) )
		->set_required( true )
		->set_help_text('The issue number in text form (not number)'),
	));
}
add_action('carbon_fields_register_fields', 'future_natures_register_magazine_post_meta');

// create block for displaying magazine issue number

function future_natures_magazine_issue_number_block() {
	Block::make( __( 'Magazine issue number' ) )
	->add_fields( array(
		Field::make( 'html', 'magazine_issue_number' )
    	->set_html( '<p>Displays issue number of the edition</p>' )
		) )
		->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
			$issue_number = carbon_get_post_meta(get_the_ID(), 'issue_number_text');
			?>
				<p class="wp-block-issue-number">Issue <?= $issue_number ?></p>
			<?php	
	});
}
add_action('carbon_fields_register_fields', 'future_natures_magazine_issue_number_block');

// add magazine edition as association to commons post fields

function future_natures_register_commons_post_meta() {
	Container::make('post_meta', 'Magazine Edition')
	->set_context('side')
	->where('post_type', '=', 'post')
	->add_fields(array(
		Field::make( 'association', 'crb_association', __( 'Magazine Edition' ) )
		->set_types( array(
			array(
				'type'      => 'post',
				'post_type' => 'magazine',
				)
				) )
				->set_max( 1 )
				->set_help_text('The magazine edition this post will belong to'),
	));
}
add_action('carbon_fields_register_fields', 'future_natures_register_commons_post_meta');

// add custom setting to display email in member directory

function future_natures_user_meta() {
	Container::make( 'user_meta', 'Custom Settings' )
		->add_fields( array(
     
			Field::make( 'checkbox', 'crb_show_email', 'Show user email in member directory' )
    			->set_option_value( 'true' ),
		) );
}
add_action('carbon_fields_register_fields', 'future_natures_user_meta');

// print email in membership directory

function custom_um_members_just_after_name( $user_id, $directory_data ){ 
	$userData = get_user_meta($user_id);
	$showEmail = $userData['_crb_show_email'];
	$userEmail = get_userdata($user_id)->user_email;

	if ($showEmail[0] == 'true') {
	?>
	 	<div class="country">
			<strong>E-mail:</strong> <?=$userEmail?>
		</div>
	<?php 
	};
} 
 
 add_action('um_members_just_after_name', 'custom_um_members_just_after_name', 10, 2);


 function num_posts_archive_events($query){
    if ( is_front_page() && $query->is_archive('events')) {
            $query->set('posts_per_page', 3);
    }
    return $query;
}

add_filter('pre_get_posts', 'num_posts_archive_events');
 

 function sticky_mobile_navigation() {
    ?>
        <script>
          	let prevScrollposition = window.pageYOffset;
			window.onscroll = function() {
				const currentScrollPosition = window.pageYOffset;
				if (prevScrollposition > currentScrollPosition) {
					document.getElementsByClassName("mobile-header")[0].style.top = "0";
				} else {
					document.getElementsByClassName("mobile-header")[0].style.top = "-82px";
				}
				prevScrollposition = currentScrollPosition;
			}
        </script>
    <?php
}
add_action('wp_head', 'sticky_mobile_navigation');