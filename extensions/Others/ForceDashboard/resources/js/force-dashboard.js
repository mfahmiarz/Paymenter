// Hide Home and Shop from navigation
function hideNavigationItems() {
    // Find all navigation links
    const navLinks = document.querySelectorAll('a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        const text = link.textContent.trim().toLowerCase();
        
        // Hide Home (text is "home", href is root)
        if (text === 'home' || (href && href === window.location.origin + '/')) {
            // Hide the link and its parent
            link.style.display = 'none';
            const parent = link.closest('li, [data-nav], .nav-item');
            if (parent) {
                parent.style.display = 'none';
            }
        }
    });
    
    // Find Shop button directly
    const allButtons = document.querySelectorAll('button');
    allButtons.forEach(button => {
        const buttonText = button.textContent.trim().toLowerCase();
        if (buttonText.includes('shop')) {
            const shopContainer = button.closest('div.relative');
            if (shopContainer) {
                shopContainer.style.display = 'none';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', hideNavigationItems);

// Run periodically to catch dynamically added elements
setInterval(hideNavigationItems, 100);

// Also run on window resize and other events
window.addEventListener('resize', hideNavigationItems);
window.addEventListener('popstate', hideNavigationItems);
