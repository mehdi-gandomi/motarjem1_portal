<?php
namespace App\Controllers;

use App\Models\Translator;
use Core\Config;
use Core\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Slim\Http\UploadedFile;

class TranslatorPanelController extends Controller
{
    public function get_dashboard($req,$res,$args)
    {
        return $this->view->render($res,"admin/translator/dashboard.twig");
    }
}