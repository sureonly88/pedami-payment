@extends('...layouts/template')

@section('content')

<script>
$( document ).ready(function() {
	var KodeLoket = "{{$user['loket_code']}}";
	var Role = "{{$user['role']}}";
	
	dtLog = $('#historyLog').dataTable( {
        "ajax": "{{ url('admin/loghistory/') }}/"+KodeLoket,
        "destroy": true,
        "columns": [
            { "data": "CREATED_AT" },
            { "data": "TOPUP_MONEY" },
            { "data": "NOTE" },
		],
		"info": false,
        "scrollY": "400px",
        "paging": false,
		"scrollCollapse": true,
        "searching": false,
		"ordering": false,

		"aoColumnDefs": [ 

        {
        "aTargets": [ 1 ],
            "mRender": function (data, type, full) {
                var formmatedvalue= numeral(data).format('0,0')
                return formmatedvalue;
            }
        },

        ]    
    });
});
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
    <li class="active">Profil</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
<div class="row">
	<div class="col-md-3">
		<div class="box box-primary" style="min-height: 550px">
                <div class="box-body box-profile">
                  <img class="profile-user-img img-responsive img-circle" src="{{ URL::asset('dist/img/avatar04.png') }}" alt="User profile picture">
                  <h3 class="profile-username text-center">{{strtoupper($user['username'])}} ({{strtoupper($user['role'])}})</h3>
                  <p class="text-muted text-center">Last Login : {{$user['lastlogin']}}</p>

				  <hr>
                  <strong><i class="fa fa-user margin-r-5"></i>NAMA LOKET : </strong>
                  <p class="text-muted">
                    {{strtoupper($user['loket_code'])}} - {{strtoupper($user['loket_name'])}}
                  </p>

                  <hr>

                  <strong><i class="fa fa-envelope margin-r-5"></i>EMAIL :</strong>
                  <p class="text-muted">{{$user['email']}}</p>

                  <hr>

                  <strong><i class="fa fa-calculator margin-r-5"></i>BIAYA ADMIN :</strong>
                  <p>Rp. {{number_format($user['byadmin'])}}</p>

                  <hr>

                  <strong><i class="fa fa-calculator margin-r-5"></i>TOTAL SALDO :</strong>
                  <p>Rp. {{number_format($user['pulsa'],0)}}</p>
				  

                </div><!-- /.box-body -->
              </div>
	</div>
	
	<div class="col-md-9">
		<div class="box box-primary" style="min-height: 550px">
			<div class="box-header with-border">
			  <i class="fa fa-info"></i>
			  <h3 class="box-title">HISTORY SALDO</h3>
			</div>
			<div class="box-body">
				<table id="historyLog" class="table table-bordered table-hover table-striped dataTable">
					<thead>
					<tr>
						<th style="min-width: 100px">TANGGAL</th>
						<th style="min-width: 100px">RP. TOPUP</th>
						<th style="min-width: 200px">KETERANGAN</th>
					</tr>
					</thead>
					<tbody id="dataHistory">
					<tr>
						<td>-</td>
						<td>-</td>
						<td>-</td>
					</tr>
					</tbody>

				</table>
			</div>
		</div>
	
	</div>
</div>

</section>
@endsection