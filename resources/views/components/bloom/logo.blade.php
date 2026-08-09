@props(['class' => 'h-10 w-10'])

<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" aria-hidden="true">
    <defs>
        <linearGradient id="bloom-logo-g" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FBCFE8"/>
            <stop offset="0.5" stop-color="#F472B6"/>
            <stop offset="1" stop-color="#EC4899"/>
        </linearGradient>
    </defs>

    <rect x="2" y="2" width="60" height="60" rx="20" fill="url(#bloom-logo-g)"/>
    <rect x="2" y="2" width="60" height="60" rx="20" stroke="white" stroke-opacity="0.4" stroke-width="2"/>

    <g transform="translate(32 32)">
        <ellipse cx="0" cy="-14" rx="7" ry="11" fill="white" opacity="0.95"/>
        <ellipse cx="13.3" cy="-4.3" rx="7" ry="11" fill="white" opacity="0.9" transform="rotate(72 13.3 -4.3)"/>
        <ellipse cx="8.2" cy="11.3" rx="7" ry="11" fill="white" opacity="0.9" transform="rotate(144 8.2 11.3)"/>
        <ellipse cx="-8.2" cy="11.3" rx="7" ry="11" fill="white" opacity="0.9" transform="rotate(216 -8.2 11.3)"/>
        <ellipse cx="-13.3" cy="-4.3" rx="7" ry="11" fill="white" opacity="0.9" transform="rotate(288 -13.3 -4.3)"/>
        <circle cx="0" cy="0" r="6.5" fill="#EC4899"/>
        <circle cx="0" cy="0" r="3" fill="#FBCFE8"/>
    </g>
</svg>
