<?php namespace App\Models;

use App\mPdambjmTrans;

class mPdambjmCrud {
 
	protected $mPdambjmTrans;
 
	public function __construct()
	{
		$this->mPdambjmTrans = new mPdambjmTrans();
	}
 
	public function create(array $data)
	{

	 //$data['length'] = str_replace(',', '', $data['length']);
 
	  try
	  {
mPdambjmTrans::insert($data);
		//$this->mPdambjmTrans->create($data);
	  }
	  catch (Exception $e)
	  {
		return json_encode(array('success' => false, 'message' => 'Something went wrong, please try again later.'));
	  }
 
	  return json_encode(array('success' => true, 'message' => 'mPdambjmTrans successfully saved!'));
	}
 
	public function update($id, array $data)
	{
		//return $id;
	  $mPdambjmTrans = $this->mPdambjmTrans->find($id);
 
	  //$data['length'] = str_replace(',', '', $data['length']);
 
	  foreach ($data as $key => $value)
	  {
		$mPdambjmTrans->$key = $value;
	  }
 
	  try
	  {
		$mPdambjmTrans->save();
	  }
	  catch (Exception $e)
	  {
		return json_encode(array('success' => false, 'message' => 'Something went wrong, please try again later.'));
	  }
 
	  return json_encode(array('success' => true, 'message' => 'mPdambjmTrans successfully updated!'));
	}

	public function delete($id)
	{
	  try
	  {
		$this->mPdambjmTrans->destroy($id);
	  }
	  catch (Exception $e)
	  {
		return json_encode(array('success' => false, 'message' => 'Something went wrong, please try again later.'));
	  }
 
	  return json_encode(array('success' => true, 'message' => 'mPdambjmTrans successfully deleted! '.$id));
	}
}