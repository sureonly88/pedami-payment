@extends('...layouts/template')

@section('content')
<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<script>
	$(document).ready(function() {
		$('#listData').DataTable();
        LoadData();
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    });
</script>

<script type="text/javascript">

    function Kosongkan(){
		$('#register_number').val('');$('#transaction_code').val('');$('#transaction_name').val('');$('#registration_date').val('');$('#expiration_date').val('');$('#subscriber_id').val('');$('#subscriber_name').val('');$('#pln_ref_number').val('');$('#switcher_ref_number').val('');$('#service_unit_address').val('');$('#service_unit_phone').val('');$('#total_transaction').val('');$('#pln_bill_value').val('');$('#admin_charge').val('');$('#info_text').val('');$('#username').val('');$('#loket_name').val('');$('#loket_code').val('');$('#jenis_loket').val('');$('#transaction_date').val('');$('#transaction_code_pln').val('');$('#trace_audit_number').val('');
    }

    function showDialog(){
        $("#Id").val('');
        $("#divPesan").html('');
        Kosongkan();
        $('#modalUser').modal("show");
    }

    function LoadData(){
        dtTable = $('#listData').dataTable( {
            "ajax": "{{ url('/admin/man_nontaglis/list') }}",
            "destroy": true,
            "columns": [
                { "data": "aksi" },
				{ 'data': 'register_number' },
                { 'data': 'subscriber_id' },
                { 'data': 'subscriber_name' },
                { 'data': 'total_transaction' },
                { 'data': 'pln_bill_value' },
                { 'data': 'admin_charge' },
                { 'data': 'transaction_date' }
                
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
            "aTargets": [ 4,5,6 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue= numeral(data).format('0,0')
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
			'register_number': $('#register_number').val(),'transaction_code': $('#transaction_code').val(),'transaction_name': $('#transaction_name').val(),'registration_date': $('#registration_date').val(),'expiration_date': $('#expiration_date').val(),'subscriber_id': $('#subscriber_id').val(),'subscriber_name': $('#subscriber_name').val(),'pln_ref_number': $('#pln_ref_number').val(),'switcher_ref_number': $('#switcher_ref_number').val(),'service_unit_address': $('#service_unit_address').val(),'service_unit_phone': $('#service_unit_phone').val(),'total_transaction': $('#total_transaction').val(),'pln_bill_value': $('#pln_bill_value').val(),'admin_charge': $('#admin_charge').val(),'info_text': $('#info_text').val(),'username': $('#username').val(),'loket_name': $('#loket_name').val(),'loket_code': $('#loket_code').val(),'jenis_loket': $('#jenis_loket').val(),'transaction_date': $('#transaction_date').val(),'transaction_code_pln': $('#transaction_code_pln').val(),'trace_audit_number': $('#trace_audit_number').val()
        }

        sentAjax("{{ url('/admin/man_nontaglis/simpan') }}",Data);    
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
        $.getJSON("{{ url('admin/man_nontaglis/edit') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $("#Id").val(msg.data.id);
                // $("#imei").val(msg.data.imei);
				$('#register_number').val(msg.data.register_number);$('#transaction_code').val(msg.data.transaction_code);$('#transaction_name').val(msg.data.transaction_name);$('#registration_date').val(msg.data.registration_date);$('#expiration_date').val(msg.data.expiration_date);$('#subscriber_id').val(msg.data.subscriber_id);$('#subscriber_name').val(msg.data.subscriber_name);$('#pln_ref_number').val(msg.data.pln_ref_number);$('#switcher_ref_number').val(msg.data.switcher_ref_number);$('#service_unit_address').val(msg.data.service_unit_address);$('#service_unit_phone').val(msg.data.service_unit_phone);$('#total_transaction').val(msg.data.total_transaction);$('#pln_bill_value').val(msg.data.pln_bill_value);$('#admin_charge').val(msg.data.admin_charge);$('#info_text').val(msg.data.info_text);$('#username').val(msg.data.username);$('#loket_name').val(msg.data.loket_name );$('#loket_code').val(msg.data.loket_code );$('#jenis_loket').val(msg.data.jenis_loket);$('#transaction_date').val(msg.data.transaction_date);$('#transaction_code_pln').val(msg.data.transaction_code_pln);$('#trace_audit_number').val(msg.data.trace_audit_number);

                $("#divPesan").html('');
                $('#modalUser').modal("show");
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
    }

    function confirmDelete(mId){
        $("#isiConfirm").html("Hapus MANAGE NONTAGLIS ini?");
        $("#btnHapus").attr("onclick","deleteData("+mId+");");
        $('#modalConfirm').modal("show");
    }

    function deleteData(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ url('/admin/man_nontaglis/hapus') }}/"+mId,
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

<section class="content-header">
  <h1>
    Dashboard
    <small>Halaman Pedami Payment</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Admin</a></li>
    <li class="active">Manage Nontaglis</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">

<div class="box box-primary">
    <div class="box-header">
      <h3 class="box-title">MANAGE DATA PLN NONTAGLIS</h3>
    </div>
    <div class="box-body">
        <div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped dataTable" id="listData">
            <thead>
                <tr>
                    <th style="min-width: 100px">Aksi</th>
					<!--<th style="min-width: 100px">Username</th>
					<th style="min-width: 150px">IMEI</th> -->
					<th style='min-width: 150px'>NOMOR REGISTRASI</th><th style='min-width: 100px'>IDPEL</th><th style='min-width: 100px'>NAMA</th><th style='min-width: 100px'>TOTAL</th><th style='min-width: 100px'>TAGIHAN PLN</th><th style='min-width: 100px'>ADMIN</th>
                    <th style='min-width: 100px'>TANGGAL</th>
                </tr>
            </thead>                
            <tbody>
                <tr>
                    <td></td>
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <!--<td></td>
                    <td></td>-->
                </tr>
            </tbody>
        </table>
        </div>
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="Tambah MANAGE NONTAGLIS" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Input MANAGE NONTAGLIS</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">

                <input type="text" class="form-control" id="Id" placeholder="Id Data" readonly="readonly" style="display: none; height: 5px">

                <!-- <div class="form-group">
                  <label for="imei">IMEI Handphone</label>
                  <input type="text" class="form-control" id="imei" placeholder="Enter IMEI">
                </div> -->
				<div class='form-group'><label for='register_number' >REGISTER_NUMBER</label><input type='text' class='form-control' id='register_number' placeholder='Enter REGISTER_NUMBER'></div><div class='form-group'><label for='transaction_code' >TRANSACTION_CODE</label><input type='text' class='form-control' id='transaction_code' placeholder='Enter TRANSACTION_CODE'></div><div class='form-group'><label for='transaction_name' >TRANSACTION_NAME </label><input type='text' class='form-control' id='transaction_name' placeholder='Enter TRANSACTION_NAME '></div><div class='form-group'><label for='registration_date' >REGISTRATION_DATE</label><input type='text' class='form-control' id='registration_date' placeholder='Enter REGISTRATION_DATE'></div><div class='form-group'><label for='expiration_date' >EXPIRATION_DATE</label><input type='text' class='form-control' id='expiration_date' placeholder='Enter EXPIRATION_DATE'></div><div class='form-group'><label for='subscriber_id' >SUBSCRIBER_ID</label><input type='text' class='form-control' id='subscriber_id' placeholder='Enter SUBSCRIBER_ID'></div><div class='form-group'><label for='subscriber_name' >SUBSCRIBER_NAME</label><input type='text' class='form-control' id='subscriber_name' placeholder='Enter SUBSCRIBER_NAME'></div><div class='form-group'><label for='pln_ref_number' >PLN_REF_NUMBER</label><input type='text' class='form-control' id='pln_ref_number' placeholder='Enter PLN_REF_NUMBER'></div><div class='form-group'><label for='switcher_ref_number' >SWITCHER_REF_NUMBER</label><input type='text' class='form-control' id='switcher_ref_number' placeholder='Enter SWITCHER_REF_NUMBER'></div><div class='form-group'><label for='service_unit_address' >SERVICE_UNIT_ADDRESS</label><input type='text' class='form-control' id='service_unit_address' placeholder='Enter SERVICE_UNIT_ADDRESS'></div><div class='form-group'><label for='service_unit_phone' >SERVICE_UNIT_PHONE</label><input type='text' class='form-control' id='service_unit_phone' placeholder='Enter SERVICE_UNIT_PHONE'></div><div class='form-group'><label for='total_transaction' >TOTAL_TRANSACTION</label><input type='text' class='form-control' id='total_transaction' placeholder='Enter TOTAL_TRANSACTION'></div><div class='form-group'><label for='pln_bill_value' >PLN_BILL_VALUE</label><input type='text' class='form-control' id='pln_bill_value' placeholder='Enter PLN_BILL_VALUE'></div><div class='form-group'><label for='admin_charge' >ADMIN_CHARGE</label><input type='text' class='form-control' id='admin_charge' placeholder='Enter ADMIN_CHARGE'></div><div class='form-group'><label for='info_text' >INFO_TEXT</label><input type='text' class='form-control' id='info_text' placeholder='Enter INFO_TEXT'></div><div class='form-group'><label for='username' >USERNAME</label><input type='text' class='form-control' id='username' placeholder='Enter USERNAME'></div><div class='form-group'><label for='loket_name' >LOKET_NAME </label><input type='text' class='form-control' id='loket_name' placeholder='Enter LOKET_NAME '></div><div class='form-group'><label for='loket_code' >LOKET_CODE </label><input type='text' class='form-control' id='loket_code' placeholder='Enter LOKET_CODE'></div><div class='form-group'><label for='jenis_loket' >JENIS_LOKET</label><input type='text' class='form-control' id='jenis_loket' placeholder='Enter JENIS_LOKET'></div><div class='form-group'><label for='transaction_date' >TRANSACTION_DATE</label><input type='text' class='form-control' id='transaction_date' placeholder='Enter TRANSACTION_DATE'></div><div class='form-group'><label for='transaction_code_pln' >TRANSACTION_CODE_PLN</label><input type='text' class='form-control' id='transaction_code_pln' placeholder='Enter TRANSACTION_CODE_PLN'></div><div class='form-group'><label for='trace_audit_number' >TRACE_AUDIT_NUMBER</label><input type='text' class='form-control' id='trace_audit_number' placeholder='Enter TRACE_AUDIT_NUMBER'></div>

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