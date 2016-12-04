<?php
namespace Nwoc;

/**
 * Represents validation methods for a Student class.
 */
class StudentValidator {
    const NAME_REGEXP = "/^[А-Яа-я' ]+$/u";
    const GROUP_REGEXP = "/^[А-Яа-я0-9]+$/u";
    const EMAIL_REGEXP = "/@/";

    /**
     * Validates student against predefined set of rules. Throws ValidationException with
     * errors property that contains validation errors if student validation failed.
     * $check_email_registration parameter handles checking that email is already registered
     * within system.
     * @student: student to validate
     * @gateway: 
     */
    public static function validate(Student $student, StudentsTableGateway $gateway, bool $validateEmailCollision = true) {
        if (!isset($gateway)) {
            throw new InvalidArgumentException('$gateway is not set.');
        }

        $errors = array();

        // forename
        $validator = new ObjectValidator('UTF-8');
        $validator->min_length(1, 'Вы не задали своё имя.')
                  ->max_length(64, 'Имя не должно быть длиннее :inparam букв (вы ввели :outparam букв).')
                  ->regexp_match(self::NAME_REGEXP, 'Имя может включать в себя только буквы от А до Я, пробел, дефис и апостроф.')
                  ->validate($student->forename, $errors, 'forename');

        // surname
        $validator->clear();
        $validator->min_length(1, 'Вы не задали свою фамилию.')
                  ->max_length(64, 'Фамилия не должна быть длиннее :inparam букв, вы ввели :outparam букв.')
                  ->regexp_match(self::NAME_REGEXP, 'Фамилия может включать в себя только буквы от А до Я, пробел, дефис и апостроф.')
                  ->validate($student->surname, $errors, 'surname');

        // email
        $validator->clear();
        $validator->min_length(1, 'Вы не задали свой e-mail.')
                  ->max_length(64, 'Вы ввели слишком длинный e-mail, максимальное кол-во символов: :inparam, вы ввели: :outparam символов.')
                  ->regexp_match(self::EMAIL_REGEXP, 'В e-mail должен присутствовать символ \'@\'.');

        if ($validator->validate($student->email, $errors, 'email') === 0 && $validateEmailCollision) {
            $query = $this->db->prepare('SELECT COUNT(email) FROM students WHERE email=? LIMIT 1');
            $query->execute(array(strval($student->email)));
            $result = $query->fetch(\PDO::FETCH_NUM);
            if (intval($result[0]) != 0) {
                $errors['email'] = array('Студент с таким E-mail уже зарегистрирован.');
            }
            $query->closeCursor();
        }

        // gender
        if (!($student->gender === Student::GENDER_MALE || $student->gender === Student::GENDER_FEMALE))
        {
            $errors['gender'] = array('Вы не выбрали свой пол.');
        }

        // group id
        if (!isset($student->group_id)) {
            $errors['group_id'] = array('Вы не указали название своей группы.');
        } else {
            $validator->clear();
            $validator->min_length(2, 'Название группы состоит как минимум из :inparam символов, вы ввели :outparam символов')
                      ->max_length(5, 'Название группы должно быть не более :inparam символов, вы ввели :outparam символов.')
                      ->regexp_match(self::GROUP_REGEXP, 'Название группы должно включать лишь буквы от А до Я и цифры.')
                      ->validate($student->group_id, $errors, 'group_id');
        }

        // exam_results
        if (!isset($student->exam_results)) {
            $errors['exam_results'] = array('Вы не ввели свои баллы по ЕГЭ.');
        } elseif (!ctype_digit(intval($student->exam_results))
              && (intval($student->exam_results) <= 0 || intval($student->exam_results) > 315))
        {
            $errors['exam_results'] = array('Введите свои баллы по ЕГЭ от 0 до 315.');
        }

        // birth_year
        if (!isset($student->birth_year)) {
            $errors['birth_year'] = array('Вы не указали ваш год рождения.');
        } elseif (!ctype_digit($student->birth_year)) {
            $errors['birth_year'] = array('Год рождения должен состоять только из цифр.');
        } elseif (intval($student->birth_year) < 1900 ||
                  intval($student->birth_year) > 2015)
        {
            $errors['birth_year'] = array('Год рождения должен быть указан в диапазоне от 1900 до 2015 года.');
        }

        // is_foreign
        if (!isset($student->is_foreign)
          && !(intval($student->is_foreign) === 0 /* false */
          ||   intval($student->is_foreign) === 1 /* true  */))
        {
            $errors['is_foreign'] = array('Вы не указали свой статус.');
        }

        if (count($errors) !== 0) {
            throw new ValidationException($student, $errors);
        }
    }
}
