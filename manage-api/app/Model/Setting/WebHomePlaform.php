<?php

declare(strict_types=1);

namespace App\Model\Setting;

use App\Foundation\Utils\Cron;
use App\Model\Model;

/**
 * 首页设置
 * Class WebHomePlaform
 * @package App\Model\Setting
 * @Author tx
 * @Date: 2021/4/25
 */
class WebHomePlaform extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'web_home_plaform';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

}