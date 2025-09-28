@extends('...layouts/template')

@section('content')
<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<!-- bootstrap wysihtml5 - text editor -->
<link rel="stylesheet" href="{{ secure_asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">

<script>
	$(document).ready(function() {
        $("select").select2();
		$('#listData').DataTable();
        LoadData();
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        $(".textarea").wysihtml5();
    });
</script>

<script type="text/javascript">

    function Kosongkan(){
        // $("#Id").val("");
        // $("#nama").val("");
        // $("#alamat").val("");
        // $("#loket_code").val("");
        // $("#blok_message").val("");
        // $("#byadmin").val("");
		$('#judul').val('');$('#isi').val('');
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
            "ajax": "{{ url('/admin/berita/list') }}",
            "destroy": true,
            "columns": [
                { "data": "aksi" },
                // { "data": "username" },
                // { "data": "imei" }
				
				{ 'data': 'judul' },{ 'data': 'isi' },{ 'data': 'user' }
                
            ],
            "aoColumnDefs": [ {
            "aTargets": [ 0 ],
            "mRender": function (data, type, full) {
                    var formmatedvalue = "<button type='button' onclick=\"getEdit("+full.id+")\" class='btn btn-primary btn-xs'>Edit</button> " + "<button type='button' onclick=\"confirmDelete("+full.id+")\" class='btn btn-primary btn-xs'>Delete</button>";
                    return formmatedvalue;
                }
            },


            ]   
        });
        Kosongkan();
    }

    function simpanData(){

        var Data = {
			"id": $("#Id").val(),
            // "username": $("#username").val(),
            // "imei": $("#imei").val()
			'judul': $('#judul').val(),'isi': $('#isi').val()
        }

        sentAjax("{{ url('/admin/berita/simpan') }}",Data);    
    }

    function sentAjax(mUrl, mData){
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
                $("#divPesan").html( $("#SuccessMessage").html() );

                LoadData();
            }else{
                mPesan = "";
                for(i=0;i<msg.message.length; i++){
                    mPesan += "- " + msg.message[i] + "<br/>";
                }
                $("#pesanError").html(mPesan);
                $("#divPesan").html( $("#ErrorMessage").html() );
            }

            $("#btnSimpan").attr("disabled", false);

        });
    }

    function getEdit(mId){
        $.ajaxSetup({ cache: false });
        $.getJSON("{{ url('admin/berita/edit') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $("#Id").val(msg.data.id);
                // $("#imei").val(msg.data.imei);
				$('#judul').val(msg.data.judul);
                //$('#isi').val(msg.data.isi);
                $('.wysihtml5-sandbox').contents().find('body').html(msg.data.isi);

                $("#divPesan").html('');
                $('#modalUser').modal("show");
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
    }

    function confirmDelete(mId){
        $("#isiConfirm").html("Hapus BERITA ini?");
        $("#btnHapus").attr("onclick","deleteData("+mId+");");
        $('#modalConfirm').modal("show");
    }

    function deleteData(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ url('/admin/berita/hapus') }}/"+mId,
            data: { Data: mData,
                   _token: "{{ csrf_token() }}" }
        })
        .done(function(msg) {
            if(msg.status == "Success"){
                $('#modalConfirm').modal("hide");

                showPesan(msg.message);
                LoadData();
                $("#btnHapus").removeAttr("onclick","");
            }else{
                $('#modalConfirm').modal("hide");

                showPesan(msg.message);
                $("#btnHapus").removeAttr("onclick","");
            }
            
        });
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
      <h3 class="box-title">DAFTAR BERITA</h3>
    </div>
    <div class="box-body">
        <div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped dataTable" id="listData">
            <thead>
                <tr>
                    <th style="min-width: 50px">AKSI</th>
					<!--<th style="min-width: 100px">Username</th>
					<th style="min-width: 150px">IMEI</th> -->
					<th style='min-width: 100px'>JUDUL</th>
                    <th style='min-width: 350px'>ISI</th>
                    <th style='min-width: 50px'>USER</th>
                </tr>
            </thead>                
            <tbody>
                <tr>
                    <td></td>
					<td></td><td></td><td></td>
                    <!--<td></td>
                    <td></td>-->
                </tr>
            </tbody>
        </table>
        </div>
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="TAMBAH BERITA" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">INPUT BERITA</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">

                <input type="text" class="form-control" id="Id" placeholder="Id Data" readonly="readonly" style="display: none; height: 5px">

                <!-- <div class="form-group">
                  <label for="imei">IMEI Handphone</label>
                  <input type="text" class="form-control" id="imei" placeholder="Enter IMEI">
                </div> -->
                <div class='form-group'><label for='judul' >JUDUL</label><input type='text' class='form-control' id='judul' placeholder='Enter JUDUL'></div><div class='form-group'><label for='isi' >ISI</label>

                <!-- <textarea class="form-control" rows="3" id='isi' placeholder="Enter ..."></textarea> -->
                <textarea class="textarea" id='isi' placeholder="Ketik berita disini" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                </div>

                <div id="divPesan"></div>

            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSimpan" onclick="simpanData()">SIMPAN</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

<script src="{{ secure_asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"></script>

@include('admin.modals')

<div style="visibility: collapse;" id="ErrorMessage">
<div class="alert alert-danger" >
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-ban"></i> Error!</h4>
    <div id="pesanError"></div>
</div>
</div>

<div style="visibility: collapse;" id="SuccessMessage">
<div class="alert alert-success" >
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i> Success!</h4>
    <div id="pesanSimpan"></div>
</div>
</div>
</section>
@endsection