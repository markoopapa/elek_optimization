<?php
/**
 * @author    Markoo
 * @copyright 2026 Markoo
 */

class PerformanceHandler {
    
    /**
     * LCP Preload link generálása a <head> részbe.
     * Javítva: kezeli a .png és .jpeg kiterjesztéseket is.
     */
    public static function run($context) {
        if (!Configuration::get('OPT_LCP_PRELOAD')) return '';

        if ($context->controller->php_self == 'product') {
            $product = $context->controller->getProduct();
            if (Validate::isLoadedObject($product)) {
                $cover = $product->getCover($product->id);
                if ($cover) {
                    $img_url = $context->link->getImageLink($product->link_rewrite, $cover['id_image'], 'large_default');
                    // Okosabb kiterjesztés csere
                    $webp_url = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $img_url);
                    
                    return "\n" . '<link rel="preload" as="image" href="'.htmlspecialchars($webp_url).'" fetchpriority="high">' . "\n";
                }
            }
        }
        return '';
    }

    /**
     * Eltávolítja a lazy load-ot és prioritást ad a hajtás feletti képeknek.
     */
    public static function fixLazyLoad($html) {
        // Töröljük a lazy load-ot az első 3 képnél
        $html = preg_replace('/(<img[^>]*?)loading\s*=\s*["\']lazy["\']([^>]*?>)/i', '$1$2', $html, 3);
        
        // Adjunk hozzá fetchpriority="high"-t az első 3 képhez (Lighthouse kedvence)
        $html = preg_replace('/<img\s+/i', '<img fetchpriority="high" ', $html, 3);
        
        return $html;
    }
    
    /**
     * Cookie Banner LCP Fix (MAXIMÁLIS SEBESSÉG Verzió)
     * Mindent késleltetünk, mert a CookieYes szervere lassú (1.2s blocking).
     */
    public static function fixCookieBanner($html) {
        if (!Configuration::get('OPT_COOKIE_FIX')) return $html;

        // 1. DIV KEZELÉS: Adat attribútum hozzáadása a CSS contain-hez
        $html = preg_replace(
            '/(<div[^>]*?class=["\'][^"\']*(?:cookie|gdpr|consent|banner)[^"\']*["\'][^>]*?)>/i', 
            '$1 data-elek-cookie="1">', 
            $html
        );

        // 2. SCRIPT KEZELÉS: MINDENT KÉSLELTETÜNK!
        // Visszatettem a cookie|consent szavakat, és hozzáadtam a 'cookieyes'-t is.
        // Így nem blokkolja a renderelést 1.2 másodpercig.
        $html = preg_replace(
            '/(<script[^>]*?src=["\'][^"\']*(?:cookie|gdpr|consent|analytics|gtm|pixel|facebook)[^"\']*["\'][^>]*?)>/i', 
            '$1 fetchpriority="low" defer>', 
            $html
        );

        return $html;
    }
	
	/**
     * SEO Link Javítás (Vue.js és Wishlist hibák)
     * 1. Kicseréli a :href="prestashop..." kódot.
     * 2. Pótolja a hiányzó href-et a wishlist gomboknál.
     */
    public static function fixSeoLinks($html) {
        
        // --- 1. LOGIN JAVÍTÁS (Ez volt eddig is) ---
        if (strpos($html, ':href="prestashop.urls.pages.authentication"') !== false) {
            $loginUrl = Context::getContext()->link->getPageLink('authentication', true);
            $html = str_replace(
                ':href="prestashop.urls.pages.authentication"',
                'href="' . $loginUrl . '"',
                $html
            );
        }

        // --- 2. ÚJ: WISHLIST JAVÍTÁS (Kívánságlista) ---
        // A hiba: <a class="wishlist-add-to-new ..."> (nincs href)
        // Javítás: <a href="#" class="wishlist-add-to-new ...">
        // Egyszerűen beszúrunk egy href="#"-t a class elé.
        if (strpos($html, 'class="wishlist-add-to-new') !== false) {
            $html = str_replace(
                'class="wishlist-add-to-new',
                'href="#" class="wishlist-add-to-new',
                $html
            );
        }

        return $html;
    }
    
    /**
     * Logo WebP csere.
     */
    public static function fixLogoWebp($html) {
        if (!Configuration::get('OPT_LOGO_WEBP')) return $html;

        $pattern = '/<img[^>]*?\bsrc\s*=\s*["\']([^"\']*?\/img\/(logo[^"\?\/\s]*)\.(?:jpg|png|jpeg))(?:\?[^"\']*)?["\']/i';

        return preg_replace_callback($pattern, function($matches) {
            $full_url = $matches[1];    
            $filename = $matches[2];    
            $img_tag = $matches[0];     

            $webp_path = _PS_IMG_DIR_ . $filename . '.webp';
            
            if (file_exists($webp_path)) {
                $webp_url = preg_replace('/\.(?:jpg|png|jpeg)/i', '.webp', $full_url);
                return str_replace($full_url, $webp_url, $img_tag);
            }
            return $img_tag;
        }, $html);
    }
    
    /**
     * Márka képek WebP cseréje az /img/m/ mappában.
     */
    public static function fixBrandWebp($html) {
        if (strpos($html, '/img/m/') === false) return $html;

        // JAVÍTOTT REGEX: Figyeli a szóközöket is (src  =  "...")
        $pattern = '/<img[^>]*?\bsrc\s*=\s*["\']([^"\']*?\/img\/m\/([^"\']*?)\.(?:jpg|png|jpeg))["\'][^>]*?>/i';

        return preg_replace_callback($pattern, function($matches) {
            $full_url = $matches[1];    
            $filename = $matches[2];    
            $img_tag = $matches[0];     

            $webp_path = _PS_MANU_IMG_DIR_ . $filename . '.webp';
            
            if (file_exists($webp_path)) {
                $webp_url = preg_replace('/\.(?:jpg|png|jpeg)/i', '.webp', $full_url);
                return str_replace($full_url, $webp_url, $img_tag);
            }
            
            return $img_tag;
        }, $html);
    }

    /**
     * SEO Címsor hierarchia javítása (H5 -> H2).
     */
    public static function fixHeadings($html) {
        if (!Configuration::get('OPT_HEADING_FIX')) return $html;
        return preg_replace('/<h5([^>]*?)>(.*?)<\/h5>/i', '<h2$1>$2</h2>', $html);
    }
}
