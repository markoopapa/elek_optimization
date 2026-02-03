<?php
class ExtraHandler {
    public static function handleEmptyCart($html, $context) {
        // 1. TÖRLÉSI LOGIKA: Bármelyik oldalon futhat
        if (Tools::getValue('empty_cart') == 1) {
            $context->cart->delete();
            $context->cookie->write();
            // Visszairányítunk oda, ahol voltunk, de paraméter nélkül
            Tools::redirect($context->link->getPageLink($context->controller->php_self));
        }
        return $html;
    }
}
