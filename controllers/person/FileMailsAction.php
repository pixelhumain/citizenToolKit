<?php
class FileMailsAction extends CAction
{
	public function run()
    {
        
        $csv = new SplFileObject($_FILES['fileMail']['tmp_name'], 'r');
        $csv->setFlags(SplFileObject::READ_CSV);
        $csv->setCsvControl(";", '"', '"');

        $arrayMails = array();
        $i = 0 ;
        foreach ($csv as $key => $value) 
        {
            if($i == 0)
            {
                $arrayMails = $value;
                break ;
            }
        }

        Rest::json(array('arrayMails' => $arrayMails));
       
    }
}
?>