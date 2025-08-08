<?php
namespace weebz\yii2basics\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Model;
use yii\base\ModelEvent;
use yii\base\Event;
use yii\db\BaseActiveRecord;
use yii\db\AfterSaveEvent;
use yii\web\UploadedFile;
use weebz\yii2basics\controllers\rest\StorageController;

class AttachFileBehavior extends Behavior
{
    /** atributo que guarda o id do File (ex.: file_id) */
    public string $attribute = 'file_id';

    /** nome da flag de remoção no POST */
    public string $removeFlagParam = 'remove';

    /** apaga o arquivo antigo ao trocar */
    public bool $deleteOldOnReplace = true;

    /** apaga o arquivo ao deletar o dono */
    public bool $deleteOnOwnerDelete = false;

    /** ligar logs (Yii::info) */
    public bool $debug = false;

    private $oldId;
    private $toDeleteId = null;
    public bool $emptyMeansRemove = false; // <- NOVO: por padrão, vazio NÃO remove

    public function events(): array
    {
        return [
            Model::EVENT_BEFORE_VALIDATE          => 'rememberOld',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'handleUploadOrKeep',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'handleUploadOrKeep',
            BaseActiveRecord::EVENT_AFTER_INSERT  => 'deleteOldIfNeeded',
            BaseActiveRecord::EVENT_AFTER_UPDATE  => 'deleteOldIfNeeded',
            BaseActiveRecord::EVENT_AFTER_DELETE  => 'deleteOnDelete',
        ];
    }

    private function log($msg, $data = [])
    {
        if ($this->debug) {
            Yii::info(['attachFile' => $msg, 'data' => $data], 'attach.file');
        }
    }

    public function rememberOld(ModelEvent $event): void
    {
        $this->oldId = $this->owner->getOldAttribute($this->attribute) ?? $this->owner->{$this->attribute};
        $this->log('rememberOld', ['oldId' => $this->oldId]);
    }

    public function handleUploadOrKeep(ModelEvent $event): void
    {
        $owner   = $this->owner;
        $attr    = $this->attribute;
        $req     = Yii::$app->request;

        $postedModel = $req->post($owner->formName(), []);
        $hasPostedKey = array_key_exists($attr, $postedModel) || $req->post($attr, null) !== null;
        $postedId     = $hasPostedKey ? ($postedModel[$attr] ?? $req->post($attr, null)) : null;

        $removeFlag = (int)($req->post($this->removeFlagParam, $postedModel[$this->removeFlagParam] ?? 0));

        // 1) Upload síncrono no próprio atributo (modo defer)
        $uploaded = UploadedFile::getInstance($owner, $attr);
        if ($uploaded instanceof UploadedFile) {
            $resp = StorageController::uploadFile($uploaded, ['save' => true, 'thumb_aspect' => 1]);
            if (!empty($resp['success'])) {
                $newId = (int)$resp['data']['id'];
                $owner->{$attr} = $newId;
                if ($this->deleteOldOnReplace && $this->oldId && $this->oldId != $newId) {
                    $this->toDeleteId = $this->oldId;
                }
                return;
            }
            $owner->addError($attr, Yii::t('app', 'Falha ao enviar imagem.'));
            $event->isValid = false;
            return;
        }

        // 2) ID vindo por hidden (widget assíncrono / instant)
        if ($hasPostedKey) {
            $raw = trim((string)$postedId);

            // ---- MUDANÇA: vazio NÃO é remoção — apenas ignore e mantenha o antigo
            if ($raw === '') {
                $owner->{$attr} = $this->oldId;
                return;
            }

            // '0'/'null' só remove se houver flag explícita
            if ($raw === '0' || strtolower($raw) === 'null') {
                if ($removeFlag === 1 || $this->emptyMeansRemove) {
                    if ($this->oldId) $this->toDeleteId = $this->oldId;
                    $owner->{$attr} = null;
                } else {
                    $owner->{$attr} = $this->oldId;
                }
                return;
            }

            // Novo id válido
            $newId = (int)$raw;
            if ($newId !== (int)$this->oldId) {
                if ($this->deleteOldOnReplace && $this->oldId) $this->toDeleteId = $this->oldId;
                $owner->{$attr} = $newId;
            } else {
                $owner->{$attr} = $this->oldId;
            }
            return;
        }

        // 3) Flag de remoção isolada
        if ($removeFlag === 1) {
            if ($this->oldId) $this->toDeleteId = $this->oldId;
            $owner->{$attr} = null;
            return;
        }

        // 4) Nada mudou → mantém
        $owner->{$attr} = $this->oldId;
    }

    public function deleteOldIfNeeded(AfterSaveEvent $event): void
    {
        if ($this->toDeleteId) {
            $this->log('delete old', ['id' => $this->toDeleteId]);
            StorageController::removeFile($this->toDeleteId);
            $this->toDeleteId = null;
        }
    }

    public function deleteOnDelete(Event $event): void
    {
        if ($this->deleteOnOwnerDelete) {
            $id = (int)$this->owner->{$this->attribute};
            if ($id) {
                $this->log('delete on owner delete', ['id' => $id]);
                StorageController::removeFile($id);
            }
        }
    }
}
