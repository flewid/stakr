<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "metalgrade".
 *
 * @property integer $ID
 * @property string $metalGrade
 * @property string $metalGradeDescription
 * @property string $metalGradeScale
 */
class MetalGrade extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'metalgrade';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['author_id', 'required'],
            ['default', 'boolean'],
            [['metalGrade', 'metalGradeDescription', 'metalGradeScale'], 'required'],
            [['metalGrade', 'metalGradeDescription', 'metalGradeScale'], 'string', 'max' => 255]
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
            'metalGrade' => Yii::t('app', 'Grade'),
            'metalGradeDescription' => Yii::t('app', 'Description'),
            'metalGradeScale' => Yii::t('app', 'Scale'),
            'author_id' => Yii::t('app', 'Author'),
        ];
    }
}
