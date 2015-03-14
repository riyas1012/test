<?php

class WebController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 *
	 * @return Response
	 */

	public function __construct()
    {
        if (Config::get('app.production'))
		{
		    echo "Something cool is going to be here soon.";
		    die();
		}

    }

	public function index()
	{
		return View::make('website.index');
	}



}