<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    // GET /api/v1/settings — only expose safe public settings
    public function index()
    {
        $settings = Cache::remember('public_settings', 3600, function () {
            return Setting::whereIn('group', ['business', 'social', 'website', 'seo', 'orders'])
                ->pluck('value', 'key')
                ->toArray();
        });

        // Never expose internal/admin-only settings
        $safe = [
            'business.name'             => $settings['business.name'] ?? 'Artisan Leather',
            'business.tagline'          => $settings['business.tagline'] ?? '',
            'business.email'            => $settings['business.email'] ?? '',
            'business.phone'            => $settings['business.phone'] ?? '',
            'business.whatsapp'         => $settings['business.whatsapp'] ?? '',
            'business.address'          => $settings['business.address'] ?? '',
            'business.city'             => $settings['business.city'] ?? 'Muscat',
            'social.instagram'          => $settings['social.instagram'] ?? '',
            'social.facebook'           => $settings['social.facebook'] ?? '',
            'social.tiktok'             => $settings['social.tiktok'] ?? '',
            'social.twitter'            => $settings['social.twitter'] ?? '',
            'website.url'               => $settings['website.url'] ?? 'https://artisanleatherom.com',
            'website.support_email'     => $settings['website.support_email'] ?? '',
            'orders.free_delivery_threshold' => $settings['orders.free_delivery_threshold'] ?? '0',
            'orders.whatsapp_message'   => $settings['orders.whatsapp_message'] ?? '',
            'seo.meta_title'            => $settings['seo.meta_title'] ?? '',
            'seo.meta_description'      => $settings['seo.meta_description'] ?? '',
            'seo.google_analytics'      => $settings['seo.google_analytics'] ?? '',
            'seo.google_tag_manager'    => $settings['seo.google_tag_manager'] ?? '',
        ];

        return response()->json(['data' => $safe]);
    }
}
