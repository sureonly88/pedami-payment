@extends('...layouts/template')

@section('content')

<script>
	$(document).ready(function() {
		LoadUsers();
	});

	function LoadUsers(){
        dtTable = $('#beritaTable').dataTable( {
            "ajax": "{{ secure_url('/admin/berita/list') }}",
            "destroy": true,
            "columns": [
                { "data": "judul" },
                { "data": "created_at" },  
                { "data": "isi" }
            ],
            "paging":   false,
            "searching":   false,
            "info":     false   
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

<div class="row">
	<div class="col-md-4">
		<div class="box box-default">
			<div class="box-header with-border">
			  <i class="fa fa-info"></i>
			  <h3 class="box-title">INFORMASI USER LOGIN</h3>
			</div>
			<div class="box-body">
				<table class="table table-striped table-hover" id="tab-info" style="padding-top:10px">
					<tbody>
					<tr>
						<td style="width: 100px">USERNAME</td>
						<td>: {{$user['username']}}</td>
					</tr>
					<tr>
						<td>USER ROLE</td>
						<td>: {{ strtoupper($user['role']) }}</td>
					</tr>
					<tr>
						<td>NAMA LOKET</td>
						<td>: {{ strtoupper($user['loket_name']) }}</td>
					</tr>
					<tr>
						<td>KODE LOKET</td>
						<td>: {{ strtoupper($user['loket_code']) }}</td>
					</tr>
					<tr>
						<td>EMAIL</td>
						<td>: {{$user['email']}}</td>
					</tr>
					<tr>
						<td>BIAYA ADMIN</td>
						<td>: Rp. {{ number_format($user['byadmin'],0) }}</td>
					</tr>

					<tr>
						<td>LAST LOGIN</td>
						<td>: {{ $user['lastlogin'] }}</td>
					</tr>


					</tbody>
				</table>
				<br/>
				<div class="alert alert-success" style="font-size: 30px"><strong>SALDO : </strong>Rp. {{ number_format($user['pulsa'],0) }}</div></td>
			</div>
		</div>
	</div>

	<div class="col-md-8">
		<div class="row">
			<div class="col-md-6">
				<div class="small-box bg-green">
		            <div class="inner">
		              <h3>Rp. {{ number_format($total,0) }}</h3>

		              <p>Total Penerimaan Tahun <?php echo date('Y') ?></p>
		            </div>
		            <div class="icon">
		              <i class="ion ion-stats-bars"></i>
		            </div>
		            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
		          </div>
			</div>

			<div class="col-md-6">
				<div class="small-box bg-red">
		            <div class="inner">
		              <h3>Rp. {{ number_format($total_admin,0) }}</h3>

		              <p>Total Biaya Admin Tahun <?php echo date('Y') ?></p>
		            </div>
		            <div class="icon">
		              <i class="ion ion-pie-graph"></i>
		            </div>
		            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
		          </div>
			</div>

		</div>

		<div class="row">
		<div class="col-md-12">
			<div class="box box-default">
				<div class="box-header with-border">
				  <i class="fa fa-info"></i>
				  <h3 class="box-title">BERITA TERKINI</h3>
				</div>
				<div class="box-body">
					<div style="width:100%;min-height: 420px;overflow:auto;">
			        <table class="table table-bordered table-hover table-striped dataTable" id="beritaTable">
			            <thead>
			                <tr>
			                    <th style="min-width: 100px">JUDUL</th>
			                    <th style="min-width: 100px">TANGGAL</th>
			                    <th style="min-width: 350px">BERITA</th>
			                </tr>
			            </thead>                
			            <tbody>
			                <tr>
			                    <td></td>
			                    <td></td>
			                    <td></td>
			                </tr>
			            </tbody>
			        </table>
			        </div>
				</div>
			</div>

		</div>

	</div>
		
	</div>

</div>

<script src="{{ secure_asset('plugins/chartjs/Chart.min.js') }}"></script>
<script>

</script>

</section>
@endsection