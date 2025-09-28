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
		<td><img src="{{ secure_asset('images/kopkar/koperasi_logo.gif') }}" style="width:50px;height:50px;"> 
		</td>
		<td>KOPERASI PEDAMI<BR/>LAPORAN TRANSAKSI ANDROID PDAM BANDARMASIH</td>
	</tr>
</table>
<hr/>
<table class="gridtable">
	<tr>
		<td>KODE LOKET</td>
		<td>: @foreach($loket as $lok) {{ strtoupper($lok->loket_code) }}, @endforeach</td>
	</tr>
	<tr>
		<td>NAMA</td>
		<td>: @foreach($loket as $lok) {{ strtoupper($lok->nama) }},  @endforeach</td>
	</tr>
</table>
<br/>
<table class="gridtable">
	<tr>
		<th>NOPEL</th>
		<th width="200px">NAMA</th>
		<th>USERNAME</th>			
		<th>LOKET</th>
		<th>TANGGAL</th>
		<th>JML</th>
		<th>SUBTOTAL</th>
		<th>ADMIN</th>
		<th>TOTAL</th>
	</tr>
	<?php
		$Jml = 0;
		$Total = 0;
		$SubTotal = 0;
		$Admin = 0;
	?>
	@foreach($transaksi as $trans)
	<?php
		$conv_total = str_replace(",","",$trans->TOTAL);
		$conv_total = str_replace(".","",$conv_total);
		
		$conv_sub = str_replace(",","",$trans->SUB_TOTAL);
		$conv_sub = str_replace(".","",$conv_sub);
		
		$conv_adm = str_replace(",","",$trans->ADMIN);
		$conv_adm = str_replace(".","",$conv_adm);

		$Jml += $trans->JML;
		$Total += $conv_total;
		$SubTotal += $conv_sub;
		$Admin += $conv_adm;
	?>
	<tr>
        <td>{{ $trans->CUST_ID }}</td>
        <!--<td>{{ $trans->NAMA }}</td>-->
		<td>{{ str_limit($trans->NAMA, $limit = 30, $end = '...') }}</td>
        <td>{{ $trans->USERNAME }}</td>       
        <td>{{ $trans->LOKET_CODE }}</td>
        <td>{{ $trans->TRANSACTION_DATE }}</td>
        <td>{{ $trans->JML }}</td>
		<td align="right">{{ $trans->SUB_TOTAL }}</td>
        <td align="right">{{ $trans->ADMIN }}</td>
        <td align="right">{{ $trans->TOTAL }}</td>
    </tr>
    @endforeach
    <tr>
    	<td colspan="5">TOTAL</td>
    	<td align="right">{{ number_format($Jml,0) }}</td>
		<td align="right">{{ number_format($SubTotal,0) }}</td>
		<td align="right">{{ number_format($Admin,0) }}</td>
    	<td align="right">{{ number_format($Total,0) }}</td>
    </td>

</table>
</body>
</html>