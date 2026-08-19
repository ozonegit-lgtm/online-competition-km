@extends('layouts.app')

@section('title', 'แก้ไขการแข่งขัน')

@section('header')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">แก้ไขการแข่งขัน</h1>
            <p class="mt-1 text-sm text-slate-500">
                แก้ไขข้อมูล กำหนดการ และการเผยแพร่ของการแข่งขัน
            </p>
        </div>

        <a href="{{ route('competition-admin.competitions.show', $competition) }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200">
            กลับหน้ารายละเอียด
        </a>
    </div>
@endsection

@section('content')
    @php
        $currentCover = $competition->cover_image ?: $competition->template?->cover_image;
        $currentCoverUrl = null;

        if ($currentCover) {
            $currentCoverUrl = \Illuminate\Support\Str::startsWith($currentCover, ['http://', 'https://'])
                ? $currentCover
                : \Illuminate\Support\Facades\Storage::disk('public')->url($currentCover);
        }

        $dateValue = function ($date) {
            return $date ? $date->format('Y-m-d\TH:i') : '';
        };
    @endphp

    <div class="mx-auto w-full max-w-6xl">
        <form action="{{ route('competition-admin.competitions.update', $competition) }}" method="POST"
            enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- ข้อมูลพื้นฐาน --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">ข้อมูลพื้นฐาน</h2>
                    <p class="mt-1 text-sm text-slate-500">ชื่อ รายละเอียด หมวดหมู่ และแม่แบบการแข่งขัน</p>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-slate-700">
                            หมวดหมู่การแข่งขัน <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('category_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                            <option value="">-- เลือกหมวดหมู่การแข่งขัน --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected((string) old('category_id', $competition->category_id) === (string) $category->id)>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="template_id" class="block text-sm font-semibold text-slate-700">
                            แม่แบบการแข่งขัน
                        </label>
                        <select id="template_id" name="template_id"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('template_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                            <option value="">-- ไม่ใช้แม่แบบ --</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}"
                                    @selected((string) old('template_id', $competition->template_id) === (string) $template->id)>
                                    {{ $template->template_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('template_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-semibold text-slate-700">
                            ชื่อการแข่งขัน <span class="text-red-500">*</span>
                        </label>
                        <input id="title" type="text" name="title" value="{{ old('title', $competition->title) }}"
                            required maxlength="255" placeholder="เช่น การประกวดสื่อสร้างสรรค์ประจำปี 2569"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 {{ $errors->has('title') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-semibold text-slate-700">
                            รายละเอียดการแข่งขัน
                        </label>
                        <textarea id="description" name="description" rows="6"
                            placeholder="อธิบายวัตถุประสงค์ คุณสมบัติผู้เข้าร่วม และรายละเอียดสำคัญ"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 {{ $errors->has('description') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">{{ old('description', $competition->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- ภาพปก --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">ภาพปกการแข่งขัน</h2>
                    <p class="mt-1 text-sm text-slate-500">แนะนำภาพแนวนอนอัตราส่วนประมาณ 16:6 ไฟล์ JPG, PNG หรือ WEBP</p>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                    <div id="coverPreviewContainer" class="relative h-56 w-full sm:h-72 lg:h-80">
                        <img id="coverPreview" src="{{ $currentCoverUrl ?: '' }}" alt="ตัวอย่างภาพปก"
                            class="h-full w-full object-cover object-center {{ $currentCoverUrl ? '' : 'hidden' }}">

                        <div id="coverPlaceholder"
                            class="flex h-full w-full items-center justify-center px-6 text-center {{ $currentCoverUrl ? 'hidden' : '' }}">
                            <div>
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Z" />
                                </svg>
                                <p class="mt-3 text-sm font-medium text-slate-500">ยังไม่มีภาพปกการแข่งขัน</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 bg-white p-5">
                        <label for="cover_image" class="block text-sm font-semibold text-slate-700">
                            เลือกภาพปกใหม่
                        </label>
                        <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                            class="mt-2 block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-2 text-xs text-slate-500">หากไม่เลือกภาพใหม่ ระบบจะใช้ภาพเดิม ขนาดไฟล์ไม่เกิน 2 MB</p>
                        @error('cover_image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- รูปแบบและการเข้าถึง --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">รูปแบบและการเข้าถึง</h2>
                    <p class="mt-1 text-sm text-slate-500">กำหนดลักษณะการส่งผลงานและผู้ที่สามารถเข้าร่วมได้</p>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="competition_type" class="block text-sm font-semibold text-slate-700">
                            รูปแบบการแข่งขัน <span class="text-red-500">*</span>
                        </label>
                        <select id="competition_type" name="competition_type" required
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('competition_type') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                            <option value="individual" @selected(old('competition_type', $competition->competition_type) === 'individual')>
                                ประเภทบุคคล
                            </option>
                            <option value="team" @selected(old('competition_type', $competition->competition_type) === 'team')>
                                ประเภททีม
                            </option>
                        </select>
                        @error('competition_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="visibility" class="block text-sm font-semibold text-slate-700">
                            การเข้าถึงการแข่งขัน <span class="text-red-500">*</span>
                        </label>
                        <select id="visibility" name="visibility" required
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('visibility') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                            <option value="public" @selected(old('visibility', $competition->visibility) === 'public')>
                                สาธารณะ — ทุกคนเข้าร่วมได้
                            </option>
                            <option value="private" @selected(old('visibility', $competition->visibility) === 'private')>
                                ส่วนตัว — ต้องใช้รหัสเข้าร่วม
                            </option>
                        </select>
                        @error('visibility')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="accessCodeBox" class="md:col-span-2">
                        <label for="access_code" class="block text-sm font-semibold text-slate-700">
                            รหัสเข้าร่วม <span class="text-red-500">*</span>
                        </label>
                        <input id="access_code" type="text" name="access_code"
                            value="{{ old('access_code', $competition->access_code) }}" maxlength="100"
                            placeholder="เช่น CREATIVE2569" autocomplete="off"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 font-mono uppercase tracking-wider text-slate-800 outline-none transition placeholder:font-sans placeholder:normal-case placeholder:tracking-normal placeholder:text-slate-400 focus:bg-white focus:ring-4 {{ $errors->has('access_code') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                        <p class="mt-2 text-xs text-slate-500">ผู้เข้าร่วมต้องกรอกรหัสนี้ก่อนส่งผลงาน</p>
                        @error('access_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- กำหนดการ --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">กำหนดการแข่งขัน</h2>
                    <p class="mt-1 text-sm text-slate-500">ระบุวันและเวลาให้ครบตามลำดับการดำเนินงาน</p>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="registration_start" class="block text-sm font-semibold text-slate-700">
                            เริ่มรับผลงาน <span class="text-red-500">*</span>
                        </label>
                        <input id="registration_start" type="datetime-local" name="registration_start" required
                            value="{{ old('registration_start', $dateValue($competition->registration_start)) }}"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('registration_start') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                        @error('registration_start')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="registration_end" class="block text-sm font-semibold text-slate-700">
                            ปิดรับผลงาน <span class="text-red-500">*</span>
                        </label>
                        <input id="registration_end" type="datetime-local" name="registration_end" required
                            value="{{ old('registration_end', $dateValue($competition->registration_end)) }}"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('registration_end') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                        @error('registration_end')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="judging_start" class="block text-sm font-semibold text-slate-700">
                            เริ่มตัดสิน <span class="text-red-500">*</span>
                        </label>
                        <input id="judging_start" type="datetime-local" name="judging_start" required
                            value="{{ old('judging_start', $dateValue($competition->judging_start)) }}"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('judging_start') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                        @error('judging_start')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="judging_end" class="block text-sm font-semibold text-slate-700">
                            สิ้นสุดการตัดสิน <span class="text-red-500">*</span>
                        </label>
                        <input id="judging_end" type="datetime-local" name="judging_end" required
                            value="{{ old('judging_end', $dateValue($competition->judging_end)) }}"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('judging_end') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                        @error('judging_end')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="result_announcement" class="block text-sm font-semibold text-slate-700">
                            วันประกาศผล <span class="text-red-500">*</span>
                        </label>
                        <input id="result_announcement" type="datetime-local" name="result_announcement" required
                            value="{{ old('result_announcement', $dateValue($competition->result_announcement)) }}"
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('result_announcement') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">
                        @error('result_announcement')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- สถานะและการเผยแพร่ --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">สถานะและการเผยแพร่</h2>
                    <p class="mt-1 text-sm text-slate-500">ควบคุมสถานะการแข่งขัน คะแนน และผลงานในคลังความรู้</p>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700">
                            สถานะการแข่งขัน <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                            class="mt-2 w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:bg-white focus:ring-4 {{ $errors->has('status') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-blue-600 focus:ring-blue-100' }}">


                            <option value="open" @selected(old('status', $competition->status) === 'open')>เปิดรับผลงาน</option>
                            <option value="closed" @selected(old('status', $competition->status) === 'closed')>ปิดรับผลงาน</option>

                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-3">
                        <input type="hidden" name="publish_scores" value="0">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-blue-300 hover:bg-blue-50/40">
                            <input type="checkbox" name="publish_scores" value="1"
                                @checked((bool) old('publish_scores', $competition->publish_scores))
                                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">แสดงคะแนนต่อสาธารณะ</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">อนุญาตให้ผู้เข้าชมดูผลคะแนนของการแข่งขัน</span>
                            </span>
                        </label>

                        <input type="hidden" name="publish_km" value="0">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-blue-300 hover:bg-blue-50/40">
                            <input type="checkbox" name="publish_km" value="1"
                                @checked((bool) old('publish_km', $competition->publish_km))
                                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">นำผลงานเข้า KM</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">อนุญาตให้นำผลงานที่เลือกไปเผยแพร่ในคลังความรู้</span>
                            </span>
                        </label>
                    </div>
                </div>
            </section>

            {{-- ปุ่มดำเนินการ --}}
            <div class="sticky bottom-4 z-20 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('competition-admin.competitions.show', $competition) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200">
                    ยกเลิก
                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const visibility = document.getElementById('visibility');
            const accessCodeBox = document.getElementById('accessCodeBox');
            const accessCode = document.getElementById('access_code');
            const coverInput = document.getElementById('cover_image');
            const coverPreview = document.getElementById('coverPreview');
            const coverPlaceholder = document.getElementById('coverPlaceholder');

            const updateAccessCode = () => {
                const isPrivate = visibility.value === 'private';

                accessCodeBox.classList.toggle('hidden', !isPrivate);
                accessCode.required = isPrivate;
            };

            visibility.addEventListener('change', updateAccessCode);
            updateAccessCode();

            coverInput.addEventListener('change', (event) => {
                const file = event.target.files[0];

                if (!file) {
                    return;
                }

                const previewUrl = URL.createObjectURL(file);

                coverPreview.src = previewUrl;
                coverPreview.classList.remove('hidden');
                coverPlaceholder.classList.add('hidden');

                coverPreview.onload = () => URL.revokeObjectURL(previewUrl);
            });
        });
    </script>
@endpush
