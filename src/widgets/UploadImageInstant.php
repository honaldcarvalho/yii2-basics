<?php

namespace weebz\yii2basics\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use weebz\yii2basics\themes\adminlte3\assets\PluginAsset;

/**
 * UploadImageInstant — Widget de upload com preview, CropperJS e compressão client-side.
 *
 * FUNCIONAMENTO
 * ─────────────
 * • mode = 'defer'  (padrão)
 *   - Não envia nada imediatamente.
 *   - Após cortar, o arquivo é INJETADO no <input type="file" name="Model[file_id]"> do formulário.
 *   - Ao salvar o form, sua lógica/Behavior faz o upload no backend, grava o file_id e remove o antigo.
 *
 * • mode = 'instant'
 *   - Envia de imediato para /rest/storage/send (StorageController::actionSend).
 *   - Com o StorageController alterado, é possível JÁ VINCULAR ao modelo enviando:
 *       model_class, model_id, model_field e (opcional) delete_old=1
 *   - O widget também preenche um <input type="hidden" name="Model[file_id]"> com o id retornado (fallback).
 *
 * AUTENTICAÇÃO (modo instant)
 * ───────────────────────────
 * O widget envia Authorization: Bearer {token} buscando, nesta ordem:
 *   1) <meta name="api-token" content="...">
 *   2) Propriedade PHP $authToken
 *   3) localStorage['token']
 *   4) window.AUTH_TOKEN
 *   5) cookie "token"
 * Se nada encontrado e authQueryFallback=true, acrescenta ?access-token= na URL.
 *
 * PARÂMETROS PRINCIPAIS
 * ──────────────────────
 * • mode: 'defer' | 'instant'              (padrão: 'defer')
 * • model: ActiveRecord (opcional, recomendado)
 * • attribute: string                      (ex.: 'file_id')
 * • imageUrl: string                       (URL do preview inicial)
 * • aspectRatio: '1' | '16/9' | 'NaN'      (NaN = livre)
 * • maxWidth (px), maxSizeMB (MB)          (compressão client-side)
 * • sendUrl, removeUrl                     (modo instant; padrões: /rest/storage/send e /rest/storage/remove-file)
 * • linkModelOnSend: bool                  (instant → envia model_* p/ vincular no StorageController)
 * • deleteOldOnReplace: bool               (instant → remove file antigo ao trocar)
 * • attactModelClass, attactModelFields    (opcional; cria pivot via attact_model junto do upload)
 * • authToken / meta api-token / storage 'token' / cookie 'token'
 *
 * COMO USAR
 * ─────────
 * A) CREATE/UPDATE simples (recomendado): mode='defer'
 *    - O arquivo é injetado no <input type="file"> e só segue ao salvar o form.
 *
 *    // No form:
 *    <?= $form->field($model, 'file_id')->fileInput([
 *         'id' => Html::getInputId($model, 'file_id'),
 *         'accept' => 'image/*', 'style' => 'display:none'
 *       ])->label(false); ?>
 *
 *    <?= \weebz\yii2basics\widgets\UploadImageInstant::widget([
 *         'mode'        => 'defer',
 *         'hideSaveButton' => true,             // só “Cortar” e fechar
 *         'model'       => $model,
 *         'attribute'   => 'file_id',
 *         'fileInputId' => Html::getInputId($model, 'file_id'),
 *         'imageUrl'    => $model->file->url ?? '',
 *         'aspectRatio' => '16/9',
 *    ]) ?>
 *
 *    // No backend (exemplo): Behavior para tratar o UploadedFile no save
 *    // - cria File, atualiza file_id, remove antigo se trocar.
 *
 * B) UPDATE sem submit do form: mode='instant' (vincula já no upload)
 *
 *    <?= \weebz\yii2basics\widgets\UploadImageInstant::widget([
 *         'mode'                => 'instant',
 *         'model'               => $model,           // precisa ter PK
 *         'attribute'           => 'file_id',
 *         'imageUrl'            => $model->file->url ?? '',
 *         'aspectRatio'         => '16/9',
 *         'linkModelOnSend'     => true,            // envia model_class/id/field
 *         'deleteOldOnReplace'  => true,            // apaga antigo ao trocar
 *         'authToken'           => Yii::$app->user->identity->access_token ?? null,
 *    ]) ?>
 *
 *    // O widget enviará para /rest/storage/send:
 *    //   file=..., save=1, folder_id, group_id, thumb_aspect, quality,
 *    //   model_class=\backend\models\Captive, model_id=<PK>, model_field=file_id, delete_old=1
 *    // O StorageController salva o File, grava Captive.file_id e opcionalmente remove o antigo.
 *
 * C) Upload avulso (sem modelo) no instant
 *
 *    <?= \weebz\yii2basics\widgets\UploadImageInstant::widget([
 *         'mode'        => 'instant',
 *         'imageUrl'    => '',
 *         'aspectRatio' => '1',
 *    ]) ?>
 *
 *    <script>
 *      document.addEventListener('uploadImage:saved', (e) => {
 *        console.log('Arquivo salvo:', e.detail.file); // {id, url, urlThumb, ...}
 *      });
 *    </script>
 *
 * REMOÇÃO
 * ───────
 * • defer: o botão “Remover” apenas limpa o input/hidden (remover antigo fica a cargo do backend no submit).
 * • instant: tenta POST /rest/storage/remove-file?id=<fileId> e limpa o hidden/preview local.
 *
 * EVENTOS JS
 * ──────────
 * • uploadImage:pending — emitido no defer após cortar (arquivo já no input file do form).
 * • uploadImage:saved   — emitido no instant após upload ok:
 *      document.addEventListener('uploadImage:saved', (e) => {
 *        const file = e.detail.file; // { id, url, ... }
 *      });
 *
 * STORAGECONTROLLER — contrato esperado (/rest/storage/send)
 * ─────────────────────────────────────────────────────────
 * • Obrigatório: file (multipart). Use save=1 para retornar o ID do File.
 * • Úteis: folder_id, group_id, thumb_aspect, quality.
 * • Vínculo direto ao modelo (novo):
 *     model_class, model_id, model_field, delete_old(0/1)
 * • Pivot opcional:
 *     attact_model (JSON: {class_name, fields:['model_id','file_id'], id:<PK do seu modelo>})
 *
 * DICAS / TROUBLESHOOTING
 * ───────────────────────
 * • “Não vinculou no instant”: verifique se o registro já tem PK e se linkModelOnSend=true.
 *   Cheque no DevTools se model_class/model_id/model_field estão no FormData do POST.
 * • “401/403”: confira o token (meta, prop PHP, localStorage, cookie). O widget também pode usar ?access-token=.
 * • “Corte fora do centro/tela”: o modal é limitado a ~92vh; o widget centraliza a crop box automaticamente.
 */

class UploadImageInstant extends \yii\bootstrap5\Widget
{
  /** Preview inicial */
  public string $imageUrl = '';
  public string $accept = 'image/*';

  /** '1', '16/9' ou 'NaN' (livre) */
  public string $aspectRatio = '1';

  /** Compressão client-side */
  public int $maxWidth = 1200;
  public float $maxSizeMB = 3.0;

  /** Modo de envio: 'defer' (padrão) ou 'instant' */
  public string $mode = 'defer';

  /** Esconder o botão "Salvar" do modal? (no 'defer' o "Cortar" já injeta e fecha) */
  public bool $hideSaveButton = true;

  /** Endpoints (usados no modo 'instant') */
  public $sendUrl        = ['/rest/storage/send'];
  public $removeUrl      = ['/rest/storage/remove-file'];     // POST + ?id=
  public $updateFieldUrl = ['/rest/controller-custom/update-field']; // opcional (não usado no link direto)

  /** Associação com o modelo/atributo (para descobrir name/id corretos) */
  public $model = null;               // \yii\db\ActiveRecord|null
  public string $attribute = 'file_id';

  /** (Opcional) se quiser forçar o id do input file do modelo (ex.: 'captive-file_id') */
  public ?string $fileInputId = null;

  /** (Opcional) pivot via attact_model (usado no 'instant') */
  public ?string $attactModelClass = null;
  public array $attactModelFields = [];

  /** Parâmetros de envio */
  public int $folderId = 2;
  public int $groupId  = 1;
  public $thumbAspect = 1;  // 1 ou "L/H" (ex.: "160/99")
  public int $quality  = 85;

  /** Ao usar modo 'instant', envia model_class/model_id/model_field e manda apagar o antigo? */
  public bool $linkModelOnSend   = true;
  public bool $deleteOldOnReplace = true;

  /** Labels */
  public string $labelSelect = 'Selecione a imagem';
  public string $labelCrop   = 'Cortar';
  public string $labelSave   = 'Salvar';
  public string $labelCancel = 'Cancelar';
  public string $labelRemove = 'Remover';

  public string $placeholder = '/dummy/code.php?x=250x250/fff/000.jpg&text=NO IMAGE';

  /** Auth (modo 'instant') */
  public ?string $authToken = null;
  public string $authMetaName = 'api-token';
  public string $authStorageKey = 'token';

  /** Lê token do cookie também (nome padrão "token") */
  public string $authCookieName = 'token';

  public bool $withCredentials = true;
  public bool $authQueryFallback = true;

  public function init(): void
  {
    parent::init();
    PluginAsset::register(Yii::$app->view)->add(['cropper']);

    if ($this->model !== null && !method_exists($this->model, 'hasAttribute')) {
      throw new InvalidConfigException('Parâmetro $model deve ser um ActiveRecord ou null.');
    }
    if (!in_array($this->mode, ['defer', 'instant'], true)) {
      throw new InvalidConfigException('Parâmetro $mode deve ser "defer" ou "instant".');
    }
  }

  public function run(): string
  {
    $view = $this->getView();
    $id = $this->getId();

    $wrapId   = "uii_wrap_{$id}";
    $photoId  = "uii_photo_{$id}";
    $imgId    = "uii_img_{$id}";
    $inputId  = "uii_input_{$id}";
    $modalId  = "uii_modal_{$id}";
    $cropId   = "uii_crop_{$id}";
    $saveId   = "uii_save_{$id}";
    $cancelId = "uii_cancel_{$id}";
    $removeId = "uii_remove_{$id}";
    $overlayId = "uii_overlay_{$id}";

    $initialUrl = $this->imageUrl ?: $this->placeholder;

    // CSRF
    $csrfParam = Yii::$app->request->csrfParam;
    $csrfToken = Yii::$app->request->getCsrfToken();

    // MODEL
    $haveModel  = $this->model !== null && $this->model->hasAttribute($this->attribute);
    $modelClass = $haveModel ? addslashes(get_class($this->model)) : '';
    $modelId    = $haveModel ? (string)$this->model->getPrimaryKey() : '';
    $modelAttr  = $this->attribute;

    // URLs
    $sendUrl        = Url::to($this->sendUrl);
    $removeUrl      = Url::to($this->removeUrl);
    $updateFieldUrl = Url::to($this->updateFieldUrl);

    // Nome/ID do input file do modelo
    $inputName   = $haveModel ? Html::getInputName($this->model, $this->attribute) : 'file_id';
    $inputIdPhp  = $this->fileInputId ?: ($haveModel ? Html::getInputId($this->model, $this->attribute) : 'uii_file_' . $id);

    // attact_model opcional
    $attactClass  = $this->attactModelClass ? addslashes($this->attactModelClass) : '';
    $attactFields = $this->attactModelFields;

    // Opções JS
    $aspect  = $this->aspectRatio;
    $maxW    = (int)$this->maxWidth;
    $maxMB   = (float)$this->maxSizeMB;
    $folder  = (int)$this->folderId;
    $group   = (int)$this->groupId;
    $thumb   = is_numeric($this->thumbAspect) ? (int)$this->thumbAspect : "'{$this->thumbAspect}'";
    $quality = (int)$this->quality;

    // Auth
    $authToken         = addslashes((string)($this->authToken ?? ''));
    $authMetaName      = addslashes($this->authMetaName);
    $authStorageKey    = addslashes($this->authStorageKey);
    $authCookieName    = addslashes($this->authCookieName);
    $withCreds         = $this->withCredentials ? 'include' : 'same-origin';
    $authQueryFallback = $this->authQueryFallback ? 'true' : 'false';

    $mode              = $this->mode;
    $hideSaveButton    = $this->hideSaveButton ? 'true' : 'false';
    $linkOnSend        = $this->linkModelOnSend ? 'true' : 'false';
    $deleteOld         = $this->deleteOldOnReplace ? 'true' : 'false';

    // ===== CSS =====
    $css = <<<CSS
.uploader-card .overlay{
  position:absolute; inset:0; display:none; align-items:center; justify-content:center; z-index:1055;
  background:rgba(0,0,0,.35);
}
.uploader-card .preview{ max-width:600px; }

/* Modal ocupa viewport sem estourar */
.uploader-modal .modal-dialog{ max-width:min(95vw, 1200px); margin:1rem auto; }
.uploader-modal .modal-content{ max-height:92vh; }
.uploader-modal .modal-body{ padding:0; overflow:hidden; }

/* Área do crop com altura explícita baseada no viewport */
.uploader-modal .img-container{
  position:relative;
  width:100%;
  height:calc(92vh - 140px); /* ~ header+footer */
  background: conic-gradient(#eee 0 25%, #ddd 0 50%) 0 / 20px 20px;
}
.uploader-modal .img-container img{
  display:block;
  max-width:100%;
}
.btn-group .btn, .btn-group label.btn { padding-top: .5rem; padding-bottom: .5rem; }
CSS;

    // ===== JS =====
    $script = <<<JS
(function(){
  // ---- AUTH HELPERS (modo 'instant') ----
  const AUTH_TOKEN_FROM_PHP = '{$authToken}';
  const AUTH_META_NAME      = '{$authMetaName}';
  const AUTH_STORAGE_KEY    = '{$authStorageKey}';
  const AUTH_COOKIE_NAME    = '{$authCookieName}';
  const WITH_CREDS          = '{$withCreds}';
  const AUTH_QUERY_FALLBACK = {$authQueryFallback};

  const MODEL_CLASS = '{$modelClass}';
  const MODEL_ID    = '{$modelId}';
  const MODEL_ATTR  = '{$modelAttr}';
  const LINK_ON_SEND = {$linkOnSend};
  const DELETE_OLD   = {$deleteOld};

  function getCookie(name){
    return document.cookie
      .split(';').map(s=>s.trim())
      .find(s=>s.startsWith(name+'='))
      ?.split('=').slice(1).join('=') || '';
  }
  function getAuthToken(){
    const meta  = document.querySelector(`meta[name="\${AUTH_META_NAME}"]`)?.content?.trim();
    const php   = AUTH_TOKEN_FROM_PHP || '';
    const ls    = localStorage.getItem(AUTH_STORAGE_KEY) || '';
    const wnd   = window.AUTH_TOKEN || '';
    const cook  = decodeURIComponent(getCookie(AUTH_COOKIE_NAME) || '');
    return meta || php || ls || wnd || cook || '';
  }
  function commonHeaders() {
    const h = { 'Accept': 'application/json' };
    const t = getAuthToken();
    if (t) h['Authorization'] = 'Bearer ' + t;
    return h;
  }
  function withAccessToken(url){
    if (!AUTH_QUERY_FALLBACK) return url;
    const t = getAuthToken();
    if (!t) return url;
    const sep = url.includes('?') ? '&' : '?';
    return url + sep + 'access-token=' + encodeURIComponent(t);
  }

  // ---- DOM ----
  const wrap    = document.getElementById('$wrapId');
  const photo   = document.getElementById('$photoId');
  const imageEl = document.getElementById('$imgId');
  const input   = document.getElementById('$inputId');
  const overlay = document.getElementById('$overlayId');

  const btnCrop   = document.getElementById('$cropId');
  const btnSave   = document.getElementById('$saveId'); btnSave.style.display = 'none';
  const btnCancel = document.getElementById('$cancelId');
  const btnRemove = document.getElementById('$removeId');

  const modalEl = document.getElementById('$modalId');
  const modal = new bootstrap.Modal(modalEl, {backdrop:'static', keyboard:false});

  const MODE = '{$mode}';
  const HIDE_SAVE_BTN = {$hideSaveButton};

  // Input file REAL do modelo (apenas para 'defer')
  const MODEL_INPUT_ID = '{$inputIdPhp}';
  const MODEL_INPUT_NAME = '{$inputName}';
  function ensureModelFileInput() {
    let el = document.getElementById(MODEL_INPUT_ID);
    if (el && el.type === 'file') return el;

    el = document.querySelector(`input[type="file"][name="\${CSS.escape(MODEL_INPUT_NAME)}"]`);
    if (el) return el;

    const form = wrap.closest('form');
    if (!form) return null;
    el = document.createElement('input');
    el.type = 'file';
    el.name = MODEL_INPUT_NAME;
    el.id = MODEL_INPUT_ID;
    el.style.display = 'none';
    form.appendChild(el);
    return el;
  }
  const modelFileInput = (MODE === 'defer') ? ensureModelFileInput() : null;

  // No modo 'instant', guardamos um hidden Model[field] com o id retornado
  let hidden = null;
  if (MODE === 'instant') {
    hidden = document.querySelector(`input[type="hidden"][name="\${CSS.escape(MODEL_INPUT_NAME)}"]`);
    if (!hidden) {
      const form = wrap.closest('form');
      if (form) {
        hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = MODEL_INPUT_NAME;
        hidden.value = '';
        form.appendChild(hidden);
      }
    }
    const fileSameName = document.querySelector(`input[type="file"][name="\${CSS.escape(MODEL_INPUT_NAME)}"]`);
    if (fileSameName) fileSameName.name = MODEL_INPUT_NAME + '__ignore';
  }

  // ---- CONFIG ----
  const CSRF_PARAM = '{$csrfParam}';
  const CSRF_TOKEN = '{$csrfToken}';

  const MAX_W = {$maxW};
  const MAX_MB = {$maxMB};
  const MAX_BYTES = MAX_MB * 1024 * 1024;
  const ASPECT = (function(){ try { return eval('{$aspect}'); } catch(e){ return NaN; }})();

  const SEND_URL        = '{$sendUrl}';
  const UPDATE_FIELD_URL= '{$updateFieldUrl}';
  const REMOVE_URL_BASE = '{$removeUrl}';

  const FOLDER_ID   = {$folder};
  const GROUP_ID    = {$group};
  const THUMB_ASPECT= {$thumb};
  const QUALITY     = {$quality};

  const attactClass  = '{$attactClass}';
  const attactFields = JSON.parse('{$this->jsonSafe($attactFields)}');

  let tmpFile = null;
  let cropper = null;
  let lastSavedFileId = hidden?.value || null;

  function showOverlay(){ overlay.style.display='flex'; }
  function hideOverlay(){ overlay.style.display='none'; }

  function isImage(file){
    return ["image/jpeg","image/png","image/gif","image/bmp","image/webp"].includes(file.type);
  }

  function compressImage(file){
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = e => {
        const img = new Image();
        img.onload = () => {
          let w = img.width, h = img.height;
          if (w > MAX_W || h > MAX_W) {
            if (w > h) { h = Math.floor(h * MAX_W / w); w = MAX_W; }
            else { w = Math.floor(w * MAX_W / h); h = MAX_W; }
          }
          const canvas = document.createElement('canvas');
          canvas.width = w; canvas.height = h;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img,0,0,w,h);
          canvas.toBlob((blob) => {
            if (!blob) return reject('Falha ao comprimir.');
            if (blob.size > MAX_BYTES) return reject('Imagem excede ' + MAX_MB + 'MB mesmo após compressão.');
            resolve(new File([blob], file.name, {type: file.type, lastModified: Date.now()}));
          }, file.type, 0.85);
        };
        img.onerror = () => reject('Erro ao carregar a imagem.');
        img.src = e.target.result;
      };
      reader.onerror = () => reject('Erro ao ler o arquivo.');
      reader.readAsDataURL(file);
    });
  }

  function fitAndCenter() {
    if (!cropper) return;
    const cont = cropper.getContainerData();
    let w, h;
    if (Number.isFinite(ASPECT)) {
      w = Math.min(cont.width * 0.92, cont.height * 0.92 * ASPECT);
      h = w / ASPECT;
    } else {
      w = cont.width * 0.92;
      h = cont.height * 0.92;
    }
    cropper.setCropBoxData({
      width:  w,
      height: h,
      left:   (cont.width  - w) / 2,
      top:    (cont.height - h) / 2
    });
  }

  function assignFileToModelInput(file) {
    if (!modelFileInput) return false;
    const dt = new DataTransfer();
    dt.items.add(file);
    modelFileInput.files = dt.files;
    return true;
  }

  async function uploadFinalFile(blobOrFile){
    const fd = new FormData();
    const fileName = (tmpFile?.name || 'image.jpg');
    const file = (blobOrFile instanceof File) ? blobOrFile : new File([blobOrFile], fileName, {type: blobOrFile.type || 'image/jpeg', lastModified: Date.now()});
    fd.append('file', file);
    fd.append('save', '1'); // precisa salvar para ter ID
    fd.append('folder_id', String(FOLDER_ID));
    fd.append('group_id', String(GROUP_ID));
    fd.append('thumb_aspect', String(THUMB_ASPECT));
    fd.append('quality', String(QUALITY));
    fd.append(CSRF_PARAM, CSRF_TOKEN);

    // --- NOVO: link direto no StorageController (model_class/model_id/model_field/delete_old) ---
    if (LINK_ON_SEND && MODEL_CLASS && MODEL_ID && MODEL_ATTR) {
      fd.append('model_class', MODEL_CLASS);
      fd.append('model_id',    MODEL_ID);
      fd.append('model_field', MODEL_ATTR);
      fd.append('delete_old',  DELETE_OLD ? '1' : '0');
    }

    // (Opcional) pivot adicional
    if (attactClass && attactFields.length === 2 && MODEL_ID) {
      const payload = { class_name: attactClass, fields: attactFields, id: MODEL_ID };
      fd.append('attact_model', JSON.stringify(payload));
    }

    const res = await fetch(withAccessToken(SEND_URL), {
      method: 'POST',
      body: fd,
      headers: commonHeaders(),
      credentials: WITH_CREDS,
    });
    if(!res.ok) throw new Error('Falha no upload ('+res.status+').');
    const json = await res.json();
    if (!json || json.success !== true) {
      const msg = (json && json.data) ? JSON.stringify(json.data) : 'Resposta inválida.';
      throw new Error('Upload não aceito: ' + msg);
    }
    return json.data; // modelo File
  }

  // ---- Eventos ----
  input.addEventListener('change', async (e) => {
    const files = e.target.files;
    if(!files || !files.length) return;
    tmpFile = files[0];
    if (!isImage(tmpFile)) { alert('Arquivo inválido.'); return; }

    try{
      showOverlay();
      let toPreview;
      if (tmpFile.type !== 'image/png') {
        const compressed = await compressImage(tmpFile);
        toPreview = URL.createObjectURL(compressed);
        tmpFile = compressed;
      } else {
        toPreview = URL.createObjectURL(tmpFile);
      }
      imageEl.src = toPreview;
      btnSave.style.display = 'block';
      modal.show();
    } catch(err){
      alert(err);
    } finally {
      hideOverlay();
    }
  });

  modalEl.addEventListener('shown.bs.modal', () => {
    if (cropper) { cropper.destroy(); cropper = null; }
    cropper = new Cropper(imageEl, {
      viewMode: 2,
      aspectRatio: ASPECT,
      initialAspectRatio: ASPECT,
      autoCropArea: 1,
      responsive: true,
      background: false,
      dragMode: 'move',
      zoomOnWheel: true,
      ready() { setTimeout(fitAndCenter, 0); }
    });
    if (HIDE_SAVE_BTN) btnSave?.classList.add('d-none');
  });

  window.addEventListener('resize', () => {
    if (modalEl.classList.contains('show')) setTimeout(fitAndCenter, 100);
  });

  btnCancel.addEventListener('click', () => modal.hide());

  // CORTAR
  btnCrop.addEventListener('click', async () => {
    if (!cropper) return;
    try{
      showOverlay();
      const canvas = cropper.getCroppedCanvas();
      const blob = await new Promise(res => canvas.toBlob(res, tmpFile?.type || 'image/jpeg', 0.9));
      if (!blob) throw new Error('Falha ao gerar recorte.');

      let finalFile = (tmpFile?.type === 'image/png')
        ? new File([blob], tmpFile.name, {type: blob.type})
        : await (async () => {
            const f = new File([blob], tmpFile?.name || 'image.jpg', {type: blob.type});
            return await compressImage(f);
          })();

      // preview sempre
      photo.src = URL.createObjectURL(finalFile);

      if (MODE === 'defer') {
        assignFileToModelInput(finalFile);
        document.dispatchEvent(new CustomEvent('uploadImage:pending', { detail: { widgetId: '$id' }}));
      }
      btnSave.style.display = 'block';
      modal.hide();
    } catch (err){
      alert(err.message || err);
    } finally {
      hideOverlay();
    }
  });

  // SALVAR
  btnSave.addEventListener('click', async () => {
    if (!cropper) return;
    try{
      showOverlay();
      const canvas = cropper.getCroppedCanvas();
      const blob = await new Promise(res => canvas.toBlob(res, tmpFile?.type || 'image/jpeg', 0.9));
      if(!blob) throw new Error('Falha ao gerar recorte.');

      let finalFile = (tmpFile?.type === 'image/png')
        ? new File([blob], tmpFile.name, {type: blob.type})
        : await (async () => {
            const f = new File([blob], tmpFile?.name || 'image.jpg', {type: blob.type});
            return await compressImage(f);
          })();

      if (MODE === 'defer') {
        photo.src = URL.createObjectURL(finalFile);
        assignFileToModelInput(finalFile);
        modal.hide();
        return;
      }

      // instant
      const saved = await uploadFinalFile(finalFile);
      lastSavedFileId = saved.id || null;
      if (saved.url) photo.src = saved.url;
      if (hidden) hidden.value = String(lastSavedFileId ?? '');
      document.dispatchEvent(new CustomEvent('uploadImage:saved', { detail: { file: saved, widgetId: '$id' }}));
      modal.hide();
    } catch(err){
      console.error(err);
      alert(err.message || err);
    } finally {
      hideOverlay();
    }
  });

  // REMOVER
  btnRemove.addEventListener('click', async () => {
    try{
      showOverlay();
      photo.src = '{$this->placeholder}';
      if (MODE === 'defer') {
        // limpa input file
        // (não há como programaticamente setar value != '' em file input)
        if (hidden) hidden.value = ''; // caso exista um hidden
        return;
      }

      if (!lastSavedFileId && hidden?.value) lastSavedFileId = hidden.value;
      if (!lastSavedFileId) { if (hidden) hidden.value = ''; return; }

      const fd = new FormData();
      fd.append(CSRF_PARAM, CSRF_TOKEN);
      const res = await fetch(withAccessToken('{$removeUrl}' + '?id=' + encodeURIComponent(lastSavedFileId)), {
        method:'POST',
        body: fd,
        headers: commonHeaders(),
        credentials: WITH_CREDS,
      });
      if (hidden) hidden.value = '';
      lastSavedFileId = null;
    } catch(err){
      console.error(err);
    } finally {
      hideOverlay();
    }
  });

})();
JS;

    $view->registerCss($css);
    $view->registerJs($script, \yii\web\View::POS_END);

    $showRemove = ($this->imageUrl !== '') ? '' : 'd-none';

    ob_start(); ?>
    <div id="<?= $wrapId ?>">
      <div class="card uploader-card">
        <div class="card-body position-relative">

          <div id="<?= $overlayId ?>" class="overlay">
            <div class="text-white d-flex align-items-center gap-2">
              <strong><?= Yii::t('app', 'Processing...') ?></strong>
              <div class="spinner-border ms-2" role="status" aria-hidden="true"></div>
            </div>
          </div>

          <div class="text-center pb-1">
            <img id="<?= $photoId ?>" class="rounded preview" src="<?= Html::encode($initialUrl) ?>" alt="preview">
          </div>

          <div class="text-center pb-2">
            <!-- input fora do label -->
            <input id="<?= $inputId ?>" type="file" accept="<?= Html::encode($this->accept) ?>" class="d-none">

            <div class="btn-group" role="group" aria-label="upload actions">
              <label class="btn btn-primary mb-0" for="<?= $inputId ?>">
                <i class="fas fa-file-upload me-1"></i><?= Html::encode($this->labelSelect) ?>
              </label>

              <button type="button" id="<?= $saveId ?>" class="btn btn-primary">
                <i class="fas fa-save me-1"></i><?= Html::encode($this->labelSave) ?>
              </button>

              <button type="button" id="<?= $removeId ?>" class="btn btn-danger <?= $showRemove ?>">
                <i class="fas fa-trash me-1"></i><?= Html::encode($this->labelRemove) ?>
              </button>
            </div>
          </div>

        </div>
      </div>

      <div class="modal fade uploader-modal modal-fullscreen-sm-down" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true" aria-labelledby="<?= $modalId ?>_label">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 id="<?= $modalId ?>_label" class="modal-title">Cortar imagem</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Html::encode($this->labelCancel) ?>"></button>
            </div>
            <div class="modal-body">
              <div class="img-container">
                <img id="<?= $imgId ?>" src="<?= Html::encode($this->placeholder) ?>" alt="crop">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" id="<?= $cropId ?>" class="btn btn-outline-primary">
                <i class="fas fa-crop"></i> <?= Html::encode($this->labelCrop) ?>
              </button>
              <button type="button" id="<?= $cancelId ?>" class="btn btn-secondary" data-bs-dismiss="modal">
                <?= Html::encode($this->labelCancel) ?>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php
    return ob_get_clean();
  }

  private function jsonSafe($data): string
  {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return addslashes($json ?? '[]');
  }
}
