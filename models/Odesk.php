<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "odesk".
 *
 * @property integer $id
 * @property string $pubDate
 * @property string $title
 * @property string $link
 * @property string $description
 * @property string $date
 */
class Odesk extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odesk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pubDate', 'title', 'link', 'description', 'date'], 'required'],
            [['pubDate', 'date'], 'safe'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 200],
            [['link'], 'string', 'max' => 1000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pubDate' => 'Pub Date',
            'title' => 'Title',
            'link' => 'Link',
            'description' => 'Description',
            'date' => 'Date',
        ];
    }
}
