@extends('layout')

@section('content')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

<script type="text/javascript">
// This identifies your website in the createToken call below
    Stripe.setPublishableKey('<?= Config::get("app.stripe_publishable_key"); ?>');

    var stripeResponseHandler = function(status, response) {
    var $form = $('#payment-form');

    if (response.error) {
      // Show the errors on the form
      $form.find('.payment-errors').text(response.error.message);
      $form.find('button').prop('disabled', false);
    } else {
      // token contains id, last4, and card type
      var token = response.id;
      // Insert the token into the form so it gets submitted to the server
      $form.append($('<input type="hidden" id="stripeToken" name="stripeToken" />').val(token));
      // and re-submit

      jQuery($form.get(0)).submit();

    }
  };

  jQuery(function($) {

        $('#payment-form').submit(function(e) {
        console.log($('#stripeToken').length);
        if($('#stripeToken').length == 0)
        {
          var $form = $(this);
          // Disable the submit button to prevent repeated clicks
          $form.find('button').prop('disabled', true);

          Stripe.card.createToken($form, stripeResponseHandler);
          // Prevent the form from submitting with the default action
          return false;
        }
      });

  });
// ...
</script>

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
          <div class="row tab-content-caption">
              <div class="container">
                  <div class="col-md-10 big-text">
                      <p><?= $title ?></p>
                  </div>
              </div>
          </div>
          <div class="row editable-content-div">
            <div class="container">
              <form method="post" action="<?php echo web_url(); ?>/admin/provider/providerS_bankingSubmit" id="payment-form"  enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $provider->id ?>">
                <input type="text" name="first_name" class="form-control" placeholder="First Name" value="{{ $provider -> first_name }}" required><br>
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="{{$provider -> last_name }}" required><br>
                <input type="text" size="20" data-stripe="number" class="form-control" placeholder="Card Number" name="number" required /><br>
                <input type="text" size="4" data-stripe="cvc"  class="form-control" placeholder="CVV" required /><br>
                <input type="text" size="2" data-stripe="exp-month" placeholder="MM" class="form-control" required /><br>
                <input type="text" size="4" data-stripe="exp-year" placeholder="YYYY" class="form-control" required /><br>
                <select name="type" class="form-control">
                  <option value="individual">Individual</option>
                  <option value="corporate">Corporate</option>
                </select><br>
                <input type="text" name="email" class="form-control" placeholder="Email" value="{{$provider -> email }}" required><br>
                <br><input type="submit" value="Update Changes" class="btn btn-green">
              </form>
            </div>
          </div>
        </div>
    </div>
</div>

<?php
if($success == 1) { ?>
<script type="text/javascript">
    alert('provider Profile Updated Successfully');
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
    alert('Sorry Something went Wrong');
</script>
<?php } ?>


@stop