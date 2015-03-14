<?php

class OwnerController extends BaseController
{

	public function isAdmin($token)
	{
		return false;
	}

	public function getOwnerData($owner_id, $token, $is_admin)
	{

		if ($owner_data = Owner::where('token', '=', $token)->where('id', '=', $owner_id)->first()) {
			return $owner_data;
		} elseif ($is_admin) {
			$owner_data = Owner::where('id', '=', $owner_id)->first();
			if (!$owner_data) {
				return false;
			}
			return $owner_data;
		} else {
			return false;
		}

	}


	public function get_braintree_token()
	{

		$token = Input::get('token');
		$owner_id = Input::get('id');
		$validator = Validator::make(
					    array(
					        'token' => $token,
					        'owner_id' => $owner_id,
					    ),
					    array(
					        'token' => 'required',
					        'owner_id' => 'required|integer'
					    )
					);

		if ($validator->fails())
		{
		   $error_messages = $validator->messages();
		   $response_array = array('success' => false , 'error' => 'Invalid Input' , 'error_code' => 401);
		   $response_code = 200;
		}
		else
		{
			$is_admin = $this->isAdmin($token);
			if( $owner_data = $this->getOwnerData($owner_id,$token,$is_admin)){
				// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin)
				{
					if(Config::get('app.default_payment') == 'braintree'){

						Braintree_Configuration::environment(Config::get('app.braintree_environment'));
						Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
						Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
						Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
						$clientToken = Braintree_ClientToken::generate();
						$response_array = array('success' => true,'token' => $clientToken);
						$response_code = 200;

					}
					else{
						$response_array = array('success' => false, 'error' => 'Please change braintree as default gateway', 'error_code' => 440);
						$response_code = 200;
					}
												
					
					
				}
				else{
					$response_array = array('success' => false , 'error' => 'Token Expired' , 'error_code' => 405);
					$response_code = 200;
				}
			}
			else{
				if($is_admin)
				{
					$response_array = array('success' => false , 'error' => 'Owner ID is not Found' , 'error_code' => 410);

				}
				else{
					$response_array = array('success' => false , 'error' => 'Not a valid token' , 'error_code' => 406);

				}					
				$response_code = 200;
			}
		}
	
		$response = Response::json($response_array , $response_code);
		return $response; 
	}



	public function apply_referral_code()
	{
		$referral_code = Input::get('referral_code');
		$token = Input::get('token');
		$owner_id = Input::get('id');

		$validator = Validator::make(
					    array(
					        'referral_code' => $referral_code,
					        'token' => $token,
					        'owner_id' => $owner_id,
					    ),
					    array(
					        'referral_code' => 'required',
					        'token' => 'required',
					        'owner_id' => 'required|integer'
					    )
					);

		if ($validator->fails())
		{
		   $error_messages = $validator->messages();
		   $response_array = array('success' => false , 'error' => 'Invalid Input' , 'error_code' => 401);
		   $response_code = 200;
		}
		else
		{
			$is_admin = $this->isAdmin($token);
			if( $owner_data = $this->getOwnerData($owner_id,$token,$is_admin)){
			// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin)
				{

					if($ledger = Ledger::where('referral_code',$referral_code)->first())
					{
						$referred_by = $ledger->owner_id;
						$settings = Settings::where('key','default_referral_bonus')->first();
						$referral_bonus = $settings->value;
						 
						$ledger = Ledger::find($ledger->id);
						$ledger->total_referrals = $ledger->total_referrals + 1;
						$ledger->amount_earned = $ledger->amount_earned + $referral_bonus;
						$ledger->save();

						$owner = Owner::find($owner_id);
						$owner->referred_by = $ledger->owner_id;
						$owner->save();


						$response_array = array('success' => true);
						$response_code = 200;
					}
					else
					{
						$response_array = array('success' => false , 'error' => 'Invalid referral code' , 'error_code' => 405);
						$response_code = 200;
					}
						
					
						
					

					
				}
				else{
					$response_array = array('success' => false , 'error' => 'Token Expired' , 'error_code' => 405);
					$response_code = 200;
				}
			}
			else{
				if($is_admin)
				{
					$response_array = array('success' => false , 'error' => 'Owner ID is not Found' , 'error_code' => 410);

				}
				else{
					$response_array = array('success' => false , 'error' => 'Not a valid token' , 'error_code' => 406);

				}					
				$response_code = 200;
			}
		}
	
		$response = Response::json($response_array , $response_code);
		return $response; 
	}
	// test
	public function register()
	{
		/*$first_name = Input::get('first_name');
		$last_name = Input::get('last_name');
		$email = Input::get('email');
		$country_code = Input::get('country_code');
		$phone = Input::get('phone');
		$password = Input::get('password');
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
					'social_unique_id' => 'required|unique:owner'
				)
			);
		} elseif ($social_unique_id != "" and $password != "") {
			$response_array = array('success' => false, 'error' => 'Invalid Input - either social_unique_id or password should be passed', 'error_code' => 401);
			$response_code = 200;
			goto response;
		}

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();

			Log::info('Error while during owner registration = '.print_r($error_messages, true));
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
			$response_code = 200;
		} else {

			if (Owner::where('phone', '=', $phone)->first()) {
				$response_array = array('success' => false, 'error' => 'Phone Number is already Registred', 'error_code' => 402);
				$response_code = 200;
			} else {

			
				
					
					
				
				
				$owner = new Owner;
				$owner->first_name = $first_name;
				$owner->last_name = $last_name;
				$owner->email = $email;
				$owner->country_code = $country_code;
				$owner->phone = $phone;
				if ($password != "") {
					$owner->password = Hash::make($password);
				}
				$owner->token = generate_token();
				$owner->token_expiry = generate_expiry();

				// upload image
				$file_name = time();
				$file_name .= rand();
				$file_name = sha1($file_name);

				$ext = Input::file('picture')->getClientOriginalExtension();
				Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
				$local_url = $file_name . "." . $ext;

				// Upload to S3
				if(Config::get('app.s3_bucket') != "")
				{
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
				}
				else{
					$s3_url = asset_url().'/uploads/'.$local_url;
				}
				$owner->picture = $s3_url;
				$owner->device_token = $device_token;
				$owner->device_type = $device_type;
				$owner->bio = $bio;
				$owner->address = $address;
				$owner->state = $state;
				$owner->login_by = $login_by;
				$owner->country = $country;
				$owner->zipcode = $zipcode;
				if ($social_unique_id != "") {
					$owner->social_unique_id = $social_unique_id;
				}
				$confirmation_number=rand(1111,rand(1111,9999));
				$owner->verification_id=$confirmation_number;

				$owner->save();
				//send sms
				$settings = Settings::where('key','sms_verification_code')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%id%', $owner->verification_id, $pattern);
				sms_notification($owner->id,'owner', $pattern);
				
				// send email
				$settings = Settings::where('key','email_owner_new_registration')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%name%', $owner->first_name, $pattern);
				$subject = "Welcome On Board";
				email_notification($owner->id,'owner',$pattern,$subject);


				$response_array = array(
								'success' => true,
								/*'id' => $owner->id,
								'first_name' => $owner->first_name,
								'last_name' => $owner->last_name,
								'phone' => $owner->phone,
								'email' => $owner->email,
								'picture' => $owner->picture,
								'bio' => $owner->bio,
								'address' => $owner->address,
								'state' => $owner->state,
								'country' => $owner->country,
								'zipcode' => $owner->zipcode,
								'login_by' => $owner->login_by,
								'social_unique_id' => $owner->social_unique_id?$owner->social_unique_id:"",
								'device_token' => $owner->device_token,
								'device_type' => $owner->device_type,
								'token' => $owner->token,
							);

				$response_code = 200;

			}
		}*/
		$country_code = Input::get('country_code');
		$phone = Input::get('phone');
		$device_token = Input::get('device_token');
		$device_type = Input::get('device_type');
		if ($phone != "")
		{
			$validator = Validator::make(
				array(
					'country_code' => $country_code,
					'phone' => $phone,
					'device_token' => $device_token,
					'device_type' => $device_type
				),
				array(
					'country_code' => 'required',
					'phone' => 'required',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios'
				)
			);
		}
		else
		{
			$response_array = array('success' => false, 'error' => 'Invalid Input Phone Number should be passed', 'error_code' => 401);
			$response_code = 200;
			goto response;
		}
		if ($validator->fails()) 
		{
			$error_messages = $validator->messages()->all();

			Log::info('Error while during owner registration = '.print_r($error_messages, true));
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
			$response_code = 200;
		} 
		else 
		{

			if ($customer = Owner::where('phone', '=', $phone)->first()) 
			{
				$confirmation_number=rand(1111,rand(1111,9999));
				$customer->verification_id=$confirmation_number;
				$customer->save();
				//send sms
				$settings = Settings::where('key','sms_verification_code')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%id%', $customer->verification_id, $pattern);
				sms_notification($customer->id,'owner', $pattern);
				$response_array = array(
										'success' => true,
										);
				$response_code = 200;
			} 
			else 
			{
				$owner = new Owner;
				$owner->country_code = $country_code;
				$owner->phone = $phone;
				$owner->token = generate_token();
				$owner->token_expiry = generate_expiry();
				$owner->device_token = $device_token;
				$owner->device_type = $device_type;
				$confirmation_number=rand(1111,rand(1111,9999));
				$owner->verification_id=$confirmation_number;
				$owner->save();
				//send sms
				$settings = Settings::where('key','sms_verification_code')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%id%', $owner->verification_id, $pattern);
				sms_notification($owner->id,'owner', $pattern);
				
				// send email
				/*$settings = Settings::where('key','email_owner_new_registration')->first();
				$pattern = $settings->value;
				$pattern = str_replace('%name%', $owner->first_name, $pattern);
				$subject = "Welcome On Board";
				email_notification($owner->id,'owner',$pattern,$subject);*/
				$response_array = array(
								'success' => true,
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
					Log::error('Validation error during manual login for owner = '.print_r($error_messages, true));
				}
				else
				{
					if ($owner = Owner::where('phone', '=', $phone)->first())
					{
						if(($owner->verification_id==$verification_code) && ($owner->phone==$phone))
						{
													if ($owner->device_type != $device_type) {
								$owner->device_type = $device_type;
							}
							if ($owner->device_token != $device_token) {
								$owner->device_token = $device_token;
							}
							$owner->token_expiry = generate_expiry();
							$owner->is_active ='1';
							$owner->save();
							$response_array = array(
										'success' => true,
										'id' => $owner->id,
										/*'first_name' => $owner->first_name,
										'last_name' => $owner->last_name,*/
										'phone' => $owner->phone,
										/*'email' => $owner->email,
										'picture' => $owner->picture,
										'bio' => $owner->bio,
										'address' => $owner->address,
										'state' => $owner->state,
										'country' => $owner->country,
										'zipcode' => $owner->zipcode,
										'login_by' => $owner->login_by,
										'social_unique_id' => $owner->social_unique_id,*/
										'device_token' => $owner->device_token,
										'device_type' => $owner->device_type,
										'token' => $owner->token,
									);

									$dog = Dog::find($owner->dog_id);
									if ($dog !== NULL) {
										$response_array = array_merge($response_array, array(
											'dog_id' => $dog->id,
											'age' => $dog->age,
											'name' => $dog->name,
											'breed' => $dog->breed,
											'likes' => $dog->likes,
											'image_url' => $dog->image_url,
										));
									}

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
		/*elseif (Input::has('social_unique_id'))
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
					Log::error('Validation error during manual login for owner = '.print_r($error_messages, true));
				}
				else
				{
					if ($owner = Owner::where('social_unique_id', '=', $social_unique_id)->first())
					{
						if(($owner->verification_id==$verification_code) && ($owner->social_unique_id==$social_unique_id))
						{
							if ($owner->device_type != $device_type) {
								$owner->device_type = $device_type;
							}
							if ($owner->device_token != $device_token) {
								$owner->device_token = $device_token;
							}
							$owner->token_expiry = generate_expiry();
							$owner->is_active ='1';
							$owner->save();
							$response_array = array(
										'success' => true,
										'id' => $owner->id,
										'first_name' => $owner->first_name,
										'last_name' => $owner->last_name,
										'phone' => $owner->phone,
										'email' => $owner->email,
										'picture' => $owner->picture,
										'bio' => $owner->bio,
										'address' => $owner->address,
										'state' => $owner->state,
										'country' => $owner->country,
										'zipcode' => $owner->zipcode,
										'login_by' => $owner->login_by,
										'social_unique_id' => $owner->social_unique_id,
										'device_token' => $owner->device_token,
										'device_type' => $owner->device_type,
										'token' => $owner->token,
									);

									$dog = Dog::find($owner->dog_id);
									if ($dog !== NULL) {
										$response_array = array_merge($response_array, array(
											'dog_id' => $dog->id,
											'age' => $dog->age,
											'name' => $dog->name,
											'breed' => $dog->breed,
											'likes' => $dog->likes,
											'image_url' => $dog->image_url,
										));
									}

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
		}*/
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

		if (Input::has('phone') && Input::has('password')) 
		{
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
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
				Log::error('Validation error during manual login for owner = '.print_r($error_messages, true));
			} 
			else 
			{
				if ($owner = Owner::where('phone', '=', $phone)->first()) {
					if($owner->is_active==1)
					{
						if (Hash::check($password, $owner->password)) 
						{
							if ($login_by !== "manual") {
								$response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
								$response_code = 200;
							}
							else {
								if ($owner->device_type != $device_type) {
									$owner->device_type = $device_type;
								}
								if ($owner->device_token != $device_token) {
									$owner->device_token = $device_token;
								}
								$owner->token_expiry = generate_expiry();
								$owner->save();

								$response_array = array(
									'success' => true,
									'id' => $owner->id,
									'first_name' => $owner->first_name,
									'last_name' => $owner->last_name,
									'phone' => $owner->phone,
									'email' => $owner->email,
									'picture' => $owner->picture,
									'bio' => $owner->bio,
									'address' => $owner->address,
									'state' => $owner->state,
									'country' => $owner->country,
									'zipcode' => $owner->zipcode,
									'login_by' => $owner->login_by,
									'social_unique_id' => $owner->social_unique_id,
									'device_token' => $owner->device_token,
									'device_type' => $owner->device_type,
									'token' => $owner->token,
								);

								$dog = Dog::find($owner->dog_id);
								if ($dog !== NULL) {
									$response_array = array_merge($response_array, array(
										'dog_id' => $dog->id,
										'age' => $dog->age,
										'name' => $dog->name,
										'breed' => $dog->breed,
										'likes' => $dog->likes,
										'image_url' => $dog->image_url,
									));
								}

								$response_code = 200;
							}
						} 
						else 
						{
							$response_array = array('success' => false, 'error' => 'Invalid Username and Password', 'error_code' => 403);
							$response_code = 200;
						}
					}
					else
					{
						//send sms
						$settings = Settings::where('key','sms_verification_code')->first();
						$pattern = $settings->value;
						$pattern = str_replace('%id%', $owner->verification_id, $pattern);
						sms_notification($owner->id,'owner', $pattern);
						$response_array = array('success' => false, 'error' => 'Not verified', 'error_code' => 404);
						$response_code = 200;
					}
					
				} else {
					$response_array = array('success' => false, 'error' => 'Not a Registered User', 'error_code' => 404);
					$response_code = 200;
				}
			}
		} 
		elseif (Input::has('social_unique_id')) {
			$social_unique_id = Input::get('social_unique_id');
			$socialValidator = Validator::make(
				array(
					'social_unique_id' => $social_unique_id,
					'device_token' => $device_token,
					'device_type' => $device_type,
					'login_by' => $login_by
				),
				array(
					'social_unique_id' => 'required|exists:owner,social_unique_id',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'login_by' => 'required|in:manual,facebook,google'
				)
			);

			if ($socialValidator->fails()) 
			{
				$error_messages = $socialValidator->messages();
				Log::error('Validation error during social login for owner = '.print_r($error_messages, true));
				$error_messages = $socialValidator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} 
			else 
			{
				if ($owner = Owner::where('social_unique_id', '=', $social_unique_id)->first()) 
				{
					if($owner->is_active==1)
					{
						if (!in_array($login_by, array('facebook', 'google'))) 
						{
							$response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
							$response_code = 200;
						} 
						else 
						{
							if ($owner->device_type != $device_type) {
								$owner->device_type = $device_type;
							}
							if ($owner->device_token != $device_token) {
								$owner->device_token = $device_token;
							}
							$owner->token_expiry = generate_expiry();
							$owner->save();

							$response_array = array(
								'success' => true,
								'id' => $owner->id,
								'first_name' => $owner->first_name,
								'last_name' => $owner->last_name,
								'phone' => $owner->phone,
								'email' => $owner->email,
								'picture' => $owner->picture,
								'bio' => $owner->bio,
								'address' => $owner->address,
								'state' => $owner->state,
								'country' => $owner->country,
								'zipcode' => $owner->zipcode,
								'login_by' => $owner->login_by,
								'social_unique_id' => $owner->social_unique_id,
								'device_token' => $owner->device_token,
								'device_type' => $owner->device_type,
								'token' => $owner->token,
							);

							$dog = Dog::find($owner->dog_id);
							if ($dog !== NULL) {
								$response_array = array_merge($response_array, array(
									'dog_id' => $dog->id,
									'age' => $dog->age,
									'name' => $dog->name,
									'breed' => $dog->breed,
									'likes' => $dog->likes,
									'image_url' => $dog->image_url,
								));
							}

							$response_code = 200;
						}
					}
					else
					{
						//send sms
						$settings = Settings::where('key','sms_verification_code')->first();
						$pattern = $settings->value;
						$pattern = str_replace('%id%', $owner->verification_id, $pattern);
						sms_notification($owner->id,'owner', $pattern);
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
		else{
			$response_array = array('success' => false, 'error' => 'Invalid input', 'error_code' => 404);
					$response_code = 200;
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}

	public function details()
	{
		if (Request::isMethod('post')) {
			$address = Input::get('address');
			$state = Input::get('state');
			$zipcode = Input::get('zipcode');
			$token = Input::get('token');
			$owner_id = Input::get('id');

			$validator = Validator::make(
				array(
					'address' => $address,
					'state' => $state,
					'zipcode' => $zipcode,
					'token' => $token,
					'owner_id' => $owner_id,
				),
				array(
					'address' => 'required',
					'state' => 'required',
					'zipcode' => 'required|integer',
					'token' => 'required',
					'owner_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($owner_data->token_expiry) || $is_admin) {
						// Do necessary operations

						$owner = Owner::find($owner_data->id);
						$owner->address = $address;
						$owner->state = $state;
						$owner->zipcode = $zipcode;
						$owner->save();

						$response_array = array('success' => true);
						$response_code = 200;
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

					}
					$response_code = 200;
				}
			}
		} else {
			//handles get request
			$token = Input::get('token');
			$owner_id = Input::get('id');
			$validator = Validator::make(
				array(
					'token' => $token,
					'owner_id' => $owner_id,
				),
				array(
					'token' => 'required',
					'owner_id' => 'required|integer'
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
			} else {

				$is_admin = $this->isAdmin($token);
				if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($owner_data->token_expiry) || $is_admin) {

						$response_array = array(
							'success' => true,
							'address' => $owner_data->address,
							'state' => $owner_data->state,
							'zipcode' => $owner_data->zipcode,

						);
						$response_code = 200;
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

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


	public function addcardtoken()
	{
		$payment_token = Input::get('payment_token');
		$last_four = Input::get('last_four');
		$token = Input::get('token');
		$owner_id = Input::get('id');

		$validator = Validator::make(
					    array(
					    	'last_four' => $last_four,
					        'payment_token' => $payment_token,
					        'token' => $token,
					        'owner_id' => $owner_id,
					    ),
					    array(
					    	'last_four' => 'required',
					        'payment_token' => 'required',
					        'token' => 'required',
					        'owner_id' => 'required|integer'
					    )
					);

		if ($validator->fails())
		{
		   $error_messages = $validator->messages();
		   $response_array = array('success' => false , 'error' => 'Invalid Input' , 'error_code' => 401);
		   $response_code = 200;
		}
		else{
			$is_admin = $this->isAdmin($token);
			if( $owner_data = $this->getOwnerData($owner_id,$token,$is_admin)){
			// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin)
				{

					try
					{
						
						if(Config::get('app.default_payment') == 'stripe')
						{
							Stripe::setApiKey(Config::get('app.stripe_secret_key'));

							$customer = Stripe_Customer::create(array(
										  "card" => $payment_token,
										  "description" => $owner_data->email)
										);

							if($customer){
								$customer_id = $customer->id;
								$payment = new Payment;
								$payment->owner_id = $owner_id;
								$payment->customer_id = $customer_id;
								$payment->last_four = $last_four;
								$payment->card_token = $customer->cards->data[0]->id;
								$payment->save();
								$response_array = array('success' => true);
								$response_code = 200;
							}
							else{
								$response_array = array('success' => false , 'error' => 'Could not create client ID' , 'error_code' => 450);
								$response_code = 200;
							}
						}
						else{
							Braintree_Configuration::environment(Config::get('app.braintree_environment'));
							Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
							Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
							Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
							$result = Braintree_Customer::create(array(
							    'paymentMethodNonce' => $payment_token
							));
							if ($result->success) {

								$customer_id = $result->customer->id;
								$payment = new Payment;
								$payment->owner_id = $owner_id;
								$payment->customer_id = $customer_id;
								$payment->last_four = $last_four;
								$payment->card_token = $result->customer->creditCards[0]->token;
								$payment->save();

								$response_array = array('success' => true);
								$response_code = 200;
							}
							else{
								$response_array = array('success' => false , 'error' => 'Could not create client ID' , 'error_code' => 450);
								$response_code = 200;
							}
						}
						
						
					} catch(Exception $e) {
						$response_array = array('success' => false , 'error' => $e , 'error_code' => 405);
						$response_code = 200;
					}

					
				}
				else{
					$response_array = array('success' => false , 'error' => 'Token Expired' , 'error_code' => 405);
					$response_code = 200;
				}
			}
			else{
				if($is_admin)
				{
					$response_array = array('success' => false , 'error' => 'Owner ID not Found' , 'error_code' => 410);

				}
				else{
					$response_array = array('success' => false , 'error' => 'Not a valid token' , 'error_code' => 406);

				}					
				$response_code = 200;
			}
		}
	
		$response = Response::json($response_array , $response_code);
		return $response; 

	}

	public function deletecardtoken()
	{
		$card_id = Input::get('card_id');
		$token = Input::get('token');
		$owner_id = Input::get('id');

		$validator = Validator::make(
					    array(
					    	'card_id' => $card_id,
					        'token' => $token,
					        'owner_id' => $owner_id,
					    ),
					    array(
					    	'card_id' => 'required',
					        'token' => 'required',
					        'owner_id' => 'required|integer'
					    )
					);

		if ($validator->fails())
		{
		   $error_messages = $validator->messages();
		   $response_array = array('success' => false , 'error' => 'Invalid Input' , 'error_code' => 401);
		   $response_code = 200;
		}
		else{
			$is_admin = $this->isAdmin($token);
			if( $owner_data = $this->getOwnerData($owner_id,$token,$is_admin)){
			// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin)
				{
					if($payment = Payment::find($card_id))
					{
						if($payment->owner_id == $owner_id)
						{
							Payment::find($card_id)->delete();
							$response_array = array('success' => true );
							$response_code = 200;
						}
						else{
							$response_array = array('success' => false , 'error' => 'Card ID and Owner ID Doesnot matches' , 'error_code' => 440);
							$response_code = 200;
						}
					}
					else{
						$response_array = array('success' => false , 'error' => 'Card not found' , 'error_code' => 441);
						$response_code = 200;
					}

					
				}
				else{
					$response_array = array('success' => false , 'error' => 'Token Expired' , 'error_code' => 405);
					$response_code = 200;
				}
			}
			else{
				if($is_admin)
				{
					$response_array = array('success' => false , 'error' => 'Owner ID not Found' , 'error_code' => 410);

				}
				else{
					$response_array = array('success' => false , 'error' => 'Not a valid token' , 'error_code' => 406);

				}					
				$response_code = 200;
			}
		}
	
		$response = Response::json($response_array , $response_code);
		return $response; 

	}

	public function set_referral_code()
	{
		
		$code = Input::get('code');
		$token = Input::get('token');
		$owner_id = Input::get('id');

		$validator = Validator::make(
			array(
				'code' => $code,
				'token' => $token,
				'owner_id' => $owner_id,
			),
			array(
				'code' => 'required',
				'token' => 'required',
				'owner_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
				$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin)
				{
					// Do necessary operations

					$ledger_count = Ledger::where('referral_code',$code)->count();
					if($ledger_count > 0)
					{
						$response_array = array('success' => false, 'error' => 'This Code already is taken by another user');
					}
					else
					{
						$ledger = new Ledger;
						$ledger->owner_id = $owner_id;
						$ledger->referral_code = $code;
						$ledger->save();

						$response_array = array('success' => true);
					}

					
					$response_code = 200;
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}

	public function get_referral_code()
	{
		
		$token = Input::get('token');
		$owner_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'owner_id' => $owner_id,
			),
			array(
				'token' => 'required',
				'owner_id' => 'required|integer'
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
			if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin) {
					// Do necessary operations

					$ledger = Ledger::where('owner_id',$owner_id)->first();
					if($ledger)
					{
						$response_array = array(
									'success' => true ,
									'referral_code' => $ledger->referral_code,
									'total_referrals' => $ledger->total_referrals,
									'amount_earned' => $ledger->amount_earned,
									'amount_spent' => $ledger->amount_spent,
									'balance_amount' => $ledger->amount_earned - $ledger->amount_spent,
									);
					}
					else
					{
						$response_array = array('success' => false, 'error' => 'This user does not have a referral code');
					
						
					}

					
					$response_code = 200;
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
					$response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

				} 
				else 
				{
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	public function get_cards()
	{
		
		$token = Input::get('token');
		$owner_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'owner_id' => $owner_id,
			),
			array(
				'token' => 'required',
				'owner_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin) {
					// Do necessary operations

						$payment_data = Payment::where('owner_id',$owner_id)->get();
						$payments = array();
						foreach ($payment_data as $data) {
							$data['id'] = $data->id;
							$data['customer_id'] = $data->customer_id;
							$data['last_four'] = $data->last_four;
							array_push($payments, $data);
						}
						$response_array = array(
									'success' => true ,
									'payments' => $payments
									);
					
					$response_code = 200;
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

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
		
		$token = Input::get('token');
		$owner_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'owner_id' => $owner_id,
			),
			array(
				'token' => 'required',
				'owner_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin) {
					// Do necessary operations

						$request_data = DB::table('request')
										->where('request.owner_id',$owner_id)
										->where('is_completed',1)
										->where('is_cancelled',0)
										->leftJoin('walker','request.confirmed_walker','=','walker.id')
										->leftJoin('walker_type','walker.type','=','walker_type.id')
										->select('request.id','request.request_start_time','walker.first_name',
											'walker.last_name','walker.phone','walker.email','walker.picture','walker.bio',
											'walker_type.name as type','walker_type.icon',
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
							$request['type'] = $data->type;
							$request['type_icon'] = $data->icon;
							$request['walker']['first_name'] = $data->first_name;
							$request['walker']['last_name'] = $data->last_name;
							$request['walker']['phone'] = $data->phone;
							$request['walker']['email'] = $data->email;
							$request['walker']['picture'] = $data->picture;
							$request['walker']['bio'] = $data->bio;
							$request['walker']['type'] = $data->type;
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
					$response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	public function update_profile()
	{
		
		$token = Input::get('token');
		$owner_id = Input::get('id');
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
				'owner_id' => $owner_id,
				'email' => $email,
				'picture' => $picture,
				'zipcode' => $zipcode
			),
			array(
				'token' => 'required',
				'owner_id' => 'required|integer',
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
			if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($owner_data->token_expiry) || $is_admin) {
					// Do necessary operations
						$owner = Owner::find($owner_id);
						if($first_name)
						{
							$owner->first_name = $first_name;
						}
						if($last_name)
						{
							$owner->last_name = $last_name;
						}
						if($email)
						{
							$owner->email = $email;
						}
						if($phone)
						{
							$owner->phone = $phone;
						}
						if($bio)
						{
							$owner->bio = $bio;
						}
						if($address)
						{
							$owner->address = $address;
						}
						if($state)
						{
							$owner->state = $state;
						}
						if($country)
						{
							$owner->country = $country;
						}
						if($zipcode)
						{
							$owner->zipcode = $zipcode;
						}
						if($password)
						{
							$owner->password = Hash::make($password);
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
							if(Config::get('app.s3_bucket') != "")
							{
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
								
							}
							else{
									$s3_url = asset_url().'/uploads/'.$local_url;
							}

							
							$owner->picture = $s3_url;
						}

						$owner->save();

						$response_array = array(
								'success' => true,
								'id' => $owner->id,
								'first_name' => $owner->first_name,
								'last_name' => $owner->last_name,
								'phone' => $owner->phone,
								'email' => $owner->email,
								'picture' => $owner->picture,
								'bio' => $owner->bio,
								'address' => $owner->address,
								'state' => $owner->state,
								'country' => $owner->country,
								'zipcode' => $owner->zipcode,
								'login_by' => $owner->login_by,
								'social_unique_id' => $owner->social_unique_id,
								'device_token' => $owner->device_token,
								'device_type' => $owner->device_type,
								'token' => $owner->token,
							);
					

					
					$response_code = 200;
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

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
