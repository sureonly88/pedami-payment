@extends('...layouts/template')

@section('content')

<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}

option {
	padding: 5px 5px 5px 5px;
}

</style>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Dashboard
    <small>Transaksi Pembayaran</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Admin</a></li>
    <li class="active">Transaksi Pembayaran</li>
  </ol>
</section>

<section class="content" id="panel-transaksi">
<div class="row">
    <div class="col-md-12">
    	<div class="box box-default">
    		<div class="box-header">
		      <h3 class="box-title">TRANSAKSI PEMBAYARAN</h3>
		    </div>
            <div class="box-body">
            		<input id="username" type="hidden" value="{!!$user['username']!!}">
	                <input id="loket_name" type="hidden" value="{!!$user['loket_name']!!}">
	                <input id="loket_code" type="hidden" value="{!!$user['loket_code']!!}">

            		<div class="row">
	            		<div class="col-md-2">
		    				<select class="form-control" v-model="pilJenis" id="pilJenis" @change="cekPrepaid()">
		    					<?php if (Auth::user()->hasPermissionTo('Transaksi Pdambjm')) { ?>
								<option value="PDAMBJM" >PDAM BANDARMASIH</option>
								<?php } ?>
								<?php if (Auth::user()->hasPermissionTo('Transaksi PLN Postpaid')) { ?>
								<option value="PLN_POSTPAID" >PLN POSTPAID</option>
								<?php } ?>
								<?php if (Auth::user()->hasPermissionTo('Transaksi PLN Prepaid')) { ?>
								<option value="PLN_PREPAID" >PLN PREPAID</option>
								<?php } ?>
								<?php if (Auth::user()->hasPermissionTo('Transaksi PLN Nontaglis')) { ?>
								<option value="PLN_NONTAG" >PLN NON TAGLIS</option>
								<?php } ?>
								<?php if (Auth::user()->hasPermissionTo('PLN Pospaid Lunasin')) { ?>
								<option value="PLN_POSTPAID_NEW" >PLN POSTPAID v2</option>
								<?php } ?>
								<?php if (Auth::user()->hasPermissionTo('PLN Prepaid Lunasin')) { ?>
								<option value="PLN_PREPAID_NEW" >PLN PREPAID v2</option>
								<?php } ?>
							</select>
		    			</div>

		    			<div class="col-md-2" v-show="isPrepaid">
		    				<select :disabled="isLoading"  class="form-control" id="pilToken">
								<option value="20000">Rp 20.000</option>
								<option value="50000">Rp 50.000</option>
								<option value="100000">Rp 100.000</option>
								<option value="200000">Rp 200.000</option>
								<option value="500000">Rp 500.000</option>
								<option value="1000000">Rp 1.000.000</option>
								<option value="5000000">Rp 5.000.000</option>
							</select>
		    			</div>

		                <div class="col-md-3">
							<input id="idlgn" @keyup.enter="Inquery" v-model="idlgn" style="text-align: right; width: 180px" placeholder="Nomor Transaksi" class="form-control" type="text">
							
						</div>

	                </div>

	                <br/>
            		 <div class="row">
						<div class="col-md-12">
							<div style="width:100%;overflow:auto;">
								<table id="dataTable" class="table table-bordered table-hover table-striped dataTable">
								<thead>
								<tr>
									<th style="min-width:50px">AKSI</th>
									<th style="min-width:100px">ID PELANGGAN</th>
									<th style="min-width:200px">NAMA</th>
									<th style="min-width:100px">PERIODE</th>
									<th style="min-width:50px">JML</th>
									<th style="min-width:100px">DISKON</th>
									<th style="min-width:100px">SUB TOTAL</th>
									<th style="min-width:100px">ADMIN</th>
									<th style="min-width:100px">TOTAL</th>
									<th style="min-width:100px">PRODUK</th>
								</tr>
								</thead>
								<tbody id="dataLap">
									<tr v-for="Rek in dataRek">
									<td><button type='button' :disabled="isLoading" @click="deleteRek(Rek.data.idpel)" class='btn btn-primary btn-xs'>Batal</button></td>
			                            <td>
			                            	<a href="#" @click="showDetail(Rek.data.idpel,Rek.data.produk)" v-if="Rek.data.produk == 'PDAMBJM' || Rek.data.produk == 'PLN_POSTPAID'">@{{ Rek.data.idpel }}</a>
			                            	<span v-else>@{{ Rek.data.idpel }}</span>
			                            </td>
			                            <td>@{{ Rek.data.nama }}</td>
			                            <td>@{{ Rek.data.periode }}</td>
			                            <td>@{{ Rek.data.jml }}</td>
										<td>@{{ Rek.data.diskon | currency('',0) }}</td>
										<td>@{{ Rek.data.sub_total | currency('',0) }}</td>
			                            <td>@{{ Rek.data.admin | currency('',0) }}</td>
			                            <td>@{{ Rek.data.total | currency('',0) }}</td>
			                            <td>@{{ Rek.data.produk }}</td>

			                        </tr>
								</tbody>
								</table>
							</div>
						</div>
					</div>

					<hr/>

	            	<div class="row">
	                    <div class="col-md-6">
	                        <div class="form-group">
	                            <label for="total" style="font-size: x-large" class="control-label">TOTAL BAYAR</label>
	                            <input id="total" v-model="totalBayar" readonly="" style="text-align: right; font-size: x-large; height: 50px" placeholder="Total Bayar" class="form-control" type="text">
	                        </div>
	                        <div class="form-group" > 
	                            <label for="bayar" style="font-size: x-large" class="control-label">BAYAR</label>
	                            <input id="bayar" @blur="setBayar()" @keyup.enter="payment()" :disabled="isLoading" v-model="pelBayar" style="text-align: right; font-size: x-large; height: 50px" placeholder="Rupiah Bayar" class="form-control" type="text">
	                        </div>
	                        <div class="form-group" >
	                            <label for="kembalian" style="font-size: x-large" class="control-label">KEMBALIAN</label>
	                            <input id="kembalian" v-model="kembalian" style="text-align: right; font-size: x-large; height: 50px" readonly="" placeholder="Rupiah Kembalian" class="form-control" type="text">
	                        </div>

	                        <span id="warningPrint">
		                        <div class="panel panel-default">

		                            <div class="panel-body">
		                            	<i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>
			                            PLUG IN PRINTER TIDAK TERDETEKSI, JALANKAN APLIKASI PRINTER CLIENT.</b>
			                            &nbsp;&nbsp;
			                            <button type="button" id="btnRefreshPrint" onclick="refreshPrinter()" class="btn btn-xs btn-warning"/>RELOAD</button>
		                            </div>
		                        </div>
	                        </span>

	                        <div id="panelPrint" style="display: none">

		                        <div class="form-group">
		                          <label for="jenisKertas" class="control-label">Jenis Kertas</label>
		                          <select class="form-control" id="jenisKertas" style="width: 100%" >
		                            <option value="A4-4">Kertas A4 / 4</option>
		                            <option value="A4-3">Kertas A4 / 3</option>
		                            <!-- <option value="A4-2">Kertas A4 / 2</option>
		                            <option value="A6">Kertas A6</option> -->
		                          </select>
		                        </div>

		                    </div>
	                	</div>

	                	<div class="col-md-6">
    
	                        <div class="panel panel-default" v-show="showMessage">

	                            <div class="panel-body">
	                                <div v-html="pesanLoading" > </div>
	                            </div>
	                        </div>

	                    </div>

	               	</div>

	               	<button type="button" id="btnCetakUlang" @click="cetakRekap()" class="btn btn-primary">Cetak Rekap</button>
	               	<button type="button" id="btnCetakUlang" @click="cetakUlang()" class="btn btn-primary">Cetak Ulang</button>
					<button type="button" id="btnKolektif" @click="showKolektif()" class="btn btn-primary">Kolektif</button>

            </div>
        </div>
    </div>

</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalKolektif">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">DAFTAR KOLEKTIF</h4>
      </div>
      <div class="modal-body">

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
                        <button type='button' @click="pilihKolektif(Kolektif.id)" class='btn btn-primary btn-xs'>Pilih</button>
                    </td>
                </tr>
            </tbody>
        </table>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

    </div>
   </div>
</div>

@include('...cetakan/cetakan')
@include('...modals/modalCetakUlang')
@include('...modals/modalDetail')
@include('...cetakan/rekap_trx')
</section>

<script type="text/javascript">
	var connection;

    function refreshPrinter(){
        connection = new WebSocket('ws://localhost:8551/Laputa');

        connection.onopen = function () {
            $("#warningPrint").css('display', 'none'); 
            $("#panelPrint").css('display', 'inline');  
            //$("#isPrinterBaru").prop('checked', true);
            //console.log("connected");
        };

        // Log errors
        connection.onerror = function (error) {
            $("#warningPrint").css('display', 'inline'); 
            $("#panelPrint").css('display', 'none'); 
            //$("#isPrinterBaru").prop('checked', false);
            //console.log('WebSocket Error ' + error);
        };

        // Log messages from the server
        connection.onmessage = function (e) {
            //console.log('Server: ' + e.data);
        };
    }

    function kirimData(data,jenisKertas,jmlRek){
        dataKirim = data+"&"+jenisKertas+"&"+jmlRek;
        //console.log(dataKirim);
        connection.send(dataKirim);
        //console.log("Terkirim");
    }

	$(document).ready(function() {
		var vm = new Vue({
			el: '#panel-transaksi',
			data: {
				pesanLoading: '',
	            pesanCetakUlang: '',
	            isLoading: false,
	            idlgn:'',
				pilJenis:'PDAMBJM',
	            pelBayar: '',
	            dataRek: [],
	            isPrepaid: false,
	            ulangIdpel: '',
	            cuProduk: '',
	            detailPdam: [],
	            detailPlnPost: [],
	            detailPlnPre: [],
	            detailPlnNon: [],
				dataKolektif: []
			},

			mounted() {
				//$("select").select2();
				refreshPrinter();
				$("#txtTglAwal").datepicker({ dateFormat: 'yy-mm-dd' }); 
	        	$("#txtTglAkhir").datepicker({ dateFormat: 'yy-mm-dd' }); 
			},

			computed: {
				showMessage: function () {
	                if(this.pesanLoading.length > 0){
	                    return true;
	                }else{
	                    return false;
	                }
	            },

	            totalBayar: function(){
	                totalTagih = 0;

	                if(this.dataRek.length > 0){
	                    for(i=0; i<this.dataRek.length; i++){
	                        totalTagih += parseInt(this.dataRek[i].data.total);
	                    }
	                }
	                return numeral(totalTagih).format('0,0');
	            },

	            totalAdmin: function(){
	                totalTagih = 0;

	                if(this.dataRek.length > 0){
	                    for(i=0; i<this.dataRek.length; i++){
	                        totalTagih += parseInt(this.dataRek[i].data.admin);
	                    }
	                }
	                return numeral(totalTagih).format('0,0');
	            },


	            totalSub: function(){
	                totalTagih = 0;

	                if(this.dataRek.length > 0){
	                    for(i=0; i<this.dataRek.length; i++){
	                        totalTagih += parseInt(this.dataRek[i].data.sub_total);
	                    }
	                }
	                return numeral(totalTagih).format('0,0');
	            },

	            kembalian: function(){
	                nTagihan = parseInt(this.totalBayar.replace(/,/g,""));
	                nBayar = parseInt(this.pelBayar.replace(/,/g,""));
	                nKembalian = nBayar - nTagihan;
	                return numeral(nKembalian).format('0,0');
	            },

			},

			methods: {
				notify: function(Title,Pesan){
	                $.notify({
	                  icon: 'fa fa-user',
	                  title: "<strong>"+Title+"</strong>",
	                  message: Pesan
	                },{
	                  type: 'success'
	                });
	            },

				showKolektif: function(){
					$('#modalKolektif').modal("show");
					this.LoadDataKolektif();
				},

				LoadDataKolektif: function(){
					axios.get("{{ secure_url('/admin/pdam_kolektif/daftar') }}")
						.then(function (response) {
							if(response.data.status){
								vm.dataKolektif = response.data.data;
								//console.log(response.data.data);
							}else{
								vm.dataKolektif = [];
							}
							
						})
						.catch(function (error) {
						//console.log(error);
					});
				},


				pilihKolektif: function(Id){
					$('#modalKolektif').modal("hide");
					//vmPdam.dataRek = [];
                	//var ErrorMessage = "";

					axios.get("{{ secure_url('/admin/pdam_kolektif/kolektif') }}/"+Id)
                    .then(function (response) {
                        if(response.data.status){

							var q = $.jqmq({
                                delay: -1,
                                callback: function(IdCust) {
									//vm.pilJenis = IdCust.split("-")[1];
									console.log(IdCust.split("-")[0] + "-" + IdCust.split("-")[1]);
									//vm.idlgn = IdCust.split("-")[0];

									if(vm.isLoading){
										$.notify({
										icon: 'fa fa-warning',
										title: "<strong>Prevent</strong> : ",
										message: "Proses Inquery Belum Selesai."
										},{
										type: 'warning'
										});
										return;
									}

									// if(vm.cekIdExist()){
									// 	vm.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>NO PELANGGAN SUDAH ADA.</b></span>";
									// 	return;
									// }

									// if(vm.idlgn.length <=0 ) {
									// 	$("#bayar").focus();
									// 	$("#bayar").select();
									// 	return;
									// }

									vm.pesanLoading = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>INQUERY TAGIHAN PELANGGAN...</b></span>";
									vm.isLoading = true;

									kodeLoket = $("#loket_code").val();
									//pilJenis = $("#pilJenis").val();
									pilToken = $("#pilToken").val();

									nJenis = IdCust.split("-")[1];
									//console.log(JenisTrx + "-" + IdCust);
									nIdlgn = IdCust.split("-")[0];

									vm.$http.get("{{ secure_url('/api/transaksi_bayar') }}/inquery/"+nIdlgn+"/"+nJenis+"/"+kodeLoket+"/"+pilToken).then(response => {
										if(response.body.status){
											
											vm.pesanLoading = "";

											Rek = response.body;

											vm.dataRek.push(Rek);

											//vm.idlgn = "";
											//$("#idlgn").focus();

											vm.isLoading = false;
											q.next();
											
										}else{
											vm.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message.toUpperCase()+"</b></span>";
											vm.isLoading = false;
											q.next();
										}

										vm.isLoading = false;
									}, response => {
										vm.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>TERJADI KESALAHAN SISTEM</b></span>";
										vm.isLoading = false;
										q.next();
									});

								},
                                complete: function(){
                                    
                                }
							});

							$.ajaxSetup({ cache: false });

                            listIdlgn = response.data.data;
                            for(i=0; i<listIdlgn.length; i++){
								//console.log(listIdlgn[i].jenis);
                                q.add(listIdlgn[i].id_pelanggan+"-"+listIdlgn[i].jenis);
                            }

						}

						else{
                            //vmPdam.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>DATA KOLEKTIF KOSONG</b></span><br/>";
                        }

					})
					.catch(function (error) {
						//console.log(error);
					});
				},

	            cekPrepaid: function(){
	            	pilProduk = $("#pilJenis").val();
	            	if(pilProduk == "PLN_PREPAID" || pilProduk == "PLN_PREPAID_NEW"){
	            		vm.isPrepaid = true;
	            	}else{
	            		vm.isPrepaid = false;
	            	}
	            },

				setBayar: function(){
					//nBayar = parseInt(this.pelBayar.replace(/,/g,""));
					//this.pelBayar = numeral(nBayar).format('0,0');
				},

				prosesBayar: function(){

				},

				showDetail: function(idpel, produk){
					for(i=0; i<vm.dataRek.length; i++){
	                    if(idpel == vm.dataRek[i].data.idpel){

	                    	switch(produk){
	                    		case 'PDAMBJM':
	                    			vm.detailPdam = vm.dataRek[i].detail;
	                    			$('#detailPdambjm').modal("show");
	                    			break;
	                    		case 'PLN_POSTPAID':
	                    			vm.detailPlnPost = [];
	                    			vm.detailPlnPost.push(vm.dataRek[i].detail);
	                    			$('#detailPlnPost').modal("show");
	                    			break;
	                    		case 'PLN_PREPAID':
	                    			//vm.detailPdambjm = vm.dataRek[i].detail;
	                    			//$('#detailPdambjm').modal("show");
	                    			break;
	                    		case 'PLN_NONTAG':
	                    			//vm.detailPdambjm = vm.dataRek[i].detail;
	                    			//$('#detailPdambjm').modal("show");
	                    			break;
	                    	}
	                    	
	                        return;
	                    }
	                }
				},

				ProsesCetakUlang: function() {
					var tglAwal 	= $("#txtTglAwal").val();
					var tglAkhir 	= $("#txtTglAkhir").val();
					var produk 		= $("#pilProduk").val();

					this.$http.get("{{ secure_url('api/transaksi_bayar/cetak_ulang') }}/"+vm.ulangIdpel+"/"+tglAwal+"/"+tglAkhir+"/"+vm.cuProduk).then(response => {
						if(response.body.status){
							mData = response.body.data;
							switch(response.body.produk){
								case 'PDAMBJM':
									vm.cetakPdambjm(mData, true);
									break;
								case 'PLN_POSTPAID':
									vm.cetakPlnPost(mData, true);
									break;
								case 'PLN_PREPAID':
									vm.cetakPlnPre(mData[0], true);
									break;
								case 'PLN_NONTAG':
									vm.cetakPlnNon(mData, true);
									break;
							}
							
						}else{
							alert(response.body.message);
						}
						
					}, response => {
		            	
		            });
				},

				cetakUlang: function () {
					$('#cetakDialog').modal("show");
	    			$("#ulangIdpel").focus();
				},

				Inquery: function(){

					if(this.isLoading){
	                    $.notify({
	                      icon: 'fa fa-warning',
	                      title: "<strong>Prevent</strong> : ",
	                      message: "Proses Inquery Belum Selesai."
	                    },{
	                      type: 'warning'
	                    });
	                    return;
	                }

	                if(this.cekIdExist()){
	                    this.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>NO PELANGGAN SUDAH ADA.</b></span>";
	                    return;
	                }

	                if(this.idlgn.length <=0 ) {
	                    $("#bayar").focus();
	                    $("#bayar").select();
	                    return;
	                }

	                this.pesanLoading = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>INQUERY TAGIHAN PELANGGAN...</b></span>";
	                this.isLoading = true;

	                kodeLoket = $("#loket_code").val();
	                pilJenis = $("#pilJenis").val();
	                pilToken = $("#pilToken").val();

	                this.$http.get("{{ secure_url('/api/transaksi_bayar') }}/inquery/"+this.idlgn+"/"+pilJenis+"/"+kodeLoket+"/"+pilToken).then(response => {
	                    if(response.body.status){
	                        
	                        this.pesanLoading = "";

	                        Rek = response.body;

	                        this.dataRek.push(Rek);

	                        this.idlgn = "";
	                        $("#idlgn").focus();
	                        
	                    }else{
	                        this.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message.toUpperCase()+"</b></span>";
	                    }

	                    this.isLoading = false;
	                }, response => {
	                    this.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>TERJADI KESALAHAN SISTEM</b></span>";
	                    this.isLoading = false;
	                });
	            },

	            payment: function(){
	            	this.pesanLoading = "";

	            	//Ambil Total Tagihan
	            	totalTagih = 0;
	                if(this.dataRek.length > 0){
	                    for(i=0; i<this.dataRek.length; i++){
	                        totalTagih += parseInt(this.dataRek[i].data.total);
	                    }
	                }

	            	if(parseInt(this.pelBayar) < parseInt(totalTagih)){
	            		this.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>UANG TIDAK CUKUP.</b></span>"; 
	            		return;
	            	}

	            	if(this.dataRek.length <= 0){
	            		this.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>TIDAK ADA DATA TRANSAKSI.</b></span>"; 
	            		return;
	            	}

	            	if(this.isLoading){
	                    $.notify({
	                      icon: 'fa fa-warning',
	                      title: "<strong>Prevent</strong> : ",
	                      message: "Proses Pembayaran Belum Selesai."
	                    },{
	                      type: 'warning'
	                    });
	                    return;
	                }

	            	this.isLoading = true;

	            	var q = $.jqmq({
						delay: -1,
	      				callback: function(mData) {
	      					var q = this;

	      					vm.pesanLoading += "<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES "+mData.data.produk+" NO.PEL : "+mData.data.idpel+" A/N "+mData.data.nama+"</b></span><br/>";

	      					switch(mData.data.produk){
	      						case "PDAMBJM":
	      							vm.paymentPdambjm(q, mData.detail);
	      							break;
	      						case "PLN_POSTPAID":
	      							vm.paymentPlnPost(q, mData.detail, mData.payment_message, mData.reversal_message);
	      							break;
								case "PLN_POSTPAID_NEW":
	      							vm.paymentPlnPost(q, mData.detail, mData.payment_message, mData.reversal_message);
	      							break;
	      						case "PLN_PREPAID":
	      							vm.paymentPlnPre(q, mData.detail, mData.payment_message, mData.reversal_message, mData.data.sub_total, 1);
	      							break;
	      						case "PLN_NONTAG":
	      							vm.paymentPlnNon(q, mData.detail, mData.payment_message, mData.reversal_message, mData.data.total);
	      							break;
	      						default: 
	      							q.next();
	      					}
	      				},
						complete: function(){
							vm.pesanLoading += "<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-green'>PROSES PEMBAYARAN SELESAI.</span><br/>";

							vm.pesanLoading += "<div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>TOTAL BAYAR</b></div><div class='col-md-6'><b>: Rp. "+vm.totalBayar+"</b></div></div><div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>BAYAR</b></div><div class='col-md-6'><b>: Rp. "+numeral(vm.pelBayar).format('0,0')+"</b></div></div><div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>KEMBALIAN</b></div><div class='col-md-6'><b>: Rp. "+vm.kembalian+"</b></div></div><br/>";

							vm.dataRek = [];
							vm.pelBayar = '';

							vm.isLoading = false;
						}
					});

					$.ajaxSetup({ cache: false });

					for (index = 0; index < this.dataRek.length; ++index) {
					    q.add(this.dataRek[index]);
					}
	            },

	            paymentPdambjm: function(q, DataRek){
	            	console.log(DataRek);

	            	var DataRekening = {};
                    var Rekening = [];

                    DataRekening.PaymentData = Rekening;

	            	var User = $("#username").val();
                    var LoketName = $("#loket_name").val();
                    var LoketCode = $("#loket_code").val();

	            	for(i=0; i<DataRek.length; i++){

	            		Rek = DataRek[i];

	            		var Rekening = {
	                        "Idlgn": Rek.idlgn,
	                        "Nama": Rek.nama,
	                        "Idgol": Rek.gol,
	                        "Alamat": Rek.alamat,
	                        "Thbln": Rek.thbln,
	                        "Pakai": Rek.pakai,
	                        "Harga": Rek.harga,
	                        "ByAdmin": Rek.byadmin,
	                        "Materai": Rek.materai,
	                        "Retri": Rek.retribusi,
	                        "Denda": Rek.denda,
	                        "Sub_Tot": Rek.total,
	                        "Limbah": Rek.limbah,
	                        "Stand_l": Rek.stand_l,
	                        "Stand_i": Rek.stand_i,
	                        "Admin_Kop": Rek.admin_kop,
							"Diskon": Rek.diskon,
	                        "Total": parseInt(Rek.total)+parseInt(Rek.admin_kop),
	                        "User": User,
	                        "LoketName": LoketName,
	                        "LoketCode": LoketCode,
	                        "Biaya_tetap": Rek.biaya_tetap,
	                        "Biaya_meter": Rek.biaya_meter
	                    }

	                    DataRekening.PaymentData.push(Rekening);
	            	}

	            	jenisKertas = $("#jenisKertas").val();

	            	this.$http.post("{{ secure_url('api/pdambjm/transaksi') }}", {
                            PaymentData: DataRekening, 
                            isPrinterBaru: 1,
                            jenisKertas: jenisKertas,
                            _token: "{{ csrf_token() }}" }).then(response => {

                        msg = response.body;
                        if(msg.status == "Success"){

                        	vm.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-green'>PAYMENT IDPEL " 
                        	+ msg.data[0].cust_id + " BERHASIL</span></b><br/><br/>";

                        	//printData = msg.print_data;

                            //for(i=0; i<printData.length; i++){
                                //kirimData(printData[i].print_data, printData[i].jenis_kertas, printData[i].jml_rek);
                            //}

                         	vm.cetakPdambjm(msg.data);

                            q.next();
                        }else{

                        	vm.pesanLoading += "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+msg.message.toUpperCase()+"</span></b><br/><br/>";
                            q.next();
                        }
                    }, response => {
                            vm.pesanLoading += "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>TERJADI KESALAHAN PAYMENT.</span></b><br/><br/>";
                            q.next();
                    });
	            },

	            paymentPlnPost: function(q,dataRek,payment_message,reversal_message){

	            	nTotalBayar = dataRek.total_tagihan;
	            	subcriber_id = dataRek.subcriber_id;

  					this.$http.post("{{ secure_url('api/pln/postpaid/payment') }}", {
  						subcriber_id: subcriber_id, 
  						payment_message: payment_message, 
  						total_bayar: nTotalBayar, 
  						_token: "{{ csrf_token() }}" }).then(response => {

  						if(response.body.status){
      						text_color = "text-green";
      						if(response.body.response_code != "0000"){
      							text_color = "text-red";
      						}else{
      							vm.cetakPlnPost(response.body.customer, false);
      						}

      						vm.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

      						q.next();
  						}else{
  							vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/><br/>";

  							//BILA RESPONSE PAYMENT BUKAN 0088 - BILL ALREADY PAID LAKUKAN REVERSAL
  							//BILA RESPONSE PAYMENT BUKAN 9990 - PULSA TIDAK CUKUP
  							if(1!=1){
  							// if(response.body.response_code != '0088' && response.body.response_code != '9990' && response.body.response_code != '0046'){

  								vm.pesanLoading +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK PROSES PEMBATALAN KE 1</b></span><br/><br/>";

      							setTimeout(function(){

      								//DO FIRST REVERSAL
	      							vm.pesanLoading +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES REVERSAL / PEMBATALAN KE 1...</b></span><br/>";

		      						vm.$http.post("{{ secure_url('api/pln/postpaid/reversal') }}", {
		      							subcriber_id: subcriber_id, 
		      							payment_message: reversal_message,
		      							number_request:1,
		      							_token: "{{ csrf_token() }}" }).then(response => {

		      							//console.log(response.body);

		      							vm.pesanLoading +=	"<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span><br/><br/>";

		      							response_code = response.body.response_code;
		      							//response_code = "0012";
		      							if(response_code == "0012"){
		      								vm.cetakPlnPost(response.body.customer, false);
		      							}

		      							//BILA RESPONSE CODE REVERSAL BUKAN 0000 DAN 0063 REQUEST REVERSAL LAGI. 
		      							if(response_code != "0000" && response_code != "0063" && response_code != "0012" && response_code != "0088" && response_code != "0094"){

		      								vm.pesanLoading +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK PROSES PEMBATALAN KE 2</b></span><br/><br/>";

		      								setTimeout(function(){

		      									vm.pesanLoading +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES REVERSAL / PEMBATALAN KE 2...</b></span><br/>";
			      								//DO SECOND REVERSAL
			      								vm.$http.post("{{ secure_url('api/pln/postpaid/reversal') }}", {
					      							subcriber_id: subcriber_id, 
					      							payment_message: reversal_message,
					      							number_request:2,
					      							_token: "{{ csrf_token() }}" }).then(response => {

					      								//console.log(response.body);

					      								vm.pesanLoading +=	"<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span><br/><br/>";
					      								q.next();
								      				}, response => {
								      					vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES REVERSAL 2 ERROR</span></b><br/><br/>";
		      											q.next();
						      					});
		      								}, 35000);
		      									
		      								//END DO SECOND REVERSAL
		      							}else{
		      								q.next();
		      							}
		      						}, response => {
		      							vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES REVERSAL 1 ERROR</span></b><br/><br/>";
	      								q.next();
		      						});
		      						//END DO FIRST REVERSAL
      							}, 35000);

  							}else{
  								q.next();
  							}
  						}
  						
  					}, response => {
  						vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES PEMBAYARAN ERROR</span></b><br/><br/>";
  						q.next();
					});
	            },

	            paymentPlnPre: function(q,dataRek,purchase_message,reversal_message,jmlToken,pilToken){

	            	subscriber_id = dataRek.subscriber_id;

  					this.$http.post("{{ secure_url('api/pln/prepaid/purchase') }}", {
  						idpel: subscriber_id, 
  						payment_message: purchase_message, 
  						rupiah_token: jmlToken, 
  						buying_option: pilToken, 
  						_token: "{{ csrf_token() }}" }).then(response => {
  						
  						//console.log(response.body);

  						if(response.body.status){
      						text_color = "text-green";
      						if(response.body.response_code != "0000"){
      							text_color = "text-red";
      						}else{
      							vm.cetakPlnPre(response.body.customer, false);
      						}

      						vm.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

      						q.next();
  						}else{
  							vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/><br/>";

  							//ADVICE ULANG PEMBELIAN TOKEN
  							//BILA RESPONSE PAYMENT BUKAN 9990 - PULSA TIDAK CUKUP
  							if(1!=1){
  							// if(response.body.response_code != '9990' && response.body.response_code != '0013' && response.body.response_code != '0046'){
  								vm.pesanLoading +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK ULANG PEMBELIAN KE 1</b></span><br/><br/>";

  								//PROSES PENGULANGAN PURCHASE KE-1
      							setTimeout(function(){

      								vm.pesanLoading +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES ULANG PEMBELIAN KE 1...</b></span><br/>";

      								vm.$http.post("{{ secure_url('api/pln/prepaid/advise') }}", {
      									idpel: subscriber_id, 
      									reversal_message: reversal_message, 
      									rupiah_token: jmlToken, 
      									counter:1,
      									_token: "{{ csrf_token() }}" }).then(response => {

      									//console.log(response.body);

      									if(response.body.status){
      										text_color = "text-green";
	      										if(response.body.response_code != "0000"){
	      											text_color = "text-red";
	      										}else{
	      											vm.cetakPlnPre(response.body.customer, false);
	      										}

	      									vm.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

	      									q.next();
      									}else{
      										vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/><br/>";

      										if(response.body.response_code != '0063'){
      											vm.pesanLoading +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK ULANG PEMBELIAN KE 2</b></span><br/><br/>";

	      										//PROSES PENGULANGAN PURCHASE KE-2
	      										setTimeout(function(){

	      											vm.pesanLoading +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES ULANG PEMBELIAN KE 2...</b></span><br/>";

				      								vm.$http.post("{{ secure_url('api/pln/prepaid/advise') }}", {
				      									idpel: subscriber_id, 
				      									reversal_message: reversal_message, 
				      									rupiah_token: jmlToken, 
				      									counter:2,
				      									_token: "{{ csrf_token() }}" }).then(response => {

				      									//console.log(response.body);

				      									if(response.body.status){
				      										text_color = "text-green";
					      										if(response.body.response_code != "0000"){
					      											text_color = "text-red";
					      										}else{
					      											vm.cetakPlnPre(response.body.customer, false);
					      										}

					      									vm.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

					      									q.next();
				      									}else{
				      										//SELESAI GAK ADA PERULANGAN ADVISE LAGI
				      										vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/>";

				      										vm.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES ULANG PEMBELIAN BISA DILAKUKAN DI MENU MANUAL PLN PRABAYAR</span></b><br/><br/>";

				      										q.next()

				      									}

				      								}, response => {
				      									vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES ADVISE ERROR</span></b><br/><br/>";
				      									q.next();
													});
				      									

				      							}, 35000);
	      										//AKHIR PROSES PENGULANGAN PURCHASE KE-2
      										}else{
      											q.next();
      										}
      									}

      								}, response => {
      									vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES ADVISE ERROR</span></b><br/><br/>";
      									q.next();
									});
      									

      							}, 35000);
      							//AKHIR PROSES PENGULANGAN PURCHASE KE-1
  							}else{
  								q.next();
  							}
  						}

  					}, response => {
  						vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES PEMBAYARAN ERROR</span></b><br/><br/>";
  						q.next();
					});
	            },

	            paymentPlnNon: function(q,dataRek,payment_message,reversal_message,nTotalBayar){

	            	register_number = dataRek.register_number;

  					this.$http.post("{{ secure_url('api/pln/nontaglis/payment') }}", {
  						register_number: register_number, 
  						payment_message: payment_message, 
  						total_bayar: nTotalBayar, 
  						_token: "{{ csrf_token() }}" }).then(response => {

  						//console.log(response.body);

  						if(response.body.status){
      						text_color = "text-green";
      						if(response.body.response_code != "0000"){
      							text_color = "text-red";
      						}else{
      							vm.cetakPlnNon(response.body.customer, false);
      						}

      						vm.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

      						q.next();
  						}else{
  							vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/><br/>";

  							//BILA RESPONSE PAYMENT BUKAN 0088 - BILL ALREADY PAID LAKUKAN REVERSAL
  							//BILA RESPONSE PAYMENT BUKAN 9990 - PULSA TIDAK CUKUP
  							if(response.body.response_code != '0088' && response.body.response_code != '9990' && response.body.response_code != '0046'){

  								vm.pesanLoading +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK PROSES PEMBATALAN KE 1</b></span><br/><br/>";

      							setTimeout(function(){

      								//DO FIRST REVERSAL
	      							vm.pesanLoading +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES REVERSAL / PEMBATALAN KE 1...</b></span><br/>";

		      						vm.$http.post("{{ secure_url('api/pln/nontaglis/reversal') }}", {
		      							register_number: register_number, 
		      							reversal_message: reversal_message,
		      							number_request: 1,
		      							_token: "{{ csrf_token() }}" }).then(response => {

		      							//console.log(response.body);

		      							vm.pesanLoading +=	"<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span><br/><br/>";

		      							response_code = response.body.response_code;
		      							//response_code = "0012";
		      							if(response_code == "0012"){
		      								vm.cetakPlnNon(response.body.customer, false);
		      							}

		      							//BILA RESPONSE CODE REVERSAL BUKAN 0000 DAN 0063 REQUEST REVERSAL LAGI. 
		      							if(response_code != "0000" && response_code != "0063" && response_code != "0012" && response_code != "0088" && response_code != "0094"){

		      								vm.pesanLoading +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK PROSES PEMBATALAN KE 2</b></span><br/><br/>";

		      								setTimeout(function(){

		      									vm.pesanLoading +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES REVERSAL / PEMBATALAN KE 2...</b></span><br/>";
			      								//DO SECOND REVERSAL
			      								vm.$http.post("{{ secure_url('api/pln/nontaglis/reversal') }}", {
					      							register_number: register_number, 
					      							reversal_message: reversal_message,
					      							number_request: 2,
					      							_token: "{{ csrf_token() }}" }).then(response => {

					      								//console.log(response.body);

					      								vm.pesanLoading +=	"<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span><br/><br/>";
					      								q.next();
								      				}, response => {
								      					vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES REVERSAL 2 ERROR</span></b><br/><br/>";
		      											q.next();
						      					});
		      								}, 35000);
		      									
		      								//END DO SECOND REVERSAL
		      							}else{
		      								q.next();
		      							}
		      						}, response => {
		      							vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES REVERSAL 1 ERROR</span></b><br/><br/>";
	      								q.next();
		      						});
		      						//END DO FIRST REVERSAL
      							}, 35000);

  							}else{
  								q.next();
  							}
  						}
  						
  					}, response => {
  						vm.pesanLoading += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES PEMBAYARAN ERROR</span></b><br/><br/>";
  						q.next();
					});
	            },

	            fungsiLain: function(){

	            },

	            formatTglCetak: function(mTgl){
					mDay = mTgl.substr(6,2);
					mBln = parseInt(mTgl.substr(4,2));
					mNamaBln = "";
					switch (mBln) {
					    case 1:
					        mNamaBln = "JAN";
					        break;
					    case 2:
					        mNamaBln = "FEB";
					        break;
					    case 3:
					        mNamaBln = "MAR";
					        break;
					    case 4:
					        mNamaBln = "APR";
					        break;
					    case 5:
					        mNamaBln = "MEI";
					        break;
					    case 6:
					        mNamaBln = "JUN";
					        break;
					    case 7:
					        mNamaBln = "JUL";
					        break;
					    case 8:
					        mNamaBln = "AGT";
					        break;
					    case 9:
					        mNamaBln = "SEP";
					        break;
					    case 10:
					        mNamaBln = "OKT";
					        break;
					    case 11:
					        mNamaBln = "NOV";
					        break;
					    case 12:
					        mNamaBln = "DES";
					        break;   
					}
					mThn = mTgl.substr(2,2);

					return mDay+mNamaBln+mThn;
				},

				cekIdExist: function () {
	                for(i=0; i<vm.dataRek.length; i++){
	                	//console.log(vm.idlgn + " = " + vm.dataRek[i].data.idpel);
	                    if(vm.idlgn == vm.dataRek[i].data.idpel){
	                        return true;
	                    }
	                }
	                return false;
	            },

	            deleteRek: function (idlgn){
	                for(i=0; i<vm.dataRek.length; i++){

	                    if(idlgn == vm.dataRek[i].data.idpel){
	                        vm.dataRek.splice(i, 1);
	                        vm.deleteRek(idlgn);
	                    }
	                }
	            },

	            cetakPlnNon: function(mData, isCu) {
					$("#CtkNoreg3").html(mData.register_number);

					mTglReg = this.formatTglCetak(mData.registration_date);
					//mTglReg = mTglReg.substr(6,2) + "-" + mTglReg.substr(4,2) + "-" + mTglReg.substr(0,4);

	                $("#CtkTglReg3").html(mTglReg);
	                $("#CtkTransaksi3").html(mData.transaction_name);
	                $("#CtkNama3").html(mData.subscriber_name);
	                $("#CtkIdpel3").html(mData.subscriber_id);

	                cBill = parseInt(mData.pln_bill_value);
	                cBill = numeral(cBill).format('0,0');

	                $("#CtkBiayaPLN3").html(cBill);
	                $("#CtkRef3").html(mData.switcher_ref_number);

	                cAdmin = parseInt(mData.admin_charge);
	                cAdmin = numeral(cAdmin).format('0,0');
	                
	                $("#CtkAdmin3").html(cAdmin);

	                cBayar = parseInt(mData.pln_bill_value) + parseInt(mData.admin_charge);
	                cBayar = numeral(cBayar).format('0,0');

	                $("#CtkTotal3").html(cBayar);

	                $("#CtkInfo3").html(mData.info_text);
	                mCodeTrans = mData.transaction_code;
	                if(isCu){
	                    mCodeTrans = "CU-"+mData.transaction_code;
	                }

	                $("#CtkKode3").html(mCodeTrans);
	                $("#CtkTanggal3").html(mData.transaction_date);

	                var sDivText = $("#cetakRekeningPlnNon").html();
	                var objWindow = window.open("", "", "left=0,top=0,width=1,height=1");
	                objWindow.document.write(sDivText);
	                objWindow.document.close();
	                objWindow.focus();
	                objWindow.jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
	                objWindow.jsPrintSetup.setOption('marginTop', 5);
	                objWindow.jsPrintSetup.setOption('marginBottom', 0);
	                objWindow.jsPrintSetup.setOption('marginLeft', 5);
	                objWindow.jsPrintSetup.setOption('marginRight', 0);
	                // set page header
	                objWindow.jsPrintSetup.setOption('headerStrLeft', '');
	                objWindow.jsPrintSetup.setOption('headerStrCenter', '');
	                objWindow.jsPrintSetup.setOption('headerStrRight', '');
	                // set empty page footer
	                objWindow.jsPrintSetup.setOption('footerStrLeft', '');
	                objWindow.jsPrintSetup.setOption('footerStrCenter', '');
	                objWindow.jsPrintSetup.setOption('footerStrRight', '');
	                objWindow.jsPrintSetup.setOption('printSilent', 1);

	                objWindow.jsPrintSetup.print();
	                objWindow.close();
				},

				cetakRekap: function(){
					var sDivText = $("#CetakRekapTrx").html();
	                var objWindow = window.open("", "", "left=0,top=0,width=1,height=1");
	                objWindow.document.write(sDivText);
	                objWindow.document.close();
	                objWindow.focus();
	                objWindow.jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
	                objWindow.jsPrintSetup.setOption('marginTop', 5);
	                objWindow.jsPrintSetup.setOption('marginBottom', 0);
	                objWindow.jsPrintSetup.setOption('marginLeft', 5);
	                objWindow.jsPrintSetup.setOption('marginRight', 0);
	                // set page header
	                objWindow.jsPrintSetup.setOption('headerStrLeft', '');
	                objWindow.jsPrintSetup.setOption('headerStrCenter', '');
	                objWindow.jsPrintSetup.setOption('headerStrRight', '');
	                // set empty page footer
	                objWindow.jsPrintSetup.setOption('footerStrLeft', '');
	                objWindow.jsPrintSetup.setOption('footerStrCenter', '');
	                objWindow.jsPrintSetup.setOption('footerStrRight', '');
	                objWindow.jsPrintSetup.setOption('printSilent', 1);

	                objWindow.jsPrintSetup.print();
	                objWindow.close();
				},

	            cetakPlnPre: function(mData, isCu) {

					$("#CtkMeter2").html(mData.material_number);
					$("#CtkAdmin2").html(numeral(mData.admin).format('0,0'));
					$("#CtkIdpel2").html(mData.subscriber_id);

					cMaterai = numeral(mData.stampduty).format('0,0.00');
					$("#CtkMaterai2").html(cMaterai);

					$("#CtkNama2").html(mData.subscriber_name);

					cPpn = numeral(mData.valueaddedtax).format('0,0.00');
					$("#CtkPpn2").html(cPpn);

					cTarif = mData.subscriber_segment + "/" + parseInt(mData.power_categori);
					$("#CtkTarif2").html(cTarif);

					cPpj = numeral(mData.lightingtax).format('0,0.00');
					$("#CtkPpj2").html(cPpj);
					$("#CtkRef2").html(mData.switcher_ref_number);

					cAngsuran = numeral(mData.cust_payable).format('0,0.00');
					$("#CtkAngsuran2").html(cAngsuran);

					//cBayar = parseInt(mData.rupiah_token) + parseInt(mData.admin_charge);
					cBayar = parseInt(mData.stampduty) + parseInt(mData.valueaddedtax) + parseInt(mData.lightingtax) + parseInt(mData.admin) + parseInt(mData.power_purchase);
					cBayar = numeral(cBayar).format('0,0');
					$("#CtkBayar2").html(cBayar);

					cPowerPurchase = numeral(mData.power_purchase).format('0,0.00');
					$("#CtkStroom2").html(cPowerPurchase);

					purchase_kwhRound = ""+mData.purchase_kwh;
					purchase_kwhSplit = purchase_kwhRound.split(".");

					if(purchase_kwhSplit.length > 1){
						if(purchase_kwhSplit[1].length == 2){
							purchase_kwhRound = purchase_kwhRound.substr(0,purchase_kwhRound.length-1);
						}	
					}

					$("#CtkKwh2").html(numeral(purchase_kwhRound).format('0,0.0'));
					$("#CtkToken2").html(mData.token_number);
					$("#CtkInfo2").html(mData.info_text);

					mCodeTrans = mData.transaction_code;
					if(isCu){
						mCodeTrans = "CU-"+mData.transaction_code;
					}

					$("#CtkKode2").html(mCodeTrans);
					$("#CtkTanggal2").html(mData.transaction_date);

					var sDivText = $("#cetakRekeningPlnPre").html();
				    var objWindow = window.open("", "", "left=0,top=0,width=1,height=1");
				    objWindow.document.write(sDivText);
				    objWindow.document.close();
				    objWindow.focus();
				    objWindow.jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
				    objWindow.jsPrintSetup.setOption('marginTop', 5);
				    objWindow.jsPrintSetup.setOption('marginBottom', 0);
				    objWindow.jsPrintSetup.setOption('marginLeft', 5);
				    objWindow.jsPrintSetup.setOption('marginRight', 0);
				    // set page header
				    objWindow.jsPrintSetup.setOption('headerStrLeft', '');
				    objWindow.jsPrintSetup.setOption('headerStrCenter', '');
				    objWindow.jsPrintSetup.setOption('headerStrRight', '');
				    // set empty page footer
				    objWindow.jsPrintSetup.setOption('footerStrLeft', '');
				    objWindow.jsPrintSetup.setOption('footerStrCenter', '');
				    objWindow.jsPrintSetup.setOption('footerStrRight', '');
				    objWindow.jsPrintSetup.setOption('printSilent', 1);

				    objWindow.jsPrintSetup.print();
				    objWindow.close();
				},

	            cetakPlnPost: function(mData, isCu){
	            	for(i=0; i<mData.billing.length; i++){

						$("#CtkIdpel1").html(mData.subcriber_id);
						$("#CtkNama1").html(mData.subcriber_name);
						$("#CtkPeriode1").html(mData.billing[i].bill_periode);

						//cSisa = parseInt(mData.outstanding_bill) - parseInt(mData.bill_status);
						cSisa = parseInt(mData.billing[i].outstanding_bill) - parseInt(mData.billing[i].bill_status);

						cStand = mData.billing[i].prev_meter_read_1 + "-"+mData.billing[i].curr_meter_read_1;
						$("#CtkStand1").html(cStand);
						cTarif = mData.subcriber_segment + "/" + parseInt(mData.power_consumtion);
						$("#CtkTarif1").html(cTarif);

						nTagihan = parseInt(mData.billing[i].total_elec_bill)+parseInt(mData.billing[i].penalty_fee);

						cTagihan = numeral(parseInt(mData.billing[i].total_elec_bill)+parseInt(mData.billing[i].penalty_fee)).format('0,0');
						$("#CtkTagihan1").html("Rp. " + cTagihan);
						$("#CtkRef1").html(mData.switcher_ref);

						cAdmin = numeral(parseInt(mData.billing[i].admin_charge)).format('0,0');
						$("#CtkAdmin1").html("Rp. " + cAdmin);

						cTotal = numeral(parseInt(mData.billing[i].admin_charge)+nTagihan).format('0,0');
						$("#CtkTotal1").html("Rp. " + cTotal);

						cPesan = "";
						if(cSisa > 0){
							$("#CtkPesan1").html("ANDA MASIH MEMILIKI TUNGGAKAN "+cSisa+" BULAN");
						}else{
							$("#CtkPesan1").html("TERIMA KASIH");
						}

						mCodeTrans = mData.transaction_code;
						if(isCu){
							mCodeTrans = "CU-"+mData.transaction_code;
						}

						$("#CtkKode1").html(mCodeTrans);
						$("#CtkTanggal1").html(mData.transaction_date);

						var sDivText = $("#cetakRekeningPln").html();
					    var objWindow = window.open("", "", "left=0,top=0,width=1,height=1");
					    objWindow.document.write(sDivText);
					    objWindow.document.close();
					    objWindow.focus();
					    objWindow.jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
					    objWindow.jsPrintSetup.setOption('marginTop', 5);
					    objWindow.jsPrintSetup.setOption('marginBottom', 0);
					    objWindow.jsPrintSetup.setOption('marginLeft', 5);
					    objWindow.jsPrintSetup.setOption('marginRight', 0);
					    // set page header
					    objWindow.jsPrintSetup.setOption('headerStrLeft', '');
					    objWindow.jsPrintSetup.setOption('headerStrCenter', '');
					    objWindow.jsPrintSetup.setOption('headerStrRight', '');
					    // set empty page footer
					    objWindow.jsPrintSetup.setOption('footerStrLeft', '');
					    objWindow.jsPrintSetup.setOption('footerStrCenter', '');
					    objWindow.jsPrintSetup.setOption('footerStrRight', '');
					    objWindow.jsPrintSetup.setOption('printSilent', 1);

					    objWindow.jsPrintSetup.print();
					    objWindow.close();
					}
	            },

	            cetakPdambjm: function(data){
	            	for (index = data.length - 1; index >= 0; --index) {
                        idlgn = data[index]['cust_id'];
                        alamat = data[index]['alamat'];
                        abodemen = data[index]['abodemen'];
                        denda = data[index]['denda'];
                        gol = data[index]['gol'];
                        harga = data[index]['harga_air'];
                        limbah = data[index]['limbah'];
                        materai = data[index]['materai'];
                        nama = data[index]['nama'];
                        pakai = data[index]['pakai'];
                        retribusi = data[index]['retribusi'];
                        stand_i = data[index]['stand_kini'];
                        stand_l = data[index]['stand_lalu'];
                        sub_tot = data[index]['sub_total'];
                        tanggal = data[index]['transaction_date'];
                        thbln = vm.convert_Blth(data[index]['blth']);
                        total = data[index]['total'];
						diskon = data[index]['diskon'];
						totald = parseInt(data[index]['total'])+parseInt(data[index]['diskon']);
                        admin_kop = data[index]['admin'];
                        beban_tetap = data[index]['beban_tetap'];
                        biaya_meter = data[index]['biaya_meter'];
                        
                        username = data[index]['username'];
                        kode = data[index]['transaction_code'] + "/" + username + "/" + data[index]['loket_code'] + "/" + data[index]['transaction_date'];

                        $("#RekIdlgn").html(idlgn);
                        $("#RekGol").html(gol);
                        $("#RekNama").html(nama);
                        $("#RekBlth").html(thbln);
                        $("#RekAlamat").html(alamat);
                        $("#RekStAwal").html(stand_l);
                        $("#RekLimbah").html(numeral(limbah).format('0,0'));
                        $("#RekStAkhir").html(stand_i);
                        $("#RekRetribusi").html(numeral(retribusi).format('0,0'));
                        $("#RekPakai").html(pakai);
                        $("#RekDenda").html(numeral(denda).format('0,0'));
                        $("#RekHarga").html(numeral(harga).format('0,0'));
                        $("#RekSubTotal").html(numeral(sub_tot).format('0,0'));
                        $("#RekAbodemen").html(numeral(abodemen).format('0,0'));
                        $("#RekAdmin").html(numeral(admin_kop).format('0,0'));
                        $("#RekMaterai").html(numeral(materai).format('0,0'));
                        $("#RekTotal").html(numeral(total).format('0,0'));
						$("#RekDiskon").html(numeral(diskon).format('0,0'));
						$("#RekTotald").html(numeral(totald).format('0,0'));
                        $("#RekKode").html(kode);
                        $("#RekMeter").html(numeral(biaya_meter).format('0,0'));
                        $("#RekBebanTetap").html(numeral(beban_tetap).format('0,0'));

                        var sDivText = $("#cetakRekeningPdambjm").html();

		                var objWindow = window.open("", "", "left=0,top=0,width=1,height=1");
	                    objWindow.document.write(sDivText);
	                    objWindow.document.close();
	                    objWindow.focus();

	                    objWindow.jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
	                    objWindow.jsPrintSetup.setOption('marginTop', 5);
	                    objWindow.jsPrintSetup.setOption('marginBottom', 0);
	                    objWindow.jsPrintSetup.setOption('marginLeft', 5);
	                    objWindow.jsPrintSetup.setOption('marginRight', 0);
	                    // set page header
	                    objWindow.jsPrintSetup.setOption('headerStrLeft', '');
	                    objWindow.jsPrintSetup.setOption('headerStrCenter', '');
	                    objWindow.jsPrintSetup.setOption('headerStrRight', '');
	                    // set empty page footer
	                    objWindow.jsPrintSetup.setOption('footerStrLeft', '');
	                    objWindow.jsPrintSetup.setOption('footerStrCenter', '');
	                    objWindow.jsPrintSetup.setOption('footerStrRight', '');
	                    objWindow.jsPrintSetup.setOption('printSilent', 1);

	                    objWindow.jsPrintSetup.print();
	                    //objWindow.print();
	                    objWindow.close();

                    }

	            },

	            convert_Blth: function(Blth){
	                Tahun = Blth.trim().substr(0,4);
	                Bulan = parseInt(Blth.trim().substr(4,2));

	                namaBulan = "";
	                BulanTahun = "";
	                switch(Bulan){
	                    case 1:
	                        namaBulan = "Januari";
	                        break;
	                    case 2:
	                        namaBulan = "Februari";
	                        break;
	                    case 3:
	                        namaBulan = "Maret";
	                        break;
	                    case 4:
	                        namaBulan = "April";
	                        break;
	                    case 5:
	                        namaBulan = "Mei";
	                        break;
	                    case 6:
	                        namaBulan = "Juni";
	                        break;
	                    case 7:
	                        namaBulan = "Juli";
	                        break;
	                    case 8:
	                        namaBulan = "Agustus";
	                        break;
	                    case 9:
	                        namaBulan = "September";
	                        break;
	                    case 10:
	                        namaBulan = "Oktober";
	                        break;
	                    case 11:
	                        namaBulan = "Nopember";
	                        break;
	                    case 12:
	                        namaBulan = "Desember";
	                        break;
	                    default:
	                        namaBulan = "";
	                }
	                BulanTahun = namaBulan + " " + Tahun;
	                return BulanTahun;
	            },
			}
		});
	});
</script>

@endsection