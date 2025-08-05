<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%url_clicks}}`.
 */
class m250804_154048_create_url_clicks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%url_clicks}}', [
            'id' => $this->primaryKey(),
            'url_id' => $this->integer()->notNull(),
            'user_agent' => $this->text(),
            'ip_address' => $this->string(45),
            'is_bot' => $this->boolean()->notNull()->defaultValue(0),
            'clicked_at' => $this->datetime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Создаем индексы
        $this->createIndex('idx_url_id', '{{%url_clicks}}', 'url_id');
        $this->createIndex('idx_clicked_at', '{{%url_clicks}}', 'clicked_at');
        $this->createIndex('idx_is_bot', '{{%url_clicks}}', 'is_bot');

        // Создаем внешний ключ
        $this->addForeignKey(
            'fk_url_clicks_url_id',
            '{{%url_clicks}}',
            'url_id',
            '{{%urls}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем внешний ключ
        $this->dropForeignKey('fk_url_clicks_url_id', '{{%url_clicks}}');
        
        // Удаляем таблицу
        $this->dropTable('{{%url_clicks}}');
    }
}
