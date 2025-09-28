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
		<td><img src="{{ secure_asset('images/kopkar/koperasi_logo.gif') }}" style="width:50px;height:50px;"> </td>
		<td>KOPERASI PEDAMI<BR/>DETAIL TRANSAKSI HARIAN PEDAMI PAYMENT</td>
	</tr>
</table>

<hr/>
<table cellpadding="0" cellspacing="0" border="0">
	<tr> 
		<td>TANGGAL</td>
		<td>: {{ $tanggal }}</td>
	</tr>
	<tr> 
		<td>USER</td>
		<td>: {{ $user_ }}</td>
	</tr>
	<tr> 
		<td>KODE LOKET</td>
		<td>: {{ $kode_loket }}</td>
	</tr>
	<tr> 
		<td>JENIS TRANSAKSI</td>
		<td>: {{ $jenis_transaksi }}</td>
	</tr>

</table>

<hr/>

<table class="gridtable">
	<tr>
		<th>NO</th>
		<th>IDPEL</th>
		<th>NAMA</th>
		<th>JENIS</th>			
		<th>PERIODE</th>
		<th>TANGGAL</th>
		<th>LOKET</th>
		<th>USER</th>
		<th>TAGIHAN</th>
		<th>ADMIN</th>
		<th>TOTAL</th>
		
	</tr>
	<?php
		$SubTotal = 0;
		$Admin = 0;
		$Total = 0;
		$No = 1;
	?>
	@foreach($transaksi as $trans)
	<?php
		$Total += $trans->total;
		$SubTotal += $trans->tagihan;
		$Admin += $trans->admin;
	?>
	<tr>
		<td>{{ $No }}</td>
        <td>{{ $trans->idpel }}</td>
        <td>{{ str_limit($trans->nama, $limit = 20, $end = '...') }}</td>
        <td>{{ $trans->jenis_transaksi }}</td>
        <td>{{ $trans->periode }}</td>     
        <td>{{ $trans->tanggal }}</td> 
        <td>{{ $trans->loket_code }}</td> 
        <td>{{ $trans->user_ }}</td>   
		<td align="right">{{ number_format($trans->tagihan,0) }}</td>
        <td align="right">{{ number_format($trans->admin,0) }}</td>
        <td align="right">{{ number_format($trans->total,0) }}</td>
        {{ $No++ }}
    </tr>
    @endforeach
    <tr>
    	<td colspan="8">TOTAL</td>
		<td align="right">{{ number_format($SubTotal,0) }}</td>
		<td align="right">{{ number_format($Admin,0) }}</td>
    	<td align="right">{{ number_format($Total,0) }}</td>
    </td>

</table>
</body>
</html>