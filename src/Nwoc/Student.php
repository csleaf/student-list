<?php
namespace Nwoc;

/**
* Represents table entry 'students' from database.
*/
class Student {
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

    public $forename; // varchar(64)
    public $surname; // varchar(64)
    public $email; // varchar(64)
    public $gender; // bool, GENDER_MALE / GENDER_FEMALE
    public $group_id; // char(5)
    public $exam_results; // smallint
    public $birth_year; // smallint
    public $is_foreign; // bool
}
