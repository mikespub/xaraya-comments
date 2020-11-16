<?php
/**
 * Comments Module
 *
 * @package modules
 * @subpackage comments
 * @category Third Party Xaraya Module
 * @version 2.4.0
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
if (defined('_COM_SORT_ASC')) {
    return;
}

// the following two defines specify the sorting direction which
// can be either ascending or descending
define('_COM_SORT_ASC', 1);
define('_COM_SORT_DESC', 2);

// the following four defines specify the sort order which can be any of
// the following: author, date, topic, lineage
// TODO: Add Rank sorting
define('_COM_SORTBY_AUTHOR', 1);
define('_COM_SORTBY_DATE', 2);
define('_COM_SORTBY_THREAD', 3);
define('_COM_SORTBY_TOPIC', 4);

// the following define is for $id when
// you want to retrieve all comments as opposed
// to entering in a real comment id and getting
// just that specific comment
define('_COM_RETRIEVE_ALL', 1);
define('_COM_VIEW_FLAT', 'flat');
define('_COM_VIEW_NESTED', 'nested');
define('_COM_VIEW_THREADED', 'threaded');

// the following defines are for the $depth variable
// the -1 (FULL_TREE) tells it to get the full
// tree/branch and the the 0 (TREE_LEAF) tells the function
// to acquire just that specific leaf on the tree.
//
define('_COM_FULL_TREE', ((int) '-1'));
define('_COM_TREE_LEAF', 1);

// Maximum allowable branch depth
define('_COM_MAX_DEPTH', 10);

// Status of comment nodes
define('_COM_STATUS_DELETED', 0);
define('_COM_STATUS_OFF', 1);
define('_COM_STATUS_ON', 3);
define('_COM_STATUS_ROOT_NODE', 4);
