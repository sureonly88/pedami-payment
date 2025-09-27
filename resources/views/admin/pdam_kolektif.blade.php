@extends('...layouts/template')

@section('content')
<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Dashboard
    <small>Halaman Pedami Payment</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Admin</a></li>
    <li class="active">Pdam Kolektif</li>
  </ol>
</section>

<!-- Main content -->
<section class="content" id="vmkolektif">
<div class="box box-primary">
    <div class="box-header">
      <h3 class="box-title">DAFTAR KOLEKTIF PDAM BANDARMASIH</h3>
    </div>
    <div class="box-body">
    	<div class="alert" v-show="showMessage" v-bind:class="cssMessage">
		    <h4><i class="icon fa fa-check"></i> Message!</h4>
		    <div v-html="message"></div>
		</div>

    	<form role="form">

            <input type="text" class="form-control" id="id_kolektif" v-model="idKolektif" readonly="readonly" style="display: none;">

            <div class="form-group">
              <label for="nama_kolektif">Nama Kolektif</label>
              <input type="text" class="form-control" id="nama_kolektif" v-model="namaKolektif" placeholder="Masukan Nama Kolektif">
            </div>

            <input type="button" name="simpanKolektif" id="simpanKolektif" value="Simpan Kolektif" @click="aksiKolektif()" class="btn btn-primary btn-sm" />
            <input type="button" name="resetKolektif" id="resetKolektif" value="Reset" @click="ResetKolektif()" class="btn btn-primary btn-sm" />
        </form>

    	<div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped dataTable" id="dtKolektif">
            <thead>
                <tr>
                	<th>NAMA KOLEKTIF</th>
                    <th>AKSI</th>
                    
                </tr>
            </thead>                
            <tbody>
                <tr v-for="Kolektif in dataKolektif">
                    
                    <td>@{{ Kolektif.nama }}</td>
                    <td>
                    	<button type='button' @click='editKolektif(Kolektif.id)' class='btn btn-primary btn-xs'>Edit</button>&nbsp;
                    	<button type='button' @click='delKolektif(Kolektif.id)' class='btn btn-primary btn-xs'>Hapus</button>&nbsp;
                    	<button type='button' @click='showDetail(Kolektif.id)' class='btn btn-primary btn-xs'>Detail</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalDetail">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">DAFTAR PELANGGAN KOLEKTIF</h4>
      </div>
      <div class="modal-body">
      	<div class="alert alert-success" v-show="showMessageDetail">
		    <h4><i class="icon fa fa-check"></i> Message!</h4>
		    <div v-html="messageDetail"></div>
		</div>

      	<table class="table table-bordered table-hover table-striped dataTable" id="dtDetail">
            <thead>
                <tr>
                    <th style="min-width: 100px">IDPEL</th>
                    <th style="min-width: 100px">NAMA PELANGGAN</th>
					<th style="min-width: 100px">JENIS</th>
                    <th style="min-width: 50px">AKSI</th>
                </tr>
            </thead>                
            <tbody>
                <tr v-for="Detail in dataDetail">
                    <td>@{{ Detail.id_pelanggan }}</td>
                    <td>@{{ Detail.nama_pelanggan }}</td>
					<td>@{{ Detail.jenis }}</td>
                    <td><button type='button' @click='delDetail(Detail.id)' class='btn btn-primary btn-xs'>Hapus</button></td>
                </tr>
            </tbody>
        </table>
        <form role="form">
        	<input type="text" class="form-control" id="id_det_kolektif" v-model="idDetailKolektif" readonly="readonly" style="display: none">

        	<div class="form-group">
              <label for="idpel">No Pelanggan</label>
              <input type="text" class="form-control" id="idpel" v-model="idpel" placeholder="Masukan Id Pelanggan">
            </div>

            <div class="form-group">
              <label for="nama_pelanggan">Nama Pelanggan</label>
              <input type="text" class="form-control" id="nama_pelanggan" v-model="namaPelanggan" placeholder="Masukan Nama Pelanggan">
            </div>

			<div class="form-group">
              <label for="jenis">Jenis</label>
              <select class="form-control" id="jenis" style="width: 100%" v-model="jenis" >
				<option value="PDAMBJM">PDAM Bandarmasih</option>
				<option value="PLN_POSTPAID">PLN Postpaid</option>
			</select>
            </div>

            <button type="button" class="btn btn-primary" @click="aksiDetailKolektif()" id="btnTambahDetail">Tambah</button>
        </form>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>

    </div>
   </div>
</div>

</section>

<script type="text/javascript">
$(document).ready(function() {

    var vmKolektif = new Vue({
	    el: '#vmkolektif',
	    data: {
	        message: '',
	        dataKolektif: [],
	        dataDetail: [],
	        namaKolektif: '',
	        idKolektif: '',
			jenis: '',
	        isSuccess: false,
	        namaPelanggan: '',
	        idpel: '',
	        idDetailKolektif: '',
	        messageDetail: '',
	    },

	    mounted() {
	    	this.LoadDataKolektif();
	    },

	    computed: {
            showMessage: function () {
                if(this.message.length > 0){
                    return true;
                }else{
                    return false;
                }
            },

            showMessageDetail: function () {
                if(this.messageDetail.length > 0){
                    return true;
                }else{
                    return false;
                }
            },

            cssMessage: function () {
                if(this.isSuccess){
                    return 'alert-success';
                }else{
                    return 'alert-danger';
                }
            },


        },

	    methods: {
	    	ResetKolektif: function(){
	    		this.idKolektif = '';
	    		this.namaKolektif = '';
	    	},

	    	LoadDataKolektif: function(){
	    		axios.get("{{ url('/admin/pdam_kolektif/daftar') }}")
				    .then(function (response) {
				    	if(response.data.status){
				    		vmKolektif.dataKolektif = response.data.data;
				    	}else{
				    		vmKolektif.dataKolektif = [];
				    	}
				    	
				    })
				    .catch(function (error) {
				      //console.log(error);
				  });
	    	},

	    	LoadDetailKolektif: function(vId){
	    		axios.get("{{ url('/admin/pdam_kolektif/kolektif') }}/"+vId)
				    .then(function (response) {
				    	if(response.data.status){
				    		vmKolektif.dataDetail = response.data.data;
				    	}else{
				    		vmKolektif.dataDetail = [];
				    	}
				    	
				    })
				    .catch(function (error) {
				      //console.log(error);
				  });
	    	},

	    	showDetail: function(vId){
	    		vmKolektif.idDetailKolektif = vId;
	    		vmKolektif.LoadDetailKolektif(vmKolektif.idDetailKolektif);
	    		vmKolektif.messageDetail = "";
	    		$('#modalDetail').modal("show");
	    	},

	    	delDetail: function(vId){
	    		vmKolektif.messageDetail = "";

	    		axios.post("{{ url('/admin/pdam_kolektif/aksi_detail') }}", {
				    id_pelanggan: '-',
				    nama_pelanggan: '-',
				    id_kolektif: '-',
				    id: vId,
				    aksi: 'delete'

				}).then(function (response) {
					//console.log(response.data);

					vmKolektif.messageDetail = response.data.message;
				    vmKolektif.idpel = "";
				    vmKolektif.namaPelanggan = "";

				    vmKolektif.LoadDetailKolektif(vmKolektif.idDetailKolektif);

				}).catch(function (error) {
				    console.log(error);
				});
	    	},

	    	editKolektif: function(vId){
	    		vmKolektif.message = "";

	    		axios.get("{{ url('/admin/pdam_kolektif/get') }}/"+vId)
				    .then(function (response) {
				    	if(response.data.status){
				    		vmKolektif.namaKolektif = response.data.data.nama;
				    		vmKolektif.idKolektif = response.data.data.id;
				    	}else{
				    		vmKolektif.namaKolektif = "";
				    		vmKolektif.idKolektif = "";
				    	}
				    	
				    })
				    .catch(function (error) {
				      //console.log(error);
				  });
	    	},

	    	delKolektif: function(vId){
	    		vmKolektif.message = "";

	    		axios.post("{{ url('/admin/pdam_kolektif/aksi') }}", {
                    nama_kolektif: '-',
                    id: vId,
                    aksi: 'delete'

                }).then(function (response) {
                    //console.log(response.data);

                    vmKolektif.isSuccess = response.data.status;
                    vmKolektif.message = response.data.message;
                    vmKolektif.idKolektif = "";
                    vmKolektif.namaKolektif = "";

                    vmKolektif.LoadDataKolektif();

                }).catch(function (error) {
                    console.log(error);
                });

	    	},

	    	aksiKolektif: function(){
	    		var  aksi = "";
	    		if(vmKolektif.idKolektif != ""){
	    			aksi = "edit";
	    		}else{
	    			aksi = "simpan";
	    		}

	    		vmKolektif.message = "";

	    		axios.post("{{ url('/admin/pdam_kolektif/aksi') }}", {
				    nama_kolektif: vmKolektif.namaKolektif,
				    id: vmKolektif.idKolektif,
				    aksi: aksi

				}).then(function (response) {
					//console.log(response.data);

					vmKolektif.isSuccess = response.data.status;
				    vmKolektif.message = response.data.message;
				    vmKolektif.idKolektif = "";
				    vmKolektif.namaKolektif = "";

				    vmKolektif.LoadDataKolektif();

				}).catch(function (error) {
				    console.log(error);
				});
	    	},

	    	aksiDetailKolektif: function(){

	    		vmKolektif.messageDetail = "";

	    		axios.post("{{ url('/admin/pdam_kolektif/aksi_detail') }}", {
				    id_pelanggan: vmKolektif.idpel,
				    nama_pelanggan: vmKolektif.namaPelanggan,
				    id_kolektif: vmKolektif.idDetailKolektif,
					jenis: vmKolektif.jenis,
				    aksi: 'simpan'

				}).then(function (response) {
					//console.log(response.data);

				    vmKolektif.messageDetail = response.data.message;
				    vmKolektif.idpel = "";
				    vmKolektif.namaPelanggan = "";

				    vmKolektif.LoadDetailKolektif(vmKolektif.idDetailKolektif);

				}).catch(function (error) {
				    console.log(error);
				});
	    	}

	    }

	});
});	
</script>

@endsection