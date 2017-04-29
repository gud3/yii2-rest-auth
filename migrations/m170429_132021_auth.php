<?php

use yii\db\Migration;

class m170429_132021_auth extends Migration
{
    public function up()
    {
        $error = null;
        if (!empty($this->db->getTableSchema('{{%auth}}'))) {
            $error = 'Table Auth exist';
        } elseif (empty($this->db->getTableSchema('{{%user}}'))) {
            $error = 'Table user not exist';
        }

        if ($error) {
            echo $error;
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth}}', [
            'id' => $this->primaryKey(20),
            'user_id' => $this->integer()->notNull(20),
            'token' => $this->string()->notNull(),
            'series' => $this->string()->notNull(),
            'date_end' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('FK_auth_token_user', 'auth', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%auth}}'))) {
            $this->dropForeignKey('FK_auth_token_user', 'auth');
            $this->dropTable('{{%auth}}');
        }
    }
}
