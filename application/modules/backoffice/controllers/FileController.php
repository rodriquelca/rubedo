<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

use Rubedo\Mongo\DataAccess, Rubedo\Mongo, Rubedo\Services;

/**
 * Controller providing access control list
 *
 * Receveive Ajax Calls with needed ressources, send true or false for each of them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_FileController extends Zend_Controller_Action
{

    function indexAction() {
        $fileService = Rubedo\Services\Manager::getService('Images');
        $filesArray = $fileService->getList();
        $data = $filesArray['data'];
        $files = array();
        foreach ($data as $value) {
            $metaData = $value->file;
            $metaData['id'] = (string)$metaData['_id'];
            unset($metaData['_id']);
            $files[] = $metaData;
        }
        $this->view->files = $files;
    }

    function putAction() {
        $adapter = new Zend_File_Transfer_Adapter_Http();

        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            echo implode("\n", $messages);
        }

        $fileInfo = array_pop($adapter->getFileInfo());

        /*
         $finfo = finfo_open(FILEINFO_MIME);
         if ($finfo){
         $newInfos = finfo_file($finfo, $fileInfo['tmp_name']);
         finfo_close($finfo);
         }
         \Zend_Debug::dump($newInfos);

         */
         

        $fileService = Rubedo\Services\Manager::getService('Images');
        $obj = array('serverFilename' => $fileInfo['tmp_name'],'text'=>$fileInfo['name'], 'filename' => $fileInfo['name'], 'Content-Type' => $fileInfo['type']);
        $result = $fileService->create($obj);
        if ($result['success'] == true) {
            $this->redirect($this->_helper->url('index'));
        } else {
            $this->_helper->json($result);
        }
    }

    function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $fileId = $this->getRequest()->getParam('file-id');
        $version = $this->getRequest()->getParam('file-version', 1);

        if (isset($fileId)) {
            $fileService = Rubedo\Services\Manager::getService('Images');
            $result = $fileService->destroy(array('id' => $fileId, 'version' => $version));

            if ($result['success'] == true) {
                $this->redirect($this->_helper->url('index'));
            }

        } else {
            throw new Zend_Controller_Exception("No Id Given", 1);

        }
    }

    function getAction() {
        $this->_forward('index', 'image', 'default');
    }
	
	function getMetaAction(){
		
        $fileId = $this->getRequest()->getParam('file-id');

        if (isset($fileId)) {
            $fileService = Rubedo\Services\Manager::getService('Images');
            $obj = $fileService->findById($fileId);
			if(! $obj instanceof MongoGridFSFile){
				throw new Zend_Controller_Exception("No Image Found", 1);
			}
			$this->_helper->json($obj->file);

        } else {
            throw new Zend_Controller_Exception("No Id Given", 1);

        }
	}
	
	function dropAllFilesAction(){
		$fileService = Rubedo\Services\Manager::getService('MongoFileAccess');
		$fileService->init();
		return $this->_helper->json($fileService->drop());
	}

}