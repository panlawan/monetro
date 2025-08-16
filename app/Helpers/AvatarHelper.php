<?php
// app/Helpers/AvatarHelper.php

namespace App\Helpers;

class AvatarHelper
{
    /**
     * Generate SVG avatar from initials.
     *
     * @param string $name            Full name to extract initials from
     * @param int    $size            Width and height of the resulting image
     * @param string $backgroundColor Hex color for the background (without '#')
     * @param string $textColor       Hex color for the text (without '#')
     *
     * @return string Base64 encoded SVG data URI
     */
    public static function generateSvgAvatar(string $name, int $size = 200, string $backgroundColor = '6366f1', string $textColor = 'ffffff'): string
    {
        $initials = self::getInitials($name);
        
        $svg = "
        <svg width='{$size}' height='{$size}' xmlns='http://www.w3.org/2000/svg'>
            <rect width='100%' height='100%' fill='#{$backgroundColor}'/>
            <text x='50%' y='50%' font-family='Arial, sans-serif' font-size='" . ($size * 0.4) . "' 
                  fill='#{$textColor}' text-anchor='middle' dominant-baseline='middle' font-weight='bold'>
                {$initials}
            </text>
        </svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Get initials from name.
     *
     * @param string $name Full name to extract initials from
     *
     * @return string Two-character initials or 'U' if none found
     */
    private static function getInitials(string $name): string
    {
        $words     = explode(' ', trim($name));
        $initials  = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
                if (strlen($initials) >= 2) {
                    break;
                }
            }
        }

        return $initials ?: 'U';
    }
    
    /**
     * Generate color from name (consistent color for same name).
     *
     * @param string $name Name to hash for color selection
     *
     * @return string Hex color string (without '#')
     */
    public static function getColorFromName(string $name): string
    {
        $colors = [
            '6366f1', // indigo
            'ef4444', // red
            'f59e0b', // amber
            '10b981', // emerald
            '3b82f6', // blue
            '8b5cf6', // violet
            'f97316', // orange
            '06b6d4', // cyan
            'ec4899', // pink
            '84cc16', // lime
        ];

        $hash = crc32($name);
        return $colors[abs($hash) % count($colors)];
    }
}
