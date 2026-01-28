/**
 * Universal Performance & Accessibility Optimizer
 * @author    Markoo
 * @copyright 2026 Markoo
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * 1. HEADING ORDER FIX (SEO & Accessibility)
     * Keresi a h5.product-name elemeket és előlépteti őket h2-vé.
     * Így a Google nem reklamál a sorrend miatt.
     */
    const fixHeadingOrder = () => {
        const productNames = document.querySelectorAll('h5.product-name');
        productNames.forEach(el => {
            const h2 = document.createElement('h2');
            h2.innerHTML = el.innerHTML;
            h2.className = el.className; // Megtartjuk a stílust (class-okat)
            
            // Ha van rajta egyedi ID vagy stílus, azt is átvisszük
            if (el.id) h2.id = el.id;
            
            el.parentNode.replaceChild(h2, el);
        });
        console.log('Universal Optimizer: Headings updated (h5 -> h2).');
    };

    /**
     * 2. SLICK SLIDER ACCESSIBILITY FIX
     * Megakadályozza a "ghost focus" hibát a láthatatlan slide-okon.
     */
    const initSlickAccessibility = () => {
        const $sliders = window.jQuery('.slick-slider');
        
        $sliders.each(function() {
            const $this = window.jQuery(this);
            
            $this.on('afterChange init', function(event, slick, currentSlide) {
                // Elrejtjük a nem aktív slide-ok linkjeit a Tab gomb elől
                $this.find('.slick-slide[aria-hidden="true"] a, .slick-slide[aria-hidden="true"] button').attr('tabindex', '-1');
                // Aktiváljuk a láthatókat
                $this.find('.slick-slide[aria-hidden="false"] a, .slick-slide[aria-hidden="false"] button').attr('tabindex', '0');
            });

            // Első futtatás a betöltéskor
            $this.find('.slick-slide[aria-hidden="true"] a').attr('tabindex', '-1');
        });
    };

    /**
     * 3. JQUERY SAFETY WRAPPER & INITIALIZATION
     * Ez a rész figyeli, hogy betöltött-e már a jQuery a PrestaShopban.
     */
    const startOptimization = () => {
        // Mindig lefuttatjuk a tiszta JS-es címsor javítást (nem kell hozzá jQuery)
        fixHeadingOrder();

        // Ha a modulban be van kapcsolva a jQuery fix, vagy csak biztosra megyünk
        let checkJQuery = setInterval(function() {
            if (window.jQuery && typeof window.jQuery.fn.slick !== 'undefined') {
                clearInterval(checkJQuery);
                initSlickAccessibility();
                console.log('Universal Optimizer: Slick accessibility initialized.');
            }
        }, 100);

        // 5 másodperc után állítsuk le az ellenőrzést, ha nem találná a jQuery-t
        setTimeout(() => clearInterval(checkJQuery), 5000);
    };

    // Indítás
    startOptimization();
});
