@extends('...layouts/template')

@section('content')
<!-- Content Header (Page header) -->

<section class="content-header">
  <h1>
    Dashboard
    <small>Halaman Pedami Payment</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Admin</a></li>
    <li class="active">Laporan Transaksi</li>
  </ol>
</section>

<!-- Main content -->
<section class="content" id="elLaporan">

<div class="row">
	<div class="col-md-4">
		<div class="box box-primary" style="min-height: 500px">
			<div class="box-header">
		      <h3 class="box-title">LAPORAN TRANSAKSI</h3>
		    </div>

		    <div class="box-body" >
		    	<form action="" >
		    		<div class="row">
		    			<div class="col-md-6">
		    				<div class="form-group">
								<label for="txtTglAwal" class="control-label">
									Tanggal Awal</label>
								<input id="txtTglAwal" type="text" style="width: 100%" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
							</div>
		    			</div>

		    			<div class="col-md-6">
		    				<div class="form-group">
								<label for="txtTglAkhir" class="control-label">
									Tanggal Akhir</label>
								<input id="txtTglAkhir" type="text" style="width: 100%" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
							</div>
		    			</div>
		    		</div>

		    		<div class="form-group">
						<label for="pilJenis" class="control-label">Jenis Transaksi</label>
						<select class="form-control select2" multiple="multiple" id="pilJenis" style="width: 100%">
							<option value="PLN_POSTPAID">PLN POSTPAID</option>
							<option value="PLN_PREPAID">PLN PREPAID</option>
							<option value="PLN_NONTAGLIS">PLN NONTAGLIS</option>
							<option value="PDAM_BANDARMASIH">PDAM BANDARMASIH</option>
							<option value="PLN_POSTPAID_N">PLN POSTPAID NEW</option>
							<option value="PLN_PREPAID_N">PLN PREPAID NEW</option>
						</select>
					</div>

					<div class="form-group">
						<label for="jenisLoket" class="control-label">
						Jenis Loket</label>

						@if(array_search("Lihat Lap. Semua Loket",$user['permissions']) !== false )
						<select class="form-control select2" id="jenisLoket" multiple="multiple" style="width: 100%">
							<option value="KASIR">KASIR LAMA</option>
							<option value="ADMIN">ADMIN</option>
                            <option value="NON_ADMIN">NON ADMIN</option>
                            <option value="SWITCHING">SWITCHING</option>
							<option value="ANDROID">ANDROID</option>
							<option value="PM">PEMBACA METER</option>
						</select>
						@else
						<select class="form-control select2" id="jenisLoket" multiple="multiple" style="width: 100%">
							<option value="KASIR">KASIR LAMA</option>
							<option value="ADMIN">ADMIN</option>
                            <option value="NON_ADMIN">NON ADMIN</option>
						</select>
						@endif	
					</div>

					<div class="form-group">
						<label for="inputName" class="control-label">
						Loket</label>

						@if(array_search("Lihat Lap. Semua Loket",$user['permissions']) !== false )
						<select class="form-control select2" id="KodeLoket" multiple="multiple" style="width: 100%">
							@foreach($lokets as $loket)
								<option value="{{ $loket->loket_code }}">{{ strtoupper($loket->nama) }}</option>
							@endforeach
						</select>
						@else
						<div class="input-icon right">
							<input id="KodeLoket" type="text" value="{!!$user['loket_code']!!}" readonly="readonly" placeholder="" class="form-control" />
						</div>
						@endif	
					</div>

				</form>
			</div>

			<div class="box-footer">
				<button type="button" class="btn btn-primary" @click="prosesLaporan()">
					Proses</button>
			</div>

		</div>
	</div>

	<div class="col-md-8">
		<div class="box box-primary" style="min-height: 500px">
		    <div class="box-header">
		      <h3 class="box-title">REKAP TRANSAKSI</h3>
		    </div>
		    <div class="box-body" >
<!-- 		    	<div style="width:100%;overflow:auto;overflow-y:scroll;height:350px;"> -->
				<table id="rekapTable" class="table table-bordered table-hover table-striped dataTable">
					<thead>
					<tr>
						<th style="min-width:30px">AKSI</th>
						<th style="min-width:40px">USER</th>
						<th style="min-width:140px">LOKET</th>
						<th style="min-width:50px">TANGGAL</th>
						<th style="min-width:40px">JML</th>
						<th style="min-width:70px">RUPIAH</th>
						<th style="min-width:50px">ADMIN</th>
						<th style="min-width:70px">TOTAL</th>
						<th style="min-width:100px">JENIS</th>
						<th style="min-width:100px">JENIS LOKET</th>
					</tr>
					</thead>
					<tbody>
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
					</tr>
					<tfoot>
						<tr>
							<th colspan="4">TOTAL</th>
							<th id="txtJml" role="row">@{{ TotalJumlah }}</th>
							<th id="txtRp" role="row">@{{ TotalTagih }}</th>
							<th id="txtAdmin" role="row">@{{ TotalAdmin }}</th>
							<th id="txtTotal" colspan="3" role="row">@{{ GrandTotal }}</th>
						</tr>
					</tbody>

				</table>
<!-- 				</div> -->
		    </div>
		    <div class="box-footer">
				<button type="button" class="btn btn-primary" onclick="cetakRekapHarian()">
					CETAK</button>
				<button type="button" class="btn btn-primary" onclick="exporRekapHarian()">
					EXCEL</button>
				<button type="button" class="btn btn-primary" onclick="cetakSetoran()">
					CETAK SETORAN</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="box box-primary" style="min-height: 150px">
			<div class="box-header">
		      <h3 class="box-title">DETAIL TRANSAKSI</h3>
		    </div>

		    <div class="box-body" >
		    	<!-- <div style="width:100%;overflow:auto;overflow-y:scroll;height:500px;"> -->
		    	<table id="detailTable" class="table table-bordered table-hover table-striped dataTable">
					<thead>
					<tr>
						<th style="min-width:100px">IDPEL</th>
						<th style="min-width:200px">NAMA</th>
						<th style="min-width:100px">JENIS</th>
						<th style="min-width:80px">PERIODE</th>
						<th style="min-width:100px">TAGIHAN</th>
						<th style="min-width:100px">ADMIN</th>
						<th style="min-width:100px">TOTAL</th>
						<th style="min-width:100px">TANGGAL</th>
						<th style="min-width:100px">JAM</th>
						<th style="min-width:150px">LOKET</th>
						<th style="min-width:100px">USER</th>
					</tr>
					</thead>
					<tbody>
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
					</tr>
					
					</tbody>

				</table>
<!-- 				</div> -->
		    </div>

		    <div class="box-footer">
				<button type="button" id="cetakDetail" class="btn btn-primary">
					CETAK</button>
				<button type="button" id="excelDetail" class="btn btn-primary">
					EXCEL</button>
			</div>

		</div>

		
	</div>
</div>

</section>

<script>

function cetakSetoran(){
	TglAwal = $("#txtTglAwal").val();
	TglAkhir = $("#txtTglAkhir").val();
    window.open("{{ secure_url('admin/setoran') }}/"+TglAwal+"/"+TglAkhir,'_blank','width=880,height=500,titlebar=0,status=0');
}

function cetakRekapHarian(){
	vPilJenis = $("#pilJenis").val();
	if(!vPilJenis){
		vPilJenis = '-';
	}

	KodeLoket = $("#KodeLoket").val();
	if(!KodeLoket){
		KodeLoket = "-";
	}

	JenisLoket = $("#jenisLoket").val();
	if(!JenisLoket){
		JenisLoket = "-";
	}

	TglAwal = $("#txtTglAwal").val();
	TglAkhir = $("#txtTglAkhir").val();
    window.open("{{ secure_url('admin/lap_transaksi/rekap') }}/"+TglAwal+"/"+TglAkhir+"/"+KodeLoket+"/"+vPilJenis+"/"+JenisLoket+"/pdf",'_blank');
}

function exporRekapHarian(){
	vPilJenis = $("#pilJenis").val();
	if(!vPilJenis){
		vPilJenis = '-';
	}

	KodeLoket = $("#KodeLoket").val();
	if(!KodeLoket){
		KodeLoket = "-";
	}

	JenisLoket = $("#jenisLoket").val();
	if(!JenisLoket){
		JenisLoket = "-";
	}

	TglAwal = $("#txtTglAwal").val();
	TglAkhir = $("#txtTglAkhir").val();
    window.open("{{ secure_url('admin/lap_transaksi/rekap') }}/"+TglAwal+"/"+TglAkhir+"/"+KodeLoket+"/"+vPilJenis+"/"+JenisLoket+"/excel",'_blank');
}

function exporDetailHarian(tanggal,kodeloket,jenis_transaksi,user){
    window.open("{{ secure_url('admin/lap_transaksi/detail') }}/"+tanggal+"/"+kodeloket+"/"+user+"/"+jenis_transaksi+"/excel",'_blank');
}

function cetakDetailHarian(tanggal,kodeloket,jenis_transaksi,user){
    window.open("{{ secure_url('admin/lap_transaksi/detail') }}/"+tanggal+"/"+kodeloket+"/"+user+"/"+jenis_transaksi+"/pdf",'_blank');
}

function prosesDetail(tanggal,kodeloket,jenis_transaksi,user) {

	dtTable = $('#detailTable').dataTable( {
        "ajax": "{{ secure_url('admin/lap_transaksi/detail') }}/"+tanggal+"/"+kodeloket+"/"+user+"/"+jenis_transaksi+"/grid",
        "destroy": true,
        "columns": [
            { "data": "idpel" },
			{ "data": "nama" },
            { "data": "jenis_transaksi" },
            { "data": "periode" },
            { "data": "tagihan" },
            { "data": "admin" },
            { "data": "total" },
            { "data": "tanggal" },
            { "data": "jam" },
            { "data": "loket_name" },
            { "data": "user_" }
        ],
        "scrollX": true,
        "info": false,
        "scrollY": "500px",
        "paging": false,
		"scrollCollapse": true,
        "searching": true,
        "ordering": false,

        "aoColumnDefs": [ 

        {
        "aTargets": [ 4,5,6 ],
            "mRender": function (data, type, full) {
                var formmatedvalue= numeral(data).format('0,0')
                return formmatedvalue;
            }
        },

        ]    

    });

    $('#excelDetail').attr('onclick',"exporDetailHarian('"+tanggal+"','"+kodeloket+"','"+jenis_transaksi+"','"+user+"')");
    $('#cetakDetail').attr('onclick',"cetakDetailHarian('"+tanggal+"','"+kodeloket+"','"+jenis_transaksi+"','"+user+"')");
}

$(document).ready(function() {

	var vmLap = new Vue({
		el: '#elLaporan',
		data() {
          return {
              TotalJumlah : "",
              TotalTagih : "",
              TotalAdmin : "",
              GrandTotal : ""
          };
      	},

		mounted() {
			$("#txtTglAwal").datepicker({ dateFormat: 'yy-mm-dd' });
			$("#txtTglAkhir").datepicker({ dateFormat: 'yy-mm-dd' });

			$('#detailTable').dataTable( {
				"scrollX": true,
		        "info": false,
		        "scrollY": "500px",
		        "paging": false,
				"scrollCollapse": true,
		        "searching": true,
			});

			$('#rekapTable').dataTable( {
				"scrollX": true,
		        "info": false,
		        "scrollY": "500px",
		        "paging": false,
				"scrollCollapse": true,
		        "searching": false,
			});

			$("select").select2();
      	},

		methods: {
			

			prosesLaporan: function () {
				vPilJenis = $("#pilJenis").val();
				if(!vPilJenis){
					vPilJenis = '-';
				}

				KodeLoket = $("#KodeLoket").val();
				if(!KodeLoket){
					KodeLoket = "-";
				}

				JenisLoket = $("#jenisLoket").val();
				if(!JenisLoket){
					JenisLoket = "-";
				}

				TglAwal = $("#txtTglAwal").val();
				TglAkhir = $("#txtTglAkhir").val();

				dtTable = $('#rekapTable').dataTable( {
			        "ajax": "{{ secure_url('admin/lap_transaksi/rekap') }}/"+TglAwal+"/"+TglAkhir+"/"+KodeLoket+"/"+vPilJenis+"/"+JenisLoket+"/grid",
			        "destroy": true,
			        "columns": [
			        	{ "data": "" },
			            { "data": "user_" },
						{ "data": "loket_name" },
						{ "data": "tanggal" },
			            { "data": "jumlah" },
			            { "data": "tagihan" },
			            { "data": "admin" },
			            { "data": "total" },
			            { "data": "jenis_transaksi" },
			            { "data": "jenis_loket" }
			        ],
			        "info": false,
			        "paging": false,
			        "searching": false,
			        "scrollX": true,
			        "scrollY": "280px",
			        "scrollCollapse": true,

		            "aoColumnDefs": [ 

		            {
		            "aTargets": [ 4,5,6,7 ],
		                "mRender": function (data, type, full) {
		                    var formmatedvalue= numeral(data).format('0,0')
		                    return formmatedvalue;
		                }
		            },

		            {
		            "aTargets": [ 0 ],
		                "mRender": function (data, type, full) {
		                    var formmatedvalue = "<button type='button' onclick=\"prosesDetail('"+full.tanggal+"','"+full.loket_code+"','"+full.jenis_transaksi+"','"+full.user_+"')\" class='btn btn-primary btn-xs'>Detail</button> ";
		                    return formmatedvalue;
		                }
		            },

		            ],

		            "footerCallback": function ( row, data, start, end, display ) {
						var api = this.api(), data;
						var juml = 0;
						var pdam = 0;
						var admin = 0;
						var total = 0;
						
						api.column(4)
			                .data()
			                .reduce( function (a, b) {
								juml += b;
			                },0);
						api.column(5)
			                .data()
			                .reduce( function (a, b) {
								//console.log(b.replace(/,/g,""));
								pdam += parseInt(b.replace(/,/g,""));
			                },0);
						api.column(6)
			                .data()
			                .reduce( function (a, b) {
								//console.log(b.replace(/,/g,""));
								admin += parseInt(b.replace(/,/g,""));
			                },0);
						api.column(7)
			                .data()
			                .reduce( function (a, b) {
								//console.log(b.replace(/,/g,""));
								total += parseInt(b.replace(/,/g,""));
			                },0);

						vmLap.TotalJumlah = numeral(juml).format('0,0');
			            vmLap.TotalTagih = numeral(pdam).format('0,0');
			            vmLap.TotalAdmin = numeral(admin).format('0,0');
			            vmLap.GrandTotal = numeral(total).format('0,0');
					}      

			    });
			},
		}

    });
		
});
</script>
@endsection