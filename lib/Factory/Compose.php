<?php
/**
 * A Horde_Injector:: based IMP_Compose:: factory.
 *
 * PHP version 5
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.fsf.org/copyleft/gpl.html GPL
 * @link     http://pear.horde.org/index.php?package=IMP
 * @package  IMP
 */

/**
 * A Horde_Injector:: based IMP_Compose:: factory.
 *
 * Copyright 2010-2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.fsf.org/copyleft/gpl.html GPL
 * @link     http://pear.horde.org/index.php?package=IMP
 * @package  IMP
 */
class IMP_Factory_Compose
{
    /**
     * Instances.
     *
     * @var array
     */
    private $_instances = array();

    /**
     * The injector.
     *
     * @var Horde_Injector
     */
    private $_injector;

    /**
     * Constructor.
     *
     * @param Horde_Injector $injector  The injector to use.
     */
    public function __construct(Horde_Injector $injector)
    {
        $this->_injector = $injector;

        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * Return the IMP_Compose:: instance.
     *
     * @param string $cacheid  The cache ID string.
     *
     * @return IMP_Compose  The singleton compose instance.
     * @throws IMP_Exception
     */
    public function create($cacheid = null)
    {
        if (empty($cacheid)) {
            $cacheid = strval(new Horde_Support_Randomid());
        } elseif (!isset($this->_instances[$cacheid])) {
            $this->_instances[$cacheid] = $GLOBALS['session']->retrieve($cacheid);
        }

        if (empty($this->_instances[$cacheid])) {
            $this->_instances[$cacheid] = new IMP_Compose($cacheid);
        }

        return $this->_instances[$cacheid];
    }

    /**
     * Tasks to perform on shutdown.
     */
    public function shutdown()
    {
        global $session;

        $cache = $session->get('imp', 'compose_cache', Horde_Session::TYPE_ARRAY);
        $changed = false;

        foreach ($this->_instances as $key => $val) {
            switch ($val->changed) {
            case 'changed':
                $val->changed = '';
                $session->store($val, false, $key);
                $cache[$key] = 1;
                $changed = true;
                break;

            case 'deleted':
                unset($cache[$key]);
                $session->purge($key);
                $changed = true;
                break;
            }

        }

        if ($changed) {
            $session->set('imp', 'compose_cache', $cache);
        }
    }

}
