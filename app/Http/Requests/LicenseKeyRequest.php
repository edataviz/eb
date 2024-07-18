<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class LicenseKeyRequest extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'licenseKey' => 'required|size:32|alpha_num',
		];
	}

}
