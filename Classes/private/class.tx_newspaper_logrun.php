<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

class tx_newspaper_LogRun {

    public function __construct(tx_newspaper_StoredObject $object, $pid, $tstamp = 0) {
        $this->object = $object;
        $this->tstamp = $tstamp? $tstamp: time();
        $this->pid = $pid;
        $this->be_user = $GLOBALS['BE_USER']->user['uid']; /// i'm not sure if this object is always available, we'll see ...
    }

    public function write($operation, $comment, $details = '') {
        tx_newspaper::insertRows('tx_newspaper_log', array(
     		'pid' => $this->pid,
     		'tstamp' => $this->tstamp,
     		'crdate' => $this->tstamp,
     		'cruser_id' => $this->be_user,
     		'be_user' => $this->be_user, // same value as cruser_id, but this field is visible in backend
     		'table_name' => $this->object->getTable(),
     		'table_uid' => $this->object->getUid(),
     		'operation' => $operation,
     		'comment' => $comment,
            'details' => $details
     	));
    }

    /** @var tx_newspaper_StoredObject */
    private $object;
    private $tstamp = 0;
    private $pid = 0;
    private $be_user = 0;
}
