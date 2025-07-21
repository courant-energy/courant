import gsap from "gsap";
import ScrollTrigger from "gsap/ScrollTrigger";

export default function missionImgFloat() {
    gsap.registerPlugin(ScrollTrigger);
    
    const missionImages = document.querySelectorAll(".mission-img");
    
    missionImages.forEach((img) => {
        gsap.fromTo(
        img,
        { y: 0 }, // Start position
        {
            y: -50, // Float up
            duration: 0.75,
            ease: "power1.inOut",
            repeat:0,
            scrollTrigger: {
            trigger: img,
            start: "top 80%",
            end: "bottom 20%",
            toggleActions: "play none none reverse",
            markers: false,
            once: true, // Animation plays only once
            },
        }
        );
    });
}