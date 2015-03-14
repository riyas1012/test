<html>
    <!-- START Head -->
    <head>
    <?php $theme = Theme::all();?>
        <!-- START META SECTION -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?= $title ?> | <?= Config::get('app.website_title') ?> Web Dashboard</title>
        <meta name="author" content="pampersdry.info">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
         <link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
         <script src="//code.jquery.com/jquery-1.10.2.js"></script>
         <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
         
         <?php 
         $active='#000066';
         $logo = 'logo.png';
         $favicon='favicon.ico';
         foreach($theme as $themes) {
            $active = $themes->active_color; 
            $favicon = $themes->favicon;
            $logo= $themes->logo;
        }
        if($logo=='')
        {
         $logo = 'logo.png';
        }
        if($favicon=='')
        {
            $favicon='favicon.ico';
        }?>

        <link rel="icon" type="image/ico" href="<?php echo asset_url(); ?>/uploads/<?php echo $favicon;?>">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon-114x114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon-57x57-precomposed.png">
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon.png">
        <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.5/angular.min.js"></script>
        <!--/ END META SECTION -->

        <!-- START STYLESHEETS -->
        <!-- Plugins stylesheet : optional -->

        <!--/ Plugins stylesheet -->

        <!-- Application stylesheet : mandatory -->
        <!--<link rel="stylesheet" href="<?php echo asset_url(); ?>library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo asset_url(); ?>stylesheet/layout.min.css">
        <link rel="stylesheet" href="<?php echo asset_url(); ?>stylesheet/uielement.min.css">

        <link rel="stylesheet"href="<?php echo asset_url(); ?>plugins/datatables/css/jquery.datatables.min.css">
        -->
         <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
      
        <script src="<?php echo asset_url(); ?>/javascript/colpick.js"></script>
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/colpick.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/bootstrap.css">
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset_url(); ?>/stylesheet/theme_cus.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/style.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/responsive-style.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/font-awesome.min.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/tinyeditor.css">

        
        <!--/ Application stylesheet -->
        <!-- END STYLESHEETS -->


           <script src="<?php echo asset_url(); ?>/web/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.ui.touch-punch.min.js"></script>

    
        <script type="text/javascript" src="<?php echo asset_url(); ?>/library/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/library/core/js/core.min.js"></script>
        <!-- START JAVASCRIPT SECTION - Load only modernizr script here -->
        <script src="<?php echo asset_url(); ?>/library/modernizr/js/modernizr.min.js"></script>
        <!--/ END JAVASCRIPT SECTION -->
        <script src="http://cdn.ckeditor.com/4.4.5/full/ckeditor.js"></script>

        <style type="text/css">
        tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
        }

        #col1{
            text-align: right;
            font-weight: 800;
            padding: 10px;
        }
        #col2{
            padding-left: 20px;
            padding: 10px;
        }

        #map {
        height: 60%;
        width: 100%
        }
        </style>
        <?php 
                 $theme_color = '#0000CC';
                 $primary_color = '#0000FF';
                 $secondary_color = '#3366FF';
                 $hover_color = '#8AB800';
                 $active_color = '#000066'; 
                foreach($theme as $themes) {
                 $theme_color = $themes->theme_color;
                 $primary_color =$themes->primary_color;
                 $secondary_color =$themes->secondary_color;
                 $hover_color =$themes->hover_color;
                 $active_color = $themes->active_color;
                 
}
        ?>
        <style>
 #picker {
    border-right:30px solid <?php echo $theme_color;?>;
}
#picker1 {
    border-right:30px solid <?php echo $primary_color;?>;
}
#picker2 {
    border-right:30px solid <?php echo $secondary_color;?>;
}
#picker3 {
    border-right:30px solid <?php echo $hover_color;?>;
}
#picker4 {
    border-right:30px solid <?php echo $active_color;?>;
}

</style>
    </head>
    <!--/ END Head -->

    <!-- START Body -->
    <body onload="load()">
        <div class="container">
            <div class="row towber-nav">
                <div class="col-md-5 ">
    
                    <h3><a href="<?php echo web_url(); ?>/admin/map_view"><img src="<?php echo asset_url(); ?>/uploads/<?php echo $logo;?>"  width="40" height="40"></a> UberX</h3>
                </div>
                <form method="GET" action="<?php echo web_url(); ?>/admin/search">
                <div class="col-md-2 nav-select">
                    <div class="dropdown">
                        <select class="cmb btn-info dropdown-toggle" id="dropdownMenu1" data-toggle="dropdown" name ="type">
                            <option value="provider">Providers</option>
                            <option value="user">Users</option>
                            <span class="caret"></span>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 nav-search">
                    <input type="text"  placeholder="Search" name="q"><i class="icon-search"></i>
                </div>
                <div class="col-md-2 nav-admin">
                    <div class="dropdown">
                        <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                            <i class="icon-smile"></i> Hello Admin
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                             <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/admins">Admin Control</a></li>

                            <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/logout">Log Out</a></li>
                        </ul>
                    </div>
                </div>
                </form>
            </div>
        </div>

        <div class="row second-nav">
    <div class="container">
        <ul class="nav nav-pills">
            <li id="dashboard" title="Dashboard">
                <a href="<?php echo web_url(); ?>/admin/report"><img src="<?php echo asset_url() ?>/image/menu_buttons/home_button.png"/><br>Dashboard</a>
            </li>
            <li id="map-view" title="Map View">
                <a href="<?php echo web_url(); ?>/admin/map_view"><img src="<?php echo asset_url() ?>/image/menu_buttons/user_requests.png"/><br>Map View</a>
            </li>
            <li id="walkers" title="Providers" >
                <a href="<?php echo web_url(); ?>/admin/providers"><img src="<?php echo asset_url() ?>/image/menu_buttons/user_icon.png"/>Providers</a>
            </li> 
            <li id="walks" title="Requests">
                <a href="<?php echo web_url(); ?>/admin/requests"><img src="<?php echo asset_url() ?>/image/menu_buttons/all_jobs.png"/>Requests</a>
            </li>
            <li id="owners" title="Users">

                <a href="<?php echo web_url(); ?>/admin/users"><img src="<?php echo asset_url() ?>/image/menu_buttons/user_icon.png"/>Owners</a>

            </li>
            <li id="reviews" title="Reviews">
                <a href="<?php echo web_url(); ?>/admin/reviews"><img src="<?php echo asset_url() ?>/image/menu_buttons/feedback_icon.png"/><br>Reviews</a>
            </li>
            <li id="settings" title="Setings">
                <a href="<?php echo web_url(); ?>/admin/settings"><img src="<?php echo asset_url() ?>/image/menu_buttons/seattings_icon.png"/><br>Settings</a>
            </li>
            <li id="information" title="Information">
                <a href="<?php echo web_url(); ?>/admin/informations"><img src="<?php echo asset_url() ?>/image/menu_buttons/change_about_us_icon.png"/><br>Information</a>
            </li>
            <li id="provider-type" title="Provider Types">
                <a href="<?php echo web_url(); ?>/admin/provider-types"><img src="<?php echo asset_url() ?>/image/menu_buttons/driver_icon.png"/><br>Types</a>
            </li>
            <li id="document-type" title="Provider Types">
                <a href="<?php echo web_url(); ?>/admin/document-types"><img src="<?php echo asset_url() ?>/image/menu_buttons/document.png"/><br>Documents</a>
            </li>
        </ul>
    </div>
</div>

@yield('content')

<script type="text/javascript">
    $("#<?= $page ?>").addClass("active");
    $('#option3').show();
    $('.fade').css('opacity', '1');
    $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('color', '#ffffff');
       $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('background-color', '<?php echo $active;?>');
   
</script>

</body>
<!--/ END Body -->
</html>