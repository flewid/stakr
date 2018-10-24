<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 2/15/15
 * Time: 2:46 PM
 */

namespace app\assets;

use Yii;
use yii\web\AssetBundle;

class BootboxAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/bootbox';
    public $js = [
        'bootbox.js',
    ];

    public static function overrideSystemConfirm()
    {
        Yii::$app->view->registerJs('
            yii.confirm = function(message, ok, cancel) {
                bootbox.confirm(message, function(result) {
                    if (result) {  !ok || ok(); } else { !cancel || cancel(); }
                });
            }
        ');
        Yii::$app->view->registerJs('
            $(document).on(\'click\', \'.modal-backdrop\', function (event) {
                bootbox.hideAll()
            });
        ');
    }
}