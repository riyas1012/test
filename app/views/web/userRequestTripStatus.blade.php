@extends('web.layout')

@section('content')

<div class="col-md-12 mt">

    @if(Session::has('message'))
            <div class="alert alert-{{ Session::get('type') }}">
                <b>{{ Session::get('message') }}</b> 
            </div>
    @endif

    @if(Session::has('status') && Session::get('status') == 5)
            <div class="alert alert-success">
                <b>Your Request is Completed. Please Rate the Driver.</b> 
            </div>
    @endif

    @if(Session::has('status') && Session::get('status') == 1)
            <div class="alert alert-success">
                <b>A new driver has accepted your request.</b> 
            </div>
    @endif

    <div class="content-panel" style="min-height:600px;">
        <h4>Trip Status</h4><br>
        <div class="col-md-6">
          <div class="col-md-8">
          <br>
              <div id="map-canvas"></div>
          </div>
        </div>
        <div  class="col-md-6">
          
          <div class="col-md-12">
            <div  class="col-md-12">
            <h3>Request ID #<?= $request->id ?></h3>
            <img src="<?= $type->icon ?>" class="img-circle" width="60">
            <b>&nbsp; <?= $type->name ?></b>
            </div>
            <?php if (isset($request->confirmed_walker) && $request->confirmed_walker != 0) { ?>
              <div  class="col-md-12">
              <div class="col-lg-12" style="height:50px;postion:relative;top:30px;">
              <b>Driver Profile</b>
              </div>

              <div class="col-lg-2">
                <p><a href="profile.html"><img src="{{ isset($walker->picture)?$walker->picture:'' }}" class="img-circle" width="50"></a></p>
              </div>
              <div class="col-lg-8">
                <div class="col-lg-12">
                  <b>{{ isset($walker->first_name)?$walker->first_name:'' }} {{ isset($walker->last_name)?$walker->last_name:'' }}</b>
                </div>
                <div class="col-lg-12">
                @for ($i = 1; $i <= $rating; $i++)
                    <span><img src="{{ web_url() }}/web/star.png"></span>
                @endfor

                </div>

              </div>

            

              </div>
            <?php } ?>
            @if(Session::has('status') && Session::get('status') == 5)

                <div class="col-lg-12">
                    
                    <h3>Leave Your Review</h3>
                    <form method="post" action="<?php echo web_url(); ?>/user/post-review">
                      <input type="hidden" name="request_id" value="{{ Session::get('request_id') }}">
                      <div class="col-lg-7">
                        <select class="form-control" name="rating">
                        <option value="5">5 Star</option>
                        <option value="4">4 Star</option>
                        <option value="3">3 Star</option>
                        <option value="2">2 Star</option>
                        <option value="1">1 Star</option>
                      </select>
                      <br>
                      <textarea class="form-control" rows="5" name="review"></textarea>
                      <br>

                      <input type="Submit" class="btn btn-primary" value="Submit Review" id="flow7">
                      </div>
                    </form>
                    
                </div>

                @endif

                @if(Session::has('status') && Session::get('status') == 0)

                <div class="col-lg-12" >
                  <br><a href="<?php echo web_url(); ?>/user/trip/cancel/<?= $request->id ?>"><button class="btn btn-primary" id="flow6">Cancel Trip</button></a>

                </div>
                @endif

          </div>
          
        </div>
    </div>



          
</div>

<script type="text/javascript">
  initialize_map(<?= $user->latitude ?>,<?= $user->longitude ?>);
</script>

<script type="text/javascript">
      var tour = new Tour(
        {
          name: "userapprequeststatus",
        });

        // Add your steps. Not too many, you don't really want to get your users sleepy
        tour.addSteps([
          {
            element: "#flow6", 
            title: "Next Steps", 
            content: "Now open the driver app and accept the trip request <br><br> Tip - You can also cancel your current trip by clicking on cancel trip." 
          },
          {
            element: "#flow7", 
            title: "Leave your Review", 
            content: "Leave the rating and review for your trip and click on submit review" 
          },
       ]);

     // Initialize the tour
     tour.init();

     // Start the tour
     tour.start();
</script>

@stop 