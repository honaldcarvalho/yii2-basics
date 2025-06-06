<?php

use weebz\yii2basics\controllers\ControllerCommon;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%report_templates}}`.
 */
class m270921_260309_create_report_templates_table extends Migration
{
    public function safeUp()
    {
        $header_html = <<<HTML
        <table width="100%" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td rowspan="3" valign="top" width="25%">
                        <img alt=""  style="width:120px" name="Image1" border="0" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQAAAAEACAMAAABrrFhUAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAANhQTFRFAAAAQJ9QQJpNP5pPPptPPptPPptPPZpPPpxQQJ9QPptOQJdQPpxQPptOPpxPPppPPptQPptOP5xPQJxQPpxOP5tPSaFZd7mDpNGsu93B3u/hx+PMsNe3mcuiVadkbLN56vXr9fv13u/gg7+Ngr+NvN3Bmsuix+PLg7+OjsWYYK1u0unWj8WYsde30unVd7mCYa1u6fXq0+nWsNe2a7N4mcuhjsWXVadjpdGsSqFZVKdksde2bLN4gr+MdrmCVKdjP5xPPZtPQJtQPZtNPptQPZtNPppOPZpP7eQaGAAAAEh0Uk5TABBgn8/v/59fII8gkIDf34B/n1Bfv////////////////////////////////////////////////////////9+/QE9/cG+v4sDrCAAACsZJREFUeJztnftvHDUQx9fJ5XFJ026SpmloI4FARa14lQp+QUgNgj+a/gbiLcSjPH9AgiL6AtokbZO0SZold+d92t61PTM7S8/fX3LZu9u1P7f2zNhjr4gyiaGiZ11JMimeJNm/WYX74uDZr3wm0dtJX8k/8/tsheHRzMPR3xGA/tRjxrLwqH84vAmGAMax/imBAYDxrL8kMABwcjzrf0zgQTIAEO9yF4RN85vHAPpPuYvBqKkdES094i4Fo3q7QkxxF4JTyaFYfshdCFadFGPdAgYB0Mo2dxlYdSSmxigE0igR09xFYFYAEABwl4BZAUAAwF0CZgUAAQB3CZgVAAQA3CVgVgAQAHCXgFkBQADAXQJmBQABAHcJmBUABADcJWBWABAAcJeAWQFAAMBdAmYFAAEAdwnMiuX6BREJsT9zh+Yi3QWwdljO3hH7k7cJLtNVAOtJoh4UE3+iX6ibANYmNdUfCB9BFwHEpw7Nb4pD3M6ggwCMP7/U1B+YV+segOcPmj4xvVe6CeJZ8XhRpAmv89FelDjcJF0DEE9ZFGjUDOLTRztJkkzrMj2T+fu9/dm9LYtzdQvA2V797S8lZjYnbVJc57Znp39rOFWnAKwRLNxs8B86BeDCTvNnfCT2J4y9QpcANHd//jowIegQgJdpVy4YEHQGQHyWeumKWPhFd7QjACy7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" />
                    </td>
                    <td width="77%" style="text-align: center;color: #64af44;">
                        <h3>CroacWorks</h3>
                    </td>
                </tr>
                <tr>
                    <td width="77%" style="text-align: center;color: #64af44;font-size: small;">
                        Saltando da ideia ao resultado com estilo e inovação
                    </td>
                </tr>
                <tr>
                    <td width="77%" style="text-align: center;color: #64af44;font-size: small;">
                        CNPJ 60.027.572/0001-96
                    </td>
                </tr>
            </tbody>
        </table>
        HTML;

        $footer_html = <<< HTML
            <p align="center" style="color: #64af44;">
                <span>Rua Rio Grande do Norte, 1786, Vila Operária, Teresina - PI</span><br>
                <span>Fone:(86) 9 9900 - 7567</span><br>
                <span>croacworks.com.br</span>
            </p>

        HTML;

        $this->createTable('{{%report_templates}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'header' => $this->text()->defaultValue($header_html),
            'footer' => $this->text()->defaultValue($footer_html),
            'styles' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk-report_templates-group_id', '{{%report_templates}}', 'group_id', '{{%groups}}', 'id', 'CASCADE');

    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-report_templates-group_id', '{{%report_templates}}');
        $this->dropTable('{{%report_templates}}');
    }
}
