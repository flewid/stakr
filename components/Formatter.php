<?php
/**
 * Created by PhpStorm.
 * User: Nurbek
 * Date: 2/24/15
 * Time: 10:25 AM
 */

namespace app\components;
use Yii;
use yii\i18n\Formatter AS yiiFormatter;


class Formatter extends yiiFormatter{

/*    public function asDate($value, $format = null)
    {
        if($value=='0000-00-00')
            return $this->nullDisplay;
        return parent::asDate($value, $format);
    }*/

    public function asDollar($value)
    {
        return $value." $";
    }
    public static function asTextLimit($string, $countOfSymbols=150)
    {
        $string = strip_tags($string);
        if(strlen($string)<$countOfSymbols)
            return $string;

        $string = substr($string, 0, $countOfSymbols);
        $string = rtrim($string, "!,.-");
        $string = substr($string, 0, strrpos($string, ' '));
        return $string."... ";
    }

    public function asDateTimeText($value, $type='medium')
    {
        $datetime1 = new \DateTime($value);
        $datetime2 = new \DateTime(date('Y-m-d H:i:s'));
        $interval = $datetime1->diff($datetime2);

        if($interval->format('%y')==0 && $interval->format('%m')==0 && $interval->format('%W')==0)
        {
            if($interval->format('%d')>0 && $interval->format('%d')<=3)
                return Yii::t('app', '{n, plural, one{# day}  other{# days}} ago', ['n' => $interval->format('%d')]);
            elseif($interval->format('%d')==0)
            {
                if($interval->format('%h')>0 && $interval->format('%h')<=3)
                    return Yii::t('app', '{n, plural, one{# hour}  other{# hours}} ago', ['n' => $interval->format('%h')]);
                elseif($interval->format('%h')==0)
                {
                    if($interval->format('%i')>0)
                        return Yii::t('app', '{n, plural, one{# minute}  other{# minutes}} ago', ['n' => $interval->format('%i')]);
                    else
                        return Yii::t('app', 'Just now');
                }
                else
                    return Yii::t('app', 'Today at {date}', ['date'=>date('H:i', strtotime($value)),]);
            }
        }
        return $this->asDatetime($value,$type);
    }
} 