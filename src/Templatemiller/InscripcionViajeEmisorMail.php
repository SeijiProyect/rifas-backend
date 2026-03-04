<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class InscripcionViajeEmisorMail
{
    private $cabezal;
    private $pie;
    private $color;
    private $boton;
    private $url_admin;

    public function __construct($cabezal, $pie, $color, $url_admin, $boton)
    {
        $this->cabezal = $cabezal;
        $this->pie = $pie;
        $this->color = $color;
        $this->boton = $boton;
        $this->url_admin = $url_admin;
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
                   
                </tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                        Se acaba de completar una nueva inscripción al viaje <strong>' . $data['viaje'] .' '. $data['itinerario'] . '</strong>
                    </td>    
                </tr>
                <tr>
                <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                    Se inscribio <strong>' . $data['nombrePersona'] . '</strong>
                </td>       
                </tr>
                <tr>
                <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                    Este es su correo electrónico ' . $data['emailPersona'] . '
                </td>       
                </tr>
                <tr>
                <td style="padding: 0 100px; width: 400px;">
                    <a href="' . $this->url_admin . '/dashboard/personas/persona-tab/' . $data['idPersona'] . '">
                        <img width="400px" src="' . $this->boton . '">
                    </a>   
                </td>
                </tr>
                <tr>
                   
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
