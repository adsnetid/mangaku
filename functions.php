<?php
function mymanga_register_post_types() {
    register_post_type('comic', [
        'labels' => ['name' => __('Komik', 'my-manga-theme')],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'komik'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'taxonomies' => ['genre', 'status', 'type']
    ]);
}
add_action('init', 'mymanga_register_post_types');

function mymanga_register_taxonomies() {
    register_taxonomy('genre', 'comic', ['label' => __('Genre', 'my-manga-theme'), 'rewrite' => ['slug' => 'genre']]);
    register_taxonomy('status', 'comic', ['label' => __('Status', 'my-manga-theme'), 'rewrite' => ['slug' => 'status']]);
    register_taxonomy('type', 'comic', ['label' => __('Tipe', 'my-manga-theme'), 'rewrite' => ['slug' => 'tipe']]);
}
add_action('init', 'mymanga_register_taxonomies');

require_once ABSPATH . 'wp-content/plugins/phpfastcache/vendor/autoload.php';
use Phpfastcache\Helper\Psr16Adapter;
function mymanga_get_cache_instance() {
    static $cache = null;
    if ($cache === null) $cache = new Psr16Adapter('Files');
    return $cache;
}

function mymanga_lazyload_images($content) {
    return preg_replace('/<img(.*?)src=["\'](.*?)["\'](.*?)>/i', '<img$1src="$2"$3 loading="lazy">', $content);
}
add_filter('the_content', 'mymanga_lazyload_images');

function mymanga_add_meta_tags() {
    if (is_single() || is_page()) {
        global $post;
        $excerpt = strip_tags($post->post_excerpt ? $post->post_excerpt : wp_trim_words($post->post_content, 30));
        echo '<meta name="description" content="' . esc_attr($excerpt) . '">' . "\n";
        echo '<meta name="keywords" content="' . esc_attr(join(', ', wp_get_post_tags($post->ID, ['fields' => 'names']))) . '">' . "\n";
    }
}
add_action('wp_head', 'mymanga_add_meta_tags');

function mymanga_generate_sitemap() {
    if (!is_admin() && isset($_GET['mymanga-sitemap'])) {
        header('Content-Type: application/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $args = ['post_type' => 'comic', 'post_status' => 'publish', 'posts_per_page' => -1];
        $comics = get_posts($args);
        foreach ($comics as $comic) {
            echo '<url>';
            echo '<loc>' . get_permalink($comic) . '</loc>';
            echo '<lastmod>' . get_the_modified_time('c', $comic) . '</lastmod>';
            echo '</url>';
        }
        echo '</urlset>';
        exit;
    }
}
add_action('init', 'mymanga_generate_sitemap');

function mymanga_add_schema_markup() {
    if (is_singular('comic')) {
        global $post;
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Book",
            "name" => get_the_title(),
            "author" => get_the_author(),
            "image" => get_the_post_thumbnail_url($post, 'full'),
            "description" => get_the_excerpt(),
            "url" => get_permalink(),
            "datePublished" => get_the_date('c'),
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
    }
}
add_action('wp_head', 'mymanga_add_schema_markup');

add_action('wp_ajax_load_chapter_pages', 'ajax_load_chapter_pages');
add_action('wp_ajax_nopriv_load_chapter_pages', 'ajax_load_chapter_pages');
function ajax_load_chapter_pages() {
    $chapter_id = intval($_POST['chapter_id']);
    $pages = get_post_meta($chapter_id, 'chapter_images', true);
    $pages = is_array($pages) ? $pages : [];
    wp_send_json_success($pages);
    wp_die();
}
