<?php

namespace App\Templatemiller;

use App\Templatemiller\ForgotPasswordMail;
use App\Templatemiller\ForgotPasswordMailCode;
use App\Templatemiller\SolicitaDuenoMail;
use App\Templatemiller\SolicitaSolicitanteMail;
use App\Templatemiller\TransferenciaDirectaMail;
use App\Templatemiller\LinkCompradorMail;
use App\Templatemiller\ConfirmacionCompradorContadoMail;
use App\Templatemiller\BorrarRegistroTalonCompradorMail;
use App\Templatemiller\ConfirmacionCompradorMail;

/**
 * Class Templates
 * @package App\Templatemiller
 */

//const myArrayConst = file("mytextfile.txt", FILE_IGNORE_NEW_LINES);

//define("myArrayConst", file("mytextfile.txt", FILE_IGNORE_NEW_LINES));

class Templates
{
    //protected static string $dbname = $_ENV['URL_RAIZ_SERVER'];

    public $inscripcionViajeEmisorMail;
    public $inscripcionViajeReceptorMail;
    public $forgotPasswordMail;
    public $forgotPasswordMailCode;
    public $solicitaDuenoMail;
    public $solicitaSolicitanteMail;
    public $rifaEntregadaMail;
    public $transferenciaDirectaMail;
    public $linkCompradorMail;
    public $confirmacionCompradorContadoMail;
    public $borrarRegistroTalonCompradorMail;
    public $confirmacionCompradorMail;
    public $confirmacionVendedorMail;

    //dTyT
    private $mailCabezal = '/img/mailing/pago-rifas/mail_Cabeza_new.png';
    private $mailPie = '/img/mailing/pago-rifas/mail_Pie_new.png';

    private $mailBoton = '/img/mailing/pago-rifas/mail_Boton.png';
    private $mailBotonDatosPersona = '/htdocs/img/mailing/pago-rifas/btn_perfil.png';
    private $mailResetear = '/img/mailing/pago-rifas/resetear-contrasena.png';
    private $mainColor = '#0F6946';

    public function __construct()
    {
        $URL_RAIZ = $_ENV['URL_RAIZ_SERVER'];
        $URL_LINK = $_ENV['URL_LINK_PAGO'];
        $URL_ADMIN = $_ENV['URL_ENV_FRONT_ADMIN'];

        $this->inscripcionViajeEmisorMail = new InscripcionViajeEmisorMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor, $URL_ADMIN, $URL_RAIZ . $this->mailBotonDatosPersona);
        $this->inscripcionViajeReceptorMail = new InscripcionViajeReceptorMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->forgotPasswordMail = new ForgotPasswordMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor, $URL_RAIZ . $this->mailResetear, $URL_LINK);
        $this->forgotPasswordMailCode = new ForgotPasswordMailCode($this->mailCabezal, $this->mailPie, $this->mainColor, $this->mailResetear);
        $this->solicitaDuenoMail = new SolicitaDuenoMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->solicitaSolicitanteMail = new SolicitaSolicitanteMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->rifaEntregadaMail = new RifaEntregadaMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->transferenciaDirectaMail = new TransferenciaDirectaMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->linkCompradorMail = new LinkCompradorMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor, $URL_RAIZ . $this->mailBoton, $URL_LINK);
        $this->confirmacionCompradorContadoMail = new ConfirmacionCompradorContadoMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->borrarRegistroTalonCompradorMail = new BorrarRegistroTalonCompradorMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->confirmacionVendedorMail = new ConfirmacionVendedorMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
        $this->confirmacionCompradorMail = new ConfirmacionCompradorMail($URL_RAIZ . $this->mailCabezal, $URL_RAIZ . $this->mailPie, $this->mainColor);
    }
}
