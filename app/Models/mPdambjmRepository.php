<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mgallegos\LaravelJqgrid\Repositories\EloquentRepositoryAbstract;
 
class mPdambjmRepository extends EloquentRepositoryAbstract {
 
  public function __construct(Model $Model)
  {
	$this->Database = $Model;
 
	$this->visibleColumns = array('ID',
		'TRANSACTION_CODE',
		'TRANSACTION_DATE',
		'CUST_ID',
		'NAMA',
		'ALAMAT',
		'IDGOL',
		'BLTH',
		'HARGA_AIR',
		'ABODEMEN',
		'MATERAI',
		'LIMBAH',
		'RETRIBUSI',
		'DENDA',
		'SUB_TOTAL',
		'ADMIN',
		'TOTAL',
		'USERNAME',
		'LOKET_CODE',
		'JENIS_LOKET',
		);
 
	$this->orderBy = array(array('ID', 'DESC'));
  }
}