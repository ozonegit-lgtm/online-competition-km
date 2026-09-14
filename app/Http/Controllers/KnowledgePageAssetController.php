<?php

namespace App\Http\Controllers;

use App\Models\KnowledgePageSetting;
use App\Services\KnowledgePageAssetStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KnowledgePageAssetController extends Controller
{
    public function show(string $asset, KnowledgePageAssetStorage $assets): StreamedResponse
    {
        abort_unless(array_key_exists($asset, KnowledgePageSetting::ASSET_COLUMNS), 404);
        $settings = KnowledgePageSetting::current();
        $enabled = match ($asset) {
            'hero' => $settings->hero_enabled,
            'about' => $settings->about_enabled,
            'footer-logo' => $settings->footer_enabled && $settings->footer_show_logo,
            default => true,
        };
        $canPreviewDisabledAsset = Auth::user()?->is_active === true
            && Auth::user()?->role?->role_name === 'Super Admin';
        abort_unless($enabled || $canPreviewDisabledAsset, 404);

        $column = KnowledgePageSetting::ASSET_COLUMNS[$asset];
        $path = $assets->managedPath($settings->{$column}, $asset);
        $disk = Storage::disk('local');
        abort_unless($path && $disk->exists($path), 404);

        $mime = $disk->mimeType($path);
        abort_unless(is_string($mime) && str_starts_with($mime, 'image/'), 404);

        return $disk->response($path, null, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
