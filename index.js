document.addEventListener('DOMContentLoaded', function() {
    // Animated Typing Effect for Welcome Section
    const welcomeText = document.querySelector('.welcome-content p');
    if (welcomeText) {
        const originalText = welcomeText.textContent;
        welcomeText.textContent = '';
        let index = 0;

        function typeWriter() {
            if (index < originalText.length) {
                welcomeText.textContent += originalText.charAt(index);
                index++;
                setTimeout(typeWriter, 50);
            }
        }
        typeWriter();
    }

    // Interactive Course Cards with Enhanced Animation
    const courseCards = document.querySelectorAll('.course-card');
    const tooltips = {
        'Mathematics': '🧮 Number Wizards Unite!',
        'Swahili': '🗣️ Language Explorers Welcome!',
        'English': '📖 Words Are Our Superpower!',
        'Biology': '🧬 Life\'s Mysteries Await!',
        'Chemistry': '🧪 Science Magic Happens Here!'
    };

    courseCards.forEach(card => {
        const icon = card.querySelector('i');
        const title = card.querySelector('h3')?.textContent;
        
        if (title) {
            card.setAttribute('data-tooltip', tooltips[title] || '🌟 Exciting Learning Ahead!');
        }

        card.addEventListener('mouseenter', () => {
            if (icon) {
                icon.style.animation = 'bounce 0.5s infinite';
            }
            card.style.transform = 'scale(1.05)';
            card.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
            
            const tooltip = document.createElement('div');
            tooltip.className = 'course-tooltip';
            tooltip.textContent = card.getAttribute('data-tooltip');
            card.appendChild(tooltip);
        });

        card.addEventListener('mouseleave', () => {
            if (icon) {
                icon.style.animation = 'none';
            }
            card.style.transform = 'scale(1)';
            card.style.boxShadow = 'none';
            
            const tooltip = card.querySelector('.course-tooltip');
            if (tooltip) tooltip.remove();
        });
    });

    // Contact Section Animation
    const contactInfo = document.querySelector('.contact-info');
    if (contactInfo) {
        contactInfo.classList.add('slide-up');
        
        const socialLinks = contactInfo.querySelectorAll('.social-links a');
        socialLinks.forEach((link, index) => {
            link.style.opacity = '0';
            link.style.transform = 'translateY(20px)';
            setTimeout(() => {
                link.style.opacity = '1';
                link.style.transform = 'translateY(0)';
            }, 300 + (index * 100));
        });
    }

    // Footer Animation
    const footerSections = document.querySelectorAll('.footer .col-md-4');
    footerSections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        setTimeout(() => {
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
            section.style.transition = 'all 0.5s ease';
        }, 200 * (index + 1));
    });

    // Animation on scroll
    function checkScroll() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight;
            if (elementPosition < screenPosition) {
                element.classList.add('animated');
            }
        });
    }

    window.addEventListener('scroll', checkScroll);
    checkScroll(); // Initial check
});