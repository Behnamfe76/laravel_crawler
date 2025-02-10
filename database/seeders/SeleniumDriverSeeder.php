<?php

namespace Database\Seeders;

use App\Models\SeleniumDriver;
use Illuminate\Database\Seeder;

class SeleniumDriverSeeder extends Seeder
{
    private array $seleniumDrivers = [
        [
            'name' => 'local driver',
            'host' => '127.0.0.1',
            'port' => 4444,
        ],[
            'name' => 'docker node 1',
            'host' => '127.0.0.1',
            'port' => 4441,
        ], [
            'name' => 'docker node 2',
            'host' => '127.0.0.1',
            'port' => 4442,
        ],
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->seleniumDrivers as $seleniumDriver) {
            SeleniumDriver::create($seleniumDriver);
        }
    }
}
