<?php get_header(); ?>

<main id="primary" class="site-main">
    <div class="container">
        <h1 class="page-title">Beranda Komik</h1>
        
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
                                    $genres = get_the_terms(get_the_ID(), 'genre');
                                    if ($genres && !is_wp_error($genres)) {
                                        echo '<div class="comic-genres">';
                                        foreach ($genres as $genre) {
                                            echo '<a href="' . esc_url(get_term_link($genre)) . '">' . $genre->name . '</a>';
                                        }
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
                <?php the_posts_pagination(); ?>
            </div>
            
        <?php else : ?>
            <p><?php _e('Tidak ada komik yang ditemukan.', 'my-manga-theme'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php get_sidebar(); ?>
<?php get_footer(); ?>