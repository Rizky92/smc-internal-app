<?php

namespace App\Application\Quality\DTOs;

class QualityIndicatorProfileData
{
    /** @var int|null */
    public $id;

    /** @var int */
    public $quality_indicator_category_id;

    /** @var int|null */
    public $quality_indicator_input_type_id;

    /** @var string */
    public $title;

    /** @var string|null */
    public $dimension;

    /** @var string|null */
    public $objective;

    /** @var string|null */
    public $definition;

    /** @var string|null */
    public $inclusion;

    /** @var string|null */
    public $exclusion;

    /** @var string */
    public $frequency;

    /** @var int|null */
    public $analysis_period;

    /** @var string|null */
    public $numerator;

    /** @var string|null */
    public $denominator;

    /** @var string */
    public $standard;

    public function __construct(
        ?int $id,
        int $quality_indicator_category_id,
        ?int $quality_indicator_input_type_id,
        string $title,
        ?string $dimension,
        ?string $objective,
        ?string $definition,
        ?string $inclusion,
        ?string $exclusion,
        string $frequency,
        ?int $analysis_period,
        ?string $numerator,
        ?string $denominator,
        string $standard
    ) {
        $this->id = $id;
        $this->quality_indicator_category_id = $quality_indicator_category_id;
        $this->quality_indicator_input_type_id = $quality_indicator_input_type_id;
        $this->title = $title;
        $this->dimension = $dimension;
        $this->objective = $objective;
        $this->definition = $definition;
        $this->inclusion = $inclusion;
        $this->exclusion = $exclusion;
        $this->frequency = $frequency;
        $this->analysis_period = $analysis_period;
        $this->numerator = $numerator;
        $this->denominator = $denominator;
        $this->standard = $standard;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int) $data['quality_indicator_category_id'],
            isset($data['quality_indicator_input_type_id']) ? (int) $data['quality_indicator_input_type_id'] : null,
            $data['title'],
            $data['dimension'] ?? null,
            $data['objective'] ?? null,
            $data['definition'] ?? null,
            $data['inclusion'] ?? null,
            $data['exclusion'] ?? null,
            $data['frequency'],
            isset($data['analysis_period']) ? (int) $data['analysis_period'] : null,
            $data['numerator'] ?? null,
            $data['denominator'] ?? null,
            $data['standard']
        );
    }

    public function toArray(): array
    {
        return [
            'id'                              => $this->id,
            'quality_indicator_category_id'   => $this->quality_indicator_category_id,
            'quality_indicator_input_type_id' => $this->quality_indicator_input_type_id,
            'title'                           => $this->title,
            'dimension'                       => $this->dimension,
            'objective'                       => $this->objective,
            'definition'                      => $this->definition,
            'inclusion'                       => $this->inclusion,
            'exclusion'                       => $this->exclusion,
            'frequency'                       => $this->frequency,
            'analysis_period'                 => $this->analysis_period,
            'numerator'                       => $this->numerator,
            'denominator'                     => $this->denominator,
            'standard'                        => $this->standard,
        ];
    }
}
