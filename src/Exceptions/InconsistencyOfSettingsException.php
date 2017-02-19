<?php
/**
 * Hasher requires a given salt
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/auth
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Settings\Exceptions;

class InconsistencyOfSettingsException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $message = "You requested some settings that are not in your database: ";

    /**
     * @param string $class
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($settings, $code = 0, \Exception $previous = null)
    {
        $message = $this->message . ' ' . implode(', ', $settings);

        parent::__construct($message, $code, $previous);
    }
}
