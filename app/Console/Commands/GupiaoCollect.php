<?php

namespace App\Console\Commands;

use App\Http\Model\Price;
use App\Http\Model\Rating;
use App\Http\Model\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use QL\QueryList;

class GupiaoCollect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crontab to collect gupiao';

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
        try {
            $list = Stock::where('is_del', 0)->get();
            foreach ($list as $val) {
                $keyword = $val->collect_is_name ? $val->code.$val->name : $val->code;
                $url = "https://m.sogou.com/web/searchList.jsp?keyword={$keyword}&pg=webSearchList&s=搜索";
                $ql = QueryList::get($url);

                $price = $this->updateStock($ql, $val->id);
                if(!$price){
                    continue;
                }
                $this->addPrice($val->id, $price);
                $this->addRating($val);
            }
            Log::info('success');
        } catch (\Exception $e) {
            Log::error('[error]'.$e->getMessage() . '[file]' . $e->getFile().'[line]'.$e->getLine());
        }
    }

    /**
     * 更新股票信息
     * @param object $ql
     * @param int $id
     *
     * @return bool
     */
    protected function updateStock($ql, $id)
    {
        for($i=0; $i<2; $i++){
            $class = $i == 0 ? '.state-rise' : '.state-down';
            $price = $ql->find("._stock_box>{$class}>.share-mes em")->text();
            if($price){
                break;
            }
        }
        if (!$price || !is_numeric($price)) {
            return false;
        }

        $data['price'] = $price ?? 0;
        $avgInfo = $ql->find('.tab-content>.box-share-tab>.img-flex>.img-layout')->attr('*');
        $data['avg'] = $avgInfo['data-value'] ?? 0;
        $data['current_rate'] = $avgInfo['data-state'] ?? '';
        $target = $ql->find('.tab-content>.box-share-tab>.share-about-list>.space-default p')->texts()->all();
        $data['shot_target'] = $target[0] ?? '';
        $data['middle_target'] = $target[1] ?? '';
        $data['long_target'] = $target[2] ?? '';
        $data['updated_at'] = date('Y-m-d H:i:s');
        Stock::where('id', $id)->update($data);
        return $data['price'];
    }

    /**
     * 更新机构评级
     *
     * @param object $val
     *
     * @return bool
     */
    private function addRating($val)
    {
        $ql = QueryList::get("http://m.10jqka.com.cn/doctor/{$val->code}/#institutions")
            ->encoding('UTF-8', 'GBK')
            ->removeHead()
            ->find('.jigou-table tbody');
        for ($i = 0; $i < 5; $i++) {
            $res = $ql->find("tr:eq({$i}) td")->texts()->all();
            if (!$res) {
                break;
            }
            Rating::firstOrCreate(
                [
                    'stock_id'     => $val->id,
                    'organization' => $res[0],
                    'rating_date'  => $res[1]
                ],
                [
                    'rating'      => $res[2],
                    'last_rating' => $res[3]
                ]
            );
        }
        return true;
    }

    /**
     * 更新价格
     * @param int $stockId
     * @param float $price
     *
     * @return mixed
     */
    private function addPrice($stockId, $price)
    {
        return Price::firstOrCreate(
            [
                'stock_id' => $stockId,
                'record_date' => date('Y-m-d')
            ],
            [
                'price' => $price,
                'updated_at' => date('Y-m-d H:i:s')
            ]
        );
    }


}
