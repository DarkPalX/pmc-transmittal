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
        'destination' => 'Project Site A', 'prepared_by' => 'Test User', 'status' => 'Draft',
        'details' => [['item_desc' => 'Inspection report', 'item_unit' => 'copy', 'item_serial_no' => '', 'remarks' => '']],
    ])->assertRedirect();

    $this->assertDatabaseHas('transmittal_header', ['ref_no' => 'PMC-OUT-TEST-001', 'transmittal_type' => 'Outgoing']);
    $this->assertDatabaseHas('transmittal_details', ['item_desc' => 'Inspection report']);
});

it('renders the printable incoming form', function () {
    $user = User::factory()->create();
    $header = \App\Models\TransmittalHeader::create(['ref_no' => 'PMC-IN-TEST-001', 'transmittal_date' => '2026-10-20', 'transmittal_type' => 'Incoming', 'destination' => 'Main Office', 'prepared_by' => 'Test User', 'status' => 'Received']);
    $this->actingAs($user)->get(route('transmittals.print', $header))->assertOk()->assertSee('INCOMING TRANSMITTAL');
});

it('updates a transmittal status directly from the index action', function () {
    $user = User::factory()->create();
    $header = \App\Models\TransmittalHeader::create([
        'ref_no' => 'PMC-STATUS-TEST-001', 'transmittal_date' => '2026-10-20',
        'transmittal_type' => 'Outgoing', 'destination' => 'Project Site A',
        'prepared_by' => 'Test User', 'status' => 'Draft',
    ]);

    $this->actingAs($user)->patch(route('transmittals.status', $header), ['status' => 'Received'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($header->fresh()->status)->toBe('Received');
});
