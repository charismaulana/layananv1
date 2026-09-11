@php $c = $active ? '#006738' : '#9ca3af'; $f = $active ? 'currentColor' : 'none'; $sz = $size ?? 20; @endphp
@if($name === 'home')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
@elseif($name === 'calendar')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
@elseif($name === 'arrow')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
@elseif($name === 'menu')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="none" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v10M8 8v4a2 2 0 002 2m4-6v4a2 2 0 002 2"/></svg>
@elseif($name === 'qr')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
@elseif($name === 'plan' || $name === 'meal')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
@elseif($name === 'doc')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
@elseif($name === 'users')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
@elseif($name === 'plus')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/></svg>
@elseif($name === 'master' || $name === 'database')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
@elseif($name === 'chart' || $name === 'dashboard')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
@elseif($name === 'star' || $name === 'feedback')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
@elseif($name === 'bed')
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10v9m18-9v9M3 14h18M3 10a2 2 0 012-2h14a2 2 0 012 2M7 8V6a1 1 0 011-1h2a1 1 0 011 1v2m4 0V6a1 1 0 011-1h2a1 1 0 011 1v2"/></svg>
@else
<svg width="{{ $sz }}" height="{{ $sz }}" fill="{{ $f }}" stroke="{{ $c }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
@endif
