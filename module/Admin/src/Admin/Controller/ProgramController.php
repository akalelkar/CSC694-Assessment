<?php

namespace Admin\Controller;

use Admin\Model\Program;
use Admin\Form\ProgramForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
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
        //Get all programs
        $paginator = $this->getProgramQueries()->fetchAll(true);
        // set the current page to what has been passed in query string, or to 1 if none set
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $paginator->setItemCountPerPage(10);

        //add program form
        $units = $this->getUnitQueries()->getUnitsForSelect();
        $form = new ProgramForm($units);
        $form->get('submit')->setValue('Add');

        //send paginator and form to index view
        return new ViewModel(array(
            'paginator' => $paginator,
            'form' => $form
        ));
    }

    /*
     *  Program Add Action
     */

    public function addAction() {
        //the add program form
        $units = $this->getUnitQueries()->getUnitsForSelect();
        $form = new ProgramForm($units);
        $form->get('submit')->setValue('Add');

        //if form is returned with post
        $request = $this->getRequest();
        if ($request->isPost()) {

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
        return array('form' => $form);
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
            return $this->redirect()->toRoute('user');
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
