<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\EnsuresSuperAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgePageSettingRequest;
use App\Models\KnowledgePageSetting;
use App\Services\KnowledgePageAssetStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class KnowledgePageSettingController extends Controller
{
    use EnsuresSuperAdmin;

    private const UPLOADS = [
        'site_logo' => ['column' => 'site_logo_path', 'slot' => 'logo'],
        'hero_image' => ['column' => 'hero_image_path', 'slot' => 'hero'],
        'about_image' => ['column' => 'about_image_path', 'slot' => 'about'],
        'footer_logo' => ['column' => 'footer_logo_path', 'slot' => 'footer-logo'],
    ];

    public function edit(): View
    {
        $this->ensureSuperAdmin();

        return view('superadmin.knowledge-page.edit', [
            'settings' => KnowledgePageSetting::current(),
        ]);
    }

    public function update(
        KnowledgePageSettingRequest $request,
        KnowledgePageAssetStorage $assets
    ): RedirectResponse {
        $this->ensureSuperAdmin();
        $validated = $request->validated();
        $newPaths = [];
        $oldPaths = [];

        try {
            foreach (self::UPLOADS as $input => $definition) {
                if ($request->hasFile($input)) {
                    $newPaths[$definition['column']] = $assets->store(
                        $request->file($input), $definition['slot']
                    );
                }
            }

            DB::transaction(function () use ($request, $validated, $newPaths, &$oldPaths): void {
                $settings = KnowledgePageSetting::query()->first() ?? new KnowledgePageSetting;
                $data = Arr::except($validated, array_merge(
                    array_keys(self::UPLOADS),
                    array_map(fn (string $key) => "remove_{$key}", array_keys(self::UPLOADS))
                ));
                foreach (self::UPLOADS as $input => $definition) {
                    $column = $definition['column'];
                    if (isset($newPaths[$column])) {
                        $oldPaths[] = $settings->{$column};
                        $data[$column] = $newPaths[$column];
                    } elseif ($request->boolean("remove_{$input}")) {
                        $oldPaths[] = $settings->{$column};
                        $data[$column] = null;
                    }
                }
                $settings->fill($data)->save();
            });
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $assets->delete($path);
            }
            throw $exception;
        }

        foreach (array_filter($oldPaths) as $path) {
            $assets->delete($path);
        }

        return back()->with('success', 'บันทึกการตั้งค่าหน้า E-Book KM แล้ว');
    }
}
