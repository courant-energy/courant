import './bootstrap';
import lottie from 'lottie-web';
import cursorXHeroInteraction from './animations/cursorXHeroInteraction.js';
import heroSubtext from './animations/heroSubtext.js';
import sectionHeaderBlur from './animations/sectionHeaderBlur.js';
import featureHover from './animations/featureHover.js';
import numCounter from './animations/numCounter.js';
import missionImgFloat from './animations/missionImgFloat.js';
import mockupFloatUp from './animations/mockupFloatUp.js';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-animation]').forEach(el => {
        lottie.loadAnimation({
            container: el,
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: el.getAttribute('data-animation')
        });
    });
    cursorXHeroInteraction();
    heroSubtext();
    sectionHeaderBlur();
    featureHover();
    numCounter();
    missionImgFloat();
    mockupFloatUp();
});
