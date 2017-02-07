<?php

namespace Actions\Controllers;

use Route;

Route::group(['middleware' => ['web']], function ()
{
	Route::get('action1', 'ActionController@index');
	Route::get('action2', 'ActionController@index2');

	Route::get('action/{id}', 'ActionController@index')->name('getAction');
	Route::get('action/get/{parent_id}/{id}', 'ActionController@get')->name('downloadActionFile');;
	Route::post('action/upload/{parent_id}/{id}', 'ActionController@upload')->name('uploadActionFile');
	Route::get('action/proc/{parent_id}/{id}', 'ActionController@proc')->name('procUploadedAction');

	Route::get('action/update/{id}/{item_id}/{type}/{item_type}/{value}', 'ActionController@update')->name('updateItem');
});