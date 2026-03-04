<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class SolicitaSolicitanteMail
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
                    <td style="padding: 20px 45px;font-size:33px;font-family:\'Cocogoose\', sans-serif;color:' . $this->color . ';line-height:30px; width: 510px; text-transform: uppercase; font-weight: bold;">
                        Hola,<br>' . $data['solicitanteName'] . '.<br>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                        Solicistaste las siguientes rifas:
                    </td>    
                </tr>';
        foreach ($data['info'] as $item) {
            $mail .= '<tr>';
            if (count($item['rifas']) > 1) {
                $mail .= '<td style="padding: 10px 45px 8px;font-size:16px;font-family:"Lato";color:#888888;line-height:20px; width: 510px;font-weight: bold;">    
                            De&nbsp;' . $item['duenoName'] . ' los n&uacute;meros&nbsp;';
                $counter = 0;
                foreach ($item['rifas'] as $rifa) {
                    if ($counter > 0) {
                        $mail .= '<span>, </span>';
                    }
                    $mail .= $rifa['Numero'];
                    $counter++;
                }
                $mail .= '.
                </td>';           
            }

            if (count($item['rifas']) == 1) {
                $mail .= '<td style="padding: 10px 45px 8px;font-size:16px;font-family:"Lato";color:#888888;line-height:20px; width: 510px; font-weight: bold;">    
                            De&nbsp;' . $item['duenoName'] . ' el n&uacute;mero&nbsp;';

                foreach ($item['rifas'] as $rifa) {
                    $mail .= $rifa['Numero'];
                }
                $mail .= '</td>';
            }
            $mail .= '</tr>
                    <tr>
                        <td style="padding: 0 45px 30px;font-size:16px;font-family:"Lato";color:#888888;line-height:20px; width: 510px; border-bottom: 1px solid #ccc; margin-bottom: 10px;">    
                            Su mail es&nbsp;' . $item['duenoEmail'] . ' y su teléfono es&nbsp;' . $item['duenoPhone'] . '.
                        </td>    
                    </tr>';
        }
        $mail .= '
                <tr>
                    <td style="padding: 20px 45px 30px;font-size:16px;font-family:"Lato";color:#888888;line-height:20px; width: 510px;">    
                        Ponte en contacto para que te entreguen las rifas.
                        Una vez que tengas las rifas, ve a la sección “Pendientes de recepción”, confirma que las recibiste y las rifas aparecerán en tu sistema. 
                        <br>
                        A partir de ese momento la responsabilidad por la venta de las rifas será tuya.
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
