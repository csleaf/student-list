<?php
namespace Nwoc;

class ObjectValidator {
    /*
        should be integers! _MSG counterparts must have value of 'option value' + 1.
    */
    const NOT_NULL = 0;
    const NOT_NULL_MSG = 1;
    const MAX_LENGTH = 2;
    const MAX_LENGTH_MSG = 3;
    const MIN_LENGTH = 4;
    const MIN_LENGTH_MSG = 5;
    const REGEXP_MATCH = 6;
    const REGEXP_MATCH_MSG = 7;

    private $options;
    private $string_encoding;

    public function __construct(string $string_encoding) {
        $this->options = array();
        $this->string_encoding = $string_encoding;
    }

    private function get_error_msg(string $msg, string $default, string $inparam = null, string$outparam = null) {
        $msg = null;
        if (isset($this->options[$msg]))
            $msg = $this->options[$msg];
        else
            $msg = $default;
        if (isset($inparam)) $msg = str_replace(':inparam', $inparam, $msg);
        if (isset($outparam)) $msg = str_replace(':outparam', $outparam, $msg);
        return $msg;
    }

    private function set_option($option, $value, string $msg = null) {
        $this->options[$option] = $value;
        if (isset($msg)) $this->options[$option + 1] = $msg;
        return $this;
    }

    public function validate($object, array &$errors, string $field = null) {
        if (!isset($errors))
            throw new \Exception('&$errors is null.');
        if (count($this->options) == 0)
            throw new \Exception('No options for validation are set.');

        if (isset($field)) {
            $seterrors = array();
        } else {
            $seterrors = $errors;
        }

        $num_errors = 0;
        if (isset($this->options[self::NOT_NULL]) && $this->options[self::NOT_NULL] === true) {
            if (!isset($object)) {
                ++$num_errors;
                array_push($seterrors, $this->get_error_msg(self::NOT_NULL_MSG, 'Object is null.'));
                // @TODO should return because there is no point in other checks.
            }
        }

        if (isset($this->options[self::MAX_LENGTH])) {
            $max_length = $this->options[self::MAX_LENGTH];
            $length = \mb_strlen(strval($object), $this->string_encoding);
            if ($length > $max_length) {
                ++$num_errors;
                array_push($seterrors, $this->get_error_msg(self::MAX_LENGTH_MSG, 'You have exceed maximum length of :inparam symbols (you have written :outparam symbols).',
                    $max_length, $length));
            }
        }

        if (isset($this->options[self::MIN_LENGTH])) {
            $min_length = $this->options[self::MIN_LENGTH];
            $length = \mb_strlen(strval($object), $this->string_encoding);
            if ($length < $min_length) {
                ++$num_errors;
                array_push($seterrors, $this->get_error_msg(self::MIN_LENGTH_MSG, 'Min length is :inparam, you have got :outparam',
                    $min_length, $length));
            }
        }

        if (isset($this->options[self::REGEXP_MATCH])) {
            $result = preg_match($this->options[self::REGEXP_MATCH], $object);
            if ($result === FALSE) {
                throw new \Exception('Regular expression '.$options[self::REGEXP_MATCH].' is invalid.');
            } elseif ($result === 0) {
                ++$num_errors;
                array_push($seterrors, $this->get_error_msg(self::REGEXP_MATCH_MSG, 'Data is invalid.'));
            }
        }

        if ($num_errors > 0 && isset($field))
            if (!isset($errors[$field]))
                $errors[$field] = $seterrors;
            else
                array_merge($errors[$field], $seterrors);

        return $num_errors;
    }

    public function clear() {
        unset($this->options);
        $this->options = array();
    }

    public function not_null(string $msg = null) {
        return $this->set_option(self::NOT_NULL, true, $msg);
    }

    public function min_length($length, string $msg = null) {
        return $this->set_option(self::MIN_LENGTH, $length, $msg);
    }

    public function max_length($length, string $msg = null) {
        return $this->set_option(self::MAX_LENGTH, $length, $msg);
    }

    public function regexp_match($regexp, string $msg = null) {
        return $this->set_option(self::REGEXP_MATCH, $regexp, $msg);
    }
}
