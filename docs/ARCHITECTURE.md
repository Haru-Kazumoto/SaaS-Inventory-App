# Arsitektur: Modular Monolith + Action Classes (CQRS-lite)

Dokumen ini adalah acuan struktur backend Inventory App. Semua kode baru **wajib** mengikuti aturan di sini; kode lama dipindahkan bertahap sesuai [Rencana Migrasi](#9-rencana-migrasi).

Gambaran produk, istilah, dan daftar fitur ada di [`PRODUCT.md`](./PRODUCT.md). Aplikasi diarahkan menjadi **inventaris & peminjaman generik (multi-organisasi/SaaS)**, jadi kode **tidak boleh** memakai istilah khas sekolah (`major`, jurusan, ruangan, pengawas). Gunakan istilah generik di §3.1.

> **TL;DR**
> - Kode dikelompokkan **per domain bisnis** (`app/Domain/<Domain>`), bukan per jenis teknis.
> - **Tulis data → Action** (satu use case, satu class, satu method `handle()`).
> - **Baca data → Query** (satu kebutuhan tampilan/laporan, satu class).
> - **Stok hanya boleh berubah lewat `StockLedger`.**
> - Controller tipis: validasi (FormRequest) → panggil Action/Query → `Inertia::render` / redirect.

---

## 1. Kenapa arsitektur ini

| Masalah saat ini | Jawaban arsitektur ini |
|---|---|
| Satu fitur tersebar di `Controllers/`, `Services/`, `Repositories/`, `Dto/`, `Http/Requests/` | Satu domain = satu folder |
| Controller gemuk (mis. `TransactionController` ±900 baris) dan berisi query langsung | Controller hanya orkestrasi; logika di Action, bacaan di Query |
| Repository interface dengan **satu** implementasi (abstraksi tanpa manfaat) | Interface dihapus; Eloquent/Query Builder dipakai langsung di Action/Query |
| `product_logs` (ledger stok) ditulis dari beberapa tempat | Satu pintu: `StockLedger` |
| String bebas `'GOOD'`, `'BORROW_OUT'`, `'DURABLE'` tersebar | PHP Enum per domain |
| Validasi campur (FormRequest vs `$request->validate()`) | Selalu FormRequest |

Yang **tidak** dipilih dan alasannya ada di [Keputusan Arsitektur](#10-keputusan-arsitektur-adr-ringkas).

---

## 2. Apa itu "CQRS-lite" di proyek ini

CQRS = *Command Query Responsibility Segregation*: memisahkan operasi yang **mengubah** data dari yang **membaca** data.

| | Command (tulis) | Query (baca) |
|---|---|---|
| Class | `Domain/<X>/Actions/*` | `Domain/<X>/Queries/*` |
| Boleh mengubah DB | ✅ (di dalam `DB::transaction`) | ❌ tidak pernah |
| Return | Model/ID/DTO hasil, atau `void` | Array, Collection, Paginator, DTO baca |
| Contoh | `BorrowItems`, `ReturnItems`, `RestockProduct` | `BorrowingListQuery`, `ProductStockQuery` |

"Lite" berarti: **satu database yang sama**, tanpa read model terpisah, tanpa event sourcing, tanpa command bus. Pemisahannya hanya di level kode, cukup untuk membuat logika tulis mudah dites dan query baca bebas dioptimasi (join, window function, raw SQL).

---

## 3. Struktur folder

```
app/
├── Domain/
│   ├── Inventory/                 # barang & stok (inti aplikasi)
│   │   ├── Actions/               # CreateProduct, UpdateProduct, RestockProduct, WriteOffProduct
│   │   ├── Queries/               # ProductListQuery, ProductDetailQuery, OffProductsQuery, RestockHistoryQuery
│   │   ├── Ledger/                # StockLedger, StockMovement (DTO), InsufficientStock (exception)
│   │   ├── Data/                  # DTO input/output domain ini
│   │   ├── Enums/                 # ProductType, StockCondition, MoveType
│   │   ├── Models/                # Product, ProductLog
│   │   └── Exceptions/
│   ├── Borrowing/                 # peminjaman & pengembalian
│   │   ├── Actions/               # BorrowItems, ReturnItems
│   │   ├── Queries/               # BorrowingListQuery, BorrowingDetailQuery, BorrowStatusQuery, ReturnableLinesQuery,
│   │   │                          # OutgoingLinesQuery (durable/consumable)
│   │   ├── Enums/                 # TxType, BorrowStatus
│   │   ├── Data/
│   │   └── Models/                # Transaction, TransactionLine, Borrower
│   ├── Procurement/               # pengajuan barang (request_item)
│   │   ├── Actions/               # SubmitRequest, ProcessRequests (bulk approve/reject)
│   │   ├── Queries/               # RequestListQuery, RequestSummaryQuery
│   │   ├── Enums/                 # RequestStatus
│   │   └── Models/                # RequestItem
│   ├── MasterData/                # gudang, kategori, satuan (uom)
│   │   ├── Actions/               # Save<Warehouse|Category|Uom>, Delete<...>
│   │   └── Models/
│   ├── Organization/              # tenant, unit, keanggotaan, pengaturan & terminologi
│   │   ├── Actions/               # RegisterOrganization, CreateUnit, AddMember, UpdateSettings
│   │   ├── Queries/
│   │   ├── Enums/                 # MemberRole (owner|admin|staff|viewer), OrgTemplate
│   │   ├── Models/                # Organization, Unit, UnitMember
│   │   ├── Scopes/                # BelongsToOrganization (global scope + trait)
│   │   └── Support/               # CurrentOrganization, CurrentUnit (resolver konteks login)
│   ├── Identity/                  # user & autentikasi
│   │   ├── Actions/               # CreateUser, UpdateUser, DeleteUser, InviteUser
│   │   ├── Queries/
│   │   ├── Models/                # User
│   │   └── Policies/              # aturan akses berbasis MemberRole + kepemilikan data
│   ├── Platform/                  # area pemilik SaaS (role DEVELOPER): organisasi pelanggan, paket,
│   │   │                          # langganan, tagihan, pembayaran  ✅ sudah diimplementasi
│   │   ├── Actions/               # SaveOrganization, ChangeSubscription, IssueInvoice, RecordPayment, VoidInvoice, …
│   │   ├── Queries/               # PlatformDashboardQuery, OrganizationListQuery, InvoiceListQuery, …
│   │   ├── Data/ Enums/ Models/ Exceptions/
│   └── Reporting/                 # dashboard & export Excel
│       ├── Queries/               # DashboardStatsQuery, MonthlyMovementQuery
│       └── Exports/               # *Export (maatwebsite/excel)
├── Http/
│   ├── Controllers/<Domain>/      # tipis
│   ├── Requests/<Domain>/         # semua validasi
│   └── Middleware/
├── Providers/
└── Support/                       # helper lintas domain (tanggal, format, dsb.)

routes/<domain>.php                # tetap per domain, nama route TIDAK berubah
resources/js/
├── Pages/<Domain>/
├── Components/Feature/<Domain>/
└── types/<domain>.ts
```

Peta tabel → domain (nama **target**; nama lama dalam kurung):

| Domain | Tabel |
|---|---|
| Organization | `organizations` 🆕, `units` (`major`), `unit_members` 🆕 (menggantikan role per jurusan) |
| Inventory | `products`, `product_logs` |
| Borrowing | `transaction`, `transaction_lines`, `borrowers` (`borrower`, menjadi master kontak) |
| Procurement | `request_item` |
| MasterData | `warehouse`, `categories` (`sub_type_products`), `uoms` (`unit_products`) |
| Identity | `users` (`roles` dihapus setelah migrasi ke `unit_members`) |
| Reporting | — (hanya membaca domain lain) |
| Platform | `organizations`, `plans`, `subscriptions`, `invoices`, `invoice_items`, `payments` (+ `users.organization_id`) |

Semua tabel bisnis memiliki `organization_id`; tabel milik unit juga memiliki `unit_id` (lihat §5.1).

### 3.1 Istilah di kode (generik)

| Konsep | Nama di kode | Label UI default | Tidak boleh dipakai di kode |
|---|---|---|---|
| Pelanggan SaaS | `Organization`, `organization_id` | Organisasi | — |
| Bagian organisasi | `Unit`, `unit_id` | dari terminologi organisasi (Jurusan/Divisi/…) | `major`, jurusan |
| Satuan barang | `Uom`, `uom_id` | Satuan | `unit_product`, `unit_id` untuk satuan |
| Kategori barang | `Category`, `category_id` | Kategori | `sub_type` (nama lama) |
| Peminjam | `Borrower` | Peminjam | — |
| Penanggung jawab | `responsible_name` | Penanggung jawab | `supervisor`, pengawas ruangan |
| Lokasi pemakaian | `usage_location` | Lokasi pemakaian | `room`, ruangan |

> **Kenapa satuan jadi `uom`?** Kata `unit` dipakai untuk unit organisasi. Supaya tidak ambigu, satuan barang memakai `uom` (*unit of measure*). Label UI tetap "Satuan".

Label UI **tidak** ditulis langsung di komponen; frontend membaca terminologi organisasi lewat composable `useTerms()` (dibagikan dari `HandleInertiaRequests` sebagai `organization.terms`).

---

## 4. Aturan per lapisan

### 4.1 Controller

- Hanya: terima FormRequest → bangun DTO → panggil Action/Query → `Inertia::render()` / `redirect()->with(...)`.
- **Tidak boleh** ada `DB::`, query builder, perulangan bisnis, atau perhitungan stok.
- Target ukuran: method ≤ ~20 baris.

```php
// app/Http/Controllers/Borrowing/BorrowingController.php
public function store(StoreBorrowingRequest $request, BorrowItems $borrowItems)
{
    $borrowItems->handle($request->toData());

    return redirect()->route('borrowing.index')->with('success', 'Peminjaman berhasil dibuat');
}

public function index(BorrowingIndexRequest $request, BorrowingListQuery $query)
{
    return Inertia::render('Borrowing/Index', [
        'transactions' => $query->paginate($request->filters(), unitId: CurrentUnit::id()),
    ]);
}
```

### 4.2 FormRequest

- Semua validasi input HTTP ada di `app/Http/Requests/<Domain>/`.
- Sediakan method `toData()` yang mengembalikan DTO domain, supaya controller tidak menyusun array manual.
- Validasi **format** (wajib, tipe, exists) di sini; validasi **bisnis** (stok cukup, status masih bisa diproses) di Action.

### 4.3 Action (Command)

- Nama = **kata kerja + objek**: `BorrowItems`, `ReturnItems`, `RestockProduct`, `WriteOffProduct`, `SubmitRequest`.
- Satu method publik `handle()`. Dependensi lewat constructor (di-resolve container).
- Membungkus perubahan dalam `DB::transaction()`.
- Melempar exception domain (mis. `InsufficientStock`) untuk pelanggaran aturan bisnis; exception ini diubah jadi `ValidationException`/flash message di FormRequest/handler, bukan ditangkap diam-diam.
- Boleh memanggil Action lain **dalam domain yang sama**; untuk domain lain lihat [§5](#5-komunikasi-antar-domain).

```php
// app/Domain/Borrowing/Actions/BorrowItems.php
final class BorrowItems
{
    public function __construct(private StockLedger $ledger) {}

    public function handle(BorrowData $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $borrower = Borrower::create($data->borrower->toArray());

            $tx = Transaction::create([
                'tx_type'     => TxType::Borrow,
                'borrower_id' => $borrower->id,
                'occurred_at' => now(),
                'created_by'  => auth()->id(),
                'description' => $data->description,
            ]);

            foreach ($data->lines as $i => $line) {
                $tx->lines()->create([...$line->toArray(), 'line_no' => $i + 1]);

                $this->ledger->move(new StockMovement(
                    productId:   $line->productId,
                    warehouseId: $line->warehouseId,
                    type:        MoveType::BorrowOut,
                    quantity:    $line->quantity,
                    from:        StockCondition::Good,
                    ref:         $tx,
                ));
            }

            return $tx;
        });
    }
}
```

### 4.4 Query (Read)

- Nama = **objek + `Query`**: `BorrowingListQuery`, `ProductStockQuery`.
- Read-only. Bebas pakai Query Builder, join, window function, atau raw SQL bila perlu performa.
- Method publik yang jelas: `get()`, `paginate()`, `find()`, `forProduct()`, dst.
- Menerima filter sebagai DTO/array bernama, bukan `Request`.
- Query yang dipakai lebih dari satu tempat (mis. status pinjaman FIFO) **harus** satu class, jangan disalin.

```php
// app/Domain/Borrowing/Queries/BorrowStatusQuery.php
final class BorrowStatusQuery
{
    /** Builder dengan kolom computed_status (POSTED|PARTIAL_RETURNED|COMPLETED). */
    public function builder(?int $unitId = null): Builder { /* FIFO allocation */ }

    public function forTransaction(int $txId): BorrowStatus
    {
        return BorrowStatus::from($this->builder()->where('id', $txId)->value('computed_status'));
    }
}
```

### 4.5 Data (DTO)

- `final readonly class` dengan constructor promotion.
- Dibuat dari FormRequest (`toData()`) atau dari array (`fromArray()`).
- Dipakai sebagai input Action/Query; jangan meneruskan `Request` atau array mentah ke domain.

### 4.6 Enum

- PHP 8.1 backed enum, nilai = nilai yang tersimpan di DB.
- Di-*cast* di model (`protected $casts = ['condition' => StockCondition::class]`).
- Label tampilan Indonesia lewat method `label()` bila dibutuhkan backend (mis. export).

```php
enum StockCondition: string
{
    case Good = 'GOOD';
    case Damaged = 'DAMAGED';
    case Lost = 'LOST';
    case Out = 'OUT';     // sedang dipinjam
    case Off = 'OFF';     // dihapuskan (rusak/pensiun, keluar dari gudang)
}

enum MoveType: string
{
    case Opening = 'OPENING';
    case Restock = 'RESTOCK';
    case BorrowOut = 'BORROW_OUT';
    case Return = 'RETURN';
    case WriteOff = 'WRITE_OFF';
    case AdjustmentPos = 'ADJUSTMENT_POS';
}
```

### 4.7 Model

- Eloquent model tinggal di `Domain/<X>/Models`. Berisi: relasi, casts, scope sederhana.
- **Tidak** berisi logika bisnis (itu tugas Action) atau query laporan (itu tugas Query).

---

## 5. Komunikasi antar domain

Aturan dependensi (panah = boleh memakai):

```
Reporting ──► (Queries semua domain)

Borrowing ──► Inventory (StockLedger, Queries)
Procurement ─► MasterData
Inventory ──► MasterData
semua ──────► Organization (CurrentOrganization, CurrentUnit, BelongsToOrganization), Identity, Support
```

- Domain lain hanya boleh memakai **public API** domain tujuan: Action, Query, `StockLedger`, Enum, DTO.
- **Dilarang** menulis langsung ke tabel milik domain lain. Contoh: Borrowing tidak boleh `insert` ke `product_logs`; harus lewat `StockLedger`.
- Membaca tabel domain lain lewat join di Query **diperbolehkan** (pragmatis, satu DB), tapi bila logikanya kompleks, pakai Query milik domain tersebut.
- Tidak ada dependensi melingkar. Inventory **tidak** boleh tahu tentang Borrowing.

### 5.1 Isolasi organisasi & unit

Isolasi data **tidak boleh** bergantung pada `where` manual (kondisi lama: ±104 `major_id` manual).

| Lapis | Mekanisme |
|---|---|
| Organisasi | Model bisnis memakai trait `BelongsToOrganization` → global scope `where organization_id = CurrentOrganization::id()` + isi otomatis `organization_id` saat `creating` |
| Query builder (`DB::table`) | Query class **wajib** memanggil `->where('<alias>.organization_id', CurrentOrganization::id())`; dibantu helper `OrgScope::apply($query, 'p')` |
| Unit | Filter unit dari `CurrentUnit` / pilihan user; akses dicek oleh Policy berdasarkan `unit_members` |
| Kepemilikan (IDOR) | Route model binding + Policy (`view`, `update`, `delete`) di setiap controller; tidak ada aksi pada resource tanpa `authorize()` |
| Unique constraint | Selalu per organisasi, mis. `unique(organization_id, code)` |
| File | Path `org/{organization_id}/...` di disk privat (S3/R2), diakses lewat signed URL |

Aturan tambahan:
- `CurrentOrganization` di-set sekali per request (middleware) dari user login; job antrean membawa `organization_id` eksplisit.
- Admin platform menembus scope **hanya** lewat Query khusus di area platform, tidak lewat `withoutGlobalScopes()` tersebar.
- Organisasi dengan satu unit: `CurrentUnit` otomatis unit default; UI unit disembunyikan.

---

## 6. StockLedger: satu pintu perubahan stok

Stok **tidak** disimpan sebagai kolom. Stok = `SUM(delta)` dari `product_logs` per `(product_id, warehouse_id, condition)`.

`App\Domain\Inventory\Ledger\StockLedger` adalah **satu-satunya** class yang boleh `INSERT` ke `product_logs`.

```php
final class StockLedger
{
    /** Catat pergerakan; lempar InsufficientStock bila saldo sumber kurang. */
    public function move(StockMovement $movement): void;

    /** Saldo satu bucket. */
    public function onHand(int $productId, int $warehouseId, StockCondition $condition = StockCondition::Good): int;
}
```

Aturan yang dijaga `StockLedger` (bukan pemanggil). Tabel ini mencerminkan **perilaku kode saat ini** (`TransactionService`, `ProductLogsRepository`) dan harus dikunci oleh test Fase 0 sebelum dipindah:

| Pergerakan (`move_type`) | Baris ledger yang ditulis | Sumber saat ini |
|---|---|---|
| `OPENING` | `+qty` ke kondisi yang diberikan | `TransactionService` (stok awal) |
| `RESTOCK` | `+qty` ke `GOOD`/`DAMAGED` | `ProductLogsRepository::restock` |
| `BORROW_OUT` | `-qty` dari kondisi asal (1 baris; **tidak** ada `+qty` ke `OUT`) | `TransactionService::borrowOut` |
| `RETURN` | `+qty` ke kondisi tujuan `GOOD`/`DAMAGED` (1 baris) | `TransactionService::returnBorrow` |
| `WRITE_OFF` | `-qty` dari kondisi asal **dan** `+qty` ke `OFF` (2 baris, `line_no` 1 & 2, `ref_type = manual`) | `ProductLogsRepository::writeOff` |
| `ADJUSTMENT_POS` | `+qty` ke `GOOD` (koreksi saat edit peminjaman) | `TransactionService` (update pinjaman) |

Konsekuensi yang perlu diingat:
- Jumlah "sedang dipinjam" **tidak** berasal dari bucket `OUT`, melainkan dihitung dari `BORROW_OUT − RETURN` per transaksi (lihat `BorrowStatusQuery` / outgoing lines).
- Baris `OFF` hanya catatan audit penghapusan; selalu dikecualikan dari stok aktif.
- Cek saldo cukup **sebelum** menulis, di dalam transaksi yang sama (gunakan `lockForUpdate` bila perlu).
- Pemanggil wajib sudah berada di dalam `DB::transaction()` (Action).
- `transaction.status` di DB selalu `POSTED`; status pinjaman yang sebenarnya **dihitung** oleh `BorrowStatusQuery`.

> ⚠️ **Temuan skema:** kolom `product_logs.move_type` dibuat sebagai `enum('OPENING','RESTOCK','BORROW_OUT','RETURN')` (migration `2025_11_08_230228`), sementara kode menulis `WRITE_OFF` dan `ADJUSTMENT_POS`, dan koneksi MySQL `strict => true`. Di database yang dibuat murni dari migration, insert tersebut akan gagal. Fase 0 harus memastikan kondisi skema produksi dan, bila perlu, menambah migration yang memperluas enum ini.

---

## 7. Konvensi penamaan

| Jenis | Pola | Contoh |
|---|---|---|
| Action | `VerbNoun` | `ReturnItems`, `WriteOffProduct` |
| Query | `NounQuery` | `OffProductsQuery` |
| DTO | `NounData` | `BorrowData`, `BorrowLineData` |
| Enum | `Noun` (tunggal) | `StockCondition` |
| FormRequest | `VerbNounRequest` | `StoreBorrowingRequest` |
| Exception | `Noun`/`Adjective` | `InsufficientStock` |
| Controller | `<Noun>Controller` di `Controllers/<Domain>/` | `Borrowing/ReturnController` |

Nama route & nama page Inertia **dipertahankan** selama migrasi (`borrowing.index`, `Borrowing/Index`) supaya frontend tidak ikut berubah.

---

## 8. Testing

| Yang dites | Jenis | Catatan |
|---|---|---|
| Action | Feature test (DB) | Uji efek ke DB & ledger; ini prioritas utama |
| `StockLedger` | Feature test (DB) | Semua baris tabel efek di §6 + kasus stok kurang |
| Query kompleks (`BorrowStatusQuery`) | Feature test (DB) | Skenario pinjam → kembali sebagian → kembali penuh |
| Controller | Feature test HTTP (opsional) | Cukup smoke test redirect/props |

- DB test: `.env.testing` → `inventory_db_test` (MySQL) dengan `RefreshDatabase`.
- Gunakan factory, bukan ID hardcode.
- Struktur test mengikuti domain: `tests/Feature/<Domain>/...`.

---

## 9. Rencana migrasi

Bertahap; aplikasi harus tetap jalan dan route/props tidak berubah setelah setiap fase.

| Fase | Pekerjaan | Selesai bila |
|---|---|---|
| **0. Pengaman** | Factory untuk model inti; feature test alur: pinjam → kembali sebagian → kembali penuh, restock, write-off, perhitungan stok & status; cek & perbaiki enum `move_type` (§6) | Test hijau terhadap kode **lama** |
| **0b. Keamanan** | Policy + middleware role; cek kepemilikan (IDOR) di produk, peminjaman, pengguna; batasi Manajemen Pengguna ke admin organisasi | Admin unit tidak bisa mengakses data unit lain / halaman admin organisasi (dibuktikan test) |
| **0c. Organisasi & generalisasi** ✅ fondasi (branch `refactor/saas-foundation`) | Migration: `organizations` (data sekarang = organisasi pertama), `major` → `units` + `organization_id`, `major_id` → `unit_id`, `unit_products` → `uoms`, `sub_type_products` → `categories`, `unit_members` dari role `ADMIN_<JURUSAN>`; field peminjam → `responsible_name`/`usage_location`; trait `BelongsToOrganization`; hapus hardcode jurusan (route per jurusan, `in_array`, mapping, menu); `useTerms()` di frontend | Tidak ada kata `major`/jurusan di kode; semua test tetap hijau; jurusan tampil dari data & terminologi |
| **1. Inventory** | Enum; `StockLedger`; pindahkan semua tulis `product_logs` ke ledger; `RestockProduct`, `WriteOffProduct`, `CreateProduct`, `UpdateProduct`; Query produk | Hanya `StockLedger` yang menulis `product_logs`; test Fase 0 tetap hijau |
| **2. Borrowing** | `BorrowItems`, `ReturnItems`, `BorrowStatusQuery`, list/detail/returnable Query; pecah `TransactionController` | Tidak ada `DB::` di controller Borrowing |
| **3. Procurement, MasterData, Identity** | Action + Query; validasi inline → FormRequest | Tidak ada `$request->validate()` & `DB::` di controller domain ini |
| **4. Legacy v1** | Hapus `Items`, `ItemsLog`, `ItemsOut`, `PartialItem`, `ItemController`, `SuperadminController`, route `item-management.*`, `Pages/ItemManagement` setelah dipastikan tak dipakai | Tidak ada referensi tersisa |
| **5. Reporting** | Dashboard & export pindah ke `Domain/Reporting` | — |
| **6. Bersih-bersih** | Hapus `app/Services`, `app/Repositories`, `app/Dto` lama, `RepositoryServiceProvider` | Folder lama kosong |

**Status:** fase 0c–6 selesai di branch `refactor/saas-foundation` (skema ditulis ulang dari nol, bukan migrasi data). Seluruh backend ada di `app/Domain`, lapisan lama sudah dihapus. Yang tersisa dari fase 0: tambah cakupan test (factory, alur pengembalian sebagian, edit stok).

---

## 10. Keputusan arsitektur (ADR ringkas)

| Keputusan | Alasan | Alternatif yang ditolak |
|---|---|---|
| Modular monolith | Tim kecil, satu DB, satu app Inertia; batas domain tetap jelas tanpa biaya operasional | Microservices: overhead deploy & konsistensi data tidak sebanding |
| Action class | Use case eksplisit, mudah dites, controller tipis | Service gemuk berisi banyak method (kondisi saat ini, sulit dirawat) |
| CQRS-lite (pemisahan kode saja) | Query laporan butuh SQL kompleks, Action butuh transaksi & aturan bisnis; dipisah supaya masing-masing sederhana | CQRS penuh / event sourcing: tidak ada kebutuhan read model terpisah atau audit replay |
| Tanpa repository interface | Hanya satu implementasi (MySQL); Eloquent/Query Builder sudah abstraksi yang cukup | Repository pattern: menambah 2 file per entitas tanpa manfaat |
| `StockLedger` tunggal | Stok adalah data paling kritis; aturan delta harus di satu tempat | Tiap fitur menulis `product_logs` sendiri (rawan selisih stok) |
| Generik: Organisasi → Unit, label lewat terminologi | Produk tidak lagi khusus sekolah; satu struktur data untuk semua sektor | Tetap "jurusan" atau fork per sektor (biaya rawat ganda) |
| Multi-tenant satu DB (`organization_id` + global scope) | Paling murah & sederhana untuk skala sekolah/UMKM | DB per tenant: biaya migrasi & operasional tinggi; dipertimbangkan hanya untuk klien enterprise |
| Satuan barang = `uom` | Menghindari tabrakan nama dengan unit organisasi | Tetap `unit_products` (ambigu dengan `units`) |
| Role generik per unit (`unit_members`) | User bisa di banyak unit dengan peran berbeda; tidak perlu role baru per unit | Role per jurusan `ADMIN_<X>` (tidak scalable) |
| Primary key tetap integer | Lihat analisis UUID: banyak breaking change (`insertGetId`, type `int`, urutan FIFO) | Ganti semua PK ke UUID; bila perlu ID publik, tambah kolom `uuid` + `getRouteKeyName()` |
