<?php

namespace T3Botmigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160214210556 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // create notifications table
        $notificationsTable = $schema->createTable('notifications');
        $notificationsTable->addColumn('id', Type::INTEGER, array('unsigned' => true))->setAutoincrement(true);
        $notificationsTable->addColumn('from_user', Type::STRING, array('length' => 32));
        $notificationsTable->addColumn('to_user', Type::STRING, array('length' => 32));
        $notificationsTable->addColumn('message', Type::TEXT);
        $notificationsTable->addColumn('created', Type::DATETIME, [
            'columnDefinition' => 'timestamp default current_timestamp',
        ]);
        $notificationsTable->addColumn('delivered', Type::DATETIME);
        $notificationsTable->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // Drop notidications table
        $schema->dropTable('notifications');
    }
}
