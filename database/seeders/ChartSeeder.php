<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartsData;

class ChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '10',
            'time' => '2023-04-26T16:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '14',
            'time' => '2023-04-25T18:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '16',
            'time' => '2023-04-24T11:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '18',
            'time' => '2023-04-27T13:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '20',
            'time' => '2023-04-22T13:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '2',
            'value' => '30',
            'time' => '2023-04-27T20:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '2',
            'value' => '38',
            'time' => '2023-04-27T14:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '2',
            'value' => '40',
            'time' => '2023-04-25T17:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '3',
            'value' => '50',
            'time' => '2023-04-21T10:06:10.000000Z',
            'type' => 'Temperature',
        ]);
        ChartsData::create([
            'sensor_id' => '3',
            'value' => '55',
            'time' => '2023-04-25T11:06:10.000000Z',
            'type' => 'Temperature',
        ]);



        ChartsData::create([
            'sensor_id' => '1',
            'value' => '60',
            'time' => '2023-04-08T11:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '80',
            'time' => '2023-04-10T18:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '110',
            'time' => '2023-04-14T16:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '1',
            'value' => '130',
            'time' => '2023-04-14T19:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '2',
            'value' => '160',
            'time' => '2023-04-14T23:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '2',
            'value' => '180',
            'time' => '2023-04-17T12:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '2',
            'value' => '150',
            'time' => '2023-04-18T16:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '3',
            'value' => '170',
            'time' => '2023-04-20T16:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '3',
            'value' => '175',
            'time' => '2023-04-23T16:06:10.000000Z',
            'type' => 'Humidity',
        ]);
        ChartsData::create([
            'sensor_id' => '3',
            'value' => '210',
            'time' => '2023-04-25T16:06:10.000000Z',
            'type' => 'Humidity',
        ]);
    }
}
