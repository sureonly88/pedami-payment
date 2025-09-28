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

		$('#subcriber_id').val('');$('#subcriber_name').val('');$('#subcriber_segment').val('');$('#switcher_ref').val('');$('#power_consumtion').val('');$('#trace_audit_number').val('');$('#bill_periode').val('');$('#added_tax').val('');$('#incentive').val('');$('#penalty_fee').val('');$('#admin_charge').val('');$('#total_elec_bill').val('');$('#username').val('');$('#loket_name').val('');$('#loket_code').val('');$('#jenis_loket').val('');$('#transaction_code').val('');$('#transaction_date').val('');$('#outstanding_bill').val('');$('#bill_status').val('');$('#prev_meter_read_1').val('');$('#curr_meter_read_1').val('');$('#prev_meter_read_2').val('');$('#curr_meter_read_2').val('');$('#prev_meter_read_3').val('');$('#curr_meter_read_3').val('');
    }

    function showDialog(){
        $("#Id").val('');
        $("#divPesan").html('');
        Kosongkan();
        $('#modalUser').modal("show");
    }

    function LoadData(){

        dtTable = $('#listData').dataTable( {
            "ajax": "{{ secure_url('/admin/man_transaksi_pln/list') }}",
            "serverSide": true,
            "ordering": false,
            "deferRender": true,
            "processing": true,
            "destroy": true,
            "columns": [
                { "data": "aksi" },
				{ 'data': 'subcriber_id' },
                { 'data': 'subcriber_name' },
                { 'data': 'subcriber_segment' },
                { 'data': 'bill_periode' },
                { 'data': 'bill_pln' },
                { 'data': 'admin_charge' },
                { 'data': 'total' },
                { 'data': 'username' },
                { 'data': 'loket_name' },
                { 'data': 'loket_code' },
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
            "aTargets": [ 5 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue = parseInt(full.added_tax)+parseInt(full.penalty_fee)+parseInt(full.total_elec_bill);
                    formmatedvalue = numeral(formmatedvalue).format('0,0');
                    return formmatedvalue;
                }
            },

            {
            "aTargets": [ 6 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue= numeral(data).format('0,0');
                    return formmatedvalue;
                }
            },

            {
            "aTargets": [ 7 ],
                "mRender": function (data, type, full) {
                    var formmatedvalue = parseInt(full.added_tax)+parseInt(full.penalty_fee)+parseInt(full.admin_charge)+parseInt(full.total_elec_bill);
                    formmatedvalue = numeral(formmatedvalue).format('0,0');
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
			'subcriber_id': $('#subcriber_id').val(),'subcriber_name': $('#subcriber_name').val(),'subcriber_segment': $('#subcriber_segment').val(),'switcher_ref': $('#switcher_ref').val(),'power_consumtion': $('#power_consumtion').val(),'trace_audit_number': $('#trace_audit_number').val(),'bill_periode': $('#bill_periode').val(),'added_tax': $('#added_tax').val(),'incentive': $('#incentive').val(),'penalty_fee': $('#penalty_fee').val(),'admin_charge': $('#admin_charge').val(),'total_elec_bill': $('#total_elec_bill').val(),'username': $('#username').val(),'loket_name': $('#loket_name').val(),'loket_code': $('#loket_code').val(),'jenis_loket': $('#jenis_loket').val(),'transaction_code': $('#transaction_code').val(),'transaction_date': $('#transaction_date').val(),'outstanding_bill': $('#outstanding_bill').val(),'bill_status': $('#bill_status').val(),'prev_meter_read_1': $('#prev_meter_read_1').val(),'curr_meter_read_1': $('#curr_meter_read_1').val(),'prev_meter_read_2': $('#prev_meter_read_2').val(),'curr_meter_read_2': $('#curr_meter_read_2').val(),'prev_meter_read_3': $('#prev_meter_read_3').val(),'curr_meter_read_3': $('#curr_meter_read_3').val()
        }

        sentAjax("{{ secure_url('/admin/man_transaksi_pln/simpan') }}",Data);    
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
        $.getJSON("{{ secure_url('admin/man_transaksi_pln/edit') }}/"+mId, function(msg){
            if(msg.status == "Success"){
                $("#Id").val(msg.data.id);
				$('#subcriber_id').val(msg.data.subcriber_id);
                $('#subcriber_name').val(msg.data.subcriber_name);
                $('#subcriber_segment').val(msg.data.subcriber_segment);
                $('#switcher_ref').val(msg.data.switcher_ref);
                $('#power_consumtion').val(msg.data.power_consumtion);
                $('#trace_audit_number').val(msg.data.trace_audit_number);
                $('#bill_periode').val(msg.data.bill_periode);
                $('#added_tax').val(msg.data.added_tax);
                $('#incentive').val(msg.data.incentive);
                $('#penalty_fee').val(msg.data.penalty_fee);
                $('#admin_charge').val(msg.data.admin_charge);
                $('#total_elec_bill').val(msg.data.total_elec_bill);
                $('#username').val(msg.data.username);
                $('#loket_name').val(msg.data.loket_name);
                $('#loket_code').val(msg.data.loket_code);
                $('#jenis_loket').val(msg.data.jenis_loket);
                $('#transaction_code').val(msg.data.transaction_code);
                $('#transaction_date').val(msg.data.transaction_date);
                $('#outstanding_bill').val(msg.data.outstanding_bill);
                $('#bill_status').val(msg.data.bill_status);
                $('#prev_meter_read_1').val(msg.data.prev_meter_read_1);
                $('#curr_meter_read_1').val(msg.data.curr_meter_read_1);
                $('#prev_meter_read_2').val(msg.data.prev_meter_read_2);
                $('#curr_meter_read_2').val(msg.data.curr_meter_read_2);
                $('#prev_meter_read_3').val(msg.data.prev_meter_read_3);
                $('#curr_meter_read_3').val(msg.data.curr_meter_read_3);

                $("#divPesan").html('');
                $('#modalUser').modal("show");
            }
            
        }).error(function(jqXHR, textStatus, errorThrown){
            
        });
    }

    function confirmDelete(mId){
        $("#isiConfirm").html("Hapus MANAGE PLN PASCABAYAR ini?");
        $("#btnHapus").attr("onclick","deleteData("+mId+");");
        $('#modalConfirm').modal("show");
    }

    function deleteData(mId){
        var mData = {
            "id": $("#Id").val()
        }

        $.ajax({
            method: "POST",
            url: "{{ secure_url('/admin/man_transaksi_pln/hapus') }}/"+mId,
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
    <li class="active">Manage PLN Pascabayar</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">

<div class="box box-primary">
    <div class="box-header">
      <h3 class="box-title">DAFTAR MANAGE PLN PASCABAYAR</h3>
    </div>
    <div class="box-body">
        <!-- <div style="width:100%;overflow:auto;"> -->
        <table class="table table-bordered table-hover table-striped dataTable" id="listData">
            <thead>
                <tr>
                    <th style="min-width: 80px">Aksi</th>
					<th style='min-width: 100px'>IDPEL</th>
                    <th style='min-width: 100px'>NAMA</th>
                    <th style='min-width: 80px'>KATEGORI</th>
                    <th style='min-width: 100px'>PERIODE</th>
                    <th style='min-width: 100px'>RP PLN</th>
                    <th style='min-width: 100px'>ADMIN CHARGE</th>
                    <th style='min-width: 100px'>TOTAL</th>
                    <th style='min-width: 80px'>USERNAME</th>
                    <th style='min-width: 100px'>NAMA LOKET</th>
                    <th style='min-width: 80px'>KODE LOKET</th>
                    <th style='min-width: 120px'>TANGGAL</th>
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
        <h4 class="modal-title">INPUT TRANSAKSI PLN PASCABAYAR</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">

                <input type="text" class="form-control" id="Id" placeholder="Id Data" readonly="readonly" style="display: none; height: 5px">

                <div class="row">
                    <div class="col-md-6">
                        <div class='form-group'><label for='subcriber_id' >SUBCRIBER_ID</label><input type='text' class='form-control' id='subcriber_id' placeholder='Enter SUBCRIBER_ID'></div><div class='form-group'><label for='subcriber_name' >SUBCRIBER_NAME</label><input type='text' class='form-control' id='subcriber_name' placeholder='Enter SUBCRIBER_NAME'></div><div class='form-group'><label for='subcriber_segment' >SUBCRIBER_SEGMENT</label><input type='text' class='form-control' id='subcriber_segment' placeholder='Enter SUBCRIBER_SEGMENT'></div><div class='form-group'><label for='switcher_ref' >SWITCHER_REF</label><input type='text' class='form-control' id='switcher_ref' placeholder='Enter SWITCHER_REF'></div><div class='form-group'><label for='power_consumtion' >POWER_CONSUMTION</label><input type='text' class='form-control' id='power_consumtion' placeholder='Enter POWER_CONSUMTION'></div><div class='form-group'><label for='trace_audit_number' >TRACE_AUDIT_NUMBER</label><input type='text' class='form-control' id='trace_audit_number' placeholder='Enter TRACE_AUDIT_NUMBER'></div>

                        <div class='form-group'><label for='total_elec_bill' >TOTAL_ELEC_BILL</label><input type='text' class='form-control' id='total_elec_bill' placeholder='Enter TOTAL_ELEC_BILL'></div><div class='form-group'><label for='username' >USERNAME</label><input type='text' class='form-control' id='username' placeholder='Enter USERNAME'></div><div class='form-group'><label for='loket_name' >LOKET_NAME</label><input type='text' class='form-control' id='loket_name' placeholder='Enter LOKET_NAME'></div><div class='form-group'><label for='loket_code' >LOKET_CODE</label><input type='text' class='form-control' id='loket_code' placeholder='Enter LOKET_CODE'></div><div class='form-group'><label for='jenis_loket' >JENIS_LOKET</label><input type='text' class='form-control' id='jenis_loket' placeholder='Enter JENIS_LOKET'></div><div class='form-group'><label for='transaction_code' >TRANSACTION_CODE</label><input type='text' class='form-control' id='transaction_code' placeholder='Enter TRANSACTION_CODE'></div>
                        <div class='form-group'><label for='transaction_date' >TRANSACTION_DATE</label><input type='text' class='form-control' id='transaction_date' placeholder='Enter TRANSACTION_DATE'></div>
                    </div>
                    <div class="col-md-6">
                        <div class='form-group'><label for='bill_periode' >BILL_PERIODE</label><input type='text' class='form-control' id='bill_periode' placeholder='Enter BILL_PERIODE'></div><div class='form-group'><label for='added_tax' >ADDED_TAX</label><input type='text' class='form-control' id='added_tax' placeholder='Enter ADDED_TAX'></div><div class='form-group'><label for='incentive' >INCENTIVE</label><input type='text' class='form-control' id='incentive' placeholder='Enter INCENTIVE'></div><div class='form-group'><label for='penalty_fee' >PENALTY_FEE</label><input type='text' class='form-control' id='penalty_fee' placeholder='Enter PENALTY_FEE'></div><div class='form-group'><label for='admin_charge' >ADMIN_CHARGE</label><input type='text' class='form-control' id='admin_charge' placeholder='Enter ADMIN_CHARGE'></div>

                        <div class='form-group'><label for='outstanding_bill' >OUTSTANDING_BILL</label><input type='text' class='form-control' id='outstanding_bill' placeholder='Enter OUTSTANDING_BILL'></div><div class='form-group'><label for='bill_status' >BILL_STATUS</label><input type='text' class='form-control' id='bill_status' placeholder='Enter BILL_STATUS'></div><div class='form-group'><label for='prev_meter_read_1' >PREV_METER_READ_1</label><input type='text' class='form-control' id='prev_meter_read_1' placeholder='Enter PREV_METER_READ_1'></div><div class='form-group'><label for='curr_meter_read_1' >CURR_METER_READ_1</label><input type='text' class='form-control' id='curr_meter_read_1' placeholder='Enter CURR_METER_READ_1'></div><div class='form-group'><label for='prev_meter_read_2' >PREV_METER_READ_2</label><input type='text' class='form-control' id='prev_meter_read_2' placeholder='Enter PREV_METER_READ_2'></div><div class='form-group'><label for='curr_meter_read_2' >CURR_METER_READ_2</label><input type='text' class='form-control' id='curr_meter_read_2' placeholder='Enter CURR_METER_READ_2'></div><div class='form-group'><label for='prev_meter_read_3' >PREV_METER_READ_3</label><input type='text' class='form-control' id='prev_meter_read_3' placeholder='Enter PREV_METER_READ_3'></div><div class='form-group'><label for='curr_meter_read_3' >CURR_METER_READ_3</label><input type='text' class='form-control' id='curr_meter_read_3' placeholder='Enter CURR_METER_READ_3'></div>
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