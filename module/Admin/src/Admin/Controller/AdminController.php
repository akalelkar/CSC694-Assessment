<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Authentication\AuthUser;
use Zend\session\container;

class AdminController extends AbstractActionController
{
   protected $adminTable;

   public function onDispatch(\Zend\Mvc\MvcEvent $e) 
   {
        /* $validUser = new AuthUser();
         if (!$validUser->Validate()){
            return $this->redirect()->toRoute('application');
        }*/
        $namespace = new Container('user');
        $namespace->userID = 'Test ID';
        $namespace->userEmail = 'testID@foo.com';
        $namespace->role = 2;
        $namespace->datatelID = 11123;
        
        
        return parent::onDispatch( $e );
   }
   
   public function indexAction()
   {
   }

}
