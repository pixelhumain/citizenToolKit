<?php 
class Pdf {

	// require __DIR__.'/vendor/autoload.php';

	// use Spipu\Html2Pdf\Html2Pdf;
	
	public static function initPdf() {
  //   	Yii::import("tcpdf.tcpdf", true);
		// Yii::import("html2pdf.MyPdf", true);	
		// Yii::import("html2pdf.Parsing.TextParser", true);	
		// Yii::import("html2pdf.Parsing.TagParser", true);	
		// Yii::import("html2pdf.Parsing.HtmlLexer", true);	
		// Yii::import("html2pdf.Parsing.Html", true);	
		// Yii::import("html2pdf.Parsing.Css", true);
		// Yii::import("html2pdf.Parsing.Token", true);	
		// Yii::import("html2pdf.Parsing.Node", true);
		
		// Yii::import("html2pdf.Exception.Html2PdfException", true);
		// Yii::import("html2pdf.Exception.HtmlParsingException", true);
		// Yii::import("html2pdf.Exception.ExceptionFormatter", true);
		// Yii::import("html2pdf.Exception.LongSentenceException", true);
		// Yii::import("html2pdf.Exception.TableException", true);
		// Yii::import("html2pdf.Exception.ImageException", true);
		// Yii::import("html2pdf.Extension.ExtensionInterface", true);
		// Yii::import("html2pdf.Tag.TagInterface", true);
		// Yii::import("html2pdf.Tag.AbstractTag", true);
		// Yii::import("html2pdf.Tag.AbstractHtmlTag", true);
		// Yii::import("html2pdf.Tag.AbstractSvgTag", true);
		// Yii::import("html2pdf.Tag.Html.Big", true);
		// Yii::import("html2pdf.Tag.Html.Bookmark", true);
		// Yii::import("html2pdf.Tag.Html.I", true);
		// Yii::import("html2pdf.Tag.Html.B", true);
		// Yii::import("html2pdf.Tag.Html.S", true);
		// Yii::import("html2pdf.Tag.Html.U", true);
		// Yii::import("html2pdf.Tag.Html.Em", true);
		// Yii::import("html2pdf.Tag.Html.Span", true);
		// Yii::import("html2pdf.Tag.Html.Ins", true);
		// Yii::import("html2pdf.Tag.Html.Small", true);
		// Yii::import("html2pdf.Tag.Html.Font", true);
		// Yii::import("html2pdf.Tag.Html.Label", true);
		// Yii::import("html2pdf.Tag.Html.Samp", true);
		// Yii::import("html2pdf.Tag.Html.Strong", true);
		// Yii::import("html2pdf.Tag.Html.Sub", true);
		// Yii::import("html2pdf.Tag.Html.Sup", true);
		// Yii::import("html2pdf.Tag.Html.Cite", true);
		// Yii::import("html2pdf.Tag.Html.Del", true);
		// Yii::import("html2pdf.Tag.Html.Address", true);
		// Yii::import("html2pdf.Locale", true);
		// Yii::import("html2pdf.Html2Pdf", true);
		// Yii::import("html2pdf.CssConverter", true);

		Yii::import("tcpdf.tcpdf", true);
		Yii::import("html2pdf.MyPdf", true);	
		Yii::import("html2pdf.Locale", true);
		Yii::import("html2pdf.Html2Pdf", true);

    }

    public static function createPdf() {
     	self::initPdf();
  //   	ob_end_clean();
		// $html2pdf = new \Spipu\Html2Pdf\Html2Pdf();
		// $html2pdf->writeHTML($tpl);
		// return $html2pdf->output();

    	//Yii::import('application.vendscsqcors.spipu.html2pdf.*');


		$html2pdf = new Html2Pdf();
		$html2pdf->writeHTML('<h1>HelloWorld</h1>This is my first test');
		$html2pdf->output();
	}
}
?> 