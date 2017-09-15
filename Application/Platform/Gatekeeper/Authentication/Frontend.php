<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Willkommen', '');
        $Stage->addButton(new Backward(true));
        $Stage->setMessage(date('d.m.Y - H:i:s'));

        $tblIdentificationSearch = Account::useService()->getIdentificationByName(TblIdentification::NAME_USER_CREDENTIAL);
        $tblAccount = Account::useService()->getAccountBySession();

        $content = false;
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
                if ($tblPerson
                    && ($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
                    && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
                ) {

                    $content = Evaluation::useService()->getTeacherWelcome($tblPerson);
                }
            }
        }

        if ($tblAccount && $tblIdentificationSearch) {
            $tblAuthentication = Account::useService()->getAuthenticationByAccount($tblAccount);
            if ($tblAuthentication && ($tblIdentification = $tblAuthentication->getTblIdentification())) {
                if ($tblIdentificationSearch->getId() == $tblIdentification->getId()) {
                    $IsEqual = false;
                    $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
                    if ($tblUserAccount) {
                        $Password = $tblUserAccount->getAccountPassword();
                        if ($tblAccount->getPassword() == $Password) {
                            $IsEqual = true;
                        }
                    }

                    if ($IsEqual) {
                        $Stage->setContent(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn('', 2),
                                        new LayoutColumn(
                                            new Center(new Panel('Warnung',
                                                new Warning('Bitte ändern Sie ihr Passwort um eine vollständige
                                            Sicherheit zu gewährleisten.')
                                                , Panel::PANEL_TYPE_DANGER,
                                                new Standard('Passwort ändern', '/Setting/MyAccount/Password'
                                                    , new Key(), array(), 'Schnellzugriff der Passwort Änderung')))
                                            , 8)
                                    ))
                                )
                            ) . ($content ? $content : '')
                        );
                        return $Stage;
                    }
                }
            }
        }

        $Stage->setContent(($content ? $content : '') . $this->getCleanLocalStorage());

        return $Stage;
    }

    /**
     * @return string
     */
    private function getCleanLocalStorage()
    {

        return '<script language=javascript>
            //noinspection JSUnresolvedFunction
            executeScript(function()
            {
                Client.Use("ModCleanStorage", function()
                {
                    jQuery().ModCleanStorage();
                });
            });
        </script>';
    }

    /**
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     *
     * @return Stage
     */
    public function frontendIdentification($CredentialName = null, $CredentialLock = null, $CredentialKey = null)
    {

        if ($CredentialName !== null) {
            Protocol::useService()->createLoginAttemptEntry($CredentialName, $CredentialLock, $CredentialKey);
        }

        $View = new Stage('Anmeldung');

        // Prepare Environment
        switch (strtolower($this->getRequest()->getHost())) {
            case 'www.schulsoftware.schule':
            case 'www.kreda.schule':
                $Environment = new Standard('Zur Demo-Umgebung wechseln', 'http://demo.schulsoftware.schule/', new Transfer(),
                    array(),
                    false);
                break;
            case 'demo.schulsoftware.schule':
            case 'demo.kreda.schule':
                $Environment = new Standard('Zur Live-Umgebung wechseln', 'http://www.schulsoftware.schule/', new Transfer(),
                    array(),
                    false);
                break;
            default:
                $Environment = new Standard('Zur Demo-Umgebung wechseln', 'http://demo.schulsoftware.schule/', new Transfer(),
                    array(),
                    false);
        }

        $View->addButton(
            $Environment
        );

        $View->setMessage('Bitte geben Sie Ihre Benutzerdaten ein');

        // Get Identification-Type (Credential,Token,System)
        $Identifier = $this->getModHex($CredentialKey)->getIdentifier();
        if ($Identifier) {
            $tblToken = Token::useService()->getTokenByIdentifier($Identifier);
            if ($tblToken) {
                if ($tblToken->getServiceTblConsumer()) {
                    $Identification = Account::useService()->getIdentificationByName('Token');
                } else {
                    $Identification = Account::useService()->getIdentificationByName('System');
                }
            } else {
                $Identification = Account::useService()->getIdentificationByName('Credential');
            }
        } else {
            $Identification = Account::useService()->getIdentificationByName('Credential');
            $tblToken = null;
        }

        if (!$Identification) {
            $Protocol = (new Database())->frontendSetup(false, true);

            $Stage = new Stage(new Danger(new Hospital()) . ' Installation', 'Erster Aufruf der Anwendung');
            $Stage->setMessage('Dieser Schritt wird automatisch ausgeführt wenn die Datenbank nicht die notwendigen Einträge aufweist. Üblicherweise beim ersten Aufruf.');
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Panel('Was ist das?', array(
                                    (new Info(new Shield() . ' Es wird eine automatische Installation der Datenbank und eine Überprüfung der Daten durchgeführt')),
                                ), Panel::PANEL_TYPE_PRIMARY,
                                    new PullRight(strip_tags((new Redirect(self::getRequest()->getPathInfo(), 110)),
                                        '<div><a><script><span>'))
                                ),
                                new Panel('Protokoll', array(
                                    $Protocol
                                ))
                            ))
                        )
                    )
                )
            );
            return $Stage;
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            new Panel('Benutzername & Passwort', array(
                                (new TextField('CredentialName', 'Benutzername', 'Benutzername', new Person()))
                                    ->setRequired(),
                                (new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
                                    ->setRequired()->setDefaultValue($CredentialLock, true)
                            ), Panel::PANEL_TYPE_INFO)
                        )
                    ),
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Hardware-Schlüssel *', array(
                                new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey())
                            ), Panel::PANEL_TYPE_INFO, new Small('* Wenn zu Ihrem Zugang ein YubiKey gehört geben Sie zuerst oben Ihren Benutzername und Passwort an, stecken Sie dann bitte den YubiKey an, klicken in das Feld YubiKey und drücken anschließend auf den metallischen Sensor am YubiKey. <br/>Das Formular wird daraufhin automatisch abgeschickt.'))
                        )
                    ))
                )
            ), new Primary('Anmelden')
        );

        // Switch Service
        if ($tblToken) {
            $FormService = Account::useService()->createSessionCredentialToken(
                $Form, $CredentialName, $CredentialLock, $CredentialKey, $Identification
            );
        } else {
            $FormService = Account::useService()->createSessionCredential(
                $Form, $CredentialName, $CredentialLock, $Identification
            );
        }

        $View->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ''
                        , 3),
                    new LayoutColumn(
                        new Well($FormService)
                        , 6),
                    new LayoutColumn(
                        ''
                        , 3),
                )),
            )))
        );
        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendDestroySession()
    {

        $View = new Stage('Abmelden', 'Bitte warten...');
        $View->setContent(Account::useService()->destroySession(
                new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_SUCCESS)
            ) . $this->getCleanLocalStorage());
        return $View;
    }
}
