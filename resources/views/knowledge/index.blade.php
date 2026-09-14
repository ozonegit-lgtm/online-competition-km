@extends('layouts.km', ['title' => $settings->site_name])

@section('content')
<div class="min-h-screen bg-white text-slate-800" data-knowledge-page>

    @include('knowledge.partials.navbar')

    <main>
        @php
            $sections = collect([
                ['view' => 'knowledge.partials.hero', 'order' => $settings->hero_sort_order],
                ['view' => 'knowledge.partials.about', 'order' => $settings->about_sort_order],
                ['view' => 'knowledge.partials.books', 'order' => $settings->books_sort_order],
                ['view' => 'knowledge.partials.contact', 'order' => $settings->contact_sort_order],
            ])->sortBy('order');
        @endphp

        @foreach ($sections as $section)
            @include($section['view'])
        @endforeach
    </main>

    @include('knowledge.partials.footer')

</div>
@endsection