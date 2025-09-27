<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pedami Payment | Billing</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="{{ URL::asset('plugins/select2/select2.min.css') }}">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="{{ URL::asset('bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css"> -->
  <!-- Ionicons -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css"> -->
  <!-- Theme style -->

  <link rel="stylesheet" href="{{ URL::asset('font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('ionicons/css/ionicons.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('dist/css/AdminLTE.min.css') }}">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="{{ URL::asset('dist/css/skins/_all-skins.min.css') }}">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <link rel="stylesheet" href="{{ URL::asset('jquery-ui/jquery-ui.min.css') }}">

  <link rel="stylesheet" href="{{ URL::asset('styles/dataTables.bootstrap.css') }}">

  <script src="{{ URL::asset('plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
  <script src="{{ URL::asset('jquery-ui/jquery-ui.min.js') }}"></script>

  <script src="{{ URL::asset('js/jqmq.js') }}"></script>
  <script src="{{ URL::asset('plugins/select2/select2.full.min.js') }}"></script>
  <script src="{{ URL::asset('js/jquery.dataTables.min.js') }}"></script>  
  <script src="{{ URL::asset('js/dataTables.bootstrap.min.js') }}"></script>
  <script src="{{ URL::asset('js/numeral.min.js') }}"></script>

  @if (env('APP_DEBUG'))
  <script src="{{ URL::asset('vuejs/vue.js') }}"></script>
  @else
  <script src="{{ URL::asset('vuejs/vue.min.js') }}"></script>
  @endif
  <script src="{{ URL::asset('js/axios.min.js') }}"></script>
  <script src="{{ URL::asset('js/linq.min.js') }}"></script>

<!--   <script src="https://unpkg.com/vue"></script> -->
<!--   <script src="https://unpkg.com/axios/dist/axios.min.js"></script> -->
<!--   <script src="https://cdn.jsdelivr.net/vue2-filters/0.1.7/vue2-filters.min.js"></script>
  <script src="https://cdn.jsdelivr.net/vue.resource/1.2.1/vue-resource.min.js"></script> -->

  <script src="{{ URL::asset('js/vue2-filters.min.js') }}"></script>
  <script src="{{ URL::asset('js/vue-resource.min.js') }}"></script>

  <script>
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

      window.Laravel = {!! json_encode([
          'csrfToken' => csrf_token(),
          'redisServer' => env('REDIS_SERVER_HOST'),
          'userRole' => Auth::user()->role,
          'kodeLoket' => $user['loket_code'],
          'urlNotifikasi' => url('admin/admin_saldo/notif'),
          'urlRefreshSaldo' => url('admin/cek_pulsa/{kode}')
      ]) !!};

      Vue.http.options.root = '/root';
      Vue.http.headers.common['csrfToken'] = "{{ csrf_token() }}";

  </script>

  <style type="text/css">
      body { padding-right: 0 !important }
  </style>

</head>
<body class="hold-transition skin-blue sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>P</b>PY</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>PEDAMI</b>PAYMENT</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      @include('.../layouts/header_navbar')
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{ URL::asset('dist/img/avatar04.png') }}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ strtoupper($user['username']) }} - {{ strtoupper($user['role']) }}</p>
          <a href="#" id="glSisaPulsa">Saldo : Rp. 0</a>
        </div>
      </div>
      <!-- search form -->
      <!-- <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form> -->
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        @include('.../layouts/sidemenu')

      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- =============================================== -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

    @yield('content')

  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 1.2.0
    </div>
    <strong>Copyright &copy; 2015-2017 <a href="#">sureonly88@gmail.com</a>.</strong> All rights
    reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- Js untuk hasil Laravel Elixir -->
<!-- <script src="{{ URL::asset('js/app.kopkar.js') }}"></script> -->

<script src="{{ URL::asset('bootstrap/js/bootstrap.min.js') }}"></script>
<!-- SlimScroll -->
<script src="{{ URL::asset('plugins/slimScroll/jquery.slimscroll.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ URL::asset('plugins/fastclick/fastclick.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ URL::asset('dist/js/app.min.js') }}"></script>

<link rel="stylesheet" href="{{ URL::asset('css/animate.css') }}">
<script src="{{ URL::asset('js/bootstrap-notify.min.js') }}"></script>

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.7.3/socket.io.min.js"></script> -->
<script src="{{ URL::asset('js/socket.io.min.js') }}"></script>

<script src="{{ URL::asset('app/kopkar.js') }}"></script>

<script src="{{ URL::asset('printing/dependencies/rsvp-3.1.0.min.js') }}"></script>
<script src="{{ URL::asset('printing/dependencies/sha-256.min.js') }}"></script>
<script src="{{ URL::asset('printing/qz-tray.js') }}"></script>
<script src="{{ URL::asset('printing/print_java.js') }}"></script>

<script type="text/javascript">
$(document).ready(function() {
    //startConnection();
    RefreshSaldo();
});

function RefreshSaldo(){
  var KodeLoket = window.Laravel.kodeLoket;
  var vurl = window.Laravel.urlRefreshSaldo;
  vurl = vurl.replace("{kode}",KodeLoket);

  axios.get(vurl)
    .then(function (response) {
      var sisaSaldo = "Saldo : Rp. " + numeral(Number(response.data.pulsa)).format('0,0');
      //console.log(sisaSaldo);
      $("#glSisaPulsa").html(sisaSaldo);
    })
    .catch(function (error) {
      console.log(error);
  });
}

</script>

</body>
</html>
