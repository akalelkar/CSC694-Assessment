<?php

namespace Admin\Controller;

use Admin\Model\User;
use Admin\Entity\UserObj;
use Admin\Entity\Role;
use Admin\Entity\UnitPriv;
use Admin\Form\UserForm;
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
        $args['roles'] = $this->getGenericQueries()->getRoleTerms(false);

        //create user form
        $args['count'] = 1;
        $args['liaison_privs'] = $this->getUnitQueries()->getUnitsForSelect();//unlimited liaisons
        $args['unit_privs'] = $this->getUnitQueries()->getUnitsForSelectAssessors();//two assessors
        $form = new UserForm(null, $args);
        $form->get('submit')->setValue('Add');


        $disabledPrivs = $this->getUnitQueries()->getDisablePrivUnits();
        //send paginator,and form to page
        return new ViewModel(array(
            'page' => $page,
            'paginator' => $paginator,
            'form' => $form,
            'disabledPrivs' =>$disabledPrivs
        ));
    }

    /*
     * User Add Action
     */

    public function addAction() {
        //get role terms for form and build form
        $args['count'] = 4;
        $args['liaison_privs'] = $this->getUnitQueries()->getUnitsForSelect();//unlimited liaisons
        $args['unit_privs'] = $this->getUnitQueries()->getUnitsForSelectAssessors();//two assessors
        $args['roles'] = $this->getGenericQueries()->getRoleTerms(false);
        $disabledPrivs = $this->getUnitQueries()->getDisablePrivUnits();
        $form = new UserForm(null, $args);
        $form->get('submit')->setValue('Add');

        //post request, otherwise return form (only accepting post)
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $user->exchangeArray($form->getData());

                //TODO: something to check it email exists
                //need to handle error of adding users of same email
                //save the user
                $this->getUserQueries()->saveUser($user);

                // Redirect to list of users
                return $this->redirect()->toRoute('user');
            }else{
              #Debug::dump($form->getMessages());
            }
        }
        return array(
            'form' => $form,
            'count' => 4,
            'disabledPrivs' =>$disabledPrivs);
    }

    /*
     * User Edit Action
     */

    public function editAction() {
        //the user id from route
        $id = (int) $this->params()->fromRoute('id');
        if (!$id) {
            return $this->redirect()->toRoute('user', array(
                        'action' => 'add'
            ));
        }

        //get the user object from the database
        $user = $this->getUserQueries()->getUser($id);

        $count = 0;
        foreach($user->user_roles as $key =>$value)
        {
            $name = 'role_'.$count;
            $user->$name = $key;
            $count++;        
        }
                
        //get unit and liasion privs
        $user->unit_privs = $this->getUserQueries()->getUserUnitPrivs($id,'unit_privs');
        $user->liaison_privs = $this->getUserQueries()->getUserUnitPrivs($id,'liaison_privs');

        //build form
        $args['count'] = 4;
        $args['roles'] = $this->getGenericQueries()->getRoleTerms(false);
        $args['action'] = 'edit';
        $args['user_id'] = $id;
        $args['liaison_privs'] = $this->getUnitQueries()->getUnitsForSelect();//unlimited liaisons
        $args['unit_privs'] = $this->getUnitQueries()->getUnitsForSelectAssessors();//two assessors
        $disabledPrivs = $this->getUnitQueries()->getDisablePrivUnits();
        $form = new UserForm(null, $args);
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'Save');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                //save user
                $this->getUserQueries()->saveUser($form->getData());

                // Redirect to list of users
                return $this->redirect()->toRoute('user');
            }
        }
        return array(
            'id' => $id,
            'form' => $form,
            'count' => 4,
            'disabledPrivs' =>$disabledPrivs
        );
    }

    /*
     * Delete user action
     */

    public function deleteAction() {
        //id from route
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('user');
        }

        //post request, otherwise return form (only accepting post)
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getUserQueries()->deleteUser($id);
            }

            // Redirect to list of users
            return $this->redirect()->toRoute('user');
        } else {
            $this->getUserQueries()->deleteUser($id);
            return $this->redirect()->toRoute('user');
        }
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

    /* not working but would have been awesome
     * 
     * Files this function uses
     *  Entity
     *       Role.php
     *       UnitPriv.php
     *       UserObj.php
     *  Form
     *       CreateUserObj.php
     *       RoleFieldset.php
     *       UnitPrivFieldset.php
     *       UserObjFieldset.php
     *  view
     *   admin
     *       user
     *           newuser.phtml
     * 
     *  These files together would have created a form that 
     *  got displayed in fieldsets and was capable to having multiple 
     *  fieldsets generated for entities.
     * 
     *  This would be nice for assigning Units to users but due to time
     *  constraints it was left unfinished. It also would have replaced 
     *  the current "working" user form" and is much cleaner/easier to follow
     *  I came really close though!
     * 
     *  The snag I hit involved not getting the Role and UnitPriv Fieldsets
     *  to bind with preloaded values.
     *
      public function newuserAction()
      {
        $id = (int) $this->params()->fromRoute('id');
        if (!$id) {
            return $this->redirect()->toRoute('user', array(
                        'action' => 'add'
            ));
        }
        $ouser = $this->getUserQueries()->getUser('137');
        foreach ($ouser->user_roles as $role => $value) {
            $user_roles[] = $role;
        }

        $form = new CreateUserObj();
        $user = new UserObj();
        $user->setFirstname('Jack');
        $user->setLastname('gregory');
        $user->setMiddleinit('w');
        $user->setEmail('jgregory700@gmail.com');

        $role1 = new Role();
        $role1->setName('Admin');

        $role2 = new Role();
        $role2->setName(3);

        $roles = array($role1, $role2);
        $priv = new UnitPriv();
        $priv->setName('HST');

        $user->setRoles($roles);
        $user->setUnitPrivs(array($priv));

        $form->bind($user);
        Debug::dump($user);
        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                var_dump($form);
            }
        }
        return array(
            'form' => $form
        );
      } */
}
