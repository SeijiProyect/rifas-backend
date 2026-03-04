<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class TransferenciaDirectaMail
{
    private $cabezal;
    private $pie;
    private $color;

    public function __construct($cabezal, $pie, $color)
    {
        $this->cabezal = $cabezal;
        $this->pie = $pie;
        $this->color = $color;
    }

    public function template($data)
    {
        $mail = '
            <style>
                @font-face {
                    font-family: "Cocogoose";
                    font-style: normal;
                    font-weight: 400;
                    src: local("Cocogoose"), url(https://fonts.googleapis.com/css2?family=Work+Sans:wght@900&display=swap) format("otf");
                }
    
                @font-face {
                    font-family: "Lato";
                    font-style: normal;
                    font-weight: 400;
                    src: local("Lato"), url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
                }
            </style>


            <table width="600" align="center">   
                <tr>
                    <td>
                        <img src="' . $this->cabezal . '">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 45px;font-size:30px;font-family:\'Cocogoose\', sans-serif;color:' . $this->color . ';line-height:30px; width: 510px; text-transform: uppercase;">
                        Hola,<br>'. $data['transfName'] .'.<br>
                    </td>
                </tr>
                
                <tr>';

                $counterAux = 0;
                foreach ($data['rifas'] as $rif) {
                    $counterAux++;
                }
                    
                $mail .= '<td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">  
                        '.$data['duenoName'] .' desea transferirte&nbsp;';
                        
                if ( $counterAux > 1 ) {
                    $mail .= 'las rifas';
                }
                if ( $counterAux == 1 ) {
                    $mail .= 'la rifa';
                }
                $mail .= ' n&uacute;mero&nbsp;';
                
                $counter = 0;
                foreach ($data['rifas'] as $rif) {
                    if ($counter > 0 ) {
                        $mail .= '<span>, </span>';
                    }
                    $mail .= $rif[0]['Numero'];
                    $counter++; 
                }
                $mail .= ' de forma directa.<br><br>
                        Si deseas aceptar esta transferencia, ve a la sección “Pendietes de recepción” en el sistema y confirma que las recibiste. De esta forma las rifas aparecerán en tu sistema y desaparecerán del sistema de&nbsp;'.$data['duenoName']. '.
                        <br><br>
                        Saludos!
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $this->pie . '">
                    </td>
                </tr>    
            </table>
        ';

        return $mail;
    }
}
