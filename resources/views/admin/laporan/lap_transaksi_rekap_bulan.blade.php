<?php
function getBulanName($nBulan){
  $NamaBulan = "";
  switch($nBulan){
    case 1:
      $NamaBulan = "Januari";
      break;
    case 2:
      $NamaBulan = "Februari";
      break;
    case 3:
      $NamaBulan = "Maret";
      break;
    case 4:
      $NamaBulan = "April";
      break;
    case 5:
      $NamaBulan = "Mei";
      break;
    case 6:
      $NamaBulan = "Juni";
      break;
    case 7:
      $NamaBulan = "Juli";
      break;
    case 8:
      $NamaBulan = "Agustus";
      break;
    case 9:
      $NamaBulan = "September";
      break;
    case 10:
      $NamaBulan = "Oktober";
      break;
    case 11:
      $NamaBulan = "Nopember";
      break;
    case 12:
      $NamaBulan = "Desember";
      break;
  }
  return $NamaBulan;
}
?>

<html>
	<head>
		<!-- CSS goes in the document HEAD or added to your external stylesheet -->
	<style type="text/css">
		table.gridtable {
			width: 100%;
			font-family: verdana,arial,sans-serif;
			font-size:11px;
			color:#333333;
			border-width: 1px;
			border-color: #666666;
			border-collapse: collapse;
		}
		table.gridtable th {
			border-width: 1px;
			padding: 5px;
			border-style: solid;
			border-color: #666666;
			background-color: #dedede;
		}
		table.gridtable td {
			border-width: 1px;
			padding: 5px;
			border-style: solid;
			border-color: #666666;
			background-color: #ffffff;
		}
	</style>
	</head>
<body>
<table>
	<tr>
		<td><img src="{{ URL::asset('images/kopkar/koperasi_logo.gif') }}" style="width:50px;height:50px;"> </td>
		<td>KOPERASI PEDAMI<BR/>REKAP TRANSAKSI BULANAN PEDAMI PAYMENT</td>
	</tr>
</table>

<hr/>
<table cellpadding="0" cellspacing="0" border="0">
	<tr> 
		<td>BULAN</td>
		<td>: {{ getBulanName($bulan) }} - {{ $tahun }}</td>
	</tr>
	@if($kode_loket != "-")
	<tr> 
		<td>KODE LOKET</td>
		<td>: {{ $kode_loket[0] }}</td>
	</tr>
	@endif
	@if($jenis_transaksi != "-")
	<tr> 
		<td>JENIS TRANSAKSI</td>
		<td>: {{ $jenis_transaksi[0] }}</td>
	</tr>
	@endif
	@if($jenis_loket != "-")
	<tr> 
		<td>JENIS LOKET</td>
		<td>:  {{ $jenis_loket[0] }}</td>
	</tr>
	@endif

</table>

<hr/>

<table class="gridtable">
	<tr>
		<th>KODE LOKET</th>
		<th>NAMA LOKET</th>			
		<th>JENIS LOKET</th>
		<th>JENIS TRANSAKSI</th>
		<th>JML</th>
		<th>TAGIHAN</th>
		<th>ADMIN</th>
		<th>TOTAL</th>
	</tr>
	<?php
		$SubTotal = 0;
		$Admin = 0;
		$Total = 0;
		$Jumlah = 0
	?>
	@foreach($transaksi as $trans)
	<?php
		$Total += $trans->total;
		$SubTotal += $trans->tagihan;
		$Admin += $trans->admin;
		$Jumlah += $trans->jumlah;
	?>
	<tr>
		<!-- <td>{{ str_limit($trans->NAMA, $limit = 10, $end = '...') }}</td> -->
        <td>{{ $trans->loket_code }}</td>       
        <td>{{ $trans->loket_name }}</td>
        <td>{{ $trans->jenis_loket }}</td>
        <td>{{ $trans->jenis_transaksi }}</td>
        <td align="right">{{ number_format($trans->jumlah,0) }}</td>
		<td align="right">{{ number_format($trans->tagihan,0) }}</td>
        <td align="right">{{ number_format($trans->admin,0) }}</td>
        <td align="right">{{ number_format($trans->total,0) }}</td>
    </tr>
    @endforeach
    <tr>
    	<td colspan="4">TOTAL</td>
    	<td align="right">{{ number_format($Jumlah,0) }}</td>
		<td align="right">{{ number_format($SubTotal,0) }}</td>
		<td align="right">{{ number_format($Admin,0) }}</td>
    	<td align="right">{{ number_format($Total,0) }}</td>
    </td>

</table>
</body>
</html>