<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Factories\CategoriesCrawlerFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Facebook\WebDriver\Exception\WebDriverException;

class StoreCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $timeout = 1200;
    protected array $validated;
    protected string $generatedSlug;

    public function __construct(array $validated, string $generatedSlug)
    {
        $this->validated = $validated;
        $this->generatedSlug = $generatedSlug;
    }
    public function getData()
    {
        return array_merge($this->validated, ['generated_slug' => $this->generatedSlug]);
    }
    public function handle(): void
    {
        try {
            $validated = $this->validated;
            $merchantId = $validated['merchant_id'];

            $crawler = (new CategoriesCrawlerFactory())->runFactory($merchantId);
            $result = $crawler->run($validated, $merchantId, $this->generatedSlug);

            if ($result instanceof WebDriverException || $result instanceof \Throwable) {
                throw $result;
            }
            
        } catch (\Throwable $e) {
            Log::error("Job Failed: " . $e->getMessage(), [
                'merchant_id' => $merchantId ?? null,
                'exception' => $e
            ]);

            throw $e;
        }
    }
}
