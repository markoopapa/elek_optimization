<?php
class ExtraHandler {
    public static function run($context) {
        $jquery = (int)Configuration::get('OPT_JQUERY_FIX');
        return '<script>var opt_jquery_active = ' . $jquery . ';</script>';
    }
}
