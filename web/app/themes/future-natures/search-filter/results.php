<?php
include '../lib/blocks/grid_block.php';

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $query->have_posts() )
{
	?>

	<section class="grid gap-3 md:grid-cols-12 md:max-w-4xl md:mx-auto p-2">

		<?php
		$variations = ['simple_row', 'three_thin', 'one_item', 'one_big_two_small'];
		$chosen = null;
		$i = 0;
		$posts = $query->get_posts();
		
		$numberOfPostsRemaining = count($posts);

		while ( $i < $numberOfPostsRemaining ) {

			$remainingVariations = array_filter($variations, function($k) use ($chosen) {
				return $k !== $chosen; 
			});
			$pick = array_rand($remainingVariations);
			$chosen = $remainingVariations[$pick];
			
			$numberOfPostsRemaining = count($posts);
			
			switch ($numberOfPostsRemaining) {
				case 0:
					break;
				case 1:
					one_item($posts);
					break;
				case 2:
					simple_row($posts);
					break;
				case 3:
					one_big_two_small($posts);
					break;
				default:
					$chosen($posts);
			}

			$i++;
		}

		?>
	
	</section>
	<?php
}
else
{
	?>
	<div class='search-filter-results-list' data-search-filter-action='infinite-scroll-end'>
	<?php // include something here to communicate there are no more results ?>
	</div>
	<?php
}
?>