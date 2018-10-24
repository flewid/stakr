<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\base\Security;
use yii\helpers\Html;
use yii\validators\UniqueValidator;
use yii\web\IdentityInterface;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $password_old;
    public $password_new;
    public $password_new_repeat;
    public $password_repeat;

    public function rules()
    {
        return [
            ['newEmail', 'required', 'on'=>'changeEmail'],
            ['newEmail', 'email', 'on'=>'changeEmail'],
            ['newEmail', 'validateNewEmail', 'on'=>'changeEmail'],

            [['email', 'name'], 'required'],

            [['password_old', 'password_new', 'password_new_repeat'], 'required', 'on'=>'changePassword'],
            ['password_new_repeat', 'compare', 'compareAttribute'=>'password_new', 'on'=>'changePassword'],
            ['password_old', 'validateOldPassword', 'on'=>'changePassword'],

            [['password', 'password_repeat'], 'required', 'on'=>'signup'],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'on'=>'signup'],

            ['email', 'unique'],
            ['email', 'email'],
            ['authKey', 'safe'],
            ['enable', 'boolean'],
            ['offline_mode', 'boolean'],
            [['name', 'address','postalCode','city','phone',], 'string', 'max' => 200],
            [['role'], 'integer',],
            [['country_id', 'state'], 'safe',],
            [['country_id'], 'default', 'value'=>0,],
        ];
    }
    public function active($query)
    {
        return $query->andWhere('enable = 1');
    }
    public function validateOldPassword($attribute, $params)
    {
        if ($this->password != md5($this->password_old))
        {
            $this->addError('password_old', 'Incorrect old password.');
        }
    }
    public function validateNewEmail($attribute, $params)
    {
        if (User::find()->where(['email'=>$this->newEmail,])->one())
        {
            $this->addError('newEmail', Yii::t('yii', 'Email "{value}" has already been taken.', ['value'=>$this->newEmail,]));
        }
    }

    const ROLE_ADMIN = 1;
    const ROLE_USER = 2;
    const ROLE_MODER = 3;
    public function getRoleLabel()
    {
        return self::$roleValues[$this->role];
    }
    public static $roleValues = [
                            self::ROLE_USER=>'User',
                            self::ROLE_ADMIN=>'Administrator',
                         ];
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }



    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['accessToken' => $token]);
    }


    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        //return static::findOne(['email' => $username]);

        $user = \app\models\User::find()
            ->where(['email' => $username])
            ->one();

        if($user)
            return $user;
    }
    /**
     * @inheritdoc
     */

    public function beforeSave($insert)
    {
          if(parent::beforeSave($insert))
          {
              if($this->isNewRecord)
              {
                  if($this->password)
                      $this->password = md5($this->password);
              }
              return true;
          } else
             return false;
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }
    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === md5($password);
    }
    public function validateEnable()
    {
        return $this->enable == 1;
    }
    public function generateAuthKey()
    {
        //$this->authKey = Security::generateRandomKey();
        $this->authKey = md5(time());
    }
    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Security::generateRandomKey() . '_' . time();
    }
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
	    {
	        $this->password_reset_token = null;
	    }
    public static function findByPasswordResetToken($token)
    {
        $expire = \Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token
        ]);
    }


    public function getLink()
    {
        return Html::a($this->name, ['user/view', 'id'=>$this->id,]);
    }
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'country_id' => Yii::t('app', 'Country'),
        ];
    }
}
