<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Imagine;

/**
 * Image implements most commonly used image manipulation functions using the [Imagine library](http://imagine.readthedocs.org/).
 *
 * Example of use:
 *
 * ~~~php
 * // generate a thumbnail image
 * Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
 *     ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
 * ~~~
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Image extends BaseImage
{
}
/*$nurbek = new \imageNameSpace\Nurbek;
            echo $nurbek::$qwe;
            /*
            $imagine=new \Imagine\Gd\Imagine();
            $image = $imagine->open('1.jpg');
            $image->usePalette(new \Imagine\Image\Palette\RGB())
                ->save('3.jpg');
            */
/*
Image::frame('1.jpg', 5, 'fc3008', 0)
    ->rotate(-8)
    ->save('2.jpg', ['quality' => 50]);
*/