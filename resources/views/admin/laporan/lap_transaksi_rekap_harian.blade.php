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
		<td>KOPERASI PEDAMI<BR/>REKAP TRANSAKSI HARIAN PEDAMI PAYMENT</td>
	</tr>
</table>

<hr/>
<table cellpadding="0" cellspacing="0" border="0">
	<tr> 
		<td>TANGGAL</td>
		<td>: {{ $tgl_awal }} s/d {{ $tgl_akhir }}</td>
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
		<th width="100px">TANGGAL</th>
		<th width="100px">USER</th>
		<th width="100px">KODE LOKET</th>
		<th width="150px">NAMA LOKET</th>			
		<th width="100px">JENIS LOKET</th>
		<th width="100px">JENIS TRANSAKSI</th>
		<th width="100px">JML</th>
		<th width="100px">TAGIHAN</th>
		<th width="100px">ADMIN</th>
		<th width="100px">TOTAL</th>
	</tr>
	<?php
		$SubTotal = 0;
		$Admin = 0;
		$Total = 0;
		$Jumlah = 0;
	?>
	@foreach($transaksi as $trans)
	<?php
		$Total += $trans->total;
		$SubTotal += $trans->tagihan;
		$Admin += $trans->admin;
		$Loket = $trans->loket_code . "-" . $trans->loket_name;
	?>
	<tr>
		
        <td>{{ $trans->tanggal }}</td>
        <td>{{ $trans->user_ }}</td>
        <td>{{ $trans->loket_code}}</td>
        <td>{{ $trans->loket_name}}</td>
		<!-- <td>{{ str_limit($trans->NAMA, $limit = 10, $end = '...') }}</td> -->
<!--         <td>{{ $trans->loket_code }}</td>       
        <td>{{ str_limit($trans->loket_name, $limit = 20, $end = '...') }}</td> -->
        <td>{{ $trans->jenis_loket }}</td>
        <td>{{ $trans->jenis_transaksi }}</td>
        <td align="right">{{ number_format($trans->jumlah,0) }}</td>
		<td align="right">{{ number_format($trans->tagihan,0) }}</td>
        <td align="right">{{ number_format($trans->admin,0) }}</td>
        <td align="right">{{ number_format($trans->total,0) }}</td>
    </tr>
    	{{ $Jumlah += $trans->jumlah }}
    @endforeach
    <tr>
    	<td colspan="6">TOTAL</td>
    	<td align="right">{{ number_format($Jumlah,0) }}</td>
		<td align="right">{{ number_format($SubTotal,0) }}</td>
		<td align="right">{{ number_format($Admin,0) }}</td>
    	<td align="right">{{ number_format($Total,0) }}</td>
    </td>

</table>
</body>
</html>