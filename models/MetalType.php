<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "metaltype".
 *
 * @property integer $ID
 * @property string $metalSymbol
 * @property string $metalDescription
 */
class MetalType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'metaltype';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['author_id', 'required'],
            ['default', 'boolean'],
            [['metalSymbol', 'metalDescription'], 'required'],
            [['metalSymbol'], 'string', 'max' => 3],
            [['metalDescription'], 'string', 'max' => 255]
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
            'metalSymbol' => Yii::t('app', 'Symbol'),
            'metalDescription' => Yii::t('app', 'Description'),
            'author_id' => Yii::t('app', 'Author'),
        ];
    }
}
