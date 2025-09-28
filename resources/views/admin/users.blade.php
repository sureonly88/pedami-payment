@extends('.../layouts/template')

@section('content')
<script src="{{ secure_asset('plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<script src="{{ secure_asset('plugins/datatables/jquery.dataTables.min.js') }}"></script> 

<script>
$( "#menuUsers" ).prop( "class", "active" );

$(document).ready(function() {
    $("select").select2();
});

function getListUsers(){
    dtTable = $('#dataTable').dataTable( {
        "ajax": "{{ url('admin/users/list') }}",
        "destroy": true,
        "columns": [
            { "data": "username" },
            { "data": "role" },
            { "data": "loket_code" },
            { "data": "created_at" },
            { "data": "updated_at" },
            { "data": "aksi" }
        ]
    });
}

function simpanUser(){
    cId = $("#id").val();
    cUsername = $("#username").val();
    cPassword = $("#password").val();
    cEmail = $("#email").val();
    cLoketId = $("#kodeloket").val();
    cRole = $("#role").val();

    $.ajax({
        method: "POST",
        url: "{{ url('/admin/users') }}",
        data: { id: cId,
                username: cUsername,
                password: cPassword,
                email: cEmail,
                kodeloket: cLoketId,
                role: cRole,
                _token: "{{ csrf_token() }}" }
    })
    .done(function(msg) {
        alert( "Data Saved: " + msg.message );
        getListUsers();
    });
}

function Empty(){
    $("#id").val("");
    $("#username").val("");
    $("#password").val("");
    $("#email").val("");
}

function editUser(id){
    $.getJSON("{{ url('/admin/users/get') }}"+"/"+id, function(data){
        if(data.status == "Success"){
            $("#id").val(id);
            $("#username").val(data.data.username);
            $("#password").val("");
            $("#email").val(data.data.email);

            $loket_id = data.data.loket_id;
            //$("#kodeloket").val($loket_id);

            $("#kodeloket").find('option').each(function( i, opt ) {
                //console.log(opt.value + "==" + $loket_id);
                if(opt.value == $loket_id )
                    $(opt).attr('selected', 'selected');
            });

            $role = data.data.role;
            $("#role").find('option').each(function( i, opt ) {
                //console.log(opt.value + "==" + $loket_id);
                if(opt.value == $role )
                    $(opt).attr('selected', 'selected');
            });
        }
    }).error(function(jqXHR, textStatus, errorThrown){
        alert("error occurred!");
    });
}

</script>
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

<div class="panel panel-green">
    <div class="panel-heading">
        Konfigurasi Users</div>
    <div class="panel-body pan">
        <form action="{{ url('/users/save') }}" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="id" id="id">
        <div class="form-body pal">
            @if($user['role']=='admin')
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Username</label>
                             <div class="input-icon right">
                                <i class="fa fa-user"></i>
                                <input id="username" name="username" placeholder="Username" value="" class="form-control" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Passsword</label>
                            <div class="input-icon right">
                                <i class="fa fa-lock"></i>
                                <input id="password" name="password" placeholder="Password" value="" class="form-control" type="password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-icon right">
                                <i class="fa fa-envelope"></i>
                                <input id="email" name="email" placeholder="Email" value="" class="form-control" type="text">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Kode Loket</label>
                            <select class="form-control" name="kodeloket" id="kodeloket">
                                @foreach($lokets as $loket)
                                    <option value="{{$loket->id}}">{{$loket->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select class="form-control" name="role" id="role">
                                <option value="user">User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-primary" onclick="simpanUser()()">
                                Simpan</button>
                            <button type="button" class="btn btn-primary" onclick="Empty()()">
                                Reset</button>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="panel panel-yellow">
                            <div class="panel-heading">Daftar Users</div>
                            <div class="panel-body">
                                <table id="dataTable" class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Kode Loket</th>
                                        <th>Created at</th>
                                        <th>Updated at</th>
                                        <th>Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody id="dataUsers">
                                    <tr>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>

                                    </tbody>
                                </table>
                                <script>
                                    
                                </script>
                            </div>
                        </div>

                    </div>
                </div>
            @else
                <div class="alert alert-danger"><strong>Error</strong> Kamu tidak punya akses untuk Konfigurasi User.</div>
            @endif

        </div>
        </form>
    </div>
</div>

</section>
<!-- /.content -->
@endsection