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
		$('#subscriber_id').val('');$('#material_number').val('');$('#subscriber_name').val('');$('#subscriber_segment').val('');$('#power_categori').val('');$('#switcher_ref_number').val('');$('#pln_ref_number').val('');$('#token_number').val('');$('#trace_audit_number').val('');$('#vending_recieve_number').val('');$('#max_kwh').val('');$('#purchase_kwh').val('');$('#info_text').val('');$('#stump_duty').val('');$('#ligthingtax').val('');$('#cust_payable').val('');$('#admin_charge').val('');$('#addtax').val('');$('#username').val('');$('#loket_name').val('');$('#loket_code').val('');$('#jenis_loket').val('');$('#transaction_code').val('');$('#transaction_date').val('');$('#power_purchase').val('');$('#rupiah_token').val('');
    }

    function showDialog(){
        $("#Id").val('');
        $("#divPesan").html('');
        Kosongkan();

        $('#modalUser').modal("show");
    }

    function LoadData(){

        dtTable = $('#listData').dataTable( {
            "ajax": "{{ url('/admin/man_pln_prepaid/list') }}",
            "serverSide": true,
            "ordering": false,
            "deferRender": true,
            "processing": true,
            "destroy": true,
            "columns": [
                { "data": "aksi" },
				{ 'data': 'subscriber_id' },{ 'data': 'material_number' },{ 'data': 'subscriber_name' },{ 'data': 'subscriber_segment' },{ 'data': 'token_number' },{ 'data': 'admin_charge' },{ 'data': 'username' },{ 'data': 'loket_name' },{ 'data': 'loket_code' },{ 'data': 'transaction_date' },{ 'data': 'rupiah_token' }
                
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
            "aTargets": [ 6,11 ],
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
            if (e.keyCode == 13 || $(this).val().length <= 0){
                dtTable.fnFilter($(this).val());
            }
            
        });
        Kosongkan();
    }

    function simpanData(){

        var Data = {
			"id": $("#Id").val(),
			'subscriber_id': $('#subscriber_id').val(),'material_number': $('#material_number').val(),'subscriber_name': $('#subscriber_name').val(),'subscriber_segment': $('#subscriber_segment').val(),'power_categori': $('#power_categori').val(),'switcher_ref_number': $('#switcher_ref_number').val(),'pln_ref_number': $('#pln_ref_number').val(),'token_number': $('#token_number').val(),'trace_audit_number': $('#trace_audit_number').val(),'vending_recieve_number': $('#vending_recieve_number').val(),'max_kwh': $('#max_kwh').val(),'purchase_kwh': $('#purchase_kwh').val(),'info_text': $('#info_text').val(),'stump_duty': $('#stump_duty').val(),'ligthingtax': $('#ligthingtax').val(),'cust_payable': $('#cust_payable').val(),'admin_charge': $('#admin_charge').val(),'addtax': $('#addtax').val(),'username': $('#username').val(),'loket_name': $('#loket_name').val(),'loket_code': $('#loket_code').val(),'jenis_loket': $('#jenis_loket').val(),'transaction_code': $('#transaction_code').val(),'transaction_date': $('#transaction_date').val(),'power_purchase': $('#power_purchase').val(),'rupiah_token': $('#rupiah_token').val()
        }

        sentAjax("{{ url('/admin/man_pln_prepaid/simpan') }}",Data);    
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
        $.getJSON("{{ url('admin/man_pln_prepaid/edit') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $("#Id").val(msg.data.id);
                // $("#imei").val(msg.data.imei);
				$('#subscriber_id').val(msg.data.subscriber_id);$('#material_number').val(msg.data.material_number);$('#subscriber_name').val(msg.data.subscriber_name);$('#subscriber_segment').val(msg.data.subscriber_segment);$('#power_categori').val(msg.data.power_categori);$('#switcher_ref_number').val(msg.data.switcher_ref_number);$('#pln_ref_number').val(msg.data.pln_ref_number);$('#token_number').val(msg.data.token_number);$('#trace_audit_number').val(msg.data.trace_audit_number);$('#vending_recieve_number').val(msg.data.vending_recieve_number);$('#max_kwh').val(msg.data.max_kwh);$('#purchase_kwh').val(msg.data.purchase_kwh);$('#info_text').val(msg.data.info_text);$('#stump_duty').val(msg.data.stump_duty);$('#ligthingtax').val(msg.data.ligthingtax);$('#cust_payable').val(msg.data.cust_payable);$('#admin_charge').val(msg.data.admin_charge);$('#addtax').val(msg.data.addtax);$('#username').val(msg.data.username);$('#loket_name').val(msg.data.loket_name);$('#loket_code').val(msg.data.loket_code);$('#jenis_loket').val(msg.data.jenis_loket);$('#transaction_code').val(msg.data.transaction_code);$('#transaction_date').val(msg.data.transaction_date);$('#power_purchase').val(msg.data.power_purchase);$('#rupiah_token').val(msg.data.rupiah_token);

                $("#divPesan").html('');
                $('#modalUser').modal("show");
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
    }

    function confirmDelete(mId){
        $("#isiConfirm").html("Hapus MANAGE TRANSAKSI PREPAID ini?");
        $("#btnHapus").attr("onclick","deleteData("+mId+");");
        $('#modalConfirm').modal("show");
    }

    function deleteData(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ url('/admin/man_pln_prepaid/hapus') }}/"+mId,
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
    <li class="active">Manage PLN Prepaid</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">

<div class="box box-primary">
    <div class="box-header">
      <h3 class="box-title">DAFTAR MANAGE TRANSAKSI PREPAID</h3>
    </div>
    <div class="box-body">
<!--         <div style="width:100%;overflow:auto;"> -->
        <table class="table table-bordered table-hover table-striped dataTable" id="listData">
            <thead>
                <tr>
                    <th style="min-width: 80px">AKSI</th>
					<th style='min-width: 100px'>IDPEL</th><th style='min-width: 100px'>NOMOR METER</th><th style='min-width: 200px'>NAMA</th><th style='min-width: 100px'>KATEGORI</th><th style='min-width: 150px'>TOKEN</th><th style='min-width: 100px'>ADMIN CHARGE</th><th style='min-width: 100px'>USERNAME</th><th style='min-width: 100px'>NAMA LOKET</th><th style='min-width: 100px'>KODE LOKET</th><th style='min-width: 150px'>TANGGAL</th><th style='min-width: 100px'>RP TOKEN</th>
                </tr>
            </thead>                
            <tbody>
                <tr>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            </tbody>
        </table>
        <!-- </div> -->
        <hr/>
        <input type="button" name="tambah" id="tambah" onclick="showDialog()" value="TAMBAH" class="btn btn-primary btn-sm" />
 
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Input MANAGE TRANSAKSI PREPAID</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">

                <input type="text" class="form-control" id="Id" placeholder="Id Data" readonly="readonly" style="display: none; height: 5px">

                <div class="row">
                    <div class="col-md-6">
                        <div class='form-group'><label for='subscriber_id' >SUBSCRIBER_ID</label><input type='text' class='form-control' id='subscriber_id' placeholder='Enter SUBSCRIBER_ID'></div><div class='form-group'><label for='material_number' >MATERIAL_NUMBER</label><input type='text' class='form-control' id='material_number' placeholder='Enter MATERIAL_NUMBER'></div><div class='form-group'><label for='subscriber_name' >SUBSCRIBER_NAME</label><input type='text' class='form-control' id='subscriber_name' placeholder='Enter SUBSCRIBER_NAME'></div><div class='form-group'><label for='subscriber_segment' >SUBSCRIBER_SEGMENT</label><input type='text' class='form-control' id='subscriber_segment' placeholder='Enter SUBSCRIBER_SEGMENT'></div><div class='form-group'><label for='power_categori' >POWER_CATEGORI</label><input type='text' class='form-control' id='power_categori' placeholder='Enter POWER_CATEGORI'></div><div class='form-group'><label for='switcher_ref_number' >SWITCHER_REF_NUMBER</label><input type='text' class='form-control' id='switcher_ref_number' placeholder='Enter SWITCHER_REF_NUMBER'></div><div class='form-group'><label for='pln_ref_number' >PLN_REF_NUMBER</label><input type='text' class='form-control' id='pln_ref_number' placeholder='Enter PLN_REF_NUMBER'></div><div class='form-group'><label for='token_number' >TOKEN_NUMBER</label><input type='text' class='form-control' id='token_number' placeholder='Enter TOKEN_NUMBER'></div><div class='form-group'><label for='trace_audit_number' >TRACE_AUDIT_NUMBER</label><input type='text' class='form-control' id='trace_audit_number' placeholder='Enter TRACE_AUDIT_NUMBER'></div><div class='form-group'><label for='vending_recieve_number' >VENDING_RECIEVE_NUMBER</label><input type='text' class='form-control' id='vending_recieve_number' placeholder='Enter VENDING_RECIEVE_NUMBER'></div><div class='form-group'><label for='max_kwh' >MAX_KWH</label><input type='text' class='form-control' id='max_kwh' placeholder='Enter MAX_KWH'></div><div class='form-group'><label for='purchase_kwh' >PURCHASE_KWH</label><input type='text' class='form-control' id='purchase_kwh' placeholder='Enter PURCHASE_KWH'></div><div class='form-group'><label for='info_text' >INFO_TEXT</label><input type='text' class='form-control' id='info_text' placeholder='Enter INFO_TEXT'></div>
                    </div>

                    <div class="col-md-6">
                        <div class='form-group'><label for='stump_duty' >STUMP_DUTY</label><input type='text' class='form-control' id='stump_duty' placeholder='Enter STUMP_DUTY'></div><div class='form-group'><label for='ligthingtax' >LIGTHINGTAX</label><input type='text' class='form-control' id='ligthingtax' placeholder='Enter LIGTHINGTAX'></div><div class='form-group'><label for='cust_payable' >CUST_PAYABLE</label><input type='text' class='form-control' id='cust_payable' placeholder='Enter CUST_PAYABLE'></div><div class='form-group'><label for='admin_charge' >ADMIN_CHARGE</label><input type='text' class='form-control' id='admin_charge' placeholder='Enter ADMIN_CHARGE'></div><div class='form-group'><label for='addtax' >ADDTAX</label><input type='text' class='form-control' id='addtax' placeholder='Enter ADDTAX'></div><div class='form-group'><label for='username' >USERNAME</label><input type='text' class='form-control' id='username' placeholder='Enter USERNAME'></div><div class='form-group'><label for='loket_name' >LOKET_NAME</label><input type='text' class='form-control' id='loket_name' placeholder='Enter LOKET_NAME'></div><div class='form-group'><label for='loket_code' >LOKET_CODE</label><input type='text' class='form-control' id='loket_code' placeholder='Enter LOKET_CODE'></div><div class='form-group'><label for='jenis_loket' >JENIS_LOKET</label><input type='text' class='form-control' id='jenis_loket' placeholder='Enter JENIS_LOKET'></div><div class='form-group'><label for='transaction_code' >TRANSACTION_CODE</label><input type='text' class='form-control' id='transaction_code' placeholder='Enter TRANSACTION_CODE'></div><div class='form-group'><label for='transaction_date' >TRANSACTION_DATE</label><input type='text' class='form-control' id='transaction_date' placeholder='Enter TRANSACTION_DATE'></div><div class='form-group'><label for='power_purchase' >POWER_PURCHASE</label><input type='text' class='form-control' id='power_purchase' placeholder='Enter POWER_PURCHASE'></div><div class='form-group'><label for='rupiah_token' >RUPIAH_TOKEN</label><input type='text' class='form-control' id='rupiah_token' placeholder='Enter RUPIAH_TOKEN'></div>
                    </div>

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