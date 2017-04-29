<?php

namespace gud3\restAuth;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\redis\Session;
use gud3\restAuth\models\Auth;

/**
 * Class CheckToken
 * @package gud3\restAuth
 */
class CheckToken extends AuthMethod
{
    const DELETE = 1;

    private $status;

    /**
     * @param \yii\web\User $user
     * @param \yii\web\Request $request
     * @param \yii\web\Response $response
     * @return null|\yii\web\IdentityInterface
     * @throws \Exception
     * @throws \Throwable
     */
    public function authenticate($user, $request, $response)
    {
        if ($key = self::isAuth()) {
            $data_auth = Auth::find()->where(['series' => $key[1]])->one();

            if ($data_auth) {
                $session = new Session();
                $session->open();

                if ($data_auth->token !== $key[0]) {
                    $this->status = self::DELETE;
                } elseif ($data_auth->date_end <= time()) {
                    $this->status = self::DELETE;
                } elseif ($data_auth->token === $key[0]) {
                    //Identity user
                    $identity = $user->loginByAccessToken($data_auth->user_id, get_class($this));

                    if ($identity === null || $identity === false) {
                        $this->status = self::DELETE;
                    } else {
                        //Compare token with session in redis
                        if ($session->get('token') != $data_auth->token) {
                            $data_auth->changeToken(true);
                        }
                        return $identity;
                    }
                }
                $this->status === self::DELETE ? $data_auth->delete() : null;
            }
        }

        return null;
    }

    /**
     * @return array|bool
     */
    public static function isAuth()
    {           
        $accessToken = Yii::$app->request->headers->get('Authorization');
        if (is_string($accessToken)) {
            $key = explode(';', $accessToken);
            //$key[0] = token
            //$key[1] = series
            return $key;
        }

        return false;
    }
}
