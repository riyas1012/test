<?php

class WebProviderController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 *
	 * @return Response
	 */

	public function __construct()
    {
        if (Config::get('app.production'))
		{
		    echo "Something cool is going to be here soon.";
		    die();
		}

        $this->beforeFilter(function()
        {
            if (!Session::has('walker_id'))
			{
			    return Redirect::to('/provider/signin');
			}
			else{
				$walker_id = Session::get('walker_id');
				$walker = Walker::find($walker_id);
				Session::put('is_approved', $walker->is_approved);
				Session::put('walker_name', $walker->first_name." ".$walker->last_name);
				Session::put('walker_pic', $walker->picture);	
			}

        } ,array('except' => array(
        							'providerLogin',
        							'providerVerify',
        							'providerForgotPassword',
        							'providerRegister',
        							'providerSave'
        						)));

    }

    public function toggle_availability()
    {
    	$walker_id = Session::get('walker_id');
    	$walker = Walker::find($walker_id);
		$walker->is_active = ($walker->is_active + 1 ) % 2;
		$walker->save();
    }


    public function set_location()
    {
    	$walker_id = Session::get('walker_id');
    	$walker = Walker::find($walker_id);
		$walker->latitude = Input::get('lat');
		$walker->longitude = Input::get('lng');
		$walker->save();
    }

    public function providerRequestPing()
    {
    	$walker_id = Session::get('walker_id');
    	$time = date("Y-m-d H:i:s");
		$query = "SELECT id,owner_id,TIMESTAMPDIFF(SECOND,request_start_time, '$time') as diff from request where is_cancelled = 0 and status = 0 and current_walker=$walker_id and TIMESTAMPDIFF(SECOND,request_start_time, '$time') <= 600 limit 1";
		$requests = DB::select(DB::raw($query));
		$request_data = array();
		foreach ($requests as $request) {
			$request_data['success'] = "true";
			$request_data['request_id'] = $request->id;
			$request_data['time_left_to_respond'] = 600 - $request->diff;

			$owner = Owner::find($request->owner_id);
						
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

		}

		$response_code = 200;
		$response = Response::json($request_data, $response_code);
		return $response;

    }


	public function providerLogin()
	{
		return View::make('web.providerLogin');
	}

	public function providerRegister()
	{
		$types = ProviderType::all();
		return View::make('web.providerSignup')->with('types',$types);
	}

	public function providerSave()
	{
		$first_name = Input::get('first_name');
		$last_name = Input::get('last_name');
		$email = Input::get('email');
		$phone = Input::get('phone');
		$password = Input::get('password');

		$type = Input::get('type');


		if(Walker::where('email',$email)->count() == 0){
			$walker = new Walker;
			$walker->first_name = $first_name;
			$walker->last_name = $last_name;
			$walker->email = $email;
			$walker->phone = $phone;

			$walker->type = $type;
			$walker->is_available = 1;

			if ($password != "") {
				$walker->password = Hash::make($password);
			}
			$walker->token = generate_token();
			$walker->token_expiry = generate_expiry();
			$walker->save();

			// send email
			$settings = Settings::where('key','email_walker_new_registration')->first();
			$pattern = $settings->value;
			$pattern = str_replace('%name%', $walker->first_name, $pattern);
			$subject = "Welcome On Board";
			email_notification($walker->id,'walker',$pattern,$subject);
				
			return Redirect::to('provider/signin')->with('success', 'You have successfully registered. <br>Please Login');
		
		}
		else{
			return Redirect::to('provider/signup')->with('error', 'This email ID is already registered.');
		}

	}

	public function providerForgotPassword()
	{
		$email = Input::get('email');
		$walker = Walker::where('email',$email)->first();
		if($walker)
		{
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
			echo $new_password;
			//return Redirect::to('provider/signin')->with('success', 'password reseted successfully. Please check your inbox for new password.');
		}
		else{
			return Redirect::to('provider/signin')->with('error', 'This email ID is not registered with us');
		}
	}

	public function providerVerify()
	{
		$email = Input::get('email');
		$password = Input::get('password');
		$walker = Walker::where('email', '=', $email)->first();
		if ($walker && Hash::check($password, $walker->password)) {
			Session::put('walker_id', $walker->id);
			Session::put('is_approved', $walker->is_approved);
			Session::put('walker_name', $walker->first_name." ".$walker->last_name);
			Session::put('walker_pic', $walker->picture);	
			return Redirect::to('provider/trips');
		}
		else{
			return Redirect::to('provider/signin')->with('error', 'Invalid email and password');
		}

	}

	public function providerLogout()
	{
		Session::flush();
		return Redirect::to('/provider/signin');
	}

	public function providerTripChangeState()
	{
		$date = date("Y-m-d H:i:s");
		$time_limit = date("Y-m-d H:i:s",strtotime($date)-(3*60*60));
		$walker_id = Session::get('walker_id');
		$state = $request_id = Request::segment(4);
		$current_request = Requests::where('confirmed_walker',$walker_id)
								 ->where('is_cancelled',0)
								 ->where('created_at','>',$time_limit)
								 ->orderBy('created_at','desc')
								 ->where(function($query)
						            {
						            	$query->where('status',0)->orWhere(function($query_inner)
						            	{
						                	$query_inner->where('status', 1)
						                      ->where('is_dog_rated', 0);
						                });
						            })
								 ->first();
		if($current_request && $state)
		{

		 	if($state == 2)
		 	{
		 		$current_request->is_walker_started = 1;

		 		$owner = Owner::find($current_request->owner_id);
		 		$walk_location = new WalkLocation;
				$walk_location->request_id = $current_request->id;
				$walk_location->latitude = $owner->latitude;
				$walk_location->longitude = $owner->longitude;
				$walk_location->distance = 0;
				$walk_location->save();

		 	}
		 	if($state == 3)
		 	{
		 		$current_request->is_walker_arrived = 1;
		 	}
		 	if($state == 4)
		 	{
		 		$current_request->is_started = 1;

		 	}

		 	if($state == 6)
		 	{
		 		$current_request->is_dog_rated = 1;
		 		$current_request->save();

		 		$review_dog = new DogReview;
	    		$review_dog->walker_id = $current_request->confirmed_walker;
	    		$review_dog->comment = Input::get('review');
	    		$review_dog->rating = Input::get('rating');
	    		$review_dog->owner_id =$current_request->owner_id;
	    		$review_dog->request_id = $current_request->id;
	    		$review_dog->save();

	    		$message = "You has successfully rated the owner.";
				$type = "success";
				return Redirect::to('/provider/trips')->with('message',$message)->with('type',$type);
		 	}

		 	if($state == 5)
		 	{
		 		
		 		$address = urlencode(Input::get('address'));
		 		$end_address = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address"),TRUE);

		 		$end_location = $end_address['results'][0]['geometry'];
		 		$latitude = $end_location['location']['lat'];
		 		$longitude = $end_location['location']['lng'];

		 		$request_id = $current_request->id;
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
				else{
					$distance = 0;
				}

				$walk_location = new WalkLocation;
				$walk_location->request_id = $request_id;
				$walk_location->latitude = $latitude;
				$walk_location->longitude = $longitude;
				$walk_location->distance = $distance;
				$walk_location->save();
				
				Walker::where('id','=',$walker_id)->update(array('is_available' => 1));

				// Calculate Rerquest Stats

				$time = 0;

				$time_query = "SELECT TIMESTAMPDIFF(SECOND,MIN(created_at),MAX(created_at)) as diff
				FROM walk_location where request_id = $current_request->id
				GROUP BY request_id limit 1 ";
				
				$time_data = DB::select(DB::raw($time_query));
				foreach ($time_data as $time_diff) {
					$time = $time_diff->diff;
				}
				$time = $time / 60;

				$walker_data = Walker::find($current_request->confirmed_walker);
				if(!$walker_data->type)
				{	
					$settings = Settings::where('key','price_per_unit_distance')->first();
					$price_per_unit_distance = $settings->value;
					$settings = Settings::where('key','price_per_unit_time')->first();
					$price_per_unit_time = $settings->value;
					$settings = Settings::where('key','base_price')->first();
					$base_price = $settings->value;
				}
				else{
					$provider_type = ProviderType::find($walker_data->type);
					$base_price = $provider_type->base_price;
					$price_per_unit_distance = $provider_type->price_per_unit_distance;
					$price_per_unit_time = $provider_type->price_per_unit_time;
				}

				$settings = Settings::where('key','default_charging_method_for_users')->first();
				$pricing_type = $settings->value;
				$settings = Settings::where('key','default_distance_unit')->first();
				$unit = $settings->value;
				$distance = convert($distance,$unit);
				if($pricing_type == 1)
				{
					$distance_cost = $price_per_unit_distance*$distance;
					$time_cost = $price_per_unit_time*$time;
					$total = $base_price+$distance_cost+$time_cost;
				}
				else{
					$distance_cost = 0;
					$time_cost = 0;
					$total = $base_price;
				}
				
				$current_request->is_completed = 1;
				$current_request->distance = $distance;
				$current_request->time = $time;
				$current_request->base_price = $base_price;
				$current_request->distance_cost = $distance_cost;
				$current_request->time_cost = $time_cost;
				$current_request->total = $total;

				// charge client

				// charge client

				
				$ledger = Ledger::where('owner_id',$current_request->owner_id)->first();
				
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
					$current_request->is_paid = 1;
				}
				else{
					
					/*
					$payment_data = Payment::where('owner_id',$current_request->owner_id)->first();
					
					if( $payment_data )
					{
						$customer_id = $payment_data->customer_id;
						try{
							if(Config::get('app.default_payment') == 'stripe')
							{
								Stripe::setApiKey(Config::get('app.stripe_secret_key'));
								Stripe_Charge::create(array(
										  "amount"   => $total * 100, 
										  "currency" => "usd",
										  "customer" => $customer_id)
										);
								$request->is_paid = 1;
							}
							else{

								Braintree_Configuration::environment(Config::get('app.braintree_environment'));
								Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
								Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
								Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));

								$result = Braintree_Transaction::sale(array(
								  'amount' => round($total,2),
								  'paymentMethodNonce' => $customer_id
								));
								
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
					*/



				}

				$current_request->card_payment = $total;
				$current_request->ledger_payment = $current_request->total - $total;
				
				$current_request->save();


		 	}
		 	
		 	$current_request->save();

		}
		return Redirect::to('/provider/tripinprogress');
	}

	public function providerTripInProgress()
	{
		$date = date("Y-m-d H:i:s");
		$time_limit = date("Y-m-d H:i:s",strtotime($date)-(3*60*60));
		$walker_id = Session::get('walker_id');

		$current_request = Requests::where('confirmed_walker',$walker_id)
								 ->where('is_cancelled',0)
								 ->where('created_at','>',$time_limit)
								 ->orderBy('created_at','desc')
								 ->where(function($query)
						            {
						            	$query->where('status',0)->orWhere(function($query_inner)
						            	{
						                	$query_inner->where('status', 1)
						                      ->where('is_dog_rated', 0);
						                });
						            })
								 ->first();

		if(!$current_request || $current_request->is_dog_rated == 1)
		{
			$message = "You don't have any trips currently in progress.";
			$type = "danger";
			return Redirect::to('/provider/trips')->with('message',$message)->with('type',$type);
		}
		else{
			$owner = Owner::find($current_request->owner_id);
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

			if($current_request->confirmed_walker)
			{
				$walker = Walker::find($current_request->confirmed_walker);
				$rating = DB::table('review_dog')->where('owner_id', '=', $current_request->owner_id)->avg('rating') ?: 0;
				return View::make('web.providerRequestTripStatus')
						->with('title','Trip Status')
						->with('page','trip-status')
						->with('request',$current_request)
						->with('user',$owner)
						->with('walker',$walker)
						->with('type',$type)
						->with('status',$status)
						->with('rating',$rating);
			}
			
		}
			
			
	}

	public function approve_request()
	{
		$request_id = Request::segment(4);
		$walker_id = Session::get('walker_id');
		$request = Requests::find($request_id);
		if($request->current_walker == $walker_id){
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
					'is_walk_started' => $request->is_started,	
					'is_completed' => $request->is_completed,
					'is_walker_rated' => $request->is_walker_rated,
					'walker' => $walker_data,
					'bill' => $bill,
				);
			$title = "Walker Accepted";
			$title_lithuanian="Walker Priimamos";
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

		return Redirect::to('/provider/tripinprogress');

	}

	public function decline_request()
	{
		$request_id = Request::segment(4);
		$walker_id = Session::get('walker_id');
		$request = Requests::find($request_id);
		if($request->current_walker == $walker_id){
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
				if ($walker) {
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
			else{
				// request ended
				Requests::where('id','=',$request_id)->update(array('current_walker' => 0,'status' => 1));
			}
		}
		return Redirect::to('/provider/trips');

	}


	public function providerTrips()
	{
		$start_date = Input::get('start_date');
		$end_date = Input::get('end_date');
		$submit = Input::get('submit');

		$start_time = date("Y-m-d H:i:s",strtotime($start_date));
		$end_time = date("Y-m-d H:i:s",strtotime($end_date));
		$start_date = date("Y-m-d",strtotime($start_date));
		$end_date = date("Y-m-d",strtotime($end_date));

		if(!Input::get('start_date') && !Input::get('end_date')){

			$walker_id = Session::get('walker_id');
			$requests = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->leftJoin('walker','walker.id','=','request.confirmed_walker')
								->leftJoin('walker_type','walker_type.id','=','walker.type')
								->leftJoin('owner','owner.id','=','request.owner_id')
								->orderBy('request_start_time','desc')
								->select('request.id','request_start_time','owner.first_name','owner.last_name','request.total as total','walker_type.name as type','request.distance','request.time')
								->get();

			$total_rides = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->count();

			$total_distance = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->sum('distance');
			
			$settings = Settings::where('key','default_distance_unit')->first();
			$unit = $settings->value;

			$total_earnings = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->sum('total');

			$average_rating = DogReview::where('walker_id',$walker_id)
								->avg('rating');
		}
		else{

			$walker_id = Session::get('walker_id');
			$requests = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->where('request_start_time','>=',$start_time)
								->where('request_start_time','<=',$end_time)
								->leftJoin('walker','walker.id','=','request.confirmed_walker')
								->leftJoin('walker_type','walker_type.id','=','walker.type')
								->leftJoin('owner','owner.id','=','request.owner_id')
								->orderBy('request_start_time','desc')
								->select('request.id','request_start_time','owner.first_name','owner.last_name','request.total as total','walker_type.name as type','request.distance','request.time')
								->get();

			$total_rides = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->where('request_start_time','>=',$start_time)
								->where('request_start_time','<=',$end_time)
								->count();

			$total_distance = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->where('request_start_time','>=',$start_time)
								->where('request_start_time','<=',$end_time)
								->sum('distance');

			$total_earnings = Requests::where('confirmed_walker',$walker_id)
								->where('is_completed',1)
								->where('request_start_time','>=',$start_time)
								->where('request_start_time','<=',$end_time)
								->sum('total');

			$average_rating = DogReview::where('walker_id',$walker_id)
								->where('created_at','>=',$start_time)
								->where('created_at','<=',$end_time)
								->avg('rating');
		}

		if(!Input::get('submit') || Input::get('submit') == 'filter')
		{
			return View::make('web.providerTrips')
					->with('title','My Trips')
					->with('requests',$requests)
					->with('total_rides',$total_rides)
					->with('total_distance',$total_distance)
					->with('total_earnings',$total_earnings)
					->with('average_rating',$average_rating);
		}
		else{

				header('Content-Type: text/csv; charset=utf-8');
				header('Content-Disposition: attachment; filename=data.csv');

			    $handle = fopen('php://output', 'w');
			    fputcsv($handle, array('Date', 'Customer name', 'Type of Service', 'Distance (Miles)','Time (Minutes)','Earning'));

			    foreach ($requests as $request) {
			    	fputcsv($handle, array(date('l, F d Y h:i A',strtotime($request->request_start_time)),$request->first_name." ".$request->last_name,$request->type,$request->distance,$request->time,$request->total));
			    }

			    fputcsv($handle, array());
			    fputcsv($handle, array());
			    fputcsv($handle, array('Total Rides',$total_rides));
			    fputcsv($handle, array('Total Distance Covered (Miles)',$total_distance));
			    fputcsv($handle, array('Average Rating',$average_rating));
			    fputcsv($handle, array('Total Earning',$total_earnings));

			    fclose($handle);

			    

		}
		
	}

	public function providerTripDetail()
	{
		$id = Request::segment(3);
		$walker_id = Session::get('walker_id');
		$request = Requests::find($id);
		if($request->confirmed_walker == $walker_id)
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
			
			$owner = Owner::find($request->owner_id);
			$owner_review = DogReview::where('request_id',$id)->first();
			if($owner_review)
			{
				$rating = round($owner_review->rating);
			}
			else{
				$rating = 0;
			}


			return View::make('web.providerTripDetail')
					->with('title','My Trips')
					->with('request',$request)
					->with('start_address',$start_address)
					->with('end_address',$end_address)
					->with('start',$start)
					->with('end',$end)
					->with('map_url',$map)
					->with('owner',$owner)
					->with('rating',$rating);
		}
		else{
			echo "false";
		}
	}


	public function providerProfile()
	{
		$walker_id = Session::get('walker_id');
		$user = Walker::find($walker_id);
		$type = ProviderType::all();
		$ps = ProviderServices::where('provider_id',$walker_id)->get();
		return View::make('web.providerProfile')
					->with('title','My Profile')
					->with('user',$user)
					->with('type',$type)
					->with('ps',$ps);
	}

	public function updateProviderProfile(){

		foreach (Input::get('service') as $key) {
			$serv = ProviderType::where('id',$key)->first();
			$pserv[] = $serv->name;
		}
		$walker_id = Session::get('walker_id');
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

		$walker = Walker::find($walker_id);

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

			
			$walker->picture = $s3_url;
		}

		$walker->first_name = $first_name;
		$walker->last_name = $last_name;
		$walker->email = $email;
		$walker->phone = $phone;
		$walker->bio = $bio;
		$walker->address = $address;
		$walker->state = $state;
		$walker->country = $country;
		$walker->zipcode = $zipcode;
		$walker->save();

		foreach (Input::get('service') as $ke) {
			$proviserv = ProviderServices::where('provider_id',$walker->id)->first();
			if($proviserv != NULL){
				DB::delete("delete from walker_services where provider_id = '".$walker->id."';");
			}
		}
		$base_price = Input::get('service_base_price');
		$service_price_distance = Input::get('service_price_distance');
		$service_price_time = Input::get('service_price_time');
		foreach (Input::get('service') as $key) {
			$prserv = new ProviderServices;
			$prserv->provider_id = $walker->id;
			$prserv->type = $key;
			$prserv->base_price = $base_price[$key-1];
			$prserv->price_per_unit_distance = $service_price_distance[$key-1];
			$prserv->price_per_unit_time = $service_price_time[$key-1];
			$prserv->save();
		}
		
		return Redirect::to('/provider/profile')->with('message','Your profile has been updated successfully')->with('type','success');
	}

	public function updateProviderPassword(){
		$current_password = Input::get('current_password');
		$new_password = Input::get('new_password');
		$confirm_password = Input::get('confirm_password');

		$walker_id = Session::get('walker_id');
		$walker = Walker::find($walker_id);
		
		if(Hash::check($current_password, $walker->password))
		{
			$password = Hash::make($new_password);
			$walker->password = $password;
			$walker->save();

			$message = "Your password is successfully updated";
			$type = "success";
		}
		else{
			$message = "Please enter your current password correctly";
			$type = "danger";
		}
		return Redirect::to('/provider/profile')->with('message',$message)->with('type',$type);
	

	}


	public function providerDocuments()
	{
		$walker_id = Session::get('walker_id');
		$documents = DB::table('documents')
						->leftJoin('walker_documents','documents.id','=','walker_documents.document_id')
						->select('name','url','documents.id')
						->get();

		$walker = Walker::find($walker_id);
		
		$status = 0;

		foreach ($documents as $document) {
			if(!$document->url)
			{
				$status = -1;
			}
		}

		if($walker->is_approved)
		{
			$status = 1;
		}

	



		return View::make('web.providerDocuments')
					->with('title','My Documents')
					->with('documents',$documents)
					->with('status',$status);
	}

	public function providerUpdateDocuments()
	{
			$inputs = Input::all();
			$walker_id = Session::get('walker_id');
			$walker_document =  new WalkerDocument;

			foreach ($inputs as $key => $input) {

				$walker_document->walker_id = $walker_id;
				$walker_document->document_id = $key;

				if($input){
					$file_name = time();
					$file_name .= rand();
					$file_name = sha1($file_name);

					$ext = $input->getClientOriginalExtension();
					$input->move(public_path() . "/uploads", $file_name . "." . $ext);
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

					
					$walker_document->url = $s3_url;
					$walker_document->save();
					
			}
		}

		$message = "Your documents are successfully updated.";
		$type = "success";
		return Redirect::to('/provider/documents')->with('message',$message)->with('type',$type);

	}




}