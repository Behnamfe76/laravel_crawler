<?php
namespace App\Jobs;

use App\Factories\CategoriesCrawlerFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

    public function handle(): void
    {
        ini_set('max_execution_time', 0);
        $validated = $this->validated;
        $merchantId = $validated['merchant_id'];

        $crawler = (new CategoriesCrawlerFactory())->runFactory($merchantId);
        $crawler->run($validated, $merchantId, $this->generatedSlug);
    }

}
