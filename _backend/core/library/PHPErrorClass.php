<?php
class PHPErrorClass
{

    public static function error_message(mixed $e)
    {
        $msg = $e->getMessage();
        $code = $e->getCode();

        // MySQL duplicate entry
        if ($code === '23000' && strpos($msg, 'Duplicate entry') !== false) {
            if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $msg, $matches)) {
                $value = $matches[1];
                $field = str_replace('_UNIQUE', '', $matches[2]); // remove suffix if present
                return ucfirst($field) . " '{$value}' already exists";
            } else {
                return "Duplicate entry.";
            }

            // PostgreSQL unique violation
        } elseif ($code === '23505') {
            if (preg_match("/Key \(([^)]+)\)=\(([^)]+)\)/", $msg, $matches)) {
                $field = $matches[1];
                $value = $matches[2];
                return ucfirst($field) . " '{$value}' already exists";
            } else {
                return "Duplicate entry.";
            }
        } else {
            return $e->getMessage();
        }
    }
}
