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
            "ajax": "{{ url('/admin/register_hps/get_all') }}",
            "destroy": true,
            "columns": [
                { "data": "aksi" },
                { "data": "username" },
                { "data": "imei" }
                
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

    function simpanUser(){

        $checkBlok = $("#is_blok").is(':checked') ? 1 : 0;

        var DtUser = {
            "username": $("#username").val(),
            "imei": $("#imei").val()
        }

        mId = $("#Id").val();
        if(mId.length > 0){
            sentAjax("{{ url('/admin/register_hps/update') }}/"+mId,DtUser);
        }else{
            sentAjax("{{ url('/admin/register_hps/add') }}",DtUser);
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
        $.getJSON("{{ url('admin/register_hps/get') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $("#Id").val(msg.data.id);
                $("#imei").val(msg.data.imei);
                
                mUsername = msg.data.username;

                $("#username").val(mUsername).change();

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
    }

    function deleteUser(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ url('/admin/register_hps/delete') }}/"+mId,
            data: { Data: mData,
                   _token: "{{ csrf_token() }}" }
        })
        .done(function(msg) {
            if(msg.status == "Success"){
                $('#modalConfirm').modal("hide");

                showPesan(msg.message);
                LoadUsers();
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
      <h3 class="box-title">DAFTAR HANDPHONE</h3>
    </div>
    <div class="box-body">
        <div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped dataTable" id="usersTable">
            <thead>
                <tr>
                    <th style="min-width: 100px">Aksi</th><th style="min-width: 100px">Username</th><th style="min-width: 150px">IMEI</th>
                </tr>
            </thead>                
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        </div>
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="Tambah Handphone" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Input Handphone</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formUser">

                <input type="text" class="form-control" id="Id" placeholder="Id User" readonly="readonly" style="display: none; height: 5px">

                <div class="form-group">
                  <label for="username">Username</label>
                  <select class="form-control" name="username" id="username" style="width: 100%">
                        @foreach($users as $user_list)
                            <option value="{{$user_list->username}}">{{$user_list->username}}</option>
                        @endforeach
                  </select>
                </div>

                <div class="form-group">
                  <label for="imei">IMEI Handphone</label>
                  <input type="text" class="form-control" id="imei" placeholder="Enter IMEI">
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