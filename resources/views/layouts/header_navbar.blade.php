<!-- Sidebar toggle button-->
<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
  <span class="sr-only">Toggle navigation</span>
  <span class="icon-bar"></span>
  <span class="icon-bar"></span>
  <span class="icon-bar"></span>
</a>

<div class="navbar-custom-menu" id="notifVue">
  <ul class="nav navbar-nav">
    <!-- Messages: style can be found in dropdown.less-->
    <li class="dropdown messages-menu">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-envelope-o"></i>
        <span class="label label-success" id="notif_num">@{{ countDatas }}</span>
      </a>
      <ul class="dropdown-menu">
        <li class="header" id="notif_num_msg">You have @{{ countDatas }} messages</li>
        <li>
          <!-- inner menu: contains the actual data -->
          <ul class="menu" id="listNotif">
            <li v-for="data in datas"><a href='#'><div class='pull-left'><img src='{{ secure_asset('dist/img/avatar04.png') }}' class='img-circle' alt='User Image'></div><h4>@{{ data.username }}<small><i class='fa fa-clock-o'></i> @{{ data.tgl_request }}</small></h4><p>@{{ data.kode_loket }} Request Topup @{{ data.request_saldo | currency('Rp. ',0) }}</p></a></li>
          </ul>
        </li>
        <li class="footer"><a href="{{ secure_url('/admin/admin_saldo') }}">See All Messages</a></li>
      </ul>
    </li>
    <!-- Notifications: style can be found in dropdown.less -->
    <li class="dropdown notifications-menu">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i>
        <span class="label label-warning">0</span>
      </a>
      <ul class="dropdown-menu">
        <li class="header">You have 0 notifications</li>
        <li>
          <!-- inner menu: contains the actual data -->
          <ul class="menu">
            
          </ul>
        </li>
        <li class="footer"><a href="#">View all</a></li>
      </ul>
    </li>

    <!-- Tasks: style can be found in dropdown.less -->
    
    <!-- User Account: style can be found in dropdown.less -->
    <li class="dropdown user user-menu">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <img src="{{ secure_asset('dist/img/avatar04.png') }}" class="user-image" alt="User Image">
        <span class="hidden-xs">{{ strtoupper($user['username']) }}</span>
      </a>
      <ul class="dropdown-menu">
        <!-- User image -->
        <li class="user-header">
          <img src="{{ secure_asset('dist/img/avatar04.png') }}" class="img-circle" alt="User Image">

          <p>
            {{ strtoupper($user['username']) }} - {{ strtoupper($user['role']) }}
            <small>Loket : {{ $user['loket_code'] }} - {{ $user['loket_name'] }}</small>

          </p>

        </li>
        <!-- Menu Body -->
        <li class="user-body">
            <h4>SALDO : Rp. {{ number_format($user['pulsa'],0) }}</h4>
            
            

        </li>
        <!-- Menu Footer-->
        <li class="user-footer">
          <div class="pull-left">
            <a href="{{ URL::to('admin/profil') }}" class="btn btn-default btn-flat">Profile</a>
          </div>
          <div class="pull-right">
            <a href="{{ URL::to('logout') }}" class="btn btn-default btn-flat">Sign out</a>
          </div>
        </li>
      </ul>
    </li>
    <!-- Control Sidebar Toggle Button -->
  </ul>
</div>