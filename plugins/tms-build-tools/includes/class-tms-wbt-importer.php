<?php
defined('ABSPATH') || exit;

final class TMS_WBT_Importer {
    public static function init(): void {
        add_filter('the_content', [self::class, 'prepend_imported_css'], 20);
    }

    public static function activate(): void {
        add_option('tms_wbt_primary_menu_id', 0);
    }

    public static function validate_rows(array $raw_rows): array {
        if (!$raw_rows) {
            throw new RuntimeException('The sitemap worksheet is empty.');
        }

        // Do not assume fixed Excel columns. Sitemap exports may contain additional
        // columns before, between, or after the core fields.
        $header_aliases = [
            'page_title'       => ['page title'],
            'page_type'        => ['page type'],
            'old_url'          => ['old url'],
            'new_slug'         => ['new slug'],
            'have_text'        => ['have text?','have text'],
            'meta_title'       => ['new meta title','meta title'],
            'meta_description' => ['new meta description','meta description'],
            'notes'            => ['text notes','notes'],
            'template'         => ['internal page templates','internal page template'],
        ];

        $header_index = null;
        $column_map = [];

        foreach ($raw_rows as $i => $candidate) {
            $normalized_headers = [];
            foreach ($candidate as $column => $value) {
                $normalized = self::normalize_header_label((string) $value);
                if ($normalized !== '') {
                    $normalized_headers[$normalized] = $column;
                }
            }

            $candidate_map = [];
            foreach ($header_aliases as $key => $aliases) {
                foreach ($aliases as $alias) {
                    $normalized_alias = self::normalize_header_label($alias);
                    if (isset($normalized_headers[$normalized_alias])) {
                        $candidate_map[$key] = $normalized_headers[$normalized_alias];
                        break;
                    }
                }
            }

            // These are the columns required to understand the sitemap. Meta
            // fields are supported when present but are not allowed to prevent
            // otherwise valid sitemap files from being imported.
            $required_keys = ['page_title', 'page_type', 'new_slug', 'have_text'];
            $has_required = count(array_intersect($required_keys, array_keys($candidate_map))) === count($required_keys);

            if ($has_required) {
                $header_index = $i;
                $column_map = $candidate_map;
                break;
            }
        }

        if ($header_index === null) {
            throw new RuntimeException(
                'Could not locate the sitemap header row. The sheet must contain Page Title, Page Type, New Slug, and Have Text? columns. ' .
                'Meta Title and Meta Description are optional and may be named either "Meta Title"/"Meta Description" or "New Meta Title"/"New Meta Description".'
            );
        }

        $value = static function (array $row, string $key) use ($column_map): string {
            $column = $column_map[$key] ?? null;
            return $column !== null ? trim((string) ($row[$column] ?? '')) : '';
        };

        $records = [];
        $stack = [];
        $errors = [];
        $seen_paths = [];

        foreach (array_slice($raw_rows, $header_index + 1) as $offset => $row) {
            $excel_index = $header_index + $offset;
            $title_raw = $value($row, 'page_title');
            if ($title_raw === '' || preg_match('/^[\s\r\n]+$/', $title_raw)) {
                continue;
            }

            $depth = self::leading_depth($title_raw);
            $title = trim(ltrim($title_raw, "- \t\r\n"));

            if ($title === '') {
                $errors[] = 'Row ' . ($excel_index + 2) . ': empty page title.';
                continue;
            }

            $type = self::normalize_page_type($value($row, 'page_type'));
            $slug = $value($row, 'new_slug');
            $old_url = self::normalize_old_url_value($value($row, 'old_url'));
            $have_text = $value($row, 'have_text');
            $meta_title = $value($row, 'meta_title');
            $meta_description = $value($row, 'meta_description');
            $notes = $value($row, 'notes');
            $template = $value($row, 'template');

            if ($depth > 0 && count($stack) < $depth) {
                $errors[] = sprintf('Row %d: hierarchy jumps from depth %d to depth %d.', $excel_index + 2, count($stack), $depth);
            }

            $parent_id = $depth > 0 && isset($stack[$depth - 1]) ? $stack[$depth - 1]['id'] : 0;

            $id = sanitize_key(wp_unique_id('tms_wbt_' . $excel_index . '_' . $title));

            if ($type === 'unknown') {
                $errors[] = sprintf('Row %d: unsupported or empty Page Type "%s".', $excel_index + 2, $value($row, 'page_type'));
            }

            $record = [
                'id' => $id,
                'source_row' => $excel_index + 2,
                'title' => $title,
                'depth' => $depth,
                'type' => $type,
                'old_url' => $old_url,
                'new_slug' => $slug,
                'have_text' => $have_text,
                'meta_title' => $meta_title,
                'meta_description' => $meta_description,
                'notes' => $notes,
                'template' => $template,
                'parent_id' => $parent_id,
            ];

            if ($slug !== '' && $type !== 'link_out' && $type !== 'anchor') {
                $normalized_path = self::normalize_path($slug);
                if (isset($seen_paths[$normalized_path])) {
                    $errors[] = sprintf(
                        'Rows %d and %d use the same destination path: %s.',
                        $seen_paths[$normalized_path], $excel_index + 2, $normalized_path
                    );
                } else {
                    $seen_paths[$normalized_path] = $excel_index + 2;
                }
            }

            if ($type === 'content' || $type === 'bucket' || $type === 'merge' || $type === 'form') {
                if ($slug === '') {
                    $errors[] = sprintf('Row %d: page type "%s" requires a New Slug.', $excel_index + 2, $type);
                }
            }

            // A Content Page without an Old URL is valid and defaults to a blank page.
            if ($old_url !== '' && !self::is_safe_source_url($old_url)) {
                $errors[] = sprintf('Row %d: Old URL must be an HTTPS/HTTP URL with a valid host.', $excel_index + 2);
            }

            if ($type === 'anchor' && $slug === '') {
                $errors[] = sprintf('Row %d: Anchor Link requires a destination path.', $excel_index + 2);
            }

            $records[] = $record;

            if ($depth === 0) {
                $stack = [$record];
            } else {
                $stack[$depth] = $record;
                $stack = array_slice($stack, 0, $depth + 1);
            }
        }

        if ($errors) {
            return ['records' => $records, 'errors' => $errors];
        }

        return ['records' => $records, 'errors' => []];
    }

    public static function default_import_mode(array $record): string {
        $page_types = ['content', 'merge'];
        $have_text = strtolower(trim((string) ($record['have_text'] ?? '')));
        $has_source = trim((string) ($record['old_url'] ?? '')) !== '';

        if (in_array($record['type'] ?? '', $page_types, true) && $has_source && $have_text === 'from existing page') {
            return 'import';
        }

        return 'blank';
    }

    public static function validate_import_options(array $records, array $settings = []): void {
        $settings = self::normalize_import_settings($settings);

        foreach ($records as $record) {
            $mode = in_array(($record['import_mode'] ?? ''), ['import', 'blank', 'skip'], true)
                ? $record['import_mode']
                : self::default_import_mode($record);

            $title = trim((string) ($record['title'] ?? ''));
            if ($title === '') {
                throw new RuntimeException('Every page must have a title before generation.');
            }

            if ($mode !== 'import') {
                continue;
            }

            $include = trim((string) ($record['include_selector'] ?? ''));
            $exclude = trim((string) ($record['exclude_selector'] ?? ''));
            if ($include !== '') {
                self::normalize_content_selector($include);
            }
            if ($exclude !== '') {
                self::normalize_content_selector($exclude);
            }
        }
    }

    public static function import(array $records, array $settings = []): array {
        if (!current_user_can('manage_options')) {
            throw new RuntimeException('You do not have permission to run the page import.');
        }

        $settings = self::normalize_import_settings($settings);

        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'content_imported' => 0,
            'blank_pages' => 0,
            'menu_items' => 0,
            'errors' => [],
        ];

        $post_map = [];
        $page_records = array_values(array_filter($records, static function (array $r): bool {
            return in_array($r['type'], ['content', 'bucket', 'preset', 'merge', 'form'], true);
        }));

        foreach ($page_records as $record) {
            $import_mode = in_array(($record['import_mode'] ?? ''), ['import', 'blank', 'skip'], true)
                ? $record['import_mode']
                : self::default_import_mode($record);

            if ($import_mode === 'skip') {
                $results['skipped']++;
                continue;
            }

            $parent_post_id = $record['parent_id'] && isset($post_map[$record['parent_id']])
                ? $post_map[$record['parent_id']]
                : 0;

            $result = self::upsert_page($record, $parent_post_id, $settings);
            if (is_wp_error($result)) {
                $results['errors'][] = sprintf('Page "%s": %s', $record['title'], $result->get_error_message());
                continue;
            }

            $post_map[$record['id']] = (int) $result['post_id'];
            $results[$result['action']]++;
            if (!empty($result['content_imported'])) {
                $results['content_imported']++;
            }
            if (!empty($result['blank_page'])) {
                $results['blank_pages']++;
            }
            if (!empty($result['content_error'])) {
                $results['errors'][] = sprintf('Page "%s": %s', $record['title'], $result['content_error']);
            }
        }

        self::save_record_map($records, $post_map);
        self::set_front_page_if_present($records, $post_map);

        $menu_result = self::build_primary_menu($records, $post_map);
        $results['menu_items'] = $menu_result['items'];
        $results['errors'] = array_merge($results['errors'], $menu_result['errors']);

        return $results;
    }

    /**
     * Treat common sitemap sentinel values as an intentionally empty Old URL.
     * This prevents values such as "new" from being incorrectly validated as URLs.
     */
    private static function normalize_old_url_value(string $value): string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $sentinels = [
            'new',
            'none',
            'n/a',
            'na',
            'null',
            '-',
            '--',
        ];

        return in_array(strtolower($value), $sentinels, true) ? '' : $value;
    }

    private static function normalize_header_label(string $label): string {
        $label = preg_replace('/\\s+/u', ' ', trim($label));
        return strtolower((string) $label);
    }

    private static function upsert_page(array $record, int $parent_id, array $settings = []) {
        $existing = self::find_existing_page($record['id'], $record['new_slug']);
        $import_mode = in_array(($record['import_mode'] ?? ''), ['import', 'blank'], true)
            ? $record['import_mode']
            : self::default_import_mode($record);

        $settings = self::normalize_import_settings($settings);

        $postarr = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field($record['title']),
            'post_name' => self::basename_from_path($record['new_slug']),
            'post_parent' => $parent_id,
        ];

        $content_imported = false;
        $content_error = '';
        $imported_css = '';
        $blank_page = false;

        if ($import_mode === 'import') {
            if ($record['old_url'] === '') {
                $content_error = 'Import Content was selected, but this page has no Old URL. The page was created/updated without imported content.';
                if (!$existing) {
                    $postarr['post_content'] = '';
                    $blank_page = true;
                }
            } else {
                try {
                    $fetched = self::fetch_source_content(
                        $record['old_url'],
                        (string) ($record['include_selector'] ?? ''),
                        (string) ($record['exclude_selector'] ?? ''),
                        $settings
                    );
                    $postarr['post_content'] = $fetched['content'];
                    $imported_css = $fetched['css'];
                    $content_imported = true;
                } catch (Throwable $e) {
                    $content_error = 'Could not import content from ' . $record['old_url'] . ': ' . $e->getMessage();
                    if (!$existing) {
                        $postarr['post_content'] = '';
                        $blank_page = true;
                    }
                }
            }
        } elseif (!$existing) {
            $postarr['post_content'] = '';
            $blank_page = true;
        }

        // The sitemap page is always rendered by the HTML sitemap shortcode.
        // This intentionally overrides imported/blank content for the destination
        // page whose final slug is exactly "sitemap".
        if (self::basename_from_path($record['new_slug']) === 'sitemap') {
            $postarr['post_content'] = '[html_sitemap menu="primary-menu"]';
            $content_imported = false;
            $content_error = '';
            $imported_css = '';
            $blank_page = false;
        }

        if ($record['template'] !== '') {
            $template = sanitize_file_name($record['template']);
            if ($template !== '') {
                $postarr['page_template'] = $template;
            }
        }

        if ($existing) {
            $postarr['ID'] = $existing;
            $post_id = wp_update_post(wp_slash($postarr), true);
            $action = 'updated';
        } else {
            $post_id = wp_insert_post(wp_slash($postarr), true);
            $action = 'created';
        }

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        update_post_meta($post_id, '_tms_wbt_migration_id', $record['id']);
        update_post_meta($post_id, '_tms_wbt_source_url', esc_url_raw($record['old_url']));
        update_post_meta($post_id, '_tms_wbt_source_row', (int) $record['source_row']);
        update_post_meta($post_id, '_tms_wbt_page_type', sanitize_key($record['type']));
        update_post_meta($post_id, '_tms_wbt_destination_path', self::normalize_path($record['new_slug']));
        update_post_meta($post_id, '_tms_wbt_import_mode', sanitize_key($import_mode));
        update_post_meta($post_id, '_tms_wbt_include_selector', sanitize_text_field((string) ($record['include_selector'] ?? '')));
        update_post_meta($post_id, '_tms_wbt_exclude_selector', sanitize_text_field((string) ($record['exclude_selector'] ?? '')));
        if (self::basename_from_path($record['new_slug']) === 'sitemap') {
            delete_post_meta($post_id, '_tms_wbt_imported_css');
        } elseif ($import_mode === 'import' && $content_imported) {
            if ($imported_css !== '') {
                update_post_meta($post_id, '_tms_wbt_imported_css', $imported_css);
            } else {
                delete_post_meta($post_id, '_tms_wbt_imported_css');
            }
        } elseif ($import_mode === 'blank') {
            delete_post_meta($post_id, '_tms_wbt_imported_css');
        }

        self::set_yoast_meta($post_id, $record);

        return [
            'post_id' => (int) $post_id,
            'action' => $action,
            'content_imported' => $content_imported,
            'content_error' => $content_error,
            'blank_page' => $blank_page,
        ];
    }

    private static function fetch_source_content(string $url, string $include_selector = '', string $exclude_selector = '', array $settings = []): array {
        $settings = self::normalize_import_settings($settings);

        if (!self::is_safe_source_url($url)) {
            throw new RuntimeException('The Old URL is not a permitted HTTP/HTTPS URL.');
        }

        $include_selector = self::normalize_content_selector($include_selector);
        $exclude_selector = self::normalize_content_selector($exclude_selector);

        $response = wp_safe_remote_get($url, [
            'timeout' => $settings['page_timeout'],
            'redirection' => 3,
            'limit_response_size' => 5 * 1024 * 1024,
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml',
            ],
        ]);

        if (is_wp_error($response)) {
            throw new RuntimeException($response->get_error_message());
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        if ($status < 200 || $status >= 400) {
            throw new RuntimeException('The source page returned HTTP ' . $status . '.');
        }

        $content_type = strtolower((string) wp_remote_retrieve_header($response, 'content-type'));
        if ($content_type !== '' && !str_contains($content_type, 'text/html') && !str_contains($content_type, 'application/xhtml+xml')) {
            throw new RuntimeException('The source URL did not return an HTML page.');
        }

        $html = (string) wp_remote_retrieve_body($response);
        if ($html === '') {
            throw new RuntimeException('The source page returned an empty response.');
        }
        if (strlen($html) >= 5 * 1024 * 1024) {
            throw new RuntimeException('The source page exceeds the 5 MB content safety limit.');
        }

        if (!class_exists('DOMDocument')) {
            throw new RuntimeException('PHP DOMDocument is required to import HTML content.');
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        if (!$loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            throw new RuntimeException('The source HTML could not be parsed.');
        }

        $xpath = new DOMXPath($dom);
        $style_parts = [];

        // Capture inline stylesheet blocks before removing them from imported content.
        foreach ($xpath->query('//style') ?: [] as $style_node) {
            $css = self::clean_css((string) $style_node->textContent, $url);
            if ($css !== '') {
                $style_parts[] = $css;
            }
        }

        // Capture linked stylesheets. The response is bounded and URLs are resolved safely.
        foreach ($xpath->query('//link[@rel]') ?: [] as $link_node) {
            $rel = strtolower((string) $link_node->getAttribute('rel'));
            if (!preg_match('/(?:^|\s)stylesheet(?:\s|$)/', $rel)) {
                continue;
            }
            $href = trim((string) $link_node->getAttribute('href'));
            if ($href === '') {
                continue;
            }
            $css_url = self::resolve_source_url($url, $href);
            if ($css_url === '' || !self::is_safe_source_url($css_url)) {
                continue;
            }
            $css_response = wp_safe_remote_get($css_url, [
                'timeout' => $settings['css_timeout'],
                'redirection' => 3,
                'limit_response_size' => 1024 * 1024,
                'headers' => ['Accept' => 'text/css,*/*;q=0.1'],
            ]);
            if (is_wp_error($css_response)) {
                continue;
            }
            $css_status = (int) wp_remote_retrieve_response_code($css_response);
            if ($css_status < 200 || $css_status >= 400) {
                continue;
            }
            $css_type = strtolower((string) wp_remote_retrieve_header($css_response, 'content-type'));
            if ($css_type !== '' && !str_contains($css_type, 'text/css')) {
                continue;
            }
            $css = self::clean_css((string) wp_remote_retrieve_body($css_response), $css_url);
            if ($css !== '') {
                $style_parts[] = $css;
            }
            if (count($style_parts) >= $settings['max_css']) {
                break;
            }
        }

        // Remove non-content elements after stylesheet extraction.
        foreach ($xpath->query('//script | //style | //noscript | //template') ?: [] as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }

        $content_node = null;
        if ($include_selector !== '') {
            $matches = self::find_selector_nodes($xpath, $include_selector);
            if ($matches && $matches->length > 0) {
                $content_node = $matches->item(0);
            } else {
                throw new RuntimeException('The requested include selector "' . $include_selector . '" was not found on the source page.');
            }
        } else {
            $main_nodes = $xpath->query('//main | //article');
            if ($main_nodes && $main_nodes->length > 0) {
                $content_node = $main_nodes->item(0);
            } else {
                $body_nodes = $xpath->query('//body');
                if ($body_nodes && $body_nodes->length > 0) {
                    $content_node = $body_nodes->item(0);
                }
            }
        }

        if (!$content_node) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            throw new RuntimeException('No usable HTML content container was found on the source page.');
        }

        if ($exclude_selector !== '') {
            $excluded = self::find_selector_nodes($xpath, $exclude_selector, $content_node);
            if ($excluded) {
                foreach ($excluded as $node) {
                    if ($node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        }

        $content = '';
        foreach ($content_node->childNodes as $child) {
            $content .= $dom->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $content = trim($content);
        if ($content === '') {
            throw new RuntimeException('The source page did not contain usable content.');
        }

        $content = self::transform_imported_html($content, $url, $settings);
        $content = self::clean_imported_html($content);
        if ($content === '') {
            throw new RuntimeException('The imported HTML was empty after sanitization.');
        }

        $styles = [];
        foreach ($style_parts as $css) {
            $css = self::clean_css($css, $url);
            if ($css !== '') {
                $styles[$css] = true;
            }
        }

        return [
            'content' => $content,
            'css' => implode("\n\n", array_keys($styles)),
        ];
    }

    private static function normalize_import_settings(array $settings): array {
        $profile = sanitize_key((string) ($settings['resource_profile'] ?? 'balanced'));
        $profiles = [
            'stable' => ['page_timeout' => 30, 'css_timeout' => 20, 'image_timeout' => 30, 'max_images' => 15, 'max_css' => 8],
            'balanced' => ['page_timeout' => 20, 'css_timeout' => 15, 'image_timeout' => 20, 'max_images' => 30, 'max_css' => 12],
            'fast' => ['page_timeout' => 12, 'css_timeout' => 8, 'image_timeout' => 12, 'max_images' => 50, 'max_css' => 12],
        ];
        if (!isset($profiles[$profile])) $profile = 'balanced';
        $result = $profiles[$profile];
        $result['resource_profile'] = $profile;
        $result['rewrite_internal_links'] = !empty($settings['rewrite_internal_links']);
        $result['download_images'] = !empty($settings['download_images']);
        $custom_max_images = isset($settings['max_images']) ? absint($settings['max_images']) : 0;
        if ($custom_max_images > 0) $result['max_images'] = min($custom_max_images, 100);
        return $result;
    }

    private static function transform_imported_html(string $html, string $source_url, array $settings): string {
        if (!class_exists('DOMDocument')) return $html;
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        if (!$dom->loadHTML('<?xml encoding="UTF-8"><div id="tms-wbt-transform-root">' . $html . '</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            libxml_clear_errors(); libxml_use_internal_errors($previous); return $html;
        }
        $xpath = new DOMXPath($dom);
        $root = $xpath->query('//div[@id="tms-wbt-transform-root"]')->item(0);
        if (!$root) { libxml_clear_errors(); libxml_use_internal_errors($previous); return $html; }

        $image_count = 0;
        foreach ($xpath->query('.//img[@src]', $root) ?: [] as $img) {
            if (!$img instanceof DOMElement) continue;
            $src = trim((string) $img->getAttribute('src'));
            $resolved = self::resolve_source_url($source_url, $src);
            if ($resolved === '' || !self::is_safe_source_url($resolved)) continue;
            $alt = trim((string) $img->getAttribute('alt'));
            if ($settings['download_images'] && $image_count < $settings['max_images']) {
                $attachment_id = self::import_source_image($resolved, $settings, $alt);
                if ($attachment_id > 0) {
                    $local_url = wp_get_attachment_url($attachment_id);
                    if ($local_url) { $img->setAttribute('src', $local_url); $image_count++; }
                }
            }
            if ($alt === '') {
                $path = (string) wp_parse_url($resolved, PHP_URL_PATH);
                $fallback = sanitize_text_field(pathinfo($path, PATHINFO_FILENAME));
                $fallback = trim(preg_replace('/[-_]+/', ' ', $fallback));
                if ($fallback !== '') {
                    $img->setAttribute('alt', ucwords($fallback));
                } else {
                    // Keep an explicit empty alt instead of exposing an image
                    // with no accessible name to assistive technology.
                    $img->setAttribute('alt', '');
                }
            }
        }

        if ($settings['rewrite_internal_links']) {
            $source_parts = wp_parse_url($source_url);
            $source_host = strtolower((string) ($source_parts['host'] ?? ''));
            if ($source_host !== '') {
                foreach ($xpath->query('.//a[@href]', $root) ?: [] as $anchor) {
                    if (!$anchor instanceof DOMElement) continue;
                    $href = trim((string) $anchor->getAttribute('href'));
                    if ($href === '' || str_starts_with($href, '#') || preg_match('#^(?:mailto:|tel:|javascript:|data:)#i', $href)) continue;
                    $absolute = self::resolve_source_url($source_url, $href);
                    if ($absolute === '' || !self::is_safe_source_url($absolute)) continue;
                    $parts = wp_parse_url($absolute);
                    if (!$parts || strtolower((string) ($parts['host'] ?? '')) !== $source_host) continue;
                    $path = (string) ($parts['path'] ?? '/');
                    $destination = home_url($path === '' ? '/' : $path);
                    if (!empty($parts['query'])) $destination .= '?' . $parts['query'];
                    if (!empty($parts['fragment'])) $destination .= '#' . $parts['fragment'];
                    $anchor->setAttribute('href', esc_url_raw($destination));
                    if (strtolower((string) $anchor->getAttribute('target')) === '_blank') {
                        $anchor->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }
        }

        $output = '';
        foreach ($root->childNodes as $child) $output .= $dom->saveHTML($child);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        return trim($output);
    }

    private static function import_source_image(string $url, array $settings, string $alt = ""): int {
        if (!self::is_safe_source_url($url)) return 0;
        $existing = get_posts(['post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>1,'fields'=>'ids','meta_key'=>'_tms_wbt_imported_source_url','meta_value'=>esc_url_raw($url)]);
        if ($existing) {
            $existing_id = (int) $existing[0];
            if ($alt !== '') {
                update_post_meta($existing_id, '_wp_attachment_image_alt', sanitize_text_field($alt));
            }
            return $existing_id;
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = download_url($url, $settings['image_timeout']);
        if (is_wp_error($tmp)) return 0;
        $file_size = @filesize($tmp);
        if ($file_size !== false && $file_size > 8 * 1024 * 1024) { @unlink($tmp); return 0; }
        $filename = sanitize_file_name(basename((string) wp_parse_url($url, PHP_URL_PATH)) ?: 'imported-image');
        if (!preg_match('/\.(jpe?g|png|gif|webp|avif)$/i', $filename)) $filename .= '.jpg';
        $attachment_id = media_handle_sideload(['name'=>$filename,'tmp_name'=>$tmp], 0, '', ['post_status'=>'inherit']);
        if (is_wp_error($attachment_id)) { @unlink($tmp); return 0; }
        if ($alt !== '') {
            update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt));
        }
        update_post_meta((int) $attachment_id, '_tms_wbt_imported_source_url', esc_url_raw($url));
        return (int) $attachment_id;
    }

    private static function clean_imported_html(string $html): string {
        if (!class_exists('DOMDocument')) {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        if (!$dom->loadHTML('<?xml encoding="UTF-8"><div id="tms-wbt-import-root">' . $html . '</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return '';
        }

        $xpath = new DOMXPath($dom);
        $root = $xpath->query('//div[@id="tms-wbt-import-root"]')->item(0);
        if (!$root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return '';
        }

        // Remove layout wrappers while preserving their children.
        foreach ($xpath->query('.//div | .//span', $root) ?: [] as $node) {
            if (!$node->parentNode) {
                continue;
            }
            while ($node->firstChild) {
                $node->parentNode->insertBefore($node->firstChild, $node);
            }
            $node->parentNode->removeChild($node);
        }

        $allowed = [
            'a','abbr','b','blockquote','br','caption','code','col','colgroup','dd','del','details','div','dl','dt','em','figure','figcaption',
            'h1','h2','h3','h4','h5','h6','hr','i','img','ins','kbd','li','mark','ol','p','pre','q','s','samp','small','strong','sub','summary','sup',
            'table','tbody','td','tfoot','th','thead','tr','u','ul',
        ];

        // Keep only semantic/content tags and remove every class, ID and inline style.
        foreach ($xpath->query('.//*', $root) ?: [] as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($node->tagName);
            if (!in_array($tag, $allowed, true)) {
                if ($node->parentNode) {
                    while ($node->firstChild) {
                        $node->parentNode->insertBefore($node->firstChild, $node);
                    }
                    $node->parentNode->removeChild($node);
                }
                continue;
            }
            for ($i = $node->attributes->length - 1; $i >= 0; $i--) {
                $attr = $node->attributes->item($i);
                if (!$attr) {
                    continue;
                }
                $name = strtolower($attr->name);
                $keep = in_array($name, [
                    'href','title','target','rel','download','src','alt','width','height','loading','decoding','colspan','rowspan','scope','headers','cite','datetime',
                ], true);
                if (!$keep || in_array($name, ['class','id','style'], true)) {
                    $node->removeAttributeNode($attr);
                }
            }
        }

        foreach ($xpath->query('.//a[@target="_blank"]', $root) ?: [] as $anchor) {
            if ($anchor instanceof DOMElement) {
                $anchor->setAttribute('rel', 'noopener noreferrer');
            }
        }
        foreach ($xpath->query('.//img', $root) ?: [] as $img) {
            if ($img instanceof DOMElement && !$img->hasAttribute('alt')) {
                $img->setAttribute('alt', '');
            }
        }

        $content = '';
        foreach ($root->childNodes as $child) {
            $content .= $dom->saveHTML($child);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim(wp_kses($content, self::import_allowed_html()));
    }

    private static function import_allowed_html(): array {
        return [
            'a' => ['href' => true, 'title' => true, 'target' => true, 'rel' => true, 'download' => true],
            'abbr' => ['title' => true], 'b' => [], 'blockquote' => ['cite' => true], 'br' => [], 'caption' => [], 'code' => [],
            'col' => ['span' => true], 'colgroup' => ['span' => true], 'dd' => [], 'del' => ['datetime' => true], 'details' => [], 'dl' => [], 'dt' => [],
            'em' => [], 'figure' => [], 'figcaption' => [], 'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [], 'hr' => [],
            'i' => [], 'img' => ['src' => true, 'alt' => true, 'width' => true, 'height' => true, 'loading' => true, 'decoding' => true],
            'ins' => ['datetime' => true], 'kbd' => [], 'li' => [], 'mark' => [], 'ol' => [], 'p' => [], 'pre' => [], 'q' => ['cite' => true],
            's' => [], 'samp' => [], 'small' => [], 'strong' => [], 'sub' => [], 'summary' => [], 'sup' => [], 'table' => [], 'tbody' => [], 'td' => ['colspan' => true, 'rowspan' => true, 'headers' => true],
            'tfoot' => [], 'th' => ['colspan' => true, 'rowspan' => true, 'scope' => true, 'headers' => true], 'thead' => [], 'tr' => [], 'u' => [], 'ul' => [],
        ];
    }

    public static function prepend_imported_css(string $content): string {
        if (is_admin() || !is_singular('page') || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        $css = (string) get_post_meta(get_the_ID(), '_tms_wbt_imported_css', true);
        if ($css === '') {
            return $content;
        }
        return "<style data-tms-wbt-imported-css>\n" . $css . "\n</style>\n" . $content;
    }

    private static function normalize_content_selector(string $selector): string {
        $selector = trim($selector);
        if ($selector === '') {
            return '';
        }
        if (!preg_match('/^(?:\.[A-Za-z0-9_-]+|#[A-Za-z0-9_-]+)$/', $selector)) {
            throw new RuntimeException('Selectors must be a single class or ID such as .content or #content.');
        }
        return $selector;
    }

    private static function find_selector_nodes(DOMXPath $xpath, string $selector, ?DOMNode $context = null): ?DOMNodeList {
        $selector = self::normalize_content_selector($selector);
        if ($selector === '') {
            return null;
        }
        if ($selector[0] === '#') {
            $id = substr($selector, 1);
            $query = './/*[@id=' . self::xpath_literal($id) . ']' . ($context ? '' : ' | //*[@id=' . self::xpath_literal($id) . ']');
        } else {
            $class = substr($selector, 1);
            $query = './/*[contains(concat(" ", normalize-space(@class), " "), ' . self::xpath_literal(' ' . $class . ' ') . ')]' . ($context ? '' : ' | //*[contains(concat(" ", normalize-space(@class), " "), ' . self::xpath_literal(' ' . $class . ' ') . ')]');
        }
        return $context ? $xpath->query($query, $context) : $xpath->query($query);
    }

    private static function xpath_literal(string $value): string {
        if (!str_contains($value, "'")) {
            return "'" . $value . "'";
        }
        if (!str_contains($value, '"')) {
            return '"' . $value . '"';
        }
        $parts = explode("'", $value);
        return 'concat(' . implode(', "\'", ', array_map(static fn(string $part): string => "'" . $part . "'", $parts)) . ')';
    }

    private static function resolve_source_url(string $base_url, string $relative): string {
        $relative = trim($relative);
        if ($relative === '' || str_starts_with($relative, 'data:') || str_starts_with($relative, '#')) {
            return '';
        }
        if (preg_match('#^https?://#i', $relative)) {
            return $relative;
        }
        $base = wp_parse_url($base_url);
        if (!$base || empty($base['scheme']) || empty($base['host'])) {
            return '';
        }
        if (str_starts_with($relative, '//')) {
            return $base['scheme'] . ':' . $relative;
        }
        $path = (string) ($base['path'] ?? '/');
        $dir = str_ends_with($path, '/') ? $path : dirname($path) . '/';
        $joined = str_starts_with($relative, '/') ? $relative : $dir . $relative;
        $segments = [];
        foreach (explode('/', $joined) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }
        return $base['scheme'] . '://' . $base['host'] . '/' . implode('/', $segments);
    }

    private static function clean_css(string $css, string $base_url): string {
        $css = trim($css);
        if ($css === '') {
            return '';
        }

        // Remove @font-face blocks and every font-family declaration as requested.
        $css = preg_replace('/@font-face\s*\{(?:[^{}]|\{[^{}]*\})*\}/is', '', $css);
        $css = preg_replace('/font-family\s*:\s*[^;}{]+;?/i', '', $css);
        $css = preg_replace('/font:\s*[^;}{]+;?/i', '', $css);

        // Make relative asset URLs resolvable from the original site.
        $css = preg_replace_callback('/url\(\s*(["\']?)([^"\')]+)\1\s*\)/i', static function (array $m) use ($base_url): string {
            $asset = trim($m[2]);
            if ($asset === '' || str_starts_with($asset, 'data:') || str_starts_with($asset, '#') || preg_match('#^(?:https?:|//)#i', $asset)) {
                return 'url(' . $m[1] . $asset . $m[1] . ')';
            }
            $resolved = self::resolve_source_url($base_url, $asset);
            return $resolved !== '' ? 'url(' . $m[1] . $resolved . $m[1] . ')' : $m[0];
        }, $css);

        return trim($css);
    }

    private static function strip_font_family_from_html(string $html): string {
        return preg_replace_callback('/(\sstyle\s*=\s*["\'])(.*?)(["\'])/is', static function (array $m): string {
            $style = preg_replace('/(?:^|;)\s*(?:font-family|font)\s*:[^;]+;?/i', '', $m[2]);
            $style = trim($style, " ;\t\r\n");
            return $style === '' ? '' : $m[1] . esc_attr($style) . $m[3];
        }, $html) ?? $html;
    }

    private static function find_existing_page(string $migration_id, string $slug) {
        $posts = get_posts([
            'post_type' => 'page',
            'post_status' => ['publish', 'draft', 'private', 'pending', 'future'],
            'posts_per_page' => 1,
            'meta_key' => '_tms_wbt_migration_id',
            'meta_value' => $migration_id,
            'fields' => 'ids',
        ]);
        if ($posts) {
            return (int) $posts[0];
        }

        $path = self::normalize_path($slug);
        if ($path === '/') {
            $front = (int) get_option('page_on_front');
            return $front ?: 0;
        }

        $post_id = url_to_postid(home_url($path));
        return $post_id ? (int) $post_id : 0;
    }

    private static function set_yoast_meta(int $post_id, array $record): void {
        if ($record['meta_title'] !== '') {
            update_post_meta($post_id, '_yoast_wpseo_title', wp_strip_all_tags($record['meta_title']));
        }
        if ($record['meta_description'] !== '') {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', wp_strip_all_tags($record['meta_description']));
        }
    }

    private static function build_primary_menu(array $records, array $post_map): array {
        $menu_name = 'Primary Menu';
        $menu = wp_get_nav_menu_object($menu_name);

        if (!$menu) {
            $menu_id = wp_create_nav_menu($menu_name);
            if (is_wp_error($menu_id)) {
                return ['items' => 0, 'errors' => [$menu_id->get_error_message()]];
            }
        } else {
            $menu_id = (int) $menu->term_id;
        }

        $existing_items = wp_get_nav_menu_items($menu_id) ?: [];
        foreach ($existing_items as $item) {
            wp_delete_post((int) $item->ID, true);
        }

        $item_map = [];
        $count = 0;
        $errors = [];

        foreach ($records as $record) {
            $type = $record['type'];

            $custom_menu_types = ['hierarchical', 'link_out', 'redirect_only', 'anchor'];
            if (in_array($type, $custom_menu_types, true)) {
                $parent_item = $record['parent_id'] && isset($item_map[$record['parent_id']])
                    ? $item_map[$record['parent_id']]
                    : 0;

                $url = self::menu_url_for_record($record);
                $item_id = wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title' => $record['title'],
                    'menu-item-url' => esc_url_raw($url),
                    'menu-item-status' => 'publish',
                    'menu-item-parent-id' => $parent_item,
                    'menu-item-type' => 'custom',
                ]);

                if (is_wp_error($item_id)) {
                    $errors[] = sprintf('Menu item "%s": %s', $record['title'], $item_id->get_error_message());
                } else {
                    $item_map[$record['id']] = (int) $item_id;
                    $count++;
                }
                continue;
            }

            if (isset($post_map[$record['id']])) {
                $post_id = $post_map[$record['id']];
                $url = get_permalink($post_id);
            } else {
                continue;
            }

            $parent_item = $record['parent_id'] && isset($item_map[$record['parent_id']])
                ? $item_map[$record['parent_id']]
                : 0;

            $args = [
                'menu-item-title' => $record['title'],
                'menu-item-status' => 'publish',
                'menu-item-parent-id' => $parent_item,
            ];

            if ($post_id) {
                $args['menu-item-object-id'] = $post_id;
                $args['menu-item-object'] = 'page';
                $args['menu-item-type'] = 'post_type';
            } else {
                $args['menu-item-url'] = esc_url_raw($url);
                $args['menu-item-type'] = 'custom';
            }

            $item_id = wp_update_nav_menu_item($menu_id, 0, $args);
            if (is_wp_error($item_id)) {
                $errors[] = sprintf('Menu item "%s": %s', $record['title'], $item_id->get_error_message());
            } else {
                $item_map[$record['id']] = (int) $item_id;
                $count++;
            }
        }

        self::assign_primary_menu_location($menu_id);
        update_option('tms_wbt_primary_menu_id', $menu_id, false);

        return ['items' => $count, 'errors' => $errors];
    }

    private static function assign_primary_menu_location(int $menu_id): void {
        $locations = get_nav_menu_locations();
        $registered = get_registered_nav_menus();

        $target = null;

        // Prefer a registered location whose label identifies it as the primary
        // navigation. This is the location the cloned theme is configured to use.
        foreach ($registered as $location_key => $location_label) {
            $label = strtolower(wp_strip_all_tags((string) $location_label));
            if (str_contains($label, 'primary') && (str_contains($label, 'navigation') || str_contains($label, 'nav'))) {
                $target = $location_key;
                break;
            }
        }

        // Fall back to conventional location keys when the label is not descriptive.
        if ($target === null) {
            foreach (['primary', 'primary-menu', 'main-menu', 'header'] as $candidate) {
                if (isset($registered[$candidate])) {
                    $target = $candidate;
                    break;
                }
            }
        }

        if ($target === null && $registered) {
            $target = array_key_first($registered);
        }

        if ($target !== null) {
            $locations[$target] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }

    private static function menu_url_for_record(array $record): string {
        $old_url = trim((string) ($record['old_url'] ?? ''));
        $new_slug = trim((string) ($record['new_slug'] ?? ''));

        if ($old_url !== '') {
            return $old_url;
        }

        if ($new_slug !== '') {
            if (preg_match('#^https?://#i', $new_slug)) {
                return $new_slug;
            }
            return home_url(self::normalize_path($new_slug));
        }

        return '#';
    }

    private static function set_front_page_if_present(array $records, array $post_map): void {
        foreach ($records as $record) {
            if ($record['depth'] === 0 && strcasecmp($record['title'], 'Home') === 0 && isset($post_map[$record['id']])) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', (int) $post_map[$record['id']]);
                break;
            }
        }
    }

    private static function save_record_map(array $records, array $post_map): void {
        $safe = [];
        foreach ($records as $record) {
            if (isset($post_map[$record['id']])) {
                $safe[$record['id']] = $post_map[$record['id']];
            }
        }
        update_option('tms_wbt_record_map', $safe, false);
    }

    public static function normalize_page_type(string $value): string {
        $v = strtolower(trim(preg_replace('/\s+/u', ' ', $value)));
        return match ($v) {
            'content page', 'merge content' => 'content',
            'bucket page' => 'bucket',
            'link out' => 'link_out',
            'preset page' => 'preset',
            'anchor link' => 'anchor',
            'redirect only' => 'redirect_only',
            'hierarchical' => 'hierarchical',
            'form page' => 'form',
            default => 'unknown',
        };
    }

    private static function leading_depth(string $title): int {
        if (preg_match('/^(-{2})+/', trim($title), $m)) {
            return (int) (strlen($m[0]) / 2);
        }
        return 0;
    }

    private static function normalize_path(string $path): string {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path);
        return trailingslashit($path);
    }

    private static function basename_from_path(string $path): string {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '';
        }
        $path = trim($path, '/');
        $parts = explode('/', $path);
        return sanitize_title(end($parts));
    }

    private static function is_safe_source_url(string $url): bool {
        $parts = wp_parse_url($url);
        if (!$parts || empty($parts['host'])) {
            return false;
        }
        if (!empty($parts['user']) || !empty($parts['pass'])) {
            return false;
        }
        return in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true);
    }
}
