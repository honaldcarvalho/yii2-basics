<?php

use yii\db\Migration;

/**
 * Class m250522_000003_insert_common_meta_tags_with_canonical
 */
class m250621_260308_insert_default_meta_tags extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%meta_tags}}', [
            'configuration_id', 'name', 'description', 'content', 'status'
        ], [
            // SEO
            [1, 'description', 'Descrição da página', 'Este é um exemplo de descrição para mecanismos de busca.', 1],
            [1, 'keywords', 'Palavras-chave', 'exemplo, site, yii2, metatags', 1],
            [1, 'author', 'Autor do site', 'Weebz - Soluções Criativas e Inteligentes para Web', 1],
            [1, 'robots', 'Diretivas para robôs', 'index, follow', 1],
            [1, 'googlebot', 'Diretivas para o Googlebot', 'index, follow', 1],
            [1, 'google-site-verification', 'Verificação do Google Search Console', 'SltCzabtrk3qu1AqoDIWI_ryE_fltAmHL-EXauQ1kVQ', 1],
            [1, 'viewport', 'Configuração de visualização para dispositivos móveis', 'width=device-width, initial-scale=1', 1],
            [1, 'canonical', 'URL canônica da página', 'https://example.com', 1],

            // Open Graph
            [1, 'og:title', 'Open Graph: título', 'Título da Página', 1],
            [1, 'og:description', 'Open Graph: descrição', 'Descrição para redes sociais.', 1],
            [1, 'og:image', 'Open Graph: imagem', 'https://example.com/imagem.jpg', 1],
            [1, 'og:url', 'Open Graph: URL', 'https://example.com', 1],
            [1, 'og:type', 'Open Graph: tipo', 'website', 1],
            [1, 'og:site_name', 'Open Graph: nome do site', 'Nome do Site', 1],
            [1, 'og:locale', 'Open Graph: idioma local', 'pt_BR', 1],

            // Twitter Cards
            [1, 'twitter:card', 'Twitter: tipo do card', 'summary_large_image', 1],
            [1, 'twitter:title', 'Twitter: título', 'Título para Twitter', 1],
            [1, 'twitter:description', 'Twitter: descrição', 'Descrição para Twitter.', 1],
            [1, 'twitter:image', 'Twitter: imagem', 'https://example.com/twitter-image.jpg', 1],
            [1, 'twitter:site', 'Twitter: usuário do site', '@weebz', 1],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%meta_tags}}', ['configuration_id' => 1]);
    }
}