<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Authentication\AuthUser;
use Zend\session\container;

class AdminController extends AbstractActionController
{
   protected $adminTable;

   public function onDispatch(\Zend\Mvc\MvcEvent $e) {
       /* $validUser = new AuthUser();
        if (!$validUser->Validate())
        {
            return $this->redirect()->toRoute('application');
        }
        else {
            $namespace = new Container('user');
            if($namespace->role != 1)
          
            {
              return $this->redirect()->toRoute('application');
            }
       */
            return parent::onDispatch($e);
       // }
   }        
   
   public function indexAction()
   {
      
      
      
   }

}
