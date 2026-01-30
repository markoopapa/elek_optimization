<?php
/**
 * @author    Markoo
 * @copyright 2026 Markoo
 */

if (!defined('_PS_VERSION_')) exit;

// Beosztottak behívása
require_once(dirname(__FILE__) . '/src/PerformanceHandler.php');
require_once(dirname(__FILE__) . '/src/AccessibilityHandler.php');
require_once(dirname(__FILE__) . '/src/ExtraHandler.php');

class Elek_Optimization extends Module {
    public function __construct() {
        $this->name = 'elek_optimization';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->author = 'Markoo';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Universal Performance & Accessibility Optimizer');
        $this->description = $this->l('Modular optimization framework for any PrestaShop store.');
    }

    public function install() {
        // 1. Telepítéskor MINDEN KI VAN KAPCSOLVA (0)
        Configuration::updateValue('OPT_LCP_PRELOAD', 0);
        Configuration::updateValue('OPT_HEADING_FIX', 0);
        Configuration::updateValue('OPT_SLICK_FIX', 0);
        Configuration::updateValue('OPT_JQUERY_FIX', 0);
        return parent::install() && $this->registerHook('displayHeader');
    }

    public function getContent() {
        $output = '';
        if (Tools::isSubmit('submit_opt')) {
            // Mentés gomb megnyomása után aktiváljuk/frissítjük a beállításokat
            Configuration::updateValue('OPT_LCP_PRELOAD', (int)Tools::getValue('OPT_LCP_PRELOAD'));
            Configuration::updateValue('OPT_HEADING_FIX', (int)Tools::getValue('OPT_HEADING_FIX'));
            Configuration::updateValue('OPT_SLICK_FIX', (int)Tools::getValue('OPT_SLICK_FIX'));
            Configuration::updateValue('OPT_JQUERY_FIX', (int)Tools::getValue('OPT_JQUERY_FIX'));
            $output .= $this->displayConfirmation($this->l('Settings saved and applied!'));
        }
        return $output . $this->renderForm();
    }

    protected function renderForm() {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->submit_action = 'submit_opt';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value = [
            'OPT_LCP_PRELOAD' => Configuration::get('OPT_LCP_PRELOAD'),
            'OPT_HEADING_FIX' => Configuration::get('OPT_HEADING_FIX'),
            'OPT_SLICK_FIX' => Configuration::get('OPT_SLICK_FIX'),
            'OPT_JQUERY_FIX' => Configuration::get('OPT_JQUERY_FIX'),
        ];

        // 2. DASHBOARD FELBONTÁSA SZEKCIÓKRA
        $form = [
            'form' => [
                'legend' => ['title' => $this->l('Optimization Dashboard'), 'icon' => 'icon-dashboard'],
                'input' => [
                    // --- PERFORMANCE ---
                    ['type' => 'html', 'name' => 'header_perf', 'html_content' => '<h4><i class="icon-bolt"></i> Performance</h4><hr>'],
                    ['type' => 'switch', 'label' => 'LCP Image Preload', 'name' => 'OPT_LCP_PRELOAD', 'is_bool' => true, 'values' => [['id'=>'on','value'=>1],['id'=>'off','value'=>0]], 'desc' => 'Prioritizes main product image for faster loading.'],
                    
                    // --- ACCESSIBILITY ---
                    ['type' => 'html', 'name' => 'header_acc', 'html_content' => '<br><h4><i class="icon-user"></i> Accessibility</h4><hr>'],
                    ['type' => 'switch', 'label' => 'Heading Fix (h5->h2)', 'name' => 'OPT_HEADING_FIX', 'is_bool' => true, 'values' => [['id'=>'on','value'=>1],['id'=>'off','value'=>0]], 'desc' => 'Fixes SEO heading structure.'],
                    ['type' => 'switch', 'label' => 'Slick Slider Fix', 'name' => 'OPT_SLICK_FIX', 'is_bool' => true, 'values' => [['id'=>'on','value'=>1],['id'=>'off','value'=>0]], 'desc' => 'Improves carousel keyboard navigation.'],
                    
                    // --- EXTRA ---
                    ['type' => 'html', 'name' => 'header_extra', 'html_content' => '<br><h4><i class="icon-plus"></i> Extra</h4><hr>'],
                    ['type' => 'switch', 'label' => 'jQuery Safety Fix', 'name' => 'OPT_JQUERY_FIX', 'is_bool' => true, 'values' => [['id'=>'on','value'=>1],['id'=>'off','value'=>0]], 'desc' => 'Prevents "$ is not defined" console errors.'],
                ],
                'submit' => ['title' => $this->l('Save Settings'), 'class' => 'btn btn-primary pull-right']
            ]
        ];
        return $helper->generateForm([$form]);
    }

    public function hookDisplayHeader() {
        $html = '';
        // A Főnök megkérdezi a beosztottakat, kell-e csinálniuk valamit
        $html .= PerformanceHandler::run($this->context);
        $html .= AccessibilityHandler::run($this->context);
        $html .= ExtraHandler::run($this->context);

        // Alap fájlok behúzása
        $this->context->controller->addJS($this->_path . 'views/js/front.js');
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');

        return $html;
    }
}
