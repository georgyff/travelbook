{{-- Line icon for a place type: capital, city, village, nature, monument, or place (a map pin, for any other type). --}}
@props(['icon'])

<svg {{ $attributes->merge(['class' => 'place-icon']) }} data-icon="{{ $icon }}" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($icon)
        @case('capital')
            <path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"></path>
            @break
        @case('city')
            <path d="M3 21h18"></path><path d="M5 21V10h5v11"></path><path d="M10 21V4h6v17"></path><path d="M16 21v-8h3v8"></path><path d="M12.5 8h1M12.5 11.5h1M12.5 15h1M7 13.5h1M7 17h1"></path>
            @break
        @case('village')
            <path d="M3 11.5L12 4l9 7.5"></path><path d="M5.5 9.5V20h13V9.5"></path><path d="M10 20v-5.5h4V20"></path>
            @break
        @case('nature')
            <path d="M2.5 20l6.5-11 4 6.5 2.5-3.5 6 8z"></path><circle cx="17" cy="6" r="2"></circle>
            @break
        @case('monument')
            <path d="M3.5 9L12 4l8.5 5z"></path><path d="M6 9v9M10 9v9M14 9v9M18 9v9"></path><path d="M3.5 18h17M2.5 21h19"></path>
            @break
        @default
            <path d="M12 21s-6.5-5.8-6.5-11a6.5 6.5 0 0 1 13 0c0 5.2-6.5 11-6.5 11z"></path><circle cx="12" cy="10" r="2.3"></circle>
    @endswitch
</svg>
