@extends('installer.layout')

@section('content')
      <div class="col-lg-12">
        <p class="lead">Step 4 - SMS Configuration</p>
      </div>

      <div class="row marketing">
        <div class="col-lg-8"  style="min-height:350px;">
        	<form role="form" method="POST" action="{{ web_url(); }}/install">
			  <div class="form-group">
          <label for="exampleInputEmail1">Twilio Account SID</label>
          <input type="text" name="twillo_account_sid" class="form-control" placeholder="Twilio Account SID">
        </div>

        <div class="form-group">
          <label for="exampleInputEmail1">Twilio Auth Token</label>
          <input type="text" name="twillo_auth_token" class="form-control" placeholder="Twilio Auth Token">
        </div>

        <div class="form-group">
          <label for="exampleInputEmail1">Twilio Number</label>
          <input type="text" name="twillo_number" class="form-control" placeholder="Twilio Number">
        </div>

			 
			
			  <br>
			  <button type="submit" class="btn btn-primary" style="position:relative;float:right">
			  <span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span> Continue </button>
			</form>
        </div>

        <div class="col-lg-4" style="background:antiquewhite;padding-top:30px;padding-bottom:30px;color:brown;font-weight:500;min-height:350px;">
        <ul>
          <li>Basic Settings</li>
          <li>Database Configuration</li>
          <li>File Configuration</li>
          <li><b>SMS Configuration</b></li>
          <li>Email Configuration</li>
          <li>Payment Configuration</li>
          <li>Finished</li>
        </ul>
        </div>
      </div>


@stop 