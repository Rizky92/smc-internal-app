<?php

namespace App\Application\Quality\DTOs;

class QualityIndicatorData
{
    /** @var int|null */
    public $id;

    /** @var int */
    public $quality_indicator_profile_id;

    /** @var string */
    public $dep_id;

    /** @var string|null */
    public $data_source;

    /** @var string|null */
    public $person_in_charge;

    /** @var string */
    public $status;

    public function __construct(
        ?int $id,
        int $quality_indicator_profile_id,
        string $dep_id,
        ?string $data_source,
        ?string $person_in_charge,
        string $status = 'active'
    ) {
        $this->id = $id;
        $this->quality_indicator_profile_id = $quality_indicator_profile_id;
        $this->dep_id = $dep_id;
        $this->data_source = $data_source;
        $this->person_in_charge = $person_in_charge;
        $this->status = $status;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int) $data['quality_indicator_profile_id'],
            (string) $data['dep_id'],
            $data['data_source'] ?? null,
            $data['person_in_charge'] ?? null,
            $data['status'] ?? 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'id'                           => $this->id,
            'quality_indicator_profile_id' => $this->quality_indicator_profile_id,
            'dep_id'                       => $this->dep_id,
            'data_source'                  => $this->data_source,
            'person_in_charge'             => $this->person_in_charge,
            'status'                       => $this->status,
        ];
    }
}
