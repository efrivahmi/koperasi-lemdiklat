@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel')
    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle;">
            <rect width="32" height="32" rx="8" fill="#4F46E5"/>
            <path d="M16 8v16M8 16h16" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
            <circle cx="16" cy="16" r="4" fill="#ffffff"/>
        </svg>
        <span style="font-size: 18px; font-weight: bold; color: #3d4852; vertical-align: middle; margin-left: 8px;">
            Koperasi Lemdiklat
        </span>
    </div>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
