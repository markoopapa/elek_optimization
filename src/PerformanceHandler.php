<?php
class PerformanceHandler {
    public static function run($context) {
        if (!Configuration::get('OPT_LCP_PRELOAD')) return '';

        if ($context->controller->php_self == 'product') {
            $product = $context->controller->getProduct();
            if (Validate::isLoadedObject($product)) {
                $cover = $product->getCover($product->id);
                if ($cover) {
                    $img_url = $context->link->getImageLink($product->link_rewrite, $cover['id_image'], 'large_default');
                    return '<link rel="preload" as="image" href="'.$img_url.'" fetchpriority="high">';
                }
            }
        }
        return '';
    }
}
