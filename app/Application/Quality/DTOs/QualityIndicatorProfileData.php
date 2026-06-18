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

    /** @var string|null */
    public $rationale;

    /** @var string|null */
    public $indicator_type;

    /** @var string|null */
    public $measurement_unit;

    /** @var string|null */
    public $formula;

    /** @var string|null */
    public $data_collection_method;

    /** @var string|null */
    public $instrument;

    /** @var string|null */
    public $sample_size;

    /** @var string|null */
    public $sampling_method;

    /** @var string|null */
    public $data_presentation;

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
        string $standard,
        ?string $rationale = null,
        ?string $indicator_type = null,
        ?string $measurement_unit = null,
        ?string $formula = null,
        ?string $data_collection_method = null,
        ?string $instrument = null,
        ?string $sample_size = null,
        ?string $sampling_method = null,
        ?string $data_presentation = null
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
        $this->rationale = $rationale;
        $this->indicator_type = $indicator_type;
        $this->measurement_unit = $measurement_unit;
        $this->formula = $formula;
        $this->data_collection_method = $data_collection_method;
        $this->instrument = $instrument;
        $this->sample_size = $sample_size;
        $this->sampling_method = $sampling_method;
        $this->data_presentation = $data_presentation;
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
            $data['standard'],
            $data['rationale'] ?? null,
            $data['indicator_type'] ?? null,
            $data['measurement_unit'] ?? null,
            $data['formula'] ?? null,
            $data['data_collection_method'] ?? null,
            $data['instrument'] ?? null,
            $data['sample_size'] ?? null,
            $data['sampling_method'] ?? null,
            $data['data_presentation'] ?? null
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
            'rationale'                       => $this->rationale,
            'indicator_type'                  => $this->indicator_type,
            'measurement_unit'                => $this->measurement_unit,
            'formula'                         => $this->formula,
            'data_collection_method'          => $this->data_collection_method,
            'instrument'                      => $this->instrument,
            'sample_size'                     => $this->sample_size,
            'sampling_method'                 => $this->sampling_method,
            'data_presentation'               => $this->data_presentation,
        ];
    }
}
