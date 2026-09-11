{{--
    Partial: layouts/_card.blade.php
    Usage: @include('layouts._card', ['title'=>'...', 'slot'=>$slot])
--}}

{{-- ── Reusable Blade Components & CSS Design System ── --}}
<style>
/* ── Design Tokens ── */
:root {
    --g: #006738; --g-dk: #004d28; --g-lt: #e8f5ee; --g-md: #d1ead9;
    --red: #E32529; --red-lt: #fef2f2;
    --gold: #F5A623; --gold-lt: #fffbeb;
    --blue: #1d4ed8; --blue-lt: #eff6ff;
    --gray: #f4f6f8; --border: #e8edf2;
    --text: #1a2332; --muted: #6b7280; --faint: #9ca3af;
}
/* ── Card ── */
.card { background:#fff; border-radius:14px; border:1px solid var(--border); overflow:hidden; }
.card-header {
    padding:14px 20px; border-bottom:1px solid #f3f4f6;
    display:flex; align-items:center; justify-content:space-between;
}
.card-header h3 { font-size:14px; font-weight:700; color:var(--text); margin:0; }
.card-body { padding:20px; }
/* ── Table ── */
.tbl { width:100%; border-collapse:collapse; font-size:13px; }
.tbl th { background:#f9fafb; padding:10px 14px; text-align:left; font-size:11px; font-weight:700; color:var(--faint); text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); }
.tbl td { padding:11px 14px; border-bottom:1px solid #f9fafb; color:var(--text); vertical-align:middle; }
.tbl tr:last-child td { border-bottom:none; }
.tbl tr:hover td { background:#fafafa; }
/* ── Badge ── */
.badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; }
.badge-green { background:var(--g-lt); color:var(--g); }
.badge-red { background:var(--red-lt); color:var(--red); }
.badge-gold { background:var(--gold-lt); color:#92400e; }
.badge-blue { background:var(--blue-lt); color:var(--blue); }
.badge-gray { background:#f3f4f6; color:#374151; }
/* ── Form Controls ── */
.form-group { margin-bottom:18px; }
.form-label { display:block; font-size:12.5px; font-weight:600; color:#374151; margin-bottom:6px; }
.form-input, .form-select, .form-textarea {
    width:100%; border:1.5px solid var(--border); border-radius:10px;
    padding:10px 14px; font-size:13.5px; color:var(--text);
    background:#fff; outline:none; font-family:inherit;
    transition:all .2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.form-select {
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%234b5563' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px 16px;
    padding-right: 38px;
    line-height: 1.4;
}
.form-select option {
    padding: 10px 14px;
    font-size: 13.5px;
    color: #1a2332;
    background: #fff;
}
.form-input:hover, .form-select:hover, .form-textarea:hover {
    border-color: #cbd5e1;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    border-color: var(--g);
    box-shadow: 0 0 0 3.5px rgba(0,103,56,.12), 0 1px 2px rgba(0,0,0,0.04);
}
.form-textarea { resize:vertical; min-height:90px; }
.form-hint { font-size:11.5px; color:var(--muted); margin-top:4px; }

/* ── Interactive Buttons & Micro-Animations ── */
.btn {
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
    padding:9.5px 18px; border-radius:10px; font-size:13.5px; font-weight:600;
    cursor:pointer; border:none; font-family:inherit; text-decoration:none;
    position: relative; overflow: hidden;
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
}
.btn:active {
    transform: scale(0.96) !important;
}
.btn-primary {
    background: var(--g); color: #fff;
    box-shadow: 0 2px 4px rgba(0,103,56,0.2), 0 1px 2px rgba(0,0,0,0.05);
}
.btn-primary:hover {
    background: var(--g-dk);
    box-shadow: 0 4px 12px rgba(0,103,56,0.32), 0 2px 4px rgba(0,103,56,0.15);
    transform: translateY(-1.5px);
}
.btn-secondary {
    background: #f3f4f6; color: #374151;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.btn-secondary:hover {
    background: #e5e7eb; color: #111827;
    transform: translateY(-1.5px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.08);
}
.btn-danger {
    background: var(--red-lt); color: var(--red);
    border: 1px solid #fecaca;
    box-shadow: 0 1px 2px rgba(227,37,41,0.06);
}
.btn-danger:hover {
    background: #fee2e2; color: #b91c1c;
    border-color: #fca5a5;
    transform: translateY(-1.5px);
    box-shadow: 0 4px 12px rgba(227,37,41,0.18);
}
.btn-outline {
    background: #fff; color: var(--text);
    border: 1.5px solid var(--border);
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.btn-outline:hover {
    background: #f8fafc; border-color: #cbd5e1;
    transform: translateY(-1.5px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.06);
}
.btn-sm {
    padding: 6px 12px; font-size: 12px; border-radius: 8px;
}
.btn-full { width: 100%; justify-content: center; }
/* ── Status Dot ── */
.dot { display:inline-block; width:7px; height:7px; border-radius:50%; margin-right:5px; }
.dot-green { background:var(--g); }
.dot-red { background:var(--red); }
.dot-gold { background:var(--gold); }
.dot-gray { background:#9ca3af; }
/* ── Section Title ── */
.section-title { font-size:13px; font-weight:700; color:var(--text); margin:0 0 14px; display:flex; align-items:center; gap:8px; }
/* ── Empty State ── */
.empty-state { padding:40px 20px; text-align:center; }
.empty-state svg { margin:0 auto 12px; display:block; }
.empty-state p { font-size:13px; color:var(--faint); margin:0; }
/* ── Pagination override ── */
nav[role="navigation"] { margin-top:16px; }
/* ── Responsive grid ── */
.grid-2 { display:grid; grid-template-columns:1fr; gap:16px; }
/* ── Sidebar Navigation ── */
#sidebar nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    transition: all .15s ease;
    border-left: 3px solid transparent;
}
#sidebar nav a:hover {
    background: #f4f6f8;
    color: #1a2332;
}
#sidebar nav a.active {
    background: #e8f5ee;
    color: #006738;
    font-weight: 700;
    border-left-color: #006738;
}
#sidebar nav a svg {
    flex-shrink: 0;
}
.sidebar-section {
    font-size: 10.5px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .6px;
    padding: 16px 18px 6px;
}
.grid-3 { display:grid; grid-template-columns:1fr; gap:16px; }
.grid-4 { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:640px) { .grid-2 { grid-template-columns:repeat(2,1fr); } .grid-3 { grid-template-columns:repeat(2,1fr); } }
@media(min-width:1024px) { .grid-3 { grid-template-columns:repeat(3,1fr); } .grid-4 { grid-template-columns:repeat(4,1fr); } }
</style>
