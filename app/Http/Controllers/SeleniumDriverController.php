<?php

namespace App\Http\Controllers;

use App\Models\SeleniumDriver;
use Illuminate\Http\Request;
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
    public function checkDriverStatus(Request $request){
        $request->validate([
            'driverUrl' => 'required',
        ]);
        $url = $request->driverUrl . '/status';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout after 5 seconds
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            return response()->json([
                'message' => "✅ Selenium WebDriver is running!\n",
                'response' => $response
            ], 200);
        } else {
            return response()->json("❌ Cannot connect to Selenium WebDriver.", 400);
        }
    }
}
