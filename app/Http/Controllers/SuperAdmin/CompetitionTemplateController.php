<?php
    namespace App\Http\Controllers\SuperAdmin;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Str;
    use App\Models\CompetitionTemplate;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;
    use Illuminate\Support\Facades\Storage;

    class CompetitionTemplateController extends Controller
    {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $competitionTemplates = CompetitionTemplate::paginate(10);
        return view('superadmin.templates.index',compact('competitionTemplates'));
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
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validate['template_slug'] = Str::slug(
            $validate['template_slug'] ?: $validate['template_name']
        );

        if ($request->hasFile('cover_image')) {
            $validate['cover_image'] = $request->file('cover_image')->store('competition-templates', 'public');
        }
        
        CompetitionTemplate::create($validate);
        return redirect()->route('superadmin.templates.index')->with('success', 'สร้าง Template สำเร็จ');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $competitionTemplate = CompetitionTemplate::findOrFail($id);

        return view('superadmin.templates.show', compact('competitionTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionTemplate $template)
        {
            return view('superadmin.templates.edit', compact('template'));
        }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionTemplate $competitionTemplate)
        {
            $validate = $request->validate([
                'template_name' => ['required', 'string', 'max:255'],
                'template_slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('competition_templates', 'template_slug')
                        ->ignore($competitionTemplate->id),
                ],
                'default_description' => ['nullable', 'string'],
                'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'is_active' => ['nullable'],
            ]);

            if ($request->hasFile('cover_image')) {
                if ($competitionTemplate->cover_image) {
                    Storage::disk('public')->delete($competitionTemplate->cover_image);
                }

                $validate['cover_image'] = $request->file('cover_image')
                    ->store('competition-templates', 'public');
            }

            $validate['is_active'] = $request->has('is_active');

            $competitionTemplate->update($validate);

            return redirect()
                ->route('superadmin.templates.edit', [
                    'template' => $competitionTemplate->id
                ])
                ->with('success', 'แก้ไขข้อมูลสำเร็จ');
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTemplate $competitionTemplate)
    {
        $competitionTemplate->delete();

        return redirect()
            ->route('superadmin.templates.index')
            ->with('success', 'ลบ Template สำเร็จ');
    }
}
