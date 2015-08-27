<?php

namespace Box\Spout\Writer\XLSX\Internal;

use Box\Spout\Writer\Exception\SheetNotFoundException;
use Box\Spout\Writer\XLSX\Helper\FileSystemHelper;
use Box\Spout\Writer\XLSX\Helper\SharedStringsHelper;
use Box\Spout\Writer\XLSX\Helper\StyleHelper;
use Box\Spout\Writer\XLSX\Sheet;

/**
 * Class Book
 * Represents a workbook within a XLSX file.
 * It provides the functions to work with worksheets.
 *
 * @package Box\Spout\Writer\XLSX\Internal
 */
class Workbook
{
    /**
     * Maximum number of rows a XLSX sheet can contain
     * @see http://office.microsoft.com/en-us/excel-help/excel-specifications-and-limits-HP010073849.aspx
     */
    protected static $maxRowsPerWorksheet = 1048576;

    /** @var bool Whether inline or shared strings should be used */
    protected $shouldUseInlineStrings;

    /** @var bool Whether new sheets should be automatically created when the max rows limit per sheet is reached */
    protected $shouldCreateNewSheetsAutomatically;

    /** @var \Box\Spout\Writer\XLSX\Helper\FileSystemHelper Helper to perform file system operations */
    protected $fileSystemHelper;

    /** @var \Box\Spout\Writer\XLSX\Helper\SharedStringsHelper Helper to write shared strings */
    protected $sharedStringsHelper;

    /** @var \Box\Spout\Writer\XLSX\Helper\StyleHelper Helper to apply styles */
    protected $styleHelper;

    /** @var Worksheet[] Array containing the workbook's sheets */
    protected $worksheets = [];

    /** @var Worksheet The worksheet where data will be written to */
    protected $currentWorksheet;

    /**
     * @param string $tempFolder
     * @param bool $shouldUseInlineStrings
     * @param bool $shouldCreateNewSheetsAutomatically
     * @param \Box\Spout\Writer\Style\Style $defaultRowStyle
     * @throws \Box\Spout\Common\Exception\IOException If unable to create at least one of the base folders
     */
    public function __construct($tempFolder, $shouldUseInlineStrings, $shouldCreateNewSheetsAutomatically, $defaultRowStyle)
    {
        $this->shouldUseInlineStrings = $shouldUseInlineStrings;
        $this->shouldCreateNewSheetsAutomatically = $shouldCreateNewSheetsAutomatically;

        $this->fileSystemHelper = new FileSystemHelper($tempFolder);
        $this->fileSystemHelper->createBaseFilesAndFolders();

        $this->styleHelper = new StyleHelper($defaultRowStyle);

        // This helper will be shared by all sheets
        $xlFolder = $this->fileSystemHelper->getXlFolder();
        $this->sharedStringsHelper = new SharedStringsHelper($xlFolder);
    }

    /**
     * Creates a new sheet in the workbook. The current sheet remains unchanged.
     *
     * @return Worksheet The created sheet
     * @throws \Box\Spout\Common\Exception\IOException If unable to open the sheet for writing
     */
    public function addNewSheet()
    {
        $newSheetIndex = count($this->worksheets);
        $sheet = new Sheet($newSheetIndex);

        $worksheetFilesFolder = $this->fileSystemHelper->getXlWorksheetsFolder();
        $worksheet = new Worksheet($sheet, $worksheetFilesFolder, $this->sharedStringsHelper, $this->shouldUseInlineStrings);
        $this->worksheets[] = $worksheet;

        return $worksheet;
    }

    /**
     * Creates a new sheet in the workbook and make it the current sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @return Worksheet The created sheet
     * @throws \Box\Spout\Common\Exception\IOException If unable to open the sheet for writing
     */
    public function addNewSheetAndMakeItCurrent()
    {
        $worksheet = $this->addNewSheet();
        $this->setCurrentWorksheet($worksheet);

        return $worksheet;
    }

    /**
     * @return Worksheet[] All the workbook's sheets
     */
    public function getWorksheets()
    {
        return $this->worksheets;
    }

    /**
     * Returns the current sheet
     *
     * @return Worksheet The current sheet
     */
    public function getCurrentWorksheet()
    {
        return $this->currentWorksheet;
    }

    /**
     * Sets the given sheet as the current one. New data will be written to this sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @param \Box\Spout\Writer\XLSX\Sheet $sheet The "external" sheet to set as current
     * @return void
     * @throws \Box\Spout\Writer\Exception\SheetNotFoundException If the given sheet does not exist in the workbook
     */
    public function setCurrentSheet($sheet)
    {
        $worksheet = $this->getWorksheetFromExternalSheet($sheet);
        if ($worksheet !== null) {
            $this->currentWorksheet = $worksheet;
        } else {
            throw new SheetNotFoundException('The given sheet does not exist in the workbook.');
        }
    }

    /**
     * @param Worksheet $worksheet
     * @return void
     */
    protected function setCurrentWorksheet($worksheet)
    {
        $this->currentWorksheet = $worksheet;
    }

    /**
     * Returns the worksheet associated to the given external sheet.
     *
     * @param \Box\Spout\Writer\XLSX\Sheet $sheet
     * @return Worksheet|null The worksheet associated to the given external sheet or null if not found.
     */
    protected function getWorksheetFromExternalSheet($sheet)
    {
        $worksheetFound = null;

        foreach ($this->worksheets as $worksheet) {
            if ($worksheet->getExternalSheet() === $sheet) {
                $worksheetFound = $worksheet;
                break;
            }
        }

        return $worksheetFound;
    }

    /**
     * Adds data to the current sheet.
     * If shouldCreateNewSheetsAutomatically option is set to true, it will handle pagination
     * with the creation of new worksheets if one worksheet has reached its maximum capicity.
     *
     * @param array $dataRow Array containing data to be written.
     *          Example $dataRow = ['data1', 1234, null, '', 'data5'];
     * @param \Box\Spout\Writer\Style\Style $style Style to be applied to the row.
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If trying to create a new sheet and unable to open the sheet for writing
     * @throws \Box\Spout\Writer\Exception\WriterException If unable to write data
     */
    public function addRowToCurrentWorksheet($dataRow, $style)
    {
        $currentWorksheet = $this->getCurrentWorksheet();
        $hasReachedMaxRows = $this->hasCurrentWorkseetReachedMaxRows();

        // if we reached the maximum number of rows for the current sheet...
        if ($hasReachedMaxRows) {
            // ... continue writing in a new sheet if option set
            if ($this->shouldCreateNewSheetsAutomatically) {
                $currentWorksheet = $this->addNewSheetAndMakeItCurrent();

                $updatedStyle = $this->styleHelper->applyExtraStylesIfNeeded($style, $dataRow);
                $registeredStyle = $this->styleHelper->registerStyle($updatedStyle);
                $currentWorksheet->addRow($dataRow, $registeredStyle);
            } else {
                // otherwise, do nothing as the data won't be read anyways
            }
        } else {
            $updatedStyle = $this->styleHelper->applyExtraStylesIfNeeded($style, $dataRow);
            $registeredStyle = $this->styleHelper->registerStyle($updatedStyle);
            $currentWorksheet->addRow($dataRow, $registeredStyle);
        }
    }

    /**
     * @return bool Whether the current worksheet has reached the maximum number of rows per sheet.
     */
    protected function hasCurrentWorkseetReachedMaxRows()
    {
        $currentWorksheet = $this->getCurrentWorksheet();
        return ($currentWorksheet->getLastWrittenRowIndex() >= self::$maxRowsPerWorksheet);
    }

    /**
     * Closes the workbook and all its associated sheets.
     * All the necessary files are written to disk and zipped together to create the XLSX file.
     * All the temporary files are then deleted.
     *
     * @param resource $finalFilePointer Pointer to the XLSX that will be created
     * @return void
     */
    public function close($finalFilePointer)
    {
        foreach ($this->worksheets as $worksheet) {
            $worksheet->close();
        }

        $this->sharedStringsHelper->close();

        // Finish creating all the necessary files before zipping everything together
        $this->fileSystemHelper
            ->createContentTypesFile($this->worksheets)
            ->createWorkbookFile($this->worksheets)
            ->createWorkbookRelsFile($this->worksheets)
            ->createStylesFile($this->styleHelper)
            ->zipRootFolderAndCopyToStream($finalFilePointer);

        $this->cleanupTempFolder();
    }

    /**
     * Deletes the root folder created in the temp folder and all its contents.
     *
     * @return void
     */
    protected function cleanupTempFolder()
    {
        $xlsxRootFolder = $this->fileSystemHelper->getRootFolder();
        $this->fileSystemHelper->deleteFolderRecursively($xlsxRootFolder);
    }
}
