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

/**
 * AttachFileBehavior
 * ------------------
 * Mantém e troca o arquivo relacionado (ex.: campo `file_id`) sem “apagar sem querer”.
 *
 * Regras:
 * 1) Upload síncrono (modo defer — <input type="file" name="Model[file_id]">):
 *    - Se vier arquivo, faz o upload via StorageController::uploadFile(save=1) e seta o novo id.
 *    - Se `deleteOldOnReplace=true`, remove o arquivo antigo no AFTER_SAVE.
 *
 * 2) ID vindo por hidden (modo instant ou outro fluxo):
 *    - Se for inteiro válido diferente do antigo, troca e marca o antigo para remoção.
 *    - Se vier string vazia '', **NÃO remove**: apenas mantém o antigo.
 *    - Se vier '0' ou 'null', **só remove** se `removeFlagParam`=1 (ou se `emptyMeansRemove=true`).
 *
 * 3) Flag de remoção isolada (`removeFlagParam`=1) sem ID: zera o atributo e marca o antigo para remoção.
 *
 * 4) Caso nada tenha mudado, mantém o valor antigo.
 *
 * Dicas:
 * - Garanta que o form NÃO tenha um hidden `Model[file_id]` vazio por padrão.
 * - O widget deve mandar um hidden `remove=1` apenas quando o usuário clicar em “Remover”.
 */
class AttachFileBehavior extends Behavior
{
    /** atributo que guarda o id do File (ex.: file_id) */
    public string $attribute = 'file_id';

    /** nome da flag de remoção no POST (pode ser global ou aninhado em Model[remove]) */
    public string $removeFlagParam = 'remove';

    /** apaga o arquivo antigo ao trocar */
    public bool $deleteOldOnReplace = true;

    /** apaga o arquivo ao deletar o dono */
    public bool $deleteOnOwnerDelete = false;

    /** ligar logs (Yii::info) */
    public bool $debug = false;

    /** por padrão, vazio NÃO remove; se true, '' passa a significar remover (não recomendado) */
    public bool $emptyMeansRemove = false;

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

    private function log($msg, $data = []): void
    {
        if ($this->debug) {
            Yii::info(['attachFile' => $msg, 'data' => $data], 'attach.file');
        }
    }

    public function rememberOld(ModelEvent $event): void
    {
        $attr = $this->attribute;
        $this->oldId = $this->owner->getOldAttribute($attr) ?? $this->owner->{$attr};
        $this->log('rememberOld', ['oldId' => $this->oldId]);
    }

    public function handleUploadOrKeep(ModelEvent $event): void
    {
        $owner = $this->owner;
        $attr  = $this->attribute;
        $req   = Yii::$app->request;

        // Captura POST aninhado e plano
        $postedModel  = $req->post($owner->formName(), []);
        $hasPostedKey = array_key_exists($attr, $postedModel) || $req->post($attr, null) !== null;
        $postedId     = $hasPostedKey ? ($postedModel[$attr] ?? $req->post($attr, null)) : null;

        // Flag de remoção: tanto global quanto aninhado
        $removeFlag = (int)($req->post($this->removeFlagParam, $postedModel[$this->removeFlagParam] ?? 0));

        $this->log('incoming', [
            'hasPostedKey' => $hasPostedKey,
            'postedId'     => $postedId,
            'removeFlag'   => $removeFlag,
        ]);

        // 1) Upload síncrono (arquivo no mesmo atributo)
        $uploaded = UploadedFile::getInstance($owner, $attr);
        if ($uploaded instanceof UploadedFile) {
            $this->log('sync upload detected', ['name' => $uploaded->name, 'type' => $uploaded->type]);
            try {
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
                // Falha no contrato
                $this->log('sync upload fail', ['resp' => $resp]);
                $owner->addError($attr, Yii::t('app', 'Falha ao enviar imagem.'));
                $event->isValid = false;
                return;
            } catch (\Throwable $e) {
                $this->log('sync upload exception', ['err' => $e->getMessage()]);
                $owner->addError($attr, Yii::t('app', 'Falha ao enviar imagem.'));
                $event->isValid = false;
                return;
            }
        }

        // 2) ID vindo por hidden (widget assíncrono / instant / outro fluxo)
        if ($hasPostedKey) {
            $raw = trim((string)$postedId);

            // vazio: não trata como remoção; mantém o antigo
            if ($raw === '') {
                $owner->{$attr} = $this->oldId;
                $this->log('hidden empty -> keep old', ['oldId' => $this->oldId]);
                return;
            }

            // '0' ou 'null': só remove com flag explícita (ou se emptyMeansRemove=true)
            if ($raw === '0' || strtolower($raw) === 'null') {
                if ($removeFlag === 1 || $this->emptyMeansRemove) {
                    if ($this->oldId) $this->toDeleteId = $this->oldId;
                    $owner->{$attr} = null;
                    $this->log('hidden zero/null with removeFlag -> remove', ['toDelete' => $this->toDeleteId]);
                } else {
                    $owner->{$attr} = $this->oldId;
                    $this->log('hidden zero/null without removeFlag -> keep', ['oldId' => $this->oldId]);
                }
                return;
            }

            // novo id válido
            $newId = (int)$raw;
            if ($newId !== (int)$this->oldId) {
                if ($this->deleteOldOnReplace && $this->oldId) {
                    $this->toDeleteId = $this->oldId;
                }
                $owner->{$attr} = $newId;
                $this->log('hidden new id', ['newId' => $newId, 'toDelete' => $this->toDeleteId]);
            } else {
                $owner->{$attr} = $this->oldId;
                $this->log('hidden same id -> keep', ['id' => $this->oldId]);
            }
            return;
        }

        // 3) Flag de remoção isolada (sem ID)
        if ($removeFlag === 1) {
            if ($this->oldId) {
                $this->toDeleteId = $this->oldId;
            }
            $owner->{$attr} = null;
            $this->log('flag remove only', ['toDelete' => $this->toDeleteId]);
            return;
        }

        // 4) Nada mudou → mantém
        $owner->{$attr} = $this->oldId;
        $this->log('no change -> keep', ['id' => $this->oldId]);
    }

    public function deleteOldIfNeeded(AfterSaveEvent $event): void
    {
        // Segurança: não remover o id atual por engano
        $currentId = (int)$this->owner->{$this->attribute};
        if ($this->toDeleteId && (int)$this->toDeleteId !== $currentId) {
            $this->log('delete old', ['id' => $this->toDeleteId]);
            try {
                StorageController::removeFile($this->toDeleteId);
            } catch (\Throwable $e) {
                $this->log('delete old exception', ['err' => $e->getMessage()]);
            }
        }
        $this->toDeleteId = null;
    }

    public function deleteOnDelete(Event $event): void
    {
        if ($this->deleteOnOwnerDelete) {
            $id = (int)$this->owner->{$this->attribute};
            if ($id) {
                $this->log('delete on owner delete', ['id' => $id]);
                try {
                    StorageController::removeFile($id);
                } catch (\Throwable $e) {
                    $this->log('delete on owner delete exception', ['err' => $e->getMessage()]);
                }
            }
        }
    }
}
