<?php
Route::get('/', function (){
    return 'welcome to oa system';
});
/*
 * OA system Web 无需登录
 */
Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\AuthController@getLogin');
    Route::post('login', 'Auth\AuthController@postLogin');
    Route::get('logout', 'Auth\AuthController@getLogout');
    Route::get('register', 'Auth\AuthController@getRegister');
    Route::post('register', 'Auth\AuthController@postRegister');
});

/*
 * OA system Web 登陆后使用
 */
//Auth::loginUsingId(1);
Route::group(['middleware' => 'auth'], function () {
    //Oauth
    Route::group(['prefix' => 'oauth'], function () {
        Route::get('oauth_client/code', 'OauthController@getCode');
        Route::get('oauth_client/user', 'OauthController@getByUser');
        Route::get('oauth_client/create', 'OauthController@create');
        Route::post('oauth_client', 'OauthController@store');
        Route::group(['middleware' => 'webCheckAdmin'], function () {
            Route::resource('oauth_client', 'OauthController', ['only' => [
                'index', 'destroy', 'update',
            ]]);
        });
        Route::group(['middleware' => ['check-authorization-params']], function () {
            Route::post('authorize', ['as' => 'oauth.authorize.post', function () {
                $params = Authorizer::getAuthCodeRequestParams();
                $params['user_id'] = Auth::user()->id;
                $redirectUri = '/';
                if (Request::has('approve')) {
                    $redirectUri = Authorizer::issueAuthCode('user', $params['user_id'], $params);
                }
                if (Request::has('deny')) {
                    $redirectUri = Authorizer::authCodeRequestDeniedRedirectUri();
                }

                return Redirect::to($redirectUri);
            }]);
        });
    });

    //User
    Route::group(['prefix' => 'user'], function () {
        Route::get('info', 'UserController@info');
        Route::group(['prefix' => 'password'], function () {
            Route::get('email', 'Auth\PasswordController@getEmail');
            Route::post('email', 'Auth\PasswordController@postEmail');
            Route::get('reset/{token}', 'Auth\PasswordController@getReset');
            Route::post('reset', 'Auth\PasswordController@postReset');
        });
    });
});


/*
 * OASystem API
 */
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers', 'prefix' => 'v1'], function ($api) {
        //获取access_token
        $api->post('oauth/access_token', function () {
            return Response::json(Authorizer::issueAccessToken());
        });

        /*
         * 需要access_token
         */
        $api->group(['middleware' => 'oauth'], function ($api) {
            $api->post('user/register', 'AuthController@register');
            $api->post('user/login', 'AuthController@authenticate');
            /*
             * 需登录后使用，携带token
             */
            $api->group(['middleware' => ['jwt.auth']], function ($api) {
                //user
                $api->get('user/confirm', 'AuthController@smsConfirm');
                $api->post('user/confirm', 'AuthController@smsCheck');
                $api->get('user/me', 'AuthController@getAuthenticatedUser');
                //organization tag
                $api->get('organization_tags', 'OrganizationTagsController@index');
                $api->get('organization_tags/{id}', 'OrganizationTagsController@show');
                //organization
            });
        });
    });
});
