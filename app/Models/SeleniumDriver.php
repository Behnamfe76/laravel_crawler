<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeleniumDriver extends Model
{
    protected $fillable = ['host', 'port', 'browser', 'version', 'status', 'name', 'last_usage', 'duration'];

    protected $appends = ['driver_url'];

    public function getDriverUrlAttribute()
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
}
