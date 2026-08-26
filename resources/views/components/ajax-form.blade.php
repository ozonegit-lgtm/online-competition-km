@props([
    'action',
    'method' => 'POST',
    'confirm' => null,
    'success' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
    'loading' => 'กำลังบันทึก...',
    'target' => null,
])

@php
    $httpMethod = strtoupper($method);
    $formMethod = $httpMethod === 'GET' ? 'GET' : 'POST';
@endphp

<form
    action="{{ $action }}"
    method="{{ $formMethod }}"
    data-ajax-form
    data-ajax-success="{{ $success }}"
    data-ajax-loading="{{ $loading }}"
    @if ($confirm)
        data-ajax-confirm="{{ $confirm }}"
    @endif
    @if ($target)
        data-ajax-target="{{ $target }}"
    @endif
    {{ $attributes }}
>
    @if ($httpMethod !== 'GET')
        @csrf
    @endif

    @if (! in_array($httpMethod, ['GET', 'POST'], true))
        @method($httpMethod)
    @endif

    {{ $slot }}
</form>
