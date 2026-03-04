<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class ForgotPasswordMail
{
    private $cabezal;
    private $pie;
    private $color;
    private $boton;
    private $link;

    public function __construct($cabezal, $pie, $color, $boton, $link)
    {
        $this->cabezal = $cabezal;
        $this->pie = $pie;
        $this->color = $color;
        $this->boton = $boton;
        $this->link = $link;
    }

    public function template($data)
    {
        return '
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
                    <td style="padding: 20px 45px;font-size:30px;font-family:\'Cocogoose\';color:' . $this->color . ';line-height:30px; width: 510px; text-transform: uppercase;">
                        Hola,<br>' . $data['nombrePasajero'] . '.<br>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                        Recientemente solicitaste resetear tu contraseña, usa el botón de abajo para restablecerlo. <strong>Este restablecimiento de contraseña solo es válido durante las próximas 24 horas.</strong>
                    </td>    
                </tr>
                <tr>
                    <td style="padding: 0 100px 30px; width: 400px;">
                        <a href="'.$this->link . '/reset/' . $data['token'] . '">
                            <img width="400px" src="' . $this->boton . '">
                        </a>   
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                        Si no solicitaste un restablecimiento de contraseña, ignorá este correo electrónico o ponete en contacto con el servicio de asistencia.
                    </td>    
                </tr>
                <tr>
                    <td style="padding: 45px 60px;font-size:30px;font-family:\'Cocogoose\';color:' . $this->color . ';line-height:33px; width: 510px; text-align: center;">
                        SALUDOS!<br>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $this->pie . '">
                    </td>
                </tr>    
            </table>
        ';
    }
}