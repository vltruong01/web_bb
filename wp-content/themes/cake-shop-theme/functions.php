<?php
remove_action('wp_head', 'wp_site_icon', 99);

function cake_shop_add_favicon() {
    $favicon_path = get_template_directory() . '/assets/images/favicon-32.png';
    $favicon_url = get_template_directory_uri() . '/assets/images/favicon-32.png';
    $favicon_48_path = get_template_directory() . '/assets/images/favicon-48.png';
    $favicon_48_url = get_template_directory_uri() . '/assets/images/favicon-48.png';

    $theme_version = wp_get_theme()->get('Version');
    if (!$theme_version) {
        $theme_version = '1.0.0';
    }

    $favicon_version = file_exists($favicon_path) ? filemtime($favicon_path) : $theme_version;
    $favicon_href = $favicon_url . '?v=' . $favicon_version;
    $favicon_48_href = $favicon_48_url . '?v=' . (file_exists($favicon_48_path) ? filemtime($favicon_48_path) : $favicon_version);
    ?>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url($favicon_href); ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?php echo esc_url($favicon_48_href); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url($favicon_href); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo esc_url($favicon_href); ?>">
    <?php
}
add_action('wp_head', 'cake_shop_add_favicon', 1);

function cake_shop_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');

    register_nav_menus([
        'primary_menu' => 'Primary Menu',
    ]);
}
add_action('after_setup_theme', 'cake_shop_theme_setup');

function cake_shop_hide_frontend_admin_bar($show_admin_bar) {
    return is_admin() ? $show_admin_bar : false;
}
add_filter('show_admin_bar', 'cake_shop_hide_frontend_admin_bar');

function cake_shop_enqueue_assets() {
    $theme_version = wp_get_theme()->get('Version');
    if (!$theme_version) {
        $theme_version = '1.0.0';
    }

    $style_path = get_stylesheet_directory() . '/style.css';
    $style_version = file_exists($style_path) ? filemtime($style_path) : $theme_version;

    wp_enqueue_style(
        'cake-shop-style',
        get_stylesheet_uri(),
        [],
        $style_version
    );

    $theme_script_path = get_template_directory() . '/assets/js/theme.js';
    $theme_script_version = file_exists($theme_script_path) ? filemtime($theme_script_path) : $theme_version;

    wp_enqueue_script(
        'cake-shop-theme',
        get_template_directory_uri() . '/assets/js/theme.js',
        [],
        $theme_script_version,
        true
    );

    if (
        is_page_template('page-menu-banh.php') ||
        is_page_template('page-banh-kem.php') ||
        is_page('menu-banh') ||
        is_page('banh-kem') ||
        is_singular('cake') ||
        is_front_page()
    ) {
        $lightbox_path = get_template_directory() . '/assets/js/cake-lightbox.js';
        $lightbox_version = file_exists($lightbox_path) ? filemtime($lightbox_path) : $theme_version;

        wp_enqueue_script(
            'cake-shop-lightbox',
            get_template_directory_uri() . '/assets/js/cake-lightbox.js',
            [],
            $lightbox_version,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'cake_shop_enqueue_assets');

function cake_shop_optimize_uploaded_images($quality, $mime_type) {
    return in_array($mime_type, ['image/jpeg', 'image/webp'], true) ? 82 : $quality;
}
add_filter('wp_editor_set_quality', 'cake_shop_optimize_uploaded_images', 10, 2);

function cake_shop_limit_large_uploads($threshold) {
    return 2560;
}
add_filter('big_image_size_threshold', 'cake_shop_limit_large_uploads');

function cake_shop_get_default_store_info() {
    return [
        'shop_name'      => 'Tiệm bánh Hồng Lập',
        'phone'          => '0964558311',
        'address'        => '76 Nguyễn Trường Tộ, Khu 2, Phú Quốc, An Giang, Vietnam',
        'zalo_link'      => 'https://zalo.me/0964558311',
        'facebook_link'  => 'https://www.facebook.com/share/1B9QueU9JZ/',
        'opening_hours'  => '8:00 - 20:00 mỗi ngày',
        'delivery_areas' => 'Phú Quốc và khu vực lân cận',
        'delivery_note'  => 'Vui lòng liên hệ tiệm trước để xác nhận thời gian giao bánh và phí giao hàng.',
    ];
}

function cake_shop_sanitize_store_info($values) {
    $defaults = cake_shop_get_default_store_info();
    $values = is_array($values) ? $values : [];

    return [
        'shop_name'      => isset($values['shop_name']) ? sanitize_text_field($values['shop_name']) : $defaults['shop_name'],
        'phone'          => isset($values['phone']) ? preg_replace('/[^0-9+\s]/', '', (string) $values['phone']) : $defaults['phone'],
        'address'        => isset($values['address']) ? sanitize_textarea_field($values['address']) : $defaults['address'],
        'zalo_link'      => isset($values['zalo_link']) ? esc_url_raw($values['zalo_link']) : $defaults['zalo_link'],
        'facebook_link'  => isset($values['facebook_link']) ? esc_url_raw($values['facebook_link']) : $defaults['facebook_link'],
        'opening_hours'  => isset($values['opening_hours']) ? sanitize_text_field($values['opening_hours']) : $defaults['opening_hours'],
        'delivery_areas' => isset($values['delivery_areas']) ? sanitize_text_field($values['delivery_areas']) : $defaults['delivery_areas'],
        'delivery_note'  => isset($values['delivery_note']) ? sanitize_textarea_field($values['delivery_note']) : $defaults['delivery_note'],
    ];
}

function cake_shop_get_store_info() {
    $defaults = cake_shop_get_default_store_info();
    $saved = get_option('cake_shop_store_info', []);

    if (!is_array($saved)) {
        $saved = [];
    }

    $legacy_address = '76 Nguyễn Trường Tộ, An Thới, Phú Quốc, Kiên Giang, Việt Nam';
    if (isset($saved['address']) && $saved['address'] === $legacy_address) {
        $saved['address'] = $defaults['address'];
        update_option('cake_shop_store_info', cake_shop_sanitize_store_info($saved));
    }

    return wp_parse_args(cake_shop_sanitize_store_info($saved), $defaults);
}

function cake_shop_get_store_field($key, $fallback = '') {
    $info = cake_shop_get_store_info();
    return isset($info[$key]) && $info[$key] !== '' ? $info[$key] : $fallback;
}

function cake_shop_get_phone_href($phone = '') {
    $phone = $phone ?: cake_shop_get_store_field('phone');
    return 'tel:' . preg_replace('/\s+/', '', (string) $phone);
}

function cake_shop_get_contact_message($action = 'Nhắn tiệm qua Zalo, Messenger hoặc gọi điện thật nhanh') {
    $action = trim((string) $action);
    return $action !== '' ? $action : 'Nhắn tiệm qua Zalo, Messenger hoặc gọi điện thật nhanh';
}

function cake_shop_get_shop_info() {
    $store_info = cake_shop_get_store_info();
    $phone_raw = isset($store_info['phone']) ? (string) $store_info['phone'] : '';
    $digits = preg_replace('/\D+/', '', $phone_raw);
    $phone_display = trim(preg_replace('/\s+/', ' ', $phone_raw));

    if ($phone_display === '' && $digits !== '') {
        $phone_display = $digits;
    }

    return [
        'name'           => $store_info['shop_name'],
        'phone_raw'      => $digits !== '' ? $digits : $phone_raw,
        'phone_display'  => $phone_display,
        'zalo_url'       => $store_info['zalo_link'],
        'messenger_url'  => $store_info['facebook_link'],
        'address'        => $store_info['address'],
        'hours'          => $store_info['opening_hours'],
    ];
}

function cake_shop_get_contact_url($type) {
    $shop_info = cake_shop_get_shop_info();

    if ($type === 'phone') {
        return cake_shop_get_phone_href($shop_info['phone_raw']);
    }

    if ($type === 'zalo') {
        return $shop_info['zalo_url'];
    }

    if ($type === 'messenger') {
        return $shop_info['messenger_url'];
    }

    return '';
}

function cake_shop_get_nav_link_attributes($page_slug = '') {
    $is_current = $page_slug === '' ? is_front_page() : is_page($page_slug);
    return $is_current ? ' class="is-current" aria-current="page"' : '';
}

function cake_shop_filter_document_title($parts) {
    if (is_admin()) {
        return $parts;
    }

    $shop_name = cake_shop_get_store_field('shop_name', get_bloginfo('name'));
    $title = '';

    if (is_front_page()) {
        $title = sprintf('%s - Tiệm bánh tại Phú Quốc', $shop_name);
    } elseif (is_page('menu-banh')) {
        $title = sprintf('Menu bánh tươi | %s', $shop_name);
    } elseif (is_page('banh-kem')) {
        $title = sprintf('Bánh kem theo yêu cầu | %s', $shop_name);
    } elseif (is_page('lien-he')) {
        $title = sprintf('Liên hệ và đặt bánh | %s', $shop_name);
    } elseif (is_singular('cake')) {
        $title = sprintf('%s | %s', get_the_title(get_queried_object_id()), $shop_name);
    }

    if ($title !== '') {
        $parts['title'] = $title;
        $parts['site'] = '';
        $parts['tagline'] = '';
    }

    return $parts;
}
add_filter('document_title_parts', 'cake_shop_filter_document_title');

/**
 * Keep the homepage title focused on the real products and local brand.
 */
function cake_shop_filter_homepage_title($parts) {
    if (!is_admin() && is_front_page()) {
        $shop_name = cake_shop_get_store_field('shop_name', get_bloginfo('name'));
        $parts['title'] = sprintf('%s | Bánh mì, bánh ngọt và bánh kem Phú Quốc', $shop_name);
        $parts['site'] = '';
        $parts['tagline'] = '';
    }

    return $parts;
}
add_filter('document_title_parts', 'cake_shop_filter_homepage_title', 11);

function cake_shop_add_private_page_robots() {
    if (!is_page(['quan-ly-banh', 'quan-ly-tiem', 'dang-nhap-tiem', 'gop-y-khach-hang'])) {
        return;
    }

    echo "\n<meta name=\"robots\" content=\"noindex, nofollow\">\n";
}
add_action('wp_head', 'cake_shop_add_private_page_robots', 4);

function cake_shop_get_seo_description() {
    $store_info = cake_shop_get_store_info();

    if (is_singular('cake')) {
        $description = get_the_excerpt() ?: wp_strip_all_tags(get_the_content());
        return wp_trim_words($description, 28, '');
    }

    if (is_front_page()) {
        return sprintf('%s - bánh tươi mỗi ngày và bánh kem theo yêu cầu. Xem mẫu, giá tham khảo và liên hệ đặt bánh nhanh.', $store_info['shop_name']);
    }

    if (is_singular()) {
        $description = get_the_excerpt() ?: wp_strip_all_tags(get_the_content());
        if ($description) {
            return wp_trim_words($description, 28, '');
        }
    }

    return sprintf('%s - xem mẫu bánh, bánh kem và liên hệ đặt bánh nhanh.', $store_info['shop_name']);
}

function cake_shop_get_seo_image_url() {
    if (is_singular() && has_post_thumbnail()) {
        return get_the_post_thumbnail_url(get_the_ID(), 'large');
    }

    $hero_image_id = (int) get_option('cake_shop_home_hero_image_id', 0);
    if ($hero_image_id) {
        $hero_image = wp_get_attachment_image_url($hero_image_id, 'large');
        if ($hero_image) {
            return $hero_image;
        }
    }

    $fallback_cakes = get_posts([
        'post_type' => 'cake',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'meta_key' => '_thumbnail_id',
    ]);

    return !empty($fallback_cakes) ? get_the_post_thumbnail_url($fallback_cakes[0]->ID, 'large') : '';
}

function cake_shop_add_social_meta() {
    if (is_admin() || is_feed() || is_robots()) {
        return;
    }

    $store_info = cake_shop_get_store_info();
    $title = wp_get_document_title();
    $description = cake_shop_get_seo_description();
    $url = is_singular() ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request));
    $image_url = cake_shop_get_seo_image_url();
    ?>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <link rel="canonical" href="<?php echo esc_url($url); ?>">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:type" content="<?php echo is_singular('cake') ? 'product' : 'website'; ?>">
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($store_info['shop_name']); ?>">
    <?php if ($image_url) : ?>
      <meta property="og:image" content="<?php echo esc_url($image_url); ?>">
      <meta property="og:image:alt" content="<?php echo esc_attr(is_singular('cake') ? get_the_title(get_queried_object_id()) : $store_info['shop_name']); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($description); ?>">
    <?php if ($image_url) : ?><meta name="twitter:image" content="<?php echo esc_url($image_url); ?>"><?php endif; ?>
    <?php
}
add_action('wp_head', 'cake_shop_add_social_meta', 5);

function cake_shop_add_bakery_schema() {
    if (!is_front_page()) {
        return;
    }

    $store_info = cake_shop_get_store_info();
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Bakery',
        'name' => $store_info['shop_name'],
        'description' => cake_shop_get_seo_description(),
        'url' => home_url('/'),
        'telephone' => $store_info['phone'],
        'priceRange' => '₫₫',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $store_info['address'],
            'addressLocality' => 'Phú Quốc',
            'addressRegion' => 'An Giang',
            'addressCountry' => 'VN',
        ],
        'areaServed' => $store_info['delivery_areas'],
        'hasMap' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($store_info['address']),
        'sameAs' => array_values(array_filter([$store_info['zalo_link'], $store_info['facebook_link']])),
    ];

    if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $store_info['opening_hours'], $hours_match)) {
        $schema['openingHoursSpecification'] = [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens' => $hours_match[1],
            'closes' => $hours_match[2],
        ];
    }

    $image_url = cake_shop_get_seo_image_url();
    if ($image_url) {
        $schema['image'] = $image_url;
    }

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}
add_action('wp_head', 'cake_shop_add_bakery_schema', 20);

function cake_shop_add_website_schema() {
    if (!is_front_page()) {
        return;
    }

    $shop_name = cake_shop_get_store_field('shop_name', get_bloginfo('name'));
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $shop_name,
        'alternateName' => [
            'Tiệm bánh mì Hồng Lập',
            'Tiệm bánh ngọt Hồng Lập',
            'Tiệm bánh kem Hồng Lập',
        ],
        'url' => home_url('/'),
        'publisher' => [
            '@type' => 'Bakery',
            'name' => $shop_name,
            'url' => home_url('/'),
        ],
    ];

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}
add_action('wp_head', 'cake_shop_add_website_schema', 20);

function cake_shop_get_product_offer_schema($post_id) {
    $raw_price = (string) get_post_meta($post_id, '_gia_tham_khao', true);
    preg_match_all('/\d[\d.,]*/', $raw_price, $matches);

    if (empty($matches[0])) {
        return [];
    }

    $prices = array_values(array_filter(array_map(function ($price) {
        return (int) preg_replace('/\D+/', '', $price);
    }, $matches[0])));

    if (empty($prices)) {
        return [];
    }

    $status = get_post_meta($post_id, '_trang_thai_banh', true);
    $availability_map = [
        'co-san' => 'https://schema.org/InStock',
        'tam-het' => 'https://schema.org/OutOfStock',
        'nhan-dat-truoc' => 'https://schema.org/PreOrder',
    ];
    $availability = $availability_map[$status] ?? 'https://schema.org/InStock';

    if (count($prices) > 1) {
        return [
            '@type' => 'AggregateOffer',
            'priceCurrency' => 'VND',
            'lowPrice' => (string) min($prices),
            'highPrice' => (string) max($prices),
            'availability' => $availability,
            'url' => get_permalink($post_id),
        ];
    }

    return [
        '@type' => 'Offer',
        'priceCurrency' => 'VND',
        'price' => (string) $prices[0],
        'availability' => $availability,
        'itemCondition' => 'https://schema.org/NewCondition',
        'url' => get_permalink($post_id),
    ];
}

function cake_shop_add_product_schema() {
    if (!is_singular('cake')) {
        return;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return;
    }

    $store_info = cake_shop_get_store_info();
    $description = get_the_excerpt($post_id);
    if ($description === '') {
        $description = wp_strip_all_tags(get_post_field('post_content', $post_id));
    }

    $images = cake_shop_get_gallery_image_urls($post_id, 'large');
    if (empty($images)) {
        $fallback_image = cake_shop_get_seo_image_url();
        $images = $fallback_image ? [$fallback_image] : [];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => get_the_title($post_id),
        'url' => get_permalink($post_id),
        'description' => wp_trim_words($description, 50, ''),
        'brand' => [
            '@type' => 'Brand',
            'name' => $store_info['shop_name'],
        ],
    ];

    if (!empty($images)) {
        $schema['image'] = array_values($images);
    }

    $terms = wp_get_post_terms($post_id, 'cake_category', ['fields' => 'names']);
    if (!is_wp_error($terms) && !empty($terms)) {
        $schema['category'] = $terms[0];
    }

    $offer = cake_shop_get_product_offer_schema($post_id);
    if (!empty($offer)) {
        $schema['offers'] = $offer;
    }

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}
add_action('wp_head', 'cake_shop_add_product_schema', 21);

function cake_shop_add_breadcrumb_schema() {
    if (is_admin() || is_feed() || is_robots() || is_front_page()) {
        return;
    }

    $items = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Trang chủ',
            'item' => home_url('/'),
        ],
    ];

    if (is_singular('cake')) {
        $post_id = get_queried_object_id();
        $term_slugs = wp_get_post_terms($post_id, 'cake_category', ['fields' => 'slugs']);
        $category_pages = [
            'menu-banh' => ['name' => 'Menu bánh', 'url' => home_url('/menu-banh')],
            'banh-kem' => ['name' => 'Bánh kem', 'url' => home_url('/banh-kem')],
        ];
        $category_slug = !is_wp_error($term_slugs) && !empty($term_slugs) ? $term_slugs[0] : '';

        if (isset($category_pages[$category_slug])) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => count($items) + 1,
                'name' => $category_pages[$category_slug]['name'],
                'item' => $category_pages[$category_slug]['url'],
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => get_the_title($post_id),
            'item' => get_permalink($post_id),
        ];
    } elseif (is_singular() || is_page()) {
        $post_id = get_queried_object_id();
        $items[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => get_the_title($post_id),
            'item' => get_permalink($post_id),
        ];
    } else {
        return;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}
add_action('wp_head', 'cake_shop_add_breadcrumb_schema', 22);

function cake_shop_add_sitemap_to_robots($output, $public) {
    $sitemap = 'Sitemap: ' . home_url('/wp-sitemap.xml');
    return strpos($output, $sitemap) === false ? trim($output) . "\n\n" . $sitemap . "\n" : $output;
}
add_filter('robots_txt', 'cake_shop_add_sitemap_to_robots', 10, 2);

function cake_shop_get_home_featured_query($category_slug) {
    $base_tax_query = [
        [
            'taxonomy' => 'cake_category',
            'field'    => 'slug',
            'terms'    => $category_slug,
        ]
    ];

    $visible_meta_query = [
        [
            'key'     => '_trang_thai_banh',
            'value'   => 'an',
            'compare' => '!=',
        ]
    ];

    $highlight_ids = get_posts([
        'post_type'      => 'cake',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'tax_query'      => $base_tax_query,
        'meta_query'     => array_merge([
            'relation' => 'AND',
        ], $visible_meta_query, [[
            'key'     => '_cake_highlight',
            'value'   => ['moi', 'hot'],
            'compare' => 'IN',
        ]]),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $featured_ids = $highlight_ids;

    if (count($featured_ids) < 3) {
        $remaining_ids = get_posts([
            'post_type'      => 'cake',
            'posts_per_page' => 3 - count($featured_ids),
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'tax_query'      => $base_tax_query,
            'meta_query'     => $visible_meta_query,
            'post__not_in'   => $featured_ids,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $featured_ids = array_merge($featured_ids, $remaining_ids);
    }

    if (empty($featured_ids)) {
        return new WP_Query([
            'post_type'      => 'cake',
            'posts_per_page' => 3,
            'tax_query'      => $base_tax_query,
            'meta_query'     => $visible_meta_query,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
    }

    return new WP_Query([
        'post_type'      => 'cake',
        'posts_per_page' => 3,
        'post__in'       => $featured_ids,
        'orderby'        => 'post__in',
    ]);
}

function cake_shop_render_lightbox_markup() {
    ?>
    <div class="cake-lightbox" id="cake-lightbox" aria-hidden="true">
      <div class="cake-lightbox__overlay"></div>
      <div class="cake-lightbox__dialog">
        <button class="cake-lightbox__close" type="button" aria-label="Đóng">&times;</button>

        <div class="cake-lightbox__media">
          <button class="cake-lightbox__nav cake-lightbox__nav--prev" type="button" aria-label="Ảnh trước">&#10094;</button>
          <img class="cake-lightbox__image" src="" alt="">
          <button class="cake-lightbox__nav cake-lightbox__nav--next" type="button" aria-label="Ảnh sau">&#10095;</button>
        </div>

        <div class="cake-lightbox__thumbs"></div>

        <div class="cake-lightbox__title"></div>
        <div class="cake-lightbox__price"></div>
        <div class="cake-lightbox__excerpt"></div>
        <a class="cake-lightbox__detail-action" href="#">Xem chi tiết</a>
      </div>
    </div>
    <?php
}

/**
 * Custom Post Type: Bánh
 */
function cake_shop_register_cake_post_type() {
    $labels = [
        'name'               => 'Bánh',
        'singular_name'      => 'Bánh',
        'add_new'            => 'Thêm mới',
        'add_new_item'       => 'Thêm bánh mới',
        'edit_item'          => 'Sửa bánh',
        'new_item'           => 'Bánh mới',
        'view_item'          => 'Xem bánh',
        'search_items'       => 'Tìm bánh',
        'not_found'          => 'Không tìm thấy bánh',
        'not_found_in_trash' => 'Không có bánh trong thùng rác',
        'menu_name'          => 'Bánh',
    ];

    $args = [
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-store',
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'      => ['slug' => 'banh'],
        'show_in_rest' => true,
    ];

    register_post_type('cake', $args);
}
add_action('init', 'cake_shop_register_cake_post_type');

/**
 * Taxonomy: Loại bánh
 */
function cake_shop_register_cake_taxonomy() {
    $labels = [
        'name'          => 'Loại bánh',
        'singular_name' => 'Loại bánh',
        'search_items'  => 'Tìm loại bánh',
        'all_items'     => 'Tất cả loại bánh',
        'edit_item'     => 'Sửa loại bánh',
        'update_item'   => 'Cập nhật loại bánh',
        'add_new_item'  => 'Thêm loại bánh mới',
        'new_item_name' => 'Tên loại bánh mới',
        'menu_name'     => 'Loại bánh',
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'loai-banh'],
        'show_in_rest'      => true,
    ];

    register_taxonomy('cake_category', ['cake'], $args);
}
add_action('init', 'cake_shop_register_cake_taxonomy');

function cake_shop_get_trang_thai_label($value) {
    $map = [
        'co-san'         => 'Có sẵn',
        'nhan-dat-truoc' => 'Nhận đặt trước',
        'tam-het'        => 'Tạm hết',
        'an'             => 'Ẩn',
    ];

    return $map[$value] ?? '';
}

function cake_shop_format_price($value) {
    if (empty($value)) {
        return '';
    }

    $raw = trim((string) $value);

    // Kiểm tra nếu chuỗi chứa dấu gạch ngang '-' đại diện cho khoảng giá
    if (strpos($raw, '-') !== false) {
        // Tách chuỗi thành 2 phần bằng dấu '-'
        $parts = explode('-', $raw);
        
        // Định dạng từng phần riêng biệt
        $price_min = preg_replace('/\D+/', '', trim($parts[0]));
        $price_max = preg_replace('/\D+/', '', trim($parts[1]));

        if ($price_min !== '' && $price_max !== '') {
            return number_format((int) $price_min, 0, ',', '.') . ' - ' . number_format((int) $price_max, 0, ',', '.') . 'đ';
        }
    }

    // Trường hợp nhập 1 giá đơn lẻ thông thường
    $number = preg_replace('/\D+/', '', $raw);

    if ($number === '') {
        return $raw;
    }

    return number_format((int) $number, 0, ',', '.') . 'đ';
}

function cake_shop_get_cake_detail_fields($post_id = 0) {
    $post_id = $post_id ? absint($post_id) : get_the_ID();

    return [
        'size' => get_post_meta($post_id, '_cake_size', true),
        'serving' => get_post_meta($post_id, '_cake_serving', true),
        'preorder' => get_post_meta($post_id, '_cake_preorder_time', true),
    ];
}

function cake_shop_get_allowed_statuses_by_category($category_slug) {
    if ($category_slug === 'menu-banh') {
        return [
            'co-san'  => 'Có sẵn',
            'tam-het' => 'Tạm hết',
            'an'      => 'Ẩn',
        ];
    }

    if ($category_slug === 'banh-kem') {
        return [
            'co-san'         => 'Có sẵn',
            'nhan-dat-truoc' => 'Nhận đặt trước',
            'an'             => 'Ẩn',
        ];
    }

    return [];
}

function cake_shop_get_next_status_label($current_status, $category_slug) {
    if ($category_slug === 'menu-banh') {
        if ($current_status === 'co-san') {
            return ['key' => 'tam-het', 'label' => 'Tạm hết'];
        }
        if ($current_status === 'tam-het' || $current_status === 'an') {
            return ['key' => 'co-san', 'label' => 'Có sẵn'];
        }
    }

    if ($category_slug === 'banh-kem') {
        if ($current_status === 'co-san') {
            return ['key' => 'nhan-dat-truoc', 'label' => 'Nhận đặt trước'];
        }
        if ($current_status === 'nhan-dat-truoc' || $current_status === 'an') {
            return ['key' => 'co-san', 'label' => 'Có sẵn'];
        }
    }

    return null;
}

function cake_shop_get_secondary_status_label($current_status, $category_slug) {
    if ($current_status !== 'an') {
        return null;
    }

    if ($category_slug === 'menu-banh') {
        return ['key' => 'tam-het', 'label' => 'Tạm hết'];
    }

    if ($category_slug === 'banh-kem') {
        return ['key' => 'nhan-dat-truoc', 'label' => 'Nhận đặt trước'];
    }

    return null;
}

function cake_shop_get_hide_status_label($current_status, $category_slug) {
    if ($current_status === 'an') {
        return null;
    }

    $allowed_statuses = cake_shop_get_allowed_statuses_by_category($category_slug);

    if (!isset($allowed_statuses['an'])) {
        return null;
    }

    return ['key' => 'an', 'label' => 'Ẩn'];
}

function cake_shop_get_highlight_label($value) {
    $map = [
        'moi' => 'New',
        'hot' => 'Hot',
    ];

    return $map[$value] ?? '';
}

function cake_shop_get_highlight_class($value) {
    $map = [
        'moi' => 'cake-highlight-badge--new',
        'hot' => 'cake-highlight-badge--hot',
    ];

    return $map[$value] ?? '';
}

function cake_shop_get_highlight_options() {
    return [
        ''    => 'Không có',
        'moi' => 'New',
        'hot' => 'Hot',
    ];
}

function cake_shop_count_highlighted_cakes_by_category($category_slug, $exclude_post_id = 0) {
    $query_args = [
        'post_type'      => 'cake',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'tax_query'      => [
            [
                'taxonomy' => 'cake_category',
                'field'    => 'slug',
                'terms'    => $category_slug,
            ]
        ],
        'meta_query'     => [
            [
                'key'     => '_cake_highlight',
                'value'   => ['moi', 'hot'],
                'compare' => 'IN',
            ]
        ],
    ];

    if ($exclude_post_id > 0) {
        $query_args['post__not_in'] = [$exclude_post_id];
    }

    $query = new WP_Query($query_args);

    return (int) $query->found_posts;
}

function cake_shop_get_gallery_ids($post_id) {
    $gallery_ids = get_post_meta($post_id, '_cake_gallery_ids', true);

    if (empty($gallery_ids)) {
        return [];
    }

    if (is_array($gallery_ids)) {
        return array_values(array_filter(array_map('absint', $gallery_ids)));
    }

    $gallery_ids = explode(',', (string) $gallery_ids);
    return array_values(array_filter(array_map('absint', $gallery_ids)));
}

function cake_shop_set_gallery_ids($post_id, $ids) {
    $ids = array_values(array_filter(array_map('absint', (array) $ids)));
    update_post_meta($post_id, '_cake_gallery_ids', implode(',', $ids));
}

function cake_shop_get_gallery_image_urls($post_id, $size = 'large') {
    $urls = [];

    if (has_post_thumbnail($post_id)) {
        $featured_url = get_the_post_thumbnail_url($post_id, $size);
        if ($featured_url) {
            $urls[] = $featured_url;
        }
    }

    $gallery_ids = cake_shop_get_gallery_ids($post_id);

    foreach ($gallery_ids as $attachment_id) {
        $url = wp_get_attachment_image_url($attachment_id, $size);
        if ($url && !in_array($url, $urls, true)) {
            $urls[] = $url;
        }
    }

    return $urls;
}

/**
 * AJAX: xóa ảnh phụ không reload
 */
function cake_shop_ajax_remove_gallery_image() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Chưa đăng nhập.'], 403);
    }

    $cake_id = isset($_POST['cake_id']) ? absint($_POST['cake_id']) : 0;
    $image_id = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!$cake_id || !$image_id) {
        wp_send_json_error(['message' => 'Thiếu dữ liệu.'], 400);
    }

    if (!wp_verify_nonce($nonce, 'cake_remove_gallery_' . $cake_id . '_' . $image_id)) {
        wp_send_json_error(['message' => 'Nonce không hợp lệ.'], 403);
    }

    if (!current_user_can('edit_post', $cake_id)) {
        wp_send_json_error(['message' => 'Không có quyền.'], 403);
    }

    $ids = cake_shop_get_gallery_ids($cake_id);
    $ids = array_values(array_filter($ids, function($id) use ($image_id) {
        return (int) $id !== (int) $image_id;
    }));

    cake_shop_set_gallery_ids($cake_id, $ids);

    wp_send_json_success([
        'message' => 'Đã xóa ảnh phụ thành công.',
        'ids' => $ids,
    ]);
}
add_action('wp_ajax_cake_shop_remove_gallery_image', 'cake_shop_ajax_remove_gallery_image');

/**
 * AJAX: lưu thứ tự ảnh phụ khi kéo thả
 */
function cake_shop_ajax_sort_gallery_images() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Chưa đăng nhập.'], 403);
    }

    $cake_id = isset($_POST['cake_id']) ? absint($_POST['cake_id']) : 0;
    $sorted_ids = isset($_POST['sorted_ids']) ? (array) $_POST['sorted_ids'] : [];
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!$cake_id) {
        wp_send_json_error(['message' => 'Thiếu cake_id.'], 400);
    }

    if (!wp_verify_nonce($nonce, 'cake_sort_gallery_' . $cake_id)) {
        wp_send_json_error(['message' => 'Nonce không hợp lệ.'], 403);
    }

    if (!current_user_can('edit_post', $cake_id)) {
        wp_send_json_error(['message' => 'Không có quyền.'], 403);
    }

    $sorted_ids = array_values(array_filter(array_map('absint', $sorted_ids)));
    cake_shop_set_gallery_ids($cake_id, $sorted_ids);

    wp_send_json_success([
        'message' => 'Đã lưu thứ tự ảnh phụ.',
        'ids' => $sorted_ids,
    ]);
}
add_action('wp_ajax_cake_shop_sort_gallery_images', 'cake_shop_ajax_sort_gallery_images');


/**
 * Xử lý upload/xóa ảnh hero trang chủ trước khi template render.
 * Việc redirect sau khi lưu giúp tránh gửi lại form khi refresh trang quản lý tiệm.
 */
function cake_shop_handle_store_hero_form() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cake_shop_store_hero_nonce'])) {
        return;
    }

    $redirect_url = wp_get_referer();
    if (!$redirect_url) {
        $redirect_url = home_url('/quan-ly-tiem');
    }

    $redirect_url = remove_query_arg(['store_hero_notice', 'store_hero_message'], $redirect_url);

    $redirect_with_notice = function ($notice, $message = '') use ($redirect_url) {
        $args = [
            'store_hero_notice' => sanitize_key($notice),
        ];

        if ($message !== '') {
            $args['store_hero_message'] = sanitize_text_field($message);
        }

        wp_safe_redirect(add_query_arg($args, $redirect_url));
        exit;
    };

    if (!is_user_logged_in()) {
        $redirect_with_notice('login_required');
    }

    if (!current_user_can('edit_posts')) {
        $redirect_with_notice('permission_denied');
    }

    $nonce = isset($_POST['cake_shop_store_hero_nonce']) ? sanitize_text_field(wp_unslash($_POST['cake_shop_store_hero_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'cake_shop_save_store_hero')) {
        $redirect_with_notice('invalid_nonce');
    }

    if (!empty($_POST['remove_home_hero_image'])) {
        delete_option('cake_shop_home_hero_image_id');
        $redirect_with_notice('removed');
    }

    if (empty($_FILES['home_hero_image']['name'])) {
        $redirect_with_notice('no_file');
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_handle_upload('home_hero_image', 0);

    if (is_wp_error($attachment_id)) {
        $redirect_with_notice('upload_error', $attachment_id->get_error_message());
    }

    update_option('cake_shop_home_hero_image_id', (int) $attachment_id);
    $redirect_with_notice('updated');
}
add_action('template_redirect', 'cake_shop_handle_store_hero_form');

function cake_shop_store_manager_shortcode() {
    if (!is_user_logged_in()) {
        return '<div class="manager-box"><h2>Quản lý tiệm</h2><p>Bạn cần đăng nhập để cập nhật thông tin tiệm.</p><a class="btn btn-primary" href="' . esc_url(home_url('/dang-nhap-tiem')) . '">Đăng nhập chủ tiệm</a></div>';
    }

    if (!current_user_can('edit_posts')) {
        return '<div class="manager-box"><h2>Quản lý tiệm</h2><p>Tài khoản của bạn chưa có quyền cập nhật thông tin tiệm.</p></div>';
    }

    $store_info = cake_shop_get_store_info();
    $notice = '';
    $notice_type = 'success';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cake_shop_store_action']) && $_POST['cake_shop_store_action'] === 'save_store_info') {
        if (!isset($_POST['cake_shop_store_nonce']) || !wp_verify_nonce($_POST['cake_shop_store_nonce'], 'cake_shop_save_store_info')) {
            $notice = 'Không thể lưu thông tin vì phiên làm việc không hợp lệ. Vui lòng thử lại.';
            $notice_type = 'error';
        } else {
            $new_values = [
                'shop_name'     => isset($_POST['shop_name']) ? wp_unslash($_POST['shop_name']) : '',
                'phone'         => isset($_POST['phone']) ? wp_unslash($_POST['phone']) : '',
                'address'       => isset($_POST['address']) ? wp_unslash($_POST['address']) : '',
                'zalo_link'     => isset($_POST['zalo_link']) ? wp_unslash($_POST['zalo_link']) : '',
                'facebook_link' => isset($_POST['facebook_link']) ? wp_unslash($_POST['facebook_link']) : '',
                'opening_hours' => isset($_POST['opening_hours']) ? wp_unslash($_POST['opening_hours']) : '',
                'delivery_areas' => isset($_POST['delivery_areas']) ? wp_unslash($_POST['delivery_areas']) : '',
                'delivery_note'  => isset($_POST['delivery_note']) ? wp_unslash($_POST['delivery_note']) : '',
            ];

            $store_info = cake_shop_sanitize_store_info($new_values);
            update_option('cake_shop_store_info', $store_info);
            $notice = 'Đã lưu thông tin tiệm thành công.';
            $notice_type = 'success';
        }
    }

    ob_start();
    ?>
    <div class="manager-wrap manager-wrap--store">
      <div class="manager-box manager-box--store">
        <h2>Quản lý tiệm</h2>
        <p>Cập nhật thông tin chung của tiệm để website tự hiển thị đồng bộ ở Trang chủ, Liên hệ, footer và các nút liên hệ nhanh.</p>

        <?php if ($notice) : ?>
          <div class="store-notice store-notice--<?php echo esc_attr($notice_type); ?>"><?php echo esc_html($notice); ?></div>
        <?php endif; ?>

        <form method="post" class="store-manager-form">
          <?php wp_nonce_field('cake_shop_save_store_info', 'cake_shop_store_nonce'); ?>
          <input type="hidden" name="cake_shop_store_action" value="save_store_info">

          <label for="shop_name">Tên tiệm</label>
          <input id="shop_name" type="text" name="shop_name" value="<?php echo esc_attr($store_info['shop_name']); ?>" required>

          <label for="shop_phone">Số điện thoại</label>
          <input id="shop_phone" type="text" name="phone" value="<?php echo esc_attr($store_info['phone']); ?>" required>

          <label for="shop_address">Địa chỉ</label>
          <textarea id="shop_address" name="address" rows="3" required><?php echo esc_textarea($store_info['address']); ?></textarea>

          <label for="shop_zalo_link">Link Zalo</label>
          <input id="shop_zalo_link" type="url" name="zalo_link" value="<?php echo esc_attr($store_info['zalo_link']); ?>" placeholder="https://zalo.me/..." required>

          <label for="shop_facebook_link">Link Facebook / Messenger</label>
          <input id="shop_facebook_link" type="url" name="facebook_link" value="<?php echo esc_attr($store_info['facebook_link']); ?>" placeholder="https://www.facebook.com/..." required>

          <label for="shop_opening_hours">Giờ mở cửa</label>
          <input id="shop_opening_hours" type="text" name="opening_hours" value="<?php echo esc_attr($store_info['opening_hours']); ?>" placeholder="Ví dụ: 8:00 - 20:00 mỗi ngày" required>

          <label for="shop_delivery_areas">Khu vực giao bánh</label>
          <input id="shop_delivery_areas" type="text" name="delivery_areas" value="<?php echo esc_attr($store_info['delivery_areas']); ?>" placeholder="Ví dụ: Phú Quốc và khu vực lân cận">

          <label for="shop_delivery_note">Lưu ý giao bánh</label>
          <textarea id="shop_delivery_note" name="delivery_note" rows="3" placeholder="Ví dụ: Liên hệ trước để xác nhận thời gian và phí giao hàng."><?php echo esc_textarea($store_info['delivery_note']); ?></textarea>

          <div class="manager-actions">
            <button type="submit" class="btn btn-primary">Lưu thông tin tiệm</button>
          </div>
        </form>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('cake_shop_store_manager', 'cake_shop_store_manager_shortcode');

function cake_shop_admin_dashboard_shortcode() {
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        return '';
    }

    $cake_ids = get_posts([
        'post_type'              => 'cake',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
    ]);

    $cake_counts = [
        'total'    => count($cake_ids),
        'available' => 0,
        'preorder' => 0,
        'soldout'  => 0,
        'needs_info' => 0,
    ];

    foreach ($cake_ids as $cake_id) {
        $status = get_post_meta($cake_id, '_trang_thai_banh', true);

        if ($status === 'co-san') {
            $cake_counts['available']++;
        } elseif ($status === 'nhan-dat-truoc') {
            $cake_counts['preorder']++;
        } elseif ($status === 'tam-het') {
            $cake_counts['soldout']++;
        }

        if (!has_post_thumbnail($cake_id) || get_post_meta($cake_id, '_gia_tham_khao', true) === '') {
            $cake_counts['needs_info']++;
        }
    }

    $feedback_count = count(get_posts([
        'post_type'              => 'cake_feedback',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]));

    ob_start();
    ?>
    <section class="manager-dashboard" aria-labelledby="manager-dashboard-title">
      <div class="manager-dashboard__heading">
        <div>
          <p class="manager-dashboard__eyebrow">Tổng quan tiệm</p>
          <h2 id="manager-dashboard-title">Bảng điều khiển</h2>
        </div>
        <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener noreferrer">Xem website</a>
      </div>

      <div class="manager-dashboard__metrics">
        <article class="manager-dashboard__metric">
          <span>Tổng số bánh</span>
          <strong><?php echo esc_html($cake_counts['total']); ?></strong>
          <a href="<?php echo esc_url(home_url('/quan-ly-banh')); ?>">Quản lý bánh</a>
        </article>
        <article class="manager-dashboard__metric">
          <span>Đang có sẵn</span>
          <strong><?php echo esc_html($cake_counts['available']); ?></strong>
          <a href="<?php echo esc_url(home_url('/menu-banh')); ?>" target="_blank" rel="noopener noreferrer">Xem menu</a>
        </article>
        <article class="manager-dashboard__metric">
          <span>Nhận đặt trước</span>
          <strong><?php echo esc_html($cake_counts['preorder']); ?></strong>
          <a href="<?php echo esc_url(home_url('/banh-kem')); ?>" target="_blank" rel="noopener noreferrer">Xem bánh kem</a>
        </article>
        <article class="manager-dashboard__metric">
          <span>Góp ý khách hàng</span>
          <strong><?php echo esc_html($feedback_count); ?></strong>
          <a href="<?php echo esc_url(cake_shop_feedback_admin_page_url()); ?>">Xem góp ý</a>
        </article>
      </div>

      <div class="manager-dashboard__footer">
        <p><?php echo $cake_counts['needs_info'] ? esc_html($cake_counts['needs_info'] . ' mẫu bánh cần bổ sung ảnh hoặc giá tham khảo.') : 'Danh sách bánh đã có đủ ảnh và giá tham khảo.'; ?></p>
        <div class="manager-dashboard__actions">
          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/quan-ly-banh')); ?>">Cập nhật bánh</a>
          <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/gop-y-khach-hang')); ?>">Góp ý khách hàng</a>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('cake_shop_admin_dashboard', 'cake_shop_admin_dashboard_shortcode');

/**
 * Frontend login shortcode
 */
function cake_shop_login_form_shortcode() {
    if (is_user_logged_in()) {
        return '<p>Bạn đã đăng nhập. <a href="' . esc_url(home_url('/quan-ly-banh')) . '">Đi tới trang quản lý bánh</a></p>';
    }

    ob_start();
    ?>
    <div class="manager-box">
      <h2>Đăng nhập chủ tiệm</h2>
      <p>Đăng nhập để quản lý bánh ngay trên website.</p>
      <?php
      wp_login_form([
          'redirect' => home_url('/quan-ly-banh'),
          'label_username' => 'Tên đăng nhập',
          'label_password' => 'Mật khẩu',
          'label_log_in'    => 'Đăng nhập',
          'remember'        => true,
          'label_remember'  => 'Ghi nhớ đăng nhập',
          'value_remember'  => true,
          // Luôn gửi rememberme để cookie đăng nhập được giữ sau khi đóng trình duyệt.
          'login_form_middle' => '<input type="hidden" name="rememberme" value="forever">',
      ]);
      ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('cake_shop_login_form', 'cake_shop_login_form_shortcode');

/**
 * Giữ đăng nhập lâu hơn khi chủ tiệm chọn/được tự động bật "Ghi nhớ đăng nhập".
 * Chỉ ảnh hưởng cookie đăng nhập, không thay đổi phần khác của website.
 */
function cake_shop_remember_login_duration($length, $user_id, $remember) {
    if ($remember) {
        return 30 * DAY_IN_SECONDS;
    }

    return $length;
}
add_filter('auth_cookie_expiration', 'cake_shop_remember_login_duration', 10, 3);


function cake_shop_render_manager_group($title, $cakes, $editing_id = 0) {
    ob_start();
    ?>
    <div class="manager-group">
      <h3><?php echo esc_html($title); ?></h3>

      <?php if (!empty($cakes)) : ?>
        <div class="manager-list">
          <?php foreach ($cakes as $cake) : ?>
            <?php
            $cake_price = get_post_meta($cake->ID, '_gia_tham_khao', true);
            $cake_status = get_post_meta($cake->ID, '_trang_thai_banh', true);
            $cake_status_label = cake_shop_get_trang_thai_label($cake_status);
            $cake_highlight = get_post_meta($cake->ID, '_cake_highlight', true);
            $cake_highlight_label = cake_shop_get_highlight_label($cake_highlight);
            $is_editing = ((int) $editing_id === (int) $cake->ID);

            $term_slugs = wp_get_post_terms($cake->ID, 'cake_category', ['fields' => 'slugs']);
            $cake_category = !empty($term_slugs) ? $term_slugs[0] : '';

            $next_status = cake_shop_get_next_status_label($cake_status, $cake_category);
            $secondary_status = cake_shop_get_secondary_status_label($cake_status, $cake_category);
            $hide_status = cake_shop_get_hide_status_label($cake_status, $cake_category);
            ?>
            <div
              class="manager-item<?php echo $is_editing ? ' is-editing' : ''; ?>"
              data-cake-title="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($cake->post_title, 'UTF-8') : strtolower($cake->post_title)); ?>"
              data-cake-category="<?php echo esc_attr($cake_category); ?>"
              data-cake-status="<?php echo esc_attr($cake_status); ?>"
            >
              <div>
                <strong><?php echo esc_html($cake->post_title); ?></strong>

                <div class="manager-meta">
                  <?php if ($cake_price) : ?>
                    <span><?php echo esc_html(cake_shop_format_price($cake_price)); ?></span>
                  <?php endif; ?>

                  <?php if ($cake_status_label) : ?>
                    <span><?php echo esc_html($cake_status_label); ?></span>
                  <?php endif; ?>

                  <?php if ($cake_highlight_label) : ?>
                    <span><?php echo esc_html($cake_highlight_label); ?></span>
                  <?php endif; ?>
                </div>

                <?php if ($next_status) : ?>
                  <div class="manager-quick-status-line">
                    <span class="manager-quick-status-label">Đổi trạng thái:</span>
                    <div class="manager-quick-status">
                      <a href="<?php echo esc_url(wp_nonce_url(add_query_arg([
                        'cake_action' => 'set_status',
                        'cake_id'     => $cake->ID,
                        'new_status'  => $next_status['key'],
                      ], home_url('/quan-ly-banh')), 'cake_set_status_' . $cake->ID . '_' . $next_status['key'])); ?>">
                        <?php echo esc_html($next_status['label']); ?>
                      </a>

                      <?php if ($secondary_status) : ?>
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg([
                          'cake_action' => 'set_status',
                          'cake_id'     => $cake->ID,
                          'new_status'  => $secondary_status['key'],
                        ], home_url('/quan-ly-banh')), 'cake_set_status_' . $cake->ID . '_' . $secondary_status['key'])); ?>">
                          <?php echo esc_html($secondary_status['label']); ?>
                        </a>
                      <?php endif; ?>

                      <?php if ($hide_status) : ?>
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg([
                          'cake_action' => 'set_status',
                          'cake_id'     => $cake->ID,
                          'new_status'  => $hide_status['key'],
                        ], home_url('/quan-ly-banh')), 'cake_set_status_' . $cake->ID . '_' . $hide_status['key'])); ?>">
                          <?php echo esc_html($hide_status['label']); ?>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>

              <div class="manager-item-actions">
                <a href="<?php echo esc_url(add_query_arg('edit_cake', $cake->ID, home_url('/quan-ly-banh'))); ?>">Sửa</a>
                <a
                  href="<?php echo esc_url(wp_nonce_url(add_query_arg([
                    'cake_action' => 'delete',
                    'cake_id' => $cake->ID,
                  ], home_url('/quan-ly-banh')), 'cake_delete_' . $cake->ID)); ?>"
                  onclick="return confirm('Bạn có chắc muốn xóa bánh này?');"
                >
                  Xóa
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <p class="manager-empty">Chưa có bánh nào trong mục này.</p>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function cake_shop_handle_gallery_uploads($field_name, $post_id) {
    if (empty($_FILES[$field_name]['name']) || !is_array($_FILES[$field_name]['name'])) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $existing_ids = cake_shop_get_gallery_ids($post_id);
    $new_ids = [];

    $files = $_FILES[$field_name];
    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        if (empty($files['name'][$i])) {
            continue;
        }

        $_FILES['single_gallery_file'] = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];

        $attachment_id = media_handle_upload('single_gallery_file', $post_id);

        if (!is_wp_error($attachment_id)) {
            $new_ids[] = $attachment_id;
        }
    }

    $final_ids = array_values(array_unique(array_merge($existing_ids, $new_ids)));
    cake_shop_set_gallery_ids($post_id, $final_ids);

    unset($_FILES['single_gallery_file']);
}

function cake_shop_manager_shortcode() {
    if (!is_user_logged_in()) {
        return '<div class="manager-box"><p>Bạn cần <a href="' . esc_url(home_url('/dang-nhap-tiem')) . '">đăng nhập</a> để quản lý bánh.</p></div>';
    }

    if (!current_user_can('edit_posts')) {
        return '<div class="manager-box"><p>Tài khoản này không có quyền quản lý bánh.</p></div>';
    }

    $notice = '';
    $notice_type = '';
    $edit_id = isset($_GET['edit_cake']) ? absint($_GET['edit_cake']) : 0;

    if (isset($_GET['manager_notice'])) {
        $notice_key = sanitize_text_field($_GET['manager_notice']);
        $notice_map = [
            'added'       => 'Đã thêm bánh mới.',
            'updated'     => 'Đã cập nhật bánh.',
            'deleted'     => 'Đã xóa bánh thành công.',
            'status_done' => 'Đã cập nhật trạng thái.',
        ];
        $notice = $notice_map[$notice_key] ?? '';
        $notice_type = isset($_GET['manager_notice_type']) ? sanitize_text_field($_GET['manager_notice_type']) : 'success';
    }

    if (
        isset($_GET['cake_action'], $_GET['cake_id'], $_GET['_wpnonce']) &&
        $_GET['cake_action'] === 'delete'
    ) {
        $cake_id = absint($_GET['cake_id']);

        if (wp_verify_nonce($_GET['_wpnonce'], 'cake_delete_' . $cake_id) && current_user_can('delete_post', $cake_id)) {
            wp_trash_post($cake_id);
            wp_safe_redirect(add_query_arg([
                'manager_notice' => 'deleted',
                'manager_notice_type' => 'success',
            ], home_url('/quan-ly-banh')));
            exit;
        }
    }

    if (
        isset($_GET['cake_action'], $_GET['cake_id'], $_GET['new_status'], $_GET['_wpnonce']) &&
        $_GET['cake_action'] === 'set_status'
    ) {
        $cake_id = absint($_GET['cake_id']);
        $new_status = sanitize_text_field($_GET['new_status']);

        $term_slugs = wp_get_post_terms($cake_id, 'cake_category', ['fields' => 'slugs']);
        $cake_category = !empty($term_slugs) ? $term_slugs[0] : '';
        $allowed_statuses = cake_shop_get_allowed_statuses_by_category($cake_category);

        if (
            isset($allowed_statuses[$new_status]) &&
            wp_verify_nonce($_GET['_wpnonce'], 'cake_set_status_' . $cake_id . '_' . $new_status) &&
            current_user_can('edit_post', $cake_id)
        ) {
            update_post_meta($cake_id, '_trang_thai_banh', $new_status);
            wp_safe_redirect(add_query_arg([
                'manager_notice' => 'status_done',
                'manager_notice_type' => 'success',
            ], home_url('/quan-ly-banh')));
            exit;
        }
    }

    $form_data = [
        'cake_id'        => $edit_id,
        'cake_title'     => '',
        'cake_excerpt'   => '',
        'cake_price'     => '',
        'cake_size'      => '',
        'cake_serving'   => '',
        'cake_preorder'  => '',
        'cake_status'    => '',
        'cake_category'  => '',
        'cake_highlight' => '',
    ];

    if (
        isset($_POST['cake_manager_nonce']) &&
        wp_verify_nonce($_POST['cake_manager_nonce'], 'cake_manager_save')
    ) {
        $cake_id   = isset($_POST['cake_id']) ? absint($_POST['cake_id']) : 0;
        $title     = isset($_POST['cake_title']) ? trim(sanitize_text_field($_POST['cake_title'])) : '';
        $excerpt   = isset($_POST['cake_excerpt']) ? sanitize_textarea_field($_POST['cake_excerpt']) : '';
        $price     = isset($_POST['cake_price']) ? sanitize_text_field($_POST['cake_price']) : '';
        $size      = isset($_POST['cake_size']) ? sanitize_text_field($_POST['cake_size']) : '';
        $serving   = isset($_POST['cake_serving']) ? sanitize_text_field($_POST['cake_serving']) : '';
        $preorder  = isset($_POST['cake_preorder']) ? sanitize_text_field($_POST['cake_preorder']) : '';
        $status    = isset($_POST['cake_status']) ? sanitize_text_field($_POST['cake_status']) : '';
        $category  = isset($_POST['cake_category']) ? sanitize_text_field($_POST['cake_category']) : '';
        $highlight = isset($_POST['cake_highlight']) ? sanitize_text_field($_POST['cake_highlight']) : '';

        $form_data = [
            'cake_id'        => $cake_id,
            'cake_title'     => $title,
            'cake_excerpt'   => $excerpt,
            'cake_price'     => $price,
            'cake_size'      => $size,
            'cake_serving'   => $serving,
            'cake_preorder'  => $preorder,
            'cake_status'    => $status,
            'cake_category'  => $category,
            'cake_highlight' => $highlight,
        ];

        $allowed_statuses = cake_shop_get_allowed_statuses_by_category($category);

        $highlight_limit_error = '';
        if (in_array($highlight, ['moi', 'hot'], true) && in_array($category, ['menu-banh', 'banh-kem'], true)) {
            $highlight_count = cake_shop_count_highlighted_cakes_by_category($category, $cake_id);

            if ($highlight_count >= 3) {
                $category_label = $category === 'menu-banh' ? 'Menu bánh' : 'Bánh kem';
                $action_label = $cake_id > 0 ? 'Sửa bánh không thành công. ' : 'Thêm bánh không thành công. ';
                $highlight_limit_error = $action_label . $category_label . ' chỉ được tối đa 3 bánh có nhãn Mới/Hot.';
            }
        }

        if (empty($title) || empty($price) || empty($status) || empty($category) || (empty($_FILES['cake_image_file']['name']) && $cake_id === 0)) {
            $action_label = $cake_id > 0 ? 'Sửa bánh không thành công. ' : 'Thêm bánh không thành công. ';
            $notice = $action_label . 'Vui lòng nhập đầy đủ Loại bánh, Tên bánh, Giá tham khảo, Trạng thái và Ảnh bánh.';
            $notice_type = 'error';
        } elseif (!isset($allowed_statuses[$status])) {
            $action_label = $cake_id > 0 ? 'Sửa bánh không thành công. ' : 'Thêm bánh không thành công. ';
            $notice = $action_label . 'Trạng thái không hợp lệ với loại bánh đã chọn.';
            $notice_type = 'error';
        } elseif (!empty($highlight_limit_error)) {
            $notice = $highlight_limit_error;
            $notice_type = 'error';
        } else {
            $duplicate = get_posts([
                'post_type'      => 'cake',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'exclude'        => $cake_id ? [$cake_id] : [],
            ]);

            $has_duplicate = false;
            foreach ($duplicate as $item) {
                $item_title = function_exists('mb_strtolower')
                    ? mb_strtolower(trim($item->post_title), 'UTF-8')
                    : strtolower(trim($item->post_title));
                $new_title = function_exists('mb_strtolower')
                    ? mb_strtolower($title, 'UTF-8')
                    : strtolower($title);

                if ($item_title === $new_title) {
                    $has_duplicate = true;
                    break;
                }
            }

            if ($has_duplicate) {
                $action_label = $cake_id > 0 ? 'Sửa bánh không thành công. ' : 'Thêm bánh không thành công. ';
                $notice = $action_label . 'Tên bánh đã tồn tại. Vui lòng nhập tên khác.';
                $notice_type = 'error';
            } else {
                $post_data = [
                    'post_type'    => 'cake',
                    'post_title'   => $title,
                    'post_excerpt' => $excerpt,
                    'post_status'  => 'publish',
                ];

                $notice_key = 'added';

                if ($cake_id > 0) {
                    $post_data['ID'] = $cake_id;
                    $cake_id = wp_update_post($post_data);
                    $notice_key = 'updated';
                } else {
                    $cake_id = wp_insert_post($post_data);
                    $notice_key = 'added';
                }

                if ($cake_id && !is_wp_error($cake_id)) {
                    update_post_meta($cake_id, '_gia_tham_khao', $price);
                    update_post_meta($cake_id, '_cake_size', $size);
                    update_post_meta($cake_id, '_cake_serving', $serving);
                    update_post_meta($cake_id, '_cake_preorder_time', $preorder);
                    update_post_meta($cake_id, '_trang_thai_banh', $status);

                    $highlight_options = cake_shop_get_highlight_options();
                    if (!array_key_exists($highlight, $highlight_options)) {
                        $highlight = '';
                    }
                    update_post_meta($cake_id, '_cake_highlight', $highlight);

                    wp_set_object_terms($cake_id, [$category], 'cake_category', false);

                    if (!empty($_FILES['cake_image_file']['name'])) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        require_once ABSPATH . 'wp-admin/includes/media.php';
                        require_once ABSPATH . 'wp-admin/includes/image.php';

                        $attachment_id = media_handle_upload('cake_image_file', $cake_id);

                        if (!is_wp_error($attachment_id)) {
                            set_post_thumbnail($cake_id, $attachment_id);
                        }
                    }

                    cake_shop_handle_gallery_uploads('cake_gallery_files', $cake_id);

                    wp_safe_redirect(add_query_arg([
                        'manager_notice' => $notice_key,
                        'manager_notice_type' => 'success',
                    ], home_url('/quan-ly-banh')));
                    exit;
                } else {
                    $action_label = $cake_id > 0 ? 'Sửa bánh không thành công. ' : 'Thêm bánh không thành công. ';
                    $notice = $action_label . 'Có lỗi xảy ra khi lưu bánh.';
                    $notice_type = 'error';
                }
            }
        }
    }

    $edit_post = $edit_id ? get_post($edit_id) : null;

    if ($edit_post && empty($form_data['cake_title']) && empty($form_data['cake_price']) && empty($form_data['cake_status']) && empty($form_data['cake_category'])) {
        $edit_terms = wp_get_post_terms($edit_id, 'cake_category', ['fields' => 'slugs']);
        $edit_category = !empty($edit_terms) ? $edit_terms[0] : '';

        $form_data['cake_id']        = $edit_id;
        $form_data['cake_title']     = $edit_post->post_title;
        $form_data['cake_excerpt']   = $edit_post->post_excerpt;
        $form_data['cake_price']     = get_post_meta($edit_id, '_gia_tham_khao', true);
        $form_data['cake_size']      = get_post_meta($edit_id, '_cake_size', true);
        $form_data['cake_serving']   = get_post_meta($edit_id, '_cake_serving', true);
        $form_data['cake_preorder']  = get_post_meta($edit_id, '_cake_preorder_time', true);
        $form_data['cake_status']    = get_post_meta($edit_id, '_trang_thai_banh', true);
        $form_data['cake_category']  = $edit_category;
        $form_data['cake_highlight'] = get_post_meta($edit_id, '_cake_highlight', true);
    }

    $current_form_category = $form_data['cake_category'];
    $edit_allowed_statuses = cake_shop_get_allowed_statuses_by_category($current_form_category);
    $edit_gallery_ids = $edit_id ? cake_shop_get_gallery_ids($edit_id) : [];
    $highlight_options = cake_shop_get_highlight_options();

    $all_cakes = get_posts([
        'post_type'      => 'cake',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $menu_banh_cakes = [];
    $banh_kem_cakes = [];

    foreach ($all_cakes as $cake) {
        $term_slugs = wp_get_post_terms($cake->ID, 'cake_category', ['fields' => 'slugs']);

        if (in_array('menu-banh', $term_slugs, true)) {
            $menu_banh_cakes[] = $cake;
        }

        if (in_array('banh-kem', $term_slugs, true)) {
            $banh_kem_cakes[] = $cake;
        }
    }

    ob_start();
    ?>
    <div class="manager-wrap">
      <?php if ($notice) : ?>
        <div class="manager-modal-notice is-open">
          <div class="manager-modal-notice__overlay" data-close-manager-modal></div>
          <div class="manager-modal-notice__dialog">
            <button type="button" class="manager-modal-notice__close" data-close-manager-modal>&times;</button>
            <div class="manager-modal-notice__content manager-modal-notice__content--<?php echo esc_attr($notice_type === 'error' ? 'error' : 'success'); ?>">
              <?php echo esc_html($notice); ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="manager-grid">
        <div class="manager-box<?php echo $form_data['cake_id'] ? ' is-edit-mode' : ''; ?>">
          <h3><?php echo $form_data['cake_id'] ? 'Sửa bánh' : 'Thêm bánh mới'; ?></h3>

          <form method="post" enctype="multipart/form-data" id="cake-manager-form">
            <?php wp_nonce_field('cake_manager_save', 'cake_manager_nonce'); ?>
            <input type="hidden" name="cake_id" value="<?php echo esc_attr($form_data['cake_id']); ?>">

            <label>Loại bánh</label>
            <select name="cake_category" id="cake_category" required>
              <option value="">-- Chọn loại bánh --</option>
              <option value="menu-banh" <?php selected($form_data['cake_category'], 'menu-banh'); ?>>Menu bánh</option>
              <option value="banh-kem" <?php selected($form_data['cake_category'], 'banh-kem'); ?>>Bánh kem</option>
            </select>

            <label>Tên bánh</label>
            <input type="text" name="cake_title" value="<?php echo esc_attr($form_data['cake_title']); ?>" required>

            <label>Giá tham khảo</label>
            <input type="text" name="cake_price" value="<?php echo esc_attr($form_data['cake_price']); ?>" placeholder="Ví dụ: 99000" required>

            <div class="manager-detail-fields">
              <div>
                <label for="cake_size">Kích thước <span>(tùy chọn)</span></label>
                <input type="text" id="cake_size" name="cake_size" value="<?php echo esc_attr($form_data['cake_size']); ?>" placeholder="Ví dụ: 16 cm">
              </div>
              <div>
                <label for="cake_serving">Khẩu phần <span>(tùy chọn)</span></label>
                <input type="text" id="cake_serving" name="cake_serving" value="<?php echo esc_attr($form_data['cake_serving']); ?>" placeholder="Ví dụ: 4-6 người">
              </div>
            </div>

            <label for="cake_preorder">Thời gian cần đặt trước <span>(tùy chọn)</span></label>
            <input type="text" id="cake_preorder" name="cake_preorder" value="<?php echo esc_attr($form_data['cake_preorder']); ?>" placeholder="Ví dụ: Đặt trước 1 ngày">

            <label>Trạng thái</label>
            <select name="cake_status" id="cake_status" required>
              <option value="">-- Chọn trạng thái --</option>
              <?php foreach ($edit_allowed_statuses as $status_key => $status_label) : ?>
                <option value="<?php echo esc_attr($status_key); ?>" <?php selected($form_data['cake_status'], $status_key); ?>>
                  <?php echo esc_html($status_label); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label>Nhãn nổi bật</label>
            <select name="cake_highlight">
              <?php foreach ($highlight_options as $highlight_key => $highlight_label) : ?>
                <option value="<?php echo esc_attr($highlight_key); ?>" <?php selected($form_data['cake_highlight'], $highlight_key); ?>>
                  <?php echo esc_html($highlight_label); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label>Mô tả ngắn</label>
            <textarea name="cake_excerpt" rows="3"><?php echo esc_textarea($form_data['cake_excerpt']); ?></textarea>

            <label>Ảnh bánh chính</label>
            <input type="file" name="cake_image_file" id="cake_image_file" accept="image/*" <?php echo $form_data['cake_id'] ? '' : 'required'; ?>>

            <div class="manager-image-preview-wrap">
              <?php if ($form_data['cake_id'] && has_post_thumbnail($form_data['cake_id'])) : ?>
                <img id="cake-image-preview" src="<?php echo esc_url(get_the_post_thumbnail_url($form_data['cake_id'], 'medium')); ?>" alt="Preview ảnh bánh">
              <?php else : ?>
                <img id="cake-image-preview" src="" alt="Preview ảnh bánh" style="display:none;">
              <?php endif; ?>
            </div>

            <label>Ảnh phụ (có thể chọn nhiều ảnh)</label>
            <input type="file" name="cake_gallery_files[]" id="cake_gallery_files" accept="image/*" multiple>

            <div class="manager-gallery-preview manager-gallery-preview--new" id="manager-gallery-preview-new"></div>

            <?php if (!empty($edit_gallery_ids)) : ?>
              <div
                class="manager-gallery-preview manager-gallery-preview--existing"
                id="manager-gallery-existing"
                data-cake-id="<?php echo esc_attr($form_data['cake_id']); ?>"
                data-sort-nonce="<?php echo esc_attr(wp_create_nonce('cake_sort_gallery_' . $form_data['cake_id'])); ?>"
              >
                <?php foreach ($edit_gallery_ids as $gallery_id) : ?>
                  <?php
                  $gallery_thumb = wp_get_attachment_image_url($gallery_id, 'thumbnail');
                  if (!$gallery_thumb) continue;
                  ?>
                  <div
                    class="manager-gallery-item"
                    data-gallery-id="<?php echo esc_attr($gallery_id); ?>"
                    draggable="true"
                  >
                    <img src="<?php echo esc_url($gallery_thumb); ?>" alt="">
                    <button
                      type="button"
                      class="manager-gallery-remove manager-gallery-remove--ajax"
                      data-cake-id="<?php echo esc_attr($form_data['cake_id']); ?>"
                      data-image-id="<?php echo esc_attr($gallery_id); ?>"
                      data-remove-nonce="<?php echo esc_attr(wp_create_nonce('cake_remove_gallery_' . $form_data['cake_id'] . '_' . $gallery_id)); ?>"
                      title="Xóa ảnh phụ"
                    >
                      ×
                    </button>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="manager-actions">
              <button type="submit" class="btn btn-primary">
                <?php echo $form_data['cake_id'] ? 'Lưu thay đổi' : 'Thêm bánh'; ?>
              </button>

              <?php if ($form_data['cake_id']) : ?>
                <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/quan-ly-banh')); ?>">Hủy sửa</a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <div class="manager-box manager-box--list">
          <div class="manager-list-header manager-list-header--filters">
            <input type="text" id="cake-live-search" placeholder="Tìm theo tên bánh...">

            <div class="manager-list-header--filters-row">
              <select id="cake-filter-category">
                <option value="">Tất cả loại bánh</option>
                <option value="menu-banh">Menu bánh</option>
                <option value="banh-kem">Bánh kem</option>
              </select>

              <select id="cake-filter-status">
                <option value="">Tất cả trạng thái</option>
                <option value="co-san">Có sẵn</option>
                <option value="tam-het">Tạm hết</option>
                <option value="nhan-dat-truoc">Nhận đặt trước</option>
                <option value="an">Ẩn</option>
              </select>
            </div>
          </div>

          <div class="manager-scroll-area">
            <?php
            echo cake_shop_render_manager_group('Menu bánh', $menu_banh_cakes, $form_data['cake_id']);
            echo cake_shop_render_manager_group('Bánh kem', $banh_kem_cakes, $form_data['cake_id']);
            ?>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

        function showManagerModal(message, type) {
          const oldModal = document.querySelector('.manager-modal-notice');
          if (oldModal) oldModal.remove();

          const modal = document.createElement('div');
          modal.className = 'manager-modal-notice is-open';
          modal.innerHTML = `
            <div class="manager-modal-notice__overlay" data-close-manager-modal></div>
            <div class="manager-modal-notice__dialog">
              <button type="button" class="manager-modal-notice__close" data-close-manager-modal>&times;</button>
              <div class="manager-modal-notice__content manager-modal-notice__content--${type === 'error' ? 'error' : 'success'}">${message}</div>
            </div>
          `;

          document.body.appendChild(modal);

          modal.querySelectorAll('[data-close-manager-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              modal.remove();
            });
          });
        }

        const closeButtons = document.querySelectorAll('[data-close-manager-modal]');
        closeButtons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            const modal = document.querySelector('.manager-modal-notice');
            if (modal) modal.remove();
          });
        });

        const searchInput = document.getElementById('cake-live-search');
        const categoryFilter = document.getElementById('cake-filter-category');
        const statusFilter = document.getElementById('cake-filter-status');
        const items = document.querySelectorAll('.manager-item');
        const groups = document.querySelectorAll('.manager-group');

        const statusOptionsByCategory = {
          '': [
            { value: '', text: 'Tất cả trạng thái' },
            { value: 'co-san', text: 'Có sẵn' },
            { value: 'tam-het', text: 'Tạm hết' },
            { value: 'nhan-dat-truoc', text: 'Nhận đặt trước' },
            { value: 'an', text: 'Ẩn' }
          ],
          'menu-banh': [
            { value: '', text: 'Tất cả trạng thái' },
            { value: 'co-san', text: 'Có sẵn' },
            { value: 'tam-het', text: 'Tạm hết' },
            { value: 'an', text: 'Ẩn' }
          ],
          'banh-kem': [
            { value: '', text: 'Tất cả trạng thái' },
            { value: 'co-san', text: 'Có sẵn' },
            { value: 'nhan-dat-truoc', text: 'Nhận đặt trước' },
            { value: 'an', text: 'Ẩn' }
          ]
        };

        function updateManagerStatusFilterOptions() {
          if (!categoryFilter || !statusFilter) return;

          const selectedCategory = categoryFilter.value;
          const currentStatus = statusFilter.value;
          const options = statusOptionsByCategory[selectedCategory] || statusOptionsByCategory[''];

          statusFilter.innerHTML = '';

          options.forEach(function (optionData) {
            const option = document.createElement('option');
            option.value = optionData.value;
            option.textContent = optionData.text;
            statusFilter.appendChild(option);
          });

          const stillExists = options.some(function (optionData) {
            return optionData.value === currentStatus;
          });

          statusFilter.value = stillExists ? currentStatus : '';
        }

        function applyManagerFilters() {
          const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
          const categoryValue = categoryFilter ? categoryFilter.value : '';
          const statusValue = statusFilter ? statusFilter.value : '';

          items.forEach(function (item) {
            const title = item.getAttribute('data-cake-title') || '';
            const category = item.getAttribute('data-cake-category') || '';
            const status = item.getAttribute('data-cake-status') || '';

            const matchKeyword = title.includes(keyword);
            const matchCategory = !categoryValue || category === categoryValue;
            const matchStatus = !statusValue || status === statusValue;

            item.style.display = (matchKeyword && matchCategory && matchStatus) ? '' : 'none';
          });

          groups.forEach(function (group) {
            const groupTitleEl = group.querySelector('h3');
            const groupTitle = groupTitleEl ? groupTitleEl.textContent.trim() : '';
            const groupCategory = groupTitle === 'Menu bánh' ? 'menu-banh' : 'banh-kem';

            const allItems = group.querySelectorAll('.manager-item');
            let visibleCount = 0;

            allItems.forEach(function (item) {
              if (item.style.display !== 'none') {
                visibleCount++;
              }
            });

            const emptyText = group.querySelector('.manager-empty-temp');

            if (visibleCount === 0) {
              if (categoryValue && categoryValue !== groupCategory) {
                group.style.display = 'none';
                if (emptyText) emptyText.remove();
              } else if (keyword || categoryValue || statusValue) {
                group.style.display = '';
                if (!emptyText) {
                  const p = document.createElement('p');
                  p.className = 'manager-empty manager-empty-temp';
                  p.textContent = 'Không có bánh phù hợp.';
                  group.appendChild(p);
                }
              } else {
                group.style.display = '';
                if (emptyText) emptyText.remove();
              }
            } else {
              group.style.display = '';
              if (emptyText) emptyText.remove();
            }
          });
        }

        if (searchInput) {
          searchInput.addEventListener('input', applyManagerFilters);
        }

        if (categoryFilter) {
          categoryFilter.addEventListener('change', function () {
            updateManagerStatusFilterOptions();
            applyManagerFilters();
          });
        }

        if (statusFilter) {
          statusFilter.addEventListener('change', applyManagerFilters);
        }

        const fileInput = document.getElementById('cake_image_file');
        const previewImg = document.getElementById('cake-image-preview');

        if (fileInput && previewImg) {
          fileInput.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (event) {
              previewImg.src = event.target.result;
              previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
          });
        }

        const galleryInput = document.getElementById('cake_gallery_files');
        const galleryPreviewNew = document.getElementById('manager-gallery-preview-new');

        if (galleryInput && galleryPreviewNew) {
          galleryInput.addEventListener('change', function (e) {
            galleryPreviewNew.innerHTML = '';
            const files = Array.from(e.target.files || []);

            files.forEach(function(file) {
              if (!file.type.startsWith('image/')) return;

              const reader = new FileReader();
              reader.onload = function(event) {
                const box = document.createElement('div');
                box.className = 'manager-gallery-item manager-gallery-item--new';
                box.innerHTML = '<img src="' + event.target.result + '" alt="">';
                galleryPreviewNew.appendChild(box);
              };
              reader.readAsDataURL(file);
            });
          });
        }

        const categorySelect = document.getElementById('cake_category');
        const statusSelect = document.getElementById('cake_status');

        function updateStatusOptionsByCategory() {
          if (!categorySelect || !statusSelect) return;

          const category = categorySelect.value;
          const currentValue = statusSelect.value;

          let options = '<option value="">-- Chọn trạng thái --</option>';

          if (category === 'menu-banh') {
            options += '<option value="co-san">Có sẵn</option>';
            options += '<option value="tam-het">Tạm hết</option>';
            options += '<option value="an">Ẩn</option>';
          } else if (category === 'banh-kem') {
            options += '<option value="co-san">Có sẵn</option>';
            options += '<option value="nhan-dat-truoc">Nhận đặt trước</option>';
            options += '<option value="an">Ẩn</option>';
          }

          statusSelect.innerHTML = options;

          const optionExists = Array.from(statusSelect.options).some(function(option) {
            return option.value === currentValue;
          });

          statusSelect.value = optionExists ? currentValue : '';
        }

        if (categorySelect && statusSelect) {
          categorySelect.addEventListener('change', updateStatusOptionsByCategory);
          updateStatusOptionsByCategory();
        }

        const galleryExisting = document.getElementById('manager-gallery-existing');

        if (galleryExisting) {
          let draggedItem = null;

          function getSortedGalleryIds() {
            return Array.from(galleryExisting.querySelectorAll('.manager-gallery-item'))
              .map(function(item) {
                return item.getAttribute('data-gallery-id');
              })
              .filter(Boolean);
          }

          function saveGalleryOrder() {
            const cakeId = galleryExisting.getAttribute('data-cake-id');
            const nonce = galleryExisting.getAttribute('data-sort-nonce');
            const sortedIds = getSortedGalleryIds();

            const formData = new FormData();
            formData.append('action', 'cake_shop_sort_gallery_images');
            formData.append('cake_id', cakeId);
            formData.append('nonce', nonce);
            sortedIds.forEach(function(id) {
              formData.append('sorted_ids[]', id);
            });

            fetch(ajaxUrl, {
              method: 'POST',
              body: formData,
              credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
              if (!data.success) {
                showManagerModal(data.data && data.data.message ? data.data.message : 'Không lưu được thứ tự ảnh.', 'error');
              }
            })
            .catch(function() {
              showManagerModal('Có lỗi khi lưu thứ tự ảnh phụ.', 'error');
            });
          }

          galleryExisting.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.manager-gallery-remove--ajax');
            if (!removeBtn) return;

            e.preventDefault();

            const ok = confirm('Bạn có chắc muốn xóa ảnh phụ này?');
            if (!ok) return;

            const item = removeBtn.closest('.manager-gallery-item');
            const cakeId = removeBtn.getAttribute('data-cake-id');
            const imageId = removeBtn.getAttribute('data-image-id');
            const nonce = removeBtn.getAttribute('data-remove-nonce');

            const formData = new FormData();
            formData.append('action', 'cake_shop_remove_gallery_image');
            formData.append('cake_id', cakeId);
            formData.append('image_id', imageId);
            formData.append('nonce', nonce);

            fetch(ajaxUrl, {
              method: 'POST',
              body: formData,
              credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
              if (data.success) {
                if (item) item.remove();
                showManagerModal(data.data && data.data.message ? data.data.message : 'Đã xóa ảnh phụ thành công.', 'success');
              } else {
                showManagerModal(data.data && data.data.message ? data.data.message : 'Không xóa được ảnh.', 'error');
              }
            })
            .catch(function() {
              showManagerModal('Có lỗi khi xóa ảnh phụ.', 'error');
            });
          });

          function moveGalleryItem(targetItem, clientX, clientY) {
            if (!draggedItem || !targetItem || draggedItem === targetItem) return;

            const rect = targetItem.getBoundingClientRect();
            const horizontalOffset = clientX - rect.left;
            const verticalOffset = clientY - rect.top;
            const insertAfter = horizontalOffset > rect.width / 2 || verticalOffset > rect.height / 2;

            if (insertAfter) {
              targetItem.parentNode.insertBefore(draggedItem, targetItem.nextSibling);
            } else {
              targetItem.parentNode.insertBefore(draggedItem, targetItem);
            }
          }

          galleryExisting.querySelectorAll('.manager-gallery-item').forEach(function(item) {
            let touchSortingStarted = false;

            item.addEventListener('dragstart', function() {
              draggedItem = item;
              item.classList.add('is-dragging');
            });

            item.addEventListener('dragend', function() {
              item.classList.remove('is-dragging');
              draggedItem = null;
              saveGalleryOrder();
            });

            item.addEventListener('dragover', function(e) {
              e.preventDefault();
            });

            item.addEventListener('drop', function(e) {
              e.preventDefault();
              moveGalleryItem(item, e.clientX, e.clientY);
            });

            item.addEventListener('pointerdown', function(e) {
              if (e.pointerType === 'mouse') return;
              if (e.target.closest('.manager-gallery-remove--ajax')) return;

              draggedItem = item;
              touchSortingStarted = true;
              item.classList.add('is-dragging');
              item.setPointerCapture(e.pointerId);
              e.preventDefault();
            });

            item.addEventListener('pointermove', function(e) {
              if (!touchSortingStarted || !draggedItem) return;

              const elementBelow = document.elementFromPoint(e.clientX, e.clientY);
              const targetItem = elementBelow ? elementBelow.closest('.manager-gallery-item') : null;

              if (targetItem && galleryExisting.contains(targetItem)) {
                moveGalleryItem(targetItem, e.clientX, e.clientY);
              }

              e.preventDefault();
            });

            function finishTouchSorting(e) {
              if (!touchSortingStarted) return;

              touchSortingStarted = false;
              item.classList.remove('is-dragging');

              try {
                item.releasePointerCapture(e.pointerId);
              } catch (error) {}

              draggedItem = null;
              saveGalleryOrder();
            }

            item.addEventListener('pointerup', finishTouchSorting);
            item.addEventListener('pointercancel', finishTouchSorting);
          });
        }

        const form = document.getElementById('cake-manager-form');
        if (form) {
          let isDirty = false;

          form.addEventListener('input', function () {
            isDirty = true;
          });

          form.addEventListener('change', function () {
            isDirty = true;
          });

          form.addEventListener('submit', function () {
            isDirty = false;
          });

          window.addEventListener('beforeunload', function (e) {
            if (!isDirty) return;
            e.preventDefault();
            e.returnValue = '';
          });

          document.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function (e) {
              const href = link.getAttribute('href') || '';
              if (!isDirty) return;
              if (href.startsWith('#') || href.startsWith('javascript:')) return;

              const ok = confirm('Bạn có thay đổi chưa lưu. Bạn có chắc muốn rời khỏi trang?');
              if (!ok) {
                e.preventDefault();
              }
            });
          });
        }

        const editingItem = document.querySelector('.manager-item.is-editing');
        if (editingItem) {
          editingItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        updateManagerStatusFilterOptions();
        applyManagerFilters();
      });
    </script>
    <?php
    return ob_get_clean();
}


/**
 * Góp ý khách hàng ẩn danh
 */
function cake_shop_register_feedback_post_type() {
    $labels = [
        'name'          => 'Góp ý khách hàng',
        'singular_name' => 'Góp ý khách hàng',
        'menu_name'     => 'Góp ý khách hàng',
        'search_items'  => 'Tìm góp ý',
        'not_found'     => 'Chưa có góp ý nào',
    ];

    register_post_type('cake_feedback', [
        'labels'              => $labels,
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'exclude_from_search' => true,
        'supports'            => ['title', 'editor'],
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
    ]);
}
add_action('init', 'cake_shop_register_feedback_post_type');

function cake_shop_feedback_page_url() {
    return home_url('/gop-y');
}

function cake_shop_feedback_admin_page_url() {
    return home_url('/gop-y-khach-hang');
}


function cake_shop_get_feedback_rate_limit_seconds() {
    return 5 * MINUTE_IN_SECONDS;
}

function cake_shop_get_feedback_client_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

    foreach ($keys as $key) {
        if (empty($_SERVER[$key])) {
            continue;
        }

        $raw_value = wp_unslash($_SERVER[$key]);
        $parts = array_map('trim', explode(',', $raw_value));
        $ip = sanitize_text_field($parts[0]);

        if ($ip !== '') {
            return $ip;
        }
    }

    return 'unknown';
}

function cake_shop_get_feedback_rate_limit_cookie_name() {
    return 'cake_shop_feedback_last_sent';
}

function cake_shop_feedback_get_remaining_seconds() {
    $limit_seconds = cake_shop_get_feedback_rate_limit_seconds();
    $now = time();

    $cookie_name = cake_shop_get_feedback_rate_limit_cookie_name();
    $cookie_last_sent = isset($_COOKIE[$cookie_name]) ? absint($_COOKIE[$cookie_name]) : 0;

    $ip = cake_shop_get_feedback_client_ip();
    $ip_last_sent = absint(get_transient('cake_shop_feedback_ip_' . md5($ip)));

    $last_sent = max($cookie_last_sent, $ip_last_sent);

    if (!$last_sent) {
        return 0;
    }

    $elapsed = $now - $last_sent;
    if ($elapsed >= $limit_seconds) {
        return 0;
    }

    return max(0, $limit_seconds - $elapsed);
}

function cake_shop_feedback_format_wait_time($seconds) {
    $seconds = max(0, absint($seconds));

    if ($seconds < MINUTE_IN_SECONDS) {
        return $seconds . ' giây';
    }

    $minutes = (int) ceil($seconds / MINUTE_IN_SECONDS);
    return $minutes . ' phút';
}


function cake_shop_handle_feedback_submission() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $action = isset($_POST['cake_shop_feedback_action']) ? sanitize_text_field(wp_unslash($_POST['cake_shop_feedback_action'])) : '';
    if ($action !== 'submit_feedback') {
        return;
    }

    $redirect_url = !empty($_POST['_wp_http_referer']) ? wp_unslash($_POST['_wp_http_referer']) : cake_shop_feedback_page_url();

    if (
        empty($_POST['cake_shop_feedback_nonce']) ||
        !wp_verify_nonce($_POST['cake_shop_feedback_nonce'], 'cake_shop_submit_feedback')
    ) {
        wp_safe_redirect(add_query_arg('feedback_status', 'invalid_nonce', $redirect_url));
        exit;
    }

    $remaining_seconds = cake_shop_feedback_get_remaining_seconds();
    if ($remaining_seconds > 0) {
        wp_safe_redirect(add_query_arg([
            'feedback_status' => 'rate_limited',
            'feedback_wait'   => $remaining_seconds,
        ], $redirect_url));
        exit;
    }

    $subject = isset($_POST['feedback_subject']) ? sanitize_text_field(wp_unslash($_POST['feedback_subject'])) : '';
    $message = isset($_POST['feedback_message']) ? sanitize_textarea_field(wp_unslash($_POST['feedback_message'])) : '';

    if ($message === '') {
        wp_safe_redirect(add_query_arg('feedback_status', 'empty_message', $redirect_url));
        exit;
    }

    $title = $subject !== '' ? $subject : 'Góp ý ngày ' . wp_date('d/m/Y H:i', current_time('timestamp'), wp_timezone());

    $feedback_id = wp_insert_post([
        'post_type'    => 'cake_feedback',
        'post_status'  => 'publish',
        'post_title'   => $title,
        'post_content' => $message,
    ], true);

    if (is_wp_error($feedback_id)) {
        wp_safe_redirect(add_query_arg('feedback_status', 'error', $redirect_url));
        exit;
    }

    update_post_meta($feedback_id, '_cake_feedback_anonymous', '1');

    $now = time();
    $limit_seconds = cake_shop_get_feedback_rate_limit_seconds();
    $cookie_name = cake_shop_get_feedback_rate_limit_cookie_name();

    setcookie(
        $cookie_name,
        (string) $now,
        $now + $limit_seconds,
        COOKIEPATH ? COOKIEPATH : '/',
        COOKIE_DOMAIN,
        is_ssl(),
        true
    );

    if (defined('SITECOOKIEPATH') && SITECOOKIEPATH && SITECOOKIEPATH !== COOKIEPATH) {
        setcookie(
            $cookie_name,
            (string) $now,
            $now + $limit_seconds,
            SITECOOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }

    $ip = cake_shop_get_feedback_client_ip();
    set_transient('cake_shop_feedback_ip_' . md5($ip), $now, $limit_seconds);

    wp_safe_redirect(add_query_arg('feedback_status', 'success', $redirect_url));
    exit;
}
add_action('template_redirect', 'cake_shop_handle_feedback_submission');

function cake_shop_get_feedback_notice() {
    $status = isset($_GET['feedback_status']) ? sanitize_key(wp_unslash($_GET['feedback_status'])) : '';

    $messages = [
        'success'       => ['type' => 'success', 'message' => 'Cảm ơn bạn đã gửi góp ý. Tiệm đã nhận được rồi nhé.'],
        'empty_message' => ['type' => 'error',   'message' => 'Bạn hãy nhập nội dung góp ý trước khi gửi nhé.'],
        'invalid_nonce' => ['type' => 'error',   'message' => 'Không thể gửi góp ý. Bạn vui lòng thử lại giúp tiệm nhé.'],
        'error'         => ['type' => 'error',   'message' => 'Có lỗi xảy ra khi gửi góp ý. Bạn thử lại sau một chút nhé.'],
    ];

    if ($status === 'rate_limited') {
        $wait_seconds = isset($_GET['feedback_wait']) ? absint(wp_unslash($_GET['feedback_wait'])) : cake_shop_get_feedback_rate_limit_seconds();
        return [
            'type'    => 'error',
            'message' => 'Bạn vừa gửi góp ý gần đây. Vui lòng thử lại sau khoảng ' . cake_shop_feedback_format_wait_time($wait_seconds) . ' nhé.',
        ];
    }

    return isset($messages[$status]) ? $messages[$status] : null;
}

function cake_shop_feedback_form_shortcode() {
    $notice = cake_shop_get_feedback_notice();

    ob_start();
    ?>
    <div class="manager-box feedback-box">
      <h2>Góp ý với tiệm</h2>
      <p class="feedback-intro">
        Nếu bạn muốn góp ý về bánh, cách phục vụ hay trải nghiệm khi ghé tiệm, bạn có thể nhắn lại tại đây. Tiệm luôn trân trọng mọi góp ý chân thành để ngày càng tốt hơn.
      </p>

      <?php if ($notice) : ?>
        <div class="feedback-notice feedback-notice--<?php echo esc_attr($notice['type']); ?>">
          <?php echo esc_html($notice['message']); ?>
        </div>
      <?php endif; ?>

      <form class="store-manager-form feedback-form" method="post">
        <?php wp_nonce_field('cake_shop_submit_feedback', 'cake_shop_feedback_nonce'); ?>
        <input type="hidden" name="cake_shop_feedback_action" value="submit_feedback">

        <label for="feedback_subject">Tiêu đề góp ý</label>
        <input
          id="feedback_subject"
          type="text"
          name="feedback_subject"
          maxlength="120"
          placeholder="Ví dụ: Tiệm tư vấn rất dễ thương"
        >

        <label for="feedback_message">Nội dung góp ý</label>
        <textarea
          id="feedback_message"
          name="feedback_message"
          rows="6"
          placeholder="Bạn có thể góp ý về bánh, cách phục vụ, thời gian phản hồi hoặc bất kỳ điều gì bạn muốn tiệm cải thiện."
          required
        ></textarea>

        <p class="feedback-note">Góp ý này sẽ được gửi ẩn danh, khách hàng khác và chủ tiệm sẽ không biết bạn là ai.</p>

        <div class="manager-actions">
          <button type="submit" class="btn btn-primary">Gửi góp ý</button>
        </div>
      </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('cake_shop_feedback_form', 'cake_shop_feedback_form_shortcode');

function cake_shop_feedback_admin_shortcode() {
    if (!is_user_logged_in()) {
        return '<div class="manager-box"><p>Bạn cần <a href="' . esc_url(home_url('/dang-nhap-tiem')) . '">đăng nhập</a> để xem góp ý của khách hàng.</p></div>';
    }

    if (!current_user_can('edit_posts')) {
        return '<div class="manager-box"><p>Tài khoản này không có quyền xem góp ý của khách hàng.</p></div>';
    }

    $feedback_posts = get_posts([
        'post_type'      => 'cake_feedback',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $grouped_feedback = [];
    foreach ($feedback_posts as $post) {
        $date_key = wp_date('Y-m-d', get_post_timestamp($post), wp_timezone());
        if (!isset($grouped_feedback[$date_key])) {
            $grouped_feedback[$date_key] = [];
        }
        $grouped_feedback[$date_key][] = $post;
    }

    $available_dates = array_keys($grouped_feedback);
    rsort($available_dates);

    $selected_date = isset($_GET['feedback_date']) ? sanitize_text_field(wp_unslash($_GET['feedback_date'])) : '';
    if ($selected_date === '') {
        $selected_date = !empty($available_dates) ? $available_dates[0] : '';
    }

    $show_all_dates = ($selected_date === 'all');
    if (!$show_all_dates && $selected_date !== '' && !isset($grouped_feedback[$selected_date])) {
        $selected_date = !empty($available_dates) ? $available_dates[0] : '';
    }

    $selected_items = [];
    if ($show_all_dates) {
        $selected_items = $feedback_posts;
    } elseif ($selected_date && isset($grouped_feedback[$selected_date])) {
        $selected_items = $grouped_feedback[$selected_date];
    }

    $total_feedback = count($feedback_posts);

    ob_start();
    ?>
    <div class="manager-wrap">
      <div class="manager-box feedback-admin-box">
        <div class="feedback-admin-hero">
          <div class="feedback-admin-summary">
            <span class="feedback-admin-summary__number"><?php echo esc_html($total_feedback); ?></span>
            <span class="feedback-admin-summary__label">góp ý đã nhận</span>
          </div>
        </div>

        <form class="feedback-filter-form" method="get">
          <?php foreach ($_GET as $key => $value) : ?>
            <?php if ($key === 'feedback_date') continue; ?>
            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr(wp_unslash($value)); ?>">
          <?php endforeach; ?>

          <div class="feedback-filter-form__inner">
            <div>
              <label for="feedback_date">Xem góp ý theo ngày</label>
              <p class="feedback-filter-help">Bạn có thể xem riêng từng ngày hoặc xem tất cả các ngày cùng lúc.</p>
            </div>

            <select id="feedback_date" name="feedback_date" onchange="this.form.submit()">
              <option value="all" <?php selected($selected_date, 'all'); ?>>Tất cả các ngày</option>
              <?php if ($available_dates) : ?>
                <?php foreach ($available_dates as $date_key) : ?>
                  <option value="<?php echo esc_attr($date_key); ?>" <?php selected($selected_date, $date_key); ?>>
                    <?php echo esc_html(wp_date('d/m/Y', strtotime($date_key), wp_timezone())); ?>
                  </option>
                <?php endforeach; ?>
              <?php else : ?>
                <option value="">Chưa có góp ý</option>
              <?php endif; ?>
            </select>
          </div>
        </form>

        <?php if ($selected_items) : ?>
          <div class="feedback-admin-day-heading">
            <div class="feedback-admin-day-heading__main">
              <?php if ($show_all_dates) : ?>
                <strong>Tất cả các ngày</strong>
                <span>Tổng hợp toàn bộ góp ý khách hàng đã gửi</span>
              <?php else : ?>
                <strong><?php echo esc_html(wp_date('d/m/Y', strtotime($selected_date), wp_timezone())); ?></strong>
                <span>Những góp ý khách hàng đã gửi trong ngày này</span>
              <?php endif; ?>
            </div>
            <span class="feedback-admin-day-heading__count"><?php echo esc_html(count($selected_items)); ?> góp ý</span>
          </div>

          <div class="feedback-admin-list">
            <?php foreach ($selected_items as $item) : ?>
              <article class="feedback-admin-item">
                <div class="feedback-admin-meta">
                  <div class="feedback-admin-heading">
                    <strong><?php echo esc_html(get_the_title($item)); ?></strong>
                    <span><?php echo esc_html(wp_date('d/m/Y H:i', get_post_timestamp($item), wp_timezone())); ?> · Gửi ẩn danh</span>
                  </div>
                </div>

                <div class="feedback-admin-content">
                  <?php echo nl2br(esc_html($item->post_content)); ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else : ?>
          <p class="manager-empty">Chưa có góp ý nào trong phạm vi bạn đang xem.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('cake_shop_feedback_admin', 'cake_shop_feedback_admin_shortcode');


/**
 * Tối ưu ảnh upload cho website bánh
 * - Giới hạn ảnh lớn
 * - Giảm chất lượng nén hợp lý để nhẹ hơn
 * - Ưu tiên xuất WebP nếu server hỗ trợ
 */

/* 1) Ảnh quá lớn sẽ tự scale xuống */
add_filter('big_image_size_threshold', function ($threshold) {
    return 1600;
});

/* 2) Chất lượng JPEG/WebP khi WordPress tạo ảnh */
add_filter('jpeg_quality', function ($quality) {
    return 82;
});

add_filter('wp_editor_set_quality', function ($quality, $mime_type) {
    return 82;
}, 10, 2);

/* 3) Tự chuyển định dạng ảnh tạo ra sang WebP nếu server hỗ trợ */
add_filter('image_editor_output_format', function ($formats) {
    $formats['image/jpeg'] = 'image/webp';
    $formats['image/jpg']  = 'image/webp';
    return $formats;
});

add_shortcode('cake_shop_manager', 'cake_shop_manager_shortcode');
