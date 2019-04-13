<?php

namespace App\Presenters;

use App,
    Nette,
    Nette\Application\UI\Form,
    Nette\Utils\DateTime,
    Tracy\Debugger,
    App\Model\ReservationManager;

class HomepagePresenter extends BasePresenter
{
    /** @var ReservationManager */
    private $reservationManager;

    /** @var Nette\Mail\IMailer */
    private $mailer;

    public function __construct(Nette\Mail\IMailer $mailer, ReservationManager $reservationManager)
    {
        $this->mailer = $mailer;
        $this->reservationManager = $reservationManager;
    }

    public function startup()
    {
        parent::startup();
    }

    public function renderDefault()
    {
        //$this->template->fromToArray = $this->createFromToArray();
        $this->template->fromToArray = $this->createFromToArrayWeek();
    }

    public function createComponentEmailForm()
    {
        $form = new Form();

        $form->addText('name')
            ->setRequired();

        $form->addEmail('email')
            ->setRequired();

        $form->addTextArea('message')
            ->setRequired();

        $form->addSubmit('submit');

        $form->onSuccess[] = array($this, 'sendEmail');

        return $form;
    }

    public function actionReserve($from, $to)
    {
        $this->template->from = $from;
        $this->template->to = $to;
    }

    public function createComponentReservationForm()
    {
        $form = new Form();

        $form->addHidden('from');

        $form->addHidden('to');

        $form->addText('name')
            ->setRequired();

        $form->addEmail('email')
            ->setRequired();

        $form->addText('phone')
            ->setRequired();

        $form->addText('peopleCount')
            ->addRule(Form::INTEGER)
            ->setRequired();

        $form->addTextArea('message');

        $form->addSubmit('submit');

        $form->onSuccess[] = array($this, 'reserveAccommodation');

        return $form;
    }

    //reservation form success callback
    public function reserveAccommodation($form, $values){
        Debugger::barDump($values);
        //creates reservation in DB
        $reserved = $this->reservationManager->reserveAccomodation($values);

        if($reserved){
            //sends email to the customer
            $this->sendReservationEmail($values);
            //sends email to the owner about new reservation
            $this->sendNewReservationEmail($values);

            $this->redirect("Homepage:success");
        } else {
            $this->redirect("Homepage:failure");
        }
    }

    //sends email to the customenr about his new reservation
    public function sendReservationEmail($values)
    {
        //TODO: rework
    }

    //sends email to the owner about new reservation
    public function sendNewReservationEmail($values)
    {
        $mail = new Nette\Mail\Message();

        $mail->setFrom($values['email'])
            ->addTo(parent::OWNER_EMAIL)
            ->setHtmlBody('
                <h1>Nová rezervace</h1>
                <p>Jméno: ' . $values['name'] . '</p>
                <p>Email: ' . $values['email'] . '</p>
                <p>Telefon: ' . $values['phone'] . '</p>
                <p>Počet lidí: ' . $values['peopleCount'] . '</p>
                <p>Zpráva: ' . $values['message'] . '</p>');

        Debugger::barDump($mail);

        //TODO:  $this->mailer->send($mail);
    }

    //sends email to the owner with message
    public function sendEmail($form, $values)
    {
        $mail = new Nette\Mail\Message();

        $mail->setFrom($values['email'])
            ->addTo(parent::OWNER_EMAIL)
            ->setHtmlBody('
                <h1>Nová zpráva</h1>
                <p>Jméno: ' . $values['name'] . '</p>
                <p>Text zprávy: ' . $values['message'] . '</p>');

        Debugger::barDump($mail);

        //TODO:  $this->mailer->send($mail);
    }

    //mon-fri sat-sun
    private function createFromToArray()
    {
        //timestamp of starting day
        $todaysTimestamp = $from;


        //timestamp of last saturday
        $lastSaturdaysTimestamp = strtotime("last Saturday", $todaysTimestamp);

        //timestamp of ending date
        $todayInOneYearsTimestap = $to;


        //workaround for first iteration
        $currentTermStart = $currentTermEnd = strtotime('-1 day', $lastMondaysTimestamp);
        $isWeekend = false;
        $iterator = 0;
        $fromToArray = array();

        /*  Create array in from - to format
         *  Monday - Friday, Saturday - Sunday
         */
        while ($currentTermEnd < $todayInOneYearsTimestap) {
            if ($isWeekend) {
                $currentTermStart = strtotime('+1 day', $currentTermStart);
                $currentTermEnd = strtotime('+1 days', $currentTermStart);

                $fromToArray[$iterator]["from"] = $currentTermStart;
                $fromToArray[$iterator]["to"] = $currentTermEnd;

                $currentTermStart = $currentTermEnd;
            } else {
                $currentTermStart = strtotime('+1 day', $currentTermStart);
                $currentTermEnd = strtotime('+4 days', $currentTermStart);

                $fromToArray[$iterator]["from"] = $currentTermStart;
                $fromToArray[$iterator]["to"] = $currentTermEnd;

                $currentTermStart = $currentTermEnd;
            }

            $iterator++;
            $isWeekend = !$isWeekend;
        }

        //return to start of last term
        $lastTermStart = "";
        if ($isWeekend) {
            $lastTermStart = strtotime('-4 day', $currentTermStart);
        } else {
            $lastTermStart = strtotime('-1 days', $currentTermStart);
        }

        $this->barDumpTimestampArrayReadable($fromToArray);

        return $fromToArray;
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

        $reservedTerms = $this->reservationManager->selectReservationsBetweenTwoDates($todaysTimestamp, $todayInOneYearsTimestap);

        Debugger::barDump($pricesArray, "CENY");
        Debugger::barDump($reservedTerms, "REZERVACE");

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
            Debugger::barDump($fromToArray[$iterator]["from"], "začátek");
            Debugger::barDump(array_search($fromToArray[$iterator]["from"], array_column($pricesArray, 'fromTimestamp')), "search");

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

        Debugger::barDump($fromToArray, "nečitelně");
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
