<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;
// use DB;
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $kat=array(
        //     'konflik-Konflik Antar Warga',
        //     'konflik-Konflik Antar Agama',
        //     'konflik-Konflik Antar Etnis',
        //     'konflik-Konflik Antar Kelompok',
        //     'kebakaran-Kebakaran Rumah/Pemukiman',
        //     'kebakaran-Kebakaran Lahan/Hutan',
        //     'teroris-Teror',
        //     'kerusuhan-Kerusuhan Sosial/Huru Hara'
        // );
        // // $this->command->info(count($kat));
        // foreach($kat as $k=>$v)
        // {
        //     list($jen,$kt)=explode('-',$v);
        //     $insert=new Kategori;
        //     $insert->kategori=$kt;
        //     $insert->jenis=$jen;
        //     $insert->save();
        //     // $this->command->info($v);
        // }

        // Eloquent::unguard()
        $path = storage_path('app/db_indonesia.sql');
        $this->command->info($path);
        DB::unprepared(file_get_contents($path));

        


        // $this->call('SentryGroupSeeder');
        // $this->call('SentryUserSeeder');
        // $this->call('SentryUserGroupSeeder');

        $this->command->info('All tables seeded!');

        Model::reguard();
    }
}
