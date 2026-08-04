<?php


namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Rubric;
use Illuminate\Http\Request;
class RubricController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Competition $competition)
    {
        abort_unless(
            (int) $competition->created_by === (int) auth()->id(),
            403
        );

        $rubrics = $competition->rubrics()->orderBy('sort_order')->orderBy('id')->get();

        $totalMaxScore = $rubrics->where('is_active', true)->sum('max_score');
        return view('competition-admin.rubrics.index', compact('competition', 'rubrics', 'totalMaxScore'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Competition $competition)
    {
        abort_unless(
            (int) $competition->created_by === (int) auth()->id(),
            403
        );

        $validated = $request->validate([
            'criteria_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable','string'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
            $isActive = $request->boolean('is_active');
            $currentTotal = $competition->rubrics()->where('is_active', true)->sum('max_score');
            if ($isActive && $currentTotal + $validated['max_score'] > 100) {
                return back()->withErrors(['max_score' => 'คะแนนรวมต้องไม่เกิน 100 คะแนน',])->withInput();
            } 

            $competition->rubrics()->create([
                'criteria_name' => $validated['criteria_name'],
                'description' => $validated['description'] ?? null,
                'max_score' => $validated['max_score'],
                'weight' => $validated['max_score'],
                'sort_order' => ((int) $competition->rubrics()->max('sort_order')) + 1,
                'is_active' => $request->boolean('is_active'),
            ]);

            return back()->with('success', 'เพิ่มเกณฑ์การให้คะแนนสำเร็จ');
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Rubric $rubric)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rubric $rubric)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competition $competition, Rubric $rubric)
    {
        abort_unless(
            (int) $competition->created_by === (int) auth()->id(),
            403
        );

        abort_unless(
            (int) $rubric->competition_id === (int) $competition->id,
            404
        );
 
        $validated = $request->validate([
            'criteria_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'is_active' => ['nullable','boolean'],
        ]);

        $otherTotal = $competition->rubrics()->where('id', '!=', $rubric->id)->where('is_active', true)->sum('max_score');

        $isActive = $request->boolean('is_active');
        if ($isActive && $otherTotal + $validated['max_score'] > 100 ) {
            return back()->withErrors(['max_score' => 'คะแนนรวมต้องไม่เกิน 100 คะแนน', ])->withInput();
        }

        $rubric->update([
            'criteria_name' => $validated['criteria_name'],
            'description' => $validated['description'] ?? null,
            'max_score' => $validated['max_score'],
            'weight' => $validated['max_score'],
            'is_active' => $isActive,
        ]);

        return back()->with('success', 'แก้ไขเกณฑ์สำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competition $competition, Rubric $rubric)
    {
        abort_unless(
            (int) $competition->created_by === (int) auth()->id(),
            403,
            'คุณไม่มีสิทธิ์จัดการการแข่งขันนี้'
        );

        abort_unless(
            (int) $rubric->competition_id === (int) $competition->id,
            404
        );

        if($rubric->scores()->exists()) {
            return back()->withErrors([
                'rubric' => 'ไม่สามารถลบเกณฑ์ที่มีการให้คะแนนแล้ว',
            ]);
        }

        $rubric->delete();
        return back()->with('success','ลบเกณฑ์การให้คะแนนสำเร็จ');
    }
}
