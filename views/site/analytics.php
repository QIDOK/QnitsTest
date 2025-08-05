<?php

/** @var yii\web\View $this */
/** @var array $analytics */

use yii\helpers\Html;

$this->title = 'Аналитика переходов';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-analytics">
    <div class="row">
        <div class="col-lg-12">
            <h1><?= Html::encode($this->title) ?></h1>
            
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($analytics)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Месяц (перехода по ссылке)</th>
                                        <th>Ссылка</th>
                                        <th>Кол-во переходов</th>
                                        <th>Позиция в топе месяца по переходам</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics as $row): ?>
                                        <tr>
                                            <td><?= Html::encode($row['month']) ?></td>
                                            <td>
                                                <a href="<?= Html::encode($row['url']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 300px;">
                                                    <?= Html::encode($row['url']) ?>
                                                </a>
                                            </td>
                                            <td><?= Html::encode($row['clicks_count']) ?></td>
                                            <td><?= Html::encode($row['position']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <h4>Данных пока нет</h4>
                            <p>Статистика переходов появится после того, как пользователи начнут переходить по сокращенным ссылкам.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-3">
                <?= Html::a('Вернуться на главную', ['index'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
</div>
