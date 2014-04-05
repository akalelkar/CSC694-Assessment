<?php

namespace Admin\Controller;

use Admin\Model\Program;
use Admin\Model\Unit;
use Admin\Form\ProgramForm;
use Admin\Form\UnitForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Application\Authentication\AuthUser;
use Zend\session\container;

class ProgramController extends AbstractActionController {

    protected $tableResults;
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
        //}
    }

    /*
     * Program Index Action
     */

    public function indexAction() {
        //get page number from route, or default to age 1
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
   
        //Get all programs
        $programs = $this->getProgramQueries()->fetchAll(true);
        
        //set # of items per page
        $itemsPerPage = 10;

        //create our paginator object set current page, items per page, and page range
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($programs));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage);

   
        //add program form
        $units = $this->getUnitQueries()->getUnitsForSelect();
        $programform = new ProgramForm($units);

        //add unit form
        $unitform = new UnitForm();
     
        //send paginator and form to index view
        return new ViewModel(array(
            'page' => $page,
            'paginator' => $paginator,
            'unitform' => $unitform,
            'programform' => $programform
        ));
    }

    /*
     *  Unit Add Action
     */

    public function addunitAction() {
        //the add unit form
        $form = new UnitForm();

        //if form is returned with post
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($request->getPost()['unit_id'] == NULL) {
                return $this->redirect()->toRoute('program');
            }
            else{
                //get the form data
                $unit = new Unit();
                $form->setInputFilter($unit->getInputFilter());
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    
                    $unit->exchangeArray($form->getData());
    
                    //save the unit
                    $this->getUnitQueries()->saveUnit($unit);
                }
                // Redirect to list of programs
                return $this->redirect()->toRoute('program');
            }
        }
        return array('unitform' => $form);
    }

    
    /*
     *  Program Add Action
     */

    public function addprogramAction() {
        //the add program form
        $units = $this->getUnitQueries()->getUnitsForSelect();
        $form = new ProgramForm($units);

        //if form is returned with post
        $request = $this->getRequest();
        if ($request->isPost()){
            if ($request->getPost()['name'] == NULL) {
                return $this->redirect()->toRoute('program');
            }
            else{
                //get the form data
                $program = new Program();
                $form->setInputFilter($program->getInputFilter());
                $form->setData($request->getPost());
    
                if ($form->isValid()) {
                    $program->exchangeArray($form->getData());
    
                    //save the program
                    $this->getProgramQueries()->saveProgram($program);
    
                    // Redirect to list of programs
                    return $this->redirect()->toRoute('program');
                }
            }
        }
        return array('programform' => $form);
    }

    /*
     *  Program Edit Action
     */

    public function editAction() {
        //get id from route or redirect user to programs page if unavailable
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('program', array(
                        'action' => 'add'
            ));
        }

        //get the program values via program id
        $program = $this->getProgramQueries()->getProgram($id);

        //the program edit form, bind with values from database
        $units = $this->getUnitQueries()->getUnitsForSelect();
        $form = new ProgramForm($units);
        $form->bind($program);
        $form->get('submit')->setAttribute('value', 'Save');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($program->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getProgramQueries()->saveProgram($form->getData());

                // Redirect to list of programs
                return $this->redirect()->toRoute('program');
            }
        }
        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    /*
     * Delete Program action
     */

    public function deleteAction() {
        
        //get id from route or redirect user to programs page if unavailable
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('program');
        }
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getProgramQueries()->deleteProgram($id);
            }

            // Redirect to list of users
            return $this->redirect()->toRoute('program');
        } else {
            //delete the program and redirect user
            $this->getProgramQueries()->deleteProgram($id);
            return $this->redirect()->toRoute('program');
        }
    }

    /*
     * Method to get the ProgramTable
     */
    public function getProgramQueries() {
        if (!$this->tableResults) {
            $this->tableResults = $this->getServiceLocator()
                    ->get('Admin\Model\ProgramTable');
        }
        return $this->tableResults;
    }

    /*
     * Method to get the UserTable
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
