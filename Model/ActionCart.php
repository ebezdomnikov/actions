<?php namespace Actions\Model;

use Illuminate\Database\Eloquent\Model;

class ActionCart extends Model
{
    public $table = 'action_cart';

	public $fillable = ['value'];
}
