@extends('website.layout')

@section('body')
<?php $theme = Theme::all();?>
<div class="row first-fold">
    <div class="landing">
        <div class="row uber-logo second-uber-logo">
        <?php foreach($theme as $themes) {?>
            <div class="col-md-1 col-xs-12"><img src="<?php echo web_url(); ?>/uploads/<?= $themes->logo;?>" alt=""></div>
            <?php }?>
            <div class="col-md-5 col-md-offset-6 col-xs-12">
                <ul class="inline">
                    <li><a href="#">Home</a> | </li>
                    <li><a href="<?php echo web_url(); ?>/user/signin">Log in</a> | </li>
                    <li><a href="<?php echo web_url(); ?>/user/signup">Sign Up</a> </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row buttons" style="background:transparent;">
        <div class="col-md-4 col-md-offset-4">
            <div class="row">
                <div class="col-md-6 col-xs-6 locate-button">
                    <a href="<?php echo web_url(); ?>/user/signup" >

                    <button id="user" class="btn btn-blue">Sign up as a User</button>
                    </a>
                </div>
                <div class="col-md-6 col-xs-6 send-here-button">
                    <a href="<?php echo web_url(); ?>/provider/signup" >

                    <button id="provider" class="btn btn-blue">Sign up as a Provider</button>

                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row map-wrapper">
        <div id="map" class="map">

        </div>
    </div>
</div>
<div class="row uber-footer">
    <div class="col-md-6 col-md-offset-3">
        <div class="row">
            <div class="col-md-12">
                <h3>Say Hi, Get In Touch</h3>
            </div>
        </div>
        <div class="row social-icons">
            <ul class="col-md-12 icons text-center">
                <li><a href="#"><i class="icon-facebook"></i></a></li>
                <li><a href="#"><i class="icon-twitter"></i></a></li>
                <li><a href="#"><i class="icon-google-plus"></i></a></li>
            </ul>
        </div>
        <div class="row">
            <p> Copyright ProvenLogic. All Rights Reserved.</p>
        </div>
    </div>
</div>

@stop
