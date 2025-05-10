<?php
function mymanga_register_post_types() {
    // Register Comic post type
    register_post_type('comic', [
        'labels' => ['name' => __('Komik', 'my-manga-theme')],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'komik'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'taxonomies' => ['genre', 'status', 'type']
    ]);
    
    // Register Chapter post type - DITAMBAHKAN
    register_post_type('chapter', [
        'labels' => ['name' => __('Chapter', 'my-manga-theme')],
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'chapter'],
        'supports' => ['title', 'editor', 'thumbnail'],
    ]);
}
add_action('init', 'mymanga_register_post_types');

function mymanga_register_taxonomies() {
    register_taxonomy('genre', 'comic', ['label' => __('Genre', 'my-manga-theme'), 'rewrite' => ['slug' => 'genre']]);
    register_taxonomy('status', 'comic', ['label' => __('Status', 'my-manga-theme'), 'rewrite' => ['slug' => 'status']]);
    register_taxonomy('type', 'comic', ['label' => __('Tipe', 'my-manga-theme'), 'rewrite' => ['slug' => 'tipe']]);
}
add_action('init', 'mymanga_register_taxonomies');

// PERBAIKAN: Gunakan caching bawaan WordPress atau load phpfastcache secara kondisional
function mymanga_get_cache_instance() {
    // Cek apakah phpfastcache tersedia
    if (file_exists(ABSPATH . 'wp-content/plugins/phpfastcache/vendor/autoload.php')) {
        require_once ABSPATH . 'wp-content/plugins/phpfastcache/vendor/autoload.php';
        use Phpfastcache\Helper\Psr16Adapter;
        static $cache = null;
        if ($cache === null) $cache = new Psr16Adapter('Files');
        return $cache;
    }
    // Gunakan array sederhana sebagai fallback
    static $fallback_cache = [];
    return new class($fallback_cache) {
        private $cache;
        public function __construct(&$cache) { $this->cache = &$cache; }
        public function get($key, $default = null) { return isset($this->cache[$key]) ? $this->cache[$key] : $default; }
        public function set($key, $value, $ttl = null) { $this->cache[$key] = $value; return true; }
        public function delete($key) { unset($this->cache[$key]); return true; }
        public function clear() { $this->cache = []; return true; }
    };
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

// PERBAIKAN: Tambahkan definisi ajaxurl untuk frontend
function mymanga_add_ajax_url() {
    echo '<script>var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
}
add_action('wp_head', 'mymanga_add_ajax_url');

add_action('wp_ajax_load_chapter_pages', 'ajax_load_chapter_pages');
add_action('wp_ajax_nopriv_load_chapter_pages', 'ajax_load_chapter_pages');
function ajax_load_chapter_pages() {
    $chapter_id = intval($_POST['chapter_id']);
    $pages = get_post_meta($chapter_id, 'chapter_images', true);
    $pages = is_array($pages) ? $pages : [];
    wp_send_json_success($pages);
    wp_die();
}

// PERBAIKAN: Tambahkan fungsi setup untuk tema
function mymanga_theme_setup() {
    // Aktifkan fitur-fitur tema
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Daftarkan menu navigasi
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'my-manga-theme'),
        'footer' => __('Footer Menu', 'my-manga-theme'),
    ));
}
add_action('after_setup_theme', 'mymanga_theme_setup');

// PERBAIKAN: Tambahkan fungsi untuk enqueue scripts dan styles
function mymanga_enqueue_scripts() {
    wp_enqueue_style('mymanga-style', get_stylesheet_uri());
    wp_enqueue_script('mymanga-script', get_template_directory_uri() . '/js/script.js', array('jquery'), '1.0.0', true);
    
    // Tambahkan ajaxurl di frontend
    wp_localize_script('mymanga-script', 'mymanga_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'mymanga_enqueue_scripts');
/**
 * Register widget area.
 */
function mymanga_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'my-manga-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'my-manga-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widgets', 'my-manga-theme'),
        'id'            => 'footer-widgets',
        'description'   => __('Add widgets here to appear in your footer.', 'my-manga-theme'),
        'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'mymanga_widgets_init');
/**
 * Menambahkan meta box untuk chapter
 */
function mymanga_register_meta_boxes() {
    add_meta_box(
        'mymanga-chapter-images',
        __('Chapter Images', 'my-manga-theme'),
        'mymanga_chapter_images_metabox',
        'chapter',
        'normal',
        'high'
    );
    
    add_meta_box(
        'mymanga-chapter-parent',
        __('Parent Comic', 'my-manga-theme'),
        'mymanga_chapter_parent_metabox',
        'chapter',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'mymanga_register_meta_boxes');

/**
 * Meta box untuk gambar chapter
 */
function mymanga_chapter_images_metabox($post) {
    wp_nonce_field('mymanga_chapter_images_save', 'mymanga_chapter_images_nonce');
    
    $images = get_post_meta($post->ID, 'chapter_images', true);
    $images = is_array($images) ? $images : [];
    ?>
    <div id="chapter-images-container">
        <p><?php _e('Add images for this chapter. Drag to reorder.', 'my-manga-theme'); ?></p>
        
        <div id="chapter-images-list" style="margin-bottom:10px;">
            <?php foreach ($images as $index => $image_url): ?>
                <div class="chapter-image-item" style="padding:10px;border:1px solid #ddd;margin-bottom:5px;background:#f9f9f9;">
                    <img src="<?php echo esc_url($image_url); ?>" style="max-height:100px;max-width:100px;display:inline-block;vertical-align:middle;margin-right:10px;">
                    <input type="text" name="chapter_images[]" value="<?php echo esc_url($image_url); ?>" style="width:80%;">
                    <button type="button" class="button remove-image" style="vertical-align:middle;">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-bottom:10px;">
            <input type="text" id="new-image-url" placeholder="Enter image URL" style="width:80%;">
            <button type="button" class="button" id="add-image-url">Add Image URL</button>
        </div>
        
        <p>
            <button type="button" class="button-primary" id="upload-chapter-images">
                <?php _e('Upload Images', 'my-manga-theme'); ?>
            </button>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        const imagesList = $('#chapter-images-list');
        
        // Make the list sortable
        imagesList.sortable();
        
        // Add image URL
        $('#add-image-url').on('click', function() {
            const url = $('#new-image-url').val().trim();
            if (url) {
                addImageItem(url);
                $('#new-image-url').val('');
            }
        });
        
        // Remove image
        $(document).on('click', '.remove-image', function() {
            $(this).closest('.chapter-image-item').remove();
        });
        
        // Helper function to add image item
        function addImageItem(url) {
            const item = `
                <div class="chapter-image-item" style="padding:10px;border:1px solid #ddd;margin-bottom:5px;background:#f9f9f9;">
                    <img src="${url}" style="max-height:100px;max-width:100px;display:inline-block;vertical-align:middle;margin-right:10px;">
                    <input type="text" name="chapter_images[]" value="${url}" style="width:80%;">
                    <button type="button" class="button remove-image" style="vertical-align:middle;">Remove</button>
                </div>
            `;
            imagesList.append(item);
        }
        
        // Bulk upload images
        $('#upload-chapter-images').on('click', function(e) {
            e.preventDefault();
            
            const uploadFrame = wp.media({
                title: 'Select or Upload Chapter Images',
                button: {
                    text: 'Use these images'
                },
                multiple: true
            });
            
            uploadFrame.on('select', function() {
                const attachments = uploadFrame.state().get('selection').toJSON();
                
                attachments.forEach(function(attachment) {
                    addImageItem(attachment.url);
                });
            });
            
            uploadFrame.open();
        });
    });
    </script>
    <?php
}

/**
 * Meta box untuk parent comic
 */
function mymanga_chapter_parent_metabox($post) {
    wp_nonce_field('mymanga_chapter_parent_save', 'mymanga_chapter_parent_nonce');
    
    $parent_comic = get_post_meta($post->ID, 'parent_comic', true);
    
    // Get all comics
    $comics = get_posts([
        'post_type' => 'comic',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    ?>
    <select name="parent_comic" style="width:100%;">
        <option value=""><?php _e('Select Comic', 'my-manga-theme'); ?></option>
        <?php foreach ($comics as $comic): ?>
            <option value="<?php echo $comic->ID; ?>" <?php selected($parent_comic, $comic->ID); ?>>
                <?php echo $comic->post_title; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Save meta box data
 */
function mymanga_save_meta_box_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    // Save chapter images
    if (
        isset($_POST['mymanga_chapter_images_nonce']) && 
        wp_verify_nonce($_POST['mymanga_chapter_images_nonce'], 'mymanga_chapter_images_save')
    ) {
        $images = isset($_POST['chapter_images']) ? (array) $_POST['chapter_images'] : [];
        $images = array_filter($images); // Remove empty values
        update_post_meta($post_id, 'chapter_images', $images);
    }
    
    // Save parent comic
    if (
        isset($_POST['mymanga_chapter_parent_nonce']) && 
        wp_verify_nonce($_POST['mymanga_chapter_parent_nonce'], 'mymanga_chapter_parent_save')
    ) {
        $parent_comic = isset($_POST['parent_comic']) ? sanitize_text_field($_POST['parent_comic']) : '';
        update_post_meta($post_id, 'parent_comic', $parent_comic);
    }
}
add_action('save_post_chapter', 'mymanga_save_meta_box_data');