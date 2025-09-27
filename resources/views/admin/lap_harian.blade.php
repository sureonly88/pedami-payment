@extends('...layouts/template')

@section('content')
<script>

$(document).ready(function() {
	$("select").select2();
	$( "#menuLapHarian" ).prop( "class", "active" );
	$("#txtTanggal").datepicker({ dateFormat: 'yy-mm-dd' });
 });

function tampilDetail(Loket, Tanggal, Username){
    dtTable = $('#dataTableDetail').dataTable( {
        "ajax": "{{ url('admin/harian/detail') }}/"+Tanggal+"/"+Loket+"/"+Username,
        "destroy": true,
        "columns": [
            { "data": "CUST_ID" },
            { "data": "NAMA" },
            { "data": "BLTH" },
            { "data": "HARGA_AIR" },
            { "data": "ABODEMEN" },
            { "data": "MATERAI" },
            { "data": "LIMBAH" },
            { "data": "RETRIBUSI" },
            { "data": "DENDA" },
            { "data": "SUB_TOTAL" },
            { "data": "ADMIN" },
            { "data": "TOTAL" },
            { "data": "TRANSACTION_DATE" },
            { "data": "LOKET_CODE" }

        ]
    });
	$('#btnDetail').attr('onclick',"ctkLaporanHarian('"+Loket+"','"+Tanggal+"','"+Username+"')");
	$('#btnExport').attr('onclick',"exportLaporanHarian('"+Loket+"','"+Tanggal+"','"+Username+"')");
}

function ctkLaporanHarian(Loket, Tanggal, Username){
    window.open("{{ url('admin/ctklaporan/harian/') }}/"+Tanggal+"/"+Loket+"/"+Username,'_blank');
}

function exportLaporanHarian(Loket, Tanggal, Username){
    window.open("{{ url('admin/extlaporan/harian/') }}/"+Tanggal+"/"+Loket+"/"+Username,'_blank');
}

function ctkRekapHarian(){
    userLoket = $("#pilUser").val();
    tglTransaksi = $("#txtTanggal").val();
	pilJenis = $("#pilJenis").val();

	if (!userLoket) {
		userLoket = ['-'];
	}

    window.open("{{ url('admin/ctklaporan/rekap_harian/') }}/"+tglTransaksi+"/"+userLoket+"/"+pilJenis,'_blank');
}

function Log(){
	console.log($('#pilUser').select2("val"));
}

function getLaporanHarian(){
    userLoket = $("#pilUser").val();
    tglTransaksi = $("#txtTanggal").val();
	pilJenis = $("#pilJenis").val();

	if (!userLoket) {
		userLoket = ['-'];
	}

    dtTable = $('#dataTable').dataTable( {
        "ajax": "{{ url('admin/harian/rekap') }}/"+tglTransaksi+"/"+userLoket+"/"+pilJenis,
        "destroy": true,
        "columns": [
            { "data": "TRANSACTION_DATE" },
			{ "data": "USER" },
            { "data": "LOKET_CODE" },
            { "data": "REKENING" },
            { "data": "SUB_TOTAL" },
            { "data": "ADMIN" },
            { "data": "TOTAL" },
            { "data": "AKSI" }
        ],
		"footerCallback": function ( row, data, start, end, display ) {
			var api = this.api(), data;
			var juml = 0;
			var pdam = 0;
			var admin = 0;
			var total = 0;
			
			api.column(3)
                .data()
                .reduce( function (a, b) {
					juml += b;
                },0);
			api.column(4)
                .data()
                .reduce( function (a, b) {
					console.log(b.replace(/,/g,""));
					pdam += parseInt(b.replace(/,/g,""));
                },0);
			api.column(5)
                .data()
                .reduce( function (a, b) {
					console.log(b.replace(/,/g,""));
					admin += parseInt(b.replace(/,/g,""));
                },0);
			api.column(6)
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
</script>

<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<script>

function cetakKwitansi(){
	window.open("{{ url('admin/kwitansi') }}/"+$("#kwLoket").val()+"/"+$("#kwTanggal").val(), "", "left=0,top=0,width=650,height=250");
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
	<div class="col-md-4">
		<div class="box box-default">
			<div class="box-header with-border">
			  <h3 class="box-title">Laporan Harian</h3>
			</div>
			<form role="form">
				<div class="box-body">
					<div class="form-group">
						<label for="inputName" class="control-label">
							Tanggal Transaksi</label>
						<input id="txtTanggal" type="text" style="width: 100%" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
					</div>
						<script>
							
						</script>
					<div>
					<div class="form-group">
						<label for="inputName" class="control-label">
						Jenis</label>
						@if($user["role"] == "admin" or $user["role"] == "laporan")
						<select class="form-control select2" id="pilJenis">
							<option value="-">SEMUA</option>
							<option value="KASIR_ADMIN" @if($user["jenis"]=="KASIR_ADMIN") selected @endif >KASIR ADMIN</option>
							<option value="KASIR_NON_ADMIN" @if($user["jenis"]=="KASIR_NON_ADMIN") selected @endif >KASIR NON ADMIN</option>
							<option value="KASIR_REKANAN" @if($user["jenis"]=="KASIR_REKANAN") selected @endif >KASIR REKANAN</option>
							<option value="ANDROID" @if($user["jenis"]=="ANDROID") selected @endif >ANDROID</option>
							<option value="PM" @if($user["jenis"]=="PM") selected @endif >PEMBACA METER</option>
						</select>
						@else
						<div class="input-icon right">
							<input id="pilJenis" type="text" value="{!!$user['jenis']!!}" readonly="readonly" placeholder="" class="form-control" />
						</div>
						@endif
					</div>
					<div class="form-group">
						<label for="inputName" class="control-label">
						Loket</label>

						@if($user["role"] == "admin"  or $user["role"] == "laporan")
						<select class="form-control select2" id="pilUser" multiple="multiple">
							<!-- <option value="-">SEMUA</option> -->
							@foreach($lokets as $loket)
								<option value="{{ $loket->loket_code }}">{{ strtoupper($loket->nama) }}</option>
							@endforeach
						</select>
						@else
						<div class="input-icon right">
							<input id="pilUser" type="text" value="{!!$user['loket_code']!!}" readonly="readonly" placeholder="" class="form-control" />
						</div>
						@endif	
					</div>

					<div class="box-footer">
						<button type="button" class="btn btn-primary" onclick="getLaporanHarian()">
							Proses</button>
						<!--<button type="button" class="btn btn-primary" onclick="Log()">
							Log</button> -->
					</div>
					
				</div>
			</form>
		</div>

		</div>	
	</div>	

	<div class="col-md-8">
		<div class="box box-default">

			<form role="form">
				<div class="box-body">
					<div class="form-group">
					<div style="width:100%;overflow:auto;">
					<table id="dataTable" class="table table-bordered table-hover table-striped dataTable">
						<thead>
						<tr>
							<th style="min-width:100px">TANGGAL</th>
							<th style="min-width:100px">USER</th>
							<th style="min-width:100px">LOKET</th>
							<th style="min-width:100px">JUMLAH</th>
							<th style="min-width:100px">PDAM</th>
							<th style="min-width:100px">ADMIN</th>
							<th style="min-width:100px">TOTAL</th>
							<th style="min-width:100px">AKSI</th>
						</tr>
						</thead>
						<tbody id="dataLap">
						<tr>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
						</tr>
						<tfoot>
						<tr>
							<th colspan="3">TOTAL</th>
							<th id="txtJml" role="row">0</th>
							<th id="txtPdam" role="row">0</th>
							<th id="txtAdmin" role="row">0</th>
							<th colspan="2" id="txtTotal" role="row">0</th>
						</tr>
						</tfoot>
						</tbody>

					</table>
					</div>
					<hr/>
					<button type="button" class="btn btn-primary" onclick="ctkRekapHarian()">
						Rekap</button>

					</div>
					
				</div>
			</form>
		</div>

	</div>	
</div>	


<div class="box box-default">
	<div class="box-header with-border">
	  <h3 class="box-title">DETAIL TRANSAKSI </h3>
	</div>
	<form role="form">
		<div class="box-body">
			<div class="form-group">
				<div style="width:100%;overflow:auto;">
				<table id="dataTableDetail" class="table table-bordered table-hover table-striped dataTable">
				<thead>
				<tr>
					<th style="min-width:80px">NOPEL</th>
					<th style="min-width:150px">NAMA</th>
					<th style="min-width:50px">BLTH</th>
					<th style="min-width:100px">HARGA</th>
					<th style="min-width:100px">ABODEMEN</th>
					<th style="min-width:100px">MATERAI</th>
					<th style="min-width:100px">LIMBAH</th>
					<th style="min-width:100px">RETRIBUSI</th>
					<th style="min-width:100px">DENDA</th>
					<th style="min-width:100px">SUB TOTAL</th>
					<th style="min-width:100px">ADMIN</th>
					<th style="min-width:100px">TOTAL</th>
					<th style="min-width:150px">TANGGAL</th>
					<th style="min-width:100px">LOKET</th>
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
					<td>-</td>
					<td>-</td>
					<td>-</td>
					<td>-</td>
					<td>-</td>
				</tr>

				</tbody>
			</table>
			</div>

			<hr/>

			<button type="button" class="btn btn-primary" id="btnDetail">
				Cetak</button>
			<button type="button" class="btn btn-primary" id="btnExport">
				Excel</button>

			<button type="button" class="btn btn-primary" id="btnSetoran">
				Cetak Setoran</button>
			</div>					
		</div>
	</form>
</div>

</div>

</form>  
@endsection