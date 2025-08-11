<?php

namespace Classes;

class Validator
{
    private static $errors = [];
    private static $failed = false;
    private static $ers = [];

    public function __construct()
    {
        self::reset();
    }

    /**
     * Validate a single form field based on rules.
     *
     * @param string $postname The name of the POST field
     * @param string $label A user-friendly label for error messages
     * @param string $rules Pipe-separated rules (e.g., 'required|min:5|alpha')
     * @return void
     * @author Tyrone Limen Malocon
     */
    public static function check($postname, $label, $rules)
    {
        $postdata = postdata();
        if (!isset($postdata[$postname])) {
            $postdata[$postname] = null;
        }

        $value = trim($postdata[$postname] ?? '');
        $rulesArray = explode('|', $rules);
        $hasRequired = in_array('required', $rulesArray);
        $rulesArray = array_reverse($rulesArray);

        foreach ($rulesArray as $rule) {
            $ruleParts = explode(':', $rule, 2);
            $ruleName = $ruleParts[0];
            $ruleParam = isset($ruleParts[1]) ? $ruleParts[1] : null;

            if ($value === '' && $ruleName !== 'required' && !$hasRequired) {
                continue;
            }

            if ($ruleName === 'required' && $value === '') {
                self::addError($postname, "$label is required.");
                self::addErrs($postname, "Required");
            }

            if ($ruleName === 'min') {
                if (is_numeric($value)) {
                    if ($value < (float)$ruleParam) {
                        self::addError($postname, "$label must be at least $ruleParam.");
                        self::addErrs($postname, "must be at least $ruleParam.");
                    }
                } else {
                    if (strlen($value) < (int)$ruleParam) {
                        self::addError($postname, "$label must be at least $ruleParam characters.");
                        self::addErrs($postname, "must be at least $ruleParam characters.");
                    }
                }
            }

            if ($ruleName === 'max') {
                if (is_numeric($value)) {
                    if ($value > (float)$ruleParam) {
                        self::addError($postname, "$label must not exceed $ruleParam.");
                        self::addErrs($postname, "must not exceed $ruleParam.");
                    }
                } else {
                    if (strlen($value) > (int)$ruleParam) {
                        self::addError($postname, "$label must not exceed $ruleParam characters.");
                        self::addErrs($postname, "must not exceed $ruleParam characters.");
                    }
                }
            }


            if ($ruleName === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                self::addError($postname, "$label must be a valid email address.");
                self::addErrs($postname, "must be a valid email address.");
            }

            if (($ruleName === 'string' || $ruleName === 'text') && !is_string($value)) {
                self::addError($postname, "$label must be a string.");
                self::addErrs($postname, "must be a string.");
            }

            if (($ruleName === 'float' || $ruleName === 'double' || $ruleName === 'decimal') && !is_float($value)) {
                if (!is_numeric($value) || strpos((string)$value, '.') === false) {
                    self::addError($postname, "$label must be a decimal.");
                    self::addErrs($postname, "must be a decimal.");
                }
            }

            if (($ruleName === 'numeric' || $ruleName === 'number') && !is_numeric($value)) {
                self::addError($postname, "$label must be a number.");
                self::addErrs($postname, "must be a number.");
            }

            if (($ruleName === 'int' || $ruleName === 'integer') && !is_int($value)) {
                self::addError($postname, "$label must be an integer.");
                self::addErrs($postname, "must be an integer.");
            }

            if ($ruleName === 'alpha' && !preg_match('/^[a-zA-Z]+$/', $value)) {
                self::addError($postname, "$label must contain only letters.");
                self::addErrs($postname, "must contain only letters.");
            }

            if ($ruleName === 'alphanumeric' && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                self::addError($postname, "$label must contain only letters and numbers.");
                self::addErrs($postname, "must contain only letters and numbers.");
            }

            if ($ruleName === 'regex' && $ruleParam !== null && !preg_match($ruleParam, $value)) {
                self::addError($postname, "$label format is invalid.");
                self::addErrs($postname, "format is invalid.");
            }

            if ($ruleName === 'match') {
                $expectedValues = [];

                if (isset($_POST[$ruleParam])) {
                    $expectedValues[] = trim((string)$_POST[$ruleParam]);
                } else {
                    $expectedValues = array_map('trim', explode(',', $ruleParam));
                }

                if (!in_array((string)$value, $expectedValues, true)) {
                    $expectedList = implode(', ', $expectedValues);
                    self::addError($postname, "$label has invalid value.");
                    self::addErrs($postname, "has invalid value.");
                }
            }

            if ($ruleName === 'in' && $ruleParam !== null) {
                $options = explode(',', $ruleParam);
                if (!in_array($value, $options)) {
                    self::addError($postname, "$label must be one of: " . implode(', ', $options) . ".");
                    self::addErrs($postname, "must be one of: " . implode(', ', $options) . ".");
                }
            }

            if ($ruleName === 'not_in' && $ruleParam !== null) {
                $options = explode(',', $ruleParam);
                if (in_array($value, $options)) {
                    self::addError($postname, "$label must not be one of: " . implode(', ', $options) . ".");
                    self::addErrs($postname, "must not be one of: " . implode(', ', $options) . ".");
                }
            }

            if ($ruleName === 'date' && !strtotime($value)) {
                self::addError($postname, "$label must be a valid date.");
                self::addErrs($postname, "must be a valid date.");
            }

            if ($ruleName === 'textonly' && !self::isPureText($value)) {
                self::addError($postname, "$label must be a plain text.");
                self::addErrs($postname, "must be a plain text.");
            }

            if ($ruleName === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                self::addError($postname, "$label must be a valid URL.");
                self::addErrs($postname, "must be a valid URL.");
            }

            if ($ruleName === 'ip' && !filter_var($value, FILTER_VALIDATE_IP)) {
                self::addError($postname, "$label must be a valid IP address.");
                self::addErrs($postname, "must be a valid IP address.");
            }

            if ($ruleName === 'boolean' && !in_array($value, ['0', '1', 0, 1, true, false], true)) {
                self::addError($postname, "$label must be true or false.");
                self::addErrs($postname, "must be true or false.");
            }

            if ($ruleName === 'length' && strlen($value) !== (int)$ruleParam) {
                self::addError($postname, "$label must be exactly $ruleParam characters.");
                self::addErrs($postname, "must be exactly $ruleParam characters.");
            }

            if ($ruleName === 'starts_with' && $ruleParam !== null && strpos($value, $ruleParam) !== 0) {
                self::addError($postname, "$label must start with '$ruleParam'.");
                self::addErrs($postname, "must start with '$ruleParam'.");
            }

            if ($ruleName === 'ends_with' && $ruleParam !== null && substr($value, -strlen($ruleParam)) !== $ruleParam) {
                self::addError($postname, "$label must end with '$ruleParam'.");
                self::addErrs($postname, "must end with '$ruleParam'.");
            }
        }

        return $value;
    }


    /**
     * Reset validation state.
     *
     * Clears previous errors and sets failed back to false.
     * Recommended to call before starting a new form validation.
     *
     * @return void
     */
    public static function reset()
    {
        self::$errors = [];
        self::$failed = false;
    }

    public static function failed()
    {
        return self::$failed;
    }

    public static function errors($complete = true)
    {
        if ($complete) {
            return self::$errors;
        }
        return self::$ers;
    }

    /**
     * Helper to add an error and mark validation as failed.
     *
     * @param string $message
     * @return void
     */
    protected static function addError(string $post, string $message)
    {
        self::$errors[$post] = $message;
        self::$failed = true;
    }

    protected static function addErrs(string $post, string $message)
    {
        self::$ers[$post] = $message;
        self::$failed = true;
    }


    private static function isPureText($string, $withSpace = true)
    {
        $pattern = $withSpace ? '/^[A-Za-z\s]+$/' : '/^[A-Za-z]+$/';

        return is_string($string) && preg_match($pattern, $string);
    }
}
