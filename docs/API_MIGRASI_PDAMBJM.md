# API Migrasi Data `pdambjm_trans`

Dokumentasi endpoint untuk mengambil data lengkap (raw) dari tabel `pdambjm_trans` pada server **switcher**, untuk kemudian diimport ke web **kasir**.

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
  "https://switcher.contoh.com/report/migrasi/pdambjm?tgl_awal=2024-01-01&tgl_akhir=2024-01-01&page=1&per_page=500" \
  -H "report-token: YOUR_TOKEN_HERE"
```

### PHP (menggunakan Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client();

$tglAwal  = '2024-01-01';
$tglAkhir = '2024-01-01';
$perPage  = 500;
$page     = 1;

$response = $client->get('https://switcher.contoh.com/report/migrasi/pdambjm', [
    'headers' => [
        'report-token' => 'YOUR_TOKEN_HERE',
        'Accept'       => 'application/json',
    ],
    'query' => [
        'tgl_awal'  => $tglAwal,
        'tgl_akhir' => $tglAkhir,
        'page'      => $page,
        'per_page'  => $perPage,
    ],
]);

$body = json_decode($response->getBody(), true);

if ($body['status'] === true) {
    $data       = $body['data'];         // array data transaksi
    $pagination = $body['pagination'];   // info pagination
    
    // proses data...
}
```

---

## Strategi Import: Loop Per Halaman

Karena dalam 1 hari bisa ada **30.000+ transaksi**, gunakan loop paginasi untuk mengambil semua data:

```php
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function importPdambjmTrans(string $tglAwal, string $tglAkhir): void
{
    $client   = new Client();
    $baseUrl  = 'https://switcher.contoh.com/report/migrasi/pdambjm';
    $token    = 'YOUR_TOKEN_HERE';
    $perPage  = 1000;  // maksimal, untuk mempercepat proses
    $page     = 1;
    $lastPage = 1;
    $imported = 0;

    do {
        $response = $client->get($baseUrl, [
            'headers' => [
                'report-token' => $token,
                'Accept'       => 'application/json',
            ],
            'query' => [
                'tgl_awal'  => $tglAwal,
                'tgl_akhir' => $tglAkhir,
                'page'      => $page,
                'per_page'  => $perPage,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        if (!$body['status']) {
            Log::error('Import gagal: ' . $body['message']);
            break;
        }

        $rows     = $body['data'];
        $lastPage = $body['pagination']['last_page'];

        // Insert atau upsert ke tabel tujuan di web kasir
        // Gunakan transaction_code sebagai kunci unik untuk menghindari duplikasi
        foreach ($rows as $row) {
            DB::table('pdambjm_trans')->updateOrInsert(
                ['transaction_code' => $row['transaction_code']],
                $row
            );
        }

        $imported += count($rows);
        Log::info("Import halaman {$page}/{$lastPage} — total diproses: {$imported}");

        $page++;

    } while ($page <= $lastPage);

    Log::info("Import selesai. Total: {$imported} transaksi.");
}

// Panggil untuk mengimport data 1 hari
importPdambjmTrans('2024-01-01', '2024-01-01');
```

---

## Tips Performa

### Di sisi server switcher (sumber data)

1. **Pastikan ada index** pada kolom `transaction_date` di tabel `pdambjm_trans`:
   ```sql
   ALTER TABLE pdambjm_trans ADD INDEX idx_transaction_date (transaction_date);
   ```
   
2. **Index komposit** untuk query dengan filter loket:
   ```sql
   ALTER TABLE pdambjm_trans ADD INDEX idx_date_loket (transaction_date, loket_code);
   ```

### Di sisi web kasir (tujuan import)

3. **Nonaktifkan autocommit** dan bungkus insert dalam batch transaction:
   ```php
   DB::beginTransaction();
   foreach (array_chunk($rows, 100) as $chunk) {
       DB::table('pdambjm_trans')->insertOrIgnore($chunk);
   }
   DB::commit();
   ```

4. **Gunakan `insertOrIgnore`** (bukan `updateOrInsert`) jika data tujuan kosong — jauh lebih cepat untuk import awal.

5. **Jalankan import via queue/job** agar tidak timeout, terutama untuk range tanggal yang panjang.

---

## Contoh: Import Range Tanggal Panjang (Migrasi Massal)

```php
use Carbon\Carbon;

$start = Carbon::parse('2023-01-01');
$end   = Carbon::parse('2023-12-31');

$current = $start->copy();

while ($current <= $end) {
    $tgl = $current->format('Y-m-d');
    importPdambjmTrans($tgl, $tgl);
    $current->addDay();
}
```

> **Catatan:** Proses per hari untuk memudahkan retry jika ada error, dan agar setiap request tetap dalam batas wajar.
