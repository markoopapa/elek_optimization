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
     * Hátrasorolja a süti sávot és egyéb blokkoló elemeket.
     */
    public static function fixCookieBanner($html) {
        if (!Configuration::get('OPT_COOKIE_FIX')) return $html;

        // Konténer optimalizálása CSS contain használatával
        $html = preg_replace(
            '/(<div[^>]*?class=["\'][^"\']*(?:cookie|gdpr|consent|banner)[^"\']*["\'][^>]*?)>/i', 
            '$1 style="content-visibility: auto; contain: layout style;">', 
            $html
        );

        // Scriptek alacsony prioritásra állítása
        $html = preg_replace(
            '/(<script[^>]*?(?:cookie|gdpr|consent|analytics)[^>]*?)>/i', 
            '$1 fetchpriority="low" defer>', 
            $html
        );

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
