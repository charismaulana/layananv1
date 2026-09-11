<?php

namespace Database\Seeders;

use App\Models\{Role, Region, MealType, MealLocation, WorkerStatus, Company,
                Department, CutoffSetting, User, MealCard, AppSetting};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Roles (3 Role Utama: User, GS, Catering) ──
        $roles = [
            ['id' => 1, 'name' => 'User',     'slug' => 'user',     'description' => 'Pekerja / Pengguna Sistem'],
            ['id' => 2, 'name' => 'GS',       'slug' => 'gs',       'description' => 'General Services & Administrator Sistem'],
            ['id' => 3, 'name' => 'Catering', 'slug' => 'catering', 'description' => 'Vendor Pengelola Dapur Katering'],
        ];
        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        // ── 2. Wilayah Operasional Field Ramba (4 Wilayah) ──
        $regions = [
            ['name' => 'Ramba',       'slug' => 'ramba'],
            ['name' => 'Bentayan',    'slug' => 'bentayan'],
            ['name' => 'Mangunjaya',  'slug' => 'mangunjaya'],
            ['name' => 'Keluang',     'slug' => 'keluang'],
        ];
        foreach ($regions as $r) {
            Region::updateOrCreate(['slug' => $r['slug']], [
                'name'      => $r['name'],
                'slug'      => $r['slug'],
                'is_active' => true,
            ]);
        }

        // ── 3. Jenis Waktu Makan (4 Waktu Makan) ──
        $mealTypes = [
            ['name' => 'Breakfast', 'slug' => 'breakfast', 'sort_order' => 1, 'shift_only' => false],
            ['name' => 'Lunch',     'slug' => 'lunch',     'sort_order' => 2, 'shift_only' => false],
            ['name' => 'Dinner',    'slug' => 'dinner',    'sort_order' => 3, 'shift_only' => false],
            ['name' => 'Supper',    'slug' => 'supper',    'sort_order' => 4, 'shift_only' => true],
        ];
        foreach ($mealTypes as $mt) {
            MealType::updateOrCreate(['slug' => $mt['slug']], $mt);
        }

        // ── 4. Lokasi Mess Hall & Titik Pengantaran per Wilayah ──
        $regionModels = Region::all()->keyBy('slug');

        $regionLocations = [
            'ramba' => [
                ['name' => 'Mess Hall Staff Ramba',        'is_active' => true],
                ['name' => 'Mess Hall Nonstaff Ramba',     'is_active' => true],
                ['name' => 'Kantor Main Office Ramba',     'is_active' => true],
                ['name' => 'SP Central Ramba',             'is_active' => true],
                ['name' => 'Pos Security Main Office Ramba','is_active' => true],
            ],
            'bentayan' => [
                ['name' => 'Mess Hall Bentayan',           'is_active' => true],
                ['name' => 'Kantor Bentayan',              'is_active' => true],
                ['name' => 'SP Bentayan',                  'is_active' => true],
                ['name' => 'Pos Security Bentayan',        'is_active' => true],
            ],
            'mangunjaya' => [
                ['name' => 'Mess Hall Mangunjaya',         'is_active' => true],
                ['name' => 'Kantor Mangunjaya',            'is_active' => true],
                ['name' => 'SP Mangunjaya',                'is_active' => true],
                ['name' => 'Pos Security Mangunjaya',      'is_active' => true],
            ],
            'keluang' => [
                ['name' => 'Mess Hall Keluang',            'is_active' => true],
                ['name' => 'Kantor Keluang',               'is_active' => true],
                ['name' => 'SP Keluang',                   'is_active' => true],
                ['name' => 'Pos Security Keluang',         'is_active' => true],
            ],
        ];

        foreach ($regionLocations as $regSlug => $locs) {
            $reg = $regionModels->get($regSlug);
            if (!$reg) continue;
            foreach ($locs as $l) {
                MealLocation::updateOrCreate(
                    ['region_id' => $reg->id, 'name' => $l['name']],
                    ['slug' => Str::slug($l['name']), 'is_active' => $l['is_active']]
                );
            }
        }

        // ── 5. Status Hubungan Kerja (Worker Status) ──
        $workerStatuses = ['Pekerja', 'TA', 'TKJP', 'Sub Contractor', 'Visitor'];
        foreach ($workerStatuses as $ws) {
            WorkerStatus::updateOrCreate(['slug' => Str::slug($ws)], ['name' => $ws]);
        }

        // ── 6. Perusahaan & Fungsi / Departemen Kerja Resmi Field Ramba ──
        $pertamina = Company::updateOrCreate(['code' => 'PEP'], [
            'name'      => 'PT Pertamina EP',
            'is_active' => true,
        ]);

        $officialFungsi = [
            'GS'            => 'General Services',
            'HSSE'          => 'Health, Safety, Security & Environment',
            'HSSE-SECURITY' => 'Security Field Ramba',
            'PO'            => 'Production Operation',
            'RAM'           => 'Reliability, Availability & Maintainability',
            'WS'            => 'Well Intervention & Workover Services',
            'WS-Rig01'      => 'Well Intervention Rig 01',
            'WS-Rig02'      => 'Well Intervention Rig 02',
            'WS-Rig03'      => 'Well Intervention Rig 03',
            'WS-Rig06'      => 'Well Intervention Rig 06',
            'FM'            => 'Facility Maintenance',
            'SCM'           => 'Supply Chain Management',
            'ICT'           => 'Information & Communication Technology',
            'Relation'      => 'Legal & Relations',
            'PE'            => 'Petroleum Engineering',
            'Land Ops'      => 'Land Operation',
            'CID'           => 'Community Involvement & Development',
            'Others'        => 'Lain-lain / Fungsi Pendukung',
        ];

        $deptGS = null;
        foreach ($officialFungsi as $code => $fullName) {
            $d = Department::updateOrCreate(
                ['name' => $code],
                ['code' => strtoupper(Str::slug($code)), 'is_active' => true]
            );
            if ($code === 'GS') {
                $deptGS = $d;
            }
        }

        // ── 7. Pengaturan Cut-off Sistem (H-1 Pukul 19:00 WIB) ──
        CutoffSetting::updateOrCreate(['name' => 'default'], [
            'cutoff_days_before' => 1,
            'cutoff_time'        => '19:00:00',
            'timezone'           => 'Asia/Jakarta',
            'is_active'          => true,
        ]);

        // ── 8. Pengaturan Default Tanda Tangan PDF Manifest ──
        AppSetting::set('catering_vendor_name', 'PT Brylian Indah');
        AppSetting::set('gs_officer_name', 'Dedy B / Marnita / Charis M');
        AppSetting::set('gs_officer_title', 'Jr. Officer Facilities Ramba');

        // ── 9. Akun Pengguna Utama: 1 AKUN ROLE GS ──
        $rambaRegion = Region::where('slug', 'ramba')->first();
        $messHallStaff = MealLocation::where('name', 'Mess Hall Staff Ramba')->first();
        $roleGS = Role::where('slug', 'gs')->first();
        $statusPekerja = WorkerStatus::where('name', 'Pekerja')->first();

        $gsUser = User::updateOrCreate(
            ['email' => 'gs@ramba.pertamina.com'],
            [
                'name'                 => 'General Services Ramba',
                'nomor_pegawai'        => 'GS-001',
                'password'             => Hash::make('password'),
                'must_change_password' => false,
                'role_id'              => $roleGS->id,
                'company_id'           => $pertamina->id,
                'department_id'        => $deptGS?->id,
                'worker_status_id'     => $statusPekerja?->id,
                'homebase_region_id'   => $rambaRegion?->id,
                'meal_location_id'     => $messHallStaff?->id,
                'is_shift'             => false,
                'is_active'            => true,
                'registration_status'  => 'active',
            ]
        );

        // Buat kartu makan aktif untuk akun GS
        if (!$gsUser->mealCard) {
            MealCard::create([
                'user_id'   => $gsUser->id,
                'token'     => MealCard::generateToken(),
                'is_active' => true,
            ]);
        }

        $this->command->info('====================================================');
        $this->command->info('✅ SEEDER BERHASIL DIJALANKAN DENGAN BERSIH & LENGKAP');
        $this->command->info('====================================================');
        $this->command->info('1 Akun Akses Utama (Role GS):');
        $this->command->table(
            ['Nama', 'Email', 'Password', 'Role', 'Wilayah', 'Fungsi'],
            [[
                $gsUser->name,
                $gsUser->email,
                'password',
                $roleGS->name,
                $rambaRegion->name,
                $deptGS?->name ?? 'GS',
            ]]
        );
        $this->command->info('Wilayah: Ramba, Bentayan, Mangunjaya, Keluang');
        $this->command->info('Mess Hall & Titik Pengantaran: Lengkap per 4 wilayah');
        $this->command->info('Fungsi / Departemen: 18 Fungsi resmi Field Ramba');
    }
}
