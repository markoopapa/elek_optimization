/**
 * Universal Performance & Accessibility Optimizer
 * * @author    Markoo
 * @copyright 2026 Markoo
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. SLICK SLIDER ACCESSIBILITY FIX
    // Megkeressük az összes slidert az oldalon
    const initSlickAccessibility = () => {
        const sliders = document.querySelectorAll('.slick-slider');
        
        sliders.forEach(slider => {
            // Eseményfigyelő: minden váltás után lefut
            $(slider).on('afterChange init', function(event, slick, currentSlide) {
                // A nem látható elemek linkjeit kivesszük a Tab-sorrendből
                $(this).find('.slick-slide[aria-hidden="true"] a, .slick-slide[aria-hidden="true"] button').attr('tabindex', '-1');
                // A látható elemek linkjeit visszahelyezzük
                $(this).find('.slick-slide[aria-hidden="false"] a, .slick-slide[aria-hidden="false"] button').attr('tabindex', '0');
            });
            
            // Első lefutás kényszerítése
            $(slider).find('.slick-slide[aria-hidden="true"] a').attr('tabindex', '-1');
        });
    };

    // 2. JQUERY SAFETY WRAPPER
    // Ha a modulban be van kapcsolva a fix, megvárjuk amíg a jQuery elérhető
    if (typeof opt_jquery_active !== 'undefined' && opt_jquery_active) {
        let checkJQuery = setInterval(function() {
            if (window.jQuery) {
                clearInterval(checkJQuery);
                initSlickAccessibility();
                console.log('Universal Optimizer: jQuery fixed and Accessibility initialized.');
            }
        }, 100); // 100ms-enként ellenőrzi
    } else {
        // Ha nincs szükség várakozásra, próbáljuk indítani azonnal
        if (window.jQuery) {
            initSlickAccessibility();
        }
    }
});
