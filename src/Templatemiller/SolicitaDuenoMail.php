<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class SolicitaDuenoMail
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
                    font-family: \'Cocogoose\';
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
                        Hola,<br>' . $data['duenoName'] . '.<br>
                    </td>
                </tr>
                <tr>';

        if (count($data['rifas']) > 1) {
            $mail .= '<td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                ' . $data['solicitanteName'] . ' solicitó tus rifas nro.&nbsp;';
            $counter = 0;
            foreach ($data['rifas'] as $rif) {
                if ($counter > 0) {
                    $mail .= '<span>, </span>';
                }
                $mail .= $rif['Numero'];
                $counter++;
            }
            $mail .= ' de la bolsa.
                </td>';
        }

        if (count($data['rifas']) == 1) {
            $mail .= '<td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                ' . $data['solicitanteName'] . ' solicitó tu rifa nro.&nbsp;';
            foreach ($data['rifas'] as $rif) {
                $mail .= $rif['Numero'];
            }
            $mail .= ' de la bolsa.
                            </td> ';
        }
        $mail .= '</tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">  
                        Dichas rifas ya no están mas en la bolsa y están pendientes de que se las entregues a&nbsp;' . $data['solicitanteFirstname'] . '.
                        El mail de&nbsp;' . $data['solicitanteFirstname'] . ' es:&nbsp;' . $data['solicitanteEmail'] . ' y su teléfono es&nbsp;' . $data['solicitantePhone'] . '.<br>
                        Ponte en contacto con&nbsp;' . $data['solicitanteFirstname'] . ' para entregarle las rifas.
                        Una vez que&nbsp;' . $data['solicitanteFirstname'] . ' confirme que le entregaste las rifas, las mismas aparecerán en su sistema y desaparecerán del tuyo. 
                        Hasta que&nbsp;' . $data['solicitanteFirstname'] . ' no confirme que tiene las rifas, la responsabilidad de la venta de las mismas es tuya. Es por esto que es importante que entregues las rifas a&nbsp;' . $data['solicitanteFirstname'] . ' cuanto antes.
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
