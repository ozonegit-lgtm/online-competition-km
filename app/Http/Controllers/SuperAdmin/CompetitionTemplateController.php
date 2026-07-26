<?php
    namespace App\Http\Controllers\SuperAdmin;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Str;
    use App\Models\CompetitionTemplate;
    use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'template_name' => ['required', 'string', 'max:255'],
            'template_slug' => ['nullable', 'string', 'max:255', 'unique:competition_templates,template_slug'],
            'default_description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['template_slug'] = Str::slug(
            $validated['template_slug'] ?: $validated['template_name']
        );

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('competition-templates', 'public');
        }
        
        CompetitionTemplate::create($validated);
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
    public function edit(CompetitionTemplate $competitionTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionTemplate $competitionTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTemplate $competitionTemplate)
    {
        //
    }
}
