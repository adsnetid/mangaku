<footer class="site-footer">
    <div class="container">
        <p class="copyright">Hak Cipta &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></p>
        
        <?php if (has_nav_menu('footer')) : ?>
            <nav class="footer-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'menu_class' => 'footer-menu',
                    'container' => false,
                ));
                ?>
            </nav>
        <?php endif; ?>
    </div>
</footer>

<?php wp_footer(); ?>

<script>
// PERBAIKAN: Gunakan variabel ajaxurl yang didefinisikan di header atau lokalisasi script
function loadChapterPages(chapterID) {
    const ajax_url = typeof ajaxurl !== 'undefined' ? ajaxurl : mymanga_ajax.ajaxurl;
    
    fetch(ajax_url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=load_chapter_pages&chapter_id=' + chapterID
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const reader = document.getElementById('reader-pages');
            if (reader) {
                reader.innerHTML = '';
                data.data.forEach(url => {
                    const img = document.createElement('img');
                    img.src = url;
                    img.loading = 'lazy';
                    img.style.width = '100%';
                    img.style.marginBottom = '20px';
                    reader.appendChild(img);
                });
            }
        }
    })
    .catch(error => console.error('Error loading chapter pages:', error));
}

document.addEventListener("DOMContentLoaded", function() {
    const reader = document.getElementById("reader-pages");
    if (!reader) return;
    
    let currentIndex = 0;
    let pages = [];
    let chapterID = parseInt(reader.dataset.chapterId);
    let loading = false;

    function loadMorePages() {
        if (loading) return;
        loading = true;

        const ajax_url = typeof ajaxurl !== 'undefined' ? ajaxurl : mymanga_ajax.ajaxurl;

        if (pages.length === 0) {
            fetch(ajax_url, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=load_chapter_pages&chapter_id=" + chapterID
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    pages = data.data;
                    appendPages();
                }
            })
            .catch(error => {
                console.error("Error loading pages:", error);
                loading = false;
            });
        } else {
            appendPages();
        }
    }

    function appendPages() {
        const batch = pages.slice(currentIndex, currentIndex + 3);
        batch.forEach(url => {
            const img = document.createElement("img");
            img.src = url;
            img.loading = "lazy";
            img.style.width = "100%";
            img.style.marginBottom = "20px";
            reader.appendChild(img);
        });
        currentIndex += batch.length;
        loading = false;
        if (currentIndex >= pages.length) {
            window.removeEventListener("scroll", onScroll);
        }
    }

    function onScroll() {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
            loadMorePages();
        }
    }

    window.addEventListener("scroll", onScroll);
    loadMorePages(); // initial load
});
</script>
</body>
</html>