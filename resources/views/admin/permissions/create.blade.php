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
      <h3 class="box-title">DAFTAR BERITA</h3>
    </div>
    <div class="box-body">

        <div class='col-lg-4 col-lg-offset-4'>

            {{-- @include ('errors.list') --}}

            <h1><i class='fa fa-key'></i> Add Permission</h1>
            <br>

            {{ Form::open(array('url' => 'admin/permissions')) }}

            <div class="form-group">
                {{ Form::label('name', 'Name') }}
                {{ Form::text('name', '', array('class' => 'form-control')) }}
            </div>
            <br>

            @if(!$roles->isEmpty())

                <h4>Assign Permission to Roles</h4>

                @foreach ($roles as $role) 
                    {{ Form::checkbox('roles[]',  $role->id ) }}
                    {{ Form::label($role->name, ucfirst($role->name)) }}<br>

                @endforeach

            @endif
            
            <br>
            {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}

            {{ Form::close() }}

        </div>
    </div>
</div>
</section>

@endsection