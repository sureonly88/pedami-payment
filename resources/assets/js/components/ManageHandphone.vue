<template>
  <div>
    <section class="content-header">
      <h1>
        Dashboard
        <small>Halaman Kopkar Billing</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Admin</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    <div class="box box-primary">
        <div class="box-header">
          <h3 class="box-title">DAFTAR HANDPHONE</h3>
        </div>
        <div class="box-body">
            <div style="width:100%;overflow:auto;">
            <table class="table table-bordered table-hover table-striped dataTable" id="usersTable">
                <thead>
                    <tr>
                        <th style="min-width: 100px">Aksi</th><th style="min-width: 100px">Username</th><th style="min-width: 150px">IMEI</th>
                    </tr>
                </thead>                
                <tbody>
                    <tr v-for="user in data_list">
                        <td>
                        <button type='button' @click="getEdit(user.id)" class='btn btn-primary btn-xs'>Edit</button>
                        <button type='button' @click="confirmDelete(user.id)" class='btn btn-primary btn-xs'>Delete</button>
                        </td>
                        <td>{{ user.username }}</td>
                        <td>{{ user.imei }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
            <hr/>
            <input type="button" name="tambah" id="tambah" @click="showDialog()" value="Tambah Handphone" class="btn btn-primary btn-sm" />
     
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalUser">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Input Handphone</h4>
          </div>
          <div class="modal-body">
                <form role="form" id="formUser">

                    <input type="text" class="form-control" id="Id" placeholder="Id User" v-model='edit_data.id' readonly="readonly" style="display: none; height: 5px">

                    <div class="form-group">
                      <label for="username">Username</label>
                      <select class="form-control" name="username" id="username" style="width: 100%" v-model="selected">
                        <option v-for="user in user_list" v-bind:value="user.username">{{ user.username }}</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="imei">IMEI Handphone</label>
                      <input type="text" class="form-control" v-model='edit_data.imei' id="imei" placeholder="Enter IMEI">
                    </div>

                    <div id="divPesan"></div>

                </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="btnSimpan" @click="simpanData()">Simpan</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalPesan">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Pesan</h4>
          </div>
          <div class="modal-body">
            <p id="isiPesan"></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalConfirm">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Pesan</h4>
          </div>
          <div class="modal-body">
            <p id="isiConfirm"></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" @click="deleteData(selected_id)" id="btnHapus">Hapus</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalLoading" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">

            <h4 class="modal-title">Loading</h4>
          </div>
          <div class="modal-body">
            <p id="isiLoading"></p>
          </div>

        </div>
      </div>
    </div>

    <div style="visibility: collapse;" id="ErrorMessage">
    <div class="alert alert-danger" >
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Error!</h4>
        <div id="pesanError"></div>
    </div>
    </div>

    <div style="visibility: collapse;" id="SuccessMessage">
    <div class="alert alert-success" >
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> Success!</h4>
        <div id="pesanSimpan"></div>
    </div>
    </div>
    </section>
  </div>
</template>

<script>
    export default {
      data() {
          return {
              selected: '',
              data_list: [],
              user_list: [],
              edit_data : {
                id: '',
                imei: '',
                username: ''
              },
              selected_id : ''
          };
      },

      ready() {
          this.prepareComponent();
      },

      mounted() {
          this.prepareComponent();
      },

      methods: {
        /**
         * Prepare the component.
         */
        prepareComponent() {
          this.LoadData();
          $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        },

        LoadData() {

          axios.get('/admin/register_hps/get_all')
            .then(response => {
              //console.log(response.data.data);
              this.data_list = response.data.data;
            });

          axios.get('/admin/users/get_all')
            .then(response => {
              //console.log(response.data.data);
              this.user_list = response.data.data;
            });

          this.Kosongkan();
        },

        Kosongkan() {
          $("#Id").val("");
          $("#nama").val("");
          $("#alamat").val("");
          $("#loket_code").val("");
          $("#blok_message").val("");
          $("#byadmin").val("");
          $('#is_blok').prop('checked', false); 
        },

        showDialog() {
          $("#Id").val('');
          $("#divPesan").html('');
          this.Kosongkan();

          $('#modalUser').modal("show");
        },

        simpanData() {
          alert();
        },

        sentAjax(mUrl, mData){
            $("#btnSimpan").attr("disabled", true);
            $.ajax({
                method: "POST",
                url: mUrl,
                data: { Data: mData,
                       _token: "{{ csrf_token() }}" }
            })
            .done(function(msg) {
                $("#PesanSimpan").html('');

                if(msg.status == "Success"){

                    $("#pesanSimpan").html(msg.message);
                    $("#divPesan").html( $("#SuccessMessage").html() );

                    this.LoadData();
                }else{
                    mPesan = "";
                    for(i=0;i<msg.message.length; i++){
                        mPesan += "- " + msg.message[i] + "<br/>";
                    }
                    $("#pesanError").html(mPesan);
                    $("#divPesan").html( $("#ErrorMessage").html() );
                }

                $("#btnSimpan").attr("disabled", false);

            });
        },

        getEdit(mId){
          axios.get('/admin/register_hps/get/'+mId)
            .then(response => {
              var mData = response.data.data;
              var mStatus = response.data.status;

              this.edit_data.id = mData.id;
              this.edit_data.imei = mData.imei;
              this.edit_data.username = mData.username;

              //$("#username").val(mData.username).change();
              
              $("#divPesan").html('');
              $('#modalUser').modal("show");
            });
        },

        confirmDelete(mId){
          this.selected_id = mId;

          $("#isiConfirm").html("Hapus Loket "+mId+"?");
          $('#modalConfirm').modal("show");
        },

        deleteData(mId){
            
            axios.post('/admin/register_hps/delete/'+mId, { id: mId })
              .then(response => {

                var mData = response.data;

                if(mData.status == "Success"){
                  $('#modalConfirm').modal("hide");

                  this.showPesan(mData.message);
                  this.LoadData();
                }else{
                  $('#modalConfirm').modal("hide");
                  this.showPesan(mData.message);
                }

              });
        },

        showPesan(mPesan){
            $("#isiPesan").html(mPesan);
            $('#modalPesan').modal("show");
        }

      }

    }
</script>