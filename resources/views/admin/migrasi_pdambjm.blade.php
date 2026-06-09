@extends('...layouts/template')

@section('content')
<style>
    .migrasi-log { font-family: monospace; font-size: 12px; background: #1e1e1e; color: #d4d4d4;
                   border-radius: 4px; padding: 15px; min-height: 120px; max-height: 320px;
                   overflow-y: auto; white-space: pre-wrap; }
    .migrasi-log .log-ok   { color: #4ec9b0; }
    .migrasi-log .log-err  { color: #f44747; }
    .migrasi-log .log-info { color: #9cdcfe; }
    #progressBar { transition: width 0.4s ease; }
    .stat-box { text-align: center; padding: 12px; border-radius: 6px; color: #fff; }
    .stat-box h4 { font-size: 28px; font-weight: bold; margin: 0; }
    .stat-box p  { margin: 4px 0 0; font-size: 12px; }
</style>

<section class="content-header">
    <h1>
        Migrasi Data PDAM <small>Sinkronisasi dari Server Switcher ke Kasir</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Admin</a></li>
        <li class="active">Migrasi PDAM Switcher</li>
    </ol>
</section>

<section class="content">

    <div class="row">
        <!-- Form Panel -->
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cloud-download"></i> Parameter Migrasi</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Tanggal Awal <span class="text-danger">*</span></label>
                        <input type="text" id="tglAwal" class="form-control"
                               value="{{ date('Y-m-d') }}" placeholder="YYYY-MM-DD" readonly />
                    </div>
                    <div class="form-group">
                        <label>Tanggal Akhir <span class="text-danger">*</span></label>
                        <input type="text" id="tglAkhir" class="form-control"
                               value="{{ date('Y-m-d') }}" placeholder="YYYY-MM-DD" readonly />
                    </div>
                    <div class="form-group">
                        <label>Filter Loket <small class="text-muted">(opsional, koma-separated)</small></label>
                        <input type="text" id="loketCode" class="form-control"
                               placeholder="Contoh: L001,L002 atau kosongkan untuk semua" />
                    </div>
                    <div class="form-group">
                        <label>Per Halaman</label>
                        <select id="perPage" class="form-control">
                            <option value="500">500</option>
                            <option value="1000" selected>1000 (Tercepat)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="includeDeleted" value="1" />
                                Sertakan data yang dihapus (<code>flag_transaksi = D</code>)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="button" id="btnJalankan" class="btn btn-primary btn-flat btn-block">
                        <i class="fa fa-play"></i> Jalankan Migrasi
                    </button>
                    <button type="button" id="btnKemarin" class="btn btn-default btn-flat btn-block" style="margin-top: 6px;">
                        <i class="fa fa-calendar"></i> Set ke Kemarin
                    </button>
                </div>
            </div>

            <!-- Info Sumber -->
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Info Sumber Data</h3>
                </div>
                <div class="box-body" style="font-size: 13px;">
                    <p><b>URL Switcher:</b><br/>
                        <code style="word-break: break-all;">{{ $switcher_url }}/report/migrasi/pdambjm</code>
                    </p>
                    <p class="text-muted">Token dikonfigurasi via env <code>MIGRASI_SWITCHER_TOKEN</code></p>
                    <hr/>
                    <p class="text-muted" style="font-size: 11px;">
                        Data diambil per halaman (maks 1000 baris/halaman).<br/>
                        Untuk 30.000 transaksi per hari dibutuhkan ~30 request.<br/>
                        Estimasi waktu: <b>15–60 detik</b> per hari transaksi.
                    </p>
                </div>
            </div>
        </div>

        <!-- Hasil Panel -->
        <div class="col-md-8">

            <!-- Progress -->
            <div class="box box-info" id="boxProgress" style="display: none;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-spinner fa-spin"></i> Proses Berjalan...</h3>
                </div>
                <div class="box-body">
                    <div class="progress progress-striped active">
                        <div id="progressBar" class="progress-bar progress-bar-info" role="progressbar"
                             style="width: 100%"></div>
                    </div>
                    <p class="text-muted text-center" id="txtProgressInfo">Menghubungi API switcher dan mengambil data...</p>
                </div>
            </div>

            <!-- Statistik Hasil -->
            <div class="box box-success" id="boxHasil" style="display: none;">
                <div class="box-header with-border">
                    <h3 class="box-title" id="hasilJudul"><i class="fa fa-check-circle"></i> Hasil Migrasi</h3>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-xs-4">
                            <div class="stat-box" style="background: #00a65a;">
                                <h4 id="statUpsert">0</h4>
                                <p>Data Berhasil</p>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="stat-box" style="background: #3c8dbc;">
                                <h4 id="statFetched">0</h4>
                                <p>Data Diambil</p>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="stat-box" style="background: #f39c12;">
                                <h4 id="statSkip">0</h4>
                                <p>Dilewati</p>
                            </div>
                        </div>
                    </div>
                    <table class="table table-condensed table-bordered" style="font-size: 13px;">
                        <tr><td width="150"><b>Tanggal Awal</b></td><td id="infoTglAwal">-</td></tr>
                        <tr><td><b>Tanggal Akhir</b></td><td id="infoTglAkhir">-</td></tr>
                        <tr><td><b>Total Halaman</b></td><td id="infoLastPage">-</td></tr>
                        <tr><td><b>Pesan</b></td><td id="infoMessage">-</td></tr>
                    </table>
                </div>
            </div>

            <!-- Log Error -->
            <div class="box box-danger" id="boxError" style="display: none;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Log Error</h3>
                </div>
                <div class="box-body">
                    <div class="migrasi-log" id="logError"></div>
                </div>
            </div>

            <!-- Riwayat Migrasi Sesi Ini -->
            <div class="box box-default" id="boxRiwayat" style="display: none;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Sesi Ini</h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-condensed table-hover table-striped" id="tblRiwayat" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Tgl Awal</th>
                                <th>Tgl Akhir</th>
                                <th>Diambil</th>
                                <th>Berhasil</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRiwayat"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</section>

<script>
$(document).ready(function () {

    // Datepicker
    $("#tglAwal").datepicker({ dateFormat: 'yy-mm-dd' });
    $("#tglAkhir").datepicker({ dateFormat: 'yy-mm-dd' });

    // Set ke kemarin
    $("#btnKemarin").on('click', function () {
        var kemarin = new Date();
        kemarin.setDate(kemarin.getDate() - 1);
        var y = kemarin.getFullYear();
        var m = String(kemarin.getMonth() + 1).padStart(2, '0');
        var d = String(kemarin.getDate()).padStart(2, '0');
        var tgl = y + '-' + m + '-' + d;
        $("#tglAwal").val(tgl);
        $("#tglAkhir").val(tgl);
    });

    var riwayat = [];

    // Jalankan migrasi
    $("#btnJalankan").on('click', function () {
        var tglAwal       = $("#tglAwal").val().trim();
        var tglAkhir      = $("#tglAkhir").val().trim();
        var loketCode     = $("#loketCode").val().trim();
        var perPage       = $("#perPage").val();
        var includeDeleted = $("#includeDeleted").is(':checked') ? 1 : 0;

        if (!tglAwal || !tglAkhir) {
            alert('Tanggal awal dan tanggal akhir wajib diisi!');
            return;
        }

        // Tampilkan progress, sembunyikan hasil sebelumnya
        $("#boxProgress").show();
        $("#boxHasil").hide();
        $("#boxError").hide();
        $("#btnJalankan").prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
        $("#txtProgressInfo").text('Menghubungi API switcher... harap tunggu.');

        $.ajax({
            url    : '{{ url("admin/migrasi_pdambjm/jalankan") }}',
            method : 'POST',
            data   : {
                _token          : '{{ csrf_token() }}',
                tgl_awal        : tglAwal,
                tgl_akhir       : tglAkhir,
                loket_code      : loketCode,
                per_page        : perPage,
                include_deleted : includeDeleted
            },
            timeout: 620000,  // 10 menit + buffer
            success: function (res) {
                // Server selalu return 200, cek status di dalam JSON
                if (res && res.status === true) {
                    tampilHasil(res);
                    tambahRiwayat(res);
                } else {
                    var errList = (res && res.errors && res.errors.length > 0)
                        ? res.errors
                        : [(res && res.message) ? res.message : 'Terjadi kesalahan pada server.'];
                    tampilError(errList);
                    if (res) tambahRiwayat(res);
                }
            },
            error: function (xhr) {
                var errMsg = 'Terjadi kesalahan pada server.';
                try {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errMsg = xhr.responseJSON.errors.join('; ');
                    }
                } catch(e) {}
                tampilError([errMsg]);
            },
            complete: function () {
                $("#boxProgress").hide();
                $("#btnJalankan").prop('disabled', false).html('<i class="fa fa-play"></i> Jalankan Migrasi');
            }
        });
    });

    function tampilHasil(res) {
        var isOk = res.status === true;
        $("#boxHasil").show().removeClass('box-success box-danger')
                     .addClass(isOk ? 'box-success' : 'box-warning');
        $("#hasilJudul").html(isOk
            ? '<i class="fa fa-check-circle"></i> Migrasi Selesai'
            : '<i class="fa fa-exclamation-circle"></i> Migrasi Selesai (ada error)'
        );

        $("#statUpsert").text(numeral(res.total_upsert || 0).format('0,0'));
        $("#statFetched").text(numeral(res.total_fetched || 0).format('0,0'));
        $("#statSkip").text(numeral(res.total_skip || 0).format('0,0'));
        $("#infoTglAwal").text(res.tgl_awal || '-');
        $("#infoTglAkhir").text(res.tgl_akhir || '-');
        $("#infoLastPage").text((res.last_page || '-') + ' halaman');
        $("#infoMessage").text(res.message || '-');

        if (res.errors && res.errors.length > 0) {
            var html = '';
            res.errors.forEach(function (e) {
                html += '<span class="log-err">ERROR: ' + $('<div>').text(e).html() + '</span>\n';
            });
            $("#logError").html(html);
            $("#boxError").show();
        }
    }

    function tampilError(errors) {
        $("#boxHasil").show().removeClass('box-success').addClass('box-danger');
        $("#hasilJudul").html('<i class="fa fa-times-circle"></i> Migrasi Gagal');
        $("#infoMessage").text(errors.join('; '));
        var html = '';
        errors.forEach(function(e) {
            html += '<span class="log-err">ERROR: ' + $('<div>').text(e).html() + '</span>\n';
        });
        $("#logError").html(html);
        $("#boxError").show();
    }

    function tambahRiwayat(res) {
        riwayat.unshift(res);
        $("#boxRiwayat").show();
        var html = '';
        riwayat.forEach(function (r) {
            var now = new Date().toLocaleTimeString('id-ID');
            html += '<tr>';
            html += '<td>' + now + '</td>';
            html += '<td>' + (r.tgl_awal || '-') + '</td>';
            html += '<td>' + (r.tgl_akhir || '-') + '</td>';
            html += '<td>' + numeral(r.total_fetched || 0).format('0,0') + '</td>';
            html += '<td>' + numeral(r.total_upsert || 0).format('0,0') + '</td>';
            html += '<td><span class="badge ' + (r.status ? 'badge-success' : 'badge-warning') + '">'
                 + (r.status ? 'OK' : 'ERROR') + '</span></td>';
            html += '</tr>';
        });
        $("#tbodyRiwayat").html(html);
    }

});
</script>
@endsection
