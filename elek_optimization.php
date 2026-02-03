<?php
/**
 * @author    Markoo
 * @copyright 2026 Markoo
 * @license   All rights reserved
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Szükséges fájlok behívása
require_once(dirname(__FILE__) . '/src/PerformanceHandler.php');
require_once(dirname(__FILE__) . '/src/AccessibilityHandler.php');
require_once(dirname(__FILE__) . '/src/ExtraHandler.php');

class Elek_Optimization extends Module
{
    public $cfg_keys;

    public function __construct()
    {
        $this->name = 'elek_optimization';
        $this->tab = 'front_office_features';
        $this->version = '2.8.0';
        $this->author = 'Markoo';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Universal Performance & Accessibility Optimizer PRO');
        $this->description = $this->l('A legteljesebb megoldás Google PageSpeed, SEO és WebP optimalizáláshoz.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->cfg_keys = array(
            'OPT_LCP_PRELOAD',
            'OPT_COOKIE_FIX',
            'OPT_LOGO_WEBP',
            'OPT_BRAND_WEBP',
            'OPT_HEADING_FIX',
            'OPT_SLICK_FIX',
            'OPT_EMPTY_CART',
            'OPT_LIST_QTY',
			'OPT_CHECKOUT_SELECTOR'
        );
    }

    public function install()
{
    foreach ($this->cfg_keys as $key) {
        Configuration::updateValue($key, 0);
    }

    return parent::install() &&
        $this->registerHook('actionOutputHTMLBefore') &&
        $this->registerHook('displayHeader');
}

    public function uninstall()
    {
        foreach ($this->cfg_keys as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_elek_opt')) {
            foreach ($this->cfg_keys as $key) {
                Configuration::updateValue($key, (int)Tools::getValue($key));
            }
            $output .= $this->displayConfirmation($this->l('Minden beállítás sikeresen mentve és alkalmazva!'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Optimalizálási Központ'),
                    'icon' => 'icon-dashboard'
                ),
                'input' => array(
                    // --- PERFORMANCE SZEKCIÓ (Kék doboz) ---
                    array(
                        'type' => 'html',
                        'name' => 'perf_info',
                        'html_content' => '
                        <div style="background: #f8fcfd; border-left: 5px solid #25b9d7; padding: 15px 20px; margin-bottom: 20px; border-radius: 0 6px 6px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center;">
                            <div style="font-size: 28px; margin-right: 15px; color: #25b9d7;"><i class="icon-bolt"></i></div>
                            <div>
                                <h4 style="margin: 0; font-weight: bold; color: #25b9d7; text-transform: uppercase; letter-spacing: 1px;">Sebesség optimalizálás</h4>
                                <p style="margin: 0; color: #6c868e; font-size: 13px;">Gyorsítsa fel az oldalbetöltést modern LCP és WebP technológiákkal.</p>
                            </div>
                        </div>'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('LCP Kép Előtöltése'),
                        'name' => 'OPT_LCP_PRELOAD',
                        'is_bool' => true,
                        'desc' => $this->l('A legnagyobb látható kép (LCP) kiemelt prioritást kap.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Cookie Banner LCP Fix'),
                        'name' => 'OPT_COOKIE_FIX',
                        'is_bool' => true,
                        'desc' => $this->l('Hátrasorolja a süti sávot a mérésekben.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Fő Logó WebP csere'),
                        'name' => 'OPT_LOGO_WEBP',
                        'is_bool' => true,
                        'desc' => $this->l('Lecseréli a fő logót WebP formátumra az /img/ mappában.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Márka Logók WebP csere'),
                        'name' => 'OPT_BRAND_WEBP',
                        'is_bool' => true,
                        'desc' => $this->l('Lecseréli a gyártók logóit WebP-re az /img/m/ mappában.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),

                    // --- SEO & ACCESSIBILITY (Zöld doboz) ---
                    array(
                        'type' => 'html',
                        'name' => 'seo_info',
                        'html_content' => '
                        <div style="background: #f9fdf8; border-left: 5px solid #78be20; padding: 15px 20px; margin-bottom: 20px; border-top-right-radius: 6px; border-bottom-right-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center;">
                            <div style="font-size: 28px; margin-right: 15px; color: #78be20;"><i class="icon-search"></i></div>
                            <div>
                                <h4 style="margin: 0; font-weight: bold; color: #78be20; text-transform: uppercase; letter-spacing: 1px;">SEO és Akadálymentesség</h4>
                                <p style="margin: 0; color: #6c868e; font-size: 13px;">Javítsa a keresőmotorok rangsorolását és a felhasználói élményt.</p>
                            </div>
                        </div>'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Címsor Fix (H5 -> H2)'),
                        'name' => 'OPT_HEADING_FIX',
                        'is_bool' => true,
                        'desc' => $this->l('Javítja a címsor hierarchiát a termékoldalakon.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Slick Slider SEO Fix'),
                        'name' => 'OPT_SLICK_FIX',
                        'is_bool' => true,
                        'desc' => $this->l('Javítja a csúszkák (sliderek) kódját.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),

                    // --- EXTRA (Narancs doboz) ---
                    array(
                        'type' => 'html',
                        'name' => 'extra_info',
                        'html_content' => '
                        <div style="background: #fdfaf8; border-left: 5px solid #f39d12; padding: 15px 20px; margin-bottom: 20px; border-top-right-radius: 6px; border-bottom-right-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center;">
                            <div style="font-size: 28px; margin-right: 15px; color: #f39d12;"><i class="icon-star"></i></div>
                            <div>
                                <h4 style="margin: 0; font-weight: bold; color: #f39d12; text-transform: uppercase; letter-spacing: 1px;">Extrák</h4>
                                <p style="margin: 0; color: #6c868e; font-size: 13px;">Kiegészítő funkciók a vásárlási folyamat megkönnyítéséhez.</p>
                            </div>
                        </div>'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Kosár ürítése gomb'),
                        'name' => 'OPT_EMPTY_CART',
                        'is_bool' => true,
                        'desc' => $this->l('Egy feltűnő ürítő gombot helyez el a Sidebarban.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Mennyiségválasztó a listában'),
                        'name' => 'OPT_LIST_QTY',
                        'is_bool' => true,
                        'desc' => $this->l('Mennyiség módosítása közvetlenül a terméklistákban.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Checkout Cégválasztó (2. lépés)'),
                        'name' => 'OPT_CHECKOUT_SELECTOR',
                        'is_bool' => true,
                        'desc' => $this->l('Bekapcsolja a Magánszemély/Cég választót a Címek megadásánál.'),
                        'values' => array(
                            array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Mentés'),
                    'class' => 'btn btn-primary pull-right'
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_callbacks = true;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_elek_opt';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }
	
	public function getConfigFormValues()
    {
        $v = array();
        foreach ($this->cfg_keys as $k) {
            $v[$k] = Configuration::get($k);
        }
        return $v;
    }
	
	public function hookDisplayHeader()
    {
        // 1. KOSÁR ÜRÍTÉSE (A korábbi hookHeader logikája)
        if ((int)Configuration::get('OPT_EMPTY_CART') && Tools::getValue('empty_cart') == 1) {
            if (isset($this->context->cart) && $this->context->cart->id) {
                $this->context->cart->delete();
                $this->context->cookie->id_cart = 0;
                unset($this->context->cookie->id_cart);
                $this->context->cookie->write();
            }
            // Visszairányítás
            $back = isset($_SERVER['HTTP_REFERER']) ? strtok($_SERVER['HTTP_REFERER'], '?') : $this->context->link->getPageLink('index');
            Tools::redirect($back);
        }

        // 2. KÜLSŐ FÁJLOK BEHÍVÁSA (front.css és front.js)
        $this->context->controller->addJS($this->_path . 'views/js/front.js');
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');

        // 3. ADATOK ÁTADÁSA A JAVASCRIPTNEK
        Media::addJsDef(array(
            'elek_cfg' => array(
                'heading_fix' => (int)Configuration::get('OPT_HEADING_FIX'),
                'empty_cart'  => (int)Configuration::get('OPT_EMPTY_CART'),
                'list_qty'    => (int)Configuration::get('OPT_LIST_QTY'),
				'checkout_selector' => (int)Configuration::get('OPT_CHECKOUT_SELECTOR'),
                'empty_url'   => $this->context->link->getPageLink('cart', null, null, array('empty_cart' => 1))
            ),
            'elek_t' => array(
                'qtyAria'    => $this->l('Quantity selection'),
                'ok'         => $this->l('I understand'),
                'yes'        => $this->l('Yes'),
                'no'         => $this->l('Cancel'),
                'stockLabel' => $this->l('%s in stock'),
                'msgTotalStock' => $this->l('%s items in stock total'),
                'msgInCart'     => $this->l('(of which %s are already in your cart)'),
                'msgNoStock'    => $this->l('Not enough stock'),
                'emptyBtn'   => $this->l('EMPTY CART'),
                'emptyQ'     => $this->l('Do you want to empty your cart?'),
                'fizica'     => $this->l('Natural Person'),
                'juridica'   => $this->l('Legal Person'),
                'clientType' => $this->l('Customer Type')
            )
        ));
    }
	
	public function hookActionOutputHTMLBefore(&$params)
{
    // 1. Konfiguráció lekérése
    $conf = array(); 
    foreach ($this->cfg_keys as $k) { 
        $conf[$k] = (int)Configuration::get($k); 
    }
    
    // Ha semmi nincs bekapcsolva, ne fusson feleslegesen
    if (!array_filter($conf)) return;

    // 2. Oldal ellenőrzése
    $page = isset($this->context->controller->php_self) ? $this->context->controller->php_self : '';
    $allowed = array('product', 'index', 'category', 'search', 'prices-drop', 'new-products', 'best-sales', 'cart', 'order');
    if (!in_array($page, $allowed)) return;

    // 3. HTML UTÓMUNKA (PerformanceHandler)
    // Itt történnek a SEO és sebesség javítások közvetlenül a HTML-kódon
    if (class_exists('PerformanceHandler')) {
        if ($conf['OPT_HEADING_FIX']) $params['html'] = PerformanceHandler::fixHeadings($params['html']);
        if ($conf['OPT_LCP_PRELOAD']) $params['html'] = PerformanceHandler::fixLazyLoad($params['html']);
        if ($conf['OPT_COOKIE_FIX']) $params['html'] = PerformanceHandler::fixCookieBanner($params['html']);
        if ($conf['OPT_LOGO_WEBP']) $params['html'] = PerformanceHandler::fixLogoWebp($params['html']);
        if ($conf['OPT_BRAND_WEBP']) $params['html'] = PerformanceHandler::fixBrandWebp($params['html']);
    }
}
}
