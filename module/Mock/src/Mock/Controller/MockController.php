<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Mock\Controller;
error_reporting(E_ALL);

ini_set('display_errors',1);
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Mock\Model\Mock;
use Mock\Form\MockForm;
class MockController extends AbstractActionController
{
    protected $mockTable;
 public function indexAction()
    {
       
    }

    public function addplanAction()
    {
        $form = new MockForm();
        $form->get('submit')->setValue('AddPlan');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $mock = new Mock();
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $mock->exchangeArray($form->getData());
                $this->getMockTable()->saveMock($mock);

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }
        return array('form' => $form);
    }

    public function modifyreportAction()
    {
     

        $form = new MockForm();
     
        $form->get('submit')->setAttribute('value', 'ModifyReport');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getMockTable()->saveMock($form->getData());

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }

        
    }
    public function modifysoAction()
    {
     

        $form = new MockForm();
     
        $form->get('submit')->setAttribute('value', 'ModifySo');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getMockTable()->saveMock($form->getData());

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }

        
    }
    public function modifyplanAction()
    {
     

        $form = new MockForm();
     
        $form->get('submit')->setAttribute('value', 'ModifyPlan');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getMockTable()->saveMock($form->getData());

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }

        
    }
//Student Outcomes
    public function soAction()
    {
         $form = new MockForm();
        $form->get('submit')->setValue('SO');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $mock = new Mock();
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $mock->exchangeArray($form->getData());
                $this->getMockTable()->saveMock($mock);

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }
        return array('form' => $form);

    }
    public function addreportAction()
    {
     

        $form = new MockForm();
     
        $form->get('submit')->setAttribute('value', 'AddReport');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getMockTable()->saveMock($form->getData());

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }
    }
    public function addoutcmAction()
    {
     

        $form = new MockForm();
     
        $form->get('submit')->setAttribute('value', 'AddOutcome');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getMockTable()->saveMock($form->getData());

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }
    }
    public function plansAction()
    {
         $form = new MockForm();
        $form->get('submit')->setValue('ViewPlan');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $mock = new Mock();
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $mock->exchangeArray($form->getData());
                $this->getMockTable()->saveMock($mock);

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }
        return array('form' => $form);

    }
    public function reportsAction()
    {
         $form = new MockForm();
        $form->get('submit')->setValue('ViewReport');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $mock = new Mock();
            $form->setInputFilter($mock->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $mock->exchangeArray($form->getData());
                $this->getMockTable()->saveMock($mock);

                // Redirect to list of mocks
                return $this->redirect()->toRoute('mock');
            }
        }
        return array('form' => $form);

    }
}