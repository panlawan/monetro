{{-- resources/views/components/role-badge.blade.php --}}

@props(['user'])

@php
    $roleConfig = [
        'super_admin' => ['color' => 'bg-red-500', 'icon' => 'fa-crown', 'text' => 'Super Admin'],
        'admin' => ['color' => 'bg-yellow-500', 'icon' => 'fa-shield-alt', 'text' => 'Admin'],
        'moderator' => ['color' => 'bg-blue-500', 'icon' => 'fa-user-shield', 'text' => 'Moderator'],
        'user' => ['color' => 'bg-green-500', 'icon' => 'fa-user', 'text' => 'User'],
    ];
    
    $config = $roleConfig[$user->role ?? 'user'] ?? $roleConfig['user'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white {{ $config['color'] }}">
    <i class="fas {{ $config['icon'] }} mr-1"></i>
    {{ $config['text'] }}
</span>