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
 * @since         DebugKit 1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses("AppHelper", "View/Helper");
App::uses("HtmlHelper", "View/Helper");

/**
 * Class SimpleGraphHelper
 *
 * Allows creation and display of extremely simple graphing elements
 *
 * @since         DebugKit 1.0
 */
class SimpleGraphHelper extends AppHelper
{
    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = ["Html"];

    /**
     * Default settings to be applied to each Simple Graph
     *
     * Allowed options:
     *
     * - max => (int) Maximum value in the graphs
     * - width => (int)
     * - valueType => string (value, percentage)
     * - style => array
     *
     * @var array
     */
    protected $_defaultSettings = [
        "max" => 100,
        "width" => 350,
        "valueType" => "value",
    ];

    /**
     * Bar
     *
     * @param mixed $value Value to be graphed.
     * @param mixed $offset How much indentation.
     * @param array|\Graph $options Graph options.
     * @return string HTML graph.
     */
    public function bar($value, $offset, $options = [])
    {
        $settings = array_merge($this->_defaultSettings, $options);
        extract($settings);

        $graphValue = ($value / $max) * $width;
        $graphValue = max(round($graphValue), 1);

        if ($valueType === "percentage") {
            $graphOffset = 0;
        } else {
            $graphOffset = ($offset / $max) * $width;
            $graphOffset = round($graphOffset);
        }
        return $this->Html->div(
            "debug-kit-graph-bar",
            $this->Html->div("debug-kit-graph-bar-value", " ", [
                "style" => "margin-left: {$graphOffset}px; width: {$graphValue}px",
                "title" => __d(
                    "debug_kit",
                    "Starting %sms into the request, taking %sms",
                    $offset,
                    $value
                ),
            ]),
            ["style" => "width: {$width}px;"],
            false
        );
    }
}
