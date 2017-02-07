<?php namespace Actions\Model;

use Illuminate\Database\Eloquent\Model;
use Yajra\Oci8\Eloquent\OracleEloquent;

class ParamStorage extends OracleEloquent
{
    public $table = 'TEMP_PARAMS_STORAGE';

	protected $primaryKey = null;

	public $incrementing = false;

	public $timestamps = false;

	public $fillable = ['session_id', 'param_group', 'param_name', 'param_value'];
}
