<p>Hi {{ $kodeloket }},</p>
<p>
  Permintaan saldo anda Rp. {{ number_format($saldo,0) }} sudah diverifikasi oleh admin dengan hasil : 
</p>
<p>
  <table border="0" cellpadding="0" cellspacing="0">
    <tbody>
      <tr>
        <td>Kode Permintaan</td><td>: {{ $code }} </td>
      </tr>
      <tr>
        <td>Kode Loket</td><td>: {{ $kodeloket }} </td>
      </tr>
      <tr>
        <td>Status Permintaan</td><td>: {{ $status }} </td>
      </tr>
      <tr>
        <td>Catatan Verifikasi</td><td>: {{ $ket }} </td>
      </tr>
      <tr>
        <td>Tanggal Verifikasi</td><td>: {{ $tanggal }} </td>
      </tr>
    </tbody>
  </table>
</p>

<p>Silahkan lakukan pengecekan di Website Kopkar Pedami Online Payment.</p>
<p>Terima Kasih.</p>

<p>
  Kopkar Pedami Online Payment<br/>
  Created By <a href="sureonly88@gmail.com">sureonly88@gmail.com</a>.<br/>
</p>
