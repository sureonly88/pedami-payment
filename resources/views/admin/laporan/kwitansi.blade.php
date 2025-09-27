<html>
	<head>
		
	</head>
<body>
TANDA TERIMA PAYMENT POINT ANDROID
<hr/>

Nama User : {!!$loket['kodeloket']!!} <BR/><BR/>
No Pelanggan : 
<?php
	$Total = 0;
?>
@foreach($data as $detail)
	<?php
		$conv_total = str_replace(",","",$detail->TOTAL);
		$conv_total = str_replace(".","",$conv_total);
		$Total += $conv_total;
	?>
	{{ $detail->CUST_ID }}
@endforeach
<BR/><BR/>
Total Setoran : {{ number_format($Total,0) }}

<BR/><BR/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Banjarmasin : {{ date('d-m-Y') }}
</body>

<script>
	jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);

	jsPrintSetup.setOption('marginTop', 5);
	jsPrintSetup.setOption('marginBottom', 0);
	jsPrintSetup.setOption('marginLeft', 5);
	jsPrintSetup.setOption('marginRight', 0);

	jsPrintSetup.setOption('headerStrLeft', '');
	jsPrintSetup.setOption('headerStrCenter', '');
	jsPrintSetup.setOption('headerStrRight', '');

	jsPrintSetup.setOption('footerStrLeft', '');
	jsPrintSetup.setOption('footerStrCenter', '');
	jsPrintSetup.setOption('footerStrRight', '');

	jsPrintSetup.setOption('printSilent', 1);

	jsPrintSetup.print();
</script>
</html>