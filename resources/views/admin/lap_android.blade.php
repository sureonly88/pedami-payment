@extends('...layouts/template')

@section('content')
<div id="dialogSearch">
	<table id="dataSearch" class="table table-striped table-hover">
		<thead>
		<tr>
			<th>KODE</th>
			<th>NAMA</th>
		</tr>
		</thead>
		<tbody id="dataSearchList">
		<tr>
			<td>-</td>
			<td>-</td>
		</tr>

		</tbody>
	</table>
</div>

<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<script>

$(document).ready(function() {
	$("select").select2();
});

function cetakLaporan(){
	TglAwal = $("#txtTglAwal").val();
	TglAkhir = $("#txtTglAkhir").val();
	KodeLoket = $("#pilLoket").val();
    window.open("{{ secure_url('admin/lap_android/laporan/cetak') }}/"+TglAwal+"/"+TglAkhir+"/"+KodeLoket,'_blank');
}

function exportLaporan(){
	TglAwal = $("#txtTglAwal").val();
	TglAkhir = $("#txtTglAkhir").val();
	KodeLoket = $("#pilLoket").val();
    window.open("{{ secure_url('admin/lap_android/laporan/export') }}/"+TglAwal+"/"+TglAkhir+"/"+KodeLoket,'_blank');
}

function tampilData(){
	TglAwal = $("#txtTglAwal").val();
	TglAkhir = $("#txtTglAkhir").val();
	KodeLoket = $("#pilLoket").val();
    dtTable = $('#dataTableDetail').dataTable( {
        "ajax": "{{ secure_url('admin/lap_android/laporan') }}/"+TglAwal+"/"+TglAkhir+"/"+KodeLoket,
        "destroy": true,
        "columns": [
            { "data": "CUST_ID" },
            { "data": "NAMA" },
            { "data": "USERNAME" },
            { "data": "LOKET_CODE" },
            { "data": "TRANSACTION_DATE" },
            { "data": "JML" },
            { "data": "SUB_TOTAL" },
            { "data": "ADMIN" },
            { "data": "TOTAL" }
        ],
        "aaSorting": [],
		"footerCallback": function ( row, data, start, end, display ) {
			var api = this.api(), data;
			var juml = 0;
			var pdam = 0;
			var admin = 0;
			var total = 0;
			
			api.column(5)
                .data()
                .reduce( function (a, b) {
					juml += b;
                },0);
			api.column(6)
                .data()
                .reduce( function (a, b) {
					console.log(b.replace(/,/g,""));
					pdam += parseInt(b.replace(/,/g,""));
                },0);
			api.column(7)
                .data()
                .reduce( function (a, b) {
					console.log(b.replace(/,/g,""));
					admin += parseInt(b.replace(/,/g,""));
                },0);
			api.column(8)
                .data()
                .reduce( function (a, b) {
					console.log(b.replace(/,/g,""));
					total += parseInt(b.replace(/,/g,""));
                },0);
			$("#txtJml").html(juml);
			$("#txtPdam").html(numeral(pdam).format('0,0'));
			$("#txtAdmin").html(numeral(admin).format('0,0'));
			$("#txtTotal").html(numeral(total).format('0,0'));
		}
    });
}

dialogSearch = $("#dialogSearch").dialog({
    autoOpen: false,
    height: 650,
    width: 550,
    modal: true,
    buttons: {
        Cancel: function() {
          $(this).dialog("close");
        }
    }
});

function openDialogSearch(){
	dialogSearch.dialog("open");

	dtTable = $('#dataSearch').dataTable( {
        "ajax": "{{ secure_url('admin/get_lokets') }}",
        "destroy": true,
        "columns": [
            { 
            	"data": "loket_code",
            	"render": function ( data, type, full, meta ) {
			      return '<a href="#" onclick="selectSearch('+"'"+data+"'"+')">'+data+'</a>';
			    } 
			},
            { "data": "nama" }
        ]
    });
}

function selectSearch(KodeLoket){
	dialogSearch.dialog("close");
	$("#pilLoket").val(KodeLoket);
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

<div class="box box-primary" style="padding: 10px 10px 10px 10px">

<div class="row">
	<div class="col-md-3">
		<div class="box">
			<div class="box-header with-border">
			  <h3 class="box-title">Laporan Harian</h3>
			</div>
			<form role="form">
				<div class="box-body">

					<div class="form-group">
						<label for="txtTglAwal" class="control-label">
							Tanggal Awal</label>
						<input id="txtTglAwal" type="text" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
						<script>
							$("#txtTglAwal").datepicker({ dateFormat: 'yy-mm-dd' });
						</script>
					</div>

					<div class="form-group">
						<label for="txtTglAkhir" class="control-label">
							Tanggal Akhir</label>
						<input id="txtTglAkhir" type="text" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
						<script>
							$("#txtTglAkhir").datepicker({ dateFormat: 'yy-mm-dd' });
						</script>
					</div>

					<div class="form-group">
						<label for="pilLoket" class="control-label">
							Loket</label>
						<select class="form-control select2" id="pilLoket" multiple="multiple">
							@foreach($lokets as $loket)
								<option value="{{ $loket->loket_code }}">{{ strtoupper($loket->nama) }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="box-footer">
						<button type="button" class="btn btn-primary" onclick="tampilData()">
							Proses</button>
					</div>

			</form>
		</div>
	</div>

	<div class="col-md-9">
		<div class="box">
			
			<form role="form">
				<div class="box-body">
					<div style="width:100%;overflow:auto;">
						<table id="dataTableDetail" class="table table-bordered table-hover table-striped dataTable">
						<thead>
							<tr>
								<th>NOPEL</th>
								<th style="min-width:100px">NAMA</th>
								<th style="min-width:80px">USERNAME</th>
								<th style="min-width:100px">LOKET CODE</th>
								<th style="min-width:150px">TGL TRANSAKSI</th>
								<th style="min-width:50px">JML</th>
								<th style="min-width:100px">SUBTOTAL</th>
								<th style="min-width:100px">ADMIN</th>
								<th style="min-width:100px">TOTAL</th>
							</tr>
							</thead>
							<tbody id="dataDetail">
							<tr>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
							</tr>

							</tbody>
							<tfoot>
								<tr>
									<th colspan="5">TOTAL</th>
									<th id="txtJml" role="row">0</th>
									<th id="txtPdam" role="row">0</th>
									<th id="txtAdmin" role="row">0</th>
									<th id="txtTotal" role="row">0</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<hr/>

					<button type="button" class="btn btn-primary" id="btnCetak" onclick="cetakLaporan()">
						Cetak</button>
					<button type="button" class="btn btn-primary" id="btnExcel" onclick="exportLaporan()">
						Excel</button>

				</div>
			</form>
		</div>
	</div>
</div>

</div>
</section>
@endsection