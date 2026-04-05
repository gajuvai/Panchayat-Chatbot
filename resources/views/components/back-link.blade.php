@props(['href'])
<a href="{{ $href }}" {{ $attributes->class('text-indigo-600 text-sm hover:underline inline-flex items-center gap-1') }}>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    {{ $slot }}
</a>
