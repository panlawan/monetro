<?php
// app/Helpers/AvatarHelper.php

namespace App\Helpers;

class AvatarHelper
{
    /**
     * Generate SVG avatar from initials
     */
    public static function generateSvgAvatar($name, $size = 200, $backgroundColor = '6366f1', $textColor = 'ffffff')
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
     * Get initials from name
     */
    private static function getInitials($name)
    {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
                if (strlen($initials) >= 2) break;
            }
        }
        
        return $initials ?: 'U';
    }
    
    /**
     * Generate color from name (consistent color for same name)
     */
    public static function getColorFromName($name)
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