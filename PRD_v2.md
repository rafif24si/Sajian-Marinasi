# PRODUCT REQUIREMENTS DOCUMENT (PRD) - VERSI 2.0
**Nama Proyek:** Sajian Marinasi - Admin Dashboard & Management System  
**Fokus Versi 2.0:** Implementasi Penuh Logika CRUD Controller, Integrasi Otomatisasi Stok & Omzet, Master Layout dengan Fixed Sidebar Kiri, serta Antarmuka (UI/UX) Blade + Chart.js untuk Seluruh Fitur.

---

## 1. Tujuan Utama Versi 2.0
Melanjutkan fondasi database dari PRD v1.0, fokus utama pengembangan tahap ini adalah:
1. **Master Layout dengan Sidebar Kiri:** Membangun kerangka tampilan utama (`resources/views/layouts/app.blade.php`) yang memiliki navigasi Sidebar di sebelah kiri yang modern, responsif, dan dilengkapi indikator menu aktif (*active state*).
2. **Fungsionalitas CRUD 100% Berjalan:** Mengisi seluruh logika pada `ProductController`, `ExpenseController`, `OrderController`, `ReportController`, dan menyempurnakan `DashboardController` agar semua fitur tambah, lihat, edit, hapus, restock, dan validasi pembayaran berfungsi penuh tanpa *error*.
3. **Desain Halaman yang Rapi, Bersih, dan Modern:** Membangun seluruh file Blade View menggunakan Tailwind CSS (via CDN/Vite), lengkap dengan komponen tabel rapi, *badge* status warna-warni, *modal/form* yang ramah pengguna, format mata uang Rupiah (`Rp`), serta grafik interaktif menggunakan Chart.js.

---

## 2. Spesifikasi Desain UI/UX & Master Layout (`layouts/app.blade.php`)

### 2.1. Gaya Visual (Visual Identity)
- **Tema Warna:** Kuliner Modern & Profesional — kombinasi warna latar belakang abu-abu sangat muda (`bg-slate-50`), Sidebar gelap elegan (`bg-slate-900` dengan aksen *amber/orange* `bg-amber-500` khas warna bumbu marinasi), dan kartu konten putih bersih (`bg-white shadow-sm rounded-xl border border-slate-200`).
- **Tipografi & Ikon:** Gunakan font *Inter* / *Plus Jakarta Sans* (Google Fonts) dan ikon dari *FontAwesome* / *Lucide Icons* / *Heroicons* (via CDN).
- **Notifikasi:** Tampilkan *Flash Message* (`session('success')` dan `session('error')`) serta pesan validasi error (`$errors->any()`) dengan komponen *alert* berwarna hijau/merah yang jelas di bagian atas konten.

### 2.2. Struktur Sidebar Kiri (Fixed Left Sidebar)
Sidebar berada di sisi kiri layar (`w-64 fixed inset-y-0 left-0`), berisi:
- **Brand Header:** Logo/Ikon Kuliner & Judul **"Sajian Marinasi"** (Sub-judul: *Admin Management*).
- **Daftar Menu Navigasi (dengan Highlight Active Route):**
  1. **Dashboard Analitik** (`route('dashboard')`)
  2. **Stok & Produk** (`route('products.index')`)
  3. **Transaksi Customer** (`route('orders.index')`)
  4. **Pengeluaran Belanja** (`route('expenses.index')`)
  5. **Laporan & Omzet** (`route('reports.index')`)

---

## 3. Spesifikasi Fungsionalitas Backend (Controllers) & Tampilan (Blade Views)

### MODUL 1: Dashboard Utama & Grafik Terintegrasi (`DashboardController` & `resources/views/dashboard.blade.php`)
**Kebutuhan Backend (`DashboardController@index`):**
- Memfilter data berdasarkan bulan (`month`) dan tahun (`year`), default ke bulan & tahun berjalan.
- Menghitung 4 *Summary Cards*:
  1. **Total Omzet** (`orders` dengan `payment_status = 'paid'`).
  2. **Total Pengeluaran** (`expenses` pada periode terpilih).
  3. **Laba Bersih Kas** (`Total Omzet - Total Pengeluaran`).
  4. **Total Transaksi Lunas & Belum Lunas** (Jumlah pesanan).
- Menyediakan data grafik untuk **Chart.js**:
  1. **Grafik Arus Kas Harian/Bulanan (Bar/Line Chart):** Perbandingan Pemasukan (Omzet) vs Pengeluaran.
  2. **Grafik Top 5 Produk Marinasi Terlaris (Doughnut/Horizontal Bar Chart):** Berdasarkan kuantitas terjual pada order lunas.
- Menampilkan tabel ringkas **Peringatan Stok Menipis (*Low Stock Alert*)** untuk produk yang `stock <= min_stock_alert` dan **5 Transaksi Terbaru**.

**Kebutuhan Tampilan (`dashboard.blade.php`):**
- Filter periode (Bulan & Tahun) di pojok kanan atas.
- 4 Kartu Ringkasan (*Summary Cards*) dengan ikon dan warna indikator (Hijau untuk Omzet, Merah untuk Pengeluaran, Biru/Amber untuk Laba Bersih).
- 2 Kolom Grafik Interaktif menggunakan **Chart.js**.
- Tabel Peringatan Stok Menipis dengan tombol pintas untuk langsung *Restock*.

---

### MODUL 2: Kelola Stok Produk (`ProductController` & `resources/views/products/`)
**Kebutuhan Backend (`ProductController`):**
- `index()`: Menampilkan semua daftar produk marinasi beserta status stok (Aman / Menipis / Habis) dan riwayat mutasi stok terbaru (`StockMovement`).
- `store(Request $request)`: Validasi dan simpan produk baru (`name`, `sku`, `cost_price`, `selling_price`, `stock`, `min_stock_alert`). Jika `stock > 0` saat dibuat, otomatis catat ke tabel `stock_movements` dengan `type = 'in'` dan `reference = 'Stok Awal'`.
- `update(Request $request, Product$product)`: Update data nama, SKU, HPP (`cost_price`), harga jual (`selling_price`), dan batas stok minimum (`min_stock_alert`).
- `destroy(Product $product)`: Hapus produk (gunakan `try-catch` agar jika produk sudah pernah terjual di `order_items`, muncul pesan peringatan tidak bisa dihapus demi menjaga keutuhan riwayat transaksi).
- `addStock(Request $request, Product$product)`: Fungsi khusus untuk menambah/mengurangi stok manual (Restock hasil produksi ungkep/marinasi baru atau koreksi stok rusak). Otomatis meng-update kolom `stock` di tabel `products` dan mencatat riwayat di `stock_movements` (`in` atau `out`).

**Kebutuhan Tampilan (`resources/views/products/index.blade.php`):**
- Form/Modal **Tambah Produk Baru** dan **Edit Produk**.
- Form/Modal **Update/Restock Stok** cepat pada setiap baris produk.
- Tabel Katalog Produk dengan perhitungan margin keuntungan per pcs (`selling_price - cost_price`), indikator warna stok (Merah jika `<= min_stock_alert`, Hijau jika aman).
- Tabel Riwayat Keluar-Masuk Stok (`stock_movements`) di bagian bawah halaman.

---

### MODUL 3: Catatan Pengeluaran Belanja (`ExpenseController` & `resources/views/expenses/`)
**Kebutuhan Backend (`ExpenseController`):**
- `index(Request $request)`: Menampilkan daftar pengeluaran dengan filter bulan, tahun, dan kategori (`bahan_baku`, `kemasan`, `operasional`, `lainnya`), serta menghitung total pengeluaran pada filter tersebut.
- `store(Request $request)`: Menyimpan pengeluaran baru (`expense_date`, `category`, `title`, `amount`, `notes`, dan upload opsional `receipt_image` ke `storage/public/receipts`).
- `update(Request $request, Expense$expense)`: Mengubah data pengeluaran yang salah input.
- `destroy(Expense $expense)`: Menghapus catatan pengeluaran (beserta file foto nota jika ada).

**Kebutuhan Tampilan (`resources/views/expenses/index.blade.php`):**
- Ringkasan Total Pengeluaran per Kategori di bagian atas halaman.
- Form input pengeluaran di sisi kiri/modal dan Tabel Daftar Pengeluaran di sisi utama lengkap dengan badge kategori, format Rupiah, tombol Edit, dan Hapus.

---

### MODUL 4: Transaksi & Pembayaran Customer (`OrderController` & `resources/views/orders/`)
**Kebutuhan Backend (`OrderController`):**
- Gunakan `DB::transaction()` untuk menjaga konsistensi stok dan pesanan.
- `index(Request $request)`: Menampilkan daftar pesanan pelanggan dengan filter status pembayaran (`paid` / `unpaid`) dan metode pembayaran (`cash`, `transfer`, `qris`).
- `create()`: Menampilkan halaman kasir/input pesanan baru dengan daftar produk yang stoknya `> 0`.
- `store(Request $request)`:
  - Generate nomor invoice otomatis (contoh: `INV-20261020-001`).
  - Validasi ketersediaan stok untuk setiap item yang dibeli.
  - Simpan data ke tabel `orders` dan rincian item ke `order_items` (simpan `unit_cost` dari `product->cost_price` dan `unit_price` dari `product->selling_price`).
  - **Logika Potong Stok Otomatis:** Jika pesanan dibuat langsung dengan status **`paid` (Lunas)**, otomatis kurangi `stock` pada tabel `products` dan buat catatan di `stock_movements` (`type = 'out'`, `reference = invoice_number`).
- `show(Order $order)`: Menampilkan detail nota/invoice pesanan lengkap dengan daftar item yang dibeli.
- `markAsPaid(Order $order)`: Mengubah status pesanan dari `unpaid` menjadi `paid`. Saat status berubah menjadi `paid`, jalankan validasi stok, **potong stok produk secara otomatis**, dan catat ke `stock_movements`.
- `destroy(Order $order)`: Menghapus pesanan. Jika pesanan yang dihapus sebelumnya berstatus `paid`, kembalikan (*restore*) jumlah stok produk ke tabel `products` dan catat di `stock_movements` (`type = 'in'`, `notes = 'Pembatalan Order'`).

**Kebutuhan Tampilan (`resources/views/orders/`):**
- `index.blade.php`: Tabel daftar pesanan dengan badge status (**Lunas** berwarna hijau, **Belum Lunas** berwarna kuning/merah), badge metode bayar (`Cash`, `Transfer`, `QRIS`), tombol **"Tandai Lunas"** sekali klik untuk order yang belum lunas, tombol Detail Invoice, dan Hapus.
- `create.blade.php`: Form kasir interaktif (menggunakan Alpine.js / Vanilla JS) di mana admin bisa menambah beberapa baris produk marinasi sekaligus, memilih jumlah (`qty`), melihat kalkulasi *subtotal* dan *Grand Total* secara otomatis sebelum disimpan.
- `show.blade.php`: Tampilan struk/invoice rapi yang siap dicetak (*print-friendly*).

---

### MODUL 5: Laporan Pemasukan & Omzet (`ReportController` & `resources/views/reports/index.blade.php`)
**Kebutuhan Backend (`ReportController@index`):**
- Menerima filter rentang tanggal (`start_date` dan `end_date`), default dari awal bulan hingga akhir bulan berjalan.
- Mengambil seluruh pesanan yang berstatus **`paid`** pada rentang tanggal tersebut.
- Menghitung metrik finansial lengkap:
  1. **Total Omzet (Pendapatan Kotor):** Jumlah `total_amount` dari order `paid`.
  2. **Total HPP Produk Terjual:** Jumlah `SUM(quantity * unit_cost)` dari `order_items` pada order `paid`.
  3. **Laba Kotor Penjualan (Gross Profit):** `Total Omzet - Total HPP Produk Terjual`.
  4. **Total Pengeluaran Belanja & Operasional:** Jumlah `amount` dari tabel `expenses` pada periode yang sama.
  5. **Arus Kas Bersih (Net Cash Flow):** `Total Omzet - Total Pengeluaran Belanja`.
- Mengelompokkan rekap pemasukan harian dan rekap berdasarkan metode pembayaran (`Cash`, `Transfer`, `QRIS`).

**Kebutuhan Tampilan (`resources/views/reports/index.blade.php`):**
- Filter rentang tanggal (`Dari Tanggal` - `Sampai Tanggal`) dan tombol Cetak Laporan (`window.print()`).
- Kartu Ringkasan Finansial (Omzet Kotor, Total HPP, Pengeluaran Belanja, dan Laba Bersih).
- Breakdown pemasukan per metode pembayaran (Cash vs Transfer vs QRIS).
- Tabel Rincian Pemasukan (Daftar transaksi lunas) dan Tabel Rincian Pengeluaran dalam satu halaman laporan yang komprehensif.

---

## 4. Kriteria Penerimaan (Acceptance Criteria - Versi 2.0)
- [ ] Seluruh halaman menggunakan Master Layout dengan **Sidebar Kiri** yang konsisten dan responsif.
- [ ] CRUD Produk & fitur **Restock Stok** berjalan lancar serta tercatat di `stock_movements`.
- [ ] CRUD Pengeluaran Belanja berfungsi penuh dan terhitung di Dashboard & Laporan.
- [ ] Input Transaksi Customer (`orders`) dapat memilih multi-produk, menghitung total otomatis, dan ketika berstatus **Paid (Lunas)** otomatis mengurangi stok produk serta menambah Omzet.
- [ ] Halaman Laporan Pemasukan & Omzet menampilkan perhitungan Omzet, HPP, Pengeluaran, dan Laba Bersih secara akurat sesuai filter tanggal.
- [ ] Grafik Chart.js di Dashboard tampil dengan baik dan terhubung secara *real-time* dengan data di database.