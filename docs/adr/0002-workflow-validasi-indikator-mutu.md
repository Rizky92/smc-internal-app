# 0002: Workflow validasi Indikator Mutu: rejected bisa diperbaiki, approved hanya lewat koreksi atau void

- Status: accepted
- Date: 2026-10-02

## Context

Record penilaian harian Indikator Mutu melewati status `draft` → `submitted` → `approved` / `rejected`. Validator juga bisa mengoreksi record lewat Edit & Approve, yang menghasilkan `approved_with_correction`. Sebelum keputusan ini:

- `isLocked()` hanya mengunci `submitted` dan `approved`. Petugas masih bisa mengubah atau menghapus record `approved_with_correction`.
- Penolakan tidak punya alasan, dan transisi status tidak dicatat.
- Data yang sudah disetujui hanya bisa diubah dengan "Batal Validasi", yang menghapus jejak approval. Data yang tidak valid hanya bisa dibuang dengan hard delete.

Penanggung jawab mutu menetapkan aturan berikut (tiket 11):

- **REJECTED:** submitter mengedit lalu submit ulang record yang sama. Alasan penolakan wajib dan menjadi histori.
- **APPROVED:** submitter tidak boleh mengedit atau menghapus langsung. Perubahan lewat koreksi dan validasi ulang, dengan histori versi dan approval sebelumnya tetap tersimpan. Pembatalan memakai status VOIDED, bukan hard delete.

## Decision

- Status terkunci untuk petugas: `submitted`, `approved`, `approved_with_correction`, `voided`. `rejected` dan `draft` bisa diubah.
- Hapus permanen hanya untuk draft yang belum pernah diserahkan, yaitu yang belum punya histori.
- Setiap transisi validasi dicatat di `quality_indicator_record_histories`, beserta pelaku, alasan, status sebelum dan sesudah, serta nilai setelah transisi. Alasan wajib untuk reject, penolakan pengajuan koreksi, dan void.
- Koreksi atas data approved diajukan petugas sebagai **pengajuan koreksi** (`quality_indicator_correction_requests`). Selama pengajuan pending, record tetap approved dengan nilai lama. Bila validator menyetujui, nilai diganti dan status menjadi `approved_with_correction`. Bila ditolak, nilai lama tetap berlaku.
- `voided` hanya diberikan validator, wajib dengan alasan, dan hanya untuk record approved/approved_with_correction. Record voided tetap menempati tanggalnya, terkunci permanen, dan tidak dihitung di Dashboard.
- `approved_with_correction` adalah data final tervalidasi. Statusnya setara `approved` untuk pelaporan dan untuk aturan kunci.

## Consequences

- Satu indikator per tanggal tetap satu record. Tanggal yang sudah di-void tidak bisa diisi ulang.
- Record yang disetujui sebelum keputusan ini tidak punya histori approval. Histori mulai dari transisi pertama setelah deploy.
- Daftar status di `QualityIndicatorRecord` (`STATUSES_TERKUNCI`, `STATUSES_DISETUJUI`, `STATUSES_DILAPORKAN`) adalah satu-satunya tempat aturan ini. Bila aturan berubah, ubah konstanta itu dan test per status di `tests/Feature/Mutu/`.
- Audit log field-level (`indikator_harian_audit_logs`) tetap ada untuk koreksi. Histori transisi melengkapinya, bukan menggantikannya.
