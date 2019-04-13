<?php

namespace App\Presenters;

use App,
    Nette,
    Nette\Application\UI\Form,
    Nette\Utils\DateTime,
    Tracy\Debugger,
    App\Model\ReservationManager,
    App\Model\ManageManager;

class ManagePresenter extends  BasePresenter
{
    /** @var ReservationManager */
    private $reservationManager;

    /** @var ManageManager */
    private $manageManager;

    public function __construct(ReservationManager $reservationManager, ManageManager $manageManager)
    {
        $this->reservationManager = $reservationManager;
        $this->manageManager = $manageManager;
    }

    public function actionDefault(){
        //redirect if not logged in
        if(!$this->user->isLoggedIn())
            $this->redirect("Sign:in");

        $this->template->fromToArray = $this->createFromToArrayWeek();
        $this->template->unverifiedReservations = $this->manageManager->getAllUnverifiedReservations();
        $this->template->verifiedReservations = $this->manageManager->getAllVerifiedReservations();

    }

    public function actionChangePrice($from, $to, $price){
        //redirect if not logged in
        if(!$this->user->isLoggedIn())
            $this->redirect("Sign:in");

        $this->template->to = $to;
        $this->template->from = $from;
        $this->template->price = $price;
    }

    public function actionVerify($from, $to){
        //redirect if not logged in
        if(!$this->user->isLoggedIn())
            $this->redirect("Sign:in");

        $this->manageManager->verifyReservation($from, $to);
        $this->flashMessage("Ověřeno", 'success');
        $this->redirect("Manage:default#verify");
    }

    public function createComponentStornoForm(){
        $form = new Form();

        $form->addHidden('from');

        $form->addHidden('to');

        $form->addSubmit('submit');

        $form->onSuccess[] = array($this, 'stornoReservation');

        return $form;
    }

    public function stornoReservation($form, $values){
        Debugger::barDump($values);
        $this->manageManager->deleteReservation($values['from'], $values['to']);

        $this->flashMessage("Stornováno", "success");
        $this->redirect("Manage:default#storno");
    }

    public function createComponentChangePriceForm(){
        $form = new Form();

        $form->addHidden('from');

        $form->addHidden('to');

        $form->addText('price')
            ->setRequired();

        $form->addSubmit('submit');

        $form->onSuccess[] = array($this, 'changePrice');

        return $form;
    }

    public function changePrice($form, $values){
        $this->manageManager->changePriceOfTerm($values['from'], $values['to'], $values['price']);

        $this->flashMessage("Cena byla změněna", 'success');
        $this->redirect("Manage:default");
    }

    //sat - sat
    private function createFromToArrayWeek()
    {
        //timestamp of starting day
        $todaysTimestamp = strtotime("now");

        //timestamp of last saturday
        $lastSaturdaysTimestamp = strtotime("last Saturday", $todaysTimestamp);

        //timestamp of ending date
        $todayInOneYearsTimestap = strtotime('+1 year', $todaysTimestamp);

        /*
         * Array with possibly changed prices for determined term
         * item => fromTimestamp | toTimestamp | price
         */
        $pricesArray = $this->reservationManager->selectReservationsPricesBetweenTwoDates($todaysTimestamp, $todayInOneYearsTimestap);
        $fromStampsOfprices = array_column($pricesArray, 'fromTimestamp');
        Debugger::barDump($pricesArray, "ceny");
        Debugger::barDump($fromStampsOfprices, "fromStampy");

        $reservedTerms = $this->reservationManager->selectReservationsBetweenTwoDates($todaysTimestamp, $todayInOneYearsTimestap);

        $currentTermEnd = $currentTermStart = $lastSaturdaysTimestamp;
        $iterator = 0;
        $fromToArray = array();

        /*  Create array in from - to format
         *  Saturday - Saturday
         */
        while ($currentTermEnd < $todayInOneYearsTimestap) {
            $currentTermEnd = strtotime('+7 days', $currentTermStart);

            $fromToArray[$iterator]["from"] = $currentTermStart;
            $fromToArray[$iterator]["to"] = $currentTermEnd;

            $currentTermStart = $currentTermEnd;

            //if the term has changed price take it from DB list
            $key = array_search($fromToArray[$iterator]["from"], array_column($pricesArray, 'fromTimestamp'));
            if($key === FALSE){
                $fromToArray[$iterator]["price"] = parent::DEFAULT_PRICE;
            } else {
                $fromToArray[$iterator]["price"] = $pricesArray[$key]['price'];
            }

            //if the term is reserved disable the reservation of it
            $key = array_search($fromToArray[$iterator]["from"], array_column($reservedTerms, 'fromTimestamp'));
            if($key === FALSE){
                $fromToArray[$iterator]["disabled"] = '';
            } else {
                $fromToArray[$iterator]["disabled"] = 'reserve-disabled';
            }

            //if it is not reserved and the week has started
            if($iterator == 0 && $fromToArray[$iterator]["from"] < strtotime('now'))
                $fromToArray[$iterator]["disabled"] = 'reserve-disabled';

            $iterator++;
        }

        $this->barDumpTimestampArrayReadable($fromToArray);

        return $fromToArray;
    }

    private function barDumpTimestampArrayReadable($fromToArray)
    {
        $datesArray = array();
        $iterator = 0;
        foreach ($fromToArray as $item) {
            $datesArray[$iterator]["from"] = date("d.m.Y", $item["from"]);
            $datesArray[$iterator]["to"] = date("d.m.Y", $item["to"]);
            $datesArray[$iterator]["disabled"] = $item["disabled"];
            $datesArray[$iterator]["price"] = $item["price"];
            $iterator++;
        }

        Debugger::barDump($datesArray, "Readable from - to");
    }
}
