<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class BorrarRegistroTalonCompradorMail
{
    private $cabezal;
    private $pie;
    private $color;

    public function __construct( $cabezal, $pie, $color ) {
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
                        <img src="'.$this->cabezal.'">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 45px;font-size:30px;font-family:\'Cocogoose\', sans-serif;font-weight:900;color:'.$this->color.';line-height:30px; width: 510px; text-transform: uppercase;">
                        Hola,<br>'. $data['comprador'] .'.<br>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">';
        if ($data['talonType'] == 'multi') {
            $mail .= $data['nombrePasajero'] .' te ha desasociado del siguiente talón:<br>';
        } else {
            $mail .= $data['nombrePasajero'] .' te ha desasociado de la siguiente rifa:<br>';
        }
                    $mail .= '</td>    
                </tr>
                <tr>
                    <td>
                        <table width="600" align="center" rules="none">
                            <tr style="">
                                <td style="border-top: 0.3px solid #c3c3c3; padding: 20px 45px;font-size:30px;font-family:\'Cocogoose\', sans-serif;color:'.$this->color.';line-height:20px; width: 210px;">
                                    NRO. '. $data['rifas']->getNumero() .'
                                </td>
                                <td style="border-top: 0.3px solid #c3c3c3;padding: 20px 45px;font-size:15px;font-family:\'Lato\';color:#888888;line-height:19px; width: 210px; text-align: right;">';
        if ($data['talonType'] == 'multi') {
            $mail .= '1 sorteo x '. $data['moneda'] .' ' . $data['precio'];
        } else {
            $mail .= $data['moneda'] . ' ' . $data['precio'];
        }
                                $mail .= '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 45px 60px;font-size:20px;font-family:\'Cocogoose\', sans-serif;color:'.$this->color.';line-height:25px; width: 510px; text-align: center;">
                        SI ESTO ES INCORRECTO PONTE EN CONTACTO CON LA PERSONA QUE TE VENDIÓ LA RIFA.<br>
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
