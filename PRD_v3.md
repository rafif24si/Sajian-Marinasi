# PRODUCT REQUIREMENTS DOCUMENT (PRD) - VERSI 3.0
**Nama Proyek:** Sajian Marinasi - Admin Dashboard & Management System  
**Fokus Versi 3.0:** Eliminasi Total Data Dummy (100% Real-Time Database Integration), Sinkronisasi Antar-Modul, dan Perombakan UI/UX Berstandar "Anti-AI Slop" (Clean, High-Density, Functional SaaS Design).

---

## 1. Masalah Utama yang Diselesaikan di Versi 3.0
1. **Bebas Data Dummy & Hardcoded:** Dilarang keras menuliskan angka statis, array grafik buatan, atau teks *placeholder* palsu di dalam Controller maupun Blade View. Seluruh metrik dan visualisasi grafik wajib bersumber dari query database aktual. Jika tabel kosong, sistem wajib menampilkan `Rp 0` dan komponen *Empty State* yang proporsional tanpa memicu error (`Division by zero` atau `Undefined array key`).
2. **Keterhubungan Penuh Antar-Modul (Single Source of Truth):** Setiap input atau perubahan status pada satu halaman wajib langsung memperbarui halaman lainnya secara *real-time* dalam satu siklus transaksi database (`DB::transaction`).
3. **Eliminasi "AI Slop" pada UI/UX:** Merombak seluruh antarmuka agar bersih dari estetika generik AI. Mengadopsi pendekatan desain **Shadcn UI / Linear / Stripe Dashboard** yang berfokus pada ketajaman tipografi, efisiensi ruang, kontras yang jelas, dan kemudahan operasional harian.

---

## 2. Standar Desain "Anti-AI Slop" (Strict UI/UX Design Rules)

### 2.1. Pola Desain yang DILARANG KERAS (Banned AI Slop Patterns):
- **DILARANG** menggunakan warna *gradient* mencolok pada background, kartu, atau tombol (contoh terlarang: `bg-gradient-to-r`, `from-indigo-500`, `via-purple-500`, `to-pink-500`).
- **DILARANG** menggunakan sudut membulat berlebihan (`rounded-2xl`, `rounded-3xl`) dan bayangan tebal mengambang (`shadow-lg`, `shadow-xl`, `shadow-2xl`).
- **DILARANG** menggunakan efek *glassmorphism* (`backdrop-blur`), animasi memantul (`animate-bounce`, `animate-pulse`), atau transformasi membesar saat kursor diarahkan (`hover:scale-105`, `hover:-translate-y-1`).
- **DILARANG** membuat kartu (*Summary Cards*) berukuran raksasa dengan *padding* boros (`p-8`, `p-10`) atau ikon dekoratif raksasa di latar belakang kartu.
- **DILARANG** menggunakan kalimat basa-basi khas AI di header halaman (seperti *"Selamat datang kembali! Mari kelola bisnis marinasi Anda dengan cerdas hari ini"*). Gunakan judul halaman yang lugas dan fungsional.

### 2.2. Aturan Desain Wajib (Clean Functional SaaS - Shadcn / Linear Style):
- **Palet Warna Netral Tajam (`Zinc` / `Slate`) + 1 Aksen Fungsional:**
  - **Latar Belakang Utama (Canvas):** `bg-zinc-50` (`#FAFAFA`).
  - **Permukaan Kartu & Tabel (Surface):** `bg-white` murni dengan garis tepi tipis dan tegas `border border-zinc-200/80`, sudut `rounded-lg` (`8px`), dan bayangan mikro `shadow-2xs` (atau tanpa bayangan).
  - **Sidebar Kiri (Fixed `w-60`):** `bg-zinc-950` (`#09090B`) dengan border kanan `border-r border-zinc-800`. Menu tidak aktif menggunakan `text-zinc-400 hover:text-zinc-100 hover:bg-zinc-900`. Menu aktif menggunakan `bg-zinc-900 text-white font-medium` dengan aksen indikator kiri `border-l-2 border-amber-500`.
  - **Tombol Utama (Primary Button):** `bg-zinc-900 hover:bg-zinc-800 text-white text-xs sm:text-sm font-medium px-3.5 py-2 rounded-md border border-transparent transition-colors`.
  - **Tombol Sekunder / Filter:** `bg-white hover:bg-zinc-50 text-zinc-700 text-xs sm:text-sm font-medium px-3 py-2 rounded-md border border-zinc-300 transition-colors`.
- **Badge Status Semantik (Micro-Pills):**
  - Gunakan ukuran ringkas `px-2 py-0.5 text-xs font-medium rounded-md border`:
    - **Lunas / Stok Aman / Pemasukan:** `bg-emerald-50 text-emerald-700 border-emerald-200`
    - **Belum Lunas / Stok Menipis:** `bg-amber-50 text-amber-700 border-amber-200`
    - **Stok Habis / Pengeluaran / Batal:** `bg-rose-50 text-rose-700 border-rose-200`
- **Tipografi & Disiplin Angka Finansial (`tabular-nums`):**
  - Gunakan font **Inter** (`font-sans antialiased`).
  - **WAJIB** menerapkan class `tabular-nums` (`font-variant-numeric: tabular-nums`) pada seluruh nominal Rupiah (`Rp`), kuantitas stok, persentase, tanggal, dan nomor invoice agar semua digit sejajar lurus secara vertikal di dalam tabel maupun kartu.
  - Gunakan `font-mono text-xs` untuk kode SKU (`SM-AYM-01`) dan Nomor Invoice (`INV-20261020-001`).
- **Tata Letak Padat & Rapi (High-Density Layout):**
  - Kartu ringkasan menggunakan `p-4` atau `p-5` dengan label atas `text-xs font-medium text-zinc-500 uppercase tracking-wider` dan nilai utama `text-2xl font-semibold text-zinc-900 tabular-nums mt-1`.
  - Tabel menggunakan header `bg-zinc-50/80 border-b border-zinc-200 text-xs font-medium text-zinc-500 uppercase tracking-wider`, sel tabel `px-4 py-3 text-sm text-zinc-700`, dan garis pemisah baris `divide-y divide-zinc-200`.

---

## 3. Arsitektur Keterhubungan Data (100% Real-Time Synchronization)

Pastikan seluruh modul terhubung secara otomatis melalui aturan logika berikut:

### 3.1. Sinkronisasi Produk & Mutasi Stok (`ProductController`)
- **Tambah Produk Baru (`store`):** Saat produk dibuat dengan stok awal `> 0`, sistem otomatis menyimpan produk ke tabel `products` sekaligus membuat record di `stock_movements` (`type = 'in'`, `quantity = $request->stock`, `reference = 'Stok Awal'`, `notes = 'Inisialisasi produk baru'`).
- **Restock / Koreksi Stok (`addStock`):** Saat pengguna menambah stok produksi (`in`) atau mencatat barang rusak/keluar manual (`out`), nilai `products.stock` langsung bertambah/berkurang dan wajib tercatat di `stock_movements`. Jika tipe `out` melebihi stok yang ada, kembalikan pesan validasi error.

### 3.2. Sinkronisasi Kasir/Transaksi Customer dengan Stok & Omzet (`OrderController`)
Seluruh operasi pada `OrderController` wajib dibungkus dengan `DB::transaction()`:
- **Form Kasir (`create`):** Mengambil daftar produk langsung dari tabel `products`. Tampilkan nama produk, harga jual aktual (`selling_price`), dan sisa stok aktual (`stock`).
- **Simpan Pesanan (`store`):**
  1. Hitung ulang seluruh harga di sisi *backend* (jangan percaya input total dari *frontend*): ambil `unit_price` dari `product->selling_price` dan `unit_cost` (HPP) dari `product->cost_price`.
  2. Jika `payment_status === 'paid'`:
     - Cek apakah `product->stock >= $item['quantity']`. Jika kurang, batalkan transaksi (*rollback*) dan tampilkan alert error.
     - Kurangi `product->stock` (`decrement`) dan catat ke `stock_movements` (`type = 'out'`, `reference = $order->invoice_number`, `notes = 'Penjualan Lunas - ' . $order->customer_name`).
  3. Jika `payment_status === 'unpaid'`:
     - Simpan pesanan dan item pesanan tanpa memotong stok terlebih dahulu (atau tandai sebagai *inden/belum lunas*), dan **jangan** masukkan ke perhitungan Omzet sampai statusnya diubah menjadi `paid`.
- **Tandai Lunas (`markAsPaid`):**
  1. Saat pesanan `unpaid` diklik **"Tandai Lunas"**, sistem mengecek ketersediaan stok seluruh item pada pesanan tersebut.
  2. Jika stok mencukupi, ubah `payment_status` menjadi `'paid'`, kurangi `products.stock` untuk setiap item, dan catat ke `stock_movements` (`type = 'out'`, `reference = $order->invoice_number`).
  3. Seketika itu juga, nilai `total_amount` pada pesanan ini langsung masuk ke perhitungan **Omzet Dashboard** dan **Laporan Pemasukan**.
- **Hapus / Batalkan Pesanan (`destroy`):**
  1. Jika pesanan yang dihapus berstatus `'paid'`, kembalikan (`increment`) stok produk ke tabel `products` dan catat ke `stock_movements` (`type = 'in'`, `reference = $order->invoice_number`, `notes = 'Restorasi stok dari penghapusan transaksi'`).

### 3.3. Sinkronisasi Pengeluaran Belanja dengan Laba Bersih (`ExpenseController`)
- Setiap pencatatan, perubahan, atau penghapusan data belanja bahan baku/kemasan/operasional di tabel `expenses` langsung memengaruhi nilai **Total Pengeluaran** dan **Laba Bersih Kas (Arus Kas Bersih)** pada Dashboard serta Halaman Laporan pada periode tanggal yang bersangkutan.

### 3.4. Sinkronisasi Real-Time Grafik Dashboard (`DashboardController`)
Semua grafik pada `dashboard.blade.php` wajib menggunakan data dinamis dari database berdasarkan filter `month` dan `year` yang aktif:
1. **Grafik 1 — Tren Arus Kas Harian (Pemasukan vs Pengeluaran):**
   - Buat *looping* jumlah hari dalam bulan terpilih (`1` s/d `Carbon::create($year,$month)->daysInMonth`).
   - Query total pemasukan per tanggal dari `orders` (`payment_status = 'paid'`) dan total pengeluaran per tanggal dari `expenses`.
   - Tanggal yang tidak memiliki transaksi wajib bernilai `0` agar grafik menampilkan skala waktu satu bulan yang utuh dan akurat.
2. **Grafik 2 — Komposisi Pengeluaran per Kategori & Top 5 Produk Terlaris:**
   - Query `OrderItem` yang terhubung dengan `orders` (`paid`) pada bulan terpilih, kelompokkan berdasarkan `product_id`, jumlahkan `quantity` terjual (`SUM(quantity)`), dan tampilkan pada grafik horizontal bar.
   - Jika belum ada penjualan sama sekali di bulan tersebut, sembunyikan kanvas grafik kosong dan tampilkan pesan *Empty State* minimalis: *"Belum ada data penjualan lunas pada periode ini."*

### 3.5. Sinkronisasi Laporan & Omzet (`ReportController`)
Halaman `/reports` menerima parameter `start_date` dan `end_date` (default: awal bulan s/d hari ini) dan menghitung secara *real-time* dari database:
1. **Omzet Kotor (Gross Revenue):** `SUM(total_amount)` dari `orders` (`paid`).
2. **Total HPP Produk Terjual (COGS):** `SUM(quantity * unit_cost)` dari `order_items` milik `orders` (`paid`).
3. **Laba Kotor Penjualan (Gross Profit):** `Omzet Kotor - Total HPP Produk Terjual`.
4. **Total Pengeluaran Belanja:** `SUM(amount)` dari `expenses`.
5. **Arus Kas Bersih (Net Cash Flow):** `Omzet Kotor - Total Pengeluaran Belanja`.
6. **Rekap Metode Pembayaran:** Total nominal dan jumlah transaksi untuk masing-masing metode (`cash`, `transfer`, `qris`) dari `orders` (`paid`).

---

## 4. Pembersihan Data Dummy (Database Reset & Seeder Cleanup)
1. Hapus semua kode pembuat data palsu (*dummy orders/expenses/products*) di dalam `database/seeders/DatabaseSeeder.php`. Biarkan `DatabaseSeeder.php` bersih.
2. Jalankan `php artisan migrate:fresh` agar seluruh tabel mulai dari kondisi kosong (`0 record`) dan siap diisi sendiri oleh pengguna.

---

## 5. Kriteria Penerimaan (Acceptance Criteria - Versi 3.0)
- [ ] Database telah di-reset (`migrate:fresh`) dan seluruh halaman dapat dibuka tanpa *error* meskipun data masih kosong (`0`).
- [ ] Tidak ada satu pun angka atau data *hardcoded* di file Blade maupun JavaScript Chart.js.
- [ ] Alur uji nyata berhasil: Input 1 Produk -> Input 1 Pengeluaran -> Input 1 Transaksi Lunas -> Stok otomatis berkurang, Riwayat Stok tercatat, Omzet & Laba di Dashboard berubah, Grafik bergerak naik, dan Laporan ter-update otomatis.
- [ ] Seluruh halaman tampil dengan desain **Clean Functional SaaS (Anti-AI Slop)**: palet warna `zinc`, border `1px` tegas, angka `tabular-nums`, tanpa gradasi norak, dan memiliki *Empty State* yang rapi.
