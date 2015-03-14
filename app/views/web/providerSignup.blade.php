<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Uber For X</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo asset_url(); ?>/web/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="<?php echo asset_url(); ?>/web/font-awesome/css/font-awesome.css" rel="stylesheet" />
        
    <!-- Custom styles for this template -->
    <link href="<?php echo asset_url(); ?>/web/css/style.css" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->

      <div id="login-page">
        <div class="container">
        
              <form class="form-login" action="<?php web_url(); ?>/provider/save" method="post">
                <h2 class="form-login-heading">Register</h2>
                <div class="login-wrap">
                    <input type="text" name="first_name" class="form-control" placeholder="First Name" autofocus><br>
                    <input type="text" name="last_name" class="form-control" placeholder="Last Name" ><br>
                    <input type="text" name="phone" class="form-control" placeholder="Mobile Number" ><br>
                    <select class="form-control" name="type">
                        <?php foreach ($types as $type) { ?>
                        <option value="<?= $type->id ?>"><?= $type->name ?></option>
                        <?php } ?>
                    </select><br>
                    <input type="text" name="email" class="form-control" placeholder="Email Address" >
                    <br>
                    <input type="password" name="password" class="form-control" placeholder="Password"><br>
                    

                    @if(Session::has('error'))
                        <div class="alert alert-danger">
                            <b>{{ Session::get('error') }}</b> 
                        </div>
                    @endif
                   


                    <button class="btn btn-theme btn-block" type="submit" id="provider-signup"><i class="fa fa-lock"></i> Register</button>
                    <hr>
                    <!--
                    <div class="login-social-link centered">
                    <p>or you can sign up via your social network</p>
                        <button class="btn btn-facebook" type="submit"><i class="fa fa-facebook"></i> Facebook</button>
                        <button class="btn btn-twitter" type="submit"><i class="fa fa-twitter"></i> Twitter</button>
                    </div>
                    -->
                    <div class="registration">
                        Do you have an account already?<br/>
                        <a class="" href="<?php echo web_url(); ?>/provider/signin  ">
                            Sign in
                        </a>
                    </div>
        
                </div>
              </form>
              
        </div>
      </div>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="<?php echo asset_url(); ?>/web/js/jquery.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/bootstrap.min.js"></script>

    <!--BACKSTRETCH-->
    <!-- You can use an image of whatever size. This script will stretch to fit in any screen size.-->
    <script type="text/javascript" src="<?php echo asset_url(); ?>/web/js/jquery.backstretch.min.js"></script>
    <script>
        $.backstretch("<?php echo asset_url(); ?>/web/img/login-bg.jpg", {speed: 500});
    </script>

            <script src="<?php echo asset_url(); ?>/web/js/bootstrap-tour.js"></script>
    <script type="text/javascript">
      var tour = new Tour(
        {
          name: "providerappSignup",
        });

        // Add your steps. Not too many, you don't really want to get your users sleepy
        tour.addSteps([
          {
            element: "#provider-signup", 
            title: "Sign up as a new Driver", 
            content: "Please fill your details and Click on Register button",
             
          },
       ]);

     // Initialize the tour
     tour.init();

     // Start the tour
     tour.start();

     </script>



  </body>
</html>
