@extends('layouts.app')

@section('title', 'จัดการเกณฑ์การให้คะแนน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            จัดการเกณฑ์การให้คะแนน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            การแข่งขัน: {{ $competition->title }}
        </p>
    </div>
@endsection

@section('content')
<a
    href="{{ route(
        'competition-admin.competitions.rubrics.index',
        $competition
    ) }}"
    class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600">

    จัดการเกณฑ์การให้คะแนน
</a>
@endsection