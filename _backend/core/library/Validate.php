<?php

class Validate
{
    public static $errors = [];
    public static $failed = false;

    public function __construct() {
        self::reset();
    }

    /**
     * Validate a single form field based on rules.
     *
     * @param string $postname The name of the POST field
     * @param string $label A user-friendly label for error messages
     * @param string $rules Pipe-separated rules (e.g., 'required|min:5|alpha')
     * @return void
     */
    public static function check($postname, $label, $rules)
    {
        $postdata = postdata();
        if (!isset($postdata[$postname])) {
            $postdata[$postname] = null;
        }

        $value = trim($postdata[$postname]);
        $rulesArray = explode('|', $rules);
        $rulesArray = array_reverse($rulesArray);

        foreach ($rulesArray as $rule) {
            $ruleParts = explode(':', $rule, 2);
            $ruleName = $ruleParts[0];
            $ruleParam = isset($ruleParts[1]) ? $ruleParts[1] : null;

            // Basic validations
            if ($ruleName === 'required' && $value === '') {
                self::addError($postname,"$label is required.");
            }

            if ($ruleName === 'min' && strlen($value) < (int)$ruleParam) {
                self::addError($postname,"$label must be at least $ruleParam characters.");
            }

            if ($ruleName === 'max' && strlen($value) > (int)$ruleParam) {
                self::addError($postname,"$label must not exceed $ruleParam characters.");
            }

            if ($ruleName === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                self::addError($postname,"$label must be a valid email address.");
            }

            if ($ruleName === 'numeric' && !is_numeric($value)) {
                self::addError($postname,"$label must be a number.");
            }

            if ($ruleName === 'alpha' && !preg_match('/^[a-zA-Z]+$/', $value)) {
                self::addError($postname,"$label must contain only letters.");
            }

            if ($ruleName === 'alphanumeric' && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                self::addError($postname,"$label must contain only letters and numbers.");
            }

            if ($ruleName === 'regex' && $ruleParam !== null) {
                if (!preg_match($ruleParam, $value)) {
                    self::addError($postname,"$label format is invalid.");
                }
            }

            // Advanced validations
            if ($ruleName === 'match' && isset($_POST[$ruleParam])) {
                if ($value !== trim($_POST[$ruleParam])) {
                    self::addError($postname,"$label must match " . ucfirst(str_replace('_', ' ', $ruleParam)) . ".");
                }
            }

            if ($ruleName === 'in' && $ruleParam !== null) {
                $options = explode(',', $ruleParam);
                if (!in_array($value, $options)) {
                    self::addError($postname,"$label must be one of: " . implode(', ', $options) . ".");
                }
            }

            if ($ruleName === 'not_in' && $ruleParam !== null) {
                $options = explode(',', $ruleParam);
                if (in_array($value, $options)) {
                    self::addError($postname,"$label must not be one of: " . implode(', ', $options) . ".");
                }
            }

            if ($ruleName === 'date' && !strtotime($value)) {
                self::addError($postname,"$label must be a valid date.");
            }

            if ($ruleName === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                self::addError($postname,"$label must be a valid URL.");
            }

            if ($ruleName === 'ip' && !filter_var($value, FILTER_VALIDATE_IP)) {
                self::addError($postname,"$label must be a valid IP address.");
            }

            if ($ruleName === 'boolean' && !in_array($value, ['0', '1', 0, 1, true, false], true)) {
                self::addError($postname,"$label must be true or false.");
            }

            if ($ruleName === 'length' && strlen($value) !== (int)$ruleParam) {
                self::addError($postname,"$label must be exactly $ruleParam characters.");
            }

            if ($ruleName === 'starts_with' && $ruleParam !== null) {
                if (strpos($value, $ruleParam) !== 0) {
                    self::addError($postname,"$label must start with '$ruleParam'.");
                }
            }

            if ($ruleName === 'ends_with' && $ruleParam !== null) {
                if (substr($value, -strlen($ruleParam)) !== $ruleParam) {
                    self::addError($postname,"$label must end with '$ruleParam'.");
                }
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

    /**
     * Helper to add an error and mark validation as failed.
     *
     * @param string $message
     * @return void
     */
    protected static function addError(string $post,string $message)
    {
        self::$errors[$post] = $message;
        self::$failed = true;
    }
}
