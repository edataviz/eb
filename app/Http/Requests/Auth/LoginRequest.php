<?php namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class LoginRequest extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		if($this->path()=="dclogin") return [];
		return [
				'username' => 'required',
// 				'password' => 'required',
		];
	}

}
