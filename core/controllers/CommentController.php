<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Topic;
use app\models\Comment;
use app\models\History;
use app\components\Util;

class CommentController extends AppController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'reply' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['edit', 'reply'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $me = Yii::$app->getUser();
                            return ( !$me->getIsGuest() && ($me->getIdentity()->isActive() || $me->getIdentity()->isAdmin()) );
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    $me = Yii::$app->getUser();
                    if( !$me->getIsGuest() && $me->getIdentity()->isInactive() ) {
                        throw new ForbiddenHttpException(Yii::t('app', 'Your account is not activated. Please activate your account first.'));
                    } else {
                        throw new ForbiddenHttpException(Yii::t('app', 'You have no authority.'));
                    }
                },
            ],
        ];
    }

    public function actionEdit($id)
    {
        $request = Yii::$app->getRequest();
        $me = Yii::$app->getUser()->getIdentity();

        $model = $this->findCommentModel($id, ['topic.node', 'topic.author']);

        if( !$me->canEdit($model, $model->topic->comment_closed) ) {
            throw new ForbiddenHttpException(Yii::t('app', 'You have no authority to edit it or exceeded time limit.'));
        }

        if( $me->isAdmin() ) {
            $model->scenario = Comment::SCENARIO_ADMIN;
        } else {
            $model->scenario = Comment::SCENARIO_AUTHOR;
        }

        if ( $model->load($request->post()) && $model->save() ) {
            (new History([
                'user_id' => $me->id,
                'action' => History::ACTION_EDIT_COMMENT,
                'action_time' => $model->updated_at,
                'target' => $model->id,
                'ext' => '',
            ]))->save(false);
            return $this->redirect(Topic::getRedirectUrl($model->topic_id, $model->position, $request->get('ip', 1), $request->get('np', 1)));
        }

        return $this->render('edit', [
            'comment' => $model,
            'topic' => Util::convertModelToArray($model->topic),
       ]);
    }

    public function actionReply($id)
    {
        $request = Yii::$app->getRequest();
        $me = Yii::$app->getUser()->getIdentity();
        if( !$me->checkActionCost('addComment') ) {
            return $this->render('@app/views/common/info', [
                'title' => Yii::t('app', 'You don\'t have enough points.'),
                'status' => 'warning',
                'msg' => Yii::t('app', 'You don\'t have enough points. You can get points from daily bonus service.'),
            ]);
        }

        $topic = $this->findTopicModel($id, ['node', 'author']);

        if( !$me->canReply($topic) ) {
            throw new ForbiddenHttpException(Yii::t('app', 'You have no authority to comment on the topic or the topic was closed.'));
        }

        $model = new Comment();
        if ( $model->load($request->post()) && $model->validate()) {
            if( !$me->canPost(History::ACTION_ADD_COMMENT) ) {
                Yii::$app->getSession()->setFlash('postNG', Yii::t('app', 'Post inverval time warning: Please try again.'));
            } else {
                $model->user_id = $me->id;
                $cid = new \app\models\Commentid(['id'=>null]);
                $cid->save(false);
                $model->id = $cid->id;
                $model->link('topic', $topic);

                $this->redirect(Topic::getRedirectUrl($id, $model->position));
            }
        }
        return $this->render('add', [
            'comment' => $model,
            'topic' => Util::convertModelToArray($topic),
       ]);

    }

    protected function findCommentModel($id, $with=null)
    {
        $model = Comment::find()->where(['id'=>$id]);
        if (!empty($with)) {
            $model = $model->with($with);
        }
        $model = $model->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', '{attribute} doesn\'t exist.', ['attribute'=>Yii::t('app', 'Comment')]));
        }
    }

    protected function findTopicModel($id, $with=null)
    {
        $model = Topic::find()->where(['id'=>$id]);
        if ( !empty($with) ) {
            $model = $model->with($with);
        }
        $model = $model->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', '{attribute} doesn\'t exist.', ['attribute'=>Yii::t('app', 'Topic')]));
        }
    }

}
