<?php

namespace Acme\Bundle\StoreLocatorBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Creates the full schema of AcmeStoreLocatorBundle on a fresh install.
 */
class AcmeStoreLocatorBundleInstaller implements Installation
{
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createAcmeStoreTable($schema);
    }

    private function createAcmeStoreTable(Schema $schema): void
    {
        $table = $schema->createTable('acme_store');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255]);
        $table->addColumn('street', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('city', Types::STRING, ['length' => 100]);
        $table->addColumn('postcode', Types::STRING, ['length' => 20, 'notnull' => false]);
        $table->addColumn('phone', Types::STRING, ['length' => 50, 'notnull' => false]);
        $table->addColumn('opening_hours', Types::TEXT, ['notnull' => false]);
        $table->addColumn('active', Types::BOOLEAN, ['default' => true]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['active'], 'acme_store_active_idx');
    }
}
