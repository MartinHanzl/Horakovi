<?php

declare(strict_types=1);

namespace App\Presenters;

use http\Message;
use Nette;
use Nette\ComponentModel\IComponent;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use Nette\Mail\SendmailMailer;
use Nette\Utils\Image;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    private $database;
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    public function renderDefault() :void {
        $popis = $this->database->query("SELECT * FROM popis WHERE popis.popisID = 1");
        $this->template->popis = $popis;

        $cenik = $this->database->query("SELECT * FROM cenik");
        $this->template->cenik = $cenik;

        $galerie = $this->database->query("SELECT * FROM galerie");
        $this->template->galerie = $galerie;
    }

    public function renderForms() :void {
        $gal = $this->database->query("SELECT * FROM galerie");
        $this->template->gal = $gal;
    }

    protected function createComponentPopis() :Form {
    $form = new Form();
    $form->addTextArea('Popis_Text')
        ->setHtmlId("summernote");
    $form->addSubmit('btnEditPopis', 'Uložit popois!');
    $form->onSuccess[] = [$this, 'addFormSucceeded'];
    return $form;
}

    public function addFormSucceeded(Form $form, array $values) : void {
        $popis = $this->database->table('popis')->where('popisID', 1);
        $popis->update($values);
        $this->redirect('Homepage:default');
    }


    protected function createComponentCenik() :Form {
        $form = new Form();
        $form->addTextArea('Cenik_Text')
            ->setHtmlId("summernote");
        $form->addText("Cena");
        $form->addSubmit('btnEditCenik', 'Uložit ceník!');
        $form->onSuccess[] = [$this, 'cenikFormSucceeded'];
        return $form;
    }

    public function actionEdit($cenikID) :void {
        $post = $this->database->table("cenik")->where("cenikID", $cenikID)->fetch();
        if(!$post) {
            $this->error("Příspěvek nebyl nalezen");
        }
        $this["cenik"]->setDefaults($post->toArray());
    }

    public function cenikFormSucceeded(Form $form, array $values) : void {
        $postID = $this->getParameter("cenikID");
        if ($postID) {
            $cenik = $this->database->table("cenik")->where("cenikID", $postID);
            $cenik->update($values);
        } else {
            $popis = $this->database->table('cenik')->insert($values);
        }
        $this->redirect('Homepage:default');
    }

    protected function createComponentFoto(string $name): Form {
        $form = new Form();
        $form->addMultiUpload("fotografie")
            ->setRequired("Vybrete prosím jeden nebo více obrázků");
        $form->addSubmit("btnAddFoto", "Nahrát fotografie");
        $form->onSuccess[] = [$this, 'fotoFormSucceeded'];
        return $form;
    }

    public function fotoFormSucceeded(Form $form, \stdClass $values) : void {
        foreach ($values->fotografie as $obrazek) {
            if($obrazek->isImage() && $obrazek->isOk()) {
                $file_ext = substr(strrchr($obrazek->name,'.'),1);
                $filename = uniqid() .".". $file_ext;
                $obrazek->move("./images/" . $filename);
                $this->database->table("galerie")->insert([
                    "Nazev" => $filename,
                ]);
            }
        }
        $this->redirect('Homepage:default');
    }

    public function actionVymazFoto($fotoID) :void {
        $foto = $this->database->table("galerie")->where("fotoID", $fotoID);
        $foto->delete();
        $this->redirect("Homepage:default");
    }

    protected function createComponentEmail(): Form {
        $form = new Form();
        $form->addText("jmeno");
        $form->addEmail("email");
        $form->addText("telefon");
        $form->addTextArea("text")
            ->setHtmlId("summernote");
        $form->addSubmit("odeslat");
        $form->onSuccess[] = [$this, 'mailFormSecceeded'];
        return $form;
    }

    public function mailFormSucceeded(Form $form, \stdClass $values) : void {
        $email = "martas.hanzl@email.cz";
        $mail = new Message();
        $mail->setFrom($values->email)
            ->addTo($email)
            ->setSubject($values->telefon . ", " . $values->jmeno)
            ->setBody($values->text);
        $mailer = new SendmailMailer;
        $mailer->send($mail);
        $this->redirect("Homepage:default");
    }

    protected function createComponentLogin() :Form {
        $form = new Form();
        $form->addEmail("email")
            ->setRequired("Zadejte prosím platnou emailovou adresu!")
            ->setHtmlAttribute("placeholder", "Emailová adresa");;
        $form->addPassword("heslo")
            ->setRequired("Zadejte prosím vaše heslo")
            ->setHtmlAttribute("placeholder", "Heslo");
        $form->addSubmit('btnLogin', 'Přihlášení');
        $form->onSuccess[] = [$this, 'prihlasFormSucceeded'];
        return $form;
    }

    public function prihlasFormSucceeded(Form $form, \stdClass $values) : void {
        try {
            $this->user->login($values->email, $values->heslo);
            $this->flashMessage("Přihlášení proběhlo úspěšně", 'success');
            $this->redirect("Homepage:default");
        }catch (Nette\Security\AuthenticationException $exception){
            $form->addError($exception->getMessage());
        }
    }

    public function actionOdhlas(){
        $this->user->logout(true);
        $this->redirect("Homepage:default");
    }

    public function actionVymazCenu($cenikID) :void {
        $foto = $this->database->table("cenik")->where("cenikID", $cenikID);
        $foto->delete();
        $this->redirect("Homepage:default");
    }
}
