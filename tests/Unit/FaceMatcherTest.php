<?php

namespace Tests\Unit;

use App\Services\Biometrics\FaceMatcher;
use PHPUnit\Framework\TestCase;

class FaceMatcherTest extends TestCase
{
    private function vector(array $values): array
    {
        return array_pad($values, 128, 0.0);
    }

    private function matcher(): FaceMatcher
    {
        return new FaceMatcher(matchThreshold: 0.363, unknownThreshold: 0.25, ambiguityMargin: 0.05);
    }

    public function test_cosine_of_identical_vectors_is_one(): void
    {
        $v = $this->vector([0.3, 0.4, 0.5]);
        $this->assertEqualsWithDelta(1.0, FaceMatcher::cosine($v, $v), 1e-9);
    }

    public function test_cosine_handles_zero_vectors(): void
    {
        $this->assertSame(0.0, FaceMatcher::cosine($this->vector([]), $this->vector([1.0])));
    }

    public function test_no_candidates(): void
    {
        $this->assertSame(FaceMatcher::NO_CANDIDATES, $this->matcher()->match($this->vector([1.0]), [])['result']);
    }

    public function test_clear_match_returns_student(): void
    {
        $result = $this->matcher()->match($this->vector([1.0]), [
            7 => $this->vector([0.9, 0.1]),
            8 => $this->vector([0.0, 1.0]),
        ]);

        $this->assertSame(FaceMatcher::MATCHED, $result['result']);
        $this->assertSame(7, $result['student_id']);
    }

    public function test_score_between_thresholds_is_low_confidence_and_hides_identity(): void
    {
        // coseno = 0.3 -> entre 0.25 y 0.363
        $result = $this->matcher()->match($this->vector([1.0]), [5 => $this->vector([0.3, sqrt(1 - 0.09)])]);

        $this->assertSame(FaceMatcher::LOW_CONFIDENCE, $result['result']);
        $this->assertNull($result['student_id']);
    }

    public function test_low_score_is_unknown(): void
    {
        $result = $this->matcher()->match($this->vector([1.0]), [5 => $this->vector([0.1, 0.99])]);

        $this->assertSame(FaceMatcher::UNKNOWN, $result['result']);
        $this->assertNull($result['student_id']);
    }

    public function test_two_close_candidates_are_ambiguous(): void
    {
        $result = $this->matcher()->match($this->vector([1.0]), [
            1 => $this->vector([0.80, 0.60]),
            2 => $this->vector([0.79, 0.0, 0.61]),
        ]);

        $this->assertSame(FaceMatcher::AMBIGUOUS, $result['result']);
        $this->assertNull($result['student_id']);
    }
}
