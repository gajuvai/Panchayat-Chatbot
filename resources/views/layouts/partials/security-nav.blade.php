@php
$navLinks = [
    ['route' => 'security.dashboard',       'match' => 'security.dashboard',     'label' => 'Dashboard'],
    ['route' => 'security.incidents.index', 'match' => 'security.incidents.*',   'label' => 'Incidents'],
    ['route' => 'security.patrols.index',   'match' => 'security.patrols.*',     'label' => 'Patrols'],
    ['route' => 'security.alerts.index',    'match' => 'security.alerts.*',      'label' => 'Emergency Alerts'],
];
@endphp
@foreach($navLinks as $link)
<a href="{{ route($link['route']) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs($link['match']) ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
    {{ $link['label'] }}
</a>
@endforeach
