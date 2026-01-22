<?php

use Illuminate\Database\Seeder;

class ReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('no_order_reasons')->insert([
            'noorderreason' => "Product related issue",
            'created_at' => '2022-04-27 05:08:43',
            'updated_at' => '2022-04-27 05:08:43',
            ]);
            DB::table('no_order_reasons')->insert([
                'noorderreason' => "Distributor related issue",
                'created_at' => '2022-04-27 05:08:43',
            'updated_at' => '2022-04-27 05:08:43',
                ]);
                DB::table('no_order_reasons')->insert([
                    'noorderreason' => "Competitor related issue",
                    'created_at' => '2022-04-27 05:08:43',
                'updated_at' => '2022-04-27 05:08:43',
                    ]);
                    DB::table('no_order_reasons')->insert([
                        'noorderreason' => "Company related issue",
                        'created_at' => '2022-04-27 05:08:43',
                    'updated_at' => '2022-04-27 05:08:43',
                        ]);
                        DB::table('no_order_reasons')->insert([
                            'noorderreason' => "Shop related issue",
                            'created_at' => '2022-04-27 05:08:43',
                        'updated_at' => '2022-04-27 05:08:43',
                            ]);
                            DB::table('no_order_reasons')->insert([
                                'noorderreason' => "More Factor",
                                'created_at' => '2022-04-27 05:08:43',
                            'updated_at' => '2022-04-27 05:08:43',
                                ]);
                             
    }
}
