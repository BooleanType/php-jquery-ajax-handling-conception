<?php

/**
 * Generates specific string: encoded array with status code and error message.
 * This string is AJAX response message in case, when exception is thrown.
 * Usage:
 *      throw new Exception(errMsg(403, 'AccessRestricted'));
 * @param int $statusCode
 * @param string|arr $err Error message
 * @return string Err message with specific structure for AJAX response in exception throwing case
 */
function errMsg($statusCode = 500, $err = null)
{
    $statusCode = (int) $statusCode;

    // By default.
    $errMsg = 'Error';

    if (is_scalar($err) && !is_bool($err)) { // Integer, float, string.
        $errMsg = $err;
    } elseif (is_array($err)) {
        $errMsg = multiImplode($err);
    }

    return json_encode([$statusCode, $errMsg]);
}

/**
 * Recursively implodes multi-dimensional array $array.
 * For ex.:
 * $arr = [
 *     'field_1' => [
 *   		0 => 'field_1 description.',
 *   		1 => 'Another field_1 description.',
 *     ],
 *     'field_2' => [
 *          0 => 'field_2 description.',
 *          1 => 'Another field_2 description.',
 *     ]
 * ];
 * 
 * // "field_1 description., Another field_1 description., field_2 description., Another field_2 description."
 * $imploded = multiImplode($arr);
 * @param array $array The array of strings to implode
 * @param string $glue The glue to join the pieces of the array
 */
function multiImplode($array, $glue = ', ')
{
    $ret = '';

    foreach ($array as $item) {
        if (is_array($item)) {
            $ret .= multiImplode($item, $glue) . $glue;
        } else {
            $ret .= $item . $glue;
        }
    }

    // If $ret = '', substr(...) returns FALSE.
    $ret = ($ret !== '') ? substr($ret, 0, 0 - strlen($glue)) : $ret;

    return $ret;
}
