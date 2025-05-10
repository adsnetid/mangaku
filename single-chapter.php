<?php
/**
 * The template for displaying chapter content
 */

get_header();

if (have_posts()) : while (have_posts()) : the_post();
    // Get parent comic
    $parent_comic = get_post_meta(get_the_ID(), 'parent_comic', true);
    
    // Get chapters list
    $chapters = get_posts([
        'post_type' => 'chapter',
        'posts_per_page' => -1,
        'meta_key' => 'parent_comic',
        'meta_value' => $parent_comic,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    // Find current chapter index
    $current_index = 0;
    foreach ($chapters as $index => $ch) {
        if ($ch->ID === get_the_ID()) {
            $current_index = $index;
            break;
        }
    }
    
    // Get prev/next chapters
    $prev_chapter = ($current_index > 0) ? $chapters[$current_index - 1] : null;
    $next_chapter = ($current_index < count($chapters) - 1) ? $chapters[$current_index + 1] : null;
    
    // Get pages
    $pages = get_post_meta(get_the_ID(), 'chapter_images', true);
    $pages = is_array($pages) ? $pages : [];
?>

<div class="chapter-reader">
    <div class="chapter-header">
        <h1><?php the_title(); ?></h1>
        
        <?php if ($parent_comic): ?>
            <div class="comic-breadcrumb">
                <a href="<?php echo esc_url(get_permalink($parent_comic)); ?>">
                    <?php echo get_the_title($parent_comic); ?>
                </a> &raquo; <?php the_title(); ?>
            </div>
        <?php endif; ?>
        
        <div class="chapter-navigation">
            <?php if ($prev_chapter): ?>
                <a href="<?php echo get_permalink($prev_chapter->ID); ?>" class="prev-chapter">
                    &laquo; <?php echo $prev_chapter->post_title; ?>
                </a>
            <?php else: ?>
                <span class="prev-chapter disabled">&laquo; Previous Chapter</span>
            <?php endif; ?>
            
            <select class="chapter-select" onchange="if (this.value) window.location.href=this.value">
                <?php foreach ($chapters as $ch): ?>
                    <option value="<?php echo get_permalink($ch->ID); ?>" 
                            <?php selected($ch->ID, get_the_ID()); ?>>
                        <?php echo $ch->post_title; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($next_chapter): ?>
                <a href="<?php echo get_permalink($next_chapter->ID); ?>" class="next-chapter">
                    <?php echo $next_chapter->post_title; ?> &raquo;
                </a>
            <?php else: ?>
                <span class="next-chapter disabled">Next Chapter &raquo;</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="reader-pages" data-chapter-id="<?php echo get_the_ID(); ?>" class="chapter-content">
        <?php if (empty($pages)): ?>
            <div class="no-pages-message">
                No pages found for this chapter.
            </div>
        <?php else: ?>
            <?php 
            // Display first 5 pages initially, rest will be loaded by JavaScript
            $initial_pages = array_slice($pages, 0, 5);
            foreach ($initial_pages as $img_url): 
            ?>
                <img src="<?php echo esc_url($img_url); ?>" loading="lazy" class="comic-page">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="chapter-footer">
        <div class="chapter-navigation">
            <?php if ($prev_chapter): ?>
                <a href="<?php echo get_permalink($prev_chapter->ID); ?>" class="prev-chapter">
                    &laquo; <?php echo $prev_chapter->post_title; ?>
                </a>
            <?php endif; ?>
            
            <?php if ($parent_comic): ?>
                <a href="<?php echo get_permalink($parent_comic); ?>" class="back-to-comic">
                    Back to Comic
                </a>
            <?php endif; ?>
            
            <?php if ($next_chapter): ?>
                <a href="<?php echo get_permalink($next_chapter->ID); ?>" class="next-chapter">
                    <?php echo $next_chapter->post_title; ?> &raquo;
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
endwhile; 
else: 
?>
    <div class="no-content">
        <p><?php _e('No chapter found.', 'my-manga-theme'); ?></p>
    </div>
<?php 
endif;

get_footer();
?>