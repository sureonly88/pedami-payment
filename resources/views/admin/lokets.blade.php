@extends('...layouts/template')

@section('content')
<script>
$("#menuLokets" ).prop( "class", "active" );

$(document).ready(function() {
    $("select").select2();
});

function saveLoket(){
    cId = $("#id").val();
    cNama = $("#nama").val();
    cAlamat = $("#alamat").val();
    cLoket_code = $("#loket_code").val();
    cIs_blok = $("#is_blok").is(':checked');
    cByadmin = $("#byadmin").val();
    cBlokMessage = $("#blok_message").val();

    $.ajax({
        method: "POST",
        url: "{{ secure_url('/admin/lokets') }}",
        data: { id: cId,
                nama: cNama,
                alamat: cAlamat,
                loket_code: cLoket_code,
                is_blok: cIs_blok,
                byadmin: cByadmin,
                blok_message: cBlokMessage,
                _token: "{{ csrf_token() }}" }
    })
    .done(function(msg) {
        alert( msg.message );
        getListLokets();
        Empty();
    });
}

function Empty(){
    $("#id").val("");
    $("#nama").val("");
    $("#alamat").val("");
    $("#loket_code").val("");
    $("#byadmin").val("");
    $("#blok_message").val("");
    $("#pulsa").val("");
}

function getListLokets(){
    dtTable = $('#dataTable').dataTable( {
        "ajax": "{{ secure_url('admin/lokets/list') }}",
        "destroy": true,
        "columns": [
            { "data": "nama" },
            { "data": "alamat" },
            { "data": "loket_code" },
            { "data": "is_blok" },
            { "data": "pulsa" },
            { "data": "byadmin" },
            { "data": "aksi" }
        ]
    });
}

function editLoket(id){
    $.getJSON("{{ secure_url('/admin/lokets/get') }}"+"/"+id, function(data){
        if(data.status == "Success"){
            $("#id").val(id);
            $("#nama").val(data.data.nama);
            $("#alamat").val(data.data.alamat);
            $("#loket_code").val(data.data.loket_code);
            is_blok = data.data.is_blok;
            $("#is_blok").prop('checked', true);
//            if(is_blok == 1)
//                $('#is_blok').prop('checked', true);
//            else
//                $('#is_blok').prop('checked', false);
            $("#byadmin").val(data.data.byadmin);
            $("#blok_message").val(data.data.blok_message);
            $("#pulsa").val(data.data.pulsa);
        }
    }).error(function(jqXHR, textStatus, errorThrown){
        alert("error occurred!");
    });
}
</script>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Dashboard
    <small>Halaman Pedami Payment</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Admin</a></li>
    <li class="active">Dashboard</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
<div class="panel panel-green">
    <div class="panel-heading">
        Konfigurasi Users</div>
    <div class="panel-body pan">
        <form action="" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="id" id="id">
        <div class="form-body pal">
            @if($user['role']=='admin')
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nama</label>
                             <div class="input-icon right">
                                <i class="fa fa-user"></i>
                                <input id="nama" name="nama" placeholder="Nama Loket" value="" class="form-control" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <div class="input-icon right">
                                <i class="fa fa-user"></i>
                                <input id="alamat" name="alamat" placeholder="Alamat Loket" value="" class="form-control" type="text">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Kode Loket</label>
                            <div class="input-icon right">
                                <i class="fa fa-user"></i>
                                <input id="loket_code" name="loket_code" placeholder="Kode Loket" value="" class="form-control" type="text">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <input type="checkbox" id="is_blok" name="is_blok" style="">&nbsp; Blokir Loket
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <textarea rows="5" id="blok_message" name="blok_message" placeholder="Pesan Blokir" class="form-control"></textarea></div>
                            </div>

                        <div class="form-group">
                            <label>Biaya Admin</label>
                            <div class="input-icon right">
                                <i class="fa fa-money"></i>
                                <input id="byadmin" name="byadmin" placeholder="Biaya Admin" value="" class="form-control" type="text">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Pulsa</label>
                            <div class="input-icon right">
                                <i class="fa fa-money"></i>
                                <input id="pulsa" readonly="readonly" name="pulsa" placeholder="Pulsa Loket" value="" class="form-control" type="text">
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-primary" onclick="saveLoket()()">
                                Simpan</button>
                            <button type="button" class="btn btn-primary" onclick="Empty()">
                                Reset</button>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="panel panel-yellow">
                            <div class="panel-heading">Daftar Lokets</div>
                            <div class="panel-body">
                                <table id="dataTable" class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>Kode Loket</th>
                                        <th>Blokir</th>
                                        <th>Pulsa</th>
                                        <th>Admin</th>
                                        <th>Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody id="dataUsers">
                                    <tr>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>

                                    </tbody>
                                </table>
                                <script>
                                    getListLokets();
                                </script>
                            </div>
                        </div>

                    </div>
                </div>
            @else
                <div class="alert alert-danger"><strong>Error</strong> Kamu tidak punya akses untuk Konfigurasi Loket.</div>
            @endif

        </div>
        </form>
    </div>
</div>
</section>
@endsection