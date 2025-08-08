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

        // 0) Captura tudo que pode vir (nested e flat)
        $postedModel = $req->post($owner->formName(), []);
        $postedId    = $postedModel[$attr] ?? null;
        if ($postedId === null) {
            $postedId = $req->post($attr, null); // fallback nome plano
        }

        $removeFlag = $req->post($this->removeFlagParam, null);
        if ($removeFlag === null) {
            $removeFlag = $postedModel[$this->removeFlagParam] ?? 0; // remove dentro do modelo
        }
        $removeFlag = (int)$removeFlag;

        $this->log('incoming', ['postedId' => $postedId, 'remove' => $removeFlag]);

        // 1) Upload síncrono (file input no mesmo atributo)
        $uploaded = UploadedFile::getInstance($owner, $attr);
        if ($uploaded instanceof UploadedFile) {
            $this->log('sync upload detected', ['name' => $uploaded->name, 'type' => $uploaded->type]);
            $resp = StorageController::uploadFile($uploaded, ['save' => true, 'thumb_aspect' => 1]);
            if (!empty($resp['success'])) {
                $newId = (int)$resp['data']['id'];
                $owner->{$attr} = $newId;
                if ($this->deleteOldOnReplace && $this->oldId && $this->oldId != $newId) {
                    $this->toDeleteId = $this->oldId;
                }
                $this->log('sync upload ok', ['newId' => $newId, 'toDelete' => $this->toDeleteId]);
                return;
            }
            // Falha de upload: cancela o save e marca erro
            $owner->addError($attr, Yii::t('app', 'Falha ao enviar imagem.'));
            $event->isValid = false;
            $this->log('sync upload fail', ['resp' => $resp]);
            return;
        }

        // 2) Veio ID pelo hidden (widget assíncrono)
        if ($postedId !== null) {
            $postedId = trim((string)$postedId);
            if ($postedId === '' || $postedId === '0' || strtolower($postedId) === 'null') {
                // remoção
                if ($this->oldId) {
                    $this->toDeleteId = $this->oldId;
                }
                $owner->{$attr} = null;
                $this->log('hidden says remove', ['toDelete' => $this->toDeleteId]);
                return;
            }
            $postedId = (int)$postedId;
            if ($postedId !== (int)$this->oldId) {
                if ($this->deleteOldOnReplace && $this->oldId) {
                    $this->toDeleteId = $this->oldId;
                }
                $owner->{$attr} = $postedId;
                $this->log('hidden new id', ['newId' => $postedId, 'toDelete' => $this->toDeleteId]);
                return;
            }
            // igual ao antigo → manter
            $owner->{$attr} = $this->oldId;
            $this->log('hidden same id keep', ['id' => $this->oldId]);
            return;
        }

        // 3) Flag de remoção sem ID
        if ($removeFlag === 1) {
            if ($this->oldId) {
                $this->toDeleteId = $this->oldId;
            }
            $owner->{$attr} = null;
            $this->log('flag remove', ['toDelete' => $this->toDeleteId]);
            return;
        }

        // 4) Nada mudou → manter
        $owner->{$attr} = $this->oldId;
        $this->log('no change keep', ['id' => $this->oldId]);
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
