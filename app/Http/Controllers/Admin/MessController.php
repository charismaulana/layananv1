<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Region, Room};
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessController extends Controller
{
    public function index(Request $request)
    {
        $date      = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        $today     = Carbon::today();
        $regionId  = $request->query('region');

        // Ambil semua wilayah yang memiliki kamar aktif
        $regions = Region::whereHas('rooms', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        // Query kamar: filter wilayah jika dipilih, eager-load penghuni + roster pada tanggal dipilih
        $roomsQuery = Room::with([
                'region',
                'users' => function ($q) use ($date) {
                    $q->where('is_active', true)
                      ->where('registration_status', 'active')
                      ->with([
                          'rosters' => fn($r) => $r->whereDate('roster_date', $date),
                          'department',
                          'company',
                      ]);
                },
            ])
            ->where('is_active', true)
            ->orderBy('region_id')
            ->orderBy('block')
            ->orderBy('name');

        if ($regionId) {
            $roomsQuery->where('region_id', $regionId);
        }

        $rooms = $roomsQuery->get();

        // Summary stats
        $totalRooms     = $rooms->count();
        $totalOccupants = $rooms->sum(fn($r) => $r->users->count());

        // Hitung penghuni berdasarkan status roster hari ini
        $totalKerja  = 0;
        $totalLibur  = 0;
        $totalNone   = 0;

        foreach ($rooms as $room) {
            foreach ($room->users as $u) {
                $roster = $u->rosters->first(); // roster hari ini (max 1 per hari)
                if (!$roster) {
                    $totalNone++;
                } elseif ($roster->status === 'Kerja') {
                    $totalKerja++;
                } else {
                    $totalLibur++;
                }
            }
        }

        // Group kamar per wilayah untuk tampilan
        $roomsByRegion = $rooms->groupBy('region_id');

        return view('admin.mess.index', compact(
            'regions', 'rooms', 'roomsByRegion',
            'date', 'today', 'regionId',
            'totalRooms', 'totalOccupants',
            'totalKerja', 'totalLibur', 'totalNone'
        ));
    }
}
