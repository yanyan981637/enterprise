<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\ContactForms\Plugin\Model\Export;

use Magento\Framework\Filesystem\DirectoryList;

class ConvertToCsvPlugin
{
    private $result;
    private $filename;
    private $inputCsvArray;
    private $outputCsvHeaders;
    private $varDir;

    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->varDir = $directoryList->getPath('var');
        return $this;
    }

    public function afterGetCsvFile(
        \Magento\Ui\Model\Export\ConvertToCsv $subject,
        $result
    ) {
        if(empty($result['value'])) {
            return $result;
        }

        $this->result = $result;
        $this->filename = $result['value'];

        if(!stristr($this->filename, 'plugincompany_entry_index')) {
            return $this->result;
        }

        $this->initInputCsvArray();
        $this->updateCsvWithSubmissionColumns();

        return $this->result;
    }

    private function updateCsvWithSubmissionColumns()
    {
        $fh = fopen($this->getCsvFileName(), 'w');
        fputcsv($fh, $this->getOutputCsvHeaders());
        foreach($this->getInputCsvContents() as $row) {
            if(empty($row)) continue;
            fputcsv($fh, $this->getOutputForRow($row));
        }
        fclose($fh);
    }

    private function getOutputCsvHeaders()
    {
        if(!$this->outputCsvHeaders){
            $this->initOutputCsvHeaders();
        }
        return $this->outputCsvHeaders;
    }

    private function initOutputCsvHeaders()
    {
        $headers = $this->getInputCsvHeaders();
        unset($headers[$this->getFormSubmissionColumnNumber()]);
        $headers = array_values($headers);
        $this->outputCsvHeaders = array_merge($headers, $this->getAdditionalColumns());
    }

    private function getOutputForRow(array $row)
    {
        $additionalFields = $this->getAdditionalFieldsFromRow($row);
        unset($row[$this->getFormSubmissionColumnNumber()]);
        $additionalColumns = array_fill_keys(array_keys(array_flip($this->getAdditionalColumns())), '');
        foreach($additionalFields as $key => $value) {
            $additionalColumns[$key] = $value;
        }
        return array_merge(array_values($row), array_values($additionalColumns));
    }

    private function getAdditionalColumns(){
        $columns = array();
        $input = $this->getInputCsvContents();
        foreach($input as $row){
            if(!is_array($row)) continue;
            $additionalFields = $this->getAdditionalFieldsFromRow($row);
            foreach($additionalFields as $key => $val){
                if(!$val || $val == $key) continue;
                $columns[$key] = $key;
            }
        }
        return $columns;
    }

    private function getAdditionalFieldsFromRow(array $row)
    {
        return json_decode($row[$this->getFormSubmissionColumnNumber()], true);
    }

    private function getInputCsvContents()
    {
        return array_slice($this->getInputCsvArray(), 1);
    }

    private function getFormSubmissionColumnNumber()
    {
        return array_search('Form Submission', $this->getInputCsvHeaders());
    }

    private function getInputCsvHeaders()
    {
        return $this->getInputCsvArray()[0];
    }

    private function getInputCsvArray()
    {
        if(!$this->hasInputCsvArray()) {
            $this->initInputCsvArray();
        }
        return $this->inputCsvArray;
    }

    private function hasInputCsvArray()
    {
        return isset($this->inputCsvArray);
    }

    private function initInputCsvArray()
    {
        $this->inputCsvArray = $this->parseInputCsv();
        return $this;
    }

    private function parseInputCsv()
    {
        $inputCsv = [];
        $fh = fopen($this->getCsvFileName(), 'r');
        while(! feof($fh))
        {
            $inputCsv[] = fgetcsv($fh);
        }
        fclose($fh);
        return $inputCsv;
    }

    private function getCsvFileName()
    {
        return $this->varDir . '/' . $this->filename;
    }
}