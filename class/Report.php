<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

define('POINT', .35);   // Milimeters

abstract class Report
{
    protected $data;
}

abstract class ReportRegistration
{
    private $name;
    private $class;
    private $formats;

    public final function __construct()
    {
        $formats = array();
        $this->init();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getFormats()
    {
        return $this->formats;
    }

    protected abstract function init();

    protected function setName($name)
    {
        $this->name = $name;
    }

    protected function setClass($class)
    {
        $this->class = $class;
    }

    protected function provideHtml()
    {
        $this->formats[] = 'html';
    }

    protected function providePdf()
    {
        $this->formats[] = 'pdf';
    }

    protected function provideCsv()
    {
        $this->formats[] = 'csv';
    }
}

?>
