<?php

namespace App\Entities;

use App\Models\SeleniumDriver;
use Illuminate\Support\Facades\Log;

class SeleniumDriverEntity
{
    private array $driverEntities = [];

    public function get()
    {
        if (empty($this->driverEntities)) {
            $this->fetch();
        }
        return $this->getRoundRobinMode();
    }

    public function fetch(): void
    {
        $driverEntities = SeleniumDriver::query()
            ->where('status', 'active')
            ->get();

        $this->driverEntities = [];
        foreach ($driverEntities as $driverEntity) {
            Log::info('Checking driver entity', ['url' => $driverEntity->getDriverUrlAttribute()]);

            $isAvailable = $driverEntity->available();
            Log::info('Driver availability', ['url' => $driverEntity->getDriverUrlAttribute(), 'available' => $isAvailable]);

            $isAlive = $driverEntity->alive();
            Log::info('Driver alive status', ['url' => $driverEntity->getDriverUrlAttribute(), 'alive' => $isAlive]);

            if ($isAvailable && $isAlive) {
                Log::info('Setting driver entity', ['url' => $driverEntity->getDriverUrlAttribute()]);
                $this->set($driverEntity);
            }
        }
    }

    public function set(SeleniumDriver $driverEntity): void
    {
        $this->driverEntities[] = $driverEntity;
    }

    private function getRoundRobinMode()
    {
        if (empty($this->driverEntities)) {
            return null;
        }
        $driverEntity = array_shift($this->driverEntities);
        $this->driverEntities[] = $driverEntity;
        return $driverEntity;
    }

    public function checkAlive(SeleniumDriver $driverEntity): bool
    {
        return $driverEntity->alive();
    }

    private function checkAvailable(SeleniumDriver $driverEntity): bool
    {
        return $driverEntity->available();
    }
}
