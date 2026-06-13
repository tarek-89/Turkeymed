@props(['as' => 'div'])

{{-- Typography wrapper for trusted CMS/WordPress HTML bodies (posts & services). --}}
<{{ $as }} {{ $attributes->merge(['class' => 'post-body']) }}>{{ $slot }}</{{ $as }}>
