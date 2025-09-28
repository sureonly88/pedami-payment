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
    <li class="active">Listik PLN</li>
  </ol>
</section>

<!-- Main content -->
<section class="content" id="elPln">

<div class="row">

	<div class="col-md-12">
		<div class="box box-primary">
		    <div class="box-body" >

		    <div class="row">
		    	<div class="col-md-3">
			        <label>IDPEL / NOMOR METER</label>

			        <table width="100%">
			        	<tr>
			        		<td><input id="nomor_pln" maxlength="12" type="text" @keyup.enter="prosesPln()" v-model="nopelanggan" placeholder="1122334455" class="form-control" /></td>
			        	</tr>
			        </table>
			        
		        </div>
		    </div>

	    	<div style="width:100%;overflow:auto;">
			<table id="dataTable" class="table table-bordered table-hover table-striped dataTable">
				<thead>
				<tr>
					<th style="min-width:50px">AKSI</th>
					<th style="min-width:100px">NOMOR METER</th>
<!-- 					<th style="min-width:150px">NOMOR PELANGGAN</th> -->
					<th style="min-width:150px">NAMA PELANGGAN</th>
					<th style="min-width:100px">TARIF / DAYA</th>
                    <th style="min-width:100px">TOKEN UNSOLD</th>
                    <th style="min-width:100px">ADMIN CHARGE</th>
                    <th style="min-width:100px">TOTAL BAYAR</th>
				</tr>
				</thead>
				<tbody id="dataPLN">
					<tr v-for="bill in bill_data">
						<td><button :disabled="disabledBayar" type="button" @click="deleteBil(bill.data.subcriber_id)" class="btn btn-primary">Batal</button>
						<td>@{{ bill.data.material_number }}</td>
				<!-- 		<td>@{{ bill.data.subscriber_id }}</td> -->
						<td>@{{ bill.data.subscriber_name }}</td>
						<td>@{{ bill.data.subscriber_segment }} / @{{ bill.data.power_categori }}</td>
						<td>@{{ bill.data.purchase_unsold }}</td>
						<td>@{{ bill.data.admin_charge | currency('',0) }}</td>
						<td>@{{ TotalTagihan }}</td>
					</tr>
				</tbody>
			</table>
			</div>
			<hr/>
          <div class="row">
                <div class="col-md-6">
		          <div class="form-group">
						<label for="total" style="font-size: x-large" class="control-label">PILIH TOKEN</label>

						<div class="row">
							<div class="col-md-4">
								<select :disabled="disabledBayar" class="form-control" id="pilTokenUnsold" @change="changeToken()" v-model="pilToken" style="font-size: x-large; height: 50px">
									<option value="0">Token Baru</option>
									<option value="1" v-show="isUnsold">Token Unsold</option>
								</select>
							</div>

							<div class="col-md-8">
								<select :disabled="disabledBayar" class="form-control" v-model="jmlToken" id="pilToken" style="font-size: x-large; height: 50px" >
									<option v-for="Token in listToken" :value="Token.nilai">@{{ Token.nama }}</option>
<!-- 									<option value="0">Pilih Token</option>
									<option value="20000">Rp 20.000</option>
									<option value="50000">Rp 50.000</option>
									<option value="100000">Rp 100.000</option>
									<option value="200000">Rp 200.000</option>
									<option value="500000">Rp 500.000</option>
									<option value="1000000">Rp 1.000.000</option>
									<option value="5000000">Rp 5.000.000</option> -->
								</select>
							</div>
						</div>
						
					</div>
					<div class="form-group" >
						<label for="total_bayar" style="font-size: x-large" class="control-label">TOTAL BAYAR</label>
						<input id="total_bayar" v-model="TotalTagihan" style="text-align: right; font-size: x-large; height: 50px" readonly="" placeholder="Total Bayar" class="form-control" type="text">
					</div>
					<div class="form-group" > 
						<label for="bayar" style="font-size: x-large" class="control-label">BAYAR</label>
						<input id="bayar" :disabled="disabledBayar" @keyup.enter="prosesPayment()" v-model="jmlBayar" style="text-align: right; font-size: x-large; height: 50px" placeholder="Rupiah Bayar" class="form-control" type="text">
					</div>
					<div class="form-group" >
						<label for="kembalian" style="font-size: x-large" class="control-label">KEMBALIAN</label>
						<input id="kembalian" v-model="Kembalian" style="text-align: right; font-size: x-large; height: 50px" readonly="" placeholder="Rupiah Kembalian" class="form-control" type="text">
					</div>


				</div>

				<div class="col-md-6">
					<div class="panel panel-default" v-show="showMessage">

		            <div class="panel-body">

		                <div v-html="pesanSpan" > </div>
		                <!-- <span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b></b></span> -->
		                </div>
		            </div>
				</div>

			</div>

			<button type="button" @click="cetakUlang()" class="btn btn-primary">
                    Cetak Ulang</button>

		</div>

	</div>


	</div>
</div>

@include('...modals/modalPrepaid')
@include('...cetakan/pln_prepaid')

</section>

<script>
$(document).ready(function() {

	var vmPln = new Vue({
		el: '#elPln',
		data: {
			jmlToken: 0,
			pilToken: 0,
			jmlBayar: 0,
			nopelanggan: '',
			ulangIdpel: '',
			pesanSpan: '',
			disabledNoPel: false,
			disabledBayar: false,
			bill_data: [],
			listToken: [],
		},

		mounted() {
	        $("#nomor_pln").focus();
	        this.changeToken();
	    },

		computed: {

		    Kembalian: function () {
		    	vTotal = 0;
		    	if(this.bill_data.length > 0){
		    		vTotal = parseInt(this.bill_data[0].data.admin_charge) + parseInt(this.jmlToken);
		    	}
		    	
		    	nKembalian = this.jmlBayar -vTotal;
		    	return numeral(nKembalian).format('0,0');
		    },

		    TotalTagihan: function () {
		    	vTotal = 0;
		    	if(this.bill_data.length > 0){
		    		vTotal = parseInt(this.bill_data[0].data.admin_charge) + parseInt(this.jmlToken);
		    	}

		    	return numeral(vTotal).format('0,0');
		    },

			showMessage: function(){
		    	if(this.pesanSpan.length > 0){
		    		return true;
		    	}else{
		    		return false;
		    	}
		    },

		    isUnsold: function(){
		    	if(this.bill_data.length > 0){
		    		listUnsold = this.bill_data[0].data.purchase_unsold.trim();
		    		//console.log(listUnsold);
		    		if(listUnsold.length > 0){
		    			return true;
		    		}else{
		    			return false;
		    		}
		    	}else{
		    		return false;
		    	}
		    }

		},

		methods: {

			changeToken: function (){

				if(this.pilToken == 0){
					Token = [
						{"nama" : "Pilih Token", "nilai": 0},
						{"nama" : "Rp 20.000", "nilai": 20000},
						{"nama" : "Rp 50.000", "nilai": 50000},
						{"nama" : "Rp 100.000", "nilai": 100000},
						{"nama" : "Rp 200.000", "nilai": 200000},
						{"nama" : "Rp 500.000", "nilai": 500000},
						{"nama" : "Rp 1.000.000", "nilai": 1000000},
						{"nama" : "Rp 5.000.000", "nilai": 5000000},
						{"nama" : "Rp 10.000.000", "nilai": 10000000},
						{"nama" : "Rp 50.000.000", "nilai": 50000000}
					];
					this.listToken = Token;
				}else{
					if(this.bill_data.length > 0){
						//console.log(this.bill_data[0].data);

						listUnsold = this.bill_data[0].data.purchase_unsold.split(",");

						unSoldToken = [{"nama" : "Pilih Token", "nilai": 0}];
						for(i=0;i<listUnsold.length; i++){
							namaToken = "Rp. " + numeral(listUnsold[i]).format('0,0');
							unSold = { "nama" : namaToken, "nilai": listUnsold[i] };
							unSoldToken.push(unSold);
						}
						//console.log(unSoldToken);
						this.listToken = unSoldToken;
					}else{
						this.listToken = [{"nama" : "Pilih Token", "nilai": 0}];
					}
				}
			},

			cetakUlang: function () {
				//$('#cetakDialog').modal("show");
    			//$("#ulangIdpel").focus();

				window.open(
				  "{{ secure_url('/admin/pln_prepaid_cu') }}",
				  "_blank" // <- This is what makes it open in a new window.
				);


			},

			ProsesCetakUlang: function() {
				this.$http.get("{{ secure_url('api/pln/prepaid/cetak_ulang') }}/"+vmPln.ulangIdpel).then(response => {

					console.log(response.body);

					if(response.body.status){
						mData = response.body.data;

						for(i=0;i<mData.length; i++){
							vmPln.cetakRekening(mData[i], true);
						}
						
					}else{
						alert(response.body.message);
					}
					
				}, response => {
	            	console.log("Error...");
	            });
			},

			cekIdExist: function () {
				if(vmPln.bill_data.length > 0){
					return true;
				}else{
					return false;
				}
				
			},

			deleteBil: function (subcriber_id){
				//console.log(subcriber_id);

				for(i=0; i<vmPln.bill_data.length; i++){
					if(subcriber_id == vmPln.bill_data[i].data.subcriber_id){
						vmPln.bill_data.splice(i, 1);
					}
				}
			},

			cetakRekening: function(mData, isCu) {

				$("#CtkMeter").html(mData.material_number);
				$("#CtkAdmin").html(numeral(mData.admin_charge).format('0,0'));
				$("#CtkIdpel").html(mData.subscriber_id);

				cMaterai = numeral(mData.stump_duty).format('0,0.00');
				$("#CtkMaterai").html(cMaterai);

				$("#CtkNama").html(mData.subscriber_name);

				cPpn = numeral(mData.addtax).format('0,0.00');
				$("#CtkPpn").html(cPpn);

				cTarif = mData.subscriber_segment + "/" + parseInt(mData.power_categori);
				$("#CtkTarif").html(cTarif);

				cPpj = numeral(mData.ligthingtax).format('0,0.00');
				$("#CtkPpj").html(cPpj);
				$("#CtkRef").html(mData.switcher_ref_number);

				cAngsuran = numeral(mData.cust_payable).format('0,0.00');
				$("#CtkAngsuran").html(cAngsuran);

				//cBayar = parseInt(mData.rupiah_token) + parseInt(mData.admin_charge);
				cBayar = parseInt(mData.stump_duty) + parseInt(mData.addtax) + parseInt(mData.ligthingtax) + parseInt(mData.cust_payable) + parseInt(mData.admin_charge) + parseInt(mData.power_purchase);
				cBayar = numeral(cBayar).format('0,0');
				$("#CtkBayar").html(cBayar);

				cPowerPurchase = numeral(mData.power_purchase).format('0,0.00');
				$("#CtkStroom").html(cPowerPurchase);

				purchase_kwhRound = ""+mData.purchase_kwh;
				purchase_kwhSplit = purchase_kwhRound.split(".");

				if(purchase_kwhSplit.length > 1){
					if(purchase_kwhSplit[1].length == 2){
						purchase_kwhRound = purchase_kwhRound.substr(0,purchase_kwhRound.length-1);
					}	
				}

				$("#CtkKwh").html(numeral(purchase_kwhRound).format('0,0.0'));
				$("#CtkToken").html(mData.token_number);
				$("#CtkInfo").html(mData.info_text);

				mCodeTrans = mData.transaction_code;
				if(isCu){
					mCodeTrans = "CU-"+mData.transaction_code;
				}

				$("#CtkKode").html(mCodeTrans);
				$("#CtkTanggal").html(mData.transaction_date);

				var sDivText = $("#cetakRekening").html();
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

			prosesPln: function () {

				if(vmPln.disabledNoPel){
					//vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>MASIH MEMPROSES...</b></span>";
					return;
				}
				vmPln.disabledNoPel = true;

				if(vmPln.cekIdExist()){
					vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>HANYA BISA TRANSAKSI PER 1 PELANGGAN.</b></span>";
					vmPln.disabledNoPel = false;
					return;
				}

				if(vmPln.nopelanggan.length < 11 || vmPln.nopelanggan.length > 12){
					vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>NO PELANGGAN TIDAK VALID.</b></span>";
					vmPln.disabledNoPel = false;
					return;
				}

				vmPln.pesanSpan = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b>PENGECEKAN TAGIHAN PELANGGAN...</b></span>";

				this.$http.get("{{ secure_url('api/pln/prepaid/request') }}/"+vmPln.nopelanggan).then(response => {
					console.log(response.body);

					if(response.body.status){
						vmPln.bill_data.push(response.body.customer);
						vmPln.nopelanggan = "";
		              	vmPln.pesanSpan = "";
					}else{
						vmPln.pesanSpan += "<br/>" + "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span>";
					}

					vmPln.disabledNoPel = false;
					$("#nomor_pln").focus();

	            }, response => {
	            	vmPln.pesanSpan += "<br/>" + "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>UNKNOWN ERROR</b></span>";
	            	vmPln.disabledNoPel = false;
	            });
					
			},

			prosesPayment: function () {

				if(vmPln.disabledBayar){
					//vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>MASIH MEMPROSES...</b></span>";
					return;
				}

				vmPln.disabledNoPel = true;
				vmPln.disabledBayar = true;

				vmPln.pesanSpan = "";

				var q = $.jqmq({
					delay: -1,
      				callback: function(mData) {
      					var q = this;

      					vmPln.pesanSpan += "<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES PEMBAYARAN NO.PEL : "+mData.data.subscriber_id+" A/N "+mData.data.subscriber_name+"</b></span><br/>";

      					vmPln.$http.post("{{ secure_url('api/pln/prepaid/purchase') }}", {idpel: mData.data.subscriber_id, payment_message: mData.purchase_message, rupiah_token: vmPln.jmlToken, buying_option: vmPln.pilToken, _token: "{{ csrf_token() }}" }).then(response => {
      						
      						//console.log(response.body);

      						if(response.body.status){
	      						text_color = "text-green";
	      						if(response.body.response_code != "0000"){
	      							text_color = "text-red";
	      						}else{
	      							vmPln.cetakRekening(response.body.customer, false);
	      						}

	      						vmPln.pesanSpan += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

	      						q.next();
      						}else{
      							vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/><br/>";

      							//ADVICE ULANG PEMBELIAN TOKEN
      							//BILA RESPONSE PAYMENT BUKAN 9990 - PULSA TIDAK CUKUP
      							if(response.body.response_code != '9990' && response.body.response_code != '0013' && response.body.response_code != '0046'){
      								vmPln.pesanSpan +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK ULANG PEMBELIAN KE 1</b></span><br/><br/>";

      								//PROSES PENGULANGAN PURCHASE KE-1
	      							setTimeout(function(){

	      								vmPln.pesanSpan +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES ULANG PEMBELIAN KE 1...</b></span><br/>";

	      								vmPln.$http.post("{{ secure_url('api/pln/prepaid/advise') }}", {
	      									idpel: mData.data.subscriber_id, 
	      									reversal_message: mData.reversal_message, 
	      									rupiah_token: vmPln.jmlToken, 
	      									counter:1,
	      									_token: "{{ csrf_token() }}" }).then(response => {

	      									//console.log(response.body);

	      									if(response.body.status){
	      										text_color = "text-green";
		      										if(response.body.response_code != "0000"){
		      											text_color = "text-red";
		      										}else{
		      											vmPln.cetakRekening(response.body.customer, false);
		      										}

		      									vmPln.pesanSpan += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

		      									q.next();
	      									}else{
	      										vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/><br/>";

	      										if(response.body.response_code != '0063'){
	      											vmPln.pesanSpan +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK ULANG PEMBELIAN KE 2</b></span><br/><br/>";

		      										//PROSES PENGULANGAN PURCHASE KE-2
		      										setTimeout(function(){

		      											vmPln.pesanSpan +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES ULANG PEMBELIAN KE 2...</b></span><br/>";

					      								vmPln.$http.post("{{ secure_url('api/pln/prepaid/advise') }}", {
					      									idpel: mData.data.subscriber_id, 
					      									reversal_message: mData.reversal_message, 
					      									rupiah_token: vmPln.jmlToken, 
					      									counter:2,
					      									_token: "{{ csrf_token() }}" }).then(response => {

					      									//console.log(response.body);

					      									if(response.body.status){
					      										text_color = "text-green";
						      										if(response.body.response_code != "0000"){
						      											text_color = "text-red";
						      										}else{
						      											vmPln.cetakRekening(response.body.customer, false);
						      										}

						      									vmPln.pesanSpan += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message+"</b></span><br/><br/>";

						      									q.next();
					      									}else{
					      										//SELESAI GAK ADA PERULANGAN ADVISE LAGI
					      										vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</span></b><br/>";

					      										vmPln.pesanSpan += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES ULANG PEMBELIAN BISA DILAKUKAN DI MENU MANUAL PLN PRABAYAR</span></b><br/><br/>";

					    
					      										q.next()

					      									}

					      								}, response => {
					      									vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES ADVISE ERROR</span></b><br/><br/>";
					      									q.next();
														});
					      									

					      							}, 35000);
		      										//AKHIR PROSES PENGULANGAN PURCHASE KE-2
	      										}else{
	      											q.next();
	      										}
	      									}

	      								}, response => {
	      									vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES ADVISE ERROR</span></b><br/><br/>";
	      									q.next();
										});
	      									

	      							}, 35000);
	      							//AKHIR PROSES PENGULANGAN PURCHASE KE-1
      							}else{
      								q.next();
      							}
      						}

      					}, response => {
      						vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES PEMBAYARAN ERROR</span></b><br/><br/>";
      						q.next();
						});
					},
					complete: function(){

						vmPln.disabledNoPel = false;
						vmPln.disabledBayar = false;

						vmPln.pesanSpan += "<span><i class='fa fa-hourglass-end'></i>&nbsp;&nbsp;&nbsp;<b class='text-green'>PROSES SELESAI.</b></span><hr/>";

						vmPln.bill_data = [];
						vmPln.changeToken();

						$("#nomor_pln").focus();

						RefreshSaldo();
				    }
			    });

			    $.ajaxSetup({ cache: false });

				for (index = 0; index < vmPln.bill_data.length; ++index) {
				    q.add(vmPln.bill_data[index]);
				}
				
			}
		}

    });

});
</script>

@endsection