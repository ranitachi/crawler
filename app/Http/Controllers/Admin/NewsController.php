<?php

namespace App\Http\Controllers\Admin;

use App\Business\NewsBusiness;
use App\Commons\UploadFileImage;
use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\BeritaCrawler;
use App\Models\Order;
use App\Models\Kategori;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\BeritaResult;
use App\Business\AuthorBusiness;
use Illuminate\Support\Facades\Redirect;
use Yangqi\Htmldom\Htmldom;
use Scrapper;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Sunra\PhpSimple\HtmlDomParser;
// use Pagination;
class NewsController extends Controller
{
    private $model;

    private  $newsBusiness;

    /**
     * @var array
     */
    private  $dataInput = array();

    /**
     * @param authorBusiness $authorBusiness
     */
    function __construct(NewsBusiness $newsBusiness) {
        $this->newsBusiness = $newsBusiness;
        $this->model = new News();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $viewData = $this->newsBusiness->getAll();
        $order=Order::all();
        $kategori=Kategori::all();
        $provinsi=Provinsi::all();
        if(isset($request->bln))
        {
            $thn=$request->thn;
            $bln=($request->bln <10) ? ('0'.$request->bln) : $request->bln;
            
            if(isset($request->key))
                $data=BeritaCrawler::where('tanggal','like',"%$thn-$bln%")->where('judul','like',"%$request->key%")->orderBy('tanggal')->paginate(20);
            else
                $data=BeritaCrawler::where('tanggal','like',"%$thn-$bln%")->orderBy('tanggal')->paginate(20);
        }
        else
        {
            if(isset($request->key))
                $data=BeritaCrawler::where('judul','like',"%$request->key%")->orderBy('tanggal')->paginate(20);
            else
                $data=BeritaCrawler::orderBy('tanggal')->paginate(20);
        }

        if ($request->ajax()) {
            return view('protected.admin.news.data', ['data' => $data])->render();  
        }
        // $viewData = BeritaCrawler
        // return view('protected.admin.news.index', compact('viewData'));
        return view('protected.admin.news.crawler', compact('viewData','order','kategori','provinsi','data'));
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $this->model->find($id)->delete();
    }

    public function data($date,$portal_id)
    {
        list($bln,$thn)=explode('-',$date);
        $date=date('Y-m',strtotime($thn.'-'.$bln));
        // echo $date;
        if($portal_id==-1)
            $data=BeritaCrawler::where('tanggal','like',"%$date%")->with('portal')->get();
        else
            $data=BeritaCrawler::where('portal_id',$portal_id)->where('tanggal','like',"%$date%")->with('portal')->get();
        // dd($data);
        return view('protected.admin.news.data', compact('portal_id', 'date', 'data'));
    }
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
    public function get_konten($id)
    {
        $d=BeritaCrawler::find($id);
        $order=Order::find($d->portal_id);
        $data['url']=$d->url;
        $data['tag_body']=$div_conten=$order->tag_body;
        $data['portal']=$order->name;
        $isi=$d->isi;
        
        if($isi !='' && $isi !='-')
        {    
            $dom = HtmlDomParser::str_get_html($isi);
            $konten = trim(preg_replace('/\t+/', '',$dom->find($div_conten,0)->text()));
            // $konten='--';
        }
        else
        {
            $getisi=$this->get_isi($d->url,$d->portal_id);
            $konten=trim(preg_replace('/\t+/', '',$getisi->text()));
        }

        // echo ($konten->text());
        
        $data['konten']=$konten;
        $data['judul']=$d->judul;
        return $data;
    }

    public function proses_berita(Request $request)
    {
        // dd($request->all());
        $insert=new BeritaResult;
        $insert->id_berita = $request->id_berita;
        $insert->kategori = $request->kategori;
        $insert->provinsi = $request->provinsi;
        $insert->kabupaten = $request->kabupaten;
        $insert->lokasi = $request->lokasi;
        $insert->tanggal_kejadian = $request->tanggal_kejadian;
        $insert->meninggal = $request->korban_meninggal;
        $insert->luka = $request->korban_luka;
        $insert->bangunan_rusak = $request->bangunan_rusak;
        $insert->url_berita = $request->url_berita;
        $insert->judul = $request->judul;
        $insert->save();
        return redirect('admin/news')->with('cari','');
    }

    public function get_result(Request $request)
    {
        $viewData = $this->newsBusiness->getAll();
        $order=Order::all();
        $kategori=Kategori::all();
        $provinsi=Provinsi::all();
        if(isset($request->bln))
        {
            $thn=$request->thn;
            $bln=($request->bln <10) ? ('0'.$request->bln) : $request->bln;
            
            if(isset($request->key))
                $data=BeritaResult::where('tanggal_kejadian','like',"%$thn-$bln%")->where('judul','like',"%$request->key%")->with('berita')->with('jnskategori')->with('provinsi')->with('kabupaten')->orderBy('tanggal_kejadian')->paginate(20);
            else
                $data=BeritaResult::where('tanggal_kejadian','like',"%$thn-$bln%")->with('berita')->with('jnskategori')->with('provinsi')->with('kabupaten')->orderBy('tanggal_kejadian')->paginate(20);
        }
        else
        {
            if(isset($request->key))
                $data=BeritaResult::where('judul','like',"%$request->key%")->with('berita')->with('jnskategori')->with('provinsi')->with('kabupaten')->orderBy('tanggal_kejadian')->paginate(20);
            else
                $data=BeritaResult::with('berita')->with('jnskategori')->with('provinsi')->with('kabupaten')->orderBy('tanggal_kejadian')->paginate(20);
        }

        if ($request->ajax()) {
            return view('protected.admin.news.result-data', ['data' => $data])->render();  
        }
        return view('protected.admin.news.result', compact('viewData','order','kategori','provinsi','data'));
    }
}
