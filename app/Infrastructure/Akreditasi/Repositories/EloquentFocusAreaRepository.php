<?php

namespace App\Infrastructure\Akreditasi\Repositories;

use App\Domain\Akreditasi\Repositories\FocusAreaRepositoryInterface;
use App\Models\Akreditasi\FocusArea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentFocusAreaRepository implements FocusAreaRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return FocusArea::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('nama', 'like', "%{$search}%"))
            ->orderBy('urutan')
            ->paginate($perPage);
    }

    public function getAllWithStats(): array
    {
        return FocusArea::query()
            ->select([
                'akreditasi_focus_areas.*',
                DB::raw('COALESCE(ep_stats.total_ep, 0) AS total_ep'),
                DB::raw('COALESCE(ep_stats.ep_terisi, 0) AS ep_terisi'),
                DB::raw('COALESCE(doc_stats.total_dokumen, 0) AS total_dokumen'),
            ])
            ->leftJoin(DB::raw('(
                SELECT s.focus_area_id,
                       COUNT(ae.id) AS total_ep,
                       COUNT(as2.id) AS ep_terisi
                FROM akreditasi_standards s
                LEFT JOIN akreditasi_assessment_elements ae ON ae.standard_id = s.id
                LEFT JOIN akreditasi_assessment_scores as2 ON as2.assessment_element_id = ae.id
                GROUP BY s.focus_area_id
            ) AS ep_stats'), 'ep_stats.focus_area_id', '=', 'akreditasi_focus_areas.id')
            ->leftJoin(DB::raw('(
                SELECT s.focus_area_id,
                       COUNT(ad.id) AS total_dokumen
                FROM akreditasi_standards s
                LEFT JOIN akreditasi_assessment_elements ae ON ae.standard_id = s.id
                LEFT JOIN akreditasi_assessment_documents ad ON ad.assessment_element_id = ae.id
                GROUP BY s.focus_area_id
            ) AS doc_stats'), 'doc_stats.focus_area_id', '=', 'akreditasi_focus_areas.id')
            ->orderBy('urutan')
            ->get()
            ->all();
    }

    public function save(array $data): FocusArea
    {
        return FocusArea::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return FocusArea::destroy($id) > 0;
    }
}
