<?php

namespace App\Presenters;

use Nette,
    Nette\Application\UI\Form,
    Tracy;


class SignPresenter extends  BasePresenter
{
    public function __construct()
    {

    }

    public function actionOut()
    {
        $usr = $this->getUser();
        $usr->logout();
        $this->redirect("Homepage:");
    }

    public function actionDefault(){
        if($this->user->isLoggedIn()){
            $this->redirect('Manage:default');
        } else {
            $this->redirect('in');
        }
    }

    public function actionIn(){
        if($this->user->isLoggedIn()) {
            $this->redirect('Manage:default');
        }
    }

    protected function createComponentSignInForm()
    {
        $form = new Form();

        $form->addText('username')->setRequired();
        $form->addPassword('password')->setRequired();
        $form->addSubmit('submit', 'Přihlásit se');

        $form->onSuccess[] = array($this, 'signInFormSucceeded');

        return $form;
    }

    public function signInFormSucceeded($form, $values)
    {
        $user = $this->getUser();

        try {
            $user->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $this->flashMessage("Uživatelské jméno nebo heslo je chybné", 'error');
            $this->redirect('in');
        }

        $this->redirect('Manage:');
    }
}
