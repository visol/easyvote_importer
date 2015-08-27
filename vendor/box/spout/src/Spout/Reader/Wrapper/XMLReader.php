<?php

namespace Box\Spout\Reader\Wrapper;


/**
 * Class XMLReader
 * Wrapper around the built-in XMLReader
 * @see \XMLReader
 *
 * @package Box\Spout\Reader\Wrapper
 */
class XMLReader extends \XMLReader
{
    use XMLInternalErrorsHelper;

    /**
     * Set the URI containing the XML to parse
     * @see \XMLReader::open
     *
     * @param string $URI URI pointing to the document
   	 * @param string|null|void $encoding The document encoding
   	 * @param int $options A bitmask of the LIBXML_* constants
     * @return bool TRUE on success or FALSE on failure
     */
    public function open($URI, $encoding = null, $options = 0)
    {
        $wasOpenSuccessful = false;

        // HHVM does not check if file exists within zip file
        // @link https://github.com/facebook/hhvm/issues/5779
        if ($this->isRunningHHVM() && $this->isZipStream($URI)) {
            if ($this->fileExistsWithinZip($URI)) {
                $wasOpenSuccessful = parent::open($URI, $encoding, $options|LIBXML_NONET);
            }
        } else {
            $wasOpenSuccessful = parent::open($URI, $encoding, $options|LIBXML_NONET);
        }

        return $wasOpenSuccessful;
    }

    /**
     * Returns whether the given URI is a zip stream.
     *
     * @param string $URI URI pointing to a document
     * @return bool TRUE if URI is a zip stream, FALSE otherwise
     */
    protected function isZipStream($URI)
    {
        return (strpos($URI, 'zip://') === 0);
    }

    /**
     * Returns whether the current environment is HHVM
     *
     * @return bool TRUE if running on HHVM, FALSE otherwise
     */
    protected function isRunningHHVM()
    {
        return defined('HHVM_VERSION');
    }

    /**
     * Returns whether the file at the given location exists
     *
     * @param string $zipStreamURI URI of a zip stream, e.g. "zip://file.zip#path/inside.xml"
     * @return bool TRUE if the file exists, FALSE otherwise
     */
    protected function fileExistsWithinZip($zipStreamURI)
    {
        $doesFileExists = false;

        $pattern = '/zip:\/\/([^#]+)#(.*)/';
        if (preg_match($pattern, $zipStreamURI, $matches)) {
            $zipFilePath = $matches[1];
            $innerFilePath = $matches[2];

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath) === true) {
                $doesFileExists = ($zip->locateName($innerFilePath) !== false);
                $zip->close();
            }
        }

        return $doesFileExists;
    }

    /**
     * Move to next node in document
     * @see \XMLReader::read
     *
     * @return bool TRUE on success or FALSE on failure
     * @throws \Box\Spout\Reader\Exception\XMLProcessingException If an error/warning occurred
     */
    public function read()
    {
        $this->useXMLInternalErrors();

        $wasReadSuccessful = parent::read();

        $this->resetXMLInternalErrorsSettingAndThrowIfXMLErrorOccured();

        return $wasReadSuccessful;
    }

    /**
     * Move cursor to next node skipping all subtrees
     * @see \XMLReader::next
     *
     * @param string|void $localName The name of the next node to move to
     * @return bool TRUE on success or FALSE on failure
     * @throws \Box\Spout\Reader\Exception\XMLProcessingException If an error/warning occurred
     */
    public function next($localName = null)
    {
        $this->useXMLInternalErrors();

        $wasNextSuccessful = parent::next($localName);

        $this->resetXMLInternalErrorsSettingAndThrowIfXMLErrorOccured();

        return $wasNextSuccessful;
    }
}
