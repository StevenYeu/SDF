<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class RRIDReportFreeze extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_report_freeze";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "rrid_report_id"    => self::fieldDef("rrid_report_id", "i", true),
            "timestamp"         => self::fieldDef("timestamp", "i", true),
            "data"              => self::fieldDef("data", "s", true),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }

    private $_report;
    private $_version;

    public static function createNewObj(RRIDReport $rrid_report) {
        if(!$rrid_report) return NULL;
        $timestamp = time();
        $data = self::genReportData($rrid_report);

        $obj = self::insertObj(Array(
            "id" => NULL,
            "rrid_report_id" => $rrid_report->id,
            "timestamp" => $timestamp,
            "data" => $data,
        ));
        if(!is_null($obj)) {
            $obj->createPDF();
        }
        return $obj;
    }

    /* cannot delete a report freeze */
    public static function deleteObj($obj) { }

    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "rrid_report_id" => $rrid_report->id,
            "timestamp" => $timestamp,
            "data" => $data,
        );
    }

    public static function genReportData(RRIDReport $rrid_report) {
        return \helper\htmlElement("rrid-report", Array("report" => $rrid_report));
    }

    private function createPDF() {
        if(!$this->id || !$this->data) return;
        $html_report = $this->data;
        $css = '<style>* { font-family: DejaVu Sans, sans-serif; font-size: 12px; }</style>';
        $html = $css . $html_report;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        $pdf_data = $dompdf->output();
        file_put_contents($this->pdfFilename(), $pdf_data);
    }

    public function report() {
        if(is_null($this->_report)) {
            $this->_report = RRIDReport::loadBy(Array("id"), Array($this->rrid_report_id));
        }
        return $this->_report;
    }

    public function version() {
        if(is_null($this->_version)) {
            $cxn = new Connection();
            $cxn->connect();
            $ids = $cxn->select(self::$_table_name, Array("id"), "i", Array($this->rrid_report_id), "where rrid_report_id=? order by id asc");
            $cxn->close();
            $counter = 1;
            foreach($ids as $id) {
                if($id["id"] == $this->id) {
                    $this->_version = $counter;
                    break;
                }
                $counter += 1;
            }
        }
        return $this->_version;
    }

    public function pdfFilename() {
        return $GLOBALS["DOCUMENT_ROOT"] . "/upload-private/rrid-reports/" . $this->id . ".pdf";
    }

    public function pdfExists() {
        return file_exists($this->pdfFilename());
    }

    public function accessible($user) {
        if($this->id == 11) return true;
        if($user->id !== $this->report()->uid) return false;
        return true;
    }
}
RRIDReportFreeze::init();

?>
