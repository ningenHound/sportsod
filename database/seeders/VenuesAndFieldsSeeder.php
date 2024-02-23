<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\Field;
use App\Models\FieldType;
use Illuminate\Database\Seeder;

class VenuesAndFieldsSeeder extends Seeder
{

    public function run() {
        $field_type1 = FieldType::create(['description' => 'Fútbol de campo']);
        $field_type2 = FieldType::create(['description' => 'Fútbol de salón']);
        $field_type3 = FieldType::create(['description' => 'Rugby']);
        $field_type4 = FieldType::create(['description' => 'Volley']);
        $field_type5 = FieldType::create(['description' => 'Basketball']);
        $field_type6 = FieldType::create(['description' => 'Tennis']);
        $field_type7 = FieldType::create(['description' => 'Padel']);
        $field_type8 = FieldType::create(['description' => 'Golf']);
        $venue1 = Venue::create(['description' => 'Asunción Golf Club', 'address' => 'calle 1 y calle2']);
        $field_venue1 = Field::create(['field_type'=> $field_type8->id, 'venue_id' => $venue1->id]);
        $venue2 = Venue::create(['description' => 'Seminario Metropolitano', 'address' => 'Kubitschek y 25 de mayo']);
        $field_venue2 = Field::create(['field_type'=> $field_type1->id, 'venue_id' => $venue2->id]);
        $venue3 = Venue::create(['description' => 'Rowing club', 'address' => 'Washington casi Juan de Salazar']);
        $field_venue3 = Field::create(['field_type'=> $field_type5->id, 'venue_id' => $venue3->id]);

    }
}