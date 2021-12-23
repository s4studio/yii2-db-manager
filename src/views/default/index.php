<?php

use yii\grid\GridView;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

use s4studio\dbManager\models\BaseDumpManager;

/* @var $this yii\web\View */
/* @var array $dbList */
/* @var array $activePids */
/* @var \s4studio\dbManager\models\Dump $model */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = Yii::t('dbManager', 'DB manager');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-md-3">
        <h3 class="">
            <?= Yii::t('dbManager', 'Create dump') ?>
        </h3>

        <?php $form = ActiveForm::begin([
            'action' => ['create'],
            'method' => 'post',
            //'layout' => 'horizontal'
        ]) ?>
        <?= $form->field($model, 'db')->dropDownList(array_combine($dbList, $dbList), ['prompt' => ''])->label(Yii::t('dbManager', 'Database')) ?>

        <?= $form->field($model, 'isArchive', [
            'horizontalCssClasses' => [
                'label' => 'col-sm-4 text-left text-sm-right pt-2',
                'wrapper' => 'col-sm-8 pt-2',
            ],
        ])->checkbox()->label(Yii::t('dbManager', 'gzip')) ?>

        <?= $form->field($model, 'schemaOnly')->checkbox()->label(Yii::t('dbManager', 'Dump only schema')) ?>

        <?php if (!BaseDumpManager::isWindows()) {
            echo $form->field($model, 'runInBackground')->checkbox()->label(Yii::t('dbManager', 'Run in background'));
        } ?>

        <?php if ($model->hasPresets()): ?>
            <?= $form->field($model, 'preset')->dropDownList($model->getCustomOptions(), ['prompt' => ''])->label(Yii::t('dbManager', 'Custom dump preset')) ?>
        <?php endif ?>

        <?= Html::submitButton(Yii::t('dbManager', 'Create dump'), ['class' => 'btn btn-success']) ?>
        <?php ActiveForm::end() ?>

    </div>
    <div class="col-md-9">
        <div class="row">
            <div class="col-md-9">
                <h3 class="">
                    <?= Html::encode($this->title) ?>
                </h3>
            </div>
            <div class="col-md-3">
                <?= Html::a(Yii::t('dbManager', 'Delete all'),
                    ['delete-all'],
                    [
                        'class' => 'btn btn-danger btn-sm btn-icon btn-icon-md',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('dbManager', 'Are you sure?'),
                        'title' => Yii::t('dbManager', 'Delete all'),
                    ]
                ) ?>
            </div>
        </div>


        <?php if (!empty($activePids)): ?>
            <div class="alert alert-warning">
                <h4><?= Yii::t('dbManager', 'Active processes:') ?></h4>
                <?php foreach ($activePids as $pid => $cmd): ?>
                    <b><?= $pid ?></b>: <?= $cmd ?><br>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'pjax' => false,
            'columns' => [
                [
                    'attribute' => 'type',
                    'label' => Yii::t('dbManager', 'Type'),
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('dbManager', 'Name'),
                ],
                [
                    'attribute' => 'size',
                    'label' => Yii::t('dbManager', 'Size'),
                ],
                [
                    'attribute' => 'create_at',
                    'label' => Yii::t('dbManager', 'Create time'),
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{download} {restore} {storage} {delete}',
                    'buttons' => [
                        'download' => function ($url, $model) {
                            return Html::a(Icon::show('download'),
                                [
                                    'download',
                                    'id' => $model['id'],
                                ],
                                [
                                    'title' => Yii::t('dbManager', 'Download'),
                                    'class' => 'text-default mr-3',
                                ]);
                        },
                        'restore' => function ($url, $model) {
                            return Html::a(Icon::show('rev', ['framework' => Icon::FAB]),
                                [
                                    'restore',
                                    'id' => $model['id'],
                                ],
                                [
                                    'title' => Yii::t('dbManager', 'Restore'),
                                    'class' => 'text-success mr-3',
                                ]);
                        },
                        'storage' => function ($url, $model) {
                            if (Yii::$app->has('backupStorage')) {
                                $exists = Yii::$app->backupStorage->has($model['name']);

                                return Html::a(Icon::show('upload'),
                                    [
                                        'storage',
                                        'id' => $model['id'],
                                    ],
                                    [
                                        'title' => $exists ? Yii::t('dbManager', 'Delete from storage') : Yii::t('dbManager', 'Upload from storage'),
                                        'class' => $exists ? 'text-danger mr-3' : 'text-success mr-3',
                                    ]);
                            }
                        },
                        'delete' => function ($url, $model) {
                            return Html::a(Icon::show('trash'),
                                [
                                    'delete',
                                    'id' => $model['id'],
                                ],
                                [
                                    'role' => 'modal-remote',
                                    'title' => Yii::t('dbManager', 'Delete'),
                                    'class' => 'text-danger',
                                    'data-confirm' => false,
                                    'data-method' => false,// for overide yii data api
                                    'data-confirm-ok' => 'Usuń',
                                    'data-confirm-cancel' => 'Anuluj',
                                    'data-request-method' => 'post',
                                    'data-toggle' => 'tooltip',
                                    'data-confirm-title' => Yii::t('dbManager', 'Are you sure?'),
                                    'data-confirm-message' => 'Na pewno chcesz usunąć ten element?'
                                ]);
                        },
                    ],
                ],
            ],
        ]) ?>

    </div>
</div>
