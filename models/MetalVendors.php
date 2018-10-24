<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "metalvendors".
 *
 * @property integer $ID
 * @property string $vendorName
 * @property string $vendorSite
 * @property string $vendorCountry
 */
class MetalVendors extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'metalvendors';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['author_id', 'required'],
            ['vendorCountry', 'number'],
            ['default', 'boolean'],
            [['vendorName', 'vendorCountry'], 'required'],
            [['vendorName', 'vendorCountry'], 'string', 'max' => 128],
            [['vendorSite'], 'string', 'max' => 255]
        ];
    }
    public function init()
    {
        $this->author_id = Yii::$app->user->id;
    }
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => Yii::t('app', 'ID'),
            'vendorName' => Yii::t('app', 'Name'),
            'vendorSite' => Yii::t('app', 'Site'),
            'vendorCountry' => Yii::t('app', 'Country'),
            'author_id' => Yii::t('app', 'Author'),
        ];
    }
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'vendorCountry']);
    }
}
