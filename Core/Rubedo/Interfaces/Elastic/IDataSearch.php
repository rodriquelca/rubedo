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
namespace Rubedo\Interfaces\Elastic;

/**
 * Interface of data search services
 *
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
interface IDataSearch
{

    /**
     * Initialize a search service handler to index or search data
     *
     * @param string $host http host name
     * @param string $port http port 
     */
    public function init ($host = null, $port= null);

    /**
     * Create ES type for new content type
     *     
	 * @param string $terms terms to search
	 * @param string $type optional content type filter
	 * @param string $lang optional lang filter
	 * @param string $author optional author filter
	 * @param string $date optional date filter
	 * @param string $pager optional pager, default set to 10
	 * @param string $orderBy optional  orderBy, default sort on score
	 * @param string $pageSize optional page size, "all" for everything
     * @return Elastica_ResultSet
     */
    public function search ($terms, $type, $lang, $author, $date, $pager, $orderBy, $pageSize);
		
}