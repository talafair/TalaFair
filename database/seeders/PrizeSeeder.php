<?php

namespace Database\Seeders;

use App\Models\Prize;
use Illuminate\Database\Seeder;

class PrizeSeeder extends Seeder
{
    public function run(): void
    {
        $prizes = [
            ['label' => 'Rice 5kg',      'prize_type' => 'foods',       'amount' => 0, 'color' => '#9ACD32'],
            ['label' => '₱500 Cash',     'prize_type' => 'cash',        'amount' => 0, 'color' => '#FBC02D'],
            ['label' => 'Grocery Pack',  'prize_type' => 'essentials',  'amount' => 0, 'color' => '#7CB342'],
            ['label' => 'Try Again',     'prize_type' => 'none',        'amount' => 0, 'color' => '#FFF176'],
            ['label' => 'Power Bank',    'prize_type' => 'electronics', 'amount' => 0, 'color' => '#558B2F'],
            ['label' => 'Canned Goods',  'prize_type' => 'foods',       'amount' => 0, 'color' => '#F9A825'],
            ['label' => 'Hygiene Kit',   'prize_type' => 'essentials',  'amount' => 0, 'color' => '#AED581'],
            ['label' => '₱1,000 Cash',   'prize_type' => 'cash',        'amount' => 0, 'color' => '#33691E'],
        ];

        foreach ($prizes as $prize) {
            Prize::create($prize);
        }
    }
}
