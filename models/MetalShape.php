<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "metalshape".
 *
 * @property integer $ID
 * @property string $shape
 */
class MetalShape extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'metalshape';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['author_id', 'required'],
            ['default', 'boolean'],
            [['shape'], 'required'],
            [['shape'], 'string', 'max' => 129]
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => Yii::t('app', 'ID'),
            'shape' => Yii::t('app', 'Shape'),
            'author_id' => Yii::t('app', 'Author'),
        ];
    }
}
