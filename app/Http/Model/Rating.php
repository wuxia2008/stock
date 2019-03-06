<?php
/**
 * @describe
 *
 * @author wulixiong@haoxiaec.com
 * @since 2019/3/2
 */

namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'rating';
    protected $guarded = [];

    public function stock()
    {
        return $this->belongsTo('App\Http\Model\Stock');
    }

}