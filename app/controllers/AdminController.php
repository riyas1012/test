<?php

class AdminController extends BaseController {

	public function __construct()
    {
        $this->beforeFilter(function()
        {
            if (!Auth::check())
			{
			    return Redirect::to('/admin/login');
			}

        } ,array('except' => array('login','verify', 'add')));
    }

	private function _braintreeConfigure()
	{
		Braintree_Configuration::environment(Config::get('app.braintree_environment'));
		Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
		Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
		Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
	}

    public function add()
    {
        $user = new User;
        $user->username = Input::get('username');
        $user->password = $user->password = Hash::make(Input::get('password'));
        $user->save();

    }
    public function report()
    {
    	$start_date = Input::get('start_date');
		$end_date = Input::get('end_date');
		$submit = Input::get('submit');
		$walker_id = Input::get('walker_id');
		$owner_id = Input::get('owner_id');
		$status = Input::get('status');

		$start_time = date("Y-m-d H:i:s",strtotime($start_date));
		$end_time = date("Y-m-d H:i:s",strtotime($end_date));
		$start_date = date("Y-m-d",strtotime($start_date));
		$end_date = date("Y-m-d",strtotime($end_date));

    	$query = DB::table('request')
    				->leftJoin('owner','request.owner_id','=','owner.id')
    				->leftJoin('walker','request.confirmed_walker','=','walker.id')
    				->leftJoin('walker_type','walker.type','=','walker_type.id');
    				
    	if(Input::get('start_date') && Input::get('end_date'))
		{	
    		$query = $query->where('request_start_time','>=',$start_time)
					   ->where('request_start_time','<=',$end_time);
		}

		if(Input::get('walker_id') && Input::get('walker_id') != 0)
		{
			$query = $query->where('request.confirmed_walker','=',$walker_id);
		}

		if(Input::get('owner_id') && Input::get('owner_id') != 0)
		{
			$query = $query->where('request.owner_id','=',$owner_id);
		}

		if(Input::get('status') && Input::get('status') != 0)
		{
			if($status == 1)
			{
				$query = $query->where('request.is_completed','=',1);
			}
			else
			{
				$query = $query->where('request.is_cancelled','=',1);
			}
		}
		else
		{

			$query = $query->where(function($que)
            {
                $que->where('request.is_completed','=',1)
							->orWhere('request.is_cancelled','=',1);
            });

            
		}

		$walks = $query->select('request.request_start_time','walker_type.name as type','request.ledger_payment','request.card_payment','owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled');
		
		$walks = $walks->paginate(10);
		$query = DB::table('request')
    				->leftJoin('owner','request.owner_id','=','owner.id')
    				->leftJoin('walker','request.confirmed_walker','=','walker.id')
    				->leftJoin('walker_type','walker.type','=','walker_type.id');
    				
    	if(Input::get('start_date') && Input::get('end_date')){	
    		$query = $query->where('request_start_time','>=',$start_time)
					   ->where('request_start_time','<=',$end_time);
		}

		if(Input::get('walker_id') && Input::get('walker_id') != 0)
		{
			$query = $query->where('request.confirmed_walker','=',$walker_id);
		}

		if(Input::get('owner_id') && Input::get('owner_id') != 0)
		{
			$query = $query->where('request.owner_id','=',$owner_id);
		}

		$completed_rides = $query->where('request.is_completed',1)->count();
		

		$query = DB::table('request')
    				->leftJoin('owner','request.owner_id','=','owner.id')
    				->leftJoin('walker','request.confirmed_walker','=','walker.id')
    				->leftJoin('walker_type','walker.type','=','walker_type.id');
    				
    	if(Input::get('start_date') && Input::get('end_date')){	
    		$query = $query->where('request_start_time','>=',$start_time)
					   ->where('request_start_time','<=',$end_time);
		}

		if(Input::get('walker_id') && Input::get('walker_id') != 0)
		{
			$query = $query->where('request.confirmed_walker','=',$walker_id);
		}

		if(Input::get('owner_id') && Input::get('owner_id') != 0)
		{
			$query = $query->where('request.owner_id','=',$owner_id);
		}
		$cancelled_rides = $query->where('request.is_cancelled',1)->count();
		

		$query = DB::table('request')
    				->leftJoin('owner','request.owner_id','=','owner.id')
    				->leftJoin('walker','request.confirmed_walker','=','walker.id')
    				->leftJoin('walker_type','walker.type','=','walker_type.id');
    				
    	if(Input::get('start_date') && Input::get('end_date')){	
    		$query = $query->where('request_start_time','>=',$start_time)
					   ->where('request_start_time','<=',$end_time);
		}

		if(Input::get('walker_id') && Input::get('walker_id') != 0)
		{
			$query = $query->where('request.confirmed_walker','=',$walker_id);
		}

		if(Input::get('owner_id') && Input::get('owner_id') != 0)
		{
			$query = $query->where('request.owner_id','=',$owner_id);
		}
		$card_payment = $query->where('request.is_completed',1)->sum('request.card_payment');
		

		$query = DB::table('request')
    				->leftJoin('owner','request.owner_id','=','owner.id')
    				->leftJoin('walker','request.confirmed_walker','=','walker.id')
    				->leftJoin('walker_type','walker.type','=','walker_type.id');
    				
    	if(Input::get('start_date') && Input::get('end_date')){	
    		$query = $query->where('request_start_time','>=',$start_time)
					   ->where('request_start_time','<=',$end_time);
		}

		if(Input::get('walker_id') && Input::get('walker_id') != 0)
		{
			$query = $query->where('request.confirmed_walker','=',$walker_id);
		}

		if(Input::get('owner_id') && Input::get('owner_id') != 0)
		{
			$query = $query->where('request.owner_id','=',$owner_id);
		}
		$credit_payment = $query->where('request.is_completed',1)->sum('request.ledger_payment');



		if(Input::get('submit') && Input::get('submit') == 'Download Report')
		{
			//header('Content-Type: text/csv; charset=utf-8');
			//header('Content-Disposition: attachment; filename=data.csv');
			$filename='report.csv';
		    $handle = fopen($filename, 'w');
		    fputcsv($handle, array('ID', 'Date', 'Type of Service','Provider','Owner','Distance (Miles)','Time (Minutes)','Earning','Ledger Payment','Card Payment'));

		    foreach ($walks as $request) 
			{
		    	fputcsv($handle, array(
		    	$request->id,
		    	date('l, F d Y h:i A',strtotime($request->request_start_time)),
		    	$request->type,
		    	$request->walker_first_name." ".$request->walker_last_name,
		    	$request->owner_first_name." ".$request->owner_last_name,
		    	$request->distance,
		    	$request->time,
		    	$request->total,
		    	$request->ledger_payment,
		    	$request->card_payment,
		    	));
		    }

		    fputcsv($handle, array());
		    fputcsv($handle, array());
		    fputcsv($handle, array('Total Trips',$completed_rides + $cancelled_rides));
		    fputcsv($handle, array('Completed Trips',$completed_rides));
		    fputcsv($handle, array('Cancelled Trips',$cancelled_rides));
		    fputcsv($handle, array('Total Payments',$credit_payment + $card_payment));
		    fputcsv($handle, array('Card Payment',$card_payment));
		    fputcsv($handle, array('Credit Payment',$credit_payment));

		    fclose($handle);

		    $headers = array(
		        'Content-Type' => 'text/csv',
				'Content-Disposition: attachment'
		    );

		    return Response::download($filename, 'report.csv', $headers);
			/*$walkers = Walker::paginate(10);
	    	$owners = Owner::paginate(10);
			return View::make('dashboard')
						->with('title','Dashboard')
						->with('page','dashboard')
						->with('walks',$walks)
						->with('owners',$owners)
						->with('walkers',$walkers)
						->with('completed_rides',$completed_rides)
						->with('cancelled_rides',$cancelled_rides)
						->with('card_payment',$card_payment)
						->with('credit_payment',$credit_payment);*/
		}
		else
		{
			$walkers = Walker::get();
	    	$owners = Owner::get();
	    	return View::make('dashboard')
						->with('title','Dashboard')
						->with('page','dashboard')
						->with('walks',$walks)
						->with('owners',$owners)
						->with('walkers',$walkers)
						->with('completed_rides',$completed_rides)
						->with('cancelled_rides',$cancelled_rides)
						->with('card_payment',$card_payment)
						->with('credit_payment',$credit_payment);
		}

    }
    //admin control

    public function admins()
	{
		Session::forget('type');
		Session::forget('valu');
		$admins = User::paginate(10);
		return View::make('admins')
					->with('title','Admin Control')
					->with('page','admins')
					->with('admin',$admins);
	}

	public function add_admin()
	{
		$admin = User::all();
		return View::make('add_admin')
					->with('title','Add Admin')
					->with('page','add_admin')
					->with('admin',$admin);
	}

	public function add_admin_do()
	{
		$admin = new User;
		$admin->username = Input::get('username');
		$admin->password = $admin->password = Hash::make(Input::get('password'));
		$admin->save();
		return Redirect::to("/admin/add_admin?success=1");
	}

public function edit_admins()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$admin = User::find($id);
		Log::info("admin = ". print_r($admin,true));
		if ($admin) {
			return View::make('edit_admin')
					->with('title','Edit Admin')
					->with('page','admins')
					->with('success',$success)
					->with('admin',$admin);
		}
		else{
			return View::make('notfound')->with('title','Error Page Not Found')->with('page','Error Page Not Found');
		}
	}

	public function update_admin()
	{
		$admin = User::find(Input::get('id'));
		$admin->username = Input::get('username');
		$pass = Input::get('password');
		if($pass != NULL){
			$admin->password = $admin->password = Hash::make($pass);
		}
		$admin->save();
		return Redirect::to("/admin/admins");
	}

	public function delete_admin()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$admin = User::find($id);
		if ($admin) {
			User::where('id',$id)->delete();
			DB::delete("delete from admin where id = '".$admin->id."';");
			return Redirect::to("/admin/admins?success=1");
		}
		else{
			return View::make('notfound')->with('title','Error Page Not Found')->with('page','Error Page Not Found');
		}
	}


	public function banking_provider()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$provider = Walker::find($id);
		if ($provider) {
			if(Config::get('app.default_payment') == 'stripe')
			{
				return View::make('banking_provider_stripe')
					->with('title','Banking Details Provider')
					->with('page','providers')
					->with('success',$success)
					->with('provider',$provider);
			}else{
				return View::make('banking_provider_braintree')
					->with('title','Banking Details Provider')
					->with('page','providers')
					->with('success',$success)
					->with('provider',$provider);
			}
		}
		else{
			return View::make('notfound')->with('title','Error Page Not Found')->with('page','Error Page Not Found');
		}
	}

	public function providerB_bankingSubmit()
	{
		$this->_braintreeConfigure();
		$result = new stdClass();
    	$result = Braintree_MerchantAccount::create(
		  array(
		    'individual' => array(
		      'firstName' => Input::get('first_name'),
		      'lastName' => Input::get('last_name'),
		      'email' => Input::get('email'),
		      'phone' => Input::get('phone'),
		      'dateOfBirth' => date('Y-m-d', strtotime(Input::get('dob'))),
		      'ssn' => Input::get('ssn'),
		      'address' => array(
		        'streetAddress' => Input::get('streetAddress'),
		        'locality' => Input::get('locality'),
		        'region' => Input::get('region'),
		        'postalCode' => Input::get('postalCode')
		      )
		    ),
		    'funding' => array(
		      'descriptor' => 'UberForX',
		      'destination' => Braintree_MerchantAccount::FUNDING_DESTINATION_BANK,
		      'email' => Input::get('bankemail'),
		      'mobilePhone' => Input::get('bankphone'),
		      'accountNumber' => Input::get('accountNumber'),
		      'routingNumber' => Input::get('routingNumber')
		    ),
		    'tosAccepted' => true,
		    'masterMerchantAccountId' => Config::get('app.masterMerchantAccountId'),
		    'id' => "anipl".Input::get('id')
		  )
		);
		
			Log::info('res = '.print_r($result,true));
		if($result->success){
			$pro = Walker::where('id',Input::get('id'))->first();
			$pro->merchant_id = $result->merchantAccount->id;
			$pro->save();
			Log::info(print_r($pro,true));
			Log::info('Adding banking details to provider from Admin = '.print_r($result,true));
			return Redirect::to("/admin/providers");
		}else{
			Log::info('Error in adding banking details: '.$result->message);
			return Redirect::to("/admin/providers");
		}
	}

	public function providerS_bankingSubmit()
	{
		$id = Input::get('id');
		Stripe::setApiKey(Config::get('app.stripe_secret_key'));
		$token_id = Input::get('stripeToken');
		// Create a Recipient
		$recipient = Stripe_Recipient::create(array(
		  "name" => Input::get('first_name')." ".Input::get('last_name'),
		  "type" => Input::get('type'),
		  "card" => $token_id,
		  "email" => Input::get('email')
		  )
		);

		$pro = Walker::where('id',Input::get('id'))->first();
		$pro->merchant_id = $recipient->id;
		$pro->card_id = $recipient->cards->data[0]->id;
		$pro->save();

		Log::info('recipient added = '.print_r($recipient,true));
		return Redirect::to("/admin/providers");
	}

    public function index()
    {
    	return Redirect::to('/admin/login');
    }

    public function get_document_types()
	{
		$types = Document::paginate(10);
		return View::make('list_document_types')
					->with('title','Document Types')
					->with('page','document-type')
					->with('types',$types);
	}

	public function searchdoc()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type=='docid'){
			$types = Document::where('id',$valu)->paginate(10);
		}elseif ($type=='docname') {
			$types = Document::where('name','like','%'.$valu.'%')->paginate(10);
		}

		return View::make('list_document_types')
					->with('title','Document Types')
					->with('page','document-type')
					->with('types',$types);
	}

	public function delete_document_type()
	{
		$id = Request::segment(4);
		Document::where('id',$id)->delete();
		return Redirect::to("/admin/document-types");
	}

	public function edit_document_type()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$document_type = Document::find($id);

		if($document_type)
		{
			$id = $document_type->id;
			$name = $document_type->name;
		}
		else{
			$id = 0;
			$name = "";
		}

		return View::make('edit_document_type')
					->with('title','Document Types')
					->with('page','document-type')
					->with('success',$success)
					->with('id',$id)
					->with('name',$name);
	}

	public function update_document_type()
	{
		$id = Input::get('id');
		$name = Input::get('name');

		if ($id == 0) {
			$document_type = new Document;
		}
		else{
			$document_type = Document::find($id);
		}


		$document_type->name = $name;
		$document_type->save();

		return Redirect::to("/admin/document-type/edit/$document_type->id?success=1");
	}

    public function get_provider_types()
	{

		$types = ProviderType::paginate(10);
		return View::make('list_provider_types')
					->with('title','Provider Types')
					->with('page','provider-type')
					->with('types',$types);
	}

	public function searchpvtype()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type=='provid'){
			$types = ProviderType::where('id',$valu)->paginate(10);
		}elseif ($type=='provname') {
			$types = ProviderType::where('name','like','%'.$valu.'%')->paginate(10);
		}

		return View::make('list_provider_types')
					->with('title','Provider Types')
					->with('page','provider-type')
					->with('types',$types);
	}

	public function delete_provider_type()
	{
		$id = Request::segment(4);
		ProviderType::where('id',$id)->where('is_default',0)->delete();
		return Redirect::to("/admin/provider-types");
	}

	public function edit_provider_type()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$providers_type = ProviderType::find($id);

		if($providers_type)
		{
			$id = $providers_type->id;
			$name = $providers_type->name;
			$is_default = $providers_type->is_default;
			$base_price = $providers_type->base_price;
			$price_per_unit_distance = $providers_type->price_per_unit_distance;
			$price_per_unit_time = $providers_type->price_per_unit_time;
			$icon = $providers_type->icon;
		}
		else{
			$id = 0;
			$name = "";
			$is_default = "";
			$base_price = "";
			$price_per_unit_time = "";
			$price_per_unit_distance = "";
			$icon = "";
		}

		return View::make('edit_provider_type')
					->with('title','Provider Types')
					->with('page','provider-type')
					->with('success',$success)
					->with('id',$id)
					->with('name',$name)
					->with('is_default',$is_default)
					->with('base_price',$base_price)
					->with('icon',$icon)
					->with('price_per_unit_time',$price_per_unit_time)
					->with('price_per_unit_distance',$price_per_unit_distance);
	}

	public function update_provider_type()
	{
		$id = Input::get('id');
		$name = Input::get('name');
		$is_default = Input::get('is_default');

		if($is_default)
		{	
			if($is_default == 1){
			ProviderType::where('is_default',1)->update(array('is_default' => 0));
			}
		}
		else{
			$is_default = 0;
		}


		if ($id == 0) {
			$providers_type = new ProviderType;
		}
		else{
			$providers_type = ProviderType::find($id);
		}
				if (Input::hasFile('icon'))
		{
			// Upload File
			$file_name = time();
			$file_name .= rand();
			$ext =  Input::file('icon')->getClientOriginalExtension();
			Input::file('icon')->move(public_path()."/uploads",$file_name.".".$ext);
			$local_url = $file_name.".".$ext;

			// Upload to S3
			if(Config::get('app.s3_bucket') != "")
			{
				$s3 = App::make('aws')->get('s3');
				$pic = $s3->putObject(array(
				'Bucket'     => Config::get('app.s3_bucket'),
				'Key'        => $file_name,
				'SourceFile' => public_path()."/uploads/".$local_url,

				));

				$s3->putObjectAcl(array(
				'Bucket' => Config::get('app.s3_bucket'),
				'Key'    => $file_name,
				'ACL'    => 'public-read'
				));

				$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
			}
			else{
				$s3_url = asset_url().'/uploads/'.$local_url;
			}

			$providers_type->icon = $s3_url;
		}
		

		$providers_type->name = $name;
		$providers_type->is_default = $is_default;
		$providers_type->save();

		return Redirect::to("/admin/provider-type/edit/$providers_type->id?success=1");
	}


	public function get_info_pages()
	{

		$informations = Information::paginate(10);
		return View::make('list_info_pages')
			->with('title','Information Pages')
			->with('page','information')
			->with('informations',$informations);
	}

	public function searchinfo()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type=='infoid'){
			$informations = Information::where('id',$valu)->paginate(10);
		}elseif($type=='infotitle'){
			$informations = Information::where('title','like','%'.$valu.'%')->paginate(10);
		}
		return View::make('list_info_pages')
			->with('title','Information Pages | Search Result')
			->with('page','information')
			->with('informations',$informations);
	}

	public function delete_info_page()
	{
		$id = Request::segment(4);
		Information::where('id',$id)->delete();
		return Redirect::to("/admin/informations");
	}

	public function edit_info_page()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$information = Information::find($id);
		if($information)
		{
			$id = $information->id;
			$title = $information->title;
			$description = $information->content;
			$icon = $information->icon;
		}
		else{
			$id = 0;
			$title = "";
			$description = "";
			$icon = "";
		}
		return View::make('edit_info_page')
					->with('title','Information Page')
					->with('page','information')
					->with('success',$success)
					->with('id',$id)
					->with('info_title',$title)
					->with('icon',$icon)
					->with('description',$description);
	}

	public function update_info_page()
	{
		$id = Input::get('id');
		$title = Input::get('title');
		$description = Input::get('description');
		if ($id == 0) {
			$information = new Information;
		}
		else{
			$information = Information::find($id);
		}

		if (Input::hasFile('icon'))
		{
			// Upload File
			$file_name = time();
			$file_name .= rand();
			$ext =  Input::file('icon')->getClientOriginalExtension();
			Input::file('icon')->move(public_path()."/uploads",$file_name.".".$ext);
			$local_url = $file_name.".".$ext;

			// Upload to S3
			if(Config::get('app.s3_bucket') != "")
			{
				$s3 = App::make('aws')->get('s3');
				$pic = $s3->putObject(array(
				'Bucket'     => Config::get('app.s3_bucket'),
				'Key'        => $file_name,
				'SourceFile' => public_path()."/uploads/".$local_url,

				));

				$s3->putObjectAcl(array(
				'Bucket' => Config::get('app.s3_bucket'),
				'Key'    => $file_name,
				'ACL'    => 'public-read'
				));

				$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
			}
			else{
				$s3_url = asset_url().'/uploads/'.$local_url;
			}

			$information->icon = $s3_url;
		}

		$information->title = $title;
		$information->content = $description;
		$information->save();
		return Redirect::to("/admin/information/edit/$information->id?success=1");
	}

	


	public function map_view()
	{
		$settings = Settings::where('key','map_center_latitude')->first();
		$center_latitude = $settings->value;
		$settings = Settings::where('key','map_center_longitude')->first();
		$center_longitude = $settings->value;
		return View::make('map_view')
					->with('title','Map View')
					->with('page','map-view')
					->with('center_longitude',$center_longitude)
					->with('center_latitude',$center_latitude);
	}

	public function walkers()
	{
		 Session::forget('che');
		//$query = "SELECT *,(select count(*) from request_meta where walker_id = walker.id  and status != 0 ) as total_requests,(select count(*) from request_meta where walker_id = walker.id and status=1) as accepted_requests FROM `walker`";
		//$walkers = DB::select(DB::raw($query));
		$walkers1 = DB::table('walker')
           			 	->leftJoin('request_meta', 'walker.id', '=', 'request_meta.walker_id')
            			->where('request_meta.status','!=',0)
            			->count();
        $walkers2 = DB::table('walker')
            			->leftJoin('request_meta', 'walker.id', '=', 'request_meta.walker_id')
            			->where('request_meta.status','=',1)
            			->count();

            $walkers = Walker::paginate(10);


            
		return View::make('walkers')
					->with('title','Providers')
					->with('page','walkers')
					->with('walkers',$walkers)
					->with('total_requests',$walkers1)	
            		->with('accepted_requests',$walkers2);

	}

	// Search Walkers from Admin Panel
	public function searchpv()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type == 'provid'){
			$walkers = Walker::where('id',$valu)->paginate(10);
		}elseif($type == 'pvname'){
			$walkers = Walker::where('first_name','like','%'.$valu.'%')->orWhere('last_name','like','%'.$valu.'%')->paginate(10);
		}elseif($type == 'pvemail'){
			$walkers = Walker::where('email','like','%'.$valu.'%')->paginate(10);
		}elseif($type == 'bio'){
			$walkers = Walker::where('bio','like','%'.$valu.'%')->paginate(10);
		}
		return View::make('walkers')
					->with('title','Walkers | Search Result')
					->with('page','walkers')
					->with('walkers',$walkers);
	}

	public function walkers_xml()
	{

		$walkers = Walker::where('');
		$response = "";
		$response .= '<markers>';

		// busy walkers
		$walkers = DB::table('walker')
					->where('walker.is_active',1)
					->where('walker.is_available',0)
					->where('walker.is_approved',1)
					->select('walker.id','walker.phone','walker.first_name','walker.last_name','walker.latitude','walker.longitude')
					->paginate(10);

		$walker_ids = array();

		
		foreach ($walkers as $walker) {
			$response .= '<marker ';
                $response .= 'name="' . $walker->first_name." ".$walker->last_name . '" ';
                $response .= 'client_name="' . $walker->first_name." ".$walker->last_name . '" ';
                $response .= 'contact="' . $walker->phone . '" ';
                $response .= 'amount="' . 0 . '" ';
                $response .= 'lat="' . $walker->latitude . '" ';
                $response .= 'lng="' . $walker->longitude . '" ';
                $response .= 'id="' . $walker->id . '" ';
                $response .= 'type="client_pay_done" ';
                $response .= '/>';
                array_push($walker_ids, $walker->id);
		}

		$walker_ids = array_unique($walker_ids);
		$walker_ids_temp = implode(",", $walker_ids);

		$walkers = DB::table('walker')
					->where('walker.is_active',0)
					->where('walker.is_approved',1)
					->select('walker.id','walker.phone','walker.first_name','walker.last_name','walker.latitude','walker.longitude')
					->paginate(10);


		
		foreach ($walkers as $walker) {
			$response .= '<marker ';
                $response .= 'name="' . $walker->first_name." ".$walker->last_name . '" ';
                $response .= 'client_name="' . $walker->first_name." ".$walker->last_name . '" ';
                $response .= 'contact="' . $walker->phone . '" ';
                $response .= 'amount="' . 0 . '" ';
                $response .= 'lat="' . $walker->latitude . '" ';
                $response .= 'lng="' . $walker->longitude . '" ';
                $response .= 'id="' . $walker->id . '" ';
                $response .= 'type="client_no_pay" ';
                $response .= '/>';
                array_push($walker_ids, $walker->id);
		}
	
		$walker_ids = array_unique($walker_ids);
		$walker_ids = implode(",", $walker_ids);
		if($walker_ids)
		{
			$query = "select * from walker where is_approved = 1 and id NOT IN ($walker_ids)";
		}
		else{
			$query = "select * from walker where is_approved = 1";
		}
		
		
		// free walkers
		$walkers = DB::select(DB::raw($query));
		
				foreach ($walkers as $walker) {
				$response .= '<marker ';
                $response .= 'name="' . $walker->first_name." ".$walker->last_name . '" ';
                $response .= 'client_name="' . $walker->first_name." ".$walker->last_name . '" ';
                $response .= 'contact="' . $walker->phone . '" ';
                $response .= 'amount="' . 0 . '" ';
                $response .= 'lat="' . $walker->latitude . '" ';
                $response .= 'lng="' . $walker->longitude . '" ';
                $response .= 'id="' . $walker->id . '" ';
                $response .= 'type="client" ';
                $response .= '/>';
                
		} 
		

		$response .= '</markers>'; 
		$content = View::make('walkers_xml')->with('response', $response);
		return Response::make($content, '200')->header('Content-Type', 'text/xml');
	}

	public function owners()
	{
		$owners = Owner::paginate(10);

		return View::make('owners')
					->with('title','Owners')
					->with('page','owners')
					->with('owners',$owners);
	}
	public function searchur()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type == 'userid'){
			$owners = Owner::where('id',$valu)->paginate(10);
		}elseif ($type == 'username') {
			$owners = Owner::where('first_name','like','%'.$valu.'%')->orWhere('last_name','like','%'.$valu.'%')->paginate(10);
		}elseif ($type == 'useremail') {
			$owners = Owner::where('email','like','%'.$valu.'%')->paginate(10);
		}elseif ($type == 'useraddress') {
			$owners = Owner::where('address','like','%'.$valu.'%')->orWhere('state','like','%'.$valu.'%')->orWhere('country','like','%'.$valu.'%')->paginate(10);
		}
		return View::make('owners')
					->with('title','Owners | Search Result')
					->with('page','owners')
					->with('owners',$owners);
	}


	public function walks()
	{
		$walks = DB::table('request')
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->select('owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled')
            ->orderBy('request.created_at')
            ->paginate(10);


		return View::make('walks')
				->with('title','Requests')
				->with('page','walks')
				->with('walks',$walks);
	}


	// Search Walkers from Admin Panel
	public function searchreq()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type == 'reqid'){
			$walks = DB::table('request')
					->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
					->leftJoin('walker','request.current_walker','=','walker.id')
					->groupBy('request.id')
					->select('owner.first_name as first_name','owner.last_name as last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled')
	            ->where('request.id', $valu)
	            ->orderBy('request.created_at')
	            ->paginate(10);
		}elseif($type == 'owner'){
			$walks = DB::table('request')
					->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
					->leftJoin('walker','request.current_walker','=','walker.id')
					->groupBy('request.id')
					->select('owner.first_name as first_name','owner.last_name as last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled')
	            ->where('owner.first_name','like','%'.$valu.'%')
	            ->orWhere('owner.last_name','like','%'.$valu.'%')
	            ->orderBy('request.created_at')
	            ->paginate(10);
		}elseif($type == 'walker'){
			$walks = DB::table('request')
					->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
					->leftJoin('walker','request.current_walker','=','walker.id')
					->groupBy('request.id')
					->select('owner.first_name as first_name','owner.last_name as last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled')
	            ->where('owner.first_name','like','%'.$valu.'%')
	            ->orWhere('owner.last_name','like','%'.$valu.'%')
	        ->orderBy('request.created_at')
	        ->paginate(10);
		}
		return View::make('walks')
			->with('title','Walks | Search Result')
			->with('page','walks')
			->with('walks',$walks);
	}


	public function reviews()
	{
		$reviews = DB::table('review_walker')
            ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
            ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
            ->select('review_walker.id as review_id','review_walker.rating','review_walker.comment','owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name','owner.id as owner_id','walker.id as walker_id','review_walker.created_at')

            ->paginate(10);

		return View::make('reviews')
					->with('title','Reviews')
					->with('page','reviews')
					->with('reviews',$reviews);
	}


	public function searchrev()
	{

		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type=='owner'){
			$reviews = DB::table('review_walker')
            ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
            ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
            ->select('review_walker.id as review_id','review_walker.rating','review_walker.comment','owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name','owner.id as owner_id','walker.id as walker_id','review_walker.created_at')
            ->where('owner.first_name','like','%'.$valu.'%')->orWhere('owner.last_name','like','%'.$valu.'%')
            ->paginate(10);
		}elseif($type=='walker'){
			$reviews = DB::table('review_walker')
            ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
            ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
            ->select('review_walker.id as review_id','review_walker.rating','review_walker.comment','owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name','owner.id as owner_id','walker.id as walker_id','review_walker.created_at')
            ->where('walker.first_name','like','%'.$valu.'%')->orWhere('walker.last_name','like','%'.$valu.'%')
            ->paginate(10);
		}
		return View::make('reviews')
					->with('title','Reviews | Search Result')
					->with('page','reviews')
					->with('reviews',$reviews);
	}


	public function search()
	{
		$type = Input::get('type');
		$q = Input::get('q');
		if($type == 'user')
		{
			$owners = Owner::where('first_name','like','%'.$q.'%')
							->orWhere('last_name','like','%'.$q.'%')
							->paginate(10);

			return View::make('owners')
					->with('title','Users')
					->with('page','owners')
					->with('owners',$owners);
		}
		else{

			$walkers = Walker::where('first_name','like','%'.$q.'%')
							->orWhere('last_name','like','%'.$q.'%')
						->paginate(10);

			return View::make('walkers')
					->with('title','Providers')
					->with('page','walkers')
					->with('walkers',$walkers);

		}
	}

	public function logout()
	{
		Auth::logout();
		return Redirect::to('/admin/login');
	}

	public function verify()
	{
		$username = Input::get('username');
		$password = Input::get('password');
		if(!User::count())
		{
			$user = new User;
			$user->username = Input::get('username');
			$user->password = $user->password = Hash::make(Input::get('password'));
			$user->save();
			return Redirect::to('/admin/login');
		}
		else{
			if (Auth::attempt(array('username' => $username, 'password' => $password)))
			{
				return Redirect::to('/admin/report');
			}
			else{
				return Redirect::to('/admin/login?error=1');
			}
		}
		
	}

	public function login()
	{
		$error = Input::get('error');
		if(User::count())
		{

			return View::make('login')->with('title','Login')->with('button','Login')->with('error',$error);
		}
		else
		{
			return View::make('login')->with('title','Create Admin')->with('button','Create')->with('error',$error);
		}
	}

	public function edit_walker()
	{
		$id = Request::segment(4);
		$type = ProviderType::all();
		$provserv = ProviderServices::where('provider_id',$id)->get();
		$success = Input::get('success');
		$walker = Walker::find($id);
		if ($walker) {
			return View::make('edit_walker')
					->with('title','Edit Provider')
					->with('page','walkers')
					->with('success',$success)
					->with('type',$type)
					->with('ps',$provserv)
					->with('walker',$walker);
		}
		else{
			return View::make('notfound')->with('title','Error Page Not Found')->with('page','Error Page Not Found');
		}
	}

	public function add_walker()
	{
		return View::make('add_walker')
					->with('title','Add Provider')
					->with('page','walkers');
	}

	public function update_walker()
	{
		if(Input::get('id') != 0)
		{
			$walker = Walker::find(Input::get('id'));
		}
		else{
			$walker = new Walker;
		}
		if(Input::has('service')!=NULL){
			foreach (Input::get('service') as $key) {
				$serv = ProviderType::where('id',$key)->first();
				$pserv[] = $serv->name;
			}
		}
		$walker->first_name = Input::get('first_name');
		$walker->last_name = Input::get('last_name');
		$walker->email = Input::get('email');
		$walker->phone = Input::get('phone');
		$walker->bio = Input::get('bio');
		$walker->address = Input::get('address');
		$walker->state = Input::get('state');
		$walker->country = Input::get('country');
		$walker->zipcode = Input::get('zipcode');

		if (Input::hasFile('pic'))
		{
			// Upload File
			$file_name = time();
			$file_name .= rand();
			$ext =  Input::file('pic')->getClientOriginalExtension();
			Input::file('pic')->move(public_path()."/uploads",$file_name.".".$ext);
			$local_url = $file_name.".".$ext;

			// Upload to S3
			if(Config::get('app.s3_bucket') != "")
			{
				$s3 = App::make('aws')->get('s3');
				$pic = $s3->putObject(array(
				'Bucket'     => Config::get('app.s3_bucket'),
				'Key'        => $file_name,
				'SourceFile' => public_path()."/uploads/".$local_url,

				));

				$s3->putObjectAcl(array(
				'Bucket' => Config::get('app.s3_bucket'),
				'Key'    => $file_name,
				'ACL'    => 'public-read'
				));

				$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
			}
			else{
				$s3_url = asset_url().'/uploads/'.$local_url;
			}

			$walker->picture = $s3_url;
		}
		$walker->save();


		if(Input::has('service')!=NULL){
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
		}

		return Redirect::to("/admin/providers");
	}

	public function approve_walker()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$walker = Walker::find($id);
		$walker->is_approved = 1;
		$walker->save();
		return Redirect::to("/admin/providers");
	}

	public function decline_walker()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$walker = Walker::find($id);
		$walker->is_approved = 0;
		$walker->save();
		return Redirect::to("/admin/providers");
	}

	public function walker_history()
	{
		$walker_id = Request::segment(4);
		$walks = DB::table('request')
			->where('request.confirmed_walker',$walker_id)
			->where('request.is_completed',1)
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->select('owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker','request.status','request.time','request.distance','request.total','request.is_cancelled')
            ->orderBy('request.created_at')
            ->paginate(10);

		return View::make('walks')
				->with('title','Walk History')
				->with('page','walkers')
				->with('walks',$walks);
	}

	public function walker_upcoming_walks()
	{
		$walker_id = Request::segment(4);
		$walks = DB::table('request')
			->where('request.walker_id',$walker_id)
			->where('request.is_completed',0)
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->select('owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker','request.status','request.time','request.distance','request.total')
            ->orderBy('request.created_at')
            ->paginate(10);

		return View::make('walks')
				->with('title','Upcoming Walks')
				->with('page','walkers')
				->with('walks',$walks);
	}



	public function edit_owner()
	{
		$id = Request::segment(4);
		$success = Input::get('success');
		$owner = Owner::find($id);
		if ($owner) {
			return View::make('edit_owner')
					->with('title','Edit User')
					->with('page','owners')
					->with('success',$success)
					->with('owner',$owner);
		}
		else{
			return View::make('notfound')
						->with('title','Error Page Not Found')
						->with('page','Error Page Not Found');
		}
	}

	public function update_owner()
	{
		$owner = Owner::find(Input::get('id'));
		$owner->first_name = Input::get('first_name');
		$owner->last_name = Input::get('last_name');
		$owner->email = Input::get('email');
		$owner->phone = Input::get('phone');
		$owner->address = Input::get('address');
		$owner->state = Input::get('state');
		$owner->zipcode = Input::get('zipcode');
		$owner->save();
		return Redirect::to("/admin/user/edit/$owner->id?success=1");
	}

	public function owner_history()
	{
		$owner_id = Request::segment(4);
		$owner = Owner::find($owner_id);
		$walks = DB::table('request')
			->where('request.owner_id',$owner->id)
			->where('request.is_completed',1)
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->select('owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker',
            	'request.status','request.time','request.distance','request.total','request.is_cancelled')
            ->orderBy('request.created_at')
            ->paginate(10);

		return View::make('walks')
				->with('title','Walk History')
				->with('page','owners')
				->with('walks',$walks);
	}

	public function owner_upcoming_walks()
	{
		$owner_id = Request::segment(4);
		$owner = Owner::find($owner_id);
		$walks = DB::table('request')
			->where('request.owner_id',$owner->id)
			->where('request.is_completed',0)
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->select('owner.first_name as owner_first_name','owner.last_name as owner_last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker','request.status','request.time','request.distance','request.total')
            ->orderBy('request.created_at')
            ->paginate(10);

		return View::make('walks')
				->with('title','Upcoming Walks')
				->with('page','owners')
				->with('walks',$walks);
	}

	public function delete_review()
	{
		$id = Request::segment(4);
		$walker = WalkerReview::where('id',$id)->delete();
		return Redirect::to("/admin/reviews");
	}


	public function approve_walk()
	{
		$id = Request::segment(4);
		$walk = Walk::find($id);
		$walk->is_confirmed = 1;
		$walk->save();
		return Redirect::to("/admin/walks");
	}

	public function decline_walk()
	{
		$id = Request::segment(4);
		$walk = Walk::find($id);
		$walk->is_confirmed = 0;
		$walk->save();
		return Redirect::to("/admin/walks");
	}

	public function view_map()
	{
		$id = Request::segment(4);
		$request = Requests::find($id);
		$walker = Walker::find($request->confirmed_walker);
		$owner = Owner::find($request->owner_id);
		if ($request->is_paid) {
			$status = "Payment Done";
		}
		elseif ($request->is_completed) {
			$status = "Request Completed";
		}
		elseif ($request->is_started) {
			$status = "Request Started";
		}
		elseif ($request->is_walker_started) {
			$status = "Provider Started";
		}
		elseif ($request->confirmed_walker) {
			$status = "Provider Yet to start";
		}
		else{
			$status = "Provider Not Confirmed";
		}
		

		if ($request->is_completed) {
			$walk_location_start = WalkLocation::where('request_id',$id)->orderBy('created_at')->first();
			$walk_location_end = WalkLocation::where('request_id',$id)->orderBy('created_at','desc')->first();	
			$walker_latitude = $walk_location_start->latitude;
			$walker_longitude = $walk_location_start->longitude;
			$owner_latitude = $walk_location_end->latitude;
			$owner_longitude = $walk_location_end->longitude;
		}
		else{
			if($request->confirmed_walker){
				$walker_latitude = $walker->latitude;
				$walker_longitude = $walker->longitude;
			}
			else{
				$walker_latitude = 0;
				$walker_longitude = 0;
			}
			$owner_latitude = $owner->latitude;
			$owner_longitude = $owner->longitude;
		}

		$request_meta = DB::table('request_meta')
						->where('request_id',$id)
						->leftJoin('walker','request_meta.walker_id','=','walker.id')
						->paginate(10);

		if ($request->confirmed_walker) {
		return View::make('walk_map')
					->with('title','Maps')
					->with('page','walks')
					->with('walk_id',$id)
					->with('is_started',$request->is_started)
					->with('owner_name',$owner->first_name." ",$owner->last_name)
					->with('walker_name',$walker->first_name." ",$walker->last_name)
					->with('walker_latitude',$walker_latitude)
					->with('walker_longitude',$walker_longitude)
					->with('owner_latitude',$owner_latitude)
					->with('owner_longitude',$owner_longitude)
					->with('walker_phone',$walker->phone)
					->with('owner_phone',$owner->phone)
					->with('status',$status)
					->with('request_meta',$request_meta);
		}
		else{
		return View::make('walk_map')
					->with('title','Maps')
					->with('page','walks')
					->with('walk_id',$id)
					->with('is_started',$request->is_started)
					->with('owner_name',$owner->first_name." ",$owner->last_name)
					->with('walker_name',"")
					->with('walker_latitude',$walker_latitude)
					->with('walker_longitude',$walker_longitude)
					->with('owner_latitude',$owner_latitude)
					->with('owner_longitude',$owner_longitude)
					->with('walker_phone',"")
					->with('owner_phone',$owner->phone)
					->with('request_meta',$request_meta)
					->with('status',$status);
		}

	
	}  

	public function change_walker()
	{
		$id = Request::segment(4);
		return View::make('reassign_walker')
					->with('title','Map View')
					->with('page','walks')
					->with('walk_id',$id);
		
	}

	public function alternative_walkers_xml()
	{
		$id = Request::segment(4);
		$walk = Walk::find($id);
		$schedule = Schedules::find($walk->schedule_id);
		$dog = Dog::find($walk->dog_id);
		$owner = Owner::find($dog->owner_id);
		$current_walker = Walker::find($walk->walker_id);
		$latitude = $owner->latitude;
		$longitude = $owner->longitude;
		$distance = 5;


		// Get Latitude
		$schedule_meta = ScheduleMeta::where('schedule_id','=',$schedule->id)
		->orderBy('started_on', 'DESC')
		->paginate(10);

		$flag = 0;
		$date = "0000-00-00";
		$days = array();
		foreach ($schedule_meta as $meta) {
			if ($flag == 0) {
				$date = $meta->started_on;
				$flag++;
			}
			array_push($days,$meta->day);
		}

		$start_time = date('H:i:s', strtotime($schedule->start_time)-(60*60));
		$end_time =  date('H:i:s', strtotime($schedule->end_time) + (60*60));
		$days_str = implode(',', $days);

		$query = "SELECT walker.id,walker.bio,walker.first_name,walker.last_name,walker.phone,walker.latitude,walker.longitude from walker where id NOT IN ( SELECT distinct schedules.walker_id FROM `schedule_meta` left join schedules on schedule_meta.schedule_id = schedules.id where schedules.is_confirmed	 != 0 and schedule_meta.day IN ($days_str) and schedule_meta.ends_on >= '$date' and schedule_meta.started_on <= '$date' and ((schedules.start_time > '$start_time' and schedules.start_time < '$end_time') OR ( schedules.end_time > '$start_time' and schedules.end_time < '$end_time' )) ) and (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance ";
		
		$walkers = DB::select(DB::raw($query));
		$response = "";
		$response .= '<markers>';
		
		foreach ($walkers as $walker) {
			$response .= '<marker ';
            $response .= 'name="' . $walker->first_name." ".$walker->last_name . '" ';
            $response .= 'client_name="' . $walker->first_name." ".$walker->last_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $walker->id . '" ';
            $response .= 'type="client" ';
            $response .= '/>';
		}

		// Add Current walker
		if($current_walker)
		{
			$response .= '<marker ';
            $response .= 'name="' . $current_walker->first_name." ".$current_walker->last_name . '" ';
            $response .= 'client_name="' . $current_walker->first_name." ".$current_walker->last_name . '" ';
            $response .= 'contact="' . $current_walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $current_walker->latitude . '" ';
            $response .= 'lng="' . $current_walker->longitude . '" ';
            $response .= 'id="' . $current_walker->id . '" ';
            $response .= 'type="driver" ';
            $response .= '/>';
		}

		// Add Owner
		$response .= '<marker ';
		$response .= 'name="' . $owner->first_name." ".$owner->last_name . '" ';
		$response .= 'client_name="' . $owner->first_name." ".$owner->last_name . '" ';
		$response .= 'contact="' . $owner->phone . '" ';
		$response .= 'amount="' . 0 . '" ';
		$response .= 'lat="' . $owner->latitude . '" ';
		$response .= 'lng="' . $owner->longitude . '" ';
		$response .= 'id="' . $owner->id . '" ';
		$response .= 'type="client_pay_done" ';
		$response .= '/>';

		// Add Busy Walkers
		
		$walkers = DB::table('request')
					->where('walk.is_started',1)
					->where('walk.is_completed',0)
					->join('walker', 'walk.walker_id', '=', 'walker.id')
					->select('walker.id','walker.phone','walker.first_name','walker.last_name','walker.latitude','walker.longitude')
					->distinct()
					->paginate(10);

		
		foreach ($walkers as $walker) {
			$response .= '<marker ';
            $response .= 'name="' . $walker->first_name." ".$walker->last_name . '" ';
            $response .= 'client_name="' . $walker->first_name." ".$walker->last_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $owner->id . '" ';
            $response .= 'type="client_no_pay" ';
            $response .= '/>';
		}
		

		$response .= '</markers>'; 

		$content = View::make('walkers_xml')->with('response', $response);
		return Response::make($content, '200')->header('Content-Type', 'text/xml');
	}

	public function save_changed_walker(){
		$walk_id = Input::get('walk_id');
		$type = Input::get('type');
		$walker_id = Input::get('walker_id');
		$walk = Walk::find($walk_id);
		if($type == 1)
		{
			$walk->walker_id = $walker_id;
			$walk->save();
		}
		else{
			Walk::where('schedule_id',$walk->schedule_id)->where('is_started',0)->update(array('walker_id' => $walker_id));
			Schedules::where('id',$walk->schedule_id)->update(array('walker_id' => $walker_id));
		}
		return Redirect::to('/admin/walk/change_walker/'.$walk_id);
	}

	public function pay_walker(){
		$walk_id = Input::get('walk_id');
		$amount = Input::get('amount');
		$walk = Walk::find($walk_id);
		$walk->is_paid = 1;
		$walk->amount = $amount;
		$walk->save();
		
		return Redirect::to('/admin/walk/map/'.$walk_id);
	}

	public function get_settings()
	{
		$success = Input::get('success');
		$settings = Settings::all();
		$theme = Theme::all();
		return View::make('settings')
					->with('title','Settings')
					->with('page','settings')
					->with('settings',$settings)
					->with('success',$success)
					->with('theme',$theme);
	}

	public function save_settings()
	{
		$settings = Settings::all();
		foreach ($settings as $setting) {
			if(Input::get($setting->id)!=NULL){
				$temp_setting = Settings::find($setting->id);
				$temp_setting->value = Input::get($setting->id);
				$temp_setting->save();
			}
		}
		return Redirect::to('/admin/settings?success=1');
	}


	//Sort Owners
	public function sortur()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type == 'userid'){
			$typename = "User ID";
			$users = Owner::orderBy('id', $valu)->paginate(10);
		}elseif ($type == 'username') {
			$typename = "User Name";
			$users = Owner::orderBy('first_name', $valu)->paginate(10);
		}elseif ($type == 'useremail') {
			$typename = "User Email";
			$users = Owner::orderBy('email', $valu)->paginate(10);
		}
		return View::make('owners')
					->with('title','Owners | Sorted by '.$typename.' in '.$valu)
					->with('page','owners')
					->with('owners',$users);
	}

public function sortpv()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type == 'provid'){
			$typename = "Providers ID";
			$providers = Walker::orderBy('id',$valu)->paginate(10);
		}elseif($type == 'pvname'){
			$typename = "Providers Name";
			$providers = Walker::orderBy('first_name',$valu)->paginate(10);
		}elseif($type == 'pvemail'){
			$typename = "Providers Email";
			$providers = Walker::orderBy('email',$valu)->paginate(10);
		}elseif($type == 'pvaddress'){
			$typename = "Providers Address";
			$providers = Walker::orderBy('address',$valu)->paginate(10);
		}
		return View::make('walkers')
				->with('title','Providers | Sorted by '.$typename.' in '.$valu)
				->with('page','walkers')
				->with('walkers',$providers);
	}
	public function sortpvtype()
	{
		$valu = $_GET['valu'];
		$type = $_GET['type'];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if($type == 'provid'){
			$typename = "Providers Type ID";
			$providers = ProviderType::orderBy('id',$valu)->paginate(10);
		}elseif($type == 'pvname'){
			$typename = "Providers Name";
			$providers = ProviderType::orderBy('name',$valu)->paginate(10);
		}
		return View::make('list_provider_types')
				->with('title','Provider Types | Sorted by '.$typename.' in '.$valu)
				->with('page','list_provider_types')
				->with('types',$providers);
	}


	public function sortreq()
	{
		$valu = $_GET["valu"];
		$type = $_GET["type"];
		Session::put('valu',$valu);
		Session::put('type',$type);
		if ($type=='reqid') {
			$typename = "Request ID";
			$requests = DB::table('request')
					->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
					->leftJoin('walker','request.current_walker','=','walker.id')
					->groupBy('request.id')
					->select('owner.first_name as first_name','owner.last_name as last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled')
					->orderBy('request.id', $valu)
					->paginate(10);
		}elseif ($type=='owner') {
			$typename = "Owner Name";
			$requests = DB::table('request')
					->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
					->leftJoin('walker','request.current_walker','=','walker.id')
					->groupBy('request.id')
					->select('owner.first_name as first_name','owner.last_name as last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled')
					->orderBy('owner.first_name', $valu)
					->paginate(10);
		}elseif ($type=='walker') {
			$typename = "Walker Name";
			$requests = DB::table('request')
					->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
					->leftJoin('owner','request.owner_id','=','owner.id')
					->groupBy('request.id')
					->select('owner.first_name as first_name','owner.last_name as last_name',
            	'walker.first_name as walker_first_name','walker.last_name as walker_last_name',
            	'owner.id as owner_id','walker.id as walker_id','request.id as id','request.created_at as date',
            	'request.is_started','request.is_walker_arrived',
            	'request.is_completed','request.is_paid','request.is_walker_started','request.confirmed_walker'
            	,'request.status','request.time','request.distance','request.total','request.is_cancelled')
					->orderBy('walker.first_name', $valu)
					->paginate(10);
		}
		return View::make('walks')
			->with('title','Requests | Sorted by '.$typename.' in '.$valu)
			->with('page','walks')
			->with('walks',$requests);
	}

// Provider Availability

	public function allow_availability()
	{
		Settings::where('key','allowcal')->update(array('value' => 1));
		return Redirect::to("/admin/providers");
	}

	public function disable_availability()
	{
		Settings::where('key','allowcal')->update(array('value' => 0));
		return Redirect::to("/admin/providers");
	}

public function availability_provider()
	{
		$id = Request::segment(4);
		$provider = Walker::where('id',$id)->first();
		if ($provider) {
			$success = Input::get('success');
			$pavail = ProviderAvail::where('provider_id',$id)->paginate(10);
	    	$prvi = array();
	    	foreach($pavail as $pv){
	    		$prv = array();
	    		$prv['title'] = 'available';
	    		$prv['start'] = date('Y-m-d',strtotime($pv->start))."T".date('H:i:s',strtotime($pv->start));
	    		$prv['end'] = date('Y-m-d',strtotime($pv->end))."T".date('H:i:s',strtotime($pv->end));;
	    		array_push($prvi, $prv);
	    	}
	    	$pvjson = json_encode($prvi);
	    	Log::info('Provider availability json = '.print_r($pvjson,true));
			return View::make('availability_provider')
				->with('title','Provider Availability')
				->with('page','availability_provider')
				->with('success',$success)
				->with('pvjson',$pvjson)
				->with('provider',$provider);
		}else{
			return View::make('admin.notfound')->with('title','Error Page Not Found')->with('page','Error Page Not Found');
		}
	}

	public function provideravailabilitySubmit()
	{
		$id = Request::segment(4);
		$proavis = $_POST['proavis'];
		$proavie = $_POST['proavie'];
		$length = $_POST['length'];
		Log::info('Start end time Array Length = '.print_r($length,true));
		DB::delete("delete from provider_availability where provider_id = '".$id."';");
		for($l=0;$l<$length;$l++){
			$pv = new ProviderAvail;
			$pv->provider_id = $id;
			$pv->start = $proavis[$l];
			$pv->end = $proavie[$l];
			$pv->save();
		}
		Log::info('providers availability start = '.print_r($proavis,true));
		Log::info('providers availability end = '.print_r($proavie,true));
		return Response::json(array('success' => true));
	}

	//Providers Who currently walking
	public function current()
	{
		        Session::put('che','current');

				$walks = DB::table('walk')
	            ->leftJoin('walker', 'walk.walker_id', '=', 'walker.id')
	            ->leftJoin('request', 'walk.walker_id', '=', 'request.confirmed_walker')
	            ->select('walker.id as id','walker.first_name as first_name','walker.last_name as last_name','walker.phone as phone','walker.email as email','walker.picture as picture','walker.bio as bio', 'request.total as total_requests', 'walker.is_approved as is_approved')
	            ->where('walk.is_started', 1)
	            ->where('walk.is_completed', 0)
	            ->paginate(10);
				return View::make('walkers')
			->with('title','Providers | Currently Providing')
			->with('page','walkers')
			->with('walkers',$walks);
	}
public function theme()
	{
			$th = Theme::all()->count();
	
		if($th == 1)
		{
			$theme = Theme::first();
		}
		else{
			$theme = new Theme;
		}
		
		$theme->theme_color = '#'.Input::get('color1');
		$theme->secondary_color = '#'.Input::get('color3');
		$theme->primary_color = '#'.Input::get('color2');
		$theme->hover_color = '#'.Input::get('color4');
		$theme->active_color = '#'.Input::get('color5');

		$css_msg = ".btn-default {
  color: #ffffff;
  background-color: $theme->theme_color;
}
.nav-pills > li {
  float: left;
}
.btn-info{
    color: #000;
    background: #fff;
    border-radius: 0px;
    border:1px solid $theme->theme_color;
}
.nav-admin .dropdown :hover, .nav-admin .dropdown :hover {
    background: $theme->hover_color;
    color: #000;
}
.nav-pills > li > a {
  border-radius: 4px;
}
.nav-pills > li + li {
  margin-left: 2px;
}
.nav-pills > li.active > a,
.nav-pills > li.active > a:hover,
.nav-pills > li.active > a:focus {
  color: #ffffff;
  background-color: $theme->active_color;
}
.logo_img_login{
border-radius: 30px;border: 4px solid $theme->theme_color;
}
.btn-success {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-success:hover,
.btn-success:focus,
.btn-success:active,
.btn-success.active,
.open .dropdown-toggle.btn-success {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;

}


.btn-success.disabled,
.btn-success[disabled],
fieldset[disabled] .btn-success,
.btn-success.disabled:hover,
.btn-success[disabled]:hover,
fieldset[disabled] .btn-success:hover,
.btn-success.disabled:focus,
.btn-success[disabled]:focus,
fieldset[disabled] .btn-success:focus,
.btn-success.disabled:active,
.btn-success[disabled]:active,
fieldset[disabled] .btn-success:active,
.btn-success.disabled.active,
.btn-success[disabled].active,
fieldset[disabled] .btn-success.active {

  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-success .badge {
  color: $theme->theme_color;
  background-color: #ffffff;
}
.btn-info {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-info:hover,
.btn-info:focus,
.btn-info:active,
.btn-info.active,
.open .dropdown-toggle.btn-info {
  color: #000;
  background-color: #FFFF;
  border-color: $theme->theme_color;
}
.btn-info:active,
.btn-info.active,
.open .dropdown-toggle.btn-info {
  background-image: none;
}
.btn-info.disabled,
.btn-info[disabled],
fieldset[disabled] .btn-info,
.btn-info.disabled:hover,
.btn-info[disabled]:hover,
fieldset[disabled] .btn-info:hover,
.btn-info.disabled:focus,
.btn-info[disabled]:focus,
fieldset[disabled] .btn-info:focus,
.btn-info.disabled:active,
.btn-info[disabled]:active,
fieldset[disabled] .btn-info:active,
.btn-info.disabled.active,
.btn-info[disabled].active,
fieldset[disabled] .btn-info.active {
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-info .badge {
  color: $theme->theme_color;
  background-color: #029acf;
  border-color: #029acf;
}
.btn-success,
.btn-success:hover {
  background-image: -webkit-linear-gradient($theme->theme_color $theme->theme_color 6%, $theme->theme_color);
  background-image: linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-repeat: no-repeat;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$theme->theme_color', endColorstr='$theme->theme_color', GradientType=0);
  filter: none;
  border: 1px solid $theme->theme_color;
}
.btn-info,
.btn-info:hover {
  background-image: -webkit-linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-image: linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-repeat: no-repeat;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$theme->theme_color', endColorstr='$theme->theme_color', GradientType=0);
  filter: none;
  border: 1px solid $theme->theme_color;
}
.logo h3{
    margin: 0px;
    color: $theme->theme_color;
}

.second-nav{
    background: $theme->theme_color;
}
.login_back{background-color: $theme->theme_color;}
.no_radious:hover{background-image: -webkit-linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);background-image: linear-gradient(#5d4dd1, #5d4dd1 6%, #5d4dd1);background-repeat: no-repeat;filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#5d4dd1', endColorstr='#5d4dd1', GradientType=0);filter: none;border: 1px solid #5d4dd1;}

.nav-pills li:nth-child(1) a{
    background: $theme->primary_color;
}

.nav-pills li:nth-child(2) a{
    background: $theme->secondary_color;
}

.nav-pills li:nth-child(3) a{
    background: $theme->primary_color;
}

.nav-pills li:nth-child(4) a{
    background: $theme->secondary_color;
}

.nav-pills li:nth-child(5) a{
    background: $theme->primary_color;
}

.nav-pills li:nth-child(6) a{
    background: $theme->secondary_color;
}

.nav-pills li:nth-child(7) a{
    background: $theme->primary_color;
}

.nav-pills li:nth-child(8) a{
    background: $theme->secondary_color;
}

.nav-pills li:nth-child(9) a{
    background: $theme->primary_color;
}

.nav-pills li:nth-child(10) a{
    background: $theme->secondary_color;
}

.nav-pills li a:hover{
    background: $theme->hover_color;
}
.btn-green{

    background: $theme->theme_color;
    color: #fff;
}
.btn-green:hover{
    background: $theme->hover_color;
    color: #fff;
}
"; 
		$t = file_put_contents(public_path().'/stylesheet/theme_cus.css', $css_msg);

		if (Input::hasFile('logo'))
		{
			// Upload File
			$file_name = time();
			$file_name .= rand();
			$ext =  Input::file('logo')->getClientOriginalExtension();
			Input::file('logo')->move(public_path()."/uploads",$file_name.".".$ext);
			$local_url = $file_name.".".$ext;

			// Upload to S3
			if(Config::get('app.s3_bucket') != "")
			{
				$s3 = App::make('aws')->get('s3');
				$pic = $s3->putObject(array(
				'Bucket'     => Config::get('app.s3_bucket'),
				'Key'        => $file_name,
				'SourceFile' => public_path()."/uploads/".$local_url,

				));

				$s3->putObjectAcl(array(
				'Bucket' => Config::get('app.s3_bucket'),
				'Key'    => $file_name,
				'ACL'    => 'public-read'
				));

				$s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
			}
			else{
				$s3_url = asset_url().'/uploads/'.$local_url;
			}
			$theme->logo = $local_url;
		}
		else
		{
			$theme->logo = 'logo.png';
		}
				if (Input::hasFile('icon'))
		{
			// Upload File
			$file_name1 = time();
			$file_name1 .= rand();
			$file_name1 .= 'icon';
			$ext1 =  Input::file('icon')->getClientOriginalExtension();
			Input::file('icon')->move(public_path()."/uploads",$file_name1.".".$ext1);
			$local_url1 = $file_name1.".".$ext1;

			// Upload to S3
			if(Config::get('app.s3_bucket') != "")
			{
				$s3 = App::make('aws')->get('s3');
				$pic = $s3->putObject(array(
				'Bucket'     => Config::get('app.s3_bucket'),
				'Key'        => $file_name1,
				'SourceFile' => public_path()."/uploads/".$local_url1,

				));

				$s3->putObjectAcl(array(
				'Bucket' => Config::get('app.s3_bucket'),
				'Key'    => $file_name1,
				'ACL'    => 'public-read'
				));

				$s3_url1 = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name1);
			}
			else{
				$s3_url1 = asset_url().'/uploads/'.$local_url1;
			}
			$theme->favicon = $local_url1;
		}
		else
		{
			$theme->favicon = 'favicon.png';
		}
		$theme->save();
		return Redirect::to("/admin/settings");
	}
}


?>
