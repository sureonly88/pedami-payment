# API Migrasi Data `pdambjm_trans`

Dokumentasi endpoint untuk mengambil data lengkap (raw) dari tabel `pdambjm_trans` pada server **switcher**, untuk kemudian diimport ke web **kasir**.

---

## Konfigurasi `.env` (Server Kasir)

Tambahkan 2 baris berikut ke file `.env` di **server kasir**:

```env
MIGRASI_SWITCHER_URL=https://gateway.paymentpedami.com
MIGRASI_SWITCHER_TOKEN=report_api_token_20260427_pedami_001
```

---

## Autentikasi

Semua request ke endpoint ini **wajib** menyertakan header:

```
report-token: <TOKEN_ANDA>
```

Token dikelola melalui tabel `report_api_tokens`. Hubungi administrator untuk mendapatkan token yang aktif.

---

## Endpoint

```
GET /report/migrasi/pdambjm
```

---

## Query Parameters

| Parameter        | Tipe    | Wajib | Default | Keterangan |
|------------------|---------|-------|---------|------------|
| `tgl_awal`       | string  | ✅    | -       | Tanggal awal filter `transaction_date`, format `Y-m-d` (contoh: `2024-01-01`) |
| `tgl_akhir`      | string  | ✅    | -       | Tanggal akhir filter `transaction_date`, format `Y-m-d` (contoh: `2024-01-31`) |
| `page`           | integer | ❌    | `1`     | Nomor halaman |
| `per_page`       | integer | ❌    | `500`   | Jumlah data per halaman, **maksimal 1000** |
| `loket_code`     | string  | ❌    | -       | Filter kode loket, bisa koma-separated (contoh: `L001,L002`) |
| `username`       | string  | ❌    | -       | Filter berdasarkan username operator |
| `include_deleted`| integer | ❌    | `0`     | `1` = sertakan data yang sudah di-flag hapus (`flag_transaksi = 'D'`) |

---

## Format Response

```json
{
    "status": true,
    "response_code": "0000",
    "message": "DATA MIGRASI PDAMBJM_TRANS",
    "filter": {
        "tgl_awal": "2024-01-01",
        "tgl_akhir": "2024-01-01",
        "include_deleted": false
    },
    "data": [
        {
            "id": 123456,
            "transaction_code": "TRX20240101001",
            "transaction_date": "2024-01-01 08:30:00",
            "cust_id": "1100001234",
            "nama": "BUDI SANTOSO",
            "alamat": "JL. CONTOH NO. 1",
            "blth": "202312",
            "harga_air": 50000,
            "abodemen": 5000,
            "materai": 0,
            "limbah": 2000,
            "retribusi": 1000,
            "denda": 0,
            "stand_lalu": 100,
            "stand_kini": 115,
            "beban_tetap": 3000,
            "biaya_meter": 2000,
            "sub_total": 63000,
            "admin": 2500,
            "total": 65500,
            "username": "kasir01",
            "loket_name": "LOKET UTAMA",
            "loket_code": "L001",
            "jenis_loket": "KASIR",
            "flag_transaksi": null,
            "created_at": "2024-01-01 08:30:01",
            "updated_at": "2024-01-01 08:30:01"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 500,
        "total": 30245,
        "last_page": 61
    }
}
```

### Response Error

| `response_code` | HTTP | Keterangan |
|-----------------|------|------------|
| `4001`          | 400  | Parameter `tgl_awal` atau `tgl_akhir` tidak diisi |
| `4010`          | 401  | Header `report-token` tidak ada |
| `4011`          | 401  | Token tidak valid atau tidak aktif |
| `4031`          | 403  | Loket yang diminta tidak diizinkan oleh token ini |

---

## Contoh Request

### cURL

```bash
curl -X GET \
  "https://gateway.paymentpedami.com/report/migrasi/pdambjm?tgl_awal=2024-01-01&tgl_akhir=2024-01-01&page=1&per_page=500" \
  -H "report-token: report_api_token_20260427_pedami_001"
```

### PHP (menggunakan Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client();

$response = $client->get('https://gateway.paymentpedami.com/report/migrasi/pdambjm', [
    'headers' => [
        'report-token' => env('MIGRASI_SWITCHER_TOKEN'),
        'Accept'       => 'application/json',
    ],
    'query' => [
        'tgl_awal'  => '2024-01-01',
        'tgl_akhir' => '2024-01-01',
        'page'      => 1,
        'per_page'  => 1000,
    ],
]);

$body = json_decode($response->getBody(), true);
```

---

## Strategi Import: Loop Per Halaman

Karena dalam 1 hari bisa ada **30.000+ transaksi**, gunakan loop paginasi untuk mengambil semua data:

```php
$page     = 1;
$lastPage = 1;

do {
    $response = $client->get($apiUrl, [
        'headers' => ['report-token' => $token],
        'query'   => [
            'tgl_awal'  => $tglAwal,
            'tgl_akhir' => $tglAkhir,
            'page'      => $page,
            'per_page'  => 1000,
        ],
    ]);

    $body     = json_decode($response->getBody(), true);
    $lastPage = $body['pagination']['last_page'];

    foreach ($body['data'] as $row) {
        DB::table('pdambjm_trans')->updateOrInsert(
            ['transaction_code' => $row['transaction_code']],
            $row
        );
    }

    $page++;

} while ($page <= $lastPage);
```

---

## Cara Menjalankan (Server Kasir)

### 1. Via Web UI (Manual)

Akses halaman berikut setelah login ke web kasir:

```
/admin/migrasi_pdambjm
```

- Pilih tanggal awal & akhir
- Klik **Jalankan Migrasi**
- Tunggu hingga selesai (tampil ringkasan: berhasil/error)

### 2. Via Artisan Command (Manual atau Cronjob)

```bash
# Migrasi data kemarin (default)
php artisan migrasi:pdambjm

# Migrasi tanggal tertentu
php artisan migrasi:pdambjm --date=2024-01-15

# Migrasi range tanggal
php artisan migrasi:pdambjm --tgl_awal=2024-01-01 --tgl_akhir=2024-01-31

# Migrasi dengan filter loket
php artisan migrasi:pdambjm --date=2024-01-15 --loket_code=L001,L002

# Termasuk data yang dihapus
php artisan migrasi:pdambjm --date=2024-01-15 --include_deleted
```

### 3. Via Crontab (Otomatis — Metode Alternatif)

Jika tidak menggunakan Laravel Scheduler, tambahkan ke crontab server kasir:

```cron
# Jalankan setiap hari jam 00:15
15 0 * * * cd /var/www/html && php artisan migrasi:pdambjm >> /var/log/migrasi_pdambjm.log 2>&1
```

### 4. Via Laravel Scheduler (Otomatis — Sudah Dikonfigurasi)

Laravel Scheduler sudah dikonfigurasi di `app/Console/Kernel.php` untuk menjalankan migrasi setiap hari pukul **00:15**. Pastikan cron Laravel Scheduler aktif di server kasir:

```cron
# Cron untuk Laravel Scheduler (1 baris untuk semua command)
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

---

## Tips Performa

### Index MySQL yang Disarankan

Pastikan ada index pada kolom `transaction_date` di tabel `pdambjm_trans` di **kedua server**:

```sql
-- Di server switcher (sumber): mempercepat query SELECT
ALTER TABLE pdambjm_trans ADD INDEX idx_transaction_date (transaction_date);

-- Index komposit untuk query dengan filter loket
ALTER TABLE pdambjm_trans ADD INDEX idx_date_loket (transaction_date, loket_code);

-- Di server kasir (tujuan): mempercepat updateOrInsert
ALTER TABLE pdambjm_trans ADD UNIQUE INDEX uq_transaction_code (transaction_code);
```

### Tips Lain

- Gunakan `per_page=1000` (maksimal) untuk meminimalkan jumlah HTTP request
- Untuk migrasi data historis dalam jumlah besar, jalankan per hari menggunakan loop
- Pastikan `set_time_limit` cukup besar di `php.ini` atau di controller (sudah di-set 600 detik)

---

## Migrasi Data Historis (Contoh Range Panjang)

Untuk migrasi semua data historis, jalankan command per bulan:

```bash
# Migrasi per bulan selama 1 tahun (12 kali command)
for month in 01 02 03 04 05 06 07 08 09 10 11 12; do
  php artisan migrasi:pdambjm \
    --tgl_awal="2023-${month}-01" \
    --tgl_akhir="2023-${month}-$(cal ${month} 2023 | awk 'NF{f=$NF}END{print f}')" \
    --per_page=1000
done
```

Atau via PHP:

```php
use Carbon\Carbon;

$start = Carbon::parse('2023-01-01');
$end   = Carbon::parse('2023-12-31');
$current = $start->copy();

while ($current <= $end) {
    $tgl = $current->format('Y-m-d');
    \Artisan::call('migrasi:pdambjm', ['--date' => $tgl]);
    $current->addDay();
}
```
