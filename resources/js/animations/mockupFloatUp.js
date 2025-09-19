import gsap from "gsap";

export default function mockupFloatUp() {
    //for each MainMockup, float up by 50px one after the other
    const MainMockup = document.querySelector(".main-mockups");

    gsap.set("[x-ref*='mockup-']", {
        rotationX: -35,
        rotationY: 15,
        transformOrigin: "center center"
    });

    gsap.fromTo("[x-ref*='mockup-']", {
        y: 50,
        opacity: 0,
    }, {
        y: 0,
        opacity: 1,
        duration: 1,
        ease: "power2.out",
        stagger: 0.3,
        delay: 2,
    });
}