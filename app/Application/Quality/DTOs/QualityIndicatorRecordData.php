<?php

namespace App\Application\Quality\DTOs;

class QualityIndicatorRecordData
{
    /** @var int */
    public $indicator_id;

    /** @var string */
    public $recorded_date;

    /** @var int */
    public $numerator_value;

    /** @var int */
    public $denominator_value;

    /** @var string|null */
    public $notes;

    /** @var int|null */
    public $recorded_by;

    /** @var string|null */
    public $status;

    public function __construct(
        int $indicator_id,
        string $recorded_date,
        int $numerator_value,
        int $denominator_value,
        ?string $notes,
        ?int $recorded_by,
        ?string $status = 'draft'
    ) {
        $this->indicator_id = $indicator_id;
        $this->recorded_date = $recorded_date;
        $this->numerator_value = $numerator_value;
        $this->denominator_value = $denominator_value;
        $this->notes = $notes;
        $this->recorded_by = $recorded_by;
        $this->status = $status;
    }

    public static function from(array $data): self
    {
        return new self(
            (int) $data['indicator_id'],
            $data['recorded_date'],
            (int) $data['numerator_value'],
            (int) $data['denominator_value'],
            $data['notes'] ?? null,
            isset($data['recorded_by']) ? (int) $data['recorded_by'] : null,
            $data['status'] ?? 'draft'
        );
    }

    public function toArray(): array
    {
        return [
            'indicator_id'      => $this->indicator_id,
            'recorded_date'     => $this->recorded_date,
            'numerator_value'   => $this->numerator_value,
            'denominator_value' => $this->denominator_value,
            'notes'             => $this->notes,
            'recorded_by'       => $this->recorded_by,
            'status'            => $this->status,
        ];
    }
}
