<?php

namespace Admin\Controller;

use Admin\Model\User;
use Admin\Entity\UserObj;
use Admin\Entity\Role;
use Admin\Entity\UnitPriv;
use Admin\Form\UserForm;
use Admin\Form\EditForm;
use Admin\Form\CreateUserObj;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Application\Authentication\AuthUser;
use Zend\session\container;
use Zend\Debug\Debug;

class UserController extends AbstractActionController {

    protected $tableResults;
    protected $generictableResults;
    protected $unittableResults;

    public function onDispatch(\Zend\Mvc\MvcEvent $e) {
        /*$validUser = new AuthUser();
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

    /*
     * User Index Action
     */

    public function indexAction() {
        //get page number from route, or default to age 1
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        //get all users
        $users = $this->getUserQueries()->fetchAll();

        //set # of items per page
        $itemsPerPage = 10;

        //create our paginator object set current page, items per page, and page range
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($users));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        //get role terms for user form
        $args['roles'] = $this->getGenericQueries()->getRoleTerms();

        //create user form
        $args['count'] = 1;
        $form = new UserForm(null, $args);
        $form->get('submit')->setValue('Add');
        
        //send paginator and form to page
        return new ViewModel(array(
            'page' => $page,
            'paginator' => $paginator,
            'form' => $form,
        ));
    }

    /*
     * User Edit Action
     */

    public function editAction() {
        //the user id from route
        $id = (int) $this->params()->fromRoute('id');

        // if post then this action was called from jquery code for submit button in edit.phtml
        $request = $this->getRequest();
        $post = $this->params();
        
        if ($request->isPost()) {
            $id = $post->fromPost('id');
            if ($post->fromPost('removeLiaison') != null){
                $this->getUserQueries()->removePrivileges($id, $post->fromPost('removeLiaison'), 2); // liaison = 2
            }
            if ($post->fromPost('removeChair') != null){
                $this->getUserQueries()->removePrivileges($id, $post->fromPost('removeChair'), 3); // chair = 3
            }
            if ($post->fromPost('removeAssessor') != null){
                $this->getUserQueries()->removePrivileges($id, $post->fromPost('removeAssessor'), 4); // assessor = 4
            }
         }
var_dump($id);
        //get the user object from the database
        $user = $this->getUserQueries()->getUser($id);

        $count = 0;
        foreach($user->user_roles as $key =>$value)
        {
            $name = 'role_'.$count;
            $user->$name = $key;
            $count++;        
        }

        //build form
        $args['action'] = 'update';
        $args['full_name'] = $user->first_name . ' ' . $user->middle_init . ' ' . $user->last_name;
        $args['roles'] = $this->getGenericQueries()->getRoleTerms();
        $args['user_id'] = $id;
        $args['user_liaison_privs'] = $this->getUserQueries()->getUserPrivs($id, '2'); // liaison = 2
        $args['user_chair_privs'] =  $this->getUserQueries()->getUserPrivs($id, '3'); // chair
        $args['user_assessor_privs'] = $this->getUserQueries()->getUserPrivs($id, '4'); // assessor = 4
        $args['liaison_privs'] = $this->getUnitQueries()->getPrivsForSelect('2');//unlimited liaisons
        $args['chair_privs'] = $this->getUnitQueries()->getPrivsForSelect('3');//unlimited chairs
        $args['assessor_privs'] = $this->getUnitQueries()->getPrivsForSelect('4');//two assessors
        $form = new EditForm($this->url()->fromRoute('user'), $args);
        
        $request = $this->getRequest();
        
        $viewModel = new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
         
       // $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    /*
     * Method to get Generic.php
     */

    public function getGenericQueries() {
        if (!$this->generictableResults) {
            $this->generictableResults = $this->getServiceLocator()
                    ->get('Admin\Model\Generic');
        }
        return $this->generictableResults;
    }

    /*
     * Method to get UserTable
     */

    public function getUserQueries() {
        if (!$this->tableResults) {
            $this->tableResults = $this->getServiceLocator()
                    ->get('Admin\Model\UserTable');
        }
        return $this->tableResults;
    }
    
    /*
     * Method to get UnitTable()
     */
    public function getUnitQueries() {
        if (!$this->unittableResults) {
            $this->unittableResults = $this->getServiceLocator()
                    ->get('Admin\Model\UnitTable');
        }
        return $this->unittableResults;
    }
}
