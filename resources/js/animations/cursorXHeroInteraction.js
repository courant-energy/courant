import gsap from "gsap";

export default function cursorXHeroInteraction() {
    const container = document.querySelector(".mockups-container");
    const track = container?.querySelector(".mockups-track");

    if (!container || !track) return;

    // STEP 1: Zoom out after Lottie animation completes (1 sec)
    setTimeout(() => {
        track.style.pointerEvents = 'auto';
        gsap.to(track, {
            scale: 0.9,
            duration: 1,
            ease: "power2.out"
        });
    }, 2000);

    const isMobile = window.matchMedia("(max-width: 768px)").matches;

    function getMaxShift() {
        const containerWidth = container.offsetWidth;
        const trackWidth = track.scrollWidth;
        const overflow = trackWidth - containerWidth;

        // Split overflow in half (can go left/right from center)
        return overflow > 0 ? overflow / 1.1 : 0;
    }

    // STEP 2: Use IntersectionObserver to start animation when container is visible
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    if (isMobile) {
                        // STEP 2A: On mobile, move based on scroll position
                        window.addEventListener("scroll", () => {
                            const containerTop = container.offsetTop;
                            const scrollY = window.scrollY;
                            const containerHeight = container.offsetHeight;
                            const viewportHeight = window.innerHeight;

                            const progress = gsap.utils.clamp(
                                0,
                                1,
                                (scrollY + viewportHeight - containerTop) / (containerHeight + viewportHeight)
                            );

                            const shiftX = gsap.utils.clamp(
                                -getMaxShift(),
                                getMaxShift(),
                                gsap.utils.mapRange(0, 1, getMaxShift() + containerHeight + 100, -getMaxShift(), progress)
                            );
                            console.log('shiftX:', shiftX);

                            gsap.to(track, {
                                x: shiftX,
                                duration: 0.4,
                                ease: "power2.out"
                            });
                        });
                    } else {
                        // STEP 2B: Track mouse X movement for horizontal shift
                        container.addEventListener("mousemove", (e) => {
                            const bounds = container.getBoundingClientRect();
                            const x = e.clientX - bounds.left;
                            const percent = x / bounds.width;

                            const shiftX = gsap.utils.mapRange(0, 1, getMaxShift(), -getMaxShift(), percent);

                            gsap.to(track, {
                                x: shiftX,
                                duration: 0.4,
                                ease: "power2.out"
                            });
                        });

                        // STEP 3: Reset X position on mouse leave
                        container.addEventListener("mouseleave", () => {
                            gsap.to(track, {
                                x: 0,
                                duration: 0.6,
                                ease: "power3.out"
                            });
                        });
                    }
                }
            });
        },
        { threshold: 0.8 } // Trigger when at least 10% of the container is visible
    );

    observer.observe(container);
}
