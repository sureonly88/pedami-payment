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
			        <label>NOMOR PELANGGAN</label>

			        <table width="100%">
			        	<tr>
			        		<td><input id="nomor_pln" maxlength="12" type="text" @keyup.enter="prosesPln()" v-model="nopelanggan" placeholder="1122334455" class="form-control" /></td>
			        		<!-- <td>&nbsp;<button type="button" class="btn btn-primary" @click="prosesPln()">Proses</button></td> -->
			        	</tr>
			        </table>
			       
			        
		        </div>
		    </div>

	    	<div style="width:100%;overflow:auto;">
			<table id="dataTable" class="table table-bordered table-hover table-striped dataTable">
				<thead>
				<tr>
					<th style="min-width:120px">AKSI</th>
					<th style="min-width:100px">IDPEL</th>
					<th style="min-width:150px">NAMA PELANGGAN</th>
					<th style="min-width:100px">KLASIFIKASI</th>
					<th style="min-width:180px">TOTAL LEMBAR TAGIHAN</th>
                    <th style="min-width:150px">BULAN TAHUN TAGIHAN</th>
                    <th style="min-width:100px">RP TAG PLN</th>
                    <th style="min-width:100px">ADMIN BANK</th>
					<th style="min-width:100px">TOTAL TAGIHAN</th>
					<th style="min-width:100px">SISA BILL</th>		
				</tr>
				</thead>
				<tbody id="dataPLN">
					<tr v-for="bill in bill_data">
						<td><button :disabled="disabledBayar" type="button" @click="deleteBil(bill.subcriber_id)" class="btn btn-primary">Batal</button>&nbsp;<button :disabled="disabledBayar" type="button" class="btn btn-primary">Rinci</button></td>
						<td>@{{ bill.subcriber_id }}</td>
						<td>@{{ bill.subcriber_name }}</td>
						<td>@{{ bill.subcriber_segment }} / @{{ bill.power_consumtion }}</td>
						<td>@{{ bill.outstanding_bill }}</td>
						<td>@{{ bill.periode }}</td>
						<td>@{{ bill.total_pln | currency('',0) }}</td>
						<td>@{{ bill.total_admin_charge | currency('',0) }}</td>
						<td>@{{ bill.total_tagihan | currency('',0) }}</td>
						<td>@{{ bill.sisa_bill | currency('',0) }}</td>
					</tr>
				</tbody>
			</table>
			</div>
			<hr/>
          <div class="row">
                <div class="col-md-6">
		          <div class="form-group">
						<label for="total" style="font-size: x-large" class="control-label">TOTAL BAYAR</label>
						<input id="total" v-model="totalTagihan" readonly="" style="text-align: right; font-size: x-large; height: 50px" placeholder="Total Bayar" class="form-control" type="text">
					</div>
					<div class="form-group" > 
						<label for="bayar" style="font-size: x-large" class="control-label">BAYAR</label>
						<input id="bayar" :disabled="disabledBayar" v-model="jmlBayar" @keyup.enter="prosesPayment()" style="text-align: right; font-size: x-large; height: 50px" placeholder="Rupiah Bayar" class="form-control" type="text">
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

@include('...modals/modalPaymentPostPaid')
@include('...cetakan/pln_postpaid')
</section>

<script>
$(document).ready(function() {

	var vmPln = new Vue({
		el: '#elPln',
		data: {
			isResponse: true,
			isSuccess: true,
			isFailed: true,
			isBtnClose: false,
			jmlBayar: 0,
			nopelanggan: '',
			ulangIdpel: '',
			pesanSpan: '',
			disabledNoPel: false,
			disabledBayar: false,
			bill_data: []
		},

		mounted() {
	        $("#nomor_pln").focus();
	        $("#txtTglAwal").datepicker({ dateFormat: 'yy-mm-dd' }); 
	        $("#txtTglAkhir").datepicker({ dateFormat: 'yy-mm-dd' }); 
	    },

		computed: {
		    totalTagihan: function () {
		    	totalTagih = 0;

	    		if(this.bill_data.length > 0){
				    for(i=0; i<this.bill_data.length; i++){
				    	totalTagih += parseInt(this.bill_data[i].total_tagihan);
				    }
				}
		    	return numeral(totalTagih).format('0,0');
		    },

		    Kembalian: function () {
		    	nTagihan = parseInt(this.totalTagihan.replace(/,/g,""));
		    	nKembalian = this.jmlBayar - nTagihan;
		    	return numeral(nKembalian).format('0,0');
		    },

		    showMessage: function(){
		    	if(this.pesanSpan.length > 0){
		    		return true;
		    	}else{
		    		return false;
		    	}
		    }
		},

		methods: {

			cetakUlang: function () {
				$('#cetakDialog').modal("show");
    			$("#ulangIdpel").focus();
			},

			ProsesCetakUlang: function() {
				var tglAwal = $("#txtTglAwal").val();
				var tglAkhir = $("#txtTglAkhir").val();

				this.$http.get("{{ secure_url('api/pln/postpaid/cetak_ulang') }}/"+vmPln.ulangIdpel+"/"+tglAwal+"/"+tglAkhir).then(response => {
					if(response.body.status){
						mData = response.body.data;
						//mData.switcher_ref = "CU-"+mData.switcher_ref;
						vmPln.cetakRekening(mData, true);
					}else{
						alert(response.body.message);
					}
					
				}, response => {
	            	
	            });
			},

			cekIdExist: function () {
				for(i=0; i<vmPln.bill_data.length; i++){
					if(vmPln.nopelanggan == vmPln.bill_data[i].subcriber_id){
						return true;
					}
				}
				return false;
			},

			pesanDialog: function(isiPesan){
				$("#isiPesan").html(isiPesan);
				$("#modalPesan").modal("show");
			},

			deleteBil: function (subcriber_id){
				//console.log(subcriber_id);

				for(i=0; i<vmPln.bill_data.length; i++){
					if(subcriber_id == vmPln.bill_data[i].subcriber_id){
						vmPln.bill_data.splice(i, 1);
					}
				}
			},

			cetakRekening: function(mData, isCu) {

				for(i=0; i<mData.billing.length; i++){

					$("#CtkIdpel").html(mData.subcriber_id);
					$("#CtkNama").html(mData.subcriber_name);
					$("#CtkPeriode").html(mData.billing[i].bill_periode);

					//cSisa = parseInt(mData.outstanding_bill) - parseInt(mData.bill_status);
					cSisa = parseInt(mData.billing[i].outstanding_bill) - parseInt(mData.billing[i].bill_status);

					cStand = mData.billing[i].prev_meter_read_1 + "-"+mData.billing[i].curr_meter_read_1;
					$("#CtkStand").html(cStand);
					cTarif = mData.subcriber_segment + "/" + parseInt(mData.power_consumtion);
					$("#CtkTarif").html(cTarif);

					nTagihan = parseInt(mData.billing[i].total_elec_bill)+parseInt(mData.billing[i].penalty_fee);

					cTagihan = numeral(parseInt(mData.billing[i].total_elec_bill)+parseInt(mData.billing[i].penalty_fee)).format('0,0');
					$("#CtkTagihan").html("Rp. " + cTagihan);
					$("#CtkRef").html(mData.switcher_ref);

					cAdmin = numeral(parseInt(mData.billing[i].admin_charge)).format('0,0');
					$("#CtkAdmin").html("Rp. " + cAdmin);

					cTotal = numeral(parseInt(mData.billing[i].admin_charge)+nTagihan).format('0,0');
					$("#CtkTotal").html("Rp. " + cTotal);

					cPesan = "";
					if(cSisa > 0){
						$("#CtkPesan").html("ANDA MASIH MEMILIKI TUNGGAKAN "+cSisa+" BULAN");
					}else{
						$("#CtkPesan").html("TERIMA KASIH");
					}

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
				}
			},

			prosesPln: function () {
				
				if(vmPln.disabledNoPel){
					console.log("Prevent.");
					return;
				}

				vmPln.disabledNoPel = true;

				if(vmPln.nopelanggan.length == 0){
					if(vmPln.bill_data.length > 0){
						vmPln.disabledNoPel = false;
						$("#bayar").focus();
						$("#bayar").select();
						return;
					}
				}

				if(vmPln.nopelanggan.length != 12){
					vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>NO PELANGGAN TIDAK VALID.</b></span>";
					vmPln.disabledNoPel = false;
					return;
				}

				if(vmPln.cekIdExist()){
					vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>NO PELANGGAN SUDAH ADA.</b></span>";
					vmPln.disabledNoPel = false;
					return;
				}

				//$('#modalLoading').modal("show");
				vmPln.pesanSpan = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b>PENGECEKAN TAGIHAN PELANGGAN...</b></span>";

				this.$http.get("{{ secure_url('api/pln/postpaid/request') }}/"+vmPln.nopelanggan).then(response => {
				  //$('#modalLoading').modal("hide");

				  //console.log(response.body);

	              if(response.body.status){

	              	vmPln.bill_data.push(response.body.customer);
	              	vmPln.nopelanggan = "";
	              	vmPln.disabledNoPel = false;
	              	vmPln.pesanSpan = "";

	              	$("#nomor_pln").focus();

	              }else{
	              	vmPln.pesanSpan += "<br/>" + "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span>";
	              	vmPln.disabledNoPel = false;
	              	$("#nomor_pln").focus();
	              }

	            }, response => {
	            	//$('#modalLoading').modal("hide");
	              	vmPln.pesanSpan += "<br/>" + "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>UNKNOWN ERROR</b></span>";
	              	vmPln.disabledNoPel = false;
	              	$("#nomor_pln").focus();
	            });
					
			},

			prosesPayment: function () {

				if(vmPln.disabledBayar){
					$.notify({
                      icon: 'fa fa-warning',
                      title: "<strong>Prevent</strong> : ",
                      message: "Proses Payment Belum Selesai."
                    },{
                      type: 'warning'
                    });
					return;
				}

				vmPln.disabledNoPel = true;
				vmPln.disabledBayar = true;

				jumBayar = parseInt(vmPln.Kembalian.replace(/,/g,"",-1));
				if(jumBayar < 0){
					vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>UANG TIDAK CUKUP.</b></span>";
					vmPln.disabledNoPel = false;
					vmPln.disabledBayar = false;

					$("#bayar").focus();
					return;
				}
				
				if(vmPln.bill_data.length <= 0){
					vmPln.pesanSpan = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b>TIDAK ADA DATA YANG DIPROSES.</b></span>";
					vmPln.disabledNoPel = false;
					vmPln.disabledBayar = false;

					$("#nomor_pln").focus();
					return;
				}

				//$('#modalProsesBayar').modal("show");
				//$('#isiProsesBayar').html("");
				//vmPln.isBtnClose = false;
				vmPln.pesanSpan = "";

				var q = $.jqmq({
					delay: -1,
      				callback: function(mData) {
      					var q = this;

      					vmPln.pesanSpan += "<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES PEMBAYARAN NO.PEL : "+mData.subcriber_id+" A/N "+mData.subcriber_name+"</b></span><br/>";

      					nTotalBayar = parseInt(vmPln.totalTagihan.replace(/,/g,"",-1));
      					vmPln.$http.post("{{ secure_url('api/pln/postpaid/payment') }}", {subcriber_id: mData.subcriber_id, payment_message: mData.payment_message, total_bayar: nTotalBayar, _token: "{{ csrf_token() }}" }).then(response => {

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

      							//BILA RESPONSE PAYMENT BUKAN 0088 - BILL ALREADY PAID LAKUKAN REVERSAL
      							//BILA RESPONSE PAYMENT BUKAN 9990 - PULSA TIDAK CUKUP
      							if(response.body.response_code != '0088' && response.body.response_code != '9990' && response.body.response_code != '0046'){

      								vmPln.pesanSpan +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK PROSES PEMBATALAN KE 1</b></span><br/><br/>";

	      							setTimeout(function(){

	      								//DO FIRST REVERSAL
		      							vmPln.pesanSpan +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES REVERSAL / PEMBATALAN KE 1...</b></span><br/>";

			      						vmPln.$http.post("{{ secure_url('api/pln/postpaid/reversal') }}", {
			      							subcriber_id: mData.subcriber_id, 
			      							payment_message: mData.reversal_message,
			      							number_request:1,
			      							_token: "{{ csrf_token() }}" }).then(response => {

			      							//console.log(response.body);

			      							vmPln.pesanSpan +=	"<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span><br/><br/>";

			      							response_code = response.body.response_code;
			      							//response_code = "0012";
			      							if(response_code == "0012"){
			      								vmPln.cetakRekening(response.body.customer, false);
			      							}

			      							//BILA RESPONSE CODE REVERSAL BUKAN 0000 DAN 0063 REQUEST REVERSAL LAGI. 
			      							if(response_code != "0000" && response_code != "0063" && response_code != "0012" && response_code != "0088" && response_code != "0094"){

			      								vmPln.pesanSpan +=	"<span><i class='fa fa-hourglass'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>TUNGGU 35 DETIK UNTUK PROSES PEMBATALAN KE 2</b></span><br/><br/>";

			      								setTimeout(function(){

			      									vmPln.pesanSpan +=	"<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES REVERSAL / PEMBATALAN KE 2...</b></span><br/>";
				      								//DO SECOND REVERSAL
				      								vmPln.$http.post("{{ secure_url('api/pln/postpaid/reversal') }}", {
						      							subcriber_id: mData.subcriber_id, 
						      							payment_message: mData.reversal_message,
						      							number_request:2,
						      							_token: "{{ csrf_token() }}" }).then(response => {

						      								//console.log(response.body);

						      								vmPln.pesanSpan +=	"<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message+"</b></span><br/><br/>";
						      								q.next();
									      				}, response => {
									      					vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES REVERSAL 2 ERROR</span></b><br/><br/>";
			      											q.next();
							      					});
			      								}, 35000);
			      									
			      								//END DO SECOND REVERSAL
			      							}else{
			      								q.next();
			      							}
			      						}, response => {
			      							vmPln.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES REVERSAL 1 ERROR</span></b><br/><br/>";
		      								q.next();
			      						});
			      						//END DO FIRST REVERSAL
	      							}, 35000);

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

      					vmPln.pesanSpan += "<div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>TOTAL BAYAR</b></div><div class='col-md-6'><b>: Rp. "+vmPln.totalTagihan+"</b></div></div><div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>BAYAR</b></div><div class='col-md-6'><b>: Rp. "+numeral(vmPln.jmlBayar).format('0,0')+"</b></div></div><div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>KEMBALIAN</b></div><div class='col-md-6'><b>: Rp. "+vmPln.Kembalian+"</b></div></div><br/>";

      					vmPln.bill_data = [];
      					vmPln.jmlBayar = 0;

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
    
 //    $('#jenis_listrik').on('select2:select', function (event) {
 //    	var value = $(event.currentTarget).find("option:selected").val();
 //    	if(value == "TOKEN"){
 //    		vmPln.isPostpaid = false;
 //    		vmPln.isNominal = true;
 //    	}else{
 //    		vmPln.isPostpaid = true;
 //    		vmPln.isNominal = false;
 //    	}
			
	// });
});
</script>

@endsection