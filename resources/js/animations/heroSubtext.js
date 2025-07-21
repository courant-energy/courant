import gsap from "gsap";
import ScrollTrigger from 'gsap/ScrollTrigger';

export default function heroSubtext() {
    gsap.registerPlugin(ScrollTrigger);

    const subtext = document.querySelector(".hero-subtext");
    const heading = document.querySelector(".hero-heading");
    const wrapper = document.querySelector(".hero-wrapper");

    const isMobile = window.matchMedia("(max-width: 768px)").matches;

    // Animate heading
    gsap.fromTo(
        heading,
        { y: isMobile ? 150 : 200 },
        {
            y: 0,
            duration: 0.75,
            ease: 'expo.out',
            scrollTrigger: {
                trigger: heading,
                start: isMobile ? 'top 30%' : 'top 40%',
                toggleActions: 'play none none none', // No reverse animation
                markers: false,
            }
        }
    );

    // Pin wrapper for smoother scroll handling
    ScrollTrigger.create({
        trigger: wrapper,
        start: 'top top',
        end: '+=200',
        pin: true,
        scrub: false,
        markers: false,
    });

    // Animate subtext children
    gsap.fromTo(
        subtext.children,
        { autoAlpha: 0, y: 30 },
        {
            autoAlpha: 1,
            y: 0,
            duration: 0.5,
            ease: 'power2.out',
            stagger: {
                each: 0.2,
                from: 'start'
            },
            scrollTrigger: {
                trigger: wrapper,
                start: 'top top',
                end: '+=300',
                toggleActions: 'play none none none', // Prevent reverse
                markers: false,
            }
        }
    );
}
