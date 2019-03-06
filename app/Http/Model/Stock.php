<?php
/**
 * @describe
 *
 * @author wulixiong@haoxiaec.com
 * @since 2019/3/2
 */

namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    public function rating()
    {
        return $this->hasMany('App\Http\Model\Rating');
    }

}