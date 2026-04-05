@php
$navLinks = [
    ['route' => 'admin.dashboard',           'match' => 'admin.dashboard',         'label' => 'Dashboard'],
    ['route' => 'admin.complaints.index',    'match' => 'admin.complaints.*',       'label' => 'Complaints'],
    ['route' => 'admin.announcements.index', 'match' => 'admin.announcements.*',    'label' => 'Announcements'],
    ['route' => 'admin.events.index',        'match' => 'admin.events.*',           'label' => 'Events'],
    ['route' => 'admin.polls.index',         'match' => 'admin.polls.*',            'label' => 'Polls'],
    ['route' => 'admin.analytics.index',     'match' => 'admin.analytics.*',        'label' => 'Analytics'],
    ['route' => 'admin.rules.index',         'match' => 'admin.rules.*',            'label' => 'Rule Book'],
    ['route' => 'admin.users.index',         'match' => 'admin.users.*',            'label' => 'Users'],
];
@endphp
@foreach($navLinks as $link)
<a href="{{ route($link['route']) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ ($link['match'] && request()->routeIs($link['match'])) ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
    {{ $link['label'] }}
</a>
@endforeach
