<?php


class DogController extends BaseController {

    public function isAdmin($token) {
        return false;
    }

    public function getOwnerData($owner_id, $token, $is_admin) {

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

    public function create() {
        if (Request::isMethod('post')) {
            $name = Input::get('name');
            $age = Input::get('age');
            $breed = Input::get('type');
            $likes = Input::get('notes');
            $token = Input::get('token');
            $owner_id = Input::get('id');
            $picture = Input::file('picture');

            $validator = Validator::make(
                            array(
                        'name' => $name,
                        'age' => $age,
                        'breed' => $breed,
                        'token' => $token,
                        'owner_id' => $owner_id,
                        'picture' => $picture,
                            ), array(
                        'name' => 'required',
                        'age' => 'required|integer',
                        'breed' => 'required',
                        'token' => 'required',
                        'owner_id' => 'required|integer',
                        'picture' => 'required|mimes:jpeg,bmp,png'
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        // Do necessary operations
                        // check if there's already a dog
                        $dog = Dog::where('owner_id', $owner_id)->first();
                        if ($dog === null) {
                            $dog = new Dog;
                        }

                        $dog->name = $name;
                        $dog->age = $age;
                        $dog->breed = $breed;
                        $dog->likes = $likes;
                        $dog->owner_id = $owner_data->id;


                        // Upload File
                        $file_name = time();
                        $file_name .= rand();
                        $ext = Input::file('picture')->getClientOriginalExtension();
                        Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
                        $local_url = $file_name . "." . $ext;

                        // Upload to S3
                        if (Config::get('app.s3_bucket') != "") {
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
                            $s3_url = asset_url() . '/uploads/' . $local_url;
                        }
                        $dog->image_url = $s3_url;

                        $dog->save();

                        $owner = Owner::find($owner_data->id);
                        $owner->dog_id = $dog->id;
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
                            ), array(
                        'token' => 'required',
                        'owner_id' => 'required|integer'
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {

                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        $dog = Dog::find($owner_data->dog_id);
                        if ($dog) {
                            $response_array = array(
                                'success' => true,
                                'thing_id' => $dog->id,
                                'age' => $dog->age,
                                'type' => $dog->breed,
                                'notes' => $dog->likes,
                                'image_url' => $dog->image_url,
                            );
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 'No Dogs Found', 'error_code' => 445);
                            $response_code = 200;
                        }
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

    // Setting Owner Location

    public function update_thing() {
        if (Request::isMethod('post')) {
            $name = Input::get('name');
            $age = Input::get('age');
            $breed = Input::get('type');
            $likes = Input::get('notes');
            $token = Input::get('token');
            $owner_id = Input::get('id');
            $picture = Input::file('picture');

            $validator = Validator::make(
                            array(
                        'token' => $token,
                        'owner_id' => $owner_id,
                        'age' => $age,
                        'picture' => $picture,
                            ), array(
                        'token' => 'required',
                        'owner_id' => 'required|integer',
                        'age' => 'integer',
                        'picture' => 'mimes:jpeg,bmp,png',
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {

                        $dog_data = Dog::where('owner_id', $owner_id)->first();
                        if ($dog_data) {
                            $dog = Dog::find($dog_data->id);
                            if ($name) {
                                $dog->name = $name;
                            }
                            if ($age) {
                                $dog->age = $age;
                            }
                            if ($breed) {
                                $dog->breed = $breed;
                            }
                            if ($likes) {
                                $dog->likes = $likes;
                            }

                            if (Input::hasFile('picture')) {
                                // upload image
                                $file_name = time();
                                $file_name .= rand();
                                $file_name = sha1($file_name);

                                $ext = Input::file('picture')->getClientOriginalExtension();
                                Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
                                $local_url = $file_name . "." . $ext;

                                // Upload to S3
                                if (Config::get('app.s3_bucket') != "") {
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
                                    $s3_url = asset_url() . '/uploads/' . $local_url;
                                }

                                $dog->image_url = $s3_url;
                            }

                            $dog->save();
                            $response_array = array('success' => true);
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 'No Dog Found', 'error_code' => 405);
                            $response_code = 200;
                        }
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

    // Rate Walker

    public function set_walker_rating() {
        if (Request::isMethod('post')) {
            $comment = Input::get('comment');
            $request_id = Input::get('request_id');
            $rating = Input::get('rating');
            $token = Input::get('token');
            $owner_id = Input::get('id');

            $validator = Validator::make(
                            array(
                        'request_id' => $request_id,
                        'rating' => $rating,
                        'token' => $token,
                        'owner_id' => $owner_id,
                            ), array(
                        'request_id' => 'required|integer',
                        'rating' => 'required|integer',
                        'token' => 'required',
                        'owner_id' => 'required|integer'
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        // Do necessary operations
                        if ($request = Requests::find($request_id)) {
                            if ($request->owner_id == $owner_data->id) {
                                if ($request->is_completed == 1) {
                                    if ($request->is_walker_rated == 0) {
                                        $walker_review = new WalkerReview;
                                        $walker_review->request_id = $request_id;
                                        $walker_review->walker_id = $request->confirmed_walker;
                                        $walker_review->rating = $rating;
                                        $walker_review->owner_id = $owner_data->id;
                                        $walker_review->comment = $comment;
                                        $walker_review->save();

                                        $request->is_walker_rated = 1;
                                        $request->save();

                                        $response_array = array('success' => true);
                                        $response_code = 200;
                                    } else {
                                        $response_array = array('success' => false, 'error' => 'Already Rated', 'error_code' => 409);
                                        $response_code = 200;
                                    }
                                } else {
                                    $response_array = array('success' => false, 'error' => 'Walk is not completed', 'error_code' => 409);
                                    $response_code = 200;
                                }
                            } else {
                                $response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Dog ID', 'error_code' => 407);
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

    // Setting Owner Location

    public function set_location() {
        if (Request::isMethod('post')) {
            $latitude = Input::get('latitude');
            $longitude = Input::get('longitude');
            $token = Input::get('token');
            $owner_id = Input::get('id');

            $validator = Validator::make(
                            array(
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'token' => $token,
                        'owner_id' => $owner_id,
                            ), array(
                        'latitude' => 'required',
                        'longitude' => 'required',
                        'token' => 'required',
                        'owner_id' => 'required|integer'
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {

                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
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
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

// Get Walk Location



    public function get_walk_location() {

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $timestamp = Input::get('ts');


        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if ($request = Requests::find($request_id)) {
                        if ($request->owner_id == $owner_id) {
                            if (isset($timestamp)) {
                                $walk_locations = WalkLocation::where('request_id', '=', $request_id)->where('created_at', '>', $timestamp)->orderBy('created_at')->get();
                            } else {
                                $walk_locations = WalkLocation::where('request_id', '=', $request_id)->orderBy('created_at')->get();
                            }
                            $locations = array();

                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;


                            foreach ($walk_locations as $walk_location) {
                                $location = array();
                                $location['latitude'] = $walk_location->latitude;
                                $location['longitude'] = $walk_location->longitude;
                                $location['distance'] = convert($walk_location->distance, $unit);
                                $location['timestamp'] = $walk_location->created_at;
                                array_push($locations, $location);
                            }

                            $response_array = array('success' => true, 'locationdata' => $locations);
                            $response_code = 200;
                        } 
						else 
						{
                            $response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Dog ID', 'error_code' => 407);
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

    /*
      // walk summary

      public function get_walk_summary()
      {
      if (Request::isMethod('get')) {
      $walk_id = Input::get('walk_id');
      $token = Input::get('token');
      $owner_id = Input::get('id');

      $validator = Validator::make(
      array(
      'walk_id' => $walk_id,
      'token' => $token,
      'owner_id' => $owner_id,
      ),
      array(
      'walk_id' => 'required|integer',
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
      if ($walk = Walk::find($walk_id)) {
      if ($walk->dog_id == $owner_data->dog_id) {

      if ($walk->is_completed == 1) {


      $response_array = array(
      'success' => true,
      'is_poo' => $walk->is_poo,
      'is_pee' => $walk->is_pee,
      'time' => $walk->time,
      'distance' => $walk->distance,
      'photo_url' => $walk->photo_url,
      'video_url' => $walk->video_url,
      'note' => $walk->note,

      );
      $response_code = 200;
      } else {
      $response_array = array('success' => false, 'error' => 'Walk not completed', 'error_code' => 415);
      $response_code = 200;
      }
      } else {
      $response_array = array('success' => false, 'error' => 'Walk ID doesnot matches with Dog ID', 'error_code' => 407);
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
     */

    /*

      // Assign Walker

      public function assign_walker()
      {

      $walker_id = Input::get('walker_id');
      $schedule_id = Input::get('schedule_id');
      $token = Input::get('token');
      $owner_id = Input::get('id');
      $validator = Validator::make(
      array(
      'walker_id' => $walker_id,
      'token' => $token,
      'owner_id' => $owner_id,
      'schedule_id' => $schedule_id,
      ),
      array(
      'walker_id' => 'required|integer',
      'token' => 'required',
      'owner_id' => 'required|integer',
      'schedule_id' => 'required|integer'
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
      if ($schedule = Schedules::find($schedule_id)) {
      if ($schedule->dog_id == $owner_data->dog_id) {

      $schedule->walker_id = $walker_id;
      $schedule->save();

      DB::table('walk')->where('schedule_id', '=', $schedule_id)->update(array('walker_id' => $walker_id));

      $response_array = array('success' => true);
      $response_code = 200;

      } else {
      $response_array = array('success' => false, 'error' => 'Schedule ID doesnot matches with Dog ID', 'error_code' => 407);
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
     */

    // Get Available Providers if provider_selection == 1 in settings table

    public function get_providers() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $type = Input::get('type');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if (!$type) {
                        // choose default type
                        $provider_type = ProviderType::where('is_default', 1)->first();

                        if (!$provider_type) {
                            $type = 1;
                        } else {
                            $type = $provider_type->id;
                        }
                    }

                    foreach ($type as $key) {
                        $typ[] = $key;
                    }
                    $ty = implode(",", $typ);

                    $typequery = "SELECT distinct provider_id from walker_services where type IN($ty)";
                    $typewalkers = DB::select(DB::raw($typequery));
                    Log::info('typewalkers = '.print_r($typewalkers,true));
                    foreach ($typewalkers as $key) {
                        $types[] = $key->provider_id;
                    }
                    $typestring = implode(",", $types);
                    Log::info('typestring = '.print_r($typestring,true));

                    if($typestring==''){
                        $response_array = array('success' => false, 'error' => 'No provider found matching the service type.', 'error_code' => 405);
                        $response_code = 200;
                        return Response::json($response_array, $response_code);
                    }

                    $settings = Settings::where('key', 'default_search_radius')->first();
                    $distance = $settings->value;
                    $query = "SELECT walker.id, 1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance and walker.id IN($typestring) order by distance";
                    $walkers = DB::select(DB::raw($query));
                    Log::info('walkers = '.print_r($walkers, true));
                    if($walkers!=NULL){
                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $request = new Requests;
                        $request->owner_id = $owner_id;
                        $request->request_start_time = date("Y-m-d H:i:s");
                        $request->save();
                        foreach ($type as $key) 
						{
                            $reqserv = new RequestServices;
                            $reqserv->request_id = $request->id;
                            $reqserv->type = $key;
                            $reqserv->save();
                        }
                        $response_array = array(
                            'success' => true,
                            'request_id' => $request->id,
                            'walkers' => $walkers,
                        );
                        $response_code = 200;
                    }else{
                        $response_array = array(
                            'success' => false,
                            'error' => 'No walker found',
                        );
                        $response_code = 200;
                    }
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

    // Create Request if provider_selection == 2 in settings table

    public function create_request_providers(){
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $provider_id = Input::get('provider_id');
        $request_id = Input::get('request_id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'provider_id' => $provider_id,
                    'request_id' => $request_id,
                    
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'provider_id' => 'required',
                    'request_id' => 'required',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $req = Requests::find($request_id);
                    $req->current_walker = $provider_id;
                    $req->save();
                    
                    $response_array = array(
                        'success' => true,
                        'request_id' => $req->id,
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

    // Cancel Request
    public function cancellation() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'request_id' => $request_id,
                    
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'request_id' => 'required',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $req = Requests::find($request_id);
                    if($req->is_paid==0){
                        DB::delete("delete from request_services where request_id = '".$request_id."';");
                        DB::delete("delete from walk_location where request_id = '".$request_id."';");
                        $req->is_cancelled = 1;
                        $req->save();
                        $response_array = array(
                            'success' => true,
                            'deleted request_id' => $req->id,
                        );
                        $response_code = 200;
                    }else{
                        $deduce = 0.85;
                        $refund = $req->total*$deduce;
                        $req->is_cancelled = 1;
                        $req->refund = $refund;
                        $req->save();
                        // Refund Braintree Stuff.
                        DB::delete("delete from request_services where request_id = '".$request_id."';");
                        DB::delete("delete from walk_location where request_id = '".$request_id."';");
                        $response_array = array(
                            'success' => true,
                            'refund' => $refund,
                            'deleted request_id' => $req->id,
                        );
                        $response_code = 200;
                    }
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

    // Create Request if provider_selection == 1 in settings table

    public function create_request() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $type = Input::get('type');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } 
		else 
		{
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if (!$type) {
                        // choose default type
                        $provider_type = ProviderType::where('is_default', 1)->first();

                        if (!$provider_type) {
                            $type = 0;
                        } else {
                            $type = $provider_type->id;
                        }
                    }

                    $settings = Settings::where('key', 'default_search_radius')->first();
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
                        if ($i == 0) 
						{
                            $first_walker_id = $walker->id;
                            $i++;
                        }
                        $request_meta->save();
                    }
                    $req = Requests::find($request->id);
                    $req->current_walker = $first_walker_id;
                    $req->save();
					/*$reqserv = new RequestServices;
                    $reqserv->request_id = $request->id;
                    $reqserv->type = $type;
                    $reqserv->save();*/
                    $settings = Settings::where('key', 'provider_timeout')->first();
                    $time_left = $settings->value;

                    // Send Notification
                    $walker = Walker::find($first_walker_id);
                    if ($walker) {
                        $msg_array = array();
                        $msg_array['unique_id'] = 1;
                        $msg_array['request_id'] = $request->id;
                        $msg_array['time_left_to_respond'] = $time_left;
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

                        $title = "New Request";
						$title_lithuanian="Nauja užklausa";
                        $message = $msg_array;
                        /* don't do json_encode in above line because if */
                        send_notifications($first_walker_id, "walker", $title, $title_lithuanian, $message);
                    }


                    // Send SMS 
                    /*$settings = Settings::where('key', 'sms_request_created')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%user%', $owner_data->first_name . " " . $owner_data->last_name, $pattern);
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%user_mobile%', $owner_data->phone, $pattern);
                    sms_notification(1, 'admin', $pattern);*/

                    // send email
                    $settings = Settings::where('key', 'email_new_request')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $request->id, $pattern);
                    $subject = "New Request Created";
                    email_notification(1, 'admin', $pattern, $subject);

                    $response_array = array(
                        'success' => true,
                        'request_id' => $request->id,
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

    // Get cancel request

    public function cancel_request() 
	{

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) 
			{
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) 
				{
                    // Do necessary operations
                    if ($request = Requests::find($request_id)) 
					{

                        if ($request->owner_id == $owner_data->id) 
						{
							
                            Requests::where('id', $request_id)->update(array('is_cancelled' => 1,'cancelled_by'=>'Owner'));
                            RequestMeta::where('request_id', $request_id)->update(array('is_cancelled' => 1));
                            if ($request->confirmed_walker) 
							{
                                $walker = Walker::find($request->confirmed_walker);
                                $walker->is_available = 1;
                                $walker->save();
                            }
                            if ($request->current_walker) 
							{
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
                            $response_array = array(
                                'success' => true,
                            );

                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with Owner ID', 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 'Request ID Not Found', 'error_code' => 408);
                        $response_code = 200;
                    }
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

    // Get Request Status

    public function get_request() {

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if ($request = Requests::find($request_id)) {

                        if ($request->owner_id == $owner_data->id) {

                            if ($request->confirmed_walker != 0) {
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
                                $walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ? : 0;
                                $walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

                                $settings = Settings::where('key', 'default_distance_unit')->first();
                                $unit = $settings->value;
                                $bill = array();
                                if ($request->is_completed == 1) {
                                    $bill['distance'] = convert($request->distance, $unit);
                                    $bill['time'] = $request->time;
                                    $bill['base_price'] = $request->base_price;
                                    $bill['distance_cost'] = $request->distance_cost;
                                    $bill['time_cost'] = $request->time_cost;
                                    $bill['total'] = $request->total;
                                    $bill['is_paid'] = $request->is_paid;
                                }

                                $response_array = array(
                                    'success' => true,
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

                                $response_array['accepted_time'] = $request->request_start_time;
                                if ($request->is_started == 1) {
                                    $response_array['start_time'] = DB::table('walk_location')
                                            ->where('request_id', $request_id)
                                            ->min('created_at');

                                    $settings = Settings::where('key', 'default_distance_unit')->first();
                                    $unit = $settings->value;

                                    $response_array['distance'] = DB::table('walk_location')
                                            ->where('request_id', $request_id)
                                            ->max('distance');

                                    $response_array['distance'] = convert($response_array['distance'], $unit);
                                }

                                if ($request->is_completed == 1) {
                                    $response_array['end_time'] = DB::table('walk_location')
                                            ->where('request_id', $request_id)
                                            ->max('created_at');
                                }
                            } else {

                                $response_array = array(
                                    'success' => true,
                                    'status' => $request->status,
                                    'confirmed_walker' => 0,
                                );
                            }
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with Owner ID', 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 'Request ID Not Found', 'error_code' => 408);
                        $response_code = 200;
                    }
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

    // Get Request Status


    public function get_request_location() {

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if ($request = Requests::find($request_id)) {

                        if ($request->owner_id == $owner_data->id) {

                            if ($request->confirmed_walker != 0) {
                                if ($request->is_started == 0) {
                                    $walker = Walker::find($request->confirmed_walker);
                                    $distance = 0;
                                } else {
                                    $walker = WalkLocation::where('request_id', $request->id)->orderBy('created_at', 'desc')->first();
                                    $distance = $walker->distance;
                                }

                                $settings = Settings::where('key', 'default_distance_unit')->first();
                                $unit = $settings->value;
                                $distance = convert($distance, $unit);

                                $response_array = array(
                                    'success' => true,
                                    'latitude' => $walker->latitude,
                                    'longitude' => $walker->longitude,
                                    'distance' => $distance,
                                );
                            } else {

                                $response_array = array(
                                    'success' => false,
                                    'error' => 'Walker not Confirmed yet',
                                    'error_code' => 421,
                                );
                            }
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with Owner ID', 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 'Request ID Not Found', 'error_code' => 408);
                        $response_code = 200;
                    }
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

    // check status and Send Request to walker
    // if request not timed out do nothing
    // else send new request
    // if user accepted change stat of request

    public function schedule_request() {
        $time = date("Y-m-d H:i:s");
        $query = "SELECT id,owner_id,current_walker,TIMESTAMPDIFF(SECOND,request_start_time, '$time') as diff from request where status = 0 and is_cancelled = 0";
        $results = DB::select(DB::raw($query));
        foreach ($results as $result) {
            $settings = Settings::where('key', 'provider_timeout')->first();
            $timeout = $settings->value;
            if ($result->diff >= $timeout) {
                // Archiving Old Walker
                RequestMeta::where('request_id', '=', $result->id)->where('walker_id', '=', $result->current_walker)->update(array('status' => 2));
                $request_meta = RequestMeta::where('request_id', '=', $result->id)->where('status', '=', 0)->orderBy('created_at')->first();

                // update request
                if (isset($request_meta->walker_id)) {
                    // assign new walker
                    Requests::where('id', '=', $result->id)->update(array('current_walker' => $request_meta->walker_id, 'request_start_time' => date("Y-m-d H:i:s")));

                    // Send Notification

                    $walker = Walker::find($request_meta->walker_id);

                    $owner_data = Owner::find($result->owner_id);
                    $msg_array = array();
                    $msg_array['request_id'] = $result->id;
                    $msg_array['id'] = $request_meta->walker_id;
                    if ($walker) {
                        $msg_array['token'] = $walker->token;
                    }
                    $msg_array['client_profile'] = array();
                    $msg_array['client_profile']['name'] = $owner_data->first_name . " " . $owner_data->last_name;
                    $msg_array['client_profile']['picture'] = $owner_data->picture;
                    $msg_array['client_profile']['bio'] = $owner_data->bio;
                    $msg_array['client_profile']['address'] = $owner_data->address;
                    $msg_array['client_profile']['phone'] = $owner_data->phone;

                    $title = "New Request";
					$title_lithuanian="Nauja užklausa";
                    $message = $msg_array;
                    send_notifications($request_meta->walker_id, "walker", $title, $title_lithuanian, $message);
                } else {
                    // request ended
                    Requests::where('id', '=', $result->id)->update(array('current_walker' => 0, 'status' => 1));

                    /*$settings = Settings::where('key', 'sms_request_unanswered')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%id%', $result->id, $pattern);
                    sms_notification(1, 'admin', $pattern);*/

                    // send email
                    $settings = Settings::where('key', 'email_request_unanswered')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%id%', $result->id, $pattern);
                    $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $result->id, $pattern);
                    $subject = "New Request Unansweres";
                    email_notification(1, 'admin', $pattern, $subject);
                }
            }
        }
    }

    // Request in Progress

    public function request_in_progress() {


        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $request = Requests::where('status', '=', 1)->where('is_completed', '=', 0)->where('is_cancelled', '=', 0)->where('owner_id', '=', $owner_id)->orderBy('created_at', 'desc')->first();
                    if ($request) {
                        $request_id = $request->id;
                    } else {
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

    /*

      // Get Walkers

      public function get_walkers()
      {
      if (Request::isMethod('get')) {
      $schedule_id = Input::get('schedule_id');
      $token = Input::get('token');
      $owner_id = Input::get('id');
      $latitude = Input::get('latitude');
      $longitude = Input::get('longitude');
      $distance = Input::get('distance');

      $validator = Validator::make(
      array(
      'schedule_id' => $schedule_id,
      'token' => $token,
      'owner_id' => $owner_id,
      'latitude' => $latitude,
      'longitude' => $longitude,
      'distance' => $distance,
      ),
      array(
      'schedule_id' => 'required|integer',
      'token' => 'required',
      'owner_id' => 'required|integer',
      'latitude' => 'required',
      'longitude' => 'required',
      'distance' => 'required|integer'
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
      if ($schedule = Schedules::find($schedule_id)) {
      if ($schedule->dog_id == $owner_data->dog_id) {

      // Get Latitude
      $schedule_meta = ScheduleMeta::where('schedule_id', '=', $schedule->id)
      ->orderBy('started_on', 'DESC')
      ->get();
      $flag = 0;
      $date = "0000-00-00";
      $days = array();
      foreach ($schedule_meta as $meta) {
      if ($flag == 0) {
      $date = $meta->started_on;
      $flag++;
      }
      array_push($days, $meta->day);
      }

      $start_time = date('H:i:s', strtotime($schedule->start_time) - (60 * 60));
      $end_time = date('H:i:s', strtotime($schedule->end_time) + (60 * 60));
      $days_str = implode(',', $days);

      $query = "SELECT walker.id,walker.bio,walker.first_name,walker.last_name,walker.picture,walker.latitude,walker.longitude from walker where id NOT IN ( SELECT distinct schedules.walker_id FROM `schedule_meta` left join schedules on schedule_meta.schedule_id = schedules.id where schedules.is_confirmed	 != 0 and schedule_meta.day IN ($days_str) and schedule_meta.ends_on >= '$date' and schedule_meta.started_on <= '$date' and ((schedules.start_time > '$start_time' and schedules.start_time < '$end_time') OR ( schedules.end_time > '$start_time' and schedules.end_time < '$end_time' )) ) and (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance limit 10";

      $walkers = DB::select(DB::raw($query));
      $walker_list = array();
      foreach ($walkers as $walker) {
      $data['walker_id'] = $walker->id;
      $data['first_name'] = $walker->first_name;
      $data['last_name'] = $walker->last_name;
      $data['bio'] = $walker->bio;
      $data['latitude'] = $walker->latitude;
      $data['longitude'] = $walker->longitude;
      $data['picture'] = $walker->picture;
      $data['type'] = $walker->type;
      $data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
      $reviews = WalkerReview::where('walker_id', '=', $walker->id)->get();
      $data['reviews'] = array();
      foreach ($reviews as $review) {
      array_push($data['reviews'], $review->comment);

      }

      array_push($walker_list, $data);
      }

      $response_array = array(
      'success' => true,
      'walkers' => $walker_list,
      );
      $response_code = 200;

      } else {
      $response_array = array('success' => false, 'error' => 'Schedule ID doesnot matches with Dog ID', 'error_code' => 407);
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
     */

    /*

      // Add Walk or Schedule or Walk

      public function add_schedule()
      {
      if (Request::isMethod('post')) {
      $start_time = Input::get('start_time');
      $end_time = Input::get('end_time');
      $days = Input::get('days');
      $is_recurring = Input::get('is_recurring');
      $lock_box_info = Input::get('lock_box_info');
      $note = Input::get('note');
      $token = Input::get('token');
      $owner_id = Input::get('id');

      $validator = Validator::make(
      array(
      'start_time' => $start_time,
      'end_time' => $end_time,
      'is_recurring' => $is_recurring,
      'lock_box_info' => $lock_box_info,
      'note' => $note,
      'token' => $token,
      'owner_id' => $owner_id,
      ),
      array(
      'start_time' => 'required',
      'end_time' => 'required',
      'is_recurring' => 'required|integer',
      'token' => 'required',
      'lock_box_info' => 'required',
      'note' => 'required',
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

      $schedule = new Schedules;
      $schedule->dog_id = $owner_data->dog_id;
      $schedule->lockbox_info = $lock_box_info;
      $schedule->notes = $note;
      $schedule->is_recurring = $is_recurring;
      $schedule->start_time = $start_time;
      $schedule->end_time = $end_time;

      $schedule->save();
      if ($is_recurring == 1) {
      foreach ($days as $day) {
      $day_copy = $day;
      $today = date("w");
      if ($day - $today >= 0) {
      $day = $day - $today;
      } else {
      $day = ($day + 7) - $today;
      }

      $inc = $day;
      $time = time();
      $date = strtotime("+$inc day", $time);
      $walk_date = date("Y-m-d", $date);


      // Adding Schedule Meta
      $schedule_meta = new ScheduleMeta;
      $schedule_meta->schedule_id = $schedule->id;
      $schedule_meta->day = $day_copy;
      $schedule_meta->started_on = $walk_date;
      $schedule_meta->ends_on = "2100-01-01";
      $schedule_meta->save();

      // Adding Walks for 4 weeks

      $today = date("w");
      if ($day - $today >= 0) {
      $day = $day - $today;
      } else {
      $day = ($day + 7) - $today;
      }
      for ($i = 0; $i < 12; $i++) {
      $inc = $day + ($i * 7);
      $time = time();
      $date = strtotime("+$inc day", $time);
      $walk_date = date("Y-m-d", $date);

      $walk = new Walk;
      $walk->schedule_id = $schedule->id;
      $walk->dog_id = $owner_data->dog_id;
      $walk->date = $walk_date;
      $walk->meta_id = $schedule_meta->id;
      $walk->save();

      }

      }

      // Add Walks
      } else {

      foreach ($days as $day) {
      $day_copy = $day;
      $today = date("w");
      if ($day - $today >= 0) {
      $day = $day - $today;
      } else {
      $day = ($day + 7) - $today;
      }

      $time = time();
      $date = strtotime("+$day day", $time);
      $walk_date = date("Y-m-d", $date);

      $schedule_meta = new ScheduleMeta;
      $schedule_meta->schedule_id = $schedule->id;
      $schedule_meta->day = $day_copy;
      $schedule_meta->started_on = $walk_date;
      $schedule_meta->ends_on = $walk_date;
      $schedule_meta->seeding_status = 1;
      $schedule_meta->save();

      $walk = new Walk;
      $walk->schedule_id = $schedule->id;
      $walk->dog_id = $owner_data->dog_id;
      $walk->date = $walk_date;
      $walk->save();
      }

      }

      $response_array = array('success' => true , 'schedule_id' => $schedule->id);
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
     */

    /*

      // Add Walk or Schedule or Walk

      public function cancel_schedule()
      {
      if (Request::isMethod('post')) {
      $schedule_id = Input::get('schedule_id');
      $date = Input::get('date');
      $day = Input::get('day');
      $mode = Input::get('mode');
      $token = Input::get('token');
      $owner_id = Input::get('id');

      $validator = Validator::make(
      array(
      'schedule_id' => $schedule_id,
      'mode' => $mode,
      'token' => $token,
      'owner_id' => $owner_id,
      ),
      array(
      'schedule_id' => 'required|integer',
      'mode' => 'required|integer',
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
      if ($schedule = Schedules::find($schedule_id)) {
      if ($owner_data->dog_id == $schedule->dog_id) {
      // Delete schedule or corresponding walks
      if ($mode == 0) {
      if(!$date)
      {
      $response_array = array('success' => false , 'error' => 'Invalid Input' , 'error_code' => 401);
      $response_code = 200;
      }
      else{
      $affected_rows =Walk::where('schedule_id','=',$schedule_id)->where('date','=',$date)->where('is_completed','=',0)->update(array('is_cancelled' => 1));
      // add cancelled walk if a walk is not yet scheduled
      if($affected_rows == 0){
      $walk = new Walk;
      $walk->schedule_id = $schedule_id;
      $walk->dog_id = $owner_data->dog_id;
      $walk->date = $date;
      $walk->is_cancelled = 1;
      $walk->save();
      }
      $response_array = array('success' => true);
      $response_code = 200;
      }

      } elseif ($mode == 1) {
      if(!$day) {
      $response_array = array('success' => false , 'error' => 'Invalid Input' , 'error_code' => 401);
      $response_code = 200;
      } else {
      $date = date('Y-m-d');
      ScheduleMeta::where('schedule_id','=',$schedule_id)->where('day','=',$day)->update(array('ends_on' => $date));
      // update all corresponding walks
      $schedule_meta = ScheduleMeta::where('schedule_id','=',$schedule_id)->where('day','=',$day)->first();
      Walk::where('schedule_id','=',$schedule_id)->where('meta_id','=',$schedule_meta->id)->where('is_completed','=',0)->update(array('is_cancelled' => 1,'updated_at' => time()));
      $response_array = array('success' => true);
      $response_code = 200;
      }
      } else {
      $date = date('Y-m-d');
      ScheduleMeta::where('schedule_id', '=', $schedule_id)->update(array('ends_on' => $date));
      Walk::where('schedule_id','=',$schedule_id)->where('is_completed','=',0)->update(array('is_cancelled' => 1));
      $response_array = array('success' => true);
      $response_code = 200;
      }

      } else {
      // Token and Schedule ID doesnot matches
      $response_array = array('success' => false, 'error' => 'Token and Schedule ID doesnot match', 'error_code' => 411);
      $response_code = 200;

      }
      } else {
      // schedule not found
      $response_array = array('success' => false, 'error' => 'Schedule ID not found', 'error_code' => 412);
      $response_code = 200;
      }
      //$response_array = array('success' => true);
      //$response_code = 200;

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

     */

    /* 	

      // Non Reviewed Walks

      public function nonreviewedwalks()
      {
      if (Request::isMethod('get')) {

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
      $walks = Walk::where('is_walker_rated', '=', 0)->where('is_completed', '=', 1)->where('dog_id', '=', $owner_data->dog_id)->get();

      $walk_ids = array();
      if (!empty($walks)) {
      foreach ($walks as $walk) {
      array_push($walk_ids, $walk->id);
      }
      }

      $response_array = array(
      'walk_ids' => $walk_ids,
      'success' => true,
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
     */

/*
      public function get_walks()
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

      $dog_id = $owner_data->dog_id;
      $walks = Walk::where('dog_id', '=', $dog_id)->where('is_started', '=', 0)->get();

      //$walks = Walk::where('walker_id','=',$walker_id)->where('is_started','=',0)->where('is_cancelled','=',0)->where('is_confirmed','=',1)->get();
      $walks = $walks = DB::table('walk')->where('walk.dog_id','=',$dog_id)->where('is_started','=',0)->where('is_confirmed','=',1)
      ->leftJoin('schedules', 'walk.schedule_id', '=', 'schedules.id')
      ->get();


      $walk_data = array();
      foreach ($walks as $walk) {
      $data = array();
      $data['walk_id'] = $walk->id;
      $data['walker_id'] = $walk->walker_id;
      $data['schedule_id'] = $walk->schedule_id;
      $data['date'] = $walk->date;
      $data['start_time'] = $walk->start_time;
      $data['end_time'] = $walk->end_time;
      $data['is_confirmed'] = $walk->is_confirmed;
      $data['is_cancelled'] = $walk->is_cancelled;
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
      $response_array = array('success' => false, 'error' => 'Owner ID not Found', 'error_code' => 410);

      } else {
      $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

      }
      $response_code = 200;
      }
      }


      $response = Response::json($response_array, $response_code);
      return $response;

      }*/
}

    
