<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Ai\OpenAiCustomsService;
use App\Services\Customs\CeisaHsCodeService;

class OpenAiCustomsServiceTest extends TestCase
{
    protected OpenAiCustomsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OpenAiCustomsService::class);
    }

    public function test_it_provides_all_13_requested_ai_models()
    {
        $models = $this->service->getAvailableModels();

        $expectedKeys = [
            'cx/gpt-6-astra-xhigh',
            'cx/gpt-6-astra',
            'cx/gpt-5.6-sol-xhigh',
            'cx/gpt-5.6-sol',
            'cx/gpt-5.6-terra',
            'cx/gpt-5.6-luna',
            'cx/gpt-5.5-xhigh',
            'cx/gpt-5.5',
            'cx/gpt-5.4',
            'ag/gemini-3.8-flash-high',
            'ag/gemini-3.8-flash-medium',
            'ag/gemini-3.8-flash-low',
            'ag/gemini-3.7-flash-high',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $models, "Model {$key} must be registered in available models list.");
        }
    }

    public function test_it_categorizes_models_correctly()
    {
        $categorized = $this->service->getCategorizedModels();

        $this->assertArrayHasKey('OpenAI Codex Astra Series', $categorized);
        $this->assertArrayHasKey('OpenAI Codex Sol & Terra Series', $categorized);
        $this->assertArrayHasKey('OpenAI Codex 5.x Series', $categorized);
        $this->assertArrayHasKey('Google Antigravity Gemini Series', $categorized);
    }

    public function test_it_cleans_and_extracts_json_cleanly()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('cleanJsonResponse');
        $method->setAccessible(true);

        $markdownWrapped = "```json\n{\"hs_code\": \"8452.29.00\", \"uraian\": \"Mesin jahit industri\"}\n```";
        $extracted = $method->invoke($this->service, $markdownWrapped);

        $this->assertNotNull($extracted);
        $this->assertEquals('8452.29.00', $extracted['hs_code']);
        $this->assertEquals('Mesin jahit industri', $extracted['uraian']);
    }

    public function test_shipment_lartas_info_accessor()
    {
        $shipment = new \App\Models\Shipment();
        $info = $shipment->lartas_info;

        $this->assertIsArray($info);
        $this->assertArrayHasKey('has_lartas', $info);
        $this->assertArrayHasKey('total_products', $info);
        $this->assertArrayHasKey('lartas_count', $info);
        $this->assertArrayHasKey('non_lartas_count', $info);
        $this->assertArrayHasKey('primary_lartas', $info);

        $item = new \App\Models\ShipmentItem([
            'kode_hs' => '84713020',
            'uraian_barang' => 'Laptop Gaming',
        ]);
        $shipment->setRelation('items', collect([$item]));
        $infoWithItem = $shipment->lartas_info;

        $this->assertEquals(1, $infoWithItem['total_products']);
        $this->assertArrayHasKey('has_lartas', $infoWithItem);
        $this->assertCount(1, $infoWithItem['lartas_items'] + $infoWithItem['non_lartas_items']);
    }
}
