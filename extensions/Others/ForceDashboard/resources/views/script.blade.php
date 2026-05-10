<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find navigation items by text content and hide them
    const navLinks = document.querySelectorAll('a');
    navLinks.forEach(link => {
        const text = link.textContent.trim().toLowerCase();
        if (text === 'home' || text === 'shop') {
            const parent = link.closest('li, [data-nav], .nav-item');
            if (parent) {
                parent.style.display = 'none';
            }
        }
    });
});
</script>
