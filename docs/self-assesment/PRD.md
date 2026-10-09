> **Catatan revisi (2026-10-09):** bagian arsitektur di PRD ini (Clean Architecture dengan lapisan Domain/Application/Infrastructure, use case, DTO, dan Filament untuk master data) harus direvisi sebelum implementasi, mengikuti `docs/adr/0001-no-clean-architecture-layers.md` (Livewire memakai Eloquent langsung) dan Livewire 2 yang dipakai proyek ini. Filament tidak ada di stack.

# PRD — Fitur Asesmen Mandiri Akreditasi Rumah Sakit
### (Submenu: Mutu → Akreditasi Rumah Sakit)

| Item | Keterangan |
|---|---|
| Modul Induk | Mutu (Manajemen Mutu Rumah Sakit) |
| Nama Fitur | Asesmen Mandiri Akreditasi Rumah Sakit |
| Versi Dokumen | 1.0 |
| Status | Draft — menunggu validasi PIC Mutu/PMKP |
| Referensi Desain | 4 screenshot sistem "SI-DHP" (lampiran di Bab 17) |
| Stack Target | Laravel + Livewire + Filament (master data), Clean Architecture (domain → application → infrastructure) |

---

## 1. Latar Belakang

Saat ini proses self-assessment akreditasi rumah sakit (per Bab/Fokus Area — TKRS, KPS, MFK, PMKP, MRMIK, dst) masih membutuhkan tracking manual terhadap ratusan Elemen Penilaian (EP), dokumen bukti, dan skor pencapaian. Referensi UI yang dikirim (sistem SI-DHP) menunjukkan pola kerja yang ingin direplikasi: dashboard progres per Bab → drill-down ke Standar → drill-down ke Elemen Penilaian → upload bukti & input skor.

Fitur ini akan ditambahkan sebagai modul baru di bawah submenu **Mutu** pada sistem internal yang sudah berjalan (selaras dengan proyek SIMM RS yang berorientasi standar akreditasi KARS/SNARS dan PMKP).

## 2. Tujuan

1. Menyediakan dashboard pemantauan progres asesmen mandiri per Fokus Area (Bab).
2. Memungkinkan tim pokja per Bab mengelola dokumen bukti per Elemen Penilaian (EP).
3. Memungkinkan penilaian skor self-assessment per EP secara terstruktur dan terukur.
4. Mengotomasi kalkulasi persentase capaian di level Standar, Bab, dan keseluruhan.
5. Menyediakan jejak audit (siapa upload, siapa menilai, kapan) untuk kebutuhan akreditasi.

## 3. Ruang Lingkup

### In Scope (PRD ini)
- Dashboard "Data Seluruh Fokus Area" (Gambar 1)
- Halaman list Standar per Bab (Gambar 2)
- Halaman list/akses Elemen Penilaian per Standar
- Halaman detail EP: Upload Data & Skor (Gambar 3 & 4)
- Kalkulasi otomatis statistik capaian (EP terisi, % capaian, jumlah dokumen)
- Master data Fokus Area, Standar, Elemen Penilaian, Metode Pembuktian (dikelola via Filament)
- Role & permission dasar

### Out of Scope (fase berikutnya / modul terpisah)
- Chart Dokumen Bukti, Chart % Capaian, Hasil Self Asesmen (modul monitoring terpisah, bisa jadi PRD lanjutan karena butuh agregasi lintas Bab)
- Pendaftaran Monev PPS / integrasi surveior eksternal
- Generate laporan resmi siap cetak untuk badan akreditasi
- Notifikasi otomatis (reminder due date) — dicatat sebagai *nice to have* di Bab 16

## 4. Definisi & Istilah

| Istilah | Definisi |
|---|---|
| **Fokus Area / Bab** | Kelompok standar besar, contoh: "Bab 1. Tata Kelola Rumah Sakit (TKRS)" |
| **Standar** | Pernyataan kebijakan/aturan di dalam satu Bab, memiliki "Maksud dan Tujuan" |
| **Elemen Penilaian (EP)** | Unit terkecil yang dinilai, turunan dari satu Standar (mis. "a) Representasi pemilik/Dewan Pengawas dipilih dan ditetapkan oleh Pemilik") |
| **Metode Pembuktian** | Kode cara pembuktian EP, contoh badge "PK" pada screenshot. Daftar kode bersifat **master data konfigurable** (R/D/O/W/S/PK, dst — disesuaikan pedoman akreditasi yang dipakai RS, KARS atau SNARS versi terbaru) |
| **Dokumen Bukti** | File yang diunggah sebagai bukti pemenuhan satu EP, punya judul & keterangan/tag |
| **Skor Self Assessment** | Nilai pencapaian EP. Default 3 opsi: *Terpenuhi (10)*, *Terpenuhi Sebagian (5)*, *Tidak Terpenuhi (0)* — opsional ditambah *Tidak Dapat Diterapkan/TDD* (dikecualikan dari pembagi) |
| **% Capaian** | `(total skor aktual / total skor maksimal EP applicable) × 100` di level Standar/Bab |

## 5. Aktor & Hak Akses

| Role | Hak Akses |
|---|---|
| **Admin Mutu / PIC PMKP** | Kelola master data (Bab, Standar, EP, Metode Pembuktian), lihat semua Bab, override skor, lihat audit log |
| **Tim Pokja Bab** (assigned per Bab) | Upload/hapus/edit dokumen bukti, input skor, hanya untuk Bab yang menjadi tanggung jawabnya |
| **Pimpinan/Direktur** | View-only dashboard & detail (tanpa aksi upload/skor) |
| **Superadmin/IT** | Akses penuh + konfigurasi sistem |

> Diasumsikan menggunakan Spatie Permission yang sudah berjalan di sistem existing, dengan permission granular: `asesmen.view`, `asesmen.upload`, `asesmen.skor`, `asesmen.master-data`.

## 6. Alur Pengguna (User Flow)

```
Dashboard Asesmen Mandiri (semua Bab)
   └─ klik "Buka" pada salah satu Bab
        └─ List Standar dalam Bab tersebut
             └─ klik "Buka Elemen" pada salah satu Standar
                  └─ List Elemen Penilaian (EP) milik Standar tersebut
                       └─ klik salah satu EP
                            └─ Halaman "Upload Data & Skor"
                                 ├─ Lihat Maksud & Tujuan Standar
                                 ├─ Lihat Panduan Pencarian Bukti (kelengkapan bukti + metode pembuktian)
                                 ├─ Upload / hapus / edit keterangan dokumen bukti
                                 └─ Input/Update Skor Self Assessment
```

Setiap aksi (upload dokumen, hapus dokumen, update skor) memicu rekalkulasi statistik EP → Standar → Bab → Total secara real-time (cache invalidation / event-driven).

## 7. Functional Requirements

### 7.1 Halaman Dashboard — "Data Seluruh Fokus Area" (Gambar 1)

| ID | Requirement |
|---|---|
| FR-1.1 | Sistem menampilkan daftar seluruh Fokus Area/Bab beserta: nama Bab, progress bar %, badge % capaian, jumlah Total EP, jumlah EP Diisi, jumlah EP Belum Diisi, jumlah Dokumen terupload |
| FR-1.2 | Sistem menampilkan search box global untuk mencari Elemen Penilaian (EP) berdasarkan kata kunci, lintas Bab |
| FR-1.3 | Tombol "Buka" pada setiap Bab mengarah ke halaman list Standar Bab tersebut |
| FR-1.4 | Statistik bersifat real-time (atau cached dengan invalidation saat ada perubahan skor/dokumen) |
| FR-1.5 | Badge progress berwarna sesuai threshold (mis. hijau ≥80%, kuning 50–79%, merah <50%) — kriteria threshold final dikonfirmasi ke PIC Mutu |

### 7.2 Halaman Detail Bab — List Standar (Gambar 2)

| ID | Requirement |
|---|---|
| FR-2.1 | Menampilkan breadcrumb: Asesmen Mandiri → nama Bab |
| FR-2.2 | Tabel list Standar: nomor urut, judul Standar, ringkasan "Maksud dan Tujuan" (expandable/truncated), status (badge "Selesai 100%"/persentase lain), total EP, jumlah EP terisi, jumlah berkas terupload |
| FR-2.3 | Tombol "Buka Elemen" mengarah ke list EP milik Standar tersebut |
| FR-2.4 | Tombol "Kembali ke Fokus Area" untuk navigasi mundur |

### 7.3 Halaman List Elemen Penilaian per Standar

> Catatan: pada screenshot, transisi dari "Buka Elemen" langsung menuju ke detail satu EP — diasumsikan ada halaman antara (list seluruh EP milik Standar tersebut), atau navigasi next/prev antar EP di dalam halaman detail. **Perlu konfirmasi (lihat Bab 15)**. PRD ini mengasumsikan ada list EP terlebih dahulu.

| ID | Requirement |
|---|---|
| FR-3.1 | Menampilkan seluruh EP milik satu Standar dengan indikator status pengisian & skor saat ini |
| FR-3.2 | Klik salah satu EP membuka halaman "Upload Data & Skor" |

### 7.4 Halaman Detail EP — "Upload Data & Skor" (Gambar 3 & 4)

| ID | Requirement |
|---|---|
| FR-4.1 | Menampilkan info kontekstual: kode Standar, deskripsi Standar, teks Elemen Penilaian yang aktif |
| FR-4.2 | Tombol "Lihat Maksud & Tujuan" menampilkan modal/expand berisi maksud & tujuan Standar terkait |
| FR-4.3 | Menampilkan tabel "Panduan Pencarian Bukti": kolom Penjelasan Kelengkapan Bukti & badge kode Metode Pembuktian (mis. "PK") — data ini bersumber dari master data EP |
| FR-4.4 | Menampilkan "Daftar File Tersimpan": nomor, judul dokumen, tag/keterangan, aksi (preview/view, hapus, "Edit Keterangan") |
| FR-4.5 | Tombol "Upload Dokumen Baru" membuka modal upload: pilih file, isi judul dokumen, isi/pilih keterangan (idealnya dropdown dari "Penjelasan Kelengkapan Bukti" yang relevan agar konsisten) |
| FR-4.6 | Validasi upload: tipe file (PDF, JPG, PNG, DOCX, XLSX — dikonfirmasi), ukuran maksimum (mis. 10MB), jumlah maksimum file per EP (opsional limit) |
| FR-4.7 | Aksi hapus dokumen memerlukan konfirmasi (modal "Yakin hapus?") dan tercatat di audit log |
| FR-4.8 | "Edit Keterangan" memungkinkan ubah judul/keterangan dokumen tanpa re-upload file |
| FR-4.9 | Panel "Penilaian Self Assessment" menampilkan Skor Saat Ini (badge warna sesuai skor) |
| FR-4.10 | Tombol "Update Skor Penilaian" membuka modal pilih skor (Terpenuhi/Terpenuhi Sebagian/Tidak Terpenuhi/TDD) + catatan opsional, kemudian menyimpan & memicu rekalkulasi statistik di level Standar & Bab |
| FR-4.11 | Sistem mencatat histori perubahan skor (siapa, kapan, skor lama → baru) — minimal disimpan di tabel log, ditampilkan di fase berikutnya jika diperlukan |

### 7.5 Kalkulasi Progress & Statistik

| ID | Requirement |
|---|---|
| FR-5.1 | EP dianggap **"Diisi"** jika sudah memiliki skor (bukan null) **dan** minimal 1 dokumen bukti terupload (kriteria final dikonfirmasi) |
| FR-5.2 | % Capaian Standar = (Σ skor aktual EP applicable) / (Σ skor maksimal EP applicable × 10) × 100 |
| FR-5.3 | % Capaian Bab = rata-rata tertimbang % capaian seluruh Standar dalam Bab tersebut |
| FR-5.4 | EP dengan skor "TDD" (jika diaktifkan) dikecualikan dari pembagi kalkulasi |
| FR-5.5 | Rekalkulasi dipicu otomatis (event/listener) setiap kali skor diubah, bukan dihitung manual oleh admin |

## 8. Model Data (Domain Entities)

```
FocusArea (Bab)
 ├─ id, kode, nama, urutan, deskripsi
 └─ hasMany Standard

Standard
 ├─ id, focus_area_id, kode, judul, maksud_tujuan, urutan
 └─ hasMany AssessmentElement

ProofMethod (Metode Pembuktian) — master data
 ├─ id, kode (mis. "PK"), nama

AssessmentElement (Elemen Penilaian / EP)
 ├─ id, standard_id, kode (mis. "a", "b"), deskripsi
 ├─ penjelasan_kelengkapan_bukti
 ├─ proof_method_id (FK ke ProofMethod)
 ├─ hasMany AssessmentDocument
 └─ hasOne (current) AssessmentScore / hasMany AssessmentScoreHistory

AssessmentDocument (Dokumen Bukti)
 ├─ id, assessment_element_id, judul_dokumen, keterangan, file_path,
 │   uploaded_by (user_id), uploaded_at

AssessmentScore (Skor Self Assessment)
 ├─ id, assessment_element_id, skor (enum: terpenuhi|sebagian|tidak_terpenuhi|tdd)
 ├─ nilai (10|5|0|null), catatan, assessed_by (user_id), assessed_at

AssessmentScoreHistory (audit trail, opsional fase 1 minimal log)
 ├─ id, assessment_element_id, skor_lama, skor_baru, changed_by, changed_at
```

## 9. Rancangan Skema Database (Draft Migration)

| Tabel | Kolom Kunci |
|---|---|
| `focus_areas` | id, kode, nama, urutan, deskripsi, timestamps |
| `standards` | id, focus_area_id (FK), kode, judul, maksud_tujuan (text), urutan, timestamps |
| `proof_methods` | id, kode, nama, timestamps |
| `assessment_elements` | id, standard_id (FK), kode, deskripsi (text), penjelasan_kelengkapan_bukti (text), proof_method_id (FK), urutan, timestamps |
| `assessment_documents` | id, assessment_element_id (FK), judul_dokumen, keterangan, file_path, file_size, mime_type, uploaded_by (FK users), timestamps |
| `assessment_scores` | id, assessment_element_id (FK, unique), skor (enum/string), nilai (integer nullable), catatan (text nullable), assessed_by (FK users), assessed_at, timestamps |
| `assessment_score_histories` | id, assessment_element_id (FK), skor_lama, skor_baru, catatan, changed_by (FK users), created_at |

> Nama tabel di atas perlu disesuaikan dengan konvensi penamaan modul Mutu yang sudah ada di proyek (mis. prefix `mutu_` atau `akreditasi_`) agar tidak konflik/duplikatif dengan tabel SIMM RS existing.

## 10. Pemetaan ke Clean Architecture

| Layer | Isi |
|---|---|
| **Domain** | Entities: `FocusArea`, `Standard`, `AssessmentElement`, `AssessmentDocument`, `AssessmentScore`. Value Object: `Skor` (enum + nilai numerik), `ProofMethodCode`. Domain Service: `AssessmentProgressCalculator` (kalkulasi % capaian murni, tanpa dependency framework) |
| **Application** | Use Cases: `GetFocusAreaDashboardUseCase`, `GetStandardListUseCase`, `GetAssessmentElementDetailUseCase`, `UploadAssessmentDocumentUseCase`, `DeleteAssessmentDocumentUseCase`, `UpdateAssessmentScoreUseCase`. DTO per use case input/output |
| **Infrastructure** | Eloquent Repository implementasi interface domain (`EloquentFocusAreaRepository`, dst), File Storage Adapter (Laravel Filesystem/S3), Livewire Components sebagai delivery layer untuk 4 halaman, Filament Resource untuk master data (`FocusArea`, `Standard`, `AssessmentElement`, `ProofMethod`) |

Disarankan menggunakan generator Clean Architecture yang sedang kamu kembangkan untuk men-scaffold struktur folder `Domain/Akreditasi`, `Application/Akreditasi`, `Infrastructure/Akreditasi` berdasarkan resource di atas.

## 11. Non-Functional Requirements

- **Keamanan**: validasi MIME type & antivirus scan opsional untuk file upload; storage dokumen idealnya di luar public path (private disk) dengan signed URL untuk preview.
- **Audit**: setiap aksi upload/hapus dokumen dan ubah skor wajib tercatat (user, timestamp).
- **Performa**: dashboard Bab (Gambar 1) harus tetap responsif walau jumlah EP ratusan — gunakan agregasi terhitung (counter cache/materialized stat) bukan query COUNT real-time setiap load jika data besar.
- **Konsistensi UI**: ikut pola UI existing (Bootstrap badge, Livewire reactive component, sidebar menu pattern) agar menyatu dengan modul Mutu yang sudah ada.
- **Kompatibilitas**: desktop-first (sesuai pola layar admin existing), responsif minimal untuk tablet.

## 12. Business Rules & Validasi

- Skor hanya dapat diubah oleh role dengan permission `asesmen.skor`.
- Dokumen tidak dapat dihapus jika EP sudah berstatus "Terpenuhi" tanpa konfirmasi tambahan (opsional guard rail, untuk dikonfirmasi).
- Satu EP minimal harus memiliki skor sebelum dianggap "Diisi" meskipun dokumen sudah diupload.
- Format file yang didukung & ukuran maksimum mengikuti konfigurasi `.env` (default disarankan: pdf, jpg, png, docx, xlsx, max 10MB).

## 13. Acceptance Criteria (contoh)

**Skenario: Update skor EP**
- *Given* user dengan role Tim Pokja membuka halaman detail EP
- *When* user memilih skor "Terpenuhi" dan klik "Update Skor Penilaian"
- *Then* skor EP tersimpan, badge "Skor Saat Ini" berubah menjadi "Terpenuhi (10)", dan % capaian Standar serta Bab terkait diperbarui otomatis

**Skenario: Upload dokumen bukti**
- *Given* user membuka halaman detail EP dan klik "Upload Dokumen Baru"
- *When* user mengisi judul dokumen, keterangan, memilih file valid, dan submit
- *Then* dokumen muncul di "Daftar File Tersimpan", counter jumlah dokumen di dashboard Bab bertambah

**Skenario: Role view-only**
- *Given* user dengan role Direktur (view-only) membuka halaman detail EP
- *Then* tombol "Upload Dokumen Baru" dan "Update Skor Penilaian" tidak ditampilkan/disabled

## 14. Asumsi & Pertanyaan Terbuka (perlu dikonfirmasi ke stakeholder)

1. Apakah pedoman skor mengikuti KARS 2022 (Terpenuhi/Terpenuhi Sebagian/Tidak Terpenuhi) atau ada varian lain (mis. ada opsi TDD)?
2. Apakah navigasi dari "Buka Elemen" benar-benar menampilkan list EP dahulu, atau langsung ke EP pertama dengan navigasi next/prev di dalam halaman detail?
3. Apakah definisi resmi kode Metode Pembuktian (mis. "PK") sudah ada standarnya di RS, atau perlu didefinisikan sebagai master data baru?
4. Siapa saja role yang berhak menjadi "Tim Pokja" per Bab — apakah 1 user bisa di-assign ke beberapa Bab?
5. Apakah dibutuhkan fitur approval berjenjang (PIC Pokja input → Admin Mutu verifikasi) sebelum skor final terkunci?
6. Apakah dokumen bukti perlu retensi/expiry (karena akreditasi biasanya periodik per 3-4 tahun)?

## 15. Rencana Fase Implementasi

| Fase | Cakupan |
|---|---|
| **Fase 1** | Master data (Filament): Fokus Area, Standar, Elemen Penilaian, Metode Pembuktian + migration & seeder |
| **Fase 2** | Dashboard Fokus Area (Gambar 1) + halaman list Standar (Gambar 2), read-only statistik |
| **Fase 3** | Halaman detail EP: upload/hapus/edit dokumen bukti (Gambar 3 & 4 — bagian dokumen) |
| **Fase 4** | Modul skor self-assessment + kalkulasi otomatis % capaian + audit log |
| **Fase 5** (opsional, di luar PRD ini) | Chart % Capaian, Chart Dokumen Bukti, Hasil Self Asesmen, notifikasi reminder |

## 16. Lampiran — Referensi Wireframe

| Gambar | Deskripsi |
|---|---|
| Gambar 1 | Dashboard "Data Seluruh Focus Area" — list Bab dengan progress bar, statistik EP, jumlah dokumen, tombol Buka |
| Gambar 2 | List Standar dalam satu Bab — judul standar, maksud & tujuan, status, total EP, tombol Buka Elemen |
| Gambar 3 | Halaman detail EP — info Standar & EP aktif, panduan pencarian bukti, panel skor |
| Gambar 4 | Halaman detail EP (scroll bawah) — daftar file tersimpan dengan aksi, panel "Update Skor Penilaian" |

---

*Dokumen ini adalah draft awal untuk didiskusikan dengan PIC Mutu/PMKP sebelum masuk ke tahap desain skema database final dan implementasi Clean Architecture per modul.*