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
<?php

namespace common\helpers;

use Yii;
use yii\helpers\Html;

class MetaHelper
{
    /**
     * Define meta tags dinâmicas para description e keywords com filtro inteligente.
     *
     * @param string|null $title
     * @param string|null $content
     * @param string[] $extraKeywords
     */
    public static function setMeta(?string $title = null, ?string $content = null, array $extraKeywords = [])
    {
        $view = Yii::$app->view;

        // Description (limitada a 160 caracteres)
        $description = $view->params['meta_description']
            ?? mb_substr(trim(strip_tags($content)), 0, 160);

        // Texto base (sem tags e pontuação)
        $baseText = strip_tags($title . ' ' . $content);
        $cleanText = preg_replace('/[^\p{L}\p{N}\s]/u', '', $baseText);

        // Lista de palavras a ignorar (stop words em pt-br)
        $stopWords = [
            'com', 'sem', 'uma', 'para', 'como', 'dos', 'das', 'nos', 'nas',
            'que', 'por', 'mais', 'mas', 'não', 'sim', 'aos', 'aí', 'de', 'em',
            'no', 'na', 'ao', 'as', 'os', 'e', 'o', 'a', 'é', 'se', 'do', 'da',
            'ou', 'um', 'uns', 'uma', 'umas', 'até', 'isso', 'isso', 'esses',
            'essas', 'esse', 'essa', 'lhe', 'eles', 'elas', 'ele', 'ela'
        ];

        // Filtra palavras (mínimo 3 letras e não na stop list)
        $words = array_filter(
            preg_split('/\s+/', mb_strtolower($cleanText)),
            fn($word) => mb_strlen($word) >= 3 && !in_array($word, $stopWords)
        );

        // Junta com extras, remove duplicadas
        $keywords = implode(', ', array_unique(array_merge($words, $extraKeywords)));

        // Registra metatags
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
