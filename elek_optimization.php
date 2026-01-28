<?php
/**
 * @author    Markoo
 * @copyright 2026 Markoo
 * @license   General Public License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Elek_Optimization extends Module
{
    public function __construct()
    {
        $this->name = 'elek_optimization';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Markoo';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Universal Performance & Accessibility Optimizer');
        $this->description = $this->l('Boosts LCP, fixes jQuery errors, and improves UX accessibility.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        Configuration::updateValue('OPT_LCP_PRELOAD', true);
        Configuration::updateValue('OPT_JQUERY_FIX', true);
        Configuration::updateValue('OPT_SLICK_FIX', true);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('OPT_LCP_PRELOAD');
        Configuration::deleteByName('OPT_JQUERY_FIX');
        Configuration::deleteByName('OPT_SLICK_FIX');

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitModule')) {
            Configuration::updateValue('OPT_LCP_PRELOAD', Tools::getValue('OPT_LCP_PRELOAD'));
            Configuration::updateValue('OPT_JQUERY_FIX', Tools::getValue('OPT_JQUERY_FIX'));
            Configuration::updateValue('OPT_SLICK_FIX', Tools::getValue('OPT_SLICK_FIX'));
            $output .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = [
            'OPT_LCP_PRELOAD' => Configuration::get('OPT_LCP_PRELOAD'),
            'OPT_JQUERY_FIX' => Configuration::get('OPT_JQUERY_FIX'),
            'OPT_SLICK_FIX' => Configuration::get('OPT_SLICK_FIX'),
        ];

        $form = [
            'form' => [
                'legend' => ['title' => $this->l('Optimization Settings'), 'icon' => 'icon-cogs'],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('LCP Image Preload'),
                        'name' => 'OPT_LCP_PRELOAD',
                        'desc' => $this->l('Automatically preloads the product cover image with high priority.'),
                        'values' => [['id' => 'on', 'value' => 1], ['id' => 'off', 'value' => 0]],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('jQuery "ReferenceError" Fix'),
                        'name' => 'OPT_JQUERY_FIX',
                        'desc' => $this->l('Prevents "$ is not defined" by waiting for jQuery to load.'),
                        'values' => [['id' => 'on', 'value' => 1], ['id' => 'off', 'value' => 0]],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Slick Slider Accessibility'),
                        'name' => 'OPT_SLICK_FIX',
                        'desc' => $this->l('Corrects focus issues for hidden slides in carousels.'),
                        'values' => [['id' => 'on', 'value' => 1], ['id' => 'off', 'value' => 0]],
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];

        return $helper->generateForm([$form]);
    }

    public function hookDisplayHeader()
    {
        // LCP Preload Logic
        if (Configuration::get('OPT_LCP_PRELOAD') && $this->context->controller->php_self == 'product') {
            $product = $this->context->controller->getProduct();
            $cover = $product->getCover($product->id);
            if ($cover) {
                $img_url = $this->context->link->getImageLink($product->link_rewrite, $cover['id_image'], 'large_default');
                $this->context->controller->addPlaceholder([
                    'rel' => 'preload', 'as' => 'image', 'href' => $img_url, 'fetchpriority' => 'high'
                ]);
            }
        }

        $this->context->controller->addJS($this->_path . 'views/js/front.js');
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');

        if (Configuration::get('OPT_JQUERY_FIX')) {
            return '<script>var opt_jquery_active = true;</script>';
        }
    }
}
