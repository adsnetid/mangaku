<?php 
/**
 * The sidebar containing the main widget area
 */
?>

<aside id="secondary" class="widget-area">
    <?php if (is_active_sidebar('sidebar-1')) : ?>
        <?php dynamic_sidebar('sidebar-1'); ?>
    <?php else : ?>
        <div class="widget">
            <h2 class="widget-title"><?php _e('Search', 'my-manga-theme'); ?></h2>
            <?php get_search_form(); ?>
        </div>
        
        <div class="widget">
            <h2 class="widget-title"><?php _e('Genres', 'my-manga-theme'); ?></h2>
            <ul>
                <?php
                $genres = get_terms(array(
                    'taxonomy' => 'genre',
                    'hide_empty' => true,
                ));
                
                if (!empty($genres) && !is_wp_error($genres)) {
                    foreach ($genres as $genre) {
                        echo '<li><a href="' . esc_url(get_term_link($genre)) . '">' . 
                             $genre->name . ' (' . $genre->count . ')</a></li>';
                    }
                }
                ?>
            </ul>
        </div>
        
        <div class="widget">
            <h2 class="widget-title"><?php _e('Recent Comics', 'my-manga-theme'); ?></h2>
            <ul>
                <?php
                $recent_comics = get_posts(array(
                    'post_type' => 'comic',
                    'posts_per_page' => 5,
                ));
                
                foreach ($recent_comics as $comic) {
                    echo '<li><a href="' . get_permalink($comic->ID) . '">' . $comic->post_title . '</a></li>';
                }
                ?>
            </ul>
        </div>
    <?php endif; ?>
</aside>