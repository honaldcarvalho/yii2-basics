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
        $assetsDir =  ControllerCommon::getAssetsDir();
        $image = "{$assetsDir}/img/croacworks-logo-hq.png";
        $this->header_html = <<<HTML
        <table width="100%" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td rowspan="3" valign="top" width="25%">
                        <img src="{$image}" alt="" style="width:120px" name="Image1" border="0" />
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

        $this->createTable('{{%report_templates}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'header' => $this->text(),
            'footer' => $this->text(),
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
