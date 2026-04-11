@php
$navLinks = [
    ['route' => 'admin.dashboard',           'match' => 'admin.dashboard',         'label' => 'Dashboard'],
    ['route' => 'admin.complaints.index',    'match' => 'admin.complaints.*',       'label' => 'Complaints'],
    ['route' => 'admin.maintenance.index',  'match' => 'admin.maintenance.*',      'label' => 'Maintenance'],
    ['route' => 'admin.amenities.index',    'match' => 'admin.amenities.*',        'label' => 'Amenities'],
    ['route' => 'admin.amenity-bookings.index', 'match' => 'admin.amenity-bookings.*', 'label' => 'Amenity Bookings'],
    ['route' => 'admin.duty-roster.index', 'match' => 'admin.duty-roster.*', 'label' => 'Duty Roster'],
    ['route' => 'admin.expenses.index',     'match' => 'admin.expenses.*',         'label' => 'Expenses'],
    ['route' => 'expenses.index',           'match' => 'expenses.*',               'label' => 'Budget Dashboard'],
    ['route' => 'admin.categories.index',   'match' => 'admin.categories.*',       'label' => 'Categories'],
    ['route' => 'admin.announcements.index', 'match' => 'admin.announcements.*',    'label' => 'Announcements'],
    ['route' => 'admin.events.index',        'match' => 'admin.events.*',           'label' => 'Events'],
    ['route' => 'admin.polls.index',         'match' => 'admin.polls.*',            'label' => 'Polls'],
    ['route' => 'admin.analytics.index',     'match' => 'admin.analytics.*',        'label' => 'Analytics'],
    ['route' => 'admin.documents.index',    'match' => 'admin.documents.*',        'label' => 'Document Library'],
    ['route' => 'admin.rules.index',         'match' => 'admin.rules.*',            'label' => 'Rule Book'],
    ['route' => 'admin.users.index',         'match' => 'admin.users.*',            'label' => 'Users'],
    ['route' => 'directory.index',           'match' => 'directory.*',              'label' => 'Community Directory'],
    ['route' => 'lost-found.index',          'match' => 'lost-found.*',             'label' => 'Lost & Found'],
];
@endphp
@foreach($navLinks as $link)
<a href="{{ route($link['route']) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ ($link['match'] && request()->routeIs($link['match'])) ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white' }} transition">
    {{ $link['label'] }}
</a>
@endforeach
