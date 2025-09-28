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
    <li class="active">Dashboard</li>
  </ol>
</section>

<!-- Main content -->
<section class="content" id="content-pdam">
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-body">
                <h4>NOMOR PELANGGAN</h4>
                <input id="idlgn" style="text-align: right; width: 180px" placeholder="No Kontrak Pelanggan" @keyup.enter="Inquery" v-model="idlgn" class="form-control" type="text">

                <input id="username" type="hidden" value="{!!$user['username']!!}">
                <input id="loket_name" type="hidden" value="{!!$user['loket_name']!!}">
                <input id="loket_code" type="hidden" value="{!!$user['loket_code']!!}">
                <br/>
                <div style="width:100%;overflow:auto;">
                <table id="dataTable" class="table table-bordered table-hover table-striped dataTable">
                    <thead>
                    <tr>
                        <th style="min-width:80px">AKSI</th>
                        <th style="min-width:80px">NOPEL</th>
                        <th style="min-width:200px">NAMA</th>
                        <th style="min-width:50px">IDGOL</th>
                        <th style="min-width:50px">BLTH</th>
                        <th style="min-width:80px">PAKAI</th>
                        <!-- <th style="min-width:100px">Harga</th>
                        <th style="min-width:100px">Abodemen</th>
                        <th style="min-width:100px">Materai</th>
                        <th style="min-width:100px">Limbah</th>
                        <th style="min-width:100px">Retribusi</th> -->
                        <th style="min-width:100px">DENDA</th>
                        <!-- <th style="min-width:100px">Angsuran</th> -->
                        <th style="min-width:100px">DISKON</th>
                        <th style="min-width:100px">RP PDAM</th>
                        <th style="min-width:100px">ADMIN</th>
                        <th style="min-width:100px">TOTAL</th>
                        <th style="min-width:300px">ALAMAT</th>
                        <!--   <th style="min-width:100px">Sub Total</th> -->
                        <th style="min-width:50px">ST.KINI</th>
                        <th style="min-width:50px">ST.LALU</th>
                        <th style="min-width:100px">BEBAN TETAP</th>
                        <th style="min-width:100px">BIAYA METER</th>
                        
                    </tr>
                    </thead>
                    <tbody id="dataLap">
                        <tr v-for="Rek in dataRek">

                            <td><button type='button' :disabled="isLoading" class='btn btn-primary btn-xs' @click="deleteRek(Rek.idlgn)">Hapus</button></td>
                            <td>@{{ Rek.idlgn }}</td>
                            <td>@{{ Rek.nama }}</td>
                            <td>@{{ Rek.gol }}</td>
                            <td>@{{ Rek.thbln }}</td>
                            <td>@{{ Rek.pakai }}</td>
                            <!-- <td>@{{ Rek.harga | currency('',0) }}</td>
                            <td>@{{ Rek.byadmin | currency('',0) }}</td>
                            <td>@{{ Rek.materai | currency('',0) }}</td>
                            <td>@{{ Rek.limbah | currency('',0) }}</td>
                            <td>@{{ Rek.retribusi | currency('',0) }}</td> -->
                            <td>@{{ Rek.denda | currency('',0) }}</td>
                            <td>@{{ Rek.diskon | currency('',0) }}</td>
                           <!--  <td>0</td> -->
                            <td>@{{ Rek.total | currency('',0) }}</td>
                            <td>@{{ Rek.admin_kop | currency('',0) }}</td>
                            <td>@{{ Rek.total_bayar | currency('',0) }}</td>
                            <td>@{{ Rek.alamat }}</td>
                            <!-- <td>@{{ Rek.sub_tot | currency('',0) }}</td> -->
                            <td>@{{ Rek.stand_i }}</td>
                            <td>@{{ Rek.stand_l }}</td>
                            <td>@{{ Rek.biaya_tetap | currency('',0) }}</td>
                            <td>@{{ Rek.biaya_meter | currency('',0) }}</td>

                        </tr>
                    </tbody>
                </table>
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
                            <input id="bayar" @keyup.enter="setBayar()" :disabled="isLoading" v-model="pelBayar" style="text-align: right; font-size: x-large; height: 50px" placeholder="Rupiah Bayar" class="form-control" type="text">
                        </div>
                        <div class="form-group" >
                            <label for="kembalian" style="font-size: x-large" class="control-label">KEMBALIAN</label>
                            <input id="kembalian" v-model="kembalian" style="text-align: right; font-size: x-large; height: 50px" readonly="" placeholder="Rupiah Kembalian" class="form-control" type="text">
                        </div>

                        <span id="warningPrint">
                            <i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>
                            PLUG IN PRINTER TIDAK TERDETEKSI, JALANKAN APLIKASI PRINTER CLIENT.</b>
                            &nbsp;&nbsp;
                            <button type="button" id="btnRefreshPrint" onclick="refreshPrinter()" class="btn btn-xs btn-warning"/>RELOAD</button>

                        </span>

                        <div id="panelPrint" style="display: none">
                            <div class="checkbox">
                              <label>
                                <input type="checkbox" id="isPrinterBaru"> Gunakan Printer Baru
                              </label>
                            </div>

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

                <button type="button" :disabled="isLoading" id="btnPayment" @click="Payment()" class="btn btn-primary">
                    Bayar</button>
                <button type="button" :disabled="isLoading" @click="CetakUlang()" class="btn btn-primary">
                    Cetak Ulang</button>
                <button type="button" id="btnKolektif" @click="showKolektif()" class="btn btn-primary">Kolektif</button>
                <button type="button" id="btnCetakUlang" @click="cetakRekap()" class="btn btn-primary">Cetak Rekap</button>
                <!-- <button type="button" :disabled="isLoading" class="btn btn-primary" onclick="clearAll()">
                    Reset</button> -->
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

<div class="modal fade" tabindex="-1" role="dialog" id="cetakDialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Cetak Ulang</h4>
      </div>
      <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                <label for="txtTglAwal" class="control-label">
                  Tanggal Awal</label>
                <input id="txtTglAwal" type="text" style="width: 100%" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
              </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                <label for="txtTglAkhir" class="control-label">
                  Tanggal Akhir</label>
                <input id="txtTglAkhir" type="text" style="width: 100%" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
              </div>
              </div>
            </div>
            
            <label for="ctkIdlgn">No. Pelanggan</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input class="form-control" placeholder="Nomor Pelanggan" id="ctkIdlgn"  type="text">
            </div>
            
            <label for="ctkBlRek">Bulan Rekening (Format : YYYYMM)</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input class="form-control" placeholder="Bulan Rekening" id="ctkBlRek"  type="text">
            </div>
            <br/>
            <div v-html="pesanCetakUlang"></div>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-primary" @click="ProsesCetakUlang()">
                    Cetak</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@include('...cetakan/pdambjm')
@include('admin.modals')
@include('...cetakan/rekap_trx_pdambjm')
</section>

<script type="text/javascript">
    var connection;

    function refreshPrinter(){
        connection = new WebSocket('ws://localhost:8551/Laputa');

        connection.onopen = function () {
            $("#warningPrint").css('display', 'none'); 
            $("#panelPrint").css('display', 'inline');  
            $("#isPrinterBaru").prop('checked', true);
            //console.log("connected");
        };

        // Log errors
        connection.onerror = function (error) {
            $("#warningPrint").css('display', 'inline'); 
            $("#panelPrint").css('display', 'none'); 
            $("#isPrinterBaru").prop('checked', false);
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

        var vmPdam = new Vue({
        el: '#content-pdam',
        data: {
            pesanLoading: '',
            pesanCetakUlang: '',
            isLoading: false,
            idlgn:'',
            pelBayar: 0,
            dataRek: [],
            dataKolektif: []
        },

        mounted() {
            $("#idlgn").focus();
            $("#txtTglAwal").datepicker({ dateFormat: 'yy-mm-dd' }); 
            $("#txtTglAkhir").datepicker({ dateFormat: 'yy-mm-dd' }); 

            refreshPrinter();
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
                        totalTagih += parseInt(this.dataRek[i].total_bayar);
                    }
                }
                return numeral(totalTagih).format('0,0');
            },

            kembalian: function(){
                nTagihan = parseInt(this.totalBayar.replace(/,/g,""));
                nKembalian = this.pelBayar - nTagihan;
                return numeral(nKembalian).format('0,0');
            },

            listIdlgn: function(){
                var linq = Enumerable.From(this.dataRek);
                var result =
                    linq.GroupBy(function(x){return x.idlgn;})
                        .Select(function(x){return { idlgn:x.Key() };})
                        .ToArray();
                return result;
            }
        },

        methods: {

            LoadDataKolektif: function(){
                axios.get("{{ secure_url('/admin/pdam_kolektif/daftar') }}")
                    .then(function (response) {
                        if(response.data.status){
                            vmPdam.dataKolektif = response.data.data;
                        }else{
                            vmPdam.dataKolektif = [];
                        }
                        
                    })
                    .catch(function (error) {
                      //console.log(error);
                  });
            },

            refreshPrintServer: function(){
                
            },

            cetakRekap: function(){
                isPrinterBaru = $("#isPrinterBaru").is(':checked') ? 1 : 0;
                jenisKertas = $("#jenisKertas").val();
                username = $("#username").val();
                loket_name = $("#loket_name").val();
                loket_code = $("#loket_code").val();
                tglNow = "<?php echo date('d-m-Y h:i:s'); ?>";

                if(isPrinterBaru > 0){
                    FormatCetak = "REKAP TAGIHAN\n\nDicetak Oleh : "+username+" - "+loket_code+" "+loket_name + "\nWaktu Cetak : "+tglNow+"\n\n:Produk :Nopel :Nama :Periode :Total\n";
                    FormatCetak += "======================================================================\n";
  
                    for(i=0; i<vmPdam.dataRek.length; i++){
                        FormatCetak += ":" + vmPdam.dataRek[i].idlgn + " :" + vmPdam.dataRek[i].nama + " :" + vmPdam.dataRek[i].thbln + " :" + vmPdam.dataRek[i].total_bayar+"\n";
                    }

                    FormatCetak += "======================================================================\n";
                    FormatCetak += ":TOTAL " + ":" + vmPdam.totalBayar;
                    //alert(FormatCetak);

                    kirimData(FormatCetak, jenisKertas, 1);

                }else{
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
                }
                
            },

            pilihKolektif: function(Id){
                $('#modalKolektif').modal("hide");
                vmPdam.dataRek = [];
                var ErrorMessage = "";

                axios.get("{{ secure_url('/admin/pdam_kolektif/kolektif') }}/"+Id)
                    .then(function (response) {
                        if(response.data.status){

                            var q = $.jqmq({
                                delay: -1,
                                callback: function(IdCust) {

                                    if(vmPdam.isLoading){
                                        $.notify({
                                          icon: 'fa fa-warning',
                                          title: "<strong>Prevent</strong> : ",
                                          message: "Proses Inquery Belum Selesai."
                                        },{
                                          type: 'warning'
                                        });
                                        return;
                                    }

                                    vmPdam.pesanLoading = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>INQUERY TAGIHAN PELANGGAN "+IdCust+"...</b></span><br/>";
                                    vmPdam.isLoading = true;

                                    kodeLoket = $("#loket_code").val();
                                    vmPdam.$http.get("{{ secure_url('/api/pdambjm') }}/get/"+IdCust+"/"+kodeLoket).then(response => {
                                        if(response.body.status == "Success"){
                                            
                                            vmPdam.pesanLoading = "";

                                            Rek = response.body.data;
                                            for(i=0;i<Rek.length;i++){
                                                total_tagihan = parseInt(Rek[i].total) + parseInt(Rek[i].admin_kop);

                                                if(!total_tagihan){
                                                    total_tagihan = 0;
                                                }
                                                
                                                Rek[i].total_bayar = total_tagihan;
                                                vmPdam.dataRek.push(Rek[i]);
                                            }

                                            vmPdam.idlgn = "";
                                            
                                        }else{
                                            ErrorMessage +="<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+IdCust+": "+response.body.message.toUpperCase()+"</b></span><br/>";
                                        }

                                        vmPdam.isLoading = false;
                                        q.next();
                                    }, response => {
                                        ErrorMessage +="<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>TERJADI KESALAHAN SISTEM</b></span><br/>";
                                        vmPdam.isLoading = false;
                                        q.next();
                                    });
                                },
                                complete: function(){
                                    vmPdam.notify("Message Kolektif", "Inquery Selesai.");
                                    vmPdam.pesanLoading = ErrorMessage;
                                    
                                    $("#bayar").focus();
                                    $("#bayar").select();
                                }
                            });

                            $.ajaxSetup({ cache: false });

                            listIdlgn = response.data.data;
                            for(i=0; i<listIdlgn.length; i++){
                                q.add(listIdlgn[i].id_pelanggan);
                            }

                        }else{
                            vmPdam.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>DATA KOLEKTIF KOSONG</b></span><br/>";
                        }
                        
                    })
                    .catch(function (error) {
                      //console.log(error);
                  });
            },

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

            setBayar: function(){
                $("#btnPayment").focus();
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

                if(this.idlgn.length <=0 ) {
                    $("#bayar").focus();
                    $("#bayar").select();
                    return;
                }

                if(this.idlgn.length < 7) {
                    this.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>NO PELANGGAN TIDAK VALID.</b></span>";
                    return;
                }

                if(this.cekIdExist()){
                    this.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>NO PELANGGAN SUDAH ADA.</b></span>";
                    return;
                }

                this.pesanLoading = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>INQUERY TAGIHAN PELANGGAN...</b></span>";
                this.isLoading = true;

                kodeLoket = $("#loket_code").val();
                this.$http.get("{{ secure_url('/api/pdambjm') }}/get/"+this.idlgn+"/"+kodeLoket).then(response => {
                    if(response.body.status == "Success"){
                        
                        this.pesanLoading = "";

                        Rek = response.body.data;
                        for(i=0;i<Rek.length;i++){
                            total_tagihan = parseInt(Rek[i].total) + parseInt(Rek[i].admin_kop);

                            if(!total_tagihan){
                                total_tagihan = 0;
                            }
                            
                            Rek[i].total_bayar = total_tagihan;
                            this.dataRek.push(Rek[i]);
                        }

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

            cekIdExist: function () {
                for(i=0; i<this.dataRek.length; i++){
                    if(this.idlgn == this.dataRek[i].idlgn){
                        return true;
                    }
                }
                return false;
            },

            ProsesCetakUlang: function(){

                this.pesanCetakUlang = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>CHECKING REKENING...</b></span>";

                Idpel = $("#ctkIdlgn").val();
                tglAwal = $("#txtTglAwal").val();
                tglAkhir = $("#txtTglAkhir").val();
                BlnRek = $("#ctkBlRek").val();
                
                if(BlnRek.length <= 0){
                    BlnRek = "-";
                }

                jenisKertas = $("#jenisKertas").val();
                isPrinterBaru = $("#isPrinterBaru").is(':checked') ? 1 : 0;
                
                $.ajaxSetup({ cache: false });
                $.getJSON("{{ secure_url('/admin/cetak_ulang_baru') }}/"+Idpel+"/"+tglAwal+"/"+tglAkhir+"/"+BlnRek+"/"+isPrinterBaru+"/"+jenisKertas, function(msg){

                    if(msg.status == "Success"){

                        vmPdam.pesanCetakUlang = "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-green'>CETAK ULANG BERHASIL.</b></span>";

                        if(msg.is_print_baru <= 0){

                            for (index = msg.data.length - 1; index >= 0; --index) {
                                idlgn = msg.data[index]['cust_id'];
                                alamat = msg.data[index]['alamat'];
                                abodemen = msg.data[index]['abodemen'];
                                denda = msg.data[index]['denda'];
                                gol = msg.data[index]['gol'];
                                harga = msg.data[index]['harga_air'];
                                limbah = msg.data[index]['limbah'];
                                materai = msg.data[index]['materai'];
                                nama = msg.data[index]['nama'];
                                pakai = msg.data[index]['pakai'];
                                retribusi = msg.data[index]['retribusi'];
                                stand_i = msg.data[index]['stand_kini'];
                                stand_l = msg.data[index]['stand_lalu'];
                                sub_tot = msg.data[index]['sub_total'];
                                tanggal = msg.data[index]['transaction_date'];
                                thbln = vmPdam.convert_Blth(msg.data[index]['blth']);
                                total = msg.data[index]['total'];
                                beban_tetap = msg.data[index]['beban_tetap'];
                                biaya_meter = msg.data[index]['biaya_meter'];
                                admin_kop = msg.data[index]['admin'];
                                username = msg.data[index]['username'];
                                kode = msg.data[index]['transaction_code'] + "/" + username + "/" + msg.data[index]['loket_code'] + "/" + msg.data[index]['transaction_date'];

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
                                $("#RekKode").html(kode);
                                $("#RekBebanTetap").html(numeral(beban_tetap).format('0,0'));
                                $("#RekMeter").html(numeral(biaya_meter).format('0,0'));

                                vmPdam.Cetak();
                            }
                        }else{

                            printData = msg.print_data;
                            //console.log(printData);

                            for(i=0; i<printData.length; i++){
                                kirimData(printData[i].print_data, printData[i].jenis_kertas, printData[i].jml_rek);
                            }

                            
                        }
                    }else{
                        vmPdam.pesanCetakUlang = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+msg.message.toUpperCase()+"</b></span>";
                        
                    }
                }).error(function(jqXHR, textStatus, errorThrown){
                     vmPdam.pesanCetakUlang = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>ERROR CETAK ULANG</b></span>";
                });
            },

            hideTable: function(){
                $('#dataTable tr > *:nth-child(7)').hide();
                $('#dataTable tr > *:nth-child(8)').hide();
                $('#dataTable tr > *:nth-child(9)').hide();
                $('#dataTable tr > *:nth-child(10)').hide();
                $('#dataTable tr > *:nth-child(11)').hide();
                $('#dataTable tr > *:nth-child(13)').hide();
                $('#dataTable tr > *:nth-child(18)').hide();
            },

            deleteRek: function (idlgn){
                for(i=0; i<vmPdam.dataRek.length; i++){

                    if(idlgn == vmPdam.dataRek[i].idlgn){
                        vmPdam.dataRek.splice(i, 1);
                        vmPdam.deleteRek(idlgn);
                    }
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

            CetakUlang: function(){
                $('#cetakDialog').modal("show");
                $("#ctkIdlgn").focus();
            },

            Cetak: function(){
                var sDivText = $("#cetakRekening").html();

                vCekPrint = $("#isPrinterBaru").prop('checked');
                if(vCekPrint){
                    printHTML(sDivText);
                }else{
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

            Payment: function(){

                if(vmPdam.isLoading){
                    $.notify({
                      icon: 'fa fa-warning',
                      title: "<strong>Prevent</strong> : ",
                      message: "Proses Payment Belum Selesai."
                    },{
                      type: 'warning'
                    });
                    return;
                }

                jumBayar = parseInt(vmPdam.kembalian.replace(/,/g,"",-1));
                if(jumBayar < 0){
                    vmPdam.pesanLoading = "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>UANG TIDAK CUKUP.</b></span>";

                    $("#bayar").focus();
                    return;
                }

                vmPdam.pesanLoading = "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>MULAI PAYMENT...</span></b><br/><br/>";
                vmPdam.isLoading = true;
                var q = $.jqmq({
                    delay: -1,
                    callback: function(IdCust) {

                        //console.log(IdCust);

                        var q = this;
                        var DataRekening = {};
                        var Rekening = [];

                        DataRekening.PaymentData = Rekening;

                        for(i=0;i<vmPdam.dataRek.length;i++){
                            if(vmPdam.dataRek[i].idlgn == IdCust){

                                var User = $("#username").val();
                                var LoketName = $("#loket_name").val();
                                var LoketCode = $("#loket_code").val();

                                Rek = vmPdam.dataRek[i];

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
                                    "Diskon": Rek.diskon,
                                    "Sub_Tot": Rek.total,
                                    "Limbah": Rek.limbah,
                                    "Stand_l": Rek.stand_l,
                                    "Stand_i": Rek.stand_i,
                                    "Admin_Kop": Rek.admin_kop,
                                    "Total": Rek.total_bayar,
                                    "User": User,
                                    "LoketName": LoketName,
                                    "LoketCode": LoketCode,
                                    "Biaya_tetap": Rek.biaya_tetap,
                                    "Biaya_meter": Rek.biaya_meter
                                }

                               DataRekening.PaymentData.push(Rekening);
                            }
                        }

                        jenisKertas = $("#jenisKertas").val();
                        isPrinterBaru = $("#isPrinterBaru").is(':checked') ? 1 : 0;

                        vmPdam.pesanLoading += "<span><i class='fa fa-cloud-download'></i>&nbsp;&nbsp;&nbsp;<b class='text-yellow'>PROSES PAYMENT "+IdCust+"...</span></b><br/>";

                        vmPdam.$http.post("{{ secure_url('api/pdambjm/transaksi') }}", {
                            PaymentData: DataRekening, 
                            isPrinterBaru: isPrinterBaru,
                            jenisKertas: jenisKertas,
                            _token: "{{ csrf_token() }}" }).then(response => {
                                msg = response.body;
                                //console.log(msg);

                                if(msg.status == "Success"){

                                    if(isPrinterBaru <= 0){
                                        for (index = msg.data.length - 1; index >= 0; --index) {
                                            idlgn = msg.data[index]['cust_id'];
                                            alamat = msg.data[index]['alamat'];
                                            abodemen = msg.data[index]['abodemen'];
                                            denda = msg.data[index]['denda'];
                                            gol = msg.data[index]['gol'];
                                            harga = msg.data[index]['harga_air'];
                                            limbah = msg.data[index]['limbah'];
                                            materai = msg.data[index]['materai'];
                                            nama = msg.data[index]['nama'];
                                            pakai = msg.data[index]['pakai'];
                                            retribusi = msg.data[index]['retribusi'];
                                            stand_i = msg.data[index]['stand_kini'];
                                            stand_l = msg.data[index]['stand_lalu'];
                                            sub_tot = msg.data[index]['sub_total'];
                                            tanggal = msg.data[index]['transaction_date'];
                                            thbln = vmPdam.convert_Blth(msg.data[index]['blth']);
                                            total = msg.data[index]['total'];
                                            admin_kop = msg.data[index]['admin'];
                                            beban_tetap = msg.data[index]['beban_tetap'];
                                            biaya_meter = msg.data[index]['biaya_meter'];
                                            
                                            username = msg.data[index]['username'];
                                            kode = msg.data[index]['transaction_code'] + "/" + username + "/" + msg.data[index]['loket_code'] + "/" + msg.data[index]['transaction_date'];

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
                                            $("#RekKode").html(kode);
                                            $("#RekMeter").html(numeral(biaya_meter).format('0,0'));
                                            $("#RekBebanTetap").html(numeral(beban_tetap).format('0,0'));

                                            vmPdam.Cetak();
                                        }
                                    }else{
                                        printData = msg.print_data;

                                        for(i=0; i<printData.length; i++){
                                            kirimData(printData[i].print_data, printData[i].jenis_kertas, printData[i].jml_rek);
                                        }
                                    }

                                    vmPdam.pesanLoading += "<span><i class='fa fa-envelope'></i>&nbsp;&nbsp;&nbsp;<b class='text-green'>PAYMENT IDPEL " + msg.data[0].cust_id + " BERHASIL</span></b><br/><br/>";
                                    q.next();
                                }else{
                                    vmPdam.pesanLoading += "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>"+msg.message.toUpperCase()+"</span></b><br/><br/>";
                                    q.next();
                                }
                            }, response => {
                                vmPdam.pesanLoading += "<span><i class='fa fa-ban'></i>&nbsp;&nbsp;&nbsp;<b class='text-red'>TERJADI KESALAHAN PAYMENT.</span></b><br/><br/>";
                                q.next();
                        });
                    },
                    complete: function(){
                        vmPdam.pesanLoading += "<span><i class='fa fa-hourglass-end'></i>&nbsp;&nbsp;&nbsp;<b class='text-green'>PROSES PAYMENT SELESAI.</span></b><br/>";

                        vmPdam.pesanLoading += "<div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>TOTAL BAYAR</b></div><div class='col-md-6'><b>: Rp. "+vmPdam.totalBayar+"</b></div></div><div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>BAYAR</b></div><div class='col-md-6'><b>: Rp. "+numeral(vmPdam.pelBayar).format('0,0')+"</b></div></div><div class='row'><div class='col-md-4'><i class='fa fa-calculator'></i>&nbsp;&nbsp;&nbsp;<b>KEMBALIAN</b></div><div class='col-md-6'><b>: Rp. "+vmPdam.kembalian+"</b></div></div><br/>";

                        vmPdam.dataRek = [];
                        vmPdam.pelBayar = 0;

                        $("#idlgn").focus();
                        vmPdam.isLoading = false;

                        RefreshSaldo();
                    }
                });

                $.ajaxSetup({ cache: false });

                vList = vmPdam.listIdlgn;

                for(j=0; j<vList.length; j++){

                    q.add(vList[j].idlgn);
                }
                
            }
        }


        }); 

    });

</script>
@endsection