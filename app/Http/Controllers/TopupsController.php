<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Lokets;
use App\Models\Topups;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Response;
use Validator;
use Illuminate\Support\Facades\Input;
use DB;
use Auth;
use App\User;
use Excel;

class TopupsController extends Controller
{
	public function __construct()
    {
        $this->middleware('is_admin');
        //$this->middleware('auth');
    }

	public function index()
	{
		$lokets = Lokets::all();
		$users = User::select('id','username')->get();

		return view('admin.man_topups')
			->with('lokets', $lokets)
			->with('users', $users);
	}

	public function simpanUser(){
		try{

			$Data = Input::all()['Data'];
			$rules = array(
		        'loket_id' => 'required',
		        'topup_money' => 'required',
		        'topup_date' => 'required',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

		    	$Data['topup_date'] = date("Y-m-d H:i:s");
		    	$Data['user_id'] = Auth::user()->id;
		    	$Data['user_topup'] = Auth::user()->username;

		    	Topups::insert($Data);
		    	Lokets::where('id',$Data['loket_id'])->increment('pulsa',$Data['topup_money']);

				return Response::json(array(
					'status' => 'Success',
					'message' => 'Simpan Berhasil',
					'data' => Input::all()
				),200);	    	
		    }

		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => array($error),
            ),200);
		}
	}
	
	public function getUsers($tgl, $loket, $user, $excel){

		try{
			$requestData = $_REQUEST;
			
			if($excel == 0){
				$start = $requestData['start'];
				$length = $requestData['length'];
				$mTotalData = DB::table('topups')->count();
				$mTotalFiltered = $mTotalData;
			}
			
			if(!empty($requestData['search']['value'])){
				$mTotalFiltered = DB::table('topups')
					->leftJoin('lokets','topups.loket_id','=','lokets.id')
					->where("lokets.nama","like","%".$requestData['search']['value']."%")
					->orWhere("lokets.loket_code","like","%".$requestData['search']['value']."%")
					->count();

				$mUsers = DB::table('topups')
					->leftJoin('lokets','topups.loket_id','=','lokets.id')
					->where("lokets.nama","like","%".$requestData['search']['value']."%")
					->orWhere("lokets.loket_code","like","%".$requestData['search']['value']."%")
					->select('lokets.loket_code','lokets.nama','topups.topup_money','topups.tujuan_dana','topups.user_topup','lokets.pulsa','topups.topup_date','topups.note')
					->orderBy('topup_date','desc')
					->offset($start)
					->limit($length)
					->get();

			}else{
				$mUsers = DB::table('topups')
					->leftJoin('lokets','topups.loket_id','=','lokets.id')
					->select('lokets.loket_code','lokets.nama','topups.topup_money','topups.tujuan_dana','topups.user_topup','lokets.pulsa','topups.topup_date','topups.note')
					->where(DB::raw('cast(topups.topup_date as date)'),$tgl);

				if($loket != "-"){
					$mUsers = $mUsers->where('topups.loket_id',$loket);
				}
				
				if($user != "-"){
					$mUsers = $mUsers->where('topups.user_id',$user);
				}

				$mTotalData = $mUsers->get()->count();
				$mTotalFiltered = $mTotalData;

				if($excel == 1){
					$mUsers = $mUsers->get();
				}else{
					$mUsers = $mUsers	
						->orderBy('topup_date','desc')
						->offset($start)
						->limit($length)
						->get();
				}

			}

			if($excel == 1){

				//dd($mUsers);

				$fileName = "TopupExcel";
				Excel::create($fileName, function($excel) use($mUsers) {
		            $excel->sheet('Topup', function($sheet) use($mUsers) {
		                $sheet->fromArray($mUsers);
		            });
		        })->export('xls');

			}else{
				return Response::json(array(
					'status' => 'Success',
					'message' => '-',
					'draw' => $requestData['draw'],
					'recordsTotal' => $mTotalData,
					'recordsFiltered' => $mTotalFiltered,
					'data' => $mUsers
				),200);
			}

		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error
            ),200);
		}
	}

}
