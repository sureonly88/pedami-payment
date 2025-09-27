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
      <h3 class="box-title">Daftar Permissions</h3>
    </div>
    <div class="box-body">

        <div class="col-lg-10 col-lg-offset-1">
            <h1><i class="fa fa-key"></i>Daftar Permissions

            <a href="{{ url('/admin/users') }}" class="btn btn-default pull-right">Users</a>
            <a href="{{ route('roles.index') }}" class="btn btn-default pull-right">Roles</a></h1>
            <hr>

            @if(Session::has('flash_message'))
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-info"></i> Message!</h4>
                    {{ Session::get('flash_message') }}
                </div>
            @endif

            <!-- <div class="table-responsive"> -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="Permissions">

                    <thead>
                        <tr>
                            <th>Permissions</th>
                            <th>Operation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td> 
                            <td>
                            <a href="{{ URL::to('admin/permissions/'.$permission->id.'/edit') }}" class="btn btn-info pull-left" style="margin-right: 3px;">Edit</a>

                            {!! Form::open(['method' => 'DELETE', 'route' => ['permissions.destroy', $permission->id] ]) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                            {!! Form::close() !!}

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <br/>
            <a href="{{ URL::to('admin/permissions/create') }}" class="btn btn-success">Add Permission</a>

        </div>
    </div>
</div>
</section>

<script type="text/javascript">
$(document).ready(function() {
    $('#Permissions').dataTable( {
        "ordering": false,    
        "scrollY": "500px",  
        "paging": false,
        "scrollCollapse": true,
        "info": false,
        "searching": false
    });
});
</script>

@endsection