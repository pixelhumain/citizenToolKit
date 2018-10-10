<?php 
class Pdf extends CommunecterController{

	// require __DIR__.'/vendor/autoload.php';

	// use Spipu\Html2Pdf\Html2Pdf;
	
	public static function initPdf() {
    	Yii::import("tcpdf.tcpdf", true);
    	

		Yii::import("html2pdf.MyPdf", true);	
		Yii::import("html2pdf.Parsing.TextParser", true);	
		Yii::import("html2pdf.Parsing.TagParser", true);	
		Yii::import("html2pdf.Parsing.HtmlLexer", true);	
		Yii::import("html2pdf.Parsing.Html", true);	
		Yii::import("html2pdf.Parsing.Css", true);
		Yii::import("html2pdf.Parsing.Token", true);	
		Yii::import("html2pdf.Parsing.Node", true);
		
		Yii::import("html2pdf.Exception.Html2PdfException", true);
		Yii::import("html2pdf.Exception.HtmlParsingException", true);
		Yii::import("html2pdf.Exception.ExceptionFormatter", true);
		Yii::import("html2pdf.Exception.LongSentenceException", true);
		Yii::import("html2pdf.Exception.TableException", true);
		Yii::import("html2pdf.Exception.ImageException", true);
		Yii::import("html2pdf.Extension.ExtensionInterface", true);
		Yii::import("html2pdf.Tag.TagInterface", true);
		Yii::import("html2pdf.Tag.AbstractTag", true);
		Yii::import("html2pdf.Tag.AbstractHtmlTag", true);
		Yii::import("html2pdf.Tag.AbstractSvgTag", true);
		Yii::import("html2pdf.Tag.Html.Big", true);
		Yii::import("html2pdf.Tag.Html.Bookmark", true);
		Yii::import("html2pdf.Tag.Html.I", true);
		Yii::import("html2pdf.Tag.Html.B", true);
		Yii::import("html2pdf.Tag.Html.S", true);
		Yii::import("html2pdf.Tag.Html.U", true);
		Yii::import("html2pdf.Tag.Html.Em", true);
		Yii::import("html2pdf.Tag.Html.Span", true);
		Yii::import("html2pdf.Tag.Html.Ins", true);
		Yii::import("html2pdf.Tag.Html.Small", true);
		Yii::import("html2pdf.Tag.Html.Font", true);
		Yii::import("html2pdf.Tag.Html.Label", true);
		Yii::import("html2pdf.Tag.Html.Samp", true);
		Yii::import("html2pdf.Tag.Html.Strong", true);
		Yii::import("html2pdf.Tag.Html.Sub", true);
		Yii::import("html2pdf.Tag.Html.Sup", true);
		Yii::import("html2pdf.Tag.Html.Cite", true);
		Yii::import("html2pdf.Tag.Html.Del", true);
		Yii::import("html2pdf.Tag.Html.Address", true);
		Yii::import("html2pdf.Locale", true);
		Yii::import("html2pdf.Html2Pdf", true);
		Yii::import("html2pdf.CssConverter", true);

		// Yii::import("tcpdf.tcpdf", true);
		// Yii::import("html2pdf.MyPdf", true);	
		// Yii::import("html2pdf.Locale", true);
		// Yii::import("html2pdf.Html2Pdf", true);

    }

    public static function createPdf($params) {
     	Yii::import("tcpdf.tcpdf", true);
     	

     	if (class_exists('tcpdf', false) && !empty($params)) {

				$server = ((isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];

				$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				// set document information
				$pdf->SetCreator(PDF_CREATOR);
				if(!empty($params["author"]))
					$pdf->SetAuthor($params["author"]);
				if(!empty($params["title"]))
					$pdf->SetTitle($params["title"]);
				if(!empty($params["subject"]))
					$pdf->SetSubject('TCPDF Tutorial');
				if(!empty($params["keywords"]))
					$pdf->SetKeywords($params["keywords"]);

				// set default header data
				// if(!empty($params["header"])){
				// 	$url = $server.Yii::app()->getModule("survey")->assetsUrl.$params["custom"]["logo"]; 
				// 	$img = ( (@$params["custom"] && @$params["custom"]["logo"]) ? $server.Yii::app()->getModule("survey")->assetsUrl.$params["custom"]["logo"] : PDF_HEADER_LOGO ) ;
				


				// 	// $pdf->SetHeaderData($img, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
				// }
				if(!empty($params["footer"]))
					$pdf->setFooterData(array(0,64,0), array(0,64,128));

				// set header and footer fonts
				$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
				$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

				// set default monospaced font
				$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

				// set margins
				$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
				$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
				$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

				// set auto page breaks
				$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

				// set image scale factor
				$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

				// set some language-dependent strings (optional)
				if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
				    require_once(dirname(__FILE__).'/lang/eng.php');
				    $pdf->setLanguageArray($l);
				}

				// ---------------------------------------------------------

				// set default font subsetting mode
				$pdf->setFontSubsetting(true);

				// Set font
				// dejavusans is a UTF-8 Unicode font, if you only need to
				// print standard ASCII chars, you can use core fonts like
				// helvetica or times to reduce file size.
				$pdf->SetFont('dejavusans', '', 14, '', true);

				// Add a page
				// This method has several options, check the source code documentation for more information.
				$pdf->AddPage();

				// set text shadow effect
				$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

				// Set some content to print
				
				$html = self::$params["tplData"]($params);


				// Print text using writeHTMLCell()
				//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
				$pdf->writeHTML($html, true, false, false, false, '');

				// ---------------------------------------------------------
				ob_end_clean();
				// Close and output PDF document
				// This method has several options, check the source code documentation for more information.
				if(!empty($params["title"]))
					$pdf->Output($params["title"].'.pdf', 'I');
				else
					$pdf->Output('$params["title"].pdf', 'I');
				//============================================================+
				// END OF FILE
				//============================================================+
			
		}

	}

	public static function cteDossier($params) {
		//Rest::json($data); exit;

		$html = "<h1>Dossier</h1>";
		$html = $params["html"];
		//$html = $ctrl->render( "dossier" ,$params);

		return $html;

	}
}
?> 