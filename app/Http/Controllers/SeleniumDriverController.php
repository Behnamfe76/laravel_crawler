<?php

namespace App\Http\Controllers;

use App\Models\SeleniumDriver;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SeleniumDriverController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('SeleniumDrivers/Index', [
            'drivers' => SeleniumDriver::all()
        ]);
    }
    public function create(): \Inertia\Response
    {
        return Inertia::render('SeleniumDrivers/Create');
    }
    public function edit(int $driverId): \Inertia\Response
    {
        $driver = SeleniumDriver::findOrFail($driverId);

        return Inertia::render('SeleniumDrivers/Edit', [
            'driver' => $driver
        ]);
    }
    public function update(Request $request, int $driverId)
    {
        $request->validate([
            'driverName' => 'required',
            'host' => 'required',
            'port' => 'required',
        ]);

        SeleniumDriver::findOrFail($driverId)
            ->update([
                'name' => $request->driverName,
                'host' => $request->host,
                'port' => $request->port,
            ]);

        return redirect()->route('dashboard.selenium-drivers.index');
    }
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'driverName' => 'required',
            'host' => 'required',
            'port' => 'required',
        ]);

        SeleniumDriver::create([
            'name' => $request->driverName,
            'host' => $request->host,
            'port' => $request->port,
        ]);

        return redirect()->route('dashboard.selenium-drivers.index');
    }
    public function checkDriverStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'driverUrl' => 'required',
        ]);
        $url = $request->driverUrl . '/status';

        $client = new Client();
        $promise = $client->getAsync($url);
        $response = Utils::unwrap([$promise])[0];

        if ($response->getStatusCode() === 200) {
            return response()->json([
                'message' => "✅ Selenium WebDriver is running!\n",
                'response' => $response
            ], 200);
        } else {
            return response()->json("❌ Cannot connect to Selenium WebDriver.", 400);
        }
    }
    public function resetDrivers(): void
    {
        $driverEntities = SeleniumDriver::all();
        foreach ($driverEntities as $driverEntity) {
            $isAlive = $driverEntity->alive();
            if ($isAlive) {
                $driverEntity->setIsWorking(false);
                $driverEntity->setIsAlive(false);
            }
        }
    }
    public function checkDriversAlive(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'driverPort' => 'required',
            'driverHost' => 'required',
        ]);
        $port = $request->driverPort;
        $host = $request->driverHost;

        $driverEntity = SeleniumDriver::where('port', $port)->where('host', $host)->first();
        $isAlive = $driverEntity->alive();
        $driverEntity->setIsAlive($isAlive);

        if (!$isAlive) {
            return response()->json('not alive', 422);
        }
        return response()->json('is alive', 200);
    }
    public function checkDriversWorking(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'driverPort' => 'required',
            'driverHost' => 'required',
        ]);
        $port = $request->driverPort;
        $host = $request->driverHost;

        $driverEntity = SeleniumDriver::where('port', $port)->where('host', $host)->first();
        $isWorking = $driverEntity->getIsWorking();


        return response()->json([
            'isWorking' => $isWorking,
            'workingSubject' => $driverEntity->getWorkingSubject(),
            'duration' => $driverEntity->getDuration(),
            'lastUsage' => $driverEntity->getLastUsage(),
        ], 200);
    }

    public function checkSeleniumsStatus() {
        $reports = [];

        $seleniums = SeleniumDriver::all();
        foreach ($seleniums as $selenium) {
            $reports[] = [
                'selenium' => $selenium,
                'is_available' => $selenium->alive(),
                'is_working' => $selenium->getIsWorking(),
                'working_subject' => $selenium->getWorkingSubject(),
                'duration' => $selenium->getDuration(),
                'last_usage' => $selenium->getLastUsage(),
            ];
        }

        return response()->json([
            $reports,
        ]);
    }
}
