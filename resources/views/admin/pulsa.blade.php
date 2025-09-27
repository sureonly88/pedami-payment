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
    <li class="active">Listik PLN & Pulsa</li>
  </ol>
</section>

<!-- Main content -->
<section class="content" id="elPulsa">

<div class="row">
	<div class="col-md-5">
		<div class="box box-primary">

		    <div class="box-body" style="min-height: 300px" >
		    	<form action="" >
				    <div class="form-group">
				        <label>Nomor Telepon</label>
				        <input id="nomor_telp" type="text" value="" placeholder="081234567890" class="form-control" />
				    </div>

				    <div class="form-group">
				       <label>Nominal</label>
				       <select style="width: 100%" class="form-control" id="nominal_telp">
							<option value="25000" >Rp. 25.000</option>
							<option value="50000" >Rp. 50.000</option>
							<option value="100000" >Rp. 100.000</option>
						</select>
				    </div>

				    <div class="form-group">
					    <button type="button" class="btn btn-primary" @click="prosesPulsa()" >
							Proses</button>

					</div>

				</form>
			</div>

		</div>
	</div>

	<div class="col-md-7">
		<div class="box box-primary" style="min-height: 300px">
		    <div class="box-header">
		      <h3 class="box-title">HASIL TRANSAKSI</h3>
		    </div>
		    <div class="box-body" >
		    	<div class="alert alert-success alert-dismissible" v-if="isSuccess">
	                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	                <h4><i class="icon fa fa-check"></i> Alert!</h4>
	                Success alert preview. This alert is dismissable.
              	</div>

              	<div class="alert alert-danger alert-dismissible" v-if="isFailed">
	                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
	                Danger alert preview. This alert is dismissable. A wonderful serenity has taken possession of my entire
	                soul, like these sweet mornings of spring which I enjoy with my whole heart.
              </div>
		    </div>
		</div>
	</div>
</div>

</section>

<script>
$(document).ready(function() {
	var vmPln = new Vue({
		el: '#elPulsa',
		data(){
			return {
				isNominal: true,
				isResponse: true,
				isSuccess: true,
				isFailed: true
			}
		},

		methods: {
			prosesPulsa: function () {
				alert("Proses Pulsa");
			}
		}

    });
    
});
</script>
@endsection