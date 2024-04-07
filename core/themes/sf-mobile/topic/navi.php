<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\models\Navi;
use app\models\Node;
use app\components\SfHtml;
use app\components\Util;

$settings = Yii::$app->params['settings'];

if( empty($title) ) {
	$this->title = Html::encode($settings['site_name']);
	$title = Yii::t('app', 'Latest');
} else {
    $this->title = Html::encode($title);
}
?>

<div class="row">
<!-- sf-left start -->
<div class="col-lg-8 sf-left">

<ul class="list-group sf-box">
<?php if(!Yii::$app->getUser()->getIsGuest()): 
    $me = Yii::$app->getUser()->getIdentity();
?>
    <li class="list-group-item sf-box-header">
<?php
    echo '<span class="fr">' . Html::a('<i class="fas fa-pencil-alt" aria-hidden="true"></i>'.Yii::t('app', 'Add Topic'), ['topic/new']) . '</span>';
    echo Html::a('<i class="fa'.($me->getNoticeCount()>0?'s':'r').' fa-bell" aria-hidden="true"></i>' . Yii::t('app', 'Notifications{n, plural, =0{} other{(+#)}}', ['n'=>$me->getNoticeCount()]), ['my/notifications']);
    echo ' ', Html::a(SfHtml::uScore($me->score), ['my/balance'], ['class'=>'btn btn-sm btn-light']);
?>
    </li>
<?php endif; ?>
    <li class="list-group-item navi-top-list">
<?php
    echo Html::a(Yii::t('app', 'All Topics'), ['topic/index']);
    $navis = Navi::getHeadNaviNodes();
    foreach($navis as $current) {
        echo Html::a(Html::encode($current['name']), ['topic/navi', 'name'=>$current['ename']], $current['id']==$navi['id']?['class'=>'btn btn-sm sf-btn current']:[]);
    }
?>
    </li>
<?php if(!empty($navi['visibleNaviNodes'])) : ?>
    <li class="list-group-item list-group-item-info navi-top-nodes small hidden-xs">
<?php
    foreach($navi['visibleNaviNodes'] as $current) {
        $current = $current['node'];
        echo Html::a(Html::encode($current['name']), ['topic/node', 'name'=>$current['ename']]);
    }
?>
    </li>
<?php endif; ?>
    <?php
    foreach($topics as $topic){
        $topic = $topic['topic'];
        $url = ['topic/view', 'id'=>$topic['id']];
        echo '<li class="list-group-item media">',
                SfHtml::uImgLink($topic['author']),
                '<div class="media-body">
                    <div class="small gray mt-0">';
                    echo '<strong><i class="fa fa-user" aria-hidden="true"></i>', SfHtml::uLink($topic['author']['username'], $topic['author']['name']), SfHtml::uGroupRank($topic['author']['score']), '</strong>',
                    $topic['alltop']==1?' • <i class="fa fa-arrow-up" aria-hidden="true"></i>'.Yii::t('app', 'Top'):'',
                    '</div>
                    <h5>';
        if($topic['comment_count'] > 0){
            $gotopage = ceil($topic['comment_count']/intval($settings['comment_pagesize']));
            if($gotopage > 1){
                $url['p'] = $gotopage;
            }
            echo Html::a($topic['comment_count'], $url, ['class'=>'badge fr count-info']);
        }
                    echo Html::a(Html::encode($topic['title']), $url), $topic['comment_closed']==1?' <i class="fa fa-lock gray" aria-hidden="true"></i>':'',
                    '</h5>
                    <div class="small gray">';
                    echo Html::a(Html::encode($topic['node']['name']), ['topic/node', 'name'=>$topic['node']['ename']], ['class'=>'btn btn-sm btn-light small']), ' • <i class="far fa-clock" aria-hidden="true"></i>'.Yii::$app->formatter->asRelativeTime($topic['replied_at']);
        if ($topic['comment_count']>0) {
                    echo '<span class="item-lastreply"> • <i class="fa fa-comment" aria-hidden="true"></i>', SfHtml::uLink($topic['lastReply']['username']), '</span>';
        }
                    echo '</div>
                </div>';

        echo '</li>';
    }
    ?>
    <li class="list-group-item">
        <?php echo Html::a('<i class="fa fa-arrow-up" aria-hidden="true"></i>'.Yii::t('app', 'More'), ['topic/index']); ?>
    </li>

</ul>

<?php
if ( intval($settings['cache_enabled'])===0 || $this->beginCache('f-bottom-nodes', ['duration' => intval($settings['cache_time'])*60])) :
?>
<ul class="list-group sf-box bottom-navi">
    <li class="list-group-item gray"><span class="fr"><?php echo Html::a(Yii::t('app', 'All Nodes'), ['node/index']); ?></span><?php echo Yii::t('app', 'Node Navi'); ?>
    </li>
<?php
    $bNavis = Navi::getBottomNaviNodes();
    foreach($bNavis as $cNavi) :
?>
    <li class="list-group-item vertical-align">
        <div class="col-3 col-sm-2 gray text-right"><?php echo Html::encode($cNavi['name']); ?></div>
        <div class="col-9 col-sm-10 navi-links">
        <?php
            foreach($cNavi['naviNodes'] as $cNode) {
                $cNode = $cNode['node'];
                echo Html::a(Html::encode($cNode['name']), ['topic/node', 'name'=>$cNode['ename']]);
            }
        ?>
        </div>
    </li>
<?php
    endforeach;
?>
</ul>
<?php
if ( intval($settings['cache_enabled']) !== 0 ) {
    $this->endCache();
}
endif;
?>

</div>
<!-- sf-left end -->

<!-- sf-right start -->
<div class="col-lg-4 sf-right">
<?php echo $this->render('@app/views/common/_index-right'); ?>
</div>
<!-- sf-right end -->

</div>
