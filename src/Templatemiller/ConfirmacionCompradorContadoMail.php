<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class ConfirmacionCompradorContadoMail
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
                    <td style="padding: 20px 45px;font-size:30px;font-family:\'Cocogoose\', sans-serif;color:'.$this->color.';line-height:30px; width: 510px; text-transform: uppercase;">
                        Hola,<br>'. $data['comprador'] .'.<br>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                        '. $data['nombrePasajero'] .' te ha asociado la/s siguiente/s rifa/s:<br>
                    </td>    
                </tr>
                <tr>
                    <td>
                        <table width="600" align="center" rules="none">';
        //$counter = 0;
        $precio_total = 0;
        foreach ($data['rifas'] as $rif) {   
            //$counter = $counter + count($rif);
            $mail .= '                 
            <tr style="">
                <td style="border-top: 0.3px solid #c3c3c3; padding: 20px 45px;font-size:30px;font-family:\'Cocogoose\', sans-serif;color:'.$this->color.';line-height:20px; width: 210px;">
                    NRO. '. $rif[0]->getNumero() .'
                </td>
                <td style="border-top: 0.3px solid #c3c3c3;padding: 20px 45px;font-size:15px;font-family:\'Lato\';color:#888888;line-height:19px; width: 210px; text-align: right;">';
                if ($data['talonType'] == 'multi') {
                    //$mail .= count($rif) .' sorteos x '. $data['moneda'] .' '. $rif[0]->getPrecio();
                    for ($i = 0; $i < count($rif); $i++) {
                        $mail .= '<p>' . ' Sorteo ' . $rif[$i]->getSorteoNumero() . ' - ' . $data['moneda'] . ' ' . $rif[$i]->getPrecio() . '</p>';
                        $precio_total += $rif[$i]->getPrecio();
                    }
                }    
                $mail .= '</td>
            </tr>';
        }
        //$counter = $counter * $data['precio'];
        $mail .= '</table>
                    </td>
                </tr>
                <tr style="">
                    <td style="padding: 20px 45px 35px; font-size:22px;font-family:\'Cocogoose\', sans-serif;color:#000;line-height:20px; width: 510px; text-align: right;">
                        TOTAL: '. $data['moneda'] .' '. $precio_total . '
                    </td>
                </tr>
                <tr>
                    <td style="padding: 45px 60px;font-size:30px;font-family:\'Cocogoose\', sans-serif;color:'.$this->color.';line-height:20px; width: 510px; text-align: center;">
                        GRACIAS POR AYUDAR!<br>
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
