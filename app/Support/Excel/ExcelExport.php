<?php

namespace App\Support\Excel;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Storage;
use Vtiful\Kernel\Excel;

class ExcelExport
{
    protected $excel;

    protected $columnHeaders = [];

    protected $pageHeaders = [];

    protected $filename;

    protected $config = [];

    protected $sheets = [];

    public function __construct($filename, $sheetName = 'Sheet 1', array $config = [])
    {
        $this->filename = Str::of($filename)
            ->ltrim('/')
            ->rtrim('/')
            ->remove('excel/');

        $this->config = empty($config)
            ? ['path' => storage_path('app/public/excel')]
            : $config;

        $this->sheets[0] = $sheetName;

        $this->excel = (new Excel($this->config))
            ->fileName($this->filename, $this->sheets[0]);

        return $this;
    }

    public function setColumnHeaders(array $columnHeaders)
    {
        $this->columnHeaders = $columnHeaders;

        if (empty($this->pageHeaders)) {
            $this->excel->header($columnHeaders);
        } else {
            foreach (array_values($this->columnHeaders) as $id => $column) {
                $this->excel->insertText(count($this->columnHeaders) - 1, $id, $column);
            }

            $this->excel->insertText(count($this->columnHeaders), 0, '');
        }

        return $this;
    }

    public function setPageHeaders(array $pageHeaders)
    {
        $this->pageHeaders = $pageHeaders;

        $colStart = $colEnd = 'A';

        for ($i = 0; $i < count($this->columnHeaders); $i++) {
            ++$colEnd;
        }

        foreach (array_values($pageHeaders) as $id => $title) {
            $i = $id + 1;

            $this->excel->mergeCells("{$colStart}{$i}:{$colEnd}{$i}", $title);
        }

        return $this;
    }

    public function setData($data)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $this->excel->data($data);

        return $this;
    }

    public function addSheet($sheetName)
    {
        $this->excel->addSheet($sheetName)
            ->checkoutSheet($sheetName);

        $this->sheets = array_merge($this->sheets, [$sheetName]);

        return $this;
    }

    public function useSheet($sheetName)
    {
        if (! in_array($sheetName, $this->sheets, true)) {
            $this->addSheet($sheetName);
        }

        return $this;
    }

    public function export()
    {
        $this->excel->output();

        return Storage::disk('public')->download("excel/{$this->filename}");
    }
}
