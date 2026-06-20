<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;

class LegalPageController extends Controller
{
    // GET /api/v1/legal/{slug}
    public function show(string $slug)
    {
        $page = LegalPage::where('slug', $slug)->first();

        if (! $page) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json([
            'data' => [
                'slug'         => $page->slug,
                'title'        => $page->title,
                'title_ar'     => $page->title_ar,
                'last_updated' => $page->last_updated,
                'sections'     => array_map(fn ($s) => [
                    'heading'    => $s['heading']    ?? '',
                    'heading_ar' => $s['heading_ar'] ?? '',
                    'body'       => $s['body']        ?? '',
                    'body_ar'    => $s['body_ar']     ?? '',
                ], $page->sections ?? []),
            ],
        ]);
    }
}
