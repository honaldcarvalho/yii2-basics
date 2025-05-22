<?php

namespace weebz\yii2basics\helpers;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class MetaHelper
{
    /**
     * Define meta tags SEO, Open Graph e Twitter dinâmicas.
     *
     * @param string|null $title
     * @param string|null $content
     * @param string[] $extraKeywords
     * @param string|null $imageUrl
     */
    public static function setMeta(?string $title = null, ?string $content = null, array $extraKeywords = [], ?string $imageUrl = null)
    {
        $view = Yii::$app->view;

        // === DESCRIPTION ===
        $description = $view->params['meta_description']
            ?? mb_substr(trim(strip_tags($content)), 0, 160);

        // === KEYWORDS ===
        $baseText = strip_tags($title . ' ' . $content);
        $cleanText = preg_replace('/[^\p{L}\p{N}\s]/u', '', $baseText);

        $stopWords = [
            'com','sem','uma','para','como','dos','das','nos','nas','que','por','mais','mas','não','sim','aos','aí','de','em','no','na','ao','as','os','e','o','a','é','se','do','da','ou','um','uns','umas','até','isso','esses','essas','esse','essa','lhe','eles','elas','ele','ela'
        ];

        $words = array_filter(
            preg_split('/\s+/', mb_strtolower($cleanText)),
            fn($word) => mb_strlen($word) >= 3 && !in_array($word, $stopWords)
        );

        $keywords = implode(', ', array_unique(array_merge($words, $extraKeywords)));

        // === DEFAULT TITLE ===
        $title = $title ?? Yii::$app->name;

        // === DEFAULT IMAGE ===
        $imageUrl = $imageUrl ?? Url::to('@web/img/logo.jpg', true); // crie essa imagem se não tiver

        // === REGISTRA META TAGS ===

        // SEO
        $view->registerMetaTag(['name' => 'description', 'content' => Html::encode($description)]);
        $view->registerMetaTag(['name' => 'keywords', 'content' => Html::encode($keywords)]);

        // Open Graph
        $view->registerMetaTag(['property' => 'og:title', 'content' => Html::encode($title)]);
        $view->registerMetaTag(['property' => 'og:description', 'content' => Html::encode($description)]);
        $view->registerMetaTag(['property' => 'og:type', 'content' => 'website']);
        $view->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
        $view->registerMetaTag(['property' => 'og:image', 'content' => $imageUrl]);
        $view->registerMetaTag(['property' => 'og:locale', 'content' => 'pt_BR']);

        // Twitter Cards
        $view->registerMetaTag(['name' => 'twitter:card', 'content' => 'summary_large_image']);
        $view->registerMetaTag(['name' => 'twitter:title', 'content' => Html::encode($title)]);
        $view->registerMetaTag(['name' => 'twitter:description', 'content' => Html::encode($description)]);
        $view->registerMetaTag(['name' => 'twitter:image', 'content' => $imageUrl]);
    }
}