<?php
get_header();

if (have_posts()) : while (have_posts()) : the_post();
    $pages = get_post_meta(get_the_ID(), 'chapter_images', true); // Array of image URLs
    $pages = is_array($pages) ? $pages : [];
?>
<div id="chapter-reader" class="chapter-viewer">
    <h1><?php the_title(); ?></h1>
    <?php $cid = get_the_ID(); ?><div id="reader-pages" data-chapter-id="<?php echo $cid; ?>">
        <?php foreach ($pages as $img_url): ?>
            <img src="<?php echo esc_url($img_url); ?>" loading="lazy" class="comic-page" style="width:100%;margin-bottom:20px;">
        <?php endforeach; ?>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const imgs = document.querySelectorAll("img[loading='lazy']");
        imgs.forEach(img => {
            img.loading = 'lazy';
        });
    });
</script>
<?php endwhile; endif;

get_footer();
?>