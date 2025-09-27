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
        LoadData("0");
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

    function LoadData(vStat){
        dtTable = $('#listData').dataTable( {
            "ajax": "{{ url('/admin/admin_saldo/list') }}"+"/"+vStat,
            "destroy": true,
            "columns": [
                { "data": "aksi" },
                { "data": "" },
                { 'data': 'request_code' },
                { 'data': 'tmp_kode_loket' },
                { 'data': 'request_saldo' },
                { 'data': 'tgl_request' },
                { 'data': 'ket_request' },
                { 'data': 'status_konfirmasi' },
                { 'data': 'ket_konfirmasi' },
                { 'data': 'tgl_konfirmasi' },
                { 'data': 'status_verifikasi' },
                { 'data': 'tgl_verifikasi' },
                { 'data': 'verifikasi_saldo' },
                { 'data': 'ket_verifikasi' },
                { 'data': 'nama_bank_tujuan' },
                { 'data': 'bank_pengirim' },
                
                
            ],
            "aoColumnDefs": [ {
            "aTargets": [ 0 ],
            "mRender": function (data, type, full) {
                    var formmatedvalue = "-";
                    if(full.status_verifikasi != "Sudah Verifikasi"){
                        var formmatedvalue = "<button type='button' onclick=\"inputVerifikasi('"+full.request_code+"','"+full.kode_loket+"','"+full.request_saldo+"')\" class='btn btn-primary btn-xs'>VERIFIKASI</button> ";
                    }
                    return formmatedvalue;
                    
                }
            },

            {
            "aTargets": [ 1 ],
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
            "aTargets": [ 4,12 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue= numeral(data).format('0,0')
                    return formmatedvalue;
                }
            },

            ],
            "scrollX": true,  
            "ordering": false         
        });
        Kosongkan();
    }

    function simpanVerfikasi(){

        var Data = {
			'request_code': $('#request_code').val(),
            'status_verifikasi': $('#status_verifikasi').val(),
            'verifikasi_saldo': $('#verifikasi_saldo').val(),
            'ket_verifikasi': $('#ket_verifikasi').val(),
            'kode_loket_tujuan': $('#kode_loket').val()
        }

        sentAjax("{{ url('/admin/admin_saldo/simpan') }}",Data, "divPesan");    
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

    function inputVerifikasi(kodeRequest, kodeLoket, requestSaldo){

        $("#request_code").val(kodeRequest);
        $("#kode_loket").val(kodeLoket);
        $("#request_saldo").val(numeral(requestSaldo).format('0,0'));
        $("#verifikasi_saldo").val(requestSaldo);

        $('#modalVerifikasi').modal("show");
        
    }

    function changeStatus(mNilai){
        LoadData(mNilai);
    }

    function changeSaldoSetuju(mNilai){
        if(mNilai == "SETUJU"){
            $("#DivSaldoSetuju").css("display", "");
        }else{
            $("#DivSaldoSetuju").css("display", "none");
        }
        //$("#verifikasi_saldo").val('');
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
      <h3 class="box-title">PERSETUJUAN PERMINTAAN TOPUP SALDO</h3>
    </div>
    <div class="box-body">

        <div class='form-group'>
            <label for='status_setuju' >STATUS PERSETUJUAN</label>
            <select class="form-control select2" style="width: 100%" id="status_setuju" onchange="changeStatus(this.value)">
                <option value="0">BELUM DI VERIFIKASI</option>
                <option value="1">SUDAH DI VERIFIKASI</option>
            </select>
        </div>
        
        <table class="table table-bordered table-hover table-striped dataTable" id="listData">
            <thead>
                <tr>
                    <th style="min-width: 50px" valign="top" rowspan="2">AKSI</th>
                    <th style='min-width: 100px' rowspan="2">STATUS</th>
                    <th style='min-width: 100px' rowspan="2">KODE REQUEST</th>
                    <th style='min-width: 150px' rowspan="2">KODE LOKET</th>
                    <th style='min-width: 150px' colspan="6">STATUS PERMINTAAN</th>
                    <th style='min-width: 150px' colspan="4">STATUS VERFIKASI</th>

                    <th style='min-width: 250px' rowspan="2">BANK TUJUAN</th>
                    <th style='min-width: 200px' rowspan="2">BANK PENGIRIM</th>
                </tr>
                <tr>
                    <th style='min-width: 100px'>PERMINTAAN</th>
                    <th style='min-width: 100px'>TGL REQUEST</th>
                    <th style='min-width: 100px'>KET REQUEST</th>
                    <th style='min-width: 100px'>KONFIRMASI</th>
                    <th style='min-width: 100px'>KET KONF</th>
                    <th style='min-width: 100px'>TGL KONF</th>

                    <th style='min-width: 100px'>VERIFIKASI</th>
                    <th style='min-width: 100px'>TGL VERIF</th>
                    <th style='min-width: 100px'>DISETUJUI</th>
                    <th style='min-width: 100px'>KET VERIF</th>

                </tr>
            </thead>                
            <tbody>
                <tr>
                    <td></td><td></td><td></td><td></td>
					<td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td>
                    <!--<td></td>
                    <td></td>-->
                </tr>
            </tbody>
        </table>
        
        <hr/>
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalVerifikasi">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">INPUT KONFIRMASI PEMBAYARAN</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">

                <div class='form-group'>
                    <label for='request_code' >KODE REQUEST</label>
                    <input type='text' class='form-control' id='request_code' readonly="readonly" >
                </div>

                <div class='form-group'>
                    <label for='kode_loket' >LOKET</label>
                    <input type='text' class='form-control' id='kode_loket' readonly="readonly" >
                </div>

                <div class='form-group'>
                    <label for='request_saldo' >SALDO DIMINTA</label>
                    <input type='text' class='form-control' id='request_saldo' readonly="readonly" >
                </div>

                <div class='form-group'>
                    <label for='status_verifikasi' >STATUS PERMINTAAN</label>
                    <select class="form-control select2" style="width: 100%" id="status_verifikasi" onchange="changeSaldoSetuju(this.value)">
                        <option value="DITOLAK">DITOLAK</option>
                        <option value="DISETUJUI">DISETUJUI</option>
                    </select>
                </div>

                <div class='form-group' style="display: none" id="DivSaldoSetuju">
                    <label for='verifikasi_saldo'>SALDO DISETUJUI</label>
                    <input type='text' class='form-control' id='verifikasi_saldo' placeholder='Enter SALDO DISETUJUI'>
                </div>

                <div class='form-group'>
                    <label for='ket_verifikasi' >CATATAN</label>
                    <textarea class="form-control" rows="3" id='ket_verifikasi' placeholder="Enter CATATAN"></textarea>
                </div>

                <div id="divPesan"></div>

            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnKonfirmasi" onclick="simpanVerfikasi()">Simpan</button>
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
<!-- /.content -->

@endsection