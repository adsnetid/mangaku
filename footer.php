<footer><p>Hak Cipta &copy; <?php echo date('Y'); ?></p></footer>
<?php wp_footer(); ?>
</body>
</html>
<script>
function loadChapterPages(chapterID) {
    fetch(ajaxurl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=load_chapter_pages&chapter_id=' + chapterID
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const reader = document.getElementById('reader-pages');
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
    });
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const reader = document.getElementById("reader-pages");
    let currentIndex = 0;
    let pages = [];
    let chapterID = parseInt(reader.dataset.chapterId);
    let loading = false;

    function loadMorePages() {
        if (loading) return;
        loading = true;

        if (pages.length === 0) {
            fetch(ajaxurl, {
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
