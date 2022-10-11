<?php
#-------------------------------------------------------
# Copyright (C) 2022 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\PHPCap;

use PHPUnit\Framework\TestCase;

use IU\PHPCap\RedCapProject;

/**
 * PHPUnit tests for users for the RedCapProject class.
 */
class UserRoleAssignmentsTest extends TestCase
{
    private static $config;
    private static $basicDemographyProject;
    private static $longitudinalDataProject;
    
    public static function setUpBeforeClass(): void
    {
        self::$config = parse_ini_file(__DIR__.'/../config.ini');

        self::$basicDemographyProject = new RedCapProject(
            self::$config['api.url'],
            self::$config['basic.demography.api.token']
        );

        self::$longitudinalDataProject = new RedCapProject(
            self::$config['api.url'],
            self::$config['longitudinal.data.api.token']
        );
    }
    
    public function testUserRoleAssignments()
    {
        /*
        $userRoles = [
            [
                //'unique_role_name' => 'U-2273084CA4',
                //'unique_role_name' => 'U-527D39JXAC',
                //'unique_role_name' => 'U-2119C4Y87T',
                //'unique_role_name' => '2119',
                'role_label' => 'Project Manager',
                'user_rights' => 0
            ]
        ];

        $importResult = self::$basicDemographyProject->importUserRoles($userRoles);
         */

        $userRoleAssignments = self::$basicDemographyProject->exportUserRoleAssignments();
        $this->assertNotNull($userRoleAssignments, 'User role assignments non-null check.');

        print_r($userRoleAssignments);

        /*
        foreach ($uniqueRoleNames as $key => $value) {
            print("[{$key}] => '{$value}'\n");
            if ($value === '') {
                unset($uniqueRoleNames[$key]);
            }
        }

        $rolesDeleted = self::$basicDemographyProject->deleteUserRoles($uniqueRoleNames);
        // print("\nROLES DELETED: {$rolesDeleted}\n\n");

        $userRoles = self::$basicDemographyProject->exportUserRoles();
         */
    }
}
