<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('test', function () {
//     return view('admin.emails.topup')
//             ->with([
//                 'saldo' => 3000000,
//                 'tanggal' => '29-03-2017',
//                 'username' => 'yakin',
//                 'loket' => 'LYKN',
//                 'catatan' => 'Minta Saldo.',
//             ]);
// });

// Route::get('test', function () {
//     return view('cetakan.pln_nontaglis');
// });

//Route Login dan Logout
Route::get('login', 'LoginController@index')->middleware('secure');
Route::post('login', 'LoginController@login');
Route::get('logout', 'LoginController@logout');

//Route Halaman Admin Middleware admin untuk Pengecekan Login
Route::get('', 'AdminController@index')->middleware('auth','secure');
Route::group(['prefix' => 'admin', 'middleware' => ['auth'] ], function()
{
    Route::post('rekon-upload',['as'=>'rekon.upload.post','uses'=>'RekonPlnController@rekonUploadPost']);

    Route::get('transaksi_bayar', 'TransaksiBayarController@index');

    Route::resource('roles', 'RoleController');
    Route::resource('permissions', 'PermissionController');

	Route::get('', 'AdminController@index');
	Route::get('home', 'AdminController@index');

	Route::get('profil', 'ProfilController@index');

    Route::get('change_passw', 'ChangePasswController@index');
    Route::post('change_passw/edit', 'ChangePasswController@edit');

    //Route Management Users
    Route::get('users', 'UsersController@index')->middleware('user');
    Route::get('users/get/{id}', 'UsersController@getUserEdit')->middleware('user');
    Route::get('users/get_all', 'UsersController@getUsers')->middleware('user');
    Route::post('users/add', 'UsersController@simpanUser')->middleware('user');
    Route::post('users/update/{id}', 'UsersController@updateUser')->middleware('user');
    Route::post('users/delete/{id}', 'UsersController@deleteUser')->middleware('user');
    Route::post('users/close_conn/{id}', 'UsersController@closeConnUser')->middleware('user');

    //Route Management Lokets
    Route::get('lokets', 'LoketsController@index')->middleware('loket');
    Route::get('lokets/get/{id}', 'LoketsController@getUserEdit')->middleware('loket');
    Route::get('lokets/get_all', 'LoketsController@getUsers')->middleware('loket');
    Route::post('lokets/add', 'LoketsController@simpanUser')->middleware('loket');
    Route::post('lokets/update/{id}', 'LoketsController@updateUser')->middleware('loket');
    Route::post('lokets/delete/{id}', 'LoketsController@deleteUser')->middleware('loket');

    //Route Management Register Handphone
    Route::get('register_hps', 'Register_hpController@index')->middleware('hp');
    Route::get('register_hps/get/{id}', 'Register_hpController@getUserEdit')->middleware('hp');
    Route::get('register_hps/get_all', 'Register_hpController@getUsers')->middleware('hp');
    Route::post('register_hps/add', 'Register_hpController@simpanUser')->middleware('hp');
    Route::post('register_hps/update/{id}', 'Register_hpController@updateUser')->middleware('hp');
    Route::post('register_hps/delete/{id}', 'Register_hpController@deleteUser')->middleware('hp');

    //Route Management Topups
    Route::get('topups', 'TopupsController@index')->middleware('topup');
    Route::get('topups/get_all/{tgl}/{loket}/{user}/{excel}', 'TopupsController@getUsers')->middleware('topup');
    Route::post('topups/add', 'TopupsController@simpanUser')->middleware('topup');

    //Route Management Transaksi PDAMBJM
    Route::get('managepdambjm', 'ManagepdambjmController@index')->middleware('manageTrx');
    Route::get('managepdambjm/get/{id}', 'ManagepdambjmController@getUserEdit')->middleware('manageTrx');
    Route::get('managepdambjm/get_all/{value}', 'ManagepdambjmController@getUsers')->middleware('manageTrx');
    Route::post('managepdambjm/add', 'ManagepdambjmController@simpanUser')->middleware('manageTrx');
    Route::post('managepdambjm/update/{id}', 'ManagepdambjmController@updateUser')->middleware('manageTrx');
    Route::post('managepdambjm/delete/{id}', 'ManagepdambjmController@deleteUser')->middleware('manageTrx');
    Route::get('managepdambjm/info_loket/{username}', 'ManagepdambjmController@getInfoLoket')->middleware('manageTrx');

    //Route untuk menu Transaksi PDAM
    Route::get('pdambjm','PdambjmController@index')->middleware('pdambjm');
    Route::get('cetak_ulang/{idlgn}/{tgl_transaksi}/{blnrek}', 'PdambjmPaymentv2@cetakUlang')->middleware('pdambjm');
    Route::get('cetak_ulang_baru/{idlgn}/{tgl_awal}/{tgl_akhir}/{blnrek}/{is_print_baru}/{jenis_kertas}', 'PdambjmPaymentv2@cetakUlangBaru')
        ->middleware('pdambjm');

    //Route untuk Laporan
    // Route::get('ctklaporan/harian/{transaction_date}/{loket_code}/{username}', 'LapTransaksiPdamBjm@laporan')->middleware('laporan');
    // Route::get('extlaporan/harian/{transaction_date}/{loket_code}/{username}', 'LapTransaksiPdamBjm@exportLaporan')->middleware('laporan');
    // Route::get('ctklaporan/rekap_harian/{transaction_date}/{loket_code}/{jenis}', 'LapTransaksiPdamBjm@LaporanHarian')->middleware('laporan');    
    // Route::get('ctklaporan/bulanan/{tahun}/{bulan}/{loket_code}', 'LapTransaksiPdamBjm@laporan_bulanan')->middleware('laporan');
  	
    // //Routing Laporan Android
    // Route::get('lap_android','LapAndroidController@index');
    // Route::get('lap_android/laporan/{TglAwal}/{TglAkhir}/{KodeLoket}','LapAndroidController@getLaporan');
    // Route::get('lap_android/laporan/cetak/{TglAwal}/{TglAkhir}/{KodeLoket}','LapAndroidController@cetakLaporan');
    // Route::get('lap_android/laporan/export/{TglAwal}/{TglAkhir}/{KodeLoket}','LapAndroidController@exportLaporan');

    // //Routing Laporan Harian
    // Route::get('harian','LapHarianController@index');
    // Route::get('harian/rekap/{transaction_date}/{loket_code}/{jenis}','LapHarianController@getLaporanHarian');
    // Route::get('harian/detail/{transaction_date}/{loket_code}/{username}','LapHarianController@getDetailLaporanHarian');

    // //Routing Laporan Bulanan
    // Route::get('bulanan','LapBulananController@index');
    // Route::get('bulanan/rekap/{tahun}/{bulan}/{loket_code}','LapBulananController@getLaporanBulanan');

    Route::get('cek_pulsa/{KodeLoket}','PulsaController@infoPulsa');

    Route::get('pulsa/{KodeLoket}/{Total}','PulsaController@cekPulsa');
    Route::get('pulsa/info/{KodeLoket}','PulsaController@cekPulsa');
  	Route::get('kwitansi/{KodeLoket}/{TglTransaksi}','KwitansiController@Cetak');
    Route::get('loghistory/{loket_code}','HistoryLogController@getHistoryLog');
    Route::get('get_lokets','LoketsController@getLokets');

    Route::get('grv/rekap','GraphicController@getRekapBulan');

    Route::get('berita', 'BeritaController@index')->middleware('berita');
    Route::get('berita/edit/{id}', 'BeritaController@getEdit')->middleware('berita');
    Route::get('berita/list', 'BeritaController@getList')->middleware('berita');
    Route::post('berita/simpan', 'BeritaController@simpanData')->middleware('berita');
    Route::post('berita/hapus/{id}', 'BeritaController@deleteData')->middleware('berita');

    Route::get('request_saldo', 'RequestSaldoController@index')->middleware('rsaldo');
    Route::get('request_saldo/list', 'RequestSaldoController@getList')->middleware('rsaldo');
    Route::get('request_saldo/get/{kode}', 'RequestSaldoController@getKonfirmasi')->middleware('rsaldo');
    Route::post('request_saldo/simpan', 'RequestSaldoController@simpanData')->middleware('rsaldo');
    Route::post('request_saldo/konfirmasi', 'RequestSaldoController@konfirmasi')->middleware('rsaldo');

    Route::get('admin_saldo', 'AdminSaldoController@index')->middleware('vsaldo');
    Route::get('admin_saldo/list/{stat}', 'AdminSaldoController@getList')->middleware('vsaldo');
    Route::post('admin_saldo/simpan', 'AdminSaldoController@simpanData')->middleware('vsaldo');
    Route::get('admin_saldo/notif', 'AdminSaldoController@getListNotif')->middleware('vsaldo');

    Route::get('pln', 'PLNController@index')->middleware('postpaid');
    Route::get('pln_prepaid', 'PlnPrepaidController@index')->middleware('prepaid');
    Route::get('pln_nontaglis', 'PlnNontagController@index')->middleware('nontaglis');

    //Route::get('isipulsa', 'IsiPulsaController@index');
    Route::get('lap_transaksi', 'LapTransaksiController@index')->middleware('laporan');
    Route::get('lap_transaksi_bulan', 'LapTransaksiController@index_bulan')->middleware('laporan');
    Route::get('lap_transaksi/rekap/{tgl_awal}/{tgl_akhir}/{kode_loket}/{jenis_transaksi}/{jenis_loket}/{tampil}','LapTransaksiController@getRekap')->middleware('laporan');
    Route::get('lap_transaksi/detail/{tanggal}/{kode_loket}/{user}/{jenis_transaksi}/{tampil}','LapTransaksiController@getDetail')->middleware('laporan');
    Route::get('lap_transaksi/bulan/{tahun}/{bulan}/{kode_loket}/{jenis_transaksi}/{jenis_loket}/{tampil}','LapTransaksiController@getBulanan')->middleware('laporan');

    Route::get('setup_email', 'SetupEmailController@index')->middleware('email');
    Route::get('setup_email/edit/{id}', 'SetupEmailController@getEdit')->middleware('email');
    Route::get('setup_email/list', 'SetupEmailController@getList')->middleware('email');
    Route::post('setup_email/simpan', 'SetupEmailController@simpanData')->middleware('email');
    Route::post('setup_email/hapus/{id}', 'SetupEmailController@deleteData')->middleware('email');

    Route::get('pln_prepaid_cu', 'PlnPrepaidController@viewCetakUlang')->middleware('prepaid');
    Route::get('pln_prepaid_manual', 'PlnPrepaidController@viewManual')->middleware('trxadvise');

    //Manage data PLN Pascabayar
    Route::get('man_transaksi_pln', 'ManageTransaksiPLN@index')->middleware('manageTrx');
    Route::get('man_transaksi_pln/edit/{id}', 'ManageTransaksiPLN@getEdit')->middleware('manageTrx');
    Route::get('man_transaksi_pln/list', 'ManageTransaksiPLN@getList')->middleware('manageTrx');
    Route::post('man_transaksi_pln/simpan', 'ManageTransaksiPLN@simpanData')->middleware('manageTrx');
    Route::post('man_transaksi_pln/hapus/{id}', 'ManageTransaksiPLN@deleteData')->middleware('manageTrx');

    //Manage data PLN Prepaid
    Route::get('man_pln_prepaid', 'ManageTransaksiPLNPrepaid@index')->middleware('manageTrx');
    Route::get('man_pln_prepaid/edit/{id}', 'ManageTransaksiPLNPrepaid@getEdit')->middleware('manageTrx');
    Route::get('man_pln_prepaid/list', 'ManageTransaksiPLNPrepaid@getList')->middleware('manageTrx');
    Route::post('man_pln_prepaid/simpan', 'ManageTransaksiPLNPrepaid@simpanData')->middleware('manageTrx');
    Route::post('man_pln_prepaid/hapus/{id}', 'ManageTransaksiPLNPrepaid@deleteData')->middleware('manageTrx');

    Route::get('rekon_pln', 'RekonPlnController@index')->middleware('rekon');
    Route::get('rekon_pln/proses/{tanggal}/{is_selisih}/{jenis}', 'RekonPlnController@ProsesRekonRev1')->middleware('rekon');
    Route::post('rekon_pln/rekon', 'RekonPlnController@ambilRekonStarlink')->middleware('rekon');
    Route::post('rekon_pln/cancel', 'RekonPlnController@cancelPayment')->middleware('rekon');
    Route::get('rekon_pln/edit/{id}', 'RekonPlnController@getEdit')->middleware('rekon');
    Route::get('rekon_pln/cek/{jenis}/{bulan}/{tahun}', 'RekonPlnController@cekFileRekon')->middleware('rekon');

    Route::get('issue_token','ManTokenContoller@index')->middleware('token');
    Route::get('issue_token/get/{username}','ManTokenContoller@getToken')->middleware('token');
    Route::post('issue_token/generate','ManTokenContoller@issue_token')->middleware('token');

    Route::get('man_nontaglis', 'ManageNonTaglisController@index')->middleware('manageTrx');
    Route::get('man_nontaglis/edit/{id}', 'ManageNonTaglisController@getEdit')->middleware('manageTrx');
    Route::get('man_nontaglis/list', 'ManageNonTaglisController@getList')->middleware('manageTrx');
    Route::post('man_nontaglis/simpan', 'ManageNonTaglisController@simpanData')->middleware('manageTrx');
    Route::post('man_nontaglis/hapus/{id}', 'ManageNonTaglisController@deleteData')->middleware('manageTrx');

    Route::get('akses_rek_pdam', 'AksesRekPdamController@index')->middleware('settingrek');
    Route::get('akses_rek_pdam/get/{loket_code}','AksesRekPdamController@getLoket')->middleware('settingrek');
    Route::post('akses_rek_pdam/simpan','AksesRekPdamController@simpan')->middleware('settingrek');

    Route::get('tes_print', 'TesPrinterController@index');
    Route::get('pdam_kolektif', 'PdamKolektifController@index')->middleware('pdambjm');
    Route::get('pdam_kolektif/daftar', 'PdamKolektifController@getKolektif')->middleware('pdambjm');
    Route::get('pdam_kolektif/kolektif/{id_kolektif}', 'PdamKolektifController@getDetailKolektif')->middleware('pdambjm');
    Route::post('pdam_kolektif/aksi', 'PdamKolektifController@aksiKolektif')->middleware('pdambjm');
    Route::post('pdam_kolektif/aksi_detail', 'PdamKolektifController@aksiDetailKolektif')->middleware('pdambjm');
    Route::get('pdam_kolektif/get/{id}', 'PdamKolektifController@getKolektifId')->middleware('pdambjm');

    Route::get('setoran/{tglawal}/{tglakhir}', 'LapTransaksiController@getSetoranHarian');

});

Route::group(['prefix' => 'printing'], function()
{
    Route::get('getqueue/{username}','PrintServiceController@getPrinterQueue');
    Route::post('login', 'PrintServiceController@loginPrinter');
    Route::post('insert_queue','PrintServiceController@insertQueue');
});

//ROUTE UNTUK API PEMBAYARAN BUAT WEB
Route::group(['prefix' => 'api'], function()
{
    //ROUTE API FOR PDAMBJM
    Route::get('pdambjm/get/{nopel}/{loket_code}','PdambjmRequest@getPelanggan')->middleware('pdambjm');
    Route::post('pdambjm/transaksi','PdambjmPaymentv2@doPayment')->middleware('pdambjm');
    Route::get('pdambjm/cetak/{nopel}','PdambjmCetak@cetakRek')->middleware('pdambjm');

    //ROUTE API FOR PLN POSTPAID
    Route::get('pln/postpaid/request/{nomor_pelanggan}', 'PLNController@InqueryTagihanPostPaid')->middleware('postpaid');
    Route::post('pln/postpaid/payment', 'PLNController@PaymentTagihanPostPaid')->middleware('postpaid');
    Route::post('pln/postpaid/reversal', 'PLNController@ReversalTagihanPostPaid')->middleware('postpaid');
    Route::get('pln/postpaid/cetak_ulang/{nomor_pelanggan}/{tgl_awal}/{tgl_akhir}', 'PLNController@CetakTagihanPostPaid')->middleware('postpaid');

    //ROUTE API FOR PLN PREPAID
    Route::get('pln/prepaid/request/{idpel}', 'PlnPrepaidController@InqueryTagihanPrePaid')->middleware('prepaid');
    Route::post('pln/prepaid/purchase', 'PlnPrepaidController@PaymentTagihanPrePaid')->middleware('prepaid');
    Route::post('pln/prepaid/advise', 'PlnPrepaidController@ReversalTagihanPrePaid')->middleware('prepaid');
    Route::get('pln/prepaid/cetak_ulang/{idpel}', 'PlnPrepaidController@CetakTagihanPrePaid')->middleware('prepaid');
    Route::get('pln/prepaid/manual', 'PlnPrepaidController@getManualPrepaid')->middleware('trxadvise');

    //ROUTE API FOR PLN NONTAGLIS
    Route::get('pln/nontaglis/request/{register_number}', 'PlnNontagController@Inquery')->middleware('nontaglis');
    Route::post('pln/nontaglis/payment', 'PlnNontagController@Payment')->middleware('nontaglis');
    Route::post('pln/nontaglis/reversal', 'PlnNontagController@Reversal')->middleware('nontaglis');
    Route::get('pln/nontaglis/cetak_ulang/{register_number}/{tgl_awal}/{tgl_akhir}', 'PlnNontagController@CetakUlang')->middleware('nontaglis');

    Route::get('transaksi_bayar/inquery/{idpel}/{jenis}/{loket}/{token}', 'TransaksiBayarController@inquery')->middleware('transaksi');
    Route::get('transaksi_bayar/cetak_ulang/{nomor_pelanggan}/{tgl_awal}/{tgl_akhir}/{produk}', 'TransaksiBayarController@cetakUlang')
        ->middleware('transaksi');
    Route::post('transaksi_bayar/advise', 'TransaksiBayarController@Advise')->middleware('trxadvise');
});

//ROUTE UNTUK API MOBILE
Route::group(['prefix' => 'mobile'], function()
{
    Route::post('login', 'MobileController@doLogin');

    Route::post('pdambjm/pay','MobileController@doPayment')->middleware('mobile','trxmobile');
    Route::get('pdambjm/get/{nopel}/{kode_loket}/{sessionid}/{username}/{imei}','MobileController@getPelanggan')->middleware('mobile','trxmobile');
    Route::get('pdambjm/get/{nopel}/{kode_loket}/{sessionid}/{username}','MobileController@getPelanggan')->middleware('mobile','trxmobile');

    Route::get('laporan/{tgl_awal}/{tgl_akhir}/{kode_loket}/{sessionid}/{username}/{imei?}/{pil}','MobileController@getLaporanHarian')->middleware('mobile');
    Route::get('laporan/{tgl_awal}/{tgl_akhir}/{kode_loket}/{sessionid}/{username}/{pil}','MobileController@getLaporanHarianv1')->middleware('mobile');

    Route::get('detail_laporan/{tanggal}/{kode_loket}/{jenis_transaksi}/{sessionid}/{username}/{imei}/{userloket}','MobileController@getDetailLaporan')->middleware('mobile');

    Route::get('cetak/{idlgn}/{tgl_transaksi}/{sessionid}/{username}/{imei}', 'MobileController@cetakUlang')->middleware('mobile');

    Route::get('cetak_idpel/{idlgn}/{tgl_awal}/{tgl_akhir}/{jenis_transaksi}/{sessionid}/{username}/{imei}', 'MobileController@cetakIdpel')->middleware('mobile');

    Route::get('userinfo/{username}/{sessionid}/{imei}', 'MobileController@getUserLoketInfo')->middleware('mobile');

    Route::get('berita/{username}/{sessionid}/{imei}', 'MobileController@getBerita')->middleware('mobile');

    Route::get('pln_postpaid/inquery/{idpel}/{username}/{sessionid}/{imei}','PLNMobileController@Inquery')->middleware('mobile','trxmobile');
    Route::get('pln_postpaid/inquery/{idpel}/{username}/{sessionid}','PLNMobileController@Inquery')->middleware('mobile','trxmobile');
    Route::post('pln_postpaid/payment','PLNMobileController@Payment')->middleware('mobile','trxmobile');
    Route::post('pln_postpaid/reversal','PLNMobileController@Reversal')->middleware('mobile','trxmobile');

    Route::get('pln_prepaid/inquery/{idpel}/{token}/{username}/{sessionid}/{imei}','PLNMobileController@InqueryPrepaid')->middleware('mobile','trxmobile');
    Route::get('pln_prepaid/inquery/{idpel}/{token}/{username}/{sessionid}','PLNMobileController@InqueryPrepaid')->middleware('mobile','trxmobile');
    Route::post('pln_prepaid/purchase','PLNMobileController@purchasePrepaid')->middleware('mobile','trxmobile');
    Route::post('pln_prepaid/advise','PLNMobileController@advisePrepaid')->middleware('mobile','trxmobile');

    Route::get('pln_nontaglis/inquery/{register_number}/{username}/{sessionid}/{imei}','PLNMobileController@InqueryNontaglis')->middleware('mobile','trxmobile');
    Route::get('pln_nontaglis/inquery/{register_number}/{username}/{sessionid}','PLNMobileController@InqueryNontaglis')->middleware('mobile','trxmobile');
    Route::post('pln_nontaglis/payment','PLNMobileController@PaymentNontaglis')->middleware('mobile','trxmobile');
    Route::post('pln_nontaglis/reversal','PLNMobileController@ReversalNontaglis')->middleware('mobile','trxmobile');

    Route::get('loghistory/{loket_code}','HistoryLogController@getHistoryLog');
});

//ROUTE UNTUK API REKANAN
Route::group(['prefix' => 'gateway'], function()
{
    Route::get('sisa_saldo','RekananAPIController@sisaSaldo')->middleware('api_rekanan');
    Route::get('pdambjm/inquery/{idpel}','RekananAPIController@inquery')->middleware('api_rekanan');
    Route::post('pdambjm/payment','RekananAPIController@payment')->middleware('api_rekanan');
});

