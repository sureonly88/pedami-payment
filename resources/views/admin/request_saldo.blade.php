@extends('...layouts/template')

@section('content')
<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<script>
	$(document).ready(function() {
        $("select").select2();
		$('#listData').DataTable();
        LoadData();
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        $("#tgl_bayar").datepicker({ dateFormat: 'yy-mm-dd' }); 
    });
</script>

<script type="text/javascript">

    function Kosongkan(){
		$('#request_saldo').val('');
        //$('#tgl_request').val('');
        $('#ket_request').val('');
    }

    function showDialog(){
        $("#Id").val('');
        $("#divPesan").html('');
        Kosongkan();
        //dialogUser.dialog("open");

        $('#modalUser').modal("show");
    }

    function LoadData(){
        dtTable = $('#listData').dataTable( {
            "ajax": "{{ secure_url('/admin/request_saldo/list') }}",
            "destroy": true,
            "columns": [
                { "data": "aksi" },
                { 'data': 'request_code' },
                { 'data': 'kode_loket' },
                { 'data': 'request_saldo' },
                { 'data': 'tgl_request' },
                { 'data': '' },
                { 'data': 'verifikasi_saldo' },
                { 'data': 'ket_verifikasi' },
                
            ],
            "scrollX": true,
            "aoColumnDefs": [ {
            "aTargets": [ 0 ],
            "mRender": function (data, type, full) {
                    var formmatedvalue = "<h5><i class='icon fa fa-check'></i> SELESAI</h5>";
                    var formmatedvalue1 = "";

                    if(full.is_verifikasi == 0){

                        formmatedvalue = "<button type='button' onclick=\"lihatBayar('"+full.request_code+"','"+full.username+"','"+full.kode_loket+"','"+full.request_saldo+"','"+full.tgl_request+"')\" class='btn btn-primary btn-xs'>BAYAR</button> ";

                        formmatedvalue1 = "<button type='button' onclick=\"inputKonfirmasi('"+full.request_code+"')\" class='btn btn-primary btn-xs'>KONFIRMASI</button> ";
                    }
                    return formmatedvalue + " " + formmatedvalue1;
                }
            },

            {
            "aTargets": [ 2 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue= full.kode_loket + " - " + full.nama;
                    return formmatedvalue;
                }
            },


            {
            "aTargets": [ 5 ],
                "mRender": function (data, type, full) {
                    var is_verifikasi = full.is_verifikasi;
                    var is_konfirmasi = full.is_konfirmasi;
                    var saldo = full.verifikasi_saldo;

                    var status = "";

                    if(is_konfirmasi == 0 && is_verifikasi == 0){
                        status = "<button type='button' class='btn btn-block btn-info btn-xs'>BARU</button>";
                    }

                    if(is_konfirmasi == 1 && is_verifikasi == 0){
                        status = "<button type='button' class='btn btn-block btn-warning btn-xs'>PROSES</button>";
                    }

                    if(is_konfirmasi == 1 && is_verifikasi == 1 && saldo > 0){
                        status = "<button type='button' class='btn btn-block btn-success btn-xs'>DISETUJUI</button>";
                    }

                    if(is_konfirmasi == 0 && is_verifikasi == 1 && saldo > 0){
                        status = "<button type='button' class='btn btn-block btn-success btn-xs'>DISETUJUI</button>";
                    }

                    if(is_konfirmasi == 1 && is_verifikasi == 1 && saldo <= 0){
                        status = "<button type='button' class='btn btn-block btn-danger btn-xs'>BATAL</button>";
                    }

                    if(is_konfirmasi == 0 && is_verifikasi == 1 && saldo <= 0){
                        status = "<button type='button' class='btn btn-block btn-danger btn-xs'>BATAL</button>";
                    }
                    return status;
                }
            },

            {
            "aTargets": [ 3,6 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue= numeral(data).format('0,0')
                    return formmatedvalue;
                }
            },

            ],
            "ordering": false     
        });
        Kosongkan();
    }

    function simpanKonfirmasi(){
        var Data = {
            "request_code": $("#kodeRequest").val(),
            'tgl_bayar': $('#tgl_bayar').val(),
            'metode_bayar': $('#metode_bayar').val(),
            'total_konfirmasi': $('#total_konfirmasi').val(),
            'bank_konfirmasi': $('#bank_konfirmasi').val(),
            'nama_pemilik_bank': $('#nama_pemilik_bank').val(),
            'ket_konfirmasi': $('#ket_konfirmasi').val()
        }

        sentAjax("{{ secure_url('/admin/request_saldo/konfirmasi') }}",Data, "divPesanKonfirmasi");    
    }

    function simpanData(){

        var Data = {
			'id': $('#Id').val(),
			'request_saldo': $('#request_saldo').val(),
            'id_bank_tujuan': $('#id_bank_tujuan').val(),
            'tgl_request': "",
            'ket_request': $('#ket_request').val()
        }

        sentAjax("{{ secure_url('/admin/request_saldo/simpan') }}",Data, "divPesan");    
    }

    function sentAjax(mUrl, mData, elementPesan){
        $("#btnSimpan").attr("disabled", true);
        $.ajax({
            method: "POST",
            url: mUrl,
            data: { Data: mData,
                   _token: "{{ csrf_token() }}" }
        })
        .done(function(msg) {
            $("#PesanSimpan").html('');

            if(msg.status == "Success"){

                $("#pesanSimpan").html(msg.message);
                $("#"+elementPesan).html( $("#SuccessMessage").html() );

                LoadData();
            }else{
                //console.log(msg);
                mPesan = "";
                for(i=0;i<msg.message.length; i++){
                    mPesan += "- " + msg.message[i] + "<br/>";
                }

                $("#pesanError").html(mPesan);
                $("#"+elementPesan).html( $("#ErrorMessage").html() );
            }

            $("#btnSimpan").attr("disabled", false);

        });
    }

    function inputKonfirmasi(kodeRequest){

        $("#kodeRequest").val(kodeRequest);

        $.ajaxSetup({ cache: false });
        $.getJSON("{{ secure_url('/admin/request_saldo/get') }}/"+kodeRequest, function(msg){
            if(msg.status == "Success"){

                $("#tgl_bayar").val(msg.data.tgl_bayar);
                $("#metode_bayar").val(msg.data.metode_bayar).change();
                $("#total_konfirmasi").val(msg.data.total_konfirmasi);
                $("#bank_konfirmasi").val(msg.data.bank_konfirmasi).change();
                $("#nama_pemilik_bank").val(msg.data.nama_pemilik_bank);
                $("#ket_konfirmasi").val(msg.data.ket_konfirmasi);

                //console.log(msg.data.is_konfirmasi);

                // if(msg.data.is_konfirmasi){
                //     //console.log("Ini");
                //     $("#tgl_bayar").prop("readonly", true);
                //     $("#metode_bayar").select2("disable");
                //     $("#total_konfirmasi").prop("readonly", true);
                //     $("#bank_konfirmasi").select2("disable");
                //     $("#nama_pemilik_bank").prop("readonly", true);
                //     $("#ket_konfirmasi").prop("readonly", true);
                // }else{
                //     //console.log("Ini 2");
                //     $("#tgl_bayar").prop("readonly", false);
                //     $("#metode_bayar").select2("enable");
                //     $("#total_konfirmasi").prop("readonly", false);
                //     $("#bank_konfirmasi").select2("enable");
                //     $("#nama_pemilik_bank").prop("readonly", false);
                //     $("#ket_konfirmasi").prop("readonly", false);
                // }
                
            }

            $('#modalKonfirmasi').modal("show");
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
        
    }

    function lihatBayar(kodemodal, usermodal, loketModal, saldoModal, tglModal){
           
        $('#kodemodal').html(": " + kodemodal);
        $('#usermodal').html(": " + usermodal);
        $('#loketModal').html(": " + loketModal);
        $('#saldoModal').html(": " + numeral(saldoModal).format('0,0'));
        $('#tglModal').html(": " + tglModal);
        $('#totalModal').html("<h3> : Rp. " + numeral(saldoModal).format('0,0') + "</h3>");

        $('#modalBayar').modal("show");
    }

    function showPesan(mPesan){
        $("#isiPesan").html(mPesan);
        $('#modalPesan').modal("show");
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
<div class="box box-primary">
    <div class="box-header">
      <h3 class="box-title">DAFTAR REQUEST SALDO</h3>
    </div>
    <div class="box-body">
        
        <table class="table table-bordered table-hover table-striped dataTable" id="listData">
            <thead>
                <tr>
                    <th style="min-width: 120px">AKSI</th>
                    <th style='min-width: 100px'>KODE REQUEST</th>
                    <th style='min-width: 150px'>KODE LOKET</th>
                    <th style='min-width: 100px'>PERMINTAAN</th>
                    <th style='min-width: 100px'>TGL REQUEST</th>
                    <th style='min-width: 100px'>STATUS</th>
                    <th style='min-width: 150px'>SALDO DISETUJUI</th>
                    <th style='min-width: 150px'>CATATAN</th>
                </tr>
            </thead>                
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="Tambah REQUEST SALDO" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalKonfirmasi">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">INPUT KONFIRMASI PEMBAYARAN</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">

                <div class='form-group'>
                    <label for='kodeRequest' >KODE REQUEST</label>
                    <input type='text' class='form-control' id='kodeRequest' readonly="readonly" >
                </div>

                <div class='form-group'>
                    <label for='tgl_bayar' >TGL PEMBAYARAN</label>
                    <input type='text' class='form-control' id='tgl_bayar' placeholder='Enter TGL PEMBAYARAN'>
                </div>

                <div class='form-group'>
                    <label for='metode_bayar' >METODE PEMBAYARAN</label>
                    <select class="form-control select2" style="width: 100%" id="metode_bayar">
                        <option value="TRANSFER">TRANSFER BANK</option>
                        <option value="LANGSUNG">BAYAR LANGSUNG</option>
                    </select>
                </div>

                <div class='form-group'>
                    <label for='total_konfirmasi' >TOTAL PEMBAYARAN</label>
                    <input type='text' class='form-control' id='total_konfirmasi' placeholder='Enter TOTAL PEMBAYARAN'>
                </div>

                <div class='form-group'>
                    <label for='bank_konfirmasi' >BANK PENGIRIM</label>
                    <select class="form-control select2" style="width: 100%" id="bank_konfirmasi">
                        <option value="MANDIRI">MANDIRI</option>
                        <option value="KALSEL">KALSEL</option>
                        <option value="BNI">BNI</option>
                        <option value="LAIN">LAINNYA</option>
                    </select>
                </div>

                <div class='form-group'>
                    <div class='form-group'><label for='nama_pemilik_bank' >ATAS NAMA BANK PENGIRIM</label>
                    <input type='text' class='form-control' id='nama_pemilik_bank' placeholder='Enter ATAS NAMA BANK PENGIRIM'></div>
                </div>

                <div class='form-group'>
                    <label for='ket_konfirmasi' >CATATAN</label>
                    <textarea class="form-control" rows="3" id='ket_konfirmasi' placeholder="Enter CATATAN"></textarea>
                </div>

                <div id="divPesanKonfirmasi"></div>

            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnKonfirmasi" onclick="simpanKonfirmasi()">Simpan</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Input REQUEST SALDO</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">

                <input type="text" class="form-control" id="Id" placeholder="Id Data" readonly="readonly" style="display: none; height: 5px">

				<div class='form-group'>
                <label for='request_saldo' >REQUEST SALDO</label><input type='text' class='form-control' id='request_saldo' placeholder='Enter REQUEST SALDO'></div>

                <div class='form-group'>
                <label for='request_saldo' >REKENING TUJUAN</label>

                <select class="form-control select2" style="width: 100%" id="id_bank_tujuan">
                   @foreach($bankTujuan as $bank)
                    <option value="{{ $bank->id }}">{{ strtoupper($bank->nama) }} - {{ $bank->nomor }} a/n {{ strtoupper($bank->atas_nama) }}</option>
                   @endforeach
                </select>

                </div>

                <div class='form-group'>

                <label for='ket_request' >CATATAN</label>
                <textarea class="form-control" rows="3" id='ket_request' placeholder="Enter ..."></textarea>

                </div>

                <div id="divPesan"></div>

            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSimpan" onclick="simpanData()">Simpan</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalBayar">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">PEMBAYARAN SALDO</h4>
      </div>
      <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    KODE REQUEST 
                </div>

                <div class="col-md-5" id="kodemodal">
                    : -
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    USER REQUEST 
                </div>

                <div class="col-md-5" id="usermodal">
                    : YAKIN
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    TAMBAH SALDO KE LOKET
                </div>

                <div class="col-md-5" id="loketModal">
                    : LYKN - LOKET YAKIN
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    SALDO REQUEST
                </div>

                <div class="col-md-5" id="saldoModal">
                    : RP. 3000.000
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    TANGGAL REQUEST
                </div>

                <div class="col-md-5" id="tglModal">
                    : 20-02-2017
                </div>
            </div>
            <br/>

            <div class="bg-light-blue color-palette" style="padding-top: 5px; padding-bottom: 5px; padding-left: 10px; padding-right: 10px">
                <div class="row">
                    <div class="col-md-5">
                        <h3> TOTAL PEMBAYARAN </h3>
                    </div>

                    <div class="col-md-5" id="totalModal">
                        <h3>: RP. 3000.000</h3>
                    </div>
                </div>
             
            </div>

            <hr/>
            Pembayaran dapat dilakukan ke salah satu rekening a/n Kopkar Pedami berikut:<br/><br/>

            <div class="row">
                @foreach($bankTujuan as $bank)
                <div class="col-md-3">
                    <img alt="Logo Bank" src="{{ secure_asset('/images/kopkar/logo-mandiri.gif') }}" style="height: 32px"><br/>
            
                    Bank {{ $bank->nama }}</br>
                    {{ $bank->nomor }}<br/>
                    a.n. {{ $bank->atas_nama }}<br/><br/>
                </div>
                @endforeach

            </div>

            Setelah melakukan pembayaran silahkan input konfirmasi pembayaran di Link Konfirmasi<br/>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

@include('admin.modals')

<div style="visibility: collapse;" id="ErrorMessage">
<div class="alert alert-danger" >
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-ban"></i> Terjadi Kesalahan</h4>
    <div id="pesanError"></div>
</div>
</div>

<div style="visibility: collapse;" id="SuccessMessage">
<div class="alert alert-success" >
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i> Berhasil</h4>
    <div id="pesanSimpan"></div>
</div>
</div>

</section>
@endsection