<?php

namespace weebz\yii2basics\helpers;

use weebz\yii2basics\controllers\ControllerCommon;
use Yii;
use yii\helpers\Html;

class MetaHelper
{
    /**
     * Define as meta tags principais com base no conteúdo.
     * Se já existir uma descrição/keywords nos params, usa elas.
     *
     * @param string|null $title Título da página
     * @param string|null $content Texto base (resumo ou conteúdo completo)
     * @param string[] $extraKeywords Palavras-chave adicionais
     */
    public static function setMeta(?string $title = null, ?string $content = null, array $extraKeywords = [])
    {
        $view = Yii::$app->view;

        // Geração de description e keywords automáticas
        $description = Yii::$app->view->params['meta_description']
            ?? mb_substr(strip_tags($content), 0, 160);

        $keywords = Yii::$app->view->params['meta_keywords']
            ?? implode(', ', array_unique(array_filter(array_merge(
                explode(' ', strip_tags(ControllerCommon::sanatize($title) . ' ' . $content)),
                $extraKeywords
            ))));

        $view->registerMetaTag([
            'name' => 'description',
            'content' => Html::encode($description),
        ]);

        $view->registerMetaTag([
            'name' => 'keywords',
            'content' => Html::encode($keywords),
        ]);
    }
}
