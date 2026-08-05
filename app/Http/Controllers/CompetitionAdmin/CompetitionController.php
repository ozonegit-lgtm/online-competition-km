<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\CompetitionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompetitionFormField;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\Submission;

class CompetitionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $competitions = Competition::with([
                'category',
                'template',
            ])
            ->where('created_by', Auth::id())->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })->latest()->paginate(10)->withQueryString();
            return view('competition-admin.competitions.index',compact('competitions'));
    }

    public function create()
    {
        $categories = CompetitionCategory::where('is_active', true)->orderBy('category_name')->get();
        $templates = CompetitionTemplate::where('is_active', true)->orderBy('template_name')->get();
        return view('competition-admin.competitions.create',compact('categories', 'templates'));
    }

    public function store(Request $request)
        {
            $validated = $request->validate([
                'title' => ['required','string','max:255',],
                'category_id' => ['required', Rule::exists('competition_categories', 'id')->where('is_active', true),],
                'template_id' => ['nullable', Rule::exists('competition_templates', 'id')->where('is_active', true),],
                'description' => ['nullable','string',],
                'competition_type' => ['required','in:individual,team',],
                'visibility' => ['required','in:public,private',],
                'registration_start' => ['required','date',],
                'registration_end' => ['required','date','after:registration_start',],
                'judging_start' => ['required','date','after_or_equal:registration_end',],
                'judging_end' => ['required','date','after:judging_start',],
                'result_announcement' => ['required','date','after_or_equal:judging_end',],
            ], [
                'registration_start.required' => 'กรุณาเลือกวันและเวลาเริ่มรับผลงาน',
                'registration_end.required' => 'กรุณาเลือกวันและเวลาปิดรับผลงาน',
                'registration_end.after' => 'วันปิดรับผลงานต้องอยู่หลังวันเริ่มรับผลงาน',
                'judging_start.required' => 'กรุณาเลือกวันและเวลาเริ่มตัดสิน',
                'judging_start.after_or_equal' => 'วันเริ่มตัดสินต้องไม่น้อยกว่าวันปิดรับผลงาน',
                'judging_end.required' => 'กรุณาเลือกวันและเวลาสิ้นสุดการตัดสิน',
                'judging_end.after' => 'วันสิ้นสุดการตัดสินต้องอยู่หลังวันเริ่มตัดสิน',
                'result_announcement.required' => 'กรุณาเลือกวันและเวลาประกาศผล',
                'result_announcement.after_or_equal' => 'วันประกาศผลต้องไม่น้อยกว่าวันสิ้นสุดการตัดสิน',
            ]);

            DB::transaction(function () use ($validated) {
                $template = null;

                if (!empty($validated['template_id'])) {
                    $template = CompetitionTemplate::with(['formFields' => function ($query) {$query->orderBy('sort_order');},])->where('is_active', true)->findOrFail($validated['template_id']);
                }
                $competition = Competition::create([
                    'category_id' => $validated['category_id'],
                    'template_id' => $template?->id,
                    'created_by' => Auth::id(),
                    'title' => $validated['title'],
                    'description' => ($validated['description'] ?? null)
                        ?: $template?->default_description,
                    'cover_image' => $template?->cover_image,
                    'competition_type' => $validated['competition_type'],
                    'visibility' => $validated['visibility'],
                    'registration_start' => $validated['registration_start'],
                    'registration_end' => $validated['registration_end'],
                    'judging_start' => $validated['judging_start'],
                    'judging_end' => $validated['judging_end'],
                    'result_announcement' => $validated['result_announcement'],
                    'status' => 'draft',
                ]);

                if ($template) {
                    foreach ($template->formFields as $templateField) {
                        $options = $templateField->options;

                        if (is_string($options)) {
                            $options = json_decode($options, true);
                        }

                        if (!is_array($options)) {
                            $options = null;
                        }

                        CompetitionFormField::create([
                            'competition_id' => $competition->id,
                            'label' => $templateField->label,
                            'field_name' => $templateField->field_name,
                            'system_field' => $templateField->system_field,
                            'field_type' => $templateField->field_type,
                            'placeholder' => $templateField->placeholder,
                            'help_text' => $templateField->help_text,
                            'options' => $options,
                            'is_required' => $templateField->is_required,
                            'sort_order' => $templateField->sort_order,
                            'is_active' => $templateField->is_active,
                        ]);
                    }
                }
            });
            return redirect()->route('competition-admin.competitions.index')->with('success', 'สร้างการแข่งขันและฟอร์มรับผลงานสำเร็จ');
        }

   public function show(Competition $competition)
    {
        abort_unless(
            (int) $competition->created_by === (int) auth()->id(),
            403
        );

        $competition->load([
            'category',
            'template.formFields',
            'creator',

            'formFields' => fn ($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),

            'rubrics' => fn ($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        $competition->loadCount([
            'formFields',
            'rubrics',
            'judgeAssignments',
            'submissions',
            'awards',
        ]);

        return view('competition-admin.competitions.show',compact('competition'));
    }

    public function edit(Competition $competition)
        {
            abort_unless(
                (int) $competition->created_by === (int) Auth::id(),403
            );

            $competition->load('template');

            $categories = CompetitionCategory::query()->where('is_active', true)->orderBy('category_name', 'asc')->get();
            $templates = CompetitionTemplate::query()->where('is_active', true)->orderBy('template_name', 'asc')->get();
            return view('competition-admin.competitions.edit', compact('competition', 'categories', 'templates'));
        }

    public function update(Request $request,Competition $competition) 
    {
        abort_unless(
            (int) $competition->created_by === (int) Auth::id(),
            403,'เอาเลข 1กับ0มาเลียงเป็นรูปภาพ'
        );

        $validated = $request->validate([
                'category_id' => ['required','integer','exists:competition_categories,id',],
                'template_id' => ['required','integer','exists:competition_templates,id',],
                'title' => ['required','string','max:255',],
                'description' => ['nullable', 'string',],
                'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240',],
                'competition_type' => ['required', 'in:individual,team',],
                'visibility' => ['required', 'in:public,private',],
                'access_code' => ['nullable', 'string', 'max:255',],
                'registration_start' => [ 'required','date',],
                'registration_end' => ['required','date','after_or_equal:registration_start',],
                'judging_start' => ['required','date','after_or_equal:registration_end',], 
                'judging_end' => ['required','date','after_or_equal:judging_start',],
                'result_announcement' => ['required','date','after_or_equal:judging_end',],
                'publish_scores' => ['required','boolean',],
                'publish_km' => ['required','boolean',],
                'status' => ['required','in:draft,published,open,closed,judging,completed,archived',],
            ], [
                'category_id.required' => 'กรุณาเลือกหมวดหมู่การแข่งขัน',
                'category_id.exists' => 'ไม่พบหมวดหมู่การแข่งขันที่เลือก',
                'template_id.exists' => 'ไม่พบแม่แบบการแข่งขันที่เลือก',
                'title.required' => 'กรุณากรอกชื่อการแข่งขัน',
                'title.max' => 'ชื่อการแข่งขันต้องไม่เกิน 255 ตัวอักษร',
                'cover_image.image' => 'ไฟล์ภาพปกต้องเป็นรูปภาพเท่านั้น',
                'cover_image.mimes' => 'ภาพปกต้องเป็นไฟล์ JPG, JPEG, PNG หรือ WEBP',
                'cover_image.max' => 'ภาพปกต้องมีขนาดไม่เกิน 2 MB',
                'competition_type.required' => 'กรุณาเลือกรูปแบบการแข่งขัน',
                'competition_type.in' => 'รูปแบบการแข่งขันไม่ถูกต้อง',
                'visibility.required' => 'กรุณาเลือกการเข้าถึงการแข่งขัน',
                'visibility.in' => 'รูปแบบการเข้าถึงไม่ถูกต้อง',
                'access_code.required_if' => 'กรุณากรอกรหัสเข้าร่วมสำหรับการแข่งขันแบบส่วนตัว',
                'access_code.max' => 'รหัสเข้าร่วมต้องไม่เกิน 100 ตัวอักษร',
                'registration_start.required' => 'กรุณาระบุวันเริ่มรับผลงาน',
                'registration_end.required' => 'กรุณาระบุวันปิดรับผลงาน',
                'registration_end.after_or_equal' => 'วันปิดรับผลงานต้องไม่อยู่ก่อนวันเริ่มรับผลงาน',
                'judging_start.required' => 'กรุณาระบุวันเริ่มตัดสิน',
                'judging_start.after_or_equal' => 'วันเริ่มตัดสินต้องไม่อยู่ก่อนวันปิดรับผลงาน',
                'judging_end.required' => 'กรุณาระบุวันสิ้นสุดการตัดสิน',
                'judging_end.after_or_equal' => 'วันสิ้นสุดการตัดสินต้องไม่อยู่ก่อนวันเริ่มตัดสิน',
                'result_announcement.required' => 'กรุณาระบุวันประกาศผล',
                'result_announcement.after_or_equal' => 'วันประกาศผลต้องไม่อยู่ก่อนวันสิ้นสุดการตัดสิน',
                'status.required' => 'กรุณาเลือกสถานะการแข่งขัน',
                'status.in' => 'สถานะการแข่งขันไม่ถูกต้อง',
            ]);

                // แปลงค่าจาก Checkbox เป็น true/false
                $validated['publish_scores'] = $request->boolean('publish_scores');
                $validated['publish_km'] = $request->boolean('publish_km');
                // การแข่งขัน Public ไม่ต้องมีรหัสเข้าร่วม
                if ($validated['visibility'] === 'public') {
                    $validated['access_code'] = null;
                }
                // จัดการภาพปกใหม่
                $oldCoverImage = $competition->cover_image;
                if ($request->hasFile('cover_image')) {
                    $validated['cover_image'] = $request
                        ->file('cover_image')
                        ->store('competitions', 'public');
                }
                // อัปเดตฐานข้อมูล
                $competition->update($validated);
                // ลบภาพเดิมหลังจากอัปเดตสำเร็จ
                if (isset($validated['cover_image']) &&$oldCoverImage &&Storage::disk('public')->exists($oldCoverImage)) {
                    Storage::disk('public')->delete($oldCoverImage);
                }

                return redirect()->route('competition-admin.competitions.show', $competition)->with('success', 'แก้ไขข้อมูลการแข่งขันสำเร็จ');
    }
    public function submissions()
    {
        $submissions = Submission::with(['competition.category','files',])->whereHas('competition', function ($query) {$query->where('created_by', auth()->id()); })
        ->latest('submitted_at')
        ->paginate(12);

        return view('competition-admin.submissions.index',compact('submissions'));
    }

    public function destroy(Competition $competition)
    {
        //
    }
}