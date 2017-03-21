<?php namespace Pckg\Import;

use Exception;

class Log
{

    protected $start;

    protected $stop;

    protected $exceptions = [];

    protected $log = [];

    protected $stats = [];

    public function start()
    {
        $this->start = date("Y-m-d H:i:s");

        return $this;
    }

    public function stop()
    {
        $this->stop = date("Y-m-d H:i:s");

        return $this;
    }

    public function exception(Exception $e)
    {
        $this->exceptions[] = $e;
        $this->log($e->getMessage());

        return $this;
    }

    public function log($msg)
    {
        $this->log[] = date('Y-m-d H:i:s') . ' - ' . $msg;

        return $this;
    }

    public function updated()
    {
        return $this->count('updated');
    }

    protected function count($key)
    {
        if (!isset($this->stats[$key])) {
            $this->stats[$key] = 0;
        }

        $this->stats[$key]++;

        return $this;
    }

    public function updateFailed()
    {
        return $this->count('update failed');
    }

    public function inserted()
    {
        return $this->count('inserted');
    }

    public function insertFailed()
    {
        return $this->count('insert failed');
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }

    public function buildHtml()
    {
        $build = '<div>';

        $build .= '<p><strong>Started: </strong>' . $this->start . '</p>';
        $build .= '<p><strong>Stopped: </strong>' . $this->stop . '</p>';

        foreach ($this->stats as $stat => $count) {
            $build .= '<p><strong>' . $stat . ': </strong>' . $count . '</p>';
        }

        if ($this->log) {
            $build .= '<p><strong>Log: </strong><br />' . implode('<br />', $this->log) . '</p>';
        }

        if ($this->exceptions) {
            $build .= '<p><strong>Exceptions:</strong>';
            foreach ($this->exceptions as $e) {
                $build .= '<br /><b onclick="$(this).next().toggle(); return false;" style="cursor: pointer;">' . $e->getMessage(
                    ) . '</b>';
                $build .= '<span style="display: none;"><br />@ ' . $e->getFile() . ':' . $e->getLine();
                $build .= '<br />' . nl2br($e->getTraceAsString()) . '<br /></span>';
            }
            $build .= '</p>';
        }

        $build .= '</div>';

        return $build;
    }

}