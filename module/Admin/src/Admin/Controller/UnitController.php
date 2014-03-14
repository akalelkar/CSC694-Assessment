<?php

namespace Admin\Controller;

use Admin\Model\Unit;
use Admin\Form\UnitForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Application\Authentication\AuthUser;
use Zend\session\container;
use Zend\Debug\Debug;

class UnitController extends AbstractActionController {

    protected $unittableResults;
    protected $programtableResults;
    protected $usertableResults;

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
     *  Unit Index Action
     */

    public function indexAction() {
        //get all units
        $paginator = $this->getUnitQueries()->fetchAll(true);
        // set the current page to what has been passed in query string, or to 1 if none set
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array(
            'paginator' => $paginator
        ));
    }

    /*
     * Unit Add Action
     */

    public function addAction() {
        //get assessors/liaisons with role 3(chair) 4(assessor) , 2(liaison)
        //TODO: use GenericQueries function to get roles
        $assessors = array(3, 4);
        $assessors = $this->getUserQueries()->fetchUsersByRole($assessors);
        $liaisons = array(2);
        $liaisons = $this->getUserQueries()->fetchUsersByRole($liaisons);

        //the Unit add form
        $form = new UnitForm();
        $form->setAssessors($assessors);
        $form->setLiaisons($liaisons);
        $form->buildForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $unit = new Unit();
            $form->setInputFilter($unit->getInputFilter());
            $form->setData($request->getPost());

            if (!$form->isValid()) {
                print_r($form->getMessages());
            }
            if ($form->isValid()) {
                $unit->exchangeArray($form->getData());

                //save the unit
                $this->getUnitQueries()->saveUnit($unit);

                // Redirect to list of units
                return $this->redirect()->toRoute('unit');
            }
        }
        return array('form' => $form);
    }

    /*
     * Unit Edit Action
     */

    public function editAction() {
        //get unit ID from route
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('unit', array(
                        'action' => 'add'
            ));
        }

        //get the Unit requested to be edited
        $unit = $this->getUnitQueries()->getUnit($id);

        //get unit's assessor/liasion
        $assessorPrivs = $this->getUnitQueries()->getUnitPrivs($id, 'unit_privs', 1);
        $liaisonPrivs = $this->getUnitQueries()->getUnitPrivs($id, 'liaison_privs', 1);

        //assign assessor and liasion to Unit to us in form
        $unit->assessor_1 = (isset($assessorPrivs[0])) ? $assessorPrivs[0] : '';
        $unit->liaison_1 = (isset($liaisonPrivs[0])) ? $liaisonPrivs[0] : '';


        //get assessors/liaisons with role 3(chair) 4(assessor) , 2(liaison)
        //TODO: use GenericQueries function to get roles
        $assessors = array(3, 4);
        $assessors = $this->getUserQueries()->fetchUsersByRole($assessors);
        $liaisons = array(2);
        $liaisons = $this->getUserQueries()->fetchUsersByRole($liaisons);


        //the Unit form
        $form = new UnitForm();
        $form->setAssessors($assessors);
        $form->setLiaisons($liaisons);
        $form->buildForm();
        $form->bind($unit);
        $form->get('submit')->setAttribute('value', 'Save');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($unit->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getUnitQueries()->saveUnit($form->getData());
                // Redirect to list of users
                return $this->redirect()->toRoute('unit');
            }
        }
        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    /*
     * Delete Unit Action
     */

    public function deleteAction() {
        //ID from route
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('unit');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getUnitQueries()->deleteUnit($id);
            }

            // Redirect to list of users
            return $this->redirect()->toRoute('unit');
        } else {
            //delete Unit
            $this->getUnitQueries()->deleteUnit($id);
            return $this->redirect()->toRoute('unit');
        }
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

    /*
     * Method to get ProgramTable()
     */

    public function getProgramQueries() {
        if (!$this->programtableResults) {
            $this->programtableResults = $this->getServiceLocator()
                    ->get('Admin\Model\ProgramTable');
        }
        return $this->programtableResults;
    }

    /*
     * Method to get UserTable()
     */

    public function getUserQueries() {
        if (!$this->usertableResults) {
            $this->usertableResults = $this->getServiceLocator()
                    ->get('Admin\Model\UserTable');
        }
        return $this->usertableResults;
    }

}
