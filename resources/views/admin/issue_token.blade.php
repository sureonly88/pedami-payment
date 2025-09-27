@extends('...layouts/template')

@section('content')

<script type="text/javascript">
  $(document).ready(function() {
    $("select").select2();
 });
</script>

<!-- Content Header (Page header) -->
<section class="content-header" id="contentToken">
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
      <h3 class="box-title">GENERATE TOKEN</h3>
    </div>
    <div class="box-body">

    <form action="{{ url('admin/change_passw/edit') }}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        
        <label>USER - LOKET</label>
        <select class="form-control select2" id="pilUser" style="width: 100%" onchange="getToken()">
          <option value="-">-- PILIH USER LOKET --</option>
          @foreach($users as $user)
            <option value="{{ $user->username }}">{{ strtoupper($user->username) }} - {{ strtoupper($user->nama) }}</option>
          @endforeach
        </select>

    </div>

     <div class="form-group">
        <label>API TOKEN</label>
        <input type="text" name="token" style="font-size: 35px; height: 80px; text-align: center; color: #357ca5" readonly="readonly" id="token" class="form-control" />
    </div>

    <div class="form-group">
        <button type="button" style="width: 100%" class="btn btn-primary form-control" onclick="prosesToken()">Generate Token</button>

    </div>
    </form>

  </div>
</div>
</section>

<script>
$(document).ready(function() {

});

function getToken(){
    vUsername = $("#pilUser").val();
    axios.get("{{ url('admin/issue_token/get') }}/"+vUsername).then(function(response){
      console.log(response.data);
      if(response.data.status){
        $("#token").val(response.data.token);
      }else{
        $("#token").val("ERRRO GET TOKEN");
      }
      
    }).catch(function (error) {
      $("#token").val("ERROR GET TOKEN");
    });
}

function prosesToken(){
    vUsername = $("#pilUser").val();

    axios.post("{{ url('/admin/issue_token/generate') }}", { username: vUsername })
      .then(function(response){
      console.log(response.data);
      if(response.data.status){
        $("#token").val(response.data.token);
      }else{
        $("#token").val("ERROR GENERATE TOKEN");
      }
    }).catch(function (error) {
      $("#token").val("ERROR GENERATE TOKEN");
    });
}
</script>
@endsection