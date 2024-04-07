<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\controllers\admin;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class CommonController extends \app\controllers\AppController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $me = Yii::$app->getUser();
                            return ( !$me->getIsGuest() && $me->getIdentity()->isAdmin() );
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\NotFoundHttpException(Yii::t('app', '{attribute} doesn\'t exist.', ['attribute'=>Yii::t('app', 'Url')]));
                }
            ],
        ];
    }

    public function isOffline($action, $user)
    {
        return false;
    }

}
