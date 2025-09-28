@extends('...layouts/template')

@section('content')
<script>
$( "#menuLapBulan" ).prop( "class", "active" );

$(document).ready(function() {
	$("select").select2();
});

function ctkLaporanBulanan(){
    userLoket = $("#pilUser").val();
    tglTransaksi = $("#txtTanggal").val();

    window.open("{{ secure_url('admin/ctklaporan/bulanan/') }}/"+Tahun+"/"+Bulan+"/"+Loket,'_blank');
}

function getLaporanBulan(){
    Tahun = $("#Tahun").val();
    Bulan = $("#Bulan").val();
    Loket = $("#pilUser").val();

    if(!Loket){
    	Loket = ['-'];
    }

    dtTable = $('#dataTable').dataTable( {
        "ajax": "{{ secure_url('/admin/bulanan/rekap') }}/"+Tahun+"/"+Bulan+"/"+Loket,
        "destroy": true,
        "columns": [
            { "data": "TRANSACTION_YEAR" },
            { "data": "TRANSACTION_MONTH" },
            { "data": "LOKET_CODE" },
            { "data": "REKENING" },
            { "data": "SUB_TOTAL" },
            { "data": "ADMIN" },
            { "data": "TOTAL" }
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
					//console.log(b.replace(/,/g,""));
					pdam += parseInt(b.replace(/,/g,""));
                },0);
			api.column(5)
                .data()
                .reduce( function (a, b) {
					//console.log(b.replace(/,/g,""));
					admin += parseInt(b.replace(/,/g,""));
                },0);
			api.column(6)
                .data()
                .reduce( function (a, b) {
					//console.log(b.replace(/,/g,""));
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
		<div class="box">
			<div class="box-header with-border">
			  <h3 class="box-title">Laporan Harian</h3>
			</div>
			<form role="form">
				<div class="box-body">
					<div class="form-group">
						<label>BULAN TAHUN</label>
						<div class="row">
							<div class="col-lg-6">
												
								<select class="form-control select2" id="Bulan">
									<option value="1">Januari</option>
									<option value="2">Februari</option>
									<option value="3">Maret</option>
									<option value="4">April</option>
									<option value="5">Mei</option>
									<option value="6">Juni</option>
									<option value="7">Juli</option>
									<option value="8">Agustus</option>
									<option value="9">September</option>
									<option value="10">Oktober</option>
									<option value="11">Nopember</option>
									<option value="12">Desember</option>
								</select>
							</div>
							
							<div class="col-lg-3">
								<input id="Tahun" placeholder="Tahun" value="<?php echo date('Y'); ?>" class="form-control" type="text">
							</div>
						</div>
						
					</div>
						
					<div class="form-group">
						<label for="inputName" class="control-label">
							LOKET</label>

						@if($user["role"] == "admin" or $user["role"] == "laporan")
						<select class="form-control select2" id="pilUser" multiple="multiple">
							<!-- <option value="-">SEMUA</option> -->
							@foreach($lokets as $loket)
								<option value="{{$loket->loket_code}}">{{ strtoupper($loket->nama)}}</option>
							@endforeach
						</select>
						@else
						<div class="input-icon right">
							<input id="pilUser" type="text" value="{!!$user['loket_code']!!}" readonly="readonly" placeholder="" class="form-control" />
						</div>
						@endif
					</div>
					
					<div class="box-footer">
						<button type="button" class="btn btn-primary" onclick="getLaporanBulan()">
							Proses</button>
						<button type="button" class="btn btn-primary" onclick="ctkLaporanBulanan()">
							Cetak</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<div class="col-md-8">
		<div class="box">
			<div class="box-header with-border">
			  <h3 class="box-title">Laporan Harian</h3>
			</div>
			<form role="form">
				<div class="box-body">
					<div class="form-group">
						<div style="width:100%;overflow:auto;">
						<table id="dataTable" class="table table-striped table-hover">
						<thead>
						<tr>
							<th>TAHUN</th>
							<th>BULAN</th>
							<th>LOKET</th>
							<th>JUMLAH</th>
							<th>TOTAL PDAM</th>
							<th>ADMIN</th>
							<th>TOTAL KOPERASI</th>
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
						</tr>
						<tfoot>
						<tr>
							<th colspan="3">TOTAL</th>
							<th id="txtJml">0</th>
							<th id="txtPdam">0</th>
							<th id="txtAdmin">0</th>
							<th id="txtTotal">0</th>
						</tr>
						</tfoot>

						</tbody>
					</table>
					</div>
					</div>
				</div>
			</form>
		</div>
	</div>


	
</div>

<div class="row">

	<div  class="col-md-12">
		<div class="box">
	        <div class="box-header with-border">
	          <h3 class="box-title">Perkembangan Pembayaran Tahun <?php echo(date('Y')) ; ?></h3>
	          <div class="box-tools pull-right">
	            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
	            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
	          </div>
	        </div>
	        <div class="box-body">
	          <div class="chart">
	            <canvas id="barChart" style="height:250px"></canvas>
	          </div>
	        </div><!-- /.box-body -->
	      </div><!-- /.box -->
	</div>

	<script src="{{ secure_asset('plugins/chartjs/Chart.min.js') }}"></script>
	<script>
      	$(function () {

      		$.ajaxSetup({ cache: false });
		    $.getJSON("{{ secure_url('/admin/grv/rekap') }}", function(msg){
		    	var areaChartData = {
		        labels: msg.label,
		        datasets: [
			            {
			              label: "Electronics",
			              fillColor: "rgba(210, 214, 222, 1)",
			              strokeColor: "rgba(210, 214, 222, 1)",
			              pointColor: "rgba(210, 214, 222, 1)",
			              pointStrokeColor: "#c1c7d1",
			              pointHighlightFill: "#fff",
			              pointHighlightStroke: "rgba(220,220,220,1)",
			              data: msg.data
			            }
		        	]
		        };

		        var barChartCanvas = $("#barChart").get(0).getContext("2d");
		        var barChart = new Chart(barChartCanvas);
		        var barChartData = areaChartData;
		        barChartData.datasets[0].fillColor = "#00a65a";
		        barChartData.datasets[0].strokeColor = "#00a65a";
		        barChartData.datasets[0].pointColor = "#00a65a";
		        var barChartOptions = {
		          //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
		          scaleBeginAtZero: true,
		          //Boolean - Whether grid lines are shown across the chart
		          scaleShowGridLines: true,
		          //String - Colour of the grid lines
		          scaleGridLineColor: "rgba(0,0,0,.05)",
		          //Number - Width of the grid lines
		          scaleGridLineWidth: 1,
		          //Boolean - Whether to show horizontal lines (except X axis)
		          scaleShowHorizontalLines: true,
		          //Boolean - Whether to show vertical lines (except Y axis)
		          scaleShowVerticalLines: true,
		          //Boolean - If there is a stroke on each bar
		          barShowStroke: true,
		          //Number - Pixel width of the bar stroke
		          barStrokeWidth: 2,
		          //Number - Spacing between each of the X value sets
		          barValueSpacing: 5,
		          //Number - Spacing between data sets within X values
		          barDatasetSpacing: 1,
		          //String - A legend template
		          legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].fillColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
		          //Boolean - whether to make the chart responsive
		          responsive: true,
		          maintainAspectRatio: true
		        };

		        barChartOptions.datasetFill = false;
		        barChart.Bar(barChartData, barChartOptions);
		    }).error(function(jqXHR, textStatus, errorThrown){

		    });
    	});
    </script>
</div>


</div>
	
</section>
 
@endsection