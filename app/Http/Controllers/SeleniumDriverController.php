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
    public function defineDrivers(): \Inertia\Response
    {
        return Inertia::render('SeleniumDrivers/Create');
    }
    public function storeDrivers(Request $request): \Illuminate\Http\RedirectResponse
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

        return redirect()->route('dashboard.selenium-drivers');
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

        $driverEntity = DB::table('selenium_drivers')->where('port', $port)->where('host', $host)->first();
        $isWorking = $driverEntity->is_working;

        if (!$isWorking) {
            return response()->json('not working', 422);
        }
        return response()->json('is working', 200);
    }
}
