<?php
    namespace App\Http\Controllers\SuperAdmin;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Str;
    use App\Models\CompetitionTemplate;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;
    use Illuminate\Support\Facades\Storage;
    use App\Models\CompetitionTemplateFormField;
    use Throwable;

    class CompetitionTemplateController extends Controller
    {
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
            $competitionTemplates = CompetitionTemplate::withCount('formFields')->latest()->paginate(9);
            return view('superadmin.templates.index', compact('competitionTemplates'));
        }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $competitionTemplates = CompetitionTemplate::paginate(10);
        return view('superadmin.templates.create',compact('competitionTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'template_name' => ['required', 'string', 'max:255'],
            'template_slug' => ['nullable', 'string', 'max:255', 'unique:competition_templates,template_slug'],
            'default_description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validate['template_slug'] = Str::slug(
            $validate['template_slug'] ?: $validate['template_name']
        );

        $validate['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('cover_image')) {
            $validate['cover_image'] = $request->file('cover_image')->store('competition-templates', 'public');
        }
        

        $template = CompetitionTemplate::create($validate);

        return redirect()->route('superadmin.templates.form-fields.create', ['template' => $template->id])->with('success','สร้าง Template สำเร็จ กรุณาสร้างช่องกรอกข้อมูลต่อ');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTemplate $template)
    {
        $template->load(['formFields' => function ($query) {$query->orderBy('sort_order');}]);
        return view('superadmin.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionTemplate $template)
        {
            $template->load('formFields');
            return view('superadmin.templates.edit', compact('template'));
        }

    /**
     * Update the specified resource in storage.
     */
        /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionTemplate $template)
    {
        $validated = $request->validate([
            'template_name' => ['required', 'string', 'max:255'],
            'template_slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('competition_templates', 'template_slug')
                    ->ignore($template->id),
            ],
            'default_description' => ['nullable', 'string'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!$request->filled('template_slug')) {
            $validated['template_slug'] = $template->template_slug;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $oldCoverImage = null;
        $newCoverImage = null;
        $deleteOldCover = false;

        try {
            if ($request->hasFile('cover_image')) {
                $oldCoverImage = $template->cover_image;

                $newCoverImage = $request
                    ->file('cover_image')
                    ->store('competition-templates', 'public');
                $validated['cover_image'] = $newCoverImage;

                $coverIsShared = $oldCoverImage
                    && $template->competitions()
                        ->where('cover_image', $oldCoverImage)
                        ->exists();

                $deleteOldCover = $oldCoverImage
                    && !$coverIsShared
                    && Str::startsWith($oldCoverImage, 'competition-templates/');
            }

            // อัปเดตตาราง competition_templates เท่านั้น
            // การแก้ไข form fields ย้ายไปที่ CompetitionTemplateFormFieldController แล้ว
            $template->update($validated);
        } catch (Throwable $exception) {
            if ($newCoverImage && Str::startsWith($newCoverImage, 'competition-templates/')) {
                try {
                    Storage::disk('public')->delete($newCoverImage);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        if ($deleteOldCover) {
            Storage::disk('public')->delete($oldCoverImage);
        }

        return redirect()
            ->route('superadmin.templates.index')
            ->with('success', 'แก้ไขข้อมูลสำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy(CompetitionTemplate $template)
        {
            if ($template->competitions()->exists()) {
                return redirect()
                    ->route('superadmin.templates.index')
                    ->with('error', 'ไม่สามารถลบ Template นี้ได้ เนื่องจากถูกใช้สร้างการแข่งขันแล้ว กรุณาปิดการใช้งาน Template แทน');
            }

            $coverImage = $template->cover_image;
            $template->delete();

            if ($coverImage && Str::startsWith($coverImage, 'competition-templates/')) {
                Storage::disk('public')->delete($coverImage);
            }

            return redirect()->route('superadmin.templates.index')->with('success', 'ลบ Template สำเร็จ');
        }

    
}
