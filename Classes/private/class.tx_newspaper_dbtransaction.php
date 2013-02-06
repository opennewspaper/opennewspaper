<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

/**
 *  Use objects of this class to wrap database operations into a MySQL transaction.
 *
 *  Usage:
 *
 *  try {
 *    $transaction = new tx_newspaper_DBTransaction();
 *    bunch_of_db_operations_that_throw_when_something_goes_wrong();
 *    $transaction->commit();
 *  catch(tx_newspaper_Exception $e) { }
 *
 *  The transaction is rolled back if commit() is not called explicitly, as soon as $transaction
 *  goes out of scope (more precisely, as soon as the last reference to $transaction is deleted.
 *  So don't create additional references to $transaction, or strange things may happen.)
 */
class tx_newspaper_DBTransaction {

    public function __construct() {
        if (self::$transaction_in_progress) throw new tx_newspaper_DBException('Cannot start a transaction; another transaction is already in progress');
        self::$transaction_in_progress = true;
        tx_newspaper_DB::getInstance()->executeQuery('START TRANSACTION WITH CONSISTENT SNAPSHOT');
    }

    public function __destruct() {
        if (self::$transaction_in_progress) $this->rollback();
    }

    public function commit() {
        tx_newspaper_DB::getInstance()->executeQuery('COMMIT');
        self::$transaction_in_progress = false;
    }

    public function rollback() {
        tx_newspaper_DB::getInstance()->executeQuery('ROLLBACK');
        self::$transaction_in_progress = false;
    }

    private static $transaction_in_progress = false;
}