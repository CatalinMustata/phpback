<?php
/*********************************************************************
PHPBack
Ivan Diaz <ivan@phpback.org>
Copyright (c) 2014 PHPBack
http://www.phpback.org
Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.
**********************************************************************/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {
	public function __construct() {
		parent::__construct();
        session_start();

		$this->load->helper('url');
		$this->load->model('get');
		$this->load->model('post');

		$this->lang->load('default', $this->get->getSetting('language'));

        $this->verifyBanning();
	}
	public function index($error = "NULL", $ban = 0) {

        if(@isset($_SESSION['phpback_userid']) || @isset($_COOKIE['phpback_sessionid'])) {
            header('Location: '. base_url() .'home');
            return;
        }

        $data = $this->getDefaultData();
        $data['error'] = $error;
        $data['ban'] = $ban;

        $this->load->view('_templates/header', $data);
				$this->load->view('home/login', $data);
				$this->load->view('_templates/menu', $data);
				$this->load->view('_templates/footer', $data);
	}

    private function getDefaultData() {
        return array(
            'title' => $this->get->getSetting('title'),
            'categories' => $this->get->getCategories(),
            'lang' => $this->lang->language,
        );
    }

    private function verifyBanning() {
        if (@isset($_SESSION['phpback_userid']) && ($ban = $this->get->getBanValue($_SESSION['phpback_userid'])) != 0) {
            date_default_timezone_set('America/Los_Angeles');

            //Remove ban if ban expired
            if ($ban <= date("Ymd") && $ban != -1) {
                $this->post->unban($_SESSION['phpback_userid']);
                return;
            }

            session_destroy();
            $this->destroyUserCookie();

            if ($ban != -1) {
                for($i = 0; $i < 366; $i++){
                    if(date('Ymd', strtotime("+$i days")) == $ban) break;
                }
            }
            else $i = -1;

            header('Location: '. base_url() .'home/login/banned/' . $i);
            exit;
        }
    }

    private function destroyUserCookie() {
        if(@isset($_COOKIE['phpback_sessionid'])){
            $this->get->verifyToken($_COOKIE['phpback_sessionid']);
            setcookie('phpback_sessionid', '', time()-3600, '/');
        }
    }
}
