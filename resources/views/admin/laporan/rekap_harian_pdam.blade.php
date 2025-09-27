<html>
	<head>
		<!-- CSS goes in the document HEAD or added to your external stylesheet -->
	<style type="text/css">
		table.gridtable {
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
		<td>KOPERASI PEDAMI<BR/>LAPORAN REKAP TRANSAKSI PDAM BANDARMASIH</td>
	</tr>
</table>
<hr/>
<table class="gridtable">
	<tr>
		<th>TGL TRANSAKSI</th>
		<th>USER</th>
		<th>KODE LOKET</th>
		<th>NAMA LOKET</th>
		<th>REKENING</th>
		<th>SUB TOTAL</th>
		<th>ADMIN</th>
		<th>TOTAL</th>
	</tr>
	<?php
		$RekTotal = 0;
		$SubTotal = 0;
		$AdmTotal = 0;
		$GrandTotal = 0;
	?>
	@foreach($transaksi as $trans)
	<?php
		$Rek = str_replace(",","",$trans->REKENING);
		$Rek = str_replace(".","",$Rek);
		$RekTotal += $Rek;

		$Sub = str_replace(",","",$trans->SUB_TOTAL);
		$Sub = str_replace(".","",$Sub);
		$SubTotal += $Sub;

		$Adm = str_replace(",","",$trans->ADMIN);
		$Adm = str_replace(".","",$Adm);
		$AdmTotal += $Adm;

		$Total = str_replace(",","",$trans->TOTAL);
		$Total = str_replace(".","",$Total);
		$GrandTotal += $Total;
	?>
	<tr>
        <td>{{ $trans->TRANSACTION_DATE }}</td>
		<td>{{ $trans->USER }}</td>
        <td>{{ $trans->LOKET_CODE }}</td>
        <td>{{ $trans->LOKET_NAME }}</td>
        <td align="right">{{ $trans->REKENING }}</td>
        <td align="right">{{ $trans->SUB_TOTAL }}</td>
        <td align="right">{{ $trans->ADMIN }}</td>
        <td align="right">{{ $trans->TOTAL }}</td>
    </tr>
    @endforeach
    <tr>
    	<td colspan="4">TOTAL</td>
    	<td align="right">{{ number_format($RekTotal,0) }}</td>
    	<td align="right">{{ number_format($SubTotal,0) }}</td>
    	<td align="right">{{ number_format($AdmTotal,0) }}</td>
    	<td align="right">{{ number_format($GrandTotal,0) }}</td>
    </td>

</table>
</body>
</html>