<?php

namespace Kinglozzer\MultiSelectField\Tests\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

/**
 * Class MultiSelectFieldTest_Department
 * @package Kinglozzer\MultiSelectField\Tests\Model
 */
class MultiSelectFieldTest_Department extends DataObject implements TestOnly
{
    /**
     * @var string
     */
    private static $table_name = 'MultiSelectFieldTest_Department';

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
        'StaffMembers' => MultiSelectFieldTest_StaffMember::class,
    ];

    /**
     * @var array
     */
    private static $many_many_extraFields = [
        'StaffMembers' => [
            'Sort' => 'Int',
        ],
    ];
}
