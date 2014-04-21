<?php
/**
 * User: anubis
 * Date: 25.10.13 12:22
 */

namespace popcorn\tests\model\gens;


use popcorn\gens\structs\TableDescription;

class TableDescriptionTest extends \PHPUnit_Framework_TestCase {

    public function testCreateTable() {
        $table = new TableDescription('pn_test');
        $table->addColumn('id', 'INT', array('AI', 'NN', 'PK'));
        $table->addColumn('title', 'VARCHAR(200)', array('NN'), '');
        $table->addColumn('descr', 'TEXT', array(), 'NULL');
        $out = <<<'TABLE'
CREATE TABLE `pn_test` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL DEFAULT '',
  `descr` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;
TABLE;
        $export = $table->export();

        $this->assertEquals($out, $export, $export);

    }
}
 