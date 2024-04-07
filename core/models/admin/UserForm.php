<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\models\admin;

use Yii;
use yii\base\Model;
use app\models\User;

class UserForm extends Model
{
    const SCENARIO_EDIT = 1;
    const SCENARIO_RESET_PWD = 2;
    const SCENARIO_SEARCH = 3;

    public $id;
    public $username;
    public $status;
    public $name;
    public $email;
    public $password;
    private $_user;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_EDIT] = ['email', 'name', 'status', '!username', '!id'];
        $scenarios[self::SCENARIO_RESET_PWD] = ['password'];
        $scenarios[self::SCENARIO_SEARCH] = ['username'];
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'integer', 'max'=>User::STATUS_ACTIVE, 'min'=>User::STATUS_BANNED],
            [['email','name'], 'trim'],
            [['email', 'name', 'username', 'password'], 'required'],
            ['email', 'email'],
            ['password', 'string', 'length' => [6, 16]],
            ['name', 'string', 'length' => [4, 40]],
            ['name', 'nameFilter'],
            ['email', 'validateEmail'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'status' => Yii::t('app', 'Status'),
            'name' => Yii::t('app', 'Name'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
        ];
    }

    public function search()
    {
        $this->_user = User::find()->select(['id','username', 'status'])->where(['username'=>$this->username])->one();
        return $this->_user;
    }

    public function find($id)
    {
        $this->_user = User::findOne($id);
        if($this->_user !== null) {
            $this->attributes = $this->_user->attributes;
            $this->username = $this->_user->username;
            $this->id = $this->_user->id;
        }
        return ($this->_user !== null);
    }

    public function validateEmail($attribute, $params)
    {
        $user = User::find()->where(['email'=>$this->$attribute])->one();
        if($user && $user->id != $this->_user->id) {
            $this->addError($attribute, Yii::t('app', '{name} already exists.', ['name' => Yii::t('app', 'Email')]));
        }
    }

    public function edit()
    {
        if ($this->validate()) {
            $user = $this->_user;
            $user->status = $this->status;
            $user->name = $this->name;
            $user->email = $this->email;
            return $this->_user->save(false);
        }

        return false;
    }
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        return $user->save();
    }

    public function getUser()
    {
        return $this->_user;
    }


    public function nameFilter($attribute, $params)
    {
        if( empty(Yii::$app->params['settings']['name_filter']) ) {
            return;
        }
        $filters = explode(',', Yii::$app->params['settings']['name_filter']);
        foreach($filters as $filter) {
            $pattern = str_replace('*', '.*', $filter);
            $result = preg_match('/^' . $pattern . '$/is', $this->$attribute);
            if ( !empty($result) ) {
                $this->addError($attribute, Yii::t('app', '{attribute} cannot contain "{value}".', ['attribute'=>Yii::t('app', 'Name'), 'value'=>str_replace('*', '', $filter)]));
                return;
            }
        }
    }
}
