<?php

namespace gud3\restAuth\models;

use Yii;

/**
 * This is the model class for table "auth_token".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 * @property string $series
 * @property integer $date_end
 */
class Auth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'token', 'series'], 'required'],
            [['user_id', 'date_end'], 'integer'],
            [['token', 'series'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => 'user', 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function getUser()
    {
        return $this->hasOne('user', ['id' => 'user_id']);
    }

    public static function generateAuth($user_id, $remember_time)
    {
        $auth = new self;
        $auth->user_id = $user_id;
        $auth->token = Yii::$app->security->generateRandomString(32);
        $auth->series = Yii::$app->security->generateRandomString(32);
        $auth->date_end = $remember_time;

        Yii::$app->response->headers->add('Authorization', $auth->token);

        if ($auth->save()) {
            Yii::$app->session->set('token', $auth->token);
            return $auth;
        }

        return false;
    }

    public function changeToken($add_to_headers = true)
    {
        $this->token = Yii::$app->security->generateRandomString(32);
        Yii::$app->session->set('token', $this->token);

        if($add_to_headers){
            Yii::$app->response->headers->add('Authorization', $this->token);
        }

        return $this->save() ? true : false;
    }

    public static function short()
    {
        return time() + (60 * 60 * 24);
    }

    public static function long()
    {
        return time() + (60 * 60 * 24 * 365);
    }
}
