<?php
namespace App\Controllers;
use Core\Controller;
use App\Models\Translator;
class AdminPanelController extends Controller
{
    public function dashboard($req,$res,$args)
    {
        $translatorEmploymentReqs=Translator::get_employment_requests();
        return $this->view->render($res,"admin/admin/dashboard.twig");
    }
}
