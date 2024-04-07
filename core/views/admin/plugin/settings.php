<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ActiveForm;

\app\assets\Select2Asset::register($this);
$this->registerJs("$('select').select2();");

$this->title = Yii::t('app/admin', 'Plugin') . '['.$plugin['pid'].']';

function showSettingForm($settings, $form)
{
    foreach ($settings as $key=>$setting) {
        $options = ArrayHelper::getValue($setting, 'option', []);
        if (!empty($options)) { 
            foreach ($options as $k=>$v) {
                $options[$k] = Yii::t('app', $v);
            }
        }
        if ($setting['type'] === 'select') {
//            $options = json_decode($setting['option'],true);
            echo $form->field($setting, "[$key]value", ['enableError'=>false,])
                    ->dropDownList($options)->label($setting['label'])->hint($setting['description']);
        } else if ($setting['type'] === 'textarea') {
            echo $form->field($setting, "[$key]value", ['enableError'=>false,])
                    ->textArea()->label($setting['label'])->hint($setting['description']);
        } else if ($setting['type'] === 'checkboxList') {
//            $options = json_decode($setting['option'],true);
//            $setting['value'] = json_decode($setting['value'],true);
            echo $form->field($setting, "[$key]value", ['enableError'=>false,])
                    ->inline()->checkboxList($options)->label($setting['label'])->hint($setting['description']);
        } else  {
            echo $form->field($setting, "[$key]value", ['enableError'=>false,])->input($setting['type'])
                    ->label($setting['label'])->hint($setting['description']);
        }
    }
}
?>
<div class="row">
<!-- sf-left start -->
<div class="col-lg-8 sf-left">

<ul class="list-group sf-box">
    <li class="list-group-item sf-box-header sf-navi">
        <?php
            echo Html::a(Yii::t('app/admin', 'Forum Manager'), ['admin/setting/all']), '&nbsp;/&nbsp;', Html::a(Yii::t('app/admin', 'Plugins'), ['index']), '&nbsp;/&nbsp;', $this->title;
        ?>
    </li>
<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'id' => 'form-setting',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-form-label col-sm-3 text-sm-right',
            'wrapper' => 'col-sm-9',
        ],
    ],
]); ?>
    <li class="list-group-item sf-box-form">
<?php
if ( !empty($settings) ) {
    showSettingForm($settings, $form);
}
?>
        <div class="form-group">
            <div class="offset-sm-3 col-sm-9">
            <?php echo Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn sf-btn']); ?>
            </div>
        </div>
    </li>

<?php
ActiveForm::end();
?>
</ul>

</div>
<!-- sf-left end -->

<!-- sf-right start -->
<div class="col-lg-4 sf-right">
<?php echo $this->render('@app/views/common/_admin-right'); ?>
</div>
<!-- sf-right end -->

</div>
