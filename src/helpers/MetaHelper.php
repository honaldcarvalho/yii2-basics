<?php

namespace weebz\yii2basics\helpers;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class MetaHelper
{
    public static array $validFields = [
        'titulo',
        'descricao',
        'resumo',
        'nome',
        'titulo_site',
        'descricao_resumida',
        'title',
        'description',
        'keywords',
    ];

    public static function setMetaFromModel($model, array $extraKeywords = [], ?string $imageUrl = null)
    {
        if (!$model) {
            return;
        }

        $view = Yii::$app->view;

        $data = [];
        foreach (self::$validFields as $field) {
            if (isset($model->{$field}) && !empty($model->{$field})) {
                $data[$field] = strip_tags($model->{$field});
            }
        }

        // Gera título e descrição automáticos
        $title = $data['titulo'] ?? $data['nome'] ?? Yii::$app->name;
        $description = $data['descricao'] ?? $data['resumo'] ?? reset($data) ?? Yii::$app->name;

        // Junta todos os campos para gerar palavras-chave
        $baseText = implode(' ', $data);

        // Filtros para keywords
        $stopWords = ['com', 'sem', 'uma', 'para', 'como', 'dos', 'das', 'nos', 'nas', 'que', 'por', 'mais', 'mas', 'não', 'sim', 'aos', 'aí', 'de', 'em', 'no', 'na', 'ao', 'as', 'os', 'e', 'o', 'a', 'é', 'se', 'do', 'da', 'ou', 'um', 'uns', 'umas', 'até', 'isso', 'esses', 'essas', 'esse', 'essa', 'lhe', 'eles', 'elas', 'ele', 'ela'];

        $words = array_filter(
            preg_split('/\s+/', mb_strtolower($baseText)),
            fn($word) => mb_strlen($word) >= 3 && !in_array($word, $stopWords)
        );

        $keywords = implode(', ', array_unique(array_merge($words, $extraKeywords)));

        $imageUrl ??= Yii::$app->params['defaultMetaImage'] ?? Yii::$app->request->hostInfo . '/img/share-default.jpg';

        // REGISTRO
        $view->registerMetaTag(['name' => 'description', 'content' => Html::encode(mb_substr($description, 0, 160))]);
        $view->registerMetaTag(['name' => 'keywords', 'content' => Html::encode($keywords)]);

        // OG
        $view->registerMetaTag(['property' => 'og:title', 'content' => Html::encode($title)]);
        $view->registerMetaTag(['property' => 'og:description', 'content' => Html::encode($description)]);
        $view->registerMetaTag(['property' => 'og:type', 'content' => 'article']);
        $view->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
        $view->registerMetaTag(['property' => 'og:image', 'content' => $imageUrl]);

        // Twitter
        $view->registerMetaTag(['name' => 'twitter:card', 'content' => 'summary_large_image']);
        $view->registerMetaTag(['name' => 'twitter:title', 'content' => Html::encode($title)]);
        $view->registerMetaTag(['name' => 'twitter:description', 'content' => Html::encode($description)]);
        $view->registerMetaTag(['name' => 'twitter:image', 'content' => $imageUrl]);
    }

    public static function setMetaForIndex(array $options = []): void
    {
        $model = $options['model'] ?? null;

        // Se não tiver um modelo real, cria um modelo genérico com os campos necessários
        if (!$model) {
            $model = (object)[
                'titulo' => $options['title'] ?? 'Conteúdo',
                'descricao' => $options['description'] ?? 'Confira os conteúdos disponíveis em nosso portal.',
            ];
        }

        // Sempre chama setMetaFromModel
        self::setMetaFromModel(
            $model,
            $options['keywords'] ?? [],
            $options['imageUrl'] ?? null
        );
    }
}
