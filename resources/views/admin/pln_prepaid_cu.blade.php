@extends('...layouts/template')

@section('content')
<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}
</style>

<section class="content-header">
  <h1>
    Dashboard
    <small>Halaman Pedami Payment</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Admin</a></li>
    <li class="active">Cetak PLN Prepaid</li>
  </ol>
</section>

<!-- Main content -->
<section class="content" id="elCU">
<div class="box box-primary" style="min-height: 300px">
    <div class="box-header">
      <h3 class="box-title">CHECK 10 TRANSAKSI TERAKHIR PLN PREPAID</h3>
    </div>
    <div class="box-body">

        <div class='form-group'>
            <label for='status_setuju' >IDPEL / NOMOR METER</label>
            <div class="row">
                <div class="col-md-3">
                    <input type='text' maxlength="15" v-model='idpel' class='form-control' id='idpel' placeholder='Masukan IDPEL / NOMOR METER'>
                </div>
                <div class="col-md-4">
                     <button type="button" class="btn btn-primary" @click="prosesBil" data-dismiss="modal">PROSES</button>
                </div>
            </div>
            
        </div>
        
        <div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped dataTable" id="listData">
            <thead>
                <tr>
                    <th style='min-width: 50px'>AKSI</th>
                    <th style='min-width: 80px'>IDPEL</th>
                    <th style='min-width: 80px'>NOMOR METER</th>
                    <th style='min-width: 150px'>NAMA</th>
                    <th style='min-width: 100px'>TGL TRANSAKSI</th>
                    <th style='min-width: 100px'>RP TOKEN</th>
                    <th style='min-width: 200px'>NOMOR TOKEN</th>
                </tr>
            </thead>                
            <tbody>
                <tr v-for="token in listToken">
                    <td><button type="button" @click="cetakBil(token.id)" class="btn btn-primary">CETAK</button></td>
                    <td>@{{ token.subscriber_id }}</td>
                    <td>@{{ token.material_number }}</td>
                    <td>@{{ token.subscriber_name }}</td>
                    <td>@{{ token.transaction_date }}</td>
                    <td>@{{ token.rupiah_token | currency('',0) }}</td>
                    <td><i>@{{ token.token_number }}</i></td>
                </tr>
            </tbody>
        </table>
        </div>
 
    </div>
</div>

@include('...cetakan/pln_prepaid')

</section>

<script>
$(document).ready(function() {

    var vmCU = new Vue({
        el: '#elCU',
        data: {
            jmlToken: 0,
            idpel: '',
            listToken: []
        },

        mounted() {
            
        },

        methods: {
            cetakBil: function (id) {
                console.log(this.listToken);
                for(i=0;i<this.listToken.length;i++){
                    console.log(this.listToken[i]);
                    if(this.listToken[i].id == id){
                        this.cetakRekening(this.listToken[i], true);
                    }
                }
            },

            prosesBil: function () {
                this.$http.get("{{ secure_url('api/pln/prepaid/cetak_ulang') }}/"+vmCU.idpel).then(response => {
                    console.log(response.body);

                    if(response.body.status){
                        vmCU.listToken = response.body.data;
                    }else{
                        alert("Data tidak ditemukan.");
                    }

                }, response => {
                    
                });
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
            }
        }

    });

});
</script>
@endsection