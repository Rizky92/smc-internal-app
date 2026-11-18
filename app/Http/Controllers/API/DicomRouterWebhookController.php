<?php

namespace App\Http\Controllers\API;

use App\Models\Bridging\DicomRouterWebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Penerima webhook SATUSEHAT DICOM Router.
 *
 * Router memanggil endpoint ini sekali setiap selesai memproses satu DICOM,
 * baik berhasil maupun gagal. Pengirimannya fire-and-forget: status kiriman
 * ditandai selesai di SQLite router sebelum POST dijalankan, dan scheduler
 * retry-nya menyaring flag yang sama, sehingga kiriman yang gagal sampai
 * hilang selamanya kecuali dikirim ulang manual dari dashboard router.
 *
 * Konsekuensinya body tidak pernah divalidasi sampai ditolak — apapun yang
 * masuk disimpan apa adanya, dan kalau penyimpanan gagal payload-nya dicatat
 * ke log aplikasi sebelum error diteruskan, supaya masih bisa dipulihkan.
 */
class DicomRouterWebhookController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        if (! is_array($payload) || empty($payload)) {
            Log::channel('daily')->warning('Webhook DICOM Router menerima body kosong atau bukan JSON.', [
                'ip'      => $request->ip(),
                'content' => $request->getContent(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Body kosong atau bukan JSON.',
            ], 400);
        }

        try {
            $log = DicomRouterWebhookLog::rekamPengiriman($payload);
        } catch (Throwable $e) {
            Log::channel('daily')->error('Gagal menyimpan webhook DICOM Router.', [
                'exception' => $e->getMessage(),
                'payload'   => $payload,
            ]);

            throw $e;
        }

        return response()->json([
            'status'  => true,
            'message' => 'Webhook diterima.',
            'data'    => [
                'id'             => $log->getKey(),
                'stage'          => $log->stage,
                'delivery_count' => $log->delivery_count,
            ],
        ]);
    }
}
