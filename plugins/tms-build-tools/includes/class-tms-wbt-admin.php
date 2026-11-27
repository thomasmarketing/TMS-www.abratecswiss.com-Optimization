<?php
defined('ABSPATH') || exit;

final class TMS_WBT_Admin {
    public static function init(): void {
        add_action('admin_menu', [self::class, 'menu']);
    }

    public static function menu(): void {
        // Position 81 places the top-level menu immediately below Settings (normally 80).
        add_menu_page(
            'TMS Build Tools',
            'TMS Build Tools',
            'manage_options',
            'tms-build-tools',
            [self::class, 'render_dashboard'],
            'dashicons-admin-tools',
            81
        );

        // Keep the build workflow ordered: Theme -> Page Importer -> Page Generator.
        add_submenu_page(
            'tms-build-tools',
            '1. Theme Generator',
            '1. Theme Generator',
            'manage_options',
            'tms-build-tools-theme-generator',
            [self::class, 'render_theme_generator']
        );
        add_submenu_page(
            'tms-build-tools',
            '2. Page Importer',
            '2. Page Importer',
            'manage_options',
            'tms-build-tools-page-importer',
            [self::class, 'render_page_importer']
        );
        add_submenu_page(
            'tms-build-tools',
            '3. Page Generator',
            '3. Page Generator',
            'manage_options',
            'tms-build-tools-page-generator',
            [self::class, 'render_page_generator']
        );
    }

    public static function render_dashboard(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'tms-build-tools'));
        }

        ?>
        <div class="wrap">
            <h1>TMS Build Tools</h1>
            <p>Tools for building, migrating, and maintaining the WordPress website.</p>

            <div class="tms-wbt-dashboard-grid" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;max-width:1200px;margin-top:24px;">
                <div style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:24px;">
                    <h2 style="margin-top:0;">1. Theme Generator</h2>
                    <p>Clone the active theme, update its metadata and navigation handling, install the supplied screenshot, and activate the generated theme.</p>
                    <p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=tms-build-tools-theme-generator')); ?>">Open Theme Generator</a></p>
                </div>

                <div style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:24px;">
                    <h2 style="margin-top:0;">2. Page Importer</h2>
                    <p>Build the page structure from the sitemap, choose content or blank pages, then generate the WordPress pages and Primary Menu.</p>
                    <p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=tms-build-tools-page-importer')); ?>">Open Page Importer</a></p>
                </div>

                <div style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:24px;">
                    <h2 style="margin-top:0;">3. Page Generator</h2>
                    <p>Prepare AI-generated ACF assets, update the existing Flexible Content field and theme renderer, and populate a selected page.</p>
                    <p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=tms-build-tools-page-generator')); ?>">Open Page Generator</a></p>
                </div>

                <div style="grid-column:1 / -1;background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:24px;">
                    <h2 style="margin-top:0;">More tools</h2>
                    <p>Additional WordPress build tools can be added here without changing the existing tools.</p>
                    <p><em>Coming soon</em></p>
                </div>
            </div>
        </div>
        <?php
    }

    public static function render_theme_generator(): void {
        if (!current_user_can('manage_options')) wp_die(esc_html__('You do not have permission to access this page.', 'tms-build-tools'));
        $notice=''; $result=null;
        if ($_SERVER['REQUEST_METHOD']==='POST' && sanitize_key(wp_unslash($_POST['tms_wbt_action'] ?? ''))==='generate_theme') {
            check_admin_referer('tms_wbt_theme_generator','tms_wbt_theme_nonce');
            try {
                $theme_name=sanitize_text_field(wp_unslash($_POST['tms_wbt_theme_name'] ?? ''));
                if ($theme_name==='') throw new RuntimeException('Theme name is required.');
                $result=self::generate_theme($theme_name);
                $notice='<div class="notice notice-success"><p><strong>Theme generated and activated successfully.</strong></p></div>';
            } catch (Throwable $e) {
                $notice='<div class="notice notice-error"><p><strong>Theme generation stopped safely:</strong> '.esc_html($e->getMessage()).'</p></div>';
            }
        }
        ?>
        <div class="wrap">
            <h1>1. Theme Generator</h1>
            <p>Clone the currently active theme into a new theme, update its metadata, install the supplied screenshot, and activate the generated theme.</p>
            <?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('tms_wbt_theme_generator','tms_wbt_theme_nonce'); ?>
                <input type="hidden" name="tms_wbt_action" value="generate_theme">
                <table class="form-table">
                    <tr><th scope="row"><label for="tms_wbt_theme_name">Theme Name</label></th><td>
                        <input id="tms_wbt_theme_name" name="tms_wbt_theme_name" type="text" class="regular-text" required placeholder="<?php echo esc_attr(get_option('blogname', '')); ?>">
                        <p class="description">The folder name is derived from the theme name, so “<?php echo esc_html(get_option('blogname', 'RPM Framework')); ?>” becomes <code><?php echo esc_html(sanitize_title(get_option('blogname', 'RPM Framework'))); ?></code></p>
                    </td></tr>
                    <tr><th scope="row"><label for="tms_wbt_screenshot">Theme Screenshot</label></th><td>
                        <input id="tms_wbt_screenshot" name="tms_wbt_screenshot" type="file" accept=".png,.jpg,.jpeg,image/png,image/jpeg" required>
                        <p class="description">Upload the screenshot for the generated theme. The recommended filename is <code>screenshot.png</code> or <code>screenshot.jpg</code>. It will be placed in the generated theme directory.</p>
                    </td></tr>
                </table>
                <?php submit_button('Generate & Activate Theme','primary'); ?>
            </form>
            <?php if ($result): ?><hr><h2>Generated Theme</h2><ul>
                <li><strong>Theme:</strong> <?php echo esc_html($result['theme_name']); ?></li>
                <li><strong>Folder:</strong> <code><?php echo esc_html($result['stylesheet']); ?></code></li>
                <li><strong>Source theme:</strong> <?php echo esc_html($result['source_theme']); ?></li>
                <li><strong>Screenshot:</strong> <?php echo esc_html($result['screenshot']); ?></li>
            </ul><?php endif; ?>
        </div>
        <?php
    }

    private static function generate_theme(string $theme_name): array {
        if (!current_user_can('switch_themes')) throw new RuntimeException('You do not have permission to activate themes.');
        $theme_slug=sanitize_title($theme_name);
        if ($theme_slug==='' || !preg_match('/^[a-z0-9][a-z0-9-]*$/',$theme_slug) || str_contains($theme_slug,'..')) throw new RuntimeException('The theme name does not produce a valid theme folder name.');

        $source=wp_get_theme(); $source_dir=$source->get_stylesheet_directory(); $source_slug=$source->get_stylesheet();
        if (!$source->exists() || !is_dir($source_dir)) throw new RuntimeException('The active theme could not be located.');
        if ($source_slug===$theme_slug) throw new RuntimeException('The new theme folder would be the same as the active theme.');

        $themes_dir=trailingslashit(WP_CONTENT_DIR).'themes'; $destination=trailingslashit($themes_dir).$theme_slug;
        if (file_exists($destination)) throw new RuntimeException('A theme folder named "'.$theme_slug.'" already exists. The generator will not overwrite it.');

        $screenshot=self::handle_theme_screenshot();
        require_once ABSPATH.'wp-admin/includes/file.php'; WP_Filesystem(); global $wp_filesystem;
        if (!$wp_filesystem) { @unlink($screenshot); throw new RuntimeException('WordPress could not initialize its filesystem API.'); }

        $source_css=trailingslashit($source_dir).'style.css';
        if (!$wp_filesystem->exists($source_css) || !$wp_filesystem->is_file($source_css)) { @unlink($screenshot); throw new RuntimeException('The active theme does not contain style.css at its theme root.'); }

        if (!self::clone_directory($wp_filesystem, $source_dir, $destination)) {
            @unlink($screenshot);
            throw new RuntimeException('Unable to clone the active theme.');
        }

        try {
            $header_values=[
                'Theme Name'=>$theme_name,
                'Description'=>'Default theme for '.$theme_name,
                'Author'=>'Thomasnet',
                'Author URI'=>'https://thomasnet.com',
            ];

            $destination_css=trailingslashit($destination).'style.css';
            if (!self::update_theme_header_file($wp_filesystem,$destination_css,$header_values,true)) {
                throw new RuntimeException('Unable to update the cloned style.css theme header.');
            }

            $destination_scss=trailingslashit($destination).'style.scss';
            if ($wp_filesystem->exists($destination_scss) && $wp_filesystem->is_file($destination_scss)) {
                if (!self::update_theme_header_file($wp_filesystem,$destination_scss,$header_values,false)) {
                    throw new RuntimeException('Unable to update the cloned style.scss theme header.');
                }
            }

            // Make the cloned theme use its registered WordPress menu location instead of
            // a hard-coded menu name such as "Primary Nav".
            if (!self::update_theme_navigation_to_location($wp_filesystem, $destination)) {
                throw new RuntimeException('Unable to update the cloned theme navigation to use a registered menu location.');
            }

            // The source theme is never modified. The generated theme receives the
            // TMS Custom Page Class body_class integration after cloning.
            if (!self::update_generated_theme_functions($wp_filesystem, $destination)) {
                throw new RuntimeException('Unable to update functions.php in the generated theme with TMS Custom Page Class support.');
            }

            // Install the HTML sitemap shortcode into the generated theme only.
            // The shortcode source and its usage documentation are embedded verbatim
            // from the supplied sitemap-shortcode.txt reference.
            if (!self::update_generated_theme_sitemap_shortcode($wp_filesystem, $destination)) {
                throw new RuntimeException('Unable to add the HTML sitemap shortcode to functions.php in the generated theme.');
            }

            // Remove any screenshot files inherited from the cloned theme before installing the new one.
            foreach (['screenshot.png','screenshot.jpg'] as $existing_screenshot) {
                $existing_path=trailingslashit($destination).$existing_screenshot;
                if ($wp_filesystem->exists($existing_path) && !$wp_filesystem->delete($existing_path,false)) {
                    throw new RuntimeException('Unable to remove the existing '.$existing_screenshot.' from the generated theme.');
                }
            }

            $screenshot_name='screenshot'.self::screenshot_extension($screenshot);
            if (!$wp_filesystem->copy($screenshot,trailingslashit($destination).$screenshot_name,true)) throw new RuntimeException('Unable to copy the screenshot into the generated theme.');

            wp_clean_themes_cache(); $generated=wp_get_theme($theme_slug);
            if (!$generated->exists()) throw new RuntimeException('The generated theme could not be registered by WordPress.');
            switch_theme($theme_slug);
            if (get_stylesheet()!==$theme_slug) throw new RuntimeException('WordPress did not activate the generated theme.');
            @unlink($screenshot);
            return ['theme_name'=>$theme_name,'stylesheet'=>$theme_slug,'source_theme'=>$source->get('Name'),'screenshot'=>$screenshot_name];
        } catch (Throwable $e) {
            @unlink($screenshot); if ($wp_filesystem->exists($destination)) $wp_filesystem->delete($destination,true); throw $e;
        }
    }

    /**
     * Update WordPress theme metadata in a stylesheet header.
     *
     * style.css is required for WordPress theme registration. style.scss is optional
     * and is updated when present. Existing fields are replaced; missing fields are
     * inserted into a standard comment header rather than causing a false failure.
     */
    private static function update_theme_header_file($filesystem, string $path, array $headers, bool $required): bool {
        if (!$filesystem->exists($path) || !$filesystem->is_file($path)) {
            return !$required;
        }

        $contents = $filesystem->get_contents($path);
        if ($contents === false) {
            return false;
        }

        $header_end = strpos($contents, '*/');
        if ($header_end !== false && $header_end < 16384) {
            $header = substr($contents, 0, $header_end + 2);
            $body = substr($contents, $header_end + 2);
        } else {
            $header = "/*\n */";
            $body = $contents;
        }

        foreach ($headers as $label => $value) {
            $pattern = '/^([ \t]*\*?[ \t]*' . preg_quote($label, '/') . ':[ \t]*).*$/mi';
            $updated = preg_replace_callback($pattern, static function ($matches) use ($value) {
                return $matches[1] . $value;
            }, $header, 1, $count);

            if ($updated === null) {
                return false;
            }

            if ($count === 1) {
                $header = $updated;
                continue;
            }

            $closing_pos = strrpos($header, '*/');
            if ($closing_pos === false) {
                return false;
            }

            $before_closing = rtrim(substr($header, 0, $closing_pos));
            $header = $before_closing . "\n * " . $label . ': ' . $value . "\n */";
        }

        return $filesystem->put_contents($path, $header . $body, FS_CHMOD_FILE);
    }

    /**
     * Add TMS Custom Page Class body_class support to the generated theme only.
     * The source/active theme is never modified.
     */
    private static function update_generated_theme_functions($filesystem, string $theme_dir): bool {
        $path = trailingslashit($theme_dir) . 'functions.php';
        $marker = '/* TMS Build Tools: TMS Custom Page Class */';
        $code = <<<PHP

$marker
if (!function_exists('tms_wbt_custom_page_body_class')) {
    function tms_wbt_custom_page_body_class(\$classes) {
        if (!is_singular()) {
            return \$classes;
        }

        \$post_id = get_queried_object_id();
        if (!\$post_id) {
            return \$classes;
        }

        \$value = get_post_meta(\$post_id, 'tms_custom_page_class', true);
        if (!is_string(\$value) || trim(\$value) === '') {
            return \$classes;
        }

        \$custom_classes = preg_split('/\\s+/', trim(\$value));
        if (!is_array(\$custom_classes)) {
            return \$classes;
        }

        foreach (\$custom_classes as \$custom_class) {
            \$custom_class = sanitize_html_class(\$custom_class);
            if (\$custom_class !== '') {
                \$classes[] = \$custom_class;
            }
        }

        return array_values(array_unique(\$classes));
    }
    add_filter('body_class', 'tms_wbt_custom_page_body_class');
}
PHP;

        if ($filesystem->exists($path) && $filesystem->is_file($path)) {
            $contents = $filesystem->get_contents($path);
            if ($contents === false) {
                return false;
            }
            if (strpos($contents, $marker) !== false) {
                return true;
            }
        } else {
            $contents = "<?php\n";
        }

        return $filesystem->put_contents($path, rtrim($contents) . $code . "\n", FS_CHMOD_FILE);
    }

    /**
     * Add the supplied HTML sitemap shortcode to the generated theme's functions.php.
     * The source includes its usage instructions and is intentionally retained verbatim.
     * The active/source theme is never modified.
     */
    private static function update_generated_theme_sitemap_shortcode($filesystem, string $theme_dir): bool {
        $path = trailingslashit($theme_dir) . 'functions.php';
        $marker = '/* TMS Build Tools: HTML Sitemap Shortcode */';
        $source = <<<'TMS_SITEMAP'
/**
 * HTML Sitemap Shortcode
 *
 * Usage:
 * [html_sitemap menu="primary-menu"]
 * [html_sitemap menu="primary-menu" all_pages="yes"]
 * [html_sitemap menu="primary-menu" posts="yes"]
 * [html_sitemap menu="primary-menu" include="123,456"]
 * [html_sitemap menu="primary-menu" exclude="789,101"]
 * [html_sitemap menu="primary-menu" exclude_category="news,uncategorized"]
 *
 * Parameters:
 * menu             = WordPress menu slug/name.
 * all_pages        = Include all other published pages not in the menu.
 * posts            = Include all published posts.
 * include          = Comma-separated page/post IDs to include.
 * exclude          = Comma-separated page/post IDs to exclude.
 * exclude_category = Comma-separated category IDs or slugs to exclude from posts.
 */

function tms_html_sitemap($atts) {

    $atts = shortcode_atts([
        'menu'             => '',
        'all_pages'        => 'no',
        'posts'            => 'no',
        'include'          => '',
        'exclude'          => '',
        'exclude_category' => '',
    ], $atts, 'html_sitemap');

    if (!$atts['menu']) {
        return '';
    }

    /*
     * IDs.
     */
    $include = array_filter(
        array_map(
            'absint',
            explode(',', $atts['include'])
        )
    );

    $exclude = array_filter(
        array_map(
            'absint',
            explode(',', $atts['exclude'])
        )
    );

    /*
     * Exclude categories by ID or slug.
     */
    $cat_ids = [];

    foreach (
        array_filter(
            array_map(
                'trim',
                explode(',', $atts['exclude_category'])
            )
        ) as $cat
    ) {
        $term = is_numeric($cat)
            ? get_category(absint($cat))
            : get_category_by_slug(sanitize_title($cat));

        if ($term && !is_wp_error($term)) {
            $cat_ids[] = (int) $term->term_id;
        }
    }

    $cat_ids = array_unique($cat_ids);

    /*
     * Get menu.
     */
    $menu = wp_get_nav_menu_object($atts['menu']);

    if (!$menu) {
        return '';
    }

    $items = wp_get_nav_menu_items($menu->term_id);

    if (!$items) {
        return '';
    }

    $by_parent = [];
    $menu_object_ids = [];

    /*
     * Build menu tree.
     */
    foreach ($items as $item) {

        $object_id = (int) $item->object_id;

        /*
         * Excluded IDs should not appear in the sitemap.
         */
        if (in_array($object_id, $exclude, true)) {
            continue;
        }

        /*
         * Skip unpublished pages.
         */
        if (
            $item->object === 'page' &&
            get_post_status($object_id) !== 'publish'
        ) {
            continue;
        }

        $menu_object_ids[] = $object_id;

        $by_parent[(int) $item->menu_item_parent][] = $item;
    }

    $menu_object_ids = array_unique($menu_object_ids);

    /*
     * Keep track of everything that has already been rendered.
     *
     * This prevents duplicate pages/posts when the same item
     * exists in multiple sitemap sources:
     *
     * - Front page
     * - Menu
     * - all_pages
     * - include
     * - posts
     */
    $rendered_ids = [];

    /*
     * Render menu recursively.
     */
    $render = function ($parent = 0) use (
        &$render,
        &$by_parent,
        &$rendered_ids,
        &$exclude
    ) {
        if (empty($by_parent[$parent])) {
            return '';
        }

        $html = '<ul>';

        foreach ($by_parent[$parent] as $item) {

            $object_id = (int) $item->object_id;

            /*
             * Prevent duplicate menu object IDs.
             *
             * A WordPress menu can technically contain the same
             * page multiple times.
             */
            if (
                $object_id &&
                in_array($object_id, $rendered_ids, true)
            ) {
                continue;
            }

            /*
             * Don't render explicitly excluded IDs.
             */
            if (
                $object_id &&
                in_array($object_id, $exclude, true)
            ) {
                continue;
            }

            if ($object_id) {
                $rendered_ids[] = $object_id;
            }

            $html .= '<li>';
            $html .= '<a href="' . esc_url($item->url) . '">';
            $html .= esc_html($item->title);
            $html .= '</a>';

            $html .= $render((int) $item->ID);

            $html .= '</li>';
        }

        return $html . '</ul>';
    };

    /*
     * Root sitemap list.
     *
     * Everything added after the menu is appended to this
     * same root <ul>.
     */
    $html = '<div class="html-sitemap"><ul>';

    /*
     * Front page.
     *
     * The front page is handled separately because it may not
     * exist in the primary menu.
     */
    $front_page_id = (int) get_option('page_on_front');

    if (
        $front_page_id &&
        !in_array($front_page_id, $exclude, true) &&
        !in_array($front_page_id, $menu_object_ids, true) &&
        !in_array($front_page_id, $rendered_ids, true) &&
        get_post_status($front_page_id) === 'publish'
    ) {
        $title = get_the_title($front_page_id) ?: __('Home', 'tms');

        $html .= '<li>';
        $html .= '<a href="' . esc_url(home_url('/')) . '">';
        $html .= esc_html($title);
        $html .= '</a>';
        $html .= '</li>';

        $rendered_ids[] = $front_page_id;
    }

    /*
     * Main menu.
     *
     * Remove the root <ul> generated by the menu renderer
     * so its <li> elements can be appended to our main list.
     */
    $menu_html = $render();

    if ($menu_html) {
        $menu_html = preg_replace('/^<ul>|<\/ul>$/', '', $menu_html);
        $html .= $menu_html;
    }

    /*
     * All other published pages.
     *
     * Only pages that aren't already represented by:
     *
     * - the menu
     * - the front page
     * - explicitly excluded IDs
     * - already rendered IDs
     *
     * are added.
     */
    if (strtolower($atts['all_pages']) === 'yes') {

        $all_pages_exclude = array_unique(
            array_merge(
                $exclude,
                $menu_object_ids,
                $rendered_ids
            )
        );

        $pages = get_posts([
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'post__not_in'   => $all_pages_exclude,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        foreach ($pages as $page) {

            $page_id = (int) $page->ID;

            /*
             * Extra safety check against duplicates.
             */
            if (
                in_array($page_id, $exclude, true) ||
                in_array($page_id, $rendered_ids, true)
            ) {
                continue;
            }

            $html .= '<li>';
            $html .= '<a href="' . esc_url(get_permalink($page)) . '">';
            $html .= esc_html(get_the_title($page));
            $html .= '</a>';
            $html .= '</li>';

            $rendered_ids[] = $page_id;
        }
    }

    /*
     * Explicitly included pages/posts.
     *
     * Exclude, existing menu items, front page and already
     * rendered items take priority.
     */
    $include = array_diff(
        $include,
        $exclude,
        $menu_object_ids,
        $rendered_ids
    );

    if ($include) {

        $included_posts = get_posts([
            'post_type'      => ['page', 'post'],
            'post_status'    => 'publish',
            'post__in'       => $include,
            'posts_per_page' => -1,
            'orderby'        => 'post__in',
            'no_found_rows'  => true,
        ]);

        foreach ($included_posts as $post) {

            $post_id = (int) $post->ID;

            /*
             * Prevent duplicate items.
             */
            if (
                in_array($post_id, $exclude, true) ||
                in_array($post_id, $rendered_ids, true)
            ) {
                continue;
            }

            /*
             * Exclude posts belonging to excluded categories.
             */
            if (
                $post->post_type === 'post' &&
                $cat_ids &&
                has_category($cat_ids, $post)
            ) {
                continue;
            }

            $html .= '<li>';
            $html .= '<a href="' . esc_url(get_permalink($post)) . '">';
            $html .= esc_html(get_the_title($post));
            $html .= '</a>';
            $html .= '</li>';

            $rendered_ids[] = $post_id;
        }
    }

    /*
     * Posts.
     */
    if (strtolower($atts['posts']) === 'yes') {

        $post_args = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => -1,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'post__not_in'        => array_unique(
                array_merge(
                    $exclude,
                    $include,
                    $rendered_ids
                )
            ),
        ];

        /*
         * Exclude posts by category.
         */
        if ($cat_ids) {
            $post_args['category__not_in'] = $cat_ids;
        }

        $posts = get_posts($post_args);

        foreach ($posts as $post) {

            $post_id = (int) $post->ID;

            /*
             * Extra safety check against duplicates.
             */
            if (
                in_array($post_id, $exclude, true) ||
                in_array($post_id, $rendered_ids, true)
            ) {
                continue;
            }

            $html .= '<li>';
            $html .= '<a href="' . esc_url(get_permalink($post)) . '">';
            $html .= esc_html(get_the_title($post));
            $html .= '</a>';
            $html .= '</li>';

            $rendered_ids[] = $post_id;
        }
    }

    return $html . '</ul></div>';
}

add_shortcode('html_sitemap', 'tms_html_sitemap');
TMS_SITEMAP;

        if ($filesystem->exists($path) && $filesystem->is_file($path)) {
            $contents = $filesystem->get_contents($path);
            if ($contents === false) {
                return false;
            }
            if (strpos($contents, $marker) !== false || strpos($contents, "add_shortcode('html_sitemap', 'tms_html_sitemap');") !== false) {
                return true;
            }
        } else {
            $contents = "<?php\n";
        }

        return $filesystem->put_contents(
            $path,
            rtrim($contents) . "\n\n" . $marker . "\n" . rtrim($source) . "\n",
            FS_CHMOD_FILE
        );
    }

    /**
     * Convert hard-coded navigation menu usage in the cloned theme to a registered
     * WordPress menu location. This keeps menu assignment in Appearance > Menus >
     * Manage Locations, rather than coupling the theme to a menu name.
     */
    private static function update_theme_navigation_to_location($filesystem, string $theme_dir): bool {
        $location = self::detect_primary_menu_location($filesystem, $theme_dir);
        if ($location === '') {
            return true;
        }

        $php_files = self::find_theme_php_files($filesystem, $theme_dir);
        foreach ($php_files as $path) {
            $contents = $filesystem->get_contents($path);
            if ($contents === false) {
                return false;
            }

            $updated = self::replace_hardcoded_nav_menu_name($contents, $location);
            if ($updated !== $contents && !$filesystem->put_contents($path, $updated, FS_CHMOD_FILE)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find a sensible registered location from the cloned theme's PHP source.
     */
    private static function detect_primary_menu_location($filesystem, string $theme_dir): string {
        $files = self::find_theme_php_files($filesystem, $theme_dir);
        $candidates = [];

        foreach ($files as $path) {
            $contents = $filesystem->get_contents($path);
            if ($contents === false) {
                continue;
            }

            if (preg_match_all('/[\'\"]([^\'\"]+)[\'\"]\s*=>\s*[\'\"]([^\'\"]*(?:primary|navigation|nav|menu)[^\'\"]*)[\'\"]/i', $contents, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $key = sanitize_key($match[1]);
                    $label = strtolower(trim(wp_strip_all_tags($match[2])));
                    if ($key !== '') {
                        $candidates[$key] = $label;
                    }
                }
            }

            if (preg_match_all('/register_nav_menu\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]\s*\)/i', $contents, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $key = sanitize_key($match[1]);
                    $label = strtolower(trim(wp_strip_all_tags($match[2])));
                    if ($key !== '') {
                        $candidates[$key] = $label;
                    }
                }
            }
        }

        foreach ($candidates as $key => $label) {
            if (str_contains($label, 'primary') && (str_contains($label, 'navigation') || str_contains($label, 'nav'))) {
                return $key;
            }
        }

        foreach (['primary', 'primary_navigation', 'primary-menu', 'main-menu', 'header-menu'] as $preferred) {
            if (isset($candidates[$preferred])) {
                return $preferred;
            }
        }

        return '';
    }

    /**
     * Replace only wp_nav_menu() calls that explicitly target the hard-coded
     * menu name \"Primary Nav\". Other menu names and unrelated strings remain intact.
     */
    private static function replace_hardcoded_nav_menu_name(string $contents, string $location): string {
        $replacement = "'theme_location' => '" . esc_js($location) . "'";

        // Array arguments: wp_nav_menu(['menu' => 'Primary Nav', ...]).
        $contents = preg_replace(
            '/(wp_nav_menu\s*\(\s*\[[^\]]*?)\[\'\"]menu[\'\"]\s*=>\s*[\'\"]Primary Nav[\'\"]([^\]]*\])/is',
            '$1' . $replacement . '$2',
            $contents
        ) ?? $contents;

        // Array arguments using a quoted key without bracket shorthand.
        $contents = preg_replace(
            '/(wp_nav_menu\s*\(\s*array\s*\([^)]*?)((?:[\'\"]menu[\'\"]\s*=>\s*)[\'\"]Primary Nav[\'\"])(.*?\)\s*\))/is',
            '$1' . $replacement . '$3',
            $contents
        ) ?? $contents;

        // String argument: wp_nav_menu('Primary Nav'). This is an uncommon form;
        // convert it to an options array rather than leaving the menu name hard-coded.
        $contents = preg_replace(
            '/wp_nav_menu\s*\(\s*[\'\"]Primary Nav[\'\"]\s*\)/i',
            'wp_nav_menu(array(' . $replacement . '))',
            $contents
        ) ?? $contents;

        return $contents;
    }

    /**
     * Return PHP files under the theme directory using the WordPress filesystem API.
     */
    private static function find_theme_php_files($filesystem, string $theme_dir): array {
        $files = [];
        $items = $filesystem->dirlist($theme_dir, true, true);
        if (!is_array($items)) {
            return $files;
        }

        foreach ($items as $name => $item) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $path = trailingslashit($theme_dir) . $name;
            if (($item['type'] ?? '') === 'd') {
                $files = array_merge($files, self::find_theme_php_files($filesystem, $path));
            } elseif (($item['type'] ?? '') === 'f' && preg_match('/\.php$/i', $name)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * Recursively clone a theme directory using the WordPress filesystem API.
     */
    private static function clone_directory($filesystem, string $source, string $destination): bool {
        if (!$filesystem->is_dir($source)) {
            return false;
        }

        if (!$filesystem->mkdir($destination, FS_CHMOD_DIR)) {
            return false;
        }

        $items = $filesystem->dirlist($source, true, true);
        if (!is_array($items)) {
            return false;
        }

        foreach ($items as $name => $item) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $source_path = trailingslashit($source) . $name;
            $destination_path = trailingslashit($destination) . $name;

            if (($item['type'] ?? '') === 'd') {
                if (!self::clone_directory($filesystem, $source_path, $destination_path)) {
                    return false;
                }
            } elseif (($item['type'] ?? '') === 'f') {
                if (!$filesystem->copy($source_path, $destination_path, true, FS_CHMOD_FILE)) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function handle_theme_screenshot(): string {
        if (empty($_FILES['tms_wbt_screenshot']) || !is_array($_FILES['tms_wbt_screenshot'])) throw new RuntimeException('Please upload a theme screenshot.');
        $file=$_FILES['tms_wbt_screenshot'];
        if (($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('The screenshot upload failed.');
        if (($file['size']??0)>5*1024*1024) throw new RuntimeException('The screenshot exceeds the 5 MB safety limit.');
        $check=wp_check_filetype_and_ext($file['tmp_name'],$file['name'],['png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg']);
        if (empty($check['ext']) || !in_array($check['ext'],['png','jpg','jpeg'],true)) throw new RuntimeException('Only valid PNG or JPEG screenshots are accepted.');
        $info=@getimagesize($file['tmp_name']);
        if (!$info || !in_array($info[2],[IMAGETYPE_PNG,IMAGETYPE_JPEG],true)) throw new RuntimeException('The uploaded screenshot is not a valid PNG or JPEG image.');
        $tmp=wp_tempnam(sanitize_file_name($file['name']));
        if (!$tmp || !move_uploaded_file($file['tmp_name'],$tmp)) { if($tmp) @unlink($tmp); throw new RuntimeException('Unable to securely store the uploaded screenshot.'); }
        return $tmp;
    }

    private static function screenshot_extension(string $path): string {
        return (wp_check_filetype($path)['ext']??'')==='png'?'.png':'.jpg';
    }


    public static function render_page_generator(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'tms-build-tools'));
        }

        $required_renderer = trailingslashit(get_stylesheet_directory()) . 'parts/shared/flexible-content.php';
        if (!is_file($required_renderer) || !is_readable($required_renderer)) {
            echo '<div class="wrap">';
            echo '<h1>3. Page Generator</h1>';
            echo '<div class="notice notice-error" style="border-left-color:#d63638;"><p style="color:#d63638;font-weight:600;">We only support RPM Understrap and RPM WordPress framework at the moment.</p></div>';
            echo '</div>';
            return;
        }

        $mode = sanitize_key(wp_unslash($_POST['tms_wbt_page_mode'] ?? get_option('tms_wbt_page_mode', 'manual')));
        if (!in_array($mode, ['manual', 'openai'], true)) {
            $mode = 'manual';
        }

        $notice = '';
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = sanitize_key(wp_unslash($_POST['tms_wbt_page_action'] ?? ''));

            try {
                if ($action === 'set_mode') {
                    check_admin_referer('tms_wbt_page_generator', 'tms_wbt_page_nonce');
                    update_option('tms_wbt_page_mode', $mode, false);
                } elseif ($action === 'generate_page') {
                    check_admin_referer('tms_wbt_page_generator', 'tms_wbt_page_nonce');
                    if (!current_user_can('manage_options')) {
                        throw new RuntimeException('You do not have permission to generate a page.');
                    }

                    $page_id = absint($_POST['tms_wbt_page_id'] ?? 0);
                    $page = get_post($page_id);
                    if (!$page || $page->post_type !== 'page') {
                        throw new RuntimeException('Please select a valid existing WordPress page.');
                    }

                    $result = self::generate_page_from_uploaded_assets($page_id);
                    $notice = '<div class="notice notice-success"><p><strong>Page generated successfully.</strong> The active theme renderer and selected page were updated.</p></div>';
                }
            } catch (Throwable $e) {
                $notice = '<div class="notice notice-error"><p><strong>Page generation stopped safely:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
            }
        }

        $pages = get_pages([
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'sort_column' => 'post_title',
            'sort_order' => 'ASC',
        ]);
        $prompt = self::get_page_generator_prompt();
        ?>
        <div class="wrap">
            <h1>3. Page Generator</h1>
            <p>Import the AI-generated ACF configuration through ACF Pro → Tools → Import first. Then upload the renderer and page layout here and select the target WordPress page.</p>
            <?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <form method="post" class="tms-wbt-page-mode-form">
                <?php wp_nonce_field('tms_wbt_page_generator', 'tms_wbt_page_nonce'); ?>
                <input type="hidden" name="tms_wbt_page_action" value="set_mode">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="tms_wbt_page_mode">Mode</label></th>
                        <td>
                            <select id="tms_wbt_page_mode" name="tms_wbt_page_mode" onchange="this.form.submit()">
                                <option value="manual" <?php selected($mode, 'manual'); ?>>Manual</option>
                                <option value="openai" <?php selected($mode, 'openai'); ?>>OpenAI API</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </form>

            <hr>
            <h2>Step 1 — Prepare Assets</h2>
            <ol class="tms-wbt-step-list">
                <li>
                    <strong>Copy the Page Generator Prompt to your AI model.</strong>
                    
                    <p><button type="button" class="button button-secondary tms-wbt-lightbox-trigger" data-target="tms-wbt-prompt">Open Page Generator Prompt</button></p>
                </li>
                <li>
                    <strong>Upload these reference assets to the AI model:</strong>
                </li>
            </ol>
            <ul class="tms-wbt-asset-list">
                <li>Figma screenshot named exactly <code>screenshot.png</code> or <code>screenshot.jpg</code> (high definition).</li>
                <li>Figma JSON named exactly <code>page-design.json</code>.</li>
                <li>Your active theme's <code>parts/shared/flexible-content.php</code> file, kept as <code>flexible-content.php</code>.</li>
                <li>Your <code>acf-fields.json</code> file exported from <strong>ACF PRO → Tools → Export</strong>, kept as <code>acf-fields.json</code>.</li>
            </ul>
            <ol class="tms-wbt-step-list" start="3">
                <li>
                    <strong>The AI must return these 5 files using these exact filenames:</strong>
                </li>
            </ol>
            <ul class="tms-wbt-asset-list">
                <li><code>acf-fields.json</code></li>
                <li><code>flexible-content.php</code></li>
                <li><code>page-layout.json</code></li>
                <li><code>page-style.scss</code></li>
                <li><code>page-script.js</code></li>
            </ul>

            <hr>
            <h2>Step 2 — Upload Assets</h2>

            <form method="post" enctype="multipart/form-data" id="tms-wbt-page-generator-form">
                <?php wp_nonce_field('tms_wbt_page_generator', 'tms_wbt_page_nonce'); ?>
                <input type="hidden" name="tms_wbt_page_action" value="generate_page">
                <input type="hidden" name="tms_wbt_page_mode" value="<?php echo esc_attr($mode); ?>">

                <table class="form-table">
                    <tr>
                        <th scope="row">ACF Field</th>
                        <td>
                            <p><strong>No upload is required in TMS Build Tools for this step.</strong></p>
                            <p>Grab the AI-generated <code>acf-fields.json</code> file and import it using <strong>ACF Pro → Tools → Import</strong> into the existing Flexible Content field/module. Confirm the new TMS layouts and their fields are available in ACF before continuing.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tms_wbt_flexible_content">Flexible Content Renderer</label></th>
                        <td>
                            <input id="tms_wbt_flexible_content" name="tms_wbt_flexible_content" type="file" accept=".php,text/plain,application/x-php" required>
                            <p class="description">Upload <strong>only</strong> a file named exactly <code>flexible-content.php</code>. The file must contain the complete updated Flexible Content renderer.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tms_wbt_page_layout">Page Layout</label></th>
                        <td>
                            <input id="tms_wbt_page_layout" name="tms_wbt_page_layout" type="file" accept=".json,application/json" required>
                            <p class="description">Upload <strong>only</strong> a file named exactly <code>page-layout.json</code>. It contains the page layout data that will be written to the selected WordPress page.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tms_wbt_page_id">Update Page</label></th>
                        <td>
                            <select id="tms_wbt_page_id" name="tms_wbt_page_id" required>
                                <option value="">Select an existing WordPress page</option>
                                <?php foreach ($pages as $page) : ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title . ' (ID ' . $page->ID . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">The selected page receives the layout data from <code>page-layout.json</code>.</p>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button button-primary button-large" id="tms-wbt-page-generator-submit">Generate Page</button>
                </p>
                <div id="tms-wbt-page-generator-processing" class="tms-wbt-page-generator-processing" aria-live="polite" aria-hidden="true">
                    <span class="tms-wbt-spinner" aria-hidden="true"></span>
                    <span>Updating theme renderer and page layout. Please wait&hellip;</span>
                </div>
            </form>

            <?php if ($result) : ?>
                <hr>
                <h2>Generation Log</h2>
                <ul>
                    <?php foreach ($result['log'] as $line) : ?>
                        <li><?php echo esc_html($line); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div id="tms-wbt-prompt" class="tms-wbt-lightbox" hidden>
            <div class="tms-wbt-lightbox__panel">
                <button type="button" class="tms-wbt-lightbox__close" aria-label="Close">&times;</button>
                <div class="tms-wbt-prompt-header">
                    <h2>Page Generator Prompt</h2>
                    <button type="button" class="button button-secondary" id="tms-wbt-copy-prompt" data-copy-target="tms-wbt-prompt-text" title="Copy prompt">Copy prompt</button>
                </div>
                <textarea id="tms-wbt-prompt-text" readonly style="width:100%;min-height:70vh;"><?php echo esc_textarea($prompt); ?></textarea>
                <p id="tms-wbt-copy-status" class="description" aria-live="polite"></p>
            </div>
        </div>
        <style>
            .tms-wbt-step-list { max-width: 1000px; }
            .tms-wbt-step-list > li { margin: 0 0 20px; }
            .tms-wbt-step-list ul { margin-top: 8px; }
            .tms-wbt-asset-list { max-width: 1000px; margin: 8px 0 20px 24px; }
            .tms-wbt-asset-list li { margin-bottom: 6px; }
            .tms-wbt-prompt-header { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:12px; }
            .tms-wbt-prompt-header h2 { margin:0; }
            .tms-wbt-page-generator-processing { display:none; align-items:center; gap:8px; margin:8px 0 16px; font-weight:600; }
            .tms-wbt-page-generator-processing.is-visible { display:flex; }
            .tms-wbt-lightbox[hidden] { display:none !important; }
            .tms-wbt-lightbox { position:fixed; inset:0; z-index:100000; display:flex; align-items:center; justify-content:center; padding:32px; box-sizing:border-box; background:rgba(0,0,0,.68); }
            .tms-wbt-lightbox__panel { position:relative; width:min(1100px,96vw); max-height:90vh; overflow:auto; box-sizing:border-box; background:#fff; border-radius:6px; padding:24px; box-shadow:0 10px 40px rgba(0,0,0,.35); }
            .tms-wbt-lightbox__close { position:absolute; top:10px; right:12px; border:0; background:transparent; font-size:28px; line-height:1; cursor:pointer; color:#50575e; }
            .tms-wbt-lightbox__close:hover { color:#1d2327; }
            body.tms-wbt-modal-open { overflow:hidden; }
            .tms-wbt-spinner { width:16px; height:16px; border:2px solid #dcdcde; border-top-color:#2271b1; border-radius:50%; animation:tms-wbt-page-generator-spin .8s linear infinite; box-sizing:border-box; flex:0 0 16px; }
            @keyframes tms-wbt-page-generator-spin { to { transform:rotate(360deg); } }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.tms-wbt-lightbox-trigger').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var target = document.getElementById(trigger.getAttribute('data-target'));
                    if (target) { target.hidden = false; document.body.classList.add('tms-wbt-modal-open'); }
                });
            });
            document.querySelectorAll('.tms-wbt-lightbox').forEach(function (box) {
                box.addEventListener('click', function (event) {
                    if (event.target === box || event.target.classList.contains('tms-wbt-lightbox__close')) { box.hidden = true; document.body.classList.remove('tms-wbt-modal-open'); }
                });
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.tms-wbt-lightbox:not([hidden])').forEach(function (box) {
                        box.hidden = true;
                    });
                    document.body.classList.remove('tms-wbt-modal-open');
                }
            });
            var copyButton = document.getElementById('tms-wbt-copy-prompt');
            if (copyButton) {
                copyButton.addEventListener('click', function () {
                    var target = document.getElementById(copyButton.getAttribute('data-copy-target'));
                    var status = document.getElementById('tms-wbt-copy-status');
                    if (!target) return;
                    var text = target.value;
                    function copied() {
                        if (status) status.textContent = 'Prompt copied to clipboard.';
                    }
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(copied).catch(function () {
                            target.select();
                            document.execCommand('copy');
                            copied();
                        });
                    } else {
                        target.select();
                        document.execCommand('copy');
                        copied();
                    }
                });
            }
            var form = document.getElementById('tms-wbt-page-generator-form');
            var button = document.getElementById('tms-wbt-page-generator-submit');
            var processing = document.getElementById('tms-wbt-page-generator-processing');
            if (form && button && processing) {
                form.addEventListener('submit', function () {
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                    processing.classList.add('is-visible');
                    processing.setAttribute('aria-hidden', 'false');
                });
            }
        });
        </script>
        <?php
    }

    private static function get_page_generator_prompt(): string {
        $path = TMS_WBT_DIR . 'page-generator-prompt.txt';
        if (is_readable($path)) {
            $contents = file_get_contents($path);
            if ($contents !== false && trim($contents) !== '') {
                return $contents;
            }
        }
        return 'Generate exactly these five files: acf-fields.json, flexible-content.php, page-layout.json, page-style.scss, and page-script.js.';
    }

    private static function generate_page_from_uploaded_assets(int $page_id): array {
        if (!function_exists('acf_get_field') || !function_exists('get_field') || !function_exists('update_field')) {
            throw new RuntimeException('ACF Pro is required for Page Generator.');
        }

        self::validate_uploaded_filename('tms_wbt_flexible_content', ['flexible-content.php'], 'flexible-content.php');
        self::validate_uploaded_filename('tms_wbt_page_layout', ['page-layout.json'], 'page-layout.json');

        $renderer = self::read_uploaded_text_file('tms_wbt_flexible_content', 'flexible-content.php', 5 * 1024 * 1024);
        $layout_json = self::read_uploaded_text_file('tms_wbt_page_layout', 'page-layout.json', 5 * 1024 * 1024);
        $layout_definition = json_decode($layout_json, true);
        if (!is_array($layout_definition) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('page-layout.json is not valid JSON.');
        }

        $field = self::find_flexible_content_field_for_page_layout($page_id, $layout_definition);
        $page_layouts = self::validate_and_prepare_page_layout($layout_definition, $field);
        $theme_file = self::locate_active_theme_flexible_renderer();
        if ($theme_file === '') {
            throw new RuntimeException('Could not locate an existing flexible-content.php in the active theme. The generated flexible-content.php must replace the existing renderer safely.');
        }

        $target_field_key = (string) ($field['key'] ?? '');
        $target_field_name = (string) ($field['name'] ?? '');
        if ($target_field_key === '' || $target_field_name === '') {
            throw new RuntimeException('The destination ACF Flexible Content field could not be resolved.');
        }

        $resolved_layouts = self::resolve_page_layout_images($page_layouts, $field);
        $original_renderer = file_get_contents($theme_file);
        if ($original_renderer === false || trim($original_renderer) === '') {
            throw new RuntimeException('The existing active theme renderer could not be read before the update. No changes were made.');
        }
        $original_page_value = get_post_meta($page_id, $target_field_name, true);
        $original_page_reference = get_post_meta($page_id, '_' . $target_field_name, true);
        $log = [];

        try {
            self::replace_active_theme_renderer($theme_file, $renderer);
            $log[] = 'Updated active theme renderer: ' . ltrim(str_replace(trailingslashit(get_stylesheet_directory()), '', $theme_file), '/\\') . '.';

            $updated = update_field($target_field_key, $resolved_layouts, $page_id);
            if ($updated === false) {
                $stored = get_field($target_field_key, $page_id, false);
                if (!self::page_layout_values_match($stored, $resolved_layouts)) {
                    update_post_meta($page_id, $target_field_name, $resolved_layouts);
                    update_post_meta($page_id, '_' . $target_field_name, $target_field_key);
                    $raw_stored = get_post_meta($page_id, $target_field_name, true);
                    if (!self::page_layout_values_match($raw_stored, $resolved_layouts)) {
                        throw new RuntimeException(
                            'The page layout could not be written to the selected page. ACF field: ' .
                            $target_field_name . ' (key ' . $target_field_key . ', page ID ' . $page_id . ').'
                        );
                    }
                    $log[] = 'ACF reported no direct update; verified the page layout through the WordPress post-meta fallback.';
                }
            }

            $log[] = 'Updated page: ' . get_the_title($page_id) . ' (ID ' . $page_id . ').';
            $log[] = 'Page style: review the AI-generated page-style.scss and merge the appropriate SCSS into the theme/page SCSS file manually.';
            return ['log' => $log];
        } catch (Throwable $e) {
            if ($original_page_value === '' || $original_page_value === null || $original_page_value === false) {
                delete_post_meta($page_id, $target_field_name);
            } else {
                update_post_meta($page_id, $target_field_name, $original_page_value);
            }
            if ($original_page_reference === '' || $original_page_reference === null || $original_page_reference === false) {
                delete_post_meta($page_id, '_' . $target_field_name);
            } else {
                update_post_meta($page_id, '_' . $target_field_name, $original_page_reference);
            }

            if (is_string($original_renderer) && $original_renderer !== '') {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
                global $wp_filesystem;
                if ($wp_filesystem) {
                    $wp_filesystem->put_contents($theme_file, $original_renderer, FS_CHMOD_FILE);
                }
            }

            throw new RuntimeException('Page generation stopped safely and all changes from this attempt were rolled back: ' . $e->getMessage(), 0, $e);
        }
    }

    private static function find_flexible_content_field_for_page_layout(int $page_id, array $layout_definition): array {
        if (!function_exists('acf_get_field_groups') || !function_exists('acf_get_fields')) {
            throw new RuntimeException('ACF Pro is required to resolve the destination Flexible Content field.');
        }

        $required_layouts = [];
        foreach (self::extract_page_layout_rows($layout_definition) as $row) {
            if (!is_array($row)) {
                continue;
            }

            // page-layout.json generated by the project prompt uses `layout`
            // for the ACF Flexible Content layout name. ACF's internal page
            // value uses `acf_fc_layout`; accept both representations here.
            $layout_name = '';
            if (!empty($row['layout'])) {
                $layout_name = sanitize_key((string) $row['layout']);
            } elseif (!empty($row['acf_fc_layout'])) {
                $layout_name = sanitize_key((string) $row['acf_fc_layout']);
            }

            if ($layout_name !== '') {
                $required_layouts[$layout_name] = true;
            }
        }
        if (!$required_layouts) {
            throw new RuntimeException('page-layout.json does not contain any Flexible Content layouts. Expected Flexible Content rows using "flexible_content", "layouts", or a supported nested content container.');
        }

        $groups = acf_get_field_groups(['post_id' => $page_id]);
        if (!is_array($groups) || !$groups) {
            $groups = acf_get_field_groups();
        }

        $matches = [];
        $walk = static function (array $fields) use (&$walk, &$matches, $required_layouts): void {
            foreach ($fields as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                if (($candidate['type'] ?? '') === 'flexible_content' && !empty($candidate['key'])) {
                    $available = [];
                    foreach ((array) ($candidate['layouts'] ?? []) as $layout) {
                        if (is_array($layout) && !empty($layout['name'])) {
                            $available[sanitize_key((string) $layout['name'])] = true;
                        }
                    }
                    $matches_all = true;
                    foreach ($required_layouts as $name => $_) {
                        if (!isset($available[$name])) {
                            $matches_all = false;
                            break;
                        }
                    }
                    if ($matches_all) {
                        $matches[(string) $candidate['key']] = $candidate;
                    }
                }
                if (!empty($candidate['sub_fields']) && is_array($candidate['sub_fields'])) {
                    $walk($candidate['sub_fields']);
                }
                foreach ((array) ($candidate['layouts'] ?? []) as $layout) {
                    if (is_array($layout) && !empty($layout['sub_fields']) && is_array($layout['sub_fields'])) {
                        $walk($layout['sub_fields']);
                    }
                }
            }
        };

        foreach ($groups as $group) {
            $fields = acf_get_fields($group);
            if (is_array($fields)) {
                $walk($fields);
            }
        }

        if (count($matches) === 1) {
            return acf_get_field((string) array_key_first($matches), true) ?: reset($matches);
        }

        if (count($matches) > 1) {
            $labels = [];
            foreach ($matches as $candidate) {
                $labels[] = (string) ($candidate['label'] ?? $candidate['name'] ?? $candidate['key']);
            }
            throw new RuntimeException('Multiple ACF Flexible Content fields contain all layouts referenced by page-layout.json (' . implode(', ', $labels) . '). The page cannot be generated safely because there is no longer a field selector.');
        }

        throw new RuntimeException('No ACF Flexible Content field on the selected page contains all layouts referenced by page-layout.json. Import acf-fields.json through ACF Tools first, then run Page Generator again.');
    }

    private static function validate_and_prepare_page_layout(array $layout_definition, array $field): array {
        $layout_rows = self::extract_page_layout_rows($layout_definition);
        if (!$layout_rows) {
            throw new RuntimeException('page-layout.json does not contain any Flexible Content layouts. Expected Flexible Content rows using "flexible_content", "layouts", or a supported nested content container.');
        }

        $available = [];
        foreach ((array) ($field['layouts'] ?? []) as $layout) {
            if (!is_array($layout) || empty($layout['name'])) {
                continue;
            }
            $available[sanitize_key((string) $layout['name'])] = true;
        }

        if (!$available) {
            throw new RuntimeException('The selected ACF Flexible Content field does not contain any layouts.');
        }

        $prepared = [];
        foreach ($layout_rows as $index => $row) {
            if (!is_array($row)) {
                throw new RuntimeException('page-layout.json contains an invalid Flexible Content row at index ' . (int) $index . '.');
            }

            // The Page Generator prompt intentionally uses the simpler
            // "layout" property. ACF itself stores this as "acf_fc_layout".
            // Normalize it here before writing the value through ACF.
            $layout_name = '';
            if (!empty($row['layout'])) {
                $layout_name = sanitize_key((string) $row['layout']);
            } elseif (!empty($row['acf_fc_layout'])) {
                $layout_name = sanitize_key((string) $row['acf_fc_layout']);
            }

            if ($layout_name === '') {
                throw new RuntimeException('page-layout.json contains a Flexible Content row without a "layout" value at index ' . (int) $index . '.');
            }
            if (!isset($available[$layout_name])) {
                throw new RuntimeException('page-layout.json references the ACF Flexible Content layout "' . $layout_name . '", but that layout is not available in the selected field.');
            }

            unset($row['layout']);
            $row['acf_fc_layout'] = $layout_name;
            $prepared[] = $row;
        }

        return $prepared;
    }

    /**
     * Normalize the supported page-layout.json Flexible Content representations.
     *
     * The generator prompt documents `flexible_content` as the conceptual page
     * content container, while older generated files used a top-level `layouts`
     * array. Accept both so the importer remains compatible with either form.
     */
    private static function extract_page_layout_rows(array $layout_definition): array {
        // The current Page Generator prompt documents `flexible_content` as the
        // conceptual content container, while older generated files used
        // `layouts`. Accept both, including a nested `layouts`/`rows` container.
        $candidates = [];

        foreach (['flexible_content', 'layouts', 'page_layout', 'modules', 'content'] as $key) {
            if (!array_key_exists($key, $layout_definition)) {
                continue;
            }

            $value = $layout_definition[$key];
            if (is_array($value)) {
                $candidates[] = $value;
            }
        }

        foreach ($candidates as $candidate) {
            if (self::is_page_layout_row_list($candidate)) {
                return array_values($candidate);
            }

            foreach (['flexible_content', 'layouts', 'rows', 'items', 'modules', 'content'] as $nested_key) {
                if (isset($candidate[$nested_key]) && is_array($candidate[$nested_key]) && self::is_page_layout_row_list($candidate[$nested_key])) {
                    return array_values($candidate[$nested_key]);
                }
            }
        }

        // Last-resort compatibility for AI output that wraps the Flexible
        // Content rows in an otherwise unnamed object. Only accept arrays whose
        // direct children clearly look like ACF Flexible Content rows; this
        // prevents arbitrary field/value arrays from being mistaken for layouts.
        foreach ($layout_definition as $value) {
            if (is_array($value) && self::is_page_layout_row_list($value)) {
                return array_values($value);
            }
        }

        return [];
    }

    private static function is_page_layout_row_list(array $value): bool {
        if ($value === []) {
            return false;
        }

        $found = false;
        foreach ($value as $row) {
            if (!is_array($row)) {
                return false;
            }
            if (!empty($row['layout']) || !empty($row['acf_fc_layout'])) {
                $found = true;
                continue;
            }
            return false;
        }

        return $found;
    }

    private static function validate_uploaded_filename(string $field_name, array $allowed_names, string $expected_label): void {
        if (empty($_FILES[$field_name]) || !is_array($_FILES[$field_name])) {
            throw new RuntimeException('Missing required input file: ' . $expected_label . '.');
        }

        $file = $_FILES[$field_name];
        $error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The input file for ' . $expected_label . ' could not be uploaded. Please upload the exact file named ' . $expected_label . '.');
        }

        $name = isset($file['name']) ? wp_basename((string) $file['name']) : '';
        if (!in_array($name, $allowed_names, true)) {
            throw new RuntimeException('Invalid input filename: "' . $name . '". Expected exactly: ' . implode(' or ', $allowed_names) . '. No changes were made.');
        }
    }

    private static function read_uploaded_text_file(string $field_name, string $expected_name, int $max_bytes): string {
        if (empty($_FILES[$field_name]) || !is_array($_FILES[$field_name])) {
            throw new RuntimeException('Please upload ' . $expected_name . '.');
        }
        $file = $_FILES[$field_name];
        if (!empty($file['error']) && (int) $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed for ' . $expected_name . '.');
        }
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size <= 0 || $size > $max_bytes) {
            throw new RuntimeException($expected_name . ' exceeds the allowed file size or is empty.');
        }
        $tmp = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('The uploaded ' . $expected_name . ' could not be verified.');
        }
        $content = file_get_contents($tmp);
        if ($content === false || trim($content) === '') {
            throw new RuntimeException($expected_name . ' is empty.');
        }
        $basename = sanitize_file_name((string) ($file['name'] ?? ''));
        if ($basename !== $expected_name) {
            throw new RuntimeException('Please upload the file with the exact filename ' . $expected_name . '.');
        }
        return $content;
    }

    private static function locate_active_theme_flexible_renderer(): string {
        $renderer = trailingslashit(get_stylesheet_directory()) . 'parts/shared/flexible-content.php';
        return (is_file($renderer) && is_readable($renderer)) ? $renderer : '';
    }

    private static function replace_active_theme_renderer(string $path, string $contents): void {
        $trimmed = ltrim($contents);
        if (!str_starts_with($trimmed, '<?php')) {
            throw new RuntimeException('flexible-content.php must begin with PHP code.');
        }
        if (strlen($contents) > 5 * 1024 * 1024) {
            throw new RuntimeException('flexible-content.php exceeds the 5 MB safety limit.');
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
        global $wp_filesystem;
        if (!$wp_filesystem) {
            throw new RuntimeException('WordPress could not initialize its filesystem API.');
        }
        $existing = $wp_filesystem->get_contents($path);
        if ($existing === false || trim($existing) === '') {
            throw new RuntimeException('The existing active theme renderer could not be read.');
        }
        if (strpos($existing, '<?php') === false) {
            throw new RuntimeException('The existing active theme renderer does not contain PHP code.');
        }

        $upload = wp_upload_dir();
        $backup_dir = trailingslashit($upload['basedir']) . 'tms-wbt-backups';
        if (!wp_mkdir_p($backup_dir)) {
            throw new RuntimeException('The backup directory for the existing theme renderer could not be created. No theme file was changed.');
        }
        $relative = ltrim(str_replace(trailingslashit(get_stylesheet_directory()), '', $path), '/\\');
        $backup_name = sanitize_file_name(str_replace(['/', '\\'], '-', $relative)) . '-' . gmdate('YmdHis') . '-' . wp_generate_password(6, false, false) . '.bak';
        $backup = trailingslashit($backup_dir) . $backup_name;
        if (file_put_contents($backup, $existing) === false) {
            throw new RuntimeException('The existing theme renderer could not be backed up. No theme file was changed.');
        }
        if (!$wp_filesystem->put_contents($path, $contents, FS_CHMOD_FILE)) {
            $wp_filesystem->put_contents($path, $existing, FS_CHMOD_FILE);
            @unlink($backup);
            throw new RuntimeException('The active theme renderer could not be updated. The original file was restored.');
        }
    }

    private static function page_layout_values_match($stored, array $expected): bool {
        if (!is_array($stored)) {
            return false;
        }

        // Normalize only the structures that ACF may legitimately add while
        // storing Flexible Content values. This keeps verification strict
        // enough to catch a real failed write without requiring an identical
        // PHP representation.
        $normalize = function ($value) use (&$normalize) {
            if (is_array($value)) {
                $out = [];
                foreach ($value as $key => $item) {
                    $out[(string) $key] = $normalize($item);
                }
                if (array_keys($out) !== range(0, count($out) - 1)) {
                    ksort($out);
                }
                return $out;
            }
            return is_scalar($value) || $value === null ? (string) $value : $value;
        };

        return wp_json_encode($normalize($stored)) === wp_json_encode($normalize($expected));
    }

    /**
     * Resolve image fields in page-layout.json against the actual WordPress
     * Media Library. AI-generated demo data may use a fake numeric ID such as
     * 12345 because the real attachment ID is not knowable outside the site.
     * Every ACF image/gallery field in the generated page data is populated
     * from the eight reusable TMS placeholder attachments; non-image fields are
     * left untouched.
     */
    private static function resolve_page_layout_images(array $layouts, array $field): array {
        $placeholder_ids = self::get_placeholder_attachment_ids(8);
        if (!$placeholder_ids) {
            throw new RuntimeException('Unable to prepare the eight TMS placeholder images in the Media Library.');
        }

        $random_placeholder = static function () use ($placeholder_ids): int {
            return (int) $placeholder_ids[array_rand($placeholder_ids)];
        };

        $field_map = static function (array $fields): array {
            $map = [];
            foreach ($fields as $acf_field) {
                if (is_array($acf_field) && !empty($acf_field['name'])) {
                    $map[(string) $acf_field['name']] = $acf_field;
                }
            }
            return $map;
        };

        $resolve_image_value = static function ($value) use ($random_placeholder): int {
            // Page Generator intentionally uses the TMS placeholder pool for
            // every ACF image field when a page is updated. This keeps AI demo
            // data independent from attachment IDs that only exist on another site.
            return $random_placeholder();
        };

        $resolve_gallery_value = static function ($value) use ($random_placeholder): array {
            if (is_array($value) && $value) {
                $out = [];
                foreach ($value as $_item) {
                    $out[] = $random_placeholder();
                }
                return $out;
            }
            return [$random_placeholder()];
        };

        $resolve_fields = null;
        $resolve_fields = static function (array $values, array $fields) use (&$resolve_fields, $field_map, $resolve_image_value, $resolve_gallery_value) {
            $map = $field_map($fields);
            foreach ($map as $name => $acf_field) {
                $type = (string) ($acf_field['type'] ?? '');
                $has_value = array_key_exists($name, $values);
                $value = $has_value ? $values[$name] : null;

                if ($type === 'image') {
                    // Add the field even when AI omitted it from page-layout.json.
                    $values[$name] = $resolve_image_value($value);
                    continue;
                }

                if ($type === 'gallery') {
                    $values[$name] = $resolve_gallery_value($value);
                    continue;
                }

                if ($type === 'group' && (is_array($value) || !$has_value)) {
                    $values[$name] = $resolve_fields(is_array($value) ? $value : [], (array) ($acf_field['sub_fields'] ?? []));
                    continue;
                }

                if ($type === 'repeater' && is_array($value)) {
                    $sub_fields = (array) ($acf_field['sub_fields'] ?? []);
                    foreach ($value as $row_key => $row_value) {
                        if (is_array($row_value)) {
                            $value[$row_key] = $resolve_fields($row_value, $sub_fields);
                        }
                    }
                    $values[$name] = $value;
                    continue;
                }

                if ($type === 'flexible_content' && is_array($value)) {
                    $layout_map = [];
                    foreach ((array) ($acf_field['layouts'] ?? []) as $layout) {
                        if (is_array($layout) && !empty($layout['name'])) {
                            $layout_map[(string) $layout['name']] = $layout;
                        }
                    }
                    foreach ($value as $row_key => $row_value) {
                        if (!is_array($row_value)) {
                            continue;
                        }
                        $layout_name = (string) ($row_value['acf_fc_layout'] ?? $row_value['layout'] ?? '');
                        if ($layout_name !== '' && isset($layout_map[$layout_name])) {
                            $row_value = $resolve_fields($row_value, (array) ($layout_map[$layout_name]['sub_fields'] ?? []));
                            $row_value['acf_fc_layout'] = $layout_name;
                            unset($row_value['layout']);
                            $value[$row_key] = $row_value;
                        }
                    }
                    $values[$name] = $value;
                    continue;
                }
            }
            return $values;
        };

        $layout_map = [];
        foreach ((array) ($field['layouts'] ?? []) as $layout) {
            if (is_array($layout) && !empty($layout['name'])) {
                $layout_map[(string) $layout['name']] = $layout;
            }
        }

        foreach ($layouts as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $layout_name = (string) ($row['acf_fc_layout'] ?? $row['layout'] ?? '');
            if ($layout_name === '' || !isset($layout_map[$layout_name])) {
                continue;
            }
            $layouts[$index] = $resolve_fields($row, (array) ($layout_map[$layout_name]['sub_fields'] ?? []));
            $layouts[$index]['acf_fc_layout'] = $layout_name;
            unset($layouts[$index]['layout']);
        }

        return $layouts;
    }

    /**
     * Return the eight reusable TMS placeholder attachments, importing only the
     * missing images. Each missing image is fetched with a separate Picsum
     * request so the eight files receive different source images.
     */
    private static function get_placeholder_attachment_ids(int $count = 8): array {
        $count = max(1, min(20, $count));
        $ids = [];

        for ($index = 1; $index <= $count; $index++) {
            $ids[$index] = self::find_placeholder_attachment($index);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        for ($index = 1; $index <= $count; $index++) {
            if (!empty($ids[$index])) {
                continue;
            }

            $source_url = 'https://picsum.photos/1200/800?tms_wbt_placeholder=' . $index . '&v=' . wp_generate_uuid4();
            $response = wp_safe_remote_get($source_url, [
                'timeout' => 30,
                'redirection' => 5,
                'limit_response_size' => 8 * 1024 * 1024,
                'headers' => [
                    'Accept' => 'image/jpeg,image/png,image/webp,image/*',
                    'Cache-Control' => 'no-cache',
                ],
            ]);
            if (is_wp_error($response)) {
                throw new RuntimeException('Unable to download placeholder image ' . sprintf('%02d', $index) . ': ' . $response->get_error_message());
            }

            $status = (int) wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $content_type = strtolower((string) wp_remote_retrieve_header($response, 'content-type'));
            if ($status < 200 || $status >= 400 || $body === '') {
                throw new RuntimeException('Unable to download placeholder image ' . sprintf('%02d', $index) . ' from Picsum.');
            }
            if (strpos($content_type, 'image/jpeg') !== 0 && strpos($content_type, 'image/png') !== 0 && strpos($content_type, 'image/webp') !== 0) {
                throw new RuntimeException('Picsum returned an unsupported format for placeholder image ' . sprintf('%02d', $index) . '.');
            }

            $extension = strpos($content_type, 'image/png') === 0 ? 'png' : (strpos($content_type, 'image/webp') === 0 ? 'webp' : 'jpg');
            $tmp = wp_tempnam('tms-placeholder-' . sprintf('%02d', $index));
            if (!$tmp || file_put_contents($tmp, $body) === false) {
                if ($tmp) {
                    @unlink($tmp);
                }
                throw new RuntimeException('Unable to temporarily store placeholder image ' . sprintf('%02d', $index) . '.');
            }

            $filename = 'tms-placeholder-' . sprintf('%02d', $index) . '.' . $extension;
            $file_array = [
                'name' => $filename,
                'tmp_name' => $tmp,
                'type' => $content_type,
                'size' => strlen($body),
                'error' => UPLOAD_ERR_OK,
            ];
            $attachment_id = media_handle_sideload($file_array, 0, 'TMS Build Tools placeholder ' . sprintf('%02d', $index));
            @unlink($tmp);
            if (is_wp_error($attachment_id)) {
                throw new RuntimeException('Unable to import placeholder image ' . sprintf('%02d', $index) . ': ' . $attachment_id->get_error_message());
            }

            update_post_meta($attachment_id, '_tms_wbt_placeholder_index', $index);
            update_post_meta($attachment_id, '_tms_wbt_placeholder_source', 'https://picsum.photos/1200/800');
            update_post_meta($attachment_id, '_tms_wbt_placeholder_download_url', $source_url);
            wp_update_post([
                'ID' => (int) $attachment_id,
                'post_title' => 'TMS Placeholder ' . sprintf('%02d', $index),
            ]);
            $ids[$index] = (int) $attachment_id;
        }

        ksort($ids);
        return array_values(array_filter(array_map('absint', $ids)));
    }

    private static function find_placeholder_attachment(int $index): int {
        $meta = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_key' => '_tms_wbt_placeholder_index',
            'meta_value' => $index,
            'fields' => 'ids',
        ]);
        if ($meta) {
            return (int) $meta[0];
        }

        $slug = 'tms-placeholder-' . sprintf('%02d', $index);
        $by_name = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'name' => $slug,
            'fields' => 'ids',
        ]);
        if ($by_name) {
            update_post_meta((int) $by_name[0], '_tms_wbt_placeholder_index', $index);
            update_post_meta((int) $by_name[0], '_tms_wbt_placeholder_source', 'https://picsum.photos/1200/800');
            return (int) $by_name[0];
        }

        return 0;
    }

    public static function render_page_importer(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'tms-build-tools'));
        }

        $notice = '';
        $preview = null;
        $import_result = null;
        $preview_token = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('tms_wbt_action', 'tms_wbt_nonce');
            $action = sanitize_key(wp_unslash($_POST['tms_wbt_action'] ?? ''));

            if ($action === 'build_preview') {
                $file = '';
                try {
                    $file = self::handle_upload();
                    $reader = new TMS_WBT_XLSX_Reader($file);
                    $raw = $reader->read_sitemap_rows();
                    $validated = TMS_WBT_Importer::validate_rows($raw);
                    $preview = $validated;

                    if ($validated['errors']) {
                        $notice = '<div class="notice notice-error"><p><strong>Structure could not be built.</strong> Validation failed, so WordPress was not modified.</p></div>';
                    } else {
                        $preview_token = bin2hex(random_bytes(16));
                        set_transient('tms_wbt_page_import_preview_' . $preview_token, $validated['records'], 30 * MINUTE_IN_SECONDS);
                        $notice = '<div class="notice notice-success"><p><strong>Structure built.</strong> Review the hierarchy, rename pages if needed, choose what to import, and skip any pages you do not want to create.</p></div>';
                    }
                } catch (Throwable $e) {
                    $notice = '<div class="notice notice-error"><p><strong>Structure build stopped safely:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
                } finally {
                    if ($file !== '' && file_exists($file)) {
                        @unlink($file);
                    }
                }
            } elseif ($action === 'generate_pages') {
                $preview_token = sanitize_key(wp_unslash($_POST['tms_wbt_preview_token'] ?? ''));
                $records = $preview_token !== '' ? get_transient('tms_wbt_page_import_preview_' . $preview_token) : false;

                if (!is_array($records) || !$records) {
                    $notice = '<div class="notice notice-error"><p><strong>Import stopped safely:</strong> The structure preview has expired. Please upload the XLSX again.</p></div>';
                } else {
                    $choices = isset($_POST['tms_wbt_import_mode']) && is_array($_POST['tms_wbt_import_mode'])
                        ? wp_unslash($_POST['tms_wbt_import_mode'])
                        : [];
                    $titles = isset($_POST['tms_wbt_title']) && is_array($_POST['tms_wbt_title'])
                        ? wp_unslash($_POST['tms_wbt_title'])
                        : [];
                    $include_selectors = isset($_POST['tms_wbt_include_selector']) && is_array($_POST['tms_wbt_include_selector'])
                        ? wp_unslash($_POST['tms_wbt_include_selector'])
                        : [];
                    $exclude_selectors = isset($_POST['tms_wbt_exclude_selector']) && is_array($_POST['tms_wbt_exclude_selector'])
                        ? wp_unslash($_POST['tms_wbt_exclude_selector'])
                        : [];
                    $import_settings = [
                        'rewrite_internal_links' => !empty($_POST['tms_wbt_rewrite_internal_links']),
                        'download_images' => !empty($_POST['tms_wbt_download_images']),
                        'resource_profile' => sanitize_key(wp_unslash($_POST['tms_wbt_resource_profile'] ?? 'balanced')),
                        'max_images' => absint($_POST['tms_wbt_max_images'] ?? 0),
                    ];

                    foreach ($records as &$record) {
                        $record['title'] = isset($titles[$record['id']])
                            ? sanitize_text_field((string) $titles[$record['id']])
                            : $record['title'];
                        if ($record['title'] === '') {
                            $record['title'] = 'Untitled Page';
                        }

                        if (in_array($record['type'], ['content', 'bucket', 'preset', 'merge', 'form'], true)) {
                            $choice = isset($choices[$record['id']]) ? sanitize_key((string) $choices[$record['id']]) : '';
                            $record['import_mode'] = in_array($choice, ['import', 'blank', 'skip'], true)
                                ? $choice
                                : TMS_WBT_Importer::default_import_mode($record);

                            $record['include_selector'] = isset($include_selectors[$record['id']])
                                ? sanitize_text_field((string) $include_selectors[$record['id']])
                                : '';
                            $record['exclude_selector'] = isset($exclude_selectors[$record['id']])
                                ? sanitize_text_field((string) $exclude_selectors[$record['id']])
                                : '';
                        }
                    }
                    unset($record);

                    try {
                        TMS_WBT_Importer::validate_import_options($records, $import_settings);
                        $import_result = TMS_WBT_Importer::import($records, $import_settings);
                        delete_transient('tms_wbt_page_import_preview_' . $preview_token);
                        $notice = '<div class="notice notice-success"><p><strong>Pages generated successfully.</strong> The selected pages, imported content, and Primary Menu were processed.</p></div>';
                    } catch (Throwable $e) {
                        $notice = '<div class="notice notice-error"><p><strong>Page generation stopped safely:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
                    }
                }
            }
        }

        ?>
        <div class="wrap">
            <h1>2. Page Importer</h1>
            <p>Upload the XLSX sitemap to build a visual page structure first. No WordPress pages are changed until you review the structure and submit the final import.</p>
            <?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <?php if (!$preview || !$preview_token) : ?>
                <form method="post" enctype="multipart/form-data" id="tms-wbt-page-import-upload-form">
                    <?php wp_nonce_field('tms_wbt_action', 'tms_wbt_nonce'); ?>
                    <input type="hidden" name="tms_wbt_action" value="build_preview">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="tms_wbt_file">Sitemap XLSX</label></th>
                            <td>
                                <input id="tms_wbt_file" type="file" name="tms_wbt_file" accept=".xlsx" required>
                                <p class="description">Selecting the workbook automatically builds the preview. This step does not create or update WordPress pages.</p>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <button type="submit" class="button button-primary" id="tms-wbt-sitemap-submit">Build Page Structure</button>
                    </p>
                    <div id="tms-wbt-sitemap-processing" class="tms-wbt-processing" aria-live="polite" aria-hidden="true">
                        <span class="tms-wbt-spinner" aria-hidden="true"></span>
                        <span>Building structure. Please wait&hellip;</span>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($preview && !empty($preview['errors'])) : ?>
                <hr>
                <h2>Validation errors</h2>
                <ol>
                <?php foreach ($preview['errors'] as $error) : ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
                </ol>
            <?php endif; ?>

            <?php if ($preview && empty($preview['errors']) && $preview_token) : ?>
                <hr>
                <h2>Review Page Structure</h2>
                <p><?php echo esc_html(count($preview['records'])); ?> sitemap rows passed validation. Normal page types are creation candidates regardless of Old URL or existing content. Use <strong>Skip Page</strong> when you do not want a page created. Hierarchical, Link Out, Redirect Only, and Anchor Link are menu-only custom links.</p>
                <p class="description"><strong>Import Content</strong> is selected by default when the sitemap indicates <em>From Existing Page</em> and provides an Old URL. The optional selector fields are only used when Import Content is selected. Selectors must be a single class or ID such as <code>.content</code> or <code>#content</code>.</p>

                <form method="post" id="tms-wbt-page-import-generate-form">
                    <?php wp_nonce_field('tms_wbt_action', 'tms_wbt_nonce'); ?>
                    <input type="hidden" name="tms_wbt_action" value="generate_pages">
                    <input type="hidden" name="tms_wbt_preview_token" value="<?php echo esc_attr($preview_token); ?>">

                    <div class="tms-wbt-import-settings">
                        <h3>Import Settings</h3>
                        <p class="description">These settings apply to all pages in this import.</p>
                        <label class="tms-wbt-setting-check"><input type="checkbox" name="tms_wbt_rewrite_internal_links" value="1"> <strong>Replace source website links with destination URLs</strong><br><span>Rewrite links pointing to the old website so they point to the current website, preserving the path, query string, and fragment. External links are untouched.</span></label>
                        <label class="tms-wbt-setting-check"><input type="checkbox" name="tms_wbt_download_images" value="1"> <strong>Download images to Media Library</strong><br><span>Download source images, attach them to WordPress Media Library, and replace their URLs in imported content. Failed image downloads are skipped so the page import can continue.</span></label>
                        <div class="tms-wbt-setting-grid">
                            <label><strong>Resource profile</strong><select name="tms_wbt_resource_profile"><option value="stable">Stable — slower, fewer remote requests</option><option value="balanced" selected>Balanced — recommended</option><option value="fast">Fast — more remote work</option></select></label>
                            <label><strong>Maximum images per page</strong><input type="number" name="tms_wbt_max_images" min="0" max="100" value="0"><span>0 = use the profile limit.</span></label>
                        </div>
                        <p class="description">Stable mode uses longer remote timeouts but limits remote image/CSS work per page. This reduces load on the source and destination servers, although your hosting provider's PHP execution limit can still stop a very large migration.</p>
                    </div>

                    <div class="tms-wbt-tree" role="tree" aria-label="Page structure preview">
                        <?php foreach ($preview['records'] as $record) :
                            $is_page = in_array($record['type'], ['content', 'bucket', 'preset', 'merge', 'form'], true);
                            $default_mode = TMS_WBT_Importer::default_import_mode($record);
                            $type_label = ucwords(str_replace('_', ' ', $record['type']));
                            $input_id = 'tms-wbt-title-' . $record['id'];
                            ?>
                            <div class="tms-wbt-tree-row" style="--tms-depth: <?php echo (int) $record['depth']; ?>;" role="treeitem">
                                <div class="tms-wbt-tree-title">
                                    <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>">Page title for <?php echo esc_html($record['title']); ?></label>
                                    <input id="<?php echo esc_attr($input_id); ?>" class="tms-wbt-page-title" type="text" name="tms_wbt_title[<?php echo esc_attr($record['id']); ?>]" value="<?php echo esc_attr($record['title']); ?>" <?php disabled(!$is_page); ?> />
                                    <span class="tms-wbt-type"><?php echo esc_html($type_label); ?></span>
                                </div>
                                <div class="tms-wbt-tree-meta">
                                    <?php if ($record['old_url'] !== '') : ?><div><strong>Old URL:</strong> <?php echo esc_html($record['old_url']); ?></div><?php endif; ?>
                                    <?php if ($record['new_slug'] !== '') : ?><div><strong>New URL:</strong> <code><?php echo esc_html($record['new_slug']); ?></code></div><?php endif; ?>
                                </div>
                                <div class="tms-wbt-tree-action">
                                    <?php if ($is_page) : ?>
                                        <label>
                                            <span class="screen-reader-text">Import mode for <?php echo esc_html($record['title']); ?></span>
                                            <select class="tms-wbt-import-mode" name="tms_wbt_import_mode[<?php echo esc_attr($record['id']); ?>]">
                                                <option value="import" <?php selected($default_mode, 'import'); ?>>Import Content</option>
                                                <option value="blank" <?php selected($default_mode, 'blank'); ?>>Blank Page</option>
                                                <option value="skip">Skip Page</option>
                                            </select>
                                        </label>
                                    <?php else : ?>
                                        <span class="tms-wbt-menu-only">Menu item only</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($is_page) : ?>
                                    <div class="tms-wbt-content-options">
                                        <label>
                                            <span>Only import from <button type="button" class="tms-wbt-help" data-help="include" aria-label="What does Only import from mean?">?</button></span>
                                            <input class="tms-wbt-selector-input" type="text" name="tms_wbt_include_selector[<?php echo esc_attr($record['id']); ?>]" value="" placeholder=".class, #id" autocomplete="off">
                                        </label>
                                        <label>
                                            <span>Exclude from import <button type="button" class="tms-wbt-help" data-help="exclude" aria-label="What does Exclude from import mean?">?</button></span>
                                            <input class="tms-wbt-selector-input" type="text" name="tms_wbt_exclude_selector[<?php echo esc_attr($record['id']); ?>]" value="" placeholder=".class, #id" autocomplete="off">
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p>
                        <button type="submit" class="button button-primary" id="tms-wbt-page-import-generate">Generate Pages &amp; Menu Structure</button>
                    </p>
                    <div id="tms-wbt-page-import-processing" class="tms-wbt-processing" aria-live="polite" aria-hidden="true">
                        <span class="tms-wbt-spinner" aria-hidden="true"></span>
                        <span>Generating pages and menu structure. Please wait&hellip;</span>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($import_result) : ?>
                <hr>
                <h2>Import Log</h2>
                <ul>
                    <li>Created: <?php echo esc_html($import_result['created']); ?></li>
                    <li>Updated: <?php echo esc_html($import_result['updated']); ?></li>
                    <li>Content imported: <?php echo esc_html($import_result['content_imported'] ?? 0); ?></li>
                    <li>Blank pages: <?php echo esc_html($import_result['blank_pages'] ?? 0); ?></li>
                    <li>Skipped: <?php echo esc_html($import_result['skipped']); ?></li>
                    <li>Menu items: <?php echo esc_html($import_result['menu_items']); ?></li>
                    <li>Errors: <?php echo esc_html(count($import_result['errors'])); ?></li>
                </ul>
                <?php if ($import_result['errors']) : ?>
                    <h3>Errors</h3>
                    <ol>
                    <?php foreach ($import_result['errors'] as $error) : ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div id="tms-wbt-help-modal" class="tms-wbt-help-modal" hidden>
            <div class="tms-wbt-help-modal__panel" role="dialog" aria-modal="true" aria-labelledby="tms-wbt-help-title">
                <button type="button" class="tms-wbt-help-modal__close" aria-label="Close">&times;</button>
                <h2 id="tms-wbt-help-title">Import content option</h2>
                <p id="tms-wbt-help-body"></p>
            </div>
        </div>
        <style>
            .tms-wbt-import-settings { margin: 0 0 20px; padding: 16px; border: 1px solid #dcdcde; background: #fff; }
            .tms-wbt-import-settings h3 { margin: 0 0 6px; }
            .tms-wbt-setting-check { display: block; margin: 12px 0; line-height: 1.5; }
            .tms-wbt-setting-check span, .tms-wbt-setting-grid span { display: block; margin-left: 24px; color: #646970; font-size: 12px; }
            .tms-wbt-setting-grid { display: grid; grid-template-columns: minmax(260px, 1fr) minmax(220px, 1fr); gap: 16px; margin-top: 14px; }
            .tms-wbt-setting-grid label { display: grid; gap: 6px; }
            .tms-wbt-setting-grid select, .tms-wbt-setting-grid input { max-width: 100%; }
            @media (max-width: 782px) { .tms-wbt-setting-grid { grid-template-columns: 1fr; } }
            .tms-wbt-processing {
                display: none;
                align-items: center;
                gap: 8px;
                margin: 8px 0 16px;
                font-weight: 600;
            }
            .tms-wbt-processing.is-visible { display: flex; }
            .tms-wbt-spinner {
                width: 16px;
                height: 16px;
                border: 2px solid #dcdcde;
                border-top-color: #2271b1;
                border-radius: 50%;
                animation: tms-wbt-spin .8s linear infinite;
                box-sizing: border-box;
                flex: 0 0 16px;
            }
            @keyframes tms-wbt-spin { to { transform: rotate(360deg); } }
            .tms-wbt-help-modal[hidden] { display: none !important; }
            .tms-wbt-help-modal { position: fixed; inset: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box; background: rgba(0,0,0,.55); }
            .tms-wbt-help-modal__panel { position: relative; width: min(520px, 100%); max-width: 100%; padding: 24px; box-sizing: border-box; background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,.25); }
            .tms-wbt-help-modal__panel h2 { margin: 0 36px 12px 0; }
            .tms-wbt-help-modal__panel p { margin: 0; line-height: 1.6; }
            .tms-wbt-help-modal__close { position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; padding: 0; border: 0; background: transparent; color: #50575e; font-size: 26px; line-height: 32px; cursor: pointer; }
            .tms-wbt-help-modal__close:hover, .tms-wbt-help-modal__close:focus { color: #1d2327; background: #f0f0f1; }
            .tms-wbt-tree {
                border: 1px solid #dcdcde;
                background: #fff;
                max-height: 760px;
                overflow: auto;
                margin: 18px 0;
            }
            .tms-wbt-tree-row {
                display: grid;
                grid-template-columns: minmax(280px, 1.25fr) minmax(150px, .6fr) minmax(175px, .8fr);
                gap: 12px 18px;
                align-items: center;
                min-height: 62px;
                padding: 10px 14px;
                padding-left: calc(14px + (var(--tms-depth) * 28px));
                border-bottom: 1px solid #f0f0f1;
                box-sizing: border-box;
            }
            .tms-wbt-tree-row:last-child { border-bottom: 0; }
            .tms-wbt-tree-title { min-width: 0; }
            .tms-wbt-page-title { width: min(100%, 360px); max-width: 100%; }
            .tms-wbt-type {
                display: inline-block;
                margin-left: 7px;
                color: #646970;
                font-size: 12px;
                text-transform: capitalize;
            }
            .tms-wbt-tree-meta {
                min-width: 0;
                color: #646970;
                font-size: 12px;
            }
            .tms-wbt-tree-meta code { white-space: nowrap; }
            .tms-wbt-tree-action select { width: 100%; max-width: 180px; }
            .tms-wbt-menu-only { color: #646970; font-size: 12px; }
            .tms-wbt-content-options {
                grid-column: 1 / -1;
                display: grid;
                grid-template-columns: repeat(2, minmax(220px, 1fr));
                gap: 10px 18px;
                padding: 0 0 4px;
            }
            .tms-wbt-content-options label { display: grid; gap: 4px; font-size: 12px; color: #50575e; }
            .tms-wbt-content-options input { width: 100%; box-sizing: border-box; }
            .tms-wbt-content-options.is-disabled { opacity: .55; }
            @media (max-width: 960px) {
                .tms-wbt-tree-row { grid-template-columns: 1fr; gap: 7px; padding-left: 14px; }
                .tms-wbt-tree-title { padding-left: calc(var(--tms-depth) * 22px); }
                .tms-wbt-tree-action select { max-width: 240px; }
                .tms-wbt-content-options { grid-template-columns: 1fr; }
            }
        </style>
        <script>
            (function () {
                function bindProcessing(formId, buttonId, processingId, autoSubmitFileId) {
                    var form = document.getElementById(formId);
                    var button = document.getElementById(buttonId);
                    var processing = document.getElementById(processingId);
                    if (!form || !button || !processing) return;
                    function startProcessing() {
                        if (form.dataset.tmsWbtSubmitted === '1') return;
                        form.dataset.tmsWbtSubmitted = '1';
                        button.disabled = true;
                        button.setAttribute('aria-disabled', 'true');
                        processing.classList.add('is-visible');
                        processing.setAttribute('aria-hidden', 'false');
                    }
                    form.addEventListener('submit', function () {
                        startProcessing();
                    });
                    if (autoSubmitFileId) {
                        var fileInput = document.getElementById(autoSubmitFileId);
                        if (fileInput) {
                            fileInput.addEventListener('change', function () {
                                if (fileInput.files && fileInput.files.length) {
                                    startProcessing();
                                    form.submit();
                                }
                            });
                        }
                    }
                }

                function bindImportOptions() {
                    document.querySelectorAll('.tms-wbt-tree-row').forEach(function (row) {
                        var mode = row.querySelector('.tms-wbt-import-mode');
                        var options = row.querySelector('.tms-wbt-content-options');
                        if (!mode || !options) return;
                        var inputs = options.querySelectorAll('input');
                        function sync() {
                            var disabled = mode.value !== 'import';
                            options.classList.toggle('is-disabled', disabled);
                            inputs.forEach(function (input) { input.disabled = disabled; });
                        }
                        mode.addEventListener('change', sync);
                        sync();
                    });
                }

                var generateForm = document.getElementById('tms-wbt-page-import-generate-form');
                if (generateForm) {
                    generateForm.addEventListener('submit', function (event) {
                        if (generateForm.dataset.submitted === '1') {
                            event.preventDefault();
                            return;
                        }
                        event.preventDefault();
                        generateForm.dataset.submitted = '1';
                        generateForm.setAttribute('aria-busy', 'true');
                        var processing = document.getElementById('tms-wbt-page-import-processing');
                        if (processing) {
                            processing.classList.add('is-visible');
                            processing.setAttribute('aria-hidden', 'false');
                        }
                        generateForm.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                            // Keep hidden state fields enabled so WordPress receives the nonce, action, and preview token.
                            // Disabling a hidden input removes it from the POST payload and causes check_admin_referer()
                            // to fail with WordPress's generic "The link you followed has expired." message.
                            if (el.type === 'hidden') return;
                            el.disabled = true;
                        });
                        // Give the browser a rendering opportunity so the spinner/message is visible before navigation.
                        window.requestAnimationFrame(function () {
                            window.setTimeout(function () {
                                HTMLFormElement.prototype.submit.call(generateForm);
                            }, 50);
                        });
                    });
                }

                var helpModal = document.getElementById('tms-wbt-help-modal');
                var helpTitle = document.getElementById('tms-wbt-help-title');
                var helpBody = document.getElementById('tms-wbt-help-body');
                var helpCopy = {
                    include: { title: 'Only import from', body: 'Enter one class or ID selector, such as .content or #content. Only the HTML inside the first matching element on the old page will be imported.' },
                    exclude: { title: 'Exclude from import', body: 'Enter one class or ID selector, such as .meta or #sidebar. Matching elements inside the imported content will be removed before the content is saved.' }
                };
                document.querySelectorAll('.tms-wbt-help').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var item = helpCopy[button.getAttribute('data-help')];
                        if (!item || !helpModal) return;
                        helpTitle.textContent = item.title;
                        helpBody.textContent = item.body;
                        helpModal.hidden = false;
                    });
                });
                if (helpModal) {
                    helpModal.addEventListener('click', function (event) {
                        if (event.target === helpModal || event.target.classList.contains('tms-wbt-help-modal__close')) helpModal.hidden = true;
                    });
                }

                bindProcessing('tms-wbt-page-import-upload-form', 'tms-wbt-sitemap-submit', 'tms-wbt-sitemap-processing', 'tms_wbt_file');
                bindImportOptions();
            }());
        </script>
        <?php
    }

    private static function handle_upload(): string {
        if (empty($_FILES['tms_wbt_file']) || !is_array($_FILES['tms_wbt_file'])) {
            throw new RuntimeException('Please select an XLSX file.');
        }

        $file = $_FILES['tms_wbt_file'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The file upload failed.');
        }

        if (($file['size'] ?? 0) > 25 * 1024 * 1024) {
            throw new RuntimeException('The XLSX file exceeds the 25 MB safety limit.');
        }

        $check = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        if (empty($check['ext']) || $check['ext'] !== 'xlsx') {
            throw new RuntimeException('Only genuine .xlsx files are accepted.');
        }

        $tmp = wp_tempnam(sanitize_file_name($file['name']));
        if (!$tmp) {
            throw new RuntimeException('Unable to allocate a temporary file.');
        }

        if (!move_uploaded_file($file['tmp_name'], $tmp)) {
            @unlink($tmp);
            throw new RuntimeException('Unable to store the uploaded workbook safely.');
        }

        return $tmp;
    }
}
