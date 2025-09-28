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
		//$('#usersTable').DataTable();
        $("#transaction_date").datepicker({ dateFormat: 'yy-mm-dd' }); 
        LoadUsers();
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    });
</script>

<script type="text/javascript">

    function Kosongkan(){
        $('#id').val("");
        $('#transaction_code').val("");
        $('#transaction_date').val("");
        $('#cust_id').val("");
        $('#nama').val("");
        $('#alamat').val("");
        $('#blth').val("");
        $('#harga_air').val("");
        $('#abodemen').val("");
        $('#materai').val("");
        $('#limbah').val("");
        $('#retribusi').val("");
        $('#denda').val("");
        $('#stand_lalu').val("");
        $('#stand_kini').val("");
        $('#sub_total').val("");
        $('#admin').val("");
        $('#total').val("");
        $('#username').val("");
        $('#loket_name').val("");
        $('#loket_code').val("");
        $('#created_at').val("");
        $('#updated_at').val("");
        $('#idgol').val("");
        $('#jenis_loket').val("");

    }

    function aktifInput(){
        $('#transaction_code').attr('readonly',false);
        $('#transaction_date').attr('readonly',false);
        $('#nama').attr('readonly',false);
        $('#alamat').attr('readonly',false);
        $('#harga_air').attr('readonly',false);
        $('#abodemen').attr('readonly',false);
        $('#materai').attr('readonly',false);
        $('#limbah').attr('readonly',false);
        $('#retribusi').attr('readonly',false);
        $('#denda').attr('readonly',false);
        $('#stand_lalu').attr('readonly',false);
        $('#stand_kini').attr('readonly',false);
        $('#sub_total').attr('readonly',false);
        $('#admin').attr('readonly',false);
        $('#total').attr('readonly',false);
        $('#created_at').attr('readonly',false);
        $('#updated_at').attr('readonly',false);
        $('#idgol').attr('readonly',false);

    }

    function showDialog(){
        $("#id").val('');
        $("#divPesan").html('');
        Kosongkan();
        //dialogUser.dialog("open");

        $('#modalUser').modal("show");
    }

    function LoadUsers(){
        mValue  = "-";

        dtTable = $('#usersTable').dataTable( {
            "ajax": "{{ secure_url('/admin/managepdambjm/get_all') }}/"+mValue,
            "serverSide": true,
            "ordering": false,
            "deferRender": true,
            "processing": true,
            "destroy": true,
            "columns": [
                { "data": "aksi" },
                { "data": "transaction_code" },
                { "data": "cust_id" },
                { "data": "nama" },
                { "data": "alamat" },
                { "data": "blth" },
                { "data": "stand_lalu" },
                { "data": "stand_kini" },
                { "data": "sub_total" },
                { "data": "admin" },
                { "data": "total" },
                { "data": "username" },
                { "data": "loket_name" },
                { "data": "loket_code" },
                { "data": "idgol" },
                { "data": "jenis_loket" },
                { "data": "transaction_date" },
               
            ],
            "aoColumnDefs": [ {
            "aTargets": [ 0 ],
            "mRender": function (data, type, full) {

                    var formmatedvalue1 = "<button type='button' onclick=\"getEdit("+full.id+")\" class='btn btn-primary btn-xs'>Edit</button> ";
                    var formmatedvalue2 = "<button type='button' onclick=\"confirmDelete("+full.id+")\" class='btn btn-primary btn-xs'>Delete</button>";
                    if(full.flag_transaksi == "cancel"){
                        formmatedvalue2 = "<button type='button' class='btn btn-danger btn-xs'>Cancel</button>";
                    }
                    return formmatedvalue1 + "&nbsp;" + formmatedvalue2;
                }
            },

            {
            "aTargets": [ 6,7,8,9,10 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue= numeral(data).format('0,0')
                    return formmatedvalue;
                }
            },

            ],
            "scrollX": true,
            "scrollY": 400,
            "scroller": {
                "loadingIndicator": true
            }      
        });

        $('.dataTables_filter input').unbind().bind('keyup', function(e){
            //console.log($(this).val());

            if (e.keyCode == 13 || $(this).val().length <= 0){
                dtTable.fnFilter($(this).val());
            }
            
        });
        Kosongkan();
    }

    function simpanUser(){

        // $checkBlok = $("#is_blok").is(':checked') ? 1 : 0;

        var DtUser = {
            'id': $('#id').val(),
            'transaction_code': $('#transaction_code').val(),
            'transaction_date': $('#transaction_date').val(),
            'cust_id': $('#cust_id').val(),
            'nama': $('#nama').val(),
            'alamat': $('#alamat').val(),
            'blth': $('#blth').val(),
            'harga_air': $('#harga_air').val(),
            'abodemen': $('#abodemen').val(),
            'materai': $('#materai').val(),
            'limbah': $('#limbah').val(),
            'retribusi': $('#retribusi').val(),
            'denda': $('#denda').val(),
            'stand_lalu': $('#stand_lalu').val(),
            'stand_kini': $('#stand_kini').val(),
            'sub_total': $('#sub_total').val(),
            'admin': $('#admin').val(),
            'total': $('#total').val(),
            'username': $('#username').val(),
            'loket_name': $('#loket_name').val(),
            'loket_code': $('#loket_code').val(),
            'created_at': $('#created_at').val(),
            'updated_at': $('#updated_at').val(),
            'idgol': $('#idgol').val(),
            'jenis_loket': $('#jenis_loket').val()
        }
        //var DtUser = $("#formUser").serializeArray();

        mId = $("#id").val();
        if(mId.length > 0){
            sentAjax("{{ secure_url('/admin/managepdambjm/update') }}/"+mId,DtUser);
        }else{
            sentAjax("{{ secure_url('/admin/managepdambjm/add') }}",DtUser);
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
        $.getJSON("{{ secure_url('admin/managepdambjm/get') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $('#id').val(msg.data.id);
                $('#transaction_code').val(msg.data.transaction_code);
                $('#transaction_date').val(msg.data.transaction_date);
                $('#cust_id').val(msg.data.cust_id);
                $('#nama').val(msg.data.nama);
                $('#alamat').val(msg.data.alamat);
                $('#blth').val(msg.data.blth);
                $('#harga_air').val(msg.data.harga_air);
                $('#abodemen').val(msg.data.abodemen);
                $('#materai').val(msg.data.materai);
                $('#limbah').val(msg.data.limbah);
                $('#retribusi').val(msg.data.retribusi);
                $('#denda').val(msg.data.denda);
                $('#stand_lalu').val(msg.data.stand_lalu);
                $('#stand_kini').val(msg.data.stand_kini);
                $('#sub_total').val(msg.data.sub_total);
                $('#admin').val(msg.data.admin);
                $('#total').val(msg.data.total);
                $('#username').val(msg.data.username).change();
                $('#loket_name').val(msg.data.loket_name);
                $('#loket_code').val(msg.data.loket_code);
                $('#created_at').val(msg.data.created_at);
                $('#updated_at').val(msg.data.updated_at);
                $('#idgol').val(msg.data.idgol);
                //$('#jenis_loket').val(msg.data.jenis_loket);

                $("#jenis_loket").val(msg.data.jenis_loket).change();


                $("#divPesan").html('');
                $('#modalUser').modal("show");
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
    }

    function confirmDelete(mId){
        $("#isiConfirm").html("Hapus Transaksi PDAM ini?");
        $("#btnHapus").attr("onclick","deleteUser("+mId+");");
        $('#modalConfirm').modal("show");
        //$('#myModal').modal('hide')
    }

    function deleteUser(mId){
        var mData = {
            "id": $("#id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ secure_url('/admin/managepdambjm/delete') }}/"+mId,
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

    function getLoket(){
        mUsername = $("#username").val();
        $.ajaxSetup({ cache: false });
        $.getJSON("{{ secure_url('admin/managepdambjm/info_loket') }}/"+mUsername, function(msg){
            if(msg.status == "Success"){
                $('#loket_name').val(msg.data.nama);
                $('#loket_code').val(msg.data.loket_code);
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
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
      <h3 class="box-title">DAFTAR TRANSAKSI PDAMBJM</h3>
    </div>
    <div class="box-body">

        <table class="table table-bordered table-hover table-striped dataTable" id="usersTable">
            <thead>
                <tr>
                    <th style="min-width: 80px">Aksi</th>
                    <th style="min-width: 200px">Kode Transaksi</th><th>No.Kontrak</th><th style="min-width: 150px">Nama</th><th style="min-width: 250px">Alamat</th><th>Blth</th>
                    <th style="min-width: 50px">St. Lalu</th>
                    <th style="min-width: 50px">St. Kini</th><th style="min-width: 100px">Sub. Total</th><th>Admin</th><th>Total</th><th>User</th><th style="min-width: 100px">Loket</th>
                    <th style="min-width: 100px">Kode Loket</th>
                    <th>Gol</th>
                    <th>Jenis</th>
                    <th style="min-width: 150px">Tanggal</th>
                    
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
                    <td></td>
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

       <!--  </div> -->
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="Tambah Transaksi" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">INPUT TRANSAKSI PDAMBJM</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formUser">

                <input type="text" class="form-control" id="id" name="id" placeholder="Id User" readonly="readonly" style="display: none; height: 5px">

                <div class='form-group'><label for='cust_id'>No. Kontrak</label><input type='text' name='cust_id' class='form-control' id='cust_id' placeholder='Enter Cust_Id'> </div>
                <div class='form-group'><label for='blth'>Blth</label><input type='text' name='blth' class='form-control' id='blth' placeholder='Enter Blth'> </div>

                <button type="button" class="btn btn-primary" onclick="cekPdam()">Cek Pelunasan PDAM</button>
                <button type="button" class="btn btn-primary" onclick="aktifInput()">Input Manual</button>
                <hr/>

                <div class="row">
                    <div class="col-md-6">
                        <div class='form-group'><label for='transaction_code'>Kode Transaksi</label><input type='text' name='transaction_code' readonly='readonly' class='form-control' id='transaction_code' placeholder='Enter Transaction_Code'> </div>
                        <div class='form-group'><label for='transaction_date'>Tanggal Transaksi</label><input type='text' name='transaction_date' readonly='readonly' class='form-control' id='transaction_date' placeholder='Enter Transaction_Date'> </div>

                        <div class='form-group'><label for='nama'>Nama</label><input type='text' name='nama' readonly='readonly' class='form-control' id='nama' placeholder='Enter Nama'> </div>
                        <div class='form-group'><label for='alamat'>Alamat</label><input type='text' name='alamat' readonly='readonly' class='form-control' id='alamat' placeholder='Enter Alamat'> </div>
                        
                        <div class='form-group'><label for='harga_air'>Harga Air</label><input type='text' name='harga_air' readonly='readonly' class='form-control' id='harga_air' placeholder='Enter Harga_Air'> </div>
                        <div class='form-group'><label for='abodemen'>Abodemen</label><input type='text' name='abodemen' readonly='readonly' class='form-control' id='abodemen' placeholder='Enter Abodemen'> </div>
                        <div class='form-group'><label for='materai'>Materai</label><input type='text' name='materai' readonly='readonly' class='form-control' id='materai' placeholder='Enter Materai'> </div>
                        <div class='form-group'><label for='limbah'>Limbah</label><input type='text' name='limbah' readonly='readonly' class='form-control' id='limbah' placeholder='Enter Limbah'> </div>
                    </div>
                    <div class="col-md-6">
                        <div class='form-group'><label for='retribusi'>Retribusi</label><input type='text' name='retribusi' readonly='readonly' class='form-control' id='retribusi' placeholder='Enter Retribusi'> </div>
                        <div class='form-group'><label for='denda'>Denda</label><input type='text' name='denda' readonly='readonly' class='form-control' id='denda' placeholder='Enter Denda'> </div>
                        <div class='form-group'><label for='stand_lalu'>Stand Lalu</label><input type='text' name='stand_lalu' readonly='readonly' class='form-control' id='stand_lalu' placeholder='Enter Stand_Lalu'> </div>
                        <div class='form-group'><label for='stand_kini'>Stand Kini</label><input type='text' name='stand_kini' readonly='readonly' class='form-control' id='stand_kini' placeholder='Enter Stand_Kini'> </div>
                        <div class='form-group'><label for='sub_total'>Sub Total</label><input type='text' name='sub_total' readonly='readonly' class='form-control' id='sub_total' placeholder='Enter Sub_Total'> </div>
                        <div class='form-group'><label for='admin'>Admin</label><input type='text' name='admin' readonly='readonly' class='form-control' id='admin' placeholder='Enter Admin'> </div>
                        <div class='form-group'><label for='total'>Total</label><input type='text' name='total' readonly='readonly' class='form-control' id='total' placeholder='Enter Total'> </div>
                        <div class='form-group'><label for='idgol'>Gol</label><input type='text' name='idgol' readonly='readonly' class='form-control' id='idgol' placeholder='Enter Idgol'> </div>
                    </div>

                </div>

                <div class="form-group">
                  <label for="username">Username</label>
                  <select class="form-control" name="username" onchange="getLoket()" id="username" style="width: 100%">
                        @foreach($list_users as $list_user)
                            <option value="{{$list_user->username}}">{{$list_user->username}}</option>
                        @endforeach
                  </select>
                </div>

                <div class='form-group'><label for='loket_name'>Nama Loket</label><input type='text' name='loket_name' readonly='readonly' class='form-control' id='loket_name' placeholder='Enter Loket_Name'> </div>
                <div class='form-group'><label for='loket_code'>Kode Loket</label><input type='text' name='loket_code' readonly='readonly' class='form-control' id='loket_code' placeholder='Enter Kode Loket'> </div>

                <div class="form-group">
                  <label for="jenis_loket">Jenis Loket</label>

                    <select class="form-control" name="jenis_loket" id="jenis_loket" style="width: 100%">
                        <option value="KASIR">KASIR LAMA</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="NON_ADMIN">NON ADMIN</option>
                        <option value="SWITCHING">SWITCHING</option>
                        <option value="ANDROID">ANDROID</option>
                        <option value="PM">PEMBACA METER</option>
                    </select>
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

<div style="display: none" id="ErrorMessage">
<div class="alert alert-danger" >
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-ban"></i> Error!</h4>
    <div id="pesanError"></div>
</div>
</div>

<div style="display: none" id="SuccessMessage">
<div class="alert alert-success" >
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i> Success!</h4>
    <div id="pesanSimpan"></div>
</div>
</div>
</section>
@endsection