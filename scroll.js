// Smooth scrolling for navigation links
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('a[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const navHeight = document.querySelector('.custom-navbar').offsetHeight;
                const targetPosition = targetElement.offsetTop - navHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update active state in navigation
                navLinks.forEach(link => link.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // Highlight active section on scroll
    function updateActiveSection() {
        const sections = document.querySelectorAll('section');
        const navHeight = document.querySelector('.custom-navbar').offsetHeight;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - navHeight - 100; // Added offset for better trigger
            const sectionBottom = sectionTop + section.offsetHeight;
            const scrollPosition = window.scrollY;

            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                const correspondingLink = document.querySelector(`a[href="#${section.id}"]`);
                if (correspondingLink) {
                    navLinks.forEach(link => link.classList.remove('active'));
                    correspondingLink.classList.add('active');
                }
            }
        });
    }

    // Scroll animations
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        const windowHeight = window.innerHeight || document.documentElement.clientHeight;
        const threshold = 0.2; // 20% of element must be visible
        
        return (
            rect.top <= windowHeight * (1 - threshold) &&
            rect.bottom >= windowHeight * threshold
        );
    }

    function handleScrollAnimation() {
        animatedElements.forEach(element => {
            if (isInViewport(element) && !element.classList.contains('animated')) {
                element.classList.add('animated');
            }
        });
    }

    // Add scroll event listeners
    window.addEventListener('scroll', () => {
        requestAnimationFrame(() => {
            updateActiveSection();
            handleScrollAnimation();
        });
    });

    // Initial check
    updateActiveSection();
    handleScrollAnimation();

    // Navbar color change on scroll
    const navbar = document.querySelector('.custom-navbar');
    window.addEventListener('scroll', () => {
        requestAnimationFrame(() => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    });
});