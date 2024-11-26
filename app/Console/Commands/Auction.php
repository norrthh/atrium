<?php

namespace App\Console\Commands;

use App\Facades\WithdrawUser;
use App\Models\User\UserAuction;
use Illuminate\Console\Command;

class Auction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        while (true) {
           $auctions = \App\Models\Auction::query()->where('auction_end_time', '<=', now())->with('item')->get();

           foreach ($auctions as $auction) {
              $userAuction = UserAuction::query()->where('auction_id', $auction->id)->orderBy('id', 'desc')->first();

              if ($userAuction) {
                 WithdrawUser::store($auction->item_id, 1, $userAuction->user_id);
              }

              \App\Models\Auction::query()->where('id', $auction->id)->delete();
           }
        }
    }
}
