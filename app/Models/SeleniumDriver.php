<?php

namespace App\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Database\Eloquent\Model;

class SeleniumDriver extends Model
{
    protected $table = 'selenium_drivers';
    protected $fillable = ['host', 'port', 'browser', 'version', 'status', 'name', 'last_usage', 'duration', 'is_working', 'working_subject','is_alive', 'working_data'];

    protected $appends = ['driver_url'];

    public function getDriverUrlAttribute(): string
    {
        $host = $this->host;
        $port = $this->port;
        $host = rtrim($host, '/');
        $port = rtrim($port, '/');
        // add http:// if not present
        if (!preg_match("~^(?:f|ht)tps?://~i", $host)) {
            $host = "http://" . $host;
        }
        return "{$host}:{$port}";
    }

    public function setLastUsage(string $lastUsage): void
    {
        $this->last_usage = $lastUsage;
        $this->save();
    }

    public function getLastUsage()
    {
        return $this->last_usage;
    }

    public function setDuration($duration): void
    {
        $this->duration = $duration;
        $this->save();
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setWorkingSubject(string $workingSubject): void
    {
        $this->working_subject = $workingSubject;
        $this->save();
    }

    public function getWorkingSubject(): string|null
    {
        return $this->working_subject;
    }

    public function setIsAlive(bool $isAlive): void
    {
        $this->is_alive = $isAlive;
        $this->save();
    }

    public function getIsAlive(): bool
    {
        return $this->is_alive;
    }

    public function setIsWorking(bool $isWorking): void
    {
        $this->is_working = $isWorking;
        $this->save();
    }

    public function getIsWorking(): bool
    {
        return $this->is_working;
    }

    public function setWorkingData(array $workingData): void
    {
        $this->working_data = $workingData;
        $this->save();
    }

    public function getWorkingData(): array
    {
        return $this->working_data;
    }


    public function alive(): bool
    {
        try {
            $url = $this->getDriverUrlAttribute() . '/status';

            $client = new Client();
            $promise = $client->getAsync($url);
            $response = Utils::unwrap([$promise])[0];

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function available(): bool
    {
        return !$this->getIsWorking();
    }

    public function casts(): array
    {
        return [
            'working_data' => 'array',
            'port' => 'int',
            'duration' => 'int',
        ];
    }
}
