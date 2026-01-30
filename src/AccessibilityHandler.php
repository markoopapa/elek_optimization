<?php
class AccessibilityHandler {
    public static function run($context) {
        $heading = (int)Configuration::get('OPT_HEADING_FIX');
        $slick = (int)Configuration::get('OPT_SLICK_FIX');

        return '<script>
            var opt_heading_fix = ' . $heading . ';
            var opt_slick_fix = ' . $slick . ';
        </script>';
    }
}
