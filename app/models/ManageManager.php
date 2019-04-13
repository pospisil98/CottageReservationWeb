<?php
namespace App\Model;

use Nette,
    Tracy\Debugger;

class ManageManager
{
    use Nette\SmartObject;

    /**
     * @var Nette\Database\Context
     */
    private $database;

    //DB from DI container
    public function __construct(Nette\Database\Connection $database)
    {
        $this->database = $database;
    }

    public function changePriceOfTerm($from, $to, $newPrice){
        $select = $this->database->query("SELECT * FROM prices WHERE fromTimestamp = ? AND toTimestamp = ?", $from, $to);

        if($select->getRowCount() == 0){
            $data = array(
                'id' => null,
                'fromTimestamp' => $from,
                'toTimestamp' => $to,
                'price' => $newPrice,
            );
            $this->database->query("INSERT INTO prices", $data);
        } else {
            $this->database->query("UPDATE prices
                                    SET price = ?
                                    WHERE fromTimestamp = ? AND toTimestamp = ?", $newPrice, $from, $to);
        }
    }

    public function getAllUnverifiedReservations()
    {
        return $this->database->query('SELECT * FROM reservations WHERE verified = 0')->fetchAll();
    }

    public function getAllVerifiedReservations()
    {
        return $this->database->query('SELECT * FROM reservations WHERE verified = 1')->fetchAll();
    }

    public function verifyReservation($from, $to){
        $this->database->query("UPDATE reservations
                                SET verified = 1
                                WHERE fromTimestamp = ? AND toTimestamp = ?", $from, $to);
    }

    public function deleteReservation($from, $to){
        $this->database->query('DELETE FROM reservations WHERE fromTimestamp = ? AND toTimestamp = ?', $from, $to);
    }

}
