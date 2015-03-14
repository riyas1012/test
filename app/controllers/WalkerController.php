<?php

class WalkerController extends BaseController
{
	public function isAdmin($token)
	{
		return false;
	}

	public function getWalkerData($walker_id, $token, $is_admin)
	{

		if ($walker_data = Walker::where('token', '=', $token)->where('id', '=', $walker_id)->first()) {
			return $walker_data;
		} elseif ($is_admin) {
			$walker_data = Walker::where('id', '=', $walker_id)->first();
			if (!$walker_data) {
				return false;
			}
			return $walker_data;
		} else {
			return false;
		}
	}

	public function register()
	{
		$first_name = Input::get('first_name');
		$last_name = Input::get('last_name');
		$email = Input::get('email');
		$country_code = Input::get('country_code');
		$phone = Input::get('phone');
		$password = Input::get('password');
		$type = Input::get('type');
		$picture = Input::file('picture');
		$device_token = Input::get('device_token');
		$device_type = Input::get('device_type');
		$bio = Input::get('bio');
		$address = Input::get('address');
		$state = Input::get('state');
		$country = Input::get('country');
		$zipcode = Input::get('zipcode');
		$login_by = Input::get('login_by');
		$social_unique_id = Input::get('social_unique_id');

		if ($password != "" and $social_unique_id == "") {
			$validator = Validator::make(
				array(
					'password' => $password,
					'email' => $email,
					'country_code' => $country_code,
					'phone' => $phone,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'picture' => $picture,
					'device_token' => $device_token,
					'device_type' => $device_type,
					'bio' => $bio,
					'address' => $address,
					'state' => $state,
					'country' => $country,
					'zipcode' => $zipcode,
					'login_by' => $login_by
				),
				array(
					'password' => 'required',
					'email' => 'required|email',
					'country_code' => 'required',
					'phone' => 'required',
					'first_name' => 'required',
					'last_name' => 'required',
					'picture' => 'required|mimes:jpeg,bmp,png',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'bio' => '',
					'address' => '',
					'state' => '',
					'country' => '',
					'zipcode' => 'integer',
					'login_by' => 'required|in:manual,facebook,google',
				)
			);
		} elseif ($social_unique_id != "" and $password == "") {
			$validator = Validator::make(
				array(
					'email' => $email,
					'phone' => $phone,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'picture' => $picture,
					'device_token' => $device_token,
					'device_type' => $device_type,
					'bio' => $bio,
					'address' => $address,
					'state' => $state,
					'country' => $country,
					'zipcode' => $zipcode,
					'login_by' => $login_by,
					'social_unique_id' => $social_unique_id
				),
				array(
					'email' => 'required|email',
					'phone' => 'required',
					'first_name' => 'required',
					'last_name' => 'required',
					'picture' => 'required|mimes:jpeg,bmp,png',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'bio' => '',
					'address' => '',
					'state' => '',
					'country' => '',
					'zipcode' => 'integer',
					'login_by' => 'required|in:manual,facebook,google',
					'social_unique_id' => 'required|unique:walker'
				)
			);
		} elseif ($social_unique_id != "" and $password != "") {
			$response_array = array('success' => false, 'error' => 'Invalid Input - either social_unique_id or password should be passed', 'error_code' => 401);
			$response_code = 200;
			goto response;
		}

		if ($validator->fails()) {
			$error_messages = $validator->messages();
			Log::info('Error while during walker registration = ' . print_r($error_messages, true));
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
			$response_code = 200;
		} else {

			if (Walker::where('phone', '=', $phone)->first()) {
				$response_array = array('success' => false, 'error' => 'Phone Number is already Registred', 'error_code' => 402);
				$response_code = 200;
			} else {

				if(!$type)
				{
					// choose default type
					$provider_type = ProviderType::where('is_default',1)->first();
					
					if(!$provider_type){
						$type = 0;
					}
					else{
						$type = $provider_type->id;
					}
				}
				$walker = new Walker;
				$walker->first_name = $first_name;
				$walker->last_name = $last_name;
				$walker->email = $email;
				$walker->country_code = $country_code;
				$walker->phone = $phone;
				if ($password != "") {
					$walker->password = Hash::make($password);
				}
				$walker->token = generate_token();
				$walker->token_expiry = generate_expiry();
				$walker->type = $type;
				// upload image
				$file_name = time();
				$file_name .= rand();
				$file_name = sha1($file_name);

				$ext = Input::file('picture')->getClientOriginalExtension();
				Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
				$local_url = $file_name . "." . $ext;

				// Upload to S3
				if(Config::get('app.s3_bucket') != "") {
					$s3 = App::make('aws')->get('s3');
					$pic = $s3->putObject(array(
						'Bucket' => Config::get('app.s3_bucket'),
						'Key' => $file_name,
						'SourceFile' => public_path() . "/uploads/" . $local_url,
					));

					$s3->putObjectAcl(array(
						'Bucket' => Config::get('app.s3_bucket'),
						'Key' => $file_name,
						'ACL' => 'public-read'
					));

					$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
				} else {
					$s3_url = asset_url().'/uploads/'.$local_url;
				}
				$walker->picture = $s3_url;
				$walker->device_token = $device_token;
				$walker->device_type = $device_type;
				$walker->bio = $bio;
				$walker->address = $address;
				$walker->state = $state;
				$walker->country = $country;
				$walker->zipcode = $zipcode;
				$walker->login_by = $login_by;
				$walker->is_available = 1;
				$walker->is_active = 0;
				$walker->is_approved = 0;
				if ($social_unique_id != "") {
					$walker->social_unique_id = $social_unique_id;
				}
				$confirmation_number=rand(1111,rand(1111,9999));
				$walker->verification_id=$confirmation_number;
				$walker->save();
				
				// Send sms
				$settings = Settings::where('key','sms_verification_code')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%id%', $walker->verification_id, $pattern);
				sms_notification($walker->id,'walker', $pattern);

				// send email
				$settings = Settings::where('key','email_walker_new_registration')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%name%', $walker->first_name, $pattern);
				$subject = "Welcome On Board";
				email_notification($walker->id,'walker',$pattern,$subject);


				$response_array = array(
								'success' => true
								/*'id' => $walker->id,
								'first_name' => $walker->first_name,
								'last_name' => $walker->last_name,
								'phone' => $walker->phone,
								'email' => $walker->email,
								'picture' => $walker->picture,
								'bio' => $walker->bio,
								'address' => $walker->address,
								'state' => $walker->state,
								'country' => $walker->country,
								'zipcode' => $walker->zipcode,
								'login_by' => $walker->login_by,
								'social_unique_id' => $walker->social_unique_id?$walker->social_unique_id:"",
								'device_token' => $walker->device_token,
								'device_type' => $walker->device_type,
								'token' => $walker->token,
								'type' => $walker->type,*/
							);
				$response_code = 200;

			}
		}

		response:
		$response = Response::json($response_array, $response_code);
		return $response;

	}
	
	public function verification_code()
	{
        $device_token = Input::get('device_token');
		$device_type = Input::get('device_type');
		$verification_code=Input::get('verification_code');
		if (Input::has('phone')) 
		{
			$phone=Input::get('phone');
			if ($verification_code!= "" and $phone!= "")
			{
				$validator = Validator::make(
					array(
						'verification_code' => $verification_code,
						'phone' => $phone,
						'device_token' => $device_token,
						'device_type' => $device_type
					),
					array(
						'verification_code' => 'required',
						'phone' => 'required',
						'device_token' => 'required',
						'device_type' => 'required|in:android,ios'
					)
				);

				if ($validator->fails()) {
					$error_messages = $validator->messages()->all();
					$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
					$response_code = 200;
					Log::error('Validation error during manual login for walker = '.print_r($error_messages, true));
				}
				else
				{
					if ($walker = Walker::where('phone', '=', $phone)->first())
					{
						if(($walker->verification_id==$verification_code) && ($walker->phone==$phone))
						{
							if ($walker->device_type != $device_type) 
							{
								$walker->device_type = $device_type;
							}
							if ($walker->device_token != $device_token) 
							{
								$walker->device_token = $device_token;
							}
							$walker->token_expiry = generate_expiry();
							$walker->is_approved = '1';
							$walker->is_registered ='1';
							$walker->save();
							$response_array = array(
										'success' => true,
										'id' => $walker->id,
										'first_name' => $walker->first_name,
										'last_name' => $walker->last_name,
										'phone' => $walker->phone,
										'email' => $walker->email,
										'picture' => $walker->picture,
										'bio' => $walker->bio,
										'address' => $walker->address,
										'state' => $walker->state,
										'country' => $walker->country,
										'zipcode' => $walker->zipcode,
										'login_by' => $walker->login_by,
										'social_unique_id' => $walker->social_unique_id,
										'device_token' => $walker->device_token,
										'device_type' => $walker->device_type,
										'token' => $walker->token,
										'type' => $walker->type,
									);
																	$response_code = 200;
						}
						else
						{
							$response_array = array('success' => false, 'error' => 'Incorrect Verification code', 'error_code' => 403);
							$response_code = 200;
						}
					}
					else
					{
						$response_array = array('success' => false, 'error' => 'Not a Registered User', 'error_code' => 403);
						$response_code = 200;
					}
				}
			}
			else
			{
				$response_array = array('success' => false, 'error' => 'Invalid input', 'error_code' => 404);
				$response_code = 200;
			}
		}
		elseif (Input::has('social_unique_id')) 
		{
			$social_unique_id=Input::get('social_unique_id');
			if ($verification_code!= "" and $social_unique_id!= "")
			{
				$validator = Validator::make(
					array(
						'verification_code' => $verification_code,
						'social_unique_id' => $social_unique_id,
						'device_token' => $device_token,
						'device_type' => $device_type
					),
					array(
						'verification_code' => 'required',
						'social_unique_id' => 'required',
						'device_token' => 'required',
						'device_type' => 'required|in:android,ios'
					)
				);

				if ($validator->fails()) {
					$error_messages = $validator->messages()->all();
					$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
					$response_code = 200;
					Log::error('Validation error during manual login for walker = '.print_r($error_messages, true));
				}
				else
				{
					if ($walker = Walker::where('social_unique_id', '=', $social_unique_id)->first())
					{
						if(($walker->verification_id==$verification_code) && ($walker->social_unique_id==$social_unique_id))
						{
													if ($walker->device_type != $device_type) 
							{
								$walker->device_type = $device_type;
							}
							if ($walker->device_token != $device_token) 
							{
								$walker->device_token = $device_token;
							}
							$walker->token_expiry = generate_expiry();
							$walker->is_approved = '1';
							$walker->is_registered ='1';
							$walker->save();
							$response_array = array(
										'success' => true,
										'id' => $walker->id,
										'first_name' => $walker->first_name,
										'last_name' => $walker->last_name,
										'phone' => $walker->phone,
										'email' => $walker->email,
										'picture' => $walker->picture,
										'bio' => $walker->bio,
										'address' => $walker->address,
										'state' => $walker->state,
										'country' => $walker->country,
										'zipcode' => $walker->zipcode,
										'login_by' => $walker->login_by,
										'social_unique_id' => $walker->social_unique_id,
										'device_token' => $walker->device_token,
										'device_type' => $walker->device_type,
										'token' => $walker->token,
										'type' => $walker->type,
									);
									$response_code = 200;
						}
						else
						{
							$response_array = array('success' => false, 'error' => 'Incorrect Verification code', 'error_code' => 403);
							$response_code = 200;
						}
					}
					else
					{
						$response_array = array('success' => false, 'error' => 'Not a Registered User', 'error_code' => 403);
						$response_code = 200;
					}
				}
			}
			else
			{
				$response_array = array('success' => false, 'error' => 'Invalid input', 'error_code' => 404);
				$response_code = 200;
			}
		}
		else
		{
			$response_array = array('success' => false, 'error' => 'Invalid input', 'error_code' => 404);
					$response_code = 200;
		}
		$response = Response::json($response_array, $response_code);
		return $response;
	}

	public function login()
	{
		$login_by = Input::get('login_by');
		$device_token = Input::get('device_token');
		$device_type = Input::get('device_type');
		if (Input::has('phone') && Input::has('password')) {
			$phone = Input::get('phone');
			$password = Input::get('password');

			$validator = Validator::make(
				array(
					'password' => $password,
					'phone' => $phone,
					'device_token' => $device_token,
					'device_type' => $device_type,
					'login_by' => $login_by
				),
				array(
					'password' => 'required',
					'phone' => 'required',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'login_by' => 'required|in:manual,facebook,google'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages();
				Log::error('Validation error during manual login for walker = ' . print_r($error_messages, true));
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				if ($walker = Walker::where('phone', '=', $phone)->first()) {
					if($walker->is_registered==1)
					{
						if (Hash::check($password, $walker->password)) {
							if ($login_by != "manual") {
								$response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
								$response_code = 200;
							} else {
								if ($walker->device_type != $device_type) {
									$walker->device_type = $device_type;
								}
								if ($walker->device_token != $device_token) {
									$walker->device_token = $device_token;
								}
								$walker->token_expiry = generate_expiry();
                                $walker->is_approved = '1';
								$walker->save();

								$response_array = array(
									'success' => true,
									'id' => $walker->id,
									'first_name' => $walker->first_name,
									'last_name' => $walker->last_name,
									'phone' => $walker->phone,
									'email' => $walker->email,
									'picture' => $walker->picture,
									'bio' => $walker->bio,
									'address' => $walker->address,
									'state' => $walker->state,
									'country' => $walker->country,
									'zipcode' => $walker->zipcode,
									'login_by' => $walker->login_by,
									'social_unique_id' => $walker->social_unique_id,
									'device_token' => $walker->device_token,
									'device_type' => $walker->device_type,
									'token' => $walker->token,
									'type' => $walker->type,
								);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Invalid Username and Password', 'error_code' => 403);
							$response_code = 200;
						}
					}
					else
					{
						$settings = Settings::where('key','sms_verification_code')->first();
						$pattern = $settings->value;
						$pattern = str_replace('%id%', $walker->verification_id, $pattern);
						sms_notification($walker->id,'walker', $pattern);
						$response_array = array('success' => false, 'error' => 'Not verified', 'error_code' => 404);
						$response_code = 200;
					}
					
				} else {
					$response_array = array('success' => false, 'error' => 'Not a Registered User', 'error_code' => 404);
					$response_code = 200;
				}
			}
		} elseif (Input::has('social_unique_id')) {
			$social_unique_id = Input::get('social_unique_id');
			$socialValidator = Validator::make(
				array(
					'social_unique_id' => $social_unique_id,
					'device_token' => $device_token,
					'device_type' => $device_type,
					'login_by' => $login_by
				),
				array(
					'social_unique_id' => 'required|exists:walker,social_unique_id',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'login_by' => 'required|in:manual,facebook,google'
				)
			);
			if ($socialValidator->fails()) {
				$error_messages = $socialValidator->messages();
				Log::error('Validation error during social login for walker = ' . print_r($error_messages, true));
				$error_messages = $socialValidator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				if ($walker = Walker::where('social_unique_id', '=', $social_unique_id)->first()) 
				{
					if($walker->is_registered==1)
					{
						if (!in_array($login_by, array('facebook', 'google')))
						{
							$response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
							$response_code = 200;
						}
						else
						{
							if ($walker->device_type != $device_type) {
								$walker->device_type = $device_type;
							}
							if ($walker->device_token != $device_token) {
								$walker->device_token = $device_token;
							}
							$walker->token_expiry = generate_expiry();
							$walker->is_approved = '1';
							$walker->save();

							$response_array = array(
								'success' => true,
								'id' => $walker->id,
								'first_name' => $walker->first_name,
								'last_name' => $walker->last_name,
								'phone' => $walker->phone,
								'email' => $walker->email,
								'picture' => $walker->picture,
								'bio' => $walker->bio,
								'address' => $walker->address,
								'state' => $walker->state,
								'country' => $walker->country,
								'zipcode' => $walker->zipcode,
								'login_by' => $walker->login_by,
								'social_unique_id' => $walker->social_unique_id,
								'device_token' => $walker->device_token,
								'device_type' => $walker->device_type,
								'token' => $walker->token,
								'type' => $walker->type,
							);
							$response_code = 200;
						}
					}
					else
					{
						$settings = Settings::where('key','sms_verification_code')->first();
						$pattern = $settings->value;
						$pattern = str_replace('%id%', $walker->verification_id, $pattern);
						sms_notification($walker->id,'walker', $pattern);
						$response_array = array('success' => false, 'error' => 'Not verified', 'error_code' => 404);
						$response_code = 200;
					}
				}
				else 
				{
					$response_array = array('success' => false, 'error' => 'Not a valid social registration User', 'error_code' => 404);
					$response_code = 200;
				}
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Rate Dog

	public function set_dog_rating()
	{
		if (Request::isMethod('post')) {
			$comment = Input::get('comment');
			$request_id = Input::get('request_id');
			$rating = Input::get('rating');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'comment' => $comment,
					'request_id' => $request_id,
					'rating' => $rating,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'comment' => 'required',
					'request_id' => 'required|integer',
					'rating' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_dog_rated == 0) {

									$owner  = Owner::find($request->owner_id);

									$dog_review = new DogReview;
									$dog_review->request_id = $request_id;
									$dog_review->walker_id = $walker_id;
									$dog_review->rating = $rating;
									$dog_review->owner_id = $owner->id;
									$dog_review->comment = $comment;
									$dog_review->save();

									$request->is_dog_rated = 1;
									$request->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Already Rated', 'error_code' => 409);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Cancel Walk

	public function cancel_walk()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {

								if ($walk->is_walk_started == 0) {
									$walk->walker_id = 0;
									$walk->is_confirmed = 0;
									$walk->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk Already Started', 'error_code' => 416);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}
/*
	// Get Walk details

	public function walk_details()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {


								$dog = Dog::find($walk->dog_id);
								$walk_data = array();
								$walk_data['dog'] = array();
								$walk_data['dog']['name'] = $dog->name;
								$walk_data['dog']['age'] = $dog->age;
								$walk_data['dog']['breed'] = $dog->breed;
								$walk_data['dog']['likes'] = $dog->likes;
								$walk_data['dog']['image_url'] = $dog->image_url;

								$owner = Owner::find($dog->owner_id);
								$walk_data['address'] = $owner->address;
								$walk_data['phone'] = $owner->phone;

								$schedule = Schedules::find($walk->schedule_id);
								$walk_data['lockbox_info'] = $schedule->lockbox_info;
								$walk_data['note'] = $schedule->notes;
								$walk_data['start_time'] = $schedule->start_time;
								$walk_data['end_time'] = $schedule->end_time;


								$response_array = array('success' => true, 'walk_data' => $walk_data);
								$response_code = 200;

							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}
*/
	// Get schedule
/*
	public function get_schedule()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');
		$month = Input::get('month');
		$year = Input::get('year');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
				'month' => $month,
				'year' => $year,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
				'month' => 'required|integer',
				'year' => 'required|integer',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					$request_date = $year . "-" . $month . "-01";
					if ($month + 1 > 12) {
						$year_temp = $year + 1;
						$month_temp = 1;
						$time_temp = $year_temp . "-" . $month_temp . "-01";
						$max_end_time = strtotime($time_temp);
					} else {
						$month_temp = $month + 1;
						$time_temp = $year . "-" . $month_temp . "-01";
						$max_end_time = strtotime($time_temp);
					}
					$max_end_date = date("Y-m-d", $max_end_time);


					// retrive all walks and schedules for a month

					$schedules_meta = DB::table('schedule_meta')
						->where('schedules.walker_id', '=', $walker_id)
						->where(function ($query) {
							$month = Input::get('month');
							$year = Input::get('year');
							$request_date = $year . "-" . $month . "-01";
							$query->where('ends_on', '=', '0000-00-00')
								->orWhere('ends_on', '>=', $request_date);
						})
						->leftJoin('schedules', 'schedule_meta.schedule_id', '=', 'schedules.id')
						->get();


					$dates = array();

					foreach ($schedules_meta as $meta) {

						$day = $meta->day;

						$end_time = strtotime($meta->ends_on);
						$end_date = date("Y-m-d", $end_time);


						$today = date("w", strtotime($request_date));
						if ($day - $today >= 0) {
							$day = $day - $today;
						} else {
							$day = ($day + 7) - $today;
						}

						$i = 0;
						while (1) {
							$inc = $day + ($i * 7);
							$time = strtotime($request_date);
							$date = strtotime("+$inc day", $time);
							$walk_date = date("Y-m-d", $date);
							$i++;

							if ($end_date > $max_end_date) {
								$end_date = $max_end_date;
							}
							if ($meta->ends_on == "0000-00-00") {
								$end_date = $max_end_date;
							}

							if ($walk_date >= $end_date) {
								break;
							} else {

								// Push Dates

								array_push($dates, $walk_date);


							}
						}

					}

					// add scheduled walks
					$walks = Walk::where('walker_id', '=', $walker_id)
						->where('date', '>=', "$request_date")
						->where('date', '<', "$time_temp")
						->get();

					//print_r(DB::getQueryLog());
					foreach ($walks as $walk) {
						array_push($dates, $walk->date);
					}

					// remove cancelled walks

					$walks = Walk::where('walker_id', '=', $walker_id)
						->where('date', '>=', "$request_date")
						->where('date', '<', "$time_temp")
						->where('is_cancelled', '=', 1)
						->get();

					//print_r(DB::getQueryLog());
					foreach ($walks as $walk) {

						if (($key = array_search($walk->date, $dates)) !== false) {
							unset($dates[$key]);
						}


					}


					$dates = array_unique($dates);
					sort($dates);

					$response_array = array('success' => true, 'dates' => $dates);
					$response_code = 200;


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}
*/
/*
	// Get Walks

	public function get_walks()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {


					//$walks = Walk::where('walker_id','=',$walker_id)->where('is_started','=',0)->where('is_cancelled','=',0)->where('is_confirmed','=',1)->get();
					$walks = $walks = DB::table('walk')->where('walk.walker_id','=',$walker_id)->where('is_started','=',0)->where('is_confirmed','=',1)->where('is_cancelled','=',0)
						->leftJoin('dog', 'walk.dog_id', '=', 'dog.id')
						->leftJoin('schedules', 'walk.schedule_id', '=', 'schedules.id')
						->get();
					$walk_data = array();
					foreach ($walks as $walk) {
						$data = array();
						$data['walk_id'] = $walk->id;
						$data['walker_id'] = $walk->walker_id;
						$data['schedule_id'] = $walk->schedule_id;
						$data['dog_name'] = $walk->name;
						$data['start_time'] = $walk->start_time;
						$data['end_time'] = $walk->end_time;
						$data['date'] = $walk->date;
						$data['is_confirmed'] = $walk->is_confirmed;
						array_push($walk_data, $data);

					}
					// /print_r($walk_data);

					$response_array = array('success' => true, 'walks' => $walk_data);
					$response_code = 200;


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}
*/

/*

// Get Profile

	public function get_details()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {


					//$walks = WalkerReview::where('walker_id','=',$walker_id)->get();
					$walks = DB::table('review_walker')->where('walker_id', '=', $walker_id)
						->leftJoin('dog', 'review_walker.dog_id', '=', 'dog.id')
						->get();
					$review_data = array();
					$count = 0;
					$rating = 0;
					foreach ($walks as $walk) {
						$data = array();
						$data['dog_name'] = $walk->name;
						$data['dog_pic'] = $walk->image_url;
						$data['rating'] = $walk->rating;
						$data['comment'] = $walk->comment;
						array_push($review_data, $data);
						$count++;
						$rating += $walk->rating;
					}

					if ($count > 0) {
						$rating = $rating / $count;
					} else {
						$rating = 0;
					}
					$response_array = array('success' => true,'name' => $walker_data->name,'bio' => $walker_data->bio,'walker_pic' => $walker_data->picture,'overall_rating' => $rating,'reviews'=>$review_data);
					$response_code = 200;


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}

*/
/*
	// walk started
	public function walk_started()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {

								if ($walk->is_confirmed == 1) {
									$walk->is_started = 1;
									$walk->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet confirmed', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Walk Completed
	public function walk_completed()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {

								if ($walk->is_started == 1) {
									$walk->is_completed = 1;
									$walk->save();

									$walker = Walker::find($walker_id);
									$walker->is_available = 1;
									$walk->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet started', 'error_code' => 414);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}
*/
	// Add walker Location Data
	public function walker_location()
	{
		if (Request::isMethod('post')) {
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						$walker = Walker::find($walker_id);
						$walker->latitude = $latitude;
						$walker->longitude = $longitude;
						$walker->save();
						$response_array = array('success' => true);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	
/*
	// Walk Completed
	public function walk_summary()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$time = Input::get('time');
			$is_poo = Input::get('is_poo');
			$is_pee = Input::get('is_pee');
			$distance = Input::get('distance');
			$note = Input::get('note');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'distance' => $distance,
					'time' => $time,
					'is_poo' => $is_poo,
					'is_pee' => $is_pee,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'walk_id' => 'required|integer',
					'distance' => 'required',
					'is_poo' => 'required|integer',
					'is_pee' => 'required|integer',
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {

								if ($walk->is_completed == 1) {
									$walk->is_poo = $is_poo;
									$walk->is_pee = $is_pee;
									$walk->distance = $distance;
									$walk->time = $time;
									if (isset($note)) {
										$walk->note = $note;
									}
									$walk->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet completed', 'error_code' => 414);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}

*/
/*
	// upload photo
	public function upload_photo()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$picture = Input::file('picture');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'picture' => $picture,
				),
				array(
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'picture' => 'required|mimes:jpeg,bmp,png'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {

								if ($walk->is_completed == 1) {


									// Upload File
									$file_name = time();
									$file_name .= rand();
									$ext = Input::file('picture')->getClientOriginalExtension();
									Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
									$local_url = $file_name . "." . $ext;

									// Upload to S3

									$s3 = App::make('aws')->get('s3');
									$pic = $s3->putObject(array(
										'Bucket' => Config::get('app.s3_bucket'),
										'Key' => $file_name,
										'SourceFile' => public_path() . '/uploads/' . $local_url,

									));

									$s3->putObjectAcl(array(
										'Bucket' => Config::get('app.s3_bucket'),
										'Key' => $file_name,
										'ACL' => 'public-read'
									));

									$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);

									$walk->photo_url = $s3_url;
									$walk->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet completed', 'error_code' => 414);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// upload photo
	public function upload_video()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$video = Input::file('video');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'video' => $video,
				),
				array(
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'video' => 'required|mimes:mp4,flv'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {

								if ($walk->is_completed == 1) {
									// Upload File
									$file_name = time();
									$file_name .= rand();
									$ext = Input::file('video')->getClientOriginalExtension();
									Input::file('video')->move(public_path() . "/uploads", $file_name . "." . $ext);
									$local_url = $file_name . "." . $ext;

									// Upload to S3

									$s3 = App::make('aws')->get('s3');
									$pic = $s3->putObject(array(
										'Bucket' => Config::get('app.s3_bucket'),
										'Key' => $file_name,
										'SourceFile' => public_path() . '/uploads/' . $local_url,

									));

									$s3->putObjectAcl(array(
										'Bucket' => Config::get('app.s3_bucket'),
										'Key' => $file_name,
										'ACL' => 'public-read'
									));

									$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);

									$walk->video_url = $s3_url;
									$walk->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet completed', 'error_code' => 414);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}
*/
	////////////////////////////////////////////
	//// On Demand                       //////
	///////////////////////////////////////////
	// Check For Requests

	// Get Profile

	public function get_requests()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {


					$time = date("Y-m-d H:i:s");

					$query = "SELECT id,owner_id,TIMESTAMPDIFF(SECOND,request_start_time, '$time') as diff from request where is_cancelled = 0 and status = 0 and current_walker='$walker_id' and TIMESTAMPDIFF(SECOND,request_start_time,'$time') <= 60";
					$requests = DB::select(DB::raw($query));
					$all_requests = array();
					foreach ($requests as $request) {
						$data['request_id'] = $request->id;
						$data['time_left_to_respond'] = 60 - $request->diff;

						$owner = Owner::find($request->owner_id);
									$request_data = array();
									$request_data['owner'] = array();
									$request_data['owner']['name'] = $owner->first_name." ".$owner->last_name;
									$request_data['owner']['picture'] = $owner->picture;
									$request_data['owner']['phone'] = $owner->phone;
									$request_data['owner']['address'] = $owner->address;
									$request_data['owner']['latitude'] = $owner->latitude;
									$request_data['owner']['longitude'] = $owner->longitude;
									$request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ?: 0;
									$request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count();
									
									$request_data['dog'] = array();
									if($dog = Dog::find($owner->dog_id))
									{
									
									$request_data['dog']['name'] = $dog->name;
									$request_data['dog']['age'] = $dog->age;
									$request_data['dog']['breed'] = $dog->breed;
									$request_data['dog']['likes'] = $dog->likes;
									$request_data['dog']['picture'] = $dog->image_url;
									}
					$data['request_data'] = $request_data;
						array_push($all_requests, $data);

					}

					$response_array = array('success' => true,'incoming_requests' => $all_requests);
					$response_code = 200;


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Respond To Request

	public function respond_request()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');
		$request_id = Input::get('request_id');
		$accepted = Input::get('accepted');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
				'request_id' => $request_id,
				'accepted' => $accepted,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
				'accepted' => 'required|integer',
				'request_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					// Retrive and validate the Request
					if ($request = Requests::find($request_id) )
					{
						if($request->current_walker == $walker_id)
						{
							if($accepted == 1)
							{
								// request ended
								Requests::where('id','=',$request_id)->update(array('confirmed_walker' => $walker_id,'status' => 1,'request_start_time' => date('Y-m-d H:i:s')));
						
								// confirm walker
								RequestMeta::where('request_id','=',$request_id)->where('walker_id','=',$walker_id)->update(array('status' => 1));

								// Update Walker availability

								Walker::where('id','=',$walker_id)->update(array('is_available' => 0));
						
								// remove other schedule_meta
								RequestMeta::where('request_id','=',$request_id)->where('status','=',0)->delete();

								// Send Notification
								$walker = Walker::find($walker_id);
									$walker_data = array();
									$walker_data['first_name'] = $walker->first_name;
									$walker_data['last_name'] = $walker->last_name;
									$walker_data['phone'] = $walker->phone;
									$walker_data['bio'] = $walker->bio;
									$walker_data['picture'] = $walker->picture;
									$walker_data['latitude'] = $walker->latitude;
									$walker_data['longitude'] = $walker->longitude;
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;
									$bill = array();
									if($request->is_completed == 1)
									{
										
										$bill['distance'] = convert($request->distance,$unit);
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walker_cancelled' => $request->is_cancelled,
										'is_walk_started' => $request->is_started,	
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'walker' => $walker_data,
										'bill' => $bill,
									);
								$title = "Walker Accepted";
								$title_lithuanian="Vairuotojo Priimamos";
								$message = $response_array;

								send_notifications($request->owner_id,"owner",$title,$title_lithuanian,$message);

								// Send SMS 
								$owner = Owner::find($request->owner_id);
								$settings = Settings::where('key','sms_when_provider_accepts')->first();
								$pattern = $settings->value;
								$pattern = str_replace('%user%', $owner->first_name." ".$owner->last_name, $pattern);
								$pattern = str_replace('%driver%', $walker->first_name." ".$walker->last_name, $pattern);
								$pattern = str_replace('%Driver_mobile%', $walker->phone, $pattern);
								sms_notification($request->owner_id,'owner',$pattern);

								// Send SMS 
								
								/*$settings = Settings::where('key','sms_request_completed')->first();
								$pattern = $settings->value;
								$pattern = str_replace('%user%', $owner->first_name." ".$owner->last_name, $pattern);
								$pattern = str_replace('%id%', $request->id, $pattern);
								$pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
								sms_notification(1,'admin',$pattern);*/


							}
							else
							{

								// Archiving Old Walker
								RequestMeta::where('request_id','=',$request_id)->where('walker_id','=',$walker_id)->update(array('status' => 3));
								$request_meta = RequestMeta::where('request_id','=',$request_id)->where('status','=',0)->orderBy('created_at')->first();
								
								// update request
								if(isset($request_meta->walker_id))
								{
									// assign new walker
									Requests::where('id','=',$request_id)->update(array('current_walker' => $request_meta->walker_id,'request_start_time' => date("Y-m-d H:i:s")));
								
									// Send Notification

									$walker = Walker::find($request_meta->walker_id);
									$owner_data = Owner::find($request->owner_id);
									$msg_array = array();
									$msg_array['request_id'] = $request->id;
									$msg_array['id'] = $request_meta->walker_id;
									if ($walker) 
									{
										$msg_array['token'] = $walker->token;
									}
									$msg_array['client_profile'] = array();
									$msg_array['client_profile']['name'] = $owner_data->first_name." ".$owner_data->last_name;
									$msg_array['client_profile']['picture'] = $owner_data->picture;
									$msg_array['client_profile']['bio'] = $owner_data->bio;
									$msg_array['client_profile']['address'] = $owner_data->address;
									$msg_array['client_profile']['phone'] = $owner_data->phone;

									$title = "New Request";
									$title_lithuanian="Nauja užklausa";
									$message = $msg_array;

									send_notifications($request_meta->walker_id,"walker",$title,$title_lithuanian,$message);

								}
								else
								{
									// request ended
									Requests::where('id','=',$request_id)->update(array('current_walker' => 0,'status' => 1));
								}

							}




							$response_array = array('success' => true);
							$response_code = 200;
						}
						else{
							$response_array = array('success' => false, 'error' => 'Request ID does not matches Walker ID', 'error_code' => 405);
							$response_code = 200;
						}

					}
					else{
						$response_array = array('success' => false, 'error' => 'Request ID Not Found', 'error_code' => 405);
						$response_code = 200;
					}


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}



	// Get Request Status
	public function request_in_progress()
	{
		
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'token' => 'required',
					'walker_id' => 'required|integer',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {

						$request = Requests::where('status', '=', 1)->where('is_cancelled', '=', 0)->where('is_completed', '=', 0)->where('confirmed_walker', '=', $walker_id)->first();
						if($request)
						{
							$request_id = $request->id;
						}
						else{
							$request_id = -1;
						}
						$response_array = array(
							'request_id' => $request_id,
							'success' => true,
						);
						$response_code = 200;

					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Get Request Status
	public function get_request()
	{
		
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
				)
			);

			if ($validator->fails()) 
			{
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} 
			else 
			{
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) 
				{
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) 
					{
						// Do necessary operations
						if ($request = Requests::find($request_id)) 
						{
							//echo $request->confirmed_walker;
							if ($request->confirmed_walker == $walker_id) 
							{

									$owner = Owner::find($request->owner_id);
									$request_data = array();
									$request_data['is_walker_started'] = $request->is_walker_started;
									$request_data['is_walker_arrived'] = $request->is_walker_arrived;
									$request_data['is_started'] = $request->is_started;
									$request_data['is_completed'] = $request->is_completed;
									$request_data['is_dog_rated'] = $request->is_dog_rated;
									$request_data['accepted_time'] = $request->request_start_time;
									if($request->is_started == 1){
										$request_data['start_time'] = DB::table('walk_location')
																		->where('request_id',$request_id)
																		->min('created_at');

										$settings = Settings::where('key','default_distance_unit')->first();
										$unit = $settings->value;


										$distance = DB::table('walk_location')
																		->where('request_id',$request_id)
																		->max('distance');
										$request_data['distance'] = convert($distance,$unit);
									}

									if($request->is_completed == 1){
										$request_data['distance'] = $distance;
										$request_data['end_time'] = DB::table('walk_location')
																		->where('request_id',$request_id)
																		->max('created_at');
									}

									$request_data['owner'] = array();
									$request_data['owner']['name'] = $owner->first_name." ".$owner->last_name;
									$request_data['owner']['picture'] = $owner->picture;
									$request_data['owner']['phone'] = $owner->phone;
									$request_data['owner']['address'] = $owner->address;
									$request_data['owner']['latitude'] = $owner->latitude;
									$request_data['owner']['longitude'] = $owner->longitude;
									$request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ?: 0;
									$request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count();
									

									$request_data['dog'] = array();
									if($dog = Dog::find($owner->dog_id))
									{
									
									$request_data['dog']['name'] = $dog->name;
									$request_data['dog']['age'] = $dog->age;
									$request_data['dog']['breed'] = $dog->breed;
									$request_data['dog']['likes'] = $dog->likes;
									$request_data['dog']['picture'] = $dog->image_url;
									}
									$request_data['bill'] = array();
									$bill = array();
									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;
									if($request->is_completed == 1)
									{
										
										$bill['distance'] = convert($request->distance,$unit);
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}
									$request_data['bill'] = $bill;


									$response_array = array('success' => true,'request' => $request_data);
									$response_code = 200;
							} 
							else 
							{
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} 
						else 
						{
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Get Request Status

	public function get_walk_location()
	{
		
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$timestamp = Input::get('ts');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

							if (isset($timestamp)) {
								$walk_locations = WalkLocation::where('request_id', '=', $request_id)->where('created_at', '>', $timestamp)->orderBy('created_at')->get();
							} else {
								$walk_locations = WalkLocation::where('request_id', '=', $request_id)->orderBy('created_at')->get();

							}
							$locations = array();
							$settings = Settings::where('key','default_distance_unit')->first();
										$unit = $settings->value;
							foreach ($walk_locations as $walk_location) {
								$location = array();
								$location['latitude'] = $walk_location->latitude;
								$location['longitude'] = $walk_location->longitude;
								$location['distance'] = convert($walk_location->distance,$unit);
								$location['timestamp'] = $walk_location->created_at;
								array_push($locations, $location);
							}

							$response_array = array('success' => true, 'locationdata' => $locations);
							$response_code = 200;
								
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// walker started

	public function request_walker_started()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->confirmed_walker != 0) {
									$request->is_walker_started = 1;
									$request->save();

									$walker_data->latitude = $latitude;
									$walker_data->longitude = $longitude;
									$walker_data->save();

									// Send Notification
									$msg_array = array();
									$walker = Walker::find($request->confirmed_walker);
									$walker_data = array();
									$walker_data['first_name'] = $walker->first_name;
									$walker_data['last_name'] = $walker->last_name;
									$walker_data['phone'] = $walker->phone;
									$walker_data['bio'] = $walker->bio;
									$walker_data['picture'] = $walker->picture;
									$walker_data['latitude'] = $walker->latitude;
									$walker_data['longitude'] = $walker->longitude;
									$walker_data['type'] = $walker->type;
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();
									
									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;

									$bill = array();
									if($request->is_completed == 1)
									{
										$bill['distance'] = convert($request->distance,$unit);
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walker_cancelled' => $request->is_cancelled,
										'is_walk_started' => $request->is_started,	
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'walker' => $walker_data,
										'bill' => $bill,
									);

									$message = $response_array;

									$title = "Driver Started";
									$title_lithuanian="Vairuotojo pradžia";
									send_notifications($request->owner_id,"owner",$title,$title_lithuanian,$message);


									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walker not yet confirmed', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// walked arrived

	public function request_walker_arrived()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_walker_started == 1) {
									$request->is_walker_arrived = 1;
									$request->save();

									$walker_data->latitude = $latitude;
									$walker_data->longitude = $longitude;
									$walker_data->save();

									// Send Notification
									$walker = Walker::find($request->confirmed_walker);
									$walker_data = array();
									$walker_data['first_name'] = $walker->first_name;
									$walker_data['last_name'] = $walker->last_name;
									$walker_data['phone'] = $walker->phone;
									$walker_data['bio'] = $walker->bio;
									$walker_data['picture'] = $walker->picture;
									$walker_data['latitude'] = $walker->latitude;
									$walker_data['longitude'] = $walker->longitude;
									$walker_data['type'] = $walker->type;
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();
									

									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;

									$bill = array();
									if($request->is_completed == 1)
									{
										$bill['distance'] = convert($request->distance,$unit);
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walker_cancelled' => $request->is_cancelled,
										'is_walk_started' => $request->is_started,	
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'walker' => $walker_data,
										'bill' => $bill,
									);
									$title = "Driver Arrived";
									$title_lithuanian="Vairuotojo atvyko";
									$message = $response_array;

									send_notifications($request->owner_id,"owner",$title,$title_lithuanian,$message);

									// Send SMS 
									$owner = Owner::find($request->owner_id);
									$settings = Settings::where('key','sms_when_provider_arrives')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%user%', $owner->first_name." ".$owner->last_name, $pattern);
									$pattern = str_replace('%driver%', $walker->first_name." ".$walker->last_name, $pattern);
									$pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
									sms_notification($request->owner_id,'owner',$pattern);

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet started', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} 
						else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}

//customer came

      public function customer_came()
	{
		if (Request::isMethod('post'))
		{
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');
			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) 
			{
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			}
			else
			{
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) 
				{
					if (is_token_active($walker_data->token_expiry) || $is_admin)
					{
						if ($request = Requests::find($request_id))
						{
							if ($request->confirmed_walker == $walker_id)
							{
								if ($request->is_walker_arrived == 1)
								{
									$request->is_customer_came = 1;
									$request->save();
									
									$walk_location = new WalkLocation;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->request_id =  $request_id;
									$walk_location->save();
									//sms notification to admin
									/*$owner = Owner::find($request->owner_id);
									$settings = Settings::where('key','sms_customer_came')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%user%', $owner->first_name." ".$owner->last_name, $pattern);
									$pattern = str_replace('%id%', $request->id, $pattern);
									sms_notification(1,'admin',$pattern);*/
									
									$response_array = array('success' => true);
									$response_code = 200;
								}
								else
								{
									$response_array = array('success' => false, 'error' => 'Walker not yet arrived', 'error_code' => 413);
									$response_code = 200;
								}
							}
							else 
							{
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						}
						else
						{
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					}
					else 
					{
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				}
				else 
				{
					if ($is_admin) 
					{
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} 
					else 
					{
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;
	}
//customer not came
public function customer_not_came()
	{
		if (Request::isMethod('post'))
		{
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');
			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) 
			{
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			}
			else
			{
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) 
				{
					if (is_token_active($walker_data->token_expiry) || $is_admin)
					{
						if ($request = Requests::find($request_id))
						{
							if ($request->confirmed_walker == $walker_id)
							{
								if ($request->is_walker_arrived == 1)
								{
									$request->is_cancelled = 1;
									$request->cancelled_by='Walker';
									$request->save();
									
									$walk_location = new WalkLocation;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->request_id =  $request_id;
									$walk_location->save();
									
									
									$walker = Walker::find($request->confirmed_walker);
									$walker_data = array();
									$walker_data['first_name'] = $walker->first_name;
									$walker_data['last_name'] = $walker->last_name;
									$walker_data['phone'] = $walker->phone;
									$walker_data['bio'] = $walker->bio;
									$walker_data['picture'] = $walker->picture;
									$walker_data['latitude'] = $walker->latitude;
									$walker_data['longitude'] = $walker->longitude;
									$walker_data['type'] = $walker->type;
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();
									
									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;

									$bill = array();
									if($request->is_completed == 1)
									{
										$bill['distance'] = convert($request->distance,$unit);
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walker_cancelled' => $request->is_cancelled,
										'is_walk_started' => $request->is_started,	
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'walker' => $walker_data,
										'bill' => $bill,
									);
									
									
									//send notification
									$title = "Driver Cancelled";
									$title_lithuanian="Vairuotojo atšauktas";
									$message = $response_array;

									send_notifications($request->owner_id,"owner",$title,$title_lithuanian,$message);
									
									//sms notification to admin
									/*$owner = Owner::find($request->owner_id);
									$settings = Settings::where('key','sms_customer_not_came')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%user%', $owner->first_name." ".$owner->last_name, $pattern);
									$pattern = str_replace('%id%', $request->id, $pattern);
									sms_notification(1,'admin',$pattern);*/

									// Update Walker availability

								       Walker::where('id','=',$walker_id)->update(array('is_available' => 1,'latitude'=>$latitude,'longitude'=>$longitude));
									   
									   DB::delete("delete from walk_location where request_id = '".$request_id."';");

									$response_array = array('success' => true);
									$response_code = 200;
								}
								else
								{
									$response_array = array('success' => false, 'error' => 'Walker not yet arrived', 'error_code' => 413);
									$response_code = 200;
								}
							}
							else 
							{
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						}
						else
						{
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					}
					else 
					{
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				}
				else 
				{
					if ($is_admin) 
					{
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} 
					else 
					{
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;
	}

	// walk started
	public function request_walk_started()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_walker_arrived == 1) {
									$request->is_started = 1;
									$request->save();

									$walk_location = new WalkLocation;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->request_id =  $request_id;
									$walk_location->save();

									// Send Notification
									$walker = Walker::find($request->confirmed_walker);
									$walker_data = array();
									$walker_data['first_name'] = $walker->first_name;
									$walker_data['last_name'] = $walker->last_name;
									$walker_data['phone'] = $walker->phone;
									$walker_data['bio'] = $walker->bio;
									$walker_data['picture'] = $walker->picture;
									$walker_data['latitude'] = $walker->latitude;
									$walker_data['longitude'] = $walker->longitude;
									$walker_data['type'] = $walker->type;
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();
									
									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;

									$bill = array();
									if($request->is_completed == 1)
									{
										$bill['distance'] = convert($request->distance,$unit);
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walker_cancelled' => $request->is_cancelled,
										'is_walk_started' => $request->is_started,	
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'walker' => $walker_data,
										'bill' => $bill,
									);
									$title = "Trip Started";
									$title_lithuanian="Kelionės pradžia";
									$message = $response_array;

									send_notifications($request->owner_id,"owner",$title,$title_lithuanian,$message);


									
									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walker not yet arrived', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}

	// walk completed
	public function request_walk_completed()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');
			$distance = Input::get('distance');
			$time = Input::get('time');
			$walker = Walker::find($walker_id);

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'distance' => $distance,
					'time' => $time,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
					'distance' => 'required',
					'time' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_started == 1) 
								{
									
									$settings = Settings::where('key','default_charging_method_for_users')->first();
									$pricing_type = $settings->value;
									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;
									$distance = convert($distance,$unit);

									$pt = ProviderServices::where('provider_id',$walker_id)->get();
									foreach ($pt as $key) {
										$reqserv = RequestServices::where('request_id',$request_id)->where('type',$key->type)->first();
										$base_price = $key->base_price;
										$price_per_unit_distance = $key->price_per_unit_distance*$distance;
										$price_per_unit_time = $key->price_per_unit_time*$time;
										$reqserv->base_price = $base_price;
										$reqserv->distance_cost = $price_per_unit_distance;
										$reqserv->time_cost = $price_per_unit_time;
										$reqserv->total = $base_price+$price_per_unit_distance+$price_per_unit_time;
										$reqserv->save();
									}

									$rs = RequestServices::where('request_id',$request_id)->get();
									$total = 0;
									foreach ($rs as $key) {
										Log::info('total = '.print_r($key->total,true));
										$total = $total+$key->total;
									}
									
									$request->is_completed = 1;
									$request->distance = $distance;
									$request->time = $time;
									$request->total = $total;

									// charge client
									$ledger = Ledger::where('owner_id',$request->owner_id)->first();
									
									if($ledger)
									{
										$balance = $ledger->amount_earned - $ledger->amount_spent;
										if($balance > 0)
										{
											if($total > $balance)
											{
												$ledger_temp = Ledger::find($ledger->id);
												$ledger_temp->amount_spent = $ledger_temp->amount_spent + $balance;
												$ledger_temp ->save();
												$total = $total - $balance;
											}
											else{
												$ledger_temp = Ledger::find($ledger->id);
												$ledger_temp->amount_spent = $ledger_temp->amount_spent + $total;
												$ledger_temp ->save();
												$total = 0;
											}

										}
									}
									
							
									if($total == 0)
									{
										$request->is_paid = 1;
									}
									else{

										$payment_data = Payment::where('owner_id',$request->owner_id)->first();
										
										if( $payment_data )
										{
											$customer_id = $payment_data->customer_id;
											try{
												if(Config::get('app.default_payment') == 'stripe')
												{
													$am = round($total) * 100;
													$sett = Settings::where('key','service_fee')->first();
													$serviceFee = $sett->value*100;
													$amount = $am -$serviceFee;
													Stripe::setApiKey(Config::get('app.stripe_secret_key'));
													Stripe_Charge::create(array(
															  "amount"   => $am,
															  "currency" => "usd",
															  "customer" => $customer_id)
															);
													if($walker->merchant_id!=NULL){
														$transfer = Stripe_Transfer::create(array(
															"amount" => $amount,
															"currency" => "usd",
															"recipient" => $walker->merchant_id,
															"statement_descriptor" => "UberFoxX")
														);
														Log::info('transfer = '.print_r($transfer,true));
													}
													$request->is_paid = 1;
												}else{
													$am = round($total,2);
													$sett = Settings::where('key','service_fee')->first();
													$serviceFee = $sett->value;
													$amount = $am -$serviceFee;
													Braintree_Configuration::environment(Config::get('app.braintree_environment'));
													Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
													Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
													Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));

													$card_id = $payment_data->card_token;
													if($walker->merchant_id==NULL){
														$result = Braintree_Transaction::sale(array(
														  'amount' => $amount,
														  'paymentMethodToken' => $card_id
														));
													}else{
														$result = Braintree_Transaction::sale(array(
														  'amount' => $amount,
														  'paymentMethodToken' => $card_id,
														  'merchantAccountId' => $walker->merchant_id,
														  'serviceFeeAmount' => $serviceFee
														));
													}
													Log::info('result = '.print_r($result,true));
													
													if ($result->success) {
														$request->is_paid = 1;
													}
													else{
														$request->is_paid = 0;
													}
												}
												
											}
											catch(Exception $e) {
												$response_array = array('success' => false , 'error' => $e , 'error_code' => 405);
												$response_code = 200;
												$response = Response::json($response_array, $response_code);
												return $response;
											}

										}



									}

									$request->card_payment = $total;
									$request->ledger_payment = $request->total - $total;

									$request->save();

									if($request->is_paid == 1)
									{
										/*$owner = Owner::find($request->owner_id);
										$settings = Settings::where('key','sms_request_unanswered')->first();
										$pattern = $settings->value;
										$pattern = str_replace('%user%', $owner->first_name." ".$owner->last_name, $pattern);
										$pattern = str_replace('%id%', $request->id, $pattern);
										$pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
										sms_notification(1,'admin',$pattern);*/
									}

									$walker = Walker::find($walker_id);
									$walker->is_available = 1;
									$walker->save();								

									$walk_location = new WalkLocation;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->request_id =  $request_id;
									$walk_location->save();

									// Send Notification
									$walker = Walker::find($request->confirmed_walker);
									$walker_data = array();
									$walker_data['first_name'] = $walker->first_name;
									$walker_data['last_name'] = $walker->last_name;
									$walker_data['phone'] = $walker->phone;
									$walker_data['bio'] = $walker->bio;
									$walker_data['picture'] = $walker->picture;
									$walker_data['latitude'] = $walker->latitude;
									$walker_data['longitude'] = $walker->longitude;
									$walker_data['type'] = $walker->type;
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();
									
									$settings = Settings::where('key','default_distance_unit')->first();
									$unit = $settings->value;

									$bill = array();
									if($request->is_completed == 1)
									{
										$bill['distance'] = convert($request->distance,$unit);
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walker_cancelled' => $request->is_cancelled,
										'is_walk_started' => $request->is_started,	
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'walker' => $walker_data,
										'bill' => $bill,
									);
									$title = "Trip Completed";
									$title_lithuanian="Kelionės Baigtas";
									$message = $response_array;
									send_notifications($request->owner_id,"owner",$title,$title_lithuanian,$message);

									// Send SMS 
									$owner = Owner::find($request->owner_id);
									$settings = Settings::where('key','sms_when_provider_completes_job')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%user%', $owner->first_name." ".$owner->last_name, $pattern);
									$pattern = str_replace('%driver%', $walker->first_name." ".$walker->last_name, $pattern);
									$pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
									$pattern = str_replace('%damount%', $request->total, $pattern);
									sms_notification($request->owner_id,'owner',$pattern);

									// send email
									$settings = Settings::where('key','email_request_finished')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%id%', $request->id, $pattern);
									$pattern = str_replace('%url%', web_url()."/admin/request/map/".$request->id, $pattern);
									$subject = "Request Completed";
									email_notification(1,'admin',$pattern,$subject);

									/*$settings = Settings::where('key','email_invoice_generated_user')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%id%', $request->id, $pattern);
									$pattern = str_replace('%amount%', $request->total, $pattern);
									$subject = "Invoice Generated";
									email_notification($request->owner_id,'owner',$pattern,$subject);*/

									$settings = Settings::where('key','email_invoice_generated_provider')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%id%', $request->id, $pattern);
									$pattern = str_replace('%amount%', $request->total, $pattern);
									$subject = "Invoice Generated";
									email_notification($request->confirmed_walker,'walker',$pattern,$subject);


									if($request->is_paid == 1)
									{
										// send email
										$settings = Settings::where('key','email_payment_charged')->first();
										$pattern = $settings->value;

										$pattern = str_replace('%id%', $request->id, $pattern);
										$pattern = str_replace('%url%', web_url()."/admin/request/map/".$request->id, $pattern);

										$subject = "Payment Charged";
										email_notification(1,'admin',$pattern,$subject);
									}

									$response_array = array(
											'success' => true,
											'total' => $total,
											'is_paid' => $request->is_paid,
											);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet started', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}

// Add Location Data
	public function walk_location()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_started == 1) {

									$walk_location_last = WalkLocation::where('request_id',$request_id)->orderBy('created_at','desc')->first();
									
									if($walk_location_last)
									{
										$distance_old = $walk_location_last->distance;
										$distance_new = distanceGeoPoints($walk_location_last->latitude,$walk_location_last->longitude,$latitude,$longitude);
										$distance = $distance_old + $distance_new;
										$settings = Settings::where('key','default_distance_unit')->first();
										$unit = $settings->value;

										$distance = $distance;

									}
									else
									{
										$distance = 0;
									}

									$walk_location = new WalkLocation;
									$walk_location->request_id = $request_id;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->distance = $distance;
									$walk_location->save();

									$response_array = array('success' => true, 'distance' => $distance);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Walk not yet started', 'error_code' => 414);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Request ID doesnot matches with Walker ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Walk ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}



// Add Location Data
	public function check_state()
	{
		
			$walker_id = Input::get('id');
			$token = Input::get('token');

			$validator = Validator::make(
				array(
					'walker_id' => $walker_id,
					'token' => $token,
				),
				array(
					'walker_id' => 'required|integer',
					'token' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						
						$response_array = array('success' => true, 'is_active' => $walker_data->is_active);
						$response_code = 200;
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		
		$response = Response::json($response_array, $response_code);
		return $response;

	}

	// Add Location Data
	public function toggle_state()
	{
		
			$walker_id = Input::get('id');
			$token = Input::get('token');

			$validator = Validator::make(
				array(
					'walker_id' => $walker_id,
					'token' => $token,
				),
				array(
					'walker_id' => 'required|integer',
					'token' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						$walker = Walker::find($walker_id);
						$walker->is_active = ($walker->is_active + 1 ) % 2;
						$walker->save();
						$response_array = array('success' => true, 'is_active' => $walker->is_active);
						$response_code = 200;
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		
		$response = Response::json($response_array, $response_code);
		return $response;

	}



	// Update Profile

	public function update_profile()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');
		$first_name = Input::get('first_name');
		$last_name = Input::get('last_name');
		$email = Input::get('email');
		$phone = Input::get('phone');
		$password = Input::get('password');
		$picture = Input::file('picture');
		$bio = Input::get('bio');
		$address = Input::get('address');
		$state = Input::get('state');
		$country = Input::get('country');
		$zipcode = Input::get('zipcode');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
				'email' => $email,
				'picture' => $picture,
				'zipcode' => $zipcode
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
				'email' => 'email',
				'picture' => 'mimes:jpeg,bmp,png',
				'zipcode' => 'integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {

						$walker = Walker::find($walker_id);
						if($first_name)
						{
							$walker->first_name = $first_name;
						}
						if($last_name)
						{
							$walker->last_name = $last_name;
						}
						if($email)
						{
							$walker->email = $email;
						}
						if($phone)
						{
							$walker->phone = $phone;
						}
						if($bio)
						{
							$walker->bio = $bio;
						}
						if($address)
						{
							$walker->address = $address;
						}
						if($state)
						{
							$walker->state = $state;
						}
						if($country)
						{
							$walker->country = $country;
						}
						if($zipcode)
						{
							$walker->zipcode = $zipcode;
						}
						if($password)
						{
							$walker->password = Hash::make($password);
						}

						if(Input::hasFile('picture')){
							// upload image
							$file_name = time();
							$file_name .= rand();
							$file_name = sha1($file_name);

							$ext = Input::file('picture')->getClientOriginalExtension();
							Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
							$local_url = $file_name . "." . $ext;

							// Upload to S3

							if(Config::get('app.s3_bucket') != "") {
								$s3 = App::make('aws')->get('s3');
								$pic = $s3->putObject(array(
									'Bucket' => Config::get('app.s3_bucket'),
									'Key' => $file_name,
									'SourceFile' => public_path() . "/uploads/" . $local_url,
								));

								$s3->putObjectAcl(array(
									'Bucket' => Config::get('app.s3_bucket'),
									'Key' => $file_name,
									'ACL' => 'public-read'
								));

								$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
							} else {
								$s3_url = asset_url().'/uploads/'.$local_url;
							}
							
							$walker->picture = $s3_url;
						}

						$walker->save();
					
					$response_array = array(
								'success' => true,
								'id' => $walker->id,
								'first_name' => $walker->first_name,
								'last_name' => $walker->last_name,
								'phone' => $walker->phone,
								'email' => $walker->email,
								'picture' => $walker->picture,
								'bio' => $walker->bio,
								'address' => $walker->address,
								'state' => $walker->state,
								'country' => $walker->country,
								'zipcode' => $walker->zipcode,
								'login_by' => $walker->login_by,
								'social_unique_id' => $walker->social_unique_id,
								'device_token' => $walker->device_token,
								'device_type' => $walker->device_type,
								'token' => $walker->token,
								'type' => $walker->type,
							);
					$response_code = 200;


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	public function get_completed_requests()
	{
		
			$walker_id = Input::get('id');
			$token = Input::get('token');

			$validator = Validator::make(
				array(
					'walker_id' => $walker_id,
					'token' => $token,
				),
				array(
					'walker_id' => 'required|integer',
					'token' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						
						$request_data = DB::table('request')
										->where('request.confirmed_walker',$walker_id)
										->where('request.is_completed',1)
										->leftJoin('owner','request.owner_id','=','owner.id')
										->select('request.id','request.request_start_time','owner.first_name',
											'owner.last_name','owner.phone','owner.email','owner.picture','owner.bio',
											'request.distance','request.time','request.base_price',
											'request.distance_cost','request.time_cost','request.total')
										->get();

						$requests = array();
						$settings = Settings::where('key','default_distance_unit')->first();
						$unit = $settings->value;
						foreach ($request_data as $data) {
							$request['id'] = $data->id;
							$request['date'] = $data->request_start_time;
							$request['distance'] = convert($data->distance,$unit);
							$request['time'] = $data->time;
							$request['base_price'] = $data->base_price;
							$request['distance_cost'] = $data->distance_cost;
							$request['time_cost'] = $data->time_cost;
							$request['total'] = $data->total;
							$request['owner']['first_name'] = $data->first_name;
							$request['owner']['last_name'] = $data->last_name;
							$request['owner']['phone'] = $data->phone;
							$request['owner']['email'] = $data->email;
							$request['owner']['picture'] = $data->picture;
							$request['owner']['bio'] = $data->bio;
							array_push($requests, $request);
						}

						$response_array = array(
									'success' => true ,
									'requests' => $requests
									);
						$response_code = 200;

					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		
		$response = Response::json($response_array, $response_code);
		return $response;

	}

	public function provider_services_update()
	{
		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
				Log::info('validation error ='.print_r($response_array, true));
		}
		else 
		{
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) 
			{
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) 
				{
					foreach (Input::get('service') as $key) 
					{
						$serv = ProviderType::where('id',$key)->first();
						$pserv[] = $serv->name;
					}
					foreach (Input::get('service') as $ke) 
					{
						$proviserv = ProviderServices::where('provider_id',$walker_id)->first();
						if($proviserv != NULL)
						{
							DB::delete("delete from walker_services where provider_id = '".$walker_id."';");
						}
					}
					$base_price = Input::get('service_base_price');
					$service_price_distance = Input::get('service_price_distance');
					$service_price_time = Input::get('service_price_time');
					foreach (Input::get('service') as $key) 
					{
						$prserv = new ProviderServices;
						$prserv->provider_id = $walker_id;
						$prserv->type = $key;
						$prserv->base_price = $base_price[$key-1];
						$prserv->price_per_unit_distance = $service_price_distance[$key-1];
						$prserv->price_per_unit_time = $service_price_time[$key-1];
						$prserv->save();
					}
					$response_array = array(
							'success' => true,
						);
					$response_code = 200;
					Log::info('success = '.print_r($response_array,true));
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);
				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
				}
				$response_code = 200;
			}
		}
		$response = Response::json($response_array, $response_code);
		Log::info('repsonse final = '.print_r($response,true));
		return $response;
	}

	public function services_details()
	{
		$walker_id = Input::get('id');
		$token = Input::get('token');

		$validator = Validator::make(
			array(
				'walker_id' => $walker_id,
				'token' => $token,
			),
			array(
				'walker_id' => 'required|integer',
				'token' => 'required',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					$provserv = ProviderServices::where('provider_id', $walker_id)->get();
					foreach ($provserv as $key) {
						$type = ProviderType::where('id', $key->type)->first();
						$serv_name[] = $type->name;
						$serv_base_price[] = $key->base_price;
						$serv_per_distance[] = $key->price_per_unit_distance;
						$serv_per_time[] = $key->price_per_unit_time;
					}
					$response_array = array(
								'success' => true ,
								'serv_name' => $serv_name,
								'serv_base_price' => $serv_base_price,
								'serv_per_distance' => $serv_per_distance,
								'serv_per_time' => $serv_per_time
								);
					$response_code = 200;

				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Walker ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}
		
		$response = Response::json($response_array, $response_code);
		return $response;
	}

}