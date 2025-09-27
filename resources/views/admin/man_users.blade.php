@extends('.../layouts/template')

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
        $("#Username").val("");
        $("#Password").val("");
        $("#Email").val("");
    }

    function showDialog(){
        $("#Id").val('');
        $("#divPesan").html('');
        Kosongkan();
        $('#modalUser').modal("show");
    }

    function LoadUsers(){
        dtTable = $('#usersTable').dataTable( {
            "ajax": "{{ url('/admin/users/get_all') }}",
            "destroy": true,
            "columns": [
                { "data": "aksi" },
                { "data": "username" },
                { "data": "role" },
                { "data": "nama" },
                { "data": "email" }
                
            ],
            "aoColumnDefs": [ {
            "aTargets": [ 0 ],
            "mRender": function (data, type, full) {
                    var formmatedvalue = "<button type='button' onclick=\"getEdit("+full.id+")\" class='btn btn-primary btn-xs'>Edit</button> " + "<button type='button' onclick=\"confirmDelete("+full.id+")\" class='btn btn-primary btn-xs'>Delete</button> " + "<button type='button' onclick=\"closeConn("+full.id+")\" class='btn btn-primary btn-xs'>Close</button>";

                    return formmatedvalue;
                }
            },

            ]       
        });
        Kosongkan();
    }

    function simpanUser(){
        var roles = [];
        $(':checkbox:checked').each(function(i){
          roles[i] = $(this).val();
        });

        var DtUser = {
            "username": $("#Username").val(),
            "password": $("#Password").val(),
            "email": $("#Email").val(),
            "role": $("#role").val(),
            "loket_id": $("#loket_id").val(),
            "roles": roles
        }

        mId = $("#Id").val();
        if(mId.length > 0){
            sentAjax("{{ url('/admin/users/update') }}/"+mId,DtUser);
        }else{
            sentAjax("{{ url('/admin/users/add') }}",DtUser);
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
        $.getJSON("{{ url('admin/users/get') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $("#Id").val(msg.data.id);
                $("#Username").val(msg.data.username);
                $("#Password").val("");
                $("#Email").val(msg.data.email);

                var count = 0;
                $(":checkbox").each(function(i){
 
                    //console.log($(this).next('label').text() + " == " + msg.roles[count])
                    var cek = $(this).next('label').text();
                    if(msg.roles.indexOf(cek) == -1){
                        $(this).prop("checked",false);
                    }else{
                        $(this).prop("checked",true);
                    }
                    count++;
                });

                mLoket = msg.data.loket_id;

                $("#loket_id").val(mLoket).change();

                $("#divPesan").html('');
                $('#modalUser').modal("show");
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
    }

    function confirmDelete(mId){
        $("#isiConfirm").html("Hapus User ini?");
        $("#btnHapus").attr("onclick","deleteUser("+mId+");");
        $('#modalConfirm').modal("show");
    }

    function closeConn(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ url('/admin/users/close_conn') }}/"+mId,
            data: { Data: mData,
                   _token: "{{ csrf_token() }}" }
        })
        .done(function(msg) {
            if(msg.status == "Success"){
                $('#modalConfirm').modal("hide");

                showPesan(msg.message);

            }else{
                $('#modalConfirm').modal("hide");

                showPesan(msg.message);

            }
        });
    }

    function deleteUser(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ url('/admin/users/delete') }}/"+mId,
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
      <h3 class="box-title">DAFTAR USER LOGIN</h3>
    </div>
    <div class="box-body">
        <div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped dataTable" id="usersTable">
            <thead>
                <tr>
                    <th style="min-width: 50px">AKSI</th><th style="min-width: 80px">Username</th><th style="min-width: 150px">Roles</th><th style="min-width: 100px">Nama Loket</th><th style="min-width: 100px">Email</th>
                </tr>
            </thead>                
            <tbody>
                <tr>
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
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="Tambah User" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Input Users</h4>
      </div>
      <div class="modal-body">

         <form role="form" id="formUser">

            <input type="text" class="form-control" id="Id" placeholder="Id User" readonly="readonly" style="display: none; height: 5px">

            <div class="form-group">
              <label for="Username">Username</label>
              <input type="text" class="form-control" id="Username" placeholder="Enter Username">
            </div>
            <div class="form-group">
              <label for="Password">Password</label>
              <input type="password" class="form-control" id="Password" placeholder="Enter Password">
            </div>
            <div class="form-group">
              <label for="Email">Email</label>
              <input type="text" class="form-control" id="Email" placeholder="Enter Email">
            </div>
            <div class="form-group">
              <label for="Role">Role</label>
              <select class="form-control" name="role" id="role" style="width: 100%">
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                    <option value="laporan">Laporan</option>
                </select>
            </div>
            
            <div class='form-group'>
                @foreach ($roles as $role)
                    {{ Form::checkbox('roles[]',  $role->id ) }}
                    {{ Form::label($role->name, ucfirst($role->name)) }}<br>

                @endforeach
            </div>
            <div class="form-group">
              <label for="Loket">Loket</label>
              <select class="form-control" name="loket_id" id="loket_id" style="width: 100%">
                    @foreach($lokets as $loket)
                        <option value="{{$loket->id}}">{{$loket->nama}}</option>
                    @endforeach
              </select>
            </div>
            
            <div id="divPesan"></div>

        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSimpan" onclick="simpanUser()">Simpan</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>

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
<!-- /.content -->

@endsection