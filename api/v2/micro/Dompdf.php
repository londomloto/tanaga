<?php
namespace Micro;

class Dompdf {
    public function pdf_create($html, $xfilename, $stream=true, $papersize = 'A4', $orientation = 'portrait'){
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);

        $dompdf->setPaper($papersize, $orientation);
        $dompdf->render();

        if ($stream)
        {
            $options['Attachment'] = 1;
            $options['Accept-Ranges'] = 0;
            $options['compress'] = 1;
            $dompdf->stream($xfilename.".pdf", $options);
        }
        else{
            $output = $dompdf->output();
            file_put_contents("$xfilename.pdf", $output);
        }
    }

    public function pdf_create_special($pass, $html, $xfilename, $stream=true, $papersize = 'A4', $orientation = 'portrait'){
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper($papersize, $orientation);
        $dompdf->render();
        $dompdf->get_canvas()->get_cpdf()->setEncryption('what is'.$pass, $pass, array('copy','print')); // 'copy','print'
 
        if ($stream)
        {
            $options['Attachment'] = 1;
            $options['Accept-Ranges'] = 0;
            $options['compress'] = 1;
            $dompdf->stream($xfilename.".pdf", $options);
        }
        else{
            $output = $dompdf->output();
            file_put_contents("$xfilename.pdf", $output);
        }
    }


}