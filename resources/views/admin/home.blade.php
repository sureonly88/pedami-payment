@extends('.../layouts/template')

@section('content')

<script>
    $(document).ready(function() {
        //LoadUsers();
    });

    // function LoadUsers(){
    //     dtTable = $('#beritaTable').dataTable( {
    //         "ajax": "{{ url('/admin/berita/list') }}",
    //         "destroy": true,
    //         "columns": [
    //             { "data": "judul" },
    //             { "data": "created_at" },  
    //             { "data": "isi" }
    //         ],
    //         "paging":   false,
    //         "searching":   false,
    //         "info":     false   
    //     });
    // }
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
<section class="content" id="elHome">

<div class="row">
    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
              <i class="fa fa-info"></i>
              <h3 class="box-title">INFORMASI USER LOGIN</h3>
            </div>
            <div class="box-body" style="min-height: 350px">
                <table class="table table-striped table-hover" id="tab-info" style="padding-top:10px">
                    <tbody>
                    <tr>
                        <td style="width: 100px">USERNAME</td>
                        <td>: {{$user['username']}}</td>
                    </tr>
                    <tr>
                        <td>USER ROLE</td>
                        <td>: {{ strtoupper($user['role']) }}</td>
                    </tr>
                    <tr>
                        <td>NAMA LOKET</td>
                        <td>: {{ strtoupper($user['loket_name']) }}</td>
                    </tr>
                    <tr>
                        <td>KODE LOKET</td>
                        <td>: {{ strtoupper($user['loket_code']) }}</td>
                    </tr>
                    <tr>
                        <td>EMAIL</td>
                        <td>: {{$user['email']}}</td>
                    </tr>
                    <tr>
                        <td>BIAYA ADMIN</td>
                        <td>: Rp. {{ number_format($user['byadmin'],0) }}</td>
                    </tr>

                    <tr>
                        <td>LAST LOGIN</td>
                        <td>: {{ $user['lastlogin'] }}</td>
                    </tr>


                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row">

            <div class="col-md-12">
                <div class="small-box bg-green">
                    <div class="inner">
                      <h3>Rp. {{ number_format($user['pulsa'],0) }}</h3>

                      <p>TOTAL SALDO</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
            </div>



        </div>

        <div class="row">
            <div class="col-md-12">

                <div class="row">
                <div class="col-md-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                          <h3 class="box-title">BERITA TERBARU</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body" style="min-height: 200px">
                          <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                            @foreach ($list_berita as $berita)
                                @if($loop->index == 0)
                                    <li data-target="#carousel-example-generic" data-slide-to="{{ $loop->index }}" class="active"></li>
                                @else
                                    <li data-target="#carousel-example-generic" data-slide-to="{{ $loop->index }}"></li>
                                @endif
                        
                            @endforeach

                            </ol>
                            <div class="carousel-inner">
                            @foreach ($list_berita as $berita)
                                @if($loop->index == 0)
                                    <div class="item active"><b>{{ strtoupper($berita->judul) }}, {{ $berita->created_at }}</b><br/>
                                    {!! $berita->isi !!}
                                    </div>
                                @else
                                    <div class="item"><b>{{ strtoupper($berita->judul) }}, {{ $berita->created_at }}</b><br/>
                                    {!! $berita->isi !!}
                                    </div>
                                @endif
                            @endforeach

                            </div>
                            <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
                              <span class="fa fa-angle-left"></span>
                            </a>
                            <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
                              <span class="fa fa-angle-right"></span>
                            </a>
                          </div>
                        </div>
                        <!-- /.box-body -->
                      </div>
                      <!-- /.box -->
                </div>

            </div>
                
            </div>
        </div>
    </div>

</div>


</section>
<!-- /.content -->

<script src="{{ secure_asset('plugins/chartjs/Chart.min.js') }}"></script>
@endsection