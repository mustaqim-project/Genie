<?php

namespace Tests\Unit;

use App\Services\Hrm\FaceVerificationService;
use Tests\TestCase;

class FaceVerificationServiceTest extends TestCase
{
    protected FaceVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FaceVerificationService();
    }

    public function test_identical_vectors_produce_perfect_cosine_similarity(): void
    {
        $vector = array_fill(0, 128, 0.088);
        $score = $this->service->calculateCosineSimilarity($vector, $vector);
        $this->assertEqualsWithDelta(1.0, $score, 0.0001);

        $result = $this->service->compareVectors($vector, $vector);
        $this->assertTrue($result['verified']);
        $this->assertEquals(1.0, $result['score']);
        $this->assertEquals(0.0, $result['distance']);
    }

    public function test_orthogonal_vectors_produce_zero_cosine_similarity(): void
    {
        $vecA = array_fill(0, 128, 0.0);
        $vecA[0] = 1.0;

        $vecB = array_fill(0, 128, 0.0);
        $vecB[1] = 1.0;

        $similarity = $this->service->calculateCosineSimilarity($vecA, $vecB);
        $this->assertEqualsWithDelta(0.0, $similarity, 0.0001);

        $result = $this->service->compareVectors($vecA, $vecB);
        $this->assertFalse($result['verified']);
        $this->assertEquals(0.0, $result['score']);
    }

    public function test_dissimilar_vectors_fail_threshold_verification(): void
    {
        $vecA = array_fill(0, 128, 0.5);
        $vecB = array_fill(0, 128, -0.5);

        $result = $this->service->compareVectors($vecA, $vecB);
        $this->assertFalse($result['verified']);
        $this->assertLessThan(FaceVerificationService::DEFAULT_MATCH_THRESHOLD, $result['score']);
    }

    public function test_vector_parsing_supports_array_and_json(): void
    {
        $arrayData = [0.1, 0.2, 0.3];
        $this->assertEquals([0.1, 0.2, 0.3], $this->service->parseVector($arrayData));

        $jsonData = json_encode([0.4, 0.5, 0.6]);
        $this->assertEquals([0.4, 0.5, 0.6], $this->service->parseVector($jsonData));

        $this->assertNull($this->service->parseVector(null));
        $this->assertNull($this->service->parseVector(''));
    }

    public function test_dimension_mismatch_fails_gracefully(): void
    {
        $vec128 = array_fill(0, 128, 0.1);
        $vec64 = array_fill(0, 64, 0.1);

        $result = $this->service->compareVectors($vec128, $vec64);
        $this->assertFalse($result['verified']);
        $this->assertEquals(0.0, $result['score']);
        $this->assertStringContainsString('Dimensi vektor tidak sesuai', $result['message']);
    }

    public function test_zero_vector_handled_safely_without_division_by_zero(): void
    {
        $zeroVec = array_fill(0, 128, 0.0);
        $normalVec = array_fill(0, 128, 0.2);

        $similarity = $this->service->calculateCosineSimilarity($zeroVec, $normalVec);
        $this->assertEquals(0.0, $similarity);

        $result = $this->service->compareVectors($zeroVec, $normalVec);
        $this->assertFalse($result['verified']);
        $this->assertEquals(0.0, $result['score']);
    }
}
