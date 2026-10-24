<?php

namespace App\Services\Biometrics;

/**
 * Compara un vector contra los candidatos con similitud coseno. Los vectores
 * llegan normalizados desde el motor, así que el producto punto basta.
 */
class FaceMatcher
{
    public const MATCHED = 'matched';

    public const LOW_CONFIDENCE = 'low_confidence';

    public const AMBIGUOUS = 'ambiguous';

    public const UNKNOWN = 'unknown';

    public const NO_CANDIDATES = 'no_candidates';

    public function __construct(
        private ?float $matchThreshold = null,
        private ?float $unknownThreshold = null,
        private ?float $ambiguityMargin = null,
    ) {
        $this->matchThreshold ??= (float) config('biometrics.match_threshold');
        $this->unknownThreshold ??= (float) config('biometrics.unknown_threshold');
        $this->ambiguityMargin ??= (float) config('biometrics.ambiguity_margin');
    }

    /**
     * @param  list<float>  $probe
     * @param  array<int, list<float>>  $candidates  id del estudiante => vector
     * @return array{result: string, student_id: ?int, score: ?float, runner_up: ?float}
     */
    public function match(array $probe, array $candidates): array
    {
        if ($candidates === []) {
            return ['result' => self::NO_CANDIDATES, 'student_id' => null, 'score' => null, 'runner_up' => null];
        }

        $scores = [];
        foreach ($candidates as $studentId => $vector) {
            $scores[$studentId] = self::cosine($probe, $vector);
        }
        arsort($scores);

        $bestId = array_key_first($scores);
        $best = $scores[$bestId];
        $runnerUp = count($scores) > 1 ? array_values($scores)[1] : null;

        $result = match (true) {
            $best < $this->unknownThreshold => self::UNKNOWN,
            $best < $this->matchThreshold => self::LOW_CONFIDENCE,
            $runnerUp !== null && ($best - $runnerUp) < $this->ambiguityMargin => self::AMBIGUOUS,
            default => self::MATCHED,
        };

        return [
            'result' => $result,
            // Solo se revela a quién se parece cuando la coincidencia es firme.
            'student_id' => $result === self::MATCHED ? $bestId : null,
            'score' => round($best, 4),
            'runner_up' => $runnerUp === null ? null : round($runnerUp, 4),
        ];
    }

    public static function cosine(array $a, array $b): float
    {
        $count = min(count($a), count($b));
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
