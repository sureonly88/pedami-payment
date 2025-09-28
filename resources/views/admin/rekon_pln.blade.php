@extends('...layouts/template')

@section('content')
<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<script type="text/javascript">

</script>

<section class="content-header">
  <h1>
    Dashboard
    <small>Halaman Pedami Payment</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Admin</a></li>
    <li class="active">Rekon PLN</li>
  </ol>
</section>

<!-- Main content -->
<section class="content" id="elRekon">
<div class="box box-primary" style="min-height: 550px">
    <div class="box-header">
      <h3 class="box-title">REKONSILIASI TRANSAKSI PLN</h3>
    </div>
    <div class="box-body">
    	<div class='form-group'>
    		<div class="row">
    			<div class="col-md-2">
    				<input id="txtTanggal" type="text" style="width: 100%" style="text-align: right;" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
    			</div>
    			<div class="col-md-2">
    				<select class="form-control select2" id="pilJenis">
						<option value="POSTPAID">POSTPAID</option>
						<option value="PREPAID">PREPAID</option>
					</select>
    			</div>

                <div class="col-md-2">
                    <select class="form-control select2" id="pilSelisih">
                        <option value="0">SEMUA</option>
                        <option value="1">SELISIH</option>
                    </select>
                </div>

    			<div class="col-md-3">


    				<input type="button" name="proses" id="proses" @click="ambilRekon()" value="Proses Rekon Starlink" class="btn btn-primary" />

    				<input type="button" name="proses" id="proses" @click="loadDataRekon()" value="Cek Rekon" class="btn btn-primary" />
    			</div>
    		</div>
    		
    	</div>

    	<table class="table table-bordered table-hover table-striped dataTable" id="rekonData">
            <thead>
                <tr>
                    <th style="min-width: 50px" valign="top" rowspan="2">SELISIH</th>
                    <th colspan="6">TRANSAKSI DI PEDAMI</th>
                    <th colspan="6">TRANSAKSI DI STARLINK</th>
                </tr>
                <tr>
                    <th style='min-width: 80px'>IDPEL</th>
                    <th style='min-width: 100px'>NAMA</th>
                    <th style='min-width: 80px'>PERIODE</th>
                    <th style='min-width: 80px'>TAGIHAN</th>
                    <th style='min-width: 80px'>ADMIN</th>
                    <th style='min-width: 80px'>TOTAL</th>

                    <th style='min-width: 80px'>IDPEL</th>
                    <th style='min-width: 100px'>NAMA</th>
                    <th style='min-width: 80px'>PERIODE</th>
                    <th style='min-width: 80px'>TAGIHAN</th>
                    <th style='min-width: 80px'>ADMIN</th>
                    <th style='min-width: 80px'>TOTAL</th>

                </tr>
            </thead>                
            <tbody>
                <tr>
                	<td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            </tbody>
        </table>

            <div class="form-group">
            {!! Form::open(array('route' => 'rekon.upload.post','files'=>true)) !!}
            <div class="row">

                <div class="col-md-2">
                    <select class="form-control" id="uploadJenis" name="uploadJenis">
                        <option value="POSTPAID">POSTPAID</option>
                        <option value="PREPAID">PREPAID</option>
                    </select>
                </div>

                <div class="col-md-3">

                    {!! Form::file('rekonfile', array('class' => 'form-control')) !!}

                </div>

                <div class="col-md-5">

                    <button type="submit" class="btn btn-success">Upload File Rekon</button>

                </div>

            </div>
            {!! Form::close() !!}

            <br/>

            @if ($message = Session::get('success'))

            <div class="alert <?php echo Session::get('alert'); ?> alert-block">

                <button type="button" class="close" data-dismiss="alert">×</button>

                    <strong>{{ $message }}</strong>

            </div>

            @endif
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalRekon">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">PROSES FILE REKON</h4>
      </div>
      <div class="modal-body">
            <form role="form" id="formData">
                <div class="row">
                    <div class="col-md-2">
                        <select class="form-control" v-model="pilJenisFtp" id="pilJenisFtp">
                            <option value="POSTPAID">POSTPAID</option>
                            <option value="PREPAID">PREPAID</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-control" v-model="BulanFtp" id="BulanFtp">
                                    <option value="01">Januari</option>
                                    <option value="02">Februari</option>
                                    <option value="03">Maret</option>
                                    <option value="04">April</option>
                                    <option value="05">Mei</option>
                                    <option value="06">Juni</option>
                                    <option value="07">Juli</option>
                                    <option value="08">Agustus</option>
                                    <option value="09">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">Nopember</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <input id="TahunFtp" v-model="TahunFtp" placeholder="Tahun" value="<?php echo date('Y'); ?>" class="form-control" type="text">
                            </div>

                            <div class="col-md-2">
                                 <button type="button" :disabled="isLoadingFtp" @click="cekFileRekon()" class="btn btn-primary">Proses</button>
                            </div>
                        </div>
                    </div>

                </div>
                <table class="table table-bordered table-hover table-striped dataTable" id="rekonProses">
                    <thead>
                        <tr>
                            <th style='min-width: 80px'>JENIS</th>
                            <th style='min-width: 100px'>FILE REKON FTP</th>
                            <th style='min-width: 100px'>FILE TERPROSES</th>
                            <th style='min-width: 100px'>TGL FILE REKON</th>
                            <th style='min-width: 100px'>AKSI</th>
                        </tr>
                    </thead>                
                    <tbody>
                        <tr v-for="data in dataRekon">
                            <td>@{{ data.jenis }}</td>
                            <td>@{{ data.file_ftp }}</td>
                            <td>@{{ data.file_rekon }}</td>
                            <td>@{{ data.tgl_rekon_file }}</td>
                            <td><button type="button" @click="transferRekon(data.file_ftp_path,data.jenis)" :disabled="isLoadingFtp" class="btn btn-primary">Proses</button></td>
                        </tr>
                    </tbody>
                </table>

                <div v-html="pesanProses" ></div>
            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

</section>

<script>
$(document).ready(function() {

    var vmRekon = new Vue({
        el: '#elRekon',
        data: {
        	isLoadingFtp: false,
            dataRekon: [],
            pilJenisFtp: '',
            BulanFtp: '',
            TahunFtp: '',
            pesanProses: ''
        },

        mounted() {
            this.initData();
        },

        computed: {
        	Kembalian: function () {
		    	
		    },
        },

        methods: {

			initData: function (){
				$('#rekonData').dataTable( {
		            "scrollX": true,  
		            "ordering": false,      
		        });

                this.pilJenisFtp = "POSTPAID";
                this.BulanFtp = "01";
                this.TahunFtp = "2017";

		        $("#txtTanggal").datepicker({ dateFormat: 'yy-mm-dd' });
			},

			loadDataRekon: function (){

				vTanggal = $("#txtTanggal").val();
				vJenis = $("#pilJenis").val();
				vFlag = $("#pilSelisih").val();

				dtTable = $('#rekonData').dataTable( {
		            "ajax": "{{ secure_url('/admin/rekon_pln/proses') }}/"+vTanggal+"/"+vFlag+"/"+vJenis,
		            "destroy": true,
		            "columns": [
		                { "data": '' },
		                { 'data': 'a_idpel' },
		                { 'data': 'a_nama' },
		                { 'data': 'a_periode' },
		                { 'data': 'a_tagihan' },
		                { 'data': 'a_admin' },
		                { 'data': 'a_total' },
		                { 'data': 'b_idpel' },
		                { 'data': 'b_nama' },
		                { 'data': 'b_periode' },
		                { 'data': 'b_tagihan' },
		                { 'data': 'b_admin' },
		                { 'data': 'b_total' },
		                
		            ],
		            "aoColumnDefs": [ {
		            "aTargets": [ 0 ],
		            "mRender": function (data, type, full) {
                            btnCancel = "";
                            btnForce = "";
                            btnPass = "";
                            if(!full.b_id){
                                //btnCancel = "<button type='button' onclick='cancelPayment("+full.a_id+")' class='btn btn-primary btn-xs'>Cancel</button>";
                                btnCancel = "<button type='button' class='btn btn-block btn-danger btn-xs'>STARLINK</button>";
                            }

                            if(!full.a_id){
                                //btnForce = "<button type='button' onclick='forcePayment("+full.b_id+")' class='btn btn-primary btn-xs'>Forced</button> ";
                                btnForce = "<button type='button' class='btn btn-block btn-danger btn-xs'>PEDAMI</button>";
                            }

                            if(full.a_id && full.b_id){
                                btnPass = "<button type='button' class='btn btn-block btn-success btn-xs'>KLOP</button>";
                            }

		                    return btnCancel + btnForce + btnPass;
		                }
		            },

		            {
		            "aTargets": [ 4,5,6,10,11,12 ],
		                "mRender": function (data, type, full) {
		                    var formmatedvalue= numeral(data).format('0,0')
		                    return formmatedvalue;
		                }
		            },

		            ],
		            "scrollX": true,
		            "ordering": true,

		        });
			},

			ambilRekon: function(){
                $('#modalRekon').modal("show");
			},

            cekFileRekon: function(){
                vmRekon.isLoadingFtp = true;
                vmRekon.pesanProses = "<b class='text-yellow'>PROSES PENGECEKAN FILE REKON...</b>";

                this.$http.get("{{ secure_url('admin/rekon_pln/cek/') }}/"+vmRekon.pilJenisFtp+"/"+vmRekon.BulanFtp+"/"+vmRekon.TahunFtp).then(response => {
                    //console.log(response.body);
                    vmRekon.isLoadingFtp = false;
                    vmRekon.dataRekon = response.body.data;

                    vmRekon.pesanProses = "<b class='text-green'>PROSES PENGECEKAN FILE REKON SELESAI...</b>";
                }, response => {
                    vmRekon.isLoadingFtp = false;
                    vmRekon.pesanProses = "<b class='text-red'>PROSES PENGECEKAN FILE REKON ERROR...</b>";
                });
            },

            transferRekon: function(filePath, jenis){

                vmRekon.isLoadingFtp = true;
                vmRekon.pesanProses = "<b class='text-yellow'>PROSES TRANSFER REKON...</b>";

                vmRekon.$http.post("{{ secure_url('/admin/rekon_pln/rekon') }}", { 
                    path: filePath,
                    jenis: jenis,
                    _token: "{{ csrf_token() }}"

                }).then(response => {
                    vmRekon.isLoadingFtp = false;
                    vmRekon.pesanProses = "<b class='text-green'>"+response.body.message+"</b>";
                    vmRekon.cekFileRekon();
                    
                }, response => {    
                    vmRekon.isLoadingFtp = false;
                    vmRekon.pesanProses = "<b class='text-red'>PROSES TRANSFER REKON ERROR...</b>";
                });
            },

		},

    });

});

function cancelPayment(id){
    vJenis = $("#pilJenis").val();

    axios.post("{{ secure_url('/admin/rekon_pln/cancel') }}", { id: id, jenis: vJenis })
      .then(function(response){
        console.log(response);
      }).catch(function (error) {
        alert(error);
      });
}

</script>


@endsection