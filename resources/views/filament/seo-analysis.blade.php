{{-- Live Google snippet preview + Rank Math-style checks (rendered inside the Filament post form) --}}
<div style="display: flex; flex-direction: column; gap: 1rem;">

    {{-- SERP preview --}}
    <div style="border: 1px solid rgba(128,128,128,.25); border-radius: .75rem; padding: 1rem; background: #fff;">
        <div style="font-size: .75rem; color: #4d5156; display: flex; align-items: center; gap: .5rem;">
            <span style="width: 26px; height: 26px; border-radius: 50%; background: #f1f3f4; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; color: #5f6368;">T</span>
            <span>
                <span style="display: block; color: #202124;">{{ config('app.name') }}</span>
                <span style="display: block; color: #4d5156; word-break: break-all;">{{ $serp['url'] }}</span>
            </span>
        </div>
        <div style="color: #1a0dab; font-size: 1.125rem; line-height: 1.3; margin-top: .35rem;">
            {{ \Illuminate\Support\Str::limit($serp['title'], 60) }}
        </div>
        <div style="color: #4d5156; font-size: .85rem; line-height: 1.45; margin-top: .2rem;">
            {{ \Illuminate\Support\Str::limit($serp['description'], 160) }}
        </div>
    </div>

    {{-- Checklist --}}
    <ul style="display: flex; flex-direction: column; gap: .45rem; font-size: .85rem; margin: 0; padding: 0; list-style: none;">
        @foreach ($checks as $check)
            <li style="display: flex; align-items: flex-start; gap: .5rem;">
                @if ($check['status'] === 'pass')
                    <span style="color: #16a34a; font-weight: 700;">✓</span>
                @elseif ($check['status'] === 'fail')
                    <span style="color: #dc2626; font-weight: 700;">✗</span>
                @else
                    <span style="color: #d97706; font-weight: 700;">!</span>
                @endif
                <span>{{ $check['label'] }}</span>
            </li>
        @endforeach
    </ul>
</div>
