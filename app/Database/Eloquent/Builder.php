<?php

namespace App\Database\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class Builder extends BaseBuilder
{
    /**
     * Sama seperti paginate(), hanya saja query count(*) untuk menghitung total
     * baris ikut di-cache.
     *
     * Pada laporan berat, count(*) justru lebih mahal daripada mengambil
     * datanya: query berpaginasi dibatasi LIMIT sehingga MySQL bisa berhenti
     * lebih awal, sedangkan count(*) wajib memproses seluruh baris. Padahal
     * hasilnya tidak berubah selama filternya sama, sehingga berpindah halaman
     * membayar ongkos yang sama berulang kali.
     *
     * @param  int|null  $perPage
     * @param  string[]  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @param  int  $ttl
     */
    public function cachedPaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $ttl = 300): LengthAwarePaginator
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();

        if ($ttl <= 0) {
            return $this->paginate($perPage, $columns, $pageName, $page);
        }

        $base = $this->toBase();

        // Sidik jari query sengaja diambil sebelum forPage() supaya LIMIT/OFFSET
        // tidak ikut terhitung. Dengan begitu hasil count dipakai ulang untuk
        // semua halaman selama filternya sama.
        $sidikJari = md5($base->toSql().serialize($base->getBindings()));

        $total = Cache::remember(
            'paginate-count:'.$sidikJari,
            $ttl,
            fn (): int => $this->toBase()->getCountForPagination()
        );

        // Baris disimpan sebagai array biasa, bukan model, supaya isi cache
        // tidak ikut membawa konfigurasi koneksi dan casting milik model.
        $results = $total
            ? $this->model->hydrate(Cache::remember(
                sprintf('paginate-rows:%s:%d:%d', $sidikJari, $perPage, $page),
                $ttl,
                fn (): array => $this->forPage($page, $perPage)->get($columns)->map->getAttributes()->all()
            ))
            : $this->model->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}
