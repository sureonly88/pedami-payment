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
    <li class="active">Akses Switcher</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
<div class="box box-primary">
    <div class="box-header">
      <h3 class="box-title">SETTING AKSES LEMBAR REKENING PDAMBJM</h3>
    </div>
    <div class="box-body">

    <form action="{{ url('admin/change_passw/edit') }}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        
        <label>KODE LOKET</label>
        <select class="form-control select2" id="loket_code" style="width: 100%" onchange="getLoket()">
          <option value="-">-- PILIH KODE LOKET --</option>
          @foreach($lokets as $loket)
            <option value="{{ $loket->loket_code }}">{{ strtoupper($loket->loket_code) }} - {{ strtoupper($loket->nama) }}</option>
          @endforeach
        </select>

    </div>

     <div class="form-group">
        <label>JUMLAH REKENING</label>
        <input type="text" name="jml_rek" style="font-size: 35px; height: 80px; text-align: center; color: #357ca5" id="jml_rek" class="form-control" />
    </div>

    <div class="form-group">
        <button type="button" style="width: 100%" class="btn btn-primary form-control" onclick="simpan()">Simpan</button>

    </div>
    </form>

    <div class="alert alert-success alert-dismissible" style="visibility: collapse;" id="messageDiv">
        
        <h4><i class="icon fa fa-check"></i> Message!</h4>
        <div id="message"></div>

    </div>

  </div>
</div>
</section>

<script>
$(document).ready(function() {

});

function setMessage(msg,success){
    if(success){
        $("#messageDiv").removeClass("alert-danger");
        $("#messageDiv").addClass("alert-success");
    }else{
        $("#messageDiv").removeClass("alert-success");
        $("#messageDiv").addClass("alert-danger");
    }

    if(msg.length <= 0){
        $("#messageDiv").css("visibility", "collapse");
    }else{
        $("#message").html(msg);
        $("#messageDiv").css("visibility", "visible");
    }
    
}

function getLoket(){
    vLoket= $("#loket_code").val();

    if(vLoket == "-"){
      $("#jml_rek").val("");
      return;
    }

    axios.get("{{ url('admin/akses_rek_pdam/get') }}/"+vLoket).then(function(response){
      //console.log(response.data);
      if(response.data.status){
        $("#jml_rek").val(response.data.jml_rek_pdam);
        setMessage("", true);
      }else{
        setMessage(response.data.message);
        $("#jml_rek").val("", false);
      }

    }).catch(function (error) {
      setMessage("ERROR GET SETTING.", false);
      $("#jml_rek").val("");
    });
    
}

function simpan(){
    vLoket= $("#loket_code").val();
    vRek= $("#jml_rek").val();

    if(vLoket == "-"){
      setMessage("PILIH KODE LOKET.", false);
      return;
    }

    axios.post("{{ url('/admin/akses_rek_pdam/simpan') }}", { 
      loket_code: vLoket,
      jml_rek: vRek
    })
      .then(function(response){
      //console.log(response.data);
      if(response.data.status){
        setMessage(response.data.message, true);
      }else{
        setMessage(response.data.message, false);
      }
    }).catch(function (error) {
      setMessage("ERROR SIMPAN.", false);
    });
}

</script>
@endsection