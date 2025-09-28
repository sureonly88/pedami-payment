@extends('...layouts/template')

@section('content')

<style>
.ui-dialog-titlebar-close {
  visibility: hidden;
}

</style>

<script>
$("#menuPdamBjm" ).prop( "class", "active" );

$(document).ready(function() {
    $("select").select2();
    $("#txtTanggal").datepicker({ dateFormat: 'yy-mm-dd' }); 
});

function showDialogNoCancel(message){
    $("#isiLoading").html(message.toUpperCase());
    $('#modalLoading').modal("show");
}

function closeDialogNoCancel(){
    $('#modalLoading').modal("hide");
}

function showDialog(message){
    $("#isiPesan").html(message);
    $('#modalPesan').modal("show");
}

function closeDialog(){
    $('#modalPesan').modal("hide");
}

function Cetak(){
    var sDivText = $("#cetakRekening").html();
    var objWindow = window.open("", "", "left=0,top=0,width=1,height=1");
    objWindow.document.write(sDivText);
    objWindow.document.close();
    objWindow.focus();

    // objWindow.jsPrintSetup.definePaperSize(255, 255, "Custom", "Custom_Paper", "Custom PAPER", 250, 250, jsPrintSetup.kPaperSizeMillimeters);
    // objWindow.jsPrintSetup.setPaperSizeData(255);

    //objWindow.jsPrintSetup.setOption('orientation', jsPrintSetup.kLandscapeOrientation);
    objWindow.jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
    // objWindow.jsPrintSetup.setOption('paperHeight', 300);
    // objWindow.jsPrintSetup.setOption('paperWidth', 300);
       // set top margins in millimeters
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
    // clears user preferences always silent print value
    // to enable using 'printSilent' option
    //objWindow.jsPrintSetup.clearSilentPrint();
    // Suppress print dialog (for this context only)
    objWindow.jsPrintSetup.setOption('printSilent', 1);

    objWindow.jsPrintSetup.print();
    //objWindow.print();
    objWindow.close();
}

function CetakUlang(){
    $('#cetakDialog').modal("show");
    $("#ctkIdlgn").focus();
}

function ProsesCetakUlang(){
    $('#cetakDialog').modal("hide");

    showDialogNoCancel("GETTING CUSTOMER DATA...");
    Idpel = $("#ctkIdlgn").val();
    Tgl_Transaksi = $("#txtTanggal").val();
	BlnRek = $("#ctkBlRek").val();
	
	if(BlnRek.length <= 0){
		BlnRek = "-";
	}
	
    $.ajaxSetup({ cache: false });
    $.getJSON("{{ secure_url('/admin/cetak_ulang') }}/"+Idpel+"/"+Tgl_Transaksi+"/"+BlnRek, function(msg){
        closeDialogNoCancel();
        if(msg.status == "Success"){
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
                thbln = convert_Blth(msg.data[index]['blth']);
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

                Cetak();
            }
        }else{
            showDialog(msg.message);
        }
    }).error(function(jqXHR, textStatus, errorThrown){
        closeDialogNoCancel();
        showDialog("Error occured Cetak Ulang...");
    });
}

function getPelanggan(e){
    if (e.keyCode != 13) {
        return;
    }

    $("#responseMessage").hide();
    $("#responseFailed").hide();
    Idpel = $("#idlgn").val();
    LoketCode = $("#loket_code").val();

    if(Idpel.length <= 0){
        $("#bayar").focus();
        $("#bayar").select();
        return;   
    } 

    if(cekIdpel(Idpel) > 0){
        showDialog("NOMOR PELANGGAN SUDAH ADA");
        $("#idlgn").val("");
        return;
    }

    showDialogNoCancel("GETTING CUSTOMER DATA...");
    $.ajaxSetup({ cache: false });
    $.getJSON("{{ secure_url('/api/pdambjm') }}/get/"+Idpel+"/"+LoketCode, function(data){
        closeDialogNoCancel();
        if(data.status == "Success"){
            cekError = data.data[0]['status'];
            if(cekError != "A"){
                showDialog(data.data[0]['alamat']);
                return;
            }
            var index;
            var nomor = 1;
            for (index = data.data.length - 1; index >= 0; --index) {
                alamat = data.data[index]['alamat'];
                byadmin = (jQuery.isEmptyObject(data.data[index]['byadmin'])) ? "0" : data.data[index]['byadmin'];
                denda = (jQuery.isEmptyObject(data.data[index]['denda'])) ? "0" : data.data[index]['denda'];
                gol = data.data[index]['gol'];
                //harga = data.data[index]['harga'];
                limbah = (jQuery.isEmptyObject(data.data[index]['limbah'])) ? "0" : data.data[index]['limbah'];
                materai = (jQuery.isEmptyObject(data.data[index]['materai'])) ? "0" : data.data[index]['materai'];
                harga = (jQuery.isEmptyObject(data.data[index]['harga'])) ? "0" : data.data[index]['harga'];
                biaya_tetap = (jQuery.isEmptyObject(data.data[index]['biaya_tetap'])) ? "0" : data.data[index]['biaya_tetap'];
                biaya_meter = (jQuery.isEmptyObject(data.data[index]['biaya_meter'])) ? "0" : data.data[index]['biaya_meter'];
                nama = data.data[index]['nama'];
				pakai = (jQuery.isEmptyObject(data.data[index]['pakai'])) ? "0" : data.data[index]['pakai'];              
                retribusi = data.data[index]['retribusi'];				
                stand_i = (jQuery.isEmptyObject(data.data[index]['stand_i'])) ? "0" : data.data[index]['stand_i'];
                stand_l = (jQuery.isEmptyObject(data.data[index]['stand_l'])) ? "0" : data.data[index]['stand_l'];
                status = data.data[index]['status'];
                sub_tot = (jQuery.isEmptyObject(data.data[index]['sub_tot'])) ? "0" : data.data[index]['sub_tot'];
                tanggal = data.data[index]['tanggal'];
                thbln = data.data[index]['thbln'];
                total = (jQuery.isEmptyObject(data.data[index]['total'])) ? "0" : data.data[index]['total'];
                admin_kop = (jQuery.isEmptyObject(data.data[index]['admin_kop'])) ? "0" : data.data[index]['admin_kop'];

                dataLap = "<tr><td><button type='button' class='btn btn-primary btn-xs' onclick=\"delDataFromGrid('"+Idpel+"')\">Hapus</button></td><td>"+Idpel+"</td><td>"+nama+"</td>" +
                          "<td>"+gol+"</td><td>"+thbln+"</td>" + "<td>"+pakai+"</td>" +
                          "<td>Rp. "+harga+"</td>" +
                          "<td>Rp. "+byadmin+"</td> " +
                          "<td>Rp. "+materai+"</td>" +
                          "<td>Rp. "+limbah+"</td>" +
                          "<td>Rp. "+retribusi+"</td>" +
                          "<td>Rp. "+denda+"</td>" +
                          "<td>Rp. 0</td>" +
                          "<td>Rp. "+numeral(total).format('0,0')+"</td>" +
                          "<td>Rp. "+numeral(admin_kop).format('0,0')+"</td>" +
                          "<td>Rp. "+numeral(parseInt(total)+parseInt(admin_kop)).format('0,0')+"</td>" +
                          "<td>"+alamat+"</td>" +
                          "<td>"+sub_tot+"</td>" +
                          "<td>"+stand_i+"</td>" +
                          "<td>"+stand_l+"</td>" +
                          "<td>"+biaya_tetap+"</td>" +
                          "<td>"+biaya_meter+"</td>" +
                          "</tr>";
                $("#dataLap").append(dataLap);
                nomor++;
            }
            getTotal();
            hide();
            // $("#bayar").focus();
            // $("#bayar").select();

            $("#idlgn").focus();
            //$("#total").val(numeral(total_bayar).format('0,0'));
        }else{
            showDialog(data.message);
        }
        $("#idlgn").val("");
    }).error(function(jqXHR, textStatus, errorThrown){
        closeDialogNoCancel();
        showDialog("Error occured Get Customer...");
    });


}

function focusBayar(){
    $("#bayar").focus();
    $("#bayar").select();
}

function delDataFromGrid(Idpel){
     var r = confirm("Hapus Idpel : " + Idpel + " ?");
     if (r == true) {
         $('#dataLap tr').each(function(){
             //var customerId = $(this).find("td:first").html();
             var customerId = $(this).find("td").eq(1).html();
             if(customerId == Idpel){
                 $(this).remove();
             }
         });
         getTotal();
     }
 }

 function clearAll(){
    $('#dataLap tr').each(function(){
        $(this).remove();
     });

    $("#total").val("0");
    $("#bayar").val("0");
    $("#kembalian").val("0");

 }

 function delFromGrid(Idpel){
     $('#dataLap tr').each(function(){
         //var customerId = $(this).find("td:first").html();
         var customerId = $(this).find("td").eq(1).html();
         if(customerId == Idpel){
             $(this).remove();
         }
     });
 }

function getNumber(strTotal){
    intTotal = strTotal.replace("Rp. ", "");
    intTotal = intTotal.replace(/,/g,"");
    //console.log(intTotal);
    return Number(intTotal);
}

function getTotal(){
    total_bayar = 0;
    $('#dataLap tr').each(function(){
        var total = getNumber($(this).find("td").eq(15).html());
        //console.log(total);
        total_bayar += total;
    });
    $("#total").val(numeral(total_bayar).format('0,0'));
    hitungKembalian();
}

function hitungKembalianEvent(e){
    if (e.keyCode != 13) {
        return;
    }

    total_bayar = getNumber($("#total").val());
    bayar = getNumber($("#bayar").val());

    $("#kembalian").val(numeral(bayar-total_bayar).format('0,0'));
    $("#bayar").val(numeral(bayar).format('0,0'));

    $("#btnPayment").focus();
}

function hitungKembalian(){
    total_bayar = getNumber($("#total").val());
    bayar = getNumber($("#bayar").val());

    $("#kembalian").val(numeral(bayar-total_bayar).format('0,0'));
    $("#bayar").val(numeral(bayar).format('0,0'));
}

function cekIdpel(Idpel){
    jmlPel = 0;
    $('#dataLap tr').each(function(){
        var customerId = $(this).find("td").eq(1).html();
        //var customerId = $(this).find("td:first").html();
        //var customerId = $(this).find("td").eq(2).html();
        //console.log(customerId + " = " + Idpel);
        if(customerId == Idpel){
            jmlPel ++;
        }
    });
    return jmlPel;
}

function hide(){
    $('#dataTable tr > *:nth-child(7)').hide();
    $('#dataTable tr > *:nth-child(8)').hide();
    $('#dataTable tr > *:nth-child(9)').hide();
    $('#dataTable tr > *:nth-child(10)').hide();
    $('#dataTable tr > *:nth-child(11)').hide();
    //$('#dataTable tr > *:nth-child(12)').hide();
    $('#dataTable tr > *:nth-child(13)').hide();
    //$('#dataTable tr > *:nth-child(17)').hide();
    $('#dataTable tr > *:nth-child(18)').hide();
    //$('#dataTable tr > *:nth-child(19)').hide();
    //$('#dataTable tr > *:nth-child(20)').hide();
}

// function hide(){
//     $('#dataTable tr > *:nth-child(6)').hide();
//     $('#dataTable tr > *:nth-child(7)').hide();
//     $('#dataTable tr > *:nth-child(8)').hide();
//     $('#dataTable tr > *:nth-child(9)').hide();
//     $('#dataTable tr > *:nth-child(10)').hide();
//     //$('#dataTable tr > *:nth-child(11)').hide();
//     $('#dataTable tr > *:nth-child(12)').hide();
//     //$('#dataTable tr > *:nth-child(16)').hide();
//     $('#dataTable tr > *:nth-child(17)').hide();
//     //$('#dataTable tr > *:nth-child(18)').hide();
//     //$('#dataTable tr > *:nth-child(19)').hide();
// }

function Payment(){
	$("#btnPayment").prop('disabled', true);
    if(getNumber($("#kembalian").val()) < 0){
       showDialog("Uang tidak cukup untuk membayar!");
       
       return;
    }

   //showDialogNoCancel("Checking Pulsa...");
   $.getJSON("{{ secure_url('/admin/pulsa') }}/"+$("#loket_code").val()+"/"+$("#total").val(), function(data){
        if(data.status == "Success"){
           //closeDialogNoCancel();
           ProsesPayment();
        }else{
           //closeDialogNoCancel();
		   $("#btnPayment").prop('disabled', false);
           showDialog(data.message);
        }
   }).error(function(jqXHR, textStatus, errorThrown){
       //closeDialogNoCancel();
	   $("#btnPayment").prop('disabled', false);
       showDialog("Error occured Check Pulsa...");
   });
}

function convert_Blth(Blth){
	Tahun = Blth.trim().substr(0,4);
	Bulan = parseInt(Blth.trim().substr(4,2));
	//console.log(Blth.trim().substr(4,2));
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
}

function ProsesPayment(){

   $("#responseMessage").html("");
   $("#responseFailed").html(""); 

   showDialogNoCancel("Proses Pembayaran...");

   var q = $.jqmq({
      delay: -1,
      callback: function(DtRek) {
            var q = this;
            var IdCust = DtRek['Idlgn'];
            var DataRekening = {};
            var Rekening = [];

            DataRekening.PaymentData = Rekening;

            $('#dataLap tr').each(function(){
                if($(this).find("td").eq(1).html() == IdCust){

                    var customerId = $(this).find("td").eq(1).html();
                    var Nama = $(this).find("td").eq(2).html();
                    var Idgol = $(this).find("td").eq(3).html();
                    var Thbln = $(this).find("td").eq(4).html();
                    var Pakai = getNumber($(this).find("td").eq(5).html());
                    var Harga = getNumber($(this).find("td").eq(6).html());
                    var ByAdmin = getNumber($(this).find("td").eq(7).html());
                    var Materai = getNumber($(this).find("td").eq(8).html());
                    var Retri = getNumber($(this).find("td").eq(10).html());
                    var Denda = getNumber($(this).find("td").eq(11).html());
                    var Sub_Tot = getNumber($(this).find("td").eq(13).html());
                    var Limbah = getNumber($(this).find("td").eq(9).html());
                    var Total = getNumber($(this).find("td").eq(15).html());
                    var Stand_l = $(this).find("td").eq(19).html();
                    var Stand_i = $(this).find("td").eq(18).html();
                    var Biaya_tetap = getNumber($(this).find("td").eq(20).html());
                    var Biaya_meter = getNumber($(this).find("td").eq(21).html());
                    var Total = getNumber($(this).find("td").eq(15).html());
                    var Admin_Kop = getNumber($(this).find("td").eq(14).html());
                    var Alamat = $(this).find("td").eq(16).html();
                    var User = $("#username").val();
                    var LoketName = $("#loket_name").val();
                    var LoketCode = $("#loket_code").val();

                    var Rekening = {
                        "Idlgn": customerId,
                        "Nama": Nama,
                        "Idgol": Idgol,
                        "Alamat": Alamat,
                        "Thbln": Thbln,
                        "Pakai": Pakai,
                        "Harga": Harga,
                        "ByAdmin": ByAdmin,
                        "Materai": Materai,
                        "Retri": Retri,
                        "Denda": Denda,
                        "Sub_Tot": Sub_Tot,
                        "Limbah": Limbah,
                        "Stand_l": Stand_l,
                        "Stand_i": Stand_i,
                        "Admin_Kop": Admin_Kop,
                        "Total": Total,
                        "User": User,
                        "LoketName": LoketName,
                        "LoketCode": LoketCode,
                        "Biaya_tetap": Biaya_tetap,
                        "Biaya_meter": Biaya_meter
                    }

                   DataRekening.PaymentData.push(Rekening);
                }
            });

            $.ajax({
                method: "POST",
                url: "{{ secure_url('api/pdambjm/transaksi') }}",
                data: { PaymentData: DataRekening,
                       _token: "{{ csrf_token() }}" }
            })
            .done(function(msg) {

                if(msg.status == "Success"){
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
                        thbln = convert_Blth(msg.data[index]['blth']);
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

                        Cetak();
                    }

                    $("#responseMessage").append("<strong>MESSAGE : </strong>" + msg.data[0].cust_id + " a/n " + msg.data[0].nama + " " + msg.message + " .<br/>");
                    $("#responseMessage").show();
                    delFromGrid(msg.data[0].cust_id);

                    q.next();
                }else{
                    $("#responseFailed").append("<strong>MESSAGE : </strong>" + msg.message + " .<br/>");
                    $("#responseFailed").show();

                    q.next();
                    //q.next( !msg.status );
                }

            });
            
      },
      complete: function(){
        
        closeDialogNoCancel();

        $("#idlgn").focus();
        $("#btnPayment").prop('disabled', false);
      }
    });

   var custId = "";
   $('#dataLap tr').each(function(){
        //var customerId = $(this).find("td").eq(0).html();
        if(custId != $(this).find("td").eq(1).html()){
            custId = $(this).find("td").eq(1).html();

            var DtQueu = {
                "Idlgn": custId
            }

            q.add(DtQueu);
        }
   });
}

function getJmlRecord(){
    jml = 0;
    $('#dataLap tr').each(function(){
        jml++;
    })
    return jml;
}
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
    <li class="active">Dashboard</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-default">
			<div class="box-body">
				<h4>NOMOR PELANGGAN</h4>
				<input id="idlgn" style="text-align: right; width: 180px" placeholder="No Kontrak Pelanggan" onkeypress="getPelanggan(event)" class="form-control" type="text">
                
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
						<th style="min-width:100px">Harga</th>
						<th style="min-width:100px">Abodemen</th>
						<th style="min-width:100px">Materai</th>
						<th style="min-width:100px">Limbah</th>
						<th style="min-width:100px">Retribusi</th>
						<th style="min-width:100px">DENDA</th>
						<th style="min-width:100px">Angsuran</th>
						<th style="min-width:100px">RP PDAM</th>
						<th style="min-width:100px">ADMIN</th>
						<th style="min-width:100px">TOTAL</th>
						<th style="min-width:200px">ALAMAT</th>
						<th style="min-width:100px">Sub Total</th>
						<th style="min-width:50px">ST.KINI</th>
						<th style="min-width:50px">ST.LALU</th>
                        <th style="min-width:100px">BEBAN TETAP</th>
                        <th style="min-width:100px">BIAYA METER</th>
						
					</tr>
					</thead>
					<tbody id="dataLap">

					</tbody>
				</table>
				</div>

				<script>
					hide();
				</script>

                <hr/>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total" style="font-size: x-large" class="control-label">TOTAL BAYAR</label>
                            <input id="total" readonly="" style="text-align: right; font-size: x-large; height: 50px" placeholder="Total Bayar" class="form-control" type="text">
                        </div>
                        <div class="form-group" > 
                            <label for="bayar" style="font-size: x-large" class="control-label">BAYAR</label>
                            <input id="bayar" onkeypress="hitungKembalianEvent(event)" style="text-align: right; font-size: x-large; height: 50px" placeholder="Rupiah Bayar" class="form-control" type="text">
                        </div>
                        <div class="form-group" >
                            <label for="kembalian" style="font-size: x-large" class="control-label">KEMBALIAN</label>
                            <input id="kembalian" style="text-align: right; font-size: x-large; height: 50px" readonly="" placeholder="Rupiah Kembalian" class="form-control" type="text">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="alert alert-success" id="responseMessage"></div>
                        <div class="alert alert-danger" id="responseFailed"></div>
                        <script>
                            $("#responseMessage").hide();
                            $("#responseFailed").hide();
                        </script>
                    </div>
                </div>

                <button type="button" id="btnPayment" onclick="Payment()" class="btn btn-primary">
                    Bayar</button>
                <button type="button" onclick="CetakUlang()" class="btn btn-primary">
                    Cetak Ulang</button>
                <button type="button" class="btn btn-primary" onclick="clearAll()">
                    Reset</button>
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
            <label for="txtTanggal">Tanggal Transaksi</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control" placeholder="Tanggal Transaksi" id="txtTanggal" type="text" value="<?php echo date("Y-m-d"); ?>">
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
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-primary" onclick="ProsesCetakUlang()">
                    Cetak</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@include('...cetakan/pdambjm')
@include('admin.modals')
</section>
@endsection