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
        $("#txtTanggal").datepicker({ dateFormat: 'yy-mm-dd' });
        LoadUsers();
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    });
</script>

<script type="text/javascript">

    function Kosongkan(){
        $("#topup_money").val("");
        $("#topup_money").val("");
        $("#note").val("");
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

    function LoadUsers(excel){
        var tgl = $("#txtTanggal").val();
        var loket = $("#loketFilter").val();
        var user = $("#userFilter").val();

        if(excel == 1){
            window.open("{{ secure_url('/admin/topups/get_all') }}/"+tgl+"/"+loket+"/"+user+"/"+excel);
        }else{
            dtTable = $('#usersTable').dataTable( {
                "ajax": "{{ secure_url('/admin/topups/get_all') }}/"+tgl+"/"+loket+"/"+user+"/"+excel,
                "serverSide": true,
                "ordering": false,
                "deferRender": true,
                "processing": true,
                "destroy": true,
                "columns": [
                    { "data": "topup_date" },
                    { "data": "loket_code" },
                    { "data": "nama" },
                    { "data": "topup_money" },
                    { "data": "pulsa" },
                    { "data": "user_topup" },
                    { "data": "tujuan_dana" },
                    { "data": "note" }
                ],
                "aoColumnDefs": [ {
                    "aTargets": [ 3,4 ],
                    "mRender": function (data, type, full) {
                        var formmatedvalue = "<span style='float:right'>" + numeral(data).format('0,0') + "</span>";
                        return formmatedvalue;
                    }
                }],  

                "scrollX": true,
                "scrollY": 400,
                "scroller": {
                    "loadingIndicator": true
                }  
            });

            $('.dataTables_filter input').unbind().bind('keyup', function(e){
                if (e.keyCode == 13 || $(this).val().length <= 0){
                    dtTable.fnFilter($(this).val());
                }
                
            });

            Kosongkan();
        }
    }

    function simpanUser(){

        var DtUser = {
            "loket_id": $("#loket_id").val(),
            "topup_money": $("#topup_money").val(),
            "topup_date": $("#topup_date").val(),
            "note": $("#note").val(),
            "tujuan_dana": $("#tujuan_dana").val()
        }

        sentAjax("{{ secure_url('/admin/topups/add') }}",DtUser);

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

        <div class='form-group'>
    		<div class="row">
    			<div class="col-md-2">
    				<input id="txtTanggal" type="text" style="width: 100%" style="text-align: right;" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
    			</div>
    			<div class="col-md-2">
    				<select class="form-control select2" id="loketFilter">
                        <option value="-">Semua</option>
						@foreach($lokets as $loket)
                            <option value="{{$loket->id}}">{{$loket->nama}}</option>
                        @endforeach
					</select>
    			</div>

                <div class="col-md-2">
                    <select class="form-control select2" id="userFilter">
                        <option value="-">Semua</option>
                        @foreach($users as $user)
                            <option value="{{$user->id}}">{{$user->username}}</option>
                        @endforeach
                    </select>
                </div>

    			<div class="col-md-3">


    				<input type="button" name="filter" id="filter" value="Filter" onclick="LoadUsers(0)" class="btn btn-primary" />
                    <input type="button" name="excel" id="excel" value="Excel" onclick="LoadUsers(1)" class="btn btn-primary" />

    			</div>
    		</div>
    		
    	</div>

<!--         <div style="width:100%;overflow:auto;"> -->
        <table class="table table-bordered table-hover table-striped dataTable" id="usersTable">
            <thead>
                <tr>
                    <th style="min-width: 150px">Tanggal</th>
                    <th style="min-width: 80px">Kode Loket</th>
                    <th style="min-width: 100px">Nama Loket</th>
                    <th style="min-width: 100px">Rupiah Topup</th>
                    <th style="min-width: 100px">Saldo Akhir</th>
                    <th style="min-width: 100px">User Topup</th>
                    <th style="min-width: 100px">Tujuan Dana</th>
                    <th style="min-width: 200px">Catatan</th>
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
<!--         </div> -->
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="Topup Pulsa" class="btn btn-primary btn-sm" />
 
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
                <div class="form-group">
                  <label for="Loket">Loket</label>
                  <select class="form-control" name="loket_id" id="loket_id" style="width: 100%">
                        @foreach($lokets as $loket)
                            <option value="{{$loket->id}}">{{$loket->nama}}</option>
                        @endforeach
                  </select>
                </div>

                <div class="form-group">
                  <label for="topup_money">Jumlah Topup</label>
                  <input type="text" class="form-control" id="topup_money" placeholder="Enter Jumlah Topup">
                </div>
                <div class="form-group">
                  <label for="tujuan_dana">Tujuan Dana</label>
                  <select class="form-control" name="tujuan_dana" id="tujuan_dana" style="width: 100%">
                        <option value="BRI KOPKAR PEDAMI">BRI KOPKAR PEDAMI</option>
                        <option value="BNI KHAIRUDDIN">BNI KHAIRUDDIN</option>
                        <option value="BNI KOPERASI PEDAMI">BNI KOPERASI PEDAMI</option>
                        <option value="CASH">CASH</option>
                        <option value="FEE LOKET">FEE LOKET</option>
                  </select>
                </div>
<!--                 <div class="form-group">
                  <label for="fee_loket">Fee Loket</label>
                  <input type="text" class="form-control" id="fee_loket" placeholder="Enter Fee Loket">
                </div> -->
                <div class="form-group">
                  <label for="topup_date">Tanggal Topup</label>
                  <input type="text" class="form-control" value="<?php echo date("Y-m-d H:i:s"); ?>" id="topup_date" readonly="readonly" placeholder="Enter Tanggal Topup">
                </div>
                <div class="form-group">
                  <label>Catatan</label>
                  <textarea class="form-control" id="note" rows="3" placeholder="Enter ..."></textarea>
                </div>

                <div id="divPesan"></div>

            </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnSimpan" class="btn btn-primary" onclick="simpanUser()">Simpan</button>
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