<?php
namespace App\Model;

use Nette,
    Tracy\Debugger;

class ReservationManager
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

    public function selectReservationsPricesBetweenTwoDates($start, $end){

        $endPlusOne = strtotime('+1 day', $end);
        $pricesArray = $this->database->query("SELECT fromTimestamp, toTimestamp, price
                    FROM prices
                    WHERE fromTimestamp >= ? AND fromTimestamp < ?", $start, $endPlusOne)->fetchAll();

        return $pricesArray;
    }

    public function selectReservationsBetweenTwoDates($start, $end){
        $endPlusOne = strtotime('+1 day', $end);
        $reservationArray = $this->database->query("SELECT fromTimestamp, toTimestamp
                    FROM reservations
                    WHERE fromTimestamp >= ? AND fromTimestamp < ?", $start, $endPlusOne)->fetchAll();

        return $reservationArray;
    }

    public function reserveAccomodation($userData){
        $data = array(
            'id' => null,
            'fromTimestamp' => $userData['from'],
            'toTimestamp' => $userData['to'],
            'name' => $userData['name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            'peopleCount' => $userData['peopleCount'],
            'message' => $userData['message'],
            'verified' => 0
        );

        $q = $this->database->query('SELECT *
                                    FROM reservations
                                    WHERE fromTimestamp = ?
                                    AND toTimestamp = ?', $data['fromTimestamp'], $data['toTimestamp']);
        if($q->getRowCount() == 0){
            $this->database->query('INSERT INTO reservations', $data);
            return true;
        } else {
            return false;
        }
    }

    public function selectUnverifiedReservationsUnderMonth($start){
        $uverifiedReservations = $this->database->query("SELECT * FROM reservations WHERE verified = 0 AND creation > (NOW() - INTERVAL 1 MONTH)");
    }

    public function selectUnverifiedReservationsOverMonth($start){
        $uverifiedReservations = $this->database->query("SELECT * FROM reservations WHERE verified = 0 AND creation < (NOW() - INTERVAL 1 MONTH)");
    }
}
