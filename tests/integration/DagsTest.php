<?php
#-------------------------------------------------------
# Copyright (C) 2019 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\PHPCap;

use PHPUnit\Framework\TestCase;

use IU\PHPCap\RedCapProject;
use IU\PHPCap\PhpCapException;

/**
 * PHPUnit integration tests for DAGs.
 */
class DagsTest extends TestCase
{
    private static $config;
    private static $dagsProject;
    
    public static function setUpBeforeClass(): void
    {
        self::$config = parse_ini_file(__DIR__.'/../config.ini');
        self::$dagsProject = new RedCapProject(
            self::$config['api.url'],
            self::$config['dags.api.token']
        );
    }
    
    public function testExportDags()
    {
        $result = self::$dagsProject->exportDags($format='php');

        $this->assertEquals(2, count($result), 'Exports DAGs count check.');

        $dagNames = array_column($result, 'data_access_group_name');
        $this->assertContains('group1', $dagNames, 'Export DAGs name check.');
    }
      
    public function testImportAndDeleteDag()
    {
        # add the new DAG
	    $newDag = [
            'data_access_group_name'  => 'group3',
            'unique_group_name' => ""
        ];
        
        $newDags = [$newDag];
        $result = self::$dagsProject->importDags($newDags, $format='php');
        $this->assertEquals(1, $result, 'Import and Delete DAG count check.');

        
        # delete the new DAG
    }
}
