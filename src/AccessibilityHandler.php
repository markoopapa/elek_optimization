<?php
/**
 * Elek Optimization - Accessibility Handler
 */

class AccessibilityHandler
{
    /**
     * Javítja a Slick Slider és egyéb elemek akadálymentességi hibáit
     */
    public static function fixSlickLabels($html)
    {
        // Megkeressük a névtelen Slick listákat és elnevezzük őket
        if (strpos($html, 'role="listbox"') !== false) {
            $html = str_replace(
                'role="listbox"', 
                'role="listbox" aria-label="Termék lista"', 
                $html
            );
        }

        // Itt a későbbiekben más accessibility fixeket is hozzáadhatunk
        // Pl. hiányzó alt tagek pótlása, gombok elnevezése stb.

        return $html;
    }
}
