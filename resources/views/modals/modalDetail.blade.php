<div class="modal fade" tabindex="-1" role="dialog" id="detailPdambjm">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">DETAIL TRANSAKSI PDAM BANDARMASIH</h4>
      </div>
      <div class="modal-body">

        <div style="width:100%;overflow:auto;">
        <table id="dataTable" class="table table-bordered table-hover table-striped dataTable">
            <thead>
            <tr>
                <th style="min-width:80px">NOPEL</th>
                <th style="min-width:200px">NAMA</th>
                <th style="min-width:50px">IDGOL</th>
                <th style="min-width:50px">BLTH</th>
                <th style="min-width:80px">PAKAI</th>
                <th style="min-width:100px">DENDA</th>
                <th style="min-width:100px">RP PDAM</th>
                <th style="min-width:100px">ADMIN</th>
                <th style="min-width:100px">DISKON</th>
                <th style="min-width:100px">TOTAL</th>
                <th style="min-width:300px">ALAMAT</th>
                <th style="min-width:50px">ST.KINI</th>
                <th style="min-width:50px">ST.LALU</th>
                <th style="min-width:100px">BEBAN TETAP</th>
                <th style="min-width:100px">BIAYA METER</th>
                
            </tr>
            </thead>
            <tbody>
                <tr v-for="Rek in detailPdam">

                    <td>@{{ Rek.idlgn }}</td>
                    <td>@{{ Rek.nama }}</td>
                    <td>@{{ Rek.gol }}</td>
                    <td>@{{ Rek.thbln }}</td>
                    <td>@{{ Rek.pakai }}</td>
                    <td>@{{ Rek.denda | currency('',0) }}</td>
                    <td>@{{ Rek.total | currency('',0) }}</td>
                    <td>@{{ Rek.admin_kop | currency('',0) }}</td>
                    <td>@{{ Rek.diskon | currency('',0) }}</td>
                    <td>@{{ parseInt(Rek.total)+parseInt(Rek.admin_kop) | currency('',0) }}</td>
                    <td>@{{ Rek.alamat }}</td>
                    <td>@{{ Rek.stand_i }}</td>
                    <td>@{{ Rek.stand_l }}</td>
                    <td>@{{ Rek.biaya_tetap | currency('',0) }}</td>
                    <td>@{{ Rek.biaya_meter | currency('',0) }}</td>

                </tr>
            </tbody>
        </table>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="detailPlnPost">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">DETAIL TRANSAKSI PLN POSTPAID</h4>
      </div>
      <div class="modal-body">
            <div style="width:100%;overflow:auto;">
            <table id="dataTable" class="table table-bordered table-hover table-striped dataTable">
                <thead>
                <tr>
                    <th style="min-width:100px">IDPEL</th>
                    <th style="min-width:150px">NAMA PELANGGAN</th>
                    <th style="min-width:100px">KLASIFIKASI</th>
                    <th style="min-width:180px">TOTAL LEMBAR TAGIHAN</th>
                    <th style="min-width:150px">BULAN TAHUN TAGIHAN</th>
                    <th style="min-width:100px">RP TAG PLN</th>
                    <th style="min-width:100px">ADMIN BANK</th>
                    <th style="min-width:100px">TOTAL TAGIHAN</th>
                    <th style="min-width:100px">SISA BILL</th>      
                </tr>
                </thead>
                <tbody id="dataPLN">
                    <tr v-for="bill in detailPlnPost">
                        <td>@{{ bill.subcriber_id }}</td>
                        <td>@{{ bill.subcriber_name }}</td>
                        <td>@{{ bill.subcriber_segment }} / @{{ bill.power_consumtion }}</td>
                        <td>@{{ bill.outstanding_bill }}</td>
                        <td>@{{ bill.periode }}</td>
                        <td>@{{ bill.total_pln | currency('',0) }}</td>
                        <td>@{{ bill.total_admin_charge | currency('',0) }}</td>
                        <td>@{{ bill.total_tagihan | currency('',0) }}</td>
                        <td>@{{ bill.sisa_bill | currency('',0) }}</td>
                    </tr>
                </tbody>
            </table>
            </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>