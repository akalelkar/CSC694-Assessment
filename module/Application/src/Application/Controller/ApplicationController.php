<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Application\Form\ApplicationForm;
use Application\Authentication\AuthUser;

class ApplicationController extends AbstractActionController
{
    //if the user if not logged in and authenticated they are sent back to the the login screen
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $validUser = new AuthUser();
        if (!$validUser->Validate()) {
            return $this->redirect()->toRoute('home');
        }
        return parent::onDispatch($e);
    }
    
    //the indexAction just renders the main screen giving the options of modules to choose from
    public function indexAction()
    {
        $form = new ApplicationForm($this->url()->fromRoute('choose'));
        return array('form' => $form);
    }

    //this method determines the choice the user made and directs them to the appropriate module
    public function chooseAction()
    {
        $request = $this->getRequest();
        $choice = strtolower($request->getPost()['module']);

        return $this->redirect()->toRoute($choice);
    }

}
