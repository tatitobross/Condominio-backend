<?php

namespace Database\Seeders;

use App\Models\{Home, Incident, Payment, Resident, Visit};
use App\Models\User;
use Illuminate\Database\Seeder;

class CondominiumDataSeeder extends Seeder
{
    public function run(): void
    {
        $homes = collect([
            ['code' => 'A-101', 'block' => 'Torre A', 'address' => 'Apartamento 101', 'status' => 'active'],
            ['code' => 'A-102', 'block' => 'Torre A', 'address' => 'Apartamento 102', 'status' => 'active'],
            ['code' => 'B-201', 'block' => 'Torre B', 'address' => 'Apartamento 201', 'status' => 'active'],
        ])->map(fn (array $data) => Home::updateOrCreate(['code' => $data['code']], $data));

        $residents = collect([
            ['home_id' => $homes[0]->id, 'user_id' => User::where('username', 'residente')->value('id'), 'name' => 'Ana López', 'dpi' => '0101200012345', 'phone' => '5555-0101', 'email' => 'ana@condominio.test', 'status' => 'active'],
            ['home_id' => $homes[1]->id, 'name' => 'Carlos Méndez', 'dpi' => '0101200012346', 'phone' => '5555-0102', 'email' => 'carlos@condominio.test', 'status' => 'active'],
            ['home_id' => $homes[2]->id, 'name' => 'María Ruiz', 'dpi' => '0101200012347', 'phone' => '5555-0103', 'email' => 'maria@condominio.test', 'status' => 'active'],
        ])->map(fn (array $data) => Resident::updateOrCreate(['dpi' => $data['dpi']], $data));

        foreach (['2026-10', '2026-11', '2026-12'] as $index => $period) {
            Payment::updateOrCreate(
                ['home_id' => $homes[$index]->id, 'period' => $period],
                ['due_date' => now()->addMonths($index)->startOfMonth()->addDays(5), 'amount' => 850, 'status' => $index === 0 ? 'pending' : 'overdue', 'reference' => null],
            );
        }

        Visit::updateOrCreate(['visitor_dpi' => '0101200099999'], ['home_id' => $homes[0]->id, 'resident_id' => $residents[0]->id, 'visitor_name' => 'Laura Gómez', 'vehicle_plate' => 'P-123ABC', 'scheduled_at' => now()->addHour(), 'status' => 'pending']);
        Visit::updateOrCreate(['visitor_dpi' => '0101200099998'], ['home_id' => $homes[1]->id, 'resident_id' => $residents[1]->id, 'visitor_name' => 'Diego Pérez', 'vehicle_plate' => 'P-456DEF', 'scheduled_at' => now()->subHour(), 'status' => 'authorized']);
        Incident::updateOrCreate(['title' => 'Lámpara dañada en acceso'], ['home_id' => $homes[0]->id, 'reported_by' => User::where('username', 'admin')->value('id'), 'description' => 'La iluminación del ingreso principal requiere revisión.', 'priority' => 'medium', 'status' => 'open', 'reported_at' => now()->subDay()]);
    }
}
