<?php
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
      <h3 class="box-title">TRANSAKSI GANTUNG PDAM & PLN LUNASIN</h3>
    </div>
    <div class="box-body">

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" class="form-control" value="<?php echo date("Y-m-d"); ?>" v-model="filterTanggal" id="tanggal">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="produk">Produk</label>
                    <select class="form-control" v-model="filterProduk" id="produk">
                        <option value="">Semua Produk</option>
                        <option value="PDAMBJM">PDAMBJM</option>
                        <option value="PLN_POSTPAID">PLN POSTPAID</option>
                        <option value="PLN_PREPAID">PLN PREPAID</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="button" @click="prosesBil(1)" class="btn btn-primary">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <button type="button" @click="resetFilter()" class="btn btn-default">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div style="width:100%;overflow:auto;">
        <table class="table table-bordered table-hover table-striped" id="listData">
            <thead>
                <tr>
                    <th style='min-width: 50px'>AKSI</th>
                    <th style='min-width: 100px'>ID TRANSAKSI</th>
                    <th style='min-width: 100px'>PRODUK</th>
                    <th style='min-width: 80px'>USERNAME</th>
                    <th style='min-width: 100px'>TANGGAL</th>
                </tr>
            </thead>                
            <tbody>
                <tr v-if="listAdvise.length === 0">
                    <td colspan="5" class="text-center">Tidak ada data</td>
                </tr>
                <tr v-for="advise in listAdvise">
                    <td>
                        <button type="button" @click="prosesAdvise('',advise.produk,advise.denom,advise.idtrx,advise.advise_message)" class="btn btn-primary">Proses</button> &nbsp;&nbsp;
                        <button type="button" @click="batalAdvise(advise.idtrx)" class="btn btn-primary">Batal&nbsp;&nbsp;&nbsp;</button>
                    </td>
                    <td>@{{ advise.idtrx }}</td>
                    <td>@{{ advise.produk }}</td>
                    <td>@{{ advise.username }}</td>
                    <td>@{{ advise.created_at }}</td>
                </tr>
            </tbody>
        </table>
        </div>

        <!-- Pagination -->
        <div class="row" v-if="pagination.total > 0">
            <div class="col-sm-5">
                <div class="dataTables_info">
                    Menampilkan @{{ pagination.from }} sampai @{{ pagination.to }} dari @{{ pagination.total }} data
                </div>
            </div>
            <div class="col-sm-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <ul class="pagination">
                        <li class="paginate_button previous" :class="{'disabled': pagination.current_page === 1}">
                            <a href="#" @click.prevent="prosesBil(pagination.current_page - 1)">Previous</a>
                        </li>
                        <li class="paginate_button" v-for="page in paginationPages" :class="{'active': page === pagination.current_page}">
                            <a href="#" @click.prevent="prosesBil(page)">@{{ page }}</a>
                        </li>
                        <li class="paginate_button next" :class="{'disabled': pagination.current_page === pagination.last_page}">
                            <a href="#" @click.prevent="prosesBil(pagination.current_page + 1)">Next</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div v-html="pesanSpan" v-show="showMessage" > </div>
 
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
            username: '',
            pesanSpan: '',
            listAdvise: [],
            filterTanggal: '',
            filterProduk: '',
            pagination: {
                total: 0,
                per_page: 20,
                current_page: 1,
                last_page: 1,
                from: 1,
                to: 1
            }
        },

        mounted() {

            $("#tanggal").datepicker({ dateFormat: 'yy-mm-dd' });

            // Set tanggal default ke hari ini
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var yyyy = today.getFullYear();
            this.filterTanggal = yyyy + '-' + mm + '-' + dd;
            
            this.prosesBil(1);
        },

        computed: {
            showMessage: function(){
                if(this.pesanSpan.length > 0){
                    return true;
                }else{
                    return false;
                }
            },

            paginationPages: function() {
                var pages = [];
                var startPage = Math.max(1, this.pagination.current_page - 2);
                var endPage = Math.min(this.pagination.last_page, this.pagination.current_page + 2);
                
                for (var i = startPage; i <= endPage; i++) {
                    pages.push(i);
                }
                return pages;
            }
        },

        methods: {
            
            prosesBil: function (page) {
                page = page || 1;
                
                var params = {
                    page: page,
                    per_page: this.pagination.per_page
                };

                if (this.filterTanggal) {
                    params.tanggal = this.filterTanggal;
                }

                if (this.filterProduk) {
                    params.produk = this.filterProduk;
                }

                this.$http.get("{{ secure_url('api/pln/prepaid/manual') }}", { params: params }).then(response => {
                    if(response.body.status){
                        vmCU.listAdvise = response.body.data;
                        vmCU.pagination = response.body.pagination;
                    }
                }, response => {
                    
                });
            },

            resetFilter: function() {
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();
                this.filterTanggal = yyyy + '-' + mm + '-' + dd;
                this.filterProduk = '';
                this.prosesBil(1);
            },

            prosesAdvise: function (idpel,produk,denom,idtrx,message) {

                vUser = vmCU.username;
                if(vUser == ""){
                    alert("Username Loket Belum Diisi");
                    return;
                }
                
                vmCU.pesanSpan = "<span><i class='fa fa-cloud-upload'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>MEMPROSES ADVISE...</b></span><br/>";

                vmCU.$http.post("{{ secure_url('api/transaksi_bayar/advise/') }}", {
                    nomor_pelanggan: idpel, 
                    produk: produk, 
                    denom: denom, 
                    username: vUser,
                    message: message, 
                    idtrx:idtrx,
                    _token: "{{ csrf_token() }}" }).then(response => {

                    if(response.body.status){
                        text_color = "text-green";
                            if(response.body.response_code != "0000"){
                                text_color = "text-red";
                            }else{
                                text_color = "text-green";
                            }

                        vmCU.pesanSpan += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='"+text_color+"'>"+response.body.message.toUpperCase()+"</b></span><br/><br/>";
                    }else{
                        vmCU.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+response.body.message.toUpperCase()+"</span></b><br/><br/>";
                    }

                    vmCU.pesanSpan += "<span><i class='fa fa-hourglass-end'></i>&nbsp;&nbsp;&nbsp;<b class='text-green'>PROSES SELESAI.</b></span><hr/>";
                    vmCU.prosesBil();
                },response => {
                    vmCU.pesanSpan += "<span><i class='fa fa-warning'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>PROSES ADVISE ERROR</span></b><br/><br/>";
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