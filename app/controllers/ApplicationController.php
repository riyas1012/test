<?php

class ApplicationController extends BaseController {

	public function pages()
	{
		$informations = Information::all();
		$informations_array = array();
		foreach ($informations as $information) {
			$data = array();
			$data['id'] = $information->id;
			$data['title'] = $information->title;
			$data['content'] = $information->content;
			$data['icon'] = $information->icon;
			array_push($informations_array, $data);
		}
		$response_array = array();
		$response_array['success'] = true;
		$response_array['informations'] = $informations_array;
		$response_code = 200;
		$response = Response::json($response_array, $response_code);
		return $response;
	}

	public function get_page()
	{
		$id = Request::segment(3);
		$information = Information::find($id);
		$response_array = array();
		if($information)
		{
			$response_array['success'] = true;
			$response_array['title'] = $information->title;
			$response_array['content'] = $information->content;
			$response_array['icon'] = $information->icon;

		}
		else{
			$response_array['success'] = false;
		}
		$response_code = 200;
		$response = Response::json($response_array, $response_code);
		return $response;
	}

	public function types()
	{
		$types = ProviderType::all();
		$type_array = array();
		foreach ($types as $type) {
			$data = array();
			$data['id'] = $type->id;
			$data['name'] = $type->name;
			$data['icon'] = $type->icon;
			$data['is_default'] = $type->is_default;
			$data['price_per_unit_time'] = $type->price_per_unit_time;
			$data['price_per_unit_distance'] = $type->price_per_unit_distance;
			$data['base_price'] = $type->base_price;
			array_push($type_array, $data);
		}
		$response_array = array();
		$response_array['success'] = true;
		$response_array['types'] = $type_array;
		$response_code = 200;
		$response = Response::json($response_array, $response_code);
		return $response;
	}

	public function forgot_password()
	{
		$type = Input::get('type');
		$email = Input::get('email');
		if($type == 1)
		{
			// Walker
			$walker_data = Walker::where('email',$email)->first();
			if($walker_data)
			{
				$walker = Walker::find($walker_data->id);
				$new_password = time();
				$new_password .= rand();
				$new_password = sha1($new_password);
				$new_password = substr($new_password,0,8);
				$walker->password = Hash::make($new_password);
				$walker->save();


				// send email
				$settings = Settings::where('key','email_forgot_password')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%password%', $new_password, $pattern);
				$subject = "Your New Password";
				email_notification($walker->id,'walker',$pattern,$subject);
			
				$response_array = array();
				$response_array['success'] = true;
				$response_code = 200;
				$response = Response::json($response_array, $response_code);
				return $response;

			}
			else{
				$response_array = array('success' => false, 'error' => 'This Email is not Registered', 'error_code' => 425);
				$response_code = 200;
				$response = Response::json($response_array, $response_code);
				return $response;
			}

		}
		else{
			$owner_data = Owner::where('email',$email)->first();
			if($owner_data)
			{

				$owner = Owner::find($owner_data->id);
				$new_password = time();
				$new_password .= rand();
				$new_password = sha1($new_password);
				$new_password = substr($new_password,0,8);
				$owner->password = Hash::make($new_password);
				$owner->save();

				
				/*$settings = Settings::where('key','email_forgot_password')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%password%', $new_password, $pattern);
				$subject = "Your New Password";
				email_notification($owner->id,'owner',$pattern,$subject);*/

				$response_array = array();
				$response_array['success'] = true;
				$response_code = 200;
				$response = Response::json($response_array, $response_code);
				return $response;

			}
			else{
				$response_array = array('success' => false, 'error' => 'This Email is not Registered', 'error_code' => 425);
				$response_code = 200;
				$response = Response::json($response_array, $response_code);
				return $response;
			}

		}

	}

}