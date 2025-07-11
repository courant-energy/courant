import gsap from "gsap";
import ScrollTrigger from "gsap/ScrollTrigger";

export default function sectionHeaderBlur() {
  gsap.registerPlugin(ScrollTrigger);

  const sectionHeaders = document.querySelectorAll(".section-header");

  sectionHeaders.forEach((header) => {
    gsap.fromTo(
      header,
      { filter: "blur(5px)" }, // Start blurred
      {
        filter: "blur(0px)", // End clear
        duration: 1,
        ease: "power2.out",
        scrollTrigger: {
          trigger: header,
          start: "top 90%",
          end: "bottom 20%",
          toggleActions: "play none none reverse",
          markers: false,
        },
      }
    );
  });
}
