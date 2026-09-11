<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CutoffSetting;
use Illuminate\Http\Request;

class CutoffController extends Controller
{
    public function index()
    {
        $cutoff = CutoffSetting::where('is_active', true)->first();
        return view('admin.cutoff.index', compact('cutoff'));
    }

    public function edit(CutoffSetting $cutoff)
    {
        return view('admin.cutoff.edit', compact('cutoff'));
    }

    public function update(Request $request, CutoffSetting $cutoff)
    {
        $validated = $request->validate([
            'cutoff_days_before' => 'required|integer|min:0|max:7',
            'cutoff_time'        => 'required|date_format:H:i',
        ]);
        $cutoff->update($validated);
        return redirect()->route('admin.master.cutoff.index')->with('success', 'Pengaturan cutoff diperbarui.');
    }
}
