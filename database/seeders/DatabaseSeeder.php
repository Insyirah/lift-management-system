<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Company;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\Lift;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if the database is empty (safe for container restarts)
        if (User::count() > 0) {
            return;
        }

        // Super Admin
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
        ]);

        // Company
        $company = Company::create([
            'name'            => 'ElevaSafe Sdn Bhd',
            'registration_no' => 'ES-2024-001',
            'address'         => 'No. 12, Jalan Industri 3, 47500 Subang Jaya, Selangor',
            'phone'           => '+60 3-5123 4567',
            'email'           => 'info@elevasafe.com.my',
        ]);

        // Admin user for company
        $admin = User::create([
            'name'       => 'Nuraiman Abd Rahman',
            'email'      => 'admin@test.com',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
            'company_id' => $company->id,
            'phone'      => '+60 12-345 6789',
        ]);

        // Inspector user
        $inspector = User::create([
            'name'        => 'Nuzul Arzan',
            'email'       => 'inspector@elevasafe.com.my',
            'password'    => Hash::make('password'),
            'role'        => 'inspector',
            'company_id'  => $company->id,
            'phone'       => '+60 11-234 5678',
            'cert_number' => 'JKKP-INS-2022-0045',
            'cert_expiry' => '2026-12-31',
        ]);

        // Organisation
        $organisation = Organisation::create([
            'company_id'      => $company->id,
            'name'            => 'Menara KL Holdings Sdn Bhd',
            'registration_no' => 'MKL-0012345-W',
            'address'         => 'Lot 3, Kompleks Menara, 50088 Kuala Lumpur',
            'contact_person'  => 'Encik Faizal Hamid',
            'contact_phone'   => '+60 3-2123 4567',
            'email'           => 'faizal@menarakl.com.my',
        ]);

        // Buildings
        $building1 = Building::create([
            'organisation_id'  => $organisation->id,
            'name'             => 'Menara KL Tower A',
            'address'          => 'Jalan Parlimen, 50480 Kuala Lumpur',
            'number_of_floors' => 40,
            'year_built'       => 2005,
        ]);

        $building2 = Building::create([
            'organisation_id'  => $organisation->id,
            'name'             => 'Menara KL Tower B',
            'address'          => 'Jalan Parlimen, 50480 Kuala Lumpur',
            'number_of_floors' => 28,
            'year_built'       => 2010,
        ]);

        // Lifts
        $lift1 = Lift::create([
            'building_id'       => $building1->id,
            'lift_code'         => 'MKL-A-LFT-001',
            'lift_type'         => 'passenger',
            'brand'             => 'OTIS',
            'model'             => 'Gen2',
            'serial_number'     => 'OT2024A001',
            'capacity'          => 1000,
            'installation_date' => '2005-06-15',
            'status'            => 'active',
        ]);

        $lift2 = Lift::create([
            'building_id'       => $building1->id,
            'lift_code'         => 'MKL-A-LFT-002',
            'lift_type'         => 'cargo',
            'brand'             => 'Schindler',
            'model'             => '5500',
            'serial_number'     => 'SC2024A002',
            'capacity'          => 2000,
            'installation_date' => '2005-06-15',
            'status'            => 'active',
        ]);

        $lift3 = Lift::create([
            'building_id'       => $building2->id,
            'lift_code'         => 'MKL-B-LFT-001',
            'lift_type'         => 'passenger',
            'brand'             => 'KONE',
            'model'             => 'MonoSpace',
            'serial_number'     => 'KN2024B001',
            'capacity'          => 800,
            'installation_date' => '2010-03-20',
            'status'            => 'active',
        ]);

        // Inspection checklist items
        $items = [
            // Safety Devices
            ['category' => 'Safety Devices', 'name' => 'Safety gear / governor rope', 'sort_order' => 1],
            ['category' => 'Safety Devices', 'name' => 'Buffer condition', 'sort_order' => 2],
            ['category' => 'Safety Devices', 'name' => 'Overspeed governor', 'sort_order' => 3],
            ['category' => 'Safety Devices', 'name' => 'Final limit switches', 'sort_order' => 4],
            ['category' => 'Safety Devices', 'name' => 'Door lock contacts', 'sort_order' => 5],

            // Mechanical Components
            ['category' => 'Mechanical Components', 'name' => 'Wire rope condition', 'sort_order' => 1],
            ['category' => 'Mechanical Components', 'name' => 'Guide rail lubrication', 'sort_order' => 2],
            ['category' => 'Mechanical Components', 'name' => 'Machine room equipment', 'sort_order' => 3],
            ['category' => 'Mechanical Components', 'name' => 'Drive sheave condition', 'sort_order' => 4],
            ['category' => 'Mechanical Components', 'name' => 'Brake operation', 'sort_order' => 5],

            // Electrical Systems
            ['category' => 'Electrical Systems', 'name' => 'Control panel condition', 'sort_order' => 1],
            ['category' => 'Electrical Systems', 'name' => 'Lighting in car and pit', 'sort_order' => 2],
            ['category' => 'Electrical Systems', 'name' => 'Emergency power operation', 'sort_order' => 3],
            ['category' => 'Electrical Systems', 'name' => 'Earth bonding / continuity', 'sort_order' => 4],

            // Door Operation
            ['category' => 'Door Operation', 'name' => 'Car door operation', 'sort_order' => 1],
            ['category' => 'Door Operation', 'name' => 'Landing door operation', 'sort_order' => 2],
            ['category' => 'Door Operation', 'name' => 'Door reopening device', 'sort_order' => 3],
            ['category' => 'Door Operation', 'name' => 'Door closing force', 'sort_order' => 4],

            // General
            ['category' => 'General', 'name' => 'Load test (rated capacity)', 'sort_order' => 1],
            ['category' => 'General', 'name' => 'JKKP lift log book', 'sort_order' => 2],
            ['category' => 'General', 'name' => 'Emergency communication', 'sort_order' => 3],
            ['category' => 'General', 'name' => 'Signage and notices', 'sort_order' => 4],
        ];

        foreach ($items as $item) {
            InspectionItem::create(array_merge($item, [
                'description' => null,
                'is_active'   => true,
            ]));
        }

        // Sample Inspection
        $inspection = Inspection::create([
            'lift_id'         => $lift1->id,
            'user_id'         => $inspector->id,
            'assigned_by'     => $admin->id,
            'inspection_date' => now()->toDateString(),
            'next_due_date'   => now()->addYear()->toDateString(),
            'inspection_type' => 'annual',
            'status'          => 'pending',
            'notes'           => 'Annual JKKP inspection for Tower A Lift 001.',
        ]);

        $allItems = InspectionItem::all();
        foreach ($allItems as $item) {
            $inspection->results()->create([
                'inspection_item_id' => $item->id,
                'result'             => null,
            ]);
        }
    }
}
