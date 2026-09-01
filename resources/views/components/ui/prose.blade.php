@props(['as' => 'div'])

{{-- Typography wrapper for trusted CMS/WordPress HTML bodies (posts & services).
     Consecutive images are automatically laid out side by side (.image-row). --}}
<{{ $as }} {{ $attributes->merge(['class' => 'post-body']) }}>{!! \App\Support\Html\ImageRowFormatter::apply($slot) !!}</{{ $as }}>
