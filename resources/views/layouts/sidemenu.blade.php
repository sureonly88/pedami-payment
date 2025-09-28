<li><a href="{{ secure_url('/admin') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

@if(array_search("Transaksi Pdambjm",$user['permissions']) !== false || 
array_search("Transaksi PLN Postpaid",$user['permissions']) !== false ||
array_search("Transaksi PLN Prepaid",$user['permissions']) !== false ||
array_search("Transaksi PLN Nontaglis",$user['permissions']) !== false)
<li class="header">TRANSAKSI</li>

<li class="treeview">
  <a href="#">
    <i class="fa fa-laptop"></i> <span>Transaksi Pembayaran</span> <i class="fa fa-angle-left pull-right"></i>
  </a>
  <ul class="treeview-menu">
    @if(array_search("Transaksi",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/transaksi_bayar') }}"><i class="fa fa-circle-o"></i>Transaksi Pembayaran</a></li>
    @endif
    @if(array_search("Transaksi Pdambjm",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/pdambjm') }}"><i class="fa fa-circle-o"></i>Pdam Bandarmasih</a></li>
    @endif
    @if(array_search("Transaksi PLN Postpaid",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/pln') }}"><i class="fa fa-circle-o"></i>PLN Pascabayar</a></li>
    @endif
    @if(array_search("Transaksi PLN Prepaid",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/pln_prepaid') }}"><i class="fa fa-circle-o"></i>PLN Prabayar (Token)</a></li>
    @endif
    @if(array_search("Transaksi PLN Nontaglis",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/pln_nontaglis') }}"><i class="fa fa-circle-o"></i>PLN Non Tagihan Listrik</a></li>
    @endif
  </ul>
</li>

<li class="treeview">
  <a href="#">
    <i class="fa fa-laptop"></i> <span>Transaksi Lain</span> <i class="fa fa-angle-left pull-right"></i>
  </a>
  <ul class="treeview-menu">
    <li><a href="{{ secure_url('/admin/request_saldo') }}"><i class="fa fa-circle-o"></i>Tambah Saldo</a></li>
    @if(array_search("Transaksi PLN Prepaid",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/pln_prepaid_cu') }}"><i class="fa fa-circle-o"></i>Cetak Ulang PLN Prabayar</a></li>
    @endif
    @if(array_search("PLN Advise Lunasin",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/pln_prepaid_manual') }}"><i class="fa fa-circle-o"></i>Advise PDAM & PLN Lunasin</a></li>
    @endif
    <li><a href="{{ secure_url('/admin/tes_print') }}"><i class="fa fa-circle-o"></i>Testing Printer</a></li>
    @if(array_search("Transaksi Pdambjm",$user['permissions']) !== false)
    <li><a href="{{ secure_url('/admin/pdam_kolektif') }}"><i class="fa fa-circle-o"></i>Pdambjm Kolektif</a></li>
    @endif
  </ul>
</li>
@endif

@if(array_search("Laporan",$user['permissions']) !== false)
<li class="header">LAPORAN</li>

<li class="treeview">
  <a href="#">
    <i class="fa fa-bar-chart"></i> <span>Laporan Transaksi</span> <i class="fa fa-angle-left pull-right"></i>
  </a>
  <ul class="treeview-menu">
    <li><a href="{{ secure_url('/admin/lap_transaksi') }}"><i class="fa fa-circle-o"></i>Laporan Transaksi Harian</a></li>
    <li><a href="{{ secure_url('/admin/lap_transaksi_bulan') }}"><i class="fa fa-circle-o"></i>Laporan Transaksi Bulanan</a></li>
  </ul>
</li>
@endif

@if(array_search("Setup",$user['permissions']) !== false)
<li class="header">ADMINISTRATOR</li>
<li class="treeview">
  <a href="#">
    <i class="fa fa-tasks"></i> <span>Setup</span> <i class="fa fa-angle-left pull-right"></i>
  </a>
  <ul class="treeview-menu">
    <li><a href="{{ secure_url('/admin/topups') }}"><i class="fa fa-circle-o"></i>Topup Pulsa</a></li>
		<li><a href="{{ secure_url('/admin/users') }}"><i class="fa fa-circle-o"></i>Konfigurasi User</a></li>
		<li><a href="{{ secure_url('/admin/lokets') }}"><i class="fa fa-circle-o"></i>Konfigurasi Loket</a></li>
		<li><a href="{{ secure_url('/admin/register_hps') }}"><i class="fa fa-circle-o"></i>Konfigurasi Handphone</a></li>
    <li><a href="{{ secure_url('/admin/berita') }}"><i class="fa fa-circle-o"></i>Input Berita</a></li>   
    <li><a href="{{ secure_url('/admin/setup_email') }}"><i class="fa fa-circle-o"></i>Setup Email Admin</a></li>
    <li><a href="{{ secure_url('/admin/issue_token') }}"><i class="fa fa-circle-o"></i>Generate Token</a></li>
    <li><a href="{{ secure_url('/admin/roles') }}"><i class="fa fa-circle-o"></i>Roles</a></li>
    <li><a href="{{ secure_url('/admin/permissions') }}"><i class="fa fa-circle-o"></i>Permissions</a></li>
    <li><a href="{{ secure_url('/admin/akses_rek_pdam') }}"><i class="fa fa-circle-o"></i>Akses Rek Pdam</a></li>
  </ul>
</li>
@endif

@if(array_search("Manage Transaksi",$user['permissions']) !== false)
<li class="treeview">
  <a href="#">
    <i class="fa fa-tasks"></i> <span>Manage Transaksi</span> <i class="fa fa-angle-left pull-right"></i>
  </a>
  <ul class="treeview-menu">
    <li><a href="{{ secure_url('/admin/managepdambjm') }}"><i class="fa fa-circle-o"></i>Manage Pdam Bandarmasih</a></li>
    <li><a href="{{ secure_url('/admin/man_transaksi_pln') }}"><i class="fa fa-circle-o"></i>Manage PLN Pascabayar</a></li>
    <li><a href="{{ secure_url('/admin/man_pln_prepaid') }}"><i class="fa fa-circle-o"></i>Manage PLN Prabayar (Token)</a></li>
    <li><a href="{{ secure_url('/admin/man_nontaglis') }}"><i class="fa fa-circle-o"></i>Manage PLN Non Taglis</a></li>
    <li><a href="{{ secure_url('/admin/admin_saldo') }}"><i class="fa fa-circle-o"></i>Persetujuan Topup</a></li>
  </ul>
</li>
@endif

@if(array_search("Rekonsiliasi",$user['permissions']) !== false)
<li class="treeview">
  <a href="#">
    <i class="fa fa-tasks"></i> <span>Rekonsiliasi</span> <i class="fa fa-angle-left pull-right"></i>
  </a>
  <ul class="treeview-menu">
    <li><a href="{{ secure_url('/admin/rekon_pln') }}"><i class="fa fa-circle-o"></i>Rekon Transaksi PLN</a></li>
  </ul>
</li>
@endif

<li class="header">PROFIL</li>
<li><a href="{{ secure_url('/admin/profil') }}"><i class="fa fa-user"></i> <span>Profil Anda</span></a></li>
<li><a href="{{ secure_url('/admin/change_passw') }}"><i class="fa fa-user"></i> <span>Ganti Password</span></a></li>