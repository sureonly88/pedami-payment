<style type="text/css">
	@media print {
	  #printPageButton {
	    display: none;
	  }

	  #pagePrint {
	  	font-size: small;
      	font-family: 'Courier New'; 
      	letter-spacing: 4px;
	  }

	  #pagePrint table {
	  	font-size: small;
      	font-family: 'Courier New'; 
      	letter-spacing: 4px;
	  }
	  
	}
</style>

<style type="text/css" media="print">
    @page 
    {
        size: auto;   /* auto is the initial value */
        margin: 0mm;  /* this affects the margin in the printer settings */
    }
</style>
<div id="pagePrint">

LAPORAN DAFTAR REKAP HARIAN ALL PRODUCT
<br/><br/>
KOPKAR PEDAMI ONLINE PAYMENT
<br/>
Nama User : {{ $transaksi[0]['user_'] }} / {{ $transaksi[0]['loket_name'] }}
<br/>
Di Cetak Pada : <?php echo date('d-m-Y H:i:s') ?>

<hr/>

<table>
	<tr>
		<td width="100px">TANGGAL</td>
		<td width="100px">TRANSAKSI</td>
		<td width="100px" align="right">JML</td>
		<td width="150px" align="right">TAGIHAN</td>
		<td width="150px" align="right">ADMIN</td>
		<td width="150px" align="right">TOTAL</td>
	</tr>
	<?php
		$Jml = 0;
		$Total = 0;
		$SubTotal = 0;
		$Admin = 0;
	?>
	@foreach($transaksi as $trans)
	<?php
		$User = $trans['user_'] . "-" . $trans['loket_name'];
	?>
	<tr>	
        <td>{{ $trans['tanggal'] }}</td>
        <td>{{ $trans['jenis_transaksi'] }}</td>
        <td align="right">{{ number_format($trans['jumlah'],0) }}</td>
		<td align="right">{{ number_format($trans['tagihan'],0) }}</td>
        <td align="right">{{ number_format($trans['admin'],0) }}</td>
        <td align="right">{{ number_format($trans['total'],0) }}</td>
    </tr>
    <?php
    	$Total += $trans['total'];
		$SubTotal += $trans['tagihan'];
		$Admin += $trans['admin'];
		$Jml+= $trans['jumlah'];
    ?>
    @endforeach

</table>
<hr/>
Jumlah : {{ number_format($Jml,0) }}
<br/>
Total Tagihan : Rp. {{ number_format($Total,0) }}

</div>

<br/>

<button type="button" onclick="window.print()" id="printpagebutton">CETAK</button>