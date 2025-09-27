var socket = io(window.Laravel.redisServer+":3000");

$(document).ready(function() {
    //$("select").select2();

    var vm = new Vue({
      el: '#notifVue',
      data: {
        datas: []
      },

      methods: {
        getNotifs: function () {

          if(window.Laravel.userRole == "admin"){
            // $.getJSON("{{ url('/admin/admin_saldo/notif') }}", function(msg){
            //   vm.datas = msg.data;
            // }).error(function(jqXHR, textStatus, errorThrown){
          
            // });

            // axios.get(window.Laravel.urlNotifikasi)
            //   .then(function (response) {
            //     //console.log(response.data.data);
            //     vm.datas = response.data.data;
            //   })
            //   .catch(function (error) {
            //     console.log(error);
            // });

            this.$http.get(window.Laravel.urlNotifikasi).then(response => {

              console.log(response.body);
              vm.datas = response.body.data;

            }, response => {
              
            });
          }
        }
      },

      computed: {
        countDatas: function () {
          return this.datas.length;
        }
      },

      mounted(){
        this.getNotifs();
      }
    });
    
    socket.on('topup-channel:App\\Events\\TopupEvent', function(data){

      mCode = data['Response']['data']['request_code'];
      mLoket = data['Response']['data']['kode_loket'];
      mUser = data['Response']['data']['username'];

      mPesan = mUser + " - " + mLoket + " Permintaan Topup dengan Kode : " + mCode;

      //console.log(data.Response.original.data.request_code);
      //alert(data.Response.original.message);

      if(window.Laravel.userRole == "admin"){
        $.notify({
          icon: 'fa fa-user',
          title: "<strong>Notifikasi Saldo</strong> : ",
          message: mPesan
        },{
          type: 'success'
        });

        vm.getNotifs();
      }
      
    });

    socket.on('topup-verifikasi-channel:App\\Events\\TopupVerifikasiEvent', function(data){
      mCode = data['Response']['data']['request_code'];
      mStatus = data['Response']['data']['status_verifikasi'];
      mSaldo = data['Response']['data']['verifikasi_saldo'];

      mPesan = "Permintaan Saldo " + mCode + " " + mStatus + " Rp." + mSaldo;

      //alert(data['LoketTopup']);
      //alert(window.Laravel.kodeLoket);

      if(window.Laravel.kodeLoket == data['LoketTopup']){
        $.notify({
          icon: 'fa fa-user',
          title: "<strong>Notifikasi Verifikasi Saldo</strong> : ",
          message: mPesan
        },{
          type: 'success'
        });
      }

    });

    socket.on('login-channel:App\\Events\\LoginEvent', function(data){
      console.log(data);
      //alert(data.UserLogin);
      var PesanLogin = data.UserLogin + " baru saja login ke sistem.";
      // if("{{ Auth::user()->username }}" == data.UserLogin){
      //   PesanLogin = "Selamat datang " + data.UserLogin;
      // }else{
      //   PesanLogin = data.UserLogin + " baru saja login ke sistem.";
      // }

      $.notify({
        icon: 'fa fa-user',
        title: "&nbsp;<strong>Notifikasi Login</strong> : ",
        message: "&nbsp;" + PesanLogin
      },{
        type: 'success'
      });

    });

    socket.on('logout-channel:App\\Events\\LogoutEvent', function(data){
      console.log(data);
      //alert(data.UserLogout);
      $.notify({
        icon: 'fa fa-user',
        title: "&nbsp;<strong>Notifikasi Logout</strong> : ",
        message: data.UserLogout + " baru saja logout dari sistem."
      },{
        type: 'success'
      });
    });
});

function getBulanName(nBulan){
  NamaBulan = "";
  switch(nBulan){
    case 1:
      NamaBulan = "Januari";
      break;
    case 2:
      NamaBulan = "Februari";
      break;
    case 3:
      NamaBulan = "Maret";
      break;
    case 4:
      NamaBulan = "April";
      break;
    case 5:
      NamaBulan = "Mei";
      break;
    case 6:
      NamaBulan = "Juni";
      break;
    case 7:
      NamaBulan = "Juli";
      break;
    case 8:
      NamaBulan = "Agustus";
      break;
    case 9:
      NamaBulan = "September";
      break;
    case 10:
      NamaBulan = "Oktober";
      break;
    case 11:
      NamaBulan = "Nopember";
      break;
    case 12:
      NamaBulan = "Desember";
      break;
  }
  return NamaBulan;
}