<div class="modal fade" tabindex="-1" role="dialog" id="cetakDialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Cetak Ulang</h4>
      </div>
      <div class="modal-body">

            <label for="ctkIdlgn">NOMOR METER : </label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input class="form-control" placeholder="Nomor Meter" id="ulangIdpel" v-model="ulangIdpel"  type="text">
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