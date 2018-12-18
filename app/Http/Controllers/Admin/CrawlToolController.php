<?php

namespace App\Http\Controllers\Admin;


use App\Commons\Common;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\BeritaCrawler;
use App\Models\PagingSetting;
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
use Sunra\PhpSimple\HtmlDomParser;
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
        $dateformat=array('yyyy-mm-dd','yyyy/mm/dd','dd/mm/yyyy','dd-mm-yyyy','mm-dd-yyyy','mm/dd/yyyy');
        return view('protected.admin.tool.create', compact('tables', 'settings', 'tags','dateformat'));
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
    public function store(Request $request) 
    {
        $id_order=$request->id_order;
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
        $time['tgl']=$tgl=$request->tanggal;
        $time['bln']=$bln=$request->bulan;
        $time['thn']=$thn=$request->tahun;
        if($request->tanggal==0)
        {
            $jlhhari=jumlahhari($bln,$thn);
            for($xx=1;$xx<=$jlhhari;$xx++)
            {
                
                $tgl=$xx;
                // if(date('n')==$bln && date('d')<$xx)
                // {
                //     // dd(date('d')<$xx);
                //     echo $tgl.'-';
                //     break;
                // }
                
                
                if(strpos($url,'jpnn')!==false)
                {
                    $date='&d='.$tgl.'&m='.$bln.'&y='.$thn;
                }
                else
                {
                    $df=str_replace('yyyy','Y',$request->date_format);
                    $df=str_replace('mm','m',$df);
                    $df=str_replace('dd','d',$df);
                    $date=date($df,strtotime($thn.'-'.$bln.'-'.$tgl));
                }
                // echo $date;
                
                $link=$url.$date;
                // echo $link;
                $tag_parent=$tag_child=$tag_paging='';

                // dd('-');
                foreach($tags as $k=>$tag)
                {
                    if($k!=100)
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
                        $tag_parent.=$tag.$sep2.' > ';
                    }
                    else
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

                        $tag_paging.=$tag.$sep2. ' > ';
                    }
             
                }
                $tag_parent=substr($tag_parent,0,-2);
                $tag_paging=substr($tag_paging,0,-2);
                $page_url=$request->input('url-paging');
              
                if(strpos($link,'jpnn')!==false)
                {
                    $client = new Client();
                    $crawler_b = $client->request('GET', $link);
                    $response_b = $client->getResponse();
                    $isi = $response_b->getContent();
                    $ee=HtmlDomParser::str_get_html($isi);
                    $ff=$ee->find($tag_parent);
                    
                    $data=$dd=$this->getjpnn(90,$tgl,$bln,$thn);
                    // dd($dd);

                    $data=array();
                    $idx=0;
                    foreach($dd['judul'] as $k=>$v)
                    {
                        $data['judul'][$idx]=$v;
                        $data['link_berita'][$idx]=$dd['link_berita'][$k];
                        $idx++;
                    }
                    foreach($ff as $e=>$f)
                    {
                        $data['judul'][$idx]=$f->title;
                        $data['link_berita'][$idx]=$f->href;
                 
                        $idx++;
                    }
                    // dd($data);
                    foreach($data['link_berita'] as $kd=>$vd)
                    {

                        $isi = $this->get_isi($vd,$id_order);
                        // dd($isi);
                        // $isi=$body
                        $judul = $data['judul'][$kd];
                        $link_berita = $vd;

                        $insert=new BeritaCrawler;
                        $insert->portal_id=$request->setting;
                        $insert->url=$link_berita;
                        $insert->file='';
                        $insert->isi=$isi;
                        $insert->tanggal=($request->tahun.'-'.$request->bulan.'-'.$tgl);
                        $insert->judul=$judul;
                        $insert->save();
                        
                    }
                }
                else
                {
                    $data_crawler=array();
                    
                    $crawler = Scrapper::request('GET', $link);
                    $response = Scrapper::getResponse();
                    
                        if($response->getStatus()==200)
                        {
                            $page_url=$link.$page_url;
                            $data_craw = $crawler->filter($tag_paging)->each(function($node) use ($request,$tgl,&$x,&$y) {
                                $title = $node->extract(array('_text','href','title'));
                                $x=$title[0][1];
                                $y[]=$title[0][1];
                            });
                            
                            if(strpos($page_url,'detik.com')!==false)
                            {
                                $ln=$y[count($y)-2];
                                $bef=strtok($ln,'?');
                                $bf=explode('/',$bef);
                                $jlh_page=$bf[count($bf)-1];
                  
                            }
                            elseif(strpos($page_url,'jpnn')!==false || strpos($page_url,'tempo')!==false || strpos($page_url,'metrotvnews')!==false)
                            {
                                $jlh_page=1;
                            }
                            else
                            {

                                $jlh_page=str_replace($page_url,' ',$x);
                            }
                            
                            for($ix=1;$ix<=$jlh_page;$ix++)
                            {   
                                // echo $ix;
                                if(strpos($page_url,'detik.com')!==false)
                                {
                                    $cc=substr($page_url,0,-1);
                                    echo str_replace('?',('/'.$ix.'?'),$cc).'<br>';
                                    $link_detik=str_replace('?',('/'.$ix.'?'),$cc);
                                    $crawler_page = Scrapper::request('GET', $link_detik);
                                }
                                elseif(strpos($page_url,'jpnn')!==false || strpos($page_url,'tempo')!==false || strpos($page_url,'metrotvnews')!==false)
                                {
                                    // echo $link;
                                    $crawler_page = Scrapper::request('GET', $link);
                                }
                                else
                                {
                                    // echo $page_url.$ix.'<br>';
                                    $crawler_page = Scrapper::request('GET', $page_url.$ix);
                                }
                                //echo $tag_parent;
                                $data_craw = $crawler_page->filter($tag_parent)->each(function($node) use ($request,$tgl,$page_url) {
                                    $title = $node->extract(array('_text','href','title'));
                                    

                                    if(strpos($page_url,'tempo')!==false)
                                    {
                                        $node->filter('h2.title')->each(function ($nd) use (&$title_) {
                                            $title_= $nd->text();
                                        });
                                        $data['judul2']=$judul2=trim(preg_replace('/\t+/', '',$title_));
                                        $data['judul']=$judul=trim(preg_replace('/\t+/', '',$title[0][0]));
                                        $data['link_berita']=$link_berita=$title[0][1];
                                    }
                                    else
                                    {
                                        $data['judul2']=$judul2=trim(preg_replace('/\t+/', '',$title[0][2]));
                                        $data['judul']=$judul=trim(preg_replace('/\t+/', '',$title[0][0]));
                                        $data['link_berita']=$link_berita=$title[0][1];
                                    }
                                    
                                    
                                    $cek=BeritaCrawler::where('url',$link_berita)->first();
                                    if(is_null($cek))
                                    {
                                            // $client = new Client();
                                            // $crawler_b = $client->request('GET', $link_berita);
                                            // $response_b = $client->getResponse();
                                            // $code=$response_b->getStatus();
                                            
                                            // $isi = $response_b->getContent();
                                            // // $isi=$body
                                            // if($code==200)
                                            // {
                                                echo $tgl.'-<br>';
                                                $isi=$this->get_isi($link_berita,$id_order);
                                                $insert=new BeritaCrawler;
                                                $insert->portal_id=$request->setting;
                                                $insert->url=$link_berita;
                                                $insert->file='';
                                                $insert->isi=$isi;
                                                $insert->tanggal=($request->tahun.'-'.$request->bulan.'-'.$tgl);
                                                if(strpos($page_url,'jpnn')!==false || strpos($page_url,'tempo')!==false)
                                                {
                                                    $insert->judul=$judul2;
                                                }
                                                else
                                                {
                                                    $insert->judul=$judul;
                                                }
                                                $insert->save();
                                            // }
                                        // }
                                    }
                                    
                                });
                            }
                        
                        }
                        $pesan='Crawler Telah Di Lakukan';  
                    // }
                }

               
            }
             $vd=BeritaCrawler::where('portal_id',$setting)->where('tanggal','like',"%$request->tahun-$request->bulan%")->get();
        }
        else
        {
            if(strpos($url,'jpnn')!==false)
            {
                $date='&d='.$tgl.'&m='.$bln.'&y='.$thn;
            }
            else
            {
                $df=str_replace('yyyy','Y',$request->date_format);
                $df=str_replace('mm','m',$df);
                $df=str_replace('dd','d',$df);
                $date=date($df,strtotime($thn.'-'.$bln.'-'.$tgl));
            }
            
            $link=$url.$date;
            $tag_parent=$tag_child=$tag_paging='';

            foreach($tags as $k=>$tag)
            {
                if($k!=100)
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

                    $tag_parent.=$tag.$sep2.' > ';
                }
                else
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

                    $tag_paging.=$tag.$sep2. ' > ';
                }
            }
            $tag_parent=substr($tag_parent,0,-2);
            $tag_paging=substr($tag_paging,0,-2);
            $page_url=$request->input('url-paging');

            if(strpos($link,'jpnn')!==false)
            {
                $client = new Client();
                $crawler_b = $client->request('GET', $link);
                $response_b = $client->getResponse();
                $isi = $response_b->getContent();
                $ee=HtmlDomParser::str_get_html($isi);
                $ff=$ee->find($tag_parent);
                
                $data=$dd=$this->getjpnn(90,$tgl,$bln,$thn);
                // dd($dd);

                $data=array();
                $idx=0;
                foreach($dd['judul'] as $k=>$v)
                {
                    $data['judul'][$idx]=$v;
                    $data['link_berita'][$idx]=$dd['link_berita'][$k];
                    $idx++;
                }
                foreach($ff as $e=>$f)
                {
                    $data['judul'][$idx]=$f->title;
                    $data['link_berita'][$idx]=$f->href;
                    // echo $f->href.'<br>'.$f->title;
                    // echo '<br>';
                    $idx++;
                }
                // dd($data);
                foreach($data['link_berita'] as $kd=>$vd)
                {

                    $isi = $this->get_isi($vd,$id_order);
                    // dd($isi);
                    // $isi=$body
                    $judul = $data['judul'][$kd];
                    $link_berita = $vd;

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
            else
            {
                $data_crawler=array();
                
                $crawler = Scrapper::request('GET', $link);
                $response = Scrapper::getResponse();
                
                    if($response->getStatus()==200)
                    {
                        // dd($crawler);
                        // echo $link.$page_url.'<br>';
                        $page_url=$link.$page_url;
                        $data_craw = $crawler->filter($tag_paging)->each(function($node) use ($request,&$x,&$y) {
                            $title = $node->extract(array('_text','href','title'));
                            $x=$title[0][1];
                            $y[]=$title[0][1];
                            // echo '<pre>';
                            // print_r($title);
                            // echo '</pre>';
                        });
                        
                        if(strpos($page_url,'detik.com')!==false)
                        {
                            // $jlh_page=str_replace($page_url,' ',$x);
                            $ln=$y[count($y)-2];
                            $bef=strtok($ln,'?');
                            $bf=explode('/',$bef);
                            $jlh_page=$bf[count($bf)-1];
                            // echo $ln;
                            // dd($jlh_page);

                        }
                        elseif(strpos($page_url,'jpnn')!==false || strpos($page_url,'tempo')!==false || strpos($page_url,'metrotvnews')!==false)
                        {
                            $jlh_page=1;
                        }
                        else
                        {

                            $jlh_page=str_replace($page_url,' ',$x);
                            // echo '<br>'.$page_url.'<br>'.$jlh_page;        
                        }
                        
                        for($ix=1;$ix<=$jlh_page;$ix++)
                        {   
                            // echo $ix;
                            if(strpos($page_url,'detik.com')!==false)
                            {
                                $cc=substr($page_url,0,-1);
                                echo str_replace('?',('/'.$ix.'?'),$cc).'<br>';
                                $link_detik=str_replace('?',('/'.$ix.'?'),$cc);
                                $crawler_page = Scrapper::request('GET', $link_detik);
                            }
                            elseif(strpos($page_url,'jpnn')!==false || strpos($page_url,'tempo')!==false || strpos($page_url,'metrotvnews')!==false)
                            {
                                // echo $link;
                                $crawler_page = Scrapper::request('GET', $link);
                            }
                            else
                            {
                                // echo $page_url.$ix.'<br>';
                                $crawler_page = Scrapper::request('GET', $page_url.$ix);
                            }
                            echo $tag_parent;
                            $data_craw = $crawler_page->filter($tag_parent)->each(function($node) use ($request,$page_url,$id_order) {
                                $title = $node->extract(array('_text','href','title'));
                                

                                if(strpos($page_url,'tempo')!==false)
                                {
                                    // $title_=$node->filter('h2.title')->extract(array('_text','href','title'))?;
                                    // print_r($title_);
                                    $node->filter('h2.title')->each(function ($nd) use (&$title_) {
                                        $title_= $nd->text();
                                    });
                                    $data['judul2']=$judul2=trim(preg_replace('/\t+/', '',$title_));
                                    $data['judul']=$judul=trim(preg_replace('/\t+/', '',$title[0][0]));
                                    $data['link_berita']=$link_berita=$title[0][1];
                                }
                                else
                                {
                                    $data['judul2']=$judul2=trim(preg_replace('/\t+/', '',$title[0][2]));
                                    $data['judul']=$judul=trim(preg_replace('/\t+/', '',$title[0][0]));
                                    $data['link_berita']=$link_berita=$title[0][1];
                                }
                                
                                
                                $cek=BeritaCrawler::where('url',$link_berita)->first();
                                if(is_null($cek))
                                {
                                    // $isi=file_get_contents($link_berita);
                                    // if($judul2!='')
                                    // {
                                        // $client = new Client();
                                        // $crawler_b = $client->request('GET', $link_berita);
                                        // $response_b = $client->getResponse();
                                        // $code=$response_b->getStatus();
                                        
                                        // $isi = $response_b->getContent();
                                        // $isi=$body
                                        // if($code==200)
                                        // {
                                            $isi=$this->get_isi($link_berita,$id_order);

                                            $insert=new BeritaCrawler;
                                            $insert->portal_id=$request->setting;
                                            $insert->url=$link_berita;
                                            $insert->file='';
                                            $insert->isi=$isi;
                                            $insert->tanggal=($request->tahun.'-'.$request->bulan.'-'.$request->tanggal);
                                            if(strpos($page_url,'jpnn')!==false || strpos($page_url,'tempo')!==false)
                                            {
                                                $insert->judul=$judul2;
                                            }
                                            else
                                            {
                                                $insert->judul=$judul;
                                            }
                                            $insert->save();
                                        // }
                                    // }
                                }
                                // echo '<pre>';
                                // print_r($data);
                                // echo '</pre>';
                                
                            });
                        }
                    
                    }
                    $pesan='Crawler Telah Di Lakukan';  
                // }
            }
            // dd('-');

        }
        $vd=BeritaCrawler::where('portal_id',$setting)->where('tanggal',($request->tahun.'-'.$request->bulan.'-'.$request->tanggal))->get();
        $no=0;
        $data=array();
        foreach($vd as $k=>$v)
        {
            $data[$no]['judul']=$v->judul;
            $data[$no]['link']=$v->url;
            $no++;
        }
        $keys=['Judul','Link'];
        
        return Redirect::to('admin/tool')->with(['success' => trans('message.SUCCESS'), 'viewData' => $data, 'keys' => $keys]);
    }
    // public function store(Request $request) {
    //     // dd($request->all());
    //     $id_order=$request->id_order;
    //     $table = $request->tables;
    //     $url = $request->url;
    //     $tags = $request->tags;
    //     $htmls = $request->htmls;
    //     $hid_fields = $request->hid_fields;
    //     $depths = $request->depths;
    //     $types = $request->types;
    //     $setting = $request->setting;
    //     $data = array();
    //     $arrDepths = $this->divDepth($depths);
    //     $time['tgl']=$tgl=$request->tanggal;
    //     $time['bln']=$bln=$request->bulan;
    //     $time['thn']=$thn=$request->tahun;
        

    //     if($tgl==0)
    //     {
    //         $jlhhari=jumlahhari($bln,$thn);
    //         for($h=1;$h<=$jlhhari;$h++)
    //         {

    //         }
    //     }
    //     else
    //     {
    //         if(strpos($url,'jpnn')!==false)
    //         {
    //             $date='&d='.$tgl.'&m='.$bln.'&y='.$thn;
    //         }
    //         else
    //         {
    //             $df=str_replace('yyyy','Y',$request->date_format);
    //             $df=str_replace('mm','m',$df);
    //             $df=str_replace('dd','d',$df);
    //             $date=date($df,strtotime($thn.'-'.$bln.'-'.$tgl));
    //         }
    //         $link=$url.$date;
    //         $tag_parent=$tag_child=$tag_paging='';
    //         foreach($tags as $k=>$tag)
    //         {
    //             if($k!=100)
    //             {

    //                 if(strpos($htmls[$k],'class')!==false)
    //                 {
    //                     $sep='.';
    //                     $sep2=str_replace('class="','.',$htmls[$k]);
    //                     $sep2=str_replace('"','',$sep2);
    //                     $sep2=str_replace(' ','.',$sep2);
    //                 }
    //                 elseif(strpos($htmls[$k],'id')!==false)
    //                 {
    //                     $sep='#';
    //                     $sep2=str_replace('id="','#',$htmls[$k]);
    //                     $sep2=str_replace('"','',$sep2);
    //                     $sep2=str_replace(' ','.',$sep2);
    //                 }
    //                 else
    //                     $sep=$sep2='';

    //                 $tag_parent.=$tag.$sep2.' > ';
    //             }
    //             else
    //             {
    //                 if(strpos($htmls[$k],'class')!==false)
    //                 {
    //                     $sep='.';
    //                     $sep2=str_replace('class="','.',$htmls[$k]);
    //                     $sep2=str_replace('"','',$sep2);
    //                     $sep2=str_replace(' ','.',$sep2);
    //                 }
    //                 elseif(strpos($htmls[$k],'id')!==false)
    //                 {
    //                     $sep='#';
    //                     $sep2=str_replace('id="','#',$htmls[$k]);
    //                     $sep2=str_replace('"','',$sep2);
    //                     $sep2=str_replace(' ','.',$sep2);
    //                 }
    //                 else
    //                     $sep=$sep2='';

    //                 $tag_paging.=$tag.$sep2. ' > ';
    //             }
    //         }
    //         $tag_parent=substr($tag_parent,0,-2);
    //         $tag_paging=substr($tag_paging,0,-2);
    //         $page_url=$request->input('url-paging');
    //         if(strpos($link,'jpnn')!==false)
    //         {
    //             $client = new Client();
    //             $crawler_b = $client->request('GET', $link);
    //             $response_b = $client->getResponse();
    //             $isi = $response_b->getContent();
    //             $ee=HtmlDomParser::str_get_html($isi);
    //             $ff=$ee->find($tag_parent);
                
    //             $data=$dd=$this->getjpnn(90,$tgl,$bln,$thn);
            
    //             $data=array();
    //             $idx=0;
    //             foreach($dd['judul'] as $k=>$v)
    //             {
    //                 $data['judul'][$idx]=$v;
    //                 $data['link_berita'][$idx]=$dd['link_berita'][$k];
    //                 $idx++;
    //             }
    //             foreach($ff as $e=>$f)
    //             {
    //                 $data['judul'][$idx]=$f->title;
    //                 $data['link_berita'][$idx]=$f->href;
    //                 $idx++;
    //             }
                
    //             foreach($data['link_berita'] as $kd=>$vd)
    //             {

    //                 $isi = $this->get_isi($vd,$id_order);
    //                 $judul = $data['judul'][$kd];
    //                 $link_berita = $vd;

    //                 $insert=new BeritaCrawler;
    //                 $insert->portal_id=$request->setting;
    //                 $insert->url=$link_berita;
    //                 $insert->file='';
    //                 $insert->isi=$isi;
    //                 $insert->tanggal=($request->tahun.'-'.$request->bulan.'-'.$request->tanggal);
    //                 $insert->judul=$judul;
    //                 $insert->save();      
    //             }
    //         }
    //         else
    //         {
    //             echo $tag_parent;
    //             $tg=explode('>',$tag_parent);
    //             $tag_new=$tg[count($tg)-2].' '.$tg[count($tg)-1];
    //             $client = new Client();
    //             $crawler_b = $client->request('GET', $link);
    //             $response_b = $client->getResponse();
    //             $isi = $response_b->getContent();
    //             $ee=HtmlDomParser::str_get_html($isi);
                
    //             // $ff=$ee->find('h3.f16 a');
    //             $ff=$ee->find($tag_new);
    //             $idx=0;
    //             foreach($ff as $e=>$f)
    //             {
    //                 // var_dump($f);
    //                 $judul=trim(preg_replace('/(\v|\s)+/', ' ', $f->plaintext));
    //                 if($judul!='')
    //                 {
    //                     $data['judul'][$idx]=$judul;
    //                     $data['link_berita'][$idx]=$f->href;
    //                     // $isi=$this->get_isi($f->href,$id_order);
    //                     // foreach($isi as $is=>$vis)
    //                     // {
    //                     //     echo print_r($vis).'<br>';
    //                     // }
    //                     // $data['isi'][$idx]=$isi->text();
    //                     $idx++;
    //                 }
    //                 // break;
    //             }

    //             dd($data);
    //         }
    //     }
       
    //     // $no=0;
    //     // $data=array();
    //     // foreach($vd as $k=>$v)
    //     // {
    //     //     $data[$no]['judul']=$v->judul;
    //     //     $data[$no]['link']=$v->url;
    //     //     $no++;
    //     // }
    //     // $keys=['Judul','Link'];
        
    //     // return Redirect::to('admin/tool')->with(['success' => trans('message.SUCCESS'), 'viewData' => $data, 'keys' => $keys]);
    //     // return Redirect::to('admin/tool')->with(['success' => trans('message.SUCCESS'), 'viewData' => $viewData]);
    // }

    public function get_isi($link,$order_id)
    {
        $order=Order::find($order_id);
        $div_conten=$order->tag_body;

        $client = new Client();
        $crawler_b = $client->request('GET', $link);
        $response_b = $client->getResponse();
        $code=$response_b->getStatus();
        $isi = $response_b->getContent();

        $dom = HtmlDomParser::str_get_html($isi);
        $konten = $dom->find($div_conten,0);

        return $konten;
    }

    public function getjpnn($offset,$tgl,$bln,$thn)
    {
        $i=$offset/10;

        $client = new Client();
        $crawler = $client->request('GET', 'https://www.jpnn.com/indeks?id=&d=10&m=11&y=2016&tab=all');
        $data=array();
        for($j=1;$j<=$i;$j++)
        {
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
            $craw = $client->submit($form, array('offset' => ($j*10), 'tab' => 'all','d'=>$tgl,'m'=>$bln,'y'=>$thn));
            $html_craw= $craw->html();
            $crawler_new = $client->request('GET', $html_craw);
            $dom = HtmlDomParser::str_get_html($html_craw);
            $d=$dom->find('li > a');
            foreach($d as $k => $v)
            {
                // $title=$v->str_get();
                // echo $v->href.'<br>'.$v->title.'<br>';
                // echo '--------------------------<br>';
                $data['judul'][]=$v->title;
                $data['link_berita'][]=$v->href;
            }
        }
        return $data;
    }

    /**
     * @param $datas
     * @return array|int
     */

    

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
    // public function updateSetting($id) {
    //     $req=Session::get('request');
    //     dd($req);
    // }
    
    public function saveSetting(Request $request) {

        // 
        // $idorder=$request->id_order;
        

        $data = $request->data;
        $idorder=$data['id_order'];
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
        // echo $idorder.'-';
        // dd($idorder);
        // foreach($tags as $key => $tag) {
        //     if($key==100)
        //     {
        //         echo 'Paging :'.$key.'=>'.$tag.'<br>';
        //     }
        //     else
        //     {
        //         echo 'Tag :'.$key.'=>'.$tag.'<br>';
        //     }
        // }
        // dd($tags);
        //save data into table order
        
        if($idorder!=-1)
        {
            $order = Order::find($idorder);
            $order->table = $table;
            $order->url = $url;
            $order->url_paging = $data['url-paging'];
            $order->date_format = $data['date_format'];
            $order->tag_body = $data['tag_content'];
            $order->name = $sName;
            $order->save();

            PagingSetting::where('order_id',$idorder)->forceDelete();
            Setting::where('order_id',$idorder)->forceDelete();
        }
        else
        {
            $order = new Order();
            $order->table = $table;
            $order->url = $url;
            $order->url_paging = $data['url-paging'];
            $order->date_format = $data['date_format'];
            $order->tag_body = $data['tag_content'];
            $order->name = $sName;
            $order->save();
        }


        //save data into table settings
        $isSave = false;
        foreach($tags as $key => $tag) {
            if($key==100)
            {
                // echo 'Paging :'.$key.'=>'.$tag.'<br>';
                $page_setting=new PagingSetting;
                $page_setting->order_id=$order->id;
                $page_setting->tag=$tag;
                $page_setting->name=$key;
                $page_setting->html=isset($htmls[$key]) ? $htmls[$key] : '';
                $page_setting->count_of_page=0;
                $isSave=$page_setting->save();
            }
            else
            {
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
    
    public function loadSettingPaging(Request $request) {
        $order_id = $request->order;
        $order = Order::find($order_id);
        $settings = $order->pagingsetting;
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
    public function loadPagingSettingItem(Request $request) {
        $id = $request->id;
        $setting = PagingSetting::find($id);
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