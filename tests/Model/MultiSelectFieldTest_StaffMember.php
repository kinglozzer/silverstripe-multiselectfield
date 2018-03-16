<?php

namespace Kinglozzer\MultiSelectField\Tests\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

/**
 * Class MultiSelectFieldTest_StaffMember
 * @package Kinglozzer\MultiSelectField\Tests\Model
 */
class MultiSelectFieldTest_StaffMember extends DataObject implements TestOnly
{
    /**
     * @var string
     */
    private static $table_name = 'MultiSelectFieldTest_StaffMember';

    /**
     * @var array
     */
    private static $db = [
        'Name' => 'Varchar',
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'Departments' => MultiSelectFieldTest_Department::class,
    ];
}
