<?php

namespace pfs\yii\web;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use pfs\yii\helpers\Html;

class Export extends \yii\base\Component
{
    const EXPORT_CSV = 'csv';
    const EXPORT_EMAIL = 'email';
    const EXPORT_EXCEL = 'excel';
    const EXPORT_HTML = 'html';
    const EXPORT_JSON = 'json';
    const EXPORT_PDF = 'pdf';
    const EXPORT_PRINT = 'print';
    const EXPORT_WORD = 'word';
    const EXPORT_XML = 'xml';

    const EXPORT_TYPE_LIST = 'list';
    const EXPORT_TYPE_DETAIL = 'detail';

    /**
     * @var array Export types and details
     */
    public $types = [
        'csv' => [
            'ext' => 'csv',
            'mime' => 'text/csv'
        ],
        'email' => [
            'ext' => '',
            'mime' => ''
        ],
        'excel' => [
            'ext' => 'xls',
            'mime' => 'application/msexcel'
        ],
        'html' => [
            'ext' => 'html',
            'mime' => 'application/html'
        ],
        'json' => [
            'ext' => 'json',
            'mime' => 'application/json'
        ],
        'pdf' => [
            'ext' => 'pdf',
            'mime' => 'applciation/pdf'
        ],
        'print' => [
            'ext' => '',
            'mime' => 'applciation/html'
        ],
        'word' => [
            'ext' => 'doc',
            'mime' => 'application/msword'
        ],
        'xml' => [
            'ext' => 'xml',
            'mime' => 'application/xml'
        ]
    ];

    /**
     * @var array Columns for exports.
     */
    public $columns = [];

    /**
     * @var array RAW Html supports.
     */
    public $htmlRawSuuport = [
        'pdf',
        'html',
        'print',
        'email'
    ];

    /**
     * @var string Title of document.
     */
    public $title = '';

    /**
     * @var boolean Enabled title of columns.
     */
    public $titleColumns = true;

    /**
     * @var string Text heading for report.
     */
    public $header = '';

    /**
     * @var string Text description for reoprt.
     */
    public $description = '';

    /**
     * @var string Report file name.
     */
    public $fileName = 'report';

    /**
     * @var boolean Enable download report.
     */
    public $download = false;

    /**
     * @var boolean Enable force download from URL.
     */
    public $forceDownload = true;

    /**
     * @var string Param for download report on URL
     */
    public $forceDownloadParam = 'export-download';

    /**
     * @var boolean Enable strip tags for all columns.
     */
    public $stripTags = false;

    /**
     * @var string Strip tags param for enable strip tags on URL.
     */
    public $stripTagsParam = 'export-strip-tags';

    /**
     * @var string Report orientation.
     */
    public $orientation = 'portrait';

    /**
     * @var string Report paper size.
     */
    public $paperSize = 'A4';

    /**
     * @var string CSV separator.
     */
    public $csvSeparator = ',';

    /**
     * @var string CSV Tag.
     */
    public $csvTag = '"';

    /**
     * @var string Charset report
     */
    public $charset = 'utf-8';

    /**
     * @var string Type of report.
     */
    public $type = 'list';

    /**
     * @var array Primary Keys.
     */
    public $keys = [];

    /**
     * @var array Pages lists
     */
    public $pages = [
        0 => ''
    ];

    /**
     * @var string Controller id
     */
    public $controller;

    /**
     * @var object DataProvider
     */
    public $dataProvider;

    /**
     * @var array|string Export to type(s)
     */
    public $exportTo;

    /**
     * @var string Param to export on a specific type.
     */
    public $exportToParam = 'export-to';

    /** 
     * @var object PDF driver to export reports in pdf format.
     */
    public $pdfDriver;

    /** 
     * @var object Excel driver to export reports in excel format.
     */
    public $excelDriver;

    /** 
     * @var object Word driver to export reports in word format.
     */
    public $wordDriver;

    /**
     * Export execute.
     */
    public function export()
    {
        if (empty($this->columns)) {
            throw new InvalidConfigException('The "columns" property must be set.');
        }

        if (!$this->dataProvider) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }

        if (!$this->controller) {
            throw new InvalidConfigException('The "controller" property must be set.');
        }

        if ($this->forceDownload && isset(Yii::$app->request->queryParams[$this->forceDownloadParam])) {
            $download = ArrayHelper::getValue(Yii::$app->request->queryParams, $this->forceDownloadParam, 'true');
            $this->download = trim($download) == 'true' ? true : false;
        }

        if (isset(Yii::$app->request->queryParams[$this->stripTagsParam])) {
            $stripTags = ArrayHelper::getValue(Yii::$app->request->queryParams, $this->stripTagsParam, 'false');
            $this->stripTags = trim($stripTags) == 'true' ? true : false;
        }

        if (is_array($this->exportTo)) {
            if (isset(Yii::$app->request->queryParams[$this->exportToParam])) {
                $exportTo = ArrayHelper::getValue(Yii::$app->request->queryParams, $this->exportToParam);
                if (!in_array($exportTo, $this->exportTo)) {
                    throw new InvalidConfigException('Export to "'. $exportTo .'" not support.');
                }

                if (!isset($this->types[$exportTo])) {
                    throw new InvalidConfigException('Export to "'. $exportTo .'" not support.');
                }
            } else {
                throw new InvalidConfigException('The "exportTo" param not set.');
            }
        } else if (!isset($this->types[ (string) $this->exportTo ])) {
            throw new InvalidConfigException('The "exportTo" property failed.');
        } else {
            $exportTo = $this->exportTo;
        }

        $this->exportTo = $exportTo;
        return call_user_func([$this, 'exportTo'. ucfirst($this->exportTo)]);
    }

    /**
     * Directing reports to be downloaded or displayed.
     * @param string $string Report document.
     * @param boolean $forceDownload Force download report.
     * @return string;
     */
    protected function setContentHeader($string, $forceDownload = false)
    {
        $type = $this->types[$this->exportTo];
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;

        if ($this->exportTo !== 'html') {
            $headers->add('Content-Type', $type['mime']);
        }

        if ($this->download || $forceDownload) {
            $headers->add('Content-Disposition', 'attachment; filename='. $this->fileName .'.'. $type['ext']);
        }
        return $string;
    }

    /**
     * Make a list report.
     * @param boolean $isFOrOffice Create report for office
     * @return string
     */
    protected function tableLists($isForOffice = true)
    {
        $models = $this->fetchDataProvider();
        $tableOptions = [];
        if ($isForOffice) {
            Html::addCssClass($tableOptions, 'MsoTableGrid');
            ArrayHelper::merge($tableOptions, [
                'border' => '1',
                'cellpadding' => '0',
                'cellspacing' => '0',
            ]);
            Html::addCssStyle($tableOptions, [
                'border-collapse' => 'collapse',
                'border' => 'none',
                'mso-border-alt' => 'solid windowtext .5pt',
                'mso-yfti-tbllook' => '1184',
                'mso-padding-alt' => '0cm 5.4pt 0cm 5.4pt'
            ]);
        }
        $result = [];
        $result[] = Html::beginTag('table', $tableOptions);

        if (count($models)) {
            if ($this->titleColumns) {
                $result[] = Html::beginTag('thead');
                $result[] = Html::beginTag('tr');
                foreach ($this->columns as $column) {
                    $name = $column;
                    if (is_array($column)) {
                        $name = $column['name'];
                    }
                    $result[] = Html::tag('th', strip_tags(Yii::t($this->controller, $name .'.caption')));
                }
                $result[] = Html::endTag('tr');
                $result[] = Html::endTag('thead');
            }

            $result[] = Html::beginTag('tbody');
            foreach (array_values($models) as $model) {
                $result[] = Html::beginTag('tr');
                foreach ($this->columns as $column) {
                    if (is_array($column)) {
                        $name = $column['name'];
                        if (isset($column['value']) && $column['value'] instanceof \Closure) {
                            $value = call_user_func($column['value'], $model, $this->getKeys($model), false, false);
                        } else {
                            if ($model->hasProperty($name)) {
                                $value = ArrayHelper::getValue($model, $name, '');
                            } else {
                                $value = '';
                            }
                        }
                    } else {
                        $name = $column;
                        if ($model->hasProperty($name)) {
                            $value = ArrayHelper::getValue($model, $name, '');
                        } else {
                            $value = '';
                        }
                    }

                    if ($this->stripTags) {
                        $value = strip_tags($value);
                    }

                    if (in_array($this->exportTo, $this->htmlRawSuuport) === false) {
                        $result[] = Html::tag('td', strip_tags($value));
                    } else {
                        $result[] = Html::tag('td', $value);
                    }
                }
                $result[] = Html::endTag('tr');
            }
            $result[] = Html::endTag('tbody');
        }

        $result[] = Html::endTag('table');
        return implode("", $result);
    }

    /**
     * Make a detail report.
     * @param boolean $isFOrOffice Create report for office
     * @return string
     */
    protected function tableDetail($isForOffice = true) 
    {
        $models = $this->fetchDataProvider();
        $model = $models[0];
        $tableOptions = [];
        if ($isForOffice) {
            Html::addCssClass($tableOptions, 'MsoTableGrid');
            ArrayHelper::merge($tableOptions, [
                'border' => '1',
                'cellpadding' => '0',
                'cellspacing' => '0',
            ]);
            Html::addCssStyle($tableOptions, [
                'border-collapse' => 'collapse',
                'border' => 'none',
                'mso-border-alt' => 'solid windowtext .5pt',
                'mso-yfti-tbllook' => '1184',
                'mso-padding-alt' => '0cm 5.4pt 0cm 5.4pt'
            ]);
        }
        $result = [];

        $pages = [];
        foreach ($this->columns as $column) {
            if (is_array($column) && isset($column['page'])) {
                $page = (int) $column['page'];
                if (in_array($page, $pages) === false) {
                    array_push($pages, $page);
                }
            } else {
                if (in_array(0, $pages) === false) {
                    array_push($pages, 0);
                }
            }
        }

        foreach ($pages as $page) {
            if ($page > 0) {
                if ($isForOffice) {
                    $result[] = '<br />';
                }

                $result[] = Html::tag(($isForOffice ? 'h3' : 'p'), strip_tags(Yii::t($this->controller, 'page-'. $page)), [
                    'style' => ['margin' => '20px 3px 8px 3px']
                ]);        
            }

            $result[] = Html::beginTag('table', $tableOptions);

            foreach ($this->columns as $column) {
                if (is_array($column)) {
                    $name = $column['name'];
                    $columnPage = (int) ArrayHelper::getValue($column, 'page', 0);
                    if (isset($column['value']) && $column['value'] instanceof \Closure) {
                        $value = call_user_func($column['value'], $model, $this->getKeys($model), false, false);
                    } else {
                        if ($model->hasProperty($name)) {
                            $value = ArrayHelper::getValue($model, $name, '');
                        } else {
                            $value = '';
                        }
                    }
                } else {
                    $name = $column;
                    $columnPage = 0;
                    if ($model->hasProperty($name)) {
                        $value = ArrayHelper::getValue($model, $name, '');
                    } else {
                        $value = '';
                    }
                }

                if ($this->stripTags) {
                    $value = strip_tags($value);
                }


                if ($page === $columnPage) {

                    $result[] = Html::beginTag('tr');
                    if ($this->titleColumns) {
                        $result[] = Html::tag('td', strip_tags(Yii::t($this->controller, $name .'.caption')), [
                            'style' => 'width: 30%'
                        ]);
                    }
                    if (in_array($this->exportTo, $this->htmlRawSuuport) === false) {
                        $result[] = Html::tag('td', strip_tags($value));
                    } else {
                        $result[] = Html::tag('td', $value);
                    }
                    $result[] = Html::endTag('tr');

                }
            }
            $result[] = Html::endTag('table');
        }

        return implode("", $result);
    }

    /**
     * Export to HTML format.
     * @return boolean return report
     * @return string
     */
    protected function exportToHtml($return = false)
    {
        if ($this->type === self::EXPORT_TYPE_LIST) {
            $table = $this->tableLists(false);
        } else if ($this->type == self::EXPORT_TYPE_DETAIL) {
            $table = $this->tableDetail(false);
        } else {
            throw new InvalidConfigException('The "type" property failed.');
        }

        $document = '<!DOCTYPE html><html lang="en"><head><title>'. $this->title .'</title><style type="text/css">table{border-collapse:collapse;border-spacing:0;background-color:transparent;font-size:9pt;width:100%;max-width: 100%;margin-bottom:20px;border:1px solid #999;}th{font-weight: bold}td,th{padding:0;color:#333;}table>thead>tr>th,table>tbody>tr>th,table>tfoot>tr>th,table>thead>tr>td,table>tbody>tr>td,table>tfoot>tr>td{padding:8px;line-height:1.42857143;vertical-align:top;border-top: 1px solid #999;}table>thead>tr>th{vertical-align: bottom;border-bottom: 2px solid #999;}table>thead>tr>th,table>tbody>tr>th,table>tfoot>tr>th,table>thead>tr>td,table>tbody>tr>td,table>tfoot>tr>td{border:1px solid #999;}table>thead>tr>th,table>thead>tr>td{border-bottom-width:1px;}table>tbody>tr:nth-of-type(odd){background-color:#f5f5f5;}tbody>tr:hover,tbody>tr:nth-of-type(odd):hover{background-color: #f0f0f0}.label-detail{font-weight:bold;text-align:right}a{text-decoration:none;color:#0e7dc3}a:hover{color:#92c0dc}</style></head><body>'. $table .'</body></html>';
        if ($return === false) {
            return $this->setContentHeader($document);
        } else {
            return $document;
        }
    }

    /**
     * Print report.
     * @return string
     */
    protected function exportToPrint()
    {
        $document = $this->exportToHtml(true);
        $document .= Html::script('window.print()');
        return $document; 
    }

    /**
     * Export to email.
     * @return string
     */
    protected function exportToEmail()
    {
        return $this->exportToHtml(true);
    }

    /**
     * Export to PDF format.
     * @return string
     */
    protected function exportToPdf()
    {
        if ($this->pdfDriver && $this->pdfDriver instanceof \Closure) {
            return call_user_func($this->pdfDriver, $this->exportToHtml(true));
        } else {
            return $this->exportToPrint();
        }
    }

    /**
     * Export to DOC format.
     * @return string
     */
    protected function exportToWord()
    {
        if ($this->type === self::EXPORT_TYPE_LIST) {
            $table = $this->tableLists(false);
        } else if ($this->type == self::EXPORT_TYPE_DETAIL) {
            $table = $this->tableDetail(false);
        } else {
            throw new InvalidConfigException('The "type" property failed.');
        }

        $document = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns:css="http://macVmlSchemaUri" xmlns="http://www.w3.org/TR/REC-html40"><head><meta name="Title" content="'. $this->fileName .'"/><meta name=Keywords content=""><meta http-equiv=Content-Type content="text/html; charset=unicode"><meta name=ProgId content=Word.Document><meta name=Generator content="Microsoft Word 14"><meta name="Originator" content="Microsoft Word 14"/><link rel="File-List" href="Customer%20(5)_files/filelist.xml"/><!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View></w:WordDocument></xml><![endif]--><style>'. 
                (
                    $this->orientation == 'potrait' 
                    ? '<!--@page WORDSECTION1 {mso-page-orientation:potrait;} -->' 
                    : '<!--@page WordSection1 {size:792.0pt 612.0pt; mso-page-orientation:landscape; margin:90.0pt 72.0pt 90.0pt 72.0pt; mso-header-margin:35.4pt; mso-footer-margin:35.4pt; mso-paper-source:0;}div.WordSection1 {page:WordSection1;}table.MsoTableGrid{ width: 100%; margin-bottom: 6pt; margin-top: 6pt;}.label-detail{ font-weight: bold; text-align: right }-->'
                ) .
            '</style></head><body bgcolor=white lang=EN-US style=\'tab-interval:36.0pt\'><div class=WordSection1>'. $table .'<p class=MsoNormal style=\'mso-margin-top-alt:auto;mso-margin-bottom-alt:auto\'><span style=\'mso-fareast-font-family:"Times New Roman";mso-bidi-font-family:"Times New Roman"\'><o:p>&nbsp;</o:p></span></p></div></body></html>';


        return $this->setContentHeader($document, true);
    }

    /**
     * Export to XLS format.
     * @return string
     */
    protected function exportToExcel()
    {
        if ($this->type === self::EXPORT_TYPE_LIST) {
            $table = $this->tableLists(false);
        } else if ($this->type == self::EXPORT_TYPE_DETAIL) {
            $table = $this->tableDetail(false);
        } else {
            throw new InvalidConfigException('The "type" property failed.');
        }

        $document = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="Content-Type" content="text/html; charset=windows-1252"/><meta name="ProgId" content="Excel.Sheet"/><meta name="Generator" content="Microsoft Excel 11"/><style> <!--table @page{}--></style><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sheet1</x:Name><x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></ x:ExcelWorkbook></xml><![endif]--><body>'. $table .'</body></html>';


        return $this->setContentHeader($document, true);
    }

    /**
     * Export to XML format.
     * @return string
     */
    protected function exportToXml()
    {
        $models = $this->fetchDataProvider();
        $result = [];
        $result = "<?xml version=\"1.0\" encoding=\"{$this->charset}\"?>\n";
        $result .= HTML::beginTag('Data');

        if (count($models)) {
            foreach (array_values($models) as $index => $model) {
                $result .= "\n\t";
                $result .= Html::beginTag('Row');
                foreach ($this->columns as $column) {
                    if (is_array($column)) {
                        $name = $column['name'];
                        if (isset($column['value']) && $column['value'] instanceof \Closure) {
                            $value = call_user_func($column['value'], $model, $this->getKeys($model), false, false);
                        } else {
                            if ($model->hasProperty($name)) {
                                $value = ArrayHelper::getValue($model, $name, '');
                            } else {
                                $value = '';
                            }
                        }
                    } else {
                        $name = $column;
                        if ($model->hasProperty($name)) {
                            $value = ArrayHelper::getValue($model, $name, '');
                        } else {
                            $value = '';
                        }
                    }

                    if ($this->stripTags) {
                        $value = strip_tags($value);
                    }

                    $result .= "\n\t\t";
                    $result .= Html::tag($name, Html::encode($value));
                }
                $result .= "\n\t";
                $result .= Html::endTag('Row');
            }
        }

        $result .= "\n";
        $result .= Html::endTag('Data');

        return $this->setContentHeader($result);
    }

    /**
     * Export to JSON format.
     * @return string
     */
    protected function exportToJson()
    {
        $models = $this->fetchDataProvider();
        $result = [
            'Data' => [
                'Row' => [

                ]
            ]
        ];


        if (count($models)) {
            foreach (array_values($models) as $index => $model) {
                $row = [];
                foreach ($this->columns as $column) {
                    if (is_array($column)) {
                        $name = $column['name'];
                        if (isset($column['value']) && $column['value'] instanceof \Closure) {
                            $value = call_user_func($column['value'], $model, $this->getKeys($model), false, false);
                        } else {
                            if ($model->hasProperty($name)) {
                                $value = ArrayHelper::getValue($model, $name, '');
                            } else {
                                $value = '';
                            }
                        }
                    } else {
                        $name = $column;
                        if ($model->hasProperty($name)) {
                            $value = ArrayHelper::getValue($model, $name, '');
                        } else {
                            $value = '';
                        }
                    }

                    if ($this->stripTags) {
                        $value = strip_tags($value);
                    }

                    $row[] = Html::encode($value);
                }
                $result['Data']['Row'][] = $row;
            }
        }

        return $this->setContentHeader(json_encode($result));
    }

    /**
     * Export to CSV format.
     * @return string
     */
    protected function exportToCsv()
    {
        $models = $this->fetchDataProvider();
        $result = [];

        if (count($models)) {
            if ($this->titleColumns) {
                $line = [];
                foreach ($this->columns as $column) {
                    if (is_array($column)) {
                        $line[] = $this->csvLine(strip_tags(Yii::t($this->controller, $column['name'] .'.caption')));
                    } else {
                        $line[] = $this->csvLine(strip_tags(Yii::t($this->controller, $column .'.caption')));
                    }
                }
                $result[] = implode($this->csvSeparator, $line);
            }
        }

        foreach (array_values($models) as $index => $model) {
            $line = [];
            foreach ($this->columns as $column) {
                if (is_array($column)) {
                    $name = $column['name'];
                    if (isset($column['value']) && $column['value'] instanceof \Closure) {
                        $value = call_user_func($column['value'], $model, $this->getKeys($model), false, false);
                    } else {
                        if ($model->hasProperty($name)) {
                            $value = ArrayHelper::getValue($model, $name, '');
                        } else {
                            $value = '';
                        }
                    }
                } else {
                    $name = $column;
                    if ($model->hasProperty($name)) {
                        $value = ArrayHelper::getValue($model, $name, '');
                    } else {
                        $value = '';
                    }
                }

                if ($this->stripTags) {
                    $value = strip_tags($value);
                }

                $line[] = $this->csvLine(strip_tags($value));    
            }
            $result[] = implode($this->csvSeparator, $line);
        }

        return $this->setContentHeader(implode("\n", $result), true);
    }

    /**
     * Create csv line
     * @param string $string Line of csv
     * @param boolean $escape Adding slashes for double quotes.
     * @return string
     */
    protected function csvLine($string, $escape = true)
    {
        return $this->csvTag . ($escape ? $this->doubleQuoteSlashes($string) : $string) . $this->csvTag; 
    }

    /**
     * Double quote slashes
     * @param string
     * @return string
     */
    protected function doubleQuoteSlashes($string)
    {
        return implode('\"', explode('"', $string));
    }

    /**
     * Manage dataProvider
     * @return array
     */
    protected function fetchDataProvider()
    {
        if ($this->dataProvider instanceof \yii\data\BaseDataProvider) {
            return $this->dataProvider->getModels();
        } else if ($this->dataProvider instanceof \yii\base\Model) {
            return [$this->dataProvider];
        } else {
            throw new InvalidConfigException('The "dataProvider" property failed.');
        }
    }


    /**
     * Manage PrimaryKeys
     * @return array
     */
    protected function getKeys($model) 
    {
        if (is_string($this->keys)) {
            return ArrayHelper::getValue($model, $this->keys);
        } else if (is_array($this->keys) && count($this->keys)) {
            $result = [];
            foreach ($this->keys as $key) {
                $result[$key] = ArrayHelper::getValue($model, $key);
            }
            return $result;
        }

        return false;
    }
}