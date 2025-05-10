/**
 * Main JavaScript for My Manga Theme
 */

(function() {
    'use strict';
    
    // Mobile Menu Toggle
    function setupMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNav = document.querySelector('.main-navigation');
        
        if (menuToggle && mainNav) {
            menuToggle.addEventListener('click', function() {
                mainNav.classList.toggle('toggled');
                this.setAttribute('aria-expanded', 
                    this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true'
                );
            });
        }
    }
    
    // Lazy Loading Images
    function setupLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.src = img.dataset.src;
            });
        } else {
            // Fallback for browsers that don't support native lazy loading
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
            document.body.appendChild(script);
        }
    }
    
    // Chapter Navigation
    function setupChapterNavigation() {
        const prevBtn = document.querySelector('.prev-chapter');
        const nextBtn = document.querySelector('.next-chapter');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                const prevUrl = this.getAttribute('href');
                if (!prevUrl || prevUrl === '#') {
                    e.preventDefault();
                    alert('This is the first chapter');
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                const nextUrl = this.getAttribute('href');
                if (!nextUrl || nextUrl === '#') {
                    e.preventDefault();
                    alert('This is the latest chapter');
                }
            });
        }
    }
    
    // Init functions when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        setupMobileMenu();
        setupLazyLoading();
        setupChapterNavigation();
        
        // Add smooth scrolling to all links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    
})();