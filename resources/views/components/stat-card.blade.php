@props([
    'label',
    'value',
    'icon'  => null,
    'color' => 'indigo',  // indigo | green | yellow | red | blue | gray
])
@php
$colors = [
    'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'icon' => 'bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-300', 'val' => 'text-indigo-700 dark:text-indigo-300'],
    'green'  => ['bg' => 'bg-green-50 dark:bg-green-900/20',   'icon' => 'bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-300',   'val' => 'text-green-700 dark:text-green-300'],
    'yellow' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'icon' => 'bg-yellow-100 dark:bg-yellow-800 text-yellow-600 dark:text-yellow-300','val' => 'text-yellow-700 dark:text-yellow-300'],
    'red'    => ['bg' => 'bg-red-50 dark:bg-red-900/20',       'icon' => 'bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-300',           'val' => 'text-red-700 dark:text-red-300'],
    'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',     'icon' => 'bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300',       'val' => 'text-blue-700 dark:text-blue-300'],
    'gray'   => ['bg' => 'bg-gray-50 dark:bg-gray-800',        'icon' => 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300',       'val' => 'text-gray-700 dark:text-gray-300'],
];
$c = $colors[$color] ?? $colors['indigo'];
@endphp
<div class="rounded-xl border dark:border-gray-700 p-5 {{ $c['bg'] }} flex items-center gap-4">
    @if($icon)
    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 {{ $c['icon'] }} text-xl">
        {{ $icon }}
    </div>
    @endif
    <div>
        <div class="text-2xl font-bold {{ $c['val'] }}">{{ $value }}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $label }}</div>
    </div>
</div>
