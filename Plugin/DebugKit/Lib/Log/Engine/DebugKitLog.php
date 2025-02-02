<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * A CakeLog listener which saves having to munge files or other configured loggers.
 */
class DebugKitLog implements CakeLogInterface
{
    /**
     * logs
     *
     * @var array
     */
    public $logs = [];

    /**
     * Makes the reverse link needed to get the logs later.
     *
     * @param array $options Options.
     * @return \DebugKitLog
     */
    public function __construct($options)
    {
        $options["panel"]->logger = $this;
    }

    /**
     * Captures log messages in memory
     *
     * @param string $type Type of log message.
     * @param string $message The log message.
     * @return void
     */
    public function write($type, $message)
    {
        if (!isset($this->logs[$type])) {
            $this->logs[$type] = [];
        }
        $this->logs[$type][] = [date("Y-m-d H:i:s"), (string) $message];
    }
}
