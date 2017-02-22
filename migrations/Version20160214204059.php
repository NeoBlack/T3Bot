<?php

namespace T3Botmigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160214204059 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // create beer table
        $beerTable = $schema->createTable('beers');
        $beerTable->addColumn('id', Type::INTEGER, array('unsigned' => true))->setAutoincrement(true);
        $beerTable->addColumn('from_user', Type::STRING, array('length' => 32));
        $beerTable->addColumn('to_user', Type::STRING, array('length' => 32));
        $beerTable->addColumn('tstamp', Type::INTEGER, array('unsigned' => true));
        $beerTable->setPrimaryKey(['id']);

        // create messages table
        $messagesTable = $schema->createTable('messages');
        $messagesTable->addColumn('id', Type::INTEGER, array('unsigned' => true))->setAutoincrement(true);
        $messagesTable->addColumn('message', Type::TEXT);
        $messagesTable->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // Drop beer table
        $schema->dropTable('beers');

        // Drop messages table
        $schema->dropTable('messages');
    }
}
