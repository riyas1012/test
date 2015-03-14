<?php

class WebUserController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 *
	 * @return Response
	 */

	public $status = 0;

	public function __construct()
    {
        if (Config::get('app.production'))
		{
		    echo "Something cool is going to be here soon.";
		    die();
		}

        $this->beforeFilter(function()
        {
            if (!Session::has('user_id'))
			{
			    return Redirect::to('/user/signin');
			}
			else{
				$user_id = Session::get('user_id');
				$owner = Owner::find($user_id);
				Session::put('user_name', $owner->first_name." ".$owner->last_name);
				Session::put('user_pic', $owner->picture);	
			}

        } ,array('except' => array(
        							'userLogin',
        							'userVerify',
        							'userForgotPassword',
        							'userRegister',
        							'userSave'
        						)));

        
		$date = date("Y-m-d H:i:s");
		$time_limit = date("Y-m-d H:i:s",strtotime($date)-(3*60*60));
		$owner_id = Session::get('user_id');

		$current_request = Requests::where('owner_id',$owner_id)
								 ->where('is_cancelled',0)
								 ->where('created_at','>',$time_limit)
								 ->orderBy('created_at','desc')
								 ->where(function($query)
						            {
						            	$query->where('status',0)->orWhere(function($query_inner)
						            	{
						                	$query_inner->where('status', 1)
						                      ->where('is_walker_rated', 0);
						                });
						            })
								 ->first();
		$this->status = 0;
		if($current_request)
		{
			if($current_request->confirmed_walker)
			{
				$walker = Walker::find($current_request->confirmed_walker);
			}
			
			if ($current_request->is_completed) {
				$this->status = 5;
			}
			elseif ($current_request->is_started) {
				$this->status = 4;
			}
			elseif ($current_request->is_walker_arrived) {
				$this->status = 3;
			}
			elseif ($current_request->is_walker_started) {
				$this->status = 2;
			}
			elseif ($current_request->confirmed_walker) {
				$this->status = 1;
			}
			else{
				if($current_request->status == 1)
				{
					$this->status = 6;
				}
			}
			Session::put('status', $this->status);	
			Session::put('request_id', $current_request->id);
		}


    }

    public function saveUserPayment(){
    	$payment_token = Input::get('stripeToken');
    	$owner_id = Session::get('user_id');
    	$owner_data = Owner::find($owner_id);
    	try{
				if(Config::get('app.default_payment') == 'stripe')
				{
					Stripe::setApiKey(Config::get('app.stripe_secret_key'));

					$customer = Stripe_Customer::create(array(
							"card" => $payment_token,
							"description" => $owner_data->email)
						);

    				$last_four = substr(Input::get('number'),-4);
					if($customer){
						$customer_id = $customer->id;
						$payment = new Payment;
						$payment->owner_id = $owner_id;
						$payment->customer_id = $customer_id;
						$payment->last_four = $last_four;
						$payment->card_token = $customer->cards->data[0]->id;
						$payment->save();
						
						$message = "Your Card is successfully added.";
						$type = "success";
						return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
						
					}
					else{
						$message = "Sorry something went wrong.";
						$type = "danger";
						return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
					}
				}else{
					Braintree_Configuration::environment(Config::get('app.braintree_environment'));
					Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
					Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
					Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
					$result = Braintree_Customer::create(array(
							    "firstName" => $owner_data->first_name,
							    "lastName" => $owner_data->last_name,
							    "creditCard" => array(
							        "number" => Input::get('number'),
							        "expirationMonth" => Input::get('month'),
							        "expirationYear" => Input::get('year'),
							        "cvv" => Input::get('cvv'),
							    )
							));
					Log::info('result = '.print_r($result, true));
					if ($result->success) {
						$num = $result->customer->creditCards[0]->maskedNumber;
						$last_four = substr($num,-4);
						$customer_id = $result->customer->id;
						$payment = new Payment;
						$payment->owner_id = $owner_id;
						$payment->customer_id = $customer_id;
						$payment->last_four = $last_four;
						$payment->card_token = $result->customer->creditCards[0]->token;
						$payment->save();

						$message = "Your Card is successfully added.";
						$type = "success";
						return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
						

						
					}
					else{
						$message = "Sorry something went wrong.";
						$type = "danger";
						return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
					}
				}
				
				
			} catch(Exception $e) {
				$message = "Sorry something went wrong.";
				$type = "danger";
				return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
			}

    }

    public function saveUserReview()
    {
    	$request_id = Input::get('request_id');
    	$owner_id = Session::get('user_id');
    	$request = Requests::where('id',$request_id)->where('owner_id',$owner_id)->first();
    	if($request)
    	{
    		$review_walker = new WalkerReview;
    		$review_walker->walker_id = $request->confirmed_walker;
    		$review_walker->comment = Input::get('review');
    		$review_walker->rating = Input::get('rating');
    		$review_walker->owner_id = $owner_id;
    		$review_walker->request_id = $request->id;
    		$review_walker->save();

    		$request->is_walker_rated = 1;
    		$request->save();
    	}

    	$message = "You has successfully rated the driver.";
		$type = "success";
		return Redirect::to('/user/trips')->with('message',$message)->with('type',$type);

    }

	public function index()
	{
		//return Redirect::to('/user/signin');
	}

	public function userLogin()
	{
		return View::make('web.userLogin');
	}

	public function userRegister()
	{
		return View::make('web.userSignup');
	}

	public function userTripCancel()
	{
		$request_id = Request::segment(4);
		$owner_id = Session::get('user_id');
		$request = Requests::find($request_id);
		if($request->owner_id == $owner_id)
		{
			 Requests::where('id', $request_id)->update(array('is_cancelled' => 1));
             RequestMeta::where('request_id', $request_id)->update(array('is_cancelled' => 1));
             if ($request->confirmed_walker) {
                $walker = Walker::find($request->confirmed_walker);
                $walker->is_available = 1;
                $walker->save();
             }
             if ($request->current_walker) {


                $msg_array = array();
                $msg_array['request_id'] = $request_id;
                $msg_array['unique_id'] = 2;

                $owner = Owner::find($owner_id);
                $request_data = array();
                $request_data['owner'] = array();
                $request_data['owner']['name'] = $owner->first_name . " " . $owner->last_name;
                $request_data['owner']['picture'] = $owner->picture;
                $request_data['owner']['phone'] = $owner->phone;
                $request_data['owner']['address'] = $owner->address;
                $request_data['owner']['latitude'] = $owner->latitude;
                $request_data['owner']['longitude'] = $owner->longitude;
                $request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ? : 0;
                $request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count();

                $request_data['dog'] = array();
                if ($dog = Dog::find($owner->dog_id)) {
                    $request_data['dog']['name'] = $dog->name;
                    $request_data['dog']['age'] = $dog->age;
                    $request_data['dog']['breed'] = $dog->breed;
                    $request_data['dog']['likes'] = $dog->likes;
                    $request_data['dog']['picture'] = $dog->image_url;
                }
                $msg_array['request_data'] = $request_data;

                $title = "Request Cancelled";
				$title_lithuanian="Prašymas atšauktas";
                $message = $msg_array;
                send_notifications($request->current_walker, "walker", $title, $title_lithuanian, $message);
            }
		}

		// Redirect
		$message = "Your Request is Cancelled.";
		$type = "success";
		return Redirect::to('/user/trips')->with('message',$message)->with('type',$type);
	}


	public function userTripStatus()
	{
		$id = Request::segment(4);
		$owner_id = Session::get('user_id');
		$request = Requests::find($id);
		if($request->owner_id == $owner_id)
		{
			$status = 0;
			if ($request->is_completed) {
				$status = 5;
			}
			elseif ($request->is_started) {
				$status = 4;
			}
			elseif ($request->is_walker_arrived) {
				$status = 3;
			}
			elseif ($request->is_walker_started) {
				$status = 2;
			}
			elseif ($request->confirmed_walker) {
				$status = 1;
			}

			else{
				if($request->status == 1)
				{
					$status = 6;
				}
			}
			echo $status;
		}
	}

	public function userRequestTrip()
	{
		$date = date("Y-m-d H:i:s");
		$time_limit = date("Y-m-d H:i:s",strtotime($date)-(3*60*60));
		$owner_id = Session::get('user_id');

		$current_request = Requests::where('owner_id',$owner_id)
								 ->where('is_cancelled',0)
								 ->where('created_at','>',$time_limit)
								 ->orderBy('created_at','desc')
								 ->where(function($query)
						            {
						            	$query->where('status',0)->orWhere(function($query_inner)
						            	{
						                	$query_inner->where('status', 1)
						                      ->where('is_walker_rated', 0)
						                      ->where('confirmed_walker', '>',0);
						                });
						            })
								 ->first();

		if(!$current_request || $current_request->is_walker_rated == 1)
		{
			$payments = Payment::where('owner_id',Session::get('user_id'))->count();
			if($payments != 0)
			{
				$types = ProviderType::all();
				return View::make('web.userRequestTrip')
						->with('title','Request Trip')
						->with('types',$types)
						->with('page','request-trip');
			}
			else{
				$message = "You should add atleast one credit card to request a Trip.";
				$type = "danger";
				return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
			}
			
		}
		else{

			$owner = Owner::find($owner_id);
			$type = ProviderType::find($current_request->type);
			$status = 0;

			if ($current_request->is_completed) {
				$status = 5;
			}
			elseif ($current_request->is_started) {
				$status = 4;
			}
			elseif ($current_request->is_walker_arrived) {
				$status = 3;
			}
			elseif ($current_request->is_walker_started) {
				$status = 2;
			}
			elseif ($current_request->confirmed_walker) {
				$status = 1;
			}

			else{
				if($current_request->status == 1)
				{
					$status = 6;
				}
			}


			if($current_request->confirmed_walker)
			{
				$walker = Walker::find($current_request->confirmed_walker);
				$rating = DB::table('review_walker')->where('walker_id', '=', $current_request->confirmed_walker)->avg('rating') ?: 0;
				return View::make('web.userRequestTripStatus')
						->with('title','Trip Status')
						->with('page','trip-status')
						->with('request',$current_request)
						->with('user',$owner)
						->with('walker',$walker)
						->with('type',$type)
						->with('status',$status)
						->with('rating',$rating);
			}
			else{
				return View::make('web.userRequestTripStatus')
						->with('title','Trip Status')
						->with('page','trip-status')
						->with('request',$current_request)
						->with('user',$owner)
						->with('type',$type)
						->with('rating',0)
						->with('status',$status);
			}
			
		}
	}

	public function saveUserRequestTrip()
	{
		$latitude = Input::get('latitude');
		$longitude = Input::get('longitude');
		$type = Input::get('type');
		$owner_id = Session::get('user_id');

		$owner_data = Owner::find($owner_id);

		$settings = Settings::where('key','default_search_radius')->first();
		$distance = $settings->value;
		$query = "SELECT walker.id, 1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and type = $type and (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance  order by distance";
		$walkers = DB::select(DB::raw($query));
		$walker_list = array();
		
		$owner = Owner::find($owner_id);
		$owner->latitude = $latitude;
		$owner->longitude = $longitude;
		$owner->save();

		$request = new Requests;
		$request->owner_id = $owner_id;
		$request->type = $type;
		$request->request_start_time = date("Y-m-d H:i:s");
		$request->save();

		$i = 0;
		$first_walker_id = 0;
		foreach ($walkers as $walker) {
			$request_meta = new RequestMeta;
			$request_meta->request_id = $request->id;
			$request_meta->walker_id = $walker->id;
			if($i == 0)
			{
				$first_walker_id = $walker->id;
				$i++;

			}
			$request_meta->save();
		}
		$req = Requests::find($request->id);
		$req->current_walker = $first_walker_id;
		$req->confirmed_walker = 0;
		$req->save();

		$settings = Settings::where('key','provider_timeout')->first();
		$time_left = $settings->value;

		$message = "Your Request is successful. Please wait while we are finding a nearest cab driver for you.";
		$type = "success";
		return Redirect::to('/user/request-trip')->with('message',$message)->with('type',$type);
	

		/*
		// Send Notification
		$walker = Walker::find($first_walker_id);
		if($walker){
		$msg_array = array();
		$msg_array['unique_id'] = 1;
		$msg_array['request_id'] = $request->id;
		$msg_array['time_left_to_respond'] = $time_left;
		$owner = Owner::find($owner_id);
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
		$msg_array['request_data'] = $request_data;

		$title = "New Request";
		$message = json_encode($msg_array);
		send_notifications($first_walker_id,"walker",$title,$message);
		}


		// Send SMS 
		$settings = Settings::where('key','sms_request_created')->first();
		$pattern = $settings->value;
		$pattern = str_replace('%user%', $owner_data->first_name." ".$owner_data->last_name, $pattern);
		$pattern = str_replace('%id%', $request->id, $pattern);
		$pattern = str_replace('%user_mobile%', $owner_data->phone, $pattern);
		sms_notification(1,'admin',$pattern);

		// send email
		$settings = Settings::where('key','email_new_request')->first();
		$pattern = $settings->value;
		$pattern = str_replace('%id%', $request->id, $pattern);
		$pattern = str_replace('%url%', web_url()."/admin/request/map/".$request->id, $pattern);
		$subject = "New Request Created";
		email_notification(1,'admin',$pattern,$subject); */
	}

	public function userSave()
	{
		$first_name = Input::get('first_name');
		$last_name = Input::get('last_name');
		$email = Input::get('email');
		$phone = Input::get('phone');
		$password = Input::get('password');
		$referral_code = Input::get('referral_code');
		if(Owner::where('email',$email)->count() == 0){
			$owner = new Owner;
			$owner->first_name = $first_name;
			$owner->last_name = $last_name;
			$owner->email = $email;
			$owner->phone = $phone;
			if ($password != "") {
				$owner->password = Hash::make($password);
			}
			$owner->token = generate_token();
			$owner->token_expiry = generate_expiry();
			if($referral_code != ""){
				if($ledger = Ledger::where('referral_code',$referral_code)->first())
				{
					$referred_by = $ledger->owner_id;
					$settings = Settings::where('key','default_referral_bonus')->first();
					$referral_bonus = $settings->value;
					 
					$ledger = Ledger::find($ledger->id);
					$ledger->total_referrals = $ledger->total_referrals + 1;
					$ledger->amount_earned = $ledger->amount_earned + $referral_bonus;
					$ledger->save();

					$owner->referred_by = $ledger->owner_id;

					$response_array = array('success' => true);
					$response_code = 200;
				}
			}	

			$owner->save();

			// send email
			/*$settings = Settings::where('key','email_owner_new_registration')->first();
			$pattern = $settings->value;
			$pattern = str_replace('%name%', $owner->first_name, $pattern);
			$subject = "Welcome On Board";
			email_notification($owner->id,'owner',$pattern,$subject);*/
				
			return Redirect::to('/user/signin')->with('success', 'Ypu have successfully registered. <br>Please Login');
		
		}
		else{
			return Redirect::to('/user/signup')->with('error', 'This email ID is already registered.');
		}

	}

	public function userForgotPassword()
	{
		$email = Input::get('email');
		$owner = Owner::where('email',$email)->first();
		if($owner)
		{
			$new_password = time();
			$new_password .= rand();
			$new_password = sha1($new_password);
			$new_password = substr($new_password,0,8);
			$owner->password = Hash::make($new_password);
			$owner->save();

			// send email
			/*$settings = Settings::where('key','email_forgot_password')->first();
			$pattern = $settings->value;
			$pattern = str_replace('%password%', $new_password, $pattern);
			$subject = "Your New Password";
			email_notification($owner->id,'owner',$pattern,$subject);*/
			return Redirect::to('user/signin')->with('success', 'password reseted successfully. Please check your inbox for new password.');
		}
		else{
			return Redirect::to('user/signin')->with('error', 'This email ID is not registered with us');
		}
	}

	public function userVerify()
	{
		$email = Input::get('email');
		$password = Input::get('password');
		$owner = Owner::where('email', '=', $email)->first();
		if ($owner && Hash::check($password, $owner->password)) {
			Session::put('user_id', $owner->id);
			Session::put('user_name', $owner->first_name." ".$owner->last_name);
			Session::put('user_pic', $owner->picture);	
			return Redirect::to('/user/trips');
		}
		else{
			return Redirect::to('/user/signin')->with('error', 'Invalid email and password');
		}

	}

	public function userLogout()
	{
		Session::flush();
		return Redirect::to('/user/signin');
	}

	public function userTripDetail()
	{
		$id = Request::segment(3);
		$owner_id = Session::get('user_id');
		$request = Requests::find($id);
		if($request->owner_id == $owner_id)
		{
			$locations = WalkLocation::where('request_id',$id)
								->orderBy('id')
								->get();
			$start = WalkLocation::where('request_id',$id)
								->orderBy('id')
								->first();
			$end = WalkLocation::where('request_id',$id)
								->orderBy('id','desc')
								->first();
			$map = "https://maps-api-ssl.google.com/maps/api/staticmap?size=249x249&style=feature:landscape|visibility:off&style=feature:poi|visibility:off&style=feature:transit|visibility:off&style=feature:road.highway|element:geometry|lightness:39&style=feature:road.local|element:geometry|gamma:1.45&style=feature:road|element:labels|gamma:1.22&style=feature:administrative|visibility:off&style=feature:administrative.locality|visibility:on&style=feature:landscape.natural|visibility:on&scale=2&markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-start@2x.png|$start->latitude,$start->longitude&markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-finish@2x.png|$end->latitude,$end->longitude&path=color:0x2dbae4ff|weight:4";

			foreach ($locations as $location) {
				$map .= "|$location->latitude,$location->longitude";
			}

			$start_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$start->latitude,$start->longitude"),TRUE);
			$start_address = $start_location['results'][0]['formatted_address'];
			
			$end_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$end->latitude,$end->longitude"),TRUE);
			$end_address = $end_location['results'][0]['formatted_address'];
			
			$walker = Walker::find($request->confirmed_walker);
			$walker_review = WalkerReview::where('request_id',$id)->first();
			if($walker_review)
			{
				$rating = round($walker_review->rating);
			}
			else{
				$rating = 0;
			}


			return View::make('web.userTripDetail')
					->with('title','My Trips')
					->with('request',$request)
					->with('start_address',$start_address)
					->with('end_address',$end_address)
					->with('start',$start)
					->with('end',$end)
					->with('map_url',$map)
					->with('walker',$walker)
					->with('rating',$rating);
		}
		else{
			echo "false";
		}
	}

	public function userTrips()
	{
		$owner_id = Session::get('user_id');
		$requests = Requests::where('owner_id',$owner_id)
							->where('is_completed',1)
							->leftJoin('walker','walker.id','=','request.confirmed_walker')
							->leftJoin('walker_type','walker_type.id','=','walker.type')
							->orderBy('request_start_time','desc')

							->select('request.id','request_start_time','walker.first_name','walker.last_name','request.total as total','walker_type.name as type')
							->get();

		return View::make('web.userTrips')
					->with('title','My Trips')
					->with('requests',$requests);
	}

	public function userProfile()
	{
		$owner_id = Session::get('user_id');
		$user = Owner::find($owner_id);
		return View::make('web.userProfile')
					->with('title','My Profile')
					->with('user',$user);
	}

	public function updateUserProfile(){

		$owner_id = Session::get('user_id');
		$first_name = Input::get('first_name');
		$last_name = Input::get('last_name');
		$email = Input::get('email');
		$phone = Input::get('phone');
		$picture = Input::file('picture');
		$bio = Input::get('bio');
		$address = Input::get('address');
		$state = Input::get('state');
		$country = Input::get('country');
		$zipcode = Input::get('zipcode');

		$owner = Owner::find($owner_id);

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

		$owner->first_name = $first_name;
		$owner->last_name = $last_name;
		$owner->email = $email;
		$owner->phone = $phone;
		$owner->bio = $bio;
		$owner->address = $address;
		$owner->state = $state;
		$owner->country = $country;
		$owner->zipcode = $zipcode;
		$owner->save();
		return Redirect::to('/user/profile')->with('message','Your profile has been updated successfully')->with('type','success');
	}

	public function updateUserPassword(){
		$current_password = Input::get('current_password');
		$new_password = Input::get('new_password');
		$confirm_password = Input::get('confirm_password');

		$owner_id = Session::get('user_id');
		$owner = Owner::find($owner_id);
		
		if(Hash::check($current_password, $owner->password))
		{
			$password = Hash::make($new_password);
			$owner->password = $password;
			$owner->save();

			$message = "Your password is successfully updated";
			$type = "success";
		}
		else{
			$message = "Please enter your current password correctly";
			$type = "danger";
		}
		return Redirect::to('/user/profile')->with('message',$message)->with('type',$type);
	

	}

	public function userPayments()
	{
		$owner_id = Session::get('user_id');
		$payments = Payment::where('owner_id',$owner_id)->get();
		$ledger = Ledger::where('owner_id',$owner_id)->first();

		return View::make('web.userPayments')
					->with('title','Payments and Credits')
					->with('payments',$payments)
					->with('ledger',$ledger);

	}

	public function deleteUserPayment()
	{
		$owner_id = Session::get('user_id');
		$id = Request::segment(4);
		Payment::where('owner_id',$owner_id)
				->where('id',$id)
				->delete();
		$message = "Your card is successfully removed";
		$type = "success";
		return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
	

	}

	public function updateUserCode(){
		$owner_id = Session::get('user_id');
		$code = Input::get('code');
		$code_count = Ledger::where('referral_code','=',$code)->where('owner_id','!=',$owner_id)->count();
		if($code_count)
		{
			$message = "This referral code is already in use. Please choose a new one";
			$type = "danger";
		}
		else{
			$ledger = Ledger::where('owner_id',$owner_id)->first();
			$ledger = Ledger::find($ledger->id);
			$ledger->referral_code = $code;
			$ledger->save();
			$message = "Your referral code is successfully updated";
			$type = "success";
		}
		return Redirect::to('/user/payments')->with('message',$message)->with('type',$type);
	
	}


}