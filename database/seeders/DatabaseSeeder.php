<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\TransmittalHeader;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::query()->updateOrCreate(['email' => 'dms@pmc.com'], [
            'name' => 'John Cover',
            'password' => 'password',
            'role' => 'user',
        ]);

        User::query()->updateOrCreate(['email' => 'superadmin@pmc.com'], [
            'name' => 'PMC Superadmin',
            'password' => 'password',
            'role' => 'superadmin',
        ]);

        // $incoming = TransmittalHeader::updateOrCreate(
        //     ['ref_no' => 'PMC-IN-2026-001'],
        //     ['transmittal_date' => '2026-10-18', 'transmittal_type' => 'Incoming', 'destination' => 'PMC Main Office', 'prepared_by' => 'John Cover', 'received_by' => 'Rosemarie Mondejar', 'noted_by' => 'Stephen Jan Venusi', 'status' => 'Transmitted', 'remarks' => 'Initial equipment receipt.']
        // );
        // $incoming->details()->delete();

        // $laptop = Item::updateOrCreate(
        //     ['item_code' => 'IN-LAP-001'],
        //     ['description' => 'Laptop computer', 'unit' => 'pc', 'serial_no' => 'PMC-LAP-001', 'is_active' => true, 'transmittal_date' => '2026-10-18', 'par_no' => 'PAR-2026-001']
        // );
        // $monitor = Item::updateOrCreate(
        //     ['item_code' => 'IN-MON-001'],
        //     ['description' => '24-inch LED monitor', 'unit' => 'pc', 'serial_no' => 'PMC-MON-001', 'is_active' => true, 'transmittal_date' => '2026-10-18', 'par_no' => 'PAR-2026-002']
        // );
        // $incoming->details()->createMany([
        //     ['item_id' => $laptop->id, 'item_desc' => $laptop->description, 'item_unit' => $laptop->unit, 'item_serial_no' => $laptop->serial_no, 'par_no' => $laptop->par_no, 'remarks' => 'For assigned workstation.'],
        //     ['item_id' => $monitor->id, 'item_desc' => $monitor->description, 'item_unit' => $monitor->unit, 'item_serial_no' => $monitor->serial_no, 'par_no' => $monitor->par_no, 'remarks' => 'For assigned workstation.'],
        // ]);

        // $outgoing = TransmittalHeader::updateOrCreate(
        //     ['ref_no' => 'PMC-OUT-2026-001'],
        //     ['transmittal_date' => '2026-10-20', 'transmittal_type' => 'Outgoing', 'destination' => 'Agusan ICT Office', 'plate_no' => 'LOY789', 'prepared_by' => 'John Cover', 'received_by' => 'Rosemarie Mondejar', 'noted_by' => 'Stephen Jan Venusi', 'status' => 'Saved', 'remarks' => 'For transmittal and workstation setup.']
        // );
        // $outgoing->details()->delete();
        // $outgoing->details()->createMany([
        //     ['item_id' => $laptop->id, 'item_desc' => $laptop->description, 'item_unit' => $laptop->unit, 'item_serial_no' => $laptop->serial_no, 'par_no' => $laptop->par_no, 'remarks' => 'For ICT office use.'],
        // ]);

        // $catalog = [
        //     ['code' => 'IN-PRN-001', 'description' => 'Laser printer', 'unit' => 'pc', 'serial' => 'PMC-PRN-001'],
        //     ['code' => 'IN-SCN-001', 'description' => 'Document scanner', 'unit' => 'pc', 'serial' => 'PMC-SCN-001'],
        //     ['code' => 'IN-UPS-001', 'description' => 'UPS 1200VA', 'unit' => 'pc', 'serial' => 'PMC-UPS-001'],
        //     ['code' => 'IN-DSK-001', 'description' => 'Work desk', 'unit' => 'unit', 'serial' => 'PMC-DSK-001'],
        //     ['code' => 'IN-CHR-001', 'description' => 'Office chair', 'unit' => 'unit', 'serial' => 'PMC-CHR-001'],
        //     ['code' => 'IN-RTR-001', 'description' => 'Network router', 'unit' => 'pc', 'serial' => 'PMC-RTR-001'],
        //     ['code' => 'IN-SWH-001', 'description' => '24-port network switch', 'unit' => 'pc', 'serial' => 'PMC-SWH-001'],
        //     ['code' => 'IN-CAM-001', 'description' => 'CCTV camera', 'unit' => 'pc', 'serial' => 'PMC-CAM-001'],
        //     ['code' => 'IN-MIC-001', 'description' => 'Wireless microphone', 'unit' => 'pc', 'serial' => 'PMC-MIC-001'],
        //     ['code' => 'IN-SPK-001', 'description' => 'Portable speaker', 'unit' => 'pc', 'serial' => 'PMC-SPK-001'],
        // ];

        // foreach ($catalog as $index => $entry) {
        //     $date = Carbon::create(2026, 10, 17)->subDays($index);
        //     $item = Item::updateOrCreate(
        //         ['item_code' => $entry['code']],
        //         ['description' => $entry['description'], 'unit' => $entry['unit'], 'serial_no' => $entry['serial'], 'is_active' => true, 'transmittal_date' => $date, 'par_no' => 'PAR-2026-'.str_pad((string) ($index + 3), 3, '0', STR_PAD_LEFT)]
        //     );

        //     $incoming = TransmittalHeader::updateOrCreate(
        //         ['ref_no' => 'PMC-IN-2026-'.str_pad((string) ($index + 2), 3, '0', STR_PAD_LEFT)],
        //         ['transmittal_date' => $date, 'transmittal_type' => 'Incoming', 'destination' => 'PMC Main Office', 'prepared_by' => 'John Cover', 'received_by' => 'Rosemarie Mondejar', 'noted_by' => 'Stephen Jan Venusi', 'status' => 'Transmitted', 'remarks' => 'Incoming catalog item receipt '.($index + 1).'.']
        //     );
        //     $incoming->details()->delete();
        //     $incoming->details()->create([
        //         'item_id' => $item->id, 'item_qty' => 1, 'item_desc' => $item->description,
        //         'item_unit' => $item->unit, 'item_serial_no' => $item->serial_no, 'par_no' => $item->par_no,
        //         'remarks' => 'Received in good condition.',
        //     ]);

        //     $outgoing = TransmittalHeader::updateOrCreate(
        //         ['ref_no' => 'PMC-OUT-2026-'.str_pad((string) ($index + 2), 3, '0', STR_PAD_LEFT)],
        //         ['transmittal_date' => $date->copy()->addDays(1), 'transmittal_type' => 'Outgoing', 'destination' => ['Agusan ICT Office', 'Davao Project Office', 'Main Warehouse'][$index % 3], 'plate_no' => 'PMC-'.str_pad((string) ($index + 101), 3, '0', STR_PAD_LEFT), 'prepared_by' => 'John Cover', 'received_by' => $index % 2 === 0 ? 'Rosemarie Mondejar' : null, 'noted_by' => 'Stephen Jan Venusi', 'status' => $index % 3 === 0 ? 'Transmitted' : 'Saved', 'remarks' => 'Outgoing equipment deployment '.($index + 1).'.']
        //     );
        //     $outgoing->details()->delete();
        //     $outgoing->details()->create([
        //         'item_id' => $item->id, 'item_qty' => 1, 'item_desc' => $item->description,
        //         'item_unit' => $item->unit, 'item_serial_no' => $item->serial_no, 'par_no' => $item->par_no,
        //         'remarks' => 'For receiving and acknowledgment.',
        //     ]);
        // }
    }
}
