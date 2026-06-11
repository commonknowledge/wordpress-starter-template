<?php

/**
 * Theme Functions
 */

use CommonKnowledge\WordpressStarterTemplate\ThemeOptions;

/**
 * Autoload theme classes from src/. The theme registers its own autoloader
 * so it keeps working when installed as a standalone zip, without the
 * project-level Composer autoloader.
 */
spl_autoload_register(function ($class) {
    $prefix = 'CommonKnowledge\\WordpressStarterTemplate\\';
    if (str_starts_with($class, $prefix)) {
        $path = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
});

ThemeOptions::register();

/**
 * Register every block found in build/ (source in blocks/, built by webpack).
 */
add_action('init', function () {
    foreach (glob(get_template_directory() . '/build/*/block.json') as $metadata) {
        register_block_type(dirname($metadata));
    }
});

add_action('wp_enqueue_scripts', function () {
    $assetFile = get_theme_file_path('build/main.asset.php');
    if (! file_exists($assetFile)) {
        return; // Theme assets have not been built yet: run `npm run build`.
    }
    $asset = include $assetFile;

    wp_enqueue_style('theme-main', get_theme_file_uri('build/main.css'), [], filemtime(get_theme_file_path('build/main.css')));
    wp_enqueue_script('theme-main', get_theme_file_uri('build/main.js'), $asset['dependencies'], $asset['version'], true);
});

add_action('wp_footer', function () {
    /**
     * Output required config and session information to the footer for use by front-end JavaScript.
     */
    $data = [];
    echo '<script type="application/json" id="wordpress-config">' . json_encode($data, JSON_PRETTY_PRINT) . '</script>';
});
