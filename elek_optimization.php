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
                'title' => $this->l('Optimalizálási Központ & Vezérlőpult'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                // --- 0. STATUS INFO + CSS FIX (ITT A TRÜKK!) ---
                array(
                    'type' => 'html',
                    'name' => 'global_info',
                    'html_content' => '
                    <style>
                        /* Ez a CSS felülírja a PrestaShop közpre igazítását */
                        .elek-admin-box { text-align: left !important; justify-content: flex-start !important; }
                        .elek-admin-box h3, .elek-admin-box h4, .elek-admin-box p, .elek-admin-box ul, .elek-admin-box li, .elek-admin-box div { 
                            text-align: left !important; 
                        }
                        /* Ikonok igazítása */
                        .elek-icon-container { margin-right: 15px !important; }
                    </style>

                    <div class="elek-admin-box" style="background: #fff; border: 1px solid #dce1ef; border-left: 5px solid #2ecca7; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-radius: 4px; text-align: left !important;">
                        <h3 style="margin-top: 0; color: #222; display: flex; align-items: center; justify-content: flex-start;">
                            <i class="icon-heartbeat" style="font-size: 24px; color: #2ecca7; margin-right: 10px;"></i> 
                            A Rendszer Aktív és Figyel
                        </h3>
                        <p style="font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 15px;">
                            A modul jelenleg beépült a webshop motorjába. Az alábbi kapcsolókkal a látható funkciókat vezérelheti, de az alaprendszer folyamatosan fut.
                        </p>
                        
                        <div style="background: #f4fdfb; padding: 15px; border-radius: 4px; border: 1px solid #cbf0e8;">
                            <strong style="color: #0c8a73; display:block; margin-bottom:5px;">AUTOMATIKUS HÁTTÉRFOLYAMATOK:</strong>
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px; color: #444;">
                                <li style="margin-bottom: 5px;"><i class="icon-check" style="color:#2ecca7"></i> <strong>Hook Integráció:</strong> A modul automatikusan csatlakozik a Header és Footer pontokhoz.</li>
                                <li style="margin-bottom: 5px;"><i class="icon-check" style="color:#2ecca7"></i> <strong>Globális Változók:</strong> Injektálja a szükséges JS konfigurációkat.</li>
                                <li style="margin-bottom: 5px;"><i class="icon-check" style="color:#2ecca7"></i> <strong>Ajax Figyelés:</strong> A háttérben figyeli a kosár eseményeit.</li>
                            </ul>
                        </div>
                    </div>'
                ),

                // --- 1. PERFORMANCE SZEKCIÓ (BALRA IGAZÍTVA) ---
                array(
                    'type' => 'html',
                    'name' => 'perf_info',
                    'html_content' => '
                    <div class="elek-admin-box" style="background: #f8fcfd; border-left: 5px solid #25b9d7; padding: 15px 20px; margin-bottom: 20px; border-radius: 0 6px 6px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: flex-start; text-align: left !important;">
                        <div class="elek-icon-container" style="font-size: 28px; color: #25b9d7;"><i class="icon-bolt"></i></div>
                        <div>
                            <h4 style="margin: 0; font-weight: bold; color: #25b9d7; text-transform: uppercase; letter-spacing: 1px;">Sebesség optimalizálás</h4>
                            <p style="margin: 0; color: #6c868e; font-size: 13px;">Core Web Vitals mutatók javítása és erőforrás kezelés.</p>
                        </div>
                    </div>'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('LCP Kép Előtöltése'),
                    'name' => 'OPT_LCP_PRELOAD',
                    'is_bool' => true,
                    'desc' => $this->l('Prioritást ad a fő termékkép betöltésének (Preload), javítva a Google sebesség pontszámot.'),
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
                    'desc' => $this->l('Késlelteti/Hátrasorolja a süti sávot, hogy ne rontsa az oldalbetöltési mutatókat (CLS).'),
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
                    'desc' => $this->l('Ha létezik, automatikusan WebP formátumra cseréli a bolt logóját.'),
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
                    'desc' => $this->l('A gyártók logóit (Brand) modern, tömörített WebP formátumban tölti be.'),
                    'values' => array(
                        array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                        array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                    ),
                ),

                // --- 2. SEO & ACCESSIBILITY (BALRA IGAZÍTVA) ---
                array(
                    'type' => 'html',
                    'name' => 'seo_info',
                    'html_content' => '
                    <div class="elek-admin-box" style="background: #f9fdf8; border-left: 5px solid #78be20; padding: 15px 20px; margin-bottom: 20px; border-top-right-radius: 6px; border-bottom-right-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: flex-start; text-align: left !important;">
                        <div class="elek-icon-container" style="font-size: 28px; color: #78be20;"><i class="icon-search"></i></div>
                        <div>
                            <h4 style="margin: 0; font-weight: bold; color: #78be20; text-transform: uppercase; letter-spacing: 1px;">SEO és Akadálymentesség</h4>
                            <p style="margin: 0; color: #6c868e; font-size: 13px;">Keresőoptimalizálás és HTML struktúra javítás.</p>
                        </div>
                    </div>'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Címsor Fix (H5 -> H2)'),
                    'name' => 'OPT_HEADING_FIX',
                    'is_bool' => true,
                    'desc' => $this->l('A termékneveket H5-ről H2-re cseréli a jobb SEO hierarchia érdekében.'),
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
                    'desc' => $this->l('Pótolja a hiányzó ARIA címkéket a csúszkáknál (Slider), javítva a Google Lighthouse pontszámot.'),
                    'values' => array(
                        array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                        array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                    ),
                ),

                // --- 3. EXTRÁK (BALRA IGAZÍTVA) ---
                array(
                    'type' => 'html',
                    'name' => 'extra_info',
                    'html_content' => '
                    <div class="elek-admin-box" style="background: #fdfaf8; border-left: 5px solid #f39d12; padding: 15px 20px; margin-bottom: 20px; border-top-right-radius: 6px; border-bottom-right-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: flex-start; text-align: left !important;">
                        <div class="elek-icon-container" style="font-size: 28px; color: #f39d12;"><i class="icon-star"></i></div>
                        <div>
                            <h4 style="margin: 0; font-weight: bold; color: #f39d12; text-transform: uppercase; letter-spacing: 1px;">Extrák & Funkciók</h4>
                            <p style="margin: 0; color: #6c868e; font-size: 13px;">Felhasználói élmény (UX) és konverzió növelés.</p>
                        </div>
                    </div>'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Kosár ürítése gomb'),
                    'name' => 'OPT_EMPTY_CART',
                    'is_bool' => true,
                    'desc' => $this->l('Sidebarba helyez egy gombot a kosár teljes törléséhez. [Cache-Buster]: Azonnali törlést garantál, kikerülve a gyorsítótárat.'),
                    'values' => array(
                        array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                        array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Mennyiségválasztó (+/-)'),
                    'name' => 'OPT_LIST_QTY',
                    'is_bool' => true,
                    'desc' => $this->l('Megjeleníti a +/- gombokat a terméklistákban. [Native Mode]: A PrestaShop eredeti kosár-motorját használja a maximális kompatibilitásért.'),
                    'values' => array(
                        array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                        array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Checkout Cégválasztó'),
                    'name' => 'OPT_CHECKOUT_SELECTOR',
                    'is_bool' => true,
                    'desc' => $this->l('Modern választó (Magán/Cég) a Pénztárban. Magánszemély esetén automatikusan elrejti a felesleges (Cég, Adószám, Gender) mezőket.'),
                    'values' => array(
                        array('id' => 'on', 'value' => 1, 'label' => $this->l('Enabled')),
                        array('id' => 'off', 'value' => 0, 'label' => $this->l('Disabled'))
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Beállítások Mentése'),
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
		
		// --- ÚJ RÉSZ: CSS PRELOAD (Kapcsolóhoz kötve) ---
        // Csak akkor fut, ha az "LCP Kép Előtöltése" be van kapcsolva az adminban
        if (Configuration::get('OPT_LCP_PRELOAD') && ($this->context->controller->php_self == 'product' || $this->context->controller->php_self == 'index')) {
            if (isset($this->context->controller->css_files) && is_array($this->context->controller->css_files)) {
                foreach ($this->context->controller->css_files as $css_uri => $media) {
                    // Megkeressük a fő téma fájlt
                    if (strpos($css_uri, 'theme') !== false) {
                         $this->context->controller->registerJavascript('elek-css-preload', '', ['server' => 'remote', 'content' => '<link rel="preload" href="'.$css_uri.'" as="style">']);
                         break; 
                    }
                }
            }
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
				'slick_fix'   => (int)Configuration::get('OPT_SLICK_FIX'),
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
		$params['html'] = PerformanceHandler::fixSeoLinks($params['html']);
        if ($conf['OPT_LOGO_WEBP']) $params['html'] = PerformanceHandler::fixLogoWebp($params['html']);
        if ($conf['OPT_BRAND_WEBP']) $params['html'] = PerformanceHandler::fixBrandWebp($params['html']);
    }
}
}
