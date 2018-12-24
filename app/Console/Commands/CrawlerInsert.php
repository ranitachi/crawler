<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\CrawlToolController;
class CrawlerInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawlerinsert {idorder} {bln} {thn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command CrawlerInsert';

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
        $bln = $this->argument('bln');
        $thn = $this->argument('thn');
        // $coba=$controller->coba($idorder,$thn,$bln,$tgl);
        $coba=$controller->simpandatabase($idorder,$bln,$thn);
    }
}
