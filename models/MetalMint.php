<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "metalmint".
 *
 * @property integer $id
 * @property string $title
 */
class MetalMint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'metalmint';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id'], 'number'],
            ['author_id', 'required'],
            ['default', 'boolean'],
            [['title'], 'required'],
            [['title'], 'string', 'max' => 200],
            [['description'], 'string']
        ];
    }
    public function init()
    {
        $this->author_id = Yii::$app->user->identity->id;
    }
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Name'),
            'country_id' => Yii::t('app', 'Country'),
            'author_id' => Yii::t('app', 'Author'),
        ];
    }
}
