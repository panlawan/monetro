{{-- resources/views/components/status-badge.blade.php --}}

@props(['user'])

@php
    $statusConfig = [
        'active' => ['color' => 'bg-green-500', 'icon' => 'fa-check-circle', 'text' => 'Active'],
        'inactive' => ['color' => 'bg-gray-500', 'icon' => 'fa-minus-circle', 'text' => 'Inactive'],
        'suspended' => ['color' => 'bg-red-500', 'icon' => 'fa-ban', 'text' => 'Suspended'],
        'pending' => ['color' => 'bg-yellow-500', 'icon' => 'fa-clock', 'text' => 'Pending'],
    ];
    
    $config = $statusConfig[$user->status ?? 'active'] ?? $statusConfig['active'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white {{ $config['color'] }}">
    <i class="fas {{ $config['icon'] }} mr-1"></i>
    {{ $config['text'] }}
</span>