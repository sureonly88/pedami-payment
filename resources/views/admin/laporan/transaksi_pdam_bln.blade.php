<html>
	<head>
		<!-- CSS goes in the document HEAD or added to your external stylesheet -->
	<style type="text/css">
		table.gridtable {
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
		<td>KOPERASI PEDAMI<BR/>LAPORAN BULANAN PDAM BANDARMASIH</td>
	</tr>
</table>
<hr/>

<table class="gridtable">
	<tr>
		<th>Tahun</th>
		<th>Bulan</th>
		<th>Loket</th>
		<th>Jumlah</th>
		<th>Sub Total</th>
		<th>Admin</th>
		<th>Total</th>
	</tr>
	<?php
		$Total = 0;
		$SubTotal = 0;
		$Admin = 0;
		$Jml = 0;
	?>
	@foreach($transaksi as $trans)
	<?php
		$conv_total = str_replace(",","",$trans->TOTAL);
		$conv_total = str_replace(".","",$conv_total);
		
		$conv_sub = str_replace(",","",$trans->SUB_TOTAL);
		$conv_sub = str_replace(".","",$conv_sub);
		
		$conv_adm = str_replace(",","",$trans->ADMIN);
		$conv_adm = str_replace(".","",$conv_adm);
		
		$conv_jml = str_replace(",","",$trans->REKENING);
		$conv_jml = str_replace(".","",$conv_jml);

		$Total += $conv_total;
		$SubTotal += $conv_sub;
		$Admin += $conv_adm;
		$Jml += $conv_jml;
	?>
	<tr>
        <td>{{ $trans->TRANSACTION_YEAR }}</td>
        <td>{{ $trans->TRANSACTION_MONTH }}</td>
        <td>{{ $trans->LOKET_CODE }}</td>
        <td align="right">{{ $trans->REKENING }}</td>
        <td align="right">{{ $trans->SUB_TOTAL }}</td>
        <td align="right">{{ $trans->ADMIN }}</td>
        <td align="right">{{ $trans->TOTAL }}</td>

    </tr>
    @endforeach
    <tr>
    	<td colspan="3">TOTAL</td>
		<td align="right">{{ number_format($Jml,0) }}</td>
    	<td align="right">{{ number_format($SubTotal,0) }}</td>
		<td align="right">{{ number_format($Admin,0) }}</td>
    	<td align="right">{{ number_format($Total,0) }}</td>
    </td>

</table>
</body>
</html>