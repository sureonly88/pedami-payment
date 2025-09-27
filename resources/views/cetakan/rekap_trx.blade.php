<div style="display: none" id="CetakRekapTrx">
    <table border="0" style="font-size: small; font-family: 'Courier New'; letter-spacing: 4px" cellpadding="0" cellspacing="0">
        <tr>
            <td colspan="6">REKAP TAGIHAN</td>
        </tr>
        <tr>
            <td>Dicetak Oleh</td>
            <td colspan="5">: {!!$user['username']!!} - {!!$user['loket_code']!!} {!!$user['loket_name']!!}</td>
        </tr>
        <tr>
            <td>Waktu Cetak</td>
            <td colspan="5">: <?php echo date('d-m-Y'); ?></td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr>
            <td min-width="30px">:Nomor</td>
            <td min-width="100px">:Produk</td>
            <td min-width="100px">:Nopel</td>
            <td min-width="200px">:Nama</td>
            <td min-width="150px">:Periode</td>
<!--             <td min-width="150px">:Sub Total</td>
            <td min-width="150px">:Admin</td> -->
            <td min-width="150px">:Total</td>
        </tr>
        <tr>
            <td colspan="6">======================================================================</td>
        </tr>
        <tr v-for="(Rek, index) in dataRek">
            <td>:@{{ index+1 }}</td>
            <td>:@{{ Rek.data.produk }}</td>
            <td>:@{{ Rek.data.idpel }}</td>
            <td>:@{{ Rek.data.nama }}</td>
            <td>:@{{ Rek.data.periode }}</td>
     <!--        <td>:@{{ Rek.data.sub_total | currency('',0) }}</td> -->
<!--             <td>:@{{ Rek.data.admin | currency('',0) }}</td> -->
            <td>:@{{ Rek.data.total | currency('',0) }}</td> 
        </tr>
        <tr>
            <td colspan="7">======================================================================</td>
        </tr>
        <tr>
            <td colspan="4">:TOTAL</td>
<!--             <td>:@{{ totalSub }}</td>
            <td>:@{{ totalAdmin }}</td> -->
            <td>:@{{ totalBayar }}</td>
        </tr>
    </table>
</div>