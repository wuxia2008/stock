<?php
/**
 * @describe 首页
 *
 * @author wulixiong@haoxiaec.com
 * @since 2019/3/2
 */

namespace App\Http\Controllers;


use App\Http\Response\StockResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    protected $response;

    public function __construct(StockResponse $response)
    {
        $this->response = $response;
    }

    /**
     * 首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $code = trim(request()->get('code', ''));
        $list = $this->response->getIndex($code);
        return view('index.index', ['list' => $list, 'code' => $code]);
    }

    /**
     * 根据ID获取最近5条评级列表
     */
    public function ajaxGetRate(Request $request)
    {
        $stockId = request()->post('stock_id', 0);
        $list = $this->response->getRateByStockIdList($stockId);
        echo json_encode($list);
    }


}