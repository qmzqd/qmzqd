<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\controllers;

use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use app\models\Tag;
use app\models\TagTopic;

class TagController extends AppController
{
    public function actionIndex($name)
    {
		$tag = $this->findModel($name);
	    $pages = new Pagination([
			'totalCount' => $tag['topic_count'],
			'pageSize' => intval($this->settings['list_pagesize']),
			'pageParam' => 'p',
		]);

	    return $this->render('index', [
			 'tag' => $tag,
	         'topics' => TagTopic::getTopics($tag['id'], $pages),
	         'pages' => $pages,
	    ]);
    }

    protected function findModel($name, $with=null)
    {
		$model = Tag::find()->select(['id', 'name', 'topic_count'])->where(['name' => $name]);
		if ( !empty($with) ) {
			$model = $model->with($with);
		}
		$model = $model->asArray()->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', '{attribute} doesn\'t exist.', ['attribute'=>Yii::t('app', 'Tag')]));
        }
    }

}
