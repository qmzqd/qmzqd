<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Alert;

$session = Yii::$app->getSession();

$this->title = Yii::t('app', 'Sign in');
?>

<div class="row">
<!-- sf-left start -->
<div class="col-lg-8 sf-left">

<div class="card sf-box">
    <div class="card-header sf-box-header sf-navi">
        <?php echo Html::a(Yii::t('app', 'Home'), ['topic/index']), '&nbsp;/&nbsp;', $this->title; ?>
    </div>
    <div class="card-body sf-box-form">
<?php
if ( $session->hasFlash('accessNG') ) {
echo Alert::widget([
       'options' => ['class' => 'alert-warning'],
       'body' => Yii::t('app', $session->getFlash('accessNG')),
    ]);
}
?>
<?php $form = ActiveForm::begin([
          'layout' => 'horizontal',
          'id' => 'form-login',
          'fieldConfig' => [
              'horizontalCssClasses' => [
                  'label' => 'col-form-label col-sm-3 text-sm-right',
                  'wrapper' => 'col-sm-9',
              ],
          ],
      ]); ?>
        <?php echo $form->field($model, 'username')->textInput(['maxlength'=>20]); ?>
        <?php echo $form->field($model, 'password')->passwordInput(['maxlength'=>20]); ?>
        <?php echo $form->field($model, 'rememberMe', [
                       'horizontalCssClasses' => [
                           'offset' => 'offset-sm-3',
                       ]
                   ])->checkbox(); ?>
        <?php
        $captcha = ArrayHelper::getValue(Yii::$app->params, 'settings.captcha', '');
        if(!empty($captcha) && ($plugin=ArrayHelper::getValue(Yii::$app->params, 'plugins.' . $captcha, []))) {
            $plugin['class']::captchaWidget('signin', $form, $model, null, $plugin);
        }
        ?>

        <div class="form-group">
            <div class="offset-sm-3 col-sm-9">
            <?php echo Html::submitButton(Yii::t('app', 'Sign in'), ['class' => 'btn sf-btn', 'name' => 'login-button', 'id'=>'recaptchaToken']); ?>
            &nbsp;&nbsp;<?php echo Html::a(Yii::t('app', 'Forgot password?'), ['site/forgot-password']); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
<?php if ( intval(Yii::$app->params['settings']['auth_enabled']) === 1 ) : ?>
        <h6 class="third-party-login-msg"><strong><?php echo Yii::t('app', 'Third-party login'); ?></strong></h6>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 third-party-login">
        <?php
foreach (Yii::$app->authClientCollection->getClients() as $client){
    echo '<div class="col ' .$client->getId() . '">';
    if ($client->getId() == 'weixin' && $client->type == 'mp') {
        echo Html::a('<i class="fab fa-'.$client->getId().'"></i><span class="snstext">'. Html::encode($client->getTitle()) . '</span>', '#', ['onclick'=>'return false;', 'class'=>'btn auth-link '. $client->getId(), 'id'=>'weixinmp', 'link'=>Url::to(['site/auth', 'authclient'=>$client->getId()], true)]);
    } else {
        echo Html::a('<i class="fab fa-'.$client->getId().'"></i><span class="snstext">'. Html::encode($client->getTitle()) . '</span>', ['site/auth', 'authclient'=>$client->getId()], ['class'=>'btn auth-link '. $client->getId(), 'title'=>Html::encode($client->getTitle())]);
    }
    echo '</div>';
}
        ?></div>
<?php endif; ?>
    </div>
</div>

</div>
<!-- sf-left end -->

<!-- sf-right start -->
<div class="col-lg-4 sf-right">
<?php echo $this->render('@app/views/common/_right'); ?>
</div>
<!-- sf-right end -->

</div>
