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
    <li class="active">Test Printing</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
<div class="box box-primary">
    <div class="box-header">
      <h3 class="box-title">TEST PRINTING</h3>
    </div>
    <div class="box-body">

    <div class="form-group">
        <button type="button" style="width: 100%" class="btn btn-primary form-control" onclick="TesPrint()">Test Printing</button>

    </div>

  </div>
</div>
</section>

@include('...cetakan/pln_postpaid')

<script type="text/javascript">
function TesPrint(){
    vData = $("#cetakRekening").html();
    printHTML(vData);
}

</script>
@endsection