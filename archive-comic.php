<?php
/**
 * The template for displaying comic archives
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <h1 class="page-title">
            <?php 
            if (is_tax()) {
                single_term_title('', true);
            } else {
                _e('Arsip Komik', 'my-manga-theme');
            }
            ?>
        </h1>
        
        <?php if (is_tax()) : ?>
            <div class="taxonomy-description">
                <?php echo term_description(); ?>
            </div>
        <?php endif; ?>
        
        <?php if (have_posts()) : ?>
            <div class="comics-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="comic-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', array('class' => 'comic-thumbnail')); ?>
                                </a>
                            <?php endif; ?>
                            
                            <div class="comic-info">
                                <h2 class="comic-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <div class="comic-meta">
                                    <?php
                                    // Display genres
                                    $genres = get_the_terms(get_the_ID(), 'genre');
                                    if ($genres && !is_wp_error($genres)) {
                                        echo '<div class="comic-genres">';
                                        foreach ($genres as $genre) {
                                            echo '<a href="' . esc_url(get_term_link($genre)) . '">' . $genre->name . '</a>';
                                        }
                                        echo '</div>';
                                    }
                                    
                                    // Display status if available
                                    $status = get_the_terms(get_the_ID(), 'status');
                                    if ($status && !is_wp_error($status)) {
                                        echo '<div class="comic-status">' . __('Status: ', 'my-manga-theme') . 
                                             '<span>' . $status[0]->name . '</span></div>';
                                    }
                                    
                                    // Get latest chapter for this comic
                                    $chapters = get_posts([
                                        'post_type' => 'chapter',
                                        'posts_per_page' => 1,
                                        'meta_key' => 'parent_comic',
                                        'meta_value' => get_the_ID(),
                                        'orderby' => 'date',
                                        'order' => 'DESC'
                                    ]);
                                    
                                    if (!empty($chapters)) {
                                        echo '<div class="latest-chapter">';
                                        echo '<a href="' . get_permalink($chapters[0]->ID) . '">' . 
                                             __('Chapter Terbaru: ', 'my-manga-theme') . $chapters[0]->post_title . '</a>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <div class="pagination">
                <?php the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => __('&laquo; Sebelumnya', 'my-manga-theme'),
                    'next_text' => __('Selanjutnya &raquo;', 'my-manga-theme'),
                )); ?>
            </div>
            
        <?php else : ?>
            <p><?php _e('Tidak ada komik yang ditemukan.', 'my-manga-theme'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php 
get_sidebar();
get_footer();
?>