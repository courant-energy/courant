import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

function parseTargetValue(str) {
    if (str.includes('%')) {
        return { value: parseFloat(str), suffix: '%' };
    } else if (str.toLowerCase().includes('k')) {
        const num = parseFloat(str.toLowerCase().replace('k+', ''));
        return { value: num, suffix: 'k+' };
    } else if (str.includes('/')) {
        // Special case: treat as constant (e.g., 24/7)
        return { value: str, isConstant: true };
    } else {
        return { value: parseFloat(str), suffix: '' };
    }
}

export default function numCounter() {
    document.querySelectorAll('.count-number').forEach(el => {
        const raw = el.getAttribute('data-target');
        const { value, suffix, isConstant } = parseTargetValue(raw);

        if (isConstant) {
            el.innerText = value;
            return;
        }

        // Create a ScrollTrigger for each number
        ScrollTrigger.create({
            trigger: el,
            start: 'top 90%',
            once: true,
            onEnter: () => {
                gsap.fromTo(el,
                    { innerText: 0 },
                    {
                        innerText: value,
                        duration: 1,
                        ease: 'power1.out',
                        snap: { innerText: 1 },
                        onUpdate: function () {
                            el.innerText = Math.floor(el.innerText) + suffix;
                        }
                    }
                );
            }
        });
    });
}
