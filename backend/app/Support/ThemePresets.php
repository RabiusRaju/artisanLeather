<?php

namespace App\Support;

class ThemePresets
{
    // Mirrors frontend/src/context/ThemeContext.jsx — keep in sync.
    public static function all(): array
    {
        return [
            'warm-leather' => [
                'name'   => 'Warm Leather',
                'emoji'  => '🟤',
                'bg'        => '#120D05',
                'bg_card'   => '#1A1208',
                'bg_hover'  => '#3A2E1E',
                'accent'    => '#C9A84C',
                'is_light'  => false,
            ],
            'classic-black' => [
                'name'   => 'Classic Black',
                'emoji'  => '⬛',
                'bg'        => '#0A0A0A',
                'bg_card'   => '#141414',
                'bg_hover'  => '#2A2A2A',
                'accent'    => '#C9A84C',
                'is_light'  => false,
            ],
            'forest-atelier' => [
                'name'   => 'Forest Atelier',
                'emoji'  => '🌲',
                'bg'        => '#050F08',
                'bg_card'   => '#0A1510',
                'bg_hover'  => '#1A2E20',
                'accent'    => '#C9A84C',
                'is_light'  => false,
            ],
            'royal-burgundy' => [
                'name'   => 'Royal Burgundy',
                'emoji'  => '🍷',
                'bg'        => '#0F0508',
                'bg_card'   => '#15060D',
                'bg_hover'  => '#2E1020',
                'accent'    => '#C9A84C',
                'is_light'  => false,
            ],
            'midnight-navy' => [
                'name'   => 'Midnight Navy',
                'emoji'  => '🌊',
                'bg'        => '#05080F',
                'bg_card'   => '#080D1A',
                'bg_hover'  => '#101828',
                'accent'    => '#C9A84C',
                'is_light'  => false,
            ],
            'desert-dusk' => [
                'name'   => 'Desert Dusk',
                'emoji'  => '🏜️',
                'bg'        => '#1A1005',
                'bg_card'   => '#201407',
                'bg_hover'  => '#3C2810',
                'accent'    => '#C9A84C',
                'is_light'  => false,
            ],
            'daylight-white' => [
                'name'   => 'Daylight White',
                'emoji'  => '☀️',
                'bg'        => '#FAF7F2',
                'bg_card'   => '#FFFFFF',
                'bg_hover'  => '#E0D4C4',
                'accent'    => '#C9A84C',
                'is_light'  => true,
            ],
        ];
    }

    public static function get(?string $id): ?array
    {
        return static::all()[$id] ?? null;
    }
}
