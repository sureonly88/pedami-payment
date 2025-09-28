@extends('...layouts/template')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Dashboard
    <small>Halaman Pedami Payment</small>
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
      <h3 class="box-title">GANTI PASSWORD</h3>
    </div>
    <div class="box-body">

    <form action="{{ secure_url('admin/change_passw/edit') }}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        
        <label>Username</label>
        <input type="text" name="username" id="username" value="{{ $user['username'] }}" readonly="readonly" class="form-control" />

    </div>

     <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" value="" id="password" class="form-control" />
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary form-control">Ganti Password</button>

    </div>
    </form>

    @if (isset($pesan))
    <div class="alert alert-success">
        <h4><i class="icon fa fa-check"></i>Berhasil</h4>
            {{ $pesan }}
      </div>
    @endif

    @if (isset($error))
    <div class="alert alert-danger">
        <h4><i class="icon fa fa-check"></i>Gagal</h4>
            {{ $error }}
      </div>
    @endif


</div>
</div>
</section>
@endsection