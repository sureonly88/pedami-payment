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
            <h1><i class="fa fa-key"></i> Roles

            <a href="{{ secure_url('/admin/users') }}" class="btn btn-default pull-right">Users</a>
            <a href="{{ route('permissions.index') }}" class="btn btn-default pull-right">Permissions</a></h1>
            <hr>

            @if(Session::has('flash_message'))
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-info"></i> Message!</h4>
                    {{ Session::get('flash_message') }}
                </div>
            @endif

            <!-- <div class="table-responsive"> -->
            <div>
                <table class="table table-bordered table-striped" id="listRoles">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Operation</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($roles as $role)
                        <tr>

                            <td>{{ $role->name }}</td>

                            <td>{{  $role->permissions()->pluck('name')->implode(' ') }}</td>{{-- Retrieve array of permissions associated to a role and convert to string --}}
                            <td>
                            <a href="{{ URL::to('admin/roles/'.$role->id.'/edit') }}" class="btn btn-info pull-left" style="margin-right: 3px;">Edit</a>

                            {!! Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id] ]) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                            {!! Form::close() !!}

                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
            <br/>
            <a href="{{ URL::to('admin/roles/create') }}" class="btn btn-success">Add Role</a>

        </div>
    </div>
</div>
</section>

<script type="text/javascript">
$(document).ready(function() {

    $('#listRoles').dataTable( {
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