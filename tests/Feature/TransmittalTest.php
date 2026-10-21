<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('requires authentication to access the transmittal workspace', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('allows an authenticated user to create an outgoing transmittal with details', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('transmittals.store'), [
        'ref_no' => 'PMC-OUT-TEST-001', 'transmittal_date' => '2026-10-20', 'transmittal_type' => 'Outgoing',
        'destination' => 'Project Site A', 'prepared_by' => 'Test User', 'status' => 'Saved',
        'details' => [['item_qty' => 1, 'item_desc' => 'Inspection report', 'item_unit' => 'copy', 'item_serial_no' => '', 'remarks' => '']],
    ])->assertRedirect();

    $this->assertDatabaseHas('transmittal_header', ['ref_no' => 'PMC-OUT-TEST-001', 'transmittal_type' => 'Outgoing']);
    $this->assertDatabaseHas('transmittal_details', ['item_desc' => 'Inspection report']);
    $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'description' => 'Transmittal created.']);
});

it('persists editable quantities and accepts the received status', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('transmittals.store'), [
        'ref_no' => 'PMC-RECEIVED-TEST-001', 'transmittal_date' => '2026-10-20', 'transmittal_type' => 'Outgoing',
        'destination' => 'Project Site A', 'prepared_by' => 'Test User', 'status' => 'Saved',
        'details' => [['item_qty' => 20, 'item_desc' => 'Wire', 'item_unit' => 'meter', 'item_serial_no' => '', 'remarks' => '']],
    ])->assertRedirect();

    $header = \App\Models\TransmittalHeader::where('ref_no', 'PMC-RECEIVED-TEST-001')->firstOrFail();
    expect((float) $header->details()->first()->item_qty)->toBe(20.0);

    $this->actingAs($user)->patch(route('transmittals.status', $header), ['status' => 'Received'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($header->fresh()->status)->toBe('Received');
});

it('renders the printable incoming form', function () {
    $user = User::factory()->create();
    $header = \App\Models\TransmittalHeader::create(['ref_no' => 'PMC-IN-TEST-001', 'transmittal_date' => '2026-10-20', 'transmittal_type' => 'Incoming', 'destination' => 'Main Office', 'prepared_by' => 'Test User', 'status' => 'Transmitted']);
    $this->actingAs($user)->get(route('transmittals.print', $header))->assertOk()->assertSee('ITEM DESCRIPTION')->assertSee("Receiver");
});

it('updates a transmittal status directly from the index action', function () {
    $user = User::factory()->create();
    $header = \App\Models\TransmittalHeader::create([
        'ref_no' => 'PMC-STATUS-TEST-001', 'transmittal_date' => '2026-10-20',
        'transmittal_type' => 'Outgoing', 'destination' => 'Project Site A',
        'prepared_by' => 'Test User', 'status' => 'Saved',
    ]);

    $this->actingAs($user)->patch(route('transmittals.status', $header), ['status' => 'Transmitted'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($header->fresh()->status)->toBe('Transmitted');
});

it('locks non-saved transmittals for regular users but allows superadmins to edit them', function () {
    $user = User::factory()->create();
    $header = \App\Models\TransmittalHeader::create([
        'ref_no' => 'PMC-LOCK-TEST-001', 'transmittal_date' => '2026-10-20',
        'transmittal_type' => 'Outgoing', 'destination' => 'Project Site A',
        'prepared_by' => 'Test User', 'status' => 'Received',
    ]);

    $this->actingAs($user)->get(route('transmittals.edit', $header))->assertForbidden();

    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->actingAs($superadmin)->get(route('transmittals.edit', $header))->assertOk();
});

it('catalogs incoming items for later outgoing selection', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('transmittals.store'), [
        'ref_no' => 'PMC-IN-CATALOG-001', 'transmittal_date' => '2026-10-20', 'transmittal_type' => 'Incoming',
        'destination' => 'Main Office', 'prepared_by' => 'Test User', 'status' => 'Transmitted',
        'details' => [['item_qty' => 1, 'item_desc' => 'Laptop computer', 'item_unit' => 'pc', 'item_serial_no' => 'SN-001', 'par_no' => 'PAR-001', 'remarks' => '']],
    ])->assertRedirect();

    $this->assertDatabaseHas('transmittal_details', ['item_desc' => 'Laptop computer', 'par_no' => 'PAR-001']);
    $item = \App\Models\Item::where('description', 'Laptop computer')->first();
    expect($item)->not->toBeNull()
        ->and($item->serial_no)->toBe('SN-001')
        ->and($item->par_no)->toBe('PAR-001')
        ->and($item->transmittal_date->format('Y-m-d'))->toBe('2026-10-20');
});

it('keeps same descriptions as separate catalog items when serial numbers differ', function () {
    $user = User::factory()->create();
    $payload = [
        'transmittal_date' => '2026-10-20', 'transmittal_type' => 'Incoming',
        'destination' => 'Main Office', 'prepared_by' => 'Test User', 'status' => 'Transmitted',
        'details' => [['item_qty' => 1, 'item_desc' => 'Desktop computer', 'item_unit' => 'pc', 'item_serial_no' => 'PC-001', 'par_no' => 'PAR-101', 'remarks' => '']],
    ];

    $this->actingAs($user)->post(route('transmittals.store'), $payload + ['ref_no' => 'PMC-DUP-001']);
    $payload['details'][0]['item_serial_no'] = 'PC-002';
    $payload['details'][0]['par_no'] = 'PAR-102';
    $this->actingAs($user)->post(route('transmittals.store'), $payload + ['ref_no' => 'PMC-DUP-002']);

    expect(\App\Models\Item::where('description', 'Desktop computer')->count())->toBe(2);
    expect(\App\Models\Item::where('description', 'Desktop computer')->pluck('serial_no')->sort()->values()->all())
        ->toBe(['PC-001', 'PC-002']);
});
