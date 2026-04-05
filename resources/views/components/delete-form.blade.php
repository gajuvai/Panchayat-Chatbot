@props([
    'route',
    'resource' => 'item',
    'label'    => 'Delete',
    'btnClass' => 'text-red-400 hover:text-red-600 text-xs hover:underline',
    'confirm'  => null,
])
<form action="{{ $route }}" method="POST" class="inline"
    onsubmit="return confirm('{{ $confirm ?? 'Delete this ' . $resource . '? This cannot be undone.' }}')">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->class($btnClass) }}>{{ $label }}</button>
</form>
