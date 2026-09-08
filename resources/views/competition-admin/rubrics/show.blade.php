@extends('layouts.app')

@section('title', 'จัดการเกณฑ์การให้คะแนน')

@section('header')
    <div>
        <h1 class="text-slate-800 text-xl font-bold">
            จัดการเกณฑ์การให้คะแนน
        </h1>

        <p class="mt-1 text-slate-500 text-xs">
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
    class="inline-flex items-center justify-center rounded-xl bg-amber-500 text-sm font-semibold text-white transition hover:bg-amber-600 h-9 px-3">

    จัดการเกณฑ์การให้คะแนน
</a>
@endsection