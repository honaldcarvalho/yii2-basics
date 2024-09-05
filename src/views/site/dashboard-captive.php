<?php

/** @var yii\web\View $this */

use weebz\yii2basics\controllers\ AuthController;
use weebz\yii2basics\models\custom\DashboarSearch;
use weebz\yii2basics\models\Params;

$params = Params::get();
//dd($registrationPerDay );

if (!empty($params->file_id)) {
    $url = Yii::getAlias('@web') . $params->file->urlThumb;
    $logo_image = "<img alt='{$params->title}' width='150px' class='brand-image img-circle elevation-3' src='{$url}' style='opacity: .8' />";
} else {
    $logo_image = "<img src='/images/logo_weebz.png' width='150px' alt='{$params->title}' class='brand-image img-circle elevation-3' style='opacity: .8'>";
}

$this->title = 'Dashboard';

?>

<div class="site-index">

    <div class="body-content">

        <div class="row">

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-weebz elevation-1 position-relative">
                        <div class="position-absolute bottom-0 right-5"><i class="fas fa-users" style="color: #fff;"></i></div>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= Yii::t('app', 'Registered Visitors'); ?></span>
                        <span class="info-box-number">
                            <?= $visitors; ?><small></small>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-weebz elevation-1 position-relative">
                        <div class="position-absolute bottom-0 right-5"><i class="fas fa-user" style="color: #fff;"></i></div>
                        <i class="fas fa-plus position-absolute" style="color: #fff;font-size:0.5em;top:0.5em;left:0.5em;"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= Yii::t('app', 'New Visitors'); ?></span>
                        <span class="info-box-number">
                            <?= $visitors_day; ?><small></small>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-weebz elevation-1 position-relative">
                        <div class="position-absolute bottom-0 right-5"><i class="fas fa-calendar-alt" style="color: #fff;"></i></div>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= Yii::t('app', 'Registered Month'); ?></span>
                        <span class="info-box-number">
                            <?= $visitors_month; ?><small></small>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-weebz elevation-1 position-relative">
                        <div class="position-absolute bottom-0 right-5"><i class="fas fa-globe" style="color: #fff;"></i></div>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= Yii::t('app', 'Users Online'); ?></span>
                        <span class="info-box-number">
                            <?= $onlineUsers; ?><small></small>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-weebz elevation-1 position-relative">
                        <div class="position-absolute bottom-0 right-5"><i class="fas fa-undo" style="color: #fff;"></i></div>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= Yii::t('app', 'Returning Users'); ?></span>
                        <span class="info-box-number">
                            <?= $returningUsers; ?><small></small>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-weebz elevation-1 position-relative">
                        <div class="position-absolute bottom-0 right-5"><i class="fas fa-user-clock" style="color: #fff;"></i></div>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= Yii::t('app', 'Active in Last 24h Users'); ?></span>
                        <span class="info-box-number">
                            <?= $userActiveLast24; ?><small></small>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-12 col-sm-6 col-md-6">

                <div class="card card-outline card-weebz">
                    <div class="card-header">
                        <h3 class="card-title"><?=Yii::t('app', 'Registrations Per Day')?></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <?= $this->render('_charts/registrations_per_day',['series'=>$registrationPerDay]); ?>
                    </div>
                    <!--.card-body-->
                </div>
                <!--.card-->
            </div>
            <div class="col-md-6">
                <div class="col-md-12">

                    <div class="card card-outline card-weebz">

                        <div class="card-header">
                            <h3 class="card-title"><?=Yii::t('app', 'Operational System (%)')?></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <?= $this->render('_charts/visitors_os',['series'=>$accessPerOs]); ?>
                        </div>

                    </div>

                </div>

                <div class="col-md-12">

                    <div class="card card-outline card-weebz">
                        <div class="card-header">
                            <h3 class="card-title"><?=Yii::t('app', 'Device Type (%)')?></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <?= $this->render('_charts/visitors_device_type',['series'=>$accessPerDevice]); ?>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>
</div>