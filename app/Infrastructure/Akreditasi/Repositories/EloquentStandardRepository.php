<?php

namespace App\Infrastructure\Akreditasi\Repositories;

use App\Domain\Akreditasi\Repositories\StandardRepositoryInterface;
use App\Models\Akreditasi\Standard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentStandardRepository implements StandardRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Standard::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('judul', 'like', "%{$search}%"))
            ->when($filters['focus_area_id'] ?? null, fn ($q, $id) => $q->where('focus_area_id', $id))
            ->orderBy('urutan')
            ->paginate($perPage);
    }

    public function getByFocusAreaWithStats(int $focusAreaId): array
    {
        return Standard::query()
            ->select([
                'akreditasi_standards.*',
                DB::raw('COALESCE(ep_stats.total_ep, 0) AS total_ep'),
                DB::raw('COALESCE(ep_stats.ep_terisi, 0) AS ep_terisi'),
                DB::raw('COALESCE(doc_stats.total_dokumen, 0) AS total_dokumen'),
            ])
            ->leftJoin(DB::raw('(
                SELECT ae.standard_id,
                       COUNT(ae.id) AS total_ep,
                       COUNT(as2.id) AS ep_terisi
                FROM akreditasi_assessment_elements ae
                LEFT JOIN akreditasi_assessment_scores as2 ON as2.assessment_element_id = ae.id
                GROUP BY ae.standard_id
            ) AS ep_stats'), 'ep_stats.standard_id', '=', 'akreditasi_standards.id')
            ->leftJoin(DB::raw('(
                SELECT ae.standard_id,
                       COUNT(ad.id) AS total_dokumen
                FROM akreditasi_assessment_elements ae
                LEFT JOIN akreditasi_assessment_documents ad ON ad.assessment_element_id = ae.id
                GROUP BY ae.standard_id
            ) AS doc_stats'), 'doc_stats.standard_id', '=', 'akreditasi_standards.id')
            ->where('akreditasi_standards.focus_area_id', $focusAreaId)
            ->orderBy('urutan')
            ->get()
            ->all();
    }

    public function save(array $data): Standard
    {
        return Standard::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return Standard::destroy($id) > 0;
    }
}
