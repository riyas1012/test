@extends('web.layout')

@section('content')

<div class="col-md-12 mt">
    
        @if(Session::has('message'))
            <div class="alert alert-{{ Session::get('type') }}">
                <b>{{ Session::get('message') }}</b> 
            </div>
        @endif
    
    <div class="content-panel" style="min-height:600px;">
        <h4>Request Trip</h4><br>
        <div class="col-md-6">
          <div class="col-md-8">
          <input type="text" class="form-control" id="my-address" placeholder="Please enter your address">
          </div>

          <div class="col-md-4" id="flow2">
          <button id="getCords" class="btn btn-success" onClick="codeAddress();">Find Location</button>
          </div>
        
          <div class="col-md-8" id="flow3">
          <br>
              <div id="map-canvas"></div>
          </div>

          <form method="post" id="request-form" action="<?php echo web_url() ?>/user/request-trip" style="display:none;">
          <div class="form-group">
              <div class="col-sm-12">
              <label class="col-sm-12 col-sm-12 control-label">Type of Service</label>
              </div>

                  <select name="type" class="form-control" id="flow4">

                    <?php foreach ($types as $type) { ?>
                        <option value="<?= $type->id ?>"><?= $type->name ?></option>
                    <?php } ?>
                  </select>
              </div>
              <div class="col-sm-4" >
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <input type="submit" class="btn btn-primary" value="Request Trip" id="flow5">

              </div>
          </div>
          
          </form>
          
        </div>
    </div>



          
</div>


    <!--script for this page-->
    <script type="text/javascript">
      var tour = new Tour(
        {
          name: "userapprequest",
        });

        // Add your steps. Not too many, you don't really want to get your users sleepy
        tour.addSteps([
          {
            element: "#flow2", 
            title: "Choosing address", 
            content: "Please Enter your address and click on find location",
             
          },
          {
            element: "#flow3", 
            title: "Adjust location", 
            content: "You can move the marker to adjust your pick up location" 
          },
          {
            element: "#flow4", 
            title: "Choosing yype of service", 
            content: "You can select the type of service in the drop down" 
          },
          {
            element: "#flow5", 
            title: "Requesting a trip request", 
            content: "Now click on request trip to request your first trip",
             
          },
       ]);

     // Initialize the tour
     tour.init();

     // Start the tour
     tour.start();
</script>



@stop 