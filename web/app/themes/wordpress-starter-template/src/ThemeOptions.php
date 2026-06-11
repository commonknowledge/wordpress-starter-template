<?php

namespace CommonKnowledge\WordpressStarterTemplate;

/**
 * Theme Options
 *
 * A settings page at Appearance → Theme Options, built on the WordPress
 * Settings API. All options are stored in a single "theme_options" array.
 *
 * To add an option:
 *   1. Add a key to the sanitize() method below.
 *   2. Add a matching add_settings_field() call in registerSettings().
 *   3. Read it anywhere with ThemeOptions::get('your_key').
 */
class ThemeOptions
{
    private const PAGE = 'theme-options';

    private const OPTION = 'theme_options';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addPage']);
        add_action('admin_init', [self::class, 'registerSettings']);
    }

    /**
     * Get a single theme option, with an optional fallback.
     */
    public static function get(string $name, $default = '')
    {
        $options = get_option(self::OPTION, []);

        return $options[$name] ?? $default;
    }

    public static function addPage(): void
    {
        add_theme_page(
            __('Theme Options', 'wordpress-starter-template'),
            __('Theme Options', 'wordpress-starter-template'),
            'manage_options',
            self::PAGE,
            [self::class, 'renderPage'],
        );
    }

    public static function renderPage(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION);
        do_settings_sections(self::PAGE);
        submit_button();
        ?>
            </form>
        </div>
        <?php
    }

    public static function registerSettings(): void
    {
        register_setting(self::OPTION, self::OPTION, [
            'type' => 'array',
            'default' => [],
            'sanitize_callback' => [self::class, 'sanitize'],
        ]);

        add_settings_section(
            'theme_options_general',
            __('General', 'wordpress-starter-template'),
            '__return_null',
            self::PAGE,
        );

        // Example fields: replace or extend these with your own.
        add_settings_field(
            'footer_text',
            __('Footer text', 'wordpress-starter-template'),
            function () {
                ?>
                <input type="text" class="regular-text" name="theme_options[footer_text]"
                    value="<?php echo esc_attr(self::get('footer_text')); ?>">
                <?php
            },
            self::PAGE,
            'theme_options_general',
        );

        add_settings_field(
            'contact_email',
            __('Contact email', 'wordpress-starter-template'),
            function () {
                ?>
                <input type="email" class="regular-text" name="theme_options[contact_email]"
                    value="<?php echo esc_attr(self::get('contact_email')); ?>">
                <?php
            },
            self::PAGE,
            'theme_options_general',
        );
    }

    public static function sanitize($value): array
    {
        $value = is_array($value) ? $value : [];

        return [
            'footer_text' => sanitize_text_field($value['footer_text'] ?? ''),
            'contact_email' => sanitize_email($value['contact_email'] ?? ''),
        ];
    }
}
