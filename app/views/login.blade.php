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
<?php 
         $active='#000066';
         $favicon='favicon.ico';
         $logo = 'logo.png';
         foreach($theme as $themes) {
            $active = $themes->active_color; 
            $favicon = $themes->favicon;
            $logo= $themes->logo;
        }
            ?>

        <link rel="icon" type="image/ico" href="<?php echo asset_url(); ?>/uploads/<?php echo $favicon;?>">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon-114x114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon-57x57-precomposed.png">
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>/image/touch/apple-touch-icon.png">
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
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/bootstrap.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/style.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/theme_cus.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/responsive-style.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/font-awesome.min.css">
        <link rel="stylesheet"href="<?php echo asset_url(); ?>/stylesheet/tinyeditor.css">
        <!--/ Application stylesheet -->
        <!-- END STYLESHEETS -->


        <script type="text/javascript" src="<?php echo asset_url(); ?>/library/jquery/js/jquery.min.js"></script>

        <!--/ END JAVASCRIPT SECTION -->
    </head>
    <!--/ END Head -->

    <!-- START Body -->
   <!-- START Body -->
<body class="login_back">
    <div class="container">
        <form method="POST" action="<?php echo web_url(); ?>/admin/verify">
        <div class="col-lg-12">
            <p class="title_welcome">Welcome to</p>
            <h1 class="title_up"><?= Config::get('app.website_title') ?></h1>
        </div>
        <div class="col-lg-12">
            <p class="logo_img_login"><img src="<?php echo asset_url(); ?>/uploads/<?php echo $logo;?>"></p>
        </div>
        <div class="col-lg-12 ">
            <div class="col-lg-4 align_center">
            </div>
            <div class="col-lg-4 align_center">
                <i class="icon-user"></i>
                <input  name="username" type="text" placeholder="Username / email" data-parsley-errors-container="#error-container" data-parsley-error-message="Please fill in your username / email" data-parsley-required>
            </div>
            <div class="col-lg-4 align_center">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-4 align_center">
            </div>
            <div class="col-lg-4 align_center">
                <i class="icon-lock"></i>
                <input class="form-control" name="password" type="password" placeholder="Password" data-parsley-errors-container="#error-container" data-parsley-error-message="Please fill in your password" data-parsley-required>
            </div>
            <div class="col-lg-4 align_center">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-4 align_center">
            </div>
            <div class="col-lg-4 align_center">
                <div class="col-xs-6 no_space">
                    <div class="checkbox custom-checkbox">
                        <input id="remember" type="checkbox" value="1" name="remember">
                        <!--
                        <label for="remember" style="color: #FFF;"> &nbsp;Remember me</label>
                        -->
                    </div>
                </div>
                <!--<div class="col-xs-6 no_space text-right">
                    <a href="javascript:void(0);" class="no_link">Lost password?</a>
                </div>-->
            </div>
            <div class="col-lg-4 align_center">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="col-lg-4 align_center">
            </div>
            <div class="col-lg-4 align_center">
                <div class="col-xs-4 no_space">
                </div>
                <div class="col-xs-4 no_space">
                    <div class="checkbox custom-checkbox">
                        <button class="btn btn-block btn-green"  type="submit">
                            <font size="5"><b><?= $button ?></b></font>
                        </button>
                    </div>
                </div>
                <div class="col-xs-4 no_space">
                </div>
            </div>
            <div class="col-lg-4 align_center">
            </div>
        </div>
    </form>
</div>

        <!-- START JAVASCRIPT SECTION (Load javascripts at bottom to reduce load time) -->
<!-- Library script : mandatory -->

<!--/ Library script -->


<!--/ App and page level scrip -->
<!--/ END JAVASCRIPT SECTION -->
<?php
if($error) { ?>
<script type="text/javascript">
    alert('Invalid Username and Password');
</script>
<?php } ?>
</body>
<!--/ END Body -->
</html>