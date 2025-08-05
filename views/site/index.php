<?php

/** @var yii\web\View $this */
/** @var app\models\Url $model */
/** @var string|null $shortUrl */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'URL Shortener';

// Регистрируем JavaScript файл
$this->registerJsFile('@web/js/url-shortener.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">URL Shortener</h1>
        <p class="lead">Сократите любую ссылку быстро и легко</p>
    </div>

    <div class="body-content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Введите URL для сокращения</h5>

                        <?php $form = ActiveForm::begin([
                            'id' => 'url-form',
                            'enableClientValidation' => false,
                            'enableAjaxValidation' => false,
                            'validateOnSubmit' => false,
                            'options' => [
                                'data-ajax' => 'true',
                                'onsubmit' => 'return false;' // Полностью отключаем стандартную отправку
                            ],
                        ]); ?>

                        <?= $form->field($model, 'original_url')->textInput([
                            'placeholder' => 'https://example.com/very-long-url',
                            'class' => 'form-control form-control-lg',
                            'autofocus' => true,
                        ])->label('URL для сокращения:') ?>

                        <div class="form-group text-center">
                            <?= Html::submitButton('Сократить ссылку', [
                                'class' => 'btn btn-primary btn-lg',
                                'id' => 'shorten-btn'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                        <div id="ajax-result" style="display:none;"></div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <?= Html::a('Просмотреть аналитику', ['analytics'], ['class' => 'btn btn-info']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
