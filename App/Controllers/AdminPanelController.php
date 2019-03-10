<?php
namespace App\Controllers;
use Core\Controller;

class AdminPanelController extends Controller
{
    public function dashboard($req,$res,$args)
    {
        return $this->view->render($res,"admin/admin/dashboard.twig");
    }
}
