<?php

namespace App\Templatemiller;

/**
 * Class UserBienvenido
 * @package App\Templatemiller
 */
class RifaEntregadaMail
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
                    <td style="padding: 20px 45px;font-size:30px;font-family:\'Cocogoose\', sans-serif;color:' . $this->color . ';line-height:30px; width: 510px; text-transform: uppercase;">
                        Hola,<br>'. $data['exDueno'] .'.<br>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 45px 30px;font-size:16px;font-family:\'Lato\';color:#888888;line-height:20px; width: 510px;">    
                        '. $data['actualDueno'] .' confirmó que recibió tus rifas, por lo que las mismas ya no están en tu sistema y ahora es responsabilidad de&nbsp;'. $data['actualDuenoName'] . ' su venta.<br><br>
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
    }
}
