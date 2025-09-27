<div class="modal fade" tabindex="-1" role="dialog" id="cetakDialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Cetak Ulang</h4>
      </div>
      <div class="modal-body">

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                <label for="pilProduk" class="control-label">
                  Produk :</label>
                <select class="form-control" id="pilProduk" v-model='cuProduk'>
                  <option value="PDAMBJM" >PDAM BANDARMASIH</option>
                  <option value="PLN_POSTPAID" >PLN POSTPAID</option>
                  <option value="PLN_PREPAID" >PLN PREPAID</option>
                  <option value="PLN_NONTAG" >PLN NON TAGLIS</option>
                </select>
              </div>
              </div>

            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                <label for="txtTglAwal" class="control-label">
                  Tanggal Awal :</label>
                <input id="txtTglAwal" type="text" style="width: 100%" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
              </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                <label for="txtTglAkhir" class="control-label">
                  Tanggal Akhir :</label>
                <input id="txtTglAkhir" type="text" style="width: 100%" value="<?php echo date("Y-m-d"); ?>" placeholder="" class="form-control" />
              </div>
              </div>
            </div>

            <label for="ctkIdlgn">ID Pelanggan : </label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input class="form-control" placeholder="Nomor Pelanggan" id="ulangIdpel" v-model="ulangIdpel"  type="text">
            </div>

      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-primary" @click="ProsesCetakUlang()">
                    Cetak</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>