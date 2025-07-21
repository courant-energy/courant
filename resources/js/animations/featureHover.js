import gsap from "gsap";

export default function featureHover() {
    const isMobile = window.matchMedia("(max-width: 768px)").matches;
    if (isMobile) return; // Skip animations on mobile devices
    const cardContainer = document.querySelectorAll('.feature-card');

    cardContainer.forEach((card) => {
        const cardTitle = card.querySelector('.feature-card-title');
        const cardDescription = card.querySelector('.feature-card-description');

        // 🔹 INITIAL STATE: center title and hide description
        gsap.set(cardTitle, {
            y: 25, // Start title slightly lower
        });

        gsap.set(cardDescription, {
            opacity: 0,
        });

        // 🔹 HOVER ANIMATION
        card.addEventListener('mouseenter', () => {
            gsap.to(cardTitle, {
                y: 0, // Move title upward to centered position
                duration: 0.3,
                ease: 'power2.out',
            });

            gsap.to(cardDescription, {
                opacity: 1,
                duration: 0.3,
                ease: 'power2.out',
            });

            gsap.to(card, {
                backgroundColor: '#fffaf3',
                duration: 0.3,
            });
        });

        card.addEventListener('mouseleave', () => {
            gsap.to(cardTitle, {
                y: 25, // Move title back down
                duration: 0.3,
                ease: 'power2.out',
            });

            gsap.to(cardDescription, {
                opacity: 0,
                duration: 0.3,
                ease: 'power2.out',
            });

            gsap.to(card, {
                backgroundColor: '#ffffff',
                duration: 0.3,
            });
        });
    });
}
