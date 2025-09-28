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
		$('#usersTable').DataTable();
        LoadUsers();
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    });
</script>

<script type="text/javascript">

    function Kosongkan(){
        $("#Id").val("");
        $("#nama").val("");
        $("#alamat").val("");
        $("#loket_code").val("");
        $("#blok_message").val("");
        $("#byadmin").val("");
        $('#is_blok').prop('checked', false); 
    }

    function showDialog(){
        $("#Id").val('');
        $("#divPesan").html('');
        Kosongkan();
        //dialogUser.dialog("open");

        $('#modalUser').modal("show");
    }

    // <th>ID</th><th>Nama</th><th>Alamat</th><th>Kode Loket</th><th>Pulsa</th><th>Blok</th><th>Biaya Admin</th><th>Jenis</th>
    //                 <th>Aksi</th>

    function LoadUsers(){
        dtTable = $('#usersTable').dataTable( {
            "ajax": "{{ secure_url('/admin/lokets/get_all') }}",
            "destroy": true,
            "columns": [
                { "data": "aksi" },
                { "data": "nama" },
                { "data": "alamat" },
                { "data": "loket_code" },
                { "data": "pulsa" },
                { "data": "is_blok" },
                { "data": "byadmin" },
                { "data": "jenis" },
                
            ],
            "aoColumnDefs": [ {
            "aTargets": [ 0 ],
            "mRender": function (data, type, full) {
                    var formmatedvalue = "<button type='button' onclick=\"getEdit("+full.id+")\" class='btn btn-primary btn-xs'>Edit</button> " + "<button type='button' onclick=\"confirmDelete("+full.id+")\" class='btn btn-primary btn-xs'>Delete</button>";
                    return formmatedvalue;
                }
            },

            {
            "aTargets": [ 4,6 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue= numeral(data).format('0,0')
                    return formmatedvalue;
                }
            },

            ]       
        });
        Kosongkan();
    }

    function simpanUser(){

        $checkBlok = $("#is_blok").is(':checked') ? 1 : 0;

        var DtUser = {
            "nama": $("#nama").val(),
            "alamat": $("#alamat").val(),
            "loket_code": $("#loket_code").val(),
            "is_blok": $checkBlok,
            "blok_message": $("#blok_message").val(),
            "byadmin": $("#byadmin").val(),
            "jenis": $("#jenis").val()
        }

        //console.log($("#is_blok").is(':checked'));

        mId = $("#Id").val();
        if(mId.length > 0){
            sentAjax("{{ secure_url('/admin/lokets/update') }}/"+mId,DtUser);
        }else{
            sentAjax("{{ secure_url('/admin/lokets/add') }}",DtUser);
        }    
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

                LoadUsers();
            }else{
                $("#divPesan").html('');
                mPesan = "";
                for(i=0;i<msg.message.length; i++){
                    mPesan += "* " + msg.message[i] + "<br/>";
                }
                $("#pesanError").html(mPesan);
                $("#divPesan").html( $("#ErrorMessage").html() );

                console.log("ERROR");
            }

            $("#btnSimpan").attr("disabled", false);

        });
    }

    function getEdit(mId){
        $.ajaxSetup({ cache: false });
        $.getJSON("{{ secure_url('admin/lokets/get') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $("#Id").val(msg.data.id);
                $("#nama").val(msg.data.nama);
                $("#alamat").val(msg.data.alamat);
                $("#loket_code").val(msg.data.loket_code);
                $("#byadmin").val(msg.data.byadmin);
                $("#blok_message").val(msg.data.blok_message);
                
                mJenis = msg.data.jenis;
                mBlok = msg.data.is_blok;

                if(mBlok > 0){
                    $('#is_blok').prop('checked', true); 
                }else{
                    $('#is_blok').prop('checked', false); 
                }
                $("#jenis").val(mJenis).change();

                $("#divPesan").html('');
                $('#modalUser').modal("show");
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
    }

    function confirmDelete(mId){
        $("#isiConfirm").html("Hapus Loket ini?");
        $("#btnHapus").attr("onclick","deleteUser("+mId+");");
        $('#modalConfirm').modal("show");
        //$('#myModal').modal('hide')
    }

    function deleteUser(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ secure_url('/admin/lokets/delete') }}/"+mId,
            data: { Data: mData,
                   _token: "{{ csrf_token() }}" }
        })
        .done(function(msg) {
            if(msg.status == "Success"){
                $('#modalConfirm').modal("toggle");

                showPesan(msg.message);
                LoadUsers();
                $("#btnHapus").removeAttr("onclick","");
            }else{
                $('#modalConfirm').modal("toggle");

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
      <h3 class="box-title">DAFTAR LOKET</h3>
    </div>
    <div class="box-body">
        <div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped dataTable" id="usersTable">
            <thead>
                <tr>
                    <th style="min-width: 100px">Aksi</th>
                    <th style="min-width: 150px">Nama</th><th style="min-width: 150px">Alamat</th><th style="min-width: 100px">Kode Loket</th><th>Pulsa</th><th>Blok</th><th style="min-width: 100px">Biaya Admin</th><th>Jenis</th>
                    
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
        </div>
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="Tambah Loket" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Input Loket</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formUser">

                <input type="text" class="form-control" id="Id" placeholder="Id User" readonly="readonly" style="display: none; height: 5px">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                          <label for="loket_code">Kode Loket</label>
                          <input type="text" class="form-control" id="loket_code" placeholder="Enter Kode Loket">
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                          <label for="nama">Nama Loket</label>
                          <input type="text" class="form-control" id="nama" placeholder="Enter Nama">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                  <label for="alamat">Alamat Loket</label>
                  <input type="text" class="form-control" id="alamat" placeholder="Enter Alamat">
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                          <label for="byadmin">Biaya Admin</label>
                          <input type="text" class="form-control" id="byadmin" placeholder="Enter Biaya Admin">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                          <label for="jenis">Role</label>
                          <select class="form-control" name="jenis" id="jenis" style="width: 100%">
                                <option value="ADMIN">ADMIN</option>
                                <option value="NON_ADMIN">NON ADMIN</option>
                                <option value="SWITCHING">SWITCHING</option>
                                <option value="ANDROID">ANDROID</option>
                                <option value="PM">PEMBACA METER</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" id="is_blok">
                      Blokir Loket
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label>Catatan Blokir</label>
                  <textarea class="form-control" id="blok_message" rows="3" placeholder="Enter ..."></textarea>
                </div>

                <div id="divPesan"></div>

            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSimpan" onclick="simpanUser()">Simpan</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

@include('admin.modals')

<div style="visibility: collapse;" id="ErrorMessage">
<div class="alert alert-danger" >
    <div id="pesanError"></div>
</div>
</div>

<div style="visibility: collapse;" id="SuccessMessage">
<div class="alert alert-success" >
    <div id="pesanSimpan"></div>
</div>
</div>

</section>

@endsection