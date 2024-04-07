<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\models\Navi;
use app\components\SfHtml;

$settings = Yii::$app->params['settings'];
$formatter = Yii::$app->getFormatter();
$currentPage = $pages->page+1;

if( empty($title) ) {
    $this->title = Html::encode($settings['site_name']);
    $title = Yii::t('app', 'Latest');
} else {
    $this->title = Html::encode($title);
}
if($currentPage > 1) {
    $this->title = $this->title . ' - ' . Yii::t('app', 'Page {0,number}', $currentPage);
}
?>

<div class="row">
<!-- sf-left start -->
<div class="col-lg-8 sf-left">

<ul class="list-group sf-box">
    <li class="list-group-item sf-box-header navi-top-list py-2">
<?php
    echo Yii::$app->getUser()->getIsGuest()?'':'<span class="fr">' . Html::a('<i class="fas fa-pencil-alt" aria-hidden="true"></i>'.Yii::t('app', 'Add Topic'), ['topic/new']) . '</span>';
    echo Html::a(Yii::t('app', 'All Topics'), ['topic/index'], ['class'=>'btn btn-sm sf-btn current']);
    $navis = Navi::getHeadNaviNodes();
    foreach($navis as $current) {
        echo Html::a(Html::encode($current['name']), ['topic/navi', 'name'=>$current['ename']]);
    }
?>
    </li>
    <?php
    foreach($topics as $topic){
        $topic = $topic['topic'];
        $url = ['topic/view', 'id'=>$topic['id']];
        if ( $currentPage > 1) {
            $url['ip'] = $currentPage;
        }
        if($topic['comment_count'] > 1) {
            $url['#'] = 'reply' . $topic['comment_count'];
        }
        echo '<li class="list-group-item media">',
                SfHtml::uImgLink($topic['author']),
                '<div class="media-body">
                    <h5 class="mt-0">',
                    Html::a(Html::encode($topic['title']), $url, ['class'=>'sf-topic-link']), $topic['comment_closed']==1?' <i class="fa fa-lock gray" aria-hidden="true"></i>':'',
                    '</h5>
                    <div class="small gray">';
        if($topic['comment_count'] > 0){
            $gotopage = ceil($topic['comment_count']/intval($settings['comment_pagesize']));
            if($gotopage > 1){
                $url['p'] = $gotopage;
            }
            echo Html::a($topic['comment_count'], $url, ['class'=>'badge fr count-info']);
        }
                    echo Html::a(Html::encode($topic['node']['name']), ['topic/node', 'name'=>$topic['node']['ename']], ['class'=>'btn btn-sm btn-light small']),
                    ' • <strong><i class="fa fa-user" aria-hidden="true"></i>', SfHtml::uLink($topic['author']['username'], $topic['author']['name']), SfHtml::uGroupRank($topic['author']['score']), '</strong>',
                    ' • ', $topic['alltop']==1?'<i class="fa fa-arrow-up" aria-hidden="true"></i>'.Yii::t('app', 'Top'):'<i class="far fa-clock" aria-hidden="true"></i>' . $formatter->asRelativeTime($topic['replied_at']);
        if ($topic['comment_count']>0) {
                    echo '<span class="item-lastreply"> • <i class="fa fa-comment" aria-hidden="true"></i>', SfHtml::uLink($topic['lastReply']['username']), '</span>';
        }
                    echo '</div>
                </div>';
        echo '</li>';
    }
    ?>
    <li class="list-group-item sf-pagination">
    <?php
    echo LinkPager::widget([
        'pagination' => $pages,
        'maxButtonCount'=>5,
        'listOptions' => ['class'=>'pagination justify-content-center my-2'],
        'activeLinkCssClass' => ['sf-btn'],
    ]);
    ?>
    </li>

</ul>

<?php
if ( intval($settings['cache_enabled'])===0 || $this->beginCache('f-bottom-nodes', ['duration' => intval($settings['cache_time'])*60])) :
?>
<ul class="list-group sf-box bottom-navi">
	<li class="sf-box-header list-group-item"><span class="fr"><?php echo Html::a(Yii::t('app', 'All Nodes'), ['node/index']); ?></span><?php echo Yii::t('app', 'Node Navi'); ?>
	</li>
<?php
    $bNavis = Navi::getBottomNaviNodes();
    foreach($bNavis as $cNavi) :
?>
    <li class="list-group-item vertical-align">
        <div class="col-4 col-sm-3 col-lg-2 gray text-right"><?php echo Html::encode($cNavi['name']); ?></div>
        <div class="col-8 col-sm-9 col-lg-10 navi-links">
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
