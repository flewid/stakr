<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $action
 * @property integer $metal_id
 * @property integer $date
 * @property integer $description
 */
class Log extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log';
    }
    public function getMetal()
    {
        return $this->hasOne(MetalStack::className(), ['ID' => 'metal_id']);
    }
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public static function create($options=[])
    {
        $log = new Log($options);
        $log->user_id = Yii::$app->user->id;
        $log->action = Yii::$app->controller->action->id;
        $log->date = date('Y-m-d H:i:s');
        if($log->description=='' && $log->metal_id)
        {
            switch($log->action){
                case 'create': $action = 'created'; break;
                case 'update': $action = 'updated'; break;
                case 'lost': $action = 'lost'; break;
                case 'trade': $action = 'traded'; break;
                case 'sold': $action = 'sold'; break;
                case 'restore': $action = 'restored'; break;
            }
            $metalLink = $log->metal->link;
            $metalDesc = $log->metal->metalDescription;
            $log->description = Yii::$app->user->identity->link." $action item $metalDesc ($metalLink).";
        }
        $log->save();
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'metal_id'], 'integer'],
            [['action'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'action' => 'Action',
            'metal_id' => 'Metal ID',
        ];
    }
}
