<?php

namespace App\Http\Controllers\v1;

use App\Factories\CategoriesCrawlerFactory;
use App\Helpers\SlugHelper;
use App\Http\Controllers\Controller;
use App\Jobs\StoreCategoriesJob;
use Illuminate\Http\Request;

class CrawlerApiController extends Controller
{
    public function getCategoriesTesting(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'merchant_id' => 'required',
            'merchant_url' => 'required',
        ]);

        $startGoogleDriverTime = microtime(true);
        $merchantId = $request->merchant_id;
        $crawler = (new CategoriesCrawlerFactory())->runFactory($merchantId);
        if(!$crawler){
            return response()->json('merchant not found', 404);
        }
        $result = $crawler->test($request->all(), $merchantId);
        $endGoogleDriverTime = microtime(true);
        if($result instanceof \Throwable){
            return response()->json($result->getMessage(), 400);
        }
        return response()->json([
            'result' => $result,
            'time' => $endGoogleDriverTime - $startGoogleDriverTime
        ]);
    }

    public function getCategoriesCrawling(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'merchant_id' => 'required',
            'merchant_url' => 'required',
        ]);

        $merchantId = $request->merchant_id;
        $crawler = (new CategoriesCrawlerFactory())->runFactory($merchantId);
        if(!$crawler){
            return response()->json('merchant not found', 404);
        }

        // Initialize the SlugHelper
        $slugHelper = new SlugHelper();
        $generatedSlug = $slugHelper->createRandomSlug(); // Generate a random slug

        // Dispatch the job to the queue
        StoreCategoriesJob::dispatch($request->all(), $generatedSlug);

        return response()->json(['message' => 'Crawling job has been dispatched.', 'slug' => $generatedSlug]);
    }
}
