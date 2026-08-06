<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompetitionJudgeController extends Controller
{

    /**
     * แสดงรายการแข่งขันทั้งหมด
     * เพื่อให้ Super Admin เลือกจัดการกรรมการ
     */
    public function competitions(
        Request $request
    ): View {
        $search = trim(
            (string) $request->query('q', '')
        );

        $competitions = Competition::query() ->with([ 'category', 'creator.adminProfile',])->withCount(['judgeAssignments',])->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        'title',
                        'like',
                        '%' . $search . '%'
                    );
                }
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view(
            'superadmin.competition-judges.competitions',
            compact(
                'competitions',
                'search'
            )
        );
    }
    /**
     * แสดงหน้าแต่งตั้งกรรมการของการแข่งขัน
     */
    public function index(Competition $competition): View 
    {
        Gate::authorize('assignJudges',$competition);

        $competition->load([
            'judgeAssignments.judge.adminProfile',
        ]);

        $judges = User::query()->where('is_active', true)->whereHas('role', function ($query) {
                $query->where('role_name', 'Judge');
            })->with(['role','adminProfile',])->orderBy('username')->get();

        $assignedJudgeIds = $competition
            ->judgeAssignments
            ->pluck('judge_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('superadmin.competition-judges.index',
            compact('competition', 'judges','assignedJudgeIds')
        );
    }
    /**
     * บันทึกรายชื่อกรรมการของการแข่งขัน
     */
    public function sync(
        Request $request,
        Competition $competition
    ): RedirectResponse {
        Gate::authorize(
            'assignJudges',
            $competition
        );

        $validated = $request->validate(
            [
                'judge_ids' => [
                    'nullable',
                    'array',
                ],
                'judge_ids.*' => [
                    'integer',
                    'distinct',
                    'exists:users,id',
                ],
            ],
            [
                'judge_ids.array' =>
                    'รูปแบบรายชื่อกรรมการไม่ถูกต้อง',

                'judge_ids.*.integer' =>
                    'รหัสกรรมการไม่ถูกต้อง',

                'judge_ids.*.distinct' =>
                    'มีรายชื่อกรรมการซ้ำกัน',

                'judge_ids.*.exists' =>
                    'ไม่พบข้อมูลกรรมการที่เลือก',
            ]
        );

        $requestedJudgeIds = collect(
            $validated['judge_ids'] ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
         * ป้องกันการส่ง User ID ที่ไม่ได้เป็น Role Judge
         * หรือถูกปิดใช้งานผ่านการแก้ HTML
         */
        $validJudgeIds = User::query()
            ->whereIn('id', $requestedJudgeIds)
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('role_name', 'Judge');
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if (
            $validJudgeIds->count()
            !== $requestedJudgeIds->count()
        ) {
            return back()
                ->withErrors([
                    'judge_ids' =>
                        'มีผู้ใช้ที่ไม่ใช่กรรมการหรือถูกปิดใช้งาน',
                ])
                ->withInput();
        }

        $assignmentsToRemove = $competition
            ->judgeAssignments()
            ->when(
                $validJudgeIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn(
                    'judge_id',
                    $validJudgeIds
                )
            )
            ->when(
                $validJudgeIds->isEmpty(),
                fn ($query) => $query
            )
            ->get();

        /*
         * ถ้าเคยให้คะแนนแล้ว ห้ามถอดกรรมการ
         * เพื่อรักษาประวัติคะแนน
         */
        foreach ($assignmentsToRemove as $assignment) {
            if ($assignment->scores()->exists()) {
                return back()->withErrors([
                    'judge_ids' =>
                        'ไม่สามารถนำกรรมการที่มีคะแนนแล้วออกได้',
                ]);
            }
        }

        DB::transaction(function () use (
            $competition,
            $validJudgeIds,
            $assignmentsToRemove
        ) {
            foreach ($assignmentsToRemove as $assignment) {
                $assignment->delete();
            }

            foreach ($validJudgeIds as $judgeId) {
                JudgeAssignment::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'judge_id' => $judgeId,
                    ],
                    [
                        /*
                         * Super Admin เป็นผู้กำหนดสิทธิ์โดยตรง
                         * จึงอนุมัติ Assignment ทันที
                         */
                        'assigned_at' => now(),
                        'assignment_status' => 'accepted',
                        'accepted_at' => now(),
                        'declined_at' => null,
                    ]
                );
            }
        });

        return redirect()
            ->route(
                'superadmin.competitions.judges.index',
                $competition
            )
            ->with(
                'success',
                'บันทึกรายชื่อกรรมการเรียบร้อยแล้ว'
            );
    }

    /**
     * นำกรรมการออกจากการแข่งขัน
     */
    public function destroy(
        Competition $competition,
        User $judge
    ): RedirectResponse {
        Gate::authorize(
            'assignJudges',
            $competition
        );

        $assignment = $competition
            ->judgeAssignments()
            ->where('judge_id', $judge->id)
            ->firstOrFail();

        if ($assignment->scores()->exists()) {
            return back()->withErrors([
                'judge' =>
                    'ไม่สามารถนำกรรมการที่มีคะแนนแล้วออกได้',
            ]);
        }

        $assignment->delete();

        return back()->with(
            'success',
            'นำกรรมการออกจากการแข่งขันเรียบร้อยแล้ว'
        );
    }
}