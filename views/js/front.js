/**
 * Universal Performance & Accessibility Optimizer
 * @author    Markoo
 */

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. HEADING ORDER FIX ---
    // Ha a Dashboardon bekapcsoltad, előlépteti a neveket h2-vé
    if (typeof opt_heading_fix !== 'undefined' && opt_heading_fix) {
        const productNames = document.querySelectorAll('h5.product-name');
        productNames.forEach(el => {
            const h2 = document.createElement('h2');
            h2.innerHTML = el.innerHTML;
            h2.className = el.className;
            el.parentNode.replaceChild(h2, el);
        });
        console.log('Optimizer: Headings upgraded to H2.');
    }

    // --- 2. SLIDER & JQUERY LOGIC ---
    const runSlickFix = () => {
        if (typeof opt_slick_fix !== 'undefined' && opt_slick_fix && window.jQuery && window.jQuery.fn.slick) {
            $('.slick-slider').on('afterChange init', function() {
                $(this).find('.slick-slide[aria-hidden="true"] a').attr('tabindex', '-1');
                $(this).find('.slick-slide[aria-hidden="false"] a').attr('tabindex', '0');
            }).find('.slick-slide[aria-hidden="true"] a').attr('tabindex', '-1');
        }
    };

    // Ha be van kapcsolva a jQuery Safety fix, várunk rá
    if (typeof opt_jquery_active !== 'undefined' && opt_jquery_active) {
        let checkJQuery = setInterval(function() {
            if (window.jQuery) {
                clearInterval(checkJQuery);
                runSlickFix();
            }
        }, 100);
        setTimeout(() => clearInterval(checkJQuery), 5000);
    } else {
        runSlickFix();
    }
});
