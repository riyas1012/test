<!doctype html>
<html class="" lang="en">
<head>
    <meta charset="utf-8">
    <title>Uber X</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/styles.css" />
	<link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/introjs.css" />
	<link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/jquery-ui.css" />
	<link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/font-awesome.min.css">
  <script src="js/colpick.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/colpick.css" type="text/css"/>
	<link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/style.css">
	<link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/responsive-style.css">
</head>
<body>

@yield('body')

<link href='http://fonts.googleapis.com/css?family=Raleway:400,500,600,300,200,100' rel='stylesheet' type='text/css'>
<script src="<?php echo asset_url(); ?>/website/scripts/jquery.js"></script>
<script src="<?php echo asset_url(); ?>/website/scripts/bootstrap.min.js"></script>

<script src="<?php echo asset_url(); ?>/web/js/bootstrap-tour.js"></script>

<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/map.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/intro.js"></script>

</body>

</html>
    <script type="text/javascript">
      var tour = new Tour(
        {
          name: "homepage",
        });

        // Add your steps. Not too many, you don't really want to get your users sleepy
        tour.addSteps([
          {
            element: "#user", 
            title: "Sign up as a User", 
            content: "Open this in a new tab to sign in as a user",
             
          },
          {
            element: "#provider", 
            title: "Sign up as a Driver", 
            content: "Open this link in a new Incognito Window to sign in as a driver",
             
          },
          {
            element: "#user-signin", 
            title: "Sign up as a User", 
            content: "Click on signup button",
             
          },

       ]);

     // Initialize the tour
     tour.init();

     // Start the tour
     tour.start();
</script>

</body>

</html>

