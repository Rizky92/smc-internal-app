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

        return $this;
    }

    public function setPageHeaders(array $pageHeaders)
    {
        $this->pageHeaders = $pageHeaders;

        return $this;
    }

    public function setData($data)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $this->putColumnHeadersToCell();
        $this->putPageHeadersToCell();

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
        } else {
            $this->excel->checkoutSheet($sheetName);
        }

        return $this;
    }

    public function export()
    {
        $this->excel->output();

        return Storage::disk('public')->download("excel/{$this->filename}");
    }

    protected function putColumnHeadersToCell()
    {
        if (empty($this->columnHeaders)) {
            throw new Exception("Cell column headers need to be set first!");
        }

        if (empty($this->pageHeaders)) {
            $this->excel->header($this->columnHeaders);

            return;
        }

        foreach (array_values($this->columnHeaders) as $id => $column) {
            $this->excel->insertText(count($this->pageHeaders), $id, $column);
        }

        $this->excel->insertText(count($this->pageHeaders) + 1, 0, '');
    }

    protected function putPageHeadersToCell()
    {
        if (empty($this->pageHeaders)) {
            return;
        }

        $colStart = $colEnd = 'A';

        for ($i = 0; $i < count($this->columnHeaders) - 1; $i++) {
            $colEnd++;
        }

        foreach (array_values($this->pageHeaders) as $id => $title) {
            $i = $id + 1;

            $this->excel->mergeCells("{$colStart}{$i}:{$colEnd}{$i}", $title);
        }
    }
}
