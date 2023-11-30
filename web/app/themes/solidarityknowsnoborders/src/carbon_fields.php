<?php


use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Block;
use CommonKnowledge\WordPress\SolidarityKnowsNoBorders;
use function Env\env;
use PHPCoord\CoordinateReferenceSystem\Geographic2D;
use PHPCoord\Point\GeographicPoint;
use PHPCoord\UnitOfMeasure\Angle\Degree;

Container::make('post_meta', 'Organisation details')
    ->where('post_type', '=', 'mapster-wp-location')
    ->add_fields(array(
        Field::make('text', 'address', __('Address'))
            ->set_help_text('Add address or organisation including city'),
        Field::make('text', 'city', __('City'))
            ->set_required(true),
        Field::make('text', 'website', __('Website'))
            ->set_required(true)
            ->set_attribute('type', 'url'),
        Field::make('text', 'instagram', __('Instagram'))
            ->set_attribute('type', 'url'),
        Field::make('text', 'twitter', __('Twitter'))
            ->set_attribute('type', 'url'),
        Field::make('text', 'facebook', __('Facebook'))
            ->set_attribute('type', 'url'),
        Field::make('image', 'org_image', 'Image'),
        Field::make('text', 'alt_text', 'Alt Text')
            ->set_help_text('Describe the image for visually impaired users & search engines'),
    ));


Block::make(__('Resource metadata'))
    ->set_icon('info')
    ->set_description(__('Metadata for the resource'))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $date = get_the_date("d.m.Y");
        $resource_format_terms = get_the_terms(get_the_ID(), 'resource_format');
        $resource_formats = !empty($resource_format_terms) ? wp_list_pluck($resource_format_terms, 'name') : [];

        // Get the first category
        $categories = get_the_category();
        $first_category = !empty($categories) ? $categories[0]->name : '';

        ?>
    <div class="resource-metadata">
        <p><?= $first_category ?></p>
        <p><?= implode(', ', $resource_formats) ?></p>
        <p><?= $date ?></p>
    </div>
        <?php
    });


function add_query_vars($vars)
{
    $vars[] = 'category_name';
    $vars[] = 'resource_format';
    $vars[] = 'resource_language';
    $vars[] = 'search_query';
    return $vars;
}
add_filter('query_vars', 'add_query_vars');

Block::make(__('Search and Filter'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Search and Filter'))
    ))
    ->set_icon('filter')
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $categories = get_categories();
        $taxonomies = [
            "resource_type",
            "resource_language",
        ];
        $category_name = get_query_var('category_name');
        $resource_format = get_query_var('resource_format');
        $resource_language = get_query_var('resource_language');
        $search_query = get_query_var('search_query');


        $query_args = ['post_type' => 'resource'];

        $query_args = SolidarityKnowsNoBorders\addFilterVarsToQueryArgs($query_args, $category_name, $resource_format, $resource_language, $search_query);

        $query = new WP_Query($query_args);

        // Get the post count
        $post_count = $query->found_posts;


        $post_type = 'resource';
        $count = wp_count_posts($post_type);

        // Output the filter options
        ?>
    <div class="filters">
        <button class="filters-toggle">Filters</button>
        <form class="filters-form" method="get">

            <div>
                <label for="category-filter">Category</label>
                <select id="category-filter" name="category_name" onchange="this.form.submit()">
                    <option value="">Category</option>
                    <?php
                    foreach ($categories as $category) {
                        echo "<option value='{$category->slug}'" . selected($category->slug, $category_name, false) . ">{$category->name}</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="resource-format-filter">Format</label>
                <select id="resource-format-filter" name="resource_format" onchange="this.form.submit()">
                    <option value="">Format</option>
                    <?php
                    // Output options for the "resource_format" taxonomy
                    $terms = get_terms("resource_format");
                    foreach ($terms as $term) {
                        echo "<option value='{$term->slug}'" . selected($term->slug, $resource_format, false) . ">{$term->name}</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="resource-language-filter">Language</label>
                <select id="resource-language-filter" name="resource_language" onchange="this.form.submit()">
                    <option value="">Language</option>
                    <?php
                    // Output options for the "resource_language" taxonomy
                    $terms = get_terms("resource_language");
                    foreach ($terms as $term) {
                        echo "<option value='{$term->slug}'" . selected($term->slug, $resource_language, false) . ">{$term->name}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="search-input-wrapper">
                <label for="search">Search</label>
                <input type="text" id="search" name="search_query" value="<?php echo $search_query; ?>" placeholder="Search">
                <button id="search-button" type="submit"></button>
            </div>
        </form>
    
    </div>

        <?php
        ?>

    <p class="has-small-font-size" style="text-transform:uppercase; margin-top:60px">
        <?php
        if (!empty($category_name) || !empty($resource_format) || !empty($resource_language) || !empty($search_query)) {
            // The query has been filtered, so display the filtered post count
            ?>
                <div class="clear-button-wrapper">
    <p class="has-small-font-size" style="text-transform:uppercase; margin-bottom:0px">
            <?php

            if ($post_count === 0) {
                echo 'No resources found </p>';
            } elseif ($post_count > 1) {
                echo $post_count . ' resources</p>';
            } else {
                echo $post_count . ' resource</p>';
            }
            ?>
                <button id="clearButton">Clear filters</button>
        </div>
            <?php
        } else {
            // No filters applied, so display all posts
            ?>
        
    <p class="has-small-font-size" style="text-transform:uppercase; margin-top:60px">
            <?= $count->publish ?> resources</p>
            <?php
        }


        ?>

        <?php
    });

/**
 * Calculate the distance in metres between two points.
 * $from and $to should be arrays of the form [$longitude, $latitude],
 * e.g. the array coordinates of London are [-0.1, 51.5]
 */
function calculate_distance($from, $to)
{
    // Specify the CRS (Coordinate Reference System) of the points
    // We are using latitude and longitude, which is called WGS 84
    // (https://gisgeography.com/wgs84-world-geodetic-system/)
    $crs = Geographic2D::fromSRID(Geographic2D::EPSG_WGS_84);

    $from_latitude = $from[1];
    $from_longitude = $from[0];

    $to_latitude = $to[1];
    $to_longitude = $to[0];

    $from = GeographicPoint::create(
        $crs,
        new Degree($from_latitude),
        new Degree($from_longitude),
        null
    );
    $to = GeographicPoint::create(
        $crs,
        new Degree($to_latitude),
        new Degree($to_longitude),
        null
    );
    $distance = $from->calculateDistance($to);
    return $distance;
}



Block::make(__('Map display block'))
    ->add_fields(array(
        // This dummy field just displays the name of the block in the block editor
        Field::make('separator', 'crb_separator', __('Map')),

    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {



        // Get the ?search= parameter from the URL
        // The "?? null" part prevents a PHP error if the parameter is missing
        $search_param = $_GET['search'] ?? null;

        // Initialise the search results coordinates to null, in case the location is not found
        $results_coordinates = null;

        // If a search parameter is required, use the Mapbox Geocode API to find the coordinates
        if ($search_param) {
            // Build the URL for the Geocode API query
            // The optional parameter "?country=gb" is provided to restrict results to the UK
            // The access token is retrieved from an environment variable using the env() function
            $geocode_data_url = ('https://api.mapbox.com/geocoding/v5/mapbox.places/' . $search_param . '.json' .
                '?country=gb&access_token=' . env('MAPBOX_ACCESS_TOKEN')
            );

            // Get the data from the URL
            $geocode_data = file_get_contents($geocode_data_url);

            // Parse the JSON response (a string) into an object
            $results = json_decode($geocode_data);
            $has_results = count($results->features) > 0;
            if ($has_results) {
                $results_coordinates = $results->features[0]->geometry->coordinates;
            }
        }

        // Make a query to find all Location posts
        $query = new WP_Query([
            'post_type' => 'mapster-wp-location',
        ]);

        // Create an array to store the Location data
        // We are going to iterate through the Location posts to get the data we need,
        // then store them in this array
        $locations = [];

        // For each Location $post, get the data we need
        foreach ($query->posts as $post) {
            // Standard WordPress fields
            $name = get_the_title($post);

            // Carbon fields
            $address = carbon_get_post_meta($post->ID, 'address');
            $city = carbon_get_post_meta($post->ID, 'city');
            $twitter = carbon_get_post_meta($post->ID, 'twitter');
            $website = carbon_get_post_meta($post->ID, 'website');
            $instagram = carbon_get_post_meta($post->ID, 'instagram');
            $facebook = carbon_get_post_meta($post->ID, 'facebook');
            $image_id = carbon_get_post_meta($post->ID, 'org_image');


            // Get the image details using WordPress functions
            $image_url = wp_get_attachment_image_src($image_id, 'thumbnail');
            $image_alt = carbon_get_post_meta($post->ID, 'alt_text');

            // Metadata managed by the Mapster plugin
            $feature_collection = get_post_meta($post->ID, 'location', true);

            // Parse the JSON features stored by Mapster into an object
            // so we can get the coordinates of the location
            $parsed_feature_collection = json_decode($feature_collection);

            // Get the coordinates. It is possible to create a Location with no coordinates,
            // so this if statement handles that
            if ($parsed_feature_collection->features) {
                $coordinates = $parsed_feature_collection->features[0]->geometry->coordinates;
            } else {
                $coordinates = null;
            }

            // Calculate the distance between the Location and the search location
            if ($coordinates && $results_coordinates) {
                $distance = calculate_distance($coordinates, $results_coordinates);
                // Convert metres to miles
                $distance_miles = $distance->getValue()  * 0.00062137;
            } else {
                $distance_miles = null;
            }

            // Create an array to store all the data for the Location
            $location_data = [
                "name" => $name,
                "coordinates" => $coordinates,
                "distance_miles" =>  $distance_miles,
                "address" =>  $address,
                "city" =>  $city,
                "website" => $website,
                "instagram" => $instagram,
                "facebook" => $facebook,
                "image_url" => $image_url,
                "image_alt" => $image_alt,
                "twitter" => $twitter,

            ];

            // Add this Location to the list so we can use it later
            $locations[] = $location_data;
        }
        $locations = array_filter($locations, function ($location) {
            return $location['distance_miles'] <= 20;
        });
        
        usort($locations, function ($l1, $l2) {
            if ($l1['distance_miles'] < $l2['distance_miles']) {
                return -1;
            } else {
                return 1;
            }
        });
        

        ?>
    <div class="container">
        <div id='map' style='height: 90vh;'></div>
        <div id="searchResults">
            <form id="search-form" method="GET">
                <input id="search" name="search" type="text" value="<?php echo $search_param; ?>" placeholder="Search by location"> </input>
                <button type="submit"></button>
            </form>
            <?php if ($search_param && !$results_coordinates || $search_param && !$locations) {
                $locations = array();?>
                <p class="search-error">Sorry, we can't find any organisations at that location.</p>
                <button id="clearButton">Clear search</button>
            <?php } ?>
            <div style="height:60px" aria-hidden="true" class="wp-block-spacer"></div>
            <div class="clear-button-wrapper">
            <?php if (!empty($locations)) { ?>
                <div class="location-metadata"><?= count($locations) ?> <?= count($locations) > 1 ? "organisations" : "organisation" ?></div>
            <?php } ?>
            <?php if ($search_param && $locations) { ?>
            <button id="clearButton">Clear search</button>
            <?php } ?>
            </div>
            <div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
            <div>
                <ul class="locations-list">
                    <?php foreach ($locations as $location) : ?>
                        <li>
                            <div class="locations-list-row">
                                <div>
                                    <div class="location-metadata"> <?= $location['city'] ?></div>
                                    <div style="height:20px" aria-hidden="true" class="wp-block-spacer"></div>
                                    <h4><?= $location['name'] ?></h4>
                                    <div style="height:20px" aria-hidden="true" class="wp-block-spacer"></div>
                                    <div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex" style="margin-top:0;margin-bottom:0">
                                <div class="wp-block-button">
                                    <a href="<?= $location['website'] ?>" class="wp-block-button__link wp-element-button" style="padding-top:8px;padding-bottom:8px">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="28" height="28" viewBox="0 0 28 28" fill="none">
                                            <rect width="27.9412" height="28" fill="url(#pattern1)"/>
                                            <defs>
                                            <pattern id="pattern1" patternContentUnits="objectBoundingBox" width="1" height="1">
                                                <use xlink:href="#image0_761_618" transform="matrix(0.0104167 0 0 0.0103948 0 0.00105044)"/>
                                            </pattern>
                                            <image id="image0_761_618" width="96" height="96" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGAAAABgCAYAAADimHc4AAAACXBIWXMAAAsTAAALEwEAmpwYAAADIklEQVR4nO3by04UQRTG8X9mxoVcTLxtBHdGfAEJPIGXtaLRxIUati4R1iosBRFCeAMJGsX4HhpcGy6aeMEtwqZNm5qIEKnTXTX00P39ktqRU13n9FRXVTcgIiIiIiIiIiIiIiIiIiIiIiIiIiLyrzowCIwBi8BH4CewDSSBLVRo/9tuLMtubKPAAFCjDZwFJoD1CANN2rQA/2trwDjQSwFOA3PAVgsHmLR5AZotzcEMcJIDcgvYOICBJYekAM32A7hBCx0B5g9wQMkhK0CzzQINIusA3hUwmCTCtRdxzW9dzqLd+UUlP4lw/UmBRYjySyhi2klKUIDEPZyD3M7Y4QowBVwC+oBODr9ON5Z0TM+A1Yw5Gcrbcbqs+m7sJN0HDLsNWdnVgOvApwyro1N5OpozdvAK6KJ6uoHXxhxN59nhWjZZT9tlS16QdOyThjz9AnqyBJ4w3vlVTn5TzfhLeIJR3XC2s1bRaWe/6eizJ2er1ht20FDNu5ZAFXPfkLd+S6Axw1KzCqudrOpuZtgvdw8tgV56gqQPnRBJm7cQ057YC5Ygy54gl4MusdwFuOqJ/cESxHfUfC7wIstcgD5P7HRj6+Vb/4eufspcgC7DfsBLBcjvWIwC+Kag84Qp8y/gQowpSA/hgh/Ci54g6ZFsiDL/AmY8sV9YgowattTaiO3VMGzERjAYMNwl9yyBKmbYkLeLlkA1QyXX3QGU/F39fDEc4ZhPj8cN1UyPYHUczZ8cvDHk6zEZ9BpfyExWvAg19w7cl6dN4EzsJ/rOX0J3RaedJWOO0iJldgL4Zuwg/bsHrfgirE3v+juGOX/nS/nc343eNHbSbGvuSPaK2xWW4a1ZlxtLusl6blig7G7XQi9gNmOHsVuoIq8919SzW93wkkYFYE8OlmJOyR3uW0cVAFMO0mXpUSJrZFgZVXkKmmr1YmQowyeLVSrA1xgP3CxL1Gn3gqHqBdh0d/1xCtDjvvharWABVoBHeXa4rdqc9LvvXhbci4eNSP/EFyq0/y03lvdubCPuVLPKRzAiIiIiIiIiIiIiIiIiIiIiIiIiIsJevwHM218LQVp+KgAAAABJRU5ErkJggg=="/>
                                            </defs>
                                        </svg>
                                    </a>
                                </div>
                                <?php if ($location['instagram']) : ?>
                                <div class="wp-block-button">
                                    <a href="<?= $location['instagram'] ?>" class="wp-block-button__link wp-element-button" style="padding-top:8px;padding-bottom:8px">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="29" height="29" viewBox="0 0 29 29" fill="none">
                                            <g id="Icon">
                                            <path id="Vector" d="M24.8436 12.0884H22.4318C22.6279 12.7943 22.7259 13.5002 22.7259 14.2061C22.7259 15.3825 22.5004 16.5002 22.0495 17.559C21.5985 18.6178 20.9808 19.5394 20.1965 20.3237C19.4122 21.108 18.4906 21.7257 17.4318 22.1766C16.373 22.6276 15.2553 22.8531 14.0789 22.8531C12.9024 22.8531 11.7946 22.6276 10.7553 22.1766C9.71613 21.7257 8.80436 21.108 8.02005 20.3237C7.23573 19.5394 6.61809 18.6178 6.16711 17.559C5.71613 16.5002 5.49064 15.3825 5.49064 14.2061C5.49064 13.5002 5.58868 12.7943 5.78475 12.0884H3.31417V23.9119C3.31417 24.2257 3.42201 24.4806 3.6377 24.6766C3.85338 24.8727 4.11809 24.9708 4.43181 24.9708H23.7848C24.0985 24.9708 24.3534 24.8727 24.5495 24.6766C24.7455 24.4806 24.8436 24.2257 24.8436 23.9119V12.0884ZM24.8436 4.559C24.8436 4.24527 24.7455 3.98056 24.5495 3.76488C24.3534 3.54919 24.0985 3.44135 23.7848 3.44135H20.5495C20.2357 3.44135 19.9808 3.54919 19.7848 3.76488C19.5887 3.98056 19.4906 4.24527 19.4906 4.559V7.73547C19.4906 8.04919 19.5887 8.3139 19.7848 8.52958C19.9808 8.74527 20.2357 8.85311 20.5495 8.85311H23.7848C24.0985 8.85311 24.3534 8.74527 24.5495 8.52958C24.7455 8.3139 24.8436 8.04919 24.8436 7.73547V4.559ZM14.0789 8.85311C12.5887 8.85311 11.324 9.37272 10.2848 10.4119C9.24554 11.4512 8.72593 12.7159 8.72593 14.2061C8.72593 14.9512 8.86318 15.657 9.13769 16.3237C9.4122 16.9904 9.79456 17.5688 10.2848 18.059C10.775 18.5492 11.3436 18.9315 11.9906 19.2061C12.6377 19.4806 13.3338 19.6178 14.0789 19.6178C14.824 19.6178 15.5299 19.4806 16.1965 19.2061C16.8632 18.9315 17.4416 18.5492 17.9318 18.059C18.422 17.5688 18.8044 16.9904 19.0789 16.3237C19.3534 15.657 19.4906 14.9512 19.4906 14.2061C19.4906 13.461 19.3534 12.7649 19.0789 12.1178C18.8044 11.4708 18.422 10.9021 17.9318 10.4119C17.4416 9.92174 16.8632 9.53939 16.1965 9.26488C15.5299 8.99037 14.824 8.85311 14.0789 8.85311ZM3.31417 28.2061C2.4122 28.2061 1.6573 27.8923 1.04946 27.2649C0.441617 26.6374 0.137695 25.8727 0.137695 24.9708V3.44135C0.137695 2.53939 0.441617 1.77468 1.04946 1.14723C1.6573 0.51978 2.4122 0.206055 3.31417 0.206055H24.8436C25.7455 0.206055 26.5102 0.51978 27.1377 1.14723C27.7651 1.77468 28.0789 2.53939 28.0789 3.44135V24.9708C28.0789 25.8727 27.7651 26.6374 27.1377 27.2649C26.5102 27.8923 25.7455 28.2061 24.8436 28.2061H3.31417Z" fill="#222222"></path>
                                            </g>
                                        </svg>
                                    </a>
                                </div>
                                <?php endif ?>
                                <?php if ($location['facebook']) : ?>
                                <div class="wp-block-button">
                                    <a href="<?= $location['facebook'] ?>" class="wp-block-button__link wp-element-button" style="padding-top:8px;padding-bottom:8px">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="29" height="29" viewBox="0 0 29 29" fill="none">
                                            <g id="Icon">
                                            <path id="Vector" d="M21.1232 0.626465C22.0692 0.626465 22.9602 0.807693 23.7962 1.17015C24.6322 1.53261 25.3637 2.02687 25.9907 2.65293C26.6177 3.27899 27.1127 4.00939 27.4757 4.84414C27.8387 5.67889 28.0202 6.56856 28.0202 7.51314V21.2865C28.0202 22.2311 27.8387 23.1262 27.4757 23.972C27.1127 24.8177 26.6177 25.5536 25.9907 26.1797C25.3637 26.8057 24.6322 27.3 23.7962 27.6624C22.9602 28.0249 22.0692 28.2061 21.1232 28.2061H7.29635C6.35036 28.2061 5.45937 28.0249 4.62338 27.6624C3.78738 27.3 3.05589 26.8057 2.4289 26.1797C1.8019 25.5536 1.30691 24.8177 0.943909 23.972C0.580913 23.1262 0.399414 22.2311 0.399414 21.2865V7.51314C0.399414 6.56856 0.580913 5.67889 0.943909 4.84414C1.30691 4.00939 1.8019 3.27899 2.4289 2.65293C3.05589 2.02687 3.78738 1.53261 4.62338 1.17015C5.45937 0.807693 6.35036 0.626465 7.29635 0.626465H21.1232ZM19.3743 6.65643H16.4703C15.8983 6.65643 15.3703 6.75528 14.8863 6.95298C14.4023 7.15069 13.9843 7.4088 13.6323 7.72732C13.2803 8.04584 12.9998 8.41379 12.7908 8.83117C12.5818 9.24854 12.4773 9.66592 12.4773 10.0833V11.8297H9.90333V15.2565H12.4773V22.1762H15.9423V15.2565H18.5163V11.8297H15.9423V10.973C15.9423 10.6874 16.0468 10.4677 16.2558 10.3139C16.4648 10.1602 16.6463 10.0833 16.8003 10.0833H19.3743V6.65643Z" fill="#222222"></path>
                                            </g>
                                        </svg>
                                    </a>
                                </div>
                                <?php endif ?>
                                <?php if ($location['twitter']) : ?>
                                <div class="wp-block-button">
                                    <a href="<?= $location['twitter'] ?>" class="wp-block-button__link wp-element-button" style="padding-top:8px;padding-bottom:8px">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="29" viewBox="0 0 28 29" fill="none">
                                            <g id="Icon">
                                            <path id="Vector" d="M27.9612 5.71554C27.135 6.91984 26.1773 7.89833 25.0882 8.65102V9.44134C25.0882 11.3231 24.7314 13.233 24.0179 15.1712C23.3043 17.1094 22.2622 18.8594 20.8914 20.4212C19.5206 21.983 17.8213 23.2626 15.7933 24.2599C13.7653 25.2572 11.4368 25.7559 8.80797 25.7559C7.15553 25.7559 5.59699 25.5301 4.13233 25.0784C2.66767 24.6268 1.2969 23.987 0.0200195 23.1591C0.245352 23.1967 0.470684 23.2249 0.696016 23.2438C0.921348 23.2626 1.14668 23.272 1.37201 23.272C2.724 23.272 4.01027 23.0462 5.23082 22.5946C6.45137 22.143 7.54987 21.5408 8.52631 20.7881C7.24942 20.7505 6.13215 20.3647 5.17449 19.6309C4.21683 18.897 3.55022 17.9467 3.17467 16.7801C3.36245 16.8553 3.54083 16.893 3.70983 16.893H4.245C4.77077 16.893 5.25899 16.8365 5.70965 16.7236C4.39522 16.4225 3.30611 15.7545 2.44234 14.7196C1.57857 13.6846 1.14668 12.4709 1.14668 11.0784V11.022C1.55979 11.2102 1.9729 11.3701 2.38601 11.5018C2.79911 11.6335 3.24978 11.6994 3.738 11.6994C2.98689 11.1725 2.37662 10.4951 1.90718 9.66715C1.43773 8.83919 1.20301 7.93597 1.20301 6.95747C1.20301 5.90371 1.4659 4.94403 1.99168 4.07844C3.41878 5.80962 5.14632 7.21151 7.17431 8.28409C9.2023 9.35667 11.3993 9.94941 13.7653 10.0623C13.6526 9.6107 13.5963 9.1779 13.5963 8.76393C13.5963 7.9736 13.7465 7.23032 14.0469 6.53409C14.3474 5.83785 14.7605 5.22629 15.2863 4.69941C15.812 4.17253 16.4223 3.75855 17.1171 3.45747C17.8119 3.1564 18.5536 3.00586 19.3422 3.00586C20.1685 3.00586 20.9383 3.16581 21.6519 3.4857C22.3654 3.80559 22.9851 4.2478 23.5109 4.81231C24.1869 4.69941 24.8253 4.52064 25.4262 4.27602C26.0271 4.0314 26.6092 3.73973 27.1725 3.40102C26.7219 4.79349 25.8769 5.86608 24.6375 6.61876C25.8393 6.43059 26.9472 6.12952 27.9612 5.71554Z" fill="#222222"></path>
                                            </g>
                                        </svg>
                                    </a>
                                </div>
                                <?php endif ?>
                                </div>
                                </div>
                                <?php if ($location['image_url']) : ?>
                                <div class="image-container" style="background: linear-gradient(0deg, #FFE998 0%, #FFE998 100%), url('<?= $location['image_url'][0] ?>'); background-size: cover; background-position: 50%; background-repeat: no-repeat; background-blend-mode: color, normal; height: 80px; width: 80px; border-radius: 50%">
                                <span style="display: none">
                                    <?= $location['image_alt'] ?>
                                </span>
                                </div>
                                </div>
                                <div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
                                <?php else : ?>
                                    <div class="image-container" style="height: 80px; width: 80px;">
                                    </div>
                                </div>
                                    <div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
                                <?php endif ?>
                                </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    </div>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>

    <script>
        // Output the PHP data into a JavaScript variable, so Mapbox can use it
        // json_encode() converts PHP arrays into JavaScript arrays/objects
        const LOCATIONS = <?php echo json_encode($locations); ?> ;
        const SEARCH_LOCATION = <?php echo json_encode($results_coordinates);?>  ;
        // Convert locations to geojson so that we can use cluster method
        function toGeoJsonFeature(data) {
            return {
                type: 'Feature',
                properties: {
                    name: data.name,
                    distance_miles: data.distance_miles,
                    address: data.address,
                    city: data.city,
                    website: data.website,
                    instagram: data.instagram,
                    facebook: data.facebook,
                    image_url: data.image_url[0],
                    image_alt: data.image_alt,
                    twitter: data.twitter
                },
                geometry: {
                    type: 'Point',
                    coordinates: data.coordinates
                }
            };
        }
        let data = LOCATIONS;
        let geoJsonFeatures = data.map(toGeoJsonFeature);
        let geoJson = {
            type: 'FeatureCollection',
            features: geoJsonFeatures
        };

        // Output the Mapbox access token environment variable into a JavaScript constant
        const ACCESS_TOKEN = <?php echo json_encode(env("MAPBOX_ACCESS_TOKEN")) ?>

        mapboxgl.accessToken = ACCESS_TOKEN
        const map = new mapboxgl.Map({
            container: 'map', // container ID
            style: 'mapbox://styles/commonknowledge/cloe9s9ee001u01r0dkm7ch4h', // style URL
            center: [-2, 54], // starting position [lng, lat]
            zoom: 5, // starting zoom
        });
        map.on('load', function() {
            map.addSource('points', {
                type: 'geojson',
                data: geoJson,
                cluster: true,
                clusterMaxZoom: 14, // Max zoom level at which clusters are generated
                clusterRadius: 10 // Radius of each cluster when clustering points
            });

        map.addLayer({
            id: 'clusters',
            type: 'circle',
            source: 'points',
            filter: ['has', 'point_count'],
            paint: {
                // Use point_count to adjust the size of the cluster circle
                'circle-radius': ['step', ['get', 'point_count'], 20, 100, 30, 750, 40],
                'circle-color': '#FFE998',
            }
        });

        map.addLayer({
            id: 'cluster-count',
            type: 'symbol',
            source: 'points',
            filter: ['has', 'point_count'],
            layout: {
                'text-field': '{point_count_abbreviated}',
                'text-size': 18,
                'text-font': ['Instrument Sans Regular']
            }
        });

        map.addLayer({
            id: 'unclustered-point',
            type: 'circle',
            source: 'points',
            filter: ['!', ['has', 'point_count']],
            paint: {
                'circle-color': '#FFE998',
                'circle-radius': 7,
                'circle-stroke-width': 1,
                'circle-stroke-color': '#FFE998'
            }
        });

        map.on('click', 'unclustered-point', function(e) {
            let coordinates = e.features[0].geometry.coordinates.slice();
            let name = e.features[0].properties.name;
            let address = e.features[0].properties.address;

            new mapboxgl.Popup()
                .setLngLat(coordinates)
                .setHTML(`<h4>${name}</h4>
                        <div class="location-metadata">${address}</div>
                            `)
                .addTo(map);
        });
        map.on('click', 'clusters', function(e) {
            let coordinates = e.features[0].geometry.coordinates.slice();

            new mapboxgl.Popup()
                .setLngLat(coordinates)
                .addTo(map);
        });

        // Zoom to search location on search
        if (SEARCH_LOCATION) {
            map.flyTo({
                center: SEARCH_LOCATION,
                zoom: 10,
            });
        }
    });
    </script>
        <?php
    });
