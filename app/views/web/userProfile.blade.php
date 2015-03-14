@extends('web.layout')

@section('content')

<div class="col-md-12 mt">
    
        @if(Session::has('message'))
            <div class="alert alert-{{ Session::get('type') }}">
                <b>{{ Session::get('message') }}</b> 
            </div>
        @endif
    
    <div class="content-panel">
        <h4>Update Profile</h4><br>
        <form class="form-horizontal style-form" method="post" action="{{ web_url() }}/user/update_profile" enctype="multipart/form-data">
                          <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">First Name</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Last Name</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="last_name" value="{{ $user->last_name }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Phone</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="phone"  value="{{ $user->phone }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Email Address</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="email"  value="{{ $user->email }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Photo</label>
                              <div class="col-sm-1">
                                <img src="{{ $user->picture }}" class="img-circle" width="60">
                              </div>
                              <div class="col-sm-5" style="position:relative;top:15px;">
                                  <input type="file" class="form-control" name="picture" >
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Bio</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="bio"  value="{{ $user->bio }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Address</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="address"  value="{{ $user->address }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">State</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="state"  value="{{ $user->state }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Country</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="country"  value="{{ $user->country }}">
                              </div>
                          </div>
                           <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Zipcode</label>
                              <div class="col-sm-6">
                                  <input type="text" class="form-control" name="zipcode"  value="{{ $user->zipcode }}">
                              </div>
                          </div>
                          <span class="col-sm-2"></span>
                          <button type="submit" class="btn btn-info">Update Profile</button>
                          <button type="reset" class="btn btn-info">Reset</button>

                      </form>
      </div>

          <div class="content-panel">
          <h4>Change Password</h4><br>
        <form class="form-horizontal style-form" method="post" action="{{ web_url() }}/user/update_password">
              <div class="form-group">
                  <label class="col-sm-2 col-sm-2 control-label">Current Password</label>
                  <div class="col-sm-6">
                      <input type="password" class="form-control" name="current_password" value="">
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 col-sm-2 control-label">New Password</label>
                  <div class="col-sm-6">
                      <input type="password" class="form-control" name="new_password" value="">
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 col-sm-2 control-label">Confirm Password</label>
                  <div class="col-sm-6">
                      <input type="password" class="form-control" name="confirm_password" value="">
                  </div>
              </div>
              <span class="col-sm-2"></span>
              <button type="submit" class="btn btn-info">Change Password</button>
              <button type="reset" class="btn btn-info">Reset</button>

        </form>
        </div>
</div>


@stop 