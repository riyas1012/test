<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/test', 'HelloController@test');

Route::post('/dog/addschedule', 'DogController@add_schedule');

Route::post('/dog/cancelschedule', 'DogController@cancel_schedule');

Route::get('/dog/getwalkers','DogController@get_walkers');

Route::post('/dog/assignwalker','DogController@assign_walker');

Route::get('/walk/walkinprogress', 'DogController@walkinprogress');
	
Route::get('/walk/nonreviewedwalks', 'DogController@nonreviewedwalks');

Route::get('/walks', 'DogController@get_walks');

Route::post('/walk/walksummary', 'WalkerController@walk_summary');

Route::post('/walk/photo', 'WalkerController@upload_photo');

Route::post('/walk/video', 'WalkerController@upload_video');

Route::get('/walker/walks', 'WalkerController@get_walks');

Route::get('/walker/details', 'WalkerController@get_details');

Route::post('/walker/cancelwalk', 'WalkerController@cancel_walk');

Route::post('/walker/getwalk', 'WalkerController@walk_details');

Route::post('/walker/getschedule', 'WalkerController@get_schedule');


// On Demand API's


// Owner APIs

Route::post('/user/login', 'OwnerController@login');

Route::any('/user/register', 'OwnerController@register');

Route::post('/user/verification', 'OwnerController@verification_code');

Route::post('/user/location', 'DogController@set_location');

Route::any('/user/details', 'OwnerController@details');
	
Route::post('/user/addcardtoken', 'OwnerController@addcardtoken');

Route::get('/user/braintreekey', 'OwnerController@get_braintree_token');

Route::post('/user/deletecardtoken', 'OwnerController@deletecardtoken');

Route::post('/user/update', 'OwnerController@update_profile');

Route::get('/user', 'OwnerController@getProfile');

Route::any('/user/thing', 'DogController@create');

Route::post('/user/updatething', 'DogController@update_thing');

Route::post('/user/createrequest', 'DogController@create_request');

Route::post('/user/getproviders', 'DogController@get_providers');

Route::post('/user/createrequestproviders', 'DogController@create_request_providers');

Route::post('/user/cancellation', 'DogController@cancellation');

Route::any('/user/getrequest', 'DogController@get_request');

Route::post('/user/cancelrequest', 'DogController@cancel_request');

Route::get('/server/schedulerequest', 'DogController@schedule_request');

Route::get('/user/getrequestlocation', 'DogController@get_request_location');

Route::post('/user/rating', 'DogController@set_walker_rating');

Route::get('/user/requestinprogress', 'DogController@request_in_progress');

Route::get('/user/requestpath', 'DogController@get_walk_location');

Route::get('/provider/requestpath', 'WalkerController@get_walk_location');

Route::post('/user/referral', 'OwnerController@set_referral_code');

Route::get('/user/referral', 'OwnerController@get_referral_code');

Route::post('/user/apply-referral', 'OwnerController@apply_referral_code');

Route::get('/user/cards', 'OwnerController@get_cards');

Route::get('/user/history', 'OwnerController@get_completed_requests');

// Walker APIs

Route::get('/provider/getrequests', 'WalkerController@get_requests');

Route::get('/provider/getrequest', 'WalkerController@get_request');

Route::post('/provider/respondrequest', 'WalkerController@respond_request');

Route::post('/provider/location', 'WalkerController@walker_location');

Route::post('/provider/requestwalkerstarted', 'WalkerController@request_walker_started');

Route::post('/provider/requestwalkerarrived', 'WalkerController@request_walker_arrived');

Route::post('/provider/customercame', 'WalkerController@customer_came');

Route::post('/provider/customernotcame', 'WalkerController@customer_not_came');

Route::post('/provider/requestwalkstarted', 'WalkerController@request_walk_started');

Route::post('/request/location', 'WalkerController@walk_location');

Route::post('/provider/requestwalkcompleted', 'WalkerController@request_walk_completed');

Route::post('/provider/rating', 'WalkerController@set_dog_rating');

Route::post('/provider/login', 'WalkerController@login');

Route::post('/provider/register', 'WalkerController@register');

Route::post('/provider/verification', 'WalkerController@verification_code');

Route::post('/provider/update', 'WalkerController@update_profile');

Route::post('/provider_services/update', 'WalkerController@provider_services_update');

Route::get('/provider/services_details', 'WalkerController@services_details');

Route::get('/provider/requestinprogress', 'WalkerController@request_in_progress');

Route::get('/provider/checkstate','WalkerController@check_state');

Route::post('/provider/togglestate','WalkerController@toggle_state');

Route::get('/provider/history', 'WalkerController@get_completed_requests');

// Info Page API

Route::get('/application/pages', 'ApplicationController@pages');

Route::get('/application/types', 'ApplicationController@types');

Route::get('/application/page/{id}', 'ApplicationController@get_page');

Route::post('/application/forgot-password', 'ApplicationController@forgot_password');

// Admin Panel

Route::get('/admin/report', 'AdminController@report');

Route::get('/admin/map_view', 'AdminController@map_view');

Route::get('/admin/providers', 'AdminController@walkers');

Route::get('/admin/users', 'AdminController@owners');

Route::get('/admin/requests', 'AdminController@walks');

Route::get('/admin/reviews', 'AdminController@reviews');

Route::get('/admin/reviews/delete/{id}', 'AdminController@delete_review');

Route::get('/admin/search', 'AdminController@search');

Route::get('/admin/login', 'AdminController@login');

Route::post('/admin/verify', 'AdminController@verify');

Route::get('/admin/logout', 'AdminController@logout');


Route::get('/admin/admins', 'AdminController@admins');

Route::get('/admin/add_admin', 'AdminController@add_admin');

Route::post('/admin/admins/add', 'AdminController@add_admin_do');

Route::get('/admin/admins/edit/{id}', 'AdminController@edit_admins');

Route::post('/admin/admins/update', 'AdminController@update_admin');

Route::get('/admin/admins/delete/{id}', 'AdminController@delete_admin');



Route::get('/admin', 'AdminController@index');

Route::get('/admin/add', 'AdminController@add');



Route::get('/admin/provider/edit/{id}', 'AdminController@edit_walker');

Route::get('/admin/provider/add', 'AdminController@add_walker');

Route::post('/admin/provider/update', 'AdminController@update_walker');

Route::get('/admin/provider/history/{id}', 'AdminController@walker_history');

Route::get('/admin/provider/requests/{id}', 'AdminController@walker_upcoming_walks');

Route::get('/admin/provider/decline/{id}', 'AdminController@decline_walker');

Route::get('/admin/provider/approve/{id}', 'AdminController@approve_walker');

Route::get('/admin/providers_xml', 'AdminController@walkers_xml');


Route::get('/admin/user/edit/{id}', 'AdminController@edit_owner');

Route::post('/admin/user/update', 'AdminController@update_owner');

Route::get('/admin/user/history/{id}', 'AdminController@owner_history');

Route::get('/admin/user/requests/{id}', 'AdminController@owner_upcoming_walks');

Route::get('/admin/request/decline/{id}', 'AdminController@decline_walk');

Route::get('/admin/request/approve/{id}', 'AdminController@approve_walk');

Route::get('/admin/request/map/{id}', 'AdminController@view_map');

Route::get('/admin/request/change_provider/{id}', 'AdminController@change_walker');

Route::get('/admin/request/alternative_providers_xml/{id}', 'AdminController@alternative_walkers_xml');

Route::post('/admin/request/change_provider', 'AdminController@save_changed_walker');

Route::post('/admin/request/pay_provider', 'AdminController@pay_walker');

Route::get('/admin/settings', 'AdminController@get_settings');

Route::post('/admin/theme', 'AdminController@theme');

Route::post('/admin/settings', 'AdminController@save_settings');

Route::get('/admin/informations', 'AdminController@get_info_pages');

Route::get('/admin/information/edit/{id}', 'AdminController@edit_info_page');

Route::post('/admin/information/update', 'AdminController@update_info_page');

Route::get('/admin/information/delete/{id}', 'AdminController@delete_info_page');

Route::get('/admin/provider-types', 'AdminController@get_provider_types');

Route::get('/admin/provider-type/edit/{id}', 'AdminController@edit_provider_type');

Route::post('/admin/provider-type/update', 'AdminController@update_provider_type');

Route::get('/admin/provider-type/delete/{id}', 'AdminController@delete_provider_type');

Route::get('/admin/document-types', 'AdminController@get_document_types');

Route::get('/admin/document-type/edit/{id}', 'AdminController@edit_document_type');

Route::post('/admin/document-type/update', 'AdminController@update_document_type');

Route::get('/admin/document-type/delete/{id}', 'AdminController@delete_document_type');


Route::get('/admin/provider/banking/{id}', 'AdminController@banking_provider');


Route::post('/admin/provider/providerB_bankingSubmit', 'AdminController@providerB_bankingSubmit');

Route::post('/admin/provider/providerS_bankingSubmit', 'AdminController@providerS_bankingSubmit');


//Admin Panel Sorting 

Route::get('/admin/sortur', array('as' => '/admin/sortur', 'uses' => 'AdminController@sortur'));	

Route::get('/admin/sortpv', array('as' => '/admin/sortpv', 'uses' => 'AdminController@sortpv'));

Route::get('/admin/sortpvtype', array('as' => '/admin/sortpvtype', 'uses' => 'AdminController@sortpvtype'));	

Route::get('/admin/sortreq', array('as' => '/admin/sortreq', 'uses' => 'AdminController@sortreq'));

//Provider Availability

Route::get('/admin/provider/allow_availability', 'AdminController@allow_availability');

Route::get('/admin/provider/disable_availability', 'AdminController@disable_availability');

Route::get('/admin/provider/availability/{id}', 'AdminController@availability_provider');

Route::post('/admin/provider/availabilitySubmit/{id}','AdminController@provideravailabilitySubmit');

//Providers Who currently walking

Route::get('/admin/provider/current', 'AdminController@current');


// Web User

Route::any('/', 'WebController@index');

Route::get('/user/signin', 'WebUserController@userLogin');

Route::get('/user/signup', 'WebUserController@userRegister');

Route::post('/user/save', 'WebUserController@userSave');

Route::post('/user/forgot-password', 'WebUserController@userForgotPassword');

Route::get('/user/logout', 'WebUserController@userLogout');

Route::post('/user/verify', 'WebUserController@userVerify');

Route::get('/user/trips', 'WebUserController@userTrips');

Route::get('/user/trip/status/{id}', 'WebUserController@userTripStatus');


Route::get('/user/trip/cancel/{id}', 'WebUserController@userTripCancel');


Route::get('/user/request-trip', 'WebUserController@userRequestTrip');

Route::post('/user/request-trip', 'WebUserController@saveUserRequestTrip');

Route::post('/user/post-review', 'WebUserController@saveUserReview');

Route::get('/user/profile', 'WebUserController@userProfile');

Route::get('/user/payments', 'WebUserController@userPayments');


Route::post('/user/payments', 'WebUserController@saveUserPayment');


Route::get('/user/payment/delete/{id}', 'WebUserController@deleteUserPayment');

Route::post('/user/update_profile', 'WebUserController@updateUserProfile');

Route::post('/user/update_password', 'WebUserController@updateUserPassword');

Route::post('/user/update_code', 'WebUserController@updateUserCode');

Route::get('/user/trip/{id}', 'WebUserController@userTripDetail');


// Search Admin Panel
Route::get('/admin/searchpv', array('as' => '/admin/searchpv', 'uses' => 'AdminController@searchpv'));
Route::get('/admin/searchur', array('as' => '/admin/searchur', 'uses' => 'AdminController@searchur'));
Route::get('/admin/searchreq', array('as' => '/admin/searchreq', 'uses' => 'AdminController@searchreq'));
Route::get('/admin/searchrev', array('as' => '/admin/searchrev', 'uses' => 'AdminController@searchrev'));
Route::get('/admin/searchinfo', array('as' => '/admin/searchinfo', 'uses' => 'AdminController@searchinfo'));
Route::get('/admin/searchpvtype', array('as' => '/admin/searchpvtype', 'uses' => 'AdminController@searchpvtype'));
Route::get('/admin/searchdoc', array('as' => '/admin/searchdoc', 'uses' => 'AdminController@searchdoc'));


// Web Provider

Route::get('/provider/signin', 'WebProviderController@providerLogin');

Route::get('/provider/signup', 'WebProviderController@providerRegister');

Route::post('/provider/save', 'WebProviderController@providerSave');

Route::post('/provider/forgot-password', 'WebProviderController@providerForgotPassword');

Route::get('/provider/logout', 'WebProviderController@providerLogout');

Route::post('/provider/verify', 'WebProviderController@providerVerify');

Route::get('/provider/trips', 'WebProviderController@providerTrips');

Route::get('/provider/trip/{id}', 'WebProviderController@providerTripDetail');

Route::get('/provider/trip/changestate/{id}', 'WebProviderController@providerTripChangeState');

Route::get('/provider/tripinprogress', 'WebProviderController@providerTripInProgress');

Route::get('/provider/profile', 'WebProviderController@providerProfile');

Route::post('/provider/update_profile', 'WebProviderController@updateProviderProfile');

Route::post('/provider/update_password', 'WebProviderController@updateProviderPassword');

Route::get('/provider/documents', 'WebProviderController@providerDocuments');

Route::post('/provider/update_documents', 'WebProviderController@providerUpdateDocuments');

Route::get('/provider/request', 'WebProviderController@providerRequestPing');

Route::get('/provider/request/decline/{id}', 'WebProviderController@decline_request');

Route::get('/provider/request/accept/{id}', 'WebProviderController@approve_request');

Route::any('/provider/availability/toggle', 'WebProviderController@toggle_availability');

Route::any('/provider/location/set', 'WebProviderController@set_location');


// Installer

Route::any('/install', 'InstallerController@install');

Route::get('/install/complete', 'InstallerController@finish_install');