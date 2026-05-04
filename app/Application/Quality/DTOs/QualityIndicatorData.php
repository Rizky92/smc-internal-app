<?php

namespace App\Application\Quality\DTOs;

class QualityIndicatorData
{
    /** @var int|null */
    public $id;

    /** @var int */
    public $bidang_id;

    /** @var int */
    public $quality_indicator_category_id;

    /** @var int */
    public $sort_order;

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
    public $quality_indicator_input_type_id;

    /** @var int|null */
    public $analysis_period;

    /** @var string|null */
    public $numerator;

    /** @var string|null */
    public $denominator;

    /** @var string|null */
    public $data_source;

    /** @var string */
    public $standard;

    /** @var string|null */
    public $person_in_charge;

    /** @var string */
    public $status;

    public function __construct(
        ?int $id,
        int $bidang_id,
        int $quality_indicator_category_id,
        int $sort_order,
        string $title,
        ?string $dimension,
        ?string $objective,
        ?string $definition,
        ?string $inclusion,
        ?string $exclusion,
        string $frequency,
        ?int $quality_indicator_input_type_id,
        ?int $analysis_period,
        ?string $numerator,
        ?string $denominator,
        ?string $data_source,
        string $standard,
        ?string $person_in_charge,
        string $status = 'active'
    ) {
        $this->id = $id;
        $this->bidang_id = $bidang_id;
        $this->quality_indicator_category_id = $quality_indicator_category_id;
        $this->sort_order = $sort_order;
        $this->title = $title;
        $this->dimension = $dimension;
        $this->objective = $objective;
        $this->definition = $definition;
        $this->inclusion = $inclusion;
        $this->exclusion = $exclusion;
        $this->frequency = $frequency;
        $this->quality_indicator_input_type_id = $quality_indicator_input_type_id;
        $this->analysis_period = $analysis_period;
        $this->numerator = $numerator;
        $this->denominator = $denominator;
        $this->data_source = $data_source;
        $this->standard = $standard;
        $this->person_in_charge = $person_in_charge;
        $this->status = $status;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int) $data['bidang_id'],
            (int) $data['quality_indicator_category_id'],
            (int) ($data['sort_order'] ?? 1),
            $data['title'],
            $data['dimension'] ?? null,
            $data['objective'] ?? null,
            $data['definition'] ?? null,
            $data['inclusion'] ?? null,
            $data['exclusion'] ?? null,
            $data['frequency'],
            isset($data['quality_indicator_input_type_id']) ? (int) $data['quality_indicator_input_type_id'] : null,
            isset($data['analysis_period']) ? (int) $data['analysis_period'] : null,
            $data['numerator'] ?? null,
            $data['denominator'] ?? null,
            $data['data_source'] ?? null,
            $data['standard'],
            $data['person_in_charge'] ?? null,
            $data['status'] ?? 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'id'                              => $this->id,
            'bidang_id'                       => $this->bidang_id,
            'quality_indicator_category_id'   => $this->quality_indicator_category_id,
            'sort_order'                      => $this->sort_order,
            'title'                           => $this->title,
            'dimension'                       => $this->dimension,
            'objective'                       => $this->objective,
            'definition'                      => $this->definition,
            'inclusion'                       => $this->inclusion,
            'exclusion'                       => $this->exclusion,
            'frequency'                       => $this->frequency,
            'quality_indicator_input_type_id' => $this->quality_indicator_input_type_id,
            'analysis_period'                 => $this->analysis_period,
            'numerator'                       => $this->numerator,
            'denominator'                     => $this->denominator,
            'data_source'                     => $this->data_source,
            'standard'                        => $this->standard,
            'person_in_charge'                => $this->person_in_charge,
            'status'                          => $this->status,
        ];
    }
}
