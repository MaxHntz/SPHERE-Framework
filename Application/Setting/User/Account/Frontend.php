<?php
namespace SPHERE\Application\Setting\User\Account;

use SPHERE\Application\Api\Contact\ApiContactAddress;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing as ListingLayout;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title as TitleLayout;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Übersicht', 'Accounts');
        $Stage->addButton(new Standard('Zurück', '/People', new ChevronLeft()));
//        $Stage->addButton(new Standard('Personenzuweisung', '/Setting/User/Account/Person', new Listing(), array()
//            , 'Auswahl der Personen'));

        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Accountnamen hinterlegt');
                $Item['UserPassword'] = '';
                $Item['Address'] = new Warning(new WarningIcon().' Keine Adresse gewählt');
                $Item['Mail'] = new Warning(new WarningIcon().' Keine E-Mail gewählt');
                $Item['PersonListCustody'] = '';
                $Item['PersonListStudent'] = '';
                $Item['Option'] =
//                    new Standard('', '/Setting/User/Account/Address/Edit', new Building(),
//                        array('Id' => $tblUserAccount->getId()), 'Adresse ändern/anlegen')
//                    .new Standard('', '/Setting/User/Account/Mail/Edit', new MailIcon(),
//                        array('Id' => $tblUserAccount->getId()), 'E-Mail ändern/anlegen')
                    new Standard('', '/Setting/User/Account/Reset', new Repeat(),
                        array('Id' => $tblUserAccount->getId())
                        , 'Passwort Zurücksetzten')
                    .new Standard('', '/Setting/User/Account/Destroy', new Remove(),
                        array('Id' => $tblUserAccount->getId()), 'Benutzer entfernen');
                $tblAccount = $tblUserAccount->getServiceTblAccount();
                if ($tblAccount) {
                    $Item['UserName'] = $tblAccount->getUsername();
                    if (hash('sha256', $tblUserAccount->getUserPassword()) != $tblAccount->getPassword()) {
                        $Item['UserPassword'] = new Success(new SuccessIcon().' PW geändert');
                    } else {
                        $Item['UserPassword'] = new Warning($tblUserAccount->getUserPassword());
                    }
                } else {
                    $Item['UserPassword'] = $tblUserAccount->getUserPassword();
                }

                $tblToPersonAddress = $tblUserAccount->getServiceTblToPersonAddress();
                if ($tblToPersonAddress) {
                    $tblAddress = $tblToPersonAddress->getTblAddress();
                    if ($tblAddress) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                }

                $tblToPersonMail = $tblUserAccount->getServiceTblToPersonMail();
                if ($tblToPersonMail) {
                    $tblMail = $tblToPersonMail->getTblMail();
                    if ($tblMail) {
                        $Item['Mail'] = $tblMail->getAddress();
                    }
                }

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $CustodyList = array();
                    $StudentList = array();
                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {

                                $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    $CustodyList[] = new Container($tblPersonCustody->getLastFirstName());
                                }
                                $tblPersonStudent = $tblRelationship->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    $StudentList[] = new Container($tblPersonStudent->getLastFirstName());
                                }
                            }
                        }
                    }
                    if (!empty($CustodyList)) {
                        $Item['PersonListCustody'] = implode($CustodyList);
                    }
                    if (!empty($StudentList)) {
                        $Item['PersonListStudent'] = implode($StudentList);
                    }

                }

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty($TableContent)
                                ?
                                new TableData($TableContent, new Title('Übersicht', 'Benutzer'),
                                    array(
                                        'Salutation'        => 'Anrede',
                                        'Name'              => 'Name',
                                        'UserName'          => 'Account',
                                        'UserPassword'      => 'Passwort',
                                        'Address'           => 'Adresse',
                                        'Mail'              => 'E-Mail',
                                        'PersonListCustody' => 'Sorgeberechtigte',
                                        'PersonListStudent' => 'Schüler',
                                        'Option'            => ''
                                    ))
                                : new WarningMessage('Keine Benutzerzugänge vorhanden.
                                Bitte klicken Sie auf &nbsp;'.new Standard('Benutzer verwalten',
                                        '/Setting/User/Account/Person')
                                    .' um neue Benutzer anzulegen')
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Year
     * @param null $Division
     *
     * @return Stage
     */
    public function frontendStudentAdd($Year = null, $Division = null)
    {

        $Stage = new Stage('Schüler-Accounts', 'Erstellen');

        $form = $this->getStudentFilterForm();

        $TableContent = $this->getStudentFilterResult($Year, $Division);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            $form
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            new TableData($TableContent, null, array(
                                                'Check'         => 'Auswahl',
                                                'Name'          => 'Name',
                                                'StudentNumber' => 'Schüler-Nr.',
                                                'Course'        => 'Schulart',
                                                'Division'      => 'Klasse',
                                                'Address'       => 'Adresse',
                                            ), null)
                                        )
                                    )
                                )
                            )
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function getStudentFilterForm()
    {
        $tblLevelShowList = array();

        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as &$tblLevel) {
                if (!$tblLevel->getName()) {
                    $tblLevelClone = clone $tblLevel;
                    $tblLevelClone->setName('Stufenübergreifende Klassen');
                    $tblLevelShowList[] = $tblLevelClone;
                } else {
                    $tblLevelShowList[] = $tblLevel;
                }
            }
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                            new Panel('Bildung: Schuljahr', array(
                                (new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Schuljahr',
                                    array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                    ->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                            )
                        )
                        , 3),
                    new FormColumn(
                        new Panel('Bildung: Schulart', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Klasse: Stufe', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Stufe',
                                array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList))
                        ), Panel::PANEL_TYPE_INFO
                        ), 3),
                    new FormColumn(
                        new Panel('Klasse: Gruppe', array(
                            new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Gruppe',
                                'Klasse: Gruppe',
                                array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO
                        ), 3),
                )),
                new FormRow(
                    new FormColumn(
                        new Danger('*'.new Small('Pflichtfeld'))
                    )
                )
            ))
            , new Primary('Filtern')
        );
    }

    /**
     * @param $Year
     * @param $Division
     *
     * @return array
     */
    private function getStudentFilterResult($Year, $Division)
    {

        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = '';

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            // Preparation Filter
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Year = array_filter($Year);
//            // Preparation FilterPerson
//            $Filter['Person'] = array();

            // Preparation $FilterType
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $Division = array_filter($Division);
            } else {
                $Division = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => array(),   // empty Person search
                2 => $Division,
                3 => $Year
            ));
        }

        $SearchResult = array();
        if ($Result != '') {
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Check'] = '';
                $DataPerson['Course'] = '';
                $DataPerson['Course'] = '';

                $Button = (new Standard('', ApiContactAddress::getEndpoint(), new Edit(), array(),
                    'Bearbeiten der Hauptadresse'))
                    ->ajaxPipelineOnClick(ApiContactAddress::pipelineOpen($tblPerson->getId()));

                $AddressReceiver = new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                ApiContactAddress::receiverColumn($tblPerson->getId())
                                , 11),
                            new LayoutColumn(
                                new Center($Button).ApiContactAddress::receiverModal($tblPerson->getId())
                                , 1),
                        ))
                    )
                );
//                $AddressReceiver = ApiContactAddress::receiverColumn($tblPerson->getId()); // . new PullRight($Button);


                $DataPerson['Address'] = $AddressReceiver;

                if ($tblPerson) {
                    $DataPerson['Check'] = (new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ',
                        $tblPerson->getId()
                        , array($tblPerson->getId())))->setChecked();
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
//                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        if ($tblTransferType) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer && ($tblCourse = $tblStudentTransfer->getServiceTblCourse())) {
                                $DataPerson['Course'] = $tblCourse->getName();
                            }
                        }
                    }
                }
                $DataPerson['Division'] = '';
                $DataPerson['Level'] = '';

                $tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']);
                if ($tblDivision) {
                    $DataPerson['Division'] = $tblDivision->getDisplayName();
                }

//                /** @noinspection PhpUndefinedFieldInspection */
//                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
//                    /** @noinspection PhpUndefinedFieldInspection */
//                    $DataPerson['Address'] = $AddressReceiver;
//                }
                $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
                if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
                }

                if (!isset($DataPerson['ProspectYear'])) {
                    $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
                }
                if (!isset($DataPerson['ProspectDivision'])) {
                    $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
                }

                // ignor existing Accounts (By Person)
                if (!Account::useService()->getUserAccountByPerson($tblPerson)) {
                    // ignore duplicated Person
                    if ($DataPerson['Name']) {
                        if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                            $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                        }
                    }
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @return Stage
     */
    public function frontendAddress()
    {

        $Stage = new Stage('Benutzer', 'Übersicht der Adressen');
        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));
        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Account hinterlegt');
                $Item['Address'] = new Warning(new WarningIcon().' Keine Adresse gewählt');
                $Item['IsAddress'] = '';
                $Item['Option'] = new Standard('', '/Setting/User/Account/Address/Edit', new Building(),
                    array('Id' => $tblUserAccount->getId()), 'Adresse ändern/anlegen');
                $tblAccount = $tblUserAccount->getServiceTblAccount();
                if ($tblAccount) {
                    $Item['UserName'] = $tblAccount->getUsername();
                }
                $IsAddress = false;
                $tblToPersonAddress = $tblUserAccount->getServiceTblToPersonAddress();
                if ($tblToPersonAddress) {
                    $tblAddress = $tblToPersonAddress->getTblAddress();
                    if ($tblAddress) {
                        $IsAddress = true;
                        $Item['Address'] = $tblAddress->getGuiTwoRowString();
                        // show send status
                        if ($tblUserAccount->getIsExport()) {
                            $Item['IsAddress'] = ''; //new Center(new Warning(new Disable()));
                            $Item['Option'] .= new Standard('', '/Setting/User/Account/Address/IsExport',
                                new SuccessIcon(),
                                array('Id' => $tblUserAccount->getId(), 'IsExport' => false), 'Adresse benutzen');
                        } else {
                            // extra layout for sorting
                            $Item['IsAddress'] = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                new Center(new Success(new SuccessIcon()))
                            ))));
                            $Item['Option'] .= new Standard('', '/Setting/User/Account/Address/IsExport', new Disable(),
                                array('Id' => $tblUserAccount->getId(), 'IsExport' => true), 'Adresse nicht benutzen');
                        }
                    }
                }
                if (!$IsAddress) {
                    $Item['IsAddress'] = new Center(new Warning(new WarningIcon().new Container('Keine Adresse')));
                }

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();
                    if ($tblToPersonAddress) {
//                        $tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson);
//                        $AddressCount = count($tblAddressList);
//                        if ($AddressCount > 1) {
//                            $Item['Address'] .= new Container(new Warning('Es stehen ('.$AddressCount.') Adressen zur Auswahl'));
//                        }
                    } else {
//                        $tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson);
//                        $AddressCount = count($tblAddressList);
//                        if ($AddressCount == 1) {
//                            $Item['Address'] .= new Container(new Warning('Es steht eine Adresse zur Auswahl'));
//                        } elseif($AddressCount > 1){
//                            $Item['Address'] .= new Container(new Warning('Es stehen ('.$AddressCount.') Adressen zur Auswahl'));
//                        }
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            (!empty($TableContent)
                                ?
                                new TableData($TableContent, new Title('Übersicht',
                                    'der zu exportierenden Adressen für Benutzer'),
                                    array(
                                        'Salutation' => 'Anrede',
                                        'Name'       => 'Name',
                                        'UserName'   => 'Account',
                                        'Address'    => 'Adresse',
                                        'IsAddress'  => 'Wird exportiert',
                                        'Option'     => ''
                                    ), array(
                                        'order'      => array(array(4, 'desc'), array(1, 'asc')),
                                        'columnDefs' => array(
                                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
                                            array('width' => '95px', 'targets' => -2)
                                        )
                                    ))
                                : new WarningMessage('Keine Benutzerzugänge vorhanden.
                                Bitte klicken Sie auf &nbsp;'.new Standard('Benutzer verwalten',
                                        '/Setting/User/Account/Person')
                                    .' um neue Benutzer anzulegen')
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Street
     * @param null $City
     * @param null $State
     * @param null $Type
     * @param null $County
     * @param null $Nation
     *
     * @return Stage
     */
    public function frontendAddressEdit(
        $Id = null,
        $Street = null,
        $City = null,
        $State = null,
        $Type = null,
        $County = null,
        $Nation = null
    ) {

        $Stage = new Stage('Adresse', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Setting/User/Account/Address', new ChevronLeft()));
        $tblUserAccount = ( $Id === null ? false : Account::useService()->getUserAccountById($Id) );
        if (!$tblUserAccount) {
            $Stage->setContent(new WarningMessage('Acountzuweisung nicht gefunden')
                .new Redirect('/Setting/User/Account/Address', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }
        $tblPerson = $tblUserAccount->getServiceTblPerson();
        if (!$tblPerson) {
            $Stage->setContent(new WarningMessage('Person nicht gefunden')
                .new Redirect('/Setting/User/Account/Address', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }

        $tblToPersonAddress = $tblUserAccount->getServiceTblToPersonAddress();
        $tblAddress = null;
        $AddressString = '';
        $ActiveType = 'Keine Adresse';
        if ($tblToPersonAddress) {
            $tblType = $tblToPersonAddress->getTblType();
            if ($tblType) {
                $ActiveType = $tblType->getName();
            }
            $tblAddress = $tblToPersonAddress->getTblAddress();
            if ($tblAddress) {
                $AddressString = $tblAddress->getGuiTwoRowString();
            }
        }

        $LayoutAddress = $this->layoutPanelAddress($tblUserAccount, $tblPerson, $tblAddress);
        $formAddress = Address::useFrontend()->formAddress();

        // upper panels
        $personPanel = new Panel('Person', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS);
        if (($tblAccount = $tblUserAccount->getServiceTblAccount())) {
            $userName = $tblAccount->getUsername();
        } else {
            $userName = '';
        }
        $UserNamePanel = new Panel('Benutzername', $userName, Panel::PANEL_TYPE_SUCCESS);
        $activeAddressPanel = new Panel('Ausgwählte Adresse ('.$ActiveType.')', $AddressString,
            Panel::PANEL_TYPE_SUCCESS);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $personPanel, 3
                        ),
                        new LayoutColumn(
                            $UserNamePanel, 3
                        ),
                        new LayoutColumn(
                            $activeAddressPanel, 3
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TitleLayout(new Check().' Auswahl einer verfügbaren Adresse'),
                            new Well(
                                $LayoutAddress
                            )
                        )),
                    )),
                    new LayoutRow(
                        new LayoutColumn(array(
                            new TitleLayout(new Plus().' Hinzufügen einer neuen Adresse'),
                            new Well(Address::useService()->createAddressToPersonByRoute(
                                $formAddress
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblPerson, $Street, $City, $State, $Type, $County, $Nation,
                                '/Setting/User/Account/Address/Edit',
                                array('Id' => $tblUserAccount->getId())
                            ))
                        ))
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param TblUserAccount  $tblUserAccount
     * @param TblPerson       $tblPerson
     * @param TblAddress|null $tblActiveAddress
     *
     * @return Layout
     */
    public function layoutPanelAddress(TblUserAccount $tblUserAccount, TblPerson $tblPerson, TblAddress $tblActiveAddress = null)
    {
        $LayoutColumnList = array();
        $tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson);
        if ($tblToPersonAddressList) {
            foreach ($tblToPersonAddressList as $tblToAddress) {
                // get typeName for Panel title
                $TypeString = '';
                if (( $tblType = $tblToAddress->getTblType() )) {
                    $TypeString = $tblType->getName();
                    // get bold front for MainAddress
                    if ($TypeString == 'Hauptadresse') {
                        $TypeString = new Bold($TypeString);
                    }
                }
                // get Address for Panel content
                $tblAddress = $tblToAddress->getTblAddress();
                if ($tblAddress) {
                    // set LayoutColumn
                    $LayoutColumnList[] = new LayoutColumn(
                        new Panel($TypeString, $tblAddress->getGuiTwoRowString(),
                            ($tblActiveAddress
                                ? ($tblAddress->getId() == $tblActiveAddress
                                    ? Panel::PANEL_TYPE_SUCCESS
                                    : Panel::PANEL_TYPE_INFO)
                                : Panel::PANEL_TYPE_INFO),
                            new Standard('', '/Setting/User/Account/Address/Select', new Ok(),
                                array('Id'         => $tblUserAccount->getId(),
                                      'toPersonId' => $tblToAddress->getId()), 'Adresse auswählen'
                            )
                        )
                        , 3);
                }
            }
        }

        // build clean view
        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $LayoutColumn
         */
        if (empty($LayoutColumnList)) {
            $LayoutColumnList[] = new LAyoutColumn(
                new WarningMessage('Die Person "'.$tblPerson->getFullName().'" besitzt keine Adresse!')
                , 6);
        }
        foreach ($LayoutColumnList as $LayoutColumn) {
            // new line after 4 Columns
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($LayoutColumn);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param null $Id
     * @param null $toPersonId
     *
     * @return Stage
     */
    public function frontendAddressSelect($Id = null, $toPersonId = null)
    {
        $Stage = new Stage('Adresse'.'Zuweisen');
        // check to continue
        $tblUserAccount = ( $Id === null ? false : Account::useService()->getUserAccountById($Id) );
        if (!$tblUserAccount) {
            $Stage->setContent(new WarningMessage('Acountzuweisung nicht gefunden')
                .new Redirect('/Setting/User/Account/Address', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }
        $tblToPersonAddress = ( $toPersonId === null ? false : Address::useService()->getAddressToPersonById($toPersonId) );
        if (!$tblToPersonAddress) {
            $Stage->setContent(new WarningMessage('Acountzuweisung nicht gefunden')
                .new Redirect('/Setting/User/Account/Address/Edit', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblUserAccount->getId()))
            );
            return $Stage;
        }

        // update TblToPersonAddress for TblUserAccount
        if (Account::useService()->updateUserAccountByToPersonAddress($tblUserAccount, $tblToPersonAddress)) {
            // success
            $Stage->setContent(new SuccessMessage('Adresse erfolgreich übernommen')
                .new Redirect('/Setting/User/Account/Address', Redirect::TIMEOUT_SUCCESS));
        } else {
            // error
            $Stage->setContent(new WarningMessage('Adresse konnte nicht übernommen werden')
                .new Redirect('/Setting/User/Account/Address/Edit', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblUserAccount->getId())));
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $IsExport
     *
     * @return Stage
     */
    public function frontendSendAddressIsExport($Id = null, $IsExport = false)
    {
        $Stage = new Stage('Adresse', 'Exporteinstellung');
        $tblUserAccount = ($Id == null ? false : Account::useService()->getUserAccountById($Id));
        if (!$tblUserAccount) {
            return $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                new Warning('Acountzuweisung nicht gefunden.'),
                new Redirect('/Setting/User/Account/Address', Redirect::TIMEOUT_ERROR)
            ))))));
        }
        if ($IsExport) {
            $Success = new SuccessMessage('Adresse wird nicht mehr für den Export verwendet');
        } else {
            $Success = new SuccessMessage('Adresse wird für den Export verwendet');
        }
        $Error = new WarningMessage('Einstellung konnte nicht vorgenommen werden');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            (Account::useService()->updateUserAccountByIsExport($tblUserAccount, $IsExport)
                                ? $Success.new Redirect('/Setting/User/Account/Address', Redirect::TIMEOUT_SUCCESS)
                                : $Error.new Redirect('/Setting/User/Account/Address', Redirect::TIMEOUT_ERROR))
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendMail()
    {

        $Stage = new Stage('Benutzer', 'Übersicht der E-Mails');
        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));
        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Account hinterlegt');
                $Item['Mail'] = new Warning(new WarningIcon().' Keine E-Mail gewählt');
                $Item['IsMail'] = '';
                $Item['Option'] = new Standard('', '/Setting/User/Account/Mail/Edit', new MailIcon(),
                    array('Id' => $tblUserAccount->getId()), 'E-Mail ändern/anlegen');
                $tblAccount = $tblUserAccount->getServiceTblAccount();
                if ($tblAccount) {
                    $Item['UserName'] = $tblAccount->getUsername();
                }

                $IsMail = false;
                $tblToPersonMail = $tblUserAccount->getServiceTblToPersonMail();
                if ($tblToPersonMail) {
                    $tblMail = $tblToPersonMail->getTblMail();
                    if ($tblMail) {
                        $IsMail = true;
                        $Item['Mail'] = $tblMail->getAddress();
                        // show send status
                        if ($tblUserAccount->getIsSend()) {
//                            $Item['IsMail'] = new Center(new Warning(new Disable()));
                            $Item['Option'] .= new Standard('', '/Setting/User/Account/Mail/IsSend', new SuccessIcon(),
                                array('Id' => $tblUserAccount->getId(), 'IsSend' => false), 'E-Mail verschicken');
                        } else {
                            $Item['IsMail'] = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                new Center(new Success(new SuccessIcon()))
                            ))));
                            $Item['Option'] .= new Standard('', '/Setting/User/Account/Mail/IsSend', new Disable(),
                                array('Id' => $tblUserAccount->getId(), 'IsSend' => true), 'keine E-Mail verschicken');
                        }
                    }
                }
                if (!$IsMail) {
                    $Item['IsMail'] = new Center(new Warning(new WarningIcon().new Container('Keine E-Mail')));
                }

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    if ($tblToPersonMail) {
                        $tblMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                        $MailCount = count($tblMailList);
                        if ($MailCount > 1) {
                            $Item['Mail'] .= new Container(new Warning('Es stehen ('.$MailCount.') E-Mails zur Auswahl'));
                        }
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            (!empty($TableContent)
                                ?
                                new TableData($TableContent, new Title('Übersicht',
                                    'der zu sendenden E-Mails für Benutzer'),
                                    array(
                                        'Salutation' => 'Anrede',
                                        'Name'       => 'Name',
                                        'UserName'   => 'Account',
                                        'Mail'       => 'E-Mail',
                                        'IsMail'     => 'Wird versendet',
                                        'Option'     => ''
                                    ), array(
                                        'order'      => array(array(4, 'desc'), array(1, 'asc')),
                                        'columnDefs' => array(
                                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
                                            array('width' => '95px', 'targets' => -2)
                                        )
                                    ))
                                : new WarningMessage('Keine Benutzerzugänge vorhanden.
                                Bitte klicken Sie auf &nbsp;'.new Standard('Benutzer verwalten',
                                        '/Setting/User/Account/Person')
                                    .' um neue Benutzer anzulegen')
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Address
     * @param null $Type
     *
     * @return Stage
     */
    public function frontendMailEdit(
        $Id = null,
        $Address = null,
        $Type = null
    ) {

        $Stage = new Stage('E-Mail', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));
        $tblUserAccount = ( $Id === null ? false : Account::useService()->getUserAccountById($Id) );
        if (!$tblUserAccount) {
            $Stage->setContent(new WarningMessage('Acountzuweisung nicht gefunden')
                .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }
        $tblPerson = $tblUserAccount->getServiceTblPerson();
        if (!$tblPerson) {
            $Stage->setContent(new WarningMessage('Person nicht gefunden')
                .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }
        $tblToPersonMail = $tblUserAccount->getServiceTblToPersonMail();

        $tblMail = null;
        $MailString = '';
        $ActiveType = 'Keine Adresse';
        if ($tblToPersonMail) {
            $tblType = $tblToPersonMail->getTblType();
            if ($tblType) {
                $ActiveType = $tblType->getName();
            }
            $tblMail = $tblToPersonMail->getTblMail();
            if ($tblMail) {
                $MailString = $tblMail->getAddress();
            }
        }

        $LayoutMail = $this->layoutPanelMail($tblUserAccount, $tblPerson, $tblMail);
        $formAddress = Mail::useFrontend()->formAddress();

        // upper panels
        $personPanel = new Panel('Person', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS);
        if (($tblAccount = $tblUserAccount->getServiceTblAccount())) {
            $userName = $tblAccount->getUsername();
        } else {
            $userName = '';
        }
        $UserNamePanel = new Panel('Benutzername', $userName, Panel::PANEL_TYPE_SUCCESS);
        $chosenAddressPanel = new Panel('Ausgwählte Adresse ('.$ActiveType.')', $MailString, Panel::PANEL_TYPE_SUCCESS);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $personPanel, 3
                        ),
                        new LayoutColumn(
                            $UserNamePanel, 3
                        ),
                        new LayoutColumn(
                            $chosenAddressPanel, 3
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TitleLayout(new Check().' Auswahl einer verfügbaren E-Mail Adresse'),
                            new Well(
                                $LayoutMail
                            )
                        )),
                    )),
                    new LayoutRow(
                        new LayoutColumn(array(
                            new TitleLayout(new Plus().' Hinzufügen einer neuen E-Mail Adresse'),
                            new Well(Mail::useService()->createMailToPersonByRoute(
                                $formAddress
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblPerson, $Address, $Type, '/Setting/User/Account/Mail/Edit',
                                array('Id' => $tblUserAccount->getId())
                            ))
                        ))
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param TblUserAccount $tblUserAccount
     * @param TblPerson      $tblPerson
     * @param TblMail|null   $tblMailActive
     *
     * @return Layout
     */
    public function layoutPanelMail(TblUserAccount $tblUserAccount, TblPerson $tblPerson, TblMail $tblMailActive = null)
    {
        $LayoutColumnList = array();
        $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPerson);

        if ($tblToPersonMailList) {
            foreach ($tblToPersonMailList as $tblToMail) {

                $activateButton = new Standard('', '/Setting/User/Account/Mail/Select', new Ok(),
                    array(
                        'Id'         => $tblUserAccount->getId(),
                        'toPersonId' => $tblToMail->getId()
                    ), 'E-Mail Adresse auswählen');

                // get typeName for Panel title
                $TypeString = '';
                if (( $tblType = $tblToMail->getTblType() )) {
                    $TypeString = $tblType->getName();
                }
                // get Mail for Panel content
                $tblMail = $tblToMail->getTblMail();
                if ($tblMail) {

                    // set LayoutColumn
                    $LayoutColumnList[] = new LayoutColumn(
                        new Panel($TypeString, $tblMail->getAddress(),
                            ($tblMailActive
                                ? ($tblMail->getId() == $tblMailActive->getId()
                                    ? Panel::PANEL_TYPE_SUCCESS
                                    : Panel::PANEL_TYPE_INFO)
                                : Panel::PANEL_TYPE_INFO),
                            ($tblMailActive
                                ? ($tblMail->getId() == $tblMailActive->getId()
                                    ? ''
                                    : $activateButton)
                                : $activateButton)
                        )
                        , 3);
                }
            }
        }

        // build clean view
        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $LayoutColumn
         */
        if (empty($LayoutColumnList)) {
            $LayoutColumnList[] = new LAyoutColumn(
                new WarningMessage('Die Person "'.$tblPerson->getFullName().'" besitzt keine E-Mail Adresse!')
                , 6);
        }
        foreach ($LayoutColumnList as $LayoutColumn) {
            // new line after 4 Columns
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($LayoutColumn);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param null $Id
     * @param null $toPersonId
     *
     * @return Stage
     */
    public function frontendMailSelect($Id = null, $toPersonId = null)
    {
        $Stage = new Stage('Adresse'.'Zuweisen');
        // check to continue
        $tblUserAccount = ( $Id === null ? false : Account::useService()->getUserAccountById($Id) );
        if (!$tblUserAccount) {
            $Stage->setContent(new WarningMessage('Acountzuweisung nicht gefunden')
                .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }
        $tblToPersonMail = ( $toPersonId === null ? false : Mail::useService()->getMailToPersonById($toPersonId) );
        if (!$tblToPersonMail) {
            $Stage->setContent(new WarningMessage('Acountzuweisung nicht gefunden')
                .new Redirect('/Setting/User/Account/Mail/Edit', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblUserAccount->getId()))
            );
            return $Stage;
        }

        // update TblToPersonMail for TblUserAccount
        if (Account::useService()->updateUserAccountByToPersonMail($tblUserAccount, $tblToPersonMail)) {
            // success
            $Stage->setContent(new SuccessMessage('E-Mail Adresse erfolgreich übernommen')
                .new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS));
        } else {
            // error
            $Stage->setContent(new WarningMessage('E-Mail Adresse konnte nicht übernommen werden')
                .new Redirect('/Setting/User/Account/Mail/Edit', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblUserAccount->getId())));
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $IsSend
     *
     * @return Stage
     */
    public function frontendMailIsSend($Id = null, $IsSend = false)
    {
        $Stage = new Stage('E-Mail', 'Sende Einstellung');
        $tblUserAccount = ($Id == null ? false : Account::useService()->getUserAccountById($Id));
        if (!$tblUserAccount) {
            return $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                new Warning('Acountzuweisung nicht gefunden.'),
                new Redirect('/Setting/User/Account/Mail', Redirect::TIMEOUT_ERROR)
            ))))));
        }
        if ($IsSend) {
            $Success = new SuccessMessage('E-Mail wird nicht mehr verschickt');
        } else {
            $Success = new SuccessMessage('E-Mail wird verschickt');
        }
        $Error = new WarningMessage('Einstellung konnte nicht vorgenommen werden');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            (Account::useService()->updateUserAccountByIsSend($tblUserAccount, $IsSend)
                                ? $Success.new Redirect('/Setting/User/Account/Mail', Redirect::TIMEOUT_SUCCESS)
                                : $Error.new Redirect('/Setting/User/Account/Mail', Redirect::TIMEOUT_ERROR))
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null   $FilterGroup
     * @param null   $FilterStudent
     * @param null   $FilterPerson
     * @param null   $FilterYear
     * @param string $TabActive
     *
     * @return Stage
     */
    public function frontendPreparePersonList(
        $FilterGroup = null,
        $FilterStudent = null,
        $FilterPerson = null,
        $FilterYear = null,
        $TabActive = 'STUDENTFILTER'
    ) {
        $Stage = new Stage('Personen', 'Zuweisung');
        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));
        $IsSend = $IsExport = false;
        $tblUserAccountList = Account::useService()->getUserAccountByIsSendAndIsExport($IsSend, $IsExport);
        $Global = $this->getGlobal();
        $IsPost = false;
        if (!isset($Global->POST['Button'])) {
            // set Year
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $Global->POST['FilterYear']['TblYear_Id'] = $tblYear->getId();
                }
            }
            $Global->savePost();
        } else {
            $IsPost = true;
        }

        // create Tabs
        $LayoutTabs[] = new LayoutTab('Schülerbezogener Filter', 'STUDENTFILTER');
        if (!empty($LayoutTabs) && $TabActive === 'STUDENTFILTER') {
            $LayoutTabs[0]->setActive();
        }
        $LayoutTabs[] = new LayoutTab('Personenbezogener Filter', 'PERSONFILTER');

        $InfoMessage[] = new InfoMessage('Hinzufügen aller Personen die ein Benutzerzugang erhalten sollen.');
        $Timeout = false;
        $SearchTable = false;
        $IsCustody = false;
        $FormFilter = '';
        switch ($TabActive) {
            case 'STUDENTFILTER':
                $InfoMessage[] = new InfoMessage('Diese Filterung basiert auf den Schüler.'
                    .new Container('Dadurch können Schüler oder Sorgeberechtigte nach Schülerkriterien
                                    gesucht werden (Schuljahr/Klassen).')
                    .new Container('Ob Sorgeberechtigte oder Schüler dargestellt werden bestimmt man mit der
                                    Auswahl der '.new Bold('"Gruppe"').'.'));

                $FormFilter = $this->formFilterStudent();
                $FormFilter
                    ->appendFormButton(new Primary('Filtern', new Filter()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                if (isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] != 0) {
                    $Result = Account::useService()->getStudentFilterResultList($FilterGroup, $FilterStudent,
                        $FilterYear, $Timeout);
                    if ($Result) {
                        $tblGroup = Group::useService()->getGroupById($FilterGroup['TblGroup_Id']);
                        if ($tblGroup && $tblGroup->getMetaTable() == 'CUSTODY') {
                            $IsCustody = true;
                            $SearchTable = $this->getStudentTableByResult($Result, $IsCustody);
                        } else {
                            $SearchTable = $this->getStudentTableByResult($Result);
                        }
                    }
                }
                break;
            case 'PERSONFILTER':
                $FormFilter = $this->formFilterPerson();
                $FormFilter
                    ->appendFormButton(new Primary('Filtern', new Filter()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                if (isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] != 0) {
                    $Result = Account::useService()->getPersonFilterResultList($FilterGroup, $FilterPerson, $Timeout);
                    if ($Result) {
                        $SearchTable = $this->getPersonTableByResult($Result);
                    }
                }
                break;
        }

        $TableLeftContent = array();
        if ($tblUserAccountList) {
            array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$TableLeftContent) {

                $Item['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                    'Id' => $tblUserAccount->getId()
                ));
                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['Address'] = new WarningMessage('Keine Adresse gewählt');
                $Item['Year'] = '';
                $Item['Division'] = '';
                $Item['PersonListCustody'] = '';
                $Item['PersonListStudent'] = '';

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {

                    $Item['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                        'Id'       => $tblUserAccount->getId(),
                        'PersonId' => $tblPerson->getId()
                    ));

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationshipList) {
                        $CustodyList = array();
                        $StudentList = array();
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {

                                $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    $CustodyList[] = new Container($tblPersonCustody->getLastFirstName());
                                    continue;
                                }
                                $tblPersonStudent = $tblRelationship->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    $StudentList[] = new Container($tblPersonStudent->getLastFirstName());
                                    continue;
                                }
                            }
                        }
                        $Item['PersonListCustody'] = implode($CustodyList);
                        $Item['PersonListStudent'] = implode($StudentList);
                    }
                }
                $tblToPersonAddress = $tblUserAccount->getServiceTblToPersonAddress();
                if ($tblToPersonAddress) {
                    $tblAddress = $tblToPersonAddress->getTblAddress();
                    if ($tblAddress) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                }

                if (!$tblUserAccount->getIsExport() || $tblUserAccount->getIsSend()) {
                    array_push($TableLeftContent, $Item);
                }
            });
        }

        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        // remove existing Person
        /** @var TblUserAccount[] $tblUserAccountList */
        if ($tblUserAccountAll && $SearchTable) {
            $tblPersonList = array();
            foreach ($tblUserAccountAll as $tblUserAccount) {
                $tblPerson = $tblUserAccount->getServiceTblPerson();
                $tblPersonList[] = $tblPerson;
            }

            $tblPersonIdList = array();
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$tblPersonIdList) {
                if (!in_array($tblPerson->getId(), $tblPersonIdList)) {
                    array_push($tblPersonIdList, $tblPerson->getId());
                }
            });

            array_filter($SearchTable, function (&$Item) use ($tblPersonIdList) {
                if (in_array($Item['TblPerson_Id'], $tblPersonIdList)) {
                    $Item = false;
                }
            });

            $SearchTable = array_filter($SearchTable);
        }

        $InfoPanel = new Panel('Info',
            $InfoMessage,
            Panel::PANEL_TYPE_SUCCESS);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $InfoPanel
                            , 6),
                        new LayoutColumn(array(
                            new LayoutTabs($LayoutTabs),
                            new Well(new Panel('Filter'
                                , $FormFilter
                                , Panel::PANEL_TYPE_INFO))
                        ), 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn('', 6),
                        new LayoutColumn(
                            ( $Timeout === true
                                ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                : ''
                            ).
                            ( !$IsPost
                                ? new WarningMessage('Inhalt lädt nach der Filterung')
                                : ''
                            )
                            , 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new TableData($TableLeftContent,
                                    new Title('Personen für die Accounts erstellt werden sollen'),
                                    array(
                                        'Exchange'          => '',
                                        'Salutation'        => 'Anrede',
                                        'Name'              => 'Name',
                                        'Address'           => 'Adresse',
                                        'PersonListCustody' => 'Sorgeberechtigte',
                                        'PersonListStudent' => 'Schüler'
                                    ), array(
                                        'order'                => array(array(2, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                        ),
                                        'ExtensionRowExchange' => array(
                                            'Enabled' => true,
                                            'Url'     => '/Api/Setting/UserAccount/Exchange',
                                            'Handler' => array(
                                                'From' => 'glyphicon-minus-sign',
                                                'To'   => 'glyphicon-plus-sign',
                                                'All'  => 'TableRemoveAll'
                                            ),
                                            'Connect' => array(
                                                'From' => 'TableCurrent',
                                                'To'   => 'TableAvailable',
                                            )
                                        )
                                    )
                                )
                            , new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll')
                            )
                            , 6),
                        new LayoutColumn(
                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(
                                        new LayoutColumn(
                                            array(
                                                new TableData($SearchTable, new Title(( $IsCustody
                                                    ? 'Sorgeberechtigte zu den gefilterten Schülern'
                                                    : 'Gefilterte Schüler' )),
                                                    array('Exchange'     => '',
                                                          'Salutation'        => 'Anrede',
                                                          'Name'              => 'Name',
                                                          'Address'           => 'Adresse',
                                                          'PersonListCustody' => 'Sorgeberechtigte',
                                                          'PersonListStudent' => 'Schüler '
                                                    ),
                                                    array(
                                                        'order'                => array(array(2, 'asc')),
                                                        'columnDefs'           => array(
                                                            array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                                        ),
                                                        'ExtensionRowExchange' => array(
                                                            'Enabled' => true,
                                                            'Url'     => '/Api/Setting/UserAccount/Exchange',
                                                            'Handler' => array(
                                                                'From' => 'glyphicon-plus-sign',
                                                                'To'   => 'glyphicon-minus-sign',
                                                                'All'  => 'TableAddAll'
                                                            ),
                                                            'Connect' => array(
                                                                'From' => 'TableAvailable',
                                                                'To'   => 'TableCurrent',
                                                            ),
                                                        )
                                                    ))
                                            , new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                                            )
                                        )
                                    )
                                ))
                            )
                            , 6)
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param array $Result
     * @param bool  $IsCustody
     *
     * @return array|bool
     */
    private function getStudentTableByResult($Result, $IsCustody = false)
    {

        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewDivisionStudent[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();
                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();

                $DataPerson['DivisionYear'] = new Container(new Small(new Muted('Gefiltertes Jahr:'))).new Container('-NA-');
                $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                /** @var TblDivisionStudent $tblDivisionStudent */
                if ($tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        if (( $tblYear = $tblDivision->getServiceTblYear() )) {
                            $DataPerson['DivisionYear'] = new Container(new Small(new Muted('Gefiltertes Jahr:'))).new Container($tblYear->getName());
                        }
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($tblDivision->getDisplayName());
                    }
                }

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);

                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));
                $DataPerson['PersonListCustody'] = '';
                $DataPerson['PersonListStudent'] = '';

                $CustodyList = array();
                $StudentList = array();

                if ($tblPerson) {
                    if ($IsCustody) {
                        // Add Custody to List
                        $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                        if ($tblToPersonList) {
                            foreach ($tblToPersonList as $tblToPerson) {
                                if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt') {
                                    $tblPersonCustody = $tblToPerson->getServiceTblPersonFrom();
                                    if ($tblPersonCustody) {
                                        // no result for person with existing account
                                        if (AccountAuthorization::useService()->getAccountAllByPerson($tblPersonCustody)) {
                                            continue;
                                        }
                                        /** @noinspection PhpUndefinedFieldInspection */
                                        $DataPerson['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                                            'PersonId' => $tblPersonCustody->getId()
                                        ));

                                        $DataPerson['Name'] = $tblPersonCustody->getLastFirstName();
                                        $DataPerson['Salutation'] = ( $tblPersonCustody->getSalutation() !== ''
                                            ? $tblPersonCustody->getSalutation()
                                            : new Small(new Muted('-NA-')) );
                                        $tblAddress = Address::useService()->getAddressByPerson($tblPersonCustody);

                                        /** @noinspection PhpUndefinedFieldInspection */
                                        $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                                        if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                                            /** @noinspection PhpUndefinedFieldInspection */
                                            $DataPerson['Address'] = $tblAddress->getGuiString();
                                        }
                                        // show student
                                        $DataPerson['PersonListStudent'] = new ListingLayout(array(
                                            new Muted(new Small('Gefiltert durch:')).
                                            new Container($tblPerson->getLastFirstName())
                                        ));

                                        // reset PersonId for Custody
                                        $DataPerson['TblPerson_Id'] = false;
                                        if (isset($tblPersonCustody)) {
                                            $DataPerson['TblPerson_Id'] = $tblPersonCustody->getId();
                                        }

                                        // ignore duplicated Person
                                        if ($DataPerson['TblPerson_Id']) {
                                            if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                                                $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        continue;
                    } else {

                        /** @noinspection PhpUndefinedFieldInspection */
                        $DataPerson['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                            'PersonId' => $tblPerson->getId()
                        ));

                        $DataPerson['Name'] = $tblPerson->getLastFirstName();
                        $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                            ? $tblPerson->getSalutation()
                            : new Small(new Muted('-NA-')) );
                        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    }

                    // show custody
                    $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblToPersonList) {
                        foreach ($tblToPersonList as $tblToPerson) {
                            if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt') {
                                $tblPersonCustody = $tblToPerson->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    $CustodyList[] = $tblPersonCustody->getLastFirstName();
                                }

                                $tblPersonStudent = $tblToPerson->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    $StudentList[] = $tblPersonStudent->getLastFirstName();
                                }
                            }
                        }
                    }
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                    if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                        /** @noinspection PhpUndefinedFieldInspection */
                        $DataPerson['Address'] = $tblAddress->getGuiString();
                    }
                    $DataPerson['PersonListCustody'] = implode('<br/>', $CustodyList);
                    $DataPerson['PersonListStudent'] = implode('<br/>', $StudentList);

                    // ignore duplicated Person
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }

        return (!empty($TableSearch) ? $TableSearch : false);
    }

    /**
     * @param array $Result
     *
     * @return array|bool
     */
    private function getPersonTableByResult($Result)
    {

        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewDivisionStudent[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);

                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));
                $DataPerson['PersonListCustody'] = '';
                $DataPerson['PersonListStudent'] = '';
                $CustodyList = array();
                $StudentList = array();

                if ($tblPerson) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                        'PersonId' => $tblPerson->getId()
                    ));

                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ($tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')));
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);

                    // show custody
                    $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblToPersonList) {
                        foreach ($tblToPersonList as $tblToPerson) {
                            if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt') {
                                $tblPersonCustody = $tblToPerson->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    $CustodyList[] = $tblPersonCustody->getLastFirstName();
                                }
                            }
                            if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt') {
                                $tblPersonStudent = $tblToPerson->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    $StudentList[] = $tblPersonStudent->getLastFirstName();
                                }
                            }
                        }
                    }
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }
                $DataPerson['PersonListCustody'] = implode('<br/>', $CustodyList);
                $DataPerson['PersonListStudent'] = implode('<br/>', $StudentList);

                // ignore duplicated Person
                if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                    $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                }
            }
        }

        return ( !empty($TableSearch) ? $TableSearch : false );
    }

    /**
     * @return Form
     */
    private function formFilterStudent()
    {

        $GroupList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        if ($tblGroup) {
            $GroupList[] = $tblGroup;
        }
        $tblGroupCustody = Group::useService()->getGroupByMetaTable('CUSTODY');
        if ($tblGroupCustody) {
            $GroupList[] = $tblGroupCustody;
        }
        $LevelList = array();
        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as $tblLevel) {
                if ($tblLevel->getName() !== '') {
                    $LevelList[] = $tblLevel;
                }
            }
        }

        $FormGroup = array();
        // Filter
        $FormGroup[] = new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterGroup[TblGroup_Id]', 'Gruppe: Name', array('Name' => $GroupList))
                    , 6),
                new FormColumn(
                    new SelectBox('FilterYear[TblYear_Id]', 'Bildung: Schuljahr',
                        array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                    , 6)
            )),
            new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterStudent[TblLevel_Id]', 'Klasse: Stufe',
                        array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                    , 6),
                new FormColumn(
                    new AutoCompleter('FilterStudent[TblDivision_Name]', 'Klasse: Gruppe', '',
                        array('Name' => Division::useService()->getDivisionAll()))
                    , 6),
            )),
//            new FormRow(array(
//                new FormColumn(
//                    new TextField('FilterPerson[TblPerson_FirstName]', 'Vorname', 'Person: Vorname')
//                    , 6),
//                new FormColumn(
//                    new TextField('FilterPerson[TblPerson_LastName]', 'Nachname', 'Person: Nachname')
//                    , 6),
//            ))
        ));
        // POST StandardGroup (first Visit)
        $Global = $this->getGlobal();
        if (!isset($Global->POST['FilterGroup']['TblGroup_Id'])) {
            $Global->POST['FilterGroup']['TblGroup_Id'] = $tblGroup->getId();
            $Global->savePost();
        }
        return new Form(
            $FormGroup
        );
    }

    /**
     * @return Form
     */
    private function formFilterPerson()
    {

        $GroupList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        if ($tblGroup) {
            $GroupList[] = $tblGroup;
        }
        $tblGroupCustody = Group::useService()->getGroupByMetaTable('CUSTODY');
        if ($tblGroupCustody) {
            $GroupList[] = $tblGroupCustody;
        }
        $LevelList = array();
        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as $tblLevel) {
                if ($tblLevel->getName() !== '') {
                    $LevelList[] = $tblLevel;
                }
            }
        }

        $FormGroup = array();
        // Filter
        $FormGroup[] = new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterGroup[TblGroup_Id]', 'Gruppe: Name', array('Name' => $GroupList))
                    , 6)
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('FilterPerson[TblPerson_FirstName]', 'Vorname', 'Person: Vorname')
                    , 6),
                new FormColumn(
                    new TextField('FilterPerson[TblPerson_LastName]', 'Nachname', 'Person: Nachname')
                    , 6),
            ))
        ));
        // POST StandardGroup (first Visit)
        $Global = $this->getGlobal();
        if (!isset($Global->POST['FilterGroup']['TblGroup_Id'])) {
            $Global->POST['FilterGroup']['TblGroup_Id'] = $tblGroup->getId();
            $Global->savePost();
        }
        return new Form(
            $FormGroup
        );
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendResetAccount($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Benutzer Passwort', 'Zurücksetzen');
        if ($Id) {
            $tblUserAccount = Account::useService()->getUserAccountById($Id);
            if (!$tblUserAccount) {
                return $Stage.new DangerMessage('Benutzeraccount nicht gefunden', new Ban())
                    .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR);
            }
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            if (!$tblAccount) {
                return $Stage->setContent(new WarningMessage('Account nicht vorhanden')
                    .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR));
            }
            $tblPerson = $tblUserAccount->getServiceTblPerson();
            if (!$tblPerson) {
                return $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Person', new WarningMessage('Person wurde nicht gefunden')),
                        new Panel(new Question().' Diesen Benutzer wirklich Zurücksetzen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Reset', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User', new Disable())
                        )
                    )
                )))));
            }

            $Stage->addButton(
                new Standard('Zurück', '/Setting/User', new ChevronLeft())
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName())
                            ,
                                'Benutzer: '.new Bold($tblAccount->getUserName())
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Diesen Benutzer wirklich Zurücksetzen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Reset', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User', new Disable())
                        )
                    )))))
                );
            } else {
                $IsChanged = false;
                $Password = $tblUserAccount->getUserPassword();
                // remove tblAccount
                if ($tblAccount && $Password) {
                    if (AccountAuthorization::useService()->changePassword($Password, $tblAccount)) {
                        $IsChanged = true;
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ($IsChanged
                                ? new SuccessMessage(new SuccessIcon().' Der Benutzer wurde Zurückgesetzt')
                                : new DangerMessage(new Ban().' Der Benutzer konnte nicht Zurückgesetzt werden')
                            ),
                            new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new DangerMessage(new Ban().' Der Benutzer konnte nicht gefunden werden'),
                        new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyPrepare($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Benutzer', 'Löschen');
        if ($Id) {
            $tblUserAccount = Account::useService()->getUserAccountById($Id);
            if (!$tblUserAccount) {
                return $Stage.new DangerMessage('Benutzeraccount nicht gefunden', new Ban())
                    .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR);
            }
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            $tblPerson = $tblUserAccount->getServiceTblPerson();
            if (!$tblPerson) {
                // remove prepare if person are deleted (without asking)
                if ($tblAccount) {
                    // remove tblAccount
                    AccountAuthorization::useService()->destroyAccount($tblAccount);
                }
                // remove tblUserAccount
                Account::useService()->removeUserAccount($tblUserAccount);
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            array(
                                new SuccessMessage(new SuccessIcon().' Der Benutzer wurde gelöscht'),
                                new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS)
                            )
                        ))
                    )))
                );
                return $Stage;
            }
            $Stage->addButton(
                new Standard('Zurück', '/Setting/User', new ChevronLeft())
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName())
                            ,
                                'Benutzer: '.new Bold($tblAccount->getUserName())
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Diesen Benutzer wirklich löschen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User', new Disable())
                        )
                    )))))
                );
            } else {
                $IsDestroy = false;
                // remove tblAccount
                if ($tblAccount) {
                    AccountAuthorization::useService()->destroyAccount($tblAccount);
                }
                // remove tblUserAccount
                if (Account::useService()->removeUserAccount($tblUserAccount)) {
                    $IsDestroy = true;
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ($IsDestroy
                                ? new SuccessMessage(new SuccessIcon().' Der Benutzer wurde gelöscht')
                                : new DangerMessage(new Ban().' Der Benutzer konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new DangerMessage(new Ban().' Der Benutzer konnte nicht gefunden werden'),
                        new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}