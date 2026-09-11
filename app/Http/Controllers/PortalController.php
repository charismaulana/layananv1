<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $services = [
            [
                'id'          => 'catering',
                'title'       => 'Layanan Katering & Makan',
                'subtitle'    => 'Ramba Meal Planning System',
                'icon'        => '🍱',
                'color'       => '#006738',
                'bg_gradient' => 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)',
                'border'      => '#86efac',
                'badge'       => 'Aktif & Beroperasi',
                'badge_class' => 'badge-green',
                'desc'        => 'Pengelolaan jadwal makan harian, perencanaan roster kerja/libur, permohonan perpindahan lokasi makan, outside meal, manifest dapur, dan evaluasi katering.',
                'action_type' => 'link',
                'url'         => route('dashboard'),
                'btn_text'    => 'Masuk Layanan Katering',
                'is_active'   => true,
            ],
            [
                'id'          => 'vehicle',
                'title'       => 'Layanan Pemesanan Kendaraan',
                'subtitle'    => 'Vehicle Request',
                'icon'        => '🚗',
                'color'       => '#1d4ed8',
                'bg_gradient' => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
                'border'      => '#93c5fd',
                'badge'       => 'Dalam Pengembangan',
                'badge_class' => 'badge-gold',
                'desc'        => 'Permohonan dan penjadwalan peminjaman kendaraan ringan penumpang untuk operasional dan crew change.',
                'action_type' => 'modal',
                'modal_msg'   => 'Layanan pemesanan kendaraan dinas saat ini masih dalam tahap pengembangan sistem. Untuk kebutuhan operasional mendesak, silakan langsung menghubungi tim General Services (GS).',
                'btn_text'    => 'Pesan Kendaraan',
                'is_active'   => false,
            ],
            [
                'id'          => 'facility',
                'title'       => 'Layanan Perbaikan Fasum',
                'subtitle'    => 'Facility Repair & Maintenance',
                'icon'        => '🛠️',
                'color'       => '#0f766e',
                'bg_gradient' => 'linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%)',
                'border'      => '#5eead4',
                'badge'       => 'Dalam Pengembangan',
                'badge_class' => 'badge-gold',
                'desc'        => 'Pengajuan perbaikan dan pemeliharaan fasilitas kantor, akomodasi camp, sarana ibadah, mess hall, dan fasilitas umum lainnya di Ramba Field.',
                'action_type' => 'modal',
                'modal_msg'   => 'Layanan pelaporan & perbaikan fasilitas umum saat ini masih dalam tahap pengembangan sistem. Untuk kebutuhan perbaikan sarana, silakan langsung menghubungi tim General Services (GS).',
                'btn_text'    => 'Ajukan Perbaikan',
                'is_active'   => false,
            ],
            [
                'id'          => 'ac',
                'title'       => 'Layanan Perbaikan AC',
                'subtitle'    => 'Air Conditioner Service & Maintenance',
                'icon'        => '❄️',
                'color'       => '#0369a1',
                'bg_gradient' => 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)',
                'border'      => '#7dd3fc',
                'badge'       => 'Dalam Pengembangan',
                'badge_class' => 'badge-gold',
                'desc'        => 'Pengajuan perbaikan dan pemeliharaan unit pendingin ruangan (AC) kantor, akomodasi camp, maupun fasilitas produksi.',
                'action_type' => 'modal',
                'modal_msg'   => 'Layanan pemeliharaan & perbaikan AC saat ini masih dalam tahap pengembangan sistem. Untuk kebutuhan servis atau perbaikan AC, silakan langsung menghubungi tim General Services (GS).',
                'btn_text'    => 'Ajukan Servis AC',
                'is_active'   => false,
            ],
        ];

        return view('portal.index', compact('user', 'services'));
    }
}
