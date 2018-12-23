<?php

namespace App\Console\Commands;
// namespace App\Http\Controllers\Admin;
use Illuminate\Console\Command;
use App\User;
// use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\CrawlToolController;
class Crawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler {idorder} {tgl} {bln} {thn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command Cralwer description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $controller = new CrawlToolController(); // make sure to import the controller
        $idorder = $this->argument('idorder');
        $tgl = $this->argument('tgl');
        $bln = $this->argument('bln');
        $thn = $this->argument('thn');
        // $coba=$controller->coba($idorder,$thn,$bln,$tgl);
        $coba=$controller->simpancrawl($idorder,$tgl,$bln,$thn);
        

        // echo $coba;
        // return $userId;
    }
}
