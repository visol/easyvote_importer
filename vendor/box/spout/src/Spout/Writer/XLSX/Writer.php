<?php

namespace Box\Spout\Writer\XLSX;

use Box\Spout\Writer\AbstractWriter;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\XLSX\Internal\Workbook;

/**
 * Class Writer
 * This class provides base support to write data to XLSX files
 *
 * @package Box\Spout\Writer\XLSX
 */
class Writer extends AbstractWriter
{
    /** Default style font values */
    const DEFAULT_FONT_SIZE = 12;
    const DEFAULT_FONT_NAME = 'Calibri';

    /** @var string Content-Type value for the header */
    protected static $headerContentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** @var string Temporary folder where the files to create the XLSX will be stored */
    protected $tempFolder;

    /** @var bool Whether inline or shared strings should be used - inline string is more memory efficient */
    protected $shouldUseInlineStrings = true;

    /** @var bool Whether new sheets should be automatically created when the max rows limit per sheet is reached */
    protected $shouldCreateNewSheetsAutomatically = true;

    /** @var Internal\Workbook The workbook for the XLSX file */
    protected $book;

    /** @var int */
    protected $highestRowIndex = 0;

    /**
     * @param string $tempFolder Temporary folder where the files to create the XLSX will be stored
     * @return Writer
     */
    public function setTempFolder($tempFolder)
    {
        $this->tempFolder = $tempFolder;
        return $this;
    }

    /**
     * Use inline string to be more memory efficient. If set to false, it will use shared strings.
     *
     * @param bool $shouldUseInlineStrings Whether inline or shared strings should be used
     * @return Writer
     */
    public function setShouldUseInlineStrings($shouldUseInlineStrings)
    {
        $this->shouldUseInlineStrings = $shouldUseInlineStrings;
        return $this;
    }

    /**
     * @param bool $shouldCreateNewSheetsAutomatically Whether new sheets should be automatically created when the max rows limit per sheet is reached
     * @return Writer
     */
    public function setShouldCreateNewSheetsAutomatically($shouldCreateNewSheetsAutomatically)
    {
        $this->shouldCreateNewSheetsAutomatically = $shouldCreateNewSheetsAutomatically;
        return $this;
    }

    /**
     * Configures the write and sets the current sheet pointer to a new sheet.
     *
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If unable to open the file for writing
     */
    protected function openWriter()
    {
        if (!$this->book) {
            $tempFolder = ($this->tempFolder) ? : sys_get_temp_dir();
            $this->book = new Workbook($tempFolder, $this->shouldUseInlineStrings, $this->shouldCreateNewSheetsAutomatically, $this->defaultRowStyle);
            $this->book->addNewSheetAndMakeItCurrent();
        }
    }

    /**
     * Returns all the workbook's sheets
     *
     * @return Sheet[] All the workbook's sheets
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     */
    public function getSheets()
    {
        $this->throwIfBookIsNotAvailable();

        $externalSheets = [];
        $worksheets = $this->book->getWorksheets();

        /** @var Internal\Worksheet $worksheet */
        foreach ($worksheets as $worksheet) {
            $externalSheets[] = $worksheet->getExternalSheet();
        }

        return $externalSheets;
    }

    /**
     * Creates a new sheet and make it the current sheet. The data will now be written to this sheet.
     *
     * @return Sheet The created sheet
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     */
    public function addNewSheetAndMakeItCurrent()
    {
        $this->throwIfBookIsNotAvailable();
        $worksheet = $this->book->addNewSheetAndMakeItCurrent();

        return $worksheet->getExternalSheet();
    }

    /**
     * Returns the current sheet
     *
     * @return Sheet The current sheet
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     */
    public function getCurrentSheet()
    {
        $this->throwIfBookIsNotAvailable();
        return $this->book->getCurrentWorksheet()->getExternalSheet();
    }

    /**
     * Sets the given sheet as the current one. New data will be written to this sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @param Sheet $sheet The sheet to set as current
     * @return void
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     * @throws \Box\Spout\Writer\Exception\SheetNotFoundException If the given sheet does not exist in the workbook
     */
    public function setCurrentSheet($sheet)
    {
        $this->throwIfBookIsNotAvailable();
        $this->book->setCurrentSheet($sheet);
    }

    /**
     * Checks if the book has been created. Throws an exception if not created yet.
     *
     * @return void
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the book is not created yet
     */
    protected function throwIfBookIsNotAvailable()
    {
        if (!$this->book) {
            throw new WriterNotOpenedException('The writer must be opened before performing this action.');
        }
    }

    /**
     * Adds data to the currently opened writer.
     * If shouldCreateNewSheetsAutomatically option is set to true, it will handle pagination
     * with the creation of new worksheets if one worksheet has reached its maximum capicity.
     *
     * @param array $dataRow Array containing data to be written.
     *          Example $dataRow = ['data1', 1234, null, '', 'data5'];
     * @param \Box\Spout\Writer\Style\Style $style Style to be applied to the row.
     * @return void
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the book is not created yet
     * @throws \Box\Spout\Common\Exception\IOException If unable to write data
     */
    protected function addRowToWriter(array $dataRow, $style)
    {
        $this->throwIfBookIsNotAvailable();
        $this->book->addRowToCurrentWorksheet($dataRow, $style);
    }

    /**
     * Returns the default style to be applied to rows.
     *
     * @return \Box\Spout\Writer\Style\Style
     */
    protected function getDefaultRowStyle()
    {
        return (new StyleBuilder())
            ->setFontSize(self::DEFAULT_FONT_SIZE)
            ->setFontName(self::DEFAULT_FONT_NAME)
            ->build();
    }

    /**
     * Closes the writer, preventing any additional writing.
     *
     * @return void
     */
    protected function closeWriter()
    {
        if ($this->book) {
            $this->book->close($this->filePointer);
        }
    }
}
