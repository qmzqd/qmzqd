<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\plugins\WysibbEditor;

use yii\web\AssetBundle;

class WysibbAsset extends AssetBundle
{
//    public $basePath = '@webroot';
    public $baseUrl = '@web/static/assets/wysibb';
    public $css = [
        'theme/default/wbbtheme.css',
    ];
    public $js = [
        'jquery.wysibb.min.js',
        'lang/zh-CN.js',
        'wysibb-for-simpleforum.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
