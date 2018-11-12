<?php

namespace App\Http\Controllers\Admin;


use App\Commons\Common;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\BeritaCrawler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Yangqi\Htmldom\Htmldom;
use Scrapper;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
/**
 * Class CrawlToolController
 * @package App\Http\Controllers\Admin
 */
class CrawlToolController extends Controller
{

    /**
     * show tool craweler
     */
    public function index() {

        $tables = $this->getTables();
        $settings = $this->getSetting();
        $tags = Common::$TAGS;
        return view('protected.admin.tool.create', compact('tables', 'settings', 'tags'));
    }

    /**
     * @return array
     */
    public function getTables() {
        $tables = DB::select('SHOW TABLES');
        $tableList = array("" => "select one",'news'=>'news','berita_crawler'=>'Berita Crawler');
        $ignoreTables = array('migrations', 'groups', 'password_resets', 'throttle', 'users', 'users_groups', 'orders', 'settings');
        foreach($tables as $tab) {
            // if(in_array($tab->Tables_in_buy_theme, $ignoreTables)) {
            //     continue;
            // }

            // $tableList[$tab->Tables_in_buy_theme] = $tab->Tables_in_buy_theme;
        }

        return $tableList;
    }

    /**
     * @return array
     */
    public function getSetting() {
        $orders = Order::all();
        $orderList = array("" => "select one");
        foreach($orders as $od) {
            $orderList[$od->id] = $od->name;
        }

        return $orderList;
    }

    /**
     * add form setting
     */
    public function addFormSetting(Request $request) {
        $id = $request->id;
        $arrElm = explode('_', $id);
        $margin = count($arrElm);
        $tags = Common::$TAGS;
        $types = Common::$TYPES;
        return view('protected.admin.tool.form-setting', compact('id', 'margin', 'tags', 'types'));
    }

    /**
     * get table field
     */
    public function getTableField(Request $request) {
        $tableName = $request->tableName;
        $columns = Schema::getColumnListing($tableName);
        $ignoreField = array('id', 'created_at', 'updated_at');
        $fields = array("" => "select one");
        foreach($columns as $col) {
            if(in_array($col, $ignoreField)) {
                continue;
            }

            $fields[$col] = $col;
        }

        return view('protected.admin.tool.select-box', compact('fields'));
    }

    /**
     * @param Request $request
     */
    public function store(Request $request) {
        $table = $request->tables;
        $url = $request->url;
        $tags = $request->tags;
        $htmls = $request->htmls;
        $hid_fields = $request->hid_fields;
        $depths = $request->depths;
        $types = $request->types;
        $setting = $request->setting;
        $data = array();
        $arrDepths = $this->divDepth($depths);
        $tgl=$request->tanggal;
        $bln=$request->bulan;
        $thn=$request->tahun;
        $date=date('Y-m-d',strtotime($thn.'-'.$bln.'-'.$tgl));
        // echo $date;
        
        $link=$url.$date;
        // echo $link;
        $tag_parent=$tag_child='';
        foreach($tags as $k=>$tag)
        {
            if(strpos($htmls[$k],'class')!==false)
            {
                $sep='.';
                $sep2=str_replace('class="','.',$htmls[$k]);
                $sep2=str_replace('"','',$sep2);
                $sep2=str_replace(' ','.',$sep2);
            }
            elseif(strpos($htmls[$k],'id')!==false)
            {
                $sep='#';
                $sep2=str_replace('id="','#',$htmls[$k]);
                $sep2=str_replace('"','',$sep2);
                $sep2=str_replace(' ','.',$sep2);
            }
            else
                $sep=$sep2='';

            // echo $k.'-';
            // if($k=='0')
            // {
                $tag_parent.=$tag.$sep2.' > ';
            // }
            // else
            // {
            //     if($htmls[$k]!='')
            //     {
            //         $tag_child.=$tag.$sep2.' ';
            //     }
            //     else
            //     {
            //         $tag_child.=$tag.' ';
            //     }
            // }
        }
        $tag_parent=substr($tag_parent,0,-2);
        // echo $tag_parent;
        // echo '<br>'.$tag_child;
        // dd($request->all());
        // $crawler = Scrapper::request('GET', 'https://indeks.kompas.com/news/2015-01-01');
        // $url = $crawler->filter('div.article__list')->each(function($node) {
        $crawler = Scrapper::request('GET', $link);
        $response = Scrapper::getResponse();
        // echo $response->getStatus();
        
        if($response->getStatus()==200)
        {
            $data = $crawler->filter($tag_parent)->each(function($node) use ($request) {

                $title = $node->extract(array('_text','href','title'));
    
                echo '<pre>';
                var_dump($title);
                echo '</pre>';
                // $title = $node->filter('div.article__list__title h3  a')->extract(array('_text', 'href'));
                /*$title = $node->filter($tag_child)->extract(array('_text', 'href'));
                if(count($title)!=0)
                {
                // trim(preg_replace('/\t+/', '', $string));
                // dd($title);
                    $data['judul']=$judul=trim(preg_replace('/\t+/', '',$title[0][0]));
                    $data['link_berita']=$link_berita=$title[0][1];
                    
                    $cek=BeritaCrawler::where('url',$link_berita)->first();
                    if(is_null($cek))
                    {
                        // $isi=file_get_contents($link_berita);
                        $client = new Client();
                        $crawler_b = $client->request('GET', $link_berita);
                        $response_b = $client->getResponse();
                        $code=$response_b->getStatus();
                        
                        $isi = $response_b->getContent();
                        // $isi=$body
                        if($code==200)
                        {
                            $insert=new BeritaCrawler;
                            $insert->portal_id=$request->setting;
                            $insert->url=$link_berita;
                            $insert->file='';
                            $insert->isi=$isi;
                            $insert->tanggal=($request->tahun.'-'.$request->bulan.'-'.$request->tanggal);
                            $insert->judul=$judul;
                            $insert->save();
                        }
                    }
                // echo $request->setting;
                // echo $link_berita;
                // echo '<hr>';
                }*/

                
            });
            $pesan='Crawler Telah Di Lakukan';
        }
        else
        {
            echo 'Fail Connect To Server';
            $pesan='Fail Connect To Server';
        }
        // $vd=BeritaCrawler::where('portal_id',$setting)->where('tanggal',($request->tahun.'-'.$request->bulan.'-'.$request->tanggal))->get();
        // echo $vd->count();
        // Start clone content
        // $page = new Htmldom($url);

        // foreach($arrDepths as $depth) {
        //     $data = $this->lastValue($data, $page, $depth, $tags, $htmls, $types, $hid_fields, 0);
        // }

        // Session::put('dataSave', $data);
        // Session::put('table', $table);
        // $keys = array_keys($data);
        // $viewData = $this->convertData($data);
        /*$no=0;
        $data=array();
        foreach($vd as $k=>$v)
        {
            $data[$no]['judul']=$v->judul;
            $data[$no]['link']=$v->url;
            $no++;
        }
        $keys=['Judul','Link'];
        
        return Redirect::to('admin/tool')->with(['success' => trans('message.SUCCESS'), 'viewData' => $data, 'keys' => $keys]);*/
        // return Redirect::to('admin/tool')->with(['success' => trans('message.SUCCESS'), 'viewData' => $viewData]);
    }

    /**
     * @param $datas
     * @return array|int
     */

    public function data($date,$portal_id)
    {
        $data=BeritaCrawler::where('portal_id',$portal_id)->where('tanggal','like',"%$date")->get();
        return view('protected.admin.tool.data', compact('portal_id', 'date', 'data'));
    }

    public function convertData($datas) {
        $keys = array_keys($datas);
        if(count($keys) < 1) {
            return 0;
        }

        $results = array();
        for($i = 0; $i < count($datas[$keys[0]]); $i ++) {
            $arrTmp = array();
            foreach($keys as $key) {
                $arrTmp[$key][] = $datas[$key][$i];
            }

            $results[] = $arrTmp;
        }
        return $results;
    }

    /**
     * save data into table
     * @param $data
     */
    public function saveData() {
        $table = Session::get('table');
        $data  = Session::get('dataSave');
        $count = 0;
        $modelName = ucfirst(str_singular($table));
        $modelClass = "App\Models\\" . $modelName;
        $keys = array_keys($data);
        if(count($keys) < 1) {
            return 0;
        }
        for($i = 0; $i < count($data[$keys[0]]); $i ++) {
            $model = new $modelClass;
            foreach($keys as $key) {
                $model->$key = $data[$key][$i];
            }
            if($model->save()) {
                $count++;
            }
        }

        // unset session after save
        Session::forget('table');
        Session::forget('dataSave');

        return $count;
    }

    /**
     * @param $data
     * @param $page
     * @param $depths
     * @param $tags
     * @param $htmls
     * @param $types
     * @param $hid_fields
     * @param int $count
     * @return mixed
     */
    public function lastValue($data, $page, $depths, $tags, $htmls, $types, $hid_fields, $count) {
        $length = count($depths);
        $attr = $this->axtractOneAttribute($htmls[$depths[$count]]);
        $tag = $attr != "" ? $tags[$depths[$count]] . '[' . $attr . ']' : $tags[$depths[$count]];
        $type = $count > 0 ? $types[$depths[$count]] : '';
        $hid_field = $count > 0 ? $hid_fields[$depths[$count]] : '';

        foreach ($page->find($tag) as $item) {
            if($type == '1') {
                // get text
                $data[$hid_field][] = $item->plaintext;
            }

            if($type == '2') {
                //upload image
                $data[$hid_field][] = $item->src;
            }

            if($type == '3') {
                //get link
                $data[$hid_field][] = $item->href;
            }

            if($count < $length - 1) {
                $count ++;
                $data = $this->lastValue($data, $item, $depths, $tags, $htmls, $types, $hid_fields, $count);
                $count --;
            }
        }

        return $data;

    }

    /**
     * @param $attr
     * @return string
     */
    public function axtractOneAttribute($attr) {
        $explodeAttr = explode('=', $attr);
        if(!isset($explodeAttr[1])) {
            return '';
        }
        $explodeValue = explode(' ', trim($explodeAttr[1], '"'));
        return $explodeAttr[0] . '="' . $explodeValue[0] . '"';
    }

    /**
     * @param $depths
     * @return array
     */
    public function divDepth($depths) {
        $arrDepths = array();
        $parentKey = 0;
        $count = 0;
        foreach($depths as $key => $depth) {
            $arrTmp = explode('_', $depth);
            if(count($arrTmp) > $count) {
                $arrDepths[$parentKey][] = $depth;
                $count = count($arrTmp);
            } else {
                $parentKey ++;
                for($i = 0; $i< count($arrTmp) - 1; $i ++) {
                    $arrDepths[$parentKey][] = $depths[$i];
                }
                $arrDepths[$parentKey][] = $depth;
                $count = count($arrTmp);
            }
        }
        return $arrDepths;
    }

    /**
     * @param Request $request
     */
    public function saveSetting(Request $request) {
        $data = $request->data;
        $table = $data['tables'];
        $url = $data['url'];
        $sName = $data['sName'];
        $setting = $data['setting'];

        $tags = array();
        $htmls = array();
        $types = array();
        $depths = array();
        $hid_fields = array();

        //unset data not an array
        unset($data['_token']);
        unset($data['tables']);
        unset($data['url']);
        unset($data['sName']);
        unset($data['setting']);

        // convert data for each array
        foreach ($data as $key => $item) {
            $arrExplodeKey = explode('[', $key);
            if(count($arrExplodeKey) <= 1) {
                continue;
            }
            $arrExplodeValue = explode(']', $arrExplodeKey[1]);
            switch($arrExplodeKey[0]) {
                case 'tags' :
                    $tags[$arrExplodeValue[0]] = $item;
                    break;

                case 'htmls' :
                    $htmls[$arrExplodeValue[0]] = $item;
                    break;

                case 'types' :
                    $types[$arrExplodeValue[0]] = $item;
                    break;

                case 'depths' :
                    $depths[$arrExplodeValue[0]] = $item;
                    break;

                case 'hid_fields' :
                    $hid_fields[$arrExplodeValue[0]] = $item;
                    break;
            }
        }

        //save data into table order
        $order = new Order();
        $order->table = $table;
        $order->url = $url;
        $order->name = $sName;
        $order->save();

        //save data into table settings
        $isSave = false;
        foreach($tags as $key => $tag) {
            $setting = new Setting();
            $setting->order_id = $order->id;
            $setting->parent_id = $this->getParentID($key);
            $setting->tag = $tag;
            $setting->name = $key;
            $setting->html = isset($htmls[$key]) ? $htmls[$key] : '';
            $setting->type = isset($types[$key]) ? $types[$key] : 0;
            $setting->field = isset($hid_fields[$key]) ? $hid_fields[$key] : '';
            $isSave = $setting->save();
        }

        return Response::json($isSave);
    }

    /**
     * @param $name
     * @return int
     */
    public function getParentID($name) {
        $arrTmp = explode('_', $name);
        if(count($arrTmp) < 2) {
            return 0;
        }

        array_pop($arrTmp);
        $parentName = implode('_', $arrTmp);
        $parentID = Setting::where('name', $parentName)->first();
        return $parentID->id;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function loadSetting(Request $request) {
        $order_id = $request->order;
        $order = Order::find($order_id);
        $settings = $order->setting;
        return Response::json([
            'order' => $order,
            'setting' => $settings
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function loadSettingItem(Request $request) {
        $id = $request->id;
        $setting = Setting::find($id);
        $arrElm = explode('_', $setting->name);
        $margin = count($arrElm);
        $tags = Common::$TAGS;
        $types = Common::$TYPES;
        return view('protected.admin.tool.form-load-setting', compact('setting', 'margin', 'tags', 'types'));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function checkName(Request $request) {
        $name = $request->name;
        $isDuplicate = false;
        $count = Order::where('name', $name)->count();
        if($count > 0) {
            $isDuplicate = true;
        }

        return Response::json($isDuplicate);
    }
} //class