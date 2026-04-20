@extends('...layouts/template')

@section('content')
<style>
    .mapping-table th, .mapping-table td { vertical-align: middle !important; font-size: 13px; }
    .sample-cell { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    #dropZone { border: 2px dashed #ccc; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s; }
    #dropZone:hover, #dropZone.dragover { border-color: #3c8dbc; background: #f0f8ff; }
    .progress-import { display: none; }
    .badge-success { background-color: #00a65a; }
    .badge-warning { background-color: #f39c12; }
</style>

<section class="content-header">
    <h1>
        Import Data PDAM
        <small>Import dari Excel ke Transaksi PDAM Bandarmasih</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Admin</a></li>
        <li class="active">Import PDAM</li>
    </ol>
</section>

<section class="content">

    <!-- STEP 1: Upload -->
    <div class="box box-primary" id="boxUpload">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-upload"></i> Step 1: Upload File Excel</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div id="dropZone" onclick="$('#fileExcel').click()">
                        <i class="fa fa-cloud-upload fa-3x" style="color: #aaa"></i>
                        <br/><br/>
                        <span style="font-size: 14px; color: #777">Klik atau drag file Excel (.xlsx / .xls) ke sini</span>
                        <input type="file" id="fileExcel" accept=".xlsx,.xls" style="display:none" />
                        <p id="fileInfo" style="margin-top: 10px; color: #333; font-weight: bold;"></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kode Loket (default)</label>
                        <input type="text" id="txtLoketCode" class="form-control" placeholder="Kode loket, misal: LYKN" />
                    </div>
                    <div class="form-group">
                        <label>Username (default)</label>
                        <input type="text" id="txtUsername" class="form-control" value="{{ $user['username'] }}" />
                    </div>
                    <div class="form-group">
                        <label>Jenis Loket (default)</label>
                        <select id="selJenisLoket" class="form-control">
                            <option value="ADMIN">ADMIN</option>
                            <option value="SWITCHING">SWITCHING</option>
                            <option value="ANDROID">ANDROID</option>
                            <option value="PEMBACA METER">PEMBACA METER</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-flat" id="btnUpload" disabled>
                        <i class="fa fa-upload"></i> Upload & Baca Header
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2: Mapping -->
    <div class="box box-success" id="boxMapping" style="display: none;">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-columns"></i> Step 2: Mapping Kolom Excel → Database</h3>
        </div>
        <div class="box-body">
            <p class="text-muted">Petakan setiap kolom Excel ke kolom database yang sesuai. Kolom bertanda <b>*</b> wajib dipetakan.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mapping-table" id="tblMapping">
                    <thead>
                        <tr>
                            <th style="width: 30px">#</th>
                            <th style="width: 180px">Kolom Excel</th>
                            <th style="width: 220px">Map ke Database</th>
                            <th>Sample Data (5 baris)</th>
                        </tr>
                    </thead>
                    <tbody id="mappingBody">
                    </tbody>
                </table>
            </div>
            <br/>
            <button type="button" class="btn btn-success btn-flat btn-lg" id="btnImport">
                <i class="fa fa-database"></i> Proses Import
            </button>
            <button type="button" class="btn btn-default btn-flat btn-lg" id="btnReset">
                <i class="fa fa-refresh"></i> Reset
            </button>
        </div>
    </div>

    <!-- STEP 3: Hasil -->
    <div class="box box-info" id="boxHasil" style="display: none;">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-circle"></i> Hasil Import</h3>
        </div>
        <div class="box-body">
            <div id="hasilImport"></div>
        </div>
    </div>

</section>

@include('admin.modals')

<script type="text/javascript">
$(document).ready(function () {

    var uploadedFile = null;
    var uploadedFileName = '';
    var excelHeaders = [];
    var dbColumns = {};

    // Drag & drop
    var dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    dropZone.addEventListener('dragleave', function(e) {
        $(this).removeClass('dragover');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('fileExcel').files = files;
            fileSelected(files[0]);
        }
    });

    $('#fileExcel').on('change', function () {
        if (this.files.length > 0) {
            fileSelected(this.files[0]);
        }
    });

    function fileSelected(file) {
        uploadedFile = file;
        $('#fileInfo').text(file.name + ' (' + formatSize(file.size) + ')');
        $('#btnUpload').prop('disabled', false);
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // Step 1: Upload
    $('#btnUpload').on('click', function () {
        if (!uploadedFile) return;

        var formData = new FormData();
        formData.append('file_excel', uploadedFile);
        formData.append('_token', '{{ csrf_token() }}');

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Membaca file...');

        $.ajax({
            url: '{{ secure_url("/admin/import_pdambjm/upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (msg) {
                btn.prop('disabled', false).html('<i class="fa fa-upload"></i> Upload & Baca Header');

                if (msg.status === 'Success') {
                    uploadedFileName = msg.file_name;
                    excelHeaders = msg.headers;
                    dbColumns = msg.db_columns;
                    buildMappingTable(msg.headers, msg.sample, msg.db_columns);
                    $('#boxMapping').slideDown();
                    $('html,body').animate({ scrollTop: $('#boxMapping').offset().top - 60 }, 500);
                } else {
                    showPesan(msg.message);
                }
            },
            error: function () {
                btn.prop('disabled', false).html('<i class="fa fa-upload"></i> Upload & Baca Header');
                showPesan('Terjadi kesalahan saat upload file.');
            }
        });
    });

    // Build mapping table
    function buildMappingTable(headers, sample, dbCols) {
        var body = '';
        for (var i = 0; i < headers.length; i++) {
            body += '<tr>';
            body += '<td>' + (i + 1) + '</td>';
            body += '<td><strong>' + escapeHtml(headers[i]) + '</strong></td>';
            body += '<td><select class="form-control input-sm mapping-select" data-index="' + i + '">';

            for (var key in dbCols) {
                var selected = autoMatch(headers[i], key) ? ' selected' : '';
                body += '<option value="' + key + '"' + selected + '>' + escapeHtml(dbCols[key]) + '</option>';
            }

            body += '</select></td>';
            body += '<td class="sample-cell">';

            // Sample values
            var samples = [];
            for (var j = 0; j < sample.length; j++) {
                if (sample[j][i] !== undefined && sample[j][i] !== null && sample[j][i] !== '') {
                    samples.push(escapeHtml(String(sample[j][i])));
                }
            }
            body += '<small class="text-muted">' + samples.join(' | ') + '</small>';
            body += '</td>';
            body += '</tr>';
        }
        $('#mappingBody').html(body);
    }

    // Auto-match header Excel dengan kolom DB
    function autoMatch(header, dbCol) {
        if (!dbCol) return false;
        var h = header.toLowerCase().replace(/[^a-z0-9]/g, '');
        var d = dbCol.toLowerCase().replace(/[^a-z0-9]/g, '');

        var matchMap = {
            'custid': 'cust_id', 'nopelanggan': 'cust_id', 'nopel': 'cust_id', 'idpel': 'cust_id', 'idpelanggan': 'cust_id',
            'nama': 'nama', 'namapelanggan': 'nama',
            'alamat': 'alamat',
            'blth': 'blth', 'bulantahun': 'blth', 'periode': 'blth',
            'hargaair': 'harga_air',
            'abodemen': 'abodemen', 'abonemen': 'abodemen',
            'materai': 'materai',
            'limbah': 'limbah',
            'retribusi': 'retribusi',
            'denda': 'denda',
            'standlalu': 'stand_lalu', 'meterlalu': 'stand_lalu',
            'standkini': 'stand_kini', 'meterkini': 'stand_kini',
            'subtotal': 'sub_total',
            'admin': 'admin', 'biadmin': 'admin', 'biayaadmin': 'admin',
            'total': 'total', 'totalbayar': 'total', 'jumlah': 'total',
            'golongan': 'idgol', 'idgol': 'idgol', 'gol': 'idgol',
            'diskon': 'diskon',
            'bebanatetap': 'beban_tetap', 'bebantetap': 'beban_tetap',
            'biayameter': 'biaya_meter',
        };

        if (matchMap[h] && matchMap[h] === dbCol) return true;
        if (h === d) return true;
        return false;
    }

    // Step 2: Import
    $('#btnImport').on('click', function () {
        // Kumpulkan mapping
        var mapping = {};
        var hasCustId = false;
        var hasBlth = false;

        $('.mapping-select').each(function () {
            var idx = $(this).data('index');
            var val = $(this).val();
            if (val) {
                mapping[idx] = val;
                if (val === 'cust_id') hasCustId = true;
                if (val === 'blth') hasBlth = true;
            }
        });

        if (!hasCustId) {
            showPesan('Kolom "No. Pelanggan (cust_id)" wajib dipetakan.');
            return;
        }
        if (!hasBlth) {
            showPesan('Kolom "Bulan/Tahun Rek (blth)" wajib dipetakan.');
            return;
        }

        // Cek duplikat mapping
        var vals = [];
        for (var k in mapping) {
            if (vals.indexOf(mapping[k]) !== -1) {
                showPesan('Kolom database "' + dbColumns[mapping[k]] + '" dipetakan lebih dari satu kali.');
                return;
            }
            vals.push(mapping[k]);
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses import...');

        $.ajax({
            url: '{{ secure_url("/admin/import_pdambjm/proses") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                file_name: uploadedFileName,
                mapping: mapping,
                loket_code: $('#txtLoketCode').val(),
                username: $('#txtUsername').val(),
                jenis_loket: $('#selJenisLoket').val()
            },
            success: function (msg) {
                btn.prop('disabled', false).html('<i class="fa fa-database"></i> Proses Import');

                if (msg.status === 'Success') {
                    var html = '<div class="callout callout-success">';
                    html += '<h4><i class="fa fa-check"></i> Import Berhasil</h4>';
                    html += '<p>' + msg.message + '</p>';
                    html += '<p><span class="badge badge-success">' + msg.inserted + ' data diimport</span> ';
                    html += '<span class="badge badge-warning">' + msg.skipped + ' data dilewati (duplikat)</span></p>';
                    html += '</div>';
                    $('#hasilImport').html(html);
                    $('#boxHasil').slideDown();
                    $('html,body').animate({ scrollTop: $('#boxHasil').offset().top - 60 }, 500);
                } else {
                    showPesan(msg.message);
                }
            },
            error: function () {
                btn.prop('disabled', false).html('<i class="fa fa-database"></i> Proses Import');
                showPesan('Terjadi kesalahan saat proses import.');
            }
        });
    });

    // Reset
    $('#btnReset').on('click', function () {
        uploadedFile = null;
        uploadedFileName = '';
        $('#fileExcel').val('');
        $('#fileInfo').text('');
        $('#btnUpload').prop('disabled', true);
        $('#boxMapping').slideUp();
        $('#boxHasil').slideUp();
        $('#mappingBody').html('');
    });

    function showPesan(msg) {
        $('#isiPesan').html(msg);
        $('#modalPesan').modal('show');
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

});
</script>

@endsection
