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
 * PHPUnit integration tests for Logging.
 */
class LoggingTest extends TestCase
{
    private static $config;
    private static $basicDemographyProject;
    
    const DELETE_WAIT_TIME = 60;

    public static function setUpBeforeClass(): void
    {
        self::$config = parse_ini_file(__DIR__.'/../config.ini');
        self::$basicDemographyProject = new RedCapProject(
            self::$config['api.url'],
            self::$config['basic.demography.api.token']
        );
        
        #clean up any records that did not get properly deleted from a prior test.
        $oldIds = [1200, 1201];
        $runWait = false;
        foreach ($oldIds as $id) {
            $exists = self::$basicDemographyProject->exportRecordsAp(['recordIds' => [$id]]);
            if (count($exists) > 0) {
                self::$basicDemographyProject->deleteRecords([$id]);
                $runWait = true;
            }
        }
        
        # If records were deleted, wait three minutes so that the test doesn't
        # pick up these log entries for the deletions as modified records
        if ($runWait) {
            sleep(self::DELETE_WAIT_TIME);
        };

    }
    
    public function testExportLoggingWithDateRange()
    {
        
        # Establish the begin date 
        $twoMinutesAgo = new \DateTime();
        $twoMinutesAgo->sub(new \DateInterval('PT2M'));

        # Create a test record to insert
        $records = FileUtil::fileToString(__DIR__.'/../data/basic-demography-import2.csv');
        $result = self::$basicDemographyProject->importRecords(
            $records,
            $format = 'csv',
            $type = null,
            $overwriteBehavior = null,
            $dateFormat = null,
            $returnContent = null
        );

        # Establish the end date 
        $fiveMinutesFromNow = new \DateTime();
        $fiveMinutesFromNow->add(new \DateInterval('PT5M'));

        #adjust for timezone
        if (array_key_exists('timezone', self::$config)) {
            $tz = self::$config['timezone'];
        } else {
            $message = 'No timezone defined in configuration file "'.realpath(self::$configFile).'"';
            throw new \Exception($message);
        }

        if ($tz) {
            $twoMinutesAgo->setTimezone(new \DateTimeZone($tz));
            $fiveMinutesFromNow->setTimezone(new \DateTimeZone($tz));
        }
        
        $result = self::$basicDemographyProject->exportLogging(
            $format='php',
            $logType = null, 
            $username = null, 
            $recordId = null, 
            $dag = null, 
            $beginTime = $twoMinutesAgo->format('Y-m-d H:i:s'), 
            $endTime = $fiveMinutesFromNow->format('Y-m-d H:i:s') 
        );
 
        $this->assertGreaterThan(0, count($result), 'Export logging count check.');
        
        $actions = array_column($result, 'action');
        $this->assertContains('Created Record (API) 1200', $actions, 'Export logging begin date-action check.');

        #test entering a begin date in the future so that no records should be returned
        $result1 = self::$basicDemographyProject->exportLogging(
            $format='php',
            $logType = null, 
            $username = null, 
            $recordId = null, 
            $dag = null, 
            $beginTime = '2100-01-01 00:00:00',
            $endTime = null
        );
        $this->assertEquals(0, count($result1), "Export logging begin date with future begin date.");

        #test entering an end date in the past so that no records should be returned
        $result2 = self::$basicDemographyProject->exportLogging(
            $format='php',
            $logType = null, 
            $username = null, 
            $recordId = null, 
            $dag = null, 
            $beginTime = null,
            $dateRangeEnd = '1901-01-01 00:00:00'
        );
        $this->assertEquals(0, count($result2), "Export logging end date with old end date.");

        # Clean up the test by deleting the inserted test record
        self::$basicDemographyProject->deleteRecords([1200]);

    }
      
    public function testExportLoggingLogType()
    {
        $result = self::$basicDemographyProject->exportRecords($format = null);
        $logType = 'export';
        $logs = self::$basicDemographyProject->exportLogging(
            $format='php',
            $logType = $logType,
            $username = null, 
            $recordId = null, 
            $dag = null, 
            $beginTime = null,
            $endTime = null
        );

        $expected = 'Data Export (API) ';
        $result = $logs[0]['action'];
        $this->assertEquals($expected, $result, "Export logging: log type check.");
    }
    
    public function testExportLoggingUser() 
    {
#you are here
    }
    
}
