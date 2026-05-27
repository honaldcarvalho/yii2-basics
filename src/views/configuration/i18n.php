<?php

/** @var yii\web\View $this */
/** @var app\models\Configuration $model */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Automatic Translation Settings (i18n)');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Configurations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$pingUrl  = Url::to(['i18n-ping']);
$loginUrl = Url::to(['i18n-login']);
$syncUrl  = Url::to(['i18n-sync']);

// JS translation strings
$jsStrings = [
    'checking'       => Yii::t('app', 'Checking...'),
    'testConnection' => Yii::t('app', 'Test Connection'),
    'syncing'        => Yii::t('app', 'Syncing...'),
    'syncNow'        => Yii::t('app', 'Sync Local Database Now'),
    'authenticating' => Yii::t('app', 'Authenticating...'),
    'getNewToken'    => Yii::t('app', 'Get New Token'),
    'unexpectedErr'  => Yii::t('app', 'Unexpected error. Check the console.'),
];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3><?= Html::encode($this->title) ?></h3>
                        <p class="text-muted small mb-0">
                            <?= Yii::t('app', 'This application synchronizes local dictionaries with the <strong>Central Translation API</strong>. The process runs automatically every hour via cron, but you can force or reconfigure it here.') ?>
                        </p>
                    </div>
                    <div id="connection-indicator" class="d-flex align-items-center ms-3 flex-shrink-0">
                        <span class="badge bg-secondary rounded-pill me-2" id="status-label"><?= Yii::t('app', 'Checking...') ?></span>
                        <div id="status-dot" class="rounded-circle" style="width: 12px; height: 12px; background-color: #6c757d;"></div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?= Yii::t('app', 'Endpoint Configuration') ?></h5>
                                </div>
                                <?php $form = ActiveForm::begin(['id' => 'i18n-form']); ?>
                                <div class="card-body">
                                    <?= $form->field($model, 'i18n_api_url', [
                                        'template' => '<label class="form-label fw-bold">{label}</label>{input}{hint}{error}',
                                    ])->textInput([
                                        'id' => 'api-url',
                                        'placeholder' => 'https://i18n.example.com',
                                    ])->label(Yii::t('app', 'Central API URL')) ?>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold"><?= Yii::t('app', 'Authentication Token (Bearer)') ?></label>
                                        <div class="input-group">
                                            <?= Html::passwordInput('i18n_api_token_display', $model->i18n_api_token, [
                                                'id'          => 'api-token',
                                                'class'       => 'form-control',
                                                'placeholder' => Yii::t('app', 'Secret Token'),
                                            ]) ?>
                                            <button class="btn btn-outline-secondary" type="button" id="toggle-token">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <?= $form->field($model, 'i18n_api_token')->hiddenInput(['id' => 'api-token-hidden'])->label(false) ?>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                                        <button type="button" id="btn-ping" class="btn btn-outline-primary">
                                            <i class="fas fa-sync-alt me-1"></i><?= Yii::t('app', 'Test Connection') ?>
                                        </button>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                                            <i class="fas fa-sign-in-alt me-1"></i><?= Yii::t('app', 'Login to Central API') ?>
                                        </button>
                                    </div>
                                    <div id="feedback-area" class="mt-3"></div>
                                </div>
                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?= Yii::t('app', 'Manual Commands') ?></h5>
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted"><?= Yii::t('app', 'You can run the sync right now via terminal or by clicking the button below:') ?></p>
                                    <div class="callout callout-info p-2 rounded mb-3 font-monospace small">
                                        php yii translation-sync/pull
                                    </div>
                                    <button class="btn btn-info w-100" id="btn-sync-now">
                                        <i class="fas fa-cloud-download-alt me-1"></i>
                                        <?= Yii::t('app', 'Sync Local Database Now') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= Yii::t('app', 'Central Authentication') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><?= Yii::t('app', 'Username') ?></label>
                    <input type="text" id="login-user" class="form-control form-control-sm">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= Yii::t('app', 'Password') ?></label>
                    <input type="password" id="login-pass" class="form-control form-control-sm">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?= Yii::t('app', 'Cancel') ?></button>
                <button type="button" id="btn-do-login" class="btn btn-primary btn-sm"><?= Yii::t('app', 'Get New Token') ?></button>
            </div>
        </div>
    </div>
</div>

<?php
$strings = \yii\helpers\Json::encode($jsStrings);
$this->registerJs(<<<JS
var i18n = $strings;

function updateStatus(success, message) {
    var label = $('#status-label');
    var dot   = $('#status-dot');

    if (success) {
        label.text('Online').removeClass('bg-secondary bg-danger').addClass('bg-success');
        dot.css('background-color', '#28a745');
    } else {
        label.text('Offline').removeClass('bg-secondary bg-success').addClass('bg-danger');
        dot.css('background-color', '#dc3545');
    }
}

function showAlert(type, message) {
    var cls  = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
    var el   = $('<div class="alert ' + cls + ' alert-dismissible fade show mt-3" role="alert">' +
                 '<i class="' + icon + ' me-2"></i>' + message +
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    $('#feedback-area').empty().append(el);
}

function doPing() {
    var btn = $('#btn-ping');
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>' + i18n.checking);

    $.post('$pingUrl', {
        _csrf: yii.getCsrfToken(),
        url:   $('#api-url').val(),
        token: $('#api-token').val()
    }, function(res) {
        updateStatus(res.success, res.message);
        showAlert(res.success ? 'success' : 'error', res.message);
    }).fail(function() {
        updateStatus(false, i18n.unexpectedErr);
        showAlert('error', i18n.unexpectedErr);
    }).always(function() {
        btn.prop('disabled', false).html(originalHtml);
    });
}

// Initial Ping
doPing();

$('#btn-ping').click(doPing);

$('#btn-sync-now').click(function() {
    var btn = $(this);
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>' + i18n.syncing);

    $.post('$syncUrl', { _csrf: yii.getCsrfToken() }, function(res) {
        showAlert(res.success ? 'success' : 'error', res.message);
    }).fail(function() {
        showAlert('error', i18n.unexpectedErr);
    }).always(function() {
        btn.prop('disabled', false).html(originalHtml);
    });
});

$('#btn-do-login').click(function() {
    var btn = $(this);
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>' + i18n.authenticating);

    $.post('$loginUrl', {
        _csrf: yii.getCsrfToken(),
        url: $('#api-url').val(),
        username: $('#login-user').val(),
        password: $('#login-pass').val()
    }, function(res) {
        if (res.success) {
            $('#api-token').val(res.token);
            $('#api-token-hidden').val(res.token); // Update hidden field for form submission
            $('#loginModal').modal('hide');
            showAlert('success', res.message);
            doPing(); // Re-test connection with new token
        } else {
            showAlert('error', res.message);
        }
    }).fail(function() {
        showAlert('error', i18n.unexpectedErr);
    }).always(function() {
        btn.prop('disabled', false).html(originalHtml);
    });
});

$('#toggle-token').click(function() {
    var input = $('#api-token');
    var icon = $(this).find('i');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

// Sync display token with hidden real token
$('#api-token').on('input', function() {
    $('#api-token-hidden').val($(this).val());
});

JS
);
?>
