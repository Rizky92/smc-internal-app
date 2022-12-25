<?php

namespace App\Support\Excel;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Str;
use Storage;
use Vtiful\Kernel\Excel;

class ExcelExport
{
    /**
     * The excel instance
     * 
     * @var \Vtiful\Kernel\Excel $excel
     */
    protected $excel;

    /**
     * Array of columns for the data
     * 
     * @var array<int|string,string> $columnHeaders
     */
    protected $columnHeaders = [];

    /**
     * Array of titles for page header
     * 
     * @var array<int,string> $pageHeaders
     */
    protected $pageHeaders = [];

    /**
     * The exported excel file name
     * 
     * @var string $filename
     */
    protected $filename;

    /**
     * Array of configurations for excel instance
     * 
     * @var array<int|string,string> $config
     */
    protected $config = [];

    /**
     * Array of sheet names for excel
     * 
     * @var array<int,string> $sheets
     */
    protected $sheets = [];

    /**
     * Base path for exported excel file
     * 
     * @var string $basePath
     */
    protected $basePath = 'excel';

    /**
     * The disk used to export the excel
     * 
     * @var string $disk
     */
    protected $disk = 'public';

    /**
     * Initialize a new object
     * 
     * @param  string $filename
     * @param  string $sheetName = 'Sheet 1'
     * @param  array<int|string,string> $config = []
     * 
     * @return self
     */
    public function __construct($filename, $sheetName = 'Sheet 1', array $config = [])
    {
        $this->filename = Str::of($filename)
            ->ltrim('/')
            ->rtrim('/')
            ->remove('excel/');

        $this->config = empty($config)
            ? ['path' => Storage::disk($this->disk)->path($this->basePath)]
            : $config;

        $this->sheets[0] = $sheetName;

        $this->excel = (new Excel($this->config))
            ->fileName($this->filename, $this->sheets[0]);

        return $this;
    }

    /**
     * Set the column headers to display for the cell data
     * 
     * @param  array<int|string,string> $columnHeaders = []
     * 
     * @return self
     */
    public function setColumnHeaders(array $columnHeaders = [])
    {
        $this->columnHeaders = $columnHeaders;

        return $this;
    }

    /**
     * Set the page headers for the given sheet or all sheets
     * 
     * @param  array<int,string> $pageHeaders = []
     * 
     * @return self
     */
    public function setPageHeaders(array $pageHeaders = [])
    {
        $this->pageHeaders = $pageHeaders;

        return $this;
    }

    /**
     * Set the type of data to be inserted to excel
     * 
     * @param  \Illuminate\Contracts\Support\Arrayable|array<int|string,mixed> $data
     * 
     * @return self
     */
    public function setData($data = [])
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $this->putColumnHeadersToCell();
        $this->putPageHeadersToCell();

        $this->excel->data($data);

        return $this;
    }

    /**
     * Add a new sheet to excel
     * 
     * @param  string $sheetName
     * 
     * @return self
     */
    public function addSheet($sheetName)
    {
        $this->excel->addSheet($sheetName)
            ->checkoutSheet($sheetName);

        $this->sheets = array_merge($this->sheets, [$sheetName]);

        return $this;
    }

    /**
     * Use currently available sheets
     * 
     * @param  string $sheetName
     * 
     * @return self
     * 
     * @throws \Exception
     */
    public function useSheet($sheetName)
    {
        if (! in_array($sheetName, $this->sheets, true)) {
            throw new Exception("No sheets are available for sheet \"{$sheetName}\".");
        }

        $this->excel->checkoutSheet($sheetName);

        return $this;
    }

    /**
     * Get currently available sheets
     * 
     * @return array<int,string>
     */
    public function getAvailableSheets()
    {
        return $this->sheets;
    }

    /**
     * Set the disk for excel export
     * 
     * @param  string $diskName
     * 
     * @return self
     */
    public function setDisk($diskName = 'public')
    {
        $this->disk = $diskName;

        return $this;
    }

    /**
     * Set the base path for the excel export
     * 
     * @param  string $basePath
     * 
     * @return self
     */
    public function setBasePath($basePath = 'excel')
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Export excel to file as downloadable
     * 
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export()
    {
        $this->excel->output();

        $exportedFilename = $this->basePath . '/' . $this->filename;

        return Storage::disk($this->disk)->download($exportedFilename);
    }

    /**
     * Create a new instance to generate excel
     * 
     * @param  string $filename
     * @param  string $sheetName
     * @param  array<int|string,string> $config
     * 
     * @return static
     */
    public static function make($filename, $sheetName = 'Sheet 1', array $config = [])
    {
        return new static($filename, $sheetName, $config);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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
