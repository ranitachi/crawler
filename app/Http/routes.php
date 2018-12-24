<?php
    use JonnyW\PhantomJs\Client; 
    # Static Pages. Redirecting admin so admin cannot access these pages.
    Route::group(['middleware' => ['redirectAdmin']], function()
    {
        Route::get('/', ['as' => 'home', 'uses' => 'PagesController@getHome']);
        Route::get('/product/{id}/{slug}', ['as' => 'product.detail', 'uses' => 'PagesController@getDetailProduct']);
        Route::get('/filter/{listID}', ['as' => 'filter.ajax', 'uses' => 'PagesController@getProductListFilterAjax']);
        Route::get('/category/{id}/{slug}', ['as' => 'category.detail', 'uses' => 'PagesController@getDetailCategory']);
        Route::get('/search', ['as' => 'search', 'uses' => 'PagesController@getResultSearch']);
    });

    # Registration
    Route::group(['middleware' => 'guest'], function()
    {
        Route::get('register', 'RegistrationController@create');
        Route::post('register', ['as' => 'registration.store', 'uses' => 'RegistrationController@store']);
    });

    # Authentication
    Route::get('login', ['as' => 'login', 'middleware' => 'guest', 'uses' => 'SessionsController@create']);
    Route::get('logout', ['as' => 'logout', 'uses' => 'SessionsController@destroy']);
    Route::resource('sessions', 'SessionsController' , ['only' => ['create','store','destroy']]);

    # Forgotten Passwordjhh
    Route::group(['middleware' => 'guest'], function()
    {
        Route::get('forgot_password', 'Auth\PasswordController@getEmail');
        Route::post('forgot_password','Auth\PasswordController@postEmail');
        Route::get('reset_password/{token}', 'Auth\PasswordController@getReset');
        Route::post('reset_password/{token}', 'Auth\PasswordController@postReset');
    });

    # Standard User Routes
    Route::group(['middleware' => ['auth','standardUser']], function()
    {
        Route::get('userProtected', 'StandardUser\StandardUserController@getUserProtected');
        Route::resource('profiles', 'StandardUser\UsersController', ['only' => ['show', 'edit', 'update']]);
    });

    # Admin Routes
    Route::group(['middleware' => ['auth', 'admin']], function()
    {
        Route::group(['prefix' => 'admin'], function() {
            Route::get('/', ['as' => 'admin_dashboard', 'uses' => 'Admin\AdminController@getHome']);

            #Kategori Manage
            Route::resource('kategori', 'Admin\KategoriController');
            Route::post('kategori/destroy', ['as' => 'kategori/destroy', 'uses' => 'Admin\KategoriController@destroy']);
            
            #news Manage
            Route::resource('news', 'Admin\NewsController');
            Route::post('news/destroy', ['as' => 'news/destroy', 'uses' => 'Admin\NewsController@destroy']);
            Route::get('/data/{date}/{portal}', 'Admin\NewsController@data');
            Route::get('/get-konten/{id}', 'Admin\NewsController@get_konten');
            Route::post('/proses-berita', 'Admin\NewsController@proses_berita');
            Route::get('/resultnew', 'Admin\NewsController@get_result');

            #crawl tool
            Route::get('/tool', 'Admin\CrawlToolController@index');
            Route::post('/add-form-setting', ['as' => 'add-form-setting', 'uses' => 'Admin\CrawlToolController@addFormSetting']);
            Route::post('/get-table-field', ['as' => 'get-table-field', 'uses' => 'Admin\CrawlToolController@getTableField']);
            Route::post('/tool', ['as' => 'admin.tool.store', 'uses' => 'Admin\CrawlToolController@store']);
            Route::post('/save-data', ['as' => 'admin.tool.save', 'uses' => 'Admin\CrawlToolController@saveData']);

            Route::post('/save-setting', ['as' => 'save-setting', 'uses' => 'Admin\CrawlToolController@saveSetting']);
            Route::post('/load-setting', ['as' => 'load-setting', 'uses' => 'Admin\CrawlToolController@loadSetting']);
            Route::post('/load-setting-item', ['as' => 'load-setting-item', 'uses' => 'Admin\CrawlToolController@loadSettingItem']);
            
            Route::post('/load-setting-paging', ['as' => 'load-setting', 'uses' => 'Admin\CrawlToolController@loadSettingPaging']);
            Route::post('/load-paging-setting-item', ['as' => 'load-setting-item', 'uses' => 'Admin\CrawlToolController@loadPagingSettingItem']);
            
            Route::get('updateSetting/{id}', 'Admin\CrawlToolController@updateSetting');
            
            Route::post('/check-name', ['as' => 'check-name', 'uses' => 'Admin\CrawlToolController@checkName']);
        });
    });
Route::get('kabupaten-by/{idprov}','PagesController@kabupaten_by');
Route::get('scrapper-js', function() {
    $client = Client::getInstance();
    // $client->setTempDir("storage/framework/cache");
    // $client->getProcedureCompiler()->disableCache();
    $link = 'https://www.jpnn.com/indeks?tab=all&d=1&m=1&y=2015';
    $request = $client->getMessageFactory()->createRequest($link, 'GET');
    $response = $client->getMessageFactory()->createResponse();
        
    $client->send($request, $response);
    
    if($response->getStatus() === 200) {
        echo $response->getContent();
    }
});
Route::get('simpancrawl/{idorder}/{tgl}/{bln}/{thn}', 'Admin\CrawlToolController@simpancrawl');
Route::get('simpandatabase/{idorder}/{bln}/{thn}', 'Admin\CrawlToolController@simpandatabase');
Route::get('getjpnn/{offset}/{tgl}/{bln}/{thn}', 'Admin\CrawlToolController@getjpnn');
Route::get('/foo', function()
{
    $exitCode = Artisan::call('command:name', ['--option' => 'foo']);
 
});
Route::get('scrapper', function() {
//   $crawler = Scrapper::request('GET', 'http://malesbanget.com/');
    // $crawler = Scrapper::request('GET', 'https://news.detik.com/indeks/all?date=01%2F01%2F2015');
    // $crawler = Scrapper::request('GET', 'https://news.detik.com/indeks/all?date=11%2F01%2F2018');
    // dd($crawler->getInternalResponse());
    // $crawler = Scrapper::request('GET', 'https://tirto.id/indeks?date=2017-01-01');
    $client = new Goutte\Client();
    $crawler = $client->request('GET', 'https://www.jpnn.com/indeks?id=&d=10&m=11&y=2016&tab=all');
    $html = '<form action="https://www.jpnn.com/ajax/loadmore_indeks" method="POST">';
    $html .='<input type="hidden" name="offset">';
    $html .='<input type="hidden" name="tab">';
    $html .='<input type="hidden" name="d">';
    $html .='<input type="hidden" name="m">';
    $html .='<input type="hidden" name="y">';
    $html .='<input type="submit" name="load" id="load">';
    $html .='</form>';
    $crawler->add($html);
    $form = $crawler->selectButton('load')->form();
    $craw = $client->submit($form, array('offset' => '150', 'tab' => 'all','d'=>1,'m'=>1,'y'=>2015));
    $html_craw= $craw->html();
    
    $crawler_new = $client->request('GET', $html_craw);
    // dd($crawler_new);
    // $url = $crawler_new->filter('li > a')->each(function($node) {
    //     $title = $node->extract(array('_text','href','title'));
    // });
    $dom = SimpleHtmlDom\str_get_html($html_craw);
    $d=$dom->find('li > a');
    foreach($d as $k => $v)
    {
        // $title=$v->str_get();
        echo $v->href.'<br>'.$v->title.'<br>';
        echo '--------------------------<br>';
    }
    // echo '<pre>';
    // var_dump($d);
    // echo '</pre>';

    // $crawler = Scrapper::request('GET', 'https://www.jpnn.com/indeks?id=&d=10&m=11&y=2016&tab=all');
    // // $response = $client->getResponse();
    // // echo $response->getStatus();
    // $url = $crawler->filter('ul.loadmore > li > a')
    //                       ->each(function($node) {
    // // $url = $crawler->filter('ul#indeks-container > li > article > div.desc_idx.ml10 > a')
    //                     //   ->each(function($node) {
        
    
    //     $title = $node->extract(array('_text','href','title'));
    
    //     echo '<pre>';
    //     var_dump($title);
    //     echo '</pre>';
    // // // $img = $node->filter('figure.image img')->attr('src');
    // // // echo $img.'<br>';
    // // // dd($img);
    // // // return [
    // // //     'title' => $title[0][0],
    // // //     'link' => $title[0][1],
    // // //     // 'image' => $img,
    // // // ];
    // });
    // // echo '<pre>';
    // // var_dump($url);
    // // echo '</pre>';
});