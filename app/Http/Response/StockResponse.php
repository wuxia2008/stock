<?php
/**
 * @describe
 *
 * @author wulixiong@haoxiaec.com
 * @since 2019/3/2
 */

namespace App\Http\Response;


use App\Http\Model\Price;
use App\Http\Model\Rating;
use App\Http\Model\Stock;

class StockResponse
{
    /**
     * 获取首页列表
     * @return mixed
     */
    public function getIndex($code)
    {
        $query = Stock::where('is_del', 0);
        if ($code) {
            $query->where(function ($q) use ($code) {
                $q->where('code', $code)
                    ->OrWhere('name', $code);
            });
        }
        $list = $query->orderBy('avg', 'desc')
            ->paginate(10);
        foreach($list as &$val){
            $w = date('w');
            $days = in_array($w, [0,1]) ? '-5 days' : '-3 days';
            $val->star = Rating::where('stock_id', $val->id)
                ->whereDate('rating_date', '>', date('Y-m-d', strtotime($days)))
                ->whereIn('rating', ['增持', '买入'])
                ->count();
        }
        return $list;
    }

    /**
     * 根据ID获取评级列表
     *
     * @param int $stockId
     *
     * @return array
     */
    public function getRateByStockIdList($stockId)
    {
        $rateList = Rating::where('stock_id', $stockId)
            ->orderBy('rating_date', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
        $priceList = Price::where('stock_id', $stockId)
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->toArray();
        return [
            'rateList'  => $rateList,
            'priceList' => $priceList,
            'hello'     => [123]
        ];
    }

}