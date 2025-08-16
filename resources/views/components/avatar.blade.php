{{-- สร้าง Blade Component: resources/views/components/avatar.blade.php --}}

@props(['user', 'size' => 'w-10 h-10', 'textSize' => 'text-sm'])

@php
    $initials = collect(explode(' ', $user->name))
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('');
    
    $colors = [
        'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 
        'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-gray-500'
    ];
    
    $colorIndex = crc32($user->name) % count($colors);
    $bgColor = $colors[abs($colorIndex)];
@endphp

@if($user->avatar && \Storage::disk('public')->exists(str_replace('storage/', '', $user->avatar)))
    <img src="{{ asset($user->avatar) }}" 
         alt="{{ $user->name }}" 
         class="{{ $size }} rounded-full object-cover">
@else
    <div class="{{ $size }} {{ $bgColor }} rounded-full flex items-center justify-center text-white font-semibold {{ $textSize }}">
        {{ $initials }}
    </div>
@endif

{{-- สร้างไฟล์ resources/views/components/avatar.blade.php --}}

@props(['user', 'size' => 'w-10 h-10', 'textSize' => 'text-sm'])

@if($user->avatar_url)
    <img src="{{ $user->avatar_url }}" 
         alt="{{ $user->name }}" 
         class="{{ $size }} rounded-full object-cover border-2 border-gray-200 dark:border-gray-700">
@else
    <div class="{{ $size }} {{ $user->avatar_color }} rounded-full flex items-center justify-center text-white font-semibold {{ $textSize }} border-2 border-gray-200 dark:border-gray-700">
        {{ $user->initials }}
    </div>
@endif