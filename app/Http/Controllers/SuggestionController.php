<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function create()
    {
        $mySuggestions = Suggestion::where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        return view('suggestion.create', compact('mySuggestions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'     => 'required|in:Menu,Rasa,Porsi,Distribusi,Kebersihan,Lainnya,menu,quality,portion,service,schedule,system,other',
            'title'        => 'nullable|string|max:255',
            'content'      => 'required|string|max:2000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $categoryMap = [
            'menu'       => 'Menu',
            'quality'    => 'Kebersihan',
            'portion'    => 'Porsi',
            'service'    => 'Distribusi',
            'schedule'   => 'Distribusi',
            'system'     => 'Lainnya',
            'other'      => 'Lainnya',
        ];

        $category = $categoryMap[$validated['category']] ?? $validated['category'];
        if (!in_array($category, ['Menu', 'Rasa', 'Porsi', 'Distribusi', 'Kebersihan', 'Lainnya'])) {
            $category = 'Lainnya';
        }

        $fullContent = $validated['content'];
        if (!empty($validated['title'])) {
            $fullContent = "【" . $validated['title'] . "】\n" . $fullContent;
        }

        Suggestion::create([
            'user_id'   => auth()->id(),
            'region_id' => auth()->user()->homebase_region_id,
            'category'  => $category,
            'content'   => $fullContent,
            'is_read'   => false,
        ]);

        return redirect()->route('suggestion.create')
            ->with('success', 'Terima kasih! Saran & masukan Anda telah berhasil dikirimkan ke tim katering dan pengelola GS.');
    }
}
