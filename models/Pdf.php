<?php 
class Pdf {
	
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
		Yii::import("html2pdf.Extension.CoreExtension", true);
		Yii::import("html2pdf.Tag.TagInterface", true);
		Yii::import("html2pdf.Tag.AbstractTag", true);
		Yii::import("html2pdf.Tag.AbstractDefaultTag", true);
		Yii::import("html2pdf.Tag.Big", true);
		Yii::import("html2pdf.Tag.Bookmark", true);
		Yii::import("html2pdf.Tag.I", true);
		Yii::import("html2pdf.Tag.B", true);
		Yii::import("html2pdf.Tag.S", true);
		Yii::import("html2pdf.Tag.U", true);
		Yii::import("html2pdf.Tag.Em", true);
		Yii::import("html2pdf.Tag.Span", true);
		Yii::import("html2pdf.Tag.Ins", true);
		Yii::import("html2pdf.Tag.Small", true);
		Yii::import("html2pdf.Tag.Font", true);
		Yii::import("html2pdf.Tag.Label", true);
		Yii::import("html2pdf.Tag.Samp", true);
		Yii::import("html2pdf.Tag.Strong", true);
		Yii::import("html2pdf.Tag.Sub", true);
		Yii::import("html2pdf.Tag.Sup", true);
		Yii::import("html2pdf.Tag.Cite", true);
		Yii::import("html2pdf.Tag.Del", true);
		Yii::import("html2pdf.Tag.Address", true);
		Yii::import("html2pdf.Locale", true);
		Yii::import("html2pdf.Html2Pdf", true);
		Yii::import("html2pdf.CssConverter", true);

    }

    public static function createPdf($tpl) {
    	self::initPdf();
    	ob_end_clean();
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf();
		$html2pdf->writeHTML($tpl);
		return $html2pdf->output();
	}
}
?> 