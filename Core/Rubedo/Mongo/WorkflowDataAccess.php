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
namespace Rubedo\Mongo;

use Rubedo\Interfaces\Mongo\IWorkflowDataAccess;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class WorkflowDataAccess extends DataAccess implements IWorkflowDataAccess
{
	/**
	 * Contain the current workspace
	 */	
	protected $_currentWs = "live";
	
	/**
	 * Contain common fields
	 */
	protected $_metaDataFields = array('_id', 'id', 'idLabel', 'typeId', 'createTime', 'createUser', 'lastUpdateTime', 'lastUpdateUser', 'version', 'online');
	
	/**
	 * Changes the array to obtain workspace and live blocks
	 * 
	 * @param $obj is an array
	 * @return array
	 */
	protected function _inputObjectFilter($obj){
		foreach ($obj as $key => $value) {
			if(in_array($key, $this->_metaDataFields)){
				continue;
			}
			$obj[$this->_currentWs][$key]=$value;
			unset($obj[$key]);
		}
		
		return $obj;
	}
	
	/**
	 * Changes the array to keep the response usable by the BO
	 * 
	 * @param $obj is an array
	 * @return array
	 */
	protected function _outputObjectFilter($obj){
		if(isset($obj['live']) || isset($obj['workspace'])){
			foreach ($obj[$this->_currentWs] as $key => $value){
				$obj[$key] = $value;
			}
			unset($obj['live']);
			unset($obj['workspace']);
		}
		return $obj;
	}
	
	/**
	 * Adapt filter for the workflow
	 * 
	 * @param $filter is the current filter
	 * @return array compatible with the data in mongoDb
	 */
	protected function _adaptFilter($filterArray){
		if(count($filterArray) == 1){
			$this->clearFilter();
			
			foreach ($filterArray as $key => $value) {
				if($key == '_id'){
					$filterArray['id'] = (string) $value;
					unset($filterArray['_id']);
				}	
					
				if(in_array($key, $this->_metaDataFields)){
					continue;
				}
				$newKey = $this->_currentWs.".".$key;
				$filterArray[$newKey] = $value;
				unset($filterArray[$key]);
			}
			
			$this->addFilter($filterArray);
			return $filterArray;
		}
	}
	
	/**
	 * Adapt sort for the workflow
	 * 
	 * @param $sort is the current sort
	 * @return array compatible with the data in mongoDb
	 */
	protected function _adaptSort($sortArray){
		if(count($sortArray) != 0){
			$this->clearSort();
				
			foreach ($sortArray as $key => $value) {
				if(in_array($key, $this->_metaDataFields)){
					continue;
				}
				$newKey = $this->_currentWs.".".$key;
				$sortArray[$newKey] = $value;
				
				$this->addSort(array($newKey => $sortArray[$newKey]));
				unset($sortArray[$key]);
			}
		}
	}
	
	/**
	 * Adapt fields for the workflow
	 * 
	 * @param $fieldsArray is the current included fields
	 * @return array compatible with the data in mongoDb
	 */
	protected function _adaptFields($fieldsArray){
		if(count($fieldsArray) != 0){
			$this->clearFieldList();
			$newArray = array();
			
			foreach ($fieldsArray as $key => $value) {
				if(in_array($key, $this->_metaDataFields)){
					$newArray[] = $key;
				} else {
					$newKey = $this->_currentWs.".".$key;
					$newArray[] = $newKey;
				}
			}
			
			unset($fieldsArray);
			$this->addToFieldList($newArray);
		}
	}
	
	/**
	 * Adapt excluded fields for the workflow
	 * 
	 * @param $fieldsArray is the current excluded fields
	 * @return array compatible with the data in mongoDb
	 */
	protected function _adaptExcludeFields($fieldsArray){
		if(count($fieldsArray) != 0){
			$this->clearExcludeFieldList();
			$newArray = array();
			
			foreach ($fieldsArray as $key => $value) {
				if(in_array($key, $this->_metaDataFields)){
					$newArray[] = $key;
				} else {
					$newKey = $this->_currentWs.".".$key;
					$newArray[] = $newKey;
				}
			}
			
			unset($fieldsArray);
			$this->addToExcludeFieldList($newArray);
		}
	}
	
	/**
	 * Set the current workspace to workspace
	 */
	public function setWorkspace(){
		$this->_currentWs = 'workspace';
	}
	
	/**
	 * Set the current workspace to live
	 */
	public function setLive(){
		$this->_currentWs = 'live';
	}
	
	/**
	 * Publish a content
	 */
	public function publish($objectId){
		
	}
	
	/**
	 * Allow to read in the current collection
	 * 
	 * @return array
	 */
	public function read(){
		//Adaptation of the conditions for the workflow
		$filter = $this->getFilterArray();
		$this->_adaptFilter($filter);
		$sort = $this->getSortArray();
		$this->_adaptSort($sort);
		$includedFields = $this->getFieldList();
		$this->_adaptFields($includedFields);
		$excludedFields = $this->getExcludeFieldList();
		$this->_adaptExcludeFields($excludedFields);
		
		$content = parent::read();
		
		foreach ($content as $key => $value) {
			$content[$key] = $this->_outputObjectFilter($content[$key]);
		}
		
		return $content;
	}
	
	/**
	 * Allow to update an element in the current collection
	 * 
	 * @return bool
	 */
	public function update(array $obj, $safe = true){
		$obj = $this->_inputObjectFilter($obj);
		
		$result = parent::update($obj, $safe);
		
		$result['data'] = $this->_outputObjectFilter($result['data']);
		
		if(isset($obj[$this->_currentWs]['status'])){
			if($this->_currentWs == 'workspace' && $obj[$this->_currentWs]['status']=='published'){
				//do publish action
				//call version service
			}
		}
		
		return $result;
	}
	
	/**
	 * Allow to create an item in the current collection
	 * 
	 * @return array
	 */
	public function create(array $obj, $safe = true){
		$result = parent::create($obj, $safe);
		
		$result['data'] = $this->_outputObjectFilter($result['data']);
		
		return $result;
	}

}